<?php
namespace MonLabo\Frontend;

use MonLabo\Lib\{Polylang_Interface, Translate, Lib, Options};
use MonLabo\Lib\Access_Data\{Access_Data};
use MonLabo\Lib\Person_Or_Structure\Groups\{Persons_Group, Teams_Group};
use MonLabo\Lib\Person_Or_Structure\{Unit};

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

/*
class Html {

	//General LIBRAIRY
	_to_html( string $string ):
	_obsfuscate_emails_separated_by_comma ( $emails )
	_obsfuscate_email( $email )
	dashicon( string $glyph_name )
	get_first_wpPostId_of_object( $object_info )
	_get_url_from_wp_pos_id( $object_info )
	_get_url_of_object( $object_info )
	_set_cliquable( string $url, string $text_to_click, string $a_complement = '' )
	_get_phone_with_prefix( $phone, $strong_ext = 'false' )

	//Pages
	page_full_info( int $wp_post_id )

	//Extract person's items
	persons_names( $persons_info )
	_person_name( $person_info )
	_person_name_simple_text( $person_info )
	_person_name_with_link( $person_info )
	person_name_with_admin_link( $person_info )
	item_name_with_logo( string $type, $item_info, bool $add_link = true )
	item_name_with_admin_link(string $type, $item_info, bool $add_link = true  )
	_team_name_in_html( $team_info, $language, $with_logo )
	_person_teams_enumeration( $person_info, $langage, $separator=', ' )
	person_address_HTML( $person_info, $language )
	person_thumbnail( $person_info, $width = 70, $height = 70, $class = 'wp-image-6 alignleft img-arrondi' ) {

	//Build sets of persons
	persons_chart( $unit_name, $directors, $teams_info, $persons_info_by_category_and_team, $language )
	persons_table_normal( $main_title, $colums_titles, $persons_info, $language, $titles_color, $status )
	persons_table_compact_column( $persons_info, $title, $language )
	persons_list( $title_single, $title_plural, $persons_info, $language )
	teams_list( $teams_info, $teams_leaders, $thematics_info, $teams_publi_page, $language )

	//Build panels
	_person_small_panel( $person_info, $language )
	person_panel( $person_info, $teams_id, $mentors_info, $students_info, $language )
	team_panel( $team_info, $leaders_info, $thematics_name, $language, $color )
	alumni_function( $person_info, $language )

	//Generic functions
	_name_to_id( $value )
	generic_table( $main_title, $colums_titles, $HTML_content_array, $column_titles_color, $table_title_color );
	generic_list( $main_title, $main_title_id, $HTML_content_list, $class_of_li );
*/

//For use function is_plugin-active()
//include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
/**
 * Class \MonLabo\Frontend\Html
 *
 * @package
 */
class Html {

	/**
	* constructor
	*/
	public function __construct() {
	}

	/**
	* Convert display string into HTML
	* @param string $string string to convert
	* @param bool $use_dashicons if true code like "#dashicon-foo#" are converted to "<span class="dashicons dashicons-foo">&nbsp;</span>"
	* @return string string HTML encoded
	* @access private
	*/
	private function _to_html( string $string, bool $use_dashicons = false ): string {
		$retval =  htmlspecialchars( $string,
							ENT_HTML5 | ENT_QUOTES, /*Convertit ', ", &, < et > */
							'UTF-8',
							false ); /*Convertit les code HTML déjà présents*/
		if ( $use_dashicons ) {
			$retval = preg_replace_callback(
						'/#dashicon-([a-z\-]+)#/', 
						function( $matches ) { return $this->dashicon($matches[1]); },
						$retval
					);
		}
		return $retval;
	 }


	/**
	* Obsfuscate email adress in order to avoid spam
	* @param string|null $emails email to encode
	* @return string encoded emails
	* @access private
	*/
	private function _obsfuscate_emails_separated_by_comma( $emails ): string {
		// separate out the host and the name
		$html = '';
		$options11 = get_option( 'MonLabo_settings_group11' );
		if( empty( $options11['MonLabo_hide_persons_email'] ) ) {
			if ( ! empty( $emails ) ) {
				$emails = str_replace( array( '|', ';', ' '), ',', $emails ); //Pouvoir utiliser d’autres separateur
				$emails_table = explode( ',', $emails );
				$emails_obfuscated = array();
				foreach ( $emails_table as $email ) {
					if ( ! empty( trim( $email ) ) ) {
						$emails_obfuscated[] = $this->_obsfuscate_email( trim ( $email ) );
					}
				}
				$html = Lib::secured_implode( ', ',  $emails_obfuscated );
			}
		}
		return $html;
	}


	/**
	* Obsfuscate one email adress in order to avoid spam
	* @param string|null $email email to encode
	* @return string encoded email
	* @access private
	*/
	private function _obsfuscate_email( $email ): string {
		// separate out the host and the name
		if ( '' != $email ) {
			$parts = explode( '@', $email );
			$email= '<span class="MonLabo-email">' . $parts[0] . '&#64;<span>-Code to remove to avoid SPAM-</span>' . $parts[1] . '</span>';
		}
		return $email;
	}

	/**
	* Returns the HTLM code for the dashicon.
	* @param string $glyph_name Glyph name : see https://developer.wordpress.org/resource/dashicons
	* @return string HTML code
	*/
	public function dashicon( string $glyph_name ): string {
		return '<span class="dashicons dashicons-' . $glyph_name . '" style="opacity: 0.5;">&nbsp;</span>';
	}

	/**
	* Returns the first wp_post_id fielf of an team, thematic or unit.
	* @param object|null $object_info Team informations object
	* @return string wp_post_id
	*/
	public function get_first_wpPostId_of_object( $object_info ): string {
		$wp_post_ids = $object_info->wp_post_ids;
		if ( empty( $wp_post_ids ) ) {
			return '';
		}
		return $wp_post_ids[0];
	}
	/**
	* Transform a wp_post_id into an url
	* @param string $wp_post_id
	* @return string URL
	* @access private
	*/
	private function _get_url_from_wp_pos_id( $wp_post_id ): string {
		if ( ! empty( $wp_post_id ) ) {
			if ( !is_numeric( $wp_post_id ) ) {
				return $wp_post_id;
			}
			//step 2b : use translated page if exists
			$Polylang_Interface = new Polylang_Interface();
			$transl_post_id = $Polylang_Interface->post_id_in_current_page_language( intval( $wp_post_id ) );
			return site_url() . '/?p=' . $transl_post_id;
		}
		return '';
	}


	/**
	* Returns the URL of an team, thematic or unit.
	* @param object|null $object_info Team informations object
	* @return string URL
	* @access private
	*/
	private function _get_url_of_object( $object_info ): string {
		if (  ! empty( $object_info )
	   		and property_exists( $object_info, 'wp_post_ids' )
		 ) {
			$wp_post_id = $this->get_first_wpPostId_of_object( $object_info );
			return $this->_get_url_from_wp_pos_id( $wp_post_id );
		}
		return '';
   }

	/**
	* Generates a URL link
	* @param string $url URL
	* @param string $text_to_click Text of the link
	* @param string $a_complement class of property to add in tag <a>
	* @return string HTML code
	* @access private
	*/
	private function _set_cliquable( string $url, string $text_to_click, string $a_complement = '' ): string {
		if ( '' != $url ) {
			return "<a href='" . $url . "'"
				. ( ! empty( $a_complement ) ? ' ' . $a_complement : '' ) . '>'
				. $text_to_click . '</a>';
		}
		return $text_to_click;
   }

	/**
	* Simple function to return a prefixed phone from table MonLabo_members
	* @param string|null $phone  Phone grabbed in table MonLabo_members
	* @param bool $strong_ext if true return extension number into <strong> tag
	* @return string the array based on the string
	* @access private
	*/
	private function _get_phone_with_prefix( $phone, bool $strong_ext = false ): string {
		if ( ! empty( $phone ) ) {
			if ( isset( $phone[0] ) AND '+' === $phone[0] ) {   //The phone is already with a prefix
				return $phone;
			}
			$options = get_option( 'MonLabo_settings_group1' );
			if ( $strong_ext ) {
				return $options['MonLabo_prefixe_tel'] . ' <strong>'. $phone .'</strong>';
			}
			return $options['MonLabo_prefixe_tel'] . ' '. $phone;
		}
		return '';
	}

	/**
	 * Display one page with all usefull info (ID, link, title, language flag)
	 * @param int $wp_post_id ID of the page
	 * @return string HTML code
	 */
	function  page_full_info( int $wp_post_id ): string {
		$Polylang_Interface = new Polylang_Interface();
		return $Polylang_Interface->get_edit_link_if_exists( $wp_post_id, 'compact' );
	}

	/**
	* Create an array of HTML codes of the names of persons.
	* @param object[]|null $persons_info Persons informations object table
	* @param string $format format of the names
	* @return string[] HTML code array
	*/
	public function persons_names( $persons_info, string $format = 'default' ): array {
		switch ( $format ) {
			case 'simple_text':      $person_func_name = '_person_name_simple_text'   ; break;
			case 'with_link':        $person_func_name = '_person_name_with_link'     ; break;
			case 'with_link_alumni': $person_func_name = '_person_name_force_link'    ; break;
			case 'with_admin_link':  $person_func_name = 'person_name_with_admin_link'; break;
			default:                 $person_func_name = '_person_name'               ; break;
		}
		$persons_names = array();
		if ( is_array( $persons_info ) ) {
			foreach ( $persons_info as $key => $person_info ) {
				$persons_names[ $key ] = $this->{$person_func_name}( $person_info );
			}
		}
		return $persons_names;
	}

	/**
	* Generates a HTML text of a name of a person
	* @param object $person_info Person informations object
	* @return string HTML code
	* @access private
	*/
	private function _person_name( $person_info ): string {
		return $this->_to_html( $person_info->first_name )
			. ' <span class="MonLabo-lastname">' . $this->_to_html( $person_info->last_name ) . '</span>';
	}

	/**
	* Generates a text of a name of a person (no CSS)
	* @param object $person_info Person informations object
	* @return string text
	* @access private
	* @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	*/
	private function _person_name_simple_text( $person_info ): string {
		return sanitize_text_field( $person_info->first_name ) . ' '
			. mb_strtoupper( sanitize_text_field( $person_info->last_name ), 'UTF-8' );
	}

	/**
	* Create HTML code of the name of a person that one can click on.
	* @param object|null $person_info Person informations object
	* @return string HTML code
	* @access private
	*  - If wp_post_id is not empty and is numerical return '<a href="?p=wp_post_id">Fistname LASTNAME</a>'
	*  - If wp_post_id is not empty and is not numerical return '<a href="wp_post_id">Fistname LASTNAME</a>'
	*  - else return 'Fistname LASTNAME'
	*/
	private function _person_name_with_link( $person_info ): string {
		if ( ! isset( $person_info->id ) ) { return ''; }

		$is_alumni = false;
		if ( property_exists( $person_info, 'status' ) ) {
			$is_alumni = ( 'alumni' === strtolower( $person_info->status ) );
		}
		if ( ! $is_alumni ) {
			return $this->_person_name_force_link( $person_info );
		}
		return $this->_person_name( $person_info );
	}

		/**
	* Create HTML code of the name of a person that one can click on.
	* @param object|null $person_info Person informations object
	* @return string HTML code
	* @access private
	*  - If wp_post_id is not empty and is numerical return '<a href="?p=wp_post_id">Fistname LASTNAME</a>'
	*  - If wp_post_id is not empty and is not numerical return '<a href="wp_post_id">Fistname LASTNAME</a>'
	*  - else return 'Fistname LASTNAME'
	*/
	private function _person_name_force_link( $person_info ): string {
		if ( ! isset( $person_info->id ) ) { return ''; }

		$link_to_display = '';
		$end_link_to_display = '';
		$wp_post_id = $this->get_first_wpPostId_of_object( $person_info );
		if ( !empty( $wp_post_id ) ) {
			if (
				! is_numeric( $wp_post_id )
				or ( 'publish' ===  get_post_status( intval( $wp_post_id ) ) ) // @phan-suppress-current-line PhanTypeMismatchArgument
			) {
				$link_to_display = '<a href="' . $this->_get_url_from_wp_pos_id( $wp_post_id ). '" class=\'MonLaboLink\'>';
				$end_link_to_display = '</a>';
			}
		}
		return $link_to_display . $this->_person_name( $person_info ) . $end_link_to_display;
	}

	/**
	* Create HTML code of the name of a person that one can click on but link is the
	* administrative page of this person.
	* @param object|null $person_info Person informations object
	* @return string HTML code '<a href="admin.php?page=MonLabo_edit_members_and_groups&tab=tab_person&submit_item=id&lang=all">Fistname LASTNAME</a>'
	*/
	public function person_name_with_admin_link( $person_info ): string { // @phan-suppress-current-line PhanUnreferencedPublicMethod
	   return $this->item_name_with_admin_link( 'person', $person_info );
   }

	/**
	* Create HTML code of the name of a person,team,thematic or unit that one can click on but link is the
	* administrative page of this item.
	* @param string $type 'person', 'persons', 'team', 'teams', 'thematic', 'thematics', 'unit' or 'units'
	* @param object|null $item_info Person_or_structure informations object
	* @return string HTML code '<a href="admin.php?page=MonLabo_edit_members_and_groups&tab=tab_person&submit_item=id&lang=all">Fistname LASTNAME</a>'
	* @SuppressWarnings(PHPMD.ElseExpression)
	*/
	public function item_name_with_admin_link( string $type, $item_info, bool $add_link = true ): string {
		if ( ! isset( $item_info->id ) ) { return ''; }
		$translate = new Translate();
		$type = rtrim( $type, "s");
		if ( 'person' != $type ) {
			if ( ! isset( $item_info->name_en ) or ! isset( $item_info->name_fr )  ) { return ''; }
			$name = sanitize_text_field( $translate->switch_lang( $item_info->name_en, $item_info->name_fr ) );
		} else {
			$name = $this->_person_name( $item_info );
		}
		$tab = 'tab_' . $type;
		$link_to_display = '';
		$end_link_to_display = '';
		if ( $add_link ) {
			$link_to_display = '<a href="admin.php?page=MonLabo_edit_members_and_groups&tab=' . $tab . '&submit_item=' . $item_info->id . '&lang=all" class=\'MonLaboLink\'>';
			$end_link_to_display = '</a>';
		}
		return $link_to_display . $name . $end_link_to_display;
	}

	/**
	* Create HTML code of the name of a person,team,thematic or unit that one can click on but link is the
	* administrative page of this item and is on the logo.
	* @param string $type 'person', 'persons', 'team', 'teams', 'thematic', 'thematics', 'unit' or 'units'
	* @param object|null $item_info Person_or_structure informations object
	* @return string HTML code '<a href="admin.php?page=MonLabo_edit_members_and_groups&tab=tab_person&submit_item=id&lang=all">Fistname LASTNAME</a>'
	* @SuppressWarnings(PHPMD.ElseExpression)
	*/
	public function item_name_with_logo( string $type, $item_info, bool $add_link = true ): string {
		if ( ! isset( $item_info->id ) ) { return ''; }
		switch ( $type ) {
			case 'person':
			case 'alumni':
					$icon = $this->dashicon( 'admin-users' );
				break;
			case 'team':
				$icon = $this->dashicon( 'groups' );
				break;			
			case 'thematic':
				$icon = $this->dashicon( 'buddicons-groups' );
				break;	
			default:
				$icon = $this->dashicon( 'admin-multisite' );
				break;
		}
		$translate = new Translate();
		$type = rtrim( $type, "s");
		if ( ( 'person' != $type ) && ( 'alumni' != $type ) ) {
			if ( ! isset( $item_info->name_en ) or ! isset( $item_info->name_fr )  ) { return ''; }
			$name = sanitize_text_field( $translate->switch_lang( $item_info->name_en, $item_info->name_fr ) );
		} else {
			$name = $this->_person_name( $item_info );
		}
		$tab = 'tab_' . $type;
		$link_to_display = '';
		$end_link_to_display = '';
		if ( $add_link ) {
			$link_to_display = '<a href="admin.php?page=MonLabo_edit_members_and_groups&tab=' . $tab . '&submit_item=' . $item_info->id . '&lang=all" class=\'MonLaboLink\'>';
			$end_link_to_display = '</a>';
		}
		return $link_to_display . '<small>'. $icon  . '</small>' . $end_link_to_display . '&nbsp;' . $name;
	}

	/**
	* Create HTML code of the name of a team that one can click.
	* @param object|null $team_info Team informations object
	* @param string $language display language 'en-US' or 'fr-FR'
	* @param bool $with_logo display a logo image ?
	* @param bool $with_team_color display a team color block ?
	* @return string HTML code
	*	- if wp_post_id is not empty return '<a href="?p=ID">name_of_team</a>'
	*	- elseif is wp_post_id as url return '<a href="wp_post_id">name_of_team</a>'
	*	- else return name_of_team
	* @access private
	*/
	 private function _team_name_in_html( $team_info, string $language, bool $with_logo = false, bool $with_team_color = false ): string {
		$translate = new Translate( $language );
		$name = 'name_' . $translate->get_lang_short();
		$team_name = '';
		if ( ! empty( $team_info ) ) {
			// Prepare color block of the team
			$color_block = '';
			if ( $with_team_color ) {
				$color_block = "<div class='teams-list-color-block'";
				if ( ! empty( $team_info->color ) ) {
					$color_block .= " style='background-color:" . $team_info->color . ";'";
				}
				$color_block .= "></div>";
			}
			// Prepare picture of the team
			$team_logo = '';
			if ( ( '' != $team_info->logo ) && $with_logo ) {
				$team_logo = "<img src='" . $this->image_from_id_or_url( $team_info->logo )
					. "' class='wp-image-8 alignleft wp-post-image team_logo' alt=' ' />";
			}
			$team_name = $this->_set_cliquable(
				$this->_get_url_of_object( $team_info ),
				$color_block . $team_logo . "<span class='MonLabo_team_name'>" .  $this->_to_html( $team_info->{$name} ) . "</span>",
				"class='MonLaboLink'"
			);
		}
		return $team_name;
	}

	/**
	* Creates the HTML code of a list of a person's teams
	* @param object|null $person_info Person informations object
	* @param string $language display language "en-US" or "fr-FR"
	* @param string $separator string to insert between each team (default = ', ')
	* @return string HTML code
	* @access private
	*/
	private function _person_teams_enumeration( $person_info, string $language, string $separator = ', ' ): string {
		$accessData = new Access_Data();

		if ( ! isset( $person_info->id ) ) { return ''; }
		$teams_info = $accessData->get_teams_info_for_a_person( $person_info->id );
		$teams = new Teams_Group( $teams_info );
		if ( $teams->count() === 0 ) {
			return '';
		}
		$teams_description = array();
		foreach ( $teams->sort_by_name( $language )->get_teams() as $team ) {
			$teams_description[ $team->id ] = $this->_team_name_in_html( $team, $language );
		}
		return "<span class='team-description'>" . implode( $separator, $teams_description ) . '</span>';
	}

	/**
	* Creates the HTML code of a person's address
	* @param object|null $person_info Person informations object
	* @param string $language display language 'en-US' or 'fr-FR'
	* @return string HTML code
	*/
	public function person_address_HTML( $person_info, string $language ): string {
		if ( ! isset( $person_info->id )  ) { return ''; }
		if ( isset( $person_info->address_alt ) and strlen( $person_info->address_alt ) > 0 ) {
			// 1-  Si une adresse alternative est précisée pour la personne, alors l’utiliser
			// ------------------------------------------------------------------------------
			return  '<div class="adresse"><p>'
				. nl2br( $this->_to_html( $person_info->address_alt ) ) . '</p></div>';
		}
		// 2- Par défaut utiliser l’adresse de la structure principale
		// -----------------------------------------------------------
		$options = Options::getInstance();
		$options1 = get_option( 'MonLabo_settings_group1' );
		$labo_adresse = $options1['MonLabo_adresse'];
		$labo_contact = $options1['MonLabo_contact'];
		$labo_nom = $options1['MonLabo_nom'];
		$labo_code = $options1['MonLabo_code'];

		// 3- Si une unité est précisée, utiliser les coordonnées de cette unité
		// ---------------------------------------------------------------------
		if ( $options->uses['units'] ) {
			if ( isset( $person_info->id ) ) {
				$accessData = new Access_Data();
				$teams = $accessData->get_teams_info_for_a_person( $person_info->id );
				if ( ! empty( $teams ) ) {
					$first_team = reset( $teams ); //Reorder pointers
					if ( isset( $first_team->id ) and isset( $first_team->id_unit ) and ( $first_team->id_unit > 0 ) ) {
						//Regardons l’unité de la première équipe de la personne
						$unit = new Unit( 'from_id', (int ) $first_team->id_unit );

						if ( ! $unit->is_empty() ) {
							if ( strlen( (string) $unit->info->address_alt ) > 0 ) {
								//Une adresse alternative d’unité est précisée? Alors l’utiliser.
								$labo_adresse = $unit->info->address_alt;
							}
							if ( strlen( (string) $unit->info->contact_alt ) > 0 ) {
								//Un contact alternatif d’unité est précisé? Alors l’utiliser.
								$labo_contact = $unit->info->contact_alt;
							}
							//récupérer le nom et le code de l’unité
							$translate = new Translate( $language );
							$labo_nom = $translate->switch_lang( $unit->info->name_en, $unit->info->name_fr );
							$labo_code = $unit->info->code;
						}
					}
				}
			}
		}
		//Forger l’adresse
		return  '<div class="adresse"><p>' . $this->_to_html(
						$labo_nom
						. ( empty( $labo_code ) ? '' : ' - ' . $labo_code ) ) . '<br />'
						. nl2br( $this->_to_html( $labo_adresse ) ) . '</p>'
						. '<p>' . nl2br( $this->_to_html( $labo_contact ) ) . '</p></div>';
	}


	/**
	* Creates the HTML code of a person's thumbnail picture
	* @param object|null $person_info Person informations object
	* @param int $width Width of the picture
	* @param int $height Height of the picture
	* @param string $class Custom class for the <img> (default = 'wp-image-6 alignleft img-arrondi')
	* @return string HTML code
	* @SuppressWarnings(PHPMD.ElseExpression)
	*/
	function person_thumbnail( $person_info, int $width = 70, int $height = 70, string $class = 'wp-image-6 alignleft img-arrondi' ) {
		if ( empty( $person_info->image ) || ( 'DEFAULT' === $person_info->image ) ) {
			$options2 = get_option( 'MonLabo_settings_group2' );
			$image_url = $this->image_from_id_or_url( $options2['MonLabo_img_par_defaut'] );
		} else {
			$image_url = $this->image_from_id_or_url( $person_info->image );
		}
		$image = '<img src="' . $image_url . '" class="' . $class . ' wp-post-image" height="' . $height . '" width="' . $width . '" alt=" " />';
		return $image;
	}

	/**
	* Suppress invisible persons in table persons_by_category_and_team
	* @param array<string,object[][]> $persons_by_category_and_team TWo dimentional array of persons classed by category and team_id
	* @return array<string,object[][]> $persons_by_category_and_team
	*/
	private function _filter_invisble_persons_by_category_and_team( array &$persons_by_category_and_team ) {
		if ( !empty( $persons_by_category_and_team ) ) {
			foreach ( $persons_by_category_and_team as $category => $persons_by_team ) {
				if ( is_array( $persons_by_team ) ) {
					foreach ( $persons_by_team as $team => $persons ) {
						if ( is_array( $persons_by_team ) ) {
							foreach ( $persons as $id => $person ) {
								if ( 'non' === $person->visible ) {
									unset( $persons_by_category_and_team[ $category ][ $team ][ $id ] );
								}
							}
						}
					}
				}
			}
		}
		return $persons_by_category_and_team;
	}

	/**
	* Creates the HTML code for an organization chart created from the object
	* information of a group of people.
	* @param string $unit_name Name of the unit
	* @param object[] $directors Table of persons that are directors
	* @param object[] $teams_info Table of teams of the unit
	* @param array<string,object[][]> $persons_by_category_and_team TWo dimentional array of persons classed by category and team_id
	* @param string $language Display language
	* @return string HTML code
	* @SuppressWarnings(PHPMD.ElseExpression)
	*/
	function persons_chart( string $unit_name, array $directors, array $teams_info, array $persons_by_category_and_team, string $language ): string {
		$translate = new Translate( $language );
		$name = 'name_' . $translate->get_lang_short();
		$persons_by_category_and_team = $this->_filter_invisble_persons_by_category_and_team( $persons_by_category_and_team );
		$number_of_persons = count( $persons_by_category_and_team );
		$a_afficher = '';
		if ( '0' != $number_of_persons ) {
			$a_afficher .= '<table class="MonLabo-persons-chart">';
			if ( count( $directors ) > 0 ) {
				$directors_name = $this->persons_names( $directors, 'with_link' );
				$a_afficher .= '<caption>' . $this->_to_html( $unit_name ) . '<br />' . $translate->tr__( 'Direction:' )  . ' ' . implode( ' &amp; ', $directors_name ) . '</caption>';
			} else {
				if ( ! empty( $unit_name ) ) {
					$a_afficher .= '<caption>' . $this->_to_html( $unit_name ) . '</caption>';
				}
			}

			$a_afficher .= '<tr>';
			foreach ( $teams_info as $team_id=>$team_info ) {
				$style = '';
				if ( ! empty( $team_info->color ) ) {
					$style = " style='color:#FFF; background-color:" . $team_info->color . ";'";
				}
				$a_afficher .= '<td class="team_' . $team_id . '"' . $style . '>';
				$a_afficher .= $this->_to_html( $team_info->{$name} );
				$a_afficher .= '</td>';
			}
			$a_afficher .= '</tr>';
			$categories = empty( $persons_by_category_and_team ) ? array() : array_keys( $persons_by_category_and_team );
			foreach ( $categories as $category ) {
				$a_afficher .= '<tr>';
				foreach ( $teams_info as $team_id=>$team_info ) {
					$style = '';
					if ( ! empty( $team_info->color ) ) {
						$style = " style='color:" . $team_info->color . ";'";
					}
					$translate = new Translate( $language );
					$transl_title =  $translate->tr__( ucfirst( $category ) );
					$a_afficher .= '<td class="team_' . $team_id . '"' . $style . '>';
					$a_afficher .= $this->_to_html( $transl_title );
					$a_afficher .= '</td>';
				}
				$a_afficher .= '</tr>';
				$a_afficher .= '<tr>';
				foreach ( $teams_info as $team_info ) {

					$a_afficher .= '<td>';
					if ( 	( array_key_exists( $category, $persons_by_category_and_team ) )
						and ( array_key_exists( $team_info->id, $persons_by_category_and_team[ $category ] ) ) ) {
						$members = $persons_by_category_and_team[ $category ][ $team_info->id ];
						foreach ( (array) $members as $member ) {
							if ( 0 !== strcmp( $member->visible, 'non' ) ) {  // supprime les personnes qui doivent être invisible
								if ( ( property_exists( $member, 'leader' ) ) and ( $member->leader ) ) {
									$a_afficher .= '<strong>' . $this->_person_name_with_link( $member ) . '</strong>';
								} else {
									$a_afficher .= $this->_person_name_with_link( $member );
								}
							}
							//If member is alumni, tells it
							$alumni_str = $this->alumni_function( $member, $language );
							if ( ! empty( $alumni_str ) ) {
								$a_afficher .= '<small>' . $alumni_str . '</small>';
							}
							$a_afficher .= '<br />';
						}
					}
					$a_afficher .= '</td>';
				}
				$a_afficher .= '</tr>';
				$a_afficher .= '<tr>';
				foreach ( $teams_info as $team_info ) {
					$style = '';
					if ( ! empty( $team_info->color ) ) {
						$style = " style='border-top-color:" . $team_info->color . ";'";
					}
					$a_afficher .= '<td>';
					$a_afficher .= '<hr' . $style . ' />';
					$a_afficher .= '</td>';
				}
				$a_afficher .= '</tr>';
			}
			$a_afficher .= '</table>';
		}
		return $a_afficher;
	}

	/**
	* Creates the HTML code for a complete table created from a group of persons
	* @param string $main_title Title to display in the top of the table
	* @param string[] $columns_titles Titles to display for each colums ( [ column_id => 'column title'] )
	* @param object[] $persons_info Pable of persons
	* @param string $language Display language
	* @param string $titles_color Color of titles
	* @param string $status status of persons to display
	* @return string HTML code
	* @SuppressWarnings(PHPMD.ElseExpression)
	*/
	function persons_table_normal(
		string $main_title,
		array $columns_titles,
		array $persons_info,
		string $language,
		string $titles_color = '',
		string $status = 'actif'
		): string {
		$MonLaboPersons = new Persons_Group( $persons_info );
		$list_array = array();
		//Recupère la bonne fonction en fonction du language
		$translate = new Translate( $language );
		$function = 'function_' . $translate->get_lang_short();

		$MonLaboPersons->suppress_non_visible();
		$a_afficher = '';
		if ( $MonLaboPersons->count() > 0 ) {
			$person_array = array();
			foreach ( $MonLaboPersons->get_persons() as $person_info ) {
				if ( ( 'all' === $status ) or ( $person_info->status === $status ) ) {
					$person_array['Nom'] = $this->_person_name_with_link( $person_info );
					if ( 'alumni' === $person_info->status ) {
						if ( ! empty( $person_info->date_departure ) ) {
							$person_array['Nom'] .= $this->_to_html( ' ('. $translate->tr__( lcfirst( $person_info->{$function} ) ) . ' ' . $translate->tr__( 'until' ) . ' ' . $person_info->date_departure . ')' );
						} else {
							$person_array['Nom'] .= $this->_to_html( ' (' . $translate->tr__( 'former' ) . ' '. $translate->tr__( lcfirst( $person_info->{$function} ) ) . ')' );
						}
					}
					$person_array['Equipe'] = $this->_person_teams_enumeration( $person_info, $language );
					$person_array['Email'] = $this->_obsfuscate_emails_separated_by_comma( $person_info->mail );
					$person_array['Tel'] = $this->_to_html( $this->_get_phone_with_prefix( $person_info->phone ) );
					$list_array[] = $person_array;
				}
			}
			//Hide emails if necessary
			$options11 = get_option( 'MonLabo_settings_group11' );
			if( ! empty( $options11['MonLabo_hide_persons_email'] ) ) {
				unset( $person_array['Email'] );
				foreach ( $columns_titles as $key => $value ) {
					if ( $value === "Email" ) {
						unset( $columns_titles[ $key ] ); 
					}
				}
			}
			//Traduction des colonnes
			$transl_titles = array();
			foreach ( $columns_titles as $title ) {
				$transl_titles['column_' . $title] = $translate->tr__( ucfirst( $title ) );
			}
			$a_afficher .= '<div class="MonLabo MonLabo-persons-table-normal">'
							 . $this->generic_table( $translate->tr__( ucfirst( $main_title ) ), $main_title, $transl_titles, $list_array, '', $titles_color )
							 . '</div>';
		}
		return $a_afficher;
	}

	/**
	* Creates the HTML code for a compact table (one-column table) created from a group of persons
	* @param object[] $persons_info Table of persons
	* @param string $title Title of table
	* @param string $language Display language
	* @param string $column_titles_color Color of titles
	* @return string HTML code
	*/
	function persons_table_compact_column( array $persons_info, string $title, string $language, string $column_titles_color = '' ): string {
		$MonLaboPersons = new Persons_Group( $persons_info );
		$a_afficher = '';
		$MonLaboPersons->suppress_non_visible();
		if ( $MonLaboPersons->count( ) >0 ) {
			//0 - Initialisations
			$person_array = array();
			$person_array[ $title ] = '<ul>';

			// 2 - Ajout des personnes
			foreach ( $MonLaboPersons->get_persons() as $person_info ) {
				$person_array[ $title ] .= '<li>' . $this->_person_name_with_link( $person_info ) . '</li>';
			}
			// 3 - Fermeture de toutes les balises <ul>
			$person_array[ $title ] .= '</ul>';

			// 4 - Traduction du titre
			$transl_categories = array();
			$translate = new Translate( $language );
			$transl_categories[ $title ] = $this->_to_html( $translate->tr__( ucfirst( $title ) ) );

			$a_afficher .= '<div class="MonLabo MonLabo-persons-table-compact">' . $this->generic_table( '', '', $transl_categories, array( $person_array ), $column_titles_color ) . '</div>';
		}
		return $a_afficher;
	}

	/**
	 * Crée le code HTML d’une liste de personnes crée à partir des Informations
	 * d’un groupe de personnes.
	 *
	 * @param string $title_single titre de la liste si celle si ne comporte qu’une personne
	 * @param string $title_plural titre de la liste si celle si comporte plusieurs personnes
	 * @param object[] $persons_info tableau d’information sur les personnes
	 * @param string $language langue d’affichage
	 *
	 * @return string code HTML
	 */
	public function persons_list( string $title_single, string $title_plural, array $persons_info, string $language ): string {
		$a_afficher = '';
		$MonLaboPersons = new Persons_Group( $persons_info );
		$MonLaboPersons->suppress_non_visible();
		if ( $MonLaboPersons->count() > 0 ) {
			$number_of_persons = 0;
			$HTML_content_list = array();
			foreach ( $MonLaboPersons->get_persons() as $person_info ) {
				$list_item = $this->_person_small_panel( $person_info, $language );
				if ( '' != $list_item ) {
					$HTML_content_list[] = $list_item;
					++$number_of_persons;
				}
			}
			if ( $number_of_persons > 0 ) {
				$translate = new Translate( $language );
				$adapted_title = $translate->tr_n( ucfirst( $title_single ), ucfirst( $title_plural ), $number_of_persons );
				$HTML_full_list = $this->generic_list( $adapted_title, $title_single, $HTML_content_list, 'MonLaboUser' );
				if ( '' != $HTML_full_list ) {
					$a_afficher .= '<div class="MonLabo MonLabo-persons-list">' . $HTML_full_list . '</div>';
				}
			}
		}
		return $a_afficher;
	}

	/**
	* Creates the HTML code for a list of teams
	 * @param object[] $teams_info Table of teams to display
	 * @param object[][] $teams_leaders_info[ $team_id ] table of persons information that are team leaders
	 * @param object[][] $thematics_info[ $team_id ] table of thematics for each teams
	 * @param string $teams_publi_page URL where team's publications are published
	 * @param string $language Display language
	* @return string HTML code
	*/
	public function teams_list(
		array $teams_info,
		array $teams_leaders_info,
		array $thematics_info,
		string $teams_publi_page,
		$language
		): string {
		$a_afficher = '';
		$translate = new Translate( $language );
		$nom = 'name_' . $translate->get_lang_short();
		$options = Options::getInstance();

		if ( 0 !== count( $teams_info ) ) { //We already know that teams_info is an array
			$a_afficher .= '<div class="teams_list"><ul>';

			/* Is any team uses colors ? */
			$use_teams_colors = false;
			foreach ( $teams_info as $team_info ) {
				if ( ! empty( $team_info->color ) ) {
					$use_teams_colors = true;
					break;
				}
			}

			/* Display each team */
			foreach ( $teams_info as $team_info ) {
				// Preparation du nom avec un lien vers la page d’équipe si elle existe

				//Prepare pictures of the other thematics
				$other_thematics_logos = '';
				if (
					$options->uses['thematics']
					and is_array( $thematics_info[ $team_info->id ] )
				) {
					foreach ( $thematics_info[ $team_info->id ] as $other_thematic ) {
						$other_thematics_logos .= $this->_set_cliquable(
							$this->_get_url_of_object( $other_thematic ),
							"<img src='" . $this->image_from_id_or_url( $other_thematic->logo ) . "' class='thematics_logo' alt=' '>" ,
							"class='MonLaboLink' title='Other thematic of the team : " . $this->_to_html( $other_thematic->{$nom} ) . "'"
						);
					}
					if ( ! empty( $other_thematics_logos ) ) {
						$other_thematics_logos = "<div class='teams-list-other-thematics-logos'>" . $other_thematics_logos . '</div>';
					}
				}
				$a_afficher .= '<li>'. "<div class='team_list_item'><h4>"
						. $this->_team_name_in_html( $team_info, $language, true, $use_teams_colors )
						. '</h4>';
				if (
					! empty( $teams_leaders_info )
					and property_exists( $team_info, 'id' )
					and isset( $teams_leaders_info[ $team_info->id ] )
				) {
					$teams_leaders_name = $this->persons_names( $teams_leaders_info[ $team_info->id ], 'with_link' );
					if ( ! empty( $teams_leaders_name ) ) {
						$a_afficher .=  '<div class="team_information">' . $translate->tr__( 'Direction:' ) . ' '
							.  Lib::secured_implode( ' &amp; ', $teams_leaders_name ) .'</div>';
					}
				}
				if (
					! empty( $teams_publi_page )
					and ! empty( $team_info->descartes_publi_team_id )
				) {
					$a_afficher .= "<div class='teams-list-publications-link'><a href='" . $teams_publi_page . '?equipe=';
					$a_afficher .= $team_info->descartes_publi_team_id . "' class='MonLaboLink'>Team publications</a></div>";
				}
				$a_afficher .= '</div>' . $other_thematics_logos . '</li>';
			}
			$a_afficher .= '</ul></div>';
		}
		return $a_afficher;
	}

	/**
	* Creates the HTML code for an item of the person's list
	* @param object $person_info Person to display
 	* @param string $language Display language
	* @return string HTML code
	* @access private
	* @SuppressWarnings(PHPMD.ElseExpression)
	*/
	private function _person_small_panel( /*object //PHP7.0 compatibility*/ $person_info, string $language ): string {
		$a_afficher = '';

		//Get the text data for the user
		//-----------------------------
		$translate = new Translate( $language );
		$function = $translate->switch_lang( $person_info->function_en, $person_info->function_fr );
		$is_alumni = ( 'alumni' === strtolower( $person_info->status ) );

		//function generate_HTML_for_list_user(
		//$external_link = '' ) {
		if ( 'non' != $person_info->visible ) {

			//Prepare picture of the user
			//---------------------------
			$image = $this->person_thumbnail( $person_info, 70, 70, 'wp-image-8 alignleft img-arrondi' );

			//Prepare link
			//------------
			$link_to_display = '';
			$end_link_to_display = '';
			if ( ( !$is_alumni )
				&& ( !empty( $this->get_first_wpPostId_of_object( $person_info ) ) )
			) {
				$link_to_display = '<a href="' . $this->_get_url_of_object( $person_info ). '" class=\'MonLaboLink\'>';
				$end_link_to_display = '</a>';
			} elseif ( ! empty( $person_info->external_url ) ) {
				$link_to_display = '<a href="' .$person_info->external_url . '" class=\'MonLaboLink\'>';
				$end_link_to_display = '</a>';
			}

			//Generate the HTML code
			//----------------------
			$a_afficher .= '<div class="MonLabo-user-image">' . $link_to_display . $image . $end_link_to_display . '</div>';
			$a_afficher .= '<div class="MonLabo-user-text">' . $link_to_display . '<strong>' . $this->_person_name( $person_info ) . '</strong>'. $end_link_to_display;
			if ( ! empty( $function ) ) {
				if ( $is_alumni ) {
					if ( '' != $person_info->date_departure ) {
						$a_afficher .= ', <em>' . $this->_to_html( $function. ' '. $translate->tr__( 'until' ) . ' ' . $person_info->date_departure ) . '</em>';
					} else {
						$a_afficher .= ', <em>' . $this->_to_html( $translate->tr__( 'Former' ) . ' ' . $function ). '</em>';
					}
				} else {
					$a_afficher .= ', <em>' . $this->_to_html( $function ). '</em>';
				}
			}
			$teams_enumeration = $this->_person_teams_enumeration( $person_info, $language );
			if ( ! empty( $teams_enumeration ) ) {
				$a_afficher .= ', ' . $teams_enumeration;
			}

			$a_afficher .= '<br /><div class="MonLabo-user-coordonates">';
			$list_to_display = array(); //On range les items suivants dans un tableau
			if ( ! empty( $person_info->mail ) ) {
				$list_to_display[] = $this->_obsfuscate_emails_separated_by_comma( $person_info->mail );
			}
			if ( ! empty( $person_info->phone ) ) {
				$list_to_display[] = $this->_to_html( $this->_get_phone_with_prefix( $person_info->phone ) );
			}
			if ( ( ! $is_alumni ) && ( ! empty( $person_info->room ) ) ) {
				$list_to_display[] = $this->_to_html( $translate->tr__( 'room' ) .' ' . $person_info->room );
			}
			$a_afficher .= Lib::secured_implode( ', ', $list_to_display ); //On met des virgules entre chaque item
			$a_afficher .= '</div></div>';
		}
		return $a_afficher;
	}

	/**
	 * sanitize name to be used as CSS class ID
	 * @param string $value
	 * @return string sanitized string
	 * @access private
	 */
	private function _name_to_id( $value ): string {
		return mb_strtolower( str_replace( ' ', '_', $value ), 'UTF8' );
	}

	/**
	* Creates the HTML code for gereric table based on an array and titles
	* @param string $main_title Title to display in the top of the table
	* @param string $main_title_id Id of title
	* @param string[] $colums_titles Titles to display for each colums ( [ column_id => 'column title'] )
	* @param array<string[]> $HTML_content_array Two dimentional array of table cells content (string)
	* @param string $column_titles_color Color of each column title
	* @param string $table_title_color Color the table title
	* @param string $table_class Table class
	* @return string HTML code
	*/
	function generic_table(
		string $main_title,
		string $main_title_id,
		array $colums_titles,
		array $HTML_content_array,
		string $column_titles_color = '',
		string $table_title_color = '',
		string $table_class = ''
		): string {
		$a_afficher = '';
		if ( ( ! ( empty( $HTML_content_array ) ) ) or ( ! empty( $colums_titles ) ) ) {
			if ( ! empty( $table_title_color ) ) {
				$table_title_color = ' style="color:' . $table_title_color .';"';
			}
			if (  ! empty( $main_title )  ) {
				$a_afficher .= "<a id='" . $this->_name_to_id( $main_title_id ) . "'></a>"; //Anchor
				$a_afficher .= '<h1' . $table_title_color . '>' . $this->_to_html( $main_title, true ) . '</h1>';
			}
			if ( ! empty( $table_class ) ) {
				$table_class = ' class="' . $table_class . '"';
			}
			$a_afficher .= '<table' . $table_class . '>';

			if ( ! empty( $colums_titles ) ) { //If title is empty => Do not display
				$a_afficher .= '<thead><tr>';
				if ( ! empty( $column_titles_color ) ) {
					$column_titles_color = ' style="color:' . $column_titles_color . ';"';
				}
				foreach ( $colums_titles as $class => $title ) {
					$classText = '';
					if ( ! is_int( $class ) ) {
						$classText = ' class="' . $class . " " . strtolower( $class ) . '"';
					}
					$a_afficher .= '<th scope="col"' . $classText . $column_titles_color . '>'
									. $this->_to_html( $title, true ) . '</th>';
				}
				$a_afficher .= '</tr></thead>';
			}

			if ( ! empty( $HTML_content_array ) ) { //If content is empty => Do not display
				$a_afficher .= '<tbody>';
				foreach ( $HTML_content_array as $line ) {
					if ( ! empty( $line ) ) {
						$a_afficher .= '<tr>';
						foreach ( $line as $cell ) {
							$a_afficher .= '<td>' . $cell . '</td>';
						}
						$a_afficher .= '</tr>';
					}
				}
				$a_afficher .= '</tbody>';
			}
			$a_afficher .= '</table>';
		}
		return $a_afficher;
	}

	/**
	* Creates the HTML code for a gereric list based on an array and titles
	* @param string $main_title Title to display in the top of the list
	* @param string $main_title_id ID of the title
	* @param string[] $HTML_content_list array of cells content (string)
	* @param string $class_of_li Optional class for each <li> element
	* @return string HTML code
	*/
	function generic_list( string $main_title, string $main_title_id, array $HTML_content_list, string $class_of_li = '' ): string {
		$a_afficher ='';
		if ( ! empty( $main_title ) and ! empty( $HTML_content_list ) )  {
			$a_afficher .= "<a id='" . $this->_name_to_id( $main_title_id ) . "'></a>"; //Anchor
			$a_afficher .= '<h1>' . $this->_to_html( $main_title ) . '</h1>'; //Title
		}
		if ( ! empty( $HTML_content_list ) ) { //If content is empty => Do not display
			$a_afficher .= '<ul>';
			if ( ! empty( $class_of_li ) ) {
				$class_of_li = ' class="' . $class_of_li . '"';
			}
			foreach ( $HTML_content_list as $line ) {
				if ( ! empty( $line ) ) {
					$a_afficher .= '<li' . $class_of_li . '>' . $line . '</li>';
				}
			}
			$a_afficher .= '</ul>';
		}
		return $a_afficher;
	}

	/**
	* Creates the HTML code for person panel
	* @param object $person_info Person object
	* @param object[] $mentors_info table of person that are mentors
	* @param object[] $students_info table of person that are students
	* @param string $language Display language
	* @return string HTML code
	* @SuppressWarnings(PHPMD.ElseExpression)
	*/
	public function person_panel(
		/*object //PHP7.0 compatibility*/ $person_info,
		array $mentors_info,
		array $students_info,
		string $language ): string {
		$translate = new Translate( $language );
		$function = $translate->switch_lang( $person_info->function_en, $person_info->function_fr );
		$room_name = $this->_to_html( $translate->tr__( 'room' ) );
		$title = $this->_to_html( $person_info->title );

		$mail = $this->_obsfuscate_emails_separated_by_comma( $person_info->mail );
		$phone = $this->_to_html( $this->_get_phone_with_prefix( $person_info->phone ) );
		$door = $person_info->room;
		$external_url = $person_info->external_url;

		$image = $this->person_thumbnail( $person_info, 150, 150, 'wp-image-6 alignleft img-arrondi' );
		//Generate HTML
		//------------
		$a_afficher = '';
		$a_afficher .= '<div class="bandeau-personnel MonLaboUser">';
		$a_afficher .= $image;
		$a_afficher .= $this->person_address_HTML( $person_info, $language );
		$a_afficher .= '<div class="monlaboBlocTexte">';
		$a_afficher .= '<h1>';
		if ( $title<>'' ) { $a_afficher .= $title . ' ';   }

		//If member is alumni, tells it
		$alumni_str = $this->alumni_function( $person_info, $language );

		$a_afficher .=  $this->_person_name( $person_info ) . $alumni_str;
		$a_afficher .= '</h1><p class="coordonnees"><em>' . $function;
		$a_afficher .= '</em><br />';
		$a_afficher .= $this->_person_teams_enumeration( $person_info, $language );
		$a_afficher .= '<br /><br />';
		if ( $mail<>'' ) { $a_afficher .= '' . $mail .'<br />'; }
		if ( $phone<>'' ) { $a_afficher .= $phone; }
		if ( $door<>'' ) {
			if ( $phone<>'' ) {
				$a_afficher .= ', ';
			}
			$a_afficher .=  $room_name . ' ' . $door;
		}
		if ( ! empty( $external_url ) ) {
			$a_afficher .= '<div class="external_url"><h1 style="text-align: center;">'
							 . '<a href="'. $external_url . '">'
							 . '<button class="btn btn-oldstyle" type="button">' . $translate->tr__( 'Personal website' ) . '</button></a></h1></div>';
		}
		$MonLabo_mentors = new Persons_Group( $mentors_info );
		$MonLabo_mentors->suppress_non_visible();
		$mentors_name = array();
		if ( $MonLabo_mentors->count() > 0 ) {
			$mentors_name = $this->persons_names( $MonLabo_mentors->get_persons(), 'with_link' );
		}
		if ( ! empty( $person_info->external_mentors ) ) {
			$lines = explode( "\n", str_replace( "\r","\n", $person_info->external_mentors ) );
			foreach ( $lines as $line ) {
				if ( ! empty( $line ) ) {
					$pos = strpos( $line, ',' );
					$link_to_display = '';
					$end_link_to_display = '';
					if ( $pos ) {
						$name = substr( $line, 0, $pos );
						$url = substr( $line, $pos + 1 );
						if ( ! empty( $url ) ) {
							$link_to_display = '<a href="'. $url . '" class=\'MonLaboLink\'>';
							$end_link_to_display = '</a>';
						}
					} else {
						$name = $line;
					}
					$mentors_name[] = $link_to_display . $name . $end_link_to_display;
				}
			}
		}
		if ( count( $mentors_name ) > 0 ) {
			$mentors_translate = $translate->tr_n(  'Supervisor',  'Supervisors', count( $mentors_name ) );
			$a_afficher .= '<div class="supervisor"><h1>' . $this->_to_html( $mentors_translate ) . ' : </h1>';
			$a_afficher .= '<div class="supervisors_list">'. implode( '<br />', $mentors_name ) . '</div></div>';
		}

		$MonLabo_students = new Persons_Group( $students_info );
		$MonLabo_students->suppress_non_visible();
		$students_name = array();
		if ( $MonLabo_students->count() > 0 ) {
			$students_name = $this->persons_names( $MonLabo_students->get_persons(), 'with_link' );
		}
		if ( ! empty( $person_info->external_students ) ) {
			$lines = explode( "\n", str_replace( "\r","\n", $person_info->external_students ) );
			foreach ( $lines as $line ) {
				if ( ! empty( $line ) ) {
					$link_to_display = '';
					$end_link_to_display = '';
					$pos = strpos( $line, ',' );
					if ( $pos ) {
						$name = substr( $line, 0, $pos );
						$url = substr( $line, $pos + 1 );
						if ( ! empty( $url ) ) {
							$link_to_display = '<a href="'. $url . '" class=\'MonLaboLink\'>';
							$end_link_to_display = '</a>';
						}
					} else {
						$name = $line;
					}
					$students_name[] = $link_to_display . $name . $end_link_to_display;
				}
			}
		}
		if ( count( $students_name ) > 0 ) {
			$students_translate = $translate->tr_n(  'Supervised student',  'Supervised students', count( $students_name ) );
			$a_afficher .= '<div class="supervisor"><h1>' . $this->_to_html( $students_translate ) . ' : </h1>';
			$a_afficher .= '<div class="supervisors_list">'. implode( '<br />', $students_name ) . '</div></div>';
		}
		$a_afficher .= '</div></div>';
		return $a_afficher;
	}

	/**
	* Creates the HTML code for an alumni function
	* @param object $person_info Person object
	* @param string $language Display language
	* @return string HTML code
	*/
	public function alumni_function( /*object //PHP7.0 compatibility*/ $person_info, string $language ): string {
		if ( 'alumni' === strtolower( $person_info->status ) ) {
			$translate = new Translate( $language );
			if ( '' != $person_info->date_departure ) {
				return  '<br /><em>(' . $this->_to_html( $translate->tr__( 'alumni since' ) . ' ' . $person_info->date_departure ) . ')</em>';
			}
			return  '<br /><em>(' . $this->_to_html( $translate->tr__( 'alumni' ) ) . ')</em>';
		}
		return '';
	}

	/**
	* Creates the HTML code for a team panel
	* @param object $team_info Team object
	* @param object[] $leaders_info table of person that are leaders of the team
	* @param object[] $thematics_info table of thematics informations of the team
	* @param string $language Display language
	* @param string $color Color of the team
	* @return string HTML code
	* @SuppressWarnings(PHPMD.ElseExpression)
	*/
	public function team_panel(
		/*object //PHP7.0 compatibility*/ $team_info,
		array $leaders_info,
		array $thematics_info,
		string $language,
		string $color = ''
		): string {

		$translate = new Translate( $language );
		$team_name = $this->_to_html( $translate->switch_lang( $team_info->name_en, $team_info->name_fr ) );

		//Generate HTML
		//------------
		$colorText = '';
		if ( ! empty( $color ) ) {
			$colorText = ' style="color:' . $color . ';"';
		}
		$a_afficher = '<h2' . $colorText . '>' . $team_name . '</h2>';
		$a_afficher .= '<div class="bandeau-equipe">';
		if ( ! empty( $team_info->color ) ) {
			$a_afficher .= "<div class='team-panel-color-block' style='background-color:" . $team_info->color . ";'></div>";

		}
		if ( ! empty( $team_info->logo ) ) {
			$a_afficher .= '<img src="' . wp_get_attachment_url( $team_info->logo ) .'" class="wp-image-6 alignleft img-arrondi wp-post-image" height="70" width="70" alt=" " />';
		}
		if ( count( $thematics_info ) > 0 ) {
			$a_afficher .= '<div class="thematics"><h1>';
			$thematics_translate = $translate->tr_n( 'Thematic', 'Thematics', count( $thematics_info ) );
			$a_afficher .= $this->_to_html( $thematics_translate ) . ' : </h1>';
			$thematics_list = array();
			foreach ( $thematics_info as $one_thematic ) {
				$thematic_name =  $this->_to_html( $translate->switch_lang( $one_thematic->name_en, $one_thematic->name_fr ) );
				$thematics_list[] = $this->_set_cliquable(
					$this->_get_url_of_object( $one_thematic ),
					$thematic_name
				);
			}
			$a_afficher .= implode( '<br />', $thematics_list ) .'</div>';
		}
		$MonLabo_leaders = new Persons_Group( $leaders_info );
		$MonLabo_leaders->suppress_non_visible();
		if ( $MonLabo_leaders->count() > 0 ) {
			$a_afficher .= '<h1>';
			$leaders_translate = $translate->tr_n( 'Team leader', 'Team leaders', $MonLabo_leaders->count() );
			$a_afficher .= $this->_to_html( $leaders_translate ) . ' : </h1>';
			$leaders_name = $this->persons_names( $MonLabo_leaders->get_persons(), 'with_link' );
			$a_afficher .= implode( '<br />', $leaders_name );
		}
		$a_afficher .= '</div>';
		return $a_afficher;
	}

	/**
	 * Get URL of an image from an image ID or URL (autodetect)
	 * @param string $id_or_url value that can be either an URL or a WordPress image ID
	 * @return string URL
	 */
	function image_from_id_or_url( string $id_or_url ): string {
		if ( $id_or_url === (string) abs( intval( $id_or_url ) ) ) {  //if id_or_url is an integer >0
			$image_arraystruct = wp_get_attachment_image_src( intval( $id_or_url ) );
			if ( isset( $image_arraystruct[0] ) ) {
				return $image_arraystruct[0];
			}
			return '';
		}
		return $id_or_url;
	}

	/**
	 * Get HTML code of an image flag
	 * @param string $lang language (fr or en)
	 * @return string img HTML code
	 */
	function get_translation_flag( string $lang ): string {
		if ( 'fr' === $lang ) {
			return "<img width='16' height='11' class='wp-image-8 wp-post-image' src='"
				 . plugin_dir_url( __DIR__  ) . "/../Admin/images/fr.png' alt='drapeau français' />";
		}
		return  "<img width='16' height='11' class='wp-image-8 wp-post-image' src='"
				. plugin_dir_url( __DIR__  ) . "/../Admin/images/en.png' alt='drapeau anglais' />";
	}
}
