<?php
require_once __DIR__ . '/admin-auth.php';

$adminAuthNotice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_action']) && is_string($_POST['admin_action'])) {
    $action = $_POST['admin_action'];
    $token = isset($_POST['csrf_token']) && is_string($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

    if (!adminValidateCsrfToken($token)) {
        $adminAuthNotice = 'Sicherheitsprüfung fehlgeschlagen. Bitte erneut versuchen.';
    } elseif ($action === 'login') {
        $password = isset($_POST['admin_password']) && is_string($_POST['admin_password']) ? $_POST['admin_password'] : '';
        $adminAuthNotice = adminLogin($password)
            ? 'Admin-Login erfolgreich.'
            : 'Login fehlgeschlagen.';
    } elseif ($action === 'logout') {
        adminLogout();
        $adminAuthNotice = 'Du wurdest als Admin abgemeldet.';
    }
}

$adminAuthenticated = isAdminAuthenticated();
$adminCsrfToken = adminGetCsrfToken();
?>
    </main>

    <footer class="site-footer">
        <div class="footer-shell">
            <section class="footer-hero" aria-label="Abschlussbereich">
                <div>
                    <p class="footer-eyebrow">NeuroDiag · reflektiert, evidenznah, menschlich</p>
                    <h2>Dein nächster klarer Schritt zählt mehr als Perfektion.</h2>
                    <p>Mit verhaltenstherapeutisch orientierten Mikro-Impulsen unterstützt NeuroDiag dich dabei, Gedanken zu sortieren, hilfreiche Alternativen zu testen und Fortschritt sichtbar zu machen.</p>
                </div>
                <div class="footer-hero-actions">
                    <a class="footer-cta-link" href="diagnostics.php">Selbstentdeckung starten <span aria-hidden="true">→</span></a>
                    <a class="footer-secondary-link" href="resources.php">Werkzeugkiste öffnen</a>
                </div>
            </section>

            <div class="footer-grid">
                <div class="footer-intro">
                    <h4>NeuroDiag</h4>
                    <p>Ressourcenorientierte Orientierung für Alltag, Lernen und Selbstverständnis – ohne Schubladendenken, mit Fokus auf Handlungsspielräume.</p>
                </div>
                <div>
                    <h4>Navigation</h4>
                    <ul>
                        <li><a href="index.php">Startseite</a></li>
                        <li><a href="diagnostics.php">Selbstentdeckung</a></li>
                        <li><a href="resources.php">Ressourcen</a></li>
                        <li><a href="about.php">Über uns</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Begleitung</h4>
                    <ul>
                        <li><a href="contact.php">Kontakt</a></li>
                        <li><a href="resources.php">Hilfsmaterialien</a></li>
                        <li><a href="interviews/neurodivergence-interview.php">Interview</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Rechtliches</h4>
                    <ul>
                        <li><a href="impressum.php">Impressum</a></li>
                        <li><a href="datenschutz.php">Datenschutz</a></li>
                        <li><a href="#top">Nach oben</a></li>
                    </ul>
                </div>
            </div>

            <div class="admin-auth-box" aria-live="polite">
                <p class="admin-auth-state">
                    Admin-Status:
                    <strong><?php echo $adminAuthenticated ? 'angemeldet' : 'nicht angemeldet'; ?></strong>
                </p>

                <?php if ($adminAuthNotice !== ''): ?>
                    <p class="admin-auth-notice"><?php echo htmlspecialchars($adminAuthNotice, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>

                <?php if ($adminAuthenticated): ?>
                    <form method="post" class="admin-auth-form" autocomplete="off">
                        <input type="hidden" name="admin_action" value="logout">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($adminCsrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="btn btn-secondary btn-sm">Admin Logout</button>
                    </form>
                <?php else: ?>
                    <form method="post" class="admin-auth-form" autocomplete="off">
                        <input type="hidden" name="admin_action" value="login">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($adminCsrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                        <label for="adminPassword" class="visually-hidden">Admin Passwort</label>
                        <input type="password" id="adminPassword" name="admin_password" placeholder="Admin Passwort" required>
                        <button type="submit" class="btn btn-secondary btn-sm">Admin Login</button>
                    </form>
                <?php endif; ?>
            </div>

            <p class="footer-small">&copy; <?php echo date('Y'); ?> NeuroDiag. Klarheit durch kleine, machbare Schritte.</p>
        </div>
    </footer>

    <div id="cookieBanner" class="cookie-banner" role="dialog" aria-live="polite" aria-label="Cookie-Banner">
      <p>Diese Website verwendet Cookies, um Funktionalität zu gewährleisten und das Nutzererlebnis zu verbessern. Durch Klicken auf "Akzeptieren" stimmen Sie zu.</p>
      <div class="cookie-actions">
        <button id="cookieAccept" class="btn btn-primary">Akzeptieren</button>
        <button id="cookieReject" class="btn btn-secondary">Ablehnen</button>
      </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.7.14/lottie.min.js" integrity="sha512-CTCk3cGvrJdOpHec6R3Gga2x9VzIYpZQd5lmU4jT41O28+oz2usn0JKYiFJb1Vi8dJRF8YVsvqz2lAHa7wJC2A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="js/script.js"></script>
    <script src="js/diagnostics-process.js"></script>
    <script src="js/process.js"></script>
    <script src="js/process-admin.js"></script>
    <script src="js/result.js"></script>
</body>
</html>
