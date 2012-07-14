<?php

/*
	The ImageUploader class handles the insertion of images into the system -
	whether they were uploaded or should be copied from a remote URL
*/

require_once( 'lib/config.php' );
require_once( 'lib/db.php' );
require_once( 'lib/filesystem.php' );
require_once( 'lib/images.php' );
require_once( 'lib/users.php' );
require_once( 'lib/keyword.php' );

class ImageUploader {

	public static function copyFromUrl( $url, $tags, $referer, &$errors ) {
		if( stripos( $url, 'http://' ) !== 0 ) {
			$url = 'http://'.$url;
		}
		// no recursion, no non-images
		if( 
			stripos( $url, Config::$frontendPath ) !== false ||
			!preg_match( '/([^\/]*?)\.(gif|jpg|jpeg|png)$/i', $url, $fileMatch )
		) {
			$errors['downloadFailed'] = 'The Image could not be downloaded!';
			return false;
		}
		
		
		$fileName = $fileMatch[1];
		// use the domain name as part of the keyword
		$urlParts = parse_url( $url );
		if( preg_match( '/(^|\.)([\w\-]+)\.\w+$/', $urlParts['host'], $m ) ) {
			$fileName = strtolower($m[2]).'-'.$fileName;
		}
		$fileName = Keyword::get( $fileName ).'.'.$fileMatch[2];
		$targetPath = './import/'.$fileName;
		
		if( !Filesystem::download( $url, $targetPath, Config::$images['maxDownload'], $referer ) ) {
			$errors['downloadFailed'] = 'The Image could not be downloaded!';
			return false;
		}
		
		if( self::process( $fileName, $targetPath, $tags, false, $errors, $url ) ) {
			return true;
		} else {
			@unlink( $targePath );
			return false;
		}
	}

	public static function process( $name, $localPath, $tags, $uploaded = true, &$errors, $source = '' ) {
	
		$user = new User();
		$user->login();
		
		if( !$user->id ) {
			$errors['notLoggedIn'] = 'You are not logged in!';
			return false;
		}
	
		// no non-images
		if( 
			!preg_match( '/([^\/]*?)\.(gif|jpg|jpeg|png)$/i', $name, $fileMatch )
		) {
			$errors['wrongExtension'] = 'Your file has not the right extension (gif, jpg or png)!';
			return false;
		}
		
		$time = time();
		$keyword = Keyword::get( $fileMatch[1] );
		
		// Iterate over all keywords, if this one is allready taken
		$taken = array();
		$r = DB::query( 'SELECT keyword FROM '.TABLE_IMAGES.' WHERE keyword LIKE :1', date('Y/m/',$time).$keyword.'%' );
		foreach( $r as $v ) {
			$taken[] = substr($v['keyword'],8); // Keyword without date!
		}
		$keyword = Keyword::iterate( $keyword, $taken );
		
		list( $srcWidth, $srcHeight, $type ) = getimagesize( $localPath );
		if( $type == IMAGETYPE_JPEG ) {
			$extension = 'jpg';
		} else if( $type == IMAGETYPE_GIF ) {
			$extension = 'gif';
		} else if( $type == IMAGETYPE_PNG ) {
			$extension = 'png';
		} else {
			return false;
		}
		
		$thumbName = $keyword.".jpg"; // all thumbs are jpegs!
		$name = $keyword.".".$extension;
		$imageDir = Config::$images['imagePath'] . date('Y/m', $time);
		$thumbDir = Config::$images['thumbPath'] . date('Y/m', $time);
		$imagePath = $imageDir.'/'.$name;
		
		if( !Filesystem::mkdirr($imageDir) || !Filesystem::mkdirr($thumbDir) ) {
			return false;
		}
		
		if( $uploaded ) {
			if( !@move_uploaded_file( $localPath, $imagePath ) ) {
				return false;
			}
		} else {
			if( !@rename( $localPath, $imagePath ) ) {
				return false;
			}
		}
		
		$hash = md5_file( $imagePath );
		$c = DB::query('SELECT id FROM '.TABLE_IMAGES.' WHERE hash = :1', $hash);
		if( !empty( $c ) ) {
			unlink( $imagePath );
			$errors['duplicate'] = 'This image is already in our database!';
			return false;
		}
		
		// make thumbs
		foreach( Config::$gridView['classes'] as $gc ) {
			$thumbPath = $thumbDir.'/'.$gc['dir'].'/'.$thumbName;
			Filesystem::mkdirr($thumbDir.'/'.$gc['dir']);
			if( !Image::createThumb( $imagePath, $thumbPath, 
				$gc['width'] * Config::$gridView['gridSize'] - Config::$gridView['borderWidth'], 
				$gc['height'] * Config::$gridView['gridSize'] - Config::$gridView['borderWidth']
			)) {
				unlink( $imagePath );
				$errors['internalError'] = 'Internal error processing the image!';
				return false;
			}
		}
		
		
		$user->increaseScore( Config::$postScore );
		
		DB::query( 'UPDATE '.TABLE_USERS.' SET images = images + 1 WHERE id = :1', $user->id );
		
		DB::insertRow( TABLE_IMAGES, 
			array(
				'logged' => date('Y-m-d H:i:s', $time),
				'user' => $user->id,
				'score' => 3,
				'votes' => 0,
				'keyword' => date('Y/m/', $time).$keyword,
				'tags' => $tags,
				'image' => $name,
				'thumb' => $thumbName,
				'hash' => $hash,
				'source' => $source
			)
		);
		$imageId = DB::insertId();
		
		$colors = Image::getColors( $imagePath, 4 );
		foreach( $colors as $c ) {
			DB::insertRow( TABLE_IMAGECOLORS,
				array(
					'imageId' => $imageId, 
					'r' => $c['r'], 
					'g' => $c['g'], 
					'b' => $c['b']
				)
			);
		}
		return true;
	}
}
?>