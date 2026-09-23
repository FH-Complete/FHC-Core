<?php
class Lehreinheitgruppe_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_lehreinheitgruppe';
		$this->pk = 'lehreinheitgruppe_id';
		$this->load->model('education/lehreinheit_model', 'LehreinheitModel');
		$this->load->model('organisation/studiengang_model', 'StudiengangModel');
		$this->load->model('organisation/gruppe_model', 'GruppeModel');
		$this->load->model('person/benutzergruppe_model', 'BenutzergruppeModel');
		$this->load->model('ressource/stundenplandev_model', 'StundenplandevModel');
	}

	/**
	 * Gets group directly assigned to a Lehreinheit
	 * @param $lehreinheit_id
	 * @return array
	 */
	public function getDirectGroup($lehreinheit_id)
	{
		$this->addSelect('tbl_lehreinheitgruppe.*');
		$this->addSelect('tbl_gruppe.*');
		$this->addSelect('uid');
		$this->addSelect('vorname');
		$this->addSelect('nachname');
		$this->addJoin('public.tbl_gruppe', 'gruppe_kurzbz');
		$this->addJoin('public.tbl_benutzergruppe', 'gruppe_kurzbz', 'LEFT');
		$this->addJoin('public.tbl_benutzer', 'uid', 'LEFT');
		$this->addJoin('public.tbl_person', 'person_id', 'LEFT');
		return $this->loadWhere(
			array(
			'tbl_gruppe.direktinskription' => true,
			'lehreinheit_id' => $lehreinheit_id
			)
		);
	}

	/**
	 * Gets direct group assignment, consisting of lehreinheitgruppe, gruppe and benutzergruppe
	 * @param $uid
	 * @param $lehreinheit_id
	 * @return array
	 */
	public function getDirectGroupAssignment($uid, $lehreinheit_id)
	{
		$this->addJoin('public.tbl_gruppe', 'gruppe_kurzbz');
		$this->addJoin('public.tbl_benutzergruppe', 'gruppe_kurzbz');
		return $this->loadWhere(array(
				'tbl_gruppe.direktinskription' => true,
				'lehreinheit_id' => $lehreinheit_id,
				'tbl_benutzergruppe.uid' => $uid
			)
		);
	}

	/**
	 * Directly assigns a user to a Lehreinheit (Lehreinheitgruppe, Benutzergruppe).
	 * Creates own "hidden" group if necessary.
	 * @param $uid of the student to assign
	 * @param $lehreinheit_id
	 * @return array error or success
	 */
	public function direktUserAdd($uid, $lehreinheit_id)
	{
		$result = success('User added successfully to group');
		$directgroup = $this->getDirectGroup($lehreinheit_id);
		$lehreinheit = $this->LehreinheitModel->load($lehreinheit_id);
		$loggedInUser = getAuthUID();

		if (hasData($lehreinheit))
		{
			if (hasData($directgroup))
			{
				$gruppe_kurzbz = $directgroup->retval[0]->gruppe_kurzbz;
			}
			else
			{
				// Es gibt keine direkte Gruppe zu dieser LE
				// es wird eine erstellt und zugewiesen
				$lva = $this->LehrveranstaltungModel->load($lehreinheit->retval[0]->lehrveranstaltung_id);

				if (hasData($lva))
				{
					$lvadata = $lva->retval[0];
					$studiengang = $this->StudiengangModel->load($lvadata->studiengang_kz);

					if (hasData($studiengang))
					{
						$gruppe_kurzbz = 'GRP_'.$lehreinheit_id;
						$studiengangdata = $studiengang->retval[0];
						$kuerzel = mb_strtoupper($studiengangdata->typ.$studiengangdata->kurzbz);
						$bezeichnung = $kuerzel.' '.$lvadata->semester.' '.$lvadata->kurzbz;

						$gruppe = $this->GruppeModel->load($gruppe_kurzbz);

						if (!hasData($gruppe))
						{
							$groupdata = array(
								'gruppe_kurzbz' => $gruppe_kurzbz,
								'studiengang_kz' => $lvadata->studiengang_kz,
								'semester' => $lvadata->semester,
								'bezeichnung' => $bezeichnung,
								'aktiv' => true,
								'mailgrp' => true,
								'sichtbar' => false,
								'generiert' => false,
								'insertamum' => date('Y-m-d H:i:s'),
								'insertvon' => $loggedInUser,
								'orgform_kurzbz' => $lvadata->orgform_kurzbz,
								'direktinskription' => true

							);

							$groupadd = $this->GruppeModel->insert($groupdata);

							if (isError($groupadd))
								return error('Error when inserting Gruppe');
						}

						$lehreinheitgruppedata = array(
							'lehreinheit_id' => $lehreinheit->retval[0]->lehreinheit_id,
							'gruppe_kurzbz' => $gruppe_kurzbz,
							'studiengang_kz' => $lvadata->studiengang_kz,
							'semester' => $lvadata->semester,
							'insertamum' => date('Y-m-d H:i:s'),
							'insertvon' => $loggedInUser
						);

						$lehreinheitgruppeadd = $this->insert($lehreinheitgruppedata);

						if (isError($lehreinheitgruppeadd))
							$result = error('Error when inserting Lehreinheit');
					}
				}
			}

			if (isset($gruppe_kurzbz) && !isEmptyString($gruppe_kurzbz))
			{
				$benutzergruppe = $this->BenutzergruppeModel->load(array(
					'uid' => $uid,
					'gruppe_kurzbz' => $gruppe_kurzbz
				));

				if (!hasData($benutzergruppe))
				{
					$benutzergruppedata = array(
						'uid' => $uid,
						'gruppe_kurzbz' => $gruppe_kurzbz,
						'studiensemester_kurzbz' => $lehreinheit->retval[0]->studiensemester_kurzbz,
						'insertamum' => date('Y-m-d H:i:s'),
						'insertvon' => $loggedInUser
					);

					$benutzergruppeadd = $this->BenutzergruppeModel->insert($benutzergruppedata);

					if (isError($benutzergruppeadd))
						$result = error('Error when inserting Benutzergruppe');
				}
			}
		}
		else
		{
			return error('No Lehreinheit found');
		}

		return $result;
	}

	/**
	 * Deletes direct assignment of a student to a Lehreinheit
	 * @param $uid of the assigned student
	 * @param $lehreinheit_id
	 * @return array error or success
	 */
	public function direktUserDelete($uid, $lehreinheit_id)
	{
		$result = success('User deleted successfully');
		$lehreinheit = $this->LehreinheitModel->load($lehreinheit_id);

		if (hasData($lehreinheit))
		{
			$directgroup = $this->getDirectGroup($lehreinheit_id);
			if (hasData($directgroup))
			{
				$gruppe_kurzbz = $directgroup->retval[0]->gruppe_kurzbz;
				// delete benutzer assignment
				$deleteresp = $this->BenutzergruppeModel->delete(array('uid' => $uid, 'gruppe_kurzbz' => $gruppe_kurzbz));

				if (hasData($deleteresp))
				{
					$uids = $this->BenutzergruppeModel->loadWhere(
						array(
							'gruppe_kurzbz' => $gruppe_kurzbz,
							'studiensemester_kurzbz' => $lehreinheit->retval[0]->studiensemester_kurzbz)
					);

					if (isSuccess($uids) && !hasData($uids))
					{
						// group is empty and can be deleted

						// delete from Lehreinheit
						$this->delete($directgroup->retval[0]->lehreinheitgruppe_id);

						$studplandevqry = "
								SELECT
									*
								FROM
									lehre.tbl_stundenplandev
								WHERE
									gruppe_kurzbz=?
								LIMIT 1";

						$studplandevres = $this->execQuery($studplandevqry, array('gruppe_kurzbz' => $gruppe_kurzbz));
						if (isSuccess($studplandevres))
						{
							if (hasData($studplandevres))
							{
								$studplandevdelqry = "
									DELETE FROM lehre.tbl_stundenplandev
											WHERE gruppe_kurzbz=?";

								$studplandevdelres = $this->execQuery($studplandevdelqry, array('gruppe_kurzbz' => $gruppe_kurzbz));

								if (!hasData($studplandevdelres))
									$result = error('Studienplan entry could not be deleted');
							}
							else
							{
								$lehreinheitgrupperes = $this->load(array('gruppe_kurzbz' => $gruppe_kurzbz));

								if (isSuccess($lehreinheitgrupperes) && !hasData($lehreinheitgrupperes))
								{
									$benutzergruppegrupperes = $this->BenutzergruppeModel->load(array('gruppe_kurzbz' => $gruppe_kurzbz));

									if (isSuccess($benutzergruppegrupperes) && !hasData($benutzergruppegrupperes))
									{
										// delete group if not in Studienplandev. If in Studienplan, deleted next day after cronjob.
										$gruppedelres = $this->GruppeModel->delete($gruppe_kurzbz);

										if (!hasData($gruppedelres))
										{
											$result = error('Gruppe could not be deleted');
										}
									}
								}
							}
						}
						else
						{
							$result = error('Error when querying Studienplan');
						}
					}
				}
				else
				{
					$result = error('Benutzergruppe could not be deleted');
				}
			}
			else
			{
				$result = error('No direct group found for Lehreinheit');
			}
		}
		else
		{
			$result = error('No Lehreinheit found');
		}
		return $result;
	}

	public function addGroup($lehreinheit_id, $gid, $verband)
	{
		$lehreinheit = $this->LehreinheitModel->load($lehreinheit_id);

		if (!hasData($lehreinheit))
			return error ('No Lehreinheit found!');

		if ($verband === false)
		{
			$gruppen_result = $this->GruppeModel->loadWhere(array('gid' => $gid));

			if (!hasData($gruppen_result))
				return error('No group found for gid ' . $gid);

			$gruppen_array = getData($gruppen_result)[0];

			if (!isEmptyString($gruppen_array->gruppe_kurzbz))
			{
				$this->db->where('trim(gruppe_kurzbz)', $gruppen_array->gruppe_kurzbz);
			}
			else
			{
				$this->db->group_start();
				$this->db->where("trim(gruppe_kurzbz) = ''");
				$this->db->or_where("gruppe_kurzbz IS NULL");
				$this->db->group_end();
			}
		}
		else if ($verband === true)
		{
			$gruppen_result = $this->LehrverbandModel->loadWhere(array('gid' => $gid));

			if (!hasData($gruppen_result))
				return error('No group found for gid ' . $gid);

			$gruppen_array = getData($gruppen_result)[0];

			if (!isEmptyString($gruppen_array->verband))
			{
				$this->db->where('verband', $gruppen_array->verband);
			}
			else
			{
				$this->db->group_start();
				$this->db->where("trim(verband) = ''");
				$this->db->or_where("verband IS NULL");
				$this->db->group_end();
			}

			if (!isEmptyString($gruppen_array->gruppe))
			{
				$this->db->where('gruppe', $gruppen_array->gruppe);
			}
			else
			{
				$this->db->group_start();
				$this->db->where("trim(gruppe) = ''");
				$this->db->or_where("gruppe IS NULL");
				$this->db->group_end();
			}
		}
		else
			return error('Wrong type of verband');

		$this->db->where('lehreinheit_id', $lehreinheit_id);
		$this->db->where('studiengang_kz', $gruppen_array->studiengang_kz);

		if (!isEmptyString((string)$gruppen_array->semester))
		{
			$this->db->where('semester', $gruppen_array->semester);
		}
		else
		{
			$this->db->group_start();
			$this->db->where("semester = ''");
			$this->db->or_where("semester IS NULL");
			$this->db->group_end();
		}

		$exist_result = $this->load();

		if (!hasData($exist_result))
		{
			$new_group_result = $this->insert(array(
				'lehreinheit_id' => $lehreinheit_id,
				'studiengang_kz' => $gruppen_array->studiengang_kz,
				'gruppe_kurzbz' => isset($gruppen_array->gruppe_kurzbz) ? $gruppen_array->gruppe_kurzbz : null,
				'semester' => $gruppen_array->semester,
				'verband' => isset($gruppen_array->verband) && !isEmptyString($gruppen_array->verband) ? $gruppen_array->verband : null,
				'gruppe' => isset($gruppen_array->gruppe) && !isEmptyString($gruppen_array->gruppe) ? $gruppen_array->gruppe : null,
				'insertamum' => date('Y-m-d H:i:s'),
				'insertvon' => getAuthUID()
			));

			if (isError($new_group_result))
				return error('Error when adding Group');

			return success('Group assigned successfully to Lehreinheit');
		}
		else
			return error($this->p->t('lehre', 'grpbereitszugeteilt'));
	}

	public function deleteGroup($lehreinheit_id, $lehreinheitgruppe_id)
	{
		$lehreinheit = $this->LehreinheitModel->load($lehreinheit_id);

		if (!hasData($lehreinheit))
			return error ('No Lehreinheit found!');

		$lehreinheitgruppe = $this->load($lehreinheitgruppe_id);

		if (!hasData($lehreinheitgruppe))
			return error ('No Lehreinheitgruppe found!');

		$this->addSelect('stundenplandev_id');
		$this->addJoin('lehre.tbl_stundenplandev',
			"tbl_stundenplandev.lehreinheit_id = tbl_lehreinheitgruppe.lehreinheit_id
				AND tbl_stundenplandev.studiengang_kz = tbl_lehreinheitgruppe.studiengang_kz
				AND tbl_stundenplandev.semester = tbl_lehreinheitgruppe.semester
				AND trim(COALESCE(tbl_stundenplandev.verband, '')) = trim(COALESCE(tbl_lehreinheitgruppe.verband, ''))
				AND trim(COALESCE(tbl_stundenplandev.gruppe, '')) = trim(COALESCE(tbl_lehreinheitgruppe.gruppe, ''))
				AND trim(COALESCE(tbl_stundenplandev.gruppe_kurzbz, '')) = trim(COALESCE(tbl_lehreinheitgruppe.gruppe_kurzbz, ''))"
		);
		$stundenplan_result = $this->loadWhere(array('tbl_lehreinheitgruppe.lehreinheitgruppe_id' => $lehreinheitgruppe_id));

		if (hasData($stundenplan_result))
			return error($this->p->t('lehre', 'grpbereitsverplant'));

		$delete_result = $this->delete($lehreinheitgruppe_id);

		if (isError($delete_result))
			return error('Error deleting Group');

		return success('Group deleted');
	}

	public function getByLehreinheit($lehreinheit_id)
	{
		$lehreinheit = $this->LehreinheitModel->load($lehreinheit_id);

		if (!hasData($lehreinheit))
			return error ('No Lehreinheit found!');

		$this->addSelect('tbl_lehreinheitgruppe.*');
		$this->addSelect('tbl_gruppe.direktinskription');
		$this->addSelect('tbl_gruppe.gruppe_kurzbz');
		$this->addSelect("CASE 
									WHEN tbl_lehreinheitgruppe.gruppe_kurzbz IS NULL THEN 
										COALESCE (
											UPPER(tbl_studiengang.typ || tbl_studiengang.kurzbz) || 
											COALESCE(tbl_lehreinheitgruppe.semester::varchar, '') || 
											COALESCE(tbl_lehreinheitgruppe.verband::varchar, '') || 
											COALESCE(tbl_lehreinheitgruppe.gruppe, ''), 
										'')
									ELSE tbl_lehreinheitgruppe.gruppe_kurzbz
								END AS bezeichnung");
		$this->addSelect("CASE 
									WHEN tbl_lehreinheitgruppe.gruppe_kurzbz IS NULL THEN 
										(
											SELECT bezeichnung
											FROM public.tbl_lehrverband
											WHERE studiengang_kz = tbl_lehreinheitgruppe.studiengang_kz
												AND semester = tbl_lehreinheitgruppe.semester
												AND verband = tbl_lehreinheitgruppe.verband
												AND gruppe = tbl_lehreinheitgruppe.gruppe
											LIMIT 1
										) 
									ELSE tbl_gruppe.beschreibung
								END AS beschreibung");
		$this->addSelect("
									CASE
										WHEN trim(COALESCE(tbl_lehreinheitgruppe.gruppe_kurzbz, '')) = '' THEN
										(
											SELECT EXISTS (
												SELECT 1
												FROM lehre.tbl_stundenplandev sp
												WHERE sp.lehreinheit_id = tbl_lehreinheitgruppe.lehreinheit_id
													AND sp.studiengang_kz = tbl_lehreinheitgruppe.studiengang_kz
													AND sp.semester = tbl_lehreinheitgruppe.semester
													AND trim(COALESCE(sp.verband, '')) = trim(COALESCE(tbl_lehreinheitgruppe.verband, ''))
													AND trim(COALESCE(sp.gruppe, ''))  = trim(COALESCE(tbl_lehreinheitgruppe.gruppe, ''))
													AND trim(COALESCE(sp.gruppe_kurzbz, '')) = ''
											)
										)
									ELSE
										(
											SELECT EXISTS (
												SELECT 1
												FROM lehre.tbl_stundenplandev sp
												WHERE sp.lehreinheit_id = tbl_lehreinheitgruppe.lehreinheit_id
													AND sp.studiengang_kz = tbl_lehreinheitgruppe.studiengang_kz
													AND sp.semester = tbl_lehreinheitgruppe.semester
													AND trim(COALESCE(sp.verband, '')) = trim(COALESCE(tbl_lehreinheitgruppe.verband, ''))
													AND trim(COALESCE(sp.gruppe, ''))  = trim(COALESCE(tbl_lehreinheitgruppe.gruppe, ''))
													AND trim(COALESCE(sp.gruppe_kurzbz, '')) = trim(COALESCE(tbl_lehreinheitgruppe.gruppe_kurzbz, ''))
											)
										)
										END AS verplant
									");

		$this->addJoin('tbl_studiengang', 'studiengang_kz', 'LEFT');
		$this->addJoin('public.tbl_gruppe', 'gruppe_kurzbz', 'LEFT');

		$this->db->where('lehreinheit_id', $lehreinheit_id);
		$this->db->group_start()
			->where('tbl_gruppe.direktinskription !=', true)
			->or_where('tbl_gruppe.direktinskription IS NULL')
			->group_end();
		return $this->load();
	}
}
