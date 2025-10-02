<?php
/**
 * Template Name: Custom Lost Password
 *
 * Handles lost password form and submission.
 */

if (is_user_logged_in()) {
    wp_redirect(home_url());
    exit;
}

$error = '';
$success = '';

// Handle form submission
if (isset($_POST['custom_lost_password']) && check_admin_referer('custom_lost_password_action', 'custom_lost_password_nonce')) {

    $user_login = sanitize_text_field($_POST['user_login']);

    if (empty($user_login)) {
        $error = 'Please enter your username or email.';
    } else {
        // Try to send password reset email
        $reset = retrieve_password($user_login);
        if (is_wp_error($reset)) {
            $error = $reset->get_error_message();
        } else {
            $success = 'Check your email for the password reset link.';
        }
    }
}

get_header();
?>

<div class="mt-5 container" style="max-width: 480px;">
    <h2>Lost your password?</h2>
    <p>Enter your username or email address to reset your password.</p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= esc_html($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= esc_html($success) ?></div>
    <?php endif; ?>

    <?php if (empty($success)): ?>
        <form method="post" novalidate>
            <?php wp_nonce_field('custom_lost_password_action', 'custom_lost_password_nonce'); ?>

            <div class="mb-3">
                <label for="user_login" class="form-label">Username or Email</label>
                <input type="text" name="user_login" class="form-control" id="user_login" required>
            </div>

            <button type="submit" name="custom_lost_password" class="btn btn-primary">Reset Password</button>
        </form>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
