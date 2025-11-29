
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
                    <ul class="nav nav-tabs fs-6" id="homeTab" role="tablist">
                        <?php
                        // Query to get the tabs
                        $tabs_query = new WP_Query(array(
                            'post_type' => 'tab',
                            'posts_per_page' => -1,
                            'orderby' => 'menu_order',
                            'order' => 'ASC'
                        ));

                        if ($tabs_query->have_posts()) :
                            $active_class = 'active'; // To add the active class to the first tab
                            while ($tabs_query->have_posts()) : $tabs_query->the_post();
                                // Get the custom fields
                                $tab_title = get_the_title();
                        ?>
                                <li class="nav-item" role="presentation">
                                    <button class="custom-tab-button <?php echo $active_class; ?>" id="<?php echo sanitize_title($tab_title); ?>-tab" data-bs-toggle="tab" data-bs-target="#<?php echo sanitize_title($tab_title); ?>" type="button" role="tab" aria-controls="<?php echo sanitize_title($tab_title); ?>" aria-selected="true"><?php echo esc_html($tab_title); ?></button>
                                </li>
                        <?php
                                $active_class = ''; // After the first tab, no need to add "active"
                            endwhile;
                        endif;
                        ?>
                    </ul>

                    <div class="p-3 text-left tab-content transparent-bg" id="homeTabContent">
                        <?php
                        // Reset the loop to fetch the content for each tab
                        if ($tabs_query->have_posts()) :
                            $active_class = 'show active'; // To add the active class to the first tab content
                            while ($tabs_query->have_posts()) : $tabs_query->the_post();
                                // Get the custom fields
                                $tab_title = get_the_title();
                                $tab_content = get_the_content(); // Get the raw content

                                // Allow specific tags like <p>, <br>, etc.
                                $allowed_tags = array(
                                    'p' => array(),
                                    'br' => array(),
                                    'strong' => array(),
                                    'em' => array(),
                                    'ul' => array(),
                                    'ol' => array(),
                                    'li' => array(),
                                    'blockquote' => array(),
                                    'a' => array(
                                        'href' => array(),
                                        'title' => array(),
                                        'target' => array(),
                                        'rel' => array(),
                                        'class' => array()
                                    ),
                                );
                                $tab_content = wp_kses($tab_content, $allowed_tags);
                        ?>
                                <div class="tab-pane fade <?php echo $active_class; ?>" id="<?php echo sanitize_title($tab_title); ?>" role="tabpanel" aria-labelledby="<?php echo sanitize_title($tab_title); ?>-tab">
                                    <div><?php echo wp_kses_post($tab_content); ?></div>
                                </div>
                        <?php
                                $active_class = ''; // After the first tab content, no need to add "show active"
                            endwhile;
                        endif;
                        ?>
                    </div>
            </div>
        </div>
        <div class="rounded-start-0 col-lg-3">
            <?php get_template_part('template-custom/auth/editprofile-parts/profile-photo-form', null, ['profile' => $profile, 'user' => $user]); ?>
        </div>
    </div>
</div>
<?php get_footer_based_on_login(); ?>