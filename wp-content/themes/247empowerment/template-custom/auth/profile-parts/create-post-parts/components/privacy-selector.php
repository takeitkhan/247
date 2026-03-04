<?php
/**
 * Privacy Selector Component
 * Variables (optional):
 * - $privacy_prefix: Suffix for IDs when multiple instances needed (e.g., '-schedule')
 */

$privacy_prefix = isset($privacy_prefix) ? $privacy_prefix : '';
?>

<div class="">
    <label class="form-label fw-bold mb-2">
        <i class="bi bi-shield-check me-2"></i>Who can see this?
    </label>
    <div class="privacy-options" id="privacyOptionsContainer">
        <!-- Only Me -->
        <div class="privacy-option">
            <input type="radio" name="post_privacy" id="privacy-only-me<?php echo esc_attr($privacy_prefix); ?>" value="only_me" data-audience-label="Only Me" checked>
            <label for="privacy-only-me<?php echo esc_attr($privacy_prefix); ?>" class="privacy-label">
                <span class="privacy-icon">
                    <i class="bi bi-lock-fill"></i>
                </span>
                <span class="privacy-text">
                    <strong>Only Me</strong>
                    <small class="d-block text-muted">Private, only you can see</small>
                </span>
            </label>
        </div>

        <!-- Referral Partners -->
        <div class="privacy-option">
            <input type="radio" name="post_privacy" id="privacy-referral<?php echo esc_attr($privacy_prefix); ?>" value="referral_partners" data-audience-label="Shared with Partners">
            <label for="privacy-referral<?php echo esc_attr($privacy_prefix); ?>" class="privacy-label">
                <span class="privacy-icon">
                    <i class="bi bi-people-fill"></i>
                </span>
                <span class="privacy-text">
                    <strong>Referral Partners</strong>
                    <small class="d-block text-muted">Share with your network</small>
                </span>
            </label>
        </div>

        <!-- Public -->
        <div class="privacy-option">
            <input type="radio" name="post_privacy" id="privacy-public<?php echo esc_attr($privacy_prefix); ?>" value="public" data-audience-label="Public">
            <label for="privacy-public<?php echo esc_attr($privacy_prefix); ?>" class="privacy-label">
                <span class="privacy-icon">
                    <i class="bi bi-globe"></i>
                </span>
                <span class="privacy-text">
                    <strong>Public</strong>
                    <small class="d-block text-muted">Anyone can see this</small>
                </span>
            </label>
        </div>
    </div>
</div>

