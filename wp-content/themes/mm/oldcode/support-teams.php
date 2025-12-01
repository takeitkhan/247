
<?php

/**
 * Template Name: Support Teams
 * Custom Support Teams Page Template
 */
get_header_based_on_login();

// Get current logged-in user ID (used as a fallback if no slug is provided)
$current_user_id = get_current_user_id();

// 1. Get the user slug from the query variable
$user_slug = get_query_var('user_profile');

// 2. Determine the target user
if ($user_slug) {
    // If a slug is present, try to get the user by their slug (login or nicename)
    $user = get_user_by('slug', $user_slug);
} else {
    // If no slug, fall back to the currently logged-in user
    $user = get_user_by('ID', $current_user_id);
}

// 3. Instantiate the UserProfileData class and get the profile array
if ($user) {
    // We pass the WP_User object to the class constructor, or the ID/slug depending on the class's constructor.
    // Given your original line: $profile = (new UserProfileData($user_slug))->getProfile();
    // We'll update it to pass the $user object for better data handling, assuming the class supports it.
    // If the class REQUIRES a slug, use $user_slug or $user->user_login.

    // Option A: If UserProfileData takes a WP_User object (Recommended)
    $profile_data_instance = new UserProfileData($user);

    // Option B: If UserProfileData only takes the slug (Sticking closer to your original code)
    // Use the slug if present, otherwise use the current user's login.
    $target_identifier = $user_slug ? $user_slug : $user->user_login;
    $profile_data_instance = new UserProfileData($target_identifier);

    // Get the profile array
    $profile = $profile_data_instance->getProfile();
} else {
    // Set variables to null if no user could be determined
    $user = null;
    $profile = null;
}
?>
<div class="container profile-page pt20">
    <div class="row">
        <div class="col-lg-3">
            <?php get_template_part('template-custom/auth/feed-parts/profile-card', null, ['profile' => $profile]); ?>
            <?php get_template_part('template-custom/auth/profile-parts/navlink', null, ['profile' => $profile]); ?>
        </div>
        <div class="mb-4 col-lg-6">
            <div class="bg-white custom-box-shadow mb-3 p-3 custom-border-radius">                
                <h2 class='mb-4 text-start title'>Support Teams</h2>
                <p class='mb-4 text-start'>Together we grow stronger — find your team and start your journey.</p>
                
                <?php
                // Replace 123 with the actual Page ID where FAQ content is stored
                $faq_page_id = 18;
                $faq_page = get_post($faq_page_id);

                if ($faq_page) {
                    //echo '<h2 class="mb-4 text-center">' . esc_html(get_the_title($faq_page)) . '</h2>';
                    echo '<div class="faq-text">' . apply_filters('the_content', $faq_page->post_content) . '</div>';
                } else {
                    echo '<p class="text-danger">FAQ content not found. Please set the correct page ID.</p>';
                }
                ?>
            </div>
        </div>
        <div class="rounded-start-0 col-lg-3">
            <?php get_template_part('template-custom/auth/editprofile-parts/profile-photo-form', null, ['profile' => $profile, 'user' => $user]); ?>
        </div>
    </div>
</div>
<?php get_footer_based_on_login(); ?>