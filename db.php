<?php
/**
 * الاتصال بقاعدة البيانات والتهيئة التلقائية
 * Database Connection & Auto Initialization (PDO)
 */
require_once __DIR__ . '/config.php';

function get_db_connection() {
    static $pdo = null;
    
    if ($pdo !== null) {
        return $pdo;
    }
    
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // إذا لم تكن قاعدة البيانات موجودة، يتم إنشاؤها تلقائياً
        if ($e->getCode() == 1049 || strpos($e->getMessage(), 'Unknown database') !== false) {
            try {
                $rootPdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4", DB_USER, DB_PASS, $options);
                $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                init_database_schema($pdo);
            } catch (PDOException $ex) {
                die("<div style='font-family:sans-serif; direction:rtl; padding:20px; background:#ffebee; color:#c62828; border-radius:8px;'>
                    <h3>تعذر إنشاء قاعدة البيانات تلقائياً:</h3>
                    <p>" . e($ex->getMessage()) . "</p>
                </div>");
            }
        } else {
            die("<div style='font-family:sans-serif; direction:rtl; padding:20px; background:#ffebee; color:#c62828; border-radius:8px;'>
                <h3>تعذر الاتصال بخادم MySQL:</h3>
                <p>" . e($e->getMessage()) . "</p>
                <p>يرجى التأكد من تشغيل خادم MySQL في لوحة تحكم XAMPP Control Panel.</p>
            </div>");
        }
    }
    
    return $pdo;
}

/**
 * إنشاء الجداول والبيانات الافتراضية إن لم تكن موجودة
 */
function init_database_schema(PDO $pdo) {
    $createTableSql = "
    CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(50) NOT NULL UNIQUE,
        `full_name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(100) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `role` ENUM('admin', 'user') NOT NULL DEFAULT 'user',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($createTableSql);

    // إضافة مستخدمين تجريبيين إذا كان الجدول فارغاً
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `users`");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $adminPass = password_hash('admin123', PASSWORD_BCRYPT);
        $userPass = password_hash('123456', PASSWORD_BCRYPT);
        
        $insert = $pdo->prepare("INSERT INTO `users` (`username`, `full_name`, `email`, `password`, `role`) VALUES (?, ?, ?, ?, ?)");
        $insert->execute(['admin', 'المدير العام', 'admin@example.com', $adminPass, 'admin']);
        $insert->execute(['ahmed', 'أحمد محمود', 'ahmed@example.com', $userPass, 'user']);
        $insert->execute(['sara', 'سارة خالد', 'sara@example.com', $userPass, 'user']);
    }
}

// تهيئة الاتصال والجدول
$db = get_db_connection();
init_database_schema($db);
