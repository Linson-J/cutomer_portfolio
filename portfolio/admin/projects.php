<?php
$page_title = 'Manage Projects';

// Start session & auth check inside header.php
require_once __DIR__ . '/../includes/header.php';

// Create uploads directory if not exists (redundancy check)
$upload_dir = __DIR__ . '/../uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$edit_project = null;
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$project_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch project for editing
if ($action === 'edit' && $project_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$project_id]);
        $edit_project = $stmt->fetch();
        if (!$edit_project) {
            $_SESSION['flash_error'] = 'Project not found.';
            header('Location: projects.php');
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['flash_error'] = 'Database error: ' . $e->getMessage();
        header('Location: projects.php');
        exit;
    }
}

// Handle Form Submission (Add / Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim(filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS));
    $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_SPECIAL_CHARS));
    $tech_stack = trim(filter_input(INPUT_POST, 'tech_stack', FILTER_SANITIZE_SPECIAL_CHARS));
    $live_url = trim(filter_input(INPUT_POST, 'live_url', FILTER_VALIDATE_URL));
    $github_url = trim(filter_input(INPUT_POST, 'github_url', FILTER_VALIDATE_URL));
    $submit_type = $_POST['submit_type']; // 'add' or 'edit'
    
    if (empty($title) || empty($description)) {
        $_SESSION['flash_error'] = 'Title and description are required.';
    } else {
        try {
            $image_path = isset($_POST['current_image']) ? $_POST['current_image'] : '';
            
            // Handle image upload
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES['image']['tmp_name'];
                $file_name = $_FILES['image']['name'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($file_ext, $allowed_exts)) {
                    // Check MIME type
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime_type = finfo_file($finfo, $file_tmp);
                    finfo_close($finfo);
                    
                    $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    
                    if (in_array($mime_type, $allowed_mimes)) {
                        // Unique name
                        $new_file_name = 'project_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
                        $dest_path = $upload_dir . $new_file_name;
                        
                        if (move_uploaded_file($file_tmp, $dest_path)) {
                            // Delete old image if it exists and we are replacing it
                            if (!empty($image_path) && file_exists(__DIR__ . '/../' . $image_path)) {
                                @unlink(__DIR__ . '/../' . $image_path);
                            }
                            $image_path = 'uploads/' . $new_file_name;
                        } else {
                            $_SESSION['flash_error'] = 'Failed to move uploaded file.';
                        }
                    } else {
                        $_SESSION['flash_error'] = 'Invalid file MIME type.';
                    }
                } else {
                    $_SESSION['flash_error'] = 'Invalid file extension. Only JPG, PNG, GIF, and WEBP allowed.';
                }
            }
            
            if (!isset($_SESSION['flash_error'])) {
                if ($submit_type === 'edit' && $project_id > 0) {
                    // Update project
                    $stmt = $pdo->prepare("UPDATE projects SET title = ?, description = ?, image = ?, tech_stack = ?, live_url = ?, github_url = ? WHERE id = ?");
                    $stmt->execute([$title, $description, $image_path, $tech_stack, $live_url, $github_url, $project_id]);
                    $_SESSION['flash_success'] = 'Project updated successfully.';
                } else {
                    // Add project
                    $stmt = $pdo->prepare("INSERT INTO projects (title, description, image, tech_stack, live_url, github_url) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$title, $description, $image_path, $tech_stack, $live_url, $github_url]);
                    $_SESSION['flash_success'] = 'Project added successfully.';
                }
                header('Location: projects.php');
                exit;
            }
        } catch (Exception $e) {
            $_SESSION['flash_error'] = 'Error saving project: ' . $e->getMessage();
        }
    }
}

// Handle Delete Action
if ($action === 'delete' && $project_id > 0) {
    try {
        // Fetch current image path to delete file from filesystem
        $stmtImage = $pdo->prepare("SELECT image FROM projects WHERE id = ?");
        $stmtImage->execute([$project_id]);
        $old_image = $stmtImage->fetchColumn();
        
        if ($old_image && file_exists(__DIR__ . '/../' . $old_image)) {
            @unlink(__DIR__ . '/../' . $old_image);
        }
        
        $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->execute([$project_id]);
        $_SESSION['flash_success'] = 'Project deleted successfully.';
    } catch (Exception $e) {
        $_SESSION['flash_error'] = 'Failed to delete project: ' . $e->getMessage();
    }
    header('Location: projects.php');
    exit;
}

// Fetch all projects for listing
$projects = [];
try {
    $projects = $pdo->query("SELECT * FROM projects ORDER BY id DESC")->fetchAll();
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

<?php if ($action === 'edit' && $edit_project): ?>
    <!-- Edit Project Form -->
    <div class="admin-card">
        <h3 class="card-title">Edit Project</h3>
        <form action="projects.php?action=edit&id=<?php echo $project_id; ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="submit_type" value="edit">
            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($edit_project['image']); ?>">
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="title">Project Title</label>
                    <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($edit_project['title']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="tech_stack">Tech Stack (comma-separated)</label>
                    <input type="text" id="tech_stack" name="tech_stack" class="form-control" placeholder="e.g. PHP, MySQL, CSS Grid" value="<?php echo htmlspecialchars($edit_project['tech_stack']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="live_url">Live URL</label>
                    <input type="url" id="live_url" name="live_url" class="form-control" placeholder="https://example.com" value="<?php echo htmlspecialchars($edit_project['live_url']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="github_url">GitHub URL</label>
                    <input type="url" id="github_url" name="github_url" class="form-control" placeholder="https://github.com/..." value="<?php echo htmlspecialchars($edit_project['github_url']); ?>">
                </div>
                
                <div class="form-group full-width">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" required><?php echo htmlspecialchars($edit_project['description']); ?></textarea>
                </div>
                
                <div class="form-group full-width">
                    <label for="image">Project Image</label>
                    <input type="file" id="image" name="image" class="form-control image-upload-input" accept="image/*">
                    <div class="image-preview-container">
                        <img src="../<?php echo !empty($edit_project['image']) && file_exists(__DIR__ . '/../' . $edit_project['image']) ? htmlspecialchars($edit_project['image']) : 'assets/css/placeholder.jpg'; ?>" class="image-preview" alt="Preview" style="<?php echo !empty($edit_project['image']) ? '' : 'display:none;'; ?>">
                        <span style="font-size: 0.8rem; color: var(--text-secondary);">Select new image to replace current.</span>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">Update Project</button>
                <a href="projects.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
<?php else: ?>
    <!-- Project Creator (Add form) + List of projects -->
    <div class="admin-card">
        <h3 class="card-title">Add New Project</h3>
        <form action="projects.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="submit_type" value="add">
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="title">Project Title</label>
                    <input type="text" id="title" name="title" class="form-control" placeholder="Project Name" required>
                </div>
                
                <div class="form-group">
                    <label for="tech_stack">Tech Stack (comma-separated)</label>
                    <input type="text" id="tech_stack" name="tech_stack" class="form-control" placeholder="e.g. PHP, MySQL, CSS Grid">
                </div>
                
                <div class="form-group">
                    <label for="live_url">Live URL</label>
                    <input type="url" id="live_url" name="live_url" class="form-control" placeholder="https://example.com">
                </div>
                
                <div class="form-group">
                    <label for="github_url">GitHub URL</label>
                    <input type="url" id="github_url" name="github_url" class="form-control" placeholder="https://github.com/...">
                </div>
                
                <div class="form-group full-width">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" placeholder="Describe the project..." required></textarea>
                </div>
                
                <div class="form-group full-width">
                    <label for="image">Project Image</label>
                    <input type="file" id="image" name="image" class="form-control image-upload-input" accept="image/*">
                    <div class="image-preview-container">
                        <img src="" class="image-preview" alt="Preview" style="display: none;">
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="margin-top: 1.5rem;">Save Project</button>
        </form>
    </div>

    <!-- Project List -->
    <div class="admin-card">
        <h3 class="card-title">Project List</h3>
        <?php if (!empty($projects)): ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width: 10%;">Image</th>
                            <th style="width: 25%;">Title</th>
                            <th style="width: 35%;">Tech Stack</th>
                            <th style="width: 30%; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $proj): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($proj['image']) && file_exists(__DIR__ . '/../' . $proj['image'])): ?>
                                        <img src="../<?php echo htmlspecialchars($proj['image']); ?>" alt="" style="width: 48px; height: 48px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border);">
                                    <?php else: ?>
                                        <div style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; background: var(--bg); border: 1px solid var(--border); border-radius: 4px; color: var(--text-muted);">
                                            <svg viewBox="0 0 24 24" style="width: 20px; height: 20px; fill: currentColor;"><path d="M5 4h14v16H5zm2 2v12h10V6z"/></svg>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="font-weight: 500;"><?php echo htmlspecialchars($proj['title']); ?></td>
                                <td>
                                    <?php 
                                    $stack = explode(',', $proj['tech_stack']);
                                    foreach ($stack as $s): 
                                        if (trim($s) === '') continue;
                                    ?>
                                        <span class="badge badge-neutral" style="margin-right: 3px; font-size: 0.7rem;"><?php echo htmlspecialchars(trim($s)); ?></span>
                                    <?php endforeach; ?>
                                </td>
                                <td style="text-align: right;">
                                    <a href="projects.php?action=edit&id=<?php echo $proj['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                    <a href="projects.php?action=delete&id=<?php echo $proj['id']; ?>" class="btn btn-danger btn-sm confirm-delete" data-confirm-message="Are you sure you want to delete this project? This will also remove the image file.">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                <p>No projects have been added yet.</p>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
