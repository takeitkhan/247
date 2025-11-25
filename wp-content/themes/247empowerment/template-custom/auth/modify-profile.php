<?php
/* Template Name: Modify Profile */
get_header_based_on_login();

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

$current_user = wp_get_current_user();

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


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!isset($_POST['frontend_profile_update_nonce']) || !wp_verify_nonce($_POST['frontend_profile_update_nonce'], 'frontend_profile_update')) {
        echo '<div class="alert alert-danger">Security check failed.</div>';
    } else {
        $result = handle_frontend_profile_update($current_user->ID, $_POST);

        // Trigger points modal only
        if (!empty($result['notifications'])) {
            echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                var notifications = ' . json_encode($result['notifications']) . ';
                notifications.forEach(function(notif) {
                    openPointsModal(notif);
                });
            });
            </script>';
        }
    }
}



?>

<div class="container profile-page pt20">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3">
            <?php get_template_part('template-custom/auth/common-parts/editprofilemenu', null, ['profile' => $profile]); ?>
            <?php get_template_part('template-custom/auth/profile-parts/navlink', null, ['profile' => $profile]); ?>
        </div>
        <div class="mb-0 rounded-end-0 col-lg-6">
            <div class="bg-white custom-card post-search">
                <div class="gap-3 post-row">
                    <div>
                        <h5 class="pb-4 text-start portal-title">Update Profile</h5>
                    </div>

                    <div>
                        <form method="post" class="row g-3">
                            <?php wp_nonce_field('frontend_profile_update', 'frontend_profile_update_nonce'); ?>

                            <div class="col-12 col-md-6">
                                <label class="form-label">First Name:</label>
                                <input type="text" name="first_name" class="form-control input"
                                    value="<?php echo esc_attr($current_user->first_name); ?>" placeholder="Enter your first name" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Last Name:</label>
                                <input type="text" name="last_name" class="form-control input"
                                    value="<?php echo esc_attr($current_user->last_name); ?>" placeholder="Enter your last name" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Email:</label>
                                <input type="email" name="email" class="form-control input"
                                    value="<?php echo esc_attr($current_user->user_email); ?>" placeholder="Enter your email" required>
                                <div class="mt-2 form-check">
                                    <input type="checkbox" class="form-check-input" id="show_email" name="show_email" value="1"
                                        <?php checked(get_user_meta($current_user->ID, 'show_email', true), true); ?>>
                                    <label class="form-check-label" for="show_email">Show Email on profile</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">Phone number:</label>
                                <input type="text" name="phone" class="form-control input"
                                    value="<?php echo esc_attr($current_user->phone); ?>" placeholder="Enter your phone number" required>
                                <div class="mt-2 form-check">
                                    <input type="checkbox" class="form-check-input" id="show_phone" name="show_phone" value="1"
                                        <?php checked(get_user_meta($current_user->ID, 'show_phone', true), true); ?>>
                                    <label class="form-check-label" for="show_phone">Show phone number on profile</label>
                                </div>
                            </div>

                            <!-- DOB -->
                            <div class="col-12 col-md-6">
                                <label class="form-label">Date of Birth:</label>
                                <input type="date" name="dob" class="form-control input" 
                                    value="<?php echo esc_attr(get_user_meta($current_user->ID, 'dob', true)); ?>">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label">About me (one liner):</label>
                                <input type="text" name="about_me_short" class="form-control input"
                                    value="<?php echo esc_attr(get_user_meta($current_user->ID, 'about_me_short', true)); ?>" 
                                        placeholder="About me" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label">About me (full description):</label>
                                <div class="position-relative">
                                    <textarea name="about_me" class="form-control input" rows="5"
                                        placeholder="What's on your mind?"><?php echo esc_textarea(get_user_meta($current_user->ID, 'about_me', true)); ?></textarea>
                                    <img class="modal-emoji" src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/imogi.png" alt="">
                                </div>
                            </div>

                            <!-- <div id="place-autocomplete-card">
                                <p>Search for a place here:</p>
                            </div>

                            <div id="map" style="height: 300px; border-radius: 10px;"></div> -->

                            <div class="col-12">
                                <label class="form-label">Latitude:</label>
                                <input type="text" name="latitude" id="latitude" class="form-control input"
                                    value="<?php echo esc_attr(get_user_meta($current_user->ID, 'latitude', true)); ?>" placeholder="Enter latitude">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Longitude:</label>
                                <input type="text" name="longitude" id="longitude" class="form-control input"
                                    value="<?php echo esc_attr(get_user_meta($current_user->ID, 'longitude', true)); ?>" placeholder="Enter longitude">
                            </div>

                            <!-- Location -->
                            <div class="col-12 col-md-6">
                                <label class="form-label">Location Name:</label>
                                <input type="text" name="place_display_name" class="form-control input"
                                    value="<?php echo esc_attr(get_user_meta($current_user->ID, 'place_display_name', true)); ?>">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Full Address:</label>
                                <input type="text" name="location" id="location" class="form-control input"
                                    value="<?php echo esc_attr(get_user_meta($current_user->ID, 'location', true)); ?>" placeholder="Enter your full address information">
                                <div class="mt-2 form-check">
                                    <input class="form-check-input" type="checkbox" id="show_full_address" name="show_full_address" value="1"
                                        <?php checked(get_user_meta($current_user->ID, 'show_full_address', true), '1'); ?>>
                                    <label class="form-check-label" for="show_full_address">Show full address on profile</label>
                                </div>
                            </div>

                            
                            <div class="col-12">
                                <label class="form-label">Referrer:</label>
                                <input type="text" class="form-control input" value="<?php echo esc_attr($referrer ?? 'No Referrer'); ?>" disabled>
                            </div>                            


                            <div class="col-12">
                                <label class="form-label">Choose Focus Categories:</label>
                                <div class="row">
                                    <?php
                                    $categories = get_categories(['hide_empty' => false]);
                                    $selected_cats = get_user_meta($current_user->ID, 'user_categories', true);
                                    $selected_cats = is_array($selected_cats) ? $selected_cats : [];

                                    $half = ceil(count($categories) / 2);
                                    $chunks = array_chunk($categories, $half);

                                    foreach ($chunks as $chunk) {
                                        echo '<div class="col-12 col-md-6">';
                                        foreach ($chunk as $cat) {
                                            echo '<div class="mb-2 form-check">';
                                            echo '<input class="form-check-input" type="checkbox" name="user_categories[]" value="' . esc_attr($cat->term_id) . '" ' . checked(in_array($cat->term_id, $selected_cats), true, false) . '>';
                                            echo '<label class="form-check-label">' . esc_html($cat->name) . '</label>';
                                            echo '</div>';
                                        }
                                        echo '</div>';
                                    }
                                    ?>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-3 mt-3 col-12">
                                <!-- <button type="button" class="w-auto text-blue-color custom-btn-size" data-bs-dismiss="modal">Cancel</button> -->
                                <button type="submit" name="update_profile" class="w-auto custom-btn">Update</button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="rounded-start-0 col-lg-3">
            <?php get_template_part('template-custom/auth/editprofile-parts/profile-photo-form', null, ['profile' => $profile, 'user' => $user]); ?>
        </div>
    </div>
</div>
<script>
    jQuery(document).ready(function($) {
        $('#frontend-profile-form').on('submit', function(e) {
            e.preventDefault();

            var formData = $(this).serializeArray();
            formData.push({
                name: 'nonce',
                value: $('input[name="frontend_profile_update_nonce"]').val()
            });

            $.ajax({
                url: '<?php echo admin_url("admin-ajax.php"); ?>',
                method: 'POST',
                data: $.param(formData) + '&action=frontend_profile_update',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message || 'Profile updated'); // simple fallback
                        if (response.data.notifications && response.data.notifications.length) {
                            response.data.notifications.forEach(function(notif) {
                                openPointsModal(notif); // call your existing modal function
                            });
                        }
                    } else {
                        alert(response.data.message || 'Error occurred');
                    }
                },
                error: function() {
                    alert('AJAX error');
                }
            });
        });
    });
</script>

<?php get_footer_based_on_login(); ?>