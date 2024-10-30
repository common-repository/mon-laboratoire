<?php
namespace MonLabo\Admin;
#use MonLabo\Lib\{Polylang_Interface, Translate};

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

/////////////////////////////////////////////////////////////////////////////////////
// IMPORTOR EXPORT CSV
/////////////////////////////////////////////////////////////////////////////////////
/*
class Csv {

	__construct(  )
    _create_media_from_array( string $file_content, string $filename)
    _get_titles_from_array( array $table_to_export )
    _subarrays_to_json( array $line_to_export )
    _escape_csv_values( string $value ) 
    exportArrayToCsv(  string $filename, array $table_to_export, array $titles_of_colums = array() )
    _clean_imported_array( array $table_imported )
    importCsvToArray( string $filename );
*/

/**
 * Class \MonLabo\Admin\Csv
 * @package
 */
class Csv {

	use \MonLabo\Lib\Singleton;

    /**
	 * Constructor
	 */
	private function __construct( ) {
	}

    /**
     * Create a media file from a content in memory
	 * @param string $file_content : Content of file to save
	 * @param string $filename : Name of file
     * @return int|\WP_Error  ID of media
	 * @access private
     */
    function _create_media_from_array( string $file_content, string $filename)  {
        // Save file into a temp dir
        $temp_filepath = wp_tempnam( $filename );
        file_put_contents( $temp_filepath, $file_content );
    
        // Create media file from temp file
        $file = wp_upload_bits( $filename, null, file_get_contents( $temp_filepath ) );
        if ( ! $file['error'] ) {
            $attachment = array(
                'post_mime_type' => 'text/csv',
                'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            $attach_id = wp_insert_attachment( $attachment, $file['file']);
            unlink( $temp_filepath ); //Erase tempfile
            return $attach_id;
        }
        unlink( $temp_filepath ); //Erase tempfile
        return new \WP_Error('upload_error', 'Download media error.');
    }
    

    /**
     * Create an array from the index of first line of $table_to_export
	 * @param array<int,object> $table_to_export : Table to export to CSV and from wich we need a title line
     * @return string[]
     * 
	 * @access private
     */
    private function _get_titles_from_array( array $table_to_export ) : array {
        if ( empty( $table_to_export ) ) {
            return array();
        }
        $first_line = (array) array_values( $table_to_export )[0];
        return array_keys( $first_line );
    }

    /**
     * Transform to json all values of an array that are an array
	 * @param array<string,mixed> $line_to_export : Line of a table to export to CSV and from wich we need to transform subarrays 
     * @return array<string,int|string>
     * 
	 * @access private
     */
    private function _subarrays_to_json( array $line_to_export ) : array {
        $retval = array();
        if ( empty( $line_to_export ) ) {
            return $retval;
        }
        foreach ($line_to_export as $key => $value) {
            if( is_array( $value ) ) {
                $retval[$key] = json_encode( $value );
            } else {
                $retval[$key] = $value;
            }
        }
        return $retval;
    }

    /**
     * escape the quotation marks and enclose the vale with quotation mark if necessary
	 * @param ?string $value : Value to escape
     * @return string escaped value
 	 * @access private
    */
    function _escape_csv_values( $value ) : string { // @phan-suppress-current-line PhanUnreferencedPublicMethod
        if( null === $value ) {
            return '';
        }
        $value = strval( $value );
        if ( strpos( $value, '"' ) !== false || strpos( $value, ',' ) !== false || strpos( $value, "\n" ) !== false) {
            return '"' . str_replace( '"', '""', $value ) . '"';
        }
        return $value;
    }
    
    /**
     * Exports all the responses from the array to a CSV file and downloads it.
	 * @param string $filename : File name to generate (without extension)
	 * @param array<int,object> $table_to_export : Table to export to CSV
	 * @param string[] $titles_of_colums : titles of all columns of CSV. 
     *                                     By default, uses array text index of first line of $table_to_export.
     * @return string
     */
    function exportArrayToCsv(  string $filename, array $table_to_export, array $titles_of_colums = array() ) : string {
        $retval = '';
        // Check if answers are available
        $filename = sanitize_text_field( $filename );
        if ( !empty( $table_to_export ) ) {
            if ( empty( $titles_of_colums ) ) {
                $titles_of_colums = $this->_get_titles_from_array( $table_to_export );
            }
            
            // Prepare content of CSV
            $function_escape = array( $this, '_escape_csv_values' );
            $file_content = implode( ',', array_map( $function_escape, $titles_of_colums) );
            foreach ($table_to_export as $row) { 
                $formed_row = $this->_subarrays_to_json( (array) $row );
                $file_content .= "\n" . implode( ',', array_map( $function_escape , $formed_row  ) );
            }
            //save it
            $attach_id = $this->_create_media_from_array( $file_content, $filename . '.csv' );

            $messages = new Messages();
            if ( is_wp_error( $attach_id )) {
                $retval .= $messages->notice(
                    'error',
                    __( 'Error in export', 'mon-laboratoire'),
                    $attach_id->get_error_message()
                );
            } else {
                $retval .= $messages->notice(
                    'info',
                    __( 'Export successfull to media librairy', 'mon-laboratoire'),
                    ' <a href="' . wp_get_attachment_url( $attach_id ) . '">' . __( 'Download CSV',  'mon-laboratoire' ) . " ($filename.csv)" . '</a>'
                );
            }
        } 
        return $retval;
    }


    /**
     * Clean imported array. Every cell should be indexed with the name of the column.
	 * @param array<int,string[]> $table_imported : Array to clean
     * @return array<int,array<string,string>>
     */
    function _clean_imported_array( array $table_imported ) : array {
        $clean_array = array();
        //A valid CSV should have at least 2 lines
        if ( count( $table_imported ) > 1 ) {
            //Extract names of columns
            $columns_names = $table_imported[0];
            unset( $table_imported[0] );
            if( ( ! empty( $columns_names ) ) && is_array( $columns_names ) ) {
                $nb_line = 0;
                //For every line of table
                foreach ( $table_imported as $line ) {
                    if( ( ! empty( $line ) ) && is_array( $line ) ) {
                        //for every cell of table
                        foreach ( $columns_names as $index => $cell_name ) {
                            //reindex if possible
                            if( isset( $line[ $index ] ) ) {
                                $clean_array[ $nb_line ][ $cell_name ] = $line[ $index ];
                            }
                        }
                        $nb_line++;
                    }
                }
            }
        }
        return $clean_array;
    }

    /**
     * Import the array from a CSV file
	 * @param string $filepath : File path to read
     * @return array<int,array<string,string>>
     */
    function importCsvToArray( string $filepath ) : array {
        $table_imported = array();
        $extension = strtolower( pathinfo( $filepath, PATHINFO_EXTENSION ) );
        if ( 'csv' === $extension ) {
            $csvFile = fopen( $filepath, 'r');
            if ( $csvFile !== false ) {
                while( ! feof( $csvFile ) ){
                    $data = fgetcsv( $csvFile );
                    $table_imported[] = $data;
                }
                fclose($csvFile);
            }
        }
        return $this->_clean_imported_array( $table_imported );
    }
   
}