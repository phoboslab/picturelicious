<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title><?php echo Config::$siteTitle ?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo Config::$absolutePath; ?>media/styles.css" />
	<link rel="Shortcut Icon" href="<?php echo Config::$absolutePath; ?>media/favicon.ico"/>
	<script type="text/javascript" src="<?php echo Config::$absolutePath; ?>media/general.js"></script>
</head>
<body style="margin: 0.2em 1em 0 1em; padding: 0;">

<h1 class="remotePost"><?php echo Config::$siteName ?></h1>

<?php if( !$user->id ) { ?>
	<form action="remotepost.php" method="post">
			<em>Name:</em>
			<input type="text" name="name" style="width: 80px; margin-right: 10px" value="<?php echo htmlspecialchars($_POST['name']); ?>"/>
			
			<em>Passwort:</em>
			<input type="password" style="width: 80px; margin-right: 10px" name="pass" />
		
			<input type="submit" class="button login" name="login" value="Login" />
		<input type="hidden" name="remember" value="1"/>
	</form>
<?php } else if( $user->isSpamLocked() ) { ?>
	<p>Sorry, you are not allowed to post more than 10 Images in 2 Hours.</p>
<?php } else if( $status == 'ready' ) { ?>
	<p>Please click on the Image you want to post!</p>
<?php } else if( $status == 'failed' ) { ?>
	<p>Sorry, the Image could not be posted for the following reason:</p>
	<p><strong><?php echo implode(' ', $uploadErrors); ?></strong></p>
<?php } else if( $status == 'posted' ) { ?>
	<p>Image posted! Thank you!</p>
	<script type="text/javascript">
		if( parent ) {
			parent.location = "<?php echo addslashes($_GET['xhrLocation']) ?>#RP_Success";
		}
	</script>
<?php } ?>

</body>
</html>