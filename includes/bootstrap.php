<?php

declare(strict_types=1);

if (!function_exists('ndIsPathAllowedByOpenBaseDir')) {
    /**
     * @param list<string> $allowedBaseDirs
     */
    function ndIsPathAllowedByOpenBaseDir(string $path, array $allowedBaseDirs): bool
    {
        if ($allowedBaseDirs === []) {
            return true;
        }

        foreach ($allowedBaseDirs as $baseDir) {
            $normalizedBaseDir = rtrim($baseDir, '/');
            if ($normalizedBaseDir === '') {
                $normalizedBaseDir = '/';
            }

            if ($normalizedBaseDir === '/' || $path === $normalizedBaseDir || str_starts_with($path, $normalizedBaseDir . '/')) {
                return true;
            }
        }

        return false;
    }
}

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

        $openBaseDirSetting = ini_get('open_basedir');
        $allowedBaseDirs = [];
        if (is_string($openBaseDirSetting) && $openBaseDirSetting !== '') {
            foreach (explode(PATH_SEPARATOR, $openBaseDirSetting) as $baseDir) {
                $baseDir = trim($baseDir);
                if ($baseDir === '') {
                    continue;
                }

                $allowedBaseDirs[] = $baseDir;
            }
        }

        $candidates = [];
        $envPath = getenv('NEURODIAG_CONFIG_PATH');
        if (is_string($envPath) && $envPath !== '') {
            $candidates[] = $envPath;
        }

        $candidates[] = '/etc/neurodiag/config.inc.php';
        $candidates[] = '/opt/neurodiag/config.inc.php';
        $candidates = array_values(array_filter(
            $candidates,
            static function (string $candidate) use ($allowedBaseDirs): bool {
                if ($candidate === '' || $candidate[0] !== '/') {
                    return false;
                }

                return ndIsPathAllowedByOpenBaseDir($candidate, $allowedBaseDirs);
            }
        ));

        $loaded = null;

        foreach ($candidates as $candidate) {
            if (!is_file($candidate)) {
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
