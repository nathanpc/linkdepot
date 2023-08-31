<?php require(__DIR__ . "/../head.php"); ?>

<form id="shelf-delete" method="POST"
		action="<?= href("/shelf.php?action=delete&id={$shelf->id()}") ?>">
	<div class="centered">
		<p>
			Are you sure you want to delete the
			<a href="<?= href('/shelf.php?action=view&id=' . $shelf->id()) ?>">
			<?= $shelf->title() ?></a> shelf and <b>all of its contents</b>?
		</p>

		<input type="submit" value="Delete" class="delete-button" />
		<input type="button" value="Cancel" onclick="history.back()" />
	</div>
</form>

<?php require(__DIR__ . "/../footer.php"); ?>
