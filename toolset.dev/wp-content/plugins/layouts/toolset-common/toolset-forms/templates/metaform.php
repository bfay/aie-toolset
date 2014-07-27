<?php
/**
 *
 * $HeadURL: https://www.onthegosystems.com/misc_svn/common/trunk/toolset-forms/templates/metaform.php $
 * $LastChangedDate: 2014-07-24 04:33:05 +0000 (Thu, 24 Jul 2014) $
 * $LastChangedRevision: 25257 $
 * $LastChangedBy: bruce $
 *
 */

if ( is_admin() ) {
    $child_div_classes = array( 'js-wpt-field-items' );
    if ( $cfg['use_bootstrap'] && in_array( $cfg['type'], array( 'date', 'select' ) ) ) {
        $child_div_classes[] = 'form-inline';
    }
    ?><div class="js-wpt-field wpt-field js-wpt-<?php echo $cfg['type']; ?> wpt-<?php echo $cfg['type']; ?><?php if ( @$cfg['repetitive'] ) echo ' js-wpt-repetitive wpt-repetitive'; ?><?php do_action('wptoolset_field_class', $cfg); ?>" data-wpt-type="<?php echo $cfg['type']; ?>" data-wpt-id="<?php echo $cfg['id']; ?>">
        <div class="<?php echo implode( ' ', $child_div_classes ); ?>">
	<?php foreach ( $html as $out ):
		include 'metaform-item.php';
	endforeach; ?>
    <?php if ( @$cfg['repetitive'] ): ?>
        <a href="#" class="js-wpt-repadd wpt-repadd button-primary" data-wpt-type="<?php echo $cfg['type']; ?>" data-wpt-id="<?php echo $cfg['id']; ?>"><?php printf(__('Add new %s', 'wpv-views'), $cfg['title']); ?></a>
	<?php endif; ?>
		</div>
	</div>
<?php
} else {
	$types_without_wrapper = array( 'submit', 'hidden' );
	$needs_wrapper = ( isset( $cfg['type'] ) && in_array( $cfg['type'], $types_without_wrapper ) ) ? false : true;
	ob_start();
	do_action('wptoolset_field_class', $cfg);
	$conditional_classes = ob_get_clean();
	if (strpos($conditional_classes, 'wpt-hidden') === false) {
		$conditional_classes = '';
	} else {
		$conditional_classes = 'true';
	}
	if ( $needs_wrapper) {
		echo '<div class="js-wpt-field-items';if ( @$cfg['repetitive'] ) echo ' js-wpt-repetitive wpt-repetitive';echo '" data-initial-conditional="' . $conditional_classes . '">';
	}
    foreach ( $html as $out ) {
        include 'metaform-item.php';
    }
	if ( $cfg['repetitive'] ) {
		echo '<a href="#" class="js-wpt-repadd wpt-repadd button-primary" data-wpt-type="' . $cfg['type'] . '" data-wpt-id="' . $cfg['id'] . '">';
		printf(__('Add new %s', 'wpv-views'), $cfg['title']);
		echo '</a>';
	}
	if ( $needs_wrapper) {
		echo '</div>';
	}
}

