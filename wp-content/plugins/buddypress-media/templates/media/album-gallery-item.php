<?php
/** That's all, stop editing from here **/
global $rtmedia_backbone;
global $rtmedia_query;
$rtmedia_backbone = array(
	'backbone' => false,
	'is_album' => false,
	'is_edit_allowed' => false
);
if ( isset( $_POST[ 'backbone' ] ) )
	$rtmedia_backbone['backbone'] = $_POST[ 'backbone' ];
if ( isset( $_POST[ 'is_album' ] ) )
	$rtmedia_backbone['is_album'] = $_POST[ 'is_album' ][0];
if ( isset( $_POST[ 'is_edit_allowed' ] ) )
	$rtmedia_backbone['is_edit_allowed'] = $_POST[ 'is_edit_allowed' ][0];
	//!!!!!
	global $rtmedia_media;
	//var_dump("7005");
	//var_dump($rtmedia_media);
	$is_backbone = $_POST[ 'backbone' ];
	if ($is_backbone != true){
		$img_src = rtmedia_image ( 'rt_media_thumbnail' );
	}
	if($is_backbone != true && strpos($img_src, "buddypress-media/app/assets/img/image_thumb.png") == FALSE) {
?>
<li class="rtmedia-list-item has-author">
    <div class="rtmedia-item-author">
        <a href='<?php rtmedia_author_link (); ?>'>
        <div class="avatar">
            <img src="<?php rtmedia_author_avatar (); ?>" width="45"/>
        </div>
        <?php rtmedia_author_name (); ?>
        </a>
    </div>
    <div class="rtmedia-item-thumbnail">
        <div class="rtmedia-item-count">
            <span><?php rtmedia_album_count (); ?> Drēms</span>
        </div>    
<?php
	$URL = $_SERVER['REQUEST_URI'];
	if(strpos($URL, 'album') !== FALSE){
		$media_id = rtmedia_image ( 'rt_media_thumbnail' ); 
?>
		
        
        <a href ="<?php rtmedia_permalink (); ?>" title="<?php echo rtmedia_title (); ?>">
        <div onclick="location.href='<?php rtmedia_permalink (); ?>'" title="<?php echo rtmedia_title (); ?>">
        		
			<div class="rtmedia-media" id ="rtmedia-media-<?php echo $media_id ?>" style="display: inline-block;vertical-align: middle;text-align: center;width: auto;">
				<?php
					global $rtmedia_media, $rtmedia;
		            $size = " width=\"" . $rtmedia->options[ "defaultSizes_video_singlePlayer_width" ] . "\" height=\"" . $rtmedia->options[ "defaultSizes_video_singlePlayer_height" ] . "\" ";
		            $html = "<div id='rtm-mejs-video-container' style=''>";
		            $html .= '<video src="' . wp_get_attachment_url ( $media_id ) . '" ' . $size . ' type="video/mp4" class="wp-video-shortcode" id="bp_media_video_' . $media_id . '" controls="controls" preload="true"></video>';
		            $html .= '</div>';
					echo $html;
				?>
			</div>
        </div>
        </a>
    </div>

    <div class="rtmedia-item-title">
        <h4 title="<?php echo rtmedia_title (); ?>">
            <a href ="<?php rtmedia_permalink (); ?>" title="<?php echo rtmedia_title (); ?>">
                <?php echo rtmedia_title (); ?>
            </a>
        </h4>
    </div>
<?php
	}else{
?>
        <a href ="<?php rtmedia_permalink (); ?><?php echo (isset($rtmedia_query->attr['attr']['media_type']))? 'show'.'/' : ''?>" title="<?php echo rtmedia_title (); ?>">
        	<img src="<?php echo $img_src; ?>" alt="<?php echo rtmedia_title(); ?>">
        </a>
    </div>

    <div class="rtmedia-item-title">
        <h4 title="<?php echo rtmedia_title (); ?>">
            <a href ="<?php rtmedia_permalink (); ?><?php echo (isset($rtmedia_query->attr['attr']['media_type']))? 'show'.'/' : ''?>" title="<?php echo rtmedia_title (); ?>">
                <?php echo rtmedia_title (); ?>
            </a>
        </h4>
    </div>
<?php
	}
?>
    

</li>
<?php
	}
	if ($is_backbone == true){
?>

<li class="rtmedia-list-item has-author">
    <div class="rtmedia-item-author">
        <a href="<%= rt_author_link %>">
            <div class="avatar">
                <img src="<%= rt_author_avatar %>" width="45">
            </div>
        </a>
	<%= rt_author_name_link %>
    </div>
    <div class="rtmedia-item-thumbnail">
        <div class="rtmedia-item-count">
            <span><%= rt_album_count %> Drēms</span>
        </div>    
        <a href="<%= rt_permalink %><?php echo 'show'.'/'; ?>" title="Alcohol">
            <img src="<%= rt_album_thumb %>" alt="<%= media_title %>">
        </a>
    </div>

    <div class="rtmedia-item-title">
        <h4 title="<%= media_title %>">
            <a href="<%= rt_permalink %><?php echo 'show'.'/'; ?>" title="Alcohol"><%= media_title %></a>
        </h4>
    </div>
</li>

<?php
	}
	?>
