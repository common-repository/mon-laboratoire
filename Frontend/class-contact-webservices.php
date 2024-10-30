<?php
namespace MonLabo\Frontend;

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );
/*
class Contact_Webservices {
	clear_transients()
	number_of_transient_entries()
	_grab_external_content( string $url )
	webpage_get_content( string $url )
*/

/**
 * Class \MonLabo\Frontend\Contact_Webservices
 * Usefull to be replaced by stub in unit test.
 * @package
 */
class Contact_Webservices {

	/**
	* Clean the cache
	* @return Contact_Webservices
	*/
	function clear_transients() : self {
		global $wpdb;
		// delete all "monlabo namespace" transients
		$sql = "
				DELETE
				FROM %i
				WHERE option_name like '\_transient\_monlabo\_%'
				OR option_name like '\_transient\_timeout\_monlabo\_%'
			";
		$wpdb->query( $wpdb->prepare( $sql, $wpdb->options ) );
		return $this;
	}

	/**
	* Get the number of pages saved in cache
	* @return int size of cache in number of page
	*/
	function number_of_transient_entries() : int {
		global $wpdb;
		$sql = "SELECT COUNT(*) FROM %i WHERE option_name like '\_transient\_monlabo\_%'";
		return intval( $wpdb->get_var( $wpdb->prepare( $sql, "$wpdb->options" ) ) );
	}

	/**
	* Get URL content
	* @param string $url URL
	* @return string body of HTML page
	* @access private
	*/
	private function _grab_external_content( string $url ): string {
		$args = ['timeout'=>20,
			'user-agent'=>"mon-laboratoire Extension Wordpress " . get_MonLabo_version(),
			'headers' => ['Content-type'=> 'application/json'] ];
		$content = wp_remote_get( $url , $args );
		return wp_remote_retrieve_body( $content );
	}

	/**
	 * Get page content of an URL (transient management is inspired by HAL CCSD plugin)
	 * @param string $url URL where to get content
	 * @return string content of the page at URL address
	 */
	function webpage_get_content( string $url ): string {
		$transient_id = 'monlabo_' . md5( $url );
		$options7 = get_option( 'MonLabo_settings_group7' );
		$cache_expiration = isset( $options7['MonLabo_cache_duration'] ) ? intval( $options7['MonLabo_cache_duration'] ) * HOUR_IN_SECONDS : 1 * DAY_IN_SECONDS ;
		$content = get_transient( $transient_id );
		if ( false === $content ) {
			$content = $this->_grab_external_content( $url );
			if ( !empty( $content ) ) {
				set_transient( $transient_id, $content, $cache_expiration );
			}
		}
		return $content;
	}
}
?>
