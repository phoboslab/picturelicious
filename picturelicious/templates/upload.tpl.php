<?php include( $templates.'header.tpl.php' ); ?>

<form action="<?php echo Config::$absolutePath; ?>upload" enctype="multipart/form-data" method="POST">
	<fieldset>
		<legend>Upload / Post</legend>

		<p>
			Images must be no larger than <strong>4069x4096</strong> and must not exceed <strong>2 MB</strong>. 
			If the image is already in our Database, the upload will fail. Please follow our rules:
		</p>
		
		<ul>
			<li> Absolutely NO pictures of child (Less 18 Yrs.), preteen or animal porn.</li>
			<li> NO pictures of racist propaganda. No snuff pictures!</li>
			<li> NO HARDCORE (porn) contents!!</li>
			<li> No crappy low quality pics!</li>
		</ul>

		<p>You can either upload an Image directly from your Computer, or specify an URL to copy the image From.</p>
		
		<?php if( !empty( $uploadErrors ) ) { ?>
			<div class="warn">
				There was an issue uploading your image:
				<ul>
				<?php foreach( $uploadErrors as $msg ) { ?>
					<li class="error"><?php echo $msg?></li>
				<?php }/*foreach*/?>
				</ul>
			</div>
		<?php }/*if*/?>
		
		<dl class="form">
			<dt>File:</dt>
			<dd>
				<input type="file" name="image" style="color: #000; background-color: #fff;"/>
			</dd>
			
			<dt>or URL:</dt>
			<dd>
				<input type="text" name="url" value="<?php echo htmlspecialchars( $_POST['url'] ); ?>"/>
			</dd>
			
			<dt>Tags:</dt>
			<dd>
				<input type="text" name="tags" value="<?php echo htmlspecialchars( $_POST['tags'] ); ?>"/>
			</dd>
			
			<dt>&nbsp;</dt>
			<dd>
				<input type="submit" name="upload" value="Upload" class="button"/>
			</dd>
		</dl>
	</fieldset>
</form>

<?php include( $templates.'footer.tpl.php' ); ?>