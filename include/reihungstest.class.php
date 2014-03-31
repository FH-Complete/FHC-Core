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
require_once(dirname(__FILE__).'/basis_db.class.php');

class reihungstest extends basis_db 
{
	public $new;			//  boolean
	public $done=false;		//  boolean
	public $result = array();
	
	//Tabellenspalten
	public $reihungstest_id;//  integer
	public $studiengang_kz;	//  integer
	public $ort_kurzbz;		//  string
	public $anmerkung;		//  string
	public $datum;			//  date
	public $uhrzeit;		//  time without time zone
	public $ext_id;			//  integer
	public $insertamum;		//  timestamp
	public $insertvon;		//  bigint
	public $updateamum;		//  timestamp
	public $updatevon;		//  bigint
	public $freigeschaltet=false;	//  boolean
	
	/**
	 * Konstruktor
	 * @param $reihungstest_id ID der Adresse die geladen werden soll (Default=null)
	 */
	public function __construct($reihungstest_id=null)
	{
		parent::__construct();
		
		if(!is_null($reihungstest_id))
			$this->load($reihungstest_id);
	}
	
	/**
	 * Laedt den Reihungstest mit der ID $reihungstest_id
	 * @param  $sreihungstest_id ID des zu ladenden Reihungstests
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($reihungstest_id)
	{
		if(!is_numeric($reihungstest_id))
		{
			$this->errormsg = 'Reihungstest_id ist ungueltig';
			return false;
		}
		
		$qry = "SELECT * FROM public.tbl_reihungstest WHERE reihungstest_id=".$this->db_add_param($reihungstest_id, FHC_INTEGER, false);
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
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
				$this->freigeschaltet = $this->db_parse_bool($row->freigeschaltet);
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
	
	/**
	 * Liefert alle Reihungstests
	 * wenn ein Datum uebergeben wird, dann werden alle Reihungstests ab diesem 
	 * Datum zurueckgeliefert
	 */
	public function getAll($datum=null)
	{
		$qry = "SELECT * FROM public.tbl_reihungstest ";
		if($datum!=null)
			$qry.=" WHERE datum>=".$this->db_add_param($datum);
		$qry.=" ORDER BY datum DESC, uhrzeit";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new reihungstest();
				
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
				$obj->freigeschaltet = $this->db_parse_bool($row->freigeschaltet);
				
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
		
	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	private function validate()
	{		
		//Zahlenfelder pruefen
		if(!is_numeric($this->studiengang_kz))
		{
			$this->errormsg='studiengang_kz enthaelt ungueltige Zeichen';
			return false;
		}
		//Gesamtlaenge pruefen
		if(mb_strlen($this->ort_kurzbz)>32)
		{
			$this->errormsg = 'Ort_kurzbz darf nicht länger als 16 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->anmerkung)>64)
		{
			$this->errormsg = 'Anmerkung darf nicht länger als 64 Zeichen sein';
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
	public function save()
	{
		if(!$this->validate())
			return false;
		
		if($this->new)
		{
			//Neuen Datensatz einfuegen
					
			$qry='BEGIN; INSERT INTO public.tbl_reihungstest (studiengang_kz, ort_kurzbz, anmerkung, datum, uhrzeit, 
				ext_id, insertamum, insertvon, updateamum, updatevon, freigeschaltet) VALUES('.
			     $this->db_add_param($this->studiengang_kz, FHC_INTEGER).', '.
			     $this->db_add_param($this->ort_kurzbz).', '.
			     $this->db_add_param($this->anmerkung).', '.
			     $this->db_add_param($this->datum).', '.
			     $this->db_add_param($this->uhrzeit).', '.
			     $this->db_add_param($this->ext_id, FHC_INTEGER).',  now(), '.
			     $this->db_add_param($this->insertvon).', now(), '.
			     $this->db_add_param($this->updatevon).','.
			     $this->db_add_param($this->freigeschaltet, FHC_BOOLEAN).');';
		}
		else
		{			
			$qry='UPDATE public.tbl_reihungstest SET '.
				'studiengang_kz='.$this->db_add_param($this->studiengang_kz, FHC_INTEGER).', '. 
				'ort_kurzbz='.$this->db_add_param($this->ort_kurzbz).', '.
				'anmerkung='.$this->db_add_param($this->anmerkung).', '.  
				'datum='.$this->db_add_param($this->datum).', '. 
				'uhrzeit='.$this->db_add_param($this->uhrzeit).', '.
				'ext_id='.$this->db_add_param($this->ext_id, FHC_INTEGER).', '. 
		     	'updateamum= now(), '.
		     	'updatevon='.$this->db_add_param($this->updatevon).', '.
				'freigeschaltet='.$this->db_add_param($this->freigeschaltet, FHC_BOOLEAN).' '.
				'WHERE reihungstest_id='.$this->db_add_param($this->reihungstest_id, FHC_INTEGER, false).';';					
		}
		
		if($this->db_query($qry))
		{
			if($this->new)
			{
				$qry = "SELECT currval('public.tbl_reihungstest_reihungstest_id_seq') as id";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->reihungstest_id = $row->id;
						$this->db_query('COMMIT');
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
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}
		
	/**
	 * Liefert die Reihungstests eines Studienganges
	 *
	 * @param $studiengang_kz
	 * @param $order (optional)
	 * @return true wenn ok, sonst false
	 */
	public function getReihungstest($studiengang_kz,$order=null)
	{
		$qry = "SELECT * FROM public.tbl_reihungstest WHERE studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER, false);
		
		if ($order!=null)
			$qry .=" ORDER BY ".$order.";";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new reihungstest();
				
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
				$obj->freigeschaltet = $this->db_parse_bool($row->freigeschaltet);
				
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

	/**
	 * Liefert die Reihungstests der Zukunft und einer bestimmten ID
	 * Und sortiert diese so, dass die des uebergebenen Studienganges zuerst geliefert werden
	 * @param $include_id
	 * @param $studiengang_kz
	 * @return true wenn ok, sonst false
	 */
	public function getZukuenftige($include_id, $studiengang_kz)
	{
		$qry = "SELECT *, '1' as sortierung,(SELECT upper(typ || kurzbz) FROM public.tbl_studiengang WHERE studiengang_kz=tbl_reihungstest.studiengang_kz) as stg FROM public.tbl_reihungstest WHERE datum>=now()-'1 days'::interval AND studiengang_kz=".$this->db_add_param($studiengang_kz)."
			UNION 
			SELECT *, '2' as sortierung,(SELECT upper(typ || kurzbz) FROM public.tbl_studiengang WHERE studiengang_kz=tbl_reihungstest.studiengang_kz) as stg FROM public.tbl_reihungstest WHERE datum>=now()-'1 days'::interval AND studiengang_kz!=".$this->db_add_param($studiengang_kz)."
			UNION
			SELECT *, '0' as sortierung,(SELECT upper(typ || kurzbz) FROM public.tbl_studiengang WHERE studiengang_kz=tbl_reihungstest.studiengang_kz) as stg FROM public.tbl_reihungstest WHERE reihungstest_id=".$this->db_add_param($include_id)."
			ORDER BY sortierung, stg, datum";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new reihungstest();
				
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
				$obj->freigeschaltet = $this->db_parse_bool($row->freigeschaltet);
				
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
