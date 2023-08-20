<?php
/**
 * DatabaseItem.php
 * Abstract class that helps represent an object that's contained in our
 * database.
 *
 * @author Nathan Campos <nathan@innoveworkshop.com>
 */

namespace LinkDepot;

require_once __DIR__ . "/../functions.php";
require_once __DIR__ . "/../vendor/autoload.php";

use LinkDepot\Renderable;
use PDO;

/**
 * Abstract class that helps represent an object that's contained in our
 * database.
 */
abstract class DatabaseItem extends Renderable {
	protected $id;

	/**
	 * Constructs an object from an database ID.
	 *
	 * @param int $id ID of the object in the database.
	 *
	 * @return object Pre-populated object or NULL if the ID wasn't found.
	 */
	abstract public static function FromID($id);

	/**
	 * Constructs an object from an database ID.
	 *
	 * @param string $table Database table name.
	 * @param int    $id    ID of the object in the database.
	 *
	 * @return array Database row associative array or NULL if the ID wasn't
	 *               found.
	 */
	protected static function FromTableID($table, $id) {
		$dbh = db_connect();

		// Query the database.
		$query = $dbh->prepare("SELECT * FROM $table WHERE id = :id LIMIT 1");
		$query->bindValue(":id", $id);
		$query->execute();

		// Check if we have the ID on record.
		$row = $query->fetch(PDO::FETCH_ASSOC);
		if (!$row)
			return null;

		return $row;
	}

	/**
	 * Constructs an object from a row (associative array) of the dabatase.
	 *
	 * @param array $row Associative array representing a database row of this
	 *                   object.
	 *
	 * @return object Pre-populated database object.
	 */
	abstract public static function FromRow($row);
}

