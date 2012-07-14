<?php

/*
	The UserList class loads a number of users from the database, specified by a page
*/

require_once( 'lib/config.php' );
require_once( 'lib/db.php' );

class UserList {
	protected $page = 0;
	
	protected $usersPerPage = 0;
	protected $totalResults = 0;
	
	public $users = array();
	public $pages = array();
	
	public function __construct( $usersPerPage ) {
		$this->usersPerPage = abs(intval($usersPerPage));
	}
	
	public function setPage( $page ) {
		$page = intval($page);
		$this->page = $page > 0 ? $page - 1 : 0;
	}
	
	public function load() {
		$this->users = DB::query( 
			'SELECT SQL_CALC_FOUND_ROWS 
				UNIX_TIMESTAMP( u.registered ) as registered, 
				u.name, u.score, u.images, u.avatar, u.website
			FROM '.TABLE_USERS.' u
			WHERE valid = 1
			GROUP BY u.id
			ORDER BY u.score DESC
			LIMIT :1, :2',
			$this->page * $this->usersPerPage, 
			$this->usersPerPage
		);
		$this->totalResults = DB::foundRows();
		
		// compute previoues, current and next page
		if( $this->totalResults > 0 ) {
			$this->pages['current'] = $this->page+1;
			$this->pages['total'] = ceil($this->totalResults / $this->usersPerPage);
			if( $this->page > 0 ) {
				$this->pages['prev'] = $this->page;
			}
			if( $this->totalResults > $this->usersPerPage * $this->page + $this->usersPerPage ) {
				$this->pages['next'] = $this->page + 2;
			}
		}
	}
}

?>