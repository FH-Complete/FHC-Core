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

class projektbetreuer
{
	var $conn;     			// @var resource DB-Handle
	var $new;       		// @var boolean
	var $errormsg;  		// @var string
	var $result = array(); 	// @var adresse Objekt
	
	//Tabellenspalten
	var $person_id;			// @var integer
	var $projektarbeit_id;	// @var integer
	var $note;				// @var integer
	var $betreuerart_kurzbz;// @var varchar
	var $faktor;			// @var numeric(3,2)
	var $name;				// @var string
	var $punkte;			// @var numeric(6,2)
	var $stunden;			// @var numeric(8,4)
	var $stundensatz;		// @var numeric(6,2)
	var $ext_id;			// @var integer
	var $insertamum;		// @var timestamp
	var $insertvon;			// @var bigint
	var $updateamum;		// @var timestamp
	var $updatevon;			// @var bigint

	var $person_id_old;
	
	/**
	 * Konstruktor
	 * @param $conn      Connection
	 *        $person_id, $projektarbeit ID des Projektbetreuers, der geladen werden soll (Default=null)
	 */
	function projektbetreuer($conn, $person_id=null, $projektarbeit_id=null, $unicode=false)
	{
		$this->conn = $conn;
		if($unicode!=null)
		{
			if ($unicode)
			{
				$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
			}
			else 
			{
				$qry="SET CLIENT_ENCODING TO 'LATIN9';";
			}
			if(!pg_query($conn,$qry))
			{
				$this->errormsg	 = "Encoding konnte nicht gesetzt werden";
				return false;
			}
		}
		if($projektarbeit_id != null && $person_id!=null) 	
			$this->load($person_id, $projektarbeit_id);
	}
	
	/**
	 * Laedt die Funktion mit der ID $person_id, $projektarbeit_id
	 * @param  $person_id ID der zu ladenden Funktion
	 * @param  $projektarbeit_id ID der zu ladenden Funktion
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($person_id, $projektarbeit_id, $betreuerart_kurzbz)
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
		
		$qry = "SELECT * FROM lehre.tbl_projektbetreuer WHERE person_id='$person_id' AND projektarbeit_id='$projektarbeit_id' AND betreuerart_kurzbz='".addslashes($betreuerart_kurzbz)."'";
		
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
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
	 * Prueft die Variablen auf gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	function checkvars()
	{		
				
		//Gesamtlaenge pruefen
		//$this->errormsg='Eine der Gesamtlaengen wurde ueberschritten';
	
		if(strlen($this->betreuerart_kurzbz)>16)
		{
			$this->errormsg = 'betreuerart darf nicht länger als 16 Zeichen sein  - person_id/projektarbeit: '.$this->person_id.'/'.$this->projektarbeit_id;
			return false;
		}
		if(strlen($this->name)>32)
		{
			$this->errormsg = 'Name darf nicht länger als 32 Zeichen sein - person_id/projektarbeit: '.$this->person_id.'/'.$this->projektarbeit_id;
			return false;
		}
		
		if($this->note!='' && !is_numeric($this->note))
		{
			$this->errormsg = 'Note muss ein numerischer Wert sein - person_id/projektarbeit: '.$this->person_id.'/'.$this->projektarbeit_id;
			return false;
		}
		if(!is_numeric($this->punkte))
		{
			$this->errormsg = 'Punkte muß ein numerischer Wert sein - person_id/projektarbeit: '.$this->person_id.'/'.$this->projektarbeit_id;
		}
		if(!is_numeric($this->faktor))
		{
			$this->errormsg = 'Faktor muß ein numerischer Wert sein - person_id/projektarbeit: '.$this->person_id.'/'.$this->projektarbeit_id;
			return false;
		}
		if(!is_numeric($this->stundensatz))
		{
			$this->errormsg = 'Stundensatz muß ein numerischer Wert sein - person_id/projektarbeit: '.$this->person_id.'/'.$this->projektarbeit_id;
			return false;
		}
		
				
		$this->errormsg = '';
		return true;		
	}
	
	// ************************************************
	// * wenn $var '' ist wird "null" zurueckgegeben
	// * wenn $var !='' ist werden datenbankkritische 
	// * Zeichen mit backslash versehen und das Ergebnis
	// * unter Hochkomma gesetzt.
	// ************************************************
	function addslashes($var)
	{
		return ($var!=''?"'".addslashes($var)."'":'null');
	}
	
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank	 
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	function save($new=null)
	{
		if($new==null)
			$new = $this->new;
		
		//Variablen pruefen
		if(!$this->checkvars())
			return false;
			
		if($new)
		{
			//Neuen Datensatz einfuegen								
			$qry='INSERT INTO lehre.tbl_projektbetreuer (person_id, projektarbeit_id, note, betreuerart_kurzbz, faktor, name,
				 punkte, stunden, stundensatz, ext_id, insertamum, insertvon, updateamum, updatevon) VALUES('.
			     $this->addslashes($this->person_id).', '.
			     $this->addslashes($this->projektarbeit_id).', '.
			     $this->addslashes($this->note).', '.
			     $this->addslashes($this->betreuerart_kurzbz).', '.
			     $this->addslashes($this->faktor).', '.
			     $this->addslashes($this->name).', '.
			     $this->addslashes($this->punkte).', '.
			     $this->addslashes($this->stunden).', '.
			     $this->addslashes($this->stundensatz).', '.
			     $this->addslashes($this->ext_id).',  now(), '.
			     $this->addslashes($this->insertvon).', now(), '.
			     $this->addslashes($this->updatevon).');';			
		}
		else
		{
			//Updaten des bestehenden Datensatzes
			if($this->person_id_old=='')
				$this->person_id_old = $this->person_id;
			
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
			if($this->betreuerart_kurzbz_old=='')
				$this->betreuerart_kurzbz_old = $this->betreuerart_kurzbz;
			
			$qry='UPDATE lehre.tbl_projektbetreuer SET '.
				'person_id='.$this->addslashes($this->person_id).', '. 
				'note='.$this->addslashes($this->note).', '.
				'betreuerart_kurzbz='.$this->addslashes($this->betreuerart_kurzbz).', '.
				'faktor='.$this->addslashes($this->faktor).', '.
				'name='.$this->addslashes($this->name).', '.
				'punkte='.$this->addslashes($this->punkte).', '.
				'stunden='.$this->addslashes($this->stunden).', '.
				'stundensatz='.$this->addslashes($this->stundensatz).', '.
				'updateamum='.$this->addslashes($this->updateamum).', '.
			    'updatevon='.$this->addslashes($this->updatevon).' '.
				"WHERE projektarbeit_id='".addslashes($this->projektarbeit_id)."' AND person_id='".addslashes($this->person_id_old)."' AND betreuerart_kurzbz='".addslashes($this->betreuerart_kurzbz_old)."';";
		}
		//echo $qry;
		if(pg_query($this->conn,$qry))
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
	function delete($person_id, $projektarbeit_id, $betreuerart_kurzbz)
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
		
		$qry = "DELETE FROM lehre.tbl_projektbetreuer WHERE person_id='".$person_id."' AND projektarbeit_id='".$projektarbeit_id."' AND betreuerart_kurzbz='".addslashes($betreuerart_kurzbz)."';";
		
		if(pg_query($this->conn, $qry))
		{
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Loeschen des Datensatzes';
			return false;
		}
	}
	
	// ************************************************
	// * Liefert alle Betreuer zu einer Projektarbeit
	// * @param projektarbeit_id
	// ************************************************
	function getProjektbetreuer($projektarbeit_id)
	{
		if(!is_numeric($projektarbeit_id))
		{
			$this->errormsg = 'Projektarbeit_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM lehre.tbl_projektbetreuer WHERE projektarbeit_id='".$projektarbeit_id."' ORDER BY name";
		
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$obj = new projektbetreuer($this->conn, null, null, null);
				
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
				
				$this->result[] = $obj;
			}
		}
	}
}
?>