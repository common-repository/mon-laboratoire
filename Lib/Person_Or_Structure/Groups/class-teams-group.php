<?php
namespace MonLabo\Lib\Person_Or_Structure\Groups;

use MonLabo\Lib\{Translate, Lib};
use MonLabo\Lib\Access_Data\{Access_Data};

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

///////////////////////////////////////////////////////////////////////////////////////////
// Librairie de fonctions pour manipuler les groupes d’équipes.
///////////////////////////////////////////////////////////////////////////////////////////
/**
 * Class \MonLabo\Lib\Person_Or_Structure\Groups\Teams_Group
 *
 * @package
 */

/*
	get_teams()
	sort_by_name()
	filter_with_categories( $categories )
	filter_with_ids( $persons_ids )
	filter_with_groups( $groups )
	filter_with_units( $units )
*/
class Teams_Group extends Generic_Group {

	/**
	 * Return a list of teams
	 * @return object[]
	 */
	public function get_teams(): array {
		return $this->_elements_table;
	}

	/**
	 * Sort by name an array of $teams_info
	 * @param string $language For chosing laguage of the name
	 * @return Teams_Group
	 */
	public function sort_by_name( string $language = 'en' ): self {
		$table_to_sort = array();
		$translate = new Translate( $language );
		$name = 'name_' . $translate->get_lang_short();
		if ( $this->count() > 0 ) {
			foreach ( $this->_elements_table as $key=>$team_info ) {
				if ( isset( $team_info->{$name} ) ) {
					$table_to_sort[
							Lib::generate_french_index_alphabetic_order(
								mb_strtoupper( $team_info->{$name} , 'UTF-8' ) ) . $key
								] = $team_info;
				}
			}
			ksort( $table_to_sort );
		}
		$this->_elements_table = $table_to_sort;
		return $this;
	}

	/**
	 * Removes teams who are not in one of the groups provided as an argument
	 * @param int[] $groups List of groups id
	 * @return Teams_Group
	 */
	public function filter_with_groups( array $groups ): self {
		if ( ! empty( $this->_elements_table ) ) {
			//1 - On établi une liste d’id d’équipes qui seront autorisées
			$accessData = new Access_Data();
			if ( empty( $groups ) ) {
				$this->_elements_table = array();
				//$this->_team_filter = array();
				return $this;
			}
			$groups_thematics = array();
			foreach ( $groups as $group_id ) {
				$groups_thematics += $accessData->get_teams_id_for_a_thematic( (int) $group_id );
			}
			//2 - On supprime toute équipe n’etant pas dans la liste autorisée
			$this->filter_with_ids( $groups_thematics );
		}
		return $this;
	}

	/**
	 * Removes teams who are not in one of the units provided as an argument
	 * @param int[] $units List of units id
	 * @return Teams_Group
	 */
	public function filter_with_units( array $units ): self {
		if ( ! empty( $this->_elements_table ) ) {
			//1 - On établi une liste d’id d’équipes qui seront autorisées
			$accessData = new Access_Data();
			if ( empty( $units ) ) {
				$this->_elements_table = array();
				//$this->_team_filter = array();
				return $this;
			}
			$teams = array();
			foreach ( $units as $unit_id ) {
				  $teams += $accessData->get_teams_id_for_an_unit( (int) $unit_id );
			}
			//2 - On supprime toute équipe n’etant pas dans la liste autorisée
			$this->filter_with_ids( $teams );
		}
		return $this;
	}


}
