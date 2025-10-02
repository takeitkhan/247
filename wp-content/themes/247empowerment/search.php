<?php
if (is_user_logged_in()) {
    get_header('portal');
} else {
    get_header('main');
}
?>
<h1>Search Results</h1>
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <h2><?php the_title(); ?></h2>
        <?php the_excerpt(); ?>
<?php endwhile;
endif; ?>

<?php
if (is_user_logged_in()) {
    get_footer('portal'); // loads footer-custom.php
} else {
    get_footer('main'); // loads footer-main.php
}
?>