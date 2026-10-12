<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class LVVerwaltungPermissionLib
{
	const BERECHTIGUNG = 'basis/lvverwaltung';

	private $_ci;

	public function __construct()
	{
		$this->_ci =& get_instance();
		$this->_ci->load->library('PermissionLib');
	}

	/**
	 * Überprüft, ob man auf die Lehreinheit berechtigt ist
	 * Berechtigt, wenn man Rechte hat auf:
	 *  die OE der LV der Lehreinheit
	 *  ODER die OE des Studiengangs der LV
	 *  ODER die OE des Lehrfachs der Lehreinheit
	 *  ODER die OE eines Studiengangs, in dessen Studienplan die LV hängt
	 */
	public function isEntitledForLehreinheit($lehreinheit_id, $art = 's', $berechtigungen = null)
	{
		return !empty($this->filterLehreinheitIds(array($lehreinheit_id), $art, $berechtigungen));
	}

	/**
	 * Überprüft, ob man auf die Lehrveranstaltung berechtigt ist
	 * Berechtigt, wenn man Rechte hat auf:
	 *  die OE der LV
	 *  ODER die OE des Studiengangs der LV
	 *  ODER die OE des Lehrfachs irgendeiner Lehreinheit der LV
	 *  ODER die OE eines Studiengangs, in dessen Studienplan die LV hängt
	 */
	public function isEntitledForLV($lehrveranstaltung_id, $art = 's', $berechtigungen = null)
	{
		return !empty($this->filterLVIds(array($lehrveranstaltung_id), $art, true, $berechtigungen));
	}

	/**
	 * Überprüft, ob man eine Lehreinheit mit dieser LV und diesem Lehrfach anlegen bzw. darauf ändern darf
	 * Berechtigt, wenn man Rechte hat auf:
	 *  die OE der LV
	 *  ODER die OE des Studiengangs der LV
	 *  ODER die OE des übergebenen Lehrfachs
	 *  ODER die OE eines Studiengangs, in dessen Studienplan die LV hängt
	 */
	public function isEntitledForLVUndLehrfach($lehrveranstaltung_id, $lehrfach_id, $art = 'suid', $berechtigungen = null)
	{
		$dbModel = new DB_Model();
		$result = $dbModel->execReadOnlyQuery("
			SELECT lv.oe_kurzbz
			FROM lehre.tbl_lehrveranstaltung lv
			WHERE lv.lehrveranstaltung_id = ?

			UNION

			SELECT stg.oe_kurzbz
			FROM lehre.tbl_lehrveranstaltung lv
			JOIN public.tbl_studiengang stg ON (stg.studiengang_kz = lv.studiengang_kz)
			WHERE lv.lehrveranstaltung_id = ?

			UNION

			SELECT lehrfach.oe_kurzbz
			FROM lehre.tbl_lehrveranstaltung lehrfach
			WHERE lehrfach.lehrveranstaltung_id = ?

			UNION

			SELECT stg.oe_kurzbz
			FROM lehre.tbl_studienplan_lehrveranstaltung
			JOIN lehre.tbl_studienplan USING (studienplan_id)
			JOIN lehre.tbl_studienordnung USING (studienordnung_id)
			JOIN public.tbl_studiengang stg USING (studiengang_kz)
			WHERE lehrveranstaltung_id = ?
		", array($lehrveranstaltung_id, $lehrveranstaltung_id, $lehrfach_id, $lehrveranstaltung_id));

		if (isError($result) || !hasData($result))
			return false;

		$oes = array_filter(array_column(getData($result), 'oe_kurzbz'));

		if (empty($oes))
			return false;

		return $this->_isBerechtigt(array_values($oes), $art, $berechtigungen);
	}

	/**
	 * Gibt aus einer Liste von Lehreinheiten die zurück, auf die man berechtigt ist
	 * Gleiche Regel wie isEntitledForLehreinheit
	 */
	public function filterLehreinheitIds($lehreinheit_ids, $art = 's', $berechtigungen = null)
	{
		if (empty($lehreinheit_ids))
			return array();

		$dbModel = new DB_Model();
		$result = $dbModel->execReadOnlyQuery("
			SELECT le.lehreinheit_id AS id, lv.oe_kurzbz
			FROM lehre.tbl_lehreinheit le
			JOIN lehre.tbl_lehrveranstaltung lv ON (lv.lehrveranstaltung_id = le.lehrveranstaltung_id)
			WHERE le.lehreinheit_id IN ?

			UNION

			SELECT le.lehreinheit_id AS id, stg.oe_kurzbz
			FROM lehre.tbl_lehreinheit le
			JOIN lehre.tbl_lehrveranstaltung lv ON (lv.lehrveranstaltung_id = le.lehrveranstaltung_id)
			JOIN public.tbl_studiengang stg ON (stg.studiengang_kz = lv.studiengang_kz)
			WHERE le.lehreinheit_id IN ?

			UNION

			SELECT le.lehreinheit_id AS id, lehrfach.oe_kurzbz
			FROM lehre.tbl_lehreinheit le
			JOIN lehre.tbl_lehrveranstaltung lehrfach ON (lehrfach.lehrveranstaltung_id = le.lehrfach_id)
			WHERE le.lehreinheit_id IN ?

			UNION

			SELECT le.lehreinheit_id AS id, stg.oe_kurzbz
			FROM lehre.tbl_lehreinheit le
			JOIN lehre.tbl_studienplan_lehrveranstaltung USING (lehrveranstaltung_id)
			JOIN lehre.tbl_studienplan USING (studienplan_id)
			JOIN lehre.tbl_studienordnung USING (studienordnung_id)
			JOIN public.tbl_studiengang stg USING (studiengang_kz)
			WHERE le.lehreinheit_id IN ?
		", array($lehreinheit_ids, $lehreinheit_ids, $lehreinheit_ids, $lehreinheit_ids));

		return $this->_filter($result, $art, $berechtigungen);
	}

	/**
	 * Gibt aus einer Liste von LVs die zurück, auf die man berechtigt ist
	 * Gleiche Regel wie isEntitledForLV, aber eine Abfrage für alle
	 * $auchUeberLehrfach = false: Lehrfach-OEs der Lehreinheiten zählen nicht
	 */
	public function filterLVIds($lehrveranstaltung_ids, $art = 's', $auchUeberLehrfach = true, $berechtigungen = null)
	{
		if (empty($lehrveranstaltung_ids))
			return array();

		$qry = "
			SELECT lv.lehrveranstaltung_id AS id, lv.oe_kurzbz
			FROM lehre.tbl_lehrveranstaltung lv
			WHERE lv.lehrveranstaltung_id IN ?

			UNION

			SELECT lv.lehrveranstaltung_id AS id, stg.oe_kurzbz
			FROM lehre.tbl_lehrveranstaltung lv
			JOIN public.tbl_studiengang stg ON (stg.studiengang_kz = lv.studiengang_kz)
			WHERE lv.lehrveranstaltung_id IN ?

			UNION

			SELECT lehrveranstaltung_id AS id, stg.oe_kurzbz
			FROM lehre.tbl_studienplan_lehrveranstaltung
			JOIN lehre.tbl_studienplan USING (studienplan_id)
			JOIN lehre.tbl_studienordnung USING (studienordnung_id)
			JOIN public.tbl_studiengang stg USING (studiengang_kz)
			WHERE lehrveranstaltung_id IN ?
		";
		$params = array($lehrveranstaltung_ids, $lehrveranstaltung_ids, $lehrveranstaltung_ids);

		if ($auchUeberLehrfach)
		{
			$qry .= "
			UNION

			SELECT le.lehrveranstaltung_id AS id, lehrfach.oe_kurzbz
			FROM lehre.tbl_lehreinheit le
			JOIN lehre.tbl_lehrveranstaltung lehrfach ON (lehrfach.lehrveranstaltung_id = le.lehrfach_id)
			WHERE le.lehrveranstaltung_id IN ?
			";
			$params[] = $lehrveranstaltung_ids;
		}

		$dbModel = new DB_Model();
		$result = $dbModel->execReadOnlyQuery($qry, $params);

		return $this->_filter($result, $art, $berechtigungen);
	}

	/**
	 * Gibt die ids zurück, bei denen man auf mind. eine der OEs berechtigt ist
	 */
	private function _filter($result, $art, $berechtigungen)
	{
		if (isError($result) || !hasData($result))
			return array();

		$oe_berechtigt = array();
		$ids = array();

		foreach (getData($result) as $row)
		{
			$oe = $row->oe_kurzbz;

			if (isEmptyString($oe))
				continue;

			if (!isset($oe_berechtigt[$oe]))
				$oe_berechtigt[$oe] = $this->_isBerechtigt(array($oe), $art, $berechtigungen);

			if ($oe_berechtigt[$oe])
				$ids[$row->id] = $row->id;
		}

		return array_values($ids);
	}

	private function _isBerechtigt($oes, $art, $berechtigungen)
	{
		if (is_null($berechtigungen))
			$berechtigungen = array(self::BERECHTIGUNG);

		foreach ((array)$berechtigungen as $berechtigung)
		{
			if ($this->_ci->permissionlib->isBerechtigtMultipleOe($berechtigung, $oes, $art))
				return true;
		}

		return false;
	}
}