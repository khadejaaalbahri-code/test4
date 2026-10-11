<?php
/**
 * إعدادات النظام والجلسات
 * User Management System - Configuration & Session Handlers
 */

// بدء الجلسة بأمان
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// ثوابت الاتصال بقاعدة البيانات (افتراضيات XAMPP)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'user_management_db');
define('DB_PORT', '3306');

// اسم التطبيق
define('APP_NAME', 'نظام إدارة المستخدمين');

/**
 * دالة تنظيف النصوص وحمايتها من XSS
 */
function e($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

/**
 * التحقق من تسجيل دخول المستخدم
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * التأكد من تسجيل الدخول وإعادة التوجيه إذا لم يكن مسجلاً
 */
function require_login() {
    if (!is_logged_in()) {
        set_flash('error', 'يجب تسجيل الدخول أولاً للوصول إلى هذه الصفحة');
        header('Location: login.php');
        exit;
    }
}

/**
 * بيانات المستخدم الحالي المسجل
 */
function current_user() {
    if (is_logged_in()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'full_name' => $_SESSION['full_name'],
            'email' => $_SESSION['email'],
            'role' => $_SESSION['role']
        ];
    }
    return null;
}

/**
 * التحقق من صلاحية المدير
 */
function is_admin() {
    return is_logged_in() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * إنشاء رمز حماية CSRF
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * التحقق من رمز حماية CSRF
 */
function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}

/**
 * إضافة رسالة تنبيه سريعة Flash Message
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'error', 'info', 'warning'
        'message' => $message
    ];
}

/**
 * قراءة رسالة التنبيه ومسحها
 */
function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
