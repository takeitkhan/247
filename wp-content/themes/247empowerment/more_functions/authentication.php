<?php
// -----------------------------
// Custom User Registration
// -----------------------------


add_action('init', function () {
    if (
        isset($_POST['user_signup']) &&
        check_admin_referer('custom_user_registration', 'custom_user_registration_nonce')
    ) {
        $username   = sanitize_user($_POST['username']);
        $email      = sanitize_email($_POST['email']);
        $password   = $_POST['password'];
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name  = sanitize_text_field($_POST['last_name']);
        $dob        = sanitize_text_field($_POST['dob']);

        // ❌ Reject invalid username
        if ($username !== $_POST['username']) {
            set_transient('custom_user_message', [
                'type' => 'danger',
                'text' => 'Username cannot contain special characters.',
                'old_input' => $_POST
            ], 30);
            return;
        }

        // Reject uppercase characters
        if (preg_match('/[A-Z]/', $_POST['username'])) {
            set_transient('custom_user_message', [
                'type' => 'danger',
                'text' => 'Username must be lowercase only.',
                'old_input' => $_POST
            ], 30);
            return;
        }

        // Reject username containing any special characters
        if (!preg_match('/^[a-z0-9_]+$/', $username)) {
            set_transient('custom_user_message', [
                'type' => 'danger',
                'text' => 'Username can only contain letters and numbers.',
                'old_input' => $_POST
            ], 30);
            return;
        }

        if (empty($_POST['consent']) || $_POST['consent'] !== 'yes') {
            set_transient('custom_user_message', [
                'type' => 'danger',
                'text' => 'You must agree to the consent checkbox.',
                'old_input' => $_POST
            ], 30);
            return;
        }


        if (is_email($_POST['username'])) {
            set_transient('custom_user_message', [
                'type' => 'danger',
                'text' => 'You cannot use an email as username.',
                'old_input' => $_POST
            ], 30);
            return;
        }

        if (username_exists($username) || email_exists($email)) {
            set_transient('custom_user_message', [
                'type' => 'danger',
                'text' => 'Username or Email already exists.',
                'old_input' => $_POST
            ], 30);
            return;
        }

        // Create user
        $user_id = wp_create_user($username, $password, $email);

        if (!is_wp_error($user_id)) {
            wp_update_user([
                'ID'         => $user_id,
                'first_name' => $first_name,
                'last_name'  => $last_name,
            ]);
            update_user_meta($user_id, 'dob', $dob);

            $referrer = !empty($_POST['referrer'])
                ? sanitize_text_field($_POST['referrer'])
                : sanitize_text_field(get_option('default_referrer_username'));
            update_user_meta($user_id, 'referrer', $referrer);

            update_user_meta($user_id, 'consent_transactional', 'yes');
            update_user_meta($user_id, 'consent_marketing', 'yes');

            // Notifications
            if (class_exists('Notifications')) {
                $notifications = Notifications::getInstance();
                $notifications->add_referrer_notification_for_user($user_id);
                $notifications->add_referral_notification_to_referrer($user_id);
            }

            // ✅ Trigger the registered action
            if (function_exists('mm_trigger_action')) {
                mm_trigger_action('user_register', $user_id);
            }

            // Set success message
            set_transient('custom_user_message', [
                'type' => 'success',
                'text' => 'You have received <span style="color: #E835B0;">30 Points</span> for registering into 24/7 Empowerment\'s Non-Profit Social Platform.'
            ], 30);


            // ✅ Redirect to login page
            wp_redirect(home_url('/signin')); // your login page URL
            exit; // ← important
        } else {
            set_transient('custom_user_message', [
                'type' => 'danger',
                'text' => $user_id->get_error_message()
            ], 30);
        }
    }
});

// -----------------------------
// Custom User Login
// -----------------------------
function handle_custom_user_login()
{

    // Only trigger on our custom login form
    if (
        !empty($_POST['action']) &&
        $_POST['action'] === 'custom_user_login' &&
        check_admin_referer('custom_user_login', 'custom_user_login_nonce')
    ) {

        $creds = [
            'user_login'    => sanitize_user($_POST['username']),
            'user_password' => $_POST['password'],
            'remember'      => true,
        ];

        $user = wp_signon($creds, false);

        if (!is_wp_error($user)) {

            // First login trigger
            if (function_exists('mm_trigger_action')) {
                $last_login = get_user_meta($user->ID, 'last_login', true);
                if (empty($last_login)) {
                    mm_trigger_action('first_login', $user->ID);
                }
                update_user_meta($user->ID, 'last_login', current_time('mysql'));
            }

            // Redirect to modify profile
            wp_redirect(home_url('/modify-profile'));
            exit;
        } else {

            // Preserve safe error message with "Lost your password?" replacement
            $raw_error_msg = $user->get_error_message();
            $allowed_tags = ['a' => ['href' => [], 'class' => []], 'strong' => [], 'em' => []];
            $safe_error_msg = wp_kses($raw_error_msg, $allowed_tags);

            $safe_error_msg = preg_replace_callback(
                '#<a href="[^"]+">Lost your password?</a>#i',
                function () {
                    $url = esc_url(home_url('/lost-password'));
                    return '<a href="' . $url . '" class="alert-link">Lost your password?</a>';
                },
                $safe_error_msg
            );

            // Save transient for displaying on signin page
            set_transient('custom_user_message', [
                'type' => 'danger',
                'text' => $safe_error_msg
            ], 30);

            // Redirect back to signin
            wp_redirect(home_url('/signin'));
            exit;
        }
    }
}
add_action('init', 'handle_custom_user_login');

// Register AJAX action for logged-in users
add_action('wp_ajax_frontend_profile_update', 'ajax_frontend_profile_update');

function ajax_frontend_profile_update() {
    // Security check
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'frontend_profile_update')) {
        wp_send_json_error(['message' => 'Security check failed']);
    }

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'You must be logged in']);
    }

    $user_id = get_current_user_id();
    $post_data = $_POST;

    // Use the previously defined handler function
    $result = handle_frontend_profile_update($user_id, $post_data);

    if (!empty($result['error'])) {
        wp_send_json_error(['message' => $result['error']]);
    }

    wp_send_json_success([
        'notifications' => $result['notifications'],
        'message' => 'Profile updated successfully'
    ]);
}


/**
 * Handle frontend profile update, award points for birthday/location once, and return notification data.
 */
function handle_frontend_profile_update($user_id, $post_data) {
    if (!function_exists('mm_award_points_and_notify')) return ['error' => 'Gamification function missing'];

    $notification_data = [];

    // Map form field to gamification action
    $fields = [
        'dob' => 'birthday_update',
        'place_display_name' => 'location_update',
    ];

    foreach ($fields as $meta_key => $action_key) {
        $old_value = get_user_meta($user_id, $meta_key, true);
        $new_value = sanitize_text_field($post_data[$meta_key] ?? '');

        if (!empty($new_value) && $new_value !== $old_value) {
            // Award points once
            $earned_key = $action_key . '_earned_once';
            $already_earned = get_user_meta($user_id, $earned_key, true);

            if (!$already_earned) {
                $notif = mm_award_points_and_notify($user_id, $action_key);
                if ($notif) $notification_data[] = $notif;
                update_user_meta($user_id, $earned_key, 1);
            }

            update_user_meta($user_id, $meta_key, $new_value);
        }
    }

    // Basic fields
    wp_update_user([
        'ID'         => $user_id,
        'first_name' => sanitize_text_field($post_data['first_name'] ?? ''),
        'last_name'  => sanitize_text_field($post_data['last_name'] ?? ''),
        'user_email' => sanitize_email($post_data['email'] ?? ''),
    ]);

    // Other meta fields
    $meta_fields = ['phone','about_me','about_me_short','latitude','longitude','place_address'];
    foreach ($meta_fields as $meta_key) {
        if (isset($post_data[$meta_key])) {
            update_user_meta($user_id, $meta_key, sanitize_text_field($post_data[$meta_key]));
        }
    }

    // Checkbox fields
    $checkbox_fields = ['show_email','show_phone','show_dob','show_full_address'];
    foreach ($checkbox_fields as $meta_key) {
        update_user_meta($user_id, $meta_key, isset($post_data[$meta_key]) ? '1' : '0');
    }

    // Categories
    if (!empty($post_data['user_categories']) && is_array($post_data['user_categories'])) {
        update_user_meta($user_id, 'user_categories', array_map('intval', $post_data['user_categories']));
    }

    return [
        'success' => true,
        'notifications' => $notification_data,
    ];
}

// Handle profile photo upload
add_action('wp_ajax_upload_profile_photo', function () {
    check_ajax_referer('update_profile_photo_nonce', 'nonce');

    if (!is_user_logged_in() || empty($_FILES['profile_photo'])) {
        wp_send_json_error(['message' => 'No file uploaded or not logged in']);
    }

    $user_id = get_current_user_id();
    $file = $_FILES['profile_photo'];

    // Use WordPress media uploader
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');

    $attachment_id = media_handle_upload('profile_photo', 0); // 0 = no parent post
    if (is_wp_error($attachment_id)) {
        wp_send_json_error(['message' => $attachment_id->get_error_message()]);
    }

    // Save the attachment URL in user meta
    $url = wp_get_attachment_url($attachment_id);
    update_user_meta($user_id, 'profile_photo', $url);

    wp_send_json_success(['url' => $url]);
});

// Handle profile photo deletion
add_action('wp_ajax_delete_profile_photo', function () {
    check_ajax_referer('update_profile_photo_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Not logged in']);
    }

    $user_id = get_current_user_id();
    $photo_url = get_user_meta($user_id, 'profile_photo', true);

    if ($photo_url) {
        // Get attachment ID from URL
        $attachment_id = attachment_url_to_postid($photo_url);
        if ($attachment_id) {
            wp_delete_attachment($attachment_id, true); // delete from Media Library
        }
        delete_user_meta($user_id, 'profile_photo');
    }

    wp_send_json_success();
});



// add_action('init', function () {
//     // 🛑 GATES: Only execute this block if the specific hidden field is present.
//     if (! isset($_POST['action']) || $_POST['action'] !== 'custom_user_login_form_submit') {
//         return; // Exit early if it's not the intended login form submission
//     }
//     // Corrected condition to check for login action on event form submissions
//     if (
//         isset($_POST['action']) && $_POST['action'] === 'custom_user_login' &&
//         check_admin_referer('custom_user_login', 'custom_user_login_nonce')
//     ) {
//         $creds = [
//             'user_login'    => sanitize_user($_POST['username']),
//             'user_password' => $_POST['password'],
//             'remember'      => true
//         ];

//         $user = wp_signon($creds, false);

//         if (!is_wp_error($user)) {
//             // ✅ Check for first login
//             if (function_exists('mm_trigger_action')) {
//                 $last_login = get_user_meta($user->ID, 'last_login', true);
//                 if (empty($last_login)) {
//                     mm_trigger_action('first_login', $user->ID);
//                 }
//                 update_user_meta($user->ID, 'last_login', current_time('mysql'));
//             }

//             if (!is_wp_error($user)) {                
//                 wp_redirect(home_url('/modify-profile'));
//                 exit; // This exit will now only run for successful logins
//             }
//         } else {
//             $raw_error_msg = $user->get_error_message();
//             $allowed_tags = ['a' => ['href' => [], 'class' => []], 'strong' => [], 'em' => []];
//             $safe_error_msg = wp_kses($raw_error_msg, $allowed_tags);

//             $safe_error_msg = preg_replace_callback(
//                 '#<a href="[^"]+">Lost your password\?</a>#i',
//                 function () {
//                     $url = esc_url(home_url('/lost-password'));
//                     return '<a href="' . $url . '" class="alert-link">Lost your password?</a>';
//                 },
//                 $safe_error_msg
//             );

//             set_transient('custom_user_message', [
//                 'type' => 'error',
//                 'text' => $safe_error_msg
//             ], 30);

//             wp_redirect(home_url('/signin'));
//             exit;
//         }
//     }
// });


add_action('init', function () {
    add_rewrite_tag('%user_profile%', '([^&]+)');

    $existing_slugs = [];

    // Get all published page and post slugs to prevent conflicts
    $pages = get_pages(['post_status' => 'publish']);
    $posts = get_posts(['post_type' => 'post', 'numberposts' => -1, 'post_status' => 'publish']);

    foreach ($pages as $page) {
        $existing_slugs[] = $page->post_name;
    }
    foreach ($posts as $post) {
        $existing_slugs[] = $post->post_name;
    }

    // Avoid any known slugs
    $pattern = '^((?!' . implode('|', array_map('preg_quote', $existing_slugs)) . ')[^/]+)/?$';

    add_rewrite_rule(
        $pattern,
        'index.php?user_profile=$matches[1]',
        'top'
    );
});



add_filter('query_vars', function ($vars) {
    $vars[] = 'user_profile';
    return $vars;
});

// -----------------------------
// Admin Area Restriction
// -----------------------------
add_action('admin_init', function () {

    // Exclude event form submissions
    if (isset($_POST['action']) && $_POST['action'] === 'submit_event_form') {
        return; // Allow event submission to continue
    }

    // Exclude AJAX requests
    if (wp_doing_ajax()) {
        return;
    }

    // Only allow admins to access admin area
    if (!current_user_can('administrator')) {
        wp_redirect(home_url('/modify-profile'));
        exit;
    }
});


add_action('template_redirect', function () {
    // Redirect lost password requests from wp-login.php to custom page
    if (
        isset($_GET['action']) &&
        $_GET['action'] === 'lostpassword' &&
        strpos($_SERVER['REQUEST_URI'], 'wp-login.php') !== false
    ) {
        wp_redirect(site_url('/lost-password')); // Change this URL as needed
        exit;
    }

    // If user is not logged in, no further redirects
    if (!is_user_logged_in()) {
        return;
    }

    // Pages to redirect logged-in users away from
    $redirect_to_dashboard = ['signin', 'signup'];
    $redirect_to_home = ['video-library', 'faqs'];

    $current_slug = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

    if (in_array($current_slug, $redirect_to_dashboard)) {
        wp_redirect(home_url('/dashboard'));
        exit;
    }

    if (in_array($current_slug, $redirect_to_home)) {
        wp_redirect(home_url());
        exit;
    }
});


// Replace the default WP lost password URL with your custom lost password page URL
add_filter('lostpassword_url', function ($lostpassword_url, $redirect) {
    return site_url('/lost-password'); // Your custom lost password page URL here
}, 10, 2);


add_action('template_redirect', 'handle_password_change');
function handle_password_change()
{
    if (is_page_template('change-password.php') && is_user_logged_in()) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
            if (!isset($_POST['change_password_nonce']) || !wp_verify_nonce($_POST['change_password_nonce'], 'change_password_action')) {
                wp_die('Security check failed.');
            }

            $new_password = sanitize_text_field($_POST['new_password']);
            $confirm_password = sanitize_text_field($_POST['confirm_password']);

            if (empty($new_password) || empty($confirm_password)) {
                wp_die('All fields are required.');
            } elseif ($new_password !== $confirm_password) {
                wp_die('Passwords do not match.');
            } else {
                wp_set_password($new_password, get_current_user_id());
                wp_logout();
                wp_redirect(wp_login_url());
                exit;
            }
        }
    }
}

add_action('init', function () {
    if (!is_user_logged_in()) {
        return; // Skip if not logged in
    }

    global $pagenow;

    if (!current_user_can('administrator')) {
        // Check if user is accessing /dashboard via pretty permalinks
        if (strpos($_SERVER['REQUEST_URI'], '/dashboard') === 0) {
            wp_redirect(home_url());
            exit;
        }

        // Also check for direct admin URLs (optional)
        if ($pagenow === 'admin.php' && isset($_GET['page']) && $_GET['page'] === 'dashboard') {
            wp_redirect(home_url());
            exit;
        }
    }
});
