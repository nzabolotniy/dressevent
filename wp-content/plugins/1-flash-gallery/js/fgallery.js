jQuery(document).ready(function(){
	jQuery('.fgallery_action.delete').click(function(e){	
		rel = jQuery(this).attr('rel');
		if(confirm('Are you sure you want to delete this item?')) {
			jQuery.ajax({
				url     : jQuery(this).attr('href'),
				success : function(){
					id = '#'+rel;
					jQuery(id).remove();
				}
			});
		}
		e.preventDefault();
	});
        jQuery('#images_sortby').change(function(){
            jQuery(this).parent().submit();
        });
		
	var ord = parseInt(jQuery('.fgallery_album').eq(0).find('.order').text());
		
	jQuery( ".widefat tbody" ).sortable({ 
		items: '.fgallery_album',
		cursor: 'move', 
		forcePlaceholderSize: true,
		update: function(event, ui) {
			album = jQuery(this).attr('id');
			sort_url = jQuery('.fgallery_sort').attr('href');
			jQuery('.fgallery_album').each(function(index){
				gall_id = jQuery(this).attr('id');
				gall_id = parseInt(gall_id.replace("album_", ""));
				neword = parseInt(index + ord);
				jQuery.ajax({
					url : sort_url,
					type: "GET",
					data: ({id : gall_id, order: neword})
				});
			});
		}
	});
		
    jQuery('thead, tfoot').find(':checkbox').click( function(e) {
		var c = jQuery(this).attr('checked'),
			kbtoggle = 'undefined' == typeof toggleWithKeyboard ? false : toggleWithKeyboard,
			toggle = e.shiftKey || kbtoggle;

		jQuery(this).closest( 'table' ).children( 'tbody' ).filter(':visible')
		.children().children('.check-column').find(':checkbox')
		.attr('checked', function() {
			if ( jQuery(this).closest('tr').is(':hidden') )
				return '';
			if ( toggle )
				return jQuery(this).attr( 'checked' ) ? '' : 'checked';
			else if (c)
				return 'checked';
			return '';
		});

		jQuery(this).closest('table').children('thead,  tfoot').filter(':visible')
		.children().children('.check-column').find(':checkbox')
		.attr('checked', function() {
			if ( toggle )
				return '';
			else if (c)
				return 'checked';
			return '';
		});
	});
});