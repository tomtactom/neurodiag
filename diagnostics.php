<?php $pageTitle = 'Selbstentdeckung'; include 'includes/header.php'; ?>

<section class="diagnostics-hero">
  <div class="hero-content">
    <p class="hero-pretitle">NeuroDiag</p>
    <h1>Selbstentdeckung in einer neurodiversen Welt</h1>
    <p>Neurodivergenz ist Teil menschlicher Vielfalt. Kein Defizitmodell – vielmehr Orientierung, Empowerment und praktische Klarheit.</p>
    <a href="#module-selector" class="btn btn-primary">Module entdecken</a>
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
    <span id="statusCount">Noch kein Modul gestartet</span>
    <div class="progress-bar" aria-hidden="true"><div id="progressFill"></div></div>
  </div>
</section>

<section id="module-selector" class="module-selector">
  <h2>Modul-Auswahl</h2>
  <p>Wähle aus acht spezialisierten Punkten – jeder Selbsttest beleuchtet ein anderes neurodivergentes Profil.</p>
  <div class="module-filters" role="group" aria-label="Modulfilter">
    <button class="filter-btn active" data-filter="all">Alle</button>
    <button class="filter-btn" data-filter="social">Sozial</button>
    <button class="filter-btn" data-filter="attention">Aufmerksamkeit</button>
    <button class="filter-btn" data-filter="motor">Motorik</button>
    <button class="filter-btn" data-filter="language">Sprache</button>
  </div>
  <div class="module-grid">
    <article class="module-card" data-category="social">
      <img src="https://cdn-icons-png.flaticon.com/512/9990/9990347.png" alt="Autismus Icon" loading="lazy">
      <h3>Autismus</h3>
      <p>Wahrnehmung, Reizverarbeitung und soziale Dynamik.</p>
      <a href="tests/test.php?module=aq-test" class="btn btn-secondary">Starten</a>
    </article>
    <article class="module-card" data-category="attention">
      <img src="https://cdn-icons-png.flaticon.com/512/10371/10371961.png" alt="ADHS Icon" loading="lazy">
      <h3>AD(H)S</h3>
      <p>Fokus, Impulssteuerung und Arbeitsgedächtnis.</p>
      <a href="tests/test.php?module=asrs-test" class="btn btn-secondary">Starten</a>
    </article>
    <article class="module-card" data-category="language">
      <img src="https://cdn-icons-png.flaticon.com/512/1993/1993497.png" alt="Dyslexie Icon" loading="lazy">
      <h3>Dyslexie</h3>
      <p>Lesefluss, Dekodierung und Texterkennung.</p>
      <a href="tests/test.php?module=dyslexia-test" class="btn btn-secondary">Starten</a>
    </article>
    <article class="module-card" data-category="motor">
      <img src="https://cdn-icons-png.flaticon.com/512/18448/18448228.png" alt="Dysgraphie Icon" loading="lazy">
      <h3>Dysgraphie</h3>
      <p>Schriftmotorik und handschriftlicher Ausdruck.</p>
      <a href="tests/test.php?module=dysgraphia-test" class="btn btn-secondary">Starten</a>
    </article>
    <article class="module-card" data-category="attention">
      <img src="https://cdn-icons-png.flaticon.com/512/5090/5090298.png" alt="Dyskalkulie Icon" loading="lazy">
      <h3>Dyskalkulie</h3>
      <p>Zahlenverständnis und Mustererkennung.</p>
      <a href="tests/test.php?module=dyskalkulie-test" class="btn btn-secondary">Starten</a>
    </article>
    <article class="module-card" data-category="motor">
      <img src="https://cdn-icons-png.flaticon.com/512/8320/8320277.png" alt="Dyspraxie Icon" loading="lazy">
      <h3>Dyspraxie</h3>
      <p>Koordination, Planung und Motorik.</p>
      <a href="tests/test.php?module=dyspraxie-test" class="btn btn-secondary">Starten</a>
    </article>
    <article class="module-card" data-category="social">
      <img src="https://cdn-icons-png.flaticon.com/512/16779/16779640.png" alt="Tic/Tourette Icon" loading="lazy">
      <h3>Tic/Tourette</h3>
      <p>Bewegungsimpulse, Kontrolle und Stressreaktion.</p>
      <a href="tests/test.php?module=tic-test" class="btn btn-secondary">Starten</a>
    </article>
    <article class="module-card" data-category="language">
      <img src="https://cdn-icons-png.flaticon.com/512/8984/8984825.png" alt="Sprachstörung Icon" loading="lazy">
      <h3>Sprachstörung (DLD)</h3>
      <p>Sprachverstehen und -produktion.</p>
      <a href="tests/test.php?module=dld-test" class="btn btn-secondary">Starten</a>
    </article>
  </div>
</section>

<section class="info-section">
  <h2>Diagnostik: Chancen und Grenzen</h2>
  <div class="info-grid">
    <article class="info-card">
      <h3>Diagnostik als Engpass</h3>
      <ul>
        <li>Späte oder falsche Diagnose, v.a. bei Frauen</li>
        <li>Versorgungslücken und unklare Strukturen</li>
      </ul>
    </article>
    <article class="info-card">
      <h3>Kritik am Defizitmodell</h3>
      <ul>
        <li>Fokus auf Schwächen statt Ressourcen</li>
        <li>Reduziert Selbst- und Fremdwahrnehmung</li>
      </ul>
    </article>
    <article class="info-card">
      <h3>Support und Umwelt</h3>
      <ul>
        <li>Diagnose kann Zugang geben, aber stigmatisieren</li>
        <li>Umgebungsanpassung als Schlüssel</li>
      </ul>
    </article>
  </div>
  <blockquote>Paradox: Diagnosen sind hilfreich für Unterstützung, doch die Systeme sind oft unpassend oder ausschließend.</blockquote>
</section>

<section class="identity-section">
  <h2>Selbstbeschreibung jenseits der Störung</h2>
  <div class="info-grid">
    <article class="info-card">
      <h3>Identity-first</h3>
      <ul>
        <li>„Autistisch“, „ADHS“, „neurodivergent“ als Identität</li>
        <li>Selbstwahrnehmung gewinnt an Gewicht</li>
      </ul>
    </article>
    <article class="info-card">
      <h3>Dynamisch und kontextabhängig</h3>
      <ul>
        <li>Neurodivergenz wirkt je nach Umfeld anders</li>
        <li>Erfahrungen sind vielschichtig und flexibel</li>
      </ul>
    </article>
  </div>
  <blockquote>Selbstbeschreibung verlagert sich von „Ich habe eine Störung“ zu „Ich habe eine Art, die Welt zu erleben und zu verarbeiten“.</blockquote>
</section>

<section class="cta-strip">
  <div>
    <h2>Bereit für einen nächsten Schritt?</h2>
    <p>Wähle ein Modul aus und starte mit Einsicht statt Urteil.</p>
  </div>
  <a href="diagnostics.php#module-selector" class="btn btn-primary">Jetzt starten</a>
</section>

<?php include 'includes/footer.php'; ?>