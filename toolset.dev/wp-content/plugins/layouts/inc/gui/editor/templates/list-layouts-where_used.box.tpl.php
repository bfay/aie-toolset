<?php
if ( sizeof($posts) > 0 ): ?>
	<div class="dd-layouts-wrap">
		<div class="dd-layouts-where-used js-dd-layouts-where-used">
			<p class="where-used-title-wrap">
				<?php _e('This layout is used for these posts:', 'ddl-layouts');
                $data_amount = $all ? 'not_all' : 'all';
                $text_show = $all ? __('Show some', 'ddl-layouts') : __('Show all', 'ddl-layouts');
                
                if( ($items->found_posts > $items->shown_posts) || $data_amount == 'not_all' ):
                ?>
                    <span class="show_all_wrap"><a class="show-all js-show-all button button-small hide-if-no-js" href="#" data-amount="<?php echo $data_amount; ?>" data-textnotall="<?php _e('Show some', 'ddl-layouts') ;?>" data-textall="<?php _e('Show all', 'ddl-layouts') ;?>"><?php echo $text_show; ?></a></span>
			    <?php
                endif; ?>
              </p>

			<div>
				<?php
                $type = '';
                $count = 0;
                foreach($posts as $post):
                    if( $post->post_type !== $type):
                            $type = $post->post_type;
                            $label = $post_types[$type]->labels->name;
                            $class = $count > 0 ? ' padding-top' : '';
                            if( $count > 0 ) echo '</ul>';
                        ?>

                        <ul>
                        <li class="post-type-li-in-where-used-list <?php echo $class; ?>"> <?php echo $label; ?> (<?php echo $items->{$type} ;?>) </li>
                    <?php endif; ?>
					<li>
						<a href="<?php echo $post->edit_link ?>"><?php echo $post->post_title; ?></a>
					</li>
				<?php
                    if( $post->post_type !== $type):?>
                        </ul>
                    <?php endif;
                 $count++;
                endforeach; ?>
			</div>
		</div>
	</div>
<?php endif; ?>