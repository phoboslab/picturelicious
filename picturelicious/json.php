<?php 
require_once( 'lib/config.php' );
require_once( 'lib/users.php' );
require_once( 'lib/db.php' );

$user = new User();
$user->login();

require_once( 'lib/cache.php' );
$cache = new Cache( Config::$cache['path'], ''  );

if( isset($_GET['addTags']) && $user->id && strlen(trim($_POST['tags'])) >= 3 ) {
	$i = DB::getRow( 'SELECT id, tags, keyword FROM '.TABLE_IMAGES.' WHERE id = :1', $_POST['id'] );
	if( !empty( $i ) ) {
		if($user->admin) {
			$tags = trim($_POST['tags']);
		} else {
			$tags = trim(trim($i['tags'])." ".trim($_POST['tags']));
		}
		DB::updateRow( TABLE_IMAGES, 
			array('id' => $i['id']), 
			array( 'tags' => $tags ) 
		);
		
		$score  = strlen(trim($_POST['tags'])) * Config::$tagScorePerChar;
		$user->increaseScore( min($score, Config::$tagScoreMax) );
		$cache->clear( $i['keyword'] );
		
		echo '{"id":"'.$i['id'].'","tags":"'.addslashes(htmlspecialchars($tags)).'"}';
		exit(0);
	}
} 

if( isset($_GET['quickTag']) && $user->id ) {
	if( !empty($_POST['id']) ) {
	
		// remove lock
		DB::updateRow( TABLE_TAGLOG,
			array( 'id' => $_POST['logId'], 'userId' => $user->id ),
			array( 'locked' => 0 )
		);
		
		if( strlen(trim($_POST['tags'])) >= 3 ) {
			$i = DB::getRow( 'SELECT id, tags, keyword FROM '.TABLE_IMAGES.' WHERE id = :1', $_POST['id'] );
			if( !empty( $i ) ) {
				$tags = trim(trim($i['tags'])." ".trim($_POST['tags']));
				DB::updateRow( TABLE_IMAGES, 
					array('id' => $i['id']), 
					array( 'tags' => $tags ) 
				);
			}
			$score  = strlen(trim($_POST['tags'])) * Config::$tagScorePerChar;
			$user->increaseScore( min($score, Config::$tagScoreMax) );
			$cache->clear( $i['keyword'] );
		}
	}

	$i = DB::getRow(
		'SELECT i.id, i.keyword, i.tags, i.image, UNIX_TIMESTAMP(i.logged) AS loggedTS
			FROM '.TABLE_IMAGES.' i
			LEFT JOIN '.TABLE_TAGLOG.' t
				ON t.imageId = i.id AND (
					(
						t.userId = :1 AND
						t.tagged > NOW() - INTERVAL 24 HOUR
					) OR (
						t.locked = 1 AND
						t.tagged > NOW() - INTERVAL 5 MINUTE
					)
				)
			WHERE t.id IS NULL
		ORDER BY LENGTH(i.tags), i.logged DESC
		LIMIT 1',
		$user->id
	);
	if( !empty( $i ) ) {
		DB::insertRow( TABLE_TAGLOG, 
			array(
				'tagged' => date( 'Y.m.d H:i:s' ),
				'userId' => $user->id,
				'imageId' => $i['id'],
				'locked' => 1
			)
		);
		$logId = DB::insertId();
		$link = Config::$absolutePath.'all/view/'.$i['keyword'];
		$image = 
			Config::$absolutePath
			.Config::$images['imagePath']
			.date('Y/m/', $i['loggedTS'])
			.$i['image'];
		
		echo '{"id":"'.$i['id'].'","tags":"'.addslashes(htmlspecialchars($i['tags'])).'"'
			.',"link":"'.$link.'","image":"'.$image.'","logId":"'.$logId.'"}';
		exit(0);
	}
} 

else if( isset($_GET['delete']) && $user->id && $user->admin ) {
	$i = DB::getRow( 'SELECT id, logged, image, thumb, user FROM '.TABLE_IMAGES.' WHERE id = :1', $_POST['id'] );
	if( !empty($i) ) {
		DB::query( 'DELETE FROM '.TABLE_IMAGES.' WHERE id = :1', $_POST['id'] );
		DB::query( 'DELETE FROM '.TABLE_IMAGECOLORS.' WHERE imageId = :1', $_POST['id'] );
		DB::query( 'DELETE FROM '.TABLE_COMMENTS.' WHERE imageId = :1', $_POST['id'] );
		foreach( Config::$gridView['classes'] as $c ) {
			unlink( 
				Config::$images['thumbPath'] . 
				str_replace( '-', '/', substr( $i['logged'], 0, 7 ) ).'/'. 
				$c['dir'].'/'. 
				$i['thumb'] 
			);
		}
		
		unlink( 
			Config::$images['imagePath'] . 
			str_replace( '-', '/', substr( $i['logged'], 0, 7 ) ).'/'. 
			$i['image']
		);
		
		DB::query( 
			'UPDATE '.TABLE_USERS.' SET images = images -1, score = score - :2 
			WHERE id = :1', 
			$i['user'], Config::$postScore 
		);
		$cache->clear();
	}
	echo "{}";
}

else if( isset($_GET['deleteComment']) && $user->id && $user->admin ) {
	DB::query( 'DELETE FROM '.TABLE_COMMENTS.' WHERE id = :1', $_POST['id'] );
	$cache->clear();
	echo "{}";
}

else if( isset($_GET['rate']) ) {
	// score is in valid range / not float?
	if( in_array( $_POST['score'], array(1,2,3,4,5) ) ) {
	
		// check if this ip has allready rated this quote
		list(, $ip) = unpack('l',pack('l',ip2long($_SERVER['REMOTE_ADDR'])));
		$r = DB::getRow( 'SELECT ip FROM '.TABLE_IPLOCK.' 
			WHERE ip = :1 AND imageId = :2', 
			$ip, $_POST['id']
		);
		
		// not rated?
		if( empty( $r ) ) {
			// insert ip
			DB::insertRow( TABLE_IPLOCK, 
				array( 
					'ip' => $ip, 
					'imageId' => $_POST['id'],
					'ts' => time()
				) 
			);
			
			// update score
			DB::query( 'UPDATE '.TABLE_IMAGES.' SET 
					score = ( score * votes + :1 ) / ( votes + 1 ),
					votes = votes + 1
				WHERE id = :2',
				$_POST['score'], $_POST['id']
			);
			
			$user->increaseScore( Config::$votingScore );
			
			if( rand(1, Config::$cache['clearEvery']) == 1 ) {
				$cache->clear();
			}
			else { 
				$i = DB::getRow( 'SELECT keyword FROM '.TABLE_IMAGES.' WHERE id = :1', $_POST['id'] );
				$cache->clear( $i['keyword'] );
			}
		}
	}
	
	// delete all ips older than IP_LOCK_TIME
	DB::query( 'DELETE FROM '.TABLE_IPLOCK.' WHERE ts < :1', time() - Config::$ipLockTime );
	
	// return JSON Data
	header( 'Content-Type', 'text/plain' );
	$r = DB::getRow( 'SELECT id, score, votes FROM '.TABLE_IMAGES.' WHERE id = :1', $_POST['id'] );
	echo '{"id":"'.$r['id'].'","score":"'.number_format($r['score'],1).'","votes":"'.($r['votes']).'"}';
	exit(0);
}

echo "{}";
?>