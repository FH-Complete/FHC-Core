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
 * Klasse Betriebsmittelperson 
 * @create 13-01-2007
 */

class betriebsmittelperson
{
	var $conn;     			// @var resource DB-Handle
	var $new;       		// @var boolean
	var $errormsg;  		// @var string
	var $done=false;		// @var boolean
	
	//Tabellenspalten
	Var $betriebsmittel_id;	// @var integer
	var $person_id;		// @var integer
	var $anmerkung;		// @var string
	var $kaution;			// @var numeric(5,2)
	var $ausgegebenam;	// @var date
	var $retouram;		// @var date
	var $ext_id;			// @var integer
	var $insertamum;		// @var timestamp
	var $insertvon;		// @var bigint
	var $updateamum;		// @var timestamp
	var $updatevon;		// @var bigint
	
	/**
	 * Konstruktor
	 * @param $conn      Connection
	 *       
	 */
	function betriebsmittelperson($conn,$betriebsmittel_id=null, $unicode=false)
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
	 * Laedt das Betriebsmittel mit der ID $betriebsmittel_id, person_id
	 * @param  $betriebsmittel_id ID des zu ladenden Betriebsmittels
	 * @param  $person_id ID der zu ladenden Person
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($betriebsmittel_id, $person_id)
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
	 * andernfalls wird der Datensatz mit der ID in $betriebsmittel_id, $person_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	function save()
	{
		$this->done=false;
			
		if($this->new)
		{
			//Neuen Datensatz einfuegen
					
			$qry='INSERT INTO public.tbl_betriebsmittelperson (betriebsmittel_id, person_id, anmerkung, kaution, 
			ausgegebenam, retouram, ext_id, insertamum, insertvon, updateamum, updatevon) VALUES('.
			     $this->addslashes($this->betriebsmittel_id).', '.
			     $this->addslashes($this->person_id).', '.
			     $this->addslashes($this->anmerkung).', '.
			     $this->addslashes($this->kaution).', '.
			     $this->addslashes($this->ausgegebenam).', '.
			     $this->addslashes($this->retouram).', '.
			     $this->addslashes($this->ext_id).',  now(), '.
			     $this->addslashes($this->insertvon).', now(), '.
			     $this->addslashes($this->updatevon).');';
			 $this->done=true;			
		}
		else
		{	
			//Pruefen ob betriebsmittel_id eine gueltige Zahl ist
			if(!is_numeric($this->betriebsmittel_id))
			{
				$this->errormsg = "betriebsmittel_id muss eine gueltige Zahl sein: ".$this->betriebsmittel_id." (".$this->person_id.")\n";
				return false;
			}
					
			$qryz="SELECT * FROM public.tbl_betriebsmittelperson WHERE betriebsmittel_id='$this->betriebsmittel_id' AND person_id='$this->person_id';";
			if($resultz = pg_query($this->conn, $qryz))
			{
				while($rowz = pg_fetch_object($resultz))
				{
					$update=false;	
					if($rowz->betriebsmittel_id!=$this->betriebsmittel_id)		$update=true;		
					if($rowz->person_id!=$this->person_id) 				$update=true;
					if($rowz->anmerkung!=$this->anmerkung)			$update=true;
					if($rowz->kaution!=$this->kaution)					$update=true;
					if($rowz->ausgegebenam!=$this->ausgegebenam)		$update=true;
					if($rowz->retouram!=$this->retouram)				$update=true;
					if($rowz->ext_id!=$this->ext_id)	 				$update=true;
				
					if($update)
					{
						$qry='UPDATE public.tbl_betriebsmittelperson SET '.
							'betriebsmittel_id='.$this->addslashes($this->betriebsmittel_id).', '. 
							'person_id='.$this->addslashes($this->person_id).', '. 
							'anmerkung='.$this->addslashes($this->anmerkung).', '. 
							'kaution='.$this->addslashes($this->kaution).', '. 
							'ausgegebenam='.$this->addslashes($this->ausgegebenam).', '.
							'retouram='.$this->addslashes($this->retouram).', '.
							'ext_id='.$this->addslashes($this->ext_id).', '. 
						     	'updateamum= now(), '.
						     	'updatevon='.$this->addslashes($this->updatevon).' '.
							'WHERE betriebsmittel_id='.$this->addslashes($this->betriebsmittel_id).
							' AND person_id='.$this->addslashes($this->person_id).';';
							$this->done=true;
					}
				}
			}
			else 
			{
				return false;
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
				$this->errormsg = "*****\nFehler beim Speichern des Betriebsmittelperson-Datensatzes: ID:".$this->person_id." Betriebsmittel-ID: ".$this->betriebsmittel_id."\n".$qry."\n".pg_errormessage($this->conn)."\n*****\n";
				
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
	 * @param $betriebsmittel_id ID die geloescht werden soll
	 * @param $person_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($betriebsmittel_id, $person_id)
	{
		//noch nicht implementiert!	
	}
}
?>