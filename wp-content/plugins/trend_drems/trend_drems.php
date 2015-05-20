<?php
/*
  Plugin Name: Trend Drems Widget
  Plugin URI: www.dremboard.com
  Description: Trend Drems Widget shows Trend Drems posted in dremboard to display on your sidebar
  Author: JinXinZhi
  Version: 1.0
  Author URI: www.dremboard.com
 */

class TrendDremsWidget extends WP_Widget {

    /**
     *
     * Constructor
     *
     * @return void
     *
     * */
    function TrendDremsWidget() {
        $widget_ops = array('classname' => 'TrendDremsWidget', 'description' => 'Trend Drems posted in www.dremboard.com');
        $this->WP_Widget('TrendDremsWidget', 'Trend Drems', $widget_ops);
    }

    /**
     *
     * Outputs the HTML code of the widget in the back-end
     *
     * @param array instance of the widget settings
     * @return void - HTML output
     *
     * */
    function form($instance) {
        $title = isset($instance['title']) ? esc_attr($instance['title']) : '';
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title:', GKTPLNAME); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
        </p>

        <?php
    }

    /**
     *
     * Used in the back-end to update the module options
     *
     * @param array new instance of the widget settings
     * @param array old instance of the widget settings
     * @return updated instance of the widget settings
     *
     * */
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        return $instance;
    }

    /**
     *
     * Outputs the HTML code of this widget.
     *
     * @param array An array of standard parameters for widgets in this theme
     * @param array An array of settings for this widget instance
     * @return void
     *
     * */
    function widget($args, $instance) {
        extract($args, EXTR_SKIP);

        $title = apply_filters('widget_title', empty($instance['title']) ? __('Latest Photos', GKTPLNAME) : $instance['title'], $instance, $this->id_base);

        echo $before_widget;
        echo $before_title;
        echo $title;
        echo $after_title;

        ob_start();

        $trend_drems = array();
        $page = 0;
        $more_flag = false;

        $trend_html = get_trend_drems(0, $more_flag);

        if (empty($trend_html)) :

            echo __('There has been no trending drems.', GKTPLNAME);

        else :
            ?>
            <div class="gk-bp-trend-drems">
                <div class="trend-container">
                    <?php echo $trend_html; ?>
                </div>
                <a class="see-more" <?php if (!$more_flag): ?>style="display: none;"<?php endif; ?>>See More ...</a>
                <input type="hidden" id="trend-drem-page" value="1">
            </div>
        <?php
        endif;

        ob_get_flush();
        // 
        echo $after_widget;
    }

}

function get_trend_drems($page, &$more_flag) {
    global $wpdb, $bp;

    $offset = $page * 10;
    $sql = "SELECT DATE_FORMAT(w.date_recorded, '%Y%m%d') as o_date, count(lw.id) as o_comments, m.meta_value as o_likes, w.* 
                FROM {$bp->activity->table_name} w
                left join {$bp->activity->table_name} lw on w.id = lw.item_id and lw.type='activity_comment'
                left join {$bp->activity->table_name_meta} m on m.activity_id=w.id and m.meta_key='liked_count'"
            . " where w.component='trend'"
            . " group by w.id
                order by o_date desc, o_comments desc, o_likes desc
                limit {$offset}, 10";

    $count_sql = "SELECT count(w.id) FROM {$bp->activity->table_name} w"
            . " where w.component='trend'"
            . "";

    $results = $wpdb->get_results($sql);
    $count = $wpdb->get_var($count_sql);

    if ($count > ($offset + 10)) {
        $more_flag = true;
    } else {
        $more_flag = false;
    }

    foreach ($results as $drems) {
        $post_date = date("M d, Y g:i A", strtotime($drems->date_recorded)); //"December 18, 2014 6:39 PM";
        $content = $drems->content;
        $activity_text = strip_tags(strstr($content, '<ul', true));
        if (strstr($content, '<ul', true) == false) {
            $activity_text = strip_tags($content);
        }
        $title = $activity_text;
        $content = $activity_text;

        $rtmedia_model = new RTMediaModel();
        $activity_medias = bp_get_media_by_activity($drems->id);
        
        $comments = $drems->o_comments;
        $likes = count(array_map( 'maybe_unserialize', (array) $drems->o_likes ));//(array)$drems->o_likes;
        
        $rtmedia = array();
        foreach ($activity_medias as $media) {
            $guid = rtmedia_image($size = 'rt_media_thumbnail', $id = $media->id, $recho = false);
            $type = $media->media_type;
            $media_title = $media->media_title;
           
            $rtmedia[] = array(
                'media_title' => $media_title,
                'guid' => $guid,
                'type' => $type,
            );
        }

        $trend_drems[] = array(
            'activity_id' => $drems->id,
            'title' => $title,
            'post-date' => $post_date,
            'comments'  => $comments,
            'likes' => $likes,
            'content' => $content,
            'media' => $rtmedia,
        );
    }
    ob_start();
    foreach ($trend_drems as $trend_drem):
        ?>
        <a class="trend-drem-link" href="/activity/<?php echo $trend_drem['activity_id']; ?>">
            <div class="trend-drem">
                <div class="title"><?php echo $trend_drem['title']; ?></div>
                <div class="trend-info">
                <div class="post-date"><?php echo $trend_drem['post-date']; ?></div>
                <div class="post-comments"><?php echo $trend_drem['comments']; ?> comments</div>
                <div class="post-likes"><?php echo $trend_drem['likes']; ?> likes</div>
                </div>
                <div class="content"><?php echo $trend_drem['content']; ?></div>
                <?php if (!empty($trend_drem['media'])): ?>
                    <div class="sub-media">
                        <?php foreach ($trend_drem['media'] as $trend_media): ?>
                            <div class="sub-media-item">
                                <?php if ($trend_media['type'] == 'photo'): ?>
                                    <img src="<?php echo $trend_media['guid']; ?>" class="thumbnail trend" alt="<?php echo $trend_media['media_title'] ?>" />
                                <?php endif; ?>
                                <?php if ($trend_media['type'] == 'video'): ?>
                                    <video src="<?php echo $trend_media['guid']; ?>"  width="200" type="video/mp4" ></video>
                                    <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </a>
        <?php
    endforeach;
    $result = ob_get_clean();
    return $result;
}

add_action('widgets_init', create_function('', 'return register_widget("TrendDremsWidget");'));

function trend_drems_load_scripts() {
    wp_enqueue_script('trend_drems_js', plugins_url('js/trend_drems.js', __FILE__), array(), '1.0', true);
    wp_enqueue_style('trend_drems_css', plugins_url('css/trend_drems.css', __FILE__), false, '1.0');
}

add_action('wp_enqueue_scripts', 'trend_drems_load_scripts');
?>
