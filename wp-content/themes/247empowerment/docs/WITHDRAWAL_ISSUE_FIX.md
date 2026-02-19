# Withdrawal System Issue Fix - Complete Analysis & Solution

## Problem Summary

Reported issues by user:

### Issue 1: Balance Deducted But Withdrawal Failed
- User's balance was $30, requested $1 withdrawal
- Admin clicked Approve button
- Withdrawal marked as "Failed"
- But balance was deducted: $30 → $29
- Money disappeared, not paid out to user

### Issue 2: No Visibility Into Failure Reason
- User cannot see why withdrawal failed
- Admin cannot see error details
- No error logging or tracking

---

## Root Cause Analysis

### Why Did This Happen?

**In PayoutSystem.php's `handle_approve_withdrawal()` method (Line 362-367):**

```php
error_log('Deducting balance from user account');
// Deduct balance when approved
payout_deduct_balance($withdrawal->user_id, $withdrawal->amount, 'Withdrawal request approved - ID: ' . $withdrawal_id);

error_log('Updating withdrawal status to processing');
// Update status to processing
$this->update_withdrawal_status($withdrawal_id, 'processing');
```

**The Problem:**
1. Balance is deducted at Approve time
2. PayPal payout is triggered asynchronously via `do_action()`
3. If PayPal fails, balance is NOT refunded
4. No error message is stored or displayed

**In PayPalAPI.php's `process_withdrawal()` method (Line 141-150 old code):**

```php
if (is_wp_error($response)) {
    $this->payout_system->log_audit($withdrawal_id, 'payout_failed', 'API error: ' . $response->get_error_message());
    $this->payout_system->update_withdrawal_status($withdrawal_id, 'failed');
    do_action('payout_payment_failed', $withdrawal->user_id, $withdrawal_id, $response->get_error_message());
    return $response;
}
```

**Problems with old code:**
1. Balance refund is never called
2. Error message is only logged to audit, not displayed
3. `admin_notes` field is never populated
4. User has no way to know what happened

---

## Complete Solution Implemented

### Solution 1: Balance Refund Logic (PayPalAPI.php)

All error scenarios now refund the balance:

**1) Token Error Handling:**
```php
if (is_wp_error($token)) {
    $error_msg = $token->get_error_message();
    error_log('PayPal token error: ' . $error_msg);
    
    $this->payout_system->log_audit($withdrawal_id, 'payout_failed', 'Token error: ' . $error_msg);
    $this->payout_system->update_withdrawal_status($withdrawal_id, 'failed', null, 'Token Error: ' . $error_msg);
    
    // CRITICAL: Refund the balance back to user
    error_log('Refunding balance of $' . $withdrawal->amount . ' to user ' . $withdrawal->user_id);
    payout_refund_withdrawal($withdrawal->user_id, $withdrawal->amount, 'PayPal token error: ' . $error_msg);
    
    do_action('payout_payment_failed', $withdrawal->user_id, $withdrawal_id, $error_msg);
    return $token;
}
```

**2) API Connection Error:**
```php
if (is_wp_error($response)) {
    $error_msg = $response->get_error_message();
    error_log('PayPal API request error: ' . $error_msg);
    
    $this->payout_system->log_audit($withdrawal_id, 'payout_failed', 'API Error: ' . $error_msg);
    $this->payout_system->update_withdrawal_status($withdrawal_id, 'failed', null, 'API Error: ' . $error_msg);
    
    // CRITICAL: Refund the balance back to user
    error_log('Refunding balance of $' . $withdrawal->amount . ' to user ' . $withdrawal->user_id);
    payout_refund_withdrawal($withdrawal->user_id, $withdrawal->amount, 'PayPal API error: ' . $error_msg);
    
    do_action('payout_payment_failed', $withdrawal->user_id, $withdrawal_id, $error_msg);
    return $response;
}
```

**3) PayPal Response Error (HTTP 400+):**
```php
if ($http_code >= 400 || isset($body['name'])) {
    $error_msg = $body['message'] ?? $body['name'] ?? 'Unknown error';
    error_log('PayPal payout failed: ' . $error_msg);
    
    $error_details = [
        'http_code' => $http_code,
        'error_message' => $error_msg,
        'response_body' => $body,
        'timestamp' => current_time('mysql')
    ];
    $this->payout_system->log_audit($withdrawal_id, 'payout_failed', json_encode($error_details));
    
    // Update withdrawal status to 'failed' with error reason in admin_notes
    $this->payout_system->update_withdrawal_status($withdrawal_id, 'failed', null, $error_msg);
    
    // CRITICAL: Refund the balance back to user
    error_log('Refunding balance of $' . $withdrawal->amount . ' to user ' . $withdrawal->user_id);
    payout_refund_withdrawal($withdrawal->user_id, $withdrawal->amount, 'PayPal payout failed - Error: ' . $error_msg);
    
    do_action('payout_payment_failed', $withdrawal->user_id, $withdrawal_id, $error_msg);
    return new WP_Error('paypal_error', $error_msg);
}
```

### Solution 2: Error Message Storage (PayoutSystem.php)

Enhanced `update_withdrawal_status()` method:

```php
/**
 * Update withdrawal status with optional error notes
 */
public function update_withdrawal_status($withdrawal_id, $status, $transaction_id = null, $admin_notes = null) {
    global $wpdb;
    $table_name = $wpdb->prefix . self::TABLE_NAME;

    $update_data = array_filter([
        'status' => $status,
        'transaction_id' => $transaction_id,
        'admin_notes' => $admin_notes
    ], function($value) {
        return $value !== null;
    });

    return $wpdb->update(
        $table_name,
        $update_data,
        ['id' => $withdrawal_id],
        array_fill(0, count($update_data), '%s'),
        ['%d']
    );
}
```

Error messages are now stored in the `admin_notes` field.

### Solution 3: Admin Panel Error Display (PayoutSystem.php)

Admin page table updated with new "Details" column:

```php
<td>
    <?php if (!empty($withdrawal->admin_notes) && $withdrawal->status === 'failed') { ?>
        <details style="cursor: pointer;">
            <summary style="color: #f44336; font-weight: bold;">View Error</summary>
            <div style="background: #fff3cd; padding: 8px; margin-top: 8px; border-left: 3px solid #f44336; border-radius: 3px; font-size: 12px;">
                <strong>Error:</strong><br>
                <?php echo nl2br(esc_html($withdrawal->admin_notes)); ?>
            </div>
        </details>
    <?php } elseif (!empty($withdrawal->transaction_id)) { ?>
        <small style="color: #666;">
            <strong>Batch ID:</strong><br>
            <code><?php echo esc_html($withdrawal->transaction_id); ?></code>
        </small>
    <?php } else { ?>
        <small style="color: #999;">-</small>
    <?php } ?>
</td>
```

### Solution 4: User-Facing Error Details (withdrawal-form.php)

Recent withdrawals table updated to show errors:

```php
<td style="padding: 10px; font-size: 12px;">
    <?php if ($withdrawal->status === 'failed' && !empty($withdrawal->admin_notes)) { ?>
        <details style="cursor: pointer; color: #d32f2f;">
            <summary style="font-weight: bold;">⚠️ Failed - Click for details</summary>
            <div style="background: #ffebee; padding: 8px; margin-top: 5px; border-left: 3px solid #d32f2f; border-radius: 3px; margin-top: 8px;">
                <strong style="color: #d32f2f;">Error Reason:</strong><br>
                <span style="color: #c62828;"><?php echo esc_html($withdrawal->admin_notes); ?></span>
                <p style="margin: 10px 0 0 0; font-size: 11px; color: #666;">
                    ✓ Your balance has been restored.<br>
                    Please contact support if you need assistance.
                </p>
            </div>
        </details>
    <?php } elseif ($withdrawal->status === 'paid' && !empty($withdrawal->transaction_id)) { ?>
        <small style="color: #27ae60;">
            ✓ Completed<br>
            <code style="font-size: 10px;"><?php echo esc_html(substr($withdrawal->transaction_id, 0, 12)); ?>...</code>
        </small>
    <?php } else { ?>
        <span style="color: #999;">-</span>
    <?php } ?>
</td>
```

---

## Changes Summary

| File | Change | Benefit |
|------|--------|---------|
| [PayPalAPI.php](../inc/PayPalAPI.php) | ✓ Balance refund logic added | Failed withdrawals restore balance automatically |
| [PayoutSystem.php](../inc/PayoutSystem.php) | ✓ Enhanced status update method | admin_notes parameter support |
| [PayoutSystem.php](../inc/PayoutSystem.php) | ✓ Admin page table updated | Display error reasons for failed withdrawals |
| [withdrawal-form.php](../template-custom/frontend/withdrawal-form.php) | ✓ Recent withdrawals table updated | Show error details to users |

---

## Testing the Fix

### Test Case 1: PayPal Token Error
```php
// Set invalid credentials:
get_option('payout_paypal_client_id', ''); // Invalid
```

**Expected Result:**
- ✓ Withdrawal status: Failed
- ✓ User balance: Restored
- ✓ Error message: "Token Error: ..." visible

### Test Case 2: PayPal API Error
```php
// Simulate network error or invalid credentials
```

**Expected Result:**
- ✓ Withdrawal status: Failed
- ✓ User balance: Restored
- ✓ Both admin and user see error reason

### Test Case 3: Successful Payout
```php
// Valid PayPal credentials
// Valid recipient email
```

**Expected Result:**
- ✓ Withdrawal status: paid
- ✓ Balance: Deducted (stays as-is)
- ✓ Batch ID: Stored in transaction_id

---

## Impact

✅ **Problem 1 Solved:** Failed withdrawal now automatically refunds balance  
✅ **Problem 2 Solved:** Error reasons visible to both admin and user  
✅ **New Benefits:** Complete audit trail, better reliability, improved user experience  

---

## Database Schema

No migration needed. The `admin_notes` field already exists:

```sql
CREATE TABLE wp_withdrawal_requests (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT(20) UNSIGNED NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    paypal_email VARCHAR(255) NOT NULL,
    status ENUM('pending', 'approved', 'processing', 'paid', 'rejected', 'failed') DEFAULT 'pending',
    transaction_id VARCHAR(255) DEFAULT NULL,
    admin_notes LONGTEXT DEFAULT NULL,          -- Error messages stored here
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY user_id (user_id),
    KEY status (status),
    KEY created_at (created_at)
);
```

---

## References

- [WITHDRAWAL_DEVELOPER_GUIDE.md](WITHDRAWAL_DEVELOPER_GUIDE.md) - Code reference and debugging
- [WITHDRAWAL_DEPLOYMENT_CHECKLIST.md](WITHDRAWAL_DEPLOYMENT_CHECKLIST.md) - Deployment steps
- [WITHDRAWAL_BEFORE_AFTER.md](WITHDRAWAL_BEFORE_AFTER.md) - Visual comparison
