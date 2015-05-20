<?php
/*

  If you would like to edit this file, copy it to your current theme's directory and edit it there.

  Theme My Login will always look in your theme's directory first, before using this default template.

 */
?>
<div class="welcome-message">
	<div class="welcome-title">Welcome to </div>
	<div class="welcome-title name">Drēmboard</div>
	<div class="welcome-dremboard">
		<img src="<?php echo get_template_directory_uri() ?>/images/final logo.png">
	</div>
	<div class="welcome-vision">Drēm It, Believe It, Achieve It</div>
	<div class="welcome-desc">Drēmboard is a place to connect with family and friends while sharing and collecting photos & videos.</div>
</div>
<style>
    #gk-sidebar {
        display: none!important;
    }
    
	div#gk-content-wrapper{
		background-image: url(<?php echo get_template_directory_uri() ?>/images/login_bground.png);
	}
	.content {
		margin-top: 0px !important;
	}
	.box {
		margin: 0px !important;
		background: none !important;
		border: none !important;
	}
	.box-title, .item-options {
		display: none !important;
	}
	#gk-mainbody-columns{
		background: none !important;
	}
	.widget_bp_core_members_widget {
		width: 100% !important;
	}
	#gk-user-area {
		background: none !important;
	}
	#gk-user-area > a {
		display: none !important;
	}
	#gk-header {
		display: none;
	}
	#gk-mainbody-columns > aside {
		margin-right: 0px;
		width: 100% !important;
		margin-left: 10px;
	}
	.widget_gk_latestphotos {
		display: none;
	}
    .TrendDremsWidget {
		display: none!important;
	}
	div#gk-style-area {
		display: none;
	}
	#gk-mainbody-columns > aside {
		background: none;
	}
	#gk-sidebar .box.widget.buddypress ul.item-list img.avatar, 
	#gk-sidebar .box.widget.buddypress div.item-avatar img, 
	#gk-sidebar .box.widget.buddypress ul.item-list .item-avatar > a, 
	#gk-sidebar .box.widget.buddypress div.item-avatar > a	.avatar {
		width: 73px !important;
		height: 73px !important;
	}
	.login form label {
		color: #FFF !important;
	}
	.content p {
		font-size: 12px !important;
	}
	.login input#captcha_code {
		width: 91% !important;
	}
	#gk-mainbody {
		padding: 0px;
	}
	@media screen and (max-device-width: 580px){
	.widget_bp_core_members_widget {
		display: none;
	}
	#gk-mainbody {
		padding: 0 0 30px 0;
	}
	}
</style>
	
<div class="login" id="theme-my-login<?php $template->the_instance(); ?>">

    <form name="loginform" id="loginform<?php $template->the_instance(); ?>" action="<?php $template->the_action_url('login'); ?>" method="post">

        <p>

                        <!--<label for="user_login<?php //$template->the_instance();  ?>"><?php //_e( 'Username' );  ?></label>-->
            <i class="icon-user"></i>
            <input class="username" type="text" name="log" id="user_login<?php $template->the_instance(); ?>" class="input" value="<?php $template->the_posted_value('log'); ?>" size="20" placeholder="<?php _e('User name'); ?>"/>

        </p>

        <p>

                        <!--<label for="user_pass<?php //$template->the_instance();  ?>"><?php //_e( 'Password' );  ?></label>-->
            <i class="icon-lock"></i>
            <input class="password" type="password" name="pwd" id="user_pass<?php $template->the_instance(); ?>" class="input" value="" size="20" placeholder="<?php _e('Password'); ?>"/>

        </p>






        <div class="width100" style="position: relative; margin-top: 10px;">
            <div class="forgetmenot">

                <input name="rememberme" type="checkbox" id="rememberme<?php $template->the_instance(); ?>" value="forever" />

                <label for="rememberme<?php $template->the_instance(); ?>"><?php esc_attr_e('Remember Me'); ?></label>

            </div>

            <div class="submit">

                <input type="submit" name="wp-submit" id="wp-submit<?php $template->the_instance(); ?>" class="submit-btn" value="<?php esc_attr_e('Log In'); ?>" />

                <input type="hidden" name="redirect_to" value="<?php $template->the_redirect_url('login'); ?>" />

                <input type="hidden" name="instance" value="<?php $template->the_instance(); ?>" />

                <input type="hidden" name="action" value="login" />

            </div>
        </div>
<?php $template->the_action_template_message('login'); ?>

<?php $template->the_errors(); ?>

<?php do_action('login_form'); ?>

    </form>

<?php $template->the_action_links(array('login' => false)); ?>

</div>

<?php
    if (!wp_is_mobile()):
    $rtmedia_model = new RTMediaModel();
    $cover_arts = $rtmedia_model->get_media(array('cover_art' => '1'));
    
?>
<div class="cover-arts">
        <?php foreach ($cover_arts as $key => $cover_art):
            $guid = rtmedia_image ( $size = 'rt_media_thumbnail', $id = $cover_art->id ,$recho = false );
            if ($key > 11){
                break;
            }
            ?>
    <div class="cover">
            <img src="<?php echo $guid;?>" class="thumbnail art" alt="<?php echo $cover_art->media_title;?>" />
        </div>
        <?php endforeach;?>
</div>
<?php endif;?>