<div class="dropdown">

    <button class="d-flex align-items-center gap-2 rounded-circle dropdown-toggle btn-custom" data-bs-toggle="dropdown"
        data-bs-auto-close="outside"
        aria-expanded="false">
        <div class="position-relative img44">
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/nd/'); ?>profile.png" class="rounded-circle w-100 h-100 object-fit-cover" alt="Profile">
            <img class="position-absolute active-icon" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/nd/'); ?>active_icon.png" alt="">
        </div>

    </button>

    <div class="shadow border-0 rounded-3 dropdown-menu profile-width dropdown-menu-end custom-card">
        <ul class="p-0">
            <?php if (is_user_logged_in()) :
                $current_user = wp_get_current_user();
                $first_name = $current_user->first_name;
                $last_name = $current_user->last_name;
                $full_name = trim($first_name . ' ' . $last_name) ?: $current_user->display_name;
                $profile_photo = get_user_meta(get_current_user_id(), 'profile_photo', true)
                    ?: get_template_directory_uri() . '/assets/img/loggedin_images/banner.jpg';

                $user_slug = $current_user->user_login;
                $profile_url = home_url('/' . $user_slug);
            ?>
                <!-- User info -->
                <li class="list-unstyled">
                    <a class="dropdown-item" href="<?php echo esc_url($profile_url); ?>">
                        <div class="d-flex align-items-center img44 gap10">
                            <img src="<?php echo esc_url($profile_photo); ?>" class="rounded-circle w-100 h-100 object-fit-cover" alt="Profile">
                            <span class="fs18 fw-medium"><?php echo esc_html($full_name); ?></span>
                        </div>
                    </a>
                </li>

                <!-- Update Profile -->
                <li class="py-2 pt-0 border-underline list-unstyled">
                    <a class="d-flex align-content-center dropdown-item gap10 fs18 fw-medium" href="<?php echo esc_url(home_url('/modify-profile')); ?>">
                        <div class="img24">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/nd/user_profile.png'); ?>" class="w-100 h-100 object-fit-contain mr10" alt="">
                        </div>
                        Update your profile
                    </a>
                </li>

                <!-- Report an issue -->
                <li class="py-2 list-unstyled">
                    <a class="d-flex align-content-center dropdown-item gap10 fs18 fw-medium" href="<?php echo esc_url(home_url('/report')); ?>">
                        <div class="img24">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/nd/circle-alert.png'); ?>" class="w-100 h-100 object-fit-contain" alt="">
                        </div>
                        Report an issue
                    </a>
                </li>

                <!-- Make a suggestion -->
                <li class="py-2 border-underline list-unstyled">
                    <a class="d-flex align-content-center dropdown-item gap10 fs18 fw-medium" href="<?php echo esc_url(home_url('/suggestion')); ?>">
                        <div class="img24">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/nd/suggestion.png'); ?>" class="w-100 h-100 object-fit-contain" alt="">
                        </div>
                        Make a suggestion
                    </a>
                </li>

                <!-- Dashboard (only for admins) -->
                <?php if (current_user_can('administrator')) : ?>
                    <li class="py-2 list-unstyled">
                        <a class="d-flex align-content-center dropdown-item gap10 fs18 fw-medium" href="<?php echo esc_url(admin_url()); ?>">
                            <div class="img24">
                                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/nd/dashboard.png'); ?>" class="w-100 h-100 object-fit-contain" alt="">
                            </div>
                            Dashboard
                        </a>
                    </li>
                <?php endif; ?>

                <!-- Sign Out -->
                <li class="py-2 list-unstyled">
                    <a class="d-flex align-content-center dropdown-item gap10 fs18 fw-medium" href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>">
                        <div class="img24">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/nd/log-out.png'); ?>" class="w-100 h-100 object-fit-contain" alt="">
                        </div>
                        Sign Out
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>

</div>