<!DOCTYPE html>
<!--[if lt IE 7 ]><html <?php language_attributes(); ?> class="no-js ie ie6 ie-lte7 ie-lte8 ie-lte9"><![endif]-->
<!--[if IE 7 ]><html <?php language_attributes(); ?> class="no-js ie ie7 ie-lte7 ie-lte8 ie-lte9"><![endif]-->
<!--[if IE 8 ]><html <?php language_attributes(); ?> class="no-js ie ie8 ie-lte8 ie-lte9"><![endif]-->
<!--[if IE 9 ]><html <?php language_attributes(); ?> class="no-js ie ie9 ie-lte9"><![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--><html <?php language_attributes(); ?> class="no-js"><!--<![endif]-->
<head>

	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title><?php echo bloginfo("name") ?> <?php echo wp_title( '&ndash;', false, 'left' ); ?></title>
	<link rel="profile" href="http://gmpg.org/xfn/11" />
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
	<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
	<?php if ( of_get_option( 'favicon' ) ): ?>
		<link rel="shortcut icon" href="<?php echo of_get_option( 'favicon' ); ?>">
	<?php else: ?>
		<link rel="shortcut icon" href="<?php echo get_template_directory_uri() ?>/favicon.ico">
	<?php endif ?>
	<!--[if lt IE 9]>
		<?php // Loads HTML5 JavaScript file to add support for HTML5 elements in older IEs: http://code.google.com/p/html5shiv/ ?>
		<script src="<?php echo get_template_directory_uri() ?>/js/html5shiv.js" type="text/javascript"></script>
		<?php // Loads selectivizr script to add support for some CSS3 selectors in older IEs. More info: http://selectivizr.com/ ?>
		<script src="<?php echo get_template_directory_uri() ?>/js/selectivizr.min.js" type="text/javascript"></script>
		<?php // Loads respons.js script to add baisc support for @media-queries for older IEs. More info: https://github.com/scottjehl/Respond ?>
		<script src="<?php echo get_template_directory_uri() ?>/js/respond.min.js" type="text/javascript"></script>
	<![endif]-->
	<?php
		do_action( 'wpbootstrap_before_wp_head' );
		wp_head();
		do_action( 'wpbootstrap_after_wp_head' );
	?>
</head>

<body <?php body_class(); ?>>

	<?php do_action( 'wpbootstrap_before_container' ); ?>
	<div class="container">

		<?php
			if( wpbootstrap_get_setting('general_settings','display_header_widgets') ) {
				get_sidebar('header-widgets');
			}
		?>

		<?php do_action( 'wpbootstrap_before_header' ); ?>

		<?php if ( wpbootstrap_get_setting( 'general_settings', 'display_header_site_title' ) || wpbootstrap_get_setting('general_settings','display_header_nav') ): ?>
		<header id="header" class="row" role="banner">

			<?php if ( wpbootstrap_get_setting( 'general_settings', 'display_header_site_title' ) ): ?>
			<hgroup class="span12">
				<h1 class="site-title">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
				</h1>
				<h2 class="site-description"><?php bloginfo( 'description' ); ?></h2>
			</hgroup>
			<?php endif; ?>

			<?php
				if ( wpbootstrap_get_setting('general_settings','display_header_nav') ):
					get_template_part('_navbar');
				endif
			?>

		</header><!-- #header -->
		<?php endif; ?>

		<?php do_action( 'wpbootstrap_after_header' ); ?>

		<div class="row" id="main">
			<?php do_action( 'wpbootstrap_before_content' ); ?>
			<section class="<?php echo wpbootstrap_get_content_width(); ?>" id="content" role="main">