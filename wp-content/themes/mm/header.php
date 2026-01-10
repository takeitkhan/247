<?php
global $post;

// Check if the page is the front page (custom or default)
if (is_front_page()) {
    if (is_home()) {
        // Default blog page, no need to change this, unless you want specific settings.
        $og_title = get_bloginfo('name');
        $og_description = get_bloginfo('description');
        $og_url = home_url();
        $og_image = get_template_directory_uri() . '/assets/img/helping_image.jpg'; // fallback image
    } else {
        // Custom front page
        $og_title = get_the_title(get_option('page_on_front')); // Title of the custom front page
        $og_description = get_the_excerpt(get_option('page_on_front')); // Excerpt of the custom front page
        $og_url = home_url(); // Front page URL
        $og_image = get_the_post_thumbnail_url(get_option('page_on_front'), 'full'); // Get image from custom front page
        if (empty($og_image)) {
            $og_image = get_template_directory_uri() . '/assets/img/helping_image.jpg'; // fallback if no image
        }
    }
} elseif (is_singular()) {
    // For posts or pages
    setup_postdata($post);
    $og_title = get_the_title($post);
    $og_description = get_the_excerpt($post);
    $og_url = get_permalink($post);

    if (has_post_thumbnail($post)) {
        $og_image = get_the_post_thumbnail_url($post, 'full');
    } else {
        $og_image = get_template_directory_uri() . '/assets/img/helping_image.jpg';
    }
    wp_reset_postdata();
} else {
    // Fallback for other pages
    $og_title = get_bloginfo('name');
    $og_description = get_bloginfo('description');
    $og_url = home_url();
    $og_image = get_template_directory_uri() . '/assets/img/helping_image.jpg';
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title><?php echo esc_html($og_title); ?> | <?php bloginfo('name'); ?></title>


    <!-- Open Graph Meta -->
    <meta property="fb:app_id" content="1389188676349074" />
    <meta property="og:title" content="<?php echo esc_attr($og_title); ?>" />
    <meta property="og:description" content="<?php echo esc_attr($og_description); ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?php echo esc_url($og_url); ?>" />
    <meta property="og:image" content="<?php echo esc_url($og_image); ?>" />

    <!-- Twitter Meta -->
    <meta name="twitter:card" content="<?php echo esc_attr($og_title); ?>">
    <meta name="twitter:title" content="<?php echo esc_attr($og_title); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr($og_description); ?>">
    <meta name="twitter:image" content="<?php echo esc_url($og_image); ?>">

    <link rel="canonical" href="<?php echo esc_url($og_url); ?>" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- Toastify CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

    <!-- Toastify JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const storedReferrer = sessionStorage.getItem("referred_by");

            if (!storedReferrer) {
                const segments = window.location.pathname.split('/');
                const referrer = segments[1]; // 'joseph'

                if (referrer) {
                    sessionStorage.setItem("referred_by", referrer);
                    console.log(`Referral set to ${referrer}`);
                }
            } else {
                console.log(`Already referred by ${storedReferrer}`);
            }
        });

    </script>

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-C3V0VTNC42"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-C3V0VTNC42');
    </script>
    <!-- Google Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-MMZ2RG8C');
    </script>
    <!-- End Google Tag Manager -->
     <!-- Facebook Pixel Code --> <script nonce="gnuSF21j"> !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod? n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n; n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0; t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,'script','https://connect.facebook.net/en_US/fbevents.js');  fbq('init', '1430184528309439'); fbq('track', "PageView");</script> <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=1430184528309439&ev=PageView&noscript=1" /></noscript> <!-- End Facebook Pixel Code -->
    <?php wp_head(); ?>
</head>
<?php if (is_user_logged_in()) : ?>
    <?php
    $custom_header = get_template_directory() . '/template-custom/header.php';
    if (file_exists($custom_header)) {
        include $custom_header;
    } else {
        echo "<p>Missing: $custom_header</p>";
    }
    ?>
<?php else : ?>
    <?php
    $main_header = get_template_directory() . '/template-main/header.php';
    if (file_exists($main_header)) {
        include $main_header;
    } else {
        echo "<p>Missing: $main_header</p>";
    }
    ?>
<?php endif; ?>