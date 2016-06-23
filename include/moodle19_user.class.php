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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Andreas Moik <moik@technikum-wien.at>.
 */
/*
 * requires moodle_course.class.php
 * studiengang.class.php
 *
 * Klasse zur Kommunikation mit Moodle 1.9 
 */
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/prestudent.class.php');

class moodle19_user extends basis_db
{
	private $conn_moodle;
	public $log=''; 			//log message fuer Syncro
	public $log_public='';		//log message fuer Syncro
	public $sync_create=0; 	//anzahl der durchgefuehrten zuteilungen beim syncro
	public $group_update=0;	//anzahl der updates an gruppen
	
	public $mdl_user_id;
	public $mdl_user_username;
	public $mdl_user_firstname;
	public $mdl_user_lastname;
		
	/**
	 * Konstruktor
	 */
	public function __construct()
	{
		if(!$this->conn_moodle=pg_pconnect(CONN_STRING_MOODLE))
		{
			$this->errormsg = 'Fehler beim Herstellen der Moodle Verbindung';
			return false;
		}
		else 
			return true;
	}
	
	/**
	 * Laedt einen Moodle User
	 *
	 * @param $uid
	 * @return boolean
	 */
	public function loaduser($uid)
	{
		$qry = "SELECT * FROM public.mdl_user WHERE username='".addslashes($uid)."'";
		
		if($result = pg_query($this->conn_moodle, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->mdl_user_id = $row->id;
				$this->mdl_user_username = $row->username;
				$this->mdl_user_firstname = $row->firstname;
				$this->mdl_user_lastname = $row->lastname;
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
	
	/**
	 * Liefert ein Array mit allen Lektoren die
	 * zu dem Moodle Kurs zugeteilt sind 
	 */
	public function getMitarbeiter($mdl_course_id)
	{
		//Mitarbeiter laden die zu diesem Kurs zugeteilt sind
		$qry = "SELECT 
					mitarbeiter_uid
				FROM 
					lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_moodle USING(lehreinheit_id) 
				WHERE 
					moodle_version='1.9'
					AND mdl_course_id='".addslashes($mdl_course_id)."'
				UNION
				SELECT 
					mitarbeiter_uid 
				FROM 
					lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
					JOIN lehre.tbl_moodle USING(lehrveranstaltung_id) 
				WHERE 
					moodle_version='1.9'
					AND tbl_lehreinheit.studiensemester_kurzbz=tbl_moodle.studiensemester_kurzbz
					AND mdl_course_id='".addslashes($mdl_course_id)."'";
		$mitarbeiter=array();
		if($this->db_query($qry))
		{
			while($row_ma = $this->db_fetch_object())
			{
				$mitarbeiter[] = $row_ma->mitarbeiter_uid;
			}
			return $mitarbeiter;
		}
		else 
		{
			$this->errormsg='Fehler beim Laden der Mitarbeiter';
			return false;
		}
	}
	
	/**
	 * Synchronisiert die Lektoren der Lehreinheiten
	 * mit denen des Moodle Kurses
	 * @param $mdl_course_id ID des MoodleKurses
	 *        lehrveranstaltung_id wird nur angegeben beim Syncro von Testkursen
	 *        studiensemester_kurzbz wird nur angegeben beim Syncro von Testkursen
	 * @return true wenn ok, false wenn Fehler
	 */
	public function sync_lektoren($mdl_course_id, $lehrveranstaltung_id=null, $studiensemester_kurzbz=null)
	{
		//Mitarbeiter laden die zu diesem Kurs zugeteilt sind
		if(!is_null($lehrveranstaltung_id) && !is_null($studiensemester_kurzbz))
		{
			//Bei Testkursen werden alle Lektoren einer Lehrveranstaltung zugeteilt
			//da hier kein Eintrag in der tbl_moodle vorhanden ist, werden die Lektoren direkt aus
			//der tbl_lehreinheitmitarbeiter geholt.
			$qry = "SELECT mitarbeiter_uid FROM lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
					WHERE lehrveranstaltung_id='".addslashes($lehrveranstaltung_id)."' 
					AND studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."'";	
		}
		else 
		{
			$qry = "SELECT 
						mitarbeiter_uid
					FROM 
						lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_moodle USING(lehreinheit_id) 
					WHERE 
						moodle_version='1.9'
						AND mdl_course_id='".addslashes($mdl_course_id)."'
					UNION
					SELECT 
						mitarbeiter_uid 
					FROM 
						lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
						JOIN lehre.tbl_moodle USING(lehrveranstaltung_id) 
					WHERE 
						moodle_version='1.9'
						AND tbl_lehreinheit.studiensemester_kurzbz=tbl_moodle.studiensemester_kurzbz
						AND mdl_course_id='".addslashes($mdl_course_id)."'";
		}
		$mitarbeiter='';
		if($result_ma = $this->db_query($qry))
		{
			//Context des Kurses holen
			$mdlcourse = new moodle19_course();
			if(!$mdlcourse->getContext(50, $mdl_course_id))
			{
				$this->errormsg = 'Fehler beim Laden des Contexts';
				return false;
			}
			
			while($row_ma = $this->db_fetch_object($result_ma))
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
							$this->log.="\nder Lektor $this->mdl_user_firstname $this->mdl_user_lastname wurde zum Kurs hinzugefügt";
							$this->log_public.="\nder Lektor $this->mdl_user_firstname $this->mdl_user_lastname wurde zum Kurs hinzugefügt";
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
	
	/**
	 * Synchronisiert die Studenten der Lehreinheiten
	 * mit denen des Moodle Kurses
	 * @param $mdl_course_id ID des MoodleKurses
	 * @return true wenn ok, false wenn Fehler
	 */
	public function sync_studenten($mdl_course_id)
	{
		//Studentengruppen laden die zu diesem Kurs zugeteilt sind
		$qry = "SELECT 
					studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, tbl_moodle.studiensemester_kurzbz, tbl_moodle.gruppen
				FROM 
					lehre.tbl_lehreinheitgruppe JOIN lehre.tbl_moodle USING(lehreinheit_id) 
				WHERE 
					moodle_version='1.9'
					AND mdl_course_id='".addslashes($mdl_course_id)."'
				UNION
				SELECT 
					studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, tbl_moodle.studiensemester_kurzbz, tbl_moodle.gruppen
				FROM 
					lehre.tbl_lehreinheitgruppe JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
					JOIN lehre.tbl_moodle USING(lehrveranstaltung_id) 
				WHERE 
					moodle_version='1.9'
					AND tbl_lehreinheit.studiensemester_kurzbz=tbl_moodle.studiensemester_kurzbz
					AND mdl_course_id='".addslashes($mdl_course_id)."'";
		$studenten='';
		if($result_std = $this->db_query($qry))
		{
			//Context des Kurses holen
			$mdlcourse = new moodle19_course();
			if(!$mdlcourse->getContext(50, $mdl_course_id))
			{
				$this->errormsg = 'Fehler beim Laden des Contexts';
				return false;
			}
			
			while($row_std = $this->db_fetch_object($result_std))
			{
				//Schauen ob fuer diesen Kurs die Gruppen mitgesynct werden sollen
				$gruppensync = $row_std->gruppen=='t'?true:false;
				
				//Studenten dieser Gruppe holen

				if($row_std->gruppe_kurzbz=='') //LVB Gruppe
				{
					$qry = "SELECT
								distinct prestudent_id
							FROM
								public.tbl_studentlehrverband
							WHERE
								studiensemester_kurzbz=".$db->db_add_param($row_std->studiensemester_kurzbz)." AND
								studiengang_kz = ".$db->db_add_param($row_std->studiengang_kz)." AND
								semester = ".$db->db_add_param($row_std->semester);
					if(trim($row_std->verband)!='')
					{
						$qry.=" AND verband = '$row_std->verband'";
						if(trim($row_std->gruppe)!='')
						{
							$qry.=" AND gruppe = '$row_std->gruppe'";
						}
					}
					$studiengang_obj = new studiengang();
					$studiengang_obj->load($row_std->studiengang_kz);
					$gruppenbezeichnung = $studiengang_obj->kuerzel.'-'.trim($row_std->semester).trim($row_std->verband).trim($row_std->gruppe);
				}
				else //Spezialgruppe
				{
					$qry = "SELECT
								distinct uid as student_uid
							FROM
								public.tbl_benutzergruppe
							WHERE
								gruppe_kurzbz=".$this->db_add_param($row_std->gruppe_kurzbz)." AND
								studiensemester_kurzbz=".$this->db_add_param($row_std->studiensemester_kurzbz);
					$gruppenbezeichnung = $row_std->gruppe_kurzbz;
				}

				if($result_user = $this->db_query($qry))
				{
					while($row_user = $this->db_fetch_object($result_user))
					{
						if(isset($row_user->prestudent_id))
						{
							$guidps = new prestudent();
							$uid = $guidps->getUid($row_user->prestudent_id);
						}
						else
						{
							$uid = $row_user->student_uid;
						}

						//MoodleID des Users holen bzw ggf neu anlegen
						if(!$this->loaduser($uid))
						{
							//User anlegen
							if(!$this->createUser($uid))
							{
								$this->errormsg = "Fehler beim Anlegen des Users $uid: $this->errormsg";
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
									$this->log.="\nder Student $this->mdl_user_firstname $this->mdl_user_lastname wurde zum Kurs hinzugefügt";
									$this->log_public.="\nder Student $this->mdl_user_firstname $this->mdl_user_lastname wurde zum Kurs hinzugefügt";
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
						
						//Gruppenzuteilung
						if($gruppensync)
						{
							//Schauen ob die Gruppe vorhanden ist
							if(!$groupid = $this->getGroup($mdl_course_id, $gruppenbezeichnung))
							{
								//wenn nicht dann anlegen
								if(!$groupid = $this->createGroup($mdl_course_id, $gruppenbezeichnung))
									continue;
								$this->group_update++;
								$this->log.="\nes wurde eine neue Gruppe angelgt: $gruppenbezeichnung";
								$this->log_public.="\nes wurde eine neue Gruppe angelgt: $gruppenbezeichnung";
							}
							
							//Schauen ob eine Zuteilung zu dieser Gruppe vorhanden ist
							if(!$this->getGroupMember($groupid, $this->mdl_user_id))
							{
								//wenn nicht dann zuteilen
								$this->createGroupMember($groupid, $this->mdl_user_id);
								$this->group_update++;
								$this->log.="\nder Student $this->mdl_user_firstname $this->mdl_user_lastname wurde der Gruppe $gruppenbezeichnung zugeordnet";
								$this->log_public.="\nder Student $this->mdl_user_firstname $this->mdl_user_lastname wurde der Gruppe $gruppenbezeichnung zugeordnet";
							}
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
	
	/**
	 * Schaut ob eine Zuteilung von Person zu Gruppe
	 * existiert
	 * @param grouid ID der Gruppe
	 *        userid ID des Users
	 * @return ID der Zuteilung
	 */
	public function getGroupMember($groupid, $userid)
	{
		$qry = "SELECT id FROM public.mdl_groups_members WHERE groupid='".addslashes($groupid)."' AND userid='".addslashes($userid)."'";
		if($result = pg_query($this->conn_moodle, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				return $row->id;
			}
			else 
			{
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Ermitteln der Gruppe';
			return false;
		}
	}
	
	/**
	 * Legt eine Zuteilung eines Users zu 
	 * einer Gruppe an
	 * @param groupid ID der Gruppe
	 *        userid ID des Users
	 * @return ID der Zuteilung oder false im Fehlerfall
	 */
	public function createGroupMember($groupid, $userid)
	{
		$qry = 'BEGIN; INSERT INTO public.mdl_groups_members(groupid, userid) VALUES('.
				$this->addslashes($groupid).','.$this->addslashes($userid).');';
		if(pg_query($this->conn_moodle, $qry))
		{
			$qry = "SELECT currval('mdl_groups_members_id_seq') as id";
			if($result = pg_query($this->conn_moodle, $qry))
			{
				if($row = pg_fetch_object($result))
				{
					pg_query($this->conn_moodle, 'COMMIT;');
					return $row->id;
				}
				else 
				{
					pg_query($this->conn_moodle, 'ROLLBACK;');
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					return false;
				}
			}
			else 
			{
				pg_query($this->conn_moodle, 'ROLLBACK;');
				$this->errormsg = 'Fehler beim Auslesen der Sequence';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Anlegen der Zuteilung';
			return false;
		}
	}
	
	/**
	 * Holt die ID einer MoodleGruppe
	 * @param $mdl_course_id ID des Kurses
	 *        $gruppenbezeichnung Name der Gruppe
	 * @return GruppenID wenn ok, false im Fehlerfall
	 */
	public function getGroup($mdl_course_id, $gruppenbezeichnung)
	{
		$qry = "SELECT id FROM public.mdl_groups WHERE courseid='".addslashes($mdl_course_id)."' AND name='".addslashes($gruppenbezeichnung)."'";
		
		if($result = pg_query($this->conn_moodle, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				return $row->id;
			}
			else 
			{
				$this->errormsg = "Gruppe wurde nicht gefunden $gruppenbezeichnung";
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden einer Gruppe';
			return false;
		}
	}
	
	/**
	 * Legt eine MoodleGruppe zu einem Kurs an
	 * @param mdl_course_id ID des MoodleKuses
	 *        gruppenbezeichnung Bezeichnung der Gruppe
	 * @return ID der Gruppe wenn ok, false im Fehlerfall
	 */
	public function createGroup($mdl_course_id,  $gruppenbezeichnung)
	{
		$qry = 'BEGIN;INSERT INTO public.mdl_groups(courseid, name, description) VALUES('.
				$this->addslashes($mdl_course_id).','.
				$this->addslashes($gruppenbezeichnung).','.
				$this->addslashes($gruppenbezeichnung).');';
		if(pg_query($this->conn_moodle, $qry))
		{
			$qry = "SELECT currval('mdl_groups_id_seq') as id";
			if($result = pg_query($this->conn_moodle, $qry))
			{
				if($row = pg_fetch_object($result))
				{
					pg_query($this->conn_moodle, 'COMMIT;');
					return $row->id;
				}
				else 
				{
					pg_query($this->conn_moodle, 'ROLLBACK;');
					$this->errormsg = 'Fehler beim Auslesen der GruppenSequence';
					return false;
				}
			}
			else 
			{
				pg_query($this->conn_moodle, 'ROLLBACK;');
				$this->errormsg = 'Fehler beim Auslesen der GruppenSequence';
				return false;
			}
		}
		else 
		{
			$this->errormsgr ='Fehler beim Anlegen der Gruppe';
			return false;
		}
	}
		
	/**
	 * Legt einen User im Moodle an
	 * @param $uid UID der Person die angelegt werden soll
	 * @return true wenn ok, false wenn Fehler
	 */
	public function createUser($uid)
	{
		$qry = "SELECT uid, vorname, nachname FROM campus.vw_benutzer WHERE uid='".addslashes($uid)."'";
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
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
						$this->addslashes($username.'@'.DOMAIN).", 3, 1, 'de_utf8');";
				
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
	
	/**
	 * Teilt den User mit der ID $mdl_user_id zum
	 * Kurs mit der ContextID $mdl_context_id zu.
	 * @param $mdl_user_id Moodle ID des Users
	 *        $mdl_context_id ContextID des Kurses
	 *        $role Rolle der Zuteilung (1=Admin/3=Lektor/5=Student)
	 * @return true wenn ok, false wenn Fehler
	 */
	public function createZuteilung($mdl_user_id, $mdl_context_id, $role)
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
	
	/**
	 * Fuegt dem User die globale Gastrolle hinzu
	 * @param $mdl_user_id Moodle ID des Users der
	 *                     die GastRolle bekommt
	 * @return true wenn ok, false wenn Fehler
	 */
	public function createGlobaleGastrolle($mdl_user_id)
	{
	
		//Nachschauen ob diese Person bereits eine globale Gastrolle hat
		$qry = "SELECT 1 FROM public.mdl_role_assignments 
				WHERE 
				userid='".addslashes($mdl_user_id)."' AND 
				contextid='1' AND
				roleid='6'";
		
		if($result = pg_query($this->conn_moodle, $qry))
		{
			if(pg_num_rows($result)==0)
			{
				//noch nicht zugeteilt
				if($this->createZuteilung($mdl_user_id, 1, 6))
				{
					$this->log.="\n$this->mdl_user_firstname $this->mdl_user_lastname wurde die globale Gastrolle zugeteilt";
					$this->log_public.="\n$this->mdl_user_firstname $this->mdl_user_lastname wurde die globale Gastrolle zugeteilt";
				}
				else 
					$this->log.="\nFehler beim Anlegen der Gast-Zuteilung: $this->errormsg";
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Auslesen der Rollen';
			return false;
		}
	}
	
	/**
	 * Loescht die Zuteilung eines Users zu einem Kurs
	 * @param $mdl_user_id MoodleID des Users
	 *        $mdl_context_id ContextID des Users
	 * @return true wenn ok, false wenn Fehler
	 */
	public function deleteZuteilung($mdl_user_id, $mdl_context_id)
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

	/**
	 * Teilt die TestStudenten zu einem Testkurs zu
	 * @param mdl_course_id ID des Moodle Kurses
	 */
	public function createTestStudentenZuordnung($mdl_course_id)
	{
		//Context des Kurses holen
		$mdlcourse = new moodle19_course();
		if(!$mdlcourse->getContext(50, $mdl_course_id))
		{
			$this->errormsg = 'Fehler beim Laden des Contexts';
			return false;
		}
		
		$users = array('student1', 'student2', 'student3');
		foreach ($users as $row_user)
		{
			//MoodleID des Users holen
			if(!$this->loaduser($row_user))
			{
				$this->errormsg = "Fehler beim Laden des Users $row_user: $this->errormsg";
				return false;				
			}
					
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
						$this->log.="\nder Student $this->mdl_user_firstname $this->mdl_user_lastname wurde zum Kurs hinzugefügt";
						$this->log_public.="\nder Student $this->mdl_user_firstname $this->mdl_user_lastname wurde zum Kurs hinzugefügt";
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
		return true;
	}
}
