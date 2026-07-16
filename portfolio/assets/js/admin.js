document.addEventListener('DOMContentLoaded', () => {
    initAlertFading();
    initImagePreview();
    initDeleteConfirmations();
});

/* --- Auto-fade Admin Alerts --- */
function initAlertFading() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    });
}

/* --- Image Upload Preview --- */
function initImagePreview() {
    const fileInput = document.querySelector('input[type="file"].image-upload-input');
    const previewImg = document.querySelector('img.image-preview');
    
    if (!fileInput || !previewImg) return;
    
    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            
            reader.addEventListener('load', function() {
                previewImg.setAttribute('src', this.result);
                previewImg.style.display = 'block';
            });
            
            reader.readAsDataURL(file);
        }
    });
}

/* --- Confirm Delete Actions --- */
function initDeleteConfirmations() {
    const deleteButtons = document.querySelectorAll('.confirm-delete');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm-message') || 'Are you sure you want to delete this item? This action cannot be undone.';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
}
