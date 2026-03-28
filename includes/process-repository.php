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
    $configured = getenv('NEURODIAG_DATA_DIR');
    if (is_string($configured)) {
        $configured = trim($configured);
        if ($configured !== '') {
            return rtrim($configured, '/');
        }
    }

    return dirname(__DIR__) . '/../neurodiag-storage';
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

    return ndRepoBaseDir() . '/' . $cleanCollection . '/' . $cleanHandle . '.json';
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

    return [$instrumentDefinition, null];
}
