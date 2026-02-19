<?php
/**
 * Clear Test Data Page
 * 
 * Provides WordPress admin interface to clear withdrawal and audit data
 * Only accessible to administrators
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check permissions
if (!is_admin() || !current_user_can('manage_options')) {
    wp_die('Unauthorized access');
}

// Handle form submission
if (isset($_POST['action']) && $_POST['action'] === 'clear_withdrawal_data') {
    check_admin_referer('clear_withdrawal_nonce');
    
    global $wpdb;
    
    $results = array();
    
    // Clear withdrawal requests table
    $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}pet_withdrawal_requests");
    $results['withdrawal_requests'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}pet_withdrawal_requests");
    
    // Clear payout audit log table
    $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}payout_audit_log");
    $results['audit_log'] = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}payout_audit_log");
    
    // Clear user meta balance change logs (optional)
    if (isset($_POST['clear_balance_logs']) && $_POST['clear_balance_logs'] === '1') {
        $users = get_users();
        foreach ($users as $user) {
            delete_user_meta($user->ID, 'balance_change_logs');
        }
        $results['balance_logs_cleared'] = true;
    }
    
    $results['success'] = true;
    $results['timestamp'] = current_time('mysql');
    
    // Display success message
    echo '<div class="notice notice-success is-dismissible"><p>';
    echo 'Test data cleared successfully!<br>';
    echo 'Withdrawal Requests: ' . $results['withdrawal_requests'] . ' remaining<br>';
    echo 'Audit Logs: ' . $results['audit_log'] . ' remaining<br>';
    if (isset($results['balance_logs_cleared']) && $results['balance_logs_cleared']) {
        echo 'Balance change logs cleared for all users<br>';
    }
    echo 'Timestamp: ' . $results['timestamp'];
    echo '</p></div>';
}

// Get current counts
global $wpdb;
$withdrawal_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}pet_withdrawal_requests");
$audit_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}payout_audit_log");
?>

<div class="wrap">
    <h1>Clear Withdrawal Test Data</h1>
    
    <div class="card">
        <div class="card-body" style="padding: 20px;">
            <h2>Current Status</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">Withdrawal Requests</th>
                    <td><strong><?php echo $withdrawal_count; ?></strong> records</td>
                </tr>
                <tr>
                    <th scope="row">Audit Logs</th>
                    <td><strong><?php echo $audit_count; ?></strong> records</td>
                </tr>
            </table>
            
            <hr>
            
            <h2>Clear Test Data</h2>
            <p style="color: #dc3545; font-weight: bold;">⚠️ WARNING: This action cannot be undone!</p>
            
            <form method="POST">
                <?php wp_nonce_field('clear_withdrawal_nonce'); ?>
                <input type="hidden" name="action" value="clear_withdrawal_data">
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="clear_requests">
                                <input type="checkbox" id="clear_requests" name="clear_requests" value="1" checked disabled>
                                Clear Withdrawal Requests Table
                            </label>
                        </th>
                        <td>Will delete all withdrawal requests</td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="clear_audit">
                                <input type="checkbox" id="clear_audit" name="clear_audit" value="1" checked disabled>
                                Clear Audit Logs Table
                            </label>
                        </th>
                        <td>Will delete all payout audit logs</td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="clear_balance_logs">
                                <input type="checkbox" id="clear_balance_logs" name="clear_balance_logs" value="1">
                                Clear Balance Change Logs (Optional)
                            </label>
                        </th>
                        <td>Clears transaction history from all users (keeps current balance)</td>
                    </tr>
                </table>
                
                <p>
                    <button type="submit" class="button button-primary button-large" style="background-color: #dc3545;">
                        Clear Test Data
                    </button>
                </p>
            </form>
            
            <div style="margin-top: 30px; padding: 15px; background: #f1f1f1; border-left: 4px solid #0073aa;">
                <h3>What This Does:</h3>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Deletes all withdrawal requests (fresh state for testing)</li>
                    <li>Deletes all audit logs (clean history)</li>
                    <li>Optionally clears transaction history (keeps current user balances)</li>
                    <li>Does NOT reset user balances - those remain as-is for testing</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    background: white;
    border: 1px solid #ccc;
    border-radius: 5px;
    margin: 20px 0;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
}

.card-body {
    border-bottom: 1px solid #eee;
}

.card-body h2 {
    margin-top: 0;
    color: #333;
}
</style>
