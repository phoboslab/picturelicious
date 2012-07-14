<?php
/* 
	Picturelicious
	(c) 2008 Dominic Szablewski
*/

header("Content-type: text/html; charset=UTF-8");
require_once( 'lib/config.php' );

// No JS - redirect to GET search url
if( isset($_POST['q']) ) {
	header( 'location: '.Config::$absolutePath.'search/'.$_POST['q'] );
	exit;
}


require_once( 'lib/cache.php' );
$cache = new Cache( Config::$cache['path'], !empty($_GET['s']) ? $_GET['s'] : 'index' );
if( !Config::$cache['enabled'] ) {
	$cache->disable();
}

// no session or remember cookie -> get the page from cache
if(
	empty($_COOKIE[Config::$sessionCookie]) && 
	empty($_COOKIE[Config::$rememberCookie]) 
) {
	$cache->lookup();
}


require_once( 'lib/users.php' );
require_once( 'lib/db.php' );

// If the User logged in with POST or a remember cookie, we need forumops.php
// to log him in the forum, too.
if( 
	isset($_POST['login']) || 
	(
		empty($_COOKIE[Config::$sessionCookie]) && 
		!empty($_COOKIE[Config::$rememberCookie])
	) 
) {
	if( Config::$vbbIntegration['enabled'] ) { require_once( 'lib/class.forumops.php' ); }
}
$user = new User();
$user->login();

if( $user->id ) {
	// Don't cache pages for logged-in users
	$cache->disable();
}

$messages = array();
$query = !empty($_GET['s']) ? $_GET['s'] : '';
$r = explode( '/', $query );


if( $r[0] == 'login' ) { //---------------------------------------- login
	if( Config::$vbbIntegration['enabled'] ) { require_once( 'lib/class.forumops.php' ); }
	if( !empty( $r[1] ) ) {
		if( $user->validate($r[1]) ) {
			header( 'location: '.Config::$absolutePath );
		} else {
			header( 'location: '.Config::$absolutePath.'login' );
		}
		exit(0);
	}
	if( $user->id ) {
		header( 'location: '.Config::$absolutePath );
		exit();
	}
	
	if( isset($_POST['login']) ) {
		$messages['wrongLogin'] = true;
	}
	include( Config::$templates.'login.tpl.php' );
}

else if( $r[0] == 'static' ) {
	preg_match('/(\w+)/', $r[1], $m );
	if( !empty($m[1]) && file_exists('static/'.$m[1].'.html.php') ) {
		include( Config::$templates.'header.tpl.php' );
		include( 'static/'.$m[1].'.html.php' );
		include( Config::$templates.'footer.tpl.php' );
	} else {
		header( 'HTTP/1.0 404 Not Found' );
		include( Config::$templates.'404.tpl.php' );
	}
}

else if( $r[0] == 'logout' ) { //---------------------------------- logout
	if( Config::$vbbIntegration['enabled'] ) { require_once( 'lib/class.forumops.php' ); }
	$user->logout();
	header( 'location: '.Config::$absolutePath );
	exit();
} 

else if( $r[0] == 'register' ) { //-------------------------------- new user
	if( Config::$vbbIntegration['enabled'] ) { require_once( 'lib/class.forumops.php' ); }
	if( $user->id ) {
		header( 'location: '.Config::$absolutePath );
		exit();
	}
	
	if( isset($_POST['register']) && $user->register( $messages ) ) {
		include( Config::$templates.'registered.tpl.php' );
	} else {
		include( Config::$templates.'register.tpl.php' );
	}
}

else if( //----------------------------------------------------- view
	($r[0] == 'all' && $r[1] == 'view') ||
	( in_array($r[0], array('user', 'search')) && $r[2] == 'view')
) {
	require_once( 'lib/imageviewer.php' );
	$iv = new ImageViewer();
	
	if( $r[0] == 'all' ) { // /all/view/2007/09/hans
		$iv->setCurrent( $r[2].'/'.$r[3].'/'.$r[4] );
	}
	else if( $r[0] == 'user' ) { // /user/name/view/2007/09/hans
		$iv->setCurrent( $r[3].'/'.$r[4].'/'.$r[5] );
		$iv->setUser( $r[1] );
	}
	else if( $r[0] == 'search' ) { // /search/term/view/2007/09/hans
		list( $term, $keyword ) = explode( '/view/', str_ireplace('search/', '', $query) );
		$iv->setCurrent( $keyword );
		$iv->setSearch( $term );
	}
	$iv->load();
	
	// Add comment if we have one
	if( $_POST['addComment'] && $iv->addComment($user->id, $_POST['content']) ) {
		$cache->clear( $iv->image['keyword'] );
		header( 'Location: '.Config::$absolutePath.$iv->basePath.'view/'.$iv->image['keyword'] );
		exit();
	}
	
	if( !empty($iv->image['id']) ) {
		$iv->loadComments();
		
		$cache->capture();
		include( Config::$templates.'view.tpl.php' );
	} else {
		header( 'HTTP/1.0 404 Not Found' );
		include( Config::$templates.'404.tpl.php' );
	}
}

else if( //----------------------------------------------------- browse
	empty($r[0]) || 
	in_array( $r[0], array('all', 'user', 'search') )
) {
	require_once( 'lib/imagebrowser.php' );
	$ib = new ImageBrowser( Config::$images['thumbsPerPage'] );
	
	if( empty($r[0]) || $r[0] == 'all' ) { // /all/page/2
		$ib->setPage( $r[2] );
	}
	else if( $r[0] == 'user' ) { // /user/name/page/2
		$ib->setPage( $r[3] );
		$ib->setUser( $r[1] );
	}
	else if( $r[0] == 'search' ) { // /search/term/page/2
		list( $term, $page ) = explode( '/page/', str_ireplace('search/', '', $query) );
		$ib->setPage( $page );
		$ib->setSearch( $term );
	}
	
	$ib->load();
	if( !empty($ib->thumbs) ) {
		require_once( 'lib/gridview.php' );
		$gv = new GridView( Config::$gridView['gridWidth'] );
		$gv->solve( $ib->thumbs );
		
		$cache->capture();
		include( Config::$templates.'browse.tpl.php' );
	} else {
		header( 'HTTP/1.0 404 Not Found' );
		include( Config::$templates.'404.tpl.php' );
	}
}

else if( $r[0] == 'random' ) {
	$count = max( 1, min( Config::$maxRandomThumbs, intval($r[1]) ) );
	$size = '';
	foreach( Config::$gridView['classes'] as $c ) {
		if( $r[2] == $c['dir'] ) {
			$size = $r[2];
			break;
		}
		if( empty($size) ) {
			$size = $c['dir'];
		}
	}

	require_once( 'lib/imagebrowser.php' );
	$ib = new ImageBrowser( $count );
	$ib->loadRandom( Config::$minRandomScore, $size );
	
	$cache->forceEnable();
	$cache->capture();
	include( Config::$templates.'random.js.php' );
}

else if( $r[0] == 'upload' ) {
	if( $user->id ) {
		$uploadErrors = array();
		if( $user->isSpamLocked() ) {
			$uploadErrors[] = 'No more than 10 images in 2 hours!';
		}
		else if( !empty( $_POST ) ) {
			require_once( 'lib/imageuploader.php' );
			if( 
				( 
					!empty($_POST['url']) && 
					ImageUploader::copyFromUrl( $_POST['url'], $_POST['tags'], false, $uploadErrors) 
				) ||
				( 
					!empty($_FILES['image']['name']) && 
					ImageUploader::process($_FILES['image']['name'], $_FILES['image']['tmp_name'], $_POST['tags'], true, $uploadErrors)
				)
			) {
				$cache->clear();
				$user->logUpload();
				header( "Location: ".Config::$absolutePath );
				exit(0);
			}
		}
		include( Config::$templates.'upload.tpl.php' );
	} else {
		header( 'location: '.Config::$absolutePath.'login' );
	}
}

else if( $r[0] == 'profile' ) {
	if( $user->id ) {
		if( Config::$vbbIntegration['enabled'] ) { require_once( 'lib/class.forumops.php' ); }
		$messages = array();
		if( !empty( $_POST ) ) {
			$user->profile( $_FILES['avatar']['tmp_name'], $messages );
		
			if( empty( $messages ) ) {
				header( 'location: '.Config::$absolutePath );
			}
		}
		$user->loadEmail();
		include( Config::$templates.'profile.tpl.php' );
	} else {
		header( 'location: '.Config::$absolutePath.'login' );
	}
}

else if( $r[0] == 'users' ) {
	require_once( 'lib/userlist.php' );
	$ul = new UserList( Config::$usersPerPage );
	$ul->setPage( $r[2] );
	$ul->load();
	
	$cache->capture();
	include( Config::$templates.'userlist.tpl.php' );
}

else if( $r[0] == 'quicktags' ) { //------------------------------------------- quicktagger
	if( $user->id ) {
		include( Config::$templates.'quicktags.tpl.php' );
	} else {
		header( 'location: '.Config::$absolutePath.'login' );
	}
}

else { //------------------------------------------------------- 404
	header( 'HTTP/1.0 404 Not Found' );
	include( Config::$templates.'404.tpl.php' );
	$cache->disable();
}

$cache->write();


?>