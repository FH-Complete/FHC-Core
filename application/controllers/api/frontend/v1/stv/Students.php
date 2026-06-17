<?php
/**
 * Copyright (C) 2024 fhcomplete.org
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
 * This controller operates between (interface) the JS (GUI) and the back-end
 * Provides data to the ajax get calls about listing students
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 */
class Students extends FHCAPI_Controller
{
	public function __construct()
	{
		$permissions = [];
		$router = load_class('Router');
		$permissions[$router->method] = ['admin:r', 'assistenz:r'];
		parent::__construct($permissions);

		$allowedStgs = $this->permissionlib->getSTG_isEntitledFor('admin') ?: [];
		$allowedStgs = array_merge($allowedStgs, $this->permissionlib->getSTG_isEntitledFor('assistenz') ?: []);
		
		if (!$allowedStgs) {
			$this->_outputAuthError([$router->method => ['admin:r', 'assistenz:r']]);
			exit;
		}

		// Load Libraries
		$this->load->library('PhrasesLib');
		$this->load->library('stv/StudentListLib', ['allowedStgs' => $allowedStgs]);
		$this->loadPhrases(
			array(
				'lehre'
			)
		);
	}

	/**
	 * Routing
	 *
	 * /inout												=> index
	 * /(studiensemester_kurzbz)							=> index
	 * /(studiensemester_kurzbz)/inout						=> index
	 *
	 * /(studiensemester_kurzbz)/inout/incoming				=> getIncoming
	 * /(studiensemester_kurzbz)/inout/outgoing				=> getOutgoing
	 * /(studiensemester_kurzbz)/inout/gemeinsamestudien	=> getGemeinsamestudien
	 *
	 * /(studiengang_kz)/prestudent													=> getPrestudents
	 * /(studiengang_kz)/prestudent/(studiensemester_kurzbz)						=> getPrestudents
	 * /(studiengang_kz)/prestudent/(studiensemester_kurzbz)/(filter)				=> getPrestudents
	 * /(studiengang_kz)/prestudent/(studiensemester_kurzbz)/(filter)/(otherfilter)	=> getPrestudents
	 *
	 * /(studiengang_kz)/(orgform)/prestudent													=> getPrestudentsOrgform
	 * /(studiengang_kz)/(orgform)/prestudent/(studiensemester_kurzbz)							=> getPrestudentsOrgform
	 * /(studiengang_kz)/(orgform)/prestudent/(studiensemester_kurzbz)/(filter)					=> getPrestudentsOrgform
	 * /(studiengang_kz)/(orgform)/prestudent/(studiensemester_kurzbz)/(filter)/(otherfilter)	=> getPrestudentsOrgform
	 *
	 * /(studiensemester_kurzbz)/(studiengang_kz)/(semester)/grp/(gruppe)	=> getStudentsSpezialgruppe
	 *
	 * /(studiensemester_kurzbz)/(studiengang_kz)								=> getStudents
	 * /(studiensemester_kurzbz)/(studiengang_kz)/(semester)					=> getStudents
	 * /(studiensemester_kurzbz)/(studiengang_kz)/(semester)/(verband)			=> getStudents
	 * /(studiensemester_kurzbz)/(studiengang_kz)/(semester)/(verband)/(gruppe) => getStudents
	 *
	 * /(studiensemester_kurzbz)/(studiengang_kz)/(orgform)/(semester)/grp/(gruppe) => getStudentsOrgformSpezialgruppe
	 *
	 * /(studiensemester_kurzbz)/(studiengang_kz)/(orgform)									=> getStudentsOrgform
	 * /(studiensemester_kurzbz)/(studiengang_kz)/(orgform)/(semester)						=> getStudentsOrgform
	 * /(studiensemester_kurzbz)/(studiengang_kz)/(orgform)/(semester)/(verband)			=> getStudentsOrgform
	 * /(studiensemester_kurzbz)/(studiengang_kz)/(orgform)/(semester)/(verband)/(gruppe)	=> getStudentsOrgform
	 *
	 * /(studiensemester_kurzbz)/uid/(student_uid)		=> getStudent
	 * /(studiensemester_kurzbz)/prestudent/(prestudent_id)	=> getPrestudent
	 * /(studiensemester_kurzbz)/person/(person_id)			=> getPerson
	 */

	public function index()
	{
		$this->addMeta('ci_method', __FUNCTION__);
		$this->terminateWithSuccess([]);
	}

	/**
	 * @param string		$studiensemester_kurzbz
	 *
	 * @return void
	 */
	public function getIncoming($studiensemester_kurzbz)
	{
		$this->addMeta('ci_method', __FUNCTION__);
		$this->addMeta('ci_params', [
			'studiensemester_kurzbz' => $studiensemester_kurzbz
		]);
		

		$this->studentlistlib->addJoin(
			"(
				SELECT prestudent_id 
				FROM public.tbl_prestudentstatus
				WHERE status_kurzbz = 'Incoming'
				AND studiensemester_kurzbz = " . $this->PrestudentModel->escape($studiensemester_kurzbz) . "
			) test",
			"prestudent_id",
			"",
			"start"
		);

		$this->studentlistlib->addSelect("COALESCE(
			v.semester::text, 
			CASE 
				WHEN pls.status_kurzbz IN ('Aufgenommener', 'Bewerber', 'Wartender', 'interessent') 
				THEN pls.ausbildungssemester::text 
				ELSE ''::text 
			END
		) AS semester", false);
		$this->studentlistlib->addSelect("COALESCE(v.verband::text, ''::text) AS verband");
		$this->studentlistlib->addSelect("COALESCE(v.gruppe::text, ''::text) AS gruppe");


		$this->addFilter($studiensemester_kurzbz);

		$result = $this->studentlistlib->execute($studiensemester_kurzbz);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	/**
	 * @param string		$studiensemester_kurzbz
	 *
	 * @return void
	 */
	public function getOutgoing($studiensemester_kurzbz)
	{
		$this->addMeta('ci_method', __FUNCTION__);
		$this->addMeta('ci_params', [
			'studiensemester_kurzbz' => $studiensemester_kurzbz
		]);
		

		$this->studentlistlib->addJoin(
			"(
				SELECT prestudent_id 
				FROM bis.tbl_bisio bis 
				JOIN public.tbl_student USING (student_uid)
				JOIN public.tbl_studiensemester stdsem ON (
					(bis.von >= stdsem.start AND bis.von <= stdsem.ende) 
					OR 
					(bis.bis >= stdsem.start AND bis.bis <= stdsem.ende) 
					OR 
					(bis.von <= stdsem.start AND bis.bis >= stdsem.ende)
				)
				WHERE NOT EXISTS (
					SELECT 1 
					FROM public.tbl_prestudentstatus 
					WHERE status_kurzbz = 'Incoming'
					AND prestudent_id = tbl_student.prestudent_id
				) AND stdsem.studiensemester_kurzbz = " . $this->PrestudentModel->escape($studiensemester_kurzbz) . "
				GROUP BY prestudent_id
			) test",
			"prestudent_id",
			"",
			"start"
		);

		$this->studentlistlib->addSelect("COALESCE(
			v.semester::text, 
			CASE 
				WHEN pls.status_kurzbz IN ('Aufgenommener', 'Bewerber', 'Wartender', 'interessent') 
				THEN pls.ausbildungssemester::text 
				ELSE ''::text 
			END
		) AS semester", false);
		$this->studentlistlib->addSelect("COALESCE(v.verband::text, ''::text) AS verband");
		$this->studentlistlib->addSelect("COALESCE(v.gruppe::text, ''::text) AS gruppe");
		

		$this->addFilter($studiensemester_kurzbz);

		$result = $this->studentlistlib->execute($studiensemester_kurzbz);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	/**
	 * @param string		$studiensemester_kurzbz
	 *
	 * @return void
	 */
	public function getGemeinsamestudien($studiensemester_kurzbz)
	{
		$this->addMeta('ci_method', __FUNCTION__);
		$this->addMeta('ci_params', [
			'studiensemester_kurzbz' => $studiensemester_kurzbz
		]);
		

		$this->studentlistlib->addJoin(
			"(
				SELECT prestudent_id
				FROM bis.tbl_mobilitaet 
				WHERE studiensemester_kurzbz = " . $this->PrestudentModel->escape($studiensemester_kurzbz) . "
			) bis",
			"prestudent_id",
			"",
			"start"
		);

		$this->studentlistlib->addSelect("COALESCE(
			v.semester::text, 
			CASE 
				WHEN pls.status_kurzbz IN ('Aufgenommener', 'Bewerber', 'Wartender', 'interessent') 
				THEN pls.ausbildungssemester::text 
				ELSE ''::text 
			END
		) AS semester", false);
		$this->studentlistlib->addSelect("COALESCE(v.verband::text, ''::text) AS verband");
		$this->studentlistlib->addSelect("COALESCE(v.gruppe::text, ''::text) AS gruppe");
		

		$this->addFilter($studiensemester_kurzbz);

		$result = $this->studentlistlib->execute($studiensemester_kurzbz);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getPrestudents(
		$studiengang_kz,
		$studiensemester_kurzbz = null,
		$filter = null
	) {
		$this->addMeta('ci_method', __FUNCTION__);
		$this->addMeta('ci_params', array(
			'studiengang_kz' => $studiengang_kz,
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'filter' => $filter
		));
		
		$this->fetchPrestudents($studiengang_kz, $studiensemester_kurzbz, $filter);
	}

	public function getPrestudentsOrgform(
		$studiengang_kz,
		$orgform_kurzbz,
		$studiensemester_kurzbz = null,
		$filter = null
	) {
		$this->addMeta('ci_method', __FUNCTION__);
		$this->addMeta('ci_params', array(
			'studiengang_kz' => $studiengang_kz,
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'filter' => $filter,
			'orgform_kurzbz' => $orgform_kurzbz
		));
		
		$this->fetchPrestudents($studiengang_kz, $studiensemester_kurzbz, $filter, $orgform_kurzbz);
	}

	/**
	 * @param integer		$studiengang_kz
	 * @param string		$studiensemester_kurzbz			(optional)
	 * @param string		$filter							(optional)
	 * @param string		$orgform_kurzbz					(optional)
	 *
	 * @return void
	 */
	protected function fetchPrestudents($studiengang_kz, $studiensemester_kurzbz = null, $filter = null, $orgform_kurzbz = null)
	{
		$stdsemEsc = $studiensemester_kurzbz ? $this->PrestudentModel->escape($studiensemester_kurzbz) : 'NULL';

		$selectRT = "
			SELECT 1 
			FROM public.tbl_rt_person 
			JOIN public.tbl_reihungstest r ON (rt_id = reihungstest_id) 
			WHERE person_id=p.person_id 
			AND studienplan_id IN (
				SELECT studienplan_id 
				FROM lehre.tbl_studienplan 
				JOIN lehre.tbl_studienordnung o USING(studienordnung_id) 
				WHERE o.studiengang_kz=tbl_prestudent.studiengang_kz
			) 
			AND r.studiensemester_kurzbz=" . $stdsemEsc;

		
		$this->studentlistlib->addWhere('tbl_prestudent.studiengang_kz', $studiengang_kz);

		if ($orgform_kurzbz) {
			$this->studentlistlib->addWhere('ps.orgform_kurzbz', $orgform_kurzbz);
		}

		switch ($filter) {
			case "interessenten":
				$this->studentlistlib->addWhere('ps.status_kurzbz', 'Interessent');
				break;
			case "bewerbungnichtabgeschickt":
				$this->studentlistlib->addWhere('ps.status_kurzbz', 'Interessent');
				$this->studentlistlib->addWhere('ps.bewerbung_abgeschicktamum IS NULL');
				break;
			case "bewerbungabgeschickt":
				$this->studentlistlib->addWhere('ps.status_kurzbz', 'Interessent');
				$this->studentlistlib->addWhere('ps.bewerbung_abgeschicktamum IS NOT NULL');
				$this->studentlistlib->addWhere('ps.bestaetigtam IS NULL');
				break;
			case "statusbestaetigt":
				$this->studentlistlib->addWhere('ps.status_kurzbz', 'Interessent');
				$this->studentlistlib->addWhere('ps.bestaetigtam IS NOT NULL');
				break;
			case "statusbestaetigtrtnichtangemeldet":
				$this->studentlistlib->addWhere('ps.status_kurzbz', 'Interessent');
				$this->studentlistlib->addWhere('ps.bestaetigtam IS NOT NULL');
				$this->studentlistlib->addWhere('NOT EXISTS(' . $selectRT . ')', null, false);
				break;
			case "statusbestaetigtrtangemeldet":
				$this->studentlistlib->addWhere('ps.status_kurzbz', 'Interessent');
				$this->studentlistlib->addWhere('ps.bestaetigtam IS NOT NULL');
				$this->studentlistlib->addWhere('EXISTS(' . $selectRT . ')', null, false);
				break;
			case "zgv":
				$this->load->model('organisation/Studiengang_model', 'StudiengangModel');

				$result = $this->StudiengangModel->load($studiengang_kz);

				$stg = $this->getDataOrTerminateWithError($result);
				if (!$stg)
					$this->terminateWithSuccess([]);
				$stg = current($stg);

				$this->studentlistlib->addWhere('ps.status_kurzbz', 'Interessent');
				
				if ($stg->typ == 'm') {
					$this->studentlistlib->addWhere('zgvmas_code IS NOT NULL');
					if (defined('ZGV_ERFUELLT_ANZEIGEN') && ZGV_ERFUELLT_ANZEIGEN)
						$this->studentlistlib->addWhere('zgvmas_erfuellt', true);
				} elseif ($stg->typ == 'p') {
					$this->studentlistlib->addWhere('zgvdoktor_code IS NOT NULL');
					if (defined('ZGV_DOKTOR_ANZEIGEN') && ZGV_DOKTOR_ANZEIGEN)
						$this->studentlistlib->addWhere('zgvdoktor_erfuellt', true);
				} else {
					$this->studentlistlib->addWhere('zgv_code IS NOT NULL');
					if (defined('ZGV_ERFUELLT_ANZEIGEN') && ZGV_ERFUELLT_ANZEIGEN)
						$this->studentlistlib->addWhere('zgv_erfuellt', true);
				}
				break;
			case "reihungstestangemeldet":
				$this->studentlistlib->addWhere('ps.status_kurzbz', 'Interessent');
				$this->studentlistlib->addWhere('EXISTS(' . $selectRT . ')', null, false);
				break;
			case "reihungstestnichtangemeldet":
				$this->studentlistlib->addWhere('ps.status_kurzbz', 'Interessent');
				$this->studentlistlib->addWhere('NOT EXISTS(' . $selectRT . ')', null, false);
				break;
			case "bewerber":
				$this->studentlistlib->addWhere('ps.status_kurzbz', 'Bewerber');
				break;
			case "bewerberrtnichtangemeldet":
				$this->studentlistlib->addWhere('ps.status_kurzbz', 'Bewerber');
				$this->studentlistlib->addWhere('NOT EXISTS(' . $selectRT . ')', null, false);
				break;
			case "bewerberrtangemeldet":
				$this->studentlistlib->addWhere('ps.status_kurzbz', 'Bewerber');
				$this->studentlistlib->addWhere('EXISTS(' . $selectRT . ')', null, false);
				break;
			case "bewerberrtangemeldetteilgenommen":
				$this->studentlistlib->addWhere('ps.status_kurzbz', 'Bewerber');
				$this->studentlistlib->addWhere('EXISTS(' . $selectRT . ')', null, false);
				$this->studentlistlib->addWhere('reihungstestangetreten', true);
				break;
			case "bewerberrtangemeldetnichtteilgenommen":
				$this->studentlistlib->addWhere('ps.status_kurzbz', 'Bewerber');
				$this->studentlistlib->addWhere('EXISTS(' . $selectRT . ')', null, false);
				$this->studentlistlib->addWhere('reihungstestangetreten', false);
				break;
			case "aufgenommen":
				$this->studentlistlib->addWhere('ps.status_kurzbz', 'Aufgenommener');
				break;
			case "warteliste":
				$this->studentlistlib->addWhere('ps.status_kurzbz', 'Wartender');
				break;
			case "absage":
				$this->studentlistlib->addWhere('ps.status_kurzbz', 'Abgewiesener');
				break;
			case "incoming":
				// NOTE(chris): in FAS it was not filtered for studiengang_kz
				$this->studentlistlib->addWhere('ps.status_kurzbz', 'Incoming');
				break;
			case "absolvent":
				$this->studentlistlib->addWhere('ps.status_kurzbz', 'Absolvent');
				break;
			case "diplomand":
				$this->studentlistlib->addWhere('ps.status_kurzbz', 'Diplomand');
				break;
			default:
				if (!$studiensemester_kurzbz) {
					/** NOTE(chris):
					 * show all prestudents in this stg who don't have a status
					 * $orgform_kurzbz does not change the results since orgform is stored in the status table
					 */
					$this->studentlistlib->addWhere('ps.status_kurzbz IS NULL');
				} else {
					$this->studentlistlib->addWhere('ps.status_kurzbz', [
						'Interessent',
						'Bewerber',
						'Aufgenommener',
						'Wartender',
						'Abgewiesener'
					]);
				}
				break;
		}

		$this->studentlistlib->addSelect("
			CASE 
				WHEN pls.status_kurzbz IN ('Aufgenommener', 'Bewerber', 'Wartender', 'interessent') 
				THEN ps.ausbildungssemester::text 
				ELSE ''::text 
			END AS semester", false);
		$this->studentlistlib->addSelect("'' AS verband");
		$this->studentlistlib->addSelect("'' AS gruppe");

		$query_studiensemester_kurzbz = $studiensemester_kurzbz ? $this->PrestudentModel->escape($studiensemester_kurzbz) : '\'NULL\'';
		$this->studentlistlib->addSelect($query_studiensemester_kurzbz . ' as query_studiensemester_kurzbz');

		$this->addFilter($studiensemester_kurzbz);

		$result = $this->studentlistlib->execute($studiensemester_kurzbz);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getStudents(
		$studiensemester_kurzbz,
		$studiengang_kz,
		$semester = null,
		$verband = null,
		$gruppe = null
	) {
		$this->addMeta('ci_method', __FUNCTION__);
		$this->addMeta('ci_params', array(
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'studiengang_kz' => $studiengang_kz,
			'semester' => $semester,
			'verband' => $verband,
			'gruppe' => $gruppe
		));
		
		$this->fetchStudents($studiensemester_kurzbz, $studiengang_kz, $semester, $verband, $gruppe, null, null);
	}

	public function getStudentsOrgform(
		$studiensemester_kurzbz,
		$studiengang_kz,
		$orgform_kurzbz,
		$semester = null,
		$verband = null,
		$gruppe = null
	) {
		$this->addMeta('ci_method', __FUNCTION__);
		$this->addMeta('ci_params', array(
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'studiengang_kz' => $studiengang_kz,
			'orgform_kurzbz' => $orgform_kurzbz,
			'semester' => $semester,
			'verband' => $verband,
			'gruppe' => $gruppe
		));
		
		$this->fetchStudents($studiensemester_kurzbz, $studiengang_kz, $semester, $verband, $gruppe, null, $orgform_kurzbz);
	}

	public function getStudentsSpezialgruppe(
		$studiensemester_kurzbz,
		$studiengang_kz,
		$semester,
		$gruppe_kurzbz
	) {
		$this->addMeta('ci_method', __FUNCTION__);
		$this->addMeta('ci_params', array(
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'studiengang_kz' => $studiengang_kz,
			'semester' => $semester,
			'gruppe_kurzbz' => $gruppe_kurzbz
		));
		
		$this->fetchStudents($studiensemester_kurzbz, $studiengang_kz, $semester, null, null, $gruppe_kurzbz, null);
	}

	public function getStudentsOrgformSpezialgruppe(
		$studiensemester_kurzbz,
		$orgform_kurzbz,
		$studiengang_kz,
		$semester,
		$gruppe_kurzbz
	) {
		$this->addMeta('ci_method', __FUNCTION__);
		$this->addMeta('ci_params', array(
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'orgform_kurzbz' => $orgform_kurzbz,
			'studiengang_kz' => $studiengang_kz,
			'semester' => $semester,
			'gruppe_kurzbz' => $gruppe_kurzbz
		));
		
		$this->fetchStudents($studiensemester_kurzbz, $studiengang_kz, $semester, null, null, $gruppe_kurzbz, $orgform_kurzbz);
	}

	/**
	 * @param integer		$studiengang_kz
	 * @param string		$studiensemester_kurzbz
	 * @param integer		$semester						(optional)
	 * @param string		$verband						(optional)
	 * @param integer		$gruppe							(optional)
	 * @param string		$gruppe_kurzbz					(optional)
	 * @param string		$orgform_kurzbz					(optional)
	 *
	 * @return void
	 */
	protected function fetchStudents(
		$studiensemester_kurzbz,
		$studiengang_kz,
		$semester = null,
		$verband = null,
		$gruppe = null,
		$gruppe_kurzbz = null,
		$orgform_kurzbz = null
	) {
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		if (!$this->StudiensemesterModel->isValidStudiensemester($studiensemester_kurzbz))
		{
			$this->terminateWithError($studiensemester_kurzbz . ' - ' . $this->p->t('lehre', 'error_noStudiensemester'));
		}

		// NOTE(chris): overwrite 'LEFT JOIN' with 'JOIN'
		$this->studentlistlib->addJoin("public.tbl_student s", "prestudent_id");

		$this->studentlistlib->addSelect("'' AS priorisierung_relativ");
		$this->studentlistlib->addSelect($this->PrestudentModel->escape($studiensemester_kurzbz) . ' as query_studiensemester_kurzbz');

		if ($gruppe_kurzbz !== null) {
			$this->studentlistlib->addJoin('public.tbl_benutzergruppe g', 'uid', '', 'after_b');
			$this->studentlistlib->addWhere('g.gruppe_kurzbz', $gruppe_kurzbz);
			$this->studentlistlib->addWhere('g.studiensemester_kurzbz', $studiensemester_kurzbz);
		} else {
			$this->studentlistlib->addWhere('v.studiengang_kz', $studiengang_kz);

			if ($semester !== null)
				$this->studentlistlib->addWhere('v.semester', $semester);

			if ($verband !== null)
				$this->studentlistlib->addWhere('v.verband', $verband);

			if ($gruppe !== null)
				$this->studentlistlib->addWhere('v.gruppe', $gruppe);
	
			if (!$verband && !$gruppe && $orgform_kurzbz !== null) {
				$this->studentlistlib->addWhere(
					"(
						SELECT orgform_kurzbz
						FROM public.tbl_prestudentstatus 
						WHERE prestudent_id=tbl_prestudent.prestudent_id 
						AND studiensemester_kurzbz=" . $this->PrestudentModel->escape($studiensemester_kurzbz) . " 
						ORDER BY datum DESC, insertamum DESC, ext_id DESC LIMIT 1
					) =",
					$this->PrestudentModel->escape($orgform_kurzbz),
					false
				);
			}
		}


		$this->addFilter($studiensemester_kurzbz);

		$result = $this->studentlistlib->execute($studiensemester_kurzbz);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	/**
	 * @param string		$prestudent_id
	 *
	 * @return void
	 */
	public function getPrestudent($studiensemester_kurzbz, $prestudent_id)
	{
		$this->addMeta('ci_method', __FUNCTION__);
		$this->addMeta('ci_params', array(
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'prestudent_id' => $prestudent_id,
		));

		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		if (!$this->StudiensemesterModel->isValidStudiensemester($studiensemester_kurzbz))
		{
			$this->terminateWithError($studiensemester_kurzbz . ' - ' . $this->p->t('lehre', 'error_noStudiensemester'));
		}

		
		$this->studentlistlib->addSelect("COALESCE(
			v.semester::text, 
			CASE 
				WHEN pls.status_kurzbz IN ('Aufgenommener', 'Bewerber', 'Wartender', 'interessent') 
				THEN pls.ausbildungssemester::text 
				ELSE ''::text 
			END
		) AS semester", false);
		$this->studentlistlib->addSelect("COALESCE(v.verband::text, ''::text) AS verband");
		$this->studentlistlib->addSelect("COALESCE(v.gruppe::text, ''::text) AS gruppe");

		$this->studentlistlib->addWhere('tbl_prestudent.prestudent_id', $prestudent_id);


		$this->addFilter($studiensemester_kurzbz);

		$result = $this->studentlistlib->execute($studiensemester_kurzbz);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	/**
	 * @param string		$student_uid
	 *
	 * @return void
	 */
	public function getStudent($studiensemester_kurzbz, $student_uid)
	{
		$this->addMeta('ci_method', __FUNCTION__);
		$this->addMeta('ci_params', array(
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'student_uid' => $student_uid,
		));

		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		if (!$this->StudiensemesterModel->isValidStudiensemester($studiensemester_kurzbz))
		{
			$this->terminateWithError($studiensemester_kurzbz . ' - ' . $this->p->t('lehre', 'error_noStudiensemester'));
		}


		$this->studentlistlib->addWhere('s.student_uid', $student_uid);
		

		$this->addFilter($studiensemester_kurzbz);

		$result = $this->studentlistlib->execute($studiensemester_kurzbz);
		
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	/**
	 * @param string		$studiensemester_kurzbz
	 * @param integer		$person_id
	 *
	 * @return void
	 */
	public function getPerson($studiensemester_kurzbz, $person_id)
	{
		$this->addMeta('ci_method', __FUNCTION__);
		$this->addMeta('ci_params', array(
			'studiensemester_kurzbz' => $studiensemester_kurzbz,
			'person_id' => $person_id,
		));

		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		if (!$this->StudiensemesterModel->isValidStudiensemester($studiensemester_kurzbz))
		{
			$this->terminateWithError($studiensemester_kurzbz . ' - ' . $this->p->t('lehre', 'error_noStudiensemester'));
		}


		$this->studentlistlib->addWhere('p.person_id', $person_id);

		
		$this->addFilter($studiensemester_kurzbz);

		$result = $this->studentlistlib->execute($studiensemester_kurzbz);
		
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	/**
	 * @param string		$studiensemester_kurzbz
	 *
	 * @return void
	 */
	public function search($studiensemester_kurzbz)
	{
		$this->addMeta('ci_method', __FUNCTION__);
		$this->addMeta('ci_params', array(
			'studiensemester_kurzbz' => $studiensemester_kurzbz
		));

		$this->load->library('SearchLib', [ 'config' => 'searchstv' ]);
		$this->load->library('form_validation');

		$this->form_validation->set_rules('searchstr', 'searchstr', 'required');
		$this->form_validation->set_rules('types[]', 'types', 'required');

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$result = $this->searchlib->search($this->input->post('searchstr'), $this->input->post('types'));

		$data = $this->getDataOrTerminateWithError($result);

		$prestudent_ids = [];
		$student_uids = [];
		foreach ($data as $row) {
			$dataset = json_decode($row->data);
			if ($row->type == 'prestudent') {
				$prestudent_ids[] = $dataset->prestudent_id;
			} elseif ($row->type == 'student') {
				$student_uids[] = $dataset->uid;
			}
		}


		$this->studentlistlib->addSelect("COALESCE(
			v.semester::text, 
			CASE 
				WHEN public.get_rolle_prestudent(tbl_prestudent.prestudent_id, NULL) IN ('Aufgenommener', 'Bewerber', 'Wartender', 'interessent') 
				THEN public.get_absem_prestudent(tbl_prestudent.prestudent_id, NULL)::text 
				ELSE ''::text 
			END
		) AS semester", false);

		$this->studentlistlib->addSelect($this->PrestudentModel->escape($studiensemester_kurzbz) . ' as query_studiensemester_kurzbz');

		if ($prestudent_ids && $student_uids) {
			$this->studentlistlib->addWhere('tbl_prestudent.prestudent_id', $prestudent_ids);
			$this->studentlistlib->addOrWhere('s.student_uid', $student_uids);
		} elseif ($prestudent_ids) {
			$this->studentlistlib->addWhere('tbl_prestudent.prestudent_id', $prestudent_ids);
		} elseif ($student_uids) {
			$this->studentlistlib->addWhere('s.student_uid', $student_uids);
		} else {
			$this->terminateWithSuccess([]);
		}


		$this->addFilter($studiensemester_kurzbz);

		$result = $this->studentlistlib->execute($studiensemester_kurzbz);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	/**
	 * Adds additional filters to the query
	 *
	 * @param string		$studiensemester_kurzbz
	 *
	 * @return void
	 */
	protected function addFilter($studiensemester_kurzbz)
	{
		$filter = $this->input->post('filter');
		
		if (!is_array($filter))
		{
			$this->addMeta('addfilter', 'invalid filter: ' . json_encode($this->input->post('filter')));
			return;
		}
		foreach ($filter as $item) {
			if (isset($item['usestdsem']) && $item['usestdsem'])
				$item['studiensemester_kurzbz'] = $studiensemester_kurzbz;
			if (!$this->PrestudentModel->addFilter($item)) {
				$this->addMeta('addfilter', 'invalid filter: ' . json_encode($item));
				return;
			}
		}
	}
}
