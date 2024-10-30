<?php
/**
 * Mon Laboratoire
 *
 * @package           Mon_Laboratoire
 * @author            Hervé SUAUDEAU
 * @link              https://wordpress.org/plugins/mon-laboratoire/
 * @license           GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Mon Laboratoire
 * Plugin URI:        http://www.monlabo.org
 * Description:       Simplify the management of a research unit's website
 * Version:           4.8.3
 * Requires at least: 5.6
 * Requires PHP:      7.0.33
 * Author:            Hervé SUAUDEAU
 * Author URI:        https://www.sppin.fr/members/herve-suaudeau/
 * Text Domain:       mon-laboratoire
 * Domain Path:       /languages
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
*/
/* Delphine RIDER was co-author before versions v2.0 */

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

require_once ( __DIR__ . '/Lib/polyfill.php' );
require_once ( __DIR__ . '/autoload.php' );

use MonLabo\Lib\{Db,Options,First_Config};
use MonLabo\Frontend\{Contact_Webservices};

/**
* Currently plugin version, use SemVer - https://semver.org
* @return string version
*/
function get_MonLabo_version(): string { return '4.8.3'; }

///////////////////////////////////////////////////////////////////////////////////////////
//					PLUGIN CLASS DEFINITIONS
///////////////////////////////////////////////////////////////////////////////////////////

class MonLabo {
	function  __construct() {
		add_action( 'init', array( &$this, 'MonLabo_init' ) );
		if ( is_admin() ) {
			require_once( __DIR__.'/Admin/class-admin.php' );
		}
	}

	/**
	* Add all shortcodes
	* @access private
	* @return MonLabo
	*/
	private function _add_shortcodes() {
		$options = Options::getInstance();
		if ( $options->uses['members_and_groups'] ) {
			add_shortcode( 'members_list', array( '\MonLabo\Frontend\Shortcodes_Static', 'members_list' ) );
			add_shortcode( 'members_table', array( '\MonLabo\Frontend\Shortcodes_Static', 'members_table' ) );
			add_shortcode( 'members_chart', array( '\MonLabo\Frontend\Shortcodes_Static', 'members_chart' ) );
			add_shortcode( 'former_members_list', array( '\MonLabo\Frontend\Shortcodes_Static', 'former_members_list' ) );
			add_shortcode( 'former_members_table', array( '\MonLabo\Frontend\Shortcodes_Static', 'former_members_table' ) );
			add_shortcode( 'former_members_chart', array( '\MonLabo\Frontend\Shortcodes_Static', 'former_members_chart' ) );
			add_shortcode( 'teams_list', array( '\MonLabo\Frontend\Shortcodes_Static', 'teams_list' ) );
			add_shortcode( 'perso_panel', array( '\MonLabo\Frontend\Shortcodes_Static', 'perso_panel' ) );
			add_shortcode( 'team_panel', array( '\MonLabo\Frontend\Shortcodes_Static', 'team_panel' ) );

			//For back-compatibility
			add_shortcode( 'alumni_list', array( '\MonLabo\Frontend\Shortcodes_Static', 'former_members_list' ) );
			add_shortcode( 'alumni_table', array( '\MonLabo\Frontend\Shortcodes_Static', 'former_members_table' ) );
			add_shortcode( 'alumni_chart', array( '\MonLabo\Frontend\Shortcodes_Static', 'former_members_chart' ) );

			//For development (not documented )
			add_shortcode( 'dev_team_name', array( '\MonLabo\Frontend\Shortcodes_Static', 'dev_team_name' ) );
			add_shortcode( 'dev_team_logo', array( '\MonLabo\Frontend\Shortcodes_Static', 'dev_team_logo' ) );// OPTIMIZE:
		}

		// Shortcodes for publications
		add_shortcode( 'publications_list', array( '\MonLabo\Frontend\Shortcodes_Static', 'publications_list' ) );
		add_shortcode( 'publications_list2', array( '\MonLabo\Frontend\Shortcodes_Static', 'publications_list2' ) );
		return $this;
	}

	/**
	* Define inline CSS
	* @access private
	* @return MonLabo
	*/
	private function _define_inline_css() {
		$options = get_option( 'MonLabo_settings_group2' );
		$color1 = '';
		$color2 = '';
		$address_font_size = '';
		$address_block_width = '';
		$perso_panel_width = '';
		$img_arrondi = '';
		$name_size = '';

		if ( isset( $options['MonLabo_foreground_color'] ) ) { $color1 .= $options['MonLabo_foreground_color']; }
		if ( isset( $options['MonLabo_address_color'] ) ) { $color2 .= $options['MonLabo_address_color']; }
		if ( isset( $options['MonLabo_address_size'] ) ) { $address_font_size .= $options['MonLabo_address_size']; }
		if ( isset( $options['MonLabo_address_block_width'] ) ) { $address_block_width .= $options['MonLabo_address_block_width']; }
		if ( isset( $options['MonLabo_perso_panel_width'] ) ) { $perso_panel_width .= $options['MonLabo_perso_panel_width']; }
		if ( isset( $options['MonLabo_img_arrondi'] ) ) { $img_arrondi .= $options['MonLabo_img_arrondi']; }
		if ( isset( $options['MonLabo_name_size'] ) ) { $name_size .= $options['MonLabo_name_size']; }
		wp_enqueue_style( 'MonLabo', plugin_dir_url( __FILE__ ) . 'Frontend/css/mon-laboratoire.css', array(), get_MonLabo_version() );
		//TODO: Retirer les commentaires et voir pourquoi cela plante
		/*
		$$accessData = new \MonLabo\Lib\Access_Data\Access_Data();
		$teams = $$accessData->get_teams_info();
		$teams_id = array();
		$teams_color = '';
		if ( ! empty( $teams ) ){
			foreach ( $teams as $team ) {
			if ( isset( $team->color ) and ( $team->color! = '' ) ) {
				$teams_color. = ".MonLabo-persons-chart td .team_$team->id { color:'$team->color'; }";
			}
			}
		}*/
		$inline_css = '';
		if ( ! empty( $color1 ) ) {
			$inline_css .=  '
				MonLabo-persons-list {
					color: ' . $color1 . ';
				}
				.MonLabo-persons-list a:visited {
					color: ' . $color1 . ';
				}
				.MonLabo-persons-list ul li img {
					border-color: ' . $color1 . ';
				}
				.MonLabo-persons-list ul li a:hover img {
					border-color: ' . $color1 . ';
				}
				.MonLaboUser a,
				.MonLaboUser a:hover,
				.MonLaboUser a:visited {
					color: ' . $color1 . ' !important;
				}
				a.MonLaboLink,
				a.MonLaboLink:hover,
				a.MonLaboLink:visited {
					color: ' . $color1 . ' !important;
				}
				.publi_parisdescartes a {
					color: ' . $color1 . ' !important;
				}';
		}
		if ( ! empty( $perso_panel_width ) ) {
			$inline_css .=  '
				.bandeau-personnel {
					width: ' . $perso_panel_width . ';
				}';
		}
		$inline_css .=  '.bandeau-personnel .adresse {'
			.  ( ! empty( $color2 ) ? ( 'color: ' . $color2 . ';' ) : '' )
			.  ( ! empty( $address_block_width ) ? 'width: ' . $address_block_width . ';' : '' )
			. '}';
		$inline_css .=  '.bandeau-personnel .adresse p {'
			.  ( ! empty( $address_font_size ) ? 'font-size: ' . $address_font_size . ';' : '' )
			. '}';
		$inline_css .=  '.bandeau-personnel h1 {'
			.  ( ! empty( $name_size ) ? 'font-size: ' . $name_size . ';' : '' )
			. '}';
		$inline_css .=  '.img-arrondi {'
			.  ( ! empty( $img_arrondi ) ? 'border-radius: ' . $img_arrondi . ';' : '' )
			. '}';
		if ( ( isset( $options['MonLabo_custom_css'] ) ) and ( ! empty( $options['MonLabo_custom_css'] ) ) ) {
			$inline_css .= $options['MonLabo_custom_css'];
		}
		wp_add_inline_style( 'MonLabo', $inline_css );
		return $this;
	}

	/**
		* hook into WP's admin_init action hook
		* Configured in action 'init'
		* @return void
		*/
	public function MonLabo_init()  { //@phan-suppress-current-line PhanUnreferencedPublicMethod
		$this->_add_shortcodes();
		$this->_define_inline_css();

		//On ajoute un bouton éventuel sur la barre d'admin
		if ( is_admin_bar_showing() ) {
			require_once( __DIR__ . '/Admin/class-admin.php' );
			add_action( 'admin_bar_menu', '\MonLabo\Admin\custom_toolbar_user_link', 999 );
		}
	}

}

/* main
*******/

/**
 * The code that runs during plugin activation.
 * This action is documented in Frontend/class-plugin-name-activator.php
 * @return void
 */
function activate_MonLabo() {
	$FirstConfig = new First_Config();
	//Launch eventual migration
	if ( $FirstConfig->is_first_time_plugin_configured() ) {
		$FirstConfig->process_first_config();
	}
	$MonLaboDB = new Db();
	$MonLaboDB->migrate();
	$MonLaboDB->fill_options_with_default_values_if_empty();
	return;
}

/**
 * Deactivate the plugin
 * @return void
 */
function deactivate_MonLabo() {
	$webservice = new Contact_Webservices();
	$webservice->clear_transients();
}

/**
 * Check if the database is old
 * Configured in action 'plugin_loaded'
 * @return void
 */
function MonLabo_update_db_check() {  //@phan-suppress-current-line PhanUnreferencedFunction
	$options = Options::getInstance();
	if ($options->activated_version != get_MonLabo_version() ) {
		activate_MonLabo();
	}
}

/**
* hook to load languages
* Configured in action 'init'
* @return void
*/
function MonLabo_load_languages()  { //@phan-suppress-current-line PhanUnreferencedPublicMethod
	//For translation
	load_plugin_textdomain( 'mon-laboratoire', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

///////////////////////////////////////////////////////////////////////////////////////////
//						MAIN CODE
///////////////////////////////////////////////////////////////////////////////////////////
if ( class_exists( 'MonLabo' ) ) {
	// Installation hooks
	register_activation_hook	( __FILE__, 'activate_MonLabo' );
	register_deactivation_hook  ( __FILE__, 'deactivate_MonLabo' );

	// register_activation_hook() is not called when a plugin is updated. Thus
	// check if database is updated
	add_action( 'plugins_loaded', 'MonLabo_update_db_check' );
	add_action( 'plugins_loaded', 'MonLabo_load_languages' );

	// instantiate the plugin class
	$laboPlugin = new MonLabo();

	/**
	*  Add a link to the settings page onto the plugin page
	* Configured in action  "plugin_action_links_mon_laboratoire"
	* Add the settings link to the plugins page
	* https://www.skyverge.com/blog/wordpress-plugin-action-links/
	* @param string[] $links
	* @return string[]
	*/
	function plugin_settings_link( array $links ): array {  //@phan-suppress-current-line PhanUnreferencedFunction
		$settings_link = '<a href="'.admin_url( 'admin.php?page=MonLabo' ).'" title="' . __( 'Open the configuration page for this plugin', 'mon-laboratoire' ) . '">'.__( 'Configuration', 'mon-laboratoire' ).'</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	$plugin = plugin_basename( __FILE__ );
	add_filter( "plugin_action_links_$plugin", 'plugin_settings_link' );
}
