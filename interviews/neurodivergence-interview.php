<?php
declare(strict_types=1);

$pageTitle = 'Neurodivergenz Interview';
require_once __DIR__ . '/../includes/process-repository.php';
include '../includes/header.php';

[$interviewData, $error] = ndRepoLoadAndValidateInstrument('neurodivergence-interview');
if ($interviewData === null) {
    echo '<p>Interview-Daten konnten nicht geladen werden. ';
    echo htmlspecialchars((string) $error);
    echo '</p>';
    include '../includes/footer.php';
    exit;
}

$title = isset($interviewData['title']) && is_string($interviewData['title'])
    ? $interviewData['title']
    : 'Neurodivergenz Interview';
$description = isset($interviewData['description']) && is_string($interviewData['description'])
    ? $interviewData['description']
    : '';
$questions = isset($interviewData['questions']) && is_array($interviewData['questions'])
    ? $interviewData['questions']
    : [];

echo '<h2>' . htmlspecialchars($title) . '</h2>';
if ($description !== '') {
    echo '<p>' . htmlspecialchars($description) . '</p>';
}

echo '<form action="' . htmlspecialchars(basename((string) $_SERVER['PHP_SELF'])) . '" method="post" class="interview-form">';
foreach ($questions as $index => $question) {
    if (!is_array($question)) {
        continue;
    }

    $questionId = isset($question['id']) && is_string($question['id']) && trim($question['id']) !== ''
        ? trim($question['id'])
        : 'q' . ($index + 1);
    $questionText = isset($question['text']) && is_string($question['text']) && trim($question['text']) !== ''
        ? trim($question['text'])
        : 'Frage ' . ($index + 1);
    $questionType = isset($question['type']) && is_string($question['type'])
        ? strtolower(trim($question['type']))
        : 'open';
    $fieldName = 'q_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $questionId);

    echo '<div class="question">';
    echo '<p>' . htmlspecialchars($questionText) . '</p>';

    if ($questionType === 'yesno' && isset($question['options']) && is_array($question['options']) && !empty($question['options'])) {
        foreach ($question['options'] as $option) {
            if (!is_string($option) || trim($option) === '') {
                continue;
            }
            $cleanOption = trim($option);
            echo '<label><input type="radio" name="' . htmlspecialchars($fieldName) . '" value="' . htmlspecialchars($cleanOption) . '"> ' . htmlspecialchars($cleanOption) . '</label>';
        }
    } else {
        echo '<textarea name="' . htmlspecialchars($fieldName) . '" rows="3"></textarea>';
    }

    echo '</div>';
}
echo '<button type="submit" class="btn">Interview abschließen</button>';
echo '</form>';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo '<p>Danke für Ihre Teilnahme am Interview. Die Eingaben wurden übermittelt.</p>';
}

include '../includes/footer.php';
