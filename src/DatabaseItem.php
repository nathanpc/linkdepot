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
	 * Gets a list of all of the objects of this type in the database.
	 *
	 * @return array List of objects of this type in the database.
	 */
	abstract public static function List();

	/**
	 * Gets a list of all of the rows in a table.
	 *
	 * @param string $table Database table name.
	 *
	 * @return array List of the rows from the database in the form of
	 *               associative arrays.
	 */
	protected static function ListTable($table) {
		$dbh = db_connect();

		// Query the database.
		$query = $dbh->prepare("SELECT * FROM $table");
		$query->execute();

		return $query->fetchAll(PDO::FETCH_ASSOC);
	}

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

	/**
	 * Commits any changes made to the object to the database.
	 */
	abstract public function save();

	/**
	 * Commits changes to the object to the database. Will create a new row in
	 * the database if the ID of the object is NULL.
	 *
	 * @param string $table Name of the table in the database.
	 * @param array  $cols  Associative array with the column name and value.
	 */
	protected function commit($table, $cols) {
		$is_insert = is_null($this->id);
		$dbh = db_connect();
		$stmt = null;

		// Make our life a bit easier when building up the statement.
		$col_names = array_keys($cols);
		$col_keys = array_map(function ($col) { return ":$col"; }, $col_names);

		// Create the base of the statement.
		if ($is_insert) {
			$stmt = "INSERT INTO $table (" . implode(", ", $col_names) .
				") VALUES (" . implode(", ", $col_keys) . ")";
		} else {
			$stmt = "UPDATE $table SET ";

			for ($i = 0, $size = count($col_names); $i < $size; ++$i) {
				$stmt .= $col_names[$i] . " = " . $col_keys[$i];
				if ($i < ($size - 1))
					$stmt .= ", ";
			}

			$stmt .= " WHERE id = :id";
		}

		// Build up the database query.
		$query = $dbh->prepare($stmt);
		if (!$is_insert)
			$query->bindValue(":id", $this->id);
		for ($i = 0, $size = count($col_keys); $i < $size; ++$i) {
			$query->bindValue($col_keys[$i], $cols[$col_names[$i]]);
		}

		// Execute our statement and get the new ID if we did an insert.
		$query->execute();
		if ($is_insert)
			$this->id = $dbh->lastInsertId();
	}

	/**
	 * Deletes the item from the database and sets the ID to NULL.
	 */
	abstract public function delete();

	/**
	 * Deletes the item from the database given a table using its ID.
	 *
	 * @warning This method will also set the item ID to NULL.
	 *
	 * @param string $table Database table name.
	 */
	protected function delete_from($table) {
		$dbh = db_connect();

		// Query the database.
		$query = $dbh->prepare("DELETE FROM $table WHERE id = :id");
		$query->bindValue(":id", $this->id);
		$query->execute();

		// Set the item ID to NULL to indicate it's no longer in the database.
		$this->id = null;
	}

	/**
	 * Gets the ID od the item in the database.
	 *
	 * @return int ID of the item in the database.
	 */
	public function id() {
		return $this->id;
	}
}

