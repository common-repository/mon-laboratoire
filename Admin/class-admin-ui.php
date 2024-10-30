<?php
namespace MonLabo\Admin;
use MonLabo\Lib\{App, Translate, Options};
use MonLabo\Frontend\{Html};

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

require_once ( __DIR__ . '/../autoload.php' );
require_once( __DIR__ . '/MonLabo-doc.php' );
require_once( __DIR__ . '/includes/inc-lib-tables.php' );
require_once( __DIR__ . '/includes/inc-lib-modals.php' );


///////////////////////////////////////////////////////////////////////////////////////////
//					   PLUGIN ADMIN CLASS DEFINITIONS
///////////////////////////////////////////////////////////////////////////////////////////
/*
class Admin_Ui {
	__construct()
	_handle_functionality_tab()
	_handle_publications_tab()
	_handle_language_tab()
	_handle_pages_tab()
	_handle_appearance_tab()
	_handle_privacy_tab()
	_handle_customTexts_tab()
	_handle_customFields_tab()
	_handle_publicationsCache_menu()
	add_section( string $section, string $title )
	add_simple_html( string $html_code )
	pluginPage_section_generic_callback( )
	add_field( string $type, string $option_name, string $title, bool $disable = false, string $description = '' )

	edit_persons_and_structures_render()
	function get_title( string $soustitre='' )
	doc_render()
	config_render()
	config_cache_render()
	view_items_in_tables_render()
}
*/
/**
 * Class \MonLabo\Admin\Admin_Ui
 * @package
 */
class Admin_Ui {
	use \MonLabo\Lib\Singleton;

	/**
	* Current section of a  menu
	* @access private
	* @var string
	*/
	private $_curr_section;

	/**
	* Current page of a  menu
	* @access private
	* @var string
	*/
	private $_curr_page;

	/**
	* Current settings group of an option
	* @access private
	* @var string
	*/
	private $_curr_settings_group;

	/**
	 * Current instance of Settings_Fields
	* @access private
	* @var Settings_Fields
	 */
	private $_adminSettingsField = null;

	/**
	 * Current instance of Html
	* @access private
	* @var Html
	 */
	private $_html = null;

	/**
	 * Create a new admin class
	 * @access private
	 */
	private function  __construct( ) {
		$this->_html = new Html();
	}

	/**
	 * Setter of property _adminSettingsField
	 * @param Settings_Fields $settingsFields content to set
	 * @return void
	 */
	function setSettingsField( Settings_Fields $settingsFields ) {
		$this->_adminSettingsField = $settingsFields;

		//Manages all tabs
		$this->_handle_functionality_tab();
		$this->_handle_publications_tab();
		$this->_handle_language_tab();
		$this->_handle_pages_tab();
		$this->_handle_appearance_tab();
		$this->_handle_privacy_tab();
		$this->_handle_customTexts_tab();
		$this->_handle_customFields_tab();

		// Menu 'Publication cache'
		//-------------------------
		$this->_handle_publicationsCache_menu();
	}

	/**
	* Configure 'PublicationsCache' menu
	* @return void
	*/
	private function _handle_publicationsCache_menu() {
		$options = Options::getInstance();
		if ( 'aucun' !== $options->publication_server_type ) {
			$this->_curr_settings_group = 'MonLabo_settings_group7';
			$this->_curr_page = 'MonLaboPageConfigPublicationsCache';
			$this->add_section( 'MonLabo_pluginPage_section_config_publication_cache', "&nbsp;" . __( 'Configure publications cache', 'mon-laboratoire' ) );
			$this->add_field( 'button_clear_cache', 'MonLabo_do_erase_cache',
				$this->_html->dashicon( 'trash' ) .   __( 'Clear cache', 'mon-laboratoire' ), false,
				__( ' page(s) in cache.', 'mon-laboratoire' ) );
			$this->add_field( 'number',  'MonLabo_cache_duration',    __( 'Cache duration', 'mon-laboratoire' ), false, __( 'Number of hours before update', 'mon-laboratoire' ) );
		}
	}

	/**
	* Configure 'functionalities' tab
	* @return void
	*/
	private function _handle_functionality_tab() {
		$options = Options::getInstance();
		$this->_curr_settings_group='MonLabo_settings_group0';
		$this->_curr_page = 'MonLaboPageFunctionalities';
		$this->add_section( 'MonLabo_pluginPage_section_fonctionalites', __( 'Plugin functions that can be activated', 'mon-laboratoire' ));
		$this->add_field( 'radio', 'MonLabo_publication_server_type',  __( 'External publication server', 'mon-laboratoire' ), false, ' ' );
		$this->add_field( 'checkbox2', 'MonLabo_uses_members_and_groups',
			$this->_html->dashicon( 'admin-users' ) .  __( 'Persons and teams', 'mon-laboratoire' ),
			 false, __( 'Uncheck this box to use only the extraction of Descartes Publi publications.', 'mon-laboratoire' ) );
		$this->add_field(
			'checkbox2',
			'MonLabo_uses_thematiques',
			$this->_html->dashicon( 'buddicons-groups' ) . __( 'Team groups', 'mon-laboratoire' ),
			!$options->uses['members_and_groups'],
			sprintf (
				__( 'To be able to group teams (thematic, research group, axis...). You can configure the name of the grouping on <a href="%s">this page</a>', 'mon-laboratoire' ),
				"admin.php?page=MonLabo_config&tab=tab_customtexts"
				)
			 );

		$this->add_field( 'checkbox2', 'MonLabo_uses_unites',
			$this->_html->dashicon( 'admin-multisite' ) .  __( 'Several units', 'mon-laboratoire' ),
			!$options->uses['members_and_groups'], __( 'For a structure that brings together several laboratories.', 'mon-laboratoire' ) );
		$this->add_field( 'hidden',  'MonLabo_activated_version', '', true );
		$this->add_section( 'MonLabo_pluginPage_section_fonctionalites2', __( 'Advanced features', 'mon-laboratoire' ) );
		$this->add_field( 'checkbox2', 'MonLabo_uses_custom_fields_for_staff',
			$this->_html->dashicon( 'insert-before' ) .  __( 'Custom fields for persons', 'mon-laboratoire' ),
			!$options->uses['members_and_groups'], __( 'Displays the "Custom Fields" tab to be able to add custom columns to user tables (need programming to use).', 'mon-laboratoire' ) );
		
		$hideSelect = true;
		$prefixTitle = $this->_html->dashicon( 'database' ) . __( 'Prefix to use for the database', 'mon-laboratoire' );
		if ( is_multisite() ) {
			$hideSelect = false;
			$prefixTitle = '<div class="MonLabo_option_right">'. __( 'Prefix:', 'mon-laboratoire' ) . '</div>';
		}
		$this->add_field( 'select', 'MonLabo_multisite_db_to_use', 
				$this->_html->dashicon( 'database' ) . __( 'Database table prefix to use (multisite)', 'mon-laboratoire' ),
				$hideSelect, '', 'changeDbToUse(this.value)' );
		$this->add_field( 'text_field', 'MonLabo_db_prefix_manual_edit', $prefixTitle, false, 
				__( 'Do not touch if you do not understand. Permit to use the plugin tables with other prefixed name.', 'mon-laboratoire' ),
				'editDbToUse()' );
	}

	/**
	* Configure 'Publications' tab
	* @return void
	*/
	private function _handle_publications_tab() {
		$options = Options::getInstance();
		$this->_curr_settings_group = 'MonLabo_settings_group4';
		$this->_curr_page = 'MonLaboPageConfigPublications';
	
		if ( 'aucun' !== $options->publication_server_type ) {
			switch ( $options->publication_server_type ) {
				case 'hal':
					$disableHal = false;
					$disableDescartesPubli = true;
					break;
				case 'DescartesPubli':
					$disableHal = true;
					$disableDescartesPubli = false;
					break;
				case 'both':
					$disableHal = false;
					$disableDescartesPubli = false;
					break;
				default:
					$disableHal = true;
					$disableDescartesPubli = true;
					break;
			}
			if ( ! $disableHal ) {
				$img = "<a href='https://hal.science/'><img width='61' height='30' class='wp-image-8 alignleft wp-post-image' src='" . plugins_url( 'images/logoHAL.png', __FILE__ ) . "' alt='logo HAL' /></a>";
				$this->add_section( 'MonLabo_pluginPage_section_publication_HAL_servers', "&nbsp;" . __( 'Link to the HAL publication server', 'mon-laboratoire' ) . $img );	
			}
			$this->add_field( 'select',  'MonLabo_hal_publi_style', __( 'Display format of publications', 'mon-laboratoire' ), $disableHal );

			if ( ! $disableDescartesPubli ) {
				$img = "<img width='61' height='34' class='wp-image-8 alignleft wp-post-image' src='" . plugins_url( 'images/DescartesPubli.logo.png', __FILE__ ) . "' alt='logo DescartesPubli'> ";
				$this->add_section( 'MonLabo_pluginPage_section_publication_DP_servers', "&nbsp;" . __( 'Link to the DescartesPubli publication server', 'mon-laboratoire' ) . $img );
			}
			$this->add_field( 'text_field',  'MonLabo_DescartesPubmed_api_url',    __( 'Address of the Public API of Descartes Publi', 'mon-laboratoire' ), $disableDescartesPubli );
			$this->add_field( 'select',  'MonLabo_DescartesPubmed_format', __( 'Display format of publications', 'mon-laboratoire' ), $disableDescartesPubli );
		}
	}

	/**
	* Configure 'Languages' tab
	* @return void
	*/
	private function _handle_language_tab() {
		$this->_curr_settings_group = 'MonLabo_settings_group6';
		$this->_curr_page = 'MonLaboPageLangues';
		$this->add_section( 'MonLabo_pluginPage_section_Langues',  __( 'Language of the content generated by Mon Laboratoire', 'mon-laboratoire' ) );
		$this->add_field( 'radio',   'MonLabo_language_config',  __( 'Display language', 'mon-laboratoire' ), false, ' ' );
	}


	/**
	* Configure 'Pages' tab
	* @return void
	*/
	private function _handle_pages_tab() {
		$options = Options::getInstance();
		$options10 = get_option( 'MonLabo_settings_group10' );
		$this->_curr_settings_group = 'MonLabo_settings_group10';
		$this->_curr_page = 'MonLaboPagePages';
		if ( $options->uses['members_and_groups'] ) {
			$this->add_section( 'MonLabo_pluginPage_section_PagePersonnelles',  __( 'Parent pages of persons and structures', 'mon-laboratoire' ) );
			$this->add_field( 'select_page', 'MonLabo_perso_page_parent',  $this->_html->dashicon( 'admin-users' ) . __( 'Parent page for personal pages', 'mon-laboratoire' ), false );
			$this->add_field( 
				'button_create_default_parent_page', 'MonLabo_do_create_perso_page_parent', '', !is_null( get_post( $options10['MonLabo_perso_page_parent'] ) ),
				$this->_html->dashicon( 'admin-users' ) . __( 'Generate default parent page ("Lab members")', 'mon-laboratoire' ) 
			);
			$this->add_field( 'select_page', 'MonLabo_team_page_parent',  $this->_html->dashicon( 'groups' ) . __( 'Parent page for teams pages', 'mon-laboratoire' ), false );
			$this->add_field( 
				'button_create_default_parent_page', 'MonLabo_do_create_team_page_parent', '', !is_null( get_post( $options10['MonLabo_team_page_parent'] ) ),
				$this->_html->dashicon( 'groups' ) . __( 'Generate default parent page ("Teams")', 'mon-laboratoire' ) 
			);
			if ( $options->uses['thematics'] ) {
				$this->add_field( 'select_page', 'MonLabo_thematic_page_parent',  $this->_html->dashicon( 'buddicons-groups' ) . __( 'Parent page for thematic pages', 'mon-laboratoire' ), false );
				$this->add_field( 
					'button_create_default_parent_page', 'MonLabo_do_create_thematic_page_parent', '', !is_null( get_post( $options10['MonLabo_thematic_page_parent'] ) ),
					$this->_html->dashicon( 'buddicons-groups' ) . __( 'Generate default parent page ("Thematics")', 'mon-laboratoire' ) 
				);
			}
			if ( $options->uses['units'] ) {
				$this->add_field( 'select_page', 'MonLabo_unit_page_parent',  $this->_html->dashicon( 'admin-multisite' ) . __( 'Parent page for units pages', 'mon-laboratoire' ), false );
				$this->add_field( 
					'button_create_default_parent_page', 'MonLabo_do_create_unit_page_parent', '', !is_null( get_post( $options10['MonLabo_unit_page_parent'] ) ),
					$this->_html->dashicon( 'admin-multisite' ) . __( 'Generate default parent page ("Units")', 'mon-laboratoire' )
				);
			}
		}
	}

	/**
	* Configure 'Appearance' tab
	* @return void
	*/
	private function _handle_appearance_tab() {
		$options = Options::getInstance();
		$this->_curr_settings_group = 'MonLabo_settings_group2';
		$this->_curr_page = 'MonLaboPageAppearance';
		$this->add_section( 'MonLabo_pluginPage_section_liens',  __( 'Links', 'mon-laboratoire' ) );
		$this->add_field( 'color_picker', 'MonLabo_foreground_color',  __( 'Color of links and photo outlines', 'mon-laboratoire' ),   !$options->uses['members_and_groups'] );
		$this->add_section( 'MonLabo_pluginPage_section_photoPerso',  __( 'Personal pictures', 'mon-laboratoire' ) );
		$this->add_field( 'text_field',  'MonLabo_img_arrondi', 	  __( 'Rounding rate of pictures (0 to 50%)', 'mon-laboratoire' ),  !$options->uses['members_and_groups'] );
		$this->add_field( 'img_field',   'MonLabo_img_par_defaut',    __( 'Default image', 'mon-laboratoire' ), 					 !$options->uses['members_and_groups'] );
		$this->add_section( 'MonLabo_pluginPage_section_persoPanel', __( 'Personal banner (perso panel)', 'mon-laboratoire' ));
		$this->add_field( 'text_field',  'MonLabo_perso_panel_width',   __( 'Width of the personal banner', 'mon-laboratoire' ), 		 !$options->uses['members_and_groups'] );
		$this->add_field( 'text_field',  'MonLabo_name_size', 		__( 'Size of person\'s name', 'mon-laboratoire' ), 		 !$options->uses['members_and_groups'] );
		$this->add_field( 'text_field',  'MonLabo_address_block_width',  __( 'Address block width', 'mon-laboratoire' ), 		   !$options->uses['members_and_groups'] );
		$this->add_field( 'color_picker', 'MonLabo_address_color', 	__( 'Address color', 'mon-laboratoire' ), 				!$options->uses['members_and_groups'] );
		$this->add_field( 'text_field',  'MonLabo_address_size', 	 __( 'Size of the address text', 'mon-laboratoire' ), 		!$options->uses['members_and_groups'] );
		$this->add_section( 'MonLabo_pluginPage_section_csscustom', __( 'Custom formatting', 'mon-laboratoire' ) );
		$this->add_field( 'text_area',   'MonLabo_custom_css',    __( 'Custom CSS code', 'mon-laboratoire' ), 			  !$options->uses['members_and_groups'] );
	}

	/**
	* Configure 'Pivacy' tab
	* @return void
	*/
	private function _handle_privacy_tab() {
		$options = Options::getInstance();
		$this->_curr_settings_group='MonLabo_settings_group11';
		$this->_curr_page = 'MonLaboPagePrivacy';
		$this->add_section( 'MonLabo_pluginPage_section_data_privacy', __( 'Data privacy', 'mon-laboratoire' ));
		$this->add_field( 'checkbox2', 'MonLabo_hide_persons_email',
			$this->_html->dashicon( 'admin-users' ) .  __( 'Hide emails on the website', 'mon-laboratoire' ),
			!$options->uses['members_and_groups'] , __( 'Check this box to hide emails of all users.', 'mon-laboratoire' ) );
	}

	/**
	* Configure 'Custom texts' tab
	* @return void
	*/
	private function _handle_customTexts_tab() {

		$options = Options::getInstance();
		$this->_curr_settings_group = 'MonLabo_settings_group5';
		$this->_curr_page = 'MonLaboPageCustomTexts';
		$this->add_simple_html( '<p>' .  __( 'Shortcuts:', 'mon-laboratoire' )
			. ( $options->uses['thematics'] ? ' <a href="#group">' . __( 'Team groups', 'mon-laboratoire' ) . '</a>,' : '' )
			. ' <a href="#publications">' . __( 'Display of publications', 'mon-laboratoire' ) . '</a>'
			. ( $options->uses['members_and_groups'] ? ' <a href="#person">' . __( 'Person pannel', 'mon-laboratoire' ) . '</a>,' : '' )
			. ( $options->uses['members_and_groups'] ? ' <a href="#persons">' . __( 'Members list', 'mon-laboratoire' ) . '</a>,' : '' )
			. '</p>' );
	
		if ( ! !$options->uses['thematics'] ) {
			$this->add_simple_html( '<fieldset id="group" class="MonLabo-fieldset">' );
		}
		$this->add_section(
			'MonLabo_pluginPage_section_custom_groupName',
			( $options->uses['thematics'] ?  __( 'Names of the groups of teams (thematic, research group, axis...)', 'mon-laboratoire' ) : '' )
		);
		$this->add_field( 'four_text_fields', 'MonLabo_custom_text_Thematic', __( 'Name of the grouping', 'mon-laboratoire' ), !$options->uses['thematics'] );

		$this->add_simple_html( ( $options->uses['thematics'] ? '</fieldset>' : '' ) . '<fieldset id="publications" class="MonLabo-fieldset">' );

		$this->add_section( 'MonLabo_pluginPage_section_custom_publications_texts', __( 'Display of publications', 'mon-laboratoire' ));
		$this->add_field( 'two_text_fields',  'MonLabo_custom_text_Recent_Publications', __( 'Default title before the list of publications', 'mon-laboratoire' ) . ' (en/fr)' );

		$this->add_simple_html( '</fieldset>' . ( !$options->uses['members_and_groups'] ? '' : '<fieldset id="person" class="MonLabo-fieldset">' ) );
		if( $options->uses['members_and_groups'] ) {
			$this->add_simple_html( ' <div style="float:right;"><img src="' . plugins_url( 'images/VulcaneOlogist-66pc.png', __FILE__ ) . '" width="300" height="96" alt="Illustration where are displayed theses fields."></div>');
			$this->add_section( 'MonLabo_pluginPage_section_custom_perso_panel_texts', __( 'Person pannel', 'mon-laboratoire' ) );
		} else {
			$this->add_section( 'MonLabo_pluginPage_section_custom_perso_panel_texts', '' );
		}
		$this->add_simple_html(  ( $options->uses['members_and_groups'] ? '<div style="float:left;">' : '' ) );

		$this->add_field( 'two_text_fields', 'MonLabo_custom_text_Room', __( 'Room', 'mon-laboratoire' )
			. ' <img src="' . plugins_url( 'images/1.png', __FILE__ ) . '" width="15" height="15" alt="1">'
			, !$options->uses['members_and_groups'] );
		$this->add_field( 'two_text_fields', 'MonLabo_custom_text_Personal_website', __( 'Links to an external site', 'mon-laboratoire' )
			. ' <img src="' . plugins_url( 'images/2.png', __FILE__ ) . '" width="15" height="15" alt="2">'
			, !$options->uses['members_and_groups'] );
		$this->add_simple_html(  ( !$options->uses['members_and_groups'] ? '' : '</div>' ) );

		$this->add_field( 'four_text_fields', 'MonLabo_custom_text_Supervisor', __( 'Mentors and supervised students', 'mon-laboratoire' )
			. ' <img src="' . plugins_url( 'images/3.png', __FILE__ ) . '" width="15" height="15" alt="3">'
			, !$options->uses['members_and_groups'] );
		$this->add_field( 'four_text_fields', 'MonLabo_custom_text_Supervised_student', '', !$options->uses['members_and_groups'] );
		$this->add_simple_html(  ( !$options->uses['members_and_groups'] ? '' : '</fieldset>' ) . ( !$options->uses['members_and_groups'] ? '' : '<fieldset id="persons" class="MonLabo-fieldset">' ) );
		$this->add_section( 'MonLabo_pluginPage_section_custom_persons_texts',
			( $options->uses['members_and_groups'] ? __( 'Members list', 'mon-laboratoire' ) : '' )
		);

		$this->add_field( 'four_text_fields', 'MonLabo_custom_text_Team_leader', __( 'Default title for list of team leaders', 'mon-laboratoire' ), !$options->uses['members_and_groups'] );
		$this->add_field( 'two_text_fields', 'MonLabo_custom_text_Direction', __( 'Default title for list of direction members', 'mon-laboratoire' ), !$options->uses['members_and_groups'] );
		$this->add_field( 'four_text_fields', 'MonLabo_custom_text_Member', __( 'Default title for current members', 'mon-laboratoire' ), !$options->uses['members_and_groups'] );
		$this->add_field( 'two_text_fields', 'MonLabo_custom_text_Faculty', __( 'Categories', 'mon-laboratoire' ) . ' (Faculty / staff / postdocs / students / visitors)', !$options->uses['members_and_groups'] );
		$this->add_field( 'two_text_fields',  'MonLabo_custom_text_Staff' , '', !$options->uses['members_and_groups'] );
		$this->add_field( 'two_text_fields',  'MonLabo_custom_text_Postdocs', '', !$options->uses['members_and_groups'] );
		$this->add_field( 'two_text_fields',  'MonLabo_custom_text_Students', '', !$options->uses['members_and_groups'] );
		$this->add_field( 'two_text_fields',  'MonLabo_custom_text_Visitors', '', !$options->uses['members_and_groups'] );
		$this->add_simple_html( ( $options->uses['members_and_groups'] ? '</fieldset>' : '' ) );
	}

	/**
	* Configure 'Custom fields' tab
	* @return void
	*/
	private function _handle_customFields_tab() {
		$options = Options::getInstance();
		if ( $options->uses['custom_fields'] ) {
			$this->_curr_settings_group = 'MonLabo_settings_group3';
			$this->_curr_page = "MonLaboPageCustomFields";
			$this->add_section( 'MonLabo_pluginPage_section_custom_fields',  __( 'Personalized fields for the personal table', 'mon-laboratoire' ) );
			$this->add_field( 'number', 'MonLabo_custom_fields_number', __( 'Number of custom fields (1 to 10)', 'mon-laboratoire' ), !$options->uses['members_and_groups'] );
			$options3 = get_option( 'MonLabo_settings_group3' );
			if	 ( intval( $options3['MonLabo_custom_fields_number'] ) < 1 ) {  $nb_field=0; }
			elseif ( intval( $options3['MonLabo_custom_fields_number'] ) > 10 ) {  $nb_field=10; }
			else   {  $nb_field = intval( $options3['MonLabo_custom_fields_number'] ); }
			for ( $i=1; $i <= $nb_field; $i++ ) {
				$this->add_field( 'text_field', 'MonLabo_custom_field' . $i . '_title', sprintf( __( 'Field title #%u', 'mon-laboratoire' ), $i ), ( "$nb_field" >= "$i" ? !$options->uses['members_and_groups'] : true ) );
			}
		}
	}

	/**
	* Add section in a config page
	* @param string $section ID of the section
	* @param string $title title to display
	* @return Admin_Ui
	*/
	function add_section( string $section, string $title ): self {
		$this->_curr_section = $section;
		add_settings_section(   $section, 												// ID used to identify this section and with which to register options
								$title, 												// Title to be displayed on the administration page
								array( &$this, 'pluginPage_section_generic_callback' ), // Callback used to render the description of the section
								$this->_curr_page );												// Page on which to add this section of options
		return $this;
	}

	/**
	* Directly add HTML code in the config menu
	* @param string $html_code HTML code to display
	* @return Admin_Ui
	*/
	function add_simple_html( string $html_code ): self {
		add_settings_section(
			'simple_text_' . uniqid(),
			'',
				/*array( &$this, 'MonLabo_simple_html_render' ),*/
				function () use ( $html_code ) { // @phan-suppress-current-line PhanUnreferencedClosure
					echo ( $html_code );
				},
			$this->_curr_page
		);
		return $this;
	}

	/**
	* Callback used to render the description of the section
	* Called by Admin::add_section()
	* @return Admin_Ui
	*/
	function pluginPage_section_generic_callback( ): self { // @phan-suppress-current-line PhanUnreferencedPublicMethod
		/*echo __( '<h3>'.$arg['title'].'</h3>', 'MonLabo' ); */	
		return $this;
	}

	/**
	* Easily add a form field in the config menu
	* @param string $type Type of the field
	* @param string $option_name Name of the option to fill in the current section of options
	* @param string $title Text before the form field
	* @param bool $disable If true, do not display this form field
	* @param string $description Text description after the form field
	* @param string $on_change js to launch on change
	* @return Admin_Ui
	* @SuppressWarnings(PHPMD.BooleanArgumentFlag)
	*/
	function  add_field( string $type, string $option_name, string $title, bool $disable = false, string $description = '', string $on_change = '' ): self {
		$options_DEFAULT = App::get_options_DEFAULT();
		//if ( true === $disable ) { $type='hidden'; }
		if ( 'hidden' === $type ) { 
			$title = '';
			$description = '';
		} elseif ( '' === $description ) {
			if ( ! empty( $options_DEFAULT[ $this->_curr_settings_group ][ $option_name ] ) ) {
				$description='Ex: <em>' . $options_DEFAULT[ $this->_curr_settings_group ][ $option_name ] . '</em>';
			}
		}
		$this->_adminSettingsField->add(
			$type,
			( $disable ? '' : $title ),
			$this->_curr_page,
			$this->_curr_section,
			array(
				'option_name' => $option_name,
				'description' => ( $disable ? '' : $description ),
				'settings_group' => $this->_curr_settings_group,
				'disable' => $disable,
				'on_change' => $on_change
			)
		);
		return $this;
	}

	/**
	 * Render the configuration pages for editing persons and structures
	 * Called by Admin::add_menu()
	 * @return Admin_Ui
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	public function edit_persons_and_structures_render(): self {  // @phan-suppress-current-line PhanUnreferencedPublicMethod
		$options = Options::getInstance();
		//Interdit cette fonction si l'utilisateur n’a pas le droit 'manage_options'
		if ( ! current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permission to access this page.', "mon-laboratoire" ) ); 	// @codeCoverageIgnore
		}
		$active_tab = ( isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'tab_person' );
		settings_errors();
		echo( '<div class="wrap MonLabo_admin">' );
		echo $this->get_title( esc_html( get_admin_page_title() ) );
		echo( '<h2 class="nav-tab-wrapper">' );
		echo( '<a href="?page=MonLabo_edit_members_and_groups&tab=tab_person&lang=all" class="nav-tab ' . ( 'tab_person' === $active_tab ? 'nav-tab-active' : '' )  .'">' . $this->_html->dashicon( 'admin-users' ) . __( 'Persons', 'mon-laboratoire' ) . '</a>' );
		echo( '<a href="?page=MonLabo_edit_members_and_groups&tab=tab_team&lang=all" class="nav-tab ' . ( 'tab_team' === $active_tab ? 'nav-tab-active' : '' )  .'">' . $this->_html->dashicon( 'groups' ) . __( 'Teams', 'mon-laboratoire' ) . '</a>' );
		if ( $options->uses['thematics'] ) {
			$translate = new Translate();
			echo( '<a href="?page=MonLabo_edit_members_and_groups&tab=tab_thematic&lang=all" class="nav-tab ' . ( $active_tab === 'tab_thematic' ? 'nav-tab-active' : '' )  .'">' . $this->_html->dashicon( 'buddicons-groups' ) . $translate->tr__( 'Thematics' ) . '</a>' );
		}
		if ( $options->uses['units'] ) {
			echo( '<a href="?page=MonLabo_edit_members_and_groups&tab=tab_unit&lang=all" class="nav-tab ' . ( 'tab_unit' === $active_tab ? 'nav-tab-active' : '' ) .'">' . $this->_html->dashicon( 'admin-multisite' ) . __( 'Units', 'mon-laboratoire' ) . '</a>' );
		}
		echo( '<a href="?page=MonLabo_edit_members_and_groups&tab=tab_mainstruct&lang=all" class="nav-tab ' . ( 'tab_mainstruct' === $active_tab ? 'nav-tab-active' : '' )  .'">' . $this->_html->dashicon( 'admin-home' ) . __( 'Main structure', 'mon-laboratoire' ) . '</a>' );
		echo( '<a href="?page=MonLabo_edit_members_and_groups&tab=tab_tables&lang=all" class="nav-tab ' . ( 'tab_tables' === $active_tab ? 'nav-tab-active' : '' )  .'">' . $this->_html->dashicon( 'editor-table' ) . __( 'Table view', 'mon-laboratoire' ) . '</a>' );
		echo( '<a href="?page=MonLabo_edit_members_and_groups&tab=tab_adv&lang=all" class="nav-tab ' . ( 'tab_adv' === $active_tab ? 'nav-tab-active' : '' )  .'">' . $this->_html->dashicon( 'hammer' ) . __( 'Advanced tools', 'mon-laboratoire' ) . '</a>' );
		echo( '</h2>' );
		echo( '<div class="wrap MonLabo_admin">' );
		$editMembers = new Edit_Members();
		switch  ( $active_tab ) {
			case 'tab_person':
				echo $editMembers->edit_person_form(); break;
			case 'tab_team':
				echo $editMembers->edit_team_form(); break;
			case 'tab_thematic':
				echo $editMembers->edit_thematic_form(); break;
			case 'tab_unit':
				echo( '<h3>'. $this->_html->dashicon( 'warning' ) . __( 'If your structure is only one lab (one unit), do not fill.', 'mon-laboratoire' ) . '&nbsp;' .  $this->_html->dashicon( 'warning' ) . '</h3>' );
				echo $editMembers->edit_unit_form(); break;
			case 'tab_mainstruct':
				echo $editMembers->edit_mainstruct_form(); break;
			case 'tab_tables':
				$this->view_items_in_tables_render(); break;
			case 'tab_adv':
				$editMembers_Advanced = new Edit_Members_Advanced();
				echo $editMembers_Advanced->render_advanced_features(); break;
		}
		echo( '</div></div>' );
		return $this;
	}

	/**
	* Extract a title for all admin pages
	* @param string $soustitre Custom under title
	* @return string HTML code to insert
	*/
	public function get_title( string $soustitre='' ): string {
		$retval='';
		//Verify version
		$MonLabo_menu_info = get_plugin_data( plugin_dir_path( __FILE__ ) .'../mon-laboratoire.php' );
		$options = Options::getInstance();
		if ( $MonLabo_menu_info['Version'] != $options->activated_version ) {
			echo ( '<div class="error notice">
			<h3>' . __( 'Obsolete parameters', 'mon-laboratoire' ) . '</h3>
			<p>' . __( 'The plugin has changed settings and will update itself.', 'mon-laboratoire' ) . '<br />' . __( 'Please reload the page.', 'mon-laboratoire' ) . '</p>
			<br/>
			<p>' . sprintf( __( '(This plugin has been updated to version %1$s since version %2$s)', 'mon-laboratoire' ), $MonLabo_menu_info['Version'], $options->activated_version ) . '</p>
			</div>');
		}
		//Display title
		$retval .= '<h1>';
		$retval .= $MonLabo_menu_info['Name'] . ' <small>(' . sprintf( __( 'version %s', 'mon-laboratoire' ), $MonLabo_menu_info['Version'] ) . ')</small> '. '<a href="' . $MonLabo_menu_info['PluginURI'] . '" class="MonLabo-uri">'. str_replace( array( 'http://', 'https://' ), '', $MonLabo_menu_info['PluginURI'] ) . '</a>';
		$retval .= '</h1>';
		if ( '' != $soustitre ) {
			$retval .= ' <h2>' . $soustitre . '</h2>';
		}

		//Permit to migrate if necessary
		activate_MonLabo();
		return $retval;
	}

	/**
	* Display all help menu and help buttons
	* Called by Admin::add_menu()
	* @return Admin_Ui
	*/
	public function doc_render(): self { // @phan-suppress-current-line PhanUnreferencedPublicMethod
		//$MonLabo_menu_info = get_plugin_data( plugin_dir_path( __FILE__ ) .'../mon-laboratoire.php' );
		echo( '<div class="MonLabo-logo-tutelle"><a href="http://www.u-paris.fr/"><img src="' . plugins_url( 'images/logo-upc.png', __FILE__ ) . '"  alt="Logo Université de Paris Cité" /></a></div>' );
		echo( '<div class="MonLabo-logo-tutelle"><a href="http://www.cnrs.fr/"><img src="' . plugins_url( 'images/logo-cnrs.png', __FILE__ ) . '" alt="Logo CNRS" /></a></div>' );
		echo $this->get_title();
		echo( '<h2>' . __( 'Simplify the management of a research unit\'s website', 'mon-laboratoire' ) . '</h2>' );
		MonLabo_help_render();
		return $this;
	}


	/**
	* Display the configuration page with all the tabs
	* Called by Admin::add_menu()
	* @return Admin_Ui
	* @SuppressWarnings(PHPMD.ElseExpression)
	*/
	public function config_render(): self { // @phan-suppress-current-line PhanUnreferencedPublicMethod
		//Interdit cette fonction si l'utilisateur n’a pas le droit 'manage_options'
		$options = Options::getInstance();
		if ( ! current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permission to access this page.', "mon-laboratoire" ) ); 	// @codeCoverageIgnore
		}
		$active_tab = ( isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'tab_functionalities' );
		echo( '<div class="wrap MonLabo_admin">' );
		echo( $this->get_title( esc_html( get_admin_page_title() ) ) );
		settings_errors();
		echo( '<form method="POST" id="MonLabo_admin_options" class="MonLabo_admin_options" action="options.php">' );

		echo( '<h2 class="nav-tab-wrapper">' );
		echo( '<a href="?page=MonLabo_config&tab=tab_functionalities" class="nav-tab '. ( 'tab_functionalities' === $active_tab ? 'nav-tab-active' : '' ) . '">' . $this->_html->dashicon( 'admin-settings' ) . __( 'Features', 'mon-laboratoire' ) . '</a>' );
		if ( 'aucun' !== $options->publication_server_type ) {
			echo( '<a href="?page=MonLabo_config&tab=tab_configpublications" class="nav-tab '. ( 'tab_configpublications' === $active_tab ? 'nav-tab-active' : '' ) . '">' . $this->_html->dashicon( 'book' ) . __( 'Publications', 'mon-laboratoire' ) . '</a>' );
		}
		echo( '<a href="?page=MonLabo_config&tab=tab_langues" class="nav-tab '. ( 'tab_langues' === $active_tab ? 'nav-tab-active' : '' ) . '">' . $this->_html->dashicon( 'flag' ) . __( 'Languages', 'mon-laboratoire' ) . '</a>' );
		if ( $options->uses['members_and_groups'] ) {
			echo( '<a href="?page=MonLabo_config&tab=tab_pages&lang=all" class="nav-tab '. ( 'tab_pages' === $active_tab ? 'nav-tab-active' : '' ) . '">' . $this->_html->dashicon( 'admin-page' ) . __( 'Pages', 'mon-laboratoire' ) . '</a>' );
			echo( '<a href="?page=MonLabo_config&tab=tab_appearance" class="nav-tab '. ( 'tab_appearance' === $active_tab ? 'nav-tab-active' : '' ) . '">' . $this->_html->dashicon( 'admin-appearance' ) . __( 'Appearance', 'mon-laboratoire' ) . '</a>' );
			echo( '<a href="?page=MonLabo_config&tab=tab_privacy" class="nav-tab '. ( 'tab_privacy' === $active_tab ? 'nav-tab-active' : '' ) . '">' . $this->_html->dashicon( 'privacy' ) . __( 'Privacy', 'mon-laboratoire' ) . '</a>' );
			if ( $options->uses['custom_fields'] ) {
				echo( '<a href="?page=MonLabo_config&tab=tab_customfields" class="nav-tab '. ( 'tab_customfields' === $active_tab ? 'nav-tab-active' : '' ) . '">' . $this->_html->dashicon( 'insert-before' ) . __( 'Custom fields', 'mon-laboratoire' ) . '</a>' );
			}
		}
		echo( '<a href="?page=MonLabo_config&tab=tab_customtexts" class="nav-tab '. ( 'tab_customtexts' === $active_tab ? 'nav-tab-active' : '' ) . '">' . $this->_html->dashicon( 'edit' ) . __( 'Custom texts', 'mon-laboratoire' ) . '</a>' );
		echo( '</h2>' );
		switch ( $active_tab ) {
			case 'tab_functionalities':
				settings_fields( 'pluginSettings_MonLabo_settings_group0' );
				do_settings_sections( 'MonLaboPageFunctionalities' );
				break;
			case 'tab_configpublications':
				settings_fields( 'pluginSettings_MonLabo_settings_group4' );
				do_settings_sections( 'MonLaboPageConfigPublications' );
				break;
			case 'tab_langues':
				settings_fields( 'pluginSettings_MonLabo_settings_group6' );
				do_settings_sections( 'MonLaboPageLangues' );
				break;
			case 'tab_pages':
				settings_fields( 'pluginSettings_MonLabo_settings_group10' );
				do_settings_sections( 'MonLaboPagePages' );
				break;				
			case 'tab_appearance':
				settings_fields( 'pluginSettings_MonLabo_settings_group2' );
				do_settings_sections( 'MonLaboPageAppearance' );
				break;
			case 'tab_privacy':
				settings_fields( 'pluginSettings_MonLabo_settings_group11' );
				do_settings_sections( 'MonLaboPagePrivacy' );
				break;
			case 'tab_customtexts':
				settings_fields( 'pluginSettings_MonLabo_settings_group5' );
				do_settings_sections( 'MonLaboPageCustomTexts' );
				break;
			case 'tab_customfields':
				settings_fields( 'pluginSettings_MonLabo_settings_group3' );
				do_settings_sections( 'MonLaboPageCustomFields' );
				break;
		}
		submit_button();
		echo( '</form>' );
		echo( '</div>' );
		return $this;
	}

	/**
	* Display the configuration page for cache
	* Called by Admin::add_menu()
	* @return Admin_Ui
	* @SuppressWarnings(PHPMD.ElseExpression)
	*/
	public function config_cache_render(): self { // @phan-suppress-current-line PhanUnreferencedPublicMethod
		echo( '<div class="wrap MonLabo_admin">' );
		echo( $this->get_title( esc_html( get_admin_page_title() ) ) );
		settings_errors();
		echo( '<form method="POST" id="MonLabo_admin_options" action="options.php">' );
		settings_fields( 'pluginSettings_MonLabo_settings_group7' );
		do_settings_sections( 'MonLaboPageConfigPublicationsCache' );
		submit_button();
		echo( '</form>' );
		echo( '</div>' );
		return $this;
	}


	/**
	* Display the configuration page "Table view"
	* @return Admin_Ui
	*/
	public function view_items_in_tables_render(): self {
		$options = Options::getInstance();
		echo( '<div class="wrap admin-tables MonLabo_admin" id="haut">' );
		echo( '<p>'. __( 'Shortcuts:', 'mon-laboratoire' ) . ' <a href="#personnes_table">' . __( 'Persons', 'mon-laboratoire' ) . '</a>, <a href="#equipes_table">' . __( 'Teams', 'mon-laboratoire' ) . '</a>' );
		if ( $options->uses['thematics'] ) {
			echo( ', <a href="#thematiques_table">' . __( 'Thematics', 'mon-laboratoire' ) . '</a>' );
		}
		if ( $options->uses['units'] ) {
			echo( ', <a href="#unites_table">' . __( 'Units', 'mon-laboratoire' ) . '</a>' );
		}
		echo( '</p><p>'. __( 'At a glance, view your entire database.', 'mon-laboratoire' ) . '</p>' );

		echo( '<h3 id="personnes_table">' . __( 'Persons', 'mon-laboratoire' ) . '</h3>' );
		echo( '<h4>' . _x( 'Working', 'nom_pluriel', 'mon-laboratoire' ) . '</h4>' );
		echo( generate_table_admin_for_persons( 'actif' ) );
		echo( '<h4>' . _x( 'Former members', 'nom_pluriel', 'mon-laboratoire' ) . '</h4>' );
		echo( generate_table_admin_for_persons( 'alumni' ) );
		echo( '<a href="#haut">' . __( 'Back to top of page', 'mon-laboratoire' ) . '</a>' );

		echo( '<h3 id="equipes_table">' . __( 'Teams', 'mon-laboratoire' ) . '</h3>' );
		echo generate_table_admin_for_teams();
		echo( '<a href="#haut">' . __( 'Back to top of page', 'mon-laboratoire' ) . '</a>' );

		if ( $options->uses['thematics'] ) {
			echo( '<h3 id="thematiques_table">' . __( 'Thematics', 'mon-laboratoire' ) . '</h3>' );
			echo generate_table_admin_for_thematics();
			echo( '<a href="#haut">' . __( 'Back to top of page', 'mon-laboratoire' ) . '</a>' );
		}

		if ( $options->uses['units'] ) {
			echo( '<h3 id="unites_table">' . __( 'Units', 'mon-laboratoire' ) . '</h3>' );
			echo generate_table_admin_for_units();
			echo( '<a href="#haut">' . __( 'Back to top of page', 'mon-laboratoire' ) . '</a>' );
		}
		echo( '</div>' );
		return $this;
	}

}



?>
