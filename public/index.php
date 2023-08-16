<?php require(__DIR__ . "/../templates/head.php"); ?>

<p>A minimalist way to store, manage, and work with your bookmarks and random
	URLs from the internet.</p>

<div class="link-shelf">
	<h3 class="shelf-title">Desktop</h3>
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
			false) ?>
	</table>
</div>

<?php require(__DIR__ . "/../templates/footer.php"); ?>
