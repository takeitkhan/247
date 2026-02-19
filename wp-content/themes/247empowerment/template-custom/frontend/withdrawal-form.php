<?php
/**
 * Withdrawal Request Form Template
 * Displays user balance and withdrawal form
 */

if (!is_user_logged_in()) {
    echo '<p>Please log in to request a withdrawal.</p>';
    return;
}

$user_id = get_current_user_id();
$user = get_user_by('ID', $user_id);
$balance = floatval(get_user_meta($user_id, 'referral_commission', true) ?: 0);
$min_amount = floatval(get_option('payout_min_amount', 5));
$max_amount = floatval(get_option('payout_max_amount', 5000));

// Get saved PayPal email from user meta
$saved_paypal_email = get_user_meta($user_id, 'paypal_email', true);

// Get withdrawal history
$payout_system = new PayoutSystem();
$withdrawals = $payout_system->get_user_withdrawals($user_id, 5);
?>

<div class="withdrawal-container" style="max-width: 600px; margin: 30px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
    <h2>Withdrawal Request</h2>

    <!-- Current Balance -->
    <div class="balance-card" style="background: #f5f5f5; padding: 20px; margin-bottom: 20px; border-radius: 5px;">
        <p style="margin: 0; font-size: 14px; color: #666;">Current Balance</p>
        <h3 style="margin: 10px 0 0 0; font-size: 28px; color: #27ae60;">
            $<?php echo number_format($balance, 2); ?>
        </h3>
    </div>

    <!-- Withdrawal Form -->
    <form id="withdrawal-form" class="withdrawal-form" style="border: 1px solid #e0e0e0; padding: 20px; border-radius: 5px;">
        <div class="form-group" style="margin-bottom: 15px;">
            <label for="paypal_email" style="display: block; margin-bottom: 5px; font-weight: bold;">
                PayPal Email Address
            </label>
            <?php if ($saved_paypal_email) { ?>
                <div style="padding: 10px; background: #e8f5e9; border: 1px solid #81c784; border-radius: 4px; margin-bottom: 10px;">
                    <p style="margin: 0; color: #2e7d32; font-size: 14px;">
                        <strong>Saved PayPal Email:</strong> <?php echo esc_html($saved_paypal_email); ?>
                    </p>
                    <p style="margin: 5px 0 0 0; font-size: 12px; color: #558b2f;">
                        <a href="<?php echo esc_url(home_url('/modify-paypal-email')); ?>" style="color: #558b2f; text-decoration: underline;">Update PayPal Email</a>
                    </p>
                </div>
            <?php } else { ?>
                <div style="padding: 10px; background: #fff3e0; border: 1px solid #ffb74d; border-radius: 4px; margin-bottom: 10px;">
                    <p style="margin: 0; color: #e65100; font-size: 14px;">
                        <strong>⚠️ No PayPal email saved.</strong> Please <a href="<?php echo esc_url(home_url('/modify-paypal-email')); ?>" style="color: #e65100; text-decoration: underline;">add your PayPal email</a> first.
                    </p>
                </div>
            <?php } ?>
        </div>

        <div class="form-group" style="margin-bottom: 15px;">
            <label for="amount" style="display: block; margin-bottom: 5px; font-weight: bold;">
                Withdrawal Amount (USD)
            </label>
            <div style="display: flex; gap: 10px;">
                <input 
                    type="number" 
                    id="amount" 
                    name="amount" 
                    placeholder="Enter amount"
                    min="<?php echo $min_amount; ?>"
                    max="<?php echo min($max_amount, $balance); ?>"
                    step="0.01"
                    required
                    style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px;"
                />
            </div>
            <small style="display: block; margin-top: 5px; color: #666;">
                Min: $<?php echo number_format($min_amount, 2); ?> | Max: $<?php echo number_format(min($max_amount, $balance), 2); ?>
            </small>
        </div>

        <div class="form-info" style="background: #e3f2fd; border-left: 4px solid #2196F3; padding: 12px; margin-bottom: 15px; border-radius: 4px; font-size: 14px;">
            <p style="margin: 0;">
                ⏱️ Your withdrawal will be processed within 1-3 business days after approval.
            </p>
        </div>

        <button 
            type="submit" 
            class="btn-submit"
            style="width: 100%; padding: 12px; background: #27ae60; color: white; border: none; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer;"
        >
            Request Withdrawal
        </button>

        <div id="form-message" style="margin-top: 15px; padding: 12px; border-radius: 4px; display: none;">
        </div>
    </form>

    <!-- Recent Withdrawals -->
    <?php if ($withdrawals) { ?>
    <div class="withdrawal-history" style="margin-top: 30px;">
        <h3 style="margin-top: 0;">Recent Withdrawal Requests</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f5f5f5; border-bottom: 2px solid #ddd;">
                    <th style="padding: 10px; text-align: left;">Amount</th>
                    <th style="padding: 10px; text-align: left;">Status</th>
                    <th style="padding: 10px; text-align: left;">Requested</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($withdrawals as $withdrawal) { 
                    $status_color = [
                        'pending' => '#ff9800',
                        'approved' => '#2196F3',
                        'processing' => '#9C27B0',
                        'paid' => '#27ae60',
                        'rejected' => '#f44336',
                        'failed' => '#f44336'
                    ][$withdrawal->status] ?? '#999';
                    ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 10px;">$<?php echo number_format($withdrawal->amount, 2); ?></td>
                        <td style="padding: 10px;">
                            <span style="background: <?php echo $status_color; ?>; color: white; padding: 4px 8px; border-radius: 3px; font-size: 12px;">
                                <?php echo ucfirst($withdrawal->status); ?>
                            </span>
                        </td>
                        <td style="padding: 10px;">
                            <?php echo date('M d, Y', strtotime($withdrawal->created_at)); ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php } ?>
</div>

<script>
(function() {
    console.log('Withdrawal form script initializing...');
    console.log('PayoutData:', typeof window.PayoutData !== 'undefined' ? window.PayoutData : 'NOT DEFINED');
    
    if (typeof jQuery === 'undefined') {
        console.error('jQuery not loaded!');
        return;
    }
    
    const $ = jQuery;
    
    $(document).ready(function() {
        console.log('Document ready, binding withdrawal form...');
        
        if (!window.PayoutData) {
            console.error('PayoutData not available - localized script data missing');
            $('#form-message').html('<div style="background: #ffebee; color: #c62828; padding: 10px; border-radius: 4px;">⚠️ System not initialized. Please refresh the page.</div>').show();
            return;
        }
        
        $('#withdrawal-form').on('submit', function(e) {
            console.log('Form submitted!');
            e.preventDefault();

            const amount = parseFloat($('#amount').val());
            const $message = $('#form-message');

            if (!amount || amount <= 0) {
                $message.html('<div style="background: #ffebee; color: #c62828; padding: 10px; border-radius: 4px;">Please enter a valid amount</div>').show();
                return;
            }

            console.log('Sending withdrawal request:', {
                action: 'submit_withdrawal',
                amount: amount,
                nonce: window.PayoutData.nonce
            });

            $.ajax({
                url: window.PayoutData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'submit_withdrawal',
                    nonce: window.PayoutData.nonce,
                    amount: amount
                },
                success: function(response) {
                    console.log('AJAX success:', response);
                    if (response.success) {
                        $message.html('<div style="background: #e8f5e9; color: #2e7d32; padding: 10px; border-radius: 4px;">✓ ' + response.data.message + '</div>').show();
                        $('#withdrawal-form')[0].reset();
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        $message.html('<div style="background: #ffebee; color: #c62828; padding: 10px; border-radius: 4px;">✗ ' + (response.data || 'Error occurred') + '</div>').show();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', {status, error, xhr});
                    $message.html('<div style="background: #ffebee; color: #c62828; padding: 10px; border-radius: 4px;">An error occurred. Please try again.</div>').show();
                }
            });
        });
        
        console.log('Withdrawal form bound successfully');
    });
})();
</script>
