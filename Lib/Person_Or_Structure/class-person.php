<?php
namespace MonLabo\Lib\Person_Or_Structure;

use MonLabo\Lib\Access_Data\{Access_Data};
//use MonLabo\Admin\{Page};

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

///////////////////////////////////////////////////////////////////////////////////////////
// Librairie de fonctions pour manipuler une équipe.
///////////////////////////////////////////////////////////////////////////////////////////
/**
 * Class \MonLabo\Lib\Person_Or_Structure\Person
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
	_set_teams( $new_teams_id )
	add_mentor( int $new_mentor_id )
	_set_mentors( $new_mentors_id )
	_set_students( $new_students_id )
	update( array $table_data = array() )
	delete()
	insert( array $table_data = array() )
*/
class Person extends Person_Or_Structure {

	/**
	* Type of item to manipulate
	* @var string
	*/
	const TYPE_OF_INFORMATION = 'person';

	/**
	* Default field if empty
	* @var array<string, int|string|array<int|string>|null>
	*/
	const DEFAULT_INFORMATION = array(
		'id'=>0,
		'wp_post_ids'=>array(),
		'external_url'=>'',
		'title'=>'',
		'first_name'=>'',
		'last_name'=>'',
		'category'=>'',
		'function_en'=>'',
		'function_fr'=>'',
		'date_departure'=>'',
		'mail'=>'',
		'room'=>'',
		'phone'=>'',
		'address_alt'=>'',
		'descartes_publi_author_id'=>null,
		'hal_publi_author_id'=>null,
		'uid_ENT_parisdescartes'=>'',
		'status'=>'',
		'visible'=>'',
		'custom1'=>'',
		'custom2'=>'',
		'custom3'=>'',
		'custom4'=>'',
		'custom5'=>'',
		'custom6'=>'',
		'custom7'=>'',
		'custom8'=>'',
		'custom9'=>'',
		'custom10'=>'',
		'image' => '',
		'external_mentors'=>'',
		'external_students'=>'',
	);

	/**
	 * Update teams for a person
	 * @param string[]|string $new_teams_id array of teams id
	 * @return Person
	 * @access private
	 */
	private function _set_teams( $new_teams_id ): self {
		if ( 'not_set' !== $new_teams_id ) {
			$accessData = new Access_Data();

			$old_teams_id = $accessData->get_teams_id_for_a_person( $this->info->id );
			$new_teams_id = $this->_input_array_of_ids( $new_teams_id );
			//On efface toutes les équipes qui ne sont pas dans la nouvelle liste $new_teams_id
			$teams_to_delete = array_diff( $old_teams_id, $new_teams_id );
			if ( ! empty( $teams_to_delete ) ) {
				$accessData->delete_relations_between_teams_and_a_person( $this->info->id, $teams_to_delete );
			}

			//On rajoute les autres équipes nouvelles
			$new_teams_id_to_add = array_diff( $new_teams_id, $old_teams_id );
			foreach ( $new_teams_id_to_add as $new_team_id_to_add ) {
				//Add only if the new team exists
				$team_to_add = new Team( 'from_id',  $new_team_id_to_add );
				$team_to_add->add_person( $this->info->id );
			}
		}
		return $this;
	}

	/**
	 * Add a mentors for a person
	 * @param int $new_mentor_id mentor ID
	 * @return self
	 */
	public function add_mentor( int $new_mentor_id ): self {
		$accessData = new Access_Data();
		$accessData->add_mentor_to_a_person( $new_mentor_id, $this->info->id );
		return $this;
	}


	/**
	 * Set mentors for a person
	 * @param string[]|string $new_mentors_id array of mentors ID
	 * @return Person
	 * @access private
	 */
	private function _set_mentors( $new_mentors_id ): self {
		if ( 'not_set' !== $new_mentors_id ) {
			$accessData = new Access_Data();

			$old_mentors_id = $accessData->get_mentors_id_for_a_person( $this->info->id );
			$new_mentors_id = $this->_input_array_of_ids( $new_mentors_id );
			//On efface tous les tuteurs qui ne sont pas dans la nouvelle liste $new_mentors_id
			$mentors_to_delete = array_diff( $old_mentors_id, $new_mentors_id );
			if ( ! empty( $mentors_to_delete ) ) {
				$accessData->delete_relations_between_a_person_and_his_or_her_mentors( $this->info->id, $mentors_to_delete );
			}

			//On rajoute les autres tuteurs nouveaux
			$mentors_to_add = array_diff( $new_mentors_id, $old_mentors_id );
			//If person to add exists
			foreach ( $mentors_to_add as $mentor_id ) {
				$this->add_mentor( (int) $mentor_id );
			}
		}
		return $this;
	}

	/**
	 * Set students for a person
	 * @param string[]|string $new_students_id array of students ID
	 * @return Person
	 * @access private
	 */
	private function _set_students( $new_students_id ): self {
		if ( 'not_set' !== $new_students_id ) {
			$accessData = new Access_Data();

			$old_students_id = $accessData->get_students_id_for_a_person( $this->info->id );
			$new_students_id = $this->_input_array_of_ids( $new_students_id );
			//On efface toutes les étudiants qui ne sont pas dans la nouvelle liste $new_students_id
			if ( ! empty( $old_students_id ) ) {
				$accessData->delete_relations_between_a_person_and_his_or_her_students(
					$this->info->id,
					array_diff( $old_students_id, $new_students_id )
				);
			}
			//On rajoute les autres tuteurs nouveaux
			$other_students_id = array_diff( $new_students_id, $old_students_id );
			//If person to add exists
			foreach ( $other_students_id as $student_id ) {
				$accessData->add_mentor_to_a_person( $this->info->id, $student_id );
			}
		}
		return $this;
	}

	/**
	 * Update a person in the database
	 * @param array<string, mixed> $table_data fields to change. If empty, send all $this->info.
	 * @return Person
	 */
	public function update( array $table_data = array() ): self {
		$accessData = new Access_Data();
		if ( empty( $table_data ) ) {
			//Put in database eventual updates in memory of $this->info
			$accessData->update_item( 'person', $this->info->id, (array) $this->info );
			return $this;
		}

		//Extract lists of teams, mentors and students for this person
		$teams = $this->_extract_list_array_from_data( 'teams', $table_data );
		$mentors =$this->_extract_list_array_from_data( 'mentors', $table_data );
		$students =$this->_extract_list_array_from_data( 'students', $table_data );

		//Keep only valid keys
		$this->_keep_only_valid_keys( $table_data );
		unset( $table_data['id'] );

		$accessData->update_item( 'person', $this->info->id, $table_data );

		//Configure teams, mentors and students for this person
		$this->_set_teams( $teams );
		$this->_set_mentors( $mentors );
		$this->_set_students( $students );

		$this->reload();
		return $this;
	}


	/**
	 * Delete the person in the database
	 * @return void
	 */
	public function delete() {
		if ( ! $this->is_empty() ) {
			$accessData = new Access_Data();
			$teams_id_to_unlink = $accessData->get_teams_id_for_a_person ( $this->info->id );
			$accessData->delete_relations_between_teams_and_a_person( $this->info->id, $teams_id_to_unlink );
			$accessData->delete_relations_between_a_person_and_his_or_her_mentors( $this->info->id );
			$accessData->delete_relations_between_units_and_a_director( $this->info->id );

			$accessData->delete_item( self::TYPE_OF_INFORMATION, $this->info->id );
			$this->info = (object) self::DEFAULT_INFORMATION;
		}
	}

	/**
	 * Insert a person in the database
	 * @param array<string, mixed> $table_data fields to change.
	 * @return void
	 */
	public function insert( array $table_data = array() ) {
		if ( ! empty( $table_data ) ) {
			$accessData = new Access_Data();

			//Extract lists of teams, mentors and students for this person
			$teams = $this->_extract_list_array_from_data( 'teams', $table_data );
			$mentors =$this->_extract_list_array_from_data( 'mentors', $table_data );
			$students =$this->_extract_list_array_from_data( 'students', $table_data );

			//Keep only valid keys
			$this->_keep_only_valid_keys( $table_data );
			unset( $table_data['id'] );

			//Set value to default if not defined
			$this->_fill_missing_keys( $table_data );

			//Insert person
			$new_id = $accessData->insert_item( self::TYPE_OF_INFORMATION, $table_data );
			$this->load_from_id( (int) $new_id );

			//Configure teams, mentors and students for this person
			$this->_set_teams( $teams );
			$this->_set_mentors( $mentors );
			$this->_set_students( $students );
		}
	}
}
