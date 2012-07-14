<?php 

header("Content-type: text/html; charset=UTF-8");
require_once( 'lib/config.php' );
require_once( 'lib/users.php' );
require_once( 'lib/db.php' );

$user = new User();
$user->login();

$status = 'ready';
if( $user->id && !$user->isSpamLocked() && !empty($_GET['url'])) {
	$uploadErrors = array();
	require_once( 'lib/imageuploader.php' );
	if(
		ImageUploader::copyFromUrl( $_GET['url'], '', $_SERVER['HTTP_REFERER'], $uploadErrors) 
	) {
		$status = 'posted';
		$user->logUpload();
	} else {
		$status = 'failed';
	}
}

include( Config::$templates.'remotepost.tpl.php' );

?>