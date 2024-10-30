<?php
//=================================================================================
//
// File:        inc-lib-modals.php
//
// Description: Librairie de gestion des fenêtes modales
//
//=================================================================================

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

/**
 * Returns the HTML code to display a button.
 * This button allows to open a single modal information window.
 * @param string $button_text Text of the button
 * @param string $Modal_content Content of the modal window
 * @param string $Modal_title Title of the modal windows.
 * @param string $button_type Class to add to the <form>
 * @param int $height height in px of the modal form
 * @param int $width width in px of the modal form
 * @return string HTML code
 */
function boostrap_info_modal( string $button_text, string $Modal_content, string $Modal_title = '', string $button_type = '', int $height = 800, int $width = 800 ): string {
	if ( empty( $button_type ) ) {
		$button_type = 'button-secondary';
	}
	$css_id = uniqid();
	add_thickbox();
	$to_display = '<div id="' . $css_id . '" style="display:none;">';
	$to_display .= '<h3>' . ( empty( $Modal_title ) ? $button_text : $Modal_title ) . '</h3>';
	$to_display .= $Modal_content;
	$to_display .= '</div>';
	$to_display .= '<a href="#TB_inline?width='. strval( $width ) . '&height='. strval( $height ) . '&inlineId='. $css_id .'" style="display:inline-block;" class="thickbox button ' . $button_type . ' btn-sm" type="button">' . $button_text . '</a>';
	return $to_display;
}


/**
 * Returns the HTML code to display a button.
 * This button allows to open a new page with a link
 * @param string $button_text Text of the button
 * @param string $URL URL to open
 * @param string $button_type Class to add to the <form>
 * @return string HTML code
 */
function boostrap_button_link( string $button_text, string $URL, string $button_type = 'button-primary' ): string {
	$css_id = uniqid();
	$to_display = '<form style="display:inline-block;" action="' . $URL . '" method="get"  target="_blank" rel="noopener noreferrer">';
	$to_display .= '<input class="button ' . $button_type . ' btn-sm" type="submit" value="' . $button_text . '" name="' . $css_id . '" id="' . $css_id . '" />';
	$to_display .= '</form>';
	return $to_display;
}
?>
