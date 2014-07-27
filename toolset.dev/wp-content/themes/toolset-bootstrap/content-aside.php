<?php
/**
 * The template for aside post format
 *
 */
?>

<?php if (!is_single()): ?>
	<?php if(
			( wpbootstrap_get_setting('titles_settings','display_categories_post_titles') && is_category() ) || // for cateogires
			( wpbootstrap_get_setting('titles_settings','display_tags_post_titles') && is_tag() ) || // for tags
			( wpbootstrap_get_setting('titles_settings','display_archives_post_titles') && is_archive() && ( !is_tag() && !is_category() ) ) || // for archives. There is an additional condition needed because is_archove() returns true not only for archives but for tags and categories as well
			( wpbootstrap_get_setting('titles_settings','display_home_post_titles') && is_home() ) ): // for homepage blog index
	?>
	<h2 class="entry-title">
		<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to', 'wpbootstrap' ).' %s', the_title_attribute( 'echo=0' ) ) ); ?>" rel="bookmark">
			<?php the_title(); ?>
		</a>
	</h2>
	<?php endif ?>
<?php endif; ?>

<div class="entry-content well">
	<?php the_content( '<span class="btn btn-small btn-primary pull-right">'.__( 'Read more', 'wpbootstrap' ).'&raquo;</span>' ); ?>
	<?php edit_post_link( __('Edit page','wpbootstrap'), '<p class="btn">', '</p>' ); ?>
</div><!-- .entry-content .well -->

<?php wpbootstrap_link_pages();