<?php
/**
 * LinkedIn OAuth Authentication Handler
 * 
 * Handles LinkedIn login, token exchange, and storage
 */

// ============================================
// LinkedIn OAuth Configuration
// ============================================

// Load from settings or wp-config
if ( ! defined( 'LINKEDIN_APP_ID' ) ) {
    $app_id = get_option('mm_linkedin_app_id', '');
    define('LINKEDIN_APP_ID', $app_id ?: 'NOT_SET');
}
if ( ! defined( 'LINKEDIN_APP_SECRET' ) ) {
    $app_secret = get_option('mm_linkedin_app_secret', '');
    define('LINKEDIN_APP_SECRET', $app_secret ?: 'NOT_SET');
}
define('LINKEDIN_REDIRECT_URI', home_url('/linkedin-oauth-callback/'));

// ============================================
// Step 1: Generate Login URL
// ============================================

function get_linkedin_login_url() {
    // Ensure user is logged in
    if (!is_user_logged_in()) {
        wp_die('You must be logged in to connect social media.');
    }
    
    $state = wp_generate_password(32, false);
    $user_id = get_current_user_id();
    
    // Store state in BOTH transient and option with timestamp
    // This ensures it survives across different PHP requests/sessions
    $transient_key = 'linkedin_oauth_state_' . $user_id;
    
    // Primary: WordPress transient
    set_transient($transient_key, $state, 600); // 10 minute expiration
    
    // Backup: User option (for redundancy)
    update_user_meta($user_id, '_linkedin_oauth_state_backup', array(
        'state' => $state,
        'timestamp' => time()
    ));
    
    error_log('LinkedIn OAuth - Generated state for user ' . $user_id . ': ' . $state);
    error_log('  Transient key: ' . $transient_key);
    error_log('  User ID verified: ' . is_user_logged_in());
    
    $params = array(
        'response_type' => 'code',
        'client_id' => LINKEDIN_APP_ID,
        'redirect_uri' => LINKEDIN_REDIRECT_URI,
        'state' => $state,
        'scope' => 'profile,email,w_member_social'
    );
    
    return 'https://www.linkedin.com/oauth/v2/authorization?' . http_build_query($params);
}

// ============================================
// Step 2: Handle OAuth Callback
// ============================================

function handle_linkedin_oauth_callback() {
    if (!is_user_logged_in()) {
        error_log('LinkedIn OAuth Callback - User NOT logged in!');
        wp_redirect(wp_login_url());
        exit;
    }
    
    $user_id = get_current_user_id();
    
    // Try to get state from transient first
    $transient_key = 'linkedin_oauth_state_' . $user_id;
    $stored_state = get_transient($transient_key);
    
    // Fallback to user meta if transient expired
    if (empty($stored_state)) {
        $backup_data = get_user_meta($user_id, '_linkedin_oauth_state_backup', true);
        if ($backup_data && is_array($backup_data)) {
            $stored_state = $backup_data['state'];
            $state_time = $backup_data['timestamp'];
            
            // Check if backup is still valid (within 10 minutes)
            if ((time() - $state_time) > 600) {
                $stored_state = null; // Expired
            }
        }
    }
    
    error_log('LinkedIn OAuth Callback - User: ' . $user_id);
    error_log('  Received state: ' . ($_GET['state'] ?? 'empty'));
    error_log('  Stored state (transient): ' . ($stored_state ?? 'empty'));
    error_log('  Transient key used: ' . $transient_key);
    
    // Check state exists and matches
    if (empty($_GET['state']) || empty($stored_state) || 
        $_GET['state'] !== $stored_state) {
        error_log('LinkedIn OAuth State Mismatch! GET=' . ($_GET['state'] ?? 'empty') . ', Stored=' . ($stored_state ?? 'empty'));
        wp_die('State mismatch. Security verification failed. Please try connecting again.');
    }
    
    // Check for errors from LinkedIn
    if (!empty($_GET['error'])) {
        $error = sanitize_text_field($_GET['error']);
        $error_description = isset($_GET['error_description']) ? sanitize_text_field($_GET['error_description']) : 'Unknown error';
        wp_die('LinkedIn Login Error: ' . esc_html($error) . ' - ' . esc_html($error_description));
    }
    
    // Get authorization code
    if (empty($_GET['code'])) {
        wp_die('No authorization code received from LinkedIn.');
    }
    
    $code = sanitize_text_field($_GET['code']);
    $user_id = get_current_user_id();
    
    // Exchange code for token
    $token_response = exchange_linkedin_code_for_token($code);
    
    if (is_wp_error($token_response)) {
        wp_die('Error exchanging code for token: ' . esc_html($token_response->get_error_message()));
    }
    
    // Store token and user info
    $stored = store_linkedin_credentials($user_id, $token_response);
    
    // Clean up state from both transient and backup meta
    delete_transient('linkedin_oauth_state_' . $user_id);
    delete_user_meta($user_id, '_linkedin_oauth_state_backup');
    
    if ($stored) {
        error_log('LinkedIn OAuth Success - User: ' . $user_id . ' connected successfully');
        wp_redirect(add_query_arg('success', 'linkedin', admin_url('admin.php?page=social-connect')));
    } else {
        error_log('LinkedIn OAuth Failed - Could not store credentials for user ' . $user_id);
        wp_die('Failed to store LinkedIn credentials.');
    }
    
    exit;
}

// ============================================
// Step 3: Exchange Authorization Code for Token
// ============================================

function exchange_linkedin_code_for_token($code) {
    $response = wp_remote_post('https://www.linkedin.com/oauth/v2/accessToken', array(
        'body' => array(
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => LINKEDIN_APP_ID,
            'client_secret' => LINKEDIN_APP_SECRET,
            'redirect_uri' => LINKEDIN_REDIRECT_URI
        ),
        'timeout' => 30
    ));
    
    if (is_wp_error($response)) {
        return $response;
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if (isset($body['error'])) {
        return new WP_Error('linkedin_error', 'LinkedIn API Error: ' . $body['error_description']);
    }
    
    if (empty($body['access_token'])) {
        return new WP_Error('no_token', 'No access token received from LinkedIn');
    }
    
    return $body;
}

// ============================================
// Step 4: Get User Info from LinkedIn
// ============================================

function get_linkedin_user_info($access_token) {
    $response = wp_remote_get('https://api.linkedin.com/v2/me', array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type' => 'application/json'
        ),
        'timeout' => 30
    ));
    
    if (is_wp_error($response)) {
        return $response;
    }
    
    $user_data = json_decode(wp_remote_retrieve_body($response), true);
    
    if (isset($user_data['serviceErrorCode'])) {
        return new WP_Error('linkedin_error', 'Failed to get LinkedIn user info');
    }
    
    return $user_data;
}

// ============================================
// Step 5: Store LinkedIn Credentials
// ============================================

function store_linkedin_credentials($user_id, $token_response) {
    // Get user info
    $user_info = get_linkedin_user_info($token_response['access_token']);
    
    if (is_wp_error($user_info)) {
        error_log('LinkedIn user info error: ' . $user_info->get_error_message());
        return false;
    }
    
    // Extract user ID and name
    $linkedin_user_id = isset($user_info['id']) ? $user_info['id'] : 'unknown';
    $linkedin_name = '';
    
    if (isset($user_info['localizedFirstName'])) {
        $linkedin_name = $user_info['localizedFirstName'];
        if (isset($user_info['localizedLastName'])) {
            $linkedin_name .= ' ' . $user_info['localizedLastName'];
        }
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
    update_user_meta($user_id, '_linkedin_token', $encrypted_token);
    update_user_meta($user_id, '_linkedin_user_id', sanitize_text_field($linkedin_user_id));
    update_user_meta($user_id, '_linkedin_user_name', sanitize_text_field($linkedin_name));
    update_user_meta($user_id, '_linkedin_token_expires', time() + ($token_response['expires_in'] ?? 5184000)); // Default 60 days
    
    error_log('LinkedIn account connected for user: ' . $user_id);
    
    return true;
}

// ============================================
// Get Decrypted LinkedIn Token
// ============================================

function get_linkedin_token($user_id) {
    $encrypted_token = get_user_meta($user_id, '_linkedin_token', true);
    
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
// Disconnect LinkedIn Account
// ============================================

function disconnect_linkedin_account($user_id) {
    delete_user_meta($user_id, '_linkedin_token');
    delete_user_meta($user_id, '_linkedin_user_id');
    delete_user_meta($user_id, '_linkedin_user_name');
    delete_user_meta($user_id, '_linkedin_token_expires');
    
    error_log('LinkedIn account disconnected for user: ' . $user_id);
    
    return true;
}

// ============================================
// Check if LinkedIn Connected
// ============================================

function is_linkedin_connected($user_id) {
    return !empty(get_user_meta($user_id, '_linkedin_user_id', true));
}
