<?php

/**
 * Plugin Name: MM Gamification
 * Description: Custom gamification plugin to manage user actions and points system.
 * Version: 1.0.0
 * Author: Samrat Khan
 * Text Domain: mm-gamification
 */

if (! defined('ABSPATH')) exit; // No direct access

// Includes
require_once plugin_dir_path(__FILE__) . 'includes/class-gamification-admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-gamification-user.php';
require_once plugin_dir_path(__FILE__) . 'includes/functions-points.php';
require_once plugin_dir_path(__FILE__) . 'includes/functions-actions.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-notifications.php';

class MM_Gamification
{
    public function __construct()
    {
        // Activation hook
        register_activation_hook(__FILE__, [$this, 'activate']);
    }

    public function activate()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'gamification_actions';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            action_key varchar(100) NOT NULL,
            custom_message text NOT NULL,
            points int NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

// Boot
new MM_Gamification();
if (is_admin()) {
    new MM_Gamification_Admin();
} else {
    new MM_Gamification_User();
}
