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
 * Klasse Zeitaufzeichnung
 * @create 06-11-2007
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class zeitaufzeichnung extends basis_db
{
	public $new;		// boolean
	public $result = array();	// zeitaufzeichnung Objekt
	public $done=false;		// boolean

	//Tabellenspalten
	public $zeitaufzeichnung_id;	// serial
	public $uid;					// varchar(16)
	public $aktivitaet_kurzbz;		// varchar(16)
	public $start;					// timestamp
	public $ende;					// timestamp
	public $beschreibung;			// varchar(256)
	public $oe_kurzbz_1;			// varchar(32) ehemals studiengangs_kz
	public $oe_kurzbz_2;			// varchar(32) ehemals fachbereich_kurzbz
	public $insertamum;				// timestamp
	public $insertvon;				// varchar(16)
	public $updateamum;				// timestamp
	public $updatevon;				// varchar(16)
	public $projekt_kurzbz;			// varchar(16)
	public $ext_id;					// bigint
	public $service_id;				// integer
	public $kunde_uid;				// varchar(32)

	/**
	 * Konstruktor
	 * @param $zeitaufzeichnung_id ID der Zeitaufzeichnung die geladen werden soll (Default=null)
	 */
	public function __construct($zeitaufzeichnung_id=null)
	{
		parent::__construct();

		if($zeitaufzeichnung_id != null)
			$this->load($zeitaufzeichnung_id);
	}

	/**
	 * Laedt die Zeitaufzeichnung mit der ID $zeitaufzeichnung_id
	 * @param  $adress_id ID der zu ladenden Adresse
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($zeitaufzeichnung_id)
	{
		//Pruefen ob zeitaufzeichnung_id eine gueltige Zahl ist
		if(!is_numeric($zeitaufzeichnung_id) || $zeitaufzeichnung_id == '')
		{
			$this->errormsg = 'Zeitaufzeichnung_id muss eine Zahl sein';
			return false;
		}

		//Daten aus der Datenbank lesen
		$qry = "SELECT * FROM campus.tbl_zeitaufzeichnung WHERE zeitaufzeichnung_id=".$this->db_add_param($zeitaufzeichnung_id, FHC_INTEGER);

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->zeitaufzeichnung_id = $row->zeitaufzeichnung_id;
			$this->uid = $row->uid;
			$this->aktivitaet_kurzbz = $row->aktivitaet_kurzbz;
			$this->start = $row->start;
			$this->ende = $row->ende;
			$this->beschreibung = $row->beschreibung;
			$this->oe_kurzbz_1 = $row->oe_kurzbz_1;
			$this->oe_kurzbz_2 = $row->oe_kurzbz_2;
			$this->insertamum = $row->insertamum;
			$this->insertvon = $row->insertvon;
			$this->updateamum = $row->updateamum;
			$this->updatevon = $row->updatevon;
			$this->projekt_kurzbz = $row->projekt_kurzbz;
			$this->ext_id = $row->ext_id;
			$this->service_id = $row->service_id;
			$this->kunde_uid = $row->kunde_uid;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}

		return true;
	}

	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		$this->errormsg = '';
		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $adresse_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save()
	{
		//Variablen pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			//Neuen Datensatz einfuegen
			$qry='BEGIN;INSERT INTO campus.tbl_zeitaufzeichnung (uid, aktivitaet_kurzbz, start, ende, beschreibung,
			      oe_kurzbz_1, oe_kurzbz_2, insertamum, insertvon, updateamum, updatevon, projekt_kurzbz, service_id, kunde_uid) VALUES('.
			      $this->db_add_param($this->uid).', '.
			      $this->db_add_param($this->aktivitaet_kurzbz).', '.
			      $this->db_add_param($this->start).', '.
			      $this->db_add_param($this->ende).', '.
			      $this->db_add_param($this->beschreibung).', '.
			      $this->db_add_param($this->oe_kurzbz_1).', '.
			      $this->db_add_param($this->oe_kurzbz_2).','.
			      $this->db_add_param($this->insertamum).', '.
			      $this->db_add_param($this->insertvon).', '.
			      $this->db_add_param($this->updateamum).', '.
			      $this->db_add_param($this->updatevon).', '.
			      $this->db_add_param($this->projekt_kurzbz).', '.
			      $this->db_add_param($this->service_id).', '.
			      $this->db_add_param($this->kunde_uid).');';
		}
		else
		{
			//Updaten des bestehenden Datensatzes

			//Pruefen ob zeitaufzeichnung_id eine gueltige Zahl ist
			if(!is_numeric($this->zeitaufzeichnung_id))
			{
				$this->errormsg = 'zeitaufzeichnung_id muss eine gueltige Zahl sein';
				return false;
			}

			$qry='UPDATE campus.tbl_zeitaufzeichnung SET'.
				' uid='.$this->db_add_param($this->uid).', '.
				' aktivitaet_kurzbz='.$this->db_add_param($this->aktivitaet_kurzbz).', '.
				' start='.$this->db_add_param($this->start).', '.
				' ende='.$this->db_add_param($this->ende).', '.
		      	' beschreibung='.$this->db_add_param($this->beschreibung).', '.
		      	' oe_kurzbz_1='.$this->db_add_param($this->oe_kurzbz_1).', '.
		      	' oe_kurzbz_2='.$this->db_add_param($this->oe_kurzbz_2).', '.
		      	' updateamum='.$this->db_add_param($this->updateamum).', '.
		      	' updatevon='.$this->db_add_param($this->updatevon).', '.
		      	' projekt_kurzbz='.$this->db_add_param($this->projekt_kurzbz).', '.
				' service_id='.$this->db_add_param($this->service_id).', '.
				' kunde_uid='.$this->db_add_param($this->kunde_uid).' '.
		      	'WHERE zeitaufzeichnung_id='.$this->db_add_param($this->zeitaufzeichnung_id, FHC_INTEGER, false);
		}

		if($this->db_query($qry))
		{
			if($this->new)
			{
				//naechste ID aus der Sequence holen
				$qry="SELECT currval('campus.tbl_zeitaufzeichnung_zeitaufzeichnung_id_seq') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->zeitaufzeichnung_id = $row->id;
						$this->db_query('COMMIT');
						return true;
					}
					else
					{
						$this->db_query('ROLLBACK');
						$this->errormsg = "Fehler beim Auslesen der Sequence";
						return false;
					}
				}
				else
				{
					$this->db_query('ROLLBACK');
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					return false;
				}
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern';
			return false;
		}
		return true;
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $zeitaufzeichnnung_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($zeitaufzeichnung_id)
	{
		//Pruefen ob zeitaufzeichnung_id eine gueltige Zahl ist
		if(!is_numeric($zeitaufzeichnung_id) || $zeitaufzeichnung_id == '')
		{
			$this->errormsg = 'zeitaufzeichnung_id muss eine gueltige Zahl sein';
			return false;
		}

		//loeschen des Datensatzes
		$qry="DELETE FROM campus.tbl_zeitaufzeichnung WHERE zeitaufzeichnung_id=".$this->db_add_param($zeitaufzeichnung_id, FHC_INTEGER, false);

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
	 * Laedt die Datensaetze eines Projektes
	 * @param $projekt_kurzbz
	 */
	public function getListeProjekt($projekt_kurzbz)
	{
		$where = 'projekt_kurzbz='.$this->db_add_param($projekt_kurzbz);

	    $qry = "SELECT
	    			*, to_char ((ende-start),'HH24:MI') as diff,
	    			(SELECT (to_char(sum(ende-start),'DD')::integer)*24+to_char(sum(ende-start),'HH24')::integer || ':' || to_char(sum(ende-start),'MI')
	    			 FROM campus.tbl_zeitaufzeichnung
	    			 WHERE $where ) as summe
	    		FROM campus.tbl_zeitaufzeichnung WHERE $where
	    		ORDER BY start DESC";

	    if($result = $this->db_query($qry))
	    {
	    	while($row = $this->db_fetch_object($result))
	    	{
	    		$obj = new zeitaufzeichnung();

	    		$obj->zeitaufzeichnung_id = $row->zeitaufzeichnung_id;
				$obj->uid = $row->uid;
				$obj->aktivitaet_kurzbz = $row->aktivitaet_kurzbz;
				$obj->start = $row->start;
				$obj->ende = $row->ende;
				$obj->beschreibung = $row->beschreibung;
				$obj->oe_kurzbz_1 = $row->oe_kurzbz_1;
				$obj->oe_kurzbz_2 = $row->oe_kurzbz_2;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->projekt_kurzbz = $row->projekt_kurzbz;
				$obj->ext_id = $row->ext_id;
				$obj->service_id = $row->service_id;
				$obj->kunde_uid = $row->kunde_uid;
				$obj->summe = $row->summe;
				$obj->diff = $row->diff;

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
	 * Laedt die Zeitaufzeichnungen eines Users. Default: Die letzten 40 Tage
	 * @param string $user
	 * @param integer $days deafult: 40 Tage
	 */
	public function getListeUser($user, $days='40')
	{
		$where = "uid=".$this->db_add_param($user);
		if ($days!='')
			$where.= " AND ende>(now() - INTERVAL '".$days." days')";

	   $qry = "SELECT
	    			*, to_char ((ende-start),'HH24:MI') as diff,
	    			(SELECT (to_char(sum(ende-start),'DD')::integer)*24+to_char(sum(ende-start),'HH24')::integer || ':' || to_char(sum(ende-start),'MI')
	    			 FROM campus.tbl_zeitaufzeichnung
	    			 WHERE $where ) as summe
	    		FROM campus.tbl_zeitaufzeichnung WHERE $where
	    		ORDER BY start DESC";

	    if($result = $this->db_query($qry))
	    {
	    	while($row = $this->db_fetch_object($result))
	    	{
	    		$obj = new zeitaufzeichnung();

	    		$obj->zeitaufzeichnung_id = $row->zeitaufzeichnung_id;
				$obj->uid = $row->uid;
				$obj->aktivitaet_kurzbz = $row->aktivitaet_kurzbz;
				$obj->start = $row->start;
				$obj->ende = $row->ende;
				$obj->beschreibung = $row->beschreibung;
				$obj->oe_kurzbz_1 = $row->oe_kurzbz_1;
				$obj->oe_kurzbz_2 = $row->oe_kurzbz_2;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->projekt_kurzbz = $row->projekt_kurzbz;
				$obj->ext_id = $row->ext_id;
				$obj->service_id = $row->service_id;
				$obj->kunde_uid = $row->kunde_uid;
				$obj->summe = $row->summe;
				$obj->diff = $row->diff;
				$obj->datum = $row->start;

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
	 * Laedt die Zeitaufzeichnungen eines Users aufgefüllt mit lehren Tagen.
	 * Default: Die letzten 40 Tage
	 * @param string $user
	 * @param integer $days deafult: 40 Tage
	 */
	public function getListeUserFull($user, $days='40')
	{
		$where = "uid=".$this->db_add_param($user);
		if ($days!='')
			$where.= " AND ende>(now() - INTERVAL '".$days." days')";
		$where_join = "and z.uid=".$this->db_add_param($user);
		if ($days!='')
			$where_join.= " AND z.ende>(now() - INTERVAL '".$days." days')";
		if ($days=='')
			$max_anz = 180;
		else
			$max_anz = $days;
	   $qry = "SELECT
	    			d.dates, z.*, to_char ((z.ende-z.start),'HH24:MI') as diff,
	    			(SELECT (to_char(sum(ende-start),'DD')::integer)*24+to_char(sum(ende-start),'HH24')::integer || ':' || to_char(sum(ende-start),'MI')
	    			 FROM campus.tbl_zeitaufzeichnung
	    			 WHERE $where) as summe
	    		FROM campus.tbl_zeitaufzeichnung z
	    		right join (select current_date - s.a as dates from generate_series(0,$max_anz,1) as s(a)) d on date(z.ende) = d.dates $where_join order by d.dates desc, z.start desc
	    		";

	    if($result = $this->db_query($qry))
	    {
	    	while($row = $this->db_fetch_object($result))
	    	{
	    		$obj = new zeitaufzeichnung();

	    		$obj->zeitaufzeichnung_id = $row->zeitaufzeichnung_id;
				$obj->uid = $row->uid;
				$obj->aktivitaet_kurzbz = $row->aktivitaet_kurzbz;
				$obj->start = $row->start;
				$obj->ende = $row->ende;
				$obj->beschreibung = $row->beschreibung;
				$obj->oe_kurzbz_1 = $row->oe_kurzbz_1;
				$obj->oe_kurzbz_2 = $row->oe_kurzbz_2;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->projekt_kurzbz = $row->projekt_kurzbz;
				$obj->ext_id = $row->ext_id;
				$obj->service_id = $row->service_id;
				$obj->kunde_uid = $row->kunde_uid;
				$obj->summe = $row->summe;
				$obj->diff = $row->diff;
				$obj->datum = $row->dates;

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
	 * Löscht sämtliche Einträge eines Users für einen Tag 
	 * @param string $user
	 * @param string $tag Y-m-d
	 */
	public function deleteEntriesForUser($user, $tag)
	{		 
		$where = "uid=".$this->db_add_param($user);	
		
		$qry = "delete from campus.tbl_zeitaufzeichnung where $where and date_trunc('day', start) = '$tag'";
	    if($result = $this->db_query($qry))
	    {
	    	return true;
	    }
	    else
	    {
	    	$this->errormsg = 'Fehler beim Laden der Daten';
	    	return false;
	    }
	}

	/**
	 * Löscht Pauseneinträge eines Users für einen Tag, die außerhalb der Arbeitszeit liegen 
	 * Löscht Pauseneinträge an Tagen ohne Arbeitszeit
	 * @param string $user
	 * @param string $tag Y-m-d
	 */
	public function cleanPausenForUser($user, $tag)
	{		 
		$where = "uid=".$this->db_add_param($user);	
		
		$qry = "
		delete from campus.tbl_zeitaufzeichnung where aktivitaet_kurzbz = 'Pause' and start::date = '$tag' and $where and
(
start::time >= 
(SELECT max(ende::time) as endzeit from campus.tbl_zeitaufzeichnung where $where and start::date = '$tag' AND (aktivitaet_kurzbz != 'LehreExtern' or aktivitaet_kurzbz is null ) and aktivitaet_kurzbz != 'Pause')
or
ende::time<=
(SELECT min(start::time) as startzeit from campus.tbl_zeitaufzeichnung where $where and start::date = '$tag' AND (aktivitaet_kurzbz != 'LehreExtern' or aktivitaet_kurzbz is null ) and aktivitaet_kurzbz != 'Pause')
or not exists
(select 1 from campus.tbl_zeitaufzeichnung where aktivitaet_kurzbz != 'LehreExtern' and aktivitaet_kurzbz != 'Pause' and start::date = '$tag' and $where ) 
)
		";
		
	    if($result = $this->db_query($qry))
	    {
	    	return true;
	    }
	    else
	    {
	    	$this->errormsg = 'Fehler beim Laden der Daten';
	    	return false;
	    }
	}

	/**
	 * Holt alle ZA-Einträge Typ LehreIntern und LehreExtern  eines Users 
	 * für das laufende Studienjahr und gibt die Summen in einem Array zurück
	 * @param string $user
	 * @return Array mit Keay: LehreIntern, LehreExtern, LehreAuftraege, LehreInkludiert
	 */	
	public function getLehreForUser($user,$sem)
	{
		$where = "uid=".$this->db_add_param($user);	
		$where_sem = "studiensemester_kurzbz=".$this->db_add_param($sem);
		$lehre_arr = array("LehreIntern"=>0, "LehreExtern"=>0, "LehreAuftraege"=>0);
		
		$qry = "
		select sum(extract(epoch from ende-start))/3600 as lehre, aktivitaet_kurzbz from campus.tbl_zeitaufzeichnung where $where and aktivitaet_kurzbz in ('LehreIntern', 'LehreExtern') and start > (select start from public.tbl_studiensemester where $where_sem) group by aktivitaet_kurzbz
		";
		
	    if($result = $this->db_query($qry))
	    {
				    	
	    	while($row = $this->db_fetch_object($result))
	    	{
					$lehre_arr[$row->aktivitaet_kurzbz] = round($row->lehre,2);	
	    	}
	    }
	    else
	    {
	    	return false;
	    }	
	    $where = "mitarbeiter_uid=".$this->db_add_param($user);
	    $where_sem = "l.studiensemester_kurzbz=".$this->db_add_param($sem);
	    $qry = "
		select sum(m.semesterstunden) from lehre.tbl_lehreinheitmitarbeiter m, lehre.tbl_lehreinheit l where $where and $where_sem and l.lehreinheit_id = m.lehreinheit_id and m.stundensatz*m.semesterstunden > 0
		";
		
	    if($result = $this->db_query($qry))
	    {
				    	
	    	while($row = $this->db_fetch_object($result))
	    	{
					$lehre_arr["LehreAuftraege"] = round($row->sum);	
	    	}
	    }
	    else
	    {
	    	return false;
	    }	
	    
	    return $lehre_arr;
	}
}
?>
