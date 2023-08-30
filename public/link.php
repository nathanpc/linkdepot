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

use LinkDepot\Link as Link;
use LinkDepot\Shelf as Shelf;
use LinkDepot\RequestHandler as RequestHandler;

class LinkHandler extends RequestHandler {
	public function __construct() {
		// Ensure we have a proper known state.
		$this->initialize();

		// Add handlers.
		$this->add_handler("GET", "favicon", $this->handler("get_favicon"));
		$this->add_handler("GET", "add", $this->handler("get_add"));
		$this->add_handler("POST", "add", $this->handler("post_add"));
	}

	public function get_favicon() {
		// Get the ID of the link.
		$id = urlparam("id");
		if (is_null($id))
			self::error_plain(400, "Required parameter id wasn't set");

		// Get the link from the ID.
		$link = \LinkDepot\Link::FromID($id);
		if (is_null($link))
			self::error_plain(400, "Invalid link ID");

		// Check if we even have a favicon.
		if (is_null($link->favicon()))
			self::error_plain(404, "No favicon associated with this link");

		// Set the content type header and send the image.
		header("Content-Type: " . buffer_mime_type($link->favicon()));
		echo $link->favicon();
	}

	public function get_add() {
		// Ignore any format that isn't HTML.
		if (!$this->is_format(self::HTML)) {
			http_response_code(400);
			return;
		}

		// Display the add form.
		$params = reqmultiparams(["url", "title", "favicon"], "");
		require(__DIR__ . "/../templates/link/add.php"); 

	}

	public function post_add() {
		$params = reqmultiparams(["url", "title", "favicon", "shelf"]);

		// Check for required parameters.
		$required = required_params(["url", "title", "shelf"]);
		if ($required) {
			$this->error(400, "Required parameters " .
				implode(", ", $required) . " weren't set");
		}

		// Check if the requested shelf event exists.
		$shelf = Shelf::FromID($params["shelf"]);
		if (is_null($shelf))
			$this->error(400, "Shelf ID {$params["shelf"]} doesn't exist");

		// Build our new link object.
		$link = new Link(null, $params["title"], $params["url"], null, $shelf);
		$link->fetch_favicon((!empty($params["favicon"])) ?
			$params["favicon"] : null);

		try {
			// Save the changes to the database.
			$link->save();

			// Reply to the client.
			$this->set_content_type();
			switch ($this->format) {
			case self::HTML:
				require(__DIR__ . "/../templates/link/add_success.php");
				break;
			default:
				$this->render_default($link);
				break;
			}
		} catch (\PDOException $e) {
			$this->error(500, "Something went wrong while trying to commit ".
				"changes to the database", $e);
		}
	}

	protected function handler($method) {
		return array($this, $method);
	}
}

(new LinkHandler())->render();
