<?php
define('FORUMPATH', realpath(dirname(__FILE__)).Config::$vbbIntegration['path']);

function userdata_convert(&$userdata) {
   $vbuser = array( 'username' => $userdata['name'] );
   if (isset($userdata['email']))
      $vbuser['email'] = $userdata['email'];
   if (isset($userdata['pass']))
      $vbuser['password'] = $userdata['pass'];
	  
   return $vbuser;
}


define('REGISTERED_USERGROUP', 2); // typical default for registered users
define('PERMANENT_COOKIE', false); // false=session cookies (recommended)

define('THIS_SCRIPT', __FILE__);
$cwd = getcwd();
chdir(FORUMPATH);
require_once('./includes/init.php'); // includes class_core.php
require_once('./includes/class_dm.php'); // for class_dm_user.php
require_once('./includes/class_dm_user.php'); // for user functions
require_once('./includes/functions.php'); // vbsetcookie etc.
require_once('./includes/functions_login.php'); // process login/logout


//---------------------------------------------------------------------
// This function duplicates the functionality of fetch_userinfo(),
// using the user name instead of numeric ID as the argument.
// See comments in includes/functions.php for documentation.
//---------------------------------------------------------------------
function fetch_userinfo_from_username(&$username, $option=0, $languageid=0)
{
   global $vbulletin;
   $useridq = $vbulletin->db->query_first_slave("SELECT userid FROM "
      . TABLE_PREFIX . "user WHERE username='{$username}'");
   if (!$useridq) return $useridq;
   $userid = $useridq['userid'];
   return fetch_userinfo($userid, $option, $languageid);
}


//---------------------------------------------------------------------
// CLASS ForumOps
//---------------------------------------------------------------------
class ForumOps extends vB_DataManager_User {
   var $userdm;

// *********************************************************************************************************
	
   function __construct( &$vbulletin ) // constructor
   {
      $this->vbulletin =& $vbulletin;
      $this->userdm =& datamanager_init('user', $vbulletin, ERRTYPE_ARRAY);
   }
   
   function ForumOps() // constructor
   {
      global $vbulletin;
      $this->userdm =& datamanager_init('User', $vbulletin, ERRTYPE_ARRAY);
   }

	// *********************************************************************************************************
	
   //======== USER REGISTRATION / UPDATE / DELETE ========

   function register_newuser(&$userdata, $login = true)
   {
      global $vbulletin;
      $vbuser = userdata_convert($userdata);
      foreach($vbuser as $key => $value)
         $this->userdm->set($key, $value);
      $this->userdm->set('usergroupid', REGISTERED_USERGROUP);
	  $this->userdm->set('timezoneoffset', 1);

      // Bitfields; set to desired defaults.
      // Comment out those you have set as defaults
      // in the vBuleltin admin control panel
      $this->userdm->set_bitfield('options', 'adminemail', 1);
      $this->userdm->set_bitfield('options', 'showsignatures', 1);
      $this->userdm->set_bitfield('options', 'showavatars', 0);
      $this->userdm->set_bitfield('options', 'showimages', 1);
      $this->userdm->set_bitfield('options', 'showemail', 0);

      if ($login) $this->login($vbuser);

      //$this->userdm->errors contains error messages
      if (empty($this->userdm->errors))
         $vbulletin->userinfo['userid'] = $this->userdm->save();
      else
         return implode('<br>', $this->userdm->errors);
      return NULL;
   }

// *********************************************************************************************************

   function update_user(&$userdata)
   {
      global $vbulletin;
      $vbuser = userdata_convert($userdata);
	  
      if (!($existing_user = fetch_userinfo_from_username($vbuser['username'])))
         return 'fetch_userinfo_from_username() failed.';

      $this->userdm->set_existing($existing_user);
      foreach($vbuser as $key => $value)
         $this->userdm->set($key, $value);

      // reset password cookie in case password changed
      if (isset($vbuser['password']))
         vbsetcookie('password',
            md5($vbulletin->userinfo['password'].COOKIE_SALT),
            PERMANENT_COOKIE, true, true);

      if (count($this->userdm->errors))
         return implode('<br>', $this->userdm->errors);
      $vbulletin->userinfo['userid'] = $this->userdm->save();
      return NULL;
   }
   
   
// *********************************************************************************************************

   function update_avatar(&$userdata)   {
	   global $vbulletin;
	   $vbuser = userdata_convert($userdata);
		  
		if (!($existing_user = fetch_userinfo_from_username($vbuser['username'])))
			return 'fetch_userinfo_from_username() failed.';

		$this->userdm->set_existing($existing_user);
		  
		// update avatar in case it should be
		if (!empty($vbuser['avatarurl'])) {
			//~ echo"<pre>";
				//~ print_r ($existing_user);
			//~ echo "</pre>";
		  
			// begin custom avatar code
			require_once(DIR . '/includes/class_upload.php');
			require_once(DIR . '/includes/class_image.php');

			$upload = new vB_Upload_Userpic($vbulletin);

			$upload->data =& datamanager_init('Userpic_Avatar', $vbulletin, ERRTYPE_STANDARD, 'userpic');
			$upload->image =& vB_Image::fetch_library($vbulletin);
			$upload->userinfo =& $existing_user;
			cache_permissions($existing_user, false);
			$upload->maxwidth = $vbulletin->userinfo['permissions']['avatarmaxwidth'];
			$upload->maxheight = $vbulletin->userinfo['permissions']['avatarmaxheight'];
			if (!$upload->process_upload($vbuser['avatarurl'])) {
				echo $upload->fetch_error();
			}
		}
		else { // no avatar used!
			$userpic =& datamanager_init('Userpic_Avatar', $vbulletin, ERRTYPE_CP, 'userpic');
			$userpic->condition = "userid = " . $existing_user['userid'];
			$userpic->delete();
		}
	$this->userdm->set_existing($vbulletin->userinfo);

	($hook = vBulletinHook::fetch_hook('profile_updateavatar_complete')) ? eval($hook) : false;
    return NULL;
   }
   
// *********************************************************************************************************
	
   function delete_user(&$username)
   {
   // The vBulletin documentation suggests using userdm->delete()
   // to delete a user, but upon examining the code, this doesn't
   // delete everything associated with the user.  The following
   // is adapted from admincp/user.php instead.
   // NOTE: THIS MAY REQUIRE MAINTENANCE WITH NEW VBULLETIN UPDATES.

      global $vbulletin;
      $db = &$vbulletin->db;
      $userdata = $db->query_first_slave("SELECT userid FROM "
         . TABLE_PREFIX . "user WHERE username='{$username}'");
      $userid = $userdata['userid'];
      if ($userid) {

      // from admincp/user.php 'do prune users (step 1)'

         // delete subscribed forums
         $db->query_write("DELETE FROM " . TABLE_PREFIX
            . "subscribeforum WHERE userid={$userid}");
         // delete subscribed threads
         $db->query_write("DELETE FROM " . TABLE_PREFIX
            . "subscribethread WHERE userid={$userid}");
         // delete events
         $db->query_write("DELETE FROM " . TABLE_PREFIX
            . "event WHERE userid={$userid}");
         // delete event reminders
         $db->query_write("DELETE FROM " . TABLE_PREFIX
            . "subscribeevent WHERE userid={$userid}");
         // delete custom avatars
         $db->query_write("DELETE FROM " . TABLE_PREFIX
            . "customavatar WHERE userid={$userid}");
         $customavatars = $db->query_read("SELECT userid, avatarrevision FROM "
          . TABLE_PREFIX . "user WHERE userid={$userid}");
         while ($customavatar = $db->fetch_array($customavatars)) {
            @unlink($vbulletin->options['avatarpath'] . "/avatar{$customavatar['userid']}_{$customavatar['avatarrevision']}.gif");
         }
         // delete custom profile pics
         $db->query_write("DELETE FROM " . TABLE_PREFIX
            . "customprofilepic WHERE userid={$userid}");
         $customprofilepics = $db->query_read(
            "SELECT userid, profilepicrevision FROM "
            . TABLE_PREFIX . "user WHERE userid={$userid}");
         while ($customprofilepic = $db->fetch_array($customprofilepics)) {
            @unlink($vbulletin->options['profilepicpath'] . "/profilepic$customprofilepic[userid]_$customprofilepic[profilepicrevision].gif");
         }
         // delete user forum access
         $db->query_write("DELETE FROM " . TABLE_PREFIX
            . "access WHERE userid={$userid}");
         // delete moderator
         $db->query_write("DELETE FROM " . TABLE_PREFIX
            . "moderator WHERE userid={$userid}");
         // delete private messages
         $db->query_write("DELETE FROM " . TABLE_PREFIX
            . "pm WHERE userid={$userid}");
         $db->query_write("DELETE FROM " . TABLE_PREFIX
            . "pmreceipt WHERE userid={$userid}");
         $db->query_write("DELETE FROM " . TABLE_PREFIX
            . "session WHERE userid={$userid}");
         // delete user group join requests
         $db->query_write("DELETE FROM " . TABLE_PREFIX
            . "usergrouprequest WHERE userid={$userid}");
         // delete bans
         $db->query_write("DELETE FROM " . TABLE_PREFIX
            . "userban WHERE userid={$userid}");
         // delete user notes
         $db->query_write("DELETE FROM " . TABLE_PREFIX
            . "usernote WHERE userid={$userid}");

      // from admincp/users.php 'do prune users (step 2)'

         // update deleted user's posts with userid=0
         $db->query_write("UPDATE " . TABLE_PREFIX
            . "thread SET postuserid = 0, postusername = '"
            . $db->escape_string($username)
            . "' WHERE postuserid = $userid");
         $db->query_write("UPDATE " . TABLE_PREFIX
            . "post SET userid = 0, username = '"
            . $db->escape_string($username)
            . "' WHERE userid = $userid");

         // finally, delete the user
         $db->query_write("DELETE FROM " . TABLE_PREFIX
            . "usertextfield WHERE userid={$userid}");
         $db->query_write("DELETE FROM " . TABLE_PREFIX
            . "userfield WHERE userid={$userid}");
         $db->query_write("DELETE FROM " . TABLE_PREFIX
            . "user WHERE userid={$userid}");
      }
   /*
      the following is suggested in the documentation but doesn't work:

      $existing_user = fetch_userinfo_from_username($username);
      $this->userdm->set_existing($existing_user);
      return $this->userdm->delete();
   */
   }

	function set_email( $username, $email ) {
		global $vbulletin;
        $db = &$vbulletin->db;
		$db->query_write("UPDATE " . TABLE_PREFIX . "user SET email = '"
			.mysql_real_escape_string($email)."' WHERE username = '"
			.mysql_real_escape_string($username)."'"
		);
	}
	
	function set_pass( $username, $pass ) {
		global $vbulletin;
        $db = &$vbulletin->db;
		$pass = md5($pass);
		$salt = $this->fetch_user_salt();
		$db->query_write("UPDATE " . TABLE_PREFIX . "user SET password = '"
			.mysql_real_escape_string(md5($pass.$salt))."', 
			salt = '".mysql_real_escape_string($salt)."' WHERE username = '"
			.mysql_real_escape_string($username)."'"
		);
	}
	
	function fetch_user_salt($length = SALT_LENGTH) {
		$salt = '';

		for ($i = 0; $i < $length; $i++)
		{
			$salt .= chr(rand(33, 126));
		}

		return $salt;
	}

   // ======== USER LOGIN / LOGOUT ========

   function login($vbuser)
   {
      global $vbulletin;
      $vbulletin->userinfo = fetch_userinfo_from_username($vbuser['username']);
      // set cookies
      vbsetcookie('userid', $vbulletin->userinfo['userid'],
         PERMANENT_COOKIE, true, true);
      vbsetcookie('password',
         md5($vbulletin->userinfo['password'].COOKIE_SALT),
         PERMANENT_COOKIE, true, true);
      // create session stuff
      process_new_login('', 1, '');
   }


   function logout()
   {
      process_logout(); // unsets all cookies and session data
   }

} // end class ForumOps
chdir($cwd);
?>