<?php
session_start();
require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/wp-load.php');
require_once('fgallery.php');
global $wpdb;

function fgallery_images_count_rest($folder, $id) {
	global $wpdb;
		$count = $wpdb->get_var("SELECT COUNT(*) FROM " .IMAGES_TABLE." WHERE `img_parent` = ".$folder." AND `img_vs_folder` IN (0,1) AND `img_id` NOT IN (SELECT img_id FROM ". IMAGES_TO_ALBUMS_TABLE." WHERE gall_id = ".$id.")");
	return $count;
}

function fgallery_get_restof_images($id, $pagenum, $per_page, $folder, $sort = 3) {
	global $wpdb;
        $cond = fgallery_sort_images_condition($sort);
	   $images = $wpdb->get_results("SELECT * FROM ". IMAGES_TABLE. " WHERE img_id NOT IN (SELECT img_id FROM ". IMAGES_TO_ALBUMS_TABLE." WHERE gall_id = ".$id.") AND `img_parent` = ".$folder." AND `img_vs_folder` IN (0,1) ORDER BY `img_vs_folder` DESC, ".$cond." LIMIT ".($pagenum-1)*$per_page.",".$pagenum*$per_page, ARRAY_A);
	return $images;
}

function fgallery_addimage_box($id, $pagenum, $per_page, $folder) {
	$images = fgallery_get_restof_images($id, $pagenum, $per_page, $folder);
	echo fgallery_render_addimage_box($images, $id);
}

function fgallery_get_album_name($id) {
	global $wpdb;
		$name = $wpdb->get_row("SELECT gall_name FROM ". ALBUMS_TABLE ." WHERE gall_id =".$id, ARRAY_A);
	return $name['gall_name'];
}

function fgallery_render_addimage_box($items, $id) {
	if (count($items) > 0) {
	$output = '<h2>'.fgallery_get_album_name($id).'</h2>';
	$output = '<form action="'.fgallery_get_addimages_url($id).'" method="post">';
	$output .= '<input type="submit" value="'.__('Add to gallery', 'fgallery').'" /><br />';
	if (isset($_GET['folder'])) {
		$output .=  '<a class="go_up thickbox" href="'.fgallery_get_addimages_url($id).'">'.__('Go up', 'fgallery').'...</a>';
	}
	$output .= '<ul>';

		foreach ($items as $item) {
			$checked = '';
			if (@in_array($item['img_id'],$_SESSION['image'])) {
				$checked = 'checked="checked"';
			} else {
				$checked = '';
			}
			$output .= '<li><div class="image_wrap">
								<div class="fgallery_image_actions">
									<input type="checkbox" value="'.$item['img_id'].'" name="image[]" '.$checked.' />
								</div>
								<div class="fgallery_image">';
					if ($item['img_vs_folder'] == 1){
							$output .= '<img src="'.FGALLERY_PATH.'/images/folder.png" />';
						} elseif($item['img_vs_folder'] == 0) {
								$output .='<img src="'.COVER_PATH.$item['img_path'].'" /> ';
						}
					$output .= '</div><div class="fgallery_image_info">';
					if ($item['img_vs_folder'] == 1){
							$output .= '<a href="'.fgallery_get_addimages_url($id).'&folder='.$item['img_id'].'"><b>'.$item['img_caption'].'</b></a>';
						} elseif ($item['img_vs_folder'] == 0) {
							$output .='<b>'.$item['img_caption'].'</b> ';
						}
									
									
					$output .= '<br />'.$item['img_description'].'
								</div>
							</div>
						</li>';
		}
		$output .= '</ul><input type="hidden" value="'.$id.'" name="gall_id" /><input type="submit" value="'.__('Add to gallery', 'fgallery').'" />';
                $output .= wp_nonce_field('fgallery_add_images','fgallery_add_images_to_gallery');
                $output .= '</form>';
	} else {
		echo 'You need to upload images for your gallery <a href="'.fgallery_get_upload_url().'" target="_blank">here</a>';
	}
	return $output;
	
}

	if (!empty($_POST) && wp_verify_nonce($_POST['fgallery_add_images_to_gallery'],'fgallery_add_images')) {
		$ids = $_SESSION['image'];
		if (empty($ids)) {
			$ids = $_POST['image'];
		}
		$gall_id = $_POST['gall_id'];
		if (count($ids) > 0) {
			foreach ($ids as $id) {
				$image = fgallery_get_image($id);
				if ($image['img_vs_folder'] == 1) {
					$wpdb->insert(IMAGES_TABLE, array('img_caption' => $image['img_caption'], 'img_vs_folder' => 2, 'img_date'=>date("Y-m-d H:i:s")));
					$parent = $wpdb->insert_id;
					$wpdb->insert(IMAGES_TO_ALBUMS_TABLE, array('img_id'=>$parent, 'gall_id'=>$gall_id, 'img_order' => 0, 'gall_folder' => 0));
					$images = fgallery_get_images(1, 99999999, $image['img_id'], $sort = 3);
					foreach ($images as $row) {
						$wpdb->insert(IMAGES_TO_ALBUMS_TABLE, array('img_id'=>$row['img_id'], 'gall_id'=>$gall_id, 'img_order' => 0, 'gall_folder' => $parent));	
					}
				} elseif ($image['img_vs_folder'] == 0) {
					$wpdb->insert(IMAGES_TO_ALBUMS_TABLE, array('img_id'=>$id, 'gall_id'=>$gall_id, 'img_order' => 0));
				}
			}
		}
		session_unset();
		session_destroy();
		?>
		<script type="text/javascript">
		/* <![CDATA[ */
		var win = window.dialogArguments || opener || parent || top;
		win.tb_remove();
		win.location.reload();
		/* ]]> */
		</script>
		<?php
		die();
	} 

	if (!empty($_POST) && isset($_POST['state'])) {
		if ($_POST['state'] == 1) {
			$_SESSION['image'][] = $_POST['value'];
		} else {
			$key = array_search($_POST['value'], $_SESSION['image']);
			unset($_SESSION['image'][$key]);
		}
		die();
	}
	?>
	

  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
<head>
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
<title><?php bloginfo('name') ?> &rsaquo; <?php _e('Insert Gallery', 'fgallery'); ?> &#8212; <?php _e('WordPress'); ?></title>
<?php
wp_enqueue_style( 'global' );
wp_enqueue_style( 'wp-admin' );
wp_enqueue_style( 'colors' );
wp_enqueue_style('fgallerycss');
// Check callback name for 'media'
wp_enqueue_style( 'ie' );
?>
<script type="text/javascript">
//<![CDATA[
addLoadEvent = function(func){if(typeof jQuery!="undefined")jQuery(document).ready(func);else if(typeof wpOnload!='function'){wpOnload=func;}else{var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}};
var userSettings = {'url':'<?php echo SITECOOKIEPATH; ?>','uid':'<?php if ( ! isset($current_user) ) $current_user = wp_get_current_user(); echo $current_user->ID; ?>','time':'<?php echo time(); ?>'};
//]]>
</script>
<?php
do_action('admin_print_styles');
do_action('admin_print_scripts');
do_action('admin_head');
?>
</head>
<body<?php if ( isset($GLOBALS['body_id']) ) echo ' id="' . $GLOBALS['body_id'] . '"'; ?>>

  <?php 
		if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $id = $_GET['id'];
            $pagenum = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 0;
            $folder = isset( $_GET['folder'] ) ? absint( $_GET['folder'] ) : 0;
            if ( empty($pagenum) ) $pagenum = 1;
            if ( empty( $per_page ) || $per_page < 1 )
			$per_page = get_option('1_flash_gallery_page_fgallery_images_per_page',25);
            $num_pages = ceil(fgallery_images_count_rest($folder, $id) / $per_page);
            $page_links = paginate_links( array(
			'base' => add_query_arg( 'paged', '%#%' ),
			'format' => '',
			'prev_text' => __('&laquo;'),
			'next_text' => __('&raquo;'),
			'total' => $num_pages,
			'current' => $pagenum
		));
         ?>
             <div class="tablenav">
    			<?php if ( $page_links ) { ?>
				<div class="tablenav-pages">
				<?php $count_posts = fgallery_images_count_rest($folder, $id);
					  $page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>%s',
										number_format_i18n( ( $pagenum - 1 ) * $per_page + 1 ),
										number_format_i18n( min( $pagenum * $per_page, $count_posts ) ),
										number_format_i18n( $count_posts ),
										$page_links
										);
					echo $page_links_text; ?>
				</div>
			<?php } ?>
        </div>
         <?php  fgallery_addimage_box($id, $pagenum, $per_page, $folder); } ?>
             <div class="tablenav">
    			<?php if ( $page_links ) { ?>
				<div class="tablenav-pages">
				<?php echo $page_links_text; ?>
				</div>
			<?php } ?>
        </div>
<?php
	do_action('admin_print_footer_scripts');
?>
<script type="text/javascript">if(typeof wpOnload=='function')wpOnload();
	jQuery(document).ready(function(){
		jQuery('.fgallery_image_actions input[type="checkbox"]').change(function(){
		c = jQuery(this).attr('checked');
		if (c) {
			state = 1;
		} else {
			state = 0;
		}
			jQuery.ajax({
				url  : '<?php echo fgallery_get_addimages_url_clean($id)?>',
				type : "POST",
				data : ({'value' : jQuery(this).val() , 'state' : state}),
			});
		});
	});
</script>
</body>
</html>