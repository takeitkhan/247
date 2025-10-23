<?php

/**
 * Template Name: Signin Page
 * Template Post Type: page
 */

defined('ABSPATH') || exit;

if (is_user_logged_in()) {
    get_header('portal');
} else {
    get_header('main');
}
$message = get_transient('custom_user_message');
$old_input = $message['old_input'] ?? [];
?>

<div class="container">
    <div class="justify-content-center row">
        <div class="col-12">
            <div class="row g-md-4">
                <!-- Login Section -->
                <div class="py-5 col-lg-6">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="d-flex align-items-center gap-2">
                            <img class="logo" src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/logo.png" alt="<?php bloginfo('name'); ?>">
                            <span class="gradient-text"><?php echo get_bloginfo('name'); ?></span>
                        </div>

                        <button class="d-flex align-items-center gap-2 go-back" onclick="window.history.back();">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/back.png" alt="Back">
                            <span>Go back</span>
                        </button>
                    </div>

                    <h2 class="mb-3 title">Welcome Back</h2>
                    <p class="mb-4">Log in to continue your empowerment journey.</p>

                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo esc_attr($message['type']); ?> mt-3" role="alert">
                            <?php echo wp_kses_post($message['text']); ?>
                        </div>
                        <?php delete_transient('custom_user_message'); ?>
                    <?php endif; ?>

                    <form method="post">
                        <?php wp_nonce_field('custom_user_login', 'custom_user_login_nonce'); ?>

                        <div class="mb-3">
                            <label for="username" class="form-label fw-normal">Username <span>*</span></label>
                            <input type="text" class="input" id="username" name="username" placeholder="Enter your username" value="<?= esc_attr($old_input['username'] ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-normal">Password <span>*</span></label>
                            <div class="position-relative">
                                <input type="password" class="input" id="password" name="password" placeholder="Enter your password" required>
                                <img class="pass-show" src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/pass-show.png" alt="Show Password" onclick="togglePassword()">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mb-3">
                            <a href="<?php echo wp_lostpassword_url(); ?>" class="text-decoration-none forgot-text">Forgot your password?</a>
                        </div>

                        <button type="submit" name="user_login" class="mb-3 custom-btn">Sign In</button>

                        <div class="mb-3 text-start">
                            <p class="mb-0">Don't have an account?</p>
                        </div>
                        <div>
                            <a href="<?php echo site_url('/signup'); ?>" class="mb-3 custom-btn-outline-none">Sign Up</a>
                        </div>
                    </form>
                </div>

                <!-- Right Info Section -->
                <div class="col-lg-6">
                    <div class="position-relative d-flex flex-column align-items-center justify-content-start px-5 px-md-5 border-singup overflow-hidden text-center">
                        <img class="ellipse-size" src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/Ellipse.png" alt="">
                        <div class="z-1 position-absolute">
                            <p class="mb-3 pt-4 title">Get Started in 4 Easy Steps</p>
                            <p class="mb-5 text-center">Sign in, personalize, explore — and you're in.</p>
                        </div>
                    </div>

                    <div class="position-relative d-flex flex-column align-items-center gap-4 px-4 px-md-5 border-singup overflow-hidden">
                        <img class="d-md-block bottom-0 z-0 position-absolute d-none img-position" src="<?php echo get_template_directory_uri(); ?>/assets/img/Vector.png" alt="">

                        <?php
                        $steps = [
                            ['title' => 'Sign In', 'desc' => 'You already have an account'],
                            ['title' => 'Invite & Share', 'desc' => 'Send your referral link to friends and contacts.'],
                            ['title' => 'Track Progress', 'desc' => 'See updates from your referrals in real time.'],
                            ['title' => 'Empower Yourself', 'desc' => 'Join coaching programs and boost your growth.'],
                        ];
                        foreach ($steps as $index => $step):
                            $cardClass = 's-card' . ($index > 0 ? ($index + 1) : '');
                        ?>
                            <div class="<?= esc_attr($cardClass); ?>">
                                <div class="d-flex align-items-center gap-4">
                                    <span class="number-sign"><?= $index + 1; ?></span>
                                    <div>
                                        <p class="c-title"><?= esc_html($step['title']); ?></p>
                                        <p class="c-des"><?= esc_html($step['desc']); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById("password");
        passwordInput.type = passwordInput.type === "password" ? "text" : "password";
    }
</script>

<?php
if (is_user_logged_in()) {
    get_footer('portal');
} else {
    get_footer('main');
}
?>