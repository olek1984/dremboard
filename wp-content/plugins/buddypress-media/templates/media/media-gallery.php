<div class="rtmedia-container">
    <?php
//!!!!!
    global $rtmedia_media;
    global $wpdb;

//var_dump("7008");
//var_dump($this);
//var_dump($rtmedia_media);
//var_dump("7009");
//var_dump($rtmedia_query);
    global $wpdb;
    $currentuser = bp_loggedin_user_id();
    if (isset($_GET['dremboard']) && (!isset($_POST['submitDremToDremboard']))) {
        //var_dump("7010");

        if (empty($currentuser))
            return;
        if (bp_loggedin_user_id() != bp_displayed_user_id())
            return;
        ?>
        <style type="text/css">
            *html, html, body
            {
                /* margin:0px !important; */
                overflow:hidden !important;
                min-height: 2% !important;
                margin-bottom:0px !important;
                background: #FFF !important;
            }
            #gk-top, #item-header, #item-nav, #subnav, #gk-sidebar, #gk-footer, #gk-style-area, #wpadminbar
            {
                display:none !important;
            }
            #rtm-media-options-list.click-nav
            {
                float:none !important;
            }
        </style>

        <?php
        $newmodel = new RTMediaModel();
        $have_dremboard = $newmodel->get_user_dremboards($currentuser);

        $dremboard_href = bp_loggedin_user_domain() . 'media/dremboard';

        if (count($have_dremboard) == 0) {
            echo "Sorry, you did not creat any drēmboard yet, please <a href='$dremboard_href' target='_blank'>creat a drēmboard</a> first, thanks.<br />";
        } else {
            echo "<p style='margin-bottom:30px;'>Please choose a drēmboard to add this drēm in:</p>";
            ?>
            <form method="POST">
				<style>
					#drupalchat-wrapper {
						display: none;
					}
					.clicker.rtmedia-action-buttons {
						display: inherit !important;
					}
				</style>
                <select  style="margin-bottom:30px;" name="selectAddDremToDremboard">
		        <?php
		        //<select  style="float:left; margin-right:20px;" name="selectAddDremToDremboard">
		        $dbCount = 0;
		        foreach ($have_dremboard as $dremboard_now) {
		            $dbTitleArr[$dbCount] = $dremboard_now->media_title;
		            $dbIdArr[$dbCount] = $dremboard_now->id;
		            $dbCount++;
		        }
		        for($i=0;$i<$dbCount;$i++)
		        	for($j=$i+1;$j<$dbCount;$j++)
		        {
		        	if($dbTitleArr[$i] > $dbTitleArr[$j]) {
		        		$swapTitle = $dbTitleArr[$i]; $dbTitleArr[$i] = $dbTitleArr[$j]; $dbTitleArr[$j] = $swapTitle;
		        		$swapId = $dbIdArr[$i]; $dbIdArr[$i] = $dbIdArr[$j]; $dbIdArr[$j] = $swapId;
		        	}
		        }
		        for($i=0; $i<$dbCount; $i++) {
		            echo "<option value='$dbIdArr[$i]'>".$dbTitleArr[$i]."</option>";
		        }
		        ?>
                </select>

        <?php
        //!!!!!!!
        //echo "<p style='margin:20px 0px;'><h4>Or click creat a dremboard for add this drem in:</h4></p>";
        //var_dump("123");
//!!!!!!!
        {
            global $rtmedia_query;
            if (is_rtmedia_album_enable() && !( isset($rtmedia_query->is_gallery_shortcode) && $rtmedia_query->is_gallery_shortcode == true) && isset($rtmedia_query->query['context_id']) && isset($rtmedia_query->query['context'])) {
                ?>
                        <div class="mfp-hide rtmedia-popup rtmedia-create-dremboard-modal" id="rtmedia-create-album-modal">
                            <div  id="rtm-modal-container">
                                <h2 class="rtm-modal-title"><?php
                //!!! _e('Create New Album', 'rtmedia'); 
                /*
                  if ( is_rtmedia_album_gallery() )
                  {
                  _e('Create New Memory', 'rtmedia');
                  }
                  if (is_rtmedia_dremboard_gallery())
                  {
                  _e('Create New Dremboard', 'rtmedia');
                  }
                 */
                //!!!!! _e('Create New Memory', 'rtmedia'); 
                _e('Create New Drēmboard', 'rtmedia');
                ?></h2>
                                    <?php //!!!!!  ?>
                                </p>
                                <label for="rtmedia_album_name"><?php
                    //!!!��_e('Album Title : ', 'rtmedia');
                    _e('Title: ', 'rtmedia');
                                    ?></label>
                                </p>
                                <input type="text" id="rtmedia_album_name" value=""  class="rtm-input-medium" />
                                <?php //!!!!!  ?>
                                <p><label for="rtmedia_album_name">Description:</label></p>
                                <textarea class="creat_album_des" name="creat_album_des" style="width: 55%;padding-top:10px !important;"></textarea>
                                <p><label for="rtmedia_album_name">Category: <i>(Choose from following Categories)</i></label></p>
                                <?php
                                //if (is_rtmedia_album ())
                                if (is_rtmedia_album_gallery()) {
                                    //var_dump("2016");
                                    $cats_all = get_category_children(5);
                                    //var_dump($cats_all);
                                    if ($cats_all) {
                                        $cats_all = ltrim($cats_all, "/");
                                        $cats_array = split("/", $cats_all);
                                        //var_dump($cats_array);
                                        //wp_category_checklist( 0, 0, false, $cats_array, null, true );
                                        global $table_prefix, $wpdb;
                                        $wpTableTerms = $table_prefix . "terms";
                                        $wpTableRelation = $table_prefix . "term_relationships";
                                        $wpTableTaxonomy = $table_prefix . "term_taxonomy";
                                        $resultGetCategory1 = $cats_array;
                                        if (sizeof($resultGetCategory1) > 0) {
                                            $wpTableTerms = $table_prefix . "terms";
                                            $wpTableRelation = $table_prefix . "term_relationships";
                                            $wpTableTaxonomy = $table_prefix . "term_taxonomy";

                                            echo '<select name="simplifyCategoryCheck" id="simplifyCategoryCheck">';

                                            foreach ($resultGetCategory1 as $tempCategory1) {
                                                //var_dump($tempCategory1);
                                                $sqlGetCategory2 = "SELECT * FROM `" . $wpTableTerms . "` WHERE `term_id` = '" . $tempCategory1 . "' LIMIT 1";
                                                $resultGetCategory2 = $wpdb->get_results($sqlGetCategory2, ARRAY_A);
                                                if (sizeof($resultGetCategory2) > 0) {
                                                    //echo "<input type='checkbox' id='simplifyCategoryCheck' name='simplifyCategoryCheck[]' value='".$resultGetCategory2[0]['term_id']."'>".$resultGetCategory2[0]['name']."  ";

                                                    echo '<option value="' . $resultGetCategory2[0]['term_id'] . '">';
                                                    echo $resultGetCategory2[0]['name'];
                                                    echo '</option>';

                                                    //echo "<input type='checkbox' id='simplifyCategoryCheck' name='simplifyCategoryCheck[]' value='".$resultGetCategory2[0]['term_id']."'>".$resultGetCategory2[0]['name']."  ";
                                                }
                                            }
                                            echo '</select>';
                                            echo '<input type="hidden" id="creat_album_dremboard_type" value="album">'; //!!!!!
                                        }
                                    }
                                    //wp_category_checklist( $post_id = 0, $descendants_and_self = 0, $selected_cats = false, $popular_cats = false, $walker = null, $checked_ontop = true )
                                }
                                //!!!!!!! if (is_rtmedia_dremboard_gallery()) 
                                {
                                    //var_dump("2017");
                                    //var_dump("2016");
                                    $cats_all = get_category_children(6);
                                    //var_dump($cats_all);
                                    if ($cats_all) {
                                        $cats_all = ltrim($cats_all, "/");
                                        $cats_array = split("/", $cats_all);
                                        //var_dump($cats_array);
                                        //wp_category_checklist( 0, 0, false, $cats_array, null, true );
                                        global $table_prefix, $wpdb;
                                        $wpTableTerms = $table_prefix . "terms";
                                        $wpTableRelation = $table_prefix . "term_relationships";
                                        $wpTableTaxonomy = $table_prefix . "term_taxonomy";
                                        $resultGetCategory1 = $cats_array;
                                        if (sizeof($resultGetCategory1) > 0) {
                                            $wpTableTerms = $table_prefix . "terms";
                                            $wpTableRelation = $table_prefix . "term_relationships";
                                            $wpTableTaxonomy = $table_prefix . "term_taxonomy";
                                            echo '<select name="simplifyCategoryCheck" id="simplifyCategoryCheck">';

                                            foreach ($resultGetCategory1 as $tempCategory1) {
                                                //var_dump($tempCategory1);
                                                $sqlGetCategory2 = "SELECT * FROM `" . $wpTableTerms . "` WHERE `term_id` = '" . $tempCategory1 . "' LIMIT 1";
                                                $resultGetCategory2 = $wpdb->get_results($sqlGetCategory2, ARRAY_A);
                                                if (sizeof($resultGetCategory2) > 0) {
                                                    //echo "<input type='checkbox' id='simplifyCategoryCheck' name='simplifyCategoryCheck[]' value='".$resultGetCategory2[0]['term_id']."'>".$resultGetCategory2[0]['name']."  ";
                                                    echo '<option value="' . $resultGetCategory2[0]['term_id'] . '">';
                                                    echo $resultGetCategory2[0]['name'];
                                                    echo '</option>';
                                                }
                                            }
                                            echo '</select>';
                                            echo '<input type="hidden" id="creat_album_dremboard_type" value="dremboard">'; //!!!!!
                                        }
                                    }
                                }
                                //get_category_children()
                                //wp_category_checklist();
                                ?>
                                </p>
                                <label for="rtmedia_album_name"><?php
                                //!!!��_e('Album Title : ', 'rtmedia');
			    				echo '<p>Drēmboard Privacy:</p>';
			    				echo "<input type='radio' class='rtmedia_select_dremboard_privacy' name='rtmedia_select_dremboard_privacy' value='PUBLIC' checked='checked'>Make it public drēmboard<br/>";
			    				echo "<input type='radio' class='rtmedia_select_dremboard_privacy' name='rtmedia_select_dremboard_privacy' value='PERSONAL'>Make it your personal drēmboard so only you can see<br/>";
			    				echo "<input type='radio' class='rtmedia_select_dremboard_privacy' name='rtmedia_select_dremboard_privacy' value='FAMILY'>Make it your family drēmboard so only your family can see<br/>";
			    				echo "<input type='radio' class='rtmedia_select_dremboard_privacy' name='rtmedia_select_dremboard_privacy' value='FRIEND'>Make it your friends drēmboard so only your friends can see<br/>";
                                ?></label>
                                </p>


                                <input type="hidden" id="rtmedia_album_context" value="<?php echo $rtmedia_query->query['context']; ?>">
                                <input type="hidden" id="rtmedia_album_context_id" value="<?php echo $rtmedia_query->query['context_id']; ?>">
                                <button type="button" id="rtmedia_create_new_album"><?php
                                    //!!! _e( "Create Album", "rtmedia" ); 
                                    /*
                                      if ( is_rtmedia_album_gallery() )
                                      {
                                      _e( "Create Memory", "rtmedia" );
                                      }

                                      if ( is_rtmedia_dremboard_gallery() )
                                      {
                                      _e( "Create Dremboard", "rtmedia" );
                                      } */
                                    _e("Create Drēmboard", "rtmedia");
                                    ?></button>
                                    <?php //!!!!! </p> ?>
                                    <?php do_action("rtmedia_add_album_privacy"); ?>
                            </div>
                        </div>

                                <?php
                                }
                            }
                            //rtmedia_author_actions (); 
                            //rtmedia_gallery_options(); 
                            //var_dump("456");
                            //!!!!!!!
                            {

                                $options_start = $options_end = $option_buttons = $output = "";
                                $options = array();
                                //$options = apply_filters('rtmedia_gallery_actions',$options );
                                //$options = rtmedia_create_album();
                                $options[] = "<a href='#rtmedia-create-album-modal' class='rtmedia-reveal-modal rtmedia-modal-link'  title='" . __('Create New Drēm', 'rtmedia') . "'><i class='rtmicon-plus-circle'></i>" . __('Add Drēmboard') . "</a>";
                                //!!!!!!! $options[] = "<a href='?type=image&TB_iframe=true&height=300&width=500' class='thickbox' target='_blank'  title='".  __( 'Create New Drem', 'rtmedia' ) ."'><i class='rtmicon-plus-circle'></i>" . __('Add Dremboard') . "</a>";
                                //var_dump($options);
                                if (!empty($options)) {

                                    $options_start = '<span class="click-nav" id="rtm-media-options-list">
                <span class="no-js">
                <span class="clicker rtmedia-action-buttons"><i class="rtmicon-cog">'. __(' Options', 'rtmedia') .'</i></span>
                <ul class="rtm-options">';
                                    foreach ($options as $action) {
                                        if ($action != "") {
                                            $option_buttons .= "<li>" . $action . "</li>";
                                        }
                                    }

                                    $options_end = "</ul></span></span>";

                                    if ($option_buttons != "") {
                                        $output = "<div style='margin:20px 0px;'>" . "Or you can create a new drēmboard first at here: " . $options_start . $option_buttons . $options_end . "</div>";
                                    }

                                    if ($output != "") {
                                        echo $output;
                                    }
                                }
                            }
                            //var_dump($_GET['dremboard']);
                            //var_dump($_SERVER);
                            $mygetdremboard = $_GET['dremboard'];
                            $mygetdremboard = str_replace("?", "", $mygetdremboard);
                            $mygetdremboard = str_replace("&", "", $mygetdremboard);
                            ?>

                <input type="hidden" name="hiddenDremIdToDremboard" value="<?php echo $mygetdremboard ?>">
                <div style="margin-top:20px;">
                    <input style="margin:20px 0px;" type="submit" name="submitDremToDremboard" value=" Add It ">
                </div>
            </form>
                <?php
            }
            //var_dump($have_dremboard);
            //get_all_dremboard($currentuser);
            ?>


    <?php
    return;
}

if (isset($_GET['dremboard']) && (isset($_POST['submitDremToDremboard']))) {
    //var_dump($_POST['hiddenDremIdToDremboard']);
    $dremid = $_POST['hiddenDremIdToDremboard'];
    $temp_RTMediaModel = new RTMediaModel();
    $now_media = $temp_RTMediaModel->get_media(array('id' => $dremid), 0, 1);
    //var_dump($now_media);
    if ((!(empty($now_media))) && (count($now_media) > 0)) {
        ?>
            <style type="text/css">
                *html, html, body
                {
                    /* margin:0px !important; */
                    overflow:hidden !important;
                    min-height: 2% !important;
                    margin-bottom:0px !important;
                    background: #FFF !important;
                }
                #gk-top, #item-header, #item-nav, #subnav, #gk-sidebar, #gk-footer, #gk-style-area, #wpadminbar
                {
                    display:none !important;
                }
                #rtm-media-options-list.click-nav
                {
                    float:none !important;
                }
            </style>

        <?php
        /*
          array(1) { [0]=> object(stdClass)#569 (21) { ["id"]=> string(2) "17" ["blog_id"]=> string(1) "1" ["media_id"]=> string(2) "44" ["media_author"]=> string(1) "1" ["media_title"]=> string(10) "Desert.jpg" ["album_id"]=> string(2) "13" ["media_type"]=> string(5) "photo" ["context"]=> string(7) "profile" ["context_id"]=> string(1) "1" ["source"]=> NULL ["source_id"]=> NULL ["activity_id"]=> string(2) "20" ["cover_art"]=> NULL ["privacy"]=> NULL ["views"]=> string(1) "0" ["downloads"]=> string(1) "0" ["ratings_total"]=> string(1) "0" ["ratings_count"]=> string(1) "0" ["ratings_average"]=> string(4) "0.00" ["likes"]=> string(1) "1" ["dislikes"]=> string(1) "0" } }
         */
        $post_id_now = $now_media[0]->media_id;
        //activity
        $media_activity_id = $now_media[0]->activity_id;

        //var_dump("7015");

        //$postid = 
        $dremboardid = $_POST['selectAddDremToDremboard'];

        $temp_RTMediaModel2 = new RTMediaModel();
        $now_dremboardid = $temp_RTMediaModel2->get_media(array('id' => $dremboardid), 0, 1);
        $partent_post_id = $now_dremboardid[0]->media_id;


        $post_details = get_post($post_id_now);
        //var_dump($post_details);

        $post_vars = array(
            //!!! 'post_title' => (empty ( $title )) ? __( 'Untitled Album', 'rtmedia' ) : $title,
            'post_author' => $currentuser,
            'post_date' => $post_details->post_date,
            'post_date_gmt' => $post_details->post_date_gmt,
            'post_content' => $post_details->post_content,
            'post_title' => $post_details->post_title,
            'post_excerpt' => $post_details->post_excerpt,
            'post_status' => $post_details->post_status,
            'comment_status' => $post_details->comment_status,
            'ping_status' => $post_details->ping_status,
            'post_password' => $post_details->post_password,
            'post_name' => $post_details->post_name,
            'to_ping' => $post_details->to_ping,
            'pinged' => $post_details->pinged,
            'post_modified' => $post_details->post_modified,
            'post_modified_gmt' => $post_details->post_modified_gmt,
            'post_content_filtered' => $post_details->post_content_filtered,
            'post_parent' => $partent_post_id,
            //'guid' => $post_details->guid,
            'post_type' => $post_details->post_type,
            'post_mime_type' => $post_details->post_mime_type
        );
        $file = get_post_meta($post_details->ID, '_wp_attached_file', true);
        //!!!!!!!! $file = ABSPATH."/wp-content/uploads/".$file;
        //$file = get_option('siteurl')."/wp-content/uploads/".$file;
        $file = str_replace('/home/dremboar/public_html//wp-content/uploads/', '', $file);
        update_option("90156", $post_vars);
        //var_dump("7017");
        //var_dump($post_vars);
        //$new_media_id = wp_insert_post($post_vars);
        //$new_media_id = wp_insert_attachment($post_vars, $file, $partent_post_id);
        update_option("90151", $file);
        //var_dump("7023");
        //var_dump($new_media_id);
        $new_blog_id = get_current_blog_id();
        $new_bmp_table = $wpdb->base_prefix . "rt_rtm_media";
        //var_dump("7020");
        $new_media_id = wp_insert_attachment($post_vars, $file, $partent_post_id);
        if ("$currentuser" != $now_media[0]->media_author)
        {
            $wpdb->insert(
                $new_bmp_table, array(
                'blog_id' => $new_blog_id,
    //            'media_id' => $media_id,
                'media_id' => $new_media_id,
                'media_type' => $now_media[0]->media_type,
                "context" => $now_media[0]->context,
                "context_id" => $currentuser,
                "activity_id" => $media_activity_id,
                "privacy" => '',
                "media_author" => $currentuser,
                "media_title" => $now_media[0]->media_title,
                "album_id" => $dremboardid,
                "likes" => 0,
                "source" => 'drems photo',
                "source_id" => $post_id_now
                    ), array('%d', '%d', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%d', '%d', '%s', '%d')
            );
            $last_id = $wpdb->insert_id;
            //var_dump($last_id);
            if ($last_id) {
                    $rtmedia_model = new RTMediaModel();
                    $category = $rtmedia_model->get_media_category( $post_id_now );
                    $rtmedia_model->set_media_category( $new_media_id, $category );
                    //$qur = "SELECT * FROM ".$wpdb->prefix."bp_activity WHERE id=$id";
                    echo "<p><h4>This drēm has been added to your drēmboard, thanks.</h4></p>";
            }
        }
        else
        {
            $wpdb->update(
                $new_bmp_table, array(
                    "album_id" => $dremboardid
                ), array("id" => $now_media[0]->id), array("%d"), array("%d")
            );
            echo "<p><h4>This drēm has been added to your drēmboard, thanks.</h4></p>";
        }
    }
    return;
}
?>

    <?php do_action('rtmedia_before_media_gallery'); ?>
    <?php
    $title = get_rtmedia_gallery_title();
    $dremboard_desc = get_rtmedia_dremboard_desc();
    $dremboard_author = get_rtmedia_dremboard_author();
    global $rtmedia_query;
    if (isset($rtmedia_query->is_gallery_shortcode) && $rtmedia_query->is_gallery_shortcode == true) { // if gallery is displayed using gallery shortcode
        ?>
        <h2><?php //!!!_e( 'Media Gallery', 'rtmedia' ); ?></h2>
    <?php } else {
        ?>
        <?php get_ps_search_form(); ?>
        <style type="text/css">
            .rtmedia-container ul.rtmedia-list.col4 li.rtmedia-list-item {
            	width: 25%;
            }
            .rtmedia-container ul.rtmedia-list.col3 li.rtmedia-list-item {
            	width: 25% !important;
            }
            #gk-sidebar
	        {
	        	<?php 
	        		if((is_user_logged_in()) && (bp_displayed_user_id() == get_current_user_id ())){
	        		}else{
	        		?>
	            //display:none !important;
	            <?php
	            	}
	            ?>
	        }
	        #gk-mainbody-columns > section {
	        	<?php 
	        		if((is_user_logged_in()) && (bp_displayed_user_id() == get_current_user_id ())){
	        		}else{
	        		?>
	            //width: 100% !important;
	            <?php
	            	}
	            ?>
	        	
	        	padding-right: 5px;
	        }
            #buddypress input[type=submit] {
            	color: #db4a37!important;
				background: #fff !important;
				border-color: #db4a37 !important;
				float: right;
				margin: 1px 5px 10px 5px;
            }
        </style>
        
        <div id="rtm-gallery-title-container">
            <h2 class="rtm-gallery-title">
        <?php if ($title) {
            echo $title;
        } else { /* //!!!_e( 'Media Gallery', 'rtmedia' ); */
        }
        ?>
            </h2>
        <?php  
            if( isset( $rtmedia_query->query['media_type'] ) &&  $rtmedia_query->query['media_type'] == "album"
        && isset( $rtmedia_query->media_query['album_id'] )  &&  $rtmedia_query->media_query['album_id'] != ""  ) {
                ?>
            <div class="rtm-media-gallery-author"><?php echo $dremboard_author; ?></div>
            <div class="rtm-media-gallery-description"><?php echo $dremboard_desc; ?></div>
            <div id="rtm-media-options"><?php do_action('rtmedia_media_gallery_actions'); ?></div>
        <?php
            }
            ?>    
        </div>
        <div id="rtm-media-gallery-uploader">
        <?php rtmedia_uploader(array('is_up_shortcode' => false)); ?>
        </div>
<?php }
?>
            <?php do_action('rtmedia_after_media_gallery_title'); ?>
            <?php if (have_rtmedia()) { ?>
		<ul class="rtmedia-list rtmedia-list-media <?php echo rtmedia_media_gallery_class(); ?> col3">
			
		</ul>
        <ul class="rtmedia-list rtmedia-list-media <?php echo rtmedia_media_gallery_class(); ?> col4">
            <style>
                a.button, div.button {
                    height: 22px;
                    margin: 3px 5px 0 0 ;
                    padding: 0px 3px 0 !important;
                    font-family: Arial, Helvetica, sans-serif !important;
                    font-size: 11px;
                    line-height: 22px;
                    text-transform: none !important;
                    color: #FFFFFF !important; 
                    font-size: 0.8rem;
                    outline: medium none;
                    text-align: center;
                    -webkit-border-radius: 0;
                    -moz-border-radius: 0;
                    border-radius: 0;
                    -webkit-transition: all .3s ease-out;
                    -moz-transition: all .3s ease-out;
                    -o-transition: all .3s ease-out;
                    transition: all .3s ease-out;
                    background: #4E93AB !important;
                    border: 1px solid #72C4B9 !important;
                }

                a.button:hover, div.button:hover {
                    color: #cccccc !important; 
                }
            </style>
    <?php while (have_rtmedia()) : rtmedia(); ?>

        <?php include ('media-gallery-item.php'); ?>

    <?php endwhile; ?>

        </ul>
        <script>
            jQuery(document).ready(function() {
                /*jQuery.noConflict();
                 jQuery(".socialactive").click(function(){
                 jQuery(".mfp-wrap").remove();
                 jQuery( ".mfp-wrap" ).remove();
                 jQuery(".social-buttons").toggle();
                 });
                 jQuery(".social-buttons a").click(function(){
                 stopPropagation();
                 });*/
            });

            

            function socialactive(id){
            //    jQuery(".social-buttons-"+ id).toggle();
            }

        </script>
        <div class='rtmedia_next_prev row'>
            <!--  these links will be handled by backbone later
                                            -- get request parameters will be removed  -->
    <?php
    $display = '';
    if (rtmedia_offset() != 0)
        $display = 'style="display:block;"';
    else
        $display = 'style="display:none;"';
    ?>
            <a id="rtMedia-galary-prev" <?php echo $display; ?> href="<?php echo rtmedia_pagination_prev_link(); ?>"><?php _e('Prev', 'rtmedia'); ?></a>

    <?php
    $display = '';
    if (rtmedia_offset() + rtmedia_per_page_media() < rtmedia_count())
        $display = 'style="display:block;"';
    else
        $display = 'style="display:none;"';
    ?>
            <a id="rtMedia-galary-next" <?php echo $display; ?> href="<?php echo rtmedia_pagination_next_link(); ?>"><?php echo __('Load more', 'rtmedia'); ?></a>
        </div>
<?php } else { ?>
        <p>
            <?php
            $message = __("Oops !! There's no media found for the request !!", "rtmedia");
            echo apply_filters('rtmedia_no_media_found_message_filter', $message);
            ?>
        </p>
        <?php } ?>

<?php do_action('rtmedia_after_media_gallery'); ?>

</div>


<script>
	jQuery(document).ready(function(){
		resizeGallery();
	});
</script>