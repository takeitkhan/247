<?php

/**
 * Template Name: Logged In Events
 */
get_header();
?>
<main>
    <div class="main-container s-main-con">
        <div class="row g-3">
            <div class="d-md-block bottom-0 position-sticky col d-none">
                <div class="bg-white custom-box-shadow p-3 custom-border-radius h-100">
                    <?php include 'event-parts/left-column.php'; ?>
                </div>
            </div>

            <div class="ms-md-auto col-12 col-md-8 col-lg-9 col-xl-9">
                <div class="">
                    <div class="bg-white custom-box-shadow mb-3 p-3 custom-border-radius">
                        <h3 class="fw-bold market-title">Events</h3>
                        <p class="m-text">Browse upcoming events from your favorite organizers.</p>
                    </div>
                    <div class="mb-3">
                        <?php
                        $store_user = get_query_var('store_user');
                        $category_slug = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';

                        $args = [
                            'post_type' => 'event',
                            'posts_per_page' => -1,
                        ];

                        if (!empty($category_slug)) {
                            $args['tax_query'] = [
                                [
                                    'taxonomy' => 'event_category',
                                    'field'    => 'slug',
                                    'terms'    => $category_slug,
                                ],
                            ];
                        }

                        $query = new WP_Query($args);

                        if ($query->have_posts()) : ?>
                            <div class="d-flex flex-column gap-3">
                                <?php while ($query->have_posts()) : $query->the_post();
                                    $event_date = get_field('event_date');
                                    $location   = get_field('location');
                                    $thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'medium') ?: '/img/banner.jpg';
                                    $event_user = get_query_var('event_user'); // username from URL
                                    $event_slug = get_post_field('post_name', get_the_ID());
                                    $custom_permalink = home_url("/{$event_user}/event/{$event_slug}/");
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
                                                </h5>

                                                <div class="d-flex flex-wrap gap-3 mb-3">
                                                    <?php if ($event_date) : ?>
                                                        <span><strong>Date:</strong> <?= esc_html($event_date); ?></span>
                                                    <?php endif; ?>
                                                    <?php if ($location) : ?>
                                                        <span><strong>Location:</strong> <?= esc_html($location); ?></span>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <span class="m-text"><?= esc_html(get_the_excerpt()); ?></span>
                                                </div>

                                                <div class="d-flex flex-wrap gap-2 mt-3">
                                                    <!-- View Event Button -->
                                                    <a
                                                        href="<?= esc_url($custom_permalink); ?>"
                                                        class="d-flex align-items-center shadow-sm px-3 btn-outline-primary btn btn-sm">
                                                        <i class="me-1 bi bi-eye-fill"></i> View Event
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>

                            <?php wp_reset_postdata(); ?>
                        <?php else : ?>
                            <p>No events found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>