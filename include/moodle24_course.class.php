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
 *                     'fhcomplete_get_course_grades'
 */
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/moodle.class.php');

class moodle24_course extends basis_db
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
		$moodle = new moodle();
		$pfad = $moodle->getPfad('2.4');
		$this->serverurl=$pfad.'/webservice/soap/server.php?wsdl=1&wstoken='.MOODLE_TOKEN24.'&'.microtime(true);
		return true;
	}
	
	/**
	 * Laedt einen MoodleKurs
	 * @param mdl_course_id ID des Moodle Kurses
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($mdl_course_id)
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
		$response = $client->core_course_get_courses(array('ids'=>array($this->mdl_course_id)));

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
											studiensemester_kurzbz, insertamum, insertvon, gruppen, moodle_version)
				VALUES('.
				$this->db_add_param($this->mdl_course_id, FHC_INTEGER).','.
				$this->db_add_param($this->lehreinheit_id, FHC_INTEGER).','.
				$this->db_add_param($this->lehrveranstaltung_id, FHC_INTEGER).','.
				$this->db_add_param($this->studiensemester_kurzbz).','.
				$this->db_add_param($this->insertamum).','.
				$this->db_add_param($this->insertvon).','.
				$this->db_add_param($this->gruppen, FHC_BOOLEAN).", '2.4');";

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

		return true;
	}
		
	/**
	 * Laedt die ID einer Kurskategorie anhand der Bezeichnung und der ParentID
	 *
	 * @param bezeichnung Bezeichnung der Kategorie
	 * @param parent ID der uebergeordneten Kurskategorie
	 * 
	 * @return id der Kategorie oder false im Fehlerfall
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
			$this->errormsg = 'Fehler beim Laden der Kurskategorie';
			return false;
		}
	}
	
	/**
	 * Erzeugt eine Kurskategorie anhand der Bezeichnung und der ParentID
	 * @param bezeichnung Bezeichnung der Kategorie
	 * @param parent ID der uebergeordneten Kategorie
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
	 * Aktualisiert die Spalte gruppen in der tbl_moodle
	 * @param moodle_id ID der MoodleZuteilung
	 *        gruppen boolean true wenn syncronisiert 
	 *                werden soll, false wenn nicht
	 * @return true wenn ok, false im Fehlerfall
	 *
	 * TODO eventuell auslagern in moodle.class
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
			{
				$this->errormsg= "Fehler beim Anlegen der Testkurskategorie";
				return false;
			}
		}
		//StSem Categorie holen
		if(!$id_stsem = $this->getCategorie($studiensemester_kurzbz, $id_testkurs))
		{
			if(!$id_stsem = $this->createCategorie($studiensemester_kurzbz, $id_testkurs))
			{
				$this->errormsg = 'Fehler beim Anlegen der Studiensemester Kategorie';
				return false;
			}
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
	 * @param lehrveranstaltung_id
	 * @param $studiensemester_kurzbz
	 *        
	 * @return objekt mit den Noten der Teilnehmer dieses Kurses
	 *
	 * TODO Anpassung an Moodle 2.4 fertigstellen
	 */
	public function loadNoten($lehrveranstaltung_id, $studiensemester_kurzbz)
	{
		$this->errormsg='';		
		$this->result=null;	
					
		if($lehrveranstaltung_id=='' || $studiensemester_kurzbz=='')
		{
			$this->errormsg = 'LehrveranstaltungID und Studiensemester_kurzbz muss uebergeben werden';
			return false;
		}

		// Ermitteln die Lehreinheiten und Moodle ID
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
			
		
		// Von der Lehreinheit kann der Moodle-Kurs ermittelt werden
		$this->mdl_course_id=trim($_lehreinheit_kpl[$row->lehreinheit_id]->mdl_course_id);
		if ($last_moodle_id==$this->mdl_course_id)	
			continue;
		$last_moodle_id=$this->mdl_course_id;


		$client = new SoapClient($serverurl); 
		$response = $client->fhcomplete_get_course_grades($this->mdl_course_id);

		if (count($response)>0) 	
		{

			foreach($response as $row)
			{

				$userobj = new stdClass();
				$userobj->vorname = $row->vorname;
				$userobj->nachname = $row->nachname;
				$userobj->idnummer = $row->idnummer;
				$userobj->username = $row->username;
				$userobj->note = $row->note;

				$this->result[]=$obj;
			}	
			return true;						
		}		
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Moodle Noten';
			return false;
		}		
	}
	

	/**
	 * Loescht einen Moodle Course im Moodel und in der DB
	 * @param mdl_course_id
	 * @param bServerinfo Detail xmlrpc Debug informationen
	 *        
	 * @return objekt mit den Noten der Teilnehmer dieses Kurses
	 *
	 * TODO anpassung moodle 2.4 eventuell Trennung in moodle.class
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
