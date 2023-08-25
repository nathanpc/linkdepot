<?php require(__DIR__ . "/../templates/head.php"); ?>

<p>A minimalist way to store, manage, and work with your bookmarks and random
	URLs from the internet.</p>

<?php foreach (LinkDepot\Shelf::List() as $shelf) { ?>
	<?= $shelf->as_html() ?>
<?php } ?>

<div class="link-shelf">
	<div class="shelf-header">
		<h3>Desktop</h3>
		<span class="shelf-actions">
			<a href="#">store</a> ‧
			<a href="#">manage</a> ‧
			<a class="action-delete" href="#">delete</a>
		</span>
	</div>

	<table class="link-box">
		<?= link_box_row("Innove Workshop",
			"https://innoveworkshop.com/",
			"https://innoveworkshop.com/favicon.ico") ?>
		<?= link_box_row("nathanpc/linkdepot: A minimalist way to store, manage, and work with your bookmarks and random URLs from the internet",
			"https://github.com/nathanpc/linkdepot",
			"https://github.com/favicon.ico") ?>
		<?= link_box_row("<table>: The Table element - HTML: HyperText Markup Language | MDN",
			"https://developer.mozilla.org/en-US/docs/Web/HTML/Element/table",
			"https://developer.mozilla.org/favicon.ico",
			false, false) ?>
	</table>
</div>

<?php require(__DIR__ . "/../templates/footer.php"); ?>
