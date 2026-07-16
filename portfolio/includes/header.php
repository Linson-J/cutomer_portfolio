<?php
// Start output buffering to prevent "headers already sent" errors
ob_start();

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Authenticate session unless on login page
if (!isset($is_login_page) && !isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Include database
require_once __DIR__ . '/../config/database.php';

// Fetch settings for theme and branding
$admin_settings = [];
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    while ($row = $stmt->fetch()) {
        $admin_settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {}

$site_name = $admin_settings['site_name'] ?? 'Alex Rivers | Portfolio';
$site_theme = $_SESSION['admin_theme'] ?? $admin_settings['default_theme'] ?? 'dark';
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo htmlspecialchars($site_theme); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' | Admin' : 'Admin Panel'; ?></title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="<?php echo isset($is_login_page) ? 'login-body' : ''; ?>">
<?php if (!isset($is_login_page)): ?>
    <!-- Sidebar Navigation -->
    <?php require_once __DIR__ . '/sidebar.php'; ?>
    
    <!-- Main Content Area -->
    <div class="admin-main">
        <!-- Admin Header -->
        <header class="admin-header">
            <h2 class="header-title"><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard'; ?></h2>
            <div class="header-actions">
                <a href="../" target="_blank" class="btn btn-secondary btn-sm" style="display: flex; align-items: center; gap: 0.5rem;">
                    <!-- External Link Icon -->
                    <svg viewBox="0 0 24 24" style="width: 14px; height: 14px; fill: currentColor;">
                        <path d="M21 13v10h-21v-19h12v2h-10v15h17v-8h2zm3-12h-10.988l4.035 4-6.914 7 1.786 1.8 6.913-7 3.968 4v-9.8z"/>
                    </svg>
                    View Website
                </a>
            </div>
        </header>
        <div class="admin-container">
<?php endif; ?>
