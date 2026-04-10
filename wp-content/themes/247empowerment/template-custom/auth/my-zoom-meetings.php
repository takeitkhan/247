<?php
/* Template Name: My Zoom Meetings */

// Check if user is logged in
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

// Get header
get_header();

// Get current user info
$user_id = get_current_user_id();
?>

<div class="container profile-page pt20">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3">
            <?php 
            // Load sidebar menu
            if (function_exists('get_template_part')) {
                get_template_part('template-custom/auth/common-parts/editprofilemenu');
                get_template_part('template-custom/auth/profile-parts/navlink');
            }
            ?>
        </div>

        <!-- Main Content -->
        <div class="mb-0 rounded-end-0 col-lg-6">
            <div class="bg-white custom-card post-search">
                <div class="gap-3 post-row">
                    <div>
                        <h5 class="pb-4 text-start portal-title">📹 My Zoom Meetings</h5>
                    </div>
                    
                    <div class="w-100">
                        <?php 
                        // Display Zoom meetings
                        echo do_shortcode('[zoom_upcoming_meetings]');
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// Get footer
get_footer();
?>