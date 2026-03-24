<div class="bg-white custom-card navbar-link edit-profile-wrapper">

    <div class="d-flex justify-content-between align-items-center edit-profile-toggle">
        <h5 class="text-start portal-title mb-0">Profile</h5>
        <span class="toggle-icon">&#9662;</span>
    </div>

    <div class="edit-profile-menu">
        <?php
        wp_nav_menu([
            'theme_location' => 'editprofilemenu',
            'container' => false,
            'menu_class' => 'nav d-flex flex-column xgap10 menu-edit-profile-menu',
            'walker' => new Edit_Profile_Walker(),
        ]);
        ?>
        <!-- Social Media Settings Link -->
        <a href="<?php echo esc_url(home_url('/social-media-settings')); ?>" class="nav-link d-flex align-items-center gap-2" style="padding: 8px 0; color: #666; text-decoration: none;">
            <i class="bi bi-share2" style="font-size: 16px;"></i>
            <span>Social Media</span>
        </a>
    </div>

</div>
