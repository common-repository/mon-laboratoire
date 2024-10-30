<?php
namespace MonLabo\Admin;
use MonLabo\Lib\{App};

defined( 'ABSPATH' ) or die('No direct script access allowed' );

/////////////////////////////////////////////////////////////////////////////////////
// GENERATE NOTICE MESSAGES ELEMENTS IN HTML
/////////////////////////////////////////////////////////////////////////////////////
/**
 * Class \MonLabo\Admin\Messages
 * @package
 */

 /* class Message {
	notice( string $type, string $title, string $message, string $mode = 'suround_with_p_tag' )
	echo_error_bad_migration()
	warning_if_necessary_unconfigured_parent( string $item_type, string $mode = 'hide' )
 }*/

class Messages {

	/**
	* Generate 'error', 'warning' or 'info' alert in HTML
	* @param string $type 'error', 'warning' or 'info'
	* @param string $title Title of box
	* @param string $message Content of box
	* @param string $mode if equal 'suround_with_p_tag' add <p> at the beginging and </p> at the end od $message
	* @return string HTML code
	*/
	public function notice( string $type, string $title, string $message, string $mode = 'suround_with_p_tag' ): string {
		switch ( $type ) {
			case 'error':
				$noticeclass = ' notice-error';
				$dismissible = '';
				break;

			case 'warning':
				$noticeclass = ' notice-warning';
				$dismissible = ' is-dismissible';
				break;

			default:
				$noticeclass = ' notice-info';
				$dismissible = ' is-dismissible';
				break;
		}
		if ( ! empty( $title ) ) {
			$title = "<h3>" . $title . "</h3>";
		}
		if ( ! empty( $message ) ) {
			if ( 'suround_with_p_tag' === $mode ) {
				$message = "<p>" . $message . "</p>";
			}
		}
		$retval ='<div class="notice inline' . $noticeclass . $dismissible . '">' . $title . $message . '</div>';
		return $retval;
	}

	/**
	* Display a specific error message for bad migration
	* Configured by self::adin_init() to be called in action 'admin_notices'
	* @return void
	*/
	public function echo_error_bad_migration() {  // @phan-suppress-current-line PhanUnreferencedPublicMethod
		echo $this->notice( 'error',
				__( 'Plugin Mon-laboratoire:', 'mon-laboratoire' ),
				__( 'The plugin does not work because it was installed on an obsolete version of mon-laboratoire (&lt; v2.8). To solve the problem, before putting back this version, you must install version 2.8 of the plugin to ensure data migration.', 'mon-laboratoire' )
			);
	}

	/**
	* Display a specific error message for unconfigured parent page if necessary
	* @param string $item_type 'person', 'team', 'thematic' or 'unit'
	* @param string $mode if 'hide', then hide.
	* @return string HTML code of the error message
	*/
	public function warning_if_necessary_unconfigured_parent( string $item_type, string $mode = 'hide' ): string {
		$retval = '';
		$options10 = get_option( 'MonLabo_settings_group10' );
		$option_item = 'MonLabo_'. $item_type .'_page_parent';
		if( $item_type === 'person' ) { $option_item = 'MonLabo_perso_page_parent'; }
		if ( !isset( $options10[ $option_item ] ) || ( App::NO_PAGE_OPTION_VALUE === $options10[ $option_item ] ) ){
			$retval .= '<div id="MonLabo_noParentPageConfigured" class="clear';
			if ( 'hide' === $mode ) { $retval .= ' MonLabo_hide'; }
			$retval .= '">';
			$button = '<div class="button button-primary btn-sm"><a href="' . get_admin_url() . 'admin.php?page=MonLabo_config&tab=tab_pages&lang=all">'
						. __( 'Configure', 'mon-laboratoire' ) . '</a></div>';
			$retval .= $this->notice(
				'warning',
				__( 'Parent pages not configured', 'mon-laboratoire'),
				'<p>' . __('You have not configured a parent page for new pages. By default, new pages will be saved at the root.', 'mon-laboratoire') 
				. '</p> ' . $button,
				'no_suround_p'
			);
			//Vous n'avez pas configuré de page parente pour les nouvelles pages. Si une page est crée, elle sera enregistrée à la racine.
			$retval .= '</div>';
		}
		return $retval;
	}

}
?>
