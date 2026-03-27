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
  <img src="https://cdn.pixabay.com/photo/2022/04/13/21/48/brain-7131241_640.png" alt="Abstraktes Gehirnnetzwerk" loading="lazy">
</div>

<section class="diagnostic-modules modern-grid">
  <article class="module-card">
    <img src="https://cdn-icons-png.flaticon.com/512/9990/9990347.png" alt="Autismus-Spektrum Icon" loading="lazy">
    <h3>Autismus</h3><p>Stärken & Verarbeitungsmuster.</p><a href="tests/test.php?module=aq-test" class="btn btn-primary">Starten</a>
  </article>
  <article class="module-card">
    <img src="https://cdn-icons-png.flaticon.com/512/10371/10371961.png" alt="ADHS Icon" loading="lazy">
    <h3>AD(H)S</h3><p>Fokus, Impuls & Struktur.</p><a href="tests/test.php?module=asrs-test" class="btn btn-primary">Starten</a>
  </article>
  <article class="module-card">
    <img src="https://cdn-icons-png.flaticon.com/512/1993/1993497.png" alt="Dyslexie Icon" loading="lazy">
    <h3>Dyslexie</h3><p>Lesen, Schreiben, Dekodierung.</p><a href="tests/test.php?module=dyslexia-test" class="btn btn-primary">Starten</a>
  </article>
  <article class="module-card">
    <img src="https://cdn-icons-png.flaticon.com/512/18448/18448228.png" alt="Dysgraphie Icon" loading="lazy">
    <h3>Dysgraphie</h3><p>Schriftmotorik & Ausdruck.</p><a href="tests/test.php?module=dysgraphia-test" class="btn btn-primary">Starten</a>
  </article>
  <article class="module-card">
    <img src="https://cdn-icons-png.flaticon.com/512/5090/5090298.png" alt="Dyskalkulie Icon" loading="lazy">
    <h3>Dyskalkulie</h3><p>Zahlen und logisches Denken.</p><a href="tests/test.php?module=dyskalkulie-test" class="btn btn-primary">Starten</a>
  </article>
  <article class="module-card">
    <img src="https://cdn-icons-png.flaticon.com/512/8320/8320277.png" alt="Dyspraxie Icon" loading="lazy">
    <h3>Dyspraxie</h3><p>Koordination & Planung.</p><a href="tests/test.php?module=dyspraxie-test" class="btn btn-primary">Starten</a>
  </article>
  <article class="module-card">
    <img src="https://cdn-icons-png.flaticon.com/512/16779/16779640.png" alt="Tic-Störungen Icon" loading="lazy">
    <h3>Tic/Tourette</h3><p>Impulskontrolle & Reizverarbeitung.</p><a href="tests/test.php?module=tic-test" class="btn btn-primary">Starten</a>
  </article>
  <article class="module-card">
    <img src="https://cdn-icons-png.flaticon.com/512/8984/8984825.png" alt="Sprachstörung Icon" loading="lazy">
    <h3>Sprachstörung (DLD)</h3><p>Verstehen & Sprachproduktion.</p><a href="tests/test.php?module=dld-test" class="btn btn-primary">Starten</a>
  </article>
  <article class="module-card"><h3>Interview</h3><p>Reflexions-Call, psychologische Einsichten.</p><a href="interviews/neurodivergence-interview.php" class="btn btn-secondary">Starten</a></article>
</section>

<?php include 'includes/footer.php'; ?>