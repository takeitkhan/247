<?php

/**
 * Template Name: Signin Page
 * Template Post Type: page
 */

defined('ABSPATH') || exit;
get_header();
?>
<div class="container-fluid">
    <div class="px-4 py-4 overflow-hidden row">
        <div class="bg-white px-4 py-5 col-md-6">
            <h2 class="">Welcome Back</h2>
            <h6 class="mb-5 text-muted">We're glad to see you again! Log in to continue where you left off.</h6>

            <?php if ($message = get_transient('custom_user_message')): ?>
                <div class="alert alert-<?= esc_attr($message['type'] === 'success' ? 'success' : 'danger') ?> mt-3" role="alert">
                    <?= $message['text'] /* safe HTML allowed and printed raw */ ?>
                </div>
                <?php delete_transient('custom_user_message'); ?>
            <?php endif; ?>


            <form method="post">
                <?php wp_nonce_field('custom_user_login', 'custom_user_login_nonce'); ?>

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="position-relative mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="pe-5 form-control" required>
                    <span class="top-55 position-absolute me-3 custom-mt-20 translate-middle-y end-0" style="cursor: pointer;" onclick="togglePassword()">
                        <i class="fa-solid fa-eye" id="toggleIcon"></i>
                    </span>
                </div>
                <button type="submit" name="user_login" class="w-25 btn btn-primary">Sign In</button>
            </form>

        </div>
        <div class="d-md-block bg-teal-100 p-3 pt-5 text-center col-md-6 d-none">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/logo_pet.png"
                alt="Signup" class="w-25 img-fluid">
            <h2 class="mb-4">Don’t Have an Account?</h2>
            <h6 class="text-muted">
                <a href="/signup">Sign Up now</a> — it’s fast, free, and opens up a world of possibilities.
            </h6>
            <section class="py-5">
                <div class="text-center container">
                    <h2 class="mb-4">How It Works</h2>
                    <div class="row g-4">
                        <div class="col-md-3">
                            <div class="bg-white shadow-sm p-4 border rounded-3 h-100">
                                <div class="mb-3 text-primary fs-1">1</div>
                                <h5 class="mb-2">Signin</h5>
                                <p class="text-muted">You already have an account. Just signin to your account.</p>
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
<script>
    function togglePassword() {
        const passwordInput = document.getElementById("password");
        const toggleIcon = document.getElementById("toggleIcon");

        const isPassword = passwordInput.type === "password";
        passwordInput.type = isPassword ? "text" : "password";

        toggleIcon.classList.toggle('fa-eye');
        toggleIcon.classList.toggle('fa-eye-slash');
    }
</script>
<?php get_footer(); ?>