<?php
/** That's all, stop editing from here * */
global $rtmedia_backbone, $wpdb;

//var_dump($_GET);
//var_dump("7006");
$rtmedia_backbone = array(
    'backbone' => false,
    'is_album' => false,
    'is_edit_allowed' => false
);
if (isset($_POST['backbone']))
    $rtmedia_backbone['backbone'] = $_POST['backbone'];
if (isset($_POST['is_album']))
    $rtmedia_backbone['is_album'] = $_POST['is_album'][0];
if (isset($_POST['is_edit_allowed']))
    $rtmedia_backbone['is_edit_allowed'] = $_POST['is_edit_allowed'][0];

$media = get_query_var('media');
$pagename = get_query_var('pagename');
$is_mobile = wp_is_mobile();

//if ($pagename == 'memories' || $media == 'video') {
if ($pagename == 'dremcast' || $media == 'video') {
    echo '<li class="rtmedia-list-item opo has-author media-type-video">';
    ?>
    <div class="rtmedia-item-author">
        <a href='<?php rtmedia_author_link(); ?>'>
            <div class="avatar">
                <img src="<?php rtmedia_author_avatar(); ?>" width="45"/>
            </div>
            <?php rtmedia_author_name(); ?>
        </a>
    </div>
    <?php
} else {
    echo '<li class="rtmedia-list-item opo">';
}
?>

<div id="hexd-<?php echo rtmedia_id(); ?>" class="hexd drem"></div>

<div class="" id="<?php echo rtmedia_id(); ?>">
    <?php do_action('rtmedia_before_item'); ?>
    <?php
    if ($pagename == 'drems' || $media == 'photo' || $rtmedia_backbone['backbone'] || isset($rtmedia_query->media_query['album_id'])):
        ?>
        <div class="rtmedia-item-category">
            <?php echo rtmedia_category(); ?>
        </div>
    <?php endif; ?>

    <?php
    if ($is_mobile) {
        ?>
        <a href ="<?php echo home_url(bp_get_activity_root_slug() . '/') . rtmedia_activity_id(rtmedia_id()); ?>" title="<?php echo rtmedia_title(); ?>">
            <?php
        } else {
            ?>
            <a class="mfp-popup" href ="<?php rtmedia_permalink(); ?>" title="<?php echo rtmedia_title(); ?>">
            <?php
            }
            ?>
            <div class="rtmedia-item-thumbnail">
                <!--<?php //if ($pagename == 'memories' || $media == 'video'): ?>-->
                <?php if ($pagename == 'dremcast' || $media == 'video'): ?>
                <div class="rtmedia-item-view-count">
                    <span><?php rtmedia_view_count(); ?> views</span>
                </div>
                <?php endif;?>
<!--                <img src="<?php rtmedia_image("rt_media_thumbnail"); ?>" alt="<?php rtmedia_image_alt(); ?>" >-->

                <div class="rtmedia-media" id ="rtmedia-media-<?php echo rtmedia_id(); ?>" style="display: inline-block;vertical-align: middle;text-align: center;width: auto;">
                    <?php rtmedia_media(true); ?>
                </div>
            </div>
        </a>
        <?php
        //if ($pagename == 'memories' || $media == 'video')
        if ($pagename == 'dremcast' || $media == 'video')
            echo '<div class="rtmedia-item-title memories">';
        else
            echo '<div class="rtmedia-item-title">';
        ?>
        <h4 title="<?php echo rtmedia_title(); ?>">


            <?php
            //!!!!!! echo rtmedia_title(); 
            $my_title_now = rtmedia_title();
            $my_title_now = str_ireplace('.jpg', '', $my_title_now);
            $my_title_now = str_ireplace('.jpeg', '', $my_title_now);
            $my_title_now = str_ireplace('.png', '', $my_title_now);
            $my_title_now = str_ireplace('.gif', '', $my_title_now);
            echo $my_title_now;
            ?>

        </h4>
</div>

<?php do_action('rtmedia_after_item'); ?>
<?php
//$fbl = '<div class="fb-like" data-href="http://dremboard.com/drem/" data-layout="button" data-action="like" data-show-faces="true" data-share="false"></div><div style="display: block;" class="social-buttons "><a rel="facebox" href="https://www.facebook.com/sharer/sharer.php?t='.rtmedia_title().'&amp;u='.get_rtmedia_permalink ( rtmedia_id() ).'" class="new-window social foundicon-facebook"></a><a rel="twitter" href="http://twitter.com/share?text='.rtmedia_title().'&amp;url='.get_rtmedia_permalink ( rtmedia_id() ).'" class="new-window social foundicon-twitter"></a><a rel="google-plus" href="https://plus.google.com/share?url='.get_rtmedia_permalink ( rtmedia_id() ).'" class="new-window social foundicon-google-plus"></a><a rel="nofollow" href="mailto:?body='.rtmedia_title().' '.get_rtmedia_permalink ( rtmedia_id() ).'" class="general foundicon-mail"></a></div>';
?>


<?php
//!!!!!
$current_item_id = rtmedia_id();
//var_dump($_SERVER['REQUEST_URI']);
$nowUI = $_SERVER['REQUEST_URI'];
$is_mobile = wp_is_mobile();
$is_mobile = ($is_mobile) ? "true" : "false";
//if ((stripos($nowUI, 'wp-admin/admin-ajax.php') === false) && (stripos($nowUI, 'memories') === false)) {
//if ((stripos($nowUI, 'wp-admin/admin-ajax.php') === false) && (stripos($nowUI, 'dremcast') === false)) {
if ((stripos($nowUI, 'wp-admin/admin-ajax.php') === false) && ((stripos($nowUI, 'dremcast') === false) && (stripos($nowUI, 'media/video') === false))) {
    if (is_user_logged_in()) {
        $user_ID = get_current_user_id();
        //SELECT * FROM `wp_rt_rtm_media` WHERE media_author=12 ORDER BY `id` DESC 
        $wert = "SELECT * FROM " . $wpdb->prefix . "rt_rtm_media WHERE media_author=" . $user_ID . " ORDER BY `id` DESC";
        $aws = $wpdb->get_results($wert);
        foreach ($aws as $fr) {
            $arrt[] = $fr->id;
        }
        $comma = implode(",", $array);
        $pid = rtmedia_id();
//                if (in_array($pid, $arrt)) {
        //$link_href = 	bp_loggedin_user_domain() . 'media/photo/dreamboard' ;
        //$link_href = 	bp_loggedin_user_domain() . "media/photo/?dremboard=$current_item_id" ;
        $link_href = bp_loggedin_user_domain() . "media/photo/?dremboard=$current_item_id";
        //. bp_get_familys_slug() . '/add-family/' . $potential_family_id . '/', 'familys_add_family' ),
        echo '<script type="text/javascript">';
        echo "toolTips('#$current_item_id','Add to my <a href=\"$link_href?keepThis=true&TB_iframe=true&height=600&width=500\" class=\"thickbox\" alt=\"Add to my drēmboard\">drēmboard</a>', {$is_mobile});";
        echo '</script>';
//                }
    } else {
        $link_href = get_option('siteurl') . '/login';
        echo '<script type="text/javascript">';
        echo "toolTips('#$current_item_id','<a href=\"$link_href\" target=\"_blank\">Login</a> to add in my drēmboard', {$is_mobile});";
        echo '</script>';
    }
} else {
    //echo '<script type="text/javascript">';
    //echo 'alert(111);';
    //echo '</script>';
}
?>
</div>
<div class="activity-btn" style="">
    <?php
    $sq = "SELECT * FROM " . $wpdb->prefix . "bp_activity_meta WHERE meta_key='favorite_count' ORDER BY `id` DESC";
    $res = $wpdb->get_results($sq);
    foreach ($res as $rt) {
        $ert[] = $rt->activity_id;
        $mval = $rt->meta_value;
        $erth[] = $rt->activity_id . "-" . $rt->meta_value;
    }

    $rid = rtmedia_activity_id(rtmedia_id());
    $ridd = $rid . "-0";
    $favorite_link = apply_filters('bp_get_activity_favorite_link', wp_nonce_url(home_url(bp_get_activity_root_slug() . '/favorite/' . $rid . '/'), 'mark_favorite'));
    $unfavorite_link = apply_filters('bp_get_activity_unfavorite_link', wp_nonce_url(home_url(bp_get_activity_root_slug() . '/unfavorite/' . $rid . '/'), 'unmark_favorite'));
    $link_href = home_url();
    if (isset($rtmedia_media->post_content)) {
        $desc = $rtmedia_media->post_content;
    } else {
        $post_details = get_post($rtmedia_media->media_id);
        $desc = $post_details->post_content;
    }

    if ($rtmedia_backbone['backbone']):
        $favorite_link = apply_filters('bp_get_activity_favorite_link', wp_nonce_url(home_url(bp_get_activity_root_slug() . '/favorite/' . 'rid' . '/'), 'mark_favorite'));
        $unfavorite_link = apply_filters('bp_get_activity_unfavorite_link', wp_nonce_url(home_url(bp_get_activity_root_slug() . '/unfavorite/' . 'rid' . '/'), 'unmark_favorite'));
        $favorite_link = str_replace('rid', $rid, $favorite_link);
        $unfavorite_link = str_replace('rid', $rid, $unfavorite_link);
        ?>

        <div class="compare_group">
            <input class="compare_para rid" name="rid" type="hidden" value=<?php echo json_encode($rid); ?>>
            <input class="compare_para ridd" name="ridd" type="hidden" value=<?php echo json_encode($ridd); ?>>
            <input class="compare_para ert" name="ert" type="hidden" value=<?php echo json_encode($ert); ?>>
            <input class="compare_para erth" name="erth" type="hidden" value=<?php echo json_encode($erth); ?>>

            <div class="compare uncontain_rid_ert" >
                <div class="button fav bp-secondary-action" title="<?php esc_attr_e('Mark as Favorite', 'buddypress'); ?>" onclick="location.href = '<?php echo $favorite_link; ?>';"><?php _e('Favorite', 'buddypress'); ?></div>
            </div>
            <div class="compare contain_rid_ert contain_ridd_erth" >
                <div class="button fav bp-secondary-action" title="<?php esc_attr_e('Mark as Favorite', 'buddypress'); ?>" onclick="location.href = '<?php echo $favorite_link; ?>';"><?php _e('Favorite', 'buddypress'); ?></div>
            </div>
            <div class="compare contain_rid_ert uncontain_ridd_erth" >
                <div class="button unfav bp-secondary-action" title="<?php esc_attr_e('Remove Favorite', 'buddypress'); ?>" onclick="location.href = '<?php echo $unfavorite_link; ?>';"><?php _e('Remove Favorite', 'buddypress'); ?></div>
            </div>
            <div class="compare unzero_rid" >
                <span class="bp-social-button active">
                    <div id="<?php echo $rid; ?>" class="button item-button bp-secondary-action buddypress-social-button socialactive" title="Send this to friends or post it on a timeline" rel="nofollow" style="font-size: 12px;" onClick="socialactive(id);">Share</div>
                </span>
                <div style="display:none;margin-bottom:5px" class="social-buttons-<?php echo $rid; ?>">
                    <textarea id="txtarea-<?php echo rtmedia_id(); ?>" style="display:none"><?php echo $desc; ?></textarea>
                    <div rel="share-on-timeline" id="<?php echo rtmedia_id(); ?>" class="social foundicon-shareontimeline" onclick="share_activity(<?php echo rtmedia_id(); ?>, <?php echo $rid; ?>, jQuery('#txtarea-<?php echo rtmedia_id(); ?>').val());" style="cursor:pointer">
                        <img src='<?php echo WP_PLUGIN_URL ?>/buddypress/bp-core/images/dremboard.png' style="width:20px;height:20px;">
                    </div>
                    <div rel="facebox" href="https://www.facebook.com/sharer/sharer.php?t=&amp;u=<?php echo home_url(bp_get_activity_root_slug() . '/' . $rid . '/'); ?>" class="new-window 1 social foundicon-facebook" onclick='window.open("https://www.facebook.com/sharer/sharer.php?t=&amp;u=<?php echo home_url(bp_get_activity_root_slug() . '/' . $rid . '/'); ?>", "facebook", "width=700, height=500, toolbar=no, scrollbars=yes");'>
                        <img src='<?php echo WP_PLUGIN_URL ?>/buddypress/bp-core/images/facebook.png' style="width:20px;height:20px;">
                    </div>
                    <div rel="twitter" class="new-window social foundicon-twitter" onclick='window.open("http://twitter.com/share?text=&amp;url=<?php echo home_url(bp_get_activity_root_slug() . '/' . $rid . '/'); ?>", "twitter", "width=700, height=500, toolbar=no, scrollbars=yes");'>
                        <img src='<?php echo WP_PLUGIN_URL ?>/buddypress/bp-core/images/twitter.png' style="width:20px;height:20px;">
                    </div>
                    <div rel="google-plus" class="new-window social foundicon-google-plus" onclick='window.open("https://plus.google.com/share?url=<?php echo home_url(bp_get_activity_root_slug() . '/' . $rid . '/'); ?>", "google-plus", "width=700, height=500, toolbar=no, scrollbars=yes");'>
                        <img src='<?php echo WP_PLUGIN_URL ?>/buddypress/bp-core/images/google.png' style="width:20px;height:20px;">
                    </div>
                    <div rel="nofollow" class="general foundicon-mail" onclick='window.open("mailto:?body=<?php echo home_url(bp_get_activity_root_slug() . '/' . $rid . '/'); ?>", "mail", "width=700, height=500, toolbar=no, scrollbars=yes");'>
                        <img src='<?php echo WP_PLUGIN_URL ?>/buddypress/bp-core/images/email.png' style="width:20px;height:20px;">
                    </div>

                </div>
                
                <?php do_action('bp_activity_entry_share');?>
                <span class="bp-flag-button active">
                    <div class="button item-button" title = "Flag this active" onClick="flag_activity(<?php echo rtmedia_activity_id();?>, <?php echo rtmedia_id(); ?>);">Flag</div>
                </span >
                <?php rtmedia_cover_button();
                ?>
            </div>
        </div>
        <?php
    endif;

    if (!in_array($rid, $ert) && $rid != 0) {
        ?>
        <div class="button fav bp-secondary-action" title="<?php esc_attr_e('Mark as Favorite', 'buddypress'); ?>" onclick="location.href = '<?php echo $favorite_link; ?>';"><?php _e('Favorite', 'buddypress'); ?></div>
        <?php
    } else if (in_array($rid, $ert)) {
        if (in_array($ridd, $erth)) {
            ?>
            <div class="button fav bp-secondary-action" title="<?php esc_attr_e('Mark as Favorite', 'buddypress'); ?>" onclick="location.href = '<?php echo $favorite_link; ?>';"><?php _e('Favorite', 'buddypress'); ?></div>
            <?php
        } else {
            ?>
            <div class="button unfav bp-secondary-action" title="<?php esc_attr_e('Remove Favorite', 'buddypress'); ?>" onclick="location.href = '<?php echo $unfavorite_link; ?>';"><?php _e('Remove Favorite', 'buddypress'); ?></div>
            <?php
        }
    }

    if ($rid != 0):
        if (isset($rtmedia_media->post_content)) {
            $desc = $rtmedia_media->post_content;
        } else {
            $post_details = get_post($rtmedia_media->media_id);
            $desc = $post_details->post_content;
        }
        ?>
            
        <span class="bp-social-button active">
            <div id="<?php echo $rid; ?>" class="button item-button bp-secondary-action buddypress-social-button socialactive" title="Send this to friends or post it on a timeline" rel="nofollow" style="font-size: 12px;" onClick="socialactive(id);">Share</div>
        </span>
        <div style="display:none;margin-bottom:5px" class="social-buttons-<?php echo $rid; ?>">
            <textarea id="txtarea-<?php echo rtmedia_id(); ?>" style="display:none"><?php echo $desc; ?></textarea>
            <div rel="share-on-timeline" id="<?php echo rtmedia_id(); ?>" class="social foundicon-shareontimeline" onclick="share_activity(<?php echo rtmedia_id(); ?>, <?php echo $rid; ?>, jQuery('#txtarea-<?php echo rtmedia_id(); ?>').val());" style="cursor:pointer">
                <img src='<?php echo WP_PLUGIN_URL ?>/buddypress/bp-core/images/dremboard.png' style="width:20px;height:20px;">
            </div>
            <div rel="facebox" href="https://www.facebook.com/sharer/sharer.php?t=&amp;u=<?php echo home_url(bp_get_activity_root_slug() . '/' . $rid . '/'); ?>" class="new-window 1 social foundicon-facebook" onclick='window.open("https://www.facebook.com/sharer/sharer.php?t=&amp;u=<?php if ($rtmedia_media->media_type == 'photo') {$src = wp_get_attachment_image_src($rtmedia_media->media_id, 'rt_media_single_image'); echo $src[0];} elseif ($rtmedia_media->media_type == 'video'){echo wp_get_attachment_url ( $rtmedia_media->media_id );}elseif ($rtmedia_media->media_type == 'music'){echo wp_get_attachment_url ( $rtmedia_media->media_id );}?>", "facebook", "width=700, height=500, toolbar=no, scrollbars=yes");'>
                <img src='<?php echo WP_PLUGIN_URL ?>/buddypress/bp-core/images/facebook.png' style="width:20px;height:20px;">
            </div>
            <div rel="twitter" class="new-window social foundicon-twitter" onclick='window.open("http://twitter.com/share?text=&amp;url=<?php echo home_url(bp_get_activity_root_slug() . '/' . $rid . '/'); ?>", "twitter", "width=700, height=500, toolbar=no, scrollbars=yes");'>
                <img src='<?php echo WP_PLUGIN_URL ?>/buddypress/bp-core/images/twitter.png' style="width:20px;height:20px;">
            </div>
            <div rel="google-plus" class="new-window social foundicon-google-plus" onclick='window.open("https://plus.google.com/share?url=<?php echo home_url(bp_get_activity_root_slug() . '/' . $rid . '/'); ?>", "google-plus", "width=700, height=500, toolbar=no, scrollbars=yes");'>
                <img src='<?php echo WP_PLUGIN_URL ?>/buddypress/bp-core/images/google.png' style="width:20px;height:20px;">
            </div>
            <div rel="nofollow" class="general foundicon-mail" onclick='window.open("mailto:?body=<?php echo home_url(bp_get_activity_root_slug() . '/' . $rid . '/'); ?>", "mail", "width=700, height=500, toolbar=no, scrollbars=yes");'>
                <img src='<?php echo WP_PLUGIN_URL ?>/buddypress/bp-core/images/email.png' style="width:20px;height:20px;">
            </div>

        </div>
        <?php do_action('bp_activity_entry_share');?>
        <span class="bp-flag-button active">
            <div class="button item-button" title = "Flag this active" onClick="flag_activity(<?php echo rtmedia_activity_id();?>, <?php echo rtmedia_id(); ?>);">Flag</div>
        </span >
        <?php rtmedia_cover_button();?>
    <?php endif; ?>
</div>

</li>