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

class vorschlag
{
	//Tabellenspalten
	var $vorschlag_id;
	var $frage_id;
	var $nummer;
	var $punkte;
	
	var $text;
	var $bild;
	var $audio;
	
	var $insertamum;
	var $insertvon;
	var $updateamum;
	var $updatevon;
	
	// ErgebnisArray
	var $result=array();
	var $num_rows=0;
	var $errormsg;
	var $new;

	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional einen vorschlag
	// * @param $conn        	Datenbank-Connection
	// *        $frage_id       Frage die geladen werden soll (default=null)
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function vorschlag($conn, $vorschlag_id=null, $unicode=false)
	{
		$this->conn = $conn;

		if($unicode)
			$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
		else
			$qry = "SET CLIENT_ENCODING TO 'LATIN9';";

		if(!pg_query($conn,$qry))
		{
			$this->errormsg	 = 'Encoding konnte nicht gesetzt werden';
			return false;
		}

		if($vorschlag_id != null)
			$this->load($vorschlag_id);
	}

	// ***********************************************************
	// * Laedt Vorschlag mit der uebergebenen ID
	// * @param $vorschlag_id ID des Vorschlages der geladen werden soll
	// ***********************************************************
	function load($vorschlag_id, $sprache='German')
	{
		$qry = "SELECT * FROM testtool.tbl_vorschlag WHERE vorschlag_id='".addslashes($vorschlag_id)."'";

		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->vorschlag_id = $row->vorschlag_id;
				$this->frage_id = $row->frage_id;
				$this->punkte = $row->punkte;
				$this->nummer = $row->nummer;
				$this->loadVorschlagSprache($vorschlag_id, $sprache);
				return true;
			}
			else
			{
				$this->errormsg = "Kein Eintrag gefunden fuer $vorschlag_id $sprache";
				return false;
			}
		}
		else
		{
			$this->errormsg = "Fehler beim Laden: $qry";
			return false;
		}
	}
	
	function loadVorschlagSprache($vorschlag_id, $sprache)
	{
		$qry = "SELECT * FROM testtool.tbl_vorschlag_sprache 
						WHERE vorschlag_id='".addslashes($vorschlag_id)."' AND sprache='".addslashes($sprache)."'";
		if($result_sprache = pg_query($this->conn, $qry))
		{
			if($row_sprache = pg_fetch_object($result_sprache))
			{				
				$this->text = $row_sprache->text;
				$this->bild = $row_sprache->bild;
				$this->audio = $row_sprache->audio;
			}
		}
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

	// *******************************************
	// * Prueft die Variablen vor dem Speichern
	// * auf Gueltigkeit.
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************
	function validate()
	{
		return true;
	}

	// ******************************************************************
	// * Speichert die Benutzerdaten in die Datenbank
	// * Wenn $new auf true gesetzt ist wird ein neuer Datensatz angelegt
	// * ansonsten der Datensatz mit $uid upgedated
	// * @return true wenn erfolgreich, false im Fehlerfall
	// ******************************************************************
	function save()
	{
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($this->new) //Wenn new true ist dann ein INSERT absetzen ansonsten ein UPDATE
		{
			$qry = 'BEGIN;INSERT INTO testtool.tbl_vorschlag (frage_id, nummer, punkte, insertamum, insertvon, updateamum, updatevon) VALUES('.
			       $this->addslashes($this->frage_id).','.
			       $this->addslashes($this->nummer).','.
				   $this->addslashes($this->punkte).','.
				   $this->addslashes($this->insertamum).','.
				   $this->addslashes($this->insertvon).','.
				   $this->addslashes($this->updateamum).','.
				   $this->addslashes($this->updatevon).');';
		}
		else
		{
			$qry = 'UPDATE testtool.tbl_vorschlag SET'.
			       ' frage_id='.$this->addslashes($this->frage_id).','.
			       ' nummer='.$this->addslashes($this->nummer).','.
			       ' punkte='.$this->addslashes($this->punkte).','.
			       ' updateamum='.$this->addslashes($this->updateamum).','.
			       ' updatevon='.$this->addslashes($this->updatevon).
					" WHERE vorschlag_id='".addslashes($this->vorschlag_id)."';";
		}

		if(pg_query($this->conn,$qry))
		{
			if($this->new)
			{
				$qry = "SELECT currval('testtool.tbl_vorschlag_vorschlag_id_seq') as id";
				if($result = pg_query($this->conn, $qry))
				{
					if($row = pg_fetch_object($result))
					{
						$this->vorschlag_id = $row->id;
						pg_query($this->conn, 'COMMIT;');
						return true;
					}
					else 
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						pg_query($this->conn, 'ROLLBACK');
						return false;
					}
				}
				else 
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					pg_query($this->conn, 'ROLLBACK');
					return false;
				}
			}
			else 
			{
				return true;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Frage:'.$qry;
			return false;
		}
	}

	/**
	 * Pueft die Daten vor dem Speichern
	 *
	 * @return true wenn ok, false wenn Fehler
	 */
	function validate_vorschlagsprache()
	{
		return true;	
	}
	
	/**
	 * Speichert einen Eintrag in tbl_vorschlag_sprache
	 *
	 * @return true wenn ok, false wenn Fehler
	 */
	function save_vorschlagsprache()
	{
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate_vorschlagsprache())
			return false;

		$qry = "SELECT * FROM testtool.tbl_vorschlag_sprache 
				WHERE vorschlag_id='".addslashes($this->vorschlag_id)."' AND
				sprache='".addslashes($this->sprache)."'";
		if($result = pg_query($this->conn, $qry))
		{
			if(pg_num_rows($result)>0)
				$this->new=false;
			else 
				$this->new=true;
		}
		
		if($this->new) //Wenn new true ist dann ein INSERT absetzen ansonsten ein UPDATE
		{
			$qry = 'INSERT INTO testtool.tbl_vorschlag_sprache (vorschlag_id, sprache, text, bild, audio, 
					insertamum, insertvon, updateamum, updatevon) VALUES('.
			       $this->addslashes($this->vorschlag_id).','.
			       $this->addslashes($this->sprache).','.
				   $this->addslashes($this->text).','.
				   $this->addslashes($this->bild).','.
				   $this->addslashes($this->audio).','.
				   $this->addslashes($this->insertamum).','.
				   $this->addslashes($this->insertvon).','.
				   $this->addslashes($this->updateamum).','.
				   $this->addslashes($this->updatevon).');';
		}
		else
		{
			$qry = 'UPDATE testtool.tbl_vorschlag_sprache SET'.
			       ' text='.$this->addslashes($this->text).',';
			if($this->bild!='')
				$qry.=' bild='.$this->addslashes($this->bild).',';
			if($this->audio!='')
				$qry.=' audio='.$this->addslashes($this->audio).',';
			
			$qry.= ' updateamum='.$this->addslashes($this->updateamum).','.
			       ' updatevon='.$this->addslashes($this->updatevon).
					" WHERE vorschlag_id='".addslashes($this->vorschlag_id)."' AND sprache='".addslashes($this->sprache)."';";
		}
		
		if($result = pg_query($this->conn, $qry))
		{
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}

	function getVorschlag($frage_id, $sprache, $random)
	{
		$qry = "SELECT * FROM testtool.tbl_vorschlag WHERE frage_id='".addslashes($frage_id)."'";
		if($random)
			$qry.=" ORDER BY random()";
		else 
			$qry.=" ORDER BY nummer";

		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$vs = new vorschlag($this->conn);
				$vs->vorschlag_id = $row->vorschlag_id;
				$vs->frage_id = $row->frage_id;
				$vs->nummer = $row->nummer;
				$vs->punkte = $row->punkte;
			
				$qry = "SELECT * FROM testtool.tbl_vorschlag_sprache 
						WHERE vorschlag_id='".addslashes($row->vorschlag_id)."' AND sprache='".addslashes($sprache)."'";
				if($result_sprache = pg_query($this->conn, $qry))
				{
					if($row_sprache = pg_fetch_object($result_sprache))
					{				
						$vs->text = $row_sprache->text;
						$vs->bild = $row_sprache->bild;
						$vs->audio = $row_sprache->audio;
					}
				}

				$this->result[] = $vs;

			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim laden der Daten';
			return false;
		}
	}
	
	function delete($vorschlag_id)
	{
		$qry = "DELETE FROM testtool.tbl_vorschlag WHERE vorschlag_id='".addslashes($vorschlag_id)."'";
		if(pg_query($this->conn, $qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim loeschen';
			return false;
		}
	}
}
?>
