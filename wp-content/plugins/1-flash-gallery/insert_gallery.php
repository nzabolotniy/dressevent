<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/wp-load.php');
require_once('fgallery.php');

function media_send_to_editor($html) {
?>
<script type="text/javascript">
/* <![CDATA[ */
var win = window.dialogArguments || opener || parent || top;
win.send_to_editor('<?php echo addslashes($html); ?>');
/* ]]> */
</script>
<?php
	exit;
}

if (!empty($_POST) && wp_verify_nonce($_POST['insert_gallery_field'], 'insert_gallery')){
    $send_ids = $_POST['gallery'];
	$type = $_POST['sn_type'];
	if ( !empty($send_ids) ) {
            $html = '';
            foreach ($send_ids as $id){
                $gall_code = fgallery_do_shortcode($id, $type).'<br />';
                $html .= apply_filters('media_send_to_editor', $gall_code, $id, '');
            }
            return media_send_to_editor($html);
	}
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
            $pagenum = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 0;
            if ( empty($pagenum) ) $pagenum = 1;
            if ( empty( $per_page ) || $per_page < 1 )
			$per_page = get_option('toplevel_page_fgallery_per_page',25);
            $num_pages = ceil(fgallery_albums_count() / $per_page);
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
        </div>
        <form action="<?php echo fgallery_get_add_album_url($post)?>" method="post">
        <?php
            $items = fgallery_get_albums($pagenum,$per_page,4);
            foreach ($items as $item) {
                if (count(fgallery_get_album_images($item['gall_id']))>0 && $item['gall_published']):
               ?>
                        <div class="image_wrap">
                            <div class="fgallery_image_actions">
                                    <input type="checkbox" value="<?php echo $item['gall_id'] ?>" name="gallery[]" />
                            </div>
                            <div class="fgallery_image">
                                    <?php echo fgallery_get_album_cover($item) ?>
                            </div>
                            <div class="fgallery_image_info">
                                    <b><?php echo $item['gall_name'] ?></b><br />
                                    <?php echo $item['gall_description'] ?>
                            </div>
                        </div>
                <?php
                endif;
            }
            wp_nonce_field('insert_gallery','insert_gallery_field');
            ?>
				<input type="radio" name="sn_type" value="0" checked> <?php _e('Flash object', 'fgallery') ?>
				<input type="radio" name="sn_type" value="1"> <?php _e('Text link to gallery', 'fgallery'); ?>
				<input type="radio" name="sn_type" value="2"> <?php _e('Cover as link to gallery', 'fgallery'); ?>
				
                <input type="submit" value="<?php _e('Insert into post', 'fgallery');?>" name="insert_submit" />
        </form>
             <div class="tablenav">
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
        </div>
        <?php
	do_action('admin_print_footer_scripts');
?>
<script type="text/javascript">if(typeof wpOnload=='function')wpOnload();</script>
</body>
</html>


