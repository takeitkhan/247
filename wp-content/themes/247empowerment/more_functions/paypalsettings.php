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

// ======================
// 3️⃣ Handle AJAX Purchase
// ======================

add_action('wp_ajax_handle_course_purchase', 'handle_course_purchase');
add_action('wp_ajax_nopriv_handle_course_purchase', 'handle_course_purchase');

function handle_course_purchase()
{
    $course_id = intval($_POST['course_id']);
    $amount    = sanitize_text_field($_POST['amount']);
    $order_id  = sanitize_text_field($_POST['orderID'] ?? '');

    if (!$order_id) {
        wp_send_json_error(['message' => 'Missing PayPal order ID']);
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

        // ✅ Payment verified
        // Do your course enrollment logic here
        wp_send_json_success(['message' => 'Payment verified, course purchased']);
    } else {
        wp_send_json_error(['message' => 'Payment verification failed']);
    }

    wp_die();
}
?>