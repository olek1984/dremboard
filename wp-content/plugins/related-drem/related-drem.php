<?php
/*
Plugin Name: Related Drem
version:1.0
Plugin URI: http://tomas.zhu.bz
Description: Related Drem
Author: Tomas Zhu
Author URI: http://tomas.zhu.bz
Plugin URI: http://tomas.zhu.bz
Released under the GPL license
http://www.gnu.org/licenses/gpl.txt
*/

  $mycss= '<style type="text/css">
	#makefun_wantmore {color:#db4a37!important; font-size:1.0em; line-height:1.5em; padding:12px 10px 13px 10px;border:1px solid #ddd;width:100%;}
	#makefun_wantmore a { text-decoration:none;color:gray;}
	#makefun_wantmore a:hover { text-decoration:underline;}
	#makefun_wantmore strong{ font-size:1.16em; line-height:1.5em; color:#db4a37; font-weight:bold;}
	#makefun_wantmore ul {margin:0px; padding:0 2px; list-style:none;}
	#makefun_wantmore li {margin:0px; padding:0 5px; list-style:none;}
	.in10 { padding-bottom:10px;}
	</style>';	


function releated_drem($limit=5,$searchingstring='') {  
    global $wpdb, $id ,$table_prefix,$mycss;

    $terms = null;
    $limit = mysql_real_escape_string($limit);
    if ($limit >10)
    {
        $limit = 8;
    }

    if($searchingstring) 
    { 

        $terms = $searchingstring;
        $time_difference = get_option('gmt_offset');
        $now = gmdate("Y-m-d H:i:s",(time()+($time_difference*3600)));      
    	if ('yes_fulltext' == 'yes_fulltext')
    	{
     		$sql = "SELECT ID, post_title, post_content,"
             	. "MATCH (post_name, post_content) "
             	. "AGAINST ('$terms') AS score "
             	. "FROM $wpdb->posts WHERE "
             	. "MATCH (post_name, post_content) "
             	. "AGAINST ('$terms') "
             	. "AND post_date <= '$now' "
             	. "AND (post_status IN ( 'hidden',  'attachment' )) "
             	. "ORDER BY score DESC LIMIT $limit" ;             	
    	}
    	else
    	{

    		$sql = "SELECT ID, post_title, post_content "
             	. "FROM $wpdb->posts WHERE "
             	. "post_content like '%" . $terms ."%' "
             	. "AND post_date <= '$now' "
             	. "AND (post_status IN ( 'publish',  'static' )) "
             	. "ORDER BY post_date DESC LIMIT $limit";

    	}        
        $permalink = $results = $title = $temppermalink = null;
//var_dump("10002");
//var_dump($sql);
        $results = $wpdb->get_results($sql);
//var_dump($results);        
        $output = $sql = '';
        if ($results) {
        	//var_dump("100033");
			echo $mycss;
            echo '<div id="makefun_wantmore">';            
            //echo '<ul><li><strong><font color = red> Related Drem </font> </strong></li><div class="in10"></div>';
            echo '<ul><li><strong> Related Drēm  </strong></li><div class="in10"></div>';
			foreach ($results as $result) 
			{
				//var_dump('100511');
                $title = stripslashes(apply_filters('the_title', $result->post_title));

               	if ('yes_optimize' == '11')
    			{
    				$sql = "SELECT guid FROM $wpdb->posts WHERE ID = '".$result->ID . "' AND post_date <= '" . $now . "' AND (post_status IN ( 'publish',  'static' )) AND (post_type IN ('post','page'))";
    				$temppermalink = $wpdb->get_results($sql);
    				if ($temppermalink)
    				{
					foreach ($temppermalink as $temppermalink)
    					{
    						$permalink = $temppermalink->guid;    						
    					}
    				}
    				else 
    				{
    					continue;
    				}    				
		    	}
		    	else
		    	{
		    		//var_dump("10089");
		    		$media_id = rtmedia_id($result->ID);
		    		
		    		//var_dump($media_id);
		    		//$permalink = get_permalink($result->ID);
		    		if (!(empty($media_id))) $permalink = get_rtmedia_permalink ( $media_id);	
		    		
		    	}
                $output .= '<li><a href="'. $permalink .'" rel="bookmark" title="Permanent Link: ' . $title . '">' . $title . '</a></li>';
            }            
            echo  $output ;
            echo '</ul></div>';
        } 
    }
}

global $wpdb, $id ,$table_prefix,$mycss;
$get_fulled = get_option('drem_fulltext_ed');
if (empty($get_fulled))
{
				$sql = 'ALTER TABLE `'.$table_prefix.'posts` ADD FULLTEXT `post_related` ( `post_name` ,' . ' `post_content` )';
		    	$wpdb->hide_errors();
		    	$sql_result = $wpdb->query($sql);
		    	$wpdb->show_errors();
		    	update_option('drem_fulltext_ed','YES');
}

?>