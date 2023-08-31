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
		$this->add_handler("GET", "view", $this->handler("get_view"));
	}

	public function get_favicon() {
		// Get the link from the ID.
		$link = \LinkDepot\Link::FromID($this->id_param());
		if (is_null($link))
			self::error(400, "Invalid link ID");

		// Check if we even have a favicon.
		if (is_null($link->favicon()))
			self::error(404, "No favicon associated with this link");

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

	public function get_view() {
		// Get the link object.
		$link = Link::FromID($this->id_param());
		if (is_null($link))
			$this->error(404, "Link ID $id doesn't exist");

		// Return the link.
		$this->set_content_type();
		switch ($this->format) {
		case self::HTML:
			require(__DIR__ . "/../templates/head.php");
			echo <<<HTML
				<div class="link-shelf">
					<table class="link-box">
						{$link->as_html(true)}
					</table>
				</div>
			HTML;
			require(__DIR__ . "/../templates/footer.php");
			break;
		default:
			$this->render_default($link);
			break;
		}
	}

	/**
	 * Gets the link ID parameter or prints an error message if it's missing and
	 * halts the execution of the script.
	 *
	 * @return int Requested link ID.
	 */
	private function id_param() {
		$id = urlparam("id");
		if (is_null($id))
			self::error(400, "Required parameter id wasn't set");

		return $id;
	}

	protected function handler($method) {
		return array($this, $method);
	}
}

(new LinkHandler())->render();
