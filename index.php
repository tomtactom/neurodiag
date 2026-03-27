<?php $pageTitle = 'Startseite'; include 'includes/header.php'; ?>

<section class="hero">
  <div class="hero-content">
    <p class="hero-pretitle">NeuroDiag</p>
    <h1>Dein moderner Zugang zur neurodivergenten Selbsterkennung</h1>
    <p>Mit datenbasierten Modulen, klaren Auswertungen und UX, die Rücksicht nimmt, entdeckst du Stärke, Verarbeitung und Potenzial.</p>
    <a href="diagnostics.php" class="btn btn-primary">Jetzt Selbstchecks starten</a>
  </div>
  <div class="hero-illustration" aria-hidden="true">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 360" preserveAspectRatio="xMidYMid slice">
      <defs>
        <linearGradient id="neuroGrad" x1="0" y1="0" x2="1" y2="1">
          <stop offset="0%" stop-color="#57B9D1" />
          <stop offset="100%" stop-color="#A7D4EC" />
        </linearGradient>
        <filter id="shadow" x="-20%" y="-20%" width="140%" height="140%">
          <feDropShadow dx="0" dy="4" stdDeviation="6" flood-color="#0A233B" flood-opacity="0.17" />
        </filter>
      </defs>
      <path d="M120 280 C150 190 240 130 320 175 C400 220 450 180 520 140" fill="none" stroke="rgba(255, 255, 255, 0.45)" stroke-width="6" />
      <path d="M90 210 C160 120 240 100 310 130 C365 155 420 130 490 118" fill="none" stroke="rgba(255, 255, 255, 0.3)" stroke-width="4" />
      <g filter="url(#shadow)">
        <circle cx="160" cy="150" r="15" fill="#F77F00" opacity="0.92" />
        <circle cx="280" cy="100" r="15" fill="#457B9D" opacity="0.9" />
        <circle cx="410" cy="180" r="15" fill="#A8DADC" opacity="0.9" />
        <circle cx="540" cy="130" r="15" fill="#1D3557" opacity="0.95" />
      </g>
      <g stroke="rgba(255,255,255,0.6)" stroke-width="2" stroke-linecap="round">
        <line x1="160" y1="150" x2="280" y2="100"/>
        <line x1="160" y1="150" x2="410" y2="180"/>
        <line x1="280" y1="100" x2="540" y2="130"/>
        <line x1="410" y1="180" x2="540" y2="130"/>
      </g>
      <circle cx="360" cy="60" r="24" fill="url(#neuroGrad)" opacity="0.76" />
      <circle cx="360" cy="60" r="10" fill="#FFFFFF" />
      <text x="50%" y="90%" text-anchor="middle" fill="rgba(255,255,255,0.74)" font-size="14" font-family="Inter, Arial, sans-serif" opacity="0.8">Neural Mesh • Verknüpfungen erforschen</text>
    </svg>
    <div id="lottieHero" class="lottie-container" aria-hidden="true"></div>
  </div>
</section>

<section class="features-grid">
  <h2>Fokusbereiche</h2>
  <p>Kurze Orientierung, schnelle Auswahl – über 8 Module, die dich gezielt weiterbringen.</p>
  <div class="cards">
    <article class="feature-card"><h3>Autismus</h3><p>Wahrnehmung, Reizverarbeitung & soziale Dynamik</p></article>
    <article class="feature-card"><h3>AD(H)S</h3><p>Aufmerksamkeit, Impulssteuerung & Arbeitsgedächtnis</p></article>
    <article class="feature-card"><h3>Dyslexie</h3><p>Lese- und Texterkennung</p></article>
    <article class="feature-card"><h3>Dysgraphie</h3><p>Schreibmotorik und Schriftbild</p></article>
    <article class="feature-card"><h3>Dyskalkulie</h3><p>Zahlenverständnis und Mustererkennung</p></article>
    <article class="feature-card"><h3>Dyspraxie</h3><p>Bewegungskoordination und Motorik</p></article>
    <article class="feature-card"><h3>Tic/Tourette</h3><p>Impulskontrolle und Auslöser</p></article>
    <article class="feature-card"><h3>Sprachstörung</h3><p>Sprachverarbeitung und -produktion</p></article>
  </div>
</section>

<section class="cta-strip">
  <div>
    <h2>Bereit für den nächsten Schritt?</h2>
    <p>Konsequent anonym, belastbar und für alle Endgeräte optimiert.</p>
  </div>
  <a href="diagnostics.php" class="btn btn-secondary">Selbstcheck starten</a>
</section>

<?php include 'includes/footer.php'; ?>