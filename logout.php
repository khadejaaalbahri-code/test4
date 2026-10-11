<?php
/**
 * تسجيل الخروج وإنهاء الجلسة
 * Logout and Session Destruction
 */
require_once __DIR__ . '/config.php';

// تفريغ مصفوفة الجلسة
$_SESSION = [];

// حذف كعكة الجلسة إن وجدت
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// إنهاء الجلسة
session_destroy();

// إعادة بدء جلسة جديدة فقط لتمرير رسالة نجاح
session_start();
set_flash('info', 'تم تسجيل الخروج بنجاح. نراك قريباً!');

header('Location: login.php');
exit;
