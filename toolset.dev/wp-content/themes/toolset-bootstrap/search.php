<?php
/**
 * The template for displaying Search results.
 *
 */
get_header(); ?>

	<?php if ( wpbootstrap_get_setting('titles_settings','display_search_header') ): ?>
	<h1>
		<?php printf( __( 'Search Results for:', 'wpbootstrap' ).' %s', '<span>' . get_search_query() . '</span>' ); ?>
	</h1>
	<?php endif; ?>

	<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
		<?php get_template_part('content'); ?>
	<?php endwhile; ?>

	<?php wpbootstrap_content_nav(); ?>

	<?php else : ?>

		<article id="post-0" class="post no-results not-found">

			<div class="entry-content">
				<p class="alert alert-error">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					<?php _e( 'No results were found for: "<strong>'.get_search_query().'"</strong>', 'wpbootstrap' ); ?>
				</p>
				<?php get_search_form(); ?>
			</div><!-- .entry-content -->

		</article><!-- .post .no-results -->

	<?php endif; ?>

<?php get_footer();