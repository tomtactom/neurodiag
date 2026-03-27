<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'NeuroDiag'; ?> - NeuroDiag</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="brand">
            <a href="index.php" class="brand-link" aria-label="NeuroDiag Startseite">
                <svg aria-hidden="true" width="44" height="44" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="logoGrad" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0%" stop-color="#A8DADC" />
                            <stop offset="100%" stop-color="#457B9D" />
                        </linearGradient>
                    </defs>
                    <circle cx="25" cy="25" r="20" fill="url(#logoGrad)" stroke="#F1FAEE" stroke-width="2"/>
                    <path d="M15 20 Q25 10 35 20 Q25 30 15 20" fill="#F77F00" stroke="#F1FAEE" stroke-width="1"/>
                    <circle cx="20" cy="18" r="2" fill="#F1FAEE"/>
                    <circle cx="30" cy="18" r="2" fill="#F1FAEE"/>
                </svg>
                <span>NeuroDiag</span>
            </a>
        </div>
        <div class="user-controls">
            <button id="themeToggle" class="theme-toggle" aria-label="Dark Mode umschalten" title="Dark Mode umschalten">🌙</button>
            <button class="nav-toggle" aria-label="Navigation umschalten" aria-expanded="false">☰</button>
        </div>
        <nav class="navbar" aria-label="Hauptnavigation">
            <ul>
                <li><a href="index.php">Startseite</a></li>
                <li><a href="diagnostics.php">Selbstentdeckung</a></li>
                <li><a href="resources.php">Ressourcen</a></li>
                <li><a href="about.php">Über uns</a></li>
                <li><a href="contact.php">Kontakt</a></li>
            </ul>
        </nav>
    </header>

    <main>