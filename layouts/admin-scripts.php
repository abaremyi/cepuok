<?php
/**
 * Admin Scripts Layout
 * File: layouts/admin-scripts.php
 */
?>
<!-- JS Implementing Plugins -->
<script src="<?= BASE_URL ?>/dashboard-assets/js/vendor.min.js"></script>

<!-- JS Front -->
<script src="<?= BASE_URL ?>/dashboard-assets/js/theme.min.js"></script>

<!-- Custom Admin JS -->
<script src="<?= BASE_URL ?>/dashboard-assets/js/admin.js"></script>

<script>
$(document).ready(function() {
    // Initialize sidebar
    new HSSideNav('.js-navbar-vertical-aside').init();
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
    
    // Load unread messages count
    function loadUnreadMessages() {
        $.get('<?= BASE_URL ?>/api/messages?action=getUnreadCount', function(response) {
            if (response.success && response.count > 0) {
                $('#unreadMessagesCount').text(response.count).show();
            } else {
                $('#unreadMessagesCount').hide();
            }
        });
    }
    
    loadUnreadMessages();
    setInterval(loadUnreadMessages, 60000); // Refresh every minute
});
</script>

</body>
</html>