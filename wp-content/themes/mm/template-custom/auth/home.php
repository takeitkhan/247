<?php

/**
 * Template Name: Logged In Front Page
 */
get_header();
// Get current user ID
$current_user_id = get_current_user_id();

?>
<main>
    <div class="main-container" style="padding-top: 80px">
        <div class="">
            <div class="row g-3">
                <div class="d-lg-block col d-none">
                    <?php include 'profile-parts/home-left-sidebar.php'; ?>
                </div>
                <div class="mx-auto col-lg-6 col-sm-8">
                    <div class="mb-3">
                        <?php include 'profile-parts/create-post.php'; ?>
                    </div>
                    <div>
                        <?php include 'profile-parts/feeds.php'; ?>
                    </div>
                </div>
                <div class="d-lg-block sticky-right col d-none">
                    <?php include 'profile-parts/referral-partners-birthday.php'; ?>
                </div>
            </div>
        </div>
    </div>
</main>
<?php get_footer(); ?>