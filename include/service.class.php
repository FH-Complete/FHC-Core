<?php
/* Copyright (C) 20012 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and 
 */
/**
 * Klasse Service
 *  
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class service extends basis_db
{
	public $new;
	public $result = array();

	//Tabellenspalten
	public $service_id;		// bigint
	public $bezeichnung;	// varchar(64)
	public $beschreibung; 	// text
	public $ext_id;			// bigint
	public $oe_kurzbz;		// varchar(32)
	public $content_id;		// integer
	 
	/**
	 * Konstruktor - Laedt optional ein Service
	 * @param $service_id
	 */
	public function __construct($service_id=null)
	{
		parent::__construct();
				
		if(!is_null($service_id))
			$this->load($service_id);
	}

	/**
	 * Laedt ein Service mit der uebergebenen ID
	 * 
	 * @param $service_id
	 * @return boolean
	 */
	public function load($service_id)
	{
		if(!is_numeric($service_id))
		{
			$this->errormsg = 'Service ID ist ungueltig';
			return false;
		}
		
		
		$qry = "SELECT * FROM public.tbl_service WHERE service_id=".$this->db_add_param($service_id, FHC_INTEGER);

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->service_id = $row->service_id;
				$this->bezeichnung = $row->bezeichnung;
				$this->beschreibung = $row->beschreibung;
				$this->ext_id = $row->ext_id;
				$this->oe_kurzbz = $row->oe_kurzbz;
				$this->content_id = $row->content_id;
				
				return true;
			}
			else
			{
				$this->errormsg = 'Service mit dieser ID exisitert nicht';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden des Service';
			return false;
		}
	}
	
	/**
	 * Laedt alle vorhandenen Services
	 */
	public function getAll()
	{	
		$qry = "SELECT * FROM public.tbl_service ORDER BY oe_kurzbz, bezeichnung";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new service();
					
				$obj->service_id = $row->service_id;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->beschreibung = $row->beschreibung;
				$obj->ext_id = $row->ext_id;
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->content_id = $row->content_id;
					
				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg='Fehler beim Laden der Daten';
			return false;
		}
	}
	
	/**
	 * Sucht ein Service
	 * 
	 * @param $suchstring
	 */
	public function search($suchstring)
	{	
		$qry = "SELECT * FROM public.tbl_service WHERE 1=1 ";	
		foreach($suchstring as $value)
			$qry.="AND (lower(beschreibung::text) like lower('%".$this->db_escape($value)."%')
					OR lower(beschreibung::text) like lower('%".$this->db_escape(htmlentities($value,ENT_NOQUOTES,'UTF-8'))."%'))";
		$qry.=" ORDER BY oe_kurzbz, bezeichnung";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new service();
					
				$obj->service_id = $row->service_id;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->beschreibung = $row->beschreibung;
				$obj->ext_id = $row->ext_id;
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->content_id = $row->content_id;
					
				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg='Fehler beim Laden der Daten';
			return false;
		}
	}
	
	/**
	 * Laedt alle vorhandenen Services, sortiert nach den am haeufigsten vom User in der Zeitaufzeichnung verwendeten
	 * 
	 * <p>Optionaler Zeitraum (Tage in die Vergangenheit), in denen das Service vorkommt<br>
	 * Optionale Anzahl an Ereignissen im angegebenen Zeitraum, um das Service zu beruecksichtigen</p>
	 * 
	 * @param string $user uid
	 * @param integer $zeitraum Anzahl Tage in die Vergangenheit, die fuer das Auftreten des Service beruecksichtigt werden sollen
	 * @param integer $anzahl_ereignisse default: 3 Wie oft soll dieses Service mindestens in $zeitraum vorkommen, um beruecksichtigt zu werden
	 */
	public function getFrequentServices($user, $zeitraum=null, $anzahl_ereignisse='3')
	{	
		if(!is_numeric($anzahl_ereignisse))
		{
			$this->errormsg = 'anzahl_ereignisse muss eine gueltige Zahl sein';
			return false;
		}
		
		if (!is_null($zeitraum) && $zeitraum>0 && is_numeric($zeitraum))
			$zeit = "AND tbl_zeitaufzeichnung.start>=(now()::date-$zeitraum)";
		else 
			$zeit = "";
		
		$qry = "	SELECT service_id,oe_kurzbz,bezeichnung,beschreibung,ext_id,content_id, sum(a.anzahl) AS anzahl FROM (
						SELECT 
						tbl_service.*,
						  (SELECT COUNT (tbl_zeitaufzeichnung.service_id) FROM campus.tbl_zeitaufzeichnung 
						   WHERE tbl_service.service_id=tbl_zeitaufzeichnung.service_id AND tbl_zeitaufzeichnung.uid=".$this->db_add_param($user)." 
						   $zeit
						   ) AS anzahl
						FROM public.tbl_service
						WHERE 
						  (SELECT COUNT (tbl_zeitaufzeichnung.service_id) FROM campus.tbl_zeitaufzeichnung
						   WHERE tbl_service.service_id=tbl_zeitaufzeichnung.service_id AND tbl_zeitaufzeichnung.uid=".$this->db_add_param($user)." 
						   $zeit
						   ) > $anzahl_ereignisse
						GROUP BY tbl_service.service_id,tbl_service.beschreibung,tbl_service.ext_id,tbl_service.oe_kurzbz,tbl_service.bezeichnung,tbl_service.content_id,anzahl
											
						UNION
						SELECT tbl_service.*, '0' AS anzahl
						FROM public.tbl_service
					) AS a
					GROUP BY service_id,oe_kurzbz,bezeichnung,beschreibung,ext_id,content_id
					ORDER BY anzahl DESC,bezeichnung,oe_kurzbz";
						   echo $qry;
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new service();
					
				$obj->service_id = $row->service_id;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->beschreibung = $row->beschreibung;
				$obj->ext_id = $row->ext_id;
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->anzahl = $row->anzahl;
				$obj->content_id = $row->content_id;
					
				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg='Fehler beim Laden der Daten';
			return false;
		}
	}
	
	/**
	 * Laedt die Services der uebergebenen OE
	 * 
	 * @param $oe_kurzbz
	 */
	public function getServicesOrganisationseinheit($oe_kurzbz)
	{	
		$qry = 'SELECT 
					* 
				FROM 
					public.tbl_service 
				WHERE 
					oe_kurzbz='.$this->db_add_param($oe_kurzbz).' 
				ORDER BY bezeichnung';
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new service();
					
				$obj->service_id = $row->service_id;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->beschreibung = $row->beschreibung;
				$obj->ext_id = $row->ext_id;
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->content_id = $row->content_id;
					
				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg='Fehler beim Laden der Daten';
			return false;
		}
	}
	
	/**
	 * Laedt die Services der uebergebenen OE und alle Services, die dieser OE untergliedert sind
	 * 
	 * @param $oe_kurzbz
	 * @param $order Default: oe_kurzbz,bezeichnung
	 */
	public function getSubServicesOrganisationseinheit($oe_kurzbz, $order='oe_kurzbz,bezeichnung')
	{	
		$qry = 'SELECT
					* 
				FROM 
					public.tbl_service 
				WHERE 
					oe_kurzbz IN (SELECT oe_kurzbz FROM public.tbl_organisationseinheit WHERE oe_parent_kurzbz='.$this->db_add_param($oe_kurzbz).')
				ORDER BY '.$order;
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new service();
					
				$obj->service_id = $row->service_id;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->beschreibung = $row->beschreibung;
				$obj->ext_id = $row->ext_id;
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->content_id = $row->content_id;
					
				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg='Fehler beim Laden der Daten';
			return false;
		}
	}
	
	/**
	 * Prueft die Daten vor dem Speichern
	 * @return boolean
	 */
	public function validate()
	{
		return true;
	}
	
	/**
	 * Speichert ein Service
	 * @param $new
	 */
	public function save($new=null)
	{
		if(is_null($new))
			$new = $this->new;
			
		if(!$this->validate())
			return false;
					
		if($new)
		{
			$qry = "BEGIN;INSERT INTO public.tbl_service (bezeichnung, beschreibung, ext_id, oe_kurzbz, content_id)
					VALUES(".
				$this->db_add_param($this->bezeichnung).','.
				$this->db_add_param($this->beschreibung).','.
				$this->db_add_param($this->ext_id).','.
				$this->db_add_param($this->oe_kurzbz).','.
				$this->db_add_param($this->content_id).');';
		}
		else
		{
			$qry = 'UPDATE public.tbl_service SET'.
				' bezeichnung = '.$this->db_add_param($this->bezeichnung).','.
				' beschreibung = '.$this->db_add_param($this->beschreibung).','.
				' ext_id = '.$this->db_add_param($this->ext_id).','.
				' oe_kurzbz = '.$this->db_add_param($this->oe_kurzbz).','.
				' content_id = '.$this->db_add_param($this->content_id).
				' WHERE service_id='.$this->db_add_param($this->service_id, FHC_INTEGER).';';					
		}
		
		if($this->db_query($qry))
		{
			if($new)
			{
				$qry = "SELECT currval('public.seq_service_service_id') as id";
				if($result = $this->db_query($qry))
				{
					if($row = $this->db_fetch_object($result))
					{
						$this->service_id = $row->id;
						$this->db_query('COMMIT;');
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
			else
				return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}
	
	/**
	 * Loescht einen Service
	 
	 * @param $service_id
	 */
	public function delete($service_id)
	{
		if(!is_numeric($service_id))
		{
			$this->errormsg='ID ist ungueltig';
			return false;
		}
		$qry = "DELETE FROM public.tbl_service WHERE service_id=".$this->db_add_param($service_id);
		
		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Loeschen des Service';
			return false;
		}
	}
}
?>