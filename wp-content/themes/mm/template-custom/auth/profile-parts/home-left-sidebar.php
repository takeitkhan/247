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
