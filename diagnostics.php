<?php $pageTitle = 'Selbstentdeckung'; include 'includes/header.php'; ?>

<section class="diagnostics-hero">
  <div class="hero-content">
    <p class="hero-pretitle">NeuroDiag</p>
    <h1>Fokussierte Selbstorientierung in 8 Neurodivergenz-Bereichen</h1>
    <p>Klar, reduziert und praxisnah: Wähle einen Bereich und starte direkt.</p>
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
  <p>Jede Karte zeigt kurz den Fokus und bietet einen direkten Einstieg in den jeweiligen Bereich.</p>
  <div class="module-grid">
    <article class="module-card" data-category="social">
      <img src="https://cdn-icons-png.flaticon.com/512/9990/9990347.png" alt="Autismus Icon" loading="lazy">
      <h3>Autismus</h3>
      <p class="brief">Klare, intensive Wahrnehmung und direkter Ausdruck. Struktur und tiefes Verstehen sind zentral.</p>
      <a href="process.php?process=aq" class="btn btn-secondary">Starten</a>
    </article>
    <article class="module-card" data-category="attention">
      <img src="https://cdn-icons-png.flaticon.com/512/10371/10371961.png" alt="ADHS Icon" loading="lazy">
      <h3>AD(H)S</h3>
      <p class="brief">Innere Bewegung, schnelle Gedankensprünge, Kreativität. Flexibilität statt starrer Struktur.</p>
      <a href="process.php?process=adhs" class="btn btn-secondary">Starten</a>
    </article>
    <article class="module-card" data-category="language">
      <img src="https://cdn-icons-png.flaticon.com/512/1993/1993497.png" alt="Dyslexie Icon" loading="lazy">
      <h3>Dyslexie</h3>
      <p class="brief">Ganzheitliche, bildhaft-zusammenhängende Wahrnehmung. Schrift braucht andere Wege.</p>
      <a href="process.php?process=dyslexia" class="btn btn-secondary">Starten</a>
    </article>
    <article class="module-card" data-category="motor">
      <img src="https://cdn-icons-png.flaticon.com/512/18448/18448228.png" alt="Dysgraphie Icon" loading="lazy">
      <h3>Dysgraphie</h3>
      <p class="brief">Gedanken sind da, Schreiben braucht Zeit. Mündlich, visuell oder digital oft einfacher.</p>
      <a href="process.php?process=dysgraphia" class="btn btn-secondary">Starten</a>
    </article>
    <article class="module-card" data-category="attention">
      <img src="https://cdn-icons-png.flaticon.com/512/5090/5090298.png" alt="Dyskalkulie Icon" loading="lazy">
      <h3>Dyskalkulie</h3>
      <p class="brief">Zahlen über eigene Wege. Bilder, Kontext, Bedeutung geben Sicherheit.</p>
      <a href="process.php?process=dyskalkulie" class="btn btn-secondary">Starten</a>
    </article>
    <article class="module-card" data-category="motor">
      <img src="https://cdn-icons-png.flaticon.com/512/8320/8320277.png" alt="Dyspraxie Icon" loading="lazy">
      <h3>Dyspraxie</h3>
      <p class="brief">Bewusste Bewegungssteuerung, feines Körpergefühl. Praktische Lösungen entstehen individuell.</p>
      <a href="process.php?process=dyspraxie" class="btn btn-secondary">Starten</a>
    </article>
    <article class="module-card" data-category="social">
      <img src="https://cdn-icons-png.flaticon.com/512/16779/16779640.png" alt="Tic/Tourette Icon" loading="lazy">
      <h3>Tic/Tourette</h3>
      <p class="brief">Spontane Bewegungen und Laute. Körperbewusstsein entsteht durch inneres Geschehen.</p>
      <a href="process.php?process=tic" class="btn btn-secondary">Starten</a>
    </article>
    <article class="module-card" data-category="language">
      <img src="https://cdn-icons-png.flaticon.com/512/8984/8984825.png" alt="Sprachstörung Icon" loading="lazy">
      <h3>Sprachstörung (DLD)</h3>
      <p class="brief">Sprache über Bilder und Situationen. Bedeutung entsteht durch Kontext und Ausdruck.</p>
      <a href="process.php?process=dld" class="btn btn-secondary">Starten</a>
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
