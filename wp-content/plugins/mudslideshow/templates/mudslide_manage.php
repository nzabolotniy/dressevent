<?php
	//Get query text
	$text = $paged = false;
	if(isset($_GET['text'])) $text=$_GET['text'];
	if(isset($_GET['paged'])) $paged=$_GET['paged'];
	
	//Create the nonce value
	$nonce = wp_create_nonce('mudslide');
	
	//Asume page number
	$page=0;
	//if there is a new text query, set 1st page and get text query from text label
	if(isset($_POST['but']) && $_POST['but']!="") {
		$text=$_POST['text'];
		$page=1;
	} else {
	//Else, use page send in URL
		$page=0+$paged;
		if($page==0) { //If there is no page, asume 1st
			$page=1;
		}
	}
	
	//Get query results
	$sql="SELECT * FROM $table_name";
	if($text!="") {
		$sql.=" WHERE (name like '%$text%')";
	}
	$galleries = $wpdb->get_results($sql);

	//Count results
	$total=count($galleries);
	
	//Local URL
	$url = get_bloginfo( 'wpurl' );
	$local_url = parse_url( $url );
	$aux_url   = parse_url(wp_guess_url());
	$url = str_replace($local_url['host'], $aux_url['host'], $url);
	
	//If we don't have a new query
	if($page==0) {
		$page=0+$_POST['paged']+$_GET['paged'];
		if($page==0) {
			$page=1;
		}
	}

	//Items by page
	$max=10;
	
	//Page must have a real group
	$page=min(ceil($total/$max),$page);
	
	//Create groups to be shown in page selector
	$start=($page-1)*$max+1;
	$end=min( $page * $max, $total );
	$page_links = paginate_links( array(
		'base' => add_query_arg( array('paged'=>'%#%','text'=>$text) ),
		'format' => '',
		'prev_text' => '&laquo;',
		'next_text' => '&raquo;',
		'total' => ceil($total/$max),
		'current' => $page
	));
	
?>
<script type='text/javascript'>
	//<![CDATA[
	
	var loading_muds_img = new Image(); 
	loading_muds_img.src = '<?php echo muds_plugin_url('/img/loading-page.gif'); ?>';
	
	function muds_update( style, id )
	{
		var muds_sack_updated = new sack('<?php echo $url; ?>/wp-admin/admin-ajax.php' );
		
		//Our plugin sack configuration
		muds_sack_updated.execute = 0;
		muds_sack_updated.method = 'POST';
		muds_sack_updated.setVar( 'action', 'muds_ajax_update' );
		muds_sack_updated.element = 'galname-'+id;
		
		//The ajax call data
		muds_sack_updated.setVar( 'style', style );
		muds_sack_updated.setVar( 'id', id );
		
		//What to do on error?
		muds_sack_updated.onError = function() {
			var aux = document.getElementById(muds_sack_updated.element);
			aux.innerHTMLsetAttribute='<?php _e("Can\'t read MudSlideShow Feed", 'muds'); ?>';
		};
		
		muds_sack_updated.onCompletion = function() {
			var aux = document.getElementById('throbber-mss'+id);
			aux.setAttribute('class', 'throbber-off');
			aux.setAttribute('className', 'throbber-off'); //IE sucks
		}
		
		muds_sack_updated.runAJAX();
		
		return true;

	} // end of JavaScript function muds_feed
	//]]>
</script>
<div class="wrap">
	<div id="icon-tools" class="icon32"><br /></div>
	<h2><?php _e( 'Galleries', 'mudslide' );?></h2>
	<form name="form1" method="post" action="<?php echo add_query_arg(array('mode'=>'', 'text'=>$text)); ?>">
		<ul class="subsubsub">
			<li class='all'><a href='<?php echo remove_query_arg(array('mode', 'id', 'paged', 'text')); ?>' class='current'><?php _e('Internal list', 'mudslide'); ?></a></li>
		</ul>
		<p class="search-box">
			<label class="hidden" for="post-search-input"><?php _e( 'Search galleries', 'mudslide' ); ?>:</label>
			<input type="text" class="search-input" id="post-search-input" name="text" value="<?php echo $text; ?>" />
			<input type="submit" value="<?php _e( 'Search galleries', 'mudslide' ); ?>" class="button" name="but" />
		</p>
		<div class="tablenav"><?php if ( $page_links ) { ?>
			<div class="tablenav-pages"><?php $page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s', 'mudslide' ) . '</span>%s',
				number_format_i18n( $start ),
				number_format_i18n( $end ),
				number_format_i18n( $total ),
				$page_links
				); echo $page_links_text; ?>
			</div><?php } 
				//Query group
				$sql.=" ORDER BY id DESC LIMIT ".($start - 1).", $max";
				$galleries = $wpdb->get_results($sql);
				$count=count($galleries);
				if($count==0) { ?> 
			<div class="clear"></div>
			<p><?php _e('No entries found','mudslide'); ?> <input type="submit" value="<?php _e( 'Add Gallery', 'mudslide' ); ?>" class="button" name="addgallery" /></p><?php } else { ?>
			<div class="alignleft actions">
				<select name="action">
					<option value="-1" selected="selected"><?php _e('Bulk Actions'); ?></option>
					<!--<option value="update"><?php _e('Update'); ?></option>-->
					<option value="delete"><?php _e('Delete'); ?></option>
				</select>
				<input type="submit" name="doaction" id="doaction" value="<?php _e('Apply'); ?>" class="button-secondary apply" />
				<input type="submit" value="<?php _e( 'Add Gallery', 'mudslide' ); ?>" class="button" name="addgallery" />
				<input type="hidden" id="_wpnonce" name="_wpnonce" value="c70ddc4ef7" />
				<input type="hidden" name="_wp_http_referer" value="/wordpress/wp-admin/edit-galleries.php" />
			</div>
			<br class="clear" />
		</div>
		<div class="clear"></div>
		<table class="widefat galleries fixed" cellspacing="0">
			<thead>
				<tr>
					<th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th>
					<th scope="col" class="manage-column" style=""><?php _e('Gallery', 'mudslide') ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th scope="col" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th>
					<th scope="col" class="manage-column" style=""><?php _e('Gallery', 'mudslide') ?></th>
				</tr>
			</tfoot>
			<tbody id="the-gallery-list" class="list:gallery"><?php foreach($galleries as $gallery) { ?>
				<tr id='gallery-<?php echo $gallery->id; ?>'>
					<th scope="row" class="check-column">
						<input type='checkbox' name='checked_galleries[]' value='<?php echo $gallery->id; ?>' />
					</th>
					<td class="gallery column-gallery">
						<p>
							<div id="submitted-on"><span id="galname-<?php echo $gallery->id ?>"><?php echo $gallery->name; ?> <img src='<?php echo muds_plugin_url('/img/loading.gif'); ?>' height='10' id='throbber-mss<?php echo $gallery->id; ?>' class='throbber-off'></span> | <?php
									switch ($gallery->type) {
										case MUDS_PICASA: echo "Picasa"; break;
										case MUDS_FLICKR: echo "Flickr"; break;
									}?>
							</div>
						</p>
						<div class="row-actions">
							<span><a style='cursor : pointer;' id="update-<?php echo $gallery->id; ?>" onclick="
									var aux = document.getElementById('throbber-mss<?php echo $gallery->id; ?>');
									aux.setAttribute('class', 'throbber-on');
									aux.setAttribute('className', 'throbber-on'); //IE sucks
									muds_update( 'update', <?php echo $gallery->id; ?> );
									"><?php _e('Update', 'mudslide') ?></a></span>
							<span class='delete'> | <a href="<?php echo wp_nonce_url(add_query_arg( array('paged'=>$page,'text'=>$text, 'mode' => 'delete', 'id' => $gallery->id) ), 'mudslide_deletegallery'); ?>" class="delete" onclick="javascript:check=confirm( '<?php _e("Delete this gallery?",'mudslide')?>');if(check==false) return false;"><?php _e('Delete', 'mudslide') ?></a></span>							
						</div>
					</td>
				</tr><?php } ?>
			</tbody>
		</table>
		<div class="tablenav"><?php if ( $page_links ) { ?>
			<div class="tablenav-pages"><?php $page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s', 'mudslide' ) . '</span>%s',
					number_format_i18n( $start ),
					number_format_i18n( $end ),
					number_format_i18n( $total ),
					$page_links
					); echo $page_links_text; ?>
			</div><?php } ?>
			<div class="alignleft actions">
				<select name="action2">
					<option value="-1" selected="selected"><?php _e('Bulk Actions'); ?></option>					
					<option value="update"><?php _e('Update'); ?></option>
					<option value="delete"><?php _e('Delete'); ?></option>
				</select>
				<input type="submit" name="doaction2" id="doaction2" value="<?php _e('Apply'); ?>" class="button-secondary apply" />
				<input type="submit" value="<?php _e( 'Add Gallery', 'mudslide' ); ?>" class="button" name="addgallery" />
				<input type="hidden" id="_wpnonce" name="_wpnonce" value="c70ddc4ef7" />
				<input type="hidden" name="_wp_http_referer" value="/wordpress/wp-admin/edit-galleries.php" />
			</div>
			<br class="clear" />
		</div><?php } ?>
	</form>
</div>
