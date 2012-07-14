<?php include( $templates.'header.tpl.php' ); ?>
<h1>
	&raquo; Browsing Users,
	Page: <?php echo $ul->pages['current']; ?> of <?php echo $ul->pages['total']; ?>
</h1>


<?php foreach( $ul->users as $i => $u ) { ?>
	<div class="userInfo">
		<img class="avatar" width="40" height="40" src="<?php echo Config::$absolutePath.$u['avatar']; ?>"/>
		<div class="name">
			<strong>
				<a href="<?php echo Config::$absolutePath.'user/'.$u['name']; ?>"><?php echo $u['name']; ?></a>
			</strong>
		</div>
		<div class="info">
			Score: <strong><?php echo $u['score']; ?></strong> /
			Images: <strong><?php echo $u['images']; ?></strong> 
			<?php if( !empty($u['website'])) { ?>/
				Website: <strong><a href="<?php echo htmlspecialchars($u['website']); ?>" target="_blank">
					<?php echo htmlspecialchars($u['website']); ?>
				</a></strong>
			<?php } ?>
		</div>
		<div style="clear:both;"></div>
	</div>
<?php } ?>
	
<div class="userInfo">
	<?php if( isset($ul->pages['prev']) ) { ?>
		<a href="<?php echo Config::$absolutePath.'users/page/'.$ul->pages['prev']; ?>" class="textPrev" title="Previous">&laquo; Previous</a>
	<?php } else { ?>
		<div class="textNoPrev">&laquo; Previous</div>
	<?php } ?>
	
	<?php if( isset($ul->pages['next']) ) { ?>
		<a href="<?php echo Config::$absolutePath.'users/page/'.$ul->pages['next']; ?>" class="textNext" title="Next">Next &raquo;</a>
	<?php } else { ?>
		<div class="textNoNext">Next &raquo;</div>
	<?php } ?>
	
	<div style="clear: both;"></div>
</div>

<?php include( $templates.'footer.tpl.php' ); ?>