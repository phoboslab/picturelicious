<?php include( $templates.'header.tpl.php' ); ?>

<form action="<?php echo Config::$absolutePath; ?>profile" enctype="multipart/form-data" method="post">
	<fieldset>
		<legend>Change Profile</legend>
		
		<?php if( isset($messages['passToShort']) ) { ?>
			<div class="warn">Your password must be at least 6 characters long!</div>
		<?php } ?>
		
		<?php if( isset($messages['passNotEqual']) ) { ?>
			<div class="warn">Your both passwords are not equal!</div>
		<?php } ?>
		
		<?php if( isset($messages['avatarFailed']) ) { ?>
			<div class="warn">Your avatar Image could not be processed!</div>
		<?php } ?>
		
		<dl class="form">
			<dt>Passwort:</dt>
			<dd>
				<input type="password" name="cpass" /> (leave empty, if you don't want to change it)
			</dd>
			
			<dt>(Repeat)</dt>
			<dd>
				<input type="password" name="cpass2" />
			</dd>
			
			<dt>&nbsp;</dt>
			<dd>&nbsp;</dd>
			
			<?php if( empty($user->email) ) { ?>
				<dt>E-Mail:</dt>
				<dd>
					<input type="text" name="email" />
				</dd>
			<?php } ?>
			
			<dt>Website:</dt>
			<dd>
				<input type="text" name="website" value="<?php echo htmlspecialchars( $user->website ); ?>"/>
			</dd>
			
			<dt>Avatar:</dt>
			<dd>
				<input type="file" name="avatar" style="color: #000; background-color: #fff;"/><br/> 
			</dd>
		
			<dt>&nbsp;</dt>
			<dd>
				<input type="submit" name="save" class="button" value="Save" />
			</dd>
		</dl>
	</fieldset>
</form>

<?php include( $templates.'footer.tpl.php' ); ?>