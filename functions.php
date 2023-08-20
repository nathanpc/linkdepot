<?php
/**
 * functions.php
 * Provides a whole bunch of handy functions.
 *
 * @author Nathan Campos <nathan@innoveworkshop.com>
 */

require_once __DIR__ . "/config.php";

/**
 * Connects to the database and reuses the connection on subsequent calls.
 *
 * @return PDO Database connection object.
 */
function db_connect() {
	static $pdo = null;

	// Connect to the database in case it hasn't been done previously.
	if ($pdo == null)
		$pdo = new \PDO("sqlite:" . __DIR__ . DB_PATH);

	return $pdo;
}

/**
 * Creates a simple, but effective, title string.
 *
 * @param  string $desc An optional descriptor of the current page. This will be
 *                      automatically substituted by a PAGE_TITLE constant if
 *                      it is defined.
 * @return string       Formatted title.
 */
function site_title($desc = NULL) {
	// Default to just the application name.
	$title = APP_NAME;

	// Check if we should use the PAGE_TITLE constant.
	if (defined('PAGE_TITLE') && is_null($desc))
		$desc = constant('PAGE_TITLE');

	// Prepend a description if the user wants.
	if (!is_null($desc))
		$title = "$desc  - $title";

	return $title;
}

/**
 * Checks if a parent page name matches the current page name.
 *
 * @param  string  $parent Parent page script name without the extension.
 * @return boolean         Are the page names the same?
 */
function is_parent_page($parent) {
	return basename($_SERVER['PHP_SELF'], '.php') == $parent;
}

/**
 * Creates a proper href location based on our project's root path.
 *
 * @param  string $loc Location as if the resource was in the root of the server
 *                     with the slash prefix.
 * @return string      Transposed location of the resource.
 */
function href($loc) {
	return SITE_URL . $loc;
}

/**
 * Gets the value of an URL parameter or uses a default if one wasn't provided.
 *
 * @param  string $name    Parameter name (key in $_GET).
 * @param  any    $default Default value in case the parameter wasn't set.
 * @return any             Parameter value provided or the default.
 */
function urlparam($name, $default = NULL) {
	// Should we use the default value?
	if (!isset($_GET[$name]))
		return $default;

	// We've got it.
	return $_GET[$name];
}

/**
 * Automatically generate a link if a string is a URL.
 *
 * @param  string $str String to be checked for an URL.
 * @return string      Same string if it's not a URL. Otherwise an anchor tag.
 */
function auto_link($str) {
	// Check if we actually have an URL.
	if (!preg_match('/^[A-Za-z]+:(\/\/)?[A-Za-z0-9]/', $str))
		return $str;

	// Parse the URL. If it's seriously malformed just return the string.
	$url = parse_url($str);
	if ($url === false)
		return $str;

	// Build up an URL.
	$str_url = ((isset($url['scheme'])) ? '' : 'https://') . $str;
	$pretty_url = $url['host'] . ((isset($url['path'])) ? $url['path'] : '');

	return "<a href=\"$str_url\">$pretty_url</a>";
}

/**
 * Generates a navbar item.
 * 
 * @param  string $label    Label of the item.
 * @param  string $href     Relative URL this item points to or a full URL.
 * @param  string $pagename Destination page script name without the extension.
 * @return string           Fully-populated Bootstrap navbar item.
 */
function nav_item($label, $href, $pagename) {
	// Are we the current page?
	$current = is_parent_page($pagename);

	// Make sure we deal with relative URLs.
	if ($href[0] == '/')
		$href = href($href);

	// Don't link up if it's the current page.
	if ($current)
		return "<span class='nav-link'>$label</span>";

	// Provide a link to the page.
	return "<a class='nav-link' href='$href'>$label</a>";
}

/**
 * Checks for 'booleanic' values.
 *
 * @param any $value Value to be checked for booleaness.
 * @return boolean Returns TRUE for "1", "true", "on" and "yes". FALSE for "0",
 *                 "false", "off" and "no".
 *
 * @see https://www.php.net/manual/en/function.is-bool.php#124179
 */
function is_enabled($value) {
	// Do a proper boolean conversion.
	return filter_var($value, FILTER_VALIDATE_BOOLEAN);
}
