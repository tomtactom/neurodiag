<?php
/**
 * Ergebnis-Einstiegspunkt (global + prozessspezifisch).
 */

declare(strict_types=1);

$processRegistry = require __DIR__ . '/config/process-registry.php';
$areas = isset($processRegistry['areas']) && is_array($processRegistry['areas']) ? $processRegistry['areas'] : [];
$aliases = isset($processRegistry['aliases']) && is_array($processRegistry['aliases']) ? $processRegistry['aliases'] : [];

$processInput = isset($_GET['process']) ? strtolower(trim((string) $_GET['process'])) : '';
if (!preg_match('/^[a-z0-9_-]*$/', $processInput)) {
    $processInput = '';
}

$processId = null;
$processTitle = null;

if ($processInput !== '' && isset($aliases[$processInput]) && is_string($aliases[$processInput])) {
    $candidateId = $aliases[$processInput];
    if (isset($areas[$candidateId]) && is_array($areas[$candidateId])) {
        $processId = $candidateId;
        $processTitle = isset($areas[$candidateId]['title']) && is_string($areas[$candidateId]['title'])
            ? $areas[$candidateId]['title']
            : strtoupper($candidateId);
    }
}

$mode = $processId === null ? 'global' : 'process';

/**
 * @return array<string, mixed>
 */
function buildViewModel(string $mode, ?string $processId, ?string $processTitle, array $areas): array
{
    $isProcessMode = $mode === 'process' && $processId !== null;

    $summaryCards = [
        [
            'title' => 'Stärken zuerst',
            'text' => 'Die Auswertung hebt zunächst Ressourcen, gelingende Strategien und belastbare Alltagskompetenzen hervor.',
            'tone' => 'resource',
        ],
        [
            'title' => 'Kontext statt Etikett',
            'text' => 'Ergebnisse werden situationsbezogen gelesen: Anforderungen, Umgebung und Unterstützungsfaktoren werden mitgedacht.',
            'tone' => 'neutral',
        ],
        [
            'title' => 'Nächste Schritte planbar',
            'text' => 'Empfehlungen sind beobachtbar, kleinschrittig und innerhalb von 1-2 Wochen überprüfbar.',
            'tone' => 'action',
        ],
    ];

    $apaBlocks = [
        [
            'headline' => 'Berichtsstruktur (APA-7 orientiert)',
            'content' => 'M = 58.4, SD = 9.1, 95%-KI [54.8, 62.0], z = 0.93. Die Kennwerte sind als orientierende Selbstbeobachtung zu interpretieren.',
        ],
        [
            'headline' => 'Interpretationsrahmen',
            'content' => 'Effektstärken und Normpositionen werden immer gemeinsam mit Funktionsniveau, Belastung und Alltagszielen eingeordnet.',
        ],
    ];

    $normSections = [
        [
            'title' => 'Normbezug',
            'points' => [
                'Vergleich mit Referenzstichprobe (alters- und sprachsensibel, sofern verfügbar).',
                'Einordnung über Perzentil, z-Wert und qualitatives Funktionsniveau.',
            ],
        ],
        [
            'title' => 'Unsicherheit transparent',
            'points' => [
                'Messfehler, Tagesform und Antwortstil können Werte beeinflussen.',
                'Konfidenzintervalle und Wiederholungsmessung werden empfohlen.',
            ],
        ],
    ];

    $recommendations = [
        'Formuliere ein Zielverhalten konkret: Was genau soll in welcher Situation häufiger gelingen?',
        'Plane Mikro-Schritte (z. B. 10 Minuten, 1 Kontext, 1 Hilfsmittel) und führe ein kurzes Wochenprotokoll.',
        'Nutze Wenn-Dann-Pläne: "Wenn Überlastung > 7/10, dann 3-Minuten-Reizreduktion + Prioritätencheck".',
        'Bewerte Fortschritt mit beobachtbaren Indikatoren (Häufigkeit, Dauer, erforderliche Unterstützung).',
    ];

    $nextSteps = [
        [
            'label' => 'Selbstbeobachtung fortsetzen',
            'detail' => '2 Wochen strukturiertes Monitoring mit identischen Kernindikatoren.',
        ],
        [
            'label' => 'Ressourceninterview',
            'detail' => '1 Gespräch mit Fokus auf Ausnahmen, gelingende Situationen und unterstützende Bedingungen.',
        ],
        [
            'label' => 'Review-Termin',
            'detail' => 'Gemeinsame Überprüfung der Ziele und Anpassung der Schritte im 14-Tage-Rhythmus.',
        ],
    ];

    if ($isProcessMode) {
        $summaryCards[] = [
            'title' => 'Prozessfokus: ' . $processTitle,
            'text' => 'Die Ergebnisblöcke sind auf diesen Neurodivergenz-Baustein zugeschnitten und können mit weiteren Modulen kombiniert werden.',
            'tone' => 'focus',
        ];
    } else {
        $summaryCards[] = [
            'title' => 'Globaler Überblick',
            'text' => 'Diese Seite bündelt querschnittliche Muster über mehrere Bausteine hinweg und priorisiert übergreifende Handlungsziele.',
            'tone' => 'focus',
        ];
    }

    return [
        'mode' => $mode,
        'processId' => $processId,
        'processTitle' => $processTitle,
        'summaryCards' => $summaryCards,
        'apaBlocks' => $apaBlocks,
        'normSections' => $normSections,
        'recommendations' => $recommendations,
        'nextSteps' => $nextSteps,
        'availableProcesses' => $areas,
    ];
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * @param array<int, array{title: string, text: string, tone: string}> $cards
 */
function renderSummaryCards(array $cards): void
{
    echo '<div class="result-grid result-grid--cards">';
    foreach ($cards as $card) {
        echo '<article class="result-card result-card--' . h($card['tone']) . '">';
        echo '<h3>' . h($card['title']) . '</h3>';
        echo '<p>' . h($card['text']) . '</p>';
        echo '</article>';
    }
    echo '</div>';
}

/**
 * @param array<int, array{headline: string, content: string}> $blocks
 */
function renderApaBlocks(array $blocks): void
{
    echo '<div class="result-grid result-grid--apa">';
    foreach ($blocks as $block) {
        echo '<article class="result-card result-card--apa">';
        echo '<h3>' . h($block['headline']) . '</h3>';
        echo '<p>' . h($block['content']) . '</p>';
        echo '</article>';
    }
    echo '</div>';
}

/**
 * @param array<int, array{title: string, points: array<int, string>}> $sections
 */
function renderNormSections(array $sections): void
{
    echo '<div class="result-grid result-grid--norm">';
    foreach ($sections as $section) {
        echo '<article class="result-card result-card--norm">';
        echo '<h3>' . h($section['title']) . '</h3><ul>';
        foreach ($section['points'] as $point) {
            echo '<li>' . h($point) . '</li>';
        }
        echo '</ul></article>';
    }
    echo '</div>';
}

/**
 * @param array<int, string> $items
 */
function renderRecommendations(array $items): void
{
    echo '<ol class="result-list result-list--recommendations">';
    foreach ($items as $item) {
        echo '<li>' . h($item) . '</li>';
    }
    echo '</ol>';
}

/**
 * @param array<int, array{label: string, detail: string}> $steps
 */
function renderNextSteps(array $steps): void
{
    echo '<ul class="result-list result-list--nextsteps">';
    foreach ($steps as $step) {
        echo '<li><strong>' . h($step['label']) . ':</strong> ' . h($step['detail']) . '</li>';
    }
    echo '</ul>';
}

/**
 * @param array<string, array<string, mixed>> $processes
 */
function renderProcessPicker(array $processes): void
{
    echo '<section class="result-section" aria-labelledby="result-process-picker">';
    echo '<h2 id="result-process-picker">Baustein auswählen</h2>';
    echo '<p>Optional können Sie eine bereichsspezifische Ergebnissicht öffnen:</p>';
    echo '<div class="result-chip-list">';
    foreach ($processes as $id => $entry) {
        $title = isset($entry['title']) && is_string($entry['title']) ? $entry['title'] : strtoupper($id);
        echo '<a class="result-chip" href="result.php?process=' . rawurlencode($id) . '">' . h($title) . '</a>';
    }
    echo '</div></section>';
}

$viewModel = buildViewModel($mode, $processId, $processTitle, $areas);
$pageTitle = $viewModel['mode'] === 'process'
    ? 'Auswertung – ' . (string) $viewModel['processTitle']
    : 'Auswertung – Gesamtüberblick';

include __DIR__ . '/includes/header.php';
?>
<section class="result-page" aria-labelledby="result-page-title">
  <header class="result-hero">
    <p class="result-eyebrow"><?php echo $viewModel['mode'] === 'process' ? 'Prozessbezogene Auswertung' : 'Globaler Ergebnis-Modus'; ?></p>
    <h1 id="result-page-title">
      <?php if ($viewModel['mode'] === 'process'): ?>
        Auswertung: <?php echo h((string) $viewModel['processTitle']); ?>
      <?php else: ?>
        Integrierte Ergebnisübersicht
      <?php endif; ?>
    </h1>
    <p class="result-subtitle">Klinisch-neutraler Hinweis: Diese Auswertung dient der strukturierten Selbstreflexion und ersetzt keine fachärztliche oder psychotherapeutische Diagnostik.</p>
  </header>

  <section class="result-section" aria-labelledby="summary-title">
    <h2 id="summary-title">Executive Summary</h2>
    <p>Kurze, verständliche und ressourcenorientierte Zusammenfassung mit Fokus auf alltagsrelevante Veränderungsschritte.</p>
    <?php renderSummaryCards($viewModel['summaryCards']); ?>
  </section>

  <section class="result-section" aria-labelledby="psychometric-title">
    <h2 id="psychometric-title">Psychometrie</h2>
    <p>Skalenwerte, Normbezug und Unsicherheiten werden transparent dokumentiert.</p>
    <?php renderNormSections($viewModel['normSections']); ?>
  </section>

  <section class="result-section" aria-labelledby="apa-title">
    <h2 id="apa-title">APA-7-konforme Ergebnisdarstellung</h2>
    <?php renderApaBlocks($viewModel['apaBlocks']); ?>
  </section>

  <section class="result-section" aria-labelledby="viz-title">
    <h2 id="viz-title">Visualisierung</h2>
    <div class="result-visual">
      <div class="result-bell" role="img" aria-label="Normalverteilung mit Positionsmarker">
        <div class="result-marker" style="left: 64%;" aria-hidden="true"></div>
      </div>
      <div class="result-axis" aria-hidden="true">
        <span>-2 SD</span><span>-1 SD</span><span>M</span><span>+1 SD</span><span>+2 SD</span>
      </div>
      <p class="result-visual-note">Beispielhafte Position im Normraum. Für belastbare Aussagen sind Messwiederholungen und Kontextdaten sinnvoll.</p>
    </div>
  </section>

  <section class="result-section" aria-labelledby="recommend-title">
    <h2 id="recommend-title">Verhaltenstherapeutische Empfehlungen</h2>
    <p>Konkret, beobachtbar und kleinschrittig formuliert, damit Fortschritt überprüfbar bleibt.</p>
    <?php renderRecommendations($viewModel['recommendations']); ?>
  </section>

  <section class="result-section" aria-labelledby="next-steps-title">
    <h2 id="next-steps-title">Nächste Schritte</h2>
    <?php renderNextSteps($viewModel['nextSteps']); ?>
  </section>

  <?php if ($viewModel['mode'] === 'global'): ?>
    <?php renderProcessPicker($viewModel['availableProcesses']); ?>
  <?php endif; ?>
</section>
<?php
include __DIR__ . '/includes/footer.php';
