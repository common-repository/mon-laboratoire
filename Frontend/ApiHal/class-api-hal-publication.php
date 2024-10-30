<?php
namespace MonLabo\Frontend\ApiHal;

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );
/*
class Api_Hal_publication {
	_convertToString( $value )
	_cleanParrallelStringArrays( $array1, $array2 )
	__construct( array $data_publi, string $format )
	_get_name_to_apa_format( int $author_index )
	_generate_firstname( string $fullname, string $lastname)
	format_authors( )
}
*/

/**
 * Class \MonLabo\Frontend\Api_Hal_publication
 * Usefull to format one single publication downloaded from https://api.archives-ouvertes.fr/ with API HAL V3.0
 * @package
 */
class Api_Hal_publication {

	/**
	* Json object of publication
	* @var array
	*/
	public $publi = array();

	/**
	* Format of publication
	* @var string
	*/
	public $format;

	/**
	* Number of authors
	* @var int
	*/
	public $nb_authors;

	/**
	 * Convert a value into string even if it is an array or an object
	 * @param mixed $value value to convert
	 * @return string converted value
	 * @access private
	 */
	private function _convertToString( $value ) : string {
		if ( is_array( $value ) || is_object( $value ) ) {
			$value = '';
		} elseif ( !is_string( $value ) ) {
			$value = strval( $value );
		}
		return $value;
	}

	/**
	 * Clean two parralel tables of string
	 * @param array $array1 first array
	 * @param array $array2 second array
	 * @return array<int, string[]> converted arrays
	 * @access private
	 */
	private function _cleanParrallelStringArrays( $array1, $array2 ) : array {
		//STEP 1 : Convert each array values to string
		$array1  = array_map( array( $this, '_convertToString' ), $array1 );
		$array2  = array_map( array( $this, '_convertToString' ), $array2 );

		//STEP 2 : Pad arrays to get the same size
		$maxSize = max( count( $array1 ), count( $array2 ) );
		$array1 = array_pad( $array1, $maxSize, '' );
		$array2 = array_pad( $array2, $maxSize, '' );

		//STEP 3 : Empty holes in arrays (with value of the other array)
		//         or suppress value if both are empty
		$count = count( $array1 );
		for ( $i = 0; $i < $count; $i++ ) {
			if ( empty( $array1[ $i ] ) ) {
				$array1[ $i ] = $array2[ $i ];
			} elseif ( empty( $array2[ $i ] ) ) {
				$array2[ $i ] = $array1[ $i ];
			}
			if ( empty( $array1[ $i ] ) && empty( $array2[ $i ] ) ) {
				unset( $array1[ $i ] );
				unset( $array2[ $i ] );
			}
		}
		return array( array_values( $array1 ), array_values( $array2 ) );
	}

	/**
	* constructor
	 * @param array $data_publi publication data as an array
	 * @param string $format format of publication to display (hal, apa, ieee...)
	 */
	public function __construct( array $data_publi, string $format = 'hal' ) {
		$this->publi = $data_publi;
		$this->format = $format;

		//Create empty authors list if necessary
		if ( !isset( $this->publi['authFullName_s'] ) || !is_array( $this->publi['authFullName_s'] ) ) {
			$this->publi['authFullName_s'] = array();
		}
		if ( !isset( $this->publi['authLastName_s'] ) || !is_array( $this->publi['authLastName_s'] ) ) {
			$this->publi['authLastName_s'] = array();
		}

		//Clean athors name arrays
		list( $this->publi['authFullName_s'], $this->publi['authLastName_s'] ) =
			$this->_cleanParrallelStringArrays(
				$this->publi['authFullName_s'],
				$this->publi['authLastName_s']
			);

		$this->nb_authors = count( $this->publi['authFullName_s'] );
	}

	/**
	 * Get an athor name into APA format
	 * @param int $author_index number of author in the publication
	 * @return string Name of the author in the APA format
	 * @access private
	 */
	private function _get_name_to_apa_format( int $author_index ) : string {
		$fullname = $this->publi['authFullName_s'][ $author_index ];
		$lastname = $this->publi['authLastName_s'][ $author_index ];
		$firstname = $this->_generate_firstname( $fullname, $lastname );
		if ( empty( $firstname ) ) {
			return $fullname;
		}
		$words = explode( ' ', $firstname );
		$initials = '';
		foreach ( $words as $word ) {
			$initials .= mb_strtoupper( mb_substr( $word, 0, 1, 'UTF-8' ), 'UTF-8' ) . '. ';
		}
		$retval = $lastname . ', ' . rtrim( $initials ) . ', ';
		return rtrim( $retval, ' ,' );
	}

	/**
	 * Generate first name from full name and last name
	 * @param string $fullname Full name
	 * @param string $lastname Last name
	 * @return string First name generated
	 * @access private
	 */
	private function _generate_firstname( string $fullname, string $lastname ) : string {
		$lastname_length = strlen( $lastname );
		if ( substr( $fullname, -$lastname_length ) === $lastname ) {
			return rtrim( substr( $fullname, 0, -$lastname_length ) );
		}
		return '';
	}

	/**
	 * authors formating
	 * @return string list of names to display
	 */
	public function format_authors( ) : string {
		if ( !empty(  $this->publi ) ) {
			switch ( $this->format ) {
				case 'apa':
					$apa_names = array();
					foreach ( array_keys( $this->publi['authFullName_s'] ) as $author_index ) {
						$apa_names[] = $this->_get_name_to_apa_format( $author_index );
					}
					$nb_authors = count( $apa_names );
					if ( $nb_authors > 1 ) {
						$last_apa_name = array_pop( $apa_names );
						return implode( ', ', $apa_names ) . ' & ' . $last_apa_name;
					} else {
						return implode( ', ', $apa_names );
					}
				
				default: //hal
					return implode( ', ', $this->publi['authFullName_s'] );
			}		
		}
		return '';
	}

	/**
	 * Publication formating
	 * @return string Publication to display
	 */
	public function format( ) : string {	
		$type_name = array( //https://api.archives-ouvertes.fr/ref/doctype?lang=fr&instance_s=cnrs
			'ART'			=> array( 'en' => 'Journal articles'						, 'fr' => 'Article dans une revue' ),
			'COMM'			=> array( 'en' => 'Conference papers'					   	, 'fr' => 'Communication dans un congrès' ),
			'POSTER'		=> array( 'en' => 'Poster communications'					, 'fr' => 'Poster de conférence' ),
			'PROCEEDINGS'	=> array( 'en' => 'Proceedings'					   			, 'fr' => 'Proceedings/Recueil des communications' ),
			'ISSUE'			=> array( 'en' => 'Special issue'						 	, 'fr' => 'N°spécial de revue/special issue' ),
			'OUV'		  	=> array( 'en' => 'Books'								   	, 'fr' => 'Ouvrages' ),
			'COUV'  		=> array( 'en' => 'Book sections'						   	, 'fr' => 'Chapitre d\'ouvrage' ),
			//'BLOG'  		=> array( 'en' => 'Scientific blog post'					   	, 'fr' => 'Article de blog scientifique' ),
			//'NOTICE'  		=> array( 'en' => 'Dictionary entry'					   	, 'fr' => 'Notice d’encyclopédie ou de dictionnaire' ),
			//'TRAD'  		=> array( 'en' => 'Translation'						   		, 'fr' => 'Traduction' ),
			'PATENT'		=> array( 'en' => 'Patents'								 	, 'fr' => 'Brevet' ),
			'OTHER'		  	=> array( 'en' => 'Other publications'					  	, 'fr' => 'Autre publication scientifique' ),
			//array( 'UNDEFINED', 'PREPRINT', 'WORKINGPAPER' )
			'UNDEFINED'		  	=> array( 'en' => 'Other publications'					  	, 'fr' => 'Pré-publication, Document de travail' ),
			//array( 'REPORT', 'RESREPORT', 'TECHREPORT', 'FUNDREPORT', 'EXPERTREPORT', 'DMP' )
			//			=> array( 'en' => 'Reports'								 	, 'fr' => 'Rapport' ),
			'THESE'	 		=> array( 'en' => 'Theses'								  	, 'fr' => 'Thèse' ),
			'HDR'		   	=> array( 'en' => 'Habilitation à diriger des recherches'	, 'fr' => 'HDR' ),
			'LECTURE'		=> array( 'en' => 'Lectures'							 	, 'fr' => 'Cours' ),
			'SOFTWARE'		=> array( 'en' => 'Software'							 	, 'fr' => 'Logiciel' ),
			// Not in CNRS'MEM' 			=> array( 'en' => 'Master thesis'						   	, 'fr' => 'Mémoire d\'étudiant' ),
			// Not in CNRS'OTHERREPORT'	=> array( 'en' => 'Other reports'						 	, 'fr' => 'Autre rapport, séminaire, workshop' ),
		);
		if ( !empty(  $this->publi ) ) {
			switch ( $this->format ) {
				case 'apa':
					break;
				
				default: //hal
					return '';
			}		
		}
		return '';
	}

}
