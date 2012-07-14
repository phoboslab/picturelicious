<?php include( $templates.'header.tpl.php' ); ?>

<form action="<?php echo Config::$absolutePath; ?>login" method="post">
	<fieldset>
		<legend>Login</legend>
		
		<?php if( isset($messages['wrongLogin']) ) { ?>
			<div class="warn">
				Wrong user or password!
			</div>
		<?php } ?>
		
		<dl class="form">
			<dt>Name:</dt>
			<dd>
				<input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name']); ?>"/>
			</dd>
			
			<dt>Passwort:</dt>
			<dd>
				<input type="password" name="pass" />
			</dd>
			
			<dt>&nbsp;</dt>
			<dd>
				<input class="check" type="checkbox" name="remember" id="inputRemember" value="1"/>
					<label for="inputRemember">Remember me!</label>
			</dd>
			
			<dt>&nbsp;</dt>
			<dd>
				<input type="submit" class="button login" name="login" value="Login" />
			</dd>
		</dl>
		
		Don't have an account yet? <a href="<?php echo Config::$absolutePath; ?>register">Register!</a>
	</fieldset>
</form>

<?php include( $templates.'footer.tpl.php' ); ?>