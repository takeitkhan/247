<?php
if (is_user_logged_in()) {
    get_header('portal');
} else {
    get_header('main');
}
?>

<div class="p-4">
    <?php while (have_posts()):
        the_post(); ?>
        <?php
        $hide_title = get_post_meta(get_the_ID(), 'hide_title', true);
        if (!$hide_title) :
        ?>
            <h1 class="mb-4 px-3 text-muted fw-bold fs-4">
                <?php the_title(); ?>
            </h1>
        <?php endif; ?>
        <?php the_content(); ?>
    <?php endwhile; ?>
</div>


<?php
if (is_user_logged_in()) {
    get_footer('portal'); // loads footer-custom.php
} else {
    get_footer('main'); // loads footer-main.php
}
?>