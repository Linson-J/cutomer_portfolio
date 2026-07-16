<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar" id="admin-sidebar">
    <div class="sidebar-brand">
        <span>PORTFOLIO ADMIN</span>
    </div>
    
    <nav class="sidebar-menu">
        <li>
            <a href="dashboard.php" class="sidebar-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
                <!-- Messages Icon -->
                <svg viewBox="0 0 24 24">
                    <path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-2 12H6v-2h12v2zm0-3H6V9h12v2zm0-3H6V6h12v2z"/>
                </svg>
                Messages
            </a>
        </li>
        <li>
            <a href="projects.php" class="sidebar-link <?php echo $current_page === 'projects.php' ? 'active' : ''; ?>">
                <!-- Briefcase / Projects Icon -->
                <svg viewBox="0 0 24 24">
                    <path d="M20 6h-4V4c0-1.11-.89-2-2-2h-4c-1.11 0-2 .89-2 2v2H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-8-2h4v2h-4V4zm8 15H4V8h16v11z"/>
                </svg>
                Projects
            </a>
        </li>
        <li>
            <a href="skills.php" class="sidebar-link <?php echo $current_page === 'skills.php' ? 'active' : ''; ?>">
                <!-- Code / Skills Icon -->
                <svg viewBox="0 0 24 24">
                    <path d="M9.4 16.6L4.8 12l4.6-4.6L8 6l-6 6 6 6 1.4-1.4zm5.2 0l4.6-4.6-4.6-4.6L16 6l6 6-6 6-1.4-1.4z"/>
                </svg>
                Skills
            </a>
        </li>
        <li>
            <a href="contact_social.php" class="sidebar-link <?php echo $current_page === 'contact_social.php' ? 'active' : ''; ?>">
                <!-- Mail / Contact Info Icon -->
                <svg viewBox="0 0 24 24">
                    <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                </svg>
                Contact & Socials
            </a>
        </li>
        <li>
            <a href="settings.php" class="sidebar-link <?php echo $current_page === 'settings.php' ? 'active' : ''; ?>">
                <!-- Settings Icon -->
                <svg viewBox="0 0 24 24">
                    <path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/>
                </svg>
                Site Settings
            </a>
        </li>
    </nav>
    
    <div class="sidebar-footer">
        <a href="logout.php" class="sidebar-link" style="color: var(--danger);">
            <!-- Logout Icon -->
            <svg viewBox="0 0 24 24">
                <path d="M10.09 15.59L11.5 17l5-5-5-5-1.41 1.41L12.67 11H3v2h9.67l-2.58 2.59zM19 3H5c-1.11 0-2 .9-2 2v4h2V5h14v14H5v-4H3v4c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/>
            </svg>
            Sign Out
        </a>
    </div>
</aside>
