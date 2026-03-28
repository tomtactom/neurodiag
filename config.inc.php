<?php

declare(strict_types=1);

/**
 * Lokale/produktive Konfiguration.
 *
 * WICHTIG:
 * - Diese Datei nicht im öffentlichen Webroot ablegen.
 * - PROCESS_STORAGE_DIR muss ein absoluter Pfad außerhalb des Webroots sein.
 */
return [
    'ADMIN_PASSWORD_HASH' => 'PLEASE_SET_A_SECURE_PASSWORD_HASH',
    'PROCESS_STORAGE_DIR' => '/var/lib/neurodiag/process-storage',
    'ADMIN_SESSION_KEY' => 'neurodiag_admin_auth',
];
