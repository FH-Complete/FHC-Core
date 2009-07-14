<?php
/* Copyright (C) 2009 Technikum-Wien
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
 */
/**
 * Klasse Organisationseinheit
 * 
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class organisationseinheit extends basis_db
{
	public $new;     			// @var boolean
	public $errormsg; 			// @var string
	public $result;
	
	//Tabellenspalten
	public $oe_kurzbz;
	public $oe_parent_kurzbz;
	public $bezeichnung;
	public $organisationseinheittyp_kurzbz;
	
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


	public function getAll()
	{
		$qry = "SELECT * FROM public.tbl_organisationseinheit ORDER BY organisationseinheittyp_kurzbz, oe_kurzbz";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new organisationseinheit();
				
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->oe_parent_kurzbz = $row->oe_parent_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->organisationseinheittyp_kurzbz = $row->organisationseinheittyp_kurzbz;
				
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

		$qry = "SELECT * FROM public.tbl_organisationseinheit WHERE oe_kurzbz = '$oe_kurzbz';";

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
	 * @return Array mit den Childs inkl derm Uebergebenen Element
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
}
?>