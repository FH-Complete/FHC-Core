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
 * requires moodle_course.class.php
 */
class moodle_user
{
	var $conn;
	var $conn_moodle;
	var $errormsg;
	var $log=''; 		//log message fuer Syncro
	var $sync_create=0; //anzahl der durchgefuehrten zuteilungen beim syncro
	
	var $mdl_user_id;
	var $mdl_user_username;
	
	
	// **********************************************
	// * moodle_user
	// * @param $conn Connection zur Vilesci DB
	// *        $conn_moodle Connection zur Moodle DB
	// **********************************************
	function moodle_user($conn, $conn_moodle)
	{
		$this->conn = $conn;
		$this->conn_moodle = $conn_moodle;
		
		$qry = "SET CLIENT_ENCODING TO 'LATIN9';";
		pg_query($this->conn_moodle, $qry);
	}
	
	function loaduser($uid)
	{
		$qry = "SELECT * FROM public.mdl_user WHERE username='".addslashes($uid)."'";
		
		if($result = pg_query($this->conn_moodle, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->mdl_user_id = $row->id;
				$this->mdl_user_username = $row->username;
				return true;
			}
			else 
			{
				$this->errormsg = 'User wurde nicht gefunden: '.$uid;
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden des Users';
			return false;
		}
	}
	
	// ************************************************
	// * Synchronisiert die Lektoren der Lehreinheiten
	// * mit denen des Moodle Kurses
	// * @param $mdl_course_id ID des MoodleKurses
	// * @return true wenn ok, false wenn Fehler
	// ************************************************
	function sync_lektoren($mdl_course_id)
	{
		//Mitarbeiter laden die zu diesem Kurs zugeteilt sind
		$qry = "SELECT 
					mitarbeiter_uid
				FROM 
					lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_moodle USING(lehreinheit_id) 
				WHERE 
					mdl_course_id='".addslashes($mdl_course_id)."'
				UNION
				SELECT 
					mitarbeiter_uid 
				FROM 
					lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
					JOIN lehre.tbl_moodle USING(lehrveranstaltung_id) 
				WHERE 
					tbl_lehreinheit.studiensemester_kurzbz=tbl_moodle.studiensemester_kurzbz
					AND mdl_course_id='".addslashes($mdl_course_id)."'";
		$mitarbeiter='';
		if($result_ma = pg_query($this->conn, $qry))
		{
			//Context des Kurses holen
			$mdlcourse = new moodle_course($this->conn, $this->conn_moodle);
			if(!$mdlcourse->getContext(50, $mdl_course_id))
			{
				$this->errormsg = 'Fehler beim Laden des Contexts';
				return false;
			}
			
			while($row_ma = pg_fetch_object($result_ma))
			{
				//MoodleID des Users holen bzw ggf neu anlegen
				if(!$this->loaduser($row_ma->mitarbeiter_uid))
				{
					//User anlegen
					if(!$this->createUser($row_ma->mitarbeiter_uid))
					{
						$this->errormsg = "Fehler beim Anlegen des Users $row_ma->mitarbeiter_uid: $this->errormsg";
						return false;
					}
					else 
						$this->errormsg = '';
				}
				
				if($mitarbeiter!='')
					$mitarbeiter.=',';
				$mitarbeiter.=$this->mdl_user_id;
				
				//Nachschauen ob dieser Lektor bereits zugeteilt ist
				$qry = "SELECT 1 FROM public.mdl_role_assignments 
						WHERE 
							userid='".addslashes($this->mdl_user_id)."' AND 
							contextid='".addslashes($mdlcourse->mdl_context_id)."'";
				if($result = pg_query($this->conn_moodle, $qry))
				{
					if(pg_num_rows($result)==0)
					{
						//Mitarbeiter ist noch nicht zugeteilt.
						
						if($this->createZuteilung($this->mdl_user_id, $mdlcourse->mdl_context_id, 3))
						{
							$this->log.="\nerzeuge Lektoren-Zuteilung für User $this->mdl_user_id zum Context $mdlcourse->mdl_context_id";
							$this->sync_create++;
						}
						else 
							$this->log.="\nFehler beim Anlegen der Lektoren-Zuteilung: $this->errormsg";
					}
				}
				else 
				{
					$this->errormsg = 'Fehler beim Auslesen der Rollen';
					return false;
				}
				
			}
			
			//Lektoren loeschen die nicht mehr zugeordnet sind
			/* Derzeit werden zugeteilte Personen nicht geloescht
			$qry = "SELECT * FROM mdl_role_assignments 
					WHERE 
						contextid='".addslashes($mdlcourse->mdl_context_id)."' AND
						userid NOT in ($mitarbeiter)";
			
			if($result = pg_query($this->conn_moodle, $qry))
			{
				while($row = pg_fetch_object($result))
				{
					$this->deleteZuteilung($row->userid, $mdlcourse->mdl_context_id);
				}
			}
			else 
			{
				$this->errormsg = 'Fehler beim Ermitteln der Lektoren die nicht mehr zugeteilt sind';
				return false;
			}
			*/
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Ermitteln der Zugeteilten Lektoren';
			return false;
		}
	}
	
	// ************************************************
	// * Synchronisiert die Studenten der Lehreinheiten
	// * mit denen des Moodle Kurses
	// * @param $mdl_course_id ID des MoodleKurses
	// * @return true wenn ok, false wenn Fehler
	// ************************************************
	function sync_studenten($mdl_course_id)
	{
		//Studentengruppen laden die zu diesem Kurs zugeteilt sind
		$qry = "SELECT 
					studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, tbl_moodle.studiensemester_kurzbz
				FROM 
					lehre.tbl_lehreinheitgruppe JOIN lehre.tbl_moodle USING(lehreinheit_id) 
				WHERE 
					mdl_course_id='".addslashes($mdl_course_id)."'
				UNION
				SELECT 
					studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, tbl_moodle.studiensemester_kurzbz
				FROM 
					lehre.tbl_lehreinheitgruppe JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
					JOIN lehre.tbl_moodle USING(lehrveranstaltung_id) 
				WHERE 
					tbl_lehreinheit.studiensemester_kurzbz=tbl_moodle.studiensemester_kurzbz
					AND mdl_course_id='".addslashes($mdl_course_id)."'";
		$studenten='';
		if($result_std = pg_query($this->conn, $qry))
		{
			//Context des Kurses holen
			$mdlcourse = new moodle_course($this->conn, $this->conn_moodle);
			if(!$mdlcourse->getContext(50, $mdl_course_id))
			{
				$this->errormsg = 'Fehler beim Laden des Contexts';
				return false;
			}
			
			while($row_std = pg_fetch_object($result_std))
			{
				//Studenten dieser Gruppe holen

				if($row_std->gruppe_kurzbz=='') //LVB Gruppe
				{
					$qry = "SELECT
								distinct student_uid
							FROM
								public.tbl_studentlehrverband
							WHERE
								studiensemester_kurzbz='$row_std->studiensemester_kurzbz' AND
								studiengang_kz = '$row_std->studiengang_kz' AND
								semester = '$row_std->semester'";
					if(trim($row_std->verband)!='')
					{
						$qry.=" AND verband = '$row_std->verband'";
						if(trim($row_std->gruppe)!='')
						{
							$qry.=" AND gruppe = '$row_std->gruppe'";
						}
					}
				}
				else //Spezialgruppe
				{
					$qry = "SELECT
								distinct uid as student_uid
							FROM
								public.tbl_benutzergruppe
							WHERE
								gruppe_kurzbz='$row_std->gruppe_kurzbz' AND
								studiensemester_kurzbz='$row_std->studiensemester_kurzbz'
							";
				}

				if($result_user = pg_query($this->conn, $qry))
				{
					while($row_user = pg_fetch_object($result_user))
					{
						
						//MoodleID des Users holen bzw ggf neu anlegen
						if(!$this->loaduser($row_user->student_uid))
						{
							//User anlegen
							if(!$this->createUser($row_user->student_uid))
							{
								$this->errormsg = "Fehler beim Anlegen des Users $row_user->student_uid: $this->errormsg";
								return false;
							}
							else 
								$this->errormsg = '';
						}
						
						if($studenten!='')
							$studenten.=',';
						$studenten.=$this->mdl_user_id;
						
						//Nachschauen ob dieser Student bereits zugeteilt ist
						$qry = "SELECT 1 FROM public.mdl_role_assignments 
								WHERE 
									userid='".addslashes($this->mdl_user_id)."' AND 
									contextid='".addslashes($mdlcourse->mdl_context_id)."'";
						if($result = pg_query($this->conn_moodle, $qry))
						{
							if(pg_num_rows($result)==0)
							{
								//Student ist noch nicht zugeteilt.
								
								if($this->createZuteilung($this->mdl_user_id, $mdlcourse->mdl_context_id, 5))
								{
									$this->log.="\nerzeuge Studenten-Zuteilung für User $this->mdl_user_id zum Context $mdlcourse->mdl_context_id";
									$this->sync_create++;
								}
								else 
									$this->log.="\nFehler beim Anlegen der Studenten-Zuteilung: $this->errormsg";
							}
						}
						else 
						{
							$this->errormsg = 'Fehler beim Auslesen der Rollen';
							return false;
						}
					}
				}
			}
			
			//Studenten loeschen die nicht mehr zugeordnet sind
			/* Derzeit werden zugeteilte Personen nicht geloescht
			$qry = "SELECT * FROM mdl_role_assignments 
					WHERE 
						contextid='".addslashes($mdlcourse->mdl_context_id)."' AND
						userid NOT in ($studenten)";
			
			if($result = pg_query($this->conn_moodle, $qry))
			{
				while($row = pg_fetch_object($result))
				{
					$this->deleteZuteilung($row->userid, $mdlcourse->mdl_context_id);
				}
			}
			else 
			{
				$this->errormsg = 'Fehler beim Ermitteln der Studenten die nicht mehr zugeteilt sind';
				return false;
			}
			*/
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Ermitteln der Zugeteilten Studenten';
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
	
	// ********************************************
	// * Legt einen User im Moodle an
	// * @param $uid UID der Person die angelegt werden soll
	// * @return true wenn ok, false wenn Fehler
	// ********************************************
	function createUser($uid)
	{
		$qry = "SELECT uid, vorname, nachname FROM campus.vw_benutzer WHERE uid='".addslashes($uid)."'";
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$username = $row->uid;
				$vorname = $row->vorname;
				$nachname = $row->nachname;
				
				$qry = "BEGIN; INSERT INTO public.mdl_user(auth, username, idnumber, firstname, lastname, email, mnethostid, confirmed, lang)
						VALUES('ldap', ".
						$this->addslashes($username).", ".
						$this->addslashes($username).",".
						$this->addslashes($vorname).",".
						$this->addslashes($nachname).",".
						$this->addslashes($username.'@'.DOMAIN).", 1, 1, 'de_utf8');";
				
				if(pg_query($this->conn_moodle, $qry))
				{
					$qry ="SELECT currval('mdl_user_id_seq') as id;";
					if($result = pg_query($this->conn_moodle, $qry))
					{
						if($row = pg_fetch_object($result))
						{
							pg_query($this->conn_moodle, 'COMMIT;');
							$this->mdl_user_id = $row->id;
							return true;
						}
						else 
						{
							pg_query($this->conn_moodle,'ROLLBACK');
							$this->errormsg = 'Fehler beim Lesen der Sequence';
							return false;
						}
					}
					else 
					{
						pg_query($this->conn_moodle,'ROLLBACK');
						$this->errormsg = 'Fehler beim Lesen der Sequence';
						return false;
					}
				}
				else 
				{
					pg_query($this->conn_moodle,'ROLLBACK');
					$this->errormsg = 'Fehler beim Anlegen des Users';
					return false;
				}
			}
			else 
			{
				$this->errormsg = 'User wurde nicht gefunden: '.$uid;
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden des Users';
			return false;
		}
	}
	
	// *********************************************
	// * Teilt den User mit der ID $mdl_user_id zum
	// * Kurs mit der ContextID $mdl_context_id zu.
	// * @param $mdl_user_id Moodle ID des Users
	// *        $mdl_context_id ContextID des Kurses
	// *        $role Rolle der Zuteilung (1=Admin/3=Lektor/5=Student)
	// * @return true wenn ok, false wenn Fehler
	// *********************************************
	function createZuteilung($mdl_user_id, $mdl_context_id, $role)
	{
		$qry = "INSERT INTO public.mdl_role_assignments(roleid, contextid, userid) 
				VALUES(".
				$this->addslashes($role).",".
				$this->addslashes($mdl_context_id).",".
				$this->addslashes($mdl_user_id).");";
		
		if(pg_query($this->conn_moodle, $qry))
		{
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Speichern der Zuteilung';
			return false;
		}
	}
	
	// *************************************************
	// * Loescht die Zuteilung eines Users zu einem Kurs
	// * @param $mdl_user_id MoodleID des Users
	// *        $mdl_context_id ContextID des Users
	// * @return true wenn ok, false wenn Fehler
	// *************************************************
	function deleteZuteilung($mdl_user_id, $mdl_context_id)
	{
		$qry = "DELETE FROM public.mdl_role_assignments 
				WHERE userid='".addslashes($mdl_user_id)."' AND contextid='".addslashes($mdl_context_id)."'";
		if(pg_query($this->conn_moodle, $qry))
			return true;
		else 
		{
			$this->errormsg = 'Fehler beim Loeschen der Zuteilung';
			return false;
		}		
	}

}