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

    $status     = get_user_meta($user_id, 'mm_spg_status', true);
    $step       = (int) get_user_meta($user_id, 'mm_spg_step', true);
    $wait_until = (int) get_user_meta($user_id, 'mm_spg_waiting_until', true);

    $phase      = (int) get_user_meta($user_id, 'mm_spg_phase', true);


    // Phase detection
    $phase_2_done = (bool) get_user_meta($user_id, 'mm_spg_phase_2_completed', true);    

    // Determine active phase
    $current_phase = $phase_2_done ? 3 : 2;

    wp_send_json_success([
        'status'        => $status,
        'step'          => $step,
        'wait_until'    => $wait_until,
        'current_phase' => $current_phase,
        'phase'      => $phase,
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

    check_ajax_referer('mm_spg_state_nonce', 'nonce');

    $user_id = get_current_user_id();
    $status  = sanitize_text_field($_POST['status'] ?? 'paused');
    $step    = (int) ($_POST['step'] ?? 0);

    // 🔑 PHASE UPGRADE HAPPENS HERE
    if (
        $status === 'active' &&
        get_user_meta($user_id, 'mm_spg_phase_2_completed', true)
    ) {
        update_user_meta($user_id, 'mm_spg_phase', 3);
    }

    update_user_meta($user_id, 'mm_spg_status', $status);
    update_user_meta($user_id, 'mm_spg_step', $step);

    wp_send_json_success();
}



/* ========================
        SOCIAL MANAGEMENT
    ========================= */
add_action('wp_ajax_mm_spg_save_social_links', 'mm_spg_save_social_links');

function mm_spg_save_social_links()
{
    if (!is_user_logged_in()) {
        wp_send_json_error('Not logged in');
    }

    check_ajax_referer('mm_spg_links_save', 'nonce');

    $user_id = get_current_user_id();

    $links = $_POST['links'] ?? [];
    $clean_links = [];

    foreach ($links as $link) {
        if (!empty($link['url'])) {
            $clean_links[] = [
                'platform' => sanitize_text_field($link['platform'] ?? ''),
                'label'    => sanitize_text_field($link['label'] ?? ''),
                'url'      => esc_url_raw($link['url']),
            ];
        }
    }

    update_user_meta($user_id, 'custom_social_links', $clean_links);

    // Optional: mark step completed
    update_user_meta($user_id, 'mm_spg_social_links_completed', 1);

    wp_send_json_success('Social links updated successfully.');
}

/**
 * Summary of mm_spg_set_wait
 * @return void
 */
function mm_spg_set_wait()
{
    if (!is_user_logged_in()) {
        wp_send_json_error();
    }

    check_ajax_referer('mm_spg_state_nonce', 'nonce');

    $user_id = get_current_user_id();

    update_user_meta($user_id, 'mm_spg_waiting_until', (int) $_POST['wait_until']);
    update_user_meta($user_id, 'mm_spg_step', (int) $_POST['step']);
    update_user_meta($user_id, 'mm_spg_status', 'waiting');

    wp_send_json_success();
}
add_action('wp_ajax_mm_spg_set_state', 'mm_spg_set_state');
add_action('wp_ajax_mm_spg_set_wait', 'mm_spg_set_wait');



/**
 * Get current user's selected avatar
 */
function mm_spg_get_user_avatar()
{
    if (!is_user_logged_in()) {
        return '';
    }

    return get_user_meta(get_current_user_id(), 'mm_spg_avatar', true);
}

function mm_spg_get_steps_for_user($user_id)
{
    $steps = mm_spg_get_steps(); // Phase 2 always

    $phase  = (int) get_user_meta($user_id, 'mm_spg_phase', true);
    $status = get_user_meta($user_id, 'mm_spg_status', true);

    // Inject Phase-3 ONLY when resumed
    if ($phase >= 3 && $status === 'active') {

        $priorities = get_user_meta($user_id, 'user_categories_priority', true);

        if (is_array($priorities) && !empty($priorities)) {
            asort($priorities);
            $primary_term_id = array_key_first($priorities);

            $term = get_term($primary_term_id);

            if ($term && !is_wp_error($term)) {
                $steps = array_merge(
                    $steps,
                    mm_spg_build_phase_3_steps($term->slug)
                );
            }
        }
    }

    return $steps;
}
/**
 * Summary of mm_spg_complete_phase_2
 * @param mixed $user_id
 * @return void
 */

function mm_spg_complete_phase_2($user_id)
{
    // Mark Phase-2 as completed
    update_user_meta($user_id, 'mm_spg_phase_2_completed', 1);

    // IMPORTANT: do NOT upgrade phase here
    // Phase-3 starts ONLY when user resumes

    update_user_meta($user_id, 'mm_spg_status', 'paused');
    update_user_meta($user_id, 'mm_spg_step', 0);
}

add_action('wp_ajax_mm_spg_get_steps', 'mm_spg_ajax_get_steps');

function mm_spg_ajax_get_steps()
{
    if (!is_user_logged_in()) {
        wp_send_json_error('Not logged in');
    }

    $user_id = get_current_user_id();

    // 🔑 Single source of truth
    $steps = mm_spg_get_steps_for_user($user_id);

    wp_send_json_success([
        'steps' => array_values($steps)
    ]);
}
