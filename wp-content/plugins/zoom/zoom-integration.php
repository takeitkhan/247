<?php
/*
Plugin Name: Zoom Integration
Description: Let users connect their Zoom accounts and manage meetings.
Version: 1.1
Author: Samrat Khan
*/

if (!defined('ABSPATH')) exit;

// Credentials
function zoom_get_client_id()
{
    return get_option('zoom_client_id', '');
}
function zoom_get_client_secret()
{
    return get_option('zoom_client_secret', '');
}
function zoom_get_redirect_uri()
{
    return site_url('/zoom-callback/');
}

// Shortcode for connect button
add_shortcode('zoom_connect_button', 'zoom_connect_button_shortcode');
function zoom_connect_button_shortcode()
{
    if (!is_user_logged_in()) return 'Please log in first.';

    $user_id = get_current_user_id();
    $access_token = get_user_meta($user_id, 'zoom_access_token', true);

    if (!empty($access_token)) {
        return '<p>✅ Zoom connected successfully!</p>
        <p><a href="' . esc_url(site_url('/my-zoom-appointments/')) . '" class="button button-primary">View Your Zoom Appointments</a></p>';
    } else {
        $auth_url = 'https://zoom.us/oauth/authorize?response_type=code&client_id=' . zoom_get_client_id() .
            '&redirect_uri=' . urlencode(zoom_get_redirect_uri()) .
            '&state=' . urlencode(base64_encode('uid_' . $user_id));
        return '<a href="' . esc_url($auth_url) . '" class="button button-secondary">Connect Zoom Account</a>';
    }
}

// Hook into template_redirect instead of init
add_action('template_redirect', 'zoom_handle_oauth_callback');

function zoom_handle_oauth_callback()
{
    if (!is_page('zoom-callback')) return;
    if (!isset($_GET['code']) || !isset($_GET['state'])) return;

    $code = sanitize_text_field($_GET['code']);

    $response = wp_remote_post('https://zoom.us/oauth/token', array(
        'body' => array(
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => zoom_get_redirect_uri(),
        ),
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode(zoom_get_client_id() . ':' . zoom_get_client_secret()),
        )
    ));

    if (is_wp_error($response)) {
        wp_die('Error connecting to Zoom');
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (!empty($body['access_token'])) {
        $user_id = get_current_user_id();
        update_user_meta($user_id, 'zoom_access_token', sanitize_text_field($body['access_token']));
        update_user_meta($user_id, 'zoom_refresh_token', sanitize_text_field($body['refresh_token']));

        // Redirect to same page with ?zoom_connected=1 to show message
        wp_redirect(add_query_arg('zoom_connected', '1', site_url('/zoom-callback/')));
        exit;
    }
}


// Show upcoming Zoom meetings
add_shortcode('zoom_appointments', 'zoom_appointments_shortcode');
function zoom_appointments_shortcode()
{
    if (!is_user_logged_in()) return 'Please log in.';

    $user_id = get_current_user_id();
    $access_token = get_user_meta($user_id, 'zoom_access_token', true);

    if (!$access_token) {
        return '<p>You need to connect your Zoom account first. <a href="' . esc_url(site_url('/connect-zoom/')) . '">Connect Now</a></p>';
    }

    return zoom_list_meetings($access_token);
}
function zoom_list_meetings($access_token)
{
    $response = wp_remote_get('https://api.zoom.us/v2/users/me/meetings', [
        'headers' => [
            'Authorization' => 'Bearer ' . $access_token,
        ],
    ]);

    if (is_wp_error($response)) {
        return '<p>Error fetching meetings: ' . esc_html($response->get_error_message()) . '</p>';
    }

    $code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);

    // Handle expired token
    if ($code === 401) {
        $user_id = get_current_user_id();
        $refresh_token = get_user_meta($user_id, 'zoom_refresh_token', true);
        $new_token = zoom_refresh_access_token($refresh_token);

        if (is_wp_error($new_token)) {
            return '<p>Zoom token expired and failed to refresh. Please reconnect your Zoom account.</p>';
        }

        // Retry the request with new access token
        $response = wp_remote_get('https://api.zoom.us/v2/users/me/meetings', [
            'headers' => [
                'Authorization' => 'Bearer ' . $new_token,
            ],
        ]);

        if (is_wp_error($response)) {
            return '<p>Retry failed: ' . esc_html($response->get_error_message()) . '</p>';
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($code !== 200) {
            return '<p>Zoom API still failing: ' . esc_html($code) . '</p><pre>' . esc_html($body) . '</pre>';
        }
    }

    $data = json_decode($body, true);

    if (empty($data['meetings'])) return '<p>No upcoming meetings</p>';

    $output = '<ul>';
    foreach ($data['meetings'] as $meeting) {
        $output .= '<li>' . esc_html($meeting['topic']) . ' at ' . esc_html($meeting['start_time']) . '</li>';
    }
    $output .= '</ul>';

    return $output;
}


function zoom_refresh_access_token($refresh_token)
{
    $client_id     = zoom_get_client_id();
    $client_secret = zoom_get_client_secret();
    $redirect_uri  = zoom_get_redirect_uri();

    $response = wp_remote_post('https://zoom.us/oauth/token', [
        'headers' => [
            'Authorization' => 'Basic ' . base64_encode($client_id . ':' . $client_secret),
        ],
        'body' => [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refresh_token,
        ],
    ]);

    if (is_wp_error($response)) {
        return new WP_Error('zoom_token_refresh_failed', $response->get_error_message());
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (!isset($body['access_token']) || !isset($body['refresh_token'])) {
        return new WP_Error('zoom_token_invalid_response', 'Zoom did not return a valid access/refresh token.');
    }

    // Save the new tokens to the current user
    $user_id = get_current_user_id();
    update_user_meta($user_id, 'zoom_access_token', $body['access_token']);
    update_user_meta($user_id, 'zoom_refresh_token', $body['refresh_token']);

    return $body['access_token'];
}




// Admin settings
add_action('admin_menu', function () {
    add_options_page('Zoom Integration Settings', 'Zoom Integration', 'manage_options', 'zoom-integration-settings', 'zoom_integration_settings_page');
});
add_action('admin_init', function () {
    register_setting('zoom_integration_options', 'zoom_client_id');
    register_setting('zoom_integration_options', 'zoom_client_secret');

    add_settings_section('zoom_integration_main', 'Zoom API Credentials', null, 'zoom-integration-settings');

    add_settings_field('zoom_client_id', 'Zoom Client ID', function () {
        $val = get_option('zoom_client_id', '');
        echo '<input type="text" name="zoom_client_id" value="' . esc_attr($val) . '" size="50" />';
    }, 'zoom-integration-settings', 'zoom_integration_main');

    add_settings_field('zoom_client_secret', 'Zoom Client Secret', function () {
        $val = get_option('zoom_client_secret', '');
        echo '<input type="text" name="zoom_client_secret" value="' . esc_attr($val) . '" size="50" />';
    }, 'zoom-integration-settings', 'zoom_integration_main');
});
function zoom_integration_settings_page()
{
    if (!current_user_can('manage_options')) return;
?>
    <div class="wrap">
        <h1>Zoom Integration Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('zoom_integration_options');
            do_settings_sections('zoom-integration-settings');
            submit_button();
            ?>
        </form>
    </div>
<?php
}


add_shortcode('zoom_callback_thankyou', function () {
    if (isset($_GET['zoom_connected']) && $_GET['zoom_connected'] === '1') {
        return '<h2>🎉 Thank you! You’re one step ahead in managing your schedule with Zoom.</h2>
                <p><a href="' . esc_url(site_url('/my-zoom-appointments/')) . '" class="button button-primary">View Your Zoom Appointments</a></p>';
    }

    return '<p>⏳ Connecting your Zoom account... Please wait.</p>';
});


add_shortcode('zoom_book_appointment', function () {
    if (!is_user_logged_in()) {
        return 'Please log in to book appointments.';
    }

    $user_id = get_current_user_id();
    $access_token = get_user_meta($user_id, 'zoom_access_token', true);
    if (!$access_token) {
        return 'Please connect your Zoom account first.';
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['zoom_meeting_topic'])) {
        $topic = sanitize_text_field($_POST['zoom_meeting_topic']);
        $start_time = sanitize_text_field($_POST['zoom_meeting_start']); // expect ISO 8601 date/time

        // Call helper to create Zoom meeting
        $result = zoom_create_meeting($access_token, $topic, $start_time);

        if (is_wp_error($result)) {
            return '<p style="color:red;">Error creating meeting: ' . esc_html($result->get_error_message()) . '</p>';
        } else {
            return '<p style="color:green;">Meeting created successfully! Join URL: <a href="' . esc_url($result['join_url']) . '" target="_blank">' . esc_html($result['join_url']) . '</a></p>';
        }
    }

    // Display booking form
    ob_start();
?>
    <form method="post">
        <label>Meeting Topic:<br>
            <input type="text" name="zoom_meeting_topic" required>
        </label><br><br>
        <label>Start Time (YYYY-MM-DDTHH:MM:SS):<br>
            <input type="datetime-local" name="zoom_meeting_start" required>
        </label><br><br>
        <input type="submit" value="Book Appointment">
    </form>
<?php
    return ob_get_clean();
});


function zoom_create_meeting($access_token, $topic, $start_time)
{
    $url = 'https://api.zoom.us/v2/users/me/meetings';

    $body = [
        'topic' => $topic,
        'type' => 2, // Scheduled meeting
        'start_time' => $start_time, // in ISO 8601 (e.g., 2025-07-19T14:30:00)
        'timezone' => get_option('timezone_string') ?: 'UTC',
        'settings' => [
            'join_before_host' => false,
            'waiting_room' => true,
        ],
    ];

    $response = wp_remote_post($url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type'  => 'application/json',
        ],
        'body' => json_encode($body),
    ]);

    if (is_wp_error($response)) {
        return $response;
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($data['id'])) {
        // Return the meeting join URL and details
        return $data;
    }

    return new WP_Error('zoom_error', $data['message'] ?? 'Unknown error');
}

add_shortcode('zoom_user_meetings', 'zoom_user_meetings_shortcode');
function zoom_user_meetings_shortcode($atts)
{
    $atts = shortcode_atts(['user_id' => 0], $atts);
    $user_id = intval($atts['user_id']);  // ensures integer


    if (!$user_id) {
        return '<p>Invalid user ID.</p>';
    }

    $access_token = get_user_meta($user_id, 'zoom_access_token', true);
    $refresh_token = get_user_meta($user_id, 'zoom_refresh_token', true);


    if (!$access_token) return '<p>This user is not connected to Zoom.</p>';

    // Function to get meetings from Zoom API
    $get_meetings = function ($token) {
        return wp_remote_get("https://api.zoom.us/v2/users/me/meetings?type=upcoming", [
            'headers' => ['Authorization' => "Bearer $token"]
        ]);
    };

    // Try initial call
    $response = $get_meetings($access_token);


    // If expired token (401), try refreshing token
    $code = wp_remote_retrieve_response_code($response);

    
    
    if ($code == 401 && $refresh_token) {
        // Refresh token
        $new_access_token = zoom_refresh_access_token_for_user($user_id, $refresh_token);
        if (is_wp_error($new_access_token)) {
            return '<p>Error refreshing Zoom access token: ' . esc_html($new_access_token->get_error_message()) . '</p>';
        }

        // Retry with new access token
        $response = $get_meetings($new_access_token);
        $code = wp_remote_retrieve_response_code($response);
    }

    if (is_wp_error($response)) {
        return '<p>Error connecting to Zoom: ' . esc_html($response->get_error_message()) . '</p>';
    }

    if ($code != 200) {
        $body = wp_remote_retrieve_body($response);
        return '<p>Zoom API returned status ' . esc_html($code) . '</p><pre>' . esc_html($body) . '</pre>';
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    echo '<pre>';
    print_r($body);
    echo '</pre>';
    if (empty($body['meetings'])) return '<p>No upcoming meetings.</p>';

    ob_start();
    echo '<ul class="zoom-meetings">';
    foreach ($body['meetings'] as $meeting) {
        if ($meeting['settings']['approval_type'] == 0) { // Auto-approval (registration enabled)
            echo '<li>';
            echo '<strong>' . esc_html($meeting['topic']) . '</strong><br>';
            echo 'Start: ' . esc_html($meeting['start_time']) . '<br>';
            echo '<form method="post">';
            echo '<input type="hidden" name="zoom_meeting_id" value="' . esc_attr($meeting['id']) . '">';
            echo '<input type="text" name="zoom_name" placeholder="Your name" required>';
            echo '<input type="email" name="zoom_email" placeholder="Your email" required>';
            echo '<button type="submit" name="zoom_register">Book Now</button>';
            echo '</form>';
            echo '</li>';
        }
    }
    echo '</ul>';

    return ob_get_clean();
}

// Refresh access token helper that accepts user ID (adjusted from your existing zoom_refresh_access_token)
function zoom_refresh_access_token_for_user($user_id, $refresh_token)
{
    $client_id     = zoom_get_client_id();
    $client_secret = zoom_get_client_secret();

    $response = wp_remote_post('https://zoom.us/oauth/token', [
        'headers' => [
            'Authorization' => 'Basic ' . base64_encode($client_id . ':' . $client_secret),
        ],
        'body' => [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refresh_token,
        ],
    ]);

    if (is_wp_error($response)) {
        return new WP_Error('zoom_token_refresh_failed', $response->get_error_message());
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (!isset($body['access_token']) || !isset($body['refresh_token'])) {
        return new WP_Error('zoom_token_invalid_response', 'Zoom did not return a valid access/refresh token.');
    }

    // Save new tokens for user
    update_user_meta($user_id, 'zoom_access_token', sanitize_text_field($body['access_token']));
    update_user_meta($user_id, 'zoom_refresh_token', sanitize_text_field($body['refresh_token']));

    return $body['access_token'];
}



add_action('init', 'handle_zoom_registration_form');
function handle_zoom_registration_form()
{
    if (!isset($_POST['zoom_register'])) return;

    $meeting_id = sanitize_text_field($_POST['zoom_meeting_id']);
    $name = sanitize_text_field($_POST['zoom_name']);
    $email = sanitize_email($_POST['zoom_email']);
    $user_id = get_current_user_id();

    $access_token = get_user_meta($user_id, 'zoom_access_token', true);
    if (!$access_token) return;

    $response = wp_remote_post("https://api.zoom.us/v2/meetings/$meeting_id/registrants", [
        'headers' => [
            'Authorization' => "Bearer $access_token",
            'Content-Type'  => 'application/json'
        ],
        'body' => json_encode([
            'email' => $email,
            'first_name' => $name,
        ])
    ]);

    if (is_wp_error($response)) {
        error_log('Zoom registration error: ' . $response->get_error_message());
        return;
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (!empty($body['join_url'])) {
        wp_mail($email, 'Zoom Meeting Registration', "You are registered. Join here: " . $body['join_url']);
    }
}
