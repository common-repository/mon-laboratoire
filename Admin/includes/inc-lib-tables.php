<?php

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

use MonLabo\Lib\{App, Lib, Options};
use MonLabo\Lib\Access_Data\{Access_Data};
use MonLabo\Lib\Person_Or_Structure\Groups\{Persons_Group, Teams_Group};
use MonLabo\Frontend\Html;

/**
 * Creates a list of admin links from table wp_post_ids
 * @param string[] $wp_post_ids tables of person, team, units... pages
 * @return string HTML code
 */
function _get_pages_links_from_wp_post_ids( array $wp_post_ids ) : string {
	$links = array();
	foreach ($wp_post_ids as $wp_post_id) {
		$url_action = $wp_post_id;
		if ( is_numeric( $wp_post_id ) ) {
			$url_action = get_edit_post_link( intval( $wp_post_id ) );
		}
		$links[] = '<a href="' .  $url_action . '">' . $wp_post_id . '</a>';
	}
	return Lib::secured_implode(', ', $links, );
}


/**
 * Creates the HTML code of a complete table created from the Information
 * of a group of people and intended to be displayed in the administration interface.
 * @param string $status status of the persons to be extracted
 * @return string HTML code
 * @SuppressWarnings(PHPMD.ElseExpression)
 */
function generate_table_admin_for_persons( string $status ): string {
	$html = new Html();
	$accessData = new Access_Data();
	$MonLaboPersons = new Persons_Group( $accessData->get_persons_info( $status ) );
	$MonLaboPersons->sort_by_name();
	$teams_info = $accessData->get_teams_info();
	$nb_custom_fields = 0;
	$options = Options::getInstance();
	if ( $options->uses['custom_fields'] ) {
		$options3 = get_option( 'MonLabo_settings_group3' );
		if ( $options3['MonLabo_custom_fields_number']<'1' ) { $nb_custom_fields = 0; }
		elseif ( $options3['MonLabo_custom_fields_number']>'10' ) { $nb_custom_fields = 10; }
		else { $nb_custom_fields = intval( $options3['MonLabo_custom_fields_number'] ); }
	}
	$colums_titles = array( '', _x( 'Name', 'personne', 'mon-laboratoire' ),
							_x( 'First name', 'personne', 'mon-laboratoire' ),
							__( 'Id', 'mon-laboratoire' ),
							_x( 'Title', 'personne', 'mon-laboratoire' ),
							__( 'Pages', 'mon-laboratoire' ),
							__( 'Photo', 'mon-laboratoire' ),
							_x( 'Category', 'personne', 'mon-laboratoire' ),
							_x( 'Function (fr)', 'personne', 'mon-laboratoire' ),
							_x( 'Function (en)', 'personne', 'mon-laboratoire' ),
							__( 'Email', 'mon-laboratoire' ),
							__( 'Phone', 'mon-laboratoire' ),
							__( 'Room', 'mon-laboratoire' ),
							__( 'Alt. addr.', 'mon-laboratoire' ),
							__( 'Ext. URL', 'mon-laboratoire' ),
							__( 'IdHAL', 'mon-laboratoire' ),
							__( 'Descartes Id', 'mon-laboratoire' ),
							__( 'Descartes login', 'mon-laboratoire' ),
							_x( 'Status', 'personne', 'mon-laboratoire' ),
							_x( 'Leave date', 'personne', 'mon-laboratoire' ),
							__( 'Visible', 'mon-laboratoire' ),
							__( 'Teams', 'mon-laboratoire' ),
							__( 'Mentors', 'mon-laboratoire' )
				);
	for ( $i = 1; $i <= $nb_custom_fields; $i++ ) {
		$colums_titles[] = 'custom' . $i;
	}
	$list_array = array();
	$a_afficher = '';
	if ( $MonLaboPersons->count() > 0 ) {
		foreach ( $MonLaboPersons->get_persons() as $person_info ) {
			$person_array = array();
			$person_array['modifier'] = '<form method="post" action="admin.php?page=MonLabo_edit_members_and_groups&tab=tab_person&submit_item=' . $person_info->id . '&lang=all">'
									 . '<button type="submit">' . __( 'Modify', 'mon-laboratoire' ) . '</button></form>';
			$person_array['last_name'] = $person_info->last_name;
			$person_array['first_name'] = $person_info->first_name;
			$person_array['id'] = $person_info->id;
			$person_array['title'] = $person_info->title;
			$person_array['wp_post_id'] = _get_pages_links_from_wp_post_ids( $person_info->wp_post_ids );
			$person_array['logo'] = '<a class="hover-zoom-square30">' . $html->person_thumbnail( $person_info ) . '</a>';
			$person_array['category'] = $person_info->category;
			$person_array['function_fr'] = '<small>' . $person_info->function_fr . '</small>';
			$person_array['function_en'] = '<small>' . $person_info->function_en . '</small>';
			$person_array['mail'] = '<small>' . $person_info->mail . '</small>';
			$person_array['phone'] = $person_info->phone;
			$person_array['room'] = $person_info->room;
			$person_array['address_alt'] = ( empty( $person_info->address_alt ) ? '' : '<small>' . $person_info->address_alt . '</small>' );
			$person_array['external_url'] = ( empty( $person_info->external_url ) ? '' : '<a href="' . $person_info->external_url . '">' . __( 'link', 'mon-laboratoire' ) . '</a>' );
			$person_array['hal_publi_author_id'] = $person_info->hal_publi_author_id;
			$person_array['descartes_publi_author_id'] = $person_info->descartes_publi_author_id;
			$person_array['uid_ENT_parisdescartes'] = $person_info->uid_ENT_parisdescartes;
			$person_array['status'] = $person_info->status;
			$person_array['date_departure'] = $person_info->date_departure;
			$person_array['visible'] = $person_info->visible;
			$teams_id = $accessData->get_teams_id_for_a_person( $person_info->id );
			$person_array['equipes'] = '';
			if ( ! empty( $teams_id ) ) {
				$teams_links = array();
				foreach ( $teams_id as $the_id ) {
					$team_title = '';
					if ( array_key_exists( $the_id, $teams_info ) ) {
						if ( property_exists( $teams_info[ $the_id ], 'name_fr' ) ) {
							$team_title = $teams_info[ $the_id ]->name_fr;
						}
					}
					$teams_links[] = "<a href='#' title='$team_title'>$the_id</a>";
				}
				$person_array['equipes'] = Lib::secured_implode( ', ', $teams_links );
			}
			$mentors_name = $html->persons_names(
				$accessData->get_mentors_info_for_a_person( $person_info->id ), 'with_admin_link' );
			$person_array['mentors'] = '';
			if ( ! empty( $mentors_name ) ) {
				$person_array['mentors'] = '<small>' . implode( ', ', $mentors_name ) . '</small>';
			}
			for ( $i = 1; $i <= $nb_custom_fields; $i++ ) {
				$nom_variable = 'custom' . $i;
				$person_array[ $nom_variable ] = $person_info->{$nom_variable};
			}
			$list_array[] = $person_array;
		}
		$a_afficher .= '<div class="MonLabo MonLabo-persons-table-normal">'
						 . $html->generic_table( '', '' , $colums_titles, $list_array )
						 . '</div>';
	}
	return $a_afficher;
}

/**
 * Creates the HTML code of a complete table created from the Information
 * of a group of teams and intended to be displayed in the administration interface.
 * @return string HTML code
 * @SuppressWarnings(PHPMD.ElseExpression)
 */
function generate_table_admin_for_teams(): string {

	$html = new Html();
	$accessData = new Access_Data();
	$MonLaboTeams = new Teams_Group( $accessData->get_teams_info() );
	//$MonLaboTeams->sort_by_name();
	$options = Options::getInstance();
	$options4 = get_option( 'MonLabo_settings_group4' );
	$colums_titles = array( '', __( 'Id', 'mon-laboratoire' ), __( 'Name (fr)', 'mon-laboratoire' ), __( 'Name (en)', 'mon-laboratoire' ), __( 'Logo', 'mon-laboratoire' ), __( 'Pages', 'mon-laboratoire' )
					, __( 'Struct. Id', 'mon-laboratoire' ), __( 'Descartes Id', 'mon-laboratoire' )
					, __( 'Color', 'mon-laboratoire' ), __( 'Leaders', 'mon-laboratoire' ), __( 'Unit', 'mon-laboratoire' )
				);
	if ( $options->uses['thematics'] ) {
		$colums_titles[]= 'Thématiques';
	}
	$list_array = array();
	$a_afficher = '';
	if ( $MonLaboTeams->count() > 0 ) {
		foreach ( $MonLaboTeams->get_teams() as $team_info ) {
			$team_array = array();
			$url_action = 'admin.php?' . http_build_query ( array(
														'page' => 'MonLabo_edit_members_and_groups',
														'tab' => 'tab_team',
														'submit_item' => $team_info->id,
														'lang' => 'all'
											) );
			$team_array['modifier'] = '<form method="post" action="' . $url_action . '">'
									 . '<button type="submit">' . __( 'Modify', 'mon-laboratoire' ) . '</button></form>';
			$team_array['id'] = $team_info->id;
			$team_array['name_fr'] = '<small>' . $team_info->name_fr . '</small>';
			$team_array['name_en'] = '<small>' . $team_info->name_en . '</small>';
			$team_array['logo'] = ( empty( $team_info->logo ) ? '' : '<a href="#" class="hover-zoom-square30"><img src="' . $html->image_from_id_or_url( $team_info->logo ) . '" height="24" alt="' . $team_info->logo . '" /></a>' );
			$team_array['wp_post_id'] = _get_pages_links_from_wp_post_ids( $team_info->wp_post_ids );
			$team_array['hal_publi_team_id'] = $team_info->hal_publi_team_id;
			$team_array['descartes_publi_team_id'] = '';
			if ( ! empty( $team_info->descartes_publi_team_id ) ) {
				$team_array['descartes_publi_team_id'] = "<a href='" . $options4['MonLabo_DescartesPubmed_api_url'] . '?equipe='. htmlspecialchars( $team_info->descartes_publi_team_id ) ."'>"
														 . $team_info->descartes_publi_team_id . '</a>';
			}
			$team_array['color'] = $team_info->color;
			$leaders_names = $html->persons_names(
				$accessData->get_leaders_info_for_a_team( $team_info->id ), 'with_admin_link');
			$team_array['leaders'] = Lib::secured_implode( ', ', $leaders_names );

			if ( App::MAIN_STRUCT_NO_UNIT == $team_info->id_unit ) {
				$team_array['id_unit'] = __( 'Main structure', 'mon-laboratoire' );
			} else {
				$team_array['id_unit'] = $team_info->id_unit;
			}

			if ( $options->uses['thematics'] ) {
				$thematics_info = $accessData->get_thematics_info_for_a_team( $team_info->id );
				$team_array['thematics'] = '';
				if ( ! empty( $thematics_info ) ) {
					$thematics_links = array();
					foreach ( $thematics_info as $thematic ) {
						$thematics_links[] = "<a href='#' title='" . $thematic->name_fr . "'>$thematic->id</a>";
					}
					$team_array['thematics'] = Lib::secured_implode( ', ', $thematics_links );
				}
			}
			$list_array[] = $team_array;
		}
		$a_afficher .= '<div class="MonLabo MonLabo_teams_table_normal">'
						 . $html->generic_table( '', '', $colums_titles, $list_array )
						 . '</div>';
	}
	return $a_afficher;
}

/**
 * Creates the HTML code of a complete table created from the Information
 * of a group of thematics and intended to be displayed in the administration interface.
 * @return string HTML code
 */
function generate_table_admin_for_thematics(): string {
	$html = new Html();
	$accessData = new Access_Data();
	$thematics_info = $accessData->get_thematics_info();
	$colums_titles = array( '', __( 'Id', 'mon-laboratoire' ), 
							__( 'Name (fr)', 'mon-laboratoire' ),
							__( 'Name (en)', 'mon-laboratoire' ),
							__( 'Pages', 'mon-laboratoire' ),
							__( 'Logo', 'mon-laboratoire' ),
							__( 'Struct. Id', 'mon-laboratoire' ),
							__( 'Teams', 'mon-laboratoire' )
				);
	$list_array = array();
	$number_of_thematics = count( $thematics_info );
	$a_afficher = '';
	if ( '0' != $number_of_thematics ) {
		foreach ( $thematics_info as $thematic_info ) {
			$thematic_array = array();
			$thematic_array['modifier'] = '<form method="post" action="admin.php?page=MonLabo_edit_members_and_groups&tab=tab_thematic&submit_item=' . $thematic_info->id . '&lang=all">'
									 . '<button type="submit">' . __( 'Modify', 'mon-laboratoire' ) . '</button></form>';
			$thematic_array['id'] = $thematic_info->id;
			$thematic_array['name_fr'] = '<small>' . $thematic_info->name_fr . '</small>';
			$thematic_array['name_en'] = '<small>' . $thematic_info->name_en . '</small>';
			$thematic_array['wp_post_id'] = _get_pages_links_from_wp_post_ids( $thematic_info->wp_post_ids );
			$thematic_array['logo'] = ( empty( $thematic_info->logo ) ? '' : '<a href="#" class="hover-zoom-square30"><img src="' . $html->image_from_id_or_url( $thematic_info->logo ) . '" height="24" alt="' . $thematic_info->logo . '" /></a>' );
			$thematic_array['hal_publi_thematic_id'] = $thematic_info->hal_publi_thematic_id;
			$teams_info = $accessData->get_teams_info_for_a_thematic( $thematic_info->id );
			$thematic_array['thematics'] = '';
			if ( ! empty( $teams_info ) ) {
				$teams_links = array();
				foreach ( $teams_info as $team ) {
					$teams_links[] = "<a href='#' title='" . $team->name_fr . "'>$team->id</a>";
				}
				$thematic_array['thematics'] = Lib::secured_implode( ', ', $teams_links );
			}
			$list_array[] = $thematic_array;
		}
		$a_afficher .= '<div class="MonLabo MonLabo_thematics_table_normal">'
						 . $html->generic_table( '', '', $colums_titles, $list_array )
						 . '</div>';
	}
	return $a_afficher;
}

/**
 * Creates the HTML code of a complete table created from the Information
 * of a group of units and intended to be displayed in the administration interface.
 * @return string HTML code
 */
function generate_table_admin_for_units(): string {
	/*			  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
				  `affiliations` varchar(200) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
				  `code` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
				  `name_fr` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
				  `name_en` varchar(255) CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL,
				  `hal_publi_unit_id` int(11) DEFAULT NULL,
				  */
	$html = new Html();
	$accessData = new Access_Data();
	$units_info = $accessData->get_units_info();
	$options4 = get_option( 'MonLabo_settings_group4' );
	$colums_titles = array( '',  __( 'Id', 'mon-laboratoire' ),
							  __( 'Name (fr)', 'mon-laboratoire' ),
							  __( 'Name (en)', 'mon-laboratoire' ),
							  __( 'Pages', 'mon-laboratoire' ),
							  __( 'Logo', 'mon-laboratoire' ),
							  __( 'Code', 'mon-laboratoire' ),
							  __( 'Affiliations', 'mon-laboratoire' ),
							  __( 'Descartes Id', 'mon-laboratoire' ),
							  __( 'Struct. Id', 'mon-laboratoire' ),
							  __( 'Alt. address', 'mon-laboratoire' ),
							  __( 'Alt. contact', 'mon-laboratoire' ),
							  __( 'Directors', 'mon-laboratoire' ) );
	$list_array = array();
	$number_of_units = 0;
	if ( ! empty( $units_info ) ) {
		$number_of_units = count( $units_info );
	}
	$a_afficher = '';
	if ( '0' != $number_of_units ) {
		foreach ( $units_info as $unit_info ) {
			$unit_array = array();
			$unit_array['modifier'] = '<form method="post" action="admin.php?page=MonLabo_edit_members_and_groups&tab=tab_unit&submit_item=' . $unit_info->id . '&lang=all">'
									 . '<button type="submit">' . __( 'Modify', 'mon-laboratoire' ) . '</button></form>';
			$unit_array['id'] = $unit_info->id;
			$unit_array['name_fr'] = '<small>' . $unit_info->name_fr . '</small>';
			$unit_array['name_en'] = '<small>' . $unit_info->name_en . '</small>';
			$unit_array['wp_post_id'] = _get_pages_links_from_wp_post_ids( $unit_info->wp_post_ids );
			$unit_array['logo'] = ( empty( $unit_info->logo ) ? '' : '<a href="#" class="hover-zoom-square30"><img src="' . $html->image_from_id_or_url( $unit_info->logo ) . '" height="24" alt="' . $unit_info->logo . '" /></a>' );
			$unit_array['code'] = $unit_info->code;
			$unit_array['affiliations'] = '<small>' . $unit_info->affiliations . '</small>';
			$unit_array['descartes_publi_unit_id'] = '';
			if ( ! empty( $unit_info->descartes_publi_unit_id ) ) {
				$unit_array['descartes_publi_unit_id'] = "<a href='" . $options4['MonLabo_DescartesPubmed_api_url'] . '?unite=' . $unit_info->descartes_publi_unit_id . "'>"
														 . $unit_info->descartes_publi_unit_id . '</a>';
			}

			$unit_array['hal_publi_unit_id'] = $unit_info->hal_publi_unit_id;
			$unit_array['address_alt'] = $unit_info->address_alt;
			$unit_array['contact_alt'] = $unit_info->contact_alt;
			$directors = $html->persons_names(
				$accessData->get_directors_info_for_an_unit( $unit_info->id ), 'with_admin_link');

			$unit_array['directors'] = '';
			if ( ! empty( $directors ) ) {
				$unit_array['directors'] = implode( ', ', $directors );
			}
			$list_array[] = $unit_array;
		}
		$a_afficher .= '<div class="MonLabo MonLabo_units_table_normal">'
						 . $html->generic_table( '', '', $colums_titles, $list_array )
						 . '</div>';
	}
	return $a_afficher;
}
