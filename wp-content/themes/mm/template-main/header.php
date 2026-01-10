<body class="bg-dark-custom">
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MMZ2RG8C"
            height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <nav class="bg-dark-custom navbar d-lg-none">
        <div class="container-fluid">
            <button class="border-0 navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMobile"
                aria-controls="sidebarMobile">
                <i class="bi bi-list fs-2"></i>
            </button>

            <a href="<?php echo esc_url(home_url('/')); ?>" class="mx-auto text-custom-dark text-decoration-none fw-bold">
                <span class="mx-auto fw-bold">
                    <?php echo esc_html(get_theme_mod('mm_header_text_line1', 'Personal')); ?>
                    <?php echo esc_html(get_theme_mod('mm_header_text_line2', 'Empowerment')); ?>
                    <?php echo esc_html(get_theme_mod('mm_header_text_line3', 'Teams, Inc.')); ?>
                </span>
            </a>
        </div>
    </nav>
    <div class="bg-dark-custom offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebarMobile"
        data-bs-scroll="true" data-bs-backdrop="true">
        <div class="offcanvas-header">
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
                aria-label="Close"></button>
        </div>
        <div class="px-4 offcanvas-body">
            <div class="d-flex flex-column gap-4">
                <h4 class="m-0 pt-5 fw-bold">
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="d-inline-block mx-auto text-custom-dark text-decoration-none fw-bold">
                        <?php echo esc_html(get_theme_mod('mm_header_text_line1', 'Personal')); ?><br />
                        <?php echo esc_html(get_theme_mod('mm_header_text_line2', 'Empowerment')); ?><br />
                        <?php echo esc_html(get_theme_mod('mm_header_text_line3', 'Teams, Inc.')); ?>
                    </a>
                </h4>


                <nav class="flex-column gap-3 pt-5 nav">
                    <?php
                    wp_nav_menu([
                        'theme_location' => 'primary',
                        'container' => false,
                        'menu_class' => 'p-0  nav-link fw-bold',
                        'link_before' => '',
                        'link_after' => '',
                        'fallback_cb' => false,
                    ]);
                    ?>
                </nav>
            </div>
        </div>
    </div>

    <div class="p-0 container-fluid">
        <div class="row g-0">
            <aside class="d-lg-flex flex-column p-5 col-md-2 d-none" style="min-height: 100vh;">
                <div class="d-flex flex-column gap-4">
                    <div class="m-0 pt-5 company_name">
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="d-inline-block mx-auto text-custom-dark text-decoration-none xfw-bold">
                            <img style="width: 120px;" src="<?php echo esc_url(get_theme_mod('large_logo')); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
                            <br />
                            <br />
                            <?php echo esc_html(get_theme_mod('mm_header_text_line1', 'Personal')); ?><br />
                            <?php echo esc_html(get_theme_mod('mm_header_text_line2', 'Empowerment')); ?><br />
                            <?php echo esc_html(get_theme_mod('mm_header_text_line3', 'Teams, Inc.')); ?>
                        </a>
                    </div>
                    <nav class="flex-column gap-3 pt-3 nav">
                        <?php
                        wp_nav_menu([
                            'theme_location' => 'primary',
                            'container' => false,
                            'menu_class' => 'p-0  nav-link xfw-bold',
                            'link_before' => '',
                            'link_after' => '',
                            'fallback_cb' => false,
                            //'walker' => new MM_Walker_Nav_Menu(),
                        ]);
                        ?>
                    </nav>
                </div>
            </aside>
            <main class="justify-content-between shadow col-lg-10 col-12" style="min-height: 100vh;">
                <div class="align-items-center justify-content-center"></div>