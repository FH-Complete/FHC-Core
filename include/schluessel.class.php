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
 * Klasse Schüssel 
 * @create 22-12-2006
 */

class schluessel
{
	var $conn;     // @var resource DB-Handle
	var $new;       // @var boolean
	var $errormsg;  // @var string
	var $done=false;	// @var boolean
	
	//Tabellenspalten
	Var $schluessel_id;		// @var integer
	var $person_id;		// @var integer
	var $schluesseltyp;		// @var string
	var $nummer;			// @var string
	var $kaution;			// @var numeric(5,2)
	var $ausgegebenam;	// @var date
	var $ext_id;			// @var integer
	var $insertamum;		// @var timestamp
	var $insertvon;		// @var bigint
	var $updateamum;		// @var timestamp
	var $updatevon;		// @var bigint
	
	/**
	 * Konstruktor
	 * @param $conn      Connection
	 *        $kontakt_id ID der Adresse die geladen werden soll (Default=null)
	 */
	function schluessel($conn,$schluessel_id=null, $unicode=false)
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
	}
	
	/**
	 * Laedt den Schlüssel mit der ID $schluessel_id
	 * @param  $schluessel_id ID dem zu ladenden Schlüssel
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($schluessel_id)
	{
		//noch nicht implementiert
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
	 * andernfalls wird der Datensatz mit der ID in $schluessel_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	function save()
	{
		$this->done=false;
			
		if($this->new)
		{
			//Neuen Datensatz einfuegen
					
			$qry='INSERT INTO public.tbl_schluessel (person_id, schluesseltyp, nummer, kaution, ausgegebenam, 
				ext_id, insertamum, insertvon, updateamum, updatevon) VALUES('.
			     $this->addslashes($this->person_id).', '.
			     $this->addslashes($this->schluesseltyp).', '.
			     $this->addslashes($this->nummer).', '.
			     $this->addslashes($this->kaution).', '.
			     $this->addslashes($this->ausgegebenam).', '.
			     $this->addslashes($this->ext_id).',  now(), '.
			     $this->addslashes($this->insertvon).', now(), '.
			     $this->addslashes($this->updatevon).');';
			 $this->done=true;			
		}
		else
		{			
			$qryz="SELECT * FROM public.tbl_schluessel WHERE schluessel_id='$this->schluessel_id';";
			if($resultz = pg_query($this->conn, $qryz))
			{
				while($rowz = pg_fetch_object($resultz))
				{
					$update=false;			
					if($rowz->person_id!=$this->person_id) 				$update=true;
					if($rowz->schluesseltyp!=$this->schluesseltyp)			$update=true;
					if($rowz->nummer!=$this->nummer)				$update=true;
					if($rowz->kaution!=$this->kaution)					$update=true;
					if($rowz->ausgegebenam!=$this->ausgegebenam)		$update=true;
					if($rowz->ext_id!=$this->ext_id)	 				$update=true;
				
					if($update)
					{
						$qry='UPDATE public.tbl_schluessel SET '.
							'person_id='.$this->addslashes($this->person_id).', '. 
							'schluesseltyp='.$this->addslashes($this->schluesseltyp).', '. 
							'nummer='.$this->addslashes($this->nummer).', '.  
							'kaution='.$this->addslashes($this->kaution).', '. 
							'ausgegebenam='.$this->addslashes($this->ausgegebenam).', '.
							'ext_id='.$this->addslashes($this->ext_id).', '. 
						     	'updateamum= now(), '.
						     	'updatevon='.$this->addslashes($this->updatevon).' '.
							'WHERE schluessel_id='.$this->addslashes($this->schluessel_id).';';
							$this->done=true;
					}
				}
			}
		}
		if ($this->done)
		{
			if(pg_query($this->conn, $qry))
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
		else 
		{
			return true;
		}
	}
	
	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $schluessel_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($schluessel_id)
	{
		//noch nicht implementiert!	
	}
}
?>