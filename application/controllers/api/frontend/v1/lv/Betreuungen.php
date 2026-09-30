<?php
/**
 * Copyright (C) 2026 fhcomplete.org
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

class Betreuungen extends FHCAPI_Controller
{

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'getBetreuungen' => self::PERM_LOGGED,
		]);

	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	public function getBetreuungen()
	{
		$semester = $this->input->get("semester");
		if (!$semester) $this->terminateWithError("Missing parameters!");

		$personId = getAuthPersonId();

		$betreuungenQuery = "SELECT
			tbl_lehrveranstaltung.bezeichnung,
			tbl_projektarbeit.titel,
			(SELECT nachname || ' ' || vorname
				FROM public.tbl_benutzer
				JOIN public.tbl_person USING(person_id)
				WHERE uid=student_uid)
				as name,
			(SELECT uid
				FROM public.tbl_benutzer
				WHERE uid=student_uid)
				as uid,
			tbl_lehrveranstaltung.studiengang_kz,
			tbl_lehrveranstaltung.semester,
			tbl_studiengang.kurzbzlang as studiengang,
			tbl_studiengang.email,
			tbl_betreuerart.beschreibung AS beutreuerart_beschreibung,
			tbl_projektbetreuer.stunden
			FROM
				lehre.tbl_lehrveranstaltung, lehre.tbl_projektarbeit, lehre.tbl_projektbetreuer, public.tbl_studiengang, lehre.tbl_betreuerart
			WHERE
				tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_projektarbeit.lehrveranstaltung_id AND
				tbl_projektarbeit.studiensemester_kurzbz = '$semester' AND
				tbl_projektarbeit.projektarbeit_id = tbl_projektbetreuer.projektarbeit_id AND
				tbl_lehrveranstaltung.studiengang_kz = tbl_studiengang.studiengang_kz AND
				tbl_projektbetreuer.betreuerart_kurzbz = tbl_betreuerart.betreuerart_kurzbz AND
				tbl_projektbetreuer.person_id = '$personId'";
		$this->addMeta("query", $betreuungenQuery);

		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		$result = $this->LehrveranstaltungModel->execReadOnlyQuery($betreuungenQuery);
		$result = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($result);
	}

}
