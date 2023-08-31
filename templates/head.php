<?php
require_once __DIR__ . "/../functions.php";
require_once __DIR__ . "/../templates/functions.php";
require_once __DIR__ . "/../vendor/autoload.php";

use LinkDepot\Link;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="en">

<head>
	<meta charset="utf-8" />
	<title><?= site_title() ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- Styling -->
	<link rel="stylesheet" href="<?= href('/css/reset.css') ?>">
	<link rel="stylesheet" href="<?= href('/css/main.css') ?>">

	<!-- Javascript -->
	<script type="text/javascript" src="<?= href('/js/main.js') ?>"></script>
</head>

<body>
	<!-- Navigation Bar -->
	<div id="navbar">
		<h1>
			<a href="<?= href('/') ?>">
				<span id="navbar-brand-link">Link</span>
				<span id="navbar-brand-depot">Depot</span>
			</a>
		</h1>

		<!-- Navigation -->
		<div class="nav">
			<?= nav_item('Home', '/', 'index') ?> ‧
			<?= nav_item('Add Link', '/link.php?action=add', 'link') ?> ‧
			<?= nav_item('Shelves', '/shelves.php', 'shelves') ?> ‧
			<?= nav_item('About', '/about.php', 'about') ?>
		</div>
	</div>


	<!-- Main Page Contents -->
	<div id="main">
