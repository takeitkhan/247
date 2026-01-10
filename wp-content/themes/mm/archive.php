<?php get_header(); ?>
<h1>Archive</h1>
<?php if(have_posts()) : while(have_posts()) : the_post(); ?>
<h2><?php the_title(); ?></h2>
<?php the_excerpt(); ?>
<?php endwhile; endif; ?>
<?php get_footer(); ?>