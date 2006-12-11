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
/**
 * Klasse funktionen (FAS-Online)
 * Verwaltet die Funktionen der Mitarbeiter
 * @create 07-03-2006
 */

class funktion
{
	var $conn;    				// @var resource DB-Handle
	var $errormsg; 			// @var string
	var $new;      				// @var boolean
	var $result = array(); 		// @var funktion Objekt
	
	//vars fuer Tabellenspalten
	var $funktion_id;        		// @var integer
	var $mitarbeiter_id;     		// @var integer
	var $studiensemester_id; 		// @var integer
	var $erhalter_id;        		// @var integer
	var $studiengang_id;     		// @var integer
	var $fachbereich_id;     		// @var integer
	var $name;       	        		// @var string
	var $funktion;          			// @var integer ( 0 = Mitarbeiter, 1 = Lektor, 2 = Fachbereichskoordinatior, 3 = Assistenz, 
	                         			//                4 = Rektor, 5 = Studiengangsleiter, 6 = Fachbereichsleiter)
	var $updateamum;         		// @var timestamp
	var $updatevon=0;          		// @var string
	var $beschart1;         		// @var integer ( 1 = Dienstverhaeltnis zum Bund, 2 = Dienstverhaeltnis zu einer anderen Gebietskoerperschaft,
	                            			//                3 = Echter Dienstvertrag, 4 = Freier Dienstvertrag, 5 = Lehre/Ausbildung, 6 = Sonstiges)
	var $beschart2;          		// @var integer ( 1 = befristet, 2 = unbefristet)
	var $verwendung;         		// @var integer
	var $hauptberuflich;     		// @var boolean
	var $hauptberuf;         		// @var integer
	var $entwicklungsteam;   		// @var boolean
	var $besonderequalifikation; 	// @var integer
	var $sws;                			// @var float wird nicht verwendet
	var $ausmass;            		// @var float ( 1 = Vollzeit, 2 = <=15 Wochenstd, 3 = 15-25 Wochenstd, 4 = 26-36 Wochenstd, 5 = Karenz)
	var $status;				// @var integer Aktstatus der Person (wird bei loeschen einer funktion gesetzt)
		
	/**
	 * Konstruktor
	 * @param $conn   Connection zur Datenbank
	 *        $fkt_id Id der zu ladenden Funktion (Default=null)
	 */
	function funktion($conn, $fkt_id=null)
	{
		$this->conn = $conn;
		$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
		if(!pg_query($conn,$qry))
		{
			$this->errormsg	 = "Encoding konnte nicht gesetzt werden";
			return false;
		}
		if($fkt_id != null)
			$this->load($fkt_id);
	}
	
	/**
	 * loescht die Funktion mit der uebergebenen ID
	 * @param $funktion_id ID des zu loeschenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($funktion_id)
	{
		//Pruefen ob funktion_id gueltig ist
		if(is_numeric($funktion_id) && $funktion_id != '')
		{
			//Person ermitteln
			$person_id=0;
			$mitarbeiter_id=0;
			$qry = "Select person_fk, mitarbeiter_pk from mitarbeiter join funktion on(mitarbeiter_pk=mitarbeiter_fk) where funktion_pk = $funktion_id";
			if($row=pg_fetch_object(pg_query($this->conn,$qry)))
			{
				$person_id = $row->person_fk;
				$mitarbeiter_id = $row->mitarbeiter_pk;
			}
			else 
			{
				$this->errormsg = 'Fehler beim ermitteln der Person';
				return false;
			}
			
			$qry = "DELETE FROM funktion WHERE funktion_pk=$funktion_id;";
			$sql = $qry;
			if(pg_query($this->conn,$qry))
			{
				//Neuen aktstatus ermitteln
				$qry = "Select aktstatus from person where person_pk=$person_id";
				if($row=pg_fetch_object(pg_query($this->conn,$qry)))
				{
					$aktstatus = $row->aktstatus;
					if($aktstatus!=150) //wenn er nicht ausgeschieden ist
					{
						//Funktionen holen
						$qry = "Select funktion from funktion where ".
						       "studiensemester_fk = (Select studiensemester_pk from studiensemester where aktuell='J')".
						       " AND mitarbeiter_fk	= '$mitarbeiter_id'";
						if($result = pg_query($this->conn, $qry))
						{
							$fkt=array();
							$i=0;
							while($row=pg_fetch_object($result))
							{
								$fkt[$i]=$row->funktion;
								$i++;
							}
							
							//Aktstatus ermitteln
							if(in_array(5,$fkt)) //STGL
								$aktstatus = 104;
							elseif(in_array(6,$fkt)) //FBL
								$aktstatus = 103;
							elseif(in_array(2,$fkt)) //FBK
								$aktstatus = 102;
							elseif(in_array(1,$fkt)) //LKT
								$aktstatus = 101;
							else
								$aktstatus = 100; //Mitarbeiter
								
							$this->status = $aktstatus;
							//neuen akstatus setzen
							$qry = "Update person set aktstatus = $aktstatus where person_pk = $person_id";
							if(pg_query($qry))
							{
								//Log schreiben
								$sql .= $qry;
								$qry = "SELECT nextval('log_seq') as id;";
								if(!$row = pg_fetch_object(pg_query($this->conn, $qry)))
								{
									$this->errormsg = 'Fehler beim Auslesen der Log-Sequence';
									return false;
								}
											
								$qry = "INSERT INTO log(log_pk, creationdate, creationuser, sql) VALUES('$row->id', now(), '$this->updatevon', '".addslashes($sql)."')";
								if(pg_query($this->conn, $qry))
									return true;
								else 
								{
									$this->errormsg = 'Fehler beim Speichern des Log-Eintrages';
									return false;
								}
							}
							else 
							{
								$this->errormsg = 'Fehler beim setzen des Aktstatus';
								return false;
							}
						}
					}
					else 
						return true;
						
				}
				else
				{
					$this->errormsg = 'Fehler beim Laden des aktuellen Status';
					return false;
				}
			}
			else 
			{
				$this->errormsg = 'Beim loeschen ist ein Fehler aufgetreten';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'funktion_id muss eine gueltige Zahl sein';
			return false;
		}		
	}
	
	
	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	function checkvars()
	{
		//Hochkomma und HTML Tags ersetzen
		//$this->name = htmlentities($this->name, ENT_QUOTES);
		
		//Maximallaenge pruefen
		$this->errormsg = 'Die Maximallaenge eines Feldes wurde ueberschritten';
		if(strlen($this->name)>255)           return false;
						
		//Zahlenwerte ueberpruefen
		/*
		$this->errormsg = 'Ein Zahlenfeld enthaelt ungueltige Zeichen';
		
		if(!is_numeric($this->funktion))    return false;
		if(!is_numeric($this->beschart1))   return false;
		if(!is_numeric($this->beschart2))   return false;
		if(!is_numeric($this->verwendung))  return false;
		if(!is_numeric($this->hauptberuf))  return false;
		if(!is_numeric($this->sws))         $this->sws=0;
		if(!is_numeric($this->ausmass))     return false;
		if(!is_numeric($this->mitarbeiter_id)) return false;
		if(!is_numeric($this->erhalter_id))    return false;
		if(!is_numeric($this->studiengang_id)) return false;
		if(!is_numeric($this->fachbereich_id)) return false;
		if(!is_numeric($this->studiensemester_id))     return false;
		if(!is_numeric($this->besonderequalifikation)) return false;
		*/			
		$this->errormsg = '';
		return true;
	}
	
	/**
	 * Speichert die Daten in die Datenbank
	 * @return true wenn OK, false im Fehlerfall
	 */	 
	function save()
	{
		if(!$this->checkvars())
			return false;
			
		//neuen aktstatus ermitteln
		if($status=$this->getaktstatus())
			$statusqry = "Update person SET aktstatus=$status where person_pk = (Select person_fk from mitarbeiter where mitarbeiter_pk='$this->mitarbeiter_id');";
		else 
			$statusqry = "";
			
		if($this->new)
		{
			//Naechste ID aus der Sequence holen
			$qry = "SELECT nextval('funktion_seq') as id;";
			if(!$row = pg_fetch_object(pg_query($this->conn,$qry)))
			{
				$this->errormsg = 'Sequence konnte nicht ausgelesen werden';
				return false;
			}
			$this->funktion_id = $row->id;
			
			$qry= $statusqry."INSERT INTO funktion (funktion_pk, mitarbeiter_fk, studiensemester_fk, erhalter_fk, studiengang_fk,".
			     " fachbereich_fk, name, funktion, creationdate, creationuser, beschart1, beschart2, verwendung,".
			     " hauptberuflich, hauptberuf, entwicklungsteam, besonderequalifikation, sws, ausmass) VALUES(".
			     " '$this->funktion_id','$this->mitarbeiter_id', '$this->studiensemester_id', '$this->erhalter_id',".
			     ($this->studiengang_id!=''?" '$this->studiengang_id'":'null').",".
			     ($this->fachbereich_id!=''?" '$this->fachbereich_id'":'null').",".
			     ($this->name!=''?" '$this->name'":'null').",".
			     ($this->funktion!=''?" '$this->funktion'":'null').", now(), $this->updatevon,".
			     ($this->beschart1!=''?" '$this->beschart1'":'null').",".
			     ($this->beschart2!=''?" '$this->beschart2'":'null').",".
			     ($this->verwendung!=''?" '$this->verwendung'":'null').", '".($this->hauptberuflich?'J':'N')."',".
			     ($this->hauptberuf!=''?" '$this->hauptberuf'":'null').", '".($this->entwicklungsteam?'J':'N')."',".
			     ($this->besonderequalifikation!=''?" '$this->besonderequalifikation'":'null').", null,".
			     ($this->ausmass!=''?" '$this->ausmass'":'null').")";
		
		}
		else 
		{
			if(!is_numeric($this->mitarbeiter_id) && !is_numeric($this->funktion_id))
			{
				$this->errormsg = 'mitarbeiter_id und funktion_id muessen eine gueltige Zahl sein';
				return false;
			}
			
			$qry= $statusqry. "UPDATE funktion SET ".
				 " studiensemester_fk=".($this->studiensemester_id!=''?"'$this->studiensemester_id'":'null').",".
				 " erhalter_fk=".($this->erhalter_id!=''?"'$this->erhalter_id'":'null').",".
			     " studiengang_fk=".($this->studiengang_id!=''?"'$this->studiengang_id'":'null').",".
			     " fachbereich_fk=".($this->fachbereich_id!=''?"'$this->fachbereich_id'":'null').",".
			     " name=".($this->name!=''?"'$this->name'":'null').",".
			     " funktion=".($this->funktion!=''?"'$this->funktion'":'null').",".
			     " beschart1=".($this->beschart1!=''?"'$this->beschart1'":'null').",".
			     " beschart2=".($this->beschart2!=''?"'$this->beschart2'":'null').",".
			     " verwendung=".($this->verwendung!=''?"'$this->verwendung'":'null').",".
			     " hauptberuflich='".($this->hauptberuflich?'J':'N')."',".
			     " hauptberuf=".($this->hauptberuf!=''?"'$this->hauptberuf'":'null').",".
			     " entwicklungsteam='".($this->entwicklungsteam?'J':'N')."',".
			     " besonderequalifikation=".($this->besonderequalifikation!=''?"'$this->besonderequalifikation'":'null').",".
			     " sws=".($this->sws!=''?"'$this->sws'":'null').",".
			     " ausmass=".($this->ausmass!=''?"'$this->ausmass'":'null').
			     " WHERE funktion_pk=$this->funktion_id"; // AND mitarbeiter_fk=$this->mitarbeiter_id";
		}
		
		if(pg_query($this->conn,$qry))
		{
			$qry = "UPDATE funktion SET hauptberuflich='".($this->hauptberuflich?'J':'N')."', hauptberuf=".($this->hauptberuf!=''?"'$this->hauptberuf'":'null')." WHERE mitarbeiter_fk ='$this->mitarbeiter_id' AND studiensemester_fk='$this->studiensemester_id'";
			if(!pg_query($this->conn,$qry))
			{
				$this->errormsg = 'Fehler beim Updaten der Funktionen';
				return false;
			}
			//Log schreiben
			$sql = $qry;
			$qry = "SELECT nextval('log_seq') as id;";
			if(!$row = pg_fetch_object(pg_query($this->conn, $qry)))
			{
				$this->errormsg = 'Fehler beim Auslesen der Log-Sequence';
				return false;
			}
						
			$qry = "INSERT INTO log(log_pk, creationdate, creationuser, sql) VALUES('$row->id', now(), '$this->updatevon', '".addslashes($sql)."')";
			if(pg_query($this->conn, $qry))
				return true;
			else 
			{
				$this->errormsg = 'Fehler beim Speichern des Log-Eintrages';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Speichern der Daten'.$qry;
			return false;
		}
	}
	
	/**
	 * Ermittelt den neuen aktstatus einer Person
	 */
	function getaktstatus()
	{
		$aktstatus=100;
		//Aktuellen Status holen
		$qry = "Select aktstatus from person join mitarbeiter on(person_fk=person_pk) where mitarbeiter_pk='".$this->mitarbeiter_id."'";
		if($result = pg_query($this->conn,$qry))
		{
			if($row = pg_fetch_object($result))
				$aktstatus = $row->aktstatus;
			else 
			{
				$this->errormsg = 'Fehler beim Laden des aktuellen Status';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden des aktuellen Status';
			return false;
		}
		
		/*
		//Wenn die Funktion das aktuelle Studiensemester betrifft
		$qry = "Select studiensemester_pk from studiensemester where aktuell='J'";
		if($result = pg_query($this->conn,$qry))
		{
			if($row=pg_fetch_object($result))
			{
				if($row->studiensemester_pk == $this->studiensemester_id)
				{
		*/
					//Neuen Status setzen
					if($this->funktion == 1 && $aktstatus < 101) //Lektor
						$aktstatus = 101;
					elseif($this->funktion == 2 && $aktstatus < 102) //Fachbereichskoordinator
						$aktstatus = 102;
					elseif($this->funktion == 6 && $aktstatus < 103) //Fachbereichsleiter
						$aktstatus = 103;
					elseif($this->funktion == 5 && $aktstatus < 104) //Studiengangsleiter
						$aktstatus = 104;						
		/*		}
			}
			else 
			{
				$this->errormsg = 'Fehler beim Laden des aktuellen Studiensemesters';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden des aktuellen Studiensemesters';
			return false;
		}*/
		$this->status = $aktstatus;
		return $aktstatus;		
	}
	
	/**
	 * Laedt eine Funktion aus der DB
	 * @param  $fkt_id  ID der zu ladenden Funktion
	 * @return true wenn erfolgreich geladen, false im Fehlerfall
	 */
	function load($fkt_id)
	{
		//Pruefen ob fkt_id gueltig ist
		if(!is_numeric($fkt_id))
		{
			$this->errormsg = 'funktion_id muss eine Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM funktion WHERE funktion_pk=$fkt_id";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}
		
		if($row = pg_fetch_object($res))
		{						
			$this->funktion_id            = $row->funktion_pk;
			$this->mitarbeiter_id         = $row->mitarbeiter_fk;
			$this->studiensemester_id     = $row->studiensemester_fk;
			$this->erhalter_id            = $row->erhalter_fk;
			$this->studiengang_id         = $row->studiengang_fk;
			$this->fachbereich_id         = $row->fachbereich_fk;
			$this->name                   = $row->name;
			$this->funktion               = $row->funktion;
			$this->updateamum             = $row->creationdate;
			$this->updatevon              = $row->creationuser;
			$this->beschart1              = $row->beschart1;
			$this->beschart2              = $row->beschart2;
			$this->verwendung             = $row->verwendung;
			$this->hauptberuflich         = ($row->hauptberuflich=='J'?true:false);
			$this->hauptberuf             = $row->hauptberuf;
			$this->entwicklungsteam       = ($row->entwicklungsteam=='J'?true:false);
			$this->besonderequalifikation = $row->besonderequalifikation;
			$this->sws                    = $row->sws;
			$this->ausmass                = $row->ausmass;
		}
		else 
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}
		
		return true;
	}
	
	/**
	 * Laedt die Funktion(en) eines Mitarbeiters
	 * @param  $ma_id  ID des zu ladenden Mitarbeiters
	 * @return true wenn erfolgreich geladen, false im Fehlerfall
	 */
	function load_pers($ma_id, $stsem='')
	{
		//pruefen ob ma_id gueltig ist
		if(!is_numeric($ma_id))
		{
			$this->errormsg = 'mitarbeiter_id muss eine Zahl sein';
			return false;
		}
		
		$qry="SELECT * FROM funktion WHERE mitarbeiter_fk=$ma_id";
		if($stsem!='')
			$qry.= " AND studiensemester_fk='$stsem'";
		$qry.=" ORDER BY studiensemester_fk DESC, funktion_pk";
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$fkt_obj = new funktion($this->conn);
			
			$fkt_obj->funktion_id            = $row->funktion_pk;
			$fkt_obj->mitarbeiter_id         = $row->mitarbeiter_fk;
			$fkt_obj->studiensemester_id     = $row->studiensemester_fk;
			$fkt_obj->erhalter_id            = $row->erhalter_fk;
			$fkt_obj->studiengang_id         = $row->studiengang_fk;
			$fkt_obj->fachbereich_id         = $row->fachbereich_fk;
			$fkt_obj->name                   = $row->name;
			$fkt_obj->funktion               = $row->funktion;
			$fkt_obj->updateamum             = $row->creationdate;
			$fkt_obj->updatevon              = $row->creationuser;
			$fkt_obj->beschart1              = $row->beschart1;
			$fkt_obj->beschart2              = $row->beschart2;
			$fkt_obj->verwendung             = $row->verwendung;
			$fkt_obj->hauptberuflich         = ($row->hauptberuflich=='J'?true:false);
			$fkt_obj->hauptberuf             = $row->hauptberuf;
			$fkt_obj->entwicklungsteam       = ($row->entwicklungsteam=='J'?true:false);
			$fkt_obj->besonderequalifikation = $row->besonderequalifikation;
			$fkt_obj->sws                    = $row->sws;
			$fkt_obj->ausmass                = $row->ausmass;
			
			$this->result[] = $fkt_obj;
		}
			
		return true;
	}
	
	function getMitarbeiter($stg,$fb,$funktion,$stsem=null)
	{
		$qry = "SELECT 
					mitarbeiter_fk 
				FROM 
					funktion 
				WHERE 
					studiengang_fk='$stg' AND 
					fachbereich_fk='$fb' 
				GROUP BY mitarbeiter_fk";
		if($result = pg_query($this->conn,$qry))
		{			
			while($row = pg_fetch_object($result))
			{
				$fkt = new funktion($this->conn);
				$fkt->mitarbeiter_id = $row->mitarbeiter_fk;
				$this->result[] = $fkt;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim laden der Mitarbeiter';
			return false;
		}
	}
	
	/**
	 * Laedt alle Funktionen
	 * @return true wenn erfolgreich geladen, false im Fehlerfall
	 */
	function getAll()
	{
		/*Eventuell Speicherprobleme
		
		$qry = "SELECT * FROM funktion";
			
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}
		
		while($row=pg_fetch_object($res))
		{
			$fkt_obj = new funktion($this->conn);
			
			$fkt_obj->funktion_id            = $row->funktion_pk;
			$fkt_obj->mitarbeiter_id         = $row->mitarbeiter_fk;
			$fkt_obj->studiensemester_id     = $row->studiensemester_fk;
			$fkt_obj->erhalter_id            = $row->erhalter_fk;
			$fkt_obj->studiengang_id         = $row->studiengang_fk;
			$fkt_obj->fachbereich_id         = $row->fachbereich_fk;
			$fkt_obj->name                   = $row->name;
			$fkt_obj->funktion               = $row->funktion;
			$fkt_obj->updateamum             = $row->creationdate;
			$fkt_obj->updatevon              = $row->creationuser;
			$fkt_obj->beschart1              = $row->beschart1;
			$fkt_obj->beschart2              = $row->beschart2;
			$fkt_obj->verwendung             = $row->verwendung;
			$fkt_obj->hauptberuflich         = ($row->hauptberuflich=='J'?true:false);
			$fkt_obj->hauptberuf             = $row->hauptberuf;
			$fkt_obj->entwicklungsteam       = ($row->entwicklungsteam=='J'?true:false);
			$fkt_obj->besonderequalifikation = $row->besonderequalifikation;
			$fkt_obj->sws                    = $row->sws;
			$fkt_obj->ausmass                = $row->ausmass;
			
			$this->result[] = $fkt_obj;
		}
		return true;
		*/
		return false;
	}	
	
	function FunktionExists($mitarbeiter_id, $studiengang_id, $fachbereich_id, $studiensemester_id, $funktion)
	{
		$qry = "SELECT 
					count(*) as anzahl
				FROM 
					funktion 
				WHERE 
					mitarbeiter_fk='$mitarbeiter_id' AND 
					studiengang_fk='$studiengang_id' AND 
					fachbereich_fk='$fachbereich_id' AND 
					studiensemester_fk = '$studiensemester_id' AND 
					funktion='$funktion'";
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				if($row->anzahl>0)
					return true;
				else
				{
					return false;
				}
			}
			else 
			{
				return false;
				$this->errormsg = 'Fehler beim auslesen der Funktionen';
			}
		}
		else 
		{
			return false;
			$this->errormsg = 'Fehler beim auslesen der Funktionen';
		}
	}
	
	function getNameFunktion($id)
	{
		switch($id)
		{
			case 0: return 'Mitarbeiter';
			case 1: return 'Lektor';
			case 2: return 'Fachbereichskoordinatior';
			case 3:	return 'Assistenz';
			case 4: return 'Rektor';
			case 5: return 'Studiengangsleiter';
			case 6: return 'Fachbereichsleiter';
			default: return '';
		}
	}
	
	function getNameBeschart1($id)
	{
		switch($id)
		{			
			case 1: return 'Dienstverhältnis zum Bund';
			case 2: return 'Dienstverhältnis zu einer anderen Gebietskörperschaft';
			case 3:	return 'Echter Dienstvertrag';
			case 4: return 'Freier Dienstvertrag';
			case 5: return 'Lehr- oder Ausbildungsverhältnis';
			case 6: return 'Sonstiges Beschäftigungsverhältnis';
			default: return '';
		}
	}
	
	function getNameBeschart2($id)
	{
		switch($id)
		{			
			case 1: return 'befristet';
			case 2: return 'unbefristet';
			default: return '';
		}
	}
	
	function getNameVerwendung($id)
	{
		switch($id)
		{			
			case 1: return 'Lehr- und Forschungspersonal';
			case 2: return 'Lehr- und Forschungshilfspersonal';
			case 3: return 'Akademische dienste für Studierende';
			case 4: return 'Soziale Dienste und Gesundheitsdienste';
			case 5: return 'Studiengangsleiter/in';
			case 6: return 'Leiter/in FH-Kollegium';
			case 7: return 'Management';
			case 8: return 'Verwaltung';
			case 9: return 'Hauspersonal, Gebäude-/Haustechnik';
			default: return '';
		}
		
	}
	
	function getNameHauptberuf($id)
	{
		switch($id)
		{			
			case '': return '';
			case 0: return 'Universität';
			case 1: return 'Fachhochschule';
			case 2: return 'Andere postsekundäre Bildungseinrichtung';
			case 3: return 'Allgemeinbildende höhere Schule';
			case 4: return 'Berufsbildende höhere Schule';
			case 5: return 'Andere Schule';
			case 6: return 'Öffentlicher Sektor';
			case 7: return 'Unternehmenssektor';
			case 8: return 'Freiberuflich tätig';
			case 9: return 'Privater gemeinnütziger Sektor';
			case 10: return 'Ausserhochschulische Forschungseinrichtung';
			case 11: return 'Internationale Organisation';
			case 12: return 'Sonstiges';
			default: return '';
		}
	}
	
	function getNameBesonderequalifikation($id)
	{
		switch($id)
		{			
			case 0: return 'keine';
			case 1: return 'Habilitation';
			case 2: return 'der Habilitation gleichwertige Qualifikation';
			case 3: return 'berufliche Tätigkeit';
			default: return '';
		}
	}
	
	function getNameAusmass($id)
	{
		switch($id)
		{			
			case 1: return 'Vollzeit';
			case 2: return '<= 15 Wochenstunden';
			case 3: return '16 - 25 Wochenstunden';
			case 4: return '26 - 35 Wochenstunden';
			case 5: return 'Karenz';
			default: return '';
		}
	}
}
?>