<?php
if (!defined('ABSPATH')) exit;

// Generate the signup URL for the "Create Account" link
$signup_url = admin_url('admin.php?page=seodevi-signup');
if (seodevi_is_logged_in()) {
    echo '<script>window.location.href="' . admin_url('admin.php?page=seodevi-smart-links') . '";</script>';
    exit;
}

?>

<div class="wrap seodevi-login-container">
    <div class="seodevi-account-box" style="max-width: 400px; margin: 50px auto;">
        <h1 style="text-align: center;">🚀 SEODevi Login</h1>
        <p class="description" style="text-align: center;">
            Enter your credentials to access smart link suggestions.
        </p>

        <hr>

        <form id="seodevi-login-form" method="POST">
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="email">Email</label></th>
                    <td>
                        <input name="email" type="email" id="email" value="" class="regular-text" required placeholder="name@example.com">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="password">Password</label></th>
                    <td>
                        <input name="password" type="password" id="password" value="" class="regular-text" required>
                        <p class="description">Forgot password? <a href="#" id="forgot-password">Reset it here</a></p>
                    </td>
                </tr>
            </table>

            <div id="login-message" style="margin: 10px 0; display: none;"></div>

            <p class="submit" style="text-align: center;">
                <button type="submit" name="submit" id="submit" class="button button-primary">Sign In</button>
            </p>
        </form>

        <p style="text-align: center; margin-top: 20px;">
            Don't have an account? <a href="<?php echo esc_url($signup_url); ?>">Create one here</a>.
        </p>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        // Check if already logged in
        $.ajax({
            url: seodeviConfig.ajaxUrl,
            method: 'POST',
            data: {
                action: 'seodevi_check_auth',
                nonce: seodeviConfig.nonce
            },
            success: function(response) {
                if (response.logged_in) {
                    window.location.href = seodeviConfig.dashboardUrl;
                }
            }
        });

        $('#seodevi-login-form').on('submit', function(e) {
            e.preventDefault();

            const $message = $('#login-message');
            const $btn = $('#submit');

            const formData = {
                email: $('#email').val(),
                password: $('#password').val()
            };

            $btn.attr('disabled', true).text('Logging in...');
            $message.hide().removeClass('updated error');

            // Use the pre-defined login URL
            $.ajax({
                url: seodeviConfig.loginUrl,
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(formData),
                // Inside the #seodevi-login-form submit handler success callback:
                success: function(response) {
                    if (response.success && response.token) {
                        $message.addClass('updated').text('Login successful! Redirecting...').show();

                        // Store the full user object from the backend into the WP Session
                        $.ajax({
                            url: seodeviConfig.ajaxUrl,
                            method: 'POST',
                            data: {
                                action: 'seodevi_store_auth',
                                nonce: seodeviConfig.nonce,
                                token: response.token,
                                user_data: JSON.stringify(response.user)
                            },
                            success: function() {
                                window.location.href = seodeviConfig.dashboardUrl;
                            }
                        });
                    } else {
                        $message.addClass('error').text(response.message || 'Login failed. Please try again.').show();
                        $btn.attr('disabled', false).text('Sign In');
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'Login failed. Please check your credentials.';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.status === 0) {
                        errorMsg = 'Cannot connect to server. Please check your internet connection.';
                    } else if (xhr.status === 404) {
                        errorMsg = 'Login endpoint not found. Please contact support.';
                    } else if (xhr.status >= 500) {
                        errorMsg = 'Server error. Please try again later.';
                    }

                    $message.addClass('error').text(errorMsg).show();
                    $btn.attr('disabled', false).text('Sign In');
                }
            });
        });

        $('#forgot-password').on('click', function(e) {
            e.preventDefault();
            alert('Password reset feature coming soon!');
        });
    });
</script>