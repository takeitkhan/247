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

function add_event_caps()
{
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

function add_event_caps_to_subscribers()
{
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
        echo '<div class="alert alert-warning">You must be logged in to submit an event.</div>';
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_title'])) {
        if (!isset($_POST['event_submission_nonce']) || !wp_verify_nonce($_POST['event_submission_nonce'], 'event_submission')) {
            echo '<div class="alert alert-danger">Nonce verification failed!</div>';
        } else {

            // Prepare post data
            $event_id = wp_insert_post([
                'post_title'   => sanitize_text_field($_POST['event_title']),
                'post_content' => wp_kses_post($_POST['event_description']),
                'post_type'    => 'event',
                'post_status'  => 'publish',
                'post_author'  => get_current_user_id(),
            ]);

            if ($event_id) {
                // Save custom fields (ACF or post meta)
                update_post_meta($event_id, 'event_location', sanitize_text_field($_POST['event_location']));
                update_post_meta($event_id, 'event_start_date', sanitize_text_field($_POST['event_start_date']));
                update_post_meta($event_id, 'event_end_date', sanitize_text_field($_POST['event_end_date']));
                update_post_meta($event_id, 'event_privacy', sanitize_text_field($_POST['event_privacy']));

                echo '<div class="alert alert-success">✅ Event posted successfully!</div>';
            } else {
                echo '<div class="alert alert-danger">Failed to post the event.</div>';
            }
        }
    }
    ?>

    <form method="post" action="" class="needs-validation" novalidate enctype="multipart/form-data">
        <?php wp_nonce_field('event_submission', 'event_submission_nonce'); ?>

        <div class="mb-3">
            <label for="event_title" class="form-label">Event Name</label>
            <input type="text" name="event_title" class="form-control" id="event_title" required>
            <div class="invalid-feedback">Please enter the event name.</div>
        </div>

        <div class="mb-3">
            <label for="event_description" class="form-label">Description</label>
            <textarea name="event_description" class="form-control" id="event_description" rows="5"></textarea>
        </div>

        <div class="mb-3">
            <label for="event_location" class="form-label">Location</label>
            <input type="text" name="event_location" class="form-control" id="event_location">
        </div>

        <div class="mb-3 row g-3">
            <div class="col-md-6">
                <label for="event_start_date" class="form-label">Start Date & Time</label>
                <input type="datetime-local" name="event_start_date" class="form-control" id="event_start_date">
            </div>
            <div class="col-md-6">
                <label for="event_end_date" class="form-label">End Date & Time</label>
                <input type="datetime-local" name="event_end_date" class="form-control" id="event_end_date">
            </div>
        </div>

        <div class="mb-3">
            <label for="event_privacy" class="form-label">Privacy</label>
            <select name="event_privacy" class="form-select" id="event_privacy">
                <option value="public">Public</option>
                <option value="friends">Friends</option>
                <option value="private">Private</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="event_image" class="form-label">Event Image</label>
            <input type="file" name="event_image" class="form-control" id="event_image" accept="image/*">
        </div>

        <button type="submit" class="btn btn-primary">Submit Event</button>
    </form>

    <script>
        // Bootstrap form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>

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


// Register Event Submission Action
if (function_exists('mm_register_action')) {
    mm_register_action('event_submission', 'Event Submission');

    // Optionally, you can register more specific actions if needed
    mm_register_action('event_created', 'Event Created');
    mm_register_action('event_edited', 'Event Edited');
    mm_register_action('event_deleted', 'Event Deleted');
}
