# Server-Storage Migration Report (2026-03-28)

## 1) Migrationsliste: JSON-Dateien unter `data/`

Gefundene JSON-Dateien:

1. `data/module-config.json`
2. `data/templates/psychometric-instrument-template.json`

Nicht vorhanden in diesem Repository-Stand:
- `data/processes/*.json`
- `data/units/*.json`
- `data/tests/*.json` (bzw. `tests/` existiert nicht)

## 2) Migration in den serverseitigen Speicher (alte → neue ID)

Ausgeführt mit:

```bash
php scripts/import-server-data.php --source=/workspace/neurodiag/data
```

Zuordnung:

- `module-config.json` → `legacy/module-config`
- `templates/psychometric-instrument-template.json` → `templates/psychometric-instrument-template`

Hinweis: Laufzeitkritische Referenzen aus `config/process-registry.php` verweisen auf Prozess-Handles (`aq`, `adhs`, `dyslexia`, `dysgraphia`, `dyskalkulie`, `dyspraxie`, `tic`, `dld`) und werden serverseitig aus `PROCESS_STORAGE_DIR/processes/*.json` geladen, nicht aus `data/`.

## 3) Referenz-Check

Durchgeführt mit:

```bash
rg -n "module-config|psychometric-instrument-template|data/|processes/|units/|templates/|aq|adhs|dyslexia|dysgraphia|dyskalkulie|dyspraxie|tic|dld" process.php includes/result-functions.php config/process-registry.php tests
```

Ergebnis (Kurzfassung):
- `tests/` nicht vorhanden.
- In den geprüften Laufzeitdateien keine harte Abhängigkeit auf `data/module-config.json` oder `data/templates/psychometric-instrument-template.json`.
- Prozessauflösung erfolgt über Registry-Handles und serverseitige Collections (`processes`, `units`).

## 4) Cleanup

Nach erfolgreicher Migration entfernt:
- `data/module-config.json`
- `data/templates/psychometric-instrument-template.json`

Verbleibend:
- `data/.htaccess` als Apache-Fallback für Zugriffsschutz.

## 5) Deploy-Hinweise

Für Produktion nach dem Cleanup:

1. Backup/Export mit benötigten JSON-Dateien (mind. `processes/` und `units/`, optional `templates/`) bereitstellen.
2. Import via CLI durchführen:
   ```bash
   php scripts/import-server-data.php --source=/absoluter/pfad/zum/export
   ```
3. Mapping-Ausgabe als Deploy-Artefakt speichern.
4. Smoke-Test gegen mindestens zwei Prozesse aus `config/process-registry.php`.
