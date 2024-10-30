<?php
namespace MonLabo\Frontend;

use MonLabo\Lib\{Translate, Lib, Options};
use MonLabo\Lib\Access_Data\{Access_Data};
//Next classes are used but not explicitely => need to suppress unnecessary error message PhanUnreferencedUseNormal.
use MonLabo\Lib\Person_Or_Structure\{Person, Team, Unit}; //@phan-suppress-current-line PhanUnreferencedUseNormal

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

/**
 * Class \MonLabo\Frontend\Shortcodes_Publications
 *
 * @package
 */

class Shortcodes_Publications extends Shortcodes_Mother {
	/*
		getInstance()
		function _suppress_bibtex_value_limiter( $string )

		function publications_list				   ( $atts )
		function publications_list_web			   ( $atts,  $webservice_name = 'MonLabo_contact_webservices' )
		function _publications_list_prepare_url	   ( $args )

		function _parse_haltools_response ( $hal_content = '' )
		function _format_publi			( $format = 'ieee', $data_publi, $currentlang )
		function _bibtex_to_ieee_authors  ( $authors_bib_string )
		function _bibtex_to_apa_authors   ( $authors_bib_string )
		function _format_bib_field		( $array, $field, $format )

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
			self::$_instance = new Shortcodes_Publications();
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
		$publi_db_ids = array();
		if ( ! empty( $items_id ) and ( is_array( $items_id ) ) ) {
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
					$className = "\MonLabo\Lib\Person_Or_Structure\Person";
					break;
				case 'team':
					$field_name = $prefix . 'team_id';
					$className = "\MonLabo\Lib\Person_Or_Structure\Team";
					break;
				case 'unit':
					$field_name = $prefix . 'unit_id';
					$className = "\MonLabo\Lib\Person_Or_Structure\Unit";
					break;
			}
			if ( ! empty( $field_name  )  ) {
				foreach ( $items_id as $item_id ) {
					$the_item = new $className( 'from_id', $item_id );
					if ( ( ! $the_item->is_empty() ) and ( ! empty( $the_item->info->{$field_name} ) ) ) {
						$publi_db_ids = array_merge( $publi_db_ids, $this->_prepare_multiple_values_variable_into_array( $the_item->info->{$field_name} ) );
					}
				}
			}
		}
		return array_unique( $publi_db_ids );
	}

	/**
	 * Suppress surrounding chars of a string
	 * as quotes, double quotes, braket or double bracket.
	 * @param string $string string to process
	 * @return string cleaned string
	 * @access private
	 */
	private function _suppress_bibtex_value_limiter( string $string ): string {
		$string = trim( $string );
		$size = strlen( $string )-1;
		if ( $size>0 ) {
			if ( $string[0] === $string[ $size ] ) {
				if ( ( '"' === $string[0] ) or ( "'" === $string[0] ) ) {
					$string = substr( $string, 1, -1 );  //Si "chaine" ou 'chaine'
				}
				return trim( $string );
			}
			if ( ( '{' === $string[0] ) and ( '}' === $string[ $size ] ) ) {
				if ( $size>3 ) {
					if ( ( '{' === $string[1] ) and ( '}' === $string[ $size-1 ] ) ) {
						$string = substr( $string, 1, -1 ); //Si {{chaine}}
					}
				}
				$string = substr( $string, 1, -1 ); //Si {chaine} ou {{chaine}}
			}
		}
		return trim( $string );
	}

	/**
	 * Build HAL query
	 * @param string $years_interval Interval of years
	 * @param string $languageHAL language for desired HAL answer
	 * @param string $style_citation style of desired answer ('hal' or '')
	 * @return array{string,string[],string} array($api_base_url, $POST_query, $request_mode
	 * 	- string $api_base_url : base HAL URL for request
	 * 	- string[] $POST_query : table of POST option necessary in query
	 * 	- string $request_mode : mode of request ('native_html_display' or 'generate_html_from_bibtext')
	 * @access private
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	function _build_HAL_POST_query(
		string $years_interval,
		string $languageHAL,
		string $style_citation
	): array {
		$POST_query = array();

		//Calcul des paramètres des années
		//--------------------------------
		if ( strlen( $years_interval ) > 0 ) {
			$anneeMinAndMax = explode( '-', $years_interval );
			$anneeMin = $anneeMinAndMax[0];
			if ( count( $anneeMinAndMax ) > 1 ) {
				$anneeMax = $anneeMinAndMax[1];
			} else {
				$anneeMax = $anneeMinAndMax[0];
			}
			if ( ! empty( $anneeMin ) ) {
				$POST_query['annee_publideb'] = "$anneeMin";
				if ( ! empty( $anneeMax ) ) {
					$POST_query['annee_publifin'] = "$anneeMax";
				}
			} else {
				if ( ! empty( $anneeMax ) ) {
					$POST_query['annee_publifin'] = "$anneeMax";
				}
			}
		}

		//Calcul des autres paramètres de requête
		//---------------------------------------
		$POST_query['tri_exp'] = 'typdoc';
		$POST_query['tri_exp2'] = 'annee_publi';
		$POST_query['tri_exp3'] = 'auteur_exp';
		if ( 0 === strcmp( $style_citation, 'hal' ) ) {
			$POST_query['CB_ref_biblio'] = 'oui';
			$POST_query['ordre_aff'] = 'TA';
			$POST_query['Fen'] = 'Aff';
			$POST_query['langue'] = $languageHAL;
			$api_base_url = 'https://haltools.archives-ouvertes.fr/Public/afficheRequetePubli.php?';
			$request_mode = 'native_html_display';
		} else {
			$POST_query['CB_accent_latex'] = 'non';
			$POST_query['format_export'] = 'bibtex';
			$POST_query['langue'] = 'Anglais';
			$api_base_url = 'https://haltools.inria.fr/Public/exportPubli.php?';
			$request_mode = 'generate_html_from_bibtext';
		}
		return array( $api_base_url, $POST_query, $request_mode );
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
		$args = $this->_sanitize_parameters( $this->_init_args(
			array(
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
			),
			$atts,
			'publications_list'
		));

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
	public function publications_list_web( $atts = array(),  string $webservice_name = '\MonLabo\Frontend\Contact_Webservices' ): string {

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
		if ( ( 'DescartesPubli' === $options->publication_server_type)
				or ( ( 'both' === $options->publication_server_type )
					and ( 'descartespubli' === $args['base'] )
				)
			) {
			if ( 0 === strcmp ( $title, '__empty__' ) ) {
				$translate = new Translate( $args['lang'] );
				$title = $translate->tr__( 'Recent Publications' );
			}
		}

		// 2 - On forge l’URL et demande le mode de traitement de cette URL
		// ----------------------------------------------------------------
		list( $request_mode, $url ) = $this->_publications_list_prepare_url( $args );
		
		if ( '__empty__' != $args['debug'] ) {
			$a_afficher .= "\nDEBUG MonLaboratoire : Request URL = " . $url . "\n";
		}
		
		// 3 - Traitement de l’URL
		//------------------------
		switch ( $request_mode ) {
			case 'native_html_display':
				$contenu = $webservice->webpage_get_content( $url );
				//Aller chercher le code au sein des balises <body></body> si elles existent
				$publications = '';
				if ( ! empty( $contenu ) ) {
					$contenu_explose = explode ( '<body>', $contenu );
					if ( count( $contenu_explose ) > 1 ) {
						$contenu_explose = explode ( '</body>', $contenu_explose[1] );
					}
					$publications = $contenu_explose[0];
				}
				break;

			case 'generate_html_from_bibtext':
				$hal_content = $webservice->webpage_get_content( $url );
				$publi = $this->_parse_haltools_response( $hal_content );
				$publications = $this->_format_publi( $style_citation, $publi, $args['lang'] );
				break;

			default:
				$publications = '';
				break;
		}

		// 4 - affichage des publications
		//-------------------------------
		if ( strlen( $publications ) > 10 ) {
			//Un titre avec '__empty__' doit être vide
			if ( ( ! empty( $title ) ) and ( 0 !== strcmp ( $title, '__empty__' ) ) ) {
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
		    valeur de            |  $options->publication_server_type             |
		    $base_to_use         +----------------+-------+----------------+------+
		                         | DescartesPubli |  hal  |     both       |  ''  |
		+-------+----------------+----------------+-------+----------------+------+
		|       |       ''       | DescartesPubli    hal        hal           ''  |
		|       +----------------+                                                +
		| $forc | descartespubli | DescartesPubli    hal    DescartesPubli    ''  |
		| ed_   +----------------+                                                +
		| base  |      hal       | DescartesPubli    hal        hal           ''  |
		+-------+----------------+----------------+-------+----------------+------+*/
		$base_to_use = $options->publication_server_type;
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
	function publications_list( $atts = array() ): string {
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
		if ( ( null != $custom_array ) and ( isset( $custom_array[ $parameter_name ] ) ) ){
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
	 * @return string[] array( $request_mode, $url )
	 * 	- string $request_mode : mode of request ('native_html_display' or 'generate_html_from_bibtext')
	 *  - string $url : Request URI generated
	 * @access private
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	private function _publications_list_prepare_url( array $args ): array {
		$mandadory_params = array( 'years', 'lang', 'limit', 'offset', 'base',
		 'hal_struct', 'hal_idhal', 'hal_typepub', 'debug',
		 'descartes_alias', 'descartes_auteurid', 'descartes_equipe', 'descartes_unite', 'descartes_typepub',
		 'descartes_nohighlight', 'descartes_orga_types', 'descartes_format');
		foreach ( $mandadory_params as $one_param ) {
			$args[ $one_param ] = $this->_get_custom_param( $args, $one_param );
		}
		//'units', 'teams', 'persons',
		$units_id = $args['units'];
		$teams_id = $args['teams'];
		$persons_id = $args['persons'];

		$accessData = new Access_Data();
		$POST_query = array();

		// Les valeur descartes_custom viennent écraser les autres
		if (
			   ( '__empty__' !== $args['descartes_alias'] )
			or ( '__empty__' !== $args['descartes_auteurid'] )
			or ( '__empty__' !== $args['descartes_equipe'] )
			or ( '__empty__' !== $args['descartes_unite'] )
		) {
			$units_id = array(); $teams_id = array(); $persons_id = array();
		}

		//-----------------------------
		// 1 - Récupération des options
		//-----------------------------
		$options4 = get_option( 'MonLabo_settings_group4' );
		$style_citation = $options4['MonLabo_hal_publi_style'];

		$base_to_use = $this->_get_base_to_use( $args['base'] );

		$request_mode = '';

		//-------------------------------
		// 2 - Détermination de la langue
		//-------------------------------
		if ( empty( $args['lang'] ) ) {
			$translate = new Translate( get_locale() );
		} else { //La langue est forcée
			$translate = new Translate( $args['lang'] );
		}
		$languageHAL = $translate->switch_lang( 'Anglais', 'Francais');

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
		$api_base_url = '';
		if ( empty( $teams_id ) and empty( $units_id ) and empty( $persons_id ) ) {
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
						$units_id = array( $page_unit_id );
					}
				} else {
					$teams_id = array( $page_team_id );
				}
			} else {
				$persons_id = array( $page_person_id );
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
			$options1 = get_option( 'MonLabo_settings_group1' );

			//Construction de la la requête de base
			//-------------------------------------
			list( $api_base_url, $POST_query, $request_mode ) = $this->_build_HAL_POST_query( $args['years'], $languageHAL, $style_citation );
			//Calcul du paramètre identifiant l'équipe, la personne ou la structure
			//---------------------------------------------------------------------
			$HAL_ids = array();
			$idHal = array();

			//On ecrase les autres paramètres avec les paramètres experts
			if ( '__empty__' !== $args['hal_idhal'] ) {
				$idHal= $this->_prepare_multiple_values_variable_into_array( $args['hal_idhal'] );
			} elseif ( '__empty__' !== $args['hal_struct'] ) {
				$HAL_ids = $this->_prepare_multiple_values_variable_into_array( $args['hal_struct'] );
			} else {

				//En cas d’absence de paramètres experts
				if ( 'user_page' === $page_mode ) { //Personne
						$idHal = $this->_MonLaboPersonsIds_into_publicationServerAuthorsIds( $persons_id, $base_to_use );
				} else {

					//Determine la liste des ID struct HAL a utiliser
					if ( 'team_or_unit_page' === $page_mode ) { //Equipe
						$HAL_ids =	$this->_MonLaboTeamsIds_into_publicationServerTeamsIds( $teams_id, $base_to_use );
						// Si pas de hal_publi_team_id définis pour les équipes
						if ( ( ! empty( $teams_id ) ) and ( empty( $HAL_ids ) ) ) {
							//Retourner les publications des membres de l’équipe
							foreach ( $teams_id as $team_id ) {
								$idHal = array_merge( $idHal, $this->_MonLaboPersonsIds_into_publicationServerAuthorsIds( $accessData->get_persons_id_for_a_team( (int) $team_id ), $base_to_use ) );
							}
						}
						$unitHAL_ids = $this->_MonLaboUnitsIds_into_publicationServerUnitsIds( $units_id, $base_to_use );
						$HAL_ids = array_merge( $HAL_ids, $unitHAL_ids );
						// Si pas de hal_publi_team_id définis pour les unités
						if ( ( ! empty( $units_id ) ) and ( empty( $unitHAL_ids ) ) ) {
							//Retourner les publications des membres de l’unité
							foreach ( $units_id as $unit_id ) {
								$idHal = array_merge(
									$idHal,
									$this->_MonLaboPersonsIds_into_publicationServerAuthorsIds(
										Lib::secured_array_keys(
											$accessData->get_persons_info_for_an_unit( (int) $unit_id )
										),
										$base_to_use )
									);
							}
						}
					} elseif ( 'main_structure_page' === $page_mode ) {
						if ( ! empty( $options1['MonLabo_hal_publi_struct_id'] ) ) {
							$HAL_ids = $this->_prepare_multiple_values_variable_into_array( $options1['MonLabo_hal_publi_struct_id'] );
						}
					}
					//Nettoyage des ID HAL non numériques
					if ( ! empty( $HAL_ids ) ) {
						foreach ( $HAL_ids as $key => $value ) {
							if ( ! is_numeric( $value ) ) {
								unset( $HAL_ids[ $key ] );
							}
						}
					}
				}
			}
			if ( ! ( '' === $args['limit'] ) ) {
				$POST_query['NbAffiche'] = intval( $args['limit'] );
			}
			//Les paramètres idHal et struct sont incompatibles.
			if ( ! empty( $idHal ) ) {
				$POST_query['idHal'] = Lib::secured_implode( ';', $idHal );
			} elseif ( ! empty( $HAL_ids ) ) {
				$POST_query['struct'] = Lib::secured_implode( ';', $HAL_ids );
			} else {
				return array( '', '' ); /*Vide*/
			}
			if ( '__empty__' !== $args['hal_typepub'] ) {
				$POST_query['typdoc'] =  "('" . implode("','", $this->_prepare_multiple_values_variable_into_array( $args['hal_typepub'] ) ). "')";
			}
		}
		//---------------------------------------------------
		// 7 - contruire l’URL
		//---------------------------------------------------
		$url = '';
		if ( ! ( '' === $request_mode ) ) {
			$url = $api_base_url . http_build_query ( $POST_query );
		}
		return array( $request_mode, $url );
	}


	///////////////////////////////////////////////////////////////////////////////////////////
	/**
	 * URL request generator for shortcode [publications_list] specific to Descartes repo
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
	 * @return string[] array( $request_mode, $url )
	 * 	- string $request_mode : mode of request ('native_html_display' or 'generate_html_from_bibtext')
	 *  - string $url : Request URI generated
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
	) : array {
		$request_mode = 'native_html_display';
		$POST_query = array();
		$accessData = new Access_Data();
		$options4 = get_option( 'MonLabo_settings_group4' );
		$api_base_url = $options4['MonLabo_DescartesPubmed_api_url'] . '?';
		//Paramètres communs
		$POST_query['lang'] = ( 'Anglais' === $languageHAL ? 'en' : 'fr' );
		$POST_query['type'] = '*';
		$POST_query['orga_types'] = 'par_titre';
		$POST_query['format'] = $options4['MonLabo_DescartesPubmed_format'];
		if ( strlen( $args['years'] ) > 0 ) {
			$POST_query['annee'] = $args['years'];
		}
		if ( ! ( '' === $args['limit'] ) ) {
			$POST_query['limit'] = intval( $args['limit'] );
		}
		if ( ! ( '' === $args['offset'] ) ) {
			$POST_query['offset'] = intval( $args['offset'] );
		}

		//On ecrase les autres paramètres avec les paramètres experts
		$expert_mode = false;
		if ( '__empty__' !== $args['descartes_alias'] ) {
			$POST_query['alias'] = $args['descartes_alias'];
			$expert_mode = true;
		}
		if ( '__empty__' !== $args['descartes_auteurid'] ) {
			$POST_query['auteurid'] = $args['descartes_auteurid'];
			$expert_mode = true;
		}
		if ( '__empty__' !== $args['descartes_equipe'] ) {
			if ( '*' === $args['descartes_equipe'] ) {  //Get all the descartes_publi_team_id list of teams configured in MonLabo
				$all_teams_id = Lib::secured_array_keys( $accessData->get_teams_info() );
				$descartes_team_ids   = $this->_MonLaboTeamsIds_into_publicationServerTeamsIds( $all_teams_id, 'DescartesPubli' );
				if ( ! empty( $descartes_team_ids ) ) { $args['descartes_equipe'] = Lib::secured_implode( '|', $descartes_team_ids ); }
			}
			$POST_query['equipe'] = $args['descartes_equipe'];
			$expert_mode = true;
		}
		if ( '__empty__' !== $args['descartes_unite'] ) {
			$POST_query['unite'] = $args['descartes_unite'];
			$expert_mode = true;
		}
		if ( '__empty__' !== $args['descartes_typepub'] ) { //Ce paramètre expert est compatible avec les autres
			$POST_query['type'] = $args['descartes_typepub'];
		}
		if ( '__empty__' !== $args['descartes_nohighlight'] ) { //Ce paramètre expert est compatible avec les autres
			$POST_query['nohighlight'] = $args['descartes_nohighlight'];
		}
		if ( '__empty__' !== $args['descartes_orga_types'] ) { //Ce paramètre expert est compatible avec les autres
			$POST_query['orga_types'] = $args['descartes_orga_types'];
		}
		if ( '__empty__' !== $args['descartes_format'] ) { //Ce paramètre expert est compatible avec les autres
			$POST_query['format'] = $args['descartes_format'];
		}
		if ( '__empty__' !== $args['debug'] ) {
			$POST_query['debug'] = $args['debug'];
		}

		if ( false === $expert_mode ) {
			//En cas d’absence de paramètres experts
			if ( 'team_or_unit_page' === $page_mode ) {
				//Formatage de la requête
				$descartes_team_ids   = $this->_MonLaboTeamsIds_into_publicationServerTeamsIds( $teams_id, 'DescartesPubli' );
				$descartes_unit_ids   = $this->_MonLaboUnitsIds_into_publicationServerUnitsIds( $units_id, 'DescartesPubli' );
				if ( ! empty( $descartes_team_ids ) ) {
					$POST_query['equipe'] = Lib::secured_implode( '|', $descartes_team_ids );
				}
				if ( ! empty( $descartes_unit_ids ) ) {
					$POST_query['unite'] = Lib::secured_implode( '|', $descartes_unit_ids );
				}
				if ( empty( $descartes_team_ids ) and empty( $descartes_unit_ids ) ) {
					//Page d’équipe ou d’unite, mais n'indique pas d’ID Descartes
					return array( '', '' ); /*Vide*/
				}
				if ( 0 === strlen( $args['years'] ) ) {
					$POST_query['annee'] = '-5'; //Par défaut prendre les 5 dernières années
				}
			} elseif ( 'user_page' === $page_mode ) {
				$descartes_ids = $this->_MonLaboPersonsIds_into_publicationServerAuthorsIds( $persons_id, 'DescartesPubli' );
				if ( empty( $descartes_ids ) ) {
					return array( '', '' ); /*Vide*/
				}
				$POST_query['auteurid'] = Lib::secured_implode( '|', $descartes_ids );
			} elseif ( 'main_structure_page' === $page_mode ) {
				//On retourne les publications de toutes les personnes
				$persons = '';
				$all_persons_id = Lib::secured_array_keys( $accessData->get_persons_info() );
				if ( ! empty( $all_persons_id ) ) { $persons = Lib::secured_implode( '|', $all_persons_id ); }
				$persons_id	= $this->_prepare_multiple_values_variable_into_array( $persons );
				$descartes_ids = $this->_MonLaboPersonsIds_into_publicationServerAuthorsIds( $persons_id, 'DescartesPubli' );
				if ( empty( $descartes_ids ) ) {
					return array( '', '' ); /*Vide*/
				}
				$POST_query['auteurid'] = Lib::secured_implode( '|', $descartes_ids );
			} else {
				return array( '', '' ); /*Vide*/
			}
		}
		return array( $request_mode,  $api_base_url . http_build_query ( $POST_query ) );
	}


	/**
	 * Syntax analyser of the page returned by haltools
	 * @param string $hal_content page returned by haltools
	 * @return array<array<array<array<string,mixed>>>>  publications table [type][year][order][field]
	 *			   field : title, author, article
	 * @access private
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	private function _parse_haltools_response( string $hal_content = '' ): array {
		$publi =array();
	  /*
		$caractere_texte = array( 'à', 'À', 'á', 'Á', 'â', 'Â', 'ã', 'Ā', 'ä', 'Ä', 'å', 'Å', 'æ', 'Æ',
								 'è', 'È', 'é', 'É', 'ê', 'Ê', 'ë', 'Ë',
								 'ì', 'Ì', 'í', 'Í', 'î', 'Î', 'ï', 'Ï',
								 'ò', 'Ò', 'ó', 'Ó', 'ô', 'Ô', 'õ', 'Õ', 'ö', 'Ö', 'ø', 'Ø',
								 'ù', 'Ù', 'ú', 'Ú', 'û', 'Û', 'ü', 'Ü',
								 'ñ', 'ç', 'Ç', 'ý', 'Ý', 'ß',
								 '°',
								);
		*/
		$caractere_texte = array( '&agrave;', '&Agrave;', '&aacute;', '&Aacute;', '&acirc;', '&Acirc;', '&atilde;', '&Atilde;', '&auml;', '&Auml;', '&aring;', '&Aring;', '&aelig;', '&AElig;',
								 '&egrave;', '&Egrave;', '&eacute;', '&Eacute;', '&ecirc;', '&Ecirc;', '&etilde;', '&Etilde;', '&euml;', '&Euml;',
								 '&igrave;', '&Igrave;', '&iacute;', '&Iacute;', '&icirc;', '&Icirc;', '&itilde;', '&Itilde;', '&iuml;', '&Iuml;',
								 '&ograve;', '&Ograve;', '&oacute;', '&Oacute;', '&ocirc;', '&Ocirc;', '&otilde;', '&Otilde;', '&ouml;', '&Ouml;', '&oslash;', '&Oslash;', '&oelig;', '&OElig;',
								 '&ugrave;', '&Ugrave;', '&uacute;', '&Uacute;', '&ucirc;', '&Ucirc;', '&utilde;', '&Utilde;', '&uuml;', '&Uuml;',
								 '&ntilde;', '&ccedil;', '&Ccedil;', '&yacute;', '&Yacute;', '&szlig;', '&amp;',
								 '&deg;',
								);
		$caractere_latex = array( '{\`a}', '{\`A}', "{\'a}", "{\'A}", '{\^a}', '{\^A}', '{\~a}', '{\~A}', '{\"a}', '{\"A}', '{\aa}', '{\AA}', '{\ae}', '{\AE}',
								 '{\`e}', '{\`E}', "{\'e}", "{\'E}", '{\^e}', '{\^E}', '{\~e}', '{\~E}', '{\"e}', '{\"E}',
								 '{\`i}', '{\`I}', "{\'i}", "{\'I}", '{\^i}', '{\^I}', '{\~i}', '{\~I}', '{\"i}', '{\"I}',
								 '{\`o}', '{\`O}', "{\'o}", "{\'O}", '{\^o}', '{\^O}', '{\~o}', '{\~O}', '{\"o}', '{\"O}', '{\o}', '{\O}', '{\oe}', '{\OE}',
								 '{\`u}', '{\`U}', "{\'u}", "{\'U}", '{\^u}', '{\^U}', '{\~u}', '{\~U}', '{\"u}', '{\"U}',
								 '{\~n}', '{\c c}', '{\c C}', "{\'y}", "{\'Y}", '{\ss}', '\&',
								 '{\textdegree}',
								);
		/*$webservice = new $webservice_name();
		$hal_content = $webservice->webpage_get_content( $url );*/

		$data_contenu = explode ( '@', $hal_content ); //Séparer chaque entrée bibliographique
		foreach ( $data_contenu as $id_publi => $datum_publi ) {
			$datum_publi = str_replace( $caractere_latex, $caractere_texte, $datum_publi );
			if ( 0 != $id_publi ) { //On ne prend que après le premier @
				// correction du probleme de MONTH dans hal
				//On recherche 'MONTH = truc, ' et on le remplace par 'MONTH = {truc}, '
				//Cette recherche se fait indépendament de la case ( /i ) et du nombre de carracères d’espacement ou tabulation autour du =
				$datum_publi = preg_replace( '/MONTH\s*=\s*(\S+)\s*,/i', 'MONTH = {${1}},', $datum_publi );
				//type = mot en début de chaine avant le première accolade. (/s est pour le traitement multiligne)
				$type = preg_replace( '/^([^{]+)\S*{.*/s','${1}',$datum_publi );
				//contenu = tout ce qui est dans les accolades suivantes (les espaces et sauts de lignes autour ne comptent pas)
				$contenu = preg_replace( '/^([^{]+)\S*{\S*(.*)\S*}\S*$/s','${2}', $datum_publi );
				//On récupère un gros tableau des 'field' = 'values', en étant tolérant sur les espaces surnuméraires.
				preg_match_all( '/(?P<field>\w+)[ \t]* = [ \t]*(?P<values>.*)\S*,\S*[\n\r\f]+/', $contenu, $matches );
				$fields = $matches['field'];
				$values = $matches['values'];
				$publi_tmp = array();
				foreach ( array_keys( $fields ) as $key ) {
					$publi_tmp [strtoupper( $fields[ $key ] ) ] = $values[ $key ];
				}
				//nettoyage de chaque champs
				foreach ( $publi_tmp as $key => $value ) {
					$value = $this->_suppress_bibtex_value_limiter( $value );
					$publi_tmp[ $key ] = str_replace( array( '{','}' ), '', $value );
				}
				$annee = ( ! empty( $publi_tmp['YEAR'] ) ) ? $publi_tmp['YEAR'] :  'unknown year';
				//unknown year : En cas de fichier bib invalide
				if ( ! empty( $publi_tmp['TYPE'] ) ) {
					if ( 0 === strcmp( substr( $publi_tmp['TYPE'], 0, 12 ), 'Habilitation' ) ) {
						$publi['hdr'][ $annee ][] = $publi_tmp;
					} elseif ( 0 === strcmp( $publi_tmp['TYPE'], 'Theses' ) ) {
						$publi['phdthesis'][ $annee ][] = $publi_tmp;
					} else {
						$publi[ $type ][ $annee ][] = $publi_tmp;
					}
				} else {
					$publi[ $type ][ $annee ][] = $publi_tmp;
				}
			}
		}
		return $publi;
	}

	/**
	 * publications formating
	 * @param string $format format for publications ('ieee' or 'apa')
	 * @param array<array<array<array<string,mixed>>>> $data_publi publications table [type][year][order][field]
	 * @param string $currentlang language for display
	 * @return string html to display
	 * @access private
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	private function _format_publi(
			string $format /*= 'ieee'*/,
			array $data_publi,
			string $currentlang
		): string {
		//Voir https://doc.archives-ouvertes.fr/bib2hal/champs-bibtex-obligatoiresoptionnels-par-type/
		//Il faudra rajouter:
		//@conference
		//@presconf
		//@poster
		//@inbook
		//@proceedings
		//@manual
		//@note

		$type_name = array(
			'article'	   => array( 'en' => 'Journal articles'						, 'fr' => 'Article dans une revue' ),
			'inproceedings' => array( 'en' => 'Conference papers'					   , 'fr' => 'Communication dans un congrès' ),
			'incollection'  => array( 'en' => 'Book sections'						   , 'fr' => 'Chapitre d\'ouvrage' ),
			'book'		  => array( 'en' => 'Books'								   , 'fr' => 'Ouvrage (y compris édition critique et traduction)' ),
			'hdr'		   => array( 'en' => 'Habilitation à diriger des recherches'   , 'fr' => 'HDR' ),
			'mastersthesis' => array( 'en' => 'Master thesis'						   , 'fr' => 'Mémoire d\'étudiant' ),
			'techreport'	=> array( 'en' => 'Reports'								 , 'fr' => 'Rapport' ),
			'misc'		  => array( 'en' => 'Other publications'					  , 'fr' => 'Autre publication' ),
			//Sont aussi classés dans la categorie bibtex @misc les catégories HAL suivantes :
			//			  - Posters
			//			  - Autre rapport, séminaire, workshop
			//			  - Document associé à des manifestations scientifiques
			//			  - Carte
			'phdthesis'	 => array( 'en' => 'Theses'								  , 'fr' => 'Thèse' ),
			'unpublished'   => array( 'en' => 'Preprints, Working Papers, ...'		  , 'fr' => 'Pré-publication, Document de travail' ),
			//Sont aussi classés dans la categorie bibtex @unpublished les catégories HAL suivantes :
			//			  - Cours
			'patent'		=> array( 'en' => 'Patents'								 , 'fr' => 'Brevet' ), //TODO rajouter le formatage
			'proceedings'   => array( 'en' => 'Directions of work or proceedings'	   , 'fr' => 'Direction d’ouvrage, Proceedings, Dossier' ), //TODO rajouter le formatage
		);
		$formats_ieee_strings = array(
			'article' =>
				', "#{TITLE},"| <i>#{JOURNAL}</i>|, vol. #{VOLUME}|, no. #{NUMBER}|, pp. #{PAGES}|, |#{MONTH}. |#{YEAR}',
			'inproceedings' => // Communication dans un congrès
				', "#{TITLE},"| in <i>#{BOOKTITLE}</i>|, #{ADDRESS}|, |#{MONTH}. |#{YEAR}|, pp. #{PAGES}',
			'incollection' =>  // Chapitre d’ouvrage
				', "#{TITLE},"| in <i>#{BOOKTITLE}</i>| (#{SERIES})|, #{PUBLISHER}|, |#{MONTH}. |#{YEAR}|, pp. #{PAGES}',
			'book' => // Ouvrage (y compris édition critique et traduction )
				', <i>#{TITLE}</i>| (#{SERIES})|, #{PUBLISHER}|, |#{MONTH}. |#{YEAR}|, pp. #{PAGES}',
			'hdr' => // HDR
				', "#{TITLE},"| H.D.R. dissertation|, #{SCHOOL}|, |#{MONTH}. |#{YEAR}|, pp. #{PAGES}',
			'mastersthesis' => // Mémoire d’étudiant
				', "#{TITLE},"| student dissertation|, #{SCHOOL}|, |#{MONTH}. |#{YEAR}|, pp. #{PAGES}',
			'techreport' => // Rapport
				', "#{TITLE},"| unpublished|, |#{MONTH}. |#{YEAR}|, pp. #{PAGES}',
			'misc' => //Autre publication
				', "#{TITLE},"| unpublished|, <i>#{HOWPUBLISHED}</i>|, |#{MONTH}. |#{YEAR}|, pp. #{PAGES}',
			'phdthesis' => // Thèse
				', "#{TITLE},"| M.S. thesis|, #{SCHOOL}|, |#{MONTH}. |#{YEAR}|, no. #{NUMBER}|, pp. #{PAGES}',
			'unpublished' => // Pré-publication, Document de travail
				', "#{TITLE},"| unpublished|, |#{MONTH}. |#{YEAR}|, pp. #{PAGES}',
			'patent' => // brevet //http://libraryguides.vu.edu.au/ieeereferencing/standardsandpatents //Author(s) Initial(s). Surname(s), “Title of patent,” Published Patent\'s country of origin and number xxxx, Abbrev. Month. Day, Year of Publication.​
				', "#{TITLE},"| #{ADDRESS}| Patent #{NUMBER}|, |#{MONTH}. |#{YEAR}',
			'proceedings' => // Direction d’ouvrage, Proceedings, Dossier //http://libraryguides.vu.edu.au/ieeereferencing/confernenceproceedings //Author( s) Initial( s). Surname( s), “Title of paper,” in <i>Abbrev. Name of Conf.</i>, [location of conference is optional], [vol., no. if available], Year, pp. xxx–xxx.
				', "#{TITLE},"| in <i>#{BOOKTITLE}</i>|, #{ADDRESS}|, |#{MONTH}. |#{YEAR}|, vol. #{VOLUME}|, no. #{NUMBER}|, pp. #{PAGES}',
		);
		$formats_apa_strings = array(
			'article' =>
				' | #{TITLE}.| <i>#{JOURNAL}</i>|, #{VOLUME}| (#{NUMBER})|, #{PAGES}',
			'inproceedings' => // Communication dans un congrès
				' | <i>#{TITLE}</i>.| Paper presented at the #{BOOKTITLE}|, #{ADDRESS}|, #{VOLUME}| (#{NUMBER})|, #{PAGES}',
			'incollection' => // Chapitre d’ouvrage
				' In #{EDITOR},| <i>#{BOOKTITLE}</i>|, <i>#{BOOKTITLE}</i>|, #{SERIES}|, #{VOLUME}| (#{NUMBER})|, #{PAGES}',
			'book' => // Ouvrage (y compris édition critique et traduction)
				' <i>#{TITLE}</i>| #{PUBLISHER}|, <i>#{BOOKTITLE}</i>|, #{SERIES}|, #{VOLUME}| (#{NUMBER})|, #{PAGES}',
			'hdr' => // HDR
				' <i>#{TITLE}</i>|, HDR not published |, HDR not published #{SCHOOL}',
			'mastersthesis' => // Mémoire d’étudiant
				' <i>#{TITLE}</i>|, HDR not published |, HDR not published #{SCHOOL}|, #{PAGES}',
			'techreport' => // Rapport
				' <i>#{TITLE}</i>| (#{NUMBER})|, #{INSTITUTION}|, #{PAGES}',
			'misc' => // Autre publication
				' <i>#{TITLE}</i>|, Paper presented at the #{HOWPUBLISHED}|, #{PAGES}',
			'phdthesis' => // Thèse
				' <i>#{TITLE}</i>| (#{NUMBER})|, Thesis not published |, Thesis not published #{SCHOOL}',
			'unpublished' => // Pré-publication, Document de travail
				' <i>#{TITLE}</i>|, #{PAGES}',
			'patent' => // brevet //http://libraryguides.vu.edu.au/apa-referencing/patents-and-standards
				' <i>#{TITLE}</i>| <i>No. #{NUMBER}</i>|#{ADDRESS}|: #{PUBLISHER}',
			'proceedings' => // Direction d’ouvrage, Proceedings, Dossier //http://libraryguides.vu.edu.au/apa-referencing/conference-proceedings
				', "#{TITLE},"|, #{EDITOR}|, <i>#{BOOKTITLE}</i>| (pp. #{PAGES})|. |#{ADDRESS}|: #{PUBLISHER}',
		);
		$translate = new Translate( $currentlang );
		$lang = $translate->get_lang_short();

		$a_afficher = '';
		if ( ! empty( $data_publi ) ) {
			foreach ( $data_publi as $type => $datum_type ) {
				if ( in_array( $type, Lib::secured_array_keys( $type_name ) ) ) { //Vérifier qu’on est capable de traiter le $type dans le switch
					$a_afficher .= "<p class='Rubrique'>" . $type_name[ $type ][ $lang ] . '</p>';
					foreach ( $datum_type as $date => $datum_date ) {
						$a_afficher .= "<p class='SousRubrique'>" . $date . '</p>';
						foreach ( $datum_date as $datum_order ) {
							if ( ! empty( $datum_order['AUTHOR'] ) ) { //Ne rien afficher si pas d’auteur
								$a_afficher .= "<dl class='NoticeRes'><dt class='ChampRes'>ref_biblio</dt><dd class='ValeurRes ref_biblio'>";
								if ( 'ieee' === $format ) {
									$a_afficher .= $this->_bibtex_to_ieee_authors( $datum_order['AUTHOR'] );
									if ( array_key_exists( $type,  $formats_ieee_strings ) ) {
										$string_format = $formats_ieee_strings[ $type ];
										if ( ! empty( $datum_order['ADDRESS'] ) ) {
											if ( 'incollection' === $type ) {
												$string_format =
													', "#{TITLE},"| in <i>#{BOOKTITLE}</i>| (#{SERIES})|, ' . $datum_order['ADDRESS'] . '|: #{PUBLISHER}|, |#{MONTH}. |#{YEAR}|, pp. #{PAGES}';
											} elseif ( 'book' === $type ) {
												$string_format =
													', <i>#{TITLE}</i>| (#{SERIES})|, ' . $datum_order['ADDRESS'] . '|: #{PUBLISHER}|, |#{MONTH}. |#{YEAR}|, pp. #{PAGES}';
											}
										}
										$a_afficher .= $this->_format_bib_fields( $datum_order, $string_format
											. '|.| <a target="_blank" rel="noopener noreferrer" href="http://dx.doi.org/#{DOI}">&lt;|#{DOI}&gt;</a>.| #{HAL_ID-URL}.| <a href="#{PDF}" target="_blank" rel="noopener noreferrer"><img style="width:11px; height:11px;" alt="PDF" src="'
											. Lib::get_file_url( "Frontend/images/Haltools_pdf.png" )
											. '"| title="#{PDF}" border="0"></a>' );
									}
								} elseif ( 'apa' === $format ) {
									$a_afficher .= $this->_bibtex_to_apa_authors( $datum_order['AUTHOR'] );
									if ( array_key_exists( $type,  $formats_apa_strings ) ) {
										$string_format = $formats_apa_strings[ $type ];
										if ( ! empty( $datum_order['PDF'] ) ) {
											if ( 'hdr' === $type ) {
												$string_format = ' <i>#{TITLE}</i>|, HDR (#{SCHOOL})';
											} elseif ( 'mastersthesis' === $type ) {
												$string_format = ' <i>#{TITLE}</i>|, HDR (#{SCHOOL})|, #{PAGES}';
											} elseif ( 'phdthesis' === $type ) {
												$string_format = ' <i>#{TITLE}</i>| (#{NUMBER})|, Thesis (#{SCHOOL})';
											}
										}
										if ( ! empty( $datum_order['EDITOR'] ) and ( 'book' === $type ) ) {
											$string_format =
											' <i>#{TITLE}</i>| In ' . $datum_order['EDITOR'] . '|, #{PUBLISHER}|, <i>#{BOOKTITLE}</i>|, #{SERIES}|, #{VOLUME}| (#{NUMBER})|, #{PAGES}';
										}
										$a_afficher .= $this->_format_bib_fields( $datum_order, ' (|#{YEAR}|, #{MONTH}|).|' . $string_format
											. '|.| <a target="_blank" rel="noopener noreferrer" href="http://dx.doi.org/#{DOI}">&lt;|#{DOI}&gt;</a>.| #{HAL_ID-URL}.| <a href="#{PDF}" target="_blank" rel="noopener noreferrer"><img style="width:11px; height:11px;" alt="PDF" src="'
											. Lib::get_file_url( "Frontend/images/Haltools_pdf.png" )
											. '"| title="#{PDF}" border="0"></a>' );
									}
								}
								$a_afficher .= '</dd></dl>';
							}
						}
					}
				}
			}
		}
		return empty( $a_afficher ) ? '' : '<div id="res_script">'. $a_afficher . '</div>';
	}

	/**
	 * formatting of authors in IEEE format from BibTex authors
	 * @param string $authors_bib_string authors in BibTex format
	 * @return string authors in IEEE format
	 * @access private
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	private function _bibtex_to_ieee_authors( string $authors_bib_string ): string {
		$authors = explode( ' and ', $authors_bib_string );
		$authors_names = array();
		foreach ( $authors as $author ) {
			$names = explode( ',', $author );
			$first_names = explode( ' ', trim( $names[1] ) );
			$abr_first_names = array();
			foreach ( $first_names as $first_name_datum ) {
				$composed_first_names = explode( '-', $first_name_datum );
				if ( count( $composed_first_names ) > 1 ) {
					$abr_composed_first_names = array();
					foreach ( $composed_first_names as $composed_first_name ) {
						$abr_composed_first_names[] = strtoupper( substr( trim( $composed_first_name ), 0, 1 ) ) . '.';
					}
					$abr_first_names[] = Lib::secured_implode( '-', $abr_composed_first_names );
				} else {
					$abr_first_names[] = strtoupper( substr( trim( $composed_first_names[0] ), 0, 1 ) ) . '.';
				}
			}
			$last_names = explode( ' ', trim( $names[0] ) );
			$cased_last_names = array();
			foreach ( $last_names as $last_name_datum ) {
				$composed_last_names = explode( '-', $last_name_datum );
				if ( count( $composed_last_names ) > 1 ) {
					$cased_composed_last_names = array();
					foreach ( $composed_last_names as $composed_last_name ) {
						$cased_composed_last_names[] = ucwords( strtolower( trim( $composed_last_name ) ) );
					}
					$cased_last_names[] = Lib::secured_implode( '-', $cased_composed_last_names );
				} else {
					$cased_last_names[] = ucwords( strtolower( trim( $composed_last_names[0] ) ) );
				}
			}
			$first_name = Lib::secured_implode( ' ', $abr_first_names );
			$last_name = Lib::secured_implode( ' ', $cased_last_names );
			$authors_names[] = $first_name . ' ' . $last_name;
		}
		if ( count( $authors ) > 1 ) {
			$authors_name = Lib::secured_implode( ', ', array_splice( $authors_names, 0, count( $authors_names )-1 ) );
			if ( count( $authors_names ) > 0 ) {
				$authors_name .= ', and ' . $authors_names[0];
			}
			return $authors_name;
		}
		return $authors_names[0];
	}

	/**
	 * formatting of authors in APA format from BibTex authors
	 * @param string $authors_bib_string authors in BibTex format
	 * @return string authors in APA format
	 * @access private
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	function _bibtex_to_apa_authors( string $authors_bib_string ): string {
		$authors = explode( ' and ', $authors_bib_string );
		$authors_names = array();
		foreach ( $authors as $author ) {
			$names = explode( ',', $author );
			$first_names = explode( ' ', trim( $names[1] ) );
			$abr_first_names = array();
			foreach ( $first_names as $first_name_datum ) {
				$composed_first_names = explode( '-', $first_name_datum );
				if ( count( $composed_first_names ) > 1 ) {
					$abr_composed_first_names = array();
					foreach ( $composed_first_names as $composed_first_name ) {
						$abr_composed_first_names[] = strtoupper( substr( trim( $composed_first_name ), 0, 1 ) ) . '.';
					}
					$abr_first_names[] = Lib::secured_implode( '-', $abr_composed_first_names );
				} else {
					$abr_first_names[] = strtoupper( substr( trim( $composed_first_names[0] ), 0, 1 ) ) . '.';
				}
			}
			$first_name = Lib::secured_implode( '', $abr_first_names );
			$last_name = ucwords( strtolower( trim( $names[0] ) ) );
			$authors_names[] = $last_name . ', ' . $first_name;
		}
		if ( count( $authors ) > 1 ) {
			$authors_name = Lib::secured_implode( ', ', array_splice( $authors_names, 0, count( $authors_names ) - 1 ) );
			if ( count( $authors_names ) > 0 ) {
				$authors_name .= ', & ' . $authors_names[0];
			}
			return $authors_name;
		}
		return $authors_names[0];
	}

	/**
	 * formatting of fields from a BibTex file
	 * @param string[] $array array where to get BibTex field
	 * @param string $field index of the field of the table to be retrieved
	 * @param string $format formatting to be produced from the field
	 * @return string generated HTML code
	 * @access private
	 */
	//==========================================================================
	private function _format_bib_field( array $array, string $field, string $format ): string {
		$a_afficher = '';
		$field = strtoupper( $field );
		//Pre-preprocess:
		//-----------
		if ( 'HAL_ID-URL' === $field ) {
			$field = 'INVALID'; //ne rien afficher par défaut
			if ( ! empty( $array['HAL_ID'] ) and ! empty( $array['URL'] ) ) {
				//lien de HAL_ID avec URL
				$field = 'HAL_ID';
				$format = str_replace( '#{field}', '<a target="_blank" rel="noopener noreferrer" href="' . $array['URL'] . '">&lt;#{field}&gt;</a>', $format );
			} elseif ( ! empty( $array['HAL_ID'] ) and  empty( $array['URL'] ) ) {
				//lien de HAL_ID avec HAL_ID
				$field = 'HAL_ID';
				$format = str_replace( '#{field}', '<a target="_blank" rel="noopener noreferrer" href="https://hal.science/' . $array['HAL_ID'] . '">&lt;#{field}&gt;</a>', $format );
			} elseif ( empty( $array['HAL_ID'] ) and ! empty( $array['URL'] ) ) {
				//lien URL simple
				$field = 'URL';
				$format = str_replace( '#{field}', '<a target="_blank" rel="noopener noreferrer" href="' . $array['URL'] . '">#{field}</a>', $format );
			}
		}

		if ( ! empty( $array[ $field ] ) ) {

			//Preprocess:
			//-----------
			switch ( $field ) {
				case 'PAGES':
				$reprocessed = trim( str_replace( 'p', '', str_replace( 'p.', '', str_replace( 'pp', '', str_replace( 'pp.', '', $array[ $field ] ) ) ) ) );
				break;

				case 'MONTH':
				$reprocessed = substr( $array[ $field ], 0, 3 );
				break;

				default:
				$reprocessed = $array[ $field ];
				break;
			}

			//Process:
			//-------
			$a_afficher .= str_replace( '#{field}', $reprocessed, $format );
		}
		return $a_afficher;
	}

	/**
	 * formatting of fields from a BibTex file
	 * @param string[] $array array where to get BibTex field
	 * @param string $format_string string
	 * @return string generated HTML code
	 * @access private
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	//==========================================================================
	private function _format_bib_fields( array $array, string $format_string ): string {
		$a_afficher = '';
		$fields_format = explode( '|', $format_string );
		foreach ( $fields_format as $one_field_format ) {
			$matches=array();
			preg_match( '/#{(.+)}/', $one_field_format, $matches);
			if ( isset( $matches[1] ) ){
				$field = $matches[1];
				$format = str_replace( '#{' . $field . '}', '#{field}', $one_field_format );
				$a_afficher .= $this->_format_bib_field( $array, $field, $format );
			} else {
				$a_afficher .= $one_field_format;
			}
		}
		return $a_afficher;
	}
}
?>
