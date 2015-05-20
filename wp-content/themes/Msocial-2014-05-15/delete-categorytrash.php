<?php

/**
 *
 * Category page
 *
 **/

global $tpl;

gk_load('header');
gk_load('before');

add_action( 'pre_get_posts', 'load_my_album_category' );
function load_my_album_category( $query )
{
    //if ( is_admin() && $query->is_main_query() && (isset($_GET['post_type'])) && ($_GET['post_type']=='rtmedia_album') )
    {
    	if ((isset($_GET['post_status'])) && ($_GET['post_status'] == 'trash'))
    	{
    		//$query->set( 'post_status', 'trash' );
    	}
    	else 
    	{
    		//$query->set( 'post_status', 'hidden' );
    		var_dump("1001");
    		$query->set( 'post_status', 'hidden' );
    	}
        //$query->set( 'post_status', 'any' );
        
        //$query->set( 'post_type', array('rtmedia_album','trash'));
        //$query->set( 'post_type', array('rtmedia_album','attachment'));
        var_dump("1002");
        $query->set( 'post_type', array('rtmedia_album'));
	}
}
//$rand_posts = get_posts('post_type=rtmedia_album&orderby=post_date');
//var_dump($rand_posts);
$rightBox = the_category_ID(false);
var_dump("2012");
var_dump($rightBox);
	$categories = get_the_category(); 
	$cat = $categories[0]->term_id;
	$categories = get_the_terms( '', 'category' );
var_dump($cat);	
var_dump($categories);	
query_posts("post_type=rtmedia_album&post_status=hidden&cat=$rightBox"); 
var_dump($cat);	
var_dump($categories);	
?>
<?php echo single_cat_title( '', false ); var_dump("2013"); ?>
<section id="gk-mainbody" class="category-page">
	<?php if ( have_posts() ) : ?>	
		<?php if ( category_description() ) : // Show an optional category description ?>
		<h1 class="page-title">
			<?php echo single_cat_title( '', false ); ?>
		</h1>
		
		<div class="page-desc">
			<?php echo category_description(); ?>
		</div>
		<?php endif; ?>
		
		<?php do_action('gavernwp_before_loop'); ?>
		
		<?php while ( have_posts() ) : the_post(); ?>
			<?php get_template_part( 'content', get_post_format() ); ?>
		<?php endwhile; ?>
	
		<?php gk_content_nav(); ?>
		
		<?php do_action('gavernwp_after_loop'); ?>
	
	<?php else : ?>
	
		<h1 class="page-title">
			<?php _e( 'Nothing Found', GKTPLNAME ); ?>
		</h1>
	
		<section class="intro">
			<?php _e( 'Apologies, but no results were found for the requested archive. Perhaps searching will help find a related post.', GKTPLNAME ); ?>
		</section>
		
		<?php get_search_form(); ?>
		
	<?php endif; ?>
</section>

<?php

gk_load('after');
gk_load('footer');

// EOF