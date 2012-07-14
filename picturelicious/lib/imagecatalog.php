<?php

/*
	ImageCatalog is the abstract base class for ImageBrowser and ImageViewer. It provides
	the basic functionality to help build queries to get the images out of the database.
*/

require_once( 'lib/config.php' );
require_once( 'lib/db.php' );

class ImageCatalog {
	public $user = array();
	public $searchTerm = '';
	public $searchColor = array();
	public $totalResults = 0;
	
	public $basePath = 'all/';
	
	public function setUser( $name ) {
		$this->user = DB::getRow(
			'SELECT id, name, score, images, avatar, website
				FROM '.TABLE_USERS.' 
				WHERE 
					valid = 1 AND 
					name = :1', 
			$name
		);
		if( !empty($this->user) ) {
			$this->basePath = 'user/'.$this->user['name'].'/';
		}
	}
	
	public function setSearch( $term ) {
		
		$this->basePath = 'search/'.htmlspecialchars($term).'/';
		
		if( preg_match( '/^\s*0x([0-9a-f]{6})\s*$/i', $term, $m ) ) {
			$c = str_split( $m[1], 2 );
			$this->searchColor = array(
				'r' => hexdec($c[0]),
				'g' => hexdec($c[1]),
				'b' => hexdec($c[2]),
			);
		}
		else if( !empty($term) ) {
			$this->searchTerm = $term;
			$ftq = preg_replace( '/\s+/',' +', $this->searchTerm );
			$this->seachCondition .= " AND (
				i.image LIKE ".DB::quote('%'.$this->searchTerm.'%')."
				OR MATCH( i.tags ) AGAINST ( ".DB::quote($ftq)." IN BOOLEAN MODE )
			)";
		}
	}
	
	

	public function load() {
	}
}

?>