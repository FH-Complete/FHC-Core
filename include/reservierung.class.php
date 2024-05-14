<?php
/* Copyright (C) 2006 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/datum.class.php');
require_once(dirname(__FILE__).'/log.class.php');
require_once(dirname(__FILE__).'/authentication.class.php');

class reservierung extends basis_db 
{
	public $new;      					// boolean
	public $reservierungen = array(); 	// reservierung Objekt

	//Tabellenspalten
	public $reservierung_id;	// int
	public $ort_kurzbz;			// varchar(8)
	public $studiengang_kz;		// int
	public $uid;				// varchar(32)
	public $stunde;				// smalint
	public $datum;				// date
	public $titel;				// varchar(10)
	public $beschreibung;		// varchar(32)
	public $semester;			// smalint
	public $verband;			// char(1)
	public $gruppe;				// char(1)
	public $gruppe_kurzbz;		// varchar(10)
	public $insertamum;
	public $insertvon;

	/**
	 * Konstruktor - Uebergibt die Connection und laedt optional eine Reservierung
	 * @param $reservierung_id
	 */
	public function __construct($reservierung_id=null)
	{
		parent::__construct();
		$this->new = true;
		if($reservierung_id!=null)
			$this->load($reservierung_id);
	}

	/**
	 * Laedt eine Reservierung
	 * @param $reservierung_id
	 * @return boolean
	 */	
	public function load($reservierung_id)
	{
		$qry = "SELECT * FROM campus.tbl_reservierung WHERE reservierung_id=".$this->db_add_param($reservierung_id);

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->reservierung_id = $row->reservierung_id;
				$this->ort_kurzbz = $row->ort_kurzbz;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->uid = $row->uid;
				$this->stunde = $row->stunde;
				$this->datum = $row->datum;
				$this->titel = $row->titel;
				$this->beschreibung = $row->beschreibung;
				$this->semester = $row->semester;
				$this->verband = $row->verband;
				$this->gruppe = $row->gruppe;
				$this->gruppe_kurzbz = $row->gruppe_kurzbz;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				return true;
			}
			else
			{
				$this->errormsg = 'Eintrag wurde nicht gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Ladend er Daten';
			return false;
		}
	}

	/**
	 * Prueft die Variablen vor dem Speichern
	 * auf Gueltigkeit.
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		if($this->ort_kurzbz=='')
		{
			$this->errormsg = 'Es muss ein Ort angegeben werden';
			return false;
		}
		if(mb_strlen($this->ort_kurzbz)>16)
		{
			$this->errormsg = 'Ort_Kurzbz darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(!is_numeric($this->studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}
		if(mb_strlen($this->uid)>32)
		{
			$this->errormsg = 'UID darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if(!is_numeric($this->stunde))
		{
			$this->errormsg = 'Stunde ist ungueltig';
			return false;
		}
		if($this->titel=='')
		{
			$this->errormsg = 'Es muss ein Titel angegeben werden';
			return false;
		}
		if(mb_strlen($this->titel)>10)
		{
			$this->errormsg = 'Titel darf nicht laenger als 10 Zeichen sein';
			return false;
		}
		if($this->beschreibung=='')
		{
			$this->errormsg = 'Es muss eine Beschreibung angegeben werden';
			return false;
		}
		if(mb_strlen($this->beschreibung)>32)
		{
			$this->beschreibung = 'Beschreibung darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if($this->semester!='' && !is_numeric($this->semester))
		{
			$this->errormsg = 'Semester ist ungueltig';
			return false;
		}
		if(mb_strlen($this->verband)>1)
		{
			$this->errormsg = 'Verband darf nicht laenger als 1 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->gruppe)>1)
		{
			$this->errormsg = 'Gruppe darf nicht laenger als 1 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->gruppe_kurzbz)>32)
		{
			$this->errormsg = 'Gruppe_kurzbz darf nicht laenger als 32 Zeichen sein';
			return false;
		}

		return true;
	}

	/**
	 * Speichert Reservierung in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save($new=null)
	{
		if(!is_null($new))
			$this->new = $new;

		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			$qry = 'INSERT INTO campus.tbl_reservierung (ort_kurzbz, studiengang_kz, uid, stunde, datum, titel,
			                                      beschreibung, semester, verband, gruppe, gruppe_kurzbz, insertamum, insertvon)
			        VALUES('.$this->db_add_param($this->ort_kurzbz).','.
					$this->db_add_param($this->studiengang_kz).','.
					$this->db_add_param($this->uid).','.
					$this->db_add_param($this->stunde).','.
					$this->db_add_param($this->datum).','.
					$this->db_add_param($this->titel).','.
					$this->db_add_param($this->beschreibung).','.
					$this->db_add_param($this->semester).','.
					$this->db_add_param($this->verband).','.
					$this->db_add_param($this->gruppe).','.
					$this->db_add_param($this->gruppe_kurzbz).','.
					$this->db_add_param($this->insertamum).','.
					$this->db_add_param($this->insertvon).');';
		}
		else
		{
			$qry = 'UPDATE campus.tbl_reservierung SET'.
			       ' ort_kurzbz='.$this->db_add_param($this->ort_kurzbz).','.
			       ' studiengang_kz='.$this->db_add_param($this->studiengang_kz).','.
			       ' uid='.$this->db_add_param($this->uid).','.
			       ' stunde='.$this->db_add_param($this->stunde).','.
			       ' datum='.$this->db_add_param($this->datum).','.
			       ' titel='.$this->db_add_param($this->titel).','.
			       ' beschreibung='.$this->db_add_param($this->beschreibung).','.
			       ' semester='.$this->db_add_param($this->semester).','.
			       ' verband='.$this->db_add_param($this->verband).','.
			       ' gruppe='.$this->db_add_param($this->gruppe).','.
			       ' gruppe_kurzbz='.$this->db_add_param($this->gruppe_kurzbz).
			       " WHERE reservierung_id=".$this->db_add_param($this->reservierung_id, FHC_INTEGER);
		}

		if($this->db_query($qry))
		{
			//Log schreiben
			$this->new = false;
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Reservierung';
			return false;
		}
	}
	
	/**
	 * Loescht eine Reservierung
	 * @param reservierung_id ID der zu leoschenden Reservierung
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($reservierung_id)
	{
		if(!is_numeric($reservierung_id))
		{
			$this->errormsg = 'Reservierung_id muss eine gueltige Zahl sein';
			return false;
		}

		$reservierung = new reservierung($reservierung_id);
		$qry = "DELETE FROM campus.tbl_reservierung WHERE reservierung_id=".$this->db_add_param($reservierung_id, FHC_INTEGER);

		if($this->db_query($qry))
		{
			$logdata_reservierung = (array)$reservierung;
			$logdata = var_export($logdata_reservierung, true);
			$log = new log();
			$log->executetime = date('Y-m-d H:i:s');
			$log->sqlundo = '';
			$log->sql = 'DELETE FROM campus.tbl_reservierung WHERE reservierung_id='.$reservierung_id.'; LogData:'.$logdata;
			$log->beschreibung = 'Löschen der Reservierung '.$reservierung_id;
			$auth = new authentication();
			$uid = $auth->getUser();
			$log->mitarbeiter_uid = $uid;
			if(!$log->save(true))
			{
				$this->errormsg = 'Fehler: '.$log->errormsg;
				return false;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Loeschen der Reservierung';
			return false;
		}
	}
	
	/**
	 * Prueft ob ein Raum reserviert ist
	 *
	 * @param $ort_kurzbz
	 * @param $datum
	 * @param $stunde
	 * @return boolean
	 */
	public function isReserviert($ort_kurzbz, $datum, $stunde)
	{
		if(!is_numeric($stunde))
		{
			$this->errormsg='Stunde muss eine gueltige Zahl sein';
			return false;
		}
		
		$datum_obj = new Datum();
		if(!$datum_obj->checkDatum($datum))
		{
			$this->errormsg='Datum hat ein ungueltiges Format';
			return false;
		}
				
		$qry = "SELECT * FROM campus.tbl_reservierung 
				WHERE 
					ort_kurzbz=".$this->db_add_param($ort_kurzbz)." AND 
					datum=".$this->db_add_param($datum)." AND  
					stunde=".$this->db_add_param($stunde);
		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result)>0)
				return true;
			else 
				return false;
		}
		else 
		{
			$this->errormsg='Fehler beim Ermitteln der Reservierungen';
			return false;
		}
				
	}
}
?>
