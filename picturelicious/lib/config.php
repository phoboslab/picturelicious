<?php

/*
	All config settings are defined in this file. Please refer to your readme
	for an in depth explanation of each value.
*/

class Config {

	// Names and URLs for your installation
	public static $absolutePath = '/';
	public static $frontendPath = 'http://picturelicious.net/';
	public static $siteName = 'Picturelicious';
	public static $siteTitle = 'Picturelicious - Social Imaging';

	public static $sessionCookie = 'sid';
	public static $rememberCookie = 'remember';
	
	// Spam protection
	public static $ipLockTime = 3600;
	public static $uploadLockTime = 7200;
	public static $maxNumUploads = 10;
	
	// User score
	public static $votingScore = 5;
	public static $postScore = 10;
	public static $tagScorePerChar = 0.5;
	public static $tagScoreMax = 20;
	
	// Misc settings
	public static $usersPerPage = 50;
	public static $defaultChmod = 0777;
	public static $templates = 'templates/';
	public static $keywordWordSeperator = '-';
	
	public static $colorSearchDev = 50;

	public static $maxRandomThumbs = 10;
	public static $minRandomScore = 4.0;
	
	// vbb Forum integration
	public static $vbbIntegration = array(
		'enabled' => false,
		'path' => '/../forum'
	);
	
	// Cache settings
	public static $cache = array(
		'enabled' => true,
		'path' => 'cache/',
		'clearEvery' => 10
	);
	
	// Database settings
	public static $db = array(
		'host' => 'localhost',
		'database' => 'picturelicious',
		'user' => 'root',
		'password' => '',
		'prefix' => 'pl_'
	);
	
	// Image processing and thumbnail creation
	public static $images = array(
		'thumbsPerPage' => 90,
		'imagePath' => 'data/images/',
		'thumbPath' => 'data/thumbs/',
		'avatarsPath' => 'data/avatars/',
		'maxDownload' => 2097152,
		'jpegQuality' => 80,
		'sharpen' => true
	);
	
	// Grid Solver settings
	public static $gridView = array(
		'gridSize' => 64,
		'gridWidth' => 12,
		'borderWidth' => 2,
		
		// Definition of all Thumbnail classes ---------------------------
		// KEY: 			CSS-className
		// width/ height: 	Size of the thumb in grid units
		// percentage:		Percentage of thumbnails in this size
		// dir: 			Name of the subdir with thumbs in this size
		'classes' => array(
			'b2' => array( 'width'=>1, 'height'=>1,	'percentage'=>0.6,	'dir'=>'64x64' ),
			'b1' => array( 'width'=>2, 'height'=>2,	'percentage'=>0.35,	'dir'=>'128x128' ),
			'b0' => array( 'width'=>3, 'height'=>3,	'percentage'=>0.05,	'dir'=>'192x192' )
		),
	);
}


// Edit below this line only if you know what you're doing!
// -----------------------------------------------------------------------

// MySQL Table names
define( 'TABLE_IMAGES', 	Config::$db['prefix'].'images' );
define( 'TABLE_IMAGECOLORS',Config::$db['prefix'].'imagecolors' );
define( 'TABLE_USERS', 		Config::$db['prefix'].'users' );
define( 'TABLE_IPLOCK',		Config::$db['prefix'].'iplock' );
define( 'TABLE_UPLOADLOCK',	Config::$db['prefix'].'uploadlock' );
define( 'TABLE_TAGLOG',		Config::$db['prefix'].'taglog' );
define( 'TABLE_COMMENTS',	Config::$db['prefix'].'comments' );

// MagicQuotes undo
if( function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc() ) {
	$_GET = array_map( 'stripslashes', $_GET );
	$_POST = array_map( 'stripslashes', $_POST );
}

?>