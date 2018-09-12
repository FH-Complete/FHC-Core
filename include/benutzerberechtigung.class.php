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
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/organisationseinheit.class.php');
require_once(dirname(__FILE__).'/studiengang.class.php');
require_once(dirname(__FILE__).'/fachbereich.class.php');
require_once(dirname(__FILE__).'/functions.inc.php');
require_once(dirname(__FILE__).'/wawi_kostenstelle.class.php');

class benutzerberechtigung extends basis_db
{
	public $new;      // boolean
	public $berechtigungen = array(); // benutzerberechtigung Objekt

	//Tabellenspalten
	public $benutzerberechtigung_id;	// serial
	public $uid;						// varchar(32)
	public $funktion_kurzbz;			// varchar(16)
	public $rolle_kurzbz;				// varchar(32)
	public $berechtigung_kurzbz;		// varchar(16)
	public $art;						// varchar(5)
	public $oe_kurzbz;					// varchar(32)
	public $studiensemester_kurzbz;		// varchar(16)
	public $start;						// date
	public $ende;						// date
	public $negativ;					// boolean
	public $updateamum;
	public $updatevon;
	public $insertamum;
	public $insertvon;
	public $kostenstelle_id;
	public $anmerkung;					// varchar(256)

	public $starttimestamp;
	public $endetimestamp;

	//Attribute des Mitarbeiters
	public $fix;
	public $lektor;

	/**
	 * Konstruktor - Laedt optional eine Berechtigung
	 * @param $benutzerberechtigung_id
	 */
	public function __construct($benutzerberechtigung_id=null)
	{
		parent::__construct();

		if($benutzerberechtigung_id!=null)
			$this->load($benutzerberechtigung_id);
	}

	/**
	 * Laedt eine Benutzerberechtigung
	 * @param benutzerberechtigung_id
	 */
	public function load($benutzerberechtigung_id)
	{
		if(!is_numeric($benutzerberechtigung_id) || $benutzerberechtigung_id=='')
		{
			$this->errormsg = 'benutzerberechtigung_id ist ungueltig';
			return false;
		}

		$qry = "SELECT * FROM system.tbl_benutzerrolle WHERE benutzerberechtigung_id=".$this->db_add_param($benutzerberechtigung_id, FHC_INTEGER);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->benutzerberechtigung_id = $benutzerberechtigung_id;
				$this->rolle_kurzbz = $row->rolle_kurzbz;
				$this->berechtigung_kurzbz = $row->berechtigung_kurzbz;
				$this->uid = $row->uid;
				$this->funktion_kurzbz = $row->funktion_kurzbz;
				$this->oe_kurzbz = $row->oe_kurzbz;
				$this->art = $row->art;
				$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$this->start = $row->start;
				$this->ende = $row->ende;
				$this->negativ = $this->db_parse_bool($row->negativ);
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->kostenstelle_id = $row->kostenstelle_id;
				$this->anmerkung = $row->anmerkung;

				return true;
			}
			else
			{
				$this->errormsg = 'Es wurde kein Eintrag mit dieser ID gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Berechtigung';
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
		if(mb_strlen($this->art)>16)
		{
			$this->errormsg = 'Art darf nicht laenger als 16 Zeichen sein';
			return false;
		}

		if(mb_strlen($this->berechtigung_kurzbz)>32)
		{
			$this->errormsg = 'Berechtigung_kurzbz darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->uid)>32)
		{
			$this->errormsg = 'UID darf nicht laenger als 32 Zeichen sein';
			return false;
		}

		if($this->rolle_kurzbz=='' && $this->berechtigung_kurzbz=='')
		{
			$this->errormsg = 'Es muss entweder eine Rolle oder eine Berechtigung angegeben werden';
			return false;
		}

		if($this->rolle_kurzbz!='' && $this->berechtigung_kurzbz!='')
		{
			$this->errormsg = 'Rolle und Berechtigung kann nicht gleichzeitig angegeben werden';
			return false;
		}

		if($this->uid=='' && $this->funktion_kurzbz=='')
		{
			$this->errormsg = 'Ess muss entweder eine UID oder eine Funktion_kurzbz angegeben werden';
			return false;
		}

		if($this->uid!='' && $this->funktion_kurzbz!='')
		{
			$this->errormsg = 'UID und Funktion_kurzbz kann nicht gleichzeitig angegeben werden';
			return false;
		}

		if($this->funktion_kurzbz!='' && $this->oe_kurzbz!='')
		{
			$this->errormsg = 'Wenn eine Funktion_kurzbz angegeben wird, darf keine Organisationseinheit eingetragen sein';
			return false;
		}

		if($this->art=='')
		{
			$this->errormsg = 'Art darf nicht leer sein';
			return false;
		}

		if($this->kostenstelle_id!='' && !is_numeric($this->kostenstelle_id))
		{
			$this->errormsg = 'Kostenstelle_id muss eine gueltige Zahl sein';
			return false;
		}

		if($this->kostenstelle_id!='' && $this->oe_kurzbz!='')
		{
			$this->errormsg = 'Wenn eine Kostenstelle angegeben wird, darf keine Organisationseinheit eingetragen sein';
			return false;
		}

		if(mb_strlen($this->anmerkung)>256)
		{
			$this->errormsg = 'Anmerkung darf nicht laenger als 256 Zeichen sein';
			return false;
		}

		return true;
	}

	/**
	 * Speichert Benutzerberechtigung in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save()
	{
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			$qry = 'INSERT INTO system.tbl_benutzerrolle (rolle_kurzbz, berechtigung_kurzbz, uid, funktion_kurzbz,
						oe_kurzbz, art, studiensemester_kurzbz, start, ende, negativ, updateamum, updatevon,
						insertamum, insertvon, kostenstelle_id, anmerkung)
			        VALUES('.$this->db_add_param($this->rolle_kurzbz).','.
					$this->db_add_param($this->berechtigung_kurzbz).','.
					$this->db_add_param($this->uid).','.
					$this->db_add_param($this->funktion_kurzbz).','.
					$this->db_add_param($this->oe_kurzbz).','.
					$this->db_add_param($this->art).','.
					$this->db_add_param($this->studiensemester_kurzbz).','.
					$this->db_add_param($this->start).','.
					$this->db_add_param($this->ende).','.
					$this->db_add_param($this->negativ, FHC_BOOLEAN).','.
					$this->db_add_param($this->updateamum).','.
					$this->db_add_param($this->updatevon).','.
					$this->db_add_param($this->insertamum).','.
					$this->db_add_param($this->insertvon).','.
					$this->db_add_param($this->kostenstelle_id, FHC_INTEGER).','.
					$this->db_add_param($this->anmerkung).');';
		}
		else
		{
			$qry = 'UPDATE system.tbl_benutzerrolle SET'.
				   ' rolle_kurzbz='.$this->db_add_param($this->rolle_kurzbz).','.
				   ' berechtigung_kurzbz='.$this->db_add_param($this->berechtigung_kurzbz).','.
				   ' uid='.$this->db_add_param($this->uid).','.
				   ' funktion_kurzbz='.$this->db_add_param($this->funktion_kurzbz).','.
				   ' oe_kurzbz='.$this->db_add_param($this->oe_kurzbz).','.
			       ' art='.$this->db_add_param($this->art).','.
			       ' studiensemester_kurzbz='.$this->db_add_param($this->studiensemester_kurzbz).','.
			       ' start='.$this->db_add_param($this->start).','.
			       ' ende='.$this->db_add_param($this->ende).','.
			       ' negativ='.$this->db_add_param($this->negativ, FHC_BOOLEAN).','.
				   ' kostenstelle_id='.$this->db_add_param($this->kostenstelle_id).','.
			       ' anmerkung='.$this->db_add_param($this->anmerkung).','.
			       ' updateamum='.$this->db_add_param($this->updateamum).','.
			       ' updatevon='.$this->db_add_param($this->updatevon).
			       " WHERE benutzerberechtigung_id=".$this->db_add_param($this->benutzerberechtigung_id, FHC_INTEGER, false);
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Benutzerberechtigung';
			return false;
		}
	}

	/**
	 * Loescht einen Eintrag aus der Tabelle benutzerrolle
	 * @param benutzerberechtigung_id
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function delete($benutzerberechtigung_id)
	{
		if(!is_numeric($benutzerberechtigung_id) || $benutzerberechtigung_id=='')
		{
			$this->errormsg = 'benutzerberechtigung_id ist ungültig';
			return false;
		}

		// Berechtigungen loeschen
		$sql_query="DELETE FROM system.tbl_benutzerrolle where benutzerberechtigung_id=".$this->db_add_param($benutzerberechtigung_id, FHC_INTEGER);

		if(!$this->db_query($sql_query))
		{
			$this->errormsg='Fehler beim Löschen';
			return false;
		}
		return true;
	}

	/**
	 * Laedt die Benutzerrollen zu einer UID
	 *
	 * @param unknown_type $uid
	 */
	public function loadBenutzerRollen($uid=null, $funktion_kurzbz=null)
	{
		$qry = 'SELECT * FROM system.tbl_benutzerrolle WHERE ';

		if(!is_null($uid))
			$qry.= " uid=".$this->db_add_param($uid);
		elseif(!is_null($funktion_kurzbz))
			$qry.= " funktion_kurzbz=".$this->db_add_param($funktion_kurzbz);
		else
		{
			$this->errormsg = 'Entweder UID oder funktion_kurzbz muss uebergeben werden';
			return false;
		}
		$qry.= " ORDER BY rolle_kurzbz,berechtigung_kurzbz,oe_kurzbz,kostenstelle_id";
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new benutzerberechtigung();

				$obj->benutzerberechtigung_id = $row->benutzerberechtigung_id;
				$obj->rolle_kurzbz = $row->rolle_kurzbz;
				$obj->berechtigung_kurzbz = $row->berechtigung_kurzbz;
				$obj->uid = $row->uid;
				$obj->funktion_kurzbz = $row->funktion_kurzbz;
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->art = $row->art;
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->start = $row->start;
				$obj->ende = $row->ende;
				$obj->negativ = $this->db_parse_bool($row->negativ);
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->kostenstelle_id = $row->kostenstelle_id;
				$obj->anmerkung = $row->anmerkung;

				$this->berechtigungen[] = $obj;
			}
		}
		return true;
	}

	/**
	 * Laedt die Berechtigungen eines Users
	 * @param $uid
	 * @param $all wenn $all auf true gesetzt wird, werden auch bereits abgelaufene
	 *              berechtigungen geladen.
	 */
	public function getBerechtigungen($uid,$all=false)
	{
		// Pruefen ob die Person aktiv ist
		$qry = "SELECT aktiv FROM public.tbl_benutzer WHERE uid=".$this->db_add_param($uid);
		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				// Wenn die Person nicht aktiv ist dann hat diese auch keine Rechte
				if($this->db_parse_bool($row->aktiv) == false)
					return false;
			}
			else
			{
				// Wenn die Person nicht gefunden wurde dann hat diese auch keine Rechte
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
		// Berechtigungen holen
		/*
		Direkte Berechtigungszuordnung
		UNION
		Berechtigung ueber Rolle
		UNION
		Berechtigung ueber Funktion
		UNION
		Berechtigung ueber Funktion Mitarbeiter
		UNION
		Berechtigung ueber Funktion Student
		*/
		$qry = "SELECT
					benutzerberechtigung_id, tbl_benutzerrolle.uid, tbl_benutzerrolle.funktion_kurzbz,
					tbl_benutzerrolle.rolle_kurzbz, tbl_benutzerrolle.berechtigung_kurzbz, tbl_benutzerrolle.art, tbl_benutzerrolle.art art1,
					tbl_benutzerrolle.oe_kurzbz, tbl_benutzerrolle.studiensemester_kurzbz, tbl_benutzerrolle.start,
					tbl_benutzerrolle.ende, tbl_benutzerrolle.negativ, tbl_benutzerrolle.updateamum, tbl_benutzerrolle.updatevon,
					tbl_benutzerrolle.insertamum, tbl_benutzerrolle.insertvon,tbl_benutzerrolle.kostenstelle_id,tbl_benutzerrolle.anmerkung
				FROM
					system.tbl_benutzerrolle JOIN system.tbl_berechtigung USING(berechtigung_kurzbz)
				WHERE uid=".$this->db_add_param($uid)."

				UNION

				SELECT
					benutzerberechtigung_id, tbl_benutzerrolle.uid, tbl_benutzerrolle.funktion_kurzbz,
					tbl_benutzerrolle.rolle_kurzbz, tbl_berechtigung.berechtigung_kurzbz, tbl_benutzerrolle.art, tbl_rolleberechtigung.art art1,
					tbl_benutzerrolle.oe_kurzbz, tbl_benutzerrolle.studiensemester_kurzbz, tbl_benutzerrolle.start,
					tbl_benutzerrolle.ende, tbl_benutzerrolle.negativ, tbl_benutzerrolle.updateamum, tbl_benutzerrolle.updatevon,
					tbl_benutzerrolle.insertamum, tbl_benutzerrolle.insertvon,tbl_benutzerrolle.kostenstelle_id,tbl_benutzerrolle.anmerkung
				FROM
					system.tbl_benutzerrolle JOIN system.tbl_rolle USING(rolle_kurzbz)
					JOIN system.tbl_rolleberechtigung USING(rolle_kurzbz)
					JOIN system.tbl_berechtigung ON(tbl_rolleberechtigung.berechtigung_kurzbz=tbl_berechtigung.berechtigung_kurzbz)
				WHERE uid=".$this->db_add_param($uid)."

				UNION

				SELECT
					benutzerberechtigung_id, tbl_benutzerfunktion.uid, tbl_benutzerrolle.funktion_kurzbz,
					tbl_benutzerrolle.rolle_kurzbz, tbl_benutzerrolle.berechtigung_kurzbz, tbl_benutzerrolle.art, tbl_benutzerrolle.art art1,
					tbl_benutzerfunktion.oe_kurzbz, tbl_benutzerrolle.studiensemester_kurzbz, tbl_benutzerrolle.start,
					tbl_benutzerrolle.ende, tbl_benutzerrolle.negativ, tbl_benutzerrolle.updateamum, tbl_benutzerrolle.updatevon,
					tbl_benutzerrolle.insertamum, tbl_benutzerrolle.insertvon,tbl_benutzerrolle.kostenstelle_id,tbl_benutzerrolle.anmerkung
				FROM
					system.tbl_benutzerrolle JOIN public.tbl_benutzerfunktion USING(funktion_kurzbz)
				WHERE tbl_benutzerfunktion.uid=".$this->db_add_param($uid)."
					AND (tbl_benutzerfunktion.datum_von IS NULL OR tbl_benutzerfunktion.datum_von<=now())
					AND (tbl_benutzerfunktion.datum_bis IS NULL OR tbl_benutzerfunktion.datum_bis>=now())

				UNION

				SELECT
					benutzerberechtigung_id, tbl_benutzerfunktion.uid, tbl_benutzerrolle.funktion_kurzbz,
					tbl_benutzerrolle.rolle_kurzbz, tbl_rolleberechtigung.berechtigung_kurzbz, tbl_benutzerrolle.art, tbl_rolleberechtigung.art art1,
					tbl_benutzerfunktion.oe_kurzbz, tbl_benutzerrolle.studiensemester_kurzbz, tbl_benutzerrolle.start,
					tbl_benutzerrolle.ende, tbl_benutzerrolle.negativ, tbl_benutzerrolle.updateamum, tbl_benutzerrolle.updatevon,
					tbl_benutzerrolle.insertamum, tbl_benutzerrolle.insertvon,tbl_benutzerrolle.kostenstelle_id,tbl_benutzerrolle.anmerkung
				FROM
					system.tbl_benutzerrolle
					JOIN public.tbl_benutzerfunktion USING(funktion_kurzbz)
					JOIN system.tbl_rolleberechtigung ON(tbl_benutzerrolle.rolle_kurzbz=tbl_rolleberechtigung.rolle_kurzbz)
				WHERE tbl_benutzerfunktion.uid=".$this->db_add_param($uid)."
					AND (tbl_benutzerfunktion.datum_von IS NULL OR tbl_benutzerfunktion.datum_von<=now())
					AND (tbl_benutzerfunktion.datum_bis IS NULL OR tbl_benutzerfunktion.datum_bis>=now())

				UNION

				SELECT
					benutzerberechtigung_id, '', tbl_benutzerrolle.funktion_kurzbz,
					tbl_benutzerrolle.rolle_kurzbz, tbl_benutzerrolle.berechtigung_kurzbz, tbl_benutzerrolle.art, tbl_benutzerrolle.art art1,
					tbl_benutzerrolle.oe_kurzbz, tbl_benutzerrolle.studiensemester_kurzbz, tbl_benutzerrolle.start,
					tbl_benutzerrolle.ende, tbl_benutzerrolle.negativ, tbl_benutzerrolle.updateamum, tbl_benutzerrolle.updatevon,
					tbl_benutzerrolle.insertamum, tbl_benutzerrolle.insertvon,tbl_benutzerrolle.kostenstelle_id,tbl_benutzerrolle.anmerkung
				FROM
					system.tbl_benutzerrolle
				WHERE
					tbl_benutzerrolle.funktion_kurzbz='Mitarbeiter' AND
					EXISTS (SELECT mitarbeiter_uid FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid=".$this->db_add_param($uid).")

				UNION

				SELECT
					benutzerberechtigung_id, '', tbl_benutzerrolle.funktion_kurzbz,
					tbl_benutzerrolle.rolle_kurzbz, tbl_benutzerrolle.berechtigung_kurzbz, tbl_benutzerrolle.art, tbl_benutzerrolle.art art1,
					tbl_benutzerrolle.oe_kurzbz, tbl_benutzerrolle.studiensemester_kurzbz, tbl_benutzerrolle.start,
					tbl_benutzerrolle.ende, tbl_benutzerrolle.negativ, tbl_benutzerrolle.updateamum, tbl_benutzerrolle.updatevon,
					tbl_benutzerrolle.insertamum, tbl_benutzerrolle.insertvon,tbl_benutzerrolle.kostenstelle_id,tbl_benutzerrolle.anmerkung
				FROM
					system.tbl_benutzerrolle
				WHERE
					tbl_benutzerrolle.funktion_kurzbz='Student' AND
					EXISTS (SELECT student_uid FROM public.tbl_student WHERE student_uid=".$this->db_add_param($uid).")

				ORDER BY negativ DESC";

		if(!$result = $this->db_query($qry))
		{
			$this->errormsg='Fehler beim Laden der Berechtigungen';
			return false;
		}

		while($row=$this->db_fetch_object($result))
		{
   			$b=new benutzerberechtigung();

   			$b->benutzerberechtigung_id = $row->benutzerberechtigung_id;
   			$b->uid=$row->uid;
   			$b->funktion_kurzbz=$row->funktion_kurzbz;
   			$b->rolle_kurzbz = $row->rolle_kurzbz;
   			$b->berechtigung_kurzbz = $row->berechtigung_kurzbz;
			$b->art=intersect($row->art, $row->art1);
			$b->oe_kurzbz = $row->oe_kurzbz;
			$b->studiensemester_kurzbz=$row->studiensemester_kurzbz;
			$b->start=$row->start;
			if ($row->start!=null)
				$b->starttimestamp=mktime(0,0,0,mb_substr($row->start,5,2),mb_substr($row->start,8),mb_substr($row->start,0,4));
			else
				$b->starttimestamp=null;
			$b->ende=$row->ende;
			if ($row->ende!=null)
				$b->endetimestamp=mktime(23,59,59,mb_substr($row->ende,5,2),mb_substr($row->ende,8),mb_substr($row->ende,0,4));
			$b->negativ = ($row->negativ=='t'?true:false);
			$b->updateamum = $row->updateamum;
			$b->updatevon = $row->updatevon;
			$b->insertamum = $row->insertamum;
			$b->insertvon = $row->insertvon;
			$b->kostenstelle_id = $row->kostenstelle_id;
			$b->anmerkung = $row->anmerkung;

			$this->berechtigungen[]=$b;
		}

		unset($result);
		// Attribute des Mitarbeiters holen
		$sql_query="SELECT fixangestellt, lektor FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid=".$this->db_add_param($uid);
		if(!$this->db_query($sql_query))
		{
			$this->errormsg='Fehler beim Laden der Berechtigungen';
			return false;
		}
		while($row=$this->db_fetch_object())
		{
   			if ($row->fixangestellt=='t')
   				$this->fix=true;
   			else
   				$this->fix=false;

			if ($row->lektor=='t')
   				$this->lektor=true;
   			else
   				$this->lektor=false;
		}
		return true;
	}

	/**
	 * Prueft ob die Berechtigung vorhanden ist. Vor der Verwendung muss die
	 * Funktion getBerechtigungen aufgerufen werden.
	 *
	 * @param $berechtigung
	 * @param $oe_kurzbz
	 * 		derzeit kann hier noch die Studiengangskennzahl uebergeben werden,
	 * 		dies wird in Zukunft aber nicht mehr moeglich sein
	 * @param $art			suid (select|update|insert|delete)
	 * @param $kostenstelle_id	ID der Kostenstelle
	 * @return true wenn eine Berechtigung entspricht.
	 */
	public function isBerechtigt($berechtigung_kurzbz, $oe_kurzbz=null, $art=null, $kostenstelle_id=null)
	{
		$timestamp=time();

		//Studiengang
		if(is_numeric($oe_kurzbz))
		{
			//Studiengang
			$stg = new studiengang($oe_kurzbz);
			$oe_kurzbz = $stg->oe_kurzbz;
		}

		if($kostenstelle_id!='' && !is_numeric($kostenstelle_id))
		{
			$this->errormsg = 'Kostenstelle_id "'.$kostenstelle_id.'" is invalid';
			return false;
		}

		$oe = new organisationseinheit();

		foreach ($this->berechtigungen as $b)
		{
			//Pruefen ob eine negativ-Berechtigung vorhanden ist
			if($b->berechtigung_kurzbz==$berechtigung_kurzbz
				&& $b->negativ
				&& (is_null($oe_kurzbz) || ($b->kostenstelle_id=='' && ($b->oe_kurzbz=='' || $oe_kurzbz==$b->oe_kurzbz || $oe->isChild($b->oe_kurzbz, $oe_kurzbz))))
				&& (is_null($kostenstelle_id) || $kostenstelle_id==$b->kostenstelle_id))
			{
				if (($timestamp>$b->starttimestamp || $b->starttimestamp==null)
				 && ($timestamp<$b->endetimestamp || $b->endetimestamp==null))
				{
					$this->errormsg='Access denied! You need permission '.strtoupper($berechtigung_kurzbz).' '.($oe_kurzbz!=null?'in '.strtoupper($oe_kurzbz):'').' '.($art!=null?'with '.strtoupper($art):'');
					return false;
				}
			}

			if($b->berechtigung_kurzbz==$berechtigung_kurzbz
			   && (is_null($art) || mb_strstr($b->art, $art))
			   && (is_null($oe_kurzbz) || ($b->kostenstelle_id=='' && ($b->oe_kurzbz=='' || $oe_kurzbz==$b->oe_kurzbz || $oe->isChild($b->oe_kurzbz, $oe_kurzbz))))
			   && (is_null($kostenstelle_id) || $kostenstelle_id==$b->kostenstelle_id))
			{
				if (($timestamp>$b->starttimestamp || $b->starttimestamp==null)
				 && ($timestamp<$b->endetimestamp || $b->endetimestamp==null))
				{
						return true;
				}
			}
		}

		//Kostenstellenrecht ueber Organisationseinheit
		if($kostenstelle_id!='')
		{
			//Kostenstelle laden und schauen, ob auf die Organisationseinheit der Kostenstelle
			//die Berechtigung vorhanden ist
			$kostenstelle = new wawi_kostenstelle();
			if($kostenstelle->load($kostenstelle_id))
			{
				return $this->isBerechtigt($berechtigung_kurzbz, $kostenstelle->oe_kurzbz, $art);
			}
			else
			{
				$this->errormsg='Cost center (ID '.$kostenstelle_id.') does not exist';
				return false;
			}
		}

		//wenn ein Doppelpunkt vorkommt, pruefen ob das Uebergeordnete vorhanden ist
		if($pos=mb_strpos($berechtigung_kurzbz,':')===false)
		{
			$this->errormsg='Access denied! You need permission '.strtoupper($berechtigung_kurzbz).' '.($oe_kurzbz!=null?'in '.strtoupper($oe_kurzbz):'').' '.($art!=null?'with '.strtoupper($art):'');
			return false;
		}
		else
		{
			return $this->isBerechtigt(substr($berechtigung_kurzbz,0,$pos-1), $oe_kurzbz, $art, $kostenstelle_id);
		}
	}

	/**
	 * Prueft ob die Berechtigung zumindest fuer eine der angegebenen OE vorhanden ist.
	 * @param $berechtigung_kurzbz
	 * @param $oe_kurzbz
	 * @param $art
	 * @param $kostenstelle_id
	 * @return boolean
	 */
	public function isBerechtigtMultipleOe($berechtigung_kurzbz, $oe_kurzbz, $art=null, $kostenstelle_id=null)
	{
		$results = array();

		foreach($oe_kurzbz as $value)
		{
			$results[] = $this->isBerechtigt($berechtigung_kurzbz, $value, $art, $kostenstelle_id);
		}

		if(!in_array(true, $results))
			return false;
		else
			return true;
	}

	/**
	 * Prueft ob die Person Fixangestellt ist
	 * @return true wenn ja, false wenn nein
	 */
	public function isFix()
	{
		if ($this->fix)
			return true;
		else
			return false;
	}

	/**
	 * Gibt Array mit den Studiengangskennzahlen zurueck fuer welche die
	 * Person eine Berechtigung besitzt.
	 * Optional wird auf Berechtigung eingeschraenkt.
	 */
	public function getStgKz($berechtigung_kurzbz=null)
	{
		$studiengang_kz=array();
		$timestamp=time();
		$in='';
		$not='';
		$all=false;
		$oe = new organisationseinheit();

		foreach ($this->berechtigungen as $b)
		{
			if	(($berechtigung_kurzbz==$b->berechtigung_kurzbz || (mb_strpos($berechtigung_kurzbz,':')!==false && mb_substr($berechtigung_kurzbz,0,mb_strpos($berechtigung_kurzbz,':'))==$b->berechtigung_kurzbz))
				&& (($timestamp>$b->starttimestamp || $b->starttimestamp==null) && ($timestamp<$b->endetimestamp || $b->endetimestamp==null)))
			{
				if($b->negativ)
				{
					//Negativ-Recht
					if(!is_null($b->oe_kurzbz))
					{
						$childoes = $oe->getChilds($b->oe_kurzbz);
						foreach($childoes as $row)
							$not .="'".$this->db_escape($row)."',";
					}
					else
						return array();
				}
				else
				{
					if(!is_null($b->oe_kurzbz))
					{
						$childoes = $oe->getChilds($b->oe_kurzbz);
						foreach($childoes as $row)
						{
							$in .= "'".$this->db_escape($row)."',";
						}
					}
					else
					{
						//Wenn NULL dann berechtigung auf alles
						$all = true;
						break;
					}
				}
			}
		}

		if(!$all)
		{
			if($in=='')
				return array();
			else
				$in = ' AND oe_kurzbz IN('.mb_substr($in,0, mb_strlen($in)-1).')';
		}
		else
		{
			$in='';
			$not='';
		}

		if($not!='')
			$not = ' AND oe_kurzbz NOT IN('.mb_substr($not,0, mb_strlen($not)-1).')';

		$qry = "SELECT studiengang_kz FROM public.tbl_studiengang WHERE 1=1 $in $not";

		if($this->db_query($qry))
			while($row = $this->db_fetch_object())
				$studiengang_kz[]=$row->studiengang_kz;

		$studiengang_kz=array_unique($studiengang_kz);
		sort($studiengang_kz);

		return $studiengang_kz;
	}

	/**
	 * Gibt eine Array mit den Fachbereichen/Instituten zurueck
	 *
	 * @param $berechtigung
	 * @return array mit fachbereichen
	 */
	public function getFbKz($berechtigung_kurzbz=null)
	{
		$fachbereich_kurzbz=array();
		$timestamp=time();
		$in='';
		$not='';
		$all=false;
		$oe = new organisationseinheit();

		foreach ($this->berechtigungen as $b)
		{
			if	(($berechtigung_kurzbz==$b->berechtigung_kurzbz || (mb_strpos($berechtigung_kurzbz,':')!==false && mb_substr($berechtigung_kurzbz,0,mb_strpos($berechtigung_kurzbz,':'))==$b->berechtigung_kurzbz))
				&& (($timestamp>$b->starttimestamp || $b->starttimestamp==null) && ($timestamp<$b->endetimestamp || $b->endetimestamp==null)))
			{
				if($b->negativ)
				{
					//Negativ-Recht
					if(!is_null($b->oe_kurzbz))
					{
						$childoes = $oe->getChilds($b->oe_kurzbz);
						foreach($childoes as $row)
							$not .="'".$this->db_escape($row)."',";
					}
					else
						return array();
				}
				else
				{
					if(!is_null($b->oe_kurzbz))
					{
						$childoes = $oe->getChilds($b->oe_kurzbz);
						foreach($childoes as $row)
							$in .= "'".$this->db_escape($row)."',";
					}
					else
					{
						//Wenn NULL dann berechtigung auf alles
						$all = true;
						break;
					}
				}
			}
		}

		if(!$all)
		{
			if($in=='')
				return array();
			else
				$in = ' AND oe_kurzbz IN('.mb_substr($in,0, mb_strlen($in)-1).')';
		}
		else
		{
			$in='';
		}

		if($not!='')
			$not = ' AND oe_kurzbz NOT IN('.mb_substr($not,0, mb_strlen($not)-1).')';

		$qry = "SELECT fachbereich_kurzbz FROM public.tbl_fachbereich WHERE 1=1 $in $not";

		if($this->db_query($qry))
			while($row = $this->db_fetch_object())
				$fachbereich_kurzbz[]=$row->fachbereich_kurzbz;

		$fachbereich_kurzbz=array_unique($fachbereich_kurzbz);
		sort($fachbereich_kurzbz);
		return $fachbereich_kurzbz;
	}

	/**
	 * Gibt Array mit den Organisationseinheiten zurueck fuer welche die
	 * Person eine Berechtigung besitzt.
	 * Optional wird auf Berechtigung eingeschraenkt.
	 */
	public function getOEkurzbz($berechtigung_kurzbz=null)
	{
		$oe_kurzbz=array();
		$timestamp=time();
		$not=array();
		$all=false;
		$oe = new organisationseinheit();
		foreach ($this->berechtigungen as $b)
		{
			if	(($berechtigung_kurzbz==$b->berechtigung_kurzbz || $berechtigung_kurzbz==null || (mb_strpos($berechtigung_kurzbz,':')!==false && mb_substr($berechtigung_kurzbz,0,mb_strpos($berechtigung_kurzbz,':'))==$b->berechtigung_kurzbz))
				&& (($timestamp>$b->starttimestamp || $b->starttimestamp==null) && ($timestamp<$b->endetimestamp || $b->endetimestamp==null)))
			{
				if($b->negativ)
				{
					//Negativ-Recht
					if(!is_null($b->oe_kurzbz))
					{
						$childoes = $oe->getChilds($b->oe_kurzbz);
						foreach($childoes as $row)
							$not[] = $row;
					}
					else
						return array();
				}
				else
				{
					if($b->kostenstelle_id != '')
					{
						$kst = new wawi_kostenstelle();
						$kst->load($b->kostenstelle_id);
						$oe_kurzbz[] = $kst->oe_kurzbz;
					}
					else
					{
						if(!is_null($b->oe_kurzbz))
						{
							$childoes = $oe->getChilds($b->oe_kurzbz);
							foreach($childoes as $row)
								$oe_kurzbz[] = $row;
						}
						else
						{
							$all=true;
							break;
						}
					}
				}
			}
		}

		if($all)
		{
			$oe->loadParentsArray();
			$oe_kurzbz = array_keys(organisationseinheit::$oe_parents_array);
		}
		$oe_kurzbz = array_diff($oe_kurzbz, $not);
		$oe_kurzbz=array_unique($oe_kurzbz);
		sort($oe_kurzbz);
		return $oe_kurzbz;
	}

	/**
	 * Gibt Array mit den Kostenstellen zurueck fuer welche die
	 * Person eine Berechtigung besitzt.
	 * Optional wird auf Berechtigung eingeschraenkt.
	 */
	public function getKostenstelle($berechtigung_kurzbz=null)
	{
		$oe_kurzbz=array();
		$not = array();
		$not_id = array();
		$kst_id = array();
		$kostenstellen = array();
		$timestamp=time();
		$all=false;
		$oe = new organisationseinheit();

		foreach ($this->berechtigungen as $b)
		{
			if(!mb_strstr($b->berechtigung_kurzbz,'wawi/'))
				continue;

			if	(($berechtigung_kurzbz==$b->berechtigung_kurzbz || (mb_strpos($berechtigung_kurzbz,':')!==false &&  mb_substr($berechtigung_kurzbz,0,mb_strpos($berechtigung_kurzbz,':'))==$b->berechtigung_kurzbz))
				&& (($timestamp>$b->starttimestamp || $b->starttimestamp==null) && ($timestamp<$b->endetimestamp || $b->endetimestamp==null)))
			{
				if($b->negativ)
				{
					//Negativ-Recht
					if(!is_null($b->oe_kurzbz))
					{
						$childoes = $oe->getChilds($b->oe_kurzbz);
						foreach($childoes as $row)
							$not[] = $row;
					}
					elseif($b->kostenstelle_id!='')
					{
						$not_id[] = $b->kostenstelle_id;
					}
					else
						return array();
				}
				else
				{
					if($b->oe_kurzbz!='')
					{
						$childoes = $oe->getChilds($b->oe_kurzbz);
						foreach($childoes as $row)
							$oe_kurzbz[] = $row;
					}
					elseif($b->kostenstelle_id!='')
					{
						$kst_id[]=$b->kostenstelle_id;
					}
					else
					{
						$all=true;
						break;
					}
				}
			}

		}

		$qry = "SELECT distinct kostenstelle_id FROM wawi.tbl_kostenstelle";

		if(!$all)
		{
			if(count($kst_id)==0 && count($oe_kurzbz)==0)
				return array();
			$qry.="
				WHERE
					(";
			if(count($kst_id)>0)
				$qry.=" kostenstelle_id IN(".$this->db_implode4SQL($kst_id).")";
			if(count($oe_kurzbz)>0)
			{
				if(count($kst_id)>0)
					$qry.= ' OR ';
				$qry.=" oe_kurzbz IN(".$this->db_implode4SQL($oe_kurzbz).")";
			}
			$qry.=")";
			if(count($not_id)>0)
				$qry.=" AND kostenstelle_id NOT IN(".$this->db_implode4SQL($not_id).")";
			if(count($not)>0)
				$qry.=" AND oe_kurzbz NOT IN(".$this->db_implode4SQL($not).")";
		}

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$kostenstellen[] = $row->kostenstelle_id;
			}
		}
		return $kostenstellen;
	}

	/**
	 * Liefert die UID der Benutzer, die Freigabeberechtigt für eine bestimmte Kostenstelle oder
	 * Organisationseinheit sind.
	 * Es werden nur die Benutzer zurückgeliefert die genau auf diese Organisationseinheit das Freigaberecht haben
	 * Uebergeordnete Benutzer werden nicht geliefert
	 *
	 * @param $kostenstelle_id
	 * @param $oe_kurzbz
	 */
	public function getFreigabeBenutzer($kostenstelle_id, $oe_kurzbz=null)
	{
		if($kostenstelle_id=='' && $oe_kurzbz=='')
		{
			$this->errormsg = 'Kostenstelle und Organisationseinheit darf nicht gleichzeitig leer sein';
			return false;
		}

		if($kostenstelle_id!='' && $oe_kurzbz!='')
		{
			$this->errormsg = 'Kostenstelle und Organisationseinheit darf nicht gleichzeitig gesetzt sein';
			return false;
		}

		$where = '';
		if($kostenstelle_id!='')
			$where.=" kostenstelle_id=".$this->db_add_param($kostenstelle_id, FHC_INTEGER);
		elseif($oe_kurzbz!='')
			$where.=" oe_kurzbz=".$this->db_add_param($oe_kurzbz);
		$where .=" AND berechtigung_kurzbz='wawi/freigabe'";
		$where .=" AND (start<=now() OR start is null) AND (ende>=now() OR ende is null)";


		$qry = "SELECT uid, negativ FROM system.tbl_benutzerrolle WHERE ".$where;
		$qry .= " UNION
			SELECT uid, negativ
			FROM
				system.tbl_benutzerrolle
				JOIN system.tbl_rolle USING(rolle_kurzbz)
				JOIN system.tbl_rolleberechtigung USING(berechtigung_kurzbz)
			WHERE ".$where;

		$freigabebenutzer=array();
		$not = array();

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				if($this->db_parse_bool($row->negativ)==true)
					$not[]=$row->uid;
				$freigabebenutzer[]=$row->uid;
			}

			return array_diff($freigabebenutzer,$not);
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der FreigabeBenutzer';
			return false;
		}
	}

	/**
	 * Liefert alle User mit deren Rechte fuer eine Kostenstelle
	 * @param $kostenstelle_id ID der Kostenstelle
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function getKostenstelleUser($kostenstelle_id)
	{
		$qry = "SELECT
					distinct uid, a.art,
					CASE WHEN a.berechtigung_kurzbz is null
						THEN tbl_rolleberechtigung.berechtigung_kurzbz
						ELSE a.berechtigung_kurzbz END as berechtigung_kurzbz
				FROM
				(
				SELECT * FROM system.tbl_benutzerrolle WHERE kostenstelle_id=".$this->db_add_param($kostenstelle_id, FHC_INTEGER)."
				UNION
				SELECT * FROM system.tbl_benutzerrolle WHERE
				oe_kurzbz = (SELECT oe_kurzbz FROM wawi.tbl_kostenstelle WHERE kostenstelle_id=".$this->db_add_param($kostenstelle_id, FHC_INTEGER).")
				OR oe_kurzbz IN(
				WITH RECURSIVE oes(oe_parent_kurzbz) as
				(
					SELECT oe_parent_kurzbz FROM public.tbl_organisationseinheit
					WHERE oe_kurzbz=(SELECT oe_kurzbz FROM wawi.tbl_kostenstelle WHERE kostenstelle_id=".$this->db_add_param($kostenstelle_id, FHC_INTEGER).")
					UNION ALL
					SELECT o.oe_parent_kurzbz FROM public.tbl_organisationseinheit o, oes
					WHERE o.oe_kurzbz=oes.oe_parent_kurzbz
				)
				SELECT oe_parent_kurzbz
				FROM oes)
				) as a
				LEFT JOIN system.tbl_rolleberechtigung USING(rolle_kurzbz)
				JOIN public.tbl_benutzer USING(uid)
				WHERE tbl_benutzer.aktiv AND (start is null OR start<=now()) AND (ende is null OR ende>=now()) AND negativ=false";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new benutzerberechtigung();

				$obj->berechtigung_kurzbz = $row->berechtigung_kurzbz;
				$obj->uid = $row->uid;
				$obj->art = $row->art;

				$this->berechtigungen[] = $obj;
			}
		}
	}

	/**
	 * Laedt die Benutzer zu einer Berechtigung. Wenn $inklusiveRollen true ist (default), wird ein UNION mit der tbl_rolleberechtigung ausgefuehrt
	 *
	 * @param string $berechtigung_kurzbz Kurzbezeichnung der Berechtigung, deren Rollen geladen werden sollen
	 * @param boolean $inklusiveRollen Default TRUE. Wenn true, wird ein UNION SELECT mit der tbl_rolleberechtigung ausgefuehrt
	 * @param string $oe_kurzbz Organisationseinheit
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function getBenutzerFromBerechtigung($berechtigung_kurzbz, $inklusiveRollen = true, $oe_kurzbz = null)
	{
		$qry = "SELECT
					benutzerberechtigung_id,
					rolle_kurzbz,
					funktion_kurzbz,
					oe_kurzbz,
					uid,
					art,
					berechtigung_kurzbz,
					start,
					ende
				FROM
					system.tbl_benutzerrolle
				WHERE
					berechtigung_kurzbz = ".$this->db_add_param($berechtigung_kurzbz);

		if(!is_null($oe_kurzbz))
		{
			$qry.=" AND oe_kurzbz=".$this->db_add_param($oe_kurzbz);
		}

		if ($inklusiveRollen == true)
		{
			$qry .= "	UNION SELECT
							NULL,
							rolle_kurzbz,
							NULL,
							NULL,
							NULL,
							art,
							berechtigung_kurzbz,
							NULL,
							NULL
						FROM
							system.tbl_rolleberechtigung
						WHERE
							berechtigung_kurzbz = ".$this->db_add_param($berechtigung_kurzbz);
		}
		$qry.= " ORDER BY rolle_kurzbz NULLS LAST, funktion_kurzbz NULLS LAST, uid";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new benutzerberechtigung();

				$obj->benutzerberechtigung_id = $row->benutzerberechtigung_id;
				$obj->rolle_kurzbz = $row->rolle_kurzbz;
				$obj->berechtigung_kurzbz = $row->berechtigung_kurzbz;
				$obj->uid = $row->uid;
				$obj->funktion_kurzbz = $row->funktion_kurzbz;
				$obj->art = $row->art;
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->start = $row->start;
				$obj->ende = $row->ende;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der FreigabeBenutzer';
			return false;
		}
	}

	/**
	 * Laedt die Benutzer zu einer Rolle.
	 *
	 * @param string $rolle_kurzbz Kurzbezeichnung der Rolle, deren Benutzer geladen werden sollen
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function getBenutzerFromRolle($rolle_kurzbz)
	{
		$qry = "SELECT
					*
				FROM
					system.tbl_benutzerrolle
				WHERE
					rolle_kurzbz = ".$this->db_add_param($rolle_kurzbz);

		$qry.= " ORDER BY rolle_kurzbz NULLS LAST, funktion_kurzbz NULLS LAST, uid";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new benutzerberechtigung();

				$obj->benutzerberechtigung_id = $row->benutzerberechtigung_id;
				$obj->rolle_kurzbz = $row->rolle_kurzbz;
				$obj->berechtigung_kurzbz = $row->berechtigung_kurzbz;
				$obj->uid = $row->uid;
				$obj->funktion_kurzbz = $row->funktion_kurzbz;
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->art = $row->art;
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->start = $row->start;
				$obj->ende = $row->ende;
				$obj->negativ = $this->db_parse_bool($row->negativ);
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->kostenstelle_id = $row->kostenstelle_id;
				$obj->anmerkung = $row->anmerkung;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der FreigabeBenutzer';
			return false;
		}
	}
}
?>
