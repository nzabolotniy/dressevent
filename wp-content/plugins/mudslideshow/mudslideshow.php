<?php
/*
Plugin Name: MudSlideShow
Plugin URI: http://www.sebaxtian.com/acerca-de/mudslideshow
Description: An image gallery system using Picasa and/or Flickr.
Version: 0.12.8.6
Author: Sebaxtian
Author URI: http://www.sebaxtian.com
*/

/*  Copyright 2007-2010  Sebaxtian  (email : sebaxtian@gawab.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('RANDOM_PHOTO', -1);
define('LAST_PHOTO', -2);
define('NO_USER', false);
define('MUDS_HEADER_V', 1.17);
define('MUDS_DB_VERSION', 2);

define('MUDS_LOCAL', 1);
define('MUDS_PICASA', 2);
define('MUDS_FLICKR', 3);
define('MUDS_HIGH', 100000000);
define('MUDS_XML_V', 3.1);

define('MUDS_TYPE_NONE', 0);
define('MUDS_TYPE_PICASA', 1);
define('MUDS_TYPE_FLICKR', 2);

define('MUDS_HEIGHT', 1);
define('MUDS_WIDTH', 2);

define('MUDS_FILE_ZERO', 0);
define('MUDS_FILE_LIST', 1);
define('MUDS_FILE_ALBUM', 2);
define('MUDS_FILE_TAG', 3);
define('MUDS_FILE_FEED', 4);

define('MUDS_WIDGET_RANDOM',1);
define('MUDS_WIDGET_LAST',2);
define('MUDS_WIDGET_RANDOMLAST',3);
define('MUDS_WIDGET_RANDOMFROM',4);

define('MUDS_SQUARED', 1);
define('MUDS_SCALED', 2);
define('MUDS_DEFAULT_MAXSIZE', 75);

define('MUDS_OPT_COMMENT', 1);
define('MUDS_OPT_REVERSEORDER', 2);

define('MUDS_800', 0);
define('MUDS_ORIGINAL', 1);

//Set the viewer functions
$options = get_option('muds_options');

require('viewers/simple-viewer.php');
require('viewers/highslide-viewer.php');
require('viewers/fancybox-viewer.php');
require('viewers/lytebox-viewer.php');
require('viewers/wpp-viewer.php');
require('viewers/none-viewer.php');

add_action('admin_init', 'muds_add_buttons');
add_action('get_header', 'muds_redeclareJQuery');
add_action('init', 'muds_text_domain',1);
add_action('admin_menu', 'muds_menus');
add_action('wp_head', 'muds_header');
add_action('admin_head', 'muds_adminHeader');
add_action('wp_footer', 'muds_footer');
add_action('save_post', 'muds_save_post');
add_filter('the_content', 'muds_content');
add_filter('the_excerpt', 'muds_content');
register_activation_hook(__FILE__, 'muds_activate');

add_action('wp_ajax_muds_tinymce', 'muds_tinymce');
add_action('wp_ajax_muds_ajax_update', 'muds_ajax_update');
add_action('wp_ajax_nopriv_muds_ajax_update', 'muds_ajax_update');
add_action('wp_ajax_muds_ajax_data', 'muds_ajax_data');
add_action('wp_ajax_nopriv_muds_ajax_data', 'muds_ajax_data');

remove_filter('get_the_excerpt', 'wp_trim_excerpt');
add_filter('get_the_excerpt', 'improved_trim_excerpt');
add_filter('wpbook_attachment', 'muds_wpbook', 10, 2);
add_shortcode('mudslide', 'muds_shortcode' );

require_once(ABSPATH . WPINC . '/class-simplepie.php');

require_once('sources/picasa.php');
require_once('sources/flickr.php');

/**
* Function to delete the shortcode.
* This function should be called by a filter.
*
* @access public
*/
function muds_shortcode($atts, $content = null) {}

/**
* Function to modify the excerpt.
* This function should be called by a filter.
*
* @access public
*/
function improved_trim_excerpt($text) {
	$raw_excerpt = $text;
	if ( '' == $text ) {
		$text = get_the_content('');

		$text = strip_shortcodes( $text );

		$text = apply_filters('the_content', $text);
		$text = str_replace(']]>', ']]&gt;', $text);
		$text = preg_replace('@<script[^>]*?>.*?</script>@si', '', $text);
		$text = preg_replace('@<!-- muds_begin -->.*?<!-- muds_end -->@si', '', $text);
		$text = strip_tags($text);
		$excerpt_length = apply_filters('excerpt_length', 55);
		$excerpt_more = apply_filters('excerpt_more', ' ' . '[...]');
		$words = preg_split("/[\n\r\t ]+/", $text, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY);
		if ( count($words) > $excerpt_length ) {
			array_pop($words);
			$text = implode(' ', $words);
			$text = $text . $excerpt_more;
		} else {
			$text = implode(' ', $words);
		}
	}
	return apply_filters('wp_trim_excerpt', $text, $raw_excerpt);
}

/**
* Function to call scripts required by admin header.
* This function should be called by an action.
*
* @access public
*/
function muds_adminHeader() {
	require('viewers/none-header.php');
}

/**
* Function to answer the 'update' ajax call.
* This function should be called by an action.
*
* @access public
*/
function muds_ajax_update() {
	//Get the data from the post call
	$style= $id = $type = $user = $gallery = $var1 = $var2 = $conf = $rand = false;
	
	if(isset($_POST['style']))	$style   = $_POST['style'];
	if(isset($_POST['id'])) 	$id      = $_POST['id'];
	if(isset($_POST['type']))	$type    = $_POST['type']; 
	if(isset($_POST['user']))	$user    = $_POST['user'];
	if(isset($_POST['gallery'])) $gallery = $_POST['gallery'];
	if(isset($_POST['var1']))	$var1    = $_POST['var1'];
	if(isset($_POST['var2']))	$var2    = $_POST['var2'];
	if(isset($_POST['conf']))	$conf    = $_POST['conf'];
	if(isset($_POST['rand']))	$rand    = $_POST['rand'];
	//$page    = $_POST['page'];
	
	$answer = false;

	if(is_numeric($id)) {
		if(muds_update_gallery($id)) {
			$answer=true;
			$name=muds_get_gallery_name($id);
		}
	} else {
		switch($type) {
			case 'picasa' :
				if($user && $gallery) {
					$answer = mudsPicasa_update_gallery( $user, $gallery );
				}
				break;
			case 'flickr' :
				if($user && $gallery) {
					$answer = mudsFlickr_update_gallery( $user, $gallery );
				}
				break;
		}
	}

	if(!$answer) {
		$answer = _e('Error while Updating', 'mudslide');
	} else {
		if(is_numeric($id)) {
			$answer = sprintf( __('%s (updated)', 'mudslide'), $name)."<img src='".muds_plugin_url('/img/loading.gif')." ' height='10' id='throbber-mss$id' class='throbber-off'>";
		} else {
			switch($type) {
				case 'picasa': 
					if($style=='gallery') {
						$answer = mudsPicasa_show_gallery( $user, $gallery, $var1, $var2, $conf, $rand );
					} else {
						$answer = mudsPicasa_show_simple($user, $gallery, $var1, $var2, $conf, $rand);
					}
					break;
				case 'flickr': 
					if($style=='gallery') {
						$answer = mudsFlickr_show_gallery( $user, $gallery, $first, $last, $conf, $rand );
					} else {
						$answer = mudsFlickr_show_simple($user, $gallery, $var1, $var2, $conf, $rand);
					}
					break;
			}
		}
	}
	// Compose JavaScript for return
	die( $answer );
}

/**
* Function to answer the MCE ajax call.
* This function should be called by an action.
*
* @access public
*/
function muds_tinymce() {
	// check for rights
    if ( !current_user_can('edit_pages') && !current_user_can('edit_posts') ) 
    	die(__("You are not allowed to be here"));
   	
   	require_once('tinymce/mce_mudslide.php');
    
    die();
}

/**
* Function to answer the 'update' ajax call.
* This function should be called by an action.
*
* @access public
*/
function muds_ajax_data() {
	//Get the data from the post call
	$type=$_POST['type'];
	$kind=$_POST['kind'];
	$update=$_POST['update'];
	$file = false;
	$answer=false;

	if($type=='picasa') {
		$user=$_POST['user'];
		mudsPicasa_set_user($user);
		if($kind=='check') {
			if($user = mudsPicasa_check_user($user)) {
				$answer = $user;
				if($update) mudsPicasa_update_user_galleries_list($user);
			}
		}
		if($kind=='galleries') {
			if($update) {
				mudsPicasa_update_user_galleries_list($user);
			} else {
				mudsPicasa_list_user_galleries($user);
			}
			$file = "muds-pl-$user.xml";
		}
		if($kind=='gallery') {
			$gallery=$_POST['gallery'];
			if($update) {
				mudsPicasa_update_gallery($user, $gallery);
			} else {
				mudsPicasa_list_photos_in_gallery($user, $gallery);
			}
			if(strcmp($user,$gallery)==0 || is_numeric($gallery)) {
				$file = "muds-pa-$gallery.xml";
			} else {
				$file = "muds-pt-$gallery.xml";
			}
		}
	}
	
	if($type=='flickr') {
		$user=$_POST['user'];
		mudsFlickr_set_user($user);
		if($kind=='check') {
			if($user = mudsFlickr_check_user($user)) {
				$answer = $user;
				if($update) mudsFlickr_update_user_galleries_list($user);
			}
		}
		if($kind=='galleries') {
			if($update) {
				mudsFlickr_update_user_galleries_list($user);
			} else {
				mudsFlickr_list_user_galleries($user);
			}
			$file = "muds-fl-$user.xml";
		}
		if($kind=='gallery') {
			$gallery=$_POST['gallery'];
			if($update) {
				mudsFlickr_update_gallery($user, $gallery);
			} else {
				mudsFlickr_list_photos_in_gallery($user, $gallery);
			}
			if(strcmp($user,$gallery)==0 || is_numeric($gallery)) {
				$file = "muds-fa-$gallery.xml";
			} else {
				$file = "muds-ft-$gallery.xml";
			}
		}
	}

	if($file && $data = get_transient($file)) {
		$answer = $data;
	}
	die($answer);
}

/**
* Function that return the URL of the post that have the asked gallery.
* If the query has more than one answer it returns the query page.
*
* @access public
*/
function muds_search($owner, $album, $url) {
	$answer=false;

	$args = array(
		's' => "$owner,$album"
	);
	$the_query = new WP_Query( $args );
	$answer = $the_query->pos_count;
	switch($the_query->post_count) {
		case 0:
			$answer = $url;
			break;
		case 1:
			$post = $the_query->posts[0];
			$link = get_permalink( $post->ID );
			$answer = $link;
			break;
		default:
			$answer = get_bloginfo( 'siteurl')."?s=$owner,$album";
			break;
	}

	return $answer;
}

/**
* To redeclare JQery for fancybox.
* This function should be called by an action.
*
* @access public
*/
function muds_redeclareJQuery() {
	if(version_compare(get_bloginfo('version'), '3.0.0', '<')) {
		$options = get_option('muds_options');
		if($options['gallery_type'] == 2) {//FANCYBOX
			wp_deregister_script('jquery');
			wp_register_script('jquery', 'http://code.jquery.com/jquery-1.4.2.min.js', false, '1.4.2');
			wp_enqueue_script('jquery');
		}
	}
}

function browser_info($agent=null) {
  // Declare known browsers to look for
  $known = array('msie', 'firefox', 'safari', 'webkit', 'opera', 'netscape',
    'konqueror', 'gecko');

  // Clean up agent and build regex that matches phrases for known browsers
  // (e.g. "Firefox/2.0" or "MSIE 6.0" (This only matches the major and minor
  // version numbers.  E.g. "2.0.0.6" is parsed as simply "2.0"
  $agent = strtolower($agent ? $agent : $_SERVER['HTTP_USER_AGENT']);
  $pattern = '#(?<browser>' . join('|', $known) .
    ')[/ ]+(?<version>[0-9]+(?:\.[0-9]+)?)#';

  // Find all phrases (or return empty array if none found)
  if (!preg_match_all($pattern, $agent, $matches)) return array();

  // Since some UAs have more than one phrase (e.g Firefox has a Gecko phrase,
  // Opera 7,8 have a MSIE phrase), use the last one found (the right-most one
  // in the UA).  That's usually the most correct.
  $i = count($matches['browser'])-1;
  return array($matches['browser'][$i] => $matches['version'][$i]);
}

/**
* Function to solve the display:table bug in IE lower or equal to 7.
*
* @access public
*/
function muds_general($string,$float) {
	$browser = browser_info();
	if(isset($browser['msie']) && $browser['msie'] <=7) { //Did I sayd I hate IE?
		if($float=='center') $string = "<table><tr><td>$string</td></tr></table>";
	} 
	if($float=='center') $string = "<div align='center'><div style='display: table;'>$string</div></div>";
	return $string;
}

/**
* To declare where are the mo files (i18n).
* This function should be called by an action.
*
* @access public
*/
function muds_text_domain() {
	load_plugin_textdomain('mudslide', false, 'mudslideshow/lang');
}

/**
* Function to return the url of the plugin concatenated to a string. The idea is to
* use this function to get the entire URL for some file inside the plugin.
*
* @access public
* @param string str The string to concatenate
* @return The URL of the plugin concatenated with the string 
*/
function muds_plugin_url($str = '')
{

	$aux = '/wp-content/plugins/mudslideshow/'.$str;
	$aux = str_replace('//', '/', $aux);
	$url = get_bloginfo('wpurl');
	return $url.$aux;

}


/**
* Function to create the database and to add options into WordPress
* This function should be called by an action.
*
* @access public
*/
function muds_activate()
{
	global $wpdb;
	
	$table_name = $wpdb->prefix . "mudslide";
	
	$db_version=get_option('muds_db_version');
	switch($db_version) {
		case 1: //SQL code to update from MSS1 to MSS2
			$sql="ALTER TABLE $table_name ADD data text NOT NULL"; 
			$wpdb->query($sql);
			update_option('muds_db_version', MUDS_DB_VERSION);
		case 2: //We are in MSS2, so theres nothing we have to do
			break;
		default: //It's a fresh installation, create the table.
			if($wpdb->get_var("show tables like '$table_name'") != $table_name)
			{

				$sql = "CREATE TABLE $table_name(
					id bigint(1) NOT NULL AUTO_INCREMENT,
					type int NOT NULL,
					file text NOT NULL,
					name text NOT NULL,
					data text NOT NULL,
					PRIMARY KEY (id)
					);";

				require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
				dbDelta($sql);
				add_option('muds_db_version', MUDS_DB_VERSION);
			}
			break;
	}
	
	$options = array( 
		'gallery_type' => 0,
		'columns' => 5,
		'rows' => 4,
		'user_picasa' => '',
		'user_flickr' => '',
		'show_link' => false,
		'extend' => false,
		'feature' => false,
		'last_source' => MUDS_TYPE_PICASA,
		'thsize' => MUDS_SCALED,
		'thmaxsize' => MUDS_DEFAULT_MAXSIZE,
		'full_size' => MUDS_800
	);
	
	add_option('muds_options', $options);
	
	//Update old slides
	$sql = "update wp_mudslide set file = concat('muds-', file) where file not like 'muds%'";
	$wpdb->query($sql);
	
	//Update to multiwidget	
	muds_multiWidget();
	
	//Update to new configuration array.
	muds_newConfig();
	
}

/**
* Update to new configuration array.
*
* @access public
*/
function muds_newConfig() {
	//Do we have the old configuration rows?
	if( get_option('muds_user') ) {
		$options = get_option('muds_options');
		$options['gallery_type'] = get_option('muds_gallery_type');
		delete_option('muds_gallery_type');
		$options['columns'] = get_option('muds_columns');
		delete_option('muds_columns');
		$options['rows'] = get_option('muds_rows');
		delete_option('muds_rows');
		if($options['rows']==0) $options['rows'] = 4;
		$options['user_picasa'] = get_option('muds_user');
		delete_option('muds_user');
		$options['show_link'] = get_option('muds_showlink');
		delete_option('muds_showlink');
		$options['extend'] = get_option('muds_extend');
		delete_option('muds_extend');
		$options['feature'] = get_option('muds_feature');
		delete_option('muds_feature');
		
		update_option('muds_options', $options);
	}
}

/**
* Update to multiwidget.
*
* @access public
*/
function muds_multiWidget() {
	//If we have the widget from 0.9.3, use it with the new name
	$settings = get_option('widget_muds_widget');
	if ( is_array($settings) ) {
		//Change the gallery to gallery_id
		foreach ( (array) $settings as $index => $widget ) {
			if ( is_array($widget) && count($widget)>1) {
				$settings[$index]['gallery_id']=$settings[$index]['gallery'];
				unset($settings[$index]['gallery']);
			}
		}
		
		update_option('widget_mudslide', $settings);
		
		//Update the sidebar to use the new widget name
		$sidebars_widgets = get_option('sidebars_widgets');
		foreach ( (array) $sidebars_widgets as $index => $sidebar ) {
			if ( is_array($sidebar) ) {
				foreach ( $sidebar as $i => $name ) {
					//Explode the name
					$name_aux=explode('-', $name); 
					//Check if the widget has the name from the old one
					if ( $name_aux[0] == 'muds_widget' && count($name_aux)>1) {
						//Use the new name with the old number
						$sidebars_widgets[$index][$i] = "mudslide-".$name_aux[1];
						$changed = true;
					}
				}
			}
			if($changed) update_option('sidebars_widgets', $sidebars_widgets);
		}
		
		//We have updated it,so  we don't need it anymore
		delete_option('widget_muds_widget');
	} else { //No, we don't have the widget from version 0.9.3
		//Update the sidebar for multiwidget
		$base_name='mudslide';
		$settings = get_option('widget_'.$base_name);
		if ( !array_key_exists('_multiwidget', $settings) ) { //If we don't have a multiwidget, create it
			$changed = false;
			//Update the sidebar to use the first item in the multiwidget array
			$sidebars_widgets = get_option('sidebars_widgets');
			foreach ( (array) $sidebars_widgets as $index => $sidebar ) {
				if ( is_array($sidebar) ) {
					foreach ( $sidebar as $i => $name ) {
						//Check if the widget has the name from the single one
						if ( $name == $base_name.'-widget' ) {
							//Use the first item in the multiwidget array, it means item number 2
							$sidebars_widgets[$index][$i] = "$base_name-2";
							$changed = true;
						}
					}
				}
			}
			if($changed) update_option('sidebars_widgets', $sidebars_widgets);
		}
	}
}

/**
* Enable menus.
* This function should be called by an action.
*
* @access public
*/
function muds_menus() {
	add_management_page('MudSlideShow', 'MudSlideShow', 'publish_posts', 'mudsmanage', 'muds_manage_page');
	add_options_page('MudSlideShow', 'MudSlideShow', 'manage_options', 'mudsoptions', 'muds_options');
}

/**
* Create or update the album in the MSS database.
*
* @access public
* @param filename The name of the gallery.
* @param type The gallery type (MUDS_PICASA, MUDS_FLICKR)
* @param title The title of the gallery
* @param data Data to update the gallery
* @return bool If the gallery was created or updated correctly
*/
function muds_set_album($filename, $type, $title, $data) {
	global $wpdb;
	$answer = false;
	$data = mb_htmlentities($data);
	
	$table_name = $wpdb->prefix . "mudslide";
	if($wpdb->get_var("SELECT id FROM $table_name WHERE file='$filename'")) { //Ok, we have a gallery, update it
		$sql = "UPDATE " . $table_name .
		" set name='$title', data='$data' WHERE file='$filename'";	
	} else { //Nope, we donb have a gallery, create it
		$sql = "INSERT INTO " . $table_name .
		" (type, file, name, data) " .
		"VALUES ($type,'$filename','$title', '$data')";	
	}
	if($wpdb->query( $sql )) $answer = true;
	return $answer;
}

/**
* Return the file of a gallery in the internal list.
*
* @access public
* @param int id ID of the gallery in the internal list.
* @return string The filename of the gallery. False if it doesn't exists.
*/
function muds_get_gallery_file($id) {
	global $wpdb;
	$answer=false;
	$table_name = $wpdb->prefix . "mudslide";
	if($aux = $wpdb->get_var("SELECT file FROM $table_name WHERE id=$id")) {
		$answer=$aux;
	}
	return $answer;
}

/**
* Return the type of a gallery in the internal list.
*
* @access public
* @param int id ID of the gallery in the internal list.
* @return string The gallery type. False if it doesn't exists.
*/
function muds_get_gallery_type($id) {
	global $wpdb;
	$answer=false;
	$table_name = $wpdb->prefix . "mudslide";
	if($aux = $wpdb->get_var("SELECT type FROM $table_name WHERE id=$id")) {
		$answer=$aux;
	}
	return $answer;
}

/**
* Return the name of a gallery in the internal list.
*
* @access public
* @param int id ID of the gallery in the internal list.
* @return string The gallery name. False if it doesn't exists.
*/
function muds_get_gallery_name($id) {
	global $wpdb;
	$answer=false;
	$table_name = $wpdb->prefix . "mudslide";
	if($aux = $wpdb->get_var("SELECT name FROM $table_name WHERE id=$id")) {
		$answer=$aux;
	}
	return $answer;
}

/**
* Return the data of a gallery in the internal list.
*
* @access public
* @param string filename The gallery filename.
* @return string The gallery data. False if it doesn't exists.
*/
function muds_get_gallery_data($filename) {
	global $wpdb;
	$answer=false;
	$table_name = $wpdb->prefix . "mudslide";
	if($aux = $wpdb->get_var("SELECT data FROM $table_name WHERE file='$filename'")) {
		$answer=$aux;
	}
	return $answer;
}

/**
* Deletes a gallery from the internal list.
*
* @access public
* @param int id ID of the gallery in the internal list.
* @return bool True on succes. False if not.
*/
function muds_delete_gallery($id) {
	global $wpdb;
	$answer=false;
	$table_name = $wpdb->prefix . "mudslide";
	if($aux = $wpdb->get_results("SELECT type, file FROM $table_name WHERE id=$id")) {
		$type=$aux[0]->type;
		$filename=$aux[0]->file;
		$del = delete_transient($filename);
		$db = $wpdb->query("DELETE FROM $table_name WHERE id=$id");
		if($del && $db) $answer = true;
	}
	return $answer; 
}

/**
* Updates a gallery in the internal list.
*
* @access public
* @param int id ID of the gallery in the internal list.
* @return bool True on succes. False if not.
*/
function muds_update_gallery($id) {
	global $wpdb;
	$answer=false;
	
	//Get the data from this gallery
	$table_name = $wpdb->prefix . "mudslide";
	if($aux = $wpdb->get_results("SELECT type, file FROM $table_name WHERE id=$id")) {
		$type=$aux[0]->type;
		$filename=$aux[0]->file;
		
		switch($type) {
			case MUDS_PICASA:	//If this is a picasa album
				$answer = mudsPicasa_update_gallery_by_filename($filename);
				break;
			case MUDS_FLICKR:	//If this is a flickr album
				$answer = mudsFlickr_update_gallery_by_filename($filename);
				break;
		}
	}
	return $answer;
}


/**
* XML Entity Mandatory Escape Characters
*
* @access public
* @param string string The string to change
* @return string The chabged string
*/
if (! function_exists ('mb_htmlentities'))
{
   function mb_htmlentities ($string, $quote_style = ENT_COMPAT, $charset = 'UTF-8', $double_encode = true)
   {
		$version = explode('.', PHP_VERSION);
		$version = $version[0] * 10000 + $version[1] * 100 + $version[2];
		if($version < 50203)
   		return htmlentities ($string, $quote_style, $charset);
     	else
     		return htmlentities ($string, $quote_style, $charset, $double_encode);
   }
}

/**
* XML Entity Mandatory Escape Characters
*
* @access public
* @param string string The string to change
* @return string The chabged string
*/
function xmlentities($string, $xml = false) {
	$string = (string) $string;
	if($xml) $string = str_replace ( array ( '&', '"', '\'', '<', '>' ), array ( '&amp;' , '&quot;', '&#39;' , '&lt;' , '&gt;' ), $string );
	else $string = mb_htmlentities($string, ENT_QUOTES);
	return $string;
} 

/**
* Page to manage the galleries likst.
*
* @access public
*/
function muds_manage_page()
{
	global $wpdb;
	$options = get_option('muds_options');
	$show = 'manage';
	$table_name = $wpdb->prefix . "mudslide";
	$messages=array();
	$mode = $mode_x = false;

	if(isset($_POST['mode_x'])) $mode_x=$_POST['mode_x']; //Something to execute?
	if(isset($_GET['mode'])) $mode=$_GET['mode']; //Something to show?
	
	if(isset($_POST['addgallery']) && $_POST['addgallery']) $mode = 'addgallery';
	if(isset($_POST['add_x']) && $_POST['add_x']) $mode = 'add_x';
	if(isset($_POST['cancel']) && $_POST['cancel']) $mode = 'cancel';
	
	$doaction=false; //CLicked the button for bulk action?
	if(isset($_POST['doaction']) && $_POST['doaction']!="") $doaction=$_POST['action']; //Top button
	if(isset($_POST['doaction2']) && $_POST['doaction2']!="") $doaction=$_POST['action2']; //Bottom button
	if($doaction) {
		switch($doaction) {
			case 'delete': //Delete som galleries
				if(count($_POST['checked_galleries'])>0) {
					foreach($_POST['checked_galleries'] as $checked_id) { //Delete each gallery
						muds_delete_gallery($checked_id);
					}
				}
				break;
		}
	}
	
	switch($mode) { //Something to do?
		case 'delete': //Delete one gallery
			check_admin_referer('mudslide_deletegallery');
			$id=$_GET['id'];
			//If deleted, show the message too.
			if(muds_delete_gallery($id)) array_push($messages, __("Gallery deleted",'mudslide'));
			break;
		case 'add_x':
			switch($_POST['sourcetype']) {
				case MUDS_TYPE_PICASA:
					if($_POST['gallerytag'])
						mudsPicasa_update_gallery( $_POST['user_name'], $_POST['gallerytag'] );
					break;
				case MUDS_TYPE_FLICKR:
					if($_POST['gallerytag'])
						mudsFlickr_update_gallery( $_POST['user_name'], $_POST['gallerytag'] );
					break;
			}
			break;
		case 'addgallery':
			$show = 'addgallery';
			break;
	}
	
	//Do we have to show a message?
	if(count($messages)>0) {
		echo "<div class='updated'>";
		foreach($messages as $message) echo "<p><strong>$message</strong></p>";
		echo "</div>";
	}

	// Now display the options editing screen
	// options form
	switch($show) {
		case 'addgallery':
			include('templates/mudslide_addgallery.php');
			break;
		default:
			include('templates/mudslide_manage.php');
			break;
	}
}

/**
* Page to manage the options.
*
* @access public
*/
function muds_options()
{
	global $wpdb;
	$messages=array();

	$options = get_option('muds_options');

	$mode = $mode_x = false;

	if(isset($_POST['mode_x'])) $mode_x=$_POST['mode_x']; //Something to execute?
	if(isset($_GET['mode'])) $mode=$_GET['mode']; //Something to show?
	
	switch($mode_x) {
		case 'manage_x': //Update the config data
			$mode='manage';
			
			$options['gallery_type'] = $_POST['muds_gallery_type'];
			$options['columns'] = $_POST['muds_columns'];
			//$options['rows'] = $_POST['muds_rows'];
			$options['show_link'] = $_POST['muds_showlink'];
			$options['extend'] = $_POST['muds_extend'];
			$options['feature'] = $_POST['muds_feature'];
			$options['thsize'] = $_POST['thsize'];
			$options['thmaxsize'] = $_POST['thmaxsize'];
			$options['full_size'] = $_POST['full_size'];
			if(!is_numeric($options['thmaxsize'])) $options['thmaxsize'] = MUDS_DEFAULT_MAXSIZE;
						
			// Save the posted value in the database
			update_option( 'muds_options', $options);

			// Put an 'options updated' message on the screen
			array_push($messages,__( 'Options saved', 'mudslide' ));
			
			break;
	}
	
	switch($mode) {
		case 'manage':
			break;
	}
	
	//Do we have messages to show?
	if(count($messages)>0) {
		echo "<div class='updated'>";
		foreach($messages as $message) echo "<p><strong>$message</strong></p>";
		echo "</div>";
	}
	
	//Ok, show the dialog
	include('templates/mudslide_options.php');
}


/**
* Change the URL to unify it.
*
* @access public
* @param string url The url to unify.
* @return string The unifyed URL.
*/
function muds_decode($url) {
	while(strstr($url, '&amp;')) {
		$url = str_replace('&amp;', '&', $url);
	}
	return urldecode($url);
}

/**
* Filter to manage contents. Check for [mudslide] tags.
* This function should be called by a filter.
*
* @access public
* @param string content The content to change.
* @return string The content with the changes the plugin have to do.
*/
function muds_content($content) {
	global $post;
	$options = get_option('muds_options');
	$caption = false;
	
	//The images with caption!
	if($options['extend']) {
		$search = "/(?:<p>)*\s*\[caption(.*?)align=('|\")align([^>]*)('|\")(.*?)width=('|\")([\d]*)('|\")(.*?)caption=('|\")([^>]*)('|\")\](<a([^>]*)><img([^>]*)><\/a>)\[\/caption\]\s*(?:<\/p>)*/i";
		if(preg_match_all($search, $content, $matches)) { //search for mudslide picasa
			if (is_array($matches)) { //We found
				foreach ($matches[1] as $key =>$v0) { //for each gallery, do
					//Get the values
					$search = $matches[0][$key];
					$float = $matches[3][$key];
					if(strcmp($float, 'none') == 0) $float = 'left';
					$size = $matches[7][$key];
					$caption = $matches[11][$key];
					
					$xml = new SimpleXMLElement("<div>{$matches[13][$key]}</div>");
					$a = $xml->a->attributes();
					$img = $xml->a->img->attributes();
					
					$image  = $a->href;
					$title =  '';
					if(isset($img->title)) $title  = $img->title; else
					if(isset($img->alt)) $title  = $img->alt;
					$thumbnail = $img->src;
					
					if(strlen($caption)==0) $caption = false;
					
					$rand = mt_rand(111111,999999);
					//$replace = "<p>".muds_show($image, $thumbnail, $title,$rand, $float, $caption, $size, false, MUDS_TYPE_NONE)."</p>";
					$conf = 0;
					if(strlen($caption)>0) $conf=1;
					$replace = muds_show($image, $thumbnail, $title,$rand, $float, $caption, $size, false, MUDS_TYPE_NONE, $conf);
					if(function_exists('muds_hsScriptGallery')) $replace.=muds_hsScriptGallery($rand,true,$caption);
					$content = str_replace ($search, $replace, $content);
				}
			}
		}
	}
	
	//The images without caption!
	if($options['extend']) {
		$search = "/\s*<a([^>]*)><img([^>]*)><\/a>\s*/i";
		if(preg_match_all($search, $content, $matches)) { //search for image
			if (is_array($matches)) { //We found
				foreach ($matches[1] as $key =>$v0) { //for each gallery, do
					//Get the values
					$search = $matches[0][$key];
					//Is this an image from a previus mudslide change?
					if(strpos($search, "class='muds-feed'") === false) {
						$xml = new SimpleXMLElement("<div>$search</div>");
						$a = $xml->a->attributes();
						$img = $xml->a->img->attributes();
						$class = $img->class;
						$float = '';
						if(strpos($class, 'center')) $float = 'center';
						if(strpos($class, 'left')) $float = 'left';
						if(strpos($class, 'right')) $float = 'right';
						if(strpos($class, 'none')) $float = 'none';
						$size = $img->width;
						//$caption = $matches[11][$key];
						$image  = $a->href;
						$title =  '';
						if(isset($img->title)) $title  = $img->title; else
						if(isset($img->alt)) $title  = $img->alt;
						$thumbnail = $img->src;
						
						if(strlen($caption)==0) $caption = false;
						
						$rand = mt_rand(111111,999999);
						//$replace = "<p>".muds_show($image, $thumbnail, $title, $rand, $float, $caption, $size, false, MUDS_TYPE_NONE)."</p>";
						$replace = muds_show($image, $thumbnail, $title, $rand, $float, $caption, $size, false, MUDS_TYPE_NONE);
						if(function_exists('muds_hsScriptGallery')) $replace.=muds_hsScriptGallery($rand,true,$caption);
						$content = str_replace ($search, $replace, $content);
						$content = str_replace (array('<p><div', '<p style="text-align: center;"><div', '</div></p>'), array('<div', '<div', '</div>'), $content);
					}
				}
			}
		}
	}
	
	/*$replace = mudsPicasa_show_photo( $user, $gallery, $photo, $size, $float, $conf ); //Show the photo
   $replacement = '<a$1href=$2$3.$4$5 class="highslide-image" onclick="return hs.expand(this);"$6>$7</a>';
   $content = preg_replace($search, $replacement, $content);*/

	//MudSlideShow - Gallery
	$search = "/(?:<p>)*\s*\[mudslide:(picasa|flickr),(\d+),(htt[^,\]]+|([\w.\-\+@]+),([\w.\-\+@]+|\d+|[\w#]+))(,\d+,\d+|,(\d+c|\d+|s|t|m|l),(left|right|center)|)?\]\s*(?:<\/p>)*/i";
	if(preg_match_all($search, $content, $matches)) { //search for mudslide picasa
		if (is_array($matches)) { //We found
			foreach ($matches[1] as $key =>$v0) { //for each gallery, do
				
				//Get the values
				$search = $matches[0][$key];
				
				$type=$matches[1][$key];
				$conf=$matches[2][$key];
				
				$user = muds_decode($matches[3][$key]);
				$gallery=1;
				if($matches[5][$key]) {
					$user = $matches[4][$key];
					$gallery = $matches[5][$key];
				}
				
				$last=$matches[6][$key];
			
				$begin=0;
				$end=0;
				$simple=false;
				
				//If we have data at the end, search if it is for the begin and the end or
				//for a simple gallery 
				if($last) {
					$data=explode(',', $last);
					if(is_numeric($data[2])) { //This is begin-end data
						$begin=$data[1];
						$end=$data[2];
						if($first>$last) {
							$first=$last=0;
						}
					} else { //Simple gallery
						$simple=true;
						$size=$data[1];
						$float=$data[2];
					}
					
				}
				
				$rand = mt_rand(111111,999999); //The identifier
				
				if(!$simple) { //If it is not a simple gallery, show the gallery, else show simple
					switch($type) {
						case 'picasa': 
							$replace = mudsPicasa_show_gallery( $user, $gallery, $begin, $end, $conf, $rand );
							break;
						case 'flickr': 
							$replace = mudsFlickr_show_gallery( $user, $gallery, $begin, $end, $conf, $rand );
							break;
					}
				} else {
					switch($type) {
						case 'picasa':
							$replace = mudsPicasa_show_simple($user, $gallery, $size, $float, $conf, $rand);
							break;
						case 'flickr':
							$replace = mudsFlickr_show_simple($user, $gallery, $size, $float, $conf, $rand);
							break;
					}
				}
				//Ok, done with this, go ahead
				$replace = "<div id='mss$rand'>$replace</div>"; 
				$content= str_replace ($search, $replace, $content);
			}
		}
	}

	//MudSlideShow - Photo
	$search = "/(?:<p>)*\s*\[mudslide:(picasa|flickr),(\d+),(htt[^,]+|([\w.\-\+@]+),([\w.\-\+@]+|\d+|[\w#]+)),(\d+),(\d+|\d+c|s|t|m|l),(left|right|center)?\]\s*(?:<\/p>)*/i";
	if(preg_match_all($search, $content, $matches)) { //search for picasa photo
		if (is_array($matches)) { //we found
			foreach ($matches[1] as $key =>$v0) { //foreach photo, do
				//get the values
				$search = $matches[0][$key];
				$type=$matches[1][$key];
				$conf=$matches[2][$key];
				
				$user = muds_decode($matches[3][$key]);
				$gallery=1;
				if($matches[5][$key]) {
					$user = $matches[4][$key];
					$gallery = $matches[5][$key];
				}
				
				$photo=$matches[6][$key];
				$size=$matches[7][$key];
				$float=$matches[8][$key];

				switch($type) {
					case 'picasa':
						$replace = mudsPicasa_show_photo( $user, $gallery, $photo, $size, $float, $conf ); //Show the photo
						//$replace = "<p>".mudsPicasa_show_photo( $user, $gallery, $photo, $size, $float, $conf )."</p>"; //Show the photo
						break;
					case 'flickr':
						$replace = mudsFlickr_show_photo( $user, $gallery, $photo, $size, $float, $conf ); //Show the photo
						//$replace = "<p>".mudsFlickr_show_photo( $user, $gallery, $photo, $size, $float, $conf )."</p>"; //Show the photo
						break;
				}
				$content = str_replace ($search, $replace, $content);
			}
		}
	}
	
	$search = "/(?:<p>)*\s*\[mudthumb:(picasa|flickr),(htt[^,]+|([\w.\-\+@]+),([\w.\-\+@]+|\d+|[\w#]+)),(\d+)\]\s*(?:<\/p>)*/i";
	$content = preg_replace($search, '', $content);
	
	if(defined('MUDS_CREATE_THUMBS')) {
		global $post;
		muds_save_post($post->ID);
	}
	
	return $content;
}

/**
* Returns an array with filename, gallery_id and md5
* @acces private
* @param string user The user id or feed url
* @param string gallery The gallery id
* @return array with filename, gallery_id and md5
**/
function muds_detect_type($user, $gallery, $source) {
	$md5 = false;
	
	$search = "*******";
	switch($source) {
		case MUDS_TYPE_PICASA:
			$search = "http://picasaweb.google.com";
			$letter = "muds-p";
			break;
		case MUDS_TYPE_FLICKR:
			$search = "http://api.flickr.com";
			$letter = "muds-f";
			break;
	}
	
	$filename = $letter."t-$gallery.xml"; //We suppose it's a tag
	$album_type = MUDS_FILE_TAG; 
	
	if(strstr($user, $search)) { //An RSS feed
		//$user = str_replace('&amp;', '&', $user);
		$md5 = md5($user);
		$filename = $letter."f-$md5.xml";
		$gallery = 1;
		$album_type = MUDS_FILE_FEED;
	}
	
	if(strcmp($gallery, "0")==0 || strcmp($gallery,$user)==0) { //Zero gallery
		$gallery=$user;
		$filename = $letter."a-$gallery.xml";
		$album_type = MUDS_FILE_ZERO;
	}
	
	if(is_numeric($gallery) && !$md5) { //If the gallery is numeric and we don't have an md5, it means is an album
		$filename = $letter."a-$gallery.xml";
		$album_type = MUDS_FILE_ALBUM;
	}
	
	return compact("filename", "gallery", "md5", "album_type");
}

function muds_clip($type, $muds_maxsize, $muds_thsize) {

	//Select icon
	switch($type) {
		case MUDS_TYPE_PICASA:
			$icon = muds_plugin_url('img/source-picasa.gif');
			$icon = "Picasa"; //"<img src='$icon' alt='Picasa' border='0'>";
			$thumb_size = "800";
			$size_picasa = array("64", "72", "144", "160", "200", "288", "320", "400", "512", "576", "640", "720", "800");
			$size_picasa = array_reverse($size_picasa);
			foreach($size_picasa as $aux) {
				if($muds_maxsize<=$aux) $thumb_size = $aux;
			}
			if($muds_maxsize>$thumb_size) $muds_maxsize = $thumb_size;
			
			if($muds_thsize==MUDS_SQUARED && $thumb_size<=160) {
				$thumb_size.= "-c";
				$muds_thsize=MUDS_SQUARED;
			} else {
				$muds_thsize=MUDS_SCALED;
			}
			$full_size = 800;
			break;
		case MUDS_TYPE_FLICKR:
			$icon = muds_plugin_url('img/source-flickr.gif');
			$icon = "Flickr"; //"<img src='$icon' alt='Picasa' border='0'>";
			$thumb_size = "500";
			$size_flickr = array("75", "100", "240", "500");
			$size_flickr_index = array(75=>'s',100=>'t', 240=>'m', 500=>'l');
			$size_flickr = array_reverse($size_flickr);
			foreach($size_flickr as $aux) {
				if($muds_maxsize<=$aux) $thumb_size = $aux;
			}
			if($muds_maxsize>$thumb_size) $muds_maxsize = $thumb_size;
			
			
			if($muds_thsize==MUDS_SQUARED && $thumb_size<=75){
				$thumb_size = "s";
				$muds_thsize=MUDS_SQUARED;
			} else {
				$thumb_size = $size_flickr_index[$thumb_size];
				$muds_thsize=MUDS_SCALED;
			}
			$full_size = 'z';
			break;
	}
	return compact("full_size", "thumb_size", "icon");
}

/**
* Create the HTML code for a simple gallery. If first and last are equal to 0, show all the gallery
*
* @access public
* @param xml data The data description of the gallery.
* @param string update_post The post elements for the data.php script to update this gallery.
* @param int first The first foto to show
* @param int first The last foto to show
* @param int conf A configuration number for future releases.
* @return string The HTML code for the simple frame viewer.
*/
function muds_gallery($data, $update_post, $first=0, $last=0, $conf=0, $type, $rand=0) {
	global $wpdb, $post, $current_user, $wp_query;
	
	$feed = false;
	$force = false;
	if(isset($_POST['action']) &&  $_POST['action']=='muds_ajax_update') $force = true;
	if(!$force && ( is_feed() || $wp_query->post_count == 0 || (!is_singular() && !in_the_loop()) ) ) $feed = true;
	
	//The configuration data to show the gallery
	$options = get_option('muds_options');
	$muds_type = $options['gallery_type'];
	$muds_columns = $options['columns'];
	$muds_showlink = $options['show_link']; 
	$muds_thsize = $options['thsize'];
	$muds_maxsize = MUDS_DEFAULT_MAXSIZE;
	if($options['thmaxsize']) $muds_maxsize = $options['thmaxsize'];
	
	if($rand==0) $rand = mt_rand(111111,999999); //The identifier
	$answer="";
	$count=1; //Count the number of the picture
	$column=1; //Counter to determine if we have to jump
	$table="";
	
	extract(muds_clip($type, $muds_maxsize,$muds_thsize));
	
	//The nonce value
	$nonce = wp_create_nonce('mudslide');
	
	if($photo_gallery = new SimpleXMLElement($data)) { //Parse the data
		$answer="";
		$attr = $photo_gallery->attributes();
		$gallery_url = $attr->url;
		
		//Get the link to external gallery
		$link = "<a href='$gallery_url' target='_BLANK'>".sprintf(__('Open in %s', 'mudslide'), $icon)."</a>";
		
		if($first==0 || $last==0) { //If we don't have begin and end, show all the gallery
			$first=1;
			$last=count($photo_gallery->photo);
		}
		
		$total = count($photo_gallery->photo);
		$reverse = 0; 
		if($conf & MUDS_OPT_REVERSEORDER) {
			$reverse = $total - 1;
		}
		
		for($i = 0; $i < $total; $i++) { //gallery as $photo) { //Go trhough the gallery
			$photo = $photo_gallery->photo[abs($reverse - $i)];
			if($count>=$first && $count<=$last) { //If the photo is between the first and the last picture to show
				if($column==1 && !$feed) { //Are we starting a row?
					$table.="<tr>"; //We start the row
				}
				
				//Get the data
				$media = str_replace('%size%', $full_size, $photo->resize);
				$thumbnail = str_replace('%size%', $thumb_size, $photo->resize);
				if($type == MUDS_TYPE_FLICKR) $media = str_replace('__', '', $media);
				$attr2 = $photo->attributes();
				$url = $attr2->url;
				$title=$photo->title;
				$caption = false;
				
				if((int)$photo->height > (int)$photo->width) {
					$height = $muds_maxsize;
					$width = floor((int)$photo->width * $muds_maxsize / (int)$photo->height);
				} else {
					$width = $muds_maxsize;
					if((int)$photo->width > 0) {
						$height = floor((int)$photo->height * $muds_maxsize / (int)$photo->width);
					} else {
						$height = false;
					}
				}
				
				if($muds_thsize==MUDS_SQUARED) {
					$height = $width = $muds_maxsize;
				}
				if($conf & MUDS_OPT_COMMENT) { //Should we show the comment
					$caption = $photo->comment;
				}
				
				$options = get_option('muds_options');
				if($options['full_size'] == MUDS_ORIGINAL) {
					$media = $photo->src;
				}
				
				if(!$feed) $table.="<td align='center'><div class='slide'>".muds_show($media, $thumbnail, $title, $rand, 'center', $caption, $width, $url, $type, $conf)."</div></td>"; //Create a cell with the photo
				else $table.= muds_show($media, $thumbnail, $title, $rand, 'center', $caption, $width, $url, $type, $conf); //Create a cell with the photo
				if($column==$muds_columns && !$feed) { //is this the last picture from the row
					$table.="</tr>"; //close the row
					$column=0; //Start the counter again
				}

				$column++; //Add 1 to the column counter
			}
			$count++; //Add 1 to the picture counter
		}
		
		//No more pictures
		if($column!=1 && !$feed) { //Did we left a row opened?
			$table.="</tr>"; //Close the opened row
		}
		
		$sourcelink = "";
		if($muds_showlink && !$feed) $sourcelink = "<div class='throbber-off'>$link</div>";
		
		//Section to update the gallery
		if(($post->post_author == $current_user->id || current_user_can('edit_others_posts')) && !$feed) { //Can this user edit the post
			parse_str(str_replace('&amp;', '&', $update_post), $aux);
			if(!isset($aux['id'])) $aux['id']='false';
			if(!isset($aux['type'])) $aux['type']='false';
			if(!isset($aux['user'])) $aux['user']='false';
			if(!isset($aux['gallery'])) $aux['gallery']='false';
			
			$updatelink = "<div id='throbber-msspage$rand' class='throbber-off'><a style='cursor : pointer;' id='mss-update-$rand' onclick=\"
					var aux = document.getElementById('throbber-msspage$rand');
					aux.setAttribute('class', 'throbber-on');
					aux.setAttribute('className', 'throbber-on'); //IE sucks
					muds_update( 'gallery', {$aux['id']},'{$aux['type']}', '{$aux['user']}', '{$aux['gallery']}', $first, $last, $conf, $rand );\">".__('Update','mudslide')."</a></div>";
			
			//Add the AJAX call to update the gallery
			$table.="<tr><td colspan='$muds_columns' align='right'><!-- muds_begin -->$sourcelink $updatelink<!-- muds_end --></td></tr>";
		} else {
			if($muds_showlink  && !$feed) $table.= "<tr><td colspan='$muds_columns' align='right'><!-- muds_begin -->$sourcelink<!-- muds_end --></td></tr>";
		}
		
		//We have owr table!!!!!
		if(!$feed) $answer = "<table class='mudslideshow'>$table</table>";
		else $answer = "<p>$table</p>";
		if(function_exists('muds_hsScriptGallery') && !$feed) $answer.=muds_hsScriptGallery($rand,false,$caption);
	}
	
	return $answer;
}


/**
* Check if a transient is old or doesn't exists
*
* @access public
* @param string filename The file.
* @return bool True if it's old or doesn't exists, false if exists and is the version we need. 
*/
function muds_not_readable_file( $filename )
{
	$answer = false;
	if(!$data = get_transient($filename)) { //Doesn't exist
		$answer = true; 
	} else { //Exists
		$data = new SimpleXMLElement($data); //Parse XML
		$attr = $data->attributes();
		$version = $attr->version;//Get version
		if($version<MUDS_XML_V) { //Is older
			$answer = true;
		}
	}
	return $answer;
}

/**
* Create the HTML code for a single photo
*
* @access public
* @param xml data The data description of the gallery.
* @param int p_id The position of the photo in the gallery. Can be also LAST_PHOTO or RANDOM_PHOTO.
* @param int size The size of the photo in the post.
* @param string float Where the photo has to float.
* @param int conf A numeric value for the configuration.
* @return string The HTML code for the gallery the photos, false if not. 1 to show the first comment
	in picasaweb as a big description. 
*/
function muds_photo($data, $p_id, $size, $float, $conf, $type, $widget=false) {
	
	global $open_search;
	$answer = false;
	if($photo_gallery = new SimpleXMLElement($data)) { //Parse the data
		$count = count($photo_gallery->photo);
		if($count>0) {
			$alb_attr = $photo_gallery->attributes();
			$owner = $alb_attr->owner;
			if($p_id==RANDOM_PHOTO) $p_id=mt_rand(0,$count); // Show a random photo?
			if($p_id==LAST_PHOTO) $p_id=$count-1; //Show the last photo?
			if($count>=$p_id) { //Do the gallery have a photo in this position?
				$photo=$photo_gallery->photo[$p_id-1]; //Ok, get the photo
				$answer="";
				$thumbnail = str_replace('%size%', $size, $photo->resize);
				$squared =false;
				
				
				if( is_numeric($size) ) {
					extract(muds_clip($type, $size, MUDS_SCALED));
					$media = str_replace('%size%', $full_size, $photo->resize);
					if($type == MUDS_TYPE_FLICKR) $media = str_replace('__', '', $media);
					$thumbnail = str_replace('%size%', $thumb_size, $photo->resize);
				} else {
					if(substr($size,-1)=='c') { //It's a picasa squared image
						$size = substr($size,0,-2);
						extract(muds_clip($type, $size, MUDS_SQUARED));
						$media = str_replace('%size%', $full_size, $photo->resize);
						$thumbnail = str_replace('%size%', $thumb_size, $photo->resize);
						$squared =true;
					} else {
						if($size=='l') {
							$thumbnail = str_replace('_%size%', '', $photo->resize);	
						} else {
							$thumbnail = str_replace('%size%', $size, $photo->resize);
						}
						$media = str_replace('_%size%', '', $photo->resize);
					}
				}
				
				$attr = $photo->attributes();
				$url = $attr->url;
				$title = $photo->title;
				$photo_album = $attr->album;
				
				if(!$squared) {
					if((int)$photo->height > (int)$photo->width) {
						$height = $size;
						$width = floor((int)$photo->width * $size / (int)$photo->height);
					} else {
						$width = $size;
						if((int)$photo->width > 0) {
							$height = floor((int)$photo->height * $size / (int)$photo->width);
						} else {
							$height = false;
						}
					}
				} else {
					$width = $height = $size;
				}
				
				$caption=false;
				if($conf & 1) { //Should we show the comment?
					$caption = $photo->comment;
				}
				
				$options = get_option('muds_options');
				if($options['full_size'] == MUDS_ORIGINAL) {
					$media = $photo->src;
				}
				
				$rand = mt_rand(111111,999999);
				if($widget && $open_search) {
					$answer=muds_show_none($media, $thumbnail, $title, $rand, $float, $caption, $width, $url, $type, 0, muds_search($owner, $photo_album, $url));
				} else {
					$answer=muds_show($media, $thumbnail, $title, $rand, $float, $caption, $width, $url, $type, $widget);
				}
				if(function_exists('muds_hsScriptGallery')) $answer.=muds_hsScriptGallery($rand,true,$caption);
					
			} else { //You are asking for something that is not here.
				$answer=__('Photo doesn\'t exist', 'mudslide' );
			}
		} else {
			$answer=__('This album doesn\'t have photos', 'mudslide' );
		}
	}	
	return $answer;
}

/**
* Enable buttons in tinymce.
* This function should be called by an action.
*
* @access public
*/
function muds_add_buttons() {
	// Don't bother doing this stuff if the current user lacks permissions
	if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) return;

	// Add only in Rich Editor mode
	if ( get_user_option('rich_editing') == 'true') {
	 
		// add the button for wp21 in a new way
		add_filter('mce_external_plugins', 'add_mudslide_script');
		add_filter('mce_buttons', 'add_mudslide_button');
	}
}

/**
* Function to add the button to the bar.
* This function should be called by a filter.
*
* @access public
*/
function add_mudslide_button($buttons) {

	array_push($buttons, 'MudSlide');
	return $buttons;

}

/**
* Function to set the script which should answer when the user press the button.
* This function should be called by a filter.
*
* @access public
*/
function add_mudslide_script($plugins) {
	$pluginURL = muds_plugin_url('/tinymce/editor_plugin.js?ver='.MUDS_HEADER_V);
	$plugins['MudSlide'] = $pluginURL;
	return $plugins;
}

/**
* Return the HTML code for the photo in the widget.
*
* @access public
* @param int gallery The id of the gallery in the internal list.
* @param int photo_id The position of the photo in the gallery.
* @return string The HTML code. Empty if error. 
*/
function muds_widget_photo($gallery, $photo_id, $instance) {
	global $open_search;
	$open_search = false;
	if((int)$instance['search']) $open_search = true;
	$size = $instance['size'];
	$type = muds_get_gallery_type($gallery);
	$answer="";
	switch($type) {
		case MUDS_PICASA: 
			$answer = mudsPicasa_widget_photo(muds_get_gallery_file($gallery), $photo_id, $size);
			break;
		case MUDS_FLICKR: 
			$answer = mudsFlickr_widget_photo(muds_get_gallery_file($gallery), $photo_id, $size);
			break;
		default:
			$answer=__('Unsupported source type', 'mudslide');
			break;
	}
	return $answer;
}

/**
* Return the HTML for the widget, using the configuration variables.
*
* @access public
* @return string The HTML code. Empty if error. 
*/
function muds_widget_contents( $instance ) {
	global $wpdb;
	$table_name = $wpdb->prefix . "mudslide";
	$answer=__('Album doesn\'t exist', 'mudslide' );
	switch($instance['type']) {
		case MUDS_WIDGET_RANDOM:
			if($gallery_id=$wpdb->get_var("SELECT id FROM $table_name ORDER BY RAND() LIMIT 1"))
				$answer=muds_widget_photo($gallery_id,RANDOM_PHOTO, $instance);
			break;
		case MUDS_WIDGET_LAST:
			if($gallery_id=$wpdb->get_var("SELECT id FROM $table_name ORDER BY id DESC LIMIT 1"))
					$answer=muds_widget_photo($gallery_id,LAST_PHOTO, $instance);
			break;
		case MUDS_WIDGET_RANDOMLAST:
			if($gallery_id=$wpdb->get_var("SELECT id FROM $table_name ORDER BY id DESC LIMIT 1"))
					$answer=muds_widget_photo($gallery_id,RANDOM_PHOTO, $instance);
			break;
		case MUDS_WIDGET_RANDOMFROM:
			$gallery = $instance['gallery_id'];
			if(empty($gallery)) $gallery = $instance['gallery']; //A stupid bug
			if(muds_get_gallery_file($gallery))
				$answer=muds_widget_photo($gallery,RANDOM_PHOTO, $instance);
			break;
	}
	return $answer;
}


/**
* Function to create the post thumbnail.
* This function should be called by an action.
*
* @access public
*/
function muds_save_post($postID) {
	//If we require thumb
	if(function_exists('has_post_thumbnail')) {
		//Get the post ID we are working on
		if($parent_id = wp_is_post_revision($postID)) {
			$postID = $parent_id;
		}
		//If this post doesn't have a thumb, create one.
		if(!has_post_thumbnail($postID)) {
			//get new thumb data
			if($muds_src = muds_post_thumbnail($postID)) {
				//if we found a thumbnail in the post, and it is outside the server, upload it
				switch($muds_src[3]) {
					case 'picasa':
					case 'flickr':
						//Get the data
						$bits = muds_readfile($muds_src[2]);
						$url = parse_url($muds_src[2]);
						$path = $url['path'];
						$name = pathinfo($path);
						$name = rawurldecode($name['basename']);
						//Save it in the upload directory
						$upload = wp_upload_bits($name, null, $bits);
						$filename = $upload['file'];
						$wp_filetype = wp_check_filetype(basename($filename), null );
						$attachment = array(
							'post_mime_type' => $wp_filetype['type'],
							'post_title' => preg_replace('/\.[^.]+$/', '', $muds_src[0]),
							'post_content' => '',
							'post_status' => 'inherit'
						);
						$attach_id = wp_insert_attachment( $attachment, $filename, $postID );
						// you must first include the image.php file
						// for the function wp_generate_attachment_metadata() to work
						require_once(ABSPATH . "wp-admin" . '/includes/image.php');
						$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
						wp_update_attachment_metadata( $attach_id, $attach_data );
						break;
					case 'local':
						$attach_id = $muds_src[3];
						break;
					case 'img':
						//Sorry, we can resize this image.
						break;
				}
				if($attach_id) update_post_meta($postID, '_thumbnail_id', $attach_id);
			}
		}
	}
}


/**
* Function wraper to solve a situation with the name.
*
* @access public
* @return string The HTML code. False if none image.
*/
function mud_thumb($size = 100) {
	return muds_thumb($size);
}

/**
* Return the HTML for the firts simple image or the first from the gallery.
*
* @access public
* @return string The HTML code. False if none image.
*/
function muds_thumb($size = 100) {
	global $post, $id;
	$post_id = $post->ID;
	$image = false;
	$img = false;
	
	$search = $post->post_excerpt;
	if(empty( $search )) {
		$search = $post->post_content;
	}
	$search = explode( "<!--more-->" , $search );
	$search = $search[0];
	//Check if there isn't an image before -more-
	if(strpos($search, '<img')===false && strpos($search, '[mudslide:')===false && $image = muds_post_thumbnail($post_id)) {
		$title = $image[0];
		$src = false;
		switch($image[3]) {
			case 'picasa':
				$src = str_replace('%size%', '144-c', $image[1]);
				break;
			case 'flickr':
				$src = str_replace('%size%', 's', $image[1]);
				break;
			case 'local': 
				$img = wp_get_attachment_image( $image[2], array($size, $size) );
				break;
			case 'img': 
				//Sorry, we can't resize this image
				break;
		}
		if($src) {
			$img = "<img width='{$size}' height='{$size}' src='$src' class='attachment-{$size}x{$size}' alt='$title' title='$title'>";
		}
		if($img) $img = '<a class="post-thumb size-'.$size[0].'" border="0" href="'.get_permalink($post->ID).'">'.$img.'</a>';
	}
	return $img;
}

function muds_wpbook( $attachment, $post_id ) {
	$image = muds_featureImage( $post_id );
	if($image) {
		$attachment['picture'] = $image;
	}
	return $attachment;
}

/**
* Return the HTML for the firts simple image or the first from the gallery.
*
* @access public
* @return string The HTML code. False if none image.
*/
function muds_featureImage( $post_id ) {
	
	$post = get_post( $post_id );
	$image = false;
	
	//Try first with thumbnails
	if (function_exists('get_the_post_thumbnail') && has_post_thumbnail($post_id)) {
		$my_thumb_id = get_post_thumbnail_id($post_id);
		$my_thumb_array = wp_get_attachment_image_src($my_thumb_id);
		$image = $my_thumb_array[0]; // this should be the url
	} else { //If not thumb, it's show time		
		$image = muds_post_thumbnail($post_id);
		switch($image[3]) {
			case 'picasa':
			case 'flickr':
				$image = $image[2];
				break;
			case 'local': 
				$my_thumb_array = wp_get_attachment_image_src( $image[2] );
				$image = $my_thumb_array[0]; // this should be the url
				break;
			case 'img': 
				$image = $image[1];
				break;
		}
	}
	return $image;
}

/**
* Return an array with the data for the thumbnail.
*
* @access public
* @return string The HTML code. False if none image.
*/
function muds_post_thumbnail($post_id = false) {
	$options = get_option('muds_options');
	global $post;
	if(!$post_id) {
		$post_id = $post->ID;
	}
	$my_post = get_post($post_id);
	$image = false;
	if($options['feature'] && !get_post_meta($post_id, 'no_thumb', true)) {
		$content = $my_post->post_content;
		
		//MudThumb - Photo
		if(!$image) { // get the 1st image
			$search = "/(?:<p>)*\s*\[mudthumb:(picasa|flickr),(htt[^,]+|([\w.\-\+@]+),([\w.\-\+@]+|\d+|[\w#]+)),(\d+)\]\s*(?:<\/p>)*/i";
			if(preg_match_all($search, $content, $matches)) { //search for picasa photo
				if (is_array($matches)) { //we found
					$key = 0;
					//get the values
					$type=$matches[1][$key];
					
					$user = muds_decode($matches[2][$key]);
					$gallery=1;
					if($matches[4][$key]) {
						$user = $matches[3][$key];
						$gallery = $matches[4][$key];
					}
					
					$num=$matches[5][$key];
					
					switch($type) {
						case 'picasa':
							$image = mudsPicasa_thumbnail($user, $gallery, $num);
							break;
						case 'flickr':
							$image = mudsFlickr_thumbnail($user, $gallery, $num);
							break;
					}
				}
			}
		}
		
		//MudSlideShow - Photo
		if(!$image) { // get the 1st image
			$search = "/(?:<p>)*\s*\[mudslide:(picasa|flickr),(\d+),(htt[^,]+|([\w.\-\+@]+),([\w.\-\+@]+|\d+|[\w#]+)),(\d+),(\d+|\d+c|s||m|l),(left|right|center)?\]\s*(?:<\/p>)*/i";
			if(preg_match_all($search, $content, $matches)) { //search for picasa photo
				if (is_array($matches)) { //we found
					$key = 0;
					//get the values
					$type=$matches[1][$key];
					
					$user = muds_decode($matches[3][$key]);
					$gallery=1;
					if($matches[5][$key]) {
						$user = $matches[4][$key];
						$gallery = $matches[5][$key];
					}
					
					$num=$matches[6][$key];
					
					switch($type) {
						case 'picasa':
							$image = mudsPicasa_thumbnail($user, $gallery, $num);
							break;
						case 'flickr':
							$image = mudsFlickr_thumbnail($user, $gallery, $num);
							break;
					}
				}
			}
		}
		
		//MudSlideShow Gallery
		if(!$image) { // get the 1st image
			$search = "/(?:<p>)*\s*\[mudslide:(picasa|flickr),(\d+),(htt[^,\]]+|([\w.\-\+@]+),([\w.\-\+@]+|\d+|[\w#]+))(,\d+,\d+|,(\d+c|\d+|s|t|m|l),(left|right|center)|)?\]\s*(?:<\/p>)*/i";
			if(preg_match_all($search, $content, $matches)) { //search for mudslide picasa
				if (is_array($matches)) { //We found
					$key = 0;
					
					//Get the values
					$type=$matches[1][$key];
					$user = muds_decode($matches[3][$key]);
					$gallery=1;
					if($matches[5][$key]) {
						$user = $matches[4][$key];
						$gallery = $matches[5][$key];
					}
					
					$last=$matches[6][$key];
					$begin=1;
					
					//If we have data at the end, search if it is for the begin and the end or
					//for a simple gallery 
					if($last) {
						$data=explode(',', $last);
						if(is_numeric($data[2])) { //This is begin-end data
							$begin=$data[1];
						}
					}
				
					switch($type) {
						case 'picasa':
							$image = mudsPicasa_thumbnail($user, $gallery, $begin);
							break;
							case 'flickr':
							$image = mudsFlickr_thumbnail($user, $gallery, $begin);
							break;
					}
					
				}
			}
		}
		
		if($options['extend']) {
			if(!$image) { // get the 1st image
				$attachments = get_children(array('post_parent' => $post_id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'menu_order ID'));
				if($attachments) {
					$aux = array_shift($attachments);
					$image = array($aux->post_title, $aux->guid, $aux->ID, 'local');
				}
			}
			
			if(!$image) { // Ok, our last chance, search for an img tag in the post
				$search = "/\s*<img([^>]*)>\s*/i";
				if(preg_match_all($search, $content, $matches)) { //search for mudslide picasa
					if (is_array($matches)) { //We found
						$search = $matches[0][0];
						$xml = new SimpleXMLElement("<div>$search</div>");
						$img = $xml->img->attributes();
						if(isset($img->title)) $title  = $img->title; else
						if(isset($img->alt)) $title  = $img->alt;
						$thumbnail = $img->src;
						$image = array((string)$title, (string)$thumbnail, false, 'img');
					}
				}
			}
		}
	}
	return $image;
}

// check version. only 2.8 WP support class multi widget system
global $wp_version;
if((float)$wp_version >= 2.8) { //The new widget system

	class MudsWidget extends WP_Widget {
		
		/**
		 * constructor
		 */	 
		function MudsWidget() {
			parent::WP_Widget('mudslide', 'MudSlideShow', array('description' => __('MudSlideShow widget to show photos from the galleries you have added into your posts.', 'mudslide') ));	
		}
		
		/**
		 * display widget
		 */	 
		function widget($args, $instance) {
		
			$sidebars_widgets = get_option('sidebars_widgets');
		
			extract($args, EXTR_SKIP);
			echo $before_widget;
			$title = empty($instance['title']) ? '&nbsp;' : apply_filters('widget_title', $instance['title']);
			if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
			echo muds_widget_contents($instance);
			echo $after_widget;
			
			
		}
		
		/**
		 *	update/save function
		 */	 	
		function update($new_instance, $old_instance) {
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
			$instance['type'] = strip_tags($new_instance['type']);
			if($instance['type']==MUDS_WIDGET_RANDOMFROM) {
				$instance['gallery_id']=strip_tags($new_instance['gallery_id']);
			} else {
				$instance['gallery_id']=0;
			}
			$instance['size'] = strip_tags($new_instance['size']);
			if(!is_numeric($instance['size'])) $instance['size'] = 160;
			
			if($new_instance['search']) $instance['search'] = 1; else $instance['search'] = 0;
				
			return $instance;
		}
		
		/**
		 *	admin control form
		 */	 	
		function form($instance) {
			$default = 	array('title'=> __('Photo Album' , 'mudslide'), 'type'=>'1', 'gallery_id' => '0', 'size' => 160);
			$instance = wp_parse_args( (array) $instance, $default );
			
			$title = $instance['title'];
			$title_name = $this->get_field_name('title');
			$title_id = $this->get_field_id('title');
			
			$type = $instance['type'];
			$type_name = $this->get_field_name('type');
			$type_id = $this->get_field_id('type');
			
			$size = $instance['size'];
			$size_name = $this->get_field_name('size');
			$size_id_name = $this->get_field_id('size');
			
			$gallery = $instance['gallery_id'];
			if(empty($gallery)) $gallery = $instance['gallery']; //A stupid bug
			$gallery_name=$this->get_field_name('gallery_id');
			$gallery_id=$this->get_field_id('gallery_id');
			
			$search = $instance['search'];
			$search_name = $this->get_field_name('search');
			$search_id = $this->get_field_id('search');
			
			include('templates/mudslide_widget_manage.php');
		}
	}

	/* register widget when loading the WP core */
	add_action('widgets_init', 'muds_register_widgets');

	function muds_register_widgets(){
		// curl need to be installed
		register_widget('MudsWidget');
	}

}

/**
* A kind of readfile function to determine if use Curl or fopen.
*
* @access public
* @param string filename URI of the File to open
* @return The content of the file
*/
function muds_readfile($filename)
{
	//Just to declare the variables
	$data = false;
	$have_curl = false;
	$local_file = false;
	
	if(function_exists('curl_init')) { //do we have curl installed?
		$have_curl = true;
	}
	
	$search = "@([\w]*)://@i"; //is the file to read a local file?
	if (!preg_match_all($search, $filename, $matches)) {
		$local_file = true;
	}
	
	if($local_file) { //A local file can be handle by fopen
		if($fop = @fopen($filename, 'r')) {
			$data = null;
			while(!feof($fop))
				$data .= fread($fop, 1024);
			fclose($fop);
		}
	} else { //Oops, an external file
		if($have_curl) { //Try with curl
			if($ch = curl_init($filename)) {
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				$data=curl_exec($ch);
				curl_close($ch);
			}
		} else { //Try with fsockopen
			$url = parse_url($filename);
			if($fp = fsockopen($url['host'], 80)) {
				//Enviar datos POST
				fputs($fp, "GET " . $url['path'] . "?" . $url['query'] . " HTTP/1.1\r\n");
				fputs($fp, "HOST: " . $url['host'] . " \r\n");
				fputs($fp, "Connection: close \r\n\r\n");
				 
				//Obtener datos
				while(!feof($fp))
				    $data .= fgets($fp, 1024);
				fclose($fp);
				
				$chunked = false;
				$http_status = trim(substr($data, 0, strpos($data, "\n")));
				if ( $http_status != 'HTTP/1.1 200 OK' ) {
					die('The web service endpoint returned a "' . $http_status . '" response');
				}
				if ( strpos($data, 'Transfer-Encoding: chunked') !== false ) {
					$temp = trim(strstr($data, "\r\n\r\n"));
					$data = '';
					$length = trim(substr($temp, 0, strpos($temp, "\r")));
					while ( trim($temp) != "0" && ($length = trim(substr($temp, 0, strpos($temp, "\r")))) != "0" ) {
						$data .= trim(substr($temp, strlen($length)+2, hexdec($length)));
						$temp = trim(substr($temp, strlen($length) + 2 + hexdec($length)));
					}
				} elseif ( strpos($data, 'HTTP/1.1 200 OK') !== false ) {
					$data = trim(strstr($data, "\r\n\r\n"));
				}
			}
		}
	}

	return $data;
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
function muds_show($image, $thumbnail, $title, $album=0, $float='center', $comment=false, $width=200, $url, $type, $conf=0) {
	global $wp_query;
	$force = false;
	if(isset($_POST['action']) &&  $_POST['action']=='muds_ajax_update') $force = true;
	$options = get_option('muds_options');
	$set = $options['gallery_type'];
	//Detect if we aren't showing a post, page or search. 
	if(!$force && ( is_feed() || $wp_query->post_count == 0 || (!is_singular() && !in_the_loop() && !is_dynamic_sidebar()) ) ) $set = -1;
	$answer = false;
	switch($set) {
		case '-1':
			$answer = muds_show_feed($image, $thumbnail, $title, $album, $float, $comment, $width, $url, $type, $conf);
			break;
		case '1':
			$answer = muds_show_hs($image, $thumbnail, $title, $album, $float, $comment, $width, $url, $type, $conf);
			break;
		case '2':
			$answer = muds_show_fb($image, $thumbnail, $title, $album, $float, $comment, $width, $url, $type, $conf);
			break;
		case '3':
			$answer = muds_show_lb($image, $thumbnail, $title, $album, $float, $comment, $width, $url, $type, $conf);
			break;
		case '4':
			$answer = muds_show_wpp($image, $thumbnail, $title, $album, $float, $comment, $width, $url, $type, $conf);
			break;
		default:
			$answer = muds_show_none($image, $thumbnail, $title, $album, $float, $comment, $width, $url, $type, $conf);
			break;
	}
	return $answer;
}

/**
* Function to write the data needed in the header by fancybox.
*
* @access public
*/
function muds_header() {
	$options = get_option('muds_options');
	switch($options['gallery_type']) {
		case '1':
			require('viewers/highslide-header.php');
			break;
		case '2':
			require('viewers/fancybox-header.php');
			break;
		case '3':
			require('viewers/lytebox-header.php');
			break;
		default:
			require('viewers/none-header.php');
			break;
	}
}

/**
* Function to write the data needed in the header by fancybox.
*
* @access public
*/
function muds_footer() {
	$options = get_option('muds_options');
	switch($options['gallery_type']) {
		default:
			//No footer required
			break;
	}
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
function muds_show_simple($data, $update_post, $size, $float, $conf, $type, $rand=0) {
	$options = get_option('muds_options');
	$answer = false;
	switch($options['gallery_type']) {
		case '1':
			$answer = muds_show_simple_hs($data, $update_post, $size, $float, $conf, $type, $rand);
			break;
		default:
			$answer = muds_show_simple_general($data, $update_post, $size, $float, $conf, $type, $rand);
			break;
	}
	return $answer;
}

?>
