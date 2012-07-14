<?php

/*
	Various functions regarding thumbnail creation and color extraction
*/

require_once( 'lib/config.php' );

class Image {
	
	public static function createThumb( $imgPath, $thumbPath, $thumbWidth, $thumbHeight ) {
		list( $srcWidth, $srcHeight, $type ) = getimagesize( $imgPath );
		$srcX = 0;
		$srcY = 0;
		if( 
			$srcWidth < 1 || $srcWidth > 4096
			|| $srcHeight < 1 || $srcHeight > 4096
		) {
			return false;
		}
		
		if( $type == IMAGETYPE_JPEG ) {
			$imgcreate = 'ImageCreateFromJPEG';
		} else if( $type == IMAGETYPE_GIF ) {
			$imgcreate = 'ImageCreateFromGIF';
		} else if( $type == IMAGETYPE_PNG ) {
			$imgcreate = 'ImageCreateFromPNG';
		} else {
			return false;
		}
		
		$thumbDirName = dirname( $thumbPath );

		if( ( $srcWidth/$srcHeight ) > ( $thumbWidth/$thumbHeight ) ) {
			$zoom = ($srcWidth/$srcHeight) / ($thumbWidth/$thumbHeight);
			$srcX = ($srcWidth - $srcWidth / $zoom) / 2;
			$srcWidth = $srcWidth / $zoom;
		}
		else {
			$zoom = ($thumbWidth/$thumbHeight) / ($srcWidth/$srcHeight);
			$srcY = ($srcHeight - $srcHeight / $zoom) / 2;
			$srcHeight = $srcHeight / $zoom;
		}
		
		$thumb = Imagecreatetruecolor( $thumbWidth, $thumbHeight );
		$orig = $imgcreate( $imgPath );
		Imagecopyresampled($thumb, $orig, 0, 0, $srcX, $srcY, $thumbWidth, $thumbHeight, $srcWidth, $srcHeight);
		
		if( Config::$images['sharpen'] && function_exists('imageconvolution') ) {
			$sharpenMatrix = array( array(-1,-1,-1), array(-1,16,-1), array(-1,-1,-1) );
			imageconvolution($thumb, $sharpenMatrix, 8, 0);
		}
		imagejpeg( $thumb, $thumbPath, Config::$images['jpegQuality'] );

		// clean up
		Imagedestroy( $thumb );
		Imagedestroy( $orig );
			
		return true;
	}
	
	public static function getColors( $imgPath, $size ) {
		list( $srcWidth, $srcHeight, $type ) = getimagesize( $imgPath );
		
		if( $type == IMAGETYPE_JPEG ) {
			$imgcreate = 'ImageCreateFromJPEG';
		} else if( $type == IMAGETYPE_GIF ) {
			$imgcreate = 'ImageCreateFromGIF';
		} else if( $type == IMAGETYPE_PNG ) {
			$imgcreate = 'ImageCreateFromPNG';
		} else {
			return array();
		}

		$orig = $imgcreate( $imgPath );
	
		$thumb = Imagecreatetruecolor( $size, $size );
		Imagecopyresampled($thumb, $orig, 0, 0, 0, 0, $size, $size, $srcWidth, $srcHeight);
		
		$colors = array();
		for( $x = 0; $x < $size; $x++ ) {
			for( $y = 0; $y < $size; $y++ ) {
				$rgb = ImageColorAt($thumb, $x, $y);
				$r = ($rgb >> 16) & 0xFF;
				$g = ($rgb >> 8) & 0xFF;
				$b = $rgb & 0xFF;
				$colors[] = array( 
					'r' => $r, 
					'g' => $g, 
					'b' => $b
				);
			}
		}
		return $colors;
	}
}

?>