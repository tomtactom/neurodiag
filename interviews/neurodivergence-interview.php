<?php
$pageTitle = 'Neurodivergenz Interview';
include '../includes/header.php';
include '../includes/test-functions.php';

$interviewData = loadTestData('neurodivergence-interview');
if (!$interviewData) {
    echo '<p>Interview-Daten konnten nicht geladen werden.</p>';
    include '../includes/footer.php';
    exit;
}

echo '<h2>' . htmlspecialchars($interviewData['title']) . '</h2>';
echo '<p>' . htmlspecialchars($interviewData['description']) . '</p>';
echo '<form action="' . basename($_SERVER['PHP_SELF']) . '" method="post" class="interview-form">';
foreach ($interviewData['questions'] as $question) {
    echo '<div class="question">';
    echo '<p>' . htmlspecialchars($question['text']) . '</p>';
    if ($question['type'] === 'open') {
        echo '<textarea name="q' . $question['id'] . '" rows="3"></textarea>';
    } elseif ($question['type'] === 'yesno') {
        foreach ($question['options'] as $option) {
            echo '<label><input type="radio" name="q' . $question['id'] . '" value="' . htmlspecialchars($option) . '"> ' . htmlspecialchars($option) . '</label>';
        }
    }
    echo '</div>';
}
echo '<button type="submit" class="btn">Interview abschließen</button>';
echo '</form>';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo '<p>Danke für Ihre Teilnahme am Interview. Die Ergebnisse werden qualitatativ analysiert.</p>';
}

include '../includes/footer.php';
?>