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

class pruefling extends basis_db
{
	//Tabellenspalten
	public $pruefling_id;
	public $studiengang_kz;
	public $idnachweis;
	public $registriert;
	public $prestudent_id;
	public $semester;
	
	// ErgebnisArray
	public $result=array();
	public $num_rows=0;
	public $new;
		
	/**
	 * Konstruktor - Laedt optional einen pruefling
	 * @param $frage_id       Frage die geladen werden soll (default=null)
	 */
	public function __construct($pruefling_id=null)
	{
		parent::__construct();

		if($pruefling_id != null)
			$this->load($pruefling_id);
	}
	
	/**
	 * Laedt Pruefling mit der uebergebenen ID
	 * @param $pruefling_id ID der Frage die geladen werden soll
	 */
	public function load($pruefling_id)
	{
		$qry = "SELECT * FROM testtool.tbl_pruefling WHERE pruefling_id='".addslashes($pruefling_id)."'";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->pruefling_id = $row->pruefling_id;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->idnachweis = $row->idnachweis;
				$this->registriert = $row->registriert;
				$this->prestudent_id = $row->prestudent_id;
				$this->semester = $row->semester;
				return true;
			}
			else 
			{
				$this->errormsg = "Kein Eintrag gefunden fuer $pruefling_id";
				return false;
			}				
		}
		else 
		{
			$this->errormsg = "Fehler beim Laden: $qry";
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
	 * Speichert die Benutzerdaten in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * ansonsten der Datensatz mit $uid upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save()
	{
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;
		
		if($this->new) //Wenn new true ist dann ein INSERT absetzen ansonsten ein UPDATE
		{
			$qry = 'BEGIN;INSERT INTO testtool.tbl_pruefling (studiengang_kz, idnachweis, registriert, prestudent_id, semester) VALUES('.
			       $this->addslashes($this->studiengang_kz).",".
			       $this->addslashes($this->idnachweis).",".
			       $this->addslashes($this->registriert).",".
			       $this->addslashes($this->prestudent_id).",".
			       $this->addslashes($this->semester).");";
		}
		else
		{			
			$qry = 'UPDATE testtool.tbl_pruefling SET'.
			       ' studiengang_kz='.$this->addslashes($this->studiengang_kz).','.
			       ' idnachweis='.$this->addslashes($this->idnachweis).','.
			       ' registriert='.$this->addslashes($this->registriert).','.
			       ' semester='.$this->addslashes($this->semester).','.
			       ' prestudent_id='.$this->addslashes($this->prestudent_id).
			       " WHERE pruefling_id='".addslashes($this->pruefling_id)."';";
		}
		
		if($this->db_query($qry))
		{
			if($this->new)
			{
				$qry = "SELECT currval('testtool.tbl_pruefling_pruefling_id_seq') as id";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->pruefling_id = $row->id;
						$this->db_query('COMMIT;');
						return true;
					}
					else 
					{
						$this->db_query('ROLLBACK;');
						$this->errormsg = 'Fehler beim Lesen der Sequence';
						return false;
					}
				}
				else
				{
					$this->db_query('ROLLBACK;');
					$this->errormsg = 'Fehler beim Lesen der Sequence';
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
			$this->db_query('ROLLBACK');
			$this->errormsg = 'Fehler beim Speichern der Frage:'.$qry;
			return false;
		}
	}
	
	/**
	 * Laedt einen Puefling anhand der Prestudent_id
	 *
	 * @param $prestudent_id
	 * @return boolean
	 */
	public function getPruefling($prestudent_id)
	{
		$qry = "SELECT * FROM testtool.tbl_pruefling WHERE prestudent_id='".addslashes($prestudent_id)."'";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->pruefling_id = $row->pruefling_id;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->idnachweis = $row->idnachweis;
				$this->registriert = $row->registriert;
				$this->prestudent_id = $row->prestudent_id;
				$this->semester = $row->semester;
				return true;
			}
			else 
			{
				$this->errormsg = "Kein Eintrag gefunden fuer $prestudent_id";
				return false;
			}				
		}
		else 
		{
			$this->errormsg = "Fehler beim Laden: $qry";
			return false;
		}		
	}
	
	/**
	 * Ermittelt den aktuellen Level (schwierigkeitsgrad der Frage) 
	 * des Prueflings fuer das uebergebene Gebiet
	 *
	 * @param $pruefling_id
	 * @param $gebiet_id
	 */
	public function getPrueflingLevel($pruefling_id, $gebiet_id)
	{
		$gebiet = new gebiet($gebiet_id);
				
		//wenn Levelsystem fuer dieses Gebiet aktiviert ist
		if($gebiet->level_start!='')
		{
			//Maximal und Minimal Level fuer dieses Gebiet ermitteln
			$max_level = 0;
			$min_level = 0;
					
			$qry = "SELECT max(level) as max, min(level) as min FROM testtool.tbl_frage 
					WHERE gebiet_id='".addslashes($gebiet_id)."'";
			
			if($this->db_query($qry))
			{
				if($row = $this->db_fetch_object())
				{
					$max_level = $row->max;
					$min_level = $row->min;
				}
				else 
				{
					$this->errormsg = 'unbekannter Fehler in getPrueflingLevel';
					return false;
				}
			}
			else 
			{
				$this->errormsg = 'Fehler beim Ermitteln des Pruefling-Levels';
				return false;
			}
			
			//alle bisherigen Antworten fuer dieses Gebiet holen
			$qry = "SELECT
						tbl_vorschlag.punkte
					FROM 
						testtool.tbl_pruefling_frage 
						JOIN testtool.tbl_vorschlag USING(frage_id) 
						JOIN testtool.tbl_antwort USING(vorschlag_id)
						JOIN testtool.tbl_frage USING(frage_id)
					WHERE
						tbl_frage.gebiet_id='".addslashes($gebiet_id)."' AND
						tbl_pruefling_frage.pruefling_id='".addslashes($pruefling_id)."' AND
						tbl_antwort.pruefling_id = tbl_pruefling_frage.pruefling_id
					ORDER BY tbl_pruefling_frage.nummer ASC";
			
			$aktueller_level=$gebiet->level_start;
			$anzahl_richtig=0;
			$anzahl_falsch=0;
			if($this->db_query($qry))
			{
				while($row = $this->db_fetch_object())
				{
					if($row->punkte>0)
					{
						//wenn die Frage richtig beantwortet wurde dann richtig-zaehler erhoehen
						$anzahl_richtig++;
						$anzahl_falsch=0;
					}
					else 
					{
						//wenn die Frage falsch beantwortet wurde dann falsch-zaehler erhoehen
						$anzahl_richtig=0;
						$anzahl_falsch++;
					}
					
					//wenn einer der Zaehler das Sprunglevel erreicht hat, dann
					//in ein anderes Level springen
					if($anzahl_richtig==$gebiet->level_sprung_auf)
					{
						$aktueller_level++;
						$anzahl_richtig=0;
						$anzahl_falsch=0;
					}
					elseif($anzahl_falsch==$gebiet->level_sprung_ab)
					{
						$aktueller_level--;
						$anzahl_richtig=0;
						$anzahl_falsch=0;
					}
					
					//aktueller level darf nicht kleiner/groesser als der minimal/maximal Level sein
					if($aktueller_level<$min_level)
						$aktueller_level=$min_level;
					if($aktueller_level>$max_level)
						$aktueller_level=$max_level;
				}
				
				return $aktueller_level;
			}
			else 
			{
				$this->errormsg = 'Fehler bei einer Abfrage';
				return false;
			}
		}
		else 
			return -1;
	}
	
	/**
	 * Berechnet das Reihungstestergebnis fuer einen Prestudenten
	 *
	 * @param $prestudent_id
	 * @return Endpunkte des Reihungstests
	 */
	public function getReihungstestErgebnis($prestudent_id)
	{
		$qry = "SELECT * FROM testtool.vw_auswertung 
				WHERE prestudent_id='".addslashes($prestudent_id)."'";
		
		$ergebnis=0;
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				//wenn maxpunkte ueberschritten wurde -> 100%
				if($row->punkte>=$row->maxpunkte)
					$prozent=100;
				else
					$prozent = ($row->punkte/$row->maxpunkte)*100;
						
				$ergebnis+=$prozent*$row->gewicht;		
			}
			return $ergebnis;
		}
		else 
		{
			$this->errormsg = 'Fehler bei einer Abfrage';
			return false;
		}
	}
}
?>
