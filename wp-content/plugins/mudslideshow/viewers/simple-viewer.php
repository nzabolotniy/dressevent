<?php

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
function muds_show_simple_general($data, $update_post, $size, $float, $conf, $type, $rand=0) {

	global $post, $current_user;

	switch($type) {
		case MUDS_TYPE_PICASA:
			$size_aux = str_replace('-c', '', $size);
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
	
	//Calculate the position for the buttons
	$box_height=$size_aux+10;
	$box_width=$size_aux+10;
	$slidemeter_left=$size_aux-110;
	$slidemeter_top=$size_aux-35;
	
	if($rand==0) $rand = mt_rand(111111,999999); //The identifier
	$throbber="<div id='slidemeter$rand' class='slidemeter'></div>";
		
	//Section to update the gallery
	if($post->post_author == $current_user->id || current_user_can('edit_others_posts')) { //can this user edit the post
		//Add a AJAX call to update the gallery
		parse_str(str_replace('&amp;', '&', $update_post), $aux);
		if(!isset($aux['id'])) $aux['id']='false';
		if(!isset($aux['type'])) $aux['type']='false';
		if(!isset($aux['user'])) $aux['user']='false';
		if(!isset($aux['gallery'])) $aux['gallery']='false';
		$throbber="<div id='throbber-msspage$rand' class='throbbersimp-off' onclick=\"
					var aux = document.getElementById('throbber-msspage$rand');
					aux.setAttribute('class', 'throbbersimp-on');
					aux.setAttribute('className', 'throbbersimp-on'); //IE sucks
					muds_update( 'simple', {$aux['id']},'{$aux['type']}', '{$aux['user']}', '{$aux['gallery']}', '$size', '$float', $conf, $rand );\" />$throbber</div>
		";
	}
	
	//Data to align the frame
	if($float=='center') {
		$center=" align=\"center\"";
	}
	$class="";
	
	if($photo_gallery = new SimpleXMLElement($data)) { //Parse the data
		//Create the simple frame	
		$answer="<div class='simpleslidecontainer'  style='height : ".$box_height."px; width : ".$box_width."px;'>
			<div id='simple_gallery$rand' class='simpleslideimg' style='height : ".$size_aux."px; width : ".$size_aux."px;'></div>
			<div class='slidemetercontainer' align='center'><input type='hidden' id='actualPic$rand' value='0'/><input type='hidden' id='path$rand' value='".muds_plugin_url('/img/simpleslide/')."' />
			<table class='slidemeter'>
			<tr>
			<td width='25' align='center'>
			<input type='image' id='backslide$rand' src='".muds_plugin_url('/img/simpleslide/leftarrow.png')."' onclick='doPrevious($rand,slideImages$rand);' />
			</td>
			<td align='center'>$throbber</td>
			<td width='25' align='center'>
			<input type='image' id='nextslide$rand' src='".muds_plugin_url('/img/simpleslide/rightarrow.png')."' onclick='doNext($rand,slideImages$rand);' />
			</td>
			</tr>
			</table>
			</div>
			<script type='text/javascript' id='muds_script$rand'>
			var slideImages$rand = new Array(";
		//Create the array of photos
		$first=true;
		$first_image="";
		
		$total = count($photo_gallery->photo);
		$reverse = 0; 
		if($conf & MUDS_OPT_REVERSEORDER) {
			$reverse = $total - 1;
		}
		
		for($i = 0; $i < $total; $i++) { //gallery as $photo) { //Go trhough the gallery
			$photo = $photo_gallery->photo[abs($reverse - $i)];
			//Get the photo 
			$media = str_replace('%size%', $size, $photo->resize);
			if($type==MUDS_TYPE_FLICKR && $size=='l') $media = str_replace('_%size%', '', $photo->resize);
			
			if(!$first) {
				$answer.=",";
			} else {
				$first=false;
				$first_image=$media;
			}
			$answer.="'".$media."'";
		}
		$answer.=")
			var actualPic=document.getElementById('actualPic$rand').value;
			var div=document.getElementById('simple_gallery$rand');
			div.style.backgroundImage='url(".$first_image.")';
			arrows($rand,slideImages$rand);
			changeText($rand,slideImages$rand);
			preloadImages(slideImages$rand);
			</script>
			</div>";
		if($float=='center') $float="";
		
		//Ok, we have owr frame!!!!
		$answer="<div class=\"slide$float\"".$center.">$answer</div>";
	}
	
	return $answer;

}

?>
