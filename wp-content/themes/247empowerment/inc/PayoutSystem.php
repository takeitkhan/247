<?php
/**
 * Payout System for Withdrawal Requests
 * Handles user withdrawal requests and PayPal integration
 */

class PayoutSystem {

    const TABLE_NAME = 'withdrawal_requests';
    const MIN_WITHDRAWAL = 5;
    const MAX_WITHDRAWAL = 5000;
    private static $admin_menu_registered = false;

    public function __construct() {
        add_action('init', [$this, 'register_post_types']);
        add_action('admin_menu', [$this, 'register_admin_menu']);
        add_action('wp_ajax_submit_withdrawal', [$this, 'handle_withdrawal_request']);
        add_action('wp_ajax_approve_withdrawal', [$this, 'handle_approve_withdrawal']);
        add_action('wp_ajax_reject_withdrawal', [$this, 'handle_reject_withdrawal']);
        add_action('wp_footer', [$this, 'enqueue_frontend_assets']);
    }

    /**
     * Create withdrawal requests table on plugin activation
     */
    public static function activate() {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            amount DECIMAL(10, 2) NOT NULL,
            paypal_email VARCHAR(255) NOT NULL,
            status ENUM('pending', 'approved', 'processing', 'paid', 'rejected', 'failed') DEFAULT 'pending',
            transaction_id VARCHAR(255) DEFAULT NULL,
            admin_notes LONGTEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY user_id (user_id),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        // Create audit log table
        $audit_table = $wpdb->prefix . 'payout_audit_log';
        $audit_sql = "CREATE TABLE IF NOT EXISTS $audit_table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            withdrawal_id BIGINT(20) UNSIGNED NOT NULL,
            admin_id BIGINT(20) UNSIGNED,
            action VARCHAR(50) NOT NULL,
            notes LONGTEXT DEFAULT NULL,
            response_data LONGTEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            KEY withdrawal_id (withdrawal_id),
            KEY admin_id (admin_id),
            FOREIGN KEY (withdrawal_id) REFERENCES $table_name(id) ON DELETE CASCADE
        ) $charset_collate;";

        dbDelta($audit_sql);
    }

    /**
     * Register admin menu for payouts
     */
    public function register_admin_menu() {
        // Prevent duplicate menu registration
        if (self::$admin_menu_registered) {
            return;
        }
        self::$admin_menu_registered = true;

        add_menu_page(
            'Payouts',
            'Payouts',
            'manage_options',
            'payout-requests',
            null, // No callback - use first submenu instead
            'dashicons-money-alt',
            25
        );

        add_submenu_page(
            'payout-requests',
            'Withdrawal Requests',
            'Withdrawal Requests',
            'manage_options',
            'payout-requests',
            [$this, 'render_admin_page']
        );

        add_submenu_page(
            'payout-requests',
            'Payout Settings',
            'Settings',
            'manage_options',
            'payout-settings',
            [$this, 'render_settings_page']
        );

        add_submenu_page(
            'payout-requests',
            'Documentation',
            'Documentation',
            'manage_options',
            'payout-documentation',
            [$this, 'render_documentation_page']
        );
    }

    /**
     * Register custom post types
     */
    public function register_post_types() {
        // Not using CPT for payouts, using custom table instead
    }

    /**
     * Handle withdrawal request submission
     */
    public function handle_withdrawal_request() {
        check_ajax_referer('payout_security', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }

        $user_id = get_current_user_id();
        $amount = floatval($_POST['amount'] ?? 0);
        
        // Get PayPal email from user meta instead of form input
        $paypal_email = get_user_meta($user_id, 'paypal_email', true);

        // Validation
        if ($amount < self::MIN_WITHDRAWAL) {
            wp_send_json_error("Minimum withdrawal amount is \$" . self::MIN_WITHDRAWAL);
        }

        if ($amount > self::MAX_WITHDRAWAL) {
            wp_send_json_error("Maximum withdrawal amount is \$" . self::MAX_WITHDRAWAL);
        }

        if (!$paypal_email || !is_email($paypal_email)) {
            wp_send_json_error('Please set your PayPal email in your profile settings before requesting a withdrawal');
        }

        // Check balance
        $balance = floatval(get_user_meta($user_id, 'referral_commission', true) ?: 0);
        if ($amount > $balance) {
            wp_send_json_error("Insufficient balance. Your balance: \$" . number_format($balance, 2));
        }

        // Check rate limiting (max 1 request per day)
        $last_request = $this->get_last_withdrawal_request($user_id);
        if ($last_request && strtotime('now') - strtotime($last_request->created_at) < 86400) {
            wp_send_json_error('You can only submit one withdrawal request per day');
        }

        // Create withdrawal request
        $withdrawal_id = $this->create_withdrawal_request($user_id, $amount, $paypal_email);

        if ($withdrawal_id) {
            do_action('payout_withdrawal_requested', $user_id, $withdrawal_id, $amount, $paypal_email);
            wp_send_json_success([
                'message' => 'Withdrawal request submitted successfully',
                'withdrawal_id' => $withdrawal_id
            ]);
        } else {
            wp_send_json_error('Failed to create withdrawal request');
        }
    }

    /**
     * Create a new withdrawal request
     */
    public function create_withdrawal_request($user_id, $amount, $paypal_email) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;

        $result = $wpdb->insert(
            $table_name,
            [
                'user_id' => $user_id,
                'amount' => $amount,
                'paypal_email' => $paypal_email,
                'status' => 'pending'
            ],
            ['%d', '%f', '%s', '%s']
        );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Get last withdrawal request for rate limiting
     */
    public function get_last_withdrawal_request($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d AND status IN ('pending', 'approved', 'processing', 'paid') ORDER BY created_at DESC LIMIT 1",
            $user_id
        ));
    }

    /**
     * Get withdrawal requests for user
     */
    public function get_user_withdrawals($user_id, $limit = 20) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d ORDER BY created_at DESC LIMIT %d",
            $user_id,
            $limit
        ));
    }

    /**
     * Get all withdrawal requests for admin
     */
    public function get_all_withdrawals($status = null, $limit = 50, $offset = 0) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;

        $query = "SELECT wr.*, u.user_login, u.user_email 
                  FROM $table_name wr 
                  LEFT JOIN $wpdb->users u ON wr.user_id = u.ID";

        if ($status) {
            $query .= $wpdb->prepare(" WHERE wr.status = %s", $status);
        }

        $query .= " ORDER BY wr.created_at DESC LIMIT %d OFFSET %d";

        return $wpdb->get_results($wpdb->prepare($query, $limit, $offset));
    }

    /**
     * Handle approval of withdrawal
     */
    public function handle_approve_withdrawal() {
        check_ajax_nonce('payout_security', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $withdrawal_id = intval($_POST['withdrawal_id'] ?? 0);
        $notes = sanitize_textarea_field($_POST['notes'] ?? '');

        if (!$withdrawal_id) {
            wp_send_json_error('Invalid withdrawal ID');
        }

        $withdrawal = $this->get_withdrawal($withdrawal_id);
        if (!$withdrawal) {
            wp_send_json_error('Withdrawal not found');
        }

        // Update status to processing
        $this->update_withdrawal_status($withdrawal_id, 'processing');
        $this->log_audit($withdrawal_id, 'approved', $notes, get_current_user_id());

        // Trigger PayPal payout
        do_action('payout_process_paypal', $withdrawal_id);

        wp_send_json_success('Withdrawal approved and processing');
    }

    /**
     * Handle rejection of withdrawal
     */
    public function handle_reject_withdrawal() {
        check_ajax_nonce('payout_security', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $withdrawal_id = intval($_POST['withdrawal_id'] ?? 0);
        $notes = sanitize_textarea_field($_POST['notes'] ?? '');

        if (!$withdrawal_id) {
            wp_send_json_error('Invalid withdrawal ID');
        }

        $withdrawal = $this->get_withdrawal($withdrawal_id);
        if (!$withdrawal) {
            wp_send_json_error('Withdrawal not found');
        }

        // Update status to rejected
        $this->update_withdrawal_status($withdrawal_id, 'rejected');
        $this->log_audit($withdrawal_id, 'rejected', $notes, get_current_user_id());

        do_action('payout_withdrawal_rejected', $withdrawal->user_id, $withdrawal_id, $withdrawal->amount);

        wp_send_json_success('Withdrawal rejected');
    }

    /**
     * Get single withdrawal
     */
    public function get_withdrawal($withdrawal_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $withdrawal_id
        ));
    }

    /**
     * Update withdrawal status
     */
    public function update_withdrawal_status($withdrawal_id, $status, $transaction_id = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;

        return $wpdb->update(
            $table_name,
            array_filter([
                'status' => $status,
                'transaction_id' => $transaction_id
            ]),
            ['id' => $withdrawal_id],
            ['%s', '%s'],
            ['%d']
        );
    }

    /**
     * Log audit action
     */
    public function log_audit($withdrawal_id, $action, $notes = '', $admin_id = 0) {
        global $wpdb;
        $audit_table = $wpdb->prefix . 'payout_audit_log';

        return $wpdb->insert(
            $audit_table,
            [
                'withdrawal_id' => $withdrawal_id,
                'admin_id' => $admin_id ?: get_current_user_id(),
                'action' => $action,
                'notes' => $notes
            ],
            ['%d', '%d', '%s', '%s']
        );
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : null;
        $paged = max(1, isset($_GET['paged']) ? intval($_GET['paged']) : 1);
        $per_page = 20;
        $offset = ($paged - 1) * $per_page;

        $withdrawals = $this->get_all_withdrawals($status, $per_page, $offset);
        ?>
        <div class="wrap">
            <h1>Withdrawal Requests</h1>
            
            <div class="tablenav top">
                <form method="get">
                    <input type="hidden" name="page" value="payout-requests">
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="pending" <?php selected($status, 'pending'); ?>>Pending</option>
                        <option value="approved" <?php selected($status, 'approved'); ?>>Approved</option>
                        <option value="processing" <?php selected($status, 'processing'); ?>>Processing</option>
                        <option value="paid" <?php selected($status, 'paid'); ?>>Paid</option>
                        <option value="rejected" <?php selected($status, 'rejected'); ?>>Rejected</option>
                        <option value="failed" <?php selected($status, 'failed'); ?>>Failed</option>
                    </select>
                    <?php submit_button('Filter', 'button', 'submit', false); ?>
                </form>
            </div>

            <table class="wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Amount</th>
                        <th>PayPal Email</th>
                        <th>Status</th>
                        <th>Requested</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($withdrawals as $withdrawal) { ?>
                        <tr>
                            <td><?php echo $withdrawal->id; ?></td>
                            <td><?php echo $withdrawal->user_login; ?> (<?php echo $withdrawal->user_email; ?>)</td>
                            <td>$<?php echo number_format($withdrawal->amount, 2); ?></td>
                            <td><?php echo esc_html($withdrawal->paypal_email); ?></td>
                            <td><span class="status-<?php echo $withdrawal->status; ?>"><?php echo ucfirst($withdrawal->status); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($withdrawal->created_at)); ?></td>
                            <td>
                                <?php if ($withdrawal->status === 'pending') { ?>
                                    <button class="button button-primary approve-btn" data-id="<?php echo $withdrawal->id; ?>">Approve</button>
                                    <button class="button button-danger reject-btn" data-id="<?php echo $withdrawal->id; ?>">Reject</button>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_admin_referer('payout_settings_nonce');

            update_option('payout_min_amount', floatval($_POST['min_amount'] ?? 5));
            update_option('payout_max_amount', floatval($_POST['max_amount'] ?? 5000));
            update_option('payout_paypal_client_id', sanitize_text_field($_POST['paypal_client_id'] ?? ''));
            update_option('payout_paypal_secret', sanitize_text_field($_POST['paypal_secret'] ?? ''));
            update_option('payout_paypal_mode', sanitize_text_field($_POST['paypal_mode'] ?? 'sandbox'));

            echo '<div class="notice notice-success"><p>Settings saved!</p></div>';
        }

        $min_amount = get_option('payout_min_amount', 5);
        $max_amount = get_option('payout_max_amount', 5000);
        $client_id = get_option('payout_paypal_client_id', '');
        $mode = get_option('payout_paypal_mode', 'sandbox');
        ?>
        <div class="wrap">
            <h1>Payout Settings</h1>
            <form method="post" action="">
                <?php wp_nonce_field('payout_settings_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th><label for="min_amount">Minimum Withdrawal Amount</label></th>
                        <td><input type="number" step="0.01" name="min_amount" value="<?php echo $min_amount; ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="max_amount">Maximum Withdrawal Amount</label></th>
                        <td><input type="number" step="0.01" name="max_amount" value="<?php echo $max_amount; ?>" /></td>
                    </tr>
                    <tr>
                        <th><label for="paypal_mode">PayPal Mode</label></th>
                        <td>
                            <select name="paypal_mode">
                                <option value="sandbox" <?php selected($mode, 'sandbox'); ?>>Sandbox (Testing)</option>
                                <option value="live" <?php selected($mode, 'live'); ?>>Live</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="paypal_client_id">PayPal Client ID</label></th>
                        <td><input type="text" name="paypal_client_id" value="<?php echo esc_attr($client_id); ?>" style="width: 400px;" /></td>
                    </tr>
                    <tr>
                        <th><label for="paypal_secret">PayPal Secret</label></th>
                        <td><input type="password" name="paypal_secret" style="width: 400px;" /></td>
                    </tr>
                </table>
                
                <?php submit_button('Save Settings'); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render documentation page
     */
    public function render_documentation_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        // Get the markdown file path
        $doc_file = ABSPATH . 'wp-content/themes/247empowerment/PAYOUT_SETUP_GUIDE.md';

        if (!file_exists($doc_file)) {
            echo '<div class="wrap"><div class="notice notice-error"><p>Documentation file not found.</p></div></div>';
            return;
        }

        // Read markdown file
        $markdown_content = file_get_contents($doc_file);

        // Simple markdown to HTML conversion
        $html = $this->markdown_to_html($markdown_content);

        ?>
        <div class="wrap" style="background: #fff; padding: 20px; border-radius: 8px; max-width: 900px;">
            <style>
                .payout-docs h1 { font-size: 2em; margin: 1em 0 0.5em 0; border-bottom: 3px solid #0073aa; padding-bottom: 0.5em; }
                .payout-docs h2 { font-size: 1.5em; margin: 1.5em 0 0.5em 0; color: #0073aa; }
                .payout-docs h3 { font-size: 1.2em; margin: 1.2em 0 0.5em 0; color: #0073aa; }
                .payout-docs h4 { font-size: 1.1em; margin: 1em 0 0.5em 0; }
                .payout-docs p { line-height: 1.6; margin: 0.8em 0; }
                .payout-docs ul, .payout-docs ol { margin: 1em 0; margin-left: 2em; }
                .payout-docs li { margin: 0.5em 0; }
                .payout-docs code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; font-family: monospace; color: #d63638; }
                .payout-docs pre { background: #f5f5f5; padding: 15px; border-left: 4px solid #0073aa; overflow-x: auto; margin: 1em 0; border-radius: 4px; }
                .payout-docs pre code { background: none; color: #333; padding: 0; }
                .payout-docs blockquote { border-left: 4px solid #ddd; margin-left: 0; padding-left: 15px; color: #666; font-style: italic; }
                .payout-docs table { width: 100%; border-collapse: collapse; margin: 1em 0; }
                .payout-docs th, .payout-docs td { padding: 10px; border: 1px solid #ddd; text-align: left; }
                .payout-docs th { background: #f9f9f9; font-weight: bold; }
                .payout-docs a { color: #0073aa; text-decoration: none; }
                .payout-docs a:hover { text-decoration: underline; }
            </style>
            <div class="payout-docs">
                <?php echo wp_kses_post($html); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Convert markdown to HTML (simple implementation)
     */
    private function markdown_to_html($markdown) {
        // Escape HTML first
        $html = htmlspecialchars($markdown);

        // Headers
        $html = preg_replace('/^### (.*?)$/m', '<h3>$1</h3>', $html);
        $html = preg_replace('/^## (.*?)$/m', '<h2>$1</h2>', $html);
        $html = preg_replace('/^# (.*?)$/m', '<h1>$1</h1>', $html);

        // Bold and italic
        $html = preg_replace('/\*\*(.*?)\*\*/m', '<strong>$1</strong>', $html);
        $html = preg_replace('/\*(.*?)\*/m', '<em>$1</em>', $html);
        $html = preg_replace('/__(.*?)__/m', '<strong>$1</strong>', $html);
        $html = preg_replace('/_(.*?)_/m', '<em>$1</em>', $html);

        // Code blocks
        $html = preg_replace('/```(.*?)```/s', '<pre><code>$1</code></pre>', $html);
        $html = preg_replace('/`([^`]+)`/m', '<code>$1</code>', $html);

        // Links
        $html = preg_replace('/\[(.*?)\]\((.*?)\)/m', '<a href="$2">$1</a>', $html);

        // Line breaks
        $html = preg_replace('/\n\n+/', '</p><p>', $html);
        $html = '<p>' . $html . '</p>';

        // Lists
        $html = preg_replace('/<p>- (.*?)<\/p>/m', '<ul><li>$1</li></ul>', $html);
        $html = preg_replace('/<\/ul>\s*<ul>/', '', $html);

        return $html;
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        wp_localize_script('jquery', 'PayoutData', [
            'nonce' => wp_create_nonce('payout_security'),
            'ajaxurl' => admin_url('admin-ajax.php')
        ]);
    }
}
