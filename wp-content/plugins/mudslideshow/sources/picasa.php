<?php
/* Copyright 2007-2010 Juan Sebastián Echeverry (email : sebaxtian@gawab.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

/**
* Returns the user id given a mail, username or even the id
*
* @access public
* @param string The mail or username
* @return string The picasa id, false on error. 
*/
function mudsPicasa_check_user($user) {
	$url = "http://picasaweb.google.com/data/feed/api/user/$user?max-results=0";
	if($data = muds_readfile($url)) { //Ask Picasa
		if($data[0]=='<') { //Ok, we have an answer and it looks like an XML
			$data = new SimpleXMLElement($data); //Parse the XML
			if($data->title) { //Ok, we got a username
				$aux = (string) $data->title;
				if(!is_numeric($aux)) $user = $aux; //Users don't like numbers, but this user has an account in Picasa
			} else { //Dooo, we didn't found a username
				$user = false;
			}
		}
	}
	return $user;
}

/**
* Returns the last used username in the editor.
*
* @access public
* @return string The last used username in the editor. 
*/
function mudsPicasa_get_user() {
	$options = get_option('muds_options');
	return $options['user_picasa'];
}

/**
* Sets the user for future references.
*
* @access public
* @param string The username. 
*/
function mudsPicasa_set_user($new_user) {
	$options = get_option('muds_options');
	$options['user_picasa'] = $new_user;	
	$options['last_source'] = MUDS_TYPE_PICASA;
	update_option('muds_options', $options);
}

/**
* Returns user name.
*
* @access public
* @param string The username.
* @return string Username
*/
function mudsPicasa_get_username($userid) {
	$answer = $userid;
	return $answer;
}

/**
* Returns the list of galleries a user has.
*
* @access public
* @param string user The username in picasa.
* @return array The list of galleries. 
*/
function mudsPicasa_list_user_galleries($user) {
	//The default answer is an empty array, it means we can't read the file, nor create it. 
	$answer = array();
	
	//Ask for the user file 
	if(!get_transient("muds-pl-$user.xml")) { //If the user doesn't have a file yet
		mudsPicasa_update_user_galleries_list($user); //try to create it.
	}
	
	
	if($data = get_transient("muds-pl-$user.xml")) { //Try again to read the user file, maybe we have created it yet
		$data = new SimpleXMLElement($data); //Parse the XML file
		foreach($data->album as $album) { //Read each album
			$id = $album->id;
			$title = $album->title;
			array_push($answer, array('id'=>$id, 'title'=>$title)); //Fill the array with the data we need
		}
	}
	return $answer;
}

/**
* Updates the list of galleries a user has.
*
* @access public
* @param string user The username in picasa.
* @return bool True on succes, false if not. 
*/
function mudsPicasa_update_user_galleries_list($user) {
	//The default answer
	$answer = false;
	
	//A flag to know if we can save the file
	$save = false;
	
	//Start the XML file for the user's galleries.
	$out="<?xml version = '1.0' encoding = 'UTF-8'?><slide version='".MUDS_XML_V."' owner='$user'>";
	
	//Hello Picasa, we have a little question. Do you know this user? Can you send the
	//albums he has?
	$url = "http://picasaweb.google.com/data/feed/api/user/$user?max-results=".MUDS_HIGH;
	if($data = muds_readfile($url)) { //Ask picasa
		if($data[0]=='<') { //Ok, we have an answer and it looks like an XML
			$data = new SimpleXMLElement($data); //Parse the XML
			//$attr = $data->attributes();
			if($data) { //Picasa returns a string when it can't find the username, 
							//so if we don't have data it means the answer wasn't an XML  
				$save = true; //We are reading something, so we can save something
				
				$title=sprintf(__('Most Recent photos from %s', 'mudslide' ), mudsPicasa_get_username($user));
				$gallery_url = "http://picasaweb.google.com/$user/";
				$out.="<album><id>$user</id><title>$title</title><url>$gallery_url</url></album>"; //Add the entry to the XML file
				
				foreach($data->entry as $entry) { //Read each entry
					$id = $entry->xpath("gphoto:id"); //Get the id
					$id = $id[0];
					
					$title = xmlentities($entry->title, true); //Get the title
					//$title = str_replace("&", "&amp;", $title); //To save it correctly in the file and in MySQL
					//$title = str_replace(chr(34) , "&quot;", $title); //To save it correctly in the file and in MySQL
					
					$attr = $entry->link[1]->attributes();
					$gallery_url = $attr->href; //Get the link in Picasaweb
					
					$out.="<album><id>$id</id><title>$title</title><url>$gallery_url</url></album>"; //Add the entry to the XML file
				}
						
				//Hello Picasa, we again. Can you send the tags this user has?
				$url = "http://picasaweb.google.com/data/feed/api/user/$user?kind=tag&max-results=".MUDS_HIGH;
				if($data = muds_readfile($url)) { //Ask picasa
					$data = new SimpleXMLElement($data); //Parse the XML
					foreach($data->entry as $entry) { //Read each entry
						
						$tag = xmlentities($entry->summary, true); //Get the tag
						//$tag = str_replace("&", "&amp;", $tag); //To save it correctly in the file and in MySQL
						//$tag = str_replace(chr(34), "&quot;", $tag); //To save it correctly in the file and in MySQL
						$tag_id = $tag;
						
						//Get the tag link in Picasa
						$tag_url = sprintf('http://picasaweb.google.com/lh/view?uname=%s&amp;tags=%s', $user, $tag );
						
						if(is_numeric($tag_id)) { //A numeric tag can be a problem, rename it
							$tag_id = "tag#$tag#"; //Pray no one would use this kind of tags 
						}
						
						$out.="<album><id>$tag_id</id><title>".sprintf(__('Tag: %s', 'mudslide'), $tag)."</title><url>$tag_url</url></album>"; //Add the entry to the XML file
					}
				}
			}
		}
		
		$out.="</slide>";
		
		//Saves the file
		if($save) {	
			set_transient("muds-pl-$user.xml", $out, 60*60*24*365); //Update every year
			while (!get_transient("muds-pl-$user.xml")) {
				sleep(1);
			}
			$answer = true;
		}
	}
	
	return $answer;
}

/**
* Does an album existsts in the internal list?.
*
* @access public
* @param int album The album code to search.
* @return bool True on succes, false if not. 
*/
/*function mudsPicasa_album_exists( $album ) {
	global $wpdb;
	$table_name = $wpdb->prefix . "mudslide";
	$answer=false;
	if($wpdb->get_var("SELECT id FROM $table_name WHERE file='muds-pa-$album.xml'")) {
		$answer=true;
	}
	return $answer;
}*/

/**
* Does an tag album existsts in the internal list?.
*
* @access public
* @param int album The album code to search.
* @return bool True on succes, false if not. 
*/
/*function mudsPicasa_tag_exists( $tag ) {
	global $wpdb;
	
	if(is_numeric($tag)) { //A numeric tag can be a problem, rename it
		$tag = "tag#$tag#"; //Pray no one would use this kind of tags 
	}
	
	$table_name = $wpdb->prefix . "mudslide";
	$answer=false;
	if($wpdb->get_var("SELECT id FROM $table_name WHERE file='muds-pt-$tag.xml'")) {
		$answer=true;
	}
	return $answer;
}*/


/**
* Return the list of photos in a gallery.
*
* @access public
* @param string user The username in picasa.
* @param string gallery The id or tag from the gallery where we have to get the photos.
* @return array The list of photos, false if not. 
*/
function mudsPicasa_list_photos_in_gallery( $user, $gallery ) {
	$answer = array();
	$filename = false;
	if(strcmp($gallery, "0")==0) $gallery=$user;
	if(strcmp($user,$gallery)!=0 && !is_numeric($gallery)) { //If this is a tag
		$filename = "muds-pt-$gallery.xml";
		if(muds_not_readable_file($filename)) { //Do we have this album file?
			mudsPicasa_update_tag($user, $gallery); //No! Try to create it
		}
	} else { //If it's an album
		$filename = "muds-pa-$gallery.xml";
		if(muds_not_readable_file($filename)) { //Do we have this album file?
			if(strcmp($user,$gallery)==0) { //A zero album
				mudsPicasa_update_zero($user);
			} else {
				mudsPicasa_update_album($user, $gallery); //No! Try to create it
			}
		}
	}
	
	if($filename) { //So, do we have a file?
		if($data = get_transient($filename)) { //Ok, try again to read it.
			$data = new SimpleXMLElement($data); //Parse the XML
			$id=1;
			foreach($data->photo as $photo) { //Read each entry
				$title = $photo->title;
				array_push($answer, array('id'=>$id, 'title'=>$title)); //Add the photo to the array
				$id++;
			}
		}
	}
	
	return $answer;
}

/**
* Updates a gallery.
*
* @access public
* @param string user The username in picasa.
* @param int album The id of the album from where we have to get the photos.
* @return array The list of photos, false if not. 
*/
function mudsPicasa_update_gallery( $user, $gallery ) {
	$answer = false;
	extract(muds_detect_type($user, $gallery, MUDS_TYPE_PICASA));
	
	switch($album_type) {
		case MUDS_FILE_ZERO:
			$answer = mudsPicasa_update_zero( $user ); //Call the function to update the Zero gallery
			break;
		case MUDS_FILE_TAG:
			$answer = mudsPicasa_update_tag( $user, $gallery ); //Call the function to update the tag gallery
			break;
		case MUDS_FILE_ALBUM:
			$answer = mudsPicasa_update_album( $user, $gallery ); //Call the function to update the album
			break;
		case MUDS_FILE_FEED:
			$user = urldecode($user);
			$answer = mudsPicasa_update_feed( $user ); //Call the function to update the album
			break;
	}
	return $answer;
}

/**
* Updates a gallery using the filename.
*
* @access public
* @param string filename The file to check.
* @return bool
*/
function mudsPicasa_update_gallery_by_filename( $filename ) {
	$answer = false;
	
	$data = muds_get_gallery_data($filename);
	$data = explode('|', $data);
	if(strstr($data[0], 'url=')) {
		$url = substr($data[0],4);
		$answer = mudsPicasa_update_feed( $url );
	}
	if(strstr($data[1], 'album=')) {
		$user = substr($data[0],5);
		$album = substr($data[1],6);
		if(strcmp($user,$album)!=0) {
			$answer = mudsPicasa_update_album( $user, $album );
		} else {
			$answer = mudsPicasa_update_zero( $user );
		}
	}
	if(strstr($data[1], 'tag=')) {
		$user = substr($data[0],5);
		$album = substr($data[1],4);
		$answer = mudsPicasa_update_tag( $user, $album );
	}
		
	return $answer;
}

/**
* Updates the album zero.
*
* @access public
* @param string user The username in Picasa.
* @return array The list of photos, false if not. 
*/
function mudsPicasa_update_zero( $user ) {
	$fwr = false;
	$album = $user;
	$options = get_option('muds_options');
	$rows = $options['rows'];
	if($rows == NULL) $rows = 3;
	$per_page = $options['columns'] * $rows; //
	//Hello Picasa, we again. Can you show the photos from this album?
	$url = "http://picasaweb.google.com/data/feed/api/user/$user?kind=photo&max-results=$per_page";
	$gallery_title=sprintf(__('Most Recent photos from %s', 'mudslide' ), mudsPicasa_get_username($user));
	$out=false;
	if($data = muds_readfile($url)) { //Ask Picasa
		$data = new SimpleXMLElement($data); //Parse the XML
		//$attr = $data->attributes();
		if($data) { //The answer is correct 
			$gallery_url = "http://picasaweb.google.com/$user/";
			
			//Creates the XML data
			$out="<?xml version = '1.0' encoding = 'UTF-8'?><album version='".MUDS_XML_V."' description='$gallery_title' owner='$user' id='$album' url='$gallery_url'>"; //Our XML header
			foreach($data->entry as $entry) {
				$title = xmlentities($entry->summary, true); //Get the title
				//$title = str_replace("&", "&amp;", $title); //To save it correctly in the file and in MySQL
				//$title = str_replace(chr(34), "&quot;", $title); //To save it correctly in the file and in MySQL
				if(strlen($title)==0) $title = "--nn--"; //If there are not title, use a dummy title.
				
				$id = $entry->xpath("gphoto:id"); //Get the id from the photo
				$id = $id[0];
				
				//Get dimmension
				$width = $entry->xpath("gphoto:width"); //Get the width from the photo
				$width = $width[0];
				$height = $entry->xpath("gphoto:height"); //Get the height from the photo
				$height = $height[0];
				
				//Get the link in Picasaweb
				$attr2 = $entry->link[1]->attributes();
				$photo_url = $attr2->href;
				
				//Get the source file
				$attr3 = $entry->content->attributes();
				$src = $attr3->src;
				
				//Get the url to resize a file
				$dirname=dirname($src);
				$basename=basename($src);
				$resize = "$dirname/s%size%/$basename";
				
				/* Just while i fuger how to get the comments in the same API call
				//Get the comment
				$comment="";
				//Hello picasa, we again. Can you send the first comment this photo have?
				$photo_uri="http://picasaweb.google.com/data/feed/api/user/$user/photoid/$id?kind=comment&max-results=1";

				if($data_comment = muds_readfile($photo_uri)) { //Ask picasa
					$rss_comment = new SimpleXMLElement($data_comment); //Parse the XML
					$entry=$rss_comment->entry;
					//If there is a comment
					if($entry) {
						$comment = xmlentities($entry->content, true);
						//$comment = str_replace("&", "&amp;", $comment); //To save it correctly in the file and in MySQL
						//$comment = str_replace(chr(34), "&quot;", $comment); //To save it correctly in the file and in MySQL
					}
				}*/
				
				//Create the XML entry
				$out.="<photo album='$album' id='$id' url='$photo_url'><title>$title</title><comment>$comment</comment><src>$src</src><resize>$resize</resize><width>$width</width><height>$height</height></photo>";
			}
			$out.="</album>"; //Close the XML file
		}
	
		if($out) { //If we have something to save
		
			$filename = "muds-pa-$album.xml";
			$answer = set_transient($filename, $out, 60*60*24*365); //Update every year
			while (!get_transient($filename)) {
				sleep(1);
			}
			
			$fwr = true;
			muds_set_album($filename, MUDS_PICASA, $gallery_title, "user=$user|album=$album");
		}
	}
	
	return $fwr;
}

/**
* Updates an album.
*
* @access public
* @param string user The username in picasa.
* @param int album The id of the album from where we have to get the photos.
* @return array The list of photos, false if not. 
*/
function mudsPicasa_update_album( $user, $album ) {
	$fwr = false;
	if($album>0) { //If it is a number
		//Hello picasa, we again. Can you show the photos from this album?
		$out = false;
		$url = "http://picasaweb.google.com/data/feed/api/user/$user/albumid/$album?kind=photo,comment&max-results=".MUDS_HIGH;
		$gallery_title=__('Album doesn\'t exist', 'mudslide' ); //Maybe we are wrong, so this is to create a dummy answer
		if($data = muds_readfile($url)) { //Ask picasa
			if($data[0]=='<') { //Ok, we have an answer and it looks like an XML
				$data = new SimpleXMLElement($data); //Parse the XML
				$gallery_title = xmlentities($data->title, true); //Get the title
				//$gallery_title = str_replace("&", "&amp;", $gallery_title); //To save it correctly in the file and in MySQL
				//$gallery_title=str_replace(chr(34), "&quot;", $gallery_title); //To save it correctly in the file and in MySQL
				$attr = $data->link[1]->attributes();
				$gallery_url = $attr->href; //Get the link in Picasaweb
				//Creates the XML data
				$gallery = "";
				$out="<?xml version = '1.0' encoding = 'UTF-8'?><album version='".MUDS_XML_V."' description='$gallery_title' owner='$user' id='$album' url='$gallery_url'>"; //Our XML header
				foreach($data->entry as $entry) {	
					//Is a comment?
					if(strstr ( $entry->id , "/commentid/" )) {
						$id = $entry->xpath("gphoto:photoid"); //Get the id from the photo
						$id = (string)$id[0];
						$aux = $gallery[$id];
						$comment = (string)$entry->content;
						$comment = xmlentities($comment, true);
						$aux['comment']=$comment;
						$gallery[$id] = $aux;
					} else { //Must be a photo
						$title = xmlentities($entry->summary, true); //Get the title
						if(strlen($title)==0) $title = "--nn--"; //If there are not title, use a dummy title.
						
						$id = $entry->xpath("gphoto:id"); //Get the id from the photo
						$id = (string)$id[0];
						
						//Get dimmension
						$width = $entry->xpath("gphoto:width"); //Get the width from the photo
						$width = $width[0];
						$height = $entry->xpath("gphoto:height"); //Get the height from the photo
						$height = $height[0];
						
						//Get the link in Picasaweb
						$attr = $entry->link[1]->attributes();
						$photo_url = $attr->href;
						
						//Get the source file
						$attr2 = $entry->content->attributes();
						$src = $attr2->src;
						
						//Get the url to resize a file
						$dirname=dirname($src);
						$basename=basename($src);
						$resize = "$dirname/s%size%/$basename";
						
						//Get the comment
						$comment="";
						
						$aux['title']=$title;
						$aux['id']=$id;
						$aux['album']=$album;
						$aux['photo_url']=$photo_url;
						$aux['src']=$src;
						$aux['resize']=$resize;
						$aux['comment']='';
						$aux['width']=$width;
						$aux['height']=$height;
						$gallery[$id] = $aux;
						
					}
				}
				foreach($gallery as $entry) {
					//Create the XML entry
					$title = $entry['title'];
					$id = $entry['id'];
					$album = $entry['album'];
					$photo_url = $entry['photo_url'];
					$src = $entry['src'];
					$resize = $entry['resize'];
					$comment = $entry['comment'];
					$width = $entry['width'];
					$height = $entry['height'];
					$out.="<photo album='$album' id='$id' url='$photo_url'><title>$title</title><comment>$comment</comment><src>$src</src><resize>$resize</resize><width>$width</width><height>$height</height></photo>";
				}
				$out.="</album>"; //Close the XML file
			}
		}
		
		if($out) { //If we have something to save	
			$filename = "muds-pa-$album.xml";
			$answer = set_transient($filename, $out, 60*60*24*365); //Update every year
			while (!get_transient($filename)) {
				sleep(1);
			}
			
			$fwr = true;
			muds_set_album($filename, MUDS_PICASA, $gallery_title, "user=$user|album=$album");
		}
	}
	
	return $fwr;
}

/**
* Updates a tag album.
*
* @access public
* @param string user The username in picasa.
* @param int album The id of the tag album from where we have to get the photos.
* @return array The list of photos, false if not. 
*/
function mudsPicasa_update_tag( $user, $tag ) {
	//We put some code with the numeric tags, we have to delete it to use here
	$search = "@tag#(\d+)#@i";
	if(preg_match_all($search, $tag, $matches)) {
		if (is_array($matches)) {
			foreach ($matches[1] as $key =>$v0) {
				$tag=$matches[1][$key]; //if we found something, use it
			}
		}
	}

	//Hello picasa, we again. Can you show the photos with this tag?
	$url = "http://picasaweb.google.com/data/feed/api/user/$user?kind=photo,comment&tag=$tag&max-results=".MUDS_HIGH; 
	$gallery_title=__('Album doesn\'t exist', 'mudslide' ); //Maybe we are wrong, so this is to create a dummy answer
	$out = false;
	if($data = muds_readfile($url)) { //Ask picasa
		if($data[0]=='<') { //Ok, we have an answer and it looks like an XML
			$data = new SimpleXMLElement($data); //Parse the XML
			//Get the tag link in Picasa
			$gallery_url = sprintf('http://picasaweb.google.com/lh/view?uname=%s&amp;tags=%s', $user, $tag );
			$gallery_title = xmlentities(sprintf(__('Tag: %s', 'mudslide'), $tag), true); //This is our gallery title
			//$gallery_title = str_replace("&", "&amp;", $gallery_title); //To save it correctly in the file and in MySQL
			//$gallery_title = str_replace(chr(34), "&quot;", $gallery_title); //To save it correctly in the file and in MySQL
			//Start the XML
			$out="<?xml version = '1.0' encoding = 'UTF-8'?><tag version='".MUDS_XML_V."' description='$gallery_title' owner='$user' tag='$tag' url='$gallery_url'>";
			$n=1;
			$gtallery = "";
			foreach($data->entry as $entry) {
				//Is a comment?
				if(strstr ( $entry->id , "/commentid/" )) {
					$id = $entry->xpath("gphoto:photoid"); //Get the id from the photo
					$id = (string)$id[0];
					$aux = $gallery[$id];
					$comment = (string)$entry->content;
					$comment = xmlentities($comment, true);
					$aux['comment']=$comment;
					if($gallery[$id]) $gallery[$id] = $aux;
				} else { //Must be a photo
					$title = xmlentities($entry->summary, true); //Get the title
					//$title = str_replace("&", "&amp;", $title); //To save it correctly in the file and in MySQL
					//$title = str_replace(chr(34), "&quot;", $title); //To save it correctly in the file and in MySQL
					if(strlen($title)==0) $title = "--nn--"; //If there are not title, use a dummy title.
					
					$id = $entry->xpath("gphoto:id"); //Get the id from the photo
					$id = (string)$id[0];
					
					$album = $entry->xpath("gphoto:albumid"); //Get the album id from the photo
					$album = $album[0];
					
					//Get dimmension
					$width = $entry->xpath("gphoto:width"); //Get the width from the photo
					$width = $width[0];
					$height = $entry->xpath("gphoto:height"); //Get the height from the photo
					$height = $height[0];
					
					//Get the link in Picasaweb
					$attr = $entry->link[1]->attributes();
					$photo_url = $attr->href;
					
					//Get the source file
					$attr2 = $entry->content->attributes();
					$src = $attr2->src;
					
					//Get the url to resize a file
					$dirname=dirname($src);
					$basename=basename($src);
					$resize = "$dirname/s%size%/$basename";
						
					$aux['title']=$title;
					$aux['id']=$id;
					$aux['album']=$album;
					$aux['photo_url']=$photo_url;
					$aux['src']=$src;
					$aux['resize']=$resize;
					$aux['comment']='';
					$aux['width']=$width;
					$aux['height']=$height;
					$gallery[$id] = $aux;
					
				}
				
			}
			
			//Create the XML entry
			foreach($gallery as $entry) {
				//Create the XML entry
				$title = $entry['title'];
				$id = $entry['id'];
				$album = $entry['album'];
				$photo_url = $entry['photo_url'];
				$src = $entry['src'];
				$resize = $entry['resize'];
				$comment = $entry['comment'];
				$width = $entry['width'];
				$height = $entry['height'];
				$out.="<photo album='$album' id='$id' url='$photo_url'><title>$title</title><comment>$comment</comment><src>$src</src><resize>$resize</resize><width>$width</width><height>$height</height></photo>";
			}
			$out.="</tag>"; //Close the XML
		}
	}
	
	if($out) { //Do we have something to show?
		$tag_id = (string)$tag;
		if(is_numeric($tag_id)) { //A numeric tag can be a problem, rename it
			$tag= "tag#$tag#"; //Pray no one would use this kind of tags 
		}
		
		$filename = "muds-pt-$tag.xml";
		$answer = set_transient($filename, $out, 60*60*24*365); //Update every year
		while (!get_transient($filename)) {
			sleep(1);
		}
		
		$fwr = true;
		muds_set_album($filename, MUDS_PICASA, $gallery_title, "user=$user|tag=$tag");
	}
	
	return $fwr;
}

/**
* Updates feed.
*
* @access public
* @param string url The feed url.
* @return array The list of photos, false if not. 
*/
function mudsPicasa_update_feed( $url ) {
	$fwr = false;
	$out = false;
	$url = muds_decode($url);
	$md5 = md5($url);
	$gallery_title=__('Album doesn\'t exist', 'mudslide' ); //Maybe we are wrong, so this is to create a dummy answer
	if(class_exists('SimplePie') && strstr($url, "http://picasaweb.google.com/") ) { // Does simplepie library exists? Is this a Picasa feed?
		$rss = new SimplePie(); //Get the data from the rss
		$rss->set_feed_url($url); //Set the feed url
		$rss->enable_cache(false); //Disable cache
		$rss->enable_order_by_date(false); //Come as you are
		$rss->init(); //Get the feed

		$gallery_url = $rss->get_permalink(); //Ǵet the link to the page (not to the RSS) from the feed
		$gallery_title = htmlspecialchars($rss->get_title()); //If we don't have a title, use the name from the feed
		
		//Get the items to show
		$items = $rss->get_items();
		$out="<?xml version = '1.0' encoding = 'UTF-8'?><album version='".MUDS_XML_V."' description='$gallery_title' owner='" . urlencode($url) ."' id='$md5' url='$gallery_url'>"; //Our XML header */
		foreach($rss->get_items() as $entry) {
			$photo_url = htmlspecialchars($entry->get_permalink());
			$id = 0;
			$album = $md5;
			$title = $entry->get_title();
			$comment = "";
			$enc =  $entry->get_enclosure();
			$src = $enc->link;
			$width = $enc->width;
			$height = $enc->height;
			$resize = $enc->get_thumbnail();
			$resize = str_replace('/s72/', '/s%size%/', $resize); //Picasa!!!! 
			
			$out.="<photo album='$album' id='$id' url='$photo_url'><title>$title</title><comment>$comment</comment><src>$src</src><resize>$resize</resize><width>$width</width><height>$height</height></photo>";
		}
		$out.="</album>"; //Close the XML file
	}
		
	if($out) { //If we have something to save	
	
		$filename = "muds-pf-$md5.xml";
		$answer = set_transient($filename, $out, 60*60*24*365); //Update every year
		while (!get_transient($filename)) {
			sleep(1);
		}
		
		$fwr = true;
		muds_set_album($filename, MUDS_PICASA, $gallery_title, "url=$url");
	}
	
	return $fwr;
}

/**
* Returns the HTML code of a gallery. If the user can modify the post this function
* also puts a link to update he gallery.
*
* @access public
* @param string user The username in picasa.
* @param string gallery The id of the gallery from where we have to get the photos.
* @param int first The first photo of the list to show.
* @param int last The last photo of the list to show.
* @param int conf A numeric value for the configuration. 1 to show the first comment
	in picasaweb as a big description.
* @return string The HTML code for the gallery the photos, false if not. 
*/
function mudsPicasa_show_gallery( $user, $gallery, $first=0, $last=0, $conf=0, $rand=0 ) {
	$answer = "";
	extract(muds_detect_type($user, $gallery, MUDS_TYPE_PICASA));
	
	$update_post = "type=picasa&amp;user=".urlencode($user)."&amp;gallery=$gallery"; //Create the update post
	if(!muds_not_readable_file($filename)) { //If it's a readable gallery
		//We have data, so we can try to show the gallery.
		$answer = muds_gallery(get_transient($filename), $update_post, $first, $last, $conf, MUDS_TYPE_PICASA, $rand);
	} else { //Oops, something goes wrong, it is not a readable gallery
		if(mudsPicasa_update_gallery( $user, $gallery )) { //Try to create it
			$answer = mudsPicasa_show_gallery( $user, $gallery, $first, $last, $conf, $rand ); //If we created it, show the gallery
		} else { //Oops, we can't create it
			$answer=__('XML File doesn\'t exist', 'mudslide' );
		}
	}
	return $answer;
}

/**
* Returns the HTML code of a simple frame gallery. If the user can modify the post this function
* also puts a link to update he gallery.
*
* @access public
* @param string user The username in picasa.
* @param string gallery The id of the gallery from where we have to get the photos.
* @param int conf A numeric value for the configuration.
* @return string The HTML code for the gallery the photos, false if not. 
*/
function mudsPicasa_show_simple($user, $gallery, $size=200, $float=false, $conf=0, $rand=0) {
	$answer = "";
	extract(muds_detect_type($user, $gallery, MUDS_TYPE_PICASA));
	
	$update_post = "type=picasa&amp;user=".urlencode($user)."&amp;gallery=$gallery"; //Create the update post
	
	if(!muds_not_readable_file($filename)) { //If it's a readable file
		//We have data, so we can try to show the simple album.
		$size = str_replace('c', '-c', $size);
		$answer = muds_show_simple(get_transient($filename), $update_post ,$size, $float, $conf, MUDS_TYPE_PICASA, $rand);
	} else { //Oops, something goes wrong, it is not a readable gallery.
		if(mudsPicasa_update_gallery( $user, $gallery )) { //Try to create it
			$answer = mudsPicasa_show_simple($user, $gallery, $size, $float, $conf, $rand); //If we created it, show the gallery
		} else { //Oops, we can't create it
			$answer=__('XML File doesn\'t exist', 'mudslide' );
		}
	}
	return $answer;
}

/**
* Returns the HTML code of a single photo.
*
* @access public
* @param string user The username in picasa. Can be NO_USER
* @param string gallery The id of the gallery from where we have to get the photo.
* @param int p_id The position of the photo in the gallery. Can be also LAST_PHOTO or RANDOM_PHOTO.
* @param int size The size of the photo in the post.
* @param string float Where the photo has to float.
* @param int conf A numeric value for the configuration.
* @return string The HTML code for the gallery the photos, false if not. 1 to show the first comment
	in picasaweb as a big description. 
*/
function mudsPicasa_show_photo( $user, $gallery, $p_id, $size=200, $float=false, $conf=0 ) {
	$answer = "";
	extract(muds_detect_type($user, $gallery, MUDS_TYPE_PICASA));
	
	if(!muds_not_readable_file($filename)) { //If it's a readable file
		$size = str_replace('c', '-c', $size);
		//We have data, so we can try to show the photo.
		$answer = muds_photo(get_transient($filename), $p_id, $size, $float, $conf, MUDS_TYPE_PICASA);
	} else { //Oops, something goes wrong, we don't have the file.
		//Try to create it if we have the user. The widget doesn't know it.
		if($user && mudsPicasa_update_gallery( $user, $gallery )) {
			$answer = mudsPicasa_show_photo( $user, $gallery, $p_id, $size, $float, $conf ); //If we created the gallery, show the photo
		} else { //Oops, we can't create the gallery
			$answer=__('XML File doesn\'t exist', 'mudslide' );
		}
	}
	
	return $answer;
}

/**
* Return the HTML code for the photo in the widget.
*
* @access public
* @param string filename The file of the album to select from.
* @param int p_id The position of the photo in the gallery.
* @return string The HTML code. Empty if error. 
*/
function mudsPicasa_widget_photo($filename, $p_id, $size=200) {
	$answer = "";
	
	if(!muds_not_readable_file($filename)) { //If it's a readable file
		$size = str_replace('c', '-c', $size);
		//We have data, so we can try to show the photo.
		$answer = muds_photo(get_transient($filename), $p_id, $size, 'center', 0, MUDS_TYPE_PICASA, true);
	} else { 
		//Try to update the gallery.
		if(mudsPicasa_update_gallery_by_filename( $filename )) { //If we can update the file
			//We have data, so we can try to show the photo.
			$answer = mudsPicasa_widget_photo($filename, $p_id, $size);
		} else { 
			$answer = __('XML File doesn\'t exist', 'mudslide' );
		}
	}
	
	return $answer;
}

/**
* Return the HTML code for the post thumbnail.
*
* @access public
* @param string user The file of the album to select from.
* @param string gallery The position of the photo in the gallery.
* @return string The HTML code. Empty if error. 
*/
function mudsPicasa_thumbnail($user, $gallery, $num) {
	$image = false;
	$pass = false;
	extract(muds_detect_type($user, $gallery, MUDS_TYPE_PICASA));
	if(!muds_not_readable_file($filename)) { //If it's a readable gallery
		$pass =true;
	} else {
		$pass = mudsPicasa_update_gallery( $user, $gallery );
	}
	
	if($pass) {
		//We have data, so we can try to show the gallery.
		//$answer = muds_gallery(get_transient($filename), $update_post, $first, $last, $conf, MUDS_TYPE_PICASA, $rand);
		$data = get_transient($filename);
		$photo_gallery = new SimpleXMLElement($data);
		$aux = $photo_gallery->photo[$num-1];
		$image = array((string)$aux->title, (string)$aux->resize, (string)$aux->src, 'picasa');
	}
	return $image;
}

?>
