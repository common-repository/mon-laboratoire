<?php
namespace MonLabo\Admin;
use MonLabo\Lib\{App, Db, Options};
use MonLabo\Lib\Person_Or_Structure\{Person, Team, Unit };
use MonLabo\Frontend\{Contact_Webservices};

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

require_once ( __DIR__ . '/../autoload.php' );
require_once( __DIR__ . '/MonLabo-doc.php' );
require_once( __DIR__ . '/includes/inc-lib-tables.php' );
require_once( __DIR__ . '/includes/inc-lib-modals.php' );


///////////////////////////////////////////////////////////////////////////////////////////
//					   PLUGIN ADMIN CLASS DEFINITIONS
///////////////////////////////////////////////////////////////////////////////////////////
/*
class Admin {

	__construct()
	add_csv_mime_to_wordpress( array $mimes )
	enqueue_admin_scripts( string $hook_suffix )
	add_menu()
	admin_init()
	_fillOptionsWithDefaultValues()
	update_page_infobox( $disable_wp_die = '' )

	// CONFIGURATION MENU
	custom_toolbar_user_link( \WP_Admin_Bar $wp_admin_bar )
}
*/
/**
 * Class \MonLabo\Admin\Admin
 * @package
 */
class Admin {

	/**
	* Current instance of Messages
	* @access private
	* @var Messages
	*/
	private $_messages = null;

	/**
	 * Current instance of Settings_Fields
	* @access private
	* @var Settings_Fields
	 */
	private $_adminSettingsField = null;

	/**
	 * Current instance of Admin_Ui (must be public for unit test)
	* @var Admin_Ui
	 */
	public $_adminUI;

	/**
	 * Create a new admin class
	 */
	function  __construct() {

		add_action('admin_init', array( &$this, 'check_specific_admin_lang_parameter' ) ) ;
		$this->_messages = new Messages();
		add_action( 'admin_menu', array( &$this, 'add_menu' ) );
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_admin_scripts' ) );
		add_action( 'wp_ajax_update_page_infobox', array( &$this, 'update_page_infobox' ) );

		$this->_adminSettingsField = new Settings_Fields( );
		//Authorize CSV to be uploaded
		add_filter( 'upload_mimes', array( $this, 'add_csv_mime_to_wordpress' ) );		
	}

	/**
	* Add parameter &lang=all to page admin.php?page=MonLabo_edit_members_and_groups
	* Configured by self::__construct() to be called in action 'admin_init'
	* @return void
	*/
	function check_specific_admin_lang_parameter() { // @phan-suppress-current-line PhanUnreferencedPublicMethod
		// check we are on page admin.php?page=MonLabo_edit_members_and_groups
		if (isset($_GET['page']) && $_GET['page'] === 'MonLabo_edit_members_and_groups') {
			// Check if parameter &lang=all is not set
			if (!isset($_GET['lang']) || $_GET['lang'] !== 'all') {
				wp_redirect( $_SERVER['REQUEST_URI'] . '&lang=all' );
				exit();
			}
		}
	}

	/**
	* Utility fonction of filter 'upload_mimes' to permit to add CSV to athorized mime type of wordpress
	* @param string[] $mimes table of Mime types
	* @return string[]
	*/
	function add_csv_mime_to_wordpress( array $mimes ) : array {
		$mimes['csv'] = 'text/csv';
		return $mimes;
	}

	/**
	* Enqueue JS and CSS scripts in the admin page
	* Configured by self::__construct() to be called in action 'admin_enqueue_scripts'
	* @param string $hook_suffix The current admin page
	* @return void
	*/
	function enqueue_admin_scripts( string $hook_suffix ) {  // @phan-suppress-current-line PhanUnreferencedPublicMethod
		// first check that $hook_suffix is appropriate for your admin page
		if ( ( 'toplevel_page_MonLabo' === $hook_suffix )
		or ( 'mon-laboratoire_page_MonLabo_config' === $hook_suffix  )
		or ( 'mon-laboratoire_page_MonLabo_config_cache' === $hook_suffix  )
		or ( 'mon-laboratoire_page_MonLabo_edit_members_and_groups' === $hook_suffix  )
		) {
			wp_enqueue_style( 'wp-color-picker' );
			$MonLabo_menu_info = get_plugin_data( plugin_dir_path( __FILE__ ) .'../mon-laboratoire.php' );
			wp_enqueue_script(
				'MonLabo_admin-script',
				plugins_url( '/js/MonLabo-admin.js', __FILE__ ),
				array( 'wp-color-picker', 'jquery' ),
				$MonLabo_menu_info['Version'],
				true
			);
			wp_enqueue_script( 
				'select2-script',
				plugins_url( '/js/select2.min.js', __FILE__ ),
				array( 'jquery' ),
				'4.0.13',
				true
			);
			
			//Ne pas laisser d'espace vide quand un champs de formulaire est caché
			// => mettre les marges internes par défaut des conteneurs en question à 0.
			wp_add_inline_script(
				'MonLabo_admin-script',
					"jQuery( 'input[type=hidden]' ).parents( 'td' ).css( 'padding', '0' );"
				   ."jQuery( 'input[type=hidden]' ).parents( 'td' ).prev( 'th' ).css( 'padding', '0' );"
			);

			// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
			wp_localize_script(
				'MonLabo_admin-script',
				'ajax_object_update_page_infobox',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ) ,
					'nonce' => wp_create_nonce( "nonce_update_page_infobox" )
				)
			);

			wp_enqueue_media(); //On insère toutes les dépendances nécessaires pour l'affichage du menu média  //Obligatoire sous cette forme pour que le menu media fonctionne en AJAX
		}
	}

	/**
	* Add mon-laboratoire admin menus
	* Configured by self::__construct() to be called in action 'admin_menu'
	* @return void
	*/
	public function add_menu() { // @phan-suppress-current-line PhanUnreferencedPublicMethod
		$options = Options::getInstance();

		//Add menu accessible by simple users
		add_menu_page(
			__( 'Configuration of members and teams in the lab', 'mon-laboratoire' ),
			'Mon Laboratoire',
			'edit_pages',
			'MonLabo',
			array( &$this->_adminUI, 'doc_render' ),
			plugins_url( 'images/logo16.gif', __FILE__ ),
			999
		);

		//Add submenus accessible by administrators only
		if ( current_user_can( 'manage_options' ) ) {
			add_submenu_page(
				'MonLabo',
				__( 'Configuration of members and teams in the lab', 'mon-laboratoire' ),
				'Documentation',
				'manage_options',
				'MonLabo'
			);
			add_submenu_page(
				'MonLabo',
				__( 'General configurations', 'mon-laboratoire' ),
				'Configuration',
				'manage_options',
				'MonLabo_config',
				array( &$this->_adminUI, 'config_render' )
			);
			if ( 'aucun' !== $options->publication_server_type ) {
				$webservice = new Contact_Webservices();
				$nbcache = $webservice->number_of_transient_entries();
				$cacheCountText = '';
				if ( $nbcache > 0 ) {
					$cacheCountText = '&nbsp;<span class="update-plugins"><span class="MonLabo-count">' . $nbcache . '</span></span>';
				}
				add_submenu_page(
					'MonLabo',
					'',
					__( 'Publications cache', 'mon-laboratoire' ) . $cacheCountText,
					'manage_options',
					'MonLabo_config_cache',
					array( &$this->_adminUI, 'config_cache_render' )
				);
			}
			if ( $options->uses['members_and_groups'] ) {
				add_submenu_page(
					'MonLabo',
					__( 'Edit persons and structures', 'mon-laboratoire' ),
					__( 'Persons and structures', 'mon-laboratoire' ),
					'manage_options',
					'MonLabo_edit_members_and_groups',
					array( &$this->_adminUI, 'edit_persons_and_structures_render' )
				);
			}
		}
	}

	/**
	* hook into WP's admin_init action hook
	* Configured by self::__construct() to be called in action 'admin_init'
	* @SuppressWarnings(PHPMD.ElseExpression)
	* @return void
	*/
	public function admin_init() {  // @phan-suppress-current-line PhanUnreferencedPublicMethod
		// Set up the settings for this plugin
		//Uses specific CSS for this admin
		wp_enqueue_style( 'MonLaboAdmin', plugin_dir_url( __FILE__ ) . 'css/MonLabo-admin.css', array(), get_MonLabo_version() );
		wp_enqueue_style( 'Select2', plugin_dir_url( __FILE__ ) . 'css/select2.min.css', array(), '4.0.13' );

		// register the settings for this plugin
		foreach ( array_keys( App::get_options_DEFAULT() ) as $group ) {
			register_setting( 'pluginSettings_' . $group , $group );
		}
		$this->_adminUI = Admin_Ui::getInstance();
		$this->_adminUI->setSettingsField( $this->_adminSettingsField );

		$this->_fillOptionsWithDefaultValues();
	}

	/**
	* Set default values for options not present in the database and alert if there is a problem
	* @return void
	*/
	private function _fillOptionsWithDefaultValues() {
		$MonLaboDB = new Db();
		$MonLaboDB->fill_options_with_default_values_if_empty();

		//Alerte si la base MonLabo est obsolète
		//--------------------------------------
		if ( $MonLaboDB->detect_obsolete_table_name() ) {
			add_action( 'admin_notices', array( &$this->_messages, 'echo_error_bad_migration' ) );
		}
	}


	/**
	 * Ajax function to display the thumbnail image and infobox of member edition page
	 * Configured by self::__construct() to be called in action 'wp_ajax_update_page_infobox'
	 * @param string $disable_wp_die Default ''. 'inactivated_wp_die' only for unit test ( inactivate wp_die() )
 	 * @return void
	 */
	public static function update_page_infobox( string $disable_wp_die = '' ){ // @phan-suppress-current-line PhanUnreferencedPublicMethod
		if ( check_ajax_referer( 'nonce_update_page_infobox' ) ) {
			$formsProcessing = new Forms_Processing();
			$item_id =  $formsProcessing->getPOSTnumber( 'item_id' );
			$wp_post_id = '';
			if ( isset( $_POST['wp_post_id'] ) ) {
				$wp_post_id 	= $formsProcessing->getPOSTsimpleString( 'wp_post_id' );
			}
			$div_id 		= $formsProcessing->getPOSTsimpleString( 'div_id' );
			$page_number	= $formsProcessing->getPOSTnumber( 'page_number' );
			$type 			= $formsProcessing->getPOSTsimpleString( 'type' );
			$htmlForms = new Html_Forms();
			$text = $htmlForms->update_page_infobox( $item_id, $page_number, $wp_post_id, $type );
			$response = array( 'type' => 'success' );
			$response['text'] = $text;
			$response['div_id'] = $div_id;
			echo( json_encode( $response ) );
		}
		if ( 'inactivated_wp_die' !== $disable_wp_die ) {  //Permit to inactivate wp_die() for unit test
			wp_die(); 	// @codeCoverageIgnore
		}
	}

}

///////////////////////////////////////////////////////////////////////////////////////////
//								  CONFIGURATION MENU
///////////////////////////////////////////////////////////////////////////////////////////
/**
 * add a link to the WP Toolbar
 * called by add_action( 'admin_bar_menu', 'custom_toolbar_user_link', 999 );
 * @param \WP_Admin_Bar $wp_admin_bar Admin bar to add button
 * @SuppressWarnings(PHPMD.ElseExpression)
 * @return void
 */
function custom_toolbar_user_link( \WP_Admin_Bar $wp_admin_bar ) { //@phan-suppress-current-line PhanUndeclaredTypeParameter,PhanUnreferencedFunction,PhanRedefinedClassReference
	$wp_post_id=get_the_ID();
	if ( ! empty( $wp_post_id ) ) {
		$iconurl = plugin_dir_url( __FILE__ ) . 'images/logo16.gif';
		$iconspan = '<span class="custom-icon" style="color: #72777c; float:left; width:16px !important; height:16px !important; margin: 7px 5px 0px 5px !important; background-image:url(\'' . $iconurl . '\' );"></span>';

		$item = new Person( 'from_wpPostId', $wp_post_id );
		if ( ! $item->is_empty() ) {
			$title = __( 'Edit this person', 'mon-laboratoire' );
			$tab = 'tab_person';
		} else {
			$item = new Team( 'from_wpPostId', $wp_post_id );
			if ( ! $item->is_empty() ) {
				$title = __( 'Edit this team', 'mon-laboratoire' );
				$tab = 'tab_team';
			} else {
				$item = new Unit( 'from_wpPostId', $wp_post_id );
				$title = __( 'Edit this unit', 'mon-laboratoire' );
				$tab = 'tab_unit';
			}
		}
		if ( ! $item->is_empty() ) {
			$args = array(
				'id' => 'MonLabo_edit_link',
				'title' => $iconspan . $title,
				'href' => get_admin_url() . 'admin.php?page=MonLabo_edit_members_and_groups&tab=' . $tab . '&submit_item=' . $item->info->id . '&lang=all',
			);
			$wp_admin_bar->add_node( $args ); // @phan-suppress-current-line PhanUndeclaredClassMethod,PhanRedefinedClassReference
		}
	}
}

///////////////////////////////////////////////////////////////////////////////////////////
//					   MAIN CODE
///////////////////////////////////////////////////////////////////////////////////////////
if ( class_exists( 'MonLabo' ) ) {
	if ( class_exists( '\MonLabo\Admin\Admin' ) ) {
		// instantiate the plugin admin class
		$laboAdminPlugin = new \MonLabo\Admin\Admin();
	}
}

?>
