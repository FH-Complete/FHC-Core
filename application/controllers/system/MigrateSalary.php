<?php
/*
 * Job zur einmaligen Import der Gehälter
 *
 * Aufruf (Encode / im Filenmae mit %2F):
 * php index.ci.php system/MigrateSalary/import filename 
 *
 */
/*
AUFBAU CSV:
SVNR;Pers-Nr;Name;Dienstverhältnis;LA-Nr;Bezeichnung;2022-09-01;2022-10-01;2022-11-01;2022-12-01;2023-01-01;2023-02-01;2023-03-01
XXXX XXXXXX;00;Name;5;1000;Gehalt;1.111,10;1.211,10;1.311,10;1.411,10;1.511,10;1.611,10;1.711,10
*/

if (! defined('BASEPATH')) exit('No direct script access allowed');

class MigrateSalary extends CLI_Controller
{
	private $OE_DEFAULT = 'gst';
	private $GEHALT_BEGINN_SPALTE = 6; // Beginnend mit 0 => G
	private $INDEX_LOHNART = 4;
	private $INDEX_BEZEICHNUNG = 5;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		$this->load->model('vertragsbestandteil/Gehaltsbestandteil_model', 'GehaltsbestandteilModel');
		$this->load->model('vertragsbestandteil/Dienstverhaeltnis_model','DienstverhaeltnisModel');
		$this->load->model('vertragsbestandteil/Vertragsbestandteil_model','VertragsbestandteilModel');
		$this->load->model('vertragsbestandteil/VertragsbestandteilStunden_model','VertragsbestandteilStundenModel');
		$this->load->model('vertragsbestandteil/VertragsbestandteilFreitext_model','VertragsbestandteilFreitextModel');
		$this->load->model('vertragsbestandteil/VertragsbestandteilFunktion_model','VertragsbestandteilFunktionModel');
		
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Everything has a beginning
	 */
	public function import($file)
	{
	
		// CSV Laden
		$file = urldecode($file);
		if($handle = fopen($file, "r"))
		{
			$csvrow = -1;
			$lastuser = '';
			$monate = array();
			$gehaltsarr = array();
			$gehaltsindex = 0;

			while (($data = fgetcsv($handle, null, ';')) !== FALSE)
			{
				$csvrow++;
				// Kopfzeile ueberspringen
				if($csvrow == 0)
				{
					for($i = $this->GEHALT_BEGINN_SPALTE; $i < count($data); $i++)
					{
						$monate[] = $data[$i];
					}
					continue;
				}

				// User zur SVNR ermitteln
				$svnr = str_replace(' ', '',$data[0]);
				$resultuser = $this->_getUser($svnr);

				if(!hasData($resultuser))
				{
					echo getError($resultuser);
					break;
				}

				$user = getData($resultuser)[0]->mitarbeiter_uid;
				echo "\nUser:".$user;

				if($user != $lastuser && $lastuser != '')
				{
					$this->_saveGehalt($lastuser, $gehaltsarr);
					$gehaltsarr = array();
					$gehaltsindex = 0;
					$lastuser = $user;
				}
				else
				{
					$lastuser = $user;
				}

				// Gehalt Clustern

				$monat = 0;
				for ($i = $this->GEHALT_BEGINN_SPALTE; $i < count($data); $i++)
				{
					if (count($gehaltsarr) == 0 && $data[$i] != '')
					{
						$gehaltsarr[$gehaltsindex]['betrag'] = $data[$i];
						$gehaltsarr[$gehaltsindex]['lohnart'] = $data[$this->INDEX_LOHNART];
						$gehaltsarr[$gehaltsindex]['bezeichnung'] = $data[$this->INDEX_BEZEICHNUNG];
						$gehaltsarr[$gehaltsindex]['beginn'] = $monate[$monat];
					}
					else
					{
						if ($data[$i] != '' 
							&& isset($gehaltsarr[$gehaltsindex]) && isset($gehaltsarr[$gehaltsindex]['betrag']) 
							&& $gehaltsarr[$gehaltsindex]['betrag'] == $data[$i])
						{
							// Gehalt bleibt gleich
						}
						else
						{
							if ($data[$i] != '')
							{
								// Gehalt hat sich geändert
								if ($monat != 0 && isset($gehaltsarr[$gehaltsindex]))
									$gehaltsarr[$gehaltsindex]['ende'] = $monate[$monat-1];

								$gehaltsindex++;

								$gehaltsarr[$gehaltsindex]['betrag'] = $data[$i];
								$gehaltsarr[$gehaltsindex]['lohnart'] = $data[$this->INDEX_LOHNART];
								$gehaltsarr[$gehaltsindex]['bezeichnung'] = $data[$this->INDEX_BEZEICHNUNG];
								$gehaltsarr[$gehaltsindex]['beginn'] = $monate[$monat];
							}
							elseif(isset($gehaltsarr[$gehaltsindex]))
							{
								// Gehalt wurde beendet
								if($monat!=0)
									$gehaltsarr[$gehaltsindex]['ende'] = $monate[$monat-1];
								$gehaltsindex++;
							}
						}
					}
					
					$monat++;
				}

				// Zeile zu Ende - Ende Datum setzen wenn nicht für alle Monate ein Eintrag vorhanden ist
				if($monat < count($monate) && isset($gehaltsarr[$gehaltsindex]))
					$gehaltsarr[$gehaltsindex]['ende'] == $monate[$monat-1];
				
			}
			$this->_saveGehalt($lastuser, $gehaltsarr);
		}
	}

	/**
	 * Ermittelt das passende Dienstverhaeltnis uns speichert den 
	 * Gehaltsbestandteil
	 */
	private function _saveGehalt($uid, $gehaltsarr)
	{		
		$failed = false;
		$this->db->trans_begin();

		foreach($gehaltsarr as $row_gehalt)
		{
			$auszahlungen = 14;
			$dvid = '';
			$vbsid = '';
			$typ = '';
			$allin = false;

			//DV und VBS Ermitteln
			$dv = $this->DienstverhaeltnisModel->getDVByPersonUID($uid, $this->OE_DEFAULT, $row_gehalt['beginn']);

			if (!hasData($dv))
			{
				$date = new DateTime($row_gehalt['beginn']);
				$date->modify('last day of this month');
				$last_day_this_month = $date->format('Y-m-d');

				// Wenn mit Monatsersten kein DV gefunden wird, wird stattdessen mit Monatsletzten gesucht um DVs zu finden 
				// für Personen die erst später im Monat in ihr DV einsteigen
				$dv = $this->DienstverhaeltnisModel->getDVByPersonUID($uid, $this->OE_DEFAULT, $last_day_this_month);
				
				if (!hasData($dv))
				{
					echo "\nKein passendes DV gefunden für User ".$uid." und Datum ".$row_gehalt['beginn']." -> ROLLBACK\n";
					$failed = true;
					break;
				}
				else
				{
					// Gehaltsstart wird auf den Start des DV korrigiert wenn nicht der Monatserste
					$row_gehalt['beginn'] = getData($dv)[0]->von;
				}
			}

			$resultdata = getData($dv);
			if (count($resultdata) !== 1)
			{
				echo "Kein oder Mehrere DVs gefunden -> ROLLBACK";
				$failed = true;
				break;
			}

			$dvid = $resultdata[0]->dienstverhaeltnis_id;
			
			$allin = $this->_isAllIn($dvid, $row_gehalt['beginn']);

			$db = new DB_Model();

			$resultVBS = $this->_getVBS($dvid, $row_gehalt['beginn']);
			
			if (hasData($resultVBS))
			{
				$vbsid = getData($resultVBS)[0]->vertragsbestandteil_id;
			}
			else
			{
				echo "Vertragsbestandteil wurde nicht gefunden -> ROLLBACK";
				$failed = true;
				break;
			}

			if ($row_gehalt['lohnart'] == 1000)
			{
				if($allin)
					$typ = 'grundgehalt';
				else
					$typ = 'basisgehalt';
			}
			elseif ($row_gehalt['lohnart']==1041 // 14x
				|| $row_gehalt['lohnart']==1042  // 12x
				|| $row_gehalt['lohnart']==3410)  // USTDPausch
			{
				$typ = 'zusatzvereinbarung';

				// Freitextbestandteil anlegen fuer die Zulage
				// Gaehalt wird der Zuglage zugeordnet

				$data = array(
					'dienstverhaeltnis_id' => $dvid,
					'von' => $row_gehalt['beginn'],
					'vertragsbestandteiltyp_kurzbz' => 'freitext',
					'insertamum' => date('Y-m-d H:i:s'),
					'insertvon' => 'MigrateSalary'
				);
				if (isset($row_gehalt['ende']) && $row_gehalt['ende']!='')
					$data['bis'] = $row_gehalt['ende'];
				
				$resultVBS = $this->VertragsbestandteilModel->Insert($data);
				if(!isSuccess($resultVBS))
				{
					echo "VBS kann nicht erstellt werden -> ROLLBACK";
					$failed = true;
					break;
				}
				$vbsid = getData($resultVBS);

				$data = array(
					'vertragsbestandteil_id' => $vbsid,
					'freitexttyp_kurzbz' => 'zusatzvereinbarung',
					'titel' => $row_gehalt['bezeichnung'],
					'anmerkung' => $row_gehalt['bezeichnung'],
				);
				$resultVBSFreitext = $this->VertragsbestandteilFreitextModel->Insert($data);
				if(!isSuccess($resultVBSFreitext))
				{
					echo "VBS Freitext Zusatz kann nicht erstellt werden -> ROLLBACK";
					$failed = true;
					break;
				}
			}
			elseif ($row_gehalt['lohnart']==9999) // All-In Custom Lohnart nicht per Default vorhanden
			{
				$typ = 'zulage';

				// Freitextbestandteil anlegen fuer die Zulage
				// Gaehalt wird der Zuglage zugeordnet

				$data = array(
					'dienstverhaeltnis_id' => $dvid,
					'von' => $row_gehalt['beginn'],
					'vertragsbestandteiltyp_kurzbz' => 'freitext',
					'insertamum' => date('Y-m-d H:i:s'),
					'insertvon' => 'MigrateSalary'
				);
				if (isset($row_gehalt['ende']) && $row_gehalt['ende']!='')
					$data['bis'] = $row_gehalt['ende'];
				
				$resultVBS = $this->VertragsbestandteilModel->Insert($data);
				if(!isSuccess($resultVBS))
				{
					echo "VBS AllIn kann nicht erstellt werden -> ROLLBACK";
					$failed = true;
					break;
				}
				$vbsid = getData($resultVBS);

				$data = array(
					'vertragsbestandteil_id' => $vbsid,
					'freitexttyp_kurzbz' => 'allin',
					'titel' => $row_gehalt['bezeichnung'],
					'anmerkung' => $row_gehalt['bezeichnung'],
				);
				$resultVBSFreitext = $this->VertragsbestandteilFreitextModel->Insert($data);
				if(!isSuccess($resultVBSFreitext))
				{
					echo "VBS Freitext AllIn Zusatz kann nicht erstellt werden -> ROLLBACK";
					$failed = true;
					break;
				}
			}
			elseif($row_gehalt['lohnart']==5500) // ATZ
			{
				$typ = 'lohnausgleichatz';
			}
			else
			{
				$typ = 'unbekannt - '.$row_gehalt['lohnart'];
				echo "\nGehaltstyp unbekannt Lohnart: ".$row_gehalt['lohnart']." -> ROLLBACK";
				$failed = true;
				break;
			}

			// Zulage 12x und Zulage 14x aus der Bezeichnung ermitteln
			if(strstr($row_gehalt['bezeichnung'], '12x'))
			{
				$auszahlungen = 12;
			}

			// Format ist 7.777,77 und wird umformattiert in 7777.77
			$betrag = str_replace('.','', $row_gehalt['betrag']);
			$betrag = str_replace(',','.',$betrag);

			$data = array(
				'dienstverhaeltnis_id' => $dvid,
				'vertragsbestandteil_id' => $vbsid,
				'gehaltstyp_kurzbz' => $typ,
				'von' => $row_gehalt['beginn'],
				'grundbetrag' => $betrag,
				'betrag_valorisiert' => $betrag,
				'anmerkung' => $row_gehalt['bezeichnung'],
				'valorisierung' => true,
				'auszahlungen' => $auszahlungen,
				'insertamum' => date('Y-m-d H:i:s'),
				'insertvon' => 'MigrateSalary',
				'updateamum' => date('Y-m-d H:i:s'),
				'updatevon' => 'MigrateSalary'
			);

			if (isset($row_gehalt['ende']) && $row_gehalt['ende'] != '')
			{
				// Im Ende steht noch der Monatserste des letzten Monats
				// Das muss geaendert werden auf den Monatsletzten oder das Ende des DVs
				$date = new DateTime($row_gehalt['ende']);
				$date->modify('last day of this month');
				$last_day_this_month = $date->format('Y-m-d');

				// TODO: wenn das Dienstverhaeltnis in diesem Monat endet und nicht der Monatsletzte ist,
				// dann muss hier das Ende Datum des DV stehen bzw das Ende
				// oder das Ende des VBS falls die Person in der Monatsmitte Stunden wechselt
				$data['bis'] = $last_day_this_month;
			}

			$ret = $this->GehaltsbestandteilModel->insert($data,
				$this->GehaltsbestandteilModel->getEncryptedColumns()
			);
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

	/**
	 * Prueft ob ein AllIn Vertrag vorhanden ist
	 */
	private function _isAllIn($dvid, $datum)
	{
		$db = new DB_Model();

		$qry = "
			SELECT 
				* 
			FROM 
				hr.tbl_vertragsbestandteil 
				JOIN hr.tbl_vertragsbestandteil_freitext USING(vertragsbestandteil_id)
			WHERE 
				dienstverhaeltnis_id=".$db->escape($dvid)." 
				AND vertragsbestandteiltyp_kurzbz='freitext' 
				AND ".$db->escape($datum)." BETWEEN von AND COALESCE(bis, '2999-12-31')
				AND freitexttyp_kurzbz='allin'";
		
		$resultAllIn = $db->execReadOnlyQuery($qry);

		if (hasData($resultAllIn))
			return true;
		else
			return false;
	}

	private function _getVBS($dvid, $datum)
	{
		$db = new DB_Model();

		$qry = "
			SELECT 
				* 
			FROM 
				hr.tbl_vertragsbestandteil 
			WHERE 
				dienstverhaeltnis_id=".$db->escape($dvid)." 
				AND vertragsbestandteiltyp_kurzbz='stunden' 
				AND ".$db->escape($datum)." BETWEEN von AND COALESCE(bis, '2999-12-31')";
		
		$resultVBS = $db->execReadOnlyQuery($qry);

		return $resultVBS;
	}

	/**
	 * Ermittelt den User zu einer SVNR
	 */
	private function _getUser($svnr)
	{
		$db = new DB_Model();
		
		$qry = "
		SELECT 
			mitarbeiter_uid 
		FROM 
			public.tbl_person 
			JOIN public.tbl_benutzer using(person_id)
			JOIN public.tbl_mitarbeiter ON(uid=mitarbeiter_uid)
		WHERE
			tbl_person.svnr = ". $db->escape($svnr)."
			AND EXISTS(
				SELECT 
					1 
				FROM 
					hr.tbl_dienstverhaeltnis 
				WHERE 
					mitarbeiter_uid=tbl_mitarbeiter.mitarbeiter_uid
					AND oe_kurzbz=". $db->escape($this->OE_DEFAULT)."
			)
		ORDER BY tbl_benutzer.aktiv DESC
		LIMIT 1;
		";

		$result = $db->execReadOnlyQuery($qry);

		if (hasdata($result))
		{
			return $result;
		}
		else
			return error('Kein Benutzer mit DV und SVNR:'.$svnr.' gefunden');
	}
}
