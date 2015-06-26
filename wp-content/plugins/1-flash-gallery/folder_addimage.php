<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
 
require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/wp-load.php');
require_once('fgallery.php');

global $wpdb;

if (!empty($_POST)) {
	$wpdb->update(IMAGES_TABLE, array('img_parent' => $_POST['folder_id']), array('img_id' => $_POST['img_id']));
	$folder = fgallery_get_image($_POST['folder_id']);
	echo fgallery_get_folder_attributes($folder);
} else {
	die('asdasd');
}
?>
