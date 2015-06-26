jQuery(document).ready(function() {

	jQuery("a.mssfb-image").fancybox({
		'padding'		:	4,
		'titlePosition':	'over',
		'transitionIn'	:	'fade',
		'transitionOut':	'fade',
		'speedIn'		:	400, 
		'speedOut'		:	200, 
		'overlayShow'	:	true,
		'centerOnScroll':	true,
		'onComplete'	:	function() {
			jQuery("#mssfb-caption").hide();
			 jQuery("#mssfb-title").show();
			 jQuery("#fancybox-title").hover(function() {
				 jQuery("#mssfb-caption").show();
				 jQuery("#mssfb-title").hide();
			}, function() {
				 jQuery("#mssfb-caption").hide();
				 jQuery("#mssfb-title").show();
			});
		}
		
	});
});
