<?php
$page_title = 'Contact & Social Links';

// Start session & auth check inside header.php
require_once __DIR__ . '/../includes/header.php';

// Handle Contact Info Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
    $phone = trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS));
    $location = trim(filter_input(INPUT_POST, 'location', FILTER_SANITIZE_SPECIAL_CHARS));
    
    if (!$email || empty($location)) {
        $_SESSION['flash_error'] = 'A valid email and location are required.';
    } else {
        try {
            // Check if contact info exists
            $stmtCount = $pdo->query("SELECT COUNT(*) FROM contact_info");
            $exists = $stmtCount->fetchColumn() > 0;
            
            if ($exists) {
                // Update the first record
                $stmt = $pdo->prepare("UPDATE contact_info SET email = ?, phone = ?, location = ? ORDER BY id LIMIT 1");
                $stmt->execute([$email, $phone, $location]);
            } else {
                // Insert
                $stmt = $pdo->prepare("INSERT INTO contact_info (email, phone, location) VALUES (?, ?, ?)");
                $stmt->execute([$email, $phone, $location]);
            }
            $_SESSION['flash_success'] = 'Contact information updated successfully.';
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Error updating contact details: ' . $e->getMessage();
        }
    }
    header('Location: contact_social.php');
    exit;
}

// Handle Add Social Link
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_social'])) {
    $platform = trim(filter_input(INPUT_POST, 'platform', FILTER_SANITIZE_SPECIAL_CHARS));
    $url = trim(filter_input(INPUT_POST, 'url', FILTER_VALIDATE_URL));
    
    if (empty($platform) || !$url) {
        $_SESSION['flash_error'] = 'Please enter a valid platform name and URL.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO social_links (platform, url) VALUES (?, ?)");
            $stmt->execute([$platform, $url]);
            $_SESSION['flash_success'] = 'Social link added successfully.';
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Failed to add social link: ' . $e->getMessage();
        }
    }
    header('Location: contact_social.php');
    exit;
}

// Handle Delete Social Link
if (isset($_GET['action']) && $_GET['action'] === 'delete_social') {
    $social_id = intval($_GET['id']);
    
    if ($social_id > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM social_links WHERE id = ?");
            $stmt->execute([$social_id]);
            $_SESSION['flash_success'] = 'Social link deleted successfully.';
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Failed to delete social link: ' . $e->getMessage();
        }
    }
    header('Location: contact_social.php');
    exit;
}

// Fetch existing contact info
$contact = ['email' => '', 'phone' => '', 'location' => ''];
try {
    $stmtContact = $pdo->query("SELECT email, phone, location FROM contact_info LIMIT 1");
    $res = $stmtContact->fetch();
    if ($res) {
        $contact = $res;
    }
} catch (Exception $e) {}

// Fetch existing social links
$social_links = [];
try {
    $social_links = $pdo->query("SELECT id, platform, url FROM social_links ORDER BY platform ASC")->fetchAll();
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
    <!-- Contact Details Card -->
    <div class="admin-card">
        <h3 class="card-title">Contact Information</h3>
        <form action="contact_social.php" method="POST">
            <input type="hidden" name="submit_contact" value="1">
            
            <div class="form-group">
                <label for="email">Public Email</label>
                <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($contact['email']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Public Phone Number</label>
                <input type="text" id="phone" name="phone" class="form-control" placeholder="e.g. +1 (555) 000-0000" value="<?php echo htmlspecialchars($contact['phone']); ?>">
            </div>
            
            <div class="form-group">
                <label for="location">Location / Office</label>
                <input type="text" id="location" name="location" class="form-control" placeholder="e.g. San Francisco, CA" value="<?php echo htmlspecialchars($contact['location']); ?>" required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Update Contact Info</button>
        </form>
    </div>

    <!-- Social Links Card -->
    <div class="admin-card">
        <h3 class="card-title">Add Social Connection</h3>
        <form action="contact_social.php" method="POST">
            <input type="hidden" name="submit_social" value="1">
            
            <div class="form-group">
                <label for="platform">Platform Name</label>
                <input type="text" id="platform" name="platform" class="form-control" placeholder="e.g. GitHub, LinkedIn, Twitter" required>
            </div>
            
            <div class="form-group">
                <label for="url">Profile / Page URL</label>
                <input type="url" id="url" name="url" class="form-control" placeholder="https://..." required>
            </div>
            
            <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Add Social Link</button>
        </form>
    </div>
</div>

<!-- Social Links List -->
<div class="admin-card">
    <h3 class="card-title">Active Social Links</h3>
    <?php if (!empty($social_links)): ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 30%;">Platform</th>
                        <th style="width: 50%;">URL</th>
                        <th style="width: 20%; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($social_links as $link): ?>
                        <tr>
                            <td style="font-weight: 600; text-transform: uppercase; font-size: 0.8rem;"><?php echo htmlspecialchars($link['platform']); ?></td>
                            <td>
                                <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" rel="noopener noreferrer" style="border-bottom: 1px solid var(--text-muted);">
                                    <?php echo htmlspecialchars($link['url']); ?>
                                </a>
                            </td>
                            <td style="text-align: right;">
                                <a href="contact_social.php?action=delete_social&id=<?php echo $link['id']; ?>" class="btn btn-danger btn-sm confirm-delete" data-confirm-message="Are you sure you want to delete this social link?">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
            <p>No social connections have been added yet.</p>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
