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
            <div class="bg-white rounded mb-4 p-4">
                <div class="mb-0">
                    <h4 class="mb-2">
                        <i class="bi bi-share2 me-2"></i>
                        Connect Social Media Accounts
                    </h4>
                    <p class="text-muted mb-0 small">
                        Link your Facebook and LinkedIn accounts to share your posts automatically across social platforms
                    </p>
                </div>
            </div>

            <!-- Social Media Connect Component -->
            <?php get_template_part('template-custom/auth/profile-parts/social-media-connect'); ?>

            <!-- Help Section -->
            <div class="help-section mt-5">
                <div class="help-header">
                    <h5 class="help-title">
                        <i class="bi bi-lightbulb"></i>
                        How to Connect Your Accounts
                    </h5>
                    <p class="help-subtitle">Follow these simple steps to connect your social accounts</p>
                </div>

                <div class="help-cards-grid">
                    <!-- Facebook Instructions -->
                    <div class="help-card facebook-help">
                        <div class="help-card-icon">
                            <i class="bi bi-facebook"></i>
                        </div>
                        <h6 class="help-card-title">Connect Facebook</h6>
                        
                        <div class="steps-container">
                            <div class="step">
                                <div class="step-number">1</div>
                                <p>Click "Connect Now" button above</p>
                            </div>
                            <div class="step">
                                <div class="step-number">2</div>
                                <p>Log in with your Facebook account</p>
                            </div>
                            <div class="step">
                                <div class="step-number">3</div>
                                <p>Review and approve the permissions</p>
                            </div>
                            <div class="step">
                                <div class="step-number">4</div>
                                <p>You're all set! Start sharing posts</p>
                            </div>
                        </div>

                        <div class="help-card-footer">
                            <p class="footer-text">
                                <i class="bi bi-shield-check"></i>
                                We'll never post without your permission
                            </p>
                        </div>
                    </div>

                    <!-- LinkedIn Instructions -->
                    <div class="help-card linkedin-help">
                        <div class="help-card-icon">
                            <i class="bi bi-linkedin"></i>
                        </div>
                        <h6 class="help-card-title">Connect LinkedIn</h6>
                        
                        <div class="steps-container">
                            <div class="step">
                                <div class="step-number">1</div>
                                <p>Click "Connect Now" button above</p>
                            </div>
                            <div class="step">
                                <div class="step-number">2</div>
                                <p>Log in with your LinkedIn account</p>
                            </div>
                            <div class="step">
                                <div class="step-number">3</div>
                                <p>Review and approve the permissions</p>
                            </div>
                            <div class="step">
                                <div class="step-number">4</div>
                                <p>You're all set! Start sharing posts</p>
                            </div>
                        </div>

                        <div class="help-card-footer">
                            <p class="footer-text">
                                <i class="bi bi-briefcase"></i>
                                Perfect for professional networking
                            </p>
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
/* Help Section Styling */
.help-section {
    margin-top: 3rem;
}

.help-header {
    text-align: center;
    margin-bottom: 2.5rem;
    padding-bottom: 2rem;
    border-bottom: 2px solid #e9ecef;
}

.help-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #212529;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
}

.help-title i {
    color: #ffc107;
    font-size: 1.75rem;
}

.help-subtitle {
    color: #6c757d;
    font-size: 1rem;
    margin: 0;
}

/* Help Cards Grid */
.help-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2rem;
}

/* Individual Help Card */
.help-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    flex-direction: column;
    height: 100%;
}

.help-card:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    transform: translateY(-4px);
}

/* Help Card Icon */
.help-card-icon {
    width: 100%;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: white;
}

.facebook-help .help-card-icon {
    background: linear-gradient(135deg, #1877f2 0%, #0a66f2 100%);
}

.linkedin-help .help-card-icon {
    background: linear-gradient(135deg, #0a66c2 0%, #004182 100%);
}

/* Help Card Title */
.help-card-title {
    padding: 1.5rem 1.5rem 0.75rem 1.5rem;
    font-size: 1.25rem;
    font-weight: 600;
    color: #212529;
    margin: 0;
}

/* Steps Container */
.steps-container {
    padding: 0 1.5rem 1.5rem 1.5rem;
    flex: 1;
}

.step {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
    align-items: flex-start;
}

.step:last-child {
    margin-bottom: 0;
}

.step-number {
    flex-shrink: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-weight: 600;
    font-size: 0.95rem;
    color: white;
    min-width: 32px;
}

.facebook-help .step-number {
    background-color: #1877f2;
}

.linkedin-help .step-number {
    background-color: #0a66c2;
}

.step p {
    margin: 0;
    font-size: 0.95rem;
    color: #495057;
    line-height: 1.5;
    padding-top: 0.25rem;
}

/* Help Card Footer */
.help-card-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #e9ecef;
    background-color: #f8f9fa;
}

.footer-text {
    margin: 0;
    font-size: 0.9rem;
    color: #495057;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.facebook-help .footer-text i {
    color: #1877f2;
}

.linkedin-help .footer-text i {
    color: #0a66c2;
}

/* Accordion Button Styling */
.accordion-button {
    font-weight: 500;
    transition: all 0.3s ease;
}

.accordion-button:not(.collapsed) {
    background-color: #f8f9fa;
    color: #000;
    box-shadow: none;
    border-bottom: 1px solid #dee2e6;
}

.accordion-button:hover {
    background-color: #f8f9fa;
}

/* Card Hover Effects */
.card {
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
}

/* Responsive Design */
@media (max-width: 768px) {
    .help-cards-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }

    .help-header {
        padding-bottom: 1.5rem;
        margin-bottom: 2rem;
    }

    .help-title {
        font-size: 1.25rem;
    }

    .help-subtitle {
        font-size: 0.95rem;
    }
}

@media (max-width: 480px) {
    .help-card-icon {
        padding: 1rem;
    }

    .help-card-title {
        padding: 1rem 1rem 0.5rem 1rem;
        font-size: 1.1rem;
    }

    .steps-container {
        padding: 0 1rem 1rem 1rem;
    }

    .step {
        gap: 0.75rem;
        margin-bottom: 0.75rem;
    }

    .help-card-footer {
        padding: 0.75rem 1rem;
    }
}
</style>

<?php get_footer_based_on_login(); ?>
