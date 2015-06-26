jQuery(document).ready(function(){	
	jQuery('#grant-permissions').click(function() {
		window.open(this.href, 'fotobookAuth', 'width=500,height=300');
		jQuery(this).css('opacity', 0.5);
		window.onbeforeunload = function(e) {
			return ('If this page is refreshed or changed between granting and applying permissions the activation process will not work.');
		};
		return false;
	});
	jQuery('#request-permissions').click(function() {
		window.open(this.href, 'fotobookAuth', 'width=500,height=300');
		jQuery(this).css('opacity', 0.5);
		return false;
	});
	jQuery('#apply-permissions').submit(function() {
		jQuery(this).find('input[type="submit"]').css('opacity', 0.5);
		window.onbeforeunload = undefined;
	});

	var options1 = { 
		beforeSubmit: validateData,
        success:  cleanFields
    }; 

	var options = {
	    beforeSubmit: showLoader,
		success:  cleanFields
	}
	
	function showLoader() {
		jQuery('#upload_tabs').css('opacity','0.5');		
		jQuery(".ajax_loader").show();
	}
	
	function cleanFields(responseText, statusText, xhr, $form)  { 
		jQuery("#upload_tabs form")[0].reset();
		jQuery("#upload_tabs form")[1].reset();
		jQuery("#upload_tabs form")[2].reset();
		jQuery("#upload_tabs form")[3].reset();
		jQuery(".ajax_loader").hide();
		jQuery('#upload_tabs').css('opacity','1');
		jQuery('.input_fields').html(''); 
		alert(responseText);
	} 
	
	function validateData(formData, jqForm, options) {
		jQuery('#upload_tabs').css('opacity','0.5');		
		jQuery(".ajax_loader").show();
		var res = true;
		jQuery('#url .fgallery_url').each(function(){
		if(/^([a-z]([a-z]|\d|\+|-|\.)*):(\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?((\[(|(v[\da-f]{1,}\.(([a-z]|\d|-|\.|_|~)|[!\$&'\(\)\*\+,;=]|:)+))\])|((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=])*)(:\d*)?)(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*|(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)){0})(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(jQuery(this).val())) {
		  res = true;
		} else {
		  res = false;
		  alert(jQuery(this).val()+' is not valid url');
		  return false;
		}
	});
		return res;
	}
		
	jQuery("#upload_tabs").tabs();
	jQuery("#upload_tabs .upload").ajaxForm(options);
	//jQuery("#upload_tabs .facebook_confirm").ajaxForm();
	jQuery("#upload_tabs #url").ajaxForm(options1);
	jQuery('#second_tab a').click(function(e){
		jQuery('#uploadify').uploadifyClearQueue();
		e.preventDefault();
	});
	jQuery('#second_tab button').click(function(e){
		jQuery('#uploadify').uploadifySettings('scriptData', {'img_parent' : jQuery("#uploadify_img_folder").val()});
		jQuery('#uploadify').uploadifyUpload();
		e.preventDefault();
	});
	jQuery('.add_url').click(function(){
		id = jQuery('.fgallery_url').length;
		jQuery('.input_fields').append('<div id="fgallery_url_wrap_'+id+'" class="fgallery_url_wrap"><input type="text" name="fgallery_url[]" id="fgallery_url_'+id+'" class="fgallery_url" onchange="show_img_from_url(this.value,'+id+')" /><img src="" id="fgallery_img_'+id+'" width="100"/><a class="delete_url" onclick="delete_url('+id+')" title="Delete url"></a></div>');
	});
		
});

function delete_img(id) {
	href = jQuery('#image_'+id).attr('rel');
		if(confirm('Are you sure you want to delete this item?')) {
			jQuery.ajax({
				url     : href,
				success : function(){
					jQuery('.image_'+id).remove();
				}
			});
		}
}

function show_img_from_url(val,id) {
	jQuery('#fgallery_img_'+id).attr('src',val);
}

function delete_url(id) {
	jQuery('#fgallery_url_wrap_'+id).remove();
}

