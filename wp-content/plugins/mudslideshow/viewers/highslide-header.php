<?php

	require('none-header.php');
	
	function muds_hsScriptGallery($rand, $single= false, $side=false) {
	if($single) {
		$base = "hsSingle";
	} else {
		if($side) {
			$base = "hsInfo";
		} else {
			$base = "hsGallery";
		}
	}
	
	$answer="<script type='text/javascript' id='muds_script$rand'>
	var hsGallery$rand = {
		slideshowGroup: 'group$rand'
	};
	";
	
	if($single) {
		$answer.= " for (attrname in hsSingle) { hsGallery{$rand}[attrname] = hsSingle[attrname]; }";
	} else {
		$answer.= " for (attrname in hsGallery) { hsGallery{$rand}[attrname] = hsGallery[attrname]; }";
	}
	if($side) {
		$answer.= " for (attrname in hsInfo) { hsGallery{$rand}[attrname] = hsInfo[attrname]; }";
	}
	
	$answer.= "</script>";
	return $answer;
}
	
	$hs_config = get_theme_root()."/".get_template()."/hs-config.js";
	if(file_exists($hs_config)) {
		$hs_config = get_bloginfo('template_directory')."/hs-config.js";
	} else {
		$hs_config = muds_plugin_url("/scripts/hs-config.js");
	}

	//Declare javascript
	wp_register_script('hs-config', $hs_config, false, MUDS_HEADER_V);
	wp_enqueue_script('hs-config');
	wp_register_script('hs-full', muds_plugin_url('/scripts/highslide-full.js'), array('jquery', 'hs-config'), MUDS_HEADER_V);
	wp_enqueue_script('hs-full');
	wp_register_script('highslide', muds_plugin_url('/scripts/hs-functions.js'), array('jquery', 'hs-full'), MUDS_HEADER_V);
	wp_enqueue_script('highslide');
	
	//Define custom CSS URI
	$css = get_theme_root()."/".get_template()."/highslide.css";
	if(file_exists($css)) {
		$css_register = get_bloginfo('template_directory')."/highslide.css";
	} else {
		$css_register = muds_plugin_url("/css/highslide.css");
	}
	//Declare style
	wp_register_style('highslide', $css_register, false, MUDS_HEADER_V);
	wp_enqueue_style('highslide');
	
	// Declare we use JavaScript SACK library for Ajax
	wp_print_scripts( array( 'highslide' ));
	wp_print_styles( array( 'highslide' ));


?>
<script type="text/javascript" language="JavaScript" >

//i18n for Highslide
        
hs.lang.cssDirection = '<?php _e('ltr', 'mudslide'); ?>';
hs.lang.loadingText = '<?php _e('Loading...', 'mudslide'); ?>';
hs.lang.loadingTitle = '<?php _e('Click to cancel', 'mudslide'); ?>';
hs.lang.focusTitle = '<?php _e('Click to bring to front', 'mudslide'); ?>';
hs.lang.fullExpandTitle = '<?php _e('Expand to actual size (f)', 'mudslide'); ?>';
hs.lang.creditsText = '<?php _e('Powered by Highslide JS', 'mudslide'); ?>';
hs.lang.creditsTitle = '<?php _e('Go to the Highslide JS homepage', 'mudslide'); ?>';
hs.lang.previousText = '<?php _e('Previous', 'mudslide'); ?>';
hs.lang.nextText = '<?php _e('Next', 'mudslide'); ?>'; 
hs.lang.moveText = '<?php _e('Move', 'mudslide'); ?>';
hs.lang.closeText = '<?php _e('Close', 'mudslide'); ?>'; 
hs.lang.closeTitle = '<?php _e('Close (esc)', 'mudslide'); ?>'; 
hs.lang.resizeTitle = '<?php _e('Resize', 'mudslide'); ?>';
hs.lang.playText = '<?php _e('Play', 'mudslide'); ?>';
hs.lang.playTitle = '<?php _e('Play slideshow (spacebar)', 'mudslide'); ?>';
hs.lang.pauseText = '<?php _e('Pause', 'mudslide'); ?>';
hs.lang.pauseTitle = '<?php _e('Pause slideshow (spacebar)', 'mudslide'); ?>';
hs.lang.previousTitle = '<?php _e('Previous (arrow left)', 'mudslide'); ?>';
hs.lang.nextTitle = '<?php _e('Next (arrow right)', 'mudslide'); ?>';
hs.lang.moveTitle = '<?php _e('Move', 'mudslide'); ?>';
hs.lang.fullExpandText = '<?php _e('1:1', 'mudslide'); ?>';
hs.lang.number = '<?php _e('Image %1 of %2', 'mudslide'); ?>';
hs.lang.restoreTitle = '<?php _e('Click to close image, click and drag to move. Use arrow keys for next and previous.', 'mudslide'); ?>';

hsConfig('<?php echo muds_plugin_url('/img/highslide/'); ?>');

</script>
