<?php

/**
 * Template Name: Logged In Marketplace
 */
get_header();
?>
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

<?php get_footer(); ?>