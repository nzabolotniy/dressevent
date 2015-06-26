<?php 

$img_yes = muds_plugin_url('/img/yes.png');
$img_no = muds_plugin_url('/img/no.png');

$txt_yes = __('Yes', 'mudslide');
$txt_no = __('No', 'mudslide');

//Detect if we have the plugins required for each viewer
$lb_enabled = true;
$fb_enabled = true;
$hs_enabled = true;
$pf_enabled = false;
if(class_exists('WP_prettyPhoto')) $pf_enabled = true;

?><div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
	<h2><?php _e( 'MudSlideShow Options', 'mudslide' ); ?></h2>
	<form method="post" action="<?php echo remove_query_arg(array('mode', 'id')); ?>">
		<input type="hidden" name="muds_hidden_field" value="Y"><input type="hidden" name="mode_x" value="manage_x">
		<table class="form-table">
			<tr valign="top">
				<td width='50px'><?php _e("Columns", 'mudslide' ); ?>: </td>
				<td><select name="muds_columns">
						<option <?php if ($options['columns'] == 1) echo('selected'); ?>>1</option>
						<option <?php if ($options['columns'] == 2) echo('selected'); ?>>2</option>
						<option <?php if ($options['columns'] == 3) echo('selected'); ?>>3</option>
						<option <?php if ($options['columns'] == 4) echo('selected'); ?>>4</option>
						<option <?php if ($options['columns'] == 5) echo('selected'); ?>>5</option>
						<option <?php if ($options['columns'] == 6) echo('selected'); ?>>6</option>
						<option <?php if ($options['columns'] == 7) echo('selected'); ?>>7</option>
						<option <?php if ($options['columns'] == 8) echo('selected'); ?>>8</option>
						<option <?php if ($options['columns'] == 9) echo('selected'); ?>>9</option>
						<option <?php if ($options['columns'] == 10) echo('selected'); ?>>10</option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<td><?php _e("Thumbnail size", 'mudslide' ); ?>: </td>
				<td><input type="text" name="thmaxsize" size="5" value="<?php echo $options['thmaxsize']; ?>"></td>
			</tr>
			<tr valign="top">
				<td><?php _e("Thumbnail type", 'mudslide' ); ?>: </td>
				<td><select name="thsize">
					<option value="1"<?php if($options['thsize'] == 1) echo " selected=true"; ?>><?php _e('Squared', 'mudslide'); ?></option>
					<option value="2"<?php if($options['thsize'] == 2) echo " selected=true"; ?>><?php _e('Scaled', 'mudslide'); ?></option>
				</select> <?php _e('For squared thumbnails, size has to be lower or equal to 75 for Flickr, and 160 for Picasa.', 'mudslide'); ?>
				</td>
			</tr>
			<tr valign="top">
				<td><?php _e("Full size", 'mudslide' ); ?>: </td>
				<td><select name="full_size">
					<option value="0"<?php if($options['full_size'] == 0) echo " selected=true"; ?>><?php _e('800px (approx)', 'mudslide'); ?></option>
					<option value="1"<?php if($options['full_size'] == 1) echo " selected=true"; ?>><?php _e('Original', 'mudslide'); ?></option>
				</select>
				</td>
			</tr>
			<tr>
				<td><input type='checkbox' name='muds_showlink' <?php if($options['show_link']) echo " checked"; ?>></td>
				<td><?php _e("Show link to source gallery/photo", 'mudslide'); ?> </td>
			</tr>
			<tr>
				<td><input type='checkbox' name='muds_extend' <?php if($options['extend']) echo " checked"; ?>></td>
				<td><?php echo __("Use MudSlideShow to show images added with the Wordpress media manager? (Only the thumbnails linked to the original image)", 'mudslide')." -".__("This is an early stage feature, be careful.", 'mudslide')."-"; ?> </td>
			</tr>
			<tr>
				<td><input type='checkbox' name='muds_feature' <?php if($options['feature']) echo " checked"; ?>></td>
				<td><?php echo __("Import the feature picture and set it as thumbnail when save or modify a post/page without feature image.", 'mudslide'); ?></td>
			</tr>
			<tr>
				<td></td>
				<td><?php echo __("The order to select the feature picture is <ol style='font-size: 11px;'><li>First image described with the tag <strong>[mudthumb]</strong>.</li><li>First single image.</li><li>First image in first gallery.</li><li>First attached image.</li></ol>", 'mudslide'). __("To not asign automatically a thumbnail to a page/post just add the <a href='http://www.kriesi.at/archives/how-to-use-wordpress-custom-fields' target='_BLANK'>custom field</a> <strong>no_thumb</strong>.", 'mudslide'); ?></td>
			</tr>
			<tr>
				<td colspan="2">
					<table>
						<tr style="background-color: #8787ab; font-size:14px; font-weight:700;">
							<td></td>
							<td><?php _e('Viewer', 'mudslide'); ?></td>
							<td><?php _e('Enabled?', 'mudslide'); ?></td>
							<td><?php _e('Required plugin', 'mudslide'); ?></td>
							<td><?php _e('Free for<br>commercial sites?', 'mudslide'); ?></td>
						</tr>
						<tr>
							<td><input type="radio" name="muds_gallery_type" value="0" <?php if($options['gallery_type'] == 0) echo " checked"; ?>/></td>
							<td><?php _e("None", "mudslide"); ?></td>
							<td align="center"><img src="<?php echo $img_yes ?>" alt="<?php echo $txt_yes; ?>" /></td>
							<td><?php _e("None", "mudslide"); ?></td>
							<td align="center"><img src="<?php echo $img_yes ?>" alt="<?php echo $txt_yes; ?>" /></td>
						</tr>
						<tr>
							<td><input type="radio" name="muds_gallery_type" value="3"<?php if(!$lb_enabled) echo " disabled=\"true\""; if($options['gallery_type'] == 3) echo " checked"; ?>/></td>
							<td><a href="http://www.dolem.com/lytebox/" target="_BLANK">Lytebox</a></td>
							<td align="center"><img src="<?php if($lb_enabled) echo $img_yes; else echo $img_no; ?>" alt="<?php if($lb_enabled) echo $txt_yes; else echo $txt_no; ?>" /></td>
							<td><?php _e("None", "mudslide"); ?></td>
							<td align="center"><img src="<?php echo $img_yes ?>" alt="<?php echo $txt_yes; ?>" /></td>
						</tr>
						<tr>
							<td><input type="radio" name="muds_gallery_type" value="2"<?php if(!$fb_enabled) echo " disabled=\"true\""; if($options['gallery_type'] == 2) echo " checked"; ?>/></td>
							<td><a href="http://www.fancybox.net" target="_BLANK">FancyBox</a></td>
							<td align="center"><img src="<?php if($fb_enabled) echo $img_yes; else echo $img_no; ?>" alt="<?php if($fb_enabled) echo $txt_yes; else echo $txt_no; ?>" /></td>
							<td><?php _e("None", "mudslide"); ?></td>
							<td align="center"><img src="<?php echo $img_yes ?>" alt="<?php echo $txt_yes; ?>" /></td>
						</tr>
						<tr>
							<td><input type="radio" name="muds_gallery_type" value="1"<?php if(!$hs_enabled) echo " disabled=\"true\""; if($options['gallery_type'] == 1) echo " checked"; ?>/></td>
							<td><a href="http://highslide.com/"target="_BLANK">HighSlide</a></td>
							<td align="center"><img src="<?php if($hs_enabled) echo $img_yes; else echo $img_no; ?>" alt="<?php if($hs_enabled) echo $txt_yes; else echo $txt_no; ?>" /></td>
							<td><?php _e("None", "mudslide"); ?></td>
							<td align="center"><img src="<?php echo $img_no ?>" alt="<?php echo $txt_no; ?>" /></td>
						</tr>
						<tr>
							<td><input type="radio" name="muds_gallery_type" value="4"<?php if(!$pf_enabled) echo " disabled=\"true\""; if($options['gallery_type'] == 4) echo " checked"; ?>/></td>
							<td><a href="http://www.no-margin-for-errors.com/projects/prettyphoto-jquery-lightbox-clone/"target="_BLANK">prettyPhoto</a></td>
							<td align="center"><img src="<?php if($pf_enabled) echo $img_yes; else echo $img_no; ?>" alt="<?php if($pf_enabled) echo $txt_yes; else echo $txt_no; ?>" /></td>
							<td><a href="http://wordpress.org/extend/plugins/wp-prettyphoto/"target="_BLANK">wp-prettyPhoto</a></td>
							<td align="center"><img src="<?php echo $img_yes ?>" alt="<?php echo $txt_yes; ?>" /></td>
						</tr>
						<tr><td colspan="5"><?php _e('Be a fair user with the creator of the viewer you choose, and buy the license<br>if this plugin will run on a commercial site.','mudslide'); ?></td></tr>
					</table>
				</td>
			</tr>
		</table>
		<p class="submit">
			<input type="submit" name="Submit" class="button-primary" value="<?php _e('Update Options', 'mudslide' ) ?>" />
		</p>
	</form>
</div>
