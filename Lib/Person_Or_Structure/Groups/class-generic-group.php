<?php
namespace MonLabo\Lib\Person_Or_Structure\Groups;

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );
///////////////////////////////////////////////////////////////////////////////////////////
// Librairie de fonctions pour manipuler les groupes de personnes ou d’équipes.
///////////////////////////////////////////////////////////////////////////////////////////

//========================================================================
/**
 * generic functionalities to manipulate people and teams
 * __construct( array $_elements_table = array() )
 * count()
 * filter_with_ids( $elements_ids )
 *
 */
/**
 * Class \MonLabo\Lib\Person_Or_Structure\Groups\Generic_Group
 * @package
 */
abstract class Generic_Group {

	/**
	* List of persons or teams
	* @access protected
	* @var object[]
	*/
	protected $_elements_table;
	/**
	* list of team that was used by filter
	* @access protected
	* @var int[]
	*/
	protected $_team_filter;

	/**
	 * Create a new group of person or team
	 * @param object[] $elements_table array of object of persons or teams
	 */
	public function __construct( array $elements_table = array() ) {
		$this->_elements_table = $elements_table;
		$this->_team_filter = array();
	}

	/**
	 * Count number of elements int elements_table
	 * @return int
	 */
	public function count(): int {
		return count( $this->_elements_table );
	}

	/**
	 * Removes people or teams that are not in a list provided as an argument.
	 * @param int[]|null $elements_ids List of id to suppress
	 * @return $this
	 */
	public function filter_with_ids( $elements_ids ): self {
		if ( ! empty( $this->_elements_table ) ) {
			if ( empty( $elements_ids ) ) { //On supprime tout
				$this->_elements_table = array();
				return $this;
			}
			//1 - On indexe pour suppression toute personne ou équipe n'etant pas dans la liste autorisée
			$key_to_suppress = array();
			foreach ( array_keys( $this->_elements_table ) as $key ) {
				if ( ! in_array( $key, $elements_ids ) ) {
					$key_to_suppress[ $key ] = $key;
				}
			}
			$this->_filter_with_cleaned_ids( $key_to_suppress ); //2 - On procède à la suppression effective
		}
		return $this;
	}

	/**
	 * Removes people or teams that are the list provided as an argument.
	 * @param int[] $key_to_suppress List of id to suppress
	 * @return void
	 * @access protected
	 */
	protected function _filter_with_cleaned_ids( array $key_to_suppress ) {
		if ( ! empty( $key_to_suppress ) ) {
			foreach ( $key_to_suppress as $key ) {
				unset( $this->_elements_table[ $key ] );
			}
		}
	}

	/**
	 * Removes people or teams with the property values not in a list of authorized values
	 * @param string $property property to inspect
	 * @param int[]|string[]|null $authorized_values List of authorized values.
	 * @return void
	 * @access protected
	 */
	protected function _filter_from_authorized_values( string $property, $authorized_values ) {
		if ( ! empty( $authorized_values ) ) {
			$key_to_suppress = array();
			foreach ( $this->_elements_table as $key => $elements_ids ) {
				if ( property_exists( $elements_ids, $property ) ) {
					if ( ! in_array( $elements_ids->{$property}, $authorized_values ) ) {
						$key_to_suppress[ $key ] = $key;
					}
				}
			}
			$this->_filter_with_cleaned_ids( $key_to_suppress ); //On procède à la suppression effective
		}
	}

}
