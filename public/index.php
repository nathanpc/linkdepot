<?php require(__DIR__ . "/../templates/head.php"); ?>

<?php
$favorites = LinkDepot\Shelf::ListFavorites();

if (empty($favorites)) {
?>
	<p>
		You don't seem to have any <a href="<?= href('/shelves.php') ?>">
		shelves</a> marked as <span class="action-star">favorites</span>.
	</p>
<?php
} else {
	foreach ($favorites as $shelf) {
		echo $shelf->as_html();
	}
} 
?>

<?php require(__DIR__ . "/../templates/footer.php"); ?>
