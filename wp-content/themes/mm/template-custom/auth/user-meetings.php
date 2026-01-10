<?php
/* Template Name: User Meetings Page */
get_header();

$user_slug = get_query_var('user_profile');
$user = get_user_by('slug', $user_slug);

if (!$user) {
    echo '<h2>User not found.</h2>';
    get_footer();
    exit;
}

// Optional: custom user data
$profile = (new UserProfileData($user_slug))->getProfile();
?>
<main>
    <div class="main-container" style="padding-top: 80px">
        <div class="row g-3">
            <?php include get_template_directory() . '/template-custom/auth/profile-parts/edit-profile-left-sidebar.php'; ?>

            <div class="ms-md-auto col-12 col-md-8 col-lg-9 col-xl-9">
                <div class="bg-white custom-box-shadow mb-3 p-3 custom-border-radius">
                    <div class="row">
                        <div class="col-6">
                            <h5 class="mb-5">📹 My Zoom Meetings</h5>
                        </div>
                    </div>
                    <?php
                    // echo '<pre>';
                    // print_r($profile);
                    // echo '</pre>';
                    // Your shortcode
                    echo do_shortcode('[zoom_user_meetings user_id="' . esc_attr($profile['id']) . '"]');
                    ?>
                </div>
            </div>
        </div>
    </div>
    </div>
</main>

<?php
get_footer();
