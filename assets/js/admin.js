jQuery(document).ready(function($) {
    $('#seodevi-logout-btn').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm('Are you sure you want to log out?')) return;
        
        $.ajax({
            url: seodeviConfig.ajaxUrl,
            type: 'POST',
            data: {
                action: 'seodevi_logout',
                nonce: seodeviConfig.nonce
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = seodeviConfig.logoutUrl;
                } else {
                    alert('Logout failed. Please try again.');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });
});