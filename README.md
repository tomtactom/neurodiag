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

- **Backend**: PHP 8.x (vanilla, no frameworks)
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
- Dokumentation aktualisiert (siehe unten)

## Dokumentation

### Für Entwickler & Deployment
- [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md) – Schritt-für-Schritt Deployment-Anleitung für tomaschmann.de

## Sicherheits-Setup für Prozess-/Unit-Dateien

- Der Speicherpfad für Prozess- und Unit-Dateien wird über `PROCESS_STORAGE_DIR` in `config.inc.php` gesetzt und muss als **absoluter Pfad außerhalb des Webroots** konfiguriert werden.
- Für Apache liegt zusätzlich unter `data/.htaccess` eine harte Zugriffssperre (`Require all denied`), falls Deployments ausnahmsweise datennahe Verzeichnisse im Webroot führen.
- Für Nginx wirkt `.htaccess` nicht. Verwende stattdessen z. B.:

```nginx
location ^~ /data/ {
    deny all;
    return 403;
}
```

Wenn ein dedizierter Storage-Unterordner im Webroot unvermeidbar ist, sperre diesen analog per `location`-Block.


### Initialer Server-Datenimport (nach Repo-Cleanup)

Für einen Erstimport aus einem Export-Ordner (z. B. Backup mit `processes/`, `units/`, `templates/`) steht ein CLI-Skript bereit:

```bash
php scripts/import-server-data.php --source=/absoluter/pfad/zum/export
```

- Das Skript schreibt in den serverseitigen Speicher (`PROCESS_STORAGE_DIR`).
- Eine alte→neue Zuordnung wird direkt im Terminal ausgegeben (`old/path.json -> collection/handle`).
- Vorabprüfung ohne Schreiben: `--dry-run` ergänzen.

## Schnellstart

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
├── process.php                  # JSON-gesteuerter Prozess-Renderer
├── result.php                   # Ergebnisdarstellung
├── includes/
│   ├── header.php               # SVG-Logo, Navigation
│   ├── footer.php               # Footer
│   └── process-repository.php   # Serverseitiger JSON-Repository-Layer
├── data/
│   └── .htaccess                # Zugriffsschutz-Fallback (Apache)
├── scripts/
│   └── import-server-data.php   # Initialer JSON-Import in PROCESS_STORAGE_DIR
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

## Humanismusprinzipien

1. **Neurodiversität nicht Pathologie**: Wort- und Framewahl reflektiert Variation
2. **Nutzer als Experten**: "Du kennst dich selbst am besten"
3. **Eigenverantwortlichkeit**: Nutzer kontrolliert Exploration
4. **Ressourcenorientierung**: Tests fokussieren auf Stärken, nicht Mängel
5. **Zugänglichkeit**: Semantisches HTML, Kontrast, Keyboard Navigation

## Philosophie: Selbstdiagnose

**These**: Selbstdiagnose ist legitim und oft der erste Schritt zu Selbstverständnis.

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
2. Relevante Projektdateien hochladen (mind. PHP-, CSS- und JS-Dateien gemäß Checkliste)
3. QA: Funktions- und Darstellungs-Checks durchführen
4. Go-Live: Monitoring starten

## Installation und Ausführung

1. PHP 8.x installieren
2. Repository klonen
3. Server starten: `php -S localhost:8000`
4. Im Browser öffnen: http://localhost:8000

## Beitrag

Dieses Projekt ist Open-Source. Beiträge sind willkommen!

## Autorenhinweise: VT-kompatible Instrument-JSONs

Neue Instrumente werden serverseitig unter `${PROCESS_STORAGE_DIR}/units/*.json` gepflegt und sollen verhaltenstherapeutisch nutzbar, ressourcenorientiert und konkret beobachtbar aufgebaut sein.

### Pflichtfelder (bestehender Renderer)
- `id` (string, eindeutig)
- `title` (string)
- `description` (string)
- `instructions` (string **oder** Array aus Strings)
- `questions` (Array)

### Optionale VT-Felder (werden in `process.php` als eigene Abschnitte gerendert)
- `goal`: Ziel in überprüfbarer Form (z. B. „3x pro Woche 10 Minuten Fokusarbeit“)
- `self_monitoring`: Hinweise zur Selbstbeobachtung (Situation, Verhalten, Ergebnis)
- `trigger_context`: typische Auslöser/Kontexte (wann, wo, mit wem, unter welchen Bedingungen)
- `coping_exercise`: konkrete Coping-Übung in kleinen Schritten
- `transfer_task`: alltagsnaher Transferauftrag zwischen zwei Sitzungen/Einheiten
- `reflection`: kurze Auswertung mit Blick auf Lernfortschritt und nächste Schritte

Jedes Feld kann als String oder als Liste (`Array`) aus kurzen Stichpunkten geschrieben werden.

### Sprachliche Leitlinien (verpflichtend)
- **Ressourcenorientiert** formulieren: Fokus auf Kompetenzen, Lernschritte und Selbstwirksamkeit.
- **Konkret beobachtbar** schreiben: keine vagen Begriffe; lieber „2 Unterbrechungen in 30 Minuten“ statt „war unkonzentriert“.
- **Nicht pathologisierend** formulieren: keine abwertenden Labels, keine defizitorientierten Generalisierungen.
- **Kleine, realistische Schritte** bevorzugen: Interventionen so formulieren, dass sie im Alltag testbar sind.

### Mini-Beispiel
```json
{
  "id": "example_unit_v1",
  "title": "Beispiel Einheit",
  "description": "Kurze verhaltensorientierte Einheit.",
  "instructions": [
    "Wähle eine konkrete Alltagssituation der letzten 48 Stunden.",
    "Notiere Situation, Handlung und direkt sichtbares Ergebnis."
  ],
  "goal": "An 3 Tagen je 10 Minuten eine priorisierte Aufgabe ohne Medienwechsel bearbeiten.",
  "self_monitoring": [
    "Startzeit und Endzeit dokumentieren.",
    "Anzahl Unterbrechungen pro Durchgang notieren."
  ],
  "trigger_context": "Häufige Ablenkung bei offenen Browser-Tabs und Push-Benachrichtigungen.",
  "coping_exercise": "Vor Start 2 Minuten Atemfokus + Benachrichtigungen für 15 Minuten stummschalten.",
  "transfer_task": "Strategie in einer realen Arbeits- oder Lernsituation an zwei Tagen testen.",
  "reflection": "Was hat messbar funktioniert? Was ist der kleinste nächste Schritt für morgen?",
  "questions": [
    {
      "id": "q1",
      "text": "Wie häufig konntest du den 10-Minuten-Block umsetzen?",
      "options": ["0x", "1x", "2x", "3x oder mehr"]
    }
  ]
}
```

## Lizenz

© 2026 NeuroDiag. Alle Rechte vorbehalten.
