<?php
/* Copyright (C) 2007 Technikum-Wien
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
 * Klasse projektbetreuer
 * @create 08-02-2007
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class projektbetreuer extends basis_db
{
	public $new;       		// boolean
	public $result = array(); 	// adresse Objekt
	
	//Tabellenspalten
	public $person_id;			// integer
	public $projektarbeit_id;	// integer
	public $note;				// integer
	public $betreuerart_kurzbz;// varchar
	public $faktor;				// numeric(3,2)
	public $name;				// string
	public $punkte;				// numeric(6,2)
	public $stunden;			// numeric(8,4)
	public $stundensatz;		// numeric(6,2)
	public $ext_id;				// integer
	public $insertamum;			// timestamp
	public $insertvon;			// bigint
	public $updateamum;			// timestamp
	public $updatevon;			// bigint
	public $vertrag_id;			// bigint

	public $person_id_old;
	
	/**
	 * Konstruktor
	 * @param $person_id, $projektarbeit ID des Projektbetreuers, der geladen werden soll (Default=null)
	 */
	public function __construct($person_id=null, $projektarbeit_id=null)
	{
		parent::__construct();

		if($projektarbeit_id != null && $person_id!=null) 	
			$this->load($person_id, $projektarbeit_id);
	}
	
	/**
	 * Laedt die Funktion mit der ID $person_id, $projektarbeit_id
	 * @param  $person_id ID der zu ladenden Funktion
	 * @param  $projektarbeit_id ID der zu ladenden Funktion
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($person_id, $projektarbeit_id, $betreuerart_kurzbz)
	{
		if(!is_numeric($person_id))
		{
			$this->errormsg = 'Person_id muss eine gueltige Zahl sein';
			return false;
		}
		
		if(!is_numeric($projektarbeit_id))
		{
			$this->errormsg = 'Projektarbeit_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT
					* 
				FROM 
					lehre.tbl_projektbetreuer 
				WHERE 
					person_id=".$this->db_add_param($person_id, FHC_INTEGER)."
					AND projektarbeit_id=".$this->db_add_param($projektarbeit_id, FHC_INTEGER)."
					AND betreuerart_kurzbz=".$this->db_add_param($betreuerart_kurzbz);
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->person_id = $row->person_id;
				$this->projektarbeit_id = $row->projektarbeit_id;
				$this->note = $row->note;
				$this->betreuerart_kurzbz = $row->betreuerart_kurzbz;
				$this->faktor = $row->faktor;
				$this->name = $row->name;
				$this->punkte = $row->punkte;
				$this->stunden = $row->stunden;
				$this->stundensatz = $row->stundensatz;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				$this->vertrag_id = $row->vertrag_id;
				$this->new=false;
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
		if($this->betreuerart_kurzbz=='')
		{
			$this->errormsg = 'Betreuerart muss eingegeben werden';
			return false;
		}
		if(mb_strlen($this->betreuerart_kurzbz)>16)
		{
			$this->errormsg = 'betreuerart darf nicht länger als 16 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->name)>32)
		{
			$this->errormsg = 'Name darf nicht länger als 32 Zeichen sein';
			return false;
		}
		
		if($this->note!='' && !is_numeric($this->note))
		{
			$this->errormsg = 'Note muss ein numerischer Wert sein';
			return false;
		}
		if($this->punkte!='' && !is_numeric($this->punkte))
		{
			$this->errormsg = 'Punkte muss ein numerischer Wert sein';
		}
		if($this->faktor!='' && !is_numeric($this->faktor))
		{
			$this->errormsg = 'Faktor muss ein numerischer Wert sein';
			return false;
		}
		if($this->stundensatz!='' && !is_numeric($this->stundensatz))
		{
			$this->errormsg = 'Stundensatz muss ein numerischer Wert sein';
			return false;
		}
		
		//Pruefen ob projektarbeit_id eine gueltige Zahl ist
		if(!is_numeric($this->projektarbeit_id))
		{
			$this->errormsg = 'projektarbeit_id muss eine gueltige Zahl sein';
			return false;
		}
		
		//Pruefen ob person_id eine gueltige Zahl ist
		if(!is_numeric($this->person_id))
		{
			$this->errormsg = 'person_id muss eine gueltige Zahl sein';
			return false;
		}
		$this->errormsg = '';
		return true;		
	}
		
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank	 
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($new=null)
	{
		if($new==null)
			$new = $this->new;
		
		//Variablen pruefen
		if(!$this->validate())
			return false;
			
		if($new)
		{
			//Neuen Datensatz einfuegen								
			$qry='INSERT INTO lehre.tbl_projektbetreuer (person_id, projektarbeit_id, note, betreuerart_kurzbz, faktor, name,
				 punkte, stunden, stundensatz, ext_id, insertamum, insertvon, updateamum, updatevon, vertrag_id) VALUES('.
			     $this->db_add_param($this->person_id).', '.
			     $this->db_add_param($this->projektarbeit_id).', '.
			     $this->db_add_param($this->note).', '.
			     $this->db_add_param($this->betreuerart_kurzbz).', '.
			     $this->db_add_param($this->faktor).', '.
			     $this->db_add_param($this->name).', '.
			     $this->db_add_param($this->punkte).', '.
			     $this->db_add_param($this->stunden).', '.
			     $this->db_add_param($this->stundensatz).', '.
			     $this->db_add_param($this->ext_id).',  now(), '.
			     $this->db_add_param($this->insertvon).', now(), '.
			     $this->db_add_param($this->updatevon).', '.
				 $this->db_add_param($this->vertrag_id).');';			
		}
		else
		{
			//Updaten des bestehenden Datensatzes
			if($this->person_id_old=='')
				$this->person_id_old = $this->person_id;
			
			if(!isset($this->betreuerart_kurzbz_old) || $this->betreuerart_kurzbz_old=='')
				$this->betreuerart_kurzbz_old = $this->betreuerart_kurzbz;
			
			$qry='UPDATE lehre.tbl_projektbetreuer SET '.
				'person_id='.$this->db_add_param($this->person_id).', '. 
				'note='.$this->db_add_param($this->note).', '.
				'betreuerart_kurzbz='.$this->db_add_param($this->betreuerart_kurzbz).', '.
				'faktor='.$this->db_add_param($this->faktor).', '.
				'name='.$this->db_add_param($this->name).', '.
				'punkte='.$this->db_add_param($this->punkte).', '.
				'stunden='.$this->db_add_param($this->stunden).', '.
				'stundensatz='.$this->db_add_param($this->stundensatz).', '.
				'updateamum='.$this->db_add_param($this->updateamum).', '.
			    'updatevon='.$this->db_add_param($this->updatevon).', '.
				'vertrag_id='.$this->db_add_param($this->vertrag_id).' '.
				"WHERE projektarbeit_id=".$this->db_add_param($this->projektarbeit_id, FHC_INTEGER,false).
				" AND person_id=".$this->db_add_param($this->person_id_old, FHC_INTEGER,false).
				" AND betreuerart_kurzbz=".$this->db_add_param($this->betreuerart_kurzbz_old).";";
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
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $person_id ID die geloescht werden soll
	 * @param $projektarbeit_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($person_id, $projektarbeit_id, $betreuerart_kurzbz)
	{
		if(!is_numeric($person_id))
		{
			$this->errormsg = 'Person_id muss eine gueltige Zahl sein';
			return false;
		}
		
		if(!is_numeric($projektarbeit_id))
		{
			$this->errormsg = 'Projektarbeit_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "DELETE FROM lehre.tbl_projektbetreuer WHERE person_id=".$this->db_add_param($person_id, FHC_INTEGER)." AND projektarbeit_id=".$this->db_add_param($projektarbeit_id, FHC_INTEGER)." AND betreuerart_kurzbz=".$this->db_add_param($betreuerart_kurzbz).";";
		
		if($this->db_query($qry))
		{
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Loeschen des Datensatzes';
			return false;
		}
	}
	
	/**
	 * Liefert alle Betreuer zu einer Projektarbeit
	 * @param projektarbeit_id
	 */
	public function getProjektbetreuer($projektarbeit_id)
	{
		if(!is_numeric($projektarbeit_id))
		{
			$this->errormsg = 'Projektarbeit_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM lehre.tbl_projektbetreuer WHERE projektarbeit_id='".$projektarbeit_id."' ORDER BY name";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new projektbetreuer();
				
				$obj->person_id = $row->person_id;
				$obj->projektarbeit_id = $row->projektarbeit_id;
				$obj->note = $row->note;
				$obj->betreuerart_kurzbz = $row->betreuerart_kurzbz;
				$obj->faktor = $row->faktor;
				$obj->name = $row->name;
				$obj->punkte = $row->punkte;
				$obj->stunden = $row->stunden;
				$obj->stundensatz = $row->stundensatz;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->ext_id = $row->ext_id;
				$obj->vertrag_id = $row->vertrag_id;

				$this->result[] = $obj;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler bei einer Abfrage';
			return false;
		}
	}
}
?>
