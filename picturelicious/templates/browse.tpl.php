<?php include( $templates.'header.tpl.php' ); ?>

<h1>
	&raquo; Browsing
	<?php if( !empty($ib->user) ) { ?>
		User: <a href="<?php echo Config::$absolutePath.'user/'.$ib->user['name']; ?>"><?php echo $ib->user['name']; ?></a>
	<?php } else if( !empty($ib->channel) ) { ?>
		Channel: <a href="<?php echo Config::$absolutePath.'channel/'.$ib->channel['keyword']; ?>"><?php echo $ib->channel['name']; ?></a>,
	<?php } else if( !empty($ib->searchColor) || !empty($ib->searchTerm) ) { ?>
		Results for <?php echo htmlspecialchars($term); ?>,
	<?php } else { ?>
		All,
	<?php } ?>

	Page: <?php echo $ib->pages['current']; ?> of <?php echo $ib->pages['total']; ?>
</h1>

<?php if( $ib->user['id'] ) {?>
<div class="userInfo">
	<img class="avatar" width="40" height="40" src="<?php echo Config::$absolutePath.$ib->user['avatar']; ?>"/>
	<div class="name">
		<strong>
			<a href="<?php echo Config::$absolutePath.'user/'.$ib->user['name']; ?>"><?php echo $ib->user['name']; ?></a>
		</strong>
	</div>
	<div class="info">
		Score: <strong><?php echo $ib->user['score']; ?></strong> /
		Images: <strong><?php echo $ib->user['images']; ?></strong>
		<?php if( !empty($ib->user['website'])) { ?>/
			Website: <strong><a href="<?php echo htmlspecialchars($ib->user['website']); ?>" target="_blank">
				<?php echo htmlspecialchars($ib->user['website']); ?>
			</a></strong>
		<?php } ?>
	</div>
	<div style="clear:both;"></div>
</div>
<?php } ?>

<div id="images" style="height:<?php echo $gv->height * Config::$gridView['gridSize']; ?>px">
	<?php if( isset($ib->pages['prev']) ) { ?>
		<a href="<?php echo Config::$absolutePath.$ib->basePath.'page/'.$ib->pages['prev']; ?>" class="prev" title="Previous" id="prevBar">Previous</a>
	<?php } else { ?>
		<div class="noPrev" id="prevBar">Previous</div>
	<?php } ?>
	
	<?php if( isset($ib->pages['next']) ) { ?>
		<a href="<?php echo Config::$absolutePath.$ib->basePath.'page/'.$ib->pages['next']; ?>" class="next" title="Next" id="nextBar">Next</a>
	<?php } else { ?>
		<div class="noNext" id="nextBar">Next</div>
	<?php } ?>

	<div id="imageGrid" style="height:<?php echo $gv->height * Config::$gridView['gridSize']; ?>px">
<?php
	if( !empty($ib->searchColor) || !empty($ib->searchTerm) ) {
		$imgBasePath = 'all/';
	} else {
		$imgBasePath = $ib->basePath;
	}
	foreach( $ib->thumbs as $thumb ) {
		if( isset( $thumb['left'] ) ) {
		echo '<div class="'.$thumb['class'].'" style="left:'.$thumb['left'].'px;top:'.$thumb['top'].'px;"><a class="thumb" href="'
			.Config::$absolutePath.$imgBasePath.'view/'.$thumb['keyword'].'">'
			.'<img src="'.$thumb['thumb'].'" alt="'.$thumb['keyword'].'" title="'.$thumb['userName'].' - '.
			date('d. M Y H:i',$thumb['loggedTS']).'"/></a></div>'."\n";
		}
	}
?>
	</div>
</div>

<script type="text/javascript">
	gridView('imageGrid');
	ieAdjustHeight(0);
</script>


<?php include( $templates.'footer.tpl.php' ); ?>