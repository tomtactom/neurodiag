<?php

declare(strict_types=1);

if (!function_exists('ndLoadExternalConfig')) {
    /**
     * @return array<string, string>
     */
    function ndLoadExternalConfig(): array
    {
        static $config = null;

        if (is_array($config)) {
            return $config;
        }

        $candidates = [];
        $envPath = getenv('NEURODIAG_CONFIG_PATH');
        if (is_string($envPath) && $envPath !== '') {
            $candidates[] = $envPath;
        }

        $candidates[] = '/etc/neurodiag/config.inc.php';
        $candidates[] = '/opt/neurodiag/config.inc.php';

        $loaded = null;

        foreach ($candidates as $candidate) {
            if ($candidate === '' || $candidate[0] !== '/' || !is_file($candidate)) {
                continue;
            }

            $data = include $candidate;
            if (!is_array($data)) {
                continue;
            }

            $loaded = $data;
            break;
        }

        $config = [
            'ADMIN_PASSWORD_HASH' => is_array($loaded) && isset($loaded['ADMIN_PASSWORD_HASH']) && is_string($loaded['ADMIN_PASSWORD_HASH'])
                ? $loaded['ADMIN_PASSWORD_HASH']
                : '',
            'PROCESS_STORAGE_DIR' => is_array($loaded) && isset($loaded['PROCESS_STORAGE_DIR']) && is_string($loaded['PROCESS_STORAGE_DIR'])
                ? $loaded['PROCESS_STORAGE_DIR']
                : '',
            'ADMIN_SESSION_KEY' => is_array($loaded) && isset($loaded['ADMIN_SESSION_KEY']) && is_string($loaded['ADMIN_SESSION_KEY'])
                ? $loaded['ADMIN_SESSION_KEY']
                : 'neurodiag_admin_auth',
        ];

        return $config;
    }
}

if (!function_exists('ndStartSecureSession')) {
    function ndStartSecureSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);

        session_start();
    }
}

$GLOBALS['NEURODIAG_CONFIG'] = ndLoadExternalConfig();
ndStartSecureSession();
