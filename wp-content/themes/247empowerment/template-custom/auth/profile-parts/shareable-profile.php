<?php
$user_slug = get_query_var('user_profile');
$user = get_user_by('slug', $user_slug);
$profile = (new UserProfileData($user_slug))->getProfile();

echo '<pre>';
var_dump($profile); // Debugging output
echo '</pre>';

$profile_img = esc_url(get_user_meta($user->ID, 'profile_photo', true) ?: get_template_directory_uri() . '/assets/img/loggedin_images/banner.jpg');
$full_name = esc_html($profile['first_name'] . ' ' . $profile['last_name']);
$digital_card_about = esc_html($profile['digital_card_about'] ?? 'No short bio available.');
$place_display_name = esc_html($profile['place_display_name'] ?? 'Location not available');
$keywords = $profile['keywords']['list'] ?? [];
$hashtags = $profile['hashtags']['list'] ?? [];
$industry = '';

if (!empty($profile['user_category_names'])) {
    $industry = is_array($profile['user_category_names'])
        ? implode(', ', array_map('esc_html', $profile['user_category_names']))
        : esc_html($profile['user_category_names']);
} else {
    $industry = 'Not specified';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Guest Profile - <?php bloginfo('name'); ?></title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php
    // Assuming $profile is your user data array
    $display_name = sanitize_title($profile['display_name'] ?? 'guest'); // fallback to 'guest'
    $site_url = home_url(); // e.g., http://pet.test

    // Construct values for meta tags
    $page_url = "{$site_url}/{$display_name}";
    $page_title = $profile['first_name'] . ' ' . $profile['last_name'];
    $page_description = $profile['about_me_short'] ?: 'Welcome to my profile.';
    $page_image = $profile['profile_photo'] ?: get_template_directory_uri() . '/assets/img/loggedin_images/banner.jpg';
    ?>

    <!-- Canonical Link -->
    <link rel="canonical" href="<?php echo esc_url($page_url); ?>" />

    <!-- Open Graph meta tags -->
    <meta property="og:title" content="<?php echo esc_attr($page_title); ?>" />
    <meta property="og:description" content="<?php echo esc_attr($page_description); ?>" />
    <meta property="og:image" content="<?php echo esc_url($page_image); ?>" />
    <meta property="og:type" content="profile" />
    <meta property="og:url" content="<?php echo esc_url($page_url); ?>" />

    <!-- Twitter meta tags -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?php echo esc_attr($page_title); ?>" />
    <meta name="twitter:description" content="<?php echo esc_attr($page_description); ?>" />
    <meta name="twitter:image" content="<?php echo esc_url($page_image); ?>" />
    <?php wp_head(); ?>
</head>

<body>
    <div class="py-5 container">
        <div class="justify-content-center row">
            <div class="col-md-5 col-lg-4">

                <div class="shadow border-0 rounded-4 overflow-hidden card">

                    <!-- QR Section -->
                    <div class="py-4 text-center">
                        <img src="qr-code.png" alt="QR Code" class="img-fluid" style="max-width:180px;">
                    </div>

                    <!-- Brand Bar -->
                    <div class="py-4 text-white text-center fw-semibold" style="background-color: #05489C;">
                        24/7 Empowerment
                    </div>

                    <!-- Profile -->
                    <div class="px-4 card-body">
                        <img src="<?php echo $profile_img; ?>"
                            class="shadow border border-3 border-white rounded-circle"
                            width="90" height="90"
                            style="margin-top:-55px; object-fit:cover;"
                            alt="Profile Photo" />

                        <h5 class="mt-3 mb-1 fw-bold">
                            <?php echo $full_name; ?>
                        </h5>

                        <?php if ($designation): ?>
                            <p class="mb-2 text-primary designation">
                                <?php echo esc_html($designation); ?>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($digital_card_about) && $digital_card_about !== 'No short bio available.'): ?>
                            <p class="mb-3 text-muted">
                                <?php echo $digital_card_about; ?>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($keywords)) : ?>
                            <div class="d-flex flex-wrap gap-2 mb-3 text-muted keyword-list">
                                <?php foreach ($keywords as $keyword) : ?>
                                    <span class="keyword-item">
                                        <?= esc_html($keyword); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <!-- Tags -->
                        <?php if (!empty($hashtags)) : ?>
                            <div class="d-flex flex-wrap gap-2 mb-3 text-muted">
                                <?php foreach ($hashtags as $hashtag) : ?>
                                    <span class="bg-light border border-light text-black badge">
                                        <?= esc_html($hashtag); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>


                        <!-- Location -->
                        <?php if (!empty($place_display_name) && $place_display_name !== 'Unknown location'): ?>
                            <p class="mb-4 text-muted small">
                                <i class="bi bi-geo-alt"></i>
                                <?php echo $place_display_name; ?>
                            </p>
                        <?php endif; ?>

                        <!-- Actions -->
                        <?php
                        $user_slug = get_query_var('user_profile');
                        $ref = urlencode($user_slug);

                        $signup_url = home_url("/signup?ref={$ref}");
                        $signin_url = home_url("/signin?ref={$ref}");
                        ?>

                        <div class="d-flex align-items-center justify-content-end gap-3">
                            <a href="<?php echo esc_url($signup_url); ?>" class="">Sign Up</a>
                            <a href="<?php echo esc_url($signin_url); ?>" class="custom-btn">Sign In</a>
                        </div>
                        <?php wp_footer(); ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</body>

</html>