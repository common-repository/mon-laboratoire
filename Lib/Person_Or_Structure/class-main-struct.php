<?php
namespace MonLabo\Lib\Person_Or_Structure;

use MonLabo\Lib\{App};

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

///////////////////////////////////////////////////////////////////////////////////////////
// Librairie de fonctions pour manipuler une structure principale.
///////////////////////////////////////////////////////////////////////////////////////////
/**
 * Class \MonLabo\Main_Struct
 *
 * @package
 */

/*
*/
class Main_Struct extends Unit{
	##Cannot use Singleton because __construct() is modified
	##use \MonLabo\Lib\Singleton;

	/**
	* Singleton value
	* @var Main_Struct|null
	* @access private
	*/
	private static $_instance = null;

	/**
	 * To return new or existing Singleton instance of the class from which it is called.
	 * As it sets to final it can't be overridden.
	 *
	 * @return self Singleton instance of the class.
	 */
	public static function getInstance(): self {
		if ( null === self::$_instance ) {
			self::$_instance = new Main_Struct();
		}
		return self::$_instance;
	}

	/**
	* Méthode qui suprime l'instance de la classe. Usefull for unit test.
	* @return void
	*/
	public static function unsetInstance() { // @phan-suppress-current-line PhanUnreferencedPublicMethod
		self::$_instance = null;
	}

	/**
	* Type of item to manipulate
	* @var string
	*/
	const TYPE_OF_INFORMATION = 'main_struct';

	/**
	* Default field if empty
	* @var array<string, int|string|array<int|string>|null>
	*/
	const DEFAULT_INFORMATION = array(
		'id' => App::MAIN_STRUCT_NO_UNIT,
		'nom' => '',
		'code' => '',
		'prefixe_tel' => '',
		'hal_publi_struct_id' => null,
		'contact' => '',
		'adresse' => '',
	);

	/**
	* Constructor
	* @param string $type : type of construction
	* @param mixed $arg : argument for the constructor
	*/
	public function __construct( string $type = '', $arg = null ) {
		if ( ! empty( $type ) or ! empty( $arg ) ) {
			 $function_name = debug_backtrace()[1]['function'];
			 trigger_error( "Bad parameters for " . __CLASS__ . "::$function_name.", E_USER_WARNING );
		 }
		$this->reload();
	}

	/**
	* Get back in the instance the values of main struct
	* @return void
	*/
	public function reload( ) {
		//By default uses DEFAULT_INFORMATION of the child instance
		$this->info = (object) self::DEFAULT_INFORMATION;

		$options1 = get_option( 'MonLabo_settings_group1' );
		foreach ( array_keys( (array) $this->info ) as $key ) {
			if ( 'id' !== $key ) {
				if ( isset( $options1['MonLabo_' . $key] ) ) {
					$this->info->{$key} = $options1['MonLabo_' . $key];
				}
			}
		}
	}

   /**
	* Dummy function for insert that is useless for mail struct
	* @param array<string, mixed> $table_data fields to change.
	* @return void
	*/
	public function insert( array $table_data = array() ) { // @phan-suppress-current-line PhanUnusedPublicMethodParameter
	}

   /**
	* Dummy function for delete that is useless for mail struct
	* @return void
	*/
	public function delete( ) {
	}

	/**
	 * Update Main Struct in the database
	 * @param array<string, mixed> $table_data fields to change. If empty, send all $this->info.
	 * @return void
	 */
	public function update( array $table_data = array() ) {
		if ( empty( $table_data ) ) {
			//Put in database eventual updates in memory of $this->info
			$options1 = get_option( 'MonLabo_settings_group1' );
			foreach ( $this->info as $key => $value ) {
				if ( 'id' !== $key ) {
					$options1['MonLabo_' . $key] = $value;
				}
			}
			update_option( 'MonLabo_settings_group1', $options1 );
			return;
		}

		$directors = $this->_extract_list_array_from_data( 'directors', $table_data );

		//Keep only valid keys
		$this->_keep_only_valid_keys( $table_data );
		unset( $table_data['id'] );

		$options1 = get_option( 'MonLabo_settings_group1' );
		foreach ( $table_data as $key => $value ) {
			$options1['MonLabo_' . $key] = $value;
		}
		update_option( 'MonLabo_settings_group1', $options1 );

		//Configure directors for this unit
		$this->set_directors( $directors );
		$this->reload();
	}

}
