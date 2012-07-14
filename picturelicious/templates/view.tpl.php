<?php include( $templates.'header.tpl.php' ); ?>

<h1>
	&raquo; Viewing
	<?php if( !empty($iv->user) ) { ?>
		User: <a href="<?php echo Config::$absolutePath.'user/'.$iv->user['name']; ?>"><?php echo $iv->user['name']; ?></a>
	<?php } else if( !empty($iv->channel) ) { ?>
		Channel: <a href="<?php echo Config::$absolutePath.'channel/'.$iv->channel['keyword']; ?>"><?php echo $iv->channel['name']; ?></a>
	<?php } else { ?>
		All
	<?php } ?>
</h1>

<div class="userInfo">
	<img class="avatar" width="40" height="40" src="<?php echo Config::$absolutePath.$iv->userInfo['avatar']; ?>"/>
	<div class="name">
		<strong>
			<a href="<?php echo Config::$absolutePath.'user/'.$iv->userInfo['name']; ?>"><?php echo $iv->userInfo['name']; ?></a>
		</strong>
	</div>
	<div class="info">
		Score: <strong><?php echo $iv->userInfo['score']; ?></strong> /
		Images: <strong><?php echo $iv->userInfo['images']; ?></strong> 
		<?php if( !empty($iv->userInfo['website'])) { ?>/
			Website: <strong><a href="<?php echo htmlspecialchars($iv->userInfo['website']); ?>" target="_blank">
				<?php echo htmlspecialchars($iv->userInfo['website']); ?>
			</a></strong>
		<?php } ?>
	</div>
	<div style="clear:both;"></div>
</div>

<div id="viewer">
	<?php if( isset($iv->stream['prev']) ) { ?>
		<a href="<?php echo Config::$absolutePath.$iv->basePath.'view/'.$iv->stream['prev']; ?>" class="prev" id="prevBar" title="Previous">Previous</a>
	<?php } else { ?>
		<div class="noPrev" id="prevBar">Previous</div>
	<?php } ?>
	
	<?php if( isset($iv->stream['next']) ) { ?>
		<a href="<?php echo Config::$absolutePath.$iv->basePath.'view/'.$iv->stream['next']; ?>" class="next" id="nextBar" title="Next">Next</a>
	<?php } else { ?>
		<div class="noNext" id="nextBar">Next</div>
	<?php } ?>
	
	<div id="imageContainer">
		<img id="image" onclick="swap(this, 'scaled', 'full')" class="scaled" src="<?php echo Config::$absolutePath.Config::$images['imagePath'].$iv->image['path']; ?>" alt="<?php echo htmlspecialchars($iv->image['tags']) ?>"/>
	</div>
	
	<div id="imageInfo">
		<div class="rating">
			<div class="ratingBase">
				<div class="ratingCurrent" id="currentRating" style="width: <?php echo $iv->image['votes'] > 0 ? ($iv->image['score']) / 0.05 : 0;?>px"></div>
				<div class="ratingRate" id="userRating">
					<a href="#" onclick="return rate(<?php echo $iv->image['id'];?>,1);" onmouseout="sr('userRating',0);" onmouseover="sr('userRating',1);"></a>
					<a href="#" onclick="return rate(<?php echo $iv->image['id'];?>,2);" onmouseout="sr('userRating',0);" onmouseover="sr('userRating',2);"></a>
					<a href="#" onclick="return rate(<?php echo $iv->image['id'];?>,3);" onmouseout="sr('userRating',0);" onmouseover="sr('userRating',3);"></a>
					<a href="#" onclick="return rate(<?php echo $iv->image['id'];?>,4);" onmouseout="sr('userRating',0);" onmouseover="sr('userRating',4);"></a>
					<a href="#" onclick="return rate(<?php echo $iv->image['id'];?>,5);" onmouseout="sr('userRating',0);" onmouseover="sr('userRating',5);"></a>
				</div>
			</div>
			<div id="loadRating" class="load"></div>
			<span id="ratingDescription">
				<?php if($iv->image['votes'] > 0 ) { ?>
					<?php echo number_format($iv->image['score'],1);?> after <?php echo $iv->image['votes'];?> Vote<?php echo $iv->image['votes'] > 1 ? 's' : '' ?>
				<?php } else { ?>
					No votes yet!
				<?php } ?>
			</span>
			<div style="clear: both;"></div>
			<?php if($user->admin) { ?>
				<div style="float:right;" id="del">
					<div class="load" id="loadDelete"></div>
					<a href="#" onclick="del(<?php echo $iv->image['id']; ?>)">[x]</a>
				</div>
			<?php } ?>
		</div>
	
		<div class="date">
			<?php echo date('d. M Y H:i',$iv->image['loggedTS']); ?>
		</div>
		
		<div>
			Tags: <span id="tags"><?php echo !empty($iv->image['tags']) ? htmlspecialchars($iv->image['tags']) : '<em>none</em>'; ?></span>
			<?php if( $user->id ) { ?>
				<a href="#" onclick="swap($('addTag'), 'hidden', 'visible'); $('tagText').focus(); return false;">(add)</a>
				<form class="hidden" id="addTag" action="" onsubmit="return addTags(<?php echo $iv->image['id']; ?>, $('tagText'), <?php echo $user->admin ? 'true' : 'false';?>);">
					<input type="text" name="tags" id="tagText" <?php if($user->admin) {?>value="<?php echo htmlspecialchars($iv->image['tags']);?>"<?php } ?>/>
					<input type="button" name="save" value="Add Tags" class="button" onclick="addTags(<?php echo $iv->image['id']; ?>, $('tagText'), <?php echo $user->admin ? 'true' : 'false';?>);"/>
					<div id="loadTags" class="load"></div>
				</form>
			<?php } ?>
		</div>
		
		Post in Forum: <input type="text" readonly="1" value="[URL=<?php echo Config::$frontendPath ?>][IMG]<?php echo Config::$frontendPath.Config::$images['imagePath'].$iv->image['path']; ?>[/IMG][/URL]" style="width: 400px; font-size:10px" onclick="this.focus();this.select();"/>
		
		

		<div class="comments">
			<?php if( $iv->commentCount == 1 ) { ?>
				<h3>1 Comment:</h3>
			<?php } else if( $iv->commentCount > 1 ) { ?>
				<h3><?php echo $iv->commentCount ?> Comments:</h3>
			<?php } else { ?>
				<h3>No comments yet!</h3>
			<?php } ?>
			
			<?php foreach( $iv->comments as $c ) { ?>
				<div class="comment">
					<div class="commentHead">
						<img class="avatarSmall" width="16" height="16" src="<?php echo Config::$absolutePath.$c['avatar']; ?>"/>
						<a href="<?php echo Config::$absolutePath.'user/'.$c['name']; ?>"><?php echo $c['name']; ?></a>
						at <?php echo date('d. M Y H:i',$c['created']); ?>
						<?php if($user->admin) { ?>
							<div style="float:right;" id="del">
								<a href="#" onclick="return delComment(<?php echo $c['id']; ?>, this)">[x]</a>
							</div>
						<?php } ?>
					</div>
					<?php echo $c['content']; ?>
				</div>
			<?php } ?>
			
			<?php if( $user->id ) { ?>
				<form method="post" class="addComment" action="<?php echo Config::$absolutePath.$iv->basePath.'view/'.$iv->image['keyword']; ?>">
					<div>
						<textarea name="content" rows="3" cols="60"></textarea>
						<input class="submit" type="submit" name="addComment" value="Submit Comment"/>
					</div>
				</form>
			<?php } ?>
		</div>
		
	</div>
</div>

<script type="text/javascript">
	ieAdjustHeight(0);
</script>


<?php include( $templates.'footer.tpl.php' ); ?>