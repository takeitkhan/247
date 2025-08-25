<?php get_header(); ?>

<main>
    <div class="xmain-container" style="xpadding-top: 80px;">
        <div class="xcustom-box-shadow mb-3 p-3 xcustom-border-radius xbg-white">
            <div class="p-4">
                <div class="xpx-2 xpy-4 xrow">
                    <?php while (have_posts()) : the_post(); ?>

                        <?php
                        // Check if the 'hide_title' custom field is set (truthy)
                        $hide_title = get_post_meta(get_the_ID(), 'hide_title', true);
                        ?>

                        <div class="col-12">
                            <?php if (!$hide_title) : ?>
                                <h1 class="mb-4"><?php the_title(); ?></h1>
                            <?php endif; ?>

                            <?php the_content(); ?>
                        </div>

                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>