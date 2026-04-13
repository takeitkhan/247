<?php
/**
 * Template Name: Zoom Integration Page
 * Template Post Type: page
 * 
 * Universal Zoom Integration Page Template
 * Handles all Zoom-related content (Connect, Meetings, Book)
 */

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

get_header_based_on_login();

$current_user = wp_get_current_user();
$user_id = get_current_user_id();
$page_slug = basename(get_permalink());

// Determine which content to show based on page slug
$show_connect = in_array($page_slug, ['connect-zoom', 'zoom-connect']);
$show_meetings = in_array($page_slug, ['my-zoom-meetings', 'zoom-meetings']);
$show_book = in_array($page_slug, ['book-meeting', 'zoom-book']);

// Default to current page content if using shortcodes
$post_content = get_the_content();
?>

<div class="container profile-page pt-4 pb-4">
    <div class="row">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3">
            <?php 
            $profile = (new UserProfileData($current_user->ID))->getProfile();
            get_template_part('template-custom/auth/common-parts/editprofilemenu', null, ['profile' => $profile]); 
            ?>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <!-- Header Section -->
            <div class="bg-white rounded mb-4 p-4 border-bottom">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="mb-2">
                            <i class="bi bi-camera-video"></i>
                            Zoom Integration
                        </h3>
                        <p class="text-muted mb-0">
                            Connect your Zoom account and manage your meetings
                        </p>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="bg-white rounded p-4">
                <?php 
                // Display page-specific content or shortcodes
                if (!empty($post_content)) {
                    echo apply_filters('the_content', $post_content);
                } else {
                    // Fallback content based on URL
                    if ($show_connect) {
                        echo do_shortcode('[zoom_connect_button]');
                    } elseif ($show_meetings) {
                        echo do_shortcode('[zoom_upcoming_meetings]');
                    } elseif ($show_book) {
                        echo do_shortcode('[zoom_book_appointment]');
                    } else {
                        // Default: show all Zoom sections
                        echo '<div class="zoom-sections">';
                        echo '<h4 class="mb-4">Your Zoom Account</h4>';
                        echo do_shortcode('[zoom_connect_button]');
                        
                        echo '<hr class="my-5">';
                        echo '<h4 class="mb-4">Upcoming Meetings</h4>';
                        echo do_shortcode('[zoom_upcoming_meetings]');
                        echo '</div>';
                    }
                }
                ?>
            </div>

            <!-- Help Section -->
            <div class="zoom-help-section mt-5 pt-4 border-top">
                <div class="help-header text-center mb-4">
                    <h4 class="mb-2">
                        <i class="bi bi-lightbulb" style="color: #ffc107; font-size: 1.75rem;"></i>
                    </h4>
                    <h5 class="fw-600">Need Help?</h5>
                    <p class="text-muted small">Learn how to get started with Zoom integration</p>
                </div>

                <div class="row g-4">
                    <!-- How to Connect Card -->
                    <div class="col-lg-6">
                        <div class="help-card h-100">
                            <div class="help-card-header">
                                <i class="bi bi-link-45deg"></i>
                                How to Connect
                            </div>
                            <div class="help-card-body">
                                <div class="help-step">
                                    <div class="step-number">1</div>
                                    <div class="step-content">
                                        <p><strong>Click Connect Button</strong></p>
                                        <p class="text-muted small">Click the "Connect Zoom Account" button below</p>
                                    </div>
                                </div>
                                <div class="help-step">
                                    <div class="step-number">2</div>
                                    <div class="step-content">
                                        <p><strong>Zoom Sign In</strong></p>
                                        <p class="text-muted small">You'll be redirected to Zoom to sign in</p>
                                    </div>
                                </div>
                                <div class="help-step">
                                    <div class="step-number">3</div>
                                    <div class="step-content">
                                        <p><strong>Grant Permissions</strong></p>
                                        <p class="text-muted small">Approve the requested permissions (read meetings, create meetings)</p>
                                    </div>
                                </div>
                                <div class="help-step">
                                    <div class="step-number">4</div>
                                    <div class="step-content">
                                        <p><strong>All Set!</strong></p>
                                        <p class="text-muted small">Your account is now connected. Start managing your meetings!</p>
                                    </div>
                                </div>
                            </div>
                            <div class="help-card-footer">
                                <i class="bi bi-check-circle text-success"></i>
                                <span>Takes less than 2 minutes</span>
                            </div>
                        </div>
                    </div>

                    <!-- Your Privacy Card -->
                    <div class="col-lg-6">
                        <div class="help-card h-100">
                            <div class="help-card-header privacy">
                                <i class="bi bi-shield-check"></i>
                                Your Privacy & Security
                            </div>
                            <div class="help-card-body">
                                <div class="privacy-item">
                                    <div class="privacy-icon">
                                        <i class="bi bi-lock"></i>
                                    </div>
                                    <div class="privacy-content">
                                        <p><strong>Encrypted Storage</strong></p>
                                        <p class="text-muted small">Your Zoom tokens are encrypted with AES-256 and safely stored</p>
                                    </div>
                                </div>

                                <div class="privacy-item">
                                    <div class="privacy-icon">
                                        <i class="bi bi-key"></i>
                                    </div>
                                    <div class="privacy-content">
                                        <p><strong>No Password Storage</strong></p>
                                        <p class="text-muted small">We never store your Zoom password. OAuth 2.0 handles authentication</p>
                                    </div>
                                </div>

                                <div class="privacy-item">
                                    <div class="privacy-icon">
                                        <i class="bi bi-hand-thumbs-up"></i>
                                    </div>
                                    <div class="privacy-content">
                                        <p><strong>You Control Access</strong></p>
                                        <p class="text-muted small">Disconnect anytime from your profile. Permissions are revoked instantly</p>
                                    </div>
                                </div>

                                <div class="privacy-item">
                                    <div class="privacy-icon">
                                        <i class="bi bi-shield-lock"></i>
                                    </div>
                                    <div class="privacy-content">
                                        <p><strong>Industry Standard</strong></p>
                                        <p class="text-muted small">We follow OAuth 2.0 and data protection best practices</p>
                                    </div>
                                </div>
                            </div>
                            <div class="help-card-footer privacy">
                                <i class="bi bi-check-circle text-success"></i>
                                <span>Your data is secure</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Zoom Help Section Styles */
.zoom-help-section {
    margin-top: 3rem;
}

.help-header {
    padding-bottom: 2rem;
}

.help-header h5 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #212529;
}

/* Help Card Styling */
.help-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    display: flex;
    flex-direction: column;
}

.help-card:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    transform: translateY(-4px);
    border-color: #dee2e6;
}

.help-card-header {
    background: linear-gradient(135deg, #0a66c2 0%, #005b96 100%);
    color: white;
    padding: 1.5rem;
    font-size: 1.1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.help-card-header.privacy {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
}

.help-card-header i {
    font-size: 1.5rem;
}

.help-card-body {
    padding: 1.5rem;
    flex: 1;
}

/* Steps */
.help-step {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    align-items: flex-start;
}

.help-step:last-child {
    margin-bottom: 0;
}

.step-number {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #0a66c2 0%, #005b96 100%);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1rem;
    min-width: 40px;
}

.step-content p {
    margin: 0;
    line-height: 1.5;
}

.step-content p:first-child {
    font-size: 0.95rem;
    font-weight: 500;
    color: #212529;
}

.step-content p:last-child {
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

/* Privacy Items */
.privacy-item {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    align-items: flex-start;
}

.privacy-item:last-child {
    margin-bottom: 0;
}

.privacy-icon {
    flex-shrink: 0;
    width: 44px;
    height: 44px;
    background-color: #e8f5e9;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #28a745;
    font-size: 1.25rem;
    min-width: 44px;
}

.privacy-content p {
    margin: 0;
    line-height: 1.5;
}

.privacy-content p:first-child {
    font-size: 0.95rem;
    font-weight: 500;
    color: #212529;
}

.privacy-content p:last-child {
    font-size: 0.85rem;
    margin-top: 0.25rem;
}

/* Card Footer */
.help-card-footer {
    background-color: #f8f9fa;
    padding: 1rem 1.5rem;
    border-top: 1px solid #e9ecef;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    font-weight: 500;
    color: #495057;
}

.help-card-footer i {
    font-size: 1rem;
}

.help-card-footer.privacy {
    background-color: #f1f8f5;
    border-top-color: #d4edda;
}

/* Responsive Design */
@media (max-width: 992px) {
    .help-card-header {
        padding: 1.25rem;
        font-size: 1rem;
    }

    .help-card-body {
        padding: 1.25rem;
    }

    .step-number,
    .privacy-icon {
        width: 36px;
        height: 36px;
        min-width: 36px;
    }

    .step-number {
        font-size: 0.9rem;
    }
}

@media (max-width: 768px) {
    .help-header {
        padding-bottom: 1.5rem;
    }

    .help-header h5 {
        font-size: 1.25rem;
    }

    .help-step,
    .privacy-item {
        margin-bottom: 1.25rem;
    }

    .step-content p:first-child,
    .privacy-content p:first-child {
        font-size: 0.9rem;
    }
}
</style>

<?php get_footer_based_on_login(); ?>
