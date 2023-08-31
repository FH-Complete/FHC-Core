<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Studentenverwaltung extends FHC_Controller
{
	public function __construct()
	{
		// TODO(chris): access!
		parent::__construct();
	}

	/**
	 * @return void
	 */
	public function index()
	{
		// TODO(chris): load stgs (this is just for testing)
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');

		$this->StudiengangModel->addJoin('public.tbl_lehrverband v', 'studiengang_kz');
		
		$this->StudiengangModel->addDistinct();
		$this->StudiengangModel->addSelect('v.studiengang_kz');
		$this->StudiengangModel->addSelect('tbl_studiengang.bezeichnung');
		$this->StudiengangModel->addSelect('kurzbzlang');
		$this->StudiengangModel->addSelect('erhalter_kz');
		$this->StudiengangModel->addSelect('typ');
		$this->StudiengangModel->addSelect('kurzbz');
		
		$this->StudiengangModel->addOrder('erhalter_kz');
		$this->StudiengangModel->addOrder('typ');
		$this->StudiengangModel->addOrder('kurzbz');

		$result = $this->StudiengangModel->loadWhere(['v.aktiv' => true]);
		if (isError($result))
			return $this->outputJson($result);
		if (!hasData($result))
			return $this->outputJsonSuccess([]);
		$list = getData($result);
		$list[] = [
			'name' => 'International',
			'children' => [
				[
					'name' => 'Incoming',
					'leaf' => true
				],
				[
					'name' => 'Outgoing',
					'leaf' => true
				],
				[
					'name' => 'Gemeinsame Studien',
					'leaf' => true
				]
			]
		];
		$this->outputJsonSuccess($list);
	}

	/**
	 * @param integer		$studiengang_kz
	 * @return void
	 */
	public function getStudiengang($studiengang_kz)
	{
		// TODO(chris): load stgSemester + prestudent
		$this->outputJson([
			[
				'key' => 2,
				'name' => 'PreStudent'
			]
		]);
	}

	/**
	 * @param integer		$studiengang_kz
	 * @return void
	 */
	public function getStudents($studiengang_kz = null)
	{
		// TODO(chris): stdSem?
		$stdSem='SS2023';

		// TODO(chris): load students filtered by the params
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');

		$this->PrestudentModel->addJoin('public.tbl_person p', 'person_id');
		$this->PrestudentModel->addJoin('public.tbl_student s', 'prestudent_id');
		$this->PrestudentModel->addJoin('public.tbl_benutzer b', 's.student_uid=b.uid');
		$this->PrestudentModel->addJoin('public.tbl_studentlehrverband v', 'v.student_uid=s.student_uid AND v.studiensemester_kurzbz=' . $this->PrestudentModel->escape($stdSem));

		$this->PrestudentModel->addSelect('p.person_id');
		$this->PrestudentModel->addSelect('s.prestudent_id');
		$this->PrestudentModel->addSelect('b.uid');
		$this->PrestudentModel->addSelect('titelpre');
		$this->PrestudentModel->addSelect('titelpost');
		$this->PrestudentModel->addSelect('vorname');
		$this->PrestudentModel->addSelect('wahlname');
		$this->PrestudentModel->addSelect('vornamen');
		$this->PrestudentModel->addSelect('geschlecht');
		$this->PrestudentModel->addSelect('nachname');
		$this->PrestudentModel->addSelect('gebdatum');
		$this->PrestudentModel->addSelect('tbl_prestudent.anmerkung');
		$this->PrestudentModel->addSelect('ersatzkennzeichen');
		$this->PrestudentModel->addSelect('svnr');
		$this->PrestudentModel->addSelect('s.matrikelnr');
		$this->PrestudentModel->addSelect('p.anmerkung AS anmerkungen');
		$this->PrestudentModel->addSelect('v.semester');
		$this->PrestudentModel->addSelect('v.verband');
		$this->PrestudentModel->addSelect('v.gruppe');
		$this->PrestudentModel->addSelect('tbl_prestudent.studiengang_kz');
		$this->PrestudentModel->addSelect('aufmerksamdurch_kurzbz');
		$this->PrestudentModel->addSelect('mentor');
		$this->PrestudentModel->addSelect('b.aktiv AS bnaktiv');
		$this->PrestudentModel->addSelect("(SELECT kontakt FROM public.tbl_kontakt WHERE kontakttyp='email' AND person_id=p.person_id AND zustellung LIMIT 1) AS email_privat", false);
		$this->PrestudentModel->addSelect("(SELECT rt_gesamtpunkte AS punkte FROM public.tbl_prestudent WHERE prestudent_id=s.prestudent_id) AS punkte", false);
		$this->PrestudentModel->addSelect('tbl_prestudent.dual');
		$this->PrestudentModel->addSelect('tbl_prestudent.reihungstest_id');
		$this->PrestudentModel->addSelect('tbl_prestudent.anmeldungreihungstest');
		$this->PrestudentModel->addSelect('p.matr_nr');
		$this->PrestudentModel->addSelect('tbl_prestudent.gsstudientyp_kurzbz');
		$this->PrestudentModel->addSelect('tbl_prestudent.aufnahmegruppe_kurzbz');
		$this->PrestudentModel->addSelect('tbl_prestudent.priorisierung');
		$this->PrestudentModel->addSelect('p.zugangscode');
		$this->PrestudentModel->addSelect('p.bpk');

		$this->PrestudentModel->addOrder('nachname');
		$this->PrestudentModel->addOrder('vorname');

		// TODO(chris): do we need that? do we want that?
		if ($studiengang_kz === null)
			$this->PrestudentModel->addLimit(1000);
		
		if ($studiengang_kz !== null)
			$result = $this->PrestudentModel->loadWhere([
				'tbl_prestudent.studiengang_kz' => $studiengang_kz
			]);
		else
			$result = $this->PrestudentModel->load();
		
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson(getError($result));
		} else {
			$this->outputJson(getData($result));
		}
	}
}
