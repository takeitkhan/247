<?php
/* Template Name: Welcome Page */
get_header();
?>
<div class="position-relative" style="xheight: 30vh; xoverflow: hidden;">
    <?php $hero_img = get_theme_mod('hero_image');
    if ($hero_img): ?>
        <img src="<?php echo esc_url($hero_img); ?>" alt="Chess Empowerment" class="w-100 h-100 object-fit-cover" />
    <?php endif; ?>
</div>

<div class="container-fluid">
    <div class="px-2 py-4 row">
        <?php while (have_posts()): the_post(); ?>

            <?php
            // Check if the 'hide_title' custom field is checked
            $hide_title = get_post_meta(get_the_ID(), 'hide_title', true);
            ?>
            <div class="col-12">
                <?php the_content(); ?>
            </div>


        <?php endwhile; ?>
    </div>
</div>

<?php get_footer(); ?>