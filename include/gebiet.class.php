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
require_once(dirname(__FILE__).'/basis_db.class.php');

class gebiet extends basis_db
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
	public $offsetpunkte;
	public $insertamum;
	public $insertvon;
	public $updateamum;
	public $updatevon;

	// ErgebnisArray
	public $result=array();
	public $new;

	/**
	 * Konstruktor - Laedt optional ein Gebiet
	 * @param $gebiet_id      Gebiet das geladen werden soll (default=null)
	 */
	public function __construct($gebiet_id=null)
	{
		parent::__construct();

		if(!is_null($gebiet_id))
			$this->load($gebiet_id);
	}

	/**
	 * Laedt Gebiet mit der uebergebenen ID
	 * @param $gebiet_id ID des Gebiets das geladen werden soll
	 * @return true wenn ok, sonst false
	 */
	public function load($gebiet_id)
	{
		$sprache = new sprache();
		$bezeichnung_mehrsprachig = $sprache->getSprachQuery('bezeichnung_mehrsprachig');
		$qry = "SELECT *,$bezeichnung_mehrsprachig FROM testtool.tbl_gebiet WHERE gebiet_id=".$this->db_add_param($gebiet_id, FHC_INTEGER);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->gebiet_id = $row->gebiet_id;
				$this->kurzbz = $row->kurzbz;
				$this->bezeichnung = $row->bezeichnung;
				$this->bezeichnung_mehrsprachig = $sprache->parseSprachResult('bezeichnung_mehrsprachig', $row);
				$this->beschreibung = $row->beschreibung;
				$this->zeit = $row->zeit;
				$this->multipleresponse = $this->db_parse_bool($row->multipleresponse);
				$this->kategorien = $this->db_parse_bool($row->kategorien);
				$this->maxfragen = $row->maxfragen;
				$this->zufallfrage = $this->db_parse_bool($row->zufallfrage);
				$this->zufallvorschlag = $this->db_parse_bool($row->zufallvorschlag);
				$this->level_start = $row->level_start;
				$this->level_sprung_auf = $row->level_sprung_auf;
				$this->level_sprung_ab = $row->level_sprung_ab;
				$this->levelgleichverteilung = $this->db_parse_bool($row->levelgleichverteilung);
				$this->maxpunkte = $row->maxpunkte;
				$this->offsetpunkte = $row->offsetpunkte;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->antwortenprozeile = $row->antwortenprozeile;

				return true;
			}
			else
			{
				$this->errormsg = "Gebiet nicht gefunden";
				return false;
			}
		}
		else
		{
			$this->errormsg = "Fehler beim Laden des Gebiets";
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
		if(mb_strlen($this->kurzbz)>10)
		{
			$this->errormsg = 'Kurzbz darf nicht laenger als 10 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->bezeichnung)>50)
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
		if(!is_numeric($this->offsetpunkte) && $this->offsetpunkte!='')
		{
			$this->errormsg = 'Offsetpunkte muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_numeric($this->antwortenprozeile) || $this->antwortenprozeile<=0)
		{
			$this->errormsg = 'AntortenProZeile muss eine gueltige Zahl und groesser als 0 sein';
		}

		return true;
	}

	/**
	 * Speichert das Gebiet in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * ansonsten der Datensatz aktualisiert
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
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
					levelgleichverteilung, maxpunkte, offsetpunkte, antwortenprozeile, ';

					foreach($this->bezeichnung_mehrsprachig as $key=>$value)
					{
						$idx = sprache::$index_arr[$key];
						$qry.=" bezeichnung_mehrsprachig[$idx],";
					}

					$qry.='insertamum, insertvon , updateamum, updatevon) VALUES('.

					$this->db_add_param($this->kurzbz).','.
					$this->db_add_param($this->bezeichnung).','.
					$this->db_add_param($this->beschreibung).','.
					$this->db_add_param($this->zeit).','.
					$this->db_add_param($this->multipleresponse, FHC_BOOLEAN).','.
					$this->db_add_param($this->kategorien, FHC_BOOLEAN).','.
					$this->db_add_param($this->maxfragen).','.
					$this->db_add_param($this->zufallfrage, FHC_BOOLEAN).','.
					$this->db_add_param($this->zufallvorschlag, FHC_BOOLEAN).','.
					$this->db_add_param($this->level_start).','.
					$this->db_add_param($this->level_sprung_auf).','.
					$this->db_add_param($this->level_sprung_ab).','.
					$this->db_add_param($this->levelgleichverteilung, FHC_BOOLEAN).','.
					$this->db_add_param($this->maxpunkte).','.
					$this->db_add_param($this->offsetpunkte).','.
					$this->db_add_param($this->antwortenprozeile).',';
					foreach($this->bezeichnung_mehrsprachig as $key=>$value)
						$qry.=$this->db_add_param($value).',';

					$qry .= $this->db_add_param($this->insertamum).','.
					$this->db_add_param($this->insertvon).
					',null, null);';
		}
		else
		{
			$qry = 'UPDATE testtool.tbl_gebiet SET'.
					' kurzbz='.$this->db_add_param($this->kurzbz).','.
					' bezeichnung='.$this->db_add_param($this->bezeichnung).','.
					' beschreibung='.$this->db_add_param($this->beschreibung).','.
					' zeit='.$this->db_add_param($this->zeit).','.
					' multipleresponse='.$this->db_add_param($this->multipleresponse, FHC_BOOLEAN).','.
					' kategorien='.$this->db_add_param($this->kategorien, FHC_BOOLEAN).','.
					' maxfragen='.$this->db_add_param($this->maxfragen).','.
					' zufallfrage='.$this->db_add_param($this->zufallfrage, FHC_BOOLEAN).','.
					' zufallvorschlag='.$this->db_add_param($this->zufallvorschlag, FHC_BOOLEAN).','.
					' level_start='.$this->db_add_param($this->level_start).','.
					' level_sprung_auf='.$this->db_add_param($this->level_sprung_auf).','.
					' level_sprung_ab='.$this->db_add_param($this->level_sprung_ab).','.
					' levelgleichverteilung='.$this->db_add_param($this->levelgleichverteilung, FHC_BOOLEAN).','.
					' maxpunkte='.$this->db_add_param($this->maxpunkte).','.
					' offsetpunkte='.$this->db_add_param($this->offsetpunkte).','.
					' antwortenprozeile='.$this->db_add_param($this->antwortenprozeile).','.
					' updateamum='.$this->db_add_param($this->updateamum).','.
					' updatevon='.$this->db_add_param($this->updatevon).',';
					foreach($this->bezeichnung_mehrsprachig as $key=>$value)
					{
						$idx = sprache::$index_arr[$key];
						$qry .= " bezeichnung_mehrsprachig[$idx]=".$this->db_add_param($value).",";
					}
					$qry = mb_substr($qry,0,-1);
					$qry .=	' WHERE gebiet_id='.$this->db_add_param($this->gebiet_id, FHC_INTEGER, false).';';
		}

		if($this->db_query($qry))
		{
			//aktuelle ID aus der Sequence holen
			if($new)
			{
				$qry="SELECT currval('testtool.tbl_gebiet_gebiet_id_seq') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->gebiet_id = $row->id;
						$this->db_query('COMMIT');
						return true;
					}
					else
					{
						$this->errormsg = 'Fehler beim Lesen der Sequence';
						$this->db_query('ROLLBACK;');
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
				return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Frage';
			return false;
		}
	}

	/**
	 * prueft das Gebiet auf Gueltigkeit
	 * (diverse Plausichecks)
	 *
	 * @param $gebiet_id
	 */
	public function check_gebiet($gebiet_id)
	{
		$this->errormsg = '';
		$this->warningmsg = '';
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
			$qry = "SELECT count(*) as anzahl, level FROM testtool.tbl_frage WHERE gebiet_id=".$this->db_add_param($gebiet_id, FHC_INTEGER, false)." GROUP BY level";
			if($this->db_query($qry))
			{
				while($row = $this->db_fetch_object())
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
				AND gebiet_id=".$this->db_add_param($gebiet_id, FHC_INTEGER)." AND NOT demo;";
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$this->errormsg .= "Frage Nummer $row->nummer (ID: $row->frage_id) hat weniger als 2 Vorschlaege.\n";
			}
		}

		//Wenn Levels verwendet werden, muessen mindestens 2 Verschiedene Level vorhanden sein
		if($this->level_start!='')
		{
			$qry = "SELECT level FROM testtool.tbl_frage WHERE gebiet_id=".$this->db_add_param($gebiet_id, FHC_INTEGER)." AND level is not null GROUP by level";
			if($this->db_query($qry))
			{
				if($this->db_num_rows()<2)
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
				$qry = "SELECT count(*) as anzahl FROM testtool.tbl_frage WHERE gebiet_id=".$this->db_add_param($gebiet_id, FHC_INTEGER)." AND not demo AND level is not null GROUP BY level";
				if($this->db_query($qry))
				{
					if($this->db_num_rows() > $this->maxfragen)
					{
						$this->errormsg .= "Wenn Levelgleichverteilung gesetzt ist, muss maxfragen groesser als die Anzahl der verwendeten Levels sein\n";
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
								SELECT level, round(sum(punkte)) as punkte
								FROM testtool.tbl_frage JOIN testtool.tbl_vorschlag USING(frage_id)
								WHERE punkte>0 AND not demo AND gebiet_id=".$this->db_add_param($gebiet_id, FHC_INTEGER)."
								GROUP BY frage_id, level) as a
							GROUP BY level, punkte ) as b
						GROUP BY level) as c
					WHERE c.anzahl>1";
			if($this->db_query($qry))
			{
				while($row = $this->db_fetch_object())
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

		// Pruefung, ob eine Frage mehrere gleiche Antworten oder gleiche Bilder hat
		$qry = "SELECT text AS antwort, sprache, frage_id, tbl_frage.nummer, COUNT(text) AS Anz
				FROM testtool.tbl_vorschlag_sprache
				JOIN testtool.tbl_vorschlag USING (vorschlag_id)
				JOIN testtool.tbl_frage USING (frage_id)
				WHERE gebiet_id=".$this->db_add_param($gebiet_id, FHC_INTEGER)."
				AND (bild IS NULL AND audio IS NULL)
				GROUP BY antwort,frage_id,tbl_frage.nummer,sprache
				HAVING ( COUNT(text) > 1 )

				UNION

				SELECT bild AS antwort, sprache, frage_id, tbl_frage.nummer, COUNT(bild) AS Anz
				FROM testtool.tbl_vorschlag_sprache
				JOIN testtool.tbl_vorschlag USING (vorschlag_id)
				JOIN testtool.tbl_frage USING (frage_id)
				WHERE gebiet_id=".$this->db_add_param($gebiet_id, FHC_INTEGER)."
				AND ((text IS NULL OR text='' OR text=' ') AND audio IS NULL)
				GROUP BY antwort,frage_id,tbl_frage.nummer,sprache
				HAVING ( COUNT(bild) > 1 );";
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$this->warningmsg .= "Frage Nummer $row->nummer (ID: $row->frage_id) Sprache $row->sprache hat mehrere gleiche Antworten.\n";
			}
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

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new gebiet();

				$obj->gebiet_id = $row->gebiet_id;
				$obj->kurzbz = $row->kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->beschreibung = $row->beschreibung;
				$obj->zeit = $row->zeit;
				$obj->multipleresponse = $this->db_parse_bool($row->multipleresponse);
				$obj->kategorien = $this->db_parse_bool($row->kategorien);
				$obj->maxfragen = $row->maxfragen;
				$obj->zufallfrage = $this->db_parse_bool($row->zufallfrage);
				$obj->zufallvorschlag = $this->db_parse_bool($row->zufallvorschlag);
				$obj->levelgleichverteilung = $this->db_parse_bool($row->levelgleichverteilung);
				$obj->maxpunkte = $row->maxpunkte;
				$obj->offsetpunkte = $row->offsetpunkte;
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
					WHERE gebiet_id=".$this->db_add_param($gebiet_id, FHC_INTEGER)." AND punkte>0 AND NOT demo";
			if($this->maxfragen!='' && $this->maxfragen>0)
				$qry.=" LIMIT $this->maxfragen";
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
							WHERE gebiet_id=".$this->db_add_param($gebiet_id, FHC_INTEGER).") as fragengesamt
						FROM
							testtool.tbl_frage
							JOIN testtool.tbl_vorschlag USING(frage_id)
						WHERE
							gebiet_id=".$this->db_add_param($gebiet_id, FHC_INTEGER)."
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
							WHERE gebiet_id=".$this->db_add_param($gebiet_id, FHC_INTEGER).") as fragengesamt
						FROM
							testtool.tbl_frage
							JOIN testtool.tbl_vorschlag USING(frage_id)
						WHERE
							gebiet_id=".$this->db_add_param($gebiet_id, FHC_INTEGER)."
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
				WHERE gebiet_id=".$this->db_add_param($gebiet_id, FHC_INTEGER)." AND punkte>0
				AND level>=".$this->db_add_param($this->level_start)." AND NOT demo
				GROUP BY level, frage_id
				) as a
			GROUP by level, punkte ORDER BY level";

			if($this->db_query($qry))
			{
				$maxfragen = $this->maxfragen;
				$maxpunkte = 0;
				$lastpunkte=0;
				//Punkte mit der Anzahl der Mindestfragen fuer dieses Level multiplizieren
				while($row = $this->db_fetch_object())
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

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
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

	/**
	 * Berechnet die offsetpunkte fuer das Gebiet
	 *
	 * @param $gebiet_id
	 */
	public function berechneOffsetpunkte($gebiet_id)
	{
		if(!$this->load($gebiet_id))
			return false;

		$checkqry = "SELECT COUNT (uniquepoints) AS count_uniquepoints
					FROM (
							 SELECT gebiet_id,
									level,
									count(DISTINCT punkte),
									CASE WHEN count(DISTINCT punkte) > 1 THEN FALSE ELSE TRUE END AS uniquepoints
							 FROM (
									  SELECT tbl_frage.gebiet_id,
											 tbl_frage.level,
											 CASE WHEN multipleresponse THEN sum(punkte) ELSE min(punkte) END AS punkte
									  FROM testtool.tbl_frage
											   JOIN testtool.tbl_vorschlag USING (frage_id)
											   JOIN testtool.tbl_gebiet USING (gebiet_id)
									  WHERE tbl_vorschlag.aktiv
										AND tbl_vorschlag.punkte < 0
										AND tbl_frage.demo = false
										AND tbl_frage.aktiv
										AND gebiet_id = ".$this->db_add_param($gebiet_id, FHC_INTEGER)."
									  GROUP BY tbl_frage.gebiet_id, multipleresponse, tbl_frage.level, tbl_frage.frage_id
								  ) p
							 GROUP BY gebiet_id, level
						 ) pu
					WHERE uniquepoints = FALSE";

		if($this->db_query($checkqry))
		{
			if($row = $this->db_fetch_object())
			{
				if (!is_numeric($row->count_uniquepoints) || $row->count_uniquepoints > 0)
					$this->errormsg = 'Negativpunkte sind nicht für alle Fragen gleich hoch!';
			}
			else
			{
				$this->errormsg = 'Fehler beim Prüfen der Offsetpunkte';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Prüfen der Offsetpunkte';
			return false;
		}

		$qry = "
			WITH fragen AS (
				SELECT tbl_frage.frage_id, tbl_frage.gebiet_id, tbl_frage.level, tbl_frage.nummer, punkte
				FROM testtool.tbl_frage
						 JOIN testtool.tbl_vorschlag USING (frage_id)
				WHERE tbl_vorschlag.aktiv
				  AND tbl_vorschlag.punkte < 0
				  AND tbl_frage.demo = false
				  AND tbl_frage.aktiv
			),
			fragenanzahl AS (
				SELECT gebiet_id, level, levelgleichverteilung,
					   CASE WHEN levelgleichverteilung
								THEN
								ROUND(COALESCE(maxfragen
											 * ((SELECT count(*) FROM testtool.tbl_frage f WHERE f.level = tbl_frage.level AND f.gebiet_id = tbl_frage.gebiet_id)::decimal)
											 / (SELECT count(*) FROM testtool.tbl_frage f WHERE f.gebiet_id = tbl_frage.gebiet_id)::decimal
									, (SELECT count(*) FROM testtool.tbl_frage f WHERE f.level = tbl_frage.level AND f.gebiet_id = tbl_frage.gebiet_id)
									))
							ELSE
								COALESCE(maxfragen, count(*))
						   END
						   AS anzahl
				FROM testtool.tbl_frage
						 JOIN testtool.tbl_gebiet USING (gebiet_id)
				WHERE tbl_frage.aktiv
				  AND tbl_frage.demo = false
				GROUP BY gebiet_id, level, levelgleichverteilung, maxfragen
			)
			SELECT
				tbl_gebiet.gebiet_id, tbl_gebiet.bezeichnung,
				tbl_gebiet.multipleresponse,
				tbl_gebiet.maxpunkte, tbl_gebiet.maxfragen,
				tbl_gebiet.levelgleichverteilung,
				tbl_gebiet.level_start,
				(
					CASE WHEN tbl_gebiet.levelgleichverteilung  THEN
							 (CASE WHEN tbl_gebiet.multipleresponse=false THEN
									   (SELECT sum(frprolevel) FROM
										   (
											   SELECT fragen.level, (SELECT sum(punkte) FROM (SELECT min(punkte) AS punkte FROM fragen lvlfr WHERE lvlfr.gebiet_id = fragen.gebiet_id and lvlfr.level = fragen.level
																							  GROUP BY lvlfr.frage_id, lvlfr.nummer
																							  ORDER BY lvlfr.nummer
																							  LIMIT (SELECT anzahl FROM fragenanzahl WHERE fragenanzahl.gebiet_id = fragen.gebiet_id and fragenanzahl.level = fragen.level LIMIT 1)) fr)
												   AS frprolevel
											   FROM fragen
											   WHERE fragen.gebiet_id = tbl_gebiet.gebiet_id
											   GROUP BY fragen.gebiet_id, level
										   ) pkteprolevel)
								   ELSE
									   (SELECT sum(frprolevel) FROM
										   (
											   SELECT fragen.level,
													  (SELECT sum(punkte) FROM (SELECT sum(punkte) AS punkte FROM fragen lvlfr WHERE lvlfr.gebiet_id = fragen.gebiet_id and lvlfr.level = fragen.level
																				GROUP by lvlfr.frage_id, lvlfr.nummer
																				ORDER BY lvlfr.nummer
																				LIMIT (SELECT anzahl FROM fragenanzahl WHERE fragenanzahl.gebiet_id = fragen.gebiet_id and fragenanzahl.level = fragen.level LIMIT 1)) fr)
														  AS frprolevel
											   FROM fragen
											   WHERE fragen.gebiet_id = tbl_gebiet.gebiet_id
											   GROUP BY fragen.gebiet_id, level
										   ) pkteprolevel)
								 END)
					WHEN tbl_gebiet.level_start IS NOT NULL THEN
							 (CASE WHEN tbl_gebiet.multipleresponse=false THEN
									   (SELECT sum(pkte)
										FROM (
												 SELECT min(punkte) AS pkte
												 FROM fragen
												 WHERE fragen.gebiet_id = tbl_gebiet.gebiet_id
												   AND fragen.level = tbl_gebiet.level_start
												 GROUP BY frage_id, nummer
												 ORDER BY nummer
												 LIMIT (SELECT anzahl FROM fragenanzahl WHERE fragenanzahl.gebiet_id = tbl_gebiet.gebiet_id AND fragenanzahl.level = tbl_gebiet.level_start LIMIT 1)
											 ) minpkte
									   )
								   ELSE
									   (
										   SELECT sum(pkte)
										   FROM (SELECT sum(punkte) AS pkte
												 FROM fragen
												 WHERE fragen.gebiet_id = tbl_gebiet.gebiet_id
												   AND fragen.level = tbl_gebiet.level_start
												 GROUP BY frage_id, nummer
												 ORDER BY nummer
												 LIMIT (SELECT anzahl FROM fragenanzahl WHERE fragenanzahl.gebiet_id = tbl_gebiet.gebiet_id AND fragenanzahl.level = tbl_gebiet.level_start LIMIT 1)
												) sumpkte
									   )
								 END)
					ELSE
							 (CASE WHEN tbl_gebiet.multipleresponse=false THEN
									   (
										   SELECT sum(pkte)
										   FROM (SELECT min(punkte) AS pkte
												 FROM fragen
												 WHERE fragen.gebiet_id = tbl_gebiet.gebiet_id
												 GROUP BY frage_id, nummer
												 ORDER BY nummer
												 LIMIT (SELECT anzahl FROM fragenanzahl WHERE fragenanzahl.gebiet_id = tbl_gebiet.gebiet_id LIMIT 1)
												) minpkte
									   )
								   ELSE
									   (
										   SELECT sum(pkte)
										   FROM (SELECT sum(punkte) AS pkte
												 FROM fragen
												 WHERE fragen.gebiet_id = tbl_gebiet.gebiet_id
												 GROUP BY frage_id, nummer
												 ORDER BY nummer
												 LIMIT (SELECT anzahl FROM fragenanzahl WHERE fragenanzahl.gebiet_id = tbl_gebiet.gebiet_id LIMIT 1)
												) sumpkte
									   )
								 END)
			
					END) * (-1) AS offsetpunkte
			FROM
				testtool.tbl_gebiet
			WHERE
				EXISTS(
						SELECT 1 FROM fragen WHERE fragen.gebiet_id=tbl_gebiet.gebiet_id
					)
			AND gebiet_id = ".$this->db_add_param($gebiet_id, FHC_INTEGER)."
		";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				return $row->offsetpunkte;
			}
			else
			{
				$this->errormsg = 'Fehler beim Ermitteln der Offsetpunkte';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Ermitteln der Offsetpunkte';
			return false;
		}
	}

	/**
	 * Prueft ob das Gebiet bereits gestartet wurde.  Wahlweise pruefling_id oder prestudent_id
	 *
	 * @param $pruefling_id Wahlweise pruefling_id oder
	 * @param $prestudent_id prestudent_id des Prueflings.
	 * @param $gebiet_id Gebiet_id des Gebiets, dessen Start gefprueft werden soll
	 * @return true wenn das Gebiet bereits gestartet wurde, false wenn nicht.
	 */
	public function isGestartet($gebiet_id, $pruefling_id=null, $prestudent_id=null)
	{
		$this->errormsg='';

		if(!is_numeric($gebiet_id) || $gebiet_id=='')
		{
			$this->errormsg = 'Gebiet_id muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_null($pruefling_id) && (!is_numeric($pruefling_id) || $pruefling_id==''))
		{
			$this->errormsg = 'Pruefling_id muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_null($prestudent_id) && (!is_numeric($prestudent_id) || $prestudent_id==''))
		{
			$this->errormsg = 'Prestudent_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = '	SELECT
						begintime
					FROM
						testtool.tbl_pruefling_frage
					JOIN
						testtool.tbl_pruefling USING (pruefling_id)
					JOIN
						testtool.tbl_frage USING (frage_id)
					WHERE ';
		if (!is_null($pruefling_id))
			$qry.='		pruefling_id='.$this->db_add_param($pruefling_id, FHC_INTEGER);
		else
			$qry.='		prestudent_id='.$this->db_add_param($prestudent_id, FHC_INTEGER);

			$qry.='	AND
						gebiet_id='.$this->db_add_param($gebiet_id, FHC_INTEGER).'
					AND
						begintime IS NOT NULL';

	 	if($result = $this->db_query($qry))
        {
            if($this->db_fetch_object($result))
                return true;
            else
            {
                return false;
            }
        }
        else
        {
            $this->errormsg = 'Fehler bei der Abfrage aufgetreten';
            return false;
        }
	}

	/**
	 * Laedt die Ablaufe die zu einem Gebiet zugeordnet sind
	 * @param $gebiet_id
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function loadAblaufGebiet($gebiet_id)
	{
		$qry = "SELECT * FROM testtool.tbl_ablauf WHERE gebiet_id=".$this->db_add_param($gebiet_id, FHC_INTEGER);
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();
				$obj->ablauf_id = $row->ablauf_id;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->gebiet_id = $row->gebiet_id;
				$obj->reihung = $row->reihung;
				$obj->gewicht = $row->gewicht;
				$obj->semester = $row->semester;
				$obj->ablauf_vorgaben_id = $row->ablauf_vorgaben_id;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;

				$this->result[] = $obj;
			}
			return false;
		}
	}

	/**
	 * Loescht ein Gebiet und den zugeordneten Ablauf
	 * @param $gebiet_id
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function delete($gebiet_id)
	{
		$qry = "SELECT * FROM testtool.tbl_frage WHERE gebiet_id=".$this->db_add_param($gebiet_id, FHC_INTEGER);

		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result)>0)
			{
				$this->errormsg = 'Bitte entfernen Sie zuerst alle Fragen aus dem Gebiet';
				return false;
			}
		}

		$qry = "
			DELETE FROM testtool.tbl_ablauf WHERE gebiet_id=".$this->db_add_param($gebiet_id, FHC_INTEGER)."
			DELETE FROM testtool.tbl_gebiet WHERE gebiet_id=".$this->db_add_param($gebiet_id, FHC_INTEGER);

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Loeschen des Gebiets';
			return false;
		}
	}

	/**
	 * Loescht einen Ablauf
	 * @param $ablauf_id
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function deleteAblaufZuordnung($ablauf_id)
	{
		$qry = "DELETE FROM testtool.tbl_ablauf WHERE ablauf_id=".$this->db_add_param($ablauf_id, FHC_INTEGER);

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Entfernen des Ablaufs';
			return false;
		}
	}

	/**
	 * Laedt alle AblaufVorabe Eintraege
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function getAblaufVorgaben()
	{
		$qry = "SELECT * FROM testtool.tbl_ablauf_vorgaben ORDER BY studiengang_kz";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();

				$obj->ablauf_vorgaben_id = $row->ablauf_vorgaben_id;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->sprache = $row->sprache;
				$obj->sprachwahl = $this->db_parse_bool($row->sprachwahl);
				$obj->content_id = $row->content_id;

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
	 * Speichert den Ablauf
	 */
	public function saveAblauf()
	{
		if($this->new)
		{
			$qry = "INSERT INTO testtool.tbl_ablauf (studiengang_kz, gebiet_id, reihung,gewicht,semester,ablauf_vorgaben_id, insertamum, insertvon) VALUES(".
				$this->db_add_param($this->studiengang_kz, FHC_INTEGER).','.
				$this->db_add_param($this->gebiet_id, FHC_INTEGER).','.
				$this->db_add_param($this->reihung, FHC_INTEGER).','.
				$this->db_add_param($this->gewicht, FHC_INTEGER).','.
				$this->db_add_param($this->semester, FHC_INTEGER).','.
				$this->db_add_param($this->ablauf_vorgaben_id, FHC_INTEGER).','.
				$this->db_add_param($this->insertamum).','.
				$this->db_add_param($this->insertvon).');';
		}
		else
		{
			$this->errormsg='not implemented';
			return false;
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return true;
		}
	}

	/**
	 * Prueft, ob das Gebiet zumindest eine Frage oder einen Vorschlag im MathML Format hat.
	 * @param $gebiet_id
	 * return true, wenn Gebiet eine/n Frage/Vorschlag im MathML Format enthält.
	 */
	public function hasMathML($gebiet_id)
	{
		if (is_numeric($gebiet_id))
		{
			$qry = '
            WITH
               fragen AS (
                  SELECT DISTINCT
                     frage_id
                  FROM
                     testtool.tbl_frage
                  JOIN
                     testtool.tbl_gebiet USING (gebiet_id)
                  WHERE
                     tbl_gebiet.gebiet_id = '. $this->db_add_param($gebiet_id, FHC_INTEGER). '
               ),
               vorschlaege AS (
                  SELECT DISTINCT
                     vorschlag_id
                  FROM
                     testtool.tbl_vorschlag
                  JOIN
                     fragen USING (frage_id)
               )

            SELECT
               1
            FROM
               testtool.tbl_frage_sprache
            JOIN
               fragen USING (frage_id)
            WHERE
               SUBSTRING(text, \'MathML\') IS NOT NULL

            UNION

            SELECT
               1
            FROM
               testtool.tbl_vorschlag_sprache
            JOIN
               vorschlaege USING (vorschlag_id)
            WHERE
               SUBSTRING(text, \'MathML\') IS NOT NULL
         ';

			if($result = $this->db_query($qry))
			{
				return ($this->db_num_rows($result) > 0);
			}
		}
		else
		{
			$this->errormsg = 'Eine numerische gebiet_id muss übergeben werden.';
			return false;
		}
	}
}
?>
