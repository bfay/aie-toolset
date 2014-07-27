<?php
/**
 *
 * $HeadURL: https://www.onthegosystems.com/misc_svn/common/tags/Types1.6b4-CRED1.3b4-Views1.6.2b2/toolset-forms/templates/metaform-item.php $
 * $LastChangedDate: 2014-07-12 08:38:18 +0000 (Sat, 12 Jul 2014) $
 * $LastChangedRevision: 24908 $
 * $LastChangedBy: gen $
 *
 */
if ( is_admin() ) {
?>
<div class="js-wpt-field-item wpt-field-item">
    <?php echo $out; ?>
    <?php if ( @$cfg['repetitive'] ): ?>
        <div class="wpt-repctl">
            <div class="js-wpt-repdrag wpt-repdrag">&nbsp;</div>
            <a class="js-wpt-repdelete button-secondary" data-wpt-type="<?php echo $cfg['type']; ?>" data-wpt-id="<?php echo $cfg['id']; ?>"><?php printf(__('Delete %s', 'wpv-views'), strtolower( $cfg['title'])); ?></a>
        </div>
    <?php endif; ?>
</div>
<?php
} else {
    if ( $cfg['repetitive'] ) {
        echo '<div class="wpt-repctl">';
    }
    echo $out;
    if ( $cfg['repetitive'] ) {
        echo '<div class="js-wpt-repdrag wpt-repdrag">&nbsp;</div>';
        echo '<a class="js-wpt-repdelete button-secondary">';
        printf(__('Delete %s', 'wpv-views'), strtolower( $cfg['title']));
        echo '</a>';
        echo '</div>';
    }
}
