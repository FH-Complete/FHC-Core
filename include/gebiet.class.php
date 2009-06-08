<?php
/* Copyright (C) 2009 Technikum-Wien
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *			Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>
 */
/**
 * Klasse fuer die Gebiete des Testtools (Reihungstesttool)
 *
 */

class gebiet
{
	//Tabellenspalten
	public $gebiet_id;
	public $kurzbz;
	public $bezeichnung;
	public $beschreibung;
	public $zeit;
	public $multipleresponse;
	public $kategorien;
	public $maxfragen;
	public $zufallfrage;
	public $zufallvorschlag;
	public $level_start;
	public $level_sprung_auf;
	public $level_sprung_ab;
	public $levelgleichverteilung;
	public $maxpunkte;
	public $insertamum;
	public $insertvon;
	public $updateamum;
	public $updatevon;
		
	// ErgebnisArray
	public $result=array();
	public $errormsg;
	public $new;

	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional ein Gebiet
	// * @param $conn        	Datenbank-Connection
	// *        $gebiet_id      Gebiet das geladen werden soll (default=null)
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	public function gebiet($conn, $gebiet_id=null, $unicode=false)
	{
		$this->conn = $conn;
/*
		if($unicode)
			$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
		else
			$qry = "SET CLIENT_ENCODING TO 'LATIN9';";

		if(!pg_query($conn,$qry))
		{
			$this->errormsg	 = 'Encoding konnte nicht gesetzt werden';
			return false;
		}
*/
		if($gebiet_id != null)
			$this->load($gebiet_id);
	}

	// ***********************************************************
	// * Laedt Gebiet mit der uebergebenen ID
	// * @param $gebiet_id ID des Gebiets das geladen werden soll
	// ***********************************************************
	public function load($gebiet_id)
	{
		$qry = "SELECT * FROM testtool.tbl_gebiet WHERE gebiet_id='".addslashes($gebiet_id)."'";

		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->gebiet_id = $row->gebiet_id;
				$this->kurzbz = $row->kurzbz;
				$this->bezeichnung = $row->bezeichnung;
				$this->beschreibung = $row->beschreibung;
				$this->zeit = $row->zeit;
				$this->multipleresponse = ($row->multipleresponse=='t'?true:false);
				$this->kategorien = ($row->kategorien=='t'?true:false);
				$this->maxfragen = $row->maxfragen;
				$this->zufallfrage = ($row->zufallfrage=='t'?true:false);
				$this->zufallvorschlag = ($row->zufallvorschlag=='t'?true:false);
				$this->level_start = $row->level_start;
				$this->level_sprung_auf = $row->level_sprung_auf;
				$this->level_sprung_ab = $row->level_sprung_ab;
				$this->levelgleichverteilung = ($row->levelgleichverteilung=='t'?true:($row->levelgleichverteilung=='f'?false:null));
				$this->maxpunkte = $row->maxpunkte;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->antwortenprozeile = $row->antwortenprozeile;
				
				return true;
			}
			else
			{
				$this->errormsg = "Kein Eintrag gefunden fuer $gebiet_id";
				return false;
			}
		}
		else
		{
			$this->errormsg = "Fehler beim Laden des Gebiets";
			return false;
		}
	}

	// ************************************************
	// * wenn $var '' ist wird NULL zurueckgegeben
	// * wenn $var !='' ist werden Datenbankkritische
	// * Zeichen mit Backslash versehen und das Ergbnis
	// * unter Hochkomma gesetzt.
	// ************************************************
	private function addslashes($var)
	{
		return ($var!=''?"'".addslashes($var)."'":'null');
	}

	// *******************************************
	// * Prueft die Variablen vor dem Speichern
	// * auf Gueltigkeit.
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************
	private function validate()
	{
		if(strlen($this->kurzbz)>10)
		{
			$this->errormsg = 'Kurzbz darf nicht laenger als 10 Zeichen sein';
			return false;
		}
		if(strlen($this->bezeichnung)>50)
		{
			$this->errormsg = 'Bezeichnung darf nicht laenger als 50 Zeichen sein';
			return false;
		}
		if(!is_bool($this->multipleresponse))
		{
			$this->errormsg = 'Multipleresponse muss ein boolscher Wert sein';
			return false;
		}
		if(!is_bool($this->kategorien))
		{
			$this->errormsg = 'Kategorien muss ein boolscher Wert sein';
			return false;
		}
		if(!is_numeric($this->maxfragen) && $this->maxfragen!='')
		{
			$this->errormsg = 'maxfragen muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_bool($this->zufallfrage))
		{
			$this->errormsg = 'Zufallfrage muss ein boolscher Wert sein';
			return false;
		}
		if(!is_bool($this->zufallvorschlag))
		{
			$this->errormsg = 'Zufallvorschlag muss ein boolscher Wert sein';
			return false;
		}
		if(!is_bool($this->levelgleichverteilung) && !is_null($this->levelgleichverteilung))
		{
			$this->errormsg = 'Levelgleichverteilung ist ungueltig';
			return false;
		}
		if(!is_numeric($this->maxpunkte) && $this->maxpunkte!='')
		{
			$this->errormsg = 'Maxpunkte muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_numeric($this->antwortenprozeile) || $this->antwortenprozeile<=0)
		{
			$this->errormsg = 'AntortenProZeile muss eine gueltige Zahl und groesser als 0 sein';
		}

		return true;
	}

	// ******************************************************************
	// * Speichert das Gebiet in die Datenbank
	// * Wenn $new auf true gesetzt ist wird ein neuer Datensatz angelegt
	// * ansonsten der Datensatz aktualisiert
	// * @return true wenn erfolgreich, false im Fehlerfall
	// ******************************************************************
	public function save($new=null)
	{
		if(is_null($new))
			$new = $this->new;
		
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;
			
		if($new)
		{
			$qry = 'BEGIN;INSERT INTO testtool.tbl_gebiet (kurzbz, bezeichnung, beschreibung, zeit, multipleresponse, 
					kategorien, maxfragen, zufallfrage, zufallvorschlag, level_start, level_sprung_auf, level_sprung_ab, 
					levelgleichverteilung, maxpunkte, antwortenprozeile, insertamum, insertvon , updateamum, updatevon) VALUES('.
			       $this->addslashes($this->kurzbz).",".
			       $this->addslashes($this->bezeichnung).",'".
			       $this->addslashes($this->beschreibung).",'".
			       $this->addslashes($this->zeit).",".
			       ($this->multipleresponse?'true':'false').",".
			       $this->addslashes($this->kategorien).",".
			       $this->addslashes($this->maxfragen).",".
			       ($this->zufallfrage?'true':'false').",'".
			       ($this->zufallvorschlag?'true':'false').",'".
			       $this->addslashes($this->level_start).",".
			       $this->addslashes($this->level_sprung_auf).",".
			       $this->addslashes($this->level_sprung_ab).",".
			       ($this->levelgleichverteilung?'true':($this->levelgleichverteilung==false?'false':'null')).",".
			       $this->addslashes($this->maxpunkte).",".
			       $this->addslashes($this->antwortenprozeile).",".
			       $this->addslashes($this->insertamum).",".
			       $this->addslashes($this->insertvon).
			       ",null, null);";
		}
		else
		{
			$qry = 'UPDATE testtool.tbl_gebiet SET'.
			       ' kurzbz='.$this->addslashes($this->kurzbz).','.
			       ' bezeichnung='.$this->addslashes($this->bezeichnung).','.
			       ' beschreibung='.$this->addslashes($this->beschreibung).','.
			       ' zeit='.$this->addslashes($this->zeit).','.
			       ' multipleresponse='.($this->multipleresponse?'true':'false').','.
			       ' kategorien='.($this->kategorien?'true':'false').','.
			       ' maxfragen='.$this->addslashes($this->maxfragen).','.
			       ' zufallfrage='.($this->zufallfrage?'true':'false').','.
			       ' zufallvorschlag='.($this->zufallvorschlag?'true':'false').','.
			       ' level_start='.$this->addslashes($this->level_start).','.
			       ' level_sprung_auf='.$this->addslashes($this->level_sprung_auf).','.
			       ' level_sprung_ab='.$this->addslashes($this->level_sprung_ab).','.
			       ' levelgleichverteilung='.($this->levelgleichverteilung?'true':($this->levelgleichverteilung==false?'false':'null')).','.
			       ' maxpunkte='.$this->addslashes($this->maxpunkte).','.
			       ' antwortenprozeile='.$this->addslashes($this->antwortenprozeile).','.
			       ' updateamum='.$this->addslashes($this->updateamum).','.
			       ' updatevon='.$this->addslashes($this->updatevon).
			       " WHERE gebiet_id='".addslashes($this->gebiet_id)."';";
		}
		
		if(pg_query($this->conn,$qry))
		{
			//aktuelle ID aus der Sequence holen
			if($new)
			{
				$qry='SELECT currval("testtool.tbl_gebiet_gebiet_id_seq") as id;';
				if($result = pg_query($this->conn, $qry))
				{
					if($row = pg_fetch_object($result))
					{
						$this->gebiet_id = $row->id;
						pg_query($this->conn, 'COMMIT');
						return true;
					}
					else 
					{
						$this->errormsg = 'Fehler beim Lesen der Sequence';
						pg_query($this->conn, 'ROLLBACK;');
						return false;
					}
				}
				else 
				{
					$this->errormsg = 'Fehler beim Lesen der Sequence';
					pg_query($this->conn, 'ROLLBACK');
					return false;
				}
			}
			else
				return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Frage:'.$qry;
			return false;
		}
	}
	
	/**
	 * prueft das Gebiet auf Gueltigkeit
	 * (diverse Plausichecks)
	 *
	 * @param $gebiet_id
	 */
	function check_gebiet($gebiet_id)
	{
		$this->errormsg = '';
		$this->load($gebiet_id);
	
		//wenn levels verwendet werden muss maxfragen gesetzt sein
		if($this->level_start!='' && $this->maxfragen=='')
		{
			$this->errormsg .= "Wenn Levels verwendet werden, muss die maximale Fragenanzahl gesetzt sein.\n";
		}
		
		//Levelgleichverteilung gibt es nur bei nicht geleveltem ablauf
		if($this->level_start!='' && $this->levelgleichverteilung)
		{
			$this->errormsg .= "Levelgleichverteilung ist nur dann erlaubt, wenn der Ablauf nicht gelevelt ist.\n";
		}
		
		//Von jedem level muessen mindestens maxfragen vorhanden sein wenn levels aktiv ist
		if($this->level_start!='')
		{
			$qry = "SELECT count(*) as anzahl, level FROM testtool.tbl_frage WHERE gebiet_id='".addslashes($gebiet_id)."' GROUP BY level";
			if($result = pg_query($this->conn, $qry))
			{
				while($row = pg_fetch_object($result))
				{
					if($row->anzahl<$this->maxfragen)
					{
						$this->errormsg .= "Es gibt nur $row->anzahl Fragen fuer das Level $row->level. Es muessen aber mindestens $this->maxfragen angelegt werden.\n";
					}
				}
			}
		}
		
		//Pruefen ob jede Fragen mindestens 2 Vorschlaege hat
		$qry = "SELECT frage_id, nummer FROM testtool.tbl_frage 
				WHERE (SELECT count(*) as anzahl FROM testtool.tbl_vorschlag WHERE frage_id=tbl_frage.frage_id)<2
				AND gebiet_id='".addslashes($gebiet_id)."' AND NOT demo;";
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$this->errormsg .= "Frage Nummer $row->nummer (ID: $row->frage_id) hat weniger als 2 Vorschlaege.\n";
			}
		}

		//Wenn Levels verwendet werden, muessen mindestens 2 Verschiedene Level vorhanden sein
		if($this->level_start!='')
		{
			$qry = "SELECT level FROM testtool.tbl_frage WHERE gebiet_id='".addslashes($gebiet_id)."' AND level is not null GROUP by level";
			if($result = pg_query($this->conn, $qry))
			{
				if(pg_num_rows($result)<2)
				{
					$this->errormsg .= "Wenn Levels verwendet werden, muessen mindestens 2 verschiedene Level vorhanden sein.\n";
				}
			}
		}
		
		// Wenn Levelgleichverteilung true ist, muss maxfragen mindestens so gross wie die Anzahl der level sein
		if($this->levelgleichverteilung)
		{
			if($this->maxfragen!='' && $this->maxfragen!=0)
			{
				$qry = "SELECT count(*) as anzahl FROM testtool.tbl_frage WHERE gebiet_id='".addslashes($gebiet_id)."' AND not demo AND level is not null GROUP BY level";
				if($result = pg_query($this->conn, $qry))
				{
					if($row = pg_fetch_object($result))
					{
						if($row->anzahl>$this->maxfragen)
						{
							$this->errormsg .= "Wenn Levelgleichverteilung gesetzt ist, muss maxfragen groesser als die Anzahl der verwendeten Levels sein\n";
						}						
					}
				}
			}
		}
			
		// Wenn zufallsfrage=true und level_start!='' oder levelgleichverteilung 
		// dann darf die punkteanzahl pro level/Frage sich nicht unterscheiden
		if($this->zufallfrage && ($this->level_start!='' || $this->levelgleichverteilung))
		{
			$qry = "SELECT * FROM (
						SELECT level, count(*) as anzahl FROM (
							SELECT level, punkte, count(*) as anzahl FROM (
								SELECT level, sum(punkte) as punkte
								FROM testtool.tbl_frage JOIN testtool.tbl_vorschlag USING(frage_id) 
								WHERE punkte>0 AND not demo AND gebiet_id='".addslashes($gebiet_id)."'
								GROUP BY frage_id, level) as a 
							GROUP BY level, punkte ) as b 
						GROUP BY level) as c
					WHERE c.anzahl>1";
			if($result = pg_query($this->conn, $qry))
			{
				while($row = pg_fetch_object($result))
				{
					$this->errormsg .= "Pro Level/Frage darf die positive Punkteanzahl nicht unterschiedlich sein wenn Zufallsfragen und Levels/Levelgleichverteilung verwendet wird. (Unterschiede in Level $row->level)\n";
				}
			}
		}
		
		// kein Multipleresponse bei gelevelten Gebieten
		if($this->level_start!='' && $this->level_start!=0 && $this->multipleresponse)
		{
			$this->errormsg .= "Bei gelevelten Gebieten ist Multipleresponse nicht erlaubt\n";
		}
		
		// maxpunkte muss eingetragen sein
		if($this->maxpunkte=='' || $this->maxpunkte==0)
		{
			$this->errormsg = "Es wurden keine Maximalpunkte fuer dieses Gebiet eingetragen\n";
		}
		
		if($this->errormsg=='')
			return true;
		else 
			return false;
	}
	
	/**
	 * Holt alle Gebiete aus der DB
	 *
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getAll()
	{
		$qry = 'SELECT * FROM testtool.tbl_gebiet ORDER BY bezeichnung';
		
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$obj = new gebiet($this->conn, null, null);
				
				$obj->gebiet_id = $row->gebiet_id;
				$obj->kurzbz = $row->kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->beschreibung = $row->beschreibung;
				$obj->zeit = $row->zeit;
				$obj->multipleresponse = ($row->multipleresponse=='t'?true:false);
				$obj->kategorien = ($row->kategorien=='t'?true:false);
				$obj->maxfragen = $row->maxfragen;
				$obj->zufallfrage = ($row->zufallfrage=='t'?true:false);
				$obj->zufallvorschlag = ($row->zufallvorschlag=='t'?true:false);
				$obj->levelgleichverteilung = ($row->levelgleichverteilung=='t'?true:false);
				$obj->maxpunkte = $row->maxpunkte;
				$obj->level_start = $row->level_start;
				$obj->level_sprung_ab = $row->level_sprung_ab;
				$obj->level_sprung_auf = $row->level_sprung_auf;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->antwortenprozeile = $row->antwortenprozeile;
				
				$this->result[] = $obj;
			}
			
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler in Fkt getAll()';
			return false;
		}
	}
	
	/**
	 * Berechnet die maxpunkte fuer das Gebiet
	 *
	 * @param $gebiet_id
	 */
	public function berechneMaximalpunkte($gebiet_id)
	{
		if(!$this->load($gebiet_id))
			return false;
			
		if(!$this->levelgleichverteilung && ($this->level_start=='' || $this->level_start==0))
		{
			$qry = "SELECT sum(punkte) as max 
					FROM testtool.tbl_vorschlag JOIN testtool.tbl_frage USING(frage_id)
					WHERE gebiet_id='".addslashes($gebiet_id)."' AND punkte>0 AND NOT demo";
			if($this->maxfragen!='' && $this->maxfragen>0)
				$qry.=" LIMIT $maxfragen";
		}
		elseif($this->levelgleichverteilung && !$this->multipleresponse)
		{
			//Levelgleichverteilung mit singleresponse
			$qry = "SELECT sum(punkteprolevel) as max FROM
					(
						SELECT round((anz::decimal/fragengesamt::decimal)*$this->maxfragen*punkte) as punkteprolevel
						FROM
						(
						SELECT 
							level, punkte, count(*) as anz, 
							(SELECT count(*) FROM testtool.tbl_frage 
							WHERE gebiet_id='".addslashes($gebiet_id)."') as fragengesamt
						FROM 
							testtool.tbl_frage
							JOIN testtool.tbl_vorschlag USING(frage_id)
						WHERE
							gebiet_id='".addslashes($gebiet_id)."'
							AND NOT demo
						GROUP BY level, punkte
						) a
					) b";
		}
		elseif($this->levelgleichverteilung && $this->multipleresponse)
		{
			//Levelgleichverteilung mit multipleresponse
			$qry = "SELECT sum(punkteprolevel) as max FROM
					(
						SELECT round((anz::decimal/fragengesamt::decimal)*$this->maxfragen*punkte) as punkteprolevel
						FROM
						(
						SELECT 
							level, punkte, count(*) as anz, 
							(SELECT count(*) FROM testtool.tbl_frage 
							WHERE gebiet_id='".addslashes($gebiet_id)."') as fragengesamt
						FROM 
							testtool.tbl_frage
							JOIN testtool.tbl_vorschlag USING(frage_id)
						WHERE
							gebiet_id='".addslashes($gebiet_id)."'
							AND NOT demo
						GROUP BY level, punkte
						) a
					) b";
		}
		elseif($this->level_start!='')
		{
			//Maximalpunkte fuer geleveltes Gebiet ermitteln
			
			//Punkte pro Level holen
			$qry = "
			SELECT level, punkte 
			FROM
				(
				SELECT level, frage_id, sum(punkte) as punkte 
				FROM testtool.tbl_frage JOIN testtool.tbl_vorschlag USING(frage_id)
				WHERE gebiet_id=22 AND punkte>0 AND level>=2 AND NOT demo 
				GROUP BY level, frage_id
				) as a 
			GROUP by level, punkte";
			
			if($result = pg_query($this->conn, $qry))
			{
				$maxfragen = $this->maxfragen;
				$maxpunkte = 0;
				$lastpunkte=0;
				//Punkte mit der Anzahl der Mindestfragen fuer dieses Level multiplizieren
				while($row = pg_fetch_object($result))
				{
					if($maxfragen>0)
					{
						$maxpunkte += $row->punkte*$this->level_sprung_auf;
						$lastpunkte = $row->punkte;
						$maxfragen -=$this->level_sprung_auf;
					}
				}
				// zuletzt die verbleibenden Fragen mit der Punkteanzahl der letzten (schwersten) Fragen multiplizieren
				$maxpunkte += $lastpunkte*$maxfragen;
				return $maxpunkte;
			}
		}
		
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				return $row->max;
			}
			else 
			{
				$this->errormsg = 'Fehler beim Ermitteln der Maximalpunkte';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Ermitteln der Maximalpunkte';
			return false;
		}
	}
}
?>
