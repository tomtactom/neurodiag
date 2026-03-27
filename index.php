<?php $pageTitle = 'Startseite'; include 'includes/header.php'; ?>

        <section class="hero">
            <h2>Du bist der Experte für dich selbst</h2>
            <p>Neurodivergenz ist nicht pathologisch – sie ist einfach eine andere Art, die Welt wahrzunehmen und zu verarbeiten. NeuroDiag gibt dir Werkzeuge zur <strong>Selbstentdeckung</strong> in deinem eigenem Tempo. Ohne Abhängigkeit von Autoritäten.</p>
            <p class="hero-emphasis">💡 Diese Tests ersetzen keine professionelle Diagnose. Aber: Du kennst dich selbst am besten.</p>
        </section>

        <section class="empowerment-section">
            <h3>Deine Selbstwirksamkeit</h3>
            <p>Erkenne deine neurodivergenten Stärken und Verarbeitungsstile eigenverantwortlich. Das ist nicht Pathologie – es ist Wirklichkeit.</p>
            
            <article class="empowerment-card">
                <h4>🧠 Was bedeutet Neurodivergenz wirklich?</h4>
                <p>Neurodivergenz ist eine neurobiologische Variation. Menschen mit Neurodivergenz:</p>
                <ul class="empowerment-list">
                    <li>Verarbeiten Informationen auf unterschiedliche Weise</li>
                    <li>Haben einzigartige Stärken und Herausforderungen</li>
                    <li>Sind genauso wertvoll wie neurotypische Menschen</li>
                    <li>Können ihre eigenen Erfahrungen selbst beschreiben</li>
                </ul>
            </article>

            <article class="empowerment-card">
                <h4>✨ Warum Selbstdiagnose wichtig ist</h4>
                <p><strong>Viele neurodivergente Menschen erkennen sich selbst BEVOR ein Fachmann sie diagnostiziert.</strong></p>
                <ul class="empowerment-list">
                    <li>Du hast gelebt mit deiner Neurodivergenz – du kennst sie am besten</li>
                    <li>Professionelle Diagnose kann später kommen (wenn gewünscht)</li>
                    <li>Selbstverständnis ist der erste Schritt zur Selbstakzeptanz</li>
                    <li>Deine Perspektive ist legitim – ohne externe Validierung</li>
                </ul>
            </article>
        </section>

        <section class="interactive-check">
            <h3>Erste Orientierung: Selbstcheck</h3>
            <p>Diese 4 Fragen helfen dir, dich selbst besser zu verstehen. Es geht nicht um Ja/Nein, sondern um Selbstreflexion.</p>
            
            <div class="check-widget" id="selfCheckWidget">
                <div class="check-item">
                    <input type="checkbox" id="check1" class="check-input">
                    <label for="check1" class="check-label">
                        <span class="check-text">Ich verarbeite Informationen anders als die meisten um mich herum</span>
                    </label>
                </div>
                <div class="check-item">
                    <input type="checkbox" id="check2" class="check-input">
                    <label for="check2" class="check-label">
                        <span class="check-text">Ich habe stabile Patterns in meinem Denken, meiner Aufmerksamkeit oder meiner Motorik</span>
                    </label>
                </div>
                <div class="check-item">
                    <input type="checkbox" id="check3" class="check-input">
                    <label for="check3" class="check-label">
                        <span class="check-text">Diese Unterschiede sind ein Teil meiner Identität (keine Krankheit)</span>
                    </label>
                </div>
                <div class="check-item">
                    <input type="checkbox" id="check4" class="check-input">
                    <label for="check4" class="check-label">
                        <span class="check-text">Ich möchte diese Unterschiede besser verstehen</span>
                    </label>
                </div>
                <div class="check-result" id="checkResult" style="display:none;">
                    <p class="result-text">✨ Deine Selbstreflexion hat dir Klarheit gegeben. Die Tests unten können dir helfen, noch tiefer zu verstehen.</p>
                </div>
            </div>
        </section>

        <section class="features">
            <h3>Neurodivergente Bereiche – erkunde deine Vielfalt</h3>
            <p class="feature-intro">Wähle einen Bereich, der dich interessiert. Jeder Test bietet dir ressourcenorientierte Einblicke, nicht Urteile.</p>
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
            <p class="feature-note">💙 <strong>Basierend auf Neurodiversitätsprinzipien:</strong> Unterschiede sind Variation, nicht Pathologie. Du bestimmst, was für dich relevant ist.</p>
        </section>

        <section class="call-to-action">
            <a href="diagnostics.php" class="btn btn-large">Starte deine Selbstentdeckung</a>
            <p class="cta-note">Eigenverantwortlich. In deinem Tempo. Ohne externe Validierung erforderlich.</p>
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