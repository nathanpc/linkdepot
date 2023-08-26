<p>
	Link <a href="<?= $link->url() ?>"><?= $link->title() ?></a><sup>
	(<a href="<?= href("/link.php?action=edit&id={$link->id()}") ?>">edit</a>)
	</sup> successfully added to the
	<a href="<?= href("/shelf.php?action=manage&id={$link->shelf()->id()}") ?>">
	<?= $link->shelf()->title() ?></a> shelf.
</p>
