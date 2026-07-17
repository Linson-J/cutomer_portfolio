<?php
$page_title = 'Manage Hobbies';

// Start session & auth check inside header.php
require_once __DIR__ . '/../includes/header.php';

$edit_hobby = null;
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$hobby_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch hobby for editing
if ($action === 'edit' && $hobby_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM hobbies WHERE id = ?");
        $stmt->execute([$hobby_id]);
        $edit_hobby = $stmt->fetch();
        if (!$edit_hobby) {
            $_SESSION['flash_error'] = 'Hobby not found.';
            header('Location: hobbies.php');
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['flash_error'] = 'Database error: ' . $e->getMessage();
        header('Location: hobbies.php');
        exit;
    }
}

// Handle Add / Edit Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
    $icon = trim(filter_input(INPUT_POST, 'icon', FILTER_SANITIZE_SPECIAL_CHARS));
    $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS));
    $submit_type = $_POST['submit_type']; // 'add' or 'edit'
    
    if (empty($name) || empty($description)) {
        $_SESSION['flash_error'] = 'Hobby name and description are required.';
    } else {
        try {
            if ($submit_type === 'edit' && $hobby_id > 0) {
                // Update
                $stmt = $pdo->prepare("UPDATE hobbies SET name = ?, icon = ?, description = ? WHERE id = ?");
                $stmt->execute([$name, $icon, $description, $hobby_id]);
                $_SESSION['flash_success'] = 'Hobby updated successfully.';
            } else {
                // Add
                $stmt = $pdo->prepare("INSERT INTO hobbies (name, icon, description) VALUES (?, ?, ?)");
                $stmt->execute([$name, $icon, $description]);
                $_SESSION['flash_success'] = 'Hobby added successfully.';
            }
            header('Location: hobbies.php');
            exit;
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Database error: ' . $e->getMessage();
        }
    }
}

// Handle Delete Action
if ($action === 'delete' && $hobby_id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM hobbies WHERE id = ?");
        $stmt->execute([$hobby_id]);
        $_SESSION['flash_success'] = 'Hobby deleted successfully.';
    } catch (Exception $e) {
        $_SESSION['flash_error'] = 'Failed to delete hobby: ' . $e->getMessage();
    }
    header('Location: hobbies.php');
    exit;
}

// Fetch hobbies list
$hobbies = [];
try {
    $hobbies = $pdo->query("SELECT * FROM hobbies ORDER BY id DESC")->fetchAll();
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

<?php if ($action === 'edit' && $edit_hobby): ?>
    <!-- Edit Hobby Form -->
    <div class="admin-card">
        <h3 class="card-title">Edit Hobby</h3>
        <form action="hobbies.php?action=edit&id=<?php echo $hobby_id; ?>" method="POST">
            <input type="hidden" name="submit_type" value="edit">
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Hobby Name</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($edit_hobby['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="icon">Icon Identifier</label>
                    <select id="icon" name="icon" class="form-control">
                        <option value="camera" <?php echo $edit_hobby['icon'] === 'camera' ? 'selected' : ''; ?>>Camera (Photography)</option>
                        <option value="gamepad" <?php echo $edit_hobby['icon'] === 'gamepad' ? 'selected' : ''; ?>>Gamepad (Gaming)</option>
                        <option value="code" <?php echo $edit_hobby['icon'] === 'code' ? 'selected' : ''; ?>>Code (Open Source)</option>
                        <option value="book" <?php echo $edit_hobby['icon'] === 'book' ? 'selected' : ''; ?>>Book (Reading)</option>
                        <option value="music" <?php echo $edit_hobby['icon'] === 'music' ? 'selected' : ''; ?>>Music (Playing/Listening)</option>
                        <option value="bike" <?php echo $edit_hobby['icon'] === 'bike' ? 'selected' : ''; ?>>Bicycle (Cycling/Sports)</option>
                        <option value="travel" <?php echo $edit_hobby['icon'] === 'travel' ? 'selected' : ''; ?>>Globe (Travel)</option>
                        <option value="heart" <?php echo $edit_hobby['icon'] === 'heart' ? 'selected' : ''; ?>>Heart (Other)</option>
                    </select>
                </div>
                
                <div class="form-group full-width">
                    <label for="description">Brief Description</label>
                    <input type="text" id="description" name="description" class="form-control" value="<?php echo htmlspecialchars($edit_hobby['description']); ?>" required>
                </div>
            </div>
            
            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">Update Hobby</button>
                <a href="hobbies.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
<?php else: ?>
    <!-- Add Hobby Form -->
    <div class="admin-card">
        <h3 class="card-title">Add New Hobby</h3>
        <form action="hobbies.php" method="POST">
            <input type="hidden" name="submit_type" value="add">
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Hobby Name</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Hiking, Cooking, Guitar" required>
                </div>
                
                <div class="form-group">
                    <label for="icon">Icon Identifier</label>
                    <select id="icon" name="icon" class="form-control">
                        <option value="camera">Camera (Photography)</option>
                        <option value="gamepad">Gamepad (Gaming)</option>
                        <option value="code">Code (Open Source)</option>
                        <option value="book">Book (Reading)</option>
                        <option value="music">Music (Playing/Listening)</option>
                        <option value="bike">Bicycle (Cycling/Sports)</option>
                        <option value="travel">Globe (Travel)</option>
                        <option value="heart">Heart (Other)</option>
                    </select>
                </div>
                
                <div class="form-group full-width">
                    <label for="description">Brief Description</label>
                    <input type="text" id="description" name="description" class="form-control" placeholder="e.g. Taking pictures of cityscape landscapes" required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="margin-top: 1.5rem;">Save Hobby</button>
        </form>
    </div>

    <!-- Hobbies List -->
    <div class="admin-card">
        <h3 class="card-title">Hobbies List</h3>
        <?php if (!empty($hobbies)): ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width: 20%;">Icon</th>
                            <th style="width: 25%;">Hobby Name</th>
                            <th style="width: 40%;">Description</th>
                            <th style="width: 15%; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hobbies as $hb): ?>
                            <tr>
                                <td style="text-transform: capitalize; font-weight: 600; font-size: 0.85rem; color: var(--text-secondary);">
                                    <?php echo htmlspecialchars($hb['icon']); ?>
                                </td>
                                <td style="font-weight: 500;"><?php echo htmlspecialchars($hb['name']); ?></td>
                                <td><?php echo htmlspecialchars($hb['description']); ?></td>
                                <td style="text-align: right;">
                                    <a href="hobbies.php?action=edit&id=<?php echo $hb['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                    <a href="hobbies.php?action=delete&id=<?php echo $hb['id']; ?>" class="btn btn-danger btn-sm confirm-delete" data-confirm-message="Are you sure you want to delete this hobby?">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                <p>No hobbies have been added yet.</p>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
