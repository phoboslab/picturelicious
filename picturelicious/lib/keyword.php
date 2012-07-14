<?php
require_once( 'lib/config.php' );

class Keyword {
	// Get a keyword from a string
	// ----------------------------------------------------------------------------
	public static function get( $kw ) {
		// narf, since the .php source files are saved as 8859-1 (php doesn't like BOMs)
		// utf8_decode is invoked, so str_replace actually finds something for these 8859-1 
		// encoded special chars
		
		$kw = utf8_decode( $kw );
		$kw = str_replace( array( 'Ä','Ö','Ü','ä','ö','ü','ß',"'" ), array( 'Ae','Oe','Ue','ae','oe','ue','ss','' ), $kw );
		$kw = preg_replace( '/[^a-zA-Z\d]+/', '#', $kw ); 
		$kw = preg_replace( '/^\#+|\#+$/', '', $kw ); // Trim seperators
		$kw = str_replace( '#', Config::$keywordWordSeperator, $kw ); // replace # with final word seperator
		
		if( $kw == "" ) {// no keyword?
			$kw = 'Untitled'; // Assign default keyword
		}
		$kw = strtolower( $kw );
		return $kw;
	}
	
	// Check if a keyword has allready been taken. if so, return $keyword-N, where N is an increasing number
	// ----------------------------------------------------------------------------	
	public static function iterate( $kw, $taken ) {
		$kwLower = strtolower( $kw );
		if( in_array( $kwLower, $taken ) ) {
			for( $i = 2; in_array( $kwLower, $taken); $i++ ) {
				$kwLower = strtolower( $kw . Config::$keywordWordSeperator . $i );
			}
			return $kw . Config::$keywordWordSeperator . ( $i-1 );
		}
		else {
			return $kw;
		}
	}
}

?>