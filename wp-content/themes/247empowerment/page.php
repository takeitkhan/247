<?php get_header_based_on_login(); ?>

<main>
    <?php if (has_post_thumbnail()) : ?>
        <div class="position-relative page-header">
            <?php the_post_thumbnail('full', ['class' => 'w-100']); ?>
            <div class="page-header-overlay">
                <h1 class="page-title"><?php the_title(); ?></h1>
            </div>
        </div>
    <?php else : ?>
        <div class="container">
            <h1 class="pt-4 text-center page-title"><?php the_title(); ?></h1>
        </div>
    <?php endif; ?>

    <div class="container">
        <div class="xcustom-box-shadow mb-3 p-3 xcustom-border-radius xbg-white">
            <div class="wp-block-image wp-block-paragraph xwp-block-quote p-4">
                <?php the_content(); ?>
            </div>
        </div>
    </div>
</main>
<?php get_footer_based_on_login(); ?>