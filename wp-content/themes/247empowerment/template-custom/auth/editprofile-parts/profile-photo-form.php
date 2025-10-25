<?php
$profile = isset($args['profile']) ? $args['profile'] : [];
$user = isset($args['user']) ? $args['user'] : null;

// Fetch profile photo (or default)
$profile_photo = get_user_meta($user->ID, 'profile_photo', true) ?: get_template_directory_uri() . '/assets/img/loggedin_images/profile_default.png';

// Fetch display data
$full_name = trim(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? '')) ?: $user->display_name;
$location = get_user_meta($user->ID, 'place_display_name', true);
$profession = get_user_meta($user->ID, 'about_me_short', true);
?>
<div class="bg-white upcoming-events custom-card">
    <div class="d-flex flex-column align-items-center">
        <div class="pb-4">
            <div class="img100">
                <img class="rounded-circle w-100 h-100 object-fit-cover"
                    src="<?php echo esc_url($profile_photo); ?>"
                    alt="<?php echo esc_attr($full_name); ?>">
            </div>
        </div>

        <?php if (is_user_logged_in()) : ?>
            <!-- Upload photo -->
            <div>
                <form method="post" enctype="multipart/form-data" id="profile-photo-form">
                    <input type="file" id="uploadPhoto" name="profile_photo" class="d-none" accept="image/*">
                    <label for="uploadPhoto" class="d-inline-flex align-items-center justify-content-center m-lg-0 w-100 w-auto custom-btn">                        
                        Change photo
                    </label>
                    <input type="hidden" name="action" value="update_profile_photo">
                    <?php wp_nonce_field('update_profile_photo_nonce', 'profile_photo_nonce'); ?>
                </form>

            </div>

            <!-- Delete photo -->
            <div class="d-flex align-items-center mt-2 pb-3">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/delete.png" alt="" class="img-fluid img20">
                <button type="button" id="delete-photo-btn"
                    class="bg-transparent m-0 border-0 w-auto text-blue-color custom-btn-size">
                    Delete photo
                </button>
            </div>
        <?php endif; ?>

        <!-- Profile Info -->
        <div>
            <div class="d-flex flex-column align-items-center gap12">
                <span class="fs20"><?php echo esc_html($full_name); ?></span>

                <?php if ($location): ?>
                    <p class="text-color-neutral fs14"><?php echo esc_html($location); ?></p>
                <?php endif; ?>

                <?php if ($profession): ?>
                    <p class="text-color-neutral fs14"><?php echo esc_html($profession); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('edit-profile-photo-btn')?.addEventListener('click', () => {
        document.getElementById('profile-photo-input').click();
    });

    document.getElementById('profile-photo-input')?.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('action', 'upload_profile_photo');
        formData.append('profile_photo', file);

        // Optional: show temporary preview
        const preview = document.getElementById('profile-photo-preview');
        preview.src = URL.createObjectURL(file);

        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    preview.src = data.data.url; // Update image with uploaded URL
                } else {
                    alert(data.data?.message || 'Upload failed');
                }
            })
            .catch(() => alert('Something went wrong. Please try again.'));
    });

    document.getElementById('delete-photo-btn')?.addEventListener('click', async () => {
        if (!confirm('Are you sure you want to delete your profile photo?')) return;

        const response = await fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                action: 'delete_profile_photo'
            })
        });

        const result = await response.json();
        if (result.success) location.reload();
    });
</script>