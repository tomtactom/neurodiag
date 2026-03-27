<?php $pageTitle = 'Diagnostik'; include 'includes/header.php'; ?>

        <h2>Diagnostik-Module</h2>
        <p>Wählen Sie ein Diagnostik-Modul aus, um mit der Bewertung zu beginnen:</p>
        <div class="diagnostic-modules">
            <div class="module">
                <h3>Autismus-Spektrum (AQ-Test)</h3>
                <p>Bewerten Sie Merkmale des Autismus-Spektrums.</p>
                <a href="tests/aq-test.php" class="btn">Starten</a>
            </div>
            <div class="module">
                <h3>ADHS (ASRS-Test)</h3>
                <p>Screening für Aufmerksamkeitsdefizit-Hyperaktivitätsstörung.</p>
                <a href="tests/asrs-test.php" class="btn">Starten</a>
            </div>
            <div class="module">
                <h3>Dyslexie-Test</h3>
                <p>Bewertung von Lese- und Schreibschwierigkeiten.</p>
                <a href="tests/dyslexia-test.php" class="btn">Starten</a>
            </div>
            <div class="module">
                <h3>Dyspraxie-Test</h3>
                <p>Beurteilung motorischer Koordinationsstörungen.</p>
                <a href="tests/dyspraxia-test.php" class="btn">Starten</a>
            </div>
            <div class="module">
                <h3>Neurodivergenz Interview</h3>
                <p>Strukturiertes Interview zur Erfassung von Merkmalen.</p>
                <a href="interviews/neurodivergence-interview.php" class="btn">Starten</a>
            </div>
        </div>

<?php include 'includes/footer.php'; ?>