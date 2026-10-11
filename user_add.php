<?php
/**
 * صفحة إضافة مستخدم جديد
 * Add New User Form & Processing
 */
require_once __DIR__ . '/db.php';
require_login();

$errors = [];
$formData = [
    'full_name' => '',
    'username'  => '',
    'email'     => '',
    'role'      => 'user'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'رمز الحماية غير صالح، يرجى إعادة المحاولة.';
    } else {
        $formData['full_name'] = trim($_POST['full_name'] ?? '');
        $formData['username']  = trim($_POST['username'] ?? '');
        $formData['email']     = trim($_POST['email'] ?? '');
        $formData['role']      = in_array($_POST['role'] ?? '', ['admin', 'user']) ? $_POST['role'] : 'user';
        $password              = $_POST['password'] ?? '';
        $confirm_password      = $_POST['confirm_password'] ?? '';

        // التحقق من الحقول
        if (empty($formData['full_name'])) {
            $errors[] = 'الاسم الكامل مطلوب.';
        }
        if (empty($formData['username'])) {
            $errors[] = 'اسم المستخدم مطلوب.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $formData['username'])) {
            $errors[] = 'اسم المستخدم يجب أن يتكون من 3-30 حرفاً إنجليزياً أو أرقام بدون مسافات.';
        }
        if (empty($formData['email']) || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'يرجى إدخال بريد إلكتروني صحيح.';
        }
        if (empty($password)) {
            $errors[] = 'كلمة المرور مطلوبة.';
        } elseif (strlen($password) < 6) {
            $errors[] = 'كلمة المرور يجب أن لا تقل عن 6 أحرف.';
        }
        if ($password !== $confirm_password) {
            $errors[] = 'تأكيد كلمة المرور غير متطابق.';
        }

        // التحقق من عدم تكرار اسم المستخدم أو البريد
        if (empty($errors)) {
            $check = $db->prepare("SELECT id, username, email FROM `users` WHERE username = ? OR email = ?");
            $check->execute([$formData['username'], $formData['email']]);
            $existing = $check->fetch();

            if ($existing) {
                if (strtolower($existing['username']) === strtolower($formData['username'])) {
                    $errors[] = 'اسم المستخدم مستخدم بالفعل، يرجى اختيار اسم آخر.';
                }
                if (strtolower($existing['email']) === strtolower($formData['email'])) {
                    $errors[] = 'البريد الإلكتروني مسجل بالفعل.';
                }
            }
        }

        // حفظ المستخدم الجديد
        if (empty($errors)) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO `users` (full_name, username, email, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $formData['full_name'],
                $formData['username'],
                $formData['email'],
                $hashedPassword,
                $formData['role']
            ]);

            set_flash('success', 'تمت إضافة المستخدم بنجاح 🎉');
            header('Location: index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة مستخدم جديد | <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- القائمة العلوية -->
    <nav class="navbar">
        <div class="container nav-container">
            <a href="index.php" class="brand">
                <div class="brand-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                    </svg>
                </div>
                <span><?= e(APP_NAME) ?></span>
            </a>
            <div>
                <a href="index.php" class="btn btn-outline btn-sm">العودة للوحة التحكم</a>
            </div>
        </div>
    </nav>

    <main class="container">
        <div class="card form-card">
            <div class="card-header">
                <h1 class="card-title">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <line x1="20" y1="8" x2="20" y2="14"></line>
                        <line x1="23" y1="11" x2="17" y2="11"></line>
                    </svg>
                    إضافة مستخدم جديد
                </h1>
            </div>

            <div style="padding: 28px;">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <div class="alert-content">
                            <ul style="margin-right: 20px;">
                                <?php foreach ($errors as $err): ?>
                                    <li><?= e($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="user_add.php" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                    <div class="form-group">
                        <label class="form-label" for="full_name">
                            الاسم الكامل <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="full_name" 
                            name="full_name" 
                            class="form-control" 
                            placeholder="مثال: محمد عبدالله" 
                            value="<?= e($formData['full_name']) ?>" 
                            required
                        >
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="username">
                                اسم المستخدم <span class="required">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                class="form-control" 
                                placeholder="مثال: mohammed" 
                                value="<?= e($formData['username']) ?>" 
                                required
                            >
                            <span class="form-text">حروف إنجليزية أو أرقام بدون مسافات</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="email">
                                البريد الإلكتروني <span class="required">*</span>
                            </label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="form-control" 
                                placeholder="name@example.com" 
                                value="<?= e($formData['email']) ?>" 
                                required
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="role">نوع الحساب / الصلاحية</label>
                        <select id="role" name="role" class="form-control">
                            <option value="user" <?= $formData['role'] === 'user' ? 'selected' : '' ?>>مستخدم عادي (User)</option>
                            <option value="admin" <?= $formData['role'] === 'admin' ? 'selected' : '' ?>>مدير نظام (Admin)</option>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="password">
                                كلمة المرور <span class="required">*</span>
                            </label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-control" 
                                placeholder="••••••••" 
                                required
                            >
                            <span class="form-text">6 أحرف على الأقل</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="confirm_password">
                                تأكيد كلمة المرور <span class="required">*</span>
                            </label>
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                class="form-control" 
                                placeholder="••••••••" 
                                required
                            >
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            حفظ المستخدم
                        </button>
                        <a href="index.php" class="btn btn-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

</body>
</html>
