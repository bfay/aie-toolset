<div class="clearfix <?php echo wpbootstrap_get_nav_menu_classes(); ?>">
	<div class="navbar-inner">

		<div class="container">

			<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</a>

			<?php if ( of_get_option( 'navbar_title' ) ): ?>
				<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
					<?php bloginfo( 'name' ); ?>
				</a>
			<?php endif; ?>

			<div class="nav-collapse collapse">

				<nav id="nav-main" role="navigation">
					<?php
					if ( has_nav_menu( 'header-menu' ) ) :
						wp_nav_menu( array( 'theme_location' => 'header-menu', 'menu_class' => 'nav' ) );
					else:
						wp_nav_menu( array( 'menu_class' => 'nav', 'depth' => '1', 'walker' => null ) );
					endif;
					?>
				</nav> <!-- #nav-main -->

				<?php if ( of_get_option( 'navbar_search' ) ): ?>
				<form class="navbar-form pull-right" role="search" method="get" action="<?php echo home_url( '/' ); ?>">
					<input type="text" name="s" id="s" class="input-medium">
					<button type="submit" class="btn"><?php _e('Search','wpbootstrap'); ?></button>
				</form><!-- .navbar-form -->
				<?php endif; ?>

			</div>

		</div>

	</div>
</div><!-- .navbar -->