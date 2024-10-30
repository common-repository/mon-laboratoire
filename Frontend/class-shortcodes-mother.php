<?php
namespace MonLabo\Frontend;

use MonLabo\Lib\{Polylang_Interface, Lib};
//Next classes are used but not explicitely => need to suppress unnecessary error message PhanUnreferencedUseNormal.
use MonLabo\Lib\Person_Or_Structure\{Person, Team, Unit}; //@phan-suppress-current-line PhanUnreferencedUseNormal

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );
/* Class \MonLabo\Frontend\Shortcodes_Mother {
	getInstance()
	_item_of_curent_page( string $className )
	_init_args( $arg_structure, $atts, string $shortcode_name )
	_team_of_curent_page()
	_unit_of_curent_page()
	_person_of_curent_page()
	_sanitize_parameters( array $args )
	_prepare_multiple_values_variable_into_array( $inputs )

*/

/**
 * Class \MonLabo\Frontend\Shortcodes_Mother
 * Pour être utilisée dans les classes \MonLabo\Frontend\Shortcodes et \MonLabo\Frontend\Shortcodes_Publications
 * @package
 */
abstract class Shortcodes_Mother {

	/**
	  * Generic function to get person, team or unit current page
	  * @param string $className name of class (Person, Team, Unit)
	  * method to get person_id, team_id or unit_id from wp_post_id
	  * @return int id of the person, team or unit. 0 string if no answer.
	  * @access private
	  */
	  private function _item_of_curent_page( string $className ): int {
		$wp_post_id = get_the_ID();
		$className = '\\MonLabo\\Lib\Person_Or_Structure\\' . $className;
		if ( false != $wp_post_id ) {
			$item = new $className( 'from_wpPostId', $wp_post_id );
			if ( $item->is_empty() ) {
				//vérifier si une page traduite n’est pas dans la liste des pages
				$Polylang_Interface = new Polylang_Interface();
				$item = new $className( 'from_wpPostId', $Polylang_Interface->get_translated_post_if_exists( $wp_post_id ) );
			}
			if ( ! $item->is_empty() ) {
				return $item->info->id;
			}
		}

		//Ici on doit refaire un test avec l’url de la page
		$url= Lib::get_current_url_without_queries();
		$item = new $className( 'from_wpPostId', $url );
		if ( ! $item->is_empty() ) {
			return $item->info->id;
		}
		return 0;
	}

	/**
	 * Initialize shortcodes arguments
	 * @param array<string, string|int> $arg_structure Definition of parameters and eventual default values
 	 * @param mixed[] $atts User defined attributes in shortcode tag
	 * @param string $shortcode_name Name of the shortcode
	 * @return array<string, string|int> parameters in a array
	 * @access protected
	 */
	protected function _init_args( array $arg_structure, array $atts, string $shortcode_name ): array {
		if ( empty( $atts ) ) { $atts = array(); } //Shortcode call be called with $atts=''

		// normalize attribute keys, lowercase
		$atts = array_change_key_case( $atts, CASE_LOWER );

		// Attributes
		$arg = shortcode_atts( $arg_structure, $atts, $shortcode_name );
		return $arg;
	}

	//----------------------------------------------------------------
	//On recherche une éventuelle équipe attachée à la page courante
	//----------------------------------------------------------------
	/**
	  * We are looking for a possible team attached to the current page
	  * @return int id of the team. 0 if no answer.
	  * @access protected
	  */
	protected function _team_of_curent_page(): int {
		return $this->_item_of_curent_page( 'Team' );
	}
	//----------------------------------------------------------------
	//On recherche une éventuelle unité attachée à la page courante
	//----------------------------------------------------------------
	/**
	  * We are looking for a possible unit attached to the current page
	  * @return int id of the unit. 0 if no answer.
	  * @access protected
	  */
	protected function _unit_of_curent_page(): int {
		return $this->_item_of_curent_page( 'Unit' );
	}

	//------------------------------------------------------------------
	//On recherche une éventuelle personne attachée à la page courante
	//------------------------------------------------------------------
	/**
	  * We are looking for a possible person attached to the current page
	  * @return int id of the person. 0 if no answer.
	  * @access protected
	  */
	protected function _person_of_curent_page(): int {
		return $this->_item_of_curent_page( 'Person' );
	}

	/**
	 * Sanitize all parameters. Limit parameter names to its plural form.
	 * @param array<string, string|int> &$args array of arguments to sanitize
	 * @return array<string, mixed> sanitized arguments
	 * @access protected
	 */
	protected function _sanitize_parameters( array $args ): array {
		static $singular_to_plural = array (
			'year' => 'years',
			'annee' => 'years',
			'category' => 'categories',
			'team' => 'teams',
			'unit' => 'units',
			'person' => 'persons',
			'group' => 'groups',
			'thematic' => 'thematics',
		);
		//We can use parameters in singular
		foreach ( array_keys( $args ) as $key ) {
			if ( in_array( $key, array_keys( $singular_to_plural ) ) ) {
				if ( ! empty( $args[ $key ] ) ) {
					$args[ $singular_to_plural[ $key ] ] = $args[ $key ];
				}
				unset( $args[ $key ] );
			}
		}

		foreach ( array_keys( $args ) as $key ) {
			switch ( $key ) {
				case 'years':
						$args[ $key ] = Lib::sanitize_text_or_int_field( $args[ $key ] );
					break;
				case 'teams':
				case 'units':
				case 'persons':
				case 'groups':
				case 'thematics':
						$args[ $key ] = $this->_convert_array_values_to_int(
							$this->_prepare_multiple_values_variable_into_array(
								Lib::sanitize_text_or_int_field( $args[ $key ] )
								)
						);
					break;
				case 'categories':
					$args[ $key ] = $this->_prepare_multiple_values_variable_into_array( strtolower( sanitize_text_field( $args[ $key ] ) ) );
					break;
				case 'teams_publications_page':
				case 'external_link_title':
					$args[ $key ] = esc_url( $args[ $key ] );
					break;
				case 'title':
					$args[ $key ] = sanitize_text_field( $args[ $key ] );
					break;
				case 'display_direction':
				case 'uniquelist':
				case 'presentation':
					$args[ $key ] = sanitize_key( $args[ $key ] );
					break;
			}
		}
		return $args;
	}

	/**
	* Function that takes a value in the form 'a,b,c,d' and returns an array.
	* ['a', 'b', 'c', 'd']
	* You can also provide a value in the form 'a|b|c|d' or 'a;b;c;d'.
	 * @param string|string[]|int[]|null $inputs 'a,b,c' or ['a','b', 'c']
	 * @return array<int,string|int> the array based on the string
	 * @access protected
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	protected  function _prepare_multiple_values_variable_into_array( $inputs ): array {
		$retval = array();
		if ( ! empty( $inputs ) and ( '__empty__' !== $inputs ) ) {
			if ( ! is_array( $inputs ) ) {
				//First, standardize the string
				$inputs = str_replace( '|', ',', $inputs ); //Pouvoir utiliser | comme separateur
				$inputs = str_replace( ';', ',', $inputs ); //Pouvoir utiliser ; comme separateur
				$retval = explode( ',', $inputs ); //Transformation en array
			} else {
				$retval = $inputs;
			}
			//Suppress empty entries
			foreach ( $retval as $key=>$input ) {
				if ( empty( $input ) ) {
					unset( $retval[ $key ] );
				}
			}
		}
		return $retval;
	}

	/**
	* Function that convert array values into integer array
	 * @param mixed[] $input
	 * @return int[] the array based on the string
	 * @access protected
	 */
	protected function _convert_array_values_to_int( $input ): array {
		$retval = array();
		if ( ! empty( $input ) ) {
			if ( is_array( $input ) ) {
				foreach ( $input as $key => $value ) {
					$retval[ $key ] = intval( $value );
				}

			}
		}
		return $retval;
	}
}
