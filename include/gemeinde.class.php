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
 * Authors: Manfred Kindl <manfred.kindl@technikum-wien.at>.
 */

/**
 * Klasse Gemeinde (FAS-Online)
 * @create 22-12-2016
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class gemeinde extends basis_db 
{
	public $new;					// boolean
	public $gemeinde = array(); 	// gemeinde Objekt

	//Tabellenspalten
	public $gemeinde_id;			// integer
	public $plz;					// smallint
	public $name;					// varchar(64)
	public $ortschaftskennziffer;	// integer
	public $ortschaftsname;			// varchar(64)
	public $bulacode;				// integer
	public $bulabez;				// varchar(5)
	public $kennziffer;				// integer
	
	/**
	 * Konstruktor
	 * @param $code      Zu ladende Gemeinde
	 */
	public function __construct($gemeinde_id=null)
	{
		parent::__construct();
		
		if($gemeinde_id != null)
			$this->load($gemeinde_id);
	}


	/**
	 * Laedt die Gemeinde mit der ID $gemeinde_id
	 * @param  $gemeinde_id ID der zu ladenden Gemeinde
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($gemeinde_id)
	{
		//Lesen der Daten aus der Datenbank
		$qry = "SELECT * FROM bis.tbl_gemeinde WHERE gemeinde_id=".$this->db_add_param($gemeinde_id).';';
		
		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->gemeinde_id = $row->gemeinde_id;
			$this->plz = $row->plz;
			$this->name = $row->name;
			$this->ortschaftskennziffer = $row->ortschaftskennziffer;
			$this->ortschaftsname = $row->ortschaftsname;
			$this->bulacode = $row->bulacode;
			$this->bulabez = $row->bulabez;
			$this->kennziffer = $row->kennziffer;
		}
		else
		{
			$this->errormsg = 'Kein Datensatz vorhanden!';
			return false;
		}
		return true;
	}

	/**
	 * Laedt alle Gemeinden
	 * @param ohnesperre wenn dieser Parameter auf true gesetzt ist werden
	 *        nur die nationen geliefert dessen Buerger bei uns studieren duerfen
	 */
	public function getAll()
	{
		//Lesen der Daten aus der Datenbank
		$qry = "SELECT * FROM bis.tbl_gemeinde;";
			
		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		while($row = $this->db_fetch_object())
		{
			$gemeinde = new gemeinde();

			$gemeinde->gemeinde_id = $row->gemeinde_id;
			$gemeinde->plz = $row->plz;
			$gemeinde->name = $row->name;
			$gemeinde->ortschaftskennziffer = $row->ortschaftskennziffer;
			$gemeinde->ortschaftsname = $row->ortschaftsname;
			$gemeinde->bulacode = $row->bulacode;
			$gemeinde->bulabez = $row->bulabez;
			$gemeinde->kennziffer = $row->kennziffer;

			$gemeinde->gemeinde[] = $gemeinde;
		}
		return true;
	}
	
	/**
	 * Speichert die Gemeinde in die Datenbank
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save()
	{
		$qry='INSERT INTO bis.tbl_gemeinde (plz,name,ortschaftskennziffer,ortschaftsname,bulacode,bulabez,kennziffer) VALUES('.
			$this->db_add_param($this->plz).', '.
			$this->db_add_param($this->name).', '.
			$this->db_add_param($this->ortschaftskennziffer).', '.
			$this->db_add_param($this->ortschaftsname).', '.
			$this->db_add_param($this->bulacode).', '.
			$this->db_add_param($this->bulabez).', '.
			$this->db_add_param($this->kennziffer).');';

		if($this->db_query($qry))
		{
			//naechste ID aus der Sequence holen
			$qry="SELECT currval('bis.tbl_gemeinde_gemeinde_id_seq') as id;";
			if($this->db_query($qry))
			{
				if($row = $this->db_fetch_object())
				{
					$this->gemeinde_id = $row->id;
					$this->db_query('COMMIT');
				}
				else
				{
					$this->db_query('ROLLBACK');
					$this->errormsg = "Fehler beim Auslesen der Sequence";
					return false;
				}
			}
			else
			{
				$this->db_query('ROLLBACK');
				$this->errormsg = 'Fehler beim Auslesen der Sequence';
				return false;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Gemeinde-Datensatzes';
			return false;
		}
	}
	
	/**
	 * Laedt alle Gemeinden
	 * @param ohnesperre wenn dieser Parameter auf true gesetzt ist werden
	 *        nur die nationen geliefert dessen Buerger bei uns studieren duerfen
	 */
	public function getGemeinde($ortschaftsname = '', $gemeindename = '', $plz = '')
	{
		if ($ortschaftsname == '' && $gemeindename == '' && $plz == '')
		{
			$this->errormsg = 'Sie muessen mindestens einen Parameter uebergeben';
			return false;
		}

		$qry = "SELECT * FROM bis.tbl_gemeinde WHERE 1=1";
		
		if($ortschaftsname != '')
			$qry.=" AND ortschaftsname=".$this->db_add_param($ortschaftsname);
		
		if($gemeindename != '')
			$qry.=" AND name=".$this->db_add_param($gemeindename);

		if($plz != '')
			$qry.=" AND plz=".$this->db_add_param($plz);
		
		$qry.=' ORDER BY gemeinde_id DESC;';

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}
	
		while($row = $this->db_fetch_object())
		{
			$obj = new gemeinde();

			$obj->gemeinde_id = $row->gemeinde_id;
			$obj->plz = $row->plz;
			$obj->name = $row->name;
			$obj->ortschaftskennziffer = $row->ortschaftskennziffer;
			$obj->ortschaftsname = $row->ortschaftsname;
			$obj->bulacode = $row->bulacode;
			$obj->bulabez = $row->bulabez;
			$obj->kennziffer = $row->kennziffer;
	
			$this->result[] = $obj;
		}
		return true;
	}
}
?>
