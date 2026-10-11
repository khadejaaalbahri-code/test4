<?php
/**
 * معالجة حذف المستخدم
 * Delete User Action with Security & CSRF Checks
 */
require_once __DIR__ . '/db.php';
require_login();

$userId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$csrfToken = $_GET['csrf'] ?? '';

// التحقق من رمز الحماية
if (!verify_csrf($csrfToken)) {
    set_flash('error', 'طلب غير مصرح به أو انتهت صلاحية الجلسة.');
    header('Location: index.php');
    exit;
}

if (!$userId) {
    set_flash('error', 'معرّف المستخدم غير صالح.');
    header('Location: index.php');
    exit;
}

// 1. منع المستخدم من حذف حسابه الشخصي
if ($userId == $_SESSION['user_id']) {
    set_flash('error', 'لا يمكنك حذف حسابك الشخصي المسجل به حالياً!');
    header('Location: index.php');
    exit;
}

// 2. التحقق من وجود المستخدم
$stmt = $db->prepare("SELECT id, username, role FROM `users` WHERE id = ? LIMIT 1");
$stmt->execute([$userId]);
$targetUser = $stmt->fetch();

if (!$targetUser) {
    set_flash('error', 'المستخدم المطلوب حذفه غير موجود.');
    header('Location: index.php');
    exit;
}

// 3. منع حذف آخر مدير في النظام
if ($targetUser['role'] === 'admin') {
    $adminCount = $db->query("SELECT COUNT(*) FROM `users` WHERE `role` = 'admin'")->fetchColumn();
    if ($adminCount <= 1) {
        set_flash('error', 'لا يمكن حذف هذا المستخدم لأنه المدير الوحيد المتبقي في النظام!');
        header('Location: index.php');
        exit;
    }
}

// 4. تنفيذ الحذف
$deleteStmt = $db->prepare("DELETE FROM `users` WHERE id = ?");
$deleteStmt->execute([$userId]);

set_flash('success', 'تم حذف المستخدم (' . $targetUser['username'] . ') بنجاح 🗑️');
header('Location: index.php');
exit;
