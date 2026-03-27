<?php $pageTitle = 'Kontakt'; include 'includes/header.php'; ?>

<section class="page-section contact-overview">
  <div class="page-card">
    <h2>Kontaktieren Sie uns</h2>
    <p>Haben Sie Fragen oder Feedback? Kontaktieren Sie uns!</p>
    <figure>
      <img src="https://cdn.pixabay.com/photo/2022/03/04/12/51/neuron-7047268_640.jpg" alt="Neuronen-Verknüpfung als Symbol für Verbindung und Unterstützung" loading="lazy">
      <figcaption>Vertrieb & Support für Ihre neurodivergente Reise</figcaption>
    </figure>
    <form action="contact.php" method="post" class="contact-form">
      <label for="name">Name:</label>
      <input type="text" id="name" name="name" required>

      <label for="email">E-Mail:</label>
      <input type="email" id="email" name="email" required>

      <label for="message">Nachricht:</label>
      <textarea id="message" name="message" rows="5" required></textarea>

      <button type="submit" class="btn btn-primary">Senden</button>
    </form>
    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
      echo "<p class='success'>Danke für Ihre Nachricht! Wir werden uns bald bei Ihnen melden.</p>";
    }
    ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>