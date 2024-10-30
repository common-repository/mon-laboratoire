<?php
namespace MonLabo\Lib\Person_Or_Structure;

use MonLabo\Lib\Access_Data\{Access_Data};

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

///////////////////////////////////////////////////////////////////////////////////////////
// Librairie de fonctions pour manipuler une thématique.
///////////////////////////////////////////////////////////////////////////////////////////
/**
 * Class \MonLabo\Lib\Person_Or_Structure\Team
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
 * update( array $table_data = array() ):
 * delete()
 * insert( array $table_data = array() )
 */
class Thematic extends Person_Or_Structure {

	/**
	* Type of item to manipulate
	* @var string
	*/
	const TYPE_OF_INFORMATION = 'thematic';

	/**
	* Default field if empty
	* @var array<string, int|string|array<int|string>|null>
	*/
	const DEFAULT_INFORMATION = array(
		'id'=>0,
		'name_en'=>'',
		'name_fr'=>'',
		'wp_post_ids'=>array(),
		'hal_publi_thematic_id'=>null,
		'logo'=>'',
	);

	/**
	 * Update a thematic in the database
	 * @param array<string, mixed> $table_data fields to change. If empty, send all $this->info.
	 * @return Thematic
	 */
	public function update( array $table_data = array() ): self {
		$accessData = new Access_Data();
		if ( empty( $table_data ) ) {
			//Put in database eventual updates in memory of $this->info
			$accessData->update_item( 'thematic', $this->info->id, (array) $this->info );
			return $this;
		}

		//Keep only valid keys
		$this->_keep_only_valid_keys( $table_data );
		unset( $table_data['id'] );
		$accessData->update_item( 'thematic', $this->info->id, $table_data );
		$this->reload();
		return $this;
	}

	/**
	 * Delete the thematic in the database
	 * @return void
	 */
	public function delete() {
		if ( ! $this->is_empty() ) {
			$accessData = new Access_Data();
			$accessData->delete_relations_between_teams_and_a_thematic( $this->info->id );
			$accessData->delete_item( self::TYPE_OF_INFORMATION, $this->info->id );
			$this->info = (object) self::DEFAULT_INFORMATION;
		}
	}

	/**
	 * Insert a thematic in the database
	 * @param array<string, mixed> $table_data fields to change.
	 * @return void
	 */
	public function insert( array $table_data = array() ) {
		if ( ! empty( $table_data ) ) {
			$accessData = new Access_Data();
			
			//Keep only valid keys
			$this->_keep_only_valid_keys( $table_data );
			unset( $table_data['id'] );

			//Set value to default if not defined
			$this->_fill_missing_keys( $table_data );

			//Insert thematic
			$new_id = $accessData->insert_item( self::TYPE_OF_INFORMATION, $table_data );
			$this->load_from_id( (int) $new_id );
		}
	}

}
