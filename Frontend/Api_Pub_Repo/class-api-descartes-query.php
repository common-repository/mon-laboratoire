<?php
namespace MonLabo\Frontend\Api_Pub_Repo;

use MonLabo\Lib\{Lib};

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );
/*
class Api_Descartes_Query {
	__construct()
	years_POST_query( string $years_interval )
	limit_POST_query( string $limit )
	offset_POST_query( string $offset )
	language_POST_query( string $lang )
	expert_param_POST_query(
		string $descartes_alias,
		string $descartes_auteurid,
		string $descartes_equipe,
		string $descartes_unite,
		string $descartes_typepub,
		string $descartes_nohighlight,
		string $descartes_orga_types,
		string $descartes_format
	 )
	equipes_and_unite_POST_query( array $descartes_team_ids, array $descartes_unit_ids )
	persons_POST_query( array $descartes_ids )
	debug_POST_query( string $debug )
}
*/

/**
 * Class \MonLabo\Frontend\Api_Descartes_Query
 * Usefull to interact with Descartes API
 * @package
 */
class Api_Descartes_Query extends Api_Generic_Query {

	/**
	* expert mode
	* @var bool
	* @access private
	*/
	private $_expert_mode = false;

	/**
	* $page_mode 'team_or_unit_page' or 'user_page' or 'main_structure_page'
	* @var string
	* @access private
	*/
	private $_page_mode = '';


	/**
	* constructor
	* @return void
	*/
	public function __construct( string $page_mode ) {
		$this->_page_mode = $page_mode;
		$options4 = get_option( 'MonLabo_settings_group4' );
		$this->base_url = $options4['MonLabo_DescartesPubmed_api_url'];
		$this->POST_query['type'] = '*';
		$this->POST_query['orga_types'] = 'par_titre';
		$this->POST_query['format'] = $options4['MonLabo_DescartesPubmed_format'];
	}


	/**
	 * Build Descartes post query from years
	 * @param string $years_interval Interval of years
	 * @return void
	 */
	public function years_POST_query( string $years_interval ) {
		if ( strlen( $years_interval ) > 0 ){
			$this->POST_query['annee'] = $years_interval;
		} else {
			if ( false === $this->_expert_mode &&
			     'team_or_unit_page' === $this->_page_mode ) {
				$this->POST_query['annee'] = '-5';
			}
		}
	}

	/**
	 * Build Descartes post query from limit
	 * @param string $limit max number of publication to display
	 * @return void
	 */
	public function limit_POST_query( string $limit ) {
		if ( !empty( $limit ) and is_numeric( $limit ) ){
			$this->POST_query['limit'] = $limit;
		}
	}

	/**
	 * Build Descartes post query from offset
	 * @param string $offset
	 * @return void
	 */
	public function offset_POST_query( string $offset ) {
		if ( !empty( $offset ) and is_numeric( $offset ) ){
			$this->POST_query['offset'] = $offset;
		}
	}

	/**
	 * Build Descartes post query from language
	 * @param string $lang language in HAL format
	 * @return void
	 */
	public function language_POST_query( string $lang ) {
		$this->POST_query['lang']  = ( 'Anglais' === $lang ? 'en' : 'fr' );
	}

	/**
	 * Build Descartes post query from expert parameters
	 * @param string $descartes_alias expert parameter
	 * @param string $descartes_auteurid expert parameter
	 * @param string $descartes_equipe expert parameter
	 * @param string $descartes_unite expert parameter
	 * @param string $descartes_typepub expert parameter
	 * @param string $descartes_nohighlight expert parameter
	 * @param string $descartes_orga_types expert parameter
	 * @param string $descartes_format expert parameter
	 * @return void
	 */
	public function expert_param_POST_query(
		string $descartes_alias,
		string $descartes_auteurid,
		string $descartes_equipe,
		string $descartes_unite,
		string $descartes_typepub,
		string $descartes_nohighlight,
		string $descartes_orga_types,
		string $descartes_format
	 ) {
		//On ecrase les autres paramètres avec les paramètres experts
		if ( '__empty__' !== $descartes_alias ) {
			$this->POST_query['alias'] = $descartes_alias;
			$this->_expert_mode = true;
		}
		if ( '__empty__' !== $descartes_auteurid ) {
			$this->POST_query['auteurid'] = $descartes_auteurid;
			$this->_expert_mode = true;
		}
		if ( '__empty__' !== $descartes_equipe ) {
			$this->POST_query['equipe'] = $descartes_equipe;
			$this->_expert_mode = true;
		}
		if ( '__empty__' !== $descartes_unite ) {
			$this->POST_query['unite'] = $descartes_unite;
			$this->_expert_mode = true;
		}
		if ( '__empty__' !== $descartes_typepub ) { //Ce paramètre expert est compatible avec les autres
			$this->POST_query['type'] = $descartes_typepub;
		}
		if ( '__empty__' !== $descartes_nohighlight ) { //Ce paramètre expert est compatible avec les autres
			$this->POST_query['nohighlight'] = $descartes_nohighlight;
		}
		if ( '__empty__' !== $descartes_orga_types ) { //Ce paramètre expert est compatible avec les autres
			$this->POST_query['orga_types'] = $descartes_orga_types;
		}
		if ( '__empty__' !== $descartes_format ) { //Ce paramètre expert est compatible avec les autres
			$this->POST_query['format'] = $descartes_format;
		}
	}

	/**
	 * Build Descartes post query from units and teams list
	 * @param int[] $descartes_team_ids
	 * @param int[] $descartes_unit_ids
	 * @return void
	 */
	public function equipes_and_unite_POST_query( array $descartes_team_ids, array $descartes_unit_ids ) {
		if ( false === $this->_expert_mode ) {
			//En cas d’absence de paramètres experts
			//Formatage de la requête
			if ( ! empty( $descartes_team_ids ) ) {
				$this->POST_query['equipe'] = Lib::secured_implode( '|', $descartes_team_ids );
			}
			if ( ! empty( $descartes_unit_ids ) ) {
				$this->POST_query['unite'] = Lib::secured_implode( '|', $descartes_unit_ids );
			}
			if ( empty( $descartes_team_ids ) && empty( $descartes_unit_ids ) ) {
				//Page d’équipe ou d’unite, mais n'indique pas d’ID Descartes
				$this->_empty_all();
			}
		}
	}
	

	/**
	 * Build Descartes post query from persons_id
	 * @param int[] $descartes_ids
	 * @return void
	 */
	public function persons_POST_query( array $descartes_ids ) {
		if ( false === $this->_expert_mode ) {
			if ( empty( $descartes_ids ) ) {
				$this->_empty_all();
				return;
			}
			$this->POST_query['auteurid'] = Lib::secured_implode( '|', $descartes_ids );
		}
	}
	

	/**
	 * Build Descartes post query from debug option
	 * @param string $debug
	 * @return void
	 */
	public function debug_POST_query( string $debug ) {
		if ( '__empty__' != $debug ) {
			$this->POST_query['debug']  = $debug;
		}
	}

}
