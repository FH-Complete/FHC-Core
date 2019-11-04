<?php
/* Copyright (C) 2012 Technikum-Wien
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
 * Authors:	Karl Burkhart <burkhart@technikum-wien.at>.
 */

require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/benutzerberechtigung.class.php');

class webservicerecht extends basis_db
{
	public $webservicerecht_id;	// Serial
	public $berechtigung_kurzbz;// FK varchar(32)
	public $methode;			// varchar(256)
	public $attribut;			// varchar(256)
	public $insertamum;			// timestamp
	public $insertvon;			// varchar(32)
	public $updateamum;			// timestamp
    public $updatevon;			// varchar(32)
	public $klasse;				// varchar(256)

	public $new;					// boolean
	public $result = array(); 		// webservicerecht object array

	/**
	 * Konstruktor - Laedt optional einen DS
	 * @param $webservicerecht_id
	 */
	public function __construct($webservicerecht_id=null)
	{
		parent::__construct();

		if(!is_null($webservicerecht_id))
			$this->load($webservicerecht_id);
	}

    /**
     * Überprüft ob ein User die Berechtigung für eine Methode zum lesen besitzt
     * true wenn user lesen darf, false wenn nicht
     *
     * @param $user
     * @param $methode
	 * @param $klasse
     */
    public function isUserAuthorized($user, $methode, $klasse=null)
    {
        $berechtigung = new benutzerberechtigung();
        $berechtigung->getBerechtigungen($user);
        $berechtigungArray = array();

        foreach ($berechtigung->berechtigungen as $recht)
        {
            // ist berechtigung noch gültig
            if(($recht->start < date('Y-m-d') || $recht->start=='') && ($recht->ende > date('Y-m-d') || $recht->ende==''))
                $berechtigungArray[] = $recht->berechtigung_kurzbz;
        }

        $qry = "SELECT 1 from system.tbl_webservicerecht where methode = ".$this->db_add_param($methode)."
            AND berechtigung_kurzbz IN (".$this->implode4SQL($berechtigungArray).')';

		if(!is_null($klasse))
			$qry.=" AND klasse=".$this->db_add_param($klasse);

        if($result = $this->db_query($qry))
		{
            if($this->db_num_rows($result) == 0 )
            {
                return false;
            }
        }
        else
            return false;

        return true;

    }

    /**
     * Löscht alle Attribute für die ein User keine Berechtiung hat
     *
     * @param $user
     * @param $methode
     * @param $objec
     *
     */
    public function clearResponse($user, $methode, $object)
    {
        $berechtigung = new benutzerberechtigung();
        $berechtigung->getBerechtigungen($user);
        $berechtigungArray = array();
        $attributArray = array();

        foreach ($berechtigung->berechtigungen as $recht)
            $berechtigungArray[] = $recht->berechtigung_kurzbz;

        $qry = "SELECT attribut from system.tbl_webservicerecht where methode = ".$this->db_add_param($methode)."
            AND berechtigung_kurzbz IN (".$this->implode4SQL($berechtigungArray).');';

        if($result = $this->db_query($qry))
		{
            while($row = $this->db_fetch_object($result))
            {
                $attributArray[] = $row->attribut;
            }
        }

        $helpObject = new stdClass();

        for($i = 0; $i<sizeof($attributArray); $i++)
        {
			if(isset($object->{$attributArray[$i]}))
				$helpObject->{$attributArray[$i]} = $object->{$attributArray[$i]};
        }

        return $helpObject;
    }
}
