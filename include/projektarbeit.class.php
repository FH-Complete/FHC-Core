<?php
/* Copyright (C) 2007 Technikum-Wien
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
/**
 * Klasse projektarbeit
 * @create 08-02-2007
 */

class projektarbeit
{
	var $conn;     			// @var resource DB-Handle
	var $new;       		// @var boolean
	var $errormsg;  		// @var string
	var $result = array(); 	// @var adresse Objekt

	//Tabellenspalten
	var $projektarbeit_id;	// @var integer
	var $projekttyp_kurzbz;	// @var string
	var $titel;			// @var string
	var $lehreinheit_id;		// @var integer
	var $student_uid;		// @var integer
	var $firma_id;			// @var integer
	var $note;			// @var integer
	var $punkte;			// @var numeric(6,2)
	var $beginn;			// @var date
	var $ende;			// @var date
	var $faktor;			// @var numeric(3,2)
	var $freigegeben;		// @var boolean
	var $gesperrtbis;		// @var date
	var $stundensatz;		// @var numeric(6,2)
	var $gesamtstunden;	// @var numeric(8,2)
	var $themenbereich;		// @var sting
	var $anmerkung;		// @var string
	var $ext_id;			// @var integer
	var $insertamum;		// @var timestamp
	var $insertvon;		// @var bigint
	var $updateamum;		// @var timestamp
	var $updatevon;		// @var bigint


	/**
	 * Konstruktor
	 * @param $conn      Connection
	 *        $projektarbeit_id ID der Projektarbeit, die geladen werden soll (Default=null)
	 */
	function projektarbeit($conn,$projektarbeit_id=null, $unicode=false)
	{
		$this->conn = $conn;
		
		if($unicode!=null)
		{
			if ($unicode)
			{
				$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
			}
			else
			{
				$qry="SET CLIENT_ENCODING TO 'LATIN9';";
			}
			if(!pg_query($conn,$qry))
			{
				$this->errormsg	 = "Encoding konnte nicht gesetzt werden";
				return false;
			}
		}
		if($projektarbeit_id != null) 	
			$this->load($projektarbeit_id);
	}

	/**
	 * Laedt die Projektarbeit mit der ID $projektarbeit_id
	 * @param  $projektarbeit_id ID der zu ladenden Projektarbeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($projektarbeit_id)
	{
		if(!is_numeric($projektarbeit_id))
		{
			$this->errormsg = 'Projektarbeit_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM lehre.tbl_projektarbeit WHERE projektarbeit_id='$projektarbeit_id'";
		
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->projektarbeit_id = $row->projektarbeit_id;
				$this->projekttyp_kurzbz = $row->projekttyp_kurzbz;
				$this->titel = $row->titel;
				$this->lehreinheit_id = $row->lehreinheit_id;
				$this->student_uid = $row->student_uid;
				$this->firma_id = $row->firma_id;
				$this->note = $row->note;
				$this->punkte = $row->punkte;
				$this->beginn = $row->beginn;
				$this->ende = $row->ende;
				$this->faktor = $row->faktor;
				$this->freigegeben = ($row->freigegeben=='t'?true:false);
				$this->gesperrtbis = $row->gesperrtbis;
				$this->stundensatz = $row->stundensatz;
				$this->gesamtstunden = $row->gesamtstunden;
				$this->themenbereich = $row->themenbereich;
				$this->anmerkung = $row->anmerkung;
				$this->ext_id = $row->ext_id;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				return true;
			}
			else 
			{
				$this->errormsg = 'Datensatz wurde nicht gefunden';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Prueft die Variablen auf gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	function checkvars()
	{

		//Gesamtlaenge pruefen
		//$this->errormsg='Eine der Gesamtlaengen wurde ueberschritten';
		if ($this->projekttyp_kurzbz==null)
		{
			$this->errormsg='Projekttyp_kurzbz darf nicht NULL sein! - student_uid: '.$this->student_uid;
		}
		if ($this->lehreinheit_id==null)
		{
			$this->errormsg='Lehreinheit_id darf nicht NULL sein! - student_uid: '.$this->student_uid;
		}
		if(strlen($this->projekttyp_kurzbz)>16)
		{
			$this->errormsg = 'Projektyp_kurzbz darf nicht länger als 16 Zeichen sein  - student_uid: '.$this->student_uid;
			return false;
		}
		if(strlen($this->titel)>256)
		{
			$this->errormsg = 'Titel darf nicht länger als 256 Zeichen sein - student_uid: '.$this->student_uid;
			return false;
		}
		if(strlen($this->themenbereich)>64)
		{
			$this->errormsg = 'Themenbereich darf nicht länger als 64 Zeichen sein - student_uid: '.$this->student_uid;
			return false;
		}
		if(strlen($this->anmerkung)>256)
		{
			$this->errormsg = 'Anmerkung darf nicht länger als 256 Zeichen sein - student_uid: '.$this->student_uid;
			return false;
		}
		if(!is_numeric($this->note))
		{
			$this->errormsg = 'Note muß ein numerischer Wert sein - student_uid: '.$this->student_uid;
			return false;
		}
		if(!is_numeric($this->punkte))
		{
			$this->errormsg = 'Punkte muß ein numerischer Wert sein - student_uid: '.$this->student_uid;
			return false;
		}
		if(!is_numeric($this->faktor))
		{
			$this->errormsg = 'Faktor muß ein numerischer Wert sein - student_uid: '.$this->student_uid;
			return false;
		}
		if(!is_numeric($this->stundensatz))
		{
			$this->errormsg = 'Stundensatz muß ein numerischer Wert sein - student_uid: '.$this->student_uid;
			return false;
		}
		if(!is_numeric($this->gesamtstunden))
		{
			$this->errormsg = 'Gesamtstunden muß ein numerischer Wert sein - student_uid: '.$this->student_uid;
			return false;
		}
		if(!is_bool($this->freigegeben))
		{
			$this->errormsg = 'freigegeben ist ungueltig - student_uid: '.$this->student_uid;
			return false;
		}

		$this->errormsg = '';
		return true;
	}
	// ************************************************
	// * wenn $var '' ist wird "null" zurueckgegeben
	// * wenn $var !='' ist werden datenbankkritische
	// * Zeichen mit backslash versehen und das Ergebnis
	// * unter Hochkomma gesetzt.
	// ************************************************
	function addslashes($var)
	{
		return ($var!=''?"'".addslashes($var)."'":'null');
	}
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $projektarbeit_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	function save($new=null)
	{
		//Variablen pruefen
		if(!$this->checkvars())
			return false;

		if($new==null)
			$new = $this->new;
			
		if($new)
		{
			//Neuen Datensatz einfuegen

			$qry='BEGIN; INSERT INTO lehre.tbl_projektarbeit (projekttyp_kurzbz, titel, lehreinheit_id, student_uid, firma_id, note, punkte, 
				beginn, ende, faktor, freigegeben, gesperrtbis, stundensatz, gesamtstunden, themenbereich, anmerkung, 
				ext_id, insertamum, insertvon, updateamum, updatevon) VALUES('.
			     $this->addslashes($this->projekttyp_kurzbz).', '.
			     $this->addslashes($this->titel).', '.
			     $this->addslashes($this->lehreinheit_id).', '.
			     $this->addslashes($this->student_uid).', '.
			     $this->addslashes($this->firma_id).', '.
			     $this->addslashes($this->note).', '.
			     $this->addslashes($this->punkte).', '.
			     $this->addslashes($this->beginn).', '.
			     $this->addslashes($this->ende).', '.
			     $this->addslashes($this->faktor).', '.
			     ($this->freigegeben?'true':'false').', '.
			     $this->addslashes($this->gesperrtbis).', '.
			     $this->addslashes($this->stundensatz).', '.
			     $this->addslashes($this->gesamtstunden).', '.
			     $this->addslashes($this->themenbereich).', '.
			     $this->addslashes($this->anmerkung).', '.
			     $this->addslashes($this->ext_id).',  now(), '.
			     $this->addslashes($this->insertvon).', now(), '.
			     $this->addslashes($this->updatevon).');';
		}
		else
		{
			//Updaten des bestehenden Datensatzes

			//Pruefen ob projektarbeit_id eine gueltige Zahl ist
			if(!is_numeric($this->projektarbeit_id))
			{
				$this->errormsg = 'projektarbeit_id muss eine gueltige Zahl sein';
				return false;
			}

			$qry='UPDATE lehre.tbl_projektarbeit SET '.
				'projekttyp_kurzbz='.$this->addslashes($this->projekttyp_kurzbz).', '.
				'titel='.$this->addslashes($this->titel).', '.
				'lehreinheit_id='.$this->addslashes($this->lehreinheit_id).', '.
				'student_uid='.$this->addslashes($this->student_uid).', '.
				'firma_id='.$this->addslashes($this->firma_id).', '.
				'note='.$this->addslashes($this->note).', '.
				'punkte='.$this->addslashes($this->punkte).', '.
				'beginn='.$this->addslashes($this->beginn).', '.
				'ende='.$this->addslashes($this->ende).', '.
				'faktor='.$this->addslashes($this->faktor).', '.
				'freigegeben='.($this->freigegeben?'true':'false').', '.
				'gesperrtbis='.$this->addslashes($this->gesperrtbis).', '.
				'stundensatz='.$this->addslashes($this->stundensatz).', '.
				'gesamtstunden='.$this->addslashes($this->gesamtstunden).', '.
				'themenbereich='.$this->addslashes($this->themenbereich).', '.
				'anmerkung='.$this->addslashes($this->anmerkung).', '.
				'updateamum= now(), '.
				'updatevon='.$this->addslashes($this->updatevon).' '.
				'WHERE projektarbeit_id='.$this->addslashes($this->projektarbeit_id).';';
		}
		
		if(pg_query($this->conn,$qry))
		{
			if($new)
			{
				//Sequence auslesen
				$qry = "SELECT currval('lehre.tbl_projektarbeit_projektarbeit_id_seq') as id;";
				if($result = pg_query($this->conn, $qry))
				{
					if($row = pg_fetch_object($result))
					{
						$this->projektarbeit_id = $row->id;
						pg_query($this->conn, 'COMMIT');
						return true;
					}
					else 
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						pg_query($this->conn, 'ROLLBACK;');
						return false;
					}
				}
				else 
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					pg_query($this->conn, 'ROLLBACK;');
					return false;
				}
			}
					
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $projektarbeit_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($projektarbeit_id)
	{
		if(!is_numeric($projektarbeit_id))
		{
			$this->errormsg = 'Projektarbeit_id ist ungueltig';
			return true;
		}
		
		$qry = "DELETE FROM lehre.tbl_projektarbeit WHERE projektarbeit_id='$projektarbeit_id'";
		
		if(pg_query($this->conn, $qry))
		{
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Loeschen des Datensatzes';
			return false;
		}		
	}
	
	// ********************************************
	// * Laedt alle Projektarbeiten eines Studenten
	// * @param student_uid
	// * @return true wenn ok, false wenn Fehler
	// ********************************************
	function getProjektarbeit($student_uid)
	{
		$qry = "SELECT * FROM lehre.tbl_projektarbeit WHERE student_uid='".addslashes($student_uid)."'";
		
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$obj = new projektarbeit($this->conn, null, null);
				
				$obj->projektarbeit_id = $row->projektarbeit_id;
				$obj->projekttyp_kurzbz = $row->projekttyp_kurzbz;
				$obj->titel = $row->titel;
				$obj->lehreinheit_id = $row->lehreinheit_id;
				$obj->student_uid = $row->student_uid;
				$obj->firma_id = $row->firma_id;
				$obj->note = $row->note;
				$obj->punkte = $row->punkte;
				$obj->beginn = $row->beginn;
				$obj->ende = $row->ende;
				$obj->faktor = $row->faktor;
				$obj->freigegeben = ($row->freigegeben=='t'?true:false);
				$obj->gesperrtbis = $row->gesperrtbis;
				$obj->stundensatz = $row->stundensatz;
				$obj->gesamtstunden = $row->gesamtstunden;
				$obj->themenbereich = $row->themenbereich;
				$obj->anmerkung = $row->anmerkung;
				$obj->ext_id = $row->ext_id;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				
				$this->result[] = $obj;
			}

		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
}
?>