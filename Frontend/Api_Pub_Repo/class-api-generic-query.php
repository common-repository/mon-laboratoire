<?php
namespace MonLabo\Frontend\Api_Pub_Repo;

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );
/*
class Api_Generic_Query {
	get_base_url( )
	_empty_all()
}
*/

/**
 * Class \MonLabo\Api_Pub_Repo\Api_Generic_Query
 * Common fonctions for query to a publication repository
 * @package
 */
abstract class Api_Generic_Query {
	
	/**
	* Base URL of a query
	* @var string
	*/
	public $base_url = '';

	/**
	* POST_query
	* @var string[]
	*/
	public $POST_query = [];


	/**
	 * Return base URL for a query
	 * @return string
	 */
	public function get_base_url( ) : string {
		return $this->base_url;
	}

	/**
	* Clean query and URL
	 * @return void
	 * @access protected
	 */	
	protected function _empty_all() {
		$this->POST_query = [];
		$this->base_url = '';
	}


}
