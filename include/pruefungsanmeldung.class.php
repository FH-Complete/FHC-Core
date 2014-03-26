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
    public $kommentar;                       //text
    
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
    
    public function save($new = null)
    {
        if ($new == null)
            $new = $this->new;
        
        if($new)
        {
            $qry = 'INSERT INTO campus.tbl_pruefungsanmeldung (uid, pruefungstermin_id, lehrveranstaltung_id, status_kurzbz, wuensche, reihung, kommentar) VALUES ('
                    . $this->db_add_param($this->uid).', '
                    . $this->db_add_param($this->pruefungstermin_id).', '
                    . $this->db_add_param($this->lehrveranstaltung_id).', '
                    . $this->db_add_param($this->status_kurzbz).', '
                    . $this->db_add_param($this->wuensche).', '
                    . $this->db_add_param($this->reihung).', '
                    . $this->db_add_param($this->kommentar).');';
        }
        else
        {
            //UPDATE
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
                $this->pruefungsanmeldung_id = $row->pruefunganmeldung_id;
                $this->uid = $row->uid;
                $this->pruefungstermin_id = $row->pruefungstermin_id;
                $this->lehrveranstaltung_id = $row->lehrveranstaltung_id;
                $this->status_kurzbz = $row->status_kurzbz;
                $this->wuensche = $row->wuensche;
                $this->reihung = $row->reihung;
                $this->kommentar = $row->kommentar;
            }
            return true;
        }
    }
    
    public function getAnmeldungenByStudent($uid, $studiensemester_kurbz=null, $status_kurzbz=null)
    {
        $qry = 'SELECT * FROM campus.tbl_pruefungsanmeldung pa '
                . 'JOIN campus.tbl_pruefungstermin pt ON pa.pruefungstermin_id=pt.pruefungstermin_id '
                . 'JOIN campus.tbl_pruefung p ON p.pruefung_id=pt.pruefung_id '
                . 'WHERE uid='.$this->db_add_param($uid).';';
        
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
                array_push($anmeldungen, $anmeldung);
            }
            return $anmeldungen;
        }
    }
    
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
    
}
