<?php
/* Template Name: My Zoom Meetings */
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

get_header();
?>
<main>
    <div class="main-container" style="padding-top: 80px">
        <div class="row g-3">
            <?php include get_template_directory() . '/template-custom/auth/profile-parts/edit-profile-left-sidebar.php'; ?>

            <div class="ms-md-auto col-12 col-md-8 col-lg-9 col-xl-9">
                <div class="bg-white custom-box-shadow mb-3 p-3 custom-border-radius">
                    <div class="row">
                        <div class="col-6">
                            <h5 class="mb-5">📹 My Zoom Meetings</h5>
                        </div>
                    </div>
                    <?php
                    // echo '<pre>';
                    // print_r($profile);
                    // echo '</pre>';
                    echo do_shortcode('[zoom_appointments]');
                    ?>
                </div>
            </div>
        </div>
    </div>
    </div>
</main>

<?php
get_footer();
