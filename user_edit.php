<?php
/**
 * صفحة تعديل بيانات المستخدم
 * Edit User Details & Role
 */
require_once __DIR__ . '/db.php';
require_login();

$userId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$userId) {
    set_flash('error', 'معرّف المستخدم غير صالح.');
    header('Location: index.php');
    exit;
}

// جلب بيانات المستخدم
$stmt = $db->prepare("SELECT * FROM `users` WHERE `id` = ? LIMIT 1");
$stmt->execute([$userId]);
$targetUser = $stmt->fetch();

if (!$targetUser) {
    set_flash('error', 'المستخدم المطلوب غير موجود.');
    header('Location: index.php');
    exit;
}

$errors = [];
$formData = [
    'full_name' => $targetUser['full_name'],
    'username'  => $targetUser['username'],
    'email'     => $targetUser['email'],
    'role'      => $targetUser['role']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'رمز الحماية غير صالح، يرجى إعادة المحاولة.';
    } else {
        $formData['full_name'] = trim($_POST['full_name'] ?? '');
        $formData['username']  = trim($_POST['username'] ?? '');
        $formData['email']     = trim($_POST['email'] ?? '');
        $formData['role']      = in_array($_POST['role'] ?? '', ['admin', 'user']) ? $_POST['role'] : $targetUser['role'];
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

        // إذا تم إدخال كلمة مرور جديدة، يتم التحقق منها
        $updatePassword = false;
        if (!empty($password)) {
            if (strlen($password) < 6) {
                $errors[] = 'كلمة المرور يجب أن لا تقل عن 6 أحرف.';
            } elseif ($password !== $confirm_password) {
                $errors[] = 'تأكيد كلمة المرور غير متطابق.';
            } else {
                $updatePassword = true;
            }
        }

        // التحقق من تكرار اسم المستخدم أو البريد لمستخدم آخر
        if (empty($errors)) {
            $check = $db->prepare("SELECT id, username, email FROM `users` WHERE (username = ? OR email = ?) AND id != ?");
            $check->execute([$formData['username'], $formData['email'], $userId]);
            $existing = $check->fetch();

            if ($existing) {
                if (strtolower($existing['username']) === strtolower($formData['username'])) {
                    $errors[] = 'اسم المستخدم مسجل لمستخدم آخر.';
                }
                if (strtolower($existing['email']) === strtolower($formData['email'])) {
                    $errors[] = 'البريد الإلكتروني مسجل لمستخدم آخر.';
                }
            }
        }

        // حفظ التعديلات
        if (empty($errors)) {
            if ($updatePassword) {
                $newHash = password_hash($password, PASSWORD_BCRYPT);
                $updateSql = "UPDATE `users` SET full_name = ?, username = ?, email = ?, role = ?, password = ? WHERE id = ?";
                $stmt = $db->prepare($updateSql);
                $stmt->execute([
                    $formData['full_name'],
                    $formData['username'],
                    $formData['email'],
                    $formData['role'],
                    $newHash,
                    $userId
                ]);
            } else {
                $updateSql = "UPDATE `users` SET full_name = ?, username = ?, email = ?, role = ? WHERE id = ?";
                $stmt = $db->prepare($updateSql);
                $stmt->execute([
                    $formData['full_name'],
                    $formData['username'],
                    $formData['email'],
                    $formData['role'],
                    $userId
                ]);
            }

            // إذا كان المستخدم يعدل حسابه الخاص، نحدث بيانات الجلسة
            if ($userId == $_SESSION['user_id']) {
                $_SESSION['full_name'] = $formData['full_name'];
                $_SESSION['username']  = $formData['username'];
                $_SESSION['email']     = $formData['email'];
                $_SESSION['role']      = $formData['role'];
            }

            set_flash('success', 'تم تحديث بيانات المستخدم بنجاح ✨');
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
    <title>تعديل المستخدم | <?= e(APP_NAME) ?></title>
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
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    تعديل بيانات المستخدم: <?= e($targetUser['full_name']) ?>
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

                <form method="POST" action="user_edit.php?id=<?= e($userId) ?>" autocomplete="off">
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
                                value="<?= e($formData['username']) ?>" 
                                required
                            >
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

                    <div style="margin: 24px 0 16px; padding: 12px; background: #f8fafc; border-radius: var(--radius-sm); border: 1px dashed var(--slate-200);">
                        <strong style="color: var(--dark); font-size: 0.9rem;">🔑 تغيير كلمة المرور:</strong>
                        <p style="font-size: 0.8rem; color: var(--secondary); margin-top: 2px;">
                            اترك الحقول التالية فارغة إذا كنت لا تريد تغيير كلمة المرور الحالية.
                        </p>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="password">كلمة المرور الجديدة</label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-control" 
                                placeholder="اتركه فارغاً للإبقاء على الحالية"
                            >
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="confirm_password">تأكيد كلمة المرور الجديدة</label>
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                class="form-control" 
                                placeholder="أعد كتابة كلمة المرور الجديدة"
                            >
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            حفظ التعديلات
                        </button>
                        <a href="index.php" class="btn btn-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

</body>
</html>
