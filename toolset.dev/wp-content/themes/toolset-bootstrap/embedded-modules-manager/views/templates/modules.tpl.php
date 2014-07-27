<?php
// deny access
if( !defined('ABSPATH') ) die('Security check');
if(!current_user_can(MODMAN_CAPABILITY)) die('Access Denied');

// auxilliary rendering functions
function modman_list_module_elements( $module, $module_data, &$cnt, &$items )
{
    $modid=preg_replace('/\s+/', '_', $module);
    $output='';
    ob_start();?>
	<div id='<?php echo $modid; ?>' class='modules-sortables'><?php
    if (!empty($module_data))
    {  	

        foreach ($module_data as $plugin=>$elements)
        {
            // bypass internal data
            if ('__module_info__'==$plugin) continue;

            $style='';
            $icon = '';
            /*  Instead of adding inline styles we should add an additional class to element, 
                and define different icons for different Classes in CSS file. 
                We should avoid inline styles unless they are really needed. 
                In this case they are not. */
            if ( isset($items[$plugin]['info']['icon']) )
                $style="style='padding-left:23px;background:url({$items[$plugin]['info']['icon']}) no-repeat 5px 50%'";
            if (isset($items[$plugin]['info']['icon_css']))
                $icon = $items[$plugin]['info']['icon_css'];

            if (!empty($elements))
            {
                foreach ($elements as $ii=>$element)
                {
                    $item_available=false;
                    if (!empty($items[$plugin]) && !empty($items[$plugin]['items']))
                    {
                        foreach ($items[$plugin]['items'] as $_item_)
                        {
                            // current module element is available in currently registered items
                            // Fix Notice: Undefined index: id 
                            // Make sure they are set!                           
                            
                            if ((isset($element['id'])) && (isset($_item_['id']))) {
                        	    if ($_item_['id']==$element['id'])
                            	{
                            	    $item_available=true;
                            	    
                             	   // display current element details, bypass frozen element details
                             	   if (isset($_item_['title']))
                            	    {
                             	       $elements[$ii]['title'] = $_item_['title'];
                             	       $element['title'] = $_item_['title'];
                              	  }
                              	  if (isset($_item_['details']))
                             	   {
                             	       $elements[$ii]['details'] = $_item_['details'];
                              	      $element['details'] = $_item_['details'];
                              	  }
                              	  
                              	  break;
                            	}
                            }
                        	
                        }
                    }
                    
                    if ((isset($element['title'])) && (!(empty($element['title'])))) {
                    	
                    	$item_available=true;
                    	
                    }
                    
                    $class='module';
                    if (!$item_available)
                        $class.=' item-not-available';
                    
                    //Fix notices on undefine id
                    
                    if (isset($element['id'])) {
                       $id=$element['id'].'_'.++$cnt;
                    }
                    
                    ?>
                    <?php if ((isset($id)) && ($class!='module item-not-available')) {?>
                    <div id='<?php echo $id; ?>' class='<?php echo $class; ?>'>
                       <?php 
                          if ((isset($element['title'])) && (!(empty($element['title'])))) {
                       ?>
                        <div class="module-top">
                            <div class="module-title-action"></div>
                            <div class="module-title">

                                <h4 title="<?php echo esc_attr($element['title']); ?>">
                                    <i class="<?php echo $icon; ?>"></i>
                                    <span><?php echo $element['title'] ?></span>
                                    <span class="in-module-title"></span>
									<?php if(!$item_available): ?>
									<i class="icon-question-sign"></i>
									<?php endif; ?>
                                </h4>                            
                            </div>
                            <a href='javascript:;' title="<?php echo esc_attr(__('Click for details','module-manager')); ?>" class="sidebar-name-arrow"></a>
                        </div>
                        <?php } ?>
                        <div class="module-inside">
                            <div style='display:none;height:0' class='module-data'>
                                <span style='display:none' class='module-plugin'><?php echo $plugin; ?></span>
                                <span style='display:none' class='module-item'><?php if (isset($element['id'])) { echo $element['id']; } ?></span>
                            </div>
                        </div>
                        <div class="module-description"><?php
                            if (isset($element['details']))
                            {
                                echo stripslashes($element['details']);
                            }
                        ?></div>
                    </div>
                    <?php
                    }
                }
            }
        }
    }
    ?></div>
    <?php
    $output.=ob_get_clean();
    echo $output;
}

function modman_list_items( $items, $plugin, $icon, $icon_css = null )
{
    $output='';
    ob_start();
    foreach ($items as $item)
    {
        $id=$item['id'].'_'.'__cnt__';
        $style='';
        $icon_style = '';
        if (isset($icon))
            $style="style='padding-left:23px;'";
        if( isset($icon_css) && null !== $icon_css )
            $icon_style = $icon_css;
        ?>
        <div id='<?php echo $id; ?>' class='module'>
            <div class="module-top">
                <div class="module-title-action"></div>
                <div class="module-title" <?php //echo $style; ?>>

                <h4 title="<?php echo esc_attr($item['title']); ?>"><i class="<?php echo $icon_style; ?>"></i><span><?php echo $item['title'] ?></span><span class="in-module-title"></span></h4>
                </div>
                <a href='javascript:;' title="<?php echo esc_attr(__('Click for details','module-manager')); ?>" class="sidebar-name-arrow"></a>
            </div>
            <div class="module-inside">
                <div style='display:none;height:0' class='module-data'>
                    <span style='display:none' class='module-plugin'><?php echo $plugin; ?></span>
                    <span style='display:none' class='module-item'><?php echo $item['id'] ?></span>
                </div>
            </div>
            <div class="module-description"><?php
            if (isset($item['details']))
            {
                echo stripslashes($item['details']);
            }
            ?></div>
        </div><?php
    }
    $output.=ob_get_clean();
    echo $output;
}
?>

<!-- templates -->
<script id='module-template' type='text/module-template'>
    <div class="modules-holder-wrap">
        <div class="sidebar-name">
            <div class="sidebar-name-arrow"><br /></div>
            <h3>%%__MOD_NAME__%%</h3>
        </div>
        <div class="modules-holder-wrap-inside">
            <div id='%%__MOD_ID__%%' class='modules-sortables'>
            </div>
            <div class="module-controls">
            <div class="modulemanager-description">
                <div class="module-title"><h4><?php _e('Module Description','module-manager'); ?><a href='javascript:;' title="<?php echo esc_attr(__('Click for description','module-manager')); ?>" style='position:relative;float:none;margin:0 0 0 10px;display:inline-block;vertical-align:middle;' class="sidebar-name-arrow"></a></h4></div>
                <textarea class="module-info-description" rows="4"></textarea>
            </div>
                <a href='javascript:;' class='button module-remove'><?php _e('Remove','module-manager'); ?></a>
                <a href='javascript:;' class='button-primary module-export'><?php _e('Export','module-manager'); ?></a>
            </div>
        </div>
    </div>
</script>
<!-- /templates -->

<div id='modman-needs-save' class='updated' style="display:none;" ><p><?php _e('Settings changed, you need to resave before leaving the page','module-manager'); ?></p></div>

<div class="modules-liquid-left">
    <div id="modules-left">
        <div id='available-modules' class='modules-holder-wrap'>
            <h3><?php _e('Available Items','module-manager'); ?></h3>
            <div class="module-holder">
                <div class="description">
                    <p><?php _e('Drag elements from here to a module on the right to group them to modules. Drag elements back here to remove them from modules.'); ?></p>
                    <p class="modman-search-wrap">
                        <i class="icon-search"></i><input type='text' placeholder='<?php echo esc_attr(__('Search','module-manager')); ?>' class='modman-search' value='' onkeyup="ModuleManager.filter(this.value);" />
                    </p>
                </div>

                <div id="module-list"><?php
                foreach ( $items as $section => $_items ) { ?>
                    <div class="modman-module-section">
                        <h4><?php echo esc_html( $_items['info']['title'] ); ?></h4>
                        <div class="modman-module-section-inner">
                        <?php modman_list_items( $_items['items'], $section, (isset($_items['info']['icon']))?$_items['info']['icon']:null, (isset($_items['info']['icon_css']))?$_items['info']['icon_css']:null ); ?>
                        </div>
                    </div>
                <?php }  ?>
                <br class="clear" />
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modules-liquid-right" style='position:relative;'>
    <div id="modules-right">
        <h3><?php _e('Modules','module-manager'); ?></h3>
        <?php
        $i = 0;
        $cnt=0;
        if ($modules && !empty($modules))
        {
            foreach ( $modules as $module_title=>$mdata )
            {
                $wrap_class = 'modules-holder-wrap';
                if ( $i ) $wrap_class .= ' closed'; ?>
                <div class="<?php echo esc_attr( $wrap_class ); ?>">
                    <div class="sidebar-name">
                        <div class="sidebar-name-arrow"></div>
                        <h3><?php echo esc_html( $module_title ); ?></h3>
                    </div>
                    <div class="modules-holder-wrap-inside">
                        <?php modman_list_module_elements( $module_title, $mdata, $cnt, $items ); ?>
                        <div class="module-controls">
                            <div class="modulemanager-description">
                                <div class="module-title">
                                    <h4><?php _e('Module Description','module-manager'); ?>
                                        <a href='javascript:;' title="<?php echo esc_attr(__('Click for description','module-manager')); ?>" style='' class="sidebar-name-arrow"></a>
                                    </h4>
                                </div>
                                <textarea class="module-info-description" rows="4"><?php
                                    if (isset($mdata[MODMAN_MODULE_INFO]) && isset($mdata[MODMAN_MODULE_INFO]['description']))
                                    {
                                        echo stripslashes($mdata[MODMAN_MODULE_INFO]['description']);
                                    }
                                ?></textarea>
                            </div>
                            <a href='javascript:;' class='button module-remove'><?php _e('Remove','module-manager'); ?></a>
                            <a href='javascript:;' class='button button-primary module-export'><?php _e('Export','module-manager'); ?></a>
                        </div>
                    </div>
                </div>
            <?php $i++;
            }
        } ?>
    </div>

    <div class="modules-main-controls-container">
        <?php wp_nonce_field('modman-save-modules-action', 'modman-save-modules-field'); ?>
        <a href='javascript:;' onclick="ModuleManager.addNew();"  class='button button-large modman-add-module'><?php _e('Add New', 'module-manager'); ?></a>
        <div class="ajax-feedback-container">
            <img src="<?php echo esc_url(admin_url('images/wpspin_light.gif')); ?>" class="ajax-feedback" title="" alt="" />
            <input type='button' class='button button-primary button-large' value='<?php echo esc_attr(__('Save Modules', 'module-manager')); ?>' onclick="ModuleManager.save();" />
        </div>
    </div>

</div>

<br class="clear" />
<input id='__module_cnt__' type='hidden' value='<?php echo $cnt; ?>' />