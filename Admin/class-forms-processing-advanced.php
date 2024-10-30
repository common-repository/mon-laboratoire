<?php
namespace MonLabo\Admin;

use MonLabo\Lib\Person_Or_Structure\{Person, Team, Thematic, Unit};
use MonLabo\Frontend\{Html};
use MonLabo\Lib\{Polylang_Interface};
use MonLabo\Lib\Access_Data\{Access_Data};
use MonLabo\Admin\Import\{Import_Main};

defined( 'ABSPATH' ) or die('No direct script access allowed' );

///////////////////////////////////////////////////////////////////////
// MANAGEMENT OF VARIABLES PASSED BETWEEN PAGES
///////////////////////////////////////////////////////////////////////
/*
class Forms_Processing_Advanced {
	__construct()
	_get_the_item( string $type_of_items, int $the_id )
	_get_all_items( string $type_of_items )
	_export_items_process( string $form_id, string $type_of_items ) 
	items_without_page_process( string $form_id, string $type_of_items )
	_items_without_translated_page_process( string $form_id )
	_items_with_invalid_page_process( string $form_id, string $type_of_items )
	_alumni_with_no_draft_page_process( string $form_id )
	_items_with_bad_parent_page_process( string $form_id ) 
	form_advanced_features_processing()
	_form_advanced_features_export_processing()
	_form_advanced_features_for_members_processing()
	_form_advanced_features_for_teams_processing()
	_form_advanced_features_for_thematics_processing()
	_form_advanced_features_for_units_processing()
}
*/
/**
 * Class \MonLabo\Admin\Forms_Processing_Advanced
 * @package
 */
class Forms_Processing_Advanced extends Forms_Processing {

	/**
	 * Constructor
	 * @param array<string,mixed> $post_data : argument for the constructor
	 */
	function  __construct( array $post_data = array() ) {
		parent::__construct( $post_data );
		//in order to be able to verify nonce with check_admin_referer()
		$this->_init_nonces(
			array(
				'export_persons',
				'export_alumni',
				'export_teams_members',
				'export_teams',
				'export_thematics',
				'export_units',
				'delete_exported_files',
				'create_missing_person_pages',
				'create_missing_team_pages',
				'create_missing_thematic_pages',
				'create_missing_unit_pages',
				'create_missing_translated_person_pages',
				'create_missing_translated_team_pages',
				'create_missing_translated_thematic_pages',
				'create_missing_translated_unit_pages',
				'suppress_invalid_person_pages',
				'draft_alumni_published_pages',
				'suppress_invalid_team_pages',
				'suppress_invalid_thematic_pages',
				'suppress_invalid_unit_pages',
				'correct_bad_parent_person_pages',
				'correct_bad_parent_alumni_pages',
				'correct_bad_parent_team_pages',
				'correct_bad_parent_thematic_pages',
				'correct_bad_parent_unit_pages',
			) );
	}

	/**
	 * create new item
	 * @param string $type_of_items ( 'person', 'team', 'thematic' or 'unit' )
	 * @param int $the_id ID of the item
	 * @return Person|Team|Thematic|Unit|object
	 * @access private
	 */
	private function _get_the_item( string $type_of_items, int $the_id ) /*: object //retrocompatibility with PHP7.0 */ {
		switch ( $type_of_items ) {
			case 'person':
				return new Person( 'from_id', $the_id );
			case 'team':
				return new Team( 'from_id', $the_id );
			case 'thematic':
				return  new Thematic( 'from_id', $the_id );
			case 'unit':
				return  new Unit( 'from_id', $the_id );
			default:
				return (object) array();
		}
	}

	/**
	 * Get the list of all items
	 * @param string $type_of_items ( 'person', 'team', 'thematic' or 'unit' )
	 * @return array<int,object> array of items
	 * @access private
	 */
	private function _get_all_items( string $type_of_items ) : array {
		$accessData = new Access_Data();
		switch ( $type_of_items ) {
			case 'alumni':
				return $accessData->get_persons_info( 'alumni' );
			case 'person':
				return $accessData->get_persons_info( 'actif' );
			case 'all_persons':
				return $accessData->get_persons_info( 'all' );
			case 'team':
				return  $accessData->get_teams_info();
			case 'thematic':
				return $accessData->get_thematics_info();
			case 'unit':
				return $accessData->get_units_info();
			case 'teams_members':
				return $accessData->get_teams_members_info( );
			default:
				return array();
		}
	}

	/**
	 * Process all the items that have missing page
	 * @param string $form_id ID of the form
	 * @param string $type_of_items ( 'teams_members', 'person', 'team', 'thematic' or 'unit' )
	 * @return string HTML code
	 * @access private
	 */
	private function _export_items_process( string $form_id, string $type_of_items ) : string {
		$retval = '';
		//-------------------------------------------------------------
		// Export items
		//-------------------------------------------------------------
		if ( $this->_check_nonce( $form_id ) ) {
			if( 'person' !== $type_of_items ) {
				$table_to_export = $this->_get_all_items( $type_of_items );
			} else {
				$table_to_export = $this->_get_all_items( 'all_persons' );
			}
			//All empty images are explicitely coded as 'DEFAULT'
			foreach ($table_to_export as $key => $person) {
				if( property_exists( $person, 'image' ) ) {
					if( empty( $person->image ) ) {
						$table_to_export[ $key ]->image = 'DEFAULT';
					}
				}
			}
			$csv = Csv::getInstance();
			$retval .= $csv->exportArrayToCsv( $type_of_items, $table_to_export );
		}
		return $retval;
	}

	/**
	 * Process all the items that have missing page
	 * @param string $form_id ID of the form
	 * @param string $type_of_items ( 'person', 'team', 'thematic' or 'unit' )
	 * @param array<string[]> $several_fields_and_type if not empty, field id can be tuned. A table of field has to be given else.
	 * @return string HTML code
	 */
	public function items_without_page_process( string $form_id, string $type_of_items, array $several_fields_and_type = array() ) : string {
		$retval = '';
		$html = new Html();
		$messages = new Messages();
		//-------------------------------------------------------------
		// Create missing pages
		//-------------------------------------------------------------
		if( empty( $several_fields_and_type ) ) {
			$several_fields_and_type  = array( array( 'field_id' => $form_id. '_submit_ids', 'type_of_items' => $type_of_items ) );
		}
		$first_field_id = $several_fields_and_type[0]['field_id'];
		if ( isset( $this->post[ $first_field_id ] )
			and $this->_check_nonce( $form_id )
		) {
			foreach ($several_fields_and_type as $value) {
				$type_of_items = $value['type_of_items'];
				$field_id = $value['field_id'];
				$ids_to_create_page = json_decode( stripslashes( sanitize_text_field( $this->post[ $field_id ] ) ) );
				if ( ! empty( $ids_to_create_page ) ) {
					foreach ( $ids_to_create_page as $the_id ) {
						$item = $this->_get_the_item( $type_of_items, intval( $the_id ) );
						if ( ! $item->is_empty() ) {

							//Création de la page personnelle
							//-------------------------------
							if ( 'person' === $type_of_items ) {
								$page = new Page( 'person', array(
									'first_name' => $item->info->first_name,
									'last_name' => $item->info->last_name )
								);
							} else {
								$page = new Page( $type_of_items, array(
									'name_en' => $item->info->name_en,
									'name_fr' => $item->info->name_fr )
								);
							}
							$retval .= $messages->notice(
								'info',
								'',
								sprintf( __( 'Page of %1$s created (%2$s - %3$s)', 'mon-laboratoire' ),
								$html->item_name_with_admin_link( $type_of_items, $item->info ),
								get_the_title( $page->wp_post_id ),
								"<a href='" . get_edit_post_link( $page->wp_post_id ) . "'>" . __( 'Edit page', 'mon-laboratoire'
							) . '</a>' ) );
							if ( 0 === $page->wp_post_id )  {
								return $messages->notice( 'error', 'Echec:', 'Impossible de créer la page personnelle.' );
							}

							// Modification de la ligne dans la table MonLabo_members, MonLabo_teams ...
							//-------------------------------------------------------------------------
							$item->update( array( 'wp_post_ids' => array( $page->wp_post_id ) ) );
						}

					}
				}
			}
		}
		return $retval;
	}

	/**
	 * Process all the items that have no translated pages
	 * @param string $form_id ID of the form
	 * @return string HTML code
	 * @access private
	 */
	private function _items_without_translated_page_process( string $form_id ) : string {
		$retval = '';
		$Polylang_Interface = new Polylang_Interface();
		$messages = new Messages();
		//-------------------------------------------------------------
		// Create missing translated pages
		//-------------------------------------------------------------
		if ( $Polylang_Interface->is_polylang_to_use() ) {
			if ( isset( $this->post[ $form_id . '_submit_ids' ] )
				and $this->_check_nonce( $form_id )
			) {
				$list_of_untranslated_pages_id = json_decode( stripslashes( sanitize_text_field( $this->post[ $form_id.'_submit_ids' ] ) ) );
				$newPagesLinks = array();
				foreach ( $list_of_untranslated_pages_id as $wp_post_id ) {
					$wp_post_id = intval( $wp_post_id );
					$newId = $Polylang_Interface->create_translated_page_if_necessary( $wp_post_id );
					if ( $newId != $wp_post_id ) {
						$newPagesLinks[] = "<a href='" . get_edit_post_link( $newId ) . "'>" . get_the_title( $newId ) . '</a>';
					}
				}
				$retval .= $messages->notice(
					'info',
					'',
					sprintf(
						__( 'Translation of %1$s pages created (%2$s)', 'mon-laboratoire' ),
						'',
						implode( ', ', $newPagesLinks )
					)
				);
			}
		}
		return $retval;
	}

	/**
	 * Process all the items that have an invalid page
	 * @param string $form_id ID of the form
	 * @param string $type_of_items ( 'person', 'team', 'thematic' or 'unit' )
	 * @return string HTML code
	 * @access private
	 */
	private function _items_with_invalid_page_process( string $form_id, string $type_of_items ) : string {
		$retval = '';
		$html = new Html();
		//-------------------------
		// Suppress invalid pages
		//-------------------------
		if ( isset( $this->post[ $form_id . '_submit_ids' ] )
			and $this->_check_nonce( $form_id )
		) {
			$items_id_with_invalid_page = ( json_decode( stripslashes( sanitize_text_field( $this->post[ $form_id . '_submit_ids' ] ) ) ) );
			if ( ! empty( $items_id_with_invalid_page ) ) {
				foreach ( $items_id_with_invalid_page as $the_id ) {
					$invalid_list = array();
					$item = $this->_get_the_item( $type_of_items, intval( $the_id ) );

					if ( ! empty( $item->info->wp_post_ids ) ) {
						foreach ( $item->info->wp_post_ids as $key => $wp_post_id ) {
							$is_numeric_wp_post_id = ( ( (string) abs( intval( $wp_post_id ) ) ) ===  $wp_post_id );
							if  ( ( $is_numeric_wp_post_id )
									and ( null === get_post( intval( $wp_post_id ) ) ) ) {
								unset( $item->info->wp_post_ids[ $key ] );
								$invalid_list[] = intval( $wp_post_id );
							}
						}
					}
					if ( ! $item->is_empty() ) {
						$messages = new Messages();
						$text = __( 'Page %1$s deleted from %2$s.', 'mon-laboratoire' );
						if ( 1 < count( $invalid_list ) ) {
							$text = __( 'Pages %1$s deleted from %2$s.', 'mon-laboratoire' );
						}
						$retval .= $messages->notice(
								'info',
								'',
								sprintf( $text, implode( ', ', $invalid_list ), $html->item_name_with_admin_link( $type_of_items, $item->info ) )
							);

						// Modification de la ligne dans la table MonLabo_members
						//-------------------------------------------------------
						$item->update( array( 'wp_post_ids' => $item->info->wp_post_ids ) );
					}

				}
			}
		}
		return $retval;
	}

	/**
	 * Process all the alumni pages that are not in draft mode
	 * @param string $form_id ID of the form
	 * @return string HTML code
	 * @access private
	 */
	private function _alumni_with_no_draft_page_process( string $form_id ) : string {
		$retval = '';
		//-------------------------
		// Suppress invalid pages
		//-------------------------
		if ( isset( $this->post[ $form_id . '_submit_ids' ] )
			and $this->_check_nonce( $form_id )
		) {
			$pages_id_of_alumni_with_draft_status = ( json_decode( stripslashes( sanitize_text_field( $this->post[ $form_id . '_submit_ids' ] ) ) ) );
			if ( ! empty( $pages_id_of_alumni_with_draft_status ) ) {
				foreach ( $pages_id_of_alumni_with_draft_status as $wp_post_id ) {
					$page = new Page( 'from_id', $wp_post_id );
					$page->to_draft();
				}
				$messages = new Messages();
				$text = __( 'Pages switched to draft:', 'mon-laboratoire' ) . ' ' . implode( ', ', $pages_id_of_alumni_with_draft_status );
				$retval .= $messages->notice( 'info', '', $text );
			}
		}
		return $retval;
	}

	/**
	 * Process all the items that have bad parent pages
	 * @param string $form_id ID of the form
	 * @return string HTML code
	 * @access private
	 */
	private function _items_with_bad_parent_page_process( string $form_id) : string {
		$retval = '';
		if ( isset( $this->post[ $form_id . '_submit_ids' ] )
			and $this->_check_nonce( $form_id )
		) {
			$Polylang_Interface = new Polylang_Interface();
			$pages_id_with_bad_parent = ( json_decode( stripslashes( sanitize_text_field( $this->post[ $form_id . '_submit_ids' ] ) ) ) );
			if ( ! empty( $pages_id_with_bad_parent ) ) {
				$parent = intval( $this->post[ $form_id . '_submit_text' ] );
				if ( ! empty( $parent) ) {
					foreach ( $pages_id_with_bad_parent as $wp_post_id ) {
						$Polylang_Interface->update_page_parent_and_his_translation( $wp_post_id, $parent );
					}
					$messages = new Messages();
					$text = __( 'Parent changed for pages:', 'mon-laboratoire' ) . ' ' . implode( ', ', $pages_id_with_bad_parent );
					$retval .= $messages->notice( 'info', '', $text );
				}
			}
		}
		return $retval;
	}



	/**
	 * Process the forms of advanced features
	 * @return string HTML code
	 */
	function form_advanced_features_processing(): string {
		$mainImport = new Import_Main();
		$retval = $this->_form_advanced_features_export_processing();
		$retval .= $mainImport->processing();
		$retval .= $this->_form_advanced_features_for_members_processing();
		$retval .= $this->_form_advanced_features_for_teams_processing();
		$retval .= $this->_form_advanced_features_for_thematics_processing();
		$retval .= $this->_form_advanced_features_for_units_processing();
		return $retval;
	}
	

	/**
	 * Process the forms of advanced features for export items
	 * @return string HTML code
	 * @access private
	 */
	private function _form_advanced_features_export_processing(): string {
		$retval = $this->_export_items_process( 'export_persons', 'person' );
		$retval .= $this->_export_items_process( 'export_teams_members', 'teams_members' );
		$retval .= $this->_export_items_process( 'export_teams', 'team' );
		$retval .= $this->_export_items_process( 'export_thematics', 'thematic' );
		$retval .= $this->_export_items_process( 'export_units', 'unit' );
		return $retval;
	}

	/**
	 * Process the forms of advanced features for members
	 * @return string HTML code
	 * @access private 
	 */
	private function _form_advanced_features_for_members_processing(): string {
		$retval = '';
		$retval .= $this->items_without_page_process( 'create_missing_person_pages', 'person' );
		$retval .= $this->_items_without_translated_page_process( 'create_missing_translated_person_pages' );
		$retval .= $this->_items_with_invalid_page_process( 'suppress_invalid_person_pages', 'person' );
		$retval .= $this->_alumni_with_no_draft_page_process( 'draft_alumni_published_pages' );
		$retval .= $this->_items_with_bad_parent_page_process( 'correct_bad_parent_person_pages' );
		$retval .= $this->_items_with_bad_parent_page_process( 'correct_bad_parent_alumni_pages' );
		return $retval;
	}

	/**
	 * Process the forms of advanced features for teams
	 * @return string HTML code
	 * @access private 
	 */
	private function _form_advanced_features_for_teams_processing(): string {
		$retval = '';
		$retval .= $this->items_without_page_process( 'create_missing_team_pages', 'team' );
		$retval .= $this->_items_without_translated_page_process( 'create_missing_translated_team_pages' );
		$retval .= $this->_items_with_invalid_page_process( 'suppress_invalid_team_pages', 'team' );
		$retval .= $this->_items_with_bad_parent_page_process( 'correct_bad_parent_team_pages' );
		return $retval;
	}

	/**
	 * Process the forms of advanced features for thematics
	 * @return string HTML code
	 * @access private 
	 */
	private function _form_advanced_features_for_thematics_processing(): string {
		$retval = '';
		$retval .= $this->items_without_page_process( 'create_missing_thematic_pages', 'thematic' );
		$retval .= $this->_items_without_translated_page_process( 'create_missing_translated_thematic_pages' );
		$retval .= $this->_items_with_invalid_page_process( 'suppress_invalid_thematic_pages', 'thematic' );
		$retval .= $this->_items_with_bad_parent_page_process( 'correct_bad_parent_thematic_pages' );
		return $retval;
	}

	/**
	 * Process the forms of advanced features for units
	 * @return string HTML code
	 * @access private 
	 */
	private function _form_advanced_features_for_units_processing(): string {
		$retval = '';
		$retval .= $this->items_without_page_process( 'create_missing_unit_pages', 'unit' );
		$retval .= $this->_items_without_translated_page_process( 'create_missing_translated_unit_pages' );
		$retval .= $this->_items_with_invalid_page_process( 'suppress_invalid_unit_pages', 'unit' );
		$retval .= $this->_items_with_bad_parent_page_process( 'correct_bad_parent_unit_pages' );
		return $retval;
	}

}
?>
