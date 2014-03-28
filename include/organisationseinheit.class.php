<?php
/* Copyright (C) 2009 fhcomplete.org
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *			Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>
 * 			Stefan Puraner	<puraner@technikum-wien.at>
 */
/**
 * Klasse Organisationseinheit
 * 
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class organisationseinheit extends basis_db
{
	public static $oe_parents_array=array();
	public $new;     			// @var boolean
	public $errormsg; 			// @var string
	public $result;
	
	//Tabellenspalten
	public $oe_kurzbz;
	public $oe_parent_kurzbz;
	public $bezeichnung;
	public $organisationseinheittyp_kurzbz;
	public $aktiv=true;
	public $lehre=true;
	public $mailverteiler=false;
	
	public $oe_kurzbz_orig;
	public $beschreibung;
	
	
	/**
	 * Konstruktor
	 * @param $oe_kurzbz Kurzbz der Organisationseinheit
	 */
	public function __construct($oe_kurzbz=null)
	{
		parent::__construct();
				
		if($oe_kurzbz != null)
			$this->load($oe_kurzbz);
	}

	/**
	 * Liefert alle Organisationseinheiten
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAll($aktiv=null, $lehre=null)
	{
		$qry = "SELECT * FROM public.tbl_organisationseinheit WHERE 1=1";

		if(!is_null($aktiv))
			$qry.=" AND aktiv=".$this->db_add_param($aktiv, FHC_BOOLEAN);

		if(!is_null($lehre))		
			$qry.=" AND lehre=".$this->db_add_param($lehre, FHC_BOOLEAN);

		$qry .=" ORDER BY organisationseinheittyp_kurzbz, oe_kurzbz";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new organisationseinheit();
				
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->oe_parent_kurzbz = $row->oe_parent_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->organisationseinheittyp_kurzbz = $row->organisationseinheittyp_kurzbz;
				$obj->aktiv = $this->db_parse_bool($row->aktiv);
				$obj->mailverteiler = $this->db_parse_bool($row->mailverteiler);
				$obj->lehre = $this->db_parse_bool($row->lehre);
				
				$this->result[] = $obj;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Organisationseinheiten';
			return false;
		}
	}
	
	/**
	 * Laedt eine Organisationseinheit
	 * @param $oe_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($oe_kurzbz)
	{
		if($oe_kurzbz == '')
		{
			$this->errormsg = 'kurzbz darf nicht leer sein';
			return false;
		}

		$qry = "SELECT * FROM public.tbl_organisationseinheit WHERE oe_kurzbz=".$this->db_add_param($oe_kurzbz).";";

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}

		if($row=$this->db_fetch_object())
		{
			$this->oe_kurzbz = $row->oe_kurzbz;
			$this->bezeichnung = $row->bezeichnung;
			$this->oe_parent_kurzbz = $row->oe_parent_kurzbz;
			$this->organisationseinheittyp_kurzbz = $row->organisationseinheittyp_kurzbz;
			$this->aktiv = $this->db_parse_bool($row->aktiv);
			$this->mailverteiler = $this->db_parse_bool($row->mailverteiler);
			$this->lehre = $this->db_parse_bool($row->lehre);
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}

		return true;
	}
	
	/**
	 * Laedt alle Organisationseinheiten an oberster Stelle
	 *
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getHeads()
	{
		$qry = "SELECT * FROM public.tbl_organisationseinheit WHERE oe_parent_kurzbz is null";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new organisationseinheit();
				
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->oe_parent_kurzbz = $row->oe_parent_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->organisationseinheittyp_kurzbz = $row->organisationseinheittyp_kurzbz;
				$obj->aktiv = $this->db_parse_bool($row->aktiv);
				$obj->mailverteiler = $this->db_parse_bool($row->mailverteiler);
				$obj->lehre = $this->db_parse_bool($row->lehre);
				
				$this->result[] = $obj;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler bei Abfrage';
			return false;
		}
	}
	
	/**
	 * Liefert die ChildNodes einer Organisationseinheit
	 *
	 * @param $oe_kurzbz
	 * @return Array mit den Childs inkl dem Uebergebenen Element
	 */
	public function getChilds($oe_kurzbz)
	{
		$childs[] = $oe_kurzbz;
		
		$dbversion = $this->db_version();
		if($dbversion['server']>=8.4)
		{
			//ab PostgreSQL Version 8.4 wird die Rekursion von der DB aufgeloest
			$qry = "
			WITH RECURSIVE oes(oe_kurzbz, oe_parent_kurzbz) as 
			(
				SELECT oe_kurzbz, oe_parent_kurzbz FROM public.tbl_organisationseinheit 
				WHERE oe_kurzbz='".addslashes($oe_kurzbz)."'
				UNION ALL
				SELECT o.oe_kurzbz, o.oe_parent_kurzbz FROM public.tbl_organisationseinheit o, oes 
				WHERE o.oe_parent_kurzbz=oes.oe_kurzbz
			)
			SELECT oe_kurzbz
			FROM oes
			GROUP BY oe_kurzbz;";
			if($myresult = $this->db_query($qry))
			{
				while($row = $this->db_fetch_object($myresult))
				{
					$childs[] = $row->oe_kurzbz;
				}
			}
			else 
			{
				$this->errormsg = 'Fehler beim Ermitteln der Childs';
			}
			return $childs;
		}
		else 
		{		
			//vor 8.4 muss die Rekursion in PHP aufgeloest werden
			$qry = "SELECT * FROM public.tbl_organisationseinheit WHERE oe_parent_kurzbz = '$oe_kurzbz'";
		
			if($myresult = $this->db_query($qry))
			{
				while($row = $this->db_fetch_object($myresult))
				{
					$childs = array_merge($childs, $this->getChilds($row->oe_kurzbz));
				}
			}
			else 
			{
				$this->errormsg = 'Fehler beim Ermitteln der Childs';
			}
			return $childs;
		}
	}
	
	/**
	 * Liefert die Direkten KindElemente der Organisationseinheit
	 *
	 * @param $oe_kurzbz
	 * @return Array mit den Childs inkl derm Uebergebenen Element
	 */
	public function getDirectChilds($oe_kurzbz)
	{		
		$childs = array();
		$qry = "SELECT * FROM public.tbl_organisationseinheit WHERE oe_parent_kurzbz = '$oe_kurzbz' ORDER BY organisationseinheittyp_kurzbz DESC, bezeichnung";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$childs[] = $row->oe_kurzbz;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Ermitteln der Childs';
		}
		return $childs;
	}
	
	/**
	 * Speichert eine Organisationseinheit
	 *
	 * @param $new
	 * @return boolean
	 */
	public function save($new=null)
	{
		if(is_null($new))
			$new = $this->new;
			
		if($new)
		{
			//Neu anlegen
			$qry = 'INSERT INTO public.tbl_organisationseinheit(oe_kurzbz, oe_parent_kurzbz, bezeichnung, 
																organisationseinheittyp_kurzbz, aktiv, mailverteiler, lehre) VALUES('.
					$this->db_add_param($this->oe_kurzbz).','.
					$this->db_add_param($this->oe_parent_kurzbz).','.
					$this->db_add_param($this->bezeichnung).','.
					$this->db_add_param($this->organisationseinheittyp_kurzbz).','.
					$this->db_add_param($this->aktiv, FHC_BOOLEAN).','.
					$this->db_add_param($this->mailverteiler, FHC_BOOLEAN).','.
					$this->db_add_param($this->lehre, FHC_BOOLEAN).');';
		}
		else 
		{
			if($this->oe_kurzbz=='')
			{
				$this->errormsg = 'Kurzbezeichnung darf nicht leer sein';
				return false;
			}
			
			if($this->oe_kurzbz_orig=='')
			{
				$this->oe_kurzbz_orig=$this->oe_kurzbz;
			}
			
			$qry = 'UPDATE public.tbl_organisationseinheit SET '.
					' oe_kurzbz='.$this->db_add_param($this->oe_kurzbz).','.
					' oe_parent_kurzbz='.$this->db_add_param($this->oe_parent_kurzbz).','.
					' bezeichnung='.$this->db_add_param($this->bezeichnung).','.
					' organisationseinheittyp_kurzbz='.$this->db_add_param($this->organisationseinheittyp_kurzbz).','.
					' aktiv='.$this->db_add_param($this->aktiv, FHC_BOOLEAN).','.
					' mailverteiler='.$this->db_add_param($this->mailverteiler, FHC_BOOLEAN).','.
					' lehre='.$this->db_add_param($this->lehre, FHC_BOOLEAN).
					" WHERE oe_kurzbz=".$this->db_add_param($this->oe_kurzbz_orig, FHC_STRING, false).";";
		}
		
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Organisationseinheit';
			return false;
		}
	}
	
	/**
	 * Laedt alle Organisationseinheittypen
	 *
	 * @return boolean
	 */
	public function getTypen()
	{
		$qry = "SELECT * FROM public.tbl_organisationseinheittyp ORDER BY bezeichnung";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new organisationseinheit();
				
				$obj->organisationseinheittyp_kurzbz = $row->organisationseinheittyp_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->beschreibung = $row->beschreibung;
				
				$this->result[] = $obj;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Typen';
			return false;
		}
	}
	
	/**
	 * Laedt die Organisationseinheiten die als Array uebergeben werden
	 * @param $kurzbzs Array mit den kurzbezeichnungen
	 * @param $order Sortierreihenfolge
	 * @param $aktiv wenn true dann nur aktive sonst alle
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadArray($kurzbzs, $order=null, $aktiv=true)
	{
		if(count($kurzbzs)==0)
			return true;
		
		$kurzbzs = $this->db_implode4SQL($kurzbzs);
						
		$qry = 'SELECT * FROM public.tbl_organisationseinheit WHERE oe_kurzbz in('.$kurzbzs.')';
		if ($aktiv)
			$qry.=' AND aktiv=true';

		if($order!=null)
		 	$qry .=" ORDER BY $order";

		if(!$result = $this->db_query($qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		} 

		while($row = $this->db_fetch_object($result))
		{
			$obj = new organisationseinheit();
			
			$obj->oe_kurzbz = $row->oe_kurzbz;
			$obj->oe_parent_kurzbz = $row->oe_parent_kurzbz;
			$obj->bezeichnung = $row->bezeichnung;
			$obj->organisationseinheittyp_kurzbz = $row->organisationseinheittyp_kurzbz;
			$obj->aktiv = $this->db_parse_bool($row->aktiv);
			$obj->mailverteiler = $this->db_parse_bool($row->mailverteiler);
			$obj->lehre = $this->db_parse_bool($row->lehre);
			
			$this->result[] = $obj;
		}

		return true;
	}
	
	/**
	 * Laedt die Organisationseinheiten in ein Array
	 * das Array enthaelt danach Key alle Organisationseinheiten und als Value dessen Parent OE
	 */
	public function loadParentsArray()
	{
		$qry = 'SELECT * FROM public.tbl_organisationseinheit';
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				organisationseinheit::$oe_parents_array[$row->oe_kurzbz]=$row->oe_parent_kurzbz;
			}
		}
	}
	
	/**
	 * Liefert die OEs die im Tree ueberhalb der uebergebene OE liegen
	 * 
	 * @param $oe_kurzbz
	 */
	public function getParents($oe_kurzbz)
	{
		$parents=array();
		
		$qry="WITH RECURSIVE oes(oe_kurzbz, oe_parent_kurzbz) as 
		(
			SELECT oe_kurzbz, oe_parent_kurzbz FROM public.tbl_organisationseinheit 
			WHERE oe_kurzbz=".$this->db_add_param($oe_kurzbz)." and aktiv = true 
			UNION ALL
			SELECT o.oe_kurzbz, o.oe_parent_kurzbz FROM public.tbl_organisationseinheit o, oes 
			WHERE o.oe_kurzbz=oes.oe_parent_kurzbz and aktiv = true
		)
		SELECT oe_kurzbz
		FROM oes";
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$parents[]=$row->oe_kurzbz;
			}
			return $parents;
		}
		else
		{
			$this->errormsg='Fehler beim Laden der Daten';
			return false;
		}		
	}
	
	/**
	 * Prueft ob $child eine Organisationseinheit unterhalb der OE $oe_kurzbz ist
	 *
	 * @param $oe_kurzbz parent organisationseinheit
	 * @param $child child organisationseinheit
	 * @return true wenn child, false wenn nicht
	 */
	public function isChild($oe_kurzbz, $child)
	{
		if(count(organisationseinheit::$oe_parents_array)<=0)
		{
			$this->loadParentsArray();
		}
		
		if(!isset(organisationseinheit::$oe_parents_array[$child]))
		{
			$this->errormsg = 'Organisationseinheit existiert nicht';
			return false;
		}
		
		$childs = array_keys(organisationseinheit::$oe_parents_array, $oe_kurzbz);

		foreach ($childs as $row)
		{
			if($row==$child)
			{
				return true;
			}
			else
			{ 
				if($this->isChild($row, $child))
					return true;
			}
		}
		
		return false;		
	}
	
	/**
	 * Baut die Datenstruktur für senden als JSON Objekt auf
	 */
	public function cleanResult()
	{
		$data = array();
		if(count($this->result)>0)
		{
			foreach($this->result as $oeEinheit)
			{
				$obj = new stdClass();
				$obj->oe_kurzbz = $oeEinheit->oe_kurzbz;
				$obj->oe_parent_kurzbz = $oeEinheit->oe_parent_kurzbz;
				$obj->bezeichnung = $oeEinheit->bezeichnung;
				$obj->organisationseinheittyp_kurzbz = $oeEinheit->organisationseinheittyp_kurzbz;
				$obj->aktiv = $oeEinheit->aktiv;
				$obj->mailverteiler = $oeEinheit->mailverteiler;
				$obj->lehre = $oeEinheit->lehre;
				$data[]=$obj;
			}
		} 
		else 
		{
			$obj = new stdClass();
			$obj->oe_kurzbz = $this->oe_kurzbz;
			$obj->oe_parent_kurzbz = $this->oe_parent_kurzbz;
			$obj->bezeichnung = $this->bezeichnung;
			$obj->organisationseinheittyp_kurzbz = $this->organisationseinheittyp_kurzbz;
			$obj->aktiv = $this->aktiv;
			$obj->mailverteiler = $this->mailverteiler;
			$obj->lehre = $this->lehre;
			$data[]=$obj;
		}
		return $data;
	}
	
	/**
	 * Lädt Organisationseinheiten nach ihrem Typ
	 * @param type $oetyp_kurzbz
	 * @return boolean true, wenn ok; false, im Fehlerfall
	 */
	public function getByTyp($oetyp_kurzbz)
	{
	    $qry = 'SELECT * FROM public.tbl_organisationseinheit WHERE organisationseinheittyp_kurzbz='.$this->db_add_param($oetyp_kurzbz).';';
	    
	    if($this->db_query($qry))
	    {
		while($row = $this->db_fetch_object())
		{
		    $obj = new organisationseinheit();

		    $obj->oe_kurzbz = $row->oe_kurzbz;
		    $obj->oe_parent_kurzbz = $row->oe_parent_kurzbz;
		    $obj->bezeichnung = $row->bezeichnung;
		    $obj->organisationseinheittyp_kurzbz = $row->organisationseinheittyp_kurzbz;
		    $obj->aktiv = $this->db_parse_bool($row->aktiv);
		    $obj->mailverteiler = $this->db_parse_bool($row->mailverteiler);
		    $obj->lehre = $this->db_parse_bool($row->lehre);

		    $this->result[] = $obj;
		}
		return true;
	    }
	    else 
	    {
		$this->errormsg = 'Fehler beim Laden der Organisationseinheiten';
		return false;
	    }
	}
}
?>
