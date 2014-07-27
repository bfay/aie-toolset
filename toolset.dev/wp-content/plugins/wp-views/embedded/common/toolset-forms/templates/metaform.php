<?php
/**
 *
 * $HeadURL: https://www.onthegosystems.com/misc_svn/common/tags/Types1.6b4-CRED1.3b4-Views1.6.2b2/toolset-forms/templates/metaform.php $
 * $LastChangedDate: 2014-07-17 08:26:15 +0000 (Thu, 17 Jul 2014) $
 * $LastChangedRevision: 25039 $
 * $LastChangedBy: juan $
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
	if ( $needs_wrapper) {
		echo '<div class="js-wpt-field-items';if ( @$cfg['repetitive'] ) echo ' js-wpt-repetitive wpt-repetitive';echo '">';
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

