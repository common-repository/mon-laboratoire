<?php
namespace MonLabo\Admin;
use MonLabo\Lib\{Polylang_Interface, Translate};

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

/////////////////////////////////////////////////////////////////////////////////////
// MANAGE PAGES OF PERSONS OR STRUCTURE
/////////////////////////////////////////////////////////////////////////////////////
/*
class Page {

	__construct( string $type = '', $args = null )
	_get_page_by_title( string $wp_title )
	_new_unique_page_title( string $basename )
	_create_parent_page( string $type )
	_create_person_page( string $first_name, string $last_name )
	_create_structure_page( string $type, string $name_en, string $name_fr )
	to_draft()
  	is_a_person_page()

*/

/**
 * Class \MonLabo\Admin\Page
 * @package
 */
class Page {

	/**
	* WP_post_id of the page
	* @var int
	*/
	public $wp_post_id = 0;

	/**
	 * Retrieve from database person or structure
	* @param string $type : type of construction
	* @param mixed $args : argument for the constructor
		*/
	public function __construct( string $type = '', $args = null ) {
		switch ( $type ) {
			case 'from_id':
				if ( is_numeric( $args ) ) {
					$this->wp_post_id = intval( $args );
				}
				break;
			case 'person':
				if ( is_array( $args ) ) {
					$args = wp_parse_args( $args, array( 'first_name' => '', 'last_name' => '' ) );
					$this->wp_post_id = $this->_create_person_page(
						(string) $args['first_name'],
						(string) $args['last_name']
					);
				}
				break;
			case 'thematic':
			case 'team':
			case 'unit':
					if ( is_array( $args ) ) {
					$args = wp_parse_args( $args, array( 'name_en' => '', 'name_fr' => '' ) );
					$this->wp_post_id = $this->_create_structure_page(
						$type,
						(string) $args['name_en'],
						(string) $args['name_fr']
					);
				}
				break;
			case 'person_parent':
			case 'team_parent':
			case 'thematic_parent':
			case 'unit_parent':
				$this->wp_post_id = $this->_create_parent_page( $type );
				break;														
			default :
				break;
		}
	}

	/**
	 * Reproduce wordpress function get_page_by_title() obsolete in WP version 6.2
	 * @param string $title name to try
	 * @return \WP_Post|null Wordpress Page (type WP_Post)
	 * @access private
	 */
	private function _get_page_by_title( string $title ) {
		$posts = get_posts(
			array(
				'post_type'              => 'page',
				'title'                  => $title,
				'post_status'            => 'all',
				'numberposts'            => 1,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
				'orderby'                => 'post_date ID',
				'order'                  => 'ASC',
			)
		);
		if ( ! empty( $posts ) ) {
			return $posts[0];
		}
		return null;
	}

	/**
	 * Create a page name with title that do not exists (add instance number at the end if not)
	 * @param string $basename name to try
	 * @return string unique name (eventually add a number of instance at the end)
	 * @access private
	 */
	private function _new_unique_page_title( string $basename ): string {
		$wp_title = $basename;
		$page_rank = 2;
		while ( $this->_get_page_by_title( $wp_title ) ) {
			$wp_title = $basename . ' ' . $page_rank;
			++$page_rank;
		}
		return $wp_title;
	}

	/**
	 * Create a parent page
	 * @param string $type type of parent page ('person_parent', 'team_parent', 'thematic_parent' or 'unit_parent' )
	 * @return int int $wp_post_id, 0 if error
	 * @access private
	*/
	private function _create_parent_page( string $type ): int {
		switch ( $type ) {
			case 'person_parent':
				$name_en = "Lab members";
				$post_content =	"<!-- wp:shortcode -->\n[members_list]\n<!-- /wp:shortcode -->";
				break;
			case 'team_parent':
				$name_en = "Teams";
				$post_content =	"<!-- wp:shortcode -->\n[teams_list]\n<!-- /wp:shortcode -->";
				break;
			case 'thematic_parent':
				$name_en = "Thematics";
				$post_content = '';
				break;
			case 'unit_parent':
				$name_en = "Units";
				$post_content = '';
				break;
			default:
				return 0; //Error
		}

		//Get the name in main language, and the translated name
		$translate = new Translate();
		$name_fr = Translate::TRANSLATION_TABLE[ $name_en ]['fr-FR'];
		$name_in_main_lang = $translate->switch_lang( $name_en, $name_fr );
		$name_translated = $name_fr;
		if ( $name_in_main_lang !== $name_en ) {
			$name_translated = $name_en;
		}
		return $this->_create_generic_page( $post_content, $name_in_main_lang, $name_translated );
	}

	/**
	 * Create a person page
	 * @param string $first_name first name of the person
	 * @param string $last_name last name of the person
	 * @return int int $wp_post_id, 0 if error
	 * @access private
	*/
	private function _create_person_page( string $first_name, string $last_name ): int {
		//Insert new post
		$wp_title = $first_name . ' ' . mb_strtoupper( $last_name, 'UTF-8' );
		$options = get_option( 'MonLabo_settings_group10' );
		$post_content =	"<!-- wp:shortcode -->\n[perso_panel]<!-- /wp:shortcode -->\n\n"
								. "<!-- wp:paragraph -->\n<p></p>\n<!-- /wp:paragraph -->\n\n"
								. "<!-- wp:shortcode -->\n[publications_list]\n<!-- /wp:shortcode -->";

		return $this->_create_generic_page( 
			$post_content, 
			$wp_title, 
			$wp_title . ' (' . __( 'translated', 'mon-laboratoire' ) . ')', 
			intval( $options['MonLabo_perso_page_parent'] ), 
			true 
		);
	}

	/**
	 * Create a team, thematic or unit page
	 * @param string $type type of the structure ('team', 'thematic' or 'unit')
	 * @param string $name_en name of the structure in english
	 * @param string $name_fr name of the structure in french
	 * @return int int $wp_post_id, 0 if error
	 * @access private
	*/
	private function _create_structure_page( string $type, string $name_en, string $name_fr ): int {
		$post_content = "<!-- wp:shortcode -->\n[publications_list]<!-- /wp:shortcode -->";
		if ( 'team' === $type ) {
			$post_content = "<!-- wp:shortcode -->\n[team_panel]<!-- /wp:shortcode -->\n\n"
				. "<!-- wp:paragraph -->\n<p></p>\n<!-- /wp:paragraph -->\n\n"
				. "<!-- wp:shortcode -->\n[publications_list]\n<!-- /wp:shortcode -->";
		}
		//Get the name in main language, and the translated name
		$translate = new Translate();
		$name_in_main_lang = $translate->switch_lang( $name_en, $name_fr );
		$name_translated = $name_fr;
		if ( $name_in_main_lang !== $name_en ) {
			$name_translated = $name_en;
		}
		$options = get_option( 'MonLabo_settings_group10' );
		$wp_post_parent = 0;
		if( isset(  $options['MonLabo_' . $type . '_page_parent'] ) ) {
			$wp_post_parent = intval( $options['MonLabo_' . $type . '_page_parent'] );
		}
		return $this->_create_generic_page( $post_content, $name_in_main_lang, $name_translated, $wp_post_parent );
	}

	/**
	 * Create a team, thematic or unit page
	 * @param string $post_content The full text of the post
	 * @param string $title title of the page in main language
	 * @param string $title_translated (optional) title of the page translated
	 * @param int $wp_post_parent (optional) Parent to attach page
	 * @param bool $hide_title (optional) Hide title of page created
	 * @return int int $wp_post_id, 0 if error
	 * @access private
	*/
	private function _create_generic_page( string $post_content, string $title, string $title_translated = '',  int $wp_post_parent = 0, bool $hide_title = false ): int {
		//Insert new post
		$wp_post = array(
			'post_content'	=> $post_content , 
			'post_title'	=> $this->_new_unique_page_title( $title ), // The title of your post.
			'post_status'	=> 'publish', // Default 'draft'.
			'post_type'		=> 'page', // Default 'post'.
			'post_parent'	=> $wp_post_parent // Sets the parent of the new post.
		);
		$wp_post_id = wp_insert_post( $wp_post );

		//get the ID
		/** @phpstan-ignore-next-line */ /* is_wp_error may be always false but I am not sure */
		if ( ( 0 === $wp_post_id ) or ( is_wp_error( $wp_post_id ) ) )  {
			return 0;
		}
		$wp_post_id = intval( $wp_post_id );

		if ( $hide_title ) {
			update_post_meta( $wp_post_id, '_theme_show_page_title', '0' ); //Do not show title
		}

		//Create translated post
		$Polylang_Interface = new Polylang_Interface();
		$Polylang_Interface->create_translated_page_if_necessary( $wp_post_id, $this->_new_unique_page_title( $title_translated ) );
		return $wp_post_id;
	}


	/**
	 * Set in draft a page (and its translation if existing)
	 * @return Page
	 */
	public function to_draft(): self {
		//Set in draft translated page
		$Polylang_Interface = new Polylang_Interface();
		if ( $Polylang_Interface->is_polylang_to_use() ) {
			$transl_id = $Polylang_Interface->get_translated_post_if_exists( $this->wp_post_id );
			if ( $transl_id && ( $transl_id != $this->wp_post_id ) ) {
				wp_update_post( array( 'ID' => $transl_id, 'post_status'  => 'draft' ) );
			}
		}
		wp_update_post( array( 'ID' => $this->wp_post_id, 'post_status'  => 'draft' ) );
		return $this;
	}

	/**
	 * Tell if the page is for a person
	 * @return bool true if parent is the configured person parent
	 * @access public
	 */
	public function is_a_person_page() : bool {
		$options10 = get_option( 'MonLabo_settings_group10' );
		if ( isset( $options10['MonLabo_perso_page_parent'] )
			and ( $options10['MonLabo_perso_page_parent'] == wp_get_post_parent_id( $this->wp_post_id ) )	
		) {	
			return true;
		}
		return false;
	}

}

