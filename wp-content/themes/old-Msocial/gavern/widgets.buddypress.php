<?php

/**
 * 
 * GK BP Widget class
 *
 **/

class GK_BP_Widget extends WP_Widget {
	/**
	 *
	 * Constructor
	 *
	 * @return void
	 *
	 **/
	function GK_BP_Widget() {
		$this->WP_Widget(
			'widget_gk_buddypress', 
			__( 'GK BuddyPress', GKTPLNAME ), 
			array( 
				'classname' => 'widget_gk_buddypress', 
				'description' => __( 'Use this widget to show recent status of members', GKTPLNAME) 
			)
		);
		
		$this->alt_option_name = 'widget_gk_buddypress';
	}

	/**
	 *
	 * Outputs the HTML code of this widget.
	 *
	 * @param array An array of standard parameters for widgets in this theme
	 * @param array An array of settings for this widget instance
	 * @return void
	 *
	 **/
	function widget($args, $instance) {
		global $bp;
		global $rtmedia_query, $rtmedia_interaction, $rtmedia_media;
		global $rtmedia_backbone;
		
		$cache = get_transient(md5($this->id));
		
		// the part with the title and widget wrappers cannot be cached! 
		// in order to avoid problems with the calculating columns
		//
		extract($args, EXTR_SKIP);
		
		$title = apply_filters('widget_title', empty($instance['title']) ? __( 'BuddyPress Activity', GKTPLNAME ) : $instance['title'], $instance, $this->id_base);
		
		echo $before_widget;
		echo $before_title;
		echo $title;
		echo $after_title;
		
		if($cache) {
			echo $cache;
			echo $after_widget;
			return;
		}

		ob_start();
		//
		$content_type = empty($instance['content_type']) ? 'update' :  $instance['content_type'];
		$data_source_type = empty($instance['data_source_type']) ? 'latest' :  $instance['data_source_type'];
		$user_id  = empty($instance['user_id']) ? '' : $instance['user_id'];
		$show_avatar = empty($instance['show_avatar']) ? 'enable' : $instance['show_avatar'];
		$show_username = empty($instance['show_username']) ? 'enable' : $instance['show_username'];
		$show_readmore = empty($instance['show_readmore']) ? 'enable' : $instance['show_readmore'];
		$avatar_position = empty($instance['avatar_position']) ? 'left' : $instance['avatar_position'];
		$readmore_text = empty($instance['readmore_text']) ? 'read more' : $instance['readmore_text'];
		$word_count = empty($instance['word_count']) ? 20 : $instance['word_count'];
		$offset = empty($instance['offset']) ? 0 : $instance['offset'];	
		?>
		
		<?php if ($content_type == 'photo') {
			$model = new RTMediaModel();		
			if ($data_source_type == 'user') {
				$media = $model->get_media ( array( 'media_type' => 'photo', 'media_author' => $user_id), intval($offset) , 1, $order_by = 'id desc' ); 
			}
			
			else {
				$media = $model->get_media ( array( 'media_type' => 'photo'), intval($offset), 1, $order_by = 'id desc' ); 
			}
			
			if ($media == null) {
				echo __('There has been no recent activity.', GKTPLNAME);
			}
			
			else { ?>
				<a href ="<?php echo get_rtmedia_permalink ( rtmedia_id ( $media[0]->media_id ) ); ?>" class="gk-photo" style ="background-image: url(<?php rtmedia_image("rt_media_single_image", rtmedia_id ( $media[0]->media_id )); ?>);" >
				</a>
			<?php } ?>
			
		<?php }
		else {
			
			if ($data_source_type == 'user') {
				$args = array(
					'max' => 1,
					'per_page' => 1,
					'page' => $offset,
					'action' => 'activity_update',
					'user_id' => $user_id
				);
			}
			else {
				$args = array(
					'max' => 1,
					'per_page' => 1,
					'page' => $offset,
					'action' => 'activity_update'
				);
			} ?>			
				<div class="gk-status">
				<?php // BuddyPress activity loop starts
				if ( bp_has_activities( $args ) ) :
					
					while ( bp_activities() ) : bp_the_activity(); 

					// display member's avatar
					if ($show_avatar == 'enabled') : ?>
			            <a href="<?php bp_activity_user_link() ?>" class="gk-buddy-avatar <?php if($avatar_position == 'right') { echo 'right'; } ?>">
			            	<?php bp_activity_avatar( 'type=full' ) ?>
			            </a>
			        <?php endif; ?>
					
					<div class="gk-member-status">
						<?php 
						if ($show_username == 'enabled') {
							$activity_string = strip_tags(bp_get_activity_action(), '<a>');
							
							preg_match('@<a.*?</a>@mis', $activity_string, $acticity_matches);
							
							if(is_array($acticity_matches) && isset($acticity_matches[0])) {
								$activity_string = $acticity_matches[0];
							}
							
							echo $activity_string;					
						}
						
						if ($show_readmore == 'disabled') {
							echo '<a class="no-readmore" href=' .bp_get_activity_thread_permalink(). '>' .$this->activity_text(bp_get_activity_content_body(), $word_count, $readmore_text, false). '</a>';
						}
						
						else {
						echo '<p>'. $this->activity_text(bp_get_activity_content_body(), $word_count, $readmore_text, true). '</p>';
						} ?>
						
					</div>
					<?php endwhile;
				else: 
					echo __('There has been no recent activity.', GKTPLNAME);
				endif; ?>
				</div>
				
		<?php }
		
		
		// save the cache results
		$cache_output = ob_get_flush();
		set_transient(md5($this->id) , $cache_output, 3 * 60 * 60);
		// 
		echo $after_widget;
	}
	

	/**
	 *
	 * Used in the back-end to update the module options
	 *
	 * @param array new instance of the widget settings
	 * @param array old instance of the widget settings
	 * @return updated instance of the widget settings
	 *
	 **/
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['content_type'] = strip_tags($new_instance['content_type']);
		$instance['data_source_type'] = strip_tags($new_instance['data_source_type']);
		$instance['user_id'] = strip_tags($new_instance['user_id']);
		$instance['show_avatar'] = strip_tags($new_instance['show_avatar']);
		$instance['show_username'] = strip_tags($new_instance['show_username']);
		$instance['show_readmore'] = strip_tags($new_instance['show_readmore']);
		$instance['avatar_position'] = strip_tags( $new_instance['avatar_position'] );
		$instance['readmore_text'] = strip_tags( $new_instance['readmore_text'] );
		$instance['word_count'] = strip_tags( $new_instance['word_count'] );
		$instance['offset'] = strip_tags( $new_instance['offset'] );
		$this->refresh_cache();

		$alloptions = wp_cache_get('alloptions', 'options');
		if(isset($alloptions['widget_gk_buddypress'])) {
			delete_option( 'widget_gk_buddypress' );
		}

		return $instance;
	}
	
	
	/**
		 *
		 * Limits the activity text to specified words amount
		 *
		 * @param string input text
		 * @param int amount of words
		 * @param string "readmore" text
		 * @param boolean enable/disable readmore text
		 * @return string the cutted text
		 *
		 **/
	function activity_text($input, $amount, $text_end, $show_readmore) {
		global $activities_template;
		
		$output = '';
		$input = strip_tags($input);
		$input = explode(' ', $input);
		
		for($i = 0; $i < $amount; $i++) {
			if(isset($input[$i])) {
				$output .= $input[$i] . ' ';
			}
		}
		
		if(count($input) > $amount) {
			$output .= '&hellip;';
		}
		
		if ($show_readmore) {
			$output .= '<a href=' .bp_get_activity_thread_permalink(). '>' .$text_end. '</a>';
		}
		
		return $output;
	}
	
	
	/**
	 *
	 * Refreshes the widget cache data
	 *
	 * @return void
	 *
	 **/

	function refresh_cache() {
		if(is_array(get_option('widget_widget_gk_buddypress'))) {
	    	$ids = array_keys(get_option('widget_widget_gk_buddypress'));
	    } else {
	    	$ids = array();
	    }
	    
	    for($i = 0; $i < count($ids); $i++) {
	        if(is_numeric($ids[$i])) {
	            delete_transient(md5('widget_gk_buddypress-' . $ids[$i]));
	        }
	    }
	}


	/**
	 *
	 * Outputs the HTML code of the widget in the back-end
	 *
	 * @param array instance of the widget settings
	 * @return void - HTML output
	 *
	 **/
	function form($instance) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$content_type = isset($instance['content_type']) ? esc_attr($instance['content_type']) : 'update';
		$data_source_type = isset($instance['data_source_type']) ? esc_attr($instance['data_source_type']) : 'latest';
		$user_id  = isset($instance['user_id']) ? esc_attr($instance['user_id']) : '';
		$show_avatar = isset($instance['show_avatar']) ? esc_attr($instance['show_avatar']) : 'enabled';
		$show_username = isset($instance['show_username']) ? esc_attr($instance['show_username']) : 'enabled';
		$show_readmore = isset($instance['show_readmore']) ? esc_attr($instance['show_readmore']) : 'enabled';
		$avatar_position = isset($instance['avatar_position']) ? esc_attr($instance['avatar_position']) :'left';
		$readmore_text = isset($instance['readmore_text']) ? esc_attr($instance['readmore_text']) : 'read more';
		$word_count = isset($instance['word_count']) ? esc_attr($instance['word_count']) : 20;
		$offset = isset($instance['offset']) ? esc_attr($instance['offset']) : 1;
	?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', GKTPLNAME ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'content_type' ) ); ?>"><?php _e( 'Content type:', GKTPLNAME ); ?></label>
			
			<select id="<?php echo esc_attr( $this->get_field_id('content_type')); ?>" name="<?php echo esc_attr( $this->get_field_name('content_type')); ?>">
				<option value="photo"<?php echo (esc_attr($content_type) == 'photo') ? ' selected="selected"' : ''; ?>>
					<?php _e('Photo', GKTPLNAME); ?>
				</option>
				<option value="update"<?php echo (esc_attr($content_type) == 'update') ? ' selected="selected"' : ''; ?>>
					<?php _e('Update', GKTPLNAME); ?>
				</option>
			</select>
		</p>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'data_source_type' ) ); ?>"><?php _e( 'Data source:', GKTPLNAME ); ?></label>
			
			<select id="<?php echo esc_attr( $this->get_field_id('data_source_type')); ?>" name="<?php echo esc_attr( $this->get_field_name('data_source_type')); ?>">
				<option value="latest"<?php echo (esc_attr($data_source_type) == 'latest') ? ' selected="selected"' : ''; ?>>
					<?php _e('Latest status updates', GKTPLNAME); ?>
				</option>
				<option value="user"<?php echo (esc_attr($data_source_type) == 'user') ? ' selected="selected"' : ''; ?>>
					<?php _e('Specific user feed', GKTPLNAME); ?>
				</option>
			</select>
			
		</p>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'user_id' ) ); ?>"><?php _e( 'User ID:', GKTPLNAME ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'user_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'user_id' ) ); ?>" type="text" value="<?php echo esc_attr( $user_id  ); ?>" />
		</p>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'offset' ) ); ?>"><?php _e( 'Offset:', GKTPLNAME ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'offset' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'offset' ) ); ?>" type="text" value="<?php echo esc_attr( $offset ); ?>" />
		</p>
		
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('show_avatar')); ?>"><?php _e('Display avatars', GKTPLNAME); ?></label>
			
			<select id="<?php echo esc_attr( $this->get_field_id('show_avatar')); ?>" name="<?php echo esc_attr( $this->get_field_name('show_avatar')); ?>">
				<option value="enabled"<?php echo (esc_attr($show_avatar) == 'enabled') ? ' selected="selected"' : ''; ?>>
					<?php _e('Enabled', GKTPLNAME); ?>
				</option>
				<option value="disabled"<?php echo (esc_attr($show_avatar) == 'disabled') ? ' selected="selected"' : ''; ?>>
					<?php _e('Disabled', GKTPLNAME); ?>
				</option>
			</select>
		</p>
		
		<?php if ($show_avatar == 'enabled') : ?>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('avatar_position')); ?>"><?php _e('Avatar position: ', GKTPLNAME); ?></label>
			
			<select id="<?php echo esc_attr( $this->get_field_id('avatar_position')); ?>" name="<?php echo esc_attr( $this->get_field_name('avatar_position')); ?>">
				<option value="left"<?php echo (esc_attr($avatar_position) == 'left') ? ' selected="selected"' : ''; ?>>
					<?php _e('Left', GKTPLNAME); ?>
				</option>
				<option value="right"<?php echo (esc_attr($avatar_position) == 'right') ? ' selected="selected"' : ''; ?>>
					<?php _e('Right', GKTPLNAME); ?>
				</option>
			</select>
		</p>
		<?php endif; ?>
		
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('show_username')); ?>"><?php _e('Show username', GKTPLNAME); ?></label>
			
			<select id="<?php echo esc_attr( $this->get_field_id('show_username')); ?>" name="<?php echo esc_attr( $this->get_field_name('show_username')); ?>">
				<option value="enabled"<?php echo (esc_attr($show_username) == 'enabled') ? ' selected="selected"' : ''; ?>>
					<?php _e('Enabled', GKTPLNAME); ?>
				</option>
				<option value="disabled"<?php echo (esc_attr($show_username) == 'disabled') ? ' selected="selected"' : ''; ?>>
					<?php _e('Disabled', GKTPLNAME); ?>
				</option>
			</select>
		</p>
		
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('show_readmore')); ?>"><?php _e('Show readmore', GKTPLNAME); ?></label>
			
			<select id="<?php echo esc_attr( $this->get_field_id('show_readmore')); ?>" name="<?php echo esc_attr( $this->get_field_name('show_readmore')); ?>">
				<option value="enabled"<?php echo (esc_attr($show_readmore) == 'enabled') ? ' selected="selected"' : ''; ?>>
					<?php _e('Enabled', GKTPLNAME); ?>
				</option>
				<option value="disabled"<?php echo (esc_attr($show_readmore) == 'disabled') ? ' selected="selected"' : ''; ?>>
					<?php _e('Disabled', GKTPLNAME); ?>
				</option>
			</select>
		</p>
		
		<?php if ($show_readmore == 'enabled') : ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'readmore_text' ) ); ?>"><?php _e( 'Readmore text:', GKTPLNAME ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'readmore_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'readmore_text' ) ); ?>" type="text" value="<?php echo esc_attr( $readmore_text ); ?>" />
		</p>
		<?php endif; ?>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'word_count' ) ); ?>"><?php _e( 'Text limit (words):', GKTPLNAME ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'word_count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'word_count' ) ); ?>" type="text" value="<?php echo esc_attr( $word_count ); ?>" />
		</p>
	<?php
	}
}

// EOF