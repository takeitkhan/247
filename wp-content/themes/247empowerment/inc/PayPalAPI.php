<?php
/**
 * PayPal Payout API Integration
 * Handles sending payouts via PayPal
 */

class PayPalAPI {

    private $client_id;
    private $secret;
    private $mode;
    private $api_url;
    private $payout_system;

    public function __construct() {
        $this->client_id = get_option('payout_paypal_client_id', '');
        $this->secret = get_option('payout_paypal_secret', '');
        $this->mode = get_option('payout_paypal_mode', 'sandbox');
        $this->api_url = $this->mode === 'live' 
            ? 'https://api.paypal.com/v1'
            : 'https://api.sandbox.paypal.com/v1';
        
        $this->payout_system = new PayoutSystem();

        // Hook for processing withdrawals
        add_action('payout_process_paypal', [$this, 'process_withdrawal']);
    }

    /**
     * Get PayPal access token
     */
    public function get_access_token() {
        if (!$this->client_id || !$this->secret) {
            return new WP_Error('missing_credentials', 'PayPal credentials not configured');
        }

        $auth = base64_encode($this->client_id . ':' . $this->secret);

        $response = wp_remote_post($this->api_url . '/oauth2/token', [
            'headers' => [
                'Authorization' => 'Basic ' . $auth,
                'Accept' => 'application/json',
                'Accept-Language' => 'en_US',
            ],
            'body' => 'grant_type=client_credentials',
            'sslverify' => true,
            'timeout' => 10,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['error'])) {
            return new WP_Error('paypal_error', 'PayPal authentication failed: ' . $body['error_description']);
        }

        return $body['access_token'] ?? null;
    }

    /**
     * Process withdrawal payout
     */
    public function process_withdrawal($withdrawal_id) {
        $withdrawal = $this->payout_system->get_withdrawal($withdrawal_id);

        if (!$withdrawal) {
            return new WP_Error('not_found', 'Withdrawal not found');
        }

        // Get access token
        $token = $this->get_access_token();
        if (is_wp_error($token)) {
            $this->payout_system->log_audit($withdrawal_id, 'payout_failed', 'Token error: ' . $token->get_error_message());
            $this->payout_system->update_withdrawal_status($withdrawal_id, 'failed');
            do_action('payout_payment_failed', $withdrawal->user_id, $withdrawal_id, $token->get_error_message());
            return $token;
        }

        // Prepare payout request
        $payout_data = [
            'sender_batch_header' => [
                'sender_batch_id' => 'WITHDRAWAL_' . $withdrawal_id . '_' . time(),
                'email_subject' => 'You have received a PayPal payout from ' . get_bloginfo('name'),
                'email_message' => 'You have received a payout. Please log into your PayPal account to view details.',
            ],
            'items' => [
                [
                    'recipient_type' => 'EMAIL',
                    'amount' => [
                        'value' => number_format($withdrawal->amount, 2, '.', ''),
                        'currency' => 'USD',
                    ],
                    'description' => 'Withdrawal from ' . get_bloginfo('name'),
                    'sender_item_id' => 'ITEM_' . $withdrawal_id,
                    'receiver' => $withdrawal->paypal_email,
                ]
            ]
        ];

        // Send payout request
        $response = wp_remote_post($this->api_url . '/payments/payouts', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($payout_data),
            'sslverify' => true,
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            $this->payout_system->log_audit($withdrawal_id, 'payout_failed', 'API error: ' . $response->get_error_message());
            $this->payout_system->update_withdrawal_status($withdrawal_id, 'failed');
            do_action('payout_payment_failed', $withdrawal->user_id, $withdrawal_id, $response->get_error_message());
            return $response;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $http_code = wp_remote_retrieve_response_code($response);

        // Log response
        $this->payout_system->log_audit(
            $withdrawal_id, 
            'payout_response', 
            json_encode($body)
        );

        if ($http_code >= 400 || isset($body['name'])) {
            $error_msg = $body['message'] ?? $body['name'] ?? 'Unknown error';
            $this->payout_system->log_audit($withdrawal_id, 'payout_failed', $error_msg);
            $this->payout_system->update_withdrawal_status($withdrawal_id, 'failed');
            do_action('payout_payment_failed', $withdrawal->user_id, $withdrawal_id, $error_msg);
            return new WP_Error('paypal_error', $error_msg);
        }

        // Success
        $batch_id = $body['batch_header']['payout_batch_id'] ?? null;
        $this->payout_system->update_withdrawal_status($withdrawal_id, 'paid', $batch_id);
        $this->payout_system->log_audit($withdrawal_id, 'payout_success', 'Batch ID: ' . $batch_id);

        // Deduct from balance
        $user_id = $withdrawal->user_id;
        $current_balance = floatval(get_user_meta($user_id, 'referral_commission', true) ?: 0);
        $new_balance = max(0, $current_balance - $withdrawal->amount);
        update_user_meta($user_id, 'referral_commission', $new_balance);

        // Log to referral_logs
        $logs = get_user_meta($user_id, 'referral_logs', true) ?: [];
        $logs[] = [
            'type' => 'withdrawal',
            'amount' => -$withdrawal->amount,
            'date' => current_time('mysql'),
            'status' => 'paid',
            'withdrawal_id' => $withdrawal_id,
        ];
        update_user_meta($user_id, 'referral_logs', $logs);

        do_action('payout_payment_success', $user_id, $withdrawal_id, $withdrawal->amount, $batch_id);

        return [
            'success' => true,
            'batch_id' => $batch_id,
            'message' => 'Payout sent successfully'
        ];
    }

    /**
     * Get payout batch status (for checking if payment was received)
     */
    public function get_batch_status($batch_id) {
        $token = $this->get_access_token();
        if (is_wp_error($token)) {
            return $token;
        }

        $response = wp_remote_get($this->api_url . '/payments/payouts/' . $batch_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
            ],
            'sslverify' => true,
            'timeout' => 10,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }
}
