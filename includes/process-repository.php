<?php

declare(strict_types=1);

/**
 * @return array{0: ?array<string, mixed>, 1: ?string}
 */
function ndRepoLoadJsonFile(string $path): array
{
    if (!is_file($path) || !is_readable($path)) {
        return [null, 'Datei wurde nicht gefunden oder ist nicht lesbar.'];
    }

    $raw = file_get_contents($path);
    if ($raw === false) {
        return [null, 'Datei konnte nicht geladen werden.'];
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        return [null, 'Datei enthält kein gültiges JSON-Objekt.'];
    }

    return [$data, null];
}

function ndRepoBaseDir(): string
{
    static $resolved = null;
    if (is_string($resolved)) {
        return $resolved;
    }

    $configured = '';
    if (isset($GLOBALS['NEURODIAG_CONFIG']) && is_array($GLOBALS['NEURODIAG_CONFIG'])) {
        $configuredValue = $GLOBALS['NEURODIAG_CONFIG']['PROCESS_STORAGE_DIR'] ?? '';
        if (is_string($configuredValue)) {
            $configured = trim($configuredValue);
        }
    }

    if ($configured === '') {
        $envConfigured = getenv('NEURODIAG_DATA_DIR');
        if (is_string($envConfigured)) {
            $configured = trim($envConfigured);
        }
    }

    if ($configured === '') {
        $configured = dirname(__DIR__, 2) . '/neurodiag-storage';
    }

    if ($configured[0] !== '/') {
        throw new RuntimeException('PROCESS_STORAGE_DIR muss als absoluter Pfad konfiguriert sein.');
    }

    if (!is_dir($configured) && !mkdir($configured, 0770, true) && !is_dir($configured)) {
        throw new RuntimeException('PROCESS_STORAGE_DIR konnte nicht erstellt werden.');
    }

    $realBaseDir = realpath($configured);
    if ($realBaseDir === false || !is_dir($realBaseDir)) {
        throw new RuntimeException('PROCESS_STORAGE_DIR konnte nicht per realpath() validiert werden.');
    }

    $resolved = rtrim($realBaseDir, '/');
    return $resolved;
}

function ndRepoPathInRoot(string $root, string $path): bool
{
    if ($path === $root) {
        return true;
    }

    return strncmp($path, $root . '/', strlen($root) + 1) === 0;
}

function ndRepoBuildPath(string $collection, string $handle): string
{
    $cleanCollection = trim($collection);
    $cleanHandle = strtolower(trim($handle));

    if (!preg_match('/^[a-z0-9_-]+$/', $cleanCollection)) {
        return '';
    }

    if (!preg_match('/^[a-z0-9_-]+$/', $cleanHandle)) {
        return '';
    }

    $baseDir = ndRepoBaseDir();
    $collectionDir = $baseDir . '/' . $cleanCollection;
    if (!is_dir($collectionDir) && !mkdir($collectionDir, 0770, true) && !is_dir($collectionDir)) {
        return '';
    }

    $resolvedCollectionDir = realpath($collectionDir);
    if (!is_string($resolvedCollectionDir) || !ndRepoPathInRoot($baseDir, $resolvedCollectionDir)) {
        return '';
    }

    $resolvedPath = $resolvedCollectionDir . '/' . $cleanHandle . '.json';
    if (!ndRepoPathInRoot($baseDir, $resolvedCollectionDir)) {
        return '';
    }

    return $resolvedPath;
}

function ndRepoEnsureCollectionDir(string $collection): string
{
    $cleanCollection = trim($collection);
    if (!preg_match('/^[a-z0-9_-]+$/', $cleanCollection)) {
        return '';
    }

    $dir = ndRepoBaseDir() . '/' . $cleanCollection;
    if (!is_dir($dir) && !mkdir($dir, 0770, true) && !is_dir($dir)) {
        return '';
    }

    $resolved = realpath($dir);
    if (!is_string($resolved) || !ndRepoPathInRoot(ndRepoBaseDir(), $resolved)) {
        return '';
    }

    return $resolved;
}

/**
 * @return array{0: bool, 1: ?string}
 */
function ndRepoWriteJsonAtomically(string $targetPath, array $payload): array
{
    $targetDir = dirname($targetPath);
    if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
        return [false, 'Zielverzeichnis konnte nicht erstellt werden.'];
    }

    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($json)) {
        return [false, 'JSON-Kodierung fehlgeschlagen.'];
    }
    $json .= PHP_EOL;

    $lockHandle = fopen($targetPath . '.lock', 'c');
    if ($lockHandle === false) {
        return [false, 'Lock-Datei konnte nicht geöffnet werden.'];
    }

    $tempFile = '';
    try {
        if (!flock($lockHandle, LOCK_EX)) {
            return [false, 'Dateisperre konnte nicht gesetzt werden.'];
        }

        $tempFile = tempnam($targetDir, 'ndtmp_');
        if ($tempFile === false) {
            return [false, 'Temporäre Datei konnte nicht erstellt werden.'];
        }

        if (file_put_contents($tempFile, $json, LOCK_EX) === false) {
            return [false, 'Temporäre Datei konnte nicht geschrieben werden.'];
        }

        if (!rename($tempFile, $targetPath)) {
            return [false, 'Datei konnte nicht atomar ersetzt werden.'];
        }
    } finally {
        if ($tempFile !== '' && is_file($tempFile)) {
            @unlink($tempFile);
        }
        flock($lockHandle, LOCK_UN);
        fclose($lockHandle);
    }

    return [true, null];
}

/**
 * @return array{0: ?array<string, mixed>, 1: ?string}
 */
function ndRepoLoadProcessDefinition(string $handle): array
{
    $path = ndRepoBuildPath('processes', $handle);
    if ($path === '') {
        return [null, 'Ungültiger Prozess-Handle.'];
    }

    return ndRepoLoadJsonFile($path);
}

/**
 * @return array{0: bool, 1: ?string}
 */
function ndRepoSaveProcessDefinition(string $handle, array $processDefinition): array
{
    $path = ndRepoBuildPath('processes', $handle);
    if ($path === '') {
        return [false, 'Ungültiger Prozess-Handle.'];
    }

    return ndRepoWriteJsonAtomically($path, $processDefinition);
}

/**
 * @return array<int, string>
 */
function ndRepoListCollectionHandles(string $collection): array
{
    $dir = ndRepoEnsureCollectionDir($collection);
    if ($dir === '') {
        return [];
    }

    $files = glob($dir . '/*.json');
    if (!is_array($files)) {
        return [];
    }

    $handles = [];
    foreach ($files as $filePath) {
        $name = pathinfo($filePath, PATHINFO_FILENAME);
        if (is_string($name) && preg_match('/^[a-z0-9_-]+$/', $name)) {
            $handles[] = strtolower($name);
        }
    }

    sort($handles);
    return array_values(array_unique($handles));
}

/**
 * @return array{0: bool, 1: ?string}
 */
function ndRepoDeleteCollectionHandle(string $collection, string $handle): array
{
    $path = ndRepoBuildPath($collection, $handle);
    if ($path === '') {
        return [false, 'Ungültiger Handle.'];
    }

    if (!is_file($path)) {
        return [false, 'Datei wurde nicht gefunden.'];
    }

    if (!unlink($path)) {
        return [false, 'Datei konnte nicht gelöscht werden.'];
    }

    return [true, null];
}

/**
 * @return array{0: array<int, array{id: string, handle: string}>, 1: ?string}
 */
function ndRepoGetInstrumentRefs(array $processDefinition): array
{
    $instrumentRefs = [];
    $knownIds = [];

    if (!isset($processDefinition['phases']) || !is_array($processDefinition['phases']) || empty($processDefinition['phases'])) {
        return [[], 'Die Prozessdefinition enthält keine Phasen. Bitte ergänze das Feld "phases".'];
    }

    foreach ($processDefinition['phases'] as $phaseIndex => $phaseEntry) {
        if (!is_array($phaseEntry)) {
            return [[], 'Phase ' . ($phaseIndex + 1) . ' ist ungültig. Jede Phase muss ein JSON-Objekt sein.'];
        }

        $instruments = isset($phaseEntry['instruments']) && is_array($phaseEntry['instruments'])
            ? $phaseEntry['instruments']
            : [];

        if (empty($instruments)) {
            return [[], 'Phase ' . ($phaseIndex + 1) . ' enthält keine Instrument-Referenzen.'];
        }

        foreach ($instruments as $instrumentEntry) {
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
                    $fileName = pathinfo(trim($instrumentEntry['file']), PATHINFO_FILENAME);
                    $instrumentHandle = strtolower(trim($fileName));
                }

                if ($instrumentHandle === '') {
                    $instrumentHandle = $instrumentId;
                }
            }

            if ($instrumentId === '' || $instrumentHandle === '') {
                continue;
            }

            if (!preg_match('/^[a-z0-9_-]+$/', $instrumentId) || !preg_match('/^[a-z0-9_-]+$/', $instrumentHandle)) {
                continue;
            }

            $instrumentRefs[] = [
                'id' => $instrumentId,
                'handle' => $instrumentHandle,
            ];
        }
    }

    if (empty($instrumentRefs)) {
        return [[], 'Die Prozessdefinition enthält keine gültigen Instrument-Referenzen.'];
    }

    foreach ($instrumentRefs as $instrumentRef) {
        if (isset($knownIds[$instrumentRef['id']])) {
            return [[], 'Die Instrument-ID "' . $instrumentRef['id'] . '" ist mehrfach vorhanden.'];
        }

        $knownIds[$instrumentRef['id']] = true;
    }

    return [$instrumentRefs, null];
}

/**
 * @param array<string, mixed> $instrumentDefinition
 */
function ndRepoValidateQuestionStructure(array $instrumentDefinition): ?string
{
    if (!isset($instrumentDefinition['questions']) || !is_array($instrumentDefinition['questions']) || empty($instrumentDefinition['questions'])) {
        return 'Das Instrument enthält keine gültigen Fragen.';
    }

    foreach ($instrumentDefinition['questions'] as $index => $questionEntry) {
        if (is_string($questionEntry)) {
            if (trim($questionEntry) === '') {
                return 'Frage ' . ($index + 1) . ' ist leer.';
            }
            continue;
        }

        if (!is_array($questionEntry)) {
            return 'Frage ' . ($index + 1) . ' hat ein ungültiges Format.';
        }

        $questionText = '';
        if (isset($questionEntry['text']) && is_string($questionEntry['text'])) {
            $questionText = trim($questionEntry['text']);
        } elseif (isset($questionEntry['title']) && is_string($questionEntry['title'])) {
            $questionText = trim($questionEntry['title']);
        }

        if ($questionText === '') {
            return 'Frage ' . ($index + 1) . ' benötigt ein Feld "text" oder "title".';
        }

        if (isset($questionEntry['options']) && !is_array($questionEntry['options'])) {
            return 'Frage ' . ($index + 1) . ' enthält ein ungültiges "options"-Format.';
        }
    }

    return null;
}

/**
 * @param array<string, mixed> $instrumentDefinition
 */
function ndRepoValidateInstrumentSchema(array $instrumentDefinition): ?string
{
    $requiredStringFields = ['id', 'title', 'description'];
    foreach ($requiredStringFields as $fieldName) {
        if (!isset($instrumentDefinition[$fieldName]) || !is_string($instrumentDefinition[$fieldName]) || trim($instrumentDefinition[$fieldName]) === '') {
            return 'Pflichtfeld "' . $fieldName . '" fehlt oder ist leer.';
        }
    }

    if (!isset($instrumentDefinition['instructions'])) {
        return 'Pflichtfeld "instructions" fehlt.';
    }

    $instructions = $instrumentDefinition['instructions'];
    if (is_string($instructions)) {
        if (trim($instructions) === '') {
            return 'Feld "instructions" darf nicht leer sein.';
        }
    } elseif (is_array($instructions)) {
        if (empty($instructions)) {
            return 'Feld "instructions" darf nicht leer sein.';
        }
        foreach ($instructions as $index => $entry) {
            if (!is_string($entry) || trim($entry) === '') {
                return 'Feld "instructions" enthält bei Position ' . ($index + 1) . ' einen ungültigen Eintrag.';
            }
        }
    } else {
        return 'Feld "instructions" muss ein String oder ein Array sein.';
    }

    return null;
}

/**
 * @return array{0: ?array<string, mixed>, 1: ?string}
 */
function ndRepoLoadAndValidateInstrument(string $handle): array
{
    $path = ndRepoBuildPath('units', $handle);
    if ($path === '') {
        return [null, 'Ungültiger Unit-Handle.'];
    }

    [$instrumentDefinition, $instrumentError] = ndRepoLoadJsonFile($path);
    if ($instrumentDefinition === null) {
        return [null, 'Die Unit mit Handle "' . $handle . '" konnte nicht geladen werden: ' . $instrumentError];
    }

    $questionError = ndRepoValidateQuestionStructure($instrumentDefinition);
    if ($questionError !== null) {
        return [null, 'Die Fragenstruktur der Unit "' . $handle . '" ist ungültig: ' . $questionError];
    }

    $schemaError = ndRepoValidateInstrumentSchema($instrumentDefinition);
    if ($schemaError !== null) {
        return [null, 'Die Unit "' . $handle . '" verletzt das erwartete Inhaltsschema: ' . $schemaError];
    }

    return [$instrumentDefinition, null];
}
