
<div class="rtmedia-container">

    <?php do_action ( 'rtmedia_before_album_gallery' ); ?>
    <?php
//!!!!!!
//<div id="rtm-gallery-title-container">
    ?>
    <?php 
    	get_ps_search_form(); 
    ?>
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
        .shortcode-show-avatar {
        	display: none !important;
        }
    </style>
    <div id="rtm-gallery-title-container" class="dremboard-title-container">
        <h2 class="rtm-gallery-title"><?php _e( 'Drēmboard List' , 'rtmedia' ) ; ?></h2>
        <!--<div id="rtm-media-options"><?php do_action ( 'rtmedia_album_gallery_actions' ); ?></div>-->
    </div>   
    <?php do_action ( 'rtmedia_after_album_gallery_title' ); ?>
    <div id="rtm-media-gallery-uploader">
        <?php rtmedia_uploader ( array('is_up_shortcode'=> false) ); ?>
    </div>

    <?php if ( have_rtmedia () ) { 
    //var_dump("10030");	
    ?>
		<ul class="rtmedia-list rtmedia-album-list col3">
			
		</ul>
        <ul class="rtmedia-list rtmedia-album-list col4">

            <?php while ( have_rtmedia () ) : rtmedia () ; ?>

                <?php include ('album-gallery-item.php') ; ?>

            <?php endwhile ; ?>

        </ul>

        <div class='rtmedia_next_prev row'>
            <!--  these links will be handled by backbone later
                            -- get request parameters will be removed  -->
            <?php
            $display = '' ;
            if ( rtmedia_offset () != 0 )
                $display = 'style="display:block;"' ;
            else
                $display = 'style="display:none;"' ;
            ?>
            <a id="rtMedia-galary-prev" <?php echo $display ; ?> href="<?php echo rtmedia_pagination_prev_link () ; ?>"><?php _e( 'Prev' , 'rtmedia' ) ; ?></a>

            <?php
            $display = '' ;
            if ( rtmedia_offset () + rtmedia_per_page_media () < rtmedia_count () )
                $display = 'style="display:block;"' ;
            else
                $display = 'style="display:none;"' ;
            ?>
            <a id="rtMedia-galary-next" <?php echo $display ; ?> href="<?php echo rtmedia_pagination_next_link () ; ?>"><?php _e( 'Load More' , 'rtmedia' ) ; ?></a>

        </div><!--/.rtmedia_next_prev-->

    <?php } else { ?>
        <p>
            <?php 
                $message = __ ( "Sorry !! There's no media found for the request !!", "rtmedia" );
                echo apply_filters('rtmedia_no_media_found_message_filter', $message);
                ?>
            </p>
    <?php } ?>
    <?php do_action ( 'rtmedia_after_album_gallery' ) ; ?>

</div>
<script>
    jQuery(document).ready(function(){
        resizeGallery();
    });
</script>
<!-- template for single media in gallery -->

<script id="rtmedia-gallery-item-template" type="text/template">
    <div class="rtmedia-item-thumbnail">
    <a href ="media/<%= id %>">
    <img src="<%= guid %>">
    </a>
    </div>

    <div class="rtmedia-item-title">
    <h4 title="<%= media_title %>">
    <a href="media/<%= id %>">
    <%= media_title %>
    </a>
    </h4>
    </div>
</script>
<?php
//!!!!!!!!!
/*
<div id="rtmedia-gallery-item-template" type="text/template">
    <div class="rtmedia-item-thumbnail">
    <a href ="media/<%= id %>">
    <img src="<%= guid %>">
    </a>
    </div>

    <div class="rtmedia-item-title">
    <h4 title="<%= media_title %>">
    <a href="media/<%= id %>">
    <%= media_title %>
    </a>
    </h4>
    </div>
</div>
*/
?>
<!-- rtmedia_actions remained in script tag -->
