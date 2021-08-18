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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>
 * 			Karl Burkhart <burkhart@technikum-wien.at>
 */
/**
 * Klasse projekttask
 * @create 2011-05-23
 */
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/projekttask.class.php');

class projektphase extends basis_db
{
	public $new;       		// boolean
	public $result = array(); 	// adresse Objekt

	//Tabellenspalten
	public $projektphase_id;    //integer
	public $projekt_kurzbz;	    //string
	public $projektphase_fk;	    //string
	public $bezeichnung;	    //string
	public $typ='Projektphase';	    	//string
	public $beschreibung;	    //string
	public $start;		    //date
	public $ende;		    //date
	public $personentage;	    //integer
    public $farbe;
	public $budget;	    	// numeric
	public $ressource_id;	    // bigint
	public $ressource_bezeichnung;	    // string
	public $insertamum;	    // timestamp
	public $insertvon;	    // bigint
	public $updateamum;	    // timestamp
	public $updatevon;	    // bigint


	/**
	 * Konstruktor
	 * @param $projekt_kurzbz ID der Projektarbeit, die geladen werden soll (Default=null)
	 */
	public function __construct($projektphase_id=null)
	{
		parent::__construct();

		if($projektphase_id != null)
			$this->load($projektphase_id);
	}

	/**
	 * Laedt die Projektphase mit der ID $projektphase_id
	 * @param  $projektphase_id ID der zu ladenden Projektphase
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($projektphase_id)
	{
		if(!is_numeric($projektphase_id))
		{
			$this->errormsg = 'Projektarbeit_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT tbl_projektphase.*, tbl_ressource.bezeichnung AS ressource_bezeichnung
				FROM fue.tbl_projektphase LEFT OUTER JOIN fue.tbl_ressource USING (ressource_id)
				WHERE projektphase_id=".$this->db_add_param($projektphase_id, FHC_INTEGER);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->projekt_kurzbz = $row->projekt_kurzbz;
				$this->projektphase_id = $row->projektphase_id;
				$this->projektphase_fk = $row->projektphase_fk;
				$this->bezeichnung = $row->bezeichnung;
				$this->typ = $row->typ;
				$this->beschreibung = $row->beschreibung;
				$this->start = $row->start;
				$this->ende = $row->ende;
				$this->personentage = $row->personentage;
                $this->farbe = $row->farbe;
				$this->budget = $row->budget;
				$this->ressource_id = $row->ressource_id;
				$this->ressource_bezeichnung = $row->ressource_bezeichnung;
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
	 * Laedt die Projektphasen mit zu einem Projekt
	 * @param  $projekt_kurzbz Projekt der zu ladenden Projektphasen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getProjektphasenForFk($projekt_kurzbz, $projektphase_id)
	{
		$this->result=array();
		$qry = "Select * from fue.tbl_projektphase where projekt_kurzbz = ".$this->db_add_param($projekt_kurzbz)." and projektphase_id not in (
		WITH RECURSIVE tasks(projektphase_fk) as
		(
			SELECT projektphase_id FROM fue.tbl_projektphase
			WHERE projektphase_fk=".$this->db_add_param($projektphase_id, FHC_INTEGER)."
			UNION ALL
			SELECT p.projektphase_id FROM fue.tbl_projektphase p, tasks
			WHERE p.projektphase_fk=tasks.projektphase_fk
		) SELECT *
		FROM tasks) and projektphase_id not in (".$this->db_add_param($projektphase_id, FHC_INTEGER).")";
		//echo "\n".$qry."\n";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new projektphase();

				$obj->projekt_kurzbz = $row->projekt_kurzbz;
				$obj->projektphase_id = $row->projektphase_id;
				$obj->projektphase_fk = $row->projektphase_fk;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->typ = $row->typ;
				$obj->beschreibung = $row->beschreibung;
				$obj->start = $row->start;
				$obj->ende = $row->ende;
				//$obj->personentage = $row->personentage;
                $obj->farbe = $row->farbe;
				$obj->budget = $row->budget;
				$obj->ressource_id = $row->ressource_id;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;

				$this->result[] = $obj;
			}
			//var_dump($this->result);
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Laedt die Projektphasen zu einem Projekt
	 * @param  $projekt_kurzbz Projekt der zu ladenden Projektphasen
	 * @param  $foreignkey wenn ! gib nur die Erste Ebene der Projektphasen zurück
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getProjektphasen($projekt_kurzbz, $foreignkey = null)
	{
		$this->result=array();
		$qry = "SELECT tbl_projektphase.*, tbl_ressource.bezeichnung AS ressource_bezeichnung
				FROM fue.tbl_projektphase LEFT OUTER JOIN fue.tbl_ressource USING (ressource_id)
				WHERE projekt_kurzbz=".$this->db_add_param($projekt_kurzbz);
		//echo "\n".$qry."\n";

		if(!is_null($foreignkey))
			$qry .= " and projektphase_fk is NULL";

		$qry .= " ORDER BY start, projektphase_fk DESC;";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new projektphase();

				$obj->projekt_kurzbz = $row->projekt_kurzbz;
				$obj->projektphase_id = $row->projektphase_id;
				$obj->projektphase_fk = $row->projektphase_fk;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->typ = $row->typ;
				$obj->beschreibung = $row->beschreibung;
				$obj->start = $row->start;
				$obj->ende = $row->ende;
				//$obj->personentage = $row->personentage;
                $obj->farbe = $row->farbe;
				$obj->budget = $row->budget;
				$obj->ressource_id = $row->ressource_id;
				$obj->ressource_bezeichnung = $row->ressource_bezeichnung;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;

				$this->result[] = $obj;
			}
			//var_dump($this->result);
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

    /**
     * Lädt alle Unterphasen zu einem Projekt
     * @param type $phase_id
     * @return boolean
     */
    public function getAllUnterphasen($phase_id)
    {
        $qry = "SELECT tbl_projektphase.*, tbl_ressource.bezeichnung AS ressource_bezeichung
				FROM fue.tbl_projektphase LEFT OUTER JOIN fue.tbl_ressource USING (ressource_id)
				WHERE projektphase_fk =".$this->db_add_param($phase_id, FHC_INTEGER);

        if($result = $this->db_query($qry))
        {
            while($row = $this->db_fetch_object())
            {
                $obj = new projektphase();

				$obj->projekt_kurzbz = $row->projekt_kurzbz;
				$obj->projektphase_id = $row->projektphase_id;
				$obj->projektphase_fk = $row->projektphase_fk;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->typ = $row->typ;
				$obj->beschreibung = $row->beschreibung;
				$obj->start = $row->start;
				$obj->ende = $row->ende;
				//$obj->personentage = $row->personentage;
                $obj->farbe = $row->farbe;
				$obj->budget = $row->budget;
				$obj->ressource_id = $row->ressource_id;
				$obj->ressource_bezeichnung = $row->ressource_bezeichnung;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;

				$this->result[] = $obj;
            }
            return true;
        }
        else
        {
            $this->errormsg = "Fehler beim laden der Daten";
            return false;
        }
    }

	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{

		//Gesamtlaenge pruefen

		if(mb_strlen($this->bezeichnung)>32)
		{
			$this->errormsg='Bezeichnung darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->typ)>32)
		{
			$this->errormsg='Typ darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->projekt_kurzbz)>16)
		{
			$this->errormsg.='Projekt Kurzbz darf nicht länger als 16 Zeichen sein';
			return false;
		}

		$this->errormsg = '';
		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $projekt_kurzbz aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($new=null)
	{
		//Variablen pruefen
		//if(!$this->validate())
		//	return false;

		if($new==null)
			$new = $this->new;

		if($new)
		{
			//Neuen Datensatz einfuegen

			$qry='BEGIN; INSERT INTO fue.tbl_projektphase (projekt_kurzbz, projektphase_fk, bezeichnung, typ,
				beschreibung, start, ende, budget, ressource_id, insertvon, insertamum, updatevon, updateamum, farbe, personentage) VALUES ('.
			     $this->db_add_param($this->projekt_kurzbz).', '.
			     $this->db_add_param($this->projektphase_fk).', '.
				 $this->db_add_param($this->bezeichnung).', '.
			     $this->db_add_param($this->typ).', '.
			     $this->db_add_param($this->beschreibung).', '.
			     $this->db_add_param($this->start).', '.
			     $this->db_add_param($this->ende).', '.
			     $this->db_add_param($this->budget).', '.
			     $this->db_add_param($this->ressource_id).', '.
			     $this->db_add_param($this->insertvon).', now(), '.
			     $this->db_add_param($this->updatevon).', now(), '.
                 $this->db_add_param($this->farbe).', '.
			     $this->db_add_param($this->personentage).' );';
		}
		else
		{
			//Updaten des bestehenden Datensatzes

			$qry='UPDATE fue.tbl_projektphase SET '.
				'projekt_kurzbz='.$this->db_add_param($this->projekt_kurzbz).', '.
				'projektphase_fk='.$this->db_add_param($this->projektphase_fk).', '.
				'bezeichnung='.$this->db_add_param($this->bezeichnung).', '.
				'typ='.$this->db_add_param($this->typ).', '.
				'beschreibung='.$this->db_add_param($this->beschreibung).', '.
				'start='.$this->db_add_param($this->start).', '.
				'ende='.$this->db_add_param($this->ende).', '.
				'budget='.$this->db_add_param($this->budget).', '.
                'ressource_id='.$this->db_add_param($this->ressource_id).', '.
                'farbe='.$this->db_add_param($this->farbe).', '.
				'personentage='.$this->db_add_param($this->personentage).', '.
				'updateamum= now(), '.
				'updatevon='.$this->db_add_param($this->updatevon).' '.
				'WHERE projektphase_id='.$this->db_add_param($this->projektphase_id, FHC_INTEGER).';';
		}

		if($this->db_query($qry))
		{
			if($new)
			{
				//Sequence auslesen
				$qry = "SELECT currval('fue.seq_projektphase_projektphase_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->projektphase_id = $row->id;
						$this->db_query('COMMIT');
						return true;
					}
					else
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						$this->db_query('ROLLBACK;');
						return false;
					}
				}
				else
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					$this->db_query('ROLLBACK;');
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
	 * @param $projekt_kurzbz ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($projektphase_id)
	{
		if(!is_numeric($projektphase_id))
		{
			$this->errormsg = 'Projektphase_ID ist ungueltig';
			return true;
		}

		// an projektphase hängt noch eine phase
		if($this->existPhaseFk($projektphase_id))
		{
			$this->errormsg ="Phase kann nicht gelöscht werden, da noch eine andere Phase daran hängt. Bitte zuerst Phase abhängen. ";
			return false;
		}

		// Beginne Transaktion und lösche alle Tasks der Phase
		$qry1 ="Begin; DELETE FROM fue.tbl_projekttask
		WHERE projektphase_id =".$this->db_add_param($projektphase_id, FHC_INTEGER).";";

		if($this->db_query($qry1))
		{
			// Lösche alle zugewiesenen Ressourcen
			$qry2 = "DELETE FROM fue.tbl_projekt_ressource
			WHERE projektphase_id =".$this->db_add_param($projektphase_id, FHC_INTEGER).";";

			if($this->db_query($qry2))
			{
				// Lösche den Phaseneintrag
				$qry3 = "DELETE FROM fue.tbl_projektphase
				WHERE projektphase_id = ".$this->db_add_param($projektphase_id, FHC_INTEGER).";";

				if($this->db_query($qry3))
				{
					$this->db_query('COMMIT');
					return true;
				}
				else
				{
					$this->errormsg ="Fehler beim löschen der Projektphase aufgetreten";
					$this->db_query('ROLLBACK');
					return false;
				}
			}
			else
			{
				$this->errormsg ="Fehler beim löschen der Ressourcen aufgetreten";
				$this->db_query('ROLLBACK');
				return false;
			}
		}
		else
		{
			$this->errormsg ="Fehler beim löschen der Tasks aufgetreten";
			$this->db_query('ROLLBACK');
			return false;
		}

	}

	/**
	 *
	 * Überprüft ob an übergebenr Phase noch eine andere Phase hängt. true wenn noch eine daran hängt
	 * @param $projektphase_id
	 */
	public function existPhaseFk($projektphase_id)
	{
		$qry = "SELECT * FROM fue.tbl_projektphase WHERE projektphase_fk =".$this->db_add_param($projektphase_id, FHC_INTEGER).";";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
				return true;
		}
		else
		{
			$this->errormsg ="Fehler bei der Abfrage aufgetreten";
			return false;
		}
	}

	/**
	 *
	 * Löscht Ressourcen einer Phase
	 * @param $projektphase_id
	 * @param $ressource_id -> wenn != null wird nur die eine ressource gelöscht
	 */
	public function deleteRessource($projektphase_id, $ressource_id = null)
	{
		// einzelne Ressource wird gelöscht
		if($ressource_id != null)
		{
			if(!is_numeric($projektphase_id) || !is_numeric($ressource_id))
			{
				$this->errormsg = "Keine gültige ID übergeben";
				return false;
			}
			$qry ="DELETE from fue.tbl_projekt_ressource
					WHERE projektphase_id =".$this->db_add_param($projektphase_id, FHC_INTEGER)." and
					ressource_id=".$this->db_add_param($ressource_id, FHC_INTEGER).";";
		}
		else
		{
			// gesamte Ressourcen von Phase werden gelöscht
			if(!is_numeric($projektphase_id))
			{
				$this->errormsg ="Keine gültige ID übergeben";
			}
			$qry ="DELETE from fue.tbl_projekt_ressource
					WHERE projektphase_id =".$this->db_add_param($projektphase_id, FHC_INTEGER).";";
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = "Fehler bei der Abfrage aufgetreten";
			return false;
		}

	}

	/**
	 *
	 * gibt den Fortschritt der Phase in Prozent zurück --> Phasen die auf die übergebene Phase zeigen werden berücksichtigt
	 * @param $projektphase_id
	 */
public function getFortschritt($projektphase_id)
	{
		$qry = "Select * from fue.tbl_projektphase phase
		join fue.tbl_projekttask task using(projektphase_id)
		where task.projektphase_id = ".$this->db_add_param($projektphase_id, FHC_INTEGER)."
		OR task.projektphase_id IN (

		WITH RECURSIVE tasks(projektphase_fk) as
		(
			SELECT projektphase_id FROM fue.tbl_projektphase
			WHERE projektphase_fk=".$this->db_add_param($projektphase_id, FHC_INTEGER)."
			UNION ALL
			SELECT p.projektphase_id FROM fue.tbl_projektphase p, tasks
			WHERE p.projektphase_fk=tasks.projektphase_fk
		)SELECT *
		FROM tasks)";

		$taskAnzahl = 0;
		// erledige tasks
		$i = 0;
		$ergebnis = 0;

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$taskAnzahl++;
				if($row->erledigt == 't')
					$i++;
			}
		}
		$taskAnzahl = ($taskAnzahl == 0)? 1 : $taskAnzahl;
		$ergebnis = ($i*100)/$taskAnzahl;

		return sprintf("%01.2f", $ergebnis);
	}

	/**
	 * Überprüft ob alle Tasks einer Phase erledigt sind
	 */
	public function isPhaseErledigt($phase_id)
	{
		$task = new projekttask();

		$task->getProjekttasks($phase_id,null,'offen');
		if(count($task->result)==0)
			return true;
		else
			return false;
	}

	public function checkProjectphaseInCorrectTime($projektphase_id, $given_projectphase_start, $given_projektphase_ende)
	{
		if(empty($projektphase_id))
			return true;
		try
		{
			$projektphase = $this->getProjectphaseById($projektphase_id);
			if(strtotime($projektphase->start))
			{
				$projektphase_start = date('Y-m-d', strtotime($projektphase->start));
			}
			else
			{
				$projektphase_start = NULL;
			}
			if(strtotime($projektphase->ende))
			{
				$projektphase_ende = date('Y-m-d', strtotime($projektphase->ende));
			}
			else
			{
				$projektphase_ende = NULL;
			}

			$given_start = date('Y-m-d', strtotime($given_projectphase_start));
			$given_ende = date('Y-m-d', strtotime($given_projektphase_ende));

			if ((empty($projektphase_start) || $given_start >= $projektphase_start)
			&& (empty($projektphase_ende) || $given_ende <= $projektphase_ende))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		catch (Exception $e)
		{
			error_log('Exception abgefangen: ',  $e->getMessage(), "\n");
		}
	}

	/**
	 * Laedt die Projektphase mit der ID $projektphase_id
	 * @param  $projektphase_id ID der zu ladenden Projektphase
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getProjectphaseById($projektphase_id)
	{
		if(!is_numeric($projektphase_id))
		{
			$this->errormsg = 'Projektarbeit_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT tbl_projektphase.*, tbl_ressource.bezeichnung AS ressource_bezeichnung
				FROM fue.tbl_projektphase LEFT OUTER JOIN fue.tbl_ressource USING (ressource_id)
				WHERE projektphase_id=".$this->db_add_param($projektphase_id, FHC_INTEGER);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$obj = new projektphase();
				$obj->projekt_kurzbz = $row->projekt_kurzbz;
				$obj->projektphase_id = $row->projektphase_id;
				$obj->projektphase_fk = $row->projektphase_fk;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->typ = $row->typ;
				$obj->beschreibung = $row->beschreibung;
				$obj->start = $row->start;
				$obj->ende = $row->ende;
				$obj->personentage = $row->personentage;
				$obj->farbe = $row->farbe;
				$obj->budget = $row->budget;
				$obj->ressource_id = $row->ressource_id;
				$obj->ressource_bezeichnung = $row->ressource_bezeichnung;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;

				return $obj;
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
	 * Laedt die Projektphase mit der ID des mitarbeiters
	 * @param  $mitarbeiter_uid der zu ladenden Projektphase des users
	 * @return array wenn ok, false im Fehlerfall
	 */
	public function getProjectphaseForMitarbeiter($mitarbeiter_uid)
	{
		$projecphasetList = array();

		$qry = "
		SELECT
			DISTINCT tbl_projektphase.*
		FROM
			fue.tbl_projektphase
			JOIN fue.tbl_projekt USING (projekt_kurzbz)
			JOIN fue.tbl_projekt_ressource USING (projektphase_id)
			JOIN fue.tbl_ressource ON (tbl_ressource.ressource_id=tbl_projekt_ressource.ressource_id)
		WHERE
		(
			(
				(tbl_projekt.beginn<=now() or tbl_projekt.beginn is null)
				AND (tbl_projekt.ende + interval '1 month 1 day' >=now() OR tbl_projekt.ende is null)
			) AND (
				(tbl_projektphase.start<=now() or tbl_projektphase.start is null)
				AND (tbl_projektphase.ende + interval '1 month 1 day' >=now() OR tbl_projektphase.ende is null)
			)
		)
		AND mitarbeiter_uid=" . $this->db_add_param($mitarbeiter_uid);

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new projektphase();

				$obj->projekt_kurzbz = $row->projekt_kurzbz;
				$obj->projektphase_id = $row->projektphase_id;
				$obj->projektphase_fk = $row->projektphase_fk;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->typ = $row->typ;
				$obj->beschreibung = $row->beschreibung;
				$obj->start = $row->start;
				$obj->ende = $row->ende;
				$obj->personentage = $row->personentage;
				$obj->farbe = $row->farbe;
				$obj->budget = $row->budget;
				$obj->ressource_id = $row->ressource_id;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;

				$this->result[] = $obj;

				array_push($projecphasetList, $obj);
			}
			return $projecphasetList;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Laedt die Projektphase mit der ID des mitarbeiters für das jeweilige Projekt
	 * @param  $mitarbeiter_uid der zu ladenden Projektphase des users
	 * @param  $projekt_kurzbz des zu landenen Projekts
	 * @return array wenn ok, false im Fehlerfall
	 */
	public function getProjectphaseForMitarbeiterByKurzBz($mitarbeiter_uid, $projekt_kurzbz)
	{
		$projecphasetList = array();

		$qry = "
		SELECT
			DISTINCT tbl_projektphase.*
		FROM
			fue.tbl_projektphase
			JOIN fue.tbl_projekt USING (projekt_kurzbz)
			JOIN fue.tbl_projekt_ressource USING (projektphase_id)
			JOIN fue.tbl_ressource ON (tbl_ressource.ressource_id=tbl_projekt_ressource.ressource_id)
		WHERE
		(
			(
				(tbl_projekt.beginn<=now() or tbl_projekt.beginn is null)
				AND (tbl_projekt.ende + interval '1 month 1 day' >=now() OR tbl_projekt.ende is null)
			) AND (
				(tbl_projektphase.start<=now() or tbl_projektphase.start is null)
				AND (tbl_projektphase.ende + interval '1 month 1 day' >=now() OR tbl_projektphase.ende is null)
			)
		)
		AND mitarbeiter_uid = ".$this->db_add_param($mitarbeiter_uid)."
		AND tbl_projekt.projekt_kurzbz = ".$this->db_add_param($projekt_kurzbz);

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new projektphase();

				$obj->projekt_kurzbz = $row->projekt_kurzbz;
				$obj->projektphase_id = $row->projektphase_id;
				$obj->projektphase_fk = $row->projektphase_fk;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->typ = $row->typ;
				$obj->beschreibung = $row->beschreibung;
				$obj->start = $row->start;
				$obj->ende = $row->ende;
				$obj->personentage = $row->personentage;
				$obj->farbe = $row->farbe;
				$obj->budget = $row->budget;
				$obj->ressource_id = $row->ressource_id;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;

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
}
?>
