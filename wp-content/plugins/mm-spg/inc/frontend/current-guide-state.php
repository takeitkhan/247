<?php

/**
 * Get current guide state
 */
function mm_spg_get_state()
{
    if (!is_user_logged_in()) {
        wp_send_json_error();
    }

    $user_id = get_current_user_id();

    wp_send_json_success([
        'status'     => get_user_meta($user_id, 'mm_spg_status', true),
        'step'       => (int) get_user_meta($user_id, 'mm_spg_step', true),
        'wait_until' => (int) get_user_meta($user_id, 'mm_spg_waiting_until', true),
    ]);
}
add_action('wp_ajax_mm_spg_get_state', 'mm_spg_get_state');


/**
 * Update guide state
 */
function mm_spg_set_state()
{
    if (!is_user_logged_in()) {
        wp_send_json_error();
    }

    $user_id = get_current_user_id();
    $status  = sanitize_text_field($_POST['status'] ?? 'paused');
    $step    = isset($_POST['step']) ? (int) $_POST['step'] : 0;

    update_user_meta($user_id, 'mm_spg_status', $status);
    update_user_meta($user_id, 'mm_spg_step', $step);

    wp_send_json_success();
}
add_action('wp_ajax_mm_spg_set_state', 'mm_spg_set_state');

/**
 * Summary of mm_spg_set_wait
 * @return void
 */
function mm_spg_set_wait() {
    if (!is_user_logged_in()) {
        wp_send_json_error();
    }

    $user_id = get_current_user_id();

    update_user_meta($user_id, 'mm_spg_waiting_until', (int) $_POST['wait_until']);
    update_user_meta($user_id, 'mm_spg_step', (int) $_POST['step']);
    update_user_meta($user_id, 'mm_spg_status', 'waiting');

    wp_send_json_success();
}
add_action('wp_ajax_mm_spg_set_wait', 'mm_spg_set_wait');



/**
 * Get current user's selected avatar
 */
function mm_spg_get_user_avatar() {
    if (!is_user_logged_in()) {
        return '';
    }

    return get_user_meta(get_current_user_id(), 'mm_spg_avatar', true);
}


