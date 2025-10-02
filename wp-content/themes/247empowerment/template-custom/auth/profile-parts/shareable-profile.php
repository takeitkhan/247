<?php
$user_slug = get_query_var('user_profile');
$user = get_user_by('slug', $user_slug);
$profile = (new UserProfileData($user_slug))->getProfile();

// echo '<pre>';
// var_dump($profile); // Debugging output
// echo '</pre>';

$profile_img = esc_url(get_user_meta($user->ID, 'profile_photo', true) ?: get_template_directory_uri() . '/assets/img/loggedin_images/banner.jpg');
$full_name = esc_html($profile['first_name'] . ' ' . $profile['last_name']);
$short_bio = esc_html($profile['about_me_short'] ?? 'No short bio available.');
$location = esc_html($profile['location'] ?? 'Unknown location');
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
    $page_image = $profile['profile_photo'] ?: get_template_directory_uri() . '/assets/img/default-profile.png';
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

<body <?php body_class(); ?>>

    <div class="profile-img-wrapper">
        <img src="<?php echo $profile_img; ?>" alt="Profile Photo" />
    </div>

    <h1><?php echo $full_name; ?></h1>

    <?php if (!empty($short_bio) && $short_bio !== 'No short bio available.'): ?>
        <div class="info-item"><strong>Bio:</strong> <?php echo $short_bio; ?></div>
    <?php endif; ?>

    <?php if (!empty($location) && $location !== 'Unknown location'): ?>
        <div class="info-item"><strong>Location:</strong> <?php echo $location; ?></div>
    <?php endif; ?>

    <?php if (!empty($industry) && $industry !== 'Not specified'): ?>
        <div class="info-item"><strong>Industry:</strong> <?php echo $industry; ?></div>
    <?php endif; ?>

    <p><em>You must be logged in to view full profile details.</em></p>

    <?php
    $user_slug = get_query_var('user_profile');
    $ref = urlencode($user_slug);

    $signup_url = home_url("/signup?ref={$ref}");
    $signin_url = home_url("/signin?ref={$ref}");
    ?>
    <div class="btn-wrapper">
        <a class="btn btn-secondary" href="<?php echo esc_url($signin_url); ?>">Sign In</a>
        <a class="btn btn-primary" href="<?php echo esc_url($signup_url); ?>">Sign Up</a>        
    </div>

    <?php wp_footer(); ?>

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f9fafb;
            color: #222;
            max-width: 720px;
            margin: 40px auto;
            padding: 30px;
            box-shadow: 0 6px 20px rgb(0 0 0 / 0.1);
            border-radius: 12px;
            text-align: center;
        }

        .profile-img-wrapper {
            width: 140px;
            height: 140px;
            margin: 0 auto 1rem;
            border-radius: 50%;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .profile-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        h1 {
            font-weight: 700;
            font-size: 2rem;
            color: #0a3d62;
            margin-bottom: 0.2em;
        }

        .info-item {
            margin-bottom: 1rem;
            color: #444;
        }

        .info-item strong {
            color: #0984e3;
        }

        .btn {
            display: inline-block;
            margin: 10px;
            padding: 0.6rem 1.5rem;
            border-radius: 30px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background-color: #0984e3;
            color: white;
        }

        .btn-primary:hover {
            background-color: #065a9e;
        }

        .btn-secondary {
            background-color: #55efc4;
            color: #0a3d62;
        }

        .btn-secondary:hover {
            background-color: #32b28b;
        }

        @media (max-width: 480px) {
            body {
                margin: 20px 10px;
                padding: 20px;
            }

            h1 {
                font-size: 1.6rem;
            }
        }
    </style>
</body>

</html>