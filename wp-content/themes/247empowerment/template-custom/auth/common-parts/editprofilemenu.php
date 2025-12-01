<div class="bg-white custom-card navbar-link">
    <div>
        <h5 class="text-start portal-title">Profile</h5>
    </div>
    <?php
    wp_nav_menu(array(
        'theme_location' => 'editprofilemenu',
        'container' => false,
        'menu_class' => 'nav d-flex flex-column gap10 menu-edit-profile-menu',
        'walker' => new Edit_Profile_Walker(),
    ));
    ?>
</div>