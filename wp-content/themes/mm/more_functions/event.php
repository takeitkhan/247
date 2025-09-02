<?php

/**
 * ===============================
 * EVENT POST TYPE + TAXONOMY
 * ===============================
 */
function register_event_post_type()
{
    $labels = [
        'name'          => 'Events',
        'singular_name' => 'Event',
        'menu_name'     => 'Events',
    ];

    $args = [
        'labels'        => $labels,
        'public'        => true,
        'has_archive'   => true,
        'rewrite'       => ['slug' => 'events'],
        'supports'      => ['title', 'editor', 'thumbnail'],
        'menu_icon'     => 'dashicons-calendar-alt',
        'show_in_rest'  => true,
        'capability_type'    => 'post',
        'map_meta_cap'       => true,
    ];

    register_post_type('event', $args);
}
add_action('init', 'register_event_post_type');

function add_event_caps() {
    $roles = ['administrator']; // you can add other roles too
    foreach ($roles as $role_name) {
        $role = get_role($role_name);
        $role->add_cap('edit_event');
        $role->add_cap('read_event');
        $role->add_cap('delete_event');
        $role->add_cap('edit_events');
        $role->add_cap('edit_others_events');
        $role->add_cap('publish_events');
        $role->add_cap('read_private_events');
        $role->add_cap('delete_events');
        $role->add_cap('delete_private_events');
        $role->add_cap('delete_published_events');
        $role->add_cap('delete_others_events');
        $role->add_cap('edit_private_events');
        $role->add_cap('edit_published_events');
    }
}
add_action('admin_init', 'add_event_caps');


function register_event_category_taxonomy()
{
    register_taxonomy('event_category', 'event', [
        'labels' => [
            'name'          => 'Event Categories',
            'singular_name' => 'Event Category',
        ],
        'public'       => true,
        'hierarchical' => true,
        'rewrite'      => ['slug' => 'event-category'],
        'show_in_rest' => true,
    ]);
}
add_action('init', 'register_event_category_taxonomy');

function add_custom_event_rewrite_rule()
{
    // Single event page: /username/event/event-slug/
    add_rewrite_rule(
        '^([^/]+)/event/([^/]+)/?$',
        'index.php?event_user=$matches[1]&event_slug=$matches[2]',
        'top'
    );


    // User's event list: /username/events/
    add_rewrite_rule('^([^/]+)/events/?$', 'index.php?event_user=$matches[1]', 'top');
}
add_action('init', 'add_custom_event_rewrite_rule');

function add_event_caps_to_subscribers() {
    $role = get_role('subscriber');
    if ($role) {
        $role->add_cap('read');
        $role->add_cap('read_event');
        $role->add_cap('edit_event');
        $role->add_cap('edit_events');
        $role->add_cap('edit_published_events');
        $role->add_cap('publish_events');
        $role->add_cap('delete_event');
        $role->add_cap('delete_published_events');
    }
}
add_action('init', 'add_event_caps_to_subscribers');


function event_submission_form() {
    if (!is_user_logged_in()) {
        echo '<p>You must be logged in to submit an event.</p>';
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_title'])) {
        if (!isset($_POST['event_submission_nonce']) || !wp_verify_nonce($_POST['event_submission_nonce'], 'event_submission')) {
            echo '<p class="text-danger">Nonce verification failed!</p>';
        } else {
            $event_id = wp_insert_post([
                'post_title'   => sanitize_text_field($_POST['event_title']),
                'post_content' => wp_kses_post($_POST['event_content']),
                'post_type'    => 'event',
                'post_status'  => 'publish',
                'post_author'  => get_current_user_id(),
            ]);

            if ($event_id) {
                echo '<p class="text-success">✅ Event posted successfully!</p>';
            } else {
                echo '<p class="text-danger">Failed to post the event.</p>';
            }
        }
    }
    ?>
    <form method="post" action="">
        <?php wp_nonce_field('event_submission', 'event_submission_nonce'); ?>
        <p>
            <label for="event_title">Event Title</label><br>
            <input type="text" name="event_title" required>
        </p>
        <p>
            <label for="event_content">Event Details</label><br>
            <textarea name="event_content" rows="5"></textarea>
        </p>
        <p>
            <button type="submit">Submit Event</button>
        </p>
    </form>
    <?php
}
add_shortcode('event_submission_form', 'event_submission_form');



function add_event_query_vars($vars)
{
    $vars[] = 'event_user';
    $vars[] = 'event_slug';
    return $vars;
}
add_filter('query_vars', 'add_event_query_vars');

function load_event_template($template)
{
    $event_user = get_query_var('event_user'); // user identifier
    $event_slug = get_query_var('event_slug');

    if ($event_user && $event_slug) {
        // Single event page template
        return get_template_directory() . '/template-custom/auth/single-event-template.php';
    } elseif ($event_user) {
        // User-specific event list page
        return get_template_directory() . '/template-custom/auth/event-template.php';
    }

    return $template;
}
add_filter('template_include', 'load_event_template');

/**
 * ===============================
 * ADMIN ACTIONS
 * ===============================
 */
add_filter('post_row_actions', function ($actions, $post) {
    if ($post->post_type === 'event') {
        $current_user = wp_get_current_user();
        $store_user = $current_user->user_login ?: 'joseph';

        $custom_url = home_url("/{$store_user}/event/{$post->post_name}/");

        if (isset($actions['view'])) {
            $actions['view'] = '<a href="' . esc_url($custom_url) . '" target="_blank" rel="noopener noreferrer">View</a>';
        }
    }
    return $actions;
}, 10, 2);
