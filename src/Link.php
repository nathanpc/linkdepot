<?php
/**
 * Link.php
 * Object abstraction of a link in our application.
 *
 * @author Nathan Campos <nathan@innoveworkshop.com>
 */

namespace LinkDepot;

require_once __DIR__ . "/../functions.php";
require_once __DIR__ . "/../vendor/autoload.php";

use LinkDepot\DatabaseItem;
use PDO;

/**
 * Object abstraction of a link in our application.
 */
class Link extends DatabaseItem {
	private $title;
	private $url;
	private $favicon;
	private $shelf;

	/**
	 * Constructs a brand new object with some properties pre-populated.
	 *
	 * @param int      $id      Link ID in the database (NULL if not saved).
	 * @param string   $title   Title of the website's page.
	 * @param string   $url     Location of the link.
	 * @param resource $favicon Image blob of the website's icon.
	 * @param Shelf    $shelf   Shelf this link belongs to.
	 */
	public function __construct($id, $title, $url, $favicon = null,
			$shelf = null) {
		$this->id = $id;
		$this->title = $title;
		$this->url = $url;
		$this->favicon = $favicon;
		$this->shelf = $shelf;
	}

	public static function List() {
		throw new Exception("Can't list links without a shelf ID. Use " .
			'ListFromShelf($shelf_id)');
	}

	public static function FromID($id) {
		return self::FromRow(self::FromTableID("links", $id));
	}

	public static function FromRow($row, $shelf = null) {
		if (is_null($row))
			return null;

		return new self($row["id"], $row["title"], $row["url"], $row["favicon"],
			(is_null($shelf)) ? Shelf::FromID($row["shelf_id"]) : $shelf);
	}

	/**
	 * Gets a list of links stored in a shelf.
	 *
	 * @param Shelf $shelf Link shelf object.
	 *
	 * @return array List of links stored in the specified shelf.
	 */
	public static function ListFromShelf($shelf) {
		$links = array();
		$dbh = db_connect();

		// Query the database.
		$query = $dbh->prepare("SELECT * FROM links WHERE shelf_id = :id");
		$query->bindValue(":id", $shelf->id());
		$query->execute();

		// Check if we have the ID on record.
		while ($row = $query->fetch(PDO::FETCH_ASSOC))
			array_push($links, self::FromRow($row, $shelf));

		return $links;
	}

	/**
	 * Creates a link box table row element.
	 *
	 * @param bool $has_menu Should we include a menu in this item?
	 * @param bool $spacer   Insert an additional spacer row below this element.
	 *
	 * @return string HTML element of the populated link box table row.
	 */
	public function as_html($has_menu = false, $spacer = false) {
		// Ensure we properly encode the properties of the element.
		$title = htmlentities($this->title);
		$favicon = (!is_null($this->favicon)) ?
			$this->favicon : href("/assets/default-favicon.png");

		// Build up the element.
		$output = <<<HTML
			<tr class="link-item" onclick="open_link('{$this->url}', event)"
					onauxclick="open_link('{$this->url}', event)">
				<td class="col-icon">
					<img class="favicon" src="$favicon" />
				</td>
				<td class="col-desc">
					<div class="link-title">$title</div>
					<a class="link-url" href="{$this->url}"
						onclick="event.stopPropagation();"
						onauxclick="event.stopPropagation();">{$this->url}</a>
				</td>
			</tr>
		HTML;

		// Do we need the item's menu?
		if ($has_menu) {
			$output .= <<<HTML
				<tr class="link-actions">
					<td colspan="2">
						<a href="#">edit</a> ‧
						<a class="action-delete" href="#">delete</a>
					</td>
				</tr>
			HTML;
		}

		// Do we need a spacer?
		if ($spacer) {
			$output .= <<<HTML
				<tr class="spacer"></tr>
			HTML;
		}

		return $output;
	}

	public function as_array($expand = null) {
		// Build up the base array.
		$arr = array(
			"id" => $this->id,
			"title" => $this->title,
			"url" => $this->url,
			"favicon" => $this->favicon
		);

		// Give a full picture if requested.
		if (!is_null($expand) && $expand)
			$arr["shelf"] = $this->shelf->as_array();

		return $arr;
	}
};

