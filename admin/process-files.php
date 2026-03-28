<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../includes/process-repository.php';

header('Content-Type: application/json; charset=UTF-8');

function adminProcessJsonResponse(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function adminProcessInputToken(): string
{
    $headerToken = isset($_SERVER['HTTP_X_CSRF_TOKEN']) && is_string($_SERVER['HTTP_X_CSRF_TOKEN'])
        ? trim($_SERVER['HTTP_X_CSRF_TOKEN'])
        : '';

    if ($headerToken !== '') {
        return $headerToken;
    }

    return isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) ? trim($_POST['csrf_token']) : '';
}

/**
 * @return array{canonical: string, definitionHandle: string}|null
 */
function adminResolveProcessConfig(string $processInput): ?array
{
    $processRegistry = require __DIR__ . '/../config/process-registry.php';
    $areas = isset($processRegistry['areas']) && is_array($processRegistry['areas']) ? $processRegistry['areas'] : [];
    $aliases = isset($processRegistry['aliases']) && is_array($processRegistry['aliases']) ? $processRegistry['aliases'] : [];

    if (!preg_match('/^[a-z0-9_-]+$/', $processInput)) {
        return null;
    }

    $canonical = '';
    if (isset($aliases[$processInput]) && is_string($aliases[$processInput])) {
        $canonical = strtolower(trim($aliases[$processInput]));
    } elseif (isset($areas[$processInput]) && is_array($areas[$processInput])) {
        $canonical = $processInput;
    }

    if ($canonical === '' || !isset($areas[$canonical]) || !is_array($areas[$canonical])) {
        return null;
    }

    $definitionHandle = isset($areas[$canonical]['definitionHandle']) && is_string($areas[$canonical]['definitionHandle'])
        ? strtolower(trim($areas[$canonical]['definitionHandle']))
        : '';

    if ($definitionHandle === '') {
        return null;
    }

    return ['canonical' => $canonical, 'definitionHandle' => $definitionHandle];
}

/**
 * @param mixed $instrumentEntry
 * @return array{id: string, handle: string}|null
 */
function adminParseInstrumentEntry($instrumentEntry): ?array
{
    $instrumentId = '';
    $instrumentHandle = '';

    if (is_string($instrumentEntry)) {
        $instrumentId = strtolower(trim($instrumentEntry));
        $instrumentHandle = $instrumentId;
    } elseif (is_array($instrumentEntry)) {
        $instrumentId = isset($instrumentEntry['id']) && is_string($instrumentEntry['id'])
            ? strtolower(trim($instrumentEntry['id']))
            : '';

        $instrumentHandle = isset($instrumentEntry['handle']) && is_string($instrumentEntry['handle'])
            ? strtolower(trim($instrumentEntry['handle']))
            : '';

        if ($instrumentHandle === '' && isset($instrumentEntry['file']) && is_string($instrumentEntry['file'])) {
            $instrumentHandle = strtolower(trim(pathinfo($instrumentEntry['file'], PATHINFO_FILENAME)));
        }

        if ($instrumentHandle === '') {
            $instrumentHandle = $instrumentId;
        }
    }

    if ($instrumentId === '' || $instrumentHandle === '') {
        return null;
    }

    if (!preg_match('/^[a-z0-9_-]+$/', $instrumentId) || !preg_match('/^[a-z0-9_-]+$/', $instrumentHandle)) {
        return null;
    }

    return ['id' => $instrumentId, 'handle' => $instrumentHandle];
}

/**
 * @return array<int, array{id: string, handle: string}>
 */
function adminReadOrderedRefsFromProcess(array $processDefinition): array
{
    $refs = [];
    if (!isset($processDefinition['phases']) || !is_array($processDefinition['phases'])) {
        return $refs;
    }

    foreach ($processDefinition['phases'] as $phase) {
        if (!is_array($phase)) {
            continue;
        }

        $instruments = isset($phase['instruments']) && is_array($phase['instruments']) ? $phase['instruments'] : [];
        foreach ($instruments as $instrumentEntry) {
            $parsed = adminParseInstrumentEntry($instrumentEntry);
            if ($parsed !== null) {
                $refs[] = $parsed;
            }
        }
    }

    return $refs;
}

/**
 * @param array<int, array{id: string, handle: string}> $orderedRefs
 */
function adminApplyOrderedRefsToProcess(array $processDefinition, array $orderedRefs): array
{
    $offset = 0;
    $phases = isset($processDefinition['phases']) && is_array($processDefinition['phases'])
        ? $processDefinition['phases']
        : [];

    foreach ($phases as $phaseIndex => $phaseEntry) {
        if (!is_array($phaseEntry)) {
            continue;
        }

        $existingInstruments = isset($phaseEntry['instruments']) && is_array($phaseEntry['instruments'])
            ? $phaseEntry['instruments']
            : [];

        $validInstrumentCount = 0;
        foreach ($existingInstruments as $instrumentEntry) {
            if (adminParseInstrumentEntry($instrumentEntry) !== null) {
                $validInstrumentCount++;
            }
        }

        $phaseSlice = array_slice($orderedRefs, $offset, $validInstrumentCount);
        $offset += $validInstrumentCount;

        $phaseEntry['instruments'] = array_map(
            static fn(array $ref): array => ['id' => $ref['id'], 'handle' => $ref['handle']],
            $phaseSlice
        );

        $phases[$phaseIndex] = $phaseEntry;
    }

    if (empty($phases) && !empty($orderedRefs)) {
        $phases = [[
            'id' => 'phase-1',
            'title' => 'Phase 1',
            'instruments' => array_map(
                static fn(array $ref): array => ['id' => $ref['id'], 'handle' => $ref['handle']],
                $orderedRefs
            ),
        ]];
    }

    $processDefinition['phases'] = $phases;
    return $processDefinition;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    adminProcessJsonResponse(405, ['ok' => false, 'error' => 'Nur POST wird unterstützt.']);
}

if (!isAdminAuthenticated()) {
    adminProcessJsonResponse(403, ['ok' => false, 'error' => 'Nicht autorisiert.']);
}

if (!adminValidateCsrfToken(adminProcessInputToken())) {
    adminProcessJsonResponse(403, ['ok' => false, 'error' => 'CSRF-Prüfung fehlgeschlagen.']);
}

$action = isset($_POST['action']) && is_string($_POST['action']) ? strtolower(trim($_POST['action'])) : '';
$processInput = isset($_POST['process']) && is_string($_POST['process']) ? strtolower(trim($_POST['process'])) : '';
$processConfig = adminResolveProcessConfig($processInput);

if ($processConfig === null) {
    adminProcessJsonResponse(400, ['ok' => false, 'error' => 'Ungültiger Prozess.']);
}

[$processDefinition, $processLoadError] = ndRepoLoadProcessDefinition($processConfig['definitionHandle']);
if (!is_array($processDefinition)) {
    adminProcessJsonResponse(500, ['ok' => false, 'error' => 'Prozessdefinition konnte nicht geladen werden: ' . $processLoadError]);
}

$currentRefs = adminReadOrderedRefsFromProcess($processDefinition);

if ($action === 'reorder') {
    $orderedInput = isset($_POST['ordered_units']) && is_string($_POST['ordered_units'])
        ? json_decode($_POST['ordered_units'], true)
        : null;

    if (!is_array($orderedInput)) {
        adminProcessJsonResponse(400, ['ok' => false, 'error' => 'Ungültige Reihenfolgedaten.']);
    }

    $knownById = [];
    foreach ($currentRefs as $ref) {
        $knownById[$ref['id']] = $ref['handle'];
    }

    $orderedRefs = [];
    $seen = [];
    foreach ($orderedInput as $entry) {
        if (!is_string($entry)) {
            continue;
        }
        $id = strtolower(trim($entry));
        if ($id === '' || isset($seen[$id])) {
            continue;
        }
        if (!isset($knownById[$id])) {
            adminProcessJsonResponse(400, ['ok' => false, 'error' => 'Unbekannte Unit-ID in Reihenfolge: ' . $id]);
        }

        $seen[$id] = true;
        $orderedRefs[] = ['id' => $id, 'handle' => $knownById[$id]];
    }

    if (count($orderedRefs) !== count($currentRefs)) {
        adminProcessJsonResponse(400, ['ok' => false, 'error' => 'Reihenfolge muss alle vorhandenen Units enthalten.']);
    }

    $processDefinition = adminApplyOrderedRefsToProcess($processDefinition, $orderedRefs);
    [$saved, $saveError] = ndRepoSaveProcessDefinition($processConfig['definitionHandle'], $processDefinition);
    if (!$saved) {
        adminProcessJsonResponse(500, ['ok' => false, 'error' => 'Reihenfolge konnte nicht gespeichert werden: ' . $saveError]);
    }

    adminProcessJsonResponse(200, ['ok' => true, 'message' => 'Reihenfolge gespeichert.', 'units' => $orderedRefs]);
}

if ($action === 'delete') {
    $unitId = isset($_POST['unit_id']) && is_string($_POST['unit_id']) ? strtolower(trim($_POST['unit_id'])) : '';
    if ($unitId === '' || !preg_match('/^[a-z0-9_-]+$/', $unitId)) {
        adminProcessJsonResponse(400, ['ok' => false, 'error' => 'Ungültige Unit-ID.']);
    }

    $filteredRefs = [];
    $deletedHandle = '';
    foreach ($currentRefs as $ref) {
        if ($ref['id'] === $unitId) {
            $deletedHandle = $ref['handle'];
            continue;
        }
        $filteredRefs[] = $ref;
    }

    if ($deletedHandle === '') {
        adminProcessJsonResponse(404, ['ok' => false, 'error' => 'Unit wurde im Prozess nicht gefunden.']);
    }

    [$deleted, $deleteError] = ndRepoDeleteCollectionHandle('units', $deletedHandle);
    if (!$deleted) {
        adminProcessJsonResponse(500, ['ok' => false, 'error' => 'Unit-Datei konnte nicht gelöscht werden: ' . $deleteError]);
    }

    $processDefinition = adminApplyOrderedRefsToProcess($processDefinition, $filteredRefs);
    [$saved, $saveError] = ndRepoSaveProcessDefinition($processConfig['definitionHandle'], $processDefinition);
    if (!$saved) {
        adminProcessJsonResponse(500, ['ok' => false, 'error' => 'Prozessdefinition konnte nicht aktualisiert werden: ' . $saveError]);
    }

    adminProcessJsonResponse(200, ['ok' => true, 'message' => 'Unit gelöscht.', 'units' => $filteredRefs]);
}

if ($action === 'upload') {
    $unitId = isset($_POST['unit_id']) && is_string($_POST['unit_id']) ? strtolower(trim($_POST['unit_id'])) : '';
    if ($unitId === '' || !preg_match('/^[a-z0-9_-]+$/', $unitId)) {
        adminProcessJsonResponse(400, ['ok' => false, 'error' => 'Ungültige Unit-ID. Erlaubt: a-z, 0-9, _, -.']);
    }

    if (!isset($_FILES['file']) || !is_array($_FILES['file'])) {
        adminProcessJsonResponse(400, ['ok' => false, 'error' => 'Bitte eine JSON-Datei hochladen.']);
    }

    $file = $_FILES['file'];
    $tmpName = isset($file['tmp_name']) && is_string($file['tmp_name']) ? $file['tmp_name'] : '';
    $errorCode = isset($file['error']) ? (int) $file['error'] : UPLOAD_ERR_NO_FILE;
    if ($errorCode !== UPLOAD_ERR_OK || $tmpName === '' || !is_uploaded_file($tmpName)) {
        adminProcessJsonResponse(400, ['ok' => false, 'error' => 'Upload fehlgeschlagen.']);
    }

    $raw = file_get_contents($tmpName);
    if ($raw === false || trim($raw) === '') {
        adminProcessJsonResponse(400, ['ok' => false, 'error' => 'Datei ist leer oder nicht lesbar.']);
    }

    $unitDefinition = json_decode($raw, true);
    if (!is_array($unitDefinition)) {
        adminProcessJsonResponse(400, ['ok' => false, 'error' => 'Datei enthält kein gültiges JSON-Objekt.']);
    }

    $questionError = ndRepoValidateQuestionStructure($unitDefinition);
    if ($questionError !== null) {
        adminProcessJsonResponse(400, ['ok' => false, 'error' => 'Ungültige Unit-Struktur: ' . $questionError]);
    }

    $unitHandle = $unitId;
    $unitPath = ndRepoBuildPath('units', $unitHandle);
    if ($unitPath === '') {
        adminProcessJsonResponse(400, ['ok' => false, 'error' => 'Unit-Handle ist ungültig.']);
    }

    [$savedUnit, $saveUnitError] = ndRepoWriteJsonAtomically($unitPath, $unitDefinition);
    if (!$savedUnit) {
        adminProcessJsonResponse(500, ['ok' => false, 'error' => 'Unit-Datei konnte nicht gespeichert werden: ' . $saveUnitError]);
    }

    $existsInProcess = false;
    foreach ($currentRefs as $ref) {
        if ($ref['id'] === $unitId) {
            $existsInProcess = true;
            break;
        }
    }

    if (!$existsInProcess) {
        $currentRefs[] = ['id' => $unitId, 'handle' => $unitHandle];
        $processDefinition = adminApplyOrderedRefsToProcess($processDefinition, $currentRefs);
        [$savedProcess, $saveProcessError] = ndRepoSaveProcessDefinition($processConfig['definitionHandle'], $processDefinition);
        if (!$savedProcess) {
            adminProcessJsonResponse(500, ['ok' => false, 'error' => 'Prozessdefinition konnte nicht erweitert werden: ' . $saveProcessError]);
        }
    }

    adminProcessJsonResponse(200, ['ok' => true, 'message' => 'Unit hochgeladen.', 'units' => $currentRefs]);
}

adminProcessJsonResponse(400, ['ok' => false, 'error' => 'Unbekannte Aktion.']);
