<?php

defined('BASEPATH') || exit('No direct script access allowed');

class FunctionsAPI extends Auth_Controller
{

	const DEFAULT_PERMISSION = 'basis/mitarbeiter:rw';
	const HANDYVERWALTUNG_PERMISSION = 'extension/pv21_handyverwaltung:rw';

	public function __construct() {

		//TODO(Manu) check permissions
		parent::__construct(array(
				'getAllFunctions' => FunctionsAPI::DEFAULT_PERMISSION,
				'getContractFunctions' => FunctionsAPI::DEFAULT_PERMISSION,
				'getCurrentFunctions' => FunctionsAPI::DEFAULT_PERMISSION,
				'getAllUserFunctions' => [FunctionsAPI::DEFAULT_PERMISSION, self::HANDYVERWALTUNG_PERMISSION],
			)
		);
		$this->load->library('AuthLib');
		$this->load->model('extensions/FHC-Core-Personalverwaltung/Api_model','ApiModel');
		$this->load->model('ressource/Funktion_model', 'FunktionModel');
		$this->load->model('person/Benutzerfunktion_model', 'BenutzerfunktionModel');
	}



	/*
	 * return list of all functions
	 * as key value list to be used in select or autocomplete
	 */
	public function getAllFunctions()
	{
		$sql = <<<EOSQL
			SELECT
				funktion_kurzbz AS value, beschreibung AS label
			FROM
				public.tbl_funktion
			WHERE
				aktiv = true
			ORDER BY beschreibung ASC
EOSQL;

		$fkts = $this->FunktionModel->execReadOnlyQuery($sql);
		if( hasData($fkts) )
		{
			$this->outputJson($fkts);
			return;
		}
		else
		{
			$this->outputJsonError('no contract relevant funktionen found');
			return;
		}
	}

	/*
	 * return list of contract relevant functions
	 * as key value list to be used in select or autocomplete
	 */
	public function getContractFunctions($mode='all')
	{
		$addwhere = '';
		switch ($mode)
		{
			case 'zuordnung':
				$addwhere = ' AND funktion_kurzbz LIKE \'%zuordnung%\'';
				break;
			case 'funktion':
				$addwhere = ' AND funktion_kurzbz NOT LIKE \'%zuordnung%\'';
				break;
			case 'all':
			default:
				$addwhere = '';
				break;
		}

		$sql = <<<EOSQL
			SELECT
				funktion_kurzbz AS value, beschreibung AS label
			FROM
				public.tbl_funktion
			WHERE
				aktiv = true AND vertragsrelevant = true
				{$addwhere}
			ORDER BY beschreibung ASC
EOSQL;

		$fkts = $this->FunktionModel->execReadOnlyQuery($sql);
		if( hasData($fkts) )
		{
			$this->outputJson($fkts);
			return;
		}
		else
		{
			$this->outputJsonError('no contract relevant funktionen found');
			return;
		}
	}

	/*
	 * return list of child orgets for a given company orget_kurzbz
	 * as key value list to be used in select or autocomplete
	 */
	public function getCurrentFunctions($uid, $companyOrgetkurzbz)
	{
		if( empty($uid) )
		{
			$this->outputJsonError('Missing Parameter <uid>');
		}

		if( empty($companyOrgetkurzbz) )
		{
			$this->outputJsonError('Missing Parameter <companyOrgetkurzbz>');
		}

		$sql = <<<EOSQL
			SELECT
				bf.benutzerfunktion_id AS value, f.beschreibung || ', '
					|| oe.bezeichnung || ' [' || oet.bezeichnung || '], '
					|| COALESCE(to_char(bf.datum_von, 'dd.mm.YYYY'), 'n/a')
					|| ' - ' || COALESCE(to_char(bf.datum_bis, 'dd.mm.YYYY'), 'n/a')
					|| COALESCE(dvu.attachedtovb, '') AS label
			FROM (
					WITH RECURSIVE oes(oe_kurzbz, oe_parent_kurzbz) as
					(
						SELECT oe_kurzbz, oe_parent_kurzbz FROM public.tbl_organisationseinheit
						WHERE oe_kurzbz = ?
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
			JOIN public.tbl_benutzerfunktion bf ON bf.oe_kurzbz = oe.oe_kurzbz
			JOIN public.tbl_funktion f ON f.funktion_kurzbz = bf.funktion_kurzbz
			LEFT JOIN (
				SELECT
					benutzerfunktion_id, ' [DV]' AS attachedtovb
				FROM
					"hr"."tbl_vertragsbestandteil_funktion"
				GROUP BY
					benutzerfunktion_id
			) dvu ON dvu.benutzerfunktion_id = bf.benutzerfunktion_id
			WHERE bf.uid = ?
			ORDER BY f.beschreibung ASC

EOSQL;

		$benutzerfunktionen = $this->BenutzerfunktionModel->execReadOnlyQuery($sql, array($companyOrgetkurzbz, $uid));
		if( hasData($benutzerfunktionen) )
		{
			$this->outputJson($benutzerfunktionen);
			return;
		}
		else
		{
			$this->outputJsonError('no benutzerfunktionen found for uid ' . $uid . ' and oe_kurzbz ' . $companyOrgetkurzbz );
			return;
		}
	}

	/*
	 * return list of functions for a uid
	 * as objects to be used in as datasource
	 */
	public function getAllUserFunctions($uid)
	{
		if( empty($uid) )
		{
			$this->outputJsonError('Missing Parameter <uid>');
		}

		$sql = <<<EOSQL
			SELECT
				dv.dienstverhaeltnis_id, 
				un.bezeichnung || ' (' || TO_CHAR(dv.von, 'DD.MM.YYYY') || CASE WHEN dv.bis IS NOT NULL THEN ' - ' || TO_CHAR(dv.bis, 'DD.MM.YYYY') ELSE '' END || ')' AS dienstverhaeltnis_unternehmen ,
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
				f.beschreibung, bf.datum_von ASC

EOSQL;

		$benutzerfunktionen = $this->BenutzerfunktionModel->execReadOnlyQuery($sql, array($uid));
		if( hasData($benutzerfunktionen) )
		{
			$this->outputJson($benutzerfunktionen);
			return;
		}
		else
		{
			$this->outputJsonError('no benutzerfunktionen found for uid ' . $uid);
			return;
		}
	}






}