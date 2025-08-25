<?php
// Show guest layout if not logged in
if (!is_user_logged_in()) {
    include __DIR__ . '/profile-parts/shareable-profile.php';
    return;
} else {
?>

    <?php

    /**
     * Template Name: User Profile Template
     */

    get_header();

    // Get the user slug (username) from the URL
    $user_slug = get_query_var('user_profile');
    $user = get_user_by('slug', $user_slug);  // Get user by slug
    $profile = (new UserProfileData($user_slug))->getProfile();

    //$profileData = UserProfileData::getInstance()->getProfile();
    // echo '<pre>';
    // var_dump($profile);    
    // echo '</pre>';

    $social_links = $profile['social_links'] ?? [];

    if ($user) :
    ?>
        <div class="banner-section">
            <div class="banner-background">
                <div class="z-0 position-relative banner-container">
                    <img class="banner-img" src="<?php echo esc_url(get_user_meta($user->ID, 'profile_cover_photo', true) ?: get_template_directory_uri() . '/assets/img/loggedin_images/banner.jpg'); ?>" alt="Cover Photo" />

                    <?php if (is_user_logged_in()) : ?>
                        <div class="position-absolute bg-white px-3 py-2 edit-cover" style="cursor:pointer;">
                            Edit Cover Photo
                            <form id="cover-upload-form" enctype="multipart/form-data" style="display: none;">
                                <input type="file" name="cover_photo" id="cover-photo-input" accept="image/*" />
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <style>
                .nav-item-active {
                    border-bottom: none;
                }

                .hover-primary:hover {
                    color: #2D6AC4 !important;
                    /* Bootstrap primary color */
                    transition: color 0.3s ease;
                }

                @media (min-width: 992px) {
                    .w-lg-40 {
                        width: 40% !important;
                    }

                    .w-lg-60 {
                        width: 60% !important;
                    }
                }
            </style>

            <div class="profile-section">
                <div class="container-main">
                    <div class="d-flex flex-column flex-lg-row align-items-start justify-content-between pb-4">
                        <!-- Profile Section (40%) -->
                        <div class="mb-3 mb-lg-0 pe-lg-3 w-100 w-lg-40 xprofile-row">
                            <!-- Profile Info Container -->
                            <div class="d-flex flex-column flex-lg-row align-items-center">
                                <div>
                                    <div class="profile-col">
                                        <img
                                            src="<?php echo esc_url(get_user_meta($user->ID, 'profile_photo', true) ?: get_template_directory_uri() . '/assets/img/loggedin_images/profile_default.png'); ?>"
                                            class="rounded-circle w-100 h-100"
                                            id="profile-photo-preview"
                                            alt="Profile Photo" />
                                        <label class="upload-icon" title="Change Profile Photo">
                                            <i class="dashicons dashicons-camera"></i>
                                            <input type="file" id="profile-photo-upload" accept="image/*">
                                        </label>
                                    </div>
                                </div>

                                <div class="d-flex md:flex-row flex-column gap-3 ms-4 mt-2 mt-lg-3 text-lg-start text-center">
                                    <h2 class="profile-name">
                                        <?php echo esc_html($profile['first_name'] . ' ' . $profile['last_name']); ?>
                                    </h2>
                                    <?php
                                    $referredUsers = $profile['referred_users'];
                                    $referralCount = count($referredUsers);
                                    ?>
                                    <span class="profile-stats">
                                        <?php echo esc_html($referralCount); ?> referral partner<?php echo $referralCount === 1 ? '' : 's'; ?>
                                    </span>
                                    <?php if ($referralCount > 0) : ?>
                                        <div class="referred-users">
                                            <?php echo $profile['referred_users_html']; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Welcome Section (60%) -->
                        <div class="bg-light mt-0 mt-lg-5 mt-md-5 px-3 py-2 border rounded w-100 w-lg-60">
                            <div class="mb-2 text-danger fw-semibold">
                                Welcome to our "Soft Launch", Share thoughts & bugs ahead of millions of others. Complete your profile,
                                <a href="https://marketing-communications.personalempowermentteams.me" target="_blank">Click here</a>
                                to Book a Q&A Tools & Income Zoom Intro.
                            </div>
                            <?php if (is_user_logged_in()) : ?>
                                <ul class="d-flex flex-wrap gap-2 mb-0 list-unstyled">
                                    <li>
                                        <a class="btn-outline-danger btn btn-sm" href="<?= esc_url(home_url('/report')); ?>">
                                            <i class="me-1 bi bi-flag"></i> Report an issue
                                        </a>
                                    </li>
                                    <li>
                                        <a class="btn-outline-primary btn btn-sm" href="<?= esc_url(home_url('/suggestion')); ?>">
                                            <i class="me-1 bi bi-lightbulb"></i> Make a suggestion
                                        </a>
                                    </li>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>

            </div>
            <div class="router-btn container-main">
                <div class="d-flex align-items-center justify-content-center pb-2">
                    <div class="w-100">
                        <ul class="d-flex flex-column flex-md-row justify-content-md-end align-items-center justify-content-center gap-0 gap-md-4 mb-0 p-0 px-3 text-center nav-list fs-6 fs-md-5">
                            <li class="py-1 py-md-3 w-100 w-md-auto nav-item">
                                <a class="text-primary nav-link fw-semibold" href="/support-teams">
                                    <i class="me-1 bi bi-people fs-5"></i> Support Teams
                                </a>
                            </li>
                            <li class="py-1 py-md-3 w-100 w-md-auto nav-item">
                                <a class="text-primary nav-link fw-semibold" href="/marketing">
                                    <i class="me-1 bi bi-bullseye fs-5"></i> Marketing
                                </a>
                            </li>
                            <li class="py-1 py-md-3 w-100 w-md-auto nav-item">
                                <a class="text-primary nav-link fw-semibold" href="/artificial-intelligence">
                                    <i class="me-1 bi bi-cpu fs-5"></i> AI
                                </a>
                            </li>
                            <li class="py-1 py-md-3 w-100 w-md-auto nav-item">
                                <a class="text-primary nav-link fw-semibold" href="/collaboration">
                                    <i class="me-1 bi bi-people fs-5"></i> Collaboration
                                </a>
                            </li>
                            <li class="py-1 py-md-3 w-100 w-md-auto nav-item">
                                <a class="text-primary nav-link fw-semibold" href="/reputation">
                                    <i class="me-1 bi bi-award fs-5"></i> Reputation
                                </a>
                            </li>
                            <li class="py-1 py-md-3 w-100 w-md-auto nav-item">
                                <a class="text-primary nav-link fw-semibold" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#createPostModal">
                                    <i class="me-1 bi bi-megaphone fs-5"></i> Concurrent Post
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>

        </div>
        <main>
            <div class="container-main">
                <div class="row gx-3">
                    <div class="mt-3 col-lg-5">
                        <div class="top-0 z-0 position-sticky">
                            <?php
                            if (!$profile) {
                                echo '<p>User profile not found.</p>';
                                return;
                            }

                            // Helper function for safe image URL (example)
                            function get_image_url($meta_value)
                            {
                                // If stored as attachment ID or URL, convert accordingly
                                // For now assume URL or empty fallback
                                return !empty($meta_value) ? esc_url($meta_value) : get_template_directory_uri() . '/assets/img/default-profile.png';
                            }

                            // Use these variables for clarity
                            $profilePhoto = get_image_url($profile['profile_photo']);
                            $coverPhoto = get_image_url($profile['cover_photo']);
                            $profileUrl = esc_url($profile['profile_url']);
                            ?>

                            <?php include 'profile-parts/profile-items.php'; ?>
                            <?php //include 'profile-parts/profile-photos-videos.php'; 
                            ?>
                        </div>
                    </div>

                    <div class="mt-3 col-lg-7">
                        <div class="mb-3">
                            <?php require_once 'profile-parts/create-post.php'; ?>
                        </div>
                        <div>
                            <?php require_once 'profile-parts/posts.php'; ?>
                        </div>
                    </div>
                </div>
        </main>
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
    <?php get_footer(); ?>
<?php
}
