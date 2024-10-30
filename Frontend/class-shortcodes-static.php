<?php
namespace MonLabo\Frontend;

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

/**
 * Class \MonLabo\Frontend\Shortcodes_Static
 * @package
 */
class Shortcodes_Static {

	/*
		members_list( $atts )				 DONE
		members_table( $atts )				DONE
		members_chart ()

		perso_panel ()						DONE	TESTED
		team_panel ()						 DONE

		former_members_list ()						DONE
		former_members_table ()

		teams_list ()
		//team_table ()						 POUR FUTUR EVENTUEL
		//thematics_list ()					 POUR FUTUR EVENTUEL
		//unit_list ()						  POUR FUTUR EVENTUEL
		//unit_table ()						 POUR FUTUR EVENTUEL

		dev_team_name()
		dev_team_logo()
	*/


	/**
	 * Add shortcode [members_list]
	 * get list of persons from teams, labs etc...
  	 * ------------------------------------------------
  	 * ex 1: [members_list] => get all the persons
  	 * ex 2: [members_list team = '1'] => get all the persons of team 1, separate leaders
  	 * ex 3: [members_list team = '1' uniquelist = 'yes'] => get all the persons of team 1
  	 *													  do not separate leaders
	 * @param mixed[]|string $atts  User defined attributes in shortcode tag
	 * @return string HTML code of the list of persons
	 * @static
	 * @SuppressWarnings(PHPMD.StaticAccess)
	 */
	static function members_list( $atts ): string { //@phan-suppress-current-line PhanUnreferencedPublicMethod
		$Shortcodes = Shortcodes::getInstance();
		return $Shortcodes->members_list( $atts );
	}

	/**
	 * Add shortcode [members_table]
	 *  get table of persons from teams, labs etc...
  	 * ------------------------------------------------
  	 * ex 1: [members_table] => get all the persons
  	 * ex 2: [members_table team = '1'] => get all the persons of team 1
 	 * @param mixed[]|string $atts  User defined attributes in shortcode tag
	 * @return string HTML code of the table of persons
	 * @static
	 * @SuppressWarnings(PHPMD.StaticAccess)
	 */
	static function members_table( $atts ): string { //@phan-suppress-current-line PhanUnreferencedPublicMethod
		$Shortcodes = Shortcodes::getInstance();
		return $Shortcodes->members_table( $atts );
	}

	/**
	 * Add shortcode [members_chart]
	 * get organizational chart of persons from teams, labs etc...
  	 * ------------------------------------------------
  	 * ex 1: [members_chart] => get all the persons
	 * @param mixed[]|string $atts  User defined attributes in shortcode tag
	 * @return string HTML code of the chart of persons
	 * @static
	 * @SuppressWarnings(PHPMD.StaticAccess)
	 */
	static function members_chart( $atts ): string { //@phan-suppress-current-line PhanUnreferencedPublicMethod
		$Shortcodes = Shortcodes::getInstance();
		return $Shortcodes->members_chart( $atts );
	}

	/**
	 * Add shortcode [former_members_chart]
	 * get organizational chart of alumni from teams, labs etc...
  	 * ------------------------------------------------
  	 * ex 1: [former_members_chart] => get all the alumni
	 * @param mixed[]|string $atts  User defined attributes in shortcode tag
	 * @return string HTML code of the chart of persons
	 * @static
	 * @SuppressWarnings(PHPMD.StaticAccess)
	 */
	static function former_members_chart( $atts ): string { //@phan-suppress-current-line PhanUnreferencedPublicMethod
		$Shortcodes = Shortcodes::getInstance();
		return $Shortcodes->former_members_chart( $atts );
	}


	/**
	 * Add shortcode [perso_panel]
	 * Generate the personnal panel of the user belonging to the current page
  	 * ------------------------------------------------
  	 * Ex: [perso_panel] => Only way to use ( no parameters )
	 * @param mixed[]|string $atts  User defined attributes in shortcode tag
	 * @return string HTML code of the panel
	 * @static
	 * @SuppressWarnings(PHPMD.StaticAccess)
	 */
	 static function perso_panel( $atts ): string { //@phan-suppress-current-line PhanUnreferencedPublicMethod
		$Shortcodes = Shortcodes::getInstance();
		return $Shortcodes->perso_panel( $atts );
	}

	/**
	 * Add shortcode [team_panel]
	 * enerate the team panel of the team belonging to the current page
  	 * ------------------------------------------------
  	 * Ex: [team_panel] => Only way to use (no parameters)
	 * @param mixed[]|string $atts  User defined attributes in shortcode tag
	 * @return string HTML code of the panel
	 * @static
	 * @SuppressWarnings(PHPMD.StaticAccess)
	 */
	static function team_panel( $atts ): string { //@phan-suppress-current-line PhanUnreferencedPublicMethod
		$Shortcodes = Shortcodes::getInstance();
		return $Shortcodes->team_panel( $atts );
	}

	/**
	 * Add shortcode [former_members_list]
	 * get list of old members from teams, labs etc...
  	 * ------------------------------------------------
  	 * ex : [former_members_list title = 'Former PhDs and Postdocs' categories = 'postdocs,students'] => get all the alumni was PhD or PostDoct Members and print with title caption
	 * @param mixed[]|string $atts  User defined attributes in shortcode tag
	 * @return string HTML code of the list
	 * @static
	 * @SuppressWarnings(PHPMD.StaticAccess)
	 */
	static function former_members_list( $atts ): string { //@phan-suppress-current-line PhanUnreferencedPublicMethod
		$Shortcodes = Shortcodes::getInstance();
		return $Shortcodes->former_members_list( $atts );
	}

	/**
	 * Add shortcode [former_members_table]
	 * get table of old members from teams, labs etc...
  	 * ------------------------------------------------
  	 * ex: [former_members_table title = 'Former PhDs and Postdocs' categories = 'postdocs,students'] => get all the alumni was PhD or PostDoct Members and print with title caption
	 * @param mixed[]|string $atts  User defined attributes in shortcode tag
	 * @return string HTML code of the table
	 * @static
	 * @SuppressWarnings(PHPMD.StaticAccess)
	 */
	static function former_members_table( $atts ): string { //@phan-suppress-current-line PhanUnreferencedPublicMethod
		$Shortcodes = Shortcodes::getInstance();
		return $Shortcodes->former_members_table( $atts );
	}

	/**
	 * Add shortcode [teams_list]
	 *  get list of teams
  	 * ------------------------------------------------
  	 * ex: [teams_list thematic = '3'] => Get the list of teams for thematics 3
	 * @param mixed[]|string $atts  User defined attributes in shortcode tag
	 * @return string HTML code of the list
	 * @static
	 * @SuppressWarnings(PHPMD.StaticAccess)
	 */
	static function teams_list( $atts ): string { //@phan-suppress-current-line PhanUnreferencedPublicMethod
		$Shortcodes = Shortcodes::getInstance();
		return $Shortcodes->teams_list( $atts );
	}

	/**
	 * Add shortcode [dev_team_name]
	 * get team name from Descartes Pubbmed id for developpers...
	 * This function is not public and documented, only for developping purpose
  	 * ------------------------------------------------
  	 * ex : [dev_team_name descartes_publi_team_id = '3'] => get the english name of team 3
	 * @param mixed[]|string $atts  User defined attributes in shortcode tag
	 * @return string HTML code of the list
	 * @static
	 * @SuppressWarnings(PHPMD.StaticAccess)
	 */
	static function dev_team_name( $atts ): string { //@phan-suppress-current-line PhanUnreferencedPublicMethod
		$Shortcodes = Shortcodes::getInstance();
		return $Shortcodes->dev_team_name( $atts );
	}

	/**
	 * Add shortcode [dev_team_logo]
	 * get team logo from Descartes Pubbmed id for developpers...
	 * This function is not public and documented, only for developping purpose
  	 * ------------------------------------------------
  	 * ex :[dev_team_logo descartes_publi_team_id = '3'] => get the logo of team 3
	 * @param mixed[]|string $atts User defined attributes in shortcode tag
	 * @return string HTML code of the list
	 * @static
	 * @SuppressWarnings(PHPMD.StaticAccess)
	 */
	static function dev_team_logo( $atts ): string { //@phan-suppress-current-line PhanUnreferencedPublicMethod
		$Shortcodes = Shortcodes::getInstance();
		return $Shortcodes->dev_team_logo( $atts );
	}

	/**
	 * Shortcode [publications_list]
	 * Cannot be tested
	 * @param mixed[]|string $atts  shortcode parrameters
	 * @return string HTML code returned par shortcode
	 * @static
	 * @SuppressWarnings(PHPMD.StaticAccess)
	 */
	static function publications_list( $atts ): string { //@phan-suppress-current-line PhanUnreferencedPublicMethod
		$Shortcodes_Publications = Shortcodes_Publications::getInstance();
		return $Shortcodes_Publications->publications_list( $atts );
	}
	
	/**
	 * Shortcode [publications_list2]
	 * Cannot be tested
	 * @param mixed[]|string $atts  shortcode parrameters
	 * @return string HTML code returned par shortcode
	 * @static
	 * @SuppressWarnings(PHPMD.StaticAccess)
	 */
	static function publications_list2( $atts ): string { //@phan-suppress-current-line PhanUnreferencedPublicMethod
		$Shortcodes_Publications2 = Shortcodes_Publications2::getInstance();
		return $Shortcodes_Publications2->publications_list( $atts );
	}
}
