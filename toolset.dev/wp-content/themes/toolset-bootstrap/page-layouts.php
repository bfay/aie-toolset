<?php
/*
Template Name: Layouts page template
*/
get_header('layouts'); ?>

    <?php if ( function_exists( 'the_ddlayout' ) ) : ?>

    	 <?php the_ddlayout(); ?>

    <?php else: ?>

        <h1>
        	<?php _e('This template requires the Drag and Drop Layout plugin.', 'wpbootstrap'); ?>
        </h1>

    <?php endif; ?>


<?php get_footer('layouts');