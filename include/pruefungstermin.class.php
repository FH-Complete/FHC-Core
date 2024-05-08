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

class pruefungstermin extends basis_db{
    public $new;
    public $result = array();
    
    public $pruefungstermin_id;     //bigint
    public $pruefung_id;            //bigint
    public $von;                    //timestamp without timezone
    public $bis;                    //timestamp without timezone
    public $teilnehmer_max;         //smallint
    public $teilnehmer_min;         //smallint
    public $anmeldung_von;	    //date
    public $anmeldung_bis;	    //date
    public $ort_kurzbz;		    //varchar(16)
    public $sammelklausur;		//boolean
    
    /**
     * Konstruktor
     * @param pruefungsfenster_id ID des zu ladenden Pruefungfensters
     */
    public function __construct($pruefungstermin_id = null) {
        parent::__construct();

        if ($pruefungstermin_id != null)
            $this->load($pruefungstermin_id);
    }

    public function __set($name, $value) {
        switch ($name) {
            case 'pruefungstermin_id':
                if (!is_numeric($value))
                    throw new Exception('Attribute pruefungstermin_id must be numeric!"');
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
     * Lädt einen Prüfungstermin
     * @param Integer Prüfungstermin ID
     */
    public function load($pruefungstermin_id)
    {
        if(!is_numeric($pruefungstermin_id))
        {
            $this->errormsg = "Pruefungstermin ID muss eine gültige Zahl sein";
            return false;
        }
        
        $qry = 'SELECT * FROM campus.tbl_pruefungstermin WHERE pruefungstermin_id='.$this->db_add_param($pruefungstermin_id).';';
        
        if($this->db_query($qry))
        {
            if($row = $this->db_fetch_object())
            {
                $this->pruefungstermin_id = $row->pruefungstermin_id;
                $this->pruefung_id = $row->pruefung_id;
                $this->von = $row->von;
                $this->bis = $row->bis;
                $this->teilnehmer_max = $row->teilnehmer_max;
                $this->teilnehmer_min = $row->teilnehmer_min;
		$this->anmeldung_von= $row->anmeldung_von;
                $this->anmeldung_bis = $row->anmeldung_bis;
		$this->ort_kurzbz = $row->ort_kurzbz;
		$this->sammelklausur = $row->sammelklausur;
            }
            return true;
        }
        else
        {
            $this->errormsg = 'Termin konnte nicht geladen werden.';
            return false;
        }
        
    }

	/**
	 * Lädt alle Prüfungstypen aus der Datenbank
	 * @param null $abschluss gibt an, ob Abschlussprüfungen ausgegeben werden sollen. default: Abschlussprüfungen und andere Prüfungen
	 * @return array /Boolean Ein Array mit den Daten, wenn ok; ansonsten false
	 */
    public function getAllPruefungstypen($abschluss = null, $sort = false)
    {
        $qry = 'SELECT * FROM lehre.tbl_pruefungstyp';
	
	if(is_bool($abschluss))
	{
	    $qry .= ' WHERE abschluss='.$this->db_add_param($abschluss, FHC_BOOLEAN);
	}
	if($sort)
		$qry .= ' ORDER BY (sort IS NULL), sort, pruefungstyp_kurzbz';
	$qry .=';';
        
        if($this->db_query($qry))
        {
            $result = array();
            while($row = $this->db_fetch_object())
            {
                $obj = new stdClass();
                $obj->pruefungstyp_kurzbz = $row->pruefungstyp_kurzbz;
                $obj->beschreibung = $row->beschreibung;
                $obj->abschluss = $row->abschluss;
                $obj->sort = $row->sort;
                array_push($result, $obj);
            }
        }
        else
        {
            $this->errormsg = "Prüfungstypen konnten nicht geladen werden.";
            return false;
        }
        return $result;
    }
    
    /**
     * Lädt die Anzahl der Anmeldungen zu eine Prüfung
     * @return boolean|integer Anzahl der Teilnehmer; false, wenn Fehler
     */
    public function getNumberOfParticipants()
    {
        $qry = 'SELECT * FROM campus.tbl_pruefungsanmeldung WHERE pruefungstermin_id='.$this->db_add_param($this->pruefungstermin_id).';';
        
        if($this->db_query($qry))
        {
            return $this->db_num_rows();
        }
        else
        {
            $this->errormsg = 'Teilnehmeranzahl konnte nicht geladen werden.';
            return false;
        }
        return false;
    }
    
    public function save($new = false)
    {
	if($new)
	{
	    
	}
	else
	{
	    $qry = 'UPDATE campus.tbl_pruefungstermin SET '
		    . 'pruefung_id='.$this->db_add_param($this->pruefung_id).', '
		    . 'von='.$this->db_add_param($this->von).', '
		    . 'bis='.$this->db_add_param($this->bis).', '
		    . 'teilnehmer_max='.$this->db_add_param($this->teilnehmer_max).', '
		    . 'teilnehmer_min='.$this->db_add_param($this->teilnehmer_min).', '
		    . 'anmeldung_von='.$this->db_add_param($this->anmeldung_von).', '
		    . 'anmeldung_bis='.$this->db_add_param($this->anmeldung_bis).', '
		    . 'ort_kurzbz='.$this->db_add_param($this->ort_kurzbz).' '
		    . 'WHERE pruefungstermin_id='.$this->db_add_param($this->pruefungstermin_id).';';
	}
	
	if($this->db_query($qry))
	{
	    return true;
	}
	
	return false;
    }
    
    /**
     * lädt alle Prüfungstermine
     */
    public function getAll($beginn=NULL, $ende=NULL, $sammelklausur=NULL)
    {
	$qry = 'SELECT * FROM campus.tbl_pruefungstermin';
	
	if(!is_null($beginn) && !is_null($ende))
	{
	    $qry .= ' WHERE von <='.$this->db_add_param($beginn).' '
		. 'AND bis >='.$this->db_add_param($ende);
	}
	if(!is_null($sammelklausur) && !is_null($beginn))
	{
	    $qry .= ' AND sammelklausur='.$this->db_add_param($sammelklausur);
	}
	else
	{
	    $qry .= ' WHERE sammelklausur='.$this->db_add_param($sammelklausur);
	}
	
	$qry .= ';';
	
	if($this->db_query($qry))
	{
	    while($row = $this->db_fetch_object())
            {
		$obj = new stdClass();
		$obj->pruefungstermin_id = $row->pruefungstermin_id;
                $obj->pruefung_id = $row->pruefung_id;
                $obj->von = $row->von;
                $obj->bis = $row->bis;
                $obj->teilnehmer_max = $row->teilnehmer_max;
                $obj->teilnehmer_min = $row->teilnehmer_min;
		$obj->anmeldung_von= $row->anmeldung_von;
                $obj->anmeldung_bis = $row->anmeldung_bis;
		$obj->ort_kurzbz = $row->ort_kurzbz;
		$obj->sammelklausur = $row->sammelklausur;
		array_push($this->result, $obj);
	    }
	    return true;
	}
	else
	{
	    $this->errormsg = 'Termine konnten nicht geladen werden.';
	    return false;
	}
	
    }
}
