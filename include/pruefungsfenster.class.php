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

class pruefungsfenster extends basis_db {

    public $new;                        // boolean
    public $result = array();           // pruefungsfenster Objekt
    
    //Tabellenspalten in DB
    public $pruefungsfenster_id;        //bigint
    public $studiensemester_kurzbz;     //varchar(16)
    public $oe_kurzbz;                  //varchar(32)
    public $start;                      //date
    public $ende;                       //date

    /**
     * Konstruktor
     * @param pruefungsfenster_id ID des zu ladenden Pruefungfensters
     */
    public function __construct($pruefungsfenster_id = null) {
        parent::__construct();

        if ($pruefungsfenster_id != null)
            $this->load($pruefungsfenster_id);
    }

    public function __set($name, $value) {
        switch ($name) {
            case 'pruefungsfenster_id':
                if (!is_numeric($value))
                    throw new Exception('Attribute pruefungsfenster_id must be numeric!"');
                $this->$name = $value;
                break;
            default:
                $this->$name = $value;
        }
    }

    public function __get($name) {
        return $this->$name;
    }

    /**
     * Lädt einen Pruefungsfensterdatensatz
     * @param $pruefungsfenster_id ID des Prüfungsfensters
     * @return true, wenn ok; false im Fehlerfall
     */
    public function load($pruefungsfenster_id) {
        if(!is_numeric($pruefungsfenster_id))
        {
            $this->errormsg = "Prüfungsfenster ID ist keine gültige Zahl.";
            return false;
        }
        
        $qry = 'SELECT * FROM campus.tbl_pruefungsfenster'
                . ' WHERE pruefungsfenster_id='.$this->db_add_param($pruefungsfenster_id).';';
        
        if(!$this->db_query($qry))
        {
            $this->errormsg = "Fehler beim Laden des Prüfungsfensters.";
            return false;
        }
        
        if($row = $this->db_fetch_object())
        {
            $this->pruefungsfenster_id = $row->pruefungsfenster_id;
            $this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
            $this->oe_kurzbz = $row->oe_kurzbz;
            $this->start = $row->start;
            $this->ende = $row->ende;
        }
        else
        {
            $this->errormsg = "Keine Datensatz mit dieser ID vorhanden.";
            return false;
        }
        return true;
    }
    
    /**
     * speichert den aktuellen Datensatz
     */
    public function save() {
        if ($this->new) {
            $qry = 'INSERT INTO campus.tbl_pruefungsfenster (studiensemester_kurzbz, oe_kurzbz, start, ende) VALUES (' .
                    $this->db_add_param($this->studiensemester_kurzbz) . ', ' .
                    $this->db_add_param($this->oe_kurzbz) . ', ' .
                    $this->db_add_param($this->start) . ', ' .
                    $this->db_add_param($this->ende) . ');';
        } else {
            $qry = 'UPDATE campus.tbl_pruefungsfenster SET '.
                    'studiensemester_kurzbz='.$this->db_add_param($this->studiensemester_kurzbz).', '.
                    'oe_kurzbz='.$this->db_add_param($this->oe_kurzbz).', '.
                    'start='.$this->db_add_param($this->start).', '.
                    'ende='.$this->db_add_param($this->ende).' '.
                    'WHERE pruefungsfenster_id='.$this->db_add_param($this->pruefungsfenster_id).';';
        }
        if (!$this->db_query($qry)) {
            $this->errormsg = "Datensatz konnte nicht gespeichert werden";
            return false;
        }
        return true;
    }
    
    /**
     * lädt alle vorhandenen Prüfungsfenster
     * @param Datenbankspalte nach der sortiert werden soll
     * @return true, wenn ok; false im Fehlerfall
     */
    public function getAll($sort=null) {
        
        $qry = "SELECT * FROM campus.tbl_pruefungsfenster";
        
        if($sort != null)
        {
            $qry.= " ORDER BY ".$sort." ASC;";
        }
        else
        {
            $qry .= ";";
        }
        if($this->db_query($qry))
        {
                while($row = $this->db_fetch_object())
                {
                        $obj = new pruefungsfenster();
                        
                        $obj->pruefungsfenster_id = $row->pruefungsfenster_id;
                        $obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
                        $obj->oe_kurzbz = $row->oe_kurzbz;
                        $obj->start = $row->start;
                        $obj->ende = $row->ende;
                        
                        $this->result[] = $obj;
                }
                return true;
        }
        else 
        {
                $this->errormsg = 'Fehler beim Laden der Prüfungsfenster';
                return false;
        }
    }
    
    /**
     * lädt alle Prüfungsfenster zu den angebenen Organisationseinheiten
     * Es kann auch ein Array mit mehreren OEs übergeben werden.
     * @param type $oe_kurzbz
     */
    public function getByOe($oe_kurzbz) {
        
    }
    
    /**
     * löscht das Prüfungsfenster mit der angegebenen ID
     * @param $pruefungsfenster_id ID des Prüfungsfensters
     * @return true, wenn ok; false, im Fehlerfall
     */
    public function delete($pruefungsfenster_id)
    {
        if(!is_numeric($pruefungsfenster_id))
        {
            $this->errormsg = "Fehler: Die ID ist keine gültige Zahl.";
            return false;
        }
        
        $qry = 'DELETE FROM campus.tbl_pruefungsfenster'
                . ' WHERE pruefungsfenster_id='.$this->db_add_param($pruefungsfenster_id)
                . ';';
        
        if(!$this->db_query($qry))
        {
            $this->errormsg = 'Fehler: Datensatz konnte nicht gelöscht werden.';
            return false;
        }
        
        return true;
    }
    
    /**
     * Lädt alle Prüfungsfenster zu einem angegebenen Studiensemester
     * @param String $studiensemester_kurzbz Kurzbezeichnung des Studiensemesters
     * @param String $sort Spaltenname, nachdem sortiert werden soll
     * @return boolean true, wenn ok; false im Fehlerfall
     */
    public function getByStudiensemester($studiensemester_kurzbz, $sort=null)
    {
        $qry = "SELECT * FROM campus.tbl_pruefungsfenster WHERE studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);
        
        if($sort != null)
        {
            $qry.= " ORDER BY ".$sort." ASC;";
        }
        else
        {
            $qry .= ";";
        }
        if($this->db_query($qry))
        {
                while($row = $this->db_fetch_object())
                {
                        $obj = new pruefungsfenster();
                        $obj->pruefungsfenster_id = $row->pruefungsfenster_id;
                        $obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
                        $obj->oe_kurzbz = $row->oe_kurzbz;
                        $obj->start = $row->start;
                        $obj->ende = $row->ende;
                        
                        $this->result[] = $obj;
                }
                return true;
        }
        else 
        {
                $this->errormsg = 'Fehler beim Laden der Prüfungsfenster';
                return false;
        }
    }
    
    /**
    * Baut die Datenstruktur für senden als JSON Objekt auf
    */
    public function cleanResult()
    {
        $data = array();
        if(count($this->result)>0)
        {
            foreach($this->result as $prfFenster)
            {
                $obj = new stdClass();
                $obj->pruefungsfenster_id = $prfFenster->pruefungsfenster_id;
                $obj->studiensemester_kurzbz = $prfFenster->studiensemester_kurzbz;
                $obj->oe_kurzbz = $prfFenster->oe_kurzbz;
                $obj->start = $prfFenster->start;
                $obj->ende = $prfFenster->ende;
                $data[]=$obj;
            }
        } 
        else 
        {
            $obj = new stdClass();
            $obj->pruefungsfenster_id = $this->pruefungsfenster_id;
            $obj->studiensemester_kurzbz = $this->studiensemester_kurzbz;
            $obj->oe_kurzbz = $this->oe_kurzbz;
            $obj->start = $this->start;
            $obj->ende = $this->ende;
            $data[]=$obj;
        }
        return $data;
    }
    
    /**
     * prüft ob für ein Prüfungsfenster bereits Prüfungen angelegt sind
     * @param integer $pruefungsfenster_id ID des Prüfungsfensters
     * @return boolean true, wenn Prüfungsfenster vorhanden sind; false, wenn nicht und im Fehlerfall
     */
    public function hasPruefungen($pruefungsfenster_id)
    {
        if(!is_numeric($pruefungsfenster_id))
        {
            $this->errormsg = "Die Prüfungsfenster ID ist keine gültige Zahl.";
            return false;
        }
        $qry = 'SELECT * FROM campus.tbl_pruefung WHERE pruefungsfenster_id='.$this->db_add_param($pruefungsfenster_id).';';
        
        if($this->db_query($qry))
        {
            if($this->db_num_rows()>0)
            {
                return true;
            }
            return false;
        }
        else
        {
            $this->errormsg = 'Daten konnten nicht gelsesen werden.';
            return false;
        }
        
    }
}

?>
