<?php
/**
 * The template used for displaying page content in page.php
 *
 */
?>

<article <?php post_class('clearfix') ?> id="post-<?php the_ID(); ?>">

	<?php if ( wpbootstrap_get_setting('titles_settings', 'display_pages_titles' ) ): ?>
		<?php if (is_front_page()): ?>
		    <?php if ( wpbootstrap_get_setting('titles_settings', 'display_pages_titles_on_homepage' ) ): ?>
				<h1><?php the_title(); ?></h1>
			<?php endif; ?>
		<?php else: ?>
			<h1><?php the_title(); ?></h1>
		<?php endif; ?>
	<?php endif; ?>

	<div class="entry-content">
		<?php the_content(); ?>
		<?php edit_post_link( __('Edit page','wpbootstrap'), '<p class="btn">', '</p>' ); ?>
	</div><!-- .entry-content -->

</article>

<?php wpbootstrap_link_pages(); ?>
<?php comments_template();