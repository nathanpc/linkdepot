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

// HTML output.
if ($format == "html") {
	require(__DIR__ . "/../templates/head.php"); 

	if ($method == "GET") {
		if ($action == "add") {
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
