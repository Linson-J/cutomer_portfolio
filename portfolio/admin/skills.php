<?php
$page_title = 'Manage Skills';

// Start session & auth check inside header.php
require_once __DIR__ . '/../includes/header.php';

$edit_skill = null;
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$skill_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch skill for editing
if ($action === 'edit' && $skill_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM skills WHERE id = ?");
        $stmt->execute([$skill_id]);
        $edit_skill = $stmt->fetch();
        if (!$edit_skill) {
            $_SESSION['flash_error'] = 'Skill not found.';
            header('Location: skills.php');
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['flash_error'] = 'Database error: ' . $e->getMessage();
        header('Location: skills.php');
        exit;
    }
}

// Handle Add / Edit Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
    $level = filter_input(INPUT_POST, 'level', FILTER_VALIDATE_INT);
    $category = trim(filter_input(INPUT_POST, 'category', FILTER_SANITIZE_SPECIAL_CHARS));
    $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS));
    $submit_type = $_POST['submit_type']; // 'add' or 'edit'
    
    if (empty($name) || $level === false || $level < 0 || $level > 100 || empty($category)) {
        $_SESSION['flash_error'] = 'Please fill out all fields. Level must be between 0 and 100.';
    } else {
        try {
            if ($submit_type === 'edit' && $skill_id > 0) {
                // Update
                $stmt = $pdo->prepare("UPDATE skills SET name = ?, level = ?, category = ?, description = ? WHERE id = ?");
                $stmt->execute([$name, $level, $category, $description, $skill_id]);
                $_SESSION['flash_success'] = 'Skill updated successfully.';
            } else {
                // Add
                $stmt = $pdo->prepare("INSERT INTO skills (name, level, category, description) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $level, $category, $description]);
                $_SESSION['flash_success'] = 'Skill added successfully.';
            }
            header('Location: skills.php');
            exit;
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Database error: ' . $e->getMessage();
        }
    }
}

// Handle Delete Action
if ($action === 'delete' && $skill_id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM skills WHERE id = ?");
        $stmt->execute([$skill_id]);
        $_SESSION['flash_success'] = 'Skill deleted successfully.';
    } catch (Exception $e) {
        $_SESSION['flash_error'] = 'Failed to delete skill: ' . $e->getMessage();
    }
    header('Location: skills.php');
    exit;
}

// Fetch skills list
$skills = [];
try {
    $skills = $pdo->query("SELECT * FROM skills ORDER BY category ASC, level DESC")->fetchAll();
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

<?php if ($action === 'edit' && $edit_skill): ?>
    <!-- Edit Skill Form -->
    <div class="admin-card">
        <h3 class="card-title">Edit Skill</h3>
        <form action="skills.php?action=edit&id=<?php echo $skill_id; ?>" method="POST">
            <input type="hidden" name="submit_type" value="edit">
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Skill Name</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($edit_skill['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="level">Proficiency Level (0 - 100%)</label>
                    <input type="number" id="level" name="level" class="form-control" min="0" max="100" value="<?php echo htmlspecialchars($edit_skill['level']); ?>" required>
                </div>
                
                <div class="form-group full-width">
                    <label for="category">Category</label>
                    <input type="text" id="category" name="category" class="form-control" placeholder="e.g. Frontend, Backend, Design, DevOps" value="<?php echo htmlspecialchars($edit_skill['category']); ?>" required>
                </div>

                <div class="form-group full-width">
                    <label for="description">Description (Popup Details)</label>
                    <textarea id="description" name="description" class="form-control" placeholder="Explain your experience with this skill (will show in popup)"><?php echo htmlspecialchars($edit_skill['description'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">Update Skill</button>
                <a href="skills.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
<?php else: ?>
    <!-- Add Skill Form -->
    <div class="admin-card">
        <h3 class="card-title">Add New Skill</h3>
        <form action="skills.php" method="POST">
            <input type="hidden" name="submit_type" value="add">
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Skill Name</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="e.g. JavaScript, PHP, Figma" required>
                </div>
                
                <div class="form-group">
                    <label for="level">Proficiency Level (0 - 100%)</label>
                    <input type="number" id="level" name="level" class="form-control" min="0" max="100" placeholder="90" required>
                </div>
                
                <div class="form-group full-width">
                    <label for="category">Category</label>
                    <input type="text" id="category" name="category" class="form-control" placeholder="e.g. Frontend, Backend, Design, DevOps" required>
                </div>

                <div class="form-group full-width">
                    <label for="description">Description (Popup Details)</label>
                    <textarea id="description" name="description" class="form-control" placeholder="Explain your experience with this skill (will show in popup)"></textarea>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="margin-top: 1.5rem;">Save Skill</button>
        </form>
    </div>

    <!-- Skills List -->
    <div class="admin-card">
        <h3 class="card-title">Skills List</h3>
        <?php if (!empty($skills)): ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width: 35%;">Category</th>
                            <th style="width: 35%;">Skill Name</th>
                            <th style="width: 15%;">Level</th>
                            <th style="width: 15%; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($skills as $skill): ?>
                            <tr>
                                <td style="font-weight: 600; text-transform: uppercase; font-size: 0.8rem; color: var(--text-secondary);"><?php echo htmlspecialchars($skill['category']); ?></td>
                                <td style="font-weight: 500;"><?php echo htmlspecialchars($skill['name']); ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="flex-grow: 1; height: 4px; background: var(--border); width: 60px; border-radius: 2px; overflow: hidden;">
                                            <div style="height: 100%; width: <?php echo $skill['level']; ?>%; background: var(--text-primary);"></div>
                                        </div>
                                        <span><?php echo $skill['level']; ?>%</span>
                                    </div>
                                </td>
                                <td style="text-align: right;">
                                    <a href="skills.php?action=edit&id=<?php echo $skill['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                    <a href="skills.php?action=delete&id=<?php echo $skill['id']; ?>" class="btn btn-danger btn-sm confirm-delete" data-confirm-message="Are you sure you want to delete this skill?">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                <p>No skills have been added yet.</p>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
