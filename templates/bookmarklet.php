<?php

function bookmarklet() {
	$baseurl = href("/link.php?action=add", true);

	// The Javascript code contained in the bookmarklet.
	$bookmarklet_code = <<<JAVASCRIPT
		window.location.href = "{$baseurl}&title=" +
			encodeURIComponent(document.title) + "&url=" +
			encodeURIComponent(window.location.href);
	JAVASCRIPT;

	// Processing the bookmarklet code into a proper URL.
	$jsurl = rawurlencode("(function(){" .
		preg_replace('/[\t\r\n]+/', '', $bookmarklet_code) . "})();");
	$jsurl = str_replace(array("%28", "%29"), array("(", ")"), $jsurl);

	return "javascript:$jsurl";
}
