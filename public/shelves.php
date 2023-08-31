<?php require(__DIR__ . "/../templates/head.php"); ?>

<form id="shelf-add" method="POST"
		action="<?= href('/shelf.php?action=add') ?>">
	<div class="form-line centered">
		<input type="text" name="title" id="title" size="20" value=""
			placeholder="Shelf title" />
		<input type="submit" value="Add Shelf" />
	</div>
</form>

<?php foreach (LinkDepot\Shelf::List() as $shelf) { ?>
	<?= $shelf->as_html() ?>
<?php } ?>

<?php require(__DIR__ . "/../templates/footer.php"); ?>
