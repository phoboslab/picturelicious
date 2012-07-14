<?php  

require_once( 'lib/config.php' );
require_once( 'lib/users.php' );
require_once( 'lib/db.php' );
require_once( 'lib/imageuploader.php' );

header("Content-type: text/html; charset=UTF-8");
$user = new User();
$user->login();

if( !$user->admin ) {
	echo "You need to be logged in as an admin user!";
	exit();
}
set_time_limit(0);
$files = glob( 'import/*.*' );


include( 'templates/header.tpl.php' );


if( isset($_POST['import']) ) {
	echo "<h2>Imported Images:</h2><ul>";
	foreach( $files as $f ) {
		$errors = array();
		if( ImageUploader::process( basename($f), $f, '', false, $errors ) ) {
			echo "<li><a href=\"$f\">$f</a> - OK</li>";
		} else {
			echo "<li><a href=\"$f\">$f</a> - FAILED</li>";
		}
	}
	echo "</ul>";
	require_once( 'lib/cache.php' );
	$cache = new Cache( Config::$cache['path'], 'index' );
	$cache->clear();
} else {
	echo "<h2>Images ready to import:</h2>";
	if( !empty($files) ) {
		echo "<ul>";
		foreach( $files as $f ) {
			echo "<li><a href=\"$f\">$f</a></li>";
		}
		echo '</ul><form action="imageimport.php" method="post"><input type="submit" name="import" value="Import Images!"/></form>';
	} else {
		echo "<p>No images found in the <i>import/</i> directory!</p>";
	}
	
}


?>


<?php 
include( 'templates/footer.tpl.php' );
?>