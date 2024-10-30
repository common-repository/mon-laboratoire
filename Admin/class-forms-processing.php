<?php
namespace MonLabo\Admin;

use MonLabo\Lib\Person_Or_Structure\{Person, Team, Unit, Thematic, Main_Struct};
use MonLabo\Lib\{Polylang_Interface, Options};
use MonLabo\Lib\Access_Data\{Access_Data};

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

///////////////////////////////////////////////////////////////////////
// MANAGEMENT OF VARIABLES PASSED BETWEEN PAGES
///////////////////////////////////////////////////////////////////////
/*
class Forms_Processing {

	__construct( array $post_data = array() )
	_init_nonces( array $form_names ) 
	_check_nonce( string $form_id )
	_format_strings_array( array $input_array )
	getPOSTsimpleString( string $varName )
	_format_multiline_string( string $input_string )
	_format_string( $input_string )
	getPOSTnumber( string $varName )
	_sanitize_POST( array $gaps = array() )
	_extract_id_and_action_from_POST( )
	_update_post_thumbnail( $data, $image_id )
	_if_empty_get_posted_id( int $item_id = 0 )
	_create_pages( &$data, $type )
	_message_of_page_creation( $post_id )
	_nb_of_page_proprietaries( $page_id )
	_set_to_draft_all_isolated_pages()

	form_edit_person_processing()
	form_edit_team_processing()
	form_edit_thematic_processing()
	form_edit_unit_processing()
	form_edit_mainstruct_processing()
}
*/
/**
 * Class \MonLabo\Admin\Forms_Processing
 * @package
 */
class Forms_Processing {

	/**
	* _POST datas of the page
	* @var array<string,mixed>
 	* @access protected
	*/
	protected $post = array();

	/**
	 * Constructor
	* @param array<string,mixed> $post_data : argument for the constructor
	*/
	public function __construct( array $post_data = array() ) {
		if ( empty( $post_data ) ) {
			if ( !empty( $_POST ) ) {
				$this->post = $_POST;
			}
			return;
		}
		$this->post = $post_data;
		//in order to be able to verify nonce with check_admin_referer()
		$this->_init_nonces( array( 'edit_person', 'edit_team', 'edit_thematic', 'edit_unit', 'edit_mainstruct' 	));
	}

	/**
	 * Init nonce for each form
	 * @param string[] $form_names list of form name to init nounce
	 * @return void
	 * @access protected
	 */
	protected function _init_nonces( array $form_names ) {
		foreach ( $form_names 	as $name ) {
			$nonce = $name . '_form_wpnonce';
			if ( isset ( $this->post[ $nonce ] ) ) {
				$_REQUEST[ $nonce ] = $this->post[ $nonce ];
			}
		}
	}

	/**
	 * Check form nonce
	 * @param string $form_id ID of the form
	 * @return bool true if form has been submitted and nonce is OK
	 * @access protected
	 */
	protected function _check_nonce( string $form_id ) : bool {
		$check = ( isset( $this->post[ $form_id . '_form_wpnonce' ] )
			and check_admin_referer( $form_id . '_form', $form_id . '_form_wpnonce' ) );
		unset( $this->post[ $form_id.'_form_wpnonce' ] );
		unset( $this->post['_wp_http_referer'] );
		return $check;
	}

	/**
	 * Format and secure table of string
	 * @param string[] $input_array table to sanitize
	 * @return string[] sanitized variable
	 * @access private
	 */
	private function _format_strings_array( array $input_array ): array {
		//If table of string
		$retval = array();
		if ( ! empty( $input_array ) ) {
			foreach ( $input_array as $key => $value ) {
				$retval[ $key ] = $this->_format_string( $value );
			}
		}
		return $retval;
	}

	/**
	 * Format and secure string that can be given via POST method
	 * @param string $varName name of the variable in $this->post
	 *
	 * @throws \Error if $this->post[ $varName ] does not exists or is an array
	 *
	 * @return string|null sanitized variable
	 */
	function getPOSTsimpleString( string $varName ) {
		if ( isset( $this->post[ $varName ] ) ) {
			//If simple string
			return $this->_format_string( $this->post[ $varName ] );
		}
		trigger_error( "\nERROR: String $varName did not pass through a POST request.\n", E_USER_WARNING );
		return null;
	}

	/**
	 * Format and secure multiline string
	 * @param string $input_string input string
	 * @return string sanitized variable
	 * @access private
	 */
	private function _format_multiline_string( string $input_string ): string {
		return str_replace( "'", "’",
			str_replace( "\'", "’", htmlspecialchars( sanitize_textarea_field( $input_string ), ENT_COMPAT, "UTF-8"  ) )
		);
	}

	/**
	 * Format and secure a string
	 * @param string $input_string string to sanitize
	 * @return string sanitized variable
	 * @access private
	 */
	private function _format_string( string $input_string ): string {
		return str_replace( "'", "’",
			str_replace( "\'", "’", htmlspecialchars( sanitize_text_field( $input_string ), ENT_COMPAT, "UTF-8" ) )
		);
	}

	/**
	 * Format and secure number that can be given via POST method
	 * @param string $varName name of the variable in $this->post
	 *
	 * @throws \Error if $this->post[ $varName ] does not exists
	 *
	 * @return int|null sanitized variable, null if error
	 */
	function getPOSTnumber( string $varName ) {
	if ( isset( $this->post[ $varName ] ) and ctype_digit( (string) $this->post[ $varName ] ) ) {
			// OK, all digits
			return  intval( $this->post[ $varName ] );
		}
		trigger_error( "\nERROR: Number $varName did not pass through a POST request or is not in the right format.\n", E_USER_WARNING );
		return null;
	}

	/////////////////////////////////////////////////////////////////////////////////////
	// LIBRAIRY
	/////////////////////////////////////////////////////////////////////////////////////

	/**
	 * Clean $this->post[] and transfer into $data[]
	 * @param string[] $gaps list of each parameter to fill with array() if not exists
	 * @return array<string,string[]|string|int[]> $data cleaned $this->post values
	 * @access private
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	private function _sanitize_POST( array $gaps = array() ): array {
		$data = array();
		foreach ( array_keys( $this->post ) as $key ) {
			//Step 1 : suppress 'submit_' in key
			$data_key = str_replace( 'submit_', '', $key );
			//Step 2:
			if ( in_array( $key , array(
				'submit_teams', 'submit_mentors',
				'submit_leaders', 'submit_persons', 'submit_thematics',
				'submit_directors', 'submit_nom', 'submit_code',
				'submit_prefixe_tel', 'submit_hal_publi_struct_id',
			) ) ) {
				//If array, convert values to int
				if ( is_array( $this->post[ $key ] ) ) {
					foreach ( $this->post[ $key ] as $subkey => $subvalue ) {
						$data[ $data_key ][ $subkey ] = intval( $subvalue );
					}
				} else { //Else sanitize text
					$data[ $data_key ] = sanitize_text_field( $this->post[ $key ] );
				}

			} elseif ( in_array( $key , array(
				'submit_external_mentors', 'submit_external_students',
				'submit_address_alt', 'submit_contact_alt',
				'submit_contact', 'submit_adresse',
			) ) ) {
				//Multiline string
				$data[ $data_key ] = $this->_format_multiline_string( $this->post[ $key ] );
			} else {
				//String[]
				if ( is_array( $this->post[ $key ] ) ) {
					$data[ $data_key ] = $this->_format_strings_array( $this->post[ $key ] );
				//String
				} else {
					$data[ $data_key ] = $this->getPOSTsimpleString( $key );
				}
			}
		}
		//Fill gaps in data
		if ( ! empty( $gaps ) ) {
			foreach ( $gaps as $gap ) {
				if ( ! isset( $data[ $gap ] ) ) {
					$data[ $gap ] = array();
				}
			}
		}
		return $data;
	}

	/**
	 * extract sanitize and unset $this->post['submit_id'] and $this->post['action']
	 * @return array{int,string} [ int $membre_id, string $action ]
	 * @access private
	 */
	private function _extract_id_and_action_from_POST( ): array {
		$membre_id = intval( $this->post['submit_id'] );
		unset( $this->post['submit_id'] );
		$action = sanitize_key( $this->post['action'] );
		unset( $this->post['action'] );
		return array( $membre_id, $action );
	}

	/**
	 * Set post thumbnail identical to image
	 * @param array<string,mixed> $data all converted _POST
	 * @param int $image_id ID of the posted image
	 * @return Forms_Processing
	 * @access private
	 */
	private function _update_post_thumbnail( array $data, int $image_id ): self {
		//Modification de l’image en une de la page WordPress
		if ( isset ( $data['wp_post_ids'][0] )
			and isset ( $data['pageradio'][0] )
			and ( 'none' != $data['pageradio'][0] )
			and ( $data['wp_post_ids'][0] === (string) abs( intval( $data['wp_post_ids'][0] ) ) )
			and ( null !== get_post( intval( $data['wp_post_ids'][0] ) ) ) //Si la page existe
			and wp_attachment_is_image( $image_id ) )
		{
			set_post_thumbnail( intval( $data['wp_post_ids'][0] ), $image_id ); //Changer l’image à la une de cette page.
		}
		return $this;
	}

	/**
	 * If ID in parameter is zero, get the ID with posted value or in URL
	 * Usefull is page is reloaded.
	 * @param int $item_id ID to test
	 * @return int ID
	 * @access private
	 */
	private function _if_empty_get_posted_id( int $item_id = 0 ): int {
		//Retrieve eventuel posted item ID
		if ( isset( $this->post['submit_item'] ) ) {
			$_GET['submit_item'] = intval( $this->post['submit_item'] );
		}
		//If $item_id is empty, return ID in URL
		if ( ( empty( $item_id ) ) and ( isset( $_GET['submit_item'] ) ) ) {
			$item_id = intval( $_GET['submit_item'] );
		}
		return $item_id;
	}

	/**
	 * Create all pages or no pages from all pageradio status
	 * @param array<string,mixed> &$data all converted _POST
	 * @param string $type type of the structure ( 'person', 'team', 'thematic' or 'unit' )
	 * @return array<int,bool|string> array( bool $status false if error
	 * 									 bool $string message to display )
	 * @access private
	 */
	private function _create_pages( array &$data, string $type ): array {
		//Create new page or no page
		$main_message = '';
		$main_status = true;
		if ( ! isset( $data['pageradio'] ) and ! is_array( $data['pageradio'] ) ) {
			return array( false, 'Error.' );
		}
		foreach ( array_keys( $data['pageradio'] ) as $key ) {
			list( $status, $message ) = $this->_create_page( $data, $key, $type );
			$main_status *= $status;
			$main_message .= $message;
		}
		return array( $main_status, $main_message );
	}

	/**
	 * Generate page creation message
	 * @param int $post_id ID of the page created
	 * @return string HTML code of message
	 * @access private
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	private function _message_of_page_creation( int $post_id ): string {
		$Polylang_Interface = new Polylang_Interface();
		$messagesClass = new Messages();
		if ( $Polylang_Interface->is_polylang_to_use() ) {
			$tr_post_id = $Polylang_Interface->get_translated_post_if_exists( $post_id );
			$html_message =  $messagesClass->notice(
				'info',
				'',
				sprintf(
					__( 'Pages %1$s and %2$s created.', 'mon-laboratoire' ),
					"<a href='" . get_permalink( $post_id ) . "'>" . get_the_title( $post_id ) . '</a>',
					"<a href='" . get_permalink( $tr_post_id ) . "'>" . get_the_title( $tr_post_id ) . '</a>'
				)
			);
		} else {
			$html_message =  $messagesClass->notice(
				'info',
				'',
				sprintf(
					__( 'Page %s created.', 'mon-laboratoire' ),
					"<a href='" . get_permalink( $post_id ) . "'>" . get_the_title( $post_id ) . '</a>'
				)
			);
		}
		return $html_message;
	}

	/**
	 * Create pages or no pages from a pageradio status
	 * @param array<string,mixed> &$data all converted _POST
	 * @param int $page_number page number
	 * @param string $type type of the structure ( 'person', 'team', 'thematic' or 'unit' )
	 * @return array<int,bool|string> array( bool $status false if error
	 * 									 bool $string message to display )
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 * @access private
	 */
	private function _create_page( array &$data, int $page_number, string $type ): array {
		$message = '';
		$status = true;
		if ( ! isset( $data['pageradio'][ $page_number ] ) ) {
			return array( false, 'Error.' );
		}
		switch ( $data['pageradio'][ $page_number ] ) {
			case 'new':
				$messagesClass = new Messages();
				//Create page with unique title
				if ( 'person' === $type ) {
					$page = new Page( 'person', array( 'first_name' => $data['first_name'], 'last_name' => $data['last_name'] ) );
					$image = $data['image'];
				} else {
					$page = new Page( $type, array( 'name_en' => $data['name_en'], 'name_fr' => $data['name_fr'] ) );
					$image = $data['logo'];
				}
				if ( 0 === $page->wp_post_id ) {
					return array( false, $messagesClass->notice( 'error', 'Echec:', __( 'Unable to create the page.', 'mon-laboratoire' ) ) );
				}
				$data['wp_post_ids'][ $page_number ] = (string) $page->wp_post_id;
				$message .= $this->_message_of_page_creation( $page->wp_post_id );
				$this->_update_post_thumbnail( $data, intval( $image ) );
				break;

			case 'none':
				$data['wp_post_ids'][ $page_number ] = '';
				break;

			default: //edit, choose
				if ( ( 'person' === $type ) and ( 0 === $page_number ) ) {
					$this->_update_post_thumbnail( $data, intval( $data['image'] ) );
				}
				break;
		}
		return array( $status, $message );
	}

	/**
	 * Get the number of same item that have chosen the page for their personnal page
	 * @param string $wp_post_id page to test
	 * @return int : number
	 * @access private
	 */
	private function _nb_of_page_proprietaries( string $wp_post_id ): int {
		$accessData = new Access_Data();
		if ( $wp_post_id === (string) abs( intval( $wp_post_id ) ) ) { //If it is a real page
			return count( $accessData->get_itemIds_from_wpPostId( 'person', $wp_post_id ) )
				+ count( $accessData->get_itemIds_from_wpPostId( 'team', $wp_post_id ) )
				+ count( $accessData->get_itemIds_from_wpPostId( 'thematic', $wp_post_id ) )
				+ count( $accessData->get_itemIds_from_wpPostId( 'unit', $wp_post_id ) );
		}
		return 0;
	}

	/**
	 * Set to draft all pages of deleted items
	 * @return Forms_Processing
	 * @access private
	 */
	private function _set_to_draft_all_isolated_pages(): self {
		// Person pages a configured to draft
		if ( isset( $this->post['submit_wp_post_ids'] ) and  is_array( $this->post['submit_wp_post_ids'] ) ) {
			foreach ( $this->post['submit_wp_post_ids'] as $page_id ) {
				if ( 1 === $this->_nb_of_page_proprietaries( $page_id ) ) {
					//Set in draft only if this page belongs to nobody after delete person
					$page = new Page( 'from_id', $page_id );
					$page->to_draft();
				}
			}
		}
		return $this;
	}

	/////////////////////////////////////////////////////////////////////////////////////
	// FORM PROCESSING
	/////////////////////////////////////////////////////////////////////////////////////
	/**
	 * Process the edit form of a laboratory member
	 * @return array{string,int,int|null} [ string $text_to_display, int $membre_id_to_redirect, int $person_id_if_created ]
	 *		  $text_to_display = Variable in which to store the text to be displayedr
	*		  $membre_id_to_redirect = Member ID to be displayed once the form is validated
	*		  int|null $person_id_if_created = ID of the new person in case of creation
	* @SuppressWarnings(PHPMD.ElseExpression)
	* @SuppressWarnings(PHPMD.StaticAccess)
	*	Former line is due to Main_Struct::getInstance();
	*/
	function form_edit_person_processing(): array {
		$membre_id = 0;
		$main_message = '';
		//Verify that form has been properly submitted
		if ( $this->_check_nonce( 'edit_person' ) ) {
			list( $membre_id, $action ) = $this->_extract_id_and_action_from_POST();
			if ( 'edit' === $action ) {
				//Step 1: Clean $this->post[] and transfer into $data[]
				//Step 2: fills gaps 'teams', 'mentors', 'students' in $data[]
				//------------------------------------------------
				$data = $this->_sanitize_POST( array( 'teams', 'mentors', 'students' ) );

				//Step 3: Manage particular cases
				//-------------------------------
				// Particular case 1 - manage persons categories and functions
				if ( empty( $data['fonction'] ) ) {
					$data['fonction'] = ' |  | ';
				}
				$fonc_fields = explode( ' | ', $data['fonction'] );
				if ( empty( $data['category'] ) and ( '' != $fonc_fields[0] ) ) {
					$data['category'] = $fonc_fields[0];
				}
				if ( ! isset( $fonc_fields[1] ) ) { $fonc_fields[1] = ''; }
				if ( ! isset( $fonc_fields[2] ) ) { $fonc_fields[2] = ''; }
				if ( ( '' === $fonc_fields[1] ) && ( '' === $fonc_fields[2] ) ) { //Si rien n'est envoyé en argument, ne pas mettre à jour.
					unset( $data['function_en'] );
					unset( $data['function_fr'] );
				} elseif (
					( '' === $data['function_en'] )
					&& ( '' === $data['function_fr'] )
				) {
					$data['function_en'] = $fonc_fields[1];
					$data['function_fr'] = $fonc_fields[2];
				}
				if ( isset( $data['title_edit'] ) ) {
					$data['title'] = $data['title_edit'];
					unset( $data['title_edit'] );
				}
				if ( isset( $data['title'] ) and ( 'none' === $data['title'] ) ) {
					$data['title'] = '';
				}

				// Particular case 3 -Director of single unit
				// When there is only a main structure and no units then
				// configure if the person is or not the director
				$options = Options::getInstance();
				if ( !$options->uses['units'] ) {
					$main_struct =  Main_Struct::getInstance();
					if ( ! empty( $data['is_director'] ) ) {
						//On ajoute la personne comme directeur de la structure principale
						$main_struct->add_director( (int) $membre_id );
					} else {
						$main_struct->remove_director( (int) $membre_id );
					}
				}

				//Step 4a: Manage adding a person
				//-------------------------------
				if ( 0 === $membre_id ) {
					list( $status, $message ) = $this->_create_page( $data, 0, 'person' );
					if ( false === $status ) {
						return array( $message, 0, 0 );
					}
					// Creation of line in table MonLabo_members
					$person_if_created = new Person( 'insert', $data );

					$messagesClass = new Messages();
					return array(
								$messagesClass->notice(
									'info', '',
									sprintf( __( 'New person created (ID=%u).', 'mon-laboratoire' ),$person_if_created->info->id )
									) . ' ' . $message,
								0, //Forward to a new item
								$person_if_created->info->id
								);

				}
				//Step 4b: Manage editing a person
				//-------------------------------
				$Person = new Person( 'from_id', $membre_id );
				//Create new page or no page
				list( , $main_message ) = $this->_create_pages( $data, 'person' );
				//Modification de l’image en une de la page WordPress principale
				$this->_update_post_thumbnail( $data, intval( $data['image'] ) );

				$Person->update( $data );


			//Alternate step: Manage deleting a person
			//-----------------------------------------
			} elseif ( 'remove' === $action ) {
				$this->_set_to_draft_all_isolated_pages();
				$Person = new Person( 'from_id', $membre_id );
				$Person->delete( );
			}
		}
		//If the page is reloaded, retrieve the parameter in the URL
		return array( $main_message, $this->_if_empty_get_posted_id( $membre_id ), null );

	}

	/**
	 * Process the team's edit form
	 * @return array{string,int,int|null} 	 array( $text_to_display, $team_id_to_redirect, $team_id_if_created )
	 *		  string $text_to_display = Variable in which to store the text to be displayed
	*		  int $team_id_to_redirect = Team ID to be displayed once the form is validated
	*		  int|null $team_id_if_created = ID of the new team in case of creation
	* @SuppressWarnings(PHPMD.ElseExpression)
	* @SuppressWarnings(PHPMD.UnusedLocalVariable)
	*/
	function form_edit_team_processing(): array {
		$team_id = 0;
		$main_message = '';
		//Verify that form has been properly submitted
		if ( $this->_check_nonce( 'edit_team' ) ) {
			list( $team_id, $action ) = $this->_extract_id_and_action_from_POST();
			if ( 'edit' === $action ) {
				$data = $this->_sanitize_POST( array( 'leaders', 'thematics', 'persons' ) );
				//Manage adding a team
				//---------------------
				if ( 0 === $team_id ) {
					list( $status, $message ) = $this->_create_page( $data, 0, 'team' );
					if ( false === $status ) {
						return array( $message, 0, 0 );
					}
					// Creation of line in table MonLabo_teams
					$team_if_created = new Team( 'insert', $data );

					$messagesClass = new Messages();
					return array(
								$messagesClass->notice( 'info', '',
										sprintf( __( 'New team created (ID=%u).', 'mon-laboratoire' ),$team_if_created->info->id )
									) . $message ,
								0 /* Forward to a new item */,
								$team_if_created->info->id
								);
				}

				// Manage edit a team
				//-----------------------------------------------------
				$team = new Team( 'from_id', $team_id );
				//Create new page or no page
				list(, $main_message ) = $this->_create_pages( $data, 'team' );
				$team->update( $data );

			//Manage deleting a structure
			//----------------------------
			} elseif ( 'remove' === $action ) {
				$this->_set_to_draft_all_isolated_pages();
				$team = new Team( 'from_id', $team_id );
				$team->delete();
			}
		}
		//If the page is reloaded, retrieve the parameter in the URL
		return array( $main_message, $this->_if_empty_get_posted_id( $team_id ), null );
	}

	/**
	 * Process the form for editing a thematic
	 * @return array{string,int,int|null}	  array( $text_to_display, $thematic_id_to_redirect, $thematic_id_if_created )
	 *		  $text_to_display = Variable in which to store the text to be displayed
	*		  $thematic_id_to_redirect = ID of the thematic to be displayed once the form is validated
	*		  $thematic_id_if_created =  ID of the new thematic in case of creation
	* @SuppressWarnings(PHPMD.ElseExpression)
	* @SuppressWarnings(PHPMD.UnusedLocalVariable)
	*/
	function form_edit_thematic_processing(): array {
		$thematic_id = 0;
		$main_message = '';
		//Verify that form has been properly submitted
		if ( $this->_check_nonce( 'edit_thematic' ) ) {
			list( $thematic_id, $action ) = $this->_extract_id_and_action_from_POST();
			if ( 'edit' === $action ) {
				$data = $this->_sanitize_POST();
				//Manage adding a thematic
				//------------------------
				if ( 0 === $thematic_id ) {
					list( $status, $message ) = $this->_create_page( $data, 0, 'thematic' );
					if ( false === $status ) {
						return array( $message, 0, 0 );
					}
					// Creation of line in table MonLabo_teams
					$thematic_if_created = new Thematic( 'insert', $data );

					$messagesClass = new Messages();
					return array(
						$messagesClass->notice(
							'info', '',
							sprintf( __( 'New thematic created (ID=%u).', 'mon-laboratoire' ),$thematic_if_created->info->id )
							) . $message ,
						0 /* Forward to a new item */,
						$thematic_if_created->info->id
						);
				}
				// Manage edit a thematic
				//-----------------------------------------------------
				$thematic = new Thematic( 'from_id', $thematic_id );
				//Create new page or no page
				list( , $main_message ) = $this->_create_pages( $data, 'thematic' );
				$thematic->update( $data );

				//Manage deleting a structure
			//----------------------------
			} elseif ( 'remove' === $action ) {  // suppression d'un membre
				$this->_set_to_draft_all_isolated_pages();
				$thematic = new Thematic( 'from_id', $thematic_id );
				$thematic->delete();
			}
		}
		//If the page is reloaded, retrieve the parameter in the URL
		return array( $main_message, $this->_if_empty_get_posted_id( $thematic_id ), null );
	}

	/**
	 * Process the form for editing a unit
	 * @return array{string,int,int|null} array( $text_to_display, $unit_id_to_redirect, $unit_id_if_created )
	 *		  $text_to_display = Variable in which to store the text to be displayed
	*		  $unit_id_to_redirect = ID of the unit to be displayed once the form is validated
	*		  $unit_id_if_created =  ID of the new unit in case of creation
	* @SuppressWarnings(PHPMD.ElseExpression)
	* @SuppressWarnings(PHPMD.UnusedLocalVariable)
	*/
	function form_edit_unit_processing(): array {
		$unit_id = 0;
		$main_message = '';
		//Verify that form has been properly submitted
		if ( $this->_check_nonce( 'edit_unit' ) ) {
			list( $unit_id, $action ) = $this->_extract_id_and_action_from_POST();
			if ( 'edit' === $action ) {
				$data = $this->_sanitize_POST( array( 'directors' ) );
				//Manage adding an unit
				//---------------------
				if ( 0 === $unit_id ) {
					list( $status, $message ) = $this->_create_page( $data, 0, 'unit' );
					if ( false === $status ) {
						return array( $message, 0, 0 );
					}
					// Creation of line in table MonLabo_units
					$unit_if_created = new Unit( 'insert', $data );

					$messagesClass = new Messages();
					return array(
						$messagesClass->notice( 'info', '',
								sprintf( __( 'New unit created (ID=%u).', 'mon-laboratoire' ),$unit_if_created->info->id )
							) . $message ,
						0 /* Forward to a new item */,
						$unit_if_created->info->id
						);
				}
				// Modification de la ligne dans la table MonLabo_units
				//-----------------------------------------------------------
				$unit = new Unit( 'from_id', $unit_id );
				//Create new page or no page
				list( , $main_message ) = $this->_create_pages( $data, 'unit' );
				$unit->update( $data );

			} elseif ( 'remove' === $action ) {  // suppression d'un membre
				$this->_set_to_draft_all_isolated_pages();
				$unit = new Unit( 'from_id', $unit_id );
				$unit->delete();
			}
		}
		//If the page is reloaded, retrieve the parameter in the URL
		return array( $main_message, $this->_if_empty_get_posted_id( $unit_id ), null );
	}

	/**
	 * Process the form that edit main structure
	 * @return string HTML code
	 * @SuppressWarnings(PHPMD.StaticAccess)
	 */
	function form_edit_mainstruct_processing(): string {
		$retval = '';
		//Verify that form has been properly submitted
		if ( $this->_check_nonce( 'edit_mainstruct' ) ) {
				$data = $this->_sanitize_POST( array( 'directors' ) );
				$Main_Struct = Main_Struct::getInstance();
				$Main_Struct->update( $data );
		}
		return $retval;
	}
}
?>
