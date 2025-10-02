<?php

/**
 * Template Name: Signup Page
 * Template Post Type: page
 */

defined('ABSPATH') || exit;
get_header();
?>
<?php
$message = get_transient('custom_user_message');
$old_input = $message['old_input'] ?? [];
?>


<div class="container-fluid">
    <div class="px-4 py-4 overflow-hidden row">
        <div class="bg-white px-4 py-3 col-md-5">
            <h2 class="">Sign Up Your Account</h2>
            <h6 class="mb-5 text-muted">Getting started is simple — just fill out a few details below.</h6>

            <?php if ($message = get_transient('custom_user_message')): ?>
                <div class="alert alert-<?= esc_attr($message['type']) ?> mt-3" role="alert">
                    <?= wp_kses_post($message['text']) ?>
                </div>
                <?php delete_transient('custom_user_message'); ?>
            <?php endif; ?>

            <form method="post">
                <?php wp_nonce_field('custom_user_registration', 'custom_user_registration_nonce'); ?>
                <?php if (isset($_GET['ref'])): ?>
                    <input type="hidden" name="referrer" id="referrer" value="<?= esc_attr($_GET['ref']); ?>">
                <?php endif; ?>


                <div class="mb-3">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" name="first_name" id="first_name"
                        class="form-control" required
                        value="<?php echo esc_attr($old_input['first_name'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" name="last_name" id="last_name"
                        class="form-control" required
                        value="<?php echo esc_attr($old_input['last_name'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label for="dob" class="form-label">Date of Birth</label>
                    <input type="date" name="dob" id="dob"
                        class="form-control" required
                        value="<?php echo esc_attr($old_input['dob'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" id="username"
                        class="form-control" required
                        value="<?php echo esc_attr($old_input['username'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" name="email" id="email"
                        class="form-control" required
                        value="<?php echo esc_attr($old_input['email'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password"
                        class="form-control" required>
                </div>
                <!-- Consent Checkboxes -->
                <div class="mb-2 form-check">
                    <input class="form-check-input" type="checkbox" name="consent_transactional" id="consent_transactional" value="1" <?php checked(!empty($old_input['consent_transactional'])); ?> required>
                    <label class="form-check-label small" for="consent_transactional">
                        By checking this box, I consent to receive transactional messages related to my account, orders, or services I have requested.
                        These messages may include appointment reminders, order confirmations, and account notifications, among others.
                        Message frequency may vary. Message & Data rates may apply. Reply HELP for help or STOP to opt-out.
                    </label>
                </div>

                <div class="mb-4 form-check">
                    <input class="form-check-input" type="checkbox" name="consent_marketing" id="consent_marketing" value="1" <?php checked(!empty($old_input['consent_marketing'])); ?> required>
                    <label class="form-check-label small" for="consent_marketing">
                        By checking this box, I consent to receive marketing and promotional messages, including special offers, discounts,
                        and new product updates, among others.
                        Message frequency may vary. Message & Data rates may apply. Reply HELP for help or STOP to opt-out.
                    </label>
                </div>

                <button type="submit" name="user_signup" class="w-25 btn btn-primary">Sign Up</button>
            </form>

            <div class="mt-3 small footlinks" style="letter-spacing: 1px;">
                <?php
                wp_nav_menu([
                    'theme_location' => 'secondary',
                    'container' => false,
                    'menu_class' => 'me-3  text-decoration-none',
                    'items_wrap' => '%3$s',
                    'walker' => new MM_Walker_Nav_Menu(),
                    'fallback_cb' => false,
                ]);
                ?>
            </div>
        </div>
        <div class="d-md-block bg-teal-100 p-3 pt-5 text-center col-md-7 d-none">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/logo_pet.png"
                alt="Signup" class="w-25 img-fluid">
            <h2 class="mb-4">Browse Exclusive Tailored Features</h2>
            <h6 class="text-muted">Join us today — it only takes a few moments to create your account.</h6>
            <section class="py-5">
                <div class="text-center container">
                    <h2 class="mb-4">How It Works</h2>
                    <div class="row g-4">
                        <div class="col-md-3">
                            <div class="bg-white shadow-sm p-4 border rounded-3 h-100">
                                <div class="mb-3 text-primary fs-1">1</div>
                                <h5 class="mb-2">Sign Up</h5>
                                <p class="text-muted">Create your account in just a few clicks.</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-white shadow-sm p-4 border rounded-3 h-100">
                                <div class="mb-3 text-primary fs-1">2</div>
                                <h5 class="mb-2">Setup Profile</h5>
                                <p class="text-muted">Set up your profile and preferences easily.</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-white shadow-sm p-4 border rounded-3 h-100">
                                <div class="mb-3 text-primary fs-1">3</div>
                                <h5 class="mb-2">Explore</h5>
                                <p class="text-muted">Browse features and tools tailored to you.</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-white shadow-sm p-4 border rounded-3 h-100">
                                <div class="mb-3 text-primary fs-1">4</div>
                                <h5 class="mb-2">Start Using</h5>
                                <p class="text-muted">Enjoy the full experience right away.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
<style>
    .small,
    small {
        font-size: .675em !important;
    }
</style>
<?php get_footer(); ?>