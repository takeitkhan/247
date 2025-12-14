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

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('dashicons');
});

add_action('wp_ajax_upload_cover_photo', 'handle_cover_photo_upload');
function handle_cover_photo_upload()
{
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Unauthorized']);
    }

    if (!function_exists('wp_handle_upload')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }

    if (empty($_FILES['cover_photo'])) {
        wp_send_json_error(['message' => 'No file uploaded']);
    }

    $uploadedfile = $_FILES['cover_photo'];
    $upload_overrides = ['test_form' => false];

    $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

    if ($movefile && !isset($movefile['error'])) {
        update_user_meta(get_current_user_id(), 'cover_photo', esc_url_raw($movefile['url']));
        wp_send_json_success(['url' => esc_url($movefile['url'])]);
    } else {
        $error = isset($movefile['error']) ? $movefile['error'] : 'Unknown error';
        wp_send_json_error(['message' => $error]);
    }
}




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


function enqueue_post_create_script()
{
    wp_enqueue_script(
        'post-create-js',
        get_template_directory_uri() . '/assets/js/post-create.js',
        array('jquery'),
        null,
        true
    );

    wp_localize_script('post-create-js', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_post_create_script');

function enqueue_profile_map_script()
{
    if (is_page_template('template-custom/auth/modify-profile.php')) {
        wp_enqueue_script(
            'profile-map',
            get_template_directory_uri() . '/assets/js/profile-map.js',
            [],
            null,
            true
        );
        wp_enqueue_script(
            'google-maps',
            'https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places&callback=initProfileMap',
            [],
            null,
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'enqueue_profile_map_script');



add_action('wp_ajax_create_post', 'handle_create_post');
function handle_create_post()
{
    // Check nonce
    if (!isset($_POST['create_post_nonce']) || !wp_verify_nonce($_POST['create_post_nonce'], 'create_post_action')) {
        wp_send_json_error(array('message' => 'Invalid nonce'), 403);
    }

    // Sanitize post content
    $post_content = sanitize_text_field($_POST['post_content']);

    // Sanitize privacy option
    $privacy_options = array('only_me', 'referral_partners', 'public');
    $post_privacy = in_array($_POST['post_privacy'] ?? '', $privacy_options) ? $_POST['post_privacy'] : 'only_me';

    // Create post
    $post_id = wp_insert_post(array(
        'post_type'    => 'post',
        'post_status'  => 'publish',
        'post_content' => $post_content,
        'post_title'   => wp_trim_words($post_content, 10, '...')
    ));

    if (is_wp_error($post_id)) {
        wp_send_json_error(array('message' => 'Failed to create post'));
    }

    // Save privacy as post meta
    update_post_meta($post_id, '_post_privacy', $post_privacy);

    // Image handling
    if (!empty($_FILES['post_image']['name'])) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $attachment_id = media_handle_upload('post_image', $post_id);
        if (!is_wp_error($attachment_id)) {
            set_post_thumbnail($post_id, $attachment_id);
        }
    }

    wp_send_json_success(array(
        'post_id' => $post_id,
        'privacy' => $post_privacy
    ));
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

function load_custom_meetings_template($template)
{
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


// Load referral partners script
function load_more_referrals_callback()
{
    $user_id = isset($_GET['user']) ? intval($_GET['user']) : 0;
    $offset  = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    $limit   = 8;

    $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';


    if ($search) {
        $profiles = array_filter($profiles, function ($p) use ($search) {
            return str_contains(
                strtolower(
                    $p['first_name'] . ' ' .
                        $p['last_name'] . ' ' .
                        $p['username']
                ),
                strtolower($search)
            );
        });

        $profiles = array_values($profiles);
    }

    if (!$user_id) {
        wp_send_json_error('Invalid user ID');
    }

    $user = get_user_by('id', $user_id);
    if (!$user) {
        wp_send_json_error('User not found');
    }

    /* -------------------------------------------------
     * Fetch referrals (raw)
     * ------------------------------------------------- */
    $raw_referrals = UserProfileData::getReferredUsersBy($user);

    /* -------------------------------------------------
     * Normalize → profile arrays
     * ------------------------------------------------- */
    $profiles = [];

    foreach ($raw_referrals as $ref) {

        $ref_id = is_array($ref)
            ? ($ref['id'] ?? 0)
            : ($ref->ID ?? 0);

        if (!$ref_id) {
            continue;
        }

        $profile = (new UserProfileData($ref_id))->getProfile();
        if ($profile) {
            $profiles[] = $profile;
        }
    }

    $sort = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : 'recent';

    usort($profiles, function ($a, $b) use ($sort) {
        switch ($sort) {
            case 'name_asc':
                return strcasecmp($a['first_name'] . $a['last_name'], $b['first_name'] . $b['last_name']);
            case 'name_desc':
                return strcasecmp($b['first_name'] . $b['last_name'], $a['first_name'] . $a['last_name']);
            case 'recent':
            default:
                return $b['id'] <=> $a['id'];
        }
    });


    /* -------------------------------------------------
     * Slice (pagination)
     * ------------------------------------------------- */
    $slice = array_slice($profiles, $offset, $limit);

    if (empty($slice)) {
        wp_send_json_success('');
    }

    /* -------------------------------------------------
     * Render HTML
     * ------------------------------------------------- */
    ob_start();

    foreach ($slice as $profile) {

        $photo = $profile['profile_photo']
            ?: 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($profile['email']))) . '?s=150&d=mm';
?>
        <div class="d-flex flex-column flex-lg-row justify-content-between py-3">
            <div class="d-flex align-items-center gap-3">
                <div class="img60">
                    <img src="<?= esc_url($photo); ?>"
                        class="w-100 h-100 object-fit-cover"
                        alt="<?= esc_attr($profile['first_name']); ?>">
                </div>

                <div class="post-user">
                    <a href="<?= esc_url($profile['profile_url']); ?>" class="fw-bold">
                        <?= esc_html($profile['first_name'] . ' ' . $profile['last_name']); ?>
                    </a>
                    <?php if ($profile['about_me_short']) { ?>
                        <span class="px-2">
                            <i class="far fa-bookmark"></i>
                            <?= esc_html($profile['about_me_short']); ?>
                        </span>
                    <?php } ?>

                    <?php if (!empty($profile['user_category_names'])) : ?>
                        <div class="fs14">
                            <i class="fas fa-briefcase"></i> <?= esc_html(implode(', ', $profile['user_category_names'])); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="dropdown">
                <button class="btn" data-bs-toggle="dropdown">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <button class="dropdown-item remove-partner-btn"
                            data-user-id="<?= esc_attr($profile['id']); ?>">
                            Remove Partner
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    <?php
    }

    wp_send_json_success(ob_get_clean());
}

add_action('wp_ajax_load_more_referrals', 'load_more_referrals_callback');
add_action('wp_ajax_nopriv_load_more_referrals', 'load_more_referrals_callback');


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

// Handle delete profile photo
add_action('wp_ajax_delete_profile_photo', function () {
    if (!is_user_logged_in()) wp_send_json_error('Not authorized');

    $user_id = get_current_user_id();
    delete_user_meta($user_id, 'profile_photo');

    wp_send_json_success('Profile photo deleted');
});


add_filter('get_avatar', 'mm_custom_user_avatar', 10, 5);


// Default Gravatar to Profile Photo in Dashboard > Users
function mm_custom_user_avatar($avatar, $id_or_email, $size, $default, $alt)
{
    $user = false;

    // Resolve user
    if (is_numeric($id_or_email)) {
        $user = get_user_by('id', (int) $id_or_email);
    } elseif (is_object($id_or_email)) {
        if (!empty($id_or_email->user_id)) {
            $user = get_user_by('id', (int) $id_or_email->user_id);
        }
    } elseif (is_string($id_or_email)) {
        $user = get_user_by('email', $id_or_email);
    }

    if (!$user) {
        return $avatar;
    }

    // Get custom profile photo
    $custom_avatar = get_user_meta($user->ID, 'profile_photo', true);

    if (!$custom_avatar) {
        return $avatar; // fallback to Gravatar
    }

    // Return custom avatar HTML
    return sprintf(
        '<img src="%s" alt="%s" width="%d" height="%d" class="custom-avatar avatar avatar-%d photo" />',
        esc_url($custom_avatar),
        esc_attr($alt),
        (int) $size,
        (int) $size,
        (int) $size
    );
}
