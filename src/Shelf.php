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
	private $starred;

	/**
	 * Constructs a brand new object with some properties pre-populated.
	 *
	 * @param int      $id    Shelf ID in the database (NULL if not saved).
	 * @param string   $title Descriptive name for the collection.
	 */
	public function __construct($id, $title, $starred = false) {
		$this->id = $id;
		$this->title = $title;
		$this->starred = boolval($starred);
	}
	
	public static function List() {
		$arr = array();
		foreach (self::ListTable("shelves") as $row)
			array_push($arr, self::FromRow($row));

		return $arr;
	}

	public static function ListFavorites() {
		$dbh = db_connect();

		// Query the database.
		$query = $dbh->prepare(
			"SELECT * FROM shelves WHERE starred IS NOT FALSE");
		$query->execute();

		// Go through the favorites.
		$favorites = array();
		foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $row)
			array_push($favorites, self::FromRow($row));

		return $favorites;
	}

	public static function FromID($id) {
		return self::FromRow(self::FromTableID("shelves", $id));
	}

	public static function FromRow($row) {
		if (is_null($row))
			return null;

		return new self($row["id"], $row["title"], $row["starred"]);
	}

	public function save() {
		$this->commit("shelves", array(
			"title" => $this->title,
			"starred" => $this->starred
		));
	}

	public function delete() {
		$this->delete_from("shelves");
	}

	/**
	 * Lists the links associated with this collection.
	 *
	 * @return array Links that are part of this collection.
	 */
	public function links() {
		return Link::ListFromShelf($this);
	}

	/**
	 * Getter/setter for the title property.
	 *
	 * @param string $value Value of the property if used as setter.
	 *
	 * @return string Value of the property if used as getter.
	 */
	public function title($value = null) {
		if (!is_null($value))
			$this->title = $value;

		return $this->title;
	}

	/**
	 * Getter/setter for the starred property.
	 *
	 * @param string $value Value of the property if used as setter.
	 *
	 * @return string Value of the property if used as getter.
	 */
	public function starred($value = null) {
		if (!is_null($value))
			$this->starred = $value;

		return $this->starred;
	}

	/**
	 * Creates a link box table with its contents populated.
	 *
	 * @param bool $has_menu Should we include a menu in this item?
	 *
	 * @return string HTML of the link box table with its contents.
	 */
	public function as_html($has_menu = false) {
		$href = "href";
		$add_href = href("/link.php?action=add&shelf={$this->id}");
		$manage_href = href("/shelf.php?action=view&id={$this->id}");
		$delete_href = href("/shelf.php?action=delete&id={$this->id}");
		$fav_label = ($this->starred) ? "unfavorite" : "favorite";
		$fav_href = href("/shelf.php?action={$fav_label}&id={$this->id}");

		// Build up our element's base.
		$output = <<<HTML
			<div class="link-shelf" id="shelf-{$this->id}">
				<div class="shelf-header">
					<h3>{$this->title}</h3>
					<span class="shelf-actions">
						<a class="action-add" href="$add_href">add link</a> ‧
						<a href="$manage_href">manage</a> ‧
						<a class="action-star" href="$fav_href">$fav_label</a> ‧
						<a class="action-delete" href="$delete_href">delete</a>
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
			"title" => $this->title,
			"starred" => $this->starred
		);

		// Bring out the full picture.
		if ($expand) {
			$arr["links"] = array();
			foreach ($this->links() as $link)
				array_push($arr["links"], $link->as_array(false));
		}

		return $arr;
	}

	public function as_xml($parent = null, $expand = null) {
		// Create the root element.
		$xml = (is_null($parent)) ?
			xmldoc("shelf") : $parent->addChild("shelf");

		// Build up the base document.
		$xml->addAttribute("id", $this->id);
		$xml->addAttribute("starred", $this->starred);
		$xml->addChild("title", $this->title);

		// Bring out the full picture.
		if ($expand) {
			$node = $xml->addChild("links");
			foreach ($this->links() as $link)
				$link->as_xml($node, null);
		}

		return $xml;
	}
}

