<?php
// Initialize database connection
require_once __DIR__ . '/config/database.php';

// Fetch settings
$settings = [];
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {
    // Graceful fallback
}

// Settings fallback values
$site_name = $settings['site_name'] ?? 'Alex Rivers | Portfolio';
$hero_name = $settings['hero_name'] ?? 'ALEX RIVERS';
$hero_title = $settings['hero_title'] ?? 'FULL-STACK DEVELOPER';
$hero_tagline = $settings['hero_tagline'] ?? 'Building high-performance backend systems and elegant, interactive frontend interfaces.';
$about_bio = $settings['about_bio'] ?? 'I am a developer and designer specializing in custom web applications. I focus on minimal, clean layouts combined with strong backend architectures.';
$about_photo = $settings['about_photo'] ?? '';
$default_theme = $settings['default_theme'] ?? 'dark';

// Fetch contact info
$contact_info = ['email' => 'alex.rivers@example.com', 'phone' => '+1 (555) 234-5678', 'location' => 'San Francisco, CA'];
try {
    $stmt = $pdo->query("SELECT email, phone, location FROM contact_info LIMIT 1");
    $contact = $stmt->fetch();
    if ($contact) {
        $contact_info = $contact;
    }
} catch (Exception $e) {}

// Fetch social links
$social_links = [];
try {
    $stmt = $pdo->query("SELECT platform, url FROM social_links");
    $social_links = $stmt->fetchAll();
} catch (Exception $e) {}

// Fetch skills
$skills = [];
try {
    $stmt = $pdo->query("SELECT name, level, category, description FROM skills ORDER BY category, level DESC");
    $skills = $stmt->fetchAll();
} catch (Exception $e) {}

// Fetch projects
$projects = [];
try {
    $stmt = $pdo->query("SELECT title, description, image, tech_stack, live_url, github_url FROM projects ORDER BY id DESC");
    $projects = $stmt->fetchAll();
} catch (Exception $e) {}

// Fetch hobbies
$hobbies = [];
try {
    $stmt = $pdo->query("SELECT name, icon, description FROM hobbies ORDER BY id ASC");
    $hobbies = $stmt->fetchAll();
} catch (Exception $e) {}

// Group skills by category
$grouped_skills = [];
foreach ($skills as $skill) {
    $grouped_skills[$skill['category']][] = $skill;
}

// Handle AJAX contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (
    (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') ||
    (isset($_SERVER['HTTP_X_REQUEST_WITH']) && $_SERVER['HTTP_X_REQUEST_WITH'] === 'XMLHttpRequest')
)) {
    header('Content-Type: application/json');
    
    // Clean and validate input
    $name = isset($_POST['name']) ? trim(filter_var($_POST['name'], FILTER_SANITIZE_SPECIAL_CHARS)) : '';
    $email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) : false;
    $message = isset($_POST['message']) ? trim(filter_var($_POST['message'], FILTER_SANITIZE_SPECIAL_CHARS)) : '';
    
    if (empty($name) || !$email || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Please provide a valid name, email, and message.']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $message]);
        
        // Optional: Send real email if configured (configured here as placeholder)
        // mail($contact_info['email'], "Portfolio Message from $name", $message, "From: $email");
        
        echo json_encode(['success' => true, 'message' => 'Thank you! Your message has been sent successfully.']);
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'An error occurred while saving your message. Please try again.']);
        exit;
    }
}

// Helper function to return social icons
function getSocialIcon($platform) {
    $platform = strtolower(trim($platform));
    
    switch ($platform) {
        case 'github':
            return '<svg viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>';
        case 'linkedin':
            return '<svg viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.779-1.75-1.75s.784-1.75 1.75-1.75 1.75.779 1.75 1.75-.784 1.75-1.75 1.75zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>';
        case 'twitter':
        case 'x':
            return '<svg viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>';
        case 'instagram':
            return '<svg viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>';
        default:
            return '<svg viewBox="0 0 24 24"><path d="M12 2c5.52 0 10 4.48 10 10s-4.48 10-10 10-10-4.48-10-10 4.48-10 10-10zm0 1.8c-4.52 0-8.2 3.68-8.2 8.2 0 4.52 3.68 8.2 8.2 8.2 4.52 0 8.2-3.68 8.2-8.2 0-4.52-3.68-8.2-8.2-8.2zm0 2.2c1.77 0 3.2 1.43 3.2 3.2s-1.43 3.2-3.2 3.2-3.2-1.43-3.2-3.2 1.43-3.2 3.2-3.2zm0 1.5c-.94 0-1.7.76-1.7 1.7s.76 1.7 1.7 1.7 1.7-.76 1.7-1.7-.76-1.7-1.7-1.7z"/></svg>';
    }
}

// Helper function to return hobby icons
function getHobbyIcon($icon) {
    $icon = strtolower(trim($icon));
    switch ($icon) {
        case 'camera':
            return '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hobby-icon-svg"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>';
        case 'gamepad':
            return '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hobby-icon-svg"><line x1="6" y1="12" x2="10" y2="12"></line><line x1="8" y1="10" x2="8" y2="14"></line><line x1="15" y1="13" x2="15.01" y2="13"></line><line x1="18" y1="11" x2="18.01" y2="11"></line><rect x="2" y="6" width="20" height="12" rx="3"></rect></svg>';
        case 'code':
            return '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hobby-icon-svg"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>';
        case 'book':
            return '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hobby-icon-svg"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>';
        case 'music':
            return '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hobby-icon-svg"><path d="M9 18V5l12-2v13"></path><circle cx="6" cy="18" r="3"></circle><circle cx="18" cy="16" r="3"></circle></svg>';
        case 'bike':
            return '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hobby-icon-svg"><circle cx="5.5" cy="17.5" r="2.5"></circle><circle cx="18.5" cy="17.5" r="2.5"></circle><path d="M15 6h5a1 1 0 0 1 1 1v2"></path><path d="M12 11.5L8.5 5.5H3"></path><path d="M12 11.5L9.5 17.5"></path><path d="M12 11.5h6.5L15 6"></path></svg>';
        case 'travel':
            return '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hobby-icon-svg"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>';
        case 'heart':
        default:
            return '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hobby-icon-svg"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>';
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo htmlspecialchars($default_theme); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($hero_name) . ' - ' . htmlspecialchars($hero_title) . ' | Portfolio'; ?>">
    <title><?php echo htmlspecialchars($site_name); ?></title>
    <link rel="stylesheet" href="assets/css/style.css?v=1.1">
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <a href="#hero" class="logo"><?php echo htmlspecialchars(explode(' ', $hero_name)[0] ?? 'PORTFOLIO'); ?></a>
            
            <ul class="nav-menu" id="nav-menu">
                <li><a href="#hero" class="nav-link active">Home</a></li>
                <li><a href="#about" class="nav-link">About</a></li>
                <?php if (!empty($skills)): ?>
                    <li><a href="#skills" class="nav-link">Skills</a></li>
                <?php endif; ?>
                <?php if (!empty($projects)): ?>
                    <li><a href="#projects" class="nav-link">Projects</a></li>
                <?php endif; ?>
                <?php if (!empty($hobbies)): ?>
                    <li><a href="#hobbies" class="nav-link">Hobbies</a></li>
                <?php endif; ?>
                <li><a href="#contact" class="nav-link">Contact</a></li>
            </ul>
            
            <div class="nav-controls">
                <button class="theme-toggle" id="theme-toggle" aria-label="Toggle Theme">
                    <!-- Moon Icon -->
                    <svg class="moon-icon" viewBox="0 0 24 24">
                        <path d="M12.3 22h-.1c-5.5 0-10-4.5-10-10 0-4.8 3.5-8.9 8.2-9.7.5-.1 1 .2 1.2.7.2.5.1 1.1-.3 1.4-2.8 2.2-4.2 5.8-3.4 9.2.7 3.1 3.2 5.6 6.3 6.3 3.4.8 7-0.6 9.2-3.4.3-.4.9-.5 1.4-.3.5.2.8.7.7 1.2-.8 4.7-4.9 8.2-9.7 8.2z"/>
                    </svg>
                    <!-- Sun Icon -->
                    <svg class="sun-icon" viewBox="0 0 24 24">
                        <path d="M12 18c-3.3 0-6-2.7-6-6s2.7-6 6-6 6 2.7 6 6-2.7 6-6 6zm0-10c-2.2 0-4 1.8-4 4s1.8 4 4 4 4-1.8 4-4-1.8-4-4-4zm0-3c.6 0 1-.4 1-1V2c0-.6-.4-1-1-1s-1 .4-1 1v2c0 .6.4 1 1 1zm0 14c-.6 0-1 .4-1 1v2c0 .6.4 1 1 1s1-.4 1-1v-2c0-.6-.4-1-1-1zm8-7c0 .6.4 1 1 1h2c.6 0 1-.4 1-1s-.4-1-1-1h-2c-.6 0-1 .4-1 1zm-18 0c0 .6.4 1 1 1h2c.6 0 1-.4 1-1s-.4-1-1-1H3c-.6 0-1 .4-1 1zm13.7-5.7c.4.4 1 .4 1.4 0l1.4-1.4c.4-.4.4-1 0-1.4s-1-.4-1.4 0l-1.4 1.4c-.4.4-.4 1 0 1.4zm-11.4 11.4c.4.4 1 .4 1.4 0l1.4-1.4c.4-.4.4-1 0-1.4s-1-.4-1.4 0l-1.4 1.4c-.4.4-.4 1 0 1.4zm11.4 0c.4.4 1 .4 1.4 0l1.4-1.4c.4-.4.4-1 0-1.4s-1-.4-1.4 0l-1.4 1.4c-.4.4-.4 1 0 1.4zm-11.4-11.4c.4.4 1 .4 1.4 0l1.4-1.4c.4-.4.4-1 0-1.4s-1-.4-1.4 0l-1.4 1.4c-.4.4-.4 1 0 1.4z"/>
                    </svg>
                </button>
                
                <button class="hamburger" id="hamburger" aria-label="Toggle Menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="hero" class="container">
        <div class="hero-content reveal">
            <h1 class="hero-name"><?php echo htmlspecialchars($hero_name); ?></h1>
            <div class="hero-title-wrapper">
                <span id="typing-text" data-titles='<?php echo json_encode([$hero_title, "PROBLEM SOLVER", "SYSTEM ARCHITECT"]); ?>'></span>
                <span class="cursor"></span>
            </div>
            <p class="hero-tagline"><?php echo htmlspecialchars($hero_tagline); ?></p>
            <div class="hero-buttons">
                <a href="#projects" class="btn btn-primary">View Projects</a>
                <a href="#contact" class="btn btn-secondary">Contact Me</a>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="container">
        <h2 class="section-title reveal">About Me</h2>
        <div class="about-grid">
            <div class="about-photo-container glass-card reveal">
                <?php if (!empty($about_photo) && file_exists(__DIR__ . '/' . $about_photo)): ?>
                    <img src="<?php echo htmlspecialchars($about_photo); ?>" alt="<?php echo htmlspecialchars($hero_name); ?>" class="about-photo">
                <?php else: ?>
                    <div class="about-photo-placeholder">
                        <!-- Premium developer avatar visual placeholder -->
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="glass-card reveal">
                <p class="about-bio"><?php echo nl2br(htmlspecialchars($about_bio)); ?></p>
                
                <div class="about-details">
                    <div class="about-detail-item">
                        <div class="about-detail-label">Location</div>
                        <div class="about-detail-value"><?php echo htmlspecialchars($contact_info['location']); ?></div>
                    </div>
                    <div class="about-detail-item">
                        <div class="about-detail-label">Email</div>
                        <div class="about-detail-value"><?php echo htmlspecialchars($contact_info['email']); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php if (!empty($skills)): ?>
    <!-- Skills Section -->
    <section id="skills" class="container">
        <h2 class="section-title reveal">Skills & Expertise</h2>
        <div class="skills-grid">
            <?php foreach ($grouped_skills as $category => $categorySkills): ?>
                <div class="skill-category-card glass-card reveal">
                    <h3><?php echo htmlspecialchars($category); ?></h3>
                    <div class="skills-list">
                        <?php foreach ($categorySkills as $skill): ?>
                            <div class="skill-item clickable-skill" data-name="<?php echo htmlspecialchars($skill['name']); ?>" data-description="<?php echo htmlspecialchars($skill['description'] ?? ''); ?>">
                                <div class="skill-info">
                                    <span class="skill-name"><?php echo htmlspecialchars($skill['name']); ?></span>
                                    <span class="skill-level-num"><?php echo htmlspecialchars($skill['level']); ?>%</span>
                                </div>
                                <div class="skill-bar-bg">
                                    <div class="skill-bar-fill" data-level="<?php echo htmlspecialchars($skill['level']); ?>"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($projects)): ?>
    <!-- Projects Section -->
    <section id="projects" class="container">
        <h2 class="section-title reveal">Selected Works</h2>
        <div class="projects-grid">
            <?php foreach ($projects as $project): ?>
                <div class="project-card glass-card reveal">
                    <div class="project-image-container">
                        <?php if (!empty($project['image']) && file_exists(__DIR__ . '/' . $project['image'])): ?>
                            <img src="<?php echo htmlspecialchars($project['image']); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>" class="project-image">
                        <?php else: ?>
                            <div class="project-image-placeholder">
                                <!-- Dynamic Project Mockup Icon -->
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                                    <line x1="8" y1="21" x2="16" y2="21"></line>
                                    <line x1="12" y1="17" x2="12" y2="21"></line>
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="project-info">
                        <h3 class="project-title"><?php echo htmlspecialchars($project['title']); ?></h3>
                        <p class="project-desc"><?php echo htmlspecialchars($project['description']); ?></p>
                        
                        <div class="project-tech">
                            <?php 
                            $tags = explode(',', $project['tech_stack']);
                            foreach ($tags as $tag): 
                                if (trim($tag) === '') continue;
                            ?>
                                <span class="tech-tag"><?php echo htmlspecialchars(trim($tag)); ?></span>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="project-links">
                            <?php if (!empty($project['live_url'])): ?>
                                <a href="<?php echo htmlspecialchars($project['live_url']); ?>" target="_blank" rel="noopener noreferrer" class="project-link">
                                    Live Demo
                                    <svg viewBox="0 0 24 24"><path d="M21 13v10h-21v-19h12v2h-10v15h17v-8h2zm3-12h-10.988l4.035 4-6.914 7 1.786 1.8 6.913-7 3.968 4v-9.8z"/></svg>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($project['github_url'])): ?>
                                <a href="<?php echo htmlspecialchars($project['github_url']); ?>" target="_blank" rel="noopener noreferrer" class="project-link">
                                    Code
                                    <svg viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if (!empty($hobbies)): ?>
    <!-- Hobbies Section -->
    <section id="hobbies" class="container">
        <h2 class="section-title reveal">Hobbies & Interests</h2>
        <div class="hobbies-grid">
            <?php foreach ($hobbies as $hobby): ?>
                <div class="hobby-card glass-card reveal">
                    <div class="hobby-icon-wrapper">
                        <?php echo getHobbyIcon($hobby['icon']); ?>
                    </div>
                    <h3 class="hobby-name"><?php echo htmlspecialchars($hobby['name']); ?></h3>
                    <p class="hobby-description"><?php echo htmlspecialchars($hobby['description']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Contact Section -->
    <section id="contact" class="container">
        <h2 class="section-title reveal">Get In Touch</h2>
        <div class="contact-grid">
            <div class="contact-details reveal">
                <div class="contact-card-info glass-card">
                    <div class="contact-item">
                        <div class="contact-icon">
                            <!-- Mail Icon -->
                            <svg viewBox="0 0 24 24"><path d="M12 12.713l-11.985-9.713h23.97l-11.985 9.713zm0 2.574l12-9.725v15.438h-24v-15.438l12 9.725z"/></svg>
                        </div>
                        <div class="contact-text">
                            <h4>Email</h4>
                            <p><?php echo htmlspecialchars($contact_info['email']); ?></p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <!-- Phone Icon -->
                            <svg viewBox="0 0 24 24"><path d="M20 22.622l-3.276-3.276c-.075-.075-.11-.172-.11-.277 0-.585.992-1.939 1.266-2.5 1.019-2.077-1.122-4.148-3.117-3.056-.475.26-.821.574-1.208.961-.75.75-2.222.062-3.276-1.042-1.054-1.104-1.742-2.576-.992-3.326.387-.387.701-.733.961-1.208 1.092-1.995-.979-4.136-3.056-3.117-.561.274-1.915 1.266-2.5 1.266-.105 0-.202-.035-.277-.11l-3.276-3.276c-.525-.525-.525-1.379 0-1.904l1.622-1.622c1.24-1.24 3.322-.93 4.254.606l1.503 2.479c.677 1.116.368 2.587-.697 3.315-.178.122-.303.268-.456.421-.573.573-.207 1.838.647 2.734 1.171 1.23 2.766 2.825 3.996 3.996.896.854 2.161 1.22 2.734.647.153-.153.299-.278.421-.456.728-1.065 2.199-1.374 3.315-.697l2.479 1.503c1.536.932 1.846 3.014.606 4.254l-1.622 1.622c-.525.525-1.379.525-1.904 0z"/></svg>
                        </div>
                        <div class="contact-text">
                            <h4>Phone</h4>
                            <p><?php echo htmlspecialchars($contact_info['phone']); ?></p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">
                            <!-- Map Pin Icon -->
                            <svg viewBox="0 0 24 24"><path d="M12 0c-4.198 0-8 3.103-8 7.302 0 3.729 3.102 7.841 7.54 12.384.25.256.67.256.92 0 4.438-4.543 7.54-8.655 7.54-12.384 0-4.199-3.801-7.302-8-7.302zm0 10c-1.657 0-3-1.343-3-3s1.343-3 3-3 3 1.343 3 3-1.343 3-3 3z"/></svg>
                        </div>
                        <div class="contact-text">
                            <h4>Location</h4>
                            <p><?php echo htmlspecialchars($contact_info['location']); ?></p>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($social_links)): ?>
                    <div class="social-links-wrapper">
                        <h4>Connect with me</h4>
                        <div class="social-icons">
                            <?php foreach ($social_links as $social): ?>
                                <a href="<?php echo htmlspecialchars($social['url']); ?>" target="_blank" rel="noopener noreferrer" class="social-icon" aria-label="<?php echo htmlspecialchars($social['platform']); ?>">
                                    <?php echo getSocialIcon($social['platform']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="glass-card reveal">
                <form id="contact-form" class="contact-form" novalidate>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" class="form-control" placeholder="Your Name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="your.email@example.com" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" class="form-control" placeholder="Write your message here..." required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="align-self: flex-start;">Send Message</button>
                </form>
            </div>
        </div>
    </section>
    <!-- Skill Details Modal -->
    <div class="modal" id="skill-modal" aria-hidden="true">
        <div class="modal-overlay" id="skill-modal-overlay"></div>
        <div class="modal-container glass-card">
            <button class="modal-close" id="skill-modal-close" aria-label="Close modal">&times;</button>
            <h3 class="modal-title" id="skill-modal-name">Skill Name</h3>
            <div class="modal-content">
                <p class="modal-description" id="skill-modal-desc">Skill description goes here.</p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container footer-content">
            <p class="footer-text">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($hero_name); ?>. All rights reserved.</p>
            <p class="footer-text"><a href="admin/login.php" style="border-bottom: 1px solid var(--text-muted);">Admin Portal</a></p>
        </div>
    </footer>

    <script src="assets/js/main.js?v=1.1"></script>
</body>
</html>
