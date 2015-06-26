<?php

if ( !defined('ABSPATH') )
    die('You are not allowed to call this page directly.');
    
global $wpdb;

$options = get_option('muds_options');

@header('Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>MudSlideShow</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/mctabs.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo muds_plugin_url('/tinymce/mudslide.js'); ?>"></script>
	<?php 
	// Declare we use JavaScript SACK library for Ajax
	wp_print_scripts( array( 'sack' ));
	
	wp_head(); 
	
	$user_picasa = mudsPicasa_get_user(); 
	$user_flickr = mudsFlickr_get_user();
	$last_source = $options['last_source'];
	if(strlen($last_source)==0) $last_source=MUDS_TYPE_PICASA;
	
	$size_flickr = array("s", "t", "m", "l" );
	$size_picasa = array("64c", "64", "72c", "72", "144c", "144", "160c", "160", "200", "288", "320", "400", "512", "576", "640", "720", "800", "912","1024", "1152", "1280", "1440", "1660");
	
	$size_options="";
	$size_list = array();
	
	switch($last_source) {
		case MUDS_TYPE_PICASA:
			$user_name = $user_picasa;
			$size_list = $size_picasa;
			foreach($size_picasa as $size) {
				$size_options.="<option value='$size'>$size</option>";
			}
			break;
		case MUDS_TYPE_FLICKR:
			$user_name = $user_flickr;
			$size_list = $size_flickr;
			foreach($size_flickr as $size) {
				$size_options.="<option value='$size'>$size</option>";
			}
			break;
	}
	
	$gallerylist = null;
	if(strlen($user_name)>0) {
		switch($last_source) {
			case MUDS_TYPE_FLICKR:
				$gallerylist = mudsFlickr_list_user_galleries($user_name);
				break;
			case MUDS_TYPE_PICASA:
				$gallerylist = mudsPicasa_list_user_galleries($user_name);
				break;
		}
	}
	
	//Local URL
	$url = get_bloginfo( 'wpurl' );
	$local_url = parse_url( $url );
	$aux_url   = parse_url(wp_guess_url());
	$url = str_replace($local_url['host'], $aux_url['host'], $url);
	$th_editor = muds_plugin_url('img/th-editor.jpg');
	
	?>
	<script language="javascript" type="text/javascript">
		/* <![CDATA[ */
		
		var user_picasa = '<?php echo $user_picasa; ?>';
		var user_flickr = '<?php echo $user_flickr; ?>';
		var last_source = <?php echo $last_source; ?>;
		
		var photos1 = false;
		var updatePhoto1 = false;
		var photos2 = false;
		var updatePhoto2 = false;
		
		var loading_img = new Image(); 
		loading_img.src = '<?php echo muds_plugin_url('img/th-loading.gif'); ?>';
		
		var muds_data = new sack('<?php echo $url; ?>/wp-admin/admin-ajax.php' );
		
		var size_flickr = new Array("<?php echo implode('","', $size_flickr); ?>");
		var size_picasa = new Array("<?php echo implode('","', $size_picasa); ?>");
		
		var size_list = new Array();
		
		switch(last_source) {
			case 1: //Picasa
				size_list = size_picasa;
				break;
			case 2: //Flickr
				size_list = size_flickr;
				break;
		}
	
		function mudslide_updatePhoto() {
			if(updatePhoto1) {
				var select = document.getElementById('singlepictag');
				var th = photos1[select.selectedIndex].childNodes[3].firstChild.nodeValue;
				th = th.replace('%size%',size_list[0]);
				th = th.replace('s64c','s64-c');
				document.getElementById('th-editor-1').src = th;
			}
			
			if(updatePhoto2) {
				var select = document.getElementById('featurepictag');
				var th = photos2[select.selectedIndex].childNodes[3].firstChild.nodeValue;
				th = th.replace('%size%',size_list[0]);
				th = th.replace('s64c','s64-c');
				document.getElementById('th-editor-2').src = th;
			}
			
			setTimeout(function() { mudslide_updatePhoto(); }, 250);
		}
		
		function mudslide_changeSource(new_source) {
			updatePhoto1 = false;
			updatePhoto2 = false;
			document.getElementById('th-editor-1').src = '<?php echo $th_editor; ?>';
			document.getElementById('th-editor-2').src = '<?php echo $th_editor; ?>';
			
			last_source = new_source;
			var sourcetype1 = document.getElementsByName('sourcetype1')[0];
			var sourcetype2 = document.getElementsByName('sourcetype2')[0];
			var sourcetype3 = document.getElementsByName('sourcetype3')[0];
			var sourcetype4 = document.getElementsByName('sourcetype4')[0];
			var simplesize = document.getElementById('simplesize');
			var imgsize = document.getElementById('imgsize');
			
			switch(last_source) {
				case '1': //Picasa
					size_list = size_picasa;
					break;
				case '2': //Flickr
					size_list = size_flickr;
					break;
			}
			
			simplesize.length=0;
			imgsize.length=0;
			for(var j in size_list) {
				simplesize.options[j]=new Option(size_list[j], size_list[j], false, false);
				imgsize.options[j]=new Option(size_list[j], size_list[j], false, false);
			}
			
			var list = new Array("Picasa", "Flickr");
			sourcetype1.length=0;
			sourcetype2.length=0;
			sourcetype3.length=0;
			for(var i in list) {
				val = (i*1)+1;
				if(val==last_source) {
					sourcetype1.options[i]=new Option(list[i], val, true, true);
					sourcetype2.options[i]=new Option(list[i], val, true, true);
					sourcetype3.options[i]=new Option(list[i], val, true, true);
					sourcetype4.options[i]=new Option(list[i], val, true, true);
				} else {
					sourcetype1.options[i]=new Option(list[i], val, false, false);
					sourcetype2.options[i]=new Option(list[i], val, false, false);
					sourcetype3.options[i]=new Option(list[i], val, false, false);
					sourcetype4.options[i]=new Option(list[i], val, false, false);
				}
			}			
			
			switch(new_source) {
					case '<?php echo MUDS_TYPE_PICASA; ?>':
						last_source = <?php echo MUDS_TYPE_PICASA; ?>;
						mudslide_changeUser(user_picasa);
						mudslide_UpdateUser(user_picasa,false);
						break;
					case '<?php echo MUDS_TYPE_FLICKR; ?>':
						last_source = <?php echo MUDS_TYPE_FLICKR; ?>;
						mudslide_changeUser(user_flickr);
						mudslide_UpdateUser(user_flickr,false);
						break;
					}
		} 
		
		function mudslide_changeUser(user) {
			var user_name = document.getElementById('user_name1');
			user_name.value=user;
			
			user_name = document.getElementById('user_name2');
			user_name.value=user;
			
			user_name = document.getElementById('user_name3');
			user_name.value=user;
			
			user_name = document.getElementById('user_name4');
			user_name.value=user;
		}

		var funcCheck = function() { 
			var data=muds_data.response;
			
			mudslide_changeUser(data);
			mudslide_UpdateUser(data,false);
			
			switch(last_source) {
				case <?php echo MUDS_TYPE_PICASA; ?>:
					user_picasa=data;
					break
				case <?php echo MUDS_TYPE_FLICKR; ?>:
					user_flickr=data;
					break
			}
		}
		
		var funcUser = function() {
			var data=muds_data.response;

			try { //IE
				xmldoc = new ActiveXObject("Microsoft.XMLDOM");
				xmldoc.async = "false";
				xmldoc.loadXML(data);
			} catch(e) {
				parser=new DOMParser();
				var xmldoc=parser.parseFromString(data,"text/xml");
			}

			var gallerypictag = document.getElementById('gallerypictag');
			gallerypictag.length=0;
			gallerypictag.options[0]=new Option('<?php _e("Select gallery", 'mudslide'); ?>', 0, false, false);

			var singlepictag = document.getElementById('singlepictag');
			singlepictag.length=0;
			singlepictag.options[0]=new Option('<?php _e("Select gallery first", 'mudslide'); ?>', 0, false, false);

			var gallerytag = document.getElementById('gallerytag');
			gallerytag.length=0;
			gallerytag.options[0]=new Option('<?php _e("Select gallery", 'mudslide'); ?>', 0, false, false);
			
			var simplegallerytag = document.getElementById('simplegallerytag');
			simplegallerytag.length=0;
			simplegallerytag.options[0]=new Option('<?php _e("Select gallery", 'mudslide'); ?>', 0, false, false);
			
			var galleryfeaturepictag = document.getElementById('galleryfeaturepictag');
			galleryfeaturepictag.length=0;
			galleryfeaturepictag.options[0]=new Option('<?php _e("Select gallery", 'mudslide'); ?>', 0, false, false);

			var featurepictag = document.getElementById('featurepictag');
			featurepictag.length=0;
			featurepictag.options[0]=new Option('<?php _e("Select gallery first", 'mudslide'); ?>', 0, false, false);
			
			var albums = xmldoc.getElementsByTagName('album');
			for (i = 0; i < albums.length; i++) {
				var id = albums[i].childNodes[0].firstChild.nodeValue;
				var name = albums[i].childNodes[1].firstChild.nodeValue;
				gallerytag.options[i+1]=new Option(name, id, false, false)
				gallerypictag.options[i+1]=new Option(name, id, false, false)
				simplegallerytag.options[i+1]=new Option(name, id, false, false)
				featurepictag.options[i+1]=new Option(name, id, false, false)
				galleryfeaturepictag.options[i+1]=new Option(name, id, false, false)
			}
			
			var aux = document.getElementById('Throbber_user1');
			aux.setAttribute('class', 'off');
			aux.setAttribute('className', 'off'); //IE sucks
			aux = document.getElementById('Throbber_user2');
			aux.setAttribute('class', 'off');
			aux.setAttribute('className', 'off'); //IE sucks
			aux = document.getElementById('Throbber_user3');
			aux.setAttribute('class', 'off');
			aux.setAttribute('className', 'off'); //IE sucks
			aux = document.getElementById('Throbber_user4');
			aux.setAttribute('class', 'off');
			aux.setAttribute('className', 'off'); //IE sucks
		}

		var funcAlbum = function() { 
			var data=muds_data.response;

			try { //IE
				xmldoc = new ActiveXObject("Microsoft.XMLDOM");
				xmldoc.async = "false";
				xmldoc.loadXML(data);
			} catch(e) {
				parser=new DOMParser();
				var xmldoc=parser.parseFromString(data,"text/xml");
			}

			var singlepictag = document.getElementById('singlepictag');
			singlepictag.length=0;
			photos1 = xmldoc.getElementsByTagName('photo');
			for (i = 0; i < photos1.length; i++) {
				var name = photos1[i].childNodes[0].firstChild.nodeValue;
				singlepictag.options[i]=new Option(name, i, false, false)
			}
			
			var aux = document.getElementById('Throbber_album3');
			aux.setAttribute('class', 'off');
			aux.setAttribute('className', 'off'); //IE sucks
			
			updatePhoto1 = true;
			
		}
		
		var funcAlbumFeature = function() { 
			var data=muds_data.response;

			try { //IE
				xmldoc = new ActiveXObject("Microsoft.XMLDOM");
				xmldoc.async = "false";
				xmldoc.loadXML(data);
			} catch(e) {
				parser=new DOMParser();
				var xmldoc=parser.parseFromString(data,"text/xml");
			}

			var featurepictag = document.getElementById('featurepictag');
			featurepictag.length=0;
			photos2 = xmldoc.getElementsByTagName('photo');
			for (i = 0; i < photos2.length; i++) {
				var name = photos2[i].childNodes[0].firstChild.nodeValue;
				featurepictag.options[i]=new Option(name, i, false, false)
			}
			
			var aux = document.getElementById('Throbber_album4');
			aux.setAttribute('class', 'off');
			aux.setAttribute('className', 'off'); //IE sucks
			
			updatePhoto2 = true;
		}
		
		
		function mudslide_CheckUser(user,update) {
			if(update) update=1;
			else update=0;
			
			muds_data.reset();
			
			//Our plugin sack configuration
			muds_data.execute = 0;
			muds_data.method = 'POST';
			muds_data.setVar( 'action', 'muds_ajax_data' );
			//muds_data.element = 'mss'+rand;
			
			if(last_source==<?php echo MUDS_TYPE_PICASA; ?>) type='picasa';
			else type='flickr'; 
			
			muds_data.setVar( 'type', type );
			muds_data.setVar( 'kind', 'check' );
			muds_data.setVar( 'update', update );
			muds_data.setVar( 'user', user );
			
			muds_data.onCompletion = funcCheck;
			
			muds_data.runAJAX();
			
			return true;
		}
				
		function mudslide_UpdateUser(user,update) {
			if(update) update=1;
			else update=0;
			
			muds_data.reset();
			
			//Our plugin sack configuration
			muds_data.execute = 0;
			muds_data.method = 'POST';
			muds_data.setVar( 'action', 'muds_ajax_data' );
			//muds_data.element = 'mss'+rand;
			
			if(last_source==<?php echo MUDS_TYPE_PICASA; ?>) type='picasa';
			else type='flickr';
			
			muds_data.setVar( 'type', type );
			muds_data.setVar( 'kind', 'galleries' );
			muds_data.setVar( 'update', update );
			muds_data.setVar( 'user', user );
			
			muds_data.onCompletion = funcUser;
			
			muds_data.runAJAX();
			
			return true;
		}
				
		function mudslide_UpdateAlbum(user,gallery,update) {
			updatePhoto1 = false;
			if(update) update=1;
			else update=0;
			
			muds_data.reset();
			
			//Our plugin sack configuration
			muds_data.execute = 0;
			muds_data.method = 'POST';
			muds_data.setVar( 'action', 'muds_ajax_data' );
			//muds_data.element = 'mss'+rand;
			
			if(last_source==<?php echo MUDS_TYPE_PICASA; ?>) type='picasa';
			else type='flickr';
			
			muds_data.setVar( 'type', type );
			muds_data.setVar( 'kind', 'gallery' );
			muds_data.setVar( 'update', update );
			muds_data.setVar( 'gallery', gallery );
			muds_data.setVar( 'user', user );
			
			muds_data.onCompletion = funcAlbum;
			
			muds_data.runAJAX();
			
			return true;
		}
		
		function mudslide_UpdateFeatureAlbum(user,gallery,update) {
			updatePhoto2 = false;
			if(update) update=1;
			else update=0;
			
			muds_data.reset();
			
			//Our plugin sack configuration
			muds_data.execute = 0;
			muds_data.method = 'POST';
			muds_data.setVar( 'action', 'muds_ajax_data' );
			//muds_data.element = 'mss'+rand;
			
			if(last_source==<?php echo MUDS_TYPE_PICASA; ?>) type='picasa';
			else type='flickr';
			
			muds_data.setVar( 'type', type );
			muds_data.setVar( 'kind', 'gallery' );
			muds_data.setVar( 'update', update );
			muds_data.setVar( 'gallery', gallery );
			muds_data.setVar( 'user', user );
			
			muds_data.onCompletion = funcAlbumFeature;
			
			muds_data.runAJAX();
			
			return true;
		}
	/* ]]> */		
	</script>
	<style type="text/css">

		img.on {
		height: 20px;
		background: url('<?php echo muds_plugin_url('/img/th-loading.gif'); ?>') center center;
		cursor: pointer;
		vertical-align: middle;
	}


	img.off {
		height: 20px;
		background: url('<?php echo muds_plugin_url('/img/th-update.gif'); ?>') center center;
		cursor: pointer;
		vertical-align: middle;
	}
		
	</style>
	<base target="_self" />
</head>
<body id="link" onload="tinyMCEPopup.executeOnLoad('init();');document.body.style.display='';document.getElementById('gallerytag').focus(); mudslide_updatePhoto();" style="display: none">
<!-- <form onsubmit="insertLink();return false;" action="#"> -->
	<form name="MudSlide" action="#">
	<div class="tabs">
		<ul>
			<li id="gallery_tab" class="current"><span><a href="javascript:mcTabs.displayTab('gallery_tab','gallery_panel');" onmousedown="return false;"><?php _e("Gallery", 'mudslide'); ?></a></span></li>
			<li id="simplegallery_tab"><span><a href="javascript:mcTabs.displayTab('simplegallery_tab','simplegallery_panel');" onmousedown="return false;"><?php _e("Simple Gallery", 'mudslide'); ?></a></span></li>
			<li id="singlepic_tab"><span><a href="javascript:mcTabs.displayTab('singlepic_tab','singlepic_panel');" onmousedown="return false;"><?php _e("Picture", 'mudslide'); ?></a></span></li>
			<?php if($options['feature']) { ?><li id="featurepic_tab"><span><a href="javascript:mcTabs.displayTab('featurepic_tab','featurepic_panel');" onmousedown="return false;"><?php _e("Feature Picture", 'mudslide'); ?></a></span></li><?php } ?>
		</ul>
	</div>
	
	<div class="panel_wrapper" style="height: 170px;">
		<!-- gallery panel -->
		<div id="gallery_panel" class="panel current">
		<br />
		<table border="0" cellpadding="4" cellspacing="0">
			<tr>
				<td nowrap="nowrap"><label for="sourcetype1"><?php _e("Source", 'mudslide'); ?></label></td>
				<td><select id="sourcetype1" name="sourcetype1" onchange="
				var aux = document.getElementById('Throbber_user1');
				aux.setAttribute('class', 'on');
				aux.setAttribute('className', 'on'); //IE sucks
				mudslide_changeSource(this.value);">
						<option value="<?php echo MUDS_TYPE_PICASA; ?>"<?php if($last_source==MUDS_TYPE_PICASA) echo "selected"; ?>>Picasa</option>
						<option value="<?php echo MUDS_TYPE_FLICKR; ?>"<?php if($last_source==MUDS_TYPE_FLICKR) echo "selected"; ?>>Flickr</option>
					</select></td>
			</tr>
			<tr>
				<td nowrap="nowrap"><label for="gallerytag"><?php _e("User", 'mudslide'); ?></label></td>
				<td><input type="text" value="<?php echo $user_name; ?>" id="user_name1" onkeypress="
				if(window.event)
					key = window.event.keyCode; //IE
				else
					key = event.keyCode;
				if(key==13) {
					var gallerytag = document.getElementById('gallerytag');
					gallerytag.focus();
					return false;
				}" onchange="
				var aux = document.getElementById('Throbber_user1');
				aux.setAttribute('class', 'on');
				aux.setAttribute('className', 'on'); //IE sucks
				mudslide_CheckUser(this.value,false);"> <a href="http://wordpress.org/extend/plugins/mudslideshow/faq/" target="_BLANK"><img src="<?php echo muds_plugin_url('/img/question.png'); ?>" title="<?php _e('When in doubt, use your email as username.', 'mudslide'); ?>" border="0"></a></td>
			</tr>
			<tr valign="middle">
				<td nowrap="nowrap"><label for="gallerytag"><?php _e("Gallery", 'mudslide'); ?></label></td>
				<td><select id="gallerytag" name="gallerytag" style="width: 200px">
						<option value="0"><?php _e("Select gallery", 'mudslide'); ?></option>
						<?php
							if(strlen($user_name)>0) {
								if(is_array($gallerylist)) {
									foreach($gallerylist as $gallery) {
										echo '<option value="'.$gallery['id'].'" >'.$gallery['title'].'</option>'."\n";
									}
								}
							}
						?>
					</select> <a onclick="
					var aux = document.getElementById('Throbber_user1');
				aux.setAttribute('class', 'on');
				aux.setAttribute('className', 'on'); //IE sucks
				mudslide_CheckUser(document.getElementById('user_name1').value,true);"><img id="Throbber_user1" class="off" src="<?php echo muds_plugin_url('/img/th-mask.gif'); ?>" border="0"></a></td>
			</tr>
		</table>
		<table border="0" cellpadding="4" cellspacing="0">
			<tr>
				<td><input type="checkbox" id="gallery_opt1" name="gallery_opt1" /> <label for="gallery_opt1"><?php _e('Show description', 'mudslide'); ?> [<a href='http://wordpress.org/extend/plugins/mudslideshow/faq/'>?</a>]</label></td>
				<td><input type="checkbox" id="gallery_opt2" name="gallery_opt2" /> <label for="gallery_opt2"><?php _e('Reverse order', 'mudslide'); ?></label></td>
			</tr>
		</table>
		</div>
		<!-- gallery panel -->
						
		<!-- mudsimple panel -->
		<div id="simplegallery_panel" class="panel">
			<br />
			<table border="0" cellpadding="4" cellspacing="0">
				<tr>
				<td nowrap="nowrap"><label for="sourcetype2"><?php _e("Source", 'mudslide'); ?></label></td>
				<td colspan='3'><select id="sourcetype2" name="sourcetype2" onchange="
				var aux = document.getElementById('Throbber_user2');
				aux.setAttribute('class', 'on');
				aux.setAttribute('className', 'on'); //IE sucks
				mudslide_changeSource(this.value);">
						<option value="<?php echo MUDS_TYPE_PICASA; ?>"<?php if($last_source==MUDS_TYPE_PICASA) echo "selected"; ?>>Picasa</option>
						<option value="<?php echo MUDS_TYPE_FLICKR; ?>"<?php if($last_source==MUDS_TYPE_FLICKR) echo "selected"; ?>>Flickr</option>
					</select></td>
			</tr>
				<tr>
					<td nowrap="nowrap"><label for="simplegallerytag"><?php _e("User", 'mudslide'); ?></label></td>
					<td colspan='3'><input type="text" value="<?php echo $user_name; ?>" id="user_name2" onkeypress="
					if(window.event)
						key = window.event.keyCode; //IE
					else
						key = event.keyCode;
					if(key==13) {
						var simplegallerytag = document.getElementById('simplegallerytag');
						simplegallerytag.focus();
						return false;
					}" onchange="
					var aux = document.getElementById('Throbber_user2');
					aux.setAttribute('class', 'on');
					aux.setAttribute('className', 'on'); //IE sucks
					mudslide_CheckUser(this.value,false);"> <a href="http://wordpress.org/extend/plugins/mudslideshow/faq/" target="_BLANK"><img src="<?php echo muds_plugin_url('/img/question.png'); ?>" title="<?php _e('When in doubt, use your email as username.', 'mudslide'); ?>" border="0"></a></td>
				</tr>
				<tr valign="middle">
					<td nowrap="nowrap"><label for="simplegallerytag"><?php _e("Gallery", 'mudslide'); ?></label></td>
					<td colspan='3'><select id="simplegallerytag" name="simplegallerytag" style="width: 200px">
							<option value="0"><?php _e("Select gallery", 'mudslide'); ?></option>
							<?php
								if(strlen($user_name)>0) {
									if(is_array($gallerylist)) {
										foreach($gallerylist as $gallery) {
											echo '<option value="'.$gallery['id'].'" >'.$gallery['title'].'</option>'."\n";
										}
									}
								}
							?>
						</select> <a src="update.png" style="cursor : pointer;" onclick="
							var aux = document.getElementById('Throbber_user2');
					aux.setAttribute('class', 'on');
					aux.setAttribute('className', 'on'); //IE sucks
					mudslide_CheckUser(document.getElementById('user_name2').value,true);"><img border="0" id="Throbber_user2" class="off" src="<?php echo muds_plugin_url('/img/th-mask.gif'); ?>"></a></td>
				</tr>
			</table>
			<table border="0" cellpadding="4" cellspacing="0">
				<tr>
					<td nowrap="nowrap" valign="top"><?php _e("Size", 'mudslide'); ?></td>
					<td>
						<label>
							<select id="simplesize" name="simplesize"><?php echo $size_options; ?></select>
						</label>
					</td>
					<td nowrap="nowrap" valign="top"><?php _e("Float", 'mudslide'); ?></td>
					<td>
						<label><select id="simplefloat" name="simplefloat">
							<option value="left"><?php _e("Left", 'mudslide'); ?></option>
							<option value="right"><?php _e("Right", 'mudslide'); ?></option>
							<option value="center" SELECTED><?php _e("Center", 'mudslide'); ?></option>
						</select></label>
					</td>
				</tr>
			</table>
			<table border="0" cellpadding="4" cellspacing="0">
			<tr>
				<td><input type="checkbox" id="simple_opt1" name="simple_opt1" /> <label for="simple_opt1"><?php _e('Show description', 'mudslide'); ?> [<a href='http://wordpress.org/extend/plugins/mudslideshow/faq/'>?</a>]</label></td>
				<td><input type="checkbox" id="simple_opt2" name="simple_opt2" /> <label for="simple_opt2"><?php _e('Reverse order', 'mudslide'); ?></label></td>
			</tr>
		</table>
		</div>
		<!-- mudsimple panel -->
		
		<!-- single pic panel -->
		<div id="singlepic_panel" class="panel">
		<br />
		<table border="0" cellpadding="4" cellspacing="0">
			<tr>
				<td nowrap="nowrap"><label for="sourcetype3"><?php _e("Source", 'mudslide'); ?></label></td>
				<td colspan='3'><select id="sourcetype3" name="sourcetype3" onchange="
					updatePhoto1 = false;
					var aux = document.getElementById('Throbber_user3');
					aux.setAttribute('class', 'on');
					aux.setAttribute('className', 'on'); //IE sucks
					mudslide_changeSource(this.value);">
						<option value="<?php echo MUDS_TYPE_PICASA; ?>"<?php if($last_source==MUDS_TYPE_PICASA) echo "selected"; ?>>Picasa</option>
						<option value="<?php echo MUDS_TYPE_FLICKR; ?>"<?php if($last_source==MUDS_TYPE_FLICKR) echo "selected"; ?>>Flickr</option>
					</select></td>
			</tr>
			<tr>
				<td nowrap="nowrap"><label for="gallerytag"><?php _e("User", 'mudslide'); ?></label></td>
				<td colspan="3"><input type="text" value="<?php echo $user_name; ?>" id="user_name3" onkeypress="
				if(window.event)
					key = window.event.keyCode; //IE
				else
					key = event.keyCode;
				if(key==13) {
					var gallerypictag = document.getElementById('gallerypictag');
					gallerypictag.focus();
					return false;
				}" onchange="
					updatePhoto1 = false;
					var aux = document.getElementById('Throbber_user3');
					aux.setAttribute('class', 'on');
					aux.setAttribute('className', 'on'); //IE sucks
					mudslide_CheckUser(this.value,false);"> <a href="http://wordpress.org/extend/plugins/mudslideshow/faq/" target="_BLANK"><img src="<?php echo muds_plugin_url('/img/question.png'); ?>" title="<?php _e('When in doubt, use your email as username.', 'mudslide'); ?>" border="0"></a></td>
					<td rowspan="3" align="right"><img id='th-editor-1' width='64' src='<?php echo $th_editor; ?>'></td>
			</tr>
			<tr>
				<td nowrap="nowrap"><label for="gallerypictag"><?php _e("Gallery", 'mudslide'); ?></label></td>
				<td colspan='3'><select id="gallerypictag" name="gallerypictag" style="width: 190px" onchange="
					updatePhoto1 = false;
					var aux = document.getElementById('Throbber_album3');
					aux.setAttribute('class', 'on');
					aux.setAttribute('className', 'on'); //IE sucks
					mudslide_UpdateAlbum(document.getElementById('user_name3').value,this.value,false);">
						<option value="0"><?php _e("Select gallery", 'mudslide'); ?></option>
						<?php
							if(strlen($user_name)>0) {
								if(is_array($gallerylist)) {
									foreach($gallerylist as $gallery) {
										echo '<option value="'.$gallery['id'].'" >'.$gallery['title'].'</option>'."\n";
									}
								}
							}
						?>
					</select> <a src="update.png" style="cursor : pointer;" onclick="
						var aux = document.getElementById('Throbber_user3');
						aux.setAttribute('class', 'on');
						aux.setAttribute('className', 'on'); //IE sucks
						mudslide_CheckUser(document.getElementById('user_name3').value,true);"><img border="0" id="Throbber_user3" class="off" src="<?php echo muds_plugin_url('/img/th-mask.gif'); ?>"></a></td>
			</tr>
			<tr>
				<td nowrap="nowrap"><label for="singlepictag"><?php _e("Picture", 'mudslide'); ?></label></td>
				<td colspan='3'><select id="singlepictag" name="singlepictag" style="width: 190px">
						<option value="0"><?php _e("Select gallery first", 'mudslide'); ?></option>
					</select> <a src="update.png" style="cursor : pointer;" onclick="
						var aux = document.getElementById('Throbber_album3');
						aux.setAttribute('class', 'on');
						aux.setAttribute('className', 'on'); //IE sucks
						mudslide_UpdateAlbum(document.getElementById('user_name3').value,document.getElementById('gallerypictag').value,true);"><img border="0" id="Throbber_album3" class="off" src="<?php echo muds_plugin_url('/img/th-mask.gif'); ?>"></a></td>
			</tr>
		</table>
		<table border="0" cellpadding="4" cellspacing="0">
			<tr>
				<td nowrap="nowrap" valign="top"><?php _e("Size", 'mudslide'); ?></td>
				<td>
					<label>
						<select id="imgsize" name="imgsize"><?php echo $size_options; ?></select>
					</label>
				</td>
				<td nowrap="nowrap" valign="top"><?php _e("Float", 'mudslide'); ?></td>
				<td>
					<label><select id="imgfloat" name="imgfloat">
						<option value="left"><?php _e("Left", 'mudslide'); ?></option>
						<option value="right"><?php _e("Right", 'mudslide'); ?></option>
						<option value="center" SELECTED><?php _e("Center", 'mudslide'); ?></option>
					</select></label>
				</td>
			</tr>
		</table>
		<table border="0" cellpadding="4" cellspacing="0">
			<tr>
				<td><input type="checkbox" id="image_opt1" name="image_opt1" /> <label for="image_opt1"><?php _e('Show description', 'mudslide'); ?> [<a href='http://wordpress.org/extend/plugins/mudslideshow/faq/'>?</a>]</label></td>
				<td colspan="2"></td>
			</tr>
		</table>
		</div>
		<!-- single pic panel -->		
		<?php if(!$options['feature']) $visibility = " style='visibility: hidden;'"; ?>
		<!-- feature pic panel -->
		<div id="featurepic_panel" class="panel"<?php echo $visibility; ?>>
		<br />
		<table border="0" cellpadding="3" cellspacing="0">
			<tr>
				<td nowrap="nowrap"><label for="sourcetype4"><?php _e("Source", 'mudslide'); ?></label></td>
				<td><select id="sourcetype4" name="sourcetype4" onchange="
					updatePhoto2 = false;
					var aux = document.getElementById('Throbber_user4');
					aux.setAttribute('class', 'on');
					aux.setAttribute('className', 'on'); //IE sucks
					mudslide_changeSource(this.value);">
						<option value="<?php echo MUDS_TYPE_PICASA; ?>"<?php if($last_source==MUDS_TYPE_PICASA) echo "selected"; ?>>Picasa</option>
						<option value="<?php echo MUDS_TYPE_FLICKR; ?>"<?php if($last_source==MUDS_TYPE_FLICKR) echo "selected"; ?>>Flickr</option>
					</select></td>
			</tr>
			<tr>
				<td nowrap="nowrap"><label for="gallerytag"><?php _e("User", 'mudslide'); ?></label></td>
				<td><input type="text" value="<?php echo $user_name; ?>" id="user_name4" onkeypress="
				if(window.event)
					key = window.event.keyCode; //IE
				else
					key = event.keyCode;
				if(key==13) {
					var galleryfeaturepictag = document.getElementById('galleryfeaturepictag');
					galleryfeaturepictag.focus();
					return false;
				}" onchange="
					updatePhoto2 = false;
					var aux = document.getElementById('Throbber_user4');
					aux.setAttribute('class', 'on');
					aux.setAttribute('className', 'on'); //IE sucks
					mudslide_CheckUser(this.value,false);"> <a href="http://wordpress.org/extend/plugins/mudslideshow/faq/" target="_BLANK"><img src="<?php echo muds_plugin_url('/img/question.png'); ?>" title="<?php _e('When in doubt, use your email as username.', 'mudslide'); ?>" border="0"></a></td>
					<td rowspan="3" align="right"><img id='th-editor-2' width='64' src='<?php echo $th_editor; ?>'></td>
			</tr>
			<tr>
				<td nowrap="nowrap"><label for="galleryfeaturepictag"><?php _e("Gallery", 'mudslide'); ?></label></td>
				<td><select id="galleryfeaturepictag" name="galleryfeaturepictag" style="width: 200px" onchange="
					updatePhoto2 = false;
					var aux = document.getElementById('Throbber_album4');
					aux.setAttribute('class', 'on');
					aux.setAttribute('className', 'on'); //IE sucks
					mudslide_UpdateFeatureAlbum(document.getElementById('user_name4').value,this.value,false);">
						<option value="0"><?php _e("Select gallery", 'mudslide'); ?></option>
						<?php
							if(strlen($user_name)>0) {
								if(is_array($gallerylist)) {
									foreach($gallerylist as $gallery) {
										echo '<option value="'.$gallery['id'].'" >'.$gallery['title'].'</option>'."\n";
									}
								}
							}
						?>
					</select> <a src="update.png" style="cursor : pointer;" onclick="
						var aux = document.getElementById('Throbber_user4');
						aux.setAttribute('class', 'on');
						aux.setAttribute('className', 'on'); //IE sucks
						mudslide_CheckUser(document.getElementById('user_name4').value,true);"><img border="0" id="Throbber_user4" class="off" src="<?php echo muds_plugin_url('/img/th-mask.gif'); ?>"></a></td>
			</tr>
			<tr>
				<td nowrap="nowrap"><label for="featurepictag"><?php _e("Picture", 'mudslide'); ?></label></td>
				<td><select id="featurepictag" name="featurepictag" style="width: 200px">
						<option value="0"><?php _e("Select gallery first", 'mudslide'); ?></option>
					</select> <a src="update.png" style="cursor : pointer;" onclick="
						var aux = document.getElementById('Throbber_album4');
						aux.setAttribute('class', 'on');
						aux.setAttribute('className', 'on'); //IE sucks
						mudslide_UpdateFeatureAlbum(document.getElementById('user_name4').value,document.getElementById('galleryfeaturepictag').value,true);"><img border="0" id="Throbber_album4" class="off" src="<?php echo muds_plugin_url('/img/th-mask.gif'); ?>"></a></td>
			</tr>
		</table>
		</div>
		<!-- feature pic panel -->		
		
	</div>

	<div class="mceActionPanel">
		<div style="float: left">
			<input type="button" id="cancel" name="cancel" value="<?php _e("Cancel", 'mudslide'); ?>" onclick="tinyMCEPopup.close();" />
		</div>

		<div style="float: right">
			<input type="submit" id="insert" name="insert" value="<?php _e("Insert", 'mudslide'); ?>" onclick="insertMudSlideLink();" />
		</div>
	</div>
</form>
</body>
</html>
