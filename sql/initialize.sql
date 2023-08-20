-- initialize.sql
-- Creates all of the tables required for our application.
--
-- Author: Nathan Campos <nathan@innoveworkshop.com>

-- Shelves collect links in them.
CREATE TABLE shelves(
	id    INTEGER NOT NULL PRIMARY KEY,
	title TEXT NOT NULL
);

-- Individual links.
CREATE TABLE links(
	id       INTEGER NOT NULL PRIMARY KEY,
	title    TEXT NOT NULL,
	url      TEXT NOT NULL,
	favicon  BLOB,
	shelf_id INTEGER NOT NULL,

	FOREIGN KEY (shelf_id) REFERENCES shelves(id)
		ON UPDATE CASCADE ON DELETE CASCADE
);

