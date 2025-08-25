<?php

/**
 * Template Name: Video Library Page
 * Custom Video Library Page Template
 */

get_header(); ?>

<main>
    <div class="main-container" style="padding-top: 80px;">
        <div class="bg-white custom-box-shadow mb-3 p-4 custom-border-radius">

            <div class="my-4 text-center">
                <div class="d-flex flex-wrap align-items-center justify-content-center gap-2">
                    <a href="/support-teams" class="d-flex align-items-center px-3 py-2 rounded-pill btn btn-sm 
                        <?php echo is_page('support-teams') ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        <i class="me-2 bi bi-headset"></i> Support Teams
                    </a>
                    <a href="/faqs" class="d-flex align-items-center px-3 py-2 rounded-pill btn btn-sm 
                        <?php echo is_page('faqs') ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        <i class="me-2 bi bi-question-circle"></i> FAQ’s
                    </a>
                    <a href="/video-library" class="d-flex align-items-center px-3 py-2 rounded-pill btn btn-sm 
                        <?php echo is_page('video-library') ? 'btn-success' : 'btn-outline-success'; ?>">
                        <i class="me-2 bi bi-collection-play"></i> Video Library
                    </a>
                </div>
            </div>

            <div class="my-5 faq-content">
                <?php
                // Replace 123 with the actual Page ID where FAQ content is stored
                $faq_page_id = 22;
                $faq_page = get_post($faq_page_id);

                if ($faq_page) {
                    //echo '<h2 class="mb-4 text-center">' . esc_html(get_the_title($faq_page)) . '</h2>';
                    echo '<div class="faq-text">' . apply_filters('the_content', $faq_page->post_content) . '</div>';
                } else {
                    echo '<p class="text-danger">Video content not found. Please set the correct page ID.</p>';
                }
                ?>
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>