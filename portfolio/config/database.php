<?php
// Database Configuration (Dynamic Environment Setup)
if (!isset($_SERVER['HTTP_HOST']) || 
    $_SERVER['HTTP_HOST'] === 'localhost' || 
    $_SERVER['HTTP_HOST'] === '127.0.0.1' || 
    str_contains($_SERVER['HTTP_HOST'], 'localhost') || 
    str_contains($_SERVER['HTTP_HOST'], '127.0.0.1')) {
    // Localhost Environment
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'portfolio');
} else {
    // InfinityFree Production Environment
    define('DB_HOST', 'sql103.infinityfree.com');
    define('DB_USER', 'if0_37771869');
    define('DB_PASS', '11102003joel');
    define('DB_NAME', 'if0_37771869_portfolio');
}

try {
    // 1. Connect to MySQL server (without selecting DB to ensure DB can be created if missing)
    $pdo = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    // 2. Create the database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // 3. Select the database
    $pdo->exec("USE `" . DB_NAME . "`");
    
    // 4. Auto-initialize tables and seed data if tables are missing
    checkAndInitDatabase($pdo);
    
} catch (PDOException $e) {
    // If connection fails, show a beautiful, user-friendly message (instead of breaking the page silently)
    die("<div style='font-family: sans-serif; padding: 2rem; background: #0a0a0a; color: #fff; height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center;'>
            <h1 style='font-size: 2.5rem; margin-bottom: 1rem;'>Database Connection Failed</h1>
            <p style='color: #a0a0a0; max-width: 600px; margin-bottom: 2rem; line-height: 1.6;'>
                Please verify that MySQL is running and check your database credentials in <code>portfolio/config/database.php</code>.
            </p>
            <div style='background: #1a1a1a; padding: 1rem; border-radius: 8px; border: 1px solid #333; font-family: monospace; color: #ff5555; text-align: left;'>
                Error: " . htmlspecialchars($e->getMessage()) . "
            </div>
         </div>");
}

/**
 * Checks if the required tables exist, and if not, runs migrations and inserts default seeds.
 */
function checkAndInitDatabase($pdo) {
    // Check if the 'admins' table exists
    $tableExists = false;
    try {
        $result = $pdo->query("SELECT 1 FROM admins LIMIT 1");
        $tableExists = true;
    } catch (Exception $e) {
        $tableExists = false;
    }
    
    if (!$tableExists) {
        // Run SQL schema queries
        $sql = "
        CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS projects (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            image VARCHAR(255) NULL,
            tech_stack VARCHAR(255) NOT NULL,
            live_url VARCHAR(255) NULL,
            github_url VARCHAR(255) NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS skills (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            level INT NOT NULL DEFAULT 0,
            category VARCHAR(100) NOT NULL,
            description TEXT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS contact_info (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(100) NOT NULL,
            phone VARCHAR(50) NULL,
            location VARCHAR(255) NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS social_links (
            id INT AUTO_INCREMENT PRIMARY KEY,
            platform VARCHAR(50) NOT NULL,
            url VARCHAR(255) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS settings (
            setting_key VARCHAR(50) PRIMARY KEY,
            setting_value TEXT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

        CREATE TABLE IF NOT EXISTS hobbies (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            icon VARCHAR(50) NULL,
            description VARCHAR(255) NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $pdo->exec($sql);
        
        // Seed default admin: admin / admin123
        $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
        $stmt->execute(['admin', $adminPass]);
        
        // Seed default site-wide settings
        $settings = [
            'hero_name' => 'ALEX RIVERS',
            'hero_title' => 'FULL-STACK DEVELOPER & ARCHITECT',
            'hero_tagline' => 'Building high-performance backend systems and elegant, interactive frontend interfaces.',
            'about_bio' => 'I am a developer and designer specializing in custom web applications. I focus on minimal, clean layouts combined with strong backend architectures. When I am not writing code, I design user interfaces and explore modern system design methodologies.',
            'about_photo' => '', // empty means it will fallback to a beautiful SVG visual placeholder
            'default_theme' => 'dark',
            'site_name' => 'Alex Rivers | Portfolio'
        ];
        
        $stmtSettings = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
        foreach ($settings as $key => $val) {
            $stmtSettings->execute([$key, $val]);
        }
        
        // Seed default contact details
        $stmtContact = $pdo->prepare("INSERT INTO contact_info (email, phone, location) VALUES (?, ?, ?)");
        $stmtContact->execute(['alex.rivers@example.com', '+1 (555) 234-5678', 'San Francisco, CA']);
        
        // Seed default social links
        $socials = [
            ['GitHub', 'https://github.com'],
            ['LinkedIn', 'https://linkedin.com'],
            ['Twitter', 'https://twitter.com']
        ];
        $stmtSocial = $pdo->prepare("INSERT INTO social_links (platform, url) VALUES (?, ?)");
        foreach ($socials as $s) {
            $stmtSocial->execute([$s[0], $s[1]]);
        }
        
        // Seed default skills
        $skills = [
            ['HTML5 & CSS3', 95, 'Frontend', 'Expertise in modern CSS layout techniques including Grid, Flexbox, Custom Properties, and responsive media queries.'],
            ['JavaScript (ES6+)', 92, 'Frontend', 'Proficient in vanilla ES6+ JS, covering asynchronous programming, custom DOM interactions, and API communication.'],
            ['PHP (PDO & OOP)', 88, 'Backend', 'Developing scalable backend systems utilizing Object-Oriented principles, secure database preparation, and custom session middleware.'],
            ['MySQL & Redis', 85, 'Backend', 'Designing optimized relational database schemas, handling complex joins, indexing, and utilizing Redis for caching.'],
            ['RESTful APIs', 90, 'Backend', 'Designing and consuming HTTP endpoints with secure token/session authentication and JSON responses.'],
            ['Figma & UI Design', 82, 'Design', 'Crafting modern UI layouts, design tokens, and components using Figma to establish clean design systems.']
        ];
        $stmtSkill = $pdo->prepare("INSERT INTO skills (name, level, category, description) VALUES (?, ?, ?, ?)");
        foreach ($skills as $sk) {
            $stmtSkill->execute([$sk[0], $sk[1], $sk[2], $sk[3]]);
        }
        
        // Seed default projects
        $projects = [
            [
                'Nebula E-Commerce', 
                'A high-performance e-commerce platform built with raw PHP, offering security-hardened transaction processing, a responsive glassmorphic UI, and product inventory metrics dashboards.',
                '',
                'PHP, MySQL, Vanilla JS, CSS Custom Properties',
                'https://github.com',
                'https://github.com'
            ],
            [
                'Vortex Logistics System',
                'An interactive logistics tracing dashboard with visual metrics, real-time map plotting using Leaflet, and asynchronous data sync via custom background workers.',
                '',
                'JavaScript, HTML5, CSS Grid, JSON APIs',
                'https://github.com',
                'https://github.com'
            ]
        ];
        $stmtProj = $pdo->prepare("INSERT INTO projects (title, description, image, tech_stack, live_url, github_url) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($projects as $pr) {
            $stmtProj->execute([$pr[0], $pr[1], $pr[2], $pr[3], $pr[4], $pr[5]]);
        }

        // Seed default hobbies
        $hobbies = [
            ['Photography', 'camera', 'Capturing landscape and street photography.'],
            ['Gaming', 'gamepad', 'Playing strategy and RPG games.'],
            ['Open Source', 'code', 'Contributing to GitHub projects.']
        ];
        $stmtHobby = $pdo->prepare("INSERT INTO hobbies (name, icon, description) VALUES (?, ?, ?)");
        foreach ($hobbies as $hb) {
            $stmtHobby->execute([$hb[0], $hb[1], $hb[2]]);
        }
    }
}
