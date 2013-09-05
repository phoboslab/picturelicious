<?php 
require_once( 'lib/config.php' );
require_once( 'lib/users.php' );
require_once( 'lib/db.php' );

header("Content-type: text/html; charset=UTF-8");
$user = new User();
$user->login();

if( !$user->admin ) {
	echo "You need to be logged in as an admin user!";
	exit();
}


$newComments = DB::query( 
	'SELECT 
		c.id, c.content, u.name, u.avatar,
		UNIX_TIMESTAMP(c.created) AS created,
		i.keyword FROM '.TABLE_COMMENTS.' c
	LEFT JOIN '.TABLE_USERS.' u
		ON u.id = c.userId
	LEFT JOIN '.TABLE_IMAGES.' i
		ON i.id = c.imageId
	ORDER BY c.created DESC LIMIT 100'
);
include( 'templates/header.tpl.php' );
?>
<h2>Newest Comments:</h2>
<div style="width: 700px;">
	<?php foreach( $newComments as $c ) {?>
		<div class="comment">
			<div class="commentHead">
				<img class="avatarSmall" width="16" height="16" src="<?php echo Config::$absolutePath.$c['avatar']; ?>"/>
				<a href="<?php echo Config::$absolutePath.'user/'.$c['name']; ?>"><?php echo $c['name']; ?></a>
				at <?php echo date('d. M Y H:i',$c['created']); ?> 
				[bild:<a href="<?php echo Config::$absolutePath.'all/view/'.$c['keyword']; ?>"><?php echo $c['keyword']; ?></a>]
				<?php if($user->admin) { ?>
					<div style="float:right;" id="del">
						<a href="#" onclick="return delComment(<?php echo $c['id']; ?>, this)">[x]</a>
					</div>
				<?php } ?>
			</div>
			<?php echo htmlspecialchars($c['content']); ?>
		</div>
	<?php } ?>
</div>


<?php 
include( 'templates/footer.tpl.php' );
?>