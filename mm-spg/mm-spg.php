<?php

/**
 * Plugin Name: MM Sweet Portal Guide
 * Plugin URI:  https://mathmozo.com
 * Description: Minimal gamified onboarding guide with avatar and modal steps.
 * Version: 1.0.1
 * License: MIT
 * Author: Samrat Khan
 * Text Domain: mm-spg
 */

if (!defined('ABSPATH')) {
    exit; // No direct access
}

/**
 * Plugin constants
 */
define('MM_SPG_VERSION', '0.1.0');
define('MM_SPG_PATH', plugin_dir_path(__FILE__));
define('MM_SPG_URL', plugin_dir_url(__FILE__));

/**
 * Plugin init (very minimal for now)
 */
function mm_spg_init()
{
    // Placeholder: later we will load classes, assets, hooks
}
add_action('plugins_loaded', 'mm_spg_init');


function mm_spg_save_avatar()
{
    if (!is_user_logged_in()) {
        wp_send_json_error();
    }

    check_ajax_referer('mm_spg_security', 'nonce');

    $avatar = sanitize_text_field($_POST['avatar'] ?? '');

    if (!in_array($avatar, ['male', 'female'], true)) {
        wp_send_json_error();
    }

    update_user_meta(get_current_user_id(), 'mm_spg_avatar', $avatar);

    wp_send_json_success();
}
add_action('wp_ajax_mm_spg_save_avatar', 'mm_spg_save_avatar');


/**
 * Enqueue frontend assets
 */
function mm_spg_enqueue_assets()
{
    if (is_admin() || !is_user_logged_in()) {
        return;
    }

    $user_id = get_current_user_id();

    /* =========================
       BUILD STEPS FIRST
    ========================= */

    // Phase 2 steps
    $steps = mm_spg_get_steps();
    $phase_3_start_index = count($steps);

    // Phase 3 steps (conditional)
    if (get_user_meta($user_id, 'mm_spg_phase_2_completed', true)) {

        $priorities = get_user_meta($user_id, 'user_categories_priority', true);
        $priorities = is_array($priorities) ? $priorities : [];

        $primary_term_id = array_search(1, $priorities, true);

        if ($primary_term_id) {
            $term = get_term($primary_term_id);

            if ($term && !is_wp_error($term)) {
                $phase3 = mm_spg_build_phase_3_steps($term->slug);
                if ($phase3) {
                    $steps = array_merge($steps, $phase3);
                }
            }
        }
    }

    /* =========================
       ENQUEUE ASSETS
    ========================= */

    wp_enqueue_style(
        'mm-spg-css',
        MM_SPG_URL . 'inc/frontend/assets/css/mm-spg.css',
        [],
        MM_SPG_VERSION
    );

    wp_enqueue_script(
        'mm-spg-js',
        MM_SPG_URL . 'inc/frontend/assets/js/mm-spg.js',
        ['jquery'],
        MM_SPG_VERSION,
        true
    );

    /* =========================
       PASS DATA TO JS
    ========================= */

    wp_localize_script(
        'mm-spg-js',
        'MM_SPG',
        [
            'ajax_url'            => admin_url('admin-ajax.php'),
            'nonce'               => wp_create_nonce('mm_spg_security'),
            'avatar'              => mm_spg_get_user_avatar(),
            'steps'               => array_values($steps),
            'phase_3_start_index' => $phase_3_start_index,
        ]
    );
}
add_action('wp_enqueue_scripts', 'mm_spg_enqueue_assets');

// Load Frontend UI
require_once(MM_SPG_PATH . '/inc/frontend/current-guide-state.php');
require_once(MM_SPG_PATH . '/inc/frontend/modal.php');
// Hardcoded steps loader (deprecated)
// require_once(MM_SPG_PATH . '/inc/frontend/hardcoded-steps.php');
// Frontend (dynamic) steps loader
require_once __DIR__ . '/inc/frontend/dynamic-steps.php';
require_once(MM_SPG_PATH . '/inc/frontend/shortcodes.php');


// Load Admin UI (backend)
require_once MM_SPG_PATH . 'inc/backend/post-types/spg-step-cpt.php';
require_once MM_SPG_PATH . 'inc/backend/meta-boxes/step-settings.php';
require_once MM_SPG_PATH . 'inc/backend/meta-boxes/step-blocks.php';
require_once MM_SPG_PATH . 'inc/backend/save/save-step-meta.php';

require_once __DIR__ . '/inc/backend/admin-enqueue.php';
require_once __DIR__ . '/inc/backend/spg-step-sort.php';
