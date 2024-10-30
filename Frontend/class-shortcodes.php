<?php
namespace MonLabo\Frontend;

use MonLabo\Lib\{App, Translate, Lib};
use MonLabo\Lib\Access_Data\{Access_Data};
use MonLabo\Lib\Person_Or_Structure\Groups\{Persons_Group, Teams_Group};
use MonLabo\Lib\Person_Or_Structure\{Person, Team, Unit};

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

/**
 * Class \MonLabo\Frontend\Shortcodes
 * @package
 */
class Shortcodes extends Shortcodes_Mother {
	use \MonLabo\Lib\Singleton;

	/*
		_color_of_team_if_only_one_team( $teams )
		_filter_persons_from_belongins( &$teams, &$units, &$categories, $status = 'actif' );

		members_list( $atts )				 DONE
		members_table( $atts )				DONE
		members_chart ()

		perso_panel ()						DONE	TESTED
		team_panel ()						 DONE

		former_members_list ()						DONE
		former_members_table ()

		teams_list ()
		//team_table ()						 POUR FUTUR EVENTUEL
		//thematics_list ()					 POUR FUTUR EVENTUEL
		//unit_list ()						  POUR FUTUR EVENTUEL
		//unit_table ()						 POUR FUTUR EVENTUEL

		dev_team_name()
		dev_team_logo()
	*/

	/**
	 * Current instance of Access_Data
	 * @access private
	 * @var Access_Data
	 */
	private $_accessData = null;

	/**
	 * Current instance of Html
	 * @access private
	 * @var Html
	 */
	private $_html = null;

	/**
     * Create a new class
     */
	private function  __construct() {
		$this->_accessData = new Access_Data();
		$this->_html = new Html();
	}

	
	/**
	  * Check that there is only one team in array, then get its color.
	  * @param int[] $teams list of teams id.
	  * @return string color code or empty string.
	  * @access private
	  */
	private function _color_of_team_if_only_one_team( array $teams ): string {
		//----------------------------------------------------
		// S'il n'y a qu’une équipe, récupérer sa couleur
		//----------------------------------------------------
		$color = '';
		if ( 1 === count( $teams ) ) {
			$first_team_id = reset( $teams ); //Get the first element of array $teams
			$team = new Team( 'from_id', (int) $first_team_id );
			$color = $team->info->color;
		}
		return ( empty( $color ) ? '' : $color );
	}

	/**
	  * Convert list of units :
	  * 	- empty => all units
	  * 	- some units => some units + main struct
	  * @param int[] $units_id list of unit id
	  * @return int[] list of unit ID
	  * @access private
	  */
	private function _get_explicit_unit_list( array $units_id ): array {
		// Préparation de la liste de toutes les unités existantes
		$existing_units_id = array( App::MAIN_STRUCT_NO_UNIT=>App::MAIN_STRUCT_NO_UNIT ) + Lib::secured_array_keys( $this->_accessData->get_units_name() );
		// si units non vide
		if ( ! empty( $units_id ) ) {
			// Si l’on demande que la structure principale
			$id_of_units = array_values( $units_id );
			if ( 1 === ( count( $units_id ) ) and ( App::MAIN_STRUCT_NO_UNIT === $id_of_units[array_key_first( $id_of_units )] ) ) {
				// Demander toutes les unités
				$units_id = $existing_units_id;
			}
			// On enlève les unités fantaisistes
			return array_intersect( $units_id, $existing_units_id );
		}
		//$units_id et $teams sont vides
		// Demander toutes les unités
		return $existing_units_id;
	}

	/**
	  * If at least one team is specified, filter people by teams.
	  * If not, filter by units.
	  * @param int[] &$teams modified list of teams id
	  * @param int[] &$units modified list of units id
	  * @param string[] &$categories modified list of categories of persons
	  * @param string $status status of the person ('actif' or 'alumni')
	  * @return array{Persons_Group,string} array( $MonLaboPersons, $direction_title_mode )
	  * 	- MonLabo\Lib\Person_Or_Structure\Groups\Persons_Group $MonLaboPersons : list of persons
	  * 	- string $direction_title_mode : 'directors' or 'team_leaders', hint to title the direction
	  * @access private
	  * @SuppressWarnings(PHPMD.ElseExpression)
	  */
	private function _filter_persons_from_belongins( array &$teams, array &$units, array &$categories, string $status = 'actif' ): array {
		//-----------------------------------------------------------------------
		// Si au moins une équipe est précisée, filtrer les personnes en fonction des équipes.
		// Sinon filtrer en fonction des unités
		//-----------------------------------------------------------------------
		//  Si teams non vide
		//	  (ignorer units )
		//	  filtrer en fonction de teams
		// sinon
		//	  si units non vide
		//		  filtrer en fonction de units
		//	  sinon (i.e. teams et units sont vides)
		//		  filtrer en fonction de toutes les équipes existantes
		// filtrer en fonction des catégories

		//-------------------------------------------
		// 1 - Filtrage en fonction de teams ou units
		//-------------------------------------------
		$direction_title_mode = 'directors';
		$MonLaboPersons = new Persons_Group( $this->_accessData->get_persons_info( $status ) );
		//  Si teams non vide
		if ( ! empty( $teams ) ) {
			// Préparation de la liste de toutes les équipes existantes
			$teams_infos = $this->_accessData->get_teams_info();
			if ( ! empty( $teams_infos ) ) {
				//On ne filtre que si des équipes sont définies
				$existing_teams_id = Lib::secured_array_keys( $teams_infos );
				// S'il n'y a qu’une seule équipe, chercher les chefs d'équipe
				// plutot que la direction de la structure.
				if ( 1 === count( $teams ) ){ $direction_title_mode = 'team_leaders';  }
				// On enlève les équipes fantaisistes
				$teams = array_intersect( $teams, $existing_teams_id );
				// On filtre
				$MonLaboPersons->filter_with_teams( $teams, $status );
			}
		} else {
			$units = $this->_get_explicit_unit_list( $units );
			// On filtre
			$MonLaboPersons->filter_with_units( $units, $status );
		}

		//---------------------------------------
		// 2 - Filtrage en fonction de catégories
		//---------------------------------------
		// Si vide utiliser toutes les catégories existantes (pas la peine de filtrer)
		if ( empty( $categories ) ) {
			$categories = App::get_MonLabo_persons_categories();
		} else {
			// Filtrage si non vide
			$MonLaboPersons->filter_with_categories( $categories );
		}

		return array( $MonLaboPersons, $direction_title_mode );
	}

	/**
	  * If at least one groups is specified, filter teams by groups.
	  * filter also teams by units.
	  * @param int[] &$groups modified list of group id
	  * @param int[] &$units modified list of units id
	  * @return \MonLabo\Lib\Person_Or_Structure\Groups\Teams_Group list of teams
	  * @access private
	  * @SuppressWarnings(PHPMD.ElseExpression)
	  */
	private function _filter_teams_from_belongins( array &$groups, array &$units ) /*: object //retrocompatibility with PHP7.0 */{
		//-------------------------------------------
		// 1 - Filtrage en fonction de units
		//-------------------------------------------
		$MonLaboTeams = new Teams_Group( $this->_accessData->get_teams_info( ) );
		$units = $this->_get_explicit_unit_list( $units );
		// On filtre
		$MonLaboTeams->filter_with_units( $units );

		//---------------------------------------
		// 2 - Filtrage en fonction des groupes
		//---------------------------------------
		// Si vide utiliser touts les groupes existants (pas la peine de filtrer)
		if ( empty( $groups ) ) {
			$groups = array_keys( $this->_accessData->get_thematics_info() );
		} else {
			// Filtrage si non vide
			$MonLaboTeams->filter_with_groups( $groups );
		}

		return $MonLaboTeams;
	}

	/**
	* If empty, we are updating teams with team of current page.
 	* @param int[] $teams array of teams id
	* @return int[] array of teams id, eventually updated
	* @access private
	*/
	private function _fill_teams_with_team_of_current_page_if_empty ( array $teams ): array {
		//On recherche une éventuelle équipe attachée à la page courante
		if ( ( empty( $teams ) ) ) {
			$team_of_current_page = $this->_team_of_curent_page();
			if ( ! empty( $team_of_current_page ) ) {
				return array( $team_of_current_page => $team_of_current_page );
			}
			return array();
		}
		return $teams;
	}

	/**
	 * Add shortcode [members_list]
	 * get list of persons from teams, labs etc...
  	 * ------------------------------------------------
  	 * ex 1: [members_list] => get all the persons
  	 * ex 2: [members_list team = '1'] => get all the persons of team 1, separate leaders
  	 * ex 3: [members_list team = '1' uniquelist = 'yes'] => get all the persons of team 1
  	 *													  do not separate leaders
	 * @param mixed[]|string $atts  User defined attributes in shortcode tag
	 * @return string HTML code of the list of persons
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	public function members_list( $atts = array() ): string {
		$atts = (array) $atts;
		$a_afficher = '';
		$translate = new Translate();
		$direction_title_mode = '';

		// Attributes
		$args = $this->_sanitize_parameters( $this->_init_args(
			array(
				'team' => '', 'teams' => '',
				'unit'=> '', 'units' => App::MAIN_STRUCT_NO_UNIT, //structure mère par défaut
				'uniquelist' => 'no',
				'display_direction' => 'yes',
				'category' => '', 'categories' => '',
				'person' => '', 'persons' => '',
			),
			$atts,
			'members_list'
		) );

		//--------------------------------------------
		// 1 - Sélectionner les personnes
		//--------------------------------------------
		if ( empty( $args['persons'] ) ) {
			$args['teams'] = $this->_fill_teams_with_team_of_current_page_if_empty( $args['teams'] );
			//On filtre les personnes
			list( $MonLaboPersons, $direction_title_mode )
				= $this->_filter_persons_from_belongins( $args['teams'], $args['units'], $args['categories'] );
		} else {
			$MonLaboPersons = new Persons_Group( $this->_accessData->get_persons_info( 'actif' ) );
			$MonLaboPersons->filter_with_ids( $args['persons'] );
			$args['uniquelist'] = 'yes';
		}

		//--------------------------------------------
		// 2 - Générer l’affichage
		//--------------------------------------------
		if ( 'yes' === $args['uniquelist'] ) {
			if ( $MonLaboPersons->count() > 0 ) {
				return $this->_html->persons_list( '', '', $MonLaboPersons->sort_by_name()->get_persons(), $translate->get_lang() );
			}
			return '';
		}
		if ( 'yes' === $args['display_direction'] ) {
			if ( 'team_leaders' === $direction_title_mode ) {
				$direction_list = $MonLaboPersons->extract_and_suppress_team_leaders();
				$title_single = 'Team leader';
				$title_plural = 'Team leaders';
			} else {
				$direction_list = $MonLaboPersons->extract_and_suppress_direction();
				$title_single = 'Direction';
				$title_plural = 'Direction';
			}
			if ( $direction_list->count()  >0 ) {
				$a_afficher .= $this->_html->persons_list(
					$title_single,
					$title_plural,
					$direction_list->sort_by_name()->get_persons(),
					$translate->get_lang()
				);
			}
		}
		if ( 'team_leaders' === $direction_title_mode ) {
			//On est dans une équipe, afficher tous les non leaders ensemble.
			if ( $MonLaboPersons->count() >0 ) {
				$a_afficher .= $this->_html->persons_list(
					'Member',
					'Members',
					$MonLaboPersons->sort_by_name()->get_persons(),
					$translate->get_lang()
				);
			}
			return $a_afficher;
		}
		//On est dans une unité, séparer par catégories
		foreach ( $args['categories'] as $category ) {
			$category_Persons= new Persons_Group( $MonLaboPersons->get_persons() );
			$category_Persons->filter_with_categories( array( "$category" ) );
			if ( $MonLaboPersons->count() >0 ) {
				$a_afficher .= $this->_html->persons_list(
					$category,
					$category,
					$category_Persons->sort_by_name()->get_persons(),
					$translate->get_lang()
				);
			}
		}
		return $a_afficher;
	}

	/**
	 * Add shortcode [members_table]
	 *  get table of persons from teams, labs etc...
  	 * ------------------------------------------------
  	 * ex 1: [members_table] => get all the persons
  	 * ex 2: [members_table team = '1'] => get all the persons of team 1
 	 * @param mixed[]|string $atts  User defined attributes in shortcode tag
	 * @return string HTML code of the table of persons
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 */
	function members_table( $atts = array() ): string {
		$atts = (array) $atts;
		$a_afficher = '';
		$translate = new Translate();
		// Attributes
		$args = $this->_sanitize_parameters( $this->_init_args(
			array(
				'team' => '', 'teams' => '',
				'unit'=> '', 'units' => App::MAIN_STRUCT_NO_UNIT, //structure mère par défaut
				'presentation' => 'normal',
				'uniquelist' => 'no',
				'display_direction' => 'yes',
				'category' => '', 'categories' => ''
			),
			$atts,
			'members_table'
		) );

		//--------------------------------------------
		// 1 - Sélectionner les personnes
		//--------------------------------------------
		$args['teams'] = $this->_fill_teams_with_team_of_current_page_if_empty( $args['teams'] );
		list( $MonLaboPersons, ) = $this->_filter_persons_from_belongins( $args['teams'], $args['units'], $args['categories'] );

		//----------------------------------------------------
		// 2 - S'il n’y a qu’une équipe, récupérer sa couleur
		//----------------------------------------------------
		$color = $this->_color_of_team_if_only_one_team( $args['teams'] );

		//--------------------------------------------
		// 3 - Générer l'affichage
		//--------------------------------------------
		$MonLabo_directors = new Persons_Group();
		if ( ( 'yes' === $args['display_direction'] ) and ( 'yes' !== $args['uniquelist'] ) ) {
			//Ajoute une catégorie 'direction'
			$MonLabo_directors = $MonLaboPersons->extract_and_suppress_direction();
		}

		if ( 'compact' === $args['presentation'] ) {
			$person_array = array();
			foreach ( $args['categories'] as $category ) {
				$category_Persons = new Persons_Group( $MonLaboPersons->get_persons() );
				$category_Persons->filter_with_categories( array( "$category" ) );
				if ( $MonLabo_directors->count() >0 ) {
					$person_array['direction'] = $this->_html->persons_table_compact_column(
						$MonLabo_directors->sort_by_name()->get_persons(),
						'Direction',
						$translate->get_lang(),
						$color
					);
				}
				if ( $category_Persons->count() >0 ) {
					$person_array[ $category ] = $this->_html->persons_table_compact_column(
						$category_Persons->sort_by_name()->get_persons(),
						ucfirst( $category ),
						$translate->get_lang(),
						$color
					);
				}
			}
			//$person_array has to be 2 dimensions.
			if ( count( $person_array ) >0 ) {
				$a_afficher .= $this->_html->generic_table( '', '', array(), array( $person_array ), '', '', 'MonLabo-persons-table-compact' );
			}
			return $a_afficher;
		}

		//Affichage du mode normal ( annuaire )
		$colum_titles = array( 'Nom', 'Equipe', 'Email', 'Tel' );
		if ( 'yes' !== $args['uniquelist'] ) {
			if ( $MonLabo_directors->count() >0 ) {
				$a_afficher .= $this->_html->persons_table_normal(
					'Direction',
					$colum_titles,
					$MonLabo_directors->sort_by_name()->get_persons(),
					$translate->get_lang(),
					$color
				);
			}
			foreach ( $args['categories'] as $category ) {
				$category_Persons= new Persons_Group( $MonLaboPersons->get_persons() );
				$category_Persons->filter_with_categories( array( "$category" ) );
				if ( $category_Persons->count() >0 ) {
					$a_afficher .= $this->_html->persons_table_normal(
						ucfirst( $category ),
						$colum_titles,
						$category_Persons->sort_by_name()->get_persons(),
						$translate->get_lang(),
						$color
					);
				}
			}
			return $a_afficher;
		}

		//'yes' === $args['uniquelist']
		if ( $MonLaboPersons->count() >0 ) {
			$a_afficher .= $this->_html->persons_table_normal(
				'',
				$colum_titles,
				$MonLaboPersons->sort_by_name()->get_persons(),
				$translate->get_lang(),
				$color
			);
		}
		return $a_afficher;
	}

	/**
	 * Add shortcode [members_chart]
	 * get organizational chart of persons from teams, labs etc...
  	 * ------------------------------------------------
  	 * ex 1: [members_chart] => get all the persons
	 * @param mixed[]|string $atts  User defined attributes in shortcode tag
	 * @return string HTML code of the chart of persons
	 */
	function members_chart( $atts = array() ): string {
		$atts = (array) $atts;
		return $this->_persons_chart( $atts, 'actif' );
	}

	/**
	 * Add shortcode [former_members_chart]
	 * get organizational chart of alumni from teams, labs etc...
  	 * ------------------------------------------------
  	 * ex 1: [former_members_chart] => get all the alumni
	 * @param mixed[]|string $atts  User defined attributes in shortcode tag
	 * @return string HTML code of the chart of persons
	 */
	function former_members_chart( $atts = array() ): string {
		$atts = (array) $atts;
		return $this->_persons_chart( $atts, 'alumni' );
	}

	/**
	 * Generic code for shortcode [members_chart] [former_members_chart]
	 * get organizational chart of alumni from teams, labs etc...
  	 * ------------------------------------------------
  	 * ex 1: [former_members_chart] => get all the alumni
	 * @param mixed[] $atts User defined attributes in shortcode tag
	 * @param string $status status of pêrsons : 'actif' of 'alumni'
	 * @return string HTML code of the chart of persons
	 * @access private
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	private function _persons_chart( array $atts = array(), string $status = 'actif' ): string {
		//TODO: Mettre en place les couleurs des équipes à partir de la version 1.00 de la base de données
		$a_afficher = '';
		$persons_by_category_and_team = array();
		$translate = new Translate();
		// Attributes
		$args = $this->_sanitize_parameters( $this->_init_args(
			array(
				'unit'=> '', 'units' => '',
				'team' => '', 'teams' => '',
				'display_direction' => 'yes',
				'category' => '', 'categories' => '',
				'years' => '', 'year' => '', 'annee' => '', /*Pour les alumni */

			),
			$atts,
			( 'actif' === $status ? 'members_chart' : 'former_members_chart' )
			) );

		//Normalize values keys
		$units_id = (array) $args['units'];
		$teams_id = (array) $args['teams'];

		//-----------------------------------------------------------------------
		// Tenter de déterminer le contexte. Par défaut concaténer les équipes courantes
		//-----------------------------------------------------------------------
		// Si ni unité ni équipe n’est demandée, regarder si la page en cours n’en
		// précise pas par défaut. Sinon, prendre toutes les unités + la structure
		// principale.
		//-----------------------------------------------------------------------
		//  Si units et teams sont vides
		//	  Si unite(page en cours )  existe  ==>  units = unite(page en cours )
		//	  Si equipe(page en cours ) existe  ==>  teams = equipe(page en cours )
		//	  sinon
		//		  units = { ensemble des unites}+structure principale

		//-----------------------------------------------------------------------
		// On prend la direction de la structure principale si elle est demandée
		// si celle ci-est vide, alors on concatène la direction de l’ensemble
		// des unites demandée
		//-----------------------------------------------------------------------
		//  Si teams non vide
		//	  directeurs = vide  //Cela n’a pas de sens d'afficher des directeurs
		//  sinon
		//	  Si units comprend la structure principale
		//		  directeurs = directeurs(structure principale )
		//		  si directeurs est vide
		//			  directeurs = directeurs{units}
		//	  sinon
		//		  directeurs = directeurs{units}

		//-----------------------------------------------------------------------
		// On prend le nom de la structure principale si elle est demandée
		// sinon, et s'il n’y a qu’une unité demandée, on prend son nom.
		//-----------------------------------------------------------------------
		//  Si teams non vide
		//	  nom de la structure = vide
		//  sinon
		//	  Si units comprend la structure principale
		//		  nom de la structure = structure principale
		//	  sinon
		//		  nom de la structure = vide ou nom de l’unité s'il n’y en a qu’une

		//--------------------------------------------
		// 1 - Sélectionner les personnes
		//--------------------------------------------
		if ( ( empty( $teams_id ) ) and ( empty( $units_id ) ) ) {

			// On recherche une éventuelle équipe attachée à la page courante
			$team_of_current_page = $this->_team_of_curent_page();
			if ( ! empty( $team_of_current_page ) ) {
				$teams_id = array( $team_of_current_page => $team_of_current_page );
			} else {
				$units_id = array( App::MAIN_STRUCT_NO_UNIT => App::MAIN_STRUCT_NO_UNIT ) + Lib::secured_array_keys( $this->_accessData->get_units_name() );
			}
		}

		//Cela met à jour la liste des équipes, unités...
		list( $MonLaboPersons, ) = $this->_filter_persons_from_belongins( $teams_id, $units_id, $args['categories'], $status );

		//--------------------------------------------
		// 3 - Determiner le nom de l’unité à afficher
		//---------------------------------------------
		$unit_name = '';
		if ( ( empty( $teams_id ) ) and ( in_array( App::MAIN_STRUCT_NO_UNIT, $units_id ) ) ) {
			$options = get_option( 'MonLabo_settings_group1' );
			$unit_name = $options['MonLabo_nom'] . '  -  ' . $options['MonLabo_code'];
		} elseif ( ( empty( $teams_id ) ) and ( 1 === count( $units_id ) ) ) {
			$unit_id = $units_id[ array_key_first( $units_id ) ];
			$unit = new Unit( 'from_id', (int) $unit_id );
			$unit_name = $unit->get_name( $translate->get_lang() ) . '  -  ' . $unit->info->code;
		}

		//--------------------------------------------
		// 4 - Determiner les directeurs à afficher
		//---------------------------------------------
		$directors = array();
		if ( 'alumni' !== $status ) {
			if ( ( empty( $teams_id ) ) and ( 'yes' === $args['display_direction'] ) ) {
				if ( in_array( App::MAIN_STRUCT_NO_UNIT, $units_id ) ) {
					$directors = $this->_accessData->get_directors_info_for_an_unit( App::MAIN_STRUCT_NO_UNIT );
				}
				if ( empty( $directors ) ) {
					$directors = array();
					if ( ! empty( $units_id ) ) {
						foreach ( $units_id as $one_unit ) {
							$directors += $this->_accessData->get_directors_info_for_an_unit( (int) $one_unit );
						}
					}
				}
			}
		}
		$MonLabo_directors = new Persons_Group( $directors );

		//--------------------------------------------
		// 5 - Prendre toutes les équipes des unités indiquées si aucune équipe précisée
		//---------------------------------------------
		$teams_info = array();
		$all_teams_info = $this->_accessData->get_teams_info();

		if ( ( empty( $teams_id ) ) and ( ! empty( $units_id ) ) ) {
			foreach ( $units_id as $unit_id ) {
				$teams_id += $this->_accessData->get_teams_id_for_an_unit( (int) $unit_id );
			}
		}
		foreach ( $teams_id as $team_id ) {
			if ( array_key_exists( $team_id, $all_teams_info ) ) {
				$teams_info[ intval( $team_id ) ] = $all_teams_info[ $team_id ];
			}
		}

		//--------------------------------------------
		// 6 - Générer l’affichage
		//--------------------------------------------
		foreach ( $args['categories'] as $category ) {
			foreach ( $teams_id as $team_id ) {
				$persons_info = $this->_accessData->get_persons_info_for_a_category_in_a_team(
					$category,
					intval( $team_id ),
					$status
				);
				$MonLaboPersons = new Persons_Group( $persons_info );
				$MonLaboPersons->suppress_non_visible();

				// Les filtrer selon la date si l’on est en mode alumni
				if ( 'alumni' === $status ) {
					if ( ! empty( $args['years'] ) ) {
						if ( ! empty( $persons_info ) ) {
							$MonLaboPersons->filter_with_alumni_years( $args['years'] );
						}
					}
				}
				$persons_info = $MonLaboPersons->get_persons();
				$team_leaders_id = $this->_accessData->get_leaders_id_for_a_team( intval( $team_id ) );

				// On tague les leaders de chaque équipe
				if ( ! empty( $persons_info ) ) {
					foreach ( $persons_info as $the_id => $person ) {
						$person = (array) $person;
						$person['leader'] = ( ! empty( $team_leaders_id ) and in_array( $the_id, $team_leaders_id )  );
						$persons_info[ $the_id ] = (object ) $person;
					}
					$persons_by_category_and_team[ strval( $category ) ][ intval( $team_id ) ] = $persons_info;
				}
			}

		}
		$a_afficher .= $this->_html->persons_chart(
			$unit_name,
			$MonLabo_directors->get_persons(),
			$teams_info,
			$persons_by_category_and_team,
			$translate->get_lang()
		);

		return $a_afficher;
	}

	/**
	 * Add shortcode [perso_panel]
	 * Generate the personnal panel of the user belonging to the current page
  	 * ------------------------------------------------
  	 * Ex: [perso_panel] => Only way to use ( no parameters )
	 * @param mixed[]|string $atts  User defined attributes in shortcode tag
	 * @return string HTML code of the panel
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	 function perso_panel( $atts = array() ): string {
		$atts = (array) $atts;
		// Attributes
		$args = $this->_init_args(
			array(
				'person' => '',
			),
			$atts,
			'perso_panel'
		);
		if ( '' == $args['person'] ) {
			$person_id = $this->_person_of_curent_page();;
		} else {
			$person_id = intval( $args['person'] );
		}

		$person = new Person( 'from_id', $person_id );
		if ( ! $person->is_empty() ) {
			$mentors_info= $this->_accessData->get_mentors_info_for_a_person( $person->info->id );
			$students_info = $this->_accessData->get_students_info_for_a_person( $person->info->id );
			$translate = new Translate();
			return $this->_html->person_panel(
				$person->info,
				$mentors_info,
				$students_info,
				$translate->get_lang()
			);
		}
		return '';
	}

	/**
	 * Add shortcode [team_panel]
	 * enerate the team panel of the team belonging to the current page
  	 * ------------------------------------------------
  	 * Ex: [team_panel] => Only way to use (no parameters)
	 * @param mixed[]|string $atts  User defined attributes in shortcode tag
	 * @return string HTML code of the panel
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	function team_panel( $atts = array() ): string {
		$atts = (array) $atts;
		// Attributes
		$args = $this->_init_args(
			array(
				'team' => '',
			),
			$atts,
			'team_panel'
		);

		if ( '' == $args['team'] ) {
			$team_id = $this->_team_of_curent_page();;
		} else {
			$team_id = intval( $args['team'] );
		}

		if ( ! empty( $team_id ) ) {
			$team = new Team( 'from_id', $team_id );
			if ( ! $team->is_empty() ) {
				$leaders_info = $this->_accessData->get_leaders_info_for_a_team( $team->info->id );
				$translate = new Translate();
				$thematics_info = $this->_accessData->get_thematics_info_for_a_team( $team->info->id );
				return $this->_html->team_panel(
					$team->info,
					$leaders_info,
					$thematics_info,
					$translate->get_lang()
				);
			}
		}
		return '';
	}

	/**
	 * Add shortcode [former_members_list]
	 * get list of old members from teams, labs etc...
  	 * ------------------------------------------------
  	 * ex : [former_members_list title = 'Former PhDs and Postdocs' categories = 'postdocs,students'] => get all the alumni was PhD or PostDoct Members and print with title caption
	 * @param mixed[]|string $atts  User defined attributes in shortcode tag
	 * @return string HTML code of the list
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	function former_members_list( $atts = array() ): string {
		$atts = (array) $atts;
		$a_afficher = '';
		$translate = new Translate();
		// Attributes
		$args = $this->_sanitize_parameters( $this->_init_args(
			array(
				'team' => '', 'teams' => '',
				'unit'=> '', 'units' => App::MAIN_STRUCT_NO_UNIT, //structure mère par défaut
				'title' => '',
				'category' => '', 'categories' => '',
				'years' => '', 'year' => '', 'annee' => '',
				'external_link_title' => '',
				'person' => '', 'persons' => '',
			),
			$atts,
			'former_members_list'
		) );

		//--------------------------------------------
		// 1 - Sélectionner les personnes
		//--------------------------------------------
		if ( empty( $args['persons'] ) ) {
			$args['teams'] = $this->_fill_teams_with_team_of_current_page_if_empty( $args['teams'] );
			list( $MonLaboPersons, ) = $this->_filter_persons_from_belongins(
				$args['teams'],
				$args['units'],
				$args['categories'],
				'alumni'
			);
		} else {
			$MonLaboPersons = new Persons_Group( $this->_accessData->get_persons_info( 'alumni' ) );
			$MonLaboPersons->filter_with_ids( $args['persons'] );
		}

		//--------------------------------------------
		// 2 - Les filtrer selon la date
		//--------------------------------------------
		if ( ! empty( $args['years'] ) ) {
			$MonLaboPersons->filter_with_alumni_years( $args['years'] );
		}

		//--------------------------------------------
		// 3 - Générer l’affichage
		//--------------------------------------------
		if ( $MonLaboPersons->count() >0 ) {
			//2 - Lance l’affichage
			$MonLaboPersons->sort_by_name();
			$a_afficher .= $this->_html->persons_list(
				$args['title'],
				$args['title'],
				$MonLaboPersons->get_persons(),
				$translate->get_lang()
			);
		}
		return $a_afficher;
	}

	/**
	 * Add shortcode [former_members_table]
	 * get table of old members from teams, labs etc...
  	 * ------------------------------------------------
  	 * ex: [former_members_table title = 'Former PhDs and Postdocs' categories = 'postdocs,students'] => get all the alumni was PhD or PostDoct Members and print with title caption
	 * @param mixed[]|string $atts  User defined attributes in shortcode tag
	 * @return string HTML code of the table
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 */
	function former_members_table( $atts = array() ): string {
		$atts = (array) $atts;
		$a_afficher = '';
		$translate = new Translate();
		// Attributes
		$args = $this->_sanitize_parameters( $this->_init_args(
			array(
				'team' => '', 'teams' => '',
				'unit'=> '', 'units' => App::MAIN_STRUCT_NO_UNIT, //structure mère par défaut
				'title' => '',
				'category' => '', 'categories' => '',
				'years' => '', 'year' => '', 'annee' => '',
			),
			$atts,
			'former_members_table'
		) );

		//--------------------------------------------
		// 1 - Sélectionner les personnes
		//--------------------------------------------
		$args['teams'] = $this->_fill_teams_with_team_of_current_page_if_empty( $args['teams'] );
		list( $MonLaboPersons, ) = $this->_filter_persons_from_belongins(
			$args['teams'],
			$args['units'],
			$args['categories'],
			'alumni'
		);

		//--------------------------------------------
		// 2 - Les filtrer selon la date
		//--------------------------------------------
		if ( ! empty( $args['years'] ) ) {
			$MonLaboPersons->filter_with_alumni_years( $args['years'] );
		}

		//----------------------------------------------------
		// 3 - S'il n’y a qu’une équipe, récupérer sa couleur
		//----------------------------------------------------
		$color = $this->_color_of_team_if_only_one_team( $args['teams'] );

		//--------------------------------------------
		// 4 - Générer l’affichage
		//--------------------------------------------
		//Affichage du mode normal ( annuaire )
		$colum_titles = array( 'Nom', 'Equipe', 'Email', 'Tel' );
		if ( $MonLaboPersons->count() >0 ) {
			$a_afficher .= $this->_html->persons_table_normal(
				$args['title'],
				$colum_titles,
				$MonLaboPersons->sort_by_name()->get_persons(),
				$translate->get_lang(),
				$color,
				'alumni'
			);
		}
		return $a_afficher;
	}

	/**
	 * Add shortcode [teams_list]
	 *  get list of teams
  	 * ------------------------------------------------
  	 * ex: [teams_list thematic = '3'] => Get the list of teams for thematics 3
	 * @param mixed[]|string $atts  User defined attributes in shortcode tag
	 * @return string HTML code of the list
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	function teams_list( $atts = array() ): string {
		$atts = (array) $atts;
		$a_afficher = '';
		$translate = new Translate();
		// Attributes
		$args = $this->_sanitize_parameters( $this->_init_args(
			array(
				'thematic' => '', 'thematics' => '',
				'unit'=> '', 'units' => '',
				'group' => '', 'groups' => '',
				'teams_publications_page' => '',
				'team' => '', 'teams' => '',
			),
			$atts,
			'teams_list'
		) );

		//--------------------------------------------
		// 1 - Select teams
		//--------------------------------------------
		if ( empty( $args['teams'] ) ) {
			// On recherche une éventuelle unité attachée à la page courrante
			if ( ( empty( $args['units'] ) ) ) {
				$unit_of_current_page = $this->_unit_of_curent_page();
				$args['units'] = array();
				if ( ! empty( $unit_of_current_page ) ) {
					$args['units'] = array( $unit_of_current_page => $unit_of_current_page );
				}
			}
			if ( ! empty( $args['thematics'] ) ) {
				$args['groups'] =  $args['thematics'];
			}
			if ( ! isset( $args['groups'] ) ) {
				$args['groups'] = array();
			}
			//On filtre les équipes
			$MonLaboTeams = $this->_filter_teams_from_belongins( $args['groups'], $args['units'] );
		} else {
			$MonLaboTeams = new Teams_Group( $this->_accessData->get_teams_info( ) );
			$MonLaboTeams->filter_with_ids( $args['teams'] );
		}

		$groups_info = array();
		$teams_leaders = array();
		if ( $MonLaboTeams->count() > 0 ) {
			$teams_info = $MonLaboTeams->sort_by_name( $translate->get_lang() )->get_teams();
			foreach ( $teams_info as $key=>$team_info ) {
				$groups_info[ intval( $team_info->id ) ] = $this->_accessData->get_thematics_info_for_a_team( $team_info->id );
				$teams_leaders[ intval( $team_info->id ) ] = $this->_accessData->get_leaders_info_for_a_team( $team_info->id );

				//Si pas de logo définit, utiliser celui de l’unité
				if ( empty( $team_info->logo ) ) {
					if ( isset( $team_info->id_unit ) ) {
						$unit = new Unit( 'from_id', (int) $team_info->id_unit );
						if ( ! $unit->is_empty() and isset( $unit->info->logo ) )  {
							$teams_info[ $key ]->logo = $unit->info->logo;
						}
					}
				}
			}

			//--------------------------------------------
			// 2 - Générer l’affichage
			//--------------------------------------------
			$a_afficher .= $this->_html->teams_list(
				$teams_info,
				$teams_leaders,
				$groups_info,
				$args['teams_publications_page'],
				$translate->get_lang()
			);
		}
		return $a_afficher;
	}

	/**
	 * Add shortcode [dev_team_name]
	 * get team name from Descartes Pubbmed id for developpers...
	 * This function is not public and documented, only for developping purpose
  	 * ------------------------------------------------
  	 * ex : [dev_team_name descartes_publi_team_id = '3'] => get the english name of team 3
	 * @param mixed[]|string $atts  User defined attributes in shortcode tag
	 * @global $wpdb WordPress database access abstraction class
	 * @return string HTML code of the list
	 */
	function dev_team_name( $atts = array() ): string {
		$atts = (array) $atts;
		// @codeCoverageIgnoreStart
		// Attributes
		$args = $this->_init_args(
				array(
				'descartes_publi_team_id' => '',
			),
			$atts,
			'dev_team_name'
		);

		$args['descartes_publi_team_id'] = Lib::sanitize_text_or_int_field( $args['descartes_publi_team_id'] );

		global $wpdb;
		$team = $wpdb->get_results(
			$wpdb->prepare( "SELECT `name_en` FROM MonLabo_teams  WHERE `descartes_publi_team_id` = %s", $args['descartes_publi_team_id'] ),
			ARRAY_A
		);
		if ( isset( $team[array_key_first( $team )] ) and isset( $team[array_key_first( $team )]['name_en'] ) ) {
			return $team[array_key_first( $team )]['name_en'];
		}
		return '';
		// @codeCoverageIgnoreEnd

	}

	/**
	 * Add shortcode [dev_team_logo]
	 * get team logo from Descartes Pubbmed id for developpers...
	 * This function is not public and documented, only for developping purpose
  	 * ------------------------------------------------
  	 * ex :[dev_team_logo descartes_publi_team_id = '3'] => get the logo of team 3
	 * @param mixed[]|string $atts User defined attributes in shortcode tag
	 * @global $wpdb WordPress database access abstraction class
	 * @return string HTML code of the list
	 */
	function dev_team_logo( $atts = array() ): string {
		$atts = (array) $atts;
		// @codeCoverageIgnoreStart
		// Attributes
		$args = $this->_init_args(
				array(
				'descartes_publi_team_id' => '',
			),
			$atts,
			'dev_team_logo'
		);

		$args['descartes_publi_team_id'] = Lib::sanitize_text_or_int_field( $args['descartes_publi_team_id'] );

		global $wpdb;
		$team = $wpdb->get_results(
			$wpdb->prepare( "SELECT `logo` FROM MonLabo_teams  WHERE `descartes_publi_team_id` = %s", $args['descartes_publi_team_id'] ),
			ARRAY_A
		);
		if ( isset( $team[array_key_first( $team )] ) and isset( $team[array_key_first( $team )]['logo'] ) ) {
			return $team[array_key_first( $team )]['logo'];
		}
		return '';
		// @codeCoverageIgnoreEnd
	}
}
