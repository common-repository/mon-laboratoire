<?php
namespace MonLabo\Lib;

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

use MonLabo\Lib\{App};

///////////////////////////////////////////////////////////////////////////////////////////
// Librairie de fonctions pour manipuler les traductions de texte et leur personalisation
///////////////////////////////////////////////////////////////////////////////////////////


/*
	__construct( string $language = '' )
	get_lang_short()
	get_lang()
	_get_customizable_text( $custom_text_tag )
	switch_lang( $english_value, $french_value )
	tr_n( string $single, string $plural, int $number )
	tr__( $txt_to_translate )
	_get_configured_display_language( )
	get_browser_language( )
*/
/**
 * Class \MonLabo\Lib\Translate
 *
 * @package
 */
class Translate {

	const TRANSLATION_TABLE = array(
		'Nom' 					=> array( 'en-US'=> 'Name', 		'fr-FR'=> 'Nom' ),
		'Equipe' 				=> array( 'en-US'=> 'Group',		'fr-FR'=> 'Équipe' ),
		'Poste' 				=> array( 'en-US'=> 'Position',		'fr-FR'=> 'Poste' ),
		'Email' 				=> array( 'en-US'=> 'Email',		'fr-FR'=> 'Courriel' ),
		'Alumni since' 			=> array( 'en-US'=> 'Alumni since',	'fr-FR'=> 'Ancien membre depuis' ),
		'Alumni'				=> array( 'en-US'=> 'Alumni',		'fr-FR'=> 'Ancien membre' ),
		'Former'				=> array( 'en-US'=> 'Former',		'fr-FR'=> 'Ancienne fonction&nbsp;:' ),
		'Until'					=> array( 'en-US'=> 'Until',		'fr-FR'=> 'Jusqu’en' ),
		'Statut'	   			=> array( 'en-US'=> 'Status', 		'fr-FR'=> 'Statut' ),
		'Direction:'			=> array( 'en-US'=> 'Direction:', 	'fr-FR'=> 'Direction&nbsp;:' ),
		'Lab members'			=> array( 'en-US'=> 'Lab members', 	'fr-FR'=> 'Membres du laboratoire' ),
		'Teams'					=> array( 'en-US'=> 'Teams', 		'fr-FR'=> 'Équipes' ),
		'Thematics'				=> array( 'en-US'=> 'Thematics', 	'fr-FR'=> 'Thématiques' ),
		'Units'					=> array( 'en-US'=> 'Units', 		'fr-FR'=> 'Unités' ),
	);

	/**
	* Current language
	* @var string
	*/
	private $_lang;

	/**
	 * Constructor
	* @param string $language default language
	 */
	public function __construct( string $language = '' ) {
		if ( empty( $language ) ) {
			$language = $this->_get_configured_display_language();
		}
		switch ( mb_strtolower( $language, 'UTF-8' ) ) {
			case 'en-gb':
			case 'en_gb':
			case 'en-en':
			case 'en_en':
			case 'en':
			case 'en-us':
			case 'en_us':
			case 'anglais':
			case 'english':
				$this->_lang = 'en-US';
				break;
			default:
				$this->_lang = 'fr-FR';
				break;
		}
	}

	/**
	 * get "fr" or "en"
	 * @return string short language value
	 */
	public function get_lang_short(): string {
		if ( 'en-US' === $this->_lang ) {
			return 'en';
		}
		return 'fr';
	}

	/**
	 * getter of private value _lang
	 * @return string value this->_lang
	 */
	public function get_lang(): string {
		return $this->_lang;
	}

	/**
	 * Get the text value of a customizable text
	 * @param string $custom_text_tag tag of custom text ( see all tags in part
	 * 		MonLabo_settings_group5 in constants.php. Remove '_fr' or '_en' )
	 * @return string Translation of custom text
	 * @access private
	 */
	private function _get_customizable_text( string $custom_text_tag ): string {
		$options_DEFAULT = App::get_options_DEFAULT();

		//Step 1 : identify $language
		//---------------------------
		$suffix = $this->get_lang_short();

		//Step 2 : return custom text if defined
		$options5 = get_option( 'MonLabo_settings_group5' );
		if ( array_key_exists( $custom_text_tag . '_' . $suffix, $options5 ) ) {
			return strval( $options5[ $custom_text_tag . '_' . $suffix ] );
		}

		//Step 3 : return default custom text if defined
		if ( array_key_exists( $custom_text_tag . '_' . $suffix, $options_DEFAULT['MonLabo_settings_group5'] ) ) {
			return strval( $options_DEFAULT['MonLabo_settings_group5'][ $custom_text_tag . '_' . $suffix ] ); // @codeCoverageIgnore
		}
		return '';
	}

	/**
	 * Return english or french value
	 * @param mixed $english_value value to return if english
	 * @param mixed $french_value value to return if french or default
	 * @return mixed Translated value
	 */
	public function switch_lang( $english_value, $french_value ) {
		if ( 'en-US' === $this->_lang ) {
			return $english_value;
		}
		return $french_value;
	}

	/**
	 * Translates and retrieves the singular or plural form based on the supplied number.
	 * @param string $single The text to be used if the number is singular.
	 * @param string $plural The text to be used if the number is plural.
	 * @param int $number The number to compare against to use either the singular or plural form.
	 * @return string Translated text
	 * @SuppressWarnings(PHPMD.ShortMethodName)
	 */
	public function tr_n( string $single, string $plural, int $number ): string {
		if ( 1 === $number ) {
			return $this->tr__( $single );
		}
		return $this->tr__( $plural );
	}

	/**
	 * Translate a string
	 * @param string $txt_to_translate String to translate
	 * @return string Translated text
	 * @SuppressWarnings(PHPMD.ShortMethodName)
	 */
	public function tr__( string $txt_to_translate ): string {
		/*if ( 'Direction&nbsp;:' === $txt_to_translate ) {
			echo '--------------------------------------------------------------------';
		}*/
		//Test if first char is lowercase
		$is_first_char_upper = ctype_upper( substr( $txt_to_translate, 0, 1 ) );

		if ( array_key_exists( ucfirst( $txt_to_translate ), self::TRANSLATION_TABLE ) ) {
			//Case 1 : translation is in $translation_table
			//---------------------------------------------
			if ( $is_first_char_upper ) {
				return ucfirst( self::TRANSLATION_TABLE[ucfirst( $txt_to_translate )][ $this->_lang ] );
			}
			return lcfirst( self::TRANSLATION_TABLE[ucfirst( $txt_to_translate )][ $this->_lang ] );
		}

		//Case 2 : translation is a custom text
		//-------------------------------------
		$custom_text = $this->_get_customizable_text( 'MonLabo_custom_text_' . str_replace( ' ', '_', ucfirst( $txt_to_translate ) ) );
		if ( ! empty( $custom_text ) ) {
			if ( $is_first_char_upper ) {
				return ucfirst( $custom_text );
			}
			return lcfirst( $custom_text );
		}
		return $txt_to_translate;
	}

	/**
	 * Get the language to use, as it is configured in parameter 'MonLabo_language_config'
	 * @return string Language code/prefix
	 * @access private
	 */
	private function _get_configured_display_language( ): string {

		$options6 = get_option( 'MonLabo_settings_group6' );
		if ( isset( $options6['MonLabo_language_config'] ) ) {
			switch ( $options6['MonLabo_language_config'] ) {
				case 'en':
				case 'fr':
					return $options6['MonLabo_language_config'];
				case 'WordPress': //Get the WordPress configured language
					return get_locale();
				case 'browser': //Get the language of user's browser
					return $this->get_browser_language();
				case 'Polylang':  //Get the language of page configured by Polylang plugin
					$Polylang_Interface = new Polylang_Interface();
					return $Polylang_Interface->get_polylang_page_language();
			}
		}
		return  get_locale();
	}


	/**
	 * Get browser language, given an array of avalaible languages.
	 * @return string Language code/prefix
	 */
	public function get_browser_language( ): string {
		$available = array( "en", "fr" );
		if ( isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
			$langs = explode( ',', $_SERVER['HTTP_ACCEPT_LANGUAGE'] );
			foreach ( $langs as $lang ){
				$lang = substr( $lang, 0, 2 );
				if ( in_array( $lang, $available ) ) {
					return $lang;
				}
			}
		}
		return 'en';
	}

}
