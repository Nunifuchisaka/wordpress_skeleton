<?php get_header(); ?>

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
<article class="article">
	<h1 class="h1">
		<?php the_title(); ?>
	</h1>
	<div class="wp_content_1">
		<?php the_content(); ?>
	</div>
</article>
<?php endwhile; endif; ?>

<?php get_footer();