<div id="buddypress">
	<?php do_action( 'template_notices' ); ?>

	<div class="activity no-ajax" role="main">
		<?php if ( bp_has_activities( 'display_comments=threaded&show_hidden=true&include=' . bp_current_action() ) ) : ?>

			<ul id="activity-stream" class="activity-list item-list">
			<?php while ( bp_activities() ) : bp_the_activity(); 
                
                update_rtmedia_view_count_by_activity_id(bp_get_activity_id());

				bp_get_template_part( 'activity/entry' ); 

			 endwhile; ?>
			</ul>

		<?php endif; ?>
	</div>
</div>