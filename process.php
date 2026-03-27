<?php
/**
 * JSON-driven process renderer for diagnostics units.
 *
 * URL params:
 * - process (required): e.g. aq, adhs, dyslexia, dysgraphia, dyskalkulie, dyspraxie, tic, dld
 * - unit (optional): active unit identifier from process definition
 */

declare(strict_types=1);

$allowedProcesses = ['aq', 'adhs', 'dyslexia', 'dysgraphia', 'dyskalkulie', 'dyspraxie', 'tic', 'dld'];

$processId = isset($_GET['process']) ? strtolower(trim((string) $_GET['process'])) : '';
$requestedUnitId = isset($_GET['unit']) ? strtolower(trim((string) $_GET['unit'])) : '';

if (!preg_match('/^[a-z0-9_-]+$/', $processId)) {
    $processId = '';
}
if (!preg_match('/^[a-z0-9_-]*$/', $requestedUnitId)) {
    $requestedUnitId = '';
}

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
 * @return array<int, array{id: string, file: string}>
 */
function getUnitRefs(array $processDefinition): array
{
    $unitRefs = [];

    if (!isset($processDefinition['units']) || !is_array($processDefinition['units'])) {
        return $unitRefs;
    }

    foreach ($processDefinition['units'] as $unitEntry) {
        if (is_string($unitEntry)) {
            $unitId = trim($unitEntry);
            if ($unitId !== '') {
                $unitRefs[] = [
                    'id' => $unitId,
                    'file' => $unitId . '.json',
                ];
            }
            continue;
        }

        if (!is_array($unitEntry)) {
            continue;
        }

        $unitId = isset($unitEntry['id']) && is_string($unitEntry['id']) ? trim($unitEntry['id']) : '';
        $unitFile = isset($unitEntry['file']) && is_string($unitEntry['file']) ? trim($unitEntry['file']) : '';

        if ($unitId === '' && $unitFile !== '') {
            $unitId = pathinfo($unitFile, PATHINFO_FILENAME);
        }

        if ($unitId === '') {
            continue;
        }

        if ($unitFile === '') {
            $unitFile = $unitId . '.json';
        }

        $unitRefs[] = [
            'id' => $unitId,
            'file' => $unitFile,
        ];
    }

    return $unitRefs;
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

if ($processId === '' || !in_array($processId, $allowedProcesses, true)) {
    renderError('Bitte wähle einen gültigen Prozess über den Parameter "process" (z. B. aq oder dld).');
}

$processPath = __DIR__ . '/data/processes/' . $processId . '.json';
[$processDefinition, $processError] = loadJsonFile($processPath);
if ($processDefinition === null) {
    renderError('Die Prozessdatei "data/processes/' . $processId . '.json" konnte nicht geladen werden: ' . $processError);
}

$unitRefs = getUnitRefs($processDefinition);
if (empty($unitRefs)) {
    renderError('Die Prozessdefinition enthält keine Units. Bitte ergänze im JSON ein Feld "units" mit Unit-IDs.');
}

$resumeState = readResumeStateFromCookie($processId, $unitRefs);
if (isset($_COOKIE['neurodiag_process_' . $processId]) && $resumeState === null) {
    clearResumeCookie($processId);
}

if ($requestedUnitId === '' && $resumeState !== null && !$resumeState['completed']) {
    header('Location: process.php?process=' . urlencode($processId) . '&unit=' . urlencode($resumeState['unit']));
    exit;
}

$unitIds = array_map(static fn(array $entry): string => $entry['id'], $unitRefs);
$activeUnitId = $requestedUnitId !== '' ? $requestedUnitId : $unitIds[0];
$activeUnitIndex = array_search($activeUnitId, $unitIds, true);

if ($activeUnitIndex === false) {
    renderError('Die angeforderte Unit "' . $activeUnitId . '" ist in diesem Prozess nicht enthalten.', $unitRefs);
}

$activeUnitRef = $unitRefs[$activeUnitIndex];
$unitFileName = basename($activeUnitRef['file']);
$unitPath = __DIR__ . '/data/units/' . $unitFileName;

[$unitDefinition, $unitError] = loadJsonFile($unitPath);
if ($unitDefinition === null) {
    renderError('Die Unit-Datei "data/units/' . $unitFileName . '" konnte nicht geladen werden: ' . $unitError, $unitRefs);
}

$processTitle = isset($processDefinition['title']) && is_string($processDefinition['title'])
    ? $processDefinition['title']
    : strtoupper($processId) . ' Prozess';

$processDescription = isset($processDefinition['description']) && is_string($processDefinition['description'])
    ? $processDefinition['description']
    : 'Strukturierter diagnostischer Ablauf auf Basis von JSON-Definitionen.';

$unitTitle = isset($unitDefinition['title']) && is_string($unitDefinition['title'])
    ? $unitDefinition['title']
    : 'Unit ' . ($activeUnitIndex + 1);

$unitDescription = isset($unitDefinition['description']) && is_string($unitDefinition['description'])
    ? $unitDefinition['description']
    : '';

$instructions = [];
if (isset($unitDefinition['instructions']) && is_array($unitDefinition['instructions'])) {
    foreach ($unitDefinition['instructions'] as $instruction) {
        if (is_string($instruction) && trim($instruction) !== '') {
            $instructions[] = $instruction;
        }
    }
}

$questions = isset($unitDefinition['questions']) && is_array($unitDefinition['questions'])
    ? $unitDefinition['questions']
    : [];

$globalOptions = isset($unitDefinition['options']) && is_array($unitDefinition['options'])
    ? $unitDefinition['options']
    : [];

$prevUrl = null;
if ($activeUnitIndex > 0) {
    $prevUrl = 'process.php?process=' . urlencode($processId) . '&unit=' . urlencode($unitRefs[$activeUnitIndex - 1]['id']);
}

$nextUrl = null;
if ($activeUnitIndex < count($unitRefs) - 1) {
    $nextUrl = 'process.php?process=' . urlencode($processId) . '&unit=' . urlencode($unitRefs[$activeUnitIndex + 1]['id']);
}

$pageTitle = 'Diagnostischer Prozess';
include 'includes/header.php';
?>

<main class="process-page" aria-labelledby="process-title" data-process-id="<?php echo htmlspecialchars($processId); ?>" data-unit-id="<?php echo htmlspecialchars($activeUnitId); ?>">
  <header class="process-header">
    <p class="process-overline">Diagnostischer Prozess: <?php echo htmlspecialchars(strtoupper($processId)); ?></p>
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
        <p>Bitte beantworte die Fragen in einem ruhigen Moment. Beobachte Gedanken, Gefühle und Verhalten achtsam und ohne Selbstabwertung.</p>
      <?php endif; ?>
    </article>

    <form class="process-questions" method="post" action="#" aria-labelledby="questions-title">
      <h3 id="questions-title">Fragen</h3>

      <?php if (empty($questions)): ?>
        <p>Für diese Unit sind aktuell keine Fragen hinterlegt.</p>
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
              <p>Für diese Frage sind keine Antwortoptionen definiert.</p>
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
      <a href="diagnostics.php?process=<?php echo urlencode($processId); ?>" data-process-complete="true">Abschließen</a>
    <?php endif; ?>
  </nav>
</main>

<?php include 'includes/footer.php'; ?>
