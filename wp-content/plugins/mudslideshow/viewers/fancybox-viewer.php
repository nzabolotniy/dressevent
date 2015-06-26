<?php

/**
* This function returns the html code to show an image using lytebox as the viewer
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
function muds_show_fb($image, $thumbnail, $title, $album=0, $float='center', $comment=false, $width=200, $url, $type, $conf=0) {
	$options=get_option('muds_options');
	$muds_showlink=$options['show_link'];
	
	$class_float = $caption = "";
	$rand = mt_rand(111111,999999);
	
	//HTML specialchars
	$title = xmlentities($title);
	if($comment) $comment = xmlentities($comment);
	$short = $title_alt = $short_alt = $title;
	$separator = " | ";
	if($title=='--nn--') { $title_alt = $short = __('No name', 'mudslide'); $short_alt =  $title = '';  $separator = '';}
	
	if($width!=0) $style_dim = " style='width: {$width}px; max-width: {$width}px;'";
	
	//The link to the photo source
	$link = "";
	if($muds_showlink && $url) {
		switch($type) {
			case MUDS_TYPE_PICASA:
				$link = "<a href=\"$url\" target=\"_blank\">".sprintf(__('Open in %s', 'mudslide'), 'Picasa')."</a>";
				break;
			case MUDS_TYPE_FLICKR:
				$link = "<a href=\"$url\" target=\"_blank\">".sprintf(__('Open in %s', 'mudslide'), 'Flickr')."</a>";
				break;
		}
	}
	
	//Caption
	if($muds_showlink && $url) $caption="<div class=\"muds-title\">$caption</div><div class=\"muds-link\">$link</div>";
	$caption_side="";
	if($comment) {
		$vote = "";
		if(function_exists('dmkti_image')) {
			if($type == MUDS_TYPE_NONE) {
				$vote = dmkti_image($image, $title, $comment, $mini);
			} else {
				$vote = dmkti_image($url, $title, $comment, $mini);
			}
			$vote = "<br>$vote";
		}
		$caption=apply_filters('comment_text', "<h3>".$title."</h3>".$comment);
		if($muds_showlink) $caption.="<div align=\"right\">$link</div>";
		$title = "<div id=\"mssfb-title\"><div class=\"fb-backgound\">$title</div><div align=\"right\">$link</div></div><div id=\"mssfb-caption\">$caption</div>";
	} else {
		if($muds_showlink && $url) $title.= $separator.$link;
	}
	
	$title = xmlentities($title);
	$caption = xmlentities($caption);
	
	if($album == 0) $album = mt_rand(111111,999999);

	$sq = "<img class='slidecontainer' title='{$short}' alt='{$short}' src='".muds_plugin_url('img/link-bg.png')."' border='0' />";
	
	$img = "<div class='slideext slide{$float}'>
			<a class='mssfb-image' target='_self' href='{$image}' rel='{$album}' title='{$title}'><img class='mudsphoto'{$style_dim} alt='{$short_alt}' title='{$short}' src='{$thumbnail}' border='0' />{$sq}</a>
		</div>";
	return muds_general($img,$float);
}

?>
