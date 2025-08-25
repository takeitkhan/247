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

        // ❌ Reject if sanitized username is different from the original (means invalid chars removed)
        if ($username !== $_POST['username']) {
            set_transient('custom_user_message', [
                'type' => 'danger',
                'text' => 'Username cannot contain special characters. Please use only letters, numbers, underscores, hyphens, or periods.',
                'old_input' => $_POST
            ], 30);
            return;
        }
        // Consent validation (required checkboxes)
        if (empty($_POST['consent_transactional']) || empty($_POST['consent_marketing'])) {
            set_transient('custom_user_message', [
                'type' => 'danger',
                'text' => 'You must agree to both consent checkboxes to register.',
                'old_input' => $_POST
            ], 30);
            return;
        }
        // Prohibit using email as username
        if (is_email($_POST['username'])) {
            set_transient('custom_user_message', [
                'type' => 'danger',
                'text' => 'You cannot use an email address as your username.',
                'old_input' => $_POST
            ], 30);
            return;
        }

        // Check for existing user
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
            // Update user details
            wp_update_user([
                'ID'         => $user_id,
                'first_name' => $first_name,
                'last_name'  => $last_name,
            ]);

            update_user_meta($user_id, 'dob', $dob);

            // Set referrer: use submitted value or fallback to default from WP settings
            $referrer = !empty($_POST['referrer'])
                ? sanitize_text_field($_POST['referrer'])
                : sanitize_text_field(get_option('default_referrer_username'));

            update_user_meta($user_id, 'referrer', $referrer);


            // Save consents
            update_user_meta($user_id, 'consent_transactional', 'yes');
            update_user_meta($user_id, 'consent_marketing', 'yes');

            // Notifications (custom class calls)
            if (class_exists('Notifications')) {
                $notifications = Notifications::getInstance();
                $notifications->add_referrer_notification_for_user($user_id);
                $notifications->add_referral_notification_to_referrer($user_id);
            }

            // Success message
            set_transient('custom_user_message', [
                'type' => 'success',
                'text' => '🎉 Thank you for registering! <a href="/signin" class="alert-link">Login here</a>.'
            ], 30);
        } else {
            // Error from wp_create_user()
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
add_action('init', function () {
    if (
        isset($_POST['user_login']) &&
        check_admin_referer('custom_user_login', 'custom_user_login_nonce')
    ) {
        $creds = [
            'user_login'    => sanitize_user($_POST['username']),
            'user_password' => $_POST['password'],
            'remember'      => true
        ];

        $user = wp_signon($creds, false);

        if (!is_wp_error($user)) {
            wp_redirect(home_url('/modify-profile'));
            exit;
        } else {
            // Get the raw error message (may include <a> tag)
            $raw_error_msg = $user->get_error_message();

            // Allow only safe HTML tags, especially <a> for "Lost your password?"
            $allowed_tags = [
                'a' => [
                    'href' => [],
                    'title' => [],
                    'target' => [],
                    'rel' => [],
                    'class' => [], // add class attribute if you want to add class below
                ],
                'strong' => [],
                'em' => [],
            ];
            $safe_error_msg = wp_kses($raw_error_msg, $allowed_tags);

            // Replace default lostpassword URL with your custom lost password page URL
            $safe_error_msg = preg_replace_callback(
                '#<a href="[^"]+">Lost your password\?</a>#i',
                function () {
                    $url = esc_url(home_url('/lost-password')); // YOUR custom lost password page here
                    return '<a href="' . $url . '" class="alert-link">Lost your password?</a>';
                },
                $safe_error_msg
            );

            set_transient('custom_user_message', [
                'type' => 'error',
                'text' => $safe_error_msg
            ], 30);

            wp_redirect(home_url('/signin'));
            exit;
        }
    }
});

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
    if (!current_user_can('administrator') && !wp_doing_ajax()) {
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
