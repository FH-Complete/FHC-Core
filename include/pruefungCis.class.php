<?php

/*
 * Copyright 2014 fhcomplete.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 *
 *
 * Authors: Stefan Puraner	<puraner@technikum-wien.at>
 */

require_once(dirname(__FILE__) . '/basis_db.class.php');

class pruefungCis extends basis_db
{
    public $new;
    public $result = array();

    public $pruefung_id;                //bigint
    public $mitarbeiter_uid;            //varchar(32)
    public $studiensemester_kurzbz;     //varchar(16)
    public $pruefungsfenster_id;        //bigint
    public $pruefungstyp_kurzbz;        //varchar(16)
    public $titel;                      //varchar(256)
    public $beschreibung;               //beschreibung(text)
    public $methode;                    //varchar(64)
    public $einzeln;                    //boolean
    public $storniert = false;          //boolean
    public $insertvon;                  //varcahr(32)
    public $insertamum;                 //timestamp without timezone
    public $updatevon;                  //varcahr(32)
    public $updateamum;                 //timestamp without timezone
    public $pruefungsintervall;		//smallint

    public $lehrveranstaltungen = array(); //Lehrveranstaltungen zur Prüfung
    public $termine = array();             //Termine zur Prüfung

    /**
     * Konstruktor
     * @param pruefung_id ID der zu ladenden Prüfung
     */
    public function __construct($pruefung_id = null)
    {
        parent::__construct();

        if ($pruefung_id != null)
            $this->load($pruefung_id);
    }

    public function __set($name, $value)
    {
        switch ($name) {
            case 'pruefung_id':
                if (!is_numeric($value))
                    throw new Exception('Attribute pruefung_id must be numeric!"');
                $this->$name = $value;
                break;
            case 'methode':
                if(mb_strlen($value) > 64)
                    throw new Exception('Attribute methode must not be longer than 64 characters!"');
                $this->$name = $value;
                break;
            case 'titel':
                if(mb_strlen($value) > 256)
                    throw new Exception('Attribute methode must not be longer than 256 characters!"');
                $this->$name = $value;
                break;
            default:
                $this->$name = $value;
        }
    }

    public function __get($name)
    {
        return $this->$name;
    }

    /**
     * Prüft Attribute auf Ihre Richtigkeit
     * @return boolean true, wenn alle Prüfungen positiv verlaufen, andernfalls false
     */
    public function validate()
    {
        if(!is_numeric($this->pruefungsfenster_id) && $this->pruefungsfenster_id != null)
        {
            $this->errormsg = "pruefungsfenster_id muss eine gültige Zahl sein.";
            return false;
        }

        if(mb_strlen($this->mitarbeiter_uid) > 32)
        {
            $this->errormsg = "mitarbeiter_uid darf nicht länger als 32 Zeichen sein.";
            return false;
        }

        if(mb_strlen($this->studiensemester_kurzbz) > 16 && $this->studiensemester_kurzbz != null)
        {
            $this->errormsg = "studiensemester_kurzbz darf nicht länger als 16 Zeichen sein.";
            return false;
        }

        if(mb_strlen($this->pruefungstyp_kurzbz) > 16 && $this->pruefungstyp_kurzbz != null)
        {
            $this->errormsg = "pruefungstyp_kurzbz darf nicht länger als 16 Zeichen sein.";
            return false;
        }

        if(mb_strlen($this->titel) > 256)
        {
            $this->errormsg = "pruefungstyp_kurzbz darf nicht länger als 256 Zeichen sein.";
            return false;
        }

        if(mb_strlen($this->methode) > 64)
        {
            $this->errormsg = "methode darf nicht länger als 64 Zeichen sein.";
            return false;
        }

        return true;
    }

    /**
     * speichert einen Prüfungs-Datensatz
     * @param boolean $new gibt an ob es ich um einen neuen Datensatz (true) oder um ein update (false) handelt
     * @return boolean true wenn ok; false im Fehlerfall
     */
    public function save($new = null)
    {
        if(!$this->validate())
        {
            return false;
        }
        else if($new)
        {
            $qry = 'BEGIN; INSERT INTO campus.tbl_pruefung (mitarbeiter_uid, studiensemester_kurzbz, pruefungsfenster_id, pruefungstyp_kurzbz, titel, beschreibung, methode, einzeln, storniert, insertvon, insertamum, pruefungsintervall) '
                    . 'VALUES ('.$this->db_add_param($this->mitarbeiter_uid).', '
                    . $this->db_add_param($this->studiensemester_kurzbz).', '
                    . $this->db_add_param($this->pruefungsfenster_id).', '
                    . $this->db_add_param($this->pruefungstyp_kurzbz).', '
                    . $this->db_add_param($this->titel).', '
                    . $this->db_add_param($this->beschreibung).', '
                    . $this->db_add_param($this->methode).', '
                    . $this->db_add_param($this->einzeln, FHC_BOOLEAN).', '
                    . $this->db_add_param($this->storniert, FHC_BOOLEAN).', '
                    . $this->db_add_param($this->insertvon).', '
                    . 'now(), '
		    . $this->db_add_param($this->pruefungsintervall).''
                    . ');';
        }
        else
        {
            $qry = 'UPDATE campus.tbl_pruefung SET '
                    . 'mitarbeiter_uid='.$this->db_add_param($this->mitarbeiter_uid).', '
                    . 'studiensemester_kurzbz='.$this->db_add_param($this->studiensemester_kurzbz).', '
                    . 'pruefungsfenster_id='.$this->db_add_param($this->pruefungsfenster_id).', '
                    . 'pruefungstyp_kurzbz='.$this->db_add_param($this->pruefungstyp_kurzbz).', '
                    . 'titel='.$this->db_add_param($this->titel).', '
                    . 'beschreibung='.$this->db_add_param($this->beschreibung).', '
                    . 'methode='.$this->db_add_param($this->methode).', '
                    . 'einzeln='.$this->db_add_param($this->einzeln,FHC_BOOLEAN).', '
                    . 'storniert='.$this->db_add_param($this->storniert,FHC_BOOLEAN).', '
                    . 'updatevon='.$this->db_add_param($this->updatevon).', '
                    . 'updateamum=now(), '
		    . 'pruefungsintervall='.$this->db_add_param($this->pruefungsintervall).' '
                    . 'WHERE pruefung_id='.$this->db_add_param($this->pruefung_id).';';
        }

        if($this->db_query($qry))
        {
            if ($new)
            {
                $qry = "SELECT currval('campus.seq_pruefung_pruefung_id') as id";
                if ($this->db_query($qry))
                {
                    if ($row = $this->db_fetch_object())
                    {
                        $this->pruefung_id = $row->id;
                        foreach ($this->lehrveranstaltungen as $lv)
                        {
                            if(!$this->saveLehrveranstaltungPruefung($lv, $this->pruefung_id))
                            {
                                $this->errormsg = 'Fehler beim Speichern der Lehrveranstaltungen.';
                                $this->db_query('ROLLBACK');
                                return false;
                            }
                        }
                        foreach ($this->termine as $termin)
                        {
                            if(!$this->saveTerminPruefung($this->pruefung_id, $termin->beginn, $termin->ende, $termin->max, $termin->min, $termin->sammelklausur))
                            {
                                $this->errormsg = 'Fehler beim Speichern der Termine.';
                                $this->db_query('ROLLBACK');
                                return false;
                            }
                        }
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
            {
		if($this->termine !== NULL)
		{
		    foreach ($this->termine as $termin)
		    {
			if(!$this->updateTerminPruefung($termin->pruefungstermin_id, $this->pruefung_id, $termin->beginn, $termin->ende, $termin->max, $termin->min))
			{
			    $this->errormsg = 'Fehler beim ändern der Termine.ID'.$termin->pruefungstermin_id;
			    $this->db_query('ROLLBACK');
			    return false;
			}
		    }
		}
                foreach ($this->lehrveranstaltungen as $lv)
                    {
                        if(!$this->saveLehrveranstaltungPruefung($lv, $this->pruefung_id))
                        {
                            $this->errormsg = 'Fehler beim Speichern der Lehrveranstaltungen.';
                            $this->db_query('ROLLBACK');
                            return false;
                        }
                    }
                $this->db_query('COMMIT;');
                return true;
            }
            return true;
        }
        else
        {
                $this->db_query('ROLLBACK');
                $this->errormsg = 'Prüfung konnte nicht gespeichert werden.';
                return false;
        }
    }

    /**
     * Lädt einen Datensatz aus der Datenbank
     * @param integer $pruefung_id ID der zu ladenden Prüfung
     * @return boolean true, wenn ok; false im Fehlerfall
     */
    public function load($pruefung_id)
    {
        if(!is_numeric($pruefung_id))
        {
            $this->errormsg = "Prüfung ID ist keine gültige Zahl";
            return false;
        }

        $qry = 'SELECT * FROM campus.tbl_pruefung WHERE pruefung_id='.$this->db_add_param($pruefung_id).';';

        if(!$this->db_query($qry))
        {
            $this->errormsg = "Prüfung konnte nicht geladen werden";
            return false;
        }
        else
        {
            if($row = $this->db_fetch_object())
            {
                $this->pruefung_id = $row->pruefung_id;
                $this->mitarbeiter_uid = $row->mitarbeiter_uid;
                $this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
                $this->pruefungsfenster_id = $row->pruefungsfenster_id;
                $this->pruefungstyp_kurzbz = $row->pruefungstyp_kurzbz;
                $this->titel = $row->titel;
                $this->beschreibung = $row->beschreibung;
                $this->methode = $row->methode;
                $this->einzeln = $this->db_parse_bool($row->einzeln);
                $this->storniert = $this->db_parse_bool($row->storniert);
		$this->pruefungsintervall = $row->pruefungsintervall;
            }
            return true;
        }
    }

    /**
     * Lädt alle Prüfungen zu einer UID
     * @param String $uid UID deren Prüfungen geladen werden sollen
     * @param String $studiensemester_kurzbz optional kann das Laden auf ein Studiensemester beschränkt werden
     * @return boolean true, wenn ok; false, im Fehlerfall
     */
    public function getPruefungByMitarbeiter($uid, $studiensemester_kurzbz=null, $order=null)
    {
        $qry = 'SELECT * FROM campus.tbl_pruefung '
                . 'WHERE mitarbeiter_uid='.$this->db_add_param($uid);
        if($studiensemester_kurzbz!=null)
        {
            $qry .= ' AND studiensemester_kurzbz='.$this->db_add_param($studiensemester_kurzbz);
        }
        if(!is_null($order))
        {
            $qry .= ' ORDER BY '.$order;
        }
        $qry .= ';';

        if(!$this->db_query($qry))
        {
            $this->errormsg = "Prüfungen konnten nicht geladen werden";
            return false;
        }
        else
        {
            while($row = $this->db_fetch_object())
            {
                $obj = new pruefungCis();

                $obj->pruefung_id = $row->pruefung_id;
                $obj->mitarbeiter_uid = $row->mitarbeiter_uid;
                $obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
                $obj->pruefungsfenster_id = $row->pruefungsfenster_id;
                $obj->pruefungstyp_kurzbz = $row->pruefungstyp_kurzbz;
                $obj->titel = $row->titel;
                $obj->beschreibung = $row->beschreibung;
                $obj->methode = $row->methode;
                $obj->einzeln = $this->db_parse_bool($row->einzeln);
                $obj->storniert = $this->db_parse_bool($row->storniert);
		$this->pruefungsintervall = $row->pruefungsintervall;
                $this->result[] = $obj;
            }
            return true;
        }
    }

    /**
     * speichert die zugehörigen LVs zu einer Prüfung
     * @param Integer $lehrveranstaltung_id ID einer Lehrveranstaltung
     * @param Integer $pruefung_id ID einer Prüfung
     * @return boolean true, wenn ok; false, im Fehlerfall
     */
    protected function saveLehrveranstaltungPruefung($lehrveranstaltung_id, $pruefung_id)
    {
        if(!is_numeric($lehrveranstaltung_id))
        {
            $this->errormsg = "Lehrveranstaltung ID muss eine gültige Zahl sein";
            return false;
        }

        if(!is_numeric($pruefung_id))
        {
            $this->errormsg = "Prüfung ID muss eine gültige Zahl sein";
            return false;
        }

        $qry = 'INSERT INTO campus.tbl_lehrveranstaltung_pruefung (lehrveranstaltung_id, pruefung_id) VALUES ('
                .$this->db_add_param($lehrveranstaltung_id).', '
                .$this->db_add_param($pruefung_id).');';

        if(!$this->db_query($qry))
        {
            $this->errormsg = "Lehrveranstaltungen konnten nicht gespeichert werden.";
            return false;
        }
        return true;
    }

    /**
     * lädt alle zum Objekt gehörenden Lehrveranstaltungen
     * @return boolean true, wenn ok; false, im Fehlerfall
     */
    public function getLehrveranstaltungenByPruefung()
    {
        $qry = 'SELECT * FROM campus.tbl_lehrveranstaltung_pruefung WHERE pruefung_id='.$this->db_add_param($this->pruefung_id).';';

        if($this->db_query($qry))
        {
            while($row = $this->db_fetch_object())
            {
                $obj = new stdClass();
                $obj->lehrveranstaltung_pruefung_id = $row->lehrveranstaltung_pruefung_id;
                $obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
                $obj->pruefung_id = $row->pruefung_id;
                array_push($this->lehrveranstaltungen, $obj);
            }
            return true;
        }
        else
        {
            $this->errormsg = "Zugehörige Lehrveranstaltungen konnten nicht geladen werden.";
            return false;
        }

    }

    /**
     * speichert einen Termin zu Prüfung
     * @param integer $pruefung_id ID einer Prüfung
     * @param type $beginn Datum und Uhrzeit vom Beginn einer Prüfung
     * @param type $ende Datum und Uhrzeit vom Ende einer Prüfung
     * @param type $max maximale Teilnehmerzahl
     * @param type $min minimale Teilnehmerzahl
     * @return boolean true, wenn ok; false im Fehlerfall
     */
    public function saveTerminPruefung($pruefung_id, $beginn, $ende, $max, $min, $sammelklausur)
    {
        if(!is_numeric($pruefung_id))
        {
            $this->errormsg = "Pruefung ID muss eine gültige Zahl sein";
            return false;
        }

        $qry = 'INSERT INTO campus.tbl_pruefungstermin (pruefung_id, von, bis, teilnehmer_max, teilnehmer_min, sammelklausur) VALUES ('
                . $this->db_add_param($pruefung_id).', '
                . $this->db_add_param($beginn).', '
                . $this->db_add_param($ende).', '
                . $this->db_add_param($max).', '
                . $this->db_add_param($min).', '
		. $this->db_add_param($sammelklausur).');';

        if(!$this->db_query($qry))
        {
            $this->errormsg = "Termine konnten nicht gespeichert werden!";
            return false;
        }
        return true;
    }

    /**
     * Lädt alle Termine zum Prüfungs-Objekt
     * @return boolean true, wenn ok; false, im Fehlerfall
     */
    public function getTermineByPruefung()
    {
        //$qry = 'SELECT * FROM campus.tbl_pruefungstermin WHERE pruefung_id='.$this->db_add_param($this->pruefung_id).';';
		$fromdate = date("Y-m-d", strtotime("-2 months"));
		$qry = "SELECT * FROM campus.tbl_pruefungstermin WHERE pruefung_id=".$this->db_add_param($this->pruefung_id)."and von > '".$fromdate."';";
        if($this->db_query($qry))
        {
            while($row = $this->db_fetch_object())
            {
                $obj = new stdClass();
                $obj->pruefungstermin_id = $row->pruefungstermin_id;
                $obj->pruefung_id = $row->pruefung_id;
                $obj->von = $row->von;
                $obj->bis = $row->bis;
                $obj->max = $row->teilnehmer_max;
                $obj->min = $row->teilnehmer_min;
		$obj->anmeldung_von= $row->anmeldung_von;
                $obj->anmeldung_bis = $row->anmeldung_bis;
		$obj->ort_kurzbz = $row->ort_kurzbz;
		$obj->sammelklausur = $row->sammelklausur;
                array_push($this->termine, $obj);
            }
            return true;
        }
        else
        {
            $this->errormsg = "Zugehörige Termine konnten nicht geladen werden.";
            return false;
        }

    }

    /**
     * ändert einen Termin zur Prüfung
     * @param integer $pruefungstermin_id ID eines Prüfungstermins
     * @param integer $pruefung_id ID einer Prüfung
     * @param type $beginn Datum und Uhrzeit vom Beginn einer Prüfung
     * @param type $ende Datum und Uhrzeit vom Ende einer Prüfung
     * @param type $max maximale Teilnehmerzahl
     * @param type $min minimale Teilnehmerzahl
     * @return boolean true, wenn ok; false im Fehlerfall
     */
    public function updateTerminPruefung($pruefungstermin_id, $pruefung_id, $beginn, $ende, $max, $min)
    {
        if(!is_numeric($pruefungstermin_id))
        {
            $this->errormsg = "Pruefungstermin ID muss eine gültige Zahl sein.";
            return false;
        }

        $qry = 'UPDATE campus.tbl_pruefungstermin SET '
                . 'pruefung_id='.$this->db_add_param($pruefung_id).', '
                . 'von='.$this->db_add_param($beginn).', '
                . 'bis='.$this->db_add_param($ende).', '
                . 'teilnehmer_max='.$this->db_add_param($max).', '
                . 'teilnehmer_min='.$this->db_add_param($min).' '
		. 'WHERE pruefungstermin_id='.$this->db_add_param($pruefungstermin_id).';';

        if(!$this->db_query($qry))
        {
            $this->errormsg = "Termin konnte nicht geändert werden.";
            return false;
        }
        return true;
    }

    /**
     * Setzt den Storniert-Status einer Prüfung auf True
     * @param integer $pruefung_id ID einer Prüfung
     * @return boolean true, wenn ok; false, im Fehlerfall
     */
    public function pruefungStornieren($pruefung_id)
    {
        if(!is_numeric($pruefung_id))
        {
            $this->errormsg = "Pruefung ID muss eine gültige Zahl sein.";
            return false;
        }

        $qry = 'UPDATE campus.tbl_pruefung SET storniert=true WHERE pruefung_id='.$this->db_add_param($pruefung_id).';';

        if(!$this->db_query($qry))
        {
            $this->errormsg = "Prüfung konnte nicht storniert werden.";
            return false;
        }
        return true;
    }

    /**
     * löscht die Verknüpfung zwischen einer Lehrveranstaltung und einer Prüfung
     * @param integer $lehrveranstaltung_id ID einer Lehrveranstaltung
     * @param integer $pruefung_id ID einer Prüfung
     * @return boolean true, wenn ok; false, im Fehlerfall
     */
    public function deleteLehrveranstaltungPruefung($lehrveranstaltung_id, $pruefung_id){
        if(!is_numeric($lehrveranstaltung_id))
        {
            $this->errormsg = "Lehrveranstaltung ID muss eine gültige Zahl sein.";
            return false;
        }
        if(!is_numeric($pruefung_id))
        {
            $this->errormsg = "Prüfung ID muss eine gültige Zahl sein.";
            return false;
        }

        $qry = 'DELETE FROM campus.tbl_lehrveranstaltung_pruefung WHERE lehrveranstaltung_id='.$this->db_add_param($lehrveranstaltung_id).' AND pruefung_id='.$this->db_add_param($pruefung_id).';';

        if(!$this->db_query($qry))
        {
            $this->errormsg = 'Lehrveranstaltung konnte nicht gelöscht werden.';
            return false;
        }
        return true;
    }

    /**
     * löscht einen Prüfungstermin einer Prüfung
     * @param integer $pruefungstermin_id ID eines Prüfungstermins
     * @return boolean true, wenn ok; false, im Fehlerfall
     */
    public function deleteTerminPruefung($pruefungstermin_id)
    {
        if(!is_numeric($pruefungstermin_id))
        {
            $this->errormsg = "Pruefungstermin ID muss eine gültige Zahl sein.";
            return false;
        }

        $qry = 'DELETE FROM campus.tbl_pruefungstermin WHERE pruefungstermin_id='.$this->db_add_param($pruefungstermin_id).';';

        if(!$this->db_query($qry))
        {
            $this->errormsg = 'Termin konnte nicht gelöscht werden.';
            return false;
        }
        return true;
    }

    /**
     * Lädt alle Prüfungen zur angebenen Lehrveranstaltung
     * @param String|Array $lehrveranstaltung_IDs einzelne ID einer Lehrveranstaltung oder ein Array von IDs
     * @return boolean true, wenn ok; false, im Fehlerfall
     */
    public function getPruefungByLv($lehrveranstaltung_IDs, $uid = null)
    {
        if(empty($lehrveranstaltung_IDs))
        {
           $this->errormsg = "Keine Lehrveranstaltungen übergeben.</br>";
           return false;
        }

	$in = "";
        if (is_array($lehrveranstaltung_IDs))
	{
	    foreach($lehrveranstaltung_IDs as $id)
	    {
		$in.= $id.', ';
	    }
	    $in = substr($in, 0, -2);
	}
	else
	{
	    $in = $lehrveranstaltung_IDs;
	}

        $qry = 'SELECT * FROM campus.tbl_lehrveranstaltung_pruefung WHERE lehrveranstaltung_id IN ('.$in.')';

        if ($uid !== null)
        {
            // LVs entfernen wo schon eine positive Note für UID vorhanden ist
            $qry .= " AND lehrveranstaltung_id NOT IN (
                        SELECT lehrveranstaltung_id 
                        FROM lehre.tbl_pruefung
                        JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
                        WHERE student_uid = " . $this->db_add_param($uid) . "
                        AND note NOT IN (5, 7, 9, 11, 13, 14)
                    UNION
                        SELECT lehrveranstaltung_id 
                        FROM lehre.tbl_zeugnisnote
                        WHERE student_uid = " . $this->db_add_param($uid) . "
                        AND note NOT IN (5, 7, 9, 11, 13, 14)
                    );";
        }

        if($this->db_query($qry))
        {
            while($row = $this->db_fetch_object())
            {
                $obj = new stdClass();
                $obj->lehrveranstaltung_pruefung_id = $row->lehrveranstaltung_pruefung_id;
                $obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
                $obj->pruefung_id = $row->pruefung_id;
                array_push($this->lehrveranstaltungen, $obj);
            }
            return true;
        }
        return false;
    }

    /**
     * Lädt alle Prüfung-Lehrveranstaltung Kombinationen
     * @return boolean true, wenn ok; false, im Fehlerfall
     */
    public function getAll()
    {
	$qry = 'SELECT * FROM campus.tbl_lehrveranstaltung_pruefung;';

        if($this->db_query($qry))
        {
            while($row = $this->db_fetch_object())
            {
                $obj = new stdClass();
                $obj->lehrveranstaltung_pruefung_id = $row->lehrveranstaltung_pruefung_id;
                $obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
                $obj->pruefung_id = $row->pruefung_id;
                array_push($this->lehrveranstaltungen, $obj);
            }
            return true;
        }
        return false;
    }

    /**
     * Lädt alle Prüfungen
     * @param String $mitarbeiter_uid UID des Mitarbeiters (optional)
     * @return boolean
     */
    public function getAllPruefungen($mitarbeiter_uid = NULL)
    {
	$qry = 'SELECT * FROM campus.tbl_pruefung';

	if(!is_null($mitarbeiter_uid))
	{
	    $qry .= ' WHERE mitarbeiter_uid='.$this->db_add_param($mitarbeiter_uid);
	}

	$qry .= ';';

        if(!$this->db_query($qry))
        {
            $this->errormsg = "Prüfungen konnten nicht geladen werden";
            return false;
        }
        else
        {
            while($row = $this->db_fetch_object())
            {
                $obj = new pruefungCis();

                $obj->pruefung_id = $row->pruefung_id;
                $obj->mitarbeiter_uid = $row->mitarbeiter_uid;
                $obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
                $obj->pruefungsfenster_id = $row->pruefungsfenster_id;
                $obj->pruefungstyp_kurzbz = $row->pruefungstyp_kurzbz;
                $obj->titel = $row->titel;
                $obj->beschreibung = $row->beschreibung;
                $obj->methode = $row->methode;
                $obj->einzeln = $this->db_parse_bool($row->einzeln);
                $obj->storniert = $this->db_parse_bool($row->storniert);
		$this->pruefungsintervall = $row->pruefungsintervall;
                $this->result[] = $obj;
            }
            return true;
        }
    }

    /**
     * Lädt den Wert des letzten Studenten in der Anmeldereihung
     * @param type $pruefungstermin_id Id eines Prüfungstermines
     * @return boolean|integer Wert des Letzten in der Reihung oder false, wenn ein Fehler auftritt
     */
    public function getLastOfReihung($pruefungstermin_id)
    {
	$qry = 'SELECT MAX(reihung) FROM campus.tbl_pruefungsanmeldung WHERE '
		. 'pruefungstermin_id='.$this->db_add_param($pruefungstermin_id).';';

	if($this->db_query($qry))
	{
	    $row = $this->db_fetch_object();
	    return $row->max;
	}
	else
	{
	    $this->errormsg = 'Reihung konnte nicht geladen werden.';
            return false;
	}
    }
}
