<?php
defined('ABSPATH') || exit;

// List-table sorting assets
add_action('admin_enqueue_scripts', function ($hook) {

    // 🔥 THIS IS THE KEY
    if ($hook !== 'edit.php') {
        return;
    }

    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'spg_step') {
        return;
    }

    wp_enqueue_script('jquery-ui-sortable');

    // Sorting CSS (drag handle)
    wp_enqueue_style(
        'spg-step-sort',
        plugin_dir_url(__FILE__) . 'assets/css/spg-step-sort.css',
        [],
        '1.0.0'
    );

    // Sorting JS
    wp_enqueue_script(
        'spg-step-sort',
        plugin_dir_url(__FILE__) . 'assets/js/spg-step-sort.js',
        ['jquery', 'jquery-ui-sortable'],
        '1.0.0',
        true
    );

    wp_localize_script(
        'spg-step-sort',
        'SPG_STEP_SORT',
        [
            'nonce' => wp_create_nonce('spg_step_sort'),
        ]
    );
});


add_action('wp_ajax_spg_save_step_order', function () {

    // Security
    check_ajax_referer('spg_step_sort', 'nonce');

    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Permission denied');
    }

    if (empty($_POST['order']) || !is_array($_POST['order'])) {
        wp_send_json_error('Invalid order data');
    }

    foreach ($_POST['order'] as $item) {
        if (empty($item['id'])) continue;

        wp_update_post([
            'ID'         => (int) $item['id'],
            'menu_order' => (int) $item['position'],
        ]);
    }

    wp_send_json_success();
});

// Add drag handle column
add_filter('manage_spg_step_posts_columns', function ($columns) {
    $new = [];

    foreach ($columns as $key => $label) {
        if ($key === 'cb') {
            $new['cb'] = $label;
            $new['spg_drag'] = ''; // drag handle column
            continue;
        }
        $new[$key] = $label;
    }

    return $new;
});

// Render drag handle
add_action('manage_spg_step_posts_custom_column', function ($column, $post_id) {
    if ($column === 'spg_drag') {
        echo '<span class="spg-drag-handle dashicons dashicons-move"></span>';
    }
}, 10, 2);

add_filter('manage_spg_step_posts_columns', function ($cols) {
    $cols['spg_phase'] = 'Phase';
    $cols['spg_interest'] = 'Interest';
    return $cols;
});

add_action('manage_spg_step_posts_custom_column', function ($col, $post_id) {
    if ($col === 'spg_phase') {
        echo esc_html(get_post_meta($post_id, '_spg_phase', true));
    }

    if ($col === 'spg_interest') {
        echo esc_html(get_post_meta($post_id, '_spg_interest', true));
    }
}, 10, 2);
