<?php require(__DIR__ . "/../head.php"); ?>

<form id="link-delete" method="POST"
		action="<?= href("/link.php?action=delete&id={$link->id()}") ?>">
	<div class="centered">
		<p>
			Are you sure you want to delete <a href="<?= $link->url() ?>">
			<?= $link->title() ?></a>?
		</p>

		<input type="submit" class="delete-button action-button"
			value="Delete" />
		<input type="button" value="Cancel" onclick="history.back()" />
	</div>
</form>

<?php require(__DIR__ . "/../footer.php"); ?>
