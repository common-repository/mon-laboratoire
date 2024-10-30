<?php
namespace MonLabo\Lib\Access_Data;
use MonLabo\Lib\{App};

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

/*
class Access_Data {
	// Get persons methods
	get_persons_info ( $status = 'actif' )
	get_teams_members_info ()
	get_persons_info_for_a_team ( $team_id, $status = 'actif' )
	get_persons_info_for_an_unit ( $unit_id, $status = 'actif' )
	get_persons_info_for_a_category_in_a_team ( $category, $team, $status = 'actif' )
	get_persons_id_for_a_team ( $team_id, $status = 'actif' )
	get_multilingual_functions_by_category ()
	get_persons_titles ()

	// Get persons with special functions methods
	get_mentors_id_for_a_person ( $person_id, $status = 'actif' )
	get_mentors_info_for_a_person ( $team, $status = 'actif' )
	get_students_id_for_a_person ( $person_id, $status = 'actif' )
	get_students_info_for_a_person ( $team, $status = 'actif' )
	get_leaders_id_for_a_team ( $team_id, $status = 'actif' )
	get_leaders_info_for_a_team ( $team, $status = 'actif' )
	get_non_leaders_info_for_a_team ( $team, $status = 'actif' )
	get_directors_info ( $status = 'actif' )
	get_directors_id_for_an_unit ( $unit_id, $status = 'actif' )
	get_units_id_for_a_director ( $person_id, $status = 'actif' )

	// Get teams methods
	get_teams_info ()
	get_teams_info_for_an_unit ( $unit_id )
	get_teams_id_for_an_unit ( $unit_id )
	get_teams_info_for_a_person ( $person_id )
	get_teams_name ( $lang )
	get_teams_name_for_a_person ( $person_id, $lang )
	get_teams_id_for_a_person ( $person_id )
	get_teams_id_for_a_thematic ( $thematic_id )
	get_teams_id_for_a_leader( $person_id, $status = 'actif' )

	// Get thematics methods
	get_thematics_info ()
	get_thematics_name ( $lang )
	get_thematics_name_for_a_team ( $team_id, $lang )
	get_thematics_id_for_a_team ( $team_id )
	get_thematics_info_for_a_team ( $team_id )

	// Get units methods
	get_units_info ()
	get_unit_id_from_code ( $code )
	get_units_name ( $lang )

	// MUTATOR METHODS
	  -------------------------------------------------------------
	* Insert Methods
	add_mentor_to_a_person ( $mentor_id, $person_id )
	add_person_to_a_team ( $person_id, $team_id )
	add_leader_to_a_team ( $leader_id, $team_id )
	add_thematic_to_a_team ( $thematic_id, $team_id )
	add_director_to_an_unit ( $director_id, $unit_id )
	remove_director_from_an_unit ( $director_id, $unit_id )

	//Delete methods
	delete_relations_between_teams_and_a_unit( $unit_id, $teams )
	delete_relations_between_teams_and_a_person ( $person_id, $teams )
	delete_relations_between_a_person_and_his_or_her_mentors ( $person_id )
	delete_relations_between_a_person_and_his_or_her_students ( $person_id )
	delete_relations_between_persons_and_a_team ( $team_id )
	delete_relations_between_thematics_and_a_team ( $team_id )
	delete_relations_between_leaders_and_a_team ( $team_id )
	delete_relations_between_teams_and_a_thematic ( $thematic_id )
	delete_relations_between_directors_and_a_unit ( $unit_id )
	delete_relations_between_units_and_a_director( $person_id )
}
*/

 /**
  * Class \MonLabo\Lib\Access_Data\Access_Data
 * @package
 */
class Access_Data extends Access_Generic {

	/*******************************************************************
	 * Persons get Methods
	*******************************************************************/
	/**
	 * Get persons information ( get all the table of user information )
	 * @param string $status actif or alumni
	 * @return array<int,\stdclass> array( $person_id => array( information_name => information_value ) )
	 */
	public function get_persons_info( string $status = 'actif' ): array {
		$sql = 'SELECT * FROM ' . $this->_db_prefix . 'MonLabo_persons ' . $this->_prepare_condition_sql_from_status( $status ) . ' ORDER BY last_name ASC, first_name ASC';
		$table = $this->wpdb->get_results( $sql, OBJECT );
		return $this->_tidy_object_table_by_id( $table );
	}

	/**
	 * Get teams member relation table ( get all the table )
	 * @return array<int,\stdclass> array( $person_id => array( information_name => information_value ) )
	 */
	public function get_teams_members_info( ): array {
		$sql = 'SELECT * FROM ' . $this->_db_prefix . 'MonLabo_teams_members ORDER BY id_person ASC, id_team ASC';
		$table = $this->wpdb->get_results( $sql, OBJECT );
		return $table;
	}

	/**
	 * Get persons information of a team
	 * @param int $team_id id of the team
	 * @param string $status actif or alumni
	 * @return array<int,\stdclass> array( $person_id = > array( information_name => information_value ) )
	 */
	public function get_persons_info_for_a_team( int $team_id, string $status = 'actif' ): array {
		$all_persons = $this->_get_step_table_columns(
							/* union_table_name	*/ 		$this->_db_prefix . 'MonLabo_teams_members',
							/* step_table_name */ 		$this->_db_prefix . 'MonLabo_persons',
							/* union_table_columns */ 	array( 'id_team', 'id_person' ),
							/* step_table_columns */ 	array( '*' ),
							$team_id,
							/* $order_by */ 			array( 'last_name', 'first_name' ),
							/*$add_union_condition */ 	array(),
							/*$add_step_condition  */ 	$this->_prepare_condition_array_from_status( $status )
						);
		return $this->_tidy_object_table_by_id( $all_persons );
	}

	/**
	 * Get persons information of an unit
	 * @param int $unit_id id of the unit
	 * @param string $status actif or alumni
	 * @return object[] array( $person_id=> array( information_name => information_value ) )
	 */
	public function get_persons_info_for_an_unit( int $unit_id, string $status = 'actif' ): array  {
		$teams_id = $this->get_teams_id_for_an_unit( $unit_id );
		$all_persons = array();
		//We already know that $teams_id is an array. A test is not usefull.
		foreach ( $teams_id as $team_id ) {
			$persons = $this->get_persons_info_for_a_team( (int) $team_id, $status );
			foreach ( $persons as $person_id => $person_info ) {
				$all_persons[ $person_id ] = $person_info;
			}
		}
		return $all_persons;
	}

	/**
	 * Get persons information of a category in a team
	 * @param string $categories categories of the person (ex: "cat1,cat2,cat3")
	 * @param int $team_id team ID of the person
	 * @param string $status status of the persons to select ( by default $status = 'actif' )
	 * @return array<int,\stdclass> array( $person_id=> array( information_name => information_value ) )
	 */
	public function get_persons_info_for_a_category_in_a_team( string $categories, int $team_id, string $status = 'actif' ): array  {
		$add_step_condition = $this->_prepare_condition_array_from_status( $status );
		$add_step_condition['category'] = $categories;
		$all_persons = $this->_get_step_table_columns(
								/* union_table_name */		$this->_db_prefix . 'MonLabo_teams_members',
								/* step_table_name */		$this->_db_prefix . 'MonLabo_persons',
								/* union_table_columns */ 	array( 'id_team', 'id_person' ),
								/* step_table_columns */ 	array( '*' ),
								$team_id,
								/* order_by */ 				array( 'last_name', 'first_name' ),
								/* add_union_condition */ 	array(),
								$add_step_condition );
		return $this->_tidy_object_table_by_id( $all_persons );
	}

	/**
	 * Get persons id for a team
	 * @param int $team_id id of the team
	 * @param string $status status of the persons to select ( by default $status = 'actif' )
	 * @return array<int,int> array( person_id => person_id )
	 */
	public function get_persons_id_for_a_team( int $team_id, string $status = 'actif' ): array {
		$persons = $this->_get_step_table_columns(
							/* union_table_name */  	$this->_db_prefix . 'MonLabo_teams_members',
							/* step_table_name */  		$this->_db_prefix . 'MonLabo_persons',
							/* union_table_columns */ 	array( 'id_team', 'id_person' ),
							/* step_table_columns */	array( 'id' ),
							$team_id,
							/* order_by */ 				array( 'id' ),
							/* add_union_condition */ 	array(),
							/* add_step_condition */ 	$this->_prepare_condition_array_from_status( $status ) );
		return $this->_tidy_object_table_and_leave_only_one_column( $persons, 'id', 'id', 'int' );
	}


	/*******************************************************************
	 * Special persons get Methods
	******************************************************************/

	/**
	 * Get mentors id for a person
	 * @param int $person_id id of the person
	 * @param string $status status of the mentors to select ( by default $status = 'actif' )
	 * @return array<int,int> array( mentor_id => mentor_id )
	 */
	public function get_mentors_id_for_a_person( int $person_id, string $status = 'actif' ): array {
		$mentors = $this->_get_step_table_columns(
							/* union_table_name */  $this->_db_prefix . 'MonLabo_mentors',
							/* step_table_name */  $this->_db_prefix . 'MonLabo_persons',
							/* union_table_columns */ array( 'id_person_student', 'id_person_supervisor' ),
							/* step_table_columns */ array( 'id' ),
							$person_id,
							/* order_by */ array( 'id' ),
							/* add_union_condition */ array(),
							/* add_step_condition */ $this->_prepare_condition_array_from_status( $status ) );
		return $this->_tidy_object_table_and_leave_only_one_column( $mentors, 'id', 'id', 'int' );
	}

	/**
	 * Get mentors information for a person
	 * @param int $person_id id of the person
	 * @param string $status status of the students to select ( by default $status = 'actif' )
	 * @return array<int,\stdclass> array( information_name => information_value )
	 */
	public function get_mentors_info_for_a_person( int $person_id, string $status = 'actif' ): array {
		$mentors_info = $this->_get_step_table_columns(
						/* union_table_name */  $this->_db_prefix . 'MonLabo_mentors',
						/* step_table_name */  $this->_db_prefix . 'MonLabo_persons',
						/* union_table_columns */ array( 'id_person_student', 'id_person_supervisor' ),
						/* step_table_columns */ array( '*' ),
						$person_id,
						/* order_by */ array( 'last_name', 'first_name' ),
						/* add_union_condition */ array(),
						/* add_step_condition */ $this->_prepare_condition_array_from_status( $status ) );
		return $this->_tidy_object_table_by_id( $mentors_info );
	}

	/**
	 * Get students id for a person
	 * @param int $person_id id of the person
	 * @param string $status status of the students to select ( by default $status = 'actif' )
	 * @return array<int,int> array( student_id => student_id )
	 */
	public function get_students_id_for_a_person( int $person_id, string $status = 'actif' ): array {
		$students = $this->_get_step_table_columns(
							/* union_table_name */  $this->_db_prefix . 'MonLabo_mentors',
							/* step_table_name */  $this->_db_prefix . 'MonLabo_persons',
							/* union_table_columns */ array( 'id_person_supervisor', 'id_person_student' ),
							/* step_table_columns */ array( 'id' ),
							$person_id,
							/* order_by */ array( 'id' ),
							/* add_union_condition */ array(),
							/* add_step_condition */ $this->_prepare_condition_array_from_status( $status ) );
		return $this->_tidy_object_table_and_leave_only_one_column( $students, 'id', 'id', 'int' );
	}

	/**
	 * Get students information for a person
	 * @param int $person_id id of the person
	 * @param string $status status of the students to select ( by default $status = 'actif' )
	 * @return array<int,\stdclass> array( information_name => information_value )
	 */
	public function get_students_info_for_a_person( int $person_id, string $status = 'actif' ): array {
		$students_info = $this->_get_step_table_columns(
							/* union_table_name */  $this->_db_prefix . 'MonLabo_mentors',
							/* step_table_name */  $this->_db_prefix . 'MonLabo_persons',
							/* union_table_columns */ array( 'id_person_supervisor', 'id_person_student' ),
							/* step_table_columns */ array( '*' ),
							$person_id,
							/* order_by */ array( 'last_name', 'first_name' ),
							/* add_union_condition */ array(),
							/* add_step_condition */ $this->_prepare_condition_array_from_status( $status ) );
		return $this->_tidy_object_table_by_id( $students_info );
	}

	/**
	 * Get leaders id for a team
	 * @param int $team_id id of the team
	 * @param string $status status of the leaders to select ( by default $status = 'actif' )
	 * @return array<int,int> array( leader_id => leader_id )
	 */
	public function get_leaders_id_for_a_team( int $team_id, string $status = 'actif' ): array {
		$leaders = $this->_get_step_table_columns(
							/* union_table_name */ $this->_db_prefix . 'MonLabo_teams_members',
							/* step_table_name */ $this->_db_prefix . 'MonLabo_persons',
							/* union_table_columns */ array( 'id_team', 'id_person' ),
							/* step_table_columns */ array( 'id' ),
							$team_id,
							/* order_by */ array( 'id' ),
							/* add_union_condition */ array( 'directing' => '1' ),
							/* add_step_condition */ $this->_prepare_condition_array_from_status( $status ) );
		return $this->_tidy_object_table_and_leave_only_one_column( $leaders, 'id', 'id', 'int' );
	}

	/**
	 * Get leaders information for a team
	 * @param int $team_id id of the team
	 * @param string $status status of the persons to select ( by default $status = 'actif' )
	 * @return array<int,\stdclass> array( information_name => information_value )
	 */
	public function get_leaders_info_for_a_team( int $team_id, string $status = 'actif' ): array {
		$leaders_info = $this->_get_step_table_columns(
							/* union_table_name */ $this->_db_prefix . 'MonLabo_teams_members',
							/* step_table_name */ $this->_db_prefix . 'MonLabo_persons',
							/* union_table_columns */ array( 'id_team', 'id_person' ),
							/* step_table_columns */ array( '*' ),
							$team_id,
							/* order_by */ array( 'last_name', 'first_name' ),
							/* add_union_condition */ array( 'directing' => '1' ),
							/* add_step_condition */ $this->_prepare_condition_array_from_status( $status ) );
		return $this->_tidy_object_table_by_id( $leaders_info );
	}

	/**
	 * Get directors information (only used for test)
	 * @param string $status status of the persons to select ( by default $status = 'actif' )
	 * @return array<int,\stdclass> array( information_name => information_value )
	 */
	public function get_directors_info( string $status = 'actif' ): array { // @phan-suppress-current-line PhanUnreferencedPublicMethod
		$condition = $this->_prepare_condition_sql_from_status( $status );
		$condition .= ( ( '' === $condition ) ? 'WHERE u.id_person = s.id ' : ' AND u.id_person = s.id ' );
		$sql = 'SELECT s.* FROM ' . $this->_db_prefix . 'MonLabo_persons AS s, ' . $this->_db_prefix . 'MonLabo_units_directors AS u ' . $condition . ' ORDER BY last_name ASC, first_name ASC';
		$directors_info = $this->wpdb->get_results( $sql, OBJECT );
		return $this->_tidy_object_table_by_id( $directors_info ); // Tidy output by id
	}

	/**
	  * Get directors information for an unit
	  * @param int $unit_id id of the unit
	  * @param string $status status of the persons to select ( by default $status = 'actif' )
	  * @return array<int,\stdclass> array( information_name => information_value )
	  */
	public function get_directors_info_for_an_unit( int $unit_id, string $status = 'actif' ): array {
		$retval = array();
		if ( ! empty( $unit_id ) ) {
			$directors_info = $this->_get_step_table_columns(
											/* union_table_name */ $this->_db_prefix . 'MonLabo_units_directors',
											/* step_table_name */ $this->_db_prefix . 'MonLabo_persons',
											/* union_table_columns */ array( 'id_unit', 'id_person' ),
											/* step_table_columns */ array( '*' ),
											$unit_id,
											/* order_by */ array( 'last_name', 'first_name' ),
											/* add_union_condition */ array(),
											/* add_step_condition */ $this->_prepare_condition_array_from_status( $status ) );
			$retval = $this->_tidy_object_table_by_id( $directors_info );
		}
		return $retval;
	}

	/**
	 * Get directors id for an unit
	 * @param int $unit_id id of the unit
	 * @param string $status status of the directors to select ( by default $status = 'actif' )
	 * @return array<int,int> array( director_id => ( director_first_name DIRECTOR_LAST_NAME ) )
	 * @since 1.0.0
	 */
	public function get_directors_id_for_an_unit( int $unit_id, string $status = 'actif' ): array {
		$retval = array();
		if ( ! empty( $unit_id ) ) {
			$directors = $this->_get_step_table_columns(
											/* union_table_name */ $this->_db_prefix . 'MonLabo_units_directors',
											/* step_table_name */ $this->_db_prefix . 'MonLabo_persons',
											/* union_table_columns */ array( 'id_unit', 'id_person' ),
											/* step_table_columns */ array( 'id' ),
											$unit_id,
											/* order_by */ array( 'id_unit' ),
											/* add_union_condition */ array(),
											/* add_step_condition */ $this->_prepare_condition_array_from_status( $status ) );
			$retval = $this->_tidy_object_table_and_leave_only_one_column( $directors, 'id', 'id', 'int' );
		}
		return $retval;
	}

	/**
	 * Get units id for a director
	 * @param int $person_id id of the director
	 * @param string $status status of the director to select ( by default $status = 'actif' )
	 * @return array<int,int> array( unit_id => unit_id )
	 */
	public function get_units_id_for_a_director( int $person_id, string $status = 'actif' ): array {
		$status_string = ''; // $status = 'all'
		if ( ( 'actif' === $status ) or ( 'alumni' === $status ) ) {
			$status_string = 'AND ' . $this->_db_prefix . 'MonLabo_persons.status = "' . $status . '" ';
		}
		$sql = $this->wpdb->prepare( 'SELECT ' . $this->_db_prefix . 'MonLabo_units_directors.id_unit FROM ' . $this->_db_prefix . 'MonLabo_units_directors '
			 . 'LEFT JOIN ' . $this->_db_prefix . 'MonLabo_persons ON ' . $this->_db_prefix . 'MonLabo_units_directors.id_person = ' . $this->_db_prefix . 'MonLabo_persons.id '
			 . ' WHERE ' . $this->_db_prefix . 'MonLabo_units_directors.id_person = "%d" '
			 . $status_string
			 . 'ORDER BY id_unit ASC', $person_id );
		$units = $this->wpdb->get_results( $sql, OBJECT );
		return $this->_tidy_object_table_and_leave_only_one_column( $units, 'id_unit', 'id_unit', 'int' );
	}

	/*******************************************************************
	 * Get teams Methods
	******************************************************************/

	/**
	 * Get teams information ( get all the table of team information )
	 * @return array<int,\stdclass> array( $team_id=> array( information_name => information_value ) )
	 */
	public function get_teams_info(): array {
		$sql = 'SELECT * FROM ' . $this->_db_prefix . 'MonLabo_teams ORDER BY name_fr ASC, id_unit ASC';
		$table = $this->wpdb->get_results( $sql, OBJECT );
		$all_teams = $this->_tidy_object_table_by_id( $table ); //Tidy output by id
		asort( $all_teams ); //sort teams by order of enter in database.
		return $all_teams;
	}

	/**
	 * Get teams id
	 * @param int $id_unit id of the unit
	 * @return array<int,int> array( $team_id => array( information_name => information_value ) )
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	public function get_teams_id_for_an_unit( int $id_unit ): array {
		if ( App::MAIN_STRUCT_NO_UNIT === $id_unit ) {
			$sql = $this->wpdb->prepare( "SELECT id FROM " . $this->_db_prefix . "MonLabo_teams  AS u WHERE u.id_unit = \"%d\" OR u.id_unit IS NULL", App::MAIN_STRUCT_NO_UNIT );
		} else {
			$sql = $this->wpdb->prepare( "SELECT id FROM " . $this->_db_prefix . "MonLabo_teams  AS u WHERE u.id_unit = \"%d\"", $id_unit );
		}
		$teams = $this->wpdb->get_results( $sql, OBJECT_K );
		return $this->_tidy_object_table_and_leave_only_one_column( $teams, 'id', 'id', 'int' );
	}

	/**
	 * Get all teams name
	 * @param string $lang language of the teams name ( fr or en )
	 * @return array<int,string> array( team_id => team_name )
	 */
	public function get_teams_name( string $lang = 'fr' ): array {
		$name_column = $this->_column_name_for_language( $lang );
		$sql = 'SELECT id, '. $name_column .' FROM ' . $this->_db_prefix . 'MonLabo_teams ORDER BY ' . $name_column . ' ASC';
		$teams = $this->wpdb->get_results( $sql, OBJECT_K );
		return $this->_tidy_object_table_and_leave_only_one_column( $teams, 'id', $name_column );
	}

	/**
	 * Get teams name for a person
	 * @param int $person_id id of the person
	 * @param string $lang language of the thematic name ( fr or en )
	 * @return array<int,string> array( team_id => team_name )
	 */
	public function get_teams_name_for_a_person( int $person_id, string $lang = 'fr' ): array {
		$column = $this->_column_name_for_language( $lang );
		$teams = $this->_get_step_table_columns(
							/* union_table_name	*/		$this->_db_prefix . 'MonLabo_teams_members',
							/* step_table_name */		$this->_db_prefix . 'MonLabo_teams',
							/* union_table_columns */ 	array( 'id_person', 'id_team' ),
							/* step_table_columns */ 	array( 'id', $column ),
							$person_id,
							/* order_by */				array( $column ) );
		return $this->_tidy_object_table_and_leave_only_one_column( $teams, 'id', $column );
	}

	/**
	 * Get teams id for a person
	 * @param int $person_id id of the person
	 * @return array<int,int> array( team_id => team_id )
	 */
	public function get_teams_id_for_a_person( int $person_id ): array  {
		$teams = $this->_get_step_table_columns(
							/* union_table_name */ 		$this->_db_prefix . 'MonLabo_teams_members',
							/* step_table_name */ 		$this->_db_prefix . 'MonLabo_teams',
							/* union_table_columns */ 	array( 'id_person', 'id_team' ),
							/* step_table_columns */ 	array( 'id' ),
							$person_id,
							/* order_by */ 				array( 'id' ) );
		return $this->_tidy_object_table_and_leave_only_one_column( $teams, 'id', 'id', 'int' );
	}

	/**
	 * Get teams id for a person
	 * @param int $person_id id of the person
	 * @return array<int,\stdclass> array( team_id => team_id )
	 */
	public function get_teams_info_for_a_person( int $person_id ): array {
		$teams_info = $this->_get_step_table_columns(
							/* union_table_name */ 		$this->_db_prefix . 'MonLabo_teams_members',
							/* step_table_name */ 		$this->_db_prefix . 'MonLabo_teams',
							/* union_table_columns */ 	array( 'id_person', 'id_team' ),
							/* step_table_columns */ 	array( '*' ),
							$person_id,
							/* order_by */ 				array( 'name_fr' ) );
		return $this->_tidy_object_table_by_id( $teams_info );
	}

	/**
	 * Get unit id for a code
	 * @param string $code id of the page
	 * @return int|null unit_id
	 * null if no result is found
	 */
	public function get_unit_id_from_code( string $code ) {
		$sql = $this->wpdb->prepare( "SELECT id FROM " . $this->_db_prefix . "MonLabo_units p WHERE code = %s", $code );
		$retval = $this->_first_key_of_array( $this->wpdb->get_results( $sql, OBJECT_K ) );
		if ( ! is_null( $retval ) ) { $retval = intval( $retval ); }
		return $retval;
	}

	/**
	 * Get teams information for a thematic
	 * @param int $thematic_id id of the thematic
	 * @return array<int,\stdclass> array( $team_id => array( information_name => information_value ) )
	 */
	public function get_teams_info_for_a_thematic( int $thematic_id ): array {
		$teams_info = $this->_get_step_table_columns(
							/* union_table_name */ 		$this->_db_prefix . 'MonLabo_teams_thematics',
							/* step_table_name */ 		$this->_db_prefix . 'MonLabo_teams',
							/* union_table_columns */ 	array( 'id_thematic', 'id_team' ),
							/* step_table_columns */ 	array( '*' ),
							$thematic_id,
							/* order_by */ 				array( 'name_fr' ) );
		return $this->_tidy_object_table_by_id( $teams_info );
	}

	/**
	 * Get teams id for a thematic
	 * @param int $thematic_id id of the thematic
	 * @return array<int,int> array( team_id => team_id )
	 */
	public function get_teams_id_for_a_thematic( int $thematic_id ): array {
		$teams = $this->_get_step_table_columns(
						/* union_table_name */ 		$this->_db_prefix . 'MonLabo_teams_thematics',
						/* $step_table_name */ 		$this->_db_prefix . 'MonLabo_teams',
						/* $union_table_columns */ 	array( 'id_thematic', 'id_team' ),
						/* step_table_columns */ 	array( 'id' ),
						$thematic_id,
						/* $order_by */ 			array( 'name_fr' ) );
		return $this->_tidy_object_table_and_leave_only_one_column( $teams, 'id', 'id', 'int' );
	}

	/**
	 * Get teams id for a leader
	 * @param int $person_id id of the team_leader
	 * @param string $status status of the team leader to select ( by default $status = 'actif' )
	 * @return array<int,int> array( unit_id => unit_id )
	 */
	public function get_teams_id_for_a_leader( int $person_id, string $status = 'actif' ): array {
		$status_string = '';  // $status = 'all'
		if ( ( 'actif' === $status ) or ( 'alumni' === $status ) ) {
			$status_string = 'AND ' . $this->_db_prefix . 'MonLabo_persons.status = "' . $status . '" ';
		}
		$sql = $this->wpdb->prepare( 'SELECT ' . $this->_db_prefix . 'MonLabo_teams_members.id_team FROM ' . $this->_db_prefix . 'MonLabo_teams_members '
			 . 'LEFT JOIN ' . $this->_db_prefix . 'MonLabo_persons ON ' . $this->_db_prefix . 'MonLabo_teams_members.id_person = ' . $this->_db_prefix . 'MonLabo_persons.id '
			 . ' WHERE ' . $this->_db_prefix . 'MonLabo_teams_members.id_person = "%d" AND ' . $this->_db_prefix . 'MonLabo_teams_members.directing = 1 '
			 . $status_string
			 . 'ORDER BY id_team ASC', $person_id );
		$teams = $this->wpdb->get_results( $sql, OBJECT );
		return $this->_tidy_object_table_and_leave_only_one_column( $teams, 'id_team', 'id_team', 'int' );
	}

	/*******************************************************************
	 * Get thematics Methods
	******************************************************************/

	/**
	 * Get thematics information ( get all the table of thematic information )
	 * @return array<int,\stdclass> array( $team_id => array( information_name => information_value ) )
	 */
	public function get_thematics_info(): array {
		$table = $this->wpdb->get_results( 'SELECT * FROM ' . $this->_db_prefix . 'MonLabo_thematics ORDER BY name_fr ASC', OBJECT );
		return $this->_tidy_object_table_by_id( $table ); //Tidy output by id
	}

	/**
	 * Get all thematics name
	 * @param string $lang language of the thematics name ( fr or en )
	 * @return array<int,string> array( thematic_id => thematic_name )
	 */
	public function get_thematics_name( string $lang = 'fr' ): array {
		$name_column = $this->_column_name_for_language( $lang );
		$sql = 'SELECT id, '. $name_column .' FROM ' . $this->_db_prefix . 'MonLabo_thematics ORDER BY ' . $name_column . ' ASC';
		$thematics = $this->wpdb->get_results( $sql, OBJECT_K );
		return $this->_tidy_object_table_and_leave_only_one_column( $thematics, 'id', $name_column );
	}

	/**
	 * Get thematics information for a team
	 * @param int $team_id id of the team
	 * @return array<int,\stdclass> array( thematic_id => ( thematic_info_name => thematic_info_value ) )
	 */
	public function get_thematics_info_for_a_team( int $team_id ): array {
		$thematics_info = $this->_get_step_table_columns(
								/* $union_table_name */ 	$this->_db_prefix . 'MonLabo_teams_thematics',
								/* step_table_name */ 		$this->_db_prefix . 'MonLabo_thematics',
								/* union_table_columns */ 	array( 'id_team', 'id_thematic' ),
								/* step_table_columns */ 	array( '*' ),
								$team_id,
								/* order_by */ 				array( 'name_fr' ) );
		$table = $this->_tidy_object_table_by_id( $thematics_info );
		ksort( $table );
		return $table;
	}

	/**
	 * Get thematics id for a team
	 * @param int $team_id id of the team
	 * @return array<int,int> array( thematic_id => thematic_id )
	 */
	public function get_thematics_id_for_a_team( int $team_id ): array {
		$thematics = $this->_get_step_table_columns(
							/* union_table_name */ 		$this->_db_prefix . 'MonLabo_teams_thematics',
							/* step_table_name */ 		$this->_db_prefix . 'MonLabo_thematics',
							/* union_table_columns */	array( 'id_team', 'id_thematic' ),
							/* step_table_columns */ 	array( 'id' ),
							$team_id,
							/* $order_by */ 			array( 'id' ) );
		return $this->_tidy_object_table_and_leave_only_one_column( $thematics, 'id', 'id', 'int' );
	}

	/*******************************************************************
	 * Get units Methods
	******************************************************************/
	/**
	 * Get units information ( get all the table of unit information )
	 * @return array<int,\stdclass> array( $team_id => array( information_name => information_value ) )
	 */
	public function get_units_info(): array {
		$table = $this->wpdb->get_results( 'SELECT * FROM ' . $this->_db_prefix . 'MonLabo_units ORDER BY code ASC', OBJECT );
		return $this->_tidy_object_table_by_id( $table ); //Tidy output by id
	}

	/**
	 * Get all units name
	 * @param string $lang language of the units name ( fr or en )
	 * @return array<int,string> array( unit_id => unit_name )
	 */
	public function get_units_name( string $lang = 'fr' ): array {
		$name_column = $this->_column_name_for_language( $lang );
		$sql = 'SELECT id, '. $name_column .' FROM ' . $this->_db_prefix . 'MonLabo_units ORDER BY ' . $name_column . ' ASC';
		$units = $this->wpdb->get_results( $sql, OBJECT_K );
		return $this->_tidy_object_table_and_leave_only_one_column( $units, 'id', $name_column );
	}

	/*******************************************************************
	 * Other Methods
	 ******************************************************************/

	/**
	 * Get all already given functions tydied by category
	 *
	 * @return non-empty-array<string,string[]>
	 * 					array( 'categoryA' => array(
	 *											'categoryA | function1_en | function1_fr' => 'function1_en | function1_fr' ),
	 *											'categoryA | function2_en | function2_fr' => 'function2_en | function2_fr' ),
	 *											...
	 *										 )
	 *					 'categoryB' => array( ... ),
	 *					 ...
	 *					)
	 */
	public function get_multilingual_functions_by_category(): array {
		//------------------------------------------------------------------------------------------------------
		//1 - Récupère les triplets uniques ( category, function_en, function_fr ) déjà utilisés par des personnes
		//------------------------------------------------------------------------------------------------------
		$sql = 'SELECT ' . $this->_db_prefix . 'MonLabo_persons.category, ' . $this->_db_prefix . 'MonLabo_persons.function_en,  ' . $this->_db_prefix . 'MonLabo_persons.function_fr '
			 . 'FROM ' . $this->_db_prefix . 'MonLabo_persons '
			 . 'GROUP BY CONCAT(category, function_en, function_fr) '
			 . 'ORDER BY category ASC, function_en ASC, function_fr ASC';
		$functions = $this->wpdb->get_results( $sql, OBJECT );
		if ( ! isset( $functions ) ) {
			return App::get_MonLabo_MembersFunctionsByCategory_default(); // @codeCoverageIgnore
		}
		//------------------------------------------------
		//2 - Ranger les fonctions déjà utilisées par catégories
		//------------------------------------------------
		$multi_functions = array();
		foreach ( (array) $functions as $function ) {
			$category_lowercase = mb_strtolower( $function->category, 'UTF-8' ); //On converti la catégorie en minuscule ( pour une compatibilité avec les anciennes versions de MonLabo )
			if ( ! array_key_exists( $category_lowercase, $multi_functions ) ) {
				$multi_functions[ $category_lowercase ]= array();
			}
			array_push( $multi_functions[ $category_lowercase ], $function->function_en . ' | ' . $function->function_fr );
		}
		//------------------------------------------------
		//3 - Les fusionner avec les fonctions par défaut.
		//------------------------------------------------
		foreach ( App::get_MonLabo_MembersFunctionsByCategory_default() as $category => $cat_functions ) {
			if ( ! array_key_exists( $category, $multi_functions ) ) {
				$multi_functions[ $category ] = array();
			}
			$multi_functions[ $category ] = array_merge( $cat_functions, $multi_functions[ $category ] );
			foreach ( $multi_functions[ $category ] as $old_key => $value ) {
				$new_key = "$category | " . $value;
				$multi_functions[ $category ][ $new_key ] = $value;
				unset( $multi_functions[ $category ][ $old_key ] );
			}
			//$multi_functions[ $category ] = array_combine( $multi_functions[ $category ], $multi_functions[ $category ] );
			asort( $multi_functions[ $category ] );
		}
		return $multi_functions;
	}

	/**
	 * Get all already given person titles
	 * @return non-empty-array<string,string>
	 */
	public function get_persons_titles(): array {
		//------------------------------------------------------------------------------------------------------
		//1 - Récupère les triplets uniques ( category, function_en, function_fr ) déjà utilisés par des personnes
		//------------------------------------------------------------------------------------------------------
		$sql = 'SELECT DISTINCT ' . $this->_db_prefix . 'MonLabo_persons.title '
			 . 'FROM ' . $this->_db_prefix . 'MonLabo_persons '
			 . 'ORDER BY title ASC';
		$titles_sql = $this->wpdb->get_results( $sql, OBJECT_K );
		$titles = array();
		foreach ($titles_sql as $key => $value ) {
			if ( property_exists( $value, 'title' ) ) { //In theory always enter in
				if ( '' === $key ) {
					$key = 'none';
				}
				$titles[strval( $key )] = strval( $value->title );
			}
		}
		if ( empty( $titles ) ) {
			return App::get_MonLabo_MembersTitles_default(); // @codeCoverageIgnore
		}
		//------------------------------------------------
		//2 - Les fusionner avec les titres par défaut.
		//------------------------------------------------
		foreach ( App::get_MonLabo_MembersTitles_default() as $key => $value ) {
			$titles[ $key ] = $value;
		}
		asort( $titles );
		return $titles;
	}

	////////////////////////////////////////////////////////////////////
	// MUTATOR METHODS
	////////////////////////////////////////////////////////////////////

	/*******************************************************************
	 * Insert Methods
	 ******************************************************************/
	/**
	 * Add a mentor to a person
	 * @param int $new_mentor_id
	 * @param int $person_id
	 * @return void
	 *
	 */
	public function add_mentor_to_a_person( int $new_mentor_id, int $person_id ) {
		//Test that persons are reals
		$person_info = $this->get_info( 'person',  $person_id );
		$mentor_info = $this->get_info( 'person',  $new_mentor_id );
		if ( ( ! empty( $person_info ) ) and ( ! empty( $mentor_info ) )  ) {
			$person_mentors_id = $this->get_mentors_id_for_a_person( $person_id );
			//If not yet done anf mentor is a real person
			if ( ( empty( $person_mentors_id ) ) or ( ! in_array( $new_mentor_id, $person_mentors_id ) ) ) {
				$data = array( 'id_person_supervisor' => $new_mentor_id, 'id_person_student' => $person_id );
				$this->wpdb->insert( $this->_db_prefix . 'MonLabo_mentors', $data, array( '%d', '%d' ) );
			}
		}
	}

	/**
	 * Add a person to a team
	 * @param int $person_id
	 * @param int $team_id
	 * @return void
	 *
	 */
	public function add_person_to_a_team( int $person_id, int $team_id ) {
		//Test if team and person are real
		$person_info = $this->get_info( 'person',  $person_id );
		$team_info = $this->get_info( 'team',  $team_id );
		if ( ( ! empty( $person_info ) ) and ( ! empty( $team_info ) )  ) {
			$person_teams_id = $this->get_teams_id_for_a_person( $person_id );
			if ( ( empty( $person_teams_id ) ) or ( ! in_array( $team_id, $person_teams_id ) ) ) {
				$data = array( 'directing'=>0, 'id_person'=>$person_id, 'id_team'=>$team_id );
				$this->wpdb->insert( $this->_db_prefix . 'MonLabo_teams_members', $data , array( '%d', '%d', '%d' ) );
			}
		}
	}

	/**
	 * Add a leader to a team
	 * @param int $leader_id ID of the person that is a leader
	 * @param int $team_id
	 * @return void
	 *
	 */
	public function add_leader_to_a_team( int $leader_id, int $team_id ) {
		//if team and person are reals
		$leader_info = $this->get_info( 'person',  $leader_id );
		$team_info = $this->get_info( 'team',  $team_id );
		if ( ( ! empty( $leader_info ) ) and ( ! empty( $team_info ) ) ) {
			$members = $this->get_persons_id_for_a_team( $team_id );
			if ( ( ! empty( $members ) ) and ( in_array( $leader_id, $members ) ) ) {
				//Si la personne est déjà membre de l'équipe, la passe en leader
				$data = array( 'directing' => 1 );
				$where = array( 'id_person' => $leader_id, 'id_team' => $team_id );
				$this->wpdb->update( $this->_db_prefix . 'MonLabo_teams_members', $data, $where, array( '%d'), array( '%d', '%d' )   );
				return;
			}
			//Sinon on l'ajoute comme leader à l'équipe
			$data = array( 'id_person' => $leader_id, 'id_team' => $team_id, 'directing' => 1 );
			$this->wpdb->insert( $this->_db_prefix . 'MonLabo_teams_members', $data, array( '%d', '%d', '%d' ) );
		}
	}

	/**
	 * Add a thematic to a team
	 * @param int $thematic_id
	 * @param int $team_id
	 * @return bool true if succeed, false elsewhere
	 *
	 */
	public function add_thematic_to_a_team( int $thematic_id, int $team_id ): bool {
		//if team and thematic are reals
		$thematic_info = $this->get_info( 'thematic',  $thematic_id );
		$team_info = $this->get_info( 'team',  $team_id );
		if ( ( ! empty( $thematic_info ) ) and ( ! empty( $team_info ) ) ) {
			$team_thematics_id = $this->get_thematics_id_for_a_team( $team_id );
			if ( ( empty( $team_thematics_id ) ) or ( ! in_array( $thematic_id, $team_thematics_id ) ) ) {
				$data = array( 'id_thematic' => $thematic_id, 'id_team' => $team_id );
				$this->wpdb->insert( $this->_db_prefix . 'MonLabo_teams_thematics', $data, array( '%d', '%d' ) );
			}
			return true;
		}
		return false;
	}

	/**
	 * Add a director to an unit
	 * @param int $director_id
	 * @param int $unit_id
	 * @return bool true if succeed, false elsewhere
	 *
	 */
	public function add_director_to_an_unit( int $director_id, int $unit_id ): bool {
		//if director person and unit are reals
		$director_info = $this->get_info( 'person',  $director_id );
		if ( ( ! empty( $director_info ) )
				and ( ( App::MAIN_STRUCT_NO_UNIT === $unit_id )
					or ! empty( $this->get_info( 'unit',  $unit_id ) )
					)
			) {
			$unit_directors_id = $this->get_directors_id_for_an_unit( $unit_id );
			if ( ( empty( $unit_directors_id ) ) or ( ! in_array( $director_id, $unit_directors_id ) ) ) {
				$data = array( 'id_person' => $director_id, 'id_unit' => $unit_id );
				$this->wpdb->insert( $this->_db_prefix . 'MonLabo_units_directors', $data, array( '%d', '%d' ) );
			}
			return true;
		}
		return false;
	}

	/**
	 * remove a director from an unit
	 * @param int $director_id
	 * @param int $unit_id
	 * @return void
	 *
	 */
	public function remove_director_from_an_unit( int $director_id, int $unit_id ) {
		//if director person and unit are reals
		$director_info = $this->get_info( 'person',  $director_id );
		if ( ( ! empty( $director_info ) )
				and ( ( App::MAIN_STRUCT_NO_UNIT === $unit_id )
					or ! empty( $this->get_info( 'unit',  $unit_id ) )
					)
			) {
			$unit_directors_id = $this->get_directors_id_for_an_unit( $unit_id );
			if ( ( ! empty( $unit_directors_id ) ) and ( in_array( $director_id, $unit_directors_id ) ) ) {
				$this->delete_relations_between_directors_and_a_unit( $unit_id, array( $director_id ) );
			}
		}
	}

	/*---------------------------------------
	/*  Delete relation methods
	----------------------------------------*/
	/**
	 * Delete retation between teams and a unit
	 * @param int $unit_id Id of the unit
	 * @return void
	 */
	public function delete_relations_between_teams_and_a_unit( int $unit_id ) {
		$teams_id = $this->get_teams_id_for_an_unit( $unit_id );
		//Delete relations between teams and this unit
		//We already know that teams_id is not null
		foreach ( $teams_id as $team_id ) {
			$this->update_item( 'team', $team_id, array( 'id_unit' => App::MAIN_STRUCT_NO_UNIT ) );
		}
	}

	/**
	 * Delete teams from a person
	 * @param int $person_id
	 * @param int[] $teams_list list of teams to unlink
	 * @return void
	 */
	public function delete_relations_between_teams_and_a_person( int $person_id, array $teams_list ) {
		$this->_delete_relations_in_a_table(
			$this->_db_prefix . 'MonLabo_teams_members',
			'id_person',
			$person_id,
			'id_team',
			$teams_list
		);
	}

	/**
	 * Delete mentors from a person
	 * @param int $person_id
	 * @param int[]|null $optional_mentors optional list of mentors to unlink
	 *			 if null ==> unlink all mentors
	 * @return void
	 */
	public function delete_relations_between_a_person_and_his_or_her_mentors( int $person_id, $optional_mentors = null ) {
		$mentors_id = $optional_mentors;
		if ( is_null ( $mentors_id ) ) {
			$mentors_id = $this->get_mentors_id_for_a_person( $person_id );
		}
		$this->_delete_relations_in_a_table( $this->_db_prefix . 'MonLabo_mentors', 'id_person_student', $person_id,
															'id_person_supervisor',   $mentors_id );
	}

	/**
	 * Delete students from a person
	 * @param int $person_id
	 * @param int[]|null $students_id optional list of students to unlink
	 *			 if null ==> unlink all mentors
	 * @return void
	 */
	public function delete_relations_between_a_person_and_his_or_her_students( int $person_id, $students_id = null ) {
		if ( is_null( $students_id )  ) {
			$students_id = $this->get_students_id_for_a_person( $person_id );
		}
		$this->_delete_relations_in_a_table( $this->_db_prefix . 'MonLabo_mentors', 'id_person_supervisor',   $person_id
															, 'id_person_student', $students_id );
	}

	/**
	 * Delete persons for a team a team
	 * @param int $team_id
	 * @return void
	 */
	public function delete_relations_between_persons_and_a_team( int $team_id ) {
		$this->_delete_relations_in_a_table(
				$this->_db_prefix . 'MonLabo_teams_members',
				'id_team',
				$team_id,
				'id_person',
				$this->get_persons_id_for_a_team( $team_id )
			);
	}

	/**
	 * Delete thematics from a team
	 * @param int $team_id
	 * @param int[]|null $thematics_id optional list of thematics to unlink
	 *			 if null ==> unlink all thematics
	 * @return void
	 */
	public function delete_relations_between_thematics_and_a_team( int $team_id, $thematics_id = null ) {
		if ( null === $thematics_id ) {
			$thematics_id= $this->get_thematics_id_for_a_team( $team_id );
		}
		$this->_delete_relations_in_a_table( $this->_db_prefix . 'MonLabo_teams_thematics', 'id_team', $team_id,
															'id_thematic',   $thematics_id );
	}

	/**
	 * Delete leaders from a team
	 * @param int $team_id
	 * @return void
	 */
	public function delete_relations_between_leaders_and_a_team( int $team_id ) {
		$leaders_id = $this->get_leaders_id_for_a_team( $team_id );
		if ( ! empty( $leaders_id ) ){
			foreach ( $leaders_id as $leader_for_a_team ) {
				$where = array( 'id_person' => $leader_for_a_team,
								'id_team'   => $team_id );
				$data  = array( 'directing' => 0 );
				$this->wpdb->update( $this->_db_prefix . 'MonLabo_teams_members', $data, $where, array( '%d' ), array( '%d', '%d' ) );
			}
		}
	}

	/**
	 * Delete teams from an thematic
	 * @param int $thematic_id
	 * @return void
	 */
	public function delete_relations_between_teams_and_a_thematic( int $thematic_id ) {
		$this->_delete_relations_in_a_table(
				$this->_db_prefix . 'MonLabo_teams_thematics',
				'id_thematic',
				$thematic_id,
				'id_team',
				$this->get_teams_id_for_a_thematic( $thematic_id )
			);
	}

	/**
	 * Delete directors from an unit
	 * @param int $unit_id
	 * @param int[]|null $optional_directors optional list of directors to unlink
	 *			 if null ==> unlink all directors
	 * @return void
	 */
	public function delete_relations_between_directors_and_a_unit( int $unit_id, $optional_directors = null ) {
		$directors_id = $optional_directors;
		if ( null === $directors_id ) {
			$directors_id = $this->get_directors_id_for_an_unit( $unit_id );
		}
		$this->_delete_relations_in_a_table( $this->_db_prefix . 'MonLabo_units_directors', 'id_unit', $unit_id,
															'id_person',   $directors_id );
	}

	/**
	 * Delete units from a director
	 * @param int $person_id
	 * @return void
	 */
	public function delete_relations_between_units_and_a_director( int $person_id ) {
		$this->_delete_relations_in_a_table(
				$this->_db_prefix . 'MonLabo_units_directors',
				'id_person',
				$person_id,
				'id_unit',
				$this->get_units_id_for_a_director( $person_id )
			);
	}
}
?>
