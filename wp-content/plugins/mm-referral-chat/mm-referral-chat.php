<?php
/**
 * Plugin Name: MM Referral Chat
 * Description: Chat system for users connected via referral relationships
 * Version: 1.0.0
 * Author: MM Team
 * License: MIT
 * Text Domain: mm-referral-chat
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // No direct access
}

// Define plugin constants
define('MM_REFERRAL_CHAT_PATH', plugin_dir_path(__FILE__));
define('MM_REFERRAL_CHAT_URL', plugin_dir_url(__FILE__));
define('MM_REFERRAL_CHAT_VERSION', '1.0.0');

// Include required files
require_once MM_REFERRAL_CHAT_PATH . 'includes/class-chat-database.php';
require_once MM_REFERRAL_CHAT_PATH . 'includes/class-chat-manager.php';
require_once MM_REFERRAL_CHAT_PATH . 'includes/class-message-handler.php';
require_once MM_REFERRAL_CHAT_PATH . 'includes/class-chat-ajax.php';

/**
 * Main Plugin Class
 */
class MM_Referral_Chat
{
    private static $instance = null;

    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        // Plugin activation
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);

        // Initialize on WordPress load
        add_action('wp_loaded', [$this, 'init']);
    }

    /**
     * Plugin activation - Create database tables
     */
    public function activate()
    {
        try {
            MM_Chat_Database::create_tables();
            error_log('MM Chat: Plugin activated successfully');
        } catch (Exception $e) {
            error_log('MM Chat: Activation error - ' . $e->getMessage());
        }
    }

    /**
     * Plugin deactivation
     */
    public function deactivate()
    {
        // Cleanup if needed
    }

    /**
     * Initialize plugin
     */
    public function init()
    {
        // Load text domain
        load_plugin_textdomain('mm-referral-chat', false, dirname(plugin_basename(__FILE__)) . '/languages');

        // Initialize classes
        if (!is_admin()) {
            $this->init_frontend();
        }

        // Initialize AJAX handlers
        MM_Chat_AJAX::init();
    }

    /**
     * Initialize frontend
     */
    private function init_frontend()
    {
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);

        // Add chat to footer
        add_action('wp_footer', [$this, 'render_chat_interface']);
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_assets()
    {
        if (!is_user_logged_in()) {
            return;
        }

        // Chat CSS
        wp_enqueue_style(
            'mm-referral-chat-styles',
            MM_REFERRAL_CHAT_URL . 'assets/css/chat-styles.css',
            [],
            MM_REFERRAL_CHAT_VERSION
        );

        // Chat JS
        wp_enqueue_script(
            'mm-referral-chat-script',
            MM_REFERRAL_CHAT_URL . 'assets/js/chat-interface.js',
            ['jquery'],
            MM_REFERRAL_CHAT_VERSION,
            true
        );

        // Localize script
        wp_localize_script('mm-referral-chat-script', 'mmChat', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'currentUserId' => get_current_user_id(),
            'nonce' => wp_create_nonce('mm_chat_nonce'),
            'pollingInterval' => 3000, // 3 seconds
        ]);
    }

    /**
     * Render chat interface in footer
     */
    public function render_chat_interface()
    {
        if (!is_user_logged_in()) {
            return;
        }

        include MM_REFERRAL_CHAT_PATH . 'templates/chat-interface.php';
    }
}

// Boot plugin
MM_Referral_Chat::get_instance();
