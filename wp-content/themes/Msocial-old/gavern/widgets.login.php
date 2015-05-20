<?php

/**
 * 
 * GK Login Widget class
 *
 **/

class GK_Login_Widget extends WP_Widget {
	/**
	 *
	 * Constructor
	 *
	 * @return void
	 *
	 **/
	function GK_Login_Widget() {
		$this->WP_Widget(
			'widget_gk_login', 
			__( 'GK Login', GKTPLNAME ), 
			array( 
				'classname' => 'widget_gk_login', 
				'description' => __( 'Use this widget to show login form e.g. on sidebar postition', GKTPLNAME) 
			)
		);
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
		// the part with the title and widget wrappers cannot be cached! 
		// in order to avoid problems with the calculating columns
		//
		extract($args, EXTR_SKIP);
		
		$lost_pw = empty($instance['lost_pw']) ? '' : $instance['lost_pw'];
		$register = empty($instance['register']) ? '' : $instance['register'];
		
		$title = apply_filters('widget_title', empty($instance['title']) ? __( 'Login Form', GKTPLNAME ) : $instance['title'], $instance, $this->id_base);
		
		echo $before_widget;
		echo $before_title;
		echo $title;
		echo $after_title;

		ob_start();
		//
		
		if ( is_user_logged_in() ) : ?>
			<?php 
				
				global $current_user;
				get_currentuserinfo();
			
			?>
			
			<p>
				<?php echo __('Hi, ', GKTPLNAME) . ($current_user->user_firstname) . ' ' . ($current_user->user_lastname) . ' (' . ($current_user->user_login) . ') '; ?>
			</p>
			
			<p id="gk-logout">
				<a href="<?php echo wp_logout_url(); ?>" title="<?php _e('Logout', GKTPLNAME); ?>" class="btn">
					 <?php _e('Logout', GKTPLNAME); ?>
				</a>
			</p>
		<?php else : ?>
		    
			<?php 
				gk_login_form(
					array(
						'echo' => true,
						'form_id' => 'gk-loginform',
						'label_remember' => __( 'Remember Me', GKTPLNAME ),
						'label_log_in' => __( 'Log In', GKTPLNAME ),
						'label_password' => __( 'Password', GKTPLNAME ),
						'username_placeholder' => __( 'Username', GKTPLNAME ),
						'password_placeholder' => __( 'Password', GKTPLNAME ),
						'id_username' => 'gk-user_login',
						'id_password' => 'gk-user_pass',
						'id_remember' => 'gk-rememberme',
						'id_submit' => 'gk-wp-submit',
						'remember' => true,
						'value_username' => NULL,
						'value_remember' => false 
					)
				); 
			?>
			
			<nav class="gklogin-small">
				<ul>
					<?php if ($lost_pw == 'enabled') : ?>
					<li>
						<a href="<?php echo home_url(); ?>/wp-login.php?action=lostpassword" title="<?php _e('Password Lost and Found', GKTPLNAME); ?>"><?php _e('Lost your password?', GKTPLNAME); ?></a>
					</li>
					<?php endif; ?>
					<?php if ($register == 'enabled') : ?>
					<li>
						<a href="<?php echo home_url(); ?>/wp-login.php?action=register" title="<?php _e('Not a member? Register', GKTPLNAME); ?>"><?php _e('Register', GKTPLNAME); ?></a>
					</li>
					<?php endif; ?>
				</ul>
			</nav>
			<div id="gk-login-separator"></div>
		<?php endif;
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
			$instance['lost_pw'] = strip_tags($new_instance['lost_pw']);
			$instance['register'] = strip_tags($new_instance['register']);
			
			$this->refresh_cache();
	
			$alloptions = wp_cache_get('alloptions', 'options');
			if(isset($alloptions['widget_gk_login'])) {
				delete_option( 'widget_gk_login' );
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
		wp_cache_delete( 'widget_gk_login', 'widget' );
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
		$lost_pw = isset($instance['lost_pw']) ? esc_attr($instance['lost_pw']) : '';
		$register = isset($instance['register']) ? esc_attr($instance['register']) : '';
	?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', GKTPLNAME ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
	
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('lost_pw')); ?>"><?php _e('Lost Password:', GKTPLNAME); ?></label>
			
			<select id="<?php echo esc_attr( $this->get_field_id('lost_pw')); ?>" name="<?php echo esc_attr( $this->get_field_name('lost_pw')); ?>">
				<option value="enabled"<?php echo (esc_attr($lost_pw) == 'enabled') ? ' selected="selected"' : ''; ?>>
					<?php _e('Enabled', GKTPLNAME); ?>
				</option>
				<option value="disabled"<?php echo (esc_attr($lost_pw) == 'disabled') ? ' selected="selected"' : ''; ?>>
					<?php _e('Disabled', GKTPLNAME); ?>
				</option>
			</select>
		</p>
		
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('register')); ?>"><?php _e('Register Link:', GKTPLNAME); ?></label>
			
			<select id="<?php echo esc_attr( $this->get_field_id('register')); ?>" name="<?php echo esc_attr( $this->get_field_name('register')); ?>">
				<option value="enabled"<?php echo (esc_attr($register) == 'enabled') ? ' selected="selected"' : ''; ?>>
					<?php _e('Enabled', GKTPLNAME); ?>
				</option>
				<option value="disabled"<?php echo (esc_attr($register) == 'disabled') ? ' selected="selected"' : ''; ?>>
					<?php _e('Disabled', GKTPLNAME); ?>
				</option>
			</select>
		</p>
		
	<?php
	}
}

// EOF