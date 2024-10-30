<?php
namespace MonLabo\Lib;

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

/* Class \MonLabo\Lib\Lib {

	//Manage arrays
	secured_array_keys( $array )
	secured_implode( $separation, $array )

	//manage strings
	to_html( $string )
	//sansaccent_et_en_minuscule( $chaine )
	generate_french_index_alphabetic_order( $string )

	//various
	get_current_url_without_queries()
	get_file_url( $file )
*/

 /**
  * Class \MonLabo\Lib\Lib
 *
 * @package
 */


class Lib {
	/**
	* Returns the URL of the current page without any additional
	* query string ( ?foo = bar&toto )
	* @return string URL
	* @static
	*/
	public static function get_current_url_without_queries(): string {
		$protocol = ( ( ! empty( $_SERVER['HTTPS'] ) && 'off' !== $_SERVER['HTTPS'] ) || 443 === $_SERVER['SERVER_PORT'] ) ? 'https://' : 'http://';
		return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}

	/**
	* Can use array_keys even with empty arguments
	* @param mixed[]|null $array array
	* @return array<int|string> list of keys
	* @static
	*/
	public static function secured_array_keys( $array ): array {
		if ( ! empty( $array ) ) {
			return array_keys( $array );
		}
		return array();
	}

	/**
	* Can use implode even with empty arguments
	* @param array<string|int>|null $array array
	* @return string string imploded
	* @static
	*/
	public static function secured_implode( string $separation, $array ): string {
		if ( is_array( $array ) ) {
			return implode( $separation, array_map( 'strval', $array ) );
		}
		return '';
	}

	/**
	* Return the URL to access to a file
	* 'subdir/file.png' => https://url/mon-laboratoire/subdir/file.png
	* function plugins_url( 'subdir/file.png', __FILE__ ) gives the wrong answer in a subdirectory.
	* @param string $file : file name
	* @return string URL
	* @static
	*/
	public static function get_file_url( string $file ): string {
		return plugins_url( '', __DIR__ ) . '/' . $file;
	}

	/**
	 * sanitize mixed string-int into string
	 * @param int|string $value
	 * @return string sanitized string
	 * @static
	 */
	 public static function sanitize_text_or_int_field( $value ): string {
	 	if ( is_int( $value ) ) {
			return strval( $value );
		}
		return sanitize_text_field( $value );
	}

	/**
	* Convert a string into lowercap non-accented characters  (useful for sorting)
	* @param string $chaine string to convert
	* @return string converted string
	* @static
	*/
	/*public static function sansaccent_et_en_minuscule( string $chaine ): string {
	   return iconv( 'UTF-8', 'ASCII//TRANSLIT',  mb_strtolower( $chaine, 'UTF-8' ) );
	}*/

	/**
	*  Converts an accented string into an alphabetically ordered index.
	*  Converted a string of characters to lowercase non-accented characters.
	*  Add numbers after the accented and accented carracteristics in order to
	*  order them.
	*   e -> e0
	*   é -> e1
	*   è -> e2
	*   ê -> e3
	*   ë -> e4
	* @param string $string string to convert
	* @return string converted string
	* @static
	*/
	public static function generate_french_index_alphabetic_order( string $string ): string {
		$no_ligatures = str_replace (
			array( 'æ', 'Æ', 'œ', 'Œ', 	/* français */
					'ĳ', 'Ĳ', 		   	/* néerlandais */
					'ß',				/* allemand */
				),
			array( 'ae', 'AE', 'oe', 'OE',
					'ij', 'IJ',
					'ss',
				),
			$string
			);

		return str_replace (
			array(
				'a', 'á', 'à', 'â', 'ä', 'ã', 'å', 'ǎ', 'ą', 'A', 'Á', 'À', 'Â', 'Ä', 'Ã', 'Å', 'Ǎ', 'Ą',
				'e', 'é', 'è', 'ê', 'ë', 'ě', 'ę', 'E', 'É', 'È', 'Ê', 'Ë', 'Ě', 'Ę',
				'i', 'í', 'ì', 'î', 'ï', 'į', 'I', 'Í', 'Ì', 'Î', 'Ï', 'Į',
				'o', 'ó', 'ò', 'ô', 'ö', 'õ', 'ø', 'ő', 'O', 'Ó', 'Ò', 'Ô', 'Ö', 'Õ', 'Ø', 'Ő',
				'u', 'ú', 'ù', 'û', 'ü', 'ų', 'ů', 'ű', 'U', 'Ú', 'Ù', 'Û', 'Ü', 'Ų', 'Ů', 'Ű',
				'y', 'ÿ', 'Y', 'Ÿ',
				'b', 'b̌', 'B', 'B̌',
				'c', 'ç', 'ć', 'č', 'C', 'Ç',
				'd', 'đ', 'D', 'Đ',
				'l', 'ł', 'L', 'Ł',
				'm', 'm̌', 'M', 'M̌',
				'n', 'ñ', 'ň', 'N', 'Ñ', 'Ň',
				'r', 'ř', 'R', 'Ř',
				's', 'š', 'ş', 'S', 'Š', 'Ş',
				't', 'ť', 'ţ', 'T', 'Ť', 'Ţ',
				'z', 'ž', 'Z', 'Ž',

			),
			array(
				'a0', 'a1', 'a2', 'a3', 'a4', 'a5', 'a5', 'a5', 'a5', 'A0', 'A1', 'A2', 'A3', 'A4', 'A5', 'A5', 'A5', 'A5',
				'e0', 'e1', 'e2', 'e3', 'e4','e5','e5', 'E0', 'E1', 'E2', 'E3', 'E4', 'E5', 'E5',
				'i0', 'i1', 'i2', 'i3', 'i4', 'i5', 'I0', 'I1', 'I2', 'I3', 'I4', 'I5',
				'o0', 'o1', 'o2', 'o3', 'o4', 'o5', 'o5', 'o5', 'O0', 'O1', 'O2', 'O3', 'O4', 'O5', 'O5', 'O5',
				'u0', 'u1', 'u2', 'u3', 'u4', 'u5', 'u5', 'u5', 'U0', 'U1', 'U2', 'U3', 'U4', 'U5', 'U5', 'U5',
				'y0', 'y4', 'Y0', 'Y4',
				'b0', 'b1', 'B0', 'B1',
				'c0', 'c1', 'c5', 'C0', 'C1', 'C5',
				'd0', 'd1', 'D0', 'D1',
				'l0', 'l1', 'L0', 'L1',
				'm0', 'm1', 'M0', 'M1',
				'n0', 'n5', 'n5', 'N0', 'N5', 'N5',
				'r0', 'r5', 'R0', 'R5',
				's0', 's5', 's5', 'S0', 'S5', 'S5',
				't0', 't5', 't5', 'T0', 'T5', 'T5',
				'z0', 'z5', 'Z0', 'Z5',
			),
			$no_ligatures
		);
	}

}


?>
