<?php


add_action('wp_enqueue_scripts', function () {
    if (is_page_template('modify-profile.php')) {
        wp_enqueue_media();  // Enqueue WordPress media
        wp_enqueue_script('jquery');  // Ensure jQuery is loaded
        wp_enqueue_script(
            'modify-profile-js', // Handle
            get_template_directory_uri() . '/assets/js/modify-profile.js', // Path to JS file
            ['jquery'], // Dependencies: Make sure jQuery is loaded before your script
            null, // Version
            true // Load in footer
        );
    }
});

add_action('wp_ajax_upload_cover_photo', 'handle_cover_photo_upload');

function handle_cover_photo_upload()
{
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Not logged in']);
    }

    if (!function_exists('wp_handle_upload')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }

    if (empty($_FILES['cover_photo'])) {
        wp_send_json_error(['message' => 'No file received']);
    }

    $uploadedfile = $_FILES['cover_photo'];
    $upload_overrides = ['test_form' => false];

    $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

    if ($movefile && !isset($movefile['error'])) {
        $user_id = get_current_user_id();
        update_user_meta($user_id, 'profile_cover_photo', esc_url_raw($movefile['url']));
        wp_send_json_success(['url' => esc_url($movefile['url'])]);
    } else {
        $error = isset($movefile['error']) ? $movefile['error'] : 'Unknown error';
        wp_send_json_error(['message' => $error]);
    }
}


add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('dashicons');
});


add_action('wp_ajax_upload_profile_photo', 'handle_profile_photo_upload');
function handle_profile_photo_upload()
{
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Unauthorized']);
    }

    if (!function_exists('wp_handle_upload')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }

    if (empty($_FILES['profile_photo'])) {
        wp_send_json_error(['message' => 'No file uploaded']);
    }

    $uploadedfile = $_FILES['profile_photo'];
    $upload_overrides = ['test_form' => false];

    $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

    if ($movefile && !isset($movefile['error'])) {
        update_user_meta(get_current_user_id(), 'profile_photo', esc_url_raw($movefile['url']));
        wp_send_json_success(['url' => esc_url($movefile['url'])]);
    } else {
        $error = isset($movefile['error']) ? $movefile['error'] : 'Unknown error';
        wp_send_json_error(['message' => $error]);
    }
}

add_action('wp_ajax_create_post', 'handle_create_post');
function handle_create_post()
{
    // Check nonce
    if (!isset($_POST['create_post_nonce']) || !wp_verify_nonce($_POST['create_post_nonce'], 'create_post_action')) {
        wp_send_json_error(array('message' => 'Invalid nonce'), 403);
    }

    // Example basic post creation logic
    $post_content = sanitize_text_field($_POST['post_content']);

    $post_id = wp_insert_post(array(
        'post_type'    => 'post',
        'post_status'  => 'publish',
        'post_content' => $post_content,
        'post_title'   => wp_trim_words($post_content, 10, '...')
    ));

    if (is_wp_error($post_id)) {
        wp_send_json_error(array('message' => 'Failed to create post'));
    }

    // Image handling (if needed)
    if (!empty($_FILES['post_image']['name'])) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $attachment_id = media_handle_upload('post_image', $post_id);

        if (!is_wp_error($attachment_id)) {
            set_post_thumbnail($post_id, $attachment_id);
        }
    }

    wp_send_json_success(array('post_id' => $post_id));
}

// Add inside your theme's functions.php or a loaded plugin
add_action('admin_post_delete_custom_post', 'handle_delete_custom_post');
function handle_delete_custom_post()
{


    if (!is_user_logged_in()) {
        wp_die('Unauthorized');
    }

    $post_id = intval($_GET['post_id'] ?? 0);
    $nonce = $_GET['_wpnonce'] ?? '';
    $redirect_to = $_GET['redirect_to'] ?? '';

    error_log('Deleting post id: ' . $post_id);
    error_log('Current user id: ' . get_current_user_id());
    error_log('Post author id: ' . get_post_field('post_author', $post_id));
    error_log('Nonce valid: ' . wp_verify_nonce($nonce, 'delete_post_' . $post_id));

    if (!$post_id || !wp_verify_nonce($nonce, 'delete_post_' . $post_id)) {
        wp_die('Invalid request');
    }

    if (get_current_user_id() !== (int) get_post_field('post_author', $post_id)) {
        wp_die('You cannot delete this post.');
    }

    if (!current_user_can('delete_post', $post_id)) {
        wp_die('You do not have permission to delete this post.');
    }

    $deleted = wp_delete_post($post_id, true); // force delete

    if (!$deleted) {
        wp_die('Post could not be deleted.');
    }

    if ($redirect_to && wp_http_validate_url($redirect_to)) {
        wp_redirect($redirect_to);
    } else {
        wp_redirect(home_url());
    }
    exit;
}

function add_delete_posts_capability_to_role()
{
    $role = get_role('author'); // or 'editor', 'contributor' as needed
    if ($role && !$role->has_cap('delete_posts')) {
        $role->add_cap('delete_posts');
    }
}
add_action('init', 'add_delete_posts_capability_to_role');


function add_custom_referral_rewrite_rule()
{
    add_rewrite_rule('^([^/]+)/referrals/?$', 'index.php?referral_user=$matches[1]', 'top');
}
add_action('init', 'add_custom_referral_rewrite_rule');

function add_custom_query_vars($vars)
{
    $vars[] = 'username';
    $vars[] = 'referral_user';
    return $vars;
}
add_filter('query_vars', 'add_custom_query_vars');

// Add custom rewrite rule
function custom_rewrite_rule()
{
    // Get all published page slugs
    $pages = get_pages(['post_status' => 'publish']);
    $reserved_slugs = array_map(function ($page) {
        return $page->post_name;
    }, $pages);

    // Escape each slug for regex
    $reserved_pattern = implode('|', array_map('preg_quote', $reserved_slugs));

    // Add rewrite rule excluding those slugs
    add_rewrite_rule(
        '^((?!' . $reserved_pattern . ')[^/]+)/?$',
        'index.php?pagename=custom-user&username=$matches[1]',
        'top'
    );
}
add_action('init', 'custom_rewrite_rule');

function custom_meetings_rewrite_rule()
{
    add_rewrite_rule(
        '^([^/]+)/meetings/?$', // e.g. joseph/meetings
        'index.php?user_profile=$matches[1]&meetings_page=1',
        'top'
    );
}
add_action('init', 'custom_meetings_rewrite_rule');

function load_custom_meetings_template($template) {
    if (get_query_var('meetings_page')) {
        return get_template_directory() . '/template-custom/auth/user-meetings.php';
    }
    return $template;
}
add_filter('template_include', 'load_custom_meetings_template');


function custom_meetings_query_vars($vars)
{
    $vars[] = 'user_profile';
    $vars[] = 'meetings_page';
    return $vars;
}
add_filter('query_vars', 'custom_meetings_query_vars');



add_action('template_redirect', function () {
    $referral_user = get_query_var('referral_user');

    if ($referral_user && file_exists(get_template_directory() . '/template-custom/auth/referrals.php')) {
        include get_template_directory() . '/template-custom/auth/referrals.php';
        exit;
    }
});


add_action('wp_ajax_load_more_referrals', 'load_more_referrals');
add_action('wp_ajax_nopriv_load_more_referrals', 'load_more_referrals');

function load_more_referrals()
{
    $user_id = intval($_GET['user']);
    $offset = intval($_GET['offset']);

    $user = get_user_by('id', $user_id);
    if (!$user) wp_die();

    $referrals = UserProfileData::getReferredUsersBy($user);

    $slice = array_slice($referrals, $offset, 40);

    foreach ($slice as $ref_user) {
        $photo = get_user_meta($ref_user->ID, 'profile_photo', true);
        $photo = $photo ?: 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($ref_user->user_email))) . '?s=150&d=mm';
        $profile_url = site_url('/' . $ref_user->user_login);
?>
        <div class="col referral-card" data-index="<?php echo $offset; ?>">
            <a href="<?php echo esc_url($profile_url); ?>" class="text-dark text-decoration-none">
                <div class="h-100 text-center card">
                    <img src="<?php echo esc_url($photo); ?>" class="card-img-top mx-auto mt-3 rounded-circle" alt="<?php echo esc_attr($ref_user->display_name); ?>" style="width: 100px; height: 100px; object-fit: cover;">
                    <div class="card-body">
                        <h6 class="card-title"><?php echo esc_html($ref_user->display_name); ?></h6>
                    </div>
                </div>
            </a>
        </div>
    <?php
        $offset++;
    }

    wp_die();
}

// Display referred_by field in user profile
function show_referred_by_field($user)
{
    $referred_by = get_user_meta($user->ID, 'referrer', true);
    ?>
    <h3>Referral Info</h3>
    <table class="form-table">
        <tr>
            <th><label for="referrer">Referred By (Username or User ID)</label></th>
            <td>
                <input type="text" name="referrer" id="referrer" value="<?php echo esc_attr($referred_by); ?>" class="regular-text" />
                <p class="description">Enter the username or user ID of the referrer.</p>
            </td>
        </tr>
    </table>
<?php
}
add_action('show_user_profile', 'show_referred_by_field');
add_action('edit_user_profile', 'show_referred_by_field');

// Save referred_by field
function save_referred_by_field($user_id)
{
    if (!current_user_can('edit_user', $user_id)) return;

    if (isset($_POST['referrer'])) {
        update_user_meta($user_id, 'referrer', sanitize_text_field($_POST['referrer']));
    }
}
add_action('personal_options_update', 'save_referred_by_field');
add_action('edit_user_profile_update', 'save_referred_by_field');
