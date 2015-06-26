<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/wp-load.php');
require_once('fgallery.php');

global $wpdb;

if (!empty($_POST)) {
    if (is_numeric($_POST['album_id']) && is_numeric($_POST['gall_id'])){
		if (!empty($_POST['images'])) {
			$ids = $_POST['images'];
			$ids_string = implode(",",$ids);
		}
		switch ($_POST['album_id']) {
			case '-1':
				$wpdb->query("DELETE FROM ". IMAGES_TO_ALBUMS_TABLE. " WHERE img_id IN (".$ids_string.") AND gall_id = ".$_POST['gall_id']);
			break;
			default :
				$wpdb->query("UPDATE ".IMAGES_TO_ALBUMS_TABLE." SET `gall_folder` =".$_POST['album_id']." WHERE `img_id` IN (".$ids_string.") AND `gall_id` = ".$_POST['gall_id']);
			break;
		}
		wp_redirect(admin_url('admin.php?page=fgallery&action=images&id='.$_POST['gall_id']));
	}
} else {
    wp_redirect(fgallery_images_url());
}

?>
