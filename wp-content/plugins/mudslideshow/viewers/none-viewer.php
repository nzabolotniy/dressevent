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
function muds_show_none($image, $thumbnail, $title, $album=0, $float='center', $comment=false, $width=200, $url, $type, $conf=0, $widget=false) {
	global $wp_query, $open_search;
	
	$options=get_option('muds_options');
	$muds_showlink=$options['show_link'];
	
	$class = 'class="slideimg"';
	$class_float="";
	$center="";
	if($float=='center') $class_center=" align=\"center\"";
	$rand = mt_rand(111111,999999);
	
	//HTML specialchars
	$title = xmlentities($title);
	if($comment) $comment = xmlentities($comment);
	if($title=='--nn--') $title = __('No name', 'mudslide');
	$title_int = str_replace('&lt;', '&amp;lt;', $title_link);
	
	if($width!=0) $style_dim = " style='width: {$width}px; max-width: {$width}px;'";
		
	$sq = "";
	if(!(is_feed() || (!is_singular() && !in_the_loop()) )) {
		$sq = "<img class='slidecontainer' title='{$title}' alt='{$title}' src='".muds_plugin_url('img/link-bg.png')."' border='0' />";
	}
	
	$muds_showlink=$options['show_link'];
	$target = '_self';
	if($muds_showlink && $url) { $image = $url; $target = '_blank'; }
	
	$onclick = '';
	if($widget && $open_search) {
		$image = $widget;
		if(strpos($widget, get_bloginfo( 'siteurl')) === false)
			$target = '_blank';
		else {
			$target = '_self';
			$onclick = " onclick='muds_search();'";
		}
		$sq = '';
	}
	$img = "<div class='slideext slide{$float}'>
			<a target='$target' href='{$image}' rel='{$album}' title='{$title}'{$onclick}><img class='mudsphoto'{$style_dim} alt='{$title}' title='{$title}' src='{$thumbnail}' border='0' />{$sq}</a>
		</div>";
	return muds_general($img,$float);
}

/**
* This function returns the html code to show an image using feed-viewer
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
function muds_show_feed($image, $thumbnail, $title, $album=0, $float='center', $comment=false, $width=200, $url, $type, $conf=0) {
	global $wp_query;
	
	$options=get_option('muds_options');
	$muds_showlink=$options['show_link'];
	
	$class = 'class="slideimg"';
	$class_float="";
	$center="";
	if($float=='center') $class_center=" align=\"center\"";
	$rand = mt_rand(111111,999999);
	
	//HTML specialchars
	$title = xmlentities($title);
	if($comment) $comment = xmlentities($comment);
	if($title=='--nn--') $title = __('No name', 'mudslide');
	
	if($width!=0) $style_dim = " width='{$width}px' style='width: {$width}px; max-width: {$width}px; margin: 4px; padding: 4px; border: 1px solid #bbb;'";
	else $style_dim = " style='margin: 4px; padding: 4px; border: 1px solid #bbb;'";
	
	$muds_showlink=$options['show_link'];
	$target = '_self';
	if($muds_showlink && $url) { $image = $url; $target = '_blank'; }
	
	$img = "<a target='$target' href='{$image}' rel='{$album}' title='{$title}'><img class='muds-feed' {$style_dim} alt='{$title}' title='{$title}' src='{$thumbnail}' border='0' /></a>";
	return $img;
}

?>
