<?php
namespace MonLabo\Admin;
use MonLabo\Lib\{App, Polylang_Interface, Lib};
use MonLabo\Lib\Access_Data\{Access_Data};
use MonLabo\Frontend\{Html};

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

/////////////////////////////////////////////////////////////////////////////////////
// GENERATE FORM ELEMENTS IN HTML
/////////////////////////////////////////////////////////////////////////////////////
/*
class Html_Forms {

  field( $typeOfField, $isMandatory, $legend, $description, $initial_value, $css_id)
  _page_selector( $name, $isMandatory, $legend,$description, $initial_value, $child_of, $depth)
  begin_form( string $form_id )
  end_form( string $form_id, string $submit_button_text, $dashicon = '', $type = 'primary', $disabled = false )
  silent_transmit_ids( string $form_id, array $table_of_ids )
  silent_transmit_text( string $form_id, string $text )
  get_silent_transmited_ids( string $post_index, &$destination )
  silent_transmit_array_of_struct( string $form_id, array $table_of_string )
  get_silent_transmited_array_of_struct( string $post_index, array &$destination_array )
  select( $name, $values, $isMandatory, $legend, $description, $initial_value, $onchange )
  select_multiple( $name, $values, $isMandatory, $legend, $description, $initial_value, $onchange )
  radio_buttons( $name, $values, $isMandatory, $legend, $description, $initial_value, $onchange)
  checkboxes( $name, $values, $isMandatory, $legend, $description, $initial_value, $onchange )
  submit_button( $text, $css_id, $on_click, $dashicon, $type, $name, $class, bool $disabled = false )
  update_page_infobox( $item_id, $page_number, $wp_post_id, $type )

  _page_thumbnail( $wp_post_id, $page_number )
  _select_generic( $name, $values, $isMandatory, $legend, $description, $selectmultiple, $initial_value, $onchange)
  _large_text_field( $isMandatory, $legend, $name, $description, $initial_value, $css_id)
  _multi_post_addr_field( $isMandatory,$legend, $name, $description, $initial_value , $css_id)
  _person_multi_post_addr_field( $isMandatory,$legend, $name, $description, $initial_value , $css_id)
  _explicit_disabled_field( $legend, $description)
  _textarea( $isMandatory, $name, $description, $initial_value, $css_id )
  _url_field( $isMandatory, $legend, $name, $description, $initial_value, $css_id)
  _number_field( $isMandatory, $legend, $name, $description, $initial_value, $css_id)
  _color_field( $isMandatory, $legend, $name, $description, $initial_value, $css_id)
  _square_image_selector( $name, $description, $initial_value )
  _person_image_selector( $name, $description, $initial_value )
  _input_field( $isMandatory, $legend, $input_type, $name, $description, $imput_group_class, $imput_id, $initial_value, $input_class, $input_custom)
  _input_wp_post_id_or_url( $isMandatory, $legend, $name, $description, $initial_value, $page_number)
  _input_person_wp_post_id_or_url( $isMandatory, $legend, $name, $description, $initial_value, $page_number)
  _default_hidden_zone( $text, $css_id )
*/

/**
 * Class \MonLabo\Admin\Html_Forms
 * @package
 */
class Html_Forms {

	/**
	 * Current instance of Html
	 * @access private
	 * @var Html
	 */
	private $_html = null;

	/**
	 * Create a new Html_Forms class
	 * @access private
	 */
	public function  __construct( ) {
		$this->_html = new Html();
	}

	/**
	 * Generate an HTML code for a generic field of a form wich type is specified
	 * in argument
	 * @param string $typeOfField unique type of the field (equivalent to unique name)
	 * @param bool $isMandatory if true, fill this field is is mandatory.
	 * @param string $legend text that is displayed just before the field
	 * @param string $description text that is displayed after the field
	 * @param string|int|null|string[] $initial_value Initial value of the field
	 * @param string $css_id CSS ID of the form
	 * @return string HTML code
	 */
	public function field(
		string $typeOfField,
		bool $isMandatory,
		string $legend,
		string $description,
		$initial_value = '',
		string $css_id = ''
	): string {
		if ( is_null( $initial_value ) ) { $initial_value = ''; }
		switch ( $typeOfField ) {
			case 'descartes_publi_author_id':
				return $this->_number_field( $isMandatory, $legend, 'submit_' . $typeOfField, $description, strval( $initial_value ), $css_id );
			//case 'alternate_image':
			case 'external_url':
				return $this->_url_field( $isMandatory, $legend, 'submit_' . $typeOfField, $description, $initial_value, $css_id );
			/*
			//Non utilisé
			case 'title':
				return generate_text_form_small( $isMandatory, $legend, 'submit_' . $typeOfField, $description, $initial_value, $css_id );*/
			case 'color':
				return $this->_color_field( $isMandatory, $legend, 'submit_' . $typeOfField, $description, $initial_value, $css_id );
			case 'logo':
				return $this->_square_image_selector(  'submit_' . $typeOfField, $description, $initial_value );
			case 'image':
				return $this->_person_image_selector(  'submit_' . $typeOfField, $description, $initial_value );
			case 'adresse':
			case 'external_mentors':
			case 'external_students':
			case 'address_alt':
			case 'contact':
			case 'contact_alt':
				return $this->_textarea( $isMandatory, 'submit_' . $typeOfField, $description, $initial_value, $css_id );
			case 'explicit_disabled':
				return $this->_explicit_disabled_field( $legend, $description );
			case 'wp_post_ids':
				return $this->_multi_post_addr_field( $isMandatory, $legend, 'submit_' . $typeOfField, $description, $initial_value, $css_id );
			case 'person_wp_post_ids':
				return $this->_person_multi_post_addr_field( $isMandatory, $legend, 'submit_wp_post_ids', $description, $initial_value, $css_id );
			default:
				return $this->_large_text_field( $isMandatory, $legend, 'submit_' . $typeOfField, $description, $initial_value, $css_id );
		}
	}

	/**
	 * Generate an HTML code for a drop-down list form field of WordPress pages
	 * @param string $name name of the  drop-down list
	 * @param bool $isMandatory if true, select a page is mandatory.
	 * @param string $legend text that is displayed just before the field
	 * @param string $description text that is displayed after the field
	 * @param string $initial_value Pre-select an item of the list
	 * @param int $child_of Displays the sub-pages of a single Page only; uses the ID for a Page as the value. Defaults to 0 (displays all Pages ).
	 * @param int $depth This parameter controls how many levels in the hierarchy of pages are to be included in the list generated by wp_list_pages. The default value is 0 (display all pages, including all sub-pages ).
	 *  0 - Pages and sub-pages displayed in hierarchical ( indented ) form ( Default ).
	 *  -1 - Pages in sub-pages displayed in flat (no indent ) form.
	 *  1 - Show only top level Pages
	 *  2 - Value of 2 (or greater) specifies the depth (or level) to descend in displaying Pages.
	 * @return string HTML code
	 * @access private
	 */
	private function _page_selector(
			string $name,
			bool $isMandatory,
			string $legend,
			string $description,
			string $initial_value = App::NO_PAGE_OPTION_VALUE,
			int $child_of = 0,
			int $depth = 1
		): string {
		if( ( App::NO_PAGE_OPTION_VALUE == $child_of ) or empty( $child_of ) ) {
			$child_of = 0;
		}
		$output = '<div class="input-group">';
		if ( ! empty( $legend ) or ( ! $isMandatory ) ) {
			$output .= '<label for="' . $name . '">' . $legend;
			if ( ! $isMandatory ) {
				$output .= ' <small>(' . __( 'optional', 'mon-laboratoire' ) . ')</small>';
			}
			$output .= '</label>';
		}
		$output .= '<div class="input-group-addon"></div>';
		$args =	   array(
						'value_field'   => 'ID',
						'selected'		=> $initial_value,
					);
		$pages_published  = get_pages( array( 'child_of' =>  $child_of ) );
		$pages_draft  =     get_pages( array( 'child_of' =>  $child_of, 'post_status' => 'draft' ) );
		if ( ( ! empty( $pages_published ) ) or ( ! empty( $pages_draft ) ) ) {
			$output .= "<select name='" . esc_attr( $name ) . "' id='" . esc_attr(  $name ) . "'>\n";
			$output .= "\t<option value=\"" . esc_attr( App::NO_PAGE_OPTION_VALUE ) . '"'
				. ( App::NO_PAGE_OPTION_VALUE === $initial_value ? " selected" : '') . ">"
				. '&mdash; ' . __( 'No page', 'mon-laboratoire' ) . ' &mdash; ' . "</option>\n";
			if ( ! empty( $pages_published ) ) {
				$output .= "\t<optgroup label=\"" . __( 'Pages published', 'mon-laboratoire' ) . "\">\n";
				$output .= walk_page_dropdown_tree( $pages_published, $depth, $args );
				$output .= "\t</optgroup>\n";
			}
			if ( ! empty( $pages_draft ) ) {
				// @codeCoverageIgnoreStart
				// Not possible to unit test a draft page because WP test if this is admin mode to enable this code
				$output .= "\t<optgroup label=\"" . __( 'Draft pages', 'mon-laboratoire' ) . "\">\n";
				$output .= walk_page_dropdown_tree( $pages_draft, $depth, $args );
				$output .= "\t</optgroup>\n";
				// @codeCoverageIgnoreEnd
			}
			$output .= "</select>\n";
		}
		$output .= '<div class="description">' . $description . ' </div></div>';
		return $output;
	}

	/**
	 * Generate begining of the form
	 * @param string $form_id ID of the form
	 * @return string HTML code
	 */
	public function begin_form( string $form_id ) : string {
		$myurl=admin_url( 'admin.php?page=MonLabo_edit_members_and_groups&tab=tab_adv' );
		$retval = '  <form class="navbar-form" id="form_' . $form_id . '" accept-charset="utf-8" method="post" '
			.'enctype="multipart/form-data" action="' . $myurl . '&lang=all">'
			.'<div class="form-group">';
		return $retval;
	}

	/**
	 * Silent transmit ID table
	 * @param string $form_id ID of the form
	 * @param int[]|array<int,int[]> $table_of_ids IDs to silently submit
	 * @return string HTML code
	 */
	public function silent_transmit_ids( string $form_id, array $table_of_ids ) : string {
		$retval = '';
		//Be sure to get a list of int
		$table_to_encode = array();
		foreach ( $table_of_ids as $id ) {
			$table_to_encode[] = intval( $id );
		}
		//Transmit silently this list
		if ( ! empty( $table_of_ids ) ) {
			$retval .=
				'<input type="hidden" name="' . $form_id . '_submit_ids" value="'
				. htmlspecialchars( json_encode( $table_to_encode, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) )
				. '" />';
		}
		return $retval;
	}

	/**
	 * Silent transmit text
	 * @param string $form_id ID of the form
	 * @param string $text text to silently submit
	 * @return string HTML code
	 */
	public function silent_transmit_text( string $form_id, string $text ) : string {
		//Transmit silently this text
		return '<input type="hidden" name="' . $form_id . '_submit_text" value="' . $text . '" />';
	}

	/**
	 * Silent receive list of ids from POST
	 * @param string $post_index ID of the form
	 * @param int[] $destination structure received from POST
	 * @return void
	 */
	public function get_silent_transmited_ids( string $post_index, &$destination ) {
        if( isset( $_POST[ $post_index ] ) ) {
             $decoded = json_decode( htmlspecialchars_decode( stripslashes( $_POST[ $post_index ] ) ), true);
			//Be sure to get a list of int
			$destination = array();
			if ( is_array( $decoded ) ) {
				foreach ( $decoded as $id ) {
					$destination[] = intval( $id );
				}
			}
		}
    }

	/**
	 * Silent transmit string array
	 * @param string $form_id ID of the form
	 * @param array<mixed> $table_of_struct table of structure to silently submit
	 * @return string HTML code
	 */
	public function silent_transmit_array_of_struct( string $form_id, array $table_of_struct ) : string {
		$retval = '';
		//Transmit silently this list
		if ( ! empty( $table_of_struct ) ) {
			foreach ($table_of_struct as $key => $value) {
				$retval .=
				'<input type="hidden" name="' . $form_id . '_submit['. strval($key) .']" value="'
				. htmlspecialchars( json_encode( $value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) )
				. '" />';
			}
		}
		return $retval;
	}

	/**
	 * Silent receive arrays of struct from POST
	 * @param string $post_index ID of the form
	 * @param mixed[] $destination_array array of structure received from POST
	 * @return void
	 */
    public function get_silent_transmited_array_of_struct( string $post_index, array &$destination_array ) {
        if( isset( $_POST[ $post_index ] ) ) {
            if( is_array( $_POST[ $post_index ] ) ) {
                foreach ($_POST[ $post_index ] as $key => $value) {
                    $destination_array[$key] = json_decode( htmlspecialchars_decode( stripslashes( $value )), true);
                }
            }
        }
    }

	/**
	 * Generate end of the form with submit button
	 * @param string $form_id ID of the form
	 * @param string $submit_button_text Text inside the submit button
 	 * @param string $dashicon [OPTIONAL] Code of dashicon to display (https://developer.wordpress.org/resource/dashicons/#plugins-checked)
	 * @param string $type [OPTIONAL] Type of button as Bootstrap defines (https://getbootstrap.com/docs/4.0/components/buttons/)
	 * @param bool $disabled [OPTIONAL] Does the button is disabled ?
	 * @return string HTML code
	 */
	public function end_form( string $form_id, string $submit_button_text, $dashicon = '', $type = 'primary', $disabled = false ) : string {
		$retval = wp_nonce_field( $form_id . '_form', $form_id. '_form_wpnonce', true, false );
		$retval .= $this->submit_button( $submit_button_text, 'submit_' . $form_id, '', $dashicon, $type, '', '', $disabled );
		$retval .= '</div></form>';
		return $retval;
	}

	/**
	 * Generate an HTML code for a generic simple choice drop-down list form field
	 * @param string $name        see _select_generic
	 * @param array<string|int,string>|array<string|int,array<string,string>> $values see _select_generic
	 * @param bool $isMandatory   see _select_generic
	 * @param string $legend      see _select_generic
	 * @param string $description see _select_generic
	 * @param string|string[]|null $initial_value see _select_generic
	 * @param string $onchange see _select_generic
	 * @return string HTML code of the drop-down list form field
	 */
	public function select(
		string $name,
		array $values,
		bool $isMandatory,
		string $legend,
		string $description,
		$initial_value = '',
		string $onchange = ''
	): string {
		return $this->_select_generic( $name, $values, $isMandatory, $legend, $description, false, $initial_value, $onchange );
	}

	/**
	 * Generate an HTML code for a generic multiple choice drop-down list form field
	 * @param string $name        see _select_generic
	 * @param array<string|int,string>|array<string|int,array<string,string>> $values see _select_generic
	 * @param bool $isMandatory   see _select_generic
	 * @param string $legend      see _select_generic
	 * @param string $description see _select_generic
	 * @param string|string[]|int|int[]|null $initial_value see _select_generic
	 * @param string $onchange see _select_generic
	 * @return string HTML code of the drop-down list form field
	 */
	public function select_multiple(
		string $name,
		array $values,
		bool $isMandatory,
		string $legend,
		string $description,
		$initial_value = array(),
		string $onchange = ''
	): string {
		return $this->_select_generic( $name, $values, $isMandatory, $legend, $description, true, $initial_value, $onchange );
	}

	/**
	 * Generate an HTML code for radio buttons form field
	 * @param string $name name of the form field
	 * @param string[] $values array of value of each radio button
	 *  Thus each item of the array is 'value' => 'legend'
	 * @param bool $isMandatory if true, select a button is mandatory.
	 * @param string $legend text that is displayed just before the field
	 * @param string $description text that is displayed after the field
	 * @param string|null $initial_value Pre-select buttons
	 * @param string $onchange JavaScript code to execute when changing the
	 * radio buttons states
	 * @return string HTML code of the radio buttons form field
	 */
	public function radio_buttons(
		string $name,
		array $values,
		bool $isMandatory,
		string $legend,
		string $description,
		$initial_value = '',
		string $onchange = ''
	): string {
		$retval = '<div class="input-group">';
		$retval .= '<label for="submit_' . $name . '">' . $legend;
		if ( ! $isMandatory ) { $retval .= ' <small>(' . __( 'optional', 'mon-laboratoire' ) . ')</small>'; }
		$retval .= '</label>';
		$retval .= '<div class="input-group-addon"></div>';
		$retval .= '<div class="radio-group">';
		foreach ( $values as $key => $value ) {
			$retval .= '<input type="radio" class="form-control" id="submit_' . $name . '_' . $key . '" name="submit_' . $name . '"';
			if ( $isMandatory ) { $retval .= ' required '; }
			if ( '' != $onchange ) {
				$retval .= ' onchange="' . $onchange . '"';
			}
			$retval .= 'value="' . $key . '"';
			if ( ( strlen( $initial_value ) > 0 ) && ( $initial_value === $value ) ) {
				$retval .= ' checked';
			}
			$retval .= ' />' . $value . '<br />';
		}
		$retval .= '</div><div class="description">' . $description . ' </div></div>';
		return $retval;
	}

	/**
	 * Generate an HTML code for checkboxes form field
	 * @param string $name name of the form field
	 * @param string[] $values array of value of each checkbox
	 *  Thus each item of the array is 'value' => 'legend'
	 * @param bool $isMandatory if true, check a box is mandatory.
	 * @param string $legend text that is displayed just before the field
	 * @param string $description text that is displayed after the field
	 * @param string[]|null $initial_value Pre-select box
	 * @param string $onchange JavaScript code to execute when changing the checkboxes
	 * state
	 * @return string HTML code of the checkboxes form field
	 */
	public function checkboxes(
		string $name,
		array $values,
		bool $isMandatory,
		string $legend,
		string $description,
		$initial_value = array(),
		string $onchange = ''
	): string {
		$retval = '<div class="input-group">';
		$retval .= '<label for="sumbit_' . $name . '_'. array_key_first( $values ) .'">' . $legend;
		if ( ! $isMandatory ) { $retval .= ' <small>(' . __( 'optional', 'mon-laboratoire' ) . ')</small>'; }
		$retval .= '</label>';
		$retval .= '<div class="input-group-addon"></div>';
		$retval .= '<div class="checkboxes-group">';
		foreach ( $values as $key => $value ) {
			$retval .= '<input type="checkbox" name="submit_' . $name . '[' . $key . ']" id="submit_' . $name . '_' . $key . '" value="' . $key . '"';
			if ( ( null != $initial_value ) and ( in_array( $value, $initial_value ) ) ) {
				$retval .= ' checked';
			}
			if ( '' != $onchange ) {
				$retval .= ' onchange="' . $onchange . '"';
			}
			$retval .= ' /> ' . $value . '<br />';
		}
		$retval .= '</div>';
		$retval .= '<div class="description">' . $description . ' </div></div>';
		return $retval;
	}


	/**
	 * Generate an HTML code for a submitting button of a form
	 * @param string $text id of the person
	 * @param string $css_id CSS ID of the buttonGENERATION D’ELEMENTS
	 * @param string $on_click Javascrit code to launch when button is clicked on
	 * @param string $dashicon Code of dashicon to display (https://developer.wordpress.org/resource/dashicons/#plugins-checked)
	 * @param string $type Type of button as Bootstrap defines (https://getbootstrap.com/docs/4.0/components/buttons/)
	 * @param string $name property 'name' of the button
	 * @param string $class class of the button
	 * @param bool $disabled do the button is disabled ?
	 * @return string HTML code
	 */
	public function submit_button(
		string $text,
		string $css_id = '',
		string $on_click = '',
		string $dashicon = '',
		string $type = 'primary',
		string $name = '',
		string $class = '',
		bool $disabled = false
	): string {
	 	$id_text = '';
		if ( '' != $css_id ) {
			$id_text = ' id="' . $css_id . '"';
		}
		$retval = '<button ';
		if ( '' != $on_click ) {
			$retval .= 'onclick="' . $on_click . '" ';
		}
		if ( '' != $name ) {
			$retval .= 'name="' . $name . '" ';
		}
		if ( '' != $class ) {
			$class = ' ' . $class;
		}
		$retval .= 'type="submit" class="btn btn-' . $type . $class . '"' . $id_text . ( $disabled ? ' disabled' : '' ) . '>';
		if ( '' != $dashicon ) {
			$retval .= $this->_html->dashicon( $dashicon ) . '&nbsp;';
		}
		$retval .= $text . '</button>';
		return $retval;
	}

	

	/**
	 * Generate an HTML for signaling if a page is already attributed
	 * @param string $type of page 'person', 'team', 'thematic' or 'unit'
	 * @param int $item_id id of the item (person or stucture ID)
	 * @param string|int|null $wp_post_id Page ID to test
	 * @return string HTML code
	 */
	private function _tell_if_page_is_already_attributed(
		string $type,
		int $item_id,
		$wp_post_id
		//$alternate_image = ''
	): string {
		$html = '';
		if ( !empty( $wp_post_id ) ) {
			$accessData = new Access_Data();
			$other_items_txt = array();
			foreach ( array( 'person', 'team', 'thematic', 'unit' ) as $check_type ) {
				$page_items = $accessData->get_itemIds_from_wpPostId( $check_type, $wp_post_id );
				foreach ( $page_items as $key => $value ) {
					if ( ( $check_type != $type) or ( $value != $item_id ) ) {
						$classname = '\MonLabo\Lib\Person_Or_Structure\\'. ucfirst( $check_type );
						$item = new $classname( 'from_id', $value );
						$other_items_txt[ $check_type ][ $key ] = $this->_html->item_name_with_admin_link( $check_type, $item->info );
					}
				}
			}
			if ( !empty( $other_items_txt  ) ) {
				$html .= '<br /><strong>'.__( 'Attention this page is already assigned to', 'mon-laboratoire' ).' </strong>';
				if ( isset( $other_items_txt['person'] ) ) {
					$html .= '<br />' . __( 'Person(s):', 'mon-laboratoire' ). ' ' . Lib::secured_implode(', ', $other_items_txt['person'] );
				}
				if ( isset( $other_items_txt['team'] ) ) {
					$html .= '<br />' . __( 'Team(s):', 'mon-laboratoire' ). ' ' . Lib::secured_implode(', ', $other_items_txt['team'] );
				}
				if ( isset( $other_items_txt['thematic'] ) ) {
					$html .= '<br />' . __( 'Thematic(s):', 'mon-laboratoire' ). ' ' . Lib::secured_implode(', ', $other_items_txt['thematic'] );
				}
				if ( isset( $other_items_txt['unit'] ) ) {
					$html .= '<br />' . __( 'Unit(s):', 'mon-laboratoire' ). ' ' . Lib::secured_implode(', ', $other_items_txt['unit'] );
				}
			}
		}
		return $html;
	}

	/**
	 * Generate an HTML code for changing thumbnail and infobox of a page
	 * @param int|null $item_id id of the item
	 * @param int $page_number number of the page (persons can have several pages)
	 * @param string|int|null $wp_post_id
	 * @param string $type of item : 'person', 'team', 'thematic' or 'unit'
	 * @return string ex: array( $id => $value )
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	public function update_page_infobox(
		$item_id,
		int $page_number,
		$wp_post_id = null,
		string $type = 'person'
	): string {

		$retval = '';
		$html = '';
		if ( '' ==  $wp_post_id ) { $wp_post_id = App::NEW_PAGE_OPTION_VALUE; }
		if ( is_integer( $wp_post_id ) ) { $wp_post_id = strval( $wp_post_id ); }
		switch ( $wp_post_id ) {
			case App::NO_PAGE_OPTION_VALUE: // Person with no page
				break;

			case App::NEW_PAGE_OPTION_VALUE: // New item
				break;

			default: // Item with valid page
				$is_numeric_wp_post_id = ( ( (string) abs( intval( $wp_post_id ) ) ) ==  $wp_post_id );
				if  ( ( $is_numeric_wp_post_id )
						 and ( null !== get_post( intval( $wp_post_id ) ) ) )
					{ //Si la page existe


					$wp_post_id = intval( $wp_post_id );
					$post_status = get_post_status( $wp_post_id );
					if ( 'draft' === $post_status ) {
						$html .= '<fieldset><legend>'. __( 'Draft page', 'mon-laboratoire' ). ' </legend>';
					}
					$html .= $this->_page_thumbnail( $wp_post_id, $page_number );
					$html .= '<div class="description">';
					$Polylang_Interface = new Polylang_Interface();
					$html .= $Polylang_Interface->get_edit_link_if_exists( $wp_post_id );
					$html .= $this->_tell_if_page_is_already_attributed( $type, $item_id, $wp_post_id );

					$html .= '</div>';
					if ( 'draft' === $post_status ) {
						$html .= '</fieldset>';
					}
				} else {
					$is_valid_url = filter_var($wp_post_id, FILTER_VALIDATE_URL);
					if ( $is_numeric_wp_post_id ) {
						$html .= '<p>' .  sprintf( __( 'The page #%s does not exist', 'mon-laboratoire' ), $wp_post_id ) . '</p>';
					} elseif ( false === $is_valid_url ) {
						$html .= '<p>' .  sprintf( __( 'URL %s is invalid', 'mon-laboratoire' ), $wp_post_id ) . '</p>';
					}
				}
				break;
		}
		if ( '' != $html ) {
			$retval .= '<div class="input-group">' . $html . '</div>';
		}
		return $retval;
	}

	/////////////////////////////////////////////////////////////////////////////////////
	// PRIVATE METHODS
	/////////////////////////////////////////////////////////////////////////////////////

	/**
	 * Generate an HTML code for a thumbnail of a page of a person
	 * @param int $wp_post_id ID of the post to display thumbnail
	 * @param int $page_number number of the page (persons can have several pages)
	 * @return string HTML code
	 */
	private function _page_thumbnail( int $wp_post_id, int $page_number ): string {
		$retval = '<a class="hover-zoom-square30-no-border">';
		if ( ( $wp_post_id > 0 ) && has_post_thumbnail( $wp_post_id ) ) {
			$thumbnail = get_the_post_thumbnail(
				$wp_post_id,
				array( 150, 150 ),
				array( 'class' => '',  'id' => "image-preview_" . $page_number )
			);
			return $retval . $thumbnail . '</a>';
		}
		return $retval . '</a>';
	}

	/**
	 * Generate an HTML code for a generic drop-down list form field
	 * @param string $name name of the  drop-down list
	 * @param array<string|int,string>|array<string|int,array<string,string>> $values array of value of each line of the list
	 *  Items can be simple array of values or array of array of values for
	 *		grouping items (<optgroup>). Thus each item can be either:
	 *		- 'value' => 'legend'
	 *		or
	 *		- 'name of the grouping' =>  [  'value1' => 'legend 1',
	 *									  'value2' => 'legend 2',
	 *									   ....
	 *								   ]
	 * @param bool $isMandatory if true, select a value is mandatory.
	 * @param string $legend text that is displayed just before the field
	 * @param string $description text that is displayed after the field
	 * @param bool $selectmultiple if true, this is a multiple-select list.
	 *  if false, user can only select one value.
	 * @param string|string[]|int|int[]|null $initial_value Pre-select items of the list
	 * - Single choice drop-down list: the item to pre-select
	 * - Multiple choice drop-down list : array of items to pre-select
	 * @param string $onchange JavaScript code to execute when changing the selection
	 *  of the drop-down list.
	 * @return string HTML code of the drop-down list form field
	 * @access private
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	private function _select_generic(
		string $name,
		array $values,
		bool $isMandatory,
		string $legend,
		string $description,
		bool $selectmultiple = false,
		$initial_value = array(),
		string $onchange = ''
	): string {

		$retval = '<div class="input-group">';
		if ( ! $isMandatory or ! empty( $legend ) ) {
			$retval .= '<label for="submit_' . $name . '">' . $legend;
			if ( ! $isMandatory ) { $retval .= ' <small>(' . __( 'optional', 'mon-laboratoire' ) . ')</small>'; }
			$retval .= '</label>';
		}
		$retval .= '<div class="input-group-addon"></div>';
		$retval .= '<select ' . ( $selectmultiple ? 'multiple ' : '' )
			. 'class="form-control" name="submit_' . $name . ( $selectmultiple ? '[]' : '' ) . '"';
		$retval .= ' id="submit_' . $name . '"';
		if ( $isMandatory ) { $retval .= ' required'; }
		if ( '' != $onchange ) {
			$retval .= ' onchange="' . $onchange . '"';
		}
		$retval .= '>';

		foreach ( $values as $key => $value ) {
			$disabled = '';
			if ( 'disabled' === $key ) {
				$disabled = ' disabled';
			}
			if ( ( ! is_array( $value ) ) or empty( $value ) ) {
				$retval .= '<option value="' . $key . '"';
				if ( true === $selectmultiple ) {
					if ( ( is_array( $initial_value ) ) and ( in_array( $key, $initial_value ) ) ) {
						$retval .= ' selected';
					}
				} else {
					if (
						( strlen( $initial_value ) > 0 )
						&& ( ( (string) $initial_value ) ===  ( (string) $key ) )
					) {
						$retval .= ' selected';
					}
				}
				$retval .= $disabled . '>' . ( ( ! empty( $value ) ) ? $value : '' ) . '</option>';
			} else {
				//Avec des optgroup si tableau de tableau
				$retval .= '<optgroup label="' . $key . '">';
					foreach ( $value as $sub_key=>$sub_value ) {
						$retval .= '<option value="' . $sub_key . '"';
						if ( true === $selectmultiple ) {
							if ( ( null != $initial_value ) and ( in_array( $sub_key, $initial_value ) ) ) {
								$retval .= ' selected';
							}
						} else {
							if (
								( strlen( $initial_value ) > 0 )
								&& ( ( (string) $initial_value ) === ( (string) $sub_key ) )
							) {
								$retval .= ' selected';
							}
						}
						$retval .= $disabled . '>' . $sub_value . '</option>';
					}
				$retval .= '</optgroup>';
			}
		}
		$retval .= '</select><div class="description">' . $description . ' </div></div>';
		return $retval;
	}

	/**
	 * Generate an HTML code for a large text area form field
	 * @param bool $isMandatory if true, fill this field is mandatory.
	 * @param string $legend text that is displayed just before the field
	 * @param string $name name of the form field
	 * @param string $description text that is displayed after the field
	 * @param string $initial_value initial value
	 * @param string $css_id CSS id for the <input> tag.
	 * @return string HTML code of form field
	 * @access private
	  */
	private function _large_text_field(
		bool $isMandatory,
		string $legend,
		string $name,
		string $description,
		string $initial_value = '',
		string $css_id = ''
	): string {
		return $this->_input_field(  $isMandatory, $legend, 'text', $name, $description, 'input-group-large', $css_id,
							$initial_value );
	}
		/**
	 * Generate an HTML code for a large text area form field
	 * @param bool $isMandatory if true, fill this field is mandatory.
	 * @param string $legend text that is displayed just before the field
	 * @param string $name name of the form field
	 * @param string $general_description text that is displayed after the field
	 * @param string|string[] $initial_value initial value
	 * @param string $css_id CSS id for the <input> tag.
	 * @return string HTML code of form field
	 * @access private
	  */
	  private function _multi_post_addr_field(
		bool $isMandatory,
		string $legend,
		string $name,
		string $general_description,
		$initial_value = array(),
		string $css_id = ''
	): string {
		$retval = ( $css_id !== '' ? '<div class="' . $css_id . '">' : '');
		$retval .= ( '' !== $general_description ? '<p>' . $general_description . '</p>' : '');
		$description = '';
		if ( ! is_array( $initial_value ) ) {
			$initial_value = explode( ',', $initial_value ); //Retrocompatibily in case of damaged DB
		}
		if ( empty ( $initial_value ) ) {
			$initial_value = array( '' );
		}
		$count = 0;
		foreach ( $initial_value as $value ) {
			$description = sprintf( __( 'Page %d', 'mon-laboratoire' ), $count + 1 );
			if ( ! empty( $value ) ) {
				$retval .= $this->_input_wp_post_id_or_url(
					$isMandatory,
					'',
					$name . '[' . $count . ']',
					$description,
					$value,
					$count
				).'<br/>';
				$count += 1;
			}
		}
		if ( 0 !== $count ) {
			$description = __( 'Additional page', 'mon-laboratoire' );
		}
		$retval .= $this->_input_wp_post_id_or_url(  $isMandatory, $legend, $name . '[' . $count . ']',
				 $description, '', $count );
		$retval .= ( '' !== $css_id ? '</div>' : '');
		return $retval;
	}

		/**
	 * Generate an HTML code for a large text area form field for a person
	 * @param bool $isMandatory if true, fill this field is mandatory.
	 * @param string $legend text that is displayed just before the field
	 * @param string $name name of the form field
	 * @param string $general_description text that is displayed after the field
	 * @param string|string[] $initial_value initial value
	 * @param string $css_id CSS id for the <input> tag.
	 * @return string HTML code of form field
	 * @access private
	  */
	  private function _person_multi_post_addr_field(
		bool $isMandatory,
		string $legend,
		string $name,
		string $general_description,
		$initial_value = array(),
		string $css_id = ''
	): string {
		$retval = ( '' !== $css_id ? '<div class="' . $css_id . '">' : '');
		$retval .= ( '' !== $general_description ? '<p>' . $general_description . '</p>' : '');
		if ( ! is_array( $initial_value ) ) {
			$initial_value = explode( ',', $initial_value ); //Retrocompatibily in case of damaged DB
		}
		if ( empty ( $initial_value ) ) {
			$initial_value = array( '' );
		}
		$count = 0;
		$description = __( 'Main page', 'mon-laboratoire' );
		foreach ( $initial_value as $value ) {
			if ( ! empty( $value ) ) {
				if ( 0 !== $count ) {
					$description = sprintf( __( 'Page %d', 'mon-laboratoire' ), $count + 1 );
				}
				$retval .= $this->_input_person_wp_post_id_or_url(
					$isMandatory,
					'',
					$name . '[' . $count . ']',
					$description ,
					$value,
					$count
				).'<br/>';
				$count += 1;
			}
		}
		if ( 0 != $count ) {
			$description = __( 'Additional page', 'mon-laboratoire' );
		}
		$retval .= $this->_input_person_wp_post_id_or_url(  $isMandatory, $legend, $name . '[' . $count . ']',
				 $description, '', $count );
		$retval .= ( $css_id !== '' ? '</div>' : '');
		return $retval;
	}



	/**
	 * Generate an HTML code radio buttons for managing pages of Persons, Teams, Thematics or Units
	 * @param int $page_number
	 * @param string $name name of the form field
	 * @param string $description text that is displayed after the field
	 * @param string $initial_value initial value
	 * @return string HTML code of form field
	 * @param string $type of page 'person', 'team', 'thematic' or 'unit'
	 * @access private
	  */
	private function _page_radio_buttons(
		int $page_number,
		string $name,
		string $description,
		string $initial_value,
		string $type
	) : string {
		$radioname = 'pageradio['.$page_number.']';
		$radioid = 'pageradio'.$page_number.'_';
		$retval = '<div class="input-group MonLabo-wpPostId">';
		$retval .= '<label for="submit_' . $name . '" class="radiobutton-label">' . $description . '&nbsp;:</label>';
		$radioInit = 'edit';

		$page = new Page( 'from_id', $initial_value );
		$supl_choose_test = $page->is_a_person_page();
		if ( 'person' !== $type ) {
			$supl_choose_test = true;
		}

		if ( is_numeric( $initial_value )
			and ( null !== get_post( intval( $initial_value ) ) )
			and $supl_choose_test
		) { //Si la page existe et est une page de personne
			$radioInit = 'choose';
		}
		if ( '' === $initial_value ) {
			$radioInit = 'none';
		}
		$retval .= '<div class="radiobuttons input-group">'
			. '<input type="radio" id="' . $radioid . 'A" name="' . $radioname . '" value="new"'
			. ' onclick="PageButtonClick(\'new\', \'' . $page_number . '\')" >' //$radioInit === 'new' never happend
			. '<label for="' . $radioid . 'A" class="roundleft">' .__( 'Create', 'mon-laboratoire' ) . '&nbsp;' . $this->_html->dashicon( 'plus-alt2' ) . '</label>'
			. '<input type="radio" id="' . $radioid . 'B" name="' . $radioname . '" value="choose"'
			. ' onclick="PageButtonClick(\'choose\', \'' . $page_number . '\')" '. ( 'choose' === $radioInit ? ' checked' : '' ) .'>'
			. '<label for="' . $radioid . 'B">' . __( 'Choose', 'mon-laboratoire' ) . '&nbsp;' . $this->_html->dashicon( 'arrow-down-alt2' ) . '</label>'
			. '<input type="radio" id="'.$radioid.'C" name="'.$radioname.'" value="edit"'
			. ' onclick="PageButtonClick(\'edit\', \'' . $page_number . '\')" '. ( 'edit' === $radioInit ? ' checked' : '' ) . '>'
			. '<label for="'.$radioid.'C">'.__( 'Edit', 'mon-laboratoire') . '&nbsp;' . $this->_html->dashicon( 'admin-customizer' ) . '</label>'
			. '<input type="radio" id="' . $radioid . 'D" name="' . $radioname . '" value="none"'
			. ' onclick="PageButtonClick(\'none\', \'' . $page_number . '\')" ' . ( 'none' === $radioInit ? ' checked' : '' ) . '>'
			. '<label for="' . $radioid . 'D" class="roundright">' . _x( 'None', 'page', 'mon-laboratoire' )  . '&nbsp;' . $this->_html->dashicon( 'no-alt' ) . '</label>'
			. '</div>';
		return $retval;
	}

	/**
	 * Generate an HTML code for an input of wp_post_id or a URL
	 * @param bool $isMandatory if true, fill this field is mandatory.
	 * @param string $legend text that is displayed just before the field
	 * @param string $name name of the form field
	 * @param string $description text that is displayed after the field
	 * @param string $initial_value initial value
	 * @param int $page_number
	 * @return string HTML code of form field
	 * @access private
	  */
	  private function _input_person_wp_post_id_or_url(
		bool $isMandatory,
		string $legend,
		string $name,
		string $description,
		string $initial_value = App::NO_PAGE_OPTION_VALUE,
		int $page_number = 0
	): string {
		$options10 = get_option( 'MonLabo_settings_group10' );
		$retval = $this->_page_radio_buttons( $page_number, $name, $description, $initial_value, 'person' );
		$retval .= '<div class="input-group-addon">';
		if ( array_key_exists( 'MonLabo_perso_page_parent', $options10 ) ) {
			$retval .= $this->_default_hidden_zone(
					$this->_page_selector(
						"dropdown_$name",
						true,
						'',
						'',
						$initial_value,
						intval( $options10['MonLabo_perso_page_parent'] )
					),
					'hidd_drop_wp_post_ids_'.$page_number
				);
		}
		$retval .= '</div>';
		$retval .= $this->_large_text_field( $isMandatory, $legend, $name, "", $initial_value, 'submit_wp_post_ids_'. $page_number );
		$retval .= '<div id="delayedLoadDivThumbnail_' . strval( $page_number ) . '" class="delayedLoadDivThumbnail"><!-- Nous allons afficher ici la suite en asynchone grâce à ajax. --></div>';
		$retval .= '</div>';
		return $retval;
	}

	/**
	 * Generate an HTML code for an input of Wp_post_id or a URL
	 * @param bool $isMandatory if true, fill this field is mandatory.
	 * @param string $legend text that is displayed just before the field
	 * @param string $name name of the form field
	 * @param string $description text that is displayed after the field
	 * @param string $initial_value initial value
	 * @param int $page_number
	 * @return string HTML code of form field
	 * @access private
	  */
	  private function _input_wp_post_id_or_url(
		bool $isMandatory,
		string $legend,
		string $name,
		string $description,
		string $initial_value = App::NO_PAGE_OPTION_VALUE,
		int $page_number = 0
	): string {
		$retval = $this->_page_radio_buttons( $page_number, $name, $description, $initial_value, 'item' );
		$retval .= '<div class="input-group-addon">';
		$retval .= $this->_default_hidden_zone(
				$this->_dropdown_pages( 'dropdown_'.$name, $initial_value ) ,
				'hidd_drop_wp_post_ids_'.$page_number
			);
		$retval .= '</div>';
		$retval .= $this->_large_text_field( $isMandatory, $legend, $name, "", $initial_value, 'submit_wp_post_ids_'. $page_number );
		$retval .= '<div id="delayedLoadDivThumbnail_' . strval( $page_number ) . '" class="delayedLoadDivThumbnail"><!-- Nous allons afficher ici la suite en asynchone grâce à ajax. --></div>';
		$retval .= '</div>';
		return $retval;
	}


	/**
	 * Generate an HTML code for a selecting a page
	 * @param string $name name of the form field
	 * @param string $wp_post_id id of page selected
	 * @return string HTML code of form field
	 * @access private
	  */
	private function _dropdown_pages( string $name, string $wp_post_id): string {
		$retval = wp_dropdown_pages(
			array(
			'name'			  => $name ,
			'echo'			  => 0,
			'show_option_none'  => '&mdash;' . __( 'Select', 'mon-laboratoire' ) . '&mdash;',
			'option_none_value' => '0',
			'selected'		  => is_numeric( $wp_post_id ) ? intval( $wp_post_id ) : '' ,
		)
		);
		return $retval;
	}

	/**
	 * Generate an HTML code ti a default hidden zone
	 * @param string $text id of the person
	 * @param string $css_id CSS ID of the showing button
	 * @return string HTML code
	 * @access private
	 */
	private function _default_hidden_zone(
		string $text,
		string $css_id = ''
	): string {
	 	$retval = '<div id="'.$css_id.'" style="display:none;">';
		$retval .= $text.'</div>';
		return $retval;
	}

	/**
	 * Generate an HTML code for a disabled form field
	 * @param string $legend text that is displayed just before the field
	 * @param string $description text that is displayed after the field
	 * @return string HTML code of form field
	 * @access private
	  */
	private function _explicit_disabled_field(
		string $legend,
		string $description
	): string {
		$retval = '<div class="input-group">';
		$retval .= '<div class="input-group-addon"></div>';
		$retval .= '<label>' . $legend . '</label>';
		$retval .= '<div class="description">' . $description . ' </div></div>';
		return $retval;
	}

	/**
	 * Generate an HTML code for a text area form field
	 * @param bool $isMandatory if true, fill this area is mandatory.
	 * @param string $name name of the form field
	 * @param string $description text that is displayed after the field
	 * @param string $initial_value text area initial value
	 * @param string $css_id CSS id for the input-group tag.
	 * @return string HTML code of the text area form field
	 * @access private
	 */
	private function _textarea(
		bool $isMandatory,
		string $name,
		string $description,
		string $initial_value = '',
		string $css_id = ''
	): string {
		$retval = '<div class="input-group"';
		if ( ! empty( $css_id ) ) {
			$retval .= ' id="' . $css_id . '"';
		}
		$retval .= '>';
		$retval .= '<div class="input-group-addon"></div>';
		$retval .= '<textarea rows="3" name="' . $name . '"';
		if ( $isMandatory ) {
			$retval .= ' required';
		}
		$retval .= '>' . $initial_value . '</textarea>';
		$retval .= '<div class="description">' . $description . ' ';
		if ( ! $isMandatory ) {
			$retval .= '(' . __( 'optional', 'mon-laboratoire' ) . ')';
		}
		$retval .= '</div></div>';
		return $retval;
	}

	/**
	 * Generate an HTML code for an URL input form field
	 * @param bool $isMandatory if true, fill this field is mandatory.
	 * @param string $legend text that is displayed just before the field
	 * @param string $name name of the form field
	 * @param string $description text that is displayed after the field
	 * @param string $initial_value initial value
	 * @param string $css_id CSS id for the <input> tag.
	 * @return string HTML code of form field
	 * @access private
	  */
	private function _url_field(
		bool $isMandatory,
		string $legend,
		string $name,
		string $description,
		string $initial_value = '',
		string $css_id = ''
	): string {
		return $this->_input_field(  $isMandatory, $legend, 'url', $name, $description, 'input-group-large', $css_id,
							$initial_value );
	}

	/**
	 * Generate an HTML code for a number input form field
	 * @param bool $isMandatory if true, fill this field is mandatory.
	 * @param string $legend text that is displayed just before the field
	 * @param string $name name of the form field
	 * @param string $description text that is displayed after the field
	 * @param string $initial_value initial value
	 * @param string $css_id CSS id for the <input> tag.
	 * @return string HTML code of form field
	 * @access private
	  */
	private function _number_field(
		bool $isMandatory,
		string $legend,
		string $name,
		string $description,
		string $initial_value = '',
		string $css_id = ''
	): string {
		return $this->_input_field(  $isMandatory, $legend, 'number', $name, $description, 'input-group-normal nopadding-input', $css_id,
						$initial_value );
	}

	/**
	 * Generate an HTML code for a color input form field
	 * @param bool $isMandatory if true, fill this field is mandatory.
	 * @param string $legend text that is displayed just before the field
	 * @param string $name name of the form field
	 * @param string $description text that is displayed after the field
	 * @param string $initial_value initial value
	 * @param string $css_id CSS id for the <input> tag.
	 * @return string HTML code of form field
	 * @access private
	  */
	private function _color_field(
		bool $isMandatory,
		string $legend,
		string $name,
		string $description,
		string $initial_value = '',
		string $css_id = ''
	): string {
		return $this->_input_field(  $isMandatory, $legend, 'text', $name, $description, "input-group-normal nopadding-input", $css_id,
						$initial_value, 'my-color-field', "data-default-color='#effeff'" );
	}

	/**
	 * Generate an HTML code for the choice of a square image
	 * @param string $name name of the form
	 * @param string $description comment displayed just above the form
	 * @param string $initial_value Initial value of the form
	 * @return string HTML code
	 * @access private
	 */
	private function _square_image_selector(
		string $name,
		string $description,
		string $initial_value
	): string {
		$retval = '<div class="input-group"><label for="' . $name . '">' . $description . '&nbsp;: </label>';
		$retval .= '<input id="' . $name . '_no_logo_button" class="button" value="' . _x( 'None', 'logo', 'mon-laboratoire' ) . '"';
		$retval .= ' onclick="selectNoPicture(\'' . $name . '-image-preview\', \'' . $name . '\');" type="button" />';
		$retval .= '&nbsp;<input id="' . $name . '-upload-image-button" type="button" class="button" value="'. __( 'Chose media', 'mon-laboratoire' ) . '"';
		$retval .= ' onclick="imageMediaMenu('
			. '\'' . __( 'Choose a picture', 'mon-laboratoire' ) . '\','
			. '\'' . __( 'use this picture', 'mon-laboratoire' ) . '\','
			. '\'' . $name . '-image-preview\','
			. '\'' . $name . '\''
			. ');" />';
		$retval .= "<input type='hidden' name='" . $name . "' id='" . $name . "' value='" . $initial_value . "' />&nbsp;";
		$url = $this->_html->image_from_id_or_url( $initial_value );
		$supl_class = '';
		if ( empty( $initial_value ) ) {
			$supl_class = ' hidden';
			$url = '#';
		}
		$retval .= "<a href='#' class='hover-zoom-square30'>";
		$retval .= "<img width='30' height='30' id='" . $name . "-image-preview' class='wp-image-8 wp-post-image" . $supl_class . "' src='".  $url ."' alt='" . $initial_value . "'>";
		$retval .= '</a></div>';
		return $retval;
	}

	/**
	 * Generate an HTML code for the choice of an image for a person
	 * @param string $name name of the form
	 * @param string $description comment displayed just above the form
	 * @param string $initial_value Initial value of the form
	 * @return string HTML code
	 * @access private
	 */
	private function _person_image_selector(
		string $name,
		string $description,
		string $initial_value
	): string {
		$options2 = get_option( 'MonLabo_settings_group2' );
		$default_image = $this->_html->image_from_id_or_url( $options2['MonLabo_img_par_defaut'] );
		$retval = '<div class="input-group"><label for="' . $name . '">' . $description . '&nbsp;: </label>';
		$retval .= '<input id="' . $name . '_no_logo_button" class="button" value="' . _x( 'None', 'image', 'mon-laboratoire' ) . '"';
		$retval .= ' onclick="selectDefaultPicture(\'' . $name . '-image-preview\', \'' . $name . '\', \''. $default_image .'\');" type="button" />';
		$retval .= '&nbsp;<input id="' . $name . '-upload-image-button" type="button" class="button" value="'. __( 'Chose media', 'mon-laboratoire' ) . '"';
		$retval .= ' onclick="imageMediaMenu('
			. '\'' . __( 'Choose a picture', 'mon-laboratoire' ) . '\','
			. '\'' . __( 'use this picture', 'mon-laboratoire' ) . '\','
			. '\'' . $name . '-image-preview\','
			. '\'' . $name . '\''
			. ');" />';
		$retval .= "<input type='hidden' name='" . $name . "' id='" . $name . "' value='" . $initial_value . "' />&nbsp;";
		$url = $this->_html->image_from_id_or_url( $initial_value );
		if ( empty( $initial_value ) || ('DEFAULT' === $initial_value) ) {
			$url = $default_image;
		}
		$retval .= "<a href='#' class='hover-zoom-square60'>";
		$retval .= "<img width='60' height='60' id='" . $name . "-image-preview' class='wp-post-image img-arrondi' src='".  $url ."' alt='" . $initial_value . "'>";
		$retval .= '</a></div>';
		return $retval;
	}


	/**
	 * Generate an HTML code for a generic input form field
	 * @param bool $isMandatory if true, fill this field is mandatory.
	 * @param string $legend text that is displayed just before the field
	 * @param string $input_type type of the form field (text, url...)
	 * @param string $name name of the form field
	 * @param string $description text that is displayed after the field
	 * @param string $imput_group_class CSS class of the input-group <div> tag
	 * @param string $imput_id CSS id for the <input> tag.
	 * @param string $initial_value text initial value
	 * @param string $input_class additional CSS class for the <input> tag.
	 * @param string $input_custom additional custom things for the <input> tag.
	 * @return string HTML code of form field
	 * @access private
	  */
	private function _input_field(
		bool $isMandatory,
		string $legend,
		string $input_type,
		string $name,
		string $description,
		string $imput_group_class = '',
		string $imput_id = '',
		string $initial_value = '',
		string $input_class = '',
		string $input_custom = ''
	): string {
		$to_display = '<div class="input-group ' . $imput_group_class . '">';
		$to_display .= '<div class="input-group-addon"></div>';
		if ( ! empty( $input_class ) ) {
			$input_class = ' ' . $input_class;
		}
		$to_display .= '<input type="' . $input_type . '" class="form-control' . $input_class . '" name="' . $name . '" placeholder="' . $legend;
		if ( ! $isMandatory ) {
			$to_display .= ' (' . __( 'optional', 'mon-laboratoire' ) . ')';
		}
		$to_display .= '"';
		if ( ! empty( $imput_id ) ) {
			$to_display .= ' id="' . $imput_id . '"';
		}
		if ( strlen( $initial_value ) > 0 ) {
			$to_display .= ' value="'. $initial_value . '"';
		}
		if ( $isMandatory ) {
			$to_display .= ' required';
		}
		if ( ! empty( $input_custom ) ) {
			$to_display .= ' ' . $input_custom;
		}
		$to_display .= ' /><div class="description"> ' . $description . ' </div></div>';
		return $to_display;
	}


}
?>
