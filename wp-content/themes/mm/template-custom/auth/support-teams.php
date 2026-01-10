<?php

/**
 * Template Name: Support Teams
 * Custom Support Teams Page Template
 */
get_header();
?>

<main>
    <div class="main-container" style="padding-top: 80px;">
        <div class="bg-white custom-box-shadow mb-3 p-3 custom-border-radius">            
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
            <div class="my-5 container-fluid">
                <ul class="nav nav-tabs fs-6" id="homeTab" role="tablist">
                    <?php
                    // Query to get the tabs
                    $tabs_query = new WP_Query(array(
                        'post_type' => 'tab',
                        'posts_per_page' => -1,
                        'orderby' => 'menu_order',
                        'order' => 'ASC'
                    ));

                    if ($tabs_query->have_posts()) :
                        $active_class = 'active'; // To add the active class to the first tab
                        while ($tabs_query->have_posts()) : $tabs_query->the_post();
                            // Get the custom fields
                            $tab_title = get_the_title();
                    ?>
                            <li class="nav-item" role="presentation">
                                <button class="custom-tab-button <?php echo $active_class; ?>" id="<?php echo sanitize_title($tab_title); ?>-tab" data-bs-toggle="tab" data-bs-target="#<?php echo sanitize_title($tab_title); ?>" type="button" role="tab" aria-controls="<?php echo sanitize_title($tab_title); ?>" aria-selected="true"><?php echo esc_html($tab_title); ?></button>
                            </li>
                    <?php
                            $active_class = ''; // After the first tab, no need to add "active"
                        endwhile;
                    endif;
                    ?>
                </ul>

                <div class="p-3 text-left tab-content transparent-bg" id="homeTabContent">
                    <?php
                    // Reset the loop to fetch the content for each tab
                    if ($tabs_query->have_posts()) :
                        $active_class = 'show active'; // To add the active class to the first tab content
                        while ($tabs_query->have_posts()) : $tabs_query->the_post();
                            // Get the custom fields
                            $tab_title = get_the_title();
                            $tab_content = get_the_content(); // Get the raw content

                            // Allow specific tags like <p>, <br>, etc.
                            $allowed_tags = array(
                                'p' => array(),
                                'br' => array(),
                                'strong' => array(),
                                'em' => array(),
                                'ul' => array(),
                                'ol' => array(),
                                'li' => array(),
                                'blockquote' => array(),
                                'a' => array(
                                    'href' => array(),
                                    'title' => array(),
                                    'target' => array(),
                                    'rel' => array(),
                                    'class' => array()
                                ),
                            );
                            $tab_content = wp_kses($tab_content, $allowed_tags);
                    ?>
                            <div class="tab-pane fade <?php echo $active_class; ?>" id="<?php echo sanitize_title($tab_title); ?>" role="tabpanel" aria-labelledby="<?php echo sanitize_title($tab_title); ?>-tab">
                                <div><?php echo wp_kses_post($tab_content); ?></div>
                            </div>
                    <?php
                            $active_class = ''; // After the first tab content, no need to add "show active"
                        endwhile;
                    endif;
                    ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>