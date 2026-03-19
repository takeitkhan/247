<?php
/**
 * Facebook OAuth Authentication Handler
 * 
 * Handles Facebook login, token exchange, and storage
 */

// ============================================
// Facebook OAuth Configuration
// ============================================

// Load from settings or wp-config
if ( ! defined( 'FACEBOOK_APP_ID' ) ) {
    $app_id = get_option('mm_facebook_app_id', '');
    define('FACEBOOK_APP_ID', $app_id ?: 'NOT_SET');
}
if ( ! defined( 'FACEBOOK_APP_SECRET' ) ) {
    $app_secret = get_option('mm_facebook_app_secret', '');
    define('FACEBOOK_APP_SECRET', $app_secret ?: 'NOT_SET');
}
define('FACEBOOK_REDIRECT_URI', admin_url('admin.php?page=social-auth-callback&provider=facebook'));

// ============================================
// Step 1: Generate Login URL
// ============================================

function get_facebook_login_url() {
    $state = wp_generate_password(32, false);
    
    // Store state in session for verification
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['facebook_oauth_state'] = $state;
    
    $params = array(
        'client_id' => FACEBOOK_APP_ID,
        'redirect_uri' => FACEBOOK_REDIRECT_URI,
        'scope' => 'public_profile,email,pages_manage_posts,pages_read_engagement',
        'state' => $state,
        'response_type' => 'code'
    );
    
    return 'https://www.facebook.com/v18.0/dialog/oauth?' . http_build_query($params);
}

// ============================================
// Step 2: Handle OAuth Callback
// ============================================

function handle_facebook_oauth_callback() {
    if (!is_user_logged_in()) {
        wp_redirect(wp_login_url());
        exit;
    }
    
    // Verify state for security
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (empty($_GET['state']) || empty($_SESSION['facebook_oauth_state']) || 
        $_GET['state'] !== $_SESSION['facebook_oauth_state']) {
        wp_die('State mismatch. Security verification failed.');
    }
    
    // Check for errors from Facebook
    if (!empty($_GET['error'])) {
        $error = sanitize_text_field($_GET['error']);
        $error_description = isset($_GET['error_description']) ? sanitize_text_field($_GET['error_description']) : 'Unknown error';
        wp_die('Facebook Login Error: ' . esc_html($error) . ' - ' . esc_html($error_description));
    }
    
    // Get authorization code
    if (empty($_GET['code'])) {
        wp_die('No authorization code received from Facebook.');
    }
    
    $code = sanitize_text_field($_GET['code']);
    $user_id = get_current_user_id();
    
    // Exchange code for token
    $token_response = exchange_facebook_code_for_token($code);
    
    if (is_wp_error($token_response)) {
        wp_die('Error exchanging code for token: ' . esc_html($token_response->get_error_message()));
    }
    
    // Store token and user info
    $stored = store_facebook_credentials($user_id, $token_response);
    
    if ($stored) {
        wp_redirect(add_query_arg('success', 'facebook', admin_url('admin.php?page=social-connect')));
    } else {
        wp_die('Failed to store Facebook credentials.');
    }
    
    exit;
}

// ============================================
// Step 3: Exchange Authorization Code for Token
// ============================================

function exchange_facebook_code_for_token($code) {
    $response = wp_remote_post('https://graph.facebook.com/v18.0/oauth/access_token', array(
        'body' => array(
            'client_id' => FACEBOOK_APP_ID,
            'client_secret' => FACEBOOK_APP_SECRET,
            'redirect_uri' => FACEBOOK_REDIRECT_URI,
            'code' => $code
        ),
        'timeout' => 30
    ));
    
    if (is_wp_error($response)) {
        return $response;
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if (isset($body['error'])) {
        return new WP_Error('facebook_error', 'Facebook API Error: ' . $body['error']['message']);
    }
    
    if (empty($body['access_token'])) {
        return new WP_Error('no_token', 'No access token received from Facebook');
    }
    
    return $body;
}

// ============================================
// Step 4: Get User Info from Facebook
// ============================================

function get_facebook_user_info($access_token) {
    $response = wp_remote_get('https://graph.facebook.com/v18.0/me?fields=id,name,email&access_token=' . urlencode($access_token), array(
        'timeout' => 30
    ));
    
    if (is_wp_error($response)) {
        return $response;
    }
    
    $user_data = json_decode(wp_remote_retrieve_body($response), true);
    
    if (isset($user_data['error'])) {
        return new WP_Error('facebook_error', 'Failed to get Facebook user info');
    }
    
    return $user_data;
}

// ============================================
// Step 5: Store Facebook Credentials
// ============================================

function store_facebook_credentials($user_id, $token_response) {
    // Get user info
    $user_info = get_facebook_user_info($token_response['access_token']);
    
    if (is_wp_error($user_info)) {
        error_log('Facebook user info error: ' . $user_info->get_error_message());
        return false;
    }
    
    // Store encrypted token in user meta
    $encrypted_token = openssl_encrypt(
        $token_response['access_token'],
        'aes-256-cbc',
        wp_salt('auth'),
        false,
        substr(wp_salt('secure_auth'), 0, 16)
    );
    
    // Store user credentials
    update_user_meta($user_id, '_facebook_token', $encrypted_token);
    update_user_meta($user_id, '_facebook_user_id', sanitize_text_field($user_info['id']));
    update_user_meta($user_id, '_facebook_user_name', sanitize_text_field($user_info['name']));
    update_user_meta($user_id, '_facebook_token_expires', time() + ($token_response['expires_in'] ?? 5184000)); // Default 60 days
    
    error_log('Facebook account connected for user: ' . $user_id);
    
    return true;
}

// ============================================
// Get Decrypted Facebook Token
// ============================================

function get_facebook_token($user_id) {
    $encrypted_token = get_user_meta($user_id, '_facebook_token', true);
    
    if (empty($encrypted_token)) {
        return false;
    }
    
    $token = openssl_decrypt(
        $encrypted_token,
        'aes-256-cbc',
        wp_salt('auth'),
        false,
        substr(wp_salt('secure_auth'), 0, 16)
    );
    
    return $token ? $token : false;
}

// ============================================
// Disconnect Facebook Account
// ============================================

function disconnect_facebook_account($user_id) {
    delete_user_meta($user_id, '_facebook_token');
    delete_user_meta($user_id, '_facebook_user_id');
    delete_user_meta($user_id, '_facebook_user_name');
    delete_user_meta($user_id, '_facebook_token_expires');
    
    error_log('Facebook account disconnected for user: ' . $user_id);
    
    return true;
}

// ============================================
// Check if Facebook Connected
// ============================================

function is_facebook_connected($user_id) {
    return !empty(get_user_meta($user_id, '_facebook_user_id', true));
}
