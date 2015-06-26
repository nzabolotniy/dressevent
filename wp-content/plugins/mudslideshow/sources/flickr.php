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

define('MUDS_FLICKR_API', '4b6b993ed98c459f717e4468c7059ec9');

/**
* Returns the user id given a mail, username or even the id
*
* @access public
* @param string The mail, username or id to check
* @return string The flickr id, false on error. 
*/
function mudsFlickr_check_user($user) {
	$search = "/^[A-z0-9]+@[A-z0-9]+$/";
	if(!preg_match($search, $user)) { //It doesn't look like a Flickr ID
		$search = "/^[\.A-z0-9_]+@[\.A-z0-9_]+$/";
		if(preg_match($search, $user)) { //Is an email?
			$url = "http://api.flickr.com/services/rest/?method=flickr.people.findByEmail&api_key=". MUDS_FLICKR_API ."&find_email=".$user;
		} else { //It's a username
			$url = "http://api.flickr.com/services/rest/?method=flickr.people.findByUsername&api_key=". MUDS_FLICKR_API ."&username=".$user;
		}
			
		if($data = muds_readfile($url)) { //Ask flickr
			$data = new SimpleXMLElement($data); //Parse the XML
			if($data->user->username) { //Ok, we got a username
				$attr = $data->user->attributes();
				$user = $attr->id;
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
function mudsFlickr_get_user() {
	$options = get_option('muds_options');
	return $options['user_flickr'];
}

/**
* Sets the user for future references.
*
* @access public
* @param string The username. 
*/
function mudsFlickr_set_user($new_user) {
	$options = get_option('muds_options');
	$options['user_flickr'] = $new_user;	
	$options['last_source'] = MUDS_TYPE_FLICKR;
	update_option('muds_options', $options);
}

/**
* Returns user name.
*
* @access public
* @param string The username.
* @return string Username
*/
function mudsFlickr_get_username($userid) {
	$answer = $userid;
	$url = "http://api.flickr.com/services/rest/?method=flickr.people.getInfo&api_key=". MUDS_FLICKR_API ."&user_id=$userid";
	if($data = muds_readfile($url)) {
		$data = new SimpleXMLElement($data); //Parse the XML
		$attr = $data->attributes();
		if($attr->stat=='ok') {
			$answer = $data->person->username;
			if(strlen($data->person->realname)>0) $answer = $data->person->realname; 
		}
	}
	return $answer;
}

/**
* Returns the list of galleries a user has.
*
* @access public
* @param string user The username in flickr.
* @return array The list of galleries. 
*/
function mudsFlickr_list_user_galleries($user) {
	//The default answer is an empty array, it means we can't read the file, nor create it. 
	$answer = array();
	
	//Ask for the user file 
	if(!get_transient("muds-fl-$user.xml")) { //If the user doesn't have a file yet
		mudsFlickr_update_user_galleries_list($user); //try to create it.
	}
	
	
	if($data = get_transient("muds-fl-$user.xml")) { //Try again to read the user file, maybe we have created it yet
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
* @param string user The username in flickr.
* @return bool True on succes, false if not. 
*/
function mudsFlickr_update_user_galleries_list($user) {
	//The default answer
	$answer = false;
	
	//A flag to know if we can save the file
	$save = false;
	
	//Start the XML file for the user's galleries.
	$out="<?xml version = '1.0' encoding = 'UTF-8'?><slide version='".MUDS_XML_V."' owner='$user'>";
	
	//Hello flickr, we have a little question. Do you know this user? Can you send the
	//albums he has?
	$url = "http://api.flickr.com/services/rest/?method=flickr.photosets.getList&api_key=". MUDS_FLICKR_API ."&user_id=$user";
	if($data = muds_readfile($url)) { //Ask flickr
		if($data[0]=='<') { //Ok, we have an answer and it looks like an XML
			$data = new SimpleXMLElement($data); //Parse the XML
			$attr = $data->attributes();
			if($attr->stat=='ok') { //The answer is correct 
				$save = true; //We are reading something, so we can save something
				
				$title=sprintf(__('Most Recent photos from %s', 'mudslide' ), mudsFlickr_get_username($user));
				$gallery_url = "http://www.flickr.com/photos/$user/";
				$out.="<album><id>$user</id><title>$title</title><url>$gallery_url</url></album>"; //Add the entry to the XML file
				
				foreach($data->photosets->photoset as $entry) { //Read each entry
					$attr = $entry->attributes();
					$id = $attr->id; //Get the id
					
					$title = xmlentities($entry->title, true); //Get the title
					
					$gallery_url = "http://www.flickr.com/photos/$user/sets/$album/";
					
					$out.="<album><id>$id</id><title>$title</title><url>$gallery_url</url></album>"; //Add the entry to the XML file
				}
						
				//Hello flickr, we again. Can you send the tags this user has?
				$url = "http://api.flickr.com/services/rest/?method=flickr.tags.getListUser&api_key=". MUDS_FLICKR_API ."&user_id=$user";
				if($data = muds_readfile($url)) { //Ask flickr
					$data = new SimpleXMLElement($data); //Parse the XML
					foreach($data->who->tags->tag as $entry) { //Read each entry
						
						$tag = xmlentities((string)$entry, true); //Get the tag
						//$tag = str_replace("&", "&amp;", $tag); //To save it correctly in the file and in MySQL
						//$tag = str_replace(chr(34), "&quot;", $tag); //To save it correctly in the file and in MySQL
						$tag_id = $tag;
						
						//Get the tag link in flickr
						$gallery_url = "http://www.flickr.com/photos/$user/tags/$tag/";
						
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
			set_transient("muds-fl-$user.xml", $out, 60*60*24*365); //Update every year
			while (!get_transient("muds-fl-$user.xml")) {
				sleep(1);
			}
			$answer = true;
		}
	}
	
	return $answer;
}


/**
* Return the list of photos in a gallery.
*
* @access public
* @param string user The username in flickr.
* @param string gallery The id or tag from the gallery where we have to get the photos.
* @return array The list of photos, false if not. 
*/
function mudsFlickr_list_photos_in_gallery( $user, $gallery ) {
	$answer = array();
	$filename = false;
	if(strcmp($gallery, "0")==0) $gallery=$user;
	if(strcmp($user,$gallery)!=0 && !is_numeric($gallery)) { //If this is a tag
		$filename = "muds-ft-$gallery.xml";
		if(muds_not_readable_file($filename)) { //Do we have this album file?
			mudsFlickr_update_tag($user, $gallery); //No! Try to create it
		}
	} else { //If it's an album
		$filename = "muds-fa-$gallery.xml";
		if(muds_not_readable_file($filename)) { //Do we have this album file?
			if(strcmp($user,$gallery)==0) { //A zero album
				mudsFlickr_update_zero($user);
			} else {
				mudsFlickr_update_album($user, $gallery); //No! Try to create it
			}
		}
	}
	
	if($url) { //So, do we have a file?
		if($data = muds_readfile($url)) { //Ok, try again to read it.
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
* @param string user The username in flickr.
* @param int album The id of the album from where we have to get the photos.
* @return array The list of photos, false if not. 
*/
function mudsFlickr_update_gallery( $user, $gallery ) {
	$answer = false;
	extract(muds_detect_type($user, $gallery, MUDS_TYPE_FLICKR));
	
	switch($album_type) {
		case MUDS_FILE_ZERO:
			$answer = mudsFlickr_update_zero( $user ); //Call the function to update the Zero gallery
			break;
		case MUDS_FILE_TAG:
			$answer = mudsFlickr_update_tag( $user, $gallery ); //Call the function to update the tag gallery
			break;
		case MUDS_FILE_ALBUM:
			$answer = mudsFlickr_update_album( $user, $gallery ); //Call the function to update the album
			break;
		case MUDS_FILE_FEED:
			$answer = mudsFlickr_update_feed( $user ); //Call the function to update the album
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
function mudsFlickr_update_gallery_by_filename( $filename ) {
	$answer = false;
	
	$data = muds_get_gallery_data($filename);
	$data = explode('|', $data);
	if(strstr($data[0], 'url=')) {
		$url = substr($data[0],4);
		$answer = mudsFlickr_update_feed( $url );
	}
	if(strstr($data[1], 'album=')) {
		$user = substr($data[0],5);
		$album = substr($data[1],6);
		if(strcmp($user,$album)!=0) {
			$answer = mudsFlickr_update_album( $user, $album );
		} else {
			$answer = mudsFlickr_update_zero( $user );
		}
	}
	if(strstr($data[1], 'tag=')) {
		$user = substr($data[0],5);
		$album = substr($data[1],4);
		$answer = mudsFlickr_update_tag( $user, $album );
	}
		
	return $answer;
}

/**
* Updates the album zero.
*
* @access public
* @param string user The username in flickr.
* @return array The list of photos, false if not. 
*/
function mudsFlickr_update_zero( $user ) {
	$fwr = false;
	$album = $user;
	$options = get_option('muds_options');
	$rows = $options['rows'];
	if($rows == NULL) $rows = 3;
	$per_page = $options['columns'] * $rows; //
	//Hello flickr, we again. Can you show the photos from this album?
	$url = "http://api.flickr.com/services/rest/?method=flickr.photos.search&api_key=". MUDS_FLICKR_API . "&user_id=$user&per_page=$per_page&extras=url_t,url_m,url_z,url_l,description";
	$gallery_title=sprintf(__('Most Recent photos from %s', 'mudslide' ), mudsFlickr_get_username($user));
	$out=false;
	if($data = muds_readfile($url)) { //Ask flickr
		$data = new SimpleXMLElement($data); //Parse the XML
		$attr = $data->attributes();
		if($attr->stat=='ok') { //The answer is correct 
			$gallery_url = "http://www.flickr.com/photos/$user/";
			
			//Creates the XML data
			$out="<?xml version = '1.0' encoding = 'UTF-8'?><album version='".MUDS_XML_V."' description='$gallery_title' owner='$user' id='$album' url='$gallery_url'>"; //Our XML header
			foreach($data->photos->photo as $entry) {
				$attr = $entry->attributes();
				$title = xmlentities($attr->title, true); //Get the title
				if(strlen($title)==0) $title = "--nn--"; //If there are not title, use a dummy title.
				
				$id = $attr->id;
				
				//Get the link in flickrweb
				
				$photo_url = "http://www.flickr.com/photos/$user/$id/";
				
				//Get the source file and dimmensions
				if(isset($attr->url_t)) {
					$src = $attr->url_t;
					$width = $attr->width_t;
					$height = $attr->height_t;
				}
				if(isset($attr->url_m)) {
					$src = $attr->url_m;
					$width = $attr->width_m;
					$height = $attr->height_m;
				}
				if(isset($attr->url_z)) {
					$src = $attr->url_z;
					$width = $attr->width_z;
					$height = $attr->height_z;
				}
				if(isset($attr->url_l)) {
					$src = $attr->url_l;
					$width = $attr->width_l;
					$height = $attr->height_l;
				}
					
				//Get resize data
				$resize = $attr->url_t;
				
				//Get the url to resize a file
				$resize = str_replace( "_t.", "_%size%.", $resize);
				
				//Get the comment
				$comment=$entry->description;
				
				//Create the XML entry
				$out.="<photo album='$album' id='$id' url='$photo_url'><title>$title</title><comment>$comment</comment><src>$src</src><resize>$resize</resize><width>$width</width><height>$height</height></photo>";
			}
			$out.="</album>"; //Close the XML file
		}
		
		if($out) { //If we have something to save	
		
			$filename = "muds-fa-$album.xml";
			$answer = set_transient($filename, $out, 60*60*24*365); //Update every year
			while (!get_transient($filename)) {
				sleep(1);
			}
			
			$fwr = true;
			muds_set_album($filename, MUDS_FLICKR, $gallery_title, "user=$user|album=$album");
		
		}
	}
	
	return $fwr;
}


/**
* Updates an album.
*
* @access public
* @param string user The username in flickr.
* @param int album The id of the album from where we have to get the photos.
* @return array The list of photos, false if not. 
*/
function mudsFlickr_update_album( $user, $album ) {
	$fwr = false;
	if($album>0) { //If it is a number
		//Hello flickr, we again. Can you show the photos from this album?
		$url = "http://api.flickr.com/services/rest/?method=flickr.photosets.getInfo&api_key=". MUDS_FLICKR_API . "&photoset_id=$album";
		$gallery_title=__('Album doesn\'t exist', 'mudslide' ); //Maybe we are wrong, so this is to create a dummy answer
		$out=false;
		if($data = muds_readfile($url)) { //Ask flickr
			$data = new SimpleXMLElement($data); //Parse the XML
			$attr = $data->attributes();
			if($attr->stat=='ok') { //The answer is correct 
				$gallery_title = xmlentities($data->photoset->title, true); //Get the title
				$gallery_url = "http://www.flickr.com/photos/$user/sets/$album/";
				
				$url = "http://api.flickr.com/services/rest/?method=flickr.photosets.getPhotos&api_key=". MUDS_FLICKR_API . "&photoset_id=$album&extras=url_t,url_m,url_z,url_l,description";
				$data = muds_readfile($url);
				$data = new SimpleXMLElement($data);
				
				//Creates the XML data
				$out="<?xml version = '1.0' encoding = 'UTF-8'?><album version='".MUDS_XML_V."' description='$gallery_title' owner='$user' id='$album' url='$gallery_url'>"; //Our XML header
				foreach($data->photoset->photo as $entry) {
					$attr = $entry->attributes();
					$title = xmlentities($attr->title, true); //Get the title
					if(strlen($title)==0) $title = "--nn--"; //If there are not title, use a dummy title.
					
					$id = $attr->id;
					
					//Get the link in flickrweb
					
					$photo_url = "http://www.flickr.com/photos/$user/$id/in/set-$album/";
					
					//Get the source file and dimmensions
					if(isset($attr->url_t)) {
						$src = $attr->url_t;
						$width = $attr->width_t;
						$height = $attr->height_t;
					}
					if(isset($attr->url_m)) {
						$src = $attr->url_m;
						$width = $attr->width_m;
						$height = $attr->height_m;
					}
					if(isset($attr->url_z)) {
						$src = $attr->url_z;
						$width = $attr->width_z;
						$height = $attr->height_z;
					}
					if(isset($attr->url_l)) {
						$src = $attr->url_l;
						$width = $attr->width_l;
						$height = $attr->height_l;
					}
					
					//Get resize data
					$resize = $attr->url_t;
					
					//Get the url to resize a file
					$resize = str_replace( "_t.", "_%size%.", $resize);
					
					//Get the comment
					$comment=$entry->description;
					$comment = xmlentities($comment, true);
					
					//Create the XML entry
					$out.="<photo album='$album' id='$id' url='$photo_url'><title>$title</title><comment>$comment</comment><src>$src</src><resize>$resize</resize><width>$width</width><height>$height</height></photo>";
				}
				$out.="</album>"; //Close the XML file
			}
		}
		
		if($out) { //If we have something to save	
		
			$filename = "muds-fa-$album.xml";
			$answer = set_transient($filename, $out, 60*60*24*365); //Update every year
			while (!get_transient($filename)) {
				sleep(1);
			}
			
			$fwr = true;
			muds_set_album($filename, MUDS_FLICKR, $gallery_title, "user=$user|album=$album");
		
		}
	}
	
	return $fwr;
}


/**
* Updates a tag album.
*
* @access public
* @param string user The username in flickr.
* @param int album The id of the tag album from where we have to get the photos.
* @return array The list of photos, false if not. 
*/
function mudsFlickr_update_tag( $user, $tag ) {
	//We put some code with the numeric tags, we have to delete it to use here
	$search = "@tag#(\d+)#@i";
	if(preg_match_all($search, $tag, $matches)) {
		if (is_array($matches)) {
			foreach ($matches[1] as $key =>$v0) {
				$tag=$matches[1][$key]; //if we found something, use it
			}
		}
	}
	
	$url = "http://api.flickr.com/services/rest/?method=flickr.photos.search&api_key=". MUDS_FLICKR_API . "&user_id=$user&tags=$tag&extras=url_t,url_m,url_z,url_l,description";
	$gallery_title=__('Album doesn\'t exist', 'mudslide' ); //Maybe we are wrong, so this is to create a dummy answer
	$out=false;
	if($data = muds_readfile($url)) { //Ask flickr
		$data = new SimpleXMLElement($data); //Parse the XML
		$attr = $data->photos->attributes();
		if($attr->total>0) { //The answer is correct
			$gallery_title = xmlentities(sprintf(__('Tag: %s', 'mudslide'), $tag), true); //Get the title
			$gallery_url = "http://www.flickr.com/photos/$user/tags/$tag/";
			
			//Creates the XML data
			$out="<?xml version = '1.0' encoding = 'UTF-8'?><tag version='".MUDS_XML_V."' description='$gallery_title' owner='$user' tag='$tag' url='$gallery_url'>";
			foreach($data->photos->photo as $entry) {
				$attr = $entry->attributes();
				$title = xmlentities($attr->title, true); //Get the title
				if(strlen($title)==0) $title = "--nn--"; //If there are not title, use a dummy title.
				
				$id = $attr->id;
				
				//Get the link in flickrweb
				
				$photo_url = "http://www.flickr.com/photos/$user/$id/in/set-$album/";
				
				//Get the source file and dimmensions
				if(isset($attr->url_t)) {
					$src = $attr->url_t;
					$width = $attr->width_t;
					$height = $attr->height_t;
				}
				if(isset($attr->url_m)) {
					$src = $attr->url_m;
					$width = $attr->width_m;
					$height = $attr->height_m;
				}
				if(isset($attr->url_z)) {
					$src = $attr->url_z;
					$width = $attr->width_z;
					$height = $attr->height_z;
				}
				if(isset($attr->url_l)) {
					$src = $attr->url_l;
					$width = $attr->width_l;
					$height = $attr->height_l;
				}
					
				//Get resize data
				$resize = $attr->url_t;
				
				//Get the url to resize a file
				$resize = str_replace( "_t.", "_%size%.", $resize);
				
				//Get the comment
				$comment=$entry->description;
				$comment = xmlentities($comment, true);
				
				//Create the XML entry
				$out.="<photo album='$album' id='$id' url='$photo_url'><title>$title</title><comment>$comment</comment><src>$src</src><resize>$resize</resize><width>$width</width><height>$height</height></photo>";
			}
			$out.="</tag>"; //Close the XML file
		}
	}
	
	if($out) { //Do we have something to show?
		$tag_id = (string)$tag;
		if(is_numeric($tag_id)) { //A numeric tag can be a problem, rename it
			$tag= "tag#$tag#"; //Pray no one would use this kind of tags 
		}
		
		$filename = "muds-ft-$tag.xml";
		$answer = set_transient($filename, $out, 60*60*24*365); //Update every year
		while (!get_transient($filename)) {
			sleep(1);
		}
		
		$fwr = true;
		muds_set_album($filename, MUDS_FLICKR, $gallery_title, "user=$user|tag=$tag");

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
function mudsFlickr_update_feed( $url ) {
	$fwr = false;
	$out = false;
	$aux=0;
	$url = muds_decode($url);
	$md5 = md5($url);
	$gallery_title=__('Album doesn\'t exist', 'mudslide' ); //Maybe we are wrong, so this is to create a dummy answer
	if(class_exists('SimplePie') && strstr($url, "http://api.flickr.com") ) { // Does simplepie library exists? Is this a Flickr feed?
		$rss = new SimplePie(); //Get the data from the rss
		$rss->set_feed_url(str_replace('&amp;', '&', $url)); //Set the feed url
		$rss->enable_cache(false); //Disable cache
		$rss->enable_order_by_date(false); //Come as you are
		$rss->init(); //Get the feed

		$gallery_url = $rss->get_permalink(); //Ǵet the link to the page (not to the RSS) from the feed
		$gallery_title = htmlspecialchars($rss->get_title()); //If we don't have a title, use the name from the feed
		
		//Get the items to show
		$items = $rss->get_items();
		$out="<?xml version = '1.0' encoding = 'UTF-8'?><album version='".MUDS_XML_V."' description='$gallery_title' owner='" . urlencode($url) ."' id='$md5' url='$gallery_url'>"; //Our XML header */
		foreach($rss->get_items() as $entry) {
			//print_r($entry);
			$photo_url = htmlspecialchars($entry->get_permalink());
			$id = 0;
			$album = $md5;
			$title = $entry->get_title();
			$comment = "";
			$enc =  $entry->get_enclosure();
			$src = $enc->link;
			if(function_exists('getimagesize')) {
				if($size = getimagesize($src)) {
					$width = $size[0];
					$height = $size[1];
				}
			}
			preg_match_all('/<img src="([^"]*)"([^>]*)>/i', $entry->get_description(), $matches);
    		$resize=$matches[1][0];
    		$resize = str_replace('_m.', '_%size%.', $resize); //Flickr!!!! 
			
			$out.="<photo album='$album' id='$id' url='$photo_url'><title>$title</title><comment>$comment</comment><src>$src</src><resize>$resize</resize><width>$width</width><height>$height</height></photo>";
		}
		$out.="</album>"; //Close the XML file
	}
		
	if($out) { //If we have something to save	
		
		$filename = "muds-ff-$md5.xml";
		$answer = set_transient($filename, $out, 60*60*24*365); //Update every year
		while (!get_transient($filename)) {
			sleep(1);
		}
		
		$fwr = true;
		muds_set_album($filename, MUDS_FLICKR, $gallery_title, "url=$url");
	}
	
	return $fwr;
}

/**
* Returns the HTML code of a gallery. If the user can modify the post this function
* also puts a link to update he gallery.
*
* @access public
* @param string user The username in flickr.
* @param string gallery The id of the gallery from where we have to get the photos.
* @param int first The first photo of the list to show.
* @param int last The last photo of the list to show.
* @param int conf A numeric value for the configuration. 1 to show the first comment
	in flickrweb as a big description.
* @return string The HTML code for the gallery the photos, false if not. 
*/
function mudsFlickr_show_gallery( $user, $gallery, $first=0, $last=0, $conf=0, $rand=0 ) {
	$answer = "";
	extract(muds_detect_type($user, $gallery, MUDS_TYPE_FLICKR));
	
	$update_post = "type=flickr&amp;user=".urlencode($user)."&amp;gallery=$gallery"; //Create the update post

	if(!muds_not_readable_file($filename)) { //If it's a readable gallery
		//We have data, so we can try to show the gallery.
		$answer = muds_gallery(get_transient($filename), $update_post, $first, $last, $conf, MUDS_TYPE_FLICKR, $rand);
	} else { //Oops, something goes wrong, it is not a readable gallery
		if(mudsFlickr_update_gallery( $user, $gallery )) { //Try to create it
			$answer = mudsFlickr_show_gallery( $user, $gallery, $first, $last, $conf, $rand ); //If we created it, show the gallery
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
* @param string user The username in flickr.
* @param string gallery The id of the gallery from where we have to get the photos.
* @param int conf A numeric value for the configuration.
* @return string The HTML code for the gallery the photos, false if not. 
*/
function mudsFlickr_show_simple($user, $gallery, $size='t', $float=false, $conf=0, $rand=0) {
	$answer = "";
	extract(muds_detect_type($user, $gallery, MUDS_TYPE_FLICKR));
	
	$update_post = "type=flickr&amp;user=".urlencode($user)."&amp;gallery=$gallery"; //Create the update post
	
	if(!muds_not_readable_file($filename)) { //If it's a readable file
		//We have data, so we can try to show the simple album.
		$answer = muds_show_simple(get_transient($filename), $update_post ,$size, $float, $conf, MUDS_TYPE_FLICKR, $rand);
	} else { //Oops, something goes wrong, it is not a readable gallery.
		if(mudsFlickr_update_gallery( $user, $gallery )) { //Try to create it
			$answer = mudsFlickr_show_simple($user, $gallery, $size, $float, $conf, $rand); //If we created it, show the gallery
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
* @param string user The username in flickr. Can be NO_USER
* @param string gallery The id of the gallery from where we have to get the photo.
* @param int p_id The position of the photo in the gallery. Can be also LAST_PHOTO or RANDOM_PHOTO.
* @param int size The size of the photo in the post.
* @param string float Where the photo has to float.
* @param int conf A numeric value for the configuration.
* @return string The HTML code for the gallery the photos, false if not. 1 to show the first comment
	in flickrweb as a big description. 
*/
function mudsFlickr_show_photo( $user, $gallery, $p_id, $size='t', $float=false, $conf=0 ) {
	$answer = "";
	extract(muds_detect_type($user, $gallery, MUDS_TYPE_FLICKR));
	
	if(!muds_not_readable_file($filename)) { //If it's a readable file
		//We have data, so we can try to show the photo.
		$answer = muds_photo(get_transient($filename), $p_id, $size, $float, $conf, MUDS_TYPE_FLICKR);
	} else { //Oops, something goes wrong, we don't have the file.
		//Try to create it if we have the user. The widget doesn't know it.
		if($user && mudsFlickr_update_gallery( $user, $gallery )) {
			$answer = mudsFlickr_show_photo( $user, $gallery, $p_id, $size, $float, $conf ); //If we created the gallery, show the photo
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
function mudsFlickr_widget_photo($filename, $p_id, $size='t') {
	$answer = "";
	
	if(!muds_not_readable_file($filename)) { //If it's a readable file
	
		//We have data, so we can try to show the photo.
		$answer = muds_photo(get_transient($filename), $p_id, $size, 'center', 0, MUDS_TYPE_FLICKR, true);
	} else { 
		//Try to update the gallery.
		if(mudsFlickr_update_gallery_by_filename( $filename )) { //If we can update the file
			//We have data, so we can try to show the photo.
			$answer = mudsFlickr_widget_photo($filename, $p_id, $size);
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
function mudsFlickr_thumbnail($user, $gallery, $num) {
	$image = false;
	$pass = false;
	extract(muds_detect_type($user, $gallery, MUDS_TYPE_FLICKR));
	if(!muds_not_readable_file($filename)) { //If it's a readable gallery
		$pass =true;
	} else {
		$pass = mudsFlickr_update_gallery( $user, $gallery );
	}
	
	if($pass) {
		//We have data, so we can try to show the gallery.
		//$answer = muds_gallery(get_transient($filename), $update_post, $first, $last, $conf, MUDS_TYPE_PICASA, $rand);
		$data = get_transient($filename);
		$photo_gallery = new SimpleXMLElement($data);
		$aux = $photo_gallery->photo[$num-1];
		$image = array((string)$aux->title, (string)$aux->resize, (string)$aux->src, 'flickr');
	}
	return $image;
}

?>
