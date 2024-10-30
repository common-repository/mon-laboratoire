<?php
namespace MonLabo\Admin\Import;
use MonLabo\Lib\Access_Data\{Access_Data};
use MonLabo\Frontend\{Html};
use MonLabo\Admin\{Html_Forms,Messages};


defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

/////////////////////////////////////////////////////////////////////////////////////
// Import from CSV step 1
/////////////////////////////////////////////////////////////////////////////////////
/*
class Pre_Import {

	__construct( array $imported_persons, array $imported_teams_members, array $imported_teams )
	_fill_action_list( string $type_of_item )
	_fill_action_list_teams_members( )
	_clean_imported_array( string $type_of_item )
	_explain_action_point( string $type_of_item, array $action_list_id,  string $message )
	_explain_creation_action_point( string $type_of_item, array $items_to_create,  string $message )
	_explain_team_members_action_point( string $message )
	_get_item_name_after_import( string $type_of_item, int $id, array $former_items_id )
	_silent_transmit_action_list()
    processing( )
*/

/**
 * Class \MonLabo\Admin\Import\Pre_Import
 * @package
 */
class Pre_Import {
	/**
	 * Current instance of Html
	* @access private
	* @var Html
	 */
	private $_html = null;

	/**
	 * Current instance of Access_Data
	* @access private
	* @var Access_Data
	 */
	private $_accessData = null;

	/**
	* @var array<int,array<string,string>> $_imported_persons :  Imported person array
	* @access private
	*/
	private $_imported_persons = array();

	/**
	* @var array<int,array<string,string>> $_imported_teams_members :  Imported team members array
	* @access private
	*/
	private $_imported_teams_members = array();

	/**
	* @var array<int,array<string,string>> $_imported_teams :  Imported teams array
	* @access private
	*/
	private $_imported_teams = array();

	/**
	* @var array<string,array<string,int[]|array<string|int,string[]>>> $_action_list : actions to to for importation
	* @access private
	*/
	private $_action_list = array();	

	// 'wp_post_ids' is not in AUTHORIZED_INDEXES because we want to ignore this field
	const AUTHORIZED_INDEXES = array(
		'person' => array( 'id', 'title', 'first_name', 'last_name', 'category', 'function_fr', 'function_en', 'mail', 'phone', 'room', 
							'address_alt', 'external_url', 'descartes_publi_author_id', 'hal_publi_author_id', 'uid_ENT_parisdescartes', 'date_departure', 
							'status', 'visible', 'custom1', 'custom2', 'custom3', 'custom4', 'custom5', 'custom6', 'custom7', 'custom8', 'custom9', 'custom10',
							'image', 'external_mentors', 'external_students'),
		'teams_members' => array( 'id_person', 'id_team', 'directing' ),
		'team'=> array( 'id', 'name_fr', 'name_en', 'descartes_publi_team_id', 'hal_publi_team_id', 'id_unit', 'logo', 'color')
	);

	const MANDATORY_INDEXES = array(
		'person' => array( 'id', 'first_name', 'last_name', 'category', 'function_fr', 'function_en' ),
		'teams_members' => array( 'id_person', 'id_team', 'directing' ),
		'team'=> array( 'id', 'name_fr', 'name_en')
	);

    /**
	 * Constructor
	 * @param array<int,array<string,string>> $imported_persons : Imported person array
	 * @param array<int,array<string,string>> $imported_teams_members : Imported teams members table array
	 * @param array<int,array<string,string>> $imported_teams : Imported teams array
	 */
	public function __construct( array $imported_persons, array $imported_teams_members, array $imported_teams ) {
		$this->_accessData = new Access_Data();
		$this->_html = new Html();
		$this->_imported_persons 		= $imported_persons;
		$this->_imported_teams_members 	= $imported_teams_members;
		$this->_imported_teams 			= $imported_teams;
		$this->_action_list = array( 'person' 	=> array( 
															'id_to_delete' => array(),
															'item_to_modify' => array(),
															'item_to_create' => array()
													),
									 'team'	=> array( 
															'id_to_delete' => array(),
															'item_to_modify' => array(),
															'item_to_create' => array()
									 				),
									 'teams_members'	=> array(
															'item_to_create' => array()
									 				)
								);
	}

	/**
     * Fill the table action list
	 * @param string $type_of_item : type of import table. 'person', 'teams_members' or 'team'
     * @return string
	 * @access private
     */
	private function _fill_action_list( string $type_of_item ) : string {
		$retval = '';
		$imported_array_name = '_imported_' . $type_of_item . 's';
		if( 'teams_members' === $type_of_item ) {
			$imported_array_name = '_imported_' . $type_of_item;
		}
		if( !empty( $this->{$imported_array_name} ) ){
			if( 'teams_members' === $type_of_item ) {
				return $this->_fill_action_list_teams_members();
			}
			// Create the list of all imported ids
			$imported_ids = array();
			foreach ( $this->{$imported_array_name} as $item ) {
				$imported_ids[] = $item['id'];
			}
			if( $type_of_item === 'person' ) {
				$former_ids = array_keys( $this->_accessData->get_persons_info( 'all' ) );
			} else {
				$former_ids = array_keys( $this->_accessData->get_teams_info( ) );
			}
			$this->_action_list[ $type_of_item ]['id_to_delete'] = array_diff( $former_ids, $imported_ids );
			$ids_to_modify = array_intersect( $former_ids, $imported_ids );
			foreach ($ids_to_modify as $id) {
				foreach ($this->{$imported_array_name} as $item) {
					if( isset( $item['id'] ) && ( $item['id'] == $id ) ) {
						$this->_action_list[ $type_of_item ]['item_to_modify'][ $id ] = $item;
						break;
					}
				}
			}
			foreach ( $this->{$imported_array_name} as $item ) {
				#if the imported id is empty or not in the list of existing ids then select for create new item
				if( empty( $item['id'] ) ) {
					$this->_action_list[ $type_of_item ]['item_to_create'][] = $item;
				} elseif ( !in_array( $item['id'], $former_ids ) ) {
					#If id is unknown, shift it to more than 1 billion
					$item['id'] = intval( $item['id'] ) + 1000000000;
					$this->_action_list[ $type_of_item ]['item_to_create'][] = $item;
				}
			}
		}
		return $retval;
	}

	/**
     * Fill the table action list for 'teams_members" tha is executed last
     * @return string
	 * @access private
     */
	private function _fill_action_list_teams_members( ) : string {
		$retval = '';
		// Get all ids of persons and teams
		$former_persons_ids = array_keys( $this->_accessData->get_persons_info( 'all' ) );
		$former_teams_ids = array_keys( $this->_accessData->get_teams_info( ) );
		foreach ( $this->_imported_teams_members as $item ) {
			#If id is unknown, shift it to more than 1 billion
			if( !in_array( $item['id_person'], $former_persons_ids ) ) {
				$item['id_person'] = intval( $item['id_person'] ) + 1000000000;
			}
			if( !in_array( $item['id_team'], $former_teams_ids ) ) {
				$item['id_team'] = intval( $item['id_team'] ) + 1000000000;
			}
			$this->_action_list['teams_members']['item_to_create'][] = $item;
		}
		return $retval;
	}

	/**
     * Clean imported array
	 * @param string $type_of_item : type of import table. 'person', 'teams_members" or 'team'
     * @return string
	 * @access private
     */
	private function _clean_imported_array( string $type_of_item ) : string {
		$retval = '';
		$imported_array_name = '_imported_' . $type_of_item . 's';
		if( 'teams_members' === $type_of_item ) {
			$imported_array_name = '_imported_' . $type_of_item;
		}
		if ( !empty( $this->{$imported_array_name} ) ) {
			//Correct eventual malformed indexes
			foreach ( $this->{$imported_array_name} as $key => $item ) {
				foreach ( $item as $subkey => $value ) {				
					$cleaned_subkey = strtolower( trim( $subkey ) );
					if( $subkey !== $cleaned_subkey ) {
						unset( ($this->{$imported_array_name})[$key][ $subkey ] );
						($this->{$imported_array_name})[$key][ $cleaned_subkey ] = $value;
					}
				}
			}
			foreach ( $this->{$imported_array_name} as $key => $item ) {
				foreach ( array_keys( $item ) as $subkey ) {
					if( !in_array( $subkey, self::AUTHORIZED_INDEXES[ $type_of_item ] ) ) {
						unset( $this->{$imported_array_name}[$key][$subkey] );
					}
				}
			}
			//Delete image field if empty (no change if empty)
			foreach ( $this->{$imported_array_name} as $key => $item ) {
				if ( array_key_exists('image', $item ) ) {
					if ( empty( $item['image'] ) ) {
						unset(  $this->{$imported_array_name}[$key]['image'] );
					}
				}
			}

			//Delete items with no mandatory indexes
			$delete_list = array();
			foreach ( array_keys( $this->{$imported_array_name} ) as $key ) {
				foreach ( self::MANDATORY_INDEXES[ $type_of_item ]  as $mandatory_index) {
					if( !isset( $this->{$imported_array_name}[$key][$mandatory_index] ) ) {
						$delete_list[$key] = $key;
					}
				}
			}
			foreach ( $delete_list as $key ) {
				unset( $this->{$imported_array_name}[$key] );
			}
			//If there is no more line to import, generate an error message
			if( empty( $this->{$imported_array_name} ) ) {
				$messages = new Messages();
				$retval .= $messages->notice( 
					'error', 
					__( 'Importation error', 'mon-laboratoire' ), 
					sprintf( __( 'Import file (type %s) is invalid.', 'mon-laboratoire' ), $type_of_item )
				);
			}
		}
		return $retval;
	}

	/**
     * List the forcasted actions for import
	 * @param string $type_of_item : type of import table. 'person' or 'team'
	 * @param int[] $action_list_id : list of id to create or delete
	 * @param string $message : Message to display
     * @return string
	 * @access private
     */
	private function _explain_action_point( string $type_of_item, array $action_list_id,  string $message ) : string {
		$retval = '';
		if( count( $action_list_id ) ) {
			$display_list = array();
			foreach ($action_list_id as $key => $id ) {
				if( is_array( $id ) ) {
					$id = $key; //If it is the item content instead of an id
				}
				//Replace by the modified name if item is modified
				if( isset( $this->_action_list[ $type_of_item ]['item_to_modify'][$id] ) ) {
					$link_to_display = '<a href="admin.php?page=MonLabo_edit_members_and_groups&tab=tab_' . $type_of_item . '&submit_item=' . $id . '&lang=all" class=\'MonLaboLink\'>';
					$display_list[] = $link_to_display . $this->_html->item_name_with_admin_link( $type_of_item, (object) $this->_action_list[ $type_of_item ]['item_to_modify'][$id], false ) . '</a>';
				} else {
					$item = $this->_accessData->get_info ( $type_of_item, $id );
					$display_list[] = $this->_html->item_name_with_admin_link( $type_of_item, (object) $item );
				}
			}
			$icon = $this->_html->dashicon( ( $type_of_item === 'team' ? 'groups' : 'admin-users' ) );
			$retval .= '<li>' . $icon . ' ' . sprintf( $message , count( $display_list ) );
			$retval .=  ' ' . implode( ', ', $display_list). '</li>';
		}
		return $retval;
	}

	/**
     * List the forcasted creations for import
	 * @param string $type_of_item : type of import table. 'person' or 'team'
	 * @param array<int,string[]> $items_to_create : list of item to create
	 * @param string $message : Message to display
     * @return string
	 * @access private
     */
	private function _explain_creation_action_point( string $type_of_item, array $items_to_create,  string $message ) : string {
		$retval = '';
		if( count( $items_to_create ) ) {
			$display_list = array();
			foreach ($items_to_create as $item ) {
				$display_list[] = $this->_html->item_name_with_admin_link( $type_of_item, (object) $item, false );
			}
			$icon = $this->_html->dashicon( ( $type_of_item === 'team' ? 'groups' : 'admin-users' ) );
			$retval .= '<li>' . $icon . ' ' . sprintf( $message , count( $display_list ) );
			$retval .=  ' ' . implode( ', ', $display_list). '</li>';
		}
		return $retval;
	}

	/**
     * List the forcasted creations for import teams_members
	 * @param string $message : Message to display
     * @return string
	 * @access private
     */
	private function _explain_team_members_action_point( string $message ) : string {
		$retval = '';
		$items_to_create = $this->_action_list['teams_members']['item_to_create'];
		if( count( $items_to_create ) ) {
			//Reorganize by team
			//--------------------------------------
			$members_by_list = array();
			foreach ($items_to_create as $item ) {
				$members_by_list[$item['id_team']][] = array('id_person' => $item['id_person'], 'directing' => $item['directing'] );
			}

			// Create list of teams ID after import
			//--------------------------------------
			$former_persons_ids = array_keys( $this->_accessData->get_persons_info( 'all' ) );
			$former_teams_ids = array_keys( $this->_accessData->get_teams_info( ) );
			$created_teams_by_id = array();		
			foreach ( $this->_action_list['team']['item_to_create'] as $team) {
				if( !empty( $team['id'] ) ) {
					$created_teams_by_id[ $team['id'] ] = $team;
				}
			}
			$teams_id_after_import = array_merge( array_diff( $former_teams_ids, $this->_action_list['team']['id_to_delete'] ), array_keys( $created_teams_by_id ) );

			// Display
			//--------
			$display_list = array();
			foreach ( $members_by_list as $team_id => $persons_list ) {
				//Display only teams that will exists after import
				if( in_array( $team_id, $teams_id_after_import ) ) {
					$sub_display_list = array();
					$leader_text = ' - <em>' . __( 'team leader', 'mon-laboratoire' ).'</em>';
					foreach ( $persons_list as $struct ) {
						$sub_display_list[] = $this->_get_item_name_after_import( 'person',  intval( $struct['id_person'] ), $former_persons_ids )
								. ( 1 === intval( $struct['directing'] ) ? $leader_text : '' );
					}
					if ( ! empty( $team_id ) ) {
						if ( 1000000000 >= intval( $team_id ) ) {
							$team_infos = (object) $this->_accessData->get_info ( 'team', intval( $team_id ) );
						} else {
							if( isset( $created_teams_by_id[ $team_id ] ) ) {
								$team_infos = (object) $created_teams_by_id[ $team_id ];
							}
						}
					}
					if ( isset( $team_infos->id ) ) {
						$team_name = $this->_get_item_name_after_import( 'team',  intval( $team_infos->id ), $former_teams_ids );
						$display_list[] = '<strong>'. $team_name . '</strong> : '. implode( ', ', $sub_display_list) . '. ';
					}
				}
			}
			$icon = $this->_html->dashicon( 'networking' );
			$retval .= '<li>' . $icon . ' ' . sprintf( $message , count( $display_list ) );
			$retval .=  ' ' . implode( ', ', $display_list). '</li>';
		}
		return $retval;
	}

	/**
     * Get item name forcasted after import
	 * @param string $type_of_item : type of import table. 'person' or 'team'
	 * @param int $id : ID of item to import
	 * @param int[] $former_items_id : list of the IDs actualy in the database
     * @return string
	 * @access private
     */
	private function _get_item_name_after_import( string $type_of_item, int $id, array $former_items_id ) : string {
		//New item
		if ( 1000000000 < $id ) {
			if( isset( $this->_action_list[ $type_of_item ]['item_to_create'][$id] ) ) {
				return $this->_html->item_name_with_admin_link( $type_of_item, (object) $this->_action_list[ $type_of_item ]['item_to_create'][$id], false );
			}
		}
		//Former item
		if( in_array( $id, $former_items_id) ) {
			//Replace by the modified name if item is modified
			if( isset( $this->_action_list[ $type_of_item ]['item_to_modify'][$id] ) ) {
				return $this->_html->item_name_with_admin_link( $type_of_item, (object) $this->_action_list[ $type_of_item ]['item_to_modify'][$id], false );
			}
			//else get the former name
			$item_infos = $this->_accessData->get_info ( $type_of_item, $id );
			return $this->_html->item_name_with_admin_link( $type_of_item, $item_infos, false );
		}
		return '';
	}

    /**
     * Do the pre-import processing
     * @return string
     */
    function processing() : string {
        $retval = '';
		$messages = new Messages();
		#Display hidden cancel panel to show if action is canceled
		$retval .= '<div id="MonLaboCanceledAction" style="display:none;">'. $messages->notice( 'warning', __( 'Canceled action', 'mon-laboratoire' ), '' ).'</div>';
		$retval .= $this->_clean_imported_array( 'person' );
		$retval .= $this->_clean_imported_array( 'teams_members' );
		$retval .= $this->_clean_imported_array( 'team' );
		$imported_lines = count( $this->_imported_persons ) + count( $this->_imported_teams_members ) + count( $this->_imported_teams );
		if ( $imported_lines > 0 ) {
			$htmlForms = new Html_Forms();
			$retval .= $this->_fill_action_list( 'person' );
			$retval .= $this->_fill_action_list( 'team' );
			$retval .= $this->_fill_action_list( 'teams_members' ); //Must be executed last
			$message_txt = $htmlForms->begin_form( 'confirm_import' );
			$message_txt .= '<h4>' . __( 'WARNING : The following tasks will be carried out if you confirm:', 'mon-laboratoire' ) . '</h4>';
			$message_txt .= '<ul>';
			$message_txt .= $this->_explain_action_point( 'person', $this->_action_list['person']['id_to_delete'],  __( 'The %s following persons will be deleted:', 'mon-laboratoire' ) );
			$message_txt .= $this->_explain_action_point( 'person', $this->_action_list['person']['item_to_modify'],  __( 'The %s following persons will be modified:', 'mon-laboratoire' ) );
			$message_txt .= $this->_explain_creation_action_point( 'person', $this->_action_list['person']['item_to_create'],  __( 'The %s following persons will be created:', 'mon-laboratoire' ) );
			$message_txt .= $this->_explain_action_point( 'team', $this->_action_list['team']['id_to_delete'],  __( 'The %s following teams will be deleted:', 'mon-laboratoire' ) );
			$message_txt .= $this->_explain_action_point( 'team', $this->_action_list['team']['item_to_modify'],  __( 'The %s following teams will be modified:', 'mon-laboratoire' ) );
			$message_txt .= $this->_explain_creation_action_point( 'team', $this->_action_list['team']['item_to_create'],  __( 'The %s following teams will be created:', 'mon-laboratoire' ) );
			$message_txt .= $this->_explain_team_members_action_point( __( 'The new members list will be:', 'mon-laboratoire' )  );
			$message_txt .= '</ul>';
			$message_txt .= $this->_silent_transmit_action_list();
			$message_txt .= $htmlForms->submit_button( __( 'Cancel', 'mon-laboratoire' ), 'MonLaboHideDivButton', '', 'no', 'secondary'  );
			$message_txt .= ' ' . $htmlForms->end_form( 'confirm_import', __( 'Confirm to import.', 'mon-laboratoire' ),	'database-import', 'danger' );
			$retval .= $messages->notice( 'warning', __( 'Please confirm before import', 'mon-laboratoire' ), $message_txt );
		}
		return $retval;
    }

	/**
     * Transmit action list in a hidden form
     * @return string
	 * @access private
     */
    private function _silent_transmit_action_list( ) : string {
		$htmlForms = new Html_Forms();
		$retval = '';
		$retval .= $htmlForms->silent_transmit_ids( 'persons_to_delete', $this->_action_list['person']['id_to_delete'] );
		$retval .= $htmlForms->silent_transmit_array_of_struct( 'persons_to_modify', $this->_action_list['person']['item_to_modify'] );
		$retval .= $htmlForms->silent_transmit_array_of_struct( 'persons_to_create', $this->_action_list['person']['item_to_create'] );
		$retval .= $htmlForms->silent_transmit_ids( 'teams_to_delete', $this->_action_list['team']['id_to_delete'] );
		$retval .= $htmlForms->silent_transmit_array_of_struct( 'teams_to_modify', $this->_action_list['team']['item_to_modify'] );
		$retval .= $htmlForms->silent_transmit_array_of_struct( 'teams_to_create', $this->_action_list['team']['item_to_create'] );
		$retval .= $htmlForms->silent_transmit_array_of_struct( 'teams_members', $this->_action_list['teams_members']['item_to_create'] );
        return $retval;
    }
}