<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/wp-load.php');
require_once('fgallery.php');

global $wpdb;
global $current_user;

if (!empty($_POST) && wp_verify_nonce($_POST['fgallery_massedit_field'],'fgallery_massedit')) {
    if (is_numeric($_POST['image_action'])){
		switch ($_POST['image_action']) {
			case '-2':
				if (!empty($_POST['image'])){
					$ids = $_POST['image'];
					foreach ($ids as $id) {
						fgallery_delete_image($id);
					}
				}
				wp_redirect(fgallery_images_url());
				break;
			case '-1':
				if (!empty($_POST['image'])) {
					$ids = $_POST['image'];
					$user_id = $current_user->ID;//get_current_user_id();
					$max = $wpdb->get_var("SELECT MAX(`gall_order`) FROM ".ALBUMS_TABLE);
					$wpdb->insert(ALBUMS_TABLE, array('gall_name' => __('New gallery'), 'gall_createddate' => date("Y-m-d H:i:s"), 'gall_createdby' => $user_id, 'gall_published' => 1, 'gall_order' => $max+1, 'gall_type' => 3));
					$gall_id = $wpdb->insert_id;
					foreach ($ids as $id) {
							$image = fgallery_get_image($id);
							if ($image['img_vs_folder'] == 1) {
								$images = fgallery_get_images(1, 99999999, $image['img_id'], $sort = 3);
								foreach ($images as $row) {
									$wpdb->insert(IMAGES_TO_ALBUMS_TABLE, array('img_id'=>$row['img_id'], 'gall_id'=>$gall_id, 'img_order' => 0));	
								}
							} elseif ($image['img_vs_folder'] == 0) {
								$wpdb->insert(IMAGES_TO_ALBUMS_TABLE, array('img_id'=>$id, 'gall_id'=>$gall_id, 'img_order' => 0));
							}
						}
				wp_redirect(admin_url('admin.php?page=fgallery&action=edit&id='.$gall_id));
				}
				break;
			default:
				if (!empty($_POST['image'])) {
					$ids = $_POST['image'];
					$ids_string = implode(",",$ids);
					$wpdb->query("UPDATE ".IMAGES_TABLE." SET `img_parent` =".$_POST['image_action']." WHERE `img_id` IN (".$ids_string.") AND `img_vs_folder` = 0");
				}
				wp_redirect(fgallery_images_url());
				break;
		}
	} else {
			$gall_id = str_replace('gall_','',$_POST['image_action']);
			if (is_numeric($gall_id)) {
					if (!empty($_POST['image'])) {
							$ids = $_POST['image'];
							foreach ($ids as $id) {
								$image = fgallery_get_image($id);
									if ($image['img_vs_folder'] == 1) {
										$images = fgallery_get_images(1, 99999999, $image['img_id'], $sort = 3);
										foreach ($images as $row) {
											$wpdb->insert(IMAGES_TO_ALBUMS_TABLE, array('img_id'=>$row['img_id'], 'gall_id'=>$gall_id, 'img_order' => 0));	
										}
									} elseif ($image['img_vs_folder'] == 0) {
										$wpdb->insert(IMAGES_TO_ALBUMS_TABLE, array('img_id'=>$id, 'gall_id'=>$gall_id, 'img_order' => 0));
									}
								}
						wp_redirect(admin_url('admin.php?page=fgallery&action=edit&id='.$gall_id));
						}
			} else {
				wp_redirect(fgallery_images_url());
			}
	}
} else {
	wp_redirect(fgallery_images_url());
}

?>
