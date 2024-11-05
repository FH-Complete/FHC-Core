<?php
/**
 * Copyright (C) 2022 fhcomplete.org
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \stdClass as stdClass;

/**
 *
 */
class SearchBarLib
{
	// Error constats
	const ERROR_WRONG_JSON = 'ERR001';
	const ERROR_WRONG_SEARCHSTR = 'ERR002';
	const ERROR_NO_TYPES = 'ERR003';
	const ERROR_WRONG_TYPES = 'ERR004';
	const ERROR_NOT_AUTH = 'ERR005';

	// List of allowed types of search
	const ALLOWED_TYPES = ['mitarbeiter', 'mitarbeiter_ohne_zuordnung', 'organisationunit', 'raum', 'person', 'student', 'prestudent', 'document', 'cms'];

	const PHOTO_IMG_URL = '/cis/public/bild.php?src=person&person_id=';

	private $_ci; // Code igniter instance

	private $_searchfunction_priorities = [];
	private $_numeric_searchfunctions = [];
	private $_allowed_searchfunctions = [];
	
	/**
	 * Gets the CI instance and loads model
	 */
	public function __construct()
	{
		$this->_ci =& get_instance(); // get code igniter instance

		// It is loaded only to have the DB functions available
		$this->_ci->load->model('person/Benutzer_model', 'BenutzerModel');

		// Load Config
		$this->_ci->load->config('search', true);
		$this->_ci->load->config('searchfunctions', true);

		$this->_ci->load->library('PhrasesLib', [['search'], null], 'search_phrases');

		// Precompute helper arrays
		foreach ($this->_ci->config->item('searchfunctions') as $key => $arr) {
			$this->_searchfunction_priorities[$key] = $arr['priority'];
			if ($arr['force_integer'] ?? false)
				$this->_numeric_searchfunctions[] = $key;
			$this->_allowed_searchfunctions[] = $key;
		}
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * It performes the search of the given search string using the specified search types
	 * TODO(chris): permissions
	 *
	 * @param string							$searchstring
	 * @param array								$types (optional)
	 *
	 * @return stdClass		containing an array with the result on index 0
	 * 						and the overall query time on index 1.
	 */
	public function search($searchstring, $types = [])
	{
		if (!$types) {
			$types = $this->_ci->config->item('search');
		} else {
			$tmp = [];
			$missing = [];
			foreach ($types as $type) {
				$typeconfig = $this->_ci->config->item($type, 'search');
				if (!$typeconfig) {
					$missing[] = $type;
				} else {
					$tmp[$type] = $typeconfig;
				}
			}
			if ($missing) {
				$p = $this->_ci->search_phrases;
				return error(array_map(function ($type) use ($p) {
					return $p->t('search', 'error_missing_config', [
						'type' => $type
					]); // TODO(chris): phrase
				}, $missing));
			}
			$types = $tmp;
		}


		// Convert searchstring into array
		list($searchArray, $searchstring) = $this->_convertQuery($searchstring, $types);


		$sql = $this->getDynamicSearchSqls($searchArray, array_keys($types));
		if (isError($sql))
			return $sql;
		if (!hasData($sql)) {
			$retval = success([]);
			$retval->meta = ['time' => 0, 'searchstring' => $searchstring];
			return $retval;
		}

		$msc = microtime(true);
		$result = $this->_ci->BenutzerModel->execReadOnlyQuery(getData($sql));
		$msc = microtime(true) - $msc;

		if (isError($result))
			return $result;

		$retval = success($result->retval);
		$retval->meta = [
			'time' => $msc,
			'searchstring' => $searchstring
		];

		return $retval;
	}

	/**
	 * Generates the search query for the given search string and the
	 * specified search type.
	 *
	 * @param array								$searchArray
	 * @param string							$table
	 *
	 * @return stdClass		containing the query string.
	 */
	public function getDynamicSearchSql($searchArray, $table)
	{
		$res = $this->checkConfig($table);
		if (isError($res))
			return $res;
		$table_config = getData($res);

		$sql_with = [];
		
		$sql_select = $this->prepareDynamicSearchSql($sql_with, $searchArray, $table);

		if (!$sql_select)
			return success("");

		$lang = getUserLanguage();

		$output = "WITH";
		if ($sql_with && $sql_with[0] === 'RECURSIVE') {
			$output .= " RECURSIVE";
			array_shift($sql_with);
		}

		$output .= "
			lang (index) AS (
				SELECT index
				FROM public.tbl_sprache
				WHERE sprache=" . $this->_ci->db->escape($lang) . "
				LIMIT 1
			),
			auth (uid) AS (
				SELECT " . $this->_ci->db->escape(getAuthUID()) . " AS uid
			)";

		if ($sql_with) {
			$sql_with = array_unique($sql_with);
			$output .= ", " . implode(", ", $sql_with);
		}

		$other_selects = "";
		if (isset($table_config['resultfields']))
			$other_selects = implode(", ", $table_config['resultfields']);
		if ($other_selects)
			$other_selects = ", " . $other_selects;

		$output .= "
			, q (" . $this->_formatPrimarykeys($table_config['primarykey']) . ", rank) AS (
				SELECT " . $this->_formatPrimarykeys($table_config['primarykey']) . ", MAX(rank)
				FROM (" . implode(" UNION ", $sql_select) . ") q
				GROUP BY " . $this->_formatPrimarykeys($table_config['primarykey']) . "
			)
			SELECT
				" . $this->_ci->db->escape($table) . " AS type,
				q.rank
				" . $other_selects . "
			FROM q
			" . ($table_config['resultjoin'] ?? "") . "
			ORDER BY rank DESC
		";

		return success($output);
	}

	/**
	 * Generates the search query for the given search string and the
	 * specified search types.
	 *
	 * @param array								$searchArray
	 * @param array								$types
	 *
	 * @return stdClass		containing the query string.
	 */
	public function getDynamicSearchSqls($searchArray, $types)
	{
		$with = [];
		$selects = [];
		foreach ($types as $type) {
			$res = $this->checkConfig($type);
			if (isError($res))
				return $res;
			$table_config = getData($res);

			$select = $this->prepareDynamicSearchSql($with, $searchArray, $type);
			if (!$select)
				continue;

			$with[] = "final_" . $type . " (" . $this->_formatPrimarykeys($table_config['primarykey']) . ", rank) AS (
				SELECT " . $this->_formatPrimarykeys($table_config['primarykey']) . ", MAX(rank)
				FROM (" . implode(" UNION ", $select) . ") q
				GROUP BY " . $this->_formatPrimarykeys($table_config['primarykey']) . "
			)";

			$selects[] = "
				SELECT
					" . $this->_ci->db->escape($type) . " AS type,
					rank,
					TO_JSONB((SELECT x FROM (SELECT " . implode(", ", $table_config['resultfields'] ?? ['*']) . ") x)) AS data
				FROM final_" . $type  . "
				" . ($table_config['resultjoin'] ?? "");
		}

		if (!$selects)
			return success("");

		$recursive = "";
		if ($with && $with[0] === "RECURSIVE") {
			$recursive = "RECURSIVE ";
			array_shift($with);
		}

		$with = array_unique($with);

		$lang = getUserLanguage();
		array_unshift($with, "lang (index) AS (
			SELECT index
			FROM public.tbl_sprache
			WHERE sprache=" . $this->_ci->db->escape($lang) . "
			LIMIT 1
		)");
		array_unshift($with, "auth (uid) AS (
			SELECT " . $this->_ci->db->escape(getAuthUID()) . " AS uid
		)");

		return success("
			WITH " . $recursive . implode(", ", $with) . "
			SELECT *
			FROM (" . implode(" UNION ", $selects) . ") q
			ORDER BY rank DESC
			LIMIT 100
		");
	}

	//------------------------------------------------------------------------------------------------------------------
	// Protected methods

	/**
	 * Check config
	 *
	 * @param string							$name
	 *
	 * @return stdClass
	 */
	protected function checkConfig($name)
	{
		$table_config = $this->_ci->config->item($name, 'search');

		if (!$table_config)
			return error($this->_ci->search_phrases->t('search', 'error_missing_config', [
				'type' => $name
			])); // TODO(chris): phrase

		$errors = [];
		if (!isset($table_config['table'])
			|| !is_string($table_config['table'])
			|| !$table_config['table']
		) {
			$errors[] = $this->_ci->search_phrases->t('search', 'error_invalid_config', [
				'type' => $name,
				'field' => 'table'
			]); // TODO(chris): phrase
		}
		if (!isset($table_config['primarykey'])
			|| !is_string($table_config['primarykey'])
			|| !$table_config['primarykey']
		) {
			$errors[] = $this->_ci->search_phrases->t('search', 'error_invalid_config', [
				'type' => $name,
				'field' => 'primarykey'
			]);
		}
		if (!isset($table_config['resultfields'])
			|| !is_array($table_config['resultfields'])
			|| !$table_config['resultfields']
		) {
			$errors[] = $this->_ci->search_phrases->t('search', 'error_invalid_config', [
				'type' => $name,
				'field' => 'resultfields'
			]);
		}
		if (!isset($table_config['searchfields'])
			|| !is_array($table_config['searchfields'])
			|| !$table_config['searchfields']
		) {
			$errors[] = $this->_ci->search_phrases->t('search', 'error_invalid_config', [
				'type' => $name,
				'field' => 'searchfields'
			]);
		} else {
			foreach ($table_config['searchfields'] as $searchfield => $config) {
				if (!isset($config['field'])
					|| !is_string($config['field'])
					|| !$config['field']
				) {
					$errors[] = $this->_ci->search_phrases->t('search', 'error_invalid_config_searchfield', [
						'type' => $name,
						'searchfield' => $searchfield,
						'field' => 'field'
					]); // TODO(chris): phrase
				}
				if (!isset($config['comparison'])
					|| !is_string($config['comparison'])
					|| !in_array($config['comparison'], $this->_allowed_searchfunctions)
				) {
					$errors[] = $this->_ci->search_phrases->t('search', 'error_invalid_config_searchfield', [
						'type' => $name,
						'searchfield' => $searchfield,
						'field' => 'comparison'
					]);
				}
			}
		}

		if ($errors)
			return error($errors);

		return success($table_config);
	}

	/**
	 * Generates the with statements for the given search string and the
	 * specified search type.
	 *
	 * @param array								&$sqlWith
	 * @param array								$searchArray
	 * @param string							$table
	 *
	 * @return string	a query string or the name of the prepared select.
	 */
	protected function prepareDynamicSearchSql(&$sqlWith, $searchArray, $table)
	{
		$table_config = $this->_ci->config->item($table, 'search');

		$id_offset = count($sqlWith);


		$allowed_codes_w_order = ['' => 0, '!' => -1];
		$max = max($this->_searchfunction_priorities);
		foreach ($table_config['searchfields'] as $code => $config) {
			$allowed_codes_w_order[$code] = $this->_searchfunction_priorities[$config['comparison']];
			$allowed_codes_w_order['!' . $code] = $this->_searchfunction_priorities[$config['comparison']] - $max - 2;
		}

		$check_order = $this->_searchfunction_priorities;
		uasort($table_config['searchfields'], function ($a, $b) use ($check_order) {
			return $check_order[$b['comparison']] - $check_order[$a['comparison']];
		});

		$integer_functions = $this->_numeric_searchfunctions;
		$integer_fields = array_keys(array_filter($table_config['searchfields'], function ($a) use ($integer_functions) {
				return in_array($a['comparison'], $integer_functions);
		}));

		$only_integer_fields = count($integer_fields) == count($table_config['searchfields']);

		$aliases = [];
		foreach ($table_config['searchfields'] as $field => $config) {
			if (isset($config['alias'])) {
				foreach ($config['alias'] as $alias) {
					$aliases[$alias] = $field;
					$aliases['!' . $alias] = '!' . $field;
				}
			}
		}

		$sql_select = [];

		if (isset($table_config['prepare'])) {
			$this->_addPreparesToSqlWith($sqlWith, $table_config['prepare']);
		}

		foreach ($searchArray as $or_search) {
			if (isset($or_search['-filter']) && !in_array($table, $or_search['-filter']))
				continue;
			unset($or_search['-filter']);
			
			foreach ($aliases as $alias => $field) {
				if (isset($or_search[$alias])) {
					$or_search[$field] = array_merge($or_search[$alias], $or_search[$field] ?? []);
					unset($or_search[$alias]);
				}
			}

			// NOTE(chris): early out if not allowed fields are in the search array
			$used_codes = array_keys($or_search);
			if (count(array_intersect($used_codes, array_keys($allowed_codes_w_order))) != count($used_codes))
				continue;

			// NOTE(chris): expand general excludes to all fields
			if (isset($or_search['!'])) {
				$not = $or_search['!'];
				unset($or_search['!']);
				foreach ($table_config['searchfields'] as $code => $config) {
					if (isset($or_search['!' . $code]))
						$or_search['!' . $code] = array_unique(array_merge($or_search['!' . $code], $not));
					else
						$or_search['!' . $code] = $not;
				}
			}

			// NOTE(chris): early out if all searchfields require an integer and at least one searchword is not a number
			if ($only_integer_fields
				&& isset($or_search[""])
				&& $this->_hasAtLeastOneNaN($or_search[""])
			) {
				continue;
			}

			$skip = false;
			foreach ($integer_fields as $code) {
				// NOTE(chris): filter non integer for integer fields
				if (isset($or_search['!' . $code])) {
					$or_search['!' . $code] = array_filter($or_search['!' . $code], function ($a) {
						return is_numeric($a);
					});
					if (!$or_search['!' . $code])
						unset($or_search['!' . $code]);
				}
				// NOTE(chris): early out if a searchword that is not a number is compared to a searchfield that requires an integer
				if (isset($or_search[$code])
					&& $this->_hasAtLeastOneNaN($or_search[$code])
				) {
					$skip = true;
					break;
				}
			}
			if ($skip)
				continue;

			// NOTE(chris): sort for performance reasons
			uksort($or_search, function ($a, $b) use ($allowed_codes_w_order) {
				return $allowed_codes_w_order[$b] - $allowed_codes_w_order[$a];
			});

			$or_with = [];
			$or_select = [];
			$or_prepare = [];

			if (substr(key($or_search), 0, 1) == '!') {
				// NOTE(chris): only negative searchwords
				$sql = [];
				foreach ($or_search as $code => $words) {
					$code = substr($code, 1);
					// NOTE(chris): sort for performance reasons
					usort($words, function ($a, $b) {
						return strlen($b) - strlen($a);
					});
					$field_config = $table_config['searchfields'][$code];

					if (isset($field_config['prepare'])) {
						$this->_addPreparesToSqlWith($or_with, $field_config['prepare']);
						$or_prepare[$code] = $field_config['prepare'];
						unset($table_config['searchfields'][$code]['prepare']);
						unset($field_config['prepare']);
					}
					$field_sql = "
						SELECT
							" . $this->_formatPrimarykeys($table_config['primarykey'], $table_config['table']) . "
							FROM " . $table_config['table'] . "
							" . $this->_makeJoin($field_config['join']) . "
							WHERE ";
					// TODO(chris): equals and equal-int could be IN () statement???
					foreach ($words as $word) {
						$sql[] = $field_sql . $this->_makeCompareBool($field_config['comparison'], $field_config['field'], $word);
					}
				}

				$or_select[] = "
					SELECT 
						" . $table_config['table']($table_config['primarykey']) . ",
						1.0 AS rank
					FROM " . $table_config['table'] . "
					WHERE prestudent_id NOT IN (" . implode(" UNION ", $sql) . ")";
			} else {
				$current_select = false;
				$count = 0;
				$skip = false;
				foreach ($or_search as $code => $words) {
					// NOTE(chris): sort for performance reasons
					if ($code && substr($code, 0, 1) == '!') {
						usort($words, function ($a, $b) {
							return strlen($a) - strlen($b);
						});
					} else {
						usort($words, function ($a, $b) {
							return strlen($b) - strlen($a);
						});
					}
					if ($code == '') {
						foreach ($words as $i => $word) {
							$field_sql = [];
							foreach ($table_config['searchfields'] as $c => $field_config) {
								if (in_array($field_config['comparison'], $integer_functions) && !is_numeric($word))
									continue;

								$word_from = $table_config['table'];
								$word_join = "";
								$word_rank = "0";
								if ($current_select) {
									$word_from = $current_select;
									if ($this->_needBasicTableJoin($field_config['field'], $table_config['primarykey'])) {
										$word_join .= " " . $this->_makeJoin($table_config);
									}
									$word_rank = "rank";
								}
								if (isset($field_config['prepare'])) {
									$this->_addPreparesToSqlWith($or_with, $field_config['prepare']);
									$or_prepare[$c] = $field_config['prepare'];
									unset($table_config['searchfields'][$c]['prepare']);
									unset($field_config['prepare']);
								}
								if (isset($field_config['join'])) {
									$word_join .= " " . $this->_makeJoin($field_config['join']);
								}
								$field_sql[] = "
									SELECT
										" . $this->_formatPrimarykeys($table_config['primarykey'], $word_from) . ",
										" . $word_rank . " AS w_rank,
										" . $this->_makeRank($field_config['comparison'], $field_config['field'], $word) . " AS rank
										FROM " . $word_from . "
										" . $word_join . "
										WHERE " . $this->_makeCompare($field_config['comparison'], $field_config['field'], $word);
							}
							// NOTE(chris): skip because the word is not numeric but all searchfields require integers
							if (!$field_sql) {
								$or_with = [];
								$or_select = [];
								$count = 0;
								$skip = true;
								foreach ($or_prepare as $k => $v)
									$table_config['searchfields'][$k]['prepare'] = $v;
								break;
							}

							$id = "w" . ($id_offset + count($or_with));
							$or_with[] = "
								" . $id . " (" . $this->_formatPrimarykeys($table_config['primarykey']) . ", rank) AS (
								SELECT
									" . $this->_formatPrimarykeys($table_config['primarykey']) . ",
									(w_rank + 1.0 - CASE " .
										"WHEN MIN(rank) = 0 THEN 0 " .
										"ELSE EXP(SUM(LN(CASE WHEN rank = 0 THEN 1 ELSE rank " .
										"END))) END) AS rank
									FROM (" . implode(' UNION ALL ', $field_sql) . ") " . $id . "
									GROUP BY " . $this->_formatPrimarykeys($table_config['primarykey']) . ", w_rank
								)";
							$current_select = $id;
						}
					} else {
						foreach ($words as $i => $word) {
							$where = "";
							$rank = "";
							$jointype = "";
							if (substr($code, 0, 1) == '!') {
								$c = substr($code, 1);
								$field_config = $table_config['searchfields'][$c];
								
								$rank = "1";

								$jointype = "LEFT";

								$where = $field_config['field'] .
									" IS NULL OR NOT (" .
									$this->_makeCompareBool(
										$field_config['comparison'],
										$field_config['field'],
										$word
									) .
									")";
								if ($field_config['1-n'] ?? false) {
									$where = "GROUP BY " .
										$this->_formatPrimarykeys($table_config['primarykey'], $current_select ?: $table_config['table']) .
										", rank HAVING MIN(CASE WHEN " .
										$where .
										" THEN 1 ELSE 0 END) = 1";
								} else {
									$where = "WHERE " . $where;
								}
							} else {
								$field_config = $table_config['searchfields'][$code];

								$rank = $this->_makeRank($field_config['comparison'], $field_config['field'], $word);

								$where = $this->_makeCompare($field_config['comparison'], $field_config['field'], $word);
								$where = "WHERE " . $where;
							}
							$word_from = $table_config['table'];
							$word_join = "";
							$word_rank = "";
							if ($current_select) {
								$word_from = $current_select;
								if ($this->_needBasicTableJoin($field_config['field'], $table_config['primarykey'])) {
									$word_join .= " " . $this->_makeJoin($table_config);
								}
								$word_rank = "rank + ";
							}
							if (isset($field_config['prepare'])) {
								$this->_addPreparesToSqlWith($or_with, $field_config['prepare']);
								$or_prepare[$code] = $field_config['prepare'];
								unset($table_config['searchfields'][$code]['prepare']);
								unset($field_config['prepare']);
							}
							if (isset($field_config['join'])) {
								$word_join .= " " . $this->_makeJoin($field_config['join'], $jointype);
							}

							$id = "w" . ($id_offset + count($or_with));
							$or_with[] = "
								" . $id . " (" . $this->_formatPrimarykeys($table_config['primarykey']) . ", rank) AS (
								SELECT
									" . $this->_formatPrimarykeys($table_config['primarykey'], $word_from) . ",
									" . $word_rank . $rank . " AS rank
									FROM " . $word_from . "
									" . $word_join . "
									" . $where . "
								)";
							$current_select = $id;
						}
					}
					if ($skip)
						break;
					$count += count($words);
				}

				if (!$count || !$current_select)
					continue;

				$or_select[] = "
					SELECT " . $this->_formatPrimarykeys($table_config['primarykey']) . ", rank / " . $count . " AS rank FROM " . $current_select;
			}

			if ($or_with[0] === "RECURSIVE") {
				if ($sqlWith[0] !== "RECURSIVE")
					array_unshift($sqlWith, "RECURSIVE");
				array_shift($or_with);
			}

			$sqlWith = array_merge($sqlWith, $or_with);
			$sql_select = array_merge($sql_select, $or_select);
			$id_offset += count($or_with);
		}

		return $sql_select;
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Checks if the field is not one of the primarykeys.
	 *
	 * @param string							$field
	 * @param array|string						$primarykeys
	 *
	 * @return boolean
	 */
	private function _needBasicTableJoin($field, $primarykeys)
	{
		if (!is_array($primarykeys) && strpos($primarykeys, ",") !== false) {
			return $field != $primarykeys;
		}
		if (!is_array($primarykeys))
			$primarykeys = explode(",", $primarykeys);

		foreach ($primarykeys as $key) {
			if ($field == trim($key))
				return false;
		}
		return true;
	}

	/**
	 * Returns comma separated primarykeys. Optionally with table prefix
	 *
	 * @param array|string						$primarykeys
	 * @param string							$prefix
	 *
	 * @return string
	 */
	private function _formatPrimarykeys($primarykeys, $prefix = "")
	{
		if (is_array($primarykeys)) {
			if ($prefix)
				$prefix .= ".";
			return $prefix . implode(", " . $prefix, $primarykeys);
		}
		if (!$prefix)
			return $primarykeys;

		return $prefix . "." . implode(", " . $prefix . ".", explode(",", $primarykeys));
	}

	/**
	 * Adds the prepare statement to the sqlWith stack and handles the
	 * "RECURSIVE" modifier
	 *
	 * @param array								&$sqlWith
	 * @param array								$prepares
	 *
	 * @return void
	 */
	private function _addPreparesToSqlWith(&$sqlWith, $prepares)
	{
		$recursive = $sqlWith[0] ?? "" === "RECURSIVE";
		if (!is_array($prepares))
			$prepares = [$prepares];
		
		foreach ($prepares as $prep) {
			$prep = trim($prep);
			if (strtoupper(substr($prep, 0, 10)) === "RECURSIVE ") {
				$recursive = true;
				$sqlWith[] = substr($prep, 10);
			} else {
				$sqlWith[] = $prep;
			}
		}
		if ($recursive && $sqlWith[0] !== "RECURSIVE") {
			array_unshift($sqlWith, "RECURSIVE");
		}
	}

	/**
	 * Checks if an array has at least on non numeric value.
	 *
	 * @param array								$arr
	 *
	 * @return boolean
	 */
	private function _hasAtLeastOneNaN($arr)
	{
		foreach ($arr as $value)
			if (!is_numeric($value))
				return true;
		return false;
	}

	/**
	 * Helper function for getDynamicSearchSql
	 *
	 * @param array								$join
	 * @param string							$prefix
	 *
	 * @return string
	 */
	private function _makeJoin($join, $prefix = "")
	{
		if (!is_array($join))
			return "";
		if (!isset($join['table'])) {
			$output = [];
			foreach ($join as $j)
				$output[] = trim($this->_makeJoin($j, $prefix));
			return implode(" ", $output);
		}
		if (!isset($join['on']) && !isset($join['using']) && !isset($join['primarykey']))
			return "";
		$output = $prefix . " JOIN " . $join['table'];

		if (isset($join['using']))
			return $output . " USING (" . $join['using'] . ")";

		if (isset($join['primarykey']))
			return $output . " USING (" . $join['primarykey'] . ")";

		return $output . " ON (" . $join['on'] . ")";
	}

	/**
	 * Helper function for _makeRank, _makeCompare and _makeCompareBool
	 *
	 * @param string							$function
	 * @param string							$mode
	 * @param string							$field
	 * @param string							$word
	 *
	 * @return string
	 */
	private function _makeFunction($function, $mode, $field, $word)
	{
		$searchfunction = $this->_ci->config->item($mode, 'searchfunctions');

		if (!$searchfunction)
			return "";
		$tpl = $searchfunction[$function] ?? "";
		
		if (strstr($tpl, '{field}'))
			$tpl = str_replace('{field}', $field, $tpl);

		if (strstr($tpl, '{word}'))
			$tpl = str_replace('{word}', $this->_ci->db->escape($word), $tpl);
		if (strstr($tpl, '{like:word}'))
			$tpl = str_replace('{like:word}', "'%" . $this->_ci->db->escape_like_str($word) . "%'", $tpl);
		
		return $tpl;
	}

	/**
	 * Helper function for getDynamicSearchSql
	 *
	 * @param string							$mode
	 * @param string							$field
	 * @param string							$word
	 *
	 * @return string
	 */
	private function _makeRank($mode, $field, $word)
	{
		return $this->_makeFunction('rank', $mode, $field, $word);
	}

	/**
	 * Helper function for getDynamicSearchSql
	 *
	 * @param string							$mode
	 * @param string							$field
	 * @param string							$word
	 *
	 * @return string
	 */
	private function _makeCompare($mode, $field, $word)
	{
		return $this->_makeFunction('compare', $mode, $field, $word);
	}

	/**
	 * Helper function for getDynamicSearchSql
	 *
	 * @param string							$mode
	 * @param string							$field
	 * @param string							$word
	 *
	 * @return string
	 */
	private function _makeCompareBool($mode, $field, $word)
	{
		$searchfunction = $this->_ci->config->item($mode, 'searchfunctions');

		if (!$searchfunction)
			return "";
		$function = isset($searchfunction['compare_boolean']) ? 'compare_boolean' : 'compare';
		return $this->_makeFunction($function, $mode, $field, $word);
	}

	/**
	 * Converts the search string to an array.
	 * First level should be joined with an OR.
	 * Second level should be joined with an AND or AND NOT.
	 * It is an associative array where the key is a code for the field
	 * which the words should be compared with and the value is the array
	 * of words.
	 * Use AND NOT if the first letter in the key is "!".
	 * Use AND if the first letter in the key is not "!".
	 * E.g:
	 * If the key is:
	 * "": the words should be compared to all fields with AND.
	 * "!": the words should be compared to all fields with AND NOT.
	 * "somefield": the words should be compared to the field somefield with
	 * AND.
	 * "!somefield": the words should be compared to the field somefield with
	 * AND NOT.
	 *
	 * @param string							$searchstring
	 * @param array								$types
	 *
	 * @return array
	 */
	private function _convertQuery($searchstring, $types)
	{
		$searchAllTypes = count($types) == count($this->_ci->config->item('search'));
		$allowedTypes = array_keys($types);

		$currentArray = [];
		$outputArray = [];
		$cleanStrings = [];
		$cleanSearchstring = '';
		$filter = ['+' => [], '-' => []];
		$typeAliases = [];

		$tmp = explode(' ', strtolower($searchstring));
		while ($tmp) {
			$chunk = trim(array_shift($tmp));
			if ($chunk == '')
				continue;

			if (strpos($chunk, '"') !== false) {
				$test = explode('"', $chunk);
				if (count($test) > 2) {
					$rest = implode('"', array_slice($test, 2));
					if ($rest) {
						array_unshift($tmp, $rest);
						$chunk = implode('"', array_slice($test, 0, 2)) . '"';
					}
				}
				if (count($test) == 2) {
					while ($tmp && strpos($test[1], '"') === false) {
						$test[1] .= ' ' . trim(array_shift($tmp));
					}
					if (strpos($test[1], '"') === false) {
						$chunk = implode('"', $test) . '"';
					} else {
						$test2 = explode('"', $test[1], 2);
						$chunk = $test[0] . '"' . $test2[0] . '"';
						if ($test2[1]) {
							array_unshift($tmp, $test2[1]);
						}
					}
				}
				if (strpos($chunk, ' ') === false) {
					$chunk = str_replace('"', '', $chunk);
				}
			}

			if ($chunk == 'or') {
				$this->_convertQueryCleanupOr($currentArray, $cleanStrings, $filter, $searchAllTypes, $allowedTypes);
				$filter = ['+' => [], '-' => []];
				if ($currentArray) {
					$cleanSearchstring .= ($cleanSearchstring ? ' or ' : '') . implode(' ', $cleanStrings);
					$cleanStrings = [];
					$outputArray[] = $currentArray;
					$currentArray = [];
				}
				continue;
			}

			if ($chunk == ':' || $chunk == '-' || substr($chunk, -1) == ':')
				continue;

			if ($chunk[0] == ':' || ($chunk[0] == '-' && $chunk[1] == ':')) {
				if (!$typeAliases) {
					foreach ($types as $type => $config) {
						$typeAliases[$type] = $type;
						if (isset($config['alias'])) {
							foreach ($config['alias'] as $alias) {
								if (!isset($typeAliases[$alias]))
									$typeAliases[$alias] = $type;
							}
						}
					}
				}

				$test = explode(':', $chunk, 2);
				if (isset($typeAliases[$test[1]]))
					$chunk = $test[0] . ':' . $typeAliases[$test[1]];
				elseif ($test[0] == '-')
					continue;
			}

			if (in_array($chunk, $cleanStrings))
				continue;

			$cleanStrings[] = $chunk;

			$chunk = str_replace('"', '', $chunk);
			$code = '';

			if ($chunk[0] == '-') {
				$code = '!';
				$chunk = substr($chunk, 1);
			}
			if (strpos($chunk, ':') !== false) {
				$chunk = explode(':', $chunk, 2);
				if (!$chunk[0]) {
					$filter[$code ? '-' : '+'][] = $chunk[1];
					continue;
				}
				$code .= $chunk[0];
				$chunk = $chunk[1];
			}

			if (!isset($currentArray[$code]))
				$currentArray[$code] = [];
			
			$currentArray[$code][] = $chunk;
		}

		$this->_convertQueryCleanupOr($currentArray, $cleanStrings, $filter, $searchAllTypes, $allowedTypes);
		if ($currentArray) {
			$cleanSearchstring .= ($cleanSearchstring ? ' or ' : '') . implode(' ', $cleanStrings);
			$outputArray[] = $currentArray;
		}
		return [$outputArray, $cleanSearchstring];
	}

	private function _convertQueryCleanupOr(&$currentArray, &$cleanStrings, $filter, $searchAllTypes, $allowedTypes)
	{
		if ($filter['+'] && $filter['-']) {
			$double = array_intersect($filter['+'], $filter['-']);
			if ($double) {
				foreach ($double as $type) {
					array_splice($cleanStrings, array_search(':' . $type, $cleanStrings), 1);
					array_splice($cleanStrings, array_search('-:' . $type, $cleanStrings), 1);
				}
				$filter['+'] = array_diff($filter['+'], $double);
				$filter['-'] = array_diff($filter['-'], $double);
			}
			if (!$filter['+'] && !$filter['-']) {
				// All filters cancel each other out
				$currentArray = [];
				$cleanStrings = [];
				return;
			}
			if ($filter['+']) {
				foreach ($filter['-'] as $type) {
					array_splice($cleanStrings, array_search('-:' . $type, $cleanStrings), 1);
				}
				$filter['-'] = [];
			}
		}
		if ($filter['+']) {
			$cleanFilter = array_intersect($allowedTypes, $filter['+']);
			if (!$cleanFilter) {
				// All filters are forbidden
				$currentArray = [];
				$cleanStrings = [];
				return;
			}
			$forbiddenFilter = array_diff($cleanFilter, $filter['+']);
			foreach ($forbiddenFilter as $type) {
				array_splice($cleanStrings, array_search(':' . $type, $cleanStrings), 1);
			}
			$filter['+'] = $cleanFilter;
		} elseif ($filter['-']) {
			$filter['+'] = array_diff($allowedTypes, $filter['-']);
			if (!$searchAllTypes) {
				foreach ($filter['+'] as $type)
					$cleanStrings[] = ':' . $type;
				foreach ($filter['-'] as $type)
					array_splice($cleanStrings, array_search('-:' . $type, $cleanStrings), 1);
			}
		} else {
			if (!$searchAllTypes) {
				foreach ($allowedTypes as $type)
					$cleanStrings[] = ':' . $type;
			}
		}

		if ($filter['+']) {
			$currentArray['-filter'] = $filter['+'];
		}
	}
}
