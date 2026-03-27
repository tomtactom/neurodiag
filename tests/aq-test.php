<?php
$pageTitle = 'AQ-Test';
include '../includes/header.php';
include '../includes/test-functions.php';

$testData = loadTestData('aq-test');
if (!$testData) {
    echo '<p>Test-Daten konnten nicht geladen werden.</p>';
    include '../includes/footer.php';
    exit;
}

generateTestForm($testData);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $score = calculateScore($testData);
    $interpretation = interpretScore($score, $testData['norms']);
    echo "<p>Ihr AQ-Score: $score</p>";
    echo "<p>$interpretation</p>";
}

include '../includes/footer.php';
?>