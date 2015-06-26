<?php if( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
	exit();

	global $wpdb;
	global $db_version;
	$table_name = $wpdb->prefix . "mudslide";
	$wpdb->query("DROP TABLE $table_name;");
	delete_option('muds_db_version');
	delete_option('muds_options');
	delete_option('widget_mudslide');
	$table_name = $wpdb->prefix . "options";
	$wpdb->query("DELETE FROM $table_name WHERE option_name like '_transient_timeout_muds-%';");
	$wpdb->query("DELETE FROM $table_name WHERE option_name like '_transient_muds-%';");

?>
