<?php
if (!defined('ABSPATH')) exit;

// Generate the login URL for the "Already have account" link
$login_url = admin_url('admin.php?page=seodevi-login');

// Check if already logged in
if (seodevi_is_logged_in()) {
    wp_redirect(admin_url('admin.php?page=seodevi-smart-links'));
    exit;
}
?>

<div class="wrap seodevi-signup-container">
    <div class="seodevi-account-box" style="max-width: 500px; margin: 50px auto;">
        <h1 style="text-align: center;">🚀 Create SEODevi Account</h1>
        <p class="description" style="text-align: center;">
            Sign up for free and start getting smart link suggestions.
        </p>

        <hr>

        <form id="seodevi-signup-form" method="POST">
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="name">Full Name</label></th>
                    <td>
                        <input name="name" type="text" id="name" value="" class="regular-text" required placeholder="John Doe">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="email">Email Address</label></th>
                    <td>
                        <input name="email" type="email" id="email" value="" class="regular-text" required placeholder="name@example.com">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="phone_no">Phone Number</label></th>
                    <td>
                        <input name="phone_no" type="tel" id="phone_no" value="" class="regular-text" required placeholder="1234567890">
                        <p class="description">10-15 digits only</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="password">Password</label></th>
                    <td>
                        <input name="password" type="password" id="password" value="" class="regular-text" required>
                        <p class="description">Minimum 6 characters</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="confirm_password">Confirm Password</label></th>
                    <td>
                        <input name="confirm_password" type="password" id="confirm_password" value="" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row"></th>
                    <td>
                        <label>
                            <input type="checkbox" id="terms" required>
                            I agree to the <a href="#" target="_blank">Terms of Service</a> and <a href="#" target="_blank">Privacy Policy</a>
                        </label>
                    </td>
                </tr>
            </table>

            <div id="signup-message" style="margin: 10px 0; display: none;"></div>

            <p class="submit" style="text-align: center;">
                <button type="submit" name="submit" id="submit" class="button button-primary">Create Account</button>
            </p>
        </form>

        <p style="text-align: center; margin-top: 20px;">
            Already have an account? <a href="<?php echo esc_url($login_url); ?>">Login here</a>.
        </p>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#seodevi-signup-form').on('submit', function(e) {
        e.preventDefault();
        
        const $message = $('#signup-message');
        const $btn = $('#submit');
        
        // Validate passwords match
        const password = $('#password').val();
        const confirmPassword = $('#confirm_password').val();
        
        if (password !== confirmPassword) {
            $message.addClass('error').text('Passwords do not match!').show();
            return;
        }
        
        if (password.length < 6) {
            $message.addClass('error').text('Password must be at least 6 characters!').show();
            return;
        }
        
        const formData = {
            name: $('#name').val(),
            email: $('#email').val(),
            password: password,
            phone_no: $('#phone_no').val()
        };

        $btn.attr('disabled', true).text('Creating Account...');
        $message.hide().removeClass('updated error');

        // Use the pre-defined register URL
        $.ajax({
            url: seodeviConfig.registerUrl, // This is the complete register URL
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                if (response.success && response.token) {
                    $message.addClass('updated').text('Account created successfully! Logging you in...').show();
                    
                    // Store token via AJAX to PHP session
                    $.ajax({
                        url: '<?php echo admin_url("admin-ajax.php"); ?>',
                        method: 'POST',
                        data: {
                            action: 'seodevi_store_auth',
                            _ajax_nonce: seodeviConfig.nonce,
                            token: response.token,
                            user_data: JSON.stringify(response.user)
                        },
                        success: function() {
                            setTimeout(() => {
                                window.location.href = '<?php echo admin_url('admin.php?page=seodevi-smart-links'); ?>';
                            }, 1500);
                        },
                        error: function() {
                            $message.addClass('error').text('Account created but login failed. Please try logging in manually.').show();
                            $btn.attr('disabled', false).text('Create Account');
                        }
                    });
                } else {
                    $message.addClass('error').text(response.message || 'Registration failed. Please try again.').show();
                    $btn.attr('disabled', false).text('Create Account');
                }
            },
            error: function(xhr) {
                let errorMsg = 'Registration failed. Please try again.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.status === 0) {
                    errorMsg = 'Cannot connect to server. Please check your internet connection.';
                } else if (xhr.status === 404) {
                    errorMsg = 'Registration endpoint not found. Please contact support.';
                } else if (xhr.status >= 500) {
                    errorMsg = 'Server error. Please try again later.';
                }
                
                $message.addClass('error').text(errorMsg).show();
                $btn.attr('disabled', false).text('Create Account');
            }
        });
    });
});
</script>