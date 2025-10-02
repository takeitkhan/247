<?php
if (is_user_logged_in()) {
    get_header('portal');
} else {
    get_header('main');
}
?>

<?php while (have_posts()) : the_post(); ?>
    <h1><?php the_title(); ?></h1>
    <?php the_content(); ?>
<?php endwhile; ?>

<?php
if (is_user_logged_in()) {
    get_footer('portal'); // loads footer-custom.php
} else {
    get_footer('main'); // loads footer-main.php
}
?>