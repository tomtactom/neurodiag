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

function getSmartRecommendations($module, $score, $percentage) {
    // Module metadata mit Symptom-Overlap Profilen
    $modules = [
        'aq-test' => [
            'title' => 'Autismus',
            'category' => 'social',
            'icon' => 'https://cdn-icons-png.flaticon.com/512/9990/9990347.png',
            'recommendations' => [
                'high' => [
                    ['module' => 'asrs-test', 'reason' => 'Oft co-präsent mit Autismus: Aufmerksamkeit & exekutive Funktionen'],
                    ['module' => 'dyspraxia-test', 'reason' => 'Motorische Koordination ist häufig assoziiert'],
                    ['module' => 'dld-test', 'reason' => 'Sprachverarbeitung kann überlappen']
                ],
                'medium' => [
                    ['module' => 'dyslexia-test', 'reason' => 'Visuelle Verarbeitung kann verbunden sein']
                ]
            ]
        ],
        'asrs-test' => [
            'title' => 'ADHS',
            'category' => 'attention',
            'icon' => 'https://cdn-icons-png.flaticon.com/512/10371/10371961.png',
            'recommendations' => [
                'high' => [
                    ['module' => 'aq-test', 'reason' => 'ADHS + Autismus Overlap: Viele teilen beide Profile'],
                    ['module' => 'dyslexia-test', 'reason' => 'Aufmerksamkeit beeinflusst Leseverarbeitung'],
                    ['module' => 'dyspraxia-test', 'reason' => 'Motorische Planung oft beeinträchtigt']
                ],
                'medium' => [
                    ['module' => 'dyskalkulie-test', 'reason' => 'Numerische Aufmerksamkeit kann betroffen sein']
                ]
            ]
        ],
        'dyslexia-test' => [
            'title' => 'Dyslexie',
            'category' => 'language',
            'icon' => 'https://cdn-icons-png.flaticon.com/512/1993/1993497.png',
            'recommendations' => [
                'high' => [
                    ['module' => 'dysgraphia-test', 'reason' => 'Lesen und Schreiben sind oft verbunden'],
                    ['module' => 'dyskalkulie-test', 'reason' => 'Zahlensymbol-Verarbeitung ähnlich betroffen'],
                    ['module' => 'dld-test', 'reason' => 'Sprachliche Grundlagen können überlappen']
                ],
                'medium' => [
                    ['module' => 'asrs-test', 'reason' => 'Aufmerksamkeit beeinflusst Leseerfolg']
                ]
            ]
        ],
        'dysgraphia-test' => [
            'title' => 'Dysgraphie',
            'category' => 'motor',
            'icon' => 'https://cdn-icons-png.flaticon.com/512/18448/18448228.png',
            'recommendations' => [
                'high' => [
                    ['module' => 'dyslexia-test', 'reason' => 'Lesen und Schreiben sind neurologisch verbunden'],
                    ['module' => 'dyspraxia-test', 'reason' => 'Motorische Planung beeinflusst Schreibfluss'],
                    ['module' => 'aq-test', 'reason' => 'Feinmotorik-Herausforderungen oft bei Autismus']
                ],
                'medium' => [
                    ['module' => 'dld-test', 'reason' => 'Sprachexpression kann mit Schreiben verbunden sein']
                ]
            ]
        ],
        'dyskalkulie-test' => [
            'title' => 'Dyskalkulie',
            'category' => 'language',
            'icon' => 'https://cdn-icons-png.flaticon.com/512/1993/1993497.png',
            'recommendations' => [
                'high' => [
                    ['module' => 'dyslexia-test', 'reason' => 'Symbol-Verarbeitung betrifft Zahlen wie Buchstaben'],
                    ['module' => 'asrs-test', 'reason' => 'Aufmerksamkeit notwendig für mathematisches Verständnis'],
                    ['module' => 'dld-test', 'reason' => 'Numerische Konzepte sprachlich gebunden']
                ],
                'medium' => [
                    ['module' => 'dysgraphia-test', 'reason' => 'Schreiben von Zahlen kann beeinträchtigt sein']
                ]
            ]
        ],
        'dyspraxia-test' => [
            'title' => 'Dyspraxie',
            'category' => 'motor',
            'icon' => 'https://cdn-icons-png.flaticon.com/512/18448/18448228.png',
            'recommendations' => [
                'high' => [
                    ['module' => 'aq-test', 'reason' => 'Autistische Merkmale und motorische Planung oft verbunden'],
                    ['module' => 'dysgraphia-test', 'reason' => 'Motorische Koordination beeinflusst Schreiben'],
                    ['module' => 'asrs-test', 'reason' => 'Exekutive Funktionen beeinträchtigt']
                ],
                'medium' => [
                    ['module' => 'dld-test', 'reason' => 'Sprechplanung (Sprachpraxie) verwandt']
                ]
            ]
        ],
        'tic-test' => [
            'title' => 'Tics/Tourette',
            'category' => 'motor',
            'icon' => 'https://cdn-icons-png.flaticon.com/512/18448/18448228.png',
            'recommendations' => [
                'high' => [
                    ['module' => 'asrs-test', 'reason' => 'ADHS und Tics häufig co-präsent'],
                    ['module' => 'aq-test', 'reason' => 'Neuronale Ähnlichkeiten können vorhanden sein'],
                    ['module' => 'dld-test', 'reason' => 'Vokalische Tics betreffen Sprachpraxie']
                ],
                'medium' => [
                    ['module' => 'dyspraxia-test', 'reason' => 'Motorische Kontrolle relevant']
                ]
            ]
        ],
        'dld-test' => [
            'title' => 'Sprachstörung',
            'category' => 'language',
            'icon' => 'https://cdn-icons-png.flaticon.com/512/1993/1993497.png',
            'recommendations' => [
                'high' => [
                    ['module' => 'dyslexia-test', 'reason' => 'Sprach- und Leseverarbeitung verbunden'],
                    ['module' => 'aq-test', 'reason' => 'Pragmatische Aspekte bei Autismus relevant'],
                    ['module' => 'asrs-test', 'reason' => 'Aufmerksamkeit notwendig für Sprachentwicklung']
                ],
                'medium' => [
                    ['module' => 'dyspraxia-test', 'reason' => 'Sprechpraxie kann betroffen sein']
                ]
            ]
        ]
    ];

    $recs = [];
    if (isset($modules[$module])) {
        $recList = $modules[$module]['recommendations'];
        
        // Priorität nach Score-Höhe
        if ($percentage >= 60) {
            $recs = $recList['high'] ?? [];
        } else {
            $recs = array_merge($recList['high'] ?? [], $recList['medium'] ?? []);
        }
    }

    return [
        'title' => $modules[$module]['title'] ?? 'Profil',
        'recommendations' => array_slice($recs, 0, 3), // Max 3 Empfehlungen
        'hasMore' => count($recs) > 3
    ];
}

function getModuleTitle($moduleId) {
    $titles = [
        'aq-test' => 'Autismus',
        'asrs-test' => 'ADHS',
        'dyslexia-test' => 'Dyslexie',
        'dysgraphia-test' => 'Dysgraphie',
        'dyskalkulie-test' => 'Dyskalkulie',
        'dyspraxia-test' => 'Dyspraxie',
        'tic-test' => 'Tics/Tourette',
        'dld-test' => 'Sprachstörung'
    ];
    return $titles[$moduleId] ?? 'Modul';
}

function getModuleSequenceRecommendations() {
    // Diese Funktion wird vom JavaScript aufgerufen via API
    // und gibt personalisierte Sequenzen basierend auf Testhistorie zurück
    
    $sequenceMap = [
        'aq-test' => ['asrs-test', 'dyspraxia-test', 'dld-test'],
        'asrs-test' => ['aq-test', 'dyslexia-test', 'dyspraxia-test'],
        'dyslexia-test' => ['dysgraphia-test', 'dyskalkulie-test', 'dld-test'],
        'dysgraphia-test' => ['dyslexia-test', 'dyspraxia-test', 'dld-test'],
        'dyskalkulie-test' => ['dyslexia-test', 'asrs-test', 'dld-test'],
        'dyspraxia-test' => ['aq-test', 'dysgraphia-test', 'asrs-test'],
        'tic-test' => ['asrs-test', 'aq-test', 'dld-test'],
        'dld-test' => ['dyslexia-test', 'aq-test', 'asrs-test']
    ];
    
    return $sequenceMap;
}
?>
