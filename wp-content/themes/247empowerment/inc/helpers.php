<?php
/**
 * Helper Functions
 */

/**
 * Trigger WordPress action hook
 * Wrapper around do_action() for custom hooks
 * 
 * @param string $hook Action hook name
 * @param mixed ...$args Arguments to pass to hooked functions
 */
if (!function_exists('mm_trigger_action')) {
    function mm_trigger_action($hook, ...$args) {
        do_action($hook, ...$args);
    }
}

/**
 * Get header based on login status
 * Returns 'portal' for logged-in users, 'main' for guests
 */
if (!function_exists('get_header_based_on_login')) {
    function get_header_based_on_login() {
        $header_type = is_user_logged_in() ? 'portal' : 'main';
        get_header($header_type);
    }
}

/**
 * Get footer based on login status
 * Returns 'portal' for logged-in users, 'main' for guests
 */
if (!function_exists('get_footer_based_on_login')) {
    function get_footer_based_on_login() {
        $footer_type = is_user_logged_in() ? 'portal' : 'main';
        get_footer($footer_type);
    }
}
