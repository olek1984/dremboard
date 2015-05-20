<?php

/**
 *
 * The template used for displaying page content in page.php
 *
 **/
 
global $tpl;

$show_title = true;

if ((is_page_template('template.fullwidth.php') && ('post' == get_post_type() || 'page' == get_post_type())) || get_the_title() == '') {
	$show_title = false;
}

$classname = '';

if(!$show_title) {
	$classname = 'no-title';
}

if(is_page() && get_option($tpl->name . '_template_show_details_on_pages', 'Y') == 'N') {
	$classname .= ' page-fullwidth';
}

?>

<article id="post-<?php the_ID(); ?>" <?php post_class($classname); ?>>
	<?php if($show_title) : ?>
	<?php get_template_part( 'layouts/content.post.featured' ); ?>
<!--	
	<header>
		<?php get_template_part( 'layouts/content.post.header' ); ?>
	</header>
-->	
	<?php endif; ?>
    <?php 
        $is_license = false;
        $page_name = get_query_var('pagename');
        if($page_name == "privacy-policy" || $page_name == "terms" || $page_name == "about-us"){
            $is_license = true;
            $license_title = ($page_name == "privacy-policy")?"Privacy Policy":"Terms";
            if ($page_name == "about-us")
            	$license_title = "About Us";

            if ($page_name != "about-us"){
	            $other_title = ($page_name == "privacy-policy")?"Terms":"Privacy Policy";
	            $other_link = ($page_name == "privacy-policy")?"/terms":"/privacy-policy";
            }
        }
    ?>
	<section class="content<?php echo ($is_license)?" license_page":"" ?>">
        <?php if($is_license):?>
        <style>
        #gk-sidebar{
        	display:none!important;
    	}
    	#gk-mainbody-columns{
            background: none;
        }
    	#gk-mainbody-columns > section{
    		width: 100%;
    	}
    	
        </style>
        <div class="license-container">
            <div class="license-title">
                <h1><?php echo $license_title; ?></h1>
            </div>
        <?php if ($page_name != "about-us"): ?>
        	<div class="license-other">
                <a href="<?php echo $other_link; ?>">
                    <?php echo $other_title; ?>
                </a>
            </div>
        <?php endif; ?>
            <div class="license-content">
                <?php the_content(); ?>
            </div>
            
        </div>
        <?php else:?>
		<?php the_content(); ?>
		<?php endif;?>
		<?php gk_post_fields(); ?>
		<?php gk_post_links(); ?>
	</section>
	
	<?php get_template_part( 'layouts/content.post.footer' ); ?>
</article>