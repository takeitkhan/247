<?php
if (!defined('ABSPATH')) exit;

// Central array to store all available actions
$GLOBALS['mm_available_actions'] = [];

/**
 * Register a new user action for gamification
 */
if (!function_exists('mm_register_action')) {
    function mm_register_action($key, $label)
    {
        $GLOBALS['mm_available_actions'][$key] = $label;
    }
}

/**
 * Get all registered actions
 */
if (!function_exists('mm_get_available_actions')) {
    function mm_get_available_actions()
    {
        return $GLOBALS['mm_available_actions'];
    }
}

add_action('profile_update', 'gamification_profile_photo_points', 10, 2);

function gamification_profile_photo_points($user_id, $old_user_data) {
    // Get previous and current avatar URL or ID
    $old_avatar = get_user_meta($user_id, 'profile_photo', true); // adjust meta key if needed
    $new_avatar = get_user_meta($user_id, 'profile_photo', true);

    // Only award points if the photo actually changed
    if ($new_avatar && $new_avatar !== $old_avatar) {
        gamification_award_points($user_id, 'profile_photo_upload');
    }
}


function gamification_award_points($user_id, $action_key) {
    global $wpdb;
    $table = $wpdb->prefix . 'gamification_actions';
    $action = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE action_key = %s", $action_key));

    if (!$action) return;

    $logs = get_user_meta($user_id, 'earned_points_logs', true);
    $logs = is_array($logs) ? $logs : [];

    $logs[] = [
        'activity' => $action->message,
        'points'   => (float)$action->points,
        'date'     => current_time('mysql'),
    ];

    update_user_meta($user_id, 'earned_points_logs', $logs);

    gamification_notify_user($user_id, $action);
}

function gamification_notify_user($user_id, $action) {
    $message = "✅ {$action->points} points awarded for: {$action->message}";
    set_transient("gamification_notify_{$user_id}", $message, 30);
}
