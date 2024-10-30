<?php
namespace MonLabo\Admin\Import;
use MonLabo\Admin\{Html_Forms,Forms_Processing_Advanced};


defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

/////////////////////////////////////////////////////////////////////////////////////
// Import from CSV step 3
/////////////////////////////////////////////////////////////////////////////////////
/*
class Post_Import {
    processing( )
*/

/**
 * Class \MonLabo\Admin\Import\Post_Import
 * @package
 */
class Post_Import {

    /**
     * Do the import processing
     * @return string
     */
    function processing() : string {
        $retval = '';
        $htmlForms = new Html_Forms();
        
        # Retrieve list of new persons and teams
        #---------------------------------------
        $new_persons_ids = array();
        $new_teams_ids = array();
        $htmlForms->get_silent_transmited_ids( 'new_persons_ids_submit_ids', $new_persons_ids );
        $htmlForms->get_silent_transmited_ids( 'new_teams_ids_submit_ids', $new_teams_ids );

        # Process page creation
        #----------------------
        $param_array = array();
        if( count($new_persons_ids) ) {
            $param_array[] =  array( 'type_of_items'=>'person', 'field_id'=>'new_persons_ids_submit_ids');
        }
        if( count($new_teams_ids) ) {
            $param_array[] =  array( 'type_of_items'=>'team', 'field_id'=>'new_teams_ids_submit_ids');
        }
        if ( !empty( $param_array ) ) {
            $formsProcessingAdvanced = new Forms_Processing_Advanced();
            $retval .= $formsProcessingAdvanced->items_without_page_process( 'confirm_post_import', 'dummy', $param_array);
        }
        return $retval;
    }

}