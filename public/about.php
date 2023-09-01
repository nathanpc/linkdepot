<?php require(__DIR__ . "/../templates/head.php"); ?>
<?php require(__DIR__ . "/../templates/bookmarklet.php"); ?>

<h3>Overview</h3>

<p>
	Built by <a href="https://nathancampos.me/">Nathan Campos</a>, this project
	aims to be a minimalist way to store, manage, and work with your bookmarks
	and random links from the internet. Made with simplicity and extreme browser
	compatibility in mind (try it out on your retro computer!), this bookmark
	manager is probably all that you've ever hoped for.
</p>

<h3>Bookmarklet</h3>

<p>
	One of the easiest ways to add a new link to the platform is to simply have
	our bookmarklet in your browser's bookmarks toolbar where you can click on
	it and have the currently opened page added to the platform.
</p>

<p>
	In order to add the bookmarklet to your browser simply drag this to your
	bookmarks toolbar: <a class="bookmarklet" href="<?= bookmarklet() ?>">Add to LinkDepot</a>
</p>

<?php require(__DIR__ . "/../templates/footer.php"); ?>
