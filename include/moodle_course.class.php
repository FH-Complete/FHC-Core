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
	
	//Moodle Attribute
	var $mdl_fullname;
	var $mdl_shortname;
	
	var $mdl_context_id;
	var $mdl_context_level;
	var $mdl_context_instanceid;
	var $mdl_context_path;	
	var $mdl_context_depth;
	
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
											studiensemester_kurzbz, insertamum, insertvon)
				VALUES('.
				$this->addslashes($this->mdl_course_id).','.
				$this->addslashes($this->lehreinheit_id).','.
				$this->addslashes($this->lehrveranstaltung_id).','.
				$this->addslashes($this->studiensemester_kurzbz).','.
				$this->addslashes($this->insertamum).','.
				$this->addslashes($this->insertvon).');';
				
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
			$id_stsem = $this->createCategorie($this->studiensemester_kurzbz, '0');
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
				$this->addslashes($this->mdl_shortname).",'weeks', 1, 5, 0);";
		
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
			$this->errormsg = 'createCategorie: parent wurde nicht uebergeben';
			return false;
		}
		//Parent laden
		$qry = "SELECT * FROM public.mdl_course_categories WHERE id='".addslashes($parent)."'";
		echo $qry;
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
}