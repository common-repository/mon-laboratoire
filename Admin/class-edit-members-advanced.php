<?php
namespace MonLabo\Admin;
use MonLabo\Lib\{Polylang_Interface, Translate, Options};
use MonLabo\Admin\Import\{Import_Main};

// MySQL host name, user name, password, database, and table
defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

/*
class Edit_Members_Advanced {
	__construct()
	_export_item_form( string $form_id, string $type_of_items )
	_items_without_page_form( string $form_id, string $type_of_items )
	_items_without_translated_page_form( string $form_id, string $type_of_items )
	_items_with_invalid_page_form( string $form_id, string $type_of_items )
	_alumni_with_no_draft_page_form( string $form_id )
	_items_with_bad_parent_page_form( string $form_id, string $type_of_items )
	_get_all_items( string $type_of_items )
	render_advanced_features()
	_render_advanced_features_export()
	_render_advanced_features_for_members()
	_render_advanced_features_for_teams()
	_render_advanced_features_for_thematics()
	_render_advanced_features_for_units()
}
*/
/**
 * Class \MonLabo\Admin\Edit_Members_Advanced
 * @package
 */
class Edit_Members_Advanced extends Edit_Members {

	/**
	 * Current instance of Polylang_Interface
	* @access private
	* @var Polylang_Interface
	 */
	private $_Polylang_Interface = null;

	/**
	 * Current instance of Translate
	* @access private
	* @var Translate
	 */
	private $_translate = null;

	/**
	 * Title of table
	* @access private
	* @var string[]
	 */
	private $_colums_titles = array();

	/**
	 * Content of table
	* @access private
	* @var array<string[]>
	 */
	private $_table_content = array();

	/**
	 * Create a new class
	 */
	public function  __construct() {
		parent::__construct();
		$this->_Polylang_Interface = new Polylang_Interface();
		$this->_translate = new Translate();
		$this->_translate->get_lang();
	}

	/**
	 * Export all the items in a CSV file
	 * @param string $form_id ID of the form
	 * @param string $type_of_items ( 'person', 'team', 'thematic' or 'unit' )
	 * @return string HTML code
	 * @access private
	 */
	private function _export_item_form( string $form_id, string $type_of_items ) : string {
		switch ( $type_of_items ) {
			case 'person':
				$button_text = __( 'Export persons', 'mon-laboratoire' );
				$type_of_items = 'all_persons';
				break;
			case 'team':
				$button_text = __( 'Export teams', 'mon-laboratoire' );
				break;
			case 'thematic':
				$button_text = __( 'Export thematics', 'mon-laboratoire' );
				break;
			case 'unit':
				$button_text = __( 'Export units', 'mon-laboratoire' );
				break;					
			case 'teams_members':
				$button_text = __( 'Export teams_members relation table', 'mon-laboratoire' );
				break;
			default:
				return '';
		}
		$retval = $this->_htmlForms->begin_form($form_id );
		//Establish list of export lines
		$list_of_items = $this->_get_all_items( $type_of_items );
		if ( empty( $list_of_items ) ) { return ''; }
		$nb =  count( $list_of_items );
		$retval .= $this->_htmlForms->end_form(
			$form_id,
			$button_text. ' (' . $nb . ')',
			'database-export', 'secondary', ( $nb > 0 ? false : true )
		);
		return $retval;
	}

	/**
	 * Get all the items that have no pages
	 * @param string $form_id ID of the form
	 * @param string $type_of_items ( 'person', 'team', 'thematic' or 'unit' )
	 * @return string HTML code
	 * @access private
	 */
	private function _items_without_page_form( string $form_id, string $type_of_items ) : string {
		$button_text = __( 'Create missing pages', 'mon-laboratoire' );
		$retval = $this->_htmlForms->begin_form($form_id );
		//Establish list of members without page
		$list_of_items = $this->_get_all_items( $type_of_items );
		if ( empty( $list_of_items ) ) { return ''; }
		$retval .= '<p><strong>' . __( 'Missing WordPress page for:', 'mon-laboratoire' ) . '</strong></p>';

		$items_without_page = array();
		foreach ( $list_of_items as $item_id => $item ) {
			if ( empty( $item->wp_post_ids ) or (0 === count($item->wp_post_ids ) )  ) {
				$items_without_page[ $item_id ] = $item;
			}
		}

		//Display missing pages
		$links = array();
		if ( ! empty( $items_without_page ) ) {
			foreach ( $items_without_page as $item ) {
				$links[] = $this->_html->item_name_with_logo( $type_of_items, $item );
			}
		}
		if  ( ! empty( $links ) ) {
			$messages = new Messages();
			$retval .= $messages->warning_if_necessary_unconfigured_parent( $type_of_items, 'force_display' );
			$retval .= '<ul><li>' . implode( '</li><li>', $links ) . '</li></ul>';
		}
		$retval .= $this->_htmlForms->silent_transmit_ids($form_id,  array_keys( $items_without_page ) );
		$nb = count( $items_without_page );
		$retval .= $this->_htmlForms->end_form(
				$form_id,
				$button_text. ' (' . $nb . ')',
				'welcome-add-page', 'warning', ( $nb > 0 ? false : true )
			);
		return $retval;
	}

	/**
	 * Get all the items that have no translated pages
	 * @param string $form_id ID of the form
	 * @param string $type_of_items ( 'person', 'team', 'thematic' or 'unit' )
	 * @return string HTML code
	 * @access private
	 */
	private function _items_without_translated_page_form( string $form_id, string $type_of_items ) : string {
		$button_text = __( 'Create missing pages translations', 'mon-laboratoire' );
		$retval = $this->_htmlForms->begin_form($form_id );
		//Establish list of members without translated page
		$list_of_items = $this->_get_all_items( $type_of_items );
		if ( empty( $list_of_items ) ) { return ''; }
		$retval .= '<p><strong>' . __( 'WordPress page without translation for:', 'mon-laboratoire' ) . '</strong></p>';
		$items_without_translated_page = array();
		foreach ( $list_of_items as $item_id => $item ) { //For all items
			if ( !empty( $item->wp_post_ids ) ) {
				foreach ( $item->wp_post_ids  as $wp_post_id ) {
					$is_numeric_wp_post_id = ( ( (string) abs( intval( $wp_post_id ) ) ) ===  $wp_post_id );
					if  ( ( $is_numeric_wp_post_id ) and ( get_post( intval( $wp_post_id ) ) ) ) {
						if ( $wp_post_id === (string) $this->_Polylang_Interface->get_translated_post_if_exists( intval( $wp_post_id ) ) ) {
							if ( empty( $items_without_translated_page[ $item_id ] ) ) {
								$items_without_translated_page[ $item_id ] = array();
							}
							array_push( $items_without_translated_page[ $item_id ], intval( $wp_post_id ) );
						}
					}
				}
			}
		}				
		//Display missing pages
		$links = array();
		$list_of_untranslated_pages_id = array();
		if ( ! empty( $items_without_translated_page ) ) {
			foreach ( $items_without_translated_page as $item_id => $wp_post_ids ) {
				$page_list = array();
				/** @phpstan-ignore-next-line */ /* Empty array passed to foreach.  */
				foreach ( $wp_post_ids as $wp_post_id ) {
					$wp_post_id = intval( $wp_post_id );
					$list_of_untranslated_pages_id[ $wp_post_id ] = $wp_post_id;
					$page_list[ $wp_post_id ] = $this->_html->page_full_info( $wp_post_id  );
				}
				$links[] = $this->_html->item_name_with_logo( $type_of_items, $list_of_items[ $item_id ] )
					. ' (' . implode( ', ', $page_list )  . ')';
			}
		}
		if  ( ! empty( $links ) ) {
			$messages = new Messages();
			$retval .= $messages->warning_if_necessary_unconfigured_parent( $type_of_items, 'force_display' );
			$retval .= '<ul><li>' . implode( '</li><li>', $links ) . '</li></ul>';
		}
		$retval .= $this->_htmlForms->silent_transmit_ids($form_id, $list_of_untranslated_pages_id );
		$nb = count( $items_without_translated_page );
		$retval .= $this->_htmlForms->end_form(
				$form_id,
				$button_text . ' (' . $nb . ')',
				'flag', 'warning', ( $nb > 0 ? false : true )
			);
		return $retval;
	}

	/**
	 * Get the list of all items
	 * @param string $type_of_items ( 'person', 'team', 'thematic' or 'unit' )
	 * @return array<int,object> array of items
	 * @access private
	 */
	private function _get_all_items( string $type_of_items ) : array {
		switch ( $type_of_items ) {
			case 'alumni': 			return $this->_accessData->get_persons_info( 'alumni' );
			case 'person': 			return $this->_accessData->get_persons_info( 'actif' );
			case 'all_persons': 	return $this->_accessData->get_persons_info( 'all' );
			case 'team':			return  $this->_accessData->get_teams_info();
			case 'thematic':		return $this->_accessData->get_thematics_info();
			case 'unit':			return $this->_accessData->get_units_info();
			case 'teams_members':	return $this->_accessData->get_teams_members_info( );
			default:				return array();
		}
	}

	/**
	 * Get all the items that have an invalid page
	 * @param string $form_id ID of the form
	 * @param string $type_of_items ( 'person', 'team', 'thematic' or 'unit' )
	 * @return string HTML code
	 * @access private
	 */
	private function _items_with_invalid_page_form( string $form_id, string $type_of_items ) : string {
		$button_text = __( 'Removes invalid page numbers', 'mon-laboratoire' );
		$retval = $this->_htmlForms->begin_form($form_id );
		//Establish list of members without page
		$list_of_items = $this->_get_all_items( $type_of_items );
		if ( empty( $list_of_items ) ) { return ''; }
		$retval .= '<p><strong>' . __( 'Invalid WordPress page IDs for:', 'mon-laboratoire' ) . '</strong></p>';

		$items_with_invalid_page = array();
		foreach ( $list_of_items as $the_id => $item ) {
			if ( !empty( $item->wp_post_ids ) ) {
				foreach ($item->wp_post_ids as $wp_post_id ) {
					$is_numeric_wp_post_id = ( ( (string) abs( intval( $wp_post_id ) ) ) ===  $wp_post_id );
					if  ( ( $is_numeric_wp_post_id )
							and ( null === get_post( intval( $wp_post_id ) ) ) ) {
						$items_with_invalid_page[ $the_id ]=$item;
					}
				}
			}
		}
		//Display missing pages
		$links = array();
		if ( ! empty( $items_with_invalid_page ) ) {
			foreach ( $items_with_invalid_page as $item ) {
				$links[] = $this->_html->item_name_with_logo( $type_of_items, $item );
			}
		}
		if  ( ! empty( $links ) ) {
			$messages = new Messages();
			$retval .= $messages->warning_if_necessary_unconfigured_parent( $type_of_items, 'force_display' );
			$retval .= '<ul><li>' . implode( '</li><li>', $links ) . '</li></ul>';
		}
		$retval .= $this->_htmlForms->silent_transmit_ids($form_id, array_keys( $items_with_invalid_page ) );
		$nb = count( $items_with_invalid_page );
		$retval .= $this->_htmlForms->end_form(
				$form_id,
				$button_text. ' (' . $nb . ')',
				'trash', 'danger', ( $nb > 0 ? false : true )
			);
		return $retval;
	}

	/**
	 * Get all the alumni that have a not draft page
	 * @param string $form_id ID of the form
	 * @return string HTML code
	 * @access private
	 */
	private function _alumni_with_no_draft_page_form( string $form_id ) : string {
		$retval = $this->_htmlForms->begin_form($form_id );
		//Establish list of alumni with published page
		$list_of_alumni = $this->_get_all_items( 'alumni' );
		if ( empty( $list_of_alumni ) ) { return ''; }
		$retval .= '<p><strong>' . __( 'WordPress page IDs of alumni that are not draft:', 'mon-laboratoire' ) . '</strong></p>';

		$page_list = array();
		$temp_retval = '';
		foreach ( $list_of_alumni as $alumni ) {
			if ( !empty(  $alumni->wp_post_ids ) ) {
				$alumni_page_list = array();
				foreach ( $alumni->wp_post_ids as $wp_post_id ) {
					if ( 'draft' !== get_post_status( intval( $wp_post_id ) ) ) {
						$wp_post_id = intval( $wp_post_id );
						$page_list[ $wp_post_id ] = $wp_post_id;
						$alumni_page_list[ $wp_post_id ] = $this->_html->page_full_info( $wp_post_id  );
					}
				}
				if( !empty( $alumni_page_list ) ) {
					$temp_retval .=  '<li>' . $this->_html->item_name_with_logo( 'person', $alumni ) . ' (' 
								. implode( ', ', $alumni_page_list ) . ')</li>';
				}
			}
		}
		if ( '' != $temp_retval ) {
			$retval .= '<ul>' . $temp_retval . '</ul>';
		}
		$retval .= $this->_htmlForms->silent_transmit_ids($form_id, array_keys( $page_list ) );
		$nb = count( $page_list );
		$retval .= $this->_htmlForms->end_form(
				$form_id,
				__( 'Switch to draft alumni pages', 'mon-laboratoire' ) . ' (' . $nb . ')',
				'editor-unlink', 'warning', ( $nb > 0 ? false : true )
			);
		return $retval;
	}

	/**
	 * Get all the items that have bad parent page
	 * @param string $form_id ID of the form
	 * @param string $type_of_items ( 'person', 'team', 'thematic' or 'unit' )
	 * @return string HTML code
	 * @access private
	 */
	private function _items_with_bad_parent_page_form( string $form_id, string $type_of_items ) : string {
		$button_text = __( 'Correct wrong parent pages', 'mon-laboratoire' );
		$retval = $this->_htmlForms->begin_form($form_id );
		//Establish list of members without page
		$list_of_items = $this->_get_all_items( $type_of_items );
		if ( empty( $list_of_items ) ) { return ''; }
		$retval .= '<p><strong>' . __( 'Wrong WordPress parent page for:', 'mon-laboratoire' ) . '</strong></p>';

		$options10 = get_option( 'MonLabo_settings_group10' );
		switch ( $type_of_items ) {
			case 'person':
			case 'alumni':
					$option_name = 'MonLabo_perso_page_parent';
				break;
			case 'team':
				$option_name = 'MonLabo_team_page_parent';
				break;			
			case 'thematic':
				$option_name = 'MonLabo_thematic_page_parent';
				break;	
			default:
				$option_name = 'MonLabo_unit_page_parent';
				break;
		}

		$page_list = array();
		$parent = '';
		$temp_retval = '';
		if ( isset( $options10[ $option_name ] ) && ( ! empty( $options10[ $option_name  ] ) ) ) {
			$parent = intval( $options10[ $option_name ] );
			foreach ( $list_of_items as $item ) { //For all items (persons, teams...)
				if ( ! empty( $item->wp_post_ids ) ) {
					$bad_parent_page_list = array();
					foreach ( $item->wp_post_ids as $wp_post_id ) { //For all pages of each item
						$status = get_post_status( intval( $wp_post_id ) );
						if ( ( 'draft' === $status ) || ( 'publish' === $status ) ) {
							if( intval( wp_get_post_parent_id( $wp_post_id ) ) != $parent ) { //If bad parent
								$wp_post_id = intval( $wp_post_id );
								$page_list[ $wp_post_id ] = $wp_post_id;
								$bad_parent_page_list[ $wp_post_id ] = $this->_html->page_full_info( $wp_post_id );						
							}
						}
					}
				}
				if( !empty( $bad_parent_page_list ) ) {
					$temp_retval .=  '<li>'. $this->_html->item_name_with_logo(  $type_of_items, $item ) 
							. ' ('  . implode( ', ', $bad_parent_page_list ) . ')</li>';
				}
			}
		} 
		
		if ( '' != $temp_retval ) {
			$messages = new Messages();
			$retval .= $messages->warning_if_necessary_unconfigured_parent( $type_of_items, 'force_display' );
			$retval .= '<ul>' . $temp_retval . '</ul>';
		}
		$retval .= $this->_htmlForms->silent_transmit_ids( $form_id, array_keys( $page_list ) );
		$retval .= $this->_htmlForms->silent_transmit_text( $form_id, $parent );
		$nb = count( $page_list );
		$retval .= $this->_htmlForms->end_form(
				$form_id,
				$button_text . ' (' . $nb . ')',
				'edit-page', 'warning', ( $nb > 0 ? false : true )
			);
		return $retval;
	}	
	
	/**
	 * Generate form for advanced features for persons, teams...
	 * @return string HTML code
	 */
	function render_advanced_features(): string {
		$formsProcessingAdvanced = new Forms_Processing_Advanced();
		$retval = $formsProcessingAdvanced->form_advanced_features_processing();

		$retval .= __( 'Shortcuts:', 'mon-laboratoire' ) . ' <a href="#export_import">' .  __( 'Export / Import items', 'mon-laboratoire' ) . '</a>, <a href="#manage_pages">' . __( 'Manage pages', 'mon-laboratoire' ) . '</a>';
		$retval .= '<h1> ' . $this->_html->dashicon( 'warning' ) . __( 'CAUTION: potentially dangerous tools. Use them only if you know what you are doing!', 'mon-laboratoire' ) . '&nbsp;' . $this->_html->dashicon( 'warning' ) . '</h1>';

		// Import / Export
		//----------------
		$retval .= '<br /><h2 id="export_import">' . __( 'Export', 'mon-laboratoire' ) . '</h2>';
		$retval .= $this->_render_advanced_features_export();
		$retval .= '<div class="MonLabo-advanced-tool-table">'
						 . $this->_html->generic_table( '', '' , $this->_colums_titles, $this->_table_content )
						 . '</div>';

		$retval .= '<br /><h2>' . __( 'Import', 'mon-laboratoire' ) . '</h2>';
		$mainImport = new Import_Main();
		$retval .= $mainImport->render_main_form();

		// Manage pages
		//-------------
		$retval .= '<br/><br/><h2 id="manage_pages">' . __( 'Manage pages', 'mon-laboratoire' ) . '</h2>';
		//Config Titles
		$this->_table_content = array();
		$this->_colums_titles = array();
		$retval .= $this->_render_advanced_features_for_members();
		$retval .= $this->_render_advanced_features_for_teams();
		$retval .= $this->_render_advanced_features_for_thematics();
		$retval .= $this->_render_advanced_features_for_units();
		//Display table
		$retval .= '<div class="MonLabo MonLabo-advanced-tool-table">'
						 . $this->_html->generic_table( '', '' , $this->_colums_titles, $this->_table_content )
						 . '</div>';
		return $retval;
	}

	/**
	 * Generate form for advanced features for export
	 * @return string HTML code
	 * @access private
	 */
	private function  _render_advanced_features_export(): string {
		$options = Options::getInstance();
		if ( $options->uses['members_and_groups'] ) {
			$this->_colums_titles[] = '#dashicon-admin-users#' . __( 'Persons', 'mon-laboratoire' );
			$this->_table_content['export'][] = $this->_export_item_form('export_persons',  'person') . '<br/>'
												. $this->_export_item_form('export_teams_members',  'teams_members');
			$this->_colums_titles[] = '#dashicon-groups#' . __( 'Teams', 'mon-laboratoire' );
			$this->_table_content['export'][] = $this->_export_item_form('export_teams',  'team'); 
			if ( $options->uses['thematics'] ) {	
				$this->_colums_titles[] = '#dashicon-buddicons-groups#' . $this->_translate->tr__( 'Thematics' ) ;
				$this->_table_content['export'][] = $this->_export_item_form('export_thematics',  'thematic'); 
			}
			if ( $options->uses['units'] ) {		
				$this->_colums_titles[] = '#dashicon-admin-multisite#' . __( 'Units', 'mon-laboratoire' );
				$this->_table_content['export'][] = $this->_export_item_form('export_units',  'unit'); 
			}
		}
		//Pages des anciens membres toujours publiées
		return '';
	}


	/**
	 * Generate form for advanced features for persons
	 * @return string HTML code
	 * @access private
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	private function _render_advanced_features_for_members(): string {
		$retval = '';
		$options10 = get_option( 'MonLabo_settings_group10' );
		$options = Options::getInstance();
		if ( $options->uses['members_and_groups'] ) {
			if (
				isset( $options10['MonLabo_perso_page_parent'] )
				&& ( ! empty( $options10['MonLabo_perso_page_parent'] ) )
			) {
				$this->_colums_titles[] = '#dashicon-admin-users#' . __( 'Persons', 'mon-laboratoire' );
				$this->_table_content['missing'][] = $this->_items_without_page_form( 'create_missing_person_pages',  'person' );	
				if ( $this->_Polylang_Interface->is_polylang_to_use() ) {
					$this->_table_content['translate'][] = $this->_items_without_translated_page_form(  'create_missing_translated_person_pages', 'person' );
				}
				$this->_table_content['invalid'][] =  $this->_items_with_invalid_page_form( 'suppress_invalid_person_pages', 'person' );
				$this->_table_content['alumni'][] = $this->_alumni_with_no_draft_page_form( 'draft_alumni_published_pages' );
				$this->_table_content['parent'][] = 
						'<h3>' . __( 'Members:', 'mon-laboratoire' ) . '</h3>' . $this->_items_with_bad_parent_page_form( 'correct_bad_parent_person_pages', 'person' )
						. '<h3>' . __( 'Alumni:', 'mon-laboratoire' ) . '</h3>' . $this->_items_with_bad_parent_page_form( 'correct_bad_parent_alumni_pages', 'alumni' );
			} 
		}
		//Pages des anciens membres toujours publiées
		return $retval;
	}

	/**
	 * Generate form for advanced features for teams
	 * @return string HTML code
	 * @access private
	 */
	private function _render_advanced_features_for_teams(): string {
		$options = Options::getInstance();
		if ( $options->uses['members_and_groups'] ) {	
			$this->_colums_titles[] = '#dashicon-groups#' . __( 'Teams', 'mon-laboratoire' );
			$this->_table_content['missing'][] = $this->_items_without_page_form( 'create_missing_team_pages', 'team' );
			if ( $this->_Polylang_Interface->is_polylang_to_use() ) {
				$this->_table_content['translate'][] = $this->_items_without_translated_page_form( 'create_missing_translated_team_pages', 'team' );
			}
			$this->_table_content['invalid'][] = $this->_items_with_invalid_page_form( 'suppress_invalid_team_pages', 'team' );
			$this->_table_content['alumni'][] = 'N.A.';
			$this->_table_content['parent'][] = $this->_items_with_bad_parent_page_form( 'correct_bad_parent_team_pages', 'team' );
		}
		return '';
	}

	/**
	 * Generate form for advanced features for thematics
	 * @return string HTML code
	 * @access private
	 */
	private function _render_advanced_features_for_thematics(): string {
		$options = Options::getInstance();
		if ( $options->uses['members_and_groups'] && $options->uses['thematics'] ) {			
			$this->_colums_titles[] = '#dashicon-buddicons-groups#' . $this->_translate->tr__( 'Thematics' ) ;
			$this->_table_content['missing'][] = $this->_items_without_page_form( 'create_missing_thematic_pages', 'thematic' );
			if ( $this->_Polylang_Interface->is_polylang_to_use() ) {
				$this->_table_content['translate'][] = $this->_items_without_translated_page_form( 'create_missing_translated_thematic_pages', 'thematic' );
			}
			$this->_table_content['invalid'][] = $this->_items_with_invalid_page_form( 'suppress_invalid_thematic_pages', 'thematic' );
			$this->_table_content['alumni'][] = 'N.A.';
			$this->_table_content['parent'][] = $this->_items_with_bad_parent_page_form( 'correct_bad_parent_thematic_pages', 'thematic' );
		}
		return '';
	}

	/**
	 * Generate form for advanced features for unit
	 * @return string HTML code
	 * @access private
	 */
	private function _render_advanced_features_for_units(): string {
		$options = Options::getInstance();
		if ( $options->uses['members_and_groups'] && $options->uses['units'] ) {		
			$this->_colums_titles[] = '#dashicon-admin-multisite#' . __( 'Units', 'mon-laboratoire' );
			$this->_table_content['missing'][] = $this->_items_without_page_form( 'create_missing_unit_pages', 'unit' );
			if ( $this->_Polylang_Interface->is_polylang_to_use() ) {
				$this->_table_content['translate'][] = $this->_items_without_translated_page_form( 'create_missing_translated_unit_pages', 'unit' );
			}
			$this->_table_content['invalid'][] = $this->_items_with_invalid_page_form( 'suppress_invalid_unit_pages', 'unit' );
			$this->_table_content['alumni'][] = 'N.A.';
			$this->_table_content['parent'][] = $this->_items_with_bad_parent_page_form( 'correct_bad_parent_unit_pages', 'unit' );
		}
		return '';
	}

}
?>
