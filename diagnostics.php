<?php $pageTitle = 'Selbstentdeckung'; include 'includes/header.php'; ?>

<!-- Test Preview Modal -->
<div id="previewModal" class="preview-modal" style="display:none;">
  <div class="preview-modal-content">
    <button class="preview-close" aria-label="Modal schließen">&times;</button>
    <h2 id="previewTitle">Test-Vorschau</h2>
    <div id="previewQuestions" class="preview-questions">
      <!-- Wird dynamisch gefüllt -->
    </div>
    <div class="preview-actions">
      <button id="previewStartBtn" class="btn btn-primary">Test starten</button>
      <button class="preview-cancel-btn btn btn-secondary">Schließen</button>
    </div>
  </div>
</div>

<section class="diagnostics-hero">
  <div class="hero-content">
    <p class="hero-pretitle">NeuroDiag</p>
    <h1>Fokussierte Selbstorientierung in 8 Neurodivergenz-Bereichen</h1>
    <p>Klar, reduziert und praxisnah: Wähle einen Bereich, lies die Details und starte bei Bedarf direkt mit einer Vorschau.</p>
    <a href="#module-selector" class="btn btn-primary">8 Bereiche ansehen</a>
  </div>
  <div class="hero-illustration" aria-hidden="true">
    <img src="https://cdn.pixabay.com/photo/2022/04/13/21/48/brain-7131241_640.png" alt="Abstrakte Darstellung eines Gehirns mit neuralen Verbindungen" loading="lazy">
  </div>
</section>

<section id="status-overview" class="diagnostics-status">
  <div class="status-header">
    <h2>Deine Orientierung</h2>
    <p>Verfolge den Testfortschritt, finde passende Angebote und erkenne Muster in deinem Erleben.</p>
  </div>
  <div class="status-progress" aria-live="polite" aria-atomic="true">
    <span id="statusCount">Bereit für deine Selbstentdeckung? Starten wir!</span>
    <div class="progress-bar" aria-hidden="true"><div id="progressFill"></div></div>
  </div>
</section>

<section id="module-selector" class="module-selector">
  <h2>Die 8 Neurodivergenz-Bereiche</h2>
  <p>Jede Karte zeigt kurz den Fokus, in <strong>Details</strong> konkrete alltagsnahe Hinweise und in der <strong>Vorschau</strong> typische Fragen.</p>
  <div class="module-grid">
    <article class="module-card" data-category="social">
      <img src="https://cdn-icons-png.flaticon.com/512/9990/9990347.png" alt="Autismus Icon" loading="lazy">
      <h3>Autismus</h3>
      <p class="brief">Klare, intensive Wahrnehmung und direkter Ausdruck. Struktur und tiefes Verstehen sind zentral.</p>
      <button class="expand-btn" aria-expanded="false" aria-label="Mehr über Autismus">Details</button>
      <div class="module-details" hidden>
        <p><strong>Fokus:</strong> Reizverarbeitung, soziale Signale und Bedürfnis nach Klarheit.</p>
        <p><strong>VT-orientierte Mikrostrategie:</strong> Vor sozialen Situationen ein kurzes "Wenn-dann"-Skript notieren (z. B. "Wenn es laut wird, dann mache ich 2 Minuten Pause").</p>
      </div>
      <button class="btn-preview" data-module="aq-test" aria-label="Vorschau des Autismus-Tests">📋 Vorschau</button>
      <a href="process.php?process=aq-test" class="btn btn-secondary">Starten</a>
    </article>
    <article class="module-card" data-category="attention">
      <img src="https://cdn-icons-png.flaticon.com/512/10371/10371961.png" alt="ADHS Icon" loading="lazy">
      <h3>AD(H)S</h3>
      <p class="brief">Innere Bewegung, schnelle Gedankensprünge, Kreativität. Flexibilität statt starrer Struktur.</p>
      <button class="expand-btn" aria-expanded="false" aria-label="Mehr über ADHS">Details</button>
      <div class="module-details" hidden>
        <p><strong>Fokus:</strong> Aufmerksamkeit, Impulssteuerung und innere Unruhe.</p>
        <p><strong>VT-orientierte Mikrostrategie:</strong> Aufgaben in 10-Minuten-Blöcke teilen, Startbarrieren mit einem sichtbaren ersten Minischritt reduzieren.</p>
      </div>
      <button class="btn-preview" data-module="asrs-test" aria-label="Vorschau des ADHS-Tests">📋 Vorschau</button>
      <a href="process.php?process=asrs-test" class="btn btn-secondary">Starten</a>
    </article>
    <article class="module-card" data-category="language">
      <img src="https://cdn-icons-png.flaticon.com/512/1993/1993497.png" alt="Dyslexie Icon" loading="lazy">
      <h3>Dyslexie</h3>
      <p class="brief">Ganzheitliche, bildhaft-zusammenhängende Wahrnehmung. Schrift braucht andere Wege.</p>
      <button class="expand-btn" aria-expanded="false" aria-label="Mehr über Dyslexie">Details</button>
      <div class="module-details" hidden>
        <p><strong>Fokus:</strong> Lesefluss, Rechtschreibung und sprachliche Verarbeitung unter Zeitdruck.</p>
        <p><strong>VT-orientierte Mikrostrategie:</strong> Reframing bei Fehlern: "Fehler sind Daten" und anschließend 1 konkrete Anpassung für den nächsten Versuch festhalten.</p>
      </div>
      <button class="btn-preview" data-module="dyslexia-test" aria-label="Vorschau des Dyslexie-Tests">📋 Vorschau</button>
      <a href="process.php?process=dyslexia-test" class="btn btn-secondary">Starten</a>
    </article>
    <article class="module-card" data-category="motor">
      <img src="https://cdn-icons-png.flaticon.com/512/18448/18448228.png" alt="Dysgraphie Icon" loading="lazy">
      <h3>Dysgraphie</h3>
      <p class="brief">Gedanken sind da, Schreiben braucht Zeit. Mündlich, visuell oder digital oft einfacher.</p>
      <button class="expand-btn" aria-expanded="false" aria-label="Mehr über Dysgraphie">Details</button>
      <div class="module-details" hidden>
        <p><strong>Fokus:</strong> Handschrift, Schreibtempo und motorische Belastung beim Schreiben.</p>
        <p><strong>VT-orientierte Mikrostrategie:</strong> Externe Hilfen fest einplanen (Sprachnotiz, Tastatur, Stichwortliste) statt perfektes Schreiben auf Anhieb zu erwarten.</p>
      </div>
      <button class="btn-preview" data-module="dysgraphia-test" aria-label="Vorschau des Dysgraphie-Tests">📋 Vorschau</button>
      <a href="process.php?process=dysgraphia-test" class="btn btn-secondary">Starten</a>
    </article>
    <article class="module-card" data-category="attention">
      <img src="https://cdn-icons-png.flaticon.com/512/5090/5090298.png" alt="Dyskalkulie Icon" loading="lazy">
      <h3>Dyskalkulie</h3>
      <p class="brief">Zahlen über eigene Wege. Bilder, Kontext, Bedeutung geben Sicherheit.</p>
      <button class="expand-btn" aria-expanded="false" aria-label="Mehr über Dyskalkulie">Details</button>
      <div class="module-details" hidden>
        <p><strong>Fokus:</strong> Zahlenverständnis, Rechenwege und Sicherheit bei Mengen.</p>
        <p><strong>VT-orientierte Mikrostrategie:</strong> Rechenschritte laut oder schriftlich externalisieren und mit visuellen Ankern (Farben, Skizzen) stabilisieren.</p>
      </div>
      <button class="btn-preview" data-module="dyskalkulie-test" aria-label="Vorschau des Dyskalkulie-Tests">📋 Vorschau</button>
      <a href="process.php?process=dyskalkulie-test" class="btn btn-secondary">Starten</a>
    </article>
    <article class="module-card" data-category="motor">
      <img src="https://cdn-icons-png.flaticon.com/512/8320/8320277.png" alt="Dyspraxie Icon" loading="lazy">
      <h3>Dyspraxie</h3>
      <p class="brief">Bewusste Bewegungssteuerung, feines Körpergefühl. Praktische Lösungen entstehen individuell.</p>
      <button class="expand-btn" aria-expanded="false" aria-label="Mehr über Dyspraxie">Details</button>
      <div class="module-details" hidden>
        <p><strong>Fokus:</strong> Bewegungsplanung, Koordination und motorische Sequenzen.</p>
        <p><strong>VT-orientierte Mikrostrategie:</strong> Komplexe Handlungen in klar sichtbare Einzelschritte zerlegen und jede Teilhandlung kurz verstärken.</p>
      </div>
      <button class="btn-preview" data-module="dyspraxia-test" aria-label="Vorschau des Dyspraxie-Tests">📋 Vorschau</button>
      <a href="process.php?process=dyspraxia-test" class="btn btn-secondary">Starten</a>
    </article>
    <article class="module-card" data-category="social">
      <img src="https://cdn-icons-png.flaticon.com/512/16779/16779640.png" alt="Tic/Tourette Icon" loading="lazy">
      <h3>Tic/Tourette</h3>
      <p class="brief">Spontane Bewegungen und Laute. Körperbewusstsein entsteht durch inneres Geschehen.</p>
      <button class="expand-btn" aria-expanded="false" aria-label="Mehr über Tic/Tourette">Details</button>
      <div class="module-details" hidden>
        <p><strong>Fokus:</strong> Tic-Häufigkeit, Auslöser und Anspannungskurve.</p>
        <p><strong>VT-orientierte Mikrostrategie:</strong> Trigger-Tagebuch mit Situation, Spannung (0–10) und hilfreicher Reaktion führen, um Muster sichtbar zu machen.</p>
      </div>
      <button class="btn-preview" data-module="tic-test" aria-label="Vorschau des Tics/Tourette-Tests">📋 Vorschau</button>
      <a href="process.php?process=tic-test" class="btn btn-secondary">Starten</a>
    </article>
    <article class="module-card" data-category="language">
      <img src="https://cdn-icons-png.flaticon.com/512/8984/8984825.png" alt="Sprachstörung Icon" loading="lazy">
      <h3>Sprachstörung (DLD)</h3>
      <p class="brief">Sprache über Bilder und Situationen. Bedeutung entsteht durch Kontext und Ausdruck.</p>
      <button class="expand-btn" aria-expanded="false" aria-label="Mehr über Sprachstörung">Details</button>
      <div class="module-details" hidden>
        <p><strong>Fokus:</strong> Sprachverständnis, Wortfindung und alltagsnahe Kommunikation.</p>
        <p><strong>VT-orientierte Mikrostrategie:</strong> Vor wichtigen Gesprächen Kernbotschaften in 3 kurzen Sätzen vorbereiten und nach dem Gespräch kurz reflektieren.</p>
      </div>
      <button class="btn-preview" data-module="dld-test" aria-label="Vorschau des Sprachstörungs-Tests">📋 Vorschau</button>
      <a href="process.php?process=dld-test" class="btn btn-secondary">Starten</a>
    </article>
  </div>
</section>

<section class="cta-strip">
  <div>
    <h2>Bereit für einen nächsten Schritt?</h2>
    <p>Wähle ein Modul aus und starte mit Einsicht statt Urteil.</p>
  </div>
  <a href="diagnostics.php#module-selector" class="btn btn-primary">Jetzt starten</a>
</section>

<?php include 'includes/footer.php'; ?>
