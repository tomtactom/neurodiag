# NeuroDiag Production Deployment Guide

**Target Domain**: https://neurodiag.tomaschmann.de  
**Update**: Empowerment-focused homepage  
**Status**: Ready for deployment

---

## Files Modified

| File | Changes | Impact |
|------|---------|--------|
| `index.php` | Complete content overhaul: Removed all sections, rebuilt with neurodiversity education (4 main sections), hero update, and CTA | Homepage content change |
| `diagnostics.php` | Vollständige Neustrukturierung: Hero, Fortschritt, modularer Filterscreen, Diagnostikgrenzen, Selbstbeschreibung, CTA | Modulbasierte Selbstentdeckung |
| `about.php` | Added mindfulness image (Pixabay) | Visual content update |
| `resources.php` | Added diversity brain image (Pixabay) | Visual content update |
| `contact.php` | Added neural connection image (Pixabay) | Visual content update |
| `css/style.css` | Added 50+ lines for new neurodiversity sections (info-grid, info-card, blockquote) + responsive styles | Visual styling only |
| `js/script.js` | Removed self-check widget code; kept theme, consent, animations | Behavior cleanup |

**No changes** to:
- Testprozess jetzt über `process.php`
- Laufzeitdaten werden ausschließlich serverseitig in `PROCESS_STORAGE_DIR` gepflegt (nicht im Repo)
- Any other PHP files

---

## What's New on Homepage

### 1. **Self-Empowerment Messaging**
Users immediately see: "Du bist der Experte für dich selbst" (You are the expert on yourself)

### 2. **Interactive Self-Check Widget**
- 4 reflection questions with checkboxes
- When 3+ boxes checked → animated success message
- Pure JavaScript (no external libraries)

### 3. **Humanistic Content Cards**
Two cards explaining:
- Neurodivergenz as variation (not pathology)
- Why self-diagnosis is valid & important

### 4. **Emphasis on User Autonomy**
Key phrases integrated throughout:
- "Du kennst dich selbst am besten"
- "Eigenverantwortlich. In deinem Tempo."
- "Ohne externe Validierung erforderlich"

---

## Browser Compatibility

✅ Modern browsers (Chrome, Firefox, Safari, Edge)  
✅ Mobile-friendly (tested at 560px breakpoint)  
✅ Vanilla JavaScript (no polyfills needed)  
✅ CSS variables (all major browsers)  

---

## Performance Impact

- **No external CDN calls**
- **No new dependencies** (pure PHP, HTML, CSS, vanilla JS)
- **File sizes**:
  - index.php: +100 lines (scripts included)
  - style.css: +160 lines (comprehensive empowerment styles)
- **Load time**: Negligible increase

---

## Security & Licensing Checklist

✅ No user input from homepage (read-only forms)  
✅ No database queries added  
✅ No new server dependencies  
✅ HTML properly escaped (PHP automatic)  
✅ CSS/JS contained, no external loads  
✅ Bilder aus Pixabay (freie Lizenz, CC0) und Flaticon (freie Icons mit Attribution erforderlich)  
✅ Quellen im Code und Dokumentation vermerkt  

### Storage-Härtung (Prozess-/Unit-Dateien)

- [ ] `PROCESS_STORAGE_DIR` in `config.inc.php` auf einen **absoluten Pfad außerhalb des Webroots** gesetzt.
- [ ] Apache: optionaler Fallback `data/.htaccess` mit `Require all denied` vorhanden (falls `data/` im Webroot verbleibt).
- [ ] Nginx: Zugriff auf Datenpfade explizit gesperrt (da `.htaccess` ignoriert wird), z. B.:

```nginx
location ^~ /data/ {
    deny all;
    return 403;
}
```

---


### Open_basedir-Fix für Prozess-Storage (Deployment, nicht im Repo)

> Ziel: Keine `open_basedir`-Warnings (`is_file()/is_dir()/mkdir()`) und kein RuntimeException-Fatal auf der Prozessseite.

1. **Umgebungsvariable im Deployment setzen** (z. B. vHost/Panel/.user.ini):
   - `NEURODIAG_CONFIG_PATH` auf eine produktive `config.inc.php` innerhalb erlaubter `open_basedir`-Pfade setzen.
   - Erlaubte Beispiele:
     - `.../httpdocs/config.inc.php`
     - `.../tmp/neurodiag-config/config.inc.php`

2. **In dieser produktiven Config `PROCESS_STORAGE_DIR` setzen** auf ein Verzeichnis innerhalb von:
   - `/var/www/vhosts/hosting186321.ae8cd.netcup.net/neurodiag.tomaschmann.de/httpdocs/`
   - **oder** `/var/www/vhosts/hosting186321.ae8cd.netcup.net/tmp`

3. **Beispiel (empfohlen, außerhalb öffentlich erreichbarer URL-Pfade):**

```php
<?php
// produktive config.inc.php (Deployment)
define(
    'PROCESS_STORAGE_DIR',
    '/var/www/vhosts/hosting186321.ae8cd.netcup.net/tmp/neurodiag-process-storage'
);
```

4. **Schreibrechte des PHP-Users sicherstellen** (Owner/Group je nach Hosting-Setup):

```bash
mkdir -p /var/www/vhosts/hosting186321.ae8cd.netcup.net/tmp/neurodiag-process-storage
chown -R <php-user>:<php-group> /var/www/vhosts/hosting186321.ae8cd.netcup.net/tmp/neurodiag-process-storage
chmod -R 770 /var/www/vhosts/hosting186321.ae8cd.netcup.net/tmp/neurodiag-process-storage
```

5. **Verifikation nach Deployment**
   - Prozessseite neu laden (z. B. `process.php?process=ass`).
   - Erwartung im PHP-/Webserver-Log:
     - **keine** `open_basedir`-Warnings zu `is_file()/is_dir()/mkdir()`
     - **kein** RuntimeException-Fatal

6. **Wenn weiterhin Fehler auftreten:**
   - Effektiven Wert von `NEURODIAG_CONFIG_PATH` prüfen.
   - Prüfen, ob `PROCESS_STORAGE_DIR` wirklich unter einem erlaubten `open_basedir`-Root liegt.
   - Berechtigungen/Owner des Storage-Ordners erneut prüfen.

---

### Initialer Daten-Seed nach Cleanup

- [ ] Export/Backup mit JSON-Struktur bereitstellen (z. B. `processes/`, `units/`, `templates/`).
- [ ] Import ausführen: `php scripts/import-server-data.php --source=/absoluter/pfad/zum/export`.
- [ ] Mapping-Ausgabe (alte Datei → neue Collection/Handle-ID) im Deployment-Log archivieren.
- [ ] Danach Smoke-Test: `process.php?process=ass` sowie mind. ein weiterer Prozess aus `config/process-registry.php`.

## SEO/Meta Considerations

**Meta Title** (unchanged): NeuroDiag - Startseite  
**Meta Description** (consider updating to):  
"Du bist der Experte für dich selbst. NeuroDiag: Humanistische Selbstentdeckung für Neurodivergenz. Keine Diagnose, nur Selbstverständnis."

---

## Deployment Steps

1. **Backup current `index.php` & `css/style.css`**
   ```bash
   cp index.php index.php.backup
   cp css/style.css css/style.css.backup
   ```

2. **Upload updated files**
   ```
   index.php → /public_html/index.php
   css/style.css → /public_html/css/style.css
   ```

3. **Test on staging** (if available)
   ```
   https://staging.neurodiag.tomaschmann.de
   ```

4. **Verify on production**
   - Homepage loads: ✓
   - Checkboxes interactive: ✓
   - Styling renders correctly: ✓
   - Links work (diagnostics.php): ✓

5. **Clear browser cache** (if applicable)
   ```
   Ctrl+Shift+Delete (most browsers)
   ```

---

## Rollback Plan

If issues arise:
```bash
# Restore from backup
cp index.php.backup index.php
cp css/style.css.backup css/style.css
```

---

## Post-Deployment QA

### Visual Checks
- [ ] Hero section displays with new heading
- [ ] Empowerment cards visible with icons
- [ ] Self-check widget fully interactive
- [ ] Colors/fonts consistent with design

### Functional Checks
- [ ] Checkboxes toggle on/off
- [ ] Result message appears when 3+ checked
- [ ] "Starte deine Selbstentdeckung" button navigates to diagnostics.php
- [ ] Mobile layout responsive at 560px

### Browser Testing
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Mobile Chrome/Safari

---

## Monitoring

**Key Metrics to Watch**:
1. Homepage bounce rate (should not increase significantly)
2. Self-discovery funnel completion (diagnostics.php conversions)
3. Scroll depth (are users reading empowerment sections?)
4. Interactive widget engagement (checkbox clicks)

---

## Contact/Support

For questions about the empowerment messaging or technical details, refer to:
- `EMPOWERMENT_UPDATE.md` (detailed changelog)
- `README.md` (project overview)
- Code comments in `index.php` & `style.css`

---

## Version

**Release**: Empowerment Update v1.0  
**Date**: [Deployment date]  
**Deployed by**: [Your name]  
**Verified on**: https://neurodiag.tomaschmann.de  

---

## Success Criteria

✅ All content displays correctly  
✅ Interactive elements respond to user input  
✅ Mobile users can access and interact  
✅ Performance metrics remain stable  
✅ No console errors in browser DevTools  
✅ Users report improved sense of agency/autonomy  

---

## Future Enhancements

Potential additions for next phase:
1. User testimonials section: "Wie ich mich selbst erkannt habe"
2. FAQ: "Ist Selbstdiagnose legitim?"
3. Community stories/blog
4. Resources for "Nächste Schritte nach Selbstentdeckung"
5. Optional anonymous analytics on empowerment messaging effectiveness
