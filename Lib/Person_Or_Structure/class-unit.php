<?php
namespace MonLabo\Lib\Person_Or_Structure;

use MonLabo\Lib\Access_Data\{Access_Data};

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

///////////////////////////////////////////////////////////////////////////////////////////
// Librairie de fonctions pour manipuler une unité.
///////////////////////////////////////////////////////////////////////////////////////////
/**
 * Class \MonLabo\Lib\Person_Or_Structure\Unit
 * @package
 */

/*
HERITED:
	__construct( string $type = '', $arg = null )
	load_from_id( int $item_id = 0 )
	reload( )
	get_name( string $lang = 'fr' )
	_keep_only_valid_keys( &$table_data )
	_fill_missing_keys( &$table_data ):
	_extract_list_array_from_data( string $list_name, array &$data, $default_return = 'not_set' )
	_input_array_of_ids( $inputs )
	
DEFINED:
	load_custom( string $type, $arg ) 
	set_directors( $new_directors_id )
	add_director( int $new_director_id )
	remove_director( int $director_id )
	update( array $table_data = array() )
	delete()
	insert ( array $table_data = array() ) 
*/
class Unit extends Person_Or_Structure{

	/**
	* Type of item to manipulate
	* @var string
	*/
	const TYPE_OF_INFORMATION = 'unit';

	/**
	* Default field if empty
	* @var array<string, int|string|array<int|string>|null>
	*/
	const DEFAULT_INFORMATION = array(
		'id'=>0,
		'affiliations'=>'',
		'code'=>'',
		'name_en'=>'',
		'name_fr'=>'',
		'wp_post_ids'=>array(),
		'address_alt'=>'',
		'contact_alt'=>'',
		'descartes_publi_unit_id'=>null,
		'hal_publi_unit_id'=>null,
		'logo'=>'',
	);

	/**
	 * load unit in a special way
	 * @param string  $type 'from_code' permit to load a unit from code name
	 * @param mixed $arg here the code of the unit to retreive
 	 * @return void
	 */
	public function load_custom( string $type, $arg ) {
		$accessData = new Access_Data();
		if ( 'from_code' === $type ) {
			$this->load_from_id( (int) $accessData->get_unit_id_from_code( (string) $arg ) );
		}
	}

	/**
	 * Update directors for an unit
	 * @param string[]|string $new_directors_id array of directors id
	 * @return self
	 */
	public function set_directors( $new_directors_id ): self  {
		if ( 'not_set' !== $new_directors_id ) {
			$accessData = new Access_Data();
			if ( ! $this->is_empty() ) {
				if ( ( ! empty( $new_directors_id ) ) and ( ! is_array( $new_directors_id ) ) ) {
					$new_directors_id = $this->_input_array_of_ids( $new_directors_id );
				}
				$accessData->delete_relations_between_directors_and_a_unit( $this->info->id );
				if ( ( ! empty( $new_directors_id ) ) ) { //We already know that $new_directors_id is an array
					foreach ( $new_directors_id as $new_director_id ) {
						$this->add_director( (int) $new_director_id );
					}
				}
			}
		}
		return $this;
	}

	/**
	 * Add a director for an unit
	 * @param int $new_director_id director id
	 * @return self
	 */
	public function add_director( int $new_director_id ): self {
		$accessData = new Access_Data();
		$accessData->add_director_to_an_unit( $new_director_id, $this->info->id );
		return $this;
	}

	/**
	 * Remove a director for an unit
	 * @param int $director_id director id
	 * @return self
	 */
	public function remove_director( int $director_id ): self {
		$accessData = new Access_Data();
		$accessData->remove_director_from_an_unit( $director_id, $this->info->id );
		return $this;
	}

	/**
	 * Update a Unit in the database
	 * @param array<string, mixed> $table_data fields to change. If empty, send all $this->info.
	 * @return void
	 */
	public function update( array $table_data = array() )
	{
		$accessData = new Access_Data();
		if ( empty( $table_data ) ) {
			//Put in database eventual updates in memory of $this->info
			$accessData->update_item( 'unit',$this->info->id, (array) $this->info );
			return;
		}

		$directors = $this->_extract_list_array_from_data( 'directors', $table_data );

		//Keep only valid keys
		$this->_keep_only_valid_keys( $table_data );
		unset( $table_data['id'] );

		$accessData->update_item( 'unit',$this->info->id, $table_data );

		//Configure directors for this unit
		$this->set_directors( $directors );

		$this->reload();
	}

	/**
	 * Delete the Unit in the database
	 * @return void
	 */
	public function delete() {
		if ( ! $this->is_empty() ) {
			$accessData = new Access_Data();
			$accessData->delete_relations_between_directors_and_a_unit( $this->info->id );
			$accessData->delete_relations_between_teams_and_a_unit( $this->info->id );
			$accessData->delete_item( self::TYPE_OF_INFORMATION, $this->info->id );
			$this->info = (object) self::DEFAULT_INFORMATION;
		}
	}

	/**
	 * Insert a Unit in the database
	 * @param array<string, mixed> $table_data fields to change.
	 * @return void
	 */
	public function insert ( array $table_data = array() ) {
		if ( ! empty( $table_data ) ) {
			$accessData = new Access_Data();
			$directors = $this->_extract_list_array_from_data( 'directors', $table_data );

			//Keep only valid keys
			$this->_keep_only_valid_keys( $table_data );
			unset( $table_data['id'] );

			//Set value to default if not defined
			$this->_fill_missing_keys( $table_data );

			//Insert person
			$new_id = $accessData->insert_item( self::TYPE_OF_INFORMATION, $table_data );
			$this->load_from_id( (int) $new_id );

			//Configure directors for this unit
			$this->set_directors( $directors );
		}
	}

}
