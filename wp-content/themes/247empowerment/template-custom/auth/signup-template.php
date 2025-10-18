<?php

/**
 * Template Name: Signup Page
 * Template Post Type: page
 */

defined('ABSPATH') || exit;
if (is_user_logged_in()) {
    get_header('portal');
} else {
    get_header('main');
}
?>
<?php
$message = get_transient('custom_user_message');
$old_input = $message['old_input'] ?? [];
?>

<div class="container">
    <div class="justify-content-center row">
        <div class="col-12">
            <div class="">
                <div class="text-white text-center empowerment-header"></div>

                <div class="row g-md-4">
                    <!-- Login Section -->
                    <div class="py-5 col-lg-6">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="d-flex align-items-center gap-2">
                                <img class="logo" src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/logo.png" alt="Logo">
                                <span class="gradient-text">24/7 Empowerment</span>
                            </div>

                            <button class="d-flex align-items-center gap-2 go-back" onclick="window.history.back();">
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/back.png" alt="Back">
                                <span>Go back</span>
                            </button>
                        </div>

                        <h2 class="mb-3 title">Join the Empowerment Movement</h2>
                        <p class="mb-4">Create your free account and access support teams, tools, and growth opportunities.</p>

                        <?php if ($message = get_transient('custom_user_message')): ?>
                            <div class="alert alert-<?php echo esc_attr($message['type']); ?> mt-3" role="alert">
                                <?php echo wp_kses_post($message['text']); ?>
                            </div>
                            <?php delete_transient('custom_user_message'); ?>
                        <?php endif; ?>

                        <form method="post">
                            <?php wp_nonce_field('custom_user_registration', 'custom_user_registration_nonce'); ?>

                            <?php if (isset($_GET['ref'])): ?>
                                <input type="hidden" name="referrer" id="referrer" value="<?php echo esc_attr($_GET['ref']); ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label for="firstName" class="form-label fw-normal">First Name <span>*</span></label>
                                <input type="text" class="input" id="firstName" name="first_name"
                                    placeholder="Enter your first name"
                                    value="<?php echo esc_attr($old_input['first_name'] ?? ''); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="lastName" class="form-label fw-normal">Last Name <span>*</span></label>
                                <input type="text" class="input" id="lastName" name="last_name"
                                    placeholder="Enter your last name"
                                    value="<?php echo esc_attr($old_input['last_name'] ?? ''); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="username" class="form-label fw-normal">Username <span>*</span></label>
                                <input type="text" class="input" id="username" name="username"
                                    placeholder="Enter your username"
                                    value="<?php echo esc_attr($old_input['username'] ?? ''); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="dob" class="form-label fw-normal">Date of Birth <span>*</span></label>
                                <input type="date" class="input" id="dob" name="dob"
                                    value="<?php echo esc_attr($old_input['dob'] ?? ''); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-normal">Email <span>*</span></label>
                                <input type="email" class="input" id="email" name="email"
                                    placeholder="Enter your email"
                                    value="<?php echo esc_attr($old_input['email'] ?? ''); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-normal">Password <span>*</span></label>
                                <div class="position-relative">
                                    <input type="password" class="input" id="password" name="password"
                                        placeholder="Enter your password" required>
                                    <img class='pass-show' src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/pass-show.png" alt="">
                                </div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" id="consent" name="consent" class="form-check-input" value="yes" <?php checked(!empty($old_input['consent_transactional'])); ?> required>                           
                                <label class="form-check-label" for="consent">
                                    I agree to receive account-related updates (e.g. reminders, confirmations)
                                    and occasional promotional messages (e.g. offers, discounts, updates).
                                    Msg & Data rates may apply. Reply STOP to opt-out.
                                </label>
                            </div>
                            <button type="submit" name="user_signup" class="mb-3 custom-btn">Sign up</button>

                            <div class="mb-3 text-start">
                                <p class="mb-0">Already have an account?</p>
                            </div>
                            <div>
                                <a href="<?php echo wp_login_url(); ?>" class="mb-3 custom-btn-outline-none">Sign In</a>
                            </div>
                        </form>
                    </div>

                    <!-- Right Info Section -->
                    <div class="col-lg-6">
                        <div class="position-relative d-flex flex-column align-items-center justify-content-start px-5 px-md-5 border-singup overflow-hidden text-center">
                            <img class="ellipse-size" src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/Ellipse.png" alt="">
                            <div class="z-1 position-absolute">
                                <p class="mb-3 pt-4 title">Get Started in 4 Easy Steps</p>
                                <p class="mb-5 text-center">Sign up, personalize, explore — and you're in.</p>
                            </div>
                        </div>

                        <div class="position-relative d-flex flex-column align-items-center gap-4 px-4 px-md-5 border-singup overflow-hidden">
                            <img class="d-md-block bottom-0 z-0 position-absolute d-none img-position"
                                src="<?php echo get_template_directory_uri(); ?>/assets/img/Vector.png" alt="">

                            <div class="s-card">
                                <div class="d-flex align-items-center gap-4">
                                    <span class="number-sign">1</span>
                                    <div>
                                        <p class="c-title">Sign Up</p>
                                        <p class="c-des">Create your account in just a few clicks.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="s-card2">
                                <div class="d-flex align-items-center gap-4">
                                    <span class="number-sign">2</span>
                                    <div>
                                        <p class="c-title">Setup Profile</p>
                                        <p class="c-des">Set up your profile and preferences easily.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="s-card3">
                                <div class="d-flex align-items-center gap-4">
                                    <span class="number-sign">3</span>
                                    <div>
                                        <p class="c-title">Explore</p>
                                        <p class="c-des">Browse features and tools tailored to you.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="s-card4">
                                <div class="d-flex align-items-center gap-4">
                                    <span class="number-sign">4</span>
                                    <div>
                                        <p class="c-title">Start Using</p>
                                        <p class="c-des">Enjoy the full experience right away.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
if (is_user_logged_in()) {
    get_footer('portal');
} else {
    get_footer('main');
}
?>