<?php
/*
Template Name: Full width template - without sidebar
*/
get_header(); ?>

	<?php
		while ( have_posts() ) : the_post();
			get_template_part( 'content', 'page' );
		endwhile;
	?>

<?php get_footer();