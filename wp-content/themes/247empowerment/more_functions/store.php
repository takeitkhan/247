<?php
function register_course_post_type()
{
    $labels = [
        'name' => 'Courses',
        'singular_name' => 'Course',
        'menu_name' => 'Courses',
    ];

    $args = [
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'courses'],
        'supports' => ['title', 'editor', 'thumbnail'],
        'menu_icon' => 'dashicons-welcome-learn-more',
        'show_in_rest' => true,
    ];

    register_post_type('course', $args);
}
add_action('init', 'register_course_post_type');

function register_course_category_taxonomy()
{
    register_taxonomy('course_category', 'course', [
        'labels' => [
            'name' => 'Course Categories',
            'singular_name' => 'Course Category',
        ],
        'public' => true,
        'hierarchical' => true,
        'rewrite' => ['slug' => 'course-category'],
        'show_in_rest' => true,
    ]);
}
add_action('init', 'register_course_category_taxonomy');

function add_custom_store_rewrite_rule()
{
    // Store page
    add_rewrite_rule('^([^/]+)/store/?$', 'index.php?store_user=$matches[1]', 'top');

    // Single course under store
    add_rewrite_rule('^([^/]+)/store/([^/]+)/?$', 'index.php?store_user=$matches[1]&course_slug=$matches[2]', 'top');
}
add_action('init', 'add_custom_store_rewrite_rule');

// Add `store_user` and `course_slug` as query vars
function add_store_query_vars($vars)
{
    $vars[] = 'store_user';
    $vars[] = 'course_slug';
    return $vars;
}
add_filter('query_vars', 'add_store_query_vars');


function load_store_template($template)
{
    $store_user = get_query_var('store_user');
    $course_slug = get_query_var('course_slug');

    if ($store_user && $course_slug) {
        return get_template_directory() . '/template-custom/auth/single-store-template.php';
    } elseif ($store_user) {
        return get_template_directory() . '/template-custom/auth/store-template.php';
    }

    return $template;
}
add_filter('template_include', 'load_store_template');

// Add custom action link for each course row in admin
add_filter('post_row_actions', 'add_purchaser_link_to_course_row', 10, 2);



add_filter('post_row_actions', function ($actions, $post) {
    if ($post->post_type === 'course') {
        $current_user = wp_get_current_user();
        $store_user = $current_user->user_login ?: 'joseph'; // fallback if no user logged in

        $custom_url = home_url("/{$store_user}/store/{$post->post_name}/");

        if (isset($actions['view'])) {
            $actions['view'] = '<a href="' . esc_url($custom_url) . '" target="_blank" rel="noopener noreferrer">View</a>';
        }
    }
    return $actions;
}, 10, 2);


function add_purchaser_link_to_course_row($actions, $post)
{
    if ($post->post_type === 'course') {
        $url = admin_url('admin.php?page=course_purchasers&course_id=' . $post->ID);
        $actions['purchasers'] = '<a href="' . esc_url($url) . '">Purchasers</a>';
    }
    return $actions;
}



add_action('admin_menu', function () {
    add_submenu_page(
        'edit.php?post_type=course',
        'Course Purchasers',
        'Course Purchasers',
        'manage_options',
        'course_purchasers',
        'render_course_purchasers_page'
    );
});

add_filter('submenu_file', function ($submenu_file) {
    // Prevent it from appearing selected/highlighted
    if (isset($_GET['page']) && $_GET['page'] === 'course_purchasers') {
        return 'edit.php?post_type=course';
    }
    return $submenu_file;
});


function render_course_purchasers_page()
{
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }

    $course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

    if (!$course_id || get_post_type($course_id) !== 'course') {
        echo '<div class="notice notice-error"><p>Invalid Course ID</p></div>';
        return;
    }

    echo '<div class="wrap">';
    echo '<h1>Purchasers of: ' . get_the_title($course_id) . '</h1>';

    $user_query = new WP_User_Query([
        'meta_query' => [
            [
                'key' => 'purchased_courses',
                'value' => 'i:' . $course_id . ';',  // Searching for serialized integer in meta
                'compare' => 'LIKE',
            ],
        ],
    ]);

    $users = $user_query->get_results();

    if (empty($users)) {
        echo '<p>No users have purchased this course yet.</p>';
    } else {
        echo '<table class="wp-list-table fixed widefat striped">';
        echo '<thead><tr><th>Name</th><th>Display Name</th><th>Email</th><th>User ID</th></tr></thead>';
        echo '<tbody>';
        foreach ($users as $user) {
            echo '<tr>';
            echo '<td>' . esc_html($user->first_name . " " . $user->last_name) . '</td>';
            echo '<td>' . esc_html($user->display_name) . '</td>';
            echo '<td>' . esc_html($user->user_email) . '</td>';
            echo '<td>' . esc_html($user->ID) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }

    echo '<p><a href="' . admin_url('edit.php?post_type=course') . '">&laquo; Back to Courses</a></p>';
    echo '</div>';
}





// add_action('wp_ajax_handle_course_purchase', 'handle_course_purchase');
// add_action('wp_ajax_nopriv_handle_course_purchase', 'handle_course_purchase');


// /**
//  * Summary of handle_course_purchase
//  * @return void
//  */
// function handle_course_purchase()
// {
//     $user_id = get_current_user_id();
//     $course_id = isset($_POST['course_id']) ? (int) $_POST['course_id'] : 0;
//     $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;

//     if (!$user_id || !$course_id || $amount <= 0) {
//         wp_send_json_error(['message' => 'Invalid purchase data']);
//     }

//     // ✅ Save purchased course
//     $purchased_courses = get_user_meta($user_id, 'purchased_courses', true);
//     $purchased_courses = is_array($purchased_courses) ? $purchased_courses : [];

//     if (!in_array($course_id, $purchased_courses)) {
//         $purchased_courses[] = $course_id;
//         update_user_meta($user_id, 'purchased_courses', $purchased_courses);
//     }

//     // ✅ Share commission if user was referred
//     give_referral_commission($user_id, $amount, $course_id);

//     wp_send_json_success(['message' => 'Purchase successful, referral commission processed']);
// }




add_action('template_redirect', function () {
    if (!is_user_logged_in() || !isset($_GET['shareable'])) return;

    $current_url = home_url($_SERVER['REQUEST_URI']);
    $clean_url = remove_query_arg('shareable', $current_url);

    // Save the referrer (username in URL path)
    global $wp;
    $path_parts = explode('/', trim($wp->request, '/'));
    if (!empty($path_parts[0])) {
        $referrer = sanitize_user($path_parts[0]);
        update_user_meta(get_current_user_id(), 'referrer', $referrer);
    }

    // Redirect to clean URL without query
    wp_redirect($clean_url, 301);
    exit;
});


// Add field to Add New Term form
add_action('course_category_add_form_fields', function () {
?>
    <div class="form-field">
        <label for="term_icon">Icon Image</label>
        <input type="hidden" id="term_icon" name="term_icon" value="">
        <div id="term_icon_preview"></div>
        <button type="button" class="button upload_term_icon_button">Upload Icon</button>
        <button type="button" class="button remove_term_icon_button" style="display:none;">Remove Icon</button>
    </div>
<?php
});

// Add field to Edit Term form
add_action('course_category_edit_form_fields', function ($term) {
    $icon_id = get_term_meta($term->term_id, 'term_icon', true);
    $icon_url = $icon_id ? wp_get_attachment_url($icon_id) : '';
?>
    <tr class="form-field">
        <th scope="row"><label for="term_icon">Icon Image</label></th>
        <td>
            <input type="hidden" id="term_icon" name="term_icon" value="<?php echo esc_attr($icon_id); ?>">
            <div id="term_icon_preview">
                <?php if ($icon_url): ?>
                    <img src="<?php echo esc_url($icon_url); ?>" alt="" style="max-width:80px; display:block; margin-bottom:10px;">
                <?php endif; ?>
            </div>
            <button type="button" class="button upload_term_icon_button"><?php echo $icon_url ? 'Change Icon' : 'Upload Icon'; ?></button>
            <button type="button" class="button remove_term_icon_button" <?php if (!$icon_url) echo 'style="display:none;"'; ?>>Remove Icon</button>
        </td>
    </tr>
<?php
});

// Save term meta
add_action('created_course_category', 'save_course_category_icon');
add_action('edited_course_category', 'save_course_category_icon');
function save_course_category_icon($term_id)
{
    if (isset($_POST['term_icon'])) {
        update_term_meta($term_id, 'term_icon', sanitize_text_field($_POST['term_icon']));
    }
}

add_action('admin_enqueue_scripts', function ($hook) {
    // Load only on taxonomy pages
    if (!in_array($hook, ['edit-tags.php', 'term.php'])) return;

    wp_enqueue_media();
    wp_enqueue_script('jquery');

    wp_add_inline_script('jquery', "
        jQuery(document).ready(function($){
            let frame;

            function setImage(input, url, id) {
                input.val(id);
                input.siblings('#term_icon_preview').html('<img src=\"'+url+'\" style=\"max-width:80px; display:block; margin-bottom:10px;\">');
                input.siblings('.remove_term_icon_button').show();
            }

            $(document).on('click', '.upload_term_icon_button', function(e){
                e.preventDefault();
                const button = $(this);
                const input = button.siblings('#term_icon');

                frame = wp.media({
                    title: 'Select or Upload Icon',
                    button: { text: 'Use this image' },
                    multiple: false
                });

                frame.on('select', function(){
                    const attachment = frame.state().get('selection').first().toJSON();
                    setImage(input, attachment.url, attachment.id);
                });

                frame.open();
            });

            $(document).on('click', '.remove_term_icon_button', function(){
                const button = $(this);
                button.siblings('#term_icon').val('');
                button.siblings('#term_icon_preview').html('');
                button.hide();
            });
        });
    ");
});

/**
 * ACF Field Group: Course Benefits
 */
if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group([
        'key' => 'group_course_benefits',
        'title' => 'Course Benefits',
        'fields' => [
            [
                'key' => 'field_course_benefits_repeater',
                'label' => 'Benefits',
                'name' => 'course_benefits',
                'type' => 'repeater',
                'instructions' => 'Add benefits that this course provides',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => [
                    'width' => '100',
                    'class' => '',
                    'id' => '',
                ],
                'collapsed' => '',
                'min' => 0,
                'max' => 0,
                'layout' => 'table',
                'button_label' => 'Add Benefit',
                'sub_fields' => [
                    [
                        'key' => 'field_benefit_text',
                        'label' => 'Benefit Text',
                        'name' => 'benefit_text',
                        'type' => 'text',
                        'instructions' => 'What is the benefit?',
                        'required' => 1,
                        'conditional_logic' => 0,
                        'wrapper' => [
                            'width' => '100',
                            'class' => '',
                            'id' => '',
                        ],
                        'default_value' => '',
                        'placeholder' => 'e.g. Full-day guided experience',
                        'prepend' => '',
                        'append' => '',
                    ],
                ],
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'course',
                ],
            ],
        ],
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
    ]);
}
