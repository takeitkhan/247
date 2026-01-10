<div class="bg-white custom-card navbar-link">
    <?php
    wp_nav_menu([
        'theme_location' => 'profilemenu',
        'container'      => false,
        'menu_class'     => 'nav d-flex flex-column gap-2 menu-edit-profile-menu',
        'walker'         => new Profile_Menu_Walker(),
    ]);
    ?>
</div>