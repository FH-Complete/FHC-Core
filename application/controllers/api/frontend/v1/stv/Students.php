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
	private $allowedStgs = [];


	public function __construct()
	{
		$permissions = [];
		$router = load_class('Router');
		$permissions[$router->method] = ['admin:r', 'assistenz:r'];
		parent::__construct($permissions);

		$this->allowedStgs = $this->permissionlib->getSTG_isEntitledFor('admin') ?: [];
		$this->allowedStgs = array_merge($this->allowedStgs, $this->permissionlib->getSTG_isEntitledFor('assistenz') ?: []);
		
		if (!$this->allowedStgs) {
			$this->_outputAuthError([$router->method => ['admin:r', 'assistenz:r']]);
			exit;
		}

		// Load Libraries
		$this->load->library('PhrasesLib');
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
		

		$this->load->model('crm/Prestudent_model', 'PrestudentModel');


		$this->PrestudentModel->addJoin(
			"(
				SELECT prestudent_id 
				FROM public.tbl_prestudentstatus
				WHERE status_kurzbz = 'Incoming'
				AND studiensemester_kurzbz = " . $this->PrestudentModel->escape($studiensemester_kurzbz) . "
			) test",
			"prestudent_id"
		);


		$this->prepareQuery($studiensemester_kurzbz);

		$this->PrestudentModel->addSelect("COALESCE(
			v.semester::text, 
			CASE 
				WHEN pls.status_kurzbz IN ('Aufgenommener', 'Bewerber', 'Wartender', 'interessent') 
				THEN pls.ausbildungssemester::text 
				ELSE ''::text 
			END
		) AS semester", false);
		$this->PrestudentModel->addSelect("COALESCE(v.verband::text, ''::text)");
		$this->PrestudentModel->addSelect("COALESCE(v.gruppe::text, ''::text)");
		
		$this->addSelectPrioRel();

		$this->addFilter($studiensemester_kurzbz);


		$result = $this->PrestudentModel->load();
		

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
		

		$this->load->model('crm/Prestudent_model', 'PrestudentModel');


		$this->PrestudentModel->addJoin(
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
			"prestudent_id"
		);


		$this->prepareQuery($studiensemester_kurzbz);


		$this->PrestudentModel->addSelect("COALESCE(
			v.semester::text, 
			CASE 
				WHEN pls.status_kurzbz IN ('Aufgenommener', 'Bewerber', 'Wartender', 'interessent') 
				THEN pls.ausbildungssemester::text 
				ELSE ''::text 
			END
		) AS semester", false);
		$this->PrestudentModel->addSelect("COALESCE(v.verband::text, ''::text)");
		$this->PrestudentModel->addSelect("COALESCE(v.gruppe::text, ''::text)");
		
		$this->addSelectPrioRel();

		$this->addFilter($studiensemester_kurzbz);


		$result = $this->PrestudentModel->load();
		

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
		

		$this->load->model('crm/Prestudent_model', 'PrestudentModel');


		$this->PrestudentModel->addJoin(
			"(
				SELECT prestudent_id
				FROM bis.tbl_mobilitaet 
				WHERE studiensemester_kurzbz = " . $this->PrestudentModel->escape($studiensemester_kurzbz) . "
			) bis",
			"prestudent_id"
		);


		$this->prepareQuery($studiensemester_kurzbz);


		$this->PrestudentModel->addSelect("COALESCE(
			v.semester::text, 
			CASE 
				WHEN pls.status_kurzbz IN ('Aufgenommener', 'Bewerber', 'Wartender', 'interessent') 
				THEN pls.ausbildungssemester::text 
				ELSE ''::text 
			END
		) AS semester", false);
		$this->PrestudentModel->addSelect("COALESCE(v.verband::text, ''::text)");
		$this->PrestudentModel->addSelect("COALESCE(v.gruppe::text, ''::text)");
		
		$this->addSelectPrioRel();

		$this->addFilter($studiensemester_kurzbz);


		$result = $this->PrestudentModel->load();
		

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
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');

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

		
		$where = ['tbl_prestudent.studiengang_kz' => $studiengang_kz];

		if ($orgform_kurzbz) {
			$where['ps.orgform_kurzbz'] = $orgform_kurzbz;
		}

		switch ($filter) {
			case "interessenten":
				$where['ps.status_kurzbz'] = 'Interessent';
				break;
			case "bewerbungnichtabgeschickt":
				$where['ps.status_kurzbz'] = 'Interessent';
				$where['ps.bewerbung_abgeschicktamum'] = null;
				break;
			case "bewerbungabgeschickt":
				$where['ps.status_kurzbz'] = 'Interessent';
				$where['ps.bewerbung_abgeschicktamum IS NOT NULL'] = null;
				$where['ps.bestaetigtam'] = null;
				break;
			case "statusbestaetigt":
				$where['ps.status_kurzbz'] = 'Interessent';
				$where['ps.bestaetigtam IS NOT NULL'] = null;
				break;
			case "statusbestaetigtrtnichtangemeldet":
				$where['ps.status_kurzbz'] = 'Interessent';
				$where['ps.bestaetigtam IS NOT NULL'] = null;
				$this->PrestudentModel->db->where('NOT EXISTS(' . $selectRT . ')', null, false);
				break;
			case "statusbestaetigtrtangemeldet":
				$where['ps.status_kurzbz'] = 'Interessent';
				$where['ps.bestaetigtam IS NOT NULL'] = null;
				$this->PrestudentModel->db->where('EXISTS(' . $selectRT . ')', null, false);
				break;
			case "zgv":
				$this->load->model('organisation/Studiengang_model', 'StudiengangModel');

				$result = $this->StudiengangModel->load($studiengang_kz);

				$stg = $this->getDataOrTerminateWithError($result);
				if (!$stg)
					$this->terminateWithSuccess([]);
				$stg = current($stg);

				$where['ps.status_kurzbz'] = 'Interessent';
				
				if ($stg->typ == 'm') {
					$where['zgvmas_code IS NOT NULL'] = null;
					if (defined('ZGV_ERFUELLT_ANZEIGEN') && ZGV_ERFUELLT_ANZEIGEN)
						$where['zgvmas_erfuellt'] = true;
				} elseif ($stg->typ == 'p') {
					$where['zgvdoktor_code IS NOT NULL'] = null;
					if (defined('ZGV_DOKTOR_ANZEIGEN') && ZGV_DOKTOR_ANZEIGEN)
						$where['zgvdoktor_erfuellt'] = true;
				} else {
					$where['zgv_code IS NOT NULL'] = null;
					if (defined('ZGV_ERFUELLT_ANZEIGEN') && ZGV_ERFUELLT_ANZEIGEN)
						$where['zgv_erfuellt'] = true;
				}
				break;
			case "reihungstestangemeldet":
				$where['ps.status_kurzbz'] = 'Interessent';
				$this->PrestudentModel->db->where('EXISTS(' . $selectRT . ')', null, false);
				break;
			case "reihungstestnichtangemeldet":
				$where['ps.status_kurzbz'] = 'Interessent';
				$this->PrestudentModel->db->where('NOT EXISTS(' . $selectRT . ')', null, false);
				break;
			case "bewerber":
				$where['ps.status_kurzbz'] = 'Bewerber';
				break;
			case "bewerberrtnichtangemeldet":
				$where['ps.status_kurzbz'] = 'Bewerber';
				$this->PrestudentModel->db->where('NOT EXISTS(' . $selectRT . ')', null, false);
				break;
			case "bewerberrtangemeldet":
				$where['ps.status_kurzbz'] = 'Bewerber';
				$this->PrestudentModel->db->where('EXISTS(' . $selectRT . ')', null, false);
				break;
			case "bewerberrtangemeldetteilgenommen":
				$where['ps.status_kurzbz'] = 'Bewerber';
				$this->PrestudentModel->db->where('EXISTS(' . $selectRT . ')', null, false);
				$where['reihungstestangetreten'] = true;
				break;
			case "bewerberrtangemeldetnichtteilgenommen":
				$where['ps.status_kurzbz'] = 'Bewerber';
				$this->PrestudentModel->db->where('EXISTS(' . $selectRT . ')', null, false);
				$where['reihungstestangetreten'] = false;
				break;
			case "aufgenommen":
				$where['ps.status_kurzbz'] = 'Aufgenommener';
				break;
			case "warteliste":
				$where['ps.status_kurzbz'] = 'Wartender';
				break;
			case "absage":
				$where['ps.status_kurzbz'] = 'Abgewiesener';
				break;
			case "incoming":
				// NOTE(chris): in FAS it was not filtered for studiengang_kz
				$where['ps.status_kurzbz'] = 'Incoming';
				break;
			case "absolvent":
				$where['ps.status_kurzbz'] = 'Absolvent';
				break;
			case "diplomand":
				$where['ps.status_kurzbz'] = 'Diplomand';
				break;
			default:
				if (!$studiensemester_kurzbz) {
					/** NOTE(chris):
					 * show all prestudents in this stg who don't have a status
					 * $orgform_kurzbz does not change the results since orgform is stored in the status table
					 */
					$where['ps.status_kurzbz'] = null;
				} else {
					$this->PrestudentModel->db->where_in('ps.status_kurzbz', [
						'Interessent',
						'Bewerber',
						'Aufgenommener',
						'Wartender',
						'Abgewiesener'
					]);
				}
				break;
		}

		$this->prepareQuery($studiensemester_kurzbz);
		
		$this->PrestudentModel->addSelect("
			CASE 
				WHEN pls.status_kurzbz IN ('Aufgenommener', 'Bewerber', 'Wartender', 'interessent') 
				THEN ps.ausbildungssemester::text 
				ELSE ''::text 
			END AS semester", false);
		$this->PrestudentModel->addSelect("'' AS verband");
		$this->PrestudentModel->addSelect("'' AS gruppe");
		$this->addSelectPrioRel();

		$this->addFilter($studiensemester_kurzbz);

		$result = $this->PrestudentModel->loadWhere($where);

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
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		if (!$this->StudiensemesterModel->isValidStudiensemester($studiensemester_kurzbz))
		{
			$this->terminateWithError($studiensemester_kurzbz . ' - ' . $this->p->t('lehre', 'error_noStudiensemester'));
		}

		$this->prepareQuery($studiensemester_kurzbz, '');

		$this->PrestudentModel->addSelect('v.semester');
		$this->PrestudentModel->addSelect('v.verband');
		$this->PrestudentModel->addSelect('v.gruppe');
		$this->PrestudentModel->addSelect("'' AS priorisierung_relativ");


		$where = [];

		if ($gruppe_kurzbz !== null) {
			$this->PrestudentModel->addJoin('public.tbl_benutzergruppe g', 'uid');
			$where['g.gruppe_kurzbz'] = $gruppe_kurzbz;
			$where['g.studiensemester_kurzbz'] = $studiensemester_kurzbz;
		} else {
			$where['v.studiengang_kz'] = $studiengang_kz;

			if ($semester !== null)
				$where['v.semester'] = $semester;

			if ($verband !== null)
				$where['v.verband'] = $verband;

			if ($gruppe !== null)
				$where['v.gruppe'] = $gruppe;
	
			if (!$verband && !$gruppe && $orgform_kurzbz !== null) {
				$this->PrestudentModel->db->where(
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

		$result = $this->PrestudentModel->loadWhere($where);
		
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

		$this->load->model('crm/Prestudent_model', 'PrestudentModel');

		$this->prepareQuery($studiensemester_kurzbz);

		$this->PrestudentModel->addSelect("COALESCE(
			v.semester::text, 
			CASE 
				WHEN pls.status_kurzbz IN ('Aufgenommener', 'Bewerber', 'Wartender', 'interessent') 
				THEN pls.ausbildungssemester::text 
				ELSE ''::text 
			END
		) AS semester", false);
		$this->PrestudentModel->addSelect("COALESCE(v.verband::text, ''::text)");
		$this->PrestudentModel->addSelect("COALESCE(v.gruppe::text, ''::text)");

		$this->addSelectPrioRel();

		$this->addFilter($studiensemester_kurzbz);

		$result = $this->PrestudentModel->loadWhere([
			'tbl_prestudent.prestudent_id' => $prestudent_id
		]);

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

		$this->load->model('crm/Prestudent_model', 'PrestudentModel');

		$this->prepareQuery($studiensemester_kurzbz);

		$this->PrestudentModel->addSelect('v.semester');
		$this->PrestudentModel->addSelect('v.verband');
		$this->PrestudentModel->addSelect('v.gruppe');

		$this->addSelectPrioRel();



		$this->addFilter($studiensemester_kurzbz);

		$result = $this->PrestudentModel->loadWhere([
			's.student_uid' => $student_uid
		]);
		
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

		$this->load->model('crm/Prestudent_model', 'PrestudentModel');

		$this->prepareQuery($studiensemester_kurzbz);

		$this->PrestudentModel->addSelect('v.semester');
		$this->PrestudentModel->addSelect('v.verband');
		$this->PrestudentModel->addSelect('v.gruppe');

		$this->addSelectPrioRel();

		$this->addFilter($studiensemester_kurzbz);

		$result = $this->PrestudentModel->loadWhere([
			'p.person_id' => $person_id
		]);
		
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


		$this->load->model('crm/Prestudent_model', 'PrestudentModel');

		$this->prepareQuery($studiensemester_kurzbz);

		$this->PrestudentModel->addSelect("COALESCE(v.semester::text, CASE WHEN public.get_rolle_prestudent(tbl_prestudent.prestudent_id, NULL) IN ('Aufgenommener', 'Bewerber', 'Wartender', 'interessent') THEN public.get_absem_prestudent(tbl_prestudent.prestudent_id, NULL)::text ELSE ''::text END) AS semester", false);
		$this->PrestudentModel->addSelect('v.verband');
		$this->PrestudentModel->addSelect('v.gruppe');

		//add status per semester
		$this->PrestudentModel->addSelect(
			"(
				SELECT status_kurzbz
				FROM public.tbl_prestudentstatus pss
				WHERE pss.prestudent_id = public.tbl_prestudent.prestudent_id
				  AND pss.studiensemester_kurzbz = " . $this->PrestudentModel->escape($studiensemester_kurzbz) . "
				ORDER BY GREATEST(pss.datum, '0001-01-01') DESC
				LIMIT 1
				) AS statusofsemester"
		);

		$this->addSelectPrioRel();

		$this->addFilter($studiensemester_kurzbz);

		$prestudent_ids = [];
		$student_uids = [];
			$this->addMeta('data', $data);
		foreach ($data as $row) {
			$dataset = json_decode($row->data);
			if ($row->type == 'prestudent') {
				$prestudent_ids[] = $dataset->prestudent_id;
			} elseif ($row->type == 'student') {
				$student_uids[] = $dataset->uid;
			}
		}

		if ($prestudent_ids && $student_uids) {
			$this->PrestudentModel->db->where_in('tbl_prestudent.prestudent_id', $prestudent_ids);
			$this->PrestudentModel->db->or_where_in('s.student_uid', $student_uids);
		} elseif ($prestudent_ids) {
			$this->PrestudentModel->db->where_in('tbl_prestudent.prestudent_id', $prestudent_ids);
		} elseif ($student_uids) {
			$this->PrestudentModel->db->where_in('s.student_uid', $student_uids);
		} else {
			$this->terminateWithSuccess([]);
		}

		$result = $this->PrestudentModel->load();

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	/**
	 * @param string|null	$studiensemester_kurzbz
	 * @param string		$type
	 *
	 * @return void
	 */
	protected function prepareQuery($studiensemester_kurzbz, $type = 'LEFT')
	{
		$stdsemEsc = $studiensemester_kurzbz ? $this->PrestudentModel->escape($studiensemester_kurzbz) : 'NULL';

		$this->load->config('stv');
		$tags = $this->config->item('stv_prestudent_tags');

		$whereTags = '';
		if (is_array($tags) && !isEmptyArray($tags)) {
			$tags = array_keys($tags);

			foreach ($tags as $key => $tag) {
				$tags[$key] = $this->db->escape($tag);
			}
			$whereTags = " AND nt.typ_kurzbz IN (" . implode(",", $tags) . ")";
		}
		$subQueryTag = "
		  (
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
				JOIN public.tbl_notiz_typ AS nt ON n.typ = nt.typ_kurzbz "
			. $whereTags .
			"
			) AS tag
			GROUP BY tag.prestudent_id
		  ) AS tag_data_agg
		";

		$this->PrestudentModel->addJoin('public.tbl_studiengang stg', 'studiengang_kz', 'LEFT');
		$this->PrestudentModel->addJoin('public.tbl_person p', 'person_id');
		$this->PrestudentModel->addJoin('public.tbl_student s', 'prestudent_id', $type);
		$this->PrestudentModel->addJoin('public.tbl_prestudentstatus pls', '
			pls.status_kurzbz=public.get_rolle_prestudent(tbl_prestudent.prestudent_id, NULL) 
			AND pls.prestudent_id=tbl_prestudent.prestudent_id 
			AND pls.studiensemester_kurzbz=public.get_stdsem_prestudent(tbl_prestudent.prestudent_id, NULL) 
			AND pls.ausbildungssemester=public.get_absem_prestudent(tbl_prestudent.prestudent_id, NULL)', 'LEFT');
		$this->PrestudentModel->addJoin('lehre.tbl_studienplan sp', 'studienplan_id', 'LEFT');
		$this->PrestudentModel->addJoin('public.tbl_benutzer b', 's.student_uid=b.uid', 'LEFT');
		$this->PrestudentModel->addJoin(
			'public.tbl_studentlehrverband v',
			'v.student_uid=s.student_uid AND v.studiensemester_kurzbz' . ($studiensemester_kurzbz ? '=' . $stdsemEsc : ' IS NULL'),
			$type
		);
		$this->PrestudentModel->addJoin('public.tbl_prestudentstatus ps', '
			ps.status_kurzbz=public.get_rolle_prestudent(tbl_prestudent.prestudent_id, ' . $stdsemEsc . ') 
			AND ps.prestudent_id=tbl_prestudent.prestudent_id 
			AND ps.studiensemester_kurzbz=public.get_stdsem_prestudent(tbl_prestudent.prestudent_id, ' . $stdsemEsc . ') 
			AND ps.ausbildungssemester=public.get_absem_prestudent(tbl_prestudent.prestudent_id, ' . $stdsemEsc . ')', 'LEFT');

		$this->PrestudentModel->addJoin($subQueryTag, 'tag_data_agg.prestudent_id = tbl_prestudent.prestudent_id', 'LEFT');


		$this->PrestudentModel->addSelect("b.uid");
		$this->PrestudentModel->addSelect('tag_data_agg.tags');
		$this->PrestudentModel->addSelect('titelpre');
		$this->PrestudentModel->addSelect('nachname');
		$this->PrestudentModel->addSelect('vorname');
		$this->PrestudentModel->addSelect('wahlname');
		$this->PrestudentModel->addSelect('vornamen');
		$this->PrestudentModel->addSelect('titelpost');
		$this->PrestudentModel->addSelect('ersatzkennzeichen');
		$this->PrestudentModel->addSelect('gebdatum');
		$this->PrestudentModel->addSelect('geschlecht');
		$this->PrestudentModel->addSelect('foto');
		$this->PrestudentModel->addSelect('foto_sperre');

		// semester
		// verband
		// gruppe

		//add status per semester
		$this->PrestudentModel->addSelect(
			"(
				SELECT status_kurzbz
				FROM public.tbl_prestudentstatus pss
				WHERE pss.prestudent_id = public.tbl_prestudent.prestudent_id
				  AND pss.studiensemester_kurzbz = " . $this->PrestudentModel->escape($studiensemester_kurzbz) . "
				ORDER BY GREATEST(pss.datum, '0001-01-01') DESC
				LIMIT 1
				) AS statusofsemester"
		);

		$this->PrestudentModel->addSelect('UPPER(stg.typ || stg.kurzbz) AS studiengang');
		$this->PrestudentModel->addSelect('tbl_prestudent.studiengang_kz');
		$this->PrestudentModel->addSelect('stg.bezeichnung AS stg_bezeichnung');
		$this->PrestudentModel->addSelect("s.matrikelnr");
		$this->PrestudentModel->addSelect('p.person_id');
		$this->PrestudentModel->addSelect('pls.status_kurzbz AS status');
		$this->PrestudentModel->addSelect('pls.datum AS status_datum');
		$this->PrestudentModel->addSelect('pls.bestaetigtam AS status_bestaetigung');
		$this->PrestudentModel->addSelect(
			"(SELECT kontakt FROM public.tbl_kontakt WHERE kontakttyp='email' AND person_id=p.person_id AND zustellung LIMIT 1) AS mail_privat",
			false
		);
		$this->PrestudentModel->addSelect("
			CASE WHEN b.uid IS NOT NULL AND b.uid<>'' 
			THEN CONCAT(b.uid, '@', " . $this->PrestudentModel->escape(DOMAIN) . ")
			ELSE '' END AS mail_intern", false);
		$this->PrestudentModel->addSelect('p.anmerkung AS anmerkungen');
		$this->PrestudentModel->addSelect('tbl_prestudent.anmerkung');
		$this->PrestudentModel->addSelect('pls.orgform_kurzbz');
		$this->PrestudentModel->addSelect('aufmerksamdurch_kurzbz');
		$this->PrestudentModel->addSelect(
			"(SELECT rt_gesamtpunkte AS punkte FROM public.tbl_prestudent WHERE prestudent_id=ps.prestudent_id) AS punkte",
			false
		);
		$this->PrestudentModel->addSelect('tbl_prestudent.aufnahmegruppe_kurzbz');
		$this->PrestudentModel->addSelect('tbl_prestudent.dual');
		$this->PrestudentModel->addSelect('p.matr_nr');
		$this->PrestudentModel->addSelect('sp.bezeichnung AS studienplan_bezeichnung');
		$this->PrestudentModel->addSelect('tbl_prestudent.prestudent_id');

		// priorisierung_relativ

		$this->PrestudentModel->addSelect('mentor');
		$this->PrestudentModel->addSelect('b.aktiv AS bnaktiv');
		$this->PrestudentModel->addSelect('unruly');

		$this->PrestudentModel->db->where_in('tbl_prestudent.studiengang_kz', $this->allowedStgs);

		$this->PrestudentModel->addOrder('nachname');
		$this->PrestudentModel->addOrder('vorname');
	}

	/**
	 * @return void
	 */
	protected function addSelectPrioRel()
	{
		$this->PrestudentModel->addSelect("(
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
		) || ' (' || COALESCE(tbl_prestudent.priorisierung::text, ' '::text) || ')' AS priorisierung_relativ", false);
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
