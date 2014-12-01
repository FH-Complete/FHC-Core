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
/**
 * Klasse bankverbindung
 * @create 20-12-2006
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class resturlaub extends basis_db 
{
	public $new;
	public $result = array();
	
	//Tabellenspalten
	public $mitarbeiter_uid;
	public $resturlaubstage;
	public $mehrarbeitsstunden;
	public $urlaubstageprojahr;
	public $updateamum;
	public $updatevon;
	public $insertamum;
	public $insertvon;
	
	public $vorname;
	public $vornamen;
	public $nachname;

	/**
	 * Konstruktor
	 * @param $uid
	 */
	public function __construct($uid=null)
	{
		parent::__construct();

		if($uid!=null)
			$this->load($uid);
	}

	/**
	 * Laedt die Resturlaubstage eines Mitarbeiters
	 * @param  $mitarbeiter_uid ID der zu ladenden  Resturlaubstage
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($mitarbeiter_uid)
	{
		$qry = "SELECT * FROM campus.tbl_resturlaub WHERE mitarbeiter_uid=".$this->db_add_param($mitarbeiter_uid);
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->mitarbeiter_uid = $row->mitarbeiter_uid;
				$this->resturlaubstage = $row->resturlaubstage;
				$this->mehrarbeitsstunden = $row->mehrarbeitsstunden;
				$this->urlaubstageprojahr = $row->urlaubstageprojahr;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				return true;
			}
			else 
			{
				$this->errormsg = 'Datensatz wurde nicht gefunden';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		if($this->resturlaubstage!='' && !is_numeric($this->resturlaubstage))
		{
			$this->errormsg ='Resturlaubstage muss eine gueltige Zahl sein';
			return false;
		}
		if($this->mehrarbeitsstunden!='' && !is_numeric($this->mehrarbeitsstunden))
		{
			$this->errormsg ='Mehrarbeitsstunden muss eine gueltige Zahl sein';
			return false;
		}
		if($this->urlaubstageprojahr<0)
		{
			$this->errormsg = 'Urlaubsanspruch darf nicht negativ sein';
			return false;
		}
		if($this->urlaubstageprojahr=='')
			$this->urlaubstageprojahr='0';
		return true;
	}
	
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $bankverbindung_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save()
	{
		//Variablen pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			//Neuen Datensatz einfuegen
			$qry = 'INSERT INTO campus.tbl_resturlaub  (mitarbeiter_uid, resturlaubstage, mehrarbeitsstunden, urlaubstageprojahr, insertamum, insertvon, updateamum, updatevon) VALUES('.
			       $this->db_add_param($this->mitarbeiter_uid).', '.
			       $this->db_add_param($this->resturlaubstage).', '.
			       $this->db_add_param($this->mehrarbeitsstunden).', '.
			       $this->db_add_param($this->urlaubstageprojahr).', '.
			       $this->db_add_param($this->insertamum).', '.
			       $this->db_add_param($this->insertvon).', '.
			       $this->db_add_param($this->updateamum).', '.
			       $this->db_add_param($this->updatevon).');';
		}
		else
		{
			//Updaten des bestehenden Datensatzes
			$qry='UPDATE campus.tbl_resturlaub SET '.
			'resturlaubstage='.$this->db_add_param($this->resturlaubstage).', '.
			'mehrarbeitsstunden='.$this->db_add_param($this->mehrarbeitsstunden).', '.
			'urlaubstageprojahr='.$this->db_add_param($this->urlaubstageprojahr).', '.
 			'updateamum='.$this->db_add_param($this->updateamum).', '.
 			'updatevon='.$this->db_add_param($this->updatevon).
 			' WHERE mitarbeiter_uid='.$this->db_add_param($this->mitarbeiter_uid).';';
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}
	
	/**
	 * Liefert die Resturlaubstage der
	 * Fixangestellten
	 */
	public function getResturlaubFixangestellte()
	{
		$qry = "SELECT * FROM campus.vw_mitarbeiter LEFT JOIN campus.tbl_resturlaub ON(uid=mitarbeiter_uid) 
				WHERE fixangestellt=true AND aktiv=true ORDER BY nachname, vorname";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new resturlaub();
				
				$obj->mitarbeiter_uid = $row->uid;
				$obj->resturlaubstage = $row->resturlaubstage;
				$obj->mehrarbeitsstunden = $row->mehrarbeitsstunden;
				$obj->urlaubstageprojahr = $row->urlaubstageprojahr;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->vorname = $row->vorname;
				$obj->vornamen = $row->vornamen;
				$obj->nachname = $row->nachname;
				
				$this->result[] = $obj;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
}
?>
