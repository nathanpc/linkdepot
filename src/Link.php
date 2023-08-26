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

	public function save() {
		$this->commit("links", array(
			"title" => $this->title,
			"url" => $this->url,
			"favicon" => $this->favicon,
			"shelf_id" => $this->shelf->id()
		));
	}

	/**
	 * Fetches a favicon from an URL and sets the favicon property.
	 *
	 * @param string $url Location of the favicon image or NULL if it should be
	 *                    fetched based on the link's URL.
	 */
	public function fetch_favicon($url = null) {
		// Should we use Google's proxy?
		if (is_null($url)) {
			$url = "https://www.google.com/s2/favicons?sz=64&domain_url=" .
				urlencode($this->url);
		}

		// Fetch the icon.
		$icon = file_get_contents($url);
		if (!$icon)
			return;

		// Set our favicon.
		$this->favicon = $icon;
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
	 * Getter/setter for the URL property.
	 *
	 * @param string $value Value of the property if used as setter.
	 *
	 * @return string Value of the property if used as getter.
	 */
	public function url($value = null) {
		if (!is_null($value))
			$this->url = $value;

		return $this->url;
	}

	/**
	 * Getter/setter for the favicon property.
	 *
	 * @param resource $value Value of the property if used as setter.
	 *
	 * @return resource Value of the property if used as getter.
	 */
	public function favicon($value = null) {
		if (!is_null($value))
			$this->favicon = $value;

		return $this->favicon;
	}

	/**
	 * Getter/setter for the shelf property.
	 *
	 * @param Shelf $value Value of the property if used as setter.
	 *
	 * @return Shelf Value of the property if used as getter.
	 */
	public function shelf($value = null) {
		if (!is_null($value))
			$this->shelf = $value;

		return $this->shelf;
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

