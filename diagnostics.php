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
      <button class="preview-cancel-btn" class="btn btn-secondary">Schließen</button>
    </div>
  </div>
</div>

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
    <span id="statusCount">Bereit für deine Selbstentdeckung? Starten wir!</span>
    <div class="progress-bar" aria-hidden="true"><div id="progressFill"></div></div>
  </div>
</section>

<section id="module-selector" class="module-selector">
  <h2>Modul-Auswahl</h2>
  <p>Wähle aus acht spezialisierten Punkten – jeder Selbsttest beleuchtet ein anderes neurodivergentes Profil.</p>
  
  <div class="quick-check">
    <h3>Schnelle Orientierung</h3>
    <p>Was trifft auf dich zu? (Optional – hilft bei der Auswahl)</p>
    <div class="check-options">
      <label><input type="checkbox" name="quickcheck" value="social" class="quick-check-input"> Herausforderungen in sozialen Situationen</label>
      <label><input type="checkbox" name="quickcheck" value="attention" class="quick-check-input"> Fokus/Aufmerksamkeit ist schwierig</label>
      <label><input type="checkbox" name="quickcheck" value="motor" class="quick-check-input"> Bewegung/Motorik/Schreiben</label>
      <label><input type="checkbox" name="quickcheck" value="language" class="quick-check-input"> Lesen, Sprechen oder Zahlen</label>
    </div>
  </div>

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
      <p class="brief">Klare, intensive Wahrnehmung und direkter Ausdruck. Struktur und tiefes Verstehen sind zentral.</p>
      <button class="expand-btn" aria-expanded="false" aria-label="Mehr über Autismus">Details</button>
      <div class="module-details" hidden>
        <p>Viele autistische Menschen erleben die Welt klar, intensiv und detailreich. Dinge, die andere übersehen, springen sofort ins Auge. Kommunikation ist oft direkt und ehrlich, mit starkem Bedürfnis nach Struktur und Sinnzusammenhängen. Interessen geben Freude, Orientierung und Identität.</p>
      </div>
      <button class="btn-preview" data-module="aq-test" aria-label="Vorschau des Autismus-Tests">📋 Vorschau</button>
      <a href="diagnostics-process.php?module=aq-test&phase=1" class="btn btn-secondary">Starten</a>
    </article>
    <article class="module-card" data-category="attention">
      <img src="https://cdn-icons-png.flaticon.com/512/10371/10371961.png" alt="ADHS Icon" loading="lazy">
      <h3>AD(H)S</h3>
      <p class="brief">Innere Bewegung, schnelle Gedankensprünge, Kreativität. Flexibilität statt starrer Struktur.</p>
      <button class="expand-btn" aria-expanded="false" aria-label="Mehr über ADHS">Details</button>
      <div class="module-details" hidden>
        <p>Erleben ist geprägt von Bewegung, innerer Aktivität und schneller Gedankenkopplung. Aufmerksamkeit folgt oft dem, was lebendig ist. Kreativität und Spontaneität sind stark. Der Alltag verlangt häufig flexible Strukturen.</p>
      </div>
      <button class="btn-preview" data-module="asrs-test" aria-label="Vorschau des ADHS-Tests">📋 Vorschau</button>
      <a href="diagnostics-process.php?module=asrs-test&phase=1" class="btn btn-secondary">Starten</a>
    </article>
    <article class="module-card" data-category="language">
      <img src="https://cdn-icons-png.flaticon.com/512/1993/1993497.png" alt="Dyslexie Icon" loading="lazy">
      <h3>Dyslexie</h3>
      <p class="brief">Ganzheitliche, bildhaft-zusammenhängende Wahrnehmung. Schrift braucht andere Wege.</p>
      <button class="expand-btn" aria-expanded="false" aria-label="Mehr über Dyslexie">Details</button>
      <div class="module-details" hidden>
        <p>Sprache wird oft ganzheitlich, bildhaft und zusammenhängend aufgenommen. Schrift kann sich weniger automatisch erschließen. Gedankengänge sind reich und vielschichtig.</p>
      </div>
      <button class="btn-preview" data-module="dyslexia-test" aria-label="Vorschau des Dyslexie-Tests">📋 Vorschau</button>
      <a href="diagnostics-process.php?module=dyslexia-test&phase=1" class="btn btn-secondary">Starten</a>
    </article>
    <article class="module-card" data-category="motor">
      <img src="https://cdn-icons-png.flaticon.com/512/18448/18448228.png" alt="Dysgraphie Icon" loading="lazy">
      <h3>Dysgraphie</h3>
      <p class="brief">Gedanken sind da, Schreiben braucht Zeit. Mündlich, visuell oder digital oft einfacher.</p>
      <button class="expand-btn" aria-expanded="false" aria-label="Mehr über Dysgraphie">Details</button>
      <div class="module-details" hidden>
        <p>Gedanken können sehr differenziert sein, während das Aufschreiben anstrengender wirkt. Schreiben ist oft Übersetzungsprozess. Viele kommunizieren stark über mündlich, visuell oder digital.</p>
      </div>
      <button class="btn-preview" data-module="dysgraphia-test" aria-label="Vorschau des Dysgraphie-Tests">📋 Vorschau</button>
      <a href="diagnostics-process.php?module=dysgraphia-test&phase=1" class="btn btn-secondary">Starten</a>
    </article>
    <article class="module-card" data-category="attention">
      <img src="https://cdn-icons-png.flaticon.com/512/5090/5090298.png" alt="Dyskalkulie Icon" loading="lazy">
      <h3>Dyskalkulie</h3>
      <p class="brief">Zahlen über eigene Wege. Bilder, Kontext, Bedeutung geben Sicherheit.</p>
      <button class="expand-btn" aria-expanded="false" aria-label="Mehr über Dyskalkulie">Details</button>
      <div class="module-details" hidden>
        <p>Zahlen und Mengen werden häufig über eigene Strategien erschlossen. Denken läuft oft über Bilder, Situationen und Bedeutungen. Kontext und Sprache geben Orientierung.</p>
      </div>
      <button class="btn-preview" data-module="dyskalkulie-test" aria-label="Vorschau des Dyskalkulie-Tests">📋 Vorschau</button>
      <a href="diagnostics-process.php?module=dyskalkulie-test&phase=1" class="btn btn-secondary">Starten</a>
    </article>
    <article class="module-card" data-category="motor">
      <img src="https://cdn-icons-png.flaticon.com/512/8320/8320277.png" alt="Dyspraxie Icon" loading="lazy">
      <h3>Dyspraxie</h3>
      <p class="brief">Bewusste Bewegungssteuerung, feines Körpergefühl. Praktische Lösungen entstehen individuell.</p>
      <button class="expand-btn" aria-expanded="false" aria-label="Mehr über Dyspraxie">Details</button>
      <div class="module-details" hidden>
        <p>Bewegungen und Abläufe werden oft bewusster gesteuert. Automatische Abläufe erscheinen Schritt-für-Schritt. Viele entwickeln ein feines Körpergefühl und finden eigene, praktische Lösungen.</p>
      </div>      <button class="btn-preview" data-module="dyspraxia-test" aria-label="Vorschau des Dyspraxie-Tests">📋 Vorschau</button>      <a href="diagnostics-process.php?module=dyspraxia-test&phase=1" class="btn btn-secondary">Starten</a>
    </article>
    <article class="module-card" data-category="social">
      <img src="https://cdn-icons-png.flaticon.com/512/16779/16779640.png" alt="Tic/Tourette Icon" loading="lazy">
      <h3>Tic/Tourette</h3>
      <p class="brief">Spontane Bewegungen und Laute. Körperbewusstsein entsteht durch inneres Geschehen.</p>
      <button class="expand-btn" aria-expanded="false" aria-label="Mehr über Tic/Tourette">Details</button>
      <div class="module-details" hidden>
        <p>Der Körper produziert spontane Bewegungen oder Laute. Das Zusammenspiel aus innerer Spannung und Entladung führt oft zu einem präzisen Körperbewusstsein.</p>
      </div>
      <button class="btn-preview" data-module="tic-test" aria-label="Vorschau des Tics/Tourette-Tests">📋 Vorschau</button>
      <a href="diagnostics-process.php?module=tic-test&phase=1" class="btn btn-secondary">Starten</a>
    </article>
    <article class="module-card" data-category="language">
      <img src="https://cdn-icons-png.flaticon.com/512/8984/8984825.png" alt="Sprachstörung Icon" loading="lazy">
      <h3>Sprachstörung (DLD)</h3>
      <p class="brief">Sprache über Bilder und Situationen. Bedeutung entsteht durch Kontext und Ausdruck.</p>
      <button class="expand-btn" aria-expanded="false" aria-label="Mehr über Sprachstörung">Details</button>
      <div class="module-details" hidden>
        <p>Sprache arbeitet oft über Bilder, Situationen und Handlungen. Kommunikation entsteht bewusst, manchmal kreativ. Bedeutung wird stark durch Kontext und Ausdruck vermittelt.</p>
      </div>
      <button class="btn-preview" data-module="dld-test" aria-label="Vorschau des Sprachstörungs-Tests">📋 Vorschau</button>
      <a href="diagnostics-process.php?module=dld-test&phase=1" class="btn btn-secondary">Starten</a>
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
<section id="sequence-recommendations" class="sequence-recommendations" style="display:none;">
  <h2>🎯 Deine personalisierten nächsten Schritte</h2>
  <p>Basierend auf den Modulen, die du bereits getestet hast, könnten diese dir helfen:</p>
  <div id="recommendedModulesContainer" class="sequence-grid">
    <!-- Wird dynamisch gefüllt -->
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