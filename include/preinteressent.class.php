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
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/datum.class.php');

class preinteressent extends basis_db
{
	public $new;		// boolean
	public $result = array();
	
	//Tabellenspalten
	public $preinteressent_id;			// serial
	public $person_id;
	public $studiensemester_kurzbz;	// varchar(16)
	public $aufmerksamdurch_kurzbz;	// varchar(16)
	public $firma_id;					// integer
	public $anmerkung;					// text
	public $erfassungsdatum;			// date
	public $einverstaendnis;			// boolean
	public $absagedatum;				// timestamp
	public $insertamum;				// timestamp
	public $insertvon;					// varchar(16)
	public $updateamum;				// timestamp
	public $updatevon;					// varchar(16)
	public $maturajahr;				// numeric(4,0)
	public $infozusendung;				// date
	public $kontaktmedium_kurzbz;		// varchar(32)
		
	public $studiengang_kz;
	public $prioritaet;		// smallint
	public $prioritaet_arr = array('1'=>'niedrg', '2'=>'mittel', '3'=>'hoch');
	public $freigabedatum;		// timestamp
	public $uebernahmedatum;	// timestamp
		
	/**
	 * Konstruktor
	 * @param preinteressent_id ID des zu ladenden Datensatzes
	 */
	public function __construct($preinteressent_id=null)
	{
		parent::__construct();
		
		if($preinteressent_id != null)
			$this->load($preinteressent_id);
	}
	
	/**
	 * Laedt einen Datensatz
	 * @param preinteressent_id ID des zu ladenden Datensatzes
	 */
	public function load($preinteressent_id)
	{
		//id auf Gueltigkeit pruefen
		if(!is_numeric($preinteressent_id) || $preinteressent_id == '')
		{
			$this->errormsg = 'preinteressent_id muss eine gueltige Zahl sein';
			return false;
		}
		
		//laden des Datensatzes
		$qry = "SELECT * FROM public.tbl_preinteressent WHERE preinteressent_id=".$this->db_add_param($preinteressent_id, FHC_INTEGER).";";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->preinteressent_id = $row->preinteressent_id;
				$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$this->aufmerksamdurch_kurzbz = $row->aufmerksamdurch_kurzbz;
				$this->firma_id = $row->firma_id;
				$this->anmerkung = $row->anmerkung;
				$this->erfassungsdatum = $row->erfassungsdatum;
				$this->einverstaendnis = $this->db_parse_bool($row->einverstaendnis);
				$this->maturajahr = $row->maturajahr;
				$this->infozusendung = $row->infozusendung;
				$this->absagedatum = $row->absagedatum;
				$this->person_id = $row->person_id;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->kontaktmedium_kurzbz = $row->kontaktmedium_kurzbz;
				return true;		
			}
			else 
			{
				$this->errormsg = 'Fehler bei der Datenbankabfrage';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}
	}
			
	/**
	 * Loescht einen Datensatz, erstellt einen UNDO Befehl
	 * und einen LOG Eintrag
	 * @param preinteressent_id ID des zu loeschenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($preinteressent_id)
	{
		//id auf Gueltigkeit pruefen
		if(!is_numeric($preinteressent_id) || $preinteressent_id == '')
		{
			$this->errormsg = 'preinteressent_id muss eine gueltige Zahl sein';
			return false;
		}
		$undo='';
		//UNDO Befehl zusammenbauen
		$this->db_query('BEGIN;');
		
		$qry = "SELECT * FROM public.tbl_preinteressent WHERE preinteressent_id = ".$this->db_add_param($preinteressent_id, FHC_INTEGER);
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$undo.=" INSERT INTO public.tbl_preinteressent(preinteressent_id, person_id, studiensemester_kurzbz, 
						aufmerksamdurch_kurzbz, firma_id, erfassungsdatum, einverstaendnis, absagedatum, anmerkung, 
						insertamum, insertvon, updateamum, updatevon, maturajahr, infozusendung, kontaktmedium_kurzbz) VALUES (".
				 		$this->db_add_param($row->preinteressent_id, FHC_INTEGER).', '.
				 		$this->db_add_param($row->person_id, FHC_INTEGER).', '.
						$this->db_add_param($row->studiensemester_kurzbz).', '.
						$this->db_add_param($row->aufmerksamdurch_kurzbz).', '.
						$this->db_add_param($row->firma_id, FHC_INTEGER).', '.
						$this->db_add_param($row->erfassungsdatum).', '.
						$this->db_add_param($this->db_parse_bool($row->einverstaendnis), FHC_BOOLEAN).', '.
						$this->db_add_param($row->absagedatum).', '.
						$this->db_add_param($row->anmerkung).', '.
						$this->db_add_param($row->insertamum).', '.
						$this->db_add_param($row->insertvon).','.
						$this->db_add_param($row->updateamum).', '.
						$this->db_add_param($row->updatevon).', '.
						$this->db_add_param($row->maturajahr).', '.
						$this->db_add_param($row->infozusendung).', '.
						$this->db_add_param($row->kontaktmedium_kurzbz).');';
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Erstellen des UNDO Befehls';
			$this->db_query('ROLLBACK');
			return false;
		}
		
		
		$qry = "SELECT * FROM public.tbl_preinteressentstudiengang WHERE preinteressent_id=".$this->db_add_param($preinteressent_id, FHC_INTEGER);
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$undo.=" INSERT INTO public.tbl_preinteressentstudiengang(studiengang_kz, preinteressent_id, prioritaet, 
						freigabedatum, uebernahmedatum, insertamum, insertvon, updateamum, updatevon) VALUES(".
						$this->db_add_param($row->studiengang_kz, FHC_INTEGER).','.
						$this->db_add_param($row->preinteressent_id, FHC_INTEGER).','.
						$this->db_add_param($row->prioritaet).','.
						$this->db_add_param($row->freigabedatum).','.
						$this->db_add_param($row->uebernahmedatum).','.
						$this->db_add_param($row->insertamum).','.
						$this->db_add_param($row->insertvon).','.
						$this->db_add_param($row->updateamum).','.
						$this->db_add_param($row->updatevon).');';
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Erstellen des UNDO Befehls';
			$this->db_query('ROLLBACK');
			return false;
		}
		
		$qry = "DELETE FROM public.tbl_preinteressentstudiengang WHERE preinteressent_id=".$this->db_add_param($preinteressent_id, FHC_INTEGER).";
				DELETE FROM public.tbl_preinteressent WHERE preinteressent_id = ".$this->db_add_param($preinteressent_id, FHC_INTEGER).";";
		
		if($this->db_query($qry))
		{
			//Log schreiben
			$log = new log();
			
			$log->new = true;
			$log->sql = $qry;
			$log->sqlundo = $undo;
			$log->executetime = date('Y-m-d H:i:s');
			$log->mitarbeiter_uid = get_uid();
			$log->beschreibung = "Preinteressent loeschen - $preinteressent_id";

			if(!$log->save())
			{
				$this->errormsg = 'Fehler beim Schreiben des Log-Eintrages';
				$this->db_query('ROLLBACK');
				return false;
			}
			
			$this->db_query('COMMIT;');
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Loeschen der Daten';
			$this->db_query('ROLLBACK');
			return false;
		}
	}
	
	/**
	 * Prueft die Daten vor dem Speichern
	 * @return true wenn ok, false wenn fehler
	 */
	protected function validate()
	{
		if($this->person_id=='')
		{
			$this->errormsg = 'Person_id muss angegeben werden';
			return false;
		}
		
		if($this->aufmerksamdurch_kurzbz=='')
		{
			$this->errormsg = 'Aufmerksamdurch muss angegeben werden';
			return false;
		}
		
		return true;
	}
		
	/**
	 * Speichert den aktuellen Datensatz
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $preinteressent_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($new=null)
	{
		if(!$this->validate())
			return false;
		if($new==null)
			$new = $this->new;
			
		if($new)
		{
			//Neuen Datensatz anlegen	
			$qry = "BEGIN;INSERT INTO public.tbl_preinteressent (studiensemester_kurzbz, 
					aufmerksamdurch_kurzbz, firma_id, anmerkung, erfassungsdatum, einverstaendnis, absagedatum,
					maturajahr, infozusendung, person_id, updateamum, updatevon, insertamum, insertvon, kontaktmedium_kurzbz) VALUES (".
			       $this->db_add_param($this->studiensemester_kurzbz).', '.
			       $this->db_add_param($this->aufmerksamdurch_kurzbz).', '.
			       $this->db_add_param($this->firma_id, FHC_INTEGER).', '.
			       $this->db_add_param($this->anmerkung).', '.
			       $this->db_add_param($this->erfassungsdatum).', '.
			       $this->db_add_param($this->einverstaendnis, FHC_BOOLEAN).', '.
			       $this->db_add_param($this->absagedatum).', '.
			       $this->db_add_param($this->maturajahr).', '.
			       $this->db_add_param($this->infozusendung).', '.
			       $this->db_add_param($this->person_id, FHC_INTEGER).', '.
			       $this->db_add_param($this->updateamum).', '.
			       $this->db_add_param($this->updatevon).', '.
			       $this->db_add_param($this->insertamum).', '.
			       $this->db_add_param($this->insertvon).', '.
			       $this->db_add_param($this->kontaktmedium_kurzbz).');';
		}
		else 
		{
			//Bestehenden Datensatz aktualisieren
			$qry= "UPDATE public.tbl_preinteressent SET".
				  " studiensemester_kurzbz=".$this->db_add_param($this->studiensemester_kurzbz).",".
				  " aufmerksamdurch_kurzbz=".$this->db_add_param($this->aufmerksamdurch_kurzbz).",".
				  " firma_id=".$this->db_add_param($this->firma_id, FHC_INTEGER).",".
				  " anmerkung=".$this->db_add_param($this->anmerkung).",".
				  " erfassungsdatum=".$this->db_add_param($this->erfassungsdatum).",".
				  " einverstaendnis=".$this->db_add_param($this->einverstaendnis, FHC_BOOLEAN).",".
				  " absagedatum=".$this->db_add_param($this->absagedatum).",".
				  " maturajahr=".$this->db_add_param($this->maturajahr).",".
				  " infozusendung=".$this->db_add_param($this->infozusendung).",".
				  " person_id=".$this->db_add_param($this->person_id, FHC_INTEGER).",".
				  " updatevon=".$this->db_add_param($this->updatevon).",".
				  " updateamum=".$this->db_add_param($this->updateamum).','.
				  " kontaktmedium_kurzbz=".$this->db_add_param($this->kontaktmedium_kurzbz).
				  " WHERE preinteressent_id=".$this->db_add_param($this->preinteressent_id, FHC_INTEGER);
		}
		
		if($this->db_query($qry))
		{
			if($new)
			{
				$qry = "SELECT currval('public.tbl_preinteressent_preinteressent_id_seq') as id";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->preinteressent_id = $row->id;
						$this->db_query('COMMIT;');
						return true;
					}
					else 
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						$this->db_query('ROLLBACK');
						return false;
					}
				}
				else 
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					$this->db_query('ROLLBACK');
					return false;
				}
			}
			else 
				return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
	}
	
	/**
	 * Laedt die Freigegebenen Preinteressenten
	 * eines Studienganges welche noch nicht
	 * uebernommen wurden
	 * @param $studiengang_kz
	 *        $studiensemester_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadFreigegebene($studiengang_kz, $studiensemester_kurzbz='')
	{
		$qry = "SELECT tbl_preinteressent.*, tbl_preinteressentstudiengang.* FROM public.tbl_preinteressent JOIN public.tbl_preinteressentstudiengang USING(preinteressent_id) JOIN public.tbl_person USING(person_id) WHERE
		(studiengang_kz, person_id) NOT IN (SELECT studiengang_kz, person_id FROM public.tbl_prestudent JOIN public.tbl_prestudentstatus USING(prestudent_id) WHERE status_kurzbz='Interessent' AND studiensemester_kurzbz=tbl_preinteressent.studiensemester_kurzbz AND person_id=tbl_person.person_id) AND freigabedatum is not null AND
		tbl_preinteressentstudiengang.studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER)." AND tbl_preinteressentstudiengang.uebernahmedatum is null"; 
		if($studiensemester_kurzbz!='')
			$qry.=" AND tbl_preinteressent.studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new preinteressent();
				
				$obj->preinteressent_id = $row->preinteressent_id;
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->aufmerksamdurch_kurzbz = $row->aufmerksamdurch_kurzbz;
				$obj->firma_id = $row->firma_id;
				$obj->anmerkung = $row->anmerkung;
				$obj->erfassungsdatum = $row->erfassungsdatum;
				$obj->einverstaendnis = $this->db_parse_bool($row->einverstaendnis);
				$obj->absagedatum = $row->absagedatum;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->maturajahr = $row->maturajahr;
				$obj->infozusendung = $row->infozusendung;
				$obj->person_id = $row->person_id;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->prioritaet = $row->prioritaet;
				$obj->freigabedatum = $row->freigabedatum;
				$obj->uebernahmedatum = $row->uebernahmedatum;
				$obj->kontaktmedium_kurzbz = $row->kontaktmedium_kurzbz;
				
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
	 * 
	 * Laedt die Preinteressenten
	 * 
	 * @param $studiengang_kz
	 * @param $studiensemester_kurzbz
	 * @param $filter Filtert nach Nachname, Vorname, Kontakt, und Erfassungsdatum
	 * @param $nichtfreigegeben  Wenn true werden nur personen geliefert die noch nicht freigegeben wurden
	 * @param $uebernommen Wenn true werden nur Personen geliefert die bereits freigegeben sind aber noch nicht uebernommen
	 * @param $kontaktmedium Wenn -1 werden alle geliefert die das Kontaktmedium nicht gesetzt haben, sonst bei denen das uebergeben Kontaktmedium ausgewaehlt ist
	 * @param $absage
	 * @param $erfassungsdatum_von
	 * @param $erfassungsdatum_bis
	 * @param $einverstaendnis
	 * @param $preinteressent Wenn true werden nur die Personen angezeigt die noch nicht als Interessent oder Student in einem Studiengang vorhanden sind
	 */
	public function loadPreinteressenten($studiengang_kz='', $studiensemester_kurzbz=null, $filter='', $nichtfreigegeben=null, $uebernommen=null, $kontaktmedium=null, $absage=false, $erfassungsdatum_von=null, $erfassungsdatum_bis=null, $einverstaendnis=null, $preinteressent=null)
	{
		$qry = "SELECT distinct tbl_preinteressent.* 
				FROM public.tbl_preinteressent JOIN public.tbl_person USING(person_id) 
					LEFT JOIN public.tbl_preinteressentstudiengang USING(preinteressent_id) 
					LEFT JOIN public.tbl_kontakt USING(person_id) WHERE true";
				
		if($studiengang_kz!='')
			$qry.=" AND tbl_preinteressentstudiengang.studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER);
		
		if(!is_null($studiensemester_kurzbz))
		{
			if($studiensemester_kurzbz=='')
				$qry.=" AND tbl_preinteressent.studiensemester_kurzbz is null";
			else
				$qry.=" AND tbl_preinteressent.studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);
		}
		
		if($filter!='')
		{
			$datum_obj = new datum();
		
			$qry.=" AND (lower(nachname) like lower('%".$this->db_escape($filter)."%') OR lower(vorname) like lower('%".$this->db_escape($filter)."%') OR lower(kontakt) like lower('%".$this->db_escape($filter)."%')";
			if($filter = $datum_obj->formatDatum($filter))
				$qry.=" OR erfassungsdatum = ".$this->db_escape($filter);
			$qry.=")";
		}
		if($nichtfreigegeben==true)
			$qry.=" AND tbl_preinteressentstudiengang.freigabedatum is null";
		if($uebernommen==true)
			$qry.=" AND tbl_preinteressentstudiengang.freigabedatum is not null AND tbl_preinteressentstudiengang.uebernahmedatum is null";
		if(!is_null($kontaktmedium))
		{
			if($kontaktmedium=='-1')
				$qry.=" AND tbl_preinteressent.kontaktmedium_kurzbz is null";
			else
				$qry.=" AND tbl_preinteressent.kontaktmedium_kurzbz=".$this->db_add_param($kontaktmedium);
		}

		if(!is_null($erfassungsdatum_bis))
			$qry.=" AND erfassungsdatum<=".$this->db_add_param($erfassungsdatum_bis);
		
		if(!is_null($erfassungsdatum_von))
			$qry.=" AND erfassungsdatum>=".$this->db_add_param($erfassungsdatum_von);
		
		if($absage)
			$qry.=" AND absagedatum is not null";
		else 
			$qry.=" AND absagedatum is null";
		
		if($einverstaendnis)
			$qry.=" AND einverstaendnis=true";
		
		if($preinteressent)
			$qry.=" AND NOT EXISTS (SELECT * FROM public.tbl_prestudent WHERE person_id=tbl_person.person_id)";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new preinteressent();
				
				$obj->preinteressent_id = $row->preinteressent_id;
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->aufmerksamdurch_kurzbz = $row->aufmerksamdurch_kurzbz;
				$obj->firma_id = $row->firma_id;
				$obj->anmerkung = $row->anmerkung;
				$obj->erfassungsdatum = $row->erfassungsdatum;
				$obj->einverstaendnis = $this->db_parse_bool($row->einverstaendnis);
				$obj->absagedatum = $row->absagedatum;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->maturajahr = $row->maturajahr;
				$obj->infozusendung = $row->infozusendung;
				$obj->person_id = $row->person_id;
				$obj->kontaktmedium_kurzbz = $row->kontaktmedium_kurzbz;
				
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
	 * Laedt die Zuordnung von Preinteressenten zu Studiengaengen
	 *
	 * @param $preinteressent_id
	 * @return booelan
	 */
	public function loadZuordnungen($preinteressent_id)
	{
		if(!is_numeric($preinteressent_id) || $preinteressent_id=='')
		{
			$this->errormsg = 'Preinteressent_id ist ungueltig';
			return false;
		}
		
		$qry = "SELECT * FROM public.tbl_preinteressentstudiengang 
				WHERE preinteressent_id=".$this->db_add_param($preinteressent_id, FHC_INTEGER)." ORDER BY studiengang_kz";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new preinteressent();
				
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->preinteressent_id = $row->preinteressent_id;
				$obj->prioritaet = $row->prioritaet;
				$obj->freigabedatum = $row->freigabedatum;
				$obj->uebernahmedatum = $row->uebernahmedatum;
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
			$this->errormsg = 'Fehler beim Laden der Zuordnung';
			return false;
		}
	}
	
	/**
	 * Laedt eine Zuordnung
	 * @return true wenn ok, false wenn Fehler
	 */
	public function loadZuordnung($preinteressent_id, $studiengang_kz)
	{
		if(!is_numeric($preinteressent_id) || $preinteressent_id=='')
		{
			$this->errormsg = 'Preinteressent_id ist ungueltig';
			return false;
		}
		
		if(!is_numeric($studiengang_kz) || $studiengang_kz=='')
		{
			$this->errormsg = 'studiengang_kz ist ungueltig';
			return false;
		}
		
		$qry = "SELECT * FROM public.tbl_preinteressentstudiengang 
				WHERE preinteressent_id=".$this->db_add_param($preinteressent_id, FHC_INTEGER)." 
				AND studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER);
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->studiengang_kz = $row->studiengang_kz;
				$this->preinteressent_id = $row->preinteressent_id;
				$this->prioritaet = $row->prioritaet;
				$this->freigabedatum = $row->freigabedatum;
				$this->uebernahmedatum = $row->uebernahmedatum;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				return true;
			}
			else 
			{
				$this->errormsg = 'Fehler beim Laden der Zuordnung';
				return false;	
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Zuordnung';
			return false;
		}
	}
	
	/**
	 * Speichert die Studiengangszuordnung eines 
	 * Preinteressent
	 * @return true wenn ok, false wenn Fehler
	 */
	public function saveZuordnung($new=null)
	{
		if($new==null)
			$new = $this->new;
			
		if($new)
		{
			//Neuen Datensatz anlegen	
			$qry = "INSERT INTO public.tbl_preinteressentstudiengang (studiengang_kz, preinteressent_id, 
					prioritaet, freigabedatum, uebernahmedatum, updateamum, updatevon, insertamum, insertvon) VALUES (".
			       $this->db_add_param($this->studiengang_kz).', '.
			       $this->db_add_param($this->preinteressent_id, FHC_INTEGER).', '.
			       $this->db_add_param($this->prioritaet).', '.
			       $this->db_add_param($this->freigabedatum).', '.
			       $this->db_add_param($this->uebernahmedatum).', '.
			       $this->db_add_param($this->updateamum).', '.
			       $this->db_add_param($this->updatevon).', '.
			       $this->db_add_param($this->insertamum).', '.
			       $this->db_add_param($this->insertvon).');';
		}
		else 
		{
			//Bestehenden Datensatz aktualisieren
			$qry= "UPDATE public.tbl_preinteressentstudiengang SET".
				  " prioritaet=".$this->db_add_param($this->prioritaet).",".
				  " freigabedatum=".$this->db_add_param($this->freigabedatum).",".
				  " uebernahmedatum=".$this->db_add_param($this->uebernahmedatum).",".
				  " updatevon=".$this->db_add_param($this->updatevon).",".
				  " updateamum=".$this->db_add_param($this->updateamum).
				  " WHERE preinteressent_id=".$this->db_add_param($this->preinteressent_id, FHC_INTEGER)."
					AND studiengang_kz=".$this->db_add_param($this->studiengang_kz, FHC_INTEGER);
		}
		
		if($this->db_query($qry))
		{
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
	}
	
	/**
	 * Loescht eine Zuordnung
	 * @return true wenn ok, false wenn Fehler
	 */
	public function deleteZuordnung($preinteressent_id, $studiengang_kz)
	{
		if(!is_numeric($preinteressent_id) || $preinteressent_id=='')
		{
			$this->errormsg = 'Preinteressent_id ist ungueltig';
			return false;
		}
		
		if(!is_numeric($studiengang_kz) || $studiengang_kz=='')
		{
			$this->errormsg = 'Studiengang_kz ist ungueltig';
			return false;
		}
		
		$qry = "DELETE FROM public.tbl_preinteressentstudiengang 
				WHERE preinteressent_id=".$this->db_add_param($preinteressent_id, FHC_INTEGER)." AND studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER);
		
		if($this->db_query($qry))
		{
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Loeschen der Daten';
			return false;
		}
	}
	
	/**
	 * Laedt alle Preinteressenten einer Person
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getPreinteressenten($person_id)
	{
		if(!is_numeric($person_id) || $person_id=='')
		{
			$this->errormsg = 'ID ist ungueltig';
			return false;
		}
		
		$qry = "SELECT * FROM public.tbl_preinteressent WHERE person_id=".$this->db_add_param($person_id, FHC_INTEGER);
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new preinteressent();
				
				$obj->preinteressent_id = $row->preinteressent_id;
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->aufmerksamdurch_kurzbz = $row->aufmerksamdurch_kurzbz;
				$obj->firma_id = $row->firma_id;
				$obj->anmerkung = $row->anmerkung;
				$obj->erfassungsdatum = $row->erfassungsdatum;
				$obj->einverstaendnis = $this->db_parse_bool($row->einverstaendnis);
				$obj->maturajahr = $row->maturajahr;
				$obj->infozusendung = $row->infozusendung;
				$obj->absagedatum = $row->absagedatum;
				$obj->person_id = $row->person_id;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->kontaktmedium_kurzbz = $row->kontaktmedium_kurzbz;
				
				$this->result[] = $obj;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Zuordnung';
			return false;
		}
	}
}
?>