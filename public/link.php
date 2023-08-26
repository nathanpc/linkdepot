<?php
/**
 * link.php
 * Public web interface to the links stored in the system.
 *
 * @author Nathan Campos <nathan@innoveworkshop.com>
 */

namespace LinkDepot\API;

require_once __DIR__ . "/../functions.php";
require_once __DIR__ . "/../vendor/autoload.php";

use LinkDepot\Link;
use LinkDepot\Shelf;

// Get URL parameters.
$method = $_SERVER["REQUEST_METHOD"]; 
$action = strtolower(urlparam("action"));
$format = strtolower(urlparam("format", "html"));

// Handle favicons.
if ($action == "favicon") {
	// Get the ID of the link.
	$id = urlparam("id");
	if (is_null($id)) {
		http_response_code(400);
		header("Content-Type: text/plain");
		echo "Error: Required parameter id wasn't set.";
		return;
	}

	// Get the link from the ID.
	$link = \LinkDepot\Link::FromID($id);
	if (is_null($link)) {
		http_response_code(400);
		header("Content-Type: text/plain");
		echo "Error: Invalid link ID.";
		return;
	}

	// Check if we even have a favicon.
	if (is_null($link->favicon())) {
		http_response_code(404);
		header("Content-Type: text/plain");
		echo "Error: No favicon associated with this link.";
		return;
	}

	// Set the content type header and send the image.
	header("Content-Type: " . buffer_mime_type($link->favicon()));
	echo $link->favicon();
}

// HTML output.
if ($format == "html") {
	require(__DIR__ . "/../templates/head.php"); 

	if ($method == "GET") {
		if ($action == "add") {
			// Display the add form.
			$params = reqmultiparams(["url", "title", "favicon"], "");
			require(__DIR__ . "/../templates/link/add.php"); 
		}
	} else if ($method == "POST") {
		if ($action == "add") {
			$params = reqmultiparams(["url", "title", "favicon", "shelf"]);

			// Check for required parameters.
			$required = required_params(["url", "title", "shelf"]);
			if ($required) {
				$required_str = implode(", ", $required);
				echo <<<HTML
					<p>
						<b>Error:</b> Required parameters {$required_str}
						weren't set.
					</p>
				HTML;
				goto footer;
			}

			// Check if the requested shelf event exists.
			$shelf = \LinkDepot\Shelf::FromID($params["shelf"]);
			if (is_null($shelf)) {
				echo <<<HTML
					<p>
						<b>Error:</b> Shelf ID {$params["shelf"]} doesn't exist.
					</p>
				HTML;
				goto footer;
			}

			// Build our new link object.
			$link = new \LinkDepot\Link(null, $params["title"], $params["url"],
				null, $shelf);
			$link->fetch_favicon((!empty($params["favicon"])) ?
				$params["favicon"] : null);

			// Save the changes to the database.
			try {
				$link->save();
				require(__DIR__ . "/../templates/link/add_success.php"); 
			} catch (\PDOException $e) {
				echo <<<HTML
					<p><b>Error:</b> Something went wrong while trying to commit
						changes to the database.</p>
					<pre><code>{$e}</code></pre>
				HTML;
			}
		}
	}

footer:
	require(__DIR__ . "/../templates/footer.php"); 
}
