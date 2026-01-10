<?php
$profile = isset($args['profile']) ? $args['profile'] : [];
?>
<div class="profile-left bg-white custom-card">
    <div class="d-flex align-items-center justify-content-between pb-4 u-title">
        <h5 class="portal-title">About</h5>
        <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/post_option_icon.png" alt="">
    </div>
    <ul class="d-flex flex-column gap-2 nav">
        <?php if (!empty($profile['location'])) : ?>
            <li class="d-flex align-items-center gap-2">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/location_p.png" class="icon-img" alt="Location">
                <a href="#" class="p-0 p-link"><?php echo esc_html($profile['location']); ?></a>
            </li>
        <?php endif; ?>

        <?php if (!empty($profile['occupation'])) : ?>
            <li class="d-flex align-items-center gap-2">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/bag.png" class="icon-img" alt="Occupation">
                <a href="#" class="p-0 p-link"><?php echo esc_html($profile['occupation']); ?></a>
            </li>
        <?php endif; ?>

        <?php if (!empty($profile['website'])) : ?>
            <li class="d-flex align-items-center justify-content-between gap-2">
                <div class="d-flex gap-2">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/link.png" class="icon-img" alt="Website">
                    <a href="<?php echo esc_url($profile['website']); ?>" target="_blank" class="p-0 p-link text-primary-color">
                        <?php echo esc_html(wp_trim_words($profile['website'], 3, '...')); ?>
                    </a>
                </div>
                <img class="icon-img" src="<?php echo get_template_directory_uri(); ?>/assets/img/nd/copy.png" alt="Copy">
            </li>
        <?php endif; ?>
    </ul>
</div>
<div class="z-0 sticky-top">
    <div class="">
        <div
            class="bg-white custom-box-shadow p-3 custom-border-radius rounded"
            style="height: 100vh">
            <div class="d-flex align-items-center mb-3">
                <div class="">
                    <div class="d-flex align-items-center gap-2">
                        <div style="height: 40.9px; width: 40.9px">
                            <img
                                class="rounded-circle w-100 h-100 object-fit-cover"
                                src="<?php echo esc_url(get_user_meta(get_current_user_id(), 'profile_photo', true) ?: get_template_directory_uri() . '/assets/img/loggedin_images/banner.jpg'); ?>"
                                alt="" />
                        </div>
                        <div class="">
                            <h5
                                class="fw-medium"
                                style="font-size: 16px; line-height: 0.7">
                                <?php
                                $profile = UserProfileData::getInstance()->getProfile();
                                echo esc_html($profile['first_name'] . ' ' . $profile['last_name']);
                                ?>
                            </h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Links -->
            <ul class="flex-column nav">
                <?php
                $current_user = wp_get_current_user();
                $username = $current_user->user_nicename;
                ?>
                <li class="bg-hover-light mb-1 p-2 nav-item">
                    <a class="d-flex align-items-center text-decoration-none nav-link-custom" href="#">
                        <span class="me-2 icon-circle">
                            <i class="bi bi-camera-video-fill"></i>
                        </span>
                        Video
                    </a>
                </li>
                <li class="bg-hover-light mb-1 p-2 nav-item">
                    <a class="d-flex align-items-center text-decoration-none nav-link-custom" href="<?php echo esc_url(home_url("/$username/store")); ?>">
                        <span class="me-2 icon-circle">
                            <i class="bi bi-basket-fill"></i>
                        </span>
                        Marketplace
                    </a>
                </li>
            </ul>

        </div>
    </div>
</div>