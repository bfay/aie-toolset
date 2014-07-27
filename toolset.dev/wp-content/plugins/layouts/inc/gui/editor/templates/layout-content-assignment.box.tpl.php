<div class="layout-content-assignment js-layout-content-assignment">
    <p class="where-used-title-wrap"> <?php echo $title_display; ?> </p>
    <?php if( $lists !== null ):?>
        <ul>

            <?php
            // single posts block
            if( property_exists($lists, 'posts') ):
                foreach( $lists->posts as $post ):
                    if( $wpddlayout->post_types_manager->post_type_is_in_layout( $post->post_type, $current ) === false ):
                    ?>
                    <li>
                        <a href="<?php echo $post->permalink ?>" target="_blank"><?php echo $post->post_title; ?></a>
                    </li>
             <?php
                endif;
            endforeach;
            endif; ?>

            <?php
            // post types block
            if( property_exists($lists, 'post_types') && is_array($lists->post_types) ):
                foreach( $lists->post_types as $post_type ):
                    $post_type = (object) $post_type;
                    $type = get_post_type_object( $post_type->post_type );
                    $checked = $wpddlayout->post_types_manager->post_type_is_in_layout($type->name, $current) ? 'checked' : '';
                    ?>
                    <li>
                        <?php if( ( $post_type->missing !== 0 ) && ( $post_type->post_num === $post_type->missing ) ):?>
                            <?php echo $type->labels->name; ?>
                        <?php else:
                         $show = $this->get_first_post_of_type( $post_type->post_type, $current  );
                         ?>
                        <a href="<?php echo get_permalink( $show->ID ) ?>" target="_blank"><?php echo $type->labels->name; ?></a>
                        <?php endif; ?>
                       <?php
                       $wpddlayout->post_types_manager->print_apply_to_all_link_in_layout_editor($type, $checked, $current); ?>
                    </li>
                <?php
                endforeach;
            endif; ?>

            <?php
            // archives block
            if( property_exists($lists, 'loops') ):
                foreach( $lists->loops as $loop ):
                    $loop = (object) $loop;
                
                    ?>

                        <?php if ($loop->href): ?>
                            <li><a href="<?php echo $loop->href ?>" target="_blank"><?php echo $loop->title; ?></a></li>
                        <?php else: ?>
                            <li><?php echo $loop->title; ?> - <?php _e('(No previews available)', 'ddl-layouts'); ?></li>
                        <?php endif; ?>

                    <?php
                endforeach;
            endif; ?>
        </ul>
    <?php endif;

    $button_data = array(

        );
    ?>

    <button id="layout-content-assignment-button" class="js-layout-content-assignment-button button button-large" name="layout-content-assignment-button">
       <?php  _e('Change how this Layout is used', 'ddl-layouts'); ?>
     </button>
</div>