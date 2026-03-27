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
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 260" preserveAspectRatio="xMidYMid slice">
    <defs>
      <linearGradient id="diagnosticsNetwork" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0%" stop-color="#77CAED" />
        <stop offset="100%" stop-color="#2B67AB" />
      </linearGradient>
      <filter id="softBlur" x="-20%" y="-20%" width="140%" height="140%">
        <feGaussianBlur stdDeviation="2"/>
      </filter>
    </defs>
    <rect x="0" y="0" width="640" height="260" fill="url(#diagnosticsNetwork)" opacity="0.35" />
    <g stroke="#fff" stroke-width="1.2" opacity="0.7">
      <line x1="90" y1="58" x2="210" y2="105" />
      <line x1="210" y1="105" x2="330" y2="68" />
      <line x1="330" y1="68" x2="430" y2="142" />
      <line x1="430" y1="142" x2="530" y2="90" />
      <line x1="150" y1="180" x2="270" y2="145" />
      <line x1="270" y1="145" x2="390" y2="210" />
    </g>
    <g filter="url(#softBlur)">
      <circle cx="90" cy="58" r="8" fill="#FFB347" />
      <circle cx="210" cy="105" r="8" fill="#4CB5F5" />
      <circle cx="330" cy="68" r="8" fill="#72D9FF" />
      <circle cx="430" cy="142" r="8" fill="#A8DADC" />
      <circle cx="530" cy="90" r="8" fill="#F77F00" />
      <circle cx="150" cy="180" r="8" fill="#FFB347" />
      <circle cx="270" cy="145" r="8" fill="#4CB5F5" />
      <circle cx="390" cy="210" r="8" fill="#A8DADC" />
    </g>
    <text x="45" y="235" font-family="Inter, Arial, sans-serif" font-size="13" fill="#FFFFFF" opacity="0.8">Interconnected cognitive Profile</text>
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