<?php
namespace MonLabo\Lib;

use MonLabo\Frontend\Html;

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

/* Class \MonLabo\Lib\Polylang_Interface {

	__construct( )
	update_polylang_status();
	get_translated_post_if_exists( int $post_id )
	get_or_create_translated_post( int $post_id )
	post_id_in_current_page_language( int $post_id )
	get_edit_link_if_exists( $post_id )
	_update_polylang_plugin_status()
	get_polylang_plugin_status( string $return_type_flag )
	get_polylang_page_language()
	_link_translated_posts( int $wp_post1_id, int $wp_post2_id )
	create_translated_page_if_necessary( int $wp_post_id, string $translated_title = "" )
	update_page_parent_and_his_translation(  $wp_post_id, $parent )
*/

/**
 * Class \MonLabo\Lib\Polylang_Interface
 * Class to access the functions of PolyLang plugin.
 * @package
 */
class Polylang_Interface {

	/**
	* Can PolyLang be used with MonLabo?
	* @var string
	* @access private
	*/
	private $_polylang_to_use = 'disabled';

	/**
	*  Indicates if the Polylang plugin is installed and possibly activated.
	* @var string
	*/
	public $polylang_status = 'uninstalled';

	/**
	* Nesting level for parent page (usefull to prevent infinite loop)
	* @var int
	* @access private
	*/
	private $_nesting_level = 0;

	/**
	 * Constructor
	 */
	public function __construct( ) {
		$this->update_polylang_status();
	}

	/**
	 * Update polylang status
 	 * @return void
	 */
	public function update_polylang_status( ) {
		$this->_update_polylang_plugin_status();
		$options6 = get_option( 'MonLabo_settings_group6' );
		if ( isset( $options6['MonLabo_language_config'] ) ) {
			$langopt = $options6['MonLabo_language_config'];
			if ( ( 'Polylang' === $langopt ) or ( 'WordPress' === $langopt ) ) {
				if ( 'activated' === $this->polylang_status ) {
					$this->_polylang_to_use = 'enabled';
				}
			}
		}
	}

	/**
	* Return true if PolyLang plugin is activated and configured to be used in MonLabo
	* @return bool true or false
	*/
	public function is_polylang_to_use(): bool {
		return ( 'enabled' === $this->_polylang_to_use );
	}

	/**
	* Return the ID of the translated page if exists
	* @param int $post_id ID of the page to translate
	* @return int ID of the translated page
	*/
	public function get_translated_post_if_exists( int $post_id ): int {
		if ( ( false != $post_id ) && $this->is_polylang_to_use() ) {
			if (   function_exists( 'pll_get_post' )
				&& function_exists( 'pll_get_post_language' )
			){
				$translation_lang = ( ( 'fr' === pll_get_post_language( $post_id ) ) ? 'en' : 'fr' );
				$post_id_translated = pll_get_post( $post_id, $translation_lang );
				if ( $post_id_translated ) {
					if ( 'publish' === get_post_status( $post_id_translated ) ) {
						return intval( $post_id_translated );
					}
				}
			}
		}
		return $post_id;
	}

	/**
	* Return the ID of the translated page if exists
	*   if not exists, then creates the page
	* @param int $post_id ID of the page to translate
	* @return int ID of the translated page
	*/
	public function get_or_create_translated_post( int $post_id ): int {
		if ( ( false != $post_id ) && $this->is_polylang_to_use() ) {
			$post_id_translated = $this->get_translated_post_if_exists( $post_id );
			if ( $post_id_translated === $post_id ) {
				return $this->create_translated_page_if_necessary( $post_id );
			}
			return $post_id_translated;
		}
		return $post_id;
	}


	/**
	* Return the ID of the translated page if current page is also translated
	* @param int $post_id ID of the page to translate
	* @return int ID of the translated page
	* @SuppressWarnings(PHPMD.ElseExpression)
	*/
	public function post_id_in_current_page_language( int $post_id ): int {
		if ( ( false != $post_id ) && $this->is_polylang_to_use() ) {
			if (   function_exists( 'pll_get_post' )
				&& function_exists( 'pll_get_post_language' )
			){
				$current_page_id = get_the_ID();
				if ( false != $current_page_id ) {
					$lang = pll_get_post_language( $current_page_id );
					$post_id_translated = pll_get_post( $post_id, $lang );
					if ( $post_id_translated ) {
						if ( 'publish' === get_post_status( $post_id_translated ) ) {
							return intval( $post_id_translated );
						}
					}
				} else {
					return $this->get_translated_post_if_exists( $post_id );
				}
			}
		}
		return $post_id;
	}

	/**
	* Return custom PolyLang translation bar if exists
	* @param int $post_id ID of the page to translate
	* @param string $mode 'full' display or 'compact' mode
	* @return string Polylang HTML code of translation bar
	*/
	public function get_edit_link_if_exists( int $post_id, string $mode = 'full' ): string {
		$html = new Html();
		$retval = '';

		//First link
		$flag = '';
		if ( $this->is_polylang_to_use() && function_exists( 'pll_get_post_language' ) ) {
			$flag = ' ' . $html->get_translation_flag( pll_get_post_language( $post_id ) );
		}
		if ( 'compact' == $mode ) {
			$draft_message = ' <small>- <strong>'. __( 'draft', 'mon-laboratoire' ) . '</strong></small>';
		} else {
			$draft_message = ' <strong> - '. __( 'Draft page', 'mon-laboratoire' ). '</strong>';
		}
		$draft_warn = '';
		if ( 'draft' === get_post_status( $post_id ) ) {
			$draft_warn = $draft_message;
		}
		if ( 'compact' == $mode ) {
			$retval .= "<a href='" . get_edit_post_link( $post_id ) . "'>"
				. __( 'page', 'mon-laboratoire' ) . ' ' . strval( $post_id )
				. $flag . '</a>&nbsp: <small><em>' . get_the_title( $post_id ) . '</em></small>' . $draft_warn;
		} else {
			$retval .= "<a href='" . get_edit_post_link( $post_id ) . "'>"
			. sprintf( __( 'edit page #%s', 'mon-laboratoire' ), $post_id )
			. $flag . '</a>' . ' (<em>' . get_the_title( $post_id ) . '</em>)' . $draft_warn;
		}
		//Translated post if exist
		$transl_post_id = $this->get_translated_post_if_exists( $post_id );
		if ( $transl_post_id
			&& ( $transl_post_id != $post_id )
			&& function_exists( 'pll_get_post_language' )
			&& function_exists( 'pll_get_post' )
		) {
			$lang = pll_get_post_language( $post_id );
			$translation_lang = ( ( 'fr' === $lang ) ? 'en' : 'fr' );
			$post_id_translated = pll_get_post( $post_id, $translation_lang );
			if ( $post_id_translated ) {
				$draft_warn = '';
				if ( 'draft' === get_post_status( $post_id_translated ) ) {
					$draft_warn = $draft_message;
				}
				if ( 'compact' == $mode ) {
					$retval .= " / <a href='" . get_edit_post_link( $post_id_translated ) . "'>"
						. __( 'page', 'mon-laboratoire' ) . ' ' . strval( $post_id_translated )
						. $html->get_translation_flag( $translation_lang )
						. '</a>&nbsp: <small><em>' . get_the_title( $post_id_translated ) . '</em></small>' . $draft_warn;
				} else {
					$retval .= "<br /><a href='" . get_edit_post_link( $post_id_translated ) . "'>"
					. sprintf( __( 'edit page #%s', 'mon-laboratoire' ), $post_id_translated )
					. $html->get_translation_flag( $translation_lang )
					. '</a>' . ' (<em>' . get_the_title( $post_id_translated ) . '</em>)' . $draft_warn;
				}
			}
		}
		return $retval;
	}


	/**
	*  Indicates if the Polylang plugin is installed and possibly activated. Set the answer into $this->polylang_status
	* @access private
	* @return void
	*/
	private function _update_polylang_plugin_status( ) {
		if ( ! defined( 'ABSPATH' ) ) {
			define( 'ABSPATH', __DIR__ . '/');
		}
		if ( file_exists( ABSPATH . '/wp-content/plugins/polylang/polylang.php' )
			or file_exists( ABSPATH . '/wp-content/plugins/polylang-pro/polylang.php' )
		) {
			if ( class_exists( 'Polylang' ) ) {
				$this->polylang_status = 'activated';
				return;
			}
			$this->polylang_status = 'deactivated';
			return;
		}
		$this->polylang_status = 'uninstalled';
	}

	/**
	*  Indicates if the Polylang plugin is installed and possibly activated.
	* @param string $return_type_flag If this flag = 'translated', return translated status.
	* @return string Status : 'activated', 'installed' or 'uninstalled'
	*/
	public function get_polylang_plugin_status( string $return_type_flag = 'not translated' ): string {
		if ( 'not translated' === $return_type_flag  ) {
			return $this->polylang_status;
		}
		switch ( $this->polylang_status ) {
			case 'activated':
				//echo "--activated--" . get_locale() . '--' .  translate( 'activated', 'mon-laboratoire' ) . '--';
				return _x( 'activated', 'plugin', 'mon-laboratoire' );
			case 'deactivated':
				return _x( 'deactivated', 'plugin', 'mon-laboratoire' );
			default:
				return _x( 'uninstalled', 'plugin', 'mon-laboratoire' );
		}
	}

	/**
	*  Get the language of page configured by Polylang plugin
	* @return string language code (en or fr)
	*/
	public function get_polylang_page_language(): string {
		if ( $this->is_polylang_to_use() ) {
			$wp_post_id = get_the_ID();
			if ( false != $wp_post_id ) {
				if ( function_exists( 'pll_get_post_language' ) ) {
					$post_lang = pll_get_post_language( $wp_post_id );
					if ( $post_lang ) {
						return $post_lang;
					}
				}
			}
			return  get_locale();
		}
		return 'en';
	}

	/**
	* link two post as translated
	* @param int $wp_post1_id id of the first post.
	* @param int $wp_post2_id id of the second post.
	* @return void
	* @access private
	*/
	private function _link_translated_posts( int $wp_post1_id, int $wp_post2_id ) {
		if ( $wp_post1_id ) {
			if ( $this->is_polylang_to_use() ) {
				if ( function_exists( 'pll_save_post_translations' )
					&& function_exists( 'pll_default_language' )
					&& function_exists( 'pll_set_post_language' )
				) {
					$lang1 = pll_default_language();
					$lang2 = 'fr' === $lang1 ? 'en' : 'fr';
					pll_set_post_language( $wp_post1_id, $lang1 );
					pll_set_post_language( $wp_post2_id, $lang2 );
					pll_save_post_translations( array( $lang1 => $wp_post1_id, $lang2 => $wp_post2_id ) );
				}
			}
		}
	}

	/**
	 * Create a translated page if Polylang is activated
	 * @param int $wp_post_id ID of the page to translate
	 * @param string $translated_title If given, uses translated title
	 * @return int ID of the new page. null if error or not necessary
	 */
	function create_translated_page_if_necessary( int $wp_post_id, string $translated_title = '' ): int {
		//Create a translated post if Polylang is activated
		if ( $this->is_polylang_to_use() ) {
			if ( empty( $translated_title ) ) {
				$translated_title = get_the_title( $wp_post_id ) . ' (translated)';
			}

			$wp_post = array(
				'post_content'	=> get_the_content( null, false, $wp_post_id ), /* @phan-suppress-current-line PhanTypeMismatchArgumentProbablyReal */
				'post_title'	=> $translated_title,
				'post_status'	=> 'publish', // Default 'draft'.
				'post_type'		=> 'page' // Default 'post'.
			);

			if ( $this->_nesting_level <= 2 ) {
				$this->_nesting_level++; //counter to prevent infinite loop
				$wp_post['post_parent']	= $this->get_or_create_translated_post( wp_get_post_parent_id( $wp_post_id ) );
			} else {
				$this->_nesting_level = 0;
			}

			$transl_post_id = wp_insert_post( $wp_post );

			if ( $transl_post_id ) {
				$transl_post_id = intval( $transl_post_id );
				$this->_link_translated_posts( $wp_post_id, $transl_post_id );
				//Copy show title status
				update_post_meta( $transl_post_id, '_theme_show_page_title', get_post_meta( $wp_post_id, '_theme_show_page_title' ) );
				return $transl_post_id;
			}
		}
		return 0;
	}

	/**
	 * Change post parent, and set also post parent of translated page
	 * @param int $wp_post_id ID of the page
	 * @param int $parent_id ID of the parent
	 * @return void
	 */
	function  update_page_parent_and_his_translation( int $wp_post_id, int $parent_id ) {
		wp_update_post(
			array(
				'ID' => $wp_post_id, 
				'post_parent' => $parent_id
			)
		);
		$wp_post_id_translated = $this->get_translated_post_if_exists( $wp_post_id );
		if ( $wp_post_id != $wp_post_id_translated ) {
			wp_update_post(
				array(
					'ID' => $wp_post_id_translated, 
					'post_parent' => $this->get_translated_post_if_exists( $parent_id )
				)
			);
		}
	}

}
