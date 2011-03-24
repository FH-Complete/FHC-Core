<?php
/* Copyright (C) 2011 FH Technikum-Wien
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class statistik extends basis_db
{
	public $new;
	public $statistik_obj=array();
	public $result=array();

	public $statistik_kurzbz;
	public $content_id;
	public $bezeichnung;
	public $url;
	public $sql;
	public $php;
	public $r;
	public $gruppe;
	public $insertamum;
	public $insertvon;
	public $updateamum;
	public $udpatevon;
	public $berechtigung_kurzbz;
				
	public $studiengang_kz;		// integer
	public $prestudent_id;		// integer
	public $geschlecht;			// char(1)
	public $studiensemester_kurzbz;// varchar(16)
	public $ausbildungssemester;// smallint
	
	/**
	 * Konstruktor
	 */
	public function __construct($studiengang_kz=null)
	{
		parent::__construct();		
	}

	/**
	 * Laedt eine Statistik
	 * @param $statistik_kurzbz
	 */
	public function load($statistik_kurzbz)
	{
		$qry = "SELECT 
					*
				FROM
					public.tbl_statistik
				WHERE
					statistik_kurzbz='".addslashes($statistik_kurzbz)."'";
		
		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->statistik_kurzbz = $row->statistik_kurzbz;
				$this->content_id = $row->content_id;
				$this->bezeichnung = $row->bezeichnung;
				$this->url = $row->url;
				$this->sql = $row->sql;
				$this->php = $row->php;
				$this->r = $row->r;
				$this->gruppe = $row->gruppe;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->udpatevon = $row->updatevon;
				$this->berechtigung_kurzbz = $row->berechtigung_kurzbz;
				
				return true;
			}
			else
			{
				$this->errormsg = 'Dieser Eintrag wurde nicht gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}			
	}
	
	/**
	 * Laedt alle Statistiken
	 * @return true wenn ok, sonst false
	 */
	public function getAll()
	{
		$qry = 'SELECT * FROM public.tbl_statistik';
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new statistik();
				
				$obj->statistik_kurzbz = $row->statistik_kurzbz;
				$obj->content_id = $row->content_id;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->url = $row->url;
				$obj->sql = $row->sql;
				$obj->php = $row->php;
				$obj->r = $row->r;
				$obj->gruppe = $row->gruppe;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->udpatevon = $row->updatevon;
				$obj->berechtigung_kurzbz = $row->berechtigung_kurzbz;
				
				$this->result[] = $obj;
			}
			
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}			
	}
	
	/**
	 * Speichert einen Statistik Datensatz
	 * @param $new boolean
	 * @return boolean true wenn ok false im Fehlerfalls
	 */
	public function save($new=null)
	{
		if(is_null($new))
			$new = $this->new;
			
		if($new)
		{
			$qry = 'INSERT INTO public.tbl_statistik(statistik_kurzbz, content_id, bezeichnung, url, sql, 
					php, r, gruppe, insertamum, insertvon, updateamum, updatevon, berechtigung_kurzbz) VALUES('.
					$this->addslashes($this->statistik_kurzbz).','.
					$this->addslashes($this->content_id).','.
					$this->addslashes($this->bezeichnung).','.
					$this->addslashes($this->url).','.
					$this->addslashes($this->sql).','.
					$this->addslashes($this->php).','.
					$this->addslashes($this->r).','.
					$this->addslashes($this->gruppe).','.
					$this->addslashes($this->insertamum).','.
					$this->addslashes($this->insertvon).','.
					$this->addslashes($this->updateamum).','.
					$this->addslashes($this->updatevon).','.
					$this->addslashes($this->berechtigung_kurzbz).');';
		}
		else
		{
			if($this->statistik_kurzbz_orig=='')
				$this->statistik_kurzbz_orig=$this->statistik_kurzbz;
			$qry = 'UPDATE public.tbl_statistik SET
				content_id='.$this->addslashes($this->content_id).','.
				' bezeichnung='.$this->addslashes($this->bezeichnung).','.
				' statistik_kurzbz='.$this->addslashes($this->statistik_kurzbz).','.
				' url='.$this->addslashes($this->url).','.
				' sql='.$this->addslashes($this->sql).','.
				' php='.$this->addslashes($this->php).','.
				' r='.$this->addslashes($this->r).','.
				' gruppe='.$this->addslashes($this->gruppe).','.
				' insertamum='.$this->addslashes($this->insertamum).','.
				' insertvon='.$this->addslashes($this->insertvon).','.
				' updateamum='.$this->addslashes($this->updateamum).','.
				' updatevon='.$this->addslashes($this->updatevon).','.
				' berechtigung_kurzbz='.$this->addslashes($this->berechtigung_kurzbz).
				" WHERE statistik_kurzbz='".addslashes($this->statistik_kurzbz_orig)."'";
		}
		
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg='Fehler beim Speichern der Daten';
			return false;
		}
	}
	
	/**
	 * Liefert ein Array mit den Menueeintraegen der Statistiken
	 * Mit dem Returnwert dieser Funktion wird die entsprechende Stelle im
	 * Menue ueberschrieben  
	 * @return Array fuer Menue
	 */
	public function getMenueArray()
	{
		$arr = array();
				
		$qry = "SELECT 
					*
				FROM
					public.tbl_statistik
				ORDER BY gruppe, bezeichnung, statistik_kurzbz";
			
		if($result = $this->db_query($qry))
		{
			$lastgruppe='';
			while($row = $this->db_fetch_object($result))
			{
				if($row->gruppe!='' && $row->gruppe!=$lastgruppe)
				{
					$arr[$row->gruppe]=array('name'=>$row->gruppe);
					$lastgruppe=$row->gruppe;
				}
				if($row->gruppe!='')
				{
					$arr[$row->gruppe][$row->statistik_kurzbz]=array('name'=>$row->bezeichnung, 'link'=>APP_ROOT.'vilesci/statistik/statistik_frameset.php?statistik_kurzbz='.$row->statistik_kurzbz, 'target'=>'main');
					if($row->berechtigung_kurzbz!='')
						$arr[$row->gruppe][$row->statistik_kurzbz]['permissions']=array($row->berechtigung_kurzbz);
				}
				else
				{
					$arr[$row->statistik_kurzbz]=array('name'=>$row->bezeichnung, 'link'=>APP_ROOT.'vilesci/statistik/statistik_frameset.php?statistik_kurzbz='.$row->statistik_kurzbz, 'target'=>'main');
					if($row->berechtigung_kurzbz!='')
						$arr[$row->statistik_kurzbz]['permissions']=array($row->berechtigung_kurzbz);					
				}
			}
		}
		return $arr; 
	}
	
	/**
	 * Loescht einen Eintrag
	 *
	 * @param $statistik_kurzbz
	 * @return true wenn ok, sonst false
	 */
	public function delete($statistik_kurzbz)
	{
		$qry = "DELETE FROM public.tbl_statistik WHERE statistik_kurzbz='".addslashes($statistik_kurzbz)."';";
		
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg='Fehler beim Löschen des Eintrages';
			return false;
		}
	}
	
	
	
	/**
	 * Laedt bestimmte PreStudenten
	 * @param studiengang_kz KZ des Studienganges der zu Laden ist
	 * @param studiensemester_kurzbz Studiensemester
	 * @param ausbildungssemester KZ Ausbildungssemester
	 * @param datum_stichtag Stichtag im ISO-Format, Ergebniss filtert auf <= (kleiner,gleich)
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function get_prestudenten($studiengang_kz, $studiensemester_kurzbz, $ausbildungssemester=null, $datum_stichtag=null)
	{
		if(!is_numeric($studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}

		if($ausbildungssemester!='' && !is_numeric($ausbildungssemester))
		{
			$this->errormsg = 'Ausbildungssemester muss eine gueltige Zahl sein';
			return false;
		}
		
		// Neue Studenten ermitteln
		$qry="
			SELECT 
				DISTINCT prestudent_id, geschlecht, studiengang_kz, ausbildungssemester, studiensemester_kurzbz
			FROM 
				public.tbl_prestudent 
				JOIN public.tbl_prestudentstatus status USING (prestudent_id) 
				JOIN public.tbl_person USING (person_id) 
			WHERE 
				status_kurzbz='Student'
				AND NOT EXISTS(SELECT 1 FROM public.tbl_prestudentstatus WHERE status_kurzbz='Student' AND datum<status.datum AND prestudent_id=status.prestudent_id) 
				AND studiengang_kz=".$studiengang_kz;
		if($ausbildungssemester!='')
			$qry.="	AND ausbildungssemester=".$ausbildungssemester;
		
		$qry.="	AND ((studiensemester_kurzbz='".$studiensemester_kurzbz."'";
		if (!is_null($datum_stichtag))
			$qry.="	AND datum <='".$datum_stichtag."'";
		$qry.=') ';
		$qry.=" OR (studiensemester_kurzbz='".$studiensemester_kurzbz."'";
		if (!is_null($datum_stichtag))
			$qry.="	AND datum <='".$datum_stichtag."'";
		$qry.="))";
		$qry.=" ORDER BY prestudent_id;";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$stat_obj = new statistik();
				$stat_obj->studiengang_kz=$row->studiengang_kz;
				$stat_obj->ausbildungssemester=$row->ausbildungssemester;
				$stat_obj->prestudent_id=$row->prestudent_id;
				$stat_obj->geschlecht=$row->geschlecht;
				$stat_obj->studiensemester_kurzbz=$row->studiensemester_kurzbz;
				$this->statistik_obj[]=$stat_obj;
			}
		}
		else
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		return true;
	}
	
	/**
	 * 
	 * Liefert die DropOut Rate
	 * @param unknown_type $studiengang_kz
	 * @param unknown_type $studiensemester_kurzbz
	 * @param unknown_type $ausbildungssemester
	 * @param unknown_type $datum_stichtag
	 */
	public function get_DropOut($studiengang_kz, $studiensemester_kurzbz, $ausbildungssemester=null, $datum_stichtag=null)
	{
		$this->statistik_obj=array();
		
		if(!is_numeric($studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}

		if($ausbildungssemester!='' && !is_numeric($ausbildungssemester))
		{
			$this->errormsg = 'Ausbildungssemester muss eine gueltige Zahl sein';
			return false;
		}
		
		// Neue Studenten ermitteln
		$qry="SELECT DISTINCT prestudent_id, geschlecht, studiengang_kz, ausbildungssemester, studiensemester_kurzbz
			FROM tbl_prestudent JOIN tbl_prestudentstatus USING (prestudent_id) JOIN tbl_person USING (person_id) 
			WHERE (status_kurzbz='Abbrecher') 
			AND studiengang_kz=".$studiengang_kz;
		if($ausbildungssemester!='')
			$qry.="	AND ausbildungssemester=".$ausbildungssemester;
		
		$qry.="	AND (studiensemester_kurzbz='".$studiensemester_kurzbz."'";
		if (!is_null($datum_stichtag))
			$qry.="	AND datum <='".$datum_stichtag."'";
		$qry.=') ';
		$qry.=" ORDER BY prestudent_id;";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$stat_obj = new statistik();
				$stat_obj->studiengang_kz=$row->studiengang_kz;
				$stat_obj->ausbildungssemester=$row->ausbildungssemester;
				$stat_obj->prestudent_id=$row->prestudent_id;
				$stat_obj->geschlecht=$row->geschlecht;
				$stat_obj->studiensemester_kurzbz=$row->studiensemester_kurzbz;
				$this->statistik_obj[]=$stat_obj;
			}
		}
		else
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}
		
		return true;
	}
}
?>