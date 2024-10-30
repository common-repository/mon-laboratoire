<?php
namespace MonLabo\Frontend;

use MonLabo\Lib\{Translate, Lib, Options};
use MonLabo\Lib\Access_Data\{Access_Data};

//Next classes are used but not explicitely => need to suppress unnecessary error message PhanUnreferencedUseNormal.
use MonLabo\Lib\Person_Or_Structure\{Person, Team, Unit}; //@phan-suppress-current-line PhanUnreferencedUseNormal

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

/**
 * Class \MonLabo\Frontend\Shortcodes_Publications2
 *
 * @package
 */

class Shortcodes_Publications2 extends Shortcodes_Mother {
	/*
		getInstance()
		function publications_list				   ( $atts )
		function publications_list_web			   ( $atts,  $webservice_name = 'MonLabo_contact_webservices' )
		function _publications_list_prepare_url	   ( $args )
	*/

   /**
	* Singleton value
	* @var self|null
	*/
	protected static $_instance = null;

	/**
	* Méthode qui crée l’unique instance de la classe
	* si elle n’existe pas encore puis la retourne.
	* @return self
	*/
	public static function getInstance(): self {
		if ( null === self::$_instance ) {
			self::$_instance = new Shortcodes_Publications2();
		}
		return self::$_instance;
	}

	/**
	 * Translate the MonLabo persons id into the  the publication database persons id
	 * @param int[]|null $persons_id id of persons
	 * @param string $base_to_use Publication base to use ('hal' or 'DescartesPubli')
	 * @return int[] list of publications ID
	 * @access private
	 */
	private function _MonLaboPersonsIds_into_publicationServerAuthorsIds( $persons_id, string $base_to_use='hal' ): array {
		return $this->_MonLabo__item__ids_into_publicationServer__item__ids( $persons_id, 'person', $base_to_use );
	}

	/**
	 * Translate the MonLabo teams id into the  the publication database teams id
	 * @param int[]|null $teams_id id of teams
	 * @param string $base_to_use Publication base to use ('hal' or 'DescartesPubli')
	 * @return int[] list of publications ID
	 * @access private
	 */
	private function _MonLaboTeamsIds_into_publicationServerTeamsIds( $teams_id, string $base_to_use='hal' ): array {
		return $this->_MonLabo__item__ids_into_publicationServer__item__ids( $teams_id, 'team', $base_to_use );
	}

	/**
	 * Translate the MonLabo units id into the  the publication database units id
	 * @param int[]|null $units_id id of units
	 * @param string $base_to_use Publication base to use ('hal' or 'DescartesPubli')
	 * @return int[] list of publications ID
	 * @access private
	 */
	private function _MonLaboUnitsIds_into_publicationServerUnitsIds( $units_id, string $base_to_use='hal' ): array {
		return $this->_MonLabo__item__ids_into_publicationServer__item__ids( $units_id, 'unit', $base_to_use );
	}

	/**
	 * Common code for functions
	 * _MonLaboPersonsIds_into_publicationServerAuthorsIds(),
	 * _MonLaboTeamsIds_into_publicationServerTeamsIds(),
	 * and _MonLaboUnitsIds_into_publicationServerUnitsIds()
	 * @param int[]|null $items_id id of items (persons, teams or units)
	 * @param string $type_of_item 'person', 'team' or 'unit')
	 * @param string $base_to_use Publication base to use ('hal' or 'DescartesPubli')
	 * @return int[] list of publications ID
	 * @access private
	 */
	private function _MonLabo__item__ids_into_publicationServer__item__ids( $items_id, string $type_of_item, string $base_to_use ): array {
		$publi_db_ids = [];
		if ( ! empty( $items_id ) && ( is_array( $items_id ) ) ) {
			$prefix = '';
			if ( 'DescartesPubli' === $base_to_use ) {
				$prefix = 'descartes_publi_';
			} elseif ( 'hal' === $base_to_use ) {
				$prefix = 'hal_publi_';
			}
			$className = '\MonLabo\Lib\Person_Or_Structure\Person';
			$field_name = '';
			switch ( $type_of_item ) {
				case 'person':
					$field_name = $prefix . 'author_id';
					$className = '\MonLabo\Lib\Person_Or_Structure\Person';
					break;
				case 'team':
					$field_name = $prefix . 'team_id';
					$className = '\MonLabo\Lib\Person_Or_Structure\Team';
					break;
				case 'unit':
					$field_name = $prefix . 'unit_id';
					$className = '\MonLabo\Lib\Person_Or_Structure\Unit';
					break;
			}
			if ( ! empty( $field_name  )  ) {
				foreach ( $items_id as $item_id ) {
					$the_item = new $className( 'from_id', $item_id );
					if ( ( ! $the_item->is_empty() ) && ( ! empty( $the_item->info->{$field_name} ) ) ) {
						$publi_db_ids = array_merge( $publi_db_ids, $this->_prepare_multiple_values_variable_into_array( $the_item->info->{$field_name} ) );
					}
				}
			}
		}
		return array_unique( $publi_db_ids );
	}

	/**
	 * Sanitize all shortcodes parameters ($atts)
	 * @param mixed[] $atts shortcode parrameters
	 * @return array<string,string> parameters sanitized
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	private function _sanitize_publications_list_args( array $atts ) : array {
		// 1 - Récupération des paramètres et valeurs par défaut
		//------------------------------------------------------
		// get parameters
		$defaultArgs = [
			'title' => '__empty__', 		   //Optional title
			'titre' => '__empty__',
			'annee' => '', 'year' => '', 'years' => '',
			'units' => '', 'unit' => '',
			'teams' => '', 'team' => '',
			'persons' => '', 'person' => '',
			'lang' => '__empty__',
			'limit' => '',
			'offset' => '',
			'hal_struct' =>  '__empty__',
			'hal_idhal' =>  '__empty__',
			'hal_typepub' => '__empty__',
			'descartes_alias' =>  '__empty__',
			'descartes_auteurid' =>  '__empty__',
			'descartes_unite' =>  '__empty__',
			'descartes_equipe' =>  '__empty__',
			'descartes_typepub' =>  '__empty__',
			'descartes_nohighlight' =>  '__empty__',
			'descartes_orga_types' =>  '__empty__',
			'descartes_format' =>  '__empty__',
			'debug' =>  '__empty__',
			'base' => '',
		];

		$args = $this->_sanitize_parameters( $this->_init_args( $defaultArgs, $atts, 'publications_list' ) );

		// Curation des paramètres homonymes, surnuméraires ou incompatibles
		// -----------------------------------------------------------------
		$args['lang'] = sanitize_key( $args['lang'] );
		$args['debug'] = sanitize_key( $args['debug'] );
		$args['descartes_nohighlight'] = sanitize_key( $args['descartes_nohighlight'] );
		$args['descartes_orga_types'] = sanitize_key( $args['descartes_orga_types'] );
		$args['descartes_format'] = sanitize_key( $args['descartes_format'] );
		$args['descartes_alias'] = sanitize_text_field( $args['descartes_alias'] );
		$args['descartes_typepub'] = sanitize_text_field( $args['descartes_typepub'] );
		$args['descartes_auteurid'] = Lib::sanitize_text_or_int_field( $args['descartes_auteurid'] );
		$args['descartes_unite'] = Lib::sanitize_text_or_int_field( $args['descartes_unite'] );
		$args['descartes_equipe'] = Lib::sanitize_text_or_int_field( $args['descartes_equipe'] );

		if ( ! empty( $args['offset'] ) ) {
			$args['offset'] = strval( intval( $args['offset'] ) );
		} else {
			$args['offset'] = '';
		}
		if ( ! empty( $args['limit'] ) ) {
			$args['limit'] = strval( intval( $args['limit'] ) );
		} else {
			$args['limit'] = '';
		}
		$args['hal_idhal'] = Lib::sanitize_text_or_int_field( $args['hal_idhal'] );
		$args['hal_struct'] = Lib::sanitize_text_or_int_field( $args['hal_struct'] );
		$args['hal_typepub'] = sanitize_text_field( $args['hal_typepub'] );
		$args['base'] = Lib::sanitize_text_or_int_field( $args['base'] );
		return $args;
	}

	/**
	 * Testable code for Shortcode [publications_list]
	 * @param mixed[]|string $atts shortcode parrameters
	 * @param string $webservice_name Name of the class that will be used
	 * 		for CURL request (authorise to substitute CURL to a testing stub
	 * 		for unit test)
	 * @return string HTML code returned par shortcode
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	public function publications_list_web( $atts = [],  string $webservice_name = '\MonLabo\Frontend\Contact_Webservices' ): string {

		$atts = (array) $atts;

		// 0 - Initialisations
		//--------------------
		$a_afficher = '';

		$options = Options::getInstance();
		$options4 = get_option( 'MonLabo_settings_group4' );

		if ( !isset( $options4['MonLabo_hal_publi_style'] )
			or ( 'aucun' === $options->publication_server_type ) ) {
			return ''; //Exit because plugin is not initialized
		}
		$style_citation = $options4['MonLabo_hal_publi_style'];
		$translate = new Translate();
		$currentlang = $translate->get_lang();
		$webservice = new $webservice_name();

		$args = $this->_sanitize_publications_list_args( $atts );

		// Pouvoir utiliser le paramètre title en français ou anglais
		$title = ( '__empty__' === $args['title'] ) ? $args['titre'] : $args['title'];

		// Pouvoir forcer la langue
		if ( '__empty__' === $args['lang'] )   { $args['lang'] = $currentlang;	 }

		// Valeur par défaut du paramètre titre
		if ( ( 'DescartesPubli' === $options->publication_server_type )
				or ( ( 'both' === $options->publication_server_type )
					and ( 'descartespubli' === $args['base'] )
				)
			) {
			if ( 0 === strcmp ( $title, '__empty__' ) ) {
				$translate = new Translate( $args['lang'] );
				$title = $translate->tr__( 'Recent Publications' );
			}
		}

		//Debug
		if ( '__empty__' != $args['debug'] ) {
			$a_afficher .= "\nDEBUG MonLaboratoire : Request URL = " . $this->_publications_list_prepare_url( $args ) . "\n";
		}

		// 2 - On forge l’URL et demande le mode de traitement de cette URL
		// ----------------------------------------------------------------
		$url = $this->_publications_list_prepare_url( $args );
		if ( '__empty__' != $args['debug'] ) {
			$a_afficher .= "\nDEBUG MonLaboratoire : Request URL = " . $url . "\n";
		}
		
		// 3 - Traitement de l’URL
		//------------------------
		$contenu = $webservice->webpage_get_content( $url );
		$args['base'] = $this->_get_custom_param( $args, 'base' );
		$publications = '';
		switch ( $this->_get_base_to_use( $args['base'] ) ) {
			case 'hal':
				$ApiHallToHtml = new Api_Pub_Repo\Api_Hal_publications_list( $contenu );
				$publications =  $ApiHallToHtml->format_publi( $style_citation, $args['lang'] ) . $contenu ;
				break;
			
			case 'DescartesPubli':
				if ( ! empty( $contenu ) ) {
					$contenu_explose = explode ( '<body>', $contenu );
					if ( count( $contenu_explose ) > 1 ) {
						$contenu_explose = explode ( '</body>', $contenu_explose[1] );
					}
					$publications = $contenu_explose[0];
				}
				break;
		}


		// 4 - affichage des publications
		//-------------------------------
		if ( strlen( $publications ) > 10 ) {
			//Un titre avec '__empty__' doit être vide
			if ( ( ! empty( $title ) ) && ( 0 !== strcmp ( $title, '__empty__' ) ) ) {
				$a_afficher .= '<h1>'. $title .'</h1>';
			}
			$a_afficher .= $publications;
		}

		return $a_afficher;
	}

	/**
	 * Configure base to use according to options and parameters
	 * @param string $forced_base parameter 'base' in shortcode
	 * @return string value that is 'DescartesPubli' or 'hal' or ''
	 * @access private
	 */
	private function _get_base_to_use( string $forced_base = '' ) : string {
		$options = Options::getInstance();
		/*                       +------------------------------------------------+
		    valeur de            | $options->publication_server_type              |
		    $base_to_use         +----------------+-------+----------------+------+
		                         | DescartesPubli |  hal  |     both       |  ''  |
		+-------+----------------+----------------+-------+----------------+------+
		|       |       ''       | DescartesPubli    hal        hal           ''  |
		|       +----------------+                                                +
		| $forc | descartespubli | DescartesPubli    hal    DescartesPubli    ''  |
		| ed_   +----------------+                                                +
		| base  |      hal       | DescartesPubli    hal        hal           ''  |
		+-------+----------------+----------------+-------+----------------+------+*/
		$base_to_use = $options->publication_server_type ;
		if ( 'both' === $base_to_use ) {
			$base_to_use = ( '' === $forced_base ? 'hal': $forced_base );
		}
		if ( 'descartespubli' === $base_to_use ) {
			$base_to_use = 'DescartesPubli';
		}
		return $base_to_use;
	}

	/**
	 * Shortcode [publications_list]
	 * Cannot be tested
	 * @param mixed[]|string $atts  shortcode parrameters
	 * @return string HTML code returned par shortcode
	 */
	function publications_list( $atts = [] ): string {
		$atts = (array) $atts;
		// @codeCoverageIgnoreStart
		return $this->publications_list_web( $atts );
		// @codeCoverageIgnoreEnd
	}


	/**
	 * Extract a param from an array
	 * @param string[]|null $custom_array array where to extract
	 * @param string $parameter_name Name of the parameter to extract
	 * @return string Parameter value or '__empty__'
	 */
	private function _get_custom_param( $custom_array, string $parameter_name ): string {
		$param = '__empty__';
		if ( ( null != $custom_array ) && ( isset( $custom_array[ $parameter_name ] ) ) ){
			$param = $custom_array[ $parameter_name ];
		}
		return $param;
	}

	///////////////////////////////////////////////////////////////////////////////////////////
	/**
	 * URL request generator for shortcode [publications_list]
	 * @param array<string,string|int[]|string[]> $args arguments in a array
	 * - string $years Years interval or enumeration
	 * - int[] $units_id Units id
	 * - int[] $teams_id Teams id
	 * - int[] $persons_id Persons id
	 * - string $lang Language
	 * - string $limit Limit number of answers
	 * - string $offset Useful with $limit. Can show following answers
	 * - string $HALstruct custom parameters for HAL
	 * - string $hal_idhal custom parameters for HAL
	 * - string $hal_typepub custom parameters for HAL
	 * - strings 'hal_struct', 'hal_idhal', 'hal_typepub' custom parameters for HAL
	 * - strings 'descartes_alias', 'descartes_auteurid', 'descartes_equipe', 'descartes_unite', 'descartes_typepub',
	 *          'descartes_nohighlight', 'descartes_orga_types', 'descartes_format'
	 *           custom parameters for descartesPubli
	 * - string $base Useful with $limit. Can show following answers
	 * @return string $url : Request URI generated
	 * @access private
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	private function _publications_list_prepare_url( array $args ): string {

		$mandadory_params = [ 'years', 'lang', 'limit', 'offset', 'base',
		 'hal_struct', 'hal_idhal', 'hal_typepub', 'debug',
		 'descartes_alias', 'descartes_auteurid', 'descartes_equipe', 'descartes_unite', 'descartes_typepub',
		 'descartes_nohighlight', 'descartes_orga_types', 'descartes_format' ];
		foreach ( $mandadory_params as $one_param ) {
			$args[ $one_param ] = $this->_get_custom_param( $args, $one_param );
		}
		//'units', 'teams', 'persons',
		$units_id = $args['units'];
		$teams_id = $args['teams'];
		$persons_id = $args['persons'];

		$accessData = new Access_Data();


		// Les valeur descartes_custom viennent écraser les autres
		if (
			'__empty__' !== $args['descartes_alias'] ||
			'__empty__' !== $args['descartes_auteurid'] ||
			'__empty__' !== $args['descartes_equipe'] ||
			'__empty__' !== $args['descartes_unite']
		) {
			$units_id = [];
			$teams_id = [];
			$persons_id = [];
		}

		//-----------------------------
		// 1 - Récupération des options
		//-----------------------------
		$base_to_use = $this->_get_base_to_use( $args['base'] );

		//-------------------------------
		// 2 - Détermination de la langue
		//-------------------------------
		if ( empty( $args['lang'] ) ) {
			$translate = new Translate( get_locale() );
		} else { //La langue est forcée
			$translate = new Translate( $args['lang'] );
		}
		$languageHAL = $translate->switch_lang( 'Anglais', 'Francais' );

		//-------------------------------------
		// 3 - Si persons, teams ou units = '*'
		//-------------------------------------
		if ( in_array( '*' , $persons_id ) ) {
			$persons_id = Lib::secured_array_keys( $accessData->get_persons_info() );
		}
		if ( in_array( '*' , $teams_id ) ) {
			$teams_id = Lib::secured_array_keys( $accessData->get_teams_info() );
		}
		if ( in_array( '*' , $units_id ) ) {
			$units_id = Lib::secured_array_keys( $accessData->get_units_info() );
		}

		//----------------------------------
		// 4 - Détermination du type de page
		//----------------------------------
		if ( empty( $teams_id ) && empty( $units_id ) && empty( $persons_id ) ) {
			//Déterminer a qui apartient la page courante
			$page_person_id = $this->_person_of_curent_page();
			$page_mode = 'user_page';
			if ( empty( $page_person_id ) ) {
				$page_team_id = $this->_team_of_curent_page();
				$page_mode = 'team_or_unit_page';
				if ( empty( $page_team_id ) ) {
					$page_unit_id = $this->_unit_of_curent_page();
					if ( empty( $page_unit_id ) ) {
						$page_mode = 'main_structure_page';
					} else {
						$units_id = [ $page_unit_id ];
					}
				} else {
					$teams_id = [ $page_team_id ];
				}
			} else {
				$persons_id = [ $page_person_id ];
			}
		} else {
			$page_mode = empty( $persons_id ) ? 'team_or_unit_page' :  'user_page';
		}

		//----------------------------------------------------------------
		// 5.1 Traitement si le serveur de publication est Paris Descartes
		//----------------------------------------------------------------
		if ( 'DescartesPubli' === $base_to_use ) {
			return $this->_publications_list_prepare_url_Descartes( $args, $languageHAL, $units_id,
					$teams_id, $persons_id, $page_mode  );

		//----------------------------------------------------
		// 5.2 Traitement si le serveur de publication est HAL
		//----------------------------------------------------
		} elseif ( 'hal' === $base_to_use ) {
			return $this->_publications_list_prepare_url_Hal( $args, $languageHAL, $units_id,
					$teams_id, $persons_id, $page_mode  );			
		}
		return '';
	}

	///////////////////////////////////////////////////////////////////////////////////////////
	/**
	 * URL request generator for shortcode [publications_list] specific to HAL repo
	 * @param array<string,string|int[]|string[]> $args arguments in a array
	 * - string $years Years interval or enumeration
	 * - int[] $units_id Units id
	 * - int[] $teams_id Teams id
	 * - int[] $persons_id Persons id
	 * - string $lang Language
	 * - string $limit Limit number of answers
	 * - string $offset Useful with $limit. Can show following answers
	 * - string $HALstruct custom parameters for HAL
	 * - string $hal_idhal custom parameters for HAL
	 * - string $hal_typepub custom parameters for HAL
	 * - strings 'hal_struct', 'hal_idhal', 'hal_typepub' custom parameters for HAL
	 * - strings 'descartes_alias', 'descartes_auteurid', 'descartes_equipe', 'descartes_unite', 'descartes_typepub',
	 *          'descartes_nohighlight', 'descartes_orga_types', 'descartes_format'
	 *           custom parameters for descartesPubli
	 * - string $base Useful with $limit. Can show following answers
	 * @param string $languageHAL language to use : Anglais of Français
	 * @param int[] $units_id list of units ID
	 * @param int[] $teams_id list of teams ID
	 * @param int[] $persons_id list of persons ID
	 * @param string $page_mode 'team_or_unit_page' or 'user_page' or 'main_structure_page'
	 * @return string $url : Request URI generated
	 * @access private
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	private function _publications_list_prepare_url_Hal(
		array $args,
		string $languageHAL,
		array $units_id,
		array $teams_id,
		array $persons_id,
		string $page_mode
	) : string {
		
		$options1 = get_option( 'MonLabo_settings_group1' );

		//Construction de la la requête de base
		//-------------------------------------
		$apiHalQuery = new Api_Pub_Repo\Api_Hal_Query();
		$apiHalQuery->years_POST_query( $args['years'] );

		//Calcul du paramètre identifiant l'équipe, la personne ou la structure
		//---------------------------------------------------------------------
		$HAL_struct_ids = [];
		$HAL_person_ids = [];

		//On ecrase les autres paramètres avec les paramètres experts
		if ( '__empty__' != $args['hal_idhal'] ) {
			$HAL_person_ids= $this->_prepare_multiple_values_variable_into_array( $args['hal_idhal'] );
		} elseif ( '__empty__' != $args['hal_struct'] ) {
			$HAL_struct_ids = $this->_prepare_multiple_values_variable_into_array( $args['hal_struct'] );
		} else {

			//En cas d’absence de paramètres experts
			if ( 'user_page' === $page_mode ) { //Personne
					$HAL_person_ids = $this->_MonLaboPersonsIds_into_publicationServerAuthorsIds( $persons_id, 'hal' );
			} else {

				//Determine la liste des ID struct HAL a utiliser
				if ( 'team_or_unit_page' === $page_mode ) { //Equipe
					$accessData = new Access_Data();
					$HAL_struct_ids =	$this->_MonLaboTeamsIds_into_publicationServerTeamsIds( $teams_id, 'hal' );
					// Si pas de hal_publi_team_id définis pour les équipes
					if ( ( ! empty( $teams_id ) ) && ( empty( $HAL_struct_ids ) ) ) {
						//Retourner les publications des membres de l’équipe
						foreach ( $teams_id as $team_id ) {
							$HAL_person_ids = array_merge( $HAL_person_ids, $this->_MonLaboPersonsIds_into_publicationServerAuthorsIds( $accessData->get_persons_id_for_a_team( (int) $team_id ), 'hal' ) );
						}
					}
					$unitHAL_ids = $this->_MonLaboUnitsIds_into_publicationServerUnitsIds( $units_id, 'hal' );
					$HAL_struct_ids = array_merge( $HAL_struct_ids, $unitHAL_ids );
					// Si pas de hal_publi_team_id définis pour les unités
					if ( ( ! empty( $units_id ) ) && ( empty( $unitHAL_ids ) ) ) {
						//Retourner les publications des membres de l’unité
						foreach ( $units_id as $unit_id ) {
							$HAL_person_ids = array_merge(
								$HAL_person_ids,
								$this->_MonLaboPersonsIds_into_publicationServerAuthorsIds(
									Lib::secured_array_keys(
										$accessData->get_persons_info_for_an_unit( (int) $unit_id )
									),
									'hal' )
								);
						}
					}
				} elseif ( 'main_structure_page' === $page_mode ) {
					if ( ! empty( $options1['MonLabo_hal_publi_struct_id'] ) ) {
						$HAL_struct_ids = $this->_prepare_multiple_values_variable_into_array( $options1['MonLabo_hal_publi_struct_id'] );
					}
				}
				//Nettoyage des ID HAL non numériques
				if ( ! empty( $HAL_struct_ids ) ) {
					foreach ( $HAL_struct_ids as $key => $value ) {
						if ( ! is_numeric( $value ) ) {
							unset( $HAL_struct_ids[ $key ] );
						}
					}
				}
			}
		}
		$apiHalQuery->limit_POST_query( $args['limit'] );
		//Les paramètres idHal et struct sont incompatibles.
		if ( ! empty( $HAL_person_ids ) ) {
			$apiHalQuery->idHal_POST_query( $HAL_person_ids );
		} elseif ( ! empty( $HAL_struct_ids ) ) {
			$apiHalQuery->idHalStruct_POST_query( $HAL_struct_ids );
		} else {
			return '';
		}
		$apiHalQuery->doctype_POST_query( $this->_prepare_multiple_values_variable_into_array( $args['hal_typepub'] ) );
		return $apiHalQuery->get_base_url() . http_build_query ( $apiHalQuery->POST_query );
	}

	/**
	 * URL request generator for shortcode [publications_list]
	 * @param array<string,string|int[]|string[]> $args arguments in a array
	 * - string $years Years interval or enumeration
	 * - int[] $units_id Units id
	 * - int[] $teams_id Teams id
	 * - int[] $persons_id Persons id
	 * - string $lang Language
	 * - string $limit Limit number of answers
	 * - string $offset Useful with $limit. Can show following answers
	 * - string $HALstruct custom parameters for HAL
	 * - string $hal_idhal custom parameters for HAL
	 * - string $hal_typepub custom parameters for HAL
	 * - strings 'hal_struct', 'hal_idhal', 'hal_typepub' custom parameters for HAL
	 * - strings 'descartes_alias', 'descartes_auteurid', 'descartes_equipe', 'descartes_unite', 'descartes_typepub',
	 *          'descartes_nohighlight', 'descartes_orga_types', 'descartes_format'
	 *           custom parameters for descartesPubli
	 * - string $base Useful with $limit. Can show following answers
	 * @param string $languageHAL language to use : Anglais of Français
	 * @param int[] $units_id list of units ID
	 * @param int[] $teams_id list of teams ID
	 * @param int[] $persons_id list of persons ID
	 * @param string $page_mode 'team_or_unit_page' or 'user_page' or 'main_structure_page'
	 * @return string $url : Request URI generated
	 * @access private
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	private function _publications_list_prepare_url_Descartes(
		array $args,
		string $languageHAL,
		array $units_id,
		array $teams_id,
		array $persons_id,
		string $page_mode
	) : string {
		$apiDescartesQuery = new Api_Pub_Repo\Api_Descartes_Query( $page_mode );
		$apiDescartesQuery->language_POST_query( $languageHAL );
		$apiDescartesQuery->limit_POST_query( $args['limit'] );
		$apiDescartesQuery->offset_POST_query( $args['offset'] );
		if ( '*' === $args['descartes_equipe'] ) {  //Get all the descartes_publi_team_id list of teams configured in MonLabo
			$accessData = new Access_Data();
			$all_teams_id = Lib::secured_array_keys( $accessData->get_teams_info() );
			$descartes_team_ids   = $this->_MonLaboTeamsIds_into_publicationServerTeamsIds( $all_teams_id, 'DescartesPubli' );
			if ( ! empty( $descartes_team_ids ) ) {
				$args['descartes_equipe'] = Lib::secured_implode( '|', $descartes_team_ids );
			}
		}
		$apiDescartesQuery->expert_param_POST_query(
			$args['descartes_alias'],
			$args['descartes_auteurid'],
			$args['descartes_equipe'],
			$args['descartes_unite'],
			$args['descartes_typepub'],
			$args['descartes_nohighlight'],
			$args['descartes_orga_types'],
			$args['descartes_format']
		);
		$apiDescartesQuery->years_POST_query( $args['years'] );
		$apiDescartesQuery->debug_POST_query( $args['debug'] );
		if ( 'team_or_unit_page' === $page_mode ) {
			$descartes_team_ids   = $this->_MonLaboTeamsIds_into_publicationServerTeamsIds( $teams_id, 'DescartesPubli' );
			$descartes_unit_ids   = $this->_MonLaboUnitsIds_into_publicationServerUnitsIds( $units_id, 'DescartesPubli' );
			$apiDescartesQuery->equipes_and_unite_POST_query( $descartes_team_ids, $descartes_unit_ids );
		}
			
		if ( 'user_page' === $page_mode ) {
			$descartes_ids = $this->_MonLaboPersonsIds_into_publicationServerAuthorsIds( $persons_id, 'DescartesPubli' );
			$apiDescartesQuery->persons_POST_query( $descartes_ids );

		} elseif ( 'main_structure_page' === $page_mode ) {
			//On retourne les publications de toutes les personnes
			$persons = '';
			$accessData = new Access_Data();
			$all_persons_id = Lib::secured_array_keys( $accessData->get_persons_info() );
			if ( ! empty( $all_persons_id ) ) { $persons = Lib::secured_implode( '|', $all_persons_id ); }
			$persons_id	= $this->_prepare_multiple_values_variable_into_array( $persons );
			$descartes_ids = $this->_MonLaboPersonsIds_into_publicationServerAuthorsIds( $persons_id, 'DescartesPubli' );
			$apiDescartesQuery->persons_POST_query( $descartes_ids );
		}
		
		if ( empty( $apiDescartesQuery->get_base_url() ) ) {
			return '';
		}
		return $apiDescartesQuery->get_base_url() . '?' . http_build_query ( $apiDescartesQuery->POST_query );
	}
}

?>
