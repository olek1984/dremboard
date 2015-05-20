<?php

/**
 * 
 * GK BP Latest Photos Widget class
 *
 **/

class BP_LatestPhotos_Widget extends WP_Widget {
	/**
	 *
	 * Constructor
	 *
	 * @return void
	 *
	 **/
	function BP_LatestPhotos_Widget() {
		$this->WP_Widget(
			'widget_gk_latestphotos', 
			__( 'BP Latest Photos', GKTPLNAME ), 
			array( 
				'classname' => 'widget_gk_latestphotos', 
				'description' => __( 'Use this widget to show recent photos of members', GKTPLNAME) 
			)
		);
		
		$this->alt_option_name = 'widget_gk_latestphotos';
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
		
		$title = apply_filters('widget_title', empty($instance['title']) ? __( 'Latest Photos', GKTPLNAME ) : $instance['title'], $instance, $this->id_base);
		
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
		$user_id  = empty($instance['user_id']) ? '' : $instance['user_id'];
		$offset = empty($instance['offset']) ? 0 : $instance['offset'];
		$columns = empty($instance['columns']) ? 5 : $instance['columns'];	
		$rows = empty($instance['rows']) ? 2 : $instance['rows'];
		$cache = empty($instance['cache']) ? 60 : $instance['cache'];

		if(!is_numeric($offset)) { 
			$offset = 0;
		} else {
			$offset = intval($offset);
		}
		
		if(!is_numeric($rows)) {
			$rows = 0;
		} else {
			$rows = intval($rows);
		}
		
		if(!is_numeric($columns)) {
			$columns = 0;
		} else {
			$columns = intval($columns);
		}
		
		$model = new RTMediaModel();		
		
		if ($data_source_type == 'user') {
			$media = $model->get_media ( array( 'media_type' => 'photo', 'media_author' => $user_id), $offset , $columns * $rows ); 
		} else {
			$media = $model->get_media ( array( 'media_type' => 'photo', 'source' => array('compare' => 'IS NULL AND 1 IN', 'value'=>'1')), $offset, $columns * $rows ); 
		}
							
		if ($media == null) :
		
			echo __('There has been no recent activity.', GKTPLNAME);
		 
		else : ?>
			<div class="gk-bp-latest-photos" data-cols="<?php echo $columns; ?>">
				<div>
					<?php foreach($media as $img) : ?>
					<a href ="<?php echo get_rtmedia_permalink ( rtmedia_id ( $img->media_id ) ).'/single/'; ?>">
						<img src="<?php rtmedia_image("rt_media_thumbnail", rtmedia_id ( $img->media_id )); ?>" alt="<?php _e('View full image', GKTPLNAME); ?>" />
					</a>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif;
		
	    // save the cache results
		$cache_output = ob_get_flush();
		
		if(is_numeric($cache) && $cache > 0) {
			set_transient(md5($this->id) , $cache_output, 60 * $cache);
		} else {
			delete_transient(md5($this->id));
		}
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
		$instance['user_id'] = strip_tags($new_instance['user_id']);
		$instance['offset'] = strip_tags( $new_instance['offset'] );
		$instance['columns'] = strip_tags( $new_instance['columns'] );
		$instance['rows'] = strip_tags( $new_instance['rows'] );
		$instance['cache'] = strip_tags( $new_instance['cache'] );
		
		$this->refresh_cache();

		$alloptions = wp_cache_get('alloptions', 'options');
		if(isset($alloptions['widget_gk_latestphotos'])) {
			delete_option( 'widget_gk_latestphotos' );
		}

		return $instance;
	}
	
	/**
	 *
	 * Refreshes the widget cache data
	 *
	 * @return void
	 *
	 **/

	function refresh_cache() {
		if(is_array(get_option('widget_widget_gk_latestphotos'))) {
	    	$ids = array_keys(get_option('widget_widget_gk_latestphotos'));
	    } else {
	    	$ids = array();
	    }
	    
	    for($i = 0; $i < count($ids); $i++) {
	        if(is_numeric($ids[$i])) {
	            delete_transient(md5('widget_gk_latestphotos-' . $ids[$i]));
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
		$user_id  = isset($instance['user_id']) ? esc_attr($instance['user_id']) : '';
		$offset = isset($instance['offset']) ? esc_attr($instance['offset']) : 0;
		$columns = isset($instance['columns']) ? esc_attr($instance['columns']) : 5;
		$rows = isset($instance['rows']) ? esc_attr($instance['rows']) : 2;
		$cache = isset($instance['cache']) ? esc_attr($instance['cache']) : 60;
	?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', GKTPLNAME ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'user_id' ) ); ?>" style="display: inline-block; min-width: 64px;"><?php _e( 'User ID:', GKTPLNAME ); ?></label>
			<input size="3" id="<?php echo esc_attr( $this->get_field_id( 'user_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'user_id' ) ); ?>" type="text" value="<?php echo esc_attr( $user_id  ); ?>" />
		</p>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'columns' ) ); ?>" style="display: inline-block; min-width: 64px;"><?php _e( 'Columns: ', GKTPLNAME ); ?></label>
			<input size="3" id="<?php echo esc_attr( $this->get_field_id( 'columns' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'columns' ) ); ?>" type="text" value="<?php echo esc_attr( $columns  ); ?>" />
		</p>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'rows' ) ); ?>" style="display: inline-block; min-width: 64px;"><?php _e( 'Rows: ', GKTPLNAME ); ?></label>
			<input size="3" id="<?php echo esc_attr( $this->get_field_id( 'rows' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'rows' ) ); ?>" type="text" value="<?php echo esc_attr( $rows ); ?>" />
		</p>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'offset' ) ); ?>" style="display: inline-block; min-width: 64px;"><?php _e( 'Offset:', GKTPLNAME ); ?></label>
			<input size="3" id="<?php echo esc_attr( $this->get_field_id( 'offset' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'offset' ) ); ?>" type="text" value="<?php echo esc_attr( $offset ); ?>" />
		</p>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'cache' ) ); ?>" style="display: inline-block; min-width: 64px;"><?php _e( 'Cache time:', GKTPLNAME ); ?></label>
			<input size="3" id="<?php echo esc_attr( $this->get_field_id( 'cache' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'cache' ) ); ?>" type="text" value="<?php echo esc_attr( $cache ); ?>" /> (min)
		</p>
	<?php
	}
}

// EOF