<?php
/**
 * JSON-driven process renderer for diagnostics units.
 *
 * URL params:
 * - process (required): canonical slug oder Legacy-Alias (z. B. ass, adhs)
 * - unit (optional): active unit identifier from process definition
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/process-repository.php';
require_once __DIR__ . '/includes/admin-auth.php';

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

$canonicalProcessId = '';
if ($processInput !== '') {
    if (isset($aliases[$processInput]) && is_string($aliases[$processInput])) {
        $canonicalProcessId = $aliases[$processInput];
    } elseif (isset($areas[$processInput]) && is_array($areas[$processInput])) {
        $canonicalProcessId = $processInput;
    }
}

/**
 * @param array<int, array{id: string, handle: string}> $unitRefs
 */
function renderError(string $message, array $unitRefs = []): void
{
    $pageTitle = 'Diagnostischer Prozess';
    include 'includes/header.php';
    ?>
    <section class="process-page" aria-labelledby="process-error-title">
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
    </section>
    <?php
    include 'includes/footer.php';
    exit;
}

/**
 * @param array<int, array{id: string, handle: string}> $unitRefs
 * @return array{unit: string, answers: array<string, string|array<int, string>>, updatedAt: int, completed: bool}|null
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
            if (!is_string($questionId)) {
                continue;
            }

            $cleanQuestionId = trim($questionId);
            if ($cleanQuestionId === '') {
                continue;
            }

            if (is_array($value)) {
                $listValues = [];
                foreach ($value as $entry) {
                    if (is_string($entry) || is_int($entry) || is_float($entry) || is_bool($entry)) {
                        $listValues[] = (string) $entry;
                    }
                }
                if (!empty($listValues)) {
                    $answers[$cleanQuestionId] = array_values(array_unique($listValues));
                }
                continue;
            }

            if (is_string($value) || is_int($value) || is_float($value) || is_bool($value)) {
                $answers[$cleanQuestionId] = (string) $value;
            }
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

// Inline-Optionen sind auf deutschsprachige, tendenziell längere Antworttexte ausgelegt:
// deshalb entscheidet primär die Anzahl der Optionen (statt Durchschnittslänge), ergänzt um ein Label-Maximum.
function shouldUseInlineOptionLayout(array $options): bool
{
    $optionCount = count($options);
    if ($optionCount < 2 || $optionCount > 5) {
        return false;
    }

    $maxLabelLength = 0;
    foreach ($options as $option) {
        $label = '';
        if (is_array($option)) {
            if (isset($option['label']) && is_string($option['label'])) {
                $label = trim($option['label']);
            } elseif (isset($option['text']) && is_string($option['text'])) {
                $label = trim($option['text']);
            }
        } elseif (is_string($option)) {
            $label = trim($option);
        }

        if ($label === '') {
            continue;
        }

        $labelLength = mb_strlen($label);
        if ($labelLength > $maxLabelLength) {
            $maxLabelLength = $labelLength;
        }
    }

    if ($maxLabelLength === 0) {
        return false;
    }

    return $maxLabelLength <= 35;
}

/**
 * @param mixed $question
 * @return array{
 *   id: string,
 *   text: string,
 *   options: array<int, mixed>,
 *   control: string,
 *   multiple: bool,
 *   min: int,
 *   max: int,
 *   step: int,
 *   minLabel: string,
 *   maxLabel: string,
 *   hint: string,
 *   placeholder: string
 * }
 */
function buildQuestionViewModel($question, int $index, array $globalOptions): array
{
    $questionText = '';
    $questionId = 'q' . ($index + 1);
    $questionOptions = $globalOptions;
    $control = 'radio';
    $multiple = false;
    $min = 1;
    $max = 5;
    $step = 1;
    $minLabel = 'niedrig';
    $maxLabel = 'hoch';
    $hint = '';
    $placeholder = '';

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

        if (isset($question['control']) && is_string($question['control'])) {
            $control = strtolower(trim($question['control']));
        } elseif (isset($question['answerType']) && is_string($question['answerType'])) {
            $control = strtolower(trim($question['answerType']));
        } elseif (isset($question['type']) && is_string($question['type'])) {
            $control = strtolower(trim($question['type']));
        }

        $multiple = !empty($question['multiple']);
        if ($multiple && ($control === 'radio' || $control === 'likert')) {
            $control = 'checkbox';
        }

        if (isset($question['min']) && (is_int($question['min']) || is_float($question['min']))) {
            $min = (int) $question['min'];
        }
        if (isset($question['max']) && (is_int($question['max']) || is_float($question['max']))) {
            $max = (int) $question['max'];
        }
        if (isset($question['step']) && (is_int($question['step']) || is_float($question['step'])) && (int) $question['step'] > 0) {
            $step = (int) $question['step'];
        }

        if (isset($question['minLabel']) && is_string($question['minLabel'])) {
            $minLabel = trim($question['minLabel']);
        }
        if (isset($question['maxLabel']) && is_string($question['maxLabel'])) {
            $maxLabel = trim($question['maxLabel']);
        }
        if (isset($question['hint']) && is_string($question['hint'])) {
            $hint = trim($question['hint']);
        }
        if (isset($question['placeholder']) && is_string($question['placeholder'])) {
            $placeholder = trim($question['placeholder']);
        }
    } elseif (is_string($question)) {
        $questionText = $question;
    }

    if ($questionText === '') {
        $questionText = 'Frage ' . ($index + 1);
    }

    if ($control === 'scale' || $control === 'slider') {
        $control = 'range';
    } elseif ($control === 'open' || $control === 'free_text' || $control === 'memo') {
        $control = 'textarea';
    } elseif ($control === 'multi_select') {
        $control = 'checkbox';
        $multiple = true;
    } elseif (!in_array($control, ['radio', 'likert', 'checkbox', 'select', 'text', 'textarea', 'range'], true)) {
        $control = !empty($questionOptions) ? 'radio' : 'textarea';
    }

    if ($min >= $max) {
        $min = 1;
        $max = 5;
    }

    return [
        'id' => $questionId,
        'text' => $questionText,
        'options' => is_array($questionOptions) ? $questionOptions : [],
        'control' => $control,
        'multiple' => $multiple,
        'min' => $min,
        'max' => $max,
        'step' => $step,
        'minLabel' => $minLabel !== '' ? $minLabel : 'niedrig',
        'maxLabel' => $maxLabel !== '' ? $maxLabel : 'hoch',
        'hint' => $hint,
        'placeholder' => $placeholder,
    ];
}

if ($canonicalProcessId === '' || !isset($areas[$canonicalProcessId]) || !is_array($areas[$canonicalProcessId])) {
    renderError('Bitte wähle einen gültigen Prozess über den Parameter "process" (z. B. ass, dyslexie-lrs oder dld).');
}

$processMeta = $areas[$canonicalProcessId];
$definitionHandle = isset($processMeta['definitionHandle']) && is_string($processMeta['definitionHandle'])
    ? strtolower(trim($processMeta['definitionHandle']))
    : '';

if ($definitionHandle === '') {
    renderError('Für den Bereich "' . $canonicalProcessId . '" wurde kein Prozess-Handle hinterlegt.');
}

[$processDefinition, $processError] = ndRepoLoadProcessDefinition($definitionHandle);
if ($processDefinition === null) {
    renderError('Die Prozessdefinition mit Handle "' . $definitionHandle . '" konnte nicht geladen werden: ' . $processError);
}

[$unitRefs, $phaseError] = ndRepoGetInstrumentRefs($processDefinition);
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
[$unitDefinition, $instrumentValidationError] = ndRepoLoadAndValidateInstrument($activeUnitRef['handle']);
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
$completeUrl = 'result.php?process=' . urlencode($canonicalProcessId);
$adminAuthenticated = isAdminAuthenticated();
$adminCsrfToken = $adminAuthenticated ? adminGetCsrfToken() : '';

$pageTitle = 'Diagnostischer Prozess';
include 'includes/header.php';
?>

<section class="process-page" aria-labelledby="process-title" data-process-id="<?php echo htmlspecialchars($canonicalProcessId); ?>" data-unit-id="<?php echo htmlspecialchars($activeUnitId); ?>">
  <div class="process-shell">
  <header class="process-header">
    <p class="process-overline">Diagnostischer Prozess: <?php echo htmlspecialchars(strtoupper($canonicalProcessId)); ?></p>
    <h1 id="process-title"><?php echo htmlspecialchars($processTitle); ?></h1>
    <p><?php echo htmlspecialchars($processDescription); ?></p>
    <p class="process-unit-counter"><strong>Einheit:</strong> <?php echo ($activeUnitIndex + 1); ?> / <?php echo count($unitRefs); ?></p>
    <div class="process-progress" role="progressbar" aria-label="Fortschritt im Prozess" aria-valuemin="1" aria-valuemax="<?php echo count($unitRefs); ?>" aria-valuenow="<?php echo ($activeUnitIndex + 1); ?>">
      <div class="process-progress-track">
        <span class="process-progress-fill" style="width: <?php echo (int) round((($activeUnitIndex + 1) / max(count($unitRefs), 1)) * 100); ?>%;"></span>
      </div>
    </div>
  </header>

  <section class="process-unit" aria-labelledby="unit-title">
    <header>
      <h2 id="unit-title"><?php echo htmlspecialchars($unitTitle); ?></h2>
      <?php if ($unitDescription !== ''): ?>
        <p><?php echo htmlspecialchars($unitDescription); ?></p>
      <?php endif; ?>
    </header>

    <article class="process-block process-block-intro" aria-labelledby="unit-instructions-title">
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
        <article class="process-block" aria-labelledby="unit-<?php echo htmlspecialchars($vtFieldKey); ?>-title">
          <h3 id="unit-<?php echo htmlspecialchars($vtFieldKey); ?>-title"><?php echo htmlspecialchars($vtFieldLabel); ?></h3>
          <ul>
            <?php foreach ($vtItems as $vtItem): ?>
              <li><?php echo htmlspecialchars($vtItem); ?></li>
            <?php endforeach; ?>
          </ul>
        </article>
      <?php endif; ?>
    <?php endforeach; ?>

    <form class="process-questions process-block" method="post" action="#" aria-labelledby="questions-title" novalidate>
      <h3 id="questions-title">Eingaben</h3>

      <?php if (empty($questions)): ?>
        <p>Für diese Einheit sind noch keine Fragen hinterlegt. Du kannst stattdessen eine konkrete Alltagssituation wählen, drei beobachtbare Merkmale notieren und einen kleinen nächsten Handlungsschritt festlegen.</p>
      <?php else: ?>
        <?php foreach ($questions as $index => $question): ?>
          <?php
          $vm = buildQuestionViewModel($question, $index, $globalOptions);
          $questionText = $vm['text'];
          $questionId = $vm['id'];
          $questionOptions = $vm['options'];
          $controlType = $vm['control'];
          $questionName = $vm['multiple'] ? 'answers[' . $questionId . '][]' : 'answers[' . $questionId . ']';
          $hintId = $vm['hint'] !== '' ? $questionId . '-hint' : '';
          $useInlineOptions = in_array($controlType, ['radio', 'checkbox', 'likert'], true)
            && is_array($questionOptions)
            && shouldUseInlineOptionLayout($questionOptions);
          ?>
          <fieldset
            class="question-card question-control-<?php echo htmlspecialchars($controlType); ?>"
            data-question-item="true"
            data-question-position="<?php echo ($index + 1); ?>"
            data-question-total="<?php echo count($questions); ?>"
            data-control-type="<?php echo htmlspecialchars($controlType); ?>"
            data-auto-advance="<?php echo in_array($controlType, ['radio', 'likert', 'select', 'range'], true) ? 'true' : 'false'; ?>"
            data-question-id="<?php echo htmlspecialchars($questionId); ?>"
          >
            <legend><?php echo htmlspecialchars(($index + 1) . '. ' . $questionText); ?></legend>
            <?php if ($vm['hint'] !== ''): ?>
              <p class="question-hint" id="<?php echo htmlspecialchars($hintId); ?>"><?php echo htmlspecialchars($vm['hint']); ?></p>
            <?php endif; ?>

            <?php if (in_array($controlType, ['radio', 'checkbox', 'likert'], true) && is_array($questionOptions) && !empty($questionOptions)): ?>
              <?php $optionCount = is_array($questionOptions) ? count($questionOptions) : 0; ?>
              <div class="question-options<?php echo $controlType === 'likert' ? ' likert-grid' : ''; ?><?php echo $useInlineOptions ? ' question-options--inline' : ''; ?><?php echo $controlType === 'checkbox' ? ' question-options--checkbox' : ''; ?> question-options--count-<?php echo $optionCount; ?>">
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
                <div class="question-option">
                  <label class="question-option-label" for="<?php echo htmlspecialchars($inputId); ?>">
                    <input
                      type="<?php echo $controlType === 'checkbox' ? 'checkbox' : 'radio'; ?>"
                      id="<?php echo htmlspecialchars($inputId); ?>"
                      name="<?php echo htmlspecialchars($questionName); ?>"
                      value="<?php echo htmlspecialchars($optionValue); ?>"
                      <?php if ($hintId !== ''): ?>
                      aria-describedby="<?php echo htmlspecialchars($hintId); ?>"
                      <?php endif; ?>
                    >
                    <span><?php echo htmlspecialchars($optionLabel); ?></span>
                  </label>
                </div>
              <?php endforeach; ?>
              </div>
            <?php elseif ($controlType === 'select' && is_array($questionOptions) && !empty($questionOptions)): ?>
              <label class="sr-only" for="<?php echo htmlspecialchars($questionId); ?>-select">Antwort auswählen</label>
              <select id="<?php echo htmlspecialchars($questionId); ?>-select" name="<?php echo htmlspecialchars($questionName); ?>">
                <option value="">Bitte auswählen</option>
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
                  ?>
                  <option value="<?php echo htmlspecialchars($optionValue); ?>"><?php echo htmlspecialchars($optionLabel); ?></option>
                <?php endforeach; ?>
              </select>
            <?php elseif ($controlType === 'range'): ?>
              <div class="range-control">
                <div class="range-labels">
                  <span><?php echo htmlspecialchars($vm['minLabel']); ?></span>
                  <span><?php echo htmlspecialchars($vm['maxLabel']); ?></span>
                </div>
                <input
                  type="range"
                  id="<?php echo htmlspecialchars($questionId); ?>-range"
                  name="<?php echo htmlspecialchars($questionName); ?>"
                  min="<?php echo htmlspecialchars((string) $vm['min']); ?>"
                  max="<?php echo htmlspecialchars((string) $vm['max']); ?>"
                  step="<?php echo htmlspecialchars((string) $vm['step']); ?>"
                  value="<?php echo htmlspecialchars((string) $vm['min']); ?>"
                >
              </div>
            <?php elseif ($controlType === 'text'): ?>
              <label class="sr-only" for="<?php echo htmlspecialchars($questionId); ?>-text">Freitextantwort</label>
              <input
                type="text"
                id="<?php echo htmlspecialchars($questionId); ?>-text"
                name="<?php echo htmlspecialchars($questionName); ?>"
                placeholder="<?php echo htmlspecialchars($vm['placeholder'] !== '' ? $vm['placeholder'] : 'Kurze Antwort eingeben'); ?>"
              >
            <?php elseif ($controlType === 'textarea' || empty($questionOptions)): ?>
              <label class="sr-only" for="<?php echo htmlspecialchars($questionId); ?>-textarea">Freitextantwort</label>
              <textarea
                id="<?php echo htmlspecialchars($questionId); ?>-textarea"
                name="<?php echo htmlspecialchars($questionName); ?>"
                rows="4"
                placeholder="<?php echo htmlspecialchars($vm['placeholder'] !== '' ? $vm['placeholder'] : 'Beschreibe kurz Situation, Gedanken, Gefühl und nächsten machbaren Schritt.'); ?>"
              ></textarea>
            <?php else: ?>
              <p>Für diese Frage sind noch keine Antwortoptionen hinterlegt. Formuliere eine kurze eigene Beobachtung mit Situation, Verhalten und direkt sichtbarem Ergebnis.</p>
            <?php endif; ?>
          </fieldset>
        <?php endforeach; ?>
        <p class="question-flow-hint" aria-live="polite">Tipp: Bei Auswahl springt die Ansicht automatisch sanft zum nächsten Schritt.</p>
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
      <a href="<?php echo htmlspecialchars($completeUrl); ?>" data-process-complete="true">Abschließen</a>
    <?php endif; ?>
  </nav>

  <?php if ($adminAuthenticated): ?>
    <section
      class="process-admin"
      aria-labelledby="process-admin-title"
      data-process-admin="true"
      data-process-id="<?php echo htmlspecialchars($canonicalProcessId); ?>"
      data-csrf-token="<?php echo htmlspecialchars($adminCsrfToken); ?>"
      data-endpoint="admin/process-files.php"
    >
      <header>
        <h2 id="process-admin-title">Admin: Prozess-Dateien verwalten</h2>
        <p>Hier kannst du Units als JSON hochladen, per Drag-and-drop neu sortieren oder entfernen. Änderungen werden sofort in der genutzten Prozessdefinition gespeichert.</p>
      </header>

      <form class="process-admin-upload" data-admin-upload-form="true" enctype="multipart/form-data">
        <div>
          <label for="processAdminUnitId">Unit-ID</label>
          <input id="processAdminUnitId" name="unit_id" type="text" pattern="[a-z0-9_-]+" required placeholder="z. B. unit-5">
        </div>
        <div>
          <label for="processAdminFile">JSON-Datei</label>
          <input id="processAdminFile" name="file" type="file" accept="application/json,.json" required>
        </div>
        <button type="submit">Unit hochladen</button>
      </form>

      <div class="process-admin-dropzone" data-admin-dropzone="true" tabindex="0" role="button" aria-label="JSON-Datei hier ablegen">
        JSON-Datei hierher ziehen oder oben auswählen.
      </div>

      <div class="process-admin-order">
        <h3>Reihenfolge (Drag-and-drop)</h3>
        <ul class="process-admin-list" data-admin-sortable="true">
          <?php foreach ($unitRefs as $unitRef): ?>
            <li draggable="true" data-unit-id="<?php echo htmlspecialchars($unitRef['id']); ?>">
              <span class="process-admin-drag-handle" aria-hidden="true">↕</span>
              <span><strong><?php echo htmlspecialchars($unitRef['id']); ?></strong> · <?php echo htmlspecialchars($unitRef['handle']); ?></span>
              <button type="button" data-admin-delete="<?php echo htmlspecialchars($unitRef['id']); ?>">Löschen</button>
            </li>
          <?php endforeach; ?>
        </ul>
        <button type="button" data-admin-save-order="true">Reihenfolge speichern</button>
      </div>

      <p class="process-admin-status" data-admin-status="true" aria-live="polite"></p>
    </section>
  <?php endif; ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
