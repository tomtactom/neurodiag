<?php
/**
 * JSON-driven process renderer for diagnostics units.
 *
 * URL params:
 * - process (required): canonical slug oder Legacy-Alias (z. B. ass, adhs)
 * - unit (optional): active unit identifier from process definition
 */

declare(strict_types=1);

$processRegistry = require __DIR__ . '/config/process-registry.php';
$areas = isset($processRegistry['areas']) && is_array($processRegistry['areas']) ? $processRegistry['areas'] : [];
$aliases = isset($processRegistry['aliases']) && is_array($processRegistry['aliases']) ? $processRegistry['aliases'] : [];

$processInput = isset($_GET['process']) ? strtolower(trim((string) $_GET['process'])) : '';
$requestedUnitId = isset($_GET['unit']) ? strtolower(trim((string) $_GET['unit'])) : '';

if (!preg_match('/^[a-z0-9_-]+$/', $processInput)) {
    $processInput = '';
}
if (!preg_match('/^[a-z0-9_-]*$/', $requestedUnitId)) {
    $requestedUnitId = '';
}

$canonicalProcessId = ($processInput !== '' && isset($aliases[$processInput]) && is_string($aliases[$processInput]))
    ? $aliases[$processInput]
    : '';

/**
 * @return array{0: ?array<string, mixed>, 1: ?string}
 */
function loadJsonFile(string $path): array
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

/**
 * @param array<string, mixed> $processDefinition
 * @return array{0: array<int, array{id: string, file: string}>, 1: ?string}
 */
function getInstrumentRefs(array $processDefinition): array
{
    $instrumentRefs = [];
    $knownIds = [];

    if (!isset($processDefinition['phases']) || !is_array($processDefinition['phases']) || empty($processDefinition['phases'])) {
        return [[], 'Die Prozessdefinition enthält keine Phasen. Bitte ergänze im JSON das Feld "phases".'];
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
            if (is_string($instrumentEntry)) {
                $instrumentId = trim($instrumentEntry);
                if ($instrumentId === '') {
                    continue;
                }

                $instrumentRefs[] = [
                    'id' => $instrumentId,
                    'file' => $instrumentId . '.json',
                ];
                continue;
            }

            if (!is_array($instrumentEntry)) {
                continue;
            }

            $instrumentId = isset($instrumentEntry['id']) && is_string($instrumentEntry['id']) ? trim($instrumentEntry['id']) : '';
            $instrumentFile = isset($instrumentEntry['file']) && is_string($instrumentEntry['file']) ? trim($instrumentEntry['file']) : '';

            if ($instrumentId === '' && $instrumentFile !== '') {
                $instrumentId = pathinfo($instrumentFile, PATHINFO_FILENAME);
            }

            if ($instrumentId === '') {
                continue;
            }

            if ($instrumentFile === '') {
                $instrumentFile = $instrumentId . '.json';
            }

            $instrumentRefs[] = [
                'id' => $instrumentId,
                'file' => $instrumentFile,
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
function validateQuestionStructure(array $instrumentDefinition): ?string
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
 * @param array{id: string, file: string} $instrumentRef
 * @return array{0: ?array<string, mixed>, 1: ?string}
 */
function loadAndValidateInstrument(string $baseDir, array $instrumentRef): array
{
    $instrumentFileName = basename($instrumentRef['file']);
    $instrumentPath = rtrim($baseDir, '/') . '/' . $instrumentFileName;

    [$instrumentDefinition, $instrumentError] = loadJsonFile($instrumentPath);
    if ($instrumentDefinition === null) {
        return [null, 'Die Instrument-Datei "data/units/' . $instrumentFileName . '" konnte nicht geladen werden: ' . $instrumentError];
    }

    $questionError = validateQuestionStructure($instrumentDefinition);
    if ($questionError !== null) {
        return [null, 'Die Fragenstruktur von "data/units/' . $instrumentFileName . '" ist ungültig: ' . $questionError];
    }

    return [$instrumentDefinition, null];
}

/**
 * @param array<int, array{id: string, file: string}> $unitRefs
 */
function renderError(string $message, array $unitRefs = []): void
{
    $pageTitle = 'Diagnostischer Prozess';
    include 'includes/header.php';
    ?>
    <main class="process-page" aria-labelledby="process-error-title">
      <section class="process-error" role="alert">
        <h1 id="process-error-title">Prozess konnte nicht geladen werden</h1>
        <p><?php echo htmlspecialchars($message); ?></p>
        <?php if (!empty($unitRefs)): ?>
          <p>Hinweis: Die Prozessdefinition wurde erkannt, aber mindestens eine Unit fehlt oder ist fehlerhaft.</p>
        <?php endif; ?>
        <p>
          <a href="diagnostics.php">Zurück zur Modulübersicht</a>
        </p>
      </section>
    </main>
    <?php
    include 'includes/footer.php';
    exit;
}

/**
 * @param array<int, array{id: string, file: string}> $unitRefs
 * @return array{unit: string, answers: array<string, string>, updatedAt: int, completed: bool}|null
 */
function readResumeStateFromCookie(string $processId, array $unitRefs): ?array
{
    $cookieName = 'neurodiag_process_' . $processId;

    if (!isset($_COOKIE[$cookieName]) || !is_string($_COOKIE[$cookieName]) || $_COOKIE[$cookieName] === '') {
        return null;
    }

    $rawCookie = urldecode($_COOKIE[$cookieName]);
    $decoded = json_decode($rawCookie, true);
    if (!is_array($decoded)) {
        return null;
    }

    $cookieUnit = isset($decoded['unit']) && is_string($decoded['unit']) ? strtolower(trim($decoded['unit'])) : '';
    $updatedAt = isset($decoded['updatedAt']) && is_int($decoded['updatedAt']) ? $decoded['updatedAt'] : 0;
    $completed = isset($decoded['completed']) && is_bool($decoded['completed']) ? $decoded['completed'] : false;

    $validUnitIds = array_map(static fn(array $entry): string => $entry['id'], $unitRefs);
    if ($cookieUnit === '' || !in_array($cookieUnit, $validUnitIds, true)) {
        return null;
    }

    $answers = [];
    if (isset($decoded['answers']) && is_array($decoded['answers'])) {
        foreach ($decoded['answers'] as $questionId => $value) {
            if (!is_string($questionId) || (!is_string($value) && !is_int($value) && !is_float($value) && !is_bool($value))) {
                continue;
            }

            $cleanQuestionId = trim($questionId);
            if ($cleanQuestionId === '') {
                continue;
            }

            $answers[$cleanQuestionId] = (string) $value;
        }
    }

    return [
        'unit' => $cookieUnit,
        'answers' => $answers,
        'updatedAt' => $updatedAt,
        'completed' => $completed,
    ];
}

function clearResumeCookie(string $processId): void
{
    $isHttps = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);

    setcookie('neurodiag_process_' . $processId, '', [
        'expires' => time() - 3600,
        'path' => '/',
        'samesite' => 'Lax',
        'secure' => $isHttps,
        'httponly' => false,
    ]);
}

/**
 * @param mixed $value
 * @return array<int, string>
 */
function normalizeTextItems($value): array
{
    $items = [];

    if (is_string($value)) {
        $trimmed = trim($value);
        if ($trimmed !== '') {
            $items[] = $trimmed;
        }

        return $items;
    }

    if (!is_array($value)) {
        return $items;
    }

    foreach ($value as $entry) {
        if (!is_string($entry)) {
            continue;
        }

        $trimmed = trim($entry);
        if ($trimmed !== '') {
            $items[] = $trimmed;
        }
    }

    return $items;
}

if ($canonicalProcessId === '' || !isset($areas[$canonicalProcessId]) || !is_array($areas[$canonicalProcessId])) {
    renderError('Bitte wähle einen gültigen Prozess über den Parameter "process" (z. B. ass, dyslexie-lrs oder dld).');
}

$processMeta = $areas[$canonicalProcessId];
$definitionFile = isset($processMeta['definitionFile']) && is_string($processMeta['definitionFile'])
    ? trim($processMeta['definitionFile'])
    : '';

if ($definitionFile === '') {
    renderError('Für den Bereich "' . $canonicalProcessId . '" wurde keine Prozessdefinition hinterlegt.');
}

$processPath = __DIR__ . '/' . ltrim($definitionFile, '/');
[$processDefinition, $processError] = loadJsonFile($processPath);
if ($processDefinition === null) {
    renderError('Die Prozessdatei "' . $definitionFile . '" konnte nicht geladen werden: ' . $processError);
}

[$unitRefs, $phaseError] = getInstrumentRefs($processDefinition);
if ($phaseError !== null) {
    renderError($phaseError);
}

$resumeState = readResumeStateFromCookie($canonicalProcessId, $unitRefs);
if (isset($_COOKIE['neurodiag_process_' . $canonicalProcessId]) && $resumeState === null) {
    clearResumeCookie($canonicalProcessId);
}

if ($requestedUnitId === '' && $resumeState !== null && !$resumeState['completed']) {
    header('Location: process.php?process=' . urlencode($canonicalProcessId) . '&unit=' . urlencode($resumeState['unit']));
    exit;
}

$unitIds = array_map(static fn(array $entry): string => $entry['id'], $unitRefs);
$activeUnitId = $requestedUnitId !== '' ? $requestedUnitId : $unitIds[0];
$activeUnitIndex = array_search($activeUnitId, $unitIds, true);

if ($activeUnitIndex === false) {
    renderError('Die angeforderte Unit "' . $activeUnitId . '" ist in diesem Prozess nicht enthalten.', $unitRefs);
}

$activeUnitRef = $unitRefs[$activeUnitIndex];
[$unitDefinition, $instrumentValidationError] = loadAndValidateInstrument(__DIR__ . '/data/units', $activeUnitRef);
if ($unitDefinition === null) {
    renderError((string) $instrumentValidationError, $unitRefs);
}

$processTitle = isset($processDefinition['title']) && is_string($processDefinition['title'])
    ? $processDefinition['title']
    : strtoupper($canonicalProcessId) . ' Prozess';

$processDescription = isset($processDefinition['description']) && is_string($processDefinition['description'])
    ? $processDefinition['description']
    : 'Strukturierter diagnostischer Ablauf auf Basis von JSON-Definitionen.';

$unitTitle = isset($unitDefinition['title']) && is_string($unitDefinition['title'])
    ? $unitDefinition['title']
    : 'Unit ' . ($activeUnitIndex + 1);

$unitDescription = isset($unitDefinition['description']) && is_string($unitDefinition['description'])
    ? $unitDefinition['description']
    : '';

$instructions = normalizeTextItems($unitDefinition['instructions'] ?? null);

$vtSections = [
    'goal' => 'Ziel',
    'self_monitoring' => 'Selbstbeobachtung',
    'trigger_context' => 'Auslöser und Kontext',
    'coping_exercise' => 'Coping-Übung',
    'transfer_task' => 'Transferaufgabe',
    'reflection' => 'Reflexion',
];

$questions = isset($unitDefinition['questions']) && is_array($unitDefinition['questions'])
    ? $unitDefinition['questions']
    : [];

$globalOptions = isset($unitDefinition['options']) && is_array($unitDefinition['options'])
    ? $unitDefinition['options']
    : [];

$prevUrl = null;
if ($activeUnitIndex > 0) {
    $prevUrl = 'process.php?process=' . urlencode($canonicalProcessId) . '&unit=' . urlencode($unitRefs[$activeUnitIndex - 1]['id']);
}

$nextUrl = null;
if ($activeUnitIndex < count($unitRefs) - 1) {
    $nextUrl = 'process.php?process=' . urlencode($canonicalProcessId) . '&unit=' . urlencode($unitRefs[$activeUnitIndex + 1]['id']);
}

$pageTitle = 'Diagnostischer Prozess';
include 'includes/header.php';
?>

<main class="process-page" aria-labelledby="process-title" data-process-id="<?php echo htmlspecialchars($canonicalProcessId); ?>" data-unit-id="<?php echo htmlspecialchars($activeUnitId); ?>">
  <header class="process-header">
    <p class="process-overline">Diagnostischer Prozess: <?php echo htmlspecialchars(strtoupper($canonicalProcessId)); ?></p>
    <h1 id="process-title"><?php echo htmlspecialchars($processTitle); ?></h1>
    <p><?php echo htmlspecialchars($processDescription); ?></p>
    <p><strong>Einheit:</strong> <?php echo ($activeUnitIndex + 1); ?> / <?php echo count($unitRefs); ?></p>
  </header>

  <section class="process-unit" aria-labelledby="unit-title">
    <header>
      <h2 id="unit-title"><?php echo htmlspecialchars($unitTitle); ?></h2>
      <?php if ($unitDescription !== ''): ?>
        <p><?php echo htmlspecialchars($unitDescription); ?></p>
      <?php endif; ?>
    </header>

    <article aria-labelledby="unit-instructions-title">
      <h3 id="unit-instructions-title">Instruktionen</h3>
      <?php if (!empty($instructions)): ?>
        <ul>
          <?php foreach ($instructions as $instruction): ?>
            <li><?php echo htmlspecialchars($instruction); ?></li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p>Nutze für diese Einheit eine kurze, ruhige Sequenz (ca. 10–15 Minuten). Notiere konkret beobachtbare Signale (z. B. Situation, Zeitpunkt, Handlung, Ergebnis) und formuliere mindestens einen nächsten, machbaren Schritt.</p>
      <?php endif; ?>
    </article>

    <?php foreach ($vtSections as $vtFieldKey => $vtFieldLabel): ?>
      <?php $vtItems = normalizeTextItems($unitDefinition[$vtFieldKey] ?? null); ?>
      <?php if (!empty($vtItems)): ?>
        <article aria-labelledby="unit-<?php echo htmlspecialchars($vtFieldKey); ?>-title">
          <h3 id="unit-<?php echo htmlspecialchars($vtFieldKey); ?>-title"><?php echo htmlspecialchars($vtFieldLabel); ?></h3>
          <ul>
            <?php foreach ($vtItems as $vtItem): ?>
              <li><?php echo htmlspecialchars($vtItem); ?></li>
            <?php endforeach; ?>
          </ul>
        </article>
      <?php endif; ?>
    <?php endforeach; ?>

    <form class="process-questions" method="post" action="#" aria-labelledby="questions-title">
      <h3 id="questions-title">Fragen</h3>

      <?php if (empty($questions)): ?>
        <p>Für diese Einheit sind noch keine Fragen hinterlegt. Du kannst stattdessen eine konkrete Alltagssituation wählen, drei beobachtbare Merkmale notieren und einen kleinen nächsten Handlungsschritt festlegen.</p>
      <?php else: ?>
        <?php foreach ($questions as $index => $question): ?>
          <?php
          $questionText = '';
          $questionId = 'q' . ($index + 1);
          $questionOptions = $globalOptions;

          if (is_array($question)) {
              if (isset($question['id']) && is_string($question['id']) && trim($question['id']) !== '') {
                  $questionId = preg_replace('/[^a-zA-Z0-9_-]/', '-', $question['id']) ?: $questionId;
              }

              if (isset($question['text']) && is_string($question['text'])) {
                  $questionText = $question['text'];
              } elseif (isset($question['title']) && is_string($question['title'])) {
                  $questionText = $question['title'];
              }

              if (isset($question['options']) && is_array($question['options'])) {
                  $questionOptions = $question['options'];
              }
          } elseif (is_string($question)) {
              $questionText = $question;
          }

          if ($questionText === '') {
              $questionText = 'Frage ' . ($index + 1);
          }
          ?>
          <fieldset>
            <legend><?php echo htmlspecialchars(($index + 1) . '. ' . $questionText); ?></legend>

            <?php if (is_array($questionOptions) && !empty($questionOptions)): ?>
              <?php foreach ($questionOptions as $optionIndex => $option): ?>
                <?php
                $optionLabel = '';
                $optionValue = (string) $optionIndex;

                if (is_array($option)) {
                    if (isset($option['label']) && is_string($option['label'])) {
                        $optionLabel = $option['label'];
                    } elseif (isset($option['text']) && is_string($option['text'])) {
                        $optionLabel = $option['text'];
                    }

                    if (isset($option['value']) && (is_string($option['value']) || is_int($option['value']) || is_float($option['value']))) {
                        $optionValue = (string) $option['value'];
                    }
                } elseif (is_string($option)) {
                    $optionLabel = $option;
                    $optionValue = $option;
                }

                if ($optionLabel === '') {
                    $optionLabel = 'Option ' . ($optionIndex + 1);
                }

                $inputId = $questionId . '-opt-' . ($optionIndex + 1);
                ?>
                <div>
                  <input type="radio" id="<?php echo htmlspecialchars($inputId); ?>" name="answers[<?php echo htmlspecialchars($questionId); ?>]" value="<?php echo htmlspecialchars($optionValue); ?>">
                  <label for="<?php echo htmlspecialchars($inputId); ?>"><?php echo htmlspecialchars($optionLabel); ?></label>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p>Für diese Frage sind noch keine Antwortoptionen hinterlegt. Formuliere eine kurze eigene Beobachtung mit Situation, Verhalten und direkt sichtbarem Ergebnis.</p>
            <?php endif; ?>
          </fieldset>
        <?php endforeach; ?>
      <?php endif; ?>
    </form>
  </section>

  <nav class="process-navigation" aria-label="Unit-Navigation">
    <?php if ($prevUrl !== null): ?>
      <a href="<?php echo htmlspecialchars($prevUrl); ?>">&larr; Zurück</a>
    <?php else: ?>
      <a href="diagnostics.php">&larr; Zurück zur Modulübersicht</a>
    <?php endif; ?>

    <?php if ($nextUrl !== null): ?>
      <a href="<?php echo htmlspecialchars($nextUrl); ?>">Weiter &rarr;</a>
    <?php else: ?>
      <a href="diagnostics.php#module-selector" data-process-complete="true">Abschließen</a>
    <?php endif; ?>
  </nav>
</main>

<?php include 'includes/footer.php'; ?>
