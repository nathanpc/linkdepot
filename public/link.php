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
		$this->add_handler("GET", "view", $this->handler("get_view"));
		$this->add_handler("GET", "favicon", $this->handler("get_favicon"));
		$this->add_handler("GET", "add", $this->handler("get_add"));
		$this->add_handler("POST", "add", $this->handler("post_edit_add"));
		$this->add_handler("GET", "edit", $this->handler("get_edit"));
		$this->add_handler("POST", "edit", $this->handler("post_edit_add"));
		$this->add_handler("GET", "delete", $this->handler("get_delete"));
		$this->add_handler("POST", "delete", $this->handler("post_delete"));
	}

	public function get_view() {
		// Get the link object.
		$link = $this->link_param();

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

	public function get_favicon() {
		// Check if we even have a favicon.
		$link = $this->link_param();
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
		$form_action = href("/link.php?action={$this->action}");
		require(__DIR__ . "/../templates/link/edit.php"); 
	}

	public function post_edit_add() {
		$params = reqmultiparams(["url", "title", "favicon", "shelf"]);

		// Check for required parameters.
		$required = required_params(["url", "title", "shelf"]);
		if ($required) {
			$this->error(400, "Required parameters " .
				implode(", ", $required) . " weren't set");
		}

		// Check if the requested shelf exists.
		$shelf = Shelf::FromID($params["shelf"]);
		if (is_null($shelf))
			$this->error(400, "Shelf ID {$params["shelf"]} doesn't exist");

		// Build our link object.
		$link = null;
		if ($this->action == "add") {
			// Create a brand new link object.
			$link = new Link(null, $params["title"], $params["url"], null, $shelf);
			$link->fetch_favicon((!empty($params["favicon"])) ?
				$params["favicon"] : null);
		} else {
			// Change everything.
			$link = $this->link_param();
			$link->title($params["title"]);
			$link->url($params["url"]);
			$link->shelf($shelf);
			if (!empty($params["favicon"]))
				$link->fetch_favicon($params["favicon"]);
		}

		try {
			// Save the changes to the database.
			$link->save();

			// Reply to the client.
			$this->set_content_type();
			switch ($this->format) {
			case self::HTML:
				require(__DIR__ . "/../templates/link/edit_success.php");
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

	public function get_edit() {
		// Ignore any format that isn't HTML.
		if (!$this->is_format(self::HTML)) {
			http_response_code(400);
			return;
		}

		// Display the edit form.
		$link = $this->link_param();
		$params = $link->as_array();
		$params["favicon"] = "";
		$form_action = href("/link.php?action={$this->action}&id=" .
			$link->id());
		require(__DIR__ . "/../templates/link/edit.php"); 
	}

	public function get_delete() {
		// Ignore any format that isn't HTML.
		if (!$this->is_format(self::HTML)) {
			http_response_code(400);
			return;
		}

		// Get the link object and display the deletion confirmation.
		$link = $this->link_param();
		require(__DIR__ . "/../templates/link/delete.php"); 
	}

	public function post_delete() {
		// Get the link object and try to delete it.
		$link = $this->link_param();
		try {
			$link->delete();

			// Reply to the client.
			$this->set_content_type();
			switch ($this->format) {
			case self::HTML:
				require(__DIR__ . "/../templates/head.php");
				echo <<<HTML
					<p>
						Link to <a href="{$link->url()}">{$link->title()}</a>
						successfully deleted.
					</p>
				HTML;
				require(__DIR__ . "/../templates/footer.php");
				break;
			default:
				$this->render_default($link);
				break;
			}
		} catch (\PDOException $e) {
			$this->error(500, "Something went wrong while trying to delete " .
				"the item from the database", $e);
		}
	}

	/**
	 * Gets a link object from the link ID parameter or prints an error message
	 * if anything goes wrong and halts the execution of the script.
	 *
	 * @return Link Requested link object.
	 */
	private function link_param() {
		// Get the link object.
		$id = $this->id_param();
		$link = Link::FromID($id);
		if (is_null($link))
			$this->error(404, "Link ID $id doesn't exist");

		return $link;
	}

	protected function handler($method) {
		return array($this, $method);
	}
}

(new LinkHandler())->render();
