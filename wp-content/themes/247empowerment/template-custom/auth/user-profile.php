<?php

/**
 * Template Name: User Profile Template
 */

if (!is_user_logged_in()) {
    include __DIR__ . '/profile-parts/shareable-profile.php';
    return;
} else {
    if (is_user_logged_in()) {
        get_header('portal');
    } else {
        get_header('main');
    }

    // Get the user slug (username) from the URL
    $user_slug = get_query_var('user_profile');
    $user = get_user_by('slug', $user_slug);  // Get user by slug
    $profile = (new UserProfileData($user_slug))->getProfile();
    $social_links = $profile['social_links'] ?? [];

    if ($user) :
?>
        <div class="container profile-page pt20">
            <div class="bg-white custom-card">
                <!-- Cover Photo Section -->
                <div class="profile-top">
                    <div class="position-relative profile_img">
                        <img class="w-100 h-100 object-object-fit-cover banner-img"
                            src="<?php echo esc_url(get_user_meta($user->ID, 'profile_cover_photo', true) ?: get_template_directory_uri() . '/assets/img/loggedin_images/banner.jpg'); ?>"
                            alt="Cover Photo">

                        <?php if (is_user_logged_in()) : ?>
                            <button class="position-absolute d-flex align-items-center gap-2 bg-white text-black custom-btn-size edit-cover-photo">
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/camera.png" alt="">
                                <span class="d-md-block d-none">Edit cover photo</span>
                                <form id="cover-upload-form" enctype="multipart/form-data" style="display:none;">
                                    <input type="file" name="cover_photo" id="cover-photo-input" accept="image/*" />
                                </form>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Profile Header Section -->
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-between profile-section">
                    <div class="d-flex flex-column flex-md-row align-items-center gap-3">
                        <div class="position-relative profile-img-size img177">
                            <img src="<?php echo esc_url(get_user_meta($user->ID, 'profile_photo', true) ?: get_template_directory_uri() . '/assets/img/loggedin_images/profile_default.png'); ?>"
                                class="rounded-circle w-100 h-100 object-fit-cover img-p"
                                id="profile-photo-preview"
                                alt="Profile">

                            <?php if (is_user_logged_in()) : ?>
                                <label for="profile-photo-upload" class="position-absolute active-icon" title="Change Profile Photo">
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/profile_camera.png" alt="">
                                </label>
                                <input type="file" id="profile-photo-upload" accept="image/*" style="display:none;">
                            <?php endif; ?>
                        </div>

                        <div class="d-flex flex-column align-items-center align-items-lg-start post-user">
                            <span class="profile-title">
                                <?php echo esc_html($profile['first_name'] . ' ' . $profile['last_name']); ?>
                            </span>

                            <?php
                            $referredUsers = $profile['referred_users'];
                            $referralCount = count($referredUsers);
                            ?>
                            <span class='mt-1 p-r fw-medium'>
                                <span><?php echo esc_html($referralCount); ?></span> referral partner<?php echo $referralCount === 1 ? '' : 's'; ?>
                            </span>
                        </div>
                    </div>

                    <div>
                        <?php if (is_user_logged_in()) : ?>
                            <a href="<?php echo site_url('/modify-profile/'); ?>" class="d-inline-flex align-items-center m-lg-0 mt-3 custom-btn-size custom-button">
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/pen.png" class="me-2" alt="">
                                Edit your profile
                            </a>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col-lg-3">

                    <div class="profile-left bg-white custom-card">
                        <div class="d-flex align-items-center justify-content-between pb-4 u-title">
                            <h5 class="portal-title">About</h5>
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/post_option_icon.png" alt="">
                        </div>
                        <ul class="d-flex flex-column gap-2 nav">
                            <?php if (!empty($profile['location'])) : ?>
                                <li class="d-flex align-items-center gap-2">
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/location_p.png" class="icon-img" alt="Location">
                                    <a href="#" class="p-0 p-link"><?php echo esc_html($profile['location']); ?></a>
                                </li>
                            <?php endif; ?>

                            <?php if (!empty($profile['occupation'])) : ?>
                                <li class="d-flex align-items-center gap-2">
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/bag.png" class="icon-img" alt="Occupation">
                                    <a href="#" class="p-0 p-link"><?php echo esc_html($profile['occupation']); ?></a>
                                </li>
                            <?php endif; ?>

                            <?php if (!empty($profile['website'])) : ?>
                                <li class="d-flex align-items-center justify-content-between gap-2">
                                    <div class="d-flex gap-2">
                                        <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/link.png" class="icon-img" alt="Website">
                                        <a href="<?php echo esc_url($profile['website']); ?>" target="_blank" class="p-0 p-link text-primary">
                                            <?php echo esc_html(wp_trim_words($profile['website'], 3, '...')); ?>
                                        </a>
                                    </div>
                                    <img class="icon-img" src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/copy.png" alt="Copy">
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="bg-white custom-card navbar-link">
                        <?php
                        wp_nav_menu([
                            'theme_location' => 'profilemenu',
                            'container'      => false,
                            'menu_class'     => 'nav d-flex flex-column gap-2',
                            'walker'         => new Profile_Menu_Walker(),
                        ]);
                        ?>
                    </div>

                </div>
                <div class="mb-4 col-lg-6">
                    <?php get_template_part('template-custom/auth/profile-parts/create-post', null, ['profile' => $profile]); ?>

                    <?php get_template_part('template-custom/auth/profile-parts/posts', null, ['profile' => $profile]); ?>
                </div>
                <div class="col-lg-3">
                    <div class="bg-white upcoming-events custom-card">
                        <div class="pb-4">
                            <h5 class="text-start portal-title">Referral Partners</h5>

                        </div>
                        <div class="d-flex flex-column gap-3">
                            <div>
                                <div class="">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="position-relative img44">
                                            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/profile.png" class="rounded-circle w-100 h-100 object-fit-cover" alt="Profile">

                                        </div>
                                        <div class="d-flex flex-column post-user">
                                            <span class="p_name">Maria Johnson</span>
                                            <p class="n-text">Los Angeles, CA</p>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div>
                                <div class="">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="position-relative img44">
                                            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/profile.png" class="rounded-circle w-100 h-100 object-fit-cover" alt="Profile">

                                        </div>
                                        <div class="d-flex flex-column post-user">
                                            <span class="p_name">Maria Johnson</span>
                                            <p class="n-text">Los Angeles, CA</p>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div>
                                <div class="">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="position-relative img44">
                                            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/profile.png" class="rounded-circle w-100 h-100 object-fit-cover" alt="Profile">
                                        </div>
                                        <div class="d-flex flex-column post-user">
                                            <span class="p_name">Maria Johnson</span>
                                            <p class="n-text">Los Angeles, CA</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <button class="d-flex align-items-center justify-content-center gap-2 pt-3 w-100 more-option"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/loading.png" alt=""> More</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
    else :
        echo '<p>User not found.</p>';
    endif;
    ?>
    <script>
        document.querySelector('.edit-cover').addEventListener('click', () => {
            document.getElementById('cover-photo-input').click();
        });

        document.getElementById('cover-photo-input').addEventListener('change', function() {
            const formData = new FormData();
            formData.append('action', 'upload_cover_photo');
            formData.append('cover_photo', this.files[0]);

            fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData
                })
                .then(res => res.json())
                .then(response => {
                    console.log(response); // Debug
                    if (response.success && response.data && response.data.url) {
                        document.querySelector('.banner-img').src = response.data.url;
                    } else {
                        alert('Upload failed: ' + (response.data?.message || 'Unknown error'));
                    }
                });

        });

        document.getElementById('profile-photo-upload').addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('action', 'upload_profile_photo');
            formData.append('profile_photo', file);

            fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData
                })
                .then(res => res.json())
                .then(response => {
                    if (response.success && response.data?.url) {
                        document.getElementById('profile-photo-preview').src = response.data.url;
                    } else {
                        alert('Upload failed: ' + (response.data?.message || 'Unknown error'));
                    }
                });
        });
    </script>
    <?php
    if (is_user_logged_in()) {
        get_footer('portal');
    } else {
        get_footer('main');
    }
    ?>
<?php
}
