<?php
/**
 * صفحة تسجيل الدخول
 * Login Page with Session Management
 */
require_once __DIR__ . '/db.php';

// إذا كان المستخدم مسجلاً بالفعل، يتم تحويله للوحة التحكم
if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
$username_val = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        $error = 'انتهت صلاحية الجلسة، يرجى المحاولة مرة أخرى.';
    } else {
        $identifier = trim($_POST['identifier'] ?? '');
        $password   = $_POST['password'] ?? '';
        $username_val = $identifier;

        if (empty($identifier) || empty($password)) {
            $error = 'يرجى إدخال اسم المستخدم وكلمة المرور.';
        } else {
            // البحث عن المستخدم باسم المستخدم أو البريد الإلكتروني
            $stmt = $db->prepare("SELECT * FROM `users` WHERE `username` = ? OR `email` = ? LIMIT 1");
            $stmt->execute([$identifier, $identifier]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // تجديد معرف الجلسة لمنع هجمات Session Fixation
                session_regenerate_id(true);

                // حفظ بيانات الجلسة
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['username']  = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email']     = $user['email'];
                $_SESSION['role']      = $user['role'];

                set_flash('success', 'مرحباً بك مجدداً، ' . $user['full_name'] . ' 👋');
                header('Location: index.php');
                exit;
            } else {
                $error = 'اسم المستخدم أو كلمة المرور غير صحيحة.';
            }
        }
    }
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول | <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-blob auth-blob-1"></div>
        <div class="auth-blob auth-blob-2"></div>

        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <h1 class="auth-title">تسجيل الدخول</h1>
                <p class="auth-subtitle"><?= e(APP_NAME) ?></p>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>">
                    <div class="alert-content">
                        <span><?= e($flash['message']) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <div class="alert-content">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <span><?= e($error) ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                <div class="form-group">
                    <label class="form-label" for="identifier">
                        اسم المستخدم أو البريد الإلكتروني
                    </label>
                    <input 
                        type="text" 
                        id="identifier" 
                        name="identifier" 
                        class="form-control" 
                        placeholder="أدخل اسم المستخدم أو البريد..." 
                        value="<?= e($username_val) ?>" 
                        required 
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">كلمة المرور</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        placeholder="••••••••" 
                        required
                    >
                </div>

                <div style="margin-top: 25px;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                            <polyline points="10 17 15 12 10 7"></polyline>
                            <line x1="15" y1="12" x2="3" y2="12"></line>
                        </svg>
                        تسجيل الدخول
                    </button>
                </div>
            </form>

            <div class="demo-credentials">
                <strong>💡 بيانات الحساب التجريبي الافتراضي:</strong><br>
                اسم المستخدم: <code>admin</code><br>
                كلمة المرور: <code>admin123</code>
            </div>
        </div>
    </div>
</body>
</html>
