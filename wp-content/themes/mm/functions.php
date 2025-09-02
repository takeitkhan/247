<?php
function mm_theme_setup()
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
}
add_action('after_setup_theme', 'mm_theme_setup');

function enqueue_wp_media_scripts()
{
    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'enqueue_wp_media_scripts');

function enqueue_custom_scripts()
{
    // Enqueue post-create.js
    wp_enqueue_script('post-create', get_template_directory_uri() . '/assets/js/post-create.js', array('jquery'), null, true);

    wp_localize_script('post-create', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('create_post_action')
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');



function enqueue_jquery()
{
    if (!is_admin()) {
        wp_enqueue_script('jquery'); // Enqueue WordPress jQuery
    }
}
add_action('wp_enqueue_scripts', 'enqueue_jquery');

function mm_enqueue_styles()
{
    wp_enqueue_style('mm-style', get_stylesheet_uri() . '?time=' . time());
}
add_action('wp_enqueue_scripts', 'mm_enqueue_styles');

function register_my_menus()
{
    register_nav_menus([
        'primary' => __('Primary Menu', 'mm'),
        'secondary' => __('Footer Menu', 'mm'),
    ]);
}
add_action('after_setup_theme', 'register_my_menus');

class MM_Walker_Nav_Menu extends Walker_Nav_Menu
{
    function start_el(&$output, $item, $depth = 0, $args = [], $id = 0)
    {
        $classes = 'hover:text-white transition-colors';
        $output .= sprintf(
            '<a href="%s" class="%s">%s</a>',
            esc_url($item->url),
            $classes,
            esc_html($item->title)
        );
    }
}

Kirki::add_config('my_config', array(
    'capability'  => 'edit_theme_options',
    'option_type' => 'theme_mod',
));

Kirki::add_section('hero_text_section', array(
    'title'    => esc_html__('Hero Text Section', 'textdomain'),
    'priority' => 160,
));

Kirki::add_field('my_config', [
    'type'        => 'editor',
    'settings'    => 'hero_text_content',
    'label'       => esc_html__('Hero Text Content', 'textdomain'),
    'section'     => 'hero_text_section',
    'default'     => '<h2>Hello <strong>world!</strong></h2>',
    'choices'     => [
        'rows'           => 10,
        'toolbar'        => 'full',
        'media_buttons'  => false,
    ],
]);


function mm_customize_register($wp_customize)
{
    $wp_customize->add_section('hero_section', [
        'title' => __('Hero Section', 'Mathmozo'),
        'priority' => 30,
    ]);

    // Small Logo
    $wp_customize->add_setting('small_logo', [
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
    ]);

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'small_logo', [
        'label' => __('Small Logo', 'Mathmozo'),
        'section' => 'title_tagline', // Or use your desired section
        'settings' => 'small_logo',
    ]));

    // Medium Logo
    $wp_customize->add_setting('medium_logo', [
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
    ]);

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'medium_logo', [
        'label' => __('Medium Logo', 'Mathmozo'),
        'section' => 'title_tagline', // Or use your desired section
        'settings' => 'medium_logo',
    ]));

    // Large Logo
    $wp_customize->add_setting('large_logo', [
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
    ]);

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'large_logo', [
        'label' => __('Large Logo', 'Mathmozo'),
        'section' => 'title_tagline', // Or use your desired section
        'settings' => 'large_logo',
    ]));

    $wp_customize->add_setting('hero_image', [
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
    ]);

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'hero_image', [
        'label' => __('Hero Image', 'Mathmozo'),
        'section' => 'hero_section',
        'settings' => 'hero_image',
    ]));

    // Section
    $wp_customize->add_section('hero_text_section', array(
        'title' => __('Hero Section Text', 'mm'),
        'priority' => 30,
    ));

    // Textarea for custom HTML (like <span>)
    // $wp_customize->add_setting('hero_text_content', array(
    //     'default' => 'Welcome to <span style="color:#ff0;">My Website</span>',
    //     'sanitize_callback' => 'wp_kses_post', // Allows safe HTML like <span>
    // ));

    // $wp_customize->add_control('hero_text_content', array(
    //     'label' => __('Hero Text Content (HTML allowed)', 'mm'),
    //     'section' => 'hero_text_section',
    //     'type' => 'textarea',
    // ));


    // Hero Extra Classes (line-height, text-shadow, width, etc.)
    $wp_customize->add_setting('hero_extra_classes', array(
        'default' => 'lh-base text-shadow-lg w-75', // Example default
        'sanitize_callback' => 'sanitize_text_field',
    ));

    $wp_customize->add_control('hero_extra_classes', array(
        'label' => __('Hero Extra Classes (Line-height, Text-shadow, Width etc.)', 'mm'),
        'section' => 'hero_text_section',
        'type' => 'text',
    ));

    // Section
    $wp_customize->add_section('mm_custom_header_text', array(
        'title' => __('Custom Header Text', 'mm'),
        'priority' => 30,
    ));

    // Line 1
    $wp_customize->add_setting('mm_header_text_line1', array(
        'default' => 'Personal',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('mm_header_text_line1', array(
        'label' => __('Header Line 1', 'mm'),
        'section' => 'mm_custom_header_text',
        'type' => 'text',
    ));

    // Line 2
    $wp_customize->add_setting('mm_header_text_line2', array(
        'default' => 'Empowerment',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('mm_header_text_line2', array(
        'label' => __('Header Line 2', 'mm'),
        'section' => 'mm_custom_header_text',
        'type' => 'text',
    ));

    // Line 3
    $wp_customize->add_setting('mm_header_text_line3', array(
        'default' => 'Teams, Inc.',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('mm_header_text_line3', array(
        'label' => __('Header Line 3', 'mm'),
        'section' => 'mm_custom_header_text',
        'type' => 'text',
    ));
}
add_action('customize_register', 'mm_customize_register');

function custom_theme_customizer($wp_customize)
{
    // Add Section for Social Media Links
    $wp_customize->add_section('social_media_section', array(
        'title'       => __('Social Media Links', 'mm'),
        'description' => __('Manage your social media links and upload icons.', 'mm'),
        'priority'    => 30,
    ));

    // Add Settings for Social Media URLs
    $wp_customize->add_setting('facebook_url', array(
        'default'   => '',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('facebook_url', array(
        'label'   => __('Facebook URL', 'mm'),
        'section' => 'social_media_section',
        'type'    => 'url',
    ));

    $wp_customize->add_setting('twitter_url', array(
        'default'   => '',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('twitter_url', array(
        'label'   => __('Twitter (X) URL', 'mm'),
        'section' => 'social_media_section',
        'type'    => 'url',
    ));

    $wp_customize->add_setting('instagram_url', array(
        'default'   => '',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('instagram_url', array(
        'label'   => __('Instagram URL', 'mm'),
        'section' => 'social_media_section',
        'type'    => 'url',
    ));

    $wp_customize->add_setting('meetup_url', array(
        'default'   => '',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('meetup_url', array(
        'label'   => __('Meetup URL', 'mm'),
        'section' => 'social_media_section',
        'type'    => 'url',
    ));

    $wp_customize->add_setting('linkedin_url', array(
        'default'   => '',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('linkedin_url', array(
        'label'   => __('LinkedIn URL', 'mm'),
        'section' => 'social_media_section',
        'type'    => 'url',
    ));

    $wp_customize->add_setting('youtube_url', array(
        'default'   => '',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('youtube_url', array(
        'label'   => __('YouTube URL', 'mm'),
        'section' => 'social_media_section',
        'type'    => 'url',
    ));

    // Add Settings for Social Media Icons (SVG file upload)
    $wp_customize->add_setting('facebook_icon', array(
        'default'   => '',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'facebook_icon', array(
        'label'   => __('Upload Facebook Icon (SVG)', 'mm'),
        'section' => 'social_media_section',
        'mime_type' => 'image/svg+xml',
    )));

    $wp_customize->add_setting('twitter_icon', array(
        'default'   => '',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'twitter_icon', array(
        'label'   => __('Upload Twitter (X) Icon (SVG)', 'mm'),
        'section' => 'social_media_section',
        'mime_type' => 'image/svg+xml',
    )));

    $wp_customize->add_setting('instagram_icon', array(
        'default'   => '',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'instagram_icon', array(
        'label'   => __('Upload Instagram Icon (SVG)', 'mm'),
        'section' => 'social_media_section',
        'mime_type' => 'image/svg+xml',
    )));

    $wp_customize->add_setting('meetup_icon', array(
        'default'   => '',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'meetup_icon', array(
        'label'   => __('Upload Meetup Icon (SVG)', 'mm'),
        'section' => 'social_media_section',
        'mime_type' => 'image/svg+xml',
    )));

    $wp_customize->add_setting('linkedin_icon', array(
        'default'   => '',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'linkedin_icon', array(
        'label'   => __('Upload LinkedIn Icon (SVG)', 'mm'),
        'section' => 'social_media_section',
        'mime_type' => 'image/svg+xml',
    )));

    $wp_customize->add_setting('youtube_icon', array(
        'default'   => '',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'youtube_icon', array(
        'label'   => __('Upload YouTube Icon (SVG)', 'mm'),
        'section' => 'social_media_section',
        'mime_type' => 'image/svg+xml',
    )));
}
add_action('customize_register', 'custom_theme_customizer');


function mm_mime_types($mimes)
{
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'mm_mime_types');



add_filter('theme_page_templates', function ($templates) {
    $template_dir = get_template_directory() . '/template-custom/';
    $template_files = glob($template_dir . '**/*.php');

    foreach ($template_files as $file) {
        $headers = get_file_data($file, [
            'Template Name' => 'Template Name',
            'Template Post Type' => 'Template Post Type',
        ]);

        if (!empty($headers['Template Name'])) {
            $relative_path = str_replace(get_template_directory() . '/', '', $file);
            $templates[$relative_path] = $headers['Template Name'];
        }
    }


    return $templates;
});

// add_filter('template_include', function ($template) {
//     $page_template = get_page_template_slug();
//     if ($page_template && file_exists(get_theme_file_path($page_template))) {
//         return get_theme_file_path($page_template);
//     }


//     $user_slug = get_query_var('user_profile');

//     if ($user_slug) {
//         $reserved_slugs = ['wp-admin', 'signin', 'signup', 'subscribe', 'lost-password', 'register', 'logout', 'my-account', 'dashboard', 'modify-profile', 'modify-links', 'chatgpt', 'faqs', 'video-library', 'disclaimer-empowerment-coaching', 'privacy-policy-247-empowerment', 'terms-of-use-empowerment'];

//         if (in_array($user_slug, $reserved_slugs)) {
//             return $template;
//         }

//         if (get_page_by_path($user_slug)) {
//             return $template;
//         }

//         $user = get_user_by('slug', $user_slug);
//         if ($user) {
//             $custom_template = get_theme_file_path('template-custom/auth/user-profile.php');
//             if (file_exists($custom_template)) {
//                 return $custom_template;
//             }
//         }
//     }

//     if (is_front_page() && is_user_logged_in()) {
//         // Admins → redirect to dashboard
//         if (current_user_can('administrator')) {
//             wp_redirect(home_url('/dashboard'));
//             exit;
//         }

//         // Normal users → show custom home
//         $custom_home = get_theme_file_path('template-custom/auth/home.php');
//         if (file_exists($custom_home)) {
//             return $custom_home;
//         }
//     }

//     $custom_page = get_query_var('custom_page');

//     if ($custom_page === 'report') {
//         return get_template_directory() . '/template-custom/auth/report.php';
//     }

//     if ($custom_page === 'suggestion') {
//         return get_template_directory() . '/template-custom/auth/suggestion.php';
//     }

//     return $template;
// });

add_filter('template_include', function ($template) {
    // Handle custom_page
    $custom_page = get_query_var('custom_page');
    if ($custom_page === 'report') {
        return get_template_directory() . '/template-custom/auth/report.php';
    }
    if ($custom_page === 'suggestion') {
        return get_template_directory() . '/template-custom/auth/suggestion.php';
    }

    // Handle user_profile
    $user_slug = get_query_var('user_profile');

    if (!$user_slug) return $template;

    // Skip if it's an existing page or post
    if (get_page_by_path($user_slug)) return $template;
    if (get_posts(['name' => $user_slug, 'post_type' => 'any'])) return $template;

    // Try to load user profile
    $user = get_user_by('slug', $user_slug);
    if ($user) {
        $custom_template = get_theme_file_path('template-custom/auth/user-profile.php');
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }

    return $template;
});



add_filter('show_admin_bar', '__return_false');


// add_action('init', function () {
//     add_rewrite_tag('%custom_page%', '([^&]+)');
//     add_rewrite_rule('^report/?$', 'index.php?custom_page=report', 'top');
//     add_rewrite_rule('^suggestion/?$', 'index.php?custom_page=suggestion', 'top');
// });

// add_filter('query_vars', function ($vars) {
//     $vars[] = 'custom_page';
//     return $vars;
// });

add_action('wp_ajax_chatgpt_ajax_handler', 'chatgpt_ajax_handler');
function chatgpt_ajax_handler()
{
    $body = json_decode(file_get_contents('php://input'), true);
    $message = sanitize_text_field($body['message']);

    $apiKey = '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'; // Replace with your actual key

    $postData = [
        "model" => "openai/gpt-3.5-turbo", // Important: include the provider prefix (as shown in your Postman result)
        "messages" => [
            ["role" => "user", "content" => $message]
        ]
    ];

    $response = wp_remote_post("https://openrouter.ai/api/v1/chat/completions", [
        "headers" => [
            "Authorization" => "Bearer $apiKey",
            "Content-Type" => "application/json",
            "HTTP-Referer" => home_url(), // OpenRouter requires Referer
            "X-Title" => "My WP Chatbot", // Optional
        ],
        "body" => json_encode($postData),
    ]);

    if (is_wp_error($response)) {
        wp_send_json(['reply' => '⚠️ Could not reach OpenRouter.']);
    }

    $response_body = wp_remote_retrieve_body($response);
    error_log("OpenRouter RESPONSE: $response_body");

    $json = json_decode($response_body, true);
    $reply = $json['choices'][0]['message']['content'] ?? null;

    if ($reply) {
        wp_send_json(['reply' => trim($reply)]);
    } else {
        wp_send_json(['reply' => '⚠️ AI response was empty or malformed.']);
    }
}

// functions.php
// OG tags for normal posts/pages
function add_open_graph_tags()
{
    if (is_singular()) {
        global $post;

        $title = get_the_title($post->ID);
        $description = get_the_excerpt($post->ID);
        $url = get_permalink($post->ID);

        $image = get_the_post_thumbnail_url($post->ID, 'full');
        if (!$image) {
            $image = get_template_directory_uri() . '/assets/img/default-og.jpg';
        }

        echo '
            <meta property="og:title" content="' . esc_attr($title) . '" />
            <meta property="og:description" content="' . esc_attr($description) . '" />
            <meta property="og:type" content="article" />
            <meta property="og:url" content="' . esc_url($url) . '" />
            <meta property="og:image" content="' . esc_url($image) . '" />
            <meta name="twitter:card" content="summary_large_image" />
            <meta name="twitter:title" content="' . esc_attr($title) . '" />
            <meta name="twitter:description" content="' . esc_attr($description) . '" />
            <meta name="twitter:image" content="' . esc_url($image) . '" />
        ';
    }
}

// OG tags for course pages
function output_course_og_tags()
{
    $course_slug = get_query_var('course_slug');
    $store_user = get_query_var('store_user');
    $is_shareable = get_query_var('shareable');

    if ($course_slug && $store_user) {
        $course = get_page_by_path($course_slug, OBJECT, 'course');
        if (!$course) {
            return;
        }

        $title = get_the_title($course);
        $description = get_field('short_details', $course->ID) ?: wp_trim_words($course->post_content, 30);
        $image = get_the_post_thumbnail_url($course->ID, 'large') ?: get_template_directory_uri() . '/img/banner.jpg';

        // Use shareable URL if on shareable page
        $url = $is_shareable
            ? home_url("/{$store_user}/store/{$course_slug}/shareable/")
            : home_url("/{$store_user}/store/{$course_slug}/");

        echo '
            <meta property="og:title" content="' . esc_attr($title) . '" />
            <meta property="og:description" content="' . esc_attr($description) . '" />
            <meta property="og:image" content="' . esc_url($image) . '" />
            <meta property="og:url" content="' . esc_url($url) . '" />
            <meta property="og:type" content="website" />
            <meta name="twitter:card" content="summary_large_image" />
            <meta name="twitter:title" content="' . esc_attr($title) . '" />
            <meta name="twitter:description" content="' . esc_attr($description) . '" />
            <meta name="twitter:image" content="' . esc_url($image) . '" />
        ';
    }
}
add_action('wp_head', 'output_course_og_tags');


// The main function deciding which OG tags to output
function output_appropriate_og_tags()
{
    $course_slug = get_query_var('course_slug');
    $store_user = get_query_var('store_user');
    $shareable = get_query_var('shareable');

    // If this is the minimal shareable template (URL ends with /shareable or has shareable=1)
    if ($shareable && $course_slug && $store_user) {
        output_course_og_tags();
    } elseif ($course_slug && $store_user) {
        output_course_og_tags();
    } else {
        add_open_graph_tags();
    }
}
add_action('wp_head', 'output_appropriate_og_tags');


// Register the 'shareable' query var
function custom_query_vars($vars)
{
    $vars[] = 'shareable';
    return $vars;
}
add_filter('query_vars', 'custom_query_vars');


// Add rewrite rule so /{store_user}/store/{course_slug}/shareable/ maps to query vars
function custom_store_shareable_rewrite_rule()
{
    add_rewrite_rule(
        '^([^/]+)/store/([^/]+)/shareable/?$', // URL pattern: {store_user}/store/{course_slug}/shareable
        'index.php?store_user=$matches[1]&course_slug=$matches[2]&shareable=1', // query vars
        'top'
    );
}
add_action('init', 'custom_store_shareable_rewrite_rule');

/**
 * Summary of give_referral_commission
 * @param mixed $buyer_user_id
 * @param mixed $amount
 * @return bool
 */
function give_referral_commission($buyer_id, $course_price, $course_id)
{
    $referrer_username = get_user_meta($buyer_id, 'referrer', true);

    if (!$referrer_username || $referrer_username == $buyer_id) {
        return;
    }

    $referrer_user = get_user_by('login', $referrer_username);
    if (!$referrer_user) {
        return;
    }

    $referrer_id = $referrer_user->ID;

    // 💸 Commission logic
    $commission_percent = 10;
    $commission_amount = round(($course_price * $commission_percent) / 100, 2);

    if ($commission_amount <= 0) {
        return;
    }

    // 🪙 Update referrer's wallet
    $current_balance = (float) get_user_meta($referrer_id, 'referral_commission', true);
    update_user_meta($referrer_id, 'referral_commission', $current_balance + $commission_amount);

    // 📝 Add user log
    $logs = get_user_meta($referrer_id, 'referral_logs', true);
    $logs = is_array($logs) ? $logs : [];
    $logs[] = [
        'referred_user_id' => $buyer_id,
        'amount' => $commission_amount,
        'earned_for' => $course_id ? get_the_title($course_id) : 'Unknown Course',
        'earned_for_id' => $course_id,
        'date' => current_time('mysql')
    ];
    update_user_meta($referrer_id, 'referral_logs', $logs);

    // 🔁 Global log for admin
    $global_logs = get_option('referral_commission_global_log', []);
    $global_logs[] = [
        'referrer_id' => $referrer_id,
        'buyer_id' => $buyer_id,
        'amount' => $commission_amount,
        'course_price' => $course_price,
        'date' => current_time('mysql')
    ];
    update_option('referral_commission_global_log', $global_logs);
}
add_action('wp_ajax_load_more_referrals', 'load_more_referrals_callback');
add_action('wp_ajax_nopriv_load_more_referrals', 'load_more_referrals_callback');

function load_more_referrals_callback()
{
    $user_id = isset($_GET['user']) ? intval($_GET['user']) : 0;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    $limit = 40;

    if (!$user_id) {
        wp_send_json_error('Invalid user ID');
    }

    $user = get_user_by('id', $user_id);
    if (!$user) {
        wp_send_json_error('User not found');
    }

    $profileData = new UserProfileData($user);
    $referrals = $profileData::getReferredUsersBy($user);

    // Slice the referrals array from offset with limit
    $slice = array_slice($referrals, $offset, $limit);

    if (empty($slice)) {
        wp_send_json_success(''); // No more data
    }

    $html = '';

    foreach ($slice as $ref_user) {
        $ref_user = is_array($ref_user) ? (object) $ref_user : $ref_user;

        $ref_id = isset($ref_user->id) ? $ref_user->id : 0;
        $ref_email = isset($ref_user->email) ? trim($ref_user->email) : '';
        $ref_login = isset($ref_user->username) ? $ref_user->username : '';
        $ref_registered = isset($ref_user->user_registered) ? $ref_user->user_registered : '';

        $photo = get_user_meta($ref_id, 'profile_photo', true);
        $photo = $photo ?: 'https://www.gravatar.com/avatar/' . md5(strtolower($ref_email)) . '?s=150&d=mm';
        $profile_url = site_url('/' . $ref_login);
        $join_date = $ref_registered ? date('F j, Y', strtotime($ref_registered)) : '—';

        $first_name = isset($ref_user->first_name) ? $ref_user->first_name : '';
        $last_name = isset($ref_user->last_name) ? $ref_user->last_name : '';

        $html .= '
            <div class="col referral-card">
                <a href="' . esc_url($profile_url) . '" class="text-dark text-decoration-none">
                    <div class="h-100 text-center card">
                        <img src="' . esc_url($photo) . '" class="card-img-top mx-auto mt-3 rounded-circle" alt="' . esc_attr($ref_user->display_name ?? '') . '" style="width: 100px; height: 100px; object-fit: cover;">
                        <div class="card-body">
                            <h6 class="card-title">' . esc_html($first_name . ' ' . $last_name) . '</h6>
                            <p class="text-muted text-small card-text">
                                <small>' . esc_html($join_date) . '</small>
                            </p>
                        </div>
                    </div>
                </a>
            </div>
        ';
    }

    wp_send_json_success($html);
}
add_action('wp_ajax_load_more_referrals', 'load_more_referrals_callback');
add_action('wp_ajax_nopriv_load_more_referrals', 'load_more_referrals_callback');

function custom_support_faq_video_routes()
{
    add_rewrite_rule('^support-teams/?$', 'index.php?custom_page=support-teams', 'top');
    add_rewrite_rule('^faqs/?$', 'index.php?custom_page=faqs', 'top');
    add_rewrite_rule('^video-library/?$', 'index.php?custom_page=video-library', 'top');
}
add_action('init', 'custom_support_faq_video_routes');

function add_custom_query_var($vars)
{
    $vars[] = 'custom_page';
    return $vars;
}
add_filter('query_vars', 'add_custom_query_var');

function load_custom_template()
{
    $custom_page = get_query_var('custom_page');
    if ($custom_page == 'support-teams') {
        include get_template_directory() . '/template-custom/auth/support-teams.php';
        exit;
    } elseif ($custom_page == 'faqs') {
        include get_template_directory() . '/template-custom/auth/support-faqs.php';
        exit;
    } elseif ($custom_page == 'video-library') {
        include get_template_directory() . '/template-custom/auth/support-video-library.php';
        exit;
    }
}
add_action('template_redirect', 'load_custom_template');


add_action('admin_init', function() {
    register_setting('general', 'default_referrer_username', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'admin',
    ]);

    add_settings_field(
        'default_referrer_username',
        'Default Referrer Username',
        function() {
            $value = get_option('default_referrer_username', '');
            echo '<input type="text" name="default_referrer_username" value="'.esc_attr($value).'" class="regular-text">';
        },
        'general'
    );
});



require_once get_template_directory() . '/inc/UserProfileData.php';
require_once get_template_directory() . '/inc/Notifications.php';
require_once get_template_directory() . '/inc/UserConnectionManager.php';
require_once get_template_directory() . '/more_functions/authentication.php';
require_once get_template_directory() . '/more_functions/profile.php';
require_once get_template_directory() . '/more_functions/store.php';
require_once get_template_directory() . '/more_functions/event.php';
require_once get_template_directory() . '/more_functions/issues.php';
