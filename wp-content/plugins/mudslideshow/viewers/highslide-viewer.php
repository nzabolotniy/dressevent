<?php

/**
* This function returns the html code to show an image using highslide as the viewer
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
function muds_show_hs($image, $thumbnail, $title, $album=0, $float='center', $comment=false, $width=200, $url, $type, $conf=0) {
	$options=get_option('muds_options');
	$muds_showlink=$options['show_link'];
	
	$class = 'class="slideimg"';
	$class_float="";
	$center="";
	if($float=='center') $class_center=" align=\"center\"";
	
	if($album == 0) $album = mt_rand(111111,999999);
	$hsGallery = "hsGallery$album";
	$rand = mt_rand(111111,999999);
	
	//HTML specialchars
	$title = xmlentities($title);
	if($comment) $comment = xmlentities($comment);
	$short = $title_alt = $short_alt = $title;
	$title = str_replace('&lt;', '&amp;lt;', $title);
	$comment = str_replace('&lt;', '&amp;lt;', $comment);
	if($title=='--nn--') { $title_alt = $short = __('No name', 'mudslide'); $short_alt =  $title = '';}
	
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
	
	$caption=$title;
	if($muds_showlink && $url) $caption="<div class=\"muds-title\">$caption</div><div class=\"muds-link\">$link</div>";
	$caption_side="";
	if($conf & MUDS_OPT_COMMENT) {
		$caption=apply_filters('comment_text', "<div class=\"comments\"><h3>".$title."</h3>".$comment."</div>");
		if($muds_showlink) $caption.="<div class=\"muds-link\" align=\"right\">$link</div>";
	}
	
	if($width!=0) $style_dim = " style='width: {$width}px; max-width: {$width}px;'";
	
	$title = str_replace(array('<', '>'), array('&lt;', '&gt;'), $title);
	$caption = str_replace(array('<', '>'), array('&lt;', '&gt;'), $caption);
	
	$sq = "<img class='slidecontainer' title='{$short}' alt='{$short}' src='".muds_plugin_url('img/link-bg.png')."' border='0' />";
	
	$img = "<div class='slideext slide{$float}'>
			<a id='thumb{$rand}' class='mssfb-image' target='_self' href='{$image}' rel='{$album}' title='{$caption}' onclick='return hs.expand(this, {$hsGallery} );'><img class='mudsphoto'{$style_dim} alt='{$title}' title='{$short}' src='{$thumbnail}' border='0' />{$sq}</a>
		</div>";
	return muds_general($img,$float);
}

/**
* Create the HTML code for a simple gallery
*
* @access public
* @param xml data The data description of the gallery.
* @param string update_post The post elements for the data.php script to update this gallery.
* @param int size The size of the frame.
* @param string float The side where the frame has to float.
* @param int conf A configuration number for future releases.
* @return string The HTML code for the simple frame viewer.
*/
function muds_show_simple_hs($data, $update_post, $size, $float, $conf, $type, $rand=0) {
	global $post, $current_user;
	$options=get_option('muds_options');
	$muds_showlink=$options['show_link'];
	

	switch($type) {
		case MUDS_TYPE_PICASA:
			$size_aux = $size;
			break;
		case MUDS_TYPE_FLICKR:
			switch($size) {
				case 's':
					$size_aux=75;
					break;
				case 't':
					$size_aux=100;
					break;
				case 'm':
					$size_aux=240;
					break;
				case 'l':
					$size_aux=500;
					break;	
			}
			break;
		
	}
	
	$width = $size_aux;
	$width_frame = $width + 10;
	$height = ceil(($size_aux * 2) / 3);
	$height_frame = $height + 105;
	
	$updatelink = false;
	
	//Section to update the gallery
	//Section to update the gallery
		if($post->post_author == $current_user->id || current_user_can('edit_others_posts')) { //Can this user edit the post
			parse_str(str_replace('&amp;', '&', $update_post), $aux);
			if(!isset($aux['id'])) $aux['id']='false';
			if(!isset($aux['type'])) $aux['type']='false';
			if(!isset($aux['user'])) $aux['user']='false';
			if(!isset($aux['gallery'])) $aux['gallery']='false';
			
			$updatelink = "<div id='throbber-msspage$rand' class='throbber-off'><a style='cursor : pointer;' id='mss-update-$rand' onclick=\"
					var aux = document.getElementById('throbber-msspage$rand');
					aux.setAttribute('class', 'throbber-on');
					aux.setAttribute('className', 'throbber-on'); //IE sucks
					muds_update( 'simple', {$aux['id']},'{$aux['type']}', '{$aux['user']}', '{$aux['gallery']}', '$size', '$float', $conf, $rand );\">".__('Update','mudslide')."</a></div>
		";
	}
	
	//Data to align the frameSyntaxError
	if($float=='center') {
		$center=" align=\"center\"";
	}
	$class="";
	/*if($float) {
		$class=" class=\"$align\"";
	}*/
	
	//$size_m = $size_aux+10;
	//$height = $size_m+80;
	
	//Data to align the frame
	if($float=='center') {
		$center=" align=\"center\"";
	}
	$class="";
	/*if($float) {
		$class=" class=\"$align\"";
	}*/
	
	$answer = "<div class='simpleHSslidecontainer' style='width: {$width_frame}px; height: {$height_frame}px;' ><div class='hidden-container' class='simpleHSslideimg'> ";
	if($photo_gallery = new SimpleXMLElement($data)) {
		
		//Create the array of photos
		$first=true;
		$total = count($photo_gallery->photo);
		$reverse = 0; 
		if($conf & MUDS_OPT_REVERSEORDER) {
			$reverse = $total - 1;
		}
		
		for($i = 0; $i < $total; $i++) { //gallery as $photo) { //Go trhough the gallery
			$photo = $photo_gallery->photo[abs($reverse - $i)];
			
			//HTML specialchars
			$title_alt = xmlentities($photo->title);
			$comment_alt = xmlentities($photo->comment);
			$title = str_replace('&lt;', '&amp;lt;', $title_alt);
			$comment = str_replace('&lt;', '&amp;lt;', $comment_alt);
			if($title == '--nn--') { $title_alt = __('No name', 'mudslide'); $title = ''; }
			
			$attr = $photo->attributes();
			$url = $attr->url;
			
			//The link to the photo source
			$link = "";
			switch($type) {
				case MUDS_TYPE_PICASA:
					$link = "<a href='$url' target='_blank'>".sprintf(__('Open in %s', 'mudslide'), 'Picasa')."</a>";
					break;
				case MUDS_TYPE_FLICKR:
					$link = "<a href='$url' target='_blank'>".sprintf(__('Open in %s', 'mudslide'), 'Flickr')."</a>";
					break;
				default:
					$link="";
					break;
			}
	
			$caption=$title;
			if($muds_showlink && $url) $caption="<div class='muds-title'>$caption</div><div class='muds-link'>$link</div>";
			
			$title = str_replace(array('<', '>'), array('&lt;', '&gt;'), $title);
			$caption = str_replace(array('<', '>'), array('&lt;', '&gt;'), $caption);
			
			//Get the photo
			$media = str_replace('%size%', $size, $photo->resize);
			switch($type) {
				case MUDS_TYPE_PICASA:
					$thumbnail = str_replace('%size%', '75', $photo->resize);
					break;
				case MUDS_TYPE_FLICKR:
					$thumbnail = str_replace('%size%', 's', $photo->resize);
					break;
			}
			
			if($type==MUDS_TYPE_FLICKR && $size=='l') $media = str_replace('_%size%', '', $photo->resize);
			
			if($first) {
				$answer.="<a id='thumb$rand' class='highslide' href='$media' onclick='return hs.expand(this, hsSimple$rand)' title=\"$caption\"><img src='$thumbnail' alt='$title' title='$title_alt'/></a>
				";
				$first = false;
			} else {
				$answer.="<a class='highslide' href='$media' onclick='return hs.expand(this, hsSimple$rand)' title=\"$caption\"><img src='$thumbnail' alt='$title' title='$title_alt'/></a>
				";
			}
			//$answer.="<div class='highslide-caption' id='mudscaption$rand'>".$caption."</div>";
		}
	}
	
	$answer.= "</div></div>";
	$height_frame_aux = $height_frame + 25; 
	if($updatelink) $height_frame_aux = $height_frame + 35;
	$answer = "<div id='simple_gallery$rand' style='width: {$width_frame}px; height: {$height_frame_aux}px'>$answer $updatelink</div>";
	
	if($float=='center') $float="";
		
	//Ok, we have our frame!!!!
	$answer="<div class=\"slide$float\"".$center.">$answer</div>";
	
	$num = 5;
	if(function_exists('is_admin_bar_showing')) {
		if(is_admin_bar_showing()) $num = $num + 30;
	}
	
	$answer.="<script type='text/javascript' id='muds_script$rand'>
	var hsSimple$rand = {
		slideshowGroup: 'group$rand',
		thumbnailId: 'simple_gallery$rand',
		width: {$width},
		minWidth: {$width},
		height: {$height},
		minHeight: {$height},
		targetX: 'simple_gallery$rand 5px',
		targetY: 'simple_gallery$rand {$num}px'
	}
	
	for (attrname in hsSimple) { hsSimple{$rand}[attrname] = hsSimple[attrname]; }
	hs.Expander.prototype.onAfterExpand
	
	init_hs$rand = function() {
		if( hs_sem.isGreen() ) {
			hs_sem.setRed();
			document.getElementById('thumb$rand').onclick();
		} else {
			setTimeout(function (){ init_hs$rand(); }, 300);
		}
	}
	
	init_hs$rand();
	
	hs.addEventListener(window, 'load', function() {
		hs_sem.setGreen();
	});
	
	</script>";
	return $answer;
}

?>
