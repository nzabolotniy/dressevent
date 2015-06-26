<?php
/*
Plugin Name: 1 Flash Gallery
Plugin URI: http://1plugin.com/
Description: 1 Flash Gallery is a Photo Gallery with slideshow function, many skins and powerfull admin to manage your image galleries without any program skills
Version: 1.0.7
Author: 1plugin.com
Author URI: http://1plugin.com/
*/

if(!function_exists("simplexml_load_file")){
	define('FGALLERY_PHP4_MODE', 1);
	require_once "includes/simplexml.class.php";

	 function simplexml_load_file($file){
	  $sx = new SimpleXML;
	  return $sx->xml_load_file($file);
	 }
} else {
	define('FGALLERY_PHP4_MODE', 0);
}

global $wpdb;

// DENY direct access to the file 
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 
	die('You are not allowed to call this page directly.'); 
}

$siteurl = get_option('siteurl');
$extra_dir = str_replace($_SERVER['HTTP_HOST'],'',$siteurl);
$extra_dir = str_replace('http://','',$extra_dir);

//define table names and constants
define('FGALLERY_VERSION','1.0.7');
define('EXTRA_DIR',$extra_dir.'/');
define('ALBUMS_TABLE', $wpdb->prefix . "fgallery_albums");
define('IMAGES_TABLE', $wpdb->prefix . "fgallery_images");
define('IMAGES_TO_ALBUMS_TABLE', $wpdb->prefix . "fgallery_album_images");
define('ALBUMS_SETTINGS_TABLE', $wpdb->prefix . "fgallery_albums_settings");
define('FGALLERY_PATH', WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)));
define('FGALLERY_ABSPATH', ABSPATH.'/wp-content/plugins/'.basename(dirname(__FILE__)));
define('FGALLERY_DIR', ABSPATH . 'wp-content/uploads/fgallery');
define('COVER_PATH',WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/image.php/cover.jpg?width=200&amp;image='.EXTRA_DIR);
define('THUMB_PATH',WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/image.php/cover.jpg?width=600&amp;image='.EXTRA_DIR);
define('MUSIC_PATH','<musicPath>slideshow.mp3</musicPath>');
define('FGALLERY_API_KEY','0ae9d6da9808ca2047531f5b9142d924');
define('FGALLERY_API_SECRET','8a1f2349466f9de2f2e77c3e8b989abd');

include('includes/facebook.inc.php');

//registering install and uninstall hooks
register_activation_hook(__FILE__,'fgallery_install');
register_uninstall_hook(__FILE__,'fgallery_uninstall');

//localizations
$plugin_dir = basename(dirname(__FILE__)).'/languages/';
load_plugin_textdomain( 'fgallery', null, $plugin_dir );

// installation script
function fgallery_install() {
   global $wpdb;
   global $wp_filesystem;
   //add option for screen options (number of elements per page)
   add_option('1_flash_gallery_page_fgallery_images_per_page', '25', '', 'no');
   add_option('toplevel_page_fgallery_per_page', '25', '', 'no');
   add_option('fgallery_facebook_session');

    if($wpdb->get_var("SHOW TABLES LIKE '" . ALBUMS_TABLE . "'") != ALBUMS_TABLE) {
	add_option("fgallery_db_version", FGALLERY_VERSION);
	// create albums table
	$create_albums = "CREATE TABLE " . ALBUMS_TABLE . " (
						`gall_id` int(11) NOT NULL auto_increment,
						`gall_name` varchar(255) collate utf8_unicode_ci NOT NULL,
						`gall_description` text collate utf8_unicode_ci NOT NULL,
						`gall_cover` int(11) NOT NULL,
						`gall_createddate` datetime NOT NULL,
						`gall_createdby` int(11) NOT NULL,
						`gall_published` tinyint(1) NOT NULL,
						`gall_width` int(11) default '450',
					    `gall_height` int(11) default '385',
					    `gall_bgcolor` varchar(6) default 'ffffff',
					    `gall_type` smallint(3) default '3',
						`gall_order` int(11) NOT NULL,
						PRIMARY KEY  (`gall_id`)
					);" ;      
        require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
        dbDelta($create_albums);
    }

    if($wpdb->get_var("SHOW TABLES LIKE '" . IMAGES_TABLE . "'") != IMAGES_TABLE) {
	// create images table
	$create_images = "CREATE TABLE " . IMAGES_TABLE . " (
					  `img_id` int(11) NOT NULL auto_increment,
					  `img_caption` varchar(255) collate utf8_unicode_ci NOT NULL,
					  `img_description` text collate utf8_unicode_ci NOT NULL,
					  `img_date` datetime NOT NULL,
					  `img_type` varchar(50) collate utf8_unicode_ci NOT NULL,
					  `img_size` int(11) NOT NULL,
					  `img_path` varchar(255) collate utf8_unicode_ci NOT NULL,
					  `img_vs_folder` smallint(5) NOT NULL,
					  `img_parent` int(11) NOT NULL,
					  PRIMARY KEY  (`img_id`)
					)";       
        require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
        dbDelta($create_images);
    } 
	
    if($wpdb->get_var("SHOW TABLES LIKE '" . IMAGES_TO_ALBUMS_TABLE . "'") != IMAGES_TO_ALBUMS_TABLE) {
	// create table for relation between images and albums
	$create_albums_images = "CREATE TABLE " . IMAGES_TO_ALBUMS_TABLE . " (
					  `img_id` int(11) NOT NULL,
					  `gall_id` int(11) NOT NULL,
					  `gall_folder` int(11) NOT NULL,
					  `img_order` smallint(5) NOT NULL,
					  `img_url` varchar(255) collate utf8_unicode_ci NULL,
					  PRIMARY KEY  (`img_id`, `gall_id`)
					)";       
        require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
        dbDelta($create_albums_images);
    } 
	$version = get_option('fgallery_db_version', 0);
	if (version_compare($version, FGALLERY_VERSION, '<')) {
		$wpdb->query("ALTER TABLE `".IMAGES_TO_ALBUMS_TABLE."` ADD `img_url` VARCHAR( 255 ) collate utf8_unicode_ci NULL AFTER `img_order` ;");
	}

    if($wpdb->get_var("SHOW TABLES LIKE '" . ALBUMS_SETTINGS_TABLE . "'") != ALBUMS_SETTINGS_TABLE) {
	// create table for albums settings
	$albums_settings = "CREATE TABLE " . ALBUMS_SETTINGS_TABLE . " (
					  `gall_id` int(11) NOT NULL,
					  `value` text NOT NULL,
					  PRIMARY KEY  (`gall_id`)
					)";
        require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
        dbDelta($albums_settings);
    }
	update_option("fgallery_db_version", FGALLERY_VERSION);

	// try to make gallery dir if not exists
	if (!is_dir(FGALLERY_DIR)) {
		WP_Filesystem();
        @ mkdir(FGALLERY_DIR);
        @ mkdir(FGALLERY_DIR.'/tmp');
		@ chmod(FGALLERY_ABSPATH,'/imagecache', 777);
    }
	return true;
}

// uninstall script
function fgallery_uninstall() {
   global $wpdb;
   require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
   //drop tables after uninstall
   dbDelta("DROP TABLE ". ALBUMS_TABLE);
   dbDelta("DROP TABLE ". PHOTOS_TABLE);
   dbDelta("DROP TABLE ". IMAGES_TO_ALBUMS_TABLE);
   dbDelta("DROP TABLE ". ALBUMS_SETTINGS_TABLE);
   //remove gallery directory
   fgallery_delete_dir(FGALLERY_DIR); 
   return true;
}

function fgallery_get_current_user_role() {
	global $current_user;
	$user_roles = $current_user->roles;
	$user_role = array_shift($user_roles);
    return $user_role;
}

// defines the current user access level 
function fgallery_access_level(){
	$role = fgallery_get_current_user_role();
	switch ($role) {
		case 'administrator':
			return 10;
		case 'editor':
			return 7;
		case 'author':
			return 2;
		default: 
			return 2;
	}
}

// function that recursively removes directory and all files in it
function fgallery_delete_dir($path) {
    $files = glob("$path/*");
    foreach($files as $file) {
      if(is_dir($file) && !is_link($file)) {
        fgallery_delete_dir($file);
      } else {
        unlink($file);
      }
    }
    rmdir($path);
}

// initialization script
function fgallery_init() {
	global $wp_version;
	wp_register_script('uploadify', FGALLERY_PATH.'/js/uploadify/jquery.uploadify.v2.1.4.min.js',array('jquery'));
	wp_register_script('uploadjs', FGALLERY_PATH.'/js/fgallery_upload.js',array('jquery'));
	wp_register_script('fgalleryedit', FGALLERY_PATH.'/js/fgallery_edit.js',array('jquery'));
	wp_register_script('fgalleryjs', FGALLERY_PATH.'/js/fgallery.js',array('jquery'));
	wp_register_script('fgalleryimages', FGALLERY_PATH.'/js/fgallery_images.js',array('jquery'));
	wp_register_script('configurator', FGALLERY_PATH.'/js/configurator.js',array('jquery'));
	if ( version_compare( $wp_version, '3.1', '>=' ) ) {
		wp_register_script('uislider', FGALLERY_PATH.'/js/ui.slider31.js',array('jquery','jquery-ui-core'));// some code
	} else {
		wp_register_script('uislider', FGALLERY_PATH.'/js/ui.slider.js',array('jquery','jquery-ui-core'));
	}
	wp_register_script('copytoclipboard', FGALLERY_PATH.'/js/ZeroClipboard.js',array('jquery'));
	wp_register_script('copypref', FGALLERY_PATH.'/js/copy.js',array('jquery'));
	wp_register_script('swfhelper', FGALLERY_PATH. '/js/swfhelper.js',array('jquery'));
	wp_register_style('uploadifycss', FGALLERY_PATH. '/css/uploadify.css');
	wp_register_style('uislidercss', FGALLERY_PATH. '/css/ui.slider.css');
	wp_register_style('uithemecss', FGALLERY_PATH. '/css/ui.theme.css');
	wp_register_style('uitabscss', FGALLERY_PATH. '/css/ui.tabs.css');
	wp_register_style('configuratorcss', FGALLERY_PATH. '/css/configurator.css');
	wp_register_style('farbtasticcss', FGALLERY_PATH. '/css/farbtastic.css');
	wp_register_style('fgallerycss', FGALLERY_PATH. '/css/fgallery.css');
	//needed for flash embeding
    wp_enqueue_script('swfobject');
    wp_enqueue_script('thickbox');
	wp_enqueue_script('swfhelper');
	wp_enqueue_style('thickbox');
	// building menu items
	add_action('admin_menu', 'fgallery_add_pages');
}

add_action('init', 'fgallery_init');


// calls scripts for upload page
function fgallery_upload_scripts(){
	wp_enqueue_script('jquery-ui-tabs');
	wp_enqueue_script('uploadjs');
	wp_enqueue_script('uploadify');
	wp_enqueue_script('jquery-form');
}

// calls scripts for gallery index/add/edit pages
function fgallery_admin_scripts() {
global $wp_version;
	wp_enqueue_script('jquery-form');
    if ($_GET['action'] == 'edit' || $_GET['page'] == 'fgallery_add') {
		wp_enqueue_script('farbtastic');
		wp_enqueue_script('configurator');	
		wp_enqueue_script('jquery-ui-tabs');
		wp_enqueue_script('uislider');
		wp_enqueue_script('copytoclipboard');
		wp_enqueue_script('copypref');
	}
	if ($_GET['action'] == 'pref') {
		wp_enqueue_script('copytoclipboard');
		wp_enqueue_script('copypref');
	}
	if ($_GET['action'] == 'images') {
		wp_enqueue_script('jquery-color');
	}
	wp_enqueue_script('fgalleryedit');
	wp_enqueue_script('jquery-ui-sortable');
	wp_enqueue_script('fgalleryjs');
}

// calls scripts for images index/add/edit pages
function fgallery_admin_images_scripts(){
	wp_enqueue_script('jquery-ui-droppable');
	wp_enqueue_script('jquery-ui-draggable');
	wp_enqueue_script('fgalleryimages');
}

// calls css files for upload page
function fgallery_upload_styles() {
	wp_enqueue_style('uploadifycss');
	wp_enqueue_style('fgallerycss');
	wp_enqueue_style('uitabscss');
	wp_enqueue_style('uithemecss');
}

// calls css files for upload page
function fgallery_images_styles() {
	wp_enqueue_style('fgallerycss');
}

// calls css files for upload page
function fgallery_admin_styles() {
	wp_enqueue_style('fgallerycss');
	wp_enqueue_style('uitabscss');
	wp_enqueue_style('uithemecss');
	wp_enqueue_style('uislidercss');
	wp_enqueue_style('farbtasticcss');
	wp_enqueue_style('configuratorcss');
}

// adding items to menu
function fgallery_add_pages() {
	$pref = add_menu_page(__('1 Flash Gallery','fgallery'), __('1 Flash Gallery', 'fgallery'), 'upload_files', 'fgallery', 'fgallery_admin_albums');
	if ( function_exists('add_submenu_page') ) {
		$main   = add_submenu_page('fgallery', __('Galleries List','fgallery'), __('Galleries List','fgallery'), 'upload_files', 'fgallery', 'fgallery_admin_albums');
		$add    = add_submenu_page('fgallery', __('Create gallery','fgallery'), __('Create Gallery','fgallery'), 'upload_files', 'fgallery_add', 'fgallery_add_gallery');
		$images = add_submenu_page('fgallery', __('Images List','fgallery'), __('Images List','fgallery'), 'upload_files', 'fgallery_images', 'fgallery_images_page');
		$upload = add_submenu_page('fgallery', __('Upload images','fgallery'), __('Upload Images','fgallery'), 'upload_files', 'fgallery_upload', 'fgallery_upload_page');
	}
	add_action('admin_print_scripts-' . $upload, 'fgallery_upload_scripts');
	add_action('admin_print_scripts-' . $main, 'fgallery_admin_scripts');
	add_action('admin_print_scripts-' . $add, 'fgallery_admin_scripts');
	add_action('admin_print_scripts-' . $images, 'fgallery_admin_images_scripts');
	add_action('admin_print_styles-' . $upload, 'fgallery_upload_styles' );
	add_action('admin_print_styles-' . $images, 'fgallery_images_styles' );
	add_action('admin_print_styles-' . $main, 'fgallery_admin_styles' );
	add_action('admin_print_styles-' . $add, 'fgallery_admin_styles' );
}

/*
*	Starting admin pages render
*	
*/

function fgallery_upload_page() {
$folders = fgallery_get_folders();
$facebook = new FGalleryFacebookAPI;

?>
	<div class="wrap">
	<h2><?php _e('Upload images from:', 'fgallery');?></h2>
		<div id="upload_tabs">
			<ul>
				<li><a href="#second_tab"><?php _e('Local computer', 'fgallery')?></a></li>
				<li><a href="#ftp_tab"><?php _e('FTP folder', 'fgallery')?></a></li>
				<li><a href="#zip_tab"><?php _e('ZIP archive', 'fgallery')?></a></li>
				<li><a href="#flickr_tab"><?php _e('URL', 'fgallery')?></a></li>
				<li><a href="#wp_gall_tab"><?php _e('Wodpress Gallery', 'fgallery')?></a></li>
				<li><a href="#facebook_tab"><?php _e('Facebook', 'fgallery')?></a></li>
			</ul>
			<div id="second_tab">
				<div class="form_left">
				<label for="uploadify_img_folder"><?php _e('Images will be saved to:','fgallery')?></label>
				<select id="uploadify_img_folder">
						<option value="0"><?php _e('Root folder', 'fgallery')?></option>
						<?php 
								if (!empty($folders)):?>
							  <?php foreach ($folders as $item): ?>
								<option value="<?php echo $item['img_id']?>"><?php echo $item['img_caption']?></option>
							  <?php endforeach;?>  
							  <?php endif;
							?>
					</select> <br />
				<div id="fileQueue"></div>
				<input type="file" id="uploadify" name="uploadify" />
				<p><a href="javascript:void(0);"><?php _e('Cancel all uploads', 'fgallery');?></a> </p>
				<button><?php _e('Upload', 'fgallery');?></button>
				<script type="text/javascript">
					jQuery(document).ready(function() {
						jQuery("#uploadify").uploadify({
							'uploader'       : '<?php echo WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)) ?>/js/uploadify/uploadify.swf',
							'script'         : '<?php echo WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)) ?>/js/uploadify/uploadify.php',
							'cancelImg'      : '<?php echo WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)) ?>/images/cancel.png',
							'queueID'        : 'fileQueue',
							'fileDesc'       : 'Images',
							'folder'         : '<?php echo EXTRA_DIR ?>wp-content/uploads/fgallery',
							'fileExt'        : '*.jpg;*.jpeg;*.gif;*.bmp;*.png',
							'auto'           : false,
							'multi'          : true,
                            'removeCompleted': true,
							'onComplete'     : function(event, queueID, fileObj, response, data){
									var array = response.split("_");
									var id = "image_"+array[1];
									jQuery("#uploaded_images").append('<div class="uploaded_image '+id+'"><img src="<?php echo COVER_PATH?>'+array[0]+'" alt="'+fileObj.name+'" /><p>'+fileObj.name+'</p><a href="javascript:void(0)" rel="admin.php?page=fgallery_images&amp;action=delete&amp;id='+array[1]+'" id="'+id+'" title="<?php _e('Delete','fgallery') ?>" class="fgallery_action delete" onclick="delete_img('+array[1]+');"><?php _e('Delete','fgallery') ?></a></div>');
												},
							'onAllComplete'  : function () {
									jQuery("#uploaded_images").append('<br clear="all" /><a href="admin.php?page=fgallery_images&amp;folder='+jQuery('#uploadify_img_folder').val()+'" class="fgallery_action"><?php _e('Save','fgallery')?></a>');
							}
						});
					});
				</script>
				</div>
				<div class="form_left" id="uploaded_images">
				</div>
				<br clear="all" />
			</div>
			<div id="ftp_tab">
				<form method="post" action="<?php echo fgallery_upload_ftp_get_url();?>" class="upload">
				<label for="fgallery_url_folder"><?php _e('Images will be saved to:','fgallery')?></label>
				<select name="fgallery_url_folder" id="fgallery_url_folder">
						<option value="0"><?php _e('Root folder', 'fgallery')?></option>
						<?php 
								if (!empty($folders)):?>
							  <?php foreach ($folders as $item): ?>
								<option value="<?php echo $item['img_id']?>"><?php echo $item['img_caption']?></option>
							  <?php endforeach;?>  
							  <?php endif;
							?>
					</select> <br />
					<label for="ftp_name"><?php _e('FTP server name','fgallery')?></label><br />
					<input type="text" name="ftp_name" id="ftp_name" /><br />
					<label for="ftp_folder"><?php _e('FTP Folder name','fgallery')?></label><br />
					<input type="text" name="ftp_folder" id="ftp_folder" /><br />
					<label for="fgallery_ftp_subfolders"><?php _e('Include subfolders','fgallery')?></label>
					<input type="checkbox" name="fgallery_ftp_subfolders" id="fgallery_ftp_subfolders" value="1" /> <br />
					<label for="ftp_username"><?php _e('FTP username');?></label><br />
					<input type="text" name="ftp_username" id="ftp_username" value="anonymous" /> <br />
					<label for="ftp_pass"><?php _e('FTP password');?></label><br />
					<input type="password" name="ftp_pass" id="ftp_pass" />
					<?php wp_nonce_field('fgallery_upload_files','fgallery_upload_files_field');?><br />
					<input type="submit" value="<?php _e('Upload', 'fgallery');?>" />
				</form>
			</div>
			<div id="zip_tab">
				<form method="post" action="<?php echo fgallery_upload_zip_get_url();?>" enctype="multipart/form-data" class="upload">
				<label for="zip_folder"><?php _e('Images will be saved to:','fgallery')?></label>
				<select name="zip_folder" id="zip_folder">
						<option value="0"><?php _e('Root folder', 'fgallery')?></option>
						<?php 
								if (!empty($folders)):?>
							  <?php foreach ($folders as $item): ?>
								<option value="<?php echo $item['img_id']?>"><?php echo $item['img_caption']?></option>
							  <?php endforeach;?>  
							  <?php endif;
							?>
					</select> <br />
					<input type="file" name="fgallery_zip" id="fgallery_zip" />
					<input type="submit" value="<?php _e('Upload', 'fgallery');?>" />
				</form>
			</div>
			<div id="flickr_tab">
				<?php _e('Choose images to add','fgallery')?> <br />
				<p class="details"><?php _e('Images will be downloaded from remote server','fgallery')?></p>
				<form method="post" action="<?php echo fgallery_upload_url_get_url();?>" id="url">
				<label for="fgallery_url_folder"><?php _e('Images will be saved to:','fgallery')?></label>
					<select name="fgallery_url_folder" id="fgallery_url_folder">
						<option value="0"><?php _e('Root folder', 'fgallery')?></option>
						<?php 
								if (!empty($folders)):?>
							  <?php foreach ($folders as $item): ?>
								<option value="<?php echo $item['img_id']?>"><?php echo $item['img_caption']?></option>
							  <?php endforeach;?>  
							  <?php endif;
							?>
					</select>
					<?php wp_nonce_field('fgallery_upload_files','fgallery_upload_files_field');?>
					<div class="input_fields">
					<label for="fgallery_ftp_0"><?php _e('Type the URL of the image','fgallery')?></label><br />
					<div id="fgallery_url_wrap_0" class="fgallery_url_wrap"><input type="text" name="fgallery_url[]" id="fgallery_url_0" class="fgallery_url" onchange="show_img_from_url(this.value,0)" /><img src="" id="fgallery_img_0" width="100"/><a class="delete_url" onclick="delete_url(0)" title="<?php _e('Delete url','fgallery')?>"></a>
					</div>
					</div>
					<a class="add_url" title="<?php _e('Add url','fgallery')?>"></a> <br />
					<input type="submit" value="<?php _e('Save', 'fgallery');?>" />
				</form>
			</div>
			<div id="wp_gall_tab">
				<?php _e('Choose images from WordPress Gallery to add','fgallery')?>
				<?php 
					$args = array('post_type'=>'attachment','post_mime_type'=>'image','numberposts'=>999999999);
					$images = get_posts($args);
					?>
				<p class="details"><?php _e('Images won\'t be copied only the info','fgallery')?></p>
				<form method="post" action="<?php echo fgallery_upload_media_get_url();?>" class="upload">
				<label for="fgallery_url_folder"><?php _e('Images will be saved to:','fgallery')?></label>
					<select name="fgallery_url_folder" id="fgallery_url_folder">
						<option value="0"><?php _e('Root folder', 'fgallery')?></option>
						<?php 
								if (!empty($folders)):?>
							  <?php foreach ($folders as $item): ?>
								<option value="<?php echo $item['img_id']?>"><?php echo $item['img_caption']?></option>
							  <?php endforeach;?>  
							  <?php endif;
							?>
					</select>
					<?php wp_nonce_field('fgallery_upload_files','fgallery_upload_files_field');?>
					<div class="media_files">
					<?php fgallery_render_media_images_table($images) ?>
					</div>
					<input type="submit" value="<?php _e('Save', 'fgallery');?>" />
					</form>
			</div>
			<div id="facebook_tab">
				<?php 
					//echo 'Session ='; print_r($facebook->sessions[$_GET['deactivate-facebook']]);
					// authorize session
					if(isset($_POST['activate-facebook'])) {
						$facebook->get_auth_session($_POST['activate-facebook']);
					}

					// remove the user
					if(isset($_GET['deactivate-facebook']) && isset($facebook->sessions[$_GET['deactivate-facebook']])) {
						$facebook->remove_user($_GET['deactivate-facebook']);
					} 
				?>		
				<?php if($facebook->msg): ?>
					<div id="message" class="<?php echo $facebook->error ? 'error' : 'updated' ?> fade"><p><?php echo $facebook->msg ?></p></div>
				<?php endif; ?>
				<table class="accounts">
				<tr>
					<td valign="top" width="170">
						<h3>Add an Account</h3>
						<?php if($facebook->token): ?>
						<form method="post" id="apply-permissions" action="<?php echo fgallery_get_upload_url(); ?>" class="facebook_confirm">
							<input type="hidden" name="activate-facebook" value="<?php echo $facebook->token ?>" />
							<p><a id="grant-permissions" href="http://www.facebook.com/login.php?api_key=<?php echo FGALLERY_API_KEY ?>&amp;v=1.0&amp;auth_token=<?php echo $facebook->token ?>&amp;popup=0&amp;skipcookie=1&amp;ext_perm=user_photos,offline_access,user_photo_video_tags" class="button-secondary" target="_blank">Step 1: Authenticate &gt;</a></p>
							<p><a id="request-permissions" href="http://www.facebook.com/connect/prompt_permission.php?api_key=<?php echo FGALLERY_API_KEY ?>&next=<?php echo urlencode('http://www.facebook.com/desktopapp.php?api_key='.FGALLERY_API_KEY.'&popup=1') ?>&cancel=http://www.facebook.com/connect/login_failure.html&display=popup&ext_perm=offline_access,user_photos,user_photo_video_tags" class="button-secondary" target="_blank">Step 2: Get Permissions &gt;</a></p>
							<p><input type="submit" class="button-secondary" value="Step 3: Apply Permissions &gt;" /></p>
						</form>
						<?php else: ?>
						Unable to get authorization token.
						<?php endif ?>
					</td>
					<td valign="top">
						<h3>Current Accounts</h3>
						<?php 
						if($facebook->link_active()): 
						foreach($facebook->sessions as $key=>$session): 
							$uid = $session['uid'];
							$facebook->select_session($uid);
							//$fb_user_photos = $facebook->facebook->photos_get($uid, null, null);
						?>
						<form action="<?php echo fgallery_get_upload_url() ?>" method="get" class="facebook_confirm">
							<img src="http://www.facebook.com/favicon.ico" align="absmiddle"> <a href="http://www.facebook.com/profile.php?id=<?php echo $uid ?>" target="_blank"><?php echo $session['name']; ?></a>
							<input type="hidden" name="deactivate-facebook" value="<?php echo $key ?>" />
							<input type="hidden" name="page" value="<?php echo $_GET['page'] ?>" />
							<input type="submit" class="button-secondary" value="Remove" />
						</form>
						<?php endforeach; ?>
						<?php else: ?>
						<p>There are currently no active Facebook accounts.</p>
						<?php endif; ?>
						<?php if($facebook->link_active()): ?>
						<p><small>This plugin has been given access to data from your Facebook account.	You can revoke this access at any time by clicking remove above or by changing your <a href="http://www.facebook.com/privacy.php?view=platform&tab=ext" target="_blank">privacy</a> settings.</small></p>
						<?php endif; ?>
					</td>
				</tr>
			</table>		
			<?php	if($facebook->link_active()): ?>
			<form method="post" action="<?php echo fgallery_upload_facebook_get_url();?>" class="upload">
				<?php 
				//$photos = $facebook->facebook->fql_query("SELECT pid, aid, owner, src, src_big, src_small, link, caption, created FROM photo WHERE aid IN (SELECT aid FROM album WHERE owner = '$uid')");
				$fb_photos = array_merge($fb_user_photos, (array) $photos);
				if (count($fb_photos) >0):
				foreach ($fb_photos as $photo): ?>
				<div class="facebook_photo">
					<input type="checkbox" value="1" name="fgallery_url_check[<?php echo $photo['pid']?>]"/>
					<input type="hidden" name="fgallery_url[<?php echo $photo['pid']?>]" class="fgallery_url" value="<?php echo $photo['src_big']?>" /> 
					<img src="<?php echo $photo['src_small']?>" alt="<?php echo $photo['caption']?>" />
					<div>
						<p><?php echo $photo['caption']?></p>
					</div>
				</div>
				<?php endforeach;
					  endif;
				  ?>
				  <br clear="all" />
				  <?php wp_nonce_field('fgallery_upload_files','fgallery_upload_files_field');?>
				  <input type="submit" value="<?php _e('Save');?>" />
			</form>
			<?php endif;?>
			

			</div>
			<div class="ajax_loader"></div>
		</div>
		<div class="fgallery_box"><?php                                
								if (!is_dir(FGALLERY_DIR)) {
									echo '<p class="fgallery_error">'.__("Folder", 'fagllery').' wp-content/uploads/fgallery '.__("doesn't exist", 'fagllery').'</p>';
                               } elseif (!is_writeable(FGALLERY_DIR)) {
                                   echo '<p class="fgallery_error">'.__("Folder", 'fagllery').' wp-content/uploads/fgallery '.__("is not writable", 'fagllery').'</p>';
                               }
							   _e('Note: Important! If single image is more then 1Mb galleries may work slower', 'fgallery');?> </div>
	</div>
<?php	
}

function fgallery_render_media_images_table($images) {
if (!empty($images)) {
	foreach ($images as $image) {
		?>
		<div class="image_wrap">
			<div class="fgallery_image_info">
				<input type="checkbox" name="media[]" value="<?php echo $image->ID?>" />
			</div>
			<div class="fgallery_image">
			<?php 				
				echo '<img src="'.COVER_PATH.str_replace('http://'.$_SERVER['HTTP_HOST'].EXTRA_DIR,'',$image->guid).'" alt="'.$image->post_title.'" />';
					
			?>
			</div>
			<div class="fgallery_image_info">
				<?php echo $image->post_title?>
			</div>
		</div>
		<?php
	}
}

}

function fgallery_preferences_page() {
    global $wp_version;
	?>
	<div class="wrap">
		<h2><?php _e('Preferences page','fgallery')?></h2>
		<div id="fgallery_faq" class="fgallery_box">
			<a href="http://1plugin.com/faq"><?php _e('FAQ','fgallery')?></a>
		</div>
		<div id="fgallery_settings" class="fgallery_box">
			<h2><?php _e('Server Settings', 'fgallery')?></h2>
                        <?php echo '<p><span class="lbl">'.__('Server Name:', 'fagllery').'</span>'.$_SERVER['SERVER_NAME']."</p>";
                               echo '<p><span class="lbl">'.__('Document Root:', 'fagllery').'</span>'.$_SERVER['DOCUMENT_ROOT']."</p>";
                               echo '<p><span class="lbl">'.__('Web server:', 'fagllery').'</span>'.$_SERVER['SERVER_SOFTWARE']."</p>";
                               echo '<p><span class="lbl">'.__('Host:', 'fagllery').'</span>'.$_SERVER['HTTP_HOST']."</p>";
                               echo '<p><span class="lbl">'.__('Client Agent:', 'fagllery').'</span>'.$_SERVER['HTTP_USER_AGENT']."</p>";
                               echo '<p><span class="lbl">'.__('Word Press version:', 'fagllery').'</span>'.$wp_version;
                               echo '<p><span class="lbl">'.__('Plugin version:', 'fagllery').'</span>'.FGALLERY_VERSION;
                               echo '<p><span class="lbl">'.__('Max size of uploaded file:', 'fagllery').'</span>'.ini_get('upload_max_filesize').'</p>';
                               if (!is_dir(FGALLERY_DIR)) {
                                   echo '<p class="fgallery_error">'.__("Folder", 'fagllery').' wp-content/uploads/fgallery '.__("doesn't exist", 'fagllery').'</p>';
                               } elseif (!is_writeable(FGALLERY_DIR)) {
                                   echo '<p class="fgallery_error">'.__("Folder", 'fagllery').' wp-content/uploads/fgallery '.__("is not writable", 'fagllery').'</p>';
                               }
                               if (!is_dir(FGALLERY_DIR.'/tmp')) {
                                   echo '<p class="fgallery_error">'.__("Folder", 'fagllery').' wp-content/uploads/fgallery/tmp '.__("doesn't exist", 'fagllery').'</p>';
                               } elseif (!is_writeable(FGALLERY_DIR.'/tmp')) {
                                   echo '<p class="fgallery_error">'.__("Folder", 'fagllery').' wp-content/uploads/fgallery/tmp '.__("is not writable", 'fagllery').'</p>';
                               }
                               if (!is_dir(ABSPATH . 'wp-content/plugins/'.basename(dirname(__FILE__)) .'/imagecache') || !is_writeable(ABSPATH . 'wp-content/plugins/'.basename(dirname(__FILE__)) .'/imagecache')) {
                                   echo '<p class="fgallery_error">'.__("Folder", 'fagllery').' wp-content/plugins/'.basename(dirname(__FILE__)) .'/imagecache '.__("is not writable", 'fagllery').'</p>';
                               }
                         ?>
		</div>
                <?php echo '<button id="copypref" rel="'.FGALLERY_PATH.'/swf/ZeroClipboard.swf">'.__('Copy settings to clipboard', 'fagllery').'</button>';?>
		<div id="fgallery_updates" class="fgallery_box">
			<h2><?php _e('Updates', 'fgallery')?></h2>
			<h2><?php _e('Already Installed Updates', 'fgallery')?></h2>
			<?php $updated = false;
				  if (get_option('1_flash_gallery_acosta') != '') {
					echo '1 Flash Gallery Update Acosta <br />';
					$updated = true;
				  }
				  if (get_option('1_flash_gallery_airion') != '') {
					echo '1 Flash Gallery Update Airion <br />';
					$updated = true;
				  }
				  if (get_option('1_flash_gallery_arai') != '') {
					echo '1 Flash Gallery Update Arai <br />';
					$updated = true;
				  }
				  if (get_option('1_flash_gallery_pax') != '') {
					echo '1 Flash Gallery Update Pax <br />';
					$updated = true;
				  }
				  if (get_option('1_flash_gallery_pazin') != '') {
					echo '1 Flash Gallery Update Pazin <br />';
					$updated = true;
				  }
				  if (get_option('1_flash_gallery_postma') != '') {
					echo '1 Flash Gallery Update Postma <br />';
					$updated = true;
				  }
				  if (get_option('1_flash_gallery_pageflip') != '') {
					echo '1 Flash Gallery Update PageFlip<br />';
					$updated = true;
				  }
				  if (!$updated) {
					_e('There are no updates yet. You can get them at <a href="http://1plugin.com/order">1 Flash Gallery Wordpress Plugin Site</a>', 'fgallery');
				  }
				  ?>
		</div>
	</div>
	<?php 
}

function fgallery_add_gallery() {
	fgallery_edit_album_page(0);
}

function fgallery_admin_albums() {
global $wpdb;
if (isset($_GET['action'])) {
	$action = $_GET['action'];
}
	switch ($action) {
		case 'pref':
			fgallery_preferences_page();
		break;
		case 'edit':
			if (isset($_GET['id']) && is_numeric($_GET['id'])) {
				$id = $_GET['id'];
				fgallery_edit_album_page($id);
			}
		break;
		case 'images':
			if (isset($_GET['id']) && is_numeric($_GET['id'])) {
				$id = $_GET['id'];
				fgallery_edit_album_images_page($id);
			}
		break;
		case 'delete':
			if (isset($_GET['id']) && is_numeric($_GET['id'])) {
				$id = $_GET['id'];
				fgallery_delete_album($id);
			}
		break;
		case 'sort':
			if (isset($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['order']) && is_numeric($_GET['order'])) {
				$id = $_GET['id'];
				$order = $_GET['order'];
				$wpdb->update(ALBUMS_TABLE, array('gall_order' => $order), array('gall_id' => $id));
			}
			die();
		break;
				
		default:
		// -- pagination ------------
		if (!empty($_POST) && wp_verify_nonce($_POST['screenoptionnonce'], 'screen-options-nonce')) {
				update_option( $_POST['wp_screen_options']['option'],  $_POST['wp_screen_options']['value']);
			}
		$pagenum = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 0;
                $sort_by = isset($_GET['sort']) ? absint($_GET['sort']) : 4;
		if ( empty($pagenum) )
			$pagenum = 1;
		if ( empty( $per_page ) || $per_page < 1 )
			$per_page = get_option('toplevel_page_fgallery_per_page',25);
                        apply_filters('toplevel_page_fgallery_per_page', $per_page);
		$num_pages = ceil(fgallery_albums_count() / $per_page);
		$page_links = paginate_links( array(
			'base' => add_query_arg( 'paged', '%#%' ),
			'format' => '',
			'prev_text' => __('&laquo;'),
			'next_text' => __('&raquo;'),
			'total' => $num_pages,
			'current' => $pagenum
		));
		// ---------------------------	
			$items = fgallery_get_albums($pagenum,$per_page, $sort_by); ?>
			<div class="wrap">
			<h2><?php _e('Gallery List', 'fgallery') ?></h2>
			<div class="tablenav">
                        <form action="" method="get" class="form_left">
                        <span><?php _e('Sort by:', 'fgallery')?></span>
                            <select name="sort" id="images_sortby">
                                <option value="0" <?php if ($_GET['sort'] == 0) echo 'selected="selected"'?>><?php _e('Title ASC', 'fgallery');?> </option>
                                <option value="1" <?php if ($_GET['sort'] == 1) echo 'selected="selected"'?>><?php _e('Title DESC', 'fgallery');?> </option>
                                <option value="2" <?php if ($_GET['sort'] == 2) echo 'selected="selected"'?>><?php _e('Date ASC', 'fgallery');?> </option>
                                <option value="3" <?php if ($_GET['sort'] == 3) echo 'selected="selected"'?>><?php _e('Date DESC', 'fgallery');?> </option>
                            </select>
                            <input type="hidden" name="page" value="fgallery" />
                            <input type="hidden" name="paged" value="<?php echo $pagenum; ?>" />
                        </form>
			<?php if ( $page_links ) { ?>
				<div class="tablenav-pages">
				<?php $count_posts = fgallery_albums_count();
                              $page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>%s',
                                                                    number_format_i18n( ( $pagenum - 1 ) * $per_page + 1 ),
                                                                    number_format_i18n( min( $pagenum * $per_page, $count_posts ) ),
                                                                    number_format_i18n( $count_posts ),
                                                                    $page_links
                                                                    );
					echo $page_links_text; ?>
				</div>
			<?php } ?>
						<a href="<?php echo fgallery_createalbum_url()?>" title="<?php _e('Create gallery', 'fgallery')?>" id="add_gallery" class="fgallery_action"><?php _e('Create gallery', 'fgallery') ?></a>
			<a href="<?php echo fgallery_get_pref_url()?>" title="<?php _e('Preferences', 'fgallery')?>" class="fgallery_action"><?php _e('Preferences', 'fgallery') ?></a>
			</div>
			<?php 
			echo fgallery_render_albums_table($items);
			?>
				<div class="tablenav">
				<?php
				if ( $page_links ) { 
					echo "<div class='tablenav-pages'>$page_links_text</div>";
				} ?>
				</div>
			</div> <?php 
		break;
	}
	
	return true;
}

/*
 *   Begin crud routines
 */
 
 function fgallery_edit_album_images_page($id){
	$folder = isset($_GET['folder']) ? $_GET['folder'] : 0;
 	$images = fgallery_get_album_images($id, $folder);
	$album = fgallery_get_album($id);
		echo '<div class="wrap"><div id="fgallery_images">';
		echo '<h2>'.__('Images in', 'fgallery').'<a href="'.fgallery_get_edit_url($id).'"> '.$album['gall_name'].'</a></h2><div class="tablenav">';
		$title = __('Add images', 'fgallery');
		echo '<a href="'.fgallery_get_addimages_url($id).'" title="'.$title.'" class="thickbox fgallery_action">'.$title.'</a>';
		$title = __('Create album', 'fgallery');
		echo '<a href="'.fgallery_create_folder_url(2,$id).'" title="'.$title.'" class="thickbox fgallery_action">'.$title.'</a></div>';
		if (!empty($images)){
			echo fgallery_render_album_images($images, $id);	
		} else {
			_e('There are no images in this gallery', 'fgallery');
		}
		echo '</div></div>';
 }
 
 // render edit gallery page
function fgallery_edit_album_page($id){
	echo '<div class="wrap">';
	$album = fgallery_get_album($id);
	if (empty($album)) {
		$album['gall_id'] = 0;
		$album['gall_width'] = 450;
		$album['gall_height'] = 385;
		$album['gall_bgcolor'] = "ffffff";
		$album['gall_type'] = 3;
	}
	if ($id != 0) {
		echo '<h2>'.__('Edit gallery', 'fgallery').' "'.$album['gall_name'].'"</h2>';
	} else {
		echo '<h2>'.__('Add new gallery', 'fgallery').'</h2>';
	}
	echo '<div id="fgallery_settings_form">';
	if ($album['gall_published'] && $id != 0) {    
		echo '<p>'.__('Embed code', 'fgallery').'</p>';
		echo '<div id="shortcode_view">'.fgallery_do_shortcode($album['gall_id']).'</div>';
        echo '<button id="shortcode" rel="'.FGALLERY_PATH.'/swf/ZeroClipboard.swf">'.__('Copy to clipboard', 'fgallery').'</button>';
	}
	echo '<div id="configurator_wrap">';
		echo sc_params_pane($album);
	echo '</div>';
	if ($id != 0):
	$path = fgallery_search_flash_path($album);
	        echo '<script type="text/javascript">
                var flashvars = {settings: "'.FGALLERY_PATH.'/config.php?gall_id='.$id.'", images: "'.FGALLERY_PATH.'/images.php?gall_id='.$id.'"};
                var params = {bgcolor: "#'.$album['gall_bgcolor'].'", allowFullScreen: "true", wmode: "transparent"};
                swfobject.embedSWF("'.$path.'", "flashcontent", "'.$album['gall_width'].'", "'.$album['gall_height'].'", "10.0.0",false, flashvars, params);
              </script> ';
        echo '<div id="flashcontent">
                <strong>You need to upgrade your Flash Player</strong>
             </div><br />';
	
		echo '<div class="edit_gallery_urls">';
			$title = __('Add images', 'fgallery');
			echo '<a href="'.fgallery_get_addimages_url($id).'" title="'.$title.'" class="thickbox fgallery_action">'.$title.'</a>';
			$title = __('List images', 'fgallery');
			echo '<a href="'.fgallery_get_album_images_url($id).'" title="'.$title.'" class="fgallery_action">'.$title.'</a>';
		echo '</div>';
		endif;
	echo '</div>';
		
	
	echo '</div>';
}

// renders image edit page
function fgallery_edit_image_page($id){
	echo '<div class="wrap">';
	if ( !empty($_POST) && check_admin_referer('fgallery_edit','fgallery_edit_image_field') ) {
		$save = fgallery_edit_image($_POST, $id);
		if ($save) {
			echo '<div id="message" class="updated fade"><p><strong>'.__('Image has been saved', 'fgallery').'</strong></p></div>';
		} else {
			echo '<div id="message" class="error"><p><strong>'.__('Image name cannot be empty', 'fgallery').'</strong></p></div>';
		}
	}
	$image = fgallery_get_image($id);
	if ($image['img_vs_folder'] == 1) {
		echo '<h2>'.__('Edit folder', 'fgallery').' "'.$image['img_caption'].'"</h2>';
	} elseif ($image['img_vs_folder'] == 0) {
		echo '<h2>'.__('Edit image', 'fgallery').' "#'.$image['img_id'].' '.$image['img_caption'].'"</h2>';
	}
	echo fgallery_render_edit_image_form($image);
	if ($image['img_vs_folder'] == 0) {
		echo '<a href="'.get_option('siteurl').'/'.$image['img_path'].'" title="'.__('Full view','fgallery').'" target="_blank">
				<img src="'.THUMB_PATH.$image['img_path'].'" alt="'.$image['img_caption'].'"/>
			 </a>';
	}
	echo '</div>';
}

// renders images index page
function fgallery_images_page() {
global $wpdb;
	if (isset($_GET['action'])) {
		$action = $_GET['action'];
	}
	switch ($action) {
		case 'edit':
			if (isset($_GET['id']) && is_numeric($_GET['id'])) {
				$id = $_GET['id'];
				fgallery_edit_image_page($id);
			}
		break;
		case 'edit_album':
			fgallery_edit_album_image($_POST);
			die();
		break;
		case 'delete':
			if (isset($_GET['id']) && is_numeric($_GET['id'])) {
				$id = $_GET['id'];
				fgallery_delete_image($id);
			}
		break;
		case 'remove':
			if (isset($_GET['id']) && isset($_GET['gall_id']) && is_numeric($_GET['id']) && is_numeric($_GET['gall_id'])) {
				$id = $_GET['id'];
				$gall_id = $_GET['gall_id'];
				$wpdb->query("UPDATE ".IMAGES_TO_ALBUMS_TABLE." SET `gall_folder` = 0 WHERE `gall_folder` =".$id);
				$wpdb->query("DELETE FROM ". IMAGES_TO_ALBUMS_TABLE. " WHERE img_id =".$id." AND gall_id = ".$gall_id);
			}
		break;
		case 'cover':
			if (isset($_GET['id']) && isset($_GET['gall_id']) && is_numeric($_GET['id']) && is_numeric($_GET['gall_id'])) {
				$id = $_GET['id'];
				$gall_id = $_GET['gall_id'];
				$wpdb->update(ALBUMS_TABLE, array('gall_cover'=>$id), array('gall_id' => $gall_id));
			}
		break;
		case 'sort':
			if (isset($_GET['id']) && isset($_GET['gall_id']) && is_numeric($_GET['id']) && is_numeric($_GET['gall_id'])) {
				$id = $_GET['id'];
				$gall_id = $_GET['gall_id'];
				$order = $_GET['order'];
				$wpdb->update(IMAGES_TO_ALBUMS_TABLE, array('img_order' => $order), array('img_id' => $id, 'gall_id' => $gall_id));
			}
			break;
		default:
		// -- pagination ------------
		if (!empty($_POST) && wp_verify_nonce($_POST['screenoptionnonce'], 'screen-options-nonce')) {
			update_option( $_POST['wp_screen_options']['option'],  $_POST['wp_screen_options']['value']);
		}
		$pagenum = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 0;
        $sort_by = isset($_GET['sort']) ? absint($_GET['sort']) : 3;
		$folder = isset($_GET['folder']) ? absint($_GET['folder']) : 0;
		if ($folder) {
			$folder_name = fgallery_get_folder_name($folder);
		}
		if ( empty($pagenum) )
			$pagenum = 1;
		if ( empty( $per_page ) || $per_page < 1 )
			$per_page = get_option('1_flash_gallery_page_fgallery_images_per_page',25);
                        apply_filters('1_flash_gallery_page_fgallery_images_per_page', $per_page);
		$num_pages = ceil(fgallery_images_count($folder) / $per_page);
		$page_links = paginate_links( array(
			'base' => add_query_arg( 'paged', '%#%' ),
			'format' => '',
			'prev_text' => __('&laquo;'),
			'next_text' => __('&raquo;'),
			'total' => $num_pages,
			'current' => $pagenum
		));
		// ---------------------------	
			$items = fgallery_get_images($pagenum,$per_page, $folder, $sort_by); ?>
			<div class="wrap">
			<?php if (!$folder){?>
			<h2><?php _e('Images List', 'fgallery'); ?></h2>
			<?php }else{ ?>
			<h2><a href="<?php echo fgallery_images_url();?>"><?php _e('Images', 'fgallery');?> </a> <?php echo '/ '.__('Folder','fgallery').' "'.$folder_name.'"'?> </h2>
			<?php }?>
			<div class="tablenav">
                        <form action="" method="get" class="form_left">
                        <label for="images_sortby"><?php _e('Sort by:','fgallery')?></label>
                            <select name="sort" id="images_sortby">
                                <option value="0" <?php if ($_GET['sort'] == 0) echo 'selected="selected"'?>><?php _e('Title ASC','fgallery');?> </option>
                                <option value="1" <?php if ($_GET['sort'] == 1) echo 'selected="selected"'?>><?php _e('Title DESC','fgallery');?> </option>
                                <option value="2" <?php if ($_GET['sort'] == 2) echo 'selected="selected"'?>><?php _e('Date ASC','fgallery');?> </option>
                                <option value="3" <?php if ($_GET['sort'] == 3) echo 'selected="selected"'?>><?php _e('Date DESC','fgallery');?> </option>
                                <option value="4" <?php if ($_GET['sort'] == 4) echo 'selected="selected"'?>><?php _e('Size ASC','fgallery');?> </option>
                                <option value="5" <?php if ($_GET['sort'] == 5) echo 'selected="selected"'?>><?php _e('Size DESC','fgallery');?> </option>
                            </select>
                            <input type="hidden" name="page" value="fgallery_images" />
                            <input type="hidden" name="paged" value="<?php echo $pagenum; ?>" />
							<div class="clear"></div>
                        </form>
                            
			<?php if ( $page_links ) { ?>
				<div class="tablenav-pages">
				<?php $count_posts = fgallery_images_count($folder);
					  $page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>%s',
										number_format_i18n( ( $pagenum - 1 ) * $per_page + 1 ),
										number_format_i18n( min( $pagenum * $per_page, $count_posts ) ),
										number_format_i18n( $count_posts ),
										$page_links
										);
					echo $page_links_text; ?>
				</div>
			<?php } ?>
			
			<?php if(!$folder):?><a href="<?php echo fgallery_create_folder_url(1,0) ?>" class="fgallery_action thickbox" ><?php _e('Create new folder','fgallery')?></a> <?php endif;?>
			<a href="<?php echo fgallery_get_upload_url() ?>" class="fgallery_action" ><?php _e('Upload photo','fgallery')?></a>
			</div>
			<form action="<?php echo fgallery_get_massedit_url();?>" method="post" id="massedit_form">
				<?php wp_nonce_field('fgallery_massedit','fgallery_massedit_field'); ?>

				<?php if (fgallery_access_level() >=5) :?>
				<select name="image_action" id="image_action">
				<optgroup label="<?php _e('Actions', 'fgallery')?>">
					<option value="-" selected="selected"><?php _e('Choose action', 'fgallery')?></option>
					<option value="-2"><?php _e('Delete selected', 'fgallery')?></option>
					<option value="-1"><?php _e('Make new gallery from selected', 'fgallery')?></option>
				</optgroup>
					<?php $folders = fgallery_get_folders();
						if (!empty($folders)):?>
					<optgroup label="<?php _e('Move selected to', 'fgallery')?>">
						<option value="0"><?php _e('Root folder', 'fgallery')?></option>
					  <?php foreach ($folders as $item): ?>
								<option value="<?php echo $item['img_id']?>"><?php echo $item['img_caption']?></option>
					  <?php endforeach;?>
					</optgroup>  
					  <?php endif;
					?>
				<?php $albums = fgallery_get_albums(1, 9999999, 4);
						if (!empty($albums)):?>
					<optgroup label="<?php _e('Add selected to', 'fgallery')?>">
					  <?php foreach ($albums as $item): ?>
							<option value="gall_<?php echo $item['gall_id']?>"><?php echo $item['gall_name']?></option>
					  <?php endforeach;?>
					</optgroup>  
					  <?php endif;
					?>	
				</select>
				<input type="submit" value="<?php _e('Go', 'fgallery')?>" />
			   <?php endif;?>
			<?php 
			echo fgallery_render_images_table($items);
			?>
			  </form>
			<div class="tablenav">
				<?php
				if ( $page_links ) { 
					echo "<div class='tablenav-pages'>$page_links_text</div>";
				} ?>
			</div>
                       
			</div> <?php 
		break;
	}
	
	return true;
}

/*
*   Begin gallery settings functions
*/

// returns default gallery settings if there is no user customize
function fgallery_default_album_settings() {
    $settings = array();
        $settings['sc_slideshow__autostart'] = 0;
        $settings['sc_slideshow__enable'] = 0;
        $settings['sc_slideshow__delay'] = '2';
        $settings['sc_slideshow__stopByClick'] = 0;
        $settings['sc_slideshow__music'] = '';
        $settings['sc_slideshow__musicPath'] = '';
        $settings['slide_source'] = 'local';
		
		$settings['sc_preview__width'] = 240;
		$settings['sc_preview__type'] = 'triangle';
		$settings['sc_preview__height'] = 120;
		$settings['sc_preview__alpha'] = 0.7;
		$settings['sc_preview__status'] = 'fill';
		$settings['sc_preview__description'] = 0;
		$settings['sc_preview__descriptionSize'] = 14;
		$settings['sc_preview__countByWidth'] = 4;
		$settings['sc_preview__countByHeight'] = 3;
		$settings['sc_preview__scrollingDirection'] = 'top';
		$settings['sc_preview__cornerRadius'] = 15;
		$settings['sc_preview__transitionEffect'] = 'alpha';
		$settings['sc_preview__transitionDuration'] = 2;
		$settings['sc_preview__shadow'] = 0;
		$settings['sc_preview__border'] = 0;
		$settings['sc_preview__borderColor'] = 'ffffff';
		$settings['sc_preview__shadowColor'] = 'ffffff';
		$settings['sc_preview__shadowAlpha'] = 0.7;
		$settings['sc_preview__shadowBlur'] = 4;
		$settings['sc_preview__shadowDistance'] = 2;
		$settings['sc_preview__borderWidth'] = 2;
		$settings['sc_preview__distanceFromScroller'] = 1;
		$settings['sc_preview__selectTint'] = 0;
		$settings['sc_preview__scrollingSpeed'] = 1;
		$settings['sc_preview__scatter'] = 10;
		$settings['sc_preview__mouseClick'] = 0;
		$settings['sc_preview__font'] = 'Arial';
		$settings['sc_preview__backgroundColor'] = 'ffffff';
		$settings['sc_preview__isURL'] = 0;
		$settings['sc_preview__rotation'] = 25;
		$settings['sc_preview__scaleEffect'] = 'spin';
		$settings['sc_preview__reflection'] = 1;
		$settings['sc_preview__reflectionAlpha'] = 0.7;
		$settings['sc_preview__reflectionDistance'] = 8;
		$settings['sc_preview__reflectionGradientColorStart'] = 'ffffff';
		$settings['sc_preview__reflectionGradientColorFinish'] = '000000';
		
		$settings['sc_background__type'] = 'color';
		$settings['sc_background__src'] = '';
		$settings['sc_background__alpha'] = 0.7;
		$settings['sc_background__color'] = 'ffffff';
		
		$settings['sc_navigation__enable'] = 0;
		$settings['sc_navigation__align'] = 'top';
		$settings['sc_navigation__visible'] = 'onHover';
		$settings['sc_navigation__name'] = 'Navigation';
		$settings['sc_navigation__albumIcon'] = 0;
		$settings['sc_navigation__position'] = 'stage';
		
		$settings['sc_controls__fullscreen'] = 0;
		
		$settings['sc_screen__theme'] = 'black';
		$settings['sc_screen__fog'] = 0;
		$settings['sc_screen__fogWidth'] = 10;
		$settings['sc_screen__mainPreloader'] = 0;
		$settings['sc_screen__navigationsButton'] = 0;
		$settings['sc_screen__previewPreloader'] = 0;
	
        $settings['sc_scroller__distanceFromBorder'] = '30';
        $settings['sc_scroller__visible'] = 0;
        $settings['sc_scroller__scrollBy'] = 'mouse';
        $settings['sc_scroller__speed'] = 0.5;
        $settings['sc_scroller__cornerRadius'] = 10;
        $settings['sc_scroller__lineStyle'] = 'curve';
        $settings['sc_scroller__hideButtons'] = 0;
        $settings['sc_scroller__itemDistance'] = 5;
        $settings['sc_scroller__itemIndent'] = 5;
        $settings['sc_scroller__borderWidth'] = 2;
        $settings['sc_scroller__width'] = 450;
        $settings['sc_scroller__height'] = 120;
        $settings['sc_scroller__borderColor'] = 'ffffff';
        $settings['sc_scroller__align'] = 'top';
        $settings['sc_scroller__enable'] = 0;
        $settings['sc_scroller__size'] = 70;
        $settings['sc_scroller__color'] = 'abcbdf';
        $settings['sc_scroller__alpha'] = '0.5';
        $settings['sc_scroller__direction'] = 'horizontal';
		$settings['sc_scroller__useImagesInItem'] = 0;
		
		$settings['sc_scrollerItem__width'] = 80;
		$settings['sc_scrollerItem__height'] = 40;
		$settings['sc_scrollerItem__cornerRadius'] = 5;
		$settings['sc_scrollerItem__alpha'] = 0.5;
		$settings['sc_scrollerItem__shadow'] = 0;
		$settings['sc_scrollerItem__borderColor'] = 'ffffff';
		$settings['sc_scrollerItem__color'] = 'ffffff';
		$settings['sc_scrollerItem__borderColorOnHover'] = 'ffffff';
		$settings['sc_scrollerItem__borderColorOnClick'] = 'ffffff';
		$settings['sc_scrollerItem__shadowColor'] = 'ffffff';
		$settings['sc_scrollerItem__shadowAlpha'] = 0.7;
		$settings['sc_scrollerItem__shadowBlur'] = 4;
		$settings['sc_scrollerItem__shadowDistance'] = 4;
		$settings['sc_scrollerItem__borderWidth'] = 2;
		$settings['sc_scrollerItem__gradientStart'] = '000000';
		$settings['sc_scrollerItem__gradientEnd'] = 'ffffff';
		$settings['sc_scrollerItem__textColor'] = '000000';
		$settings['sc_scrollerItem__fontSize'] = 13;
		
        $settings['sc_image__imageAsLink'] = 0;
        $settings['sc_image__width'] = 80;
        $settings['sc_image__height'] = 40;
        $settings['sc_image__status'] = 'fit';
        $settings['sc_image__descriptionSize'] = 14;
        $settings['sc_image__description'] = 0;
        $settings['sc_image__stageBlackout'] = 0;
        $settings['sc_image__paginates'] = 0;
        $settings['sc_image__isURL'] = 0;
        $settings['sc_image__transitionDuration'] = '1';
        $settings['sc_image__scaleMode'] = 'fit';
        $settings['sc_image__transitionEffect'] = 'alpha';
        $settings['sc_image__cornerRadius'] = 2;
        $settings['sc_image__dimmingBackground'] = 0;
        $settings['sc_image__fullscreen'] = 0;
        $settings['sc_image__buttonsAlpha'] = 1;
		
		$settings['sc_caption__align'] = 'bottom';
		$settings['sc_caption__enable'] = 0;
		$settings['sc_caption__visible'] = 'onHover';
		$settings['sc_caption__fontSize'] = '14';
		$settings['sc_caption__fontColor'] = 'ffffff';
		$settings['sc_caption__textColor'] = 'ffffff';
        $settings['sc_caption__backgroundColor'] = '000000';
        $settings['sc_caption__backgroundAlpha'] = '0.7';
		
		$settings['sc_main__flipWidth'] = '450';
		$settings['sc_main__flipHeight'] = '380';
		$settings['sc_main__backgroundColor'] = 'ffffff';
		$settings['sc_main__backgroundImage'] = '';
		$settings['sc_main__backgroundImagePlacement'] = 'fit';
		$settings['sc_main__alwaysOpened'] = '0';
		$settings['sc_main__handOverCorner'] = '0';
		$settings['sc_main__dropShadowEnabled'] = '0';
		$settings['sc_main__dropShadowHideWhenFlipping'] = '0';
		$settings['sc_main__shadowDepth'] = '0';
		$settings['sc_main__flipSound'] = '0';
		$settings['sc_main__navigationBar'] = '0';
		
		$settings['sc_pages__pageAlignContent'] = 'fit';
		$settings['sc_pages__pageBackgroundColor'] = 'ffffff';
		$settings['sc_pages__pageBackgroundImage'] = '';
		$settings['sc_pages__pageFrame'] = '15';
		$settings['sc_pages__pageFrameColor'] = 'ffffff';
		$settings['sc_pages__pageFrameAlpha'] = '0.5';
		$settings['sc_pages__coverFrame'] = '0';
		$settings['sc_pages__coverFrameColor'] = 'ffffff';
		$settings['sc_pages__coverFrameAlpha'] = '0.5';
		
		$settings['sc_menu__albumListBtn'] = '0';
		$settings['sc_menu__exactFitBtn'] = '0';
		$settings['sc_menu__zoomInBtn'] = '0';
		$settings['sc_menu__zoomOutBtn'] = '0';
		$settings['sc_menu__fitToStageBtn'] = '0';
		$settings['sc_menu__firstBtn'] = '0';
		$settings['sc_menu__previousBtn'] = '0';
		$settings['sc_menu__navigationString'] = '0';
		$settings['sc_menu__nextBtn'] = '0';
		$settings['sc_menu__lastBtn'] = '0';
		$settings['sc_menu__soundOnBtn'] = '0';
		$settings['sc_menu__sounOffBtn'] = '0';
		$settings['sc_menu__fullscreenBtn'] = '0';
		$settings['sc_menu__exitFullscreenBtn'] = '0';
		
        $settings['sc_flickr__searchBy'] = 'keyword';
        $settings['sc_flickr__userID'] = '';
        $settings['sc_flickr__keyword'] = '';
		$settings['sc_flickr__max'] = '20';
		$settings['sc_flickr__albumID'] = '';
		
        $settings['sc_picasa__searchBy'] = 'keyword';
        $settings['sc_picasa__userName'] = '';
        $settings['sc_picasa__keyword'] = '';
        $settings['sc_picasa__max'] = '20';
        $settings['sc_picasa__albumID'] = '';
        $settings['sc_picasa__albumName'] = '';
        
		$settings['sc_photobucket__searchString'] = '';
        $settings['sc_photobucket__max'] = '20';
		
    return $settings;
}

// returns gallery settings
function fgallery_get_album_settings($gall_id) {
    global $wpdb;
        $settings = $wpdb->get_var("SELECT value FROM ".ALBUMS_SETTINGS_TABLE." WHERE gall_id = ".$gall_id);
        if (empty($settings)) {
            return fgallery_default_album_settings();
        } else {
            return unserialize($settings);
        }
}

// saves the albums settings to db
function fgallery_save_album_settings($gall_id, $data) {
global $wpdb;
	$res = $wpdb->get_row("SELECT * FROM ".ALBUMS_SETTINGS_TABLE." WHERE gall_id = ".$gall_id, ARRAY_A);
	if (empty($res)) {
		$wpdb->insert(ALBUMS_SETTINGS_TABLE, array('gall_id'=>$gall_id, 'value'=>serialize($data)));
	} else {
		$wpdb->update(ALBUMS_SETTINGS_TABLE, array('value'=>serialize($data)), array('gall_id'=>$gall_id));
	}
	return true;
}

// save changes to gallery into db
function fgallery_edit_album($data,$id) {
    global $wpdb;
	global $current_user;
    $name = htmlentities($data['fgallery_name'], ENT_NOQUOTES, "UTF-8");
    $desc = htmlentities($data['fgallery_description'], ENT_NOQUOTES, "UTF-8");
	$published = $data['fgallery_status'];
	$width = (int)$data['gall_width'];
	if ($width == 0) $width = 450;
	$height = (int)$data['gall_height'];
	if ($height == 0) $height = 385;
	$bgcolor = str_replace('#','',$data['gall_bgcolor']);
	$type = $data['gall_type'];
	if ($id == 0) {
		$user_id = $current_user->ID;//get_current_user_id();
		$max = $wpdb->get_var("SELECT MAX(`gall_order`) FROM ".ALBUMS_TABLE);
		$wpdb->insert(ALBUMS_TABLE, array('gall_name' => $name, 'gall_description' => $desc, 'gall_createddate' => date("Y-m-d H:i:s"), 'gall_createdby' => $user_id, 'gall_published' => $published, 'gall_width'=>$width, 'gall_height'=> $height, 'gall_bgcolor'=> $bgcolor, 'gall_type'=> $type, 'gall_order' => $max + 1));
		return $wpdb->insert_id;
	}
	if ($name != '') {
		$wpdb->update(ALBUMS_TABLE, array('gall_name' => $name, 'gall_description' => $desc, 'gall_published' => $published, 'gall_width'=>$width, 'gall_height'=> $height, 'gall_bgcolor'=> $bgcolor, 'gall_type'=> $type), array('gall_id' => $id));
		return $id;
    } else {
        return 0;
    } 
}

// save changes to image into db
function fgallery_edit_image($data,$id) {
	global $wpdb;
	$name = htmlentities($data['fgallery_image_caption'], ENT_NOQUOTES, "UTF-8");
    $desc = htmlentities($data['fgallery_image_description'], ENT_NOQUOTES, "UTF-8");
	if ($name != '') {
		$wpdb->update(IMAGES_TABLE, array('img_caption' => $name, 'img_description' => $desc), array('img_id' => $id));
		return 1;
    } else {
        return 0;
    } 
}

function fgallery_edit_album_image($data) {
	global $wpdb;
	if (isset($data['img_id']) && is_numeric($data['img_id'])) {
		$id = $data['img_id'];
	} else {
		return 0;
	}
	if (isset($data['gall_id']) && is_numeric($data['gall_id'])) {
		$gall_id = $data['gall_id'];
	} else {
		return 0;
	}
	if (!wp_verify_nonce($_POST['nonce'], 'fgallery_edit')) {
		return 0;
	}
	$name = htmlentities($data['img_caption'], ENT_NOQUOTES, "UTF-8");
    $desc = htmlentities($data['img_description'], ENT_NOQUOTES, "UTF-8");
	$url = $data['img_url'];
	if ($name != '') {
		$wpdb->update(IMAGES_TABLE, array('img_caption' => $name, 'img_description' => $desc), array('img_id' => $id));
		$wpdb->update(IMAGES_TO_ALBUMS_TABLE, array('img_url' => $url), array('img_id' => $id, 'gall_id' => $gall_id));
		return 1;
    } else {
        return 0;
    } 
}

// deletes image from db and deletes file
function fgallery_delete_image($id) {
	global $wpdb;
        if (fgallery_access_level()>=5) {
		
			$wpdb->query("DELETE FROM ".IMAGES_TO_ALBUMS_TABLE." WHERE `img_id` = ".$id);
			$image = fgallery_get_image($id);
			if ($image['img_vs_folder'] == 1){
					$wpdb->query("UPDATE ".IMAGES_TABLE." SET `img_parent` = 0 WHERE `img_parent` =".$id);
					$wpdb->query("DELETE FROM ".IMAGES_TABLE." WHERE `img_id` = ".$id);
			} elseif ($image['img_vs_folder'] == 0) {
				// we shouldn't delete wp media files so .....
				if (strpos($image['img_path'],'fgallery') === false) {
					$wpdb->query("DELETE FROM ".IMAGES_TABLE." WHERE `img_id` = ".$id);
				} else {
					unlink(ABSPATH.$image['img_path']);
					$wpdb->query("DELETE FROM ".IMAGES_TABLE." WHERE `img_id` = ".$id);
				}
			}
		
	return true;
        } else {
            return false;
        }
}

// deletes gallery from db (does not delete files)
function fgallery_delete_album($id) {
	global $wpdb;
        if (fgallery_access_level()>=8) {
		$wpdb->query("DELETE FROM ".IMAGES_TO_ALBUMS_TABLE." WHERE `gall_id` = ".$id);
		$wpdb->query("DELETE FROM ".ALBUMS_SETTINGS_TABLE." WHERE `gall_id` = ".$id);
		$wpdb->query("DELETE FROM ".ALBUMS_TABLE." WHERE `gall_id` = ".$id);
	return true;
        } else {
            return false;
        }
}

// returns the way of gallery sorting
function fgallery_sort_albums_condition($sort) {
    switch ($sort){
        case 0:
            return 'gall_name ASC';
            break;
        case 1:
            return 'gall_name DESC';
            break;
        case 2:
            return 'gall_createddate ASC';
            break;
        case 3:
            return 'gall_createddate DESC';
            break;
		case 4: 
			return 'gall_order ASC';
            break;
        default:
            return 'gall_order ASC';
            break;
    }
}

// returns the way of image sorting
function fgallery_sort_images_condition($sort) {
    switch ($sort){
        case 0:
            return 'img_caption ASC';
            break;
        case 1:
            return 'img_caption DESC';
            break;
        case 2:
            return 'img_date ASC';
            break;
        case 3:
            return 'img_date DESC';
            break;
        case 4:
            return 'img_size ASC';
            break;
        case 5:
            return 'img_size DESC';
            break;
    }
}

// returns all folders
function fgallery_get_folders(){
	global $wpdb;
		$folders = $wpdb->get_results("SELECT * FROM " . IMAGES_TABLE." WHERE `img_vs_folder` = 1 ORDER BY `img_caption`", 'ARRAY_A');
	return $folders;
}

// Returns all albums 
function fgallery_get_albums($pagenum, $per_page, $sort) {
	global $wpdb;
        $cond = fgallery_sort_albums_condition($sort);
		$albums = $wpdb->get_results("SELECT * FROM " . ALBUMS_TABLE." ORDER BY ".$cond." LIMIT ".($pagenum-1)*$per_page.",".$pagenum*$per_page, 'ARRAY_A');
	return $albums;
}

// counts the number of galleries
function fgallery_albums_count() {
	global $wpdb;
		$count = $wpdb->get_var("SELECT COUNT(*) FROM " .ALBUMS_TABLE);
	return $count;
}

function fgallery_count_gallery_album_images($folder) {
	global $wpdb;
		$count = $wpdb->get_var("SELECT COUNT(*) FROM ".IMAGES_TO_ALBUMS_TABLE." WHERE `gall_folder` = ".$folder);
	return $count;
}

// returns all the images
function fgallery_get_images($pagenum, $per_page, $parent = 0, $sort = 3) {
	global $wpdb;
        $cond = fgallery_sort_images_condition($sort);
		$images = $wpdb->get_results("SELECT * FROM ". IMAGES_TABLE. " WHERE `img_parent` = ".$parent." AND `img_vs_folder` IN (0,1) ORDER BY `img_vs_folder` DESC, ".$cond." LIMIT ".($pagenum-1)*$per_page.",".$pagenum*$per_page, 'ARRAY_A');
	return $images;
}

// counts the number of images in the folder (0 - root folder or not in any folder)
function fgallery_images_count($folder) {
	global $wpdb;
		$count = $wpdb->get_var("SELECT COUNT(*) FROM " .IMAGES_TABLE." WHERE `img_parent` = ".$folder." AND `img_vs_folder` IN (0,1)");
	return $count;
}

// returns defined gallery
function fgallery_get_album($id) {
	global $wpdb;
		$album = $wpdb->get_row("SELECT * FROM ".ALBUMS_TABLE." WHERE gall_id = ".$id, ARRAY_A);
	return $album;
}

// returns defined image
function fgallery_get_image($id) {
	global $wpdb;
		$image = $wpdb->get_row("SELECT * FROM ".IMAGES_TABLE." WHERE img_id = ".$id, ARRAY_A);
	return $image;
}

// returns the images from defined gallery
function fgallery_get_album_images($gall_id, $folder = 0) {
	global $wpdb;
		$items = $wpdb->get_results("SELECT a.*, b.* FROM " . IMAGES_TABLE . " as a LEFT JOIN ". IMAGES_TO_ALBUMS_TABLE." as b ON (a.img_id = b.img_id) WHERE b.gall_id = " .$gall_id." AND b.gall_folder = ".$folder." ORDER BY a.img_vs_folder DESC, b.img_order ASC", 'ARRAY_A');
	return $items;
}

// returns the galleries that have the defined image in them
function fgallery_image_get_albums($id) {
	global $wpdb;
		$albums = $wpdb->get_results("SELECT a.gall_name, a.gall_id FROM ". ALBUMS_TABLE ." as a LEFT JOIN ". IMAGES_TO_ALBUMS_TABLE. " as b ON (a.gall_id = b.gall_id) WHERE b.img_id =".$id, ARRAY_A);
	return $albums;
}

// returns the folder name
function fgallery_get_folder_name($id) {
	global $wpdb;
		$name = $wpdb->get_var("SELECT `img_caption` FROM ".IMAGES_TABLE." WHERE `img_id` =".$id);
	return $name;
}

function fgallery_get_settings_param($param, $settings) {
$name = (string)$param['element_name'];
	if ($param['values'] == 'true') {
		return $settings[$name] ? 'true' : 'false';
	} else {
		return $settings[$name];
	}
}

/*
 *  Starting the configurator rendering
 */

function sc_params_pane($album) {
  $gall_id = $album['gall_id'];
  $type = $album['gall_type'];
  $form = '<form method="post" action="'.fgallery_get_settings_url($gall_id).'" id="sc-configurator-form" onSubmit="return fgallery_checkform()"><div>';
  $params_xml = simplexml_load_file(FGALLERY_ABSPATH . '/xml/params_'.$type.'.xml');
  $settings = fgallery_get_album_settings($gall_id);//simplexml_load_file(FGALLERY_PATH.'/config.php?gall_id='.$gall_id);
  // Preparing jQuery UI tabs
  $tab_controls = "<ul>";
	$tab_controls .= '<li><a href="#sc-group-general">' . __('General','fgallery') . '</a></li>';
  foreach ($params_xml->params->group as $g) {
	// if php 4 is running
	if (FGALLERY_PHP4_MODE) {
		$temp = $g->attributes();
		$g = array();
		$g['name'] = $temp['name'];
		$g['title'] = $temp['title'];
	}
	// endif
	 $tab_controls .= '<li><a href="#sc-group-' . $g['name'] . '">' . $g['title'] . '</a></li>';
	}

  $tab_controls .= "</ul>";
  $form .= $tab_controls;
	$form .= fgallery_render_edit_album_form($album, $settings_xml->gallery->source);
  // Form the list of parameters in groups
  // if php 4 is running
  if (FGALLERY_PHP4_MODE) {
	  foreach ($params_xml->params->group as $g) {
		$temp = $g->attributes();
		$form .= '<div id="sc-group-' . $temp['name'] . '" class="sc-skin-params-tab-panel">';
		$zebra = 'even';
		foreach ($g->p as $p) {
		  $p_temp = $p->attributes();
		  if (function_exists($fn = 'sc_controls_' . $p_temp['control'])) {
			$p_temp['element_name'] = 'sc_' . $temp['name'] . '__' . $p_temp['name'];
			$p_temp['default'] = fgallery_get_settings_param($p_temp, $settings);
			$p_temp['zebra'] = $zebra;
			$form .= $fn($p_temp);
			$zebra = $zebra == 'even' ? 'odd' : 'even';
		  }
		}
		$form .= '</div>';
		}
	//end php 4 clause
  } else { 
	// if php 5 is running
	  foreach ($params_xml->params->group as $g) {
		$form .= '<div id="sc-group-' . $g['name'] . '" class="sc-skin-params-tab-panel">';
		$zebra = 'even';
		foreach ($g->p as $p) {
		  if (function_exists($fn = 'sc_controls_' . $p['control'])) {
			$p['element_name'] = 'sc_' . $g['name'] . '__' . $p['name'];
			$p['default'] = fgallery_get_settings_param($p, $settings);
			$p['zebra'] = $zebra;
			$form .= $fn($p);
			$zebra = $zebra == 'even' ? 'odd' : 'even';
		  }
		}
		$form .= '</div>';
		}
	// end php condition
   }
	// "Save" button
  $form .= '<input name="sc_submit"  type="submit" value="'.__('Save').'" /><span id="configurator_message"></span>';
  $form .= wp_nonce_field('fgallery_settings','fgallery_settings_field');
  $form .= '</div></form>';
  return $form;
}

/**
 * Element text
 */
function sc_controls_text($p) {
  return '<div class="form-item ' . $p['zebra'] . '" >'
       . '<label for="' . $p['element_name'] . '">' . $p['title'] . ':</label>'
       . '<input name="' . $p['element_name'] . '" id="' . $p['element_name'] . '" type="text" value="' . (string)$p['default'] .'" size="10" maxlength="255" class="form-text '.$p['class'].'"/>'
       . '</div>';
}

/**
 * Element - checkbox.
 */
function sc_controls_checkbox($p) {
  $checked = (int)$p['default'] ? 'checked="checked"' : '';
  if ($checked == '' && (string)$p['default'] == 'true') $checked = 'checked = "checked"';
  return '<div class="form-item ' . $p['zebra'] . '" >'
       . '<input name="' . $p['element_name'] . '" id="' . $p['element_name'] . '" type="checkbox" ' . $checked . ' class="form-checkbox" value="'.$p['values'].'"/>'
       . '<label for="' . $p['element_name'] . '">' . $p['title'] . '</label>'
       . '</div>';
}

/**
 * Element - select.
 */
function sc_controls_select($p) {
  $options_raw = explode(',', $p['values']);
  $options = '';
  foreach ($options_raw as $o) {
    $selected = (string) $p['default'] == $o ? 'selected="selected"' : '';
    $options .= '<option value="' . $o . '" ' . $selected . '>' . $o . '</option>';
  }
  return '<div class="form-item ' . $p['zebra'] . '" >'
       . '<label for="' . $p['element_name'] . '">' . $p['title'] . ':</label>'
       . '<select name="' . $p['element_name'] . '" id="' . $p['element_name'] . '" class="form-select">' . $options . '</select>'
       . '</div>';
}

/**
 * Element - slider (jQuery UI slider).
 */
function sc_controls_slider($p) {
  $values = explode(':', $p['values']); // format "1..5:1"
  $range = $values[0];
  $range = explode('..', $range);
  $min = $range[0];
  $max = $range[1];

  $step = $values[1];

  // Validate values
  if (!is_numeric($step) || !is_numeric($min) || !is_numeric($max)) {
    return '<div class="form-item ' . $p['zebra'] . '" >Parse error</div>';
  }

  return '<div class="form-item ' . $p['zebra'] . '" >'
       . '<label for="' . $p['element_name'] . '">' . $p['title'] . ':</label>'
       . '<input name="' . $p['element_name'] . '" id="' . $p['element_name'] . '" type="text" value="' . $p['default'] .'" size="3" maxlength="3" readonly="readonly" class="sc-slider-val form-text" min="' . $min . '" max="' . $max . '" step="' . $step .'"/>'
       . '</div>';
}

/**
 * Form element - color picker (Farbtastic).
 */
function sc_controls_color($p) {
  return '<div class="form-item ' . $p['zebra'] . '" >'
       . '<label for="' . $p['element_name'] . '">' . $p['title'] . ':</label>'
       . '<input name="' . $p['element_name'] . '" id="' . $p['element_name'] . '" type="text" value="' . str_replace('0x', '#', $p['default']) .'" size="10" maxlength="7" class="sc-color-val form-text"/>'
       . '</div>';
}



// Renders the list of all albums
function fgallery_render_albums_table($albums){
$output .= '<table class="widefat fixed">
		  <thead>
			<tr>
				<th id="cb" class="manage-column check-column column-cb" scope="col" ><input type="checkbox" name="check_all" class="check_all" /></th>
				<th class="gall_id" scope="col">'.__('ID').'</th>
				<th id="title" scope="col">'.__('Name','fgallery').'</th>
				<th class="gall_cover" scope="col">'.__('Cover','fgallery').'</th>
				<th class="gall_description" scope="col">'.__('Description','fgallery').'</th>
				<th class="gall_attr" scope="col">'.__('Attributes','fgallery').'</th>
				<th class="fgallery_actions" scope="col">'.__('Actions','fgallery').'</th>
			</tr>
		  </thead>';
$output .= '<tfoot>
			<tr>
				<th class="manage-column check-column column-cb" scope="col" ><input type="checkbox" name="check_all" class="check_all" /></th>
				<th class="gall_id" scope="col">'.__('ID').'</th>
				<th scope="col">'.__('Name','fgallery').'</th>
				<th class="gall_cover" scope="col">'.__('Cover','fgallery').'</th>
				<th class="gall_description" scope="col">'.__('Description','fgallery').'</th>
				<th class="gall_attr" scope="col">'.__('Attributes','fgallery').'</th>
				<th class="fgallery_actions" scope="col">'.__('Actions','fgallery').'</th>
			</tr>
		  </tfoot><tbody>';
	if (!empty($albums)) {
		foreach ($albums as $album) {
			$output .= fgallery_render_album_item_row($album);
		}
	} else {
		$output .= '<tr><td colspan="7">'.__('There are no galleries yet. You can create new one using Add gallery menu','fgallery'). '</td></tr>';
	}
	$output .= '</tbody></table>';
	$output .= '<a href="'.fgallery_sort_galleries_url().'" class="fgallery_sort" style="display:none;"></a>';
	return $output;
}

// renders the row in the albums list
function fgallery_render_album_item_row($album) {
	$id = $album['gall_id'];
	$output = '<tr class="fgallery_album" id="album_'.$album['gall_id'].'">';
		$output .= '<th class="check-column"><input type="checkbox" value="'.$album['gall_id'].'" name="gallery[]" /></th>';
		$output .= '<td>'.$id.'<span class="order" style="display:none">'.$album['gall_order'].'</span></td>';
		$output .= '<td><a href="'.fgallery_get_edit_url($id).'">'.$album['gall_name'].'</a></td>';
		$output .= '<td><a href="'.fgallery_get_edit_url($id).'">'.fgallery_get_album_cover($album).'</a></td>';
		$output .= '<td>'.$album['gall_description'].'</td>';
		$output .= '<td>'.fgallery_get_album_attributes($album).'</td>';
		$output .= '<td>'.fgallery_get_album_actions($id).'</td>';
	$output .= '</tr>';
	return $output;
}

function fgallery_render_album_image_form($item, $gall_id, $type) {
	if ($gall_id == '') {
		return '';
	}
	$output .= '<p><label for="fgalleryImageCaption_'.$item['img_id'].'">'.__('Caption:', 'fgallery').'</label>
				<input type="text" id="fgalleryImageCaption_'.$item['img_id'].'" name="fgallery_image_caption" value="'.$item['img_caption'].'" /></p>';
	$output .= '<p><label for="fgalleryImageDescription_'.$item['img_id'].'">'.__('Description:', 'fgallery').'</label>
					<textarea cols="30" rows="2" id="fgalleryImageDescription_'.$item['img_id'].'" name="fgallery_image_description">'.$item['img_description'].'</textarea></p>';
	if (!in_array($type, array(3,4))) {
		$hide = ' style="display:none;"';
	}
		$output .= '<p'.$hide.'><label for="fgalleryImageURL_'.$item['img_id'].'">'.__('URL:', 'fgallery').'</label>
				<input type="text" id="fgalleryImageURL_'.$item['img_id'].'" name="fgallery_image_url" value="'.$item['img_url'].'" /></p>';
	
	$output .= wp_nonce_field('fgallery_edit','fgallery_edit_image_field_'.$item['img_id']);
	$output .= '<input type="hidden" name="gall_id" id="fgalleryImageGall_'.$item['img_id'].'" value="'.$gall_id.'" />';
	$output .= '<a rel="'.$item['img_id'].'" class="save_album_image" href="'.fgallery_get_album_image_edit_url($item['img_id']).'">'.__('Save').'</a>';
	return $output;
}

// renders the list of album images on gallery edit page
function fgallery_render_album_images($items, $gall_id) {
	$title = __('Remove', 'fgallery');
	$title_2 = __('Set as cover', 'fgallery');
	$title_3 = __('Edit','fgallery');
	$album = fgallery_get_album($gall_id);
	$output = '<form method="post" action="'.fgallery_get_massedit_album_url().'">';
	$output.= fgallery_get_listof_gallery_albums($gall_id);
	$output.= '<ul class="fgallery_list" id="'.$gall_id.'">';
	$output.= '<a href="'.fgallery_sort_url().'" style="display:none">Sort url</a>';
	foreach ($items as $item) {
		if ($album['gall_cover'] == $item['img_id']) {
			$cover_class = 'album_cover';
		} else {
			$cover_class = '';
		}
		$output .= '<li id="image_'.$item['img_id'].'" class="'.$cover_class.'"><div class="image_wrap">
							<div class="fgallery_image_info">';
							if ($item['img_vs_folder'] != 2) {
								$output .= '<input type="checkbox" name="images[]" value="'.$item['img_id'].'" />';
							}
				$output .= '</div>
							<div class="fgallery_image">';
				if ($item['img_vs_folder'] == 0){		
						$output .=	'<img src="'.COVER_PATH.$item['img_path'].'" alt="'.$item['img_caption'].'" />';
				}
				$output .= '</div>
							<div class="fgallery_image_info">';
							if ($item['img_vs_folder'] == 2){
								$output .= '<b><a href="'.fgallery_get_album_images_url($gall_id).'&folder='.$item['img_id'].'">'.$item['img_caption'].'</a></b>';
								$output .= '('.fgallery_count_gallery_album_images($item['img_id']).')';
							} else {
								$output .= fgallery_render_album_image_form($item, $gall_id, $album['gall_type']);
							}
				$output .='</div>
							<div class="fgallery_image_actions">
								<a href="'.fgallery_get_image_edit_url($item['img_id']).'" title="'.$title_3.'">'.$title_3.'</a>
								<a href="'.fgallery_remove_image_from_gallery_url($item['img_id'],$gall_id).'" class="image_remove" rel="image_'.$item['img_id'].'" title="'.$title.'">'.$title.'</a>';
								if ($item['img_vs_folder'] != 2) {
									$output .= '<a href="'.fgallery_set_gallery_cover_url($item['img_id'],$gall_id).'" class="image_cover" rel="image_'.$item['img_id'].'" title="'.$title_2.'">'.$title_2.'</a>';
								}
				$output .='</div>
						</div>
					</li>';
	}
	$output .= '</ul>';
	$output .= '</form>';
	return $output;
}

// renders the list of images
function fgallery_render_images_table($images){
$output .= '<table class="widefat fixed">
		  <thead>
			<tr>
				<th id="cb" class="manage-column check-column column-cb" scope="col" ><input type="checkbox" name="check_all" class="check_all" /></th>
				<th class="gall_id" scope="col">'.__('ID').'</th>
				<th id="title" scope="col">'.__('Caption', 'fgallery').'</th>
				<th class="gall_cover" scope="col">'.__('Preview', 'fgallery').'</th>
				<th class="gall_description" scope="col">'.__('Description', 'fgallery').'</th>
				<th class="gall_attr" scope="col">'.__('Attributes', 'fgallery').'</th>
				<th class="fgallery_actions" scope="col">'.__('Actions', 'fgallery').'</th>
			</tr>
		  </thead>';
$output .= '<tfoot>
			<tr>
				<th class="manage-column check-column column-cb" scope="col" ><input type="checkbox" name="check_all" class="check_all" /></th>
				<th class="gall_id" scope="col">'.__('ID').'</th>
				<th scope="col">'.__('Caption', 'fgallery').'</th>
				<th class="gall_cover" scope="col">'.__('Preview', 'fgallery').'</th>
				<th class="gall_description" scope="col">'.__('Description', 'fgallery').'</th>
				<th class="gall_attr" scope="col">'.__('Attributes', 'fgallery').'</th>
				<th class="fgallery_actions" scope="col">'.__('Actions', 'fgallery').'</th>
			</tr>
		  </tfoot><tbody>';
	if (isset($_GET['folder'])) {
		$output .=  '<tr class="droppable" id="folder_0"><td colspan="7"><a class="go_up" href="'.fgallery_images_url().'">'.__('Go up', 'fgallery').'...</a><a class="folder_addimage" href="'.fgallery_get_folder_addimage_url().'"></a></td></tr>';
	}
	if (!empty($images)) {
		foreach ($images as $image) {
			$output .= fgallery_render_image_item_row($image);
		}
	} else {
		$output .= '<tr><td colspan="7">'.__('There are no images yet. You can upload new ones using Upload images menu', 'fgallery'). '</td></tr>';
	}

	$output .= '</tbody></table>';
	return $output;
}

// renders the row in the albums list
function fgallery_render_image_item_row($image) {
	$id = $image['img_id'];
	if ($image['img_vs_folder']){
		$output = '<tr class="fgallery_album droppable" id="folder_'.$id.'">';
	} else {
		$output = '<tr class="fgallery_album draggable" id="image_'.$id.'">';
	}
		$output .= '<th class="check-column"><input type="checkbox" value="'.$id.'" name="image[]" /></th>';
		$output .= '<td>'.$id.'</td>';
		
		if ($image['img_vs_folder']) {
			$output .= '<td><a href="'.fgallery_get_folder_url($id).'">'.$image['img_caption'].'</a></td>';
			$output .= '<td><a href="'.fgallery_get_folder_url($id).'"><img src="'.FGALLERY_PATH.'/images/folder.png" alt="'.$image['img_caption'].'"/></a></td>';
			$output .= '<td>'.html_entity_decode($image['img_description']).'</td>';
			$output .= '<td class="folder_attr">'.fgallery_get_folder_attributes($image).'</td>';
			$output .= '<td>'.fgallery_get_folder_actions($id).'</td>';
		} else {
			$output .= '<td><a href="'.fgallery_get_image_edit_url($id).'">'.$image['img_caption'].'</a></td>';
			$output .= '<td><a href="'.fgallery_get_image_edit_url($id).'"><img src="'.COVER_PATH.$image['img_path'].'" alt="'.$image['img_caption'].'"/></a></td>';
			$output .= '<td>'.html_entity_decode($image['img_description']).'</td>';
			$output .= '<td>'.fgallery_get_image_attributes($image).'</td>';
			$output .= '<td>'.fgallery_get_image_actions($id).'</td>';
		}
	$output .= '</tr>';
	return $output;
}

function fgallery_get_listof_gallery_albums($id) {
	global $wpdb;
	$albums =$wpdb->get_results("SELECT a.img_caption, a.img_id FROM ".IMAGES_TABLE." as a LEFT JOIN ".IMAGES_TO_ALBUMS_TABLE." as b ON (a.img_id = b.img_id) WHERE a.img_vs_folder = 2 AND b.gall_id = ".$id, ARRAY_A);
	if (count($albums) > 0) {
		$output = '<select name="album_id" id="album_id_images">';
			$output .= '<optgroup label="'.__('Choose album', 'fgallery').'">';
		foreach ($albums as $album) {
			$output .= '<option value="'.$album['img_id'].'">'.$album['img_caption'].'</option>';
		}
		$output .= '</optgroup>';
		$output .= '<optgroup label="'.__('Actions').'">';
			$output .= '<option value="-1">'.__('Remove selected').'</option>';
		$output .= '</optgroup>';
		$output .= '</select>';
		$output .= '<input type="hidden" value="'.$id.'" name="gall_id" />';
		$output .= '<input type="submit" value="'.__('Go', 'fgallery').'" />';
		
	}
	$output .= '<br clear="all" />';
	return $output;
}

function fgallery_create_thumb_url($width) {
	if (is_numeric($width)) {
		return WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/image.php/cover.jpg?width='.$width.'&amp;image='.EXTRA_DIR;
	} else {
		return COVER_PATH;
	}
}

// returns the wrapped in img tag album cover
function fgallery_get_album_cover($album, $width ='') {
global $wpdb;
$gall_id = $album['gall_id'];
	if ($album['gall_cover'] != 0){
		$cover_id = $album['gall_cover'];
		$cover = $wpdb->get_row("SELECT `img_path`, `img_caption` FROM " . IMAGES_TABLE . " WHERE `img_id` = " .$cover_id." LIMIT 1", 'ARRAY_A');
	} else {
		$cover = $wpdb->get_row("SELECT a.`img_path`, a.`img_caption` FROM " . IMAGES_TABLE . " as a LEFT JOIN ". IMAGES_TO_ALBUMS_TABLE ." as b ON (a.img_id = b.img_id) WHERE b.gall_id = " .$gall_id." ORDER BY b.img_order ASC LIMIT 1", 'ARRAY_A');
	}
	if ($cover['img_path']!=''){
		$gall_cover = '<img src="'.fgallery_create_thumb_url($width).$cover['img_path'].'" alt="'.$cover['img_caption'].'" />';
	}
	return $gall_cover;
}

//returns rendered album attributes (image quantity, total size, publish date and etc.)
function fgallery_get_album_attributes($album, $extra = true) {
global $wpdb;
$id = $album['gall_id'];
	$attr = $wpdb->get_row("SELECT COUNT(a.img_id) as 'quantity', SUM(a.img_size) as 'total_size' FROM " . IMAGES_TABLE . " as a LEFT JOIN ". IMAGES_TO_ALBUMS_TABLE ." as b ON (a.img_id = b.img_id) WHERE b.gall_id = " .$id." AND a.img_vs_folder <> 2", 'ARRAY_A');
	$output = __('Number of photos:', 'fgallery').$attr['quantity']. '<br />';
	$output .= __('Total size:','fgallery').formatBytes($attr['total_size']). '<br />';
	if ($extra) {
		if ($album['gall_published']) {
			$status = __('Published');
		} else {
			$status = __('Draft');
		}
		$output .= __('Gallery status:','fgallery').$status. '<br />';
		$output .= date("d F Y",strtotime($album['gall_createddate'])). '<br />';
	}
	return $output;
}

//returns rendered album attributes
function fgallery_get_image_attributes($image){
	$id = $image['img_id'];
	$albums = fgallery_image_get_albums($id);
	$albums_name = '';
	if (count($albums)>0){
		foreach ($albums as $album) {
			$albums_name .= $album['gall_name'].',';
		}
		$albums_name = substr($albums_name, 0, -1);
	} else {
		$albums_name = __('Image is not in any gallery', 'fgallery');
	}
	$output = __('Galleries:', 'fgallery').$albums_name.'<br />';
	$output.= __('Image type:', 'fgallery').$image['img_type'].'<br />';
	$output.= __('Image size:', 'fgallery').formatBytes($image['img_size']).'<br />';
	$output.= __('Upload date:', 'fgallery').date("d F Y",strtotime($image['img_date']));
	return $output;
}

//returns rendered folder attributes 
function fgallery_get_folder_attributes($image){
	global $wpdb;
	if ($image['img_vs_folder']) {
		$id = $image['img_id'];
		$attr = $wpdb->get_row("SELECT COUNT(a.img_id) as 'quantity', SUM(a.img_size) as 'total_size' FROM " . IMAGES_TABLE . " as a  WHERE a.img_parent = " .$id, 'ARRAY_A');
			$output = __('Images:', 'fgallery').$attr['quantity'].'<br />';
			$output.= __('Images size:', 'fgallery').formatBytes($attr['total_size']).'<br />';
			$output.= __('Create date:', 'fgallery').date("d F Y",strtotime($image['img_date']));
		}
	return $output;
}

//renders the album actions
function fgallery_get_album_actions($id) {
	$title_1 = __('Add images','fgallery');
	$title_2 = __('Edit gallery','fgallery');
	$title_3 = __('Delete gallery','fgallery');
	$title_4 = __('Edit images', 'fgallery');
	$output = '<a href="'.fgallery_get_addimages_url($id).'" title="'.$title_1.'" class="fgallery_action thickbox">'.$title_1.'</a>';
	$output .= '<a href="'.fgallery_get_edit_url($id).'" title="'.$title_2.'" class="fgallery_action">'.$title_2.'</a>';
	$output .= '<a href="'.fgallery_get_album_images_url($id).'" title="'.$title_4.'" class="fgallery_action">'.$title_4.'</a>';
	if (fgallery_access_level()>= 8){
            $output .= '<a href="'.fgallery_get_delete_url($id).'" rel="album_'.$id.'" title="'.$title_3.'" class="fgallery_action delete">'.$title_3.'</a>';
        }
        return $output;
}

//renders the folder actions
function fgallery_get_folder_actions($id) {
	$title_2 = __('Change name', 'fgallery');
	$title_3 = __('Delete folder', 'fgallery');
	$output .= '<a href="'.fgallery_get_image_edit_url($id).'" title="'.$title_2.'" class="fgallery_action">'.$title_2.'</a>';
	$output .= '<a href="'.fgallery_get_folder_addimage_url().'" class="folder_addimage"></a>';
	$output .= '<a href="#" class="fgallery_action create_from_folder" rel="'.$id.'">'.__('Create gallery', 'fgallery').'</a>';
	if (fgallery_access_level()>= 5){
            $output .= '<a href="'.fgallery_get_image_delete_url($id).'" rel="folder_'.$id.'" title="'.$title_3.'" class="fgallery_action delete">'.$title_3.'</a>';
        }
        return $output;
}

//renders the image actions
function fgallery_get_image_actions($id) {
	$title_2 = __('Edit image', 'fgallery');
	$title_3 = __('Delete image', 'fgallery');
	$output = '<a href="'.fgallery_get_image_edit_url($id).'" title="'.$title_2.'" class="fgallery_action">'.$title_2.'</a>';
	if (fgallery_access_level()>= 5){
            $output .= '<a href="'.fgallery_get_image_delete_url($id).'" rel="image_'.$id.'" title="'.$title_3.'" class="fgallery_action delete">'.$title_3.'</a>';
        }
        return $output;
}

// renders the shortcode for inserting the gallery into the content
function fgallery_do_shortcode($id, $type = 0) {
    $album = fgallery_get_album($id);
    return sprintf('[fgallery id=%d w=%d h=%d bg=%s t=%d title="%s"]',$id, $album['gall_width'], $album['gall_height'], $album['gall_bgcolor'], $type, trim($album['gall_name']));
}

// renders the gaellery edit form
function fgallery_render_edit_album_form($album, $source) {
	$id = $album['gall_id'];
	$output .= '<div id="sc-group-general" class="sc-skin-params-tab-panel">';
	$output .= '<div class="form-item even"><label for="fgalleryName">'.__('Name:', 'fgallery').'</label>
				<textarea cols="30" rows="5" id="fgalleryName" name="gallery[fgallery_name]" class="required">'.$album['gall_name'].'</textarea>
				</div>';
	$output .= '<div class="form-item odd"><label for="fgalleryDescription">'.__('Description:','fgallery').'</label>
				<textarea cols="30" rows="5" id="fgalleryDescription" name="gallery[fgallery_description]">'.$album['gall_description'].'</textarea>
				</div>';
	$output .= '<div class="form-item even" style="display:none;">
				<label for="fgallery_status">'.__('Status', 'fgallery').'</label>
					<select name="gallery[fgallery_status]" id="fgallery_status" class="form-select">
						<option value="1" '.$pubselect.'>'.__('Published').'</option>';
        $output .= '</select></div>';
	$output .= '<div class="form-item odd" style="display:none;">
		<label for="slide_source">'.__('Source', 'fgallery').'</label>
		<select name="slide_source" id="slide_source" class="form-select">
			<option value="local" '.$opt['local'].'>'.__('Local').'</option>';
	$output .= '</select></div>';
	$output .= '<div class="form-item even">
					<label for="fgalleryWidth">'.__('Width:', 'fgallery').'</label>
					<input type="text" id="fgalleryWidth" name="gallery[gall_width]" class="form-text numeric" value="'.$album['gall_width'].'" />
				</div>';
	$output .= '<div class="form-item odd">
					<label for="fgalleryHeight">'.__('Height:', 'fgallery').'</label>
					<input type="text" id="fgalleryHeight" name="gallery[gall_height]" class="form-text numeric" value="'.$album['gall_height'].'" />
				</div>';
	$output .= '<div class="form-item even">
					<label for="fgalleryBgcolor">'.__('Background Color:', 'fgallery').'</label>
					<input type="text" id="fgalleryBgcolor" name="gallery[gall_bgcolor]" class="sc-color-val form-text" value="#'.$album['gall_bgcolor'].'" />
				</div>';
	$opt = array();
	$opt[$album['gall_type']] = 'selected ="selected"';
	$output .= '<div class="form-item odd">
					<label for="gall_type"><b>'.__('Gallery Type:', 'fgallery').'</b></label> 
		<select name="gallery[gall_type]" id="gall_type" class="form-select" onchange="save_settings();">
			<option value="1" '.$opt[1].'>'.__('Acosta').'</option>
			<option value="2" '.$opt[2].'>'.__('Airion').'</option>
			<option value="3" '.$opt[3].'>'.__('Arai').'</option>
			<option value="4" '.$opt[4].'>'.__('Pax').'</option>
			<option value="5" '.$opt[5].'>'.__('Pazin').'</option>
			<option value="6" '.$opt[6].'>'.__('Postma').'</option>
			<option value="7" '.$opt[7].'>'.__('Pageflip').'</option>
		</select></div>';
	$output .= '</div>';

	return $output;
}

function fgallery_get_flash_type($album) {
	switch ($album['gall_type']) {
		case 1: return 'acosta';
		case 2: return 'airion';
		case 3: return 'arai';
		case 4: return 'pax';
		case 5: return 'pazin';
		case 6: return 'postma';
		case 7: return 'pageflip';
		default: return 'arai';
	}
}

// renders the image edit form
function fgallery_render_edit_image_form($image) {
	$id = $image['img_id'];
	$output = '<form method="post" action="'.fgallery_get_image_edit_url($id).'">';
	$output .= '<p><label for="fgalleryImageCaption">'.__('Caption:', 'fgallery').'</label><br /><input type="text" id="fgalleryImageCaption" name="fgallery_image_caption" value="'.$image['img_caption'].'" /></p>';
	if (!$image['img_vs_folder']){
		$output .= '<p><label for="fgalleryImageDescription">'.__('Description:', 'fgallery').'</label><br /><textarea cols="30" rows="5" id="fgalleryImageDescription" name="fgallery_image_description">'.$image['img_description'].'</textarea></p>';
	}
	$output .= wp_nonce_field('fgallery_edit','fgallery_edit_image_field');
	$output .= '<p><input type="submit" name="fgallery_image_submit" value="'.__('Save').'" /></p>';
	$output .='</form>';	
	return $output;
}

// formats the image/folder/gallery size 
function formatBytes($b,$p = null) {
    /**
     *
     * @author Martin Sweeny
     * @version 2010.0617
     *
     * returns formatted number of bytes.
     * two parameters: the bytes and the precision (optional).
     * if no precision is set, function will determine clean
     * result automatically.
     *
     **/
    $units = array("B","kB","MB","GB","TB","PB","EB","ZB","YB");
    $c=0;
    if(!$p && $p !== 0) {
        foreach($units as $k => $u) {
            if(($b / pow(1024,$k)) >= 1) {
                $r["bytes"] = $b / pow(1024,$k);
                $r["units"] = $u;
                $c++;
            }
        }
        return number_format($r["bytes"],2) . " " . $r["units"];
    } else {
        return number_format($b / pow(1024,$p)) . " " . $units[$p];
    }
}

/*
* Starting URLs()
*
*/

function fgallery_images_url() {
	return admin_url('admin.php?page=fgallery_images');
}

function fgallery_get_upload_url() {
	return admin_url('admin.php?page=fgallery_upload');
}

function fgallery_createalbum_url() {
	return admin_url('admin.php?page=fgallery_add');
}

function fgallery_get_addimages_url($id) {
	return FGALLERY_PATH.'/addimage.php?id='.$id.'&TB_iframe=1';
}

function fgallery_get_addimages_url_clean($id) {
	return FGALLERY_PATH.'/addimage.php?id='.$id;
}

function fgallery_get_album_images_url($id) {
	return admin_url('admin.php?page=fgallery&amp;action=images&amp;id='.$id);
}

function fgallery_create_folder_url($type,$id) {
	return FGALLERY_PATH.'/folder.php?&amp;action=create&amp;type='.$type.'&amp;gall_id='.$id.'&amp;TB_iframe=1';
}

function fgallery_get_settings_url($id) {
	return FGALLERY_PATH.'/settings.php?gall_id='.$id;
}

function fgallery_get_edit_url($id) {
	return admin_url('admin.php?page=fgallery&amp;action=edit&amp;id='.$id);
}

function fgallery_get_edit_url_clean($id) {
	return admin_url('admin.php?page=fgallery&action=edit&id='.$id);
}

function fgallery_get_image_edit_url($id) {
	return admin_url('admin.php?page=fgallery_images&amp;action=edit&amp;id='.$id);
}

function fgallery_get_album_image_edit_url() {
	return admin_url('admin.php?page=fgallery_images&amp;action=edit_album');
}

function fgallery_get_folder_url($id) {
	return admin_url('admin.php?page=fgallery_images&amp;folder='.$id);
}

function fgallery_get_image_delete_url($id) {
	return admin_url('admin.php?page=fgallery_images&amp;action=delete&amp;id='.$id);
}

function fgallery_get_delete_url($id){
	return admin_url('admin.php?page=fgallery&amp;action=delete&amp;id='.$id);
}

function fgallery_upload_zip_get_url() {
	return FGALLERY_PATH.'/upload.php?action=unzip';
}

function fgallery_upload_ftp_get_url() {
	return FGALLERY_PATH.'/upload.php?action=ftp';
}

function fgallery_upload_url_get_url() {
	return FGALLERY_PATH.'/upload.php?action=upload_url';
}

function fgallery_upload_media_get_url() {
	return FGALLERY_PATH.'/upload.php?action=media';
}

function fgallery_upload_facebook_get_url() {
	return FGALLERY_PATH.'/upload.php?action=facebook';
}

function fgallery_remove_image_from_gallery_url($img_id, $gall_id) {
	return admin_url('admin.php?page=fgallery_images&amp;action=remove&amp;id='.$img_id.'&amp;gall_id='.$gall_id);
}

function fgallery_set_gallery_cover_url($img_id, $gall_id) {
	return admin_url('admin.php?page=fgallery_images&amp;action=cover&amp;id='.$img_id.'&amp;gall_id='.$gall_id);
}

function fgallery_sort_url() {
	return admin_url('admin.php?page=fgallery_images&amp;action=sort');
}

function fgallery_sort_galleries_url() {
	return admin_url('admin.php?page=fgallery&amp;action=sort');
}

function fgallery_get_massedit_url() {
    return FGALLERY_PATH.'/massedit.php';
}

function fgallery_get_massedit_album_url() {
	return FGALLERY_PATH.'/massedit_album.php';
}

function fgallery_get_add_album_url($post){
    return FGALLERY_PATH.'/insert_gallery.php?post='.$post.'&amp;TB_iframe=1';
}

function fgallery_get_insert_button_url(){
    return FGALLERY_PATH.'/images/icon_gallery.gif';
}

function fgallery_get_folder_addimage_url() {
	return FGALLERY_PATH.'/folder_addimage.php';
}

function fgallery_get_props_url($id) {
	return FGALLERY_PATH.'/folder_addimage.php?action=prop&amp;gall_id='.$id;
}

function fgallery_get_values_url($id) {
	return FGALLERY_PATH.'/folder_addimage.php?action=value&amp;gall_id='.$id;
}

function fgallery_get_pref_url() {
	return admin_url('admin.php?page=fgallery&action=pref');
}

function fgallery_view_gallery_url($id, $width, $height, $bgcolor) {
	$height = $height+30;
	return FGALLERY_PATH.'/view.php?gall_id='.$id.'&amp;width='.$width.'&amp;height='.$height.'&amp;bg='.$bgcolor.'&amp;TB_iframe=1';
}


/*
 * Filters
 *
 */

add_filter('screen_settings', 'fgallery_screen_settings', 10, 2);
add_filter('contextual_help', 'fgallery_plugin_help', 10, 3);
add_filter('media_buttons_context','fgallery_add_button',9);
add_shortcode( 'fgallery', 'fgallery_shortcode_handler' );


function fgallery_plugin_help($contextual_help, $screen_id, $screen) {
	if ($screen_id == 'toplevel_page_fgallery' || $screen_id == '1-flash-gallery_page_fgallery_add'
		|| $screen_id == '1-flash-gallery_page_fgallery_add' || $screen_id == '1-flash-gallery_page_fgallery_images' || $screen_id == '1-flash-gallery_page_fgallery_upload') {
		$contextual_help = '<p><a href="http://1plugin.com/faq" target="_blank">'.__('FAQ').'</a></p>';
	}
	return $contextual_help;
}

// filter for setting the number of elements on the screen
function fgallery_screen_options($screen) {
    if ( is_string($screen) )
		$screen = convert_to_screen($screen);

	$option = str_replace( '-', '_', "{$screen->id}_per_page" );

	$per_page = (int) get_option( $option , 25);
        if ( $screen->id ==  '1-flash-gallery_page_fgallery_images') {
            $per_page_label = _x( 'Images', 'images per page (screen options)' );
        } else {
            $per_page_label = _x( 'Galleries', 'galleries per page (screen options)' );
        }

	$return = "<div class='screen-options'>\n";
	if ( !empty($per_page_label) )
	$return .= "<input type='text' class='screen-per-page' name='wp_screen_options[value]' id='$option' maxlength='3' value='$per_page' /> <label for='$option'>$per_page_label</label>\n";
	$return .= "<input type='submit' class='button' value='" . esc_attr__('Apply') . "' />";
	$return .= "<input type='hidden' name='wp_screen_options[option]' value='" . esc_attr($option) . "' />";
	$return .= "</div>\n";
	return $return;
}

function fgallery_screen_settings($current, $screen){
	if ( $screen->id ==  '1-flash-gallery_page_fgallery_images' && !isset($_GET['action'])){
                $current = '<h5>'._x('Show on screen', 'Screen Options').'</h5>';
		$current .= fgallery_screen_options($screen);
                $current .= '<div>'. wp_nonce_field( 'screen-options-nonce', 'screenoptionnonce', true ).'</div>';
	} elseif ($screen->id == 'toplevel_page_fgallery' && !isset($_GET['action'])){
                $current = '<h5>'._x('Show on screen', 'Screen Options').'</h5>';
		$current .= fgallery_screen_options($screen);
                $current .= '<div>'. wp_nonce_field( 'screen-options-nonce', 'screenoptionnonce', true ).'</div>';
        }
	return $current;
}

function plugin_is_active($plugin_path) {
    $return_var = in_array( $plugin_path, apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
    return $return_var;
  }


function fgallery_search_flash_path($album) {
	$type = fgallery_get_flash_type($album);
	if (get_option('1_flash_gallery_'.fgallery_get_flash_type($album)) !='') {
		if (plugin_is_active('1-flash-gallery-update/update_fgallery.php')) {
			return WP_PLUGIN_URL .'/1-flash-gallery-update/swf/'.fgallery_get_flash_type($album).'.swf';
		} elseif (plugin_is_active('1-flash-gallery-update-'.fgallery_get_flash_type($album).'/update_'.fgallery_get_flash_type($album).'.php')) {
			return WP_PLUGIN_URL .'/1-flash-gallery-update-'.fgallery_get_flash_type($album).'/swf/'.fgallery_get_flash_type($album).'.swf';
		} else {
			return FGALLERY_PATH.'/swf/'.fgallery_get_flash_type($album).'.swf';
		}
	} else {
		return FGALLERY_PATH.'/swf/'.fgallery_get_flash_type($album).'.swf';
	}
}

// filter for replacing gallery shortcode with flash movie itself
function fgallery_shortcode_handler($atts, $content = null){
		// set default params if not present
		extract(shortcode_atts( array(
			'id' => 0,
			'w' => 450,
			'h' => 385,
			'bg' => 'ffffff',
			't' => 0,	
			'align' => '',
			'thumb' => '',
			'title' => '',
		), $atts ));

		if ($t < 0 || $t > 2) {
			$t = 0;
		}
		// check if the gallery exist
		if ($id > 0) {
			$album = fgallery_get_album($id);
			if (empty($album)) {
				return '[fgallery 404 Not found]';
			}
		} else {
			return '[fgallery 404 Not found]';
		}
		// different output for different insert type
		switch ($t) {
			case '0' : 
				$rand = rand(0, 150);
				$path = fgallery_search_flash_path($album);
				if (isset($align)) {
					switch ($align) {
						case 'left' : case 'right' :
							$align_text = 'float:'.$align;
						break;
						default:
							$align_text = '';
						break;
					}
				}
				$gallery_snippet = '<div style="width:'.$w.'px;margin:5px;'.$align_text.'">
				<script type="text/javascript">
					var flashvars = {settings: "'.FGALLERY_PATH.'/config.php?gall_id='.$id.'", images : "'.FGALLERY_PATH.'/images.php?gall_id='.$id.'"};
					var params = {bgcolor: "#'.$bg.'", allowFullScreen: "true", wmode: "transparent"};
					swfobject.embedSWF("'.$path.'", "flashcontent_'.$id.$rand.'", "'.$w.'", "'.$h.'", "10.0.0",false, flashvars, params);
				  </script>
				<div id="flashcontent_'.$id.$rand.'">
					<strong>You need to upgrade your Flash Player</strong>
				 </div>';
				 if (get_option('1_flash_gallery_'.fgallery_get_flash_type($album)) =='') {
					$gallery_snippet .= '<div class="fgallery_message"></div>';
				 }
				 $gallery_snippet .= '</div>';
			break;
			case '1' : 
				if ($title == '') {
					$insert_text = $album['gall_name'];
				} else {
					$insert_text = $title;
				}
				$gallery_snippet = '<a href="'.fgallery_view_gallery_url($id, $w, $h, $bg).'"
									class="thickbox" title="'.$album['gall_name'].'">'.$insert_text.'</a>
									<p>'.fgallery_get_album_attributes($album, false).'</p>';
			break;
			case '2' :
				$gallery_snippet = '<a href="'.fgallery_view_gallery_url($id, $w, $h, $bg).'"
									class="thickbox" title="'.$album['gall_name'].'">'.fgallery_get_album_cover($album, $thumb).'</a>
									<p>'.$album['gall_name'].'</p><p>'.fgallery_get_album_attributes($album, false).'</p>';
			break;
		}
		
	   return $gallery_snippet;
}

// filter to add the button (for inserting the gallery into post) to post edit page
function fgallery_add_button($buttons) {
    if (isset($_GET['post']) && is_numeric($_GET['post'])) {
        $post = $_GET['post'];
    }
     $fgallery_button = " <a href='" . esc_url( fgallery_get_add_album_url($post) ) . "' id='insert_gallery' class='thickbox' title='".__('Insert gallery into post', 'fgallery')."'>
        <img src='" . esc_url( fgallery_get_insert_button_url( ) ) . "' alt='".__('Insert gallery into post', 'fgallery')."' /></a>";
    $buttons .= $fgallery_button;
  return $buttons;
}