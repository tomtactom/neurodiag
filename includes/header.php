<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'NeuroDiag'; ?> - NeuroDiag</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/diagnostics-process.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="site-header" id="top">
        <div class="header-shell">
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
                    <span class="brand-label-wrap">
                        <span>NeuroDiag</span>
                        <small>Neurodiversität verstehen. Selbstwirksam handeln.</small>
                    </span>
                </a>
            </div>

            <nav class="navbar" aria-label="Hauptnavigation" id="primaryNav">
                <ul>
                    <li><a href="index.php"><span aria-hidden="true">01</span>Startseite</a></li>
                    <li><a href="diagnostics.php"><span aria-hidden="true">02</span>Selbstentdeckung</a></li>
                    <li><a href="resources.php"><span aria-hidden="true">03</span>Ressourcen</a></li>
                    <li><a href="about.php"><span aria-hidden="true">04</span>Über uns</a></li>
                    <li><a href="contact.php"><span aria-hidden="true">05</span>Kontakt</a></li>
                </ul>
            </nav>

            <div class="user-controls" role="group" aria-label="Darstellungsoptionen und Schnellzugriff">
                <a class="header-cta" href="diagnostics.php">Kompass starten</a>
                <button id="themeToggle" class="theme-toggle" aria-label="Dark Mode umschalten" title="Dark Mode umschalten">🌙</button>
                <button class="nav-toggle" aria-label="Navigation umschalten" aria-controls="primaryNav" aria-expanded="false">
                    <span class="nav-toggle-line"></span>
                    <span class="nav-toggle-line"></span>
                    <span class="nav-toggle-line"></span>
                </button>
            </div>
        </div>
    </header>

    <main>
