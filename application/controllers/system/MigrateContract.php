<?php
/*
 * Job zur einmaligen Migration der Mitarbeiterverträge aus der tbl_bisverwendung in die neue
 * Vertragsstruktur im HR Schema
 *
 * Aufruf pro Person
 * php index.ci.php system/MigrateContract/index/oesi
 *
 * Aufruf fuer Alle
 * php index.ci.php system/MigrateContract/index
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

class MigrateContract extends CLI_Controller
{

	private $matching_ba1_vertragsart;
	private $OE_DEFAULT = 'gst';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		$this->load->model('codex/bisverwendung_model', 'BisVerwendungModel');
		$this->load->model('person/benutzerfunktion_model', 'BenutzerfunktionModel');

		$this->matching_ba1_vertragsart = array(
			'101'=>'externerlehrender',
			'102'=>'DV anderen Gebietskörperschaft',
			'103'=>'echterdv',
			'104'=>'studentischehilfskr',
			'105'=>'externerlehrender',
			'106'=>'Andere Bildungseinrichtung',
			'107'=>'werkvertrag',
			'108'=>'studentischehilfskr',
			'109'=>'ueberlassungsvertrag',
			'110'=>'echterfreier',
			'111'=>'echterdv', //All-In
		);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Everything has a beginning
	 */
	public function index($user = null)
	{
		if (!is_null($user))
		{
			$contracts = $this->_transformUser($user);

			/*
			Format:
			$contracts['dv'][]['vbs'][]
			*/
			//$this->outputJson($contracts);
			var_dump($contracts);
			$this->_saveJSON($contracts);
		}
		else
		{
			$qry = "SELECT distinct mitarbeiter_uid FROM bis.tbl_bisverwendung";
			$db = new DB_Model();

			$resultUser = $db->execReadOnlyQuery($qry);
			if (hasData($resultUser))
			{
				$users = getData($resultUser);
				foreach($users as $user)
				{
					$contracts = $this->_transformUser($user->mitarbeiter_uid);
					$this->_saveJSON($contracts);
				}
			}

		}
	}

	private function _saveJSON($contracts)
	{
		$this->load->model('vertragsbestandteil/Dienstverhaeltnis_model','DienstverhaeltnisModel');
		$this->load->model('vertragsbestandteil/Vertragsbestandteil_model','VertragsbestandteilModel');
		$this->load->model('vertragsbestandteil/VertragsbestandteilStunden_model','VertragsbestandteilStundenModel');
		$this->load->model('vertragsbestandteil/VertragsbestandteilZeitaufzeichnung_model','VertragsbestandteilZeitaufzeichnungModel');
		$this->load->model('vertragsbestandteil/VertragsbestandteilFreitext_model','VertragsbestandteilFreitextModel');
		$this->load->model('vertragsbestandteil/VertragsbestandteilFunktion_model','VertragsbestandteilFunktionModel');
		$this->load->model('vertragsbestandteil/VertragsbestandteilKarenz_model','VertragsbestandteilKarenzModel');

		$failed = false;
		$this->db->trans_begin();

		foreach($contracts['dv'] as $row_dv)
		{
			// Dienstvertrag erstellen
			$resultDV = $this->DienstverhaeltnisModel->insert(
				array(
				'mitarbeiter_uid' => $row_dv['mitarbeiter_uid'],
				'vertragsart_kurzbz' => $row_dv['vertragsart_kurzbz'],
				'oe_kurzbz' => $row_dv['oe_kurzbz'],
				'von' => $row_dv['von'],
				'bis' => $row_dv['bis'],
				'insertamum' => date('Y-m-d H:i:s'),
				'insertvon' => 'MigrateContract'
				)
			);

			if (isSuccess($resultDV) && hasData($resultDV))
			{
				$dv_id = getData($resultDV);

				// Vertragsbetandteile erstellen
				foreach($row_dv['vbs'] as $row_vbs)
				{
					$resultVBS = $this->VertragsbestandteilModel->insert(
						array(
						'dienstverhaeltnis_id' => $dv_id,
						'vertragsbestandteiltyp_kurzbz' => $row_vbs['vertragsbestandteiltyp_kurzbz'],
						'von' => $row_vbs['von'],
						'bis' => $row_vbs['bis'],
						'insertamum' => date('Y-m-d H:i:s'),
						'insertvon' => 'MigrateContract'
						)
					);

					if (isSuccess($resultVBS) && hasData($resultVBS))
					{
						$vbs_id = getData($resultVBS);
						echo 'VBS:'.$vbs_id;

						switch($row_vbs['vertragsbestandteiltyp_kurzbz'])
						{
							case 'stunden':
								$resultVBS = $this->_insertVBSStunden($vbs_id, $row_vbs);
								break;
							case 'zeitaufzeichnung':
								$resultVBS = $this->_insertVBSZeitaufzeichnung($vbs_id, $row_vbs);
								break;
							case 'funktion':
								$resultVBS = $this->_insertVBSFunktion($vbs_id, $row_vbs);
								break;
							case 'freitext':
								$resultVBS = $this->_insertVBSFreitext($vbs_id, $row_vbs);
								break;
							case 'karenz':
								$resultVBS = $this->_insertVBSKarenz($vbs_id, $row_vbs);
								break;
						}

						if (isError($resultVBS))
						{
							echo "FAILED:".getError($resultVBS);
							$failed = true;
						}
					}
					else
					{
						$failed = true;
					}
				}
			}
			else
			{
				$failed = true;
			}
		}

		if(!$failed)
		{
			$this->db->trans_commit();
		}
		else
		{
			echo "ROLLBACK";
			$this->db->trans_rollback();
		}
	}

	private function _insertVBSKarenz($vbs_id, $row_vbs)
	{
		return $this->VertragsbestandteilKarenzModel->insert(
			array(
			'vertragsbestandteil_id' => $vbs_id,
			'karenztyp_kurzbz' => $row_vbs['karenztyp_kurzbz']
			)
		);
	}

	private function _insertVBSFreitext($vbs_id, $row_vbs)
	{
		return $this->VertragsbestandteilFreitextModel->insert(
			array(
			'vertragsbestandteil_id' => $vbs_id,
			'freitexttyp_kurzbz' => $row_vbs['freitexttyp_kurzbz'],
			'titel' => $row_vbs['titel'],
			'anmerkung' => $row_vbs['anmerkung']
			)
		);
	}

	private function _insertVBSFunktion($vbs_id, $row_vbs)
	{
		return $this->VertragsbestandteilFunktionModel->insert(
			array(
			'vertragsbestandteil_id' => $vbs_id,
			'benutzerfunktion_id' => $row_vbs['benutzerfunktion_id']
			)
		);
	}

	private function _insertVBSZeitaufzeichnung($vbs_id, $row_vbs)
	{
		return $this->VertragsbestandteilZeitaufzeichnungModel->insert(
			array(
			'vertragsbestandteil_id' => $vbs_id,
			'zeitaufzeichnung' => $row_vbs['zeitaufzeichnung'],
			'azgrelevant' => $row_vbs['azgrelevant'],
			'homeoffice' => $row_vbs['homeoffice']
			)
		);
	}

	private function _insertVBSStunden($vbs_id, $row_vbs)
	{
		return $this->VertragsbestandteilStundenModel->insert(
			array(
			'vertragsbestandteil_id' => $vbs_id,
			'wochenstunden' => $row_vbs['wochenstunden'],
			'teilzeittyp_kurzbz' => $row_vbs['teilzeittyp_kurzbz']
			)
		);
	}

	/**
	 * Ermittelt die neue Vertragsstruktur fuer einen User
	 */
	private function _transformUser($user)
	{
		$contracts = array();
		$this->BisVerwendungModel->addOrder('beginn');
		$result_verwendung = $this->BisVerwendungModel->loadWhere(array("mitarbeiter_uid" => $user));

		if (isError($result_verwendung))
			die("Failed to load Verwendung");

		if (hasData($result_verwendung))
		{
			$verwendung = getData($result_verwendung);

			foreach ($verwendung as $row_verwendung)
			{
				$dv = $this->_getOrCreateDV($contracts, $row_verwendung);

				// Ende des DV aktualisieren
				if ($contracts['dv'][$dv]['bis'] < $row_verwendung->ende || $row_verwendung->ende == '')
					$contracts['dv'][$dv]['bis'] = $row_verwendung->ende;

				// Stundenbestandteil pruefen
				$this->_addVertragsbestandteilStunden($contracts, $dv, $row_verwendung);

				// Befristung
				$this->_addVertragsbestandteilFreitextBefristung($contracts, $dv, $row_verwendung);

				// All-In
				$this->_addVertragsbestandteilFreitextAllIn($contracts, $dv, $row_verwendung);

				// Zeitaufzeichnung
				$this->_addVertragsbestandteilZeitaufzeichnung($contracts, $dv, $row_verwendung);

				// Karenz
				$this->_addVertragsbestandteilKarenz($contracts, $dv, $row_verwendung);

				// Inkludierte Lehre
				// Kuendigungsfrist
				// Urlaubsanspruch
			}

			// Funktion
			$this->_addVertragsbestandteilFunktion($contracts, $user);

		}

		return $contracts;
	}

	/**
	 * Fuegt Karenzierungseintraege zu bestehenden Dienstverhaeltnissen hinzu
	 */
	private function _addVertragsbestandteilKarenz(&$contracts, $dv, $row_verwendung)
	{
		if ($row_verwendung->beschausmasscode == 5)
		{
			$dtstart = new DateTime($row_verwendung->beginn);
			$dtende = new DateTime($row_verwendung->ende);
			$interval = $dtende->diff($dtstart);
			$dauer = $interval->format('%a');

			// TODO: klären ob das so machbar ist
			if ($dauer < 65)
				$karenztyp = 'papamonat';
			elseif ($dauer < 120)
				$karenztyp = 'bildungskarenz';
			else
				$karenztyp = 'elternkarenz';

			// VBS anlegen und Funktion zuweisen
			$newVBSIndex = $this->_getNewVBSIndex($contracts, $dv);
			$contracts['dv'][$dv]['vbs'][$newVBSIndex]['vertragsbestandteiltyp_kurzbz'] = 'karenz';
			$contracts['dv'][$dv]['vbs'][$newVBSIndex]['von'] = $row_verwendung->beginn;
			$contracts['dv'][$dv]['vbs'][$newVBSIndex]['bis'] = $row_verwendung->ende;
			$contracts['dv'][$dv]['vbs'][$newVBSIndex]['karenztyp_kurzbz'] = $karenztyp;
			$contracts['dv'][$dv]['vbs'][$newVBSIndex]['geplanter_geburtstermin'] = null;
			$contracts['dv'][$dv]['vbs'][$newVBSIndex]['tatsaechlicher_geburtstermin'] = null;
			$contracts['dv'][$dv]['vbs'][$newVBSIndex]['hint'] = 'Dauer:'.$dauer;
		}
	}

	/**
	 * Holt die Funktionen die Vertragsrelevant sind und verknüpft diese
	 */
	private function _addVertragsbestandteilFunktion(&$contracts, $user)
	{
		// Alle Funktionen holen die Vertragsrelevant sind
		$this->BenutzerfunktionModel->addOrder('datum_von');
		$this->BenutzerfunktionModel->addJoin('public.tbl_funktion','funktion_kurzbz');
		$resultFunktionen = $this->BenutzerfunktionModel->loadWhere(array('uid' => $user, 'vertragsrelevant' => true));

		if (isSuccess($resultFunktionen) && hasData($resultFunktionen))
		{
			$funktionen = getData($resultFunktionen);

			foreach ($funktionen as  $row_funktion)
			{
				$funktion_added = 0;
				$dv = '';

				// Passendes DV suchen
				foreach ($contracts['dv'] as $key_dv => $row_contract)
				{
					// Eine Funktion kann zu mehreren DV zugeordnet sein
					// es werden daher alle durchsucht ob es reinfaellt und ggf mehrfach zugeordnet
					if ((isset($row_funktion->datum_von) && $row_funktion->datum_von >= $row_contract['von'])
					 && ($row_contract['bis'] == '' || $row_contract['bis'] >= $row_funktion->datum_von)
					 && (
							(
							isset($row_funktion->datum_bis) && isset($row_contract['bis'])
							&& $row_funktion->datum_bis <= $row_contract['bis']
							)
							|| $row_funktion->datum_bis == ''
							|| (isset($row_funktion->datum_bis) && !isset($row_contract['bis']))
						)
					)
					{

						$dv = $key_dv;

						// Startdatum und Endedatum ermitteln wenn die Funktion ueber das DV hinausgeht
						// Wenn die Dauer laenger ist, wird beim Beginn/Ende des DV abgegrenzt
						$dtstart_fkt = new DateTime($row_funktion->datum_von);
						$dtstart_dv = new DateTime($row_contract['von']);
						if ($dtstart_fkt < $dtstart_dv)
							$startdatum = $row_contract['von'];
						else
							$startdatum = $row_funktion->datum_von;

						$dtende_fkt = new DateTime($row_funktion->datum_bis);
						$dtende_dv = new DateTime($row_contract['bis']);
						if ($dtende_fkt < $dtende_dv)
							$endedatum = $row_funktion->datum_bis;
						else
							$endedatum = $row_contract['bis'];

						// VBS anlegen und Funktion zuweisen
						$newVBSIndex = $this->_getNewVBSIndex($contracts, $dv);
						$contracts['dv'][$dv]['vbs'][$newVBSIndex]['vertragsbestandteiltyp_kurzbz'] = 'funktion';
						$contracts['dv'][$dv]['vbs'][$newVBSIndex]['von'] = $startdatum;
						$contracts['dv'][$dv]['vbs'][$newVBSIndex]['bis'] = $endedatum;
						$contracts['dv'][$dv]['vbs'][$newVBSIndex]['benutzerfunktion_id'] = $row_funktion->benutzerfunktion_id;
						$contracts['dv'][$dv]['vbs'][$newVBSIndex]['hint'] = $row_funktion->funktion_kurzbz.' '.$row_funktion->datum_von.' - '.$row_funktion->datum_bis;
						$funktion_added++;
					}
				}
				if ($funktion_added == 0)
				{
					echo "\nFunktion nicht zugeordnet: ".$row_funktion->funktion_kurzbz.' '.$row_funktion->datum_von.' - '.$row_funktion->datum_bis;
				}
			}
		}
	}

	/**
	 * Prueft ob schon ein Vertragsbestandteil fuer Zeitaufzeichnung vorhanden ist das in den Zeitraum passt
	 * bzw direkt anschließt. Wenn es direkt anschließend ist und die Art gleich sind wird die Laufzeit verlaengert
	 * Ansonsten wird ein neuer VBS angelegt
	 */
	private function _addVertragsbestandteilZeitaufzeichnung(&$contracts, $dv, $row_verwendung)
	{
		if( is_null($row_verwendung->zeitaufzeichnungspflichtig) || is_null($row_verwendung->azgrelevant) )
		{
		    return;
		}

		if (isset($contracts['dv'][$dv]['vbs']))
		{
			foreach ($contracts['dv'][$dv]['vbs'] as $index_vbs=>$row_vbs)
			{
				if ($row_vbs['vertragsbestandteiltyp_kurzbz'] == 'zeitaufzeichnung')
				{
					if ($this->_isVBSAngrenzend($row_verwendung, $row_vbs)
						&& $row_vbs['zeitaufzeichnung'] == $row_verwendung->zeitaufzeichnungspflichtig
						&& $row_vbs['azgrelevant'] == $row_verwendung->azgrelevant
						&& $row_vbs['homeoffice'] == $row_verwendung->homeoffice
					)
					{
						// Zeitaufzeichnungsarten bleiben gleich - Ende des VBS verlaengern
						$contracts['dv'][$dv]['vbs'][$index_vbs]['bis'] = $row_verwendung->ende;
						return true;
					}
				}
			}
		}

		// kein passender VBS gefunden - neuen anlegen
		$newVBSIndex = $this->_getNewVBSIndex($contracts, $dv);
		$contracts['dv'][$dv]['vbs'][$newVBSIndex]['vertragsbestandteiltyp_kurzbz'] = 'zeitaufzeichnung';
		$contracts['dv'][$dv]['vbs'][$newVBSIndex]['von'] = $row_verwendung->beginn;
		$contracts['dv'][$dv]['vbs'][$newVBSIndex]['bis'] = $row_verwendung->ende;
		$contracts['dv'][$dv]['vbs'][$newVBSIndex]['zeitaufzeichnung'] = $row_verwendung->zeitaufzeichnungspflichtig;
		$contracts['dv'][$dv]['vbs'][$newVBSIndex]['azgrelevant'] = $row_verwendung->azgrelevant;
		$contracts['dv'][$dv]['vbs'][$newVBSIndex]['homeoffice'] = $row_verwendung->homeoffice;

		return true;
	}

	/**
	 * Fueg einen Freitextbestandteil fuer All-In zum DV hinzu
	 */
	private function _addVertragsbestandteilFreitextAllIn(&$contracts, $dv, $row_verwendung)
	{
		if ($row_verwendung->ba1code == 111) // All-In
		{
			$newVBSIndex = $this->_getNewVBSIndex($contracts, $dv);
			$contracts['dv'][$dv]['vbs'][$newVBSIndex]['vertragsbestandteiltyp_kurzbz'] = 'freitext';
			$contracts['dv'][$dv]['vbs'][$newVBSIndex]['von'] = $row_verwendung->beginn;
			$contracts['dv'][$dv]['vbs'][$newVBSIndex]['bis'] = $row_verwendung->ende;
			$contracts['dv'][$dv]['vbs'][$newVBSIndex]['freitexttyp_kurzbz'] = 'allin';
			$contracts['dv'][$dv]['vbs'][$newVBSIndex]['titel'] = 'allin';
			$contracts['dv'][$dv]['vbs'][$newVBSIndex]['anmerkung'] = 'allin';
		}
		return true;
	}

	/**
	 * Fueg einen Freitextbestandteil fuer die Berfristung zum DV hinzu
	 */
	private function _addVertragsbestandteilFreitextBefristung(&$contracts, $dv, $row_verwendung)
	{
		if ($row_verwendung->ba2code == 1) // Befristung
		{
			$newVBSIndex = $this->_getNewVBSIndex($contracts, $dv);
			$contracts['dv'][$dv]['vbs'][$newVBSIndex]['vertragsbestandteiltyp_kurzbz'] = 'freitext';
			$contracts['dv'][$dv]['vbs'][$newVBSIndex]['von'] = $row_verwendung->beginn;
			$contracts['dv'][$dv]['vbs'][$newVBSIndex]['bis'] = $row_verwendung->ende;
			$contracts['dv'][$dv]['vbs'][$newVBSIndex]['freitexttyp_kurzbz'] = 'befristung';
			$contracts['dv'][$dv]['vbs'][$newVBSIndex]['titel'] = 'befristung';
			$contracts['dv'][$dv]['vbs'][$newVBSIndex]['anmerkung'] = 'befristung';
		}
		return true;
	}

	/**
	 * Prueft ob schon ein Vertragsbestandteil mit diesem Stundenausmass vorhanden ist das in den Zeitraum passt
	 * bzw direkt anschließt. Wenn es direkt anschließend ist und die Stunden gleich sind wird die Laufzeit verlaengert
	 * Ansonsten wird ein neuer VBS angelegt
	 */
	private function _addVertragsbestandteilStunden(&$contracts, $dv, $row_verwendung)
	{
		// Nur anlegen wenn im aktuellen Eintrag auch Stunden eingetragen sind
		if ($row_verwendung->vertragsstunden != '')
		{
			if (isset($contracts['dv'][$dv]['vbs']))
			{
				foreach ($contracts['dv'][$dv]['vbs'] as $index_vbs=>$row_vbs)
				{
					if ($row_vbs['vertragsbestandteiltyp_kurzbz'] == 'stunden' || ($row_vbs['vertragsbestandteiltyp_kurzbz'] == 'karenz' && $row_verwendung->vertragsstunden === '0.00'))
					{
						if ($this->_isVBSAngrenzend($row_verwendung, $row_vbs) && ((isset($row_vbs['wochenstunden']) && $row_vbs['wochenstunden'] == $row_verwendung->vertragsstunden) || $row_verwendung->vertragsstunden === '0.00'))
						{
							// stunden bleiben gleich - Ende des VBS verlaengern
							$contracts['dv'][$dv]['vbs'][$index_vbs]['bis'] = $row_verwendung->ende;
							return true;
						}
					}
				}
			}

			// kein passender VBS gefunden - neuen anlegen
			$newVBSIndex = $this->_getNewVBSIndex($contracts, $dv);
			$contracts['dv'][$dv]['vbs'][$newVBSIndex]['vertragsbestandteiltyp_kurzbz'] = 'stunden';
			$contracts['dv'][$dv]['vbs'][$newVBSIndex]['von'] = $row_verwendung->beginn;
			$contracts['dv'][$dv]['vbs'][$newVBSIndex]['bis'] = $row_verwendung->ende;
			$contracts['dv'][$dv]['vbs'][$newVBSIndex]['wochenstunden'] = $row_verwendung->vertragsstunden;
			$contracts['dv'][$dv]['vbs'][$newVBSIndex]['teilzeittyp_kurzbz'] = null;
		}
		return true;
	}

	/**
	 * Prueft ob die Verwendung direkt an den Vertragsbestandteil angrenzt
	 * @return boolean true wenn ja, sonst false
	 */
	private function _isVBSAngrenzend($verwendung, $vbs)
	{
		// Beginn Minus 1 Tag
		$dtstart = new DateTime($verwendung->beginn);
		$dtstartMinus1 = $dtstart->sub(new DateInterval('P1D'))->format('Y-m-d');

		if ($vbs['bis'] == ''
			|| $vbs['bis'] == $dtstartMinus1)
		{
			return true;
		}

		return false;
	}

	/**
	 * Create a new DV or Returns the Index of an existing
	 */
	private function _getOrCreateDV(&$contracts, $row_verwendung)
	{
		$unternehmen = $this->OE_DEFAULT;
		$resultUnternehmen = $this->_getUnternehmen($row_verwendung);
		if(hasData($resultUnternehmen))
		{
			$unternehmen = getData($resultUnternehmen)[0]->oe_kurzbz;
		}
		else
		{
			// Fallback Unternehmen wird verwendet falls keine Zuordnung ermittelt werden kann
		}

		if (isset($contracts['dv']) && is_array($contracts['dv']))
		{
			foreach($contracts['dv'] as $indexdv => $row_dv)
			{
				// Vertragsart ist die selbe und selbes Unternehmen
				if ($row_dv['vertragsart_kurzbz'] == $this->matching_ba1_vertragsart[$row_verwendung->ba1code]
					&& $row_dv['oe_kurzbz'] == $unternehmen
				)
				{

					$dtstart = new DateTime($row_verwendung->beginn);

					// Zeitraum passt zur Verwendung
					if ($row_dv['von'] <= $row_verwendung->beginn // Beginn Datum Pruefen
						&& ( // Ende innerhalb des DV
							(isset($row_dv['bis']) && $row_verwendung->ende != '' && ($row_dv['bis'] == '' || $row_dv['bis'] >= $row_verwendung->ende)
							)
							|| // direkt angrenzend an dieses DV
							(isset($row_dv['bis'])
							&& ($row_dv['bis'] == ''
								|| $row_dv['bis'] == $dtstart->sub(new DateInterval('P1D'))->format('Y-m-d')
								)
							)
						)
					)
					{
						return $indexdv;
					}
				}
			}
		}

		$newDvIndex = $this->_getNewDVIndex($contracts);
		$contracts['dv'][$newDvIndex]['mitarbeiter_uid'] = $row_verwendung->mitarbeiter_uid;
		$contracts['dv'][$newDvIndex]['von'] = $row_verwendung->beginn;
		$contracts['dv'][$newDvIndex]['bis'] = $row_verwendung->ende;
		$contracts['dv'][$newDvIndex]['oe_kurzbz'] = $unternehmen;
		$contracts['dv'][$newDvIndex]['vertragsart_kurzbz'] = $this->matching_ba1_vertragsart[$row_verwendung->ba1code];

		return $newDvIndex;
	}

	/**
	 * Ermittelt in welchem Unternehmen die Person zum betreffenden Zeitpunkt ist.
	 */
	private function _getUnternehmen($row_verwendung)
	{

		$resultUnternehmen = $this->_findUnternehmen($row_verwendung->mitarbeiter_uid, "'kstzuordnung', 'oezuordnung'", $row_verwendung->beginn);

		// Wenn zeitlich keine passende Unternehmenszuordnung vorhanden ist, dann suchen ob generell eine Zuordnung ermittelt werden kann
		if(!hasData($resultUnternehmen))
		{
			$resultUnternehmen = $this->_findUnternehmen($row_verwendung->mitarbeiter_uid, "'kstzuordnung', 'oezuordnung'");

			// Falls nicht wird nach erweiterten Funktionen gesucht um die Zuordnung zu ermitteln.
			if(!hasData($resultUnternehmen))
			{
				$resultUnternehmen = $this->_findUnternehmen($row_verwendung->mitarbeiter_uid, "'kstzuordnung', 'oezuordnung','hilfskraft','Leitung','fbk','fbl'");
			}
		}

		return $resultUnternehmen;
	}

	/**
	 * Detailsuche fuer die Ermittlung des Unternehmenszuordnung einer Person
	 */
	private function _findUnternehmen($uid, $fkt=null, $datum=null)
	{
		$db = new DB_Model();

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
						uid=".$db->escape($uid);

		if(!is_null($datum))
			$qry.=" AND ".$db->escape($datum)." BETWEEN datum_von AND COALESCE(datum_bis, '2999-12-31')";

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

		$resultUnternehmen = $db->execReadOnlyQuery($qry);
		return $resultUnternehmen;
	}

	/**
	 * Ermittelt den nächsten (freien) Index für den Vertragsbetandteil
	 */
	private function _getNewVBSIndex($contracts, $dv)
	{
		if (isset($contracts['dv'][$dv]['vbs']))
			return max(array_keys($contracts['dv'][$dv]['vbs'])) + 1;
		else
			return 0;
	}

	/**
	 * Ermittelt den nächsten (freien) Index für das Dienstverhältnis
	 */
	private function _getNewDVIndex($contracts)
	{
		if (isset($contracts['dv']) && is_array($contracts['dv']))
			return max(array_keys($contracts['dv'])) + 1;
		else
			return 0;
	}

	/**
	 * Habilitation wird aus der Tabelle bis.tbl_bisverwendung in die Tabelle public.tbl_mitarbeiter uebernommen
	 * Sofern die Person einmal in den Verwendungen eine habiliation eingetragen hat wird diese in den MA-Datensatz übernommen
	 * Da es in der regel öfter vorkommt dass das hakerl vergessen wurde beim Vertragswechsel als dass die person die habiliation verliert.
	 */
	public function migrateHabilitation()
	{
		$this->load->model('ressource/Mitarbeiter_model','MitarbeiterModel');
		$db = new DB_Model();

		$qry = "
		SELECT
			distinct mitarbeiter_uid
		FROM
			bis.tbl_bisverwendung
		WHERE
			habilitation=true";

		$resultHabilitation = $db->execReadOnlyQuery($qry);

		if (isSuccess($resultHabilitation) && hasData($resultHabilitation))
		{
			$habilitationen = getData($resultHabilitation);

			foreach ($habilitationen as  $row_habilitationen)
			{
 				$this->MitarbeiterModel->update($row_habilitationen->mitarbeiter_uid, array('habilitation'=>true));
			}
		}
	}
}
