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
 * Creates a brand new XML document object.
 *
 * @param string $root Root element name.
 *
 * @return SimpleXMLElement XML document object.
 */
function xmldoc($root) {
	return new \SimpleXMLElement(
		"<?xml version=\"1.0\" encoding=\"utf-8\"?><$root/>");
}

/**
 * Gets the MIME type of a buffer.
 *
 * @param resource $buffer File contents to get the MIME type from.
 *
 * @return string MIME type of the buffer.
 */
function buffer_mime_type($buffer) {
	return (new \finfo(FILEINFO_MIME_TYPE))->buffer($buffer);
}

/**
 * Creates a simple, but effective, title string.
 *
 * @param string $desc An optional descriptor of the current page. This will be
 *                     automatically substituted by a PAGE_TITLE constant if
 *                     it is defined.
 *
 * @return string Formatted title.
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
 * @param string $parent Parent page script name without the extension.
 *
 * @return boolean Are the page names the same?
 */
function is_parent_page($parent) {
	return basename($_SERVER['PHP_SELF'], '.php') == $parent;
}

/**
 * Creates a proper href location based on our project's root path.
 *
 * @param string $loc      Location as if the resource was in the root of the
 *                         server with the slash prefix.
 * @param bool   $absolute Should the URL be absolute (including protocol and
 *                         domain name)?
 *
 * @return string Transposed location of the resource.
 */
function href($loc, $absolute = false) {
	// Pretty relative URL.
	$href = SITE_URL . $loc;
	$base = "";

	// Should we make this URL absolute globally?
	if ($absolute) {
		$base = (substr($_SERVER["SERVER_PROTOCOL"], 0, 5) == "HTTPS") ?
			"https" : "http";
		$base .= "://" . $_SERVER["HTTP_HOST"];
	}

	return $base . $href;
}

/**
 * Gets the value of an parameter or uses a default if one wasn't provided.
 *
 * @param string $name           Parameter name (key in $_GET, $_POST, etc.)
 * @param any    $default        Default value if the parameter wasn't set.
 * @param string $sanitize_regex Regex used to match characters for removal.
 *
 * @return any Parameter value provided or the default.
 */
function reqparam($name, $default = null, $sanitize_regex = null) {
	// Populate parameters list cache.
	static $params = null;
	if (is_null($params)) {
		$params = filter_input_array(INPUT_GET);
		if (!is_null(filter_input_array(INPUT_POST)))
			$params = array_merge($params, filter_input_array(INPUT_POST));
	}

	// Should we use the default value?
	if (!isset($params[$name]))
		return $default;

	// Should we sanitize the passed parameter?
	if (!is_null($sanitize_regex))
		return preg_replace($sanitize_regex, '', $params[$name]);

	// We've got it.
	return $params[$name];
}

/**
 * Gets the value of an URL parameter or uses a default if one wasn't provided.
 *
 * @param string $name           Parameter name (key in $_GET).
 * @param any    $default        Default value if the parameter wasn't set.
 * @param string $sanitize_regex Regex used to match characters for removal.
 *
 * @return any Parameter value provided or the default.
 */
function urlparam($name, $default = null, $sanitize_regex = null) {
	// Should we use the default value?
	if (!isset($_GET[$name]))
		return $default;

	// Should we sanitize the passed parameter?
	if (!is_null($sanitize_regex))
		return preg_replace($sanitize_regex, '', $_GET[$name]);

	// We've got it.
	return $_GET[$name];
}

/**
 * Gets the values of multiple request parameters.
 *
 * @param array  $names          Request parameter names get values from.
 * @param any    $default        Default value if a parameter wasn't set.
 * @param string $sanitize_regex Regex used to match characters for removal.
 *
 * @return array Associative array of request parameters and their values.
 */
function reqmultiparams($names, $default = null, $sanitize_regex = null) {
	$arr = array();

	// Go through parameter names.
	foreach ($names as $name)
		$arr[$name] = reqparam($name, $default, $sanitize_regex);

	return $arr;
}

/**
 * Checks for required URL parameters.
 *
 * @param any $names Name of a single required URL parameter or an array.
 *
 * @return any FALSE if all required parameters were passed or an array of the
 *             missing parameters.
 */
function required_params($names) {
	$missing = array();

	// Check the parameters.
	foreach ($names as $name) {
		if (is_null(reqparam($name)))
			array_push($missing, $name);
	}

	// Were all of the parameters set?
	if (empty($missing))
		return false;

	return $missing;
}

/**
 * Automatically generate a link if a string is a URL.
 *
 * @param string $str String to be checked for an URL.
 *
 * @return string Same string if it's not a URL. Otherwise an anchor tag.
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
 * @param string $label    Label of the item.
 * @param string $href     Relative URL this item points to or a full URL.
 * @param string $pagename Destination page script name without the extension.
 * 
 * @return string Fully-populated navbar item.
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
 *
 * @return boolean Returns TRUE for "1", "true", "on" and "yes". FALSE for "0",
 *                 "false", "off" and "no".
 *
 * @see https://www.php.net/manual/en/function.is-bool.php#124179
 */
function is_enabled($value) {
	// Do a proper boolean conversion.
	return filter_var($value, FILTER_VALIDATE_BOOLEAN);
}
