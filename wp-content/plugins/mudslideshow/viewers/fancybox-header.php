<?php 
	
	require('none-header.php');
	
	$fb_config = get_theme_root()."/".get_template()."/fb-config.js";
	if(file_exists($fb_config)) {
		$fb_config = get_bloginfo('template_directory')."/fb-config.js";
	} else {
		$fb_config = muds_plugin_url("/scripts/fb-config.js");
	}

	//Declare javascript
	wp_register_script('fb-config', $fb_config, false, MUDS_HEADER_V);
	wp_enqueue_script('fb-config');
	wp_register_script('fancybox', muds_plugin_url('/scripts/fancybox.js'), array('jquery', 'fb-config'), MUDS_HEADER_V);
	wp_enqueue_script('fancybox');
	
	//Define custom CSS URI
	$css = get_theme_root()."/".get_template()."/fancybox.css";
	if(file_exists($css)) {
		$css_register = get_bloginfo('template_directory')."/fancybox.css";
	} else {
		$css_register = muds_plugin_url("/css/fancybox.css");
		$browser = browser_info();
		if(isset($browser['msie'])) {
			$src_path = muds_plugin_url('/img/fancybox/');
		?>
		<style type="text/css">
			#fancybox-loading.fancybox-ie div	{ background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php echo $src_path; ?>fancy_loading.png', sizingMethod='scale'); }
.fancybox-ie #fancybox-close		{ background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php echo $src_path; ?>fancy_close.png', sizingMethod='scale'); }

.fancybox-ie #fancybox-title-over	{ background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php echo $src_path; ?>fancy_title_over.png', sizingMethod='scale'); zoom: 1; }
.fancybox-ie #fancybox-title-left	{ background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php echo $src_path; ?>fancy_title_left.png', sizingMethod='scale'); }
.fancybox-ie #fancybox-title-main	{ background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php echo $src_path; ?>fancy_title_main.png', sizingMethod='scale'); }
.fancybox-ie #fancybox-title-right	{ background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php echo $src_path; ?>fancy_title_right.png', sizingMethod='scale'); }

.fancybox-ie #fancybox-left-ico		{ background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php echo $src_path; ?>fancy_nav_left.png', sizingMethod='scale'); }
.fancybox-ie #fancybox-right-ico	{ background: transparent; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php echo $src_path; ?>fancy_nav_right.png', sizingMethod='scale'); }

.fancybox-ie .fancy-bg { background: transparent !important; }

.fancybox-ie #fancy-bg-n	{ filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php echo $src_path; ?>fancy_shadow_n.png', sizingMethod='scale'); }
.fancybox-ie #fancy-bg-ne	{ filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php echo $src_path; ?>fancy_shadow_ne.png', sizingMethod='scale'); }
.fancybox-ie #fancy-bg-e	{ filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php echo $src_path; ?>fancy_shadow_e.png', sizingMethod='scale'); }
.fancybox-ie #fancy-bg-se	{ filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php echo $src_path; ?>fancy_shadow_se.png', sizingMethod='scale'); }
.fancybox-ie #fancy-bg-s	{ filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php echo $src_path; ?>fancy_shadow_s.png', sizingMethod='scale'); }
.fancybox-ie #fancy-bg-sw	{ filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php echo $src_path; ?>fancy_shadow_sw.png', sizingMethod='scale'); }
.fancybox-ie #fancy-bg-w	{ filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php echo $src_path; ?>fancy_shadow_w.png', sizingMethod='scale'); }
.fancybox-ie #fancy-bg-nw	{ filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='<?php echo $src_path; ?>fancy_shadow_nw.png', sizingMethod='scale'); }
	</style><?php
		}
	}
	//Declare style
	wp_register_style('fancybox', $css_register, false, MUDS_HEADER_V);
	wp_enqueue_style('fancybox');
	
	// Declare we use JavaScript SACK library for Ajax
	wp_print_scripts( array( 'fancybox' ));
	wp_print_styles( array( 'fancybox' ));

?>
