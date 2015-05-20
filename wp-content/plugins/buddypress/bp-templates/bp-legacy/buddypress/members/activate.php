<div id="buddypress">

	<?php do_action( 'bp_before_activation_page' ); ?>

	<div class="page" id="activate-page">

		<?php do_action( 'template_notices' ); ?>

		<?php do_action( 'bp_before_activate_content' ); ?>

		<?php if ( bp_account_was_activated() ) : ?>
        
            <div class="welcome member">
                Welcome<?php 
                global $bp;
                if (isset($bp->activated_user_id)){
                    echo ", ".bp_core_get_user_displayname ($bp->activated_user_id);
                }   
                ?>
                
            </div>
            <div class="welcome text">
                Thanks for creating a DrēmBoard Account. Use it connect with family and friends while sharing and collecting photos &amp; videos.
            </div>

            <a class="btn loginbtn" href="/login">log in</a>

			<?php if ( isset( $_GET['e'] ) ) : ?>
				<p><?php _e( 'Your account was activated successfully! Your account details have been sent to you in a separate email.', 'buddypress' ); ?></p>
			<?php else : ?>
				<p><?php printf( __( 'Your account was activated successfully! You can now <a href="%s">log in</a> with the username and password you provided when you signed up.', 'buddypress' ), wp_login_url( bp_get_root_domain() ) ); ?></p>
			<?php endif; ?>

		<?php else : ?>

			<p><?php _e( 'Please provide a valid activation key.', 'buddypress' ); ?></p>

			<form action="" method="get" class="standard-form" id="activation-form">

				<label for="key"><?php _e( 'Activation Key:', 'buddypress' ); ?></label>
				<input type="text" name="key" id="key" value="" />

				<p class="submit">
					<input type="submit" name="submit" value="<?php _e( 'Activate', 'buddypress' ); ?>" />
				</p>

			</form>

		<?php endif; ?>

		<?php do_action( 'bp_after_activate_content' ); ?>

	</div><!-- .page -->

	<?php do_action( 'bp_after_activation_page' ); ?>

</div><!-- #buddypress -->