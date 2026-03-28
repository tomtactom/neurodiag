<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/process-repository.php';

/**
 * @return array<string, mixed>|null
 */
function ndReadJson(string $collection, string $handle): ?array
{
    $path = ndRepoBuildPath($collection, $handle);
    if ($path === '') {
        return null;
    }

    [$decoded, $error] = ndRepoLoadJsonFile($path);
    if ($decoded === null || $error !== null) {
        return null;
    }

    return $decoded;
}

/**
 * @return array{unit: string, answers: array<string, string|array<int, string>>, updatedAt: int, completed: bool}|null
 */
function ndLoadAnswersFromCookie(string $processId): ?array
{
    $cookieName = 'neurodiag_process_' . $processId;
    if (!isset($_COOKIE[$cookieName]) || !is_string($_COOKIE[$cookieName]) || $_COOKIE[$cookieName] === '') {
        return null;
    }

    $decoded = json_decode(urldecode($_COOKIE[$cookieName]), true);
    if (!is_array($decoded)) {
        return null;
    }

    $unitId = isset($decoded['unit']) && is_string($decoded['unit']) ? trim($decoded['unit']) : '';
    $answers = isset($decoded['answers']) && is_array($decoded['answers']) ? $decoded['answers'] : [];

    if ($unitId === '' || $answers === []) {
        return null;
    }

    return [
        'unit' => $unitId,
        'answers' => $answers,
        'updatedAt' => isset($decoded['updatedAt']) && is_int($decoded['updatedAt']) ? $decoded['updatedAt'] : time(),
        'completed' => isset($decoded['completed']) ? (bool) $decoded['completed'] : false,
    ];
}

/**
 * @return array{unit: string, answers: array<string, string|array<int, string>>, updatedAt: int, completed: bool}|null
 */
function ndLoadAnswersFromSession(string $processId): ?array
{
    ndStartSecureSession();

    $sessionKey = 'neurodiag_process_' . $processId;
    if (!isset($_SESSION[$sessionKey]) || !is_array($_SESSION[$sessionKey])) {
        return null;
    }

    $state = $_SESSION[$sessionKey];
    $unitId = isset($state['unit']) && is_string($state['unit']) ? trim($state['unit']) : '';
    $answers = isset($state['answers']) && is_array($state['answers']) ? $state['answers'] : [];

    if ($unitId === '' || $answers === []) {
        return null;
    }

    return [
        'unit' => $unitId,
        'answers' => $answers,
        'updatedAt' => isset($state['updatedAt']) && is_int($state['updatedAt']) ? $state['updatedAt'] : time(),
        'completed' => isset($state['completed']) ? (bool) $state['completed'] : false,
    ];
}

/**
 * @return array{unit: string, answers: array<string, string|array<int, string>>, updatedAt: int, completed: bool}|null
 */
function ndLoadAnswersFromRequest(): ?array
{
    $unitId = isset($_REQUEST['unit']) && is_string($_REQUEST['unit']) ? trim($_REQUEST['unit']) : '';
    $answers = isset($_REQUEST['answers']) && is_array($_REQUEST['answers']) ? $_REQUEST['answers'] : [];

    if ($unitId === '' || $answers === []) {
        return null;
    }

    return [
        'unit' => $unitId,
        'answers' => $answers,
        'updatedAt' => time(),
        'completed' => false,
    ];
}

/**
 * @return array{state: ?array{unit: string, answers: array<string, string|array<int, string>>, updatedAt: int, completed: bool}, source: string}
 */
function ndLoadAnswers(string $processId): array
{
    $request = ndLoadAnswersFromRequest();
    if ($request !== null) {
        return ['state' => $request, 'source' => 'request'];
    }

    $session = ndLoadAnswersFromSession($processId);
    if ($session !== null) {
        return ['state' => $session, 'source' => 'session'];
    }

    $cookie = ndLoadAnswersFromCookie($processId);
    if ($cookie !== null) {
        return ['state' => $cookie, 'source' => 'cookie'];
    }

    return ['state' => null, 'source' => 'none'];
}

/**
 * @return array<string, mixed>|null
 */
function ndFindUnitDefinition(array $areas, string $processId, string $unitId): ?array
{
    if (!isset($areas[$processId]['definitionHandle']) || !is_string($areas[$processId]['definitionHandle'])) {
        return null;
    }

    $processHandle = strtolower(trim($areas[$processId]['definitionHandle']));
    $processDefinition = ndReadJson('processes', $processHandle);
    if ($processDefinition === null) {
        return null;
    }

    [$unitRefs, $phaseError] = ndRepoGetInstrumentRefs($processDefinition);
    if ($phaseError !== null) {
        return null;
    }

    foreach ($unitRefs as $instrumentRef) {
        if (!isset($instrumentRef['id'], $instrumentRef['handle'])) {
            continue;
        }

        if ($instrumentRef['id'] === $unitId) {
            return ndReadJson('units', $instrumentRef['handle']);
        }
    }

    return null;
}

/**
 * @param array<string, string|array<int, string>> $answers
 * @return array{sum: float, mean: float, answered: int, missing: int, expected: int, invalid: bool}
 */
function ndComputeRawScores(array $unitDefinition, array $answers): array
{
    $questions = isset($unitDefinition['questions']) && is_array($unitDefinition['questions']) ? $unitDefinition['questions'] : [];
    $scoring = isset($unitDefinition['scoring']) && is_array($unitDefinition['scoring']) ? $unitDefinition['scoring'] : [];
    $normMeta = isset($scoring['norm_meta']) && is_array($scoring['norm_meta']) ? $scoring['norm_meta'] : [];

    $sum = 0.0;
    $answered = 0;
    $missing = 0;

    foreach ($questions as $question) {
        if (!is_array($question)) {
            continue;
        }

        $questionId = isset($question['id']) && is_string($question['id']) ? $question['id'] : '';
        if ($questionId === '') {
            continue;
        }

        if (!array_key_exists($questionId, $answers) || !is_string($answers[$questionId])) {
            $missing++;
            continue;
        }

        $options = isset($question['options']) && is_array($question['options']) ? $question['options'] : [];
        $rawValue = trim($answers[$questionId]);
        $optionIndex = array_search($rawValue, $options, true);

        if ($optionIndex === false) {
            if (is_numeric($rawValue)) {
                $value = (float) $rawValue;
            } else {
                $missing++;
                continue;
            }
        } else {
            $value = (float) $optionIndex + 1.0;
        }

        if (isset($question['reverse']) && $question['reverse'] === true && count($options) > 0) {
            $value = ((float) count($options) + 1.0) - $value;
        }

        $sum += $value;
        $answered++;
    }

    $expected = count($questions);
    $mean = $answered > 0 ? $sum / $answered : 0.0;

    $invalid = false;
    $maxMissing = isset($normMeta['missing_rules']['max_missing']) && is_int($normMeta['missing_rules']['max_missing'])
        ? $normMeta['missing_rules']['max_missing']
        : 0;

    if ($missing > $maxMissing) {
        $invalid = true;
    }

    if (!$invalid && $missing > 0) {
        $strategy = isset($normMeta['missing_rules']['strategy']) && is_string($normMeta['missing_rules']['strategy'])
            ? $normMeta['missing_rules']['strategy']
            : 'prorate';

        if ($strategy === 'prorate' && $answered > 0) {
            $sum = $mean * $expected;
        } else {
            $invalid = true;
        }
    }

    return [
        'sum' => $sum,
        'mean' => $mean,
        'answered' => $answered,
        'missing' => $missing,
        'expected' => $expected,
        'invalid' => $invalid,
    ];
}

function ndNormalCdf(float $z): float
{
    $sign = $z < 0 ? -1.0 : 1.0;
    $x = abs($z) / sqrt(2.0);
    $t = 1.0 / (1.0 + 0.3275911 * $x);
    $a1 = 0.254829592;
    $a2 = -0.284496736;
    $a3 = 1.421413741;
    $a4 = -1.453152027;
    $a5 = 1.061405429;
    $erf = 1.0 - (((((($a5 * $t + $a4) * $t) + $a3) * $t + $a2) * $t + $a1) * $t * exp(-$x * $x));
    return 0.5 * (1.0 + $sign * $erf);
}

/**
 * @return array{z: ?float, t: ?float, pr: ?float, reference: string, direction: string, cutoffs: array<string, mixed>, reliability: ?float, notes: string}
 */
function ndTransformNormScores(array $unitDefinition, array $raw): array
{
    $scoring = isset($unitDefinition['scoring']) && is_array($unitDefinition['scoring']) ? $unitDefinition['scoring'] : [];
    $normMeta = isset($scoring['norm_meta']) && is_array($scoring['norm_meta']) ? $scoring['norm_meta'] : [];

    $referenceMean = isset($normMeta['reference']['mean']) && is_numeric($normMeta['reference']['mean'])
        ? (float) $normMeta['reference']['mean']
        : null;
    $referenceSd = isset($normMeta['reference']['sd']) && is_numeric($normMeta['reference']['sd'])
        ? (float) $normMeta['reference']['sd']
        : null;

    $z = null;
    $t = null;
    $pr = null;

    if ($referenceMean !== null && $referenceSd !== null && $referenceSd > 0.0 && !$raw['invalid']) {
        $z = ($raw['sum'] - $referenceMean) / $referenceSd;
        $t = 50.0 + (10.0 * $z);
        $pr = ndNormalCdf($z) * 100.0;
    }

    return [
        'z' => $z,
        't' => $t,
        'pr' => $pr,
        'reference' => isset($normMeta['reference']['label']) && is_string($normMeta['reference']['label'])
            ? $normMeta['reference']['label']
            : 'Kein Referenzbereich hinterlegt',
        'direction' => isset($normMeta['direction']) && is_string($normMeta['direction']) ? $normMeta['direction'] : 'higher_is_more_strain',
        'cutoffs' => isset($normMeta['cutoffs']) && is_array($normMeta['cutoffs']) ? $normMeta['cutoffs'] : [],
        'reliability' => isset($normMeta['reliability_alpha']) && is_numeric($normMeta['reliability_alpha'])
            ? (float) $normMeta['reliability_alpha']
            : null,
        'notes' => isset($normMeta['interpretation_notes']) && is_string($normMeta['interpretation_notes'])
            ? $normMeta['interpretation_notes']
            : 'Ergebnisse sind orientierend zu verstehen.',
    ];
}

function ndFmt(?float $value, int $decimals = 2): string
{
    if ($value === null) {
        return 'n/a';
    }

    return number_format($value, $decimals, '.', '');
}

/**
 * @return array{apa: string, description: string, interpretation: string, safety: array<int, string>, transparency: array<int, string>}
 */
function ndBuildTextBlocks(array $unitDefinition, array $raw, array $norm): array
{
    $title = isset($unitDefinition['title']) && is_string($unitDefinition['title']) ? $unitDefinition['title'] : 'Skala';
    $scaleName = isset($norm['cutoffs']['scale_name']) && is_string($norm['cutoffs']['scale_name'])
        ? $norm['cutoffs']['scale_name']
        : $title;

    $apa = $raw['invalid']
        ? sprintf('%s: Keine APA-7-Ausgabe möglich, da zu viele fehlende Angaben vorliegen (fehlend = %d von %d).', $scaleName, $raw['missing'], $raw['expected'])
        : sprintf(
            '%s: M = %s, SD_ref = %s, z = %s, T = %s, PR = %s.',
            $scaleName,
            ndFmt($raw['mean'], 2),
            ndFmt(isset($norm['cutoffs']['reference_sd']) && is_numeric($norm['cutoffs']['reference_sd']) ? (float) $norm['cutoffs']['reference_sd'] : null, 2),
            ndFmt($norm['z'], 2),
            ndFmt($norm['t'], 1),
            ndFmt($norm['pr'], 1)
        );

    $description = $raw['invalid']
        ? 'Deskriptiv: Für eine belastbare Einordnung fehlen zu viele Antworten. Bitte Fragebogen vollständig bearbeiten.'
        : sprintf(
            'Deskriptiv: Rohwert = %s (beantwortet %d/%d), Referenz = %s.',
            ndFmt($raw['sum'], 2),
            $raw['answered'],
            $raw['expected'],
            (string) $norm['reference']
        );

    $interpretation = 'Interpretative Einordnung: ' . (string) $norm['notes'];
    if (isset($norm['reliability']) && is_float($norm['reliability'])) {
        $interpretation .= ' Interne Konsistenz (Cronbachs α) ≈ ' . ndFmt($norm['reliability'], 2) . '.';
    }

    return [
        'apa' => $apa,
        'description' => $description,
        'interpretation' => $interpretation,
        'safety' => [
            'Dieses Ergebnis ist ein Screening mit Orientierungscharakter und kein Diagnosenachweis.',
            'Bei erhöhten Belastungsindikatoren wird eine professionelle psychologische oder ärztliche Abklärung empfohlen.',
        ],
        'transparency' => [
            'Die Auswertung trennt deskriptive Kennwerte von interpretativen Hinweisen.',
            'Antwortquelle und fehlende Werte werden transparent dokumentiert.',
        ],
    ];
}

/**
 * @param array<string, array<string, mixed>> $areas
 * @return array<string, mixed>
 */
function ndBuildEvaluationViewModel(?string $processId, ?string $processTitle, array $areas): array
{
    if ($processId === null) {
        return [
            'hasData' => false,
            'title' => 'Integrierte Ergebnisübersicht',
            'source' => 'none',
            'unitTitle' => null,
            'raw' => null,
            'norm' => null,
            'text' => [
                'apa' => 'Keine prozessbezogenen Antwortdaten vorhanden.',
                'description' => 'Bitte wählen Sie einen Prozess und schließen Sie mindestens eine Unit ab.',
                'interpretation' => 'Interpretative Einordnung erscheint nach Vorliegen von Antwortdaten.',
                'safety' => [
                    'Diese Seite ersetzt keine Diagnostik.',
                    'Bei relevanter Belastung wird professionelle Abklärung empfohlen.',
                ],
                'transparency' => [
                    'Aktuell liegen keine Antwortdaten vor.',
                ],
            ],
        ];
    }

    $loaded = ndLoadAnswers($processId);
    $state = $loaded['state'];

    if ($state === null) {
        return [
            'hasData' => false,
            'title' => (string) $processTitle,
            'source' => $loaded['source'],
            'unitTitle' => null,
            'raw' => null,
            'norm' => null,
            'text' => [
                'apa' => 'Noch keine Antworten gefunden.',
                'description' => 'Es wurden keine Antwortdaten aus Request, Session oder Cookie geladen.',
                'interpretation' => 'Bitte starten Sie zuerst den jeweiligen Prozess.',
                'safety' => [
                    'Diese Seite ersetzt keine Diagnostik.',
                    'Bei relevanter Belastung wird professionelle Abklärung empfohlen.',
                ],
                'transparency' => [
                    'Antwortquellen geprüft: request, session, cookie.',
                ],
            ],
        ];
    }

    $unitDefinition = ndFindUnitDefinition($areas, $processId, $state['unit']);
    if ($unitDefinition === null) {
        return [
            'hasData' => false,
            'title' => (string) $processTitle,
            'source' => $loaded['source'],
            'unitTitle' => null,
            'raw' => null,
            'norm' => null,
            'text' => [
                'apa' => 'Antwortdaten wurden gefunden, aber die Unit-Definition fehlt.',
                'description' => 'Unit-ID: ' . $state['unit'],
                'interpretation' => 'Bitte JSON-Zuordnung im Prozess prüfen.',
                'safety' => [
                    'Diese Seite ersetzt keine Diagnostik.',
                    'Bei relevanter Belastung wird professionelle Abklärung empfohlen.',
                ],
                'transparency' => [
                    'Antwortquelle: ' . $loaded['source'] . '.',
                ],
            ],
        ];
    }

    $raw = ndComputeRawScores($unitDefinition, $state['answers']);
    $norm = ndTransformNormScores($unitDefinition, $raw);
    $text = ndBuildTextBlocks($unitDefinition, $raw, $norm);

    return [
        'hasData' => true,
        'title' => (string) $processTitle,
        'source' => $loaded['source'],
        'unitTitle' => isset($unitDefinition['title']) && is_string($unitDefinition['title']) ? $unitDefinition['title'] : $state['unit'],
        'raw' => $raw,
        'norm' => $norm,
        'text' => $text,
    ];
}
