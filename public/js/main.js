/**
 * main.js
 * Adds a bit of interactivity to our website.
 *
 * @author Nathan Campos <nathan@innoveworkshop.com>
 */

"use strict";

/**
 * Opens a link to another website.
 *
 * @param url   URL to open.
 * @param event Optional. Click event object.
 */
function open_link(url, event) {
	// Use a default if the click event object wasn't passed to us.
	if (event === undefined)
		event = { "button": 0 };

	// Only open the link if the left or middle mouse button was used.
	if (event.button < 2)
		window.open(url, "_blank");

	console.log(event.button + " clicked");
}

