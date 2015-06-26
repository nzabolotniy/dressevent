<p>
	<label for="mudslide_widget_title">
		<?php _e('Title', 'mudslide'); ?>:
		<input id="<?php echo $title_id; ?>" name="<?php echo $title_name; ?>" type="text" class="widefat" value="<?php echo  htmlspecialchars($title,  ENT_QUOTES); ?>"/>
	</label>
</p>
<p>
	<label for="mudslide_num">
		<?php	_e('Size', 'mudslide'); ?>: <input type="text" id="<?php echo $size_id; ?>" name="<?php echo $size_name; ?>" size="5" value="<?php echo $size; ?>">
	</label>
</p>
<p>
	<label for="mudslide_widget_type">
		<p>
			<input type='radio' id="<?php echo $type_id; ?>" name="<?php echo $type_name; ?>" value="<?php echo MUDS_WIDGET_RANDOM; ?>"<?php if($type==MUDS_WIDGET_RANDOM) echo ' checked'; ?>/> <?php _e('Random Photo', 'mudslide');?>
		</p>
		<p>
			<input type='radio' id="<?php echo $type_id; ?>" name="<?php echo $type_name; ?>" value="<?php echo MUDS_WIDGET_LAST; ?>"<?php if($type==MUDS_WIDGET_LAST) echo ' checked'; ?>/> <?php _e('Last Photo', 'mudslide');?>
		</p>
		<p>
			<input type='radio' id="<?php echo $type_id; ?>" name="<?php echo $type_name; ?>" value="<?php echo MUDS_WIDGET_RANDOMLAST; ?>"<?php if($type==MUDS_WIDGET_RANDOMLAST) echo ' checked'; ?>/> <?php _e('Random photo from last gallery', 'mudslide');?>
		</p>
		<p>
			<input type='radio' id="<?php echo $type_id; ?>" name="<?php echo $type_name; ?>" value="<?php echo MUDS_WIDGET_RANDOMFROM; ?>"<?php if($type==MUDS_WIDGET_RANDOMFROM) echo ' checked'; ?>/> <?php _e('Random photo from this gallery', 'mudslide'); ?>
			<br>
			<span>
				<label for="mudslide_widget_gal">
					<select id="<?php echo $gallery_id; ?>" name="<?php echo $gallery_name; ?>" style="width: 200px">
						<option value="0"><?php _e("No gallery", 'mudslide'); ?></option>
						<?php
							global $wpdb;
							$table_name = $wpdb->prefix . "mudslide";
							$gallerylist = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");
							if(is_array($gallerylist)) {
								foreach($gallerylist as $item) {
									$selected='';
									if($gallery==$item->id) {
										$selected=' selected';
									}
									echo '<option value="'.$item->id.'" ' . $selected . '>' . $item->name . '</option>' . "\n";
								}
							}
						?>
					</select>
				</label>
			</span>
		</p>
		<p>
			<input type='checkbox' id="<?php echo $search_id; ?>" name="<?php echo $search_name; ?>"<?php if((int)$search) echo " checked"; ?>/> <?php _e('Display on its page', 'mudslide');?>
		</p>
	</label>
</p>
<p>
	<label>
		<?php printf(__('You can <a href="%s">edit</a> the galleries list in <strong>Tools / MudSlideShow</strong>. Remember this plugin adds automatically a gallery to the list once it has been viewed in a post or page, even if you have deleted it from the list.', 'mudslide'), "tools.php?page=mudsmanage"); ?>
	</label>
</p>
<input type="hidden" id="mudslide-widget-submit" name="mudslide-widget-submit" value="1" />
