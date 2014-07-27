<?php
/**
 * The default template for displaying content.
 *
 */
?>

<article <?php post_class('clearfix') ?> id="post-<?php the_ID(); ?>">
	
		<?php if (is_single()): ?>
		    <?php 
		    if ( 'post' != get_post_type() ) {
            ?>  
			<?php if (wpbootstrap_get_setting('titles_settings','display_single_post_titles_cpt')): ?>
			    <header>
				<h1><?php the_title(); ?></h1>
			<?php endif; ?>
            <?php 
            } else {
		    ?>
			<?php if (wpbootstrap_get_setting('titles_settings','display_single_post_titles')): ?>
			    <header>
				<h1><?php the_title(); ?></h1>
			<?php endif; ?>
			<?php } ?>
		<?php else: ?>
			<?php if (
					( wpbootstrap_get_setting('titles_settings','display_categories_post_titles') && is_category() ) || // for cateogires
					( wpbootstrap_get_setting('titles_settings','display_tags_post_titles') && is_tag() ) || // for tags
					( wpbootstrap_get_setting('titles_settings','display_archives_post_titles') && is_archive() && ( !is_tag() && !is_category() ) ) || // for archives. There is an additional condition needed because is_archove() returns true not only for archives but for tags and categories as well
					( wpbootstrap_get_setting('titles_settings','display_home_post_titles') && is_home() ) || // for homepage blog index
					( wpbootstrap_get_setting('titles_settings','display_search_post_titles') && is_search() ) // for homepage blog index
				): ?>
				<header>
				<?php $archive_looped_page=TRUE;?>
				<h2 class="entry-title">
					<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to', 'wpbootstrap' ).' %s', the_title_attribute( 'echo=0' ) ) ); ?>" rel="bookmark">
						<?php the_title(); ?>
					</a>
				</h2>
			<?php endif; ?>
		<?php endif; ?>
		<?php 
        	if ((is_single()) && (wpbootstrap_get_setting('general_settings','display_postmeta')) && !(wpbootstrap_get_setting('titles_settings','display_single_post_titles'))) {   
		?>
			<header>
		<?php } elseif (( 'post' != get_post_type() ) && (wpbootstrap_get_setting('general_settings','display_postmeta_cpt')) && !(wpbootstrap_get_setting('titles_settings','display_single_post_titles_cpt'))) {?>
			<header>		
		<?php }?>
		<?php get_template_part('entry-meta'); ?>
		<?php 
        	if ((is_single()) && (wpbootstrap_get_setting('general_settings','display_postmeta'))) {   
		?>		
		</header>
		<?php } elseif (( 'post' != get_post_type() ) && (wpbootstrap_get_setting('general_settings','display_postmeta_cpt'))) {?>
		</header>
		<?php } elseif (isset($archive_looped_page)) {
		    if ($archive_looped_page) {
        ?>
        </header>
        <?php }}?>
		
	<div class="entry-content clearfix">

		<?php if (is_search()): ?>
			<?php the_excerpt(); ?>
		<?php else: ?>

			<?php if ( has_post_thumbnail() && wpbootstrap_get_setting('general_settings','display_thumbnails') ): ?>
				<a href="<?php the_permalink(); ?>" class="post-thumbnail thumbnail pull-left">
					<?php the_post_thumbnail('thumbnail'); ?>
				</a>
			<?php endif; ?>

			<?php the_content( '<span class="btn btn-small btn-primary pull-right">'.__( 'Read more', 'wpbootstrap' ).' &raquo;</span>' ); ?>

			<?php
				if (is_single()):
					edit_post_link( __('Edit post','wpbootstrap'), '<p class="btn">', '</p>' );
				endif;
			?>

			<?php wpbootstrap_link_pages(); ?>

			<?php if ( is_sticky() && is_home() ): ?>
				<a class="btn btn-primary btn-large" href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to', 'wpbootstrap' ).' %s', the_title_attribute( 'echo=0' ) ) ); ?>">
					<?php _e( 'Read more', 'wpbootstrap' ) ?>
				</a>
			<?php endif; ?>
		<?php endif; ?>
	</div><!-- .entry-content -->

</article>

<?php comments_template();