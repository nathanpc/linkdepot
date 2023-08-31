<?php require(__DIR__ . "/../head.php"); ?>

<form id="shelf-<?= $this->action ?>" method="POST"
		action="<?= href("/shelf.php?action={$this->action}&id={$shelf->id()}") ?>">
	<div class="centered">
		<p>
			Are you sure you want to <?= $this->action ?> the
			<a href="<?= href('/shelf.php?action=view&id=' . $shelf->id()) ?>">
			<?= $shelf->title() ?></a> shelf?
		</p>

		<input type="submit" class="favorite-button action-button"
			value="<?= ucfirst($this->action) ?>" />
		<input type="button" value="Cancel" onclick="history.back()" />
	</div>
</form>

<?php require(__DIR__ . "/../footer.php"); ?>
