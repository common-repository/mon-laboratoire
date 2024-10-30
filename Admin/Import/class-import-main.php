<?php
namespace MonLabo\Admin\Import;
use MonLabo\Frontend\{Html};
use MonLabo\Lib\{Options};
use MonLabo\Admin\{Forms_Processing,Html_Forms,Csv};


defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

/////////////////////////////////////////////////////////////////////////////////////
// Main functions of import process
/////////////////////////////////////////////////////////////////////////////////////
/*
class Import_Main {
    _input_one_file( string $form_name, string $label, string $description )
    render_main_form( )
    processing()
*/

/**
 * Class \MonLabo\Admin\Import\Import_Main
 * @package
 */
class Import_Main extends Forms_Processing {

	/**
	 * Constructor
	 * @param array<string,mixed> $post_data: argument for the constructor
	 */
	function  __construct( array $post_data = array() ) {
		parent::__construct( $post_data );
		//in order to be able to verify nonce with check_admin_referer()
        $this->_init_nonces( array( 'import_cvs_files', 'confirm_import', 'confirm_post_import' ));
	}

	/**
	 * Generate form for chosing file to import
	 * @return string HTML code
	 * @access private
	 */
	private function _input_one_file( string $form_name, string $label, string $description ): string {
		$retval = '<div class="input-group">';
		$retval .= '<label for="' . $form_name . '">' . $label . '</label>';
		$retval .= '<input type="file" name="submit_' . $form_name . '" id="' . $form_name . '" accept=".csv">';
		$retval .= '<div class="description">' . $description . '</div>';
		$retval .= '</div>';
		return $retval;
	}

    /**
     * Render main form for importation
     * @return string
     */
    function render_main_form() : string {
        $htmlForms = new Html_Forms();
		$html = new Html();

        $retval = '';
		$options = Options::getInstance();
		if ( $options->uses['members_and_groups'] ) {
			$retval .= $htmlForms->begin_form('import_cvs_files' );
			$retval .= $this->_input_one_file(
					'import_persons',
					$html->dashicon( 'admin-users' ) . __( 'Import all persons and erase former ones', 'mon-laboratoire' ),'' );
			$retval .=  boostrap_info_modal( $html->dashicon( 'editor-help' ) . __( 'Help for persons import', 'mon-laboratoire' ),  
					  '<p>' . $html->dashicon( 'database-export' ) . __( '<strong>Export persons</strong> to see the data structure.', 'mon-laboratoire' ) . '</p>'
					. '<p>' . __( 'Please use the following minimum mandatory columns:', 'mon-laboratoire' )  . ' <strong>id, first_name, last_name, category, function_fr, function_en</strong></p>' 
					. '<p>' . __( 'Read these tips before completing the following columns:', 'mon-laboratoire' ) . '</p>'
					. '<ul>'
					. '<li>' . __( '<strong>id:</strong> This column should be filled with the appropriate value to replace a former person. For creating a new person use an <strong>id</strong> field empty or not used (ex: >1000). By default, any person who is not in the list of ids in the imported file will be deleted.' , 'mon-laboratoire' )  . '</li>'
					. '<li>' . __( '<strong>wp_post_id:</strong> This column is ignored.', 'mon-laboratoire'). ' ' . __( 'At the end of the import, you will be asked if you want to create pages for new persons.' , 'mon-laboratoire' )  . '</li>'
					. '<li>' . __( '<strong>image:</strong> If this column is empty, it will be ignored (image is not changed for an existing person).', 'mon-laboratoire'). ' ' . __( 'For importing new image, please drop new files in the <a href="media-new.php">Upload new media</a> interface, then fill the filename in this column.' , 'mon-laboratoire' )  . '</li>'
					. '</ul>',
					'','',400
			);
			$retval .= '<br />' . $this->_input_one_file(
				'import_teams_members',
				$html->dashicon( 'networking' ) . __( 'Import teams_members relation table and erase former one', 'mon-laboratoire' ), '' );
			$retval .=  boostrap_info_modal( $html->dashicon( 'editor-help' ) . __( 'Help for teams_members import', 'mon-laboratoire' ),  
					'<p>' . $html->dashicon( 'database-export' ) . __( '<strong>Export team_member</strong> to see the data structure.', 'mon-laboratoire' ) . '</p>'
				. '<p>' . __( 'Please use the following mandatory columns:', 'mon-laboratoire' )  . ' <strong>id_person, id_team, directing</strong></p>' 
				. '<p>' . __( 'If this file is imported, all former relations will be deleted.', 'mon-laboratoire' ) . '</p>',
				'','',400
			  );
			$retval .= '<br />' . $this->_input_one_file(
				'import_teams',
				$html->dashicon( 'groups' ) . __( 'Import all teams and erase former ones', 'mon-laboratoire' ),'' );
			$retval .= boostrap_info_modal( $html->dashicon( 'editor-help' ) . __( 'Help for teams import', 'mon-laboratoire' ),
					'<p>' . $html->dashicon( 'database-export' ) . __( '<strong>Export teams</strong> to see the data structure.', 'mon-laboratoire' ) . '</p>'
					. '<p>' . __( 'Please use the following minimum mandatory columns:', 'mon-laboratoire' )  . ' <strong>id, name_fr, name_en</strong></p>' 
					. '<p>' . __( 'Read these tips before completing the following columns:', 'mon-laboratoire' ) . '</p>'
					. '<ul>'
					. '<li>'. __( '<strong>id:</strong> Use the same <strong>id</strong> management policy as for importing persons.' , 'mon-laboratoire' )  . '</li>'
					. '<li>' . __( '<strong>wp_post_id:</strong> This column is ignored.', 'mon-laboratoire'). ' ' . __( 'At the end of the import, you will be asked if you want to create pages for new teams.' , 'mon-laboratoire' )  . '</li>'
					. '</ul>',
					'','',400
			);
			$retval .= '<br />' . $htmlForms->end_form(
				'import_cvs_files',
				__( 'Import persons, teams or teams members table from CVS files', 'mon-laboratoire' ),
				'database-import', 'danger'
			);
		}
		//Pages des anciens membres toujours publiées
		return $retval;
    }

	/**
	 * Process the forms of advanced features for import items
	 * @return string HTML code
	 */
	public function processing(): string {
		$retval = '';
		$imported_persons = array();
		$imported_teams_members = array();
		$imported_teams = array();
		if ( $this->_check_nonce( 'import_cvs_files' ) ) {
			$csv = Csv::getInstance();
			if ( isset( $_FILES['submit_import_persons'] ) ) {     
				if ( !empty( $_FILES['submit_import_persons']['tmp_name'] ) ) {     
					$imported_persons = $csv->importCsvToArray( $_FILES['submit_import_persons']['tmp_name'] );
				}
			}
			if ( isset( $_FILES['submit_import_teams_members'] ) ) {     
				if ( !empty(( $_FILES['submit_import_teams_members']['tmp_name'] ) ) ) {     
					$imported_teams_members = $csv->importCsvToArray( $_FILES['submit_import_teams_members']['tmp_name'] );
				}
			}
			if ( isset( $_FILES['submit_import_teams'] ) ) {     
				if ( !empty(( $_FILES['submit_import_teams']['tmp_name'] ) ) ) {     
					$imported_teams = $csv->importCsvToArray( $_FILES['submit_import_teams']['tmp_name'] );
				}
			}						
			$Pre_Import = New Pre_Import( $imported_persons, $imported_teams_members, $imported_teams );
			$retval .= $Pre_Import->processing( );
		}
		if ( $this->_check_nonce( 'confirm_import' ) ) {
			$Import = New Import();
			$retval .= $Import->processing();
		}
		if ( $this->_check_nonce( 'confirm_post_import' ) ) {
			$Post_Import = New Post_Import();
			$retval .= $Post_Import->processing();
		}
		return $retval;
	}

}