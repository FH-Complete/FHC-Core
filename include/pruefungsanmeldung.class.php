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

class pruefungsanmeldung extends basis_db {
    public $new;
    public $result = array();

    public $pruefungsanmeldung_id;      //bigint
    public $uid;                        //varchar(32)
    public $pruefungstermin_id;         //bigint
    public $lehrveranstaltung_id;       //bigint
    public $status_kurzbz;              //varchar(32)
    public $wuensche;                   //text
    public $reihung;                    //smallint
    public $kommentar;                  //text
    public $statusupdatevon;		//varchar(32)
    public $statusupdateamum;		//timestamp
    public $anrechnung_id;		//integer
    public $pruefungstyp_kurzbz;		//varchar(32)
    public $insertamum; // timestamp

    /**
     * Konstruktor
     * @param pruefung_id ID der zu ladenden Prüfung
     */
    public function __construct($pruefungsanmeldung_id = null)
    {
        parent::__construct();

        if ($pruefungsanmeldung_id != null)
            $this->load($pruefungsanmeldung_id);
    }

    public function __set($name, $value)
    {
        switch ($name) {
            case 'pruefungsanmeldung_id':
                if (!is_numeric($value))
                    throw new Exception('Attribute pruefungsanmeldung_id must be numeric!');
                $this->$name = $value;
                break;
            case 'pruefungstermin_id':
                if(!is_numeric($value))
                    throw new Exception('Attribute pruefungstermin_id must be numeric!');
                $this->$name = $value;
                break;
            case 'lehrveranstaltung_id':
                if(!is_numeric($value))
                    throw new Exception('Attribute lehrveranstaltung_id must be numeric!');
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
     * speichert eine Prüfungsanmeldung
     * @param type $new
     * @return boolean true, wenn erfolgreich; false im Fehlerfall
     */
    public function save($new = null)
    {
        if ($new == null)
            $new = $this->new;

        if($new)
        {
            $qry = 'INSERT INTO campus.tbl_pruefungsanmeldung (uid, pruefungstermin_id, lehrveranstaltung_id, status_kurzbz, wuensche, reihung, kommentar, anrechnung_id, pruefungstyp_kurzbz) VALUES ('
                    . $this->db_add_param($this->uid).', '
                    . $this->db_add_param($this->pruefungstermin_id).', '
                    . $this->db_add_param($this->lehrveranstaltung_id).', '
                    . $this->db_add_param($this->status_kurzbz).', '
                    . $this->db_add_param($this->wuensche).', '
                    . $this->db_add_param($this->reihung).', '
                    . $this->db_add_param($this->kommentar).', '
		    . $this->db_add_param($this->anrechnung_id).', '
		    . $this->db_add_param($this->pruefungstyp_kurzbz).');';
        }
        else
        {
            $qry = 'UPDATE campus.tbl_pruefungsanmeldung SET '
		    . 'uid='.$this->db_add_param($this->uid).', '
		    . 'pruefungstermin_id='.$this->db_add_param($this->pruefungstermin_id).', '
		    . 'lehrveranstaltung_id='.$this->db_add_param($this->lehrveranstaltung_id).', '
		    . 'status_kurzbz='.$this->db_add_param($this->status_kurzbz).', '
		    . 'wuensche='.$this->db_add_param($this->wuensche).', '
		    . 'reihung='.$this->db_add_param($this->reihung).', '
		    . 'kommentar='.$this->db_add_param($this->kommentar).', '
		    . 'anrechnung_id='.$this->db_add_param($this->anrechnung_id).', '
		    . 'pruefungstyp_kurzbz='.$this->db_add_param($this->pruefungstyp_kurzbz)
		    . ' WHERE pruefungsanmeldung_id='.$this->db_add_param($this->pruefungsanmeldung_id).';';
        }

        if ($this->db_query($qry))
        {
            return true;
        }
        else
        {
            $this->errormsg = 'Fehler beim Speichern der Anmeldung.';
            return false;
        }
    }

    /**
     * Lädt eine Prüfungsanmeldung
     * @param type $pruefungsanmeldung_id
     * @return boolean true, wenn erfolgreich; false im Fehlerfall
     */
    public function load($pruefungsanmeldung_id)
    {
        if(!is_numeric($pruefungsanmeldung_id))
        {
            $this->errormsg = "Anmeldung ID muss eine gültige Zahl sein";
            return false;
        }

        $qry = 'SELECT * FROM campus.tbl_pruefungsanmeldung WHERE pruefungsanmeldung_id='.$this->db_add_param($pruefungsanmeldung_id).';';

        if(!$this->db_query($qry))
        {
            $this->errormsg = 'Anmeldungsdaten konnten nicht geladen werden.';
            return false;
        }
        else
        {
            if($row = $this->db_fetch_object())
            {
                $this->pruefungsanmeldung_id = $row->pruefungsanmeldung_id;
                $this->uid = $row->uid;
                $this->pruefungstermin_id = $row->pruefungstermin_id;
                $this->lehrveranstaltung_id = $row->lehrveranstaltung_id;
                $this->status_kurzbz = $row->status_kurzbz;
                $this->wuensche = $row->wuensche;
                $this->reihung = $row->reihung;
                $this->kommentar = $row->kommentar;
		$this->statusupdateamum = $row->statusupdateamum;
		$this->statusupdatevon = $row->statusupdatevon;
		$this->anrechnung_id = $row->anrechnung_id;
		$this->pruefungstyp_kurzbz = $row->pruefungstyp_kurzbz;
        $this->insertamum = $row->insertamum;
            }
            return true;
        }
    }

    /**
     * Lädt alle Prüfungsanmeldungen eines Studenten
     * @param type $uid UID eines Studenten
     * @param type $studiensemester_kurzbz Filter nach Studiensemester (zB 'WS2013')
     * @param type $status_kurzbz Filter nach Status (zB 'angemeldet')
     * @return boolean|array false, bei Fehler; Array mit Anmeldungen
     */
    public function getAnmeldungenByStudent($uid, $studiensemester_kurzbz=null, $status_kurzbz=null)
    {
        $qry = 'SELECT * FROM campus.tbl_pruefungsanmeldung pa '
                . 'JOIN campus.tbl_pruefungstermin pt ON pa.pruefungstermin_id=pt.pruefungstermin_id '
                . 'JOIN campus.tbl_pruefung p ON p.pruefung_id=pt.pruefung_id '
                . 'WHERE uid='.$this->db_add_param($uid) . ' '
                . 'AND p.storniert=false';

		if($studiensemester_kurzbz != null)
		{
			$qry .= ' AND studiensemester_kurzbz='.$this->db_add_param($studiensemester_kurzbz);
		}

		$qry .= ';';

        if(!$this->db_query($qry))
        {
            $this->errormsg = 'Anmeldungen konnten nicht geladen werden.';
            return false;
        }
        else
        {
            $anmeldungen = array();
            while($row = $this->db_fetch_object())
            {
                $anmeldung = new stdClass();
                $anmeldung->pruefungsanmeldung_id = $row->pruefungsanmeldung_id;
                $anmeldung->uid = $row->uid;
                $anmeldung->lehrveranstaltung_id = $row->lehrveranstaltung_id;
                $anmeldung->status_kurzbz = $row->status_kurzbz;
                $anmeldung->pruefungstermin_id = $row->pruefungstermin_id;
                $anmeldung->pruefung_id = $row->pruefung_id;
                $anmeldung->von = $row->von;
                $anmeldung->bis = $row->bis;
				$anmeldung->reihung = $row->reihung;
				$anmeldung->wuensche = $row->wuensche;
				$anmeldung->kommentar = $row->kommentar;
				$anmeldung->statusupdateamum = $row->statusupdateamum;
				$anmeldung->statusupdatevon = $row->statusupdatevon;
				$anmeldung->anrechnung_id = $row->anrechnung_id;
				$anmeldung->pruefungstyp_kurzbz = $row->pruefungstyp_kurzbz;
                array_push($anmeldungen, $anmeldung);
            }
            return $anmeldungen;
        }
    }

    /**
     * Lädt alle Anmeldungen eines Prüfungstermins
     * @param type $pruefungstermin_id ID des Prüfungstermins
     * @param type $lehrveranstaltung_id Filter nach Lehrveranstaltung
     * @param type $studiensemester_kurbz Filter nach Studiensemester (zB 'WS2013')
     * @param type $status_kurzbz Filter nach Status (zB 'angemeldet')
     * @return boolean|array false, bei Fehler; Array mit Anmeldungen
     */
    public function getAnmeldungenByTermin($pruefungstermin_id, $lehrveranstaltung_id=null, $studiensemester_kurbz=null, $status_kurzbz=null)
    {
        $qry = 'SELECT *, pa.insertamum as datum_anmeldung FROM campus.tbl_pruefungsanmeldung pa '
                . 'JOIN campus.tbl_pruefungstermin pt ON pa.pruefungstermin_id=pt.pruefungstermin_id '
                . 'JOIN campus.tbl_pruefung p ON p.pruefung_id=pt.pruefung_id '
                . 'WHERE pa.pruefungstermin_id='.$this->db_add_param($pruefungstermin_id);

	if($lehrveranstaltung_id !== null)
	{
	    $qry .= ' AND lehrveranstaltung_id='.$this->db_add_param($lehrveranstaltung_id);
	}

	if($status_kurzbz !== null)
	{
	    $qry .= ' AND status_kurzbz='.$this->db_add_param($status_kurzbz);
	}
	$qry .=' ORDER BY reihung';
	$qry .=';';

        if(!$this->db_query($qry))
        {
            $this->errormsg = "Anmeldungen konnten nicht geladen werden.";
            return false;
        }
        else
        {
            $anmeldungen = array();
            while($row = $this->db_fetch_object())
            {
                $anmeldung = new stdClass();
                $anmeldung->pruefungsanmeldung_id = $row->pruefungsanmeldung_id;
                $anmeldung->uid = $row->uid;
                $anmeldung->lehrveranstaltung_id = $row->lehrveranstaltung_id;
                $anmeldung->status_kurzbz = $row->status_kurzbz;
                $anmeldung->pruefungstermin_id = $row->pruefungstermin_id;
                $anmeldung->pruefung_id = $row->pruefung_id;
                $anmeldung->von = $row->von;
                $anmeldung->bis = $row->bis;
		$anmeldung->reihung = $row->reihung;
		$anmeldung->wuensche = $row->wuensche;
		$anmeldung->kommentar = $row->kommentar;
		$anmeldung->statusupdateamum = $row->statusupdateamum;
		$anmeldung->statusupdatevon = $row->statusupdatevon;
		$anmeldung->anrechnung_id = $row->anrechnung_id;
		$anmeldung->pruefungstyp_kurzbz = $row->pruefungstyp_kurzbz;
        $anmeldung->datum_anmeldung = $row->datum_anmeldung;
                array_push($anmeldungen, $anmeldung);
            }
            return $anmeldungen;
        }
    }

    /**
     * Löscht eine Prüfungsanmeldung
     * @param type $pruefungsanmeldung_id ID der Prüfungsanmeldung
     * @param type $uid UID eines Studenten
     * @return boolean true, wenn ok; false im Fehlerfall
     */
    public function delete($pruefungsanmeldung_id, $uid=null)
    {
        $qry = 'DELETE FROM campus.tbl_pruefungsanmeldung WHERE pruefungsanmeldung_id='.$this->db_add_param($pruefungsanmeldung_id);

	if(!is_null($uid))
	{
	    $qry .= ' AND uid='.$this->db_add_param($uid);
	}

	$qry .= ' ;';

	if($this->db_query($qry))
	{
	    return true;
	}
	else
	{
	    $this->errormsg = 'Anmeldung konnte nicht gelöscht werden.';
	    return false;
	}
    }

    /**
     * speichert die Reihung eines Anmeldungstermins
     * WICHTIG: Auf den Aufbau des übergebenen arrays muss geachtet werden
     * @param Array $reihung Ein Array mit Objekten (Attribute: lehrveranstaltung_id, reihung, terminId, uid)
     * @return boolean true, wenn ok; false, im Fehlerfall
     */
    public function saveReihung($reihung)
    {
	if(is_array($reihung))
	{
	    foreach($reihung as $key=>$anmeldung)
	    {
		if($key === 0)
		{
		    $qry = 'BEGIN; UPDATE campus.tbl_pruefungsanmeldung SET reihung='.$this->db_add_param($anmeldung["reihung"]).' WHERE '
			. 'uid='.$this->db_add_param($anmeldung["uid"]).' AND '
			. 'pruefungstermin_id='.$this->db_add_param($anmeldung["terminId"]).' AND '
			. 'lehrveranstaltung_id='.$this->db_add_param($anmeldung["lehrveranstaltung_id"]).';';
		} else {
		    $qry = 'UPDATE campus.tbl_pruefungsanmeldung SET reihung='.$this->db_add_param($anmeldung["reihung"]).' WHERE '
			. 'uid='.$this->db_add_param($anmeldung["uid"]).' AND '
			. 'pruefungstermin_id='.$this->db_add_param($anmeldung["terminId"]).' AND '
			. 'lehrveranstaltung_id='.$this->db_add_param($anmeldung["lehrveranstaltung_id"]).';';
		}
		if((!$this->db_query($qry)) || ($this->db_affected_rows()===0))
		{
		    $this->db_query('ROLLBACK');
		    $this->errormsg = "Reihung konnte nicht geändert werden.";
		    return false;
		}
	    }
	    $this->db_query('COMMIT;');
	    return true;
	}
	$this->errormsg = "No Array";
	return false;
    }


    /**
     * Ändert den Status einer Prüfungsanmeldung
     * @param type $pruefungsanmeldung_id ID der Prüfungsanmeldung
     * @param type $status Status auf den geändert werden soll
     * @return boolean true, wenn ok; false, im Fehlerfall
     */
    public function changeState($pruefungsanmeldung_id, $status, $user)
    {
	$qry = 'UPDATE campus.tbl_pruefungsanmeldung SET '
		. 'status_kurzbz='.$this->db_add_param($status).', '
		. 'statusupdatevon='.$this->db_add_param($user).', '
		. 'statusupdateamum=NOW() '
		. ' WHERE pruefungsanmeldung_id='.$this->db_add_param($pruefungsanmeldung_id).';';

	if(!$this->db_query($qry))
	{
	    $this->errormsg = 'Status konnte nicht geändert werden.';
	    return false;
	}
	return true;
    }
}
