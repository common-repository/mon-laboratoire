<?php
namespace MonLabo\Admin;
use MonLabo\Lib\{App, Translate, Options};
use MonLabo\Lib\Access_Data\{Access_Data};
use MonLabo\Frontend\{Html};
use MonLabo\Lib\Person_Or_Structure\{Person, Team, Unit, Thematic};

// MySQL host name, user name, password, database, and table
defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

/*
class Edit_Members {
	 __construct()
	_get_comment( $type, $field )
	_get_comment_for_person( $field )
	_get_comment_for_team( $field )
	_get_comment_for_thematic( $field )
	_get_comment_for_unit( $field )
	_get_comment_for_main_struct( $field )
	_vadidation( $item_type, $creation_mode )
	_generate_generic_fieldset( $fieldset_type, $item_type, $item_object, $ending_html = '' )
	_generate_start_of_edit_item_form( $item_type, $legend, $select_values, $initial_value )

	edit_person_form()
	edit_team_form()
	edit_thematic_form()
	edit_unit_form()
	edit_mainstruct_form()
}
*/
/**
 * Class \MonLabo\Admin\Edit_Members
 * @package
 */
class Edit_Members {

	/**
	 * Current instance of Html_Forms
	* @access protected
	* @var Html_Forms
	 */
	protected $_htmlForms = null;

	/**
	 * Current instance of Access_Data
	* @access protected
	* @var Access_Data
	 */
	protected $_accessData = null;

	/**
	 * Current instance of Html
	* @access protected
	* @var Html
	 */
	protected $_html = null;

	/**
	 * Create a new class
	 */
	function  __construct() {
		$this->_htmlForms = new Html_Forms();
		$this->_accessData = new Access_Data();
		$this->_html = new Html();
	}

	/**
	 * Get the comments of a form field in the edit Members admin interface
	 * @param string $type Form field type ('person', 'team', 'thematic', 'unit'...).
	 * @param string $field Form field name.
	 * @return string Comments
	 * @access private
	 */
	private function _get_comment( string $type, string $field ): string {
		switch ( $type ) {
			case 'person':
				return $this->_get_comment_for_person( $field );
			case 'team':
				return $this->_get_comment_for_team( $field );
			case 'thematic':
				return $this->_get_comment_for_thematic( $field );
			case 'unit':
				return $this->_get_comment_for_unit( $field );
			case 'main_struct':
				return $this->_get_comment_for_main_struct( $field );
			default:
				return '';
		}
	}

	/**
	 * Get the comments of a form field in the edit Members admin interface
	 * @param string $field Form field name.
	 * @return string Comments
	 * @access private
	 */
	private function _get_comment_for_person( string $field ): string {
		$options4 = get_option( 'MonLabo_settings_group4' );
		$fields_comments = array(
				'title' => '', //'Titre (ex: Pr., Dr.) affiché sur la page personnelle',
				'title_edit' => __( 'New title displayed before the name (ex: Pr., Dr.)', 'mon-laboratoire' ), //'Titre (ex: Pr., Dr.) affiché sur la page personnelle',
				'first_name' => '',
				'last_name' => '',
				'category' => '', //'Catégorie de personnel (faculty, postdocs, students, staff, visitors ou vide)',
				'fonction' => '',
				'function_en' => __( 'Employment, function of the person in English', 'mon-laboratoire' ),
				'function_fr' => __( 'Employment, function of the person in French', 'mon-laboratoire' ),
				'teams' => '', //'Numéro de l'équipe (plusieurs numéros possibles séparés par des virgules)',
				//'mentors' => __( 'Possibility to<br />select<br />several lines<br />with CTRL', 'mon-laboratoire' ), //'Page ID de l’encadrant d’un étudiant (plusieurs numéros possibles séparés par des virgules)',
				'mentors' => '',
				'external_mentors' => __( 'One person per line.', 'mon-laboratoire' ). '<br/>' . __( 'A link is possible after a coma:', 'mon-laboratoire' ) . '<br /><i>'. __('NAME,URL', 'mon-laboratoire' ) .'</i><br/>',
				//'students' => __( 'Possibility to<br />select<br />several lines<br />with CTRL', 'mon-laboratoire' ), //'Page ID de l’encadrant d’un étudiant (plusieurs numéros possibles séparés par des virgules)',
				'students' => '',
				'external_students' => __( 'One person per line.', 'mon-laboratoire' ). '<br/>' . __( 'A link is possible after a coma:', 'mon-laboratoire' ) . '<br /><i>'. __('NAME,URL', 'mon-laboratoire' ) .'</i><br/>',
				'mail' => '',
				'phone' => __( 'Telephone extension (enter a number starting with + so that the prefix is not used)', 'mon-laboratoire' ),
				'room' => '', //'Numéro de porte',
				'address_alt' => __( 'Possible address of replacement for the structure.', 'mon-laboratoire' ),
				'external_url' => '', //'Eventuel site personnel de l’utilisateur à faire apparaitre sur la page perso',
				'image' => __( 'Photo', 'mon-laboratoire' ),
				'descartes_publi_author_id' => sprintf(
					__( 'Author number in the Descartes Publi database in order to be able to display the publications (%s list available here %s)', 'mon-laboratoire' )
					, "<a href='" . $options4['MonLabo_DescartesPubmed_api_url'] . "?html_userslist' target=\"_blank\" rel=\"noopener noreferrer\">"
					, "</a>"),
				'hal_publi_author_id' => sprintf(
					__( 'IdHal (ex: annie-malduzoo) to be able to display publications (%slist available here%s)', 'mon-laboratoire' )
					, "<a href='https://aurehal.archives-ouvertes.fr/person/index' target=\"_blank\" rel=\"noopener noreferrer\" >"
					, '</a>' ),
				'uid_ENT_parisdescartes' => __( 'ParisDescartes user name (can be used as login)', 'mon-laboratoire' ),
				'date_departure' => __( 'Date or year when the person left the structure (useful for former members)', 'mon-laboratoire' ),
				'status' => '', //'actif ou ancien membre',
				'visible' => '' //'Mettre 'non' pour que cette personne n’apparaisse pas sur le site.'
			);
		if ( array_key_exists( $field, $fields_comments ) ) {
			return $fields_comments[ $field ];
		}
		return '';
	}

	/**
	 * Get the comments of a form field in the edit Teams admin interface
	 * @param string $field Form field name.
	 * @return string Comments
	 * @access private
	 */
	private function _get_comment_for_team( string $field ): string {
		$options4 = get_option( 'MonLabo_settings_group4' );
		$fields_comments = array(
		'id' => __( 'Identifier number of the team that automatically increments itself', 'mon-laboratoire' ),
		'name_en' => __( 'Name of the team in English',  'mon-laboratoire' ),
		'name_fr' => __( 'Name of the team in French',  'mon-laboratoire' ),
		'descartes_publi_team_id' => sprintf(
			__( 'Team number in the Descartes Publi database to be able to display the publications (%s list available here %s)', 'mon-laboratoire' )
			, "<a href='" . $options4['MonLabo_DescartesPubmed_api_url'] . "?html_teamslist' target=\"_blank\" rel=\"noopener noreferrer\">"
			, "</a>" ),
			'hal_publi_team_id' => sprintf(
				__( 'HAL structure identifier to be able to display the publications (numbers separated by commas) (%slist available here%s) If empty, MonLabo will use the IdHal of each member of the team.', 'mon-laboratoire' )
				, "<a href='https://aurehal.archives-ouvertes.fr/structure/index' target=\"_blank\" rel=\"noopener noreferrer\">"
				, '</a>' ),				
		'leaders' => __( 'Names of the team leaders (add as a member first)', 'mon-laboratoire' ),
		'members' => __( 'Names of the team members', 'mon-laboratoire' ),
		'alumni' => '',
		'id_unit' => '',
		'logo' => __( 'Logo <small>(by default it will be the logo of the unit)</small>', 'mon-laboratoire' ),
		'color' => __( 'Team color (used in[members_chart],[members_table] and[team_panel])', 'mon-laboratoire' ),
		'thematics' => __( 'List of groups to which the team belongs (multiple selection)', 'mon-laboratoire' )
		);
		if ( array_key_exists( $field, $fields_comments ) ) {
			return $fields_comments[ $field ];
		}
		return '';
	}

	/**
	 * Get the comments of a form field in the edit Thematic admin interface
	 * @param string $field Form field name.
	 * @return string Comments
	 * @access private
	 */
	private function _get_comment_for_thematic( string $field ): string {
		$fields_comments = array(
		'id' => __( 'Identifier number of the group of teams that automatically increases', 'mon-laboratoire' ),
		'name_fr' => __( 'French name of the team group', 'mon-laboratoire' ),
		'name_en' => __( 'English name of the team group', 'mon-laboratoire' ),
		'logo' => __( 'Logo', 'mon-laboratoire' ),
		'hal_publi_thematic_id' => sprintf(
				__( 'HAL structure identifier to be able to display publications (several possible numbers separated by commas) (%slist available here%s)', 'mon-laboratoire' )
				, "<a href='https://aurehal.archives-ouvertes.fr/structure/index' target=\"_blank\" rel=\"noopener noreferrer\">"
				, '</a>' ),
		);
		if ( array_key_exists( $field, $fields_comments ) ) {
			return $fields_comments[ $field ];
		}
		return '';
	}

	/**
	 * Get the comments of a form field in the edit Unite admin interface
	 * @param string $field Form field name.
	 * @return string Comments
	 * @access private
	 */
	private function _get_comment_for_unit( string $field ): string {
		$options4 = get_option( 'MonLabo_settings_group4' );
		$fields_comments = array(
		'id' => __( 'Identifier number of the unit that automatically increases', 'mon-laboratoire' ),
		'affiliations' => __( 'Laboratory affiliation structures', 'mon-laboratoire' ),
		'code' => __( 'Code of the lab (ex: CNRS UMR 8119)', 'mon-laboratoire' ),
		'name_fr' => __( 'Name of laboratory in French', 'mon-laboratoire' ),
		'name_en' => __( 'Name of laboratory in English', 'mon-laboratoire' ),
		'directors' => __( 'Name of the laboratory director (s)', 'mon-laboratoire' ),
		'descartes_publi_unit_id' => sprintf(
				__( 'Number of unit in the Descartes Publi database to be able to display the publications (%s list viewable here %s)', 'mon-laboratoire' )
				, "<a href='" . $options4['MonLabo_DescartesPubmed_api_url'] . "?html_unitlist' target=\"_blank\" rel=\"noopener noreferrer\">"
				, "</a>" ),
		'hal_publi_unit_id' => sprintf(
				__( 'HAL structure identifier to be able to display the publications (numbers separated by commas) (%s list viewable here %s) If empty, MyLabo will use the idHal of each member of the unit.', 'mon-laboratoire' )
				, "<a href='https://aurehal.archives-ouvertes.fr/structure/index' target=\"_blank\" rel=\"noopener noreferrer\">"
				, "</a>" ),
		'logo' => __( 'Logo', 'mon-laboratoire' ),
		'address_alt' => __( 'Alternative address (if different from the address of the main structure)', 'mon-laboratoire' ),
		'contact_alt' => __( 'Alternate contact by email, telephone, fax or other means (if different from the main contact)', 'mon-laboratoire' )
		);
		if ( array_key_exists( $field, $fields_comments ) ) {
			return $fields_comments[ $field ];
		}
		return '';
	}


	/**
	 * Get the comments of a form field in the edit main structure admin interface
	 * @param string $field Form field name.
	 * @return string Comments
	 * @access private
	 */
	private function _get_comment_for_main_struct( string $field ): string {
		$fields_comments = array(
			'nom' => __( 'Name of the main structure<br /> (ex: <em>Centre on the study of teleportation and bread crumbs</em>).', 'mon-laboratoire' ),
			'code' => __( 'Code of this structure<br />(ex: <em>UMR 666</em>).', 'mon-laboratoire' ),
			'adresse' => __( 'Address of this structure<br />(ex: <em>University Gizmo<br>160 End-of-path road<br />00000 Nowhere-on-earth<br />France</em>).', 'mon-laboratoire' ),
			'prefixe_tel' => __( 'Phone prefix of the structure<br />(ex: <em>+33 1 42 86 </em>).', 'mon-laboratoire' ),
			'contact' => __( 'Contact by email, phone, fax or other means<br />(ex: <em>Fax : +33 (0) 1 42 86 20 80</em>).', 'mon-laboratoire' ),
			'hal_publi_struct_id' => sprintf(
				__( 'HAL structure identifier to be able to display publications (several possible numbers separated by commas) (%slist available here%s)', 'mon-laboratoire' )
				, "<a href='https://aurehal.archives-ouvertes.fr/structure/index' target=\"_blank\" rel=\"noopener noreferrer\">"
				, '</a>' ),
			'directors' => __( 'Names of the structure\'s directors', 'mon-laboratoire' ),
		);
		if ( array_key_exists( $field, $fields_comments ) ) {
			return $fields_comments[ $field ];
		}
		return '';
	}

	/**
	 * Generate buttons for validation form
	 * @param string $item_type 'person', 'team', 'thematic' or 'unit'
	 * @param bool $creation_mode true for new item, false for modification
	 * @return string HTML code
	 * @access private
	 */
	private function _vadidation( string $item_type, bool $creation_mode ) : string {
		//For security
		$retval = wp_nonce_field( 'edit_' . $item_type . '_form', 'edit_' . $item_type . '_form_wpnonce', true, false );
		//Validation buttons
		$onclick = "submitForm( 'form_edit_$item_type', 'edit' )";
		if ( $creation_mode ) {
			$retval .= $this->_htmlForms->submit_button( __( 'Create', 'mon-laboratoire' ), 'submit_new_' . $item_type, $onclick, 'insert' );
			return $retval;
		}
		$retval .= $this->_htmlForms->submit_button( __( 'Modify', 'mon-laboratoire' ), 'submit_edit_' . $item_type, $onclick, 'edit', 'warning' );
		$onclick = "submitForm( 'form_edit_$item_type', 'remove' )";
		$retval .= ' '. $this->_htmlForms->submit_button( __( 'Delete', 'mon-laboratoire' ), 'submit_delete_' . $item_type, $onclick, 'trash', 'danger' );
		return $retval;
	}

	/**
	 * Generate prepared fiedset information sets
	 * @param string $fieldset_type 'informations', 'pages', or 'apparence'
	 * @param string $item_type 'person', 'team', 'thematic' or 'unit'
	 * @param Person|Team|Thematic|Unit $item_object Person_or_structure object
	 * @param string $ending_html optional code to add ad the end of fielfset
	 * @return string HTML code
	 * @access private
	 */
	private function _generate_generic_fieldset( string $fieldset_type, string $item_type, /*object //PHP7.0 */ $item_object, string $ending_html = '' ) : string {
		$retval = '';
		switch ( $fieldset_type ) {
			case 'informations':
				$retval .= '<fieldset><legend>' . __( 'Information:', 'mon-laboratoire' ) . '</legend>';
				$retval .= '<input type="hidden" name="submit_id" value="' . $item_object->info->id . '" />';
				$retval .= '<input type="hidden" name="action" id="action" value="" />';
				if ( ! $item_object->is_empty() ) {
					$retval .= 'Id : ' . $item_object->info->id . '<br />';
				}
				$retval .= $this->_htmlForms->field( 'name_fr', true, __( 'Name in French', 'mon-laboratoire' ), $this->_get_comment( $item_type, 'name_fr' ),
					$item_object->info->name_fr );
				$retval .= $this->_htmlForms->field( 'name_en', true, __( 'Name in English', 'mon-laboratoire' ), $this->_get_comment( $item_type, 'name_en' ),
					$item_object->info->name_en );
				break;

			case 'pages':
				$fieldname = ( 'person' === $item_type ? 'person_wp_post_ids' : 'wp_post_ids' );
				$messages = new Messages();
				$retval .= $messages->warning_if_necessary_unconfigured_parent( $item_type );
				$retval .= '<fieldset class=""><legend>' . __( 'Pages:', 'mon-laboratoire' ) . '</legend>';
				$retval .= $this->_htmlForms->field( $fieldname, false, __( 'Page ID or ext. URL.', 'mon-laboratoire' ),
					$this->_get_comment( $item_type, 'wp_post_ids' ), $item_object->info->wp_post_ids );
				break;

			case 'apparence':
				$retval .= '<fieldset class="clear"><legend>' . __( 'Appearance:', 'mon-laboratoire' ) . '</legend>';
				$retval .= $this->_htmlForms->field( 'logo', false, __( 'Logo', 'mon-laboratoire' ),
					$this->_get_comment( $item_type, 'logo' ), $item_object->info->logo );
				break;

			case 'publications':
				$options = Options::getInstance();
				if ( 'aucun' != $options->publication_server_type ) {
					$retval .= '<fieldset><legend>' . __( 'Publications:', 'mon-laboratoire' ) . '</legend>';
					$field1 = ( 'person' === $item_type ? 'author' : $item_type );
					if ( ( 'hal' === $options->publication_server_type ) or ( 'both' === $options->publication_server_type ) ) {
						$field = 'hal_publi_' . $field1 . '_id';
						$placeholder =  ( 'person' === $item_type ? __( 'IdHAL', 'mon-laboratoire' ) : __( 'struct. Id', 'mon-laboratoire' ) );
						$retval .= $this->_htmlForms->field( $field, false,  $placeholder,
							$this->_get_comment( $item_type, $field ), $item_object->info->{$field} );
					}
					if ( ( 'DescartesPubli' === $options->publication_server_type ) or ( 'both' === $options->publication_server_type ) ) {
						$field = 'descartes_publi_' . $field1 . '_id';
						$retval .= $this->_htmlForms->field( $field, false, __( 'Descartes Publi Id', 'mon-laboratoire' ),
							$this->_get_comment( $item_type, $field ), $item_object->info->{$field} );
					}
				}
				break;

			default:
				return '';
		}
		$retval .= $ending_html . '</fieldset>';
		return $retval;
	}

	/**
	 * Generate a select form to choose the item ID to edit
	 * @param string $item_type 'person', 'team', 'thematic', or 'unit'
	 * @param string $legend legend to print
	 * @param array<string|int,string>|array<string|int,array<string,string>> $select_values Select form list of values
	 * @param int $initial_value Initial value of the select form
	 * @return string HTML code
	 * @access private
	 */
	private function _generate_start_of_edit_item_form( string $item_type, string $legend, array $select_values, int $initial_value ) : string {
		$myurl = admin_url( 'admin.php?page=MonLabo_edit_members_and_groups&tab=tab_'. $item_type );
		$isMandatory = true;
		$description = '';
		$onchange='changeItem(\'' . $item_type . '\',this.value)';
		$retval = '<form class="navbar-form" id="choice_' . $item_type . '" method="post" action="' . $myurl . '&lang=all">'
					.'<div class="form-group">';
		$retval .= '<fieldset><legend>' . __( 'Selection:', 'mon-laboratoire' ) . '</legend>';
		$retval .= $this->_htmlForms->select( 'item', $select_values, $isMandatory, $legend, $description, strval( $initial_value ), $onchange );
		$retval .= '</fieldset></div>';
		$retval .= '</form>';
		$retval .= '<hr />';
		$retval .= '  <form class="navbar-form form_edit_item" id="form_edit_' . $item_type . '" accept-charset="utf-8" method="post" '
			.'enctype="multipart/form-data" action="' . $myurl . '&submit_item=' . $initial_value . '&lang=all">'
			.'<div class="form-group">';
		return $retval;
	}

	/**
	 * Generate form for editing a person
	 * @return string HTML code
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	function edit_person_form(): string {
		$formsProcessing = new Forms_Processing();
		$options = Options::getInstance();
		
		list( $retval, $member_id, ) = $formsProcessing->form_edit_person_processing();

		$members_name_actif = $this->_html->persons_names( $this->_accessData->get_persons_info( 'actif' ), 'simple_text' );
		$members_name_alumni = $this->_html->persons_names( $this->_accessData->get_persons_info( 'alumni' ), 'simple_text' );
		if ( ! empty( $members_name_actif ) )  { asort( $members_name_actif, SORT_STRING ); }
		if ( ! empty( $members_name_alumni ) ) { asort( $members_name_alumni, SORT_STRING ); }

		//Get member infomation. If invalid of new, return an empty object
		$member = new Person('from_id', $member_id );
		$new_member_txt = '&mdash; ' . __( 'New member', 'mon-laboratoire' ) . ' &mdash;';

		// formulaire choix du membre à éditer + debut du formulaire d'édition de membre
		$values = array( '0' => $new_member_txt, 'Actifs' => $members_name_actif , 'Alumni' => $members_name_alumni );
		$retval .= $this->_generate_start_of_edit_item_form( 'person', __( 'Member', 'mon-laboratoire' ), $values, $member->info->id );

		// Identité
		//---------
		$retval .= '<fieldset class="clear"><legend>' . __( 'Identity:', 'mon-laboratoire' ) . '</legend>';
		$retval .= '<input type="hidden" name="submit_id" value="' . $member->info->id . '" />';
		$retval .= '<input type="hidden" name="action" id="action" value="" />';
		if ( ! $member->is_empty() ) {
			$retval .= 'Id : ' . $member->info->id . '<br />';
		}
		$onchange = 'touchPersonTitle()';
		$tiles_array = $this->_accessData->get_persons_titles();
		$tiles_array['disabled'] = '--------';
		$tiles_array['edit'] =  __( 'Edit / New', 'mon-laboratoire' );
		$retval .= $this->_htmlForms->select( 'title'
			, $tiles_array
			//, array( ''=>'&nbsp;', 'Pr.'=>'Pr.', 'Dr.'=>'Dr.', 'disabled' => '--------', 'edit' => __( 'Edit / New', 'mon-laboratoire' ) )
			, false
			, _x( 'Title', 'titre honnorifique', 'mon-laboratoire' )
			, $this->_get_comment( 'person', 'title' )
			, $member->info->title
			, $onchange );
		$retval .= $this->_htmlForms->field( 'first_name', true, _x( 'First name', 'personne', 'mon-laboratoire' ), $this->_get_comment( 'person', 'first_name' ),
			$member->info->first_name );
		$retval .= $this->_htmlForms->field( 'last_name', true, _x( 'Name', 'personne', 'mon-laboratoire' ), $this->_get_comment( 'person', 'last_name' ),
			$member->info->last_name );
		$retval .= $this->_htmlForms->field( 'image', false, __( 'Personal photo', 'mon-laboratoire' ), $this->_get_comment( 'person', 'image' ), $member->info->image );

		$retval .= '<br /><div id="edit-person-title-field">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$retval .= $this->_htmlForms->field( 'title_edit', false,
			_x( 'Title', 'personne', 'mon-laboratoire' ), $this->_get_comment( 'person', 'title_edit' ),
			$member->info->title
		);
		$retval .= '</div>';

		$retval .= '</fieldset>';

		// Pages
		//---------
		$retval .= $this->_generate_generic_fieldset( 'pages' , 'person', $member );

		// Propriété
		//---------
		$retval .= '<fieldset class="clear"><legend>' . __( 'Properties:', 'mon-laboratoire' ) . '</legend>';

		//Dans le cas où il n’y a qu’une unité, configurer si la personne en est ou pas le directeur
		if ( !$options->uses['units'] ) {
			$textDirector = __( '(Co-)director of the unit', 'mon-laboratoire' );
			$those_directors = $this->_accessData->get_directors_id_for_an_unit( App::MAIN_STRUCT_NO_UNIT, 'all' );
			$initial_textDirector = null;
			if ( ! empty( $those_directors ) and ( in_array( $member->info->id, $those_directors ) ) ) {
				$initial_textDirector = array( '0' => $textDirector );
			}
			$retval .= $this->_htmlForms->checkboxes( 'is_director', array( 0=>$textDirector ), true, __( 'Direction?', 'mon-laboratoire' ), '', $initial_textDirector );
		}

		$retval .= '<br />';
		$member->info->category=mb_strtolower( $member->info->category, 'UTF-8' ); //On converti la catégorie en minuscule ( pour une compatibilité avec les anciennes versions de MonLabo )
		$retval .= $this->_htmlForms->select( 'category', App::get_MonLabo_persons_categories(), true, _x( 'Category and function', 'personne', 'mon-laboratoire' ), $this->_get_comment( 'person', 'category' ),
													$member->info->category );
		$onchange = 'touchPersonFunction()';
		$retval .= $this->_htmlForms->select(
						'fonction',
						$this->_accessData->get_multilingual_functions_by_category(),
						true,
						'',
						$this->_get_comment( 'person', 'fonction' ),
						$member->info->category . ' | ' . $member->info->function_en . ' | ' . $member->info->function_fr, $onchange
					);
		$retval .= '<input id="edition_fonctions_button" class="button" value="' . __( 'Edit / New', 'mon-laboratoire' ) . '" onclick="displaysIdField(\'edit-functions-fields\');" type="button">';
		$retval .= '<br /><div id="edit-functions-fields">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
		$retval .= $this->_htmlForms->field( 'function_en', true, _x( 'New function (English)', 'personne', 'mon-laboratoire' ), $this->_get_comment( 'person', 'function_en' ),
												$member->info->function_en );
		$retval .= $this->_htmlForms->field( 'function_fr', true, _x( 'New function (French)', 'personne', 'mon-laboratoire' ), $this->_get_comment( 'person', 'function_fr' ),
												$member->info->function_fr );
		$retval .= '<br /></div>';
		$all_teams_name = $this->_accessData->get_teams_name( 'fr' );
		if ( $member->is_empty() ) {
			$teams_name = array();
		} else {
			$teams_name = $this->_accessData->get_teams_name_for_a_person( $member->info->id, 'fr' );
		}
		$retval .= $this->_htmlForms->checkboxes( 'teams', $all_teams_name, true, __( 'Teams', 'mon-laboratoire' ), $this->_get_comment( 'person', 'teams' ),
		$teams_name );

		//choix des tuteurs
		$retval .= '<br />';
		$mentors_id = array();
		if ( ! $member->is_empty() ) {
			$mentors_id = $this->_accessData->get_mentors_id_for_a_person( $member->info->id, 'all' );
		}
		unset( $members_name_actif[ $member->info->id ] );  // on ne peut pas être tuteur de soi-même
		unset( $members_name_alumni[ $member->info->id ] ); // même si on est alumni
		$values = array( 'Actifs' => $members_name_actif , 'Alumni' => $members_name_alumni );
		$id_mentors_field = 'mentors_fields';
		if ( empty( $member->info->external_mentors ) and empty( $mentors_id ) ) {
			$retval .= '<input id="mentors_button" class="button" value="' . __( '+ Mentor', 'mon-laboratoire' ) . '" onclick="toggleIdField(\'mentors-fields-invisible\', \'mentors_button\');" type="button">';
			$id_mentors_field ='mentors-fields-invisible';
		}
		$retval .= '<div id="' . $id_mentors_field . '">';
		$retval .= $this->_htmlForms->select_multiple( 'mentors', $values, false, __( '+ Mentors', 'mon-laboratoire' ), $this->_get_comment( 'person', 'mentors' ),
			$mentors_id );
		$retval .= '<input id="external_mentors_button" class="button" value="' . __( '+ External persons', 'mon-laboratoire' ) . '" onclick="displaysIdField(\'external-mentors-invisible\');" type="button">';
		$css_id='';
		if ( empty( $member->info->external_mentors ) ) {
			$css_id='external-mentors-invisible';
		}
		$retval .= $this->_htmlForms->field( 'external_mentors', false, __( 'External mentors', 'mon-laboratoire' ), $this->_get_comment( 'person', 'external_mentors' ),
			$member->info->external_mentors, $css_id );
		$retval .= '</div>';

		//choix des étudiants
		$retval .= '<br />';
		$students_id = array();
		if ( ! $member->is_empty() ) {
			$students_id = $this->_accessData->get_students_id_for_a_person( $member->info->id, 'all' );
		}
		unset( $members_name_actif[ $member->info->id ] ); // on ne peut pas être étudiant de soi-même
		unset( $members_name_alumni[ $member->info->id ] ); // même si on est alumni
		$values = array( 'Actifs' => $members_name_actif , 'Alumni' => $members_name_alumni );
		$id_students_field =' students_fields';
		if ( empty( $member->info->external_students ) and empty( $students_id ) ) {
			$retval .= '<input id="students_button" class="button" value="' . __( '+ Student', 'mon-laboratoire' ) . '" onclick="toggleIdField(\'students-fields-invisible\', \'students_button\');" type="button">';
			$id_students_field ='students-fields-invisible';
		}
		$retval .= '<div id="' . $id_students_field . '">';
		$retval .= $this->_htmlForms->select_multiple( 'students', $values, false, __( 'Students', 'mon-laboratoire' ), $this->_get_comment( 'person', 'students' ),
			$students_id );
		$retval .= '<input id="external_students_button" class="button" value="' . __( '+ External persons', 'mon-laboratoire' ) . '" onclick="displaysIdField(\'external-students-invisible\');" type="button">';
		$css_id='';
		if ( empty( $member->info->external_students ) ) {
			$css_id='external-students-invisible';
		}
		$retval .= $this->_htmlForms->field( 'external_students', false, __( 'External students', 'mon-laboratoire' ), $this->_get_comment( 'person', 'external_students' ),
			$member->info->external_students, $css_id );
		$retval .= '</div></fieldset>';

		// Coordonnées
		//------------
		$retval .= '<fieldset><legend>' . _x( 'Coordinates:', 'personne', 'mon-laboratoire' ) . '</legend>';
		$retval .= $this->_htmlForms->field( 'mail', false, __( 'E-mail(s)', 'mon-laboratoire' ), $this->_get_comment( 'person', 'mail' ),
			$member->info->mail );
		$retval .= $this->_htmlForms->field( 'room', false, __( 'Door', 'mon-laboratoire' ), $this->_get_comment( 'person', 'room' ),
			$member->info->room );
		$retval .= $this->_htmlForms->field( 'external_url', false, __( 'Personal external website', 'mon-laboratoire' ) , $this->_get_comment( 'person', 'external_url' ),
			$member->info->external_url );
		$retval .= $this->_htmlForms->field( 'phone', false, __( 'Phone extension number', 'mon-laboratoire' ), $this->_get_comment( 'person', 'phone' ),
			$member->info->phone );
		$retval .= '<br />';
		$retval .= $this->_htmlForms->field( 'address_alt', false, __( 'Alternative address', 'mon-laboratoire' ), $this->_get_comment( 'person', 'address_alt' ),
			$member->info->address_alt );
		$retval .= '</fieldset>';

		// Publications
		//---------------
		$retval .= $this->_generate_generic_fieldset( 'publications' , 'person', $member );

		// Etat
		//------------
		//$retval .= '<pre>' . var_export( $member->info, true ) . '</pre>';
		$retval .= '<fieldset><legend>' . _x( 'Situation:', 'personne', 'mon-laboratoire' ) . '</legend>';
		$retval .= $this->_htmlForms->select( 'status', array( 'actif'=>__( 'active', 'mon-laboratoire' ), 'alumni'=>__( 'former member', 'mon-laboratoire' ) ), true, _x( 'Status', 'personne', 'mon-laboratoire' ),
			$this->_get_comment( 'person', 'status' ), $member->info->status );

		$retval .= "<div id='MonLabo-date-departure-form'>" . $this->_htmlForms->field( 'date_departure', false, __( 'Departure date from the unit', 'mon-laboratoire' ), $this->_get_comment( 'person', 'date_departure' ),
												$member->info->date_departure ) . '</div>';
		$retval .= $this->_htmlForms->select( 'visible', array( 'oui'=>__( 'yes', 'mon-laboratoire' ), 'non'=>__( 'no', 'mon-laboratoire' ) ), true, __( 'Visible person?', 'mon-laboratoire' ),
			$this->_get_comment( 'person', 'visible' ), $member->info->visible );
		$retval .= '</fieldset>';

		// Custtom fiels
		//--------------
		if ( $options->uses['custom_fields'] ) {
			$retval .= '<fieldset><legend>' . __( 'Custom fields:', 'mon-laboratoire' ) . '</legend>';
			$options3 = get_option( 'MonLabo_settings_group3' );
			if ( $options3['MonLabo_custom_fields_number']<'1' ) {
				$nb_fields = 0;
			} elseif ( $options3['MonLabo_custom_fields_number']>'10' ) {
				$nb_fields = 10;
			} else {
				$nb_fields = intval( $options3['MonLabo_custom_fields_number'] );
			}
			for ( $i=1; $i <= $nb_fields; $i++ ) {
				$nom_variable = 'custom' . $i;
				$retval .= $this->_htmlForms->field( $nom_variable, false, $nom_variable,
											'custom_' . $options3['MonLabo_custom_field' . $i . '_title'], $member->info->{$nom_variable} );
			}
			$retval .= '</fieldset>';
		}

		// Validation
		//------------
		$retval .=  $this->_vadidation( 'person', ( 0 === $member->info->id ) );
		$retval .= '</div></form>';
		return $retval;
	}

	/**
	 * Generate form for editing a team
	 * @return string HTML code
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	function edit_team_form(): string {
		$options = Options::getInstance();
		$formsProcessing = new Forms_Processing();

		list( $retval, $team_id, ) = $formsProcessing->form_edit_team_processing();

		$teams_name = $this->_accessData->get_teams_name( 'fr' );
		$new_team_txt = '&mdash; ' . __( 'New team', 'mon-laboratoire' ) . ' &mdash;';
		$teams_name = array( '0' => $new_team_txt ) + $teams_name;

		//Get team infomation. If invalid of new, return an empty object
		$team = new Team( 'from_id', (int) $team_id );

		// formulaire choix de l’équipe à éditer + debut du formulaire d'édition de l’équipe
		$retval .= $this->_generate_start_of_edit_item_form( 'team', __( 'Team', 'mon-laboratoire' ), $teams_name, $team->info->id );

		// Informations
		//-------------
		$retval .= $this->_generate_generic_fieldset( 'informations' , 'team', $team );

		// Pages
		//----------
		$retval .= $this->_generate_generic_fieldset( 'pages' , 'team', $team );

		// Apparence
		//----------
		$end_of_fieldset2 = '<div class="input-group"><label for="">' . __( 'Color', 'mon-laboratoire' ) . ' : </label>';
		$end_of_fieldset2 .= $this->_htmlForms->field( 'color', false, __( 'Color', 'mon-laboratoire' ), $this->_get_comment( 'team', 'color' ), $team->info->color );
		$end_of_fieldset2 .= '</div>';
		$retval .= $this->_generate_generic_fieldset( 'apparence' , 'team', $team, $end_of_fieldset2 );

		// Appartenance
		//--------------
		$retval .= '<fieldset><legend>' . __( 'Belonging:', 'mon-laboratoire' ) . '</legend>';
		$units_name = array();
		if ( $options->uses['units'] ) {
			$units_name = $this->_accessData->get_units_name( 'fr' );
			foreach ( $units_name as $unit_id => $unit_name ) {
				$units_name[ $unit_id ] = __( 'Unit', 'mon-laboratoire' ) . ' ' . $unit_id . ' : ' . $unit_name;
			}
		}
		$units_name[App::MAIN_STRUCT_NO_UNIT] = __( 'Main structure', 'mon-laboratoire' );
		if ( ( property_exists( $team->info, 'id_unit' ) ) and ( array_key_exists( $team->info->id_unit, $units_name ) ) ) {
			$id_unit_initval = $team->info->id_unit;
		} else {
			$id_unit_initval = App::MAIN_STRUCT_NO_UNIT;
		}
		$retval .= $this->_htmlForms->radio_buttons( 'id_unit', $units_name, true, __( 'Structure', 'mon-laboratoire' ), $this->_get_comment( 'team', 'id_unit' ), $units_name[ $id_unit_initval ] );
		if ( $options->uses['thematics'] ) {
			$all_thematics_name = $this->_accessData->get_thematics_name( 'fr' );
			$thematics_id = $this->_accessData->get_thematics_id_for_a_team( $team->info->id );
			$translate = new Translate();
			$retval .= $this->_htmlForms->select_multiple( 'thematics', $all_thematics_name, false,  $translate->tr__( 'Thematics' ),
				$this->_get_comment( 'team', 'thematics' ), $thematics_id );
		}
		$retval .= '</fieldset>';

		// Composition
		//-------------
		$retval .= '<fieldset><legend>' . __( 'Composition:', 'mon-laboratoire' ) . '</legend>';
		$members_name_actif = array();
		if ( ! $team->is_empty() ) {
			$members_name_actif = $this->_html->persons_names(
				$this->_accessData->get_persons_info_for_a_team( $team->info->id, 'actif' ), 'simple_text' );
		}
		$all_persons_name_actif = $this->_html->persons_names( $this->_accessData->get_persons_info( 'actif' ), 'simple_text' );
		$all_persons_name_alumni = $this->_html->persons_names( $this->_accessData->get_persons_info( 'alumni' ), 'simple_text' );
		if ( empty( $members_name_actif ) ) {
			$members_name_actif = $all_persons_name_actif;
		}
		$members_struct = array( 'Actifs' => $members_name_actif  );
		$all_persons_struct = array( 'Actifs' => $all_persons_name_actif , 'Alumni' => $all_persons_name_alumni );
		$leaders_id = array();
		$persons_id = array();
		//$alumni_id = array();
		if ( ! $team->is_empty() ) {
			$leaders_id = $this->_accessData->get_leaders_id_for_a_team( $team->info->id, 'all' );
			$persons_id = $this->_accessData->get_persons_id_for_a_team( $team->info->id, 'all' );
			//$alumni_id = $this->_accessData->get_persons_id_for_a_team( $team->info->id, 'alumni' );
		}
		$retval .= $this->_htmlForms->select_multiple( 'persons', $all_persons_struct, false, __( 'Persons', 'mon-laboratoire' ), $this->_get_comment( 'team', 'members' ), $persons_id );
		$retval .= $this->_htmlForms->select_multiple( 'leaders', $members_struct, false, __( 'Team leaders', 'mon-laboratoire' ), $this->_get_comment( 'team', 'leaders' ), $leaders_id );
		//$retval .= $this->_htmlForms->select_multiple( 'alumni', $all_persons_struct, false, __( 'Alumni', 'mon-laboratoire' ), $this->_get_comment( 'team', 'alumni' ), $alumni_id );
		$retval .= '</fieldset>';

		// Publications
		//--------------
		$retval .= $this->_generate_generic_fieldset( 'publications' , 'team', $team );

		// Validation
		//------------
		$retval .=  $this->_vadidation( 'team', $team->is_empty() );
		$retval .= '</div></form>';
		return $retval;
	}

	/**
	 * Generate form for editing a thematic
	 * @return string HTML code
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	function edit_thematic_form(): string {
		$formsProcessing = new Forms_Processing();

		list( $retval, $thematic_id, ) = $formsProcessing->form_edit_thematic_processing();

		$thematics_name = $this->_accessData->get_thematics_name( 'fr' );
		$new_thematic_txt = '&mdash; ' . __( 'New thematic', 'mon-laboratoire' ) . ' &mdash;';
		$thematics_name = array( '0' => $new_thematic_txt ) + $thematics_name;

		//Get thematic info. If invalid of new, return an empty object
		$thematic = new Thematic( 'from_id', (int) $thematic_id );

		// formulaire choix de la thematique à éditer + debut du formulaire d'édition de la thématique
		$translate = new Translate();
		$retval .= $this->_generate_start_of_edit_item_form( 'thematic', $translate->tr__( 'Thematic' ), $thematics_name, $thematic->info->id );

		// Informations, Pages et Apparence
		//---------------------------------
		$retval .= $this->_generate_generic_fieldset( 'informations', 'thematic', $thematic );
		$retval .= $this->_generate_generic_fieldset( 'pages' ,       'thematic', $thematic );
		$retval .= $this->_generate_generic_fieldset( 'apparence' ,   'thematic', $thematic );

		// Validation
		//------------
		$retval .=  $this->_vadidation( 'thematic', $thematic->is_empty() );
		$retval .= '</div></form>';
		return $retval;
	}

	/**
	 * Generate form for editing a unit
	 * @return string HTML code
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	function edit_unit_form(): string {
		$formsProcessing = new Forms_Processing();

		list( $retval, $unit_id, ) = $formsProcessing->form_edit_unit_processing();

		$units_name = $this->_accessData->get_units_name( 'fr' );
		$new_unit_txt = '&mdash; ' . __( 'New unit', 'mon-laboratoire' ) . ' &mdash;';
		$units_name = array( '0' => $new_unit_txt ) + $units_name;

		//Get unit info. If invalid of new, return an empty object
		$unit = new Unit( 'from_id', (int) $unit_id );

		// formulaire choix de l’unité à éditer + debut du formulaire d'édition de l’unité
		$retval .= $this->_generate_start_of_edit_item_form( 'unit', __( 'Unit', 'mon-laboratoire' ), $units_name, $unit->info->id );

		// Informations
		//-------------
		$end_of_fieldset = '<br />' . $this->_htmlForms->field( 'affiliations', true, 'Affiliations',
			$this->_get_comment( 'unit', 'affiliations' ), $unit->info->affiliations );
		$end_of_fieldset .= $this->_htmlForms->field( 'code', false, _x( 'Code', 'unité', 'mon-laboratoire' ), $this->_get_comment( 'unit', 'code' ),
		$unit->info->code );
		$end_of_fieldset .= '<br />';
		$members_name_actif = $this->_html->persons_names( $this->_accessData->get_persons_info( 'actif' ), 'simple_text' );
		$members_name_alumni = $this->_html->persons_names( $this->_accessData->get_persons_info( 'alumni' ), 'simple_text' );
		$values = array( 'Actifs' => $members_name_actif , 'Alumni' => $members_name_alumni );
		$directors_id = $this->_accessData->get_directors_id_for_an_unit( $unit->info->id, 'all' );
		$end_of_fieldset .= $this->_htmlForms->select_multiple( 'directors', $values, false, __( 'Directors', 'mon-laboratoire' ),
			$this->_get_comment( 'unit', 'directors' ), $directors_id );
		$retval .= $this->_generate_generic_fieldset( 'informations' , 'unit', $unit, $end_of_fieldset );

		// Pages, Apparence et publications
		//---------------------------------
		$retval .= $this->_generate_generic_fieldset( 'pages' ,        'unit', $unit );
		$retval .= $this->_generate_generic_fieldset( 'apparence' ,    'unit', $unit );
		$retval .= $this->_generate_generic_fieldset( 'publications' , 'unit', $unit );

		// Coordonnées alternatives
		//-------------------------
		$retval .= '<fieldset><legend>' . __( 'Alternative coordinates (if different from the main structure)', 'mon-laboratoire' ) . '</legend>';
		$retval .= $this->_htmlForms->field( 'address_alt', false, __( 'Alternative address', 'mon-laboratoire' ), $this->_get_comment( 'unit', 'address_alt' ),
			$unit->info->address_alt );
		$retval .= $this->_htmlForms->field( 'contact_alt', false, __( 'Alternate contact', 'mon-laboratoire' ), $this->_get_comment( 'unit', 'contact_alt' ),
			$unit->info->contact_alt );

		$retval .= '</fieldset>';

		// Validation
		//------------
		$retval .=  $this->_vadidation( 'unit', $unit->is_empty() );
		$retval .= '</div></form>';
		return $retval;
	}

	/**
	 * Form to edit main structure informations
	 * @return string HTML code
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	function edit_mainstruct_form(): string {
		$formsProcessing = new Forms_Processing();
		$retval = $formsProcessing->form_edit_mainstruct_processing();

		$options = Options::getInstance();
		$options1=get_option( 'MonLabo_settings_group1' );

		$myurl = admin_url( 'admin.php?page=MonLabo_edit_members_and_groups&tab=tab_mainstruct' );

		$retval .= '  <form class="navbar-form" id="form_edit_mainstruct" accept-charset="utf-8" method="post" '
			.'enctype="multipart/form-data" action="' . $myurl . '&lang=all">'
			.'<div class="form-group">';

		// Coordonnées
		//------------
		if ( $options->uses['units'] ) {
			$retval .= '<fieldset><legend>' . __( 'Coordinates of the structure that groups the laboratories:', 'mon-laboratoire' ) . '</legend>';
		} else {
			$retval .= '<fieldset><legend>' . __( 'Coordinates:', 'mon-laboratoire' ) . '</legend>';
		}
		//Si $options1['truc'] n’existe pas, y mettre une valeur par défaut
		$default_options=App::get_options_DEFAULT();
		foreach ( $default_options['MonLabo_settings_group1'] as $option_name => $option_value ) {
			if ( ! isset( $options1[ $option_name ] ) ) {
				$options1[ $option_name ]="$option_value";
			}
		}

		$retval .= $this->_htmlForms->field( 'nom', true, __( 'Name of the structure' , 'mon-laboratoire' ),
			$this->_get_comment( 'main_struct', 'nom' ), $options1['MonLabo_nom'] );
		$retval .= $this->_htmlForms->field( 'code', false, __( 'Code of the structure', 'mon-laboratoire' ),
			$this->_get_comment( 'main_struct', 'code' ), $options1['MonLabo_code'] );
		$retval .= '<br />' . $this->_htmlForms->field( 'adresse', false, __( 'Address of the structure', 'mon-laboratoire' ),
			$this->_get_comment( 'main_struct', 'adresse' ), $options1['MonLabo_adresse'] );
		$retval .= '<br />' . $this->_htmlForms->field( 'prefixe_tel', false, __( 'Phone prefix', 'mon-laboratoire' ),
			$this->_get_comment( 'main_struct', 'prefixe_tel' ), $options1['MonLabo_prefixe_tel'] );
		$retval .= '<br />' . $this->_htmlForms->field( 'contact', false, __( 'Mail, phone, fax or contact', 'mon-laboratoire' ),
			$this->_get_comment( 'main_struct', 'contact' ), $options1['MonLabo_contact'] );
		$retval .= '</fieldset>';

		// Publications
		//--------------
		if ( 'hal' === $options->publication_server_type ) {			
			$retval .= '<fieldset><legend>' . __( 'Publications:', 'mon-laboratoire' ) . '</legend>';
			$retval .= '<br />' . $this->_htmlForms->field( 'hal_publi_struct_id', false, __('struct. Id', 'mon-laboratoire'),
				$this->_get_comment( 'main_struct', 'hal_publi_struct_id' ), $options1['MonLabo_hal_publi_struct_id'] );
			$retval .= '</fieldset>';
		}

		// Direction
		//--------------
		$retval .= '<fieldset><legend>' . __( 'Direction:', 'mon-laboratoire' ) . '</legend>';
		$members_name_actif = $this->_html->persons_names( $this->_accessData->get_persons_info( 'actif' ), 'simple_text' );
		$members_name_alumni = $this->_html->persons_names( $this->_accessData->get_persons_info( 'alumni' ), 'simple_text' );
		$values = array( 'Actifs' => $members_name_actif , 'Alumni' => $members_name_alumni );
		$directors_id = $this->_accessData->get_directors_id_for_an_unit( App::MAIN_STRUCT_NO_UNIT, 'all' );
		$retval .= $this->_htmlForms->select_multiple( 'directors', $values, false, __( 'Directors', 'mon-laboratoire' ),
			$this->_get_comment( 'main_struct', 'directors' ), $directors_id );
		$retval .= '</fieldset>';

		//For security
		//------------
		$retval .= wp_nonce_field( 'edit_mainstruct_form', 'edit_mainstruct_form_wpnonce', true, false );

		// Validation
		//------------
		$retval .= $this->_htmlForms->submit_button( __( 'Modify', 'mon-laboratoire' ), 'submit_edit_mainstruct', '', 'edit', 'warning' );
		$retval .= '</div></form>';
		return $retval;
	}

}
?>
