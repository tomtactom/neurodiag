<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

if (!function_exists('ndAdminConfig')) {
    /**
     * @return array<string, string>
     */
    function ndAdminConfig(): array
    {
        $config = isset($GLOBALS['NEURODIAG_CONFIG']) && is_array($GLOBALS['NEURODIAG_CONFIG'])
            ? $GLOBALS['NEURODIAG_CONFIG']
            : ndLoadExternalConfig();

        return [
            'ADMIN_PASSWORD_HASH' => isset($config['ADMIN_PASSWORD_HASH']) && is_string($config['ADMIN_PASSWORD_HASH']) ? $config['ADMIN_PASSWORD_HASH'] : '',
            'ADMIN_SESSION_KEY' => isset($config['ADMIN_SESSION_KEY']) && is_string($config['ADMIN_SESSION_KEY']) && $config['ADMIN_SESSION_KEY'] !== ''
                ? $config['ADMIN_SESSION_KEY']
                : 'neurodiag_admin_auth',
        ];
    }
}

if (!function_exists('adminGetCsrfToken')) {
    function adminGetCsrfToken(): string
    {
        ndStartSecureSession();

        if (!isset($_SESSION['admin_csrf_token']) || !is_string($_SESSION['admin_csrf_token']) || $_SESSION['admin_csrf_token'] === '') {
            $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['admin_csrf_token'];
    }
}

if (!function_exists('adminValidateCsrfToken')) {
    function adminValidateCsrfToken(string $token): bool
    {
        ndStartSecureSession();

        if (!isset($_SESSION['admin_csrf_token']) || !is_string($_SESSION['admin_csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['admin_csrf_token'], $token);
    }
}

if (!function_exists('adminLogin')) {
    function adminLogin(string $password): bool
    {
        ndStartSecureSession();

        $config = ndAdminConfig();
        $hash = $config['ADMIN_PASSWORD_HASH'];

        if ($hash === '' || !password_verify($password, $hash)) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION[$config['ADMIN_SESSION_KEY']] = true;

        return true;
    }
}

if (!function_exists('adminLogout')) {
    function adminLogout(): void
    {
        ndStartSecureSession();

        $config = ndAdminConfig();
        unset($_SESSION[$config['ADMIN_SESSION_KEY']]);

        session_regenerate_id(true);
    }
}

if (!function_exists('isAdminAuthenticated')) {
    function isAdminAuthenticated(): bool
    {
        ndStartSecureSession();

        $config = ndAdminConfig();

        return isset($_SESSION[$config['ADMIN_SESSION_KEY']]) && $_SESSION[$config['ADMIN_SESSION_KEY']] === true;
    }
}
