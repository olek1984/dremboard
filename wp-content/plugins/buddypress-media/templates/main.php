<?php
/* * **************************************
 * Main.php
 *
 * The main template file, that loads the header, footer and sidebar
 * apart from loading the appropriate rtMedia template
 * *************************************** */
// by default it is not an ajax request
global $rt_ajax_request ;
$rt_ajax_request = false ;

// check if it is an ajax request
if ( ! empty ( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) &&
        strtolower ( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) == 'xmlhttprequest'
 ) {
    $rt_ajax_request = true ;
}
?>
<div id="buddypress">
    <?php
//if it's not an ajax request, load headers
    if ( ! $rt_ajax_request ) {
        // if this is a BuddyPress page, set template type to
        // buddypress to load appropriate headers
        if ( class_exists ( 'BuddyPress' ) && ! bp_is_blog_page () ) {
            $template_type = 'buddypress' ;
        }
        else {
            $template_type = '' ;
        }
        //get_header( $template_type );

        if ( $template_type == 'buddypress' ) {
            //load buddypress markup
			$request_url = $_SERVER['REQUEST_URI'];
			$show_4row = false;
            if (preg_match('/.*\/show\//', $request_url))
            	$show_4row = true;
            
            if (preg_match('/.*\/single\//', $request_url))
            	$show_single = true;
            
            if ($show_4row){
      ?>
	<style type="text/css">
		section {
			width: 100%!important;
		}
		#gk-sidebar{
			display: none;
		}
	</style>
      <?php
            }
            if ($show_single){
      ?>
	<style type="text/css">
		#item-header,
        #item-nav,
        .item-list-tabs{
			display: none;
		}
	</style>
      <?php
            }
            
            $is_mobile = wp_is_mobile();
            if ( bp_displayed_user_id () && !$show_4row) {
            	/*if ( (is_user_logged_in()) && (bp_displayed_user_id() == get_current_user_id ())) {*/
            		do_action( 'bp_before_member_home_content' ); ?>

			<div id="item-header" role="complementary">

			<?php do_action( 'bp_before_member_header' ); ?>

			<div id="item-header-avatar">
				<a href="<?php bp_displayed_user_link(); ?>">

					<?php bp_displayed_user_avatar( 'type=full' ); ?>

				</a>
			</div><!-- #item-header-avatar -->

			<div id="item-header-content">

				<h2>
					<a href="<?php bp_displayed_user_link(); ?>"><?php bp_displayed_user_fullname(); ?></a>
				</h2>

				<?php if ( bp_is_active( 'activity' ) && bp_activity_do_mentions() ) : ?>
					<span class="user-nicename">@<?php bp_displayed_user_mentionname(); ?></span>
				<?php endif; ?>

				<span class="activity"><?php bp_last_activity( bp_displayed_user_id() ); ?></span>

				<?php do_action( 'bp_before_member_header_meta' ); ?>

				<div id="item-meta">

					<?php if ( bp_is_active( 'activity' ) ) : ?>

						<div id="latest-update">

							<?php bp_activity_latest_update( bp_displayed_user_id() ); ?>

						</div>

					<?php endif; ?>

					<div id="item-buttons">

						<?php do_action( 'bp_member_header_actions' ); ?>

					</div><!-- #item-buttons -->

					<?php
					/***
					 * If you'd like to show specific profile fields here use:
					 * bp_member_profile_data( 'field=About Me' ); -- Pass the name of the field
					 */
					 do_action( 'bp_profile_header_meta' );

					 ?>

				</div><!-- #item-meta -->

			</div><!-- #item-header-content -->

			<?php do_action( 'bp_after_member_header' ); ?>

			<?php do_action( 'template_notices' ); ?>

			</div><!-- #item-header -->

			<div id="item-nav">
				<div class="item-list-tabs no-ajax <?php if($is_mobile)echo 'mobile mobile_sub_menu'; ?>" id="object-nav" role="navigation">
					<?php if( $is_mobile ): ?>
					<ul class="has_sub">
						<li class="has_sub"><a onclick="javascript:;">
							<?php bp_get_selected_user_nav_name(); ?>
						</a></li>
					</ul>
					<?php endif; ?>
					<ul class="sub">
						<?php bp_get_displayed_user_nav(); ?>
						<?php do_action( 'bp_member_options_nav' ); ?>
					</ul>
				</div>
			</div><!-- #item-nav -->
                <div id="item-body">

                    <?php do_action ( 'bp_before_member_body' ) ; ?>
                    <?php do_action ( 'bp_before_member_media' ) ; ?>
                    <div class="item-list-tabs no-ajax <?php if($is_mobile)echo 'mobile mobile_sub_menu'; ?>" id="subnav">
                        <?php if( $is_mobile ): ?>
						<ul class="has_sub">
							<li class="has_sub"><a onclick="javascript:;">
								<?php rtmedia_selected_sub_nav(); ?>
							</a></li>
						</ul>
						<?php endif; ?>
						<ul class="sub">

                            <?php rtmedia_sub_nav () ; ?>

                            <?php do_action ( 'rtmedia_sub_nav' ) ; ?>

                        </ul>
                    </div><!-- .item-list-tabs -->

                    <?php
                
			/*	}else{
                //if it is a buddypress member profile
                ?>

                <div id="item-body">

                    <?php do_action ( 'bp_before_member_body' ) ; ?>
                    <?php do_action ( 'bp_before_member_media' ) ; ?>
                    <?php
            }*/}
                else if ( bp_is_group () ) {

                    //not a member profile, but a group
                    ?>

                    <?php
                    if ( bp_has_groups () ) : while ( bp_groups () ) : bp_the_group () ;
                            ?>
                            <div id="item-header">

                                <?php bp_get_template_part ( 'groups/single/group-header' ) ; ?>

                            </div><!--#item-header-->

                            <div id="item-nav">
                                <div class="item-list-tabs no-ajax <?php if($is_mobile)echo 'mobile mobile_sub_menu'; ?>" id="object-nav" role="navigation">
									<?php if( $is_mobile ): ?>
									<ul class="has_sub">
										<li class="has_sub"><a onclick="javascript:;">
											<?php bp_get_selected_options_nav_name(); ?>
										</a></li>
									</ul>
									<?php endif; ?>
									<ul class="sub">

                                        <?php bp_get_options_nav () ; ?>

                                        <?php do_action ( 'bp_group_options_nav' ) ; ?>

                                    </ul>
                                </div>
                            </div><!-- #item-nav -->


                            <div id="item-body">

                                <?php do_action ( 'bp_before_group_body' ) ; ?>
                                <?php do_action ( 'bp_before_group_media' ) ; ?>
                                <div class="item-list-tabs no-ajax <?php if($is_mobile)echo 'mobile mobile_sub_menu'; ?>" id="subnav">
			                        <?php if( $is_mobile ): ?>
									<ul class="has_sub">
										<li class="has_sub"><a onclick="javascript:;">
											<?php rtmedia_selected_sub_nav(); ?>
										</a></li>
									</ul>
									<?php endif; ?>
									<ul class="sub">

                                        <?php rtmedia_sub_nav () ; ?>

                                        <?php do_action ( 'rtmedia_sub_nav' ) ; ?>

                                    </ul>
                                </div><!-- .item-list-tabs -->
                                <?php
                            endwhile ;
                        endif ;
                    } // group/profile if/else
                    ?>
                <?php
            }else{ ////if BuddyPress
                ?>
                            <div id="item-body">
                            <?php
            }
        } // if ajax
        // include the right rtMedia template
        rtmedia_load_template () ;
?>
                            </div>
<?php
        
        if ( ! $rt_ajax_request ) {
            if ( function_exists ( "bp_displayed_user_id" ) && $template_type == 'buddypress' && (bp_displayed_user_id () || bp_is_group ()) ) {

                if ( bp_is_group () ) {
                    do_action ( 'bp_after_group_media' ) ;
                    do_action ( 'bp_after_group_body' ) ;
                }
                if ( bp_displayed_user_id () ) {
                    do_action ( 'bp_after_member_media' ) ;
                    do_action ( 'bp_after_member_body' ) ;
                }
            }
        }
        //close all markup
        ?>
    </div><!--#buddypress-->
            <?php
            //get_sidebar($template_type);
            //get_footer($template_type);
        // if ajax

        