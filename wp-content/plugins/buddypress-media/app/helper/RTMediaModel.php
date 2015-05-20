<?php

/**
 * Description of BPMediaModel
 *
 * @author joshua
 */
class RTMediaModel extends RTDBModel {

    function __construct () {
        parent::__construct ( 'rtm_media', false, 10, true );
        $this->meta_table_name = "rt_rtm_media_meta";
    }

    /**
     *
     * @param type $name
     * @param type $arguments
     * @return type
     */
    function __call ( $name, $arguments ) {
        $result = parent::__call ( $name, $arguments );
        if ( ! $result[ 'result' ] ) {
            $result[ 'result' ] = $this->populate_results_fallback ( $name, $arguments );
        }
        return $result;
    }

    /**
     *
     * @global type $wpdb
     * @param type $columns
     * @param type $offset
     * @param type $per_page
     * @param type $order_by
     * @return type
     */
    function get ( $columns, $offset = false, $per_page = false, $order_by = 'media_id desc' , $count_flag = false ) {
        global $wpdb;
        global $rtmedia_query;
        
        

        $select = "SELECT ";
        if($count_flag){
            $select .= "count(*) ";
        }else{
            $select .= "{$this->table_name}.* " ;
        }
        
        $ps_flag = true;
        if(isset($columns['ps_flag'])) {
        	unset($columns['ps_flag']);
        	$ps_flag = false;
        }
        //show_sql
        $show_sql = false;
        if(isset($columns['show_sql'])) {
        	unset($columns['show_sql']);
        	$show_sql = true;
        }
        
        if (isset($columns['id']) || isset($columns['media_id']) || 
                (isset($rtmedia_query->attr['attr']['media_type']) && $rtmedia_query->attr['attr']['media_type'] == 'dremboard')){
        	$ps_flag = false;
        }
                
        if ($ps_flag && is_user_logged_in()) {
            $login_user_id = bp_loggedin_user_id();
            $user_meta_data = bp_get_user_meta($login_user_id, 'bp_activity_flags', true);
            $flag_activities = '';
            if (!empty($user_meta_data)) {
                foreach ($user_meta_data as $key => $meta) {
                    if ($flag_activities == ''){
                        $flag_activities .= $key;
                    }   else {
                        $flag_activities .= ', '.$key;
                    }
                }
            }
            if (!empty($flag_activities)) {
                $flag_where = " AND ({$this->table_name}.activity_id NOT IN ({$flag_activities}) OR {$this->table_name}.activity_id IS NULL) ";
            }
        }
               
        $have_cover_filter = false;
        if (isset($_GET['pcover'])){
            $have_cover_filter = true;
        }
        
        if ($have_cover_filter && $ps_flag){
            $columns['cover_art'] = '1';
        }
        
	$from = " FROM {$this->table_name} ";
        $join = "";
        $where = " where {$this->table_name}.del_flag != '1' {$flag_where}";
	if( is_multisite() ) {
	    $where.= " AND {$this->table_name}.blog_id = '".get_current_blog_id()."' ";
	}
        $temp = 65;
        foreach ( $columns as $colname => $colvalue ) {
            if ( strtolower ( $colname ) == "meta_query" ) {
                foreach ( $colvalue as $meta_query ) {
                    if ( ! isset ( $meta_query[ "compare" ] ) ) {
                        $meta_query[ "compare" ] = "=";
                    }
                    $tbl_alias = chr ( $temp ++  );
		    if(is_multisite() ) {
			$join .= " LEFT JOIN {$wpdb->base_prefix}{$this->meta_table_name} as {$tbl_alias} ON {$this->table_name}.id = {$tbl_alias}.media_id ";
		    } else {
			$join .= " LEFT JOIN {$wpdb->prefix}{$this->meta_table_name} as {$tbl_alias} ON {$this->table_name}.id = {$tbl_alias}.media_id ";
		    }
                    if ( isset ( $meta_query[ "value" ] ) )
                        $where .= " AND  ({$tbl_alias}.meta_key = '{$meta_query[ "key" ]}' and  {$tbl_alias}.meta_value  {$meta_query[ "compare" ]}  '{$meta_query[ "value" ]}' ) ";
                    else
                        $where .= " AND  {$tbl_alias}.meta_key = '{$meta_query[ "key" ]}' ";
                }
            } else {
                if ( is_array ( $colvalue ) ) {
                    if ( ! isset ( $colvalue[ 'compare' ] ) )
                        $compare = 'IN';
                    else
                        $compare = $colvalue[ 'compare' ];

                    $tmpVal = isset ( $colvalue[ 'value' ] ) ? $colvalue[ 'value' ] : $colvalue;
                    $col_val_comapare = ( is_array( $tmpVal ) ) ? '(\'' . implode ( "','", $tmpVal ) . '\')' : '(\''.$tmpVal.'\')';
                    if($compare == 'IS NOT'){
                        $col_val_comapare = !empty($colvalue[ 'value' ]) ? $colvalue[ 'value' ] : $col_val_comapare;
                    }
                    $where .= " AND {$this->table_name}.{$colname} {$compare} {$col_val_comapare}";
                }
                else
                    $where .= " AND {$this->table_name}.{$colname} = '{$colvalue}'";
            }
        }
        $qgroup_by = " ";
        if($order_by){
            $qorder_by = " ORDER BY {$this->table_name}.{$order_by}";
        } else {
            $qorder_by = "";
        }
        
        $have_cat_filter = '';
		if (isset($rtmedia_query->attr['attr']['category'])){
	    	$have_cat_filter = $rtmedia_query->attr['attr']['category'];
	    }else if (isset($_GET['pcat'])){
	    	$have_cat_filter = $_GET['pcat'];
	    }
	    
	    if ($ps_flag && $have_cat_filter != '') {
			$category_val = $have_cat_filter;
			
            if ($category_val != '-1'){ // all categorized
	            $catejoin = " LEFT JOIN {$wpdb->prefix}{$this->meta_table_name} ON {$wpdb->prefix}{$this->meta_table_name}.media_id = {$this->table_name}.media_id ";
	            if ($category_val == '0'){ // uncategorized
	            	$catewhere = " AND not( {$wpdb->prefix}{$this->meta_table_name}.meta_key = 'category' and {$wpdb->prefix}{$this->meta_table_name}.meta_value > '0')";
	            }else{
	            	$catewhere = " AND ( {$wpdb->prefix}{$this->meta_table_name}.meta_key = 'category' and {$wpdb->prefix}{$this->meta_table_name}.meta_value = '{$category_val}')";
	            }
	            $join .= $catejoin;
	            $where .= $catewhere;
            }
        }
        $pagename = get_query_var('pagename');
        $origin_page = (isset($_REQUEST['origin_page']))? $_REQUEST['origin_page'] : "";
        
        if ($pagename == 'drems' ||$origin_page == 'drems'){
        	$dremboardwhere = " AND (({$this->table_name}.source != 'drems photo') OR {$this->table_name}.source IS NULL) ";
        	$where .= $dremboardwhere;
        }

        $select = apply_filters ( 'rtmedia-model-select-query', $select, $this->table_name );
        $join = apply_filters ( 'rtmedia-model-join-query', $join, $this->table_name );
        $where = apply_filters ( 'rtmedia-model-where-query', $where, $this->table_name, $join );
        $qgroup_by = apply_filters ( 'rtmedia-model-group-by-query', $qgroup_by, $this->table_name );
        $qorder_by = apply_filters ( 'rtmedia-model-order-by-query', $qorder_by, $this->table_name );
        
        if ( $ps_flag && ! empty( $_GET['ps'] ) && $_GET['ps'] != "") {
			$psq['ps'] = $_GET['ps'];
			$pswhere = "";
			$psorderby = "";
			$psjoin = " LEFT JOIN $wpdb->posts ON $wpdb->posts.id = {$this->table_name}.media_id ";
			ps_parse_search_params($psq, $pswhere, $psorderby);
			
			$join .= $psjoin;
			$where .= $pswhere;
		}

        $sql = $select . $from . $join . $where . $qgroup_by . $qorder_by;
        if($offset !== false){
            if(! is_integer($offset))
                $offset = 0;

            if( intval ( $offset ) < 0 )
                $offset = 0;

            if( ! is_integer($per_page) )
                $per_page = 1;

            if( intval ( $per_page ) < 1 )
                $per_page = 1;
            
            //filter added to change the LIMIT
            $limit = apply_filters('rtmedia-model-limit-query', ' LIMIT ' . $offset . ',' . $per_page, $offset, $per_page);
            
            $sql .= $limit;
        }
        //var_dump("2095");
        //var_dump($sql);
        //if ($show_sql)
            //error_log ($sql);
        if( ! $count_flag )
            return $wpdb->get_results ( $sql );
        else
            return $wpdb->get_var ( $sql );
    }

    /**
     *
     * @param type $name
     * @param type $arguments
     * @return type
     */
    function populate_results_fallback ( $name, $arguments ) {
        $result[ 'result' ] = false;
        if ( 'get_by_media_id' == $name && isset ( $arguments[ 0 ] ) && $arguments[ 0 ] ) {

            $result[ 'result' ][ 0 ]->media_id = $arguments[ 0 ];

            $post_type = get_post_field ( 'post_type', $arguments[ 0 ] );
            if ( 'attachment' == $post_type ) {
                $post_mime_type = explode ( '/', get_post_field ( 'post_mime_type', $arguments[ 0 ] ) );
                $result[ 'result' ][ 0 ]->media_type = $post_mime_type[ 0 ];
            } elseif ( 'bp_media_album' == $post_type ) {
                $result[ 'result' ][ 0 ]->media_type = 'bp_media_album';
            } else {
                $result[ 'result' ][ 0 ]->media_type = false;
            }

            $result[ 'result' ][ 0 ]->context_id = intval ( get_post_meta ( $arguments[ 0 ], 'bp-media-key', true ) );
            if ( $result[ 'result' ][ 0 ]->context_id > 0 )
                $result[ 'result' ][ 0 ]->context = 'profile';
            else
                $result[ 'result' ][ 0 ]->context = 'group';

            $result[ 'result' ][ 0 ]->activity_id = get_post_meta ( $arguments[ 0 ], 'bp_media_child_activity', true );

            $result[ 'result' ][ 0 ]->privacy = get_post_meta ( $arguments[ 0 ], 'bp_media_privacy', true );
        }
        return $result[ 'result' ];
    }

    /**
     *
     * @param type $columns
     * @param type $offset
     * @param type $per_page
     * @param type $order_by
     * @return type
     */
    function get_media ( $columns, $offset = false, $per_page = false, $order_by = 'media_id desc', $count_flag = false ) {
    	//var_dump("2099");
    	//var_dump($columns);
        if ( is_multisite () ) {
	    $order_by = "blog_id" . (($order_by)? "," . $order_by :'');
	}

	$results = $this->get ( $columns, $offset, $per_page, $order_by , $count_flag );
//var_dump("2099001"); 
//var_dump($results); 
        return $results;
    }

    function get_user_albums ( $author_id, $offset, $per_page, $order_by = 'media_id desc' ) {
        global $wpdb;
global $rtmedia_query;         
//!!!!!!
//var_dump('2050');
	if ( ! empty( $_GET['ps'] ) && $_GET['ps'] != "") {
		$psq['ps'] = $_GET['ps'];
		$pswhere = "";
		$psorderby = "";
		$psjoin = " LEFT JOIN $wpdb->posts ON $wpdb->posts.id = {$this->table_name}.media_id ";
		ps_parse_search_params($psq, $pswhere, $psorderby);
	}
//var_dump($rtmedia_query);
	$have_cat_filter = '';
	if (isset($rtmedia_query->attr['attr']['category'])){
    	$have_cat_filter = $rtmedia_query->attr['attr']['category'];
    }else if (isset($_GET['pcat'])){
    	$have_cat_filter = $_GET['pcat'];
    	if ($have_cat_filter == '-1' || $have_cat_filter == '0')
    		$have_cat_filter = '';
    }
//var_dump($have_cat_filter);
        
        if ( is_multisite () )
            $order_by = "blog_id" . (($order_by)? "," . $order_by :'');
//!!!!!
            //var_dump("2011");
//var_dump($this->table_name);
//var_dump($_SERVER);
/*
//!!!!!!
        $sql = "SELECT * FROM {$this->table_name}  ";  // !!!!! wp_rt_rtm_media
        $where = " WHERE (id IN(SELECT DISTINCT (album_id)
				    FROM {$this->table_name} WHERE media_author = $author_id
                                    AND album_id IS NOT NULL
                                    AND media_type <> 'album' AND context <> 'group') OR (media_author = $author_id ))
			    AND media_type = 'album'
			    AND (context = 'profile' or context is NULL) ";
        
        */

        //!!!!!
        $shortcode_attr = $rtmedia_query->attr;
        $shortcode_attr_type = '';
        if ($shortcode_attr) $shortcode_attr_type = $shortcode_attr['attr']['media_type'];
        $shortcode_for_dremboard = false;
        if ($shortcode_attr_type == 'album') $shortcode_for_dremboard = true;
        if (($rtmedia_query->is_gallery_shortcode && $shortcode_for_dremboard) )
        {
        $sql = "SELECT * FROM {$this->table_name}  ";  // !!!!! wp_rt_rtm_media
        $where = " WHERE {$this->table_name}.media_type = 'album' AND ((({$this->table_name}.privacy <> 60) && ({$this->table_name}.privacy <> 50) && ({$this->table_name}.privacy <> 40)) || ({$this->table_name}.privacy is NULL ))  AND ({$this->table_name}.context = 'profile' or {$this->table_name}.context is NULL) ";
        }
        else 
        {
        	if ((is_user_logged_in()) && (bp_displayed_user_id() == get_current_user_id ()))
        	{
        		$sql = "SELECT * FROM {$this->table_name}  ";  // !!!!! wp_rt_rtm_media
        			$where = " WHERE ({$this->table_name}.id IN(SELECT DISTINCT ({$this->table_name}.album_id)
				    FROM {$this->table_name} WHERE {$this->table_name}.media_author = $author_id
                                    AND {$this->table_name}.album_id IS NOT NULL
                                    AND {$this->table_name}.media_type <> 'album' AND {$this->table_name}.context <> 'group') OR ({$this->table_name}.media_author = $author_id ) ";
                                if (class_exists('BuddyPress')) {
                                    if (bp_is_active('friends')) {
                                        $friends = BP_Friends_Friendship::get_friend_user_ids( $author_id);
                                        $where .= " OR ({$this->table_name}.privacy=40 AND {$this->table_name}.media_author IN ('" . implode("','", $friends) . "'))";
                                    }
                                    if (bp_is_active('familys')) {
                                        $family = BP_familys_familyship::get_family_user_ids( $author_id );
                                        $where .= " OR ({$this->table_name}.privacy=50 AND {$this->table_name}.media_author IN ('" . implode("','", $family) . "'))";
                                    }
                                }
                                $where .= " )
			    					AND {$this->table_name}.media_type = 'album'
			    					AND ({$this->table_name}.context = 'profile' or {$this->table_name}.context is NULL) ";        	
        	}
        	else 
        	{
        		$sql = "SELECT * FROM {$this->table_name}  ";  // !!!!! wp_rt_rtm_media
        			$where = " WHERE ({$this->table_name}.id IN(SELECT DISTINCT ({$this->table_name}.album_id)
				    FROM {$this->table_name} WHERE {$this->table_name}.media_author = $author_id
                                    AND {$this->table_name}.album_id IS NOT NULL
                                    AND {$this->table_name}.media_type <> 'album' AND {$this->table_name}.context <> 'group') OR ({$this->table_name}.media_author = $author_id ))
			    					AND {$this->table_name}.media_type = 'album' AND ((({$this->table_name}.privacy <> 60) && ({$this->table_name}.privacy <> 50) && ({$this->table_name}.privacy <> 40)) || ({$this->table_name}.privacy is NULL ))
			    					AND ({$this->table_name}.context = 'profile' or {$this->table_name}.context is NULL) ";        	
        	}
        	

        }
        
	if( is_multisite() ) {
	    $where.= " AND {$this->table_name}.blog_id = '".get_current_blog_id()."' ";
	}
	
	$where = apply_filters ( 'rtmedia-get-album-where-query', $where, $this->table_name );
	$qorder_by = " ORDER BY {$this->table_name}.$order_by ";
		$sql .= $psjoin. $where. $pswhere. $qorder_by ;
        if($offset !== false){
            if(! is_integer($offset))
                $offset = 0;
            if( intval ( $offset ) < 0 )
                $offset = 0;

            if(! is_integer($per_page))
                $per_page = 1;
            if( intval ( $per_page ) < 1 )
                $per_page = 1;

            //!!!!!!! $sql .= ' LIMIT ' . $offset . ',' . $per_page;
        }

        $results = $wpdb->get_results ( $sql );
        
        //!!!!!
        //var_dump("2012"); 
        //var_dump($sql);
        //var_dump($results);
        //!!!!!
        $results_original = $results;
        $album_result = array();
        foreach($results_original as $results_now)
        {
        	//var_dump("2012");
        	//var_dump($results_now->media_id);
        	$now_catarray = wp_get_post_categories($results_now->media_id);
        	//!!!!!!
        	//var_dump("100002");
        	$now_get_post_status = get_post_status($results_now->media_id);
        	//var_dump($now_get_post_status); 
        	if ($now_get_post_status == 'trash')
        	{
        		continue;
        	}
        	//var_dump("2043");        	
        	//!!!!!!
        	if (!(empty($have_cat_filter)))
        	{
        		if ((int) $have_cat_filter == (int) $now_catarray[0])
        		{
        			
        		}
        		else 
        		{
        			continue;
        		}
        		        		
        	}
        	//var_dump("2041");
        	//var_dump($now_catarray);
        	if ((isset($now_catarray)) && (is_array($now_catarray)) && (count($now_catarray) > 0))
        	{
        		//!!!!! if (in_array(5,$now_catarray))
        		//$parent_cat = get_category_parents($now_catarray[0]);
        		$parent_cat = get_term( $now_catarray[0], 'category' );
        		//var_dump("2019");
        		//var_dump($parent_cat);
        		if ($parent_cat->parent == 5)
        		//if (strpos($parent_cat,'/5/') === false)
        		{
        			$album_result[] = $results_now;
        		}
        		else
        		{
        			
        		}
        	}
        }
        
        
        //!!!!! return $results;
        //var_dump($dremboard_result);
        return $album_result;
    } 
//!!! new dremboard database sql query called by rtmediaquery.php
    function get_user_dremboards ( $author_id, $offset, $per_page, $order_by = 'media_id desc' ) {
        global $wpdb;
        //var_dump("5080");
global $rtmedia_query;
//var_dump($rtmedia_query);
	if ( ! empty( $_GET['ps'] ) && $_GET['ps'] != "") {
		$psq['ps'] = $_GET['ps'];
		$pswhere = "";
		$psorderby = "";
		$psjoin = " LEFT JOIN $wpdb->posts ON $wpdb->posts.id = {$this->table_name}.media_id ";
		ps_parse_search_params($psq, $pswhere, $psorderby);
	}
//!!!!!!
	$have_cat_filter = '';
	if (isset($rtmedia_query->attr['attr']['category'])){
    	$have_cat_filter = $rtmedia_query->attr['attr']['category'];
    }else if (isset($_GET['pcat'])){
    	$have_cat_filter = $_GET['pcat'];
    	if ($have_cat_filter == '-1' || $have_cat_filter == '0')
    		$have_cat_filter = '';
    }

//var_dump("5081");
//var_dump($have_cat_filter);
//var_dump("<br/ ><br/ ><br/ ><br/ ><br/ ><br/ ><br/ ><br/ ><br/ ><br/ ><br/ ><br/ >");
//var_dump($rtmedia_query->is_gallery_shortcode);


        if ( is_multisite () )
            $order_by = "blog_id" . (($order_by)? "," . $order_by :'');
//!!!!!
            //var_dump("2011");
//var_dump($this->table_name);
//var_dump($_SERVER);
/*
//!!!!!
        $sql = "SELECT * FROM {$this->table_name}  ";  // !!!!! wp_rt_rtm_media
        $where = " WHERE (id IN(SELECT DISTINCT (album_id)
				    FROM {$this->table_name} WHERE media_author = $author_id
                                    AND album_id IS NOT NULL
                                    AND media_type <> 'album' AND context <> 'group') OR (media_author = $author_id ))
			    AND media_type = 'album'
			    AND (context = 'profile' or context is NULL) ";
        */
        //!!!!!
        $shortcode_attr = $rtmedia_query->attr;
        $shortcode_attr_type = '';
        if ($shortcode_attr) $shortcode_attr_type = $shortcode_attr['attr']['media_type'];
        $shortcode_for_dremboard = false;
        if ($shortcode_attr_type == dremboard) $shortcode_for_dremboard = true;
        if (($rtmedia_query->is_gallery_shortcode && $shortcode_for_dremboard) )
        {
        $sql = "SELECT * FROM {$this->table_name}  ";  // !!!!! wp_rt_rtm_media
        $where = " WHERE {$this->table_name}.media_type = 'album' AND ((({$this->table_name}.privacy <> 60) && ({$this->table_name}.privacy <> 50) && ({$this->table_name}.privacy <> 40)) || ({$this->table_name}.privacy is NULL ))  AND ({$this->table_name}.context = 'profile' or {$this->table_name}.context is NULL) ";
        }
        else 
        {
        	if ((is_user_logged_in()) && (bp_displayed_user_id() == get_current_user_id ()))
        	{
        		$sql = "SELECT * FROM {$this->table_name}  ";  // !!!!! wp_rt_rtm_media
        			$where = " WHERE ({$this->table_name}.id IN(SELECT DISTINCT ({$this->table_name}.album_id)
				    FROM {$this->table_name} WHERE {$this->table_name}.media_author = $author_id
                                    AND {$this->table_name}.album_id IS NOT NULL
                                    AND {$this->table_name}.media_type <> 'album' AND {$this->table_name}.context <> 'group') OR ( {$this->table_name}.media_author = $author_id ) ";
                                if (class_exists('BuddyPress')) {
                                    if (bp_is_active('friends')) {
                                        $friends = BP_Friends_Friendship::get_friend_user_ids( $author_id);
                                        $where .= " OR ({$this->table_name}.privacy=40 AND {$this->table_name}.media_author IN ('" . implode("','", $friends) . "'))";
                                    }
                                    if (bp_is_active('familys')) {
                                        $family = BP_familys_familyship::get_family_user_ids( $author_id );
                                        $where .= " OR ({$this->table_name}.privacy=50 AND {$this->table_name}.media_author IN ('" . implode("','", $family) . "'))";
                                    }
                                }
                                $where .= " ) 
			    					AND {$this->table_name}.media_type = 'album'
			    					AND ({$this->table_name}.context = 'profile' or {$this->table_name}.context is NULL) ";        	
        	}
        	else 
        	{
        		$sql = "SELECT * FROM {$this->table_name}  ";  // !!!!! wp_rt_rtm_media
        			$where = " WHERE {$this->table_name}.media_author = $author_id 
			    					AND {$this->table_name}.media_type = 'album' AND ((({$this->table_name}.privacy <> 60) && ({$this->table_name}.privacy <> 50) && ({$this->table_name}.privacy <> 40)) || ({$this->table_name}.privacy is NULL ))
			    					AND ({$this->table_name}.context = 'profile' or {$this->table_name}.context is NULL) ";        	
        	}
        	

        }

        
	if( is_multisite() ) {
	    $where.= " AND {$this->table_name}.blog_id = '".get_current_blog_id()."' ";
	}
	$where = apply_filters ( 'rtmedia-get-album-where-query', $where, $this->table_name );
	$qorder_by = " ORDER BY {$this->table_name}.$order_by ";
        $sql .= $psjoin. $where. $pswhere. $qorder_by ;
        if($offset !== false){
            if(! is_integer($offset))
                $offset = 0;
            if( intval ( $offset ) < 0 )
                $offset = 0;

            if(! is_integer($per_page))
                $per_page = 1;
            if( intval ( $per_page ) < 1 )
                $per_page = 1;

           //!!!!! $sql .= ' LIMIT ' . $offset . ',' . $per_page;
        }
//var_dump("5081");
//var_dump($sql);
        $results = $wpdb->get_results ( $sql );
        //!!!!!
        //var_dump("3012");
        //var_dump($sql);
        //var_dump($results);
        //var_dump("5090");
        //!!!!!
        $results_original = $results;
        $dremboard_result = array();
        //var_dump("5082");
        //var_dump($results_original);
        foreach($results_original as $results_now)
        {
        	//var_dump("2012");
        	//var_dump($results_now->media_id);
        	$now_catarray = wp_get_post_categories($results_now->media_id);
        	//!!!!!!
        	//var_dump("100001");
        	$now_get_post_status = get_post_status($results_now->media_id);
        	//var_dump($now_get_post_status); 
        	if ($now_get_post_status == 'trash')
        	{
        		continue;
        	}
        	//!!!!!!
        	if (!(empty($have_cat_filter)))
        	{
        		if ((int) $have_cat_filter == (int) $now_catarray[0])
        		{
        			
        		}
        		else 
        		{
        			continue;
        		}
        		        		
        	}
        	
        	//var_dump($now_catarray);
        	if ((isset($now_catarray)) && (is_array($now_catarray)) && (count($now_catarray) > 0))
        	{
   		//var_dump("5080");
        		//var_dump($results_now->media_id);    
        		/*
        		if (in_array(6,$now_catarray))
        		{
        			$dremboard_result[] = $results_now;
        		}
        		*/
        		//!!!!! if (in_array(5,$now_catarray))
        		//$parent_cat = get_category_parents($now_catarray[0]);
        		$parent_cat = get_term( $now_catarray[0], 'category' );
        		//var_dump("3019");
        		//var_dump($parent_cat);
        		if ($parent_cat->parent == 6)
        		//if (strpos($parent_cat,'/5/') === false)
        		{
        		//var_dump("5065");
        		//var_dump($results_now->media_id);        			
        			$dremboard_result[] = $results_now;
        		}
        		else
        		{
        			
        		}
        	}
        }
        
        
        //!!!!! return $results;
        //var_dump("5091");
        //var_dump($dremboard_result);
        return $dremboard_result;
    }    
    
    function get_group_albums ( $group_id, $offset, $per_page, $order_by = 'media_id desc' ) {
        global $wpdb;
        if ( is_multisite () )
            $order_by = "blog_id" . (($order_by)? "," . $order_by :'');
        $sql = "SELECT * FROM {$this->table_name} WHERE {$this->table_name}.id IN(SELECT DISTINCT ({$this->table_name}.album_id) FROM {$this->table_name} WHERE {$this->table_name}.context_id = $group_id AND {$this->table_name}.album_id IS NOT NULL AND {$this->table_name}.media_type != 'album' AND {$this->table_name}.context = 'group') OR ({$this->table_name}.media_type = 'album' AND {$this->table_name}.context_id = $group_id AND {$this->table_name}.context = 'group')";

	if( is_multisite() ) {
	    $sql.= " AND  {$this->table_name}.blog_id = '".get_current_blog_id()."' ";
	}
        $sql .= " ORDER BY {$this->table_name}.$order_by";

        if($offset !== false){
            if(! is_integer($offset))
                $offset = 0;
            if( intval ( $offset ) < 0 )
                $offset = 0;

            if(! is_integer($per_page))
                $per_page = 1;
            if( intval ( $per_page ) < 1 )
                $per_page = 1;

            $sql .= ' LIMIT ' . $offset . ',' . $per_page;
        }
        $results = $wpdb->get_results ( $sql );
        return $results;
    }

    function get_counts ( $user_id = false, $where_query = false ) {
//!!!!! count nav part
        if ( ! $user_id && ! $where_query )
            return false;
        global $wpdb, $rtmedia;

        $query =
                "SELECT {$this->table_name}.privacy, ";
        foreach ( $rtmedia->allowed_types as $type ) {
            $query .= "SUM(CASE WHEN {$this->table_name}.media_type LIKE '{$type[ 'name' ]}' THEN 1 ELSE 0 END) as {$type[ 'name' ]}, ";
        }
        //!!!!! $query .= "SUM(CASE WHEN {$this->table_name}.media_type LIKE 'album' THEN 1 ELSE 0 END) as album        
        //!!!!! $query .= "SUM(CASE WHEN ( {$this->table_name}.media_type LIKE 'album' AND ISNULL({$this->table_name}.source) ) THEN 1 ELSE 0 END) as album, ";

        $query .= "SUM(CASE WHEN ( {$this->table_name}.media_type LIKE 'album' AND {$this->table_name}.source = 'album' ) THEN 1 ELSE 0 END) as album, ";
        $query .= "SUM(CASE WHEN ( {$this->table_name}.media_type LIKE 'album' AND {$this->table_name}.source = 'dremboard' ) THEN 1 ELSE 0 END) as dremboard        

	FROM
		{$this->table_name} WHERE 2=2 ";

	if ( is_multisite () ) {
	    $query.= " AND {$this->table_name}.blog_id = '".get_current_blog_id()."' ";
	}

        if ( $where_query ) {
            foreach ( $where_query as $colname => $colvalue ) {
                if ( strtolower ( $colname ) != "meta_query" ) {
                    if ( is_array ( $colvalue ) ) {
                        if ( ! isset ( $colvalue[ 'compare' ] ) )
                            $compare = 'IN';
                        else
                            $compare = $colvalue[ 'compare' ];
                        if ( ! isset ( $colvalue[ 'value' ] ) ) {
                            $colvalue[ 'value' ] = $colvalue;
                        }

                        $query .= " AND {$this->table_name}.{$colname} {$compare} ('" . implode ( "','", $colvalue[ 'value' ] ) . "')";
                    } else {

//                        if ( $colname == "context" && $colvalue == "profile" ) {
//                               $query .= " AND {$this->table_name}.{$colname} <> 'group'";
//                        } else {
//                            $query .= " AND {$this->table_name}.{$colname} = '{$colvalue}'";
//                        }
                        //profile now shows only profile media so conditional check removed and counts will be fetched according to the available context
                        $query .= " AND {$this->table_name}.{$colname} = '{$colvalue}'";
                    }
                }
            }
        }
        $query .= "GROUP BY privacy";
        //var_dump("9100");
        //var_dump($query);
        $result = $wpdb->get_results ( $query );
        //var_dump("9101");
        //var_dump($result);
        if ( ! is_array ( $result ) )
            return false;
        return $result;
    }

    function get_other_album_count ( $profile_id, $context = "profile" ) {
        $global = RTMediaAlbum::get_globals ();
	$sql = "select distinct album_id from {$this->table_name} where 2=2 AND {$this->table_name}.context = '{$context}' ";
	if ( is_multisite () ) {
	    $sql.= " AND {$this->table_name}.blog_id = '".get_current_blog_id()."' ";
	}
        if ( is_array ( $global ) && count ( $global ) > 0 ) {
            $sql .= " and {$this->table_name}.album_id in (";
            $sep = "";
            foreach ( $global as $id ) {
                $sql .= $sep . $id;
                $sep = ",";
            }
            $sql .= ")";
        }
        if ( $context == "profile" ) {
            $sql .= " AND {$this->table_name}.media_author=$profile_id ";
        } else if ( $context == "group" ) {
            $sql .= " AND {$this->table_name}.context_id=$profile_id ";
        }
        global $wpdb;
        //var_dump("2015");
        //var_dump($sql);
        $result = $wpdb->get_results ( $sql );
        if ( isset ( $result ) ) {
            return count ( $result );
        } else {
            return 0;
        }
    }
    function get_media_category ($media_id) {
    	global $wpdb;
    	
    	$sql = "SELECT meta_value FROM {$wpdb->prefix}{$this->meta_table_name} where media_id = ".$media_id." and meta_key = 'category'";
    	$result = $wpdb->get_results ( $sql );
    	if ( isset ( $result ) ) {
    		if(count($result) > 0){
    			$cate_val = $result[0]->meta_value;
    			if($cate_val == "")
    				return 1;
   				return $result[0]->meta_value;
    		}
    	}
            return 1;
    }
	function set_media_category ($media_id, $category) {
    	global $wpdb;
    	
    	$sql = "SELECT meta_value FROM {$wpdb->prefix}{$this->meta_table_name} where media_id = ".$media_id." and meta_key = 'category'";
    	$result = $wpdb->get_results ( $sql );
    	if ( isset ( $result ) ) {
    		if(count($result) > 0){
				$wpdb->update(
				"{$wpdb->prefix}{$this->meta_table_name}", 
				array('meta_value' => $category), 
				array('media_id' => $media_id, 'meta_key' => 'category'), 
				array('%d'), array('%d', '%s'));
				return $media_id;
    		}
    	}
    	$wpdb->insert(
            "{$wpdb->prefix}{$this->meta_table_name}", array(
            'media_id' => $media_id,
            'meta_key' => 'category',
            'meta_value' => $category
                ), array('%d', '%s', '%d')
        );
        return $wpdb->insert_id;
    }
    function get_media_cover_art($media_table_id){
        $media = $this->get_media(array('id'=>$media_table_id), false, false);
        return $media[0]->cover_art;
    }
    function set_media_cover_art($media_table_id, $cover_art){
        global $wpdb;
        $wpdb->update(
				"{$this->table_name}", 
				array('cover_art' => $cover_art), 
                array('id' => $media_table_id), 
				array('%d'), array('%d'));
				return $media_table_id;
    }
    
    function set_media_views($media_table_id, $views){
        global $wpdb;
        $wpdb->update(
				"{$this->table_name}", 
				array('views' => $views), 
                array('id' => $media_table_id), 
				array('%d'), array('%d'));
				return $media_table_id;
    }
    
    function set_media_meta ($media_id, $meta_key, $meta_value) {
    	global $wpdb;
    	
    	$sql = "SELECT meta_value FROM {$wpdb->prefix}{$this->meta_table_name} where media_id = ".$media_id." and meta_key = ".$meta_key;
    	$result = $wpdb->get_results ( $sql );
    	if ( isset ( $result ) ) {
    		if(count($result) > 0){
				$wpdb->update(
				"{$wpdb->prefix}{$this->meta_table_name}", 
				array('meta_value' => $meta_value), 
				array('media_id' => $media_id, 'meta_key' => $meta_key), 
				array('%d'), array('%d', '%s'));
				return $media_id;
    		}
    	}
    	$wpdb->insert(
            "{$wpdb->prefix}{$this->meta_table_name}", array(
            'media_id' => $media_id,
            'meta_key' => $meta_key,
            'meta_value' => $meta_value
                ), array('%d', '%s', '%d')
        );
        return $wpdb->insert_id;
    }
}
