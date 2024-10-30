<?php
namespace MonLabo\Lib;
use MonLabo\Lib\{Db,Options};

// MySQL host name, user name, password, database, and table
defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

/*
class First_Config {
	 __construct()
     is_first_time_plugin_configured( )
	 process_first_config()
}
*/
/**
 * Class \MonLabo\Admin\First_Config
 * @phan-constructor-used-for-side-effects
 * @package
 */
class First_Config {

    /**
	 * Constructor
	*/
	public function __construct( ) {
		//Fill First config flag if necessary
		$options = Options::getInstance();
		if ( ! $options->activated_version ) {
			$options8= array();
			$options8['MonLabo_first_configuration'] = 1;
			update_option( 'MonLabo_settings_group8', $options8 );
		}
	}

    /**
	 * Return true if plugin is first time configure (new installation)
	 * @return bool
	*/
    public function is_first_time_plugin_configured( ) {
		$MonLaboDB = new Db();
		//Launch eventual migration
		if ( ! $MonLaboDB->check_tables_exist() ) {
			return true;
		}
		$options8 = get_option( 'MonLabo_settings_group8' );
		return  ( isset( $options8['MonLabo_first_configuration'] ) && ( 1 === $options8['MonLabo_first_configuration'] ) );
    }

	/**
	 * To launch when first config is detected
	 * @return void
	*/
	public function process_first_config( ) {
		$options = Options::getInstance();
		$MonLaboDB = new Db();

		//Create minimal structure
		$MonLaboDB->create_tables();
		$MonLaboDB->fill_options_with_default_values_if_empty();
		$options->use_update( 'members_and_groups', true );
		$MonLaboDB->fill_tables_with_default_values_if_empty();

		//Update plugin version to last one
		$options->set( 'activated_version', get_MonLabo_version() );

		//Unset first config flag
		$options8= array();
		$options8['MonLabo_first_configuration'] = 0;
		update_option( 'MonLabo_settings_group8', $options8 );
	}

}