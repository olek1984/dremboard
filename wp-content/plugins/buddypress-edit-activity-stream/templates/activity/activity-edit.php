<?php //get_header() ?>
<div class="activity">
	<ul id="activity-stream" class="activity-list item-list">
		<li id="activity-1" class="activity activity_update">
		
		<?php do_action( 'bp_before_edit_activity_edit_form' ) ?>
		<?php if ( etivite_bp_edit_the_activity() ) : ?>
			<form action="<?php etivite_bp_edit_action() ?>" method="post" id="activity-edit-form" class="standard-form">
				<div class="activity-avatar">
					<?php etivite_bp_edit_the_avatar( 'type=full&width=100&height=100' ); ?>
				</div>
				<div class="activity-content">
					<h3><?php _e( 'Edit Activity', 'bp-activity-edit' ) ?></h3>
					<div class="activity-header">
						<?php if ( is_super_admin() ) : ?>
							<label for="activity_action"><?php _e( 'Action: ', 'bp-activity-edit' ) ?></label>
							<input type="text" name="activity_action" id="activity_action" value="<?php etivite_bp_edit_the_activity_action() ?>" />
						<?php endif; ?>			
					</div>
					<br />
					<div class="activity-inner">
                        <input id="origin_activity" name="" type="hidden" value="<?php echo "activity-".etivite_bp_edit_get_the_activity_id(); ?>"/>
						<label for="activity_text"><?php _e( 'Status:', 'bp-activity-edit' ) ?></label>
						<textarea name="activity_text" id="activity_content"><?php etivite_bp_edit_the_activity_text() ?></textarea>
					</div>
                    <br />
                    <?php
                    $activity_id = etivite_bp_edit_get_the_activity_id();
                    $media = bp_get_media_by_activity($activity_id, 'photo');
                    $rtmedia_model = new RTMediaModel();
                    if(count($media) != 0):
                    ?>
                    <div class="activity-media">
                        <label><?php _e( 'Category:', 'bp-activity-edit' ) ?></label>
                        <?php foreach($media as $media_item):?>
                        <div class="activity-media-item">
                            <div class="category">
                                <?php 
                                    $category = $rtmedia_model->get_media_category( $media_item->media_id );
                                    
                                    $cats_all = get_category_children(6);
                if ($cats_all) {
                    $cats_all = ltrim($cats_all, "/");
                    $cats_array = split("/", $cats_all);
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

                        echo '<select name="media_category[]">';

                        foreach ($resultGetCategory1 as $tempCategory1) {
                            $sqlGetCategory2 = "SELECT * FROM `" . $wpTableTerms . "` WHERE `term_id` = '" . $tempCategory1 . "' LIMIT 1";
                            $resultGetCategory2 = $wpdb->get_results($sqlGetCategory2, ARRAY_A);
                            if (sizeof($resultGetCategory2) > 0) {
                                $selected = '';
                                if($resultGetCategory2[0]['term_id'] == $category){
                                    $selected = ' selected ';
                                }
                                echo '<option value="' . $resultGetCategory2[0]['term_id'] . '" '.$selected.'>';
                                echo $resultGetCategory2[0]['name'];
                                echo '</option>';
                            }
                        }
                        echo '</select>';
                        echo '<input type="hidden" name="media_id[]" value="'.$media_item->media_id.'">';
                    }
                }
                                ?>
                            </div>
                            <div class="name">
                                <?php 
                                $media_title = $media_item->media_title;
        
                                $media_title = str_ireplace('.jpg','',$media_title);
                                $media_title = str_ireplace('.jpeg','',$media_title);
                                $media_title = str_ireplace('.png','',$media_title);
                                $media_title = str_ireplace('.gif','',$media_title);
                                echo $media_title;
                                ?>
                            </div>
                            
                        </div>
                        <?php endforeach;?>
                    </div>
                    <?php endif; ?>
					<div class="activity-meta">
						<p class="submit"><input type="submit" name="save_changes" id="save_activity_changes" value="<?php _e( 'Save Changes', 'bp-activity-edit' ) ?>" /></p>
					</div>
				</div>
				
				<?php do_action( 'etivite_bp_edit_activity_edit_form' ) ?>
				<?php wp_nonce_field( 'etivite_bp_edit_activity_post'. etivite_bp_edit_get_the_activity_id() ) ?>
			</form><!-- #forum-topic-form -->
		<?php else : ?>
			<div id="message" class="info">
				<p><?php _e( 'This activity does not exist.', 'bp-activity-edit' ) ?></p>
			</div>
		<?php endif;?>
		</li>
	</ul>
	
</div>
<script>
    init_edit_activity_submit();
</script>
<?php //get_footer() ?>
