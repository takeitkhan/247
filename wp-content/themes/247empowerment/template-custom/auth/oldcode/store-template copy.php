<?php

/**
 * Template Name: Logged In Marketplace
 */
if (is_user_logged_in()) {
    get_header('portal');
} else {
    get_header('main');
}


if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

$current_user = wp_get_current_user();

// Get current logged-in user ID (used as a fallback if no slug is provided)
$current_user_id = get_current_user_id();

// 1. Get the user slug from the query variable
$user_slug = get_query_var('user_profile');

// 2. Determine the target user
if ($user_slug) {
    // If a slug is present, try to get the user by their slug (login or nicename)
    $user = get_user_by('slug', $user_slug);
} else {
    // If no slug, fall back to the currently logged-in user
    $user = get_user_by('ID', $current_user_id);
}

// 3. Instantiate the UserProfileData class and get the profile array
if ($user) {
    // We pass the WP_User object to the class constructor, or the ID/slug depending on the class's constructor.
    // Given your original line: $profile = (new UserProfileData($user_slug))->getProfile();
    // We'll update it to pass the $user object for better data handling, assuming the class supports it.
    // If the class REQUIRES a slug, use $user_slug or $user->user_login.

    // Option A: If UserProfileData takes a WP_User object (Recommended)
    $profile_data_instance = new UserProfileData($user);

    // Option B: If UserProfileData only takes the slug (Sticking closer to your original code)
    // Use the slug if present, otherwise use the current user's login.
    $target_identifier = $user_slug ? $user_slug : $user->user_login;
    $profile_data_instance = new UserProfileData($target_identifier);

    // Get the profile array
    $profile = $profile_data_instance->getProfile();
} else {
    // Set variables to null if no user could be determined
    $user = null;
    $profile = null;
}
?>


<div class="container profile-page pt20">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3">

            <?php get_template_part('template-custom/auth/common-parts/editprofilemenu', null, ['profile' => $profile]); ?>
            <?php
            $current_user = wp_get_current_user();
            $username = get_query_var('store_user') ?: $current_user->user_nicename;

            $terms = get_terms([
                'taxonomy' => 'course_category',
                'hide_empty' => false,
                'orderby' => 'name',
                'order' => 'ASC',
            ]);
            ?>

            <div class="bg-white custom-card navbar-link">
                <ul class="d-flex flex-column gap-2 nav">
                    <!-- Browse All -->
                    <li class="d-flex align-items-center gap-2 nav-item">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/img/marketplace/empower.png" alt="empower" class="icon-img">
                        <a href="<?php echo esc_url(home_url("/$username/store")); ?>" class="p-0 text">Browse All</a>
                    </li>

                    <!-- Dynamic Category List -->
                    <?php foreach ($terms as $term): ?>
                        <li class="d-flex align-items-center gap-2 nav-item">
                            <?php
                            $icon_id = get_term_meta($term->term_id, 'term_icon', true) ??  get_template_directory_uri() . '/assets/img/marketplace/default.png';
                            if ($icon_id) {
                                echo '<img class="icon-img" src="' . esc_url(wp_get_attachment_url($icon_id)) . '" alt="' . esc_attr($term->name) . '">';
                            }
                            ?>
                            <a href="<?php echo esc_url(home_url("/$username/store?category={$term->slug}")); ?>" class="p-0 text">
                                <?php echo esc_html($term->name); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php get_template_part('template-custom/auth/profile-parts/navlink', null, ['profile' => $profile]); ?>
        </div>

        <!-- Main Feed -->
        <div class="mb-4 col-lg-6">
            <div class="custom-card post-search">
                <div class="gap-3 mb-2 post-row">
                    <div class="d-flex pb-3 u-title">
                        <h5 class="portal-title">Popular programs</h5>
                    </div>
                    <p>Browse and buy any courses from your favorite instructors</p>
                </div>
            </div>

            <div class="d-flex flex-column">
                <?php
                $store_user = get_query_var('store_user');
                $category_slug = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';

                $args = [
                    'post_type'      => 'course',
                    'posts_per_page' => -1,
                ];

                if (!empty($category_slug)) {
                    $args['tax_query'] = [
                        [
                            'taxonomy' => 'course_category',
                            'field'    => 'slug',
                            'terms'    => $category_slug,
                        ],
                    ];
                }

                $query = new WP_Query($args);

                if ($query->have_posts()) :
                    while ($query->have_posts()) :
                        $query->the_post();

                        $price = get_field('price');
                        $short_details = get_field('short_details');
                        $instructor = get_field('instructor');
                        $duration = get_field('duration');
                        $rating = get_field('rating') ?: '5.0'; // Optional rating field (default 5.0)
                        $thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'medium') ?: get_template_directory_uri() . '/assets/img/default-course.jpg';

                        $store_user = get_query_var('store_user') ?: 'store';
                        $course_slug = get_post_field('post_name', get_the_ID());
                        $custom_permalink = home_url("/{$store_user}/store/{$course_slug}");
                        $shareable_link = home_url("/{$store_user}/store/{$course_slug}/?shareable=1");
                ?>

                        <a href="<?= esc_url($custom_permalink); ?>" class="mb-3 text-reset text-decoration-none">
                            <div class="bg-white custom-card">
                                <div class="d-flex">
                                    <div class="d-md-flex flex-row gap-3 res-card">
                                        <div>
                                            <div class="img240">
                                                <img class="w-100 h-100 object-fit-cover" src="<?= esc_url($thumbnail_url); ?>" alt="<?= esc_attr(get_the_title()); ?>">
                                            </div>
                                        </div>
                                        <div class="d-flex flex-column gap-1 mt-3 mt-md-0">
                                            <span class="fs14 emp-color"><?= esc_html(get_the_term_list(get_the_ID(), 'course_category', '', ', ', '')); ?></span>
                                            <p class="d-flex justify-content-between">
                                                <span class="fs20"><?= esc_html(get_the_title()); ?></span>
                                                <span class="gradient-fs24"><?= $price ? '$' . esc_html($price) : ''; ?></span>
                                            </p>
                                            <p class="d-flex align-items-center">
                                                <img src="<?= get_template_directory_uri(); ?>/assets/img/star.png" alt="Rating">
                                                <span class="ms-2 fw-medium"><?= esc_html($rating); ?></span>
                                            </p>
                                            <p><?= esc_html($short_details); ?></p>

                                            <div class="d-flex align-items-center justify-content-between">
                                                <?php if ($instructor) : ?>
                                                    <p><span class="fw-medium">Instructor:</span> <?= esc_html($instructor); ?></p>
                                                <?php endif; ?>
                                                <?php if ($duration) : ?>
                                                    <p><span class="fw-medium">Duration:</span> <span class="p-price"><?= esc_html($duration); ?></span></p>
                                                <?php endif; ?>
                                            </div>

                                            <div class="d-flex align-items-center justify-content-end gap-3 mt-1">
                                                <button
                                                    type="button"
                                                    class="d-flex align-items-center gap-2 text-primary-color"
                                                    onclick="copySharableLink('<?= esc_url($shareable_link); ?>')">
                                                    <img class="copy-img" src="<?= get_template_directory_uri(); ?>/assets/img/copy-link.png" alt=""> Copy link
                                                </button>
                                                <a href="<?= esc_url($custom_permalink); ?>" class="text-white text-decoration-none custom-btn-size background-primary">More</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endwhile;
                    wp_reset_postdata();
                else : ?>
                    <p>No courses found.</p>
                <?php endif; ?>
            </div>
        </div>

        <script>
            function copySharableLink(link) {
                navigator.clipboard.writeText(link);
                alert('Shareable link copied!');
            }
        </script>


        <!-- Upcoming Events -->
        <div class="col-lg-3">
            <div class="bg-white upcoming-events custom-card">
                <div class="d-flex pb-4 u-title">
                    <h5 class="portal-title">Popular programs</h5>

                </div>
                <div class="d-flex flex-column gap-4">
                    <div class="d-flex gap-3 event">
                        <div class="img44">
                            <img class="w-100 h-100 object-fit-cover" src="./images/profile.png" alt="">
                        </div>
                        <div class="d-flex flex-column gap-1">
                            <span class="fs14 emp-color">Personal Empowerment Teams</span>
                            <span class="fs18">Legacy Retreat</span>
                            <span class="p-price">Price: $500</span>
                        </div>
                    </div>
                    <div class="d-flex gap-3 event">
                        <div class="img44">
                            <img class="w-100 h-100 object-fit-cover" src="./images/profile.png" alt="">
                        </div>
                        <div class="d-flex flex-column gap-1">
                            <span class="fs14 emp-color">Personal Empowerment Teams</span>
                            <span class="fs18">Legacy Retreat</span>
                            <span class="p-price">Price: $500</span>
                        </div>
                    </div>
                    <div class="d-flex gap-3 event">
                        <div class="img44">
                            <img class="w-100 h-100 object-fit-cover" src="./images/profile.png" alt="">
                        </div>
                        <div class="d-flex flex-column gap-1">
                            <span class="fs14 emp-color">Personal Empowerment Teams</span>
                            <span class="fs18">Legacy Retreat</span>
                            <span class="p-price">Price: $500</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<main>
    <div class="main-container s-main-con">
        <div class="row g-3">
            <!-- Left Sidebar - Fixed on desktop -->
            <div class="d-md-block bottom-0 position-sticky col d-none">
                <div class="bg-white custom-box-shadow p-3 custom-border-radius h-100">
                    <?php include 'store-parts/left-column.php'; ?>
                </div>
            </div>

            <div class="ms-md-auto col-12 col-md-8 col-lg-9 col-xl-9">
                <div class="">
                    <div class="bg-white custom-box-shadow mb-3 p-3 custom-border-radius">
                        <h3 class="fw-bold market-title">Marketplace</h3>
                        <p class="m-text">Browse and buy courses from your favorite instructors.</p>
                    </div>
                    <div class="mb-3">
                        <?php
                        $store_user = get_query_var('store_user');
                        $category_slug = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';

                        $args = [
                            'post_type' => 'course',
                            'posts_per_page' => -1,
                        ];

                        if (!empty($category_slug)) {
                            $args['tax_query'] = [
                                [
                                    'taxonomy' => 'course_category',
                                    'field'    => 'slug',
                                    'terms'    => $category_slug,
                                ],
                            ];
                        }

                        $query = new WP_Query($args);

                        if ($query->have_posts()) : ?>
                            <div class="d-flex flex-column gap-3">
                                <?php while ($query->have_posts()) : $query->the_post();
                                    $price = get_field('price');
                                    $short_details = get_field('short_details');
                                    $instructor = get_field('instructor'); // ACF instructor field
                                    $duration = get_field('duration');     // ACF duration field
                                    $lessons = get_field('lessons');     // ACF duration field
                                    $thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'medium') ?: '/img/banner.jpg';
                                    $store_user = get_query_var('store_user') ?: 'store';
                                    $course_slug = get_post_field('post_name', get_the_ID());
                                    $custom_permalink = home_url("/{$store_user}/store/" . get_post_field('post_name'));
                                ?>

                                    <div class="bg-white custom-box-shadow p-3 custom-border-radius">
                                        <div class="d-flex flex-column flex-md-row">
                                            <div class="me-md-3 mb-3 mb-md-0">
                                                <div class="fixed-thumb">
                                                    <a href="<?= esc_url($custom_permalink); ?>">
                                                        <img
                                                            src="<?= esc_url($thumbnail_url); ?>"
                                                            alt="<?= esc_attr(get_the_title()); ?>" />
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="flex-fill">
                                                <h5 class="m-top-title d-flex justify-content-between fw-medium">

                                                    <a href="<?= esc_url($custom_permalink); ?>" class="text-dark text-decoration-none">
                                                        <?= esc_html(get_the_title()); ?>
                                                    </a>

                                                    <span><?= $price ? '$ ' . esc_html($price) : ''; ?></span>
                                                </h5>

                                                <div class="d-flex flex-wrap gap-3 mb-3">
                                                    <?php if ($instructor) : ?>
                                                        <span><strong>Instructor:</strong> <?= esc_html($instructor); ?></span>
                                                    <?php endif; ?>
                                                    <?php if ($duration) : ?>
                                                        <span><strong>Duration:</strong> <?= esc_html($duration); ?></span>
                                                    <?php endif; ?>
                                                    <?php if ($lessons) : ?>
                                                        <span><strong>Lessons:</strong> <?= esc_html($lessons); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <span class="m-text"><?= esc_html($short_details); ?></span>
                                                </div>

                                                <?php
                                                $shareable_link = home_url("/{$store_user}/store/{$course_slug}/?shareable=1");
                                                ?>

                                                <div class="d-flex flex-wrap gap-2 mt-3">
                                                    <!-- Copy Sharable Link Button -->
                                                    <button
                                                        class="d-flex align-items-center btn-outline-primary btn btn-sm"
                                                        onclick="copySharableLink('<?= esc_url($shareable_link); ?>')">
                                                        <i class="me-1 bi bi-link-45deg"></i> Copy Shareable Link
                                                    </button>

                                                    <!-- View Course Button -->
                                                    <a
                                                        href="<?= esc_url($custom_permalink); ?>"
                                                        class="d-flex align-items-center shadow-sm px-3 btn-outline-primary btn btn-sm">
                                                        <i class="me-1 bi bi-cart-fill"></i> View Course
                                                    </a>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                            <?php wp_reset_postdata(); ?>
                        <?php else : ?>
                            <p>No courses found.</p>
                        <?php endif; ?>
                    </div>



                </div>
            </div>
        </div>
    </div>
</main>
<script>
    function copySharableLink(link) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(link)
                .then(() => alert("Link copied to clipboard!"))
                .catch(err => alert("Copy failed: " + err));
        } else {
            // Fallback
            const textarea = document.createElement("textarea");
            textarea.value = link;
            textarea.style.position = "fixed"; // Avoid scrolling to bottom
            document.body.appendChild(textarea);
            textarea.focus();
            textarea.select();
            try {
                document.execCommand('copy');
                alert("Link copied to clipboard!");
            } catch (err) {
                alert("Copy failed (fallback): " + err);
            }
            document.body.removeChild(textarea);
        }
    }
</script>

<?php
if (is_user_logged_in()) {
    get_footer('portal');
} else {
    get_footer('main');
}
?>