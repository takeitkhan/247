<?php
defined('ABSPATH') || exit;

add_action('init', function () {
    register_post_type('spg_step', [
        'label' => 'SPG Steps',
        'public' => false,
        'show_ui' => true,
        'menu_icon' => 'dashicons-controls-forward',
        'supports' => ['title', 'page-attributes'],
    ]);
});

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'post.php' && $hook !== 'post-new.php') return;

    wp_enqueue_script('jquery-ui-sortable');
});
