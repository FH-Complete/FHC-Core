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
require_once(dirname(__FILE__).'/basis_db.class.php');

class bisverwendung extends basis_db 
{
	public $new;
	public $result = array();
	
	//Tabellenspalten
	public $bisverwendung_id;
	public $ba1code;
	public $ba2code;
	public $beschausmasscode;
	public $verwendung_code;
	public $mitarbeiter_uid;
	public $hauptberufcode;
	public $hauptberuflich;
	public $habilitation;
	public $beginn;
	public $ende;
	public $vertragsstunden;
	public $updateamum;
	public $updatevon;
	public $insertamum;
	public $insertvon;
	public $ext_id;	
	
	public $ba1bez;
	public $ba2bez;
	public $beschausmass;
	public $verwendung;
	public $hauptberuf;
	
	/**
	 * Konstruktor
	 * @param bisverwendung_id ID des zu ladenden Datensatzes
	 */
	public function __construct($bisverwendung_id=null)
	{
		parent::__construct();
		
		if(!is_null($bisverwendung_id))
			$this->load($bisverwendung_id);
	}
	
	/**
	 * Laedt einen Datensatz
	 * @param bisverwendung_id ID des zu ladenden Datensatzes
	 */
	public function load($bisverwendung_id)
	{
		//bisverwendung_id auf gueltigkeit pruefen
		if(!is_numeric($bisverwendung_id) || $bisverwendung_id == '')
		{
			$this->errormsg = 'bisverwendung_id muss eine gueltige Zahl sein';
			return false;
		}
		
		//laden des Datensatzes
		$qry = "SELECT 
					* 
				FROM 
					bis.tbl_beschaeftigungsart1, bis.tbl_beschaeftigungsart2, 
					bis.tbl_beschaeftigungsausmass, bis.tbl_verwendung, bis.tbl_bisverwendung 
					LEFT JOIN bis.tbl_hauptberuf USING(hauptberufcode) 
				WHERE 
					tbl_bisverwendung.ba1code=tbl_beschaeftigungsart1.ba1code AND
					tbl_bisverwendung.ba2code=tbl_beschaeftigungsart2.ba2code AND
					tbl_bisverwendung.beschausmasscode=tbl_beschaeftigungsausmass.beschausmasscode AND
					tbl_bisverwendung.verwendung_code=tbl_verwendung.verwendung_code AND
					bisverwendung_id='$bisverwendung_id';";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->bisverwendung_id = $row->bisverwendung_id;
				$this->ba1code = $row->ba1code;
				$this->ba2code = $row->ba2code;
				$this->beschausmasscode = $row->beschausmasscode;
				$this->verwendung_code = $row->verwendung_code;
				$this->mitarbeiter_uid = $row->mitarbeiter_uid;
				$this->hauptberufcode = $row->hauptberufcode;
				if($row->hauptberuflich=='t')
					$this->hauptberuflich = true;
				elseif($row->hauptberuflich=='f')
					$this->hauptberuflich = false;
				else 
					$this->hauptberuflich = '';

				$this->habilitation = ($row->habilitation=='t'?true:false);
				$this->beginn = $row->beginn;
				$this->ende = $row->ende;
				$this->updatevon = $row->updatevon;
				$this->updateamum = $row->updateamum;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				$this->ba1bez = $row->ba1bez;
				$this->ba2bez = $row->ba2bez;
				$this->beschausmass = $row->beschausmassbez;
				$this->verwendung = $row->verwendungbez;
				$this->hauptberuf = $row->bezeichnung;
				$this->vertragsstunden = $row->vertragsstunden;
				return true;		
			}
			else 
			{
				$this->errormsg = 'Fehler bei der Datenbankabfrage';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}
	}
			
	/**
	 * Loescht einen Datensatz
	 * @param bisverwendung_id ID des zu loeschenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($bisverwendung_id)
	{
		//akte_id auf gueltigkeit pruefen
		if(!is_numeric($bisverwendung_id) || $bisverwendung_id == '')
		{
			$this->errormsg = 'bisverwendung_id muss eine gueltige Zahl sein';
			return false;
		}
		$qry = "SELECT count(*) as anzahl FROM bis.tbl_bisfunktion WHERE bisverwendung_id='$bisverwendung_id'";
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				if($row->anzahl>0)
				{
					$this->errormsg = 'Bitte zuerst alle zugehoerigen Funktionen loeschen';
					return false;
				}
			}
		}
		
		$qry = "DELETE FROM bis.tbl_bisverwendung WHERE bisverwendung_id = '$bisverwendung_id';";
		
		if($this->db_query($qry))
		{
			//Log schreiben
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Loeschen';
			return false;
		}
	}
	
	/**
	 * Prueft die Daten vor dem Speichern
	 *
	 * @return true wenn ok, sonst false
	 */
	protected function validate()
	{
		if(!is_numeric($this->vertragsstunden) && $this->vertragsstunden!='')
		{
			$this->errormsg = 'Vertragsstunden sind ungueltig';
			return false;
		}
		return true;
	}
		
	/**
	 * Speichert den aktuellen Datensatz
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $akte_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($new=null)
	{
		if(!$this->validate())
			return false;
		if($new==null)
			$new = $this->new;
			
		if(is_bool($this->hauptberuflich))
			$hauptberuflich = ($this->hauptberuflich?'true':'false');
		else 
			$hauptberuflich = 'null';
		if($new)
		{
			//Neuen Datensatz anlegen	
			$qry = "BEGIN;INSERT INTO bis.tbl_bisverwendung (ba1code, ba2code, beschausmasscode, 
					verwendung_code, mitarbeiter_uid, hauptberufcode, hauptberuflich, habilitation, beginn, ende, vertragsstunden, 
					updateamum, updatevon, insertamum, insertvon, ext_id) VALUES (".
			       $this->addslashes($this->ba1code).', '.
			       $this->addslashes($this->ba2code).', '.
			       $this->addslashes($this->beschausmasscode).', '.
			       $this->addslashes($this->verwendung_code).', '.
			       $this->addslashes($this->mitarbeiter_uid).', '.
			       $this->addslashes($this->hauptberufcode).', '.
			       $hauptberuflich.', '.
			       ($this->habilitation?'true':'false').', '.
			       $this->addslashes($this->beginn).', '.
			       $this->addslashes($this->ende).', '.
			       $this->addslashes($this->vertragsstunden).', '.
			       $this->addslashes($this->updateamum).', '.
			       $this->addslashes($this->updatevon).', '.
			       $this->addslashes($this->insertamum).', '.
			       $this->addslashes($this->insertvon).', '.
			       $this->addslashes($this->ext_id).');';
			       
		}
		else 
		{
			//Bestehenden Datensatz aktualisieren
			$qry= "UPDATE bis.tbl_bisverwendung SET".
				  " ba1code=".$this->addslashes($this->ba1code).",".
				  " ba2code=".$this->addslashes($this->ba2code).",".
				  " beschausmasscode=".$this->addslashes($this->beschausmasscode).",".
				  " verwendung_code=".$this->addslashes($this->verwendung_code).",".
				  " mitarbeiter_uid=".$this->addslashes($this->mitarbeiter_uid).",".
				  " hauptberufcode=".$this->addslashes($this->hauptberufcode).",".
				  " hauptberuflich=".$hauptberuflich.",".
				  " habilitation=".($this->habilitation?'true':'false').",".
				  " beginn=".$this->addslashes($this->beginn).",".
				  " ende=".$this->addslashes($this->ende).",".
				  " vertragsstunden=".$this->addslashes($this->vertragsstunden).",".
				  " updateamum=".$this->addslashes($this->updateamum).",".
				  " updatevon=".$this->addslashes($this->updatevon).",".
				  " insertamum=".$this->addslashes($this->insertamum).",".
				  " insertvon=".$this->addslashes($this->insertvon).",".
				  " ext_id=".$this->addslashes($this->ext_id).
				  " WHERE bisverwendung_id='".addslashes($this->bisverwendung_id)."'";
		}
		
		if($this->db_query($qry))
		{
			if($new)
			{
				$qry = "SELECT currval('bis.tbl_bisverwendung_bisverwendung_id_seq') as id";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->bisverwendung_id = $row->id;
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
				return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
	}
	
	/**
	 * Laedt alle Verwendungen eines Mitarbeiters
	 * @param $uid UID des Mitarbeiters
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getVerwendung($uid)
	{
		//laden des Datensatzes
		$qry = "SELECT 
					* 
				FROM 
					bis.tbl_beschaeftigungsart1, bis.tbl_beschaeftigungsart2, 
					bis.tbl_beschaeftigungsausmass, bis.tbl_verwendung, bis.tbl_bisverwendung 
					LEFT JOIN bis.tbl_hauptberuf USING(hauptberufcode) 
				WHERE 
					tbl_bisverwendung.ba1code=tbl_beschaeftigungsart1.ba1code AND
					tbl_bisverwendung.ba2code=tbl_beschaeftigungsart2.ba2code AND
					tbl_bisverwendung.beschausmasscode=tbl_beschaeftigungsausmass.beschausmasscode AND
					tbl_bisverwendung.verwendung_code=tbl_verwendung.verwendung_code AND
					mitarbeiter_uid='".addslashes($uid)."' ORDER BY beginn;";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new bisverwendung();
				
				$obj->bisverwendung_id = $row->bisverwendung_id;
				$obj->ba1code = $row->ba1code;
				$obj->ba2code = $row->ba2code;
				$obj->beschausmasscode = $row->beschausmasscode;
				$obj->verwendung_code = $row->verwendung_code;
				$obj->mitarbeiter_uid = $row->mitarbeiter_uid;
				$obj->hauptberufcode = $row->hauptberufcode;
				if($row->hauptberuflich=='t')
					$obj->hauptberuflich = true;
				elseif($row->hauptberuflich=='f')
					$obj->hauptberuflich = false;
				else 
					$obj->hauptberuflich = '';
				$obj->habilitation = ($row->habilitation=='t'?true:false);
				$obj->beginn = $row->beginn;
				$obj->ende = $row->ende;
				$obj->updatevon = $row->updatevon;
				$obj->updateamum = $row->updateamum;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->ext_id = $row->ext_id;
				$obj->ba1bez = $row->ba1kurzbz;
				$obj->ba2bez = $row->ba2bez;
				$obj->beschausmass = $row->beschausmassbez;
				$obj->verwendung = $row->verwendungbez;
				$obj->hauptberuf = $row->bezeichnung;
				$obj->vertragsstunden = $row->vertragsstunden;
				
				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}			
	}
}
?>