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
            <?php get_template_part('template-custom/auth/profile-parts/cover-photo-section', null, ['profile' => $profile, 'user' => $user]);  ?>
            <div class="row">
                <div class="col-lg-3">
                    <?php get_template_part('template-custom/auth/profile-parts/home-left-sidebar', null, ['profile' => $profile]); ?>

                </div>
                <div class="mb-4 col-lg-6">
                    <?php get_template_part('template-custom/auth/profile-parts/create-post', null, ['profile' => $profile]); ?>

                    <?php get_template_part('template-custom/auth/profile-parts/posts', null, ['profile' => $profile]); ?>
                </div>
                <div class="col-lg-3">
                    <?php get_template_part('template-custom/auth/profile-parts/referral-partners', null, ['profile' => $profile]); ?>
                </div>
            </div>
        </div>
    <?php
    else :
        echo '<p>User not found.</p>';
    endif;
    ?>

    <script>
        var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";


        document.addEventListener('DOMContentLoaded', function() {
            const editBtn = document.getElementById('edit-profile-photo-btn');
            const fileInput = document.getElementById('profile-photo-input');

            if (!editBtn || !fileInput) {
                console.error('Profile photo input or button not found');
                return;
            }

            editBtn.addEventListener('click', function() {
                fileInput.click();
            });

            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;

                const formData = new FormData();
                formData.append('action', 'upload_profile_photo');
                formData.append('profile_photo', file);

                fetch(ajaxurl, {
                        method: 'POST',
                        body: formData,
                    })
                    .then((res) => res.json())
                    .then((data) => {
                        if (data.success) {
                            document.querySelector('.img-p').src = data.data.url; // update photo
                            console.log('Uploaded:', data.data.url);
                        } else {
                            console.error(data.data.message || 'Upload failed');
                        }
                    })
                    .catch((err) => console.error('Error:', err));
            });


            /* -------------------------
               PROFILE PHOTO UPLOAD
            ------------------------- */
            const uploadInput = document.getElementById('profile-photo-upload');
            const previewImg = document.getElementById('profile-photo-preview');

            if (uploadInput && previewImg) {
                uploadInput.addEventListener('change', function() {
                    const file = this.files[0];
                    if (!file) return;

                    const formData = new FormData();
                    formData.append('action', 'upload_profile_photo');
                    formData.append('profile_photo', file);

                    console.log('🚀 Uploading profile photo...');
                    fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                            method: 'POST',
                            credentials: 'same-origin',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(response => {
                            console.log('Profile response:', response);
                            if (response.success && response.data?.url) {
                                previewImg.src = response.data.url;
                            } else {
                                alert('Profile upload failed: ' + (response.data?.message || 'Unknown error'));
                            }
                        })
                        .catch(err => console.error('Fetch error:', err));
                });
            } else {
                console.log('❌ Profile upload elements not found');
            }


            // -------------------------
            // COVER PHOTO UPLOAD
            // -------------------------
            const coverEditBtn = document.getElementById('edit-cover-photo-btn');
            const coverInput = document.getElementById('cover-photo-input');

            if (coverEditBtn && coverInput) {
                coverEditBtn.addEventListener('click', function() {
                    coverInput.click();
                });

                coverInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    const formData = new FormData();
                    formData.append('action', 'upload_cover_photo');
                    formData.append('cover_photo', file);

                    fetch(ajaxurl, {
                            method: 'POST',
                            body: formData,
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                document.querySelector('.profile_img img').src = data.data.url;
                                console.log('✅ Cover photo updated:', data.data.url);
                            } else {
                                console.error('❌ Cover photo upload failed:', data.data.message);
                            }
                        })
                        .catch(err => console.error('Error:', err));
                });
            } else {
                console.log('❌ Cover photo elements not found');
            }


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
