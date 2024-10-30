<?php
namespace MonLabo\Lib;

use MonLabo\Lib\Person_Or_Structure\{Team, Person, Thematic, Unit};
use MonLabo\Lib\{App};

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

/*
class Db {

	__construct( )
    check_tables_exist( string $table_name = '' )
	is_column_exist_in_table( string $table_name, string $column_name )
	create_tables()
	fill_tables_with_default_values_if_empty()
	fill_options_with_custom_values_if_empty( array $options_DEFAULT )
	function fill_options_with_default_values_if_empty()
	migrate()
	migrate_to_version_3_1()
	migrate_to_version_3_5()
	migrate_to_version_3_7()
	_is_valid_json_array_or_object_forMigrateTo4_0( string $string )
	_wp_post_ids_into_array_forMigrateTo4_0( string $wp_post_ids )
	migrate_to_version_4_0()
	migrate_to_version_4_4()
	migrate_to_version_4_8()
	delete_options()
	delete_tables()
	detect_obsolete_table_name()
*/

/**
 * Class \MonLabo\Lib\Db
 * Class to access database
 * @package
 */
class Db {

    /**
	* Prefix of database
	* @var string
	*/
    public $db_prefix = '___not_set___';

	/**
	* constructor
	* @global $wpdb WordPress database access abstraction class
	*/
	function  __construct() {
		$options = Options::getInstance();
		if ( '___not_set___' === $options->db_prefix_in_use ) {
			global $wpdb;
			$options->set( 'db_prefix_in_use', $wpdb->prefix );
		}
		$this->db_prefix= $options->db_prefix_in_use;
	}

	/**
	* Tells if a mysql table exists.
	* @param string $table_name table name
	* @global $wpdb WordPress database access abstraction class
	* @return bool true if table exists. Else false.
	*/
	public function check_tables_exist( string $table_name = '' ): bool {
		global $wpdb;
		if ( empty( $table_name ) ) {
			$table_name = "{$this->db_prefix}MonLabo_persons";
		}
		$sql = $wpdb->prepare( "SHOW TABLES LIKE '%s'", $table_name );
		if ( $wpdb->get_var( $sql ) === $table_name ) {
			return true;
		}
		return false;
	}

	/**
	* Function to test if a column is present in a Mysql table
	* @param string $table_name table name
	* @param string $column_name column name
	* @global $wpdb WordPress database access abstraction class
	* @return bool true if column is present. Else false.
	*/
	public function is_column_exist_in_table( string $table_name, string $column_name ): bool {
		global $wpdb;
		$wpdb->flush();
		if ( $this->check_tables_exist( $table_name ) ) {
			$result = $wpdb->get_results( $wpdb->prepare( 
					"SHOW COLUMNS FROM %i WHERE FIELD=%s;",
					"$table_name",
					"$column_name"
				) );
			if ( count( $result ) > 0 ) {
				return true;
			}
		}
		return false;
	}

	/**
	* Create all empty tables
	* @global $wpdb WordPress database access abstraction class
	* @return Db
	*/
	public function  create_tables(): self {
		global $wpdb;
		$wpdb->get_results( "CREATE TABLE IF NOT EXISTS {$this->db_prefix}MonLabo_persons (
			  id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			  wp_post_ids TEXT CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  external_url varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  title varchar(20) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  first_name varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  last_name varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  category varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  function_fr varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  function_en varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  mail varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  phone varchar(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  room varchar(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  address_alt varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  descartes_publi_author_id int(11) DEFAULT NULL,
			  hal_publi_author_id varchar(255) DEFAULT NULL,
			  uid_ENT_parisdescartes varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  date_departure varchar(100) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  status varchar(20) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'actif',
			  visible varchar(3) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'oui',
			  custom1 varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  custom2 varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  custom3 varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  custom4 varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  custom5 varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  custom6 varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  custom7 varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  custom8 varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  custom9 varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  custom10 varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  image varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  external_mentors varchar(1024) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  external_students varchar(1024) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  PRIMARY KEY (id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			" );

		$wpdb->get_results( "CREATE TABLE IF NOT EXISTS {$this->db_prefix}MonLabo_teams (
			  id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			  name_fr varchar(255) DEFAULT NULL,
			  name_en varchar(255) DEFAULT NULL,
			  wp_post_ids TEXT DEFAULT NULL,
			  descartes_publi_team_id int(11) DEFAULT NULL,
			  hal_publi_team_id varchar(255) DEFAULT NULL,
			  id_unit int(5) DEFAULT NULL,
			  logo varchar(255) DEFAULT NULL,
			  color varchar(255) DEFAULT NULL,
			  PRIMARY KEY (id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			" );
		$wpdb->get_results( "CREATE TABLE IF NOT EXISTS {$this->db_prefix}MonLabo_teams_members (
			  id_person int(11) UNSIGNED NOT NULL,
			  id_team int(11) UNSIGNED NOT NULL,
			  directing tinyint(1) NOT NULL DEFAULT '0',
			  PRIMARY KEY (id_person,id_team),
			  KEY id_team (id_team)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
			" );
		$wpdb->get_results( "CREATE TABLE IF NOT EXISTS {$this->db_prefix}MonLabo_teams_thematics (
			  id_team int(11) UNSIGNED NOT NULL,
			  id_thematic int(11) UNSIGNED NOT NULL,
			  PRIMARY KEY (id_team,id_thematic),
			  KEY id_thematic (id_thematic)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
			" );
		$wpdb->get_results( "CREATE TABLE IF NOT EXISTS {$this->db_prefix}MonLabo_thematics (
			  id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			  name_fr varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  name_en varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  wp_post_ids TEXT CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  logo varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  hal_publi_thematic_id varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  PRIMARY KEY (id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			" );
		$wpdb->get_results( "CREATE TABLE IF NOT EXISTS {$this->db_prefix}MonLabo_units (
			  id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			  affiliations varchar(200) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  code varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  name_fr varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  name_en varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  wp_post_ids TEXT CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  address_alt varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  contact_alt varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  descartes_publi_unit_id int(11) DEFAULT NULL,
			  hal_publi_unit_id varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  logo varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
			  PRIMARY KEY (id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			" );
		$wpdb->get_results( "CREATE TABLE IF NOT EXISTS {$this->db_prefix}MonLabo_units_directors (
			  id_person int(11) UNSIGNED NOT NULL,
			  id_unit int(11) UNSIGNED NOT NULL,
			  PRIMARY KEY (id_person,id_unit),
			  KEY id_unit (id_unit)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
			" );

		$wpdb->get_results( "CREATE TABLE IF NOT EXISTS {$this->db_prefix}MonLabo_mentors (
			  id_person_supervisor int(11) UNSIGNED NOT NULL,
			  id_person_student int(11) UNSIGNED NOT NULL,
			  PRIMARY KEY (id_person_supervisor,id_person_student),
			  KEY id_person_student (id_person_student)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
		" );

		return $this;
	}

	/**
	* Fill tables with default values if tables are empty
	* @global $wpdb WordPress database access abstraction class
	* @return Db
	*/
	public function fill_tables_with_default_values_if_empty(): self {
		global $wpdb;
		$Persons_are_filled = false;
		$Teams_are_filled = false;
		$Thematics_are_filled = false;
		$Units_are_filled = false;
		$person1111 = null;
		$person3333 = null;
		$person6666 = null;
		$person9999 = null;
		$team1 = null;
		$team2 = null;
		$unit1 = null;
		$thematic1 = null;

		//Remplir des exemples si les tables sont vides
		$result = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM %i", "{$this->db_prefix}MonLabo_persons" ) );
		if ( 0 === intval( $result ) ) {
			$Persons_are_filled = true;
			$person3333 = new Person( 'insert',
				array( 	'id'=>null,'wp_post_ids'=>'["3333"]', 'title'=>'Pr.', 'first_name'=>'Prenom_exemple1', 'last_name'=>'Nom_exemple1',
				'category'=>'faculty', 'function_en'=>'Direction, PU Paris Descartes', 'function_fr'=>'Directrice, PU Paris Descartes',
				'mail'=>'prenom_exemple1.nom_exemple1@parisdescartes.fr', 'phone'=>'33 33',
				'room'=>'H333', 'external_url'=>'', 'descartes_publi_author_id'=>'',
				'uid_ENT_parisdescartes'=>'', 'address_alt'=>'', 'status'=>'actif', 'visible'=>'oui', 'hal_publi_author_id'=>'',
				'external_mentors'=>'', 'external_students'=>'' )
			);
			$person6666 = new Person( 'insert',
				array( 	'id'=>null,'wp_post_ids'=>'["6666"]', 'title'=>'', 'first_name'=>'Prenom_exemple2', 'last_name'=>'Nom_exemple2',
						'category'=>'staff', 'function_en'=>'CNRS Engineer', 'function_fr'=>'Ingenieur CNRS',
						'mail'=>'prenom_exemple2.nom_exemple2@parisdescartes.fr', 'phone'=>'66 66',
						'room'=>'H666', 'external_url'=>'', 'descartes_publi_author_id'=>'',
						'uid_ENT_parisdescartes'=>'', 'address_alt'=>'', 'status'=>'actif', 'visible'=>'oui', 'hal_publi_author_id'=>'',
						'external_mentors'=>'', 'external_students'=>'' )
			 );
			$person9999 = new Person( 'insert',
				array( 'id'=>null,'wp_post_ids'=>'["9999"]', 'title'=>'Dr.', 'first_name'=>'Prenom_exemple3', 'last_name'=>'Nom_exemple3',
						'category'=>'postdocs', 'function_en'=>'Postdoctoral Researcher', 'function_fr'=>'Postdoctorant',
						'mail'=>'prenom_exemple3.nom_exemple3@parisdescartes.fr', 'phone'=>'+33 6 16 17 99 99',
						'room'=>'H999', 'external_url'=>'', 'descartes_publi_author_id'=>'',
						'uid_ENT_parisdescartes'=>'', 'address_alt'=>'', 'status'=>'actif', 'visible'=>'oui', 'hal_publi_author_id'=>'',
						'external_mentors'=>'', 'external_students'=>'' )
			);
			$person9999->add_mentor( $person3333->info->id );
			$person9999->add_mentor( $person6666->info->id );
			$person1111 = new Person( 'insert',
				array( 'id'=>null,'wp_post_ids'=>'["1111"]', 'title'=>'Pr.', 'first_name'=>'Prenom_exemple4', 'last_name'=>'Nom_exemple4',
						'category'=>'faculty', 'function_en'=>'Direction, PU Paris Descartes', 'function_fr'=>'Directrice, PU Paris Descartes',
						'mail'=>'prenom_exemple4.nom_exemple4@parisdescartes.fr', 'phone'=>'11 11',
						'room'=>'H111', 'external_url'=>'', 'descartes_publi_author_id'=>'',
						'uid_ENT_parisdescartes'=>'', 'address_alt'=>'', 'status'=>'actif', 'visible'=>'oui', 'hal_publi_author_id'=>'',
						'external_mentors'=>'', 'external_students'=>'' )
			);
		}
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT COUNT(*) AS C FROM %i", "{$this->db_prefix}MonLabo_units" ) );
		if ( !isset($result[0]) || !isset( $result[0]->C ) || ( 0 === intval( $result[0]->C ) ) ) {
			$Units_are_filled = true;
			$unit1 = new Unit( 'insert',
				array( 	'id'=>null, 'affiliations'=>'CNRS/Paris Descartes',
						'code'=> 'UMR0000', 'name_fr'=>'Laboratoire pour exemple', 'name_en'=>'Example lab',
						'wp_post_ids'=>'["http://www.666.fr"]', 'hal_publi_unit_id'=>'' )
			 );
		}
		$result = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM %i", "{$this->db_prefix}MonLabo_teams" ) );
		if ( 0 === intval( $result ) ) {
			$unit1 = new Unit( 'from_code', 'UMR0000' );
			$Teams_are_filled = true;
			$team1 = new Team( 'insert',
				array( 	'id'=>null, 'name_en'=>'Very complex subject team',
						'name_fr'=>'Equipe au sujet complexe', 'wp_post_ids'=>'["888111"]',
						'descartes_publi_team_id'=>null,
						'id_unit'=>$unit1->info->id, 'logo'=>null, 'color'=>'', 'hal_publi_team_id'=>'' )
			 );
			$team2 = new Team( 'insert',
				array( 	'id'=>null, 'name_en'=>'Direction and support team',
						'name_fr'=>'Direction &amp; personnel de support', 'wp_post_ids'=>'["888222"]',
						'descartes_publi_team_id'=>null,
					'id_unit'=>$unit1->info->id, 'logo'=>null, 'color'=>'', 'hal_publi_team_id'=>'' )
			 );
		}
		$result = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM %i", "{$this->db_prefix}MonLabo_thematics" ) );
		if ( 0 === intval( $result ) ) {
			$Thematics_are_filled = true;
			$thematic1 = new Thematic( 'insert',
				array( 	'id'=>null, 'name_fr'=>'Ma thématique n°1',
						'name_en' => 'My thematics #1',
						'logo' => 'http://www.666.fr/logo.png',
						'wp_post_ids' => '["http://www.666.fr"]', 'hal_publi_thematic_id'=>'' )
			 );
		}
		if ( $Persons_are_filled and $Teams_are_filled ) {
			$team1->add_person( $person3333->info->id );
			$team1->add_person( $person9999->info->id );
			$team1->add_person( $person1111->info->id );

			$team2->add_person( $person3333->info->id );
			$team2->add_person( $person6666->info->id );

			$team1->add_leader( $person3333->info->id );
			$team1->add_leader( $person1111->info->id );

			$team2->add_leader( $person6666->info->id );
		}
		if ( $Thematics_are_filled and $Teams_are_filled ) {
			$team1->add_thematic( $thematic1->info->id );
		}
		if ( $Persons_are_filled and $Units_are_filled ) {
			$unit1->add_director( $person3333->info->id );
			$unit1->add_director( $person6666->info->id );
		}
		return $this;
	}

	/**
	* Fill options with custom values if options are empty
	* @param non-empty-array<string, array<string, string|int>> $options_DEFAULT table of default values for all options
	* @return Db
	* @SuppressWarnings(PHPMD.ElseExpression)
	*/
	public function fill_options_with_custom_values_if_empty( array $options_DEFAULT ): self {
		//Set default values of parameters
		$do_not_touch_options = array( 	'MonLabo_uses_members_and_groups',
									'MonLabo_uses_custom_fields_for_staff',
									'MonLabo_uses_unites',
									'MonLabo_uses_thematiques',
								);
		foreach ( $options_DEFAULT as $group => $options_inOneGroup ) {
			$options = get_option( $group );
			if ( ! is_array( $options ) ) { $options = array(); }
			if ( 
					( 'MonLabo_settings_group0' === $group ) 
					&& ( array_key_exists( 'MonLabo_multisite_db_to_use', $options ) )
				) {
				//Group0 already configured
				foreach ( $options_inOneGroup as $option_name => $option_default_value ) {
					//if this key is not in this array get the default value
					if ( ( ! in_array( $option_name, $do_not_touch_options )  )
							&& ( ! array_key_exists( $option_name, $options ) )
						) {
						$options[ $option_name ] = $option_default_value;
					}
				}
			} else {
				foreach ( $options_inOneGroup as $option_name => $option_default_value ) {
					//if this key is not in this array get the default value
					if ( ! array_key_exists( $option_name, $options ) ) {
						$options[ $option_name ] = $option_default_value;
					}
				}				
			}
			update_option( $group, $options );
		}

		//Refus de certaines valeurs vides
		$options4 = get_option( 'MonLabo_settings_group4' );
		if ( strlen( $options4['MonLabo_DescartesPubmed_api_url'] ) < 1 ) {
			$options4['MonLabo_DescartesPubmed_api_url'] = $options_DEFAULT['MonLabo_settings_group4']['MonLabo_DescartesPubmed_api_url'];
		}
		update_option( 'MonLabo_settings_group4', $options4 );
		return $this;
	}


	/**
	* Fill options with default values if options are empty
	* @return Db
	*/
	public function fill_options_with_default_values_if_empty(): self {
		return $this->fill_options_with_custom_values_if_empty( App::get_options_DEFAULT() );
	}

	/**
	* Test if database needs updates, and do if necessary
	* @return Db
	* @SuppressWarnings(PHPMD.ElseExpression)
	*/
	public function migrate(): self {
		Options::unsetInstance();
		$options = Options::getInstance();
		if ( $options->activated_version != get_MonLabo_version() ) {
			if  (  '0' === $options->activated_version ) {
				//Enter here only if MonLabo_activated_version does not exist => new db
				$plugin_version = get_MonLabo_version();
				$options->set( 'activated_version', $plugin_version );
			} else {
				$plugin_version = floatval( $options->activated_version ); //To compare
			}
			if ( $plugin_version < 3.1 ) {
				$this->migrate_to_version_3_1();
			}
			if ( $plugin_version < 3.5 ) {
				$this->migrate_to_version_3_5();
			}
			if ( $plugin_version < 4.0 ) {
				$this->migrate_to_version_3_7();
				$this->migrate_to_version_4_0();
			}
			if ( $plugin_version < 4.4 ) {
				$this->migrate_to_version_4_4();
			}
			if ( $plugin_version < 4.8 ) {
				$this->migrate_to_version_4_8();
			}	
			$options->set( 'activated_version', get_MonLabo_version() );
		}
		return $this;
	}

	/**
	* Migrate database from version 3 to 3.1
	* @global $wpdb WordPress database access abstraction class
	* @return Db
	*/
	public function migrate_to_version_3_1() {
		/* Transfer options;
		MonLabo_settings_group0:MonLabo_teamGroupName_fr	--> MonLabo_settings_group5:MonLabo_custom_text_Thematic_fr
		MonLabo_settings_group0:MonLabo_teamGroupName_fr_pl	--> MonLabo_settings_group5:MonLabo_custom_text_Thematics_fr
		MonLabo_settings_group0:MonLabo_teamGroupName_en	--> MonLabo_settings_group5:MonLabo_custom_text_Thematic_en
		MonLabo_settings_group0:MonLabo_teamGroupName_en_pl	--> MonLabo_settings_group5:MonLabo_custom_text_Thematics_en
		*/
		$options0 = get_option( 'MonLabo_settings_group0' );
		$options5 = get_option( 'MonLabo_settings_group5' );
		if ( isset( $options0['MonLabo_teamGroupName_fr'] ) ) {
			$options5['MonLabo_custom_text_Thematic_fr'] =  $options0['MonLabo_teamGroupName_fr'];
			unset( $options0['MonLabo_teamGroupName_fr'] );
		}
		if ( isset( $options0['MonLabo_teamGroupName_fr_pl'] ) ) {
			$options5['MonLabo_custom_text_Thematics_fr'] =  $options0['MonLabo_teamGroupName_fr_pl'];
			unset( $options0['MonLabo_teamGroupName_fr_pl'] );
		}
		if ( isset( $options0['MonLabo_teamGroupName_en'] )  ) {
			$options5['MonLabo_custom_text_Thematic_en'] =  $options0['MonLabo_teamGroupName_en'];
			unset( $options0['MonLabo_teamGroupName_en'] );
		}
		if ( isset( $options0['MonLabo_teamGroupName_en_pl'] ) ) {
			$options5['MonLabo_custom_text_Thematics_en'] =  $options0['MonLabo_teamGroupName_en_pl'];
			unset( $options0['MonLabo_teamGroupName_en_pl'] );
		}
		delete_option( 'MonLabo_settings_group0' );
		update_option( 'MonLabo_settings_group0', $options0 );
		update_option( 'MonLabo_settings_group5', $options5 );

		global $wpdb;
		$wpdb->get_results( $wpdb->prepare(
			"ALTER TABLE %i CHANGE `wp_post_id` `wp_post_id` VARCHAR(1024) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL;",
			"{$wpdb->prefix}MonLabo_persons"
		) );
		return $this;
	}

	/**
	* Migrate database from version 3.4 to 3.5
	* @global $wpdb WordPress database access abstraction class
	* @return Db
	*/
	public function migrate_to_version_3_5() {
		global $wpdb;
		//Ajout de la colone external_mentors dans MonLabo_persons
		if ( ! self::is_column_exist_in_table( "{$wpdb->prefix}MonLabo_persons", "external_mentors" ) ) { //La colonne external_mentors n’existe pas?
			$wpdb->query( $wpdb->prepare(
				"ALTER TABLE %i ADD external_mentors VARCHAR( 1024 ) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL AFTER alternate_image;",
				"{$wpdb->prefix}MonLabo_persons"
			) );
		}
		//Ajout de la colone external_students dans MonLabo_persons
		if ( ! self::is_column_exist_in_table( "{$wpdb->prefix}MonLabo_persons", "external_students" ) ) { //La colonne external_students n’existe pas?
				$wpdb->query( $wpdb->prepare( 
				"ALTER TABLE %i ADD external_students VARCHAR( 1024 ) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL AFTER external_mentors;",
				"{$wpdb->prefix}MonLabo_persons"
			) );
		}
		return $this;
	}

	/**
	* Migrate database from version 3.6 to 3.7
	* @global $wpdb WordPress database access abstraction class
	* @return Db
	*/
	public function migrate_to_version_3_7() {
		global $wpdb;
		//Conversion en TEXT de wp_post_id dans MonLabo_persons, MonLabo_teams, MonLabo_thematics et MonLabo_units
		//wp_post_id est renommé wp_post_ids
		if ( self::is_column_exist_in_table( "{$wpdb->prefix}MonLabo_persons", "wp_post_ids" ) ) {
			return $this; //Better do nothing
		}
		foreach ( array( 'MonLabo_persons', 'MonLabo_teams', 'MonLabo_thematics', 'MonLabo_units' ) as $table ) {
			$wpdb->query( $wpdb->prepare( 
				"ALTER TABLE %iCHANGE `wp_post_id` `wp_post_ids` TEXT CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL;",
				"{$wpdb->prefix}$table"
			) );
			$lines = $wpdb->get_results( $wpdb->prepare( 
					"SELECT `id`,`wp_post_ids`,`external_url` FROM %i",
					"{$wpdb->prefix}$table"
				), OBJECT );
			if ( is_array( $lines ) ) {
				foreach ( $lines as $value ) {
					if ( property_exists( $value, "id" ) and property_exists( $value, "wp_post_ids" ) ) {
						$arr = array();
						if ( ! empty( $value->wp_post_ids ) ) {
							$arr = explode( ',', $value->wp_post_ids );
						}
						foreach ( $arr as $key => $val ) {
							$arr[ $key ] = trim( $val );
							if ( empty( $arr[ $key ] ) ) {
								unset( $arr[ $key ] );
							}
						}
						if ( ( 'MonLabo_persons' !== $table )
							and property_exists( $value, "external_url" )
							and ( !empty( $value->external_url ) )
						){
							array_push( $arr, trim( $value->external_url ) ); //On fusionne avec external_url
						}
						$wp_post_ids = json_encode( array_values( array_unique( $arr ) ) );
						$wpdb->query( $wpdb->prepare(
							"UPDATE %i SET `wp_post_ids` = '$wp_post_ids' WHERE `id` = %d;",
							"{$wpdb->prefix}$table",
							$value->id
						) );
					}
				}
			}
			if ( 'MonLabo_persons' !== $table ) {
				//Suppress `external_url` from tables 'MonLabo_teams', 'MonLabo_thematics', 'MonLabo_units'
				$wpdb->query( $wpdb->prepare( "ALTER TABLE %i DROP `external_url`", "{$wpdb->prefix}$table" ) );
			}
		}
		return $this;
	}

	//These two functions are copied from class AccessData. They cannot be directly used in order to garanty
	// a good version of code in a future migration.
	/**
	* Check that a string is an array coded into json
	* @param string $string text to anayse
	* @return bool true if text is a json coded array
	 * @access private
	*/
	private function _is_valid_json_array_or_object_forMigrateTo4_0( string $string ) : bool {
		if ( !empty( $string ) ) {
			$decoded =  json_decode( $string );
			if ( ( JSON_ERROR_NONE === json_last_error() )
				and ( is_array( $decoded ) or is_object( $decoded ) ) ) {
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
	 * @access private
	 */
	private function _wp_post_ids_into_array_forMigrateTo4_0( string $wp_post_ids ) : array {
		if ( $this->_is_valid_json_array_or_object_forMigrateTo4_0( $wp_post_ids ) ) {
			$arr = json_decode( $wp_post_ids );
			if ( is_object( $arr ) ) { $arr = (array) $arr; 	}
			foreach ( $arr as $key => $value ) {
				if ( "" === $value ) { unset( $arr[ $key ] ); } //Suppress empty entries
			}
			return array_values( (array) $arr );
		}
		return explode( ',', $wp_post_ids );
	}

	/**
	* Migrate database from version 3.7 to 4.0
	* @global $wpdb WordPress database access abstraction class
	* @return Db
	*/
	public function migrate_to_version_4_0() : self {
		global $wpdb;
		//Renomme colonne MonLabo_persons:alternate_image en MonLabo_persons:image
		//if ( self::is_column_exist_in_table( "{$wpdb->prefix}MonLabo_persons", "alternate_image" ) ) {
			$wpdb->query( $wpdb->prepare(
				"ALTER TABLE %i CHANGE `alternate_image` `image` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL;",
				"{$wpdb->prefix}MonLabo_persons"
			) );
			//Pour chaque personne on récupère le thumbnail de sa page principale pour le mettre dans le champs `image`.
			$lines = $wpdb->get_results( $wpdb->prepare( "SELECT `id`,`wp_post_ids`,`image` FROM %i", "{$wpdb->prefix}MonLabo_persons" ), OBJECT );
			if ( is_array( $lines ) ) {
				foreach ( $lines as $value ) {
					if ( property_exists( $value, "id" ) and property_exists( $value, "wp_post_ids" ) ) {
						$wp_post_ids = $this->_wp_post_ids_into_array_forMigrateTo4_0( (string) $value->wp_post_ids );
						$main_page = '';
						if ( ! empty( $wp_post_ids ) ) {
							$main_page = $wp_post_ids[0];
						}
						$is_numeric_main_page = ( $main_page == (string) abs( intval( $main_page ) ) );
						if ( $is_numeric_main_page and has_post_thumbnail( $main_page ) ) {
							$value->image = get_post_thumbnail_id( $main_page );
							$wpdb->query( $wpdb->prepare(
								"UPDATE %i SET `image` = %s WHERE `id` = %d;",
								"{$wpdb->prefix}MonLabo_persons", 
								$value->image,
								$value->id
							) );
						}
					}
				}
			}
		//}
		return $this;
	}

	/**
	* Migrate options from version 4.3 to 4.4
	* @return Db
	*/
	public function migrate_to_version_4_4() : self {
		//Transfère l'option MonLabo_cache_duration du groupe 4 vers 7
		$options4 = get_option( 'MonLabo_settings_group4' );
		$options7 = get_option( 'MonLabo_settings_group7' );
		if ( isset( $options4['MonLabo_cache_duration'] ) ) {
			$options7['MonLabo_cache_duration'] =  $options4['MonLabo_cache_duration'];
			unset( $options4['MonLabo_cache_duration'] );
		}
		update_option( 'MonLabo_settings_group4', $options4 );
		update_option( 'MonLabo_settings_group7', $options7 );
		return $this;
	}

	/**
	* Migrate options from version 4.7 to 4.8
	* @return Db
	*/
	public function migrate_to_version_4_8() : self {
		//Transfère l'option MonLabo_perso_page_parent du groupe 2 vers 10
		$options2 = get_option( 'MonLabo_settings_group2' );
		$options10 = get_option( 'MonLabo_settings_group10' );
		if( empty( $options10 ) ) {
			$options10 = array();
		}
		if ( isset( $options2['MonLabo_perso_page_parent'] ) ) {
			$options10['MonLabo_perso_page_parent'] =  $options2['MonLabo_perso_page_parent'];
			unset( $options2['MonLabo_perso_page_parent'] );
		}
		update_option( 'MonLabo_settings_group2', $options2 );
		update_option( 'MonLabo_settings_group10', $options10 );
		return $this;
	}

	/**
	* Delete all options
	* @return Db
	*/
	public function delete_options(): self {
		//Effacer les options
		//L’appel à App ne marche pas quand l’application est désactivée
		//	foreach ( array_keys( \MonLabo\Lib\App::get_options_DEFAULT() ) as $group ) {
		//L’appel à Contact_Webservices ne marche pas quand l’application est désactivée
		//	$webservice = new Contact_Webservices;
		//	$webservice->clear_transients();
		Options::getInstance();
		Options::unsetInstance();
        foreach ( array_keys( App::get_options_DEFAULT() ) as $group ) {
            delete_option($group);
        }
		return $this;
	}


	/**
	* Delete all MonLabo tables
	* @global $wpdb WordPress database access abstraction class
	* @return Db
	*/
	public function delete_tables(): self {
		global $wpdb;
		//Effacer les tables
		//Folowing line to delete before migrated to V2.8
		foreach ( App::get_tables_names() as $table ) {
			$wpdb->query( $wpdb->prepare( "DROP TABLE IF EXISTS %i", "$table" ) );
		}
		//Folowing line to delete when migrated to V2.8
		foreach ( App::get_tables_names() as $table ) {
			$wpdb->query( $wpdb->prepare( "DROP TABLE IF EXISTS %i", "{$this->db_prefix}$table" ) );
		}
		//Folowing line to delete when migrated to V1.0
		$wpdb->query( 'DROP TABLE IF EXISTS MonLabo_members, MonLabo_thematiques, MonLabo_unites' );
		return $this;
	}

	/**
	* True if table name is in obsolete format. Else false.
	* @return bool
	*/
	public function detect_obsolete_table_name(): bool {
		//If table for persons is empty
		if ( ( ! $this->check_tables_exist( $this->db_prefix . 'MonLabo_persons' ) )
				and ( $this->check_tables_exist( 'MonLabo_persons' ) )
		) {
			return true;
		}
	  return false;
	}
}
