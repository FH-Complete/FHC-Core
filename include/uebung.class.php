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

class uebung
{
	var $conn;     // resource DB-Handle
	var $errormsg; // string
	var $new;      // boolean
	var $uebungen = array(); // lehreinheit Objekt

	//Tabellenspalten
	var $uebung_id;		// serial
	var $gewicht;		// smalint
	var $punkte;		// Real
	var $angabedatei;	// oid
	var $freigabevon;	// timestamp
	var $freigabebis;	// timestamp
	var $abgabe;		// boolean
	var $beispiele;		// boolean
	var $bezeichnung;	// varchar(32)
	var $positiv;		// boolean
	var $defaultbemerkung;	// text
	var $lehreinheit_id;	// integer
	var $updateamum;		// timestamp
	var $updatevon;			// varchar(16)
	var $insertamum;		// timestamp
	var $insertvon;			// varchar(16)
	var $statistik;			// boolean
	var $liste_id;			//integer
	var $maxbsp;			//smallint
	var $maxstd;			//smallint
	var $nummer;			//smallint

	//Studentuebung
	var $student_uid;		// varchar(16)
	var $mitarbeiter_uid;	// varchar(16)
	var $abgabe_id;			// integer
	var $note;				// smalint
	var $mitarbeitspunkte;	// smalint
	var $anmerkung;			// text
	var $benotungsdatum;	// timestamp
	
	//Abgabe
	var $abgabe_abgabe_id;	// integer
	var $abgabedatei;	// varchar(64)
	var $abgabezeit;		// timestamp
	var $abgabe_anmerkung;			// text

	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional eine Uebung
	// * @param $conn        	Datenbank-Connection
	// * 		$uebung_id
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function uebung($conn, $uebung_id=null, $unicode=false)
	{
		$this->conn = $conn;

		if($unicode)
			$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
		else
			$qry = "SET CLIENT_ENCODING TO 'LATIN9';";

		if(!pg_query($this->conn,$qry))
		{
			$this->errormsg	 = 'Encoding konnte nicht gesetzt werden';
			return false;
		}

		if($uebung_id!=null)
			$this->load($uebung_id);
	}

	// *********************************************************
	// * Laedt die Uebung
	// * @param uebung_id
	// *********************************************************
	function load($uebung_id)
	{
		if(!is_numeric($uebung_id))
		{
			$this->errormsg='Uebung_id muss eine gueltige Zahl sein';
			return false;
		}
		$qry = "SELECT * FROM campus.tbl_uebung WHERE uebung_id='$uebung_id'";

		if($result=pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->uebung_id = $row->uebung_id;
				$this->gewicht = $row->gewicht;
				$this->punkte = $row->punkte;
				$this->angabedatei = $row->angabedatei;
				$this->freigabevon = $row->freigabevon;
				$this->freigabebis = $row->freigabebis;
				$this->abgabe = ($row->abgabe=='t'?true:false);
				$this->beispiele = ($row->beispiele=='t'?true:false);
				$this->bezeichnung = $row->bezeichnung;
				$this->positiv = ($row->positiv=='t'?true:false);
				$this->defaultbemerkung = $row->defaultbemerkung;
				$this->lehreinheit_id = $row->lehreinheit_id;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->statistik = ($row->statistik=='t'?true:false);
				$this->liste_id = $row->liste_id;
				$this->maxbsp = $row->maxbsp;
				$this->maxstd = $row->maxstd;
				$this->nummer = $row->nummer;
				return true;
			}
			else
			{
				$this->errormsg = "Es ist keine Uebung mit der ID $uebung_id vorhanden";
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim laden der Uebung';
			return false;
		}
	}
	
	function load_studentuebung($student_uid, $uebung_id)
	{
		$qry = "SELECT * FROM campus.tbl_studentuebung WHERE student_uid='$student_uid' AND uebung_id='$uebung_id'";

		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->student_uid = $row->student_uid;
				$this->mitarbeiter_uid = $row->mitarbeiter_uid;
				$this->abgabe_id = $row->abgabe_id;
				$this->uebung_id = $row->uebung_id;
				$this->note = $row->note;
				$this->mitarbeitspunkte = $row->mitarbeitspunkte;
				$this->punkte = $row->punkte;
				$this->anmerkung = $row->anmerkung;
				$this->benotungsdatum = $row->benotungsdatum;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				return true;
			}
			else
			{
				$this->errormsg = 'Es gibt keinen passenden Eintrag';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim laden des eintrages';
			return false;
		}
	}

	function load_abgabe($abgabe_id)
	{
		$qry = "SELECT * FROM campus.tbl_abgabe WHERE abgabe_id = '$abgabe_id'";

		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->abgabe_id = $row->abgabe_id;
				$this->abgabedatei = $row->abgabedatei;
				$this->abgabezeit = $row->abgabezeit;
				$this->anmerkung = $row->anmerkung;
				return true;
			}
			else
			{
				$this->errormsg = 'Es gibt keinen passenden Eintrag';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden des Eintrages';
			return false;
		}
	}
	
	function check_studentuebung($uebung_id)
	{
		$qry = "SELECT * FROM campus.tbl_studentuebung WHERE uebung_id='$uebung_id'";

		if($result = pg_query($this->conn, $qry))	
		{
			if (pg_num_rows($result) >0)			
				return true;
			else
				return false;
		}
		else
			return false;	
	}

	
	function load_uebung($lehreinheit_id, $level=null, $uebung_id=null)
	{
		if(!is_numeric($lehreinheit_id))
		{
			$this->errormsg = 'Lehreinheit_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM campus.tbl_uebung WHERE lehreinheit_id='".$lehreinheit_id."'";
		if ($level == 1)
			$qry .= " and liste_id is null";
		if ($level == 2)
			$qry .= " and liste_id = '".$uebung_id."'";
		$qry .= " ORDER BY bezeichnung";

		if($result=pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$uebung_obj = new uebung($this->conn);

				$uebung_obj->uebung_id = $row->uebung_id;
				$uebung_obj->gewicht = $row->gewicht;
				$uebung_obj->punkte = $row->punkte;
				$uebung_obj->angabedatei = $row->angabedatei;
				$uebung_obj->freigabevon = $row->freigabevon;
				$uebung_obj->freigabebis = $row->freigabebis;
				$uebung_obj->abgabe = ($row->abgabe=='t'?true:false);
				$uebung_obj->beispiele = ($row->beispiele=='t'?true:false);
				$uebung_obj->bezeichnung = $row->bezeichnung;
				$uebung_obj->positiv = ($row->positiv=='t'?true:false);
				$uebung_obj->defaultbemerkung = $row->defaultbemerkung;
				$uebung_obj->lehreinheit_id = $row->lehreinheit_id;
				$uebung_obj->updateamum = $row->updateamum;
				$uebung_obj->updatevon = $row->updatevon;
				$uebung_obj->insertamum = $row->insertamum;
				$uebung_obj->insertvon = $row->insertvon;
				$uebung_obj->statistik = ($row->statistik=='t'?true:false);
				$uebung_obj->liste_id = $row->liste_id;
				$uebung_obj->maxstd = $row->maxstd;
				$uebung_obj->maxbsp = $row->maxbsp;
				$uebung_obj->nummer = $row->nummer;

				$this->uebungen[] = $uebung_obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim laden der Uebung';
			return false;
		}
	}

	// *******************************************
	// * Prueft die Variablen vor dem Speichern
	// * auf Gueltigkeit.
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************
	function validate()
	{
		if(!is_numeric($this->lehreinheit_id))
		{
			$this->errormsg = 'Lehreinheit_id muss eine gueltige Zahl sein';
			return false;
		}
		if(strlen($this->bezeichnung)>32)
		{
			$this->errormsg = 'Bezeichnung darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		return true;
	}

	// ************************************************
	// * wenn $var '' ist wird NULL zurueckgegeben
	// * wenn $var !='' ist werden Datenbankkritische
	// * Zeichen mit Backslash versehen und das Ergbnis
	// * unter Hochkomma gesetzt.
	// ************************************************
	function addslashes($var)
	{
		return ($var!=''?"'".addslashes($var)."'":'null');
	}

	// ************************************************************
	// * Speichert Uebung in die Datenbank
	// * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	// * angelegt, ansonsten der Datensatz upgedated
	// * @return true wenn erfolgreich, false im Fehlerfall
	// ************************************************************
	function save($new=null)
	{
		if(is_null($new))
			$new = $this->new;

		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($new)
		{
			$qry = 'BEGIN; INSERT INTO campus.tbl_uebung(gewicht, punkte, angabedatei, freigabevon, freigabebis,
			        abgabe, beispiele, bezeichnung, positiv, defaultbemerkung, lehreinheit_id, updateamum,
			        updatevon, insertamum, insertvon, liste_id, maxstd, maxbsp, nummer, statistik) VALUES('.
			        $this->addslashes($this->gewicht).','.
			        $this->addslashes($this->punkte).','.
			        $this->addslashes($this->angabedatei).','.
			        $this->addslashes($this->freigabevon).','.
			        $this->addslashes($this->freigabebis).','.
			        ($this->abgabe?'true':'false').','.
			        ($this->beispiele?'true':'false').','.
			        $this->addslashes($this->bezeichnung).','.
			        ($this->positiv?'true':'false').','.
			        $this->addslashes($this->defaultbemerkung).','.
			        $this->addslashes($this->lehreinheit_id).','.
			        $this->addslashes($this->updateamum).','.
			        $this->addslashes($this->updatevon).','.
			        $this->addslashes($this->insertamum).','.
			        $this->addslashes($this->insertvon).','.
					$this->addslashes($this->liste_id).','.
					$this->addslashes($this->maxstd).','.
					$this->addslashes($this->maxbsp).','.
					$this->addslashes($this->nummer).','.
			        ($this->statistik?'true':'false').');';
		}
		else
		{
			$qry = 'UPDATE campus.tbl_uebung SET'.
			       ' gewicht='.$this->addslashes($this->gewicht).','.
			       ' punkte='.$this->addslashes($this->punkte).','.
			       ' angabedatei='.$this->addslashes($this->angabedatei).','.
			       ' freigabevon='.$this->addslashes($this->freigabevon).','.
			       ' freigabebis='.$this->addslashes($this->freigabebis).','.
			       ' abgabe='.($this->abgabe?'true':'false').','.
			       ' beispiele='.($this->beispiele?'true':'false').','.
			       ' bezeichnung='.$this->addslashes($this->bezeichnung).','.
			       ' positiv='.($this->positiv?'true':'false').','.
			       ' defaultbemerkung='.$this->addslashes($this->defaultbemerkung).','.
			       ' lehreinheit_id='.$this->addslashes($this->lehreinheit_id).','.
			       ' updateamum='.$this->addslashes($this->updateamum).','.
			       ' updatevon='.$this->addslashes($this->updatevon).','.
				   ' liste_id='.$this->addslashes($this->liste_id).','.
				   ' maxstd='.$this->addslashes($this->maxstd).','.
				   ' maxbsp='.$this->addslashes($this->maxbsp).','.
				   ' nummer='.$this->addslashes($this->nummer).','.
			       ' statistik='.($this->statistik?'true':'false').
			       " WHERE uebung_id=".$this->addslashes($this->uebung_id).";";
		}

		if(pg_query($this->conn,$qry))
		{
			if($new)
			{
				$qry = "SELECT currval('campus.tbl_uebung_uebung_id_seq') as id;";
				if($result = pg_query($this->conn, $qry))
				{
					if($row=pg_fetch_object($result))
					{
						$this->uebung_id = $row->id;
						pg_query($this->conn, 'COMMIT');
						return true;
					}
					else
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						pg_query($this->conn,'ROLLBACK');
						return false;
					}
				}
				else
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					pg_query($this->conn,'ROLLBACK');
					return false;
				}
			}
			else
				return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Uebung:'.$qry;
			return false;
		}
	}

	// *******************************************
	// * Prueft die Variablen vor dem Speichern
	// * auf Gueltigkeit.
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************
	function validate_studentuebung()
	{
		if(!is_numeric($this->uebung_id))
		{
			$this->errormsg = 'Uebung_id muss eine gueltige Zahl sein';
			return false;
		}
		if($this->student_uid=='')
		{
			$this->errormsg = 'Student_uid muss eingetragen werden';
			return false;
		}
		return true;
	}

	// ************************************************************
	// * Speichert StudentUebung in die Datenbank
	// * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	// * angelegt, ansonsten der Datensatz upgedated
	// * @return true wenn erfolgreich, false im Fehlerfall
	// ************************************************************
	function studentuebung_save($new=null)
	{
		if(is_null($new))
			$new = $this->new;

		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate_studentuebung())
			return false;

		if($new)
		{
			$qry = 'INSERT INTO campus.tbl_studentuebung(student_uid, mitarbeiter_uid, abgabe_id, uebung_id,
					note, mitarbeitspunkte, punkte, anmerkung, benotungsdatum, updateamum,
			        updatevon, insertamum, insertvon) VALUES('.
			        $this->addslashes($this->student_uid).','.
			        $this->addslashes($this->mitarbeiter_uid).','.
			        $this->addslashes($this->abgabe_id).','.
			        $this->addslashes($this->uebung_id).','.
			        $this->addslashes($this->note).','.
			        $this->addslashes($this->mitarbeitspunkte).','.
			        $this->addslashes($this->punkte).','.
			        $this->addslashes($this->anmerkung).','.
			        $this->addslashes($this->benotungsdatum).','.
			        $this->addslashes($this->updateamum).','.
			        $this->addslashes($this->updatevon).','.
			        $this->addslashes($this->insertamum).','.
			        $this->addslashes($this->insertvon).');';
		}
		else
		{
			$qry = 'UPDATE campus.tbl_studentuebung SET'.
			       ' mitarbeiter_uid='.$this->addslashes($this->mitarbeiter_uid).','.
			       ' abgabe_id='.$this->addslashes($this->abgabe_id).','.
			       ' uebung_id='.$this->addslashes($this->uebung_id).','.
			       ' note='.$this->addslashes($this->note).','.
			       ' mitarbeitspunkte='.$this->addslashes($this->mitarbeitspunkte).','.
			       ' punkte='.$this->addslashes($this->punkte).','.
			       ' anmerkung='.$this->addslashes($this->anmerkung).','.
			       ' benotungsdatum='.$this->addslashes($this->benotungsdatum).','.
			       ' updateamum='.$this->addslashes($this->updateamum).','.
			       ' updatevon='.$this->addslashes($this->updatevon).
			       " WHERE uebung_id=".$this->addslashes($this->uebung_id)." AND student_uid=".$this->addslashes($this->student_uid).";";
		}

		if(pg_query($this->conn,$qry))
		{
				return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der StudentUebung';
			return false;
		}
	}

function abgabe_save($new=null)
	{
		if(is_null($new))
			$new = $this->new;


		if($new)
		{
			$qry = 'INSERT INTO campus.tbl_abgabe(abgabedatei, abgabezeit, anmerkung) VALUES('.
			        $this->addslashes($this->abgabedatei).','.
			        $this->addslashes($this->abgabezeit).','.
			        $this->addslashes($this->abgabe_anmerkung).');';
		}
		else
		{
			$qry = 'UPDATE campus.tbl_abgabe SET'.
			       ' abgabe_id='.$this->addslashes($this->abgabe_id).','.
			       ' abgabedatei='.$this->addslashes($this->abgabedatei).','.
			       ' abgabezeit='.$this->addslashes($this->abgabezeit).','.
			       ' anmerkung='.$this->addslashes($this->abgabe_anmerkung).
			       " WHERE abgabe_id=".$this->addslashes($this->abgabe_id).";";
		}

		if(pg_query($this->conn,$qry))
		{
			if($new)
			{
				$qry = "SELECT currval('campus.tbl_abgabe_abgabe_id_seq') as id;";
				if($result = pg_query($this->conn, $qry))
				{
					if($row=pg_fetch_object($result))
					{
						$this->abgabe_id = $row->id;
						pg_query($this->conn, 'COMMIT');
						return true;
					}
					else
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						pg_query($this->conn,'ROLLBACK');
						return false;
					}
				}
				else
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					pg_query($this->conn,'ROLLBACK');
					return false;
				}
			}
			else
				return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der StudentUebung';
			return false;
		}
	}


	// ************************************************************
	// * Loescht eine Uebung plus die abhaengigen eintraege in den
	// * Tabellen studentuebung, studentbeispiel, und beispiel
	// ************************************************************
	function delete($uebung_id)
	{
		if(!is_numeric($uebung_id))
		{
			$this->errormsg = 'Uebung_id ist ungueltig';
			return false;
		}
		
		// subübungen wegräumen
		$qry = "SELECT * FROM campus.tbl_uebung WHERE liste_id = '".$uebung_id."'";
		if($result=pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				
					foreach (glob(BENOTUNGSTOOL_PATH."angabe/*".$row->uebung_id.".*") as $angabe)
					{
							if(file_exists($angabe))
								unlink($angabe);
					}
					$qry = "DELETE FROM campus.tbl_studentbeispiel WHERE beispiel_id IN(SELECT beispiel_id FROM campus.tbl_beispiel WHERE uebung_id='$row->uebung_id');
							DELETE FROM campus.tbl_abgabe WHERE abgabe_id IN(SELECT abgabe_id FROM campus.tbl_studentuebung WHERE uebung_id='$row->uebung_id');
							DELETE FROM campus.tbl_studentuebung WHERE uebung_id='$row->uebung_id';
							DELETE FROM campus.tbl_beispiel WHERE uebung_id='$row->uebung_id';
							DELETE FROM campus.tbl_uebung WHERE uebung_id='$row->uebung_id';
							DELETE FROM campus.tbl_studentuebung WHERE uebung_id = '$row->uebung_id'";
			
					if(!pg_query($qry))
					{
						$this->errormsg = 'Fehler beim Loeschen der Daten';
						return false;
					}								
			}
		}		
		
		
		foreach (glob(BENOTUNGSTOOL_PATH."angabe/*".$uebung_id.".*") as $angabe)
		{
				if(file_exists($angabe))
					unlink($angabe);
		}
		$qry = "DELETE FROM campus.tbl_studentbeispiel WHERE beispiel_id IN(SELECT beispiel_id FROM campus.tbl_beispiel WHERE uebung_id='$uebung_id');
				DELETE FROM campus.tbl_abgabe WHERE abgabe_id IN(SELECT abgabe_id FROM campus.tbl_studentuebung WHERE uebung_id='$uebung_id');
				DELETE FROM campus.tbl_studentuebung WHERE uebung_id='$uebung_id';
				DELETE FROM campus.tbl_beispiel WHERE uebung_id='$uebung_id';
				DELETE FROM campus.tbl_uebung WHERE uebung_id='$uebung_id';
				DELETE FROM campus.tbl_studentuebung WHERE uebung_id = '$uebung_id'";

		if(pg_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Loeschen der Daten';
			return false;
		}
		
		
		
	}

	// ************************************************************
	// * Loescht eine Uebung plus die abhaengigen eintraege in den
	// * Tabellen studentuebung, studentbeispiel, und beispiel
	// ************************************************************
	function delete_abgabe($abgabe_id)
	{
		if(!is_numeric($abgabe_id))
		{
			$this->errormsg = 'abgabe_id ist ungueltig';
			return false;
		}
		
		// subübungen wegräumen
		$qry = "SELECT * FROM campus.tbl_abgabe WHERE abgabe_id = '".$abgabe_id."'";
		if($result=pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				
														
					if(file_exists(BENOTUNGSTOOL_PATH."abgabe/".$row->abgabedatei))
						unlink(BENOTUNGSTOOL_PATH."abgabe/".$row->abgabedatei);
					$qry = "UPDATE campus.tbl_studentuebung set abgabe_id = null where abgabe_id = '$abgabe_id';
							DELETE FROM campus.tbl_abgabe WHERE abgabe_id = '$abgabe_id'";
			
					if(!pg_query($qry))
					{
						$this->errormsg = 'Fehler beim Loeschen der Daten';
						return false;
					}
					else
						return true;							
			}
		}
		
	}

}
?>