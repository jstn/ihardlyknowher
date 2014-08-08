<? if($photos): ?>
	<div class="photos">
		<ul>
			<? foreach($photos as $photo): ?>
				<?
					$src = $photo->imageURLforSuffix();
					$width = 500;
				
					if($size === 'large') {
						if ($large = $photo->imageInfoForSize('Large')) {
							$src = $large['source'];
							$width = $large['width'];
							$height = $large['height'];
						} elseif ($orig = $photo->imageInfoForSize('Original')) {
							if($orig['width'] < 1280 && $orig['height'] < 1280) {
								$src = $orig['source'];
								$width = $orig['width'];
								$height = $orig['height'];						
							}
						}
					}
				?>
				<li class="photo" id="photo_<?= $photo->id() ?>" style="width: <?= $width ?>px;">
					<a href="<?= $userinfo['photosurl'] . $photo->id() ?>/">
						<img src="<?= $src ?>" alt="<?= $photo->id() ?>" style="width: <?= $width ?>px;<?= $height ? ' height: '.$height.'px' : '' ?>" />
					</a>				
				</li>
				<? $height = false ?>
			<? endforeach ?>
			<li class="pagers" style="width: <?= $width ?>px;">
				<a href="/" class="homelink">ihkh</a> / <a href="/<?= $userinfo['urlname' ]?>"><?= $userinfo['urlname' ]?></a>				
				<? if ($page > 1): ?>
					<div class="left">
						<a href="/<?= $userinfo['urlname'] ?><?= $page == 2 ? '' : '/'.($page - 1) ?>" class="backlink">&larr; previous</a>
					</div>
				<? endif ?>
				<? if ($page != $pages): ?>
					<div class="right">
						<a href="/<?= $userinfo['urlname'] ?>/<?= $page + 1 ?>">next &rarr;</a>
					</div>
				<? endif ?>
			</li>
			<li class="footer">
				all images &copy; <a href="<?= $userinfo['profileurl' ]?>"><?= !empty($userinfo['realname']) ? $userinfo['realname'] : $userinfo['username'] ?></a>
			</li>
		</ul>
	</div>
	<? if($background != 'white'): ?>
		<style type="text/css">
			html,body { background: <?= $background ?>; }
			div.photos li.pagers, div.photos li.footer { color: #eee; }
			div.photos li.pagers a { color: #d0d0d0; }
			div.photos li.footer { color: #222; }
			div.photos li.footer a { color: #222; }			
		</style>
	<? endif ?>
<? else: ?>
	<div class="regular">
		<p>That user does not exist or has no public photos.</p>
		<p><a href="/">&larr; return</a></p>
	</div>
<? endif ?>