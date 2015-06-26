<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/wp-load.php');
require_once('fgallery.php');

global $wpdb;

if (!empty($_POST) && wp_verify_nonce($_POST['fgallery_folder_field'],'fgallery_create_folder')) {
	$save = $wpdb->insert(IMAGES_TABLE, array('img_caption'=>$_POST['fgallery_image_caption'] , 'img_vs_folder' => $_POST['folder_type'], 'img_parent' => 0, 'img_date' => date("Y-m-d H:i:s")));
	if ($_POST['folder_type'] == 2) {
		$wpdb->insert(IMAGES_TO_ALBUMS_TABLE, array('img_id'=>$wpdb->insert_id , 'gall_id' => $_POST['gall_id'], 'gall_folder' => 0));
	}
?>
	<script type="text/javascript">
		/* <![CDATA[ */
		var win = window.dialogArguments || opener || parent || top;
		win.tb_remove();
		win.location.reload();
		/* ]]> */
		</script>
	<?php 
	//wp_redirect(fgallery_images_url());
} else {
    ?>
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
	<head>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
	<title><?php bloginfo('name') ?> &rsaquo; <?php _e('Insert Gallery','fgallery'); ?> &#8212; <?php _e('WordPress'); ?></title>
	<?php
	wp_enqueue_style( 'global' );
	wp_enqueue_style( 'wp-admin' );
	wp_enqueue_style( 'fgallerycss' );
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
	if (isset($_GET['type']) && is_numeric($_GET['type'])) {
		$type = $_GET['type'];
	}
	if (isset($_GET['gall_id']) && is_numeric($_GET['gall_id'])) {
		$gall_id = $_GET['gall_id'];
	}
	?>
	</head>
	<body<?php if ( isset($GLOBALS['body_id']) ) echo ' id="' . $GLOBALS['body_id'] . '"'; ?>>
		<div id="wpwrap">
		<div id="wpcontent">
		<div id="wpbody">
			<div class="wrap">
			<h2><?php _e('Create new folder')?></h2>
			<form action="" method="POST">
				<p><label for="fgalleryImageCaption"><?php _e('Name:', 'fgallery') ?></label><br />
				<input type="text" id="fgalleryImageCaption" name="fgallery_image_caption" value="" /></p>
				<input type="hidden" name="folder_type" value="<?php echo $type;?>" />
				<input type="hidden" name="gall_id" value="<?php echo $gall_id;?>" />
				<?php wp_nonce_field('fgallery_create_folder','fgallery_folder_field'); ?>
				<p><input type="submit" name="fgallery_image_submit" value="<?php _e('Save') ?>" /></p>
			</form>
			</div>
		</div>
		</div>
		</div>
	<?php
		do_action('admin_print_footer_scripts');
	?>
	<script type="text/javascript">if(typeof wpOnload=='function')wpOnload();</script>
	</body>
	</html>
<?php
}

?>
