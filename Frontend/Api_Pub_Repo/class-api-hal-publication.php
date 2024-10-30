<?php
namespace MonLabo\Frontend\Api_Pub_Repo;

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );
/*
class Api_Hal_publication {
	_convertToString( $value )
	_cleanParrallelStringArrays( $array1, $array2 )
	__construct( array $data_publi, string $format )
	_get_name_to_apa_format( int $author_index )
	_get_name_to_ieee_format( int $author_index )
	_generate_firstname( string $fullname, string $lastname )
	format_authors( )
	format_date( )
	_get_publi_value( string $code )
	_format_publi_to( string $format_string )
	format_authors( )
	format( )
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
	* @var array<string,string|string[]>
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

	const TYPE_NAME = array( //https://api.archives-ouvertes.fr/ref/doctype?lang=fr&instance_s=cnrs
		'ART'			=> array( 'en' => 'Journal articles'						, 'fr' => 'Article dans une revue' ),
		'COMM'			=> array( 'en' => 'Conference papers'					   	, 'fr' => 'Communication dans un congrès' ),
		'POSTER'		=> array( 'en' => 'Poster communications'					, 'fr' => 'Poster de conférence' ),
		'PROCEEDINGS'	=> array( 'en' => 'Proceedings'					   			, 'fr' => 'Proceedings/Recueil des communications' ),
		'ISSUE'			=> array( 'en' => 'Special issue'						 	, 'fr' => 'N°spécial de revue/special issue' ),
		'OUV'		  	=> array( 'en' => 'Books'								   	, 'fr' => 'Ouvrages' ),
		'COUV'  		=> array( 'en' => 'Book sections'						   	, 'fr' => 'Chapitre d\'ouvrage' ),
		'BLOG'  		=> array( 'en' => 'Scientific blog post'					, 'fr' => 'Article de blog scientifique' ),
		'NOTICE'  		=> array( 'en' => 'Dictionary entry'					   	, 'fr' => 'Notice d’encyclopédie ou de dictionnaire' ),
		'TRAD'  		=> array( 'en' => 'Translation'						   		, 'fr' => 'Traduction' ),
		'PATENT'		=> array( 'en' => 'Patents'								 	, 'fr' => 'Brevet' ),
		'OTHER'		  	=> array( 'en' => 'Other publications'					  	, 'fr' => 'Autre publication scientifique' ),
		//array( 'UNDEFINED', 'PREPRINT', 'WORKINGPAPER' )
		'UNDEFINED'	  	=> array( 'en' => 'Other publications'					  	, 'fr' => 'Pré-publication, Document de travail' ),
		//array( 'REPORT', 'RESREPORT', 'TECHREPORT', 'FUNDREPORT', 'EXPERTREPORT', 'DMP' )
		'REPORT'			=> array( 'en' => 'Reports'								 	, 'fr' => 'Rapport' ),
		'THESE'	 		=> array( 'en' => 'Theses'								  	, 'fr' => 'Thèse' ),
		'HDR'		   	=> array( 'en' => 'Habilitation à diriger des recherches'	, 'fr' => 'HDR' ),
		'LECTURE'		=> array( 'en' => 'Lectures'							 	, 'fr' => 'Cours' ),
		'SOFTWARE'		=> array( 'en' => 'Software'							 	, 'fr' => 'Logiciel' ),
		// Not in CNRS'MEM' 			=> array( 'en' => 'Master thesis'						   	, 'fr' => 'Mémoire d\'étudiant' ),
		// Not in CNRS'OTHERREPORT'	=> array( 'en' => 'Other reports'						 	, 'fr' => 'Autre rapport, séminaire, workshop' ),
	);
	/*$type_name = array(
		'article'	   => array( 'en' => 'Journal articles'						, 'fr' => 'Article dans une revue' ),
		'inproceedings' => array( 'en' => 'Conference papers'					   , 'fr' => 'Communication dans un congrès' ),
		'incollection'  => array( 'en' => 'Book sections'						   , 'fr' => 'Chapitre d\'ouvrage' ),
		'book'		  => array( 'en' => 'Books'								   , 'fr' => 'Ouvrage (y compris édition critique et traduction)' ),
		'HDR'		   => array( 'en' => 'Habilitation à diriger des recherches'   , 'fr' => 'HDR' ),
		'mastersthesis' => array( 'en' => 'Master thesis'						   , 'fr' => 'Mémoire d\'étudiant' ),
		'techreport'	=> array( 'en' => 'Reports'								 , 'fr' => 'Rapport' ),
		'misc'		  => array( 'en' => 'Other publications'					  , 'fr' => 'Autre publication' ),
		//Sont aussi classés dans la categorie bibtex @misc les catégories HAL suivantes :
		//			  - Posters
		//			  - Autre rapport, séminaire, workshop
		//			  - Document associé à des manifestations scientifiques
		//			  - Carte
		'phdthesis'	 => array( 'en' => 'Theses'								  , 'fr' => 'Thèse' ),
		'unpublished'   => array( 'en' => 'Preprints, Working Papers, ...'		  , 'fr' => 'Pré-publication, Document de travail' ),
		//Sont aussi classés dans la categorie bibtex @unpublished les catégories HAL suivantes :
		//			  - Cours
		'patent'		=> array( 'en' => 'Patents'								 , 'fr' => 'Brevet' ), //TODO rajouter le formatage
		'proceedings'   => array( 'en' => 'Directions of work or proceedings'	   , 'fr' => 'Direction d’ouvrage, Proceedings, Dossier' ), //TODO rajouter le formatage
	);*/
	const FORMAT_IEEE = array(
		'ART' =>
			', "#{TITLE},"| <i>#{JOURNAL}</i>|, vol. #{VOLUME}|, no. #{NUMBER}|, pp. #{PAGES}|, |#{MONTH}. |#{YEAR}',
		'COMM' => // Communication dans un congrès
			', "#{TITLE},"| in <i>#{BOOKTITLE}</i>|, #{ADDRESS}|, |#{MONTH}. |#{YEAR}|, pp. #{PAGES}',
		'COUV' =>  // Chapitre d’ouvrage
			', "#{TITLE},"| in <i>#{BOOKTITLE}</i>| (#{SERIES})|, #{PUBLISHER}|, |#{MONTH}. |#{YEAR}|, pp. #{PAGES}',
		'OUV' => // Ouvrage (y compris édition critique et traduction )
			', <i>#{TITLE}</i>| (#{SERIES})|, #{PUBLISHER}|, |#{MONTH}. |#{YEAR}|, pp. #{PAGES}',
		'HDR' => // HDR
			', "#{TITLE},"| H.D.R. dissertation|, #{SCHOOL}|, |#{MONTH}. |#{YEAR}|, pp. #{PAGES}',
		//'mastersthesis' => // Mémoire d’étudiant
		//	', "#{TITLE},"| student dissertation|, #{SCHOOL}|, |#{MONTH}. |#{YEAR}|, pp. #{PAGES}',
		//'techreport' => // Rapport
		//	', "#{TITLE},"| unpublished|, |#{MONTH}. |#{YEAR}|, pp. #{PAGES}',
		//'misc' => //Autre publication
		//	', "#{TITLE},"| unpublished|, <i>#{HOWPUBLISHED}</i>|, |#{MONTH}. |#{YEAR}|, pp. #{PAGES}',
		'THESE' => // Thèse
			', "#{TITLE},"| M.S. thesis|, #{SCHOOL}|, |#{MONTH}. |#{YEAR}|, no. #{NUMBER}|, pp. #{PAGES}',
		//'unpublished' => // Pré-publication, Document de travail
		//	', "#{TITLE},"| unpublished|, |#{MONTH}. |#{YEAR}|, pp. #{PAGES}',
		'PATENT' => // brevet //http://libraryguides.vu.edu.au/ieeereferencing/standardsandpatents //Author(s) Initial(s). Surname(s), “Title of patent,” Published Patent\'s country of origin and number xxxx, Abbrev. Month. Day, Year of Publication.​
			', "#{TITLE},"| #{ADDRESS}| Patent #{NUMBER}|, |#{MONTH}. |#{YEAR}',
		'PROCEEDINGS' => // Direction d’ouvrage, Proceedings, Dossier //http://libraryguides.vu.edu.au/ieeereferencing/confernenceproceedings //Author( s) Initial( s). Surname( s), “Title of paper,” in <i>Abbrev. Name of Conf.</i>, [location of conference is optional], [vol., no. if available], Year, pp. xxx–xxx.
			', "#{TITLE},"| in <i>#{BOOKTITLE}</i>|, #{ADDRESS}|, |#{MONTH}. |#{YEAR}|, vol. #{VOLUME}|, no. #{NUMBER}|, pp. #{PAGES}',
		'AUTOMATIC_SUFFIX' => //à rajouter à la fin de toutes les lignes
			' <a target="_blank" rel="noopener noreferrer" href="http://dx.doi.org/#{DOI}">|&lt;#{DOI}&gt;</a>.| <a target="_blank" rel="noopener noreferrer" href="https://hal.science/#{HAL_ID}">|&lt;#{HAL_ID}&gt;</a>.',
	);

	//https://www.scribbr.fr/wp-content/uploads/2020/09/Manuel-APA-de-Scribbr-7eme-edition.pdf
	const FORMAT_APA = array(
		'ART' =>
			' #{TITLE}.| <i>#{JOURNAL}</i>|, #{VOLUME}|(#{NUMBER})|, #{PAGES}',
		'COMM' => // Communication dans un congrès
			' <i>#{TITLE}</i>.| Paper presented at the #{BOOKTITLE}|, #{ADDRESS}|, #{VOLUME}| (#{NUMBER})|, #{PAGES}',
		'COUV' => // Chapitre d’ouvrage
			' In #{EDITOR},| <i>#{BOOKTITLE}</i>|, <i>#{BOOKTITLE}</i>|, #{SERIES}|, #{VOLUME}| (#{NUMBER})|, #{PAGES}',
		'OUV' => // Ouvrage (y compris édition critique et traduction)
			' <i>#{TITLE}</i>| #{PUBLISHER}|, <i>#{BOOKTITLE}</i>|, #{SERIES}|, #{VOLUME}| (#{NUMBER})|, #{PAGES}',
		'HDR' => // HDR
			' <i>#{TITLE}</i>|, HDR not published |, HDR not published #{SCHOOL}',
		//'mastersthesis' => // Mémoire d’étudiant
		//	' <i>#{TITLE}</i>|, HDR not published |, HDR not published #{SCHOOL}|, #{PAGES}',
		//'techreport' => // Rapport
		//	' <i>#{TITLE}</i>| (#{NUMBER})|, #{INSTITUTION}|, #{PAGES}',
		//'misc' => // Autre publication
		//	' <i>#{TITLE}</i>|, Paper presented at the #{HOWPUBLISHED}|, #{PAGES}',
		'THESE' => // Thèse
			' <i>#{TITLE}</i>| (#{NUMBER})|, Thesis not published |, Thesis not published #{SCHOOL}',
		//'unpublished' => // Pré-publication, Document de travail
		//	' <i>#{TITLE}</i>|, #{PAGES}',
		'PATENT' => // brevet //http://libraryguides.vu.edu.au/apa-referencing/patents-and-standards
			' <i>#{TITLE}</i>| <i>No. #{NUMBER}</i>|#{ADDRESS}|: #{PUBLISHER}',
		'PROCEEDINGS' => // Direction d’ouvrage, Proceedings, Dossier //http://libraryguides.vu.edu.au/apa-referencing/conference-proceedings
			', "#{TITLE},"|, #{EDITOR}|, <i>#{BOOKTITLE}</i>| (pp. #{PAGES})|. |#{ADDRESS}|: #{PUBLISHER}',
		'AUTOMATIC_SUFFIX' => //à rajouter à la fin de toutes les lignes
			' <a target="_blank" rel="noopener noreferrer" href="http://dx.doi.org/#{DOI}">|&lt;#{DOI}&gt;</a>.| <a target="_blank" rel="noopener noreferrer" href="https://hal.science/#{HAL_ID}">|&lt;#{HAL_ID}&gt;</a>.',
	);

	//Le format MLA : Modern Language Association
	//Chicago Style Citation
	//Harvard Referencing Style https://www.emeraldgrouppublishing.com/archived/portal/fr/authors/harvard/2.htm
	//AFNOR, ISO 690 : https://tutos.bu.univ-rennes2.fr/ld.php?content_id=31779651
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
	 * @param mixed[] $array1 first array
	 * @param mixed[] $array2 second array
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
	 * @param array<string,string|string[]> $data_publi publication data as an array
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
		$firstname = str_replace( '-', ' -', $firstname ); //quand un nom est composé de tirets
		$words = explode( ' ', $firstname );
		$initials = '';
		foreach ( $words as $word ) {
			$initial = mb_substr( $word, 0, 1, 'UTF-8' );
			if ( '-' === $initial ) {
				$initial .=  mb_substr( $word, 1, 1, 'UTF-8' );
				//quand un nom est composé de tirets il faut inclure ce dernier dans les initiales tout en ajoutant un point entre chaque initiale.
			}
			$initials .= mb_strtoupper( $initial, 'UTF-8' ) . '. ';
		}
		$initials = str_replace( '. -', '.-', $initials ); //quand un nom est composé de tirets
		$retval = $lastname . ', ' . rtrim( $initials ) . ', ';
		return rtrim( $retval, ' ,' );
	}

	/**
	 * Get an athor name into IEEE format
	 * @param int $author_index number of author in the publication
	 * @return string Name of the author in the IEEE format
	 * @access private
	 */
	private function _get_name_to_ieee_format( int $author_index ) : string {
		//	https://www.grafiati.com/en/info/ieee/authors/
		$fullname = $this->publi['authFullName_s'][ $author_index ];
		$lastname = $this->publi['authLastName_s'][ $author_index ];
		$firstname = $this->_generate_firstname( $fullname, $lastname );
		if ( empty( $firstname ) ) {
			return $fullname;
		}
		$firstname = str_replace( '-', ' -', $firstname ); //quand un nom est composé de tirets
		$words = explode( ' ', $firstname );
		$initials = '';
		foreach ( $words as $word ) {
			$initial = mb_substr( $word, 0, 1, 'UTF-8' );
			if ( '-' === $initial ) {
				$initial .=  mb_substr( $word, 1, 1, 'UTF-8' );
				//quand un nom est composé de tirets il faut inclure ce dernier dans les initiales tout en ajoutant un point entre chaque initiale.
			}
			$initials .= mb_strtoupper( $initial, 'UTF-8' ) . '. ';
		}
		$initials = str_replace( '. -', '.-', $initials ); //quand un nom est composé de tirets
		$retval =  rtrim( $initials ) . ' ' . $lastname;
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
		if (substr( $fullname, -$lastname_length ) === $lastname ) {
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
					$names = array();
					foreach ( array_keys( $this->publi['authFullName_s'] ) as $author_index ) {
						$names[] = $this->_get_name_to_apa_format( $author_index );
					}
					$nb_authors = count( $names );
					if ( 1 === $nb_authors ) {
						return reset( $names );
					} elseif ( $nb_authors > 19 ) {
						//https://www.scribbr.fr/manuel-normes-apa/
						//Avec plus de 19 auteurs, mettre les 19 premiers auteurs puis … et le dernier auteur
						$first_names = array_slice( $names, 0, 19 );
						$last_name = end( $names );
						return implode( ', ', $first_names ) . ',… ' . $last_name;
					} else {
						//Between 2 to 19 authors
						$last_name = array_pop( $names );
						return implode( ', ', $names ) . ' & ' . $last_name;
					}

				case 'ieee':
					$names = array();
					foreach ( array_keys( $this->publi['authFullName_s'] ) as $author_index ) {
						$names[] = $this->_get_name_to_ieee_format( $author_index );
					}
					$nb_authors = count( $names );
					if ( 1 === $nb_authors ) {
						return reset( $names );
					} elseif ( $nb_authors > 6 ) {
						//https://www.grafiati.com/en/info/ieee/authors/
						//For a source with more than 6 authors, give only the first author’s name followed by ‘et al.’ in italic font:
						$first_name = reset( $names );
						return $first_name . ' <em>et al.</em>';
					} else {
						//Between 2 to 6 authors
						$last_name = array_pop( $names );
						return implode( ', ', $names ) . ' and ' . $last_name;
					}
				default : //hal
					return implode( ', ',  $this->publi['authFullName_s'] );
			}		
		}
		return '';
	}


	const TRANSLATE_MONTH = array(
		1 => 'Jan.',
		2 => 'Feb.',
		3 => 'Mar.',
		4 => 'Apr.',
		5 => 'May',
		6 => 'Jun.',
		7 => 'Jul.',
		8 => 'Aug.',
		9 => 'Sep.',
		10 => 'Oct.',
		11 => 'Nov.',
		12 => 'Dec.',
	);

	/**
	 * date formating
	 * @return string date
	 */
	public function format_date( ) : string {
		if ( !empty(  $this->publi ) ) {
			switch ( $this->format ) {
				case 'apa':
					if ( !empty( $this->publi['producedDateY_i'] ) ) {
						$date = '('. $this->publi['producedDateY_i'] . ').';
					} else {
						if ( !empty( $this->publi['inPress_bool'] ) ) {
							$date = '(in press).';
						} else {
							$date = '(n. d.).';
						}
					}
					return $date;
				
				case 'ieee':
					$date = '';
					if ( !empty( $this->publi['producedDateY_i'] ) ) {
						if ( !empty( $this->publi['producedDateM_i'] )
							&& array_key_exists( $this->publi['producedDateM_i'], self::TRANSLATE_MONTH )
						) {
							$date .= self::TRANSLATE_MONTH[ intval( $this->publi['producedDateM_i' ] ) ] . ' ';
						}
						$date .= $this->publi['producedDateY_i'] ;
					}
					return $date;
					//https://www.grafiati.com/en/info/ieee/journal-article/

				default: //hal
					return '(' . $this->publi['producedDate_s'] . ')';
			}		
		}
		return '';
	}

	const TRANSLATE_CODE = array(
		'TITLE' => 'en_title_s',
		'JOURNAL' => 'journalTitle_s',
		'VOLUME' => 'volume_s',
		'NUMBER' => 'issue_s',
		'PAGES' => 'page_s',
		'MONTH' => '',
		'YEAR' => '',
		'BOOKTITLE' => '',
		'SERIES' => 'serie_s',
		'PUBLISHER' => 'journalPublisher_s',
		'EDITOR' => 'publisher_s',
		'DOI' => 'doiId_s',
		'ISBN' => 'isbn_s',
		'URL' => 'uri_s',
		'HAL_ID' => 'halId_s',

	);

	/**
	 * Get publication value from code
	 * @param string $code format code of publication
	 * @return string value corresponding in publication
	 * @access private
	 */
	private function _get_publi_value( string $code ) : string {
		$retval = '';
		if ( isset( self::TRANSLATE_CODE[ $code ] ) ) {
			if ( isset( $this->publi[self::TRANSLATE_CODE[ $code ]] ) ) {
				$retval = $this->publi[self::TRANSLATE_CODE[ $code ]];
			}
		}
		if (   ( 'TITLE' === $code )
			|| ( 'NUMBER' === $code ) ) {
			if ( isset( $retval[0] ) ){
				$retval = $retval[0];
			}
		}
		return strval( $retval );
	}


	/**
	 * Generate first name from full name and last name
	 * @param string $format_string of publication
	 * 				 Example : ' | #{TITLE}.| <i>#{JOURNAL}</i>|, #{VOLUME}| (#{NUMBER})|, #{PAGES}',
	 * @return string format generated in HTML
	 * @access private
	 */
	private function _format_publi_to( string $format_string ) : string {
		$retval = $this->format_authors( ) . ' ' . $this->format_date( );
		$format_items = explode( '|',  $format_string );
		if ( false != $format_items ) {
			foreach ( $format_items as $format_item ) {
				// extraxt field name
				$field_code  = '';
				$found = preg_match( '/#\\{(.*?)\\}/', $format_item, $matches );
				if ( 0 === $found ) {
					$retval .= $format_item;
					continue;
				}
				if ( isset( $matches[1] ) ) {
					$field_code = strval( $matches[1] );
					$publi_value = $this->_get_publi_value( $field_code );
					if ( ! empty( $publi_value ) ) {
						// Replace field code by "%s"
						$printformat = str_replace("#{" . $field_code . "}", "%s", $format_item );
						$retval .= sprintf( $printformat,  $publi_value );
					}
				}
			}
		}	
		return $retval;
	}

	/**
	 * Publication formating
	 * @return string Publication to display
	 */
	public function format( ) : string {	
		if ( !empty(  $this->publi ) ) {
			switch ( $this->format ) {
				case 'apa':
					if ( array_key_exists( $this->publi['docType_s'], self::FORMAT_APA ) ) {
						return $this->_format_publi_to(
							self::FORMAT_APA[ strval( $this->publi['docType_s' ] )] . '|' . self::FORMAT_APA['AUTOMATIC_SUFFIX']
						);
					}
					break;

				case 'ieee' :
					if ( array_key_exists( $this->publi['docType_s'], self::FORMAT_IEEE ) ) {
						return $this->_format_publi_to(
							self::FORMAT_IEEE[ strval( $this->publi['docType_s' ] ) ] . '|' . self::FORMAT_IEEE['AUTOMATIC_SUFFIX']
						);
					}
					break;

				default: //hal
					return $this->publi['citationFull_s'];
			}		
		}
		return '';
	}

}
