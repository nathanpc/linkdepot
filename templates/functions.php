<?php
/**
 * templates/functions.php
 * Handy template functions to easily build a page with our building blocks.
 *
 * @author Nathan Campos <nathan@innoveworkshop.com>
 */

/**
 * Creates a link box table row element.
 *
 * @param string $title    Website title.
 * @param string $url      Location of the website.
 * @param string $favicon  URL of the website's favicon.
 * @param bool   $has_menu Should we include a menu in this item?
 * @param bool   $spaces   Insert an additional spacer row below this element.
 *
 * @return string HTML element of the populated link box table row.
 */
function link_box_row($title, $url, $favicon, $has_menu = false,
		$spacer = true) {
	// Ensure we properly encode the properties of the element.
	$title = htmlentities($title);

	// Build up the element.
	$output = <<<HTML
		<tr class="link-item"
				onclick="open_link('$url')" onauxclick="open_link('$url')">
			<td class="col-icon">
				<img class="favicon" src="$favicon" />
			</td>
			<td class="col-desc">
				<div class="link-title">$title</div>
				<a class="link-url" href="$url"
					onclick="event.stopPropagation();"
					onauxclick="event.stopPropagation();">$url</a>

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
	if ($spacer) 
		$output .= "<tr class=\"spacer\"></tr>";

	return $output;
}

