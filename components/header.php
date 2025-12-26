<?php
if (!defined('ABSPATH')) exit;

$is_logged_in = seodevi_is_logged_in();

if (!$is_logged_in) {
    echo '<script>window.location.href="' . admin_url('admin.php?page=seodevi-login') . '";</script>';
    exit;
}

$current_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
?>

<header class="seodevi-header">
    <div class="seodevi-header-container">
        <div class="seodevi-logo">
            <h1><span class="seodevi-logo-icon">🚀</span> SEODevi</h1>
            <span class="seodevi-version">v1.0</span>
        </div>
        
        <nav class="seodevi-nav">
            <a href="<?php echo esc_url(admin_url('admin.php?page=dashboard')); ?>"
               class="seodevi-nav-link <?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">
                <span class="dashicons dashicons-dashboard"></span>
                <span class="seodevi-nav-text">Dashboard</span>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=seodevi-pricing')); ?>"
               class="seodevi-nav-link <?php echo $current_page === 'seodevi-pricing' ? 'active' : ''; ?>">
                <span class="dashicons dashicons-cart"></span>
                <span class="seodevi-nav-text">Pricing</span>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=seodevi-about')); ?>"
               class="seodevi-nav-link <?php echo $current_page === 'seodevi-about' ? 'active' : ''; ?>">
                <span class="dashicons dashicons-info"></span>
                <span class="seodevi-nav-text">About</span>
            </a>
        </nav>
        
        <div class="seodevi-header-right">
            <div class="seodevi-user-info">
                <span class="seodevi-user-icon dashicons dashicons-admin-users"></span>
                <span class="seodevi-user-name">
                    <?php 
                    $current_user = wp_get_current_user();
                    echo esc_html($current_user->display_name ?: $current_user->user_login);
                    ?>
                </span>
            </div>
            <!-- Logout button - triggers AJAX -->
            <button class="seodevi-logout-btn" id="seodevi-logout-btn">
                <span class="dashicons dashicons-exit"></span>
                <span class="seodevi-logout-text">Logout</span>
            </button>
        </div>
    </div>
</header>
<style>
    .seodevi-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        margin: -20px -20px 20px -20px;
        position: relative;
        z-index: 1000;
    }

    .seodevi-header-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 30px;
        min-height: 70px;
        max-width: 100%;
        flex-wrap: wrap;
    }

    .seodevi-logo {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .seodevi-logo h1 {
        margin: 0;
        font-size: 24px;
        font-weight: 700;
        color: white;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .seodevi-logo-icon {
        font-size: 28px;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.1);
        }

        100% {
            transform: scale(1);
        }
    }

    .seodevi-version {
        background: rgba(255, 255, 255, 0.2);
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }

    .seodevi-nav {
        display: flex;
        gap: 2px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        padding: 4px;
    }

    .seodevi-nav-link {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        color: rgba(255, 255, 255, 0.9);
        text-decoration: none;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .seodevi-nav-link:hover {
        background: rgba(255, 255, 255, 0.15);
        color: white;
        transform: translateY(-1px);
    }

    .seodevi-nav-link.active {
        background: white;
        color: #667eea;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .seodevi-nav-link .dashicons {
        font-size: 18px;
        width: 18px;
        height: 18px;
    }

    .seodevi-nav-text {
        font-size: 14px;
    }

    .seodevi-header-right {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .seodevi-user-info {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        transition: background 0.3s ease;
    }

    .seodevi-user-info:hover {
        background: rgba(255, 255, 255, 0.15);
    }

    .seodevi-user-icon {
        font-size: 20px;
        color: rgba(255, 255, 255, 0.9);
    }

    .seodevi-user-name {
        font-weight: 500;
        font-size: 14px;
        color: white;
    }

    .seodevi-logout-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        background: rgba(255, 255, 255, 0.1);
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.3s ease;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .seodevi-logout-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .seodevi-logout-btn .dashicons {
        font-size: 18px;
    }

    .seodevi-logout-text {
        white-space: nowrap;
    }

    /* Responsive design */
    @media (max-width: 1024px) {
        .seodevi-header-container {
            padding: 15px 20px;
            gap: 15px;
        }

        .seodevi-nav {
            order: 3;
            width: 100%;
            justify-content: center;
            margin-top: 10px;
        }

        .seodevi-header-container {
            flex-direction: column;
            align-items: stretch;
        }

        .seodevi-header-right {
            align-self: flex-end;
            margin-top: -50px;
        }
    }

    @media (max-width: 768px) {
        .seodevi-nav {
            flex-wrap: wrap;
        }

        .seodevi-nav-link {
            flex: 1;
            min-width: 120px;
            justify-content: center;
        }

        .seodevi-header-right {
            position: static;
            align-self: stretch;
            justify-content: space-between;
            margin-top: 15px;
            gap: 10px;
        }

        .seodevi-user-info {
            flex: 1;
        }

        .seodevi-logout-btn {
            flex: 1;
            justify-content: center;
        }
    }
</style>