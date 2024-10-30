<?php
namespace MonLabo\Admin;
use MonLabo\Lib\{App, Translate, Polylang_Interface};
use MonLabo\Frontend\{Html, Contact_Webservices};

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

///////////////////////////////////////////////////////////////////////////////////////////
//					   PLUGIN ADMIN RENDER CLASS DEFINITIONS
///////////////////////////////////////////////////////////////////////////////////////////
/*
class Settings_Fields  {
	__construct( )
	add()
	select_generic_render( array $args )
	select_with_search_generic_render( array $args )
	radio_generic_render( array $args )
	hidden_generic_render( array $args )
	text_field_generic_render( array $args )
	four_text_fields_generic_render( array $args )
	two_text_fields_generic_render( array $args )
	_generate_array_options_field( array $array_options, string $settings_group, bool $disable )
	img_field_generic_render( array $args )
	color_picker_generic_render( array $args )
	text_area_generic_render( array $args )
	button_clear_cache_generic_render( array $args )
	button_create_default_parent_page_generic_render( array $args )
	checkbox_generic_render( array $args )
	checkbox2_generic_render( array $args )
	number_generic_render( array $args )
	select_page_generic_render( array $args )
}
*/
/**
 * Class \MonLabo\Admin\Settings_Fields
 * @package
 */

class Settings_Fields  {

	/**
	* Cache of options
	* @access private
	* @var array<string, string[]>
	*/
	private $_options = array();

	/**
	 * Create a new Settings_Fields  class
	 */
	function  __construct( ) {
		foreach ( array_keys( App::get_options_DEFAULT() ) as $group ) {
			$this->_options[ $group ] = get_option( $group );
		}
	}


	/**
	* Display a <SELECT> option list form field
	* Called with "add_field( 'select' ... "
	* @param string $type Type of the field
	* @param string $title Text before the form field
	* @param string $page Current section of a  menu
	* @param string $section Current page of a  menu
	* @param array{option_name:string,description:string,settings_group:string,disable:bool} $args Parameters :
	*		- $args['settings_group'] : Group of option to update
	*		- $args['option_name'] : Name of option to update
	*		- $args['description'] : Description to display
	*		- $args['disable'] : true if disabled form
	* @return Settings_Fields
	*/
	function add( string $type, string $title, string $page, string $section, array $args ): self {
		$list_of_type = array( 'radio', 'hidden', 'text_field', 'four_text_fields',
			'two_text_fields', 'img_field', 'color_picker', 'text_area', 'button_clear_cache',
			'button_create_default_parent_page',
			'checkbox2', 'number', 'select', 'select_with_search', 'select_page', 'hidden_order' );
		if (   isset( $args['settings_group']	)
			and isset( $args['option_name']   	)
			and isset( $args['description']		)
			and isset( $args['disable']		    )
			and in_array( $type, $list_of_type 	)
		) {
			add_settings_field(
				$args['option_name'],
				$title,
				array( &$this, $type . '_generic_render' ),
				$page,
				$section,
				$args
			);
		}
		return $this;
	}


	/**
	* Display a <SELECT> option list form field
	* Called with "add_field( 'select' ... "
	* @param array{option_name:string,description:string,settings_group:string,disable:bool,on_change:string} $args Parameters :
	*		- $args['settings_group'] : Group of option to update
	*		- $args['option_name'] : Name of option to update
	*		- $args['disable'] : true if disabled form
	*		- $args['on_change'] : js to launch if any change
	* @return Settings_Fields
	*/
	function select_generic_render( array $args ): self {  // @phan-suppress-current-line PhanUnreferencedPublicMethod
		if ( $args['disable'] ) {
			$this->hidden_generic_render( $args );
		} else {
			$onchange = '';
			switch ( $args['option_name'] ) {
				case 'MonLabo_hal_publi_style':
					$items =  array( 'apa' => 'apa', 'hal' => 'hal', 'ieee' => 'ieee' );
					break;
				case 'MonLabo_DescartesPubmed_format':
					$items =  array( 'html_default'=>'html_default', 'html_hal'=>'html_hal' );
					break;
				case 'MonLabo_multisite_db_to_use':
					$items = array( 
						'___no_change___' =>  __( '--- No change ---', 'mon-laboratoire' ),
						'___manual_edit___' =>  __( '--- Manual edit (use field "Prefix" below) ---', 'mon-laboratoire' ),
					);
					if ( is_multisite()
						&& function_exists( 'get_sites' ) 
						&& class_exists( 'WP_Site_Query' ) //WP version >= 4.6
					) {
						//get_main_site_id
						$sites = get_sites();
						if ( !empty( $sites ) ) {
							$actual_site = get_site();
							foreach ( $sites as $site ) {
								$suffix = '';
								if ( $site->blog_id === $actual_site->blog_id ) {
									$suffix = ' — '. __( 'Current site', 'mon-laboratoire' );
								}
								global $wpdb;
								$prefix = $wpdb->get_blog_prefix( $site->blog_id );
								$items[$prefix] = $prefix . ' : ' . $site->__get('blogname') . ' (' . $site->__get('siteurl') . ')' . $suffix;
							}
						}
					}
					break;					
				default:
					return $this; /* error */
			}
			if ( !empty( $args['on_change'] ) ) {
				$onchange = ' onchange="' . $args['on_change'] .'"';
			}
			echo "<select name='" . $args['settings_group'] . '[' . $args['option_name'] . "]'". $onchange . ">";
			foreach ( $items as $key => $item ) {
				$selected = ( 0 === strcmp( $this->_options[ $args['settings_group'] ][ $args['option_name'] ], $key ) ) ? 'selected="selected"' : '';
				echo "<option value='$key' $selected>$item</option>";
			}
			echo '</select>';
		}
		return $this;		
	}

	/**
	* Display a <SELECT> option list form field
	* Called with "add_field( 'select' ... "
	* @param array{option_name:string,description:string,settings_group:string,disable:bool} $args Parameters :
	*		- $args['settings_group'] : Group of option to update
	*		- $args['option_name'] : Name of option to update
	*		- $args['disable'] : true if disabled form
	* @return Settings_Fields
	*/
	function select_with_search_generic_render( array $args ): self {  // @phan-suppress-current-line PhanUnreferencedPublicMethod
		if ( $args['disable'] ) {
			$this->hidden_generic_render( $args );
		} else {
			switch ( $args['option_name'] ) {
				case 'MonLabo_hal_publi_style':
					$items =  array( 'apa', 'hal', 'ieee' );
					break;
				case 'MonLabo_DescartesPubmed_format':
					$items =  array( 'html_default'=>'html_default', 'html_hal'=>'html_hal' );
					break;
				default:
					return $this; /* error */
			}
			echo "<select name='" . $args['settings_group'] . '[' . $args['option_name'] . "]'>";
			foreach ( $items as $item ) {
				$selected = ( 0 === strcmp( $this->_options[ $args['settings_group'] ][ $args['option_name'] ], $item ) ) ? 'selected="selected"' : '';
				echo "<option value='$item' $selected>$item</option>";
			}
			echo '</select>';
		}
		return $this;		
	}

	/**
	* Display a radio button form field
	* Called with "add_field( 'radio' ... "
	* @param array{option_name:string,description:string,settings_group:string,disable:bool} $args Parameters :
	*		- $args['settings_group'] : Group of option to update
	*		- $args['option_name'] : Name of option to update
	*		- $args['description'] : Description to display
	*		- $args['disable'] : true if disabled form
	* @return Settings_Fields
	*/
	function radio_generic_render( array $args ): self { // @phan-suppress-current-line PhanUnreferencedPublicMethod
		if ( $args['disable'] ) {
			$this->hidden_generic_render( $args );
		} else {
			$translate = new Translate();
			$html = new Html();
			$Polylang_Interface = new Polylang_Interface();
			switch ( $args['option_name'] ) {
				case 'MonLabo_publication_server_type':
					$items = array( 'hal'		   	=> "<a href='https://hal.science/'><img width='61' height='30' class='wp-image-8 alignleft wp-post-image' src='" . plugins_url( 'images/logoHAL.png', __FILE__ ) . "' alt='logo HAL' /></a>&nbsp;". __( 'HAL (Public API open to all)', 'mon-laboratoire' ),
									'DescartesPubli'=> "<img width='61' height='34' class='wp-image-8 alignleft wp-post-image' src='" . plugins_url( 'images/DescartesPubli.logo.png', __FILE__ ) . "' alt='logo Descartes publi' />&nbsp;". __( 'Descartes Publi (API reserved for the University of ParisDescartes)', 'mon-laboratoire' ),
									'both' 			=> __( 'Both! (HAL is used by default if the option <i>base</i> is not specified in <i>[publication_list]</i>)', 'mon-laboratoire' ),
									'aucun' 		=> _x( 'None', 'option-langage', 'mon-laboratoire' ) );
					break;
				case 'MonLabo_language_config':
					$items = array( 'en'		=> __( 'English', 'mon-laboratoire' ) . ' ' . $html->get_translation_flag( 'en' ),
									'fr'		=> __( 'French', 'mon-laboratoire' ). ' ' . $html->get_translation_flag( 'fr' ),
									'browser' 	=> __( 'Visitor\'s browser language', 'mon-laboratoire' ) . ' <small>(' . __( 'here', 'mon-laboratoire' ) . ' : <em>' .$translate->get_browser_language() . '</em>)</small>',
									'Polylang' 	=> __( 'Multilingual, using the Polylang translation plugin', 'mon-laboratoire' ) . ' <small>(' . __( 'Polylang plugin status', 'mon-laboratoire' ). ' : <em>' . $Polylang_Interface->get_polylang_plugin_status( "translated" ) . '</em>)</small>' ,
									'WordPress' =>
										sprintf(
											__( 'Language configured %1$s in WordPress %2$s or by a translation plugin' , 'mon-laboratoire' )
											, '<a href="' . admin_url( 'options-general.php' ) . '">'
											, '</a>'
										)
										. ' <small>(' . __( 'currently', 'mon-laboratoire' ) . ' : <em>'
										. ( 'activated' === $Polylang_Interface->get_polylang_plugin_status()
												? __( 'defined by Polylang', 'mon-laboratoire' )
												: get_locale()
											)
										. '</em>)</small>',
									);
					break;
					default:
					return $this; /* Error */
			}
			echo( '<fieldset>');
			if (! empty( trim( $args['description'])) ){
				echo( '<legend>' . $args['description'] . '</legend>' );
			}
			foreach ( $items as $itemKey=>$item ) {
				$checked = ( 0 === strcmp( $this->_options[ $args['settings_group'] ][ $args['option_name'] ], $itemKey ) ) ? ' checked' : '';
				$input_id = uniqid();
				echo( '<div>'
					.'<input type="radio"'
						.' id="' . $input_id . '"'
						.' name="' . $args['settings_group'] . "[" . $args['option_name'] . "]" . '"'
						.' value="' . $itemKey . '"'
						. $checked . ' />'
					. '<label for="' . $input_id . '">' . $item . '</label>'
				.'</div>' );

			}
			echo '</fieldset>';
		}
		return $this;
	}

	/**
	* Display a hidden form field
	* Called with "add_field( 'hidden' ... "
	* @param array{option_name:string,description:string,settings_group:string,disable:bool} $args Parameters :
	*		- $args['settings_group'] : Group of option to update
	*		- $args['option_name'] : Name of option to update
	* @return Settings_Fields
	*/
	function hidden_generic_render( array $args ): self {  // @phan-suppress-current-line PhanUnreferencedPublicMethod
		echo( "<input type='hidden' name='" . $args['settings_group'] . '[' . $args['option_name'] . "]' value='" . $this->_options[ $args['settings_group'] ][ $args['option_name'] ] . "' />" );
		return $this;
	}

	/**
	* Display a text form field
	* Called with "add_field( 'text' ... "
	* @param array{option_name:string,description:string,settings_group:string,disable:bool,on_change:string} $args Parameters :
	*		- $args['settings_group'] : Group of option to update
	*		- $args['option_name'] : Name of option to update
	*		- $args['description'] : Description to display
	*		- $args['disable'] : true if disabled form
	*		- $args['on_change'] : js to launch if any change
	* @return Settings_Fields
	*/
	function text_field_generic_render( array $args ): self {  // @phan-suppress-current-line PhanUnreferencedPublicMethod
		$onchange = '';
		if ( !empty( $args['on_change'] ) ) {
			$onchange = ' onchange="' . $args['on_change'] .'"';
		}
		$type = ( $args['disable'] ? 'hidden' : 'text' );
		echo( "<input type='" . $type . "' name='" . $args['settings_group'] . '[' . $args['option_name'] . "]' value='" . $this->_options[ $args['settings_group'] ][ $args['option_name'] ] . "' " . $onchange . "/>" );
		echo( ' ' . $args['description'] );
		return $this;
	}

	/**
	* Display a quadruple text form field for translation in fr/en and singular/plural
	* Called with "add_field( 'four_text_fields' ... "
	* @param array{option_name:string,description:string,settings_group:string,disable:bool} $args Parameters :
	*		- $args['settings_group'] : Group of option to update
	*		- $args['option_name'] : Name of option to update
	*		- $args['disable'] : true if disabled form
	* @return Settings_Fields
	*/
	function four_text_fields_generic_render( array $args ): self {  // @phan-suppress-current-line PhanUnreferencedPublicMethod
		$html = new Html();
		$array_options = array(
			$args['option_name'] . '_en' =>  $html->get_translation_flag( 'en' ) . ' ' . __( 'English singular', 'mon-laboratoire' ),
			$args['option_name'] . '_fr' =>  $html->get_translation_flag( 'fr' ) . ' ' . __( 'French singular', 'mon-laboratoire' ),
			$args['option_name'] . 's_en' => $html->get_translation_flag( 'en' ) . ' ' . __( 'English plural', 'mon-laboratoire' ),
			$args['option_name'] . 's_fr' => $html->get_translation_flag( 'fr' ) . ' ' . __( 'French plural', 'mon-laboratoire' ),
		);
		echo( $this->_generate_array_options_field( $array_options, $args['settings_group'],  $args['disable'] ) );
		return $this;
	}

	/**
	* Display a double text form field for translation in fr/en
	* Called with "add_field( 'two_text_fields' ... "
	* @param array<string,string|bool> $args Parameters :
	*		- $args['settings_group'] : Group of option to update
	*		- $args['option_name'] : Name of option to update
	*		- $args['disable'] : true if disabled form
	* @return Settings_Fields
	*/
	function two_text_fields_generic_render( array $args ): self {  // @phan-suppress-current-line PhanUnreferencedPublicMethod
		$html = new Html();
		$array_options = array(
			$args['option_name'] . '_en' =>   $html->get_translation_flag( 'en' ) . ' ' . __( 'English', 'mon-laboratoire' ),
			$args['option_name'] . '_fr' =>   $html->get_translation_flag( 'fr' ) . ' ' . __( 'French', 'mon-laboratoire' ),
		);
		echo( $this->_generate_array_options_field( $array_options, $args['settings_group'],  $args['disable'] ) );
		return $this;
	}

	/**
	* Generate a double text form field for translation in fr/en
	* @param string[] $array_options List of each text options
	* @param string $settings_group Group of option to update
	* @param bool $disable If true, do not display this form field
	* @return string HTML code of form fields
	* @access private
	*/
	private function _generate_array_options_field( array $array_options, string $settings_group,  bool $disable = false ): string {
		$options_DEFAULT = App::get_options_DEFAULT();
		$type = ( $disable ? 'hidden' : 'text' );
		$out = '';
		if ( $array_options ){
			$out .= ( $disable ? '' : '<table class="MonLabo-group-form" role="presentation"><thead><tr>' );
			$out2 = ( $disable ? '' : '</tr></thead><tbody><tr>' );
			foreach ( $array_options as $option_name => $description ) {
				if ( ! $disable ) {
					$out .= "<th scope='row'>" . $description . '</th>';
					$out2 .= '<td>';
				}
				$out2 .= "<input type='" . $type . "' name='" . $settings_group . '[' . $option_name . "]' value='" . $this->_options[ $settings_group ][ $option_name ] . "' />";
				if ( ( ! $disable ) and ( ! empty( $options_DEFAULT[ $settings_group ][ $option_name ] ) ) ) {
					$out2 .= 'Ex: <em>' . $options_DEFAULT[ $settings_group ][ $option_name ] . '</em>';
				}
				$out2 .= ( $disable ? '' : '</td>' );
			}
			$out .= $out2 . ( $disable ? '' : '</tr></tbody></table>' ) ;
		}
		return $out;
	}

	/**
	* Display an image choice form field
	* Called with "add_field( 'img_field' ... "
	* @param array{option_name:string,description:string,settings_group:string,disable:bool} $args Parameters :
	*		- $args['settings_group'] : Group of option to update
	*		- $args['option_name'] : Name of option to update
	*		- $args['disable'] : true if disabled form
	* @return Settings_Fields
	*/
	function img_field_generic_render( array $args ): self {  // @phan-suppress-current-line PhanUnreferencedPublicMethod
		if ( ! $args['disable'] ) {
			$html = new Html();
			echo( "<div class='image-preview-wrapper'>" );
			echo( "<div class='MonLabo-persons-list'><ul><li><a href='#'>"
				. "<img width='60' height='60' id='image-preview' class='wp-image-8 alignleft img-arrondi wp-post-image' src='"
				. $html->image_from_id_or_url( $this->_options[ $args['settings_group'] ][ $args['option_name'] ] )
				. "' alt='" . $this->_options[ $args['settings_group'] ][ $args['option_name'] ]
				. "' />"
				. "</a>" );
			echo( '</li></ul></div>' );
			echo( '</div>' );
			echo( '<input type="button" class="upload-image-button button" value="'. __( 'Chose media', 'mon-laboratoire' ) . '"'
				. ' onclick="imageMediaMenu('
				. "'" . __( 'Choose a picture', 'mon-laboratoire' ) . "',"
				. "'" . __( 'use this picture', 'mon-laboratoire' ) . "',"
				. "'image-preview','image_attachment_id'"
				. ');" />');
		}
		echo( "<input type='hidden' name='" . $args['settings_group'] . '[' . $args['option_name'] . "]' id='image_attachment_id' value='" . $this->_options[ $args['settings_group'] ][ $args['option_name'] ] . "' />" );
		return $this;
	}

	/**
	* Display a color picker form field
	* Called with "add_field( 'color_picker' ... "
	* @param array{option_name:string,description:string,settings_group:string,disable:bool} $args Parameters :
	*		- $args['settings_group'] : Group of option to update
	*		- $args['option_name'] : Name of option to update
	*		- $args['description'] : Description to display
	*		- $args['disable'] : true if disabled form
	* @return Settings_Fields
	*/
	function color_picker_generic_render( array $args ): self {  // @phan-suppress-current-line PhanUnreferencedPublicMethod
		if ( $args['disable'] ) {
			$this->hidden_generic_render( $args );
		} else {
			echo( "<input type='text' name='" . $args['settings_group'] . '[' . $args['option_name'] . "]' value='" . $this->_options[ $args['settings_group'] ][ $args['option_name'] ] . "' class='my-color-field' data-default-color='#effeff' />" );
			echo( "<div style='float:left; height:30px;width:30px; background:" . $this->_options[ $args['settings_group'] ][ $args['option_name'] ] . ";border-radius:15px;box-shadow: 3px 3px 3px 0px #000;'></div><div style='float:left; margin:6px;'><small style='color:#777;'>" . $this->_options[ $args['settings_group'] ][ $args['option_name'] ] . '</small></div>' );
			echo( ' ' . $args['description'] );
		}
		return $this;
	}

	/**
	* Display a text area form field
	* Called with "add_field( 'text_area' ... "
	* @param array{option_name:string,description:string,settings_group:string,disable:bool} $args Parameters :
	*		- $args['settings_group'] : Group of option to update
	*		- $args['option_name'] : Name of option to update
	*		- $args['description'] : Description to display
	*		- $args['disable'] : true if disabled form
	* @return Settings_Fields
	*/
	function text_area_generic_render( array $args ): self {  // @phan-suppress-current-line PhanUnreferencedPublicMethod
		if ( $args['disable'] ) {
			$this->hidden_generic_render( $args );
		} else {
			echo( "<textarea name='" . $args['settings_group'] . '[' . $args['option_name'] . "]' rows='4' cols='50'>" . $this->_options[ $args['settings_group'] ][ $args['option_name'] ] . '</textarea>' );
			if( !empty($args['description']) ) {
				echo( ' <pre>' . $args['description'] . '</pre>' );
			}
		}
		return $this;
	}

	/**
	* Display a button for empty cache
	* Called with "add_field( 'text_area' ... "
	* @param array{option_name:string,description:string,settings_group:string,disable:bool} $args Parameters :
	*		- $args['settings_group'] : Group of option to update
	*		- $args['option_name'] : Name of option to update
	*		- $args['description'] : Description to display
	*		- $args['disable'] : true if disabled form
	* @return Settings_Fields
	*/
	function button_clear_cache_generic_render( array $args ): self { // @phan-suppress-current-line PhanUnreferencedPublicMethod
		if ( $args['disable'] ) {
			$this->hidden_generic_render( $args );
		} else {
			$webservice = new Contact_Webservices();
			$options = get_option( $args['settings_group'] );
			if ( ! empty( $options[ $args['option_name'] ] ) ) {
				$webservice->clear_transients();
				echo( '<strong>'. __( 'Cache emptied!', 'mon-laboratoire' ) . ' </strong>' );
				unset( $options[ $args['option_name'] ] );
				update_option( $args['settings_group'] , $options );
			}
			$hide_class = '';
			if ( 0 === $webservice->number_of_transient_entries() ) {
				$hide_class = 'MonLabo_hide';
			}
			$html_forms = new Html_Forms();
			echo($html_forms->submit_button( 
				__( 'Clear publications cache', 'mon-laboratoire' ), $args['option_name'] . '_button', 
				'fillTriggerAndSubmit(\'' . $args['option_name'] . '_trigger\')', 'trash', 'danger', 'button_' . $args['settings_group'] . '[' . $args['option_name'] . ']', $hide_class
			));
			echo( ' ' . $webservice->number_of_transient_entries() . ' ' . $args['description'] );
			echo( "<input type='hidden' id='" . $args['option_name'] . "_trigger' class='' name='" . $args['settings_group'] . '[' . $args['option_name'] . "]' >" );
		}
		return $this;
	}

	/**
	* Display a button for create default parent page
	* Called with "add_field( 'text_area' ... "
	* @param array{option_name:string,description:string,settings_group:string,disable:bool} $args Parameters :
	*		- $args['settings_group'] : Group of option to update
	*		- $args['option_name'] : Name of option to update
	*		- $args['description'] : Description to display
	*		- $args['disable'] : true if disabled form
	* @return Settings_Fields
	*/
	function button_create_default_parent_page_generic_render( array $args ): self { // @phan-suppress-current-line PhanUnreferencedPublicMethod
		if ( $args['disable'] ) {
			$this->hidden_generic_render( $args );
		} else {
			$options = get_option( $args['settings_group'] );

			if ( ! empty( $options[ $args['option_name'] ] ) ) {
				switch ( $args['option_name'] ) {
					case 'MonLabo_do_create_perso_page_parent':
						$page = new Page( 'person_parent' );
						$options['MonLabo_perso_page_parent'] = $page->wp_post_id;
						break;
					case 'MonLabo_do_create_team_page_parent':
						$page = new Page( 'team_parent' );
						$options['MonLabo_team_page_parent'] = $page->wp_post_id;
						break;
					case 'MonLabo_do_create_thematic_page_parent':
						$page = new Page( 'thematic_parent' );
						$options['MonLabo_thematic_page_parent'] = $page->wp_post_id;
						break;
					case 'MonLabo_do_create_unit_page_parent':
						$page = new Page( 'unit_parent' );
						$options['MonLabo_unit_page_parent'] = $page->wp_post_id;
						break;															
				}
				echo( '<strong>'. __( 'Page created!' , 'mon-laboratoire' ) . ' </strong>' );
				$options[ $args['option_name'] ] = '';
				update_option( $args['settings_group'] , $options );
				echo('<script type="text/javascript">'
					.'setTimeout(function() {'
					.	'window.location.reload(1); ' // Force reload page
					. '}, 500);'
				  	. '</script>');
			}
			$html_forms = new Html_Forms();
			echo($html_forms->submit_button( 
				$args['description'], 'submit_' . $args['option_name'], 
				'fillTriggerAndSubmit(\'' . $args['option_name'] . '_trigger\')',
				'', 'primary',  'button_' . $args['settings_group'] . '[' . $args['option_name'] . ']', '', 
				$args['disable']
			) );
			echo( "<input type='hidden' id='" . $args['option_name'] . "_trigger' class='' name='" . $args['settings_group'] . '[' . $args['option_name'] . "]' >" );
		}
		return $this;
	}

	/**
	* Display a checkbox form field
	* Called with "add_field( 'checkbox2' ... "
	* @param array{option_name:string,description:string,settings_group:string,disable:bool} $args Parameters :
	*		- $args['settings_group'] : Group of option to update
	*		- $args['option_name'] : Name of option to update
	*		- $args['description'] : Description to display
	*		- $args['disable'] : true if disabled form
	* @return Settings_Fields
	*/
	function checkbox2_generic_render( array $args ): self {  // @phan-suppress-current-line PhanUnreferencedPublicMethod
		if ( ! $args['disable'] ) {
			echo( '<div class="checkbox2">' );
			$input_id=uniqid();
			// checked() is a wp function  https://codex.wordpress.org/Function_Reference/checked
			echo( "<input type='checkbox' id='" . $input_id . "' name='" . $args['settings_group'] . '[' . $args['option_name'] . "]'" );
			if ( isset( $this->_options[ $args['settings_group'] ][ $args['option_name'] ] ) ) {
				echo( " " . checked( $this->_options[ $args['settings_group'] ][ $args['option_name'] ], '1' , false ) );
			}
			echo( " value='1'>" );
			echo( '<label for="' . $input_id . '"><span class="ui">&nbsp;</span></label>' );
			echo( '<p class="description_checkbox2">' );
			echo( $args['description'] );
			echo( '</p></div>' );
		}
		return $this;
	}
	
	/**
	* Display a hidden order to execute
	* Called with "add_field( 'hidden_order' ... "
	* @param array{option_name:string,description:string,settings_group:string,disable:bool} $args Parameters :
	*		- $args['settings_group'] : Group of option to update
	*		- $args['option_name'] : Name of option to update
	*		- $args['disable'] : true if disabled form
	* @return Settings_Fields
	*/
	function hidden_order_generic_render( array $args ): self {  // @phan-suppress-current-line PhanUnreferencedPublicMethod
		if ( $args['disable'] ) {
			$this->hidden_generic_render( $args );
		} else {
			/*
			---- No more necessary, cash erase has been moved to a specialized page ----
			if ( isset( $this->_options[ $args['settings_group'] ][ $args['option_name'] ] )
				&&  ( !empty( $this->_options[ $args['settings_group'] ][ $args['option_name'] ] ) )
			) {
				if ( 'MonLabo_ask_erase_cache_after_cfg' === $args['option_name'] ) {
					$webservice = new Contact_Webservices();
					$webservice->clear_transients();
					unset( $this->_options[ $args['settings_group'] ][ $args['option_name'] ] );
					update_option( $args['settings_group'], $this->_options[ $args['settings_group'] ]  );
				}
			}*/
			echo( "<input type='hidden' name='" . $args['settings_group'] . '[' . $args['option_name'] . "]' value='1' />" );
		}
		return $this;
	}

	/**
	* Display a number form field
	* Called with "add_field( 'number' ... "
	* @param array{option_name:string,description:string,settings_group:string,disable:bool} $args Parameters :
	*		- $args['settings_group'] : Group of option to update
	*		- $args['option_name'] : Name of option to update
	*		- $args['description'] : Description to display
	*		- $args['disable'] : true if disabled form
	* @return Settings_Fields
	*/
	function number_generic_render( array $args ): self {  // @phan-suppress-current-line PhanUnreferencedPublicMethod
		if ( $args['disable'] ) {
			$this->hidden_generic_render( $args );
		} else {		
			echo( "<input type='number' name='" . $args['settings_group'] . '[' . $args['option_name'] . "]' value='" . $this->_options[ $args['settings_group'] ][ $args['option_name'] ] . "' />" );
			echo( ' ' . $args['description'] );
		}
		return $this;
	}

	/**
	* Display a page choice form field
	* Called with "add_field( 'page' ... "
	* @param array{option_name:string,description:string,settings_group:string,disable:bool} $args Parameters :
	*		- $args['settings_group'] : Group of option to update
	*		- $args['option_name'] : Name of option to update
	*		- $args['description'] : Description to display
	*		- $args['disable'] : true if disabled form
	* @return Settings_Fields
	*/
	function select_page_generic_render( array $args ): self {  // @phan-suppress-current-line PhanUnreferencedPublicMethod
		if ( $args['disable'] ) {
			$this->hidden_generic_render( $args );
		} else {

			//Convert Option name to number in order to be used by multi-pages functions
			$name_to_nb = array ( 'MonLabo_perso_page_parent' => 1, 'MonLabo_team_page_parent' => 2, 'MonLabo_thematic_page_parent' => 3, 'MonLabo_unit_page_parent' => 4 );
			if ( !array_key_exists( $args['option_name'], $name_to_nb ) ) {
				return $this;
			}
			$nbpage = $name_to_nb[ $args['option_name'] ] ;
			
			//Initial values
			$retval = '';
			$name = $args['settings_group'] . '[' . $args['option_name'] . ']';
			$initial_value = $this->_options[ $args['settings_group'] ][ $args['option_name'] ];

			$pages_published  = get_pages( );
			if ( ! empty( $pages_published ) ) {
				$retval .= "<select name='" . esc_attr( $name ) . "' id='" . esc_attr(  $name ) . "'>\n";
				$retval .= "\t<option value=\"" . esc_attr( App::NO_PAGE_OPTION_VALUE ) . '"'
					. ( App::NO_PAGE_OPTION_VALUE === $initial_value ? " selected" : '') . ">"
					. '&mdash; ' . __( 'No page', 'mon-laboratoire' ) . ' &mdash; ' . "</option>\n";
				$retval .= walk_page_dropdown_tree( 
					$pages_published, 
					1, //depth
					array( 'value_field' => 'ID', 'selected' => $initial_value ) 
				);

				$retval .= "</select>";
				$retval .= '<div id="delayedLoadDivThumbnail_' . strval( $nbpage ) . '" class="delayedLoadDivThumbnail"><!-- Nous allons afficher ici la suite en asynchone grâce à ajax. --></div>' . "\n";
			}	

			echo $retval;
		}
		return $this;
	}

}

?>
