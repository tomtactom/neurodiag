<?php $pageTitle = 'Kontakt'; include 'includes/header.php'; ?>

        <h2>Kontaktieren Sie uns</h2>
        <p>Haben Sie Fragen oder Feedback? Kontaktieren Sie uns!</p>
        <form action="contact.php" method="post" class="contact-form">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="email">E-Mail:</label>
            <input type="email" id="email" name="email" required>

            <label for="message">Nachricht:</label>
            <textarea id="message" name="message" rows="5" required></textarea>

            <button type="submit" class="btn">Senden</button>
        </form>
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Basic form handling - in production, add validation and email sending
            echo "<p>Danke für Ihre Nachricht! Wir werden uns bald bei Ihnen melden.</p>";
        }
        ?>

<?php include 'includes/footer.php'; ?>