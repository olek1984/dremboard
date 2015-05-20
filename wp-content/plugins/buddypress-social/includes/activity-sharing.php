<?php

function buddy_social_button_activity_filter() {

    $activity_type = bp_get_activity_type();
    $activity_link = bp_get_activity_thread_permalink();
    
    $activity_id = bp_get_activity_id();
    $media_model = new RTMediaModel();
    $media_obj = $media_model->get(array('activity_id' => $activity_id));
    if (!empty($media_obj))
    {
        foreach ( $media_obj as $media ) {
            if ( $media->media_type == 'photo' ) {
                $src = wp_get_attachment_image_src ( $media->media_id );
                $fb_activity_link = $src[0];
            }elseif ( $media->media_type == 'video' ) {
                $fb_activity_link = wp_get_attachment_url ( $media->media_id );
            }elseif ( $media->media_type == 'music' ) {
                $fb_activity_link = wp_get_attachment_url ( $media->media_id );
            }else {
                $fb_activity_link = $activity_link;
            }
        }
    }
    else
    {
        $fb_activity_link = $activity_link;
    }
    
    $activity_title = bp_get_activity_feed_item_title();
    $plugin_path = plugins_url();
    
    if(is_user_logged_in()) {
    	$desc = strip_tags(strstr(bp_get_activity_content_body(), '<ul', true));
    }
	
    $buddy_social_facebook = '<a class="new-window 1 social foundicon-facebook" href="https://www.facebook.com/sharer/sharer.php?t='.$activity_title.'&u=' . $fb_activity_link . '" rel="facebox"><img src='.WP_PLUGIN_URL.'/buddypress/bp-core/images/facebook.png style="width:30px;height:30px;margin-top:3px;"></a>';

    $buddy_social_twitter = '<a class="new-window social foundicon-twitter" href="http://twitter.com/share?text='.$activity_title.'&url=' . $activity_link . '" rel="twitter"><img src='.WP_PLUGIN_URL.'/buddypress/bp-core/images/twitter.png style="width:30px;height:30px;margin-top:3px;"></a>';

    $buddy_social_google = '<a class="new-window social foundicon-google-plus" href="https://plus.google.com/share?url=' . $activity_link . '" rel="google-plus"><img src='.WP_PLUGIN_URL.'/buddypress/bp-core/images/google.png style="width:30px;height:30px;margin-top:3px;"></a>';

    $buddy_social_email = '<a class="general foundicon-mail" href="mailto:?body='.$activity_title .' ' . $activity_link . '" rel="nofollow"><img src='.WP_PLUGIN_URL.'/buddypress/bp-core/images/email.png style="width:30px;height:30px;margin-top:3px;"></a>';


    ?>
    <span class="bp-social-button">
		<a class="button item-button bp-secondary-action buddypress-social-button" rel="nofollow"  title="Send this to friends or post it on a timeline">Share</a>
	</span>
    
    <div class="social-buttons <?php $activity_type ?>" style="display: none;clear: left;">

            <?php if(get_option('social_button_facebook')==1) echo "$buddy_social_facebook"; ?>
            <?php if(get_option('social_button_twitter')==1) echo "$buddy_social_twitter"; ?>
            <?php if(get_option('social_button_google')==1) echo "$buddy_social_google"; ?>
            <?php if(get_option('social_button_email')==1) echo "$buddy_social_email"; ?>
            <?php
            $actvi = bp_get_activity_id();
            if(is_user_logged_in()) { ?>
			
                <a id="<?php bp_activity_id(); ?>" class="button acomment-reply bp-primary-action" onclick="share_activity(id, id, jQuery('#txtarea-<?php echo bp_activity_id(); ?>').val());" style="background: none !important; float: left; height: 30px; width: 30px;">
                        <img src='<?php echo WP_PLUGIN_URL ?>/buddypress/bp-core/images/dremboard.png'>
                </a>

            <textarea id="txtarea-<?php echo bp_activity_id(); ?>" style="display:none"><?php echo $desc; ?></textarea>
			<?php } ?>
            
    </div>

    <?php
}
add_action('bp_activity_entry_meta', 'buddy_social_button_activity_filter', 999);

?>
