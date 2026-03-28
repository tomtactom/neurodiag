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

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function ndClamp(float $value, float $min, float $max): float
{
    return max($min, min($max, $value));
}

/**
 * @return array{headline: string, strengths: array<int, string>, focus: array<int, string>, communication: string}
 */
function ndProcessProfile(?string $processId, ?string $processTitle): array
{
    $default = [
        'headline' => 'Ressourcenorientierte Ergebnisdarstellung',
        'strengths' => [
            'Sie erhalten eine fokussierte Übersicht über bereits sichtbare Kompetenzen.',
            'Die Darstellung trennt Datenbasis, Einordnung und alltagsnahe Handlungsoptionen.',
        ],
        'focus' => [
            'Kontexte mit hoher Belastung identifizieren und gezielt entschärfen.',
            'Stabile Routinen in kleinen, überprüfbaren Schritten ausbauen.',
        ],
        'communication' => 'Wertschätzend, klar und ohne Etikettierung: Ergebnisse beschreiben Verhalten im Kontext.',
    ];

    if ($processId === null) {
        return $default;
    }

    $profiles = [
        'ass' => [
            'headline' => 'Autismus-Profil: Reizverarbeitung, soziale Orientierung und Strukturbedarf',
            'strengths' => ['Detailgenauigkeit und Regelklarheit als Ressource nutzbar machen.', 'Soziale Energie gezielt auf planbare Situationen verteilen.'],
            'focus' => ['Reizmanagement vor sozialen Spitzenzeiten vorbereiten.', 'Kommunikationsabsprachen explizit und vorhersehbar gestalten.'],
            'communication' => 'Die Auswertung fokussiert Unterschiede in Informationsverarbeitung und Umweltpassung.',
        ],
        'adhs' => [
            'headline' => 'AD(H)S-Profil: Aufmerksamkeitssteuerung, Impulsregulation und Handlungsstart',
            'strengths' => ['Hohe Dynamik und Ideenfluss in passende Aufgabenkanäle lenken.', 'Kurzfristige Motivation in verlässliche Start-Routinen überführen.'],
            'focus' => ['Externe Strukturhilfen für Priorisierung und Abschluss nutzen.', 'Ablenkungsarme Zeitfenster mit klaren Startsignalen verankern.'],
            'communication' => 'Die Darstellung hebt exekutive Steuerung im Alltag hervor – ohne Defizitfokus.',
        ],
        'dyslexie-lrs' => [
            'headline' => 'Dyslexie-Profil: Lesefluss, Dekodierung und Textzugang',
            'strengths' => ['Inhaltliches Verständnis unabhängig vom Lesetempo sichtbar machen.', 'Wirksame Lesehilfen systematisch in Lern- und Arbeitsumgebungen integrieren.'],
            'focus' => ['Textlastige Aufgaben frühzeitig strukturieren.', 'Visuelle und auditive Entlastungsstrategien kombinieren.'],
            'communication' => 'Ergebnisse zeigen Lernbedingungen, die Textverarbeitung erleichtern.',
        ],
        'dysgraphie' => [
            'headline' => 'Dysgraphie-Profil: Schreibmotorik, Tempo und Ausdrucksorganisation',
            'strengths' => ['Inhaltliche Qualität vom Schreibtempo entkoppeln.', 'Alternative Ausdruckswege (z. B. digital) als Kompetenzverstärker einsetzen.'],
            'focus' => ['Schreibaufgaben in klare Teilsequenzen gliedern.', 'Belastungsspitzen bei längeren Schreibphasen früh steuern.'],
            'communication' => 'Im Fokus stehen Arbeitsbedingungen für klare und machbare schriftliche Leistungen.',
        ],
        'dyskalkulie' => [
            'headline' => 'Dyskalkulie-Profil: Zahlenverständnis, Mengenbezug und Rechenstrategie',
            'strengths' => ['Anschauliche Darstellungen unterstützen stabile Lösungswege.', 'Fehleranalyse als Lernressource statt Bewertungssignal nutzen.'],
            'focus' => ['Rechenschritte transparent und wiederholbar strukturieren.', 'Basisstrategien in realen Alltagssituationen verankern.'],
            'communication' => 'Die Ergebnisse ordnen numerische Anforderungen kontextbezogen und ressourcenfokussiert ein.',
        ],
        'dyspraxie-dcd' => [
            'headline' => 'Dyspraxie-Profil: Planung, Sequenzierung und Koordination von Handlungen',
            'strengths' => ['Komplexe Tätigkeiten über klare Bewegungs- und Handlungsschritte sichern.', 'Vorbereitungsroutinen reduzieren Zeitdruck und Fehlerkosten.'],
            'focus' => ['Motorische und organisatorische Übergänge trainierbar machen.', 'Aufgaben mit visuellen Schrittplänen vorstrukturieren.'],
            'communication' => 'Die Darstellung konzentriert sich auf gelingende Handlungssteuerung im Alltag.',
        ],
        'tic-tourette' => [
            'headline' => 'Tic-Profil: Belastungsdynamik, Trigger und Selbststeuerung',
            'strengths' => ['Frühe Warnsignale können für wirksame Selbstregulation genutzt werden.', 'Umgebungsanpassung erhöht Handlungsspielraum in anspruchsvollen Situationen.'],
            'focus' => ['Triggerkontexte präzise erfassen und entlastend planen.', 'Kurze Steuerungsroutinen in Alltagssituationen standardisieren.'],
            'communication' => 'Ergebnisse beschreiben Muster und Einflussfaktoren statt Bewertungen der Person.',
        ],
        'dld' => [
            'headline' => 'Sprachprofil (DLD): Verstehen, Ausdruck und kommunikative Belastung',
            'strengths' => ['Kommunikative Stärken in vertrauten Kontexten gezielt ausbauen.', 'Sprachliche Anforderungen über klare Struktur und Visualisierung absichern.'],
            'focus' => ['Gesprächssituationen mit hoher Informationsdichte vorbereiten.', 'Verständnissicherung aktiv in Interaktionen verankern.'],
            'communication' => 'Die Auswertung fokussiert alltagsnahe Kommunikationsbedingungen und Zugänglichkeit.',
        ],
    ];

    if (isset($profiles[$processId])) {
        return $profiles[$processId];
    }

    $default['headline'] = ($processTitle ?? 'Bereich') . ': ressourcenorientierte Ergebnisdarstellung';
    return $default;
}

/**
 * @return array<string, mixed>
 */
function buildViewModel(string $mode, ?string $processId, ?string $processTitle, array $areas, array $evaluation): array
{
    $isProcessMode = $mode === 'process' && $processId !== null;
    $profile = ndProcessProfile($processId, $processTitle);

    $hasData = isset($evaluation['hasData']) && $evaluation['hasData'] === true;
    $hasRaw = isset($evaluation['raw']) && is_array($evaluation['raw']);
    $hasNorm = isset($evaluation['norm']['z']) && is_float($evaluation['norm']['z']);

    $summaryCards = [
        ['title' => 'Profilfokus', 'text' => $profile['headline'], 'tone' => 'focus'],
        ['title' => 'Stärken', 'text' => implode(' ', $profile['strengths']), 'tone' => 'resource'],
        ['title' => 'Arbeitsfokus', 'text' => implode(' ', $profile['focus']), 'tone' => 'action'],
        ['title' => 'Kommunikationsstil', 'text' => $profile['communication'], 'tone' => 'neutral'],
    ];

    $measuredBlocks = [];
    if ($hasRaw) {
        $measuredBlocks[] = [
            'title' => 'Messbasis',
            'points' => [
                'Datenquelle: ' . (string) $evaluation['source'] . '.',
                'Erfasste Unit: ' . (string) ($evaluation['unitTitle'] ?? 'nicht verfügbar') . '.',
                'Beantwortete Items: ' . (string) $evaluation['raw']['answered'] . ' von ' . (string) $evaluation['raw']['expected'] . '.',
            ],
        ];

        if ((int) $evaluation['raw']['missing'] > 0) {
            $measuredBlocks[] = [
                'title' => 'Datenvollständigkeit',
                'points' => [
                    'Fehlende Antworten: ' . (string) $evaluation['raw']['missing'] . '.',
                    'Einordnung erfolgt als Momentaufnahme mit transparenter Unsicherheit.',
                ],
            ];
        }
    }

    if ($hasNorm) {
        $measuredBlocks[] = [
            'title' => 'Normeinordnung',
            'points' => [
                'Referenzbereich: ' . (string) $evaluation['norm']['reference'] . '.',
                'Kennwerte: z=' . ndFmt((float) $evaluation['norm']['z'], 2) . ', T=' . ndFmt((float) $evaluation['norm']['t'], 1) . ', PR=' . ndFmt((float) $evaluation['norm']['pr'], 1) . '.',
            ],
        ];
    }

    $actions = [];
    foreach ($profile['focus'] as $focusItem) {
        $actions[] = $focusItem;
    }
    $actions[] = 'Nächsten Schritt als beobachtbares Verhalten formulieren (Situation + Verhalten + Ergebnisindikator).';
    $actions[] = 'Wirkung nach 7 Tagen prüfen und nur eine Variable gleichzeitig anpassen.';

    if ($hasData && !$hasNorm) {
        $actions[] = 'Da keine Normposition vorliegt, den Fortschritt primär über Frequenz, Dauer und Unterstützungsbedarf dokumentieren.';
    }

    if (!$hasData) {
        $actions = ['Für eine individuelle Ergebnisdarstellung zuerst einen Bereich vollständig abschließen.', 'Danach wird ausschließlich die tatsächlich gemessene Unit berichtet.'];
    }

    return [
        'mode' => $mode,
        'processId' => $processId,
        'processTitle' => $processTitle,
        'profile' => $profile,
        'summaryCards' => $summaryCards,
        'measuredBlocks' => $measuredBlocks,
        'actions' => $actions,
        'availableProcesses' => $areas,
        'hasData' => $hasData,
        'hasNorm' => $hasNorm,
        'isProcessMode' => $isProcessMode,
        'evaluation' => $evaluation,
    ];
}

/**
 * @return array{hasNorm: bool, zValue: ?float, markerPercent: float, fallbackLabel: string, summary: string, zone: string}
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
            'Position in der Referenz: z = %s (T = %s, PR = %s). Einordnung als Kontextaufnahme mit Fokus auf beeinflussbare Rahmenbedingungen.',
            ndFmt($evaluation['norm']['z'], 2),
            ndFmt(isset($evaluation['norm']['t']) && is_float($evaluation['norm']['t']) ? $evaluation['norm']['t'] : null, 1),
            ndFmt(isset($evaluation['norm']['pr']) && is_float($evaluation['norm']['pr']) ? $evaluation['norm']['pr'] : null, 1)
        )
        : 'Keine Normposition verfügbar. Fortschritte können dennoch präzise über beobachtbare Verhaltensindikatoren dokumentiert werden.';

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
 * @param array<int, array{title: string, points: array<int, string>}> $sections
 */
function renderMeasuredBlocks(array $sections): void
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
function renderActionList(array $items): void
{
    echo '<ol class="result-list result-list--recommendations">';
    foreach ($items as $item) {
        echo '<li>' . h($item) . '</li>';
    }
    echo '</ol>';
}

/**
 * @param array<string, array<string, mixed>> $processes
 */
function renderProcessPicker(array $processes): void
{
    echo '<section class="result-section" aria-labelledby="result-process-picker">';
    echo '<h2 id="result-process-picker">Neurodivergenz-Bereich auswählen</h2>';
    echo '<p>Öffnen Sie die individuell optimierte Ergebnisseite für den jeweiligen Bereich:</p>';
    echo '<div class="result-chip-list">';
    foreach ($processes as $id => $entry) {
        $title = isset($entry['title']) && is_string($entry['title']) ? $entry['title'] : strtoupper($id);
        echo '<a class="result-chip" href="result.php?process=' . rawurlencode($id) . '">' . h($title) . '</a>';
    }
    echo '</div></section>';
}

function ndPdfEscape(string $text): string
{
    $text = str_replace('\\', '\\\\', $text);
    $text = str_replace('(', '\\(', $text);
    return str_replace(')', '\\)', $text);
}

function ndBuildPdf(string $title, array $lines): string
{
    $safeTitle = preg_replace('/[^a-zA-Z0-9_-]+/', '-', strtolower($title));
    $safeTitle = trim((string) $safeTitle, '-');
    if ($safeTitle === '') {
        $safeTitle = 'bericht';
    }

    $contentLines = [
        'BT',
        '/F1 11 Tf',
        '50 800 Td',
    ];

    foreach ($lines as $idx => $line) {
        if ($idx > 0) {
            $contentLines[] = '0 -15 Td';
        }
        $contentLines[] = '(' . ndPdfEscape($line) . ') Tj';
    }
    $contentLines[] = 'ET';

    $stream = implode("\n", $contentLines) . "\n";

    $objects = [];
    $objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
    $objects[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
    $objects[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>\nendobj\n";
    $objects[] = "4 0 obj\n<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "endstream\nendobj\n";
    $objects[] = "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";

    $pdf = "%PDF-1.4\n";
    $offsets = [0];

    foreach ($objects as $object) {
        $offsets[] = strlen($pdf);
        $pdf .= $object;
    }

    $xrefStart = strlen($pdf);
    $count = count($objects) + 1;
    $pdf .= "xref\n0 {$count}\n";
    $pdf .= "0000000000 65535 f \n";

    for ($i = 1; $i < $count; $i++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
    }

    $pdf .= "trailer\n<< /Size {$count} /Root 1 0 R >>\n";
    $pdf .= "startxref\n{$xrefStart}\n%%EOF";

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="neurodiag-' . $safeTitle . '-bericht.pdf"');
    header('Content-Length: ' . strlen($pdf));

    return $pdf;
}

$viewModel = buildViewModel($mode, $processId, $processTitle, $areas, $evaluation);
$normViz = buildNormVizModel($evaluation);

if (
    isset($_GET['download'])
    && $_GET['download'] === 'pdf'
    && $viewModel['isProcessMode']
    && $viewModel['hasData']
) {
    $lines = [];
    $lines[] = 'Neurodiag Ergebnisbericht';
    $lines[] = 'Bereich: ' . (string) $viewModel['processTitle'];
    $lines[] = 'Unit: ' . (string) ($evaluation['unitTitle'] ?? 'nicht verfügbar');
    $lines[] = 'Quelle: ' . (string) ($evaluation['source'] ?? 'n/a');

    if (isset($evaluation['raw']) && is_array($evaluation['raw'])) {
        $lines[] = 'Beantwortet: ' . (string) $evaluation['raw']['answered'] . '/' . (string) $evaluation['raw']['expected'];
        $lines[] = 'Rohwert: ' . ndFmt((float) $evaluation['raw']['sum'], 2);
    }

    if ($viewModel['hasNorm']) {
        $lines[] = 'Norm: z=' . ndFmt((float) $evaluation['norm']['z'], 2) . ', T=' . ndFmt((float) $evaluation['norm']['t'], 1) . ', PR=' . ndFmt((float) $evaluation['norm']['pr'], 1);
        $lines[] = 'Referenz: ' . (string) $evaluation['norm']['reference'];
    }

    $lines[] = 'Handlungsfokus:';
    foreach ($viewModel['actions'] as $action) {
        $lines[] = '- ' . (string) $action;
    }

    echo ndBuildPdf((string) $viewModel['processTitle'], $lines);
    exit;
}

$pageTitle = $viewModel['mode'] === 'process'
    ? 'Auswertung – ' . (string) $viewModel['processTitle']
    : 'Auswertung – Bereiche';

include __DIR__ . '/includes/header.php';
?>
<section class="result-page" aria-labelledby="result-page-title">
  <header class="result-hero">
    <p class="result-eyebrow"><?php echo $viewModel['mode'] === 'process' ? 'Bereichsspezifische Auswertung' : 'Neurodivergenz-Bereiche'; ?></p>
    <h1 id="result-page-title">
      <?php if ($viewModel['mode'] === 'process'): ?>
        Ergebnisprofil: <?php echo h((string) $viewModel['processTitle']); ?>
      <?php else: ?>
        Individuelle Ergebnisseiten nach Bereich
      <?php endif; ?>
    </h1>
    <p class="result-subtitle">Die Darstellung ist ressourcenorientiert, professionell strukturiert und berichtet ausschließlich tatsächlich erhobene Daten.<?php if (!empty($evaluation['unitTitle'])): ?> Erhobene Unit: <?php echo h((string) $evaluation['unitTitle']); ?>.<?php endif; ?></p>

    <?php if ($viewModel['isProcessMode'] && $viewModel['hasData']): ?>
      <p><a class="btn" href="result.php?process=<?php echo rawurlencode((string) $viewModel['processId']); ?>&amp;download=pdf">PDF-Bericht herunterladen</a></p>
    <?php endif; ?>
  </header>

  <?php if ($viewModel['isProcessMode'] && $viewModel['hasData']): ?>
    <section class="result-section" aria-labelledby="summary-title">
      <h2 id="summary-title">Bereichsprofil</h2>
      <?php renderSummaryCards($viewModel['summaryCards']); ?>
    </section>

    <?php if (!empty($viewModel['measuredBlocks'])): ?>
      <section class="result-section" aria-labelledby="measured-title">
        <h2 id="measured-title">Erhobene Kennwerte</h2>
        <p>Dieser Abschnitt enthält nur Messdaten, die in der ausgewählten Unit tatsächlich erhoben wurden.</p>
        <?php renderMeasuredBlocks($viewModel['measuredBlocks']); ?>
      </section>
    <?php endif; ?>

    <?php if ($viewModel['hasNorm']): ?>
      <section class="result-section" aria-labelledby="viz-title">
        <h2 id="viz-title">Normvisualisierung</h2>
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
              <title id="result-viz-title">Normalverteilungskurve mit Markerposition</title>
              <path class="result-bell-curve-line" d="M10,170 C85,170 135,50 250,22 C365,50 415,170 490,170" />
            </svg>
            <div class="result-marker <?php echo 'result-marker--' . h($normViz['zone']); ?>" style="left: <?php echo h(ndFmt($normViz['markerPercent'], 1)); ?>%;" aria-hidden="true">
              <span class="result-marker-dot"></span>
              <span class="result-marker-line"></span>
            </div>
          </div>
          <p><?php echo h($normViz['summary']); ?></p>
        </div>
      </section>
    <?php endif; ?>

    <section class="result-section" aria-labelledby="action-title">
      <h2 id="action-title">Handlungsorientierte Auswertung</h2>
      <p>Verhaltensorientiert, konkret und überprüfbar formuliert.</p>
      <?php renderActionList($viewModel['actions']); ?>
    </section>
  <?php elseif ($viewModel['isProcessMode']): ?>
    <section class="result-section" aria-labelledby="no-data-title">
      <h2 id="no-data-title">Noch keine Messdaten vorhanden</h2>
      <p>Für diesen Bereich liegen aktuell keine auswertbaren Antworten vor. Nach Abschluss einer Unit wird hier automatisch die individuelle Ergebnisseite erzeugt.</p>
    </section>
  <?php endif; ?>

  <?php if ($viewModel['mode'] === 'global'): ?>
    <?php renderProcessPicker($viewModel['availableProcesses']); ?>
  <?php endif; ?>
</section>
<?php
include __DIR__ . '/includes/footer.php';
