<?php

/*
	ImageViewer loads a single image and its comments specified by a keyword
*/

require_once( 'lib/imagecatalog.php' );

class ImageViewer extends ImageCatalog {
	protected $position = 0;
	protected $keyword = '';
	
	public $image = array();
	public $stream = array();
	public $userInfo = array();
	
	public function setCurrent( $keyword ) {
		$this->keyword = $keyword;
	}
	
	public function addComment( $userId, $comment ) {
		if( $this->image['id'] && $userId && !empty($comment) ) {
			DB::insertRow( TABLE_COMMENTS, array(
				'imageId' => $this->image['id'],
				'userId' => $userId,
				'created' => date( 'Y.m.d H:i:s' ),
				'content' => $comment
			));
			return true;
		} else {
			return false;
		}
	}
	
	public function load() {
		$conditions = '';
		if( !empty($this->user) ) {
			$conditions .= ' AND i.user = :2';
			$this->userInfo = $this->user;
		}
		
		
		$this->image = DB::getRow(
			'SELECT
				i.id, i.logged, UNIX_TIMESTAMP(i.logged) AS loggedTS,
				i.keyword, i.image, i.score, i.votes,
				tags, u.name AS userName, i.user
			FROM '.TABLE_IMAGES.' i
			LEFT JOIN '.TABLE_USERS.' u
				ON u.id = i.user
			WHERE i.keyword = :1 '.$conditions,
			$this->keyword,
			$this->user['id']
		);
		$this->image['path'] = date('Y/m/', $this->image['loggedTS']).$this->image['image'];
		
		if( empty( $this->user) ) {
			$this->userInfo = DB::getRow(
				'SELECT id, name, score, images, avatar, website
					FROM '.TABLE_USERS.' 
					WHERE 
						id = :1', 
				$this->image['user']
			);
		}
		
		// compute previoues and next page
		$next = DB::getRow(
			'SELECT i.keyword
			FROM '.TABLE_IMAGES.' i
			WHERE i.id < :1 '.$conditions.'
			ORDER BY id DESC
			LIMIT 1',
			$this->image['id'],
			$this->user['id']
		);
		
		$prev = DB::getRow(
			'SELECT i.keyword
			FROM '.TABLE_IMAGES.' i
			WHERE i.id > :1 '.$conditions.'
			ORDER BY id ASC
			LIMIT 1',
			$this->image['id'],
			$this->user['id']
		);
		
		
		
		if( !empty($next) ) {
			$this->stream['next'] = $next['keyword'];
		}
		if( !empty($prev) ) {
			$this->stream['prev'] = $prev['keyword'];
		}
	}
	
	public function loadComments() {
		$this->comments = DB::query(
			'SELECT 
				c.id, c.content, u.name, u.avatar,
				UNIX_TIMESTAMP(c.created) AS created
			FROM '.TABLE_COMMENTS.' c
			LEFT JOIN '.TABLE_USERS.' u
				ON u.id = c.userId
			WHERE c.imageId = :1
			ORDER BY created',
			$this->image['id']
		);
		$this->commentCount = count( $this->comments );
		
		foreach( array_keys($this->comments) as $i ) {
			$this->comments[$i]['content'] = preg_replace( 
				'#(?<!\w)((http://)|(www\.))([^\s<>]+)#i', 
				"<a href=\"http://$3$4\">$3$4</a>", 
				nl2br( htmlspecialchars($this->comments[$i]['content']) )
			);
		}
	}
}

?>