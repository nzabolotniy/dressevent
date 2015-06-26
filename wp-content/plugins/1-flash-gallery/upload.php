<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
 
require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/wp-admin/admin.php');
require_once('fgallery.php');

/*
* Starting API functions
*
*/


/*
 * scan all files on FTP server route $link.$dir
 * $rec - include subfolders 
 * the results are collected into $filename global array
 */
function scan_ftp($link, $dir, $rec) { 
    global $filename; 
    $file_list = ftp_rawlist($link, $dir); 
    foreach($file_list as $file) { 
      list($acc, 
           $bloks, 
           $group, 
           $user, 
           $size, 
           $month, 
           $day, 
           $year, 
           $file) = preg_split("/[\s]+/", $file); 
      if(substr($file, 0, 1) == '.') continue; 
      if(substr($acc, 0, 1) == 'd') { 
        if ($rec) scan_ftp($link, $dir.$file."/", $rec); 
      } 
      if(substr($acc, 0, 1) == '-') { 
        $filename[] = $dir.$file; 
      } 
    } 
  } 
  
  /*
   * getting files from ftp to server and inserting info into db
   */
  function process_ftp($array, $ftp_folder, $conn_id) {
	global $wpdb;
	@set_time_limit(0);
	$error = '';
	$i = 0;
	if (!empty($array)) {
		foreach ($array as $key=>$value) {
			$file_parts = pathinfo($value);
			$targetFile = $_SERVER['DOCUMENT_ROOT']. EXTRA_DIR. 'wp-content/uploads/fgallery/'.date("YmdHsi").mt_rand(0,10).'.'.$file_parts['extension'];
			$handle = fopen($targetFile, 'w');
			if (@ftp_fget($conn_id, $handle, $value, FTP_BINARY, 0)) {
			 $error .= "successfully written to $targetFile\n";
			} else {
			 $error .= "There was a problem while downloading $value to $targetFile\n";
			}
			fclose($handle);
			$file = @getimagesize($targetFile);
			if (strpos($file['mime'],'image') === false) {
				$error .= "$targetFile is not an image file";
				unlink($targetFile);
			} else {
				$i++;
				$img_size = filesize($targetFile);
				$img_path = str_replace($_SERVER['DOCUMENT_ROOT']. EXTRA_DIR,'',$targetFile);
				$wpdb->insert(IMAGES_TABLE, array('img_caption'=>$file_parts['basename'] , 'img_vs_folder' => 0, 'img_parent' => $ftp_folder, 'img_date' => date("Y-m-d H:i:s"), 'img_type' => $file['mime'], 'img_size' => $img_size, 'img_path' => $img_path));
			}
		}
	}
	if ($error == '') {
		$error = $i;
	}
	return $error;
  }

// extract archive to temp dir
function fgallery_process_zip($data, $folder) {
	if($data['fgallery_zip']['tmp_name']['size'] && $data['fgallery_zip']['type'] == 'application/zip'){
        $tmp_fname = substr(md5($time), 0, 10) . '.zip';
        move_uploaded_file($data['fgallery_zip']['tmp_name'], FGALLERY_DIR . '/tmp/'. $tmp_fname);
        $filename = FGALLERY_DIR . '/tmp/'. $tmp_fname;
        if (!is_dir($filename. '_dir')) {
            mkdir($filename. '_dir');
        }
        WP_Filesystem();
        if(!unzip_file($filename, $filename. '_dir')){
			unlink($filename);
			continue;
        }   
        unlink($filename);
        $uploaded_a_file = fgallery_process_directory($filename. '_dir', FGALLERY_DIR, $folder);
        fgallery_delete_dir($filename. '_dir');
        unset($data['fgallery_zip']);
		return $uploaded_a_file;
    }
}

// process temp directory and insert images to db
function fgallery_process_directory($dir, $fgallery_dir, $folder){     
    global $wpdb;
    $items = list_files($dir);
    		$i = 0;
    foreach($items as $item){
		$file_name = str_replace($dir.'/', '',$item);
		$file_name = str_replace(' ','_',$file_name);
		$file_name = strtolower($file_name);
		$file_pathinfo = pathinfo($file_name);
		$filename = str_replace('.'.$file_pathinfo['extension'],'',$file_name);
		$file_name = date("YmdHis").rand(0,100).'.'.$file_pathinfo['extension'];
		
		$newitem = $fgallery_dir . '/'. $file_name;
		if (copy($item, $newitem)){
			$fileinfo = getimagesize($newitem);
			$img_type = $fileinfo['mime'];
			$img_path = str_replace(ABSPATH,'',$newitem);
			$img_size = filesize($newitem);
			$wpdb->insert(IMAGES_TABLE, array('img_date' => date("Y-m-d H:i:s"), 'img_type' => $img_type, 'img_size' => $img_size, 'img_path' => $img_path, 'img_vs_folder' => 0, 'img_parent' => $folder));
			$i++;
		}
    }
    return $i;
}

global $wpdb;

if (isset($_GET['action'])) {
	$action = $_GET['action'];
	switch ($action) {
		case 'unzip':
			if (!empty($_FILES)) {
				$zip_folder = $_POST['zip_folder'];
				$i = fgallery_process_zip($_FILES, $zip_folder);
			}
			echo sprintf(__('%d image(s) were added to the list'),$i);
			die();
		break;
		case 'upload_url':
			if (!empty($_POST) && wp_verify_nonce($_POST['fgallery_upload_files_field'], 'fgallery_upload_files')) {
				if (!empty($_POST['fgallery_url'])) {
					$i = 0;
					@set_time_limit(0);
					foreach ($_POST['fgallery_url'] as $url) {
						$info = parse_url($url);
						$file_pathinfo = pathinfo($info['path']);
						$file_name = $_SERVER['DOCUMENT_ROOT']. EXTRA_DIR. 'wp-content/uploads/fgallery/'.date("YmdHis").rand(0,100).'.'.$file_pathinfo['extension'];
						$fp = fopen($file_name, 'w');
						fwrite($fp, file_get_contents($url));
						fclose($fp); 
						$fileinfo = getimagesize($file_name);
						$img_type = $fileinfo['mime'];
						if (strpos($img_type,'image') === false) {
							echo sprintf(__('%s is not an image'),$url).'<br />';
							unlink($file_name);
							continue;
						}
						$img_path = str_replace($_SERVER['DOCUMENT_ROOT']. EXTRA_DIR,'',$file_name);
						$size = filesize($file_name);
						$wpdb->insert(IMAGES_TABLE, array('img_caption'=> __('Image from url', 'fgallery'), 'img_vs_folder' => 0, 'img_parent' => $_POST['fgallery_url_folder'], 'img_date' => date("Y-m-d H:i:s"), 'img_path' => $img_path, 'img_size'=>$size, 'img_type' => $img_type));	
						$i++;
					}
				}
			}
			echo sprintf(__('%d image(s) were added to the list'),$i);
			die();
		break;
		case 'ftp':
		global $filename;
		if (!empty($_POST) && wp_verify_nonce($_POST['fgallery_upload_files_field'], 'fgallery_upload_files')) {
			if ($_POST['ftp_name'] != '') {
				$ftp_name = $_POST['ftp_name'];
				$ftp_user = $_POST['ftp_username'];
				$ftp_pass = $_POST['ftp_pass'];
				$connect = ftp_connect($ftp_name) or die("Couldn't connect to $ftp_name");
				if ($ftp_pass !='') {
					if (!@ftp_login($connect, $ftp_user, $ftp_pass)) {
						die("Couldn't login to $ftp_name");
					}
				}
				$ftp_folder = $_POST['ftp_folder'];
				$rec = $_POST['fgallery_ftp_subfolders'];
				ftp_chdir($connect, $ftp_folder);
				// enabling passive mode
				ftp_pasv( $connect, true );
				// get contents of the current directory
				scan_ftp($connect, $ftp_folder, $rec); 
				$result = process_ftp($filename,$ftp_foldet,$connect);
				ftp_close($connect);
			}
			echo sprintf(__('%d image(s) were added to the list'),$i);
		}
		die();
		break;
		case 'media':
			if (!empty($_POST) && wp_verify_nonce($_POST['fgallery_upload_files_field'], 'fgallery_upload_files')) {
				if (!empty($_POST['media'])) {
					$ids = $_POST['media'];
					foreach ($ids as $key=>$value) {
						$post = get_post($value);
						$img_path = str_replace('http://'.$_SERVER['HTTP_HOST'],$_SERVER['DOCUMENT_ROOT'],$post->guid);
						$size = filesize($img_path);
						$img_path = str_replace($_SERVER['DOCUMENT_ROOT']. EXTRA_DIR,'',$img_path);
						$wpdb->insert(IMAGES_TABLE, array('img_caption'=> $post->post_title, 'img_vs_folder' => 0, 'img_parent' => $_POST['fgallery_url_folder'], 'img_date' => $post->post_date, 'img_path' => $img_path, 'img_size'=>$size));
					}
				}
			}
			echo sprintf(__('%d image(s) were added to the list'),count($ids));
			die();
		break;
		case 'facebook':
			if (!empty($_POST) && wp_verify_nonce($_POST['fgallery_upload_files_field'], 'fgallery_upload_files')) {
				if (!empty($_POST['fgallery_url_check'])) {
					$ids = $_POST['fgallery_url_check'];
					$i = 0;
					@set_time_limit(0);
					foreach ($ids as $key=>$value) {
						if (!empty($_POST['fgallery_url'][$key])) {
							$url = $_POST['fgallery_url'][$key];
							$info = parse_url($url);
							$file_pathinfo = pathinfo($info['path']);
							$file_name = $_SERVER['DOCUMENT_ROOT']. EXTRA_DIR. 'wp-content/uploads/fgallery/'.date("YmdHis").rand(0,100).'.'.$file_pathinfo['extension'];
							$fp = fopen($file_name, 'w');
							fwrite($fp, file_get_contents($url));
							fclose($fp); 
							$fileinfo = getimagesize($file_name);
							$img_type = $fileinfo['mime'];
							if (strpos($img_type,'image') === false) {
								echo sprintf(__('%s is not an image'),$url).'<br />';
								unlink($file_name);
								continue;
							}
							$img_path = str_replace($_SERVER['DOCUMENT_ROOT']. EXTRA_DIR,'',$file_name);
							$size = filesize($file_name);
							$wpdb->insert(IMAGES_TABLE, array('img_caption'=> __('Image from Facebook', 'fgallery'), 'img_vs_folder' => 0, 'img_parent' => $_POST['fgallery_url_folder'], 'img_date' => date("Y-m-d H:i:s"), 'img_path' => $img_path, 'img_size'=>$size, 'img_type' => $img_type));	
							$i++;
						}
					}
				}
				echo sprintf(__('%d image(s) were added to the list'),$i);
			}
			die();
		break;
	}
	}
?>
