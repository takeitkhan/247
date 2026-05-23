<div class="bg-white custom-card navbar-link edit-profile-wrapper">

    <div class="d-flex align-items-center justify-content-between edit-profile-toggle">
        <h5 class="mb-0 text-start portal-title">Profile</h5>
        <span class="toggle-icon">&#9662;</span>
    </div>

    <div class="edit-profile-menu">
        <?php
        wp_nav_menu([
            'theme_location' => 'editprofilemenu',
            'container' => false,
            'menu_class' => 'nav d-flex flex-column gap-2 menu-edit-profile-menu',
            'walker' => new Edit_Profile_Walker(),
        ]);
        ?>
    </div>

</div>

<!-- Zoom Menu Section -->
<div class="bg-white mt-3 custom-card navbar-link zoom-menu-wrapper">

    <div class="d-flex align-items-center justify-content-between zoom-menu-toggle">
        <h5 class="mb-0 text-start portal-title">Collaboration with Zoom</h5>
        <span class="toggle-icon">&#9662;</span>
    </div>

    <div class="zoom-menu-content">
        <?php
        wp_nav_menu([
            'theme_location' => 'zoommenu',
            'container' => false,
            'menu_class' => 'nav d-flex flex-column gap-2 menu-zoom',
            'walker' => new Zoom_Menu_Walker(),
            'fallback_cb' => false,
        ]);
        ?>
    </div>

</div>
