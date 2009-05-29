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
require_once('basis_db.class.php');

class variable extends basis_db 
{
	public $errormsg; // string
	public $new;      // boolean
	public $variables = array(); // variable Objekt
	public $variable;

	//Tabellenspalten
	public $uid;	// varchar(32)
	public $name;	// varchar(64)
	public $wert;	// varchar(64)

	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional eine Variable
	// * @param $conn        	Datenbank-Connection
	// *        $uid
	// *		$name
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	public function __construct($uid=null, $name=null)
	{
		parent::__construct();
		
		if($uid!=null && $name!=null)
			$this->load($uid, $name);
	}

	// *********************************************************
	// * Laedt eine Variable
	// * @param $uid
	// * @param $name
	// *********************************************************
	public function load($uid, $name)
	{
		$qry = "SELECT wert FROM public.tbl_variable WHERE uid='".addslashes($uid)."' AND name='".addslashes($name)."'";
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->uid = $uid;
				$this->name = $name;
				$this->wert = $row->wert;
				
				return true;
			}
			else 
				return false;
		}
		else
			return false;
	}

	// *******************************************
	// * Prueft die Variablen vor dem Speichern
	// * auf Gueltigkeit.
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************
	private function validate()
	{
		if(strlen($this->uid)>32)
		{
			$this->errormsg = 'UID darf nicht laenger als 32 Zeichen sein';
			return true;
		}
		if(strlen($this->name)>64)
		{
			$this->errormsg = 'Name darf nicht laenger als 64 Zeichen sein';
			return false;
		}
		if(strlen($this->wert)>64)
		{
			$this->errormsg = 'Wert darf nicht laenger als 64 Zeichen sein';
			return false;
		}

		return true;
	}

	// ************************************************************
	// * Speichert Variable in die Datenbank
	// * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	// * angelegt, ansonsten der Datensatz upgedated
	// * @return true wenn erfolgreich, false im Fehlerfall
	// ************************************************************
	public function save($new=null)
	{
		if(is_null($new))
			$new = $this->new;

		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($new)
		{
			$qry = 'INSERT INTO public.tbl_variable (uid, name, wert)
			        VALUES('.$this->addslashes($this->uid).','.
					$this->addslashes($this->name).','.
					$this->addslashes($this->wert).');';
		}
		else
		{
			$qry = 'UPDATE public.tbl_variable SET'.
			       ' wert='.$this->addslashes($this->wert).
			       " WHERE uid='".addslashes($this->uid)."' AND name='".addslashes($this->name)."';";
		}

		if($this->db_query($qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Variable:'.$qry;
			return false;
		}
	}
	
	// ****
	// * Loescht einen Variableneintrag
	// ****
	public function delete($name, $uid)
	{
		if($name=='' || $uid == '')
		{
			$this->errormsg = 'Name und UID muessen angegeben werden';
			return false;
		}
		
		$qry = "DELETE FROM public.tbl_variable WHERE name='".addslashes($name)."' AND uid='".addslashes($uid)."'";
		
		if($this->db_query($qry))
			return true;
		else 
		{
			$this->errormsg = 'Fehler beim Loeschen';
			return false;
		}
	}
	
	// ******
	// * Liefert alle Variablen eines Benutzers
	// ******
	public function getVars($uid)
	{
		$qry = "SELECT * FROM public.tbl_variable WHERE uid='".addslashes($uid)."' ORDER BY name";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$v = new variable();
				
				$v->uid = $row->uid;
				$v->name = $row->name;
				$v->wert = $row->wert;
				
				$this->variables[] = $v;
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
	 * Laedt die Variablen in ein assoziatives Array
	 * 
	 * Zugriff von aussen mit $obj->variable->semester_aktuell
	 *
	 * @param $user
	 * @return true wenn ok, sonst false
	 */
	public function loadVariables($user)
	{			
		if(!$this->db_query("SELECT * FROM public.tbl_variable WHERE uid='".addslashes($user)."'"))
		{
			$this->errormsg.=$this->db_last_error();
			return false;
		}
		else
			$num_rows = $this->db_num_rows();
		
		while($row=$this->db_fetch_object())
		{
			$this->variable->{$row->name}=$row->wert;
		}
		
		if (!isset($this->variable->semester_aktuell))
		{
			if(!$this->db_query('SELECT studiensemester_kurzbz FROM public.tbl_studiensemester WHERE ende>now() ORDER BY start LIMIT 1'))
			{
				$this->errormsg.=$this->db_last_error();
				return false;
			}
			else
			{
				if($row = $this->db_fetch_object())
				{
					$this->variable->semester_aktuell=$row->studiensemester_kurzbz;
				}
			}
		}
		
		if (!isset($this->variable->db_stpl_table))
			$this->variable->db_stpl_table='stundenplan';

		if (!isset($this->variable->emailadressentrennzeichen))
			$this->variable->emailadressentrennzeichen=',';

		if (!isset($this->variable->db_stpl_table))
			$this->variable->db_stpl_table='stundenplan';

		if (!isset($this->variable->kontofilterstg))
			$this->variable->kontofilterstg='false';

		if (!isset($this->variable->ignore_kollision))
			$this->variable->ignore_kollision='false';

		if (!isset($this->variable->ignore_zeitsperre))
			$this->variable->ignore_zeitsperre='false';

		if (!isset($this->variable->ignore_reservierung))
			$this->variable->ignore_reservierung='false';

		return true;
	}
		
}
?>