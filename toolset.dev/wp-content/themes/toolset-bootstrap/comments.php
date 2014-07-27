<?php
/**
 * The template for displaying Comments.
 *
 */
if ( post_password_required() ) {
	return;
}
?>

<?php if ( wpbootstrap_get_setting('general_settings', 'display_comments' ) ): ?>
	<?php if (comments_open()) { ?><section id="comments"><?php }?>
		<?php if (comments_open()): ?>
			<?php if ( have_comments() ) : ?>
				<h2 id="comments-title">
				<?php
					$comment_count_actual = get_comments_number();
					if ($comment_count_actual = 1) {
						printf( __( 'One thought on', 'wpbootstrap' ).' &ldquo;%1$s&rdquo;', '<span>' . get_the_title() . '</span>' );
                    } elseif ($comment_count_actual > 1) {
						printf( '%1$s '.__( 'thoughts on', 'wpbootstrap' ).' &ldquo;%2$s&rdquo;',number_format_i18n( get_comments_number() ),'<span>' . get_the_title() . '</span>' );
					}
				?>
				</h2>

				<ol class="commentlist unstyled">
					<?php wp_list_comments(array('walker'=>new Wpbootstrap_Comments())); ?>
				</ol> <!-- .commentlist -->

				<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // are there comments to navigate through ?>
				<ul class="pager">
					<li class="previous"><?php previous_comments_link( '&larr; '.__( 'Older Comments', 'wpbootstrap' ) ); ?></li>
					<li class="next"><?php next_comments_link( __( 'Newer Comments', 'wpbootstrap' ).' &rarr;' ); ?></li>
				</ul>
				<?php endif; // check for comment navigation ?>

				<?php
				// If there are no comments and comments are closed
				if ( ! comments_open() && get_comments_number() ) : ?>
				<p class="nocomments"><?php _e( 'Comments are closed.' , 'wpbootstrap' ); ?></p>
				<?php endif; ?>

			<?php endif; // have_comments() ?>

			<?php comment_form(); ?>

		<?php else: ?>
			<?php if ( wpbootstrap_get_setting('general_settings', 'display_comments_closed_info' ) ): ?>
				<p class="alert alert-info">
					<button type="button" class="close" data-dismiss="alert">&times;</button>
					<?php _e('Comments are closed','wpbootstrap'); ?>
				</p>
			<?php endif; ?>
		<?php endif;?>

	<?php if (comments_open()) { ?></section><!-- #comments --><?php }?>
<?php endif;