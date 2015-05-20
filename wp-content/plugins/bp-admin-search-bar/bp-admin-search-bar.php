<?php
/**
 * Plugin Name: BuddyPress Admin Search Bar
 * Plugin URI: http://tomas.zhu.bz
 * Author: Tomas Zhu
 * Author URI: http://tomas.zhu.bz
 * Description: BuddyPress Admin Search Bar
 * Version: 1.0.0
 * License: GPL
*/

add_action('admin_bar_menu', 'add_search_items');

function add_search_items($admin_bar)
{
//echo "<pre>";
//print_r($admin_bar);
//echo "<pre>";
/*
$cur_url = get_permalink();
$home_url = home_url();

if(strpos($cur_url, $home_url.'/drems/') !== false
	 || strpos($cur_url, $home_url.'/dremboard/') !== false
	 || strpos($cur_url, $home_url.'/memories/') !== false
	 || !preg_match('/{$home_url}\/drem\-.*\//', $cur_url)
	 || !preg_match('/{$home_url}\/members\/.*\/media\/[0-9]*\//', $cur_url)
	 || !preg_match('/{$home_url}\/members\/.*\/media\/photo\//', $cur_url)
	 || !preg_match('/{$home_url}\/members\/.*\/media\/dremboard\//', $cur_url)
	 || !preg_match('/{$home_url}\/members\/.*\/media\/album\//', $cur_url)
	 || !preg_match('/{$home_url}\/memories\-.*\//', $cur_url)){
	$title = '<div id="head-search" style="float:left; !important;">
			<form action="" method="get" style="background: #000; border: 0; margin-right: 0; margin-bottom: 0; width: 140px; height: 32px; color: #999999; float: left; font-size: 12px; outline: none; font-family: \"Open Sans\", sans-serif;border-radius: 0px;">
				<input style="background: #555;color:#fff;" class="text" name="ps" type="text" value="" maxlength="150" placeholder=" Search Drems..."> 
				<input type="submit" class="button" value="Search" style="display:none; float: right;
	text-shadow: none !important;
	border: none;
	height: 32px;
	width: 60px;
	background: #131313;
	background-position: 100% 2px;
	background-repeat: no-repeat;
	border-radius: 0;
	padding: 0;">
			</form>
			</div>';
}else{
*/
	$title = '<div id="head-search" style="float:left; !important;">
			<form action="/" method="get" style="background: #000; border: 0; margin-right: 0; margin-bottom: 0; width: 140px; height: 32px; color: #999999; float: left; font-size: 12px; outline: none; font-family: \"Open Sans\", sans-serif;border-radius: 0px;">
				<input style="background: #555;color:#fff;" class="text" name="s" type="text" value="" maxlength="150" placeholder=" Search Drēms..."> 
				<input type="submit" class="button" value="Search" style="display:none; float: right;
	text-shadow: none !important;
	border: none;
	height: 32px;
	width: 60px;
	background: #131313;
	background-position: 100% 2px;
	background-repeat: no-repeat;
	border-radius: 0;
	padding: 0;">
			</form>
			</div>';
//}

	
$admin_bar->add_menu( array(
    'id'    => 'my-search-item',
            'parent' => 'top-secondary',
    'title' => $title,
    'meta'  => array(
        'tabindex' => 1,
    ),
) );
}


function my_head_search()
{
	global $wp_admin_bar;
	//adminbar-search
		$wp_admin_bar->remove_menu('search');
	
	//remove_action( 'admin_bar_menu', 'wp_admin_bar_search_menu', 10 );
//add_action( 'admin_bar_menu', 'your_custom_callback_function', 1 );	

//var_dump("5555");
}
//add_action('wp_before_admin_bar_render','my_head_search',101);
add_action('admin_bar_menu','my_head_search',101);

function get_ps_search_form( $cat_type='', $cat = false, $echo = true ) {
        global $ps_search_set;
        if(isset($ps_search_set) && $ps_search_set == true)
        	return;
        $ps_search_set = true;
        $cat_html = "";
        
        // decide $cat and $cat_type here;
        global $rtmedia_query;
        
    	if(is_page('dremboard')){
        	$cat = true;
        	$cat_type = 'dremboard';
    	}else if(is_page('drems')){
        	$cat = true;
        	$cat_type = 'drem';
    	}else if (isset($rtmedia_query->media_query)){
        	if(isset($rtmedia_query->media_query['media_type']) && $rtmedia_query->media_query['media_type'] == 'album'){
	        	$cat = true;
	        	$cat_type = 'dremboard';
        	}else if(isset($rtmedia_query->media_query['media_type']) && $rtmedia_query->media_query['media_type'] == 'photo'){
	        	$cat = true;
	        	$cat_type = 'drem';
        	}else if(isset($rtmedia_query->media_query['album_id'])){
	        	$cat = true;
	        	$cat_type = 'drem';
        	}else{
        		//$cat = true;
	        	//$cat_type = 'drem';
        	}
        }
        
        if ($cat){
        	
        	//if ($cat_type == "memory")
        	//	$cats_all = get_category_children(5);
        	//else
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

					$cat_html .= '<select name="pcat" id="pcat" class="pcat">';
					$cat_html .= '<option value="-1">All categories</option>';
					if ($cat_type == 'drem')
			       		$cat_html .= '<option value="0">Uncategorized</option>';

			        foreach ($resultGetCategory1 as $tempCategory1) {
			            $sqlGetCategory2 = "SELECT * FROM `" . $wpTableTerms . "` WHERE `term_id` = '" . $tempCategory1 . "' LIMIT 1";
			            $resultGetCategory2 = $wpdb->get_results($sqlGetCategory2, ARRAY_A);
			            if (sizeof($resultGetCategory2) > 0) {
			            	$val = $resultGetCategory2[0]['term_id'];
			            	//$selected = (isset($_REQUEST['pcat']) && $_REQUEST['pcat'] == $val)?"selected": "";
			                $cat_html .= '<option value="' . $resultGetCategory2[0]['term_id'] . '" '.$selected.'>';
			                $cat_html .= $resultGetCategory2[0]['name'];
			                $cat_html .= '</option>';
			            }
			        }
			        $cat_html .= '</select>';
			    }
			}
        }
        $pagename = get_query_var('pagename');
        
        if (is_site_admin() && $pagename == 'drems'){
            $cover_html = '<input type="checkbox" id="cover-only" name="pcover"/>
                    <label for="cover-only">Cover Only</label>';
        }
    
    $action = "";
    $media = get_query_var('media');
    if (isset($rtmedia_query->media_query['album_id'])) {
        if (strpos($media, '/show') !== false) {
            $action = '/dremboard/';
        } else if (isset($rtmedia_query->media_query['context']) && $rtmedia_query->media_query['context'] == "group") {
            global $bp;
            $group_link = bp_get_group_permalink($bp->groups->current_group);
            $action = (trailingslashit($group_link) . RTMEDIA_MEDIA_SLUG . '/dremboard/');
        } else {
            $action = (trailingslashit(get_rtmedia_user_link(get_current_user_id())) . RTMEDIA_MEDIA_SLUG . '/dremboard/');
        }
    }
    //'.(isset($_REQUEST['ps'])?$_REQUEST['ps']:'').'
        $result = '<div id="ps_search_box">
                    <form method="get" id="searchform" action="'.$action.'">
                    '.$cover_html.'
                    <input type="submit" id="searchsubmit" value="Search"/>'
                   .$cat_html.'<input type="text" class="field" name="ps" id="ps" placeholder="Search Page" value=""/> 
                    </form>
                    </div>';

	if ( $echo )
		echo $result;
	else
		return $result;
}

function ps_parse_search( &$q ) {
		global $wpdb;

		$search = '';

		// added slashes screw with quote grouping when done early, so done later
		$q['ps'] = stripslashes( $q['ps'] );
		if ( empty( $_GET['ps'] ))
			$q['ps'] = urldecode( $q['ps'] );
		// there are no line breaks in <input /> fields
		$q['ps'] = str_replace( array( "\r", "\n" ), '', $q['ps'] );
		//var_dump($q);
		$q['search_terms_count'] = 1;
		if ( ! empty( $q['sentence'] ) ) {
			$q['search_terms'] = array( $q['ps'] );
		} else {
			if ( preg_match_all( '/".*?("|$)|((?<=[\t ",+])|^)[^\t ",+]+/', $q['ps'], $matches ) ) {
				$q['search_terms_count'] = count( $matches[0] );
				$q['search_terms'] = ps_parse_search_terms( $matches[0] );
				// if the search string has only short terms or stopwords, or is 10+ terms long, match it as sentence
				if ( empty( $q['search_terms'] ) || count( $q['search_terms'] ) > 9 )
					$q['search_terms'] = array( $q['ps'] );
			} else {
				$q['search_terms'] = array( $q['ps'] );
			}
		}

		$n = ! empty( $q['exact'] ) ? '' : '%';
		$searchand = '';
		$q['search_orderby_title'] = array();
		foreach ( $q['search_terms'] as $term ) {
			$term = like_escape( esc_sql( $term ) );
			if ( $n )
				$q['search_orderby_title'][] = "$wpdb->posts.post_title LIKE '%$term%'";

			$search .= "{$searchand}(($wpdb->posts.post_title LIKE '{$n}{$term}{$n}') OR ($wpdb->posts.post_content LIKE '{$n}{$term}{$n}'))";
			$searchand = ' AND ';
		}

		if ( ! empty( $search ) ) {
			$search = " AND ({$search}) ";
			if ( ! is_user_logged_in() )
				$search .= " AND ($wpdb->posts.post_password = '') ";
		}
		return $search;
	}

function ps_parse_search_terms( $terms ) {
	$strtolower = function_exists( 'mb_strtolower' ) ? 'mb_strtolower' : 'strtolower';
	$checked = array();

	$stopwords = ps_get_search_stopwords();

	foreach ( $terms as $term ) {
		// keep before/after spaces when term is for exact match
		if ( preg_match( '/^".+"$/', $term ) )
			$term = trim( $term, "\"'" );
		else
			$term = trim( $term, "\"' " );

		// Avoid single A-Z.
		if ( ! $term || ( 1 === strlen( $term ) && preg_match( '/^[a-z]$/i', $term ) ) )
			continue;

		if ( in_array( call_user_func( $strtolower, $term ), $stopwords, true ) )
			continue;

		$checked[] = $term;
	}

	return $checked;
}

function ps_get_search_stopwords() {

	$words = explode( ',', _x( 'about,an,are,as,at,be,by,com,for,from,how,in,is,it,of,on,or,that,the,this,to,was,what,when,where,who,will,with,www',
		'Comma-separated list of search stopwords in your language' ) );

	$stopwords = array();
	foreach( $words as $word ) {
		$word = trim( $word, "\r\n\t " );
		if ( $word )
			$stopwords[] = $word;
	}

	return apply_filters( 'wp_search_stopwords', $stopwords );
}

function ps_parse_search_order( &$q ) {
	global $wpdb;

	$search_orderby = '';

	if ( $q['search_terms_count'] > 1 ) {
		$num_terms = count( $q['search_orderby_title'] );
		$search_orderby_s = like_escape( esc_sql( $q['s'] ) );

		$search_orderby = '(CASE ';
		// sentence match in 'post_title'
		$search_orderby .= "WHEN $wpdb->posts.post_title LIKE '%{$search_orderby_s}%' THEN 1 ";

		// sanity limit, sort as sentence when more than 6 terms
		// (few searches are longer than 6 terms and most titles are not)
		if ( $num_terms < 7 ) {
			// all words in title
			$search_orderby .= 'WHEN ' . implode( ' AND ', $q['search_orderby_title'] ) . ' THEN 2 ';
			// any word in title, not needed when $num_terms == 1
			if ( $num_terms > 1 )
				$search_orderby .= 'WHEN ' . implode( ' OR ', $q['search_orderby_title'] ) . ' THEN 3 ';
		}

		// sentence match in 'post_content'
		$search_orderby .= "WHEN $wpdb->posts.post_content LIKE '%{$search_orderby_s}%' THEN 4 ";
		$search_orderby .= 'ELSE 5 END)';
	} else {
		// single word or sentence search
		$search_orderby = reset( $q['search_orderby_title'] ) . ' DESC';
	}

	return $search_orderby;
}

function ps_parse_search_params(&$psq, &$pswhere, &$psorderby) {
	$pswhere = ps_parse_search($psq);
	$psorderby = ps_parse_search_order($psq);
}
?>