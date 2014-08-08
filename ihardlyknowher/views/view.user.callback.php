<div class="regular settings">
	<? if ($error): ?>
		<p><?= $error ?></p>
	<? else: ?>
		<p>Hello, <strong><?= $userinfo['username'] ?></strong>.</p>
		<form action="/save.settings" method="post" name="settings">
			<input type="hidden" name="token" value="<?= $this->token ?>" />
			
			<p>Background color:</p>
			<input type="radio" name="background" value="white" <?= $background === 'white' ? 'checked="checked"' : ''?>/> white<br />
			<input type="radio" name="background" value="black" <?= $background === 'black' ? 'checked="checked"' : ''?>/> black
			
			<p>Image size:</p>
			<input type="radio" name="size" value="large" <?= $size === 'large' ? 'checked="checked"' : ''?>/> large<br />			
			<input type="radio" name="size" value="medium" <?= $size === 'medium' ? 'checked="checked"' : ''?>/> medium
			
			<p><input type="submit" value="Save" /></p>
		</form>
		<p><a href="/">&larr; return</a></p>
	<? endif ?>
</div>