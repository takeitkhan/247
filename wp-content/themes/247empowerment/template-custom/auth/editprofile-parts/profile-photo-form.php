<?php
$profile = isset($args['profile']) ? $args['profile'] : [];
$user = isset($args['user']) ? $args['user'] : null;

// Fetch profile photo (or default)
$profile_photo = get_user_meta($user->ID, 'profile_photo', true) ?: get_template_directory_uri() . '/assets/img/loggedin_images/banner.jpg';

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
            <!-- Upload Photo -->
            <form method="post" enctype="multipart/form-data" id="profile-photo-form">
                <input type="file" id="profile-photo-input" name="profile_photo" class="d-none" accept="image/*">
                <button type="button" id="trigger-upload-btn" class="d-inline-flex align-items-center gap-2 btn custom-btn">
                    <i class="fa fa-camera"></i>
                    <span>Change Photo</span>
                </button>


                <input type="hidden" name="action" value="update_profile_photo">
                <?php wp_nonce_field('update_profile_photo_nonce', 'profile_photo_nonce'); ?>
            </form>

            <!-- Delete Photo -->
            <div class="d-flex align-items-center mt-2">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/delete.png"
                    alt="" class="me-1 img-fluid img20">
                <button type="button" id="delete-photo-btn"
                    class="bg-transparent border-0 text-blue-color custom-btn-size">
                    Delete photo
                </button>
            </div>
        <?php endif; ?>


        <!-- Profile Info -->
        <div>
            <div class="d-flex flex-column align-items-center gap6">
                <span class="fs20"><?php echo esc_html($full_name); ?></span>

                <?php if ($location): ?>
                    <span class="text-color-neutral fs14"><?php echo esc_html($location); ?></span>
                <?php endif; ?>
                <?php if ($profile['is_sales_person'] == 1): ?>
                    <span class="border rounded-pill bg-outline-primary badge"
                        style="border-color: #05489C !important; color: #05489C !important;">
                        Sales Person
                    </span>
                <?php endif; ?>

                <?php if ($profession): ?>
                    <span class="text-color-neutral fs14"><?php echo esc_html($profession); ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";

    /**
     * Shows the gamification modal and header notification.
     * @param {object} notification - The notification data from the server.
     */
    function showGamificationNotification(notification) {
        if (!notification) return;

        // --- 1. Show Header Notification ---
        // This uses the Toastify.js library already included in your theme.
        Toastify({
            text: notification.message,
            duration: 5000,
            gravity: "top",
            position: "right",
            backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)",
            stopOnFocus: true,
        }).showToast();

        // --- 2. Show Modal Popup ---
        const modal = document.getElementById('gamificationPointsModal');
        if (modal) {
            const modalTitle = document.getElementById('gamification-modal-title');
            const modalMessage = document.getElementById('gamification-modal-message');

            if (modalTitle) modalTitle.innerText = notification.title;
            if (modalMessage) modalMessage.innerHTML = notification.message;

            var gamificationModal = new bootstrap.Modal(modal);
            gamificationModal.show();
        }

        // --- 3. Play Sound ---
        if (typeof playNotificationSound === 'function') {
            console.log('🎵 Playing photo upload notification sound via theme audio system');
            playNotificationSound();
        } else {
            // Fallback: Play sound directly
            console.log('📻 Playing photo upload notification sound directly');
            const audio = new Audio("<?= get_template_directory_uri(); ?>/sounds/coin.mp3");
            audio.volume = 0.5;
            audio.play().catch(e => console.error("Audio play failed:", e));
        }
    }

    /**
     * Handles the file upload via AJAX and processes the response.
     * @param {File} file - The file to upload.
     * @param {string} action - The AJAX action name ('upload_profile_photo' or 'upload_cover_photo').
     * @param {string} fieldName - The name of the file field ('profile_photo' or 'cover_photo').
     * @param {string} previewSelector - The CSS selector for the image preview element.
     */
    function handlePhotoUpload(file, action, fieldName, previewSelector) {
        const formData = new FormData();
        formData.append('action', action); // use the dynamic action passed in
        formData.append(fieldName, file); // use the dynamic field name
        formData.append('nonce', '<?php echo wp_create_nonce("update_profile_photo_nonce"); ?>');

        fetch(ajaxurl, {
                method: 'POST',
                body: formData,
            })
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    location.reload();
                    return;
                } else {
                    alert('Upload failed: ' + (response.data.message || 'Unknown error'));
                }
            })
            .catch(err => console.error('Error:', err));
    }


    document.addEventListener('DOMContentLoaded', function() {
        const profileEditBtn = document.getElementById('profile-photo-input-label');
        const profileInput = document.getElementById('profile-photo-input');

        if (profileInput) {
            profileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;

                handlePhotoUpload(file, 'upload_profile_photo', 'profile_photo', '.img-p');
            });
        }
    });

    document.getElementById('trigger-upload-btn').addEventListener('click', function() {
        document.getElementById('profile-photo-input').click();
    });

    // Auto-submit the form when a file is selected
    document.getElementById('profile-photo-input').addEventListener('change', function() {
        document.getElementById('profile-photo-form').submit();

        // Reload after short delay
        setTimeout(() => {
            location.reload();
        }, 800);
    });
</script>