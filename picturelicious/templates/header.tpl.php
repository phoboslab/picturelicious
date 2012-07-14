<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
	<title><?php if( $iv && $iv->image && $iv->image['tags']){ echo htmlspecialchars($iv->image['tags'])?> - <?php } ?><?php echo Config::$siteTitle; ?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo Config::$absolutePath; ?>media/styles.css" />
	<link rel="icon" href="<?php echo Config::$absolutePath; ?>media/favicon.ico"/>
	<script type="text/javascript" src="<?php echo Config::$absolutePath; ?>media/picturelicious.js"></script>
</head>
<body>

<div id="menu">
	<a href="<?php echo Config::$absolutePath; ?>" id="home"><img title="Home" id="logo" alt="" src="<?php echo Config::$absolutePath; ?>media/logo.png"/></a>
	
	<div class="search">
		<form action="<?php echo Config::$absolutePath; ?>" method="post" onsubmit="return s()">
			<input type="text" name="q" value="<?php echo htmlspecialchars($term); ?>" id="q"/>
			<input type="button" class="color" value="" onclick="swap($('colorpicker'),'hidden','visible')"/>
			<input type="submit" name="search" class="search" value="search"/>
			
			<div id="colorpicker" class="hidden">
				<div id="colorpickerCurrent"></div>
				<div id="colorpickerValue">#FFFFFF</div>
				<div id="colorpickerSV"><div id="colorpickerSVSelect"></div></div>
				<div id="colorpickerH"><div id="colorpickerHSelect"></div></div>
			</div>
			<script type="text/javascript">
				var cp = new colorpicker( 'colorpicker', 64, function(cp) {$('q').value = '0x' + cp.getHex();} );
			</script>
		</form>
	</div>
	
	<div class="menuItems">
		<a href="<?php echo Config::$absolutePath; ?>upload">Upload</a>
		<a href="<?php echo Config::$absolutePath; ?>users">Users</a>
		<a href="<?php echo Config::$absolutePath; ?>quicktags">Quick-Tagging</a>
		<a href="<?php echo Config::$absolutePath; ?>static/bookmarklet">Bookmarklet</a> 
		<?php if( Config::$vbbIntegration['enabled'] ) { ?>
			<a href="<?php echo Config::$absolutePath; ?>forum/">Forum</a>
		<?php } ?>
	</div>

	<div class="userMenu">
		<?php if( $user->id ) { ?>
			Hello
			<a href="<?php echo Config::$absolutePath; ?>user/<?php echo $user->name; ?>"><?php echo $user->name; ?></a>
			(<a href="<?php echo Config::$absolutePath; ?>profile">profile</a> /
			<a href="<?php echo Config::$absolutePath; ?>logout">logout</a>)
		<?php } else { ?>
			Hello Anonymous 
			(<a href="<?php echo Config::$absolutePath; ?>login">login</a> / 
			<a href="<?php echo Config::$absolutePath; ?>register">register</a>)
		<?php } ?>
		<?php if( $user->admin ) { ?>
			<br/>Admin:
			<a href="<?php echo Config::$absolutePath; ?>comments.php">Comments</a> /
			<a href="<?php echo Config::$absolutePath; ?>imageimport.php">Import</a>
		<?php } ?>
	</div>
	
	<div style="clear:both;"></div>
</div>

<div id="content">




