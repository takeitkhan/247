<?php
// ======================
// 1️⃣ PayPal Settings Page
// ======================

add_action('admin_menu', function () {
    add_options_page(
        'PayPal Settings',
        'PayPal Settings',
        'manage_options',
        'paypal-settings',
        'render_paypal_settings_page'
    );
});

function render_paypal_settings_page()
{
?>
    <div class="wrap">
        <h1>PayPal Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('paypal_settings_group');
            do_settings_sections('paypal-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

add_action('admin_init', function () {
    register_setting('paypal_settings_group', 'paypal_environment');
    register_setting('paypal_settings_group', 'paypal_sandbox_client_id');
    register_setting('paypal_settings_group', 'paypal_sandbox_secret');
    register_setting('paypal_settings_group', 'paypal_live_client_id');
    register_setting('paypal_settings_group', 'paypal_live_secret');
    register_setting('paypal_settings_group', 'paypal_referral_commission'); // ✅ New
    register_setting('paypal_settings_group', 'paypal_webhook_id');

    add_settings_section('paypal_main_section', 'PayPal API Configuration', null, 'paypal-settings');

    // Environment
    add_settings_field('paypal_environment', 'Environment', function () {
        $env = get_option('paypal_environment', 'sandbox');
    ?>
        <select name="paypal_environment">
            <option value="sandbox" <?= selected($env, 'sandbox'); ?>>Sandbox</option>
            <option value="live" <?= selected($env, 'live'); ?>>Live</option>
        </select>
    <?php
    }, 'paypal-settings', 'paypal_main_section');

    // Sandbox Client ID
    add_settings_field('paypal_sandbox_client_id', 'Sandbox Client ID', function () {
    ?>
        <input type="text" name="paypal_sandbox_client_id" value="<?= esc_attr(get_option('paypal_sandbox_client_id')); ?>" class="regular-text" />
    <?php
    }, 'paypal-settings', 'paypal_main_section');

    // Sandbox Secret
    add_settings_field('paypal_sandbox_secret', 'Sandbox Secret', function () {
    ?>
        <input type="password" name="paypal_sandbox_secret" value="<?= esc_attr(get_option('paypal_sandbox_secret')); ?>" class="regular-text" />
    <?php
    }, 'paypal-settings', 'paypal_main_section');

    // Live Client ID
    add_settings_field('paypal_live_client_id', 'Live Client ID', function () {
    ?>
        <input type="text" name="paypal_live_client_id" value="<?= esc_attr(get_option('paypal_live_client_id')); ?>" class="regular-text" />
    <?php
    }, 'paypal-settings', 'paypal_main_section');

    // Live Secret
    add_settings_field('paypal_live_secret', 'Live Secret', function () {
    ?>
        <input type="password" name="paypal_live_secret" value="<?= esc_attr(get_option('paypal_live_secret')); ?>" class="regular-text" />
<?php
    }, 'paypal-settings', 'paypal_main_section');

    // ✅ Referral Commission Percentage
    add_settings_field('paypal_referral_commission', 'Referral Commission (%)', function () {
        $commission = get_option('paypal_referral_commission', '0');
    ?>
        <input type="number" name="paypal_referral_commission" value="<?= esc_attr($commission); ?>" class="small-text" min="0" max="100" step="0.1" /> %
        <p class="description">Enter the referral commission percentage. Example: 10 = 10%</p>
    <?php
    }, 'paypal-settings', 'paypal_main_section');

    // Webhook ID (for subscription events)
    add_settings_field('paypal_webhook_id', 'Webhook ID', function () {
        $wh = get_option('paypal_webhook_id', '');
    ?>
        <input type="text" name="paypal_webhook_id" value="<?= esc_attr($wh); ?>" class="regular-text" placeholder="WH-XXXXXXXXXXX" />
        <p class="description">
            Create a webhook in the PayPal dashboard pointing to:
            <code><?= esc_url(home_url('/?mm_paypal_webhook=1')); ?></code><br>
            Subscribe to: <code>BILLING.SUBSCRIPTION.ACTIVATED, CANCELLED, EXPIRED, SUSPENDED</code> and <code>PAYMENT.SALE.COMPLETED</code>.
        </p>
    <?php
    }, 'paypal-settings', 'paypal_main_section');

});

// ======================
// 2️⃣ PayPal API Credentials Helper
// ======================

function get_paypal_api_credentials()
{
    $env = get_option('paypal_environment', 'sandbox');
    return [
        'client_id' => $env === 'live' ? get_option('paypal_live_client_id') : get_option('paypal_sandbox_client_id'),
        'secret'    => $env === 'live' ? get_option('paypal_live_secret') : get_option('paypal_sandbox_secret'),
        'api_url'   => $env === 'live' ? 'https://api.paypal.com' : 'https://api.sandbox.paypal.com',
    ];
}

// Load PayPal REST API helpers (access token, products, plans, subscriptions)
require_once __DIR__ . '/paypal-api.php';

// ======================
// 3️⃣ Handle AJAX Purchase
// ======================

add_action('wp_ajax_handle_course_purchase', 'handle_course_purchase');
add_action('wp_ajax_nopriv_handle_course_purchase', 'handle_course_purchase');

function handle_course_purchase()
{
    $course_id       = intval($_POST['course_id']);
    $amount          = sanitize_text_field($_POST['amount']);
    $order_id        = sanitize_text_field($_POST['orderID'] ?? '');
    $variation_index = isset($_POST['variation_index']) ? intval($_POST['variation_index']) : -1;
    $variation_label = sanitize_text_field($_POST['variation_label'] ?? '');

    if (!$order_id) {
        wp_send_json_error(['message' => 'Missing PayPal order ID']);
    }

    // -----------------------------------------------------------
    // Server-side variation price check (defense against tampered amount)
    // -----------------------------------------------------------
    if ($variation_index >= 0 && function_exists('mm_get_course_variation')) {
        $variation = mm_get_course_variation($course_id, $variation_index);
        if (!$variation) {
            wp_send_json_error(['message' => 'Invalid variation selected']);
        }
        if (floatval($variation['price']) !== floatval($amount)) {
            wp_send_json_error(['message' => 'Amount does not match selected variation price']);
        }
        // Override label with server-stored value
        $variation_label = $variation['label'];
    }

    $paypal = get_paypal_api_credentials();

    // Get Access Token
    $response = wp_remote_post("{$paypal['api_url']}/v1/oauth2/token", [
        'headers' => [
            'Authorization' => 'Basic ' . base64_encode("{$paypal['client_id']}:{$paypal['secret']}"),
        ],
        'body' => ['grant_type' => 'client_credentials'],
    ]);

    if (is_wp_error($response)) wp_send_json_error(['message' => 'PayPal API connection error']);

    $body = json_decode(wp_remote_retrieve_body($response), true);
    $access_token = $body['access_token'] ?? '';

    if (!$access_token) wp_send_json_error(['message' => 'PayPal access token missing']);

    // Get Order Details
    $order_response = wp_remote_get("{$paypal['api_url']}/v2/checkout/orders/{$order_id}", [
        'headers' => [
            'Authorization' => "Bearer $access_token",
            'Content-Type' => 'application/json',
        ]
    ]);

    if (is_wp_error($order_response)) wp_send_json_error(['message' => 'Unable to verify PayPal order']);

    $order_data = json_decode(wp_remote_retrieve_body($order_response), true);

    if (($order_data['status'] ?? '') === 'COMPLETED' &&
        floatval($order_data['purchase_units'][0]['amount']['value'] ?? 0) == floatval($amount)
    ) {
        // -----------------------------------------------------------
        // Persist purchase (legacy-compatible)
        // -----------------------------------------------------------
        $user_id = get_current_user_id();
        if ($user_id) {
            // Legacy array: list of course IDs purchased
            $purchased = get_user_meta($user_id, 'purchased_courses', true);
            if (!is_array($purchased)) $purchased = [];
            if (!in_array($course_id, $purchased, true)) {
                $purchased[] = $course_id;
                update_user_meta($user_id, 'purchased_courses', $purchased);
            }

            // Detailed log: which variation, how much, when, PayPal order
            $log = get_user_meta($user_id, 'course_purchase_log', true);
            if (!is_array($log)) $log = [];
            $log[] = [
                'course_id'       => $course_id,
                'variation_index' => $variation_index,
                'variation_label' => $variation_label,
                'amount'          => $amount,
                'order_id'        => $order_id,
                'timestamp'       => current_time('mysql'),
            ];
            update_user_meta($user_id, 'course_purchase_log', $log);
        }

        wp_send_json_success([
            'message'         => 'Payment verified, course purchased',
            'variation_label' => $variation_label,
        ]);
    } else {
        wp_send_json_error(['message' => 'Payment verification failed']);
    }

    wp_die();
}

// ======================
// 4️⃣ Handle Subscription Activation (monthly/yearly)
// ======================

add_action('wp_ajax_handle_course_subscription', 'handle_course_subscription');
add_action('wp_ajax_nopriv_handle_course_subscription', 'handle_course_subscription');

function handle_course_subscription()
{
    $course_id       = intval($_POST['course_id']);
    $variation_index = isset($_POST['variation_index']) ? intval($_POST['variation_index']) : -1;
    $subscription_id = sanitize_text_field($_POST['subscriptionID'] ?? '');

    if (!$subscription_id || $variation_index < 0) {
        wp_send_json_error(['message' => 'Missing subscription data']);
    }

    if (!function_exists('mm_get_course_variation')) {
        wp_send_json_error(['message' => 'Variation system unavailable']);
    }

    $variation = mm_get_course_variation($course_id, $variation_index);
    if (!$variation) {
        wp_send_json_error(['message' => 'Invalid variation']);
    }

    $billing = $variation['billing'] ?? 'onetime';
    if ($billing === 'onetime') {
        wp_send_json_error(['message' => 'Variation is not a subscription']);
    }

    // Verify with PayPal
    $sub = mm_pp_get_subscription($subscription_id);
    if (is_wp_error($sub)) {
        wp_send_json_error([
            'message' => 'Could not verify subscription: ' . $sub->get_error_message(),
        ]);
    }

    $status = $sub['status'] ?? '';
    if (!in_array($status, ['ACTIVE', 'APPROVAL_PENDING', 'APPROVED'], true)) {
        wp_send_json_error(['message' => "Subscription not active (status: $status)"]);
    }

    // Verify that the subscription's plan_id matches the variation's plan_id
    $sub_plan = $sub['plan_id'] ?? '';
    if (!empty($variation['plan_id']) && $sub_plan && $sub_plan !== $variation['plan_id']) {
        wp_send_json_error(['message' => 'Subscription plan mismatch']);
    }

    // Persist
    $user_id = get_current_user_id();
    if ($user_id) {
        // Legacy: still mark course as purchased so the "already purchased" UI hides the button
        $purchased = get_user_meta($user_id, 'purchased_courses', true);
        if (!is_array($purchased)) $purchased = [];
        if (!in_array($course_id, $purchased, true)) {
            $purchased[] = $course_id;
            update_user_meta($user_id, 'purchased_courses', $purchased);
        }

        // Track active subscriptions separately so we can handle cancel/renew later
        $subs = get_user_meta($user_id, 'active_subscriptions', true);
        if (!is_array($subs)) $subs = [];
        $subs[$subscription_id] = [
            'course_id'       => $course_id,
            'variation_index' => $variation_index,
            'variation_label' => $variation['label'],
            'billing'         => $billing,
            'price'           => $variation['price'],
            'plan_id'         => $variation['plan_id'] ?? '',
            'status'          => $status,
            'started'         => current_time('mysql'),
        ];
        update_user_meta($user_id, 'active_subscriptions', $subs);

        // Also log in the purchase log
        $log = get_user_meta($user_id, 'course_purchase_log', true);
        if (!is_array($log)) $log = [];
        $log[] = [
            'type'            => 'subscription',
            'course_id'       => $course_id,
            'variation_index' => $variation_index,
            'variation_label' => $variation['label'],
            'billing'         => $billing,
            'amount'          => $variation['price'],
            'subscription_id' => $subscription_id,
            'timestamp'       => current_time('mysql'),
        ];
        update_user_meta($user_id, 'course_purchase_log', $log);
    }

    wp_send_json_success([
        'message'         => 'Subscription activated',
        'subscription_id' => $subscription_id,
        'status'          => $status,
    ]);
}

// ======================
// 5️⃣ Cancel a Subscription (user-initiated)
// ======================

add_action('wp_ajax_cancel_course_subscription', 'cancel_course_subscription');

function cancel_course_subscription()
{
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Not logged in']);
    }
    check_ajax_referer('mm_sub_cancel', 'nonce');

    $subscription_id = sanitize_text_field($_POST['subscription_id'] ?? '');
    $reason          = sanitize_text_field($_POST['reason'] ?? 'User requested cancellation');
    if (!$subscription_id) {
        wp_send_json_error(['message' => 'Missing subscription id']);
    }

    $user_id = get_current_user_id();
    $subs    = get_user_meta($user_id, 'active_subscriptions', true);
    if (!is_array($subs) || !isset($subs[$subscription_id])) {
        wp_send_json_error(['message' => 'Subscription not found for this user']);
    }

    // Call PayPal to cancel
    $res = mm_pp_request(
        'POST',
        '/v1/billing/subscriptions/' . rawurlencode($subscription_id) . '/cancel',
        ['reason' => $reason]
    );
    if (is_wp_error($res)) {
        // PayPal returns empty body on success; WP_Error is returned only on real failure.
        wp_send_json_error(['message' => 'PayPal cancel failed: ' . $res->get_error_message()]);
    }

    // Update local state immediately (webhook will also fire later)
    $subs[$subscription_id]['status']  = 'CANCELLED';
    $subs[$subscription_id]['updated'] = current_time('mysql');
    update_user_meta($user_id, 'active_subscriptions', $subs);

    if (function_exists('mm_pp_revoke_course_access_if_no_active_sub')) {
        mm_pp_revoke_course_access_if_no_active_sub($user_id, $subscription_id);
    }

    wp_send_json_success(['message' => 'Subscription cancelled']);
}
?>