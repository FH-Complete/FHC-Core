<?php

class Person_model extends DB_Model
{
	/**
	 * 
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_person';
		$this->pk = 'person_id';
	}

	/**
	 * 
	 */
	public function checkBewerbung($email, $studiensemester_kurzbz = null)
	{
		// Checks if the operation is permitted by the API caller
		if (! $this->fhc_db_acl->isBerechtigt($this->acl['public.tbl_person'], 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->acl['public.tbl_person'], FHC_MODEL_ERROR);
		
		if (! $this->fhc_db_acl->isBerechtigt($this->acl['public.tbl_kontakt'], 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->acl['public.tbl_kontakt'], FHC_MODEL_ERROR);
		
		if (! $this->fhc_db_acl->isBerechtigt($this->acl['public.tbl_benutzer'], 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->acl['public.tbl_benutzer'], FHC_MODEL_ERROR);
		
		if (! $this->fhc_db_acl->isBerechtigt($this->acl['public.tbl_prestudent'], 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->acl['public.tbl_prestudent'], FHC_MODEL_ERROR);
		
		if (! $this->fhc_db_acl->isBerechtigt($this->acl['public.tbl_prestudentstatus'], 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->acl['public.tbl_prestudentstatus'], FHC_MODEL_ERROR);
		
		$result = null;
		
		if (is_null($studiensemester_kurzbz))
		{
			$checkBewerbungQuery = "SELECT DISTINCT p.person_id, p.zugangscode, p.insertamum
 									  FROM public.tbl_person p JOIN public.tbl_kontakt k ON p.person_id = k.person_id
							     LEFT JOIN public.tbl_benutzer b ON p.person_id = b.person_id
								     WHERE k.kontakttyp = 'email'
									   AND (kontakt = ? OR alias || '@technikum-wien.at' = ? OR uid || '@technikum-wien.at' = ?)
								  ORDER BY p.insertamum DESC
								     LIMIT 1";
			
			$result = $this->db->query($checkBewerbungQuery, array($email, $email, $email));
		}
		else
		{
			$checkBewerbungQuery = "SELECT DISTINCT p.person_id, p.zugangscode, p.insertamum
									  FROM public.tbl_person p JOIN public.tbl_kontakt k ON p.person_id = k.person_id
								 LEFT JOIN public.tbl_benutzer b ON p.person_id = b.person_id
									  JOIN public.tbl_prestudent ps ON p.person_id = ps.person_id
									  JOIN public.tbl_prestudentstatus pst ON pst.prestudent_id = ps.prestudent_id
									 WHERE k.kontakttyp = 'email'
									   AND (kontakt = ? OR alias || '@technikum-wien.at' = ? OR uid || '@technikum-wien.at' = ?)
									   AND studiensemester_kurzbz = ?
								  ORDER BY p.insertamum DESC
									 LIMIT 1";
			
			$result = $this->db->query($checkBewerbungQuery, array($email, $email, $email, $studiensemester_kurzbz));
		}
		
		if (is_object($result))
			return $this->_success($result->result());
		else
			return $this->_error($this->db->error(), FHC_DB_ERROR);
	}
	
	public function _validate($person = NULL)
	{
		if (!isset($person))
		{
			return $this->_error('Parameter is null');
		}
		
		$person['nachname'] = trim($person['nachname']);
		$person['vorname'] = trim($person['vorname']);
		$person['vornamen'] = trim($person['vornamen']);
		$person['anrede'] = trim($person['anrede']);
		$person['titelpost'] = trim($person['titelpost']);
		$person['titelpre'] = trim($person['titelpre']);

		if (mb_strlen($person['sprache']) > 16)
		{
			return $this->_error('Sprache darf nicht laenger als 16 Zeichen sein');
		}
		if (mb_strlen($person['anrede']) > 16)
		{
			return $this->_error('Anrede darf nicht laenger als 16 Zeichen sein');
		}
		if (mb_strlen($person['titelpost']) > 32)
		{
			return $this->_error('Titelpost darf nicht laenger als 32 Zeichen sein');
		}
		if (mb_strlen($person['titelpre']) > 64)
		{
			return $this->_error('Titelpre darf nicht laenger als 64 Zeichen sein');
		}
		if (mb_strlen($person['nachname']) > 64)
		{
			return $this->_error('Nachname darf nicht laenger als 64 Zeichen sein');
		}
		if ($person['nachname'] == '' || is_null($person['nachname']))
		{
			return $this->_error('Nachname muss eingegeben werden');
		}

		if (mb_strlen($person['vorname']) > 32)
		{
			return $this->_error('Vorname darf nicht laenger als 32 Zeichen sein');
		}
		if (mb_strlen($person['vornamen']) > 128)
		{
			return $this->_error('Vornamen darf nicht laenger als 128 Zeichen sein');
		}
		//ToDo Gebdatum pruefen -> laut bis muss student aelter als 10 Jahre sein
		/* if (strlen($person['gebdatum) == 0 || is_null($person['gebdatum))
		  {
		  return $this->_error("Geburtsdatum muss eingegeben werden\n";
		  return false;
		  } */
		if (mb_strlen($person['gebort']) > 128)
		{
			return $this->_error('Geburtsort darf nicht laenger als 128 Zeichen sein');
		}

		if (mb_strlen($person['homepage']) > 256)
		{
			return $this->_error('Homepage darf nicht laenger als 256 Zeichen sein');
		}
		if (mb_strlen($person['svnr']) > 16)
		{
			return $this->_error('SVNR darf nicht laenger als 16 Zeichen sein');
		}

		if (mb_strlen($person['matr_nr']) > 32)
		{
			return $this->_error('Matrikelnummer darf nicht laenger als 32 Zeichen sein');
			return false;
		}

		if ($person['svnr'] != '' && mb_strlen($person['svnr']) != 16 && mb_strlen($person['svnr']) != 10)
		{
			return $this->_error('SVNR muss 10 oder 16 Zeichen lang sein');
		}

		if ($person['svnr'] != '' && mb_strlen($person['svnr']) == 10)
		{
			//SVNR mit Pruefziffer pruefen
			//Die 4. Stelle in der SVNR ist die Pruefziffer
			//(Summe von (gewichtung[i]*svnr[i])) modulo 11 ergibt diese Pruefziffer
			//Falls nicht, ist die SVNR ungueltig
			$gewichtung = array(3, 7, 9, 0, 5, 8, 4, 2, 1, 6);
			$erg = 0;
			//Quersumme bilden
			for ($i = 0; $i < 10; $i++)
			{
				$erg += $gewichtung[$i] * $person['svnr']{$i};
			}

			if ($person['svnr']{3} != ($erg % 11)) //Vergleichen der Pruefziffer mit Quersumme Modulo 11
			{
				return $this->_error('SVNR ist ungueltig');
			}
		}

		/*if ($person['svnr'] != '')
		{
			//Pruefen ob bereits ein Eintrag mit dieser SVNR vorhanden ist
			$qry = "SELECT person_id FROM public.tbl_person WHERE svnr=" . $person['svnr'];
			if (db_query($qry))
			{
				if ($row = db_fetch_object())
				{
					if ($row->person_id != $person['person_id'])
					{
						return $this->_error('Es existiert bereits eine Person mit dieser SVNR! Daten wurden NICHT gespeichert.');
					}
				}
			}
		}*/

		if (mb_strlen($person['ersatzkennzeichen']) > 10)
		{
			return $this->_error('Ersatzkennzeichen darf nicht laenger als 10 Zeichen sein');
		}
		if (mb_strlen($person['familienstand']) > 1)
		{
			return $this->_error('Familienstand ist ungueltig');
		}
		if ($person['anzahlkinder'] != '' && !is_numeric($person['anzahlkinder']))
		{
			return $this->_error('Anzahl der Kinder ist ungueltig');
		}
		if ($person['aktiv'] != "t" && $person['aktiv'] != "f")
		{
			return $this->_error('Aktiv ist ungueltig');
		}
		if (!isset($person['person_id']) && mb_strlen($person['insertvon']) > 32)
		{
			return $this->_error('Insertvon darf nicht laenger als 32 Zeichen sein');
		}
		if (mb_strlen($person['updatevon']) > 32)
		{
			return $this->_error('Updatevon darf nicht laenger als 32 Zeichen sein');
		}
		/*if ($person['ext_id'] != '' && !is_numeric($person['ext_id']))
		{
			return $this->_error('Ext_ID ist keine gueltige Zahl';
			return false;
		}*/
		if (mb_strlen($person['geschlecht']) > 1)
		{
			return $this->_error('Geschlecht darf nicht laenger als 1 Zeichen sein');
		}
		if (mb_strlen($person['geburtsnation']) > 3)
		{
			return $this->_error('Geburtsnation darf nicht laenger als 3 Zeichen sein');
		}
		if (mb_strlen($person['staatsbuergerschaft']) > 3)
		{
			return $this->_error('Staatsbuergerschaft darf nicht laenger als 3 Zeichen sein');
		}
		if ($person['geschlecht'] != 'm' && $person['geschlecht'] != 'w' && $person['geschlecht'] != 'u')
		{
			return $this->_error('Geschlecht muss w, m oder u sein!');
		}

		//Pruefen ob das Geburtsdatum mit der SVNR uebereinstimmt.
		if ($person['svnr'] != '' && $person['gebdatum'] != '')
		{
			if (mb_ereg("([0-9]{1,2}).([0-9]{1,2}).([0-9]{4})", $person['gebdatum'], $regs))
			{
				//$day = sprintf('%02s',$regs[1]);
				//$month = sprintf('%02s',$regs[2]);
				//$year = mb_substr($regs[3],2,2);
			}
			elseif (mb_ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})", $person['gebdatum'], $regs))
			{
				//$day = sprintf('%02s',$regs[3]);
				//$month = sprintf('%02s',$regs[2]);
				//$year = mb_substr($regs[1],2,2);
			}
			else
			{
				return $this->_error('Format des Geburtsdatums ist ungueltig');
			}

			/* das muss nicht immer so sein
			  $day_svnr = mb_substr($person['svnr, 4, 2);
			  $month_svnr = mb_substr($person['svnr, 6, 2);
			  $year_svnr = mb_substr($person['svnr, 8, 2);

			  if ($day_svnr!=$day || $month_svnr!=$month || $year_svnr!=$year)
			  {
			  return $this->_error('SVNR und Geburtsdatum passen nicht zusammen';
			  return false;
			  }
			 */
		}

		return $this->_success('Input data are valid');
	}
}