<?php

class Profil_update_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_profil_update';
		$this->pk = ['profil_update_id'];
		$this->hasSequence = true;


		$this->load->model('crm/Student_model', 'StudentModel');
		$this->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');

		$this->load->library('PermissionLib');
	}

	/**
	 * getTimestamp
	 * returns insert or update timestamp of a certain profil update
	 * 
	 * @param boolean $update: conditional whether to return insertamum or updateamum
	 */
	public function getTimestamp($id, $update = false)
	{
		$selectStatement = $update ? 'updateamum' : 'insertamum';
		$this->addSelect([$selectStatement]);
		$res = $this->load([$id]);
		return hasData($res) ? getData($res)[0]->$selectStatement : null;
	}

	/**
	 * getFilesFromChangeRequest
	 * 
	 * returns all files associated to a profil update request in the following format:
	 * {dms_id:123 , name:"test"}
	 * 
	 * @param boolean $profil_update_id primary key of the profil update request
	 * @return Array 
	 */
	public function getFilesFromChangeRequest($profil_update_id)
	{
		$this->addSelect(["requested_change"]);
		$res = $this->load([$profil_update_id]);
		$res = hasData($res) ? getData($res)[0] : null;
		return json_decode($res->requested_change)->files ?: [];
	}


	//? queries the tbl_profil_updates without permissions of the user
	public function getProfilUpdatesWhere($whereClause)
	{
		if (array_key_exists("uid", $whereClause)) {
			$whereClause["public.tbl_profil_update.uid"] = $whereClause["uid"];
			unset($whereClause["uid"]);
		}
		$this->addSelect(["public.tbl_profil_update.*", "public.tbl_person.vorname"]);
		$this->addJoin("public.tbl_benutzer", "public.tbl_benutzer.uid = public.tbl_profil_update.uid");
		$this->addJoin("public.tbl_person", "public.tbl_person.person_id = public.tbl_benutzer.person_id");
		$res = $this->loadWhere($whereClause);
		if(isError($res)){
			return $res;
		}
		if(hasData($res)){
			foreach (getData($res) as $request) {
				$this->formatProfilRequest($request);
			}
		}
		
		return $res;

	}


	/**
	 * 
	 * getProfilUpdate
	 * returns a profil update with id 
	 * returns all profil updates if id is set to null
	 */
	public function getProfilUpdateWithPermission($whereClause = null)
	{

		$studentBerechtigung = $this->permissionlib->isBerechtigt('student/stammdaten', 's');
		$mitarbeiterBerechtigung = $this->permissionlib->isBerechtigt('mitarbeiter/stammdaten', 's');
		$oe_berechtigung = $this->permissionlib->getOE_isEntitledFor('student/stammdaten');

		$res = [];

		if ($studentBerechtigung) {


			//? Nur wenn der/die AssistentIn auch die Berechtigung in der gleichen Organisationseinheit des Studenten hat
			$parameters = [];
			$query = "
			SELECT
			profil_update_id, tbl_profil_update.uid, (tbl_person.vorname || ' ' || tbl_person.nachname) AS name , topic, requested_change, tbl_profil_update.updateamum, tbl_profil_update.updatevon, tbl_profil_update.insertamum, tbl_profil_update.insertvon, status, status_timestamp, status_message, attachment_id 
			FROM public.tbl_profil_update 
			JOIN public.tbl_student ON public.tbl_student.student_uid=public.tbl_profil_update.uid
			JOIN public.tbl_benutzer ON public.tbl_benutzer.uid = public.tbl_student.student_uid
			JOIN public.tbl_person ON public.tbl_benutzer.person_id=public.tbl_person.person_id
			JOIN public.tbl_studiengang ON public.tbl_studiengang.studiengang_kz=public.tbl_student.studiengang_kz
			Where public.tbl_studiengang.oe_kurzbz IN ? ";
			$parameters[] = $oe_berechtigung;
			if ($whereClause) {
				foreach ($whereClause as $key => $value) {
					$parameters[] = $value;
					$query .= " AND " . $key . " = ?";
				}
			}

			$studentRequests = $this->execReadOnlyQuery($query, $parameters);

			if (isError($studentRequests))
				return error("db error: " . getData($studentRequests));
			$studentRequests = getData($studentRequests) ?: [];
			foreach ($studentRequests as $request) {
				array_push($res, $request);
			}
		}
		if ($mitarbeiterBerechtigung) {
			$this->addSelect(["profil_update_id", "tbl_profil_update.uid", "(tbl_person.vorname || ' ' || tbl_person.nachname) AS name", "topic", "requested_change", "tbl_profil_update.updateamum", "tbl_profil_update.updatevon", "tbl_profil_update.insertamum", "tbl_profil_update.insertvon", "status", "status_timestamp", "status_message", "attachment_id"]);
			$this->addJoin('tbl_mitarbeiter', 'tbl_mitarbeiter.mitarbeiter_uid=tbl_profil_update.uid');
			$this->addJoin('tbl_benutzer', 'tbl_benutzer.uid=tbl_profil_update.uid');
			$this->addJoin('tbl_person', 'tbl_benutzer.person_id=tbl_person.person_id');
			$mitarbeiterRequests = $this->loadWhere($whereClause);
			if (isError($mitarbeiterRequests))
				return error("db error: " . getData($mitarbeiterRequests));
			$mitarbeiterRequests = getData($mitarbeiterRequests) ?: [];
			foreach ($mitarbeiterRequests as $request) {
				array_push($res, $request);
			}
		}
		if ($res) {

			foreach ($res as $request) {
				$this->formatProfilRequest($request);
			}
		}

		return $res;

	}

	private function formatProfilRequest($request)
	{
		$request->requested_change = json_decode($request->requested_change);
		$request->insertamum = !is_null($request->insertamum) ? date_create($request->insertamum)->format('d.m.Y') : null;
		$request->updateamum = !is_null($request->updateamum) ? date_create($request->updateamum)->format('d.m.Y') : null;
		$request->status_timestamp = !is_null($request->status_timestamp) ? date_create($request->status_timestamp)->format('d.m.Y') : null;
	}

}
