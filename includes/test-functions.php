<?php
function loadTestData($filename) {
    $path = __DIR__ . '/../data/' . $filename . '.json';
    if (file_exists($path)) {
        return json_decode(file_get_contents($path), true);
    }
    return null;
}

function generateTestForm($testData) {
    echo '<h2>' . htmlspecialchars($testData['title']) . '</h2>';
    echo '<p>' . htmlspecialchars($testData['description']) . '</p>';
    echo '<form action="' . basename($_SERVER['PHP_SELF']) . '" method="post" class="test-form">';
    foreach ($testData['questions'] as $question) {
        echo '<div class="question">';
        echo '<p>' . htmlspecialchars($question['text']) . '</p>';
        foreach ($question['options'] as $option) {
            echo '<label><input type="radio" name="q' . $question['id'] . '" value="' . $option['score'] . '" required> ' . htmlspecialchars($option['text']) . '</label><br>';
        }
        echo '</div>';
    }
    echo '<button type="submit" class="btn">Test abschließen</button>';
    echo '</form>';
}

function calculateScore($testData) {
    $score = 0;
    foreach ($testData['questions'] as $question) {
        $key = 'q' . $question['id'];
        if (isset($_POST[$key])) {
            $score += (int)$_POST[$key];
        }
    }
    return $score;
}

function interpretScore($score, $norms) {
    if ($score >= $norms['threshold']) {
        return $norms['ranges']['32-50'] ?? 'Möglicher Hinweis auf Merkmale. Konsultieren Sie einen Fachmann.';
    } else {
        return $norms['ranges']['0-31'] ?? 'Kein Hinweis auf Merkmale.';
    }
}
?>