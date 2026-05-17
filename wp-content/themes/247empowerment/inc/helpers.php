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
