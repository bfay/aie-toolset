<?php
/**
 * The template for the right sidebar
 *
 */?>
<?php if (is_active_sidebar( 'header-widgets' )) : ?>
	<section id="header-widgets">
		<div class="row">
			<?php dynamic_sidebar( 'header-widgets' ); ?>
		</div>
	</section><!-- #header-widgets -->
<?php endif;