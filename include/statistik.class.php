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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class statistik extends basis_db
{
	public $new;      			// boolean
	public $statistik_obj=array();// Statistik Objekt

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