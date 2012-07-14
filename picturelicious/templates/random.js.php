<?php
header( 'Content-type: text/javascript; charset=utf-8' ); 
?>
<?php foreach($ib->thumbs as $t ) { ?>
	document.write('<a href="<?php echo Config::$frontendPath; ?>all/view/<?php echo $t['keyword']; ?>" target="_blank"><img border="0" src="<?php echo Config::$frontendPath; ?><?php echo $t['thumb']; ?>" alt=""/></a>');
<?php } ?>