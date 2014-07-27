<?php
/**
 * The template for entry meta section
 *
 */
?>
<?php 
if ((is_single()) && ( 'post' != get_post_type() )) {
?>             

<?php if(wpbootstrap_get_setting('general_settings','display_postmeta_cpt')): ?>
	<div class="entry-meta">
		<p>
			<time datetime="<?php echo get_the_time( 'c' ); ?>">
				<?php echo sprintf( __( 'Posted on','wpbootstrap').' %s '.__('at','wpbootstrap').' %s.', get_the_date(), get_the_time() ); ?>
			</time>
			<?php echo __( 'By', 'wpbootstrap' ); ?> <a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>" rel="author">
				<?php echo get_the_author(); ?>
			</a>
		</p>
		<?php if (has_category() || has_tag() ):?>
		<p>
			<?php if (has_category()): ?>
			<?php _e( 'Categories:', 'wpbootstrap' ); echo ' ';echo get_the_category_list( ', ' ); ?>.
			<?php endif; ?>
			<?php if (has_tag()): ?>
			<?php _e( 'Tags:', 'wpbootstrap' ); echo ' ';echo get_the_tag_list('',', ',''); ?>.
			<?php endif; ?>
		</p>
		<?php endif; ?>
	</div><!-- .entry-meta -->
<?php endif; ?>

<?php 
} else {
?>
<?php if(wpbootstrap_get_setting('general_settings','display_postmeta')): ?>
	<div class="entry-meta">
		<p>
			<time datetime="<?php echo get_the_time( 'c' ); ?>">
				<?php echo sprintf( __( 'Posted on','wpbootstrap').' %s '.__('at','wpbootstrap').' %s.', get_the_date(), get_the_time() ); ?>
			</time>
			<?php echo __( 'By', 'wpbootstrap' ); ?> <a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>" rel="author">
				<?php echo get_the_author(); ?>
			</a>
		</p>
		<?php if (has_category() || has_tag() ):?>
		<p>
			<?php if (has_category()): ?>
			<?php _e( 'Categories:', 'wpbootstrap' ); echo ' ';echo get_the_category_list( ', ' ); ?>.
			<?php endif; ?>
			<?php if (has_tag()): ?>
			<?php _e( 'Tags:', 'wpbootstrap' ); echo ' ';echo get_the_tag_list('',', ',''); ?>.
			<?php endif; ?>
		</p>
		<?php endif; ?>
	</div><!-- .entry-meta -->
<?php endif; ?>
<?php }