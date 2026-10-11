<?php
/**
 * لوحة التحكم وإدارة المستخدمين
 * User Management Dashboard & Listing
 */
require_once __DIR__ . '/db.php';
require_login();

$user = current_user();

// استعلام البحث
$search = trim($_GET['search'] ?? '');
$params = [];

$sql = "SELECT id, username, full_name, email, role, created_at FROM `users`";
if (!empty($search)) {
    $sql .= " WHERE username LIKE ? OR full_name LIKE ? OR email LIKE ?";
    $searchTerm = "%" . $search . "%";
    $params = [$searchTerm, $searchTerm, $searchTerm];
}
$sql .= " ORDER BY id DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

// إحصائيات سريعة
$totalUsers = $db->query("SELECT COUNT(*) FROM `users`")->fetchColumn();
$adminUsers = $db->query("SELECT COUNT(*) FROM `users` WHERE `role` = 'admin'")->fetchColumn();
$regularUsers = $db->query("SELECT COUNT(*) FROM `users` WHERE `role` = 'user'")->fetchColumn();

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المستخدمين | <?= e(APP_NAME) ?></title>
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
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <span><?= e(APP_NAME) ?></span>
            </a>

            <div class="user-nav-profile">
                <div class="user-badge">
                    <div class="user-avatar">
                        <?= mb_substr(e($user['full_name']), 0, 1, 'UTF-8') ?>
                    </div>
                    <div class="user-info">
                        <span class="user-name"><?= e($user['full_name']) ?></span>
                        <span class="user-role"><?= $user['role'] === 'admin' ? 'مدير النظام' : 'مستخدم عادي' ?></span>
                    </div>
                </div>

                <a href="logout.php" class="btn btn-outline btn-sm" title="تسجيل الخروج">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    خروج
                </a>
            </div>
        </div>
    </nav>

    <!-- المحتوى الرئيسي -->
    <main class="container">
        
        <!-- التنبيهات السريعة -->
        <?php if ($flash): ?>
            <div style="margin-top: 24px;">
                <div class="alert alert-<?= e($flash['type']) ?>">
                    <div class="alert-content">
                        <span><?= e($flash['message']) ?></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- عنوان الصفحة وزر الإضافة -->
        <header class="page-header">
            <div>
                <h1 class="page-title">قائمة المستخدمين</h1>
                <p class="page-subtitle">إدارة الحسابات، الصلاحيات، وإضافة مستخدمين جدد للنظام</p>
            </div>
            <div>
                <a href="user_add.php" class="btn btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    إضافة مستخدم جديد
                </a>
            </div>
        </header>

        <!-- بطاقات الإحصائيات -->
        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <h3>إجمالي الحسابات</h3>
                    <div class="stat-number"><?= $totalUsers ?></div>
                </div>
                <div class="stat-icon icon-blue">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                    </svg>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <h3>المدراء (Admins)</h3>
                    <div class="stat-number"><?= $adminUsers ?></div>
                </div>
                <div class="stat-icon icon-purple">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <h3>مستخدمين عاديين</h3>
                    <div class="stat-number"><?= $regularUsers ?></div>
                </div>
                <div class="stat-icon icon-green">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
            </div>
        </section>

        <!-- بطاقة جدول المستخدمين -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="3" y1="9" x2="21" y2="9"></line>
                        <line x1="9" y1="21" x2="9" y2="9"></line>
                    </svg>
                    المستخدمين المسجلين (<?= count($users) ?>)
                </div>

                <!-- شريط البحث -->
                <form method="GET" action="index.php" class="search-form">
                    <input 
                        type="text" 
                        name="search" 
                        class="search-input" 
                        placeholder="بحث بالاسم أو البريد..." 
                        value="<?= e($search) ?>"
                    >
                    <button type="submit" class="btn btn-secondary btn-sm">بحث</button>
                    <?php if (!empty($search)): ?>
                        <a href="index.php" class="btn btn-outline btn-sm">إلغاء</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>الاسم الكامل</th>
                            <th>اسم المستخدم</th>
                            <th>البريد الإلكتروني</th>
                            <th>نوع الحساب</th>
                            <th>تاريخ التسجيل</th>
                            <th style="width: 120px; text-align: center;">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">🔍</div>
                                        <h3>لا توجد نتائج مطابقة</h3>
                                        <p>لم يتم العثور على أي مستخدمين يطابقون معايير البحث.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><strong><?= e($u['id']) ?></strong></td>
                                    <td>
                                        <div style="font-weight: 700; color: var(--dark);">
                                            <?= e($u['full_name']) ?>
                                            <?php if ($u['id'] == $user['id']): ?>
                                                <span style="font-size: 0.75rem; color: var(--primary); margin-right: 4px;">(حسابك الحالي)</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><code>@<?= e($u['username']) ?></code></td>
                                    <td><?= e($u['email']) ?></td>
                                    <td>
                                        <?php if ($u['role'] === 'admin'): ?>
                                            <span class="badge badge-admin">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                                </svg>
                                                مدير نظام
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-user">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                    <circle cx="12" cy="7" r="4"></circle>
                                                </svg>
                                                مستخدم عادي
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="color: var(--secondary); font-size: 0.85rem;">
                                        <?= date('Y-m-d H:i', strtotime($u['created_at'])) ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons" style="justify-content: center;">
                                            <!-- زر التعديل -->
                                            <a href="user_edit.php?id=<?= e($u['id']) ?>" class="btn-icon edit" title="تعديل المستخدم">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                </svg>
                                            </a>

                                            <!-- زر الحذف -->
                                            <?php if ($u['id'] == $user['id']): ?>
                                                <button class="btn-icon" disabled style="opacity: 0.35; cursor: not-allowed;" title="لا يمكنك حذف حسابك الحالي">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <polyline points="3 6 5 6 21 6"></polyline>
                                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                    </svg>
                                                </button>
                                            <?php else: ?>
                                                <a 
                                                    href="user_delete.php?id=<?= e($u['id']) ?>&csrf=<?= e(csrf_token()) ?>" 
                                                    class="btn-icon delete" 
                                                    title="حذف المستخدم"
                                                    onclick="return confirm('هل أنت متأكد من رغبتك في حذف المستخدم (<?= e($u['username']) ?>)؟ لا يمكن التراجع عن هذا الإجراء.');"
                                                >
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <polyline points="3 6 5 6 21 6"></polyline>
                                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                    </svg>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

</body>
</html>
