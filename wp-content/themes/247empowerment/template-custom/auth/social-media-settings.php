<?php
/**
 * Template Name: Social Media Settings
 * Template Post Type: page
 * 
 * Displays the interface for users to connect and manage
 * their Facebook and LinkedIn accounts for social posting
 */

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

get_header_based_on_login();
?>

<div class="container profile-page pt20 pb20">
    <div class="row">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3">
            <?php 
            $current_user = wp_get_current_user();
            $profile = (new UserProfileData($current_user->ID))->getProfile();
            get_template_part('template-custom/auth/common-parts/editprofilemenu', null, ['profile' => $profile]); 
            ?>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <!-- Breadcrumb / Header -->
            <div class="bg-white rounded mb-4 p-3">
                <h4 class="mb-0">
                    <i class="bi bi-share2 me-2"></i>
                    Connect Social Media Accounts
                </h4>
                <small class="text-muted d-block mt-2">
                    Link your Facebook and LinkedIn accounts to share your posts automatically across social platforms
                </small>
            </div>

            <!-- Social Media Connect Component -->
            <?php get_template_part('template-custom/auth/profile-parts/social-media-connect'); ?>

            <!-- Help Section -->
            <div class="bg-light rounded mt-5 p-4">
                <h5 class="mb-3">
                    <i class="bi bi-question-circle me-2"></i>
                    How to Connect Your Accounts
                </h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <span style="color: #1877f2;">f</span> Facebook
                                </h6>
                                <ol style="font-size: 13px; padding-left: 20px;">
                                    <li>Click "Connect with Facebook" button</li>
                                    <li>Log in with your Facebook account</li>
                                    <li>Allow permissions for posting</li>
                                    <li>You're ready to share!</li>
                                </ol>
                                <p class="text-muted small mt-2 mb-0">
                                    <i class="bi bi-info-circle me-1"></i>
                                    We'll never post without your permission
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <span style="color: #0a66c2;">in</span> LinkedIn
                                </h6>
                                <ol style="font-size: 13px; padding-left: 20px;">
                                    <li>Click "Connect with LinkedIn" button</li>
                                    <li>Log in with your LinkedIn account</li>
                                    <li>Allow permissions for posting</li>
                                    <li>You're ready to share!</li>
                                </ol>
                                <p class="text-muted small mt-2 mb-0">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Perfect for professional networking
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="bg-white rounded mt-4 p-4">
                <h5 class="mb-3">
                    <i class="bi bi-chat-dots me-2"></i>
                    Frequently Asked Questions
                </h5>
                <div class="accordion" id="socialMediaFAQ">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Why do I need to connect my social accounts?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#socialMediaFAQ">
                            <div class="accordion-body">
                                Connecting your social accounts allows you to share your posts directly to Facebook and LinkedIn with a single click. This saves time and helps you reach a wider audience across platforms.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Is it safe to connect my accounts?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#socialMediaFAQ">
                            <div class="accordion-body">
                                Yes! We use industry-standard OAuth 2.0 authentication. Your passwords are never stored - we only get permission to post on your behalf. You can disconnect at any time.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Can I disconnect my accounts?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#socialMediaFAQ">
                            <div class="accordion-body">
                                Absolutely! You can disconnect your Facebook or LinkedIn account at any time from this page. Once disconnected, we won't be able to post to that platform until you reconnect.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                What happens when I share a post?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#socialMediaFAQ">
                            <div class="accordion-body">
                                When you create a post, you'll see options to share it to Facebook and/or LinkedIn. If you check those boxes and click publish, the post will be automatically shared to your connected accounts with a link back to the original post.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                What if I encounter an error?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#socialMediaFAQ">
                            <div class="accordion-body">
                                If you experience issues connecting your accounts, please check the <strong>Debug Logs</strong> page in the WordPress Admin panel. This will show you any OAuth-related error messages. You can also try disconnecting and reconnecting.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.accordion-button {
    font-weight: 500;
}

.accordion-button:not(.collapsed) {
    background-color: #f8f9fa;
    color: #000;
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
}
</style>

<?php get_footer_based_on_login(); ?>
