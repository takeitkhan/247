<?php
/* Template Name: Withdrawal Request */
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

get_header_based_on_login();

$current_user_id = get_current_user_id();

// Get the user slug from the query variable
$user_slug = get_query_var('user_profile');

// Determine the target user
if ($user_slug) {
    $user = get_user_by('slug', $user_slug);
} else {
    $user = get_user_by('ID', $current_user_id);
}

// Get profile data
if ($user) {
    $profile_data_instance = new UserProfileData($user);
    $target_identifier = $user_slug ? $user_slug : $user->user_login;
    $profile_data_instance = new UserProfileData($target_identifier);
    $profile = $profile_data_instance->getProfile();
} else {
    $user = null;
    $profile = null;
}

$profile = $profile ?: [];

// Get user data
$user_id = $current_user_id;
$balance = floatval(get_user_meta($user_id, 'referral_commission', true) ?: 0);
$min_amount = floatval(get_option('payout_min_amount', 5));
$max_amount = floatval(get_option('payout_max_amount', 5000));
$saved_paypal_email = get_user_meta($user_id, 'paypal_email', true);

// Get withdrawal history
$payout_system = new PayoutSystem();
$withdrawals = $payout_system->get_user_withdrawals($user_id, 5);
?>

<div class="container profile-page pt20">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3">
            <?php get_template_part('template-custom/auth/common-parts/editprofilemenu', null, ['profile' => $profile]); ?>
            <?php get_template_part('template-custom/auth/profile-parts/navlink', null, ['profile' => $profile]); ?>
        </div>
        <div class="mb-0 rounded-end-0 col-lg-6">
            <div class="bg-white custom-card post-search">
                <div class="gap-3 post-row">
                    <div>
                        <h5 class="pb-4 text-start portal-title">Withdrawal Request</h5>
                    </div>

                    <!-- Current Balance -->
                    <div class="mb-4" style="background: #f5f5f5; padding: 20px; border-radius: 5px;">
                        <p style="margin: 0; font-size: 14px; color: #666;">Current Balance</p>
                        <h3 style="margin: 10px 0 0 0; font-size: 28px; color: #27ae60;">
                            $<?php echo number_format($balance, 2); ?>
                        </h3>
                    </div>

                    <!-- PayPal Email Status -->
                    <div class="mb-4">
                        <?php if ($saved_paypal_email) { ?>
                            <div style="padding: 12px; background: #e8f5e9; border: 1px solid #81c784; border-radius: 4px; margin-bottom: 10px;">
                                <p style="margin: 0; color: #2e7d32; font-size: 14px;">
                                    <strong>✓ PayPal Email:</strong> <?php echo esc_html($saved_paypal_email); ?>
                                </p>
                                <p style="margin: 5px 0 0 0; font-size: 12px; color: #558b2f;">
                                    <a href="<?php echo esc_url(home_url('/modify-paypal-email')); ?>" style="color: #558b2f; text-decoration: underline;">Change PayPal Email</a>
                                </p>
                            </div>
                        <?php } else { ?>
                            <div style="padding: 12px; background: #fff3e0; border: 1px solid #ffb74d; border-radius: 4px; margin-bottom: 10px;">
                                <p style="margin: 0; color: #e65100; font-size: 14px;">
                                    <strong>⚠️ No PayPal email set.</strong> You must <a href="<?php echo esc_url(home_url('/modify-paypal-email')); ?>" style="color: #e65100; text-decoration: underline; font-weight: bold;">add your PayPal email</a> before requesting a withdrawal.
                                </p>
                            </div>
                        <?php } ?>
                    </div>

                    <!-- Withdrawal Form -->
                    <?php if ($saved_paypal_email) { ?>
                    <div class="mb-4">
                        <form id="withdrawal-form" class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Withdrawal Amount (USD):</label>
                                <input 
                                    type="number" 
                                    id="amount" 
                                    name="amount" 
                                    class="form-control input"
                                    placeholder="Enter amount"
                                    min="<?php echo $min_amount; ?>"
                                    max="<?php echo min($max_amount, $balance); ?>"
                                    step="0.01"
                                    required
                                />
                                <small class="form-text text-muted">
                                    Min: $<?php echo number_format($min_amount, 2); ?> | Max: $<?php echo number_format(min($max_amount, $balance), 2); ?>
                                </small>
                            </div>

                            <div class="col-12">
                                <div style="background: #e3f2fd; border-left: 4px solid #2196F3; padding: 12px; border-radius: 4px; font-size: 14px;">
                                    <p style="margin: 0;">
                                        ⏱️ Your withdrawal will be processed within 1-3 business days after approval.
                                    </p>
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="w-auto custom-btn">
                                    Request Withdrawal
                                </button>
                            </div>

                            <div id="form-message" style="margin-top: 15px; padding: 12px; border-radius: 4px; display: none;" class="col-12">
                            </div>
                        </form>
                    </div>
                    <?php } ?>

                    <!-- Recent Withdrawals -->
                    <?php if ($withdrawals) { ?>
                    <div class="mt-5">
                        <h6 class="pb-3 text-start portal-title">Recent Withdrawal Requests</h6>
                        <div style="overflow-x: auto;">
                            <table class="table table-sm">
                                <thead>
                                    <tr style="background: #f5f5f5;">
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
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#withdrawal-form').on('submit', function(e) {
        e.preventDefault();

        const amount = parseFloat($('#amount').val());
        const $message = $('#form-message');

        if (!amount || amount <= 0) {
            $message.html('<div style="background: #ffebee; color: #c62828; padding: 10px; border-radius: 4px;">Please enter a valid amount</div>').show();
            return;
        }

        $.ajax({
            url: PayoutData.ajaxurl,
            type: 'POST',
            data: {
                action: 'submit_withdrawal',
                nonce: PayoutData.nonce,
                amount: amount
            },
            success: function(response) {
                if (response.success) {
                    $message.html('<div style="background: #e8f5e9; color: #2e7d32; padding: 10px; border-radius: 4px;">✓ ' + response.data.message + '</div>').show();
                    $('#withdrawal-form')[0].reset();
                    setTimeout(() => location.reload(), 2000);
                } else {
                    $message.html('<div style="background: #ffebee; color: #c62828; padding: 10px; border-radius: 4px;">✗ ' + response.data + '</div>').show();
                }
            },
            error: function() {
                $message.html('<div style="background: #ffebee; color: #c62828; padding: 10px; border-radius: 4px;">An error occurred. Please try again.</div>').show();
            }
        });
    });
});
</script>

<?php get_footer_based_on_login(); ?>
