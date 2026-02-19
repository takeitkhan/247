<?php
/**
 * Clear all withdrawal data for fresh testing
 */

require 'wp-load.php';

global $wpdb;

echo "\n=== CLEARING WITHDRAWAL DATA ===\n\n";

// Clear withdrawal requests
$result1 = $wpdb->query('TRUNCATE TABLE ' . $wpdb->prefix . 'withdrawal_requests');
echo "✓ Cleared withdrawal_requests table\n";

// Clear audit logs
$result2 = $wpdb->query('TRUNCATE TABLE ' . $wpdb->prefix . 'payout_audit_log');
echo "✓ Cleared payout_audit_log table\n";

// Verify tables are empty
$count_requests = $wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->prefix . 'withdrawal_requests');
$count_logs = $wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->prefix . 'payout_audit_log');

echo "\n=== VERIFICATION ===\n";
echo "Withdrawal Requests: " . $count_requests . " records\n";
echo "Audit Logs: " . $count_logs . " records\n";

echo "\n✅ All withdrawal data cleared successfully!\n";
echo "Ready for fresh testing from scratch.\n\n";
?>
