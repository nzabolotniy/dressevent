<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/wp-load.php');
require_once('fgallery.php');

global $wpdb;

if (isset($_GET['gall_id']) && is_numeric($_GET['gall_id'])) {
    $gall_id = $_GET['gall_id'];
	$images = fgallery_get_album_images($gall_id);
	$album = fgallery_get_album($gall_id);
	if (empty($album)) {
		$album['gall_width'] = 450;
		$album['gall_height'] = 385;
		$album['gall_bgcolor'] = "#ffffff";
		$album['gall_type'] = 3;
	}
	header("Content-type: text/xml");
	echo "<?xml version='1.0' encoding='UTF-8'?>";
	echo '<images>';
		$siteurl = get_option('siteurl').'/';
		$nofolder = '';
			foreach ($images as $image){
				if ($image['img_vs_folder'] == 2) {
					$rows = fgallery_get_album_images($gall_id, $image['img_id']);
					if (count($rows) > 0) {
						echo '<folder name="'.$image['img_caption'].'">';
						foreach ($rows as $row) {
							switch ($album['gall_type']) {
								case 1: case 2: case 3:
									echo '<img file="'.$siteurl.$row['img_path'].'" title="'.$row['img_caption'].'" url="'.$row['img_url'].'" />'; 
								break;
								case 4: case 5: case 6:
									echo '<img file="'.$siteurl.$row['img_path'].'" title="'.$row['img_caption'].'" url="'.$row['img_url'].'"><![CDATA['.$row['img_description'].']]></img>'; 
								break;
								case 7:
									echo '<img type="page" align="fill" title="'.$row['img_caption'].'" file="'.$siteurl.$row['img_path'].'" file_b="" url="'.$row['img_url'].'" />';
								break;
							}
						}
						echo "</folder>";
					}
				} else {
							switch ($album['gall_type']) {
								case 1: case 2: case 3:
									$nofolder .= '<img file="'.$siteurl.$image['img_path'].'" title="'.$image['img_caption'].'" url="'.$image['img_url'].'" />'; 
								break;
								case 4: case 5: case 6:
									$nofolder .= '<img file="'.$siteurl.$image['img_path'].'" title="'.$image['img_caption'].'" url="'.$image['img_url'].'"><![CDATA['.$image['img_description'].']]></img>'; 
								break;
								case 7:
									$nofolder .= '<img type="page" align="fill" title="'.$image['img_caption'].'" file="'.$siteurl.$image['img_path'].'" file_b="" url="'.$image['img_url'].'" />';
								break;
							}
				}
			}
			if ($nofolder !='') {
				$nofolder = '<folder name="'.$album['gall_name'].'">'.$nofolder.'</folder>';
				echo $nofolder;
			}
		echo '</images>';
} else {
    die();
}

die();

?>
