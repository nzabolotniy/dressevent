jQuery(document).ready(function(){	
	jQuery( ".fgallery_list" ).sortable({ 
		items: 'li',
		cursor: 'move', 
		forcePlaceholderSize: true,
		update: function(event, ui) {
			album = jQuery(this).attr('id');
			sort_url = jQuery('.fgallery_list a').attr('href');
			jQuery('.fgallery_list li').each(function(index){
				img_id = jQuery(this).attr('id');
				img_id = img_id.replace("image_", "");
				jQuery.ajax({
					url : sort_url,
					type: "GET",
					data: ({id : img_id, gall_id : album, order: index})
				});
			});
		}
	});
	jQuery('.image_remove').click(function(e){	
		rel = jQuery(this).attr('rel');
		if(confirm('Are you sure you want to remove this item?')) {
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
	jQuery('.image_cover').click(function(e){	
		rel = jQuery(this).attr('rel');
			jQuery.ajax({
				url     : jQuery(this).attr('href'),
				success : function(){
					id = '#'+rel;
					jQuery('.fgallery_list').find('.album_cover').removeClass('album_cover');
					jQuery(id).addClass('album_cover');
				}
			});
		e.preventDefault();
	});
	jQuery('.save_album_image').live('click',function(e) {
		e.preventDefault();
		object = jQuery(this);
		var id = jQuery(this).attr('rel');
		var edit_url = jQuery(this).attr('href');
		caption = jQuery(this).parent().find('#fgalleryImageCaption_'+id).val();
		description = jQuery(this).parent().find('#fgalleryImageDescription_'+id).val();
		new_url = jQuery(this).parent().find('#fgalleryImageURL_'+id).val();
		album = jQuery(this).parent().find('#fgalleryImageGall_'+id).val();
		token = jQuery(this).parent().find('#fgallery_edit_image_field_'+id).val();
			jQuery.ajax({
				url : edit_url,
				type : "POST",
				data : ({img_id : id, img_caption: caption, img_description: description, img_url : new_url, gall_id : album, nonce : token}),
				success: function() {
					object.parent().css('backgroundColor', '#f00');
					object.parent().animate({ backgroundColor: "#ffffff" }, "8000")
				}
			});
	});
	
});