<?php
namespace MonLabo\Lib\Person_Or_Structure;

use MonLabo\Lib\{Translate};
use MonLabo\Lib\Access_Data\{Access_Data};

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

///////////////////////////////////////////////////////////////////////////////////////////
// Librairie abstraite de fonctions pour manipuler une équipe, ou une personne.
///////////////////////////////////////////////////////////////////////////////////////////
/**
 * Class \MonLabo\Lib\Person_Or_Structure
 * @package
 */

/*
	__construct( string $type = '', $arg = null )
	load_from_id( int $item_id = 0 )
	reload( )
	_keep_only_valid_keys( &$table_data )
	_fill_missing_keys( &$table_data ):
	_extract_list_array_from_data( string $list_name, array &$data, $default_return = 'not_set' )
	_input_array_of_ids( $inputs )
	get_name( string $lang = 'fr' )

*/
abstract class Person_Or_Structure {

	/**
	* Fields of one row of table
	* @var object
	*/
	public $info;


	/**
	 * Force les classes filles à définir ces méthodes
	 * @param array<string, mixed> $table_data fields to change.
	 * @return void
	 * @abstract
	 */
	abstract protected function insert( array $table_data = array() ) ;

	/**
	 * Retrieve from database person or structure
	* @param string $type : type of construction
	* @param mixed $arg : argument for the constructor
	 */
	public function __construct( string $type = '', $arg = null ) {
		//By default uses DEFAULT_INFORMATION of the child instance
		$this->info = (object) get_class( $this )::DEFAULT_INFORMATION;
		switch ( $type ) {
			case 'from_id':
				if ( is_numeric( $arg ) ) {
					$this->load_from_id( intval( $arg ) );
				}
				break;
			case 'insert':
				if ( is_array( $arg ) ) {
					$this->insert( $arg );
				}
				break;
			case 'from_wpPostId':
				$accessData = new Access_Data();
				$this->load_from_id(
					(int) $accessData->get_itemId_from_wpPostId(
							get_class( $this )::TYPE_OF_INFORMATION, (string) $arg
						)
				);
				break;
			default :
				$this->load_custom( $type, $arg );
				break;
		}
	}

	/**
	 * load unit in a special way : Empty dummy function
	 * @param string  $type 'from_code' permit to load a unit from code name
	 * @param mixed $arg here the code of the unit to retreive
	 * @return void
 	 * @access protected
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	protected function load_custom( string $type, $arg ) { // @phan-suppress-current-line PhanUnusedProtectedMethodParameter
		//Dummy function
	}
	
	/**
	 * Retrieve from database person or structure
	 * @param int $item_id ID of the person or structure
	 * @return void
	 * @access protected
	 */
	protected function load_from_id( int $item_id = 0 ) {
		if ( ! empty( $item_id ) ) {
			$accessData = new Access_Data();
			$tmp_info = $accessData->get_info( get_class( $this )::TYPE_OF_INFORMATION, $item_id );
			if ( empty( $tmp_info ) or ( ! property_exists( $tmp_info, "id" ) ) ) {
				//If object not exists, fill with default values
				$this->info = (object) get_class( $this )::DEFAULT_INFORMATION;
				return;
			}
			$tmp_info->id = (int) $tmp_info->id;
			$this->info = $tmp_info;
		}
	}

	/**
	* Get back in the instance the values of person of structure
	* @return void
	*/
	public function reload( ) {
		if ( ! empty( $this->info->id ) ) {
			$this->load_from_id( $this->info->id );
		}
	}

	/**
	 * Tells if $info is empty
	 * @return bool true if empty, else false
	 */
	public function is_empty(): bool {
		return ( empty( $this->info->id ) );
	}

	/**
	 * Keep only in $table_fata fields that are used for Persons->info
	 * Set value to default if missing
	 * @param array<string, mixed> $table_data fields to filter.
	 * @return $this
	 * @access protected
	 */
	protected function _keep_only_valid_keys( &$table_data ): self {
		$valid_keys = array_keys( get_class( $this )::DEFAULT_INFORMATION );
		foreach ( array_keys( $table_data ) as $key ) {
			if ( ! in_array( $key, $valid_keys ) ) {
				unset( $table_data[ $key ] );
			}
		}
		return $this;
	}

	/**
	 * Set value to default if missing
	 * @param array<string, mixed> $table_data fields to examine.
	 * @return $this
	 * @access protected
	 */
	protected function _fill_missing_keys( &$table_data ): self {
		//Set value to default if not defined
		foreach (get_class( $this )::DEFAULT_INFORMATION as $key => $value) {
			if (!isset( $table_data[ $key ] )) {
				$table_data[ $key ] = $value;
			}
		}
		return $this;
	}


	/**
	 * Extrat a list array in param and suppress from data
	 * @param string $list_name List to extract
	 * @param array<string|int, mixed> $data array( info_name => info_value )
	 * @param mixed $default_return value to return if $list_name not in $data
	 * @return string[]|mixed the list array
	 * @access protected
	 **/
	protected function _extract_list_array_from_data( string $list_name, array &$data, $default_return = 'not_set' ) {
		$list = $default_return;
		if ( array_key_exists( $list_name, $data ) ) {
			$list = $this->_input_array_of_ids( $data[ $list_name ] );
		}
		return $list;
	}

	/**
	 * Transform input into clean array
	 * @param array<string|int, string|int>|string|int $inputs array or coma separated items in a string
	 * @return int[] always in array without empty entries
	 * array() if no result is found
	 * @access protected
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 **/
	protected function _input_array_of_ids( $inputs ): array {
		if ( ! is_array( $inputs ) ) {
			if ( ! is_integer( $inputs ) ) {
				$inputs = strval( $inputs );
			}
			$inputs = str_replace( '|', ',', (string) $inputs ); //Pouvoir utiliser | comme separateur
			$inputs = explode( ',', $inputs ); //Comma separated into array
		}
		//Suppress empty entries in array
		if ( ! empty( $inputs ) ) {
			foreach ( $inputs as $key=>$input ) {
				$input = intval( trim( strval( $input ) ) );
				if ( empty( $input ) or ( $input < 1 ) ) {
					unset( $inputs[ $key ] );
				} else {
					$inputs[ intval( $key )] = $input;
				}
			}
		}
		return $inputs;
	}

	/**
	 * Get entity name (only valid if exists "name_en" and "name_fr" fields )
	 * @param string $lang language
	 * @return string
	 */
	public function get_name( string $lang = 'fr' ): string
	{
		if ( property_exists( $this->info, 'name_en' ) ) {
			$translate = new Translate( $lang );
			$field = 'name_' . $translate->get_lang_short();

			return $this->info->{$field};
		}
		return '';
	}
}
