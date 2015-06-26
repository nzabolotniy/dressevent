<?php
	
	//Local URL
	$url = get_bloginfo( 'wpurl' );
	$local_url = parse_url( $url );
	$aux_url   = parse_url(wp_guess_url());
	$url = str_replace($local_url['host'], $aux_url['host'], $url);
	
	$nonce = wp_create_nonce('mudslide');
	$user_picasa = mudsPicasa_get_user(); 
	$user_flickr = mudsFlickr_get_user();
	$last_source = $options['last_source'];
	if(strlen($last_source)==0) $last_source=MUDS_TYPE_PICASA;
	
	switch($last_source) {
		case MUDS_TYPE_PICASA:
			$user_name = $user_picasa;
			break;
		case MUDS_TYPE_FLICKR:
			$user_name = $user_flickr;
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
	
	?>
<script language="javascript" type="text/javascript">
	/* <![CDATA[ */
		
		var user_picasa = '<?php echo $user_picasa; ?>';
		var user_flickr = '<?php echo $user_flickr; ?>';
		var last_source = <?php echo $last_source; ?>;
		
		var loading_img = new Image(); 
		loading_img.src = 'loading.gif';
		
		var muds_data = new sack('<?php echo $url; ?>/wp-admin/admin-ajax.php' );
		
		function mudslide_changeSource(new_source) {
			last_source = new_source;
			var sourcetype = document.getElementsByName('sourcetype')[0];
			
			var list = new Array("Picasa", "Flickr");
			sourcetype.length=0;
			for(var i in list) {
				val = (i*1)+1;
				if(val==last_source) {
					sourcetype.options[i]=new Option(list[i], val, true, true);
				} else {
					sourcetype.options[i]=new Option(list[i], val, false, false);
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
			var user_name = document.getElementById('user_name');
			user_name.value=user;
		}

		var funcCheck = function() { 
			var data=muds_data.response;
			
			var aux = document.getElementById('Throbber_user');
			aux.setAttribute('class', 'off');
			aux.setAttribute('className', 'off'); //IE sucks
			
			switch(last_source) {
				case <?php echo MUDS_TYPE_PICASA; ?>:
					user_picasa=data;
					break
				case <?php echo MUDS_TYPE_FLICKR; ?>:
					user_flickr=data;
					break
			}
			mudslide_changeUser(data);
			mudslide_UpdateUser(data,false);
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
			
			var gallerytag = document.getElementById('gallerytag');
			gallerytag.length=0;
			gallerytag.options[0]=new Option('<?php _e("Select gallery", 'mudslide'); ?>', 0, false, false);
			
			var albums = xmldoc.getElementsByTagName('album');
			for (i = 0; i < albums.length; i++) {
				var id = albums[i].childNodes[0].firstChild.nodeValue;
				var name = albums[i].childNodes[1].firstChild.nodeValue;
				gallerytag.options[i+1]=new Option(name, id, false, false)
			}
			
			var aux = document.getElementById('Throbber_user');
			aux.setAttribute('class', 'off');
			aux.setAttribute('className', 'off'); //IE sucks
		}
		
		function mudslide_CheckUser(user,update) {
			var aux = document.getElementById('Throbber_user');
			aux.setAttribute('class', 'on');
			aux.setAttribute('className', 'on'); //IE sucks
			
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
			var aux = document.getElementById('Throbber_user');
			aux.setAttribute('class', 'on');
			aux.setAttribute('className', 'on'); //IE sucks
			
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
<div class="wrap">
	<div id="icon-tools" class="icon32"><br /></div>
	<h2><?php _e( 'Add Gallery', 'mudslide' );?></h2>
	<form name="form1" method="post" action="<?php echo add_query_arg(array('mode'=>'add_x')); ?>">
	<table class="form-table">
	<tr valign="top">
		<td width='50px'><label for="sourcetype"><?php _e("Source", 'mudslide'); ?></label></td>
		<td><select id="sourcetype" name="sourcetype" onchange="mudslide_changeSource(this.value);">
				<option value="<?php echo MUDS_TYPE_PICASA; ?>"<?php if($last_source==MUDS_TYPE_PICASA) echo "selected"; ?>>Picasa</option>
				<option value="<?php echo MUDS_TYPE_FLICKR; ?>"<?php if($last_source==MUDS_TYPE_FLICKR) echo "selected"; ?>>Flickr</option>
			</select>
		</td>
	</tr>
	<tr>
		<td><label for="gallerytag"><?php _e("User", 'mudslide'); ?></label></td>
		<td><input type="text" name="user_name" value="<?php echo $user_name; ?>" id="user_name" onkeypress="
		if(window.event)
			key = window.event.keyCode; //IE
		else
			key = event.keyCode;
		if(key==13) {
			var gallerytag = document.getElementById('gallerytag');
			gallerytag.focus();
			return false;
		}" onchange="mudslide_CheckUser(this.value,false);"> <a href="http://wordpress.org/extend/plugins/mudslideshow/faq/" target="_BLANK"><img src="<?php echo muds_plugin_url('/img/question.png'); ?>" title="<?php _e('When in doubt, use your email as username.', 'mudslide'); ?>" border="0"></a></td>
	</tr>
	<tr>
		<td><label for="gallerytag"><?php _e("Gallery", 'mudslide'); ?></label></td>
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
			</select> <a onclick="mudslide_CheckUser(document.getElementById('user_name').value,true);"><img id="Throbber_user" class="off" src="<?php echo muds_plugin_url('/img/th-mask.gif'); ?>" border="0"></a></td>
	</tr>
	<tr>
		<td colspan='2'><input type="submit" name="add_x" value="<?php _e('Add Gallery', 'mudslide'); ?>" class="button-primary apply" />
		<input type="submit" name="cancel" value="<?php _e('Cancel', 'mudslide'); ?>" class="button-secondary apply" style="align: right;" /></td>
	</tr>
</table>
</form>
