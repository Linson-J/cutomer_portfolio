<?php
$is_login_page = true;
$page_title = 'Login';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to dashboard if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Include database
require_once __DIR__ . '/../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS));
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, username, password FROM admins WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password'])) {
                // Set session variables
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                
                // Get default theme
                $stmtTheme = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'default_theme'");
                $stmtTheme->execute();
                $theme = $stmtTheme->fetchColumn();
                $_SESSION['admin_theme'] = $theme ?: 'dark';
                
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Include header layout
require_once __DIR__ . '/../includes/header.php';
?>

<div class="login-card">
    <div class="login-header">
        <h1>PORTFOLIO LOGIN</h1>
        <p>Authenticate to manage portfolio resources</p>
    </div>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>
    
    <form action="login.php" method="POST" autocomplete="off">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" class="form-control" placeholder="Enter username" required autofocus>
        </div>
        
        <div class="form-group" style="margin-bottom: 2rem;">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
        </div>
        
        <button type="submit" class="btn btn-primary" style="width: 100%;">Sign In</button>
    </form>
</div>

<?php
// Include footer layout
require_once __DIR__ . '/../includes/footer.php';
?>
