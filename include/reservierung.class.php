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
		if(mb_strlen($this->titel)>10)
		{
			$this->errormsg = 'Titel darf nicht laenger als 10 Zeichen sein';
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
		if(mb_strlen($this->gruppe_kurzbz)>10)
		{
			$this->gruppe_kurzbz = 'Gruppe_kurzbz darf nicht laenger als 10 Zeichen sein';
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
			        VALUES('.$this->addslashes($this->ort_kurzbz).','.
					$this->addslashes($this->studiengang_kz).','.
					$this->addslashes($this->uid).','.
					$this->addslashes($this->stunde).','.
					$this->addslashes($this->datum).','.
					$this->addslashes($this->titel).','.
					$this->addslashes($this->beschreibung).','.
					$this->addslashes($this->semester).','.
					$this->addslashes($this->verband).','.
					$this->addslashes($this->gruppe).','.
					$this->addslashes($this->gruppe_kurzbz).','.
					$this->addslashes($this->insertamum).','.
					$this->addslashes($this->insertvon).');';
		}
		else
		{
			$qry = 'UPDATE campus.tbl_reservierung SET'.
			       ' ort_kurzbz='.$this->addslashes($this->ort_kurzbz).','.
			       ' studiengang_kz='.$this->addslashes($this->studiengang_kz).','.
			       ' uid='.$this->addslashes($this->uid).','.
			       ' stunde='.$this->addslashes($this->stunde).','.
			       ' datum='.$this->addslashes($this->datum).','.
			       ' titel='.$this->addslashes($this->titel).','.
			       ' beschreibung='.$this->addslashes($this->beschreibung).','.
			       ' semester='.$this->addslashes($this->semester).','.
			       ' verband='.$this->addslashes($this->verband).','.
			       ' gruppe='.$this->addslashes($this->gruppe).','.
			       ' gruppe_kurzbz='.$this->addslashes($this->gruppe_kurzbz).
			       " WHERE reservierung_id='".addslashes($this->reservierung_id)."'";
		}

		if($this->db_query($qry))
		{
			//Log schreiben
			$this->new = false;
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Reservierung:'.$qry;
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
		
		$qry = "DELETE FROM campus.tbl_reservierung WHERE reservierung_id='$reservierung_id'";
		
		if($this->db_query($qry))
			return true;
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
					ort_kurzbz='".addslashes($ort_kurzbz)."' AND 
					datum='".addslashes($datum)."' AND  
					stunde='".addslashes($stunde)."'";
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
