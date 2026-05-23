<?php
/**
 * Template Name: Zoom Integration Page
 * Template Post Type: page
 * 
 * Universal Zoom Integration Page Template
 * Dynamically loads shortcodes based on page slug
 */

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

get_header_based_on_login();

$current_user = wp_get_current_user();
$user_id = get_current_user_id();
$page_slug = basename(get_permalink());
// Clean up trailing slashes
$page_slug = trim($page_slug, '/');

// Dynamic page title mapping
$page_titles = [
    'connect-zoom-account' => 'Connect Zoom Account',
    'zoom-meetings' => 'My Zoom Meetings',
    'zoom-booking' => 'Book a Meeting',
    'zoom-search' => 'Search Meetings',
    'zoom-contacts' => 'Manage Contacts',
    'zoom-calendar' => 'Meeting Calendar',
    'zoom-all-meetings' => 'All Meetings & Archive',
    'zoom-zak-token' => 'Web SDK Token',
    'zoom-my-bookings' => 'My Bookings',
    'zoom-members' => 'Member Directory',
    'zoom-cancel-meeting' => 'Cancel Meeting',
    'zoom-reschedule-meeting' => 'Reschedule Meeting',
    'zoom-meeting-details' => 'Meeting Details',
];

$page_title = $page_titles[$page_slug] ?? 'Zoom Integration';
$page_subtitle = get_post_meta(get_the_ID(), '_page_subtitle', true) ?? 'Manage your Zoom meetings and integrations';

// Mapping of page slugs to shortcodes
$shortcode_mapping = [
    'connect-zoom'      => '[zoom_connect_button]',
    'connect-zoom-account'      => '[zoom_connect_button]',
    'my-zoom-meetings'  => '[zoom_all_meetings]',
    'zoom-meetings'     => '[zoom_all_meetings]',
    'book-meeting'      => '[zoom_book_appointment]',
    'zoom-book'         => '[zoom_book_appointment]',
    'search-meetings'   => '[zoom_search_meetings]',
    'zoom-search'       => '[zoom_search_meetings]',
    'zoom-contacts'     => '[zoom_show_contacts]',
    'cancel-meeting'    => '[zoom_cancel_meeting]',
    'reschedule-meeting' => '[zoom_reschedule_meeting]',
    'meeting-details'   => '[zoom_meeting_details]',
    'zoom-token'        => '[zoom_zak_token]',
];

// Get custom shortcode from page meta (if set)
$custom_shortcode = get_post_meta(get_the_ID(), '_zoom_shortcode', true);

// Determine which shortcode to display
$shortcode_to_display = $custom_shortcode ?: ($shortcode_mapping[$page_slug] ?? '');

// Get default page content
$post_content = get_the_content();
?>

<div class="pt-4 pb-4 container profile-page">
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
            <div class="bg-white mb-4 p-4 border-bottom rounded">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="mb-2">
                            <i class="bi bi-camera-video"></i>
                            <?php echo esc_html($page_title); ?>
                        </h3>
                        <p class="mb-0 text-muted">
                            <?php echo esc_html($page_subtitle); ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="bg-white p-4 rounded">
                <?php 
                // Display content based on priority:
                // 1. Custom shortcode meta field
                // 2. Auto-mapped shortcode from slug
                // 3. Page custom content
                // 4. Default: show all sections
                
                if (!empty($shortcode_to_display)) {
                    // Display mapped or custom shortcode
                    echo do_shortcode($shortcode_to_display);
                } elseif (!empty($post_content)) {
                    // Display page custom content
                    echo apply_filters('the_content', $post_content);
                } else {
                    // Default: show all Zoom sections
                    echo '<div class="zoom-sections">';
                    echo '<h4 class="mb-4">🔗 Your Zoom Account</h4>';
                    echo do_shortcode('[zoom_connect_button]');
                    
                    echo '<hr class="my-5">';
                    echo '<h4 class="mb-4">📅 Upcoming Meetings</h4>';
                    echo do_shortcode('[zoom_all_meetings]');
                    echo '</div>';
                }
                ?>
            </div>

            <!-- Help Link -->
            <div class="mt-5 pt-4 border-top text-center">
                <p class="text-muted">Need help? <a href="<?php echo site_url('/zoom-help/'); ?>" class="text-decoration-none">Visit our Help Center</a></p>
            </div>
        </div>
    </div>
</div>

<style>
/* Zoom Page Styles */
</style>

<?php get_footer_based_on_login(); ?>
