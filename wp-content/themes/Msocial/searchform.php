<?php
/**
 *
 * The template for displaying search form
 *
 **/
 
global $tpl;
 
?>

<div style="float:right;margin-right:5px;">
<form method="get" id="searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<input type="text" class="field" name="s" id="s" placeholder="<?php esc_attr_e( 'Search', GKTPLNAME ); ?>" value="<?php echo wp_kses(get_search_query(), null); ?>" />
	
	<input type="submit" id="searchsubmit" value="<?php esc_attr_e( 'Search', GKTPLNAME ); ?>" />
</form>
</div>