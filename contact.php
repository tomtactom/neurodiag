<?php $pageTitle = 'Kontakt'; include 'includes/header.php'; ?>

<section class="contact-hero">
  <div class="hero-content">
    <p class="hero-pretitle">Kontakt & Support</p>
    <h1>Wir sind für dich da</h1>
    <p>Fragen, Feedback oder brauchst du Unterstützung? Wir freuen uns, von dir zu hören.</p>
  </div>
  <div class="hero-illustration" aria-hidden="true">
    <img src="https://cdn.pixabay.com/photo/2022/03/04/12/51/neuron-7047268_640.jpg" alt="Neuronen-Verknüpfung als Symbol für Verbindung und Unterstützung" loading="lazy">
  </div>
</section>

<section class="contact-main">
  <div class="contact-grid">
    <!-- Contact Form -->
    <div class="contact-form-wrapper">
      <h2>Sende uns eine Nachricht</h2>
      <form action="contact.php" method="post" class="modern-form">
        <div class="form-group">
          <label for="name">Name *</label>
          <input type="text" id="name" name="name" required aria-required="true" placeholder="Dein Name">
        </div>

        <div class="form-group">
          <label for="email">E-Mail *</label>
          <input type="email" id="email" name="email" required aria-required="true" placeholder="deine@email.de">
        </div>

        <div class="form-group">
          <label for="subject">Betreff *</label>
          <select id="subject" name="subject" required aria-required="true">
            <option value="">-- Wähle ein Thema --</option>
            <option value="feedback">Feedback zur Plattform</option>
            <option value="bug">Fehler melden</option>
            <option value="question">Frage zum Service</option>
            <option value="feature">Feature-Anfrage</option>
            <option value="other">Sonstiges</option>
          </select>
        </div>

        <div class="form-group">
          <label for="message">Nachricht *</label>
          <textarea id="message" name="message" rows="6" required aria-required="true" placeholder="Deine Nachricht..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Nachricht senden</button>
      </form>

      <?php
      if ($_SERVER["REQUEST_METHOD"] == "POST") {
        echo "<div class='success-message'>";
        echo "<p>✅ Danke für deine Nachricht! Wir werden uns bald bei dir melden.</p>";
        echo "</div>";
      }
      ?>
    </div>

    <!-- Contact Info & Social -->
    <div class="contact-info-wrapper">
      <h2>Weitere Wege, uns zu erreichen</h2>
      
      <div class="contact-info-card">
        <h3>📧 E-Mail</h3>
        <p><a href="mailto:info@neurodiag.de">info@neurodiag.de</a></p>
        <p class="info-note">Wir antworten in der Regel innerhalb von 24-48 Stunden.</p>
      </div>

      <div class="contact-info-card">
        <h3>🤝 Soziale Netzwerke</h3>
        <div class="social-links">
          <a href="https://twitter.com/" class="social-link" aria-label="Folge uns auf Twitter">𝕏</a>
          <a href="https://www.instagram.com/" class="social-link" aria-label="Folge uns auf Instagram">📷</a>
          <a href="https://www.linkedin.com/" class="social-link" aria-label="Verbinde dich auf LinkedIn">💼</a>
          <a href="https://www.youtube.com/" class="social-link" aria-label="Abonniere uns auf YouTube">▶️</a>
        </div>
      </div>

      <div class="contact-info-card">
        <h3>🌐 Community</h3>
        <p><a href="resources.php">Ressourcen erkunden</a></p>
        <p class="info-note">Tritt unserer wachsenden Community bei und vernetz dich mit anderen.</p>
      </div>
    </div>
  </div>
</section>

<!-- FAQ Section -->
<section class="contact-faq">
  <h2>Häufig gestellte Fragen</h2>
  <div class="faq-grid">
    <div class="faq-item">
      <button class="faq-toggle" aria-expanded="false">
        <span>Sind meine Daten sicher?</span>
        <span class="faq-icon">+</span>
      </button>
      <div class="faq-content" hidden>
        <p>Ja! Wir verwenden moderne Verschlüsselung und halten uns streng an europäische Datenschutzstandards (GDPR). Deine persönlichen Daten werden niemals an Dritte weitergegeben.</p>
      </div>
    </div>

    <div class="faq-item">
      <button class="faq-toggle" aria-expanded="false">
        <span>Können die Tests eine professionelle Diagnose ersetzen?</span>
        <span class="faq-icon">+</span>
      </button>
      <div class="faq-content" hidden>
        <p>Nein. NeuroDiag-Tests sind zur Selbstreflexion und Orientierung konzipiert, nicht als formale Diagnose. Wenn du eine klinische Diagnose brauchst, wende dich an einen Fachmann oder eine Fachfrau.</p>
      </div>
    </div>

    <div class="faq-item">
      <button class="faq-toggle" aria-expanded="false">
        <span>Kostet NeuroDiag etwas?</span>
        <span class="faq-icon">+</span>
      </button>
      <div class="faq-content" hidden>
        <p>NeuroDiag ist komplett kostenlos! Unser Ziel ist, Selbstentdeckung für alle zugänglich zu machen, unabhängig von Einkommen oder Herkunft.</p>
      </div>
    </div>

    <div class="faq-item">
      <button class="faq-toggle" aria-expanded="false">
        <span>Wie long dauert ein Test?</span>
        <span class="faq-icon">+</span>
      </button>
      <div class="faq-content" hidden>
        <p>Die meisten Tests dauern 10-20 Minuten. Es gibt keine Zeitbegrenzung – nimm dir so viel Zeit wie nötig!</p>
      </div>
    </div>

    <div class="faq-item">
      <button class="faq-toggle" aria-expanded="false">
        <span>Kann ich mehrere Tests machen?</span>
        <span class="faq-icon">+</span>
      </button>
      <div class="faq-content" hidden>
        <p>Absolut! Du kannst so viele Tests machen wie du möchtest. Viele Menschen machen mehrere Tests, um verschiedene Aspekte ihrer Neurodivergenz zu erkunden.</p>
      </div>
    </div>

    <div class="faq-item">
      <button class="faq-toggle" aria-expanded="false">
        <span>Wie kann ich Feedback geben?</span>
        <span class="faq-icon">+</span>
      </button>
      <div class="faq-content" hidden>
        <p>Dein Feedback ist wertvoll! Nutze das Kontaktformular oben, um Verbesserungen vorzuschlagen, oder sende eine E-Mail an info@neurodiag.de.</p>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>