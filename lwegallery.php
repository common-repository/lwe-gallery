<?php
/******************************************************************
Plugin Name: LWE-Gallery
Version: 0.4
Plugin URI: http://t413.com/pages/LWE-Gallery/
Description: LWE-Gallery is a simple, fast, and easy way to add more than a few photos to your wordpress posts.  
Author: Tim O'Brien
Author URI: http://t413.com
*******************************************************************/
// Plugin template by Trevor Creech (http://trevorcreech.com) March 10, 2007

$pluginpath = str_replace(str_replace('\\', '/', ABSPATH), get_settings('siteurl').'/', str_replace('\\', '/', dirname(__FILE__))).'/';
$css_final_width = '';
$css_final_height = '';


/*   embed into header   */
function lwegallery_header(){
	$options = get_option('lwegallery');
	$final_width = (($final_width != '') ? $final_width : $options['final_width']);
	$final_height = (($final_height != '') ? $final_height : $options['final_height']);
	echo '<style type="text/css">
	/*   lwegallery css   */
	div.lwegallery {position:relative;text-align:center;padding:0;border:1px solid #EBEDEF;}
	.lwegallery a {color:#fff!Important;text-decoration:none!Important;}
	  .lwegallery a.lweg-big {padding:2px;text-decoration:none!Important;position:relative;display:block;text-align:center;}
	  a:hover.lweg-big {padding:0px;border:2px solid #9D1F20;}
		a.lweg-big img {padding:2px!Important;border-width:0!Important;}
		a:hover.lweg-big img {border-width:0!Important;padding:2px!Important}
		a.lweg-big .lweg_caption {visibility:hidden;color:#fff;font-size:11px;margin:0;padding:5px 15px;text-align:left;position:absolute;bottom:0px;left:0;background-color:#9D1F20;}
		a:hover.lweg-big .lweg_caption {visibility:visible;}
	.lweg-thumbs {padding:0;text-align:center;}
	.lweg-thumbs a img{border:1px solid #ccc;padding:4px!Important;}
	.lweg-thumbs a:hover img{border:2px solid #9D1F20;padding:3px!Important;}
	</style>';
}

/*   wrapper for main function   */
function lwegallery($final_width = '400',$final_height = '300') {
	$options = get_option('lwegallery');
	$final_width = (($final_width != '') ? $final_width : $options['final_width']);
	$final_height = (($final_height != '') ? $final_height : $options['final_height']);
	echo lwegallery_return($final_width, $final_height); 
}
/*   clear returns and quotes for embedded js   */
function cleanforjs($input) {
	$input = str_replace("'","",$input);
	
	$input = urldecode( str_replace("%0D"," ", str_replace("%0D%0A","",urlencode($input))));
	
	$input = htmlentities($input);
	$input = strip_tags($input);
	
	return(($input));
}

/*   get attatched images, returns array of ids _in order_   */
function get_array_of_attatched_ims () {
	global $post;
	$arrImages = get_children('post_type=attachment&post_mime_type=image&post_parent='.$post->ID  );
	if($arrImages) {								//check to see if there are any attachments
		
		foreach($arrImages as $sub) {				//sort by the menu order
			$sub2 = (array)$sub;
			$sort_col[] = $sub2['menu_order'] ;}
		array_multisort($sort_col, $arrImages);		//done sorting
		//print_r($arrImages);
		} else echo '<b>error, no attatched images</b>';
		return($arrImages);
}
    
/*   main function   */
function lwegallery_return($final_width, $final_height)  {
	$rry_of_ims = get_array_of_attatched_ims();
	global $post;
	global $pluginpath;

	//print_r($rry_of_ims[0]->guid);
	//$output = '<img src="'.$pluginpath.'it.php?src='.wp_get_attachment_url($rry_of_ims[0]->ID).'&amp;w='.($final_width-12).'&amp;h='.$final_height.'&amp;zc=0" alt="'.$rry_of_ims[0]->post_title.'" />'."\n";

	
	$output = '<div class="lwegallery" style="width:'.($final_width).'px">'."\n";
	$output .= '	<a class="lweg-big" style="width:'.($final_width-4).'px;height:'.($final_height+8).'px">';
	$output .= '		<span class="lweg_caption" id="lweg_cap'.$post->ID.'">'.cleanforjs($rry_of_ims[0]->post_content).'</span>';
	$output .= '		<img id="lweg_big-'.$post->ID.'" src="'.$pluginpath.'it.php?src='.wp_get_attachment_url($rry_of_ims[0]->ID).'&amp;w='.($final_width-12).'&amp;h='.$final_height.'&amp;zc=0" alt="'.$rry_of_ims[0]->post_title.'" />'."\n";
	$output .= "	</a>\n";

	$output .= "<div class=\"lweg-thumbs\">\n";	
	foreach ($rry_of_ims as $image) {
		$ThumbUrl = wp_get_attachment_thumb_url($image->ID);
		$output .= "\t".'<a href="javascript:;" onmousedown="document.getElementById(\'lweg_big-'.$post->ID.'\').src=\''.$pluginpath.'it.php?src='.($image->guid).'&amp;w='.($final_width-12).'&amp;h='.$final_height.'&amp;zc=0\';document.getElementById(\'lweg_cap'.$post->ID.'\').innerHTML=\''.cleanforjs($image->post_content).'\''."\">\n";
		$output .= "\t".'<img src="'.$ThumbUrl.'" width="50" height="50" alt="'.$image->post_title.'" title="'.cleanforjs($image->post_content).'" /></a>'."\n";
	}
	$output .= '</div> </div>'."\n";

	return($output);
}

/*   search in post and replace   */
function content_lwegallery($content) {
	if(preg_match("/\[lwegallery(\s(\d+)x(\d+))*\]/",$content,$matches)) {
		$parameter1 = $matches[2];
		$parameter2 = $matches[3];
		$content = preg_replace("/\[lwegallery(\s(\d+)x(\d+))*\]/",lwegallery($parameter1,$parameter2), $content);
	}
	return $content;
}

/*   creates backend option panel for plugin   */
function lwegallery_control() {
		$options = get_option('lwegallery');
		if ( !is_array($options) )
		{
			//This array sets default options for plugin when first activated.
			$options = array('title'=>'Hi!!', 'final_width'=>'400', 'final_height'=>'300');
		}
		if ( $_POST['lwegallery-submit'] )
		{
			$options['title'] = strip_tags(stripslashes($_POST['lwegallery-title']));

			//One of these lines is needed for each parameter
			$options['final_width'] = strip_tags(stripslashes($_POST['lwegallery-width']));
			$options['final_height'] = strip_tags(stripslashes($_POST['lwegallery-height']));

			update_option('lwegallery', $options);
		}

		$title = htmlspecialchars($options['title'], ENT_QUOTES);

//		echo '<p style="text-align:right;"><label for="lwegallery-title">Title:</label><br /> <input style="width: 200px;" id="lwegallery-title" name="lwegallery-title" type="text" value="'.$title.'" /></p>';

		//You need one of these for each option/parameter.  You can use input boxes, radio buttons, checkboxes, etc.
		echo '<p style="text-align:right;"><label for="lwegallery-width">Total Width:</label><br /> <input style="width: 200px;" id="lwegallery-width" name="lwegallery-width" type="text" value="'.$options['final_width'].'" /></p>';
		echo '<p style="text-align:right;"><label for="lwegallery-height">Height:</label><br /> <input style="width: 200px;" id="lwegallery-height" name="lwegallery-height" type="text" value="'.$options['final_height'].'" /></p>';

		echo '<input type="hidden" id="lwegallery-submit" name="lwegallery-submit" value="1" />';
	}

/*   adds the options panel under the Options menu   */
function lwegallery_addMenu()
{
	add_options_page("LWE-Gallery", "LWE-Gallery" , 8, __FILE__, 'lwegallery_optionsMenu');
}	

/*   called by lwegallery_addMenu, displays options panel   */
function lwegallery_optionsMenu()
{
	echo '<div style="width:250px; margin:auto;"><form method="post">';
	lwegallery_control();
	echo '<p class="submit"><input value="Save Changes >>" type="submit"></form></p></div>';
}
	
	

// options panel appears under the Admin Options interface
add_action('admin_menu', 'lwegallery_addMenu');

// include code in header
add_action('wp_head', 'lwegallery_header');

// token calling using a token in a post ([lwegallery])
add_filter('the_content', 'content_lwegallery');

?>
