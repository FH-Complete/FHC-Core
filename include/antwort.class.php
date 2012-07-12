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
require_once(dirname(__FILE__).'/basis_db.class.php');

class antwort extends basis_db
{
	//Tabellenspalten
	public $antwort_id;
	public $pruefling_id;
	public $vorschlag_id;
		
	// ErgebnisArray
	public $result=array();
	public $num_rows=0;
	public $new;
		
	/**
	 * Konstruktor - Uebergibt die Connection und laedt optional eine Antwort
	 * @param $frage_id       Frage die geladen werden soll (default=null)
	 */
	public function __construct($antwort_id=null)
	{
		parent::__construct();
		
		if(!is_null($antwort_id))
			$this->load($antwort_id);
	}
	
	/**
	 * Laedt Antwort mit der uebergebenen ID
	 * @param $antwort_id ID der Frage die geladen werden soll
	 */
	public function load($antwort_id)
	{
		$qry = "SELECT * FROM testtool.tbl_antwort WHERE antwort_id=".$this->db_add_param($antwort_id, FHC_INTEGER);
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->antwort_id = $row->antwort_id;
				$this->pruefling_id = $row->pruefling_id;
				$this->vorschlag_id = $row->vorschlag_id;
				return true;
			}
			else 
			{
				$this->errormsg = 'Kein Eintrag gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = "Fehler beim Laden der Antwort";
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
		return true;
	}
	
	/**
	 * Speichert die Daten in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * ansonsten upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save()
	{
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;
		
		if($this->new) //Wenn new true ist dann ein INSERT absetzen ansonsten ein UPDATE
		{
			$qry = 'INSERT INTO testtool.tbl_antwort (pruefling_id, vorschlag_id) VALUES('.
			       $this->db_add_param($this->pruefling_id, FHC_INTEGER).",".
			       $this->db_add_param($this->vorschlag_id, FHC_INTEGER).");";
		}
		else
		{			
			$qry = 'UPDATE testtool.tbl_antwort SET'.
			       ' vorschlag_id='.$this->db_add_param($this->vorschlag_id, FHC_INTEGER).','.
			       ' pruefling_id='.$this->db_add_param($this->pruefling_id, FHC_INTEGER).','.
			       " WHERE antwort_id=".$this->db_add_param($this->antwort_id, FHC_INTEGER,false);
		}
		
		if($this->db_query($qry))
		{
			//Log schreiben
			return true;
		}
		else 
		{	
			$this->errormsg = 'Fehler beim Speichern der Antwort';
			return false;
		}
	}
	
	/**
	 * Loescht einen Eintrag aus der Tabelle tbl_antwort
	 *
	 * @param $antwort_id
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($antwort_id)
	{
		if(!is_numeric($antwort_id) || $antwort_id=='')
		{
			$this->errormsg = 'Antwort_id ist ungueltig';
			return false;
		}
		
		$qry = "DELETE FROM testtool.tbl_antwort WHERE antwort_id=".$this->db_add_param($antwort_id, FHC_INTEGER, false);
		if($this->db_query($qry))
		{
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Löschen der Antwort';
			return false;
		}
	}
	
	/**
	 * Liefert die Antwort eines Pruefling zu einer Frage
	 *
	 * @param $pruefling_id
	 * @param $frage_id
	 * @return boolean
	 */
	public function getAntwort($pruefling_id, $frage_id)
	{
		$qry = "SELECT * FROM testtool.tbl_antwort JOIN testtool.tbl_pruefling_frage USING(pruefling_id) 
				JOIN testtool.tbl_vorschlag USING(vorschlag_id) 
				WHERE 
					tbl_vorschlag.frage_id=tbl_pruefling_frage.frage_id AND 
					pruefling_id=".$this->db_add_param($pruefling_id, FHC_INTEGER)." AND 
					tbl_vorschlag.frage_id=".$this->db_add_param($frage_id, FHC_INTEGER, false);
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new antwort();
				
				$obj->antwort_id = $row->antwort_id;
				$obj->frage_id = $row->frage_id;
				$obj->vorschlag_id = $row->vorschlag_id;
				$obj->begintime = $row->begintime;
				$obj->endtime = $row->endtime;
				$obj->pruefling_id = $row->pruefling_id;
				
				$this->result[] = $obj;
			}
			
			return true;
		}
		else 
		{
			$this->errormsg = 'Antwort konnte nicht geladen werden';
			return false;
		}
	}
}
?>
