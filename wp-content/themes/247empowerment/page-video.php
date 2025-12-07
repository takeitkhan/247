<?php

/**
 * Template Name: Video Library Page
 */

get_header_based_on_login();
?>

<div class="mb-5 container1030 pt20">
    <!-- PAGE TITLE -->
    <div class="mb-4 text-center">
        <h3 class="fs48">Watch. Learn. Empower.</h3>
        <p>Curated videos to uplift and inspire our community.</p>
    </div>

    <!-- MAIN FEATURED PLAYLIST -->
    <div class="video-responsive-fixed-height mb-5">
        <?php
        // Get first featured playlist
        $featured_playlist_query = new WP_Query([
            'post_type'      => 'video',
            'meta_key'       => '_video_featured',
            'meta_value'     => 1,
            'posts_per_page' => 1,
        ]);

        if ($featured_playlist_query->have_posts()) :
            $featured_playlist_query->the_post();
            $playlist_url = get_post_meta(get_the_ID(), '_playlist_url', true);
            $embed_playlist_url = mm_extract_youtube_embed($playlist_url);
            $total_videos = get_post_meta(get_the_ID(), '_playlist_total_videos', true);
        ?>
            <iframe width="100%" height="100%" src="<?php echo esc_url($embed_playlist_url); ?>" frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen></iframe>
            <p class="mt-2 text-center"><?php echo esc_html($total_videos) . ' videos'; ?></p>
        <?php
            wp_reset_postdata();
        endif;
        ?>
    </div>

    <!-- FEATURED THIS WEEK -->
    <div class="mt-5">
        <h4 class="fs24 fw-bold">Featured this week</h4>
        <div class="mt-4 row g-4">
            <?php
            $featured_list = new WP_Query([
                'post_type'      => 'video',
                'meta_key'       => '_video_featured',
                'meta_value'     => 1,
                'posts_per_page' => 2,
                'offset'         => 1 // skip main featured playlist
            ]);

            while ($featured_list->have_posts()) : $featured_list->the_post();
                $youtube_playlist_url = get_post_meta(get_the_ID(), '_playlist_url', true);
                $embed_url = mm_extract_youtube_embed($youtube_playlist_url);
                $thumb = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                $total_videos = get_post_meta(get_the_ID(), '_playlist_total_videos', true);
            ?>
                <div class="col-md-6 col-sm-6">
                    <div class="yt-playlist">
                        <a href="<?php echo esc_url($youtube_playlist_url); ?>" target="_blank">
                            <div class="thumb-wrapper">
                                <div class="t-wrapper1"></div>
                                <div class="t-wrapper2"></div>

                                <?php if ($thumb) : ?>
                                    <img src="<?php echo esc_url($thumb); ?>" width="100%" height="100%">
                                <?php else : ?>
                                    <iframe width="100%" height="100%" src="<?php echo esc_url($embed_url); ?>" frameborder="0" allowfullscreen></iframe>
                                <?php endif; ?>

                                <span class="video-count"><?php echo esc_html($total_videos); ?> videos</span>
                            </div>
                            <h5 class="mt-2"><?php the_title(); ?></h5>
                        </a>
                    </div>
                </div>
            <?php
            endwhile;
            wp_reset_postdata();
            ?>
        </div>
    </div>

    <!-- ALL VIDEOS SECTION -->
    <div class="mt-5">
        <h4 class="fs24 fw-bold">All Videos</h4>
        <div class="mt-4 row g-4">
            <?php
            $all_videos = new WP_Query([
                'post_type'      => 'video',
                'posts_per_page' => -1,
                'orderby'        => 'date',
                'order'          => 'DESC'
            ]);

            while ($all_videos->have_posts()) : $all_videos->the_post();
                $youtube_playlist_url = get_post_meta(get_the_ID(), '_playlist_url', true);
                $embed_url            = mm_extract_youtube_embed($youtube_playlist_url);
                $thumb                = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                $total_videos         = get_post_meta(get_the_ID(), '_playlist_total_videos', true);
            ?>
                <div class="col-md-6 col-sm-6">
                    <div class="yt-playlist">
                        <a href="<?php echo esc_url($youtube_playlist_url); ?>" target="_blank">
                            <div class="thumb-wrapper">
                                <div class="t-wrapper1"></div>
                                <div class="t-wrapper2"></div>

                                <?php if ($thumb) : ?>
                                    <img src="<?php echo esc_url($thumb); ?>" width="100%" height="100%">
                                <?php else : ?>
                                    <iframe width="100%" height="100%" src="<?php echo esc_url($embed_url); ?>" frameborder="0" allowfullscreen></iframe>
                                <?php endif; ?>

                                <?php if ($total_videos) : ?>
                                    <span class="video-count"><?php echo esc_html($total_videos); ?> videos</span>
                                <?php endif; ?>
                            </div>
                            <h5 class="mt-2"><?php the_title(); ?></h5>
                        </a>
                    </div>
                </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </div>


</div>

<?php get_footer_based_on_login(); ?>