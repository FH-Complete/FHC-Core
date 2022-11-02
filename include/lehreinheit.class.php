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
require_once(dirname(__FILE__).'/log.class.php');
require_once(dirname(__FILE__).'/../config/global.config.inc.php');

class lehreinheit extends basis_db
{
	public $new;      					// boolean
	public $lehreinheiten = array();	// lehreinheit Objekt

	//Tabellenspalten
	public $lehreinheit_id;			// integer
	public $lehrveranstaltung_id;		// integer
	public $studiensemester_kurzbz; 	// varchar(16)
	public $lehrfach_id;				// integer
	public $lf_kurzbz;
	public $lf_bez;
	public $lf_aktiv;
	public $lehrform_kurzbz;			// varchar(8)
	public $stundenblockung;			// smalint
	public $wochenrythmus;				// smalint
	public $start_kw;					// smalint
	public $raumtyp;					// varchar(8)
	public $raumtypalternativ;			// varchar(8)
	public $lehre;						// boolean
	public $anmerkung;					// varchar(255)
	public $unr;						// integer
	public $lvnr;						// bigint
	public $insertamum;					// timestamp
	public $insertvon;					// varchar(16)
	public $updateamum;					// timestamp
	public $updatevon;					// varchar(16)
	public $sprache;					// varchar(16)
	public $ext_id;						// bigint
	public $gewicht=1;					// smallint

	public $anz=0;						//Zahler fuer erweiterte Attribute
	public $mitarbeiter_uid=array();
	public $studiengang_kz=array();
	public $semester=array();
	public $verband=array();
	public $gruppe=array();
	public $gruppe_kurzbz=array();
	public $titel=array();
	public $lehrform=array();

	/**
	 * Konstruktor - Laedt optional eine LE
	 * @param $gruppe_kurzbz
	 */
	public function __construct($lehreinheit_id=null)
	{
		parent::__construct();
		if($lehreinheit_id!=null)
			$this->load($lehreinheit_id);
	}

	/**
	 * Laedt die LE
	 * @param lehreinheit_id
	 */
	public function load($lehreinheit_id)
	{
		if(!is_numeric($lehreinheit_id))
		{
			$this->errormsg = 'Lehreinheit_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM lehre.tbl_lehreinheit WHERE lehreinheit_id=".$this->db_add_param($lehreinheit_id, FHC_INTEGER).';';

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->lehreinheit_id = $row->lehreinheit_id;
				$this->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$this->lehrfach_id = $row->lehrfach_id;
				$this->lehrform_kurzbz = $row->lehrform_kurzbz;
				$this->stundenblockung = $row->stundenblockung;
				$this->wochenrythmus = $row->wochenrythmus;
				$this->start_kw = $row->start_kw;
				$this->raumtyp = $row->raumtyp;
				$this->raumtypalternativ = $row->raumtypalternativ;
				$this->lehre = $this->db_parse_bool($row->lehre);
				$this->anmerkung = $row->anmerkung;
				$this->unr = $row->unr;
				$this->lvnr = $row->lvnr;
				$this->sprache = $row->sprache;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->ext_id = $row->ext_id;
				$this->gewicht = $row->gewicht;
				return true;
			}
			else
			{
				$this->errormsg = 'Es existiert keine Lehreinheit mit dieser ID';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Lehreinheit';
			return false;
		}
	}

	/**
	 * Laedt die LE von der View mit erweiterten Attributen
	 * @param lehreinheit_id
	 */
	public function loadLE($lehreinheit_id)
	{
		$this->errormsg ='';
		if(!is_numeric($lehreinheit_id))
		{
			$this->errormsg = 'Lehreinheit_id muss eine gueltige Zahl sein';
		}

		$qry = "SELECT * FROM campus.vw_lehreinheit WHERE lehreinheit_id=".$this->db_add_param($lehreinheit_id, FHC_INTEGER).';';

		if($this->db_query($qry))
		{
			$this->anz=0;
			while($row = $this->db_fetch_object())
			{
				$this->lehreinheit_id = $row->lehreinheit_id;
				$this->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$this->lehrfach_id = $row->lehrfach_id;
				$this->lehrform_kurzbz = $row->lehrform_kurzbz;
				$this->stundenblockung = $row->stundenblockung;
				$this->wochenrythmus = $row->wochenrythmus;
				$this->start_kw = $row->start_kw;
				$this->raumtyp = $row->raumtyp;
				$this->raumtypalternativ = $row->raumtypalternativ;
				$this->sprache = $row->sprache;
				$this->lehre = $this->db_parse_bool($row->lehre);
				$this->anmerkung = $row->anmerkung;
				$this->unr = $row->unr;
				$this->lvnr = $row->lvnr;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				//$this->ext_id = $row->ext_id;
				$this->farbe = $row->farbe;
				$this->lf_kurzbz= $row->lehrfach;
				$this->lf_bez= $row->lehrfach_bez;
				$this->lf_aktiv= $row->aktiv;

				$this->mitarbeiter_uid[$this->anz]	= $row->mitarbeiter_uid;
				$this->studiengang_kz[$this->anz] 	= $row->studiengang_kz;
				$this->semester[$this->anz] 			= $row->semester;
				$this->verband[$this->anz] 			= $row->verband;
				$this->gruppe[$this->anz] 			= $row->gruppe;
				$this->gruppe_kurzbz[$this->anz] 		= $row->gruppe_kurzbz;
				$this->titel[$this->anz] 			= '';

				$this->anz++;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Lehreinheit';
			return false;
		}
	}

	/**
	 * Laedt die Lehreinheiten zu einer Lehrveranstaltung
	 *
	 * @param $lehrveranstaltung_id
	 * @param $studiensemester_kurzbz
	 * @param $uid
	 * @param $fachbereich_kurzbz
	 * @return boolean
	 */
	public function load_lehreinheiten($lehrveranstaltung_id, $studiensemester_kurzbz, $uid='', $fachbereich_kurzbz='')
	{
		$this->lehreinheiten = array();
		$this->errormsg ='';

		$qry = "SELECT * FROM lehre.tbl_lehreinheit WHERE
				lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER)."
				AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);

		if($uid!='')
			$qry .= " AND lehreinheit_id IN ( SELECT lehreinheit_id FROM lehre.tbl_lehreinheitmitarbeiter WHERE mitarbeiter_uid=".$this->db_add_param($uid).")";

		if($fachbereich_kurzbz!='')
			$qry .= " AND EXISTS ( SELECT 1 FROM lehre.tbl_lehrveranstaltung JOIN public.tbl_fachbereich USING(oe_kurzbz) WHERE fachbereich_kurzbz=".$this->db_add_param($fachbereich_kurzbz)." AND lehrveranstaltung_id=tbl_lehreinheit.lehrfach_id)";

		$qry.= " ORDER BY lehreinheit_id;";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$le_obj = new lehreinheit();

				$le_obj->lehreinheit_id = $row->lehreinheit_id;
				$le_obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$le_obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$le_obj->lehrfach_id = $row->lehrfach_id;
				$le_obj->lehrform_kurzbz = $row->lehrform_kurzbz;
				$le_obj->stundenblockung = $row->stundenblockung;
				$le_obj->wochenrythmus = $row->wochenrythmus;
				$le_obj->start_kw = $row->start_kw;
				$le_obj->raumtyp = $row->raumtyp;
				$le_obj->raumtypalternativ = $row->raumtypalternativ;
				$le_obj->lehre = $this->db_parse_bool($row->lehre);
				$le_obj->anmerkung = $row->anmerkung;
				$le_obj->unr = $row->unr;
				$le_obj->lvnr = $row->lvnr;
				$le_obj->sprache = $row->sprache;
				$le_obj->insertamum = $row->insertamum;
				$le_obj->insertvon = $row->insertvon;
				$le_obj->updateamum = $row->updateamum;
				$le_obj->updatevon = $row->updatevon;
				$le_obj->ext_id = $row->ext_id;
				$le_obj->gewicht = $row->gewicht;

				$this->lehreinheiten[] = $le_obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Lehreinheiten';
			return false;
		}
	}

    /**
     * Laedt alle vorhandenen Lehreinheiten zu einer Lehrveranstaltung
     *
     * @param $lehrveranstaltung_id
     * @return bool
     */
	public function load_all_lehreinheiten($lehrveranstaltung_id)
    {
        $this->lehreinheiten = array();
        $this->errormsg ='';

        $qry = "SELECT * FROM lehre.tbl_lehreinheit WHERE
				lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER)."
				ORDER BY lehreinheit_id;";

        if($this->db_query($qry))
        {
            while($row = $this->db_fetch_object())
            {
                $le_obj = new lehreinheit();

                $le_obj->lehreinheit_id = $row->lehreinheit_id;
                $le_obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
                $le_obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
                $le_obj->lehrfach_id = $row->lehrfach_id;
                $le_obj->lehrform_kurzbz = $row->lehrform_kurzbz;
                $le_obj->stundenblockung = $row->stundenblockung;
                $le_obj->wochenrythmus = $row->wochenrythmus;
                $le_obj->start_kw = $row->start_kw;
                $le_obj->raumtyp = $row->raumtyp;
                $le_obj->raumtypalternativ = $row->raumtypalternativ;
                $le_obj->lehre = $this->db_parse_bool($row->lehre);
                $le_obj->anmerkung = $row->anmerkung;
                $le_obj->unr = $row->unr;
                $le_obj->lvnr = $row->lvnr;
                $le_obj->sprache = $row->sprache;
                $le_obj->insertamum = $row->insertamum;
                $le_obj->insertvon = $row->insertvon;
                $le_obj->updateamum = $row->updateamum;
                $le_obj->updatevon = $row->updatevon;
                $le_obj->ext_id = $row->ext_id;
                $le_obj->gewicht = $row->gewicht;

                $this->lehreinheiten[] = $le_obj;
            }
            return true;
        }
        else
        {
            $this->errormsg = 'Fehler beim Laden der Lehreinheiten';
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
		if($this->lehreinheit_id!='' && !is_numeric($this->lehreinheit_id))
		{
			$this->errormsg = 'Lehreinheit_id muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_numeric($this->lehrveranstaltung_id))
		{
			$this->errormsg = 'LehrveranstaltungsNr muss eine gueltige Zahl sein';
			return false;
		}
		if(mb_strlen($this->studiensemester_kurzbz)>16)
		{
			$this->errormsg = 'Studiensemesterkurzbz darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if($this->studiensemester_kurzbz=='')
		{
			$this->errormsg = 'Studiensemester muss angegeben werden';
			return false;
		}
		if(!is_numeric($this->lehrfach_id))
		{
			$this->errormsg = 'Lehrfach_id muss eine gueltige Zahl sein';
			return false;
		}
		if(mb_strlen($this->lehrform_kurzbz)>8)
		{
			$this->errormsg = 'Lehrform_kurzbz darf nicht laenger als 8 Zeichen sein';
			return false;
		}
		if($this->lehrform_kurzbz=='')
		{
			$this->lehrform_kurzbz='SO';
			//TODO
			//$this->errormsg = 'Lehrform muss angegeben werden';
			//return false;
		}
		if(!is_numeric($this->stundenblockung))
		{
			$this->errormsg = 'Stundenblockung muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_numeric($this->wochenrythmus))
		{
			$this->errormsg = 'Wochenrythmus muss eine gueltige Zahl sein';
			return false;
		}
		if($this->start_kw!='' && !is_numeric($this->start_kw))
		{
			$this->errormsg = 'StartKW muss eine gueltige Zahl sein';
			return false;
		}
		if($this->start_kw!='' && ($this->start_kw>53 || $this->start_kw<1))
		{
			$this->errormsg = 'StartKW muss zwischen 1 und 53 liegen';
			return false;
		}
		if(mb_strlen($this->raumtyp)>16)
		{
			$this->errormsg = 'Raumtyp darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->raumtypalternativ)>16)
		{
			$this->errormsg = 'Raumtypalternativ darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if($this->raumtypalternativ=='')
		{
			$this->raumtypalternativ='Dummy';
		}
		if(!is_bool($this->lehre))
		{
			$this->errormsg = 'Lehre muss ein boolscher Wert sein';
			return false;
		}
		/*
		if(mb_strlen($this->anmerkung)>255)
		{
			$this->errormsg = 'Anmerkung darf nicht laenger als 255 Zeichen sein';
			return false;
		}*/
		if($this->unr!='' && !is_numeric($this->unr))
		{
			$this->errormsg = 'UNR muss eine gueltige Zahl sein';
			return false;
		}
		if($this->ext_id!='' && !is_numeric($this->ext_id))
		{
			$this->errormsg = 'Ext_id muss eine gueltige Zahl sein';
			return false;
		}

		if($this->gewicht!='' && !is_numeric($this->gewicht))
		{
			$this->errormsg = 'Gewicht muss eine gueltige Zahl sein';
			return false;
		}
		return true;
	}

	/**
	 * Speichert LE in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save($new=null)
	{
		$this->errormsg ='';

		if(is_null($new))
			$new = $this->new;

		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($new)
		{
			if($this->unr=='')
				$unr="currval('lehre.tbl_lehreinheit_lehreinheit_id_seq')";
			else
				$unr = $this->db_add_param($this->unr, FHC_INTEGER);
			//ToDo ID entfernen
			$qry = 'BEGIN; INSERT INTO lehre.tbl_lehreinheit (lehrveranstaltung_id, studiensemester_kurzbz,
			                                     lehrfach_id, lehrform_kurzbz, stundenblockung, wochenrythmus,
			                                     start_kw, raumtyp, raumtypalternativ, lehre, anmerkung, unr, lvnr, insertamum, insertvon, updateamum, updatevon,  sprache, gewicht)
			        VALUES('.$this->db_add_param($this->lehrveranstaltung_id, FHC_INTEGER).','.
					$this->db_add_param($this->studiensemester_kurzbz).','.
					$this->db_add_param($this->lehrfach_id, FHC_INTEGER).','.
					$this->db_add_param($this->lehrform_kurzbz).','.
					$this->db_add_param($this->stundenblockung, FHC_INTEGER).','.
					$this->db_add_param($this->wochenrythmus, FHC_INTEGER).','.
					$this->db_add_param($this->start_kw, FHC_INTEGER).','.
					$this->db_add_param($this->raumtyp).','.
					$this->db_add_param($this->raumtypalternativ).','.
					$this->db_add_param($this->lehre, FHC_BOOLEAN).','.
					$this->db_add_param($this->anmerkung).','.
					$unr.','.
					$this->db_add_param($this->lvnr, FHC_INTEGER).','.
					$this->db_add_param($this->insertamum).','.
					$this->db_add_param($this->insertvon).','.
					$this->db_add_param($this->updateamum).','.
					$this->db_add_param($this->updatevon).','.
					$this->db_add_param($this->sprache).','.
					$this->db_add_param($this->gewicht, FHC_INTEGER).');';
		}
		else
		{
			$qry = 'UPDATE lehre.tbl_lehreinheit SET'.
			       ' lehrveranstaltung_id='.$this->db_add_param($this->lehrveranstaltung_id, FHC_INTEGER).','.
			       ' studiensemester_kurzbz='.$this->db_add_param($this->studiensemester_kurzbz).','.
			       ' lehrfach_id='.$this->db_add_param($this->lehrfach_id, FHC_INTEGER).','.
			       ' lehrform_kurzbz='.$this->db_add_param($this->lehrform_kurzbz).','.
			       ' stundenblockung='.$this->db_add_param($this->stundenblockung, FHC_INTEGER).','.
			       ' wochenrythmus='.$this->db_add_param($this->wochenrythmus, FHC_INTEGER).','.
			       ' start_kw='.$this->db_add_param($this->start_kw, FHC_INTEGER).','.
			       ' raumtyp='.$this->db_add_param($this->raumtyp).','.
			       ' raumtypalternativ='.$this->db_add_param($this->raumtypalternativ).','.
			       ' lehre='.$this->db_add_param($this->lehre, FHC_BOOLEAN).','.
			       ' anmerkung='.$this->db_add_param($this->anmerkung).','.
			       ' unr='.$this->db_add_param($this->unr, FHC_INTEGER).','.
			       ' lvnr='.$this->db_add_param($this->lvnr, FHC_INTEGER).','.
				   ' updateamum='.$this->db_add_param($this->updateamum).','.
				   ' updatevon='.$this->db_add_param($this->updatevon).','.
				   ' sprache='.$this->db_add_param($this->sprache).', '.
				   ' gewicht='.$this->db_add_param($this->gewicht).' '.
			       " WHERE lehreinheit_id=".$this->db_add_param($this->lehreinheit_id, FHC_INTEGER).";";
		}

		if($this->db_query($qry))
		{
			if($new)
			{
				//Sequence auslesen
				$qry ="SELECT currval('lehre.tbl_lehreinheit_lehreinheit_id_seq') AS lehreinheit_id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->lehreinheit_id = $row->lehreinheit_id;
						$this->db_query('COMMIT;');
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
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der LE: '.$qry;
			return false;
		}
	}

	/**
	 * Prueft die geladene Lehrveranstaltung auf Kollisionen im Stundenplan.
	 * Rueckgabewert 'false' und die Fehlermeldung steht in '$this->errormsg'.
	 * @param string	datum	gewuenschtes Datum YYYY-MM-TT
	 * @param integer	stunde	gewuenschte Stunde
	 * @param string	ort		gewuenschter Ort
	 * @param string	db_stpl_table	Tabllenname des Stundenplans im DBMS
	 * @return boolean true=ok, false=error
	 */
	public function check_lva($datum,$stunde,$ort,$stpl_table)
	{

		$this->errormsg ='';

		$stg_obj = new studiengang();
		$stg_obj->getAll();

		$ignore_reservation=false;
		$ignore_zeitsperre=false;
		// Parameter Checken
		// Bezeichnung der Stundenplan-Tabelle und des Keys
		$stpl_id=$stpl_table.TABLE_ID;
		$stpl_table='lehre.'.TABLE_BEGIN.$stpl_table;

		//Lektoren SQL
		$sql_lkt='';
		foreach ($this->mitarbeiter_uid as $lkt)
			$sql_lkt.="OR mitarbeiter_uid=".$this->db_add_param($lkt).' ';
		$sql_lkt=mb_substr($sql_lkt,3);
		$sql_lkt="(($sql_lkt) AND mitarbeiter_uid not in (".$this->db_implode4SQL(unserialize(KOLLISIONSFREIE_USER))."))";

		// Datenbank abfragen
		$sql_query="SELECT $stpl_id FROM $stpl_table
					WHERE datum=".$this->db_add_param($datum)." AND stunde=".$this->db_add_param($stunde)."
					AND (ort_kurzbz=".$this->db_add_param($ort)." OR $sql_lkt)";
		if (is_numeric($this->unr))
			$sql_query.=" AND unr!=".$this->db_add_param($this->unr);

		if (!$this->db_query($sql_query))
		{
			$this->errormsg=$this->db_last_error();
			return false;
		}
		$erg_stpl=$this->db_result;

		$anzahl=$this->db_num_rows($erg_stpl);
		//Check
		if ($anzahl==0)
		{
			//Gruppen / Verbaende pruefen
			$sql_query="SELECT $stpl_id, studiengang_kz, semester, verband, gruppe_kurzbz, stunde, gruppe FROM $stpl_table
					WHERE datum=".$this->db_add_param($datum)." AND stunde=".$this->db_add_param($stunde);

			// Direkte Lehreinheitsgruppen kollidieren nicht
			$sql_query.=" AND NOT EXISTS(SELECT 1 FROM public.tbl_gruppe g WHERE g.gruppe_kurzbz=$stpl_table.gruppe_kurzbz AND direktinskription=true)";
			if (is_numeric($this->unr))
				$sql_query.=" AND unr!=".$this->db_add_param($this->unr)." AND (1=2 ";

			for($anz=0;$anz<count($this->studiengang_kz);$anz++)
			{
				// Direkte Gruppen kollidieren nicht
				$direktgruppe = false;
				if($this->gruppe_kurzbz[$anz]!=null && $this->gruppe_kurzbz[$anz]!='' && $this->gruppe_kurzbz[$anz]!=' ')
				{
					$grp_obj = new gruppe();
					$grp_obj->load($this->gruppe_kurzbz[$anz]);
					if($grp_obj->direktinskription)
					{
						$direktgruppe = true;
					}
				}
				if(!$direktgruppe)
				{
					$sql_query.=" OR ((studiengang_kz=".$this->db_add_param($this->studiengang_kz[$anz])." AND semester=".$this->db_add_param($this->semester[$anz]).")";

					if ($this->gruppe_kurzbz[$anz]!=null && $this->gruppe_kurzbz[$anz]!='' && $this->gruppe_kurzbz[$anz]!=' ')
					{
						$sql_query.=" OR (gruppe_kurzbz=".$this->db_add_param($this->gruppe_kurzbz[$anz]).")";
					}
					else
					{
						if ($this->verband[$anz]!=null && $this->verband[$anz]!='' && $this->verband[$anz]!=' ')
							$sql_query.=" AND (verband=".$this->db_add_param($this->verband[$anz])." OR verband IS NULL OR verband='' OR verband=' ')";
						if ($this->gruppe[$anz]!=null && $this->gruppe[$anz]!='' && $this->gruppe[$anz]!=' ')
							$sql_query.=" AND (gruppe=".$this->db_add_param($this->gruppe[$anz])." OR gruppe IS NULL OR gruppe='' OR gruppe=' ')";
					}
					$sql_query.=')';
				}
			}
			$sql_query.=")";

			if (!$this->db_query($sql_query))
			{
				$this->errormsg=$this->db_last_error();
				return false;
			}
			$erg_stpl=$this->db_result;

			$anzahl=$this->db_num_rows($erg_stpl);
			if($anzahl==0)
			{
				// Reservierungen pruefen?
				if (!$ignore_reservation)
				{
					// Datenbank abfragen  	( studiengang_kz, titel, beschreibung )
					//Lektoren SQL
					$sql_lkt='';
					foreach ($this->mitarbeiter_uid as $lkt)
						$sql_lkt.="OR uid='$lkt' ";
					$sql_lkt=mb_substr($sql_lkt,3);
					$sql_lkt="(($sql_lkt) AND uid not in (".$this->db_implode4SQL(unserialize(KOLLISIONSFREIE_USER))."))";
					$sql_query="SELECT reservierung_id AS id, uid AS lektor, stg_kurzbz, ort_kurzbz, semester, verband, gruppe, gruppe_kurzbz, datum, stunde
								FROM lehre.vw_reservierung
								WHERE datum=".$this->db_add_param($datum)." AND stunde=".$this->db_add_param($stunde)."
								AND (ort_kurzbz=".$this->db_add_param($ort)." OR $sql_lkt)";

					if (!$this->db_query($sql_query))
					{
						$this->errormsg=$sql_query.$this->db_last_error();
						return false;
					}
					$erg_res=$this->db_result;
					$anz_res=$this->db_num_rows($erg_res);
					//Check
					if ($anz_res==0)
					{
						// Zeitsperren pruefen?
						if (!$ignore_zeitsperre)
						{
							// Datenbank abfragen  	( studiengang_kz, titel, beschreibung )
							//Lektoren SQL
							$sql_lkt='';
							foreach ($this->mitarbeiter_uid as $lkt)
								$sql_lkt.="OR mitarbeiter_uid=".$this->db_add_param($lkt)." ";
							$sql_lkt=mb_substr($sql_lkt,3);
							$sql_query="SELECT * FROM campus.tbl_zeitsperre
											WHERE ($sql_lkt) AND
												(  (vondatum<".$this->db_add_param($datum)." AND bisdatum>".$this->db_add_param($datum).")
												OR (vondatum=".$this->db_add_param($datum)." AND bisdatum=".$this->db_add_param($datum)." AND vonstunde<=".$this->db_add_param($stunde)." AND bisstunde>=".$this->db_add_param($stunde).")
												OR (vondatum=".$this->db_add_param($datum)." AND bisdatum>".$this->db_add_param($datum)." AND vonstunde<=".$this->db_add_param($stunde).")
												OR (vondatum<".$this->db_add_param($datum)." AND bisdatum=".$this->db_add_param($datum)." AND bisstunde>=".$this->db_add_param($stunde).") )";
							//echo $sql_query.'<br>';
							if (!$this->db_query($sql_query))
							{
								$this->errormsg=$sql_query.$this->db_last_error();
								return false;
							}
							$erg_zs=$this->db_result;
							$anz_zs=$this->db_num_rows($erg_zs);
							//Check
							if ($anz_zs==0)
								return true;
							else
							{
								$row=$this->db_fetch_object($erg_zs);
                                if ($row->zeitsperretyp_kurzbz != 'ZVerfueg')
                                {
                                    $this->errormsg = "Kollision (Zeitsperre): $row->zeitsperre_id|$row->mitarbeiter_uid|$row->zeitsperretyp_kurzbz|$row->bezeichnung|$row->vondatum/$row->vonstunde-$row->bisdatum/$row->bisstunde - $row->vertretung_uid";
                                    return false;
                                }
							}
						}
						return true;
					}
					else
					{
						$row=$this->db_fetch_object($erg_res);
						$this->errormsg="Kollision (Reservierung): $row->id|$row->lektor|$row->ort_kurzbz|$row->stg_kurzbz-$row->semester$row->verband$row->gruppe$row->gruppe_kurzbz - $row->datum/$row->stunde";
						return false;
					}
				}
				return true;
			}
			else
			{
				$row=$this->db_fetch_object($erg_stpl);
				$this->errormsg="Kollision mit StundenplanID($stpl_id): ".$row->$stpl_id." | $datum - $row->stunde | $ort | ".($row->gruppe_kurzbz!=''?$row->gruppe_kurzbz:$stg_obj->kuerzel_arr[$row->studiengang_kz]."-$row->semester$row->verband$row->gruppe");
				return false;
			}
		}
		else
		{
			$row=$this->db_fetch_row($erg_stpl);
			$this->errormsg="Kollision mit StundenplanID($stpl_id): $row[0] | $datum | $ort";
			return false;
		}
	}

	/**
	 * Speichert die geladene Lehreinheit im Stundenplan.
	 * Rueckgabewert 'false' und die Fehlermeldung steht in '$this->errormsg'.
	 * @param string	datum	gewuenschtes Datum YYYY-MM-TT
	 * @param integer	stunde	gewuenschte Stunde
	 * @param string	ort		gewuenschter Ort
	 * @param string	db_stpl_table	Tabllenname des Stundenplans im DBMS
	 * @param string	user	UID des aktuellen Bentzers
	 * @return boolean true=ok, false=error
	 */
	public function save_stpl($datum,$stunde,$ort,$stpl_table,$user)
	{
		$this->errormsg='';
		// Parameter Checken
		// Bezeichnung der Stundenplan-Tabelle und des Keys
		$stpl_table='lehre.'.TABLE_BEGIN.$stpl_table;

		// Variablen pruefen
		if (!is_numeric($this->unr))
		{
			$this->errormsg='Error: UNR ist nicht vorhanden!';
			return false;
		}


		for ($i=0;$i<$this->anz;$i++)
		{
			// Datenbank INSERT
			$sql_query="INSERT INTO $stpl_table
				(unr,mitarbeiter_uid,datum,	stunde,	ort_kurzbz,lehreinheit_id,studiengang_kz,semester,verband,
				gruppe,	gruppe_kurzbz,	titel, updatevon)
				VALUES (".$this->db_add_param($this->unr).",".
						$this->db_add_param($this->mitarbeiter_uid[$i]).",".
						$this->db_add_param($datum).",".
						$this->db_add_param($stunde).",".
						$this->db_add_param($ort).",".
						$this->db_add_param($this->lehreinheit_id, FHC_INTEGER).", ".
						$this->db_add_param($this->studiengang_kz[$i]).",".
						$this->db_add_param($this->semester[$i]).",".
						$this->db_add_param(trim($this->verband[$i]), FHC_STRING, false).",".
						$this->db_add_param(trim($this->gruppe[$i]), FHC_STRING, false);
			if ($this->gruppe_kurzbz[$i]==null)
				$sql_query.=',NULL';
			else
				$sql_query.=",".$this->db_add_param($this->gruppe_kurzbz[$i]);
			$sql_query.=",".$this->db_add_param($this->titel[$i]).",".$this->db_add_param($user).")";
			if (!$this->db_query($sql_query))
			{
				$this->errormsg=$this->db_last_error().$sql_query;
				return false;
			}
		}
		return true;
	}

	/**
	 * Laedt Lehreinheiten
	 * Wenn der Parameter stg_kz NULL ist tritt gruppe_kurzbzb in Kraft.
	 * @param string $gruppe_kurzbz    Einheit
	 * @param string grp    Gruppe
	 * @param string ver    Verband
	 * @param integer sem    Semester
	 * @param integer stg_kz    Kennzahl des Studiengangs
	 * @return boolean
	 */
	public function getLehreinheitLVPL($db_stpl_table,$studiensemester, $type, $stg_kz, $sem, $lektor, $ver=null, $grp=null, $gruppe=null, $order=null, $fachbereich_kurzbz=null, $orgform_kurzbz=null)
	{
		$this->errormsg='';
		$this->lehreinheiten=array();

		$lva_stpl_view=VIEW_BEGIN.'lva_'.$db_stpl_table;

		if (mb_strlen($studiensemester)<=0)
		{
			$this->errormsg='Studiensemester ist nicht gesetzt!(lehreinheit.getLehreinheitLVPL)';
			return false;
		}
		else $where=" studiensemester_kurzbz=".$this->db_add_param($studiensemester);

		if ($type=='lektor')
			$where.=" AND lektor_uid=".$this->db_add_param($lektor);
		elseif ($type=='gruppe')
			$where.=" AND gruppe_kurzbz=".$this->db_add_param($gruppe);
		elseif ($type=='verband')
		{
			$where.=" AND studiengang_kz=".$this->db_add_param($stg_kz);
			if ($sem>0)
				$where.=" AND semester=".$this->db_add_param($sem);
			if (mb_strlen($ver)>0 && $ver!=' ')
				$where.=" AND verband=".$this->db_add_param($ver);
			if (mb_strlen($grp)>0 && $grp!=' ')
				$where.=" AND gruppe=".$this->db_add_param($grp);
		}
		elseif($type=='fachbereich')
		{
			$where.=" AND fachbereich_kurzbz=".$this->db_add_param($fachbereich_kurzbz);
		}
		$sql_query='SELECT *, planstunden-verplant::smallint AS offenestunden
			FROM
				lehre.'.$lva_stpl_view.'
				JOIN lehre.tbl_lehrform ON '.$lva_stpl_view.'.lehrform=tbl_lehrform.lehrform_kurzbz
			WHERE '.$where.' AND verplanen';
		if($orgform_kurzbz!='')
			$sql_query.=" AND ".$this->db_add_param($orgform_kurzbz)."=(Select orgform_kurzbz FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id=(SELECT lehrveranstaltung_id FROM lehre.tbl_lehreinheit WHERE lehreinheit_id=".$lva_stpl_view.".lehreinheit_id))";

		if($order=='')
			$order='offenestunden DESC, lehrfach, lehrform, semester, verband, gruppe, gruppe_kurzbz';

		$sql_query.=" ORDER BY $order;";

		if(!$this->db_query($sql_query))
		{
			$this->errormsg=$this->db_last_error().$sql_query;
			return false;
		}
		while($row = $this->db_fetch_object())
		{
			if(!isset($this->lehreinheiten[$row->unr])) $this->lehreinheiten[$row->unr] = new stdClass();
			$this->lehreinheiten[$row->unr]->lehreinheit_id[]=$row->lehreinheit_id;
			$this->lehreinheiten[$row->unr]->lvnr[]=$row->lvnr;
			$this->lehreinheiten[$row->unr]->unr=$row->unr;
			$this->lehreinheiten[$row->unr]->fachbereich=$row->fachbereich_kurzbz;
			$this->lehreinheiten[$row->unr]->lehrfach_id=$row->lehrfach_id;
			$this->lehreinheiten[$row->unr]->lehrfach[]=$row->lehrfach;
			$this->lehreinheiten[$row->unr]->lehrfach_bez[]=$row->lehrfach_bez;
			$this->lehreinheiten[$row->unr]->lehrfach_farbe[]=$row->lehrfach_farbe;
			$this->lehreinheiten[$row->unr]->lehrform[]=$row->lehrform;
			$this->lehreinheiten[$row->unr]->lektor_uid[]=$row->lektor_uid;
			$this->lehreinheiten[$row->unr]->lektor[]=trim($row->lektor);
			$this->lehreinheiten[$row->unr]->stg_kz[]=$row->studiengang_kz;
			$this->lehreinheiten[$row->unr]->stg[]=$row->studiengang;
			$this->lehreinheiten[$row->unr]->gruppe_kurzbz[]=$row->gruppe_kurzbz;
			$this->lehreinheiten[$row->unr]->semester[]=$row->semester;
			$this->lehreinheiten[$row->unr]->verband[]=$row->verband;
			$this->lehreinheiten[$row->unr]->gruppe[]=$row->gruppe;
			$this->lehreinheiten[$row->unr]->gruppe_kurzbz[]=$row->gruppe_kurzbz;
			$this->lehreinheiten[$row->unr]->raumtyp=$row->raumtyp;
			$this->lehreinheiten[$row->unr]->raumtypalternativ=$row->raumtypalternativ;
			$this->lehreinheiten[$row->unr]->stundenblockung[]=$row->stundenblockung;
			$this->lehreinheiten[$row->unr]->wochenrythmus[]=$row->wochenrythmus;
			$this->lehreinheiten[$row->unr]->semesterstunden[]=$row->semesterstunden;
			$this->lehreinheiten[$row->unr]->planstunden[]=$row->planstunden;
			$this->lehreinheiten[$row->unr]->start_kw[]=$row->start_kw;
			$this->lehreinheiten[$row->unr]->anmerkung[]=$row->anmerkung;
			$this->lehreinheiten[$row->unr]->studiensemester_kurzbz=$row->studiensemester_kurzbz;
			$this->lehreinheiten[$row->unr]->verplant[]=$row->verplant;
			$this->lehreinheiten[$row->unr]->offenestunden[]=$row->offenestunden;
			if (isset($this->lehreinheiten[$row->unr]->verplant_gesamt))
				$this->lehreinheiten[$row->unr]->verplant_gesamt+=$row->verplant;
			else
				$this->lehreinheiten[$row->unr]->verplant_gesamt=$row->verplant;
			$lvb=$row->studiengang.'-'.$row->semester;
			if ($row->verband!='' && $row->verband!=' ' && $row->verband!='0' && $row->verband!=null)
				$lvb.=$row->verband;
			if ($row->gruppe!='' && $row->gruppe!=' ' && $row->gruppe!='0' && $row->gruppe!=null)
				$lvb.=$row->gruppe;
			if ($row->gruppe_kurzbz!='' && $row->gruppe_kurzbz!=null)
				$this->lehreinheiten[$row->unr]->lehrverband[]=$row->gruppe_kurzbz;
			else
				$this->lehreinheiten[$row->unr]->lehrverband[]=$lvb;
			$this->lehreinheiten[$row->unr]->lem[]=array(
				'lehreinheit_id' => $row->lehreinheit_id,
				'mitarbeiter_uid' => $row->lektor_uid);
		}
		return true;
	}

	/**
	 * Loescht eine Lehreinheit
	 * @param lehreinheit_id
	 * @return boolean
	 */
	public function delete($lehreinheit_id)
	{
		$this->errormsg='';
		if(!is_numeric($lehreinheit_id))
		{
			$this->errormsg = 'Lehreinheit_id muss eine gueltige Zahl sein';
			return false;
		}

		//Pruefen ob schon eine Kreuzerlliste fuer diese Lehreinheit angelegt wurde.
		//Falls ja dann wird das loeschen verweigert
		$qry = "SELECT count(*) as anzahl FROM campus.tbl_uebung WHERE lehreinheit_id=".$this->db_add_param($lehreinheit_id, FHC_INTEGER).";";
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				if($row->anzahl>0)
				{
					$this->errormsg = 'Zu dieser Lehreinheit wurde bereits eine Kreuzerlliste angelegt. Solange fuer eine Lehreinheit Kreuzerllisten vorhanden sind, kann diese nicht geloescht werden.';
					return false;
				}
				else
				{
					$this->db_query('BEGIN;');

					//UNDO Befehl zusammenbauen
					$undosql='';

					//LehreinheitMitarbeiter
					$qry = "SELECT * FROM lehre.tbl_lehreinheitmitarbeiter WHERE lehreinheit_id=".$this->db_add_param($lehreinheit_id, FHC_INTEGER).';';
					if($this->db_query($qry))
					{
						while($row = $this->db_fetch_object())
						{
							$undosql.=" INSERT INTO lehre.tbl_lehreinheitmitarbeiter(lehreinheit_id, mitarbeiter_uid, lehrfunktion_kurzbz, planstunden, stundensatz, faktor, anmerkung, bismelden, updateamum, updatevon, insertamum, insertvon, semesterstunden)
							            VALUES(".$this->db_add_param($row->lehreinheit_id, FHC_INTEGER).",".
                                        $this->db_add_param($row->mitarbeiter_uid).",".
                                        $this->db_add_param($row->lehrfunktion_kurzbz).",".
                                        $this->db_add_param($row->planstunden, FHC_INTEGER).",".
                                        $this->db_add_param($row->stundensatz).",".
                                        $this->db_add_param($row->faktor).",".
										$this->db_add_param($row->anmerkung).",".
                                        $this->db_add_param($this->db_parse_bool($row->bismelden), FHC_BOOLEAN).",".
                                        $this->db_add_param($row->updateamum).",".
                                        $this->db_add_param($row->updatevon).",".
                                        $this->db_add_param($row->insertamum).",".
                                        $this->db_add_param($row->insertvon).",".
                                        $this->db_add_param($row->semesterstunden).");";
						}
					}

					//LehreinheitGruppe
					$qry = "SELECT * FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheit_id=".$this->db_add_param($lehreinheit_id, FHC_INTEGER).';';
					if($this->db_query($qry))
					{
						while($row = $this->db_fetch_object())
						{
							$undosql.=" INSERT INTO lehre.tbl_lehreinheitgruppe(lehreinheitgruppe_id, lehreinheit_id, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon)
							            VALUES(".$this->db_add_param($row->lehreinheitgruppe_id, FHC_INTEGER).",".
                                        $this->db_add_param($row->lehreinheit_id, FHC_INTEGER).",".
                                        $this->db_add_param($row->studiengang_kz, FHC_INTEGER).",".
                                        $this->db_add_param($row->semester, FHC_INTEGER).",".
                                        $this->db_add_param($row->verband).",".
                                        $this->db_add_param($row->gruppe).",".
										$this->db_add_param($row->gruppe_kurzbz).",".
                                        $this->db_add_param($row->updateamum).",".
                                        $this->db_add_param($row->updatevon).",".
                                        $this->db_add_param($row->insertamum).",".
                                        $this->db_add_param($row->insertvon).");";
						}
					}

					//Lehreinheit
					$qry = "SELECT * FROM lehre.tbl_lehreinheit WHERE lehreinheit_id=".$this->db_add_param($lehreinheit_id, FHC_INTEGER).';';
					if($this->db_query($qry))
					{
						while($row = $this->db_fetch_object())
						{
							$undosql.=" INSERT INTO lehre.tbl_lehreinheit(lehreinheit_id, lehrveranstaltung_id, studiensemester_kurzbz, lehrfach_id, lehrform_kurzbz, stundenblockung, wochenrythmus, start_kw, raumtyp, raumtypalternativ, sprache, lehre, anmerkung, unr, lvnr,  updateamum, updatevon, insertamum, insertvon)
							            VALUES(".$this->db_add_param($row->lehreinheit_id, FHC_INTEGER).",".
                                        $this->db_add_param($row->lehrveranstaltung_id, FHC_INTEGER).",".
                                        $this->db_add_param($row->studiensemester_kurzbz).",".
                                        $this->db_add_param($row->lehrfach_id, FHC_INTEGER).",".
                                        $this->db_add_param($row->lehrform_kurzbz).",".
                                        $this->db_add_param($row->stundenblockung, FHC_INTEGER).",".
										$this->db_add_param($row->wochenrythmus, FHC_INTEGER).",".
                                        $this->db_add_param($row->start_kw, FHC_INTEGER).",".
                                        $this->db_add_param($row->raumtyp).",".
                                        $this->db_add_param($row->raumtypalternativ).",".
                                        $this->db_add_param($row->sprache).",".
                                        $this->db_add_param($this->db_parse_bool($row->lehre, FHC_BOOLEAN)).",".
										$this->db_add_param($row->anmerkung).",".
                                        $this->db_add_param($row->unr, FHC_INTEGER).",".
                                        $this->db_add_param($row->lvnr, FHC_INTEGER).",".
                                        $this->db_add_param($row->updateamum).",".
                                        $this->db_add_param($row->updatevon).",".
                                        $this->db_add_param($row->insertamum).",".
                                        $this->db_add_param($row->insertvon).");";
						}
					}

					$log = new log();

					//Gruppenzuteilung, Mitarbeiterzuteilung und Lehreinheit loeschen
					$qry = "DELETE FROM lehre.tbl_lehreinheitmitarbeiter WHERE lehreinheit_id=".$this->db_add_param($lehreinheit_id, FHC_INTEGER).";
							DELETE FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheit_id=".$this->db_add_param($lehreinheit_id, FHC_INTEGER).";
							DELETE FROM lehre.tbl_lehreinheit WHERE lehreinheit_id=".$this->db_add_param($lehreinheit_id, FHC_INTEGER).";";

					$log->new = true;
					$log->sql = $qry;
					$log->sqlundo = $undosql;
					$log->executetime = date('Y-m-d H:i:s');
					$log->mitarbeiter_uid = get_uid();
					$log->beschreibung = "Lehreinheit loeschen - $lehreinheit_id";

					if(!$log->save())
					{
						$this->errormsg = 'Fehler beim Schreiben des Log-Eintrages';
						$this->db_query('ROLLBACK;');
						return false;
					}
					else
					{
						if($this->db_query($qry))
						{
							$this->db_query('COMMIT;');
							return true;
						}
						else
						{
							$this->db_query('ROLLBACK;');
							$this->errormsg = $this->db_last_error();
							return false;
						}
					}
				}
			}
			else
			{
				$this->errormsg = 'Fehler beim Loeschen';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Loeschen';
			return false;
		}
	}

	/**
	 * Laedt die Daten zu einer Lehreinheit inklusive Zusatzdaten der LV und des Lehrfachs
	 * @param $lehreinheit_id
	 * @return boolean
	 */
	public function getLehreinheitDetails($lehreinheit_id)
	{
		$qry = "SELECT
					*, tbl_lehrveranstaltung.semester as lv_semester, tbl_lehrveranstaltung.studiengang_kz as lv_studiengang_kz
				FROM
					lehre.tbl_lehreinheit
					JOIN lehre.tbl_lehrveranstaltung as lehrfach ON(lehrfach_id=lehrfach.lehrveranstaltung_id)
					LEFT JOIN public.tbl_fachbereich USING(oe_kurzbz)
					JOIN lehre.tbl_lehrveranstaltung ON(tbl_lehreinheit.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id)
				WHERE
					tbl_lehreinheit.lehreinheit_id=".$this->db_add_param($lehreinheit_id, FHC_INTEGER).';';

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->lehreinheit_id = $row->lehreinheit_id;
				$this->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$this->lehrfach_id = $row->lehrfach_id;
				$this->lehrform_kurzbz = $row->lehrform_kurzbz;
				$this->stundenblockung = $row->stundenblockung;
				$this->wochenrythmus = $row->wochenrythmus;
				$this->start_kw = $row->start_kw;
				$this->raumtyp = $row->raumtyp;
				$this->raumtypalternativ = $row->raumtypalternativ;
				$this->lehre = $this->db_parse_bool($row->lehre);
				$this->anmerkung = $row->anmerkung;
				$this->unr = $row->unr;
				$this->lvnr = $row->lvnr;
				$this->sprache = $row->sprache;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->ext_id = $row->ext_id;

				$this->fachbereich_kurzbz = $row->fachbereich_kurzbz;
				$this->farbe = $row->farbe;
				$this->studiengang_kz = $row->lv_studiengang_kz;
				$this->semester = $row->lv_semester;
				return true;
			}
			else
			{
				$this->errormsg='Kein Eintrag gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenabfrage';
			return false;
		}
	}

	/**
	 * Liefert Studenten die einer Lehreinheit zugeordnet sind.
	 *
	 * @param int $lehreinheit_id
	 * @return array
	 */
	public function getStudenten($lehreinheit_id)
	{
		$qry = 'SELECT uid, vorname, nachname '
				. 'FROM campus.vw_student_lehrveranstaltung '
				. 'JOIN campus.vw_student '
				. 'USING (uid) '
				. 'WHERE lehreinheit_id = ' . $this->db_add_param($lehreinheit_id, FHC_INTEGER)
				. ' ORDER BY nachname';

		$result = $this->db_query($qry);
		$ret = array();

		while($row = $this->db_fetch_object($result))
		{
			$ret[] = $row;
		}

		return $ret;
	}
}
