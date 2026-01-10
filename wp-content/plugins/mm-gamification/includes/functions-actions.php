<?php
if (!defined('ABSPATH')) exit;

// Central array to store available actions
$GLOBALS['mm_available_actions'] = [];

/**
 * Register a new gamification action globally
 */
if (!function_exists('mm_register_action')) {
    function mm_register_action($key, $label) {
        $GLOBALS['mm_available_actions'][$key] = $label;
    }
}

/**
 * Get all registered actions
 */
if (!function_exists('mm_get_available_actions')) {
    function mm_get_available_actions() {
        return $GLOBALS['mm_available_actions'];
    }
}

mm_register_action('user_register', 'User Registration');
mm_register_action('first_login', 'First Login');
mm_register_action('daily_login', 'Daily Login');

//Newly added actions
mm_register_action('first_concurrent_post', 'First concurrent post');
mm_register_action('first_event_post', 'First event post');
mm_register_action('profile_picture_update', 'Profile picture update');
mm_register_action('cover_photo_update', 'Cover photo update');
mm_register_action('next_concurrent_posts', 'Next concurrent posts');
mm_register_action('next_event_posts', 'Next event posts');
mm_register_action('birthday_update', 'Birthday update');
mm_register_action('location_update', 'Location update');

/**
 * Starts a session if one doesn't already exist.
 */
function mm_gamification_start_session() {
    if (!session_id()) {
        session_start();
    }
}
add_action('init', 'mm_gamification_start_session');


function gamification_award_points($user_id, $action_key) {
    global $wpdb;
    $table = $wpdb->prefix . 'gamification_actions';
    $action = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE action_key = %s", $action_key));

    if (!$action) return;

    $logs = get_user_meta($user_id, 'earned_points_logs', true);
    $logs = is_array($logs) ? $logs : [];

    $logs[] = [
        'action_key' => $action_key,
        'activity' => $action->custom_message,
        'points'   => (float)$action->points,
        'date'     => current_time('mysql'),
    ];

    update_user_meta($user_id, 'earned_points_logs', $logs);
    
    // Trigger a global action for other components (like notifications) to hook into.
    do_action('user_earned_points', $user_id, (float)$action->points, $action_key);
}

function gamification_notify_user($user_id, $action) {
    // Use the specific notification_message if it exists, otherwise fall back to the custom_message.
    $raw_message = !empty($action->notification_message) ? $action->notification_message : $action->custom_message;
    $points = (float)$action->points;

    // Replace {points} placeholder with the actual points value.
    $message = str_replace('{points}', $points, $raw_message);

    $notification_data = [
        'title'   => 'Points Earned!',
        'message' => $message,
        'points'  => (float)$action->points,
    ];

    // Use session instead of transient for reliability across redirects.
    $_SESSION["gamification_notify_{$user_id}"] = $notification_data;

    return $notification_data;
}

/**
 * Get all gamification actions from the database.
 *
 * @return array|object|null
 */
function mm_get_all_gamification_actions() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'gamification_actions';
    return $wpdb->get_results("SELECT * FROM {$table_name} WHERE `custom_message` != '' ORDER BY `points` DESC");
}

/**
 * A robust function to award points and trigger notifications.
 * This should be used as the primary way to give points.
 *
 * @param int    $user_id    The ID of the user.
 * @param string $action_key The key for the gamification action.
 * @return bool True on success, false on failure.
 */
function mm_award_points_and_notify($user_id, $action_key) {
    // The function mm_get_action_by_key is defined in functions-points.php
    if (!function_exists('mm_get_action_by_key')) {
        return false;
    }

    $action = mm_get_action_by_key($action_key);
    if (!$action) {
        return false;
    }

    // The core function to award points and log the activity.
    gamification_award_points($user_id, $action_key);

    // This function creates a short-lived transient and returns the notification data.
    $notification_data = gamification_notify_user($user_id, $action);

    return $notification_data; // Return the data for AJAX calls
}
