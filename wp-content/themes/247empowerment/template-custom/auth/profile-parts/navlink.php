<div class="bg-white custom-card navbar-link profile-menu-wrapper">

    <div class="d-flex justify-content-between align-items-center profile-menu-toggle">
        <h5 class="text-start portal-title mb-0">Menu</h5>
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
