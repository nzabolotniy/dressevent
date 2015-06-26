<?php 

	require('none-header.php'); 

	//Declare javascript
	wp_register_script('lytebox', muds_plugin_url('/scripts/lytebox.js'), array('jquery'), MUDS_HEADER_V);
	wp_enqueue_script('lytebox');
	
	//Define custom CSS URI
	$css = get_theme_root()."/".get_template()."/lytebox.css";
	if(file_exists($css)) {
		$css_register = get_bloginfo('template_directory')."/lytebox.css";
	} else {
		$css_register = muds_plugin_url("/css/lytebox.css");
	}
	//Declare style
	wp_register_style('lytebox', $css_register, false, MUDS_HEADER_V);
	wp_enqueue_style('lytebox');
	
	// Declare we use JavaScript SACK library for Ajax
	wp_print_scripts( array( 'lytebox' ));
	wp_print_styles( array( 'lytebox' ));

?>
