<?php
namespace MonLabo\Lib\Person_Or_Structure\Groups;

use MonLabo\Lib\{Lib};
use MonLabo\Lib\Access_Data\{Access_Data};

defined( 'ABSPATH' ) or die( 'No direct script access allowed' );

///////////////////////////////////////////////////////////////////////////////////////////
// Librairie de fonctions pour manipuler les groupes de personnes
///////////////////////////////////////////////////////////////////////////////////////////
/**
 * Class \MonLabo\Lib\Person_Or_Structure\Groups\Persons_Group
 *
 * @package
 */

/*
	get_persons()
	suppress_non_visible()
	extract_and_suppress_direction( $suppress_direction )
	extract_and_suppress_team_leaders( $suppress_leader )
	sort_by_name()
	filter_with_categories( $categories )
	filter_with_teams( $teams )
	filter_with_units( $units )
	filter_with_ids( $persons_ids )
*/
class Persons_Group extends Generic_Group {

	/**
	 * Return a list of persons
	 * @return object[]
	 */
	public function get_persons(): array {
		return $this->_elements_table;
	}

	/**
	 * Suppress persons from $elements_table that are not visible
	 * @return Persons_Group
	 */
	public function suppress_non_visible(): self {
		if ( ! empty( $this->_elements_table ) ) {
			// supprime les personnes qui doivent être invisible
			$key_to_suppress = array();
			foreach ( $this->_elements_table as $key => $person ) {
				if ( property_exists( $person, 'visible' ) ) {
					if ( 0 === strcmp( $person->visible, 'non' ) ) {
						$key_to_suppress[ $key ] = $key;
					}
				}
			}
			$this->_filter_with_cleaned_ids( $key_to_suppress ); //On procède à la suppression effective
		}
		return $this;
	}

	/**
	 * Extract the (co)directors from a table of $persons_info
	 * @param string $display_mode If equal to 'suppress_direction', remove extracted directors from $elements_table
	 * @return \MonLabo\Lib\Person_Or_Structure\Groups\Persons_Group Group of directors
	 */
	public function extract_and_suppress_direction( string $display_mode = 'suppress_direction' ): Persons_Group {
		//TODO: Ajouter une vérification que le directeur est bien celui d’une équipe filtrée
		$accessData = new Access_Data();
		$table_of_directors = array();
		//Extraire les directeurs du tableau initial
		if ( ! empty( $this->_elements_table ) ) {
			foreach ( $this->_elements_table as $key => $person ) {
				if ( property_exists( $person, 'id' ) ) {
					//if the person is a director
					if ( ! empty( $accessData->get_units_id_for_a_director( $person->id ) ) ) {
						$table_of_directors[ $key ] = $person;
					}
				}
			}
		}
		//Effacer les directeurs du tableau initial
		if ( 'suppress_direction' === $display_mode ) {
			$this->_filter_with_cleaned_ids( array_keys( $table_of_directors ) ); //On procède à la suppression effective
		}
		return new Persons_Group( $table_of_directors );
	}

	/**
	 * Extract the team leaders from a table of $persons_info
	 * @param string $display_mode If equal to 'suppress_leaders', remove extracted directors from $elements_table
	 * @return \MonLabo\Lib\Person_Or_Structure\Groups\Persons_Group Group of leaders
	 * @SuppressWarnings(PHPMD.ElseExpression)
	*/
	public function extract_and_suppress_team_leaders( string $display_mode = 'suppress_leaders' ): Persons_Group {
		$accessData = new Access_Data();
		$table_of_leaders = array();
		$permit_leaders_id = array();

		//Calcule les leaders autorisés ( restreints aux équipes filtrées )
		if ( ! empty( $this->_team_filter ) ) {
			foreach ( $this->_team_filter as $team_id ) {
				$permit_leaders_id += $accessData->get_leaders_id_for_a_team( (int) $team_id );
			}
		}

		//Extraire les leaders du tableau initial
		if ( ! empty( $this->_elements_table ) ) {
			foreach ( $this->_elements_table as $key => $person ) {
				if ( property_exists( $person, 'id' ) ) {
					//if the person is a leader
					if ( ! empty( $this->_team_filter ) ) {
						if ( in_array( $person->id, $permit_leaders_id ) ) {
							$table_of_leaders[ $key ] = $person;
						}
					} else {
						if ( ! empty( $accessData->get_teams_id_for_a_leader( $person->id ) ) ) {
							$table_of_leaders[ $key ] = $person;
						}
					}
				}
			}
		}
		//Effacer les leaders du tableau initial
		if ( 'suppress_leaders' === $display_mode ) {
			$this->_filter_with_cleaned_ids( array_keys( $table_of_leaders ) ); //On procède à la suppression effective
		}
		return new Persons_Group( $table_of_leaders );
	}

	/**
	 * Sort by name a table of $persons_info
	 * @return Persons_Group
	 */
	public function sort_by_name(): self {
		$table_to_sort = array();
		if ( $this->count() > 0 ) {
			foreach ( $this->_elements_table as $key => $person_info ) {
				if ( ( isset( $person_info->first_name ) ) and ( isset( $person_info->last_name ) ) ) {
					$concat_name = mb_strtoupper( $person_info->last_name . '  ' . $person_info->first_name, 'UTF-8' );
					$new_key = Lib::generate_french_index_alphabetic_order( $concat_name . $key );
					$table_to_sort[ $new_key ] = $person_info;
				}
			}
			ksort( $table_to_sort );
		}
		$this->_elements_table = $table_to_sort;
		return $this;
	}

	/**
	 * Removes people who are not in one of the categories provided as an argument
	 * @param string[] $categories List of categories
	 * @return Persons_Group
	 */
	public function filter_with_categories( array $categories ): self {
		if ( ! empty( $this->_elements_table ) ) {
			// supprime les personnes qui ne sont pas dans l’une des catégories fournies en argument
			if ( empty( $categories ) ) {
				//On supprime tout
				$this->_elements_table = array();
				return $this;
			}
			$this->_filter_from_authorized_values( 'category', $categories );
		}
		return $this;
	}

	/**
	 * Removes people who are not in one of the teams provided as an argument
	 * @param int[] $teams List of teams ID
	 * @param string $status Status of people to get ('actif', 'allumni')
	 * @return Persons_Group
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	public function filter_with_teams( array $teams, string $status = 'actif' ): self {
		if ( ! empty( $this->_elements_table ) ) {

			//0 - On met à jour la table de filtrage
			if ( empty( $this->_team_filter ) ) {
				$this->_team_filter = $teams;
			} else {
				$this->_team_filter = array_intersect( $this->_team_filter, $teams );
				$teams = $this->_team_filter;
			}

			//1 - On établi une liste d’id de personnes qui seront autorisées
			$accessData = new Access_Data();
			$permit_person_ids = array();
			if ( empty( $teams ) ) {
				$this->_elements_table = array();
				return $this;
			}
			foreach ( $teams as $team_id ) {
				$permit_person_ids += $accessData->get_persons_id_for_a_team ( intval( $team_id ), $status );
			}
			//2 - On supprime toute personne n’etant pas dans la liste autorisée
			$this->filter_with_ids( $permit_person_ids );
		}
		return $this;
	}

	/**
	 * Removes people who are not in one of the units provided as an argument
	 * @param int[] $units List of units ID
	 * @param string $status Status of people to get ('actif', 'allumni')
	 * @return Persons_Group
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	public function filter_with_units( array $units, string $status = 'actif' ): self {
		if ( ! empty( $this->_elements_table ) ) {
			//1 - On établi une liste d’id de personnes qui seront autorisées
			$accessData = new Access_Data();
			if ( empty( $units ) ) {
				$this->_elements_table = array();
				$this->_team_filter = array();
				return $this;
			} else {
				$teams = array();
				foreach ( $units as $unit_id ) {
					$teams += $accessData->get_teams_id_for_an_unit( (int) $unit_id );
				}
			}
			//2 - On supprime toute personne n’etant pas dans la liste autorisée
			$this->filter_with_teams( $teams, $status );
		}
		return $this;
	}

	/**
	 * Removes alumni people who are not gone in the years field provided as an argument
	 * @param string $years_expression Interval or list of year of alumni to keep (ex: "2001|2002|2003|2004" or "2001-2004")
	 * @return Persons_Group
	 * @SuppressWarnings(PHPMD.ElseExpression)
	 */
	public function filter_with_alumni_years( string $years_expression ): self {
		if ( ( empty( $years_expression ) ) or ( empty( $this->_elements_table ) ) ) {
			//Ne pas traiter les cas évidents sans réponse
			$this->_elements_table = array();
			return $this;
		}

		//1 - On établi une liste d’id de personnes qui seront autorisées
		$key_to_suppress = array();
		$years_expression = str_replace( '|', ',', $years_expression ); //Pouvoir utiliser | comme separateur
		$years_array = explode( ',', $years_expression ); // years_expression can be a list 'year1,year2,year3....'
		if ( count( $years_array ) > 1 ) {
			//On va supprimer tous les alumni qui ne sont pas dans cette liste d’annee
			$this->_filter_from_authorized_values( 'date_departure', $years_array );
			return $this;
		}
		$years_array = explode( '-', $years_expression );
		if ( count( $years_array ) < 2 ) { //years_expression can be a single year
			if ( isset( $years_array[0] ) ) {
				$years_array = $years_array[0];
			}
			//On va supprimer tous les alumni qui ne sont pas à cette annee
			foreach ( $this->_elements_table as $key => $person ) {
				if ( property_exists( $person, 'date_departure' )
					and ( $person->date_departure != intval( $years_array ) )
				) {
					$key_to_suppress[ $key ] = $key;
				}
			}
		} else {  // years_expression can be an interval 'year1-year2'
			if ( '' === $years_array[0] ) { // years_expression can be an interval '-duration'
				$years_array[0] = strval( intval ( date( 'Y' ) ) - intval( $years_array[1] ) ); //année en cours - durée
				$years_array[1] = '9999';
			}
			//On va supprimer tous les alumni qui ne sont pas dans la plage d’annee
			foreach ( $this->_elements_table as $key => $person ) {
				if (
						property_exists( $person, 'date_departure' )
						and (
								( $person->date_departure < $years_array[0] )
							or  ( $person->date_departure > $years_array[1] )
							)
				) {
					$key_to_suppress[ $key ] = $key;
				}
			}
		}
		$this->_filter_with_cleaned_ids( $key_to_suppress );
		//Supprimer les alumni repérés mauvais
		return $this;
	}

}
