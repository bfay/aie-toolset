<?php
/*
 * Slider cell type.
 * Displays set of images using Bootstrap's carousel component
 *
 */

if( !function_exists('register_slider_cell_init') )
{
	function register_slider_cell_init() {
		if ( function_exists('register_dd_layout_cell_type') ) {
			register_dd_layout_cell_type ('slider-cell',
				array (
					'name'						=>	__('Slider', 'ddl-layouts'),
					'icon-css'					=>	'icon-exchange',
					'description'				=>	__('Displays set of images using Bootstrap\'s carousel component', 'ddl-layouts'),
					'category'					=>	__('Text and Media', 'ddl-layouts'),
					'category-icon-css'			=>	'icon-sun',
					'button-text'				=>	__('Assign slider cell', 'ddl-layouts'),
					'dialog-title-create'		=>	__('Create a new slider cell', 'ddl-layouts'),
					'dialog-title-edit'			=>	__('Edit slider cell', 'ddl-layouts'),
					'dialog-template-callback'	=>	'slider_cell_dialog_template_callback',
					'cell-content-callback'		=>	'slider_cell_content_callback',
					'cell-template-callback'	=>	'slider_cell_template_callback',
					'cell-class'				=>	'',
					'preview-image-url'			=>  WPDDL_RES_RELPATH . '/images/layouts-slider-cell.jpg'
				)
			);
		}
	}
	add_action( 'init', 'register_slider_cell_init' );


	function slider_cell_dialog_template_callback() {
		ob_start();
		?>

		<h3>
			<?php the_ddl_cell_info('name'); ?>
		</h3>
		<div class="ddl-form">
			<p>
				<label for="<?php the_ddl_name_attr('slider_height'); ?>"><?php _e( 'Slider height', 'ddl-layouts' ) ?>:</label>
				<input type="number" name="<?php the_ddl_name_attr('slider_height'); ?>" value="300">
				<span class="desc"><?php _e( 'px, number only', 'ddl-layouts' ) ?></span>
			</p>
			<p>
				<label for="<?php the_ddl_name_attr('interval'); ?>"><?php _e( 'Interval', 'ddl-layouts' ) ?>:</label>
				<input type="number" name="<?php the_ddl_name_attr('interval'); ?>" value="5000">
				<span class="desc"><?php _e( 'The amount of time to delay between automatically cycling an item, ms.', 'ddl-layouts' ) ?></span>
			</p>
			<fieldset>
				<legend><?php _e( 'Options', 'ddl-layouts' ) ?></legend>
				<div class="fields-group">
					<label class="checkbox" for="<?php the_ddl_name_attr('autoplay'); ?>">
						<input type="checkbox" name="<?php the_ddl_name_attr('autoplay'); ?>" value="true">
						<?php _e( 'Autoplay', 'ddl-layouts' ) ?>
					</label>
					<label class="checkbox" for="<?php the_ddl_name_attr('pause'); ?>">
						<input type="checkbox" name="<?php the_ddl_name_attr('pause'); ?>" value="pause">
						<?php _e( 'Pause on hover', 'ddl-layouts' ) ?>
					</label>
				</div>
			</fieldset>

			<h3><?php _e('Slides', 'ddl-layouts'); ?></h3>

			<?php ddl_repeat_start( 'slider', __( 'Add another slide', 'ddl-layouts' ), 10 ); ?>

				<div class="js-ddl-media-field">
					<label for="<?php the_ddl_name_attr('slide_url'); ?>"><?php _e( 'Image', 'ddl-layouts' ) ?>:</label>
					<input type="text" class="js-ddl-media-url" name="<?php the_ddl_name_attr('slide_url'); ?>" />
					<p class="ddl-form-button-wrap">
						<button class="button js-ddl-add-media"
								data-uploader-title="<?php _e( 'Choose an image', 'ddl-layouts' ) ?>"
								data-uploader-button-text="Insert image URL"><?php _e( 'Choose an image', 'ddl-layouts' ) ?>
						</button>
					</p>
				</div>
				<p>
					<label for="<?php the_ddl_name_attr('slide_title'); ?>"><?php _e( 'Slide title', 'ddl-layouts' ) ?>:</label>
					<input type="text" name="<?php the_ddl_name_attr('slide_title'); ?>">
				</p>
				<p>
					<label for="<?php the_ddl_name_attr('slide_text'); ?>"><?php _e( 'Slide description', 'ddl-layouts' ) ?>:</label>
					<textarea name="<?php the_ddl_name_attr('slide_text'); ?>" rows="3"></textarea>
				</p>

			<?php ddl_repeat_end(); ?>

		</div>

		<?php
		return ob_get_clean();
	}

	// Callback function for displaying the cell in the editor.
	function slider_cell_template_callback() {
		return '';
	}

	// Callback function for display the cell in the front end.
	function slider_cell_content_callback() {

		$unique_id = uniqid();
		$pause = '';

		if ( get_ddl_field('pause') ) {
			$pause = 'data-pause="hover"';
		} else {
			$pause = 'data-pause="false"';
		}

		ob_start();
		?>

		<?php if ( get_ddl_field('autoplay') ) :?>

			<script>
				jQuery(document).ready( function($) {
					var ddl_slider_id_string = "#slider-<?php echo $unique_id ?>";
					$(ddl_slider_id_string).carousel({
						interval : <?php the_ddl_field('interval') ?>
						<?php if (!get_ddl_field('pause')) {echo ', pause: "false"';} ?>
					});
				});
			</script>

		<?php endif ?>

		<style>
			#slider-<?php echo $unique_id ?> .carousel-inner > .item {
				height: <?php the_ddl_field('slider_height') ?>px;
			}
		</style>


		<div id="slider-<?php echo $unique_id ?>" class="carousel slide ddl-slider" <?php echo $pause ?> data-interval="<?php the_ddl_field('interval') ?>">

			<ol class="carousel-indicators">

				<?php while ( has_ddl_repeater('slider') ) : the_ddl_repeater('slider'); ?>

					<li data-target="#slider-<?php echo $unique_id ?>" data-slide-to="<?php the_ddl_repeater_index(); ?>" <?php if (get_ddl_repeater_index() == 0) { echo ' class="active"'; } ?>></li>

				<?php endwhile;
					ddl_rewind_repeater('slider');
				?>

			</ol>

			<div class="carousel-inner">
				<?php while ( has_ddl_repeater('slider') ) : the_ddl_repeater('slider'); ?>

					<div class="item <?php if (get_ddl_repeater_index() == 0) { echo ' active'; } ?>">
						<img src="<?php the_ddl_sub_field('slide_url'); ?>">
						<div class="carousel-caption">
							<h4>
								<?php the_ddl_sub_field('slide_title'); ?>
							</h4>
							<p>
								<?php the_ddl_sub_field('slide_text'); ?>
							</p>
						</div>
					</div>

				<?php endwhile; ?>
			</div>

			<a class="left carousel-control" href="#slider-<?php echo $unique_id ?>" data-slide="prev">‹</a>
			<a class="right carousel-control" href="#slider-<?php echo $unique_id ?>" data-slide="next">›</a>

		</div>

		<?php
		return ob_get_clean();
	}
}