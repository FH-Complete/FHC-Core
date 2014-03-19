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
    
}
