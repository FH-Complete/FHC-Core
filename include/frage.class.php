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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *			Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>
 */
/**
 * Klasse fuer die Fragen des Reihungstesttools
 */
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/gebiet.class.php');
require_once(dirname(__FILE__).'/pruefling.class.php');

class frage extends basis_db
{
	//Tabellenspalten
	public $frage_id;
	public $gebiet_id;
	public $nummer;
	public $demo;
	public $level;
	public $kategorie_kurzbz;
	public $aktiv;

	public $sprache;
	public $audio;
	public $text;
	public $bild;
	public $pruefling_id;
	public $prueflingfrage_id;
	public $begintime;
	public $endtime;

	public $insertamum;
	public $updateamum;
	public $insertvon;
	public $updatevon;

	// ErgebnisArray
	public $result=array();
	public $num_rows=0;
	public $new;

	/**
	 * Konstruktor - Uebergibt die Connection und laedt optional eine frage
	 * @param $frage_id       Frage die geladen werden soll (default=null)
	 */
	public function __construct($frage_id=null)
	{
		parent::__construct();

		if(!is_null($frage_id))
			$this->load($frage_id);
	}

	/**
	 * Laedt Frage mit der uebergebenen ID
	 * @param $frage_id ID der Frage die geladen werden soll
	 */
	public function load($frage_id)
	{
		if(!is_numeric($frage_id) || $frage_id=='')
		{
			$this->errormsg = 'Frage_id ist ungueltig';
			return false;
		}

		$qry = "SELECT
					*
				FROM
					testtool.tbl_frage
		 			LEFT OUTER JOIN testtool.tbl_frage_sprache USING (frage_id)
				WHERE
					frage_id=".$this->db_add_param($frage_id, FHC_INTEGER);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->frage_id = $row->frage_id;
				$this->gebiet_id = $row->gebiet_id;
				$this->nummer = $row->nummer;
				$this->demo = $this->db_parse_bool($row->demo);
				$this->kategorie_kurzbz = $row->kategorie_kurzbz;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->level = $row->level;
				$this->aktiv = $row->aktiv;
				$this->bild = $row->bild;

				return true;
			}
			else
			{
				$this->errormsg = "Kein Eintrag gefunden";
				return false;
			}
		}
		else
		{
			$this->errormsg = "Fehler beim Laden";
			return false;
		}
	}

	/**
	 * Prueft die Variablen vor dem Speichern
	 * auf Gueltigkeit.
	 * @return true wenn ok, false im Fehlerfall
	 */
	private function validate()
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
			$qry = 'BEGIN;INSERT INTO testtool.tbl_frage (kategorie_kurzbz, gebiet_id, level, nummer, demo,
													insertamum, insertvon, aktiv, updateamum, updatevon) VALUES('.
			       $this->db_add_param($this->kategorie_kurzbz).','.
			       $this->db_add_param($this->gebiet_id, FHC_INTEGER).','.
			       $this->db_add_param($this->level).','.
			       $this->db_add_param($this->nummer).','.
			       $this->db_add_param($this->demo, FHC_BOOLEAN).','.
			       $this->db_add_param($this->insertamum).','.
			       $this->db_add_param($this->insertvon).','.
			       $this->db_add_param($this->aktiv, FHC_BOOLEAN).','.
			       'null,null);';
		}
		else
		{
			$qry = 'UPDATE testtool.tbl_frage SET'.
			       ' gebiet_id='.$this->db_add_param($this->gebiet_id, FHC_INTEGER).','.
			       ' kategorie_kurzbz='.$this->db_add_param($this->kategorie_kurzbz).','.
			       ' level='.$this->db_add_param($this->level).','.
			       ' nummer='.$this->db_add_param($this->nummer).','.
			       ' demo='.$this->db_add_param($this->demo, FHC_BOOLEAN).','.
			       ' updateamum='.$this->db_add_param($this->updateamum).','.
			       ' updatevon='.$this->db_add_param($this->updatevon).','.
			       ' aktiv='.$this->db_add_param($this->aktiv, FHC_BOOLEAN).
			       " WHERE frage_id=".$this->db_add_param($this->frage_id, FHC_INTEGER, false).";";
		}

		if($this->db_query($qry))
		{
			if($this->new)
			{
				$qry = "SELECT currval('testtool.tbl_frage_frage_id_seq') as id";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->frage_id = $row->id;
						$this->db_query('COMMIT');
						return true;
					}
					else
					{
						$this->errormsg = 'Fehler beim Lesen der Sequence';
						$this->db_query('ROLLBACK');
						return false;
					}
				}
				else
				{
					$this->errormsg = 'Fehler beim Lesen der Sequence';
					$this->db_query('ROLLBACK');
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
			$this->errormsg = 'Fehler beim Speichern der Frage';
			return false;
		}
	}

	/**
	 * Speichert die Frage in der angegebenen Sprache
	 *
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save_fragesprache()
	{
		if($this->new) //Wenn new true ist dann ein INSERT absetzen ansonsten ein UPDATE
		{
			$qry = 'INSERT INTO testtool.tbl_frage_sprache (frage_id, sprache, text, bild, audio,
													insertamum, insertvon, updateamum, updatevon) VALUES('.
			       $this->db_add_param($this->frage_id, FHC_INTEGER).','.
			       $this->db_add_param($this->sprache).','.
			       $this->db_add_param($this->text).','.
			       $this->db_add_param($this->bild).','.
			       $this->db_add_param($this->audio).','.
			       $this->db_add_param($this->insertamum).','.
			       $this->db_add_param($this->insertvon).','.
			       'null,null);';
		}
		else
		{
			$qry = 'UPDATE testtool.tbl_frage_sprache SET'.
					' text='.$this->db_add_param($this->text).','.
					' bild='.$this->db_add_param($this->bild).','.
					' audio='.$this->db_add_param($this->audio).','.
					' updateamum='.$this->db_add_param($this->updateamum).','.
					' updatevon='.$this->db_add_param($this->updatevon).
					" WHERE frage_id=".$this->db_add_param($this->frage_id, FHC_INTEGER, false)." AND sprache=".$this->db_add_param($this->sprache).";";
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Frage';
			return false;
		}
	}

	/**
	 * Liefert die Fragen eines Gebietes mit der nummer $nummer
	 *
	 * @param $gebiet_id
	 * @param $nummer
	 * @return true wenn ok, sonst false
	 */
	public function getFragen($gebiet_id, $nummer)
	{
		$qry = "SELECT * FROM testtool.tbl_frage
				WHERE gebiet_id=".$this->db_add_param($gebiet_id, FHC_INTEGER)." AND nummer=".$this->db_add_param($nummer);

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new frage();

				$obj->frage_id = $row->frage_id;
				$obj->kategorie_kurzbz = $row->kategorie_kurzbz;
				$obj->gebiet_id = $row->gebiet_id;
				$obj->level = $row->level;
				$obj->nummer = $row->nummer;
				$obj->demo = $this->db_parse_bool($row->demo);
				$obj->aktiv = $this->db_parse_bool($row->aktiv);

				$this->result[] = $obj;
			}

			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Liefert die Fragen eines Gebietes
	 *
	 * @param integer $gebiet_id
	 * @return true wenn ok, sonst false
	 */
	public function getFragenGebiet($gebiet_id)
	{
		$qry = "SELECT * FROM testtool.tbl_frage
				WHERE gebiet_id=".$this->db_add_param($gebiet_id, FHC_INTEGER)." ORDER BY nummer";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new frage();

				$obj->frage_id = $row->frage_id;
				$obj->kategorie_kurzbz = $row->kategorie_kurzbz;
				$obj->gebiet_id = $row->gebiet_id;
				$obj->level = $row->level;
				$obj->nummer = $row->nummer;
				$obj->demo = $this->db_parse_bool($row->demo);
				$obj->aktiv = $this->db_parse_bool($row->aktiv);

				$this->result[] = $obj;
			}

			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Laedt die naechste Frage eines Gebiets
	 *
	 * @param $gebiet_id
	 * @param $pruefling_id
	 * @param $frage_id ID der vorherigen Frage. wenn Null dann wird die erste Frage geliefert
	 * @param $demo
	 * @param $levelgebiet
	 * @return frage_id der naechsten Frage oder false wenn Fehler
	 */
	public function getNextFrage($gebiet_id, $pruefling_id, $frage_id=null, $demo=false, $levelgebiet=false)
	{
		if($demo)
		{
			$qry = "SELECT frage_id FROM testtool.tbl_frage
					WHERE tbl_frage.gebiet_id=".$this->db_add_param($gebiet_id, FHC_INTEGER)."
					AND demo ";
			if(!is_null($frage_id))
				$qry.=" AND nummer<(SELECT nummer FROM testtool.tbl_frage WHERE frage_id=".$this->db_add_param($frage_id, FHC_INTEGER).")";
			$qry .= " ORDER BY nummer DESC LIMIT 1";
		}
		else
		{
			$qry = "SELECT frage_id FROM testtool.tbl_pruefling_frage JOIN testtool.tbl_frage USING(frage_id)
					WHERE
						tbl_frage.gebiet_id=".$this->db_add_param($gebiet_id, FHC_INTEGER)." AND
						tbl_pruefling_frage.pruefling_id=".$this->db_add_param($pruefling_id, FHC_INTEGER)." AND
						NOT demo ";

			if(!is_null($frage_id))
				$qry.="	AND tbl_pruefling_frage.nummer>(SELECT nummer FROM testtool.tbl_pruefling_frage WHERE pruefling_id=".$this->db_add_param($pruefling_id, FHC_INTEGER)." AND frage_id=".$this->db_add_param($frage_id, FHC_INTEGER)." LIMIT 1)";
			elseif(is_null($frage_id) && $levelgebiet)
				$qry.=" AND tbl_pruefling_frage.endtime is null";

			$qry.=" ORDER BY tbl_pruefling_frage.nummer ASC LIMIT 1";
		}

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
				return $row->frage_id;
			else
				return false;
		}
		else
			return false;
	}

	/**
	 * Laedt die Frage in der angegebenen Sprache
	 *
	 * @param $frage_id
	 * @param $sprache
	 * @param $aktiv Default: null
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getFrageSprache($frage_id, $sprache, $aktiv=null)
	{
		$qry = "SELECT * FROM testtool.tbl_frage_sprache JOIN testtool.tbl_frage USING(frage_id)
				WHERE frage_id=".$this->db_add_param($frage_id, FHC_INTEGER)." AND sprache=".$this->db_add_param($sprache);

		if (!is_null($aktiv))
			$qry .= " AND aktiv=".($aktiv?'true':'false');

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->frage_id = $row->frage_id;
				$this->sprache = $row->sprache;
				$this->text = $row->text;
				$this->bild = $row->bild;
				$this->audio = $row->audio;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;

				$this->level = $row->level;
				$this->demo = $this->db_parse_bool($row->demo);
				$this->nummer = $row->nummer;
				$this->aktiv = $this->db_parse_bool($row->aktiv);

				return true;
			}
			else
			{
				$this->errormsg = 'Fehler beim Laden der Frage';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Frage';
			return false;
		}
	}

	/**
	 * Liefert das naechste Level zu dem noch Fragen fehlen
	 *
	 * @param $levelarr Array mit den Levels und der Anzahl der noch zu liefernden Fragen
	 * @return naechstes level bzw null wenn keines mehr vorhanden
	 */
	private function getNextFrageLevel($levelarr)
	{
		foreach ($levelarr as $key=>$row)
		{
			if($row>0)
				return $key;
		}

		return null;
	}

	/**
	 * Generiert den Fragenpool fuer den Pruefling bzw
	 * die naechste Frage bei gelevelten Gebieten
	 *
	 * @param $pruefling_id
	 * @param $gebiet_id
	 */
	public function generateFragenpool($pruefling_id, $gebiet_id)
	{
		$gebiet = new gebiet($gebiet_id);

		//Bei Levelgesteuerten Fragen wird nur die erste Frage angelegt
		if($gebiet->level_start!='')
		{
			// Anzahl der bereits vorhandenen Fragen holen
			$qry = "SELECT count(*) as anzahl FROM testtool.tbl_pruefling_frage JOIN testtool.tbl_frage USING(frage_id)
					WHERE gebiet_id=".$this->db_add_param($gebiet_id, FHC_INTEGER)." AND pruefling_id=".$this->db_add_param($pruefling_id, FHC_INTEGER);
			if($this->db_query($qry))
			{
				if($row = $this->db_fetch_object())
				{
					if($row->anzahl>=$gebiet->maxfragen)
					{
						// maximale Fragenanzahl erreicht
						return true;
					}
				}
			}
			$maxfragen=1;
		}
		else
		{
			$maxfragen = $gebiet->maxfragen;

			// Wie viele Fragen gibt es in diesem Gebiet
			$qry = "SELECT count(*) as anzahl FROM testtool.tbl_frage WHERE NOT demo AND gebiet_id=".$this->db_add_param($gebiet_id, FHC_INTEGER);
			if($this->db_query($qry))
			{
				if($row = $this->db_fetch_object())
				{
					$fragengesamt = $row->anzahl;
				}
			}

			// Wenn im Gebiet keine Maximalzahl angegeben ist, dann kommen alle Fragen in den Pool
			if($maxfragen=='')
			{
				$maxfragen = $fragengesamt;
			}

			$level = array();

			// Bei Levelgleichverteilung die Anzahl der Fragen pro level ermitteln
			if($gebiet->levelgleichverteilung)
			{
				$qry = "SELECT level, count(*) as anzahl FROM testtool.tbl_frage
						WHERE NOT demo AND gebiet_id=".$this->db_add_param($gebiet_id, FHC_INTEGER)."
						GROUP BY level
						ORDER BY level";

				if($this->db_query($qry))
				{
					while($row = $this->db_fetch_object())
					{
						$level[$row->level]=round(($row->anzahl/$fragengesamt)*$maxfragen);
					}
				}
				// Wenn ein Level besonders viele Fragen und eines nur eine Frage (nach dem Runden) hat,
				// kann es durch Rundungsfehler vorkommen, dass bei der Ausgabe ein Level ausgelassen wird.
				// Deshalb wird das array hier aufsteigend sortiert. So wird ggf. vom Level mit den meisten Fragen
				// die letzte ausgelassen. Hier wäre eine sauberere Lösung besser.
				asort($level);

				// Von jedem Gebiet muss mindestens eine Frage kommen
				foreach ($level as $key=>$row)
				{
					if($level[$key]==0)
					{
						$level[$key]=1;
					}
				}
			}
		}

		$this->db_query('BEGIN;');

		while($maxfragen>0)
		{
			//Bei levelgleichverteilung das Level der naechsten Frage holen
			if($gebiet->levelgleichverteilung)
			{
				$nextlevel=$this->getNextFrageLevel($level);
				if(isset($level[$nextlevel]))
					$level[$nextlevel]=$level[$nextlevel]-1;
			}
			else
			{
				$nextlevel=null;
			}

			$this->frage_id=$this->getNewFrage($gebiet_id, $pruefling_id, $nextlevel);

			if($this->frage_id=='')
				break;

			$this->pruefling_id=$pruefling_id;

			//hoechste Nummer holen
			$qry = "SELECT
						tbl_pruefling_frage.nummer
					FROM
						testtool.tbl_pruefling_frage JOIN testtool.tbl_frage USING(frage_id)
					WHERE
						tbl_frage.gebiet_id=".$this->db_add_param($gebiet_id, FHC_INTEGER)." AND
						tbl_pruefling_frage.pruefling_id=".$this->db_add_param($pruefling_id, FHC_INTEGER)."
					ORDER BY nummer DESC LIMIT 1;";

			if($this->db_query($qry))
			{
				if($row = $this->db_fetch_object())
					$this->nummer = $row->nummer+1;
				else
					$this->nummer = 1;
			}
			else
			{
				$this->errormsg = 'Fehler beim Generieren des Fragenpools';
				$this->db_query('ROLLBACK');
				return false;
			}

			$this->begintime='';
			$this->endtime='';

			//PrueflingFrage speichern
			if(!$this->save_prueflingfrage(true))
			{
				$this->db_query('ROLLBACK');
				return false;
			}

			$maxfragen--;
		}

		$this->db_query('COMMIT;');
		return true;
	}

	/**
	 * Laedt eine Prueflingfrage
	 *
	 * @param $prueflingfrage_id
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load_prueflingfrage($prueflingfrage_id)
	{
		if(!is_numeric($prueflingfrage_id) || $prueflingfrage_id=='')
		{
			$this->errormsg = 'prueflingfrage_id ist ungueltig';
			return false;
		}

		$qry = "SELECT * FROM testtool.tbl_pruefling_frage WHERE prueflingfrage_id=".$this->db_add_param($prueflingfrage_id, FHC_INTEGER);
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->prueflingfrage_id = $row->prueflingfrage_id;
				$this->pruefling_id = $row->pruefling_id;
				$this->frage_id = $row->frage_id;
				$this->nummer = $row->nummer;
				$this->begintime = $row->begintime;
				$this->endtime = $row->endtime;

				return true;
			}
			else
			{
				$this->errormsg = 'Es wurde keine PrueflingFrage mit der uebergebenen ID gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der PrueflingFrage';
			return false;
		}
	}

	/**
	 * Laedt eine Prueflingfrage
	 *
	 * @param $pruefling_id
	 * @param $frage_id
	 * @return true wenn ok, false wenn Fehler oder kein Eintrag vorhanden
	 */
	public function getPrueflingfrage($pruefling_id, $frage_id)
	{
		if(!is_numeric($pruefling_id) || $pruefling_id=='')
		{
			$this->errormsg = 'Pruefling_id ist ungueltig';
			return false;
		}

		if(!is_numeric($frage_id) || $frage_id=='')
		{
			$this->errormsg = 'Frage_id ist ungueltig';
			return false;
		}

		$qry = "SELECT * FROM testtool.tbl_pruefling_frage WHERE pruefling_id=".$this->db_add_param($pruefling_id, FHC_INTEGER)." AND frage_id=".$this->db_add_param($frage_id, FHC_INTEGER);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->prueflingfrage_id = $row->prueflingfrage_id;
				$this->pruefling_id = $row->pruefling_id;
				$this->frage_id = $row->frage_id;
				$this->nummer = $row->nummer;
				$this->begintime = $row->begintime;
				$this->endtime = $row->endtime;

				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der PrueflingFrage';
			return false;
		}
	}

	/**
	 * Prueft die Daten vor dem Speichern in die Tabelle tbl_pruefling_frage
	 *
	 */
	private function validate_prueflingfrage()
	{
		if(!is_numeric($this->pruefling_id) || $this->pruefling_id=='')
		{
			$this->errormsg = 'Pruefling_id ist ungueltig';
			return false;
		}

		if(!is_numeric($this->frage_id) || $this->frage_id=='')
		{
			$this->errormsg = 'Frage_id ist ungueltig';
			return false;
		}

		if(!is_numeric($this->nummer))
		{
			$this->errormsg = 'Nummer ist ungueltig';
			return false;
		}
		return true;
	}

	/**
	 * Speichert einen Eintrag in der Tabelle tbl_pruefling_frage
	 *
	 * @param $new
	 */
	public function save_prueflingfrage($new=null)
	{
		if(!$this->validate_prueflingfrage())
		{
			return false;
		}

		if(is_null($new))
			$new = $this->new;

		if($new)
		{
			$qry = 'INSERT INTO testtool.tbl_pruefling_frage(pruefling_id, frage_id, nummer, begintime, endtime) VALUES('.
					$this->db_add_param($this->pruefling_id, FHC_INTEGER).','.
					$this->db_add_param($this->frage_id, FHC_INTEGER).','.
					$this->db_add_param($this->nummer).','.
					$this->db_add_param($this->begintime).','.
					$this->db_add_param($this->endtime).');';
		}
		else
		{
			$qry = 'UPDATE testtool.tbl_pruefling_frage SET'.
					' pruefling_id='.$this->db_add_param($this->pruefling_id, FHC_INTEGER).','.
					' frage_id='.$this->db_add_param($this->frage_id, FHC_INTEGER).','.
					' nummer='.$this->db_add_param($this->nummer).','.
					' begintime='.$this->db_add_param($this->begintime).','.
					' endtime='.$this->db_add_param($this->endtime).
					" WHERE prueflingfrage_id=".$this->db_add_param($this->prueflingfrage_id, FHC_INTEGER, false);
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern in tbl_pruefling_frage';
			return false;
		}
	}

	/**
	 * Sucht eine neue Frage fuer einen Pruefling
	 *
	 * @param $gebiet_id
	 * @param $pruefling_id
	 * @param $level (nur bei Levelgleichverteilung)
	 */
	private function getNewFrage($gebiet_id, $pruefling_id, $level=null)
	{
		$gebiet = new gebiet($gebiet_id);
		$pruefling = new pruefling();


		//Frage suchen die dem pruefling noch nicht zugeordnet ist
		$qry = "SELECT frage_id FROM testtool.tbl_frage
				WHERE gebiet_id=".$this->db_add_param($gebiet_id, FHC_INTEGER)." AND
					frage_id NOT IN (SELECT frage_id FROM testtool.tbl_pruefling_frage
								WHERE pruefling_id=".$this->db_add_param($pruefling_id, FHC_INTEGER, false)."
								)
					AND NOT demo";

		// Wenn die Frage abhaengig vom level sein soll, dann den aktuellen Level ermitteln und dazuhaengen
		if($gebiet->level_start!='')
		{
			$level2 = $pruefling->getPrueflingLevel($pruefling_id, $gebiet_id);
			$qry.=" AND level=".$this->db_add_param($level2);
		}

		// Bei Levelgleichverteilung wird der Level mituebergeben
		if(!is_null($level))
		{
			$qry.=" AND level=".$this->db_add_param($level);
		}

		//Sortierung
		if($gebiet->zufallfrage)
			$qry .= " ORDER BY random()";
		else
			$qry .= " ORDER BY nummer ASC";

		$qry .= " LIMIT 1";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				return $row->frage_id;
			}
			else
			{
				$this->errormsg = 'Es gibt keine Fragen die den Kriterien entsprechen';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Holen der Frage';
			return false;
		}
	}

	public function delete($frage_id)
	{
		$qry = "SELECT * FROM
			testtool.tbl_antwort
			JOIN testtool.tbl_vorschlag USING(vorschlag_id)
			WHERE tbl_vorschlag.frage_id=".$this->db_add_param($frage_id, FHC_INTEGER);
		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result)>0)
			{
				$this->errormsg = 'Frage kann nicht gelöscht werden da bereits Antworten vorhanden sind';
				return false;
			}
		}

		$qry = "
		DELETE FROM testtool.tbl_vorschlag_sprache WHERE vorschlag_id IN (SELECT vorschlag_id FROM testtool.tbl_vorschlag WHERE frage_id=".$this->db_add_param($frage_id, FHC_INTEGER).");
		DELETE FROM testtool.tbl_vorschlag WHERE frage_id=".$this->db_add_param($frage_id, FHC_INTEGER).";
		DELETE FROM testtool.tbl_frage_sprache WHERE frage_id=".$this->db_add_param($frage_id, FHC_INTEGER).";
		DELETE FROM testtool.tbl_frage WHERE frage_id=".$this->db_add_param($frage_id, FHC_INTEGER);

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim loeschen der Frage';
			return false;
		}
	}

	/**
	 * Gibt die Nummer der naechsten Frage zurueck (nicht fuer den test sondern fuer die testtool-administrationsseite)
	 *
	 * @param $frage_nummer Nummer der aktuellen Frage
	 * @param $gebiet_id Gebiet der Fragen
	 * @param $aktiv true wenn nur aktiv, false wenn nur inaktiv, null wenn beides
	 */
	public function getNextFrageNummer($frage_nummer, $gebiet_id, $aktiv=null)
	{
		$erg = '';
		$qry = "SELECT nummer FROM testtool.tbl_frage
				WHERE gebiet_id=".$this->db_add_param($gebiet_id, FHC_INTEGER)."
				 AND nummer>".$this->db_add_param($frage_nummer, FHC_INTEGER);

		if (!is_null($aktiv) && $aktiv)
			$qry .= " AND aktiv";
		if (!is_null($aktiv) && !$aktiv)
			$qry .= " AND NOT aktiv";

		$qry .=	" ORDER BY nummer ASC LIMIT 1"; //Es wird immer nur ein Maximum geben, deswegen kein max()

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$erg .= $row->nummer;
			}
			return $erg;
		} else {
			return false;
		}
	}

	/**
	 * Gibt die letzte (hoechste) Nummer eines Gebiets zurueck
	 *
	 * @param integer $gebiet_id Gebiet der Fragen
	 * @return integer $number Nummer der letzten Frage des Gebiets, wenn gefunden, sonst false
	 */
	public function getMaxNummer($gebiet_id)
	{
		$number = '';
		$qry = "SELECT nummer FROM testtool.tbl_frage
				WHERE gebiet_id=".$this->db_add_param($gebiet_id, FHC_INTEGER)." ORDER BY nummer DESC LIMIT 1";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$number = $row->nummer;
			}
			return $number;
		}
		else
		{
			return false;
		}
	}
}
?>
