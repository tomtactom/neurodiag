<?php
$pageTitle = 'Neurodivergenz-Test';
include '../includes/header.php';
include '../includes/test-functions.php';

$module = $_GET['module'] ?? null;
if (!$module) {
    echo '<p>Kein Modul ausgewählt. Bitte wählen Sie ein Modul aus der Diagnostik-Übersicht.</p>';
    include '../includes/footer.php';
    exit;
}

$allowed = [
    'aq-test', 'asrs-test', 'dyslexia-test', 'dysgraphia-test', 'dyskalkulie-test', 'dyspraxie-test', 'tic-test', 'dld-test'
];

if (!in_array($module, $allowed, true)) {
    echo '<p>Ungültiges Modul. Bitte wählen Sie ein gültiges Modul aus.</p>';
    include '../includes/footer.php';
    exit;
}

$testData = loadTestData($module);
if (!$testData) {
    echo '<p>Test-Daten konnten nicht geladen werden.</p>';
    include '../includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Location: results.php?module=' . urlencode($module), true, 303);
    $_SESSION = array_merge($_SESSION ?? [], $_POST);
    exit;
}
?>

<section class="test-container">
    <div class="test-header">
        <h2><?php echo htmlspecialchars($testData['title']); ?></h2>
        <p><?php echo htmlspecialchars($testData['description']); ?></p>
    </div>

    <div class="progress-bar">
        <div class="progress-fill" id="progressBar"></div>
    </div>

    <?php generateTestForm($testData); ?>

    <p class="test-footer-note">💡 Es gibt keine "richtigen" oder "falschen" Antworten. Beantworten Sie ehrlich, wie Sie sich selbst wahrnehmen.</p>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const questions = document.querySelectorAll('.question');
    const progressBar = document.getElementById('progressBar');
    const form = document.querySelector('.test-form');
    
    function updateProgress() {
        const answered = document.querySelectorAll('input[type="radio"]:checked').length;
        const progress = (answered / questions.length) * 100;
        progressBar.style.width = progress + '%';
    }
    
    document.querySelectorAll('input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', updateProgress);
    });
    
    form.addEventListener('submit', function(e) {
        const allAnswered = document.querySelectorAll('input[type="radio"]:checked').length === questions.length;
        if (!allAnswered) {
            e.preventDefault();
            alert('Bitte beantworten Sie alle Fragen bevor Sie fortfahren.');
        }
    });
});
</script>

<?php include '../includes/footer.php'; ?>