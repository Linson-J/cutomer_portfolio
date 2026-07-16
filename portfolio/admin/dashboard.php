<?php
$page_title = 'Dashboard';

// Start session & auth check inside header.php
require_once __DIR__ . '/../includes/header.php';

// Handle Message Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    
    try {
        $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
        $stmt->execute([$delete_id]);
        $_SESSION['flash_success'] = 'Message deleted successfully.';
    } catch (Exception $e) {
        $_SESSION['flash_error'] = 'Failed to delete message: ' . $e->getMessage();
    }
    
    header('Location: dashboard.php');
    exit;
}

// Fetch dashboard counters
$total_projects = 0;
$total_skills = 0;
$total_messages = 0;

try {
    $total_projects = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
    $total_skills = $pdo->query("SELECT COUNT(*) FROM skills")->fetchColumn();
    $total_messages = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
} catch (Exception $e) {}

// Fetch contact messages
$messages = [];
try {
    $stmt = $pdo->query("SELECT id, name, email, message, created_at FROM messages ORDER BY created_at DESC");
    $messages = $stmt->fetchAll();
} catch (Exception $e) {}
?>

<!-- Flash Messages -->
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

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <span class="stat-label">Total Projects</span>
        <span class="stat-value"><?php echo $total_projects; ?></span>
    </div>
    <div class="stat-card">
        <span class="stat-label">Total Skills</span>
        <span class="stat-value"><?php echo $total_skills; ?></span>
    </div>
    <div class="stat-card">
        <span class="stat-label">Received Messages</span>
        <span class="stat-value"><?php echo $total_messages; ?></span>
    </div>
</div>

<!-- Messages List -->
<div class="admin-card">
    <h3 class="card-title">Received Messages</h3>
    
    <?php if (!empty($messages)): ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 15%;">Date</th>
                        <th style="width: 20%;">Sender</th>
                        <th style="width: 20%;">Email</th>
                        <th style="width: 35%;">Message</th>
                        <th style="width: 10%; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $msg): ?>
                        <tr>
                            <td style="color: var(--text-muted); font-size: 0.8rem;">
                                <?php echo date('M d, Y H:i', strtotime($msg['created_at'])); ?>
                            </td>
                            <td style="font-weight: 500; color: var(--text-primary);">
                                <?php echo htmlspecialchars($msg['name']); ?>
                            </td>
                            <td>
                                <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" style="border-bottom: 1px solid var(--text-muted);">
                                    <?php echo htmlspecialchars($msg['email']); ?>
                                </a>
                            </td>
                            <td style="color: var(--text-secondary); line-height: 1.5; font-size: 0.85rem; white-space: pre-wrap;"><?php echo htmlspecialchars($msg['message']); ?></td>
                            <td style="text-align: right;">
                                <form action="dashboard.php" method="POST" style="display: inline-block;">
                                    <input type="hidden" name="delete_id" value="<?php echo $msg['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm confirm-delete" data-confirm-message="Are you sure you want to delete this message?">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
            <p>No messages have been received yet.</p>
        </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
