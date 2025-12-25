<?php
if (!defined('ABSPATH')) exit;

$is_logged_in = seodevi_is_logged_in();
$token = seodevi_get_token(); // Get the JWT
?>

<div class="wrap seodevi-wrap">
    <h1>🚀 SEODevi Smart Links</h1>
    <div class="seodevi-account-box">
        <h2>👤 Account Status</h2>
        <div id="seodevi-profile-content">
            <?php if ($is_logged_in): ?>
                <p>Loading account details...</p>
            <?php else: ?>
                <p>You are not logged in.</p>
                <a href="<?php echo admin_url('admin.php?page=seodevi-login'); ?>" class="button button-primary">Login</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    const token = '<?php echo esc_js($token); ?>';
    
    if (token) {
        // Fetch LIVE data from the Backend
        $.ajax({
            url: 'http://localhost:5000/api/dashboard/profile', // Your backend profile endpoint
            method: 'GET',
            headers: { 'Authorization': 'Bearer ' + token },
            success: function(response) {
                if (response.success) {
                    const u = response.profile;
                    $('#seodevi-profile-content').html(`
                        <p><strong>Name:</strong> ${u.name}</p>
                        <p><strong>Email:</strong> ${u.email}</p>
                        <p><strong>Plan:</strong> <span class="seodevi-plan-badge">${u.plan.toUpperCase()}</span></p>
                        <p><strong>Verified:</strong> ${u.verified ? '✅ Yes' : '❌ No'}</p>
                        <button class="button button-secondary" onclick="seodeviLogout()">Logout</button>
                    `);
                }
            },
            error: function() {
                $('#seodevi-profile-content').html('<p>Error loading profile. Please log in again.</p>');
            }
        });
    }
});

function seodeviLogout() {
    if (confirm('Are you sure you want to logout?')) {
        jQuery.ajax({
            url: seodeviConfig.ajaxUrl,
            method: 'POST',
            data: {
                action: 'seodevi_logout',
                nonce: seodeviConfig.nonce // Critical for security
            },
            success: function() {
                window.location.href = seodeviConfig.loginUrl;
            }
        });
    }
}
</script>