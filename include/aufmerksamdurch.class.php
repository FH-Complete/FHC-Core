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
 * Klasse aufmerksamdurch 
 * @create 02-01-2007
 */

class aufmerksamdurch
{
	var $conn;     // @var resource DB-Handle
	var $new;       // @var boolean
	var $errormsg;  // @var string
	var $done=false;	// @var boolean
	var $result = array();
	
	//Tabellenspalten
	Var $aufmerksamdurch_kurzbz;		// @var string
	var $beschreibung;				// @var integer
	var $ext_id;					// @var integer
	
	
	// ****
	// * Konstruktor
	// * @param $conn      Connection
	// *        $aufmerksamdurch_kurzbz = ID (Default=null)
	// *		$unicode
	// ****
	function aufmerksamdurch($conn,$aufmerksamdurch_kurzbz=null, $unicode=false)
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
	}
	
	// *******************************************
	// * @param  $aufmerksam_kurzbz ID 
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************
	function load($aufmerksam_kurzbz)
	{
		//noch nicht implementiert
	}
	
	// *******************************************
	// * Laedt alle Datansaetze
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************
	function getAll($orderby='aufmerksamdurch_kurzbz')
	{
		$qry = "SELECT * FROM public.tbl_aufmerksamdurch";
		if($orderby!='')
			$qry .= " ORDER BY ".$orderby;
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$obj = new aufmerksamdurch($this->conn, null, null);
				
				$obj->aufmerksamdurch_kurzbz = $row->aufmerksamdurch_kurzbz;
				$obj->beschreibung = $row->beschreibung;
				
				$this->result[] = $obj;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim laden';
			return false;			
		}
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
	
	// **************************************************************************
	// * Speichert den aktuellen Datensatz in die Datenbank	 
	// * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	// * andernfalls wird der Datensatz mit der ID in $schluessel_id aktualisiert
	// * @return true wenn ok, false im Fehlerfall
	// **************************************************************************
	function save()
	{
		$this->done=false;
			
		if($this->new)
		{
			//Neuen Datensatz einfuegen
					
			$qry='INSERT INTO public.tbl_aufmerksamdurch (aufmerksamdurch_kurzbz, beschreibung, ext_id) VALUES('.
			     $this->addslashes($this->aufmerksamdurch_kurzbz).', '.
			     $this->addslashes($this->beschreibung).', '.
			     $this->addslashes($this->ext_id).');';
			 $this->done=true;			
		}
		else
		{			
			$qryz="SELECT * FROM public.tbl_aufmerksamdurch WHERE aufmerksamdurch_kurzbz='$this->aufmerksamdurch_kurzbz';";
			if($resultz = pg_query($this->conn, $qryz))
			{
				while($rowz = pg_fetch_object($resultz))
				{
					$update=false;			
					if($rowz->beschreibung!=$this->beschreibung)			$update=true;
					if($rowz->beschreibung!=$this->ext_id)				$update=true;
				
					if($update)
					{
						$qry='UPDATE public.tbl_aufmerksamdurch SET '.
							'beschreibung='.$this->addslashes($this->beschreibung).', '.
							'ext_id='.$this->addslashes($this->ext_id).' '. 
							'WHERE aufmerksamdurch_kurzbz='.$this->addslashes($this->aufmerksamdurch_kurzbz).';';
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
}
?>