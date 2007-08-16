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
 * Klasse Reihungstest 
 * @create 10-01-2007
 */

class reihungstest
{
	var $conn;     // @var resource DB-Handle
	var $new;       // @var boolean
	var $errormsg;  // @var string
	var $done=false;	// @var boolean
	var $result = array();
	
	//Tabellenspalten
	Var $reihungstest_id;	// @var integer
	var $studiengang_kz;	// @var integer
	var $ort_kurzbz;		// @var string
	var $anmerkung;		// @var string
	var $datum;			// @var date
	var $uhrzeit;			// @var time without time zone
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
	function reihungstest($conn,$reihungstest_id=null, $unicode=false)
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
	
	/**
	 * Laedt den Reihungstest mit der ID $reihungstest_id
	 * @param  $sreihungstest_id ID des zu ladenden Reihungstests
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($reihungstest_id)
	{
		if(!is_numeric($reihungstest_id))
		{
			$this->errormsg = 'Reihungstest_id ist ungueltig';
			return false;
		}
		
		$qry = "SELECT * FROM public.tbl_reihungstest WHERE reihungstest_id='$reihungstest_id'";
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{								
				$this->reihungstest_id = $row->reihungstest_id;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->ort_kurzbz = $row->ort_kurzbz;
				$this->anmerkung = $row->anmerkung;
				$this->datum = $row->datum;
				$this->uhrzeit = $row->uhrzeit;
				$this->ext_id = $row->ext_id;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				return true;				
			}
			else 
			{
				$this->errormsg = 'Reihungstest existiert nicht';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Reihungstests';
			return false;
		}
	}
	
	// ****
	// * Liefert alle Reihungstests
	// * wenn ein Datum uebergeben wird, dann werden alle Reihungstests ab diesem 
	// * Datum zurueckgeliefert
	// ****
	function getAll($datum=null)
	{
		$qry = "SELECT * FROM public.tbl_reihungstest ";
		if($datum!=null)
			$qry.=" WHERE datum>='$datum'";
		$qry.=" ORDER BY datum, uhrzeit";
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$obj = new reihungstest($this->conn, null, null);
				
				$obj->reihungstest_id = $row->reihungstest_id;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->ort_kurzbz = $row->ort_kurzbz;
				$obj->anmerkung = $row->anmerkung;
				$obj->datum = $row->datum;
				$obj->uhrzeit = $row->uhrzeit;
				$obj->ext_id = $row->ext_id;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				
				$this->result[] = $obj;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Reihungstests';
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
	/**
	 * Prueft die Variablen auf gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	function checkvars()
	{		
		//Zahlenfelder pruefen
		if(!is_numeric($this->studiengang_kz))
		{
			$this->errormsg='studiengang_kz enthaelt ungueltige Zeichen:'.$this->reihungstest_id.' - Studiengang: '.$row->studiengang_kz;
			return false;
		}
		//Gesamtlaenge pruefen
		//$this->errormsg='Eine der Gesamtlaengen wurde ueberschritten';
		if(strlen($this->ort_kurzbz)>8)
		{
			$this->errormsg = 'Ort_kurzbz darf nicht länger als 8 Zeichen sein  - Studiengang: '.$row->studiengang_kz;
			return false;
		}
		if(strlen($this->anmerkung)>64)
		{
			$this->errormsg = 'Anmerkung darf nicht länger als 64 Zeichen sein - Studiengang: '.$row->studiengang_kz;
			return false;
		}
				
		$this->errormsg = '';
		return true;		
	}
	
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank	 
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $reihungstest_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	function save()
	{
		$this->done=false;
			
		if($this->new)
		{
			//Neuen Datensatz einfuegen
					
			$qry='INSERT INTO public.tbl_reihungstest (studiengang_kz, ort_kurzbz, anmerkung, datum, uhrzeit, 
				ext_id, insertamum, insertvon, updateamum, updatevon) VALUES('.
			     $this->addslashes($this->studiengang_kz).', '.
			     $this->addslashes($this->ort_kurzbz).', '.
			     $this->addslashes($this->anmerkung).', '.
			     $this->addslashes($this->datum).', '.
			     $this->addslashes($this->uhrzeit).', '.
			     $this->addslashes($this->ext_id).',  now(), '.
			     $this->addslashes($this->insertvon).', now(), '.
			     $this->addslashes($this->updatevon).');';
			 $this->done=true;			
		}
		else
		{			
			$qry="SELECT * FROM public.tbl_reihungstest WHERE reihungstest_id='$this->reihungstest_id';";
			if($resultz = pg_query($this->conn, $qry))
			{
				while($rowz = pg_fetch_object($resultz))
				{
					$update=false;			
					if($rowz->studiengang_kz!=$this->studiengang_kz)		$update=true;
					if($rowz->ort_kurzbz!=$this->ort_kurzbz)				$update=true;
					if($rowz->anmerkung!=$this->anmerkung)			$update=true;
					if($rowz->datum!=$this->datum)					$update=true;
					if($rowz->uhrzeit!=$this->uhrzeit)					$update=true;
					if($rowz->ext_id!=$this->ext_id)	 				$update=true;
				
					if($update)
					{
						$qry='UPDATE public.tbl_reihungstest SET '.
							'studiengang_kz='.$this->addslashes($this->studiengang_kz).', '. 
							'ort_kurzbz='.$this->addslashes($this->ort_kurzbz).', '. 
							'anmerkung='.$this->addslashes($this->anmerkung).', '.  
							'datum='.$this->addslashes($this->datum).', '. 
							'uhrzeit='.$this->addslashes($this->uhrzeit).', '.
							'ext_id='.$this->addslashes($this->ext_id).', '. 
						     	'updateamum= now(), '.
						     	'updatevon='.$this->addslashes($this->updatevon).' '.
							'WHERE reihungstest_id='.$this->addslashes($this->reihungstest_id).';';
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
				$this->errormsg = 'Fehler beim Speichern der Daten: '.$this->reihungstest_id.'/'.$qry;
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
	 * @param $reihungstest_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($reihungstest_id)
	{
		//noch nicht implementiert!	
	}
	
	function getReihungstest($studiengang_kz)
	{
		$qry = "SELECT * FROM public.tbl_reihungstest WHERE studiengang_kz='$studiengang_kz'";
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$obj = new reihungstest($this->conn, null, null);
				
				$obj->reihungstest_id = $row->reihungstest_id;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->ort_kurzbz = $row->ort_kurzbz;
				$obj->anmerkung = $row->anmerkung;
				$obj->datum = $row->datum;
				$obj->uhrzeit = $row->uhrzeit;
				$obj->ext_id = $row->ext_id;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				
				$this->result[] = $obj;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Reihungstests';
			return false;
		}
	}
}
?>