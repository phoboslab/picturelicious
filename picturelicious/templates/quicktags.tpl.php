<?php include( $templates.'header.tpl.php' ); ?>

<script type="text/javascript" src="<?php echo Config::$absolutePath; ?>media/quicktags.js"></script>
<h1>
	&raquo; Quicktagger
</h1>

<div class="article" id="quickTagIntro">
	<h2>Quick tagging?</h2>
	<p>
		This special site automatically searches for images which are in desperate need of getting tagged. 
		That is: new images without any or very few tags.
	</p>
	
	<p>
		This site is carefully designed to allow efficient tagging of images. You don't have to move your hands
		away from the keyboard - just write and press enter for the next image. Make sure javascript is
		enabled in your browser, or you wont see anything.
	</p>
	
	
	<h2>A few words about tagging</h2>
	
	<p>
		If you are unsure what tags to write, just imagine you are searching for this image - what word(s) would you
		search for?
	</p>
	
	<p>
		Also, is there any written text on the image itself? Try to write only the most essential words down. For instance
		if you see a motivational with the text:
	</p>
		<blockquote>
			<strong>Huddle House</strong><br/>
			For those times when Waffle House isn't quite white trashy enough
		</blockquote>
	<p>
		good tags for this image would be "<em>motivational huddle house white trash</em>"
	</p>
	<p>
		If you don't know what to write, just press enter to skip the image.
	</p>
	
	<p>
		<strong>Don't abuse the system in any way, or the wrath of the administrator will come done on you!</strong>
	</p>
	

	<h2><a href="#" onclick="return quickTagInit();"> &raquo; I get it, let's start already!</a></h2>
</div>

<div id="viewer" class="hidden">
	<div id="imageInfo">
		<div>
			location: <a id="imageLocation" href="#"></a>
		</div>
		
		<div>
			<div>
				Tags: <span id="tags"></span>
			</div>
				<form class="visible" id="addTag" action="" onsubmit="return quickTag($('logId').value, $('imageId').value, $('tagText'));">
					<input type="text" name="tags" id="tagText"/>
					<input type="hidden" id="imageId" name="imageId" value="0"/>
					<input type="hidden" id="logId" name="logId" value="0"/>
					<input type="button" name="save" value="Add Tags" class="button" onclick="quickTag($('logId').value, $('imageId').value, $('tagText'));"/>
				</form>
		</div>
	</div>
	
	<div id="imageContainer">
		<img id="image" onclick="swap(this, 'scaled', 'full')" class="scaled" src=""/>
	</div>
	<br/>
</div>

<?php include( $templates.'footer.tpl.php' ); ?>