<div class="js-ddl-message-container dd-message-container"></div>

<div class="dd-layouts-wrap">

	<div class="dd-layouts-header">
		<div id="icon-edit" class="icon32 icon32-posts-dd_layouts"><br></div>
        <h2>
        <?php  _e('Edit Layout',  'ddl-layouts'); ?>
        </h2>
			<!--<span class="js-layout-title dd-layout-title"></span>-->
        <div id="titlediv">
            <div id="titlewrap">
                 <input name="layout-title-input" id="title" class="js-layout-title dd-layout-title layout-title-input" value="<?php echo get_the_title($post->ID); ?>"/>
            </div>
        </div>

        <div id="edit-slug-box" class="hide-if-no-js">
			<label for="layout-slug"><strong><?php _e('Layout slug:','ddl-layouts'); ?></strong></label>
            <span id="layout-slug" name="layout-slug" type="text" class="edit-layout-slug js-edit-layout-slug"><?php echo urldecode( $post->post_name ); ?></span>
            <span id="edit-slug-buttons"><a href="#post_name" class="edit-slug button button-small hide-if-no-js js-edit-slug"><?php _e( 'Edit', 'ddl-layouts' ); ?></a></span>
            <span id="edit-slug-buttons-active" class="js-edit-slug-buttons-active"><a href="#" class="save button button-small js-edit-slug-save">OK</a> <a class="cancel js-cancel-edit-slug" href="#">Cancel</a></span>
            <span id="view-post-btn"><a href="#post_name" class="button button-small hide-if-no-js js-view-layout"><?php _e( 'View Layout', 'ddl-layouts' ); ?></a></span>
         <!--   <i class="icon-gear edit-layout-settings js-edit-layout-settings" title="<?php _e( 'Edit layout settings', 'ddl-layouts' ); ?>"></i> -->
            <span id="edit-layout-button"><a href="#post_name" class="button button-small hide-if-no-js js-edit-layout-settings"><?php _e( 'Edit layout settings', 'ddl-layouts' ); ?></a></span>
        </div>
	</div>
</div>

<script type="text/html" id="ddl-layout-not-assigned-to-any">

    <div class="ddl-dialog-header">
        <h2><?php printf(__('%s', 'ddl-layouts'), '{{{ layout_name }}}');?></h2>
        <i class="icon-remove js-edit-dialog-close"></i>
    </div>
    <div class="ddl-dialog-content">
    <?php printf(__('%s', 'ddl-layouts'), '{{{ message }}}'); ?>
</div>
    <div class="ddl-dialog-footer">
        <button class="button js-edit-dialog-close"><?php _e('Close', 'ddl-layouts'); ?></button>
    </div>

</script>

<script type="text/html" id="ddl-layout-assigned-to-many">
    <div class="ddl-dialog-header">
        <h2><?php _e('Select which page or post to use to view this Layout', 'ddl-layouts');?></h2>
        <i class="icon-remove js-edit-dialog-close"></i>
    </div>
    <div class="ddl-dialog-content ddl-layout-assigned-to-many">
        <ul>
            <#
                var type = '', count = 0;
                _.each(links, function(v){

                #>

                <#
                    var padding_top = count > 0 ? 'padding-top' : '';
                    if( type !== v.type ){

                    type = v.type;

                    #>
        <?php  printf(__('%s', 'ddl-layouts'), '<li class="post-type {{ padding_top }}">{{{ v.types }}}:</li>'); ?>

                    <#

                        }

                        if( v.href != ''){

                        #>
                        <li><a href="{{ v.href }}" title="{{{ v.title }}}" target="_blank">
                            {{{ v.title }}}
                        </a></li>

                        <#
                         count++;
                        }
                     }); #>
        </ul>
    </div>
    <div class="ddl-dialog-footer">

        <button class="button js-edit-dialog-close"><?php _e('Close', 'ddl-layouts'); ?></button>
    </div>

</script>

<div class="ddl-dialogs-container">
    <div class="ddl-dialog auto-width" id="js-view-layout-dialog-container"></div>
</div>