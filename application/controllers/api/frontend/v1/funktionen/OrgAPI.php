<?php

defined('BASEPATH') || exit('No direct script access allowed');

class OrgAPI extends Auth_Controller
{

	const DEFAULT_PERMISSION = 'basis/mitarbeiter:rw';
	const HANDYVERWALTUNG_PERMISSION = 'extension/pv21_handyverwaltung:rw';

	public function __construct() {
		parent::__construct(array(
				'getOrgHeads' => OrgAPI::DEFAULT_PERMISSION,
				'getOrgStructure' => OrgAPI::DEFAULT_PERMISSION,
				'getOrgPersonen' => OrgAPI::DEFAULT_PERMISSION,
				'getCompanyByOrget'  => [OrgAPI::DEFAULT_PERMISSION, self::HANDYVERWALTUNG_PERMISSION],
				'getOrgetsForCompany' => OrgAPI::DEFAULT_PERMISSION,
				'getUnternehmen' => [OrgAPI::DEFAULT_PERMISSION, self::HANDYVERWALTUNG_PERMISSION],
			)
		);
		$this->load->library('AuthLib');
		$this->load->model('extensions/FHC-Core-Personalverwaltung/Organisationseinheit_model', 'OrganisationseinheitModel');
		$this->load->model('extensions/FHC-Core-Personalverwaltung/Api_model','ApiModel');
	}

	// -----------------------------
	// Organisation
	// -----------------------------

	function getOrgHeads()
	{
		$data = $this->OrganisationseinheitModel->getHeads();
		return $this->outputJson($data);
	}

	function getOrgStructure()
	{
		$oe = $this->input->get('oe', TRUE);

		$data = $this->OrganisationseinheitModel->getOrgStructure($oe);
		return $this->outputJson($data);
	}

	function getOrgPersonen()
	{
		$oe = $this->input->get('oe', TRUE);

		$data = $this->OrganisationseinheitModel->getPersonen($oe);
		return $this->outputJson($data);
	}




	public function getCompanyByOrget($oe_kurzbz)
	{

		$sql = <<<EOSQL
        WITH RECURSIVE unternehmen as
        (
                SELECT oe_kurzbz, oe_parent_kurzbz FROM public.tbl_organisationseinheit
                WHERE oe_kurzbz=?
                UNION ALL
                SELECT o.oe_kurzbz, o.oe_parent_kurzbz
                FROM   public.tbl_organisationseinheit AS o
                       INNER JOIN unternehmen u  ON u.oe_parent_kurzbz=o.oe_kurzbz
        )
        SELECT *
        FROM unternehmen
        WHERE oe_parent_kurzbz is null;
EOSQL;
		$childorgets = $this->OrganisationseinheitModel->execReadOnlyQuery($sql, array($oe_kurzbz));
		if( hasData($childorgets) )
		{
			$this->outputJson($childorgets);
			return;
		}
		else
		{
			$this->outputJsonError('no orgets found for parent oe_kurzbz ' . $oe_kurzbz );
			return;
		}
	}

	/*
	 * return list of child orgets for a given company orget_kurzbz
	 * as key value list to be used in select or autocomplete
	 */
	public function getOrgetsForCompany($companyOrgetkurzbz=null)
	{
		if( empty($companyOrgetkurzbz) )
		{
			$this->outputJsonError('Missing Parameter <companyOrgetkurzbz>');
			return;
		}

		$sql = <<<EOSQL
			SELECT
				oe.oe_kurzbz AS value,
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
			ORDER BY oet.bezeichnung ASC, oe.bezeichnung ASC

EOSQL;

		$childorgets = $this->OrganisationseinheitModel->execReadOnlyQuery($sql, array($companyOrgetkurzbz));
		if( hasData($childorgets) )
		{
			$this->outputJson($childorgets);
			return;
		}
		else
		{
			$this->outputJsonError('no orgets found for parent oe_kurzbz ' . $companyOrgetkurzbz );
			return;
		}
	}


	public function getUnternehmen()
	{
		$this->OrganisationseinheitModel->resetQuery();
		$this->OrganisationseinheitModel->addSelect('oe_kurzbz AS value, bezeichnung AS label, \'false\'::boolean AS disabled');
		$this->OrganisationseinheitModel->addOrder('bezeichnung', 'ASC');
		$unternehmen = $this->OrganisationseinheitModel->loadWhere('oe_parent_kurzbz IS NULL');
		if( hasData($unternehmen) )
		{
			$this->outputJson($unternehmen);
			return;
		}
		else
		{
			$this->outputJsonError('no companies (orgets with parent NULL) found');
			return;
		}
	}

}