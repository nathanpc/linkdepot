<?php
/**
 * Renderable.php
 * Represents an object that can be rendered in all of the ways that our API
 * allows.
 *
 * @author Nathan Campos <nathan@innoveworkshop.com>
 */

namespace LinkDepot;

/**
 * Defines the base class of objects that can be rendered to the user and via
 * our API.
 */
abstract class Renderable {
	/**
	 * Renders the object in HTML.
	 *
	 * @return string HTML representation of this object.
	 */
	abstract public function as_html();

	/**
	 * Renders the object as an associative array.
	 *
	 * @param any $expand Determines which properties that may be expanded if
	 *                    available or NULL if nothing should be expanded.
	 *
	 * @return array Associative array representation of this object.
	 */
	abstract public function as_array($expand = null);

	/**
	 * Renders the object as an XML document.
	 *
	 * @param SimpleXMLElement $parent A parent XML element or NULL if we should
	 *                                 create a new document.
	 * @param any              $expand Determines which properties that may be
	 *                                 expanded if available or NULL if nothing
	 *                                 should be expanded.
	 *
	 * @return SimpleXMLElement XML representation of this object.
	 */
	abstract public function as_xml($parent = null, $expand = null);

	/**
	 * Renders the object as JSON.
	 *
	 * @param any  $expand Determines which properties that may be expanded if
	 *                     available or NULL if nothing should be expanded.
	 * @param bool $pretty Should the JSON output be pretty formatted?
	 *
	 * @return string JSON representation of this object.
	 */
	public function as_json($expand = null, $pretty = false) {
		$flags = 0;

		// Pretty print?
		if ($pretty)
			$flags |= JSON_PRETTY_PRINT;

		return json_encode($this->as_array($expand), $flags);
	}
}

