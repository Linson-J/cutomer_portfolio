<?php
$page_title = 'Site Settings';

// Start session & auth check inside header.php
require_once __DIR__ . '/../includes/header.php';

// Handle Site Settings Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_settings'])) {
    $site_name = trim(filter_input(INPUT_POST, 'site_name', FILTER_SANITIZE_SPECIAL_CHARS));
    $hero_name = trim(filter_input(INPUT_POST, 'hero_name', FILTER_SANITIZE_SPECIAL_CHARS));
    $hero_title = trim(filter_input(INPUT_POST, 'hero_title', FILTER_SANITIZE_SPECIAL_CHARS));
    $hero_tagline = trim(filter_input(INPUT_POST, 'hero_tagline', FILTER_SANITIZE_SPECIAL_CHARS));
    $about_bio = trim(filter_input(INPUT_POST, 'about_bio', FILTER_SANITIZE_SPECIAL_CHARS));
    $default_theme = filter_input(INPUT_POST, 'default_theme', FILTER_SANITIZE_SPECIAL_CHARS);
    
    if (empty($site_name) || empty($hero_name) || empty($hero_title) || empty($hero_tagline) || empty($about_bio)) {
        $_SESSION['flash_error'] = 'All site settings fields are required.';
    } else {
        try {
            $settings_data = [
                'site_name' => $site_name,
                'hero_name' => $hero_name,
                'hero_title' => $hero_title,
                'hero_tagline' => $hero_tagline,
                'about_bio' => $about_bio,
                'default_theme' => $default_theme
            ];
            
            // Handle profile image upload
            if (isset($_FILES['about_photo']) && $_FILES['about_photo']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/../uploads/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_tmp = $_FILES['about_photo']['tmp_name'];
                $file_name = $_FILES['about_photo']['name'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($file_ext, $allowed_exts)) {
                    // Check MIME type
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime_type = finfo_file($finfo, $file_tmp);
                    finfo_close($finfo);
                    
                    $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    
                    if (in_array($mime_type, $allowed_mimes)) {
                        $new_file_name = 'profile_photo_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
                        $dest_path = $upload_dir . $new_file_name;
                        
                        if (move_uploaded_file($file_tmp, $dest_path)) {
                            // Fetch existing photo path to delete it
                            $stmtOld = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'about_photo'");
                            $stmtOld->execute();
                            $old_photo = $stmtOld->fetchColumn();
                            if (!empty($old_photo) && file_exists(__DIR__ . '/../' . $old_photo)) {
                                @unlink(__DIR__ . '/../' . $old_photo);
                            }
                            
                            $settings_data['about_photo'] = 'uploads/' . $new_file_name;
                        } else {
                            $_SESSION['flash_error'] = 'Failed to move uploaded profile photo.';
                        }
                    } else {
                        $_SESSION['flash_error'] = 'Invalid profile photo MIME type.';
                    }
                } else {
                    $_SESSION['flash_error'] = 'Invalid profile photo extension. Only JPG, PNG, GIF, and WEBP allowed.';
                }
            }
            
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                                    ON DUPLICATE KEY UPDATE setting_value = ?");
                                    
            foreach ($settings_data as $key => $value) {
                $stmt->execute([$key, $value, $value]);
            }
            
            // Sync current session theme choice
            $_SESSION['admin_theme'] = $default_theme;
            
            $_SESSION['flash_success'] = 'Site settings updated successfully.';
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Database error: ' . $e->getMessage();
        }
    }
    header('Location: settings.php');
    exit;
}

// Handle Admin Account Credentials Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_credentials'])) {
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS));
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($username) || empty($current_password)) {
        $_SESSION['flash_error'] = 'Username and current password are required.';
    } else {
        try {
            // Fetch current password hash
            $stmt = $pdo->prepare("SELECT password FROM admins WHERE id = ?");
            $stmt->execute([$_SESSION['admin_id']]);
            $hash = $stmt->fetchColumn();
            
            if ($hash && password_verify($current_password, $hash)) {
                if (!empty($new_password)) {
                    if ($new_password !== $confirm_password) {
                        $_SESSION['flash_error'] = 'New passwords do not match.';
                    } else {
                        // Update username + new password
                        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmtUpdate = $pdo->prepare("UPDATE admins SET username = ?, password = ? WHERE id = ?");
                        $stmtUpdate->execute([$username, $new_hash, $_SESSION['admin_id']]);
                        $_SESSION['flash_success'] = 'Credentials updated successfully.';
                    }
                } else {
                    // Update username only
                    $stmtUpdate = $pdo->prepare("UPDATE admins SET username = ? WHERE id = ?");
                    $stmtUpdate->execute([$username, $_SESSION['admin_id']]);
                    $_SESSION['flash_success'] = 'Username updated successfully.';
                }
            } else {
                $_SESSION['flash_error'] = 'Incorrect current password.';
            }
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Database error: ' . $e->getMessage();
        }
    }
    header('Location: settings.php');
    exit;
}

// Fetch settings
$settings = [];
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {}

// Fetch Admin Username
$admin_username = '';
try {
    $stmt = $pdo->prepare("SELECT username FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $admin_username = $stmt->fetchColumn();
} catch (Exception $e) {}
?>

<!-- Flash Alerts -->
<?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success">
        <span><?php echo htmlspecialchars($_SESSION['flash_success']); ?></span>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger">
        <span><?php echo htmlspecialchars($_SESSION['flash_error']); ?></span>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="form-grid">
    <!-- General Settings Form -->
    <div class="admin-card" style="grid-column: 1 / -1;">
        <h3 class="card-title">General Settings</h3>
        <form action="settings.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="submit_settings" value="1">
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="site_name">Website Name / Brand Title</label>
                    <input type="text" id="site_name" name="site_name" class="form-control" value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="default_theme">Default Theme</label>
                    <select id="default_theme" name="default_theme" class="form-control">
                        <option value="dark" <?php echo ($settings['default_theme'] ?? '') === 'dark' ? 'selected' : ''; ?>>Dark Mode</option>
                        <option value="light" <?php echo ($settings['default_theme'] ?? '') === 'light' ? 'selected' : ''; ?>>Light Mode</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="hero_name">Hero Section - Large Display Name</label>
                    <input type="text" id="hero_name" name="hero_name" class="form-control" value="<?php echo htmlspecialchars($settings['hero_name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="hero_title">Hero Section - Professional Title</label>
                    <input type="text" id="hero_title" name="hero_title" class="form-control" value="<?php echo htmlspecialchars($settings['hero_title'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group full-width">
                    <label for="hero_tagline">Hero Section - Subheading Tagline</label>
                    <input type="text" id="hero_tagline" name="hero_tagline" class="form-control" value="<?php echo htmlspecialchars($settings['hero_tagline'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group full-width">
                    <label for="about_bio">About Section - Biography Details</label>
                    <textarea id="about_bio" name="about_bio" class="form-control" required><?php echo htmlspecialchars($settings['about_bio'] ?? ''); ?></textarea>
                </div>

                <div class="form-group full-width">
                    <label for="about_photo">Profile Photo</label>
                    <input type="file" id="about_photo" name="about_photo" class="form-control image-upload-input" accept="image/*">
                    <div class="image-preview-container" style="margin-top: 1rem;">
                        <img src="../<?php echo !empty($settings['about_photo']) && file_exists(__DIR__ . '/../' . $settings['about_photo']) ? htmlspecialchars($settings['about_photo']) : 'assets/css/placeholder.jpg'; ?>" class="image-preview" alt="Profile Preview" style="max-width: 150px; border-radius: 8px; border: 1px solid var(--border); <?php echo !empty($settings['about_photo']) ? '' : 'display:none;'; ?>">
                        <span style="font-size: 0.8rem; color: var(--text-secondary); display: block; margin-top: 0.5rem;">Select new image to replace current profile photo.</span>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Save Site Settings</button>
        </form>
    </div>
    
    <!-- Security Account credentials Form -->
    <div class="admin-card" style="grid-column: 1 / -1;">
        <h3 class="card-title">Security Credentials</h3>
        <form action="settings.php" method="POST">
            <input type="hidden" name="submit_credentials" value="1">
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="username">Admin Username</label>
                    <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($admin_username); ?>" required autocomplete="off">
                </div>
                
                <div class="form-group">
                    <label for="current_password">Current Password (To apply changes)</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" placeholder="••••••••" required autocomplete="new-password">
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password (Leave blank to keep current)</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" placeholder="••••••••" autocomplete="new-password">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="••••••••" autocomplete="new-password">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Update Credentials</button>
        </form>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
