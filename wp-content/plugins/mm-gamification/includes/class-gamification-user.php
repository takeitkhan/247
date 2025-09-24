<?php
if (!defined('ABSPATH')) exit;

class MM_Gamification_User
{
    private static $instance = null;

    public static function init()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        // WordPress hooks
        add_action('user_register', [$this, 'handle_user_register']);
        add_action('wp_login', [$this, 'handle_first_login'], 10, 2);

        // AJAX hooks for uploads
        add_action('wp_ajax_upload_profile_photo', [$this, 'handle_upload_photo']);
        add_action('wp_ajax_upload_cover_photo', [$this, 'handle_upload_photo']);
    }

    public function handle_user_register($user_id)
    {
        // Trigger the action
        if (function_exists('mm_trigger_action')) {
            mm_trigger_action('user_register', $user_id);
        }

        // Fetch action from dashboard
        $action = mm_get_action_by_key('user_register'); // helper function to get action
        if ($action) {
            $points = intval($action->points);
            $message = $action->notification_message ?: "You earned $points points for registering!";

            // Award points
            if (function_exists('mm_gamification_award_points')) {
                mm_gamification_award_points($user_id, 'user_register', $points);
            }

            // Add notification
            $notifications = Notifications::getInstance();
            $notifications->addNotification(
                $user_id,
                'success',
                $message
            );
        }
    }


    public function handle_first_login($user_login, $user)
    {
        $first_login_done = get_user_meta($user->ID, 'first_login_done', true);
        if (!$first_login_done) {

            // Trigger action
            if (function_exists('mm_trigger_action')) {
                mm_trigger_action('first_login', $user->ID);
            }

            // Fetch action from dashboard
            $action = mm_get_action_by_key('first_login'); // helper function to get action
            if ($action) {
                $points = intval($action->points);
                $message = $action->notification_message ?: "You earned $points points for logging in for the first time!";

                // Award points
                if (function_exists('mm_gamification_award_points')) {
                    mm_gamification_award_points($user->ID, 'first_login', $points);
                }

                // Add notification
                $notifications = Notifications::getInstance();
                $notifications->addNotification(
                    $user->ID,
                    'success',
                    $message
                );
            }

            // Mark first login done
            update_user_meta($user->ID, 'first_login_done', 1);
        }
    }





    public function handle_daily_login($user_login, $user)
    {
        $last_daily_login = get_user_meta($user->ID, 'last_daily_login', true);
        $today = date('Y-m-d');

        if ($last_daily_login !== $today) {
            // Fetch the action from DB
            $action = mm_get_action_by_key('daily_login');
            if ($action) {
                $points = intval($action->points);
                $message = $action->notification_message ?: "You earned $points points for logging in today!";

                // Award points
                mm_gamification_award_points($user->ID, 'daily_login', $points);

                // Save today's date
                update_user_meta($user->ID, 'last_daily_login', $today);

                // Add notification
                if (function_exists('mm_add_notification')) {
                    $notifications = Notifications::getInstance();
                    $notifications->addNotification(
                        $user->ID,
                        'success',
                        $message
                    );
                }
            }
        }
    }



    // Handle profile or cover photo upload
    public function handle_upload_photo()
    {
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'Not logged in']);
        }

        $user_id = get_current_user_id();

        $file_input = isset($_FILES['profile_photo']) ? 'profile_photo' : (isset($_FILES['cover_photo']) ? 'cover_photo' : null);
        if (!$file_input) {
            wp_send_json_error(['message' => 'No file uploaded']);
        }

        $meta_name  = $file_input === 'profile_photo' ? 'profile_photo' : 'profile_cover_photo';
        $action_key = $file_input === 'profile_photo' ? 'profile_photo_upload' : 'cover_photo_upload';

        $file = $_FILES[$file_input];

        require_once(ABSPATH . 'wp-admin/includes/file.php');
        $overrides = ['test_form' => false];
        $uploaded = wp_handle_upload($file, $overrides);

        if (!isset($uploaded['url'])) {
            wp_send_json_error(['message' => 'Upload failed']);
        }

        $old_value = get_user_meta($user_id, $meta_name, true);
        update_user_meta($user_id, $meta_name, esc_url($uploaded['url']));

        // Trigger gamification and notification only if changed
        if (function_exists('mm_trigger_action') && $uploaded['url'] !== $old_value) {
            mm_trigger_action($action_key, $user_id);
            mm_gamification_award_points($user_id, $action_key);

            if (function_exists('mm_add_notification')) {
                $label = $action_key === 'profile_photo_upload' ? 'Profile Photo Uploaded' : 'Cover Photo Uploaded';
                mm_add_notification($user_id, "You earned points for: $label");
            }
        }

        wp_send_json_success(['url' => $uploaded['url']]);
    }
}

// Initialize
MM_Gamification_User::init();
