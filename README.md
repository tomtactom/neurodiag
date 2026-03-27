# NeuroDiag

Eine interaktive Webanwendung für umfassende humanistische Selbstentdeckung zu Neurodivergenz.

## Überblick

NeuroDiag ermöglicht Nutzenden eigenverantwortliche Selbstentdeckung mit Fokus auf **Neurodiversität als Variation, nicht als Pathologie**. Die Plattform betont **Selbstwirksamkeit**: Nutzer sind die besten Experten für sich selbst. Das Design ist professionell mit alternativen Indie-Nuancen, basierend auf einer fundierten Farbpalette und modernen UX-Prinzipien.

### Core Messaging
"**Du bist der Experte für dich selbst.**"
- Nutzer sind kompetent, ihre Neurodivergenz selbst zu erkennen
- Externe Validierung nicht erforderlich
- Humanistische Perspektive: Unterschiede sind Variation, nicht Disorder

## Features

- **9 Selbstentdeckungs-Module**: 
  - Autismus-Spektrum (AQ-Test)
  - AD(H)S (ASRS-Test)
  - Dyslexie, Dysgraphie, Dyskalkulie, Dyspraxie, Tic-Störungen, DLD
  - + Spezialisiertes Interview
- **Empowerment-fokussierte Homepage**: Interaktiver Selbstcheck, Affirmationsmessaging
- **Humanistisches Design**: Sauberes, zugängliches Interface mit beruhigenden Farben
- **Ressourcen-Orientiert**: Interpretationen fokussieren auf Stärken, nicht Defizite
- **Datenschutz**: Sicherheit und ethische Standards

## Technologie

- **Backend**: PHP 8.5 (vanilla, no frameworks)
- **Frontend**: HTML5, CSS3 (variables-based), Vanilla JavaScript
- **Architecture**: JSON-driven modular system
- **Design System**: CSS custom properties with universal element styling
- **Server**: PHP built-in dev server

## Projekt-Status

✅ **Produktionsreif** für https://neurodiag.tomaschmann.de

### Letzte Aktualisierungen (Empowerment Update v1.0)
- Homepage komplett neu gestaltet mit Selbstwirksamkeits-Messaging
- Interaktives Selbstcheck-Widget hinzugefügt
- Empowerment-fokussierte CSS-Styles implementiert
- Dokumentation: 4 neue Leitfäden (siehe unten)

## Dokumentation

### Für Nutzer
- [EMPOWERMENT_PHILOSOPHY.md](EMPOWERMENT_PHILOSOPHY.md) – Psychologische Grundlagen der Selbstwirksamkeit
- [HOMEPAGE_VISUAL_SUMMARY.md](HOMEPAGE_VISUAL_SUMMARY.md) – Visuelle Vorher/Nachher der Homepage

### Für Entwickler & Deployment
- [EMPOWERMENT_UPDATE.md](EMPOWERMENT_UPDATE.md) – Detaillierte Änderungen in Code & Design
- [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md) – Schritt-für-Schritt Deployment-Anleitung für tomaschmann.de

## Schnelstart

### Lokal testen
```bash
cd neurodiag
php -S localhost:8000
# Öffne http://localhost:8000
```

### Dateistruktur
```
neurodiag/
├── index.php                    # Startseite (mit Empowerment-Messaging)
├── diagnostics.php              # Modulauswahl
├── about.php                    # Über uns / Mission
├── resources.php                # Ressourcen
├── contact.php                  # Kontaktformular
├── tests/
│   ├── test.php                 # Test-Formular-Engine
│   └── results.php              # Ergebnisvisualisierung
├── includes/
│   ├── header.php               # SVG-Logo, Navigation
│   ├── footer.php               # Footer
│   └── test-functions.php       # Test-Logik & Scoring
├── data/
│   ├── aq-test.json             # Autismus
│   ├── asrs-test.json           # ADHS
│   ├── dyslexia-test.json       # Dyslexie
│   ├── dysgraphia-test.json     # Dysgraphie
│   ├── dyskalkulie-test.json    # Dyskalkulie
│   ├── dyspraxie-test.json      # Dyspraxie
│   ├── tic-test.json            # Tic-Störungen
│   └── dld-test.json            # DLD
└── css/
    └── style.css                # Design System mit CSS Variables
```

## Design-System

### Farbe
- **Primary**: #457B9D (Vertrauenswürdig Blau)
- **Secondary**: #A8DADC (Beruhigendes Hellblau)
- **Accent**: #F77F00 (Energetisches Orange)
- **Background**: #F1FAEE (Helles, ruhiges Off-White)
- **Success**: #2A9D8F (Affirmierendes Grün)

### Typografie
- **Font**: Inter (sans-serif)
- **Scale**: 1rem = 16px base

### Komponenten
- **Buttons**: Hover-Effekte, Transitions
- **Cards**: Soft Shadows, Rounded Corners
- **Interactive Elements**: Smooth animations, accessibility-first
- **Responsive**: Mobile-first, breakpoint 560px

## Humanismusrerprinzipien

1. **Neurodiversität nicht Pathologie**: Wort- und Framewahl reflektiert Variation
2. **Nutzer als Experten**: "Du kennst dich selbst am besten"
3. **Eigenverantwortlichkeit**: Nutzer kontrolliert Exploration
4. **Ressourcenorientierung**: Tests fokussieren auf Stärken, nicht Mängel
5. **Zugänglichkeit**: Semantisches HTML, Kontrast, Keyboard Navigation

## Philosophie: Selbstdiagnose

**These**: Selbstdiagnose ist legitimate und oft der erste Schritt zu Selbstverständnis.

- Viele Menschen erkennen sich selbst, *bevor* ein Fachmann sie diagnostiziert
- Professionelle Diagnose kann später folgen (optional)
- Selbstverständnis ist Grundlage für Selbstakzeptanz
- Externe Validierung ist nicht erforderlich für Identifikation

## Nächste Schritte

### Geplant
- [ ] Benutzer-Testimonials: "Wie ich mich erkannt habe"
- [ ] FAQ: "Ist Selbstdiagnose legitim?"
- [ ] Blog/Artikel zu Neurodiversität
- [ ] Community-Geschichten
- [ ] Optional: PDF-Reports der Ergebnisse

### Deployment zu tomaschmann.de
1. Siehe [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md)
2. Dateien hochladen: `index.php`, `css/style.css`
3. QA: Homepage-Tests durchführen
4. Go-Live: Monitoring starten

## Installation und Ausführung

1. PHP 8.5 installieren
2. Repository klonen
3. Server starten: `php -S localhost:8000`
4. Im Browser öffnen: http://localhost:8000

## Beitrag

Dieses Projekt ist Open-Source. Beiträge sind willkommen!

## Lizenz

© 2026 NeuroDiag. Alle Rechte vorbehalten.
