<div class="rtmedia-container rtmedia-single-container">
    <div class="row rtm-lightbox-container">
        <?php
        global $rt_ajax_request;
        do_action('rtmedia_before_media');
    global $rtmedia_query;
    //var_dump($rtmedia_query);
    $now_query = $rtmedia_query->media_query;
    $now_media_author = $now_query['media_author'];
 //var_dump($now_media_author);
 //var_dump($_SERVER);
        /*
//!!!!!!
		if (is_home())
		var_dump("123456");
		var_dump("55555");
		*/
        if ( have_rtmedia () ) : rtmedia ();
  /*      
    global $rtmedia_backbone;
var_dump("111");
var_dump($rtmedia_backbone);
var_dump("222");
        global $rtmedia_media;
var_dump($rtmedia_media);                
*/
            update_rtmedia_view_count();
            ?>
            <div id="rtmedia-single-media-container" class="rtmedia-single-media columns <?php echo ($rt_ajax_request) ? "large-8" : "large-12"; ?>">
                <?php if ( !$rt_ajax_request ) { ?>
                
                    <span class="rtmedia-media-title">
                        <?php 
                    		global $rtmedia_media;
                			if($rtmedia_media->media_type == 'photo') {
                				echo rtmedia_category ();
                			} else {
                				echo rtmedia_title ();
                			}
                        ?>
                    </span>
                    <div class="rtmedia-media" id ="rtmedia-media-<?php echo rtmedia_id (); ?>"><?php rtmedia_media ( true ); ?></div>
                    
                <?php } else { ?>
                    
                    <button class="mfp-arrow mfp-arrow-left mfp-prevent-close rtm-lightbox-arrows" type="button" title="Previous Media"></button>
                    <button class="mfp-arrow mfp-arrow-right mfp-prevent-close" type="button" title="Next Media"></button>
                    <!--author actions-->
                    <div class='rtm-ltb-title-container rt-clear'>
                        <h2 class='rtm-ltb-title'>
                            <a href="<?php echo rtmedia_permalink();?>" title="<?php echo rtmedia_title (); ?>">
                        <?php 
                    		global $rtmedia_media;
                			if($rtmedia_media->media_type == 'photo') {
                				echo rtmedia_category ();
                			} else {
                				echo rtmedia_title ();
                			}
                        ?>
                        </a>
                        </h2>
                        <div class='rtmedia-author-actions'>
                            <?php rtmedia_actions(); ?>
                        </div>
                    </div>
                    <div class="rtmedia-media" id ="rtmedia-media-<?php echo rtmedia_id (); ?>"><?php rtmedia_media ( true ); ?></div>

                    <div class='rtm-ltb-action-container rt-clear'>
                        <div class="rtm-ltb-gallery-title">
                        	<span class='ltb-title'>
                        	<?php 
                        		global $rtmedia_media;
                    			if($rtmedia_media->media_type == 'photo') {
                    				echo "Drēmed by Drēmer";
                    			} else {
                    				echo "Posted by Drēmer";
                    			}
                        	?>
                        	</span>
                        	<span class='media-index'></span><span class='total-medias'></span>
                        </div>
                        <div class="rtmedia-actions">
                            <?php do_action('rtmedia_action_buttons_after_media', rtmedia_id());?>
                        </div>
                    </div>
                <?php  } ?>
            </div>
            <div class="rtmedia-single-meta columns <?php echo ($rt_ajax_request) ? "large-4" : "large-12"; ?>">
                
                <?php if ( $rt_ajax_request ) { ?>
                
                    <div class="rtm-single-meta-contents<?php if(is_user_logged_in()) echo " logged-in"; ?>">
                        <div>
                            <div class="userprofile">
                                <?php rtmedia_author_profile_pic ( true ); ?>
                            </div>
                            <div class="username">
                                <?php rtmedia_author_name ( true ); ?>
                            </div>
                        </div>
                        <div class="rtm-time-privacy rt-clear">
                            <?php echo get_rtmedia_date_gmt();?> <?php echo get_rtmedia_privacy_symbol(); ?>
                        </div>

                        <div class="rtmedia-actions-before-description rt-clear">
                            <?php do_action('rtmedia_actions_before_description', rtmedia_id()) ;?>
                        </div>

                        <div class="rtmedia-media-description rtm-more">
                            <?php echo strip_tags(rtmedia_description ( $echo = false)); ?>
                            
                        </div>

                        <?php if ( rtmedia_comments_enabled () ) { ?>
                            <div class="rtmedia-item-comments row">
                                <div class="large-12 columns">
                                    <div class='rtmedia-actions-before-comments'>
										<style>
											#buddypress .rtmedia-actions-before-comments a{
												border: none !important;
												background: none !important;
											}
											.rtmedia-author-actions .unlike, .rtmedia-author-actions .like, 
											.rtmedia-actions .unlike, .rtmedia-actions .like {
												display: none !important;
											}
                                            .rtmedia-actions-before-comments span a.button {
                                                color: rgb(219, 74, 55)!important;
                                            }
										</style>
                                        <?php do_action('rtmedia_actions_before_comments'); ?>
                                        <?php if(is_user_logged_in ()) {?>
                                            <span>
                                                <a class="rtmedia-comment-link" title="Flag this active" onclick="flag_activity_on_popup(<?php echo rtmedia_activity_id();?>, <?php echo rtmedia_id();?>);" style="margin-left:10px !important;">Flag</a>
                                            </span>
                                            <span><a href='#' class='rtmedia-comment-link' style="margin-right:10px !important; margin-left: 10px !important;"><?php _e('Comment', 'rtmedia');?></a></span>
                                        <?php }?>
                                        <?php
                                        //!!!!!!
                                        //var_dump("1001");
                                        //$links = rtmedia_permalink();
                                        $links = get_rtmedia_permalink ( rtmedia_id ( ) );
                                        //var_dump("1002");
                                        $fb_links = $rtmedia_media->guid;
                                        buddy_social_button_drem_filter($links,rtmedia_title(),$fb_links);
                                        //var_dump("1003");
                                        ?>
                                    </div>
                                    <div class="rtm-like-comments-info">
                                        <?php show_rtmedia_like_counts(); ?>
                                        <div class="rtmedia-comments-container">
                                            <?php rtmedia_comments (); ?> 
                                        </div>
                                    </div>
                                </div>
                            </div>
					<?php
					//!!!!!!
					$now_content = rtmedia_title() . " ". rtmedia_description ( $echo = false);
					//var_dump("10001");
					//var_dump($now_content);
					releated_drem(5,$now_content); 
					//var_dump("12365");
					?>                            
                        <?php } ?>
                    </div>

                    <?php if ( rtmedia_comments_enabled () && is_user_logged_in ()) { ?>
                        <div class='rtm-media-single-comments'>
                            <?php rtmedia_comment_form (); ?>
                        </div>
                    <?php } ?>
                <?php } else { // else for if ( $rt_ajax_request )?>

                <div class="rtmedia-item-actions rt-clear">
		    <?php do_action('rtmedia_actions_without_lightbox'); ?>
                    <?php rtmedia_actions (); ?>
		</div>
		<div class="rtmedia-actions-before-description rt-clear">
                    <?php do_action('rtmedia_actions_before_description', rtmedia_id()) ;?>
                </div>

                <div class="rtmedia-media-description more">
                    <?php rtmedia_description (); ?>
                </div>

                <?php if ( rtmedia_comments_enabled () ) { ?>
                    <div class="rtmedia-item-comments row">
                        <div class="large-12 columns">
                            <div class='rtmedia-actions-before-comments'>
									<style>
										.rtmedia-actions-before-comments a{
											border: none !important;
										}
										.rtmedia-author-actions .like {
											display: none !important;
										}
									</style>
                                    <?php do_action('rtmedia_actions_before_comments'); ?>
                                    <?php if(is_user_logged_in ()) {?>
                                        <span>
                                            <a class="rtmedia-comment-link" title="Flag this active" onclick="flag_activity_on_popup(<?php echo rtmedia_activity_id();?>, <?php echo rtmedia_id();?>);" style="margin-left:10px !important;">Flag</a>
                                        </span>
                                        <span><a href='#' class='rtmedia-comment-link' style="margin-left:10px!important; margin-right:10px!important; "><?php _e('Comment', 'rtmedia');?></a></span>
                                    <?php }?>
                                        <?php
                                        //!!!!!!
                                        //var_dump("1001");
                                        //$links = rtmedia_permalink();
                                        $links = get_rtmedia_permalink ( rtmedia_id ( ) );
                                        $fb_links = $rtmedia_media->guid;
                                        //var_dump("1002");
                                        buddy_social_button_drem_filter($links,rtmedia_title(), $fb_links);
                                        //var_dump("1003");
                                        ?>                                    
                                </div>
                                <div class="rtm-like-comments-info">
                                    <?php show_rtmedia_like_counts(); ?>
                                    <div class="rtmedia-comments-container">
                                        <?php rtmedia_comments (); ?>
                                    </div>
                                </div>
                            <?php if(is_user_logged_in ()) { rtmedia_comment_form (); } ?>
                        </div>
                    </div>

                <?php } ?>
                <?php } ?>
            </div>

        <?php else: ?>
            <p><?php 
                $message = __ ( "Sorry !! There's no media found for the request !!", "rtmedia" );
                echo apply_filters('rtmedia_no_media_found_message_filter', $message);
                ?>
            </p>
        <?php endif; ?>

       <?php do_action('rtmedia_after_media'); ?>
    </div>
</div>
<script>
    ready_for_share_on_pop_up();
    ready_for_flag_on_pop_up();
</script>