<?php
/**
 * shelf.php
 * Public web interface to the link shelves stored in the system.
 *
 * @author Nathan Campos <nathan@innoveworkshop.com>
 */

namespace LinkDepot\API;

require_once __DIR__ . "/../functions.php";
require_once __DIR__ . "/../vendor/autoload.php";

use LinkDepot\Link as Link;
use LinkDepot\Shelf as Shelf;
use LinkDepot\RequestHandler as RequestHandler;

class ShelfHandler extends RequestHandler {
	public function __construct() {
		// Ensure we have a proper known state.
		$this->initialize();

		// Add handlers.
		$this->add_handler("GET", "list", $this->handler("get_list"));
		$this->add_handler("GET", "view", $this->handler("get_view"));
		$this->add_handler("POST", "add", $this->handler("post_edit_add"));
		$this->add_handler("POST", "edit", $this->handler("post_edit_add"));
		$this->add_handler("GET", "delete", $this->handler("get_delete"));
		$this->add_handler("POST", "delete", $this->handler("post_delete"));
	}

	public function get_list() {
		$expand = urlparam("expand", null);

		// Return the link.
		$this->set_content_type();
		switch ($this->format) {
		case self::HTML:
			require(__DIR__ . "/../public/shelves.php");
			break;
		case self::JSON:
			$json = array("shelves" => array());
			foreach (Shelf::List() as $shelf)
				array_push($json["shelves"], $shelf->as_array($expand));
			echo json_encode($json);
			break;
		case self::XML:
			$xml = xmldoc("shelves");
			foreach (Shelf::List() as $shelf)
				$shelf->as_xml($xml, $expand);
			echo $xml->asXML();
			break;
		default:
			$this->error(400, "Format not yet implemented");
			break;
		}
	}

	public function get_view() {
		// Get the shelf object.
		$shelf = $this->shelf_param();

		// Return the shelf.
		$this->set_content_type();
		switch ($this->format) {
		case self::HTML:
			require(__DIR__ . "/../templates/shelf/manage.php");
			break;
		default:
			$this->render_default($shelf);
			break;
		}
	}

	public function post_edit_add() {
		// Get the title.
		$title = reqparam("title");
		if (is_null($title))
			$this->error(400, "Required parameter title wasn't set");

		// Build our shelf object.
		$shelf = null;
		if ($this->action == "add") {
			// Create a brand new shelf object.
			$shelf = new Shelf(null, $title);
		} else {
			// Change everything.
			$shelf = $this->shelf_param();
			$shelf->title($title);
		}

		try {
			// Save the changes to the database.
			$shelf->save();

			// Reply to the client.
			$this->set_content_type();
			switch ($this->format) {
			case self::HTML:
				require(__DIR__ . "/../templates/shelf/manage.php");
				break;
			default:
				$this->render_default($shelf, null);
				break;
			}
		} catch (\PDOException $e) {
			$this->error(500, "Something went wrong while trying to commit ".
				"changes to the database", $e);
		}
	}

	public function get_delete() {
		// Ignore any format that isn't HTML.
		if (!$this->is_format(self::HTML)) {
			http_response_code(400);
			return;
		}

		// Get the shelf object and display the deletion confirmation.
		$shelf = $this->shelf_param();
		require(__DIR__ . "/../templates/shelf/delete.php"); 
	}

	public function post_delete() {
		// Get the shelf object and try to delete it.
		$shelf = $this->shelf_param();
		try {
			$shelf->delete();

			// Reply to the client.
			$this->set_content_type();
			switch ($this->format) {
			case self::HTML:
				require(__DIR__ . "/../templates/head.php");
				echo <<<HTML
					<p>
						{$shelf->title()} shelf successfully deleted.
					</p>
				HTML;
				require(__DIR__ . "/../templates/footer.php");
				break;
			default:
				$this->render_default($shelf);
				break;
			}
		} catch (\PDOException $e) {
			$this->error(500, "Something went wrong while trying to delete " .
				"the item from the database", $e);
		}
	}

	/**
	 * Gets a shelf object from the shelf ID parameter or prints an error message
	 * if anything goes wrong and halts the execution of the script.
	 *
	 * @return Shelf Requested shelf object.
	 */
	private function shelf_param() {
		// Get the link object.
		$id = $this->id_param();
		$shelf = Shelf::FromID($id);
		if (is_null($shelf))
			$this->error(404, "Shelf ID $id doesn't exist");

		return $shelf;
	}

	protected function handler($method) {
		return array($this, $method);
	}
}

(new ShelfHandler())->render();
