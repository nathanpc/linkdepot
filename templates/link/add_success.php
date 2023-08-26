<p>
	Link successfully added to the
	<a href="<?= href("/shelf.php?action=manage&id={$link->shelf()->id()}") ?>">
	<?= $link->shelf()->title() ?></a> shelf.
</p>

<div class="link-shelf">
	<table class="link-box">
		<?= $link->as_html(true) ?>
	</table>
</div>
