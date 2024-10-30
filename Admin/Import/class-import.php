<?php
namespace MonLabo\Admin\Import;
use MonLabo\Lib\Access_Data\{Access_Data};
use MonLabo\Frontend\{Html};
use MonLabo\Admin\{Html_Forms,Messages};
use MonLabo\Lib\Person_Or_Structure\{Person, Team };



defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

/////////////////////////////////////////////////////////////////////////////////////
// Import from CSV step 2
/////////////////////////////////////////////////////////////////////////////////////
/*
class Import {

	__construct( )
    _is_image_name( string $text )
    _find_image_by_name( string $filename )
    _is_url( string $url )
    _image_name_to_id( string $image_field )
    _retierve_action_list( )
    _do_delete_tasks( string $item_class )
    _do_modify_tasks( string $item_class )
    _do_create_tasks( string $item_class )
    _do_import_from_action_list( )
    _propose_to_create_missing_pages( array $new_persons_ids, array $new_teams_ids )
    _generate_final_status_of_import( )
    processing( )

*/

/**
 * Class \MonLabo\Admin\Import\Import
 * @package
 */
class Import {

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
	* @var array<string,array<string,int[]|array<string|int,string[]>>> $_action_list : actions to to for importation
	* @access private
	*/
	private $_action_list = array();

	/**
	 * translation table of id of new persons
	* @access private
	* @var int[]
	 */
	private $_persons_ids_old_to_new = array();

	/**
	 * translation table of id of new teams
	* @access private
	* @var int[]
	 */
	private $_teams_ids_old_to_new = array();

    /**
	 * Constructor
	 */
	public function __construct( ) {
		$this->_accessData = new Access_Data();
		$this->_html = new Html();
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
     * Retrieve action list in order to process import
     * @return void
	 * @access private
     */
    private function _retierve_action_list( ) {
        $htmlForms = new Html_Forms();
        $htmlForms->get_silent_transmited_ids( 'persons_to_delete_submit_ids', $this->_action_list['person']['id_to_delete'] );
        $htmlForms->get_silent_transmited_array_of_struct( 'persons_to_modify_submit', $this->_action_list['person']['item_to_modify'] );
        $htmlForms->get_silent_transmited_array_of_struct( 'persons_to_create_submit', $this->_action_list['person']['item_to_create'] );
        $htmlForms->get_silent_transmited_ids( 'teams_to_delete_submit_ids', $this->_action_list['team']['id_to_delete'] );
        $htmlForms->get_silent_transmited_array_of_struct( 'teams_to_modify_submit', $this->_action_list['team']['item_to_modify'] );
        $htmlForms->get_silent_transmited_array_of_struct( 'teams_to_create_submit', $this->_action_list['team']['item_to_create'] );
        $htmlForms->get_silent_transmited_array_of_struct( 'teams_members_submit', $this->_action_list['teams_members']['item_to_create'] );
    }

    /**
     * Process deleted tasklist
	 * @param string $type : tye to item (person or team)
     * @return void
	 * @access private
     */
    private function _do_delete_tasks( string $type ) {
        $item_class = 'MonLabo\\Lib\\Person_Or_Structure\\'.ucfirst( $type );
        if (! empty($this->_action_list[ $type ]['id_to_delete'] ) ) {
            foreach ( $this->_action_list[ $type ]['id_to_delete'] as $id) {
                $item = new $item_class('from_id', $id );
                $item->delete();
            }
        }
    }

    /**
     * Process modify tasklist
	 * @param string $type : tye to item (person or team)
     * @return void
	 * @access private
     */
    private function _do_modify_tasks( string $type ) {
        $item_class = 'MonLabo\\Lib\\Person_Or_Structure\\'.ucfirst( $type );
        if (! empty($this->_action_list[ $type ]['item_to_modify'] ) ) {
            foreach ( $this->_action_list[ $type ]['item_to_modify'] as $table_data) {
                $id =  $table_data['id'];
                $item = new $item_class('from_id', $id );
                if ( isset( $table_data['image'] ) ) {
                    $table_data['image'] = $this->_image_name_to_id( $table_data['image'] );
                }
                $item->update( $table_data );
            }
        }
    }

    /**
     * Process creation tasklist
	 * @param string $type : tye to item (person or team)
     * @return int[] List of item created id's
	 * @access private
     */
    private function _do_create_tasks( string $type ) : array {
        $new_items_ids = array();
        $item_class = 'MonLabo\\Lib\\Person_Or_Structure\\'.ucfirst( $type );
        if (! empty($this->_action_list[ $type ]['item_to_create'] ) ) {
            foreach ( $this->_action_list[ $type ]['item_to_create'] as $table_data) {
                if ( isset( $table_data['image'] ) ) {
                    $table_data['image'] = $this->_image_name_to_id( $table_data['image'] );
                }
                $item = new $item_class('insert', $table_data );
                if ( isset( $table_data['id'] ) ) { //If an id is given, reccord it in translation table
                    if( 'person' === $type ) {
                        $this->_persons_ids_old_to_new[$table_data['id']] = $item->info->id;
                    } else {
                        $this->_teams_ids_old_to_new[$table_data['id']] = $item->info->id;
                    }
                }
                $new_items_ids[] = $item->info->id;
            }
        }
        return $new_items_ids;
    }

    /**
     * Process import from actions written to $this->_action_list
     * @return array<int[]> [ new_persons_ids[], new_teams_ids[] ]
	 * @access private
     */
    private function _do_import_from_action_list( ) : array {

        # For persons
        $this->_do_delete_tasks( 'person' );
        $this->_do_modify_tasks( 'person' );
        $new_persons_ids = $this->_do_create_tasks( 'person' );

        # For Teams
        $this->_do_delete_tasks( 'team' );
        $this->_do_modify_tasks( 'team' );
        $new_teams_ids = $this->_do_create_tasks( 'team' );

        //For Teams members
        if (! empty($this->_action_list['teams_members']['item_to_create'] ) ) {
            //Delete former relations.
            $teams_members = $this->_accessData->get_teams_members_info( );
			foreach ( $teams_members as $value) {
				$this->_accessData->delete_relations_between_teams_and_a_person( $value->id_person, array( $value->id_team ) );
			}
            #Create new relations
            foreach ( $this->_action_list['teams_members']['item_to_create'] as $value) {
				$id_person = $value['id_person'];
				$id_team = $value['id_team'];
				# Translate old ids to new
				if( isset( $this->_persons_ids_old_to_new[ $id_person ] ) ) {
					$id_person = $this->_persons_ids_old_to_new[ $id_person ];
				}
				if( isset( $this->_teams_ids_old_to_new[ $id_team ] ) ) {
					$id_team = $this->_teams_ids_old_to_new[ $id_team ];
				}
				#Create new relations			
                if( 1 == $value['directing'] ) {
                    $this->_accessData->add_leader_to_a_team ( $id_person, $id_team );
                } else {
                    $this->_accessData->add_person_to_a_team ( $id_person, $id_team );
                }
            }
        }
        return 	array( $new_persons_ids, $new_teams_ids );
    }


    /**
     * Test if a string looks like an image file name
	 * @param string $text : string to test
     * @return bool
	 * @access private
     */
    private function _is_image_name( string $text ) : bool {
        $regex = '/^.+\.(jpg|jpeg|png|gif)$/i';
        if ( preg_match( $regex, $text) ) {
            return true;
        }
        return false; 
    }

    /**
     * Find in WordPress media librairy the ID of an image with this file name
	 * @param string $filename : name of the file
     * @return int
	 * @access private
     */
    private function _find_image_by_name( string $filename ) : int {
        $args = array(
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'posts_per_page' => -1, // search for all attachements
            'meta_query'     => array(
                array(
                    'key'     => '_wp_attached_file',
                    'value'   => pathinfo( $filename, PATHINFO_BASENAME ), // Exact filename
                    'compare' => 'LIKE',
                ),
            ),
            'orderby'        => 'date',
            'order'          => 'DESC', // Most recent first
        );
        $attachments = get_posts($args);
        if ($attachments) {
            foreach ($attachments as $attachment) {
                // Return the first ID found
                return $attachment->ID;
            }
        }
        return 0; // Return 0 if no media is found
    }

    /**
     * Tell if string is an url
	 * @param string $url : string to test
     * @return bool
	 * @access private
     */
    private function _is_url( string $url ) {
        // Remove all illegal characters from a url
        $url = filter_var($url, FILTER_SANITIZE_URL);
        // Validate url
       return ( filter_var($url, FILTER_VALIDATE_URL) !== false);
    }

    /**
     * Convert potential image name to WordPress media librairy ID
	 * @param string $image_name : image name to convert
     * @return string
	 * @access private
     */
    private function _image_name_to_id( string $image_name ) : string {
        if ( ( ! $this->_is_url( $image_name ) ) and $this->_is_image_name( $image_name ) ) {
            $image_id = $this->_find_image_by_name( $image_name );
            if ( 0 === $image_id ) {
                return 'DEFAULT';
            }
            return strval( $image_id );
        }
        return $image_name;
    }

    /**
     * Propose a form to create missing pages for new persons and teams
	 * @param int[] $new_persons_ids : list of ids of new persons created
	 * @param int[] $new_teams_ids : list of ids of new teams created
     * @return string HTML code of the form
	 * @access private
     */
    private function _propose_to_create_missing_pages( array $new_persons_ids, array $new_teams_ids ) : string {
        $message_txt = '';
        $creation_count = count( $new_persons_ids ) + count( $new_teams_ids );
		if( $creation_count ) {
			$display_list_persons = array();
			$display_list_teams = array();
			foreach ($new_persons_ids as $id) {
				$item = new Person('from_id', $id);
				$display_list_persons[] = $this->_html->item_name_with_admin_link( 'person', $item->info , false );
			}
			foreach ($new_teams_ids as $id) {
				$item = new Team('from_id', $id);
				$display_list_teams[] = $this->_html->item_name_with_admin_link( 'team', $item->info , false );
			}
			$message_txt .= '<p>' . __( 'The following new items do not have a WordPress page:', 'mon-laboratoire' ) . '</p><ul>';
			if( count( $new_persons_ids )) {
				$message_txt .= '<li>' . $this->_html->dashicon('admin-users') . ' ' . __( 'Persons', 'mon-laboratoire' ) . ': '
					.  implode( ', ', $display_list_persons) . '</li>';
			}
			if( count( $new_teams_ids ) ) {
				$message_txt .= '<li>' . $this->_html->dashicon('groups') . ' ' . __( 'Teams', 'mon-laboratoire' ) . ': '
					. implode( ', ', $display_list_teams) . '</li>';
			}
			$message_txt .= '</ul>';
            $htmlForms = new Html_Forms();
			$message_txt .= $htmlForms->begin_form( 'confirm_post_import' );
			$message_txt .= $htmlForms->silent_transmit_ids( 'new_persons_ids', $new_persons_ids );
			$message_txt .= $htmlForms->silent_transmit_ids( 'new_teams_ids', $new_teams_ids );
			$message_txt .= $htmlForms->submit_button( __( 'Cancel', 'mon-laboratoire' ), 'MonLaboHideDivButton', '', 'no', 'secondary'  );
			$message_txt .= ' ' . $htmlForms->end_form( 
				'confirm_post_import', 
				__( 'Create missing pages for new items', 'mon-laboratoire' ) . ' ('. strval( $creation_count ) . ')',
				'welcome-add-page', 'warning'
			);
		}
        return $message_txt;
    }

    /**
     * Generate final status of the importation
     * @return string HTML code of the status
	 * @access private
     */
    private function _generate_final_status_of_import( ) : string {
        $nb_created_persons = count($this->_action_list['person']['item_to_create']);
        $nb_created_teams = count($this->_action_list['team']['item_to_create']);
        $deleted_count = count($this->_action_list['person']['id_to_delete']) + count($this->_action_list['team']['id_to_delete']);
        $modified_count = count($this->_action_list['person']['item_to_modify']) + count($this->_action_list['team']['item_to_modify']);
        $creation_count = $nb_created_persons + $nb_created_teams;
        $teams_members_count = count($this->_action_list['teams_members']['item_to_create']);
        $done_task = array();
        if ( $deleted_count ) {
            $done_task[] = $this->_html->dashicon( 'arrow-right-alt' ) . sprintf( __( 'Deleted %s item(s) successfully.', 'mon-laboratoire' ) , $deleted_count );
        }
        if ( $modified_count ) {
            $done_task[] = $this->_html->dashicon( 'arrow-right-alt' ) . sprintf( __( 'Modified %s item(s) successfully.', 'mon-laboratoire' ) , $modified_count );
        }
        if ( $creation_count ) {
            $done_task[] = $this->_html->dashicon( 'arrow-right-alt' ) . sprintf( __( 'Created or replaced %s item(s) successfully.', 'mon-laboratoire' ) , $creation_count );
        }
        if ( $teams_members_count ) {
            $done_task[] = $this->_html->dashicon( 'arrow-right-alt' ) . sprintf( __( 'Deleted formers relations teams-persons and replaced by %s new ones.', 'mon-laboratoire' ) , $teams_members_count );
        }
        return implode('<br />', $done_task);
    }

    /**
     * Do the import process
     * @return string
     */
    function processing() : string {
		$retval ='';
		$messages = new Messages();

        # Retrieve action list
        $this->_retierve_action_list();

        # Do the import
        list( $new_persons_ids, $new_teams_ids ) = $this->_do_import_from_action_list();

		# Display hidden cancel panel to show if action is canceled
		$retval .= '<div id="MonLaboCanceledAction" style="display:none;">'. $messages->notice( 'warning', __( 'Canceled action', 'mon-laboratoire' ), '' ).'</div>';

        # Display final status
		$message_txt = $this->_generate_final_status_of_import( );

        # Propose to create missing pages
        $message_txt .= $this->_propose_to_create_missing_pages( $new_persons_ids, $new_teams_ids );

        #Display message box with final status of import and creation page form
        $retval .= $messages->notice('info', __( 'Import is finished!', 'mon-laboratoire' ), $message_txt );
		
        return $retval;
    }

}