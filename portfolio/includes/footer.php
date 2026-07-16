<?php if (!isset($is_login_page)): ?>
        </div> <!-- Close .admin-container -->
        
        <footer style="margin-top: auto; padding: 2rem 2.5rem; border-top: 1px solid var(--border); background: var(--bg-sec); text-align: center; font-size: 0.8rem; color: var(--text-muted);">
            &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_name); ?> Admin Panel.
        </footer>
    </div> <!-- Close .admin-main -->
<?php endif; ?>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
