<?php require(__DIR__ . "/../templates/head.php"); ?>

<p>A minimalist way to store, manage, and work with your bookmarks and random
	URLs from the internet.</p>

<?php foreach (LinkDepot\Shelf::List() as $shelf) { ?>
	<?= $shelf->as_html() ?>
<?php } ?>

<?php require(__DIR__ . "/../templates/footer.php"); ?>
