<?php
/**
 * Ergebnis-Einstiegspunkt (global + prozessspezifisch).
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/result-functions.php';

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
$evaluation = ndBuildEvaluationViewModel($processId, $processTitle, $areas);

/**
 * @return array<string, mixed>
 */
function buildViewModel(string $mode, ?string $processId, ?string $processTitle, array $areas, array $evaluation): array
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
            'headline' => 'APA-7 Ergebnisstring',
            'content' => (string) $evaluation['text']['apa'],
        ],
        [
            'headline' => 'Trennung von Beschreibung und Interpretation',
            'content' => (string) $evaluation['text']['description'] . ' ' . (string) $evaluation['text']['interpretation'],
        ],
    ];

    $normSections = [
        [
            'title' => 'Normbezug und Kennwerte',
            'points' => [
                'Antwortquelle: ' . (string) $evaluation['source'] . '.',
                isset($evaluation['norm']['reference']) ? 'Referenzbereich: ' . (string) $evaluation['norm']['reference'] . '.' : 'Referenzbereich: nicht verfügbar.',
                isset($evaluation['norm']['z']) && $evaluation['norm']['z'] !== null ? 'Normwerte: z=' . ndFmt((float) $evaluation['norm']['z'], 2) . ', T=' . ndFmt((float) $evaluation['norm']['t'], 1) . ', PR=' . ndFmt((float) $evaluation['norm']['pr'], 1) . '.' : 'Normwerte: aufgrund fehlender Daten nicht berechenbar.',
            ],
        ],
        [
            'title' => 'Transparenz bei fehlenden Werten',
            'points' => [
                isset($evaluation['raw']['missing']) ? 'Fehlende Antworten: ' . (string) $evaluation['raw']['missing'] . ' von ' . (string) $evaluation['raw']['expected'] . '.' : 'Keine Antwortdaten verfügbar.',
                'Messfehler, Tagesform und Antwortstil können Werte beeinflussen.',
            ],
        ],
    ];

    $recommendations = [
        ($evaluation['text']['safety'][0] ?? ''),
        ($evaluation['text']['safety'][1] ?? ''),
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

function ndClamp(float $value, float $min, float $max): float
{
    if ($value < $min) {
        return $min;
    }

    if ($value > $max) {
        return $max;
    }

    return $value;
}

/**
 * @return array{
 *   hasNorm: bool,
 *   zValue: ?float,
 *   markerPercent: float,
 *   fallbackLabel: string,
 *   summary: string,
 *   zone: string
 * }
 */
function buildNormVizModel(array $evaluation): array
{
    $zValue = isset($evaluation['norm']['z']) && is_float($evaluation['norm']['z']) ? $evaluation['norm']['z'] : null;
    $hasNorm = $zValue !== null;
    $zForScale = $hasNorm ? ndClamp((float) $zValue, -2.5, 2.5) : 0.0;
    $markerPercent = (($zForScale + 2.5) / 5.0) * 100.0;

    $zone = 'avg';
    if ($hasNorm) {
        if ($zValue <= -1.0) {
            $zone = 'low';
        } elseif ($zValue >= 1.0) {
            $zone = 'high';
        }
    } else {
        $zone = 'unknown';
    }

    $fallbackLabel = $hasNorm
        ? sprintf('Normposition bei z = %s (%.1f%% auf der Skala)', ndFmt($zValue, 2), $markerPercent)
        : 'Normposition nicht berechenbar, da keine belastbaren Normdaten vorliegen.';

    $summary = $hasNorm
        ? sprintf(
            'Beobachtete Position: z = %s (T = %s, PR = %s). Einordnung als Momentaufnahme mit Blick auf Auslöser, aufrechterhaltende Bedingungen und konkrete nächste Schritte.',
            ndFmt($evaluation['norm']['z'], 2),
            ndFmt(isset($evaluation['norm']['t']) && is_float($evaluation['norm']['t']) ? $evaluation['norm']['t'] : null, 1),
            ndFmt(isset($evaluation['norm']['pr']) && is_float($evaluation['norm']['pr']) ? $evaluation['norm']['pr'] : null, 1)
        )
        : 'Aktuell ist keine Normposition berechenbar. Nutzen Sie beobachtbare Alltagsdaten (Häufigkeit, Dauer, Unterstützungsbedarf), um Veränderungsschritte trotzdem planbar zu machen.';

    return [
        'hasNorm' => $hasNorm,
        'zValue' => $zValue,
        'markerPercent' => $markerPercent,
        'fallbackLabel' => $fallbackLabel,
        'summary' => $summary,
        'zone' => $zone,
    ];
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

$viewModel = buildViewModel($mode, $processId, $processTitle, $areas, $evaluation);
$normViz = buildNormVizModel($evaluation);
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
    <p class="result-subtitle">Klinisch-neutraler Hinweis: Diese Auswertung dient der strukturierten Selbstreflexion und ersetzt keine fachärztliche oder psychotherapeutische Diagnostik.<?php if (!empty($evaluation['unitTitle'])): ?> Grundlage: <?php echo h((string) $evaluation['unitTitle']); ?>.<?php endif; ?></p>
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
    <div
      class="result-visual"
      data-norm-viz="true"
      data-has-norm="<?php echo $normViz['hasNorm'] ? '1' : '0'; ?>"
      data-z="<?php echo $normViz['zValue'] !== null ? h(ndFmt($normViz['zValue'], 2)) : ''; ?>"
      data-marker-percent="<?php echo h(ndFmt($normViz['markerPercent'], 1)); ?>"
    >
      <p class="sr-only" id="result-viz-description"><?php echo h($normViz['fallbackLabel']); ?></p>
      <div class="result-bell-wrap">
        <div class="result-bell-zones" aria-hidden="true">
          <span class="result-zone result-zone--low"></span>
          <span class="result-zone result-zone--avg"></span>
          <span class="result-zone result-zone--high"></span>
        </div>
        <svg class="result-bell-curve" viewBox="0 0 500 180" role="img" aria-labelledby="result-viz-title result-viz-description">
          <title id="result-viz-title">Normalverteilungskurve mit individueller Markerposition</title>
          <path class="result-bell-curve-line" d="M10,170 C85,170 135,50 250,22 C365,50 415,170 490,170" />
        </svg>
        <div class="result-marker <?php echo 'result-marker--' . h($normViz['zone']); ?>" style="left: <?php echo h(ndFmt($normViz['markerPercent'], 1)); ?>%;" aria-hidden="true">
          <span class="result-marker-dot"></span>
          <span class="result-marker-line"></span>
        </div>
      </div>

      <div class="result-scale-fallback" role="img" aria-label="<?php echo h($normViz['fallbackLabel']); ?>">
        <div class="result-scale-track" aria-hidden="true">
          <span class="result-scale-segment result-scale-segment--low"></span>
          <span class="result-scale-segment result-scale-segment--avg"></span>
          <span class="result-scale-segment result-scale-segment--high"></span>
          <span class="result-scale-marker" style="left: <?php echo h(ndFmt($normViz['markerPercent'], 1)); ?>%;"></span>
        </div>
      </div>

      <div class="result-axis" aria-hidden="true">
        <span>-2 SD</span><span>-1 SD</span><span>M</span><span>+1 SD</span><span>+2 SD</span>
      </div>

      <ul class="result-viz-legend">
        <li><span class="result-legend-chip result-legend-chip--low"></span>Untere Zone: aktuell eher gering ausgeprägt; hilfreich ist die Prüfung, wann Verhalten bereits stabil gelingt.</li>
        <li><span class="result-legend-chip result-legend-chip--avg"></span>Mittlere Zone: im Referenzbereich; Fokus auf auslösende Situationen und Bedingungen, die Stabilität fördern.</li>
        <li><span class="result-legend-chip result-legend-chip--high"></span>Obere Zone: aktuell stärker ausgeprägt; sinnvoll sind kurze Entlastungsschritte und gezielte Anpassung aufrechterhaltender Muster.</li>
      </ul>

      <div class="result-viz-help">
        <h3>Einordnung für nächste machbare Schritte</h3>
        <p><?php echo h($normViz['summary']); ?></p>
        <ul class="result-list">
          <li>Beobachtbares Verhalten festhalten: Was ist konkret sichtbar (z.&nbsp;B. Startverzögerung, Unterbrechungen, Vermeidungsverhalten)?</li>
          <li>Auslöser erfassen: In welchen Kontexten steigt die Belastung (Zeitdruck, Reizniveau, soziale Anforderungen)?</li>
          <li>Aufrechterhaltende Bedingungen prüfen: Welche kurzfristigen Entlastungen stabilisieren langfristig ungünstige Muster?</li>
          <li>Nächster Schritt in 7 Tagen: eine kleine Veränderung, ein klarer Kontext, ein überprüfbarer Indikator.</li>
        </ul>
      </div>
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



  <section class="result-section" aria-labelledby="safety-title">
    <h2 id="safety-title">Sicherheits- und Transparenzhinweise</h2>
    <ul class="result-list">
      <?php foreach (($evaluation['text']['safety'] ?? []) as $safetyItem): ?>
        <li><?php echo h((string) $safetyItem); ?></li>
      <?php endforeach; ?>
      <?php foreach (($evaluation['text']['transparency'] ?? []) as $transparencyItem): ?>
        <li><?php echo h((string) $transparencyItem); ?></li>
      <?php endforeach; ?>
    </ul>
  </section>

  <?php if ($viewModel['mode'] === 'global'): ?>
    <?php renderProcessPicker($viewModel['availableProcesses']); ?>
  <?php endif; ?>
</section>
<?php
include __DIR__ . '/includes/footer.php';
