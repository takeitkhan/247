<div class="bg-white custom-card navbar-link profile-menu-wrapper">

    <div class="d-flex align-items-center justify-content-between profile-menu-toggle">
        <h5 class="mb-0 text-start portal-title">Menu</h5>
        <span class="toggle-icon">&#9662;</span>
    </div>

    <div class="profile-menu-content">
        <?php
        wp_nav_menu([
            'theme_location' => 'profilemenu',
            'container'      => false,
            'menu_class'     => 'nav d-flex flex-column gap-2 menu-edit-profile-menu',
            'walker'         => new Profile_Menu_Walker(),
        ]);
        ?>
    </div>
</div>
<div class="bg-white custom-card">
    <div class="d-flex align-items-center justify-content-between profile-menu-toggle">
        <h5 class="mb-0 text-start portal-title">Book Appointment</h5>
        <span class="toggle-icon">&#9662;</span>
    </div>
    <?php
    // Zoom Booking Form Section
    $user_id = get_current_user_id();

    if (!$user_id) {
        echo '<div class="alert alert-warning" role="alert">Please log in to book appointments.</div>';
    } elseif (function_exists('zoom_is_connected') && zoom_is_connected($user_id)) {
        // Zoom is connected - show the booking form
        echo do_shortcode('[zoom_book_appointment]');
    } else {
        // Zoom not connected - show connect link
    ?>
        <div class="alert alert-info" role="alert">
            <h6 class="mb-2">
                <i class="bi bi-link-45deg"></i> Connect Zoom Account
            </h6>
            <p class="mb-3 small">
                Connect your Zoom account to book and manage meetings directly from your profile.
            </p>
            <a href="<?php echo esc_url(home_url('/connect-zoom/')); ?>" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-circle"></i> Connect Zoom
            </a>
        </div>
    <?php
    }
    ?>
</div>