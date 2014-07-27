<?php
/**
 * The template for displaying Search form.
 *
 */
?>
<form id="searchform" class="form-search" role="search" method="get" action="<?php echo home_url( '/' ); ?>">
	<label class="checkbox" for="s"><?php _e('Search for:','wpbootstrap') ?></label>
	<input type="text" name="s" id="s" class="input-medium">
	<button type="submit" class="btn"><?php _e('Search','wpbootstrap') ?></button>
</form><!-- #searchform -->