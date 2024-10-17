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
	}

	/**
	 * Gets group directly assigned to a Lehreinheit
	 * @param $lehreinheit_id
	 * @return array
	 */
	public function getDirectGroup($lehreinheit_id)
	{
		$this->addJoin('public.tbl_gruppe', 'gruppe_kurzbz');
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
}
