	var loading_muds_img = new Image(); 
	loading_muds_img.src = mudslide_url+'/wp-content/plugins/mudslideshow/img/loading-page.gif';
	
	function muds_update( style, id, type, user, gallery, var1, var2, conf, rand )
	{
		var muds_sack_updated = new sack(mudslide_url+'/wp-admin/admin-ajax.php' );
		
		//Our plugin sack configuration
		muds_sack_updated.execute = 0;
		muds_sack_updated.method = 'POST';
		muds_sack_updated.setVar( 'action', 'muds_ajax_update' );
		muds_sack_updated.element = 'mss'+rand;
		
		//The ajax call data
		muds_sack_updated.setVar( 'style', style );
		muds_sack_updated.setVar( 'id', id );
		muds_sack_updated.setVar( 'type', type );
		muds_sack_updated.setVar( 'user', user );
		muds_sack_updated.setVar( 'gallery', gallery );
		muds_sack_updated.setVar( 'var1', var1 );
		muds_sack_updated.setVar( 'var2', var2 );
		muds_sack_updated.setVar( 'conf', conf );
		muds_sack_updated.setVar( 'rand', rand );
		
		//What to do on error?
		muds_sack_updated.onError = function() {
			var aux = document.getElementById(muds_sack_updated.element);
			aux.innerHTMLsetAttribute=mudslide_i18n_error;
		};
		
		if(style=='simple') {
			//What to do when ready?
			muds_sack_updated.onCompletion = function() {
				var code = document.getElementById('muds_script'+rand);
				eval(code.innerHTML);
			}
		}
		
		muds_sack_updated.runAJAX();
		
		return true;

	} // end of JavaScript function muds_update
	
	function muds_search() {
		jQuery("body").append("<div id='TB_load'><img src='"+ mudslide_url +"/wp-includes/js/thickbox/loadingAnimation.gif' /></div>");
		jQuery('#TB_load').show();
	}
