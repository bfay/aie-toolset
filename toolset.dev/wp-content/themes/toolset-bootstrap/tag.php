<?php
/**
 * The template for displaying tag archive page
 *
 */
get_header(); ?>

<?php if ( have_posts() ) : ?>

	<?php if (wpbootstrap_get_setting('titles_settings','display_tags_header')): ?>
		<h1 class="tag-title">
			<?php printf( __( 'Tag:', 'wpbootstrap' ).' %s', single_tag_title( '', false ) ); ?>
		</h1>
	<?php endif; ?>

	<?php
		while ( have_posts() ) : the_post();
			get_template_part( 'content', get_post_format() );
		endwhile;
		wpbootstrap_content_nav();
	?>

<?php endif; ?>

<?php get_footer();