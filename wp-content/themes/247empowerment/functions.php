<?php

// ============================================
// Load Payout System Classes
// ============================================
require_once get_template_directory() . '/inc/PayoutSystem.php';
require_once get_template_directory() . '/inc/PayPalAPI.php';
require_once get_template_directory() . '/inc/PayoutNotifications.php';
require_once get_template_directory() . '/inc/payout-balance.php';

// ============================================
// Load Phase 1, 2, 3 Enhanced Posting Features
// ============================================
require_once get_template_directory() . '/inc/database-migration.php';
require_once get_template_directory() . '/inc/status-indicators.php';

// Activation hook - for theme activation
register_activation_hook(__FILE__, ['PayoutSystem', 'activate']);

// Ensure tables exist on every page load (fallback)
add_action('init', function() {
    static $tables_checked = false;
    if (!$tables_checked) {
        PayoutSystem::activate();
        $tables_checked = true;
    }
}, 1); // Run early

// Initialize Payout System
if (is_admin() || is_user_logged_in()) {
    new PayoutSystem();
    new PayPalAPI();
    new PayoutNotifications();
}

add_action('init', function () {
    if (!session_id()) {
        session_start();
    }
    
    // Disable WordPress emoji processing - use SVG icons instead
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
});

function mm_theme_setup()
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
}
add_action('after_setup_theme', 'mm_theme_setup');

function mm_enqueue_assets()
{
    // CSS
    wp_enqueue_style('glightbox-css', get_template_directory_uri() . '/assets/vendor/glightbox/css/glightbox.min.css', [], '3.2.0');
    wp_enqueue_style('aos-css', get_template_directory_uri() . '/assets/vendor/aos/aos.css', [], '2.3.4');
    wp_enqueue_style('output', get_template_directory_uri() . '/assets/css/output.css', [], filemtime(get_template_directory() . '/assets/css/output.css'));
    wp_enqueue_style('mm-style', get_stylesheet_uri(), [], filemtime(get_stylesheet_directory() . '/style.css'));

    // Add inline styles for comments and reactions
    $comment_styles = '
        /* Modern Comment Styles */
        .comment-item {
            transition: all 0.3s ease;
        }

        .comment-bubble {
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            transition: box-shadow 0.2s ease;
        }

        .comment-item:hover .comment-bubble {
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
        }

        .comment-like-btn,
        .reply-btn,
        .comment-options-btn {
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .comment-like-btn:hover,
        .reply-btn:hover {
            color: #0a66c2 !important;
            text-decoration: none;
        }

        .comment-like-btn:hover i,
        .reply-btn:hover i {
            transform: scale(1.1);
            transition: transform 0.2s ease;
        }

        .comment-options-btn:hover {
            color: #65676b !important;
        }

        .comment-reply-input {
            transition: all 0.2s ease;
            border-color: #e0e0e0;
            font-weight: 500;
        }

        .comment-reply-input:focus {
            border-color: #0a66c2;
            box-shadow: 0 0 0 0.2rem rgba(10, 102, 194, 0.15);
        }

        .emoji-icon {
            transition: all 0.2s ease;
        }

        .emoji-icon:hover {
            opacity: 0.8;
            transform: scale(1.1);
        }

        /* Nested comment styling */
        .comment-item[style*="margin-left"] {
            position: relative;
            padding-left: 12px;
            border-left: 2px solid #f0f2f5;
        }

        .comment-item[style*="margin-left"]:before {
            content: "";
            position: absolute;
            left: -2px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, #0a66c2, #f0f2f5);
            transition: all 0.3s ease;
        }

        .comment-item[style*="margin-left"]:hover:before {
            background: linear-gradient(to bottom, #0a66c2, #0a66c2);
            width: 3px;
            left: -3px;
        }

        /* Reply input animation */
        .reply-input-container {
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Comment action buttons group */
        .comment-item .d-flex:has(.comment-like-btn) {
            gap: 16px;
            margin-top: 6px;
        }

        /* Improved typography */
        .comment-bubble p {
            word-break: break-word;
            white-space: pre-wrap;
        }

        /* Reaction and Comment Stats */
        .post-stats {
            display: flex;
            gap: 16px;
            padding: 8px 12px !important;
            border-bottom: 1px solid #e0e0e0;
        }

        .reaction-stat,
        .comment-stat {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #65676b;
            cursor: pointer;
        }

        .reaction-stat:hover,
        .comment-stat:hover {
            color: #0a66c2;
            text-decoration: none;
        }

        /* Post Action Buttons */
        .post-actions {
            padding: 8px 0 !important;
        }

        .post-actions > div {
            margin-bottom: 6px;
        }

        .post-actions > div:last-child {
            margin-bottom: 0;
        }

        .post-actions .btn-link {
            padding: 6px 4px !important;
            border-radius: 4px;
            transition: all 0.2s ease;
            color: #65676b !important;
            text-decoration: none !important;
            font-size: 12px;
            font-weight: 500;
            display: flex !important;
            align-items: center;
            justify-content: center;
            gap: 4px;
            white-space: nowrap;
            flex: 1;
            min-height: 32px;
        }

        .post-actions .btn-link:hover {
            background-color: #f0f2f5;
            color: #0a66c2 !important;
        }

        .post-actions .btn-link img {
            max-width: 14px;
            height: auto;
            transition: transform 0.2s ease;
        }

        .post-actions .btn-link:hover img {
            transform: scale(1.1);
        }

        /* Reaction buttons active state */
        .reaction-btn.active {
            background-color: #e7f3ff;
            color: #0a66c2 !important;
        }

        .comment-input {
            transition: all 0.2s ease;
            border-color: #e0e0e0;
            font-weight: 500;
        }

        .comment-input:focus {
            border-color: #0a66c2;
            box-shadow: 0 0 0 0.2rem rgba(10, 102, 194, 0.15);
            outline: none;
        }

        /* SweetAlert2 compact styling */
        .swal2-popup.swal-compact {
            font-size: 13px;
            border-radius: 10px;
        }

        .swal2-popup.swal-compact .swal2-title {
            font-size: 16px;
            margin: 0 0 6px;
        }

        .swal2-popup.swal-compact .swal2-html-container {
            font-size: 13px;
            margin: 4px 0 0;
        }

        .swal2-popup.swal-compact .swal2-actions {
            margin-top: 10px;
        }

        .swal2-popup.swal-compact-edit {
            padding: 8px 10px !important;
        }

        .swal2-popup.swal-compact-edit .swal2-input,
        .swal2-popup.swal-compact-edit textarea {
            margin: 6px 0 4px !important;
            padding: 6px 8px !important;
            font-size: 13px !important;
        }

        .border-underline {
            border-top: 1px solid #e0e0e0 !important;
            margin-top: 10px !important;
            margin-bottom: 10px !important;
        }
    ';
    wp_add_inline_style('mm-style', $comment_styles);

    // JS
    wp_enqueue_script('purecounter-js', get_template_directory_uri() . '/assets/vendor/purecounter/purecounter_vanilla.js', [], '1.5.0', true);
    wp_enqueue_script('aos-js', get_template_directory_uri() . '/assets/vendor/aos/aos.js', [], '2.3.4', true);
    wp_enqueue_script('glightbox-js', get_template_directory_uri() . '/assets/vendor/glightbox/js/glightbox.min.js', [], '3.2.0', true);
    wp_enqueue_script('bootstrap-js', get_template_directory_uri() . '/assets/js/bootstrap.bundle.min.js', ['jquery'], '5.3.3', true);

    // main.js
    wp_enqueue_script(
        'mm-main-js',
        get_template_directory_uri() . '/assets/js/main.js',
        ['jquery', 'purecounter-js', 'aos-js', 'glightbox-js'],
        filemtime(get_template_directory() . '/assets/js/main.js'),
        true
    );

    // Pass template directory to JS
    wp_localize_script('mm-main-js', 'themeData', [
        'dir' => get_template_directory_uri(),
    ]);

    // ============================================
    // PHASE 1: Modern Posting Modal Assets
    // ============================================
    
    // Phase 1 Modal CSS
    wp_enqueue_style(
        'phase1-modal-css',
        get_template_directory_uri() . '/template-custom/auth/profile-parts/modal-design.css',
        [],
        filemtime(get_template_directory() . '/template-custom/auth/profile-parts/modal-design.css')
    );

    // Emoji Picker Library (lightweight emoji picker)
    wp_enqueue_script(
        'emoji-picker-element',
        'https://cdn.jsdelivr.net/npm/emoji-picker-element@1.0.0',
        [],
        '1.0.0',
        true
    );

    // Phase 1 Modal JavaScript Handler
    wp_enqueue_script(
        'phase1-modal-handler-js',
        get_template_directory_uri() . '/template-custom/auth/profile-parts/modal-handler.js',
        ['jquery', 'emoji-picker-element'],
        filemtime(get_template_directory() . '/template-custom/auth/profile-parts/modal-handler.js'),
        true
    );

    // Pass AJAX URL to JavaScript
    wp_localize_script('phase1-modal-handler-js', 'ajaxurl', admin_url('admin-ajax.php'));
}
add_action('wp_enqueue_scripts', 'mm_enqueue_assets');


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

// Utility functions
function get_header_based_on_login()
{
    get_header(is_user_logged_in() ? 'portal' : 'main');
}

function get_footer_based_on_login()
{
    get_footer(is_user_logged_in() ? 'portal' : 'main');
}


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

    // Section for Custom Subline (you can reuse existing section or create new one)
    $wp_customize->add_section('mm_custom_subline_section', [
        'title'    => __('Custom Subline', 'mm'),
        'priority' => 31, // after your header section
    ]);

    // New Subline Setting
    $wp_customize->add_setting('mm_custom_subline', [
        'default'           => 'Your subline goes here',
        'sanitize_callback' => 'sanitize_text_field',
    ]);

    // Control for Subline
    $wp_customize->add_control('mm_custom_subline', [
        'label'   => __('Subline Text', 'mm'),
        'section' => 'mm_custom_subline_section',
        'type'    => 'text',
    ]);

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

// Register rewrite rules
add_action('init', function () {
    add_rewrite_rule('^report/?$', 'index.php?custom_page=report', 'top');
    add_rewrite_rule('^suggestion/?$', 'index.php?custom_page=suggestion', 'top');
});

// Register query var
add_filter('query_vars', function ($vars) {
    $vars[] = 'custom_page';
    $vars[] = 'user_profile';
    return $vars;
});

add_action('init', function () {
    add_rewrite_rule(
        '^profile/([^/]+)/?$',
        'index.php?user_profile=$matches[1]',
        'top'
    );
});

add_filter('template_include', function ($template) {

    // Handle custom_page
    $custom_page = get_query_var('custom_page');
    if ($custom_page === 'report') {
        return get_theme_file_path('template-custom/auth/report.php');
    }
    if ($custom_page === 'suggestion') {
        return get_theme_file_path('template-custom/auth/suggestion.php');
    }

    // Handle user profile
    $user_slug = get_query_var('user_profile');
    if (!$user_slug) {
        return $template;
    }

    // Let real pages/posts win
    if (get_page_by_path($user_slug, OBJECT, ['page', 'post'])) {
        return $template;
    }

    // Resolve user
    $user = get_user_by('slug', $user_slug);
    if (!$user) {
        $user = get_user_by('login', $user_slug);
    }

    if ($user) {
        return get_theme_file_path('template-custom/auth/user-profile.php');
    }

    return $template;
});

// add_filter('template_include', function ($template) {
//     // Handle custom_page
//     $custom_page = get_query_var('custom_page');
//     if ($custom_page === 'report') {
//         return get_template_directory() . '/template-custom/auth/report.php';
//     }
//     if ($custom_page === 'suggestion') {
//         return get_template_directory() . '/template-custom/auth/suggestion.php';
//     }

//     // Handle user_profile
//     $user_slug = get_query_var('user_profile');

//     if (!$user_slug) return $template;

//     // Skip if slug belongs to an existing post or page (FAST)
//     if (get_page_by_path($user_slug, OBJECT, ['page', 'post'])) {
//         return $template;
//     }

//     if (get_posts(['name' => $user_slug, 'post_type' => 'any'])) return $template;

//     // Try to load user profile
//     $user_slug = get_query_var('user_profile');
//     if (!$user_slug) {
//         return $template;
//     }

//     // Let real content win (fast)
//     if (!is_404()) {
//         return $template;
//     }

//     // Try user lookup
//     $user = get_user_by('slug', $user_slug);
//     if (!$user) {
//         $user = get_user_by('login', $user_slug);
//     }

//     if ($user) {
//         return get_theme_file_path('template-custom/auth/user-profile.php');
//     }

//     return $template;
// });



add_filter('show_admin_bar', '__return_false');

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

add_action('admin_init', function () {
    register_setting('general', 'default_referrer_username', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'admin',
    ]);

    add_settings_field(
        'default_referrer_username',
        'Default Referrer Username',
        function () {
            $value = get_option('default_referrer_username', '');
            echo '<input type="text" name="default_referrer_username" value="' . esc_attr($value) . '" class="regular-text">';
        },
        'general'
    );
});

// Notifications Enqueue
function enqueue_notifications_assets()
{
    if (!is_user_logged_in()) {
        return;
    }

    wp_enqueue_script(
        'notifications',
        get_template_directory_uri() . '/assets/js/notifications.js',
        ['jquery'],
        filemtime(get_template_directory() . '/assets/js/notifications.js'),
        true
    );

    wp_localize_script('notifications', 'notificationsData', [
        'ajaxurl'    => admin_url('admin-ajax.php'),
        'nonce'      => wp_create_nonce('notifications_nonce'),
        'circleIcon' => esc_url(get_template_directory_uri() . '/assets/img/nd/circle-notification.png'),
        'userImg'    => esc_url(get_template_directory_uri() . '/assets/img/loggedin_images/banner.jpg'),
        'activeIcon' => esc_url(get_template_directory_uri() . '/assets/img/nd/active_icon.png'),
        'sound'      => esc_url(get_template_directory_uri() . '/assets/sounds/coin.mp3'),
    ]);
}
add_action('wp_enqueue_scripts', 'enqueue_notifications_assets');

// Enqueue SimpleMDE for Modify-Profile About Me editor
add_action('wp_enqueue_scripts', function () {
    if (!is_user_logged_in()) return;
    // Only enqueue on modify-profile page (adjust slug as needed)
    if (!is_page('modify-profile')) return;

    // SimpleMDE CSS & JS
    wp_enqueue_style('simplemde-css', 'https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.css');
    wp_enqueue_script('simplemde-js', 'https://cdn.jsdelivr.net/simplemde/latest/simplemde.min.js', [], null, true);

    // Inline JS to initialize SimpleMDE on About Me textarea
    $init_js = "
    document.addEventListener('DOMContentLoaded', function() {
        var textarea = document.querySelector('textarea[name=\'about_me\']');
        if (textarea && typeof SimpleMDE !== 'undefined') {
            // Prevent duplicate editors if other scripts try to init
            if (!textarea.dataset.mdeInitialized) {
                new SimpleMDE({ element: textarea });
                textarea.dataset.mdeInitialized = '1';
            }
        }
    });
    ";
    wp_add_inline_script('simplemde-js', $init_js);
});


add_action('wp_ajax_mark_all_notifications_read', 'mark_all_notifications_read');
function mark_all_notifications_read()
{
    check_ajax_referer('notifications_nonce', 'security');

    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
    }

    $user_id = get_current_user_id();
    $notifications = Notifications::getInstance();
    $notifications->markAllAsRead($user_id);

    wp_send_json_success();
}

// Mark single as read
add_action('wp_ajax_mark_notification_read', function () {
    check_ajax_referer('notifications_nonce', 'security');
    $user_id  = get_current_user_id();
    $notif_id = sanitize_text_field($_POST['notification_id'] ?? '');

    $result = Notifications::getInstance()->markAsRead($user_id, $notif_id);

    wp_send_json_success($result);
});

// Clear all notifications
add_action('wp_ajax_clear_all_notifications', function () {
    check_ajax_referer('notifications_nonce', 'security');
    $user_id = get_current_user_id();

    $result = Notifications::getInstance()->clearNotifications($user_id);

    wp_send_json_success($result);
});

// Notifications Enqueue end

add_action('wp_footer', function () {
    if (function_exists('wp_enqueue_block_template_skip_link')) {
        wp_enqueue_block_template_skip_link();
    }
}, 1);


/**
 * Sort users by most recently registered (DESC) in admin
 */
add_action('pre_get_users', function ($query) {

    if (!is_admin()) {
        return;
    }

    global $pagenow;

    if ($pagenow === 'users.php' && empty($_GET['orderby'])) {
        $query->set('orderby', 'registered');
        $query->set('order', 'DESC');
    }
});
/**
 * Add Phone column in users list
 */
add_filter('manage_users_columns', function ($columns) {
    $columns['phone'] = 'Phone';
    return $columns;
});

/**
 * Show Phone value in users list
 */
add_filter('manage_users_custom_column', function ($value, $column_name, $user_id) {

    if ($column_name === 'phone') {
        $phone = get_user_meta($user_id, 'phone', true);
        return $phone ? esc_html($phone) : '—';
    }

    return $value;

}, 10, 3);


require_once get_template_directory() . '/inc/UserProfileData.php';
require_once get_template_directory() . '/inc/Notifications.php';
require_once get_template_directory() . '/inc/UserConnectionManager.php';
require_once get_template_directory() . '/more_functions/walker-menu.php';
require_once get_template_directory() . '/more_functions/walker-menu-v2.php';
require_once get_template_directory() . '/more_functions/authentication.php';

// ============================================
// Withdrawal Shortcode
// ============================================
add_shortcode('withdrawal_form', function() {
    if (!is_user_logged_in()) {
        return '<p style="text-align: center; padding: 20px; background: #f5f5f5; border-radius: 5px;">Please <a href="' . wp_login_url() . '">log in</a> to request a withdrawal.</p>';
    }
    ob_start();
    include get_template_directory() . '/template-custom/frontend/withdrawal-form.php';
    return ob_get_clean();
});

require_once get_template_directory() . '/more_functions/profile.php';
require_once get_template_directory() . '/more_functions/store.php';
require_once get_template_directory() . '/more_functions/event.php';
require_once get_template_directory() . '/more_functions/blog.php';
require_once get_template_directory() . '/more_functions/video.php';
require_once get_template_directory() . '/more_functions/jobs.php';
require_once get_template_directory() . '/more_functions/agreement.php';
require_once get_template_directory() . '/more_functions/issues.php';
require_once get_template_directory() . '/more_functions/paypalsettings.php';
