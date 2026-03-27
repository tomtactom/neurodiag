<?php $pageTitle = 'Startseite'; include 'includes/header.php'; ?>

        <section class="hero">
            <h2>Verstehe deine eigene neurodivergente Natur</h2>
            <p>Neurodivergenz ist eine neurobiologische Variation – kein Defizit. NeuroDiag unterstützt dich bei der selbstbestimmten Erkundung deiner Stärken und Verarbeitungsmuster. In deinem Tempo, ohne externe Gatekeeper.</p>
            <p class="hero-emphasis">Diese Selbstchecks ersetzen keine Diagnose, aber: Du kennst deine eigenen Muster am besten.</p>
        </section>

        <section class="empowerment-section">
            <h3>Selbstverständnis entwickeln</h3>
            <p>Erkenne deine neurodivergenten Stärken und Verarbeitungsstile durch systematische Selbstreflexion. Evidenzbasiert und ressourcenorientiert.</p>
            
            <article class="empowerment-card">
                <h4>Neurodivergenz als Variation</h4>
                <p>Neurodivergenz beschreibt unterschiedliche neurologische Verarbeitungsmuster. Menschen mit Neurodivergenz:</p>
                <ul class="empowerment-list">
                    <li>verarbeiten Informationen in charakteristischen Mustern</li>
                    <li>verfügen über spezifische Stärken und Herausforderungen</li>
                    <li>haben denselben Wert wie alle anderen Menschen</li>
                    <li>können ihre eigenen Erfahrungen selbst beschreiben und deuten</li>
                </ul>
            </article>

            <article class="empowerment-card">
                <h4>Selbsterkenntnis im Fokus</h4>
                <p>Viele neurodivergente Menschen entwickeln Selbstverständnis, bevor eine formale Diagnose erfolgt. Dies ist ein legitimer Weg zu Selbsterkenntnis:</p>
                <ul class="empowerment-list">
                    <li>deine persönliche Erfahrung liefert zuverlässige Informationen über deine Muster</li>
                    <li>professionelle Diagnostik ist optional und kann später folgen</li>
                    <li>Selbsterkenntnis ist Grundlage für gezielte Unterstützung</li>
                    <li>deine Selbstbeschreibung hat Aussagekraft – unabhängig von externen Bestätigungen</li>
                </ul>
            </article>
        </section>

        <section class="interactive-check">
            <h3>Selbstreflexion: Erste Orientierung</h3>
            <p>Erkenne Muster in deinen Verarbeitungsstilen. Diese Fragen unterstützen deine Selbstreflexion:</p>
            
            <div class="check-widget" id="selfCheckWidget">
                <div class="check-item">
                    <input type="checkbox" id="check1" class="check-input">
                    <label for="check1" class="check-label">
                        <span class="check-text">Meine Informationsverarbeitung unterscheidet sich von typischen Mustern</span>
                    </label>
                </div>
                <div class="check-item">
                    <input type="checkbox" id="check2" class="check-input">
                    <label for="check2" class="check-label">
                        <span class="check-text">Ich erkenne stabile Muster in meinem Denken, meiner Aufmerksamkeit oder meiner Motorik</span>
                    </label>
                </div>
                <div class="check-item">
                    <input type="checkbox" id="check3" class="check-input">
                    <label for="check3" class="check-label">
                        <span class="check-text">Diese Muster sind Teil meiner Identität und Funktionsweise</span>
                    </label>
                </div>
                <div class="check-item">
                    <input type="checkbox" id="check4" class="check-input">
                    <label for="check4" class="check-label">
                        <span class="check-text">Ich möchte diese Muster systematisch verstehen</span>
                    </label>
                </div>
                <div class="check-result" id="checkResult" style="display:none;">
                    <p class="result-text">Deine Selbstreflexion hat Muster erkannt. Die detaillierten Tests unten bieten tiefere Einblicke in spezifische Bereiche.</p>
                </div>
            </div>
        </section>

        <section class="features">
            <h3>Systematische Selbsterkundung</h3>
            <p class="feature-intro">Erkunde die Bereiche, die für dich relevant sind. Jeder Test bietet detaillierte, ressourcenorientierte Auswertungen:</p>
            <ul>
                <li><strong>Autismus-Spektrum (ASS)</strong> – Wahrnehmung & soziale Verarbeitung</li>
                <li><strong>AD(H)S</strong> – Aufmerksamkeit & Aktivierungsstile</li>
                <li><strong>Dyslexie / LRS</strong> – Lese- & Schreibmuster</li>
                <li><strong>Dysgraphie</strong> – Schrift- & Feinmotorik</li>
                <li><strong>Dyskalkulie</strong> – Mathematische Denkweisen</li>
                <li><strong>Dyspraxie / DCD</strong> – Koordinationsstile</li>
                <li><strong>Tic-Störungen / Tourette</strong> – Reaktionsmuster</li>
                <li><strong>Sprachentwicklungsstörung (DLD)</strong> – Sprachverarbeitung</li>
            </ul>
            <p class="feature-note"><strong>Neurodiversitätsperspektive:</strong> Neurologische Unterschiede sind Variation – nicht Mangel. Du entscheidest, welche Bereiche für deine Selbsterkenntnis relevant sind.</p>
        </section>

        <section class="call-to-action">
            <a href="diagnostics.php" class="btn btn-large">Selbstchecks durchführen</a>
            <p class="cta-note">Selbstbestimmt erkunden. Kostenlos und anonym.</p>
        </section>

<?php include 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.check-input');
    const checkResult = document.getElementById('checkResult');
    
    function updateCheckResult() {
        const checkedCount = document.querySelectorAll('.check-input:checked').length;
        if (checkedCount >= 3) {
            checkResult.style.display = 'block';
            checkResult.classList.add('fade-in');
        } else {
            checkResult.style.display = 'none';
        }
    }
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateCheckResult);
    });
});
</script>