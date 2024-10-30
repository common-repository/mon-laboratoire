<?php
namespace MonLabo\Lib\Person_Or_Structure;

use MonLabo\Lib\Access_Data\{Access_Data};

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

///////////////////////////////////////////////////////////////////////////////////////////
// Librairie de fonctions pour manipuler une équipe.
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
 * set_leaders( $new_leaders_id ):
 * set_persons( $new_persons_id ):
 * set_thematics( $new_thematics_id )
 * add_person( int $person_id )
 * add_leader( int $leader_id )
 * add_thematic( int $thematic_id )
 * update( array $table_data = array() )
 * delete()
 * insert( array $table_data = array() )
 */

class Team extends Person_Or_Structure {

	/**
	* Type of item to manipulate
	* @var string
	*/
	const TYPE_OF_INFORMATION = 'team';

	/**
	* Default field if empty
	* @var array<string, int|string|array<int|string>|null>
	*/
	const DEFAULT_INFORMATION = array(
		'id'=>0,
		'name_en'=>'',
		'name_fr'=>'',
		'wp_post_ids'=>array(),
		'descartes_publi_team_id'=>null,
		'hal_publi_team_id'=>null,
		'id_unit'=> null,
		'logo'=>'',
		'color'=>''
	);

	/**
	 * Update leaders for a team	 *
	 * @param string[]|int[]|string|int $new_leaders_id array of leaders id
	 * @return Team
	 */
	public function set_leaders( $new_leaders_id ): self {
		if ( 'not_set' !== $new_leaders_id ) {
			if ( ! $this->is_empty() ) {
				$new_leaders_id = $this->_input_array_of_ids( $new_leaders_id );
				//On efface tous les leaders de l’équipes
				$accessData = new Access_Data();
				$accessData->delete_relations_between_leaders_and_a_team( $this->info->id );
				foreach ( $new_leaders_id as $new_leader_id ) {
					//Add only if the person exists
					if ( ! empty( $accessData->get_info( 'person', $new_leader_id ) ) ) {
						$this->add_leader( intval( $new_leader_id ) );
					}
				}
			}
		}
		return $this;
	}

	/**
	 * Update persons for a team	 *
	 * @param string[]|int[]|string|int $new_persons_id array of persons id
	 * @return Team
	 */
	public function set_persons( $new_persons_id ): self {
		if ( 'not_set' !== $new_persons_id ) {
			if ( ! $this->is_empty() ) {
				$new_persons_id = $this->_input_array_of_ids( $new_persons_id );
				$accessData = new Access_Data();
				//We will restore leaders
				$leaders_id = $accessData->get_leaders_id_for_a_team( $this->info->id  );
				$leaders_id = $this->_input_array_of_ids( $leaders_id );
				//On efface tous les membres de l’équipes
				$accessData->delete_relations_between_persons_and_a_team( $this->info->id );
				foreach ( $new_persons_id as $new_person_id ) {
					//Add only if the person exists
					if ( ! empty( $accessData->get_info( 'person', $new_person_id ) ) ) {
						$this->add_person( intval( $new_person_id ) );
					}
				}
				//Restore only leaders that are in the list of persons
				$leaders_id = array_intersect($new_persons_id, $leaders_id);
				$this->set_leaders( $leaders_id );
			}
		}
		return $this;
	}

	/**
	 * Update thematics for a team
	 * @param string[]|string $new_thematics_id array of thematics id
	 * @return Team
	 */
	public function set_thematics( $new_thematics_id ): self {
		if ( 'not_set' !== $new_thematics_id ) {
			if ( ! $this->is_empty() ) {
				$accessData = new Access_Data();
				$old_thematics_id = $accessData->get_thematics_id_for_a_team( $this->info->id );
				$new_thematics_id = $this->_input_array_of_ids( $new_thematics_id );
				//On efface toutes les thématiques qui ne sont pas dans la nouvelle liste $new_thematics_id
				$old_thematics = array_diff( $old_thematics_id, $new_thematics_id );
				if ( ! empty( $old_thematics ) ) {
					$accessData->delete_relations_between_thematics_and_a_team( $this->info->id, $old_thematics );
				}

				//On rajoute les autres thématiques nouvelles
				$thematics_id_to_add = array_diff( $new_thematics_id, $old_thematics_id );
				foreach ( $thematics_id_to_add as $thematic_id ) {
					$this->add_thematic( intval( $thematic_id ) );
				}
			}
		}
		return $this;
	}

	/**
	 * Add a person to a team
	 * @param int $person_id
	 * @return self
	 */
	public function add_person( int $person_id ): self {
		$accessData = new Access_Data();
		$accessData->add_person_to_a_team( $person_id, $this->info->id );
		return $this;
	}

	/**
	 * Add a leader to a team
	 * @param int $leader_id
	 * @return self
	 */
	public function add_leader( int $leader_id ): self {
		$accessData = new Access_Data();
		$accessData->add_leader_to_a_team( $leader_id, $this->info->id );
		return $this;
	}

	/**
	 * Add a thematic to a team
	 * @param int $thematic_id
	 * @return self
	 */
	public function add_thematic( int $thematic_id ): self {
		$accessData = new Access_Data();
		$accessData->add_thematic_to_a_team( $thematic_id, $this->info->id );
		return $this;
	}

	/**
	 * Update team information
	 *
	 * @param array<string, mixed> $table_data array( information_name => information_value )
	 * where information_value is an array when information_name is leaders or thematiques
	 * otherwise information_value is a string
	 * @return self
	 */
	public function update( array $table_data = array() ): self {
		$accessData = new Access_Data();
		if ( empty( $table_data ) ) {
			//Put in database eventual updates in memory of $this->info
			$accessData->update_item( 'team', $this->info->id, (array) $this->info );
			return $this;
		}

		//Extract lists of teams, mentors and students for this person
		$leaders = $this->_extract_list_array_from_data( 'leaders', $table_data );
		$thematics = $this->_extract_list_array_from_data( 'thematics', $table_data );
		$persons = $this->_extract_list_array_from_data( 'persons', $table_data );

		//Keep only valid keys
		$this->_keep_only_valid_keys( $table_data );
		unset( $table_data['id'] );

		$accessData->update_item( 'team', $this->info->id, $table_data );

		//Configure persons, leaders and thematics for this team
		$this->set_thematics( $thematics );
		$this->set_leaders( $leaders );
		$this->set_persons( $persons );

		$this->reload();
		return $this;
	}

	/**
	 * Delete the Team in the database
	 * @return void
	 */
	public function delete() {
		if ( ! $this->is_empty() ) {
			$accessData = new Access_Data();
			$accessData->delete_relations_between_persons_and_a_team( $this->info->id );
			$accessData->delete_relations_between_thematics_and_a_team( $this->info->id );

			$accessData->delete_item( self::TYPE_OF_INFORMATION, $this->info->id );
			$this->info = (object) self::DEFAULT_INFORMATION;
		}
	}

	/**
	 * Insert a team in the database
	 * @param array<string, mixed> $table_data fields to change.
	 * @return void
	 */
	public function insert( array $table_data = array() ) {
		if ( ! empty( $table_data ) ) {
			$accessData = new Access_Data();

			//Extract lists of teams, mentors and students for this person
			$leaders = $this->_extract_list_array_from_data( 'leaders', $table_data );
			$thematics = $this->_extract_list_array_from_data( 'thematics', $table_data );
			$persons = $this->_extract_list_array_from_data( 'persons', $table_data );

			//Keep only valid keys
			$this->_keep_only_valid_keys( $table_data );
			unset( $table_data['id'] );

			//Set value to default if not defined
			$this->_fill_missing_keys( $table_data );

			//Insert
			$new_id = $accessData->insert_item( self::TYPE_OF_INFORMATION, $table_data );
			$this->load_from_id( (int) $new_id );

			//Configure persons, leaders and thematics for this team
			$this->set_thematics( $thematics );
			$this->set_leaders( $leaders );
			$this->set_persons( $persons );
		}
	}

}
