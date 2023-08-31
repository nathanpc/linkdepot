<?php require(__DIR__ . "/../head.php"); ?>

<form id="shelf-edit" method="POST"
		action="<?= href('/shelf.php?action=edit&id=' . $shelf->id()) ?>">
	<div class="form-line">
		<input type="text" name="title" id="title" size="20"
			value="<?= $shelf->title() ?>" />
		<input type="submit" value="Change Title" />
	</div>
</form>

<?= $shelf->as_html(true); ?>

<?php require(__DIR__ . "/../footer.php"); ?>
