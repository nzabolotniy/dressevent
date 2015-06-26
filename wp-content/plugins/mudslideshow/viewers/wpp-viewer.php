<?php

/**
* This function returns the html code to show an image using none-viewer
*
* @access public
* @param url image The url to the big one.
* @param url image The url to the thumbnail. 
* @param string title The title of the image.
* @param int album If this image is part of an album we need a common number for this group.
* @param string float Is this image floating to the left, right or center?
* @param string comment Do this image have a comment? Send false if we don't have to show the comment.
* @param int size The size of the thumbnail.
* @return string The HTML code
*/
function muds_show_wpp($image, $thumbnail, $title, $album=0, $float='center', $comment=false, $width=200, $url, $type, $conf=0) {
	$options=get_option('muds_options');
	$muds_showlink=$options['show_link'];
	
	$class = 'class="slideimg"';
	$class_float="";
	$center="";
	if($float=='center') $class_center=" align=\"center\"";
	$rand = mt_rand(111111,999999);
	
	//HTML specialchars
	$title =xmlentities($title);
	if($comment) $comment = xmlentities($comment);
	$title_int = str_replace('&lt;', '&amp;lt;', $title);
	if($title=='--nn--') { $title = __('No name', 'mudslide'); $title_int = ''; }
	
	if($width!=0) $style_dim = " style='width: {$width}px; max-width: {$width}px;'";
	
	$sq = "";
	if(!is_feed()) {
		$sq = "<img class='slidecontainer' title='{$title}' alt='{$title}' src='".muds_plugin_url('img/link-bg.png')."' border='0' />";
	}
	
	$img = "<div class='slideext slide{$float}'>
			<a class='mssfb-image' target='_self' href='{$image}' rel='wp-prettyPhoto[{$album}]'>
				<img class='mudsphoto'{$style_dim} alt='{$title_int}' title='{$title}' src='{$thumbnail}' border='0' />{$sq}</a>
		</div>";
	return muds_general($img,$float);
}

?>
