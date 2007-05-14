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
 * Klasse Konto
 * @create 2007-05-14
 */

class konto
{
	var $conn;     // @var resource DB-Handle
	var $new;       // @var boolean
	var $errormsg;  // @var string
	var $result = array(); // @var adresse Objekt
		
	//Tabellenspalten
	var $buchungsnr;
	var $person_id;
	var $studiengang_kz;
	var $studiensemester_kurzbz;
	var $buchungsnr_verweis;
	var $betrag;
	var $buchungsdatum;
	var $buchungstext;
	var $mahnspanne;
	var $buchungstyp_kurzbz;
	var $updateamum;
	var $updatevon;
	var $insertamum;
	var $insertvon;
	var $ext_id;
		
	var $beschreibung;
	
	// **************************************************************************
	// * Konstruktor
	// * @param $conn      Connection
	// *        $buchungsnr ID der Adresse die geladen werden soll (Default=null)
	// **************************************************************************
	function konto($conn, $buchungsnr=null, $unicode=false)
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
		
		if($buchungsnr!=null)
			$this->load($buchungsnr);
	}
	
	// ************************************************
	// * Laedt die Funktion mit der ID $buchungsnr
	// * @param  $buchungsnr ID der zu ladenden  Email
	// * @return true wenn ok, false im Fehlerfall
	// ************************************************
	function load($buchungsnr)
	{
		if(!is_numeric($buchungsnr))
		{
			$this->errormsg = 'Buchungsnr muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM public.tbl_konto WHERE buchungsnr='$buchungsnr'";
		
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->buchungsnr = $row->buchungsnr;
				$this->person_id = $row->person_id;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$this->buchungsnr_verweis = $row->buchungsnr_verweis;
				$this->betrag = $row->betrag;
				$this->buchungsdatum = $row->buchungsdatum;
				$this->buchungstext = $row->buchungstext;
				$this->mahnspanne = $row->mahnspanne;
				$this->buchungstyp_kurzbz = $row->buchungstyp_kurzbz;
				$this->updatamum = $row->updateamum;
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
			$this->errormsg = 'Fehler beim laden der Daten';
			return false;
		}
	}
			
	// *******************************************
	// * Prueft die Variablen auf gueltigkeit
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************
	function checkvars()
	{		
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
	
	// ***********************************************************************
	// * Speichert den aktuellen Datensatz in die Datenbank	 
	// * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	// * andernfalls wird der Datensatz mit der ID in $kontakt_id aktualisiert
	// * @param $new true wenn insert false wenn update
	// * @return true wenn ok, false im Fehlerfall
	// ***********************************************************************
	function save($new=null)
	{
		//Variablen pruefen
		if(!$this->checkvars())
			return false;
			
		if($new==null)
			$new = $this->new;
		
		if($new)
		{
			//Neuen Datensatz einfuegen
					
			$qry='BEGIN;INSERT INTO public.tbl_konto (person_id, studiengang_kz, studiensemester_kurzbz, buchungsnr_verweis, betrag, buchungsdatum, buchungstext, mahnspanne, buchungstyp_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id) VALUES('.
			     $this->addslashes($this->person_id).', '.
			     $this->addslashes($this->studiengang_kz).', '.
			     $this->addslashes($this->studiensemester_kurzbz).', '.
			     $this->addslashes($this->buchungsnr_verweis).', '.
			     $this->addslashes($this->betrag).', '.
			     $this->addslashes($this->buchungsdatum).', '.
			     $this->addslashes($this->buchungstext).', '.
			     $this->addslashes($this->mahnspanne).', '.
			     $this->addslashes($this->buchungstyp_kurzbz).', '.
			     $this->addslashes($this->updateamum).', '.
			     $this->addslashes($this->updatevon).', '.
			     $this->addslashes($this->insertamum).', '.
			     $this->addslashes($this->insertvon).', '.
			     $this->addslashes($this->ext_id).');';
		}
		else
		{
			//Updaten des bestehenden Datensatzes
			$qry = 'UPDATE public.tbl_konto SET '.
				   ' person_id='.$this->addslashes($this->person_id).','.
				   ' studiengang_kz='.$this->addslashes($this->studiengang_kz).','.
				   ' studiensemester_kurzbz='.$this->addslashes($this->studiensemester_kurzbz).','.
				   ' buchungsnr_verweis='.$this->addslashes($this->buchungsnr_verweis).','.
				   ' betrag='.$this->addslashes($this->betrag).','.
				   ' buchungsdatum='.$this->addslashes($this->buchungsdatum).','.
				   ' buchungstext='.$this->addslashes($this->buchungstext).','.
				   ' mahnspanne='.$this->addslashes($this->mahnspanne).','.
				   ' buchungstyp_kurzbz='.$this->addslashes($this->buchungstyp_kurzbz).','.
				   ' updateamum='.$this->addslashes($this->updateamum).','.
				   ' updatevon='.$this->addslashes($this->updatevon).','.
				   ' insertamum='.$this->addslashes($this->insertamum).','.
				   ' insertvon='.$this->addslashes($this->insertvon).','.
				   ' ext_id='.$this->addslashes($this->ext_id).';';
				
		}
		//echo $qry;
		
		if(pg_query($this->conn, $qry))
		{
				if($new)
				{
					$qry = "SELECT currval('public.tbl_konto_buchungsnr_seq') as id";
					if($result = pg_query($this->conn, $qry))
					{
						if($row = pg_fetch_object($result))
						{
							$this->buchungsnr = $row->id;
							pg_query($this->conn, 'COMMIT;');
						}
						else 
						{
							$this->errormsg = 'Fehler beim Auslesen der Sequence';
							pg_query($this->conn, 'ROLLBACK;');
							return false;
						}
					}
					else 
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						pg_query($this->conn, 'ROLLBACK;');
						return false;
					}
				}	
				return true;		
		}
		else 
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return true;
		}
	}
	
	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param buchungsnr ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($buchungsnr)
	{
		//Pruefen ob Verweise auf diese Buchung Vorhanden sind
		$qry = "SELECT count(*) as anzahl FROM public.tbl_konto WHERE buchungsnr_verweis='".addslashes($buchungsnr)."'";
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($this->conn, $qry))
			{
				if($row->anzahl>0)
				{
					$this->errormsg = 'Bitte zuerst die zugeordneten Buchungen loeschen';
					return false;
				}
				else 
				{
					//Wenn keine Verweise Vorhanden sind, dann die Buchung loeschen
					$qry = "DELETE FROM public.tbl_konto WHERE buchungsnr_verweis='".addslashes($buchungsnr)."'";
					if(pg_query($this->conn, $qry))
						return true;
					else 
					{
						$this->errormsg = 'Fehler beim Loeschen der Buchung';
						return false;
					}					
				}
			}
			else 
			{
				$this->errormsg = 'Fehler beim Ermitteln der Verweise';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Ermitteln der Verweise';
			return false;
		}
	}
	
	// ******************************************
	// * Laedt alle Buchungen einer Person
	// * und legt diese geordnet in ein Array
	// * @param person_id
	// * @return true wenn ok, false wenn fehler
	// ******************************************
	function getBuchungen($person_id)
	{
		if(!is_numeric($person_id))
		{
			$this->errormsg = 'Person_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM public.tbl_konto WHERE person_id='".$person_id."' ORDER BY buchungsdatum";
		
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$buchung = new konto($this->conn, null, null);

				$buchung->buchungsnr = $row->buchungsnr;
				$buchung->person_id = $row->person_id;
				$buchung->studiengang_kz = $row->studiengang_kz;
				$buchung->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$buchung->buchungsnr_verweis = $row->buchungsnr_verweis;
				$buchung->betrag = $row->betrag;
				$buchung->buchungsdatum = $row->buchungsdatum;
				$buchung->buchungstext = $row->buchungstext;
				$buchung->mahnspanne = $row->mahnspanne;
				$buchung->buchungstyp_kurzbz = $row->buchungstyp_kurzbz;
				$buchung->updatamum = $row->updateamum;
				$buchung->updatevon = $row->updatevon;
				$buchung->insertamum = $row->insertamum;
				$buchung->insertvon = $row->insertvon;
				$buchung->ext_id = $row->ext_id;
				
				if($buchung->buchungsnr_verweis!='')
				{
					$this->result[$buchung->buchungsnr_verweis]['childs'][] = $buchung;
				}
				else 
				{
					$this->result[$buchung->buchungsnr]['parent'] = $buchung;
				}
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim laden der Daten';
			return false;
		}
	}	
	
	// ******************************************
	// * Liefert alle Buchungstypen
	// * @return true wenn ok, false wenn Fehler
	// ******************************************
	function getBuchungstyp()
	{
		$qry = "SELECT * FROM public.tbl_buchungstyp ORDER BY beschreibung";
		
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$typ = new konto($this->conn, null, null);
				
				$typ->buchungstyp_kurzbz = $row->buchungstyp_kurzbz;
				$typ->beschreibung = $row->beschreibung;
				
				$this->result[] = $typ;			
			}
			return false;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
}
?>