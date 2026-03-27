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
        foreach ($question['options'] as $idx => $option) {
            $encVal = $option['score'] . '|' . base64_encode($option['text']);
            echo '<label><input type="radio" name="q' . $question['id'] . '" value="' . htmlspecialchars($encVal) . '" required> ' . htmlspecialchars($option['text']) . '</label><br>';
        }
        echo '</div>';
    }
    echo '<button type="submit" class="btn">Test abschließen</button>';
    echo '</form>';
}

function parseAnswerValue($value) {
    $parts = explode('|', $value, 2);
    if (count($parts) !== 2) return null;
    return ['score' => (int)$parts[0], 'text' => base64_decode($parts[1], true) ?: ''];
}

function calculateScore($testData) {
    $score = 0;
    foreach ($testData['questions'] as $question) {
        $key = 'q' . $question['id'];
        if (isset($_POST[$key])) {
            $p = parseAnswerValue($_POST[$key]);
            if ($p) $score += $p['score'];
        }
    }
    return $score;
}

function getAnswerDetails($testData) {
    $details = [];
    foreach ($testData['questions'] as $question) {
        $key = 'q' . $question['id'];
        $ans = ['qId' => $question['id'], 'qText' => $question['text'], 'score' => null, 'text' => null];
        if (isset($_POST[$key])) {
            $p = parseAnswerValue($_POST[$key]);
            if ($p) { $ans['score'] = $p['score']; $ans['text'] = $p['text']; }
        }
        $details[] = $ans;
    }
    return $details;
}

function interpretScore($score, $norms) {
    if (!isset($norms['ranges']) || !is_array($norms['ranges'])) {
        return 'Keine Normwerte verfügbar.';
    }
    foreach ($norms['ranges'] as $range => $message) {
        $parts = explode('-', $range);
        if (count($parts) !== 2) continue;
        $min = (int) trim($parts[0]);
        $max = (int) trim($parts[1]);
        if ($score >= $min && $score <= $max) return $message;
    }
    return 'Score außerhalb des gültigen Bereichs.';
}

function getScoreStats($score, $testData) {
    $maxScore = 0;
    foreach ($testData['questions'] as $q) {
        $maxScore += max(array_column($q['options'], 'score'));
    }
    $pct = $maxScore > 0 ? round(($score / $maxScore) * 100, 1) : 0;
    return ['max' => $maxScore, 'current' => $score, 'percentage' => $pct];
}
?>