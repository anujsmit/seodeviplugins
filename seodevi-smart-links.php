<?php
/**
 * Plugin Name: SEODevi Smart Links
 * Description: Internal & External Link Suggestions for SEO. Features a credit-based system for editorial link building.
 * Version: 1.0.0
 * Author: SEODevi
 */

if (!defined('ABSPATH')) exit;

/* ===============================
   CONSTANTS & DEFINITIONS
================================ */
define('SEODEVI_PATH', plugin_dir_path(__FILE__));
define('SEODEVI_URL', plugin_dir_url(__FILE__));
// API Base URL - change this to your actual API server
define('SEODEVI_API_BASE', 'http://localhost:5000/api/auth');

// Get complete API URLs
function seodevi_get_api_url($endpoint = '') {
    $base = defined('SEODEVI_API_BASE') ? SEODEVI_API_BASE : 'http://localhost:5000/api/auth';
    return rtrim($base, '/') . '/' . ltrim($endpoint, '/');
}

// Get specific API endpoints
function seodevi_get_login_url() {
    return seodevi_get_api_url('login');
}

function seodevi_get_register_url() {
    return seodevi_get_api_url('register');
}

function seodevi_get_profile_url() {
    return seodevi_get_api_url('profile');
}

/* ===============================
   SESSION MANAGEMENT
================================ */
add_action('init', 'seodevi_start_session', 1);

function seodevi_start_session() {
    if (!session_id() && !headers_sent()) {
        session_start();
    }
}

// Function to store token after login
function seodevi_store_token($token, $user_data) {
    if (!session_id() && !headers_sent()) {
        session_start();
    }
    
    $_SESSION['seodevi_token'] = $token;
    $_SESSION['seodevi_user'] = $user_data;
    $_SESSION['seodevi_token_expiry'] = time() + (7 * 24 * 60 * 60); // 7 days
    
    // Also store in options for persistence
    update_option('seodevi_token', $token);
    update_option('seodevi_user_data', $user_data);
    update_option('seodevi_last_login', current_time('timestamp'));
}

// Function to get current token
function seodevi_get_token() {
    if (!session_id() && !headers_sent()) {
        session_start();
    }
    
    // Check session first
    if (isset($_SESSION['seodevi_token']) && 
        isset($_SESSION['seodevi_token_expiry']) && 
        $_SESSION['seodevi_token_expiry'] > time()) {
        return $_SESSION['seodevi_token'];
    }
    
    // Clear expired session
    if (isset($_SESSION['seodevi_token_expiry']) && $_SESSION['seodevi_token_expiry'] <= time()) {
        unset($_SESSION['seodevi_token']);
        unset($_SESSION['seodevi_user']);
        unset($_SESSION['seodevi_token_expiry']);
    }
    
    // Fallback to option with expiry check
    $token = get_option('seodevi_token', '');
    $last_login = get_option('seodevi_last_login', 0);
    
    // Check if token is still valid (7 days)
    if ($token && $last_login && (current_time('timestamp') - $last_login) < (7 * 24 * 60 * 60)) {
        // Update session with option data
        if (!session_id() && !headers_sent()) {
            session_start();
        }
        $_SESSION['seodevi_token'] = $token;
        $_SESSION['seodevi_user'] = get_option('seodevi_user_data', []);
        $_SESSION['seodevi_token_expiry'] = $last_login + (7 * 24 * 60 * 60);
        
        return $token;
    }
    
    return '';
}

// Function to check if user is logged in
function seodevi_is_logged_in() {
    $token = seodevi_get_token();
    return !empty($token);
}

// Function to get user data
function seodevi_get_user_data() {
    if (!session_id() && !headers_sent()) {
        session_start();
    }
    
    if (isset($_SESSION['seodevi_user'])) {
        return $_SESSION['seodevi_user'];
    }
    
    return get_option('seodevi_user_data', []);
}

// Function to logout
function seodevi_logout() {
    if (!session_id() && !headers_sent()) {
        session_start();
    }
    
    unset($_SESSION['seodevi_token']);
    unset($_SESSION['seodevi_user']);
    unset($_SESSION['seodevi_token_expiry']);
    
    delete_option('seodevi_token');
    delete_option('seodevi_user_data');
    delete_option('seodevi_last_login');
}

/* ===============================
   EARLY AUTHENTICATION CHECK
================================ */
add_action('admin_init', 'seodevi_check_authentication');

function seodevi_check_authentication() {
    // Only check on SEODevi pages
    if (!isset($_GET['page'])) {
        return;
    }
    
    $current_page = sanitize_text_field($_GET['page']);
    $auth_pages = ['seodevi-login', 'seodevi-signup'];
    
    // If user is logged in and tries to access login or signup pages, redirect to dashboard
    if (seodevi_is_logged_in() && in_array($current_page, $auth_pages)) {
        wp_redirect(admin_url('admin.php?page=seodevi-smart-links'));
        exit; // Always exit after wp_redirect
    }
}

/* ===============================
   ADMIN MENU & SUBPAGES
================================ */
add_action('admin_menu', function () {
    // Main Dashboard
    add_menu_page(
        'SEODevi Smart Links',
        'SEODevi Links',
        'manage_options',
        'seodevi-smart-links',
        'seodevi_render_dashboard',
        'dashicons-admin-links',
        30
    );

    // Hidden Login Page
    add_submenu_page(
        null, // No parent (hidden from menu)
        'SEODevi Login',
        'Login',
        'manage_options',
        'seodevi-login',
        'seodevi_render_login'
    );

    // Hidden Signup Page
    add_submenu_page(
        null,
        'SEODevi Signup',
        'Signup',
        'manage_options',
        'seodevi-signup',
        'seodevi_render_signup'
    );
});

/* ===============================
   RENDER CALLBACKS
================================ */

/**
 * Renders the main dashboard 
 */
function seodevi_render_dashboard() {
    if (file_exists(SEODEVI_PATH . 'admin/Dashboard.php')) {
        include SEODEVI_PATH . 'admin/Dashboard.php';
    } else {
        echo '<div class="error"><p>Dashboard file missing.</p></div>';
    }
}

/**
 * Renders the login page 
 */
function seodevi_render_login() {
    if (file_exists(SEODEVI_PATH . 'admin/Login.php')) {
        include SEODEVI_PATH . 'admin/Login.php';
    } else {
        echo '<div class="error"><p>Login file missing.</p></div>';
    }
}

/**
 * Renders the signup page 
 */
function seodevi_render_signup() {
    if (file_exists(SEODEVI_PATH . 'admin/Signup.php')) {
        include SEODEVI_PATH . 'admin/Signup.php';
    } else {
        echo '<div class="error"><p>Signup file missing.</p></div>';
    }
}

/* ===============================
   ASSETS (CSS & JS)
================================ */
add_action('admin_enqueue_scripts', function ($hook) {
    // Only load assets on SEODevi related pages
    $seodevi_pages = [
        'toplevel_page_seodevi-smart-links',
        'admin_page_seodevi-login',
        'admin_page_seodevi-signup'
    ];

    if (!in_array($hook, $seodevi_pages)) return;

    // Load Styles 
    wp_enqueue_style(
        'seodevi-admin-css',
        SEODEVI_URL . 'admin.css',
        [],
        '1.0.0'
    );

    // Load Scripts 
    wp_enqueue_script(
        'seodevi-admin-js',
        SEODEVI_URL . 'admin.js',
        ['jquery'],
        '1.0.0',
        true
    );

    // Pass environment variables to JavaScript 
    wp_localize_script('seodevi-admin-js', 'seodeviConfig', [
        'apiBase' => seodevi_get_api_url(),
        'loginUrl' => seodevi_get_login_url(),
        'registerUrl' => seodevi_get_register_url(),
        'profileUrl' => seodevi_get_profile_url(),
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'dashboardUrl' => admin_url('admin.php?page=seodevi-smart-links'),
        'nonce' => wp_create_nonce('seodevi_admin_nonce')
    ]);
});

/* ===============================
   AJAX HANDLERS
================================ */
add_action('wp_ajax_seodevi_store_auth', 'seodevi_ajax_store_auth');
add_action('wp_ajax_seodevi_logout', 'seodevi_ajax_logout');
add_action('wp_ajax_seodevi_get_profile', 'seodevi_ajax_get_profile');
add_action('wp_ajax_seodevi_check_auth', 'seodevi_ajax_check_auth');

function seodevi_ajax_store_auth() {
    check_ajax_referer('seodevi_admin_nonce', 'nonce');
    
    $token = isset($_POST['token']) ? sanitize_text_field($_POST['token']) : '';
    $user_data = isset($_POST['user_data']) ? json_decode(stripslashes($_POST['user_data']), true) : [];
    
    if ($token && $user_data) {
        seodevi_store_token($token, $user_data);
        wp_send_json_success(['message' => 'Authentication stored']);
    } else {
        wp_send_json_error(['message' => 'Invalid data received']);
    }
}

function seodevi_ajax_logout() {
    // Check nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'seodevi_admin_nonce')) {
        wp_send_json_error(['message' => 'Invalid security token']);
    }
    
    seodevi_logout();
    wp_send_json_success(['message' => 'Logged out successfully']);
}

function seodevi_ajax_get_profile() {
    check_ajax_referer('seodevi_admin_nonce', 'nonce');
    
    $token = seodevi_get_token();
    if (!$token) {
        wp_send_json_error(['message' => 'Not authenticated']);
    }
    
    $user_data = seodevi_get_user_data();
    wp_send_json_success(['user' => $user_data]);
}

function seodevi_ajax_check_auth() {
    check_ajax_referer('seodevi_admin_nonce', 'nonce');
    
    if (seodevi_is_logged_in()) {
        wp_send_json_success(['logged_in' => true]);
    } else {
        wp_send_json_success(['logged_in' => false]);
    }
}

// Also add for non-logged in users
add_action('wp_ajax_nopriv_seodevi_store_auth', 'seodevi_ajax_store_auth');