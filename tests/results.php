<?php
$pageTitle = 'Ihre Ergebnisse';
include '../includes/header.php';
include '../includes/test-functions.php';

$module = $_GET['module'] ?? null;
if (!$module) {
    echo '<p>Kein Modul ausgewählt.</p>';
    include '../includes/footer.php';
    exit;
}

$allowed = ['aq-test', 'asrs-test', 'dyslexia-test', 'dysgraphia-test', 'dyskalkulie-test', 'dyspraxie-test', 'tic-test', 'dld-test'];
if (!in_array($module, $allowed, true)) {
    echo '<p>Ungültiges Modul.</p>';
    include '../includes/footer.php';
    exit;
}

$testData = loadTestData($module);
if (!$testData) {
    echo '<p>Test-Daten konnten nicht geladen werden.</p>';
    include '../includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<p>Keine Testergebnisse verfügbar. Bitte führen Sie zuerst den Test durch.</p>';
    include '../includes/footer.php';
    exit;
}

$score = calculateScore($testData);
$stats = getScoreStats($score, $testData);
$interpretation = interpretScore($score, $testData['norms']);
$details = getAnswerDetails($testData);
?>

<section class="result-container">
    <h2>Ihre Selbstentdeckungs-Ergebnisse</h2>
    <p class="result-subtitle">Vielen Dank für Ihre Teilnahme. Hier ist Ihre ressourcenorientierte Auswertung.</p>

    <article class="result-card result-main">
        <h3><?php echo htmlspecialchars($testData['title']); ?></h3>
        <p class="result-module-desc"><?php echo htmlspecialchars($testData['description']); ?></p>

        <div class="score-visualization">
            <svg viewBox="0 0 100 100" class="score-circle">
                <circle cx="50" cy="50" r="45" class="score-bg"/>
                <circle cx="50" cy="50" r="45" class="score-fill" style="stroke-dasharray: <?php echo $stats['percentage'] * 2.827; ?> 282.7;"/>
                <text x="50" y="45" class="score-value"><?php echo $score; ?></text>
                <text x="50" y="60" class="score-label">von <?php echo $stats['max']; ?></text>
            </svg>
            <div class="score-meta">
                <p class="score-pct"><?php echo $stats['percentage']; ?>%</p>
                <p class="score-text">Ihr Profil</p>
            </div>
        </div>

        <div class="interpretation-box">
            <h4>📊 Ihre Auswertung</h4>
            <p class="interpretation-text"><?php echo htmlspecialchars($interpretation); ?></p>
            <p class="interpretation-note">💡 Neurodiversität bedeutet: unterschiedliche neuronale Verarbeitung ist menschlich und wertvoll.</p>
        </div>
    </article>

    <article class="result-card answer-review">
        <h3>Ihre Antworten im Überblick</h3>
        <div class="answer-list">
            <?php foreach ($details as $detail): ?>
            <div class="answer-item">
                <div class="answer-question">
                    <strong><?php echo htmlspecialchars($detail['qText']); ?></strong>
                </div>
                <div class="answer-selection">
                    <span class="answer-text"><?php echo $detail['text'] ? htmlspecialchars($detail['text']) : '(nicht beantwortet)'; ?></span>
                    <?php if ($detail['score'] !== null): ?>
                    <span class="answer-score">+<?php echo $detail['score']; ?> Punkt<?php echo $detail['score'] !== 1 ? 'e' : ''; ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </article>

    <article class="result-card resources-box">
        <h3>🌱 Nächste Schritte</h3>
        <ul>
            <li>Reflektieren Sie Ihre Ergebnisse mit einem Vertrauensperson.</li>
            <li>Erkunden Sie die <a href="../resources.php">Ressourcen</a> zu Neurodiversität und Selbstfürsorge.</li>
            <li>Diese Ergebnisse ersetzen nicht eine professionelle Diagnose – konsultieren Sie bei Bedarf einen Fachmann.</li>
            <li>Ihre neurodivergenten Stärken sind ein Teil Ihrer Einzigartigkeit!</li>
        </ul>
    </article>

    <div class="action-buttons">
        <a href="../diagnostics.php" class="btn">Weitere Tests erkunden</a>
        <a href="../index.php" class="btn">Zur Startseite</a>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
