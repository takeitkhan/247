<?php
/**
 * Header template
 * Clean version – no duplicate SEO, OG, Twitter, or analytics
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
    window.addEventListener('load', function () {

    const players = document.querySelectorAll('.ytplayer iframe');

    players.forEach((iframe) => {
        let src = iframe.getAttribute('src');

        // remove old params to avoid duplication
        src = src.replace(/(&|\?)autoplay=1/g, '');

        // add required params
        const newSrc = src + '&autoplay=1&mute=1&enablejsapi=1&playsinline=1';

        iframe.setAttribute('src', newSrc);
    });

    });
    </script>

    <?php
    /**
     * WordPress + Plugins output:
     * - <title>
     * - Canonical
     * - Open Graph
     * - Twitter Cards
     * - Schema
     * - Styles & Scripts
     */
    wp_head();
    ?>

    <!-- Google Tag Manager -->
    <script>
        (function(w,d,s,l,i){
            w[l]=w[l]||[];
            w[l].push({'gtm.start': new Date().getTime(),event:'gtm.js'});
            var f=d.getElementsByTagName(s)[0],
                j=d.createElement(s),
                dl=l!='dataLayer'?'&l='+l:'';
            j.async=true;
            j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;
            f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-MMZ2RG8C');
    </script>
    <!-- End Google Tag Manager -->

    <!-- Facebook Pixel -->
    <script>
        !function(f,b,e,v,n,t,s){
            if(f.fbq)return;
            n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;
            n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];
            t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s);
        }(window, document,'script','https://connect.facebook.net/en_US/fbevents.js');
        fbq('init','1430184528309439');
        fbq('track','PageView');
    </script>
    <!-- End Facebook Pixel -->
</head>

<body <?php body_class( 'bg-dark-custom' ); ?>>

<!-- Google Tag Manager (noscript) -->
<noscript>
    <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MMZ2RG8C"
            height="0" width="0" style="display:none;visibility:hidden"></iframe>
</noscript>
<!-- End Google Tag Manager (noscript) -->
<header class="custom-navbar shadow-sm">
    <div class="container container-home d-flex align-items-center justify-content-between w-100 h-100 py-2">

        <!-- LOGO -->
        <div class="d-flex align-items-center gap-1">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <img
                    src="<?php echo esc_url( get_theme_mod( 'large_logo' ) ); ?>"
                    alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
                    class="logo-nav">
            </a>
            <span class="logo-text fw-normal" style="color: #333;">
                <?php echo esc_html( get_bloginfo( 'name' ) ); ?>
            </span>
        </div>

        
        <!-- RIGHT SIDE -->
        <div class="d-flex align-items-center gap-3">

            <!-- AUTH MENU -->
            <?php
            wp_nav_menu( [
                'theme_location' => 'authentication',
                'container'      => false,
                'menu_class'     => 'd-flex align-items-center gap-3 list-unstyled mb-0',
                'items_wrap'     => '<ul class="%2$s">%3$s</ul>',
                'fallback_cb'    => false,
                'walker'         => new MM_Auth_Walker_Nav_Menu(),
            ] );
            ?>

            <!-- MOBILE TOGGLE -->
            <button class="btn btn-light menu-toggle" id="menuToggle" aria-label="Toggle Menu">
                <i class="bi bi-list"></i>
            </button>
        </div>
    </div>
    <hr class="p-0 m-0" />
    <div class="container container-home d-flex align-items-center justify-content-between w-100 h-100 bg-white">
        <!-- DESKTOP NAV -->
        <nav class="navbar">
            <ul class="navbar-nav desktop-menu">
                <?php
                wp_nav_menu( [
                    'theme_location' => 'primary',
                    'container'      => false,
                    'items_wrap'     => '%3$s',
                    'depth'          => 3,
                    'fallback_cb'    => false,
                    'walker'         => new MM_Walker_Nav_Menu_V2(),
                ] );
                ?>
            </ul>
        </nav>
    </div>

    <!-- MOBILE MENU -->
    <div class="mobile-menu bg-white" id="mobileMenu">
        <ul class="nav flex-column text-center">
            <?php
            wp_nav_menu( [
                'theme_location' => 'primary',
                'container'      => false,
                'items_wrap'     => '%3$s',
                'depth'          => 3,
                'fallback_cb'    => false,
                'walker'         => new MM_Walker_Nav_Menu(),
            ] );
            ?>
        </ul>
    </div>
</header>