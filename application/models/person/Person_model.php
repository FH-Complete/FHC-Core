<?php

class Person_model extends DB_Model
{
	// 
	protected $_loadQuery = "SELECT person_id,
								    sprache,
								    anrede,
								    titelpost,
								    titelpre,
								    nachname,
								    vorname,
								    vornamen,
								    gebdatum,
								    gebort,
								    gebzeit,
								    foto,
								    anmerkung,
								    homepage,
								    svnr,
								    ersatzkennzeichen,
								    familienstand,
								    anzahlkinder,
								    aktiv,
								    insertamum,
								    insertvon,
								    updateamum,
								    updatevon,
								    ext_id,
								    geschlecht,
								    staatsbuergerschaft,
								    geburtsnation,
								    kurzbeschreibung,
								    zugangscode,
								    foto_sperre,
								    matr_nr
							   FROM public.tbl_person
							  WHERE person_id = ?";
	
	/**
	 * 
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 
	 */
	public function getPerson($personID = NULL, $code = NULL, $email = NULL)
	{
		$result = NULL;
		
		// Checks if the operation is permitted by the API caller
		// All the code should be put inside this if statement
		if(isAllowed($this->_addonID, 'person'))
		{
			if((isset($code)) && (isset($email)))
			{
				$result = $this->_getPersonByCodeAndEmail($code, $email);
			}
			elseif(isset($code))
			{
				$result = $this->_getPersonByCode($code);
			}
			else
			{
				$result = $this->_getPersonByID($personID);
			}
		}
		
		return $result;
	}
	
	/**
	 * @param int $personID Person ID
	 * @return object
	 */
	private function _getPersonByID($personID = NULL)
	{
		$result = NULL;
		
		if(isset($personID))
		{
			$result = $this->db->query($this->_loadQuery, array($personID));
		}
		
		return $result;
	}

	/**
	 * 
	 */
	private function _getPersonByCodeAndEmail($code = NULL, $email = NULL)
	{
		$result = NULL;
		$query = "SELECT *
					FROM public.tbl_person p JOIN public.tbl_kontakt k USING (person_id)
				   WHERE p.zugangscode = ?
					 AND k.kontakt = ?";
		
		if((isset($code)) && (isset($email)))
		{
			$result = $this->db->query($query, array($code, $email));
		}

		return $result;
	}

	/**
	 * 
	 */
	private function _getPersonByCode($code = NULL)
	{
		$result = NULL;
		$query = "SELECT *
					FROM public.tbl_person p
				   WHERE p.zugangscode = ?";
		
		if(isset($code))
		{
			$result = $this->db->query($query, array($code));
		}

		return $result;
	}

	/**
	 * 
	 */
	public function savePerson($person = NULL)
	{
		$result = FALSE;
		
		// Checks if the operation is permitted by the API caller
		// All the code should be put inside this if statement
		if(isAllowed($this->_addonID, 'person'))
		{
			if($this->_validate($person))
			{
				if(isset($person['person_id']))
				{
					$result = $this->_updatePerson($person);
				}
				else
				{
					$result = $this->_insertPerson($person);
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * 
	 */
	private function _insertPerson($person)
	{
		$this->db->trans_begin(); // Start DB transaction
		
		$insertQuery = "INSERT INTO public.tbl_person (
										sprache,
										anrede, 
										titelpost, 
										titelpre, 
										nachname, 
										vorname, 
										vornamen,
										gebdatum, 
										gebort, 
										gebzeit, 
										foto, 
										anmerkung, 
										homepage, 
										svnr, 
										ersatzkennzeichen,
										familienstand, 
										anzahlkinder, 
										aktiv, 
										insertamum, 
										insertvon, 
										updateamum, 
										updatevon,
										geschlecht, 
										geburtsnation, 
										staatsbuergerschaft, 
										kurzbeschreibung, 
										zugangscode, 
										foto_sperre, 
										matr_nr
						) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
								  ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
								  ?, ?, ?, ?, ?, ?, ?, ?, ?)";
		
		$sqlParametersArray = array($person['sprache'], 
									$person['anrede'], 
									$person['titelpost'], 
									$person['titelpre'], 
									$person['nachname'], 
									$person['vorname'],
									$person['vornamen'], 
									$person['gebdatum'], 
									$person['gebort'], 
									$person['gebzeit'], 
									$person['foto'], 
									$person['anmerkung'], 
									$person['homepage'], 
									$person['svnr'], 
									$person['ersatzkennzeichen'], 
									$person['familienstand'], 
									$person['anzahlkinder'], 
									$person['aktiv'], 
									"now()",
									$person['insertvon'], 
									"now()",
									$person['updatevon'], 
									$person['geschlecht'], 
									$person['geburtsnation'], 
									$person['staatsbuergerschaft'], 
									$person['kurzbeschreibung'], 
									$person['zugangscode'], 
									$person['foto_sperre'], 
									$person['matr_nr']);
		
		$result = $this->db->query($insertQuery, $sqlParametersArray);
		
		// Check DB transaction result
		if($this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();
			$result = FALSE;
		}
		else
		{
			$this->db->trans_commit();
			$result = TRUE;
		}
		
		return $result;
	}
	
	/**
	 * 
	 */
	private function _updatePerson($person)
	{
		$this->db->trans_begin(); // Start DB transaction
		
		$updateQuery = "UPDATE public.tbl_person SET
								sprache = ?, 
								anrede = ?, 
								titelpost = ?, 
								titelpre = ?, 
								nachname = ?, 
								vorname = ?, 
								vornamen = ?, 
								gebdatum = ?, 
								gebort = ?, 
								gebzeit = ?, 
								foto = ?, 
								anmerkung = ?, 
								homepage = ?, 
								svnr = ?, 
								ersatzkennzeichen = ?, 
								familienstand = ?, 
								anzahlkinder = ?, 
								aktiv = ?, 
								updateamum = ?,
								updatevon = ?, 
								geschlecht = ?, 
								geburtsnation = ?, 
								staatsbuergerschaft = ?, 
								kurzbeschreibung = ?, 
								foto_sperre = ?, 
								zugangscode = ?, 
								matr_nr  = ?
						WHERE person_id = ?";
		
		$sqlParametersArray = array($person['sprache'], 
									$person['anrede'], 
									$person['titelpost'], 
									$person['titelpre'], 
									$person['nachname'], 
									$person['vorname'], 
									$person['vornamen'], 
									$person['gebdatum'], 
									$person['gebort'], 
									$person['gebzeit'], 
									$person['foto'], 
									$person['anmerkung'], 
									$person['homepage'], 
									$person['svnr'], 
									$person['ersatzkennzeichen'], 
									$person['familienstand'], 
									$person['anzahlkinder'], 
									$person['aktiv'], 
									"now()",
									$person['updatevon'], 
									$person['geschlecht'], 
									$person['geburtsnation'], 
									$person['staatsbuergerschaft'], 
									$person['kurzbeschreibung'], 
									$person['foto_sperre'], 
									$person['zugangscode'], 
									$person['matr_nr'],
									$person['person_id']);
		
		$result = $this->db->query($updateQuery, $sqlParametersArray);
		
		// Check DB transaction result
		if($this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();
			$result = FALSE;
		}
		else
		{
			$this->db->trans_commit();
			$result = TRUE;
		}
		
		return $result;
	}
	
	/**
	 * 
	 */
	public function saveInterestedStudent($interestedStudent = NULL)
	{
		// Checks if the operation is permitted by the API caller
		// All the code should be put inside this if statement
		if(isAllowed($this->_addonID, 'person'))
		{
			return $this->_saveInterestedStudent($interestedStudent);
		}
	}
	
	/**
	 * Method saveInterestedStudent
	 * 
	 * @return bool true when everything goes right, otherwise false
	 */
	private function _saveInterestedStudent($interestedStudent = NULL)
	{
		if(!isset($interestedStudent))
		{
			return FALSE;
		}
		
        if($interestedStudent['zgvmas_code'] && $interestedStudent['zgvmanation'])
		{
			$interestedStudent['ausstellungsstaat'] = $interestedStudent['zgvmanation'];
		}
		elseif($interestedStudent['zgv_code'] && $interestedStudent['zgvnation'])
		{
			$interestedStudent['ausstellungsstaat'] = $interestedStudent['zgvnation'];
		}

		//Variablen auf Gueltigkeit pruefen
		if(isset($interestedStudent['prestudent_id']) && $interestedStudent['punkte'] > 9999.9999)
		{
			//$this->errormsg = 'Reihungstestgesamtpunkte should be no bigger than 9999.9999';
			return FALSE;
		}
		if($interestedStudent['rt_punkte1'] > 9999.9999)
		{
			//$this->errormsg = 'Reihungstestpunkte1 should be no bigger than 9999.9999';
			return FALSE;
		}
		if($interestedStudent['rt_punkte2'] > 9999.9999)
		{
			//$this->errormsg = 'Reihungstestpunkte2 should be no bigger than 9999.9999';
			return FALSE;
		}
		if($interestedStudent['rt_punkte3'] > 9999.9999)
		{
			//$this->errormsg = 'Reihungstestpunkte3 should be no bigger than 9999.9999';
			return FALSE;
		}

		$this->db->trans_begin(); // Start DB transaction
		
		// If prestudent_id is NOT set it's an insert
		if(!isset($interestedStudent['prestudent_id']))
		{
			$insertQuery = "INSERT INTO public.tbl_prestudent (
											aufmerksamdurch_kurzbz,
											person_id,
											studiengang_kz,
											berufstaetigkeit_code,
											ausbildungcode,
											zgv_code,
											zgvort,
											zgvdatum,
											zgvnation,
											zgvmas_code,
											zgvmaort,
											zgvmadatum,
											zgvmanation,
											aufnahmeschluessel,
											facheinschlberuf,
											reihungstest_id,
											anmeldungreihungstest,
											reihungstestangetreten,
											rt_gesamtpunkte,
											rt_punkte1,
											rt_punkte2,
											rt_punkte3,
											bismelden,
											insertamum,
											insertvon,
											updateamum,
											updatevon,
											anmerkung,
											dual,
											ausstellungsstaat,
											mentor
							) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
									  ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
									  ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
			
			$sqlParametersArray = array($interestedStudent['aufmerksamdurch_kurzbz'], 
										$interestedStudent['person_id'], 
										$interestedStudent['studiengang_kz'], 
										$interestedStudent['berufstaetigkeit_code'], 
										$interestedStudent['ausbildungcode'], 
										$interestedStudent['zgv_code'], 
										$interestedStudent['zgvort'], 
										$interestedStudent['zgvdatum'], 
										$interestedStudent['zgvnation'], 
										$interestedStudent['zgvmas_code'], 
										$interestedStudent['zgvmaort'], 
										$interestedStudent['zgvmadatum'], 
										$interestedStudent['zgvmanation'], 
										$interestedStudent['aufnahmeschluessel'], 
										$interestedStudent['facheinschlberuf'], 
										$interestedStudent['reihungstest_id'], 
										$interestedStudent['anmeldungreihungstest'], 
										$interestedStudent['reihungstestangetreten'], 
										$interestedStudent['rt_gesamtpunkte'], 
										$interestedStudent['rt_punkte1'], 
										$interestedStudent['rt_punkte2'], 
										$interestedStudent['rt_punkte3'], 
										$interestedStudent['bismelden'], 
										$interestedStudent['insertamum'], 
										$interestedStudent['insertvon'], 
										$interestedStudent['updateamum'], 
										$interestedStudent['updatevon'], 
										$interestedStudent['anmerkung'], 
										$interestedStudent['dual'], 
										$interestedStudent['ausstellungsstaat'], 
										$interestedStudent['mentor']);
			
			$result = $this->db->query($insertQuery, $sqlParametersArray);
		}
		// otherwise it's an update
		else
		{
			$updateQuery = "UPDATE public.tbl_prestudent SET
									aufmerksamdurch_kurzbz = ?,
									person_id = ?,
									studiengang_kz = ?,
									berufstaetigkeit_code = ?,
									ausbildungcode = ?,
									zgv_code = ?,
									zgvort = ?,
									zgvdatum = ?,
									zgvnation = ?,
									zgvmas_code = ?,
									zgvmaort = ?,
									zgvmadatum = ?,
									zgvmanation = ?,
									aufnahmeschluessel = ?,
									facheinschlberuf = ?,
									reihungstest_id = ?,
									anmeldungreihungstest = ?,
									reihungstestangetreten = ?,
									rt_gesamtpunkte = ?,
									rt_punkte1 = ?,
									rt_punkte2 = ?,
									rt_punkte3 = ?,
									bismelden = ?,
									updateamum = ?,
									updatevon = ?,
									anmerkung = ?,
									mentor = ?,
									dual = ?,
									ausstellungsstaat = ?
							WHERE prestudent_id = ?";
					
			$sqlParametersArray = array($interestedStudent['aufmerksamdurch_kurzbz'], 
										$interestedStudent['person_id'], 
										$interestedStudent['studiengang_kz'], 
										$interestedStudent['berufstaetigkeit_code'], 
										$interestedStudent['ausbildungcode'], 
										$interestedStudent['zgv_code'], 
										$interestedStudent['zgvort'], 
										$interestedStudent['zgvdatum'], 
										$interestedStudent['zgvnation'], 
										$interestedStudent['zgvmas_code'], 
										$interestedStudent['zgvmaort'], 
										$interestedStudent['zgvmadatum'], 
										$interestedStudent['zgvmanation'], 
										$interestedStudent['aufnahmeschluessel'], 
										$interestedStudent['facheinschlberuf'], 
										$interestedStudent['reihungstest_id'], 
										$interestedStudent['anmeldungreihungstest'], 
										$interestedStudent['reihungstestangetreten'], 
										$interestedStudent['punkte'], 
										$interestedStudent['rt_punkte1'], 
										$interestedStudent['rt_punkte2'], 
										$interestedStudent['rt_punkte3'], 
										$interestedStudent['bismelden'], 
										$interestedStudent['updateamum'], 
										$interestedStudent['updatevon'], 
										$interestedStudent['anmerkung'], 
										$interestedStudent['mentor'], 
										$interestedStudent['dual'], 
										$interestedStudent['ausstellungsstaat'], 
										$interestedStudent['prestudent_id']);
			
			$result = $this->db->query($updateQuery, $sqlParametersArray);
		}
		
		// Check DB transaction result
		if($this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();
			$result = FALSE;
		}
		else
		{
			$this->db->trans_commit();
			$result = TRUE;
		}

		return $result;
	}
	
	private function _validate($person = NULL)
	{
		if(!isset($person))
		{
			return false;
		}
		
		$person['nachname'] = trim($person['nachname']);
		$person['vorname'] = trim($person['vorname']);
		$person['vornamen'] = trim($person['vornamen']);
		$person['anrede'] = trim($person['anrede']);
		$person['titelpost'] = trim($person['titelpost']);
		$person['titelpre'] = trim($person['titelpre']);

		if(mb_strlen($person['sprache']) > 16)
		{
			//$this->errormsg = 'Sprache darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(mb_strlen($person['anrede']) > 16)
		{
			//$this->errormsg = 'Anrede darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(mb_strlen($person['titelpost']) > 32)
		{
			//$this->errormsg = 'Titelpost darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if(mb_strlen($person['titelpre']) > 64)
		{
			//$this->errormsg = 'Titelpre darf nicht laenger als 64 Zeichen sein';
			return false;
		}
		if(mb_strlen($person['nachname']) > 64)
		{
			//$this->errormsg = 'Nachname darf nicht laenger als 64 Zeichen sein';
			return false;
		}
		if($person['nachname'] == '' || is_null($person['nachname']))
		{
			//$this->errormsg = 'Nachname muss eingegeben werden';
			return false;
		}

		if(mb_strlen($person['vorname']) > 32)
		{
			//$this->errormsg = 'Vorname darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if(mb_strlen($person['vornamen']) > 128)
		{
			//$this->errormsg = 'Vornamen darf nicht laenger als 128 Zeichen sein';
			return false;
		}
		//ToDo Gebdatum pruefen -> laut bis muss student aelter als 10 Jahre sein
		/* if (strlen($person['gebdatum) == 0 || is_null($person['gebdatum))
		  {
		  //$this->errormsg = "Geburtsdatum muss eingegeben werden\n";
		  return false;
		  } */
		if(mb_strlen($person['gebort']) > 128)
		{
			//$this->errormsg = 'Geburtsort darf nicht laenger als 128 Zeichen sein';
			return false;
		}

		if(mb_strlen($person['homepage']) > 256)
		{
			//$this->errormsg = 'Homepage darf nicht laenger als 256 Zeichen sein';
			return false;
		}
		if(mb_strlen($person['svnr']) > 16)
		{
			//$this->errormsg = 'SVNR darf nicht laenger als 16 Zeichen sein';
			return false;
		}

		if(mb_strlen($person['matr_nr']) > 32)
		{
			//$this->errormsg = 'Matrikelnummer darf nicht laenger als 32 Zeichen sein';
			return false;
		}

		if($person['svnr'] != '' && mb_strlen($person['svnr']) != 16 && mb_strlen($person['svnr']) != 10)
		{
			//$this->errormsg = 'SVNR muss 10 oder 16 Zeichen lang sein';
			return false;
		}

		if($person['svnr'] != '' && mb_strlen($person['svnr']) == 10)
		{
			//SVNR mit Pruefziffer pruefen
			//Die 4. Stelle in der SVNR ist die Pruefziffer
			//(Summe von (gewichtung[i]*svnr[i])) modulo 11 ergibt diese Pruefziffer
			//Falls nicht, ist die SVNR ungueltig
			$gewichtung = array(3, 7, 9, 0, 5, 8, 4, 2, 1, 6);
			$erg = 0;
			//Quersumme bilden
			for($i = 0; $i < 10; $i++)
			{
				$erg += $gewichtung[$i] * $person['svnr']{$i};
			}

			if($person['svnr']{3} != ($erg % 11)) //Vergleichen der Pruefziffer mit Quersumme Modulo 11
			{
				//$this->errormsg = 'SVNR ist ungueltig';
				return false;
			}
		}

		if($person['svnr'] != '')
		{
			//Pruefen ob bereits ein Eintrag mit dieser SVNR vorhanden ist
			$qry = "SELECT person_id FROM public.tbl_person WHERE svnr=" . $person['svnr'];
			if(db_query($qry))
			{
				if($row = db_fetch_object())
				{
					if($row->person_id != $person['person_id'])
					{
						//$this->errormsg = 'Es existiert bereits eine Person mit dieser SVNR! Daten wurden NICHT gepeichert.';
						return false;
					}
				}
			}
		}

		if(mb_strlen($person['ersatzkennzeichen']) > 10)
		{
			//$this->errormsg = 'Ersatzkennzeichen darf nicht laenger als 10 Zeichen sein';
			return false;
		}
		if(mb_strlen($person['familienstand']) > 1)
		{
			//$this->errormsg = 'Familienstand ist ungueltig';
			return false;
		}
		if($person['anzahlkinder'] != '' && !is_numeric($person['anzahlkinder']))
		{
			//$this->errormsg = 'Anzahl der Kinder ist ungueltig';
			return false;
		}
		if($person['aktiv'] != "t" && $person['aktiv'] != "f")
		{
			//$this->errormsg = 'Aktiv ist ungueltig';
			return false;
		}
		if(!isset($person['person_id']) && mb_strlen($person['insertvon']) > 32)
		{
			//$this->errormsg = 'Insertvon darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if(mb_strlen($person['updatevon']) > 32)
		{
			//$this->errormsg = 'Updatevon darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		/*if($person['ext_id'] != '' && !is_numeric($person['ext_id']))
		{
			//$this->errormsg = 'Ext_ID ist keine gueltige Zahl';
			return false;
		}*/
		if(mb_strlen($person['geschlecht']) > 1)
		{
			//$this->errormsg = 'Geschlecht darf nicht laenger als 1 Zeichen sein';
			return false;
		}
		if(mb_strlen($person['geburtsnation']) > 3)
		{
			//$this->errormsg = 'Geburtsnation darf nicht laenger als 3 Zeichen sein';
			return false;
		}
		if(mb_strlen($person['staatsbuergerschaft']) > 3)
		{
			//$this->errormsg = 'Staatsbuergerschaft darf nicht laenger als 3 Zeichen sein';
			return false;
		}
		if($person['geschlecht'] != 'm' && $person['geschlecht'] != 'w' && $person['geschlecht'] != 'u')
		{
			//$this->errormsg = 'Geschlecht muss w, m oder u sein!';
			return false;
		}

		//Pruefen ob das Geburtsdatum mit der SVNR uebereinstimmt.
		if($person['svnr'] != '' && $person['gebdatum'] != '')
		{
			if(mb_ereg("([0-9]{1,2}).([0-9]{1,2}).([0-9]{4})", $person['gebdatum'], $regs))
			{
				//$day = sprintf('%02s',$regs[1]);
				//$month = sprintf('%02s',$regs[2]);
				//$year = mb_substr($regs[3],2,2);
			}
			elseif(mb_ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})", $person['gebdatum'], $regs))
			{
				//$day = sprintf('%02s',$regs[3]);
				//$month = sprintf('%02s',$regs[2]);
				//$year = mb_substr($regs[1],2,2);
			}
			else
			{
				//$this->errormsg = 'Format des Geburtsdatums ist ungueltig';
				return false;
			}

			/* das muss nicht immer so sein
			  $day_svnr = mb_substr($person['svnr, 4, 2);
			  $month_svnr = mb_substr($person['svnr, 6, 2);
			  $year_svnr = mb_substr($person['svnr, 8, 2);

			  if ($day_svnr!=$day || $month_svnr!=$month || $year_svnr!=$year)
			  {
			  //$this->errormsg = 'SVNR und Geburtsdatum passen nicht zusammen';
			  return false;
			  }
			 */
		}

		return true;
	}
	
	/**
	 * Laedt Personendaten eine BenutzerUID
	 * @param	string	$uid	DB-Attr: tbl_benutzer.uid .
	 * @return	bool
	 */
	public function getPersonFromBenutzerUID($uid)
	{

		if(!$this->fhc_db_acl->bb->isBerechtigt('person', 's'))
		{
			$this->db->select('tbl_person.*');
			$this->db->from('public.tbl_person JOIN public.tbl_benutzer USING (person_id)');
			$query = $this->db->get_where(null, array('uid' => $uid));
			return $query->result_object();
		}
	}
	
	/**
	 * 
	 */
	public function checkBewerbung($email, $studiensemester_kurzbz = NULL)
	{
		$this->db->distinct();

		if(is_null($studiensemester_kurzbz))
		{
			$this->db->select("p.person_id, p.zugangscode, p.insertamum")
					->from("public.tbl_person p")
					->join("public.tbl_kontakt k", "p.person_id=k.person_id")
					->join("public.tbl_benutzer b", "p.person_id=b.person_id", "left")
					->where("k.kontakttyp", 'email')
					->where("(kontakt='" . $email . "'" .
							" OR alias ||'@technikum-wien.at'='" . $email . "'" .
							" OR uid ||'@technikum-wien.at'='" . $email . "')")
					->order_by("p.insertamum", "DESC")
					->limit(1)
			;
		}
		else
		{
			$this->db->select("p.person_id,p.zugangscode,p.insertamum")
					->from("public.tbl_person p")
					->join("public.tbl_kontakt k", "p.person_id=k.person_id")
					->join("public.tbl_benutzer b", "p.person_id=b.person_id", "left")
					->join("public.tbl_prestudent ps", "p.person_id=ps.person_id")
					->join("public.tbl_prestudentstatus pst", "pst.prestudent_id=ps.prestudent_id")
					->where("k.kontakttyp", 'email')
					->where("(kontakt='" . $email . "'" .
							" OR alias ||'@technikum-wien.at'='" . $email . "'" .
							" OR uid ||'@technikum-wien.at'='" . $email . "')")
					->where("studiensemester_kurzbz='" . $studiensemester_kurzbz . "'")
					->order_by("p.insertamum", "DESC")
					->limit(1)
			;
		}
		return $this->db->get()->result_array();
	}

	/**
	 * 
	 */
	public function checkZugangscodePerson($code)
	{
		$this->db->select("p.person_id")
				->from("public.tbl_person p")
				->where("p.zugangscode", $code);
		return $this->db->get()->result_array();
	}
}