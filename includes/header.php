<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'NeuroDiag'; ?> - NeuroDiag</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="brand">
            <svg aria-hidden="true" width="44" height="44" viewBox="0 0 50 50" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="25" cy="25" r="20" fill="#457B9D" stroke="#A8DADC" stroke-width="2"/>
                <path d="M15 20 Q25 10 35 20 Q25 30 15 20" fill="#F77F00" stroke="#F1FAEE" stroke-width="1"/>
                <circle cx="20" cy="18" r="2" fill="#F1FAEE"/>
                <circle cx="30" cy="18" r="2" fill="#F1FAEE"/>
            </svg>
            <h1>NeuroDiag</h1>
        </div>
        <nav>
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