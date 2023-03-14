<?php
/*
 * Job zur einmaligen Migration der Mitarbeiterverträge aus der tbl_bisverwendung in die neue 
 * Vertragsstruktur im HR Schema
 * 
 * Aufruf:
 * php index.ci.php system/MigrateContracts/index/oesi
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

class MigrateContract extends CLI_Controller
{

	private $matching_ba1_vertragsart;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		$this->load->model('codex/bisverwendung_model', 'BisVerwendungModel');
		$this->load->model('person/benutzerfunktion_model', 'BenutzerfunktionModel');
		
		$this->matching_ba1_vertragsart = array(
			'101'=>'DV zum Bund',
			'102'=>'DV anderen Gebietskörperschaft',
			'103'=>'EchterDV',
			'104'=>'Lehr- oder Ausbildungsverhältnis',
			'105'=>'ext. LehrendeR (freier DV)',
			'106'=>'Andere Bildungseinrichtung',
			'107'=>'Werkvertrag (Sonstiges)',
			'108'=>'Stud. Hilfskraft (Echter DV)',
			'109'=>'Überlassungsvertrag',
			'110'=>'Echter Freier DV',
			'111'=>'EchterDV', //All-In
		);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Everything has a beginning
	 */
	public function index($user = null)
	{
		$contracts = $this->_transformUser($user);

		/*
		Format:
		$contracts['dv'][]['vbs'][]
		*/
		//$this->outputJson($contracts);
		var_dump($contracts);
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
							$endedatum = $row_contract['von'];

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
			$contracts['dv'][$dv]['vbs'][$newVBSIndex]['titel'] = '';
			$contracts['dv'][$dv]['vbs'][$newVBSIndex]['anmerkung'] = '';
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
			$contracts['dv'][$dv]['vbs'][$newVBSIndex]['titel'] = '';
			$contracts['dv'][$dv]['vbs'][$newVBSIndex]['anmerkung'] = '';
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
					if ($row_vbs['vertragsbestandteiltyp_kurzbz'] == 'stunden')
					{
						if ($this->_isVBSAngrenzend($row_verwendung, $row_vbs) && $row_vbs['wochenstunden'] == $row_verwendung->vertragsstunden)
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
		if (isset($contracts['dv']) && is_array($contracts['dv']))
		{
			foreach($contracts['dv'] as $indexdv => $row_dv)
			{
				// Vertragsart ist die selbe
				if ($row_dv['vertragsart_kurzbz'] == $this->matching_ba1_vertragsart[$row_verwendung->ba1code])
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
		$contracts['dv'][$newDvIndex]['vertragsart_kurzbz'] = $this->matching_ba1_vertragsart[$row_verwendung->ba1code];

		return $newDvIndex;
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
}
