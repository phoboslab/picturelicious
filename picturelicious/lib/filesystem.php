<?php

/*
	Various functions regarding file handling
*/

require_once( 'lib/config.php' );

class Filesystem {
	
	public static function rmdirr( $dir, $clearOnly = false ) {
		$dh = opendir( $dir );
		while ( $file = readdir( $dh ) ) {
			if( $file != '.' && $file != '..' ) {
				$fullpath = $dir.'/'.$file;
				if( !is_dir( $fullpath ) ) {
					@unlink( $fullpath );
				}
				else {
					self::rmdirr( $fullpath );
				}
			}
		}
		closedir( $dh );
		
		if( !$clearOnly && @rmdir( $dir ) ) {
			return true;
		}
		else {
			return false;
		}
	}

	public static function mkdirr( $pathname ) {
		// Check if directory already exists
		if( empty($pathname) || is_dir($pathname) ) {
			return true;
		}
		if ( is_file($pathname) ) {
			return false;
		} 
		// Crawl up the directory tree
		$nextPathname = substr( $pathname, 0, strrpos( $pathname, '/' ) );
		if( self::mkdirr( $nextPathname ) ) {
			if( !file_exists( $pathname ) ) {
				$oldUmask = umask(0); 
				$success = @mkdir( $pathname, Config::$defaultChmod );
				umask( $oldUmask ); 
				return $success;
			}
		}
		return false;
	}
	
	public static function download( $url, $target, $maxSize = 2097152, $referer = false ) {
		$contents = '';
		$bytesRead = 0;
		
		if( $referer ) {
			$opts = array(
				'http'=>array(
					'method'=>"GET",
					'header'=>"Referer: $referer\r\n"
				)
			);
			$context = stream_context_create($opts);
			$fp = fopen( $url, 'r', false, $context );
		} else {
			$fp = @fopen( $url, 'r' );
		}
		if( !$fp ) {
			return false;
		}

		while ( !feof( $fp ) ) {
			$chunk = fread( $fp, 8192 );
			$bytesRead += strlen( $chunk );
			if( $bytesRead > $maxSize) {
				return false; 
			}
			$contents .= $chunk; 
		}
		fclose( $fp );
		
		file_put_contents( $target, $contents );
		return true;
	}	
}

?>