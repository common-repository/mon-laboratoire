<?php
namespace MonLabo\Frontend\Api_Pub_Repo;

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );
/*
class Api_Hal_publications_list {
	__construct( string $data_publi )
	format_publi( string $format, string $currentlang )
}
*/

/**
 * Class \MonLabo\Frontend\Api_Hal_publications_list
 * Usefull to format publications downloaded from https://api.archives-ouvertes.fr/ with API HAL V3.0
 * @package
 */
class Api_Hal_publications_list {

	/**
	* Json object of publications
	* @var Api_Hal_publication[]
	*/
	public $publis = array();
	
	/**
	* constructor
	*/
	public function __construct( string $data_publi, string $format = 'hal' ) {
		$decoded = json_decode( $data_publi, true );
		if ( 	isset( $decoded['response'] )
			and isset( $decoded['response']['docs'] ) ) {
				//Make an array of Api_Hal_publication
				foreach ( (array) $decoded['response']['docs'] as $key => $value ) {
					$this->publis[ $key ] = new Api_Hal_publication( $value, $format );
				}
		}
	}

	/**
	 * publications formating
	 * @param string $format format for publications ('ieee' or 'apa')
	 * @param string $data_publi publications in json format
	 * @param string $currentlang language for display
	 * @return string html to display
	 * @access private
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	public function format_publi(
		string $format /*= 'ieee'*/,
		string $currentlang
	): string {
		return '';
	}

}
