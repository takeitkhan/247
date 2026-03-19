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
    <div class="card border-0 shadow-sm">
        <!-- Header -->
        <div class="card-header bg-white border-bottom">
            <h4 class="mb-0">
                <i class="bi bi-share2 me-2"></i>
                Connect Social Media Accounts
            </h4>
            <small class="text-muted d-block mt-1">
                Link your Facebook and LinkedIn accounts to share posts automatically
            </small>
        </div>

        <!-- Card Body -->
        <div class="card-body p-4">
            <div class="row g-4">
                <!-- Facebook Connect -->
                <div class="col-md-6">
                    <div class="social-connect-card border rounded-3 p-4 h-100" style="background: #f5f5f5;">
                        <!-- Icon -->
                        <div class="d-flex align-items-center mb-3">
                            <div class="social-icon" style="width: 50px; height: 50px; background: #1877f2; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-facebook text-white" style="font-size: 24px;"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="mb-0">Facebook</h5>
                                <small class="text-muted">Share to your Facebook profile</small>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="status-section mb-3">
                            <?php if ($facebook_connected): ?>
                                <div class="alert alert-success py-2 px-3 mb-2" role="alert">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <strong>Connected</strong>
                                    <?php if (!empty($facebook_name)): ?>
                                        <br><small class="d-block mt-1">Account: <?php echo esc_html($facebook_name); ?></small>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning py-2 px-3 mb-2" role="alert">
                                    <i class="bi bi-exclamation-circle me-2"></i>
                                    Not connected
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Action Button -->
                        <div class="action-buttons">
                            <?php if ($facebook_connected): ?>
                                <button type="button" class="btn btn-outline-danger btn-sm w-100 disconnect-facebook" data-user-id="<?php echo esc_attr($user_id); ?>">
                                    <i class="bi bi-link-45deg me-2"></i>Disconnect Facebook
                                </button>
                            <?php else: ?>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=social-auth&provider=facebook')); ?>" class="btn btn-primary btn-sm w-100">
                                    <i class="bi bi-box-arrow-in-up-right me-2"></i>
                                    Connect with Facebook
                                </a>
                            <?php endif; ?>
                        </div>

                        <!-- Info -->
                        <small class="text-muted d-block mt-3">
                            <i class="bi bi-info-circle me-1"></i>
                            We'll only post content you explicitly choose to share
                        </small>
                    </div>
                </div>

                <!-- LinkedIn Connect -->
                <div class="col-md-6">
                    <div class="social-connect-card border rounded-3 p-4 h-100" style="background: #f5f5f5;">
                        <!-- Icon -->
                        <div class="d-flex align-items-center mb-3">
                            <div class="social-icon" style="width: 50px; height: 50px; background: #0a66c2; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-linkedin text-white" style="font-size: 24px;"></i>
                            </div>
                            <div class="ms-3">
                                <h5 class="mb-0">LinkedIn</h5>
                                <small class="text-muted">Share to your LinkedIn profile</small>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="status-section mb-3">
                            <?php if ($linkedin_connected): ?>
                                <div class="alert alert-success py-2 px-3 mb-2" role="alert">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <strong>Connected</strong>
                                    <?php if (!empty($linkedin_name)): ?>
                                        <br><small class="d-block mt-1">Account: <?php echo esc_html($linkedin_name); ?></small>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning py-2 px-3 mb-2" role="alert">
                                    <i class="bi bi-exclamation-circle me-2"></i>
                                    Not connected
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Action Button -->
                        <div class="action-buttons">
                            <?php if ($linkedin_connected): ?>
                                <button type="button" class="btn btn-outline-danger btn-sm w-100 disconnect-linkedin" data-user-id="<?php echo esc_attr($user_id); ?>">
                                    <i class="bi bi-link-45deg me-2"></i>Disconnect LinkedIn
                                </button>
                            <?php else: ?>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=social-auth&provider=linkedin')); ?>" class="btn btn-primary btn-sm w-100">
                                    <i class="bi bi-box-arrow-in-up-right me-2"></i>
                                    Connect with LinkedIn
                                </a>
                            <?php endif; ?>
                        </div>

                        <!-- Info -->
                        <small class="text-muted d-block mt-3">
                            <i class="bi bi-info-circle me-1"></i>
                            We'll only post content you explicitly choose to share
                        </small>
                    </div>
                </div>
            </div>

            <!-- Connected Accounts Summary -->
            <div class="mt-4 pt-4 border-top">
                <h6 class="mb-3">
                    <i class="bi bi-gear me-2"></i>Connected Accounts Summary
                </h6>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="p-3 rounded" style="background: #f8f9fa;">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-facebook" style="font-size: 20px; color: #1877f2; margin-right: 10px;"></i>
                                <div>
                                    <small class="d-block text-muted">Facebook</small>
                                    <strong><?php echo $facebook_connected ? '✓ Connected' : '✗ Not Connected'; ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded" style="background: #f8f9fa;">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-linkedin" style="font-size: 20px; color: #0a66c2; margin-right: 10px;"></i>
                                <div>
                                    <small class="d-block text-muted">LinkedIn</small>
                                    <strong><?php echo $linkedin_connected ? '✓ Connected' : '✗ Not Connected'; ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
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
    padding: 20px 0;
}

.social-connect-card {
    transition: all 0.3s ease;
}

.social-connect-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.social-icon {
    flex-shrink: 0;
}

.status-section {
    min-height: 60px;
    display: flex;
    align-items: center;
}
</style>
