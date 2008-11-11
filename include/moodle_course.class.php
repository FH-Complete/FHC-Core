<?php
/* Copyright (C) 2006 Technikum-Wien
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
/*
 *
 */
class moodle_course
{
	var $conn;
	var $conn_moodle;
	var $errormsg;
	var $result = array();
	
	//Vilesci Attribute
	var $moodle_id;
	var $mdl_course_id;
	var $lehreinheit_id;
	var $lehrveranstaltung_id;
	var $studiensemester_kurzbz;
	var $insertamum;
	var $insertvon;
	var $gruppen;
	
	//Moodle Attribute
	var $mdl_fullname;
	var $mdl_shortname;
	
	var $mdl_context_id;
	var $mdl_context_level;
	var $mdl_context_instanceid;
	var $mdl_context_path;	
	var $mdl_context_depth;
	
	var $note;

	// **********************************************
	// * moodle_course
	// * @param $conn Connection zur Vilesci DB
	// *        $conn_moodle Connection zur Moodle DB
	// **********************************************
	function moodle_course($conn, $conn_moodle)
	{
		$this->conn = $conn;
		$this->conn_moodle = $conn_moodle;
		
		$qry = "SET CLIENT_ENCODING TO 'LATIN9';";
		pg_query($this->conn_moodle, $qry);
	}
	
	// **********************************************
	// * Laedt einen MoodleKurs
	// * @param mdl_course_id ID des Moodle Kurses
	// * @return true wenn ok, false im Fehlerfall
	// **********************************************
	function load($mdl_course_id)
	{
		$qry = "SELECT * FROM public.mdl_course WHERE id='".addslashes($mdl_course_id)."'";
		if($result = pg_query($this->conn_moodle, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->mdl_fullname = $row->fullname;
				$this->mdl_shortname = $row->shortname;
				$this->mdl_course_id = $row->id;
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

	// **********************************************
	// * Laedt alle MoodleKurse die zu einer LV/Stsem
	// * plus die MoodleKurse die auf dessen LE haengen
	// * @param lehrveranstaltung_id
	// *        studiensemester_kurzbz
	// * @return true wenn ok, false im Fehlerfall
	// **********************************************
	function getAll($lehrveranstaltung_id, $studiensemester_kurzbz)
	{
		$qry = "SELECT distinct on(mdl_course_id) * 
				FROM 
					lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_moodle 
				WHERE
					tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
					tbl_lehrveranstaltung.lehrveranstaltung_id = '".addslashes($lehrveranstaltung_id)."' AND
					tbl_lehreinheit.studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."' AND
					((tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_moodle.lehrveranstaltung_id AND tbl_moodle.studiensemester_kurzbz=tbl_lehreinheit.studiensemester_kurzbz)
					OR
					 (tbl_lehreinheit.lehreinheit_id=tbl_moodle.lehreinheit_id)
					)";
					
		if($result = pg_query($this->conn, $qry))
		{
			while($row=pg_fetch_object($result))
			{
				$obj = new moodle_course($this->conn, $this->conn_moodle);
				$obj->moodle_id = $row->moodle_id;
				$obj->mdl_course_id = $row->mdl_course_id;
				$obj->lehreinheit_id = $row->lehreinheit_id;
				$obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->gruppen = ($row->gruppen=='t'?true:false);

				$qry_mdl = "SELECT * FROM public.mdl_course WHERE id='".addslashes($row->mdl_course_id)."'";
				if($result_mdl = pg_query($this->conn_moodle, $qry_mdl))
				{
					if($row_mdl = pg_fetch_object($result_mdl))
					{
						$obj->mdl_fullname = $row_mdl->fullname;
						$obj->mdl_shortname = $row_mdl->shortname;
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
	
	// **********************************************
	// * Schaut ob fuer diese LV/StSem schon ein 
	// * Moodle Kurs existiert
	// * @param lehrveranstaltung_id
	// *        studiensemester_kurzbz
	// * @return true wenn vorhanden, false wenn nicht
	// **********************************************
	function course_exists_for_lv($lehrveranstaltung_id, $studiensemester_kurzbz)
	{
		$qry = "SELECT 1 FROM lehre.tbl_moodle WHERE lehrveranstaltung_id='".addslashes($lehrveranstaltung_id)."' AND studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."'";
		if($result = pg_query($this->conn, $qry))
		{
			if(pg_num_rows($result)>0)
				return true;	
			else 
				return false;
		}
		else
		{
			$this->errormsg = 'Fehler bei SELECT Abfrage in moodle_course.class.php / course_exists_for_lv';
			return false;
		}
	}
	
	// **********************************************
	// * Schaut ob fuer diese LE schon ein Moodle
	// * Kurs existiert
	// * @param lehreinheit_id
	// * @return true wenn vorhanden, false wenn nicht
	// **********************************************
	function course_exists_for_le($lehreinheit_id)
	{
		$qry = "SELECT 1 FROM lehre.tbl_moodle WHERE lehreinheit_id='".addslashes($lehreinheit_id)."'";
		if($result = pg_query($this->conn, $qry))
		{
			if(pg_num_rows($result)>0)
				return true;
			else 
				return false;
		}
		else
		{
			$this->errormsg = 'Fehler bei SELECT Abfrage in moodle_course.class.php / course_exists_for_le';
			return false;
		}
	}
	
	// **********************************************
	// * Schaut ob fuer diese LV/StSem schon ein 
	// * Moodle Kurs existiert
	// * @param lehrveranstaltung_id
	// *        studiensemester_kurzbz
	// * @return true wenn vorhanden, false wenn nicht
	// **********************************************
	function course_exists_for_allLE($lehrveranstaltung_id, $studiensemester_kurzbz)
	{
		$qry = "SELECT 1 FROM lehre.tbl_lehreinheit 
				WHERE lehrveranstaltung_id='".addslashes($lehrveranstaltung_id)."' 
				AND studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."'
				AND lehreinheit_id NOT IN (SELECT lehreinheit_id FROM lehre.tbl_moodle WHERE lehreinheit_id=tbl_lehreinheit.lehreinheit_id)";
		if($result = pg_query($this->conn, $qry))
		{
			if(pg_num_rows($result)>0)
				return false;
			else 
				return true;
		}
		else
		{
			$this->errormsg = 'Fehler bei SELECT Abfrage in moodle_course.class.php / course_exists_for_allLE';
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
	
	// ************************************************
	// * Legt einen Eintrag in der tbl_moodle an
	// * @return true wenn ok, false im Fehlerfall
	// ************************************************
	function create_vilesci()
	{
		if($this->mdl_course_id=='')
		{
			$this->errormsg='mdl_course_id muss angegeben sein';
			return false;
		}
		
		$qry = 'BEGIN; INSERT INTO lehre.tbl_moodle(mdl_course_id, lehreinheit_id, lehrveranstaltung_id, 
											studiensemester_kurzbz, insertamum, insertvon, gruppen)
				VALUES('.
				$this->addslashes($this->mdl_course_id).','.
				$this->addslashes($this->lehreinheit_id).','.
				$this->addslashes($this->lehrveranstaltung_id).','.
				$this->addslashes($this->studiensemester_kurzbz).','.
				$this->addslashes($this->insertamum).','.
				$this->addslashes($this->insertvon).','.
				($this->gruppen?'true':'false').');';
				
		if(pg_query($this->conn, $qry))
		{
			$qry = "SELECT currval('lehre.tbl_moodle_moodle_id_seq') as id;";
			if($result = pg_query($this->conn, $qry))
			{
				if($row = pg_fetch_object($result))
				{
					$this->moodle_id = $row->id;
					pg_query($this->conn, 'COMMIT;');
					return true;
				}
				else 
				{
					pg_query($this->conn, 'ROLLBACK');
					$this->errormsg = 'Fehler beim Lesen der Sequence';
					return false;
				}				
			}
			else 
			{
					pg_query($this->conn, 'ROLLBACK');
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
	
	// ***********************************************
	// * Legt einen Kurs im Moodle an
	// ***********************************************
	function create_moodle()
	{
		pg_query($this->conn_moodle, 'BEGIN;');
		
		//CourseCategorie ermitteln
		
		//lehrveranstalung ID holen falls die nur die lehreinheit_id angegeben wurde
		if($this->lehrveranstaltung_id=='')
		{
			$qry = "SELECT lehrveranstaltung_id FROM lehre.tbl_lehreinheit 
					WHERE lehreinheit_id='".addslashes($this->lehreinheit_id)."'";
			if($result = pg_query($this->conn, $qry))
			{
				if($row = pg_fetch_object($result))
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
		$qry = "SELECT tbl_lehrveranstaltung.semester, UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as stg FROM lehre.tbl_lehrveranstaltung JOIN public.tbl_studiengang USING(studiengang_kz)
				WHERE lehrveranstaltung_id='$lvid'";
		
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
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
		
		//Eintrag in tbl_mdl_course
		$qry = "INSERT INTO public.mdl_course(category, sortorder, fullname, shortname, format, showgrades, newsitems, enrollable)
				VALUES (".$this->addslashes($id_sem).", (SELECT max(sortorder)+1 FROM public.mdl_course), ".$this->addslashes($this->mdl_fullname).", ".
				$this->addslashes($this->mdl_shortname).",'topics', 1, 5, 0);";
		
		if($result = pg_query($this->conn_moodle, $qry))
		{
			$qry = "SELECT currval('mdl_course_id_seq') as id";
			if($result = pg_query($this->conn_moodle, $qry))
			{
				if($row = pg_fetch_object($result))
				{
					$this->mdl_course_id = $row->id;
				}
				else 
				{
					pg_query($this->conn_moodle, 'ROLLBACK');
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					return false;
				}
			}
			else 
			{	
				pg_query($this->conn_moodle, 'ROLLBACK');
				$this->errormsg = 'Fehler beim Auslesen der Sequence';
				return false;
			}
		}
		else 
		{
			pg_query($this->conn_moodle, 'ROLLBACK');
			$this->errormsg = 'Fehler beim INSERT';
			return false;
		}
				
		//zum vorherigen Pfad die aktuelle id hinzufuegen
		$path = "(SELECT '$this->mdl_context_path' || '/' || currval('mdl_context_id_seq'))";
		//vorherige tiefe um 1 erhoehen
		$depth = $this->mdl_context_depth+1;
		
		//Context eintragen
		$qry = "INSERT INTO public.mdl_context(contextlevel, instanceid, path, depth) VALUES('50', ".
		$this->addslashes($this->mdl_course_id).",".$path.",".$this->addslashes($depth).");";
		
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
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					return false;
				}
			}
			else 
			{	
				pg_query($this->conn_moodle, 'ROLLBACK');
				$this->errormsg = 'Fehler beim Auslesen der Sequence';
				return false;
			}
		}
		else 
		{
			pg_query($this->conn_moodle, 'ROLLBACK');
			$this->errormsg = 'Fehler beim INSERT';
			return false;
		}
		
		//Bloecke hinzufuegen
		$qry = 
		"INSERT INTO public.mdl_block_instance(blockid, pageid, pagetype, position, weight, visible) VALUES(20, $this->mdl_course_id, 'course-view', 'l', 0, 1);". //Teilnehmer
		"INSERT INTO public.mdl_block_instance(blockid, pageid, pagetype, position, weight, visible) VALUES(1, $this->mdl_course_id, 'course-view', 'l', 1, 1);". //Aktivitäten
		"INSERT INTO public.mdl_block_instance(blockid, pageid, pagetype, position, weight, visible) VALUES(25, $this->mdl_course_id, 'course-view', 'l', 2, 1);". //Forumssuche
		"INSERT INTO public.mdl_block_instance(blockid, pageid, pagetype, position, weight, visible) VALUES(2, $this->mdl_course_id, 'course-view', 'l', 3, 1);". //Admin
		"INSERT INTO public.mdl_block_instance(blockid, pageid, pagetype, position, weight, visible) VALUES(9, $this->mdl_course_id, 'course-view', 'l', 4, 1);". //Kursliste
		"INSERT INTO public.mdl_block_instance(blockid, pageid, pagetype, position, weight, visible) VALUES(18, $this->mdl_course_id, 'course-view', 'r', 0, 1);". //Neueste Nachrichten
		"INSERT INTO public.mdl_block_instance(blockid, pageid, pagetype, position, weight, visible) VALUES(8, $this->mdl_course_id, 'course-view', 'r', 1, 1);". //Kalender / Bald aktuell...
		"INSERT INTO public.mdl_block_instance(blockid, pageid, pagetype, position, weight, visible) VALUES(22, $this->mdl_course_id, 'course-view', 'r', 2, 1);"; //Neueste Aktivitäten

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
		}

	}
	
	// ************************************************************
	// * Laedt eine CourseCategorie anhand der Bezeichnung und der
	// * ParentID
	// ************************************************************
	function getCategorie($bezeichnung, $parent)
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
		$qry = "SELECT id FROM public.mdl_course_categories WHERE name='".addslashes($bezeichnung)."' AND parent='".addslashes($parent)."'";
		
		if($result = pg_query($this->conn_moodle, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				return $row->id;
			}
			else 
			{
				$this->errormsg = 'Fehler beim Laden der KursKategorie';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der KursKategorie';
			return false;
		}
	}
	
	// ************************************************************
	// * Erzeugt eine CourseCategorie anhand der Bezeichnung und der
	// * ParentID
	// ************************************************************
	function createCategorie($bezeichnung, $parent)
	{
		if($bezeichnung=='')
		{
			$this->errormsg = 'Bezeichnung muss angegeben werden';
			return false;
		}
		if($parent=='')
		{
			$this->errormsg = 'createCategorie: parent wurde nicht uebergeben: '.$bezeichnung.' '.$parent;
			return false;
		}
		if($parent!='0')
		{
			//Parent laden
			$qry = "SELECT * FROM public.mdl_course_categories WHERE id='".addslashes($parent)."'";
			//echo $qry;
			if($result = pg_query($this->conn_moodle, $qry))
			{
				if($row = pg_fetch_object($result))
				{
					$depth = $row->depth;
					$path = $row->path;
				}
				else 
				{
					$this->errormsg = 'Fehler beim Laden der KursKategorie';
					return false;
				}
			}
			else 
			{
				$this->errormsg = 'Fehler beim Laden der KursKategorie';
				return false;
			}
		}
		else 
		{
			$depth=0;
			$path='';
		}
		
		//KursKategorie anlegen
		$qry = "BEGIN; INSERT INTO public.mdl_course_categories(name, parent, sortorder, 
				coursecount, visible, timemodified, depth, path, theme)
				VALUES(".$this->addslashes($bezeichnung).",".$this->addslashes($parent).",".
				"'999',0,1,0,".$this->addslashes($depth+1).
				", (SELECT ".$this->addslashes($path.'/')." || currval('mdl_course_categories_id_seq')), null);";
		
		if(pg_query($this->conn_moodle, $qry))
		{
			$qry = "SELECT currval('mdl_course_categories_id_seq') as id";
			if($result = pg_query($this->conn_moodle, $qry))
			{
				if($row = pg_fetch_object($result))
				{
					$coursecatid = $row->id;
					
					//Context anlegen
					//wenn Parent 0 ist, dann den SYSTEM Eintrag holen
					if($parent!='0')
						$qry = "SELECT path, depth FROM public.mdl_context WHERE contextlevel='40' AND instanceid='".addslashes($parent)."'";
					else 
						$qry = "SELECT path, depth FROM public.mdl_context WHERE contextlevel='10' AND instanceid='".addslashes($parent)."'";
					if($result = pg_query($this->conn_moodle, $qry))
					{
						if($row = pg_fetch_object($result))
						{
							$path = $row->path;
							$depth = $row->depth;
							
							//zum vorherigen Pfad die aktuelle id hinzufuegen
							$path = "(SELECT '$path' || '/' || currval('mdl_context_id_seq'))";
							//vorherige tiefe um 1 erhoehen
							$depth=$depth+1;
							
							//Context eintragen
							$qry = "INSERT INTO public.mdl_context(contextlevel, instanceid, path, depth) VALUES('40', ".
							$this->addslashes($coursecatid).",".$path.",".$this->addslashes($depth).");";
							
							if(pg_query($this->conn_moodle, $qry))
							{
								$qry = "SELECT currval('mdl_context_id_seq') as id";
								if($result = pg_query($this->conn_moodle, $qry))
								{
									if($row = pg_fetch_object($result))
									{
										pg_query($this->conn_moodle,'COMMIT;');
										return $coursecatid;
									}
									else 
									{
										pg_query($this->conn_moodle, 'ROLLBACK');
										$this->errormsg = 'Fehler beim Auslesen der Sequence';
										return false;
									}
								}
								else 
								{	
									pg_query($this->conn_moodle, 'ROLLBACK');
									$this->errormsg = 'Fehler beim Auslesen der Sequence';
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
						else 
						{
							pg_query($this->conn_moodle, 'ROLLBACK;');
							$this->errormsg = 'Fehler beim Auslesen des Contextes'.$qry;
							return false;	
						}
					}
					else 
					{
						pg_query($this->conn_moodle, 'ROLLBACK;');
						$this->errormsg = 'Fehler beim Auslesen des Contextes';
						return false;
					}
				}
				else
				{
					pg_query($this->conn_moodle, 'ROLLBACK');
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					return false;
				}
			}
			else
			{
				pg_query($this->conn_moodle, 'ROLLBACK');
				$this->errormsg = 'Fehler beim Auslesen der Sequence';
				return false;
			}
		}
		else
		{
			pg_query($this->conn_moodle, 'ROLLBACK');
			$this->errormsg = 'Fehler beim Speichern der KursKategorie';
			return false;
		}
	}
	
	// ************************************************************
	// * Laedt einen Context anhand des contextlevels und der instanceid
	// ************************************************************
	function getContext($contextlevel, $instanceid)
	{

		$qry ="SELECT * FROM public.mdl_context WHERE contextlevel='".addslashes($contextlevel)."' 
				AND instanceid='".addslashes($instanceid)."'";
		
		if($result = pg_query($this->conn_moodle, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->mdl_context_id = $row->id;
				$this->mdl_context_contextlevelid = $row->id;
				$this->mdl_context_instanceid = $row->instanceid;
				$this->mdl_context_path = $row->path;
				$this->mdl_context_depth = $row->depth;
				
				return true;
			}
			else 
			{
				$this->errormsg = 'Eintrag wurde nicht gefunden';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Auslesen des Contexts';
			return false;
		}
	}
	
	// ****************************************************
	// * Liefert alle Kurse dieser LV in denen der Student 
	// * zugeteilt ist
	// ****************************************************
	function getCourse($lehrveranstaltung_id, $studiensemester_kurzbz, $student_uid)
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
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				//schauen in welchen der Student ist
				$qry = "SELECT 1 FROM campus.vw_student_lehrveranstaltung 
						WHERE uid='".addslashes($student_uid)."' AND lehreinheit_id='".addslashes($row->lehreinheit_id)."'";

				if($result_vw = pg_query($this->conn, $qry))
				{
					if(pg_num_rows($result_vw)>0)
					{
						if(!array_key_exists($row->mdl_course_id, $courses))
							$courses[]=$row->mdl_course_id;						
					}
				}
			}
		}
		return $courses;
	}
	
	// ***************************************************
	// * Aktualisiert die Spalte gruppen in der tbl_moodle
	// * @param moodle_id ID der MoodleZuteilung
	// *        gruppen boolean true wenn syncronisiert 
	// *                werden soll, false wenn nicht
	// * @return true wenn ok, false im Fehlerfall
	// ***************************************************
	function updateGruppenSync($moodle_id, $gruppen)
	{
		if(!is_numeric($moodle_id))
		{
			$this->errormsg = 'Moodle_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "UPDATE lehre.tbl_moodle SET gruppen=".($gruppen?'true':'false')." WHERE moodle_id='".addslashes($moodle_id)."'";
		
		if(pg_query($this->conn, $qry))
		{
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Update';
			return false;
		}
	}
	
	// ********************************
	// * Legt einen Testkurs an
	// ********************************
	function createTestkurs($lehrveranstaltung_id, $studiensemester_kurzbz)
	{
		pg_query($this->conn_moodle, 'BEGIN;');
		
		//CourseCategorie ermitteln
				
		//Studiengang und Semester holen
		$qry = "SELECT tbl_lehrveranstaltung.semester, UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as stg FROM lehre.tbl_lehrveranstaltung JOIN public.tbl_studiengang USING(studiengang_kz)
				WHERE lehrveranstaltung_id='$lehrveranstaltung_id'";
		
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
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
				
		//CourseCategorie Context holen
		$this->getContext(40, $id_stsem);
		
		//Eintrag in tbl_mdl_course
		$qry = "INSERT INTO public.mdl_course(category, sortorder, fullname, shortname, format, showgrades, newsitems, enrollable)
				VALUES (".$this->addslashes($id_stsem).", (SELECT max(sortorder)+1 FROM public.mdl_course), ".$this->addslashes($this->mdl_fullname).", ".
				$this->addslashes($this->mdl_shortname).",'topics', 1, 5, 0);";
		
		if($result = pg_query($this->conn_moodle, $qry))
		{
			$qry = "SELECT currval('mdl_course_id_seq') as id";
			if($result = pg_query($this->conn_moodle, $qry))
			{
				if($row = pg_fetch_object($result))
				{
					$this->mdl_course_id = $row->id;
				}
				else 
				{
					pg_query($this->conn_moodle, 'ROLLBACK');
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					return false;
				}
			}
			else 
			{	
				pg_query($this->conn_moodle, 'ROLLBACK');
				$this->errormsg = 'Fehler beim Auslesen der Sequence';
				return false;
			}
		}
		else 
		{
			pg_query($this->conn_moodle, 'ROLLBACK');
			$this->errormsg = 'Fehler beim INSERT';
			return false;
		}
				
		//zum vorherigen Pfad die aktuelle id hinzufuegen
		$path = "(SELECT '$this->mdl_context_path' || '/' || currval('mdl_context_id_seq'))";
		//vorherige tiefe um 1 erhoehen
		$depth = $this->mdl_context_depth+1;
		
		//Context eintragen
		$qry = "INSERT INTO public.mdl_context(contextlevel, instanceid, path, depth) VALUES('50', ".
		$this->addslashes($this->mdl_course_id).",".$path.",".$this->addslashes($depth).");";
		
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
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					return false;
				}
			}
			else 
			{	
				pg_query($this->conn_moodle, 'ROLLBACK');
				$this->errormsg = 'Fehler beim Auslesen der Sequence';
				return false;
			}
		}
		else 
		{
			pg_query($this->conn_moodle, 'ROLLBACK');
			$this->errormsg = 'Fehler beim INSERT';
			return false;
		}
		
		//Bloecke hinzufuegen
		$qry = 
		"INSERT INTO public.mdl_block_instance(blockid, pageid, pagetype, position, weight, visible) VALUES(20, $this->mdl_course_id, 'course-view', 'l', 0, 1);". //Teilnehmer
		"INSERT INTO public.mdl_block_instance(blockid, pageid, pagetype, position, weight, visible) VALUES(1, $this->mdl_course_id, 'course-view', 'l', 1, 1);". //Aktivitäten
		"INSERT INTO public.mdl_block_instance(blockid, pageid, pagetype, position, weight, visible) VALUES(25, $this->mdl_course_id, 'course-view', 'l', 2, 1);". //Forumssuche
		"INSERT INTO public.mdl_block_instance(blockid, pageid, pagetype, position, weight, visible) VALUES(2, $this->mdl_course_id, 'course-view', 'l', 3, 1);". //Admin
		"INSERT INTO public.mdl_block_instance(blockid, pageid, pagetype, position, weight, visible) VALUES(9, $this->mdl_course_id, 'course-view', 'l', 4, 1);". //Kursliste
		"INSERT INTO public.mdl_block_instance(blockid, pageid, pagetype, position, weight, visible) VALUES(18, $this->mdl_course_id, 'course-view', 'r', 0, 1);". //Neueste Nachrichten
		"INSERT INTO public.mdl_block_instance(blockid, pageid, pagetype, position, weight, visible) VALUES(8, $this->mdl_course_id, 'course-view', 'r', 1, 1);". //Kalender / Bald aktuell...
		"INSERT INTO public.mdl_block_instance(blockid, pageid, pagetype, position, weight, visible) VALUES(22, $this->mdl_course_id, 'course-view', 'r', 2, 1);"; //Neueste Aktivitäten

		if(!pg_query($this->conn_moodle, $qry))
		{
			pg_query($this->conn_moodle, 'ROLLBACK');
			$this->errormsg = 'Fehler beim INSERT der Bloecke';
			return false;
		}
		else 
		{
			pg_query($this->conn_moodle, 'COMMIT');
			return true;
		}
	}
	
	// ******************************************************
	// * Laedt den Testkurs zu dieser Lehrveranstaltung
	// * @param lehrveranstaltung_id
	// *        studiensemester_kurzbz
	// * @return ID wenn gefunden, false wenn nicht vorhanden
	// ******************************************************
	function loadTestkurs($lehrveranstaltung_id, $studiensemester_kurzbz)
	{
		$qry = "SELECT
					UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as kuerzel,
					tbl_lehrveranstaltung.semester, tbl_lehrveranstaltung.kurzbz
				FROM
					lehre.tbl_lehrveranstaltung JOIN public.tbl_studiengang USING(studiengang_kz)
				WHERE
					lehrveranstaltung_id='".addslashes($lehrveranstaltung_id)."'";
		
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$shortname = strtoupper('TK-'.$studiensemester_kurzbz.'-'.$row->kuerzel.'-'.$row->semester.'-'.$row->kurzbz);
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
		
		$qry = "SELECT id, fullname, shortname FROM public.mdl_course WHERE shortname='".addslashes($shortname)."' AND category='$id_stsem' LIMIT 1";
		
		if($result = pg_query($this->conn_moodle, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->mdl_fullname = $row->fullname;
				$this->mdl_shortname = $row->shortname;
				$this->mdl_course_id = $row->id;
				return true;
			}
			else 
			{
				$this->errormsg = 'Es wurde kein Testkurs gefunden';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Abfragen der Kurse'; 
			return false;
		}
	}
	
	// ******************************************************
	// * Laedt die Noten zu einem Moodle Course ID
	// * @param mdl_course_id
	// *        
	// * @return objekt mit den Noten der Teilnehmer dieses Kurses
	// ******************************************************
	function loadNoten($lehrveranstaltung_id='', $studiensemester_kurzbz='',$student_uid='',$bDetailinfo=false)
	{

		// Init
			$lehrveranstaltung_id=trim($lehrveranstaltung_id);
			$studiensemester_kurzbz=trim($studiensemester_kurzbz);
			$student_uid=trim($student_uid);
			$this->errormsg='';		
					
		// plausib
			if (empty($lehrveranstaltung_id) 
			|| empty($studiensemester_kurzbz) 
			|| empty($student_uid) ) 
			{
				$this->errormsg = 'Es fehlt die Eingabe von';
				$this->errormsg.=(empty($lehrveranstaltung_id)?' Lehrveranstaltung':$lehrveranstaltung_id);
				$this->errormsg.=(empty($studiensemester_kurzbz)?' Semester (Kurzbz.)':$studiensemester_kurzbz);
				$this->errormsg.=(empty($student_uid)?' Student':$student_uid);								
				return false;
			}
	
		// --------------------------------------------------------------------
		// Ermitteln die Lehreinheiten und Moodle ID
		//		mit dem studiensemester_kurzbz ( bsp WS2008 )
		//		und der lehrveranstaltung_id aus FAS ( bsp 23802 )
		// --------------------------------------------------------------------
		$qry = "
		SELECT tbl_lehreinheit.lehreinheit_id, mdl_course_id,studiensemester_kurzbz,tbl_moodle.lehrveranstaltung_id
			FROM lehre.tbl_moodle 
			JOIN lehre.tbl_lehreinheit USING(lehrveranstaltung_id, studiensemester_kurzbz)
		WHERE tbl_moodle.lehrveranstaltung_id like E'".addslashes($lehrveranstaltung_id)."' 
		AND tbl_moodle.studiensemester_kurzbz like E'".addslashes($studiensemester_kurzbz)."'
		UNION 
			SELECT tbl_lehreinheit.lehreinheit_id, mdl_course_id,tbl_lehreinheit.studiensemester_kurzbz,tbl_moodle.lehrveranstaltung_id
			FROM lehre.tbl_moodle
			JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) 
			WHERE tbl_lehreinheit.lehrveranstaltung_id like E'".addslashes($lehrveranstaltung_id)."' 
			  AND tbl_lehreinheit.studiensemester_kurzbz like E'".addslashes($studiensemester_kurzbz)."'
		;";
		
		if(!$result = @pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim Lesen der Kurse ';
			return false;
		}	
		// init
		$lehreinheit_kpl = array(); // Gesamte Information der Lehreinheit und Moodle IDs
		$lehreinheit=array();	// Lehreinheiten zum lesen Studenten im Campus (Student und LE im FAS) 
		//echo $qry;
		while($row = pg_fetch_object($result))
		{
			$row->lehreinheit_id=trim($row->lehreinheit_id);
			$lehreinheit[$row->lehreinheit_id]=$row->lehreinheit_id; // Fuer Select Campus
			$lehreinheit_kpl[$row->lehreinheit_id]=$row; // Fuer GesamtDaten wird Ergaenzt von Campus,
		}	
		@pg_free_result($result);
		if (count($lehreinheit)<1) // Es gibt keine Lehreinheiten
		{
			$this->errormsg='Es wurde kein passender Moodle-Kurs gefunden';
			return false;
		}
		
		// Fuer die naechste Verarbeitung die Array Sortieren 
		asort($lehreinheit);
		reset($lehreinheit);

		asort($lehreinheit_kpl);
		reset($lehreinheit_kpl);		
		
		// --------------------------------------------------------------------
		//
		// Suchen Studenten Lehreinheiten zu Moodle - LE  
		//		Fuer die Notenermittlung sind nur Studenten wichtig
		//		die einen Moodlekurs besuchen der auch eine Lehrveranstaltung ist
		//	Als Ergebnis sind alle Studenten mit gemeinsame Moodle und FAS  LV
		// --------------------------------------------------------------------
		$qry = "SELECT * FROM campus.vw_student_lehrveranstaltung ";
		$qry.= " WHERE uid like  E'".addslashes($student_uid)."'";
		$qry.= " AND lehreinheit_id in (".implode(",",$lehreinheit)."); ";
		
		unset($lehreinheit); // lehreinheit wird nach Check nicht mehr benoetigt. wird nun die Lehrveranstalt
		if(!$result = @pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim Lesen der Studenten mit Lehreinheit(en) ';
			return false;
		}	
		// Lehreinheit und Moodlekurs zusammenfuehren
		$lehrveranstaltUSER=array(); // Detailinformation fuer das Noten-Ergebnis		
		$lehrveranstaltMOODLE=array(); // Je Student alle Moodle IDs
		while($row = @pg_fetch_object($result))
		{
			// Von der Lehreinheit kann der Moodle-Kurs ermittelt werden
			$mdl_course_id=trim($lehreinheit_kpl[$row->lehreinheit_id]->mdl_course_id);
			// Die Moodle Kurs ID zu den Lehreinheitdaten hinzufuegen ( DatenSelect erweitern )
			$row->mdl_course_id=$mdl_course_id; 
			$mdl_username=trim($row->uid);
			// Campusdaten merken fuer Student und MoodleID ( wird fuer merge mit Moodlenoten benotigt )
			$lehrveranstaltUSER[$mdl_username][$mdl_course_id]=$row;
			// Alle Moodle IDs zum Studenten sammeln fuer Noten Select
			$lehrveranstaltMOODLE[$mdl_username][$mdl_course_id]=$mdl_course_id;
		}
		@pg_free_result($result);	
		unset($lehreinheit_kpl); // Wird nicht mehr benoetigt (die Daten sind nun in LV,LE )

		// Fuer die naechste Verarbeitung die Array Sortieren
		asort($lehrveranstaltUSER);
		reset($lehrveranstaltUSER);
		
		if (count($lehrveranstaltMOODLE)<1) // Es gibt keine Moodle - LV,LE
			return false;
			
		// --------------------------------------------------------------------
		// Moodle Noten - Uebersetztungstabellen einlesen (min. ein Record )
		// --------------------------------------------------------------------
		$qry = "select * from mdl_grade_letters order by contextid, lowerboundary desc; ";
		if(!$result = @pg_query($this->conn_moodle, $qry))
		{
			$this->errormsg = 'Fehler beim Lesen der Noten ';
			return false;
		}	

		$mdl_grade_letters_first=null;
		// Init
		$mdl_grade_letters=array(); 
		while($row = @pg_fetch_object($result))
		{
			if ($mdl_grade_letters_first==null)
				$mdl_grade_letters_first=$row->contextid;
			$mdl_grade_letters[$row->contextid][]=$row;
		}	
		asort($mdl_grade_letters);
		reset($mdl_grade_letters);
		
		

		
		if (!isset($mdl_grade_letters[$mdl_grade_letters_first]))
		{
			$this->errormsg = ' Keinen Notenschluessel gefunden! ( Tabelle: mdl_grade_letters ist leer. )';
			return false;
		}	
		// --------------------------------------------------------------------
		//
		// 	Moodle zu User, und MoodleIDs lesen 
		//		aus der Tabelle GRADE "Noten werden die Benotungen geholt
		//		
		//
		// --------------------------------------------------------------------
		$qry = "select mdl_grade_items.courseid,
				mdl_user.username,mdl_user.lastname,mdl_user.firstname,
				mdl_grade_grades.userid,
		        mdl_grade_grades.rawgrade,
				mdl_grade_grades.rawgrademax,
				mdl_grade_grades.rawgrademin, 
				mdl_grade_grades.finalgrade,
				mdl_course.fullname,mdl_course.shortname
			from mdl_grade_items , mdl_grade_grades ,mdl_user,mdl_course
			where mdl_grade_items.itemtype='course'
			 and mdl_grade_grades.finalgrade IS NOT NULL 
			 and mdl_course.id=mdl_grade_items.courseid
			 and mdl_grade_grades.itemid=mdl_grade_items.id
			 and mdl_grade_grades.userid=mdl_user.id
			";
		$usr_qry="";
		

		
	    while (list( $user_key, $course_ids ) = each($lehrveranstaltMOODLE) )
		{
			if(empty($usr_qry))
				$usr_qry.=" and ( (mdl_user.username like E'".addslashes($user_key)."' and mdl_grade_items.courseid in (".implode(",",$course_ids).") ) ";
			else
				$usr_qry.=" or (mdl_user.username like E'".addslashes($user_key)."' and mdl_grade_items.courseid in (".implode(",",$course_ids).") ) ";
		}
		if (!empty($usr_qry))
			$qry.=$usr_qry." )";
		$qry.= "order by mdl_grade_items.courseid,mdl_user.lastname,mdl_user.firstname;"; // Ende SQL String 

		if (isset($lehrveranstaltMOODLE)) // wird nicht mehr benoetigt 
			unset($lehrveranstaltMOODLE);
		
		if(!$result = @pg_query($this->conn_moodle, $qry))
		{
			$this->errormsg = 'Fehler beim Lesen der Moodle Benutzer und Noten ';
			return false;
		}	
		
		// Init		 
		$mdl_course=array(); 
		$mdl_noten=array(); 
		$mdl_noten_course=array();
		while($row = @pg_fetch_object($result))
		{
			$mdl_course_id=trim($row->courseid);
			$mdl_username=trim($row->username);		
			$mdl_finalgrade=trim($row->finalgrade);		
			
			if (empty($mdl_finalgrade))
				continue; // Keine Notenfindung 
			
			
			// Vergleichswerte fuer die Noten aus der Tabelle mdl_grade_letters suchen
			$arrTmpDefaultNoten=array();
			$mdl_grade_letters_first=1; // Es muss einen Default geben (Administrator Noten)
			if (isset($mdl_grade_letters[$mdl_grade_letters_first]))
				$arrTmpDefaultNoten=$mdl_grade_letters[$mdl_grade_letters_first];						
				
			if($this->getContext(50, $mdl_course_id))
				$mdl_grade_letters_first = $this->mdl_context_id;
						
			if (isset($mdl_grade_letters[$mdl_grade_letters_first]))
			{
				$arrTmpDefaultNoten=$mdl_grade_letters[$mdl_grade_letters_first];
			}	
			if (!is_array($arrTmpDefaultNoten) || count($arrTmpDefaultNoten)<1)		
			{
				$this->errormsg = 'Keinen Notenschluessel gefunden! ( Tabelle: mdl_grade_letters ist leer, oder kein Eintrag zu Kurs '.$mdl_course_id.'. ) ';
				return false;
			}	
			$row->NotenermittlungTabIndex=$mdl_grade_letters_first;
			$row->NotenermittlungTab=$arrTmpDefaultNoten;
			$row->note=0;
		    for ($iTmpIndex=0;$iTmpIndex<count($arrTmpDefaultNoten);$iTmpIndex++)
			{
				// ------------ NOTEN ERMITTLUNG
				// Vergleichsoperatoren fuer Noten
				$iTmpVergl=$arrTmpDefaultNoten[$iTmpIndex]->lowerboundary;
				$iTmpFaktor=(!empty($row->rawgrademax)?(int)(100/$row->rawgrademax):1);
					//echo "<br>::$iTmpIndex $mdl_finalgrade>=( $iTmpVergl / $iTmpFaktor)::<br>";
					if ($mdl_finalgrade>=( $iTmpVergl / $iTmpFaktor) )
					{
						if (is_numeric($arrTmpDefaultNoten[$iTmpIndex]->letter)) 
						{
							$row->note=($iTmpIndex + 1);
							if($row->note>5 || $row->note<1)
							{
								echo "FOOOO";
								$this->errormsg = 'Unbekannter Notenschluessel';
								return false;
							}
						}
						else
						{
							if ( substr($arrTmpDefaultNoten[$iTmpIndex]->letter,0,1)=="A")
								$row->note=1;
							elseif (substr($arrTmpDefaultNoten[$iTmpIndex]->letter,0,1)=="B")
								$row->note=2;
							elseif (substr($arrTmpDefaultNoten[$iTmpIndex]->letter,0,1)=="C")
								$row->note=3;
							elseif (substr($arrTmpDefaultNoten[$iTmpIndex]->letter,0,1)=="D")
								$row->note=4;
							elseif (substr($arrTmpDefaultNoten[$iTmpIndex]->letter,0,1)=="E" 
								|| substr($arrTmpDefaultNoten[$iTmpIndex]->letter,0,1)=="F" )
								$row->note=5;
							else
							{
								$this->errormsg = ' Unbekannter Notenschluessel "'.$arrTmpDefaultNoten[$iTmpIndex]->letter.'" ( Erlaubt sind 1 bis 5, oder A bis F ) ! ';
								return false;
							}	
						}		
						break;
					}
			}

			// Wenn kein Detail werden nur die Noten genommen Moodle Noten 
			if (!$bDetailinfo)
			{
				$mdl_noten_course[]=array("courseid"=>$row->courseid,"fullname"=>$row->fullname,"shortname"=>$row->shortname,"userid"=>$row->userid,"username"=>$row->username,"firstname"=>$row->firstname,"lasttname"=>$row->lastname,"note"=>$row->note);
				$mdl_noten[]=$row->note;
				$mdl_course[]=$row; 
			}
			else // Fuer Detailinformation Moodle Noten und Lehrveranstaltung mischen zu einem Datensatz
			{			
				if ($bDetailinfo && isset($lehrveranstaltUSER[$mdl_username][$mdl_course_id]))
				{
					$lehrverantstaltung=$lehrveranstaltUSER[$mdl_username][$mdl_course_id];
				    while (list( $key, $value ) = each($lehrverantstaltung) )
       					$row->$key=$value;
					unset($lehrverantstaltung);		
					$mdl_course[$mdl_course_id][]=$row; 
				}	
				elseif($bDetailinfo) // Wenn Detailinformation werden auch die ohne FAS LV genommen
				{
					$mdl_course['ohneLV'][]=$row; 
				}	
			}

			
#		echo "<br />courseid:=".$row->courseid ."(".$row->fullname." ".$row->shortname.") username:=".$row->username ." Name:=".$row->firstname." ".$row->lastname." Note <b>".$row->note ."</b>";
			
		}	
		// nicht benoetigten Speicher freigeben 
		@pg_free_result($result);	
		unset($result);

		if (isset($qry)) unset($qry);
		if (isset($moodle_id)) unset($moodle_id);
		if (isset($lehreinheit_id)) unset($lehreinheit_id);
		if (isset($lehrveranstaltUSER)) unset($lehrveranstaltUSER);		
		
		// Fuer Detail wird die gesamte Information geliefert, oder ohne Detail nur die Noten
		if (!is_array($mdl_course) || count($mdl_course)<1)
		{
			$this->errormsg = ' keine Informationen gefunden'. count($mdl_course) ;
			return true;
		}	

		if($bDetailinfo) // Alle Informationen Retour
			return $this->note=$mdl_course;
			
		// Nur die Noten senden	wenn 1 Datensatz gefunden wird,
		// ansonst weitere Informationen zum zuordnen der Note zu einem Studenten
		return (count($mdl_noten)>1?$this->note=$mdl_noten_course:$this->note=$mdl_noten[0]);
	}	// Ende moodle Noten 
	

} // Ende moodle_course class