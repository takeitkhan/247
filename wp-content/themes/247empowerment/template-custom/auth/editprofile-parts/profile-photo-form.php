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
            <!-- Upload photo -->
            <div>
                <form method="post" enctype="multipart/form-data" id="profile-photo-form">
                    <input type="file" id="profile-photo-input" name="profile_photo" class="d-none" accept="image/*">
                    <label for="profile-photo-input">Change photo</label>
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
        const audio = new Audio("<?= get_template_directory_uri(); ?>/sounds/coin.mp3");
        audio.play().catch(e => console.error("Audio play failed:", e));
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
                    const previewElement = document.querySelector(previewSelector);
                    if (previewElement) previewElement.src = response.data.url;
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
</script>