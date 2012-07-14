<?php include( $templates.'header.tpl.php' ); ?>

<form action="<?php echo Config::$absolutePath; ?>register" method="post">
	<fieldset>
		<legend>Create Account</legend>
		
		<?php if( isset($messages['nameInUse']) ) { ?>
			<div class="warn">This Username is allready registered!</div>
		<?php } ?>
		
		<?php if( isset($messages['nameInvalid']) ) { ?>
			<div class="warn">Your name must be at least 2 characters long an may not have any special characters!</div>
		<?php } ?>
		
		<?php if( isset($messages['passToShort']) ) { ?>
			<div class="warn">Your password must be at least 6 characters long!</div>
		<?php } ?>
		
		<?php if( isset($messages['passNotEqual']) ) { ?>
			<div class="warn">Your both passwords are not equal!</div>
		<?php } ?>
		
		<?php if( isset($messages['emailInUse']) ) { ?>
			<div class="warn">There is already a registered user with this E-Mail address!</div>
		<?php } ?>
		
		<?php if( isset($messages['wrongEmail']) ) { ?>
			<div class="warn">Your E-Mail address seems to be invalid!</div>
		<?php } ?>
		
		
		<dl class="form">
			<dt>Name:</dt>
			<dd>
				<input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name']); ?>"/> 
				Only letters and numbers; at least 2 characters long
			</dd>
			
			<dt>E-Mail:</dt>
			<dd>
				<input type="text" name="email" value="<?php echo htmlspecialchars($_POST['email']); ?>"/> 
				Must be valid!
			</dd>
			
			<dt>Password:</dt>
			<dd>
				<input type="password" name="pass" class="pass"/>
			</dd>
			
			<dt>(Repeat)</dt>
			<dd>
				<input type="password" name="pass2" class="pass"/>
			</dd>
		
			<dt>&nbsp;</dt>
			<dd>
				<input type="submit" name="register" class="button register" value="Register" />
			</dd>
		</dl>
	</fieldset>
</form>

<?php include( $templates.'footer.tpl.php' ); ?>