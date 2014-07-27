<div class="js-change-wrap-box">
    <?php
    $layout = WPDD_Layouts::get_layout_settings_raw_not_cached($current);
    $has_post_content = is_object($layout) && property_exists($layout, 'has_post_content_cell') ? $layout->has_post_content_cell : false;
    $disabled = $has_post_content === false && $do_not_show === false ? ' disabled ' : '';
    $class = $disabled ? 'post-types-list-in-layout-editor-alerted' : '';

    if ($do_not_show === false):?>
       <!-- <p>
            <?php _e('What content will this Layout be for?', 'ddl-layouts'); ?>
        </p> -->

        <h2 class="js-change-layout-use-section-title change-layout-use-section-title-outer"><span  class="change-layout-use-section-title js-collapse-group-individual"><?php _e('Template for multiple pages:', 'ddl-layouts'); ?></span>
            <i class="icon-caret-up js-collapse-group-in-dialog change-layout-use-section-title-icon-collapse" data-has_right_cell="<?php echo $has_post_content ?>"></i>
        </h2>

    <?php else: ?>
        <p>
            <?php _e('Use this layout for these post types:', 'ddl-layouts'); ?>
        </p>

    <?php endif; ?>
    <ul class="post-types-list-in-layout-editor js-post-types-list-in-layout-editor js-change-layout-use-section change-layout-use-section <?php echo $class;?>">


        <?php if ($do_not_show === false):?>
        <li>
            <div class="alert-no-post-content toolset-alert <?php if ($has_post_content): ?>hidden<?php endif; ?>">
               <!-- <i class="icon-remove js-remove-alert-message remove-alert-message"></i> -->
                <?php echo sprintf(
                    __("This layout doesn't have a Post Content or Content Template cell, so you cannot use it for an entire post type. %s",
                        'ddl-layouts'),
                        '<p><a href="#" id="dismiss-post-content-' . $current . '" class="dismiss-alert-message-post-content js-dismiss-alert-message-post-content">Ignore and use anyway</a></p>')
                     ?>
            </div>
        </li>
        <?php endif; ?>


        <?php foreach ($types as $type): ?>
            <?php
            $checked = $this->post_type_is_in_layout($type->name, $current) ? 'checked' : '';
            $unique_id = uniqid($id_string, true);
            ?>
            <li class="js-checkboxes-elements-wrap">
                <label for="post-type-<?php echo $unique_id . $type->name; ?>">
                    <input <?php echo $disabled; ?> type="checkbox" <?php echo $checked; ?> name="<?php echo WPDD_Layouts_PostTypesManager::POST_TYPES_OPTION_NAME; ?>"
                                                    class="js-ddl-post-type-checkbox<?php echo $id_string ? '-' . $id_string : ''; ?> js-ddl-post-content-checkbox"
                                                    value="<?php echo $type->name; ?>"
                                                    id="post-type-<?php echo $unique_id . $type->name; ?>">
                    <?php echo $type->labels->menu_name; ?>
                </label>
                <?php if ($show_ui === false): ?>
                    <?php //$this->print_apply_to_all_link_in_layout_editor($type, $checked, $current); ?>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
        <?php if ($do_not_show === false): ?>
        <li class="save-archives-options-wrap"> <input <?php echo $disabled;?> id="js-save-post-types-options-<?php echo $current;?>" name="save_post_types_options" class="button button-secondary button-large js-post-types-options js-buttons-change-update buttons-change-update" value="<?php _e('Update', 'ddl-layouts');?>" type="submit"></li>
        <?php endif; ?>
    </ul>



</div>