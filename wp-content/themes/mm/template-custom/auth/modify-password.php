<?php
/* Template Name: Change Password */
ob_start(); // Start output buffering
get_header();


if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

$current_user = wp_get_current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!isset($_POST['change_password_nonce']) || !wp_verify_nonce($_POST['change_password_nonce'], 'change_password_action')) {
        echo '<div class="alert alert-danger">Security check failed.</div>';
    } else {
        $new_password = sanitize_text_field($_POST['new_password']);
        $confirm_password = sanitize_text_field($_POST['confirm_password']);

        if (empty($new_password) || empty($confirm_password)) {
            echo '<div class="alert alert-danger">All fields are required.</div>';
        } elseif ($new_password !== $confirm_password) {
            echo '<div class="alert alert-danger">Passwords do not match.</div>';
        } else {
            wp_set_password($new_password, $current_user->ID);
            wp_logout();
            wp_redirect(wp_login_url());
            exit;
        }
    }
}
?>
<main>
    <div class="main-container" style="padding-top: 80px">
        <div class="row g-3">
            <?php include get_template_directory() . '/template-custom/auth/profile-parts/edit-profile-left-sidebar.php'; ?>

            <div class="ms-md-auto col-12 col-md-8 col-lg-9 col-xl-9">
                <div class="bg-white custom-box-shadow mb-3 p-3 custom-border-radius">                    
                    <h5 class="mb-5">🔒 Change Password</h5>
                    <form method="post">
                        <?php wp_nonce_field('change_password_action', 'change_password_nonce'); ?>

                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>

                        <button type="submit" name="change_password" class="btn btn-primary">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<?php ob_end_flush(); ?>
<?php get_footer(); ?>