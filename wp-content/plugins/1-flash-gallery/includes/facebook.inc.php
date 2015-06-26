<?php

class FGalleryFacebookAPI {
	var $facebook	 = null;
	var $sessions = array();
	var $token		= null;
	var $error		= false;
	var $msg			= null;
	var $secret	 = null;
	var $progress = 0;
	var $increment = null;

	function FGalleryFacebookAPI() {
		if(!class_exists('FB_Facebook'))
			include_once('facebook.php');
		$facebook = new FB_Facebook(FGALLERY_API_KEY, FGALLERY_API_SECRET, null, true);
		$this->facebook = $facebook->api_client;

		global $fb_message;
		$this->msg = &$fb_message;

		// check if the facebook session is the structure from older
		// versions of Fotobook, if so remove it to start over
		$sessions = get_option('fgallery_facebook_session');
		if(isset($sessions['session_key'])) {
			update_option('fgallery_facebook_session', '');
		}

		// set sessions to the object
		$this->set_sessions();

		// get token every time for additional users
		$this->token = $this->get_auth_token();

		// determine how much to increment the progress bar after each request
		$this->progress  = get_option('fb_update_progress');
		$this->increment = count($this->sessions) > 0 ? 100 / (count($this->sessions) * 3) : 0;
	}

	/**
	 * Activates the provided UID to perform actions on that account.
	 * @param int $uid
	 * @return bool Whether or not the UID was found
	 */
	function select_session($uid) {
		foreach ($this->sessions as $session) {
			if ($session['uid'] == $uid) {
				$this->facebook->set_user($uid);
				$this->facebook->use_session_secret($session['secret']);
				$this->facebook->session_key = $session['session_key'];
				return true;
			}
		}
		return false;
	}

	function link_active() {
		return count($this->sessions) > 0;
	}

	function get_auth_token() {
		$this->facebook->session_key = '';
		$this->facebook->secret = FGALLERY_API_SECRET;
		$this->token = $this->facebook->auth_createToken();
		if(!$this->token) {
			$this->error = true;
			$this->msg = '1 Flash Gallery is unable to connect to Facebook.';
		}
		return $this->token;
	}

	function set_sessions() {
		$sessions = get_option('fgallery_facebook_session');

		if(!$sessions)
			return false;

		// make sure all accounts are still active
		foreach($sessions as $key => $session) {
			$this->select_session($session['uid']);
			$user = $this->facebook->users_getInfo($session['uid'], array('name'));
			if($this->facebook->error_code == 102) {
				// if it can't get the user than remove it from the Facebook session array because
				// the link isn't active anymore
				$this->msg = 'The link to '.$sessions[$key]['name'].'\'s account was lost.	 Please authorize the account again.';
				unset($sessions[$key]);
				update_option('fgallery_facebook_session', $sessions);
			}
		}

		$this->sessions = $sessions;
		return count($sessions) > 0;
	}

	function get_auth_session($token) {
		$sessions = $this->sessions;

		try {
			$new_session = $this->facebook->auth_getSession($token);
		}
		catch( Exception $e ) {
			$this->error = true;
			$this->msg = 'Unable to activate account: ' . $e->getMessage();
			return false;
		}

		// check to see if this account is already linked
		$active = array();
		if(is_array($sessions)) {
			foreach($sessions as $value) { $active[] = $value['uid']; }
		}
		if(in_array($new_session['uid'], $active)) {
			$this->msg = 'That user is already linked to 1 Flash Gallery.';
			return false;
		}

		// get user's name
		$this->select_session($new_session['uid']);
		$user = $this->facebook->users_getInfo($new_session['uid'], array('name'));
		$new_session['name'] = $user[0]['name'];
		//if(!$new_session['name'])
			//return false;
		if(!is_array($sessions)) $sessions = array();
		$sessions[] = $new_session;
		update_option('fgallery_facebook_session', $sessions);
		$this->msg = '1 Flash Gallery is now linked to '.$new_session['name'].'\'s Facebook account';

		$this->set_sessions();
		return count($sessions) > 0;
	}

	function remove_user($key) {
		// remove all of this user's albums and photos

		$this->msg = 'The link to '.$this->sessions[$key]['name'].'\'s Facebook account has been removed.';

		unset($this->sessions[$key]);
		update_option('fgallery_facebook_session', $this->sessions);
	}

	function increase_time_limit() {
		// allow the script plenty of time to make requests
		if(!ini_get('safe_mode') && !strstr(ini_get('disabled_functions'), 'set_time_limit'))
			set_time_limit(500);
	}
	
	function update_progress($reset = false) {
		if($reset == true) {
			$this->progress = 0;
		}
		else {
			$this->progress = $this->progress + $this->increment;
		}
		if($this->progress > 100) {
			$this->progress = 100;
		}
		update_option('fb_update_progress', $this->progress);
		return $this->progress;
	}

}
?>