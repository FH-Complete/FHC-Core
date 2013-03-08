<?php
/* Copyright (C) 2013 FH Technikum-Wien
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
/*
 * Moodle 2.4 Connector Klasse
 *
 * FHComplete Moodle Plugin muss installiert sein fuer
 * Webservice Funktion 'fhcomplete_courses_by_shortname' 
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class moodle_course extends basis_db
{
	public $result = array();
	public $serverurl;

	//Vilesci Attribute
	public $moodle_id;
	public $mdl_course_id;
	public $lehreinheit_id;
	public $lehrveranstaltung_id;
	public $studiensemester_kurzbz;
	public $insertamum;
	public $insertvon;
	public $gruppen;
	
	//Moodle Attribute
	public $mdl_fullname;
	public $mdl_shortname;
	

	//DEPRECATED ?
	/*
	public $mdl_context_id;
	public $mdl_context_level;
	public $mdl_context_instanceid;
	public $mdl_context_path;	
	public $mdl_context_depth;
	*/

	public $lehrveranstaltung_bezeichnung;
	public $lehrveranstaltung_semester;			
	public	$lehrveranstaltung_studiengang_kz;

	// Kurs Resourcen - Anzahl 	
	public	$mdl_benotungen;
	public	$mdl_resource;
	public	$mdl_quiz;
	public	$mdl_chat;
	public	$mdl_forum;
	public	$mdl_choice;
		
	public $note;

	/**
	 * Konstruktor
	 * 
	 */
	public function __construct()
	{			
		$this->serverurl=MOODLE_PATH24.'/webservice/soap/server.php?wsdl=1&wstoken='.MOODLE_TOKEN24.'&'.microtime(true);
		return true;
	}
	
	/**
	 * Laedt einen MoodleKurs
	 * @param mdl_course_id ID des Moodle Kurses
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($mdl_course_id=null)
	{
		$this->mdl_fullname = '';
		$this->mdl_shortname = '';
			
		$this->errormsg='';
		$this->result=array();
			
		if (!is_null($mdl_course_id))
			$this->mdl_course_id=$mdl_course_id;
		if (is_null($this->mdl_course_id) 
			|| empty($this->mdl_course_id) 
			|| !is_numeric($this->mdl_course_id))
		{
			$this->errormsg='Moodle Kurs ID fehlt';
			return false;
		}		
		
		$client = new SoapClient($this->serverurl); 
		$response = $client->core_course_get_courses(array($this->mdl_course_id));

		if($response)
		{
			if(isset($response[0]))
			{
				$this->mdl_fullname = $response[0]['fullname'];
				$this->mdl_shortname = $response[0]['shortname'];
				$this->mdl_course_id = $response[0]['id'];
				return true;
			}
			else 
			{
				$this->errormsg = 'Kurs wurde nicht gefunden';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden des Kurses';
			return false;
		}
	}
	
	/**
	 * Laedt einen MoodleKurs
	 * @param mdl_course_id ID des Moodle Kurses
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadMoodle($mdl_course_id=null)
	{
		return $this->load($mdl_course_id);
	}

	/**
	 * Laedt alle MoodleKurse die zu einer LV/Stsem
	 * plus die MoodleKurse die auf dessen LE haengen
	 * @param lehrveranstaltung_id
	 *        studiensemester_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAll($lehrveranstaltung_id, $studiensemester_kurzbz)
	{
		$qry = "SELECT 
					distinct on(mdl_course_id) * 
				FROM 
					lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_moodle 
				WHERE
				tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
					tbl_lehrveranstaltung.lehrveranstaltung_id = ".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER)." AND
					tbl_lehreinheit.studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)." AND
					((tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_moodle.lehrveranstaltung_id AND tbl_moodle.studiensemester_kurzbz=tbl_lehreinheit.studiensemester_kurzbz)
					OR
					 (tbl_lehreinheit.lehreinheit_id=tbl_moodle.lehreinheit_id)
					)";
					
		if($result=$this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new moodle_course();
				
				$obj->moodle_id = $row->moodle_id;
				$obj->mdl_course_id = $row->mdl_course_id;
				$obj->lehreinheit_id = $row->lehreinheit_id;
				$obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->gruppen = $this->db_parse_bool($row->gruppen);

				$client = new SoapClient($this->serverurl);
				$response = $client->core_course_get_courses(array('ids'=>array($row->mdl_course_id)));

				if($response)
				{
					if(isset($response[0]))
					{
						$obj->mdl_fullname = $response[0]['fullname'];
						$obj->mdl_shortname = $response[0]['shortname'];
						$obj->mdl_course_id = $response[0]['id'];
					}
				}
		
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
	 * Laedt alle MoodleKurse die zu einer LV/Stsem
	 * plus die MoodleKurse die auf dessen LE haengen
	 * @param lehrveranstaltung_id
	 *        studiensemester_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAllVariant($lehrveranstaltung_id='',$studiensemester_kurzbz='',$studiengang='',$semester='',$detail=false)
	{
			// Initialisierung
			$this->errormsg = '';
			$this->result=array();
				
			$qry = "SELECT distinct tbl_lehreinheit.studiensemester_kurzbz,tbl_lehrveranstaltung.semester
						,tbl_lehrveranstaltung.bezeichnung,tbl_lehrveranstaltung.kurzbz,tbl_lehrveranstaltung.lehrveranstaltung_id,tbl_lehrveranstaltung.studiengang_kz,tbl_lehrveranstaltung.semester 
						,tbl_moodle.mdl_course_id
					FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit,lehre.tbl_moodle
					where tbl_lehreinheit.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id 
					 and ((tbl_moodle.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id
					 		and tbl_moodle.studiensemester_kurzbz=lehre.tbl_lehreinheit.studiensemester_kurzbz)
					  OR
						 (tbl_moodle.lehreinheit_id=tbl_lehreinheit.lehreinheit_id))";
		
		if ($lehrveranstaltung_id!='')
			$qry.=" and tbl_lehrveranstaltung.lehrveranstaltung_id='".addslashes($lehrveranstaltung_id)."' ";

		if ($studiensemester_kurzbz!='')
			$qry.=" and tbl_lehreinheit.studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."' ";

		if ($studiengang!='')
			$qry.=" and tbl_lehrveranstaltung.studiengang_kz='".addslashes($studiengang)."' ";

		if ($semester!='')
			$qry.=" and tbl_lehrveranstaltung.semester='".addslashes($semester)."' ";

		$qry.=";";					
					
		if(!$result = $this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}

		while($row = $this->db_fetch_object($result))
		{
			$obj = new moodle_course($this->conn_moodle);

			$obj->mdl_course_id = $row->mdl_course_id;
			$obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
			$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
			$obj->lehrveranstaltung_kurzbz=$row->kurzbz;
			
			$obj->lehrveranstaltung_bezeichnung=$row->bezeichnung;
			$obj->lehrveranstaltung_semester=$row->semester;			
			$obj->lehrveranstaltung_studiengang_kz=$row->studiengang_kz;

			$obj->mdl_fullname = 'DB fehler ID '.$obj->mdl_course_id;
			$obj->mdl_shortname =$obj->mdl_fullname;
			
			// Anzahl Benotungen
			$obj->mdl_benotungen = 0;
			// Anzahl Aktivitaeten und Lehrmaterial
			$obj->mdl_resource = 0;
			$obj->mdl_quiz = 0;
			$obj->mdl_chat = 0;
			$obj->mdl_forum = 0;
			$obj->mdl_choice= 0;			
			
			$moddle= new moodle_course();
			if ($moddle->load($obj->mdl_course_id))
			{
				$obj->mdl_fullname = $moddle->mdl_fullname;
				$obj->mdl_shortname = $moddle->mdl_shortname;
			}
			else
			{
				$obj->mdl_fullname =$moddle->errormsg; 
				$obj->mdl_course_id = 0;
				$this->result[] = $obj;
				continue;
			}

			if(!$detail)
			{
				$this->result[] = $obj;
				continue;
			}
			
			// Anzahl Noten je Kurs und User			
			$qry_mdl = "SELECT count(*) as anz
				FROM mdl_grade_grades , mdl_grade_items
				WHERE mdl_grade_items.itemtype='course'
				AND mdl_grade_grades.finalgrade IS NOT NULL 
				AND mdl_grade_grades.itemid=mdl_grade_items.id
				AND mdl_grade_items.courseid ='".addslashes($row->mdl_course_id)."'; ";

			if($detail && $result_mdl = pg_query($this->conn_moodle, $qry_mdl))
			{
				if($row_mdl = pg_fetch_object($result_mdl))
				{
					$obj->mdl_benotungen = (empty($row_mdl->anz)?0:$row_mdl->anz);
				}
			}					
			
			
			$qry_mdl = "SELECT count(course) as anz FROM public.mdl_chat WHERE mdl_chat.course='".addslashes($row->mdl_course_id)."'; ";
			if($detail && $result_mdl = pg_query($this->conn_moodle, $qry_mdl))
			{
				if($row_mdl = pg_fetch_object($result_mdl))
				{
					$obj->mdl_chat = (empty($row_mdl->anz)?0:$row_mdl->anz);
				}
			}					

			$qry_mdl = "SELECT count(course) as anz FROM public.mdl_resource WHERE mdl_resource.course='".addslashes($row->mdl_course_id)."'; ";
			if($detail && $result_mdl = pg_query($this->conn_moodle, $qry_mdl))
			{
				if($row_mdl = pg_fetch_object($result_mdl))
				{
					$obj->mdl_resource = (empty($row_mdl->anz)?0:$row_mdl->anz);
				}
			}					

			
			$qry_mdl = "SELECT count(course) as anz FROM public.mdl_quiz WHERE mdl_quiz.course='".addslashes($row->mdl_course_id)."'; ";
			if($detail && $result_mdl = pg_query($this->conn_moodle, $qry_mdl))
			{
				if($row_mdl = pg_fetch_object($result_mdl))
				{
					$obj->mdl_quiz = (empty($row_mdl->anz)?0:$row_mdl->anz);
				}
			}					

			$qry_mdl = "SELECT count(course) as anz FROM public.mdl_forum WHERE mdl_forum.course='".addslashes($row->mdl_course_id)."'; ";
			if($detail && $result_mdl = pg_query($this->conn_moodle, $qry_mdl))
			{
				if($row_mdl = pg_fetch_object($result_mdl))
				{
					$obj->mdl_forum = (empty($row_mdl->anz)?0:$row_mdl->anz);
				}
			}					
			
			$qry_mdl = "SELECT count(course) as anz FROM public.mdl_choice WHERE mdl_choice.course='".addslashes($row->mdl_course_id)."'; ";
			if($detail && $result_mdl = pg_query($this->conn_moodle, $qry_mdl))
			{
				if($row_mdl = pg_fetch_object($result_mdl))
				{
					$obj->mdl_choice = (empty($row_mdl->anz)?0:$row_mdl->anz);
				}
			}					

			
			$this->result[] = $obj;
		}
		return true;
	}

	/**
	 * Laedt alle MoodleKurse die zu einer LV/Stsem
	 * plus die MoodleKurse die auf dessen LE haengen
	 * @param lehrveranstaltung_id
	 *        studiensemester_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAllMoodleVariant($mdl_course_id='',$lehrveranstaltung_id='',$studiensemester_kurzbz='',$lehreinheit_id='',$studiengang='',$semester='',$detail=false,$lehre=true,$aktiv=true)
	{
		// Initialisierung
		$this->errormsg = '';
		$this->result=array();

/*		$qry = "SELECT distinct tbl_moodle.lehrveranstaltung_id as moodle_lehrveranstaltung_id,tbl_moodle.lehreinheit_id as moodle_lehreinheit_id, tbl_moodle.studiensemester_kurzbz,tbl_lehrveranstaltung.semester
						,tbl_lehrveranstaltung.bezeichnung,tbl_lehrveranstaltung.kurzbz,tbl_lehrveranstaltung.lehrveranstaltung_id,tbl_lehrveranstaltung.studiengang_kz,tbl_lehrveranstaltung.semester 
						,tbl_moodle.mdl_course_id,tbl_moodle.lehreinheit_id,tbl_moodle.gruppen
					FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit,lehre.tbl_moodle
					where tbl_lehreinheit.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id 
					 and ((tbl_moodle.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id
						and tbl_moodle.studiensemester_kurzbz=lehre.tbl_lehreinheit.studiensemester_kurzbz)
					  OR
						 (tbl_moodle.lehreinheit_id=tbl_lehreinheit.lehreinheit_id))";
*/
		$where='';
		if ($mdl_course_id!='')
			$where.=" and tbl_moodle.mdl_course_id='".addslashes($mdl_course_id)."' ";
		
		if ($lehreinheit_id!='')
			$where.=" and tbl_lehreinheit.lehreinheit_id='".addslashes($lehreinheit_id)."' ";

		if ($lehrveranstaltung_id!='')
			$where.=" and tbl_lehrveranstaltung.lehrveranstaltung_id='".addslashes($lehrveranstaltung_id)."' ";

		if ($studiensemester_kurzbz!='')
			$where.=" and tbl_lehreinheit.studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."' ";

		if ($studiengang!='')
			$where.=" and tbl_lehrveranstaltung.studiengang_kz='".addslashes($studiengang)."' ";

		if ($semester!='')
			$where.=" and tbl_lehrveranstaltung.semester='".addslashes($semester)."' ";

		if ($lehre)
			$where.=" and tbl_lehrveranstaltung.lehre ";

		if ($aktiv)
			$where.=" and tbl_lehrveranstaltung.aktiv ";

		$qry ='';
		$qry.=' SELECT distinct tbl_moodle.studiensemester_kurzbz
		,tbl_lehrveranstaltung.studiengang_kz
		,tbl_lehrveranstaltung.semester
		,tbl_moodle.mdl_course_id 
		,tbl_lehrveranstaltung.lehrveranstaltung_id
		,tbl_moodle.lehreinheit_id  as moodle_lehreinheit_id 
		,tbl_moodle.lehrveranstaltung_id as moodle_lehrveranstaltung_id
		,tbl_moodle.lehreinheit_id as lehreinheit_id,tbl_lehrveranstaltung.bezeichnung,tbl_lehrveranstaltung.kurzbz,tbl_moodle.gruppen
		,tbl_lehrveranstaltung.lehrform_kurzbz,tbl_lehrveranstaltung.orgform_kurzbz
		,tbl_moodle.moodle_id
		 FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit,lehre.tbl_moodle 
		
		where tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_moodle.lehrveranstaltung_id
		and tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_moodle.lehrveranstaltung_id
		and tbl_moodle.studiensemester_kurzbz=tbl_lehreinheit.studiensemester_kurzbz
		and tbl_moodle.lehreinheit_id is null 
		';
		$qry.=$where;
		
		$qry.=' UNION ';
		$qry.=' SELECT distinct tbl_moodle.studiensemester_kurzbz
		,tbl_lehrveranstaltung.studiengang_kz
		,tbl_lehrveranstaltung.semester
		,tbl_moodle.mdl_course_id
		,tbl_lehrveranstaltung.lehrveranstaltung_id
		,tbl_moodle.lehreinheit_id as moodle_lehreinheit_id
		,tbl_moodle.lehrveranstaltung_id as moodle_lehrveranstaltung_id
		,tbl_moodle.lehreinheit_id as lehreinheit_id,tbl_lehrveranstaltung.bezeichnung,tbl_lehrveranstaltung.kurzbz,tbl_moodle.gruppen
		,tbl_lehrveranstaltung.lehrform_kurzbz,tbl_lehrveranstaltung.orgform_kurzbz
		,tbl_moodle.moodle_id
		 FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit,lehre.tbl_moodle 
		
		where tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id
		and tbl_moodle.lehreinheit_id=tbl_lehreinheit.lehreinheit_id
		and tbl_moodle.lehrveranstaltung_id is null 
		';
		$qry.=$where;
		
		$qry.=' order by 1,2,3,4,5,6,7;	';

		if(!$result = $this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}

		while($row = $this->db_fetch_object($result))
		{
		
			$obj = new moodle_course($this->conn_moodle);

			$obj->mdl_course_id = $row->mdl_course_id;
			$obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
			$obj->lehreinheit_id = $row->lehreinheit_id;
			$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
			$obj->lehrveranstaltung_kurzbz=$row->kurzbz;
			$obj->lehrveranstaltung_bezeichnung=$row->bezeichnung;
			$obj->lehrveranstaltung_semester=$row->semester;			
			$obj->lehrveranstaltung_studiengang_kz=$row->studiengang_kz;
			$obj->lehrveranstaltung_lehrform_kurzbz=$row->lehrform_kurzbz;

			$obj->lehrveranstaltung_orgform_kurzbz=$row->orgform_kurzbz;

			$obj->moodle_lehrveranstaltung_id=$row->moodle_lehrveranstaltung_id;			
			$obj->moodle_lehreinheit_id=$row->moodle_lehreinheit_id;
			$obj->moodle_mdl_course_id = $row->mdl_course_id;			
			$obj->mdl_fullname = 'Moodle Kurs nicht vorhanden ID '.$obj->mdl_course_id;
			$obj->mdl_shortname =$obj->mdl_fullname;
			$obj->gruppen=($row->gruppen=='t'?true:false);;
			
			// Anzahl Benotungen
			$obj->mdl_benotungen = 0;
			// Anzahl Aktivitaeten und Lehrmaterial
			$obj->mdl_resource = 0;
			$obj->mdl_quiz = 0;
			$obj->mdl_chat = 0;
			$obj->mdl_forum = 0;
			$obj->mdl_choice= 0;			
			
			$moddle= new moodle_course();
			if ($moddle->load($obj->mdl_course_id))
			{
				$obj->mdl_fullname = $moddle->mdl_fullname;
				$obj->mdl_shortname = $moddle->mdl_shortname;
			}
			else
			{
				$obj->mdl_course_id = 0;
				$obj->mdl_fullname =$moddle->errormsg; 
				$this->result[] = $obj;
				continue;
			}

			if(!$detail)
			{
				$this->result[] = $obj;
				continue;
			}
			
						
			// Anzahl Noten je Kurs und User			
			$qry_mdl = "SELECT count(*) as anz
				FROM mdl_grade_grades , mdl_grade_items
				WHERE mdl_grade_items.itemtype='course'
				AND mdl_grade_grades.finalgrade IS NOT NULL 
				AND mdl_grade_grades.itemid=mdl_grade_items.id
				AND mdl_grade_items.courseid ='".addslashes($row->mdl_course_id)."'; ";

			if($detail && $result_mdl = pg_query($this->conn_moodle, $qry_mdl))
			{
				if($row_mdl = pg_fetch_object($result_mdl))
				{
					$obj->mdl_benotungen = (empty($row_mdl->anz)?0:$row_mdl->anz);
				}
			}					
			
			$qry_mdl = "SELECT count(course) as anz FROM public.mdl_chat WHERE mdl_chat.course='".addslashes($row->mdl_course_id)."'; ";
			if($detail && $result_mdl = pg_query($this->conn_moodle, $qry_mdl))
			{
				if($row_mdl = pg_fetch_object($result_mdl))
				{
					$obj->mdl_chat = (empty($row_mdl->anz)?0:$row_mdl->anz);
				}
			}					

			$qry_mdl = "SELECT count(course) as anz FROM public.mdl_resource WHERE mdl_resource.course='".addslashes($row->mdl_course_id)."'; ";
			if($detail && $result_mdl = pg_query($this->conn_moodle, $qry_mdl))
			{
				if($row_mdl = pg_fetch_object($result_mdl))
				{
					$obj->mdl_resource = (empty($row_mdl->anz)?0:$row_mdl->anz);
				}
			}					

			
			$qry_mdl = "SELECT count(course) as anz FROM public.mdl_quiz WHERE mdl_quiz.course='".addslashes($row->mdl_course_id)."'; ";
			if($detail && $result_mdl = pg_query($this->conn_moodle, $qry_mdl))
			{
				if($row_mdl = pg_fetch_object($result_mdl))
				{
					$obj->mdl_quiz = (empty($row_mdl->anz)?0:$row_mdl->anz);
				}
			}					

			$qry_mdl = "SELECT count(course) as anz FROM public.mdl_forum WHERE mdl_forum.course='".addslashes($row->mdl_course_id)."'; ";
			if($detail && $result_mdl = pg_query($this->conn_moodle, $qry_mdl))
			{
				if($row_mdl = pg_fetch_object($result_mdl))
				{
					$obj->mdl_forum = (empty($row_mdl->anz)?0:$row_mdl->anz);
				}
			}					
			
			$qry_mdl = "SELECT count(course) as anz FROM public.mdl_choice WHERE mdl_choice.course='".addslashes($row->mdl_course_id)."'; ";
			if($detail && $result_mdl = pg_query($this->conn_moodle, $qry_mdl))
			{
				if($row_mdl = pg_fetch_object($result_mdl))
				{
					$obj->mdl_choice = (empty($row_mdl->anz)?0:$row_mdl->anz);
				}
			}					
			$this->result[] = $obj;
		}
		return true;
					
	}
	
	/**
	 * Schaut ob fuer diese LV/StSem schon ein 
	 * Moodle Kurs existiert
	 * @param lehrveranstaltung_id
	 *        studiensemester_kurzbz
	 * @return true wenn vorhanden, false wenn nicht
	 */
	public function course_exists_for_lv($lehrveranstaltung_id, $studiensemester_kurzbz)
	{
		$qry = "SELECT 
					1 
				FROM 
					lehre.tbl_moodle 
				WHERE 
					lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER)." 
					AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);
		if($this->db_query($qry))
		{
			if($this->db_num_rows()>0)
				return true;	
			else 
				return false;
		}
		else
		{
			$this->errormsg = 'Fehler bei Datenbankabfrage';
			return false;
		}
	}
	
	/**
	 * Schaut ob fuer diese LE schon ein Moodle
	 * Kurs existiert
	 * @param lehreinheit_id
	 * @return true wenn vorhanden, false wenn nicht
	 */
	public function course_exists_for_le($lehreinheit_id)
	{
		$qry = "SELECT 1 FROM lehre.tbl_moodle WHERE lehreinheit_id=".$this->db_add_param($lehreinheit_id, FHC_INTEGER);
		if($this->db_query($qry))
		{
			if($this->db_num_rows()>0)
				return true;
			else 
				return false;
		}
		else
		{
			$this->errormsg = 'Fehler bei Datenbankabfrage';
			return false;
		}
	}
	
	/**
	 * Schaut ob fuer diese LV/StSem schon ein 
	 * Moodle Kurs existiert
	 * @param lehrveranstaltung_id
	 *        studiensemester_kurzbz
	 * @return true wenn vorhanden, false wenn nicht
	 */
	public function course_exists_for_allLE($lehrveranstaltung_id, $studiensemester_kurzbz)
	{
		$qry = "SELECT 1 FROM lehre.tbl_lehreinheit 
				WHERE lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER)."
				AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)."
				AND lehreinheit_id NOT IN (SELECT lehreinheit_id FROM lehre.tbl_moodle WHERE lehreinheit_id=tbl_lehreinheit.lehreinheit_id)";
		if($this->db_query($qry))
		{
			if($this->db_num_rows()>0)
				return false;
			else 
				return true;
		}
		else
		{
			$this->errormsg = 'Fehler bei Datenbankabfrage';
			return false;
		}
	}
		
	/**
	 * Legt einen Eintrag in der tbl_moodle an
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function create_vilesci()
	{
		if($this->mdl_course_id=='')
		{
			$this->errormsg='mdl_course_id muss angegeben sein';
			return false;
		}
		
		$qry = 'BEGIN; INSERT INTO lehre.tbl_moodle(mdl_course_id, lehreinheit_id, lehrveranstaltung_id, 
											studiensemester_kurzbz, insertamum, insertvon, gruppen)
				VALUES('.
				$this->db_add_param($this->mdl_course_id, FHC_INTEGER).','.
				$this->db_add_param($this->lehreinheit_id, FHC_INTEGER).','.
				$this->db_add_param($this->lehrveranstaltung_id, FHC_INTEGER).','.
				$this->db_add_param($this->studiensemester_kurzbz).','.
				$this->db_add_param($this->insertamum).','.
				$this->db_add_param($this->insertvon).','.
				$this->db_add_param($this->gruppen, FHC_BOOLEAN).');';

		if($this->db_query($qry))
		{
			$qry = "SELECT currval('lehre.tbl_moodle_moodle_id_seq') as id;";
			if($this->db_query($qry))
			{
				if($row = $this->db_fetch_object())
				{
					$this->moodle_id = $row->id;
					$this->db_query('COMMIT;');
					return true;
				}
				else 
				{
					$this->db_query('ROLLBACK');
					$this->errormsg = 'Fehler beim Lesen der Sequence';
					return false;
				}				
			}
			else 
			{
					$this->db_query('ROLLBACK');
					$this->errormsg = 'Fehler beim Lesen der Sequence';
					return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Einfuegen des Datensatzes';
			return false;
		}
	}

	/**
	 * Entfernt einen Eintrag in der tbl_moodle an
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete_vilesci($mdl_course_id=null,$lehrveranstaltung_id=null,$lehreinheit_id=null)
	{
		$this->errormsg = '';
		if (!is_null($mdl_course_id) && !empty($mdl_course_id))
			$this->mdl_course_id=$mdl_course_id;
		if (!is_null($lehrveranstaltung_id) && !empty($lehrveranstaltung_id))
			$this->lehrveranstaltung_id=$lehrveranstaltung_id;
		if (!is_null($lehreinheit_id) && !empty($lehreinheit_id))
			$this->lehreinheit_id=$lehreinheit_id;
		$where='';
		if (!is_null($this->mdl_course_id) && !empty($this->mdl_course_id))
			$where.=($where?' and ':' where '). ' mdl_course_id='.$this->db_add_param($this->mdl_course_id, FHC_INTEGER);
		else			
			$where.=($where?' and ':' where '). ' mdl_course_id=0';	
		if (!is_null($this->lehrveranstaltung_id) && !empty($this->lehrveranstaltung_id))
			$where.=($where?' and ':' where '). ' lehrveranstaltung_id='.$this->db_add_param($this->lehrveranstaltung_id, FHC_INTEGER);	
		if (!is_null($this->lehreinheit_id) && !empty($this->lehreinheit_id))
			$where.=($where?' and ':' where '). ' lehreinheit_id='.$this->db_add_param($this->lehreinheit_id, FHC_INTEGER);	
		if (empty($where))
		{
			$this->errormsg='mdl_course_id oder LV oder LE muss angegeben sein';
			return false;	
		}	

		$qry='DELETE FROM lehre.tbl_moodle '.$where;
		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Loeschen der Zuteilung';
			return false;
		}	

		return true;
	}

	/**
	 * Aendert einen Eintrag in der tbl_moodle an
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function update_vilesci()
	{
		if($this->mdl_course_id=='')
		{
			$this->errormsg='mdl_course_id muss angegeben sein';
			return false;
		}
		if (is_null($this->new) || empty($this->new))
			$this->new=false;
			
		$this->db_query('BEGIN;');			
		$qry = '';
		$res=0;
		
		if (!$this->new)
		{
			$qrySel = 'SELECT 1 FROM lehre.tbl_moodle WHERE mdl_course_id='.$this->db_add_param($this->mdl_course_id, FHC_INTEGER);
			if(!$res=$this->db_query($qrySel))
			{
				$this->errormsg = 'Fehler beim Datenbankzugriff';
				return false;
			}
			if($this->db_num_rows($res)>0)
			{
				if ($this->lehrveranstaltung_id!='' && !is_null($this->lehrveranstaltung_id))
				{
					$qry.= 'DELETE FROM lehre.tbl_moodle WHERE mdl_course_id='.$this->db_add_param($this->mdl_course_id, FHC_INTEGER).' and not lehreinheit_id = '.$this->db_add_param($this->lehrveranstaltung_id, FHC_INTEGER) .';';
				}
				else
				{
					$qry.= 'DELETE FROM lehre.tbl_moodle WHERE mdl_course_id='.$this->db_add_param($this->mdl_course_id, FHC_INTEGER).' and not lehreinheit_id in ('. (is_array($this->lehreinheit_id)? implode(',',$this->lehreinheit_id) :$this->lehreinheit_id) .');';
				}
			}
		}	

		if ( ($this->lehrveranstaltung_id!='' && !is_null($this->lehrveranstaltung_id))
		|| !is_array($this->lehreinheit_id)  )
		{
			$qrySel = 'SELECT 1 FROM lehre.tbl_moodle WHERE mdl_course_id='.$this->db_add_param($this->mdl_course_id, FHC_INTEGER);
			if ($this->new)
			{
				if ( $this->lehrveranstaltung_id!='' && !is_null($this->lehrveranstaltung_id) )
					$qrySel.= ' and lehrveranstaltung_id='.$this->db_add_param($this->lehrveranstaltung_id, FHC_INTEGER);
				else
					$qrySel.= ' and lehreinheit_id='.$this->db_add_param($this->lehreinheit_id, FHC_INTEGER);
			}
			
			if(!$res=$this->db_query($qrySel))
			{
				$this->errormsg = 'Fehler beim Datenbankzugriff';
				$this->db_query('ROLLBACK');
				return false;

			}
			if($this->db_num_rows($res)>0)
			{
				$qry.= 'UPDATE lehre.tbl_moodle SET
					 lehreinheit_id='.$this->db_add_param($this->lehreinheit_id, FHC_INTEGER).',
					 lehrveranstaltung_id='.$this->db_add_param($this->lehrveranstaltung_id, FHC_INTEGER).',
					 studiensemester_kurzbz='.$this->db_add_param($this->studiensemester_kurzbz).' 
					 ';
				if (!is_null($this->gruppen))
					$qry.= ',gruppen='.$this->db_add_param($this->gruppen, FHC_BOOLEAN);
					$qry.= '  WHERE mdl_course_id='.$this->db_add_param($this->mdl_course_id, FHC_INTEGER, false).'; ';
			}
			else 
			{
				$qry.= 'INSERT INTO lehre.tbl_moodle(mdl_course_id, lehreinheit_id, lehrveranstaltung_id, 
												studiensemester_kurzbz, insertamum, insertvon, gruppen)
					VALUES('.
					$this->db_add_param($this->mdl_course_id, FHC_INTEGER).','.
					$this->db_add_param($this->lehreinheit_id, FHC_INTEGER).','.
					$this->db_add_param($this->lehrveranstaltung_id, FHC_INTEGER).','.
					$this->db_add_param($this->studiensemester_kurzbz).','.
					$this->db_add_param($this->insertamum).','.
					$this->db_add_param($this->insertvon).','.
					$this->db_add_param($this->gruppen, FHC_BOOLEAN).'); ';
			}
		}
		// Lehreinheiten anlegen - Array
		else
		{
			foreach ($this->lehreinheit_id as $key=>$value)
			{
				$qrySel = 'SELECT 1 FROM lehre.tbl_moodle WHERE mdl_course_id='.$this->db_add_param($this->mdl_course_id). ' AND lehreinheit_id='.$this->db_add_param($value, FHC_INTEGER);
				if(!$res=$this->db_query($qrySel))
				{
					$this->errormsg = 'Fehler beiDatenbank abfrage';			
					$this->db_query('ROLLBACK');		
					return false;
				}
				if($this->db_num_rows($res)>0)
				{
					$qry.= 'UPDATE lehre.tbl_moodle SET
							 lehrveranstaltung_id='.$this->db_add_param($this->lehrveranstaltung_id, FHC_INTEGER).',
							 studiensemester_kurzbz='.$this->db_add_param($this->studiensemester_kurzbz).' 
							 ';
					if (!is_null($this->gruppen))
						$qry.= ',gruppen='.$this->db_add_param($this->gruppen, FHC_BOOLEAN);
					$qry.= ' WHERE mdl_course_id='.$this->db_add_param($this->mdl_course_id, FHC_INTEGER);	
					$qry.= ' AND lehreinheit_id='.$this->db_add_param($value, FHC_INTEGER).'; ';
							
				}
				else 
				{
					$qry.= 'INSERT INTO lehre.tbl_moodle(mdl_course_id, lehreinheit_id, lehrveranstaltung_id, 
													studiensemester_kurzbz, insertamum, insertvon, gruppen)
						VALUES('.
						$this->db_add_param($this->mdl_course_id, FHC_INTEGER).','.
						$this->db_add_param($value, FHC_INTEGER).','.
						$this->db_add_param($this->lehrveranstaltung_id, FHC_INTEGER).','.
						$this->db_add_param($this->studiensemester_kurzbz).','.
						$this->db_add_param($this->insertamum).','.
						$this->db_add_param($this->insertvon).','.
						$this->db_add_param($this->gruppen, FHC_BOOLEAN).'); ';
				}
			}
		}
		
		if(!$this->db_query($qry))
		{
			$this->db_query('ROLLBACK');		
			$this->errormsg = 'Fehler beim Aendern des Datensatzes! ';
			return false;
		}

		$this->db_query('COMMIT;');
		return true;

	}

	/**
	 * Aendert einen Kurs im Moodle an
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function update_moodle($oldPath = null)
	{
		if($this->mdl_course_id=='')
		{
			$this->errormsg='mdl_course_id muss angegeben sein';
			return false;
		}	
		
		if( (is_null($this->lehrveranstaltung_id) || $this->lehrveranstaltung_id=='') 
		&& (is_null($this->lehreinheit_id) && $this->lehreinheit_id==''))
		{
			$this->errormsg='LvID oder LeID muss uebergeben werden';
			return false;
		}	
				
		pg_query($this->conn_moodle, 'BEGIN;');
		
		//CourseCategorie ermitteln
		
		//lehrveranstalung ID holen falls die nur die lehreinheit_id angegeben wurde
		if($this->lehrveranstaltung_id=='' || is_null($this->lehrveranstaltung_id))
		{
			$qry = "SELECT lehrveranstaltung_id FROM lehre.tbl_lehreinheit 
					WHERE lehreinheit_id='".addslashes($this->lehreinheit_id)."'";
			if($res=$this->db_query($qry))
			{
				if($row = $this->db_fetch_object($res))
				{
					$lvid = $row->lehrveranstaltung_id;
				}
				else 
				{
					pg_query($this->conn_moodle, 'ROLLBACK');
					$this->errormsg = 'Fehler beim Ermitteln der LehrveranstaltungID';
					return false;
				}
			}
			else 
			{
				pg_query($this->conn_moodle, 'ROLLBACK');
				$this->errormsg = 'Fehler beim Ermitteln der LehrveranstaltungID';
				return false;
			}
		}
		else 
			$lvid = $this->lehrveranstaltung_id;
		
		//Studiengang und Semester holen
		$qry = "SELECT tbl_lehrveranstaltung.semester, UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as stg 
				FROM lehre.tbl_lehrveranstaltung JOIN public.tbl_studiengang USING(studiengang_kz)
				WHERE lehrveranstaltung_id='$lvid'";
		
		if($res=$this->db_query($qry))
		{
			if($row = $this->db_fetch_object($res))
			{
				$semester = $row->semester;
				$stg = $row->stg;
			}
			else 
			{
				pg_query($this->conn_moodle, 'ROLLBACK');
				$this->errormsg = 'Fehler beim Ermitteln von Studiengang und Semester';
				return false;
			}
		}
		else 
		{
			pg_query($this->conn_moodle, 'ROLLBACK');
			$this->errormsg = 'Fehler beim Ermitteln von Studiengang und Semester';
			return false;
		}
	
		//Studiensemester Categorie holen
		if(!$id_stsem = $this->getCategorie($this->studiensemester_kurzbz, '0'))
		{
			if(!$id_stsem = $this->createCategorie($this->studiensemester_kurzbz, '0'))
				echo "<br>Fehler beim Anlegen des Studiensemesters";
		}

		//Studiengang Categorie holen
		if(!$id_stg = $this->getCategorie($stg, $id_stsem))
		{
			if(!$id_stg = $this->createCategorie($stg, $id_stsem))
				echo "<br>$this->errormsg";
		}

		//Semester Categorie holen
		if(!$id_sem = $this->getCategorie($semester, $id_stg))
		{
			if(!$id_sem = $this->createCategorie($semester, $id_stg))
				echo "<br>$this->errormsg";
		}
		
		//CourseCategorie Context holen
		$this->getContext(40, $id_sem);


		$qry = 'UPDATE public.mdl_course set 
					category='.$this->addslashes($id_sem).',
					fullname='. $this->addslashes($this->mdl_fullname) .',
					shortname='.$this->addslashes($this->mdl_shortname).'
		';
		$qry.= " WHERE id='".addslashes($this->mdl_course_id)."'; ";	
				
#echo $qry;
#return true;

		if(!$result = pg_query($this->conn_moodle, $qry))
		{
			pg_query($this->conn_moodle, 'ROLLBACK');
			$this->errormsg = 'Fehler beim Update';
			return false;
		}

		/*
		$qry = "DELETE FROM public.mdl_context where contextlevel='50' and instanceid=".$this->addslashes($this->mdl_course_id)." ;";
		if(!pg_query($this->conn_moodle, $qry))
		{
				pg_query($this->conn_moodle, 'ROLLBACK');
				$this->errormsg = 'Fehler beim Entfernen des Context eintrages.  '. pg_last_error();
				return false;
		}		
		*/	

		$update=false;
		$qry = "SELECT id FROM public.mdl_context WHERE contextlevel='50' and instanceid=".$this->addslashes($this->mdl_course_id)." ;";
		if($result = pg_query($this->conn_moodle, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->mdl_context_id = $row->id;
				$update=true;
			}
		}
		
		if($update)
		{
			//zum vorherigen Pfad die aktuelle id hinzufuegen
			$path = $this->mdl_context_path.'/'.$this->mdl_context_id;
			//vorherige tiefe um 1 erhoehen
			$depth = $this->mdl_context_depth+1;
			
			$qry = "UPDATE public.mdl_context SET 
				contextlevel=50,
				instanceid=".$this->addslashes($this->mdl_course_id).",
				path=".$this->addslashes($path).",
				depth=".$this->addslashes($depth)."
				WHERE id='".addslashes($this->mdl_context_id)."';";
			if(!pg_query($this->conn_moodle, $qry))
			{
				pg_query($this->conn_moodle, 'ROLLBACK');
				$this->errormsg = 'Fehler beim Update des Contexts';
				return false;
			} 
			
			$qry = "UPDATE public.mdl_context SET 
				path=".$this->addslashes($path)."|| '/' || mdl_context.id
				WHERE path LIKE '".$oldPath."%';";
			if(!pg_query($this->conn_moodle, $qry))
			{
				pg_query($this->conn_moodle, 'ROLLBACK');
				$this->errormsg = 'Fehler beim Update des Contexts';
				return false;
			}
		}
		else
		{
			$qry ="SELECT nextval('mdl_context_id_seq') as nextId";
			if($result = pg_query($this->conn_moodle, $qry))
			{
				if($row=pg_fetch_object($result))
				{
					// nächste id herausfinden -> wegen insert
					$path = $this->mdl_context_path.'/'.$row->nextId;
					// tiefe um 1 erhoehen
					$depth = $this->mdl_context_depth+1;
				}
				else
				{
					$this->errormsg = 'Fehler beim Select der Sequence :'. pg_last_error();
					return false;
				}	
			}
			else 
			{	
				$this->errormsg = 'Fehler beim Select der Sequence :'. pg_last_error();
				return false;
			}
			
			//Context eintragen
			$qry = "INSERT INTO public.mdl_context(contextlevel, instanceid, path, depth) VALUES('50', ".
			$this->addslashes($this->mdl_course_id).",'".$this->addslashes($path)."',".$this->addslashes($depth).");";
			if(pg_query($this->conn_moodle, $qry))
			{
				$qry = "SELECT currval('mdl_context_id_seq') as id";
				if($result = pg_query($this->conn_moodle, $qry))
				{
					if($row = pg_fetch_object($result))
					{
						$this->mdl_context_id = $row->id;
					}
					else 
					{
						pg_query($this->conn_moodle, 'ROLLBACK');
						$this->errormsg = 'Fehler beim Auslesen der Sequence ::'. pg_last_error($result).' '. pg_last_error();
						return false;
					}
				}
				else 
				{	
					pg_query($this->conn_moodle, 'ROLLBACK');
					$this->errormsg = 'Fehler beim Select der Sequence :'. pg_last_error();
					return false;
				}
			}
			else 
			{
				pg_query($this->conn_moodle, 'ROLLBACK');
				$this->errormsg = 'Fehler beim INSERT';
				return false;
			}
		}
				
		
		pg_query($this->conn_moodle, 'COMMIT;');
		return true;
	}

	
	
	/**
	 * Legt einen Kurs im Moodle an
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function create_moodle()
	{
		//CourseCategorie ermitteln
		
		//lehrveranstalung ID holen falls die nur die lehreinheit_id angegeben wurde
		if($this->lehrveranstaltung_id=='')
		{
			$qry = "SELECT lehrveranstaltung_id FROM lehre.tbl_lehreinheit 
					WHERE lehreinheit_id=".$this->db_add_param($this->lehreinheit_id, FHC_INTEGER);
			if($res=$this->db_query($qry))
			{
				if($row = $this->db_fetch_object($res))
				{
					$lvid = $row->lehrveranstaltung_id;
				}
				else 
				{
					$this->errormsg = 'Fehler beim Ermitteln der LehrveranstaltungID';
					return false;
				}
			}
			else 
			{
				$this->errormsg = 'Fehler beim Ermitteln der LehrveranstaltungID';
				return false;
			}
		}
		else 
			$lvid = $this->lehrveranstaltung_id;
		
		//Studiengang und Semester holen
		$qry = "SELECT tbl_lehrveranstaltung.semester, UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as stg 
				FROM lehre.tbl_lehrveranstaltung JOIN public.tbl_studiengang USING(studiengang_kz)
				WHERE lehrveranstaltung_id=".$this->db_add_param($lvid, FHC_INTEGER);
		
		if($res=$this->db_query($qry))
		{
			if($row = $this->db_fetch_object($res))
			{
				$semester = $row->semester;
				$stg = $row->stg;
			}
			else 
			{
				$this->errormsg = 'Fehler beim Ermitteln von Studiengang und Semester';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Ermitteln von Studiengang und Semester';
			return false;
		}
		
		//Studiensemester Categorie holen
		if(!$id_stsem = $this->getCategorie($this->studiensemester_kurzbz, '0'))
		{
			if(!$id_stsem = $this->createCategorie($this->studiensemester_kurzbz, '0'))
				echo "<br>Fehler beim Anlegen des Studiensemesters";
		}
		//Studiengang Categorie holen
		if(!$id_stg = $this->getCategorie($stg, $id_stsem))
		{
			if(!$id_stg = $this->createCategorie($stg, $id_stsem))
				echo "<br>$this->errormsg";
		}
		//Semester Categorie holen
		if(!$id_sem = $this->getCategorie($semester, $id_stg))
		{
			if(!$id_sem = $this->createCategorie($semester, $id_stg))
				echo "<br>$this->errormsg";
		}
		
		
		$client = new SoapClient($this->serverurl); 

		$data = new stdClass();
		$data->fullname=$this->mdl_fullname;
		$data->shortname=$this->mdl_shortname;
		$data->categoryid=$id_sem;
		$data->format='topics';

		$response = $client->core_course_create_courses(array($data));
		if(isset($response[0]))
		{
			$this->mdl_course_id=$response[0]['id'];
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Anlegen des Kurses';
			return false;
		}		
				
		//Bloecke hinzufuegen
		/* TODO
		
		$qry = 
		"INSERT INTO public.mdl_block_instance(blockid, pageid, pagetype, position, weight, visible) VALUES(20, $this->mdl_course_id, 'course-view', 'l', 0, 1);". //Teilnehmer
		"INSERT INTO public.mdl_block_instance(blockid, pageid, pagetype, position, weight, visible) VALUES(1, $this->mdl_course_id, 'course-view', 'l', 1, 1);". //Aktivit�ten
		"INSERT INTO public.mdl_block_instance(blockid, pageid, pagetype, position, weight, visible) VALUES(25, $this->mdl_course_id, 'course-view', 'l', 2, 1);". //Forumssuche
		"INSERT INTO public.mdl_block_instance(blockid, pageid, pagetype, position, weight, visible) VALUES(2, $this->mdl_course_id, 'course-view', 'l', 3, 1);". //Admin
		"INSERT INTO public.mdl_block_instance(blockid, pageid, pagetype, position, weight, visible) VALUES(9, $this->mdl_course_id, 'course-view', 'l', 4, 1);". //Kursliste
		"INSERT INTO public.mdl_block_instance(blockid, pageid, pagetype, position, weight, visible) VALUES(18, $this->mdl_course_id, 'course-view', 'r', 0, 1);". //Neueste Nachrichten
		"INSERT INTO public.mdl_block_instance(blockid, pageid, pagetype, position, weight, visible) VALUES(8, $this->mdl_course_id, 'course-view', 'r', 1, 1);". //Kalender / Bald aktuell...
		"INSERT INTO public.mdl_block_instance(blockid, pageid, pagetype, position, weight, visible) VALUES(22, $this->mdl_course_id, 'course-view', 'r', 2, 1);"; //Neueste Aktivit�ten
		if(!pg_query($this->conn_moodle, $qry))
		{
			pg_query($this->conn_moodle, 'ROLLBACK');
			$this->errormsg = 'Fehler beim INSERT der bloecke';
			return false;
		}
		else 
		{
			pg_query($this->conn_moodle, 'COMMIT');
			return true;
		}*/

		return true;
	}
		
	/**
	 * Laedt eine CourseCategorie anhand der Bezeichnung und der
	 * ParentID
	 */
	public function getCategorie($bezeichnung, $parent)
	{
		if($bezeichnung=='')
		{
			$this->errormsg = 'Bezeichnung muss angegeben werden';
			return false;
		}
		if($parent=='')
		{
			$this->errormsg = 'getCategorie: parent wurde nicht uebergeben';
			return false;
		}

		$client = new SoapClient($this->serverurl);
		$response = $client->core_course_get_categories(array(array('key'=>'name','value'=>$bezeichnung),array('key'=>'parent','value'=>$parent)));
		
		if(isset($response[0]))
		{		
			return $response[0]['id'];
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der KursKategorie';
			return false;
		}
	}
	
	/**
	 * Erzeugt eine CourseCategorie anhand der Bezeichnung und der
	 * ParentID
	 */
	public function createCategorie($bezeichnung, $parent)
	{
		if($bezeichnung=='')
		{
			$this->errormsg = 'Bezeichnung muss angegeben werden';
			return false;
		}
		if($parent=='')
		{
			$this->errormsg = 'createCategorie: parent wurde nicht uebergeben';
			return false;
		}

		$client = new SoapClient($this->serverurl); 
		$response = $client->core_course_create_categories(array(array('name'=>$bezeichnung,'parent'=>$parent)));

		if(isset($response[0]))
		{
			return $response[0]['id'];
		}
		else
		{
			$this->errormsg = 'Fehler beim Anlegen der Kategorie';	
			return false;
		}
	}	

	/**
	 * Liefert alle Kurse dieser LV in denen der Student 
	 * zugeteilt ist
	 */
	public function getCourse($lehrveranstaltung_id, $studiensemester_kurzbz, $student_uid)
	{
		//alle betreffenden Kurse holen
		$qry = "SELECT tbl_lehreinheit.lehreinheit_id, mdl_course_id FROM lehre.tbl_moodle JOIN lehre.tbl_lehreinheit USING(lehrveranstaltung_id, studiensemester_kurzbz)
				WHERE tbl_moodle.lehrveranstaltung_id='".addslashes($lehrveranstaltung_id)."' 
				AND tbl_moodle.studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."'
				UNION 
				SELECT tbl_lehreinheit.lehreinheit_id, mdl_course_id FROM lehre.tbl_moodle JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) 
				WHERE tbl_lehreinheit.lehrveranstaltung_id='".addslashes($lehrveranstaltung_id)."' AND 
				tbl_lehreinheit.studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."'";
		
		$courses = array();
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				//schauen in welchen der Student ist
				$qry = "SELECT 1 FROM campus.vw_student_lehrveranstaltung 
						WHERE uid='".addslashes($student_uid)."' AND lehreinheit_id='".addslashes($row->lehreinheit_id)."'";

				if($result_vw = $this->db_query($qry))
				{
					if($this->db_num_rows($result_vw)>0)
					{
						if(!array_key_exists($row->mdl_course_id, $courses))
							$courses[]=$row->mdl_course_id;						
					}
				}
			}
		}
		
		return $courses;
	}
	
	/**
	 * Aktualisiert die Spalte gruppen in der tbl_moodle
	 * @param moodle_id ID der MoodleZuteilung
	 *        gruppen boolean true wenn syncronisiert 
	 *                werden soll, false wenn nicht
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function updateGruppenSync($moodle_id, $gruppen)
	{
		if(!is_numeric($moodle_id))
		{
			$this->errormsg = 'Moodle_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "UPDATE lehre.tbl_moodle SET gruppen=".$this->db_add_param($gruppen, FHC_BOOLEAN)." 
				WHERE moodle_id=".$this->db_add_param($moodle_id, FHC_INTEGER);

		if($this->db_query($qry))
		{
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Update';
			return false;
		}
	}	
	
	/**
	 * Legt einen Testkurs an
	 */
	public function createTestkurs($lehrveranstaltung_id, $studiensemester_kurzbz)
	{		
		//CourseCategorie ermitteln
				
		//Studiengang und Semester holen
		
		$qry = "SELECT 
					tbl_lehrveranstaltung.semester, 
					UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as stg 
				FROM 
					lehre.tbl_lehrveranstaltung 
					JOIN public.tbl_studiengang USING(studiengang_kz)
				WHERE 
					lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER);
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$semester = $row->semester;
				$stg = $row->stg;
			}
			else 
			{
				$this->errormsg = 'Fehler beim Ermitteln von Studiengang und Semester';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Ermitteln von Studiengang und Semester';
			return false;
		}
		
		//Testkurs Categorie holen
		if(!$id_testkurs = $this->getCategorie('Testkurse', '0'))
		{
			if(!$id_testkurs = $this->createCategorie('Testkurse', '0'))
				echo "<br>Fehler beim Anlegen der Testkurskategorie";
		}
		//StSem Categorie holen
		if(!$id_stsem = $this->getCategorie($studiensemester_kurzbz, $id_testkurs))
		{
			if(!$id_stsem = $this->createCategorie($studiensemester_kurzbz, $id_testkurs))
				echo "<br>$this->errormsg";
		}
		
		$client = new SoapClient($this->serverurl); 

		$data = new stdClass();
		$data->fullname=$this->mdl_fullname;
		$data->shortname=$this->mdl_shortname;
		$data->categoryid=$id_stsem;
		$data->format='topics';

		$response = $client->core_course_create_courses(array($data));
		if(isset($response[0]))
		{
			$this->mdl_course_id=$response[0]['id'];
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Anlegen des Testkurses';
			return false;
		}		
	}
	
	/**
	 * Laedt den Testkurs zu dieser Lehrveranstaltung
	 * @param lehrveranstaltung_id
	 *        studiensemester_kurzbz
	 * @return ID wenn gefunden, false wenn nicht vorhanden
	 */
	public function loadTestkurs($lehrveranstaltung_id, $studiensemester_kurzbz)
	{
		$qry = "SELECT
					UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as kuerzel,
					tbl_lehrveranstaltung.semester, tbl_lehrveranstaltung.kurzbz
				FROM
					lehre.tbl_lehrveranstaltung JOIN public.tbl_studiengang USING(studiengang_kz)
				WHERE
					lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER, false);
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$shortname = mb_strtoupper('TK-'.$studiensemester_kurzbz.'-'.$row->kuerzel.'-'.$row->semester.'-'.$row->kurzbz);
			}
			else 
			{
				$this->errormsg = 'Fehler beim Laden des Testkurses';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden des Testkurses';
			return false;
		}
		
		//Testkurs Categorie holen
		if(!$id_testkurs = $this->getCategorie('Testkurse', '0'))
		{
			$this->errormsg = 'Categorie nicht gefunden';
			return false;
		}
		
		//StSem Categorie holen
		if(!$id_stsem = $this->getCategorie($studiensemester_kurzbz, $id_testkurs))
		{
			$this->errormsg = 'Categorie nicht gefunden';
			return false;
		}

		$client = new SoapClient($this->serverurl);
		$response = $client->fhcomplete_courses_by_shortname(array('shortnames'=>array($shortname)));

		if(isset($response[0]))
		{
			$this->mdl_fullname = $response[0]['fullname'];
			$this->mdl_shortname = $response[0]['shortname'];
			$this->mdl_course_id = $response[0]['id'];
			return true;
		}
		else
		{
			$this->errormsg='Es wurde kein Testkurs gefunden';
			return false;
		}
	}

	
	/**
	 * Laedt die Noten zu einem Moodle Course ID
	 * @param mdl_course_id
	 *        
	 * @return objekt mit den Noten der Teilnehmer dieses Kurses
	 */
	public function loadNoten($lehrveranstaltung_id=null, $studiensemester_kurzbz=null,$student_uid='',$bDetailinfo=false,$bServerinfo=false)
	{
	

		$this->errormsg='';		
		$this->result=null;	
	
		// Init
		if (!is_null($lehrveranstaltung_id))
			$this->lehrveranstaltung_id=trim($lehrveranstaltung_id);
		if (!is_null($studiensemester_kurzbz))	
			$this->studiensemester_kurzbz=trim($studiensemester_kurzbz);
		$student_uid=trim($student_uid);


					
		// plausib
		if (empty($this->lehrveranstaltung_id) 
		|| empty($this->studiensemester_kurzbz) )
		{
			$this->errormsg = 'Es fehlt die Eingabe von ';
			$this->errormsg.=(empty($this->lehrveranstaltung_id)?' Lehrveranstaltung ':$this->lehrveranstaltung_id);
			$this->errormsg.=(empty($this->studiensemester_kurzbz)?' Semester (Kurzbz.) ':$this->studiensemester_kurzbz);
			return false;
		}

		// --------------------------------------------------------------------
		// Ermitteln die Lehreinheiten und Moodle ID
		//		mit dem studiensemester_kurzbz ( bsp WS2008 )
		//		und der lehrveranstaltung_id aus FAS ( bsp 23802 )
		// --------------------------------------------------------------------

		$qry = "
		SELECT tbl_lehreinheit.lehreinheit_id, mdl_course_id,tbl_lehreinheit.studiensemester_kurzbz,tbl_lehreinheit.lehrveranstaltung_id
			FROM lehre.tbl_moodle 
			JOIN lehre.tbl_lehreinheit USING(lehrveranstaltung_id, studiensemester_kurzbz)
			WHERE tbl_moodle.lehrveranstaltung_id > 0 ";
		if ($this->lehrveranstaltung_id)
			$qry.= " and tbl_moodle.lehrveranstaltung_id ='".addslashes($this->lehrveranstaltung_id)."' "; 
		if ($this->studiensemester_kurzbz)
			$qry.= " and tbl_moodle.studiensemester_kurzbz ='".addslashes($this->studiensemester_kurzbz)."' "; 
		$qry.= "
		UNION 
			SELECT tbl_lehreinheit.lehreinheit_id, mdl_course_id,tbl_lehreinheit.studiensemester_kurzbz,tbl_lehreinheit.lehrveranstaltung_id
			FROM lehre.tbl_moodle
			JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) 
			WHERE tbl_lehreinheit.lehrveranstaltung_id > 0 ";
		if ($this->lehrveranstaltung_id)
			$qry.= " and tbl_lehreinheit.lehrveranstaltung_id ='".addslashes($this->lehrveranstaltung_id)."' "; 
		if ($this->studiensemester_kurzbz)
			$qry.= " and tbl_moodle.studiensemester_kurzbz ='".addslashes($this->studiensemester_kurzbz)."' "; 

		if(!$result_moodle=$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Lesen der Moodle Kurse , '.$this->errormsg;
			return false;
		}	

		
		// init
		$_lehreinheit=array();	// Lehreinheiten zum lesen Studenten im Campus (Student und LE im FAS) 
		$_lehrveranstaltung = array(); // Gesamte Information der Lehreinheit und Moodle IDs
		$_studiensemester_kurzbz=array();	
		$_lehreinheit_kpl=array();	
		while($row = $this->db_fetch_object($result_moodle))
		{
		
			$row->lehreinheit_id=trim($row->lehreinheit_id);
			$_lehreinheit_kpl[$row->lehreinheit_id]=$row;
			
			$_lehreinheit[$row->lehreinheit_id]=$row->lehreinheit_id; // Fuer Select Campus

			$row->lehrveranstaltung_id=trim($row->lehrveranstaltung_id);
			$_lehrveranstaltung[$row->lehrveranstaltung_id]=$row->lehrveranstaltung_id; // Fuer Select Campus

			$row->studiensemester_kurzbz=trim($row->studiensemester_kurzbz);
			$_studiensemester_kurzbz[$row->studiensemester_kurzbz]=$row->studiensemester_kurzbz; // Fuer Select Campus

		}	
		if (count($_lehreinheit)<1) // Es gibt keine Lehreinheiten
		{
			$this->errormsg='Es wurde kein passender Moodle-Kurs gefunden';
			return false;
		}
		
		// --------------------------------------------------------------------
		//
		// Suchen Studenten Lehreinheiten zu Moodle - LE  
		//		Fuer die Notenermittlung sind nur Studenten wichtig
		//		die einen Moodlekurs besuchen der auch eine Lehrveranstaltung ist
		//	Als Ergebnis sind alle Studenten mit gemeinsame Moodle und FAS  LV
		// --------------------------------------------------------------------
		$qry = "SELECT distinct vw_student_lehrveranstaltung.lehreinheit_id,lehrveranstaltung_id,studiensemester_kurzbz,kurzbz,bezeichnung,semester,studiengang_kz 
			FROM campus.vw_student_lehrveranstaltung 
			";
		$qry.= " WHERE vw_student_lehrveranstaltung.lehreinheit_id in (".implode(",",$_lehreinheit).") ";
		$qry.= " AND lehrveranstaltung_id in (".implode(",",$_lehrveranstaltung).") ";
		$qry.= " AND vw_student_lehrveranstaltung.studiensemester_kurzbz in ('".implode("','",$_studiensemester_kurzbz)."') ";
		if (!empty($student_uid))
			$qry.= " AND uid ='".addslashes($student_uid)."'  ";

		if(!$result_moodle=$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Lesen der Studenten mit Lehreinheit(en) ';
			return false;
		}	
		
		if (!$anz=$this->db_num_rows($result_moodle))
		{
			$this->errormsg ="keine Lehrveranstaltung (Lehreinheit) fuer Moodle Kursdaten gefunden!";
			return false;
		}		
		
		$last_moodle_id=false;
		while($row = $this->db_fetch_object($result_moodle))
		{
		
			// Von der Lehreinheit kann der Moodle-Kurs ermittelt werden
			$this->mdl_course_id=trim($_lehreinheit_kpl[$row->lehreinheit_id]->mdl_course_id);
			if ($last_moodle_id==$this->mdl_course_id)	
				continue;
			$last_moodle_id=$this->mdl_course_id;

			// XML RPC - Call
			$method = "NotenCourseByID";
			
			$m_user=array();
			$m_user['CourseID']=$this->mdl_course_id;
			$mdl_username=trim($student_uid);
			$m_user['UserId']=$mdl_username;
			
			if (!$result=$this->callMoodleXMLRPC($method,$m_user,$bServerinfo)) 
				return false;

			if ($result[0]==1) 	
			{

				$error=(isset($result[1])?$result[1]:"Kurs Info ");
				$kursArr=(isset($result[2])?$result[2]:array());
				$kursasObj=(isset($result[3])?$result[3]:array());
				$userArr=(isset($result[4])?$result[4]:array());
				$userasObj=(isset($result[5])?$result[5]:array());
				$id=(isset($result[6])?$result[6]:'');
				$kursname=(isset($result[7])?$result[7]:'');
				$shortname=(isset($result[8])?$result[8]:'');
				$courseArr=(isset($result[9])?$result[9]:array());
	
				if (!empty($student_uid))
					$note=(isset($userArr) && isset($userArr[6])?$userArr[6]:'?');	
				else
					$note=0;
					
				$obj = new moodle_course($this->conn_moodle);
				
				$obj->mdl_course_id = $this->mdl_course_id;
				$obj->lehreinheit_id=$row->lehreinheit_id;
				
				$obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				
				$obj->lehrveranstaltung_kurzbz=$row->kurzbz;
				$obj->lehrveranstaltung_bezeichnung=$row->bezeichnung;
				$obj->lehrveranstaltung_semester=$row->semester;			
				$obj->lehrveranstaltung_studiengang_kz=$row->studiengang_kz;

				$obj->mdl_fullname=$kursname;
				$obj->mdl_shortname=$shortname;				
				$obj->note=$note;
				
				$obj->errormsg=(isset($result[1])?$result[1]:"");
				$obj->note=$note;

				if ($bDetailinfo || empty($student_uid))
					$obj->result=$result;
				else
					$obj->result=$userArr;
				
				$this->errormsg.=(!empty($this->errormsg)?", \n":"").$obj->errormsg;
				$this->result[]=$obj;
						
			}		
			else 
			{
				$this->errormsg.=(!empty($this->errormsg)?", \n":"").(isset($result[1])?$result[1]:"Fehler Kurs Info ".$this->moodle_id);
			}		
					
		}
		return $this->result;
	}	// Ende moodle Noten 
	

	/**
	 * Loescht einen Moodle Course im Moodel und in der DB
	 * @param mdl_course_id
	 * @param bServerinfo Detail xmlrpc Debug informationen
	 *        
	 * @return objekt mit den Noten der Teilnehmer dieses Kurses
	 */
	public function deleteKurs($mdl_course_id=null,$moodle_id=null,$bServerinfo=false)
	{
		$this->errormsg='';
		$this->result=array();
			
		if (!is_null($mdl_course_id))
			$this->mdl_course_id=$mdl_course_id;


		if (!is_null($moodle_id))
			$this->moodle_id=$moodle_id;

		if (is_null($this->mdl_course_id) || empty($this->mdl_course_id) || !is_numeric($this->mdl_course_id))
		{
			$this->errormsg='Moodle Kurs ID fehlt';
			return false;
		}	

	// Variable Daten Initialisieren
		$args=array();
		$args['CourseID']=$this->mdl_course_id;
		$method = "DeleteCourseByID";		

		if (!$result=$this->callMoodleXMLRPC($method,$args,$bServerinfo)) 
			return false;
			
		if (isset($result[1]))
			$this->errormsg=$result[1];
			
		if ($result[0]==1 || !$this->load($this->mdl_course_id)) // Methodenaufruf erfolgreich	
		{
				$qry = "DELETE FROM lehre.tbl_moodle WHERE mdl_course_id='". addslashes($this->mdl_course_id) ."' ";
				if (!is_null($this->moodle_id) && $this->moodle_id!='')
					$qry.= " and moodle_id='".addslashes($this->moodle_id)."'"; 
				if(!$this->db_query($qry))
				{
						$this->errormsg=$this->errormsg." Moodlekurs $mdl_course_id wurde NICHT gel&ouml;scht in Lehre. ";
						return false;
				}		
		}	
		else // Result = 0 ein Fehler im RFC wurde festgestellt
		{
			$this->errormsg=(isset($result[1])?$result[1]:" - Fehler beim Kurs ".$this->mdl_course_id." l&ouml;schen ");
			return false;
		}		
		
		if (empty($this->errormsg))	
			$this->errormsg.="Moodlekurs ".$this->mdl_course_id." wurde gel&ouml;scht.";	
		return true;	
	
	}		
}
