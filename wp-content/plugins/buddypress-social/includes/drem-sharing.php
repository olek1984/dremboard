<?php



function buddy_social_button_drem_filter($links,$title, $fb_links) { 



    //$activity_type = bp_get_activity_type();

    $activity_type = '';

    //$activity_link = bp_get_activity_thread_permalink();
    
    //var_dump($fb_links);
    $activity_link = $links;

    //$activity_title = bp_get_activity_feed_item_title();

    $activity_title = $title;

    $plugin_path = plugins_url();

    //$buddy_social_facebook = '<a class="new-window social foundicon-facebook" href="https://www.facebook.com/sharer/sharer.php?t='.$activity_title.'&u=' . $activity_link . '" rel="facebox"><img src='.WP_PLUGIN_URL.'/buddypress/bp-core/images/facebook.png style="width:30px;height:30px;margin-top:3px;"></a>';
    $buddy_social_facebook = '<a class="new-window social foundicon-facebook" href="https://www.facebook.com/sharer/sharer.php?t='.$activity_title.'&u=' . $fb_links . '" rel="facebox"><img src='.WP_PLUGIN_URL.'/buddypress/bp-core/images/facebook.png style="width:30px;height:30px;margin-top:3px;"></a>';

    $buddy_social_twitter = '<a class="new-window social foundicon-twitter" href="http://twitter.com/share?text='.$activity_title.'&url=' . $activity_link . '" rel="twitter"><img src='.WP_PLUGIN_URL.'/buddypress/bp-core/images/twitter.png style="width:30px;height:30px;margin-top:3px;"></a>';

    $buddy_social_google = '<a class="new-window social foundicon-google-plus" href="https://plus.google.com/share?url=' . $activity_link . '" rel="google-plus"><img src='.WP_PLUGIN_URL.'/buddypress/bp-core/images/google.png style="width:30px;height:30px;margin-top:3px;"></a>';

    $buddy_social_email = '<a class="general foundicon-mail" href="mailto:?body='.$activity_title .' ' . $activity_link . '" rel="nofollow"><img src='.WP_PLUGIN_URL.'/buddypress/bp-core/images/email.png style="width:30px;height:30px;margin-top:3px;"></a>';





    ?>
<span class="bp-social-button">

<a class="rtmedia-comment-link item-button bp-secondary-action buddypress-social-button" rel="nofollow" title="Send this to friends or post it on a timeline">Share</a></span>
    

    <div class="social-buttons <?php $activity_type ?>" style="display: none;">

            <?php if(get_option('social_button_facebook')==1) echo "$buddy_social_facebook"; ?>

            <?php if(get_option('social_button_twitter')==1) echo "$buddy_social_twitter"; ?>

            <?php if(get_option('social_button_google')==1) echo "$buddy_social_google"; ?>

            <?php if(get_option('social_button_email')==1) echo "$buddy_social_email"; ?>

            <?php
            $link_href = home_url();
            $rid = rtmedia_activity_id(rtmedia_id());	
            global $rtmedia_media;
            if(isset($rtmedia_media->post_content)) {
                $desc = $rtmedia_media->post_content;
            } else {
                $post_details = get_post($rtmedia_media->media_id);
                $desc = $post_details->post_content;
            }
            if(is_user_logged_in()) { ?>
                <textarea id="txtarea-<?php echo rtmedia_id(); ?>" style="display:none"><?php echo $desc; ?></textarea>
                <a id="<?php bp_activity_id(); ?>" class="button bp-primary-action" onclick="share_activity_on_popup(<?php echo rtmedia_id(); ?>, <?php echo $rid; ?>, jQuery('#txtarea-<?php echo rtmedia_id(); ?>').val()); " style="background: none !important; float: left; margin-right: 4px; margin-top: 3px; height: 30px; width: 30px;border: none !important;">
                        <img src='<?php echo WP_PLUGIN_URL ?>/buddypress/bp-core/images/dremboard.png'>
                </a>
                <?php } ?>
    </div>
    <div id="hexd-<?php echo rtmedia_id(); ?>" class="hexd" style="display:none; color:#fff;background:#1E8022;padding:5px 10px 5px 10px;text-align: center; width:-webkit-calc(100% - 27px); width:-moz-calc(100% - 27px); width:100%; filter:alpha(opacity=70); opacity:0.7; -moz-opacity:0.7;"></div>

    <?php

}

//add_action('bp_activity_entry_meta', 'buddy_social_button_activity_filter', 999); 



?>