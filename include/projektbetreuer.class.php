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
	var $person_id;		// @var integer
	var $projektarbeit_id;	// @var integer
	var $note;			// @var integer
	var $betreuerart;		// @var character(1)  b-Bachelorarbeitsbetreuer, d-Diplomarbeitsbetreuer, g-Diplomarbeitsbegutachter
	var $faktor;			// @var numeric(3,2)
	var $name;			// @var string
	var $punkte;			// @var numeric(6,2)
	var $stunden;			// @var integer
	var $stundensatz;		// @var numeric(6,2)
	var $ext_id;			// @var integer
	var $insertamum;		// @var timestamp
	var $insertvon;		// @var bigint
	var $updateamum;		// @var timestamp
	var $updatevon;		// @var bigint

	
	/**
	 * Konstruktor
	 * @param $conn      Connection
	 *        $person_id, $projektarbeit ID des Projektbetreuers, der geladen werden soll (Default=null)
	 */
	function projektbetreuer($conn, $person_id=null, $projektarbeit_id=null, $unicode=false)
	{
		$this->conn = $conn;
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
		//if($projektarbeit_id != null && $person_id!=null) 	$this->load($person_id, $projektarbeit_id);
	}
	
	/**
	 * Laedt die Funktion mit der ID $person_id, $projektarbeit_id
	 * @param  $person_id ID der zu ladenden Funktion
	 * @param  $projektarbeit_id ID der zu ladenden Funktion
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($person_id, $projektarbeit_id)
	{
		//noch nicht implementiert
	}
			
	/**
	 * Prueft die Variablen auf gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	function checkvars()
	{		
				
		//Gesamtlaenge pruefen
		//$this->errormsg='Eine der Gesamtlaengen wurde ueberschritten';
	
		if(strlen($this->betreuerart)>1)
		{
			$this->errormsg = 'betreuerart darf nicht länger als 1 Zeichen sein  - person_id/projektarbeit: '.$this->person_id.'/'.$this->projektarbeit_id;
			return false;
		}
		if(strlen($this->name)>32)
		{
			$this->errormsg = 'Name darf nicht länger als 32 Zeichen sein - person_id/projektarbeit: '.$this->person_id.'/'.$this->projektarbeit_id;
			return false;
		}
		
		if(!is_numeric($this->note))
		{
			$this->errormsg = 'Note muß ein numerischer Wert sein - person_id/projektarbeit: '.$this->person_id.'/'.$this->projektarbeit_id;
			return false;
		}
		if(!is_numeric($this->Punkte))
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
	function save()
	{
		//Variablen pruefen
		if(!$this->checkvars())
			return false;
			
		if($this->new)
		{
			//Neuen Datensatz einfuegen
								
			$qry='INSERT INTO lehre.tbl_projektbetreuer (person_id, projektarbeit_id, note, betreuerart, faktor, name,
				 punkte, stunden, stundensatz, ext_id, insertamum, insertvon, updateamum, updatevon) VALUES('.
			     $this->addslashes($this->person_id).', '.
			     $this->addslashes($this->projektarbeit_id).', '.
			     $this->addslashes($this->note).', '.
			     $this->addslashes($this->betreuerart).', '.
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
			
			$qry='UPDATE lehre.tbl_projektbetreuer SET '.
				'person_id='.$this->addslashes($this->person_id).', '. 
				'projektarbeit_id='.$this->addslashes($this->projektarbeit_id).', '.
				'note='.$this->addslashes($this->note).', '.
				'betreuerart='.$this->addslashes($this->betreuerart).', '.
				'faktor='.$this->addslashes($this->faktor).', '.
				'name='.$this->addslashes($this->name).', '.
				'punkte'.$this->addslashes($this->punkte).', '.
				'stunden='.$this->addslashes($this->stunden).', '.
				'stundensatz='.$this->addslashes($this->stundensatz).', '.
				'updateamum= now(), '.
			     	'updatevon='.$this->addslashes($this->updatevon).' '.
			     	'firmentyp='.$this->addslashes($this->firmentyp_kurzbz).' '.
				'WHERE projektarbeit_id='.$this->addslashes($this->projektarbeit_id).';';
		}
		//echo $qry;
		if(pg_query($this->conn,$qry))
		{
			//Log schreiben
			/*$sql = $qry;
			$qry = "SELECT nextval('log_seq') as id;";
			if(!$row = pg_fetch_object(pg_query($this->conn, $qry)))
			{
				$this->errormsg = 'Fehler beim Auslesen der Log-Sequence';
				return false;
			}
						
			$qry = "INSERT INTO log(log_pk, creationdate, creationuser, sql) VALUES('$row->id', now(), '$this->updatevon', '".addslashes($sql)."')";
			if(pg_query($this->conn, $qry))
				return true;
			else 
			{
				$this->errormsg = 'Fehler beim Speichern des Log-Eintrages';
				return false;
			}	*/
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
	function delete($person_id, $projektarbeit_id)
	{
		//noch nicht implementiert!	
	}
}
?>