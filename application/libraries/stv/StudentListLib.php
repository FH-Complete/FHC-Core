<?php
/**
 * Copyright (C) 2025 fhcomplete.org
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

/**
 * This generates a list of students and or prestudents used for Studierendenverwaltung
 */
class StudentListLib
{
	private $_ci; // Code igniter instance

	private $_allowedStgs = [];
	private $_selects = [];
	private $_joins = [];
	
	/**
	 * Gets the CI instance, loads model and prepares default values
	 *
	 * @param array		$params
	 *
	 * @return void
	 */
	public function __construct($params = null)
	{
		$this->_ci =& get_instance(); // get code igniter instance

		$this->_ci->load->model('crm/Prestudent_model', 'PrestudentModel');

		if (isset($params['allowedStgs']))
			$this->_allowedStgs = $params['allowedStgs'];

		// Add default SELECTs
		$this->addSelect("b.uid");
		if (defined('STV_TAGS_ENABLED') && STV_TAGS_ENABLED)
			$this->addSelect('tag_data_agg.tags');
		$this->addSelect('titelpre');
		$this->addSelect('nachname');
		$this->addSelect('vorname');
		$this->addSelect('wahlname');
		$this->addSelect('vornamen');
		$this->addSelect('titelpost');
		$this->addSelect('ersatzkennzeichen');
		$this->addSelect('gebdatum');
		$this->addSelect('geschlecht');
		$this->addSelect('foto');
		$this->addSelect('foto_sperre');
		$this->addSelect('v.semester');
		$this->addSelect('v.verband');
		$this->addSelect('v.gruppe');
		$this->addSelect("statusofsemester"); // Will be replaced later
		$this->addSelect('UPPER(stg.typ || stg.kurzbz) AS studiengang');
		$this->addSelect('tbl_prestudent.studiengang_kz');
		$this->addSelect('stg.bezeichnung AS stg_bezeichnung');
		$this->addSelect("s.matrikelnr");
		$this->addSelect('p.person_id');
		$this->addSelect('pls.status_kurzbz AS status');
		$this->addSelect('pls.datum AS status_datum');
		$this->addSelect('pls.bestaetigtam AS status_bestaetigung');
		$this->addSelect(
			"(SELECT kontakt FROM public.tbl_kontakt WHERE kontakttyp='email' AND person_id=p.person_id AND zustellung LIMIT 1) AS mail_privat",
			false
		);
		$this->addSelect("
			CASE WHEN b.uid IS NOT NULL AND b.uid<>'' 
			THEN CONCAT(b.uid, '@', " . $this->_ci->PrestudentModel->escape(DOMAIN) . ")
			ELSE '' END AS mail_intern", false);
		$this->addSelect('p.anmerkung AS anmerkungen');
		$this->addSelect('tbl_prestudent.anmerkung');
		$this->addSelect('pls.orgform_kurzbz');
		$this->addSelect('aufmerksamdurch_kurzbz');
		$this->addSelect(
			"(SELECT rt_gesamtpunkte AS punkte FROM public.tbl_prestudent WHERE prestudent_id=ps.prestudent_id) AS punkte",
			false
		);
		$this->addSelect('tbl_prestudent.aufnahmegruppe_kurzbz');
		$this->addSelect('tbl_prestudent.dual');
		$this->addSelect('p.matr_nr');
		$this->addSelect('sp.bezeichnung AS studienplan_bezeichnung');
		$this->addSelect('tbl_prestudent.prestudent_id');
		$this->addSelect("(
			SELECT count(*)
			FROM (
				SELECT *, public.get_rolle_prestudent(pss.prestudent_id, NULL) AS laststatus
				FROM public.tbl_prestudent pss
				JOIN public.tbl_prestudentstatus USING (prestudent_id)
				WHERE person_id = p.person_id
				AND studiensemester_kurzbz = (
					SELECT studiensemester_kurzbz
					FROM public.tbl_prestudentstatus
					WHERE prestudent_id = tbl_prestudent.prestudent_id
					AND status_kurzbz = 'Interessent'
					LIMIT 1
				)
				AND status_kurzbz = 'Interessent'
			) prest
			WHERE laststatus NOT IN ('Abbrecher', 'Abgewiesener', 'Absolvent')
			AND priorisierung <= tbl_prestudent.priorisierung
		) || ' (' || COALESCE(tbl_prestudent.priorisierung::text, ' '::text) || ')' AS priorisierung_relativ", false); // TODO(chris): overwrite in fetchStudents
		$this->addSelect('mentor');
		$this->addSelect('b.aktiv AS bnaktiv');
		$this->addSelect('unruly');
		
		// Add default JOINs
		$this->addJoin('public.tbl_studiengang stg', 'studiengang_kz', 'LEFT');
		$this->addJoin('public.tbl_person p', 'person_id');
		$this->addJoin('public.tbl_student s', 'prestudent_id', 'LEFT'); // TODO(chris): overwrite in fetchStudents
		$this->addJoin('public.tbl_prestudentstatus pls', '
			pls.status_kurzbz=public.get_rolle_prestudent(tbl_prestudent.prestudent_id, NULL) 
			AND pls.prestudent_id=tbl_prestudent.prestudent_id 
			AND pls.studiensemester_kurzbz=public.get_stdsem_prestudent(tbl_prestudent.prestudent_id, NULL) 
			AND pls.ausbildungssemester=public.get_absem_prestudent(tbl_prestudent.prestudent_id, NULL)', 'LEFT');
		$this->addJoin('lehre.tbl_studienplan sp', 'studienplan_id', 'LEFT');
		$this->addJoin('public.tbl_benutzer b', 's.student_uid=b.uid', 'LEFT');
		$this->addJoin("v", "", ""); // Will be replaced later
		$this->addJoin("ps", "", ""); // Will be replaced later
		if (defined('STV_TAGS_ENABLED') && STV_TAGS_ENABLED) {
			$this->_ci->load->config('stv');
			$tags = $this->_ci->config->item('stv_prestudent_tags');

			$whereTags = '';
			if (is_array($tags) && !isEmptyArray($tags)) {
				$tags = array_keys($tags);

				foreach ($tags as $key => $tag) {
					$tags[$key] = $this->_ci->PrestudentModel->escape($tag);
				}
				$whereTags = " AND nt.typ_kurzbz IN (" . implode(",", $tags) . ")";
			}
			$subQueryTag = "(
				SELECT
					tag.prestudent_id,
					COALESCE(json_agg(tag ORDER BY tag.done), '[]'::json) AS tags
				FROM (
					SELECT DISTINCT ON (n.notiz_id)
						n.notiz_id AS id,
						nt.typ_kurzbz,
						array_to_json(nt.bezeichnung_mehrsprachig)->>0 AS beschreibung,
						n.text AS notiz,
						nt.style,
						n.erledigt AS done,
						nz.prestudent_id
					FROM public.tbl_notizzuordnung AS nz
					JOIN public.tbl_notiz AS n ON nz.notiz_id = n.notiz_id
					JOIN public.tbl_notiz_typ AS nt ON n.typ = nt.typ_kurzbz " . $whereTags . "
				) AS tag
				GROUP BY tag.prestudent_id
			) AS tag_data_agg";

			$this->addJoin($subQueryTag, 'tag_data_agg.prestudent_id = tbl_prestudent.prestudent_id', 'LEFT');
		}
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Adds a SELECT statement to the query.
	 *
	 * @param string|array	$select
	 * @param boolean		$escape (optional)
	 *
	 * @return void
	 */
	public function addSelect($select, $escape = true)
	{
		if (is_array($select)) {
			foreach ($select as $s)
				$this->addSelect($s, $escape);
			return;
		}
		$alias = $this->getAliasFromSelect($select);
		$this->_selects[$alias] = [$select, $escape];
	}

	/**
	 * Joins a table to the query.
	 *
	 * @param string		$table
	 * @param string		$cond
	 * @param string		$type (optional)
	 * @param string		$position (optional)
	 *
	 * @return void
	 */
	public function addJoin($table, $cond, $type = '', $position = 'end')
	{
		$alias = $this->getAliasFromTable($table);

		if ($position == 'end') {
			return $this->_joins[$alias] = [$table, $cond, $type];
		}

		if ($position == 'start') {
			return $this->_joins = [$alias => [$table, $cond, $type]] + $this->_joins;
		}

		if (substr($position, 0, 7) == 'before_') {
			$ref = substr($position, 7);
			$index = 0;
		} elseif (substr($position, 0, 6) == 'after_') {
			$ref = substr($position, 6);
			$index = 1;
		} else {
			return $this->addJoin($table, $cond, $type);
		}
		if (!isset($this->_joins[$ref]))
			return $this->addJoin($table, $cond, $type);

		$key_indeces = array_flip(array_keys($this->_joins));
		$index += $key_indeces[$ref];

		if (!$index)
			return $this->addJoin($table, $cond, $type, 'start');

		$front_part = array_slice($this->_joins, 0, $index, true);
		$back_part = array_slice($this->_joins, $index, null, true);

		if (isset($front_part[$alias])) {
			unset($front_part[$alias]);
		}

		$this->_joins = $front_part + [$alias => [$table, $cond, $type]] + $back_part;
	}

	/**
	 * Adds a WHERE clause to the query.
	 *
	 * @param string|array	$key
	 * @param string|array	$value
	 * @param boolean		$escape
	 *
	 * @return void
	 */
	public function addWhere($key, $value = null, $escape = true)
	{
		if (!is_array($key) && is_array($value)) {
			$this->_ci->PrestudentModel->db->where_in($key, $value, $escape);
		} else {
			$this->_ci->PrestudentModel->db->where($key, $value, $escape);
		}
	}

	/**
	 * Adds a OR WHERE clause to the query.
	 *
	 * @param string|array	$key
	 * @param string|array	$value
	 * @param boolean		$escape
	 *
	 * @return void
	 */
	public function addOrWhere($key, $value = null, $escape = true)
	{
		if (!is_array($key) && is_array($value)) {
			$this->_ci->PrestudentModel->db->or_where_in($key, $value, $escape);
		} else {
			$this->_ci->PrestudentModel->db->or_where($key, $value, $escape);
		}
	}

	/**
	 * Generates the query and executes it.
	 *
	 * @param string|null	$studiensemester_kurzbz
	 *
	 * @return stdClass		result of the query
	 */
	public function execute($studiensemester_kurzbz)
	{
		$stdsemEsc = $studiensemester_kurzbz ? $this->_ci->PrestudentModel->escape($studiensemester_kurzbz) : 'NULL';


		$this->addSelect(
			"public.get_rolle_prestudent(
				public.tbl_prestudent.prestudent_id, 
				" . $this->_ci->PrestudentModel->escape($studiensemester_kurzbz) . "
			)  AS statusofsemester"
		);
		$this->addJoin(
			'public.tbl_studentlehrverband v',
			'v.student_uid=s.student_uid AND v.studiensemester_kurzbz' . ($studiensemester_kurzbz ? '=' . $stdsemEsc : ' IS NULL'),
			'LEFT'
		);
		$this->addJoin(
			'public.tbl_prestudentstatus ps',
			'ps.status_kurzbz=public.get_rolle_prestudent(tbl_prestudent.prestudent_id, ' . $stdsemEsc . ') 
				AND ps.prestudent_id=tbl_prestudent.prestudent_id 
				AND ps.studiensemester_kurzbz=public.get_stdsem_prestudent(tbl_prestudent.prestudent_id, ' . $stdsemEsc . ') 
				AND ps.ausbildungssemester=public.get_absem_prestudent(tbl_prestudent.prestudent_id, ' . $stdsemEsc . ')
			',
			'LEFT'
		);

		$this->addWhere('tbl_prestudent.studiengang_kz', $this->_allowedStgs);

		foreach ($this->_joins as $join)
			$this->_ci->PrestudentModel->addJoin($join[0], $join[1], $join[2]);

		foreach ($this->_selects as $select)
			$this->_ci->PrestudentModel->addSelect($select[0], $select[1]);

		$this->_ci->PrestudentModel->addOrder('nachname');
		$this->_ci->PrestudentModel->addOrder('vorname');

		return $this->_ci->PrestudentModel->load();
	}


	//------------------------------------------------------------------------------------------------------------------
	// Protected methods

	/**
	 * Get alias of a table or select statement
	 *
	 * @param string		$select
	 *
	 * @return string
	 */
	final protected function getAliasFromSelect($select)
	{
		if (strpos($select, ' ') !== false) {
			return trim(strrchr($select, ' '));
		}

		if (strpos($select, '.') !== false) {
			return substr(strrchr($select, '.'), 1);
		}

		return $select;
	}

	/**
	 * Get alias of a table or select statement
	 *
	 * @param string|array	$table
	 *
	 * @return string|array
	 */
	final protected function getAliasFromTable($table)
	{
		if (strpos($table, ' ') !== false) {
			return trim(strrchr($table, ' '));
		}

		return $table;
	}
}
