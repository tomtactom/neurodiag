<?php $pageTitle = 'Startseite'; include 'includes/header.php'; ?>

<section class="hero">
  <div class="hero-content">
    <p class="hero-pretitle">NeuroDiag</p>
    <h1>Dein moderner Zugang zur neurodivergenten Selbsterkennung</h1>
    <p>Mit datenbasierten Modulen, klaren Auswertungen und UX, die Rücksicht nimmt, entdeckst du Stärke, Verarbeitung und Potenzial.</p>
    <a href="diagnostics.php" class="btn btn-primary">Jetzt Selbstchecks starten</a>
  </div>
  <div class="hero-illustration" aria-hidden="true">
    <img src="https://cdn.pixabay.com/photo/2020/07/22/00/32/brain-5427670_1280.png" alt="Abstrakte Darstellung eines Gehirns mit bunten Verbindungen" loading="lazy">
  </div>
</section>

<section class="empowerment-section">
  <h2>Du bist der Experte für dich selbst</h2>
  <p>Neurodivergenz ist eine natürliche Variation des menschlichen Geistes – keine Krankheit, sondern eine andere Art zu denken, fühlen und wahrnehmen.</p>
  <div class="empowerment-cards">
    <article class="empowerment-card">
      <h3>Warum Selbstentdeckung?</h3>
      <p>Du kennst dich selbst am besten. Unsere Tests helfen dir, Muster zu erkennen und dein Potenzial zu entfalten – ohne externe Validierung.</p>
    </article>
    <article class="empowerment-card">
      <h3>Humanistisch & Anonym</h3>
      <p>Keine Diagnosen, nur Einsichten. Alle Daten bleiben bei dir, und du gehst in deinem eigenen Tempo voran.</p>
    </article>
  </div>
</section>

<section class="self-check-widget">
  <h2>Schneller Selbstcheck</h2>
  <p>Beantworte diese 4 Fragen, um zu sehen, ob unsere Module für dich relevant sein könnten.</p>
  <form id="selfCheckForm">
    <label><input type="checkbox" name="q1"> Ich fühle mich oft überstimuliert in sozialen Situationen.</label>
    <label><input type="checkbox" name="q2"> Ich habe Schwierigkeiten, meine Aufmerksamkeit zu fokussieren.</label>
    <label><input type="checkbox" name="q3"> Ich mache häufig Rechtschreibfehler beim Schreiben.</label>
    <label><input type="checkbox" name="q4"> Ich habe Probleme mit der Koordination von Bewegungen.</label>
    <button type="button" id="checkBtn" class="btn btn-primary">Auswerten</button>
  </form>
  <div id="resultMessage" style="display: none;">
    <p>Basierend auf deinen Antworten könnten unsere Module hilfreich für dich sein. Starte jetzt deine Selbstentdeckung!</p>
  </div>
</section>

<section class="features-grid">
  <h2>Fokusbereiche</h2>
  <p>Kurze Orientierung, schnelle Auswahl – über 8 Module, die dich gezielt weiterbringen.</p>
  <div class="cards">
    <article class="feature-card"><img src="https://cdn-icons-png.flaticon.com/512/2770/2770932.png" alt="Autismus Icon" class="card-icon"><h3>Autismus</h3><p>Wahrnehmung, Reizverarbeitung & soziale Dynamik</p></article>
    <article class="feature-card"><img src="https://cdn-icons-png.flaticon.com/512/4775/4775917.png" alt="ADHS Icon" class="card-icon"><h3>AD(H)S</h3><p>Aufmerksamkeit, Impulssteuerung & Arbeitsgedächtnis</p></article>
    <article class="feature-card"><img src="https://cdn-icons-png.flaticon.com/512/3159/3159664.png" alt="Dyslexie Icon" class="card-icon"><h3>Dyslexie</h3><p>Lese- und Texterkennung</p></article>
    <article class="feature-card"><img src="https://cdn-icons-png.flaticon.com/512/7678/7678075.png" alt="Dysgraphie Icon" class="card-icon"><h3>Dysgraphie</h3><p>Schreibmotorik und Schriftbild</p></article>
    <article class="feature-card"><img src="https://cdn-icons-png.flaticon.com/512/13322/13322428.png" alt="Dyskalkulie Icon" class="card-icon"><h3>Dyskalkulie</h3><p>Zahlenverständnis und Mustererkennung</p></article>
    <article class="feature-card"><img src="https://cdn-icons-png.flaticon.com/512/14290/14290752.png" alt="Dyspraxie Icon" class="card-icon"><h3>Dyspraxie</h3><p>Bewegungskoordination und Motorik</p></article>
    <article class="feature-card"><img src="https://cdn.pixabay.com/photo/2024/10/13/12/34/brain-9117107_1280.png" alt="Tic/Tourette Icon" class="card-icon"><h3>Tic/Tourette</h3><p>Impulskontrolle und Auslöser</p></article>
    <article class="feature-card"><img src="https://cdn.pixabay.com/photo/2022/04/13/21/48/brain-7131241_640.png" alt="Sprachstörung Icon" class="card-icon"><h3>Sprachstörung</h3><p>Sprachverarbeitung und -produktion</p></article>
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