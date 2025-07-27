<?php
class SessionManager {
    public static function start() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null) {
        self::start();
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    public static function destroy() {
        self::start();
        session_destroy();
    }

    public static function isLoggedIn() {
        self::start();
        return isset($_SESSION['user_id']) && isset($_SESSION['role']);
    }

    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: Signin.php');
            exit();
        }
    }
    
    public static function requireRole($role) {
        self::requireLogin();
        if (self::get('role') !== $role) {
            header('Location: employeedashboard.php');
            exit();
        }
    }
}
?>
