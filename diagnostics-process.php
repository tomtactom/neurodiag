<?php
/**
 * Master Diagnostic Process Framework
 * 5-Phase System für alle 8 neurodiversen Module
 * 
 * Phases:
 * 1. Intro - Hero + Kontext
 * 2. Screening - Optional Quick-Check (5-6 Fragen)
 * 3. Main - Haupttest [Placeholder für echte Tests]
 * 4. Reflection - Persönliche Notizen & Kontextualisierung
 * 5. Results - Grafische Auswertung + PDF-Report
 */

// Load module configuration
$moduleConfig = json_decode(file_get_contents('data/module-config.json'), true);

/**
 * Parse process resume cookie defensively.
 *
 * Expected payload:
 * {
 *   "processId": "aq-test",
 *   "currentUnit": 3,
 *   "answers": { "screening": {}, "main": {}, "reflection": {} },
 *   "updatedAt": 1710000000000,
 *   "completed": false
 * }
 */
function readResumeCookie(string $module, int $maxPhase = 5): ?array
{
    $cookieKey = 'neurodiag_process_' . $module;
    if (!isset($_COOKIE[$cookieKey])) {
        return null;
    }

    $decoded = json_decode(urldecode($_COOKIE[$cookieKey]), true);
    if (!is_array($decoded)) {
        return null;
    }

    $unit = $decoded['currentUnit'] ?? null;
    $answers = $decoded['answers'] ?? null;
    $updatedAt = $decoded['updatedAt'] ?? null;
    $completed = $decoded['completed'] ?? false;

    if (!is_int($unit) || $unit < 1 || $unit > $maxPhase) {
        return null;
    }
    if (!is_array($answers)) {
        return null;
    }
    foreach (['screening', 'main', 'reflection'] as $answerGroup) {
        if (isset($answers[$answerGroup]) && !is_array($answers[$answerGroup])) {
            return null;
        }
    }
    if (!is_int($updatedAt) && !is_float($updatedAt)) {
        return null;
    }
    if (!is_bool($completed)) {
        return null;
    }

    return [
        'currentUnit' => $unit,
        'answers' => $answers,
        'updatedAt' => (int) $updatedAt,
        'completed' => $completed
    ];
}

// Get module and phase from URL
$module = isset($_GET['module']) ? $_GET['module'] : 'aq-test';
$phaseFromRequest = $_GET['phase'] ?? $_GET['unit'] ?? null;
$phase = $phaseFromRequest !== null ? (int) $phaseFromRequest : 1;

// Validate module exists
if (!isset($moduleConfig['modules'][$module])) {
    header('Location: diagnostics.php');
    exit;
}

$moduleData = $moduleConfig['modules'][$module];
$pageTitle = $moduleData['name'] . ' - Diagnostik';

$resumeData = readResumeCookie($module, 5);
$requestHasUnit = isset($_GET['phase']) || isset($_GET['unit']);

// Resume only when request does not explicitly define current unit/phase.
if (!$requestHasUnit && $resumeData !== null) {
    $phase = $resumeData['currentUnit'];
}

if ($phase < 1 || $phase > 5) {
    $phase = 1;
}

// Session initialization
session_start();
if (!isset($_SESSION['diagnostics'])) {
    $_SESSION['diagnostics'] = [];
}
if (!isset($_SESSION['diagnostics'][$module])) {
    $_SESSION['diagnostics'][$module] = [
        'startTime' => time(),
        'phase' => 1,
        'screening' => [],
        'main' => [],
        'reflection' => [],
        'completed' => false
    ];
}

// Update current phase
$_SESSION['diagnostics'][$module]['phase'] = $phase;

include 'includes/header.php';
?>

<!-- Phase Navigation Indicator -->
<div class="diagnostic-phases-indicator">
  <div class="phase-steps">
    <div class="phase-step" data-phase="1" style="--step-color: <?php echo $moduleData['color']; ?>">
      <span class="step-number">1</span>
      <span class="step-label">Intro</span>
    </div>
    <div class="phase-step" data-phase="2" style="--step-color: <?php echo $moduleData['color']; ?>">
      <span class="step-number">2</span>
      <span class="step-label">Screening</span>
    </div>
    <div class="phase-step" data-phase="3" style="--step-color: <?php echo $moduleData['color']; ?>">
      <span class="step-number">3</span>
      <span class="step-label">Test</span>
    </div>
    <div class="phase-step" data-phase="4" style="--step-color: <?php echo $moduleData['color']; ?>">
      <span class="step-number">4</span>
      <span class="step-label">Reflexion</span>
    </div>
    <div class="phase-step" data-phase="5" style="--step-color: <?php echo $moduleData['color']; ?>">
      <span class="step-number">5</span>
      <span class="step-label">Ergebnisse</span>
    </div>
  </div>
  <div class="phase-progress">
    <div class="progress-bar">
      <div class="progress-fill" style="width: <?php echo ($phase / 5) * 100; ?>%; background-color: <?php echo $moduleData['color']; ?>;"></div>
    </div>
    <span class="progress-text">Phase <?php echo $phase; ?>/5</span>
  </div>
</div>

<!-- Main Content Container -->
<main class="diagnostic-container" data-module="<?php echo $module; ?>" data-phase="<?php echo $phase; ?>">

  <?php if ($phase === 1): 
  // PHASE 1: INTRO
  ?>
  <section class="diagnostic-phase phase-intro" id="phase-1">
    <div class="phase-header" style="border-color: <?php echo $moduleData['color']; ?>;">
      <div class="header-icon"><?php echo $moduleData['icon']; ?></div>
      <h1><?php echo htmlspecialchars($moduleData['intro']['title']); ?></h1>
      <p class="subtitle"><?php echo htmlspecialchars($moduleData['intro']['subtitle']); ?></p>
    </div>

    <div class="phase-content">
      <article class="intro-card">
        <h2>Über diese Diagnostik</h2>
        <p><?php echo htmlspecialchars($moduleData['intro']['text']); ?></p>
      </article>

      <div class="intro-info-grid">
        <div class="info-box">
          <h3>⏱️ Zeitaufwand</h3>
          <p><?php echo htmlspecialchars($moduleData['intro']['duration']); ?></p>
        </div>
        <div class="info-box">
          <h3>✓ Was du brauchst</h3>
          <ul>
            <?php foreach ($moduleData['intro']['requirements'] as $req): ?>
            <li><?php echo htmlspecialchars($req); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>

      <div class="intro-process-map">
        <h3>Ablauf dieser Diagnostik</h3>
        <div class="process-steps">
          <div class="process-step">
            <span class="step-num">2</span>
            <h4>Schnelle Orientierung</h4>
            <p><?php echo $moduleData['screening']['description']; ?></p>
          </div>
          <div class="process-step">
            <span class="step-num">3</span>
            <h4><?php echo htmlspecialchars($moduleData['main']['title']); ?></h4>
            <p><?php echo htmlspecialchars($moduleData['main']['description']); ?></p>
          </div>
          <div class="process-step">
            <span class="step-num">4</span>
            <h4>Reflexion & Notizen</h4>
            <p>Persönliche Kontextualisierung deiner Ergebnisse.</p>
          </div>
          <div class="process-step">
            <span class="step-num">5</span>
            <h4>Deine Ergebnisse</h4>
            <p>Grafische Auswertung, Bericht und nächste Schritte.</p>
          </div>
        </div>
      </div>

      <div class="intro-cta">
        <p class="consent-text">Mit dem Start erklärst du dich damit einverstanden, deine Daten während dieser Sitzung zu speichern.</p>
        <button class="btn btn-primary" data-next-phase="2">Los geht's! → Phase 2</button>
        <a href="diagnostics.php" class="btn btn-secondary">Zurück zur Übersicht</a>
      </div>
    </div>
  </section>

  <?php elseif ($phase === 2): 
  // PHASE 2: SCREENING
  ?>
  <section class="diagnostic-phase phase-screening" id="phase-2">
    <div class="phase-header" style="border-color: <?php echo $moduleData['color']; ?>;">
      <h2><?php echo htmlspecialchars($moduleData['screening']['title']); ?></h2>
      <p><?php echo htmlspecialchars($moduleData['screening']['description']); ?></p>
    </div>

    <form class="screening-form" id="screeningForm" data-module="<?php echo $module; ?>">
      <fieldset>
        <legend>Schnelle Orientierungsfragen (optional)</legend>
        
        <!-- Placeholder: 5-6 Screening Questions -->
        <div class="screening-questions">
          <div class="screening-item">
            <label for="screen-q1">
              <input type="radio" id="screen-q1" name="screening-1" value="yes" class="screening-input" data-question="1">
              <span class="question-text">Frage 1 – Trifft das auf dich zu?</span>
            </label>
          </div>
          <div class="screening-item">
            <label for="screen-q2">
              <input type="radio" id="screen-q2" name="screening-1" value="no" class="screening-input" data-question="1">
              <span class="question-text">Nicht wirklich</span>
            </label>
          </div>

          <div class="screening-item">
            <label for="screen-q3">
              <input type="radio" id="screen-q3" name="screening-2" value="yes" class="screening-input" data-question="2">
              <span class="question-text">Frage 2 – Erkennst du dich hier wieder?</span>
            </label>
          </div>
          <div class="screening-item">
            <label for="screen-q4">
              <input type="radio" id="screen-q4" name="screening-2" value="no" class="screening-input" data-question="2">
              <span class="question-text">Nicht wirklich</span>
            </label>
          </div>

          <p class="screening-note">💡 Diese Fragen sind optional und dienen nur deiner Selbst-Orientierung. Du kannst sie überspringen.</p>
        </div>
      </fieldset>
    </form>

    <div class="phase-navigation">
      <button class="btn btn-secondary" data-prev-phase="1">← Zurück zu Phase 1</button>
      <button class="btn btn-primary" data-next-phase="3">Zum Test → Phase 3</button>
    </div>
  </section>

  <?php elseif ($phase === 3): 
  // PHASE 3: MAIN TEST
  ?>
  <section class="diagnostic-phase phase-main-test" id="phase-3">
    <div class="phase-header" style="border-color: <?php echo $moduleData['color']; ?>;">
      <h2><?php echo htmlspecialchars($moduleData['main']['title']); ?></h2>
      <p><?php echo htmlspecialchars($moduleData['main']['description']); ?></p>
    </div>

    <div class="test-container">
      <div class="test-info-bar">
        <span class="test-format">Format: <?php echo htmlspecialchars($moduleData['main']['format']); ?></span>
        <span class="test-items"><?php echo htmlspecialchars($moduleData['main']['items']); ?> Fragen</span>
      </div>

      <form class="main-test-form" id="mainTestForm" data-module="<?php echo $module; ?>">
        <fieldset>
          <legend>Haupttest-Fragen</legend>

          <!-- Placeholder: Main Test Questions rendered via data/questionnaires/[module].json -->
          <div class="test-questions">
            <div class="test-question-group">
              <div class="question-number">1/<?php echo htmlspecialchars($moduleData['main']['items']); ?></div>
              <p class="question-text">Beispiel-Frage 1 – [Wird aus Datenbank gefüllt]</p>
              <div class="question-options">
                <label>
                  <input type="radio" name="test-1" value="0" class="test-input" data-question="1">
                  <span>Trifft überhaupt nicht zu</span>
                </label>
                <label>
                  <input type="radio" name="test-1" value="1" class="test-input" data-question="1">
                  <span>Trifft eher nicht zu</span>
                </label>
                <label>
                  <input type="radio" name="test-1" value="2" class="test-input" data-question="1">
                  <span>Trifft eher zu</span>
                </label>
                <label>
                  <input type="radio" name="test-1" value="3" class="test-input" data-question="1">
                  <span>Trifft vollständig zu</span>
                </label>
              </div>
            </div>

            <div class="test-question-group">
              <div class="question-number">2/<?php echo htmlspecialchars($moduleData['main']['items']); ?></div>
              <p class="question-text">Beispiel-Frage 2 – [Wird aus Datenbank gefüllt]</p>
              <div class="question-options">
                <label>
                  <input type="radio" name="test-2" value="0" class="test-input" data-question="2">
                  <span>Trifft überhaupt nicht zu</span>
                </label>
                <label>
                  <input type="radio" name="test-2" value="1" class="test-input" data-question="2">
                  <span>Trifft eher nicht zu</span>
                </label>
                <label>
                  <input type="radio" name="test-2" value="2" class="test-input" data-question="2">
                  <span>Trifft eher zu</span>
                </label>
                <label>
                  <input type="radio" name="test-2" value="3" class="test-input" data-question="2">
                  <span>Trifft vollständig zu</span>
                </label>
              </div>
            </div>

            <p class="test-note">... weitere Fragen werden hier eingefügt (dynamisch aus Datenbank)</p>
          </div>
        </fieldset>
      </form>

      <div class="test-pause-notice">
        <p>⏸️ Du kannst jederzeit pausieren. Deine Antworten werden automatisch gespeichert.</p>
      </div>
    </div>

    <div class="phase-navigation">
      <button class="btn btn-secondary" data-prev-phase="2">← Zurück zu Phase 2</button>
      <button class="btn btn-primary" data-next-phase="4">Zur Reflexion → Phase 4</button>
    </div>
  </section>

  <?php elseif ($phase === 4): 
  // PHASE 4: REFLECTION
  ?>
  <section class="diagnostic-phase phase-reflection" id="phase-4">
    <div class="phase-header" style="border-color: <?php echo $moduleData['color']; ?>;">
      <h2><?php echo htmlspecialchars($moduleData['reflection']['title']); ?></h2>
      <p>Persönliche Kontextualisierung deiner Antworten</p>
    </div>

    <form class="reflection-form" id="reflectionForm" data-module="<?php echo $module; ?>">
      <fieldset>
        <legend>Deine persönlichen Notizen</legend>

        <?php foreach ($moduleData['reflection']['prompts'] as $index => $prompt): ?>
        <div class="reflection-item">
          <label for="reflection-<?php echo $index; ?>">
            <span class="reflection-prompt"><?php echo htmlspecialchars($prompt); ?></span>
          </label>
          <textarea 
            id="reflection-<?php echo $index; ?>" 
            name="reflection-<?php echo $index; ?>" 
            class="reflection-textarea" 
            data-prompt="<?php echo htmlspecialchars($prompt); ?>"
            placeholder="Schreib hier deine Gedanken..."
            rows="4"
          ></textarea>
        </div>
        <?php endforeach; ?>
      </fieldset>

      <div class="reflection-note">
        <p>💭 Diese Notizen sind vertraulich und helfen dir, deine Ergebnisse später besser zu verstehen.</p>
      </div>
    </form>

    <div class="phase-navigation">
      <button class="btn btn-secondary" data-prev-phase="3">← Zurück zu Phase 3</button>
      <button class="btn btn-primary" data-next-phase="5">Zu deinen Ergebnissen → Phase 5</button>
    </div>
  </section>

  <?php elseif ($phase === 5): 
  // PHASE 5: RESULTS
  ?>
  <section class="diagnostic-phase phase-results" id="phase-5">
    <div class="phase-header" style="border-color: <?php echo $moduleData['color']; ?>;">
      <h2><?php echo htmlspecialchars($moduleData['results']['title']); ?></h2>
      <p>Deine personalisierte Auswertung</p>
    </div>

    <div class="results-container">
      <!-- Results Summary Card -->
      <div class="results-summary" style="border-color: <?php echo $moduleData['color']; ?>;">
        <div class="summary-module">
          <span class="module-icon" style="font-size: 2.5rem;"><?php echo $moduleData['icon']; ?></span>
          <h3><?php echo htmlspecialchars($moduleData['name']); ?></h3>
        </div>
        <p class="summary-message">
          ✓ Diagnostik abgeschlossen. Deine Ergebnisse werden unten grafisch dargestellt.
        </p>
      </div>

      <!-- Placeholder: Chart Containers -->
      <div class="results-charts">
        <?php foreach ($moduleData['results']['charts'] as $chart): ?>
        <div class="chart-container" id="chart-<?php echo htmlspecialchars($chart); ?>">
          <h3><?php echo htmlspecialchars(ucfirst(str_replace('-', ' ', $chart))); ?></h3>
          <div class="chart-placeholder" style="background: linear-gradient(135deg, <?php echo $moduleData['color']; ?>15 0%, <?php echo $moduleData['color']; ?>08 100%); border: 2px dashed <?php echo $moduleData['color']; ?>; padding: 3rem; text-align: center; border-radius: 0.5rem;">
            <p>📊 Grafik wird hier angezeigt (Chart.js / Plotly)</p>
            <p style="font-size: 0.9rem; color: #666;">z.B. Perzentil-Vergleich, Profil-Graph, Stärken-Übersicht</p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Key Findings Section -->
      <div class="results-findings">
        <h3>📌 Wichtigste Erkenntnisse</h3>
        <div class="findings-list">
          <div class="finding-item">
            <h4>Dein Ergebnis</h4>
            <p>[Wird aus Scoring-Logik generiert]</p>
          </div>
          <div class="finding-item">
            <h4>Deine Stärken</h4>
            <p>[Basierend auf Profil]</p>
          </div>
          <div class="finding-item">
            <h4>Nächste Schritte</h4>
            <p>[Empfehlungen für Support & Ressourcen]</p>
          </div>
        </div>
      </div>

      <!-- PDF Report Button -->
      <div class="results-actions">
        <button class="btn btn-primary" id="downloadReportBtn" style="background-color: <?php echo $moduleData['color']; ?>;">
          📄 Bericht als PDF herunterladen
        </button>
        <button class="btn btn-secondary" id="printReportBtn">
          🖨️ Bericht drucken
        </button>
      </div>

      <!-- Next Steps & Resources -->
      <div class="results-next-steps">
        <h3>Wie geht's weiter?</h3>
        <div class="next-steps-grid">
          <div class="step-card">
            <h4>1️⃣ Verstehen</h4>
            <p>Lese deinen Bericht und nimm dir Zeit, deine Ergebnisse zu verarbeiten.</p>
          </div>
          <div class="step-card">
            <h4>2️⃣ Vertiefen</h4>
            <p>Erkunde weitere Module oder hole dir professionelle Unterstützung.</p>
          </div>
          <div class="step-card">
            <h4>3️⃣ Unterstützung</h4>
            <p>Finde Communities, Ressourcen und praktische Tipps für deinen Weg.</p>
          </div>
          <div class="step-card">
            <h4>4️⃣ Fortschritt</h4>
            <p>Verfolge deine Entwicklung, teile mit Fachpersonen oder mach später einen Re-Test.</p>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <div class="results-navigation">
        <a href="diagnostics.php" class="btn btn-secondary">Zurück zur Modul-Übersicht</a>
        <button class="btn btn-secondary" id="startNewModuleBtn">Anderes Modul starten</button>
      </div>
    </div>
  </section>

  <?php else: 
  // INVALID PHASE
  ?>
  <div class="error-message">
    <p>Ungültige Phase. Bitte starten Sie von vorne.</p>
    <a href="diagnostics.php" class="btn btn-primary">Zur Übersicht</a>
  </div>
  <?php endif; ?>

</main>

<!-- Hidden data for JavaScript -->
<script type="application/json" id="moduleData">
<?php echo json_encode($moduleData); ?>
</script>

<?php include 'includes/footer.php'; ?>
