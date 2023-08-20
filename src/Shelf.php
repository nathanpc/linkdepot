<?php
/**
 * Shelf.php
 * Object abstraction of a collection of links in the application.
 *
 * @author Nathan Campos <nathan@innoveworkshop.com>
 */

namespace LinkDepot;

require_once __DIR__ . "/../functions.php";
require_once __DIR__ . "/../vendor/autoload.php";

use LinkDepot\DatabaseItem;
use LinkDepot\Link;
use PDO;

/**
 * Object abstraction of a collection of links in our application.
 */
class Shelf extends DatabaseItem {
	private $title;

	/**
	 * Constructs a brand new object with some properties pre-populated.
	 *
	 * @param int      $id    Shelf ID in the database (NULL if not saved).
	 * @param string   $title Descriptive name for the collection.
	 */
	public function __construct($id, $title) {
		$this->id = $id;
		$this->title = $title;
	}

	public static function FromID($id) {
		return self::FromRow(self::FromTableID("shelves", $id));
	}

	public static function FromRow($row) {
		if (is_null($row))
			return null;

		return new self($row["id"], $row["title"]);
	}

	/**
	 * Lists the links associated with this collection.
	 *
	 * @return array Links that are part of this collection.
	 */
	public function links() {
		$links = array();
		$dbh = db_connect();

		// Query the database.
		$query = $dbh->prepare("SELECT * FROM links WHERE shelf_id = :id");
		$query->bindValue(":id", $this->id);
		$query->execute();

		// Check if we have the ID on record.
		while ($row = $query->fetch(PDO::FETCH_ASSOC))
			array_push($links, Link::FromRow($row));

		return $links;
	}

	/**
	 * Creates a link box table with its contents populated.
	 *
	 * @param bool $has_menu Should we include a menu in this item?
	 *
	 * @return string HTML of the link box table with its contents.
	 */
	public function as_html($has_menu = false) {
		// Build up our element's base.
		$output = <<<HTML
			<div class="link-shelf">
				<div class="shelf-header">
					<h3>{$this->title}</h3>
					<span class="shelf-actions">
						<a href="#">store</a> ‧
						<a href="#">manage</a> ‧
						<a class="action-delete" href="#">delete</a>
					</span>
				</div>

				<table class="link-box">
		HTML;

		// Go through our links populating the table.
		$links = $this->links();
		foreach ($links as $link) {
			$output .= $link->as_html($has_menu, $link != end($links)) . "\n";
		}

		// Close our containers.
		$output .= <<<HTML
				</table>
			</div>
		HTML;

		return $output;
	}

	public function as_array($expand = null) {
		$arr = array(
			"id" => $this->id,
			"title" => $this->title
		);

		// Bring out the full picture.
		if ($expand)
			$arr["links"] = links();

		return $arr;
	}
}

