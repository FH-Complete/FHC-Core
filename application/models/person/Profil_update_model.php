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
	//TODO: function wird nicht verwendet
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
	 * @return array 
	 */
	//TODO: function wird nicht verwendet
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
		$this->db->order_by('COALESCE(public.tbl_profil_update.updateamum, public.tbl_profil_update.insertamum)', 'DESC', false);
		$res = $this->loadWhere($whereClause);
		if (isError($res)) {
			return $res;
		}
		if (hasData($res)) {
			foreach (getData($res) as $request) {
				$this->formatProfilRequest($request);
			}
		}

		return $res;

	}

	//? remove File from the Profil Update
	public function removeFileFromProfilUpdate($dms_id)
	{

		if(!is_int($dms_id) || $dms_id < 0){
			return error("not valid dms_id");
		}
		
		return $this->execReadOnlyQuery("
		UPDATE public.tbl_profil_update
		SET attachment_id = NULL
		WHERE attachment_id = ?", [$dms_id]);

	}


	/**
	 * getProfilUpdateWithPermission
	 * 
	 * queries the profil updates and checks if the user trying to query the data has permissions to get the profil updates
	 *   
	 * @param string $whereClause additional where clause that will be appended to the db query
	 * @return array array with all the profil updates that the user is eligible to see
	 */
	public function getProfilUpdateWithPermission($whereClause = null)
	{

		$studentBerechtigung = $this->permissionlib->isBerechtigt('student/stammdaten', 's');
		$mitarbeiterBerechtigung = $this->permissionlib->isBerechtigt('mitarbeiter/stammdaten', 's');
		$oe_berechtigung = $this->permissionlib->getOE_isEntitledFor('student/stammdaten');

		$lang = "select index from public.tbl_sprache where sprache =" . $this->escape(getUserLanguage());
		$res = [];

		if ($studentBerechtigung) {


			//? Nur wenn der/die AssistentIn auch die Berechtigung in der gleichen Organisationseinheit des Studenten hat
			$parameters = [];
			$query = "
			SELECT
				profil_update_id,
				tbl_profil_update.uid,
				(tbl_person.vorname || ' ' || tbl_person.nachname) AS name ,
				topic,
				requested_change,
				tbl_profil_update.updateamum,
				tbl_profil_update.updatevon,
				tbl_profil_update.insertamum,
				tbl_profil_update.insertvon,
				status,
				public.tbl_profil_update_status.bezeichnung_mehrsprachig[(" . $lang . ")] as status_translated,
				status_timestamp,
				status_message,
				attachment_id,
				UPPER(public.tbl_studiengang.typ || public.tbl_studiengang.kurzbz) AS studiengang,
				COALESCE(of.orgform_kurzbz, public.tbl_studiengang.orgform_kurzbz) AS orgform,
				NULL as oezuordnung,
				tbl_student.semester
			FROM public.tbl_profil_update
			JOIN public.tbl_profil_update_status ON public.tbl_profil_update_status.status_kurzbz = public.tbl_profil_update.status 
			JOIN public.tbl_student ON public.tbl_student.student_uid=public.tbl_profil_update.uid
			JOIN public.tbl_benutzer ON public.tbl_benutzer.uid = public.tbl_student.student_uid
			JOIN public.tbl_person ON public.tbl_benutzer.person_id=public.tbl_person.person_id
			JOIN public.tbl_studiengang ON public.tbl_studiengang.studiengang_kz=public.tbl_student.studiengang_kz
			LEFT JOIN (
				select
					pss.prestudent_id, COALESCE(sp.orgform_kurzbz, pss.orgform_kurzbz) as orgform_kurzbz
				from (
					select
						prestudent_id, max(insertamum) as insertamum
					from
						public.tbl_prestudentstatus
					where
						datum <= NOW()
					group by
						prestudent_id
				) mpss
				join
					public.tbl_prestudentstatus pss on pss.prestudent_id  = mpss.prestudent_id and pss.insertamum = mpss.insertamum
				left join
					lehre.tbl_studienplan sp on pss.studienplan_id = sp.studienplan_id
			) of ON of.prestudent_id = public.tbl_student.prestudent_id
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
			$this->addSelect([
				"profil_update_id",
				"tbl_profil_update.uid",
				"(tbl_person.vorname || ' ' || tbl_person.nachname) AS name",
				"topic",
				"requested_change",
				"tbl_profil_update.updateamum",
				"tbl_profil_update.updatevon",
				"tbl_profil_update.insertamum",
				"tbl_profil_update.insertvon",
				"status",
				"public.tbl_profil_update_status.bezeichnung_mehrsprachig[(" . $lang . ")] AS status_translated",
				"status_timestamp",
				"status_message",
				"attachment_id",
				"COALESCE(NULL) as studiengang",
				"COALESCE(NULL) as orgform",
				"oe.bezeichnung as oezuordnung"
			]);
			$this->addJoin('tbl_profil_update_status', 'tbl_profil_update_status.status_kurzbz=tbl_profil_update.status');
			$this->addJoin('tbl_mitarbeiter', 'tbl_mitarbeiter.mitarbeiter_uid=tbl_profil_update.uid');
			$this->addJoin('tbl_benutzer', 'tbl_benutzer.uid=tbl_profil_update.uid');
			$this->addJoin('tbl_person', 'tbl_benutzer.person_id=tbl_person.person_id');
			$this->addJoin('tbl_benutzerfunktion bf', 'bf.uid = tbl_benutzer.uid AND bf.funktion_kurzbz = \'oezuordnung\' AND NOW() >= COALESCE(bf.datum_von, \'1970-01-01\'::date) AND NOW() <= COALESCE(bf.datum_bis, \'2170-12-31\'::date)', 'LEFT');
			$this->addJoin('tbl_organisationseinheit oe', 'oe.oe_kurzbz = bf.oe_kurzbz', 'LEFT');
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

	/**
	 * formatProfilRequest
	 * 
	 * formats the the properties of a profilUpdate request row result  
	 *   
	 * @param stdClass $request unflitered profilUpdate row result from the database
	 * @return void 
	 */
	private function formatProfilRequest($request)
	{
		$request->requested_change = json_decode($request->requested_change);
		$request->insertamum_iso = !is_null($request->insertamum) ? date_create($request->insertamum)->format('Y-m-d') : null;
		$request->insertamum = !is_null($request->insertamum) ? date_create($request->insertamum)->format('d.m.Y') : null;
		$request->updateamum_iso = !is_null($request->updateamum) ? date_create($request->updateamum)->format('Y-m-d') : null;
		$request->updateamum = !is_null($request->updateamum) ? date_create($request->updateamum)->format('d.m.Y') : null;
		$request->status_timestamp_iso = !is_null($request->status_timestamp) ? date_create($request->status_timestamp)->format('Y-m-d') : null;
		$request->status_timestamp = !is_null($request->status_timestamp) ? date_create($request->status_timestamp)->format('d.m.Y') : null;
	}

}
