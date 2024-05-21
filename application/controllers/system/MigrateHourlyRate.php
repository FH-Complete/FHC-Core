<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class MigrateHourlyRate extends CLI_Controller
{
	
	CONST DEFAULT_OE = 'gst';
	CONST DEFAULT_DATE = '1970-01-01';
	CONST STUNDENSTAZTYP_LEHRE = 'lehre';
	CONST STUNDENSTAZTYP_KALKULATORISCH = 'kalkulatorisch';
	
	private $_ci;

	public function __construct()
	{
		parent::__construct();

		$this->_ci = & get_instance();
		
		$this->load->model('codex/Bisverwendung_model', 'BisVerwendungModel');
		$this->load->model('person/Benutzerfunktion_model', 'BenutzerfunktionModel');
		$this->load->model('ressource/Stundensatz_model', 'StundensatzModel');
	}

	public function index($user = null)
	{
		echo "Lehre Stundensaetze werden migriert.\n";
		$mitarbeiterResult = $this->_getMitarbeiterStunden($user);
		if (isError($mitarbeiterResult)) return $mitarbeiterResult;
		if (!hasData($mitarbeiterResult)) return error('Keine Mitarbeiterstunden gefunden');

		$mitarbeiterArray = getData($mitarbeiterResult);

		foreach ($mitarbeiterArray as $mitarbeiter)
		{
			$this->_getUnternehmen($mitarbeiter);
			$insertResult = $this->_addStundensatz($mitarbeiter, self::STUNDENSTAZTYP_LEHRE, self::DEFAULT_DATE);
			if (isError($insertResult)) return $insertResult;
		}
		
		if( $this->checkIfSAPSyncTableExists() )
		{
			echo "SAP Sync Tabelle gefunden. SAP Stundensaetze werden migriert.\n";
			$sapResult = $this->_getSapStunden($user);
			if (isError($sapResult)) return $sapResult;
			if (!hasData($sapResult)) return error('Keinen kalkulatorischen Stundensaetze gefunden');

			$mitarbeiterArray = getData($sapResult);

			foreach ($mitarbeiterArray as $mitarbeiter)
			{
				$this->_getUnternehmen($mitarbeiter);
				$insertResult = $this->_addStundensatz($mitarbeiter, self::STUNDENSTAZTYP_KALKULATORISCH, date_format(date_create($mitarbeiter->beginn), 'Y-m-d'));
				if (isError($insertResult)) return $insertResult;
			}
		}
		else
		{
		    echo "SAP Sync Tabelle nicht gefunden. Ignoriere SAP Stundensaetze.\n";
		}
	}

	protected function checkIfSAPSyncTableExists()
	{
	    $dbModel = new DB_Model();
	    $params = array(
		DB_NAME,
		'sync',
		'tbl_sap_stundensatz'
	    );

	    $sql = "SELECT
			    1 AS exists
		    FROM
			    information_schema.tables
		    WHERE
			    table_catalog = ? AND
			    table_schema = ? AND
			    table_name = ?";

	    $res = $dbModel->execReadOnlyQuery($sql, $params);

	    if( hasData($res) )
	    {
		return true;
	    }
	    else
	    {
		return false;
	    }
	}


	private function _getSapStunden($user = null)
	{
		$dbModel = new DB_Model();
		$params = array();
		
		$qry = "SELECT ss.mitarbeiter_uid as uid,
						ss.sap_kalkulatorischer_stundensatz as stundensatz,
						ss.insertamum as beginn
				FROM sync.tbl_sap_stundensatz ss
				WHERE ss.sap_kalkulatorischer_stundensatz IS NOT NULL";
		
		if (!is_null($user))
		{
			$qry .= " AND ss.mitarbeiter_uid = ? ";
			$params[] = $user;
		}
		$qry .= " ORDER BY ss.mitarbeiter_uid";

		return $dbModel->execReadOnlyQuery($qry, $params);
	}
	
	private function _getMitarbeiterStunden($user = null)
	{
		$dbModel = new DB_Model();
		$params = array();

		$qry = "SELECT mitarbeiter.mitarbeiter_uid as uid,
						stundensatz
				FROM public.tbl_mitarbeiter mitarbeiter
				WHERE mitarbeiter.stundensatz != 0.00
					AND mitarbeiter.stundensatz IS NOT NULL";
		
		if (!is_null($user))
		{
			$qry .= " AND mitarbeiter.mitarbeiter_uid = ?";
			$params[] = $user;
		}
		
		$qry .= " ORDER BY mitarbeiter.mitarbeiter_uid";

		return $dbModel->execReadOnlyQuery($qry, $params);
	}

	private function _addStundensatz($mitarbeiter, $stundensatztyp, $gueltig_von)
	{
		return $this->_ci->StundensatzModel->insert(
			array(
				'uid' => $mitarbeiter->uid,
				'stundensatztyp' => $stundensatztyp,
				'stundensatz' => $mitarbeiter->stundensatz,
				'oe_kurzbz' => $mitarbeiter->unternehmen,
				'gueltig_von' => $gueltig_von,
				'insertamum' => date('Y-m-d H:i:s'),
				'insertvon' => 'MigrateHours'
			)
		);
	}

	private function _getUnternehmen(&$mitarbeiter)
	{
		$bvResult = $this->_ci->BisVerwendungModel->getLast($mitarbeiter->uid);

		$beginn = null;
		if (hasData($bvResult))
		{
			$beginn = getData($bvResult)[0]->beginn;
		}

		$unternehmenResult = $this->_findUnternehmen($mitarbeiter->uid, "'kstzuordnung', 'oezuordnung'", $beginn);

		if(!hasData($unternehmenResult)) //&& hasData($bvResult)
		{
			$unternehmenResult = $this->_findUnternehmen($mitarbeiter->uid, "'kstzuordnung', 'oezuordnung'");
		}

		$unternehmen = self::DEFAULT_OE;

		if (hasData($unternehmenResult))
			$unternehmen = getData($unternehmenResult)[0]->oe_kurzbz;

		$mitarbeiter->unternehmen = $unternehmen;
	}

	/**
	 * Detailsuche fuer die Ermittlung des Unternehmenszuordnung einer Person
	 */
	private function _findUnternehmen($uid, $fkt=null, $datum=null)
	{
		$dbModel = new DB_Model();

		$qry = "
		WITH RECURSIVE meine_oes(oe_kurzbz, oe_parent_kurzbz, organisationseinheittyp_kurzbz) as 
		(
			SELECT 
				oe_kurzbz, oe_parent_kurzbz, organisationseinheittyp_kurzbz
			FROM 
				public.tbl_organisationseinheit 
			WHERE 
				oe_kurzbz=(SELECT 
						oe_kurzbz 
					FROM 
						public.tbl_benutzerfunktion 
					WHERE 
						uid=".$dbModel->escape($uid);

		if(!is_null($datum))
			$qry.=" AND ".$dbModel->escape($datum)." BETWEEN datum_von AND COALESCE(datum_bis, '2999-12-31')";

		if(!is_null($fkt))
			$qry.=" AND funktion_kurzbz in ($fkt)";

		$qry.="
					ORDER BY funktion_kurzbz, datum_von LIMIT 1)
			UNION ALL
			SELECT 
				o.oe_kurzbz, o.oe_parent_kurzbz, o.organisationseinheittyp_kurzbz
			FROM 
				public.tbl_organisationseinheit o, meine_oes 
			WHERE 
				o.oe_kurzbz=meine_oes.oe_parent_kurzbz 
		)
		SELECT 
			oe_kurzbz
		FROM 
			meine_oes 
		WHERE 
			oe_parent_kurzbz is null
		LIMIT 1
		";

		return $dbModel->execReadOnlyQuery($qry);
	}
	
}
