<?php
/**
 * PayPal API helpers — access token, Products, Billing Plans.
 * Used by store-variations.php (admin save) and paypalsettings.php (purchase handler).
 */

if (!defined('ABSPATH')) exit;

/**
 * Get an OAuth access token. Cached in a transient for its lifetime.
 *
 * @return string|WP_Error
 */
function mm_pp_get_access_token()
{
    $cached = get_transient('mm_pp_access_token');
    if ($cached) return $cached;

    $cred = get_paypal_api_credentials();
    if (empty($cred['client_id']) || empty($cred['secret'])) {
        return new WP_Error('paypal_missing_creds', 'PayPal credentials are not configured.');
    }

    $resp = wp_remote_post("{$cred['api_url']}/v1/oauth2/token", [
        'headers' => [
            'Authorization' => 'Basic ' . base64_encode("{$cred['client_id']}:{$cred['secret']}"),
        ],
        'body'    => ['grant_type' => 'client_credentials'],
        'timeout' => 20,
    ]);

    if (is_wp_error($resp)) return $resp;

    $body = json_decode(wp_remote_retrieve_body($resp), true);
    if (empty($body['access_token'])) {
        return new WP_Error('paypal_no_token', 'Could not fetch PayPal access token', $body);
    }

    $ttl = max(60, intval($body['expires_in'] ?? 3600) - 60);
    set_transient('mm_pp_access_token', $body['access_token'], $ttl);
    return $body['access_token'];
}

/**
 * Make an authenticated PayPal API call.
 */
function mm_pp_request($method, $path, $payload = null)
{
    $token = mm_pp_get_access_token();
    if (is_wp_error($token)) return $token;

    $cred = get_paypal_api_credentials();
    $args = [
        'method'  => strtoupper($method),
        'headers' => [
            'Authorization' => "Bearer $token",
            'Content-Type'  => 'application/json',
            'Prefer'        => 'return=representation',
        ],
        'timeout' => 30,
    ];
    if ($payload !== null) {
        $args['body'] = wp_json_encode($payload);
    }

    $resp = wp_remote_request("{$cred['api_url']}{$path}", $args);
    if (is_wp_error($resp)) return $resp;

    $code = wp_remote_retrieve_response_code($resp);
    $body = json_decode(wp_remote_retrieve_body($resp), true);

    if ($code < 200 || $code >= 300) {
        return new WP_Error('paypal_api_error', "PayPal API {$code}", $body);
    }
    return $body;
}

/**
 * Get (or create) a PayPal Catalog Product for a course. Result cached in post meta.
 */
function mm_pp_get_or_create_product($course_id)
{
    $existing = get_post_meta($course_id, '_paypal_product_id', true);
    if ($existing) return $existing;

    $post = get_post($course_id);
    if (!$post) return new WP_Error('no_course', 'Course not found');

    $payload = [
        'name'        => mb_substr($post->post_title ?: 'Course', 0, 127),
        'description' => mb_substr(wp_strip_all_tags(get_field('short_details', $course_id) ?: $post->post_title), 0, 256),
        'type'        => 'SERVICE',
        'category'    => 'EDUCATIONAL_TEXTBOOKS',
    ];

    $res = mm_pp_request('POST', '/v1/catalogs/products', $payload);
    if (is_wp_error($res)) return $res;

    $product_id = $res['id'] ?? '';
    if (!$product_id) return new WP_Error('paypal_no_product_id', 'No product id returned', $res);

    update_post_meta($course_id, '_paypal_product_id', $product_id);
    return $product_id;
}

/**
 * Create a Billing Plan for a variation.
 *
 * @param string $product_id PayPal catalog product id
 * @param string $label       Plan name (shown on PayPal)
 * @param float  $price       Price
 * @param string $interval    'MONTH' or 'YEAR'
 * @return string|WP_Error    Plan id
 */
function mm_pp_create_plan($product_id, $label, $price, $interval)
{
    $payload = [
        'product_id'  => $product_id,
        'name'        => mb_substr($label, 0, 127),
        'description' => mb_substr("{$label} subscription", 0, 127),
        'status'      => 'ACTIVE',
        'billing_cycles' => [[
            'frequency'       => [
                'interval_unit'  => $interval,
                'interval_count' => 1,
            ],
            'tenure_type'     => 'REGULAR',
            'sequence'        => 1,
            'total_cycles'    => 0, // infinite
            'pricing_scheme'  => [
                'fixed_price' => [
                    'value'         => number_format((float)$price, 2, '.', ''),
                    'currency_code' => 'USD',
                ],
            ],
        ]],
        'payment_preferences' => [
            'auto_bill_outstanding'     => true,
            'setup_fee'                 => ['value' => '0', 'currency_code' => 'USD'],
            'setup_fee_failure_action'  => 'CONTINUE',
            'payment_failure_threshold' => 3,
        ],
    ];

    $res = mm_pp_request('POST', '/v1/billing/plans', $payload);
    if (is_wp_error($res)) return $res;

    $id = $res['id'] ?? '';
    if (!$id) return new WP_Error('paypal_no_plan_id', 'No plan id returned', $res);
    return $id;
}

/**
 * Verify a PayPal Subscription by ID. Returns the subscription object on success.
 */
function mm_pp_get_subscription($subscription_id)
{
    return mm_pp_request('GET', '/v1/billing/subscriptions/' . rawurlencode($subscription_id));
}
