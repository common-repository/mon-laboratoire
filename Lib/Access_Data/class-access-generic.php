<?php
namespace MonLabo\Lib\Access_Data;
use MonLabo\Lib\{Db, Lib, Translate};

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

///////////////////////////////////////////////////////////////////////////////////////////
// Librairie abstraite de fonctions pour manipuler une équipe, ou une personne.
///////////////////////////////////////////////////////////////////////////////////////////
/**
 * Class \MonLabo\Lib\Person_Or_Structure
 * @package
 */

/*
class Access_Generic {

	//PRIVATE METHODS
	_first_key_of_array( $myarray )
	_get_format_from_data( $table_name, $data )
	_tidy_object_table_and_leave_only_one_column( $object_table, $index_name = 'id', $value_name = 'id', $values_type = 'string' )
	_tidy_object_table_by_id( $object_table )
	_column_name_for_language( $lang )
	_type_to_table_name( string $type )

	// ACCESSOR METHODS
	-------------------------------------------------------------
	_get_step_table_columns( $union_table_name, $step_table_name, $union_table_columns, $step_table_columns, $principal_table_id, $order_by = array(), $add_union_condition = array(), $add_step_condition = array() )
 	_prepare_condition_array_from_status( $status )
	_prepare_condition_sql_from_status( $status )

	// Generic methods
	get_info ( $type, $item_id )
	get_itemId_from_wpPostId( string $type, $wp_post_id )
	get_itemIds_from_wpPostId( string $type, $wp_post_id )

	// MUTATOR METHODS
	 -------------------------------------------------------------
	// Generic methods
	insert_item ( $type, $data )

	* Update Methods
	_are_update_params_valid( $item_id, $data )
	update_item( $type, $person_id, $data )

	// Generic methods
	delete_item( string $type, int $item_id )
	_delete_relations_in_a_table( string $table, string $id1_name, int $id1_value, string $idlist_name, array $idlist_values)
}
*/
abstract class Access_Generic {

	/**
	* WordPress database access abstraction class
	* @var \wpdb
	*/
	protected $wpdb;

	/**
	* Prefix of database
	* @var string
	*/
	protected $_db_prefix;

	/*
	 * Create a new class
	 * @global $wpdb WordPress database access abstraction class
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
		$db = new Db;
		$this->_db_prefix = $db->db_prefix;
	}


	////////////////////////////////////////////////////////////////////
	//  PRIVATE METHODS
	////////////////////////////////////////////////////////////////////
	/**
	* Return the first key of the array
	* @param mixed[]|null $myarray array where to extract value
	* @return mixed|null value extracted (null if no first value exists)
	* @access protected
	*/
	protected function _first_key_of_array( $myarray ) {
		$val = null;
		if ( ! empty( $myarray ) ) {
			return array_key_first( $myarray );
		}
		return $val;
	}

	/**
	* Get the format of all data in $data
	* @param string $table_name sql table format to fit
	* @param array<string, int|string> $data array( information_name => information_value )
	*              where information_value is an array when information_name is
	*              teams or tuteur otherwise information_value is a string
	* @return string[] $format
	*              An array of formats to be mapped to each of the values in
	*              $data. A information_format is one of '%d', '%f', '%s'
	*              (integer, float, string). Retun array() if any problem.
	* @access protected
	*/
	protected function _get_format_from_data( string $table_name, array $data ): array {
		$format_std = array(
			$this->_db_prefix . 'MonLabo_persons' => array(
				'id'							=> '%d',
				'wp_post_ids'				=> '%s',
				'title'						=> '%s',
				'first_name'					=> '%s',
				'last_name'					=> '%s',
				'category'					=> '%s',
				'function_en'				=> '%s',
				'function_fr'				=> '%s',
				'mail'						=> '%s',
				'phone'						=> '%s',
				'room'						=> '%s',
				'address_alt'				=> '%s',
				'external_url'				=> '%s',
				'descartes_publi_author_id'	=> '%d',
				'hal_publi_author_id'		=> '%s',
				'uid_ENT_parisdescartes'		=> '%s',
				'date_departure'				=> '%s',
				'status'						=> '%s',
				'visible'					=> '%s',
				'custom1'					=> '%s',
				'custom2'					=> '%s',
				'custom3'					=> '%s',
				'custom4'					=> '%s',
				'custom5'					=> '%s',
				'custom6'					=> '%s',
				'custom7'					=> '%s',
				'custom8'					=> '%s',
				'custom9'					=> '%s',
				'custom10'					=> '%s',
				'image'						=> '%s',
				'external_mentors'			=> '%s',
				'external_students'			=> '%s',
			),
			$this->_db_prefix . 'MonLabo_teams' => array(
				'id'							=> '%d',
				'name_fr'					=> '%s',
				'name_en'					=> '%s',
				'wp_post_ids'				=> '%s',
				'descartes_publi_team_id'	=> '%d',
				'hal_publi_team_id'			=> '%s',
				'id_unit'					=> '%d',
				'logo'						=> '%s',
				'color'						=> '%s',
			),
			$this->_db_prefix . 'MonLabo_thematics' => array(
				'id'							=>'%d',
				'name_fr'					=>'%s',
				'name_en'					=>'%s',
				'wp_post_ids'				=>'%s',
				'logo'						=>'%s',
				'hal_publi_thematic_id'		=>'%s',
			),
			$this->_db_prefix . 'MonLabo_units' => array(
				'id'							=>'%d',
				'affiliations'				=>'%s',
				'code'						=>'%s',
				'name_fr'					=>'%s',
				'name_en'					=>'%s',
				'wp_post_ids'				=>'%s',
				'address_alt'				=>'%s',
				'contact_alt'				=>'%s',
				'descartes_publi_unit_id'	=>'%d',
				'hal_publi_unit_id'			=>'%s',
				'logo'						=>'%s',
			),
		 );
		 if ( ( ! array_key_exists( $table_name, $format_std ) )
			 or ( empty( $data ) )
		 ) {
			return array();
		 }
		$format = array();
		foreach ( array_keys( $data ) as $key ) {
			if ( ! array_key_exists( $key, $format_std[ $table_name ] ) ) {
				return array();
			}
			$format[] = $format_std[ $table_name ][ $key ];
		}
		return $format;
	}

	/**
	 * Extract from an object a table. Table value is from an attribute and indexed by another
	 * @param \stdClass[]|null|\stdClass|array<string,mixed> $object_table
	 * @param string $index_name attribute that will be used for indexes of output table
	 * @param string $value_name attribute that will be used for values of output table
	 * @param string $values_type Type of return array datas "int" or "string".
	 * @return array<int,int|string> ex: array( $id => $value )
	 * @access protected
	 */
	protected function _tidy_object_table_and_leave_only_one_column(
		$object_table,
		string $index_name = 'id',
		string $value_name = 'id',
		string $values_type = "string"
		): array {
		$id_table = array();
		$cast_function_data = "strval";
		if ( "int" === $values_type ) { $cast_function_data = "intval"; }

		if ( ! empty( $object_table ) ) {
			if ( ! is_array( $object_table ) ) {
				$object_table = array( 0 => $object_table );
			}
			foreach ( $object_table as $one_object ) {
				if ( property_exists( $one_object, $index_name ) ) { //In theory always enter in
					$id_table[ intval( $one_object->{$index_name} ) ] =  $cast_function_data( $one_object->{$value_name} );
				}
			}
		}
		return $id_table;
	}

	/**
	 * Index an object table by id
	 * @param \stdClass[]|null|\stdClass|array<string,mixed> $object_table
	 * @return array<int,\stdClass> Objects in a table by id
	 * @access protected
	 */
	protected function _tidy_object_table_by_id( $object_table ): array {
		$id_table = array();
		if ( ! empty( $object_table ) ) {
			if ( ! is_array( $object_table ) ) {
				$object_table = array( 0 => $object_table );
			}
			foreach ( $object_table as $one_object ) {
				if ( property_exists( $one_object, 'id' ) ) { //In theory always enter in
					if ( property_exists( $one_object, 'wp_post_ids' ) ) {
						$one_object->wp_post_ids = $this->_wp_post_ids_into_array( (string) $one_object->wp_post_ids );
					}
					$id_table[ intval( $one_object->id ) ] = $one_object;
				}
			}
		}
		return $id_table;
	}

	/**
	 * Return the column name depending the language
	 * @param string $lang language ( en or local language )
	 * @return string 'name_<lang>'
	 * @access protected
	 */
	protected function _column_name_for_language( string $lang ): string {
		$translate = new Translate( $lang );
		return 'name_' . $translate->get_lang_short();
	}

	/**
	 * Convert type into table name
	 * @param string $type type of information to get ('person' or 'persons', 'team'...)
	 * @return string table name (empty if no result is found)
	 * @access protected
	 */
	protected function _type_to_table_name( string $type ) : string {
		$type = rtrim( $type, "s"); //Accept plural
		switch ( $type ) {
			case 'person':
			case 'team':
			case 'unit':
			case 'thematic':
				return 'MonLabo_' . $type . 's';
			default:
				return '';
		}
	}

	////////////////////////////////////////////////////////////////////
	// ACCESSOR METHODS
	////////////////////////////////////////////////////////////////////

	/*******************************************************************
	 * Generic get Methods
	*******************************************************************/

	/**
	 * Get all values of a columns of a row of a step table
	 *
	 * @param string $union_table_name name of the union table
	 * @param string $step_table_name name of the step table
	 * @param string[] $union_table_columns list of column name of the union table array ( principal_table, step_table )
	 * @param string[] $step_table_columns list of column name of the step table
	 * @param int $principal_table_id id of the row of the principal table
	 * @param string[]|string $order_by ( facultatif) list of column name for ascendant order
	 * @param string[] $add_union_condition ( facultatif) condition array( column_name => column_value ) for union table
	 * @param string[] $add_step_condition ( facultatif) condition array( column_name => column_value ) for step table
	 * @return \stdClass[]|null|\stdClass|array<string,mixed> single variable of the database

	 * null if no result is found
	 * @access protected
	 */
	protected function _get_step_table_columns(
		string $union_table_name,
		string $step_table_name,
		array $union_table_columns,
		array $step_table_columns,
		int $principal_table_id,
		$order_by = array(),
		array $add_union_condition = array(),
		array $add_step_condition = array()
	) {
		$columns = 's.' . implode( ', s.', $step_table_columns );
		$order = '';
		if ( ! empty( $order_by ) ) {
			$order = ' ORDER BY ' . Lib::secured_implode( ' ASC, ', $order_by ) . ' ASC';
		}
		$union_condition = '';
		//No need to test that $add_union_condition is an array. It is already one by parameter declaration.
		foreach ( $add_union_condition as $key => $value ) {
			$union_condition .= ' AND u.' . $key . ' = "' . $value . '" ';
		}
		$step_condition = '';
		//No need to test that $add_step_condition is an array. It is already one by parameter declaration.
		foreach ( $add_step_condition as $key => $value ) {
			$step_condition .= ' AND s.' . $key . ' = "' . $value . '" ';
		}
		$sql = $this->wpdb->prepare( 'SELECT ' . $columns . ' FROM ' . $step_table_name . ' AS s, ' . $union_table_name . ' AS u WHERE u.' . $union_table_columns[0] . ' = "%d" AND u.' . $union_table_columns[1] . ' = s.id ' . $union_condition . $step_condition . $order, $principal_table_id );
		$retval = $this->wpdb->get_results( $sql, OBJECT );
		return ( empty( $retval ) ? null : $retval );
	}

	/**
	  * Prepare the condition array from status
	  * @param string $status status of the persons to select ( by default $status = 'actif' )
	  * @return string[] array( 'status' => $status )
	  * empty array if $status is different from 'actif' and 'alumni'
	  * @access protected
	  */
	protected function  _prepare_condition_array_from_status( string $status ) {
		if ( 'actif' === $status ) {
			return array( 'status' => 'actif' );
		}
		if ( 'alumni' === $status ) {
			return array( 'status' => 'alumni' );
		}
		// $status = 'all'
		return  array();
	}

	/**
	  * Prepare the condition in SQL from status
	  * @param string $status status of the persons to select ( by default $status = 'actif' )
	  * @return string SQL part of command
	  * empty string if $status is different from 'actif' and 'alumni'
	  * @access protected
	  */
	protected function  _prepare_condition_sql_from_status( string $status ): string {
		if ( 'actif' === $status ) {
			return 'WHERE status = "actif"';
		}
		if ( 'alumni' === $status ) {
			return 'WHERE status = "alumni"';
		}
		// $status = 'all'
		return  '';
	}

	/**
	* Check that a string is an array coded into json
	* @param string $string text to anayse
	* @return bool true if text is a json coded array
	 * @access protected
	*/
	protected function _is_valid_json_array_or_object( string $string ) : bool {
		if ( !empty( $string ) ) {
			$decoded =  json_decode( $string );
			if ( ( JSON_ERROR_NONE === json_last_error() )
				and ( is_array( $decoded ) or is_object( $decoded )  )
			) {
				return true;
			}
			return false;
		}
		return false;
	 }

	/**
	 * Convert a wp_post_ids field into an array
	 * @param string $wp_post_ids field $wp_pos_id (several ID of post separated by comma or in an json encoded array).
	 * @return mixed[] converted field
	 * @access protected
	 */
	protected function _wp_post_ids_into_array( $wp_post_ids ) : array {
		$wp_post_ids = (string) $wp_post_ids;
		if ( $this->_is_valid_json_array_or_object( $wp_post_ids ) ) {
			$arr = json_decode( $wp_post_ids );
			if ( is_object( $arr ) ) {
				$arr = (array) $arr;
			}
			foreach ( $arr as $key => $value ) {
				if ( "" === $value ) {
					unset( $arr[ $key ] ); //Suppress empty entries
				}
			}
			return array_values( (array) $arr );
		}
		return explode( ',', $wp_post_ids );
	}

	/**
	 * Get information
	 * @param string $type type of information to get ('person', 'team'...)
	 * @param int $item_id id of the person, team...
	 * @return object|null object( information_name => information_value )
	 */
	public function get_info( string $type, int $item_id ) {
		$table = $this->_type_to_table_name( $type );
		if ( empty( $table ) ){
			return null;
		}
		$sql =  $this->wpdb->prepare(
			'SELECT * FROM '. $this->_db_prefix . $table .' WHERE id = %d',
			$item_id
		);
		$row = $this->wpdb->get_row( $sql );
		if ( empty( $row ) ) {
			return null;
		}
		$row = (object) $row;
		if ( property_exists( $row, 'wp_post_ids' ) ) {
			$row->wp_post_ids = $this->_wp_post_ids_into_array( (string) $row->wp_post_ids );
		}
		return $row;
	}

	/**
	 * Get item id for a wp_post_id
 	 * @param string $type type of information to get ('person', 'team'...)
	 * @param int|string $wp_post_id ID or curtom url of the page
	 * @return int|null item
	 * null if no result is found
	 */
	public function get_itemId_from_wpPostId( string $type, $wp_post_id ) {
		$table = $this->_type_to_table_name( $type );
		if ( empty( $table ) ){
			return null;
		}
		if ( is_integer( $wp_post_id ) ) { $wp_post_id = strval( $wp_post_id ); }
		$wp_post_id = trim( json_encode( $wp_post_id ), '"' ); //Use escape char for special chars / like in json_encode a char.
		$sql = $this->wpdb->prepare( "SELECT id FROM " . $this->_db_prefix . $table . " p WHERE (p.`wp_post_ids` REGEXP CONCAT('\"', %s ,'\"'))", preg_quote( $wp_post_id ) );
		$retval = $this->_first_key_of_array( $this->wpdb->get_results( $sql, OBJECT_K ) );
		if ( ! is_null( $retval )) { $retval = intval( $retval ); }
		return $retval;
	}

	/**
	 * Get all item ids for a wp_post_id
	 * @param string $type type of information to get ('person', 'team'...)
	 * @param int|string $wp_post_id ID or curtom url of the page
	 * @return array<int,int> item_id
	 * empty array if no result is found
	 */
	public function get_itemIds_from_wpPostId( string $type, $wp_post_id ): array {
		$table = $this->_type_to_table_name( $type );
		if ( empty( $table ) ){
			return array();
		}
		if ( is_integer( $wp_post_id ) ) { $wp_post_id = strval( $wp_post_id ); }
		$wp_post_id = trim( json_encode( $wp_post_id ), '"' ); //Use escape char for special chars / like in json_encode a char.
		$sql = $this->wpdb->prepare( "SELECT id FROM " . $this->_db_prefix . $table . " p WHERE (p.`wp_post_ids` REGEXP CONCAT('\"', %s ,'\"'))", preg_quote( $wp_post_id ) );
		$persons = $this->wpdb->get_results( $sql, OBJECT_K );
		return $this->_tidy_object_table_and_leave_only_one_column( $persons, 'id', 'id', 'int' );
	}

	////////////////////////////////////////////////////////////////////
	// MUTATOR METHODS
	////////////////////////////////////////////////////////////////////

	/*******************************************************************
	 * Insert Methods
	 ******************************************************************/
	/**
	 * Insert person or structure
	 * @param string $type type of information to get ('person', 'team'...)
	 * @param array<string,int|string|array<int|string,string|int>> $data array( information_name => information_value )s
	 * @return int|null object( information_name => information_value )
	 */
	public function insert_item( string $type, array $data ) {
		$table = $this->_type_to_table_name( $type );
		if ( empty( $table ) ){
			return null;
		}
		//We need json decode and encode to add symetrical anti-slashes
		if ( isset( $data['wp_post_ids'] ) and ! is_array( $data['wp_post_ids'] ) ) {
			$data['wp_post_ids'] =  json_decode( $data['wp_post_ids'] );
		}
		if ( isset( $data['wp_post_ids'] ) and is_array( $data['wp_post_ids'] ) ) {
			foreach ( $data['wp_post_ids'] as $key => $value ) {
				$data['wp_post_ids'][ $key ] = (string) $value;
				if ( empty( $data['wp_post_ids'][ $key ] ) ) {
					unset( $data['wp_post_ids'][ $key ] );
				}
			}
			$data['wp_post_ids'] = json_encode( array_values( array_unique( $data['wp_post_ids'] ) ) );
		}
		$item_id = null;
		if ( ! empty( $data ) ) {
			$format = $this->_get_format_from_data( $this->_db_prefix . $table, $data );
			$this->wpdb->insert( $this->_db_prefix . $table, $data, $format );
			$item_id = $this->wpdb->insert_id;
		}
		return $item_id;
	}

	/*******************************************************************
	 * Update Methods
	 ******************************************************************/


	/**
	 * Test if parameters of an update function are valid
	 *
	 * @param int $item_id ID of the item to update
	 * @param array<string, int|string> $data array( information_name => information_value )
	 * @return bool : true if parameters are valid, false else
	 */
	private function _are_update_params_valid( int $item_id, array $data ): bool {
		if ( empty( $data) ) {
			return false;
		}
		if ( ( isset( $data['id'] ) )
			and ( ( ! empty( $data['id'] ) )
			and ( $data['id'] != $item_id ) )
		) {
			return false;
		}
		return true;
	}

	/**
	 * Update person or structure
	 * @param string $type type of information to get ('person', 'team'...)
	 * @param int $item_id
	 * @param array<string, int|string|array<int, string>> $data array( information_name => information_value )s
	 * @return void
	 */
	public function update_item( string $type, int $item_id, array $data ) {
		$table = $this->_type_to_table_name( $type );
		if ( empty( $table ) ){
			return;
		}
		//We need json decode and encode to add symetrical anti-slashes
		if ( isset( $data['wp_post_ids'] ) and ! is_array( $data['wp_post_ids'] ) ) {
			$data['wp_post_ids'] =  json_decode( $data['wp_post_ids'] );
		}
		if ( isset( $data['wp_post_ids'] ) and is_array( $data['wp_post_ids'] ) ) {
			foreach ( $data['wp_post_ids'] as $key => $value ) {
				$data['wp_post_ids'][ $key ] = (string) $value;
				if ( empty( $data['wp_post_ids'][ $key ] ) ) {
					unset( $data['wp_post_ids'][ $key ] );
				}
			}
			$data['wp_post_ids'] = json_encode( array_values( array_unique( $data['wp_post_ids'] ) ) );
		}
		if ( ! $this->_are_update_params_valid( $item_id, $data ) ) {
			return;
		}
		$item_info = $this->get_info( $type,  $item_id );
		if ( ! empty( $item_info ) ) {
			if ( ! empty( $data ) ) {
				$where = array( 'id' => $item_id );
				$format = $this->_get_format_from_data( $this->_db_prefix . $table, $data );
				$this->wpdb->update( $this->_db_prefix . $table, $data, $where, $format, array( '%d' ) );
			}
		}
	}

	/*******************************************************************
	 * Generic Delete Method
	 ******************************************************************/

	/**
	 * Delete person or structure
	 * @param string $type type of information to get ('person', 'team'...)
	 * @param int $item_id
	 * @return void
	 */
	public function delete_item( string $type, int $item_id ) {
		$table = $this->_type_to_table_name( $type );
		if ( empty( $table ) ){
			return;
		}
		$item_info = $this->get_info( $type,  $item_id );
		if ( ! empty( $item_info ) ) {
			$this->wpdb->delete( $this->_db_prefix . $table, array( 'id' => $item_id ), array( '%d' ) );
		}
	}

	/*---------------------------------------
	/*  Generic delete methods
	----------------------------------------*/
	/**
	 * Delete in DB table = $table delete all element
	 *		 where $id1_name = $id1_value
	 *		 and $idlist_name = $idlist_onevalue
	 *  for all $idlist_onevalue in $idlist_values
	 *
	 * @param string $table
	 * @param string $id1_name
	 * @param int $id1_value
	 * @param string $idlist_name
	 * @param int[] $idlist_values list of items to unlink
	 * @return void
	 * @access protected
	 */
	protected function _delete_relations_in_a_table(
		string $table,
		string $id1_name,
		int $id1_value,
		string $idlist_name,
		array $idlist_values
	) {
		if ( ! empty( $idlist_values ) ){
			foreach ( $idlist_values as $idlist_onevalue ) {
				$where = array( $id1_name => $id1_value,
								$idlist_name => $idlist_onevalue );
				$this->wpdb->delete( $table, $where, array( '%d', '%d' ) );
			}
		}
	}
}
