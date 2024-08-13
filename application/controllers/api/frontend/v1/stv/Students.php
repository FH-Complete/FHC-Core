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
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
	}

	/**
	 * Remap calls:
	 * /													=> return []
	 * /inout												=> return []
	 * /inout/incoming										=> getIncoming
	 * /inout/outgoing										=> getOutgoing
	 * /inout/gemeinsamestudien								=> getGemeinsamestudien
	 * /(studiengang_kz)									=> getStudents
	 * /(studiengang_kz)/prestudent							=> getPrestudents
	 * /(studiengang_kz)/prestudent/*						=> getPrestudents
	 * /(studiengang_kz)/(semester)							=> getStudents
	 * /(studiengang_kz)/(semester)/grp/(gruppe_kurzbz)		=> getStudents
	 * /(studiengang_kz)/(semester)/(verband)				=> getStudents
	 * /(studiengang_kz)/(semester)/(verband)/(gruppe)		=> getStudents
	 * /(studiengang_kz)/(org_form)							=> getStudents
	 * /(studiengang_kz)/(org_form)/prestudent				=> getPrestudents
	 * /(studiengang_kz)/(org_form)/prestudent/*			=> getPrestudents
	 * /(studiengang_kz)/(org_form)/(semester)				=> getStudents
	 * /(studiengang_kz)/(org_form)/(semester)/grp/(gruppe_kurzbz)
	 * 														=> getStudents
	 * /(studiengang_kz)/(org_form)/(semester)/(verband)	=> getStudents
	 * /(studiengang_kz)/(org_form)/(semester)/(verband)/(gruppe)
	 * 														=> getStudents
	 * /uid/(student_uid)									=> getStudent
	 * /prestudent/(prestudent_id)							=> getPrestudent
	 * /person/(person_id)									=> getPerson
	 *
	 * @param string		$method
	 * @param array			$params				(optional)
	 *
	 * @return void
	 */
	public function _remap($method, $params = [])
	{
		if ($method == '' || $method == 'index')
			return $this->terminateWithSuccess([]);

		if ($method == 'inout') {
			if (!count($params))
				return $this->terminateWithSuccess([]);
			switch ($params[0]) {
				case 'incoming':
					return $this->getIncoming();
				case 'outgoing':
					return $this->getOutgoing();
				case 'gemeinsamestudien':
					return $this->getGemeinsamestudien();
				default:
					return show_404();
			}
		}

		$count = count($params);
		if (!$count)
			return $this->getStudents($method);

		if ($method == 'uid' && $count == 1)
			return $this->getStudent($params[0]);

		if ($method == 'prestudent' && $count == 1)
			return $this->getPrestudent($params[0]);

		if ($method == 'person' && $count == 1)
			return $this->getPerson($params[0]);

		if (is_numeric($params[0])) {
			$sem = $params[0];
			if ($count == 3 && $params[1] == 'grp') {
				$g = $params[2];
				$ver = null;
				$grp = null;
			} else {
				$g = null;
				$ver = $count > 1 ? $params[1] : null;
				$grp = $count > 2 ? $params[2] : null;
			}
			return $this->getStudents($method, $sem, $ver, $grp, $g);
		} elseif ($params[0] == 'prestudent') {
			if ($count == 1)
				return $this->getPrestudents($method);
			if ($count == 2)
				return $this->getPrestudents($method, $params[1]);
			return $this->getPrestudents($method, $params[1], $params[$count-1]);
		} else {
			$org = $params[0];
			if ($count > 1 && $params[1] == 'prestudent') {
				if ($count == 2)
					return $this->getPrestudents($method, null, null, $org);
				if ($count == 3)
					return $this->getPrestudents($method, $params[2], null, $org);
				return $this->getPrestudents($method, $params[2], $params[$count-1], $org);
			}
			$sem = $count > 1 ? $params[1] : null;
			if ($count == 4 && $params[2] == 'grp') {
				$g = $params[3];
				$ver = null;
				$grp = null;
			} else {
				$g = null;
				$ver = $count > 2 ? $params[2] : null;
				$grp = $count > 3 ? $params[3] : null;
			}

			return $this->getStudents($method, $sem, $ver, $grp, $g, $org);
		}
		
		show_404();
	}

	/**
	 * @return void
	 */
	protected function getIncoming()
	{
		// TODO(chris): IMPLEMENT!
		$this->terminateWithSuccess([]);
	}

	/**
	 * @return void
	 */
	protected function getOutgoing()
	{
		// TODO(chris): IMPLEMENT!
		$this->terminateWithSuccess([]);
	}

	/**
	 * @return void
	 */
	protected function getGemeinsamestudien()
	{
		// TODO(chris): IMPLEMENT!
		$this->terminateWithSuccess([]);
	}

	/**
	 * @param integer		$studiengang_kz
	 * @param string		$studiensemester_kurzbz			(optional)
	 * @param string		$filter							(optional)
	 * @param string		$orgform_kurzbz					(optional)
	 *
	 * @return void
	 */
	protected function getPrestudents($studiengang_kz, $studiensemester_kurzbz = null, $filter = null, $orgform_kurzbz = null)
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
				$where['bewerbung_abgeschicktamum'] = null;
				break;
			case "bewerbungabgeschickt":
				$where['ps.status_kurzbz'] = 'Interessent';
				$where['bewerbung_abgeschicktamum IS NOT NULL'] = null;
				$where['bestaetigtam'] = null;
				break;
			case "statusbestaetigt":
				$where['ps.status_kurzbz'] = 'Interessent';
				$where['bestaetigtam IS NOT NULL'] = null;
				break;
			case "statusbestaetigtrtnichtangemeldet":
				$where['ps.status_kurzbz'] = 'Interessent';
				$where['bestaetigtam IS NOT NULL'] = null;
				$this->PrestudentModel->db->where('NOT EXISTS(' . $selectRT . ')', null, false);
				break;
			case "statusbestaetigtrtangemeldet":
				$where['ps.status_kurzbz'] = 'Interessent';
				$where['bestaetigtam IS NOT NULL'] = null;
				$this->PrestudentModel->db->where('EXISTS(' . $selectRT . ')', null, false);
				break;
			case "zgv":
				$this->load->model('organisation/Studiengang_model', 'StudiengangModel');

				$result = $this->StudiengangModel->load($studiengang_kz);

				$stg = $this->getDataOrTerminateWithError($result);
				if (!$stg)
					$this->terminateWithValidationErrors(['' => 'Studiengang does not exist']); // TODO(chris): phrase
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
					// TODO(chris): this does not work with $orgform_kurzbz != null
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

		/*
			$this->PrestudentModel->addJoin('public.tbl_studiengang stg', 'studiengang_kz', 'LEFT');
			$this->PrestudentModel->addJoin('public.tbl_person p', 'person_id');
			$this->PrestudentModel->addJoin('public.tbl_prestudentstatus pls', '
				pls.status_kurzbz=public.get_rolle_prestudent(tbl_prestudent.prestudent_id, NULL) 
				AND pls.prestudent_id=tbl_prestudent.prestudent_id 
				AND pls.studiensemester_kurzbz=public.get_stdsem_prestudent(tbl_prestudent.prestudent_id, NULL) 
				AND pls.ausbildungssemester=public.get_absem_prestudent(tbl_prestudent.prestudent_id, NULL)', 'LEFT');
			$this->PrestudentModel->addJoin('lehre.tbl_studienplan sp', 'studienplan_id', 'LEFT');
			$this->PrestudentModel->addJoin('public.tbl_prestudentstatus ps', '
				ps.status_kurzbz=public.get_rolle_prestudent(tbl_prestudent.prestudent_id, ' . $stdsemEsc . ') 
				AND ps.prestudent_id=tbl_prestudent.prestudent_id 
				AND ps.studiensemester_kurzbz=public.get_stdsem_prestudent(tbl_prestudent.prestudent_id, ' . $stdsemEsc . ') 
				AND ps.ausbildungssemester=public.get_absem_prestudent(tbl_prestudent.prestudent_id, ' . $stdsemEsc . ')', 'LEFT');*/
		$this->prepareQuery($studiensemester_kurzbz);
		
		$this->PrestudentModel->addSelect("
			CASE WHEN ps.status_kurzbz IN ('Aufgenommener', 'Bewerber', 'Wartender', 'interessent') 
			THEN ps.ausbildungssemester::text 
			ELSE ''::text END AS semester", false);
		$this->PrestudentModel->addSelect("'' AS verband");
		$this->PrestudentModel->addSelect("'' AS gruppe");
		$this->addSelectPrioRel();

		$this->addFilter($studiensemester_kurzbz);

		$result = $this->PrestudentModel->loadWhere($where);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	/**
	 * @param integer		$studiengang_kz
	 * @param integer		$semester						(optional)
	 * @param string		$verband						(optional)
	 * @param integer		$gruppe							(optional)
	 * @param string		$gruppe_kurzbz					(optional)
	 * @param string		$orgform_kurzbz					(optional)
	 *
	 * @return void
	 */
	protected function getStudents($studiengang_kz, $semester = null, $verband = null, $gruppe = null, $gruppe_kurzbz = null, $orgform_kurzbz = null)
	{
		$studiensemester_kurzbz = $this->variablelib->getVar('semester_aktuell');
		

		$this->load->model('crm/Prestudent_model', 'PrestudentModel');

		/*
			$this->PrestudentModel->addJoin('public.tbl_studiengang stg', 'studiengang_kz', 'LEFT');
			$this->PrestudentModel->addJoin('public.tbl_person p', 'person_id');
			$this->PrestudentModel->addJoin('public.tbl_student s', 'prestudent_id');
			$this->PrestudentModel->addJoin('public.tbl_prestudentstatus pls', '
				pls.status_kurzbz=public.get_rolle_prestudent(tbl_prestudent.prestudent_id, NULL) 
				AND pls.prestudent_id=tbl_prestudent.prestudent_id 
				AND pls.studiensemester_kurzbz=public.get_stdsem_prestudent(tbl_prestudent.prestudent_id, NULL) 
				AND pls.ausbildungssemester=public.get_absem_prestudent(tbl_prestudent.prestudent_id, NULL)', 'LEFT');
			$this->PrestudentModel->addJoin('lehre.tbl_studienplan sp', 'studienplan_id', 'LEFT');
			$this->PrestudentModel->addJoin('public.tbl_benutzer b', 's.student_uid=b.uid');
			$this->PrestudentModel->addJoin(
				'public.tbl_studentlehrverband v',
				'v.student_uid=s.student_uid AND v.studiensemester_kurzbz=' . $this->PrestudentModel->escape($studiensemester_kurzbz)
			);*/
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
	protected function getPrestudent($prestudent_id)
	{
		$studiensemester_kurzbz = $this->variablelib->getVar('semester_aktuell');

		$this->load->model('crm/Prestudent_model', 'PrestudentModel');

		/*
			$this->PrestudentModel->addJoin('public.tbl_studiengang stg', 'studiengang_kz', 'LEFT');
			$this->PrestudentModel->addJoin('public.tbl_person p', 'person_id');
			$this->PrestudentModel->addJoin('public.tbl_student s', 'prestudent_id', 'LEFT');
			$this->PrestudentModel->addJoin('public.tbl_prestudentstatus pls', '
				pls.status_kurzbz=public.get_rolle_prestudent(tbl_prestudent.prestudent_id, NULL) 
				AND pls.prestudent_id=tbl_prestudent.prestudent_id 
				AND pls.studiensemester_kurzbz=public.get_stdsem_prestudent(tbl_prestudent.prestudent_id, NULL) 
				AND pls.ausbildungssemester=public.get_absem_prestudent(tbl_prestudent.prestudent_id, NULL)', 'LEFT');
			$this->PrestudentModel->addJoin('lehre.tbl_studienplan sp', 'studienplan_id', 'LEFT');
			$this->PrestudentModel->addJoin('public.tbl_benutzer b', 's.student_uid=b.uid', 'LEFT');
			$this->PrestudentModel->addJoin(
				'public.tbl_studentlehrverband v',
				'v.student_uid=s.student_uid AND v.studiensemester_kurzbz=' . $this->PrestudentModel->escape($studiensemester_kurzbz),
				'LEFT'
			);*/
		$this->prepareQuery($studiensemester_kurzbz);

		$this->PrestudentModel->addSelect("COALESCE(v.semester::text, CASE WHEN public.get_rolle_prestudent(tbl_prestudent.prestudent_id, NULL) IN ('Aufgenommener', 'Bewerber', 'Wartender', 'interessent') THEN public.get_absem_prestudent(tbl_prestudent.prestudent_id, NULL)::text ELSE ''::text END) AS semester", false);
		$this->PrestudentModel->addSelect('v.verband');
		$this->PrestudentModel->addSelect('v.gruppe');
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
	protected function getStudent($student_uid)
	{
		$studiensemester_kurzbz = $this->variablelib->getVar('semester_aktuell');

		$this->load->model('crm/Prestudent_model', 'PrestudentModel');

		/*
			$this->PrestudentModel->addJoin('public.tbl_studiengang stg', 'studiengang_kz', 'LEFT');
			$this->PrestudentModel->addJoin('public.tbl_person p', 'person_id');
			$this->PrestudentModel->addJoin('public.tbl_student s', 'prestudent_id');
			$this->PrestudentModel->addJoin('public.tbl_prestudentstatus pls', '
				pls.status_kurzbz=public.get_rolle_prestudent(tbl_prestudent.prestudent_id, NULL) 
				AND pls.prestudent_id=tbl_prestudent.prestudent_id 
				AND pls.studiensemester_kurzbz=public.get_stdsem_prestudent(tbl_prestudent.prestudent_id, NULL) 
				AND pls.ausbildungssemester=public.get_absem_prestudent(tbl_prestudent.prestudent_id, NULL)', 'LEFT');
			$this->PrestudentModel->addJoin('lehre.tbl_studienplan sp', 'studienplan_id', 'LEFT');
			$this->PrestudentModel->addJoin('public.tbl_benutzer b', 's.student_uid=b.uid');
			$this->PrestudentModel->addJoin(
				'public.tbl_studentlehrverband v',
				'v.student_uid=s.student_uid AND v.studiensemester_kurzbz=' . $this->PrestudentModel->escape($studiensemester_kurzbz),
				'LEFT'
			);*/
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
	 * @param integer		$person_id
	 *
	 * @return void
	 */
	protected function getPerson($person_id)
	{
		$studiensemester_kurzbz = $this->variablelib->getVar('semester_aktuell');

		$this->load->model('crm/Prestudent_model', 'PrestudentModel');

		/*
			$this->PrestudentModel->addJoin('public.tbl_person p', 'person_id');
			$this->PrestudentModel->addJoin('public.tbl_student s', 'prestudent_id');
			$this->PrestudentModel->addJoin('public.tbl_benutzer b', 's.student_uid=b.uid');
			$this->PrestudentModel->addJoin(
				'public.tbl_studentlehrverband v',
				'v.student_uid=s.student_uid AND v.studiensemester_kurzbz=' . $this->PrestudentModel->escape($studiensemester_kurzbz),
				'LEFT'
			);*/
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
	 * @param string|null	$studiensemester_kurzbz
	 * @param string		$type
	 *
	 * @return void
	 */
	protected function prepareQuery($studiensemester_kurzbz, $type = 'LEFT')
	{
		$stdsemEsc = $studiensemester_kurzbz ? $this->PrestudentModel->escape($studiensemester_kurzbz) : 'NULL';


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


		$this->PrestudentModel->addSelect("b.uid");
		$this->PrestudentModel->addSelect('titelpre');
		$this->PrestudentModel->addSelect('nachname');
		$this->PrestudentModel->addSelect('vorname');
		$this->PrestudentModel->addSelect('wahlname');
		$this->PrestudentModel->addSelect('vornamen');
		$this->PrestudentModel->addSelect('titelpost');
		$this->PrestudentModel->addSelect('svnr');
		$this->PrestudentModel->addSelect('ersatzkennzeichen');
		$this->PrestudentModel->addSelect('gebdatum');
		$this->PrestudentModel->addSelect('geschlecht');

		// semester
		// verband
		// gruppe

		$this->PrestudentModel->addSelect('UPPER(stg.typ || stg.kurzbz) AS studiengang');
		$this->PrestudentModel->addSelect('tbl_prestudent.studiengang_kz');
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
			THEN b.uid || " . $this->PrestudentModel->escape(DOMAIN) . " 
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

		/*$this->PrestudentModel->addSelect('tbl_prestudent.reihungstest_id');
		$this->PrestudentModel->addSelect('tbl_prestudent.anmeldungreihungstest');
		$this->PrestudentModel->addSelect('tbl_prestudent.gsstudientyp_kurzbz');
		$this->PrestudentModel->addSelect('tbl_prestudent.priorisierung');
		$this->PrestudentModel->addSelect('p.zugangscode');
		$this->PrestudentModel->addSelect('p.bpk');*/

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
				SELECT *, public.get_rolle_prestudent(tbl_prestudent.prestudent_id, NULL) AS laststatus
				FROM PUBLIC.tbl_prestudent pss
				JOIN PUBLIC.tbl_prestudentstatus USING (prestudent_id)
				WHERE person_id = p.person_id
				AND studiensemester_kurzbz = (
					SELECT studiensemester_kurzbz
					FROM PUBLIC.tbl_prestudentstatus
					WHERE prestudent_id = tbl_prestudent.prestudent_id
					AND status_kurzbz = 'Interessent'
					LIMIT 1
				)
				AND status_kurzbz = 'Interessent'
			) prest
			WHERE laststatus NOT IN ('Abbrecher', 'Abgewiesener', 'Absolvent')
			AND priorisierung <= tbl_prestudent.priorisierung
		) || ' (' || tbl_prestudent.priorisierung || ')' AS priorisierung_relativ", false);
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
		$filter = $this->input->get('filter');
		if (isset($filter['konto_count_0'])) {
			$bt = $this->PrestudentModel->escape($filter['konto_count_0']);
			$stdsem = $this->PrestudentModel->escape($studiensemester_kurzbz);

			$this->PrestudentModel->db->where('(
				SELECT count(*) 
				FROM public.tbl_konto 
				WHERE person_id=tbl_prestudent.person_id 
				AND buchungstyp_kurzbz=' . $bt . ' 
				AND studiensemester_kurzbz=' . $stdsem . '
			) =', 0);
			$this->PrestudentModel->db->where('get_rolle_prestudent(tbl_prestudent.prestudent_id, NULL) !=', 'Incoming');
		}
		if (isset($filter['konto_missing_counter'])) {
			$bt = $this->PrestudentModel->escape($filter['konto_missing_counter']);
			$stg = '';
			if ($this->variablelib->getVar('kontofilterstg') == 'true')
				$stg = ' AND studiengang_kz=tbl_prestudent.studiengang_kz';

			$bt = $bt == 'alle' ? '' : ' AND buchungstyp_kurzbz=' . $bt;

			$this->PrestudentModel->db->where('(
				SELECT sum(betrag) 
				FROM public.tbl_konto 
				WHERE person_id=tbl_prestudent.person_id' .
				$bt .
				$stg . '
			) !=', 0);
		}
	}
}
