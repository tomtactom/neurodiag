<?php $pageTitle = 'Selbstentdeckung'; include 'includes/header.php'; ?>

<section class="diagnostics-hero">
  <div>
    <p class="hero-pretitle">Selbstentdeckung</p>
    <h1>Wähle dein Modul für präzises Feedback</h1>
    <p class="hero-subtitle">Ein klarer Startpunkt mit intelligenten Tests, die dich durch deinen neurodivergenten Prozess begleiten.</p>
  </div>
  <div class="progress-box">
    <strong>So geht es</strong>
    <ol>
      <li>Modul wählen</li>
      <li>Fragen beantworten</li>
      <li>Auswertung erhalten</li>
    </ol>
  </div>
</section>

<div class="hero-illustration compact" aria-hidden="true">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 480 220" preserveAspectRatio="xMidYMid slice">
    <defs>
      <linearGradient id="heroGrad2" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0%" stop-color="#63CEFF" stop-opacity="0.45"/>
        <stop offset="100%" stop-color="#4D99B6" stop-opacity="0.25"/>
      </linearGradient>
    </defs>
    <rect x="0" y="0" width="480" height="220" fill="url(#heroGrad2)"/>
    <circle cx="95" cy="48" r="38" fill="#F77F00" fill-opacity="0.16"/>
    <path d="M110 180 C150 90 310 102 360 180" stroke="#1D3557" stroke-width="4" fill="none" stroke-opacity="0.2"/>
    <circle cx="324" cy="158" r="7" fill="#F1FAEE"/>
  </svg>
</div>

<section class="diagnostic-modules modern-grid">
  <article class="module-card"><h3>Autismus</h3><p>Stärken & Verarbeitungsmuster.</p><a href="tests/test.php?module=aq-test" class="btn btn-primary">Starten</a></article>
  <article class="module-card"><h3>AD(H)S</h3><p>Fokus, Impuls & Struktur.</p><a href="tests/test.php?module=asrs-test" class="btn btn-primary">Starten</a></article>
  <article class="module-card"><h3>Dyslexie</h3><p>Lesen, Schreiben, Dekodierung.</p><a href="tests/test.php?module=dyslexia-test" class="btn btn-primary">Starten</a></article>
  <article class="module-card"><h3>Dysgraphie</h3><p>Schriftmotorik & Ausdruck.</p><a href="tests/test.php?module=dysgraphia-test" class="btn btn-primary">Starten</a></article>
  <article class="module-card"><h3>Dyskalkulie</h3><p>Zahlen und logisches Denken.</p><a href="tests/test.php?module=dyskalkulie-test" class="btn btn-primary">Starten</a></article>
  <article class="module-card"><h3>Dyspraxie</h3><p>Koordination & Planung.</p><a href="tests/test.php?module=dyspraxie-test" class="btn btn-primary">Starten</a></article>
  <article class="module-card"><h3>Tic/Tourette</h3><p>Impulskontrolle & Reizverarbeitung.</p><a href="tests/test.php?module=tic-test" class="btn btn-primary">Starten</a></article>
  <article class="module-card"><h3>Sprachstörung (DLD)</h3><p>Verstehen & Sprachproduktion.</p><a href="tests/test.php?module=dld-test" class="btn btn-primary">Starten</a></article>
  <article class="module-card"><h3>Interview</h3><p>Reflexions-Call, psychologische Einsichten.</p><a href="interviews/neurodivergence-interview.php" class="btn btn-secondary">Starten</a></article>
</section>

<?php include 'includes/footer.php'; ?>