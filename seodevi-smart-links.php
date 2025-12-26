<?php
/**
 * Plugin Name:       SEODevi Smart Links
 * Plugin URI:        https://seodevi.com
 * Description:       Internal & External Link Suggestions for SEO with credit-based system.
 * Version:           1.0.0
 * Author:            SEODevi
 * Author URI:        https://seodevi.com
 * License:           GPL-2.0+
 * Text Domain:       dashboard
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/* ===============================
   CONSTANTS & DEFINITIONS
================================ */
define('SEODEVI_VERSION', '1.0.0');
define('SEODEVI_PATH', plugin_dir_path(__FILE__));
define('SEODEVI_URL', plugin_dir_url(__FILE__));

// IMPORTANT: Change these to your production API URLs
define('SEODEVI_API_BASE', 'http://localhost:5000/api/auth/'); // Auth endpoints
define('SEODEVI_LINK_API_BASE', 'http://localhost:5000/api/');

function seodevi_get_api_url($endpoint = '') {
    return rtrim(SEODEVI_API_BASE, '/') . '/' . ltrim($endpoint, '/');
}

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
   SESSION & AUTH HELPERS
================================ */
function seodevi_start_session() {
    if (!session_id() && !headers_sent()) {
        session_start();
    }
}
add_action('init', 'seodevi_start_session', 1);

function seodevi_store_token($token, $user_data) {
    seodevi_start_session();

    $_SESSION['seodevi_token']        = $token;
    $_SESSION['seodevi_user']         = $user_data;
    $_SESSION['seodevi_token_expiry'] = time() + (7 * 24 * 60 * 60);

    update_option('seodevi_token', $token);
    update_option('seodevi_user_data', $user_data);
    update_option('seodevi_last_login', time());
}

function seodevi_get_token() {
    seodevi_start_session();

    if (
        isset($_SESSION['seodevi_token']) &&
        isset($_SESSION['seodevi_token_expiry']) &&
        $_SESSION['seodevi_token_expiry'] > time()
    ) {
        return $_SESSION['seodevi_token'];
    }

    if (isset($_SESSION['seodevi_token_expiry']) && $_SESSION['seodevi_token_expiry'] <= time()) {
        unset($_SESSION['seodevi_token']);
        unset($_SESSION['seodevi_user']);
        unset($_SESSION['seodevi_token_expiry']);
    }

    $token = get_option('seodevi_token', '');
    $last_login = (int) get_option('seodevi_last_login', 0);

    if ($token && $last_login && (time() - $last_login) < (7 * 24 * 60 * 60)) {
        seodevi_start_session();
        $_SESSION['seodevi_token']        = $token;
        $_SESSION['seodevi_user']         = get_option('seodevi_user_data', []);
        $_SESSION['seodevi_token_expiry'] = $last_login + (7 * 24 * 60 * 60);
        return $token;
    }

    return '';
}

function seodevi_is_logged_in() {
    return !empty(seodevi_get_token());
}

function seodevi_get_user_data() {
    seodevi_start_session();
    return isset($_SESSION['seodevi_user'])
        ? $_SESSION['seodevi_user']
        : get_option('seodevi_user_data', []);
}

function seodevi_logout() {
    seodevi_start_session();

    unset($_SESSION['seodevi_token']);
    unset($_SESSION['seodevi_user']);
    unset($_SESSION['seodevi_token_expiry']);

    delete_option('seodevi_token');
    delete_option('seodevi_user_data');
    delete_option('seodevi_last_login');
}

/* ===============================
   EARLY REDIRECT FOR AUTH PAGES
================================ */
add_action('admin_init', function () {
    if (!isset($_GET['page'])) return;

    $page = sanitize_text_field($_GET['page']);
    $auth_pages = ['seodevi-login', 'seodevi-signup'];

    if (seodevi_is_logged_in() && in_array($page, $auth_pages)) {
        wp_safe_redirect(admin_url('admin.php?page=dashboard'));
        exit;
    }
});

/* ===============================
   ADMIN MENU
================================ */
add_action('admin_menu', function () {
    add_menu_page(
        'SEODevi Smart Links',
        'SEODevi Links',
        'manage_options',
        'dashboard',
        'seodevi_render_dashboard',
        'dashicons-admin-links',
        30
    );

    add_submenu_page(null, 'SEODevi Login', 'Login', 'manage_options', 'seodevi-login', 'seodevi_render_login');
    add_submenu_page(null, 'SEODevi Signup', 'Signup', 'manage_options', 'seodevi-signup', 'seodevi_render_signup');
    add_submenu_page(null, 'SEODevi Pricing', 'Pricing', 'manage_options', 'seodevi-pricing', 'seodevi_render_pricing');
    add_submenu_page(null, 'SEODevi About', 'About', 'manage_options', 'seodevi-about', 'seodevi_render_about');
});

function seodevi_render_page($template) {
    $path = SEODEVI_PATH . 'admin/' . $template . '.php';
    if (file_exists($path)) {
        include $path;
    } else {
        echo '<div class="notice notice-error"><p>Template not found: ' . esc_html($template) . '</p></div>';
    }
}

function seodevi_render_dashboard() { seodevi_render_page('Dashboard'); }
function seodevi_render_login() { seodevi_render_page('Login'); }
function seodevi_render_signup() { seodevi_render_page('Signup'); }
function seodevi_render_pricing() { seodevi_render_page('Pricing'); }
function seodevi_render_about() { seodevi_render_page('About'); }

/* ===============================
   ASSETS
================================ */
add_action('admin_enqueue_scripts', 'seodevi_enqueue_admin_assets');

function seodevi_enqueue_admin_assets($hook) {
    $seodevi_pages = [
        'toplevel_page_dashboard',
        'admin_page_seodevi-login',
        'admin_page_seodevi-signup',
        'admin_page_seodevi-pricing',
        'admin_page_seodevi-about',
    ];

    if (!in_array($hook, $seodevi_pages, true)) return;

    wp_enqueue_style('seodevi-admin-css', SEODEVI_URL . 'admin.css', [], SEODEVI_VERSION);
    wp_enqueue_style('seodevi-header-css', SEODEVI_URL . 'assets/css/admin-header.css', [], SEODEVI_VERSION);

    wp_enqueue_script('seodevi-admin-js', SEODEVI_URL . 'assets/js/admin.js', ['jquery'], SEODEVI_VERSION, true);

    wp_localize_script('seodevi-admin-js', 'seodeviConfig', [
        'apiBase'     => SEODEVI_API_BASE,
        'loginUrl'    => seodevi_get_login_url(),
        'registerUrl' => seodevi_get_register_url(),
        'profileUrl'  => seodevi_get_profile_url(),
        'dashboardUrl'=> admin_url('admin.php?page=dashboard'),
        'ajaxUrl'     => admin_url('admin-ajax.php'),
        'nonce'       => wp_create_nonce('seodevi_admin_nonce'),
        'logoutUrl'   => admin_url('admin.php?page=seodevi-login'),
    ]);
}

/* ===============================
   AJAX HANDLERS
================================ */
add_action('wp_ajax_seodevi_store_auth', 'seodevi_ajax_store_auth');
add_action('wp_ajax_seodevi_logout', 'seodevi_ajax_logout');
add_action('wp_ajax_seodevi_check_auth', 'seodevi_ajax_check_auth');
add_action('wp_ajax_seodevi_get_profile', 'seodevi_ajax_get_profile');
add_action('wp_ajax_nopriv_seodevi_store_auth', 'seodevi_ajax_store_auth');

function seodevi_ajax_store_auth() {
    check_ajax_referer('seodevi_admin_nonce', 'nonce');
    $token = sanitize_text_field($_POST['token'] ?? '');
    $user_data = json_decode(stripslashes($_POST['user_data'] ?? '[]'), true);

    if ($token && is_array($user_data)) {
        seodevi_store_token($token, $user_data);
        wp_send_json_success(['message' => 'Authentication stored']);
    }
    wp_send_json_error(['message' => 'Invalid data']);
}

function seodevi_ajax_logout() {
    check_ajax_referer('seodevi_admin_nonce', 'nonce');
    seodevi_logout();
    wp_send_json_success(['message' => 'Logged out successfully']);
}

function seodevi_ajax_check_auth() {
    check_ajax_referer('seodevi_admin_nonce', 'nonce');
    wp_send_json_success(['logged_in' => seodevi_is_logged_in()]);
}

function seodevi_ajax_get_profile() {
    check_ajax_referer('seodevi_admin_nonce', 'nonce');
    $token = seodevi_get_token();
    if (!$token) {
        wp_send_json_error(['message' => 'Not authenticated']);
    }
    wp_send_json_success(['user' => seodevi_get_user_data()]);
}

/* ===============================
   REST API PROXY FOR DASHBOARD
================================ */
add_action('rest_api_init', 'seodevi_register_proxy_route');

function seodevi_register_proxy_route() {
    register_rest_route('seodevi/v1', '/proxy/(?P<path>[\w/-]+)', [
        'methods' => ['GET', 'POST'],
        'callback' => 'seodevi_api_proxy_callback',
        'permission_callback' => fn() => current_user_can('manage_options'),
    ]);
}

function seodevi_api_proxy_callback(WP_REST_Request $request) {
    $path = $request->get_param('path');
    $url = rtrim(SEODEVI_LINK_API_BASE, '/') . '/' . ltrim($path, '/');

    $method = $request->get_method();
    $headers = ['Content-Type' => 'application/json'];

    $token = seodevi_get_token();
    if ($token) $headers['Authorization'] = 'Bearer ' . $token;

    $args = [
        'headers' => $headers,
        'timeout' => 30,
    ];

    if ($method === 'POST') {
        $args['body'] = $request->get_body();
        $response = wp_remote_post($url, $args);
    } else {
        $response = wp_remote_get($url, $args);
    }

    if (is_wp_error($response)) {
        return new WP_Error('api_error', $response->get_error_message(), ['status' => 500]);
    }

    $response_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    return is_array($data)
        ? new WP_REST_Response($data, $response_code)
        : new WP_REST_Response($body, $response_code);
}