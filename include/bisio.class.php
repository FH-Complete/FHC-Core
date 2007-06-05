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
/**
 * Klasse bisio - Incomming/Outgoing
 * @create 2007-05-14
 */

class bisio
{
	var $conn;     			// resource DB-Handle
	var $new;       		// boolean
	var $errormsg;  		// string
	var $result = array();	// adresse Objekt

	//Tabellenspalten
	var $bisio_id; 					// serial
	var $mobilitaetsprogramm_code; 	// integer
	var $mobilitaetsprogramm_kurzbz;
	var $nation_code; 				// varchar(3)
	var $von; 						// date
	var $bis; 						// date
	var $zweck_code; 				// varchar(20)
	var $zweck_bezeichnung;
	var $student_uid; 				// varchar(16)
	var $updateamum; 				// timestamp
	var $updatevon; 				// varchar(16)
	var $insertamum; 				// timestamp
	var $insertvon; 				// varchar(16) 
	var $ext_id;					// bigint

	// **************************************************************************
	// * Konstruktor
	// * @param $conn      Connection
	// *        $bisio_id  ID die geladen werden soll (Default=null)
	// *        $unicode   Wenn true dann wird das Encoding auf Unicode gesetzt
	// **************************************************************************
	function bisio($conn, $bisio_id=null, $unicode=false)
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

		if($bisio_id!=null)
			$this->load($bisio_id);
	}

	// ************************************************
	// * Laedt die Funktion mit der ID $buchungsnr
	// * @param  $buchungsnr ID der zu ladenden  Email
	// * @return true wenn ok, false im Fehlerfall
	// ************************************************
	function load($bisio_id)
	{
		if(!is_numeric($bisio_id))
		{
			$this->errormsg = 'ID muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM bis.tbl_bisio WHERE bisio_id='$bisio_id'";

		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->bisio_id = $row->bisio_id;
				$this->mobilitaetsprogramm_code = $row->mobilitaetsprogramm_code;
				$this->nation_code = $row->nation_code;
				$this->von = $row->von;
				$this->bis = $row->bis;
				$this->zweck_code = $row->zweck_code;
				$this->student_uid = $row->student_uid;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				
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

	// *******************************************
	// * Prueft die Variablen auf Gueltigkeit
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************
	function validate()
	{
		if(!is_numeric($this->mobilitaetsprogramm_code))
		{
			$this->errormsg = 'Mobilitaetsprogramm ist ungueltig';
			return false;
		}
		
		if(strlen($this->nation_code)>3)
		{
			$this->errormsg = 'Nation ist ungueltig';
			return false;
		}
		
		if(strlen($this->zweck_code)>20)
		{
			$this->errormsg = 'Zweck ist ungueltig';
			return false;
		}
		
		if(strlen($this->student_uid)>16)
		{
			$this->errormsg = 'Student_UID ist ungueltig';
			return false;
		}
		
		if($this->von!='' && !ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})",$this->von))
		{			
			$this->errormsg = 'VON-Datum hat ein ungueltiges Format';
			return false;
		}
		
		if($this->bis!='' && !ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})",$this->bis))
		{
			$this->errormsg = 'BIS-Datum hat ein ungueltiges Format';
			return false;
		}
		
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

	// ***********************************************************************
	// * Speichert den aktuellen Datensatz in die Datenbank
	// * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	// * andernfalls wird der Datensatz mit der ID in $kontakt_id aktualisiert
	// * @param $new true wenn insert false wenn update
	// * @return true wenn ok, false im Fehlerfall
	// ***********************************************************************
	function save($new=null)
	{
		//Variablen pruefen
		if(!$this->validate())
			return false;

		if($new==null)
			$new = $this->new;

		if($new)
		{
			//Neuen Datensatz einfuegen

			$qry='BEGIN;INSERT INTO bis.tbl_bisio (mobilitaetsprogramm_code, nation_code, von, bis, zweck_code, student_uid, updateamum, updatevon, insertamum, insertvon, ext_id) VALUES('.
			     $this->addslashes($this->mobilitaetsprogramm_code).', '.
			     $this->addslashes($this->nation_code).', '.
			     $this->addslashes($this->von).', '.
			     $this->addslashes($this->bis).', '.
			     $this->addslashes($this->zweck_code).', '.
			     $this->addslashes($this->student_uid).', '.
			     $this->addslashes($this->updateamum).', '.
			     $this->addslashes($this->updatevon).', '.
			     $this->addslashes($this->insertamum).', '.
			     $this->addslashes($this->insertvon).', '.
			     $this->addslashes($this->ext_id).');';
		}
		else
		{
			//Updaten des bestehenden Datensatzes
			$qry = 'UPDATE bis.tbl_bisio SET '.
				   ' mobilitaetsprogramm_code='.$this->addslashes($this->mobilitaetsprogramm_code).','.
				   ' nation_code='.$this->addslashes($this->nation_code).','.
				   ' von='.$this->addslashes($this->von).','.
				   ' bis='.$this->addslashes($this->bis).','.
				   ' zweck_code='.$this->addslashes($this->zweck_code).','.
				   ' student_uid='.$this->addslashes($this->student_uid).','.
				   ' updateamum='.$this->addslashes($this->updateamum).','.
				   ' updatevon='.$this->addslashes($this->updatevon).','.
				   ' ext_id='.$this->addslashes($this->ext_id).
				   " WHERE bisio_id='".addslashes($this->bisio_id)."';";
		}
		//echo $qry;

		if(pg_query($this->conn, $qry))
		{
				if($new)
				{
					$qry = "SELECT currval('bis.tbl_bisio_bisio_id_seq') as id";
					if($result = pg_query($this->conn, $qry))
					{
						if($row = pg_fetch_object($result))
						{
							$this->bisio_id = $row->id;
							pg_query($this->conn, 'COMMIT;');
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
			return true;
		}
	}

	// ********************************************************
	// * Loescht den Datenensatz mit der ID die uebergeben wird
	// * @param bisio_id ID die geloescht werden soll
	// * @return true wenn ok, false im Fehlerfall
	// ********************************************************
	function delete($bisio_id)
	{
		if(!is_numeric($bisio_id))
		{
			$this->errormsg = 'ID ist ungueltig';
			return false;
		}
		
		$qry = "DELETE FROM bis.tbl_bisio WHERE bisio_id='$bisio_id'";
		
		if(pg_query($this->conn, $qry))
			return true;
		else 
		{
			$this->errormsg = 'Fehler beim Loeschen des Datensatzes';
			return false;
		}
	}

	// *****************************************
	// * Liefert alle Incomming/Outgoing 
	// * Eintraege eines Studenten
	// * @param $uid
	// * @return true wenn ok, false wenn fehler
	// *****************************************
	function getIO($uid)
	{
		$qry = "SELECT	tbl_bisio.*, 
						tbl_mobilitaetsprogramm.kurzbz as mobilitaetsprogramm_kurzbz,
						tbl_zweck.bezeichnung as zweck_bezeichnung
			    FROM 
			    	bis.tbl_bisio, 
			    	bis.tbl_zweck, 
			    	bis.tbl_mobilitaetsprogramm 
				WHERE 
					student_uid='".addslashes($uid)."' AND
					tbl_zweck.zweck_code=tbl_bisio.zweck_code AND
					tbl_mobilitaetsprogramm.mobilitaetsprogramm_code=tbl_bisio.mobilitaetsprogramm_code
				ORDER BY bis";
		
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$io = new bisio($this->conn, null, null);
				
				$io->bisio_id = $row->bisio_id;
				$io->mobilitaetsprogramm_code = $row->mobilitaetsprogramm_code;
				$io->mobilitaetsprogramm_kurzbz = $row->mobilitaetsprogramm_kurzbz;
				$io->nation_code = $row->nation_code;
				$io->von = $row->von;
				$io->bis = $row->bis;
				$io->zweck_code = $row->zweck_code;
				$io->zweck_bezeichnung = $row->zweck_bezeichnung;
				$io->student_uid = $row->student_uid;
				$io->updateamum = $row->updateamum;
				$io->udpatevon = $row->updatevon;
				$io->insertamum = $row->insertamum;
				$io->insertvon = $row->insertvon;
				$io->ext_id = $row->ext_id;
				
				$this->result[] = $io;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	
}
?>