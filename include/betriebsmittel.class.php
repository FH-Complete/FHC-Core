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
 * Klasse Betriebsmittel 
 * @create 22-12-2006
 */

class betriebsmittel
{
	var $conn;     			// @var resource DB-Handle
	var $new;       		// @var boolean
	var $errormsg;  		// @var string
	var $done=false;		// @var boolean
	
	//Tabellenspalten
	var $betriebsmittel_id;	// @var integer
	var $betriebsmitteltyp;	// @var string
	var $nummer;			// @var string
	var $reservieren;		// @var boolean
	var $ort_kurzbz;		// @var string
	var $ext_id;			// @var integer
	var $insertamum;		// @var timestamp
	var $insertvon;		// @var bigint
	var $updateamum;		// @var timestamp
	var $updatevon;		// @var bigint
	
	/**
	 * Konstruktor
	 * @param $conn      Connection
	 *        $betriebsmittel_id ID des Betrtiebsmittels, das geladen werden soll (Default=null)
	 */
	function betriebsmittel($conn,$betriebsmittel_id=null, $unicode=false)
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
			$this->errormsg= "Encoding konnte nicht gesetzt werden";
			return false;
		}
	}
	
	/**
	 * Laedt das Betriebsmittel mit der ID $betriebsmittel_id
	 * @param  $betriebsmittel_id ID des zu ladenden Betriebsmittel
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($betriebsmittel_id)
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
	 * andernfalls wird der Datensatz mit der ID in $betriebsmittel_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	function save()
	{
		$this->done=false;
			
		if($this->new)
		{
			//Neuen Datensatz einfuegen
					
			$qry='INSERT INTO public.tbl_betriebsmittel (betriebsmittel_id, beschreibung, betriebsmitteltyp, nummer, reservieren, ort_kurzbz,
				ext_id, insertamum, insertvon, updateamum, updatevon) VALUES('.
			     $this->addslashes($this->betriebsmittel_id).', '.
			     $this->addslashes($this->beschreibung).', '.
			     $this->addslashes($this->betriebsmitteltyp).', '.
			     $this->addslashes($this->nummer).', '.
			     ($this->reservieren?'true':'false').', '. 
			     $this->addslashes($this->ort_kurzbz).', '.
			     $this->addslashes($this->ext_id).',  now(), '.
			     $this->addslashes($this->insertvon).', now(), '.
			     $this->addslashes($this->updatevon).');';
			 $this->done=true;			
		}
		else
		{			
			$qryz="SELECT * FROM public.tbl_betriebsmittel WHERE betriebsmittel_id='$this->betriebsmittel_id';";
			if($resultz = pg_query($this->conn, $qryz))
			{
				while($rowz = pg_fetch_object($resultz))
				{
					$update=false;			
					if($rowz->beschreibung!=$this->beschreibung) 			$update=true;
					if($rowz->betriebsmitteltyp!=$this->betriebsmitteltyp)		$update=true;
					if($rowz->nummer!=$this->nummer)				$update=true;
					if($rowz->reservieren!=$this->reservieren)			$update=true;
					if($rowz->ort_kurzbz!=$this->ort_kurzbz)				$update=true;
					if($rowz->ext_id!=$this->ext_id)	 				$update=true;
				
					if($update)
					{
						$qry='UPDATE public.tbl_betriebsmittel SET '.
							'betriebsmitteltyp='.$this->addslashes($this->betriebsmitteltyp).', '. 
							'nummer='.$this->addslashes($this->nummer).', '.  
							'reservieren='.($this->reservieren?'true':'false').', '.
							'ort_kurzbz='.$this->addslashes($this->ort_kurzbz).', '.
							'ext_id='.$this->addslashes($this->ext_id).', '. 
						     	'updateamum= now(), '.
						     	'updatevon='.$this->addslashes($this->updatevon).' '.
							'WHERE betriebsmittel_id='.$this->addslashes($this->betriebsmittel_id).';';
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
				$this->errormsg = "*****\nFehler beim Speichern des Betriebsmittel-Datensatzes: ID:".$this->betriebsmittel_id." Schlüsseltyp: ".$this->betriebsmitteltyp."\n".$qry."\n".pg_errormessage($this->conn)."\n*****\n";
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
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($betriebsmittel_id)
	{
		//noch nicht implementiert!	
	}
}
?>