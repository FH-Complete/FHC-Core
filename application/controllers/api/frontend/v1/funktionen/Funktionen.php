<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class Funktionen extends FHCAPI_Controller
{
	public function __construct()
	{
		//TODO(Manu) check permissions
		parent::__construct(array(
				'getAllFunctions' =>  ['admin:r', 'assistenz:r'],
				'getAllUserFunctions' =>  ['admin:r', 'assistenz:r'],
				'getOrgHeads' =>  ['admin:r', 'assistenz:r'],
				'getOrgetsForCompany' => ['admin:r', 'assistenz:r'],
				'getAllOrgUnits' => ['admin:r', 'assistenz:r'],
				'loadFunction' => ['admin:r', 'assistenz:r'],
				'insertFunction' => ['admin:rw', 'assistenz:rw'],
				'updateFunction' => ['admin:rw', 'assistenz:rw'],
				'deleteFunction' => ['admin:rw', 'assistenz:rw'],
			)
		);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
		$this->load->library('form_validation');

		// Load language phrases
		$this->loadPhrases([
			'ui',
		]);

		// Load models
		$this->load->model('extensions/FHC-Core-Personalverwaltung/Api_model', 'ApiModel');
		$this->load->model('ressource/Funktion_model', 'FunktionModel');
		$this->load->model('person/Benutzerfunktion_model', 'BenutzerfunktionModel');

		$this->load->model('organisation/Organisationseinheit_model', 'OrganisationseinheitModel');
	}

	public function getAllFunctions()
	{
		$this->FunktionModel->addSelect("funktion_kurzbz");
		$this->FunktionModel->addSelect("beschreibung");
		$this->FunktionModel->addSelect("aktiv");
		$this->FunktionModel->addSelect("beschreibung AS label");
		$this->FunktionModel->addOrder("beschreibung");
		$result = $this->FunktionModel->load();

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getOrgHeads()
	{
		$result = $this->OrganisationseinheitModel->getHeads();

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getAllUserFunctions($uid)
	{
		if(!$uid)
		{
			$this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'UID']), self::ERROR_TYPE_GENERAL);
		}

		$sql = "
			SELECT
				dv.dienstverhaeltnis_id,
				un.bezeichnung || ' (' || TO_CHAR(dv.von, 'DD.MM.YYYY') || CASE WHEN dv.bis IS NOT NULL THEN ' - ' 
				 || TO_CHAR(dv.bis, 'DD.MM.YYYY') ELSE '' END || ')' AS dienstverhaeltnis_unternehmen ,
				'[' || oet.bezeichnung || '] ' || oe.bezeichnung AS funktion_oebezeichnung,
				f.beschreibung AS funktion_beschreibung,
				bf.*,
				fb.bezeichnung AS fachbereich_bezeichnung,
			    CASE
					WHEN
						bf.datum_bis IS NOT NULL AND bf.datum_bis::date < now()::date
					THEN
						false
					ELSE
						true
				END aktiv
			FROM
				public.tbl_benutzerfunktion bf
			JOIN
				public.tbl_organisationseinheit oe ON oe.oe_kurzbz = bf.oe_kurzbz
			JOIN
				public.tbl_organisationseinheittyp oet ON oe.organisationseinheittyp_kurzbz = oet.organisationseinheittyp_kurzbz
            JOIN
				public.tbl_funktion f ON f.funktion_kurzbz = bf.funktion_kurzbz
			LEFT JOIN
				hr.tbl_vertragsbestandteil_funktion vf ON vf.benutzerfunktion_id = bf.benutzerfunktion_id
			LEFT JOIN
				hr.tbl_vertragsbestandteil v ON vf.vertragsbestandteil_id = v.vertragsbestandteil_id
			LEFT JOIN
				hr.tbl_dienstverhaeltnis dv ON v.dienstverhaeltnis_id = dv.dienstverhaeltnis_id
			LEFT JOIN
				public.tbl_organisationseinheit un ON dv.oe_kurzbz = un.oe_kurzbz
			LEFT JOIN
				public.tbl_fachbereich fb ON fb.fachbereich_kurzbz = bf.fachbereich_kurzbz
            WHERE
				bf.uid = ?
            ORDER BY
				bf.datum_von, bf.datum_von ASC";

		$benutzerfunktionen = $this->BenutzerfunktionModel->execReadOnlyQuery($sql, array($uid));
		$data = $this->getDataOrTerminateWithError($benutzerfunktionen);

		$this->terminateWithSuccess($data);
	}

	/*
	 * returns list of all organisation units
	 * as key value list to be used in select or autocomplete
	 */
	public function getAllOrgUnits()
	{
		$sql = "
			SELECT
				oe.oe_kurzbz, oe.aktiv,
				'[' || COALESCE(oet.bezeichnung, oet.organisationseinheittyp_kurzbz) ||
				'] ' || COALESCE(oe.bezeichnung, oe.oe_kurzbz) AS label
			FROM public.tbl_organisationseinheit oe
			JOIN public.tbl_organisationseinheittyp oet ON oe.organisationseinheittyp_kurzbz = oet.organisationseinheittyp_kurzbz
			ORDER BY oet.bezeichnung ASC, oe.bezeichnung ASC";

		$result = $this->OrganisationseinheitModel->execReadOnlyQuery($sql);
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	/*
	 * return list of child orgets for a given company orget_kurzbz
	 * as key value list to be used in select or autocomplete
	 */
	public function getOrgetsForCompany($companyOrgetkurzbz = null)
	{
		$sql = "
			SELECT
				oe.oe_kurzbz, oe.aktiv,
				'[' || COALESCE(oet.bezeichnung, oet.organisationseinheittyp_kurzbz) ||
				'] ' || COALESCE(oe.bezeichnung, oe.oe_kurzbz) AS label
			FROM (
					WITH RECURSIVE oes(oe_kurzbz, oe_parent_kurzbz) as
					(
						SELECT oe_kurzbz, oe_parent_kurzbz FROM public.tbl_organisationseinheit
						WHERE oe_kurzbz=?
						UNION ALL
						SELECT o.oe_kurzbz, o.oe_parent_kurzbz FROM public.tbl_organisationseinheit o, oes
						WHERE o.oe_parent_kurzbz=oes.oe_kurzbz
					)
					SELECT oe_kurzbz
					FROM oes
					GROUP BY oe_kurzbz
			) c
			JOIN public.tbl_organisationseinheit oe ON oe.oe_kurzbz = c.oe_kurzbz
			JOIN public.tbl_organisationseinheittyp oet ON oe.organisationseinheittyp_kurzbz = oet.organisationseinheittyp_kurzbz
			ORDER BY oet.bezeichnung ASC, oe.bezeichnung ASC";

		$childorgets = $this->OrganisationseinheitModel->execReadOnlyQuery($sql, array($companyOrgetkurzbz));
		$data = $this->getDataOrTerminateWithError($childorgets);

		$this->terminateWithSuccess($data);
	}

	public function loadFunction($benutzerfunktion_id)
	{
		$this->BenutzerfunktionModel->addSelect("*");
		$result = $this->BenutzerfunktionModel->loadWhere(
			array('benutzerfunktion_id' => $benutzerfunktion_id)
		);
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(current($data));
	}

	public function insertFunction()
	{
		$this->load->library('form_validation');
		$authUID = getAuthUID();

		$uid = $this->input->post('uid');

		if(!$uid)
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'UID']), self::ERROR_TYPE_GENERAL);
		}

		$formData = $this->input->post('formData');

		$datum_von = $formData['datum_von'] ?? null;
		$datum_bis = $formData['datum_bis'] ?? null;
		$formData['oe_kurzbz'] = is_array($formData['oe_kurzbz']) ? $formData['oe_kurzbz']['oe_kurzbz'] : $formData['oe_kurzbz'];
		$formData['funktion_kurzbz'] = is_array($formData['funktion_kurzbz'])
			? $formData['funktion_kurzbz']['funktion_kurzbz']
			: $formData['funktion_kurzbz'];
		$bezeichnung = $formData['bezeichnung'] ?? null;
		$wochenstunden = $formData['wochenstunden'] ?? null;

		$this->form_validation->set_data($formData);
		$this->form_validation->set_rules('datum_von', 'VonDatum', 'required|is_valid_date', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'VonDatum']),
			'is_valid_date' => $this->p->t('ui', 'error_notValidDate', ['field' => 'VonDatum'])
		]);
		$this->form_validation->set_rules('datum_bis', 'BisDatum', 'is_valid_date', [
			'is_valid_date' => $this->p->t('ui', 'error_notValidDate', ['field' => 'BisDatum'])
		]);
		$this->form_validation->set_rules('oe_kurzbz', 'Organisationseinheit', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Organisationseinheit'])
		]);
		$this->form_validation->set_rules('funktion_kurzbz', 'Funktion', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Funktion'])
		]);
		$this->form_validation->set_rules('wochenstunden', 'Wochenstunden', 'numeric', [
			'numeric' => $this->p->t('ui', 'error_fieldNotNumeric', ['field' => 'Wochenstunden'])
		]);

		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$result = $this->BenutzerfunktionModel->insert([
			'uid' => $uid,
			'datum_von' => $datum_von,
			'datum_bis' => $datum_bis ,
			'oe_kurzbz' => 	$formData['oe_kurzbz'],
			'funktion_kurzbz' => $formData['funktion_kurzbz'],
			'bezeichnung' => $bezeichnung,
			'wochenstunden' => $wochenstunden,
			'insertamum' => date('c'),
			'insertvon' => $authUID,
		]);

		$data = $this->getDataOrTerminateWithError($result);
		$this->terminateWithSuccess($data);
	}

	public function updateFunction()
	{
		$this->load->library('form_validation');
		$authUID = getAuthUID();

		$uid = $this->input->post('uid');

		if(!$uid)
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'UID']), self::ERROR_TYPE_GENERAL);
		}
		$benutzerfunktion_id = $this->input->post('benutzerfunktion_id');

		if(!$benutzerfunktion_id)
		{
			return $this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Benutzerfunktion ID']), self::ERROR_TYPE_GENERAL);
		}

		$formData = $this->input->post('formData');

		$datum_von = $formData['datum_von'] ?? null;
		$datum_bis = $formData['datum_bis'] ?? null;
		$formData['oe_kurzbz'] = is_array($formData['oe_kurzbz']) ? $formData['oe_kurzbz']['oe_kurzbz'] : $formData['oe_kurzbz'];
		$formData['funktion_kurzbz'] = is_array($formData['funktion_kurzbz'])
			? $formData['funktion_kurzbz']['funktion_kurzbz']
			: $formData['funktion_kurzbz'];
		$bezeichnung = $formData['bezeichnung'] ?? null;
		$wochenstunden = $formData['wochenstunden'] ?? null;

		$this->form_validation->set_data($formData);
		$this->form_validation->set_rules('datum_von', 'VonDatum', 'required|is_valid_date', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'VonDatum']),
			'is_valid_date' => $this->p->t('ui', 'error_notValidDate', ['field' => 'VonDatum'])
		]);
		$this->form_validation->set_rules('datum_bis', 'BisDatum', 'is_valid_date', [
			'is_valid_date' => $this->p->t('ui', 'error_notValidDate', ['field' => 'BisDatum'])
		]);
		$this->form_validation->set_rules('oe_kurzbz', 'Organisationseinheit', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Organisationseinheit'])
		]);
		$this->form_validation->set_rules('funktion_kurzbz', 'Funktion', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Funktion'])
		]);
		$this->form_validation->set_rules('wochenstunden', 'Wochenstunden', 'numeric', [
			'numeric' => $this->p->t('ui', 'error_fieldNotNumeric', ['field' => 'Wochenstunden'])
		]);

		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$result = $this->BenutzerfunktionModel->update(
			[
				'benutzerfunktion_id' => $benutzerfunktion_id,
			],
			[
				'uid' => $uid,
				'datum_von' => $datum_von,
				'datum_bis' => $datum_bis ,
				'oe_kurzbz' => 	$formData['oe_kurzbz'],
				'funktion_kurzbz' => $formData['funktion_kurzbz'],
				'bezeichnung' => $bezeichnung,
				'wochenstunden' => $wochenstunden,
				'updateamum' => date('c'),
				'updatevon' => $authUID,
			]
		);

		$data = $this->getDataOrTerminateWithError($result);
		$this->terminateWithSuccess($data);
	}

	public function deleteFunction($benutzerfunktion_id)
	{
		$result = $this->BenutzerfunktionModel->delete(
			array('benutzerfunktion_id' => $benutzerfunktion_id)
		);

		$data = $this->getDataOrTerminateWithError($result);
		$this->terminateWithSuccess($data);
	}
}
