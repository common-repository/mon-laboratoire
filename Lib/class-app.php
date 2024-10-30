<?php
namespace MonLabo\Lib;

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

use MonLabo\Lib\Lib;

/**
 * Class \MonLabo\Lib\Db
 * Central class of application
 * @package
 */
class App {

	/**
	 * Get the list of tables nammes
	 * @return string[] list of table names
	 * @static
	 */
	public static function get_tables_names(): array {
		$tables = array(
			'MonLabo_persons',
			'MonLabo_teams',
			'MonLabo_thematics',
			'MonLabo_units',
			'MonLabo_mentors',
			'MonLabo_units_directors',
			'MonLabo_teams_members',
			'MonLabo_teams_thematics'
		);
		return $tables;
	}

	const MAIN_STRUCT_NO_UNIT        = 99999;
	const NO_PAGE_OPTION_VALUE       = '-1';
	const NEW_PAGE_OPTION_VALUE      = '-2';

	const OPTIONS0_DEFAULT = array(
		'MonLabo_activated_version'			 	=> '0',
		'MonLabo_publication_server_type'	   	=> 'hal',
		'MonLabo_uses_members_and_groups'	   	=> '1',
		'MonLabo_uses_custom_fields_for_staff' 	=> '0',
		'MonLabo_uses_unites'				   	=> '0',
		'MonLabo_uses_thematiques'			  	=> '0',
		'MonLabo_multisite_db_to_use'			=> '___no_change___',
		'MonLabo_db_prefix_manual_edit'			=> '___not_set___',
	);
	const OPTIONS1_DEFAULT = array(
		'MonLabo_nom'					=> 'Centre de Neurophysique, Physiologie et Pathologie',
		'MonLabo_code'					=> 'CNRS UMR 8119',
		'MonLabo_adresse'   			=> "Université Paris Descartes\n45 Rue des Saints Pères\n75270 Paris Cedex 06\nFrance",
		'MonLabo_prefixe_tel'			=> '+33 1 42 86',
		'MonLabo_contact'				=> 'Fax&nbsp;: +33 (0) 1 42 86 20 80',
		'MonLabo_hal_publi_struct_id'	=> '44477,9310',
	);
	const OPTIONS2_DEFAULT = array(
		'MonLabo_foreground_color'		=> '#00a4db',
		'MonLabo_address_color'			=> '#aaaaaa',
		'MonLabo_address_size'			=> '10px',
		'MonLabo_address_block_width'	=> '180px',
		'MonLabo_perso_panel_width'		=> '667px',
		'MonLabo_name_size'				=> '26px',
		'MonLabo_img_arrondi'			=> '50%',
		'MonLabo_img_par_defaut'		=> '',
		'MonLabo_custom_css'			=> '',
	);	
	const OPTIONS3_DEFAULT = array(
		'MonLabo_custom_fields_number'	=> '1',
		'MonLabo_custom_field1_title'	=> '',
		'MonLabo_custom_field2_title'	=> '',
		'MonLabo_custom_field3_title'	=> '',
		'MonLabo_custom_field4_title'	=> '',
		'MonLabo_custom_field5_title'	=> '',
		'MonLabo_custom_field6_title'	=> '',
		'MonLabo_custom_field7_title'	=> '',
		'MonLabo_custom_field8_title'	=> '',
		'MonLabo_custom_field9_title'	=> '',
		'MonLabo_custom_field10_title'	=> '',
	);
	const OPTIONS4_DEFAULT = array(
		'MonLabo_hal_publi_style'			=> 'hal',
		'MonLabo_DescartesPubmed_api_url'	=> 'https://www.biomedicale.univ-paris5.fr/ufr/publications/API_publique.php',
		'MonLabo_DescartesPubmed_format'  	=> 'html_default',
		/*'MonLabo_ask_erase_cache_after_cfg'	=> '',*/
	);
	const OPTIONS5_DEFAULT = array(
		'MonLabo_custom_text_Room_en'	   			=> 'Room',
		'MonLabo_custom_text_Room_fr'	   			=> 'Bureau',
		'MonLabo_custom_text_Personal_website_en'	=> 'Personal website',
		'MonLabo_custom_text_Personal_website_fr'	=> 'Site personnel',
		'MonLabo_custom_text_Recent_Publications_en'=> 'Recent Publications',
		'MonLabo_custom_text_Recent_Publications_fr'=> 'Dernières Publications',
		'MonLabo_custom_text_Team_leader_en'		=> 'Team leader',
		'MonLabo_custom_text_Team_leader_fr'		=> 'Chef⋅fe d’équipe',
		'MonLabo_custom_text_Team_leaders_en'		=> 'Team leaders',
		'MonLabo_custom_text_Team_leaders_fr'		=> 'Chef⋅fe⋅s d’équipe',
		'MonLabo_custom_text_Faculty_en'			=> 'Faculty',
		'MonLabo_custom_text_Faculty_fr'			=> 'Chercheur⋅e⋅s permanents',
		'MonLabo_custom_text_Staff_en'				=> 'Support staff',
		'MonLabo_custom_text_Staff_fr'				=> 'Ingénieur⋅e⋅s et technicien⋅e⋅s',
		'MonLabo_custom_text_Postdocs_en'			=> 'Postdocs',
		'MonLabo_custom_text_Postdocs_fr'			=> 'Postdocs',
		'MonLabo_custom_text_Student_en'			=> 'Student',
		'MonLabo_custom_text_Student_fr'			=> 'Étudiant⋅e',
		'MonLabo_custom_text_Students_en'			=> 'Students',
		'MonLabo_custom_text_Students_fr'			=> 'Étudiant⋅e⋅s',
		'MonLabo_custom_text_Visitors_en'			=> 'Visitors',
		'MonLabo_custom_text_Visitors_fr'			=> 'Visiteurs',
		'MonLabo_custom_text_Direction_en'			=> 'Direction',
		'MonLabo_custom_text_Direction_fr'			=> 'Direction',
		'MonLabo_custom_text_Member_en'				=> 'Member',
		'MonLabo_custom_text_Member_fr'				=> 'Membre',
		'MonLabo_custom_text_Members_en'			=> 'Members',
		'MonLabo_custom_text_Members_fr'			=> 'Membres',
		'MonLabo_custom_text_Thematic_en'			=> 'Thematic',
		'MonLabo_custom_text_Thematic_fr'			=> 'Thématique',
		'MonLabo_custom_text_Thematics_en'			=> 'Thematics',
		'MonLabo_custom_text_Thematics_fr'			=> 'Thématiques',
		'MonLabo_custom_text_Supervised_student_en'	=> 'Student',
		'MonLabo_custom_text_Supervised_student_fr'	=> 'Étudiant⋅e',
		'MonLabo_custom_text_Supervised_students_en'=> 'Students',
		'MonLabo_custom_text_Supervised_students_fr'=> 'Étudiant⋅e⋅s',
		'MonLabo_custom_text_Supervisor_en'			=> 'Supervisor',
		'MonLabo_custom_text_Supervisor_fr'			=> 'Encadrant⋅e',
		'MonLabo_custom_text_Supervisors_en'		=> 'Supervisors',
		'MonLabo_custom_text_Supervisors_fr'		=> 'Encadrant⋅e⋅s',
	);
	const OPTIONS6_DEFAULT = array(
		'MonLabo_language_config'  	=> 'WordPress',
	);
	const OPTIONS7_DEFAULT = array(
		'MonLabo_cache_duration'	=> 24,
		'MonLabo_do_erase_cache'	=> '',
	);
	const OPTIONS8_DEFAULT = array(
		'MonLabo_first_configuration' => '0',
	);
	const OPTIONS9_DEFAULT = array(
		'MonLabo_db_prefix_in_use'	=> '___not_set___',
	);
	const OPTIONS10_DEFAULT = array(
		'MonLabo_perso_page_parent'	 			=> self::NO_PAGE_OPTION_VALUE,
		'MonLabo_do_create_perso_page_parent'	=> '',
		'MonLabo_team_page_parent'	 			=> self::NO_PAGE_OPTION_VALUE,
		'MonLabo_do_create_team_page_parent'	=> '',
		'MonLabo_thematic_page_parent'			=> self::NO_PAGE_OPTION_VALUE,
		'MonLabo_do_create_thematic_page_parent'=> '',
		'MonLabo_unit_page_parent'	 			=> self::NO_PAGE_OPTION_VALUE,
		'MonLabo_do_create_unit_page_parent'	=> '',
	);

	const OPTIONS11_DEFAULT = array(
		'MonLabo_hide_persons_email'	 		=> '0',
	);


	/**
	 * Get the default values of all options organized by option groups.
	 * @return non-empty-array<string, array<string, string|int>> default option values array
	 * @static
	 */
	public static function get_options_DEFAULT(): array {
		$default_options = array(
			'MonLabo_settings_group0'  => self::OPTIONS0_DEFAULT,
			'MonLabo_settings_group1'  => self::OPTIONS1_DEFAULT,
			'MonLabo_settings_group2'  => self::OPTIONS2_DEFAULT,
			'MonLabo_settings_group3'  => self::OPTIONS3_DEFAULT,
			'MonLabo_settings_group4'  => self::OPTIONS4_DEFAULT,
			'MonLabo_settings_group5'  => self::OPTIONS5_DEFAULT,
			'MonLabo_settings_group6'  => self::OPTIONS6_DEFAULT,
			'MonLabo_settings_group7'  => self::OPTIONS7_DEFAULT,
			'MonLabo_settings_group8'  => self::OPTIONS8_DEFAULT,
			'MonLabo_settings_group9'  => self::OPTIONS9_DEFAULT,
			'MonLabo_settings_group10' => self::OPTIONS10_DEFAULT,
			'MonLabo_settings_group11' => self::OPTIONS11_DEFAULT,
		);
		$default_options['MonLabo_settings_group2']['MonLabo_img_par_defaut'] = Lib::get_file_url( "Frontend/images/photo_par_defaut.png" );
		return $default_options;
	}

	/**
	 * Get all the members functions
	 * @return non-empty-array<string, string[]> Structure with all the functions
	 * @static
	 */
	public static function get_MonLabo_MembersFunctionsByCategory_default(): array {
		return array(
			'faculty' => array(
							'Assistant Professor | Maître de Conférences',
							'Assistant Professor | Maîtresse de Conférences',
							'Professor | Professeur',
							'Professor | Professeure',
							'Professor Emeritus | Professeur Émérite',
							'Professor Emeritus | Professeure Émérite',
							'Research Scientist | Chargé de Recherche',
							'Research Scientist | Chargée de Recherche',
							'Senior Research Scientist | Directeur de Recherche',
							'Senior Research Scientist | Directrice de Recherche'
						),
			'staff' => array(
							'Administrative Assistant | Adjoint Administratif',
							'Administrative Assistant | Adjointe Administrative',
							'Assistant Engineer | Assistant Ingénieur',
							'Assistant Engineer | Assistante Ingénieure',
							'Engineer | Ingénieur d’Études',
							'Engineer | Ingénieure d’Études',
							'Senior Engineer | Ingénieur de Recherche',
							'Senior Engineer | Ingénieure de Recherche',
							'Technician | Technicien',
							'Technician | Technicienne',
							'Technical assistant | Adjoint Technique',
							'Technical assistant | Adjointe Technique'
						),
			'postdocs' => array(
							'Postdoctoral Researcher | Postdoctorant',
							'Postdoctoral Researcher | Postdoctorante'
						),
			'students' => array(
							'Doctoral Student | Doctorant',
							'Doctoral Student | Doctorante',
							'Master Student | Étudiant de Master',
							'Master Student | Étudiante de Master'
						),
			'visitors' => array(
							'Visitor | Visiteur',
							'Visitor | Visiteuse',
						),
				);
	}

	/**
	 * Get all the persons default tiltes
	 * @return non-empty-array<string, string> Structure with all the functions
	 * @static
	 */
	public static function get_MonLabo_MembersTitles_default(): array {
		return array(
			'none' => '&nbsp;',
			'Dr.' => 'Dr.',
			'Pr.' => 'Pr.'
		);
	}

	/**
	 * Get all the members categories
	 * @return string[] Structure with all the categories
	 * @static
	 */
	public static function get_MonLabo_persons_categories(): array {
		return array(
				'faculty' => 'faculty',
				'staff' => 'staff',
				'postdocs' => 'postdocs',
				'students' => 'students',
				'visitors' => 'visitors'
			);
	}


}
