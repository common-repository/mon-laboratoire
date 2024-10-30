<?php
namespace MonLabo\Lib;

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

/**
 * Class \MonLabo\Lib\Options
 * 
 * The Goal of this class is to be able to use two types of parameters in code :
 *  - $options->uses['<feature_name>'] : is a boolean that tells if a feature is activated or not
 *  - $options-><option_name> : an option value extracted from get_option()
 *
 * @package
 */

class Options  extends \stdClass { //"stdClass" for dynamic properties
	use Singleton;

	/*
         _get_uses_from_option( string $uses_name, string $option_name 
        __construct()
        set( string $option_name, string $value )
        _set_group0( string $option_name, string $value )
        use_update( string $functionality, bool $value )
	*/

    /**
	* Functionalities to use
	* @var array<string,bool>
	*/
    public $uses = array(
        'members_and_groups'    => false,
        'thematics'             => false,
        'custom_fields'         => false,
        'units'                 => false,
    );

    /**
	* Retrieve a uses propeerty field from wordpress option name
   	* @param string $uses_name : uses field name
   	* @param string $option_name : option name
	* @return void
	* @access private
	*/
    private function _get_uses_from_option( string $uses_name, string $option_name ) {
        $options0 = get_option( 'MonLabo_settings_group0' );
        if ( isset( $options0[ $option_name ] ) && ( 1 === intval( $options0[ $option_name ] ) ) ) {
            $this->uses[ $uses_name ] = true;
        }
    }

    /**
     * construction calls with the `new` operator.
     */
    private function __construct() {
        $options0 = get_option( 'MonLabo_settings_group0' );
        if ( empty( $options0 ) ) { $options0 = array(); }
        //Extract all functionalities
        $this->_get_uses_from_option( 'thematics',          'MonLabo_uses_thematiques' );
        $this->_get_uses_from_option( 'custom_fields',      'MonLabo_uses_custom_fields_for_staff' );
        $this->_get_uses_from_option( 'units',              'MonLabo_uses_unites' );
        $this->_get_uses_from_option( 'members_and_groups', 'MonLabo_uses_members_and_groups' );
        //Do not activate thematics if members and group are not activated
        if ( ! $this->uses['members_and_groups'] ) {
            $this->uses['thematics'] = false;
        }
        //Extract all other options of $options0
        foreach ( array_keys( App::OPTIONS0_DEFAULT ) as $options_index ) {
            if( ! str_starts_with( $options_index, 'MonLabo_uses_' ) ) {
                //Suppress leading 'MonLabo_' from option name
                $option_name = substr( $options_index, 8 );
                //Init dynamic property if not yet defined
                if( !isset( $this->{$option_name} ) ) {
                    $this->{$option_name} = App::OPTIONS0_DEFAULT[ $options_index ];
                }
                //Update option if necessary
                if ( isset( $options0[ $options_index ] )) {
                    if( '___no_change___' !== $options0[ $options_index ] ) {
                        $this->{$option_name} = strval( $options0[ $options_index ] );
                    }
                } else {
                        $this->set( $option_name , $this->{$option_name});
                }
            }
        }

        //db_prefix
        //---------
        $options9 = get_option( 'MonLabo_settings_group9' );
        if ( empty( $options9 ) ) { $options9 = array(); }
        //Init dynamic property if not yet defined
        if( 
            ( !isset( $this->db_prefix_in_use ) ) 
            || ( '-1' === $this->db_prefix_in_use )
            || ( !isset( $options9[ 'MonLabo_db_prefix_in_use' ]  ) )
        ) {
            $this->set( 'db_prefix_in_use', '___not_set___' );
        }
        //Update option if necessary
        if ( isset( $options9[ 'MonLabo_db_prefix_in_use' ] ) ) {
            $this->db_prefix_in_use = strval( $options9[ 'MonLabo_db_prefix_in_use'] );
        }
        if ( '___no_change___' !== $this->multisite_db_to_use ) {
            if( '___manual_edit___' === $this->multisite_db_to_use ) {
                $this->set( 'db_prefix_in_use', $this->db_prefix_manual_edit );
            } else {
                $this->set( 'db_prefix_in_use', $this->multisite_db_to_use );
            }
        }
    }

    /**
	* Update option value
   	* @param string $option_name : name of the option to update
   	* @param string $value : value to replace
	* @return void
	*/
	public function set( string $option_name, string $value ) {
        if ( 'db_prefix_in_use' === $option_name ) {
            $options9 = get_option( 'MonLabo_settings_group9' );
            if ( empty( $options9 ) || ( ! is_array( $options9 ) ) ) { 
                $options9 = array();
            }
            $options9[ 'MonLabo_db_prefix_in_use' ] = $value;
            $this->db_prefix_in_use = $value;            
            update_option( 'MonLabo_settings_group9', $options9 );
            $this->_set_group0( 'db_prefix_manual_edit', $value );
        } else {
            $this->_set_group0( $option_name, $value );
        }
	}

    /**
	* Update option value generic mode
   	* @param string $option_name : name of the option to update
   	* @param string $value : value to replace
	* @return void
	* @access private
	*/
	private function _set_group0( string $option_name, string $value ) {
        if ( 'db_prefix_in_use' !== $option_name ) {
            $options0 = get_option( 'MonLabo_settings_group0' );
            if ( empty( $options0 ) || ( ! is_array( $options0 ) ) ) { 
                $options0 = array();
            } 
            if ( isset( App::OPTIONS0_DEFAULT[ 'MonLabo_' . $option_name ] ) ) {
                $options0[ 'MonLabo_' . $option_name ] = $value;
                $this->{$option_name} = $value;            
            }
            update_option( 'MonLabo_settings_group0', $options0 );
        }
	}

    /**
	* Update a functionality (used only in unit test)
	* @phan-suppress PhanUnreferencedPublicMethod
	* @param string $functionality : name of the use fonctionnality to toggle
    * @param bool $value : new activation value
	* @return void
	*/
    public function use_update( string $functionality, bool $value ) {
        if ( isset( $this->uses[ $functionality ] ) ) {
            $options0 = get_option( 'MonLabo_settings_group0' );
            $option_index = 'MonLabo_uses_' .  $functionality;
            if( 'thematics'     == $functionality ) { $option_index = 'MonLabo_uses_thematiques'; }
            if( 'custom_fields' == $functionality ) { $option_index = 'MonLabo_uses_custom_fields_for_staff'; }
            if( 'units'         == $functionality ) { $option_index = 'MonLabo_uses_unites'; }
            if ( isset( $this->uses[ $functionality ] ) ) {
                $options0[ $option_index ] = $value;
            }
            update_option( 'MonLabo_settings_group0', $options0 );
            $this->uses[ $functionality ] = $value;
        }
	}
}
?>
