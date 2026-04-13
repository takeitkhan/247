<?php
/**
 * Social Media Connect Settings
 * 
 * Allows users to connect their Facebook and LinkedIn accounts
 * for cross-posting functionality
 */

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

$current_user = wp_get_current_user();
$user_id = get_current_user_id();

// Get current connection statuses
$facebook_user_id = get_user_meta($user_id, '_facebook_user_id', true);
$facebook_connected = !empty($facebook_user_id);

$linkedin_user_id = get_user_meta($user_id, '_linkedin_user_id', true);
$linkedin_connected = !empty($linkedin_user_id);

// Get user names for display
$facebook_name = get_user_meta($user_id, '_facebook_user_name', true);
$linkedin_name = get_user_meta($user_id, '_linkedin_user_name', true);
?>

<div class="social-media-connect-container">
    <!-- Social Connect Cards Grid -->
    <div class="social-connect-grid">
        <!-- Facebook Connect Card -->
        <div class="social-connect-card facebook-card">
            <div class="card-header-icon facebook-icon">
                <i class="bi bi-facebook"></i>
            </div>
            
            <div class="card-content">
                <h5 class="card-title">Facebook</h5>
                <p class="card-subtitle">Share to your Facebook profile</p>
                
                <!-- Status Badge -->
                <div class="status-badge <?php echo $facebook_connected ? 'connected' : 'disconnected'; ?>">
                    <i class="bi <?php echo $facebook_connected ? 'bi-check-circle-fill' : 'bi-exclamation-circle'; ?>"></i>
                    <span><?php echo $facebook_connected ? 'Connected' : 'Not Connected'; ?></span>
                    <?php if ($facebook_connected && !empty($facebook_name)): ?>
                        <div class="account-name"><?php echo esc_html($facebook_name); ?></div>
                    <?php endif; ?>
                </div>
                
                <!-- Action Button -->
                <div class="action-button">
                    <?php if ($facebook_connected): ?>
                        <button type="button" class="btn btn-disconnect disconnect-facebook" data-user-id="<?php echo esc_attr($user_id); ?>">
                            <i class="bi bi-link-45deg"></i>
                            Disconnect
                        </button>
                    <?php else: ?>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=social-auth&provider=facebook')); ?>" class="btn btn-connect">
                            <i class="bi bi-box-arrow-in-up-right"></i>
                            Connect Now
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Info Text -->
                <p class="info-text">
                    <i class="bi bi-info-circle"></i>
                    We'll only post content you explicitly choose to share
                </p>
            </div>
        </div>

        <!-- LinkedIn Connect Card -->
        <div class="social-connect-card linkedin-card">
            <div class="card-header-icon linkedin-icon">
                <i class="bi bi-linkedin"></i>
            </div>
            
            <div class="card-content">
                <h5 class="card-title">LinkedIn</h5>
                <p class="card-subtitle">Share to your LinkedIn profile</p>
                
                <!-- Status Badge -->
                <div class="status-badge <?php echo $linkedin_connected ? 'connected' : 'disconnected'; ?>">
                    <i class="bi <?php echo $linkedin_connected ? 'bi-check-circle-fill' : 'bi-exclamation-circle'; ?>"></i>
                    <span><?php echo $linkedin_connected ? 'Connected' : 'Not Connected'; ?></span>
                    <?php if ($linkedin_connected && !empty($linkedin_name)): ?>
                        <div class="account-name"><?php echo esc_html($linkedin_name); ?></div>
                    <?php endif; ?>
                </div>
                
                <!-- Action Button -->
                <div class="action-button">
                    <?php if ($linkedin_connected): ?>
                        <button type="button" class="btn btn-disconnect disconnect-linkedin" data-user-id="<?php echo esc_attr($user_id); ?>">
                            <i class="bi bi-link-45deg"></i>
                            Disconnect
                        </button>
                    <?php else: ?>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=social-auth&provider=linkedin')); ?>" class="btn btn-connect">
                            <i class="bi bi-box-arrow-in-up-right"></i>
                            Connect Now
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Info Text -->
                <p class="info-text">
                    <i class="bi bi-info-circle"></i>
                    Perfect for professional networking
                </p>
            </div>
        </div>
    </div>

    <!-- Connected Accounts Summary -->
    <div class="accounts-summary mt-5 pt-4 border-top">
        <h6 class="summary-title">
            <i class="bi bi-gear"></i>
            Connected Accounts Overview
        </h6>
        <div class="summary-grid">
            <div class="summary-item facebook-summary">
                <i class="bi bi-facebook"></i>
                <div class="summary-content">
                    <p class="summary-label">Facebook</p>
                    <p class="summary-status <?php echo $facebook_connected ? 'status-connected' : 'status-disconnected'; ?>">
                        <i class="bi <?php echo $facebook_connected ? 'bi-check-circle-fill' : 'bi-x-circle-fill'; ?>"></i>
                        <?php echo $facebook_connected ? 'Connected' : 'Not Connected'; ?>
                    </p>
                </div>
            </div>
            <div class="summary-item linkedin-summary">
                <i class="bi bi-linkedin"></i>
                <div class="summary-content">
                    <p class="summary-label">LinkedIn</p>
                    <p class="summary-status <?php echo $linkedin_connected ? 'status-connected' : 'status-disconnected'; ?>">
                        <i class="bi <?php echo $linkedin_connected ? 'bi-check-circle-fill' : 'bi-x-circle-fill'; ?>"></i>
                        <?php echo $linkedin_connected ? 'Connected' : 'Not Connected'; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Disconnect -->
<script>
(function($) {
    'use strict';

    $(document).ready(function() {
        // Disconnect Facebook
        $(document).on('click', '.disconnect-facebook', function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to disconnect Facebook? You won\'t be able to share posts to Facebook until you reconnect.')) {
                const userId = $(this).data('user-id');
                
                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        action: 'disconnect_social_account',
                        provider: 'facebook',
                        user_id: userId,
                        nonce: '<?php echo wp_create_nonce('disconnect_social'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Facebook account disconnected successfully');
                            location.reload();
                        } else {
                            alert('Error: ' + (response.data?.message || 'Failed to disconnect'));
                        }
                    },
                    error: function() {
                        alert('Error disconnecting account');
                    }
                });
            }
        });

        // Disconnect LinkedIn
        $(document).on('click', '.disconnect-linkedin', function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to disconnect LinkedIn? You won\'t be able to share posts to LinkedIn until you reconnect.')) {
                const userId = $(this).data('user-id');
                
                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        action: 'disconnect_social_account',
                        provider: 'linkedin',
                        user_id: userId,
                        nonce: '<?php echo wp_create_nonce('disconnect_social'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('LinkedIn account disconnected successfully');
                            location.reload();
                        } else {
                            alert('Error: ' + (response.data?.message || 'Failed to disconnect'));
                        }
                    },
                    error: function() {
                        alert('Error disconnecting account');
                    }
                });
            }
        });
    });
})(jQuery);
</script>

<style>
.social-media-connect-container {
    width: 100%;
}

/* Social Connect Grid */
.social-connect-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

/* Individual Card Styling */
.social-connect-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.social-connect-card:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    transform: translateY(-4px);
    border-color: #dee2e6;
}

/* Card Header Icon */
.card-header-icon {
    width: 100%;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: white;
}

.facebook-icon {
    background: linear-gradient(135deg, #1877f2 0%, #0a66f2 100%);
}

.linkedin-icon {
    background: linear-gradient(135deg, #0a66c2 0%, #004182 100%);
}

/* Card Content */
.card-content {
    padding: 2rem;
}

.card-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #212529;
}

.card-subtitle {
    font-size: 0.95rem;
    color: #6c757d;
    margin-bottom: 1.5rem;
}

/* Status Badge */
.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    font-weight: 500;
    margin-bottom: 1.5rem;
    font-size: 0.95rem;
}

.status-badge.connected {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.status-badge.disconnected {
    background-color: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.status-badge i {
    font-size: 1.1rem;
}

.account-name {
    font-size: 0.85rem;
    margin-top: 0.5rem;
    opacity: 0.9;
    font-weight: 500;
}

/* Action Button */
.action-button {
    margin-bottom: 1.5rem;
}

.btn {
    border: none;
    padding: 0.75rem 1.25rem;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    text-decoration: none;
    cursor: pointer;
}

.btn-connect {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
}

.btn-connect:hover {
    background: linear-gradient(135deg, #0056b3 0%, #003d82 100%);
    color: white;
    transform: translateY(-2px);
}

.btn-disconnect {
    background-color: #f8f9fa;
    color: #dc3545;
    border: 1.5px solid #dc3545;
}

.btn-disconnect:hover {
    background-color: #dc3545;
    color: white;
    transform: translateY(-2px);
}

/* Info Text */
.info-text {
    font-size: 0.85rem;
    color: #6c757d;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.info-text i {
    color: #0d6efd;
}

/* Connected Accounts Summary */
.accounts-summary {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 12px;
}

.summary-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #212529;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.summary-item {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 1rem;
    border-left: 4px solid;
}

.facebook-summary {
    border-left-color: #1877f2;
}

.facebook-summary i {
    color: #1877f2;
    font-size: 1.75rem;
}

.linkedin-summary {
    border-left-color: #0a66c2;
}

.linkedin-summary i {
    color: #0a66c2;
    font-size: 1.75rem;
}

.summary-content {
    flex: 1;
}

.summary-label {
    font-size: 0.85rem;
    color: #6c757d;
    margin: 0 0 0.5rem 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.summary-status {
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.status-connected {
    color: #28a745;
}

.status-disconnected {
    color: #dc3545;
}

/* Responsive Design */
@media (max-width: 768px) {
    .social-connect-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }

    .card-content {
        padding: 1.5rem;
    }

    .summary-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .card-header-icon {
        padding: 1rem;
    }

    .card-title {
        font-size: 1.25rem;
    }

    .card-subtitle {
        font-size: 0.9rem;
    }
}
</style>
