<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class LektorLib
{
	private $_ci; // Code igniter instance

	public function __construct()
	{
		$this->_ci =& get_instance();
		$this->_ci->load->model('education/lehreinheit_model', 'LehreinheitModel');
		$this->_ci->load->model('education/Lehreinheitmitarbeiter_model', 'LehreinheitmitarbeiterModel');
		$this->_ci->load->model('organisation/Studiensemester_model','StudiensemesterModel');
		$this->_ci->load->model('ressource/Stundensatz_model', 'StundensatzModel');
		$this->_ci->load->model('vertragsbestandteil/Dienstverhaeltnis_model','DienstverhaeltnisModel');
		$this->_ci->load->model('organisation/Organisationseinheit_model', 'OrganisationseinheitModel');
		$this->_ci->load->model('ressource/mitarbeiter_model', 'MitarbeiterModel');
		$this->_ci->load->model('person/Benutzer_model', 'BenutzerModel');
	}

	public function addLektorToLehreinheit($lehreinheit_id, $mitarbeiter_uid)
	{
		$this->_ci->LehreinheitModel->addSelect('tbl_lehreinheit.*, tbl_lehrveranstaltung.studiengang_kz, semesterstunden');
		$this->_ci->LehreinheitModel->addJoin('lehre.tbl_lehrveranstaltung', 'lehrveranstaltung_id');
		$lehreinheit_result = $this->_ci->LehreinheitModel->loadWhere(array('lehreinheit_id' => $lehreinheit_id));

		if (isError($lehreinheit_result)) return $lehreinheit_result;

		if (!hasData($lehreinheit_result)) return error("Lehreinheit not found");

		$lehreinheit = getData($lehreinheit_result)[0];

		$already_assigned = $this->_ci->LehreinheitmitarbeiterModel->loadWhere(array('lehreinheit_id' => $lehreinheit->lehreinheit_id, 'mitarbeiter_uid' => $mitarbeiter_uid));

		if (isError($already_assigned)) return $already_assigned;

		if (hasData($already_assigned)) return error('Lektor already assigned');

		$studiensemester_result = $this->_ci->StudiensemesterModel->loadWhere(array('studiensemester_kurzbz' => $lehreinheit->studiensemester_kurzbz));
		if (isError($studiensemester_result)) return $studiensemester_result;
		$studiensemester = getData($studiensemester_result)[0];

		$stundensatz = $this->_ci->StundensatzModel->getDefaultStundensatz($mitarbeiter_uid, $studiensemester->start, $studiensemester->ende, 'lehre');
		$echter_dv_result = $this->_ci->DienstverhaeltnisModel->existsDienstverhaeltnis($mitarbeiter_uid, $studiensemester->start, $studiensemester->ende, 'echterdv');

		$echter_dv = false;

		if (hasData($echter_dv_result))
		{
			$echter_dv = true;
		}

		$maxstunden = $this->getMaxStunden($mitarbeiter_uid, $studiensemester->studiensemester_kurzbz, $lehreinheit->studiengang_kz, $echter_dv);

		$newData['semesterstunden'] = 0;
		$newData['planstunden'] = 0;
		if (!is_null($lehreinheit->semesterstunden))
		{
			$newData['semesterstunden'] = min($lehreinheit->semesterstunden, $maxstunden);
			$newData['planstunden'] = min($lehreinheit->semesterstunden, $maxstunden);
		}

		$newData['lehreinheit_id'] = $lehreinheit->lehreinheit_id;
		$newData['mitarbeiter_uid'] = $mitarbeiter_uid;
		$newData['lehrfunktion_kurzbz'] = 'Lektor';
		$newData['bismelden'] = true;
		$newData['insertvon'] = getAuthUID();
		$newData['insertamum'] = date('Y-m-d H:i:s');
		$newData['stundensatz'] = $stundensatz;
		$result = $this->_ci->LehreinheitmitarbeiterModel->insert($newData);

		if (isError($result)) return $result;

		return success("Lektor added successfully");
	}

	public function updateLektorFromLehreinheit($lehreinheit_id, $mitarbeiter_uid, $new_data)
	{
		$this->_ci->LehreinheitmitarbeiterModel->addSelect('lehre.tbl_lehreinheitmitarbeiter.*, lehre.tbl_lehreinheit.studiensemester_kurzbz, tbl_lehrveranstaltung.studiengang_kz');
		$this->_ci->LehreinheitmitarbeiterModel->addJoin('lehre.tbl_lehreinheit', 'lehreinheit_id');
		$this->_ci->LehreinheitmitarbeiterModel->addJoin('lehre.tbl_lehrveranstaltung', 'lehrveranstaltung_id');
		$lehreinheit_result = $this->_ci->LehreinheitmitarbeiterModel->loadWhere(array('lehreinheit_id' => $lehreinheit_id, 'mitarbeiter_uid' => $mitarbeiter_uid));

		if (isError($lehreinheit_result)) return $lehreinheit_result;

		if (!hasData($lehreinheit_result)) return error("Lehreinheit not found");

		$lehreinheit = getData($lehreinheit_result)[0];

		//TODO kollision check, wird vorerst nicht implementiert -> nur über das FAS möglich
		if (isset($new_data['mitarbeiter_uid']) && $new_data['mitarbeiter_uid'] !== $mitarbeiter_uid)
		{
			$this->_ci->load->model('ressource/stundenplandev_model', 'StundenplandevModel');
			$this->_ci->StundenplandevModel->addGroupBy('stundenplandev_id');
			$this->_ci->StundenplandevModel->addGroupBy('mitarbeiter_uid');
			$this->_ci->StundenplandevModel->addGroupBy('mitarbeiter_uid');
			$verplant = $this->_ci->StundenplandevModel->loadWhere(array('lehreinheit_id' => $lehreinheit_id, 'mitarbeiter_uid' => $mitarbeiter_uid));

			if (hasData($verplant))
				return error('Wechsel vom Mitarbeiter nicht möglich da er bereits verplant ist!');
		}
		$warning = '';
		if (isset($new_data['semesterstunden']))
		{
			$studiengang_result = $this->_ci->StudiengangModel->loadWhere(array('studiengang_kz' => $lehreinheit->studiengang_kz));
			if (isError($studiengang_result)) return $studiengang_result;
			if (!hasData($studiengang_result)) return error('Studiengang not found');
			$studiengang = getData($studiengang_result)[0];

			$studiensemester_result = $this->_ci->StudiensemesterModel->loadWhere(array('studiensemester_kurzbz' => $lehreinheit->studiensemester_kurzbz));
			if (isError($studiensemester_result)) return $studiensemester_result;
			$studiensemester = getData($studiensemester_result)[0];

			$echter_dv_result = $this->_ci->DienstverhaeltnisModel->existsDienstverhaeltnis($mitarbeiter_uid, $studiensemester->start, $studiensemester->ende, 'echterdv');

			$echter_dv = false;

			if (hasData($echter_dv_result))
			{
				$echter_dv = true;
			}

			$neue_stunden_eingerechnet = isset($new_data['bismelden']) ? $new_data['bismelden'] : $lehreinheit->bismelden;
			$alte_stunden_eingerechnet = $lehreinheit->bismelden;

			if (($new_data['semesterstunden'] > $lehreinheit->semesterstunden) || $neue_stunden_eingerechnet)
			{
				$stundengrenze_result = $this->_ci->OrganisationseinheitModel->getStundengrenze($studiengang->oe_kurzbz, $echter_dv);
				if (isError($stundengrenze_result)) return $stundengrenze_result;

				$stundengrenze = getData($stundengrenze_result)[0];

				$oe_result = $this->_ci->OrganisationseinheitModel->getChilds($stundengrenze->oe_kurzbz);
				$oe_array = hasData($oe_result) ? array_column(getData($oe_result), 'oe_kurzbz') : array('');

				if ($alte_stunden_eingerechnet && $neue_stunden_eingerechnet)
					$this->_ci->LehreinheitmitarbeiterModel->addSelect("(SUM(tbl_lehreinheitmitarbeiter.semesterstunden) - ($lehreinheit->semesterstunden) + {$this->_ci->LehreinheitmitarbeiterModel->db->escape($new_data['semesterstunden'])}) as summe");
				else if ($alte_stunden_eingerechnet && !$neue_stunden_eingerechnet)
					$this->_ci->LehreinheitmitarbeiterModel->addSelect("(SUM(tbl_lehreinheitmitarbeiter.semesterstunden) - ($lehreinheit->semesterstunden)) as summe");
				else if (!$alte_stunden_eingerechnet && $neue_stunden_eingerechnet)
					$this->_ci->LehreinheitmitarbeiterModel->addSelect("(SUM(tbl_lehreinheitmitarbeiter.semesterstunden) + ({$this->_ci->LehreinheitmitarbeiterModel->db->escape($new_data['semesterstunden'])})) as summe");
				else if (!$alte_stunden_eingerechnet && !$neue_stunden_eingerechnet)
					$this->_ci->LehreinheitmitarbeiterModel->addSelect("(SUM(tbl_lehreinheitmitarbeiter.semesterstunden)) as summe");

				$this->_ci->LehreinheitmitarbeiterModel->addJoin('lehre.tbl_lehreinheit', 'lehreinheit_id');
				$this->_ci->LehreinheitmitarbeiterModel->addJoin('lehre.tbl_lehrveranstaltung', 'lehrveranstaltung_id');
				$this->_ci->LehreinheitmitarbeiterModel->addJoin('public.tbl_studiengang', 'studiengang_kz');

				$this->_ci->LehreinheitmitarbeiterModel->db->where('mitarbeiter_uid', (isset($new_data['mitarbeiter_uid']) ? $new_data['mitarbeiter_uid'] : $mitarbeiter_uid));
				$this->_ci->LehreinheitmitarbeiterModel->db->where('studiensemester_kurzbz', $lehreinheit->studiensemester_kurzbz);
				$this->_ci->LehreinheitmitarbeiterModel->db->where('bismelden', true);
				$this->_ci->LehreinheitmitarbeiterModel->db->where('lower(mitarbeiter_uid) NOT LIKE', '_dummy%');

				$this->_ci->LehreinheitmitarbeiterModel->db->where_in('tbl_studiengang.oe_kurzbz', $oe_array);


				if(defined('FAS_LV_LEKTORINNENZUTEILUNG_STUNDEN_IGNORE_OE')
					&& is_array(FAS_LV_LEKTORINNENZUTEILUNG_STUNDEN_IGNORE_OE)
					&& count(FAS_LV_LEKTORINNENZUTEILUNG_STUNDEN_IGNORE_OE) > 0)
				{
					$this->_ci->LehreinheitmitarbeiterModel->db->where_not_in('tbl_studiengang.oe_kurzbz', FAS_LV_LEKTORINNENZUTEILUNG_STUNDEN_IGNORE_OE);
				}

				$summe_result = $this->_ci->LehreinheitmitarbeiterModel->load();

				if (isError($summe_result)) return $summe_result;

				if (!hasData($summe_result)) return error('Fehler beim Ermitteln der Gesamtstunden');

				$summe = getData($summe_result)[0]->summe;

				if ($summe > $stundengrenze->stunden)
				{

					if (!$echter_dv && (!$this->_ci->permissionlib->isBerechtigt('admin')))
					{
						if (!$this->LehrauftragAufFirma(isset($formData['mitarbeiter_uid']) ? $formData['mitarbeiter_uid'] : $mitarbeiter_uid))
							return error("ACHTUNG: Die maximal erlaubte Semesterstundenanzahl des Lektors von $summe Stunden ($stundengrenze->stunden) wurde ueberschritten!\nDaten wurden NICHT gespeichert!\n\n");
					}
					else
					{
						$warning .= "ACHTUNG: Die maximal erlaubte Semesterstundenanzahl des Lektors von $summe Stunden ($stundengrenze->stunden) wurde ueberschritten!\nDaten wurden gespeichert!\n\n";
					}

					$stunden_limit_result = $this->getStundenInstitut($mitarbeiter_uid, $lehreinheit->studiensemester_kurzbz, $oe_array);

					if (hasData($stunden_limit_result))
					{
						$stunden_limit_array = getData($stunden_limit_result);
						foreach ($stunden_limit_array as $stunden_limit)
						{
							$warning .= $stunden_limit->summe . ' Stunden ' . $stunden_limit->bezeichnung . "\n";
						}
					}
				}
			}
		}

		$benutzer_result = $this->_ci->BenutzerModel->load(array(isset($formData['mitarbeiter_uid']) ? $formData['mitarbeiter_uid'] : $mitarbeiter_uid));

		if (isError($benutzer_result)) return $benutzer_result;

		if (!hasData($benutzer_result)) return error('Benutzer not found');

		$benutzer_aktiv = getData($benutzer_result)[0]->aktiv;

		if (!$benutzer_aktiv)
			$warning .= "Achtung: Der/Die Benutzer*in ist inaktiv!\nBitte informieren Sie die Personalbteilung.\nDaten wurden gespeichert.\n\n";

		$updatableFields = array(
			'semesterstunden',
			'planstunden',
			'stundensatz',
			'faktor',
			'anmerkung',
			'lehrfunktion_kurzbz',
			'mitarbeiter_uid',
			'bismelden'
		);

		$updateData = array();
		foreach ($updatableFields as $field)
		{
			$value = isset($new_data[$field]) ? $new_data[$field] : null;

			if ($value !== null)
			{
				$updateData[$field] = $value;
			}
		}
		$updateData['updatevon'] = getAuthUID();
		$updateData['updateamum'] = date('Y-m-d H:i:s');

		$result = $this->_ci->LehreinheitmitarbeiterModel->update(array('lehreinheit_id' => $lehreinheit_id, 'mitarbeiter_uid' => $mitarbeiter_uid), $updateData);

		if (isError($result)) return $result;

		if ($warning !== '') return success(['warning' => $warning]);

		return success('Erfolgreich geupdated');
	}

	private function getMaxStunden($mitarbeiter_uid, $studiensemester_kurzbz, $studiengang_kz, $echter_dv)
	{
		$maxstunden = 9999;

		$studiengang_result = $this->_ci->StudiengangModel->loadWhere(array('studiengang_kz' => $studiengang_kz));
		if (isError($studiengang_result)) return $studiengang_result;

		$studiengang = getData($studiengang_result)[0];

		$stundengrenze_result = $this->_ci->OrganisationseinheitModel->getStundengrenze($studiengang->oe_kurzbz, $echter_dv);
		if (isError($stundengrenze_result)) return $stundengrenze_result;

		$stundengrenze = getData($stundengrenze_result)[0];
		$maxstunden = $stundengrenze->stunden;

		$lehrauftrag_firma = $this->LehrauftragAufFirma($mitarbeiter_uid);

		if (!$echter_dv && !$lehrauftrag_firma)
		{
			$oe_result = $this->_ci->OrganisationseinheitModel->getChilds($stundengrenze->oe_kurzbz);
			$oe_array = hasData($oe_result) ? array_column(getData($oe_result), 'oe_kurzbz') : array('');

			$stunden_summe_result = $this->getSumSemesterstunden($mitarbeiter_uid, $studiensemester_kurzbz, $oe_array);

			$stunden_summe = hasData($stunden_summe_result) ? getData($stunden_summe_result)[0]->summe : 0;

			if ($stunden_summe >= $maxstunden && (!$this->_ci->permissionlib->isBerechtigt('admin')))
			{
				$stunden_limit_result = $this->getStundenInstitut($mitarbeiter_uid, $studiensemester_kurzbz, $oe_array);

				$error = "ACHTUNG: Die maximal erlaubte Semesterstundenanzahl des Lektors von $maxstunden Stunden ($stundengrenze->oe_kurzbz) wurde ueberschritten!\n
				 			Daten wurden NICHT gespeichert!\n\n";

				if (hasData($stunden_limit_result))
				{
					$stunden_limit_array = getData($stunden_limit_result);

					foreach ($stunden_limit_array as $stunden_limit)
					{
						$error .= $stunden_limit->summe . ' Stunden ' . $stunden_limit->bezeichnung . "\n";
					}
				}
				return error($error);
			}
			else
				$maxstunden =- $stunden_summe;
		}
		return $maxstunden;
	}

	private function LehrauftragAufFirma($mitarbeiter_uid)
	{
		$this->_ci->MitarbeiterModel->addJoin('tbl_benutzer', 'tbl_mitarbeiter.mitarbeiter_uid = tbl_benutzer.uid');
		$this->_ci->MitarbeiterModel->addJoin('tbl_person', 'person_id');
		$this->_ci->MitarbeiterModel->addJoin('tbl_adresse', 'person_id', 'LEFT');
		$this->_ci->MitarbeiterModel->addOrder('zustelladresse', 'DESC');
		$this->_ci->MitarbeiterModel->addOrder('firma_id');
		$this->_ci->MitarbeiterModel->addLimit(1);
		$firma_result = $this->_ci->MitarbeiterModel->loadWhere(array('mitarbeiter_uid' => $mitarbeiter_uid));
		$firma = getData($firma_result)[0]->firma_id;
		return !is_null($firma);
	}

	private function getSumSemesterstunden($mitarbeiter_uid, $studiensemester_kurzbz, $oe_array = array())
	{
		$this->_ci->LehreinheitmitarbeiterModel->addSelect('SUM(tbl_lehreinheitmitarbeiter.semesterstunden) as summe');
		$this->_ci->LehreinheitmitarbeiterModel->addJoin('lehre.tbl_lehreinheit', 'lehreinheit_id');
		$this->_ci->LehreinheitmitarbeiterModel->addJoin('lehre.tbl_lehrveranstaltung', 'lehrveranstaltung_id');
		$this->_ci->LehreinheitmitarbeiterModel->addJoin('public.tbl_studiengang', 'studiengang_kz');
		$this->_ci->LehreinheitmitarbeiterModel->db->where('mitarbeiter_uid', $mitarbeiter_uid);
		$this->_ci->LehreinheitmitarbeiterModel->db->where('studiensemester_kurzbz', $studiensemester_kurzbz);
		$this->_ci->LehreinheitmitarbeiterModel->db->where('bismelden', true);
		$this->_ci->LehreinheitmitarbeiterModel->db->where('lower(mitarbeiter_uid) NOT LIKE', '_dummy%');
		$this->_ci->LehreinheitmitarbeiterModel->db->where_in('tbl_studiengang.oe_kurzbz', $oe_array);
		return $this->_ci->LehreinheitmitarbeiterModel->load();
	}

	private function getStundenInstitut($mitarbeiter_uid, $studiensemester_kurzbz, $oe_array = array())
	{
		$this->_ci->LehreinheitmitarbeiterModel->addSelect('SUM(tbl_lehreinheitmitarbeiter.semesterstunden) as summe, tbl_studiengang.bezeichnung');
		$this->_ci->LehreinheitmitarbeiterModel->addJoin('lehre.tbl_lehreinheit', 'lehreinheit_id');
		$this->_ci->LehreinheitmitarbeiterModel->addJoin('lehre.tbl_lehrveranstaltung', 'lehrveranstaltung_id');
		$this->_ci->LehreinheitmitarbeiterModel->addJoin('public.tbl_studiengang', 'studiengang_kz');
		$this->_ci->LehreinheitmitarbeiterModel->db->where('mitarbeiter_uid', $mitarbeiter_uid);
		$this->_ci->LehreinheitmitarbeiterModel->db->where('studiensemester_kurzbz', $studiensemester_kurzbz);
		$this->_ci->LehreinheitmitarbeiterModel->db->where('bismelden', true);
		$this->_ci->LehreinheitmitarbeiterModel->db->where_in('tbl_studiengang.oe_kurzbz', $oe_array);

		if(defined('FAS_LV_LEKTORINNENZUTEILUNG_STUNDEN_IGNORE_OE')
			&& is_array(FAS_LV_LEKTORINNENZUTEILUNG_STUNDEN_IGNORE_OE)
			&& count(FAS_LV_LEKTORINNENZUTEILUNG_STUNDEN_IGNORE_OE) > 0)
		{
			$this->_ci->LehreinheitmitarbeiterModel->db->where_not_in('tbl_studiengang.oe_kurzbz', FAS_LV_LEKTORINNENZUTEILUNG_STUNDEN_IGNORE_OE);
		}

		$this->_ci->LehreinheitmitarbeiterModel->addGroupBy('tbl_studiengang.bezeichnung');
		return $this->_ci->LehreinheitmitarbeiterModel->load();
	}
}
