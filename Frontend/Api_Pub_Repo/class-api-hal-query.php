<?php
namespace MonLabo\Frontend\Api_Pub_Repo;

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );
/*
class Api_Hal_Query {
	__construct()
	_add_filter_POST_query( string $filter )
	years_POST_query( string $years_interval )
	idHal_POST_query( array $idsHal )
	idHalStruct_POST_query( array $idsHalStruct )
	doctype_POST_query( array $docType )
}
*/

/**
 * Class \MonLabo\Frontend\Api_Hal_Query
 * Usefull to interact with https://api.archives-ouvertes.fr/ with API HAL V3.0
 * @package
 */
class Api_Hal_Query extends Api_Generic_Query {

    //Nombre de publications:
    //https://api.archives-ouvertes.fr/search/hal/?q=*:*&fq=authIdHal_s:(herve-suaudeau)&rows=0&wt=json
    //puis return $json->response->numFound


    //https://api.archives-ouvertes.fr/search/hal/?q=*:*&fq=authIdHal_s:(herve-suaudeau)&fl=docid,citationFull_s&&sort=producedDate_tdate+desc&wt=json&json.nl=arrarr
    //https://api.archives-ouvertes.fr/search/hal/?q=*:*&fq=authIdHal_s:(herve-suaudeau)&fl=docid,citationFull_s,docType_s,producedDate_s,authLastName_s,authFullName_s,inPress_bool,doiId_s,uri_s, *_title_s,journalPublisher_s,issue_s,volume_s,journalTitle_s&start=0&rows=10&sort=producedDate_tdate+desc&wt=json
	
    //Limiter la recherche à la collection SPPIN https://api.archives-ouvertes.fr/search/SPPIN/
    //Rechercher le mot "plugin" dans le titre https://api.archives-ouvertes.fr/search/SPPIN/?q=title_t:plugin&wt=json
    //Plus récent devant https://api.archives-ouvertes.fr/search/SPPIN/?q=*:*?&sort=producedDate_tdate desc
     //Période d'année https://api.archives-ouvertes.fr/search/SPPIN/?q=*:*&fq=submittedDateY_i:[2000 TO 2013]
     //                 Possible de faire [2000 TO *]
     //                 Les 2 dernières années [NOW-1YEARS/DAY TO NOW/HOUR]
     // Nombre de résultats ?q=*:*&rows=100
     //
    // authIdHal_s 	Auteur : IdHal (chaîne de caractères)  et authIdHal_i 	Auteur : personID (entier)
	//authIdHal_s:(herve-suaudeau OR martin-oheim)
	//authIdHal_i:(742784)
	//authIdHal_s:(herve-suaudeau) OR authIdHal_i:(742784)
	//Grouper par type de documents group=true&group.field=docType_s
	//deptStructId_i : structure / regroupement d'equipes. ==> SPPIN = 1004820 et 552971
	//labStructId_i rteamStructId_i structId_i

	/**
	* constructor
	*/
	public function __construct() {
		$this->base_url = 'https://api.archives-ouvertes.fr/search/hal/?';

		//Requete
		$this->POST_query['q'] = '*:*';

		//Calcul des autres paramètres de requête
		//---------------------------------------
		//More recent first
		$this->POST_query['sort'] = 'producedDate_tdate desc';
		//get the 500 first results
		$this->POST_query['start'] = '0';
		$this->POST_query['rows'] = '500';
		//Contenu à rapatrier
		$this->POST_query['fl'] = 'halId_s,citationFull_s,docType_s,producedDate*,authLastName_s,authFullName_s,inPress_bool,doiId_s,uri_s,*_title_s,journalPublisher_s,issue_s,volume_s,journalTitle_s,page_s,bookTitle_s,publisher_s,isbn_s,serie_s';
		//$this->POST_query['fl'] = 'docid,citationFull_s,docType_s,journalTitle_s';
		// collCategory_s docType_s reportType_s
		// citationFull_s citationRef_s
		// abstract_s
		// authFirstName_s authMiddleName_s authLastName_s authFullName_s
		// ===>authMiddleName_s n'est pas à la bonne taille. (on ne sait pas à qui va quel middle name)
		// ===> producedDate_s (2019-06-16,2021,...)
		// bookTitle_s
		// city_s country_s
		// conferenceOrganizer_s conferenceStartDateY_i conferenceTitle_s
		// defenseDateY_i
		// doiId_s
		// ePublicationDate_s journalDate_s publicationDateY_i releasedDate_s submittedDateY_i
		// ====> producedDate_s
		// fileMain_s files_s
		// inPress_bool
		// isbn_s serie_s issue_s volume_s page_s journalPublisher_s publisher_s thesisSchool_s
		// journalTitleAbbr_s journalTitle_s
		// label_bibtex
		// *_title_s (fr,en...)
		// ===> ["fr_title_s"]=> array(1) { [0]=>
		// ===> ["en_title_s"]=> array(1) { [0]=>
		// uri_s
		//json format
		$this->POST_query['wt'] = 'json';
	}

	/**
	 * Add a newpost query filter
	 * @param string $filter query filter
	 * @access private
	 * @return void
	 */
	private function _add_filter_POST_query( string $filter ) {
		if ( !empty( $filter ) ) {
			if ( isset( $this->POST_query['fq'] ) ) {
				$this->POST_query['fq'] .= " AND $filter";
				return;
			}
			$this->POST_query['fq'] = "$filter";
		}
	}

	/**
	 * Build HAL post query from years interval string
	 * @param string $years_interval Interval of years
	 * @return void
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	public function years_POST_query( string $years_interval ) {
		//Calcul des paramètres des années
		//--------------------------------
		if ( strlen( $years_interval ) > 0 ) {
			//Grab string into two values
			$anneeMinAndMax = explode( '-', $years_interval );
			$anneeMin = $anneeMinAndMax[0];
			if ( count( $anneeMinAndMax ) > 1 ) {
				$anneeMax = $anneeMinAndMax[1];
			} else {
				$anneeMax = $anneeMinAndMax[0];
			}
			//swap min and max if in bad order
			if ( $anneeMax < $anneeMin ) {
				$tempo = $anneeMax; $anneeMax = $anneeMin; $anneeMin = $tempo;
			}
			//Fill in post parameters
			if ( empty( $anneeMax ) ) {
				$anneeMax = '*';
			}
			if ( empty( $anneeMin ) ) {
				$anneeMin = '0';
			}
			$this->_add_filter_POST_query( "submittedDateY_i:[$anneeMin TO $anneeMax]" );
		}
	}

	/**
	 * Build HAL post query from person's idHal array
	 * @param string[] $idsHal Array of person HAL ID's
	 * @return void
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	public function idHal_POST_query( array $idsHal ) {
		if ( !empty( $idsHal ) ){
			$authIdHal_s = array();
			$authIdHal_i = array();
			foreach ($idsHal as $idHal) {
				if ( ( (string) abs( intval( $idHal ) ) ) ===  $idHal ) {
					$authIdHal_i[] = $idHal;  //idHal is an integer
				} else {
					$authIdHal_s[] = $idHal;  //idHal is a string
				}
			}
			$inquery = array();
			if ( !empty( $authIdHal_i ) ) {
				$inquery[] =  'authIdHal_i:(' . implode( ' OR ', $authIdHal_i) .')';
			}
			if ( !empty( $authIdHal_s ) ) {
				$inquery[] =  'authIdHal_s:(' . implode( ' OR ', $authIdHal_s) .')';
			}
			if ( !empty( $inquery ) ) {
				$this->_add_filter_POST_query( '(' . implode( ' OR ' , $inquery ) . ')' );
			}
		}
	}

	/**
	 * Build HAL post query from HAL structure identifier
	 * @param string[] $idsHalStruct Array of Struct HAL ID's
	 * @return void
	 */
	public function idHalStruct_POST_query( array $idsHalStruct ) {
		if ( !empty( $idsHalStruct ) ){
			$idsHalStruct_i = array();
			foreach ($idsHalStruct as $idHal) {
				$idsHalStruct_i[] = $idHal;
			}
			$this->_add_filter_POST_query( 'structId_i:(' . implode( ' OR ' , $idsHalStruct_i ) . ')' );
		}
	}

	/**
	 * Build HAL post query from publication types
	 * @param string[] $docType Array of publication type
	 * @return void
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	public function doctype_POST_query( array $docType ) {
		if ( !empty( $docType ) ){
			$this->_add_filter_POST_query( 'docType_s:(' . implode( ' OR ' , $docType ) . ')' );
		} else {
			$this->_add_filter_POST_query( 'docType_s:(ART OR COMM OR POSTER OR OUV OR COUV OR DOUV OR PATENT OR REPORT OR THESE OR HDR)' );
		}
	}

	/**
	 * Build HAL post query from limit
	 * @param string $limit max number of publication to display
	 * @return void
	 */
	public function limit_POST_query( string $limit ) {
		if ( !empty( $limit ) and is_numeric( $limit ) ){
			$this->POST_query['rows'] = $limit;
		}
	}

}
