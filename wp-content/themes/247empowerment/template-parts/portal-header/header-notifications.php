<div class="dropdown">

    <button
        class="position-relative bg-supporting rounded-circle img44 btn-custom btn-focus"
        type="button"
        data-bs-toggle="dropdown"
        data-bs-auto-close="outside"
        aria-expanded="false">
        <img class="object-fit-contain notification-png" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/nd/'); ?>notification.png" alt="">
        <img class="position-absolute n-badge" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/nd/'); ?>circle.png" alt="">
    </button>

    <div class="shadow border-0 rounded-3 dropdown-menu notification-width dropdown-menu-end custom-card">
        <ul class="p-0">
            <div class="d-flex align-items-center justify-content-between pb-4">
                <p class="mb-0 text-black fw-bold">Notifications</p>
                <span class="text-blue-color fs14">Mark all as read</span>
            </div>

            <div class="d-flex align-items-center gap-3 pb-3">
                <div class="d-flex align-items-center gap10">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/nd/'); ?>circle-notification.png" alt="">
                    <div class="position-relative img44">
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/nd/'); ?>profile.png" class="rounded-circle w-100 h-100 object-fit-cover" alt="Profile">
                        <img class="position-absolute active-icon" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/nd/'); ?>active_icon.png" alt="">
                    </div>
                </div>

                <div class="d-flex flex-column gap-2 post-user">
                    <span class="p_name">Maria Johnson</span>
                    <p class="mb-0 text-blue-color fs14">24 minutes ago</p>
                </div>
            </div>
        </ul>
    </div>
</div>