<?php require(__DIR__ . "/../head.php"); ?>

<form id="link-<?= $this->action ?>" method="POST" action="<?= $form_action ?>">
	<div class="form-line">
		<label for="shelf">Shelf: </label>
		<select name="shelf" id="shelf" form="link-<?= $this->action ?>">
			<?php foreach(LinkDepot\Shelf::List() as $shelf) { ?>
				<option value="<?= $shelf->id() ?>"
					<?= ($shelf->id() == reqparam("shelf")) ? "selected" : "" ?>>
					<?= $shelf->title() ?>
				</option>
			<?php } ?>
		</select>
	</div>

	<div class="form-line">
		<label for="url">URL: </label>
		<input type="url" name="url" id="url" size="50"
			value="<?= $params['url'] ?>" required />
	</div>

	<div class="form-line">
		<label for="title">Title: </label>
		<input type="text" name="title" id="title" size="50"
			value="<?= $params['title'] ?>" required />
	</div>

	<div class="form-line">
		<label for="favicon">Favicon: </label>
		<input type="url" name="favicon" id="favicon" size="50"
			value="<?= $params['favicon'] ?>" />
	</div>

	<br>
	<input type="submit" value="Submit" />
</form>

<?php require(__DIR__ . "/../footer.php"); ?>
