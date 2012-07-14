<?php

/* 
	An object of this class represents a user on the site. All user 
	related tasks (registration, login, profile etc.) are handled here.
	
	A user object is created and attempted to login on each page load.
*/

require_once( 'lib/config.php' );
require_once( 'lib/db.php' );

class User {
	public $name = '';
	public $id = 0;
	public $validationString = '';
	public $admin = 0;
	public $website = 0;
		
	public function __construct() {
	}
	
	public function validate( $id ) {
		$u = DB::getRow( 
			'SELECT id, name, admin, website FROM '.TABLE_USERS.' WHERE valid = 0 AND remember = :1', 
			$id
		);
		
		if( empty($u) ) {
			return false;
		}
		
		DB::updateRow( TABLE_USERS, array('id' => $u['id']), array( 'valid' => 1) );
		
		session_name( Config::$sessionCookie );
		session_start();
		$_SESSION['id'] = $this->id = $u['id'];
		$_SESSION['name'] = $this->name = $u['name'];
		$_SESSION['admin'] = $this->admin = $u['admin'];
		$_SESSION['website'] = $this->website = $u['website'];
		
		setcookie( Config::$rememberCookie, $id, time() + 3600 * 24 * 365, Config::$absolutePath );
		
		if( Config::$vbbIntegration['enabled'] ) { 
			global $vbulletin;
			$forum = new ForumOps($vbulletin);
			$forum->login(array('username' => $u['name']));
		}
		return true;
	}
	
	public function isSpamLocked() {
		// delete all ips older than IP_LOCK_TIME
		DB::query( 'DELETE FROM '.TABLE_UPLOADLOCK.' WHERE ts < :1', time() - Config::$uploadLockTime );
		
		list(, $ip) = unpack('l',pack('l',ip2long($_SERVER['REMOTE_ADDR'])));
		$r = DB::getRow( 'SELECT COUNT(*) AS c FROM '.TABLE_UPLOADLOCK.' 
			WHERE ip = :1', 
			$ip
		);
		
		if( $r['c'] > Config::$maxNumUploads ) {
			return true;
		} else {
			return false;
		}
	}
	
	public function logUpload() {
		list(, $ip) = unpack('l',pack('l',ip2long($_SERVER['REMOTE_ADDR'])));
		DB::insertRow( TABLE_UPLOADLOCK, array('ip' => $ip, 'ts' => time()) );
	}
	
	public function login() {
		
		// attempt to login via post, session or remeber cookie
		session_name( Config::$sessionCookie );
		
		if( isset($_POST['login']) ) { // post login
			$u = DB::getRow(
				'SELECT id, name, admin, website FROM '.TABLE_USERS.' WHERE name = :1 AND pass = :2 and valid = 1', 
				$_POST['name'], md5($_POST['pass']) 
			);
			if( !empty($u) ) {
				session_start();
				$_SESSION['id'] = $this->id = $u['id'];
				$_SESSION['name'] = $this->name = $u['name'];
				$_SESSION['admin'] = $this->admin = $u['admin'];
				$_SESSION['website'] = $this->website = $u['website'];
				
				if( !empty($_POST['remember']) ) {
					$r = md5(uniqid(rand()));
					DB::updateRow( TABLE_USERS, array('id' => $this->id), array('remember' => $r ) );
					setcookie( Config::$rememberCookie, $r, time() + 3600 * 24 * 365, Config::$absolutePath );
				}
				
				if( Config::$vbbIntegration['enabled'] ) { 
					global $vbulletin;
					$forum = new ForumOps($vbulletin);
					$forum->login(array('username' => $u['name']));
				}
			}
		}
		
		else if( !empty($_COOKIE[Config::$sessionCookie]) ) { // session running
			session_start();
			if( empty($_SESSION['id']) ) {
				setcookie(Config::$sessionCookie, false, time() - 3600, Config::$absolutePath );
			} else {
				$this->id = $_SESSION['id'];
				$this->name = $_SESSION['name'];
				$this->admin = $_SESSION['admin'];
				$this->website = $_SESSION['website'];
			}
		} 
		
		else if( !empty($_COOKIE[Config::$rememberCookie]) ) { // remember cookie found
			$u = DB::getRow(
				'SELECT id, name, admin, website FROM '.TABLE_USERS.' WHERE remember = :1', 
				$_COOKIE[Config::$rememberCookie]
			);
			if( !empty($u) ) {
			
				session_start();
				$_SESSION['id'] = $this->id = $u['id'];
				$_SESSION['name'] = $this->name = $u['name'];
				$_SESSION['admin'] = $this->admin = $u['admin'];
				$_SESSION['website'] = $this->website = $u['website'];
				
				// refresh for another year
				setcookie( Config::$rememberCookie, $_COOKIE[Config::$rememberCookie], time() + 3600 * 24 * 365, Config::$absolutePath );
				
				if( Config::$vbbIntegration['enabled'] ) { 
					global $vbulletin;
					$forum = new ForumOps($vbulletin);
					$forum->login(array('username' => $u['name']));
				}
			}
		}
	}
	
	public function profile( $localFile, &$messages ) {
		$upd = array( 'website' => $_POST['website'] );
		
		$_SESSION['website'] = $_POST['website'];
		
		if( Config::$vbbIntegration['enabled'] ) { 
			global $vbulletin;
			$forum = new ForumOps($vbulletin);
		}
		
		$p = trim($_POST['cpass']);
		if( !empty( $p ) ) {
			if( strlen($_POST['cpass']) < 6 ) {
				$messages['passToShort'] = true;
			}
			else if( $_POST['cpass'] != $_POST['cpass2'] ) {
				$messages['passNotEqual'] = true;
			}
			else {
				$upd['pass'] = md5($_POST['cpass2']);
				if( Config::$vbbIntegration['enabled'] ) { 
					$forum->set_pass( $_SESSION['name'], $_POST['cpass2']);
				}
			}
		}
		
		if( !empty( $localFile ) ) {
			require_once( 'lib/images.php' );
			$name = Config::$images['avatarsPath'].uniqid().'.jpg';
			if( Image::createThumb( $localFile, $name, 40,40 ) ) {
				$upd['avatar'] = $name;
			} else {
				$messages['avatarFailed'] = true;
			}
		}
		
		if( empty($this->email) && 
			preg_match('/^[\.\w\-]{1,}@[\.\w\-]{2,}\.[\w]{2,}$/', $_POST['email']) 
		) {
			if( Config::$vbbIntegration['enabled'] ) { 
				$forum->set_email( $_SESSION['name'], $_POST['email']);
			}
			$upd['email'] = $_POST['email'];
		}
		
		DB::updateRow( TABLE_USERS, array( 'id' => $this->id ), $upd );
	}
	
	public function loadEmail() {
		$tmp = DB::getRow( 
			'SELECT email FROM '.TABLE_USERS.' WHERE id = :1',
			$this->id
		);
		$this->email = $tmp['email'];
	}
	
	public function logout() {
		session_unset();
		session_destroy();
		$_SESSION = array();
		setcookie( Config::$rememberCookie, false, time() - 3600, Config::$absolutePath );
		setcookie( Config::$sessionCookie, false, time() - 3600, Config::$absolutePath );
		
		if( Config::$vbbIntegration['enabled'] ) { 
			global $vbulletin;
			$forum = new ForumOps($vbulletin);
			$forum->logout();
		}
	}
	
	public function increaseScore( $amount ) {
		DB::query( 
			'UPDATE '.TABLE_USERS.' SET score = score + :1 WHERE id = :2', 
			intval( $amount ),
			$this->id
		);
	}
	
	public function register( &$messages ) {
	
		DB::query( 'DELETE FROM '.TABLE_USERS.' WHERE valid = 0 AND registered < NOW() - INTERVAL 30 MINUTE' );
	
		if( !preg_match('/^\w{2,20}$/', $_POST['name']) ) {
			$messages['nameInvalid'] = true;
		} else {
			$u = DB::getRow( 'SELECT * FROM '.TABLE_USERS.' WHERE name = :1', $_POST['name'] );
			if( !empty($u) ) {
				$messages['nameInUse'] = true;
			}
		}
		
		if( strlen($_POST['pass']) < 6 ) {
			$messages['passToShort'] = true;
		}
		else if( $_POST['pass'] != $_POST['pass2'] ) {
			$messages['passNotEqual'] = true;
		}
		
		if( !preg_match( '/^[\.\w\-]{1,}@[\.\w\-]{2,}\.[\w]{2,}$/', $_POST['email'] ) ) {
			$messages['wrongEmail'] = true;
		} else {
			$u = DB::getRow( 'SELECT * FROM '.TABLE_USERS.' WHERE email = :1', $_POST['email'] );
			if( !empty($u) ) {
				$messages['emailInUse'] = true;
			}
		}
	
	
		if( !empty($messages) ) {
			return false;
		}
		
		
		$this->validationString = md5(uniqid(rand()));
		
		DB::insertRow( TABLE_USERS, array(
			'registered' => date('Y-m-d H:i:s'),
			'name' => $_POST['name'],
			'pass' => md5($_POST['pass']),
			'valid' => 0,
			'score' => 0,
			'images' => 0,
			'website' => '',
			'avatar' => Config::$images['avatarsPath'].'default.png',
			'remember' => $this->validationString,
			'admin' => 0,
			'email' => $_POST['email']
		));
		
		if( Config::$vbbIntegration['enabled'] ) { 
			global $vbulletin;
			$forum = new ForumOps($vbulletin);
			$user['name'] = $_POST['name'];
			$user['pass'] = $_POST['pass'];
			$user['email'] = $_POST['email'];
			echo $forum->register_newuser($user, false);
		}
		
		$mail = file_get_contents( Config::$templates.'registrationmail.txt' );
		preg_match( '/From: (?<from>.*?)\r?\nSubject: (?<subject>.*?)\r?\n\r?\n(?<text>.*)/sim', $mail, $mail );
		$mail['text'] = str_replace( '%validationString%', $this->validationString, $mail['text'] );
		$mail['text'] = str_replace( '%siteName%', Config::$siteName, $mail['text'] );
		$mail['text'] = str_replace( '%frontendPath%', Config::$frontendPath, $mail['text'] );
		$mail['text'] = str_replace( '%userName%', $_POST['name'], $mail['text'] );
		
		$mail['subject'] = str_replace( '%siteName%', Config::$siteName, $mail['subject'] );
		$mail['subject'] = str_replace( '%userName%', $_POST['name'], $mail['subject'] );
		
		mail( $_POST['email'], $mail['subject'], $mail['text'], 'FROM: '.$mail['from'] );
		return true;
	}
}

?>