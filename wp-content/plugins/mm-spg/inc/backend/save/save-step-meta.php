<?php
defined('ABSPATH') || exit;

add_action('save_post_spg_step', function ($post_id) {

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['spg_phase'])) {
        update_post_meta($post_id, '_spg_phase', intval($_POST['spg_phase']));
    }

    if (isset($_POST['spg_interest'])) {
        update_post_meta($post_id, '_spg_interest', sanitize_text_field($_POST['spg_interest']));
    }

    if (isset($_POST['spg_blocks']) && is_array($_POST['spg_blocks'])) {
        update_post_meta($post_id, '_spg_blocks', array_values($_POST['spg_blocks']));
    }
});
