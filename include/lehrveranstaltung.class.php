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
 * Klasse lehrveranstaltung (FAS-Online)
 * @create 06-12-2006
 */
class lehrveranstaltung
{
	var $conn;					// @var resource DB-Handle
	var $errormsg;				// @var string
	var $new;					// @var boolean
	var $lehrveranstaltungen = array();	// @var lehrveranstaltung Objekt	
	
	var $lehrveranstaltung_id;			// @var serial
	var $studiengang_kz;		  	//@var integer
	var $bezeichnung;   				//@var string
	var $kurzbz;   				//@var string
	var $semester;  		 		//@var smallint
	var $ects;   					//@var numeric(5,2)
	var $semesterstunden;   			//@var smallint

	var $anmerkung;   				//@var string
	var $lehre;  					//@var boolean
	var $lehreverzeichnis;   			//@var string
	var $aktiv;   					//@var boolean
	var $ext_id;   					//@var bigint
	var $insertamum;   				//@var timestamp
	var $insertvon;   				//@var string
	var $planfaktor;   				//@var numeric(3,2)
	var $planlektoren;   				//@var integer
	var $planpersonalkosten;  			//@var numeric(7,2)
	var $updateamum;   				//@var timestamp
	var $updatevon;   				//@var string
	
	
	/**
	 * Konstruktor
	 * @param $conn Connection zur Datenbank
	 *        $lehrveranstaltung_id ID der zu ladenden Lehrveranstaltung
	 */
	function lehrveranstaltung($conn, $lehrveranstaltung_id=null)
	{
		$this->conn = $conn;
		if($lehrveranstaltung_id != null)
			$this->load($lehrveranstaltung_id);
	}
	
	/**
	 * Laedt einen Datensatz
	 * @param $lehrveranstaltung_id  ID des zu ladenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($lehrveranstaltung_id)
	{
		
		return true;		
	}
	
	/**
	 * Liefert alle Lehrveranstaltungen
	 * @return true wenn ok, false im Fehlerfall
	 */
	function getAll()
	{						
		$qry = "SELECT * FROM lehre.tbl_lehrveranstaltung;";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$lv_obj = new lehrveranstaltung($this->conn);
			
			$lv_obj->lehrveranstaltung_id=$row->lehrveranstaltung_id;
			$lv_obj->studiengang_kz=$row->studiengang_kz;
			$lv_obj->bezeichnung=$row->bezeichnung;
			$lv_obj->kurzbz=$row->kurzbz;
			$lv_obj->semester=$row->semester;
			$lv_obj->ects=$row->ects;
			$lv_obj->semesterstunden=$row->semesterstunden;
			$lv_obj->anmerkung=$row->anmerkung;
			$lv_obj->lehre=($row->lehre=='t'?true:false);
			$lv_obj->lehreverzeichnis=$row->lehreverzeichnis;
			$lv_obj->aktiv=($row->aktiv=='t'?true:false);
			$lv_obj->ext_id=$row->ext_id;
			$lv_obj->insertamum=$row->insertamum;
			$lv_obj->insertvon=$row->insertvon;
			$lv_obj->planfaktor=$row->planfaktor;
			$lv_obj->planlektoren=$row->planlektoren;
			$lv_obj->planpersonalkosten=$row->planpersonalkosten;
			$lv_obj->updateamum=$row->updateamum;
			$lv_obj->updatevon=$row->updatevon;
			
			$this->lehrveranstaltungen[] = $lv_obj;
		}		
		
		return true;		
	}
	
	/**
	 * Liefert alle Lehrveranstaltungen zu einem Studiengang/Studiensemester/Ausbildungssemester
	 * @param $studiengang_id ID des Studienganges
	 *        $studiensemester_id ID des Studiensemesters (optional)
	 *        $ausbildungssemester_id ID des ausbildungssemesters (optional)
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load_lva($studiengang_id, $ausbildungssemester_id=null, $lehre=null)
	{						
		//Variablen pruefen
		if(!is_numeric($studiengang_id) || $studiengang_id =='')
		{
			$this->errormsg = 'studiengang_id muss eine gueltige Zahl sein';
			return false;
		}
		if($ausbildungssemester_id != null && (!is_numeric($ausbildungssemester_id) || $ausbildungssemester_id == ''))
		{
			$this->errormsg = 'ausbildungssemester_id muss eine gueltige Zahl sein';
			return false;
		}
		if($lehre!=null && !is_bool($lehre))
		{
			$this->errormsg = 'Lehre muss ein boolscher Wert sein';
			return false;
		}
		
		//Select Befehl zusammenbauen
		$qry = "SELECT * FROM lehre.tbl_lehrveranstaltung WHERE studiengang_kz = '$studiengang_id'";
		
		if($ausbildungssemester_id != null)
			$qry .= " AND semester = '$ausbildungssemester_id'";
		if($lehre!=null)
			$qry .= " AND lehre=".($lehre?'true':'false');
			
		$qry .= " ORDER BY bezeichnung";
		
		//Datensaetze laden
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$lv_obj = new lehrveranstaltung($this->conn);
			
			$lv_obj->lehrveranstaltung_id=$row->lehrveranstaltung_id;
			$lv_obj->studiengang_kz=$row->studiengang_kz;
			$lv_obj->bezeichnung=$row->bezeichnung;
			$lv_obj->kurzbz=$row->kurzbz;
			$lv_obj->semester=$row->semester;
			$lv_obj->ects=$row->ects;
			$lv_obj->semesterstunden=$row->semesterstunden;
			$lv_obj->anmerkung=$row->anmerkung;
			$lv_obj->lehre=($row->lehre=='t'?true:false);
			$lv_obj->lehreverzeichnis=$row->lehreverzeichnis;
			$lv_obj->aktiv=($row->aktiv=='t'?true:false);
			$lv_obj->ext_id=$row->ext_id;
			$lv_obj->insertamum=$row->insertamum;
			$lv_obj->insertvon=$row->insertvon;
			$lv_obj->planfaktor=$row->planfaktor;
			$lv_obj->planlektoren=$row->planlektoren;
			$lv_obj->planpersonalkosten=$row->planpersonalkosten;
			$lv_obj->updateamum=$row->updateamum;
			$lv_obj->updatevon=$row->updatevon;
			
			$this->lehrveranstaltungen[] = $lv_obj;
		}	
		
		return true;		
	}
	function addslashes($var)
	{
		return ($var!=''?"'".addslashes($var)."'":'null');
	}
	
	/**
	 * Prueft die Gueltigkeit der Variablen
	 * @return true wenn ok, false im Fehlerfall
	 */
	function checkvars()
	{	
		//$this->name = str_replace("'",'´',$this->name);
		$this->bezeichnung = str_replace("'",'´',$this->bezeichnung);
		$this->kurzbz = str_replace("'",'´',$this->kurzbz);
		$this->anmerkung = str_replace("'",'´',$this->anmerkung);
		
		//Laenge Pruefen
		if(strlen($this->bezeichnung)>128)           
		{
			$this->errormsg = "Bezeichnung darf nicht laenger als 128 Zeichen sein bei <b>$this->ext_id</b> - $this->bezeichnung";
			return false;
		}
		if(strlen($this->kurzbz)>16)
		{
			$this->errormsg = "Kurzbez darf nicht laenger als 16 Zeichen sein bei <b>$this->ext_id</b> - $this->kurzbz";
			return false;
		}
		if(strlen($this->anmerkung)>64)
		{
			$this->errormsg = "Anmerkung darf nicht laenger als 64 Zeichen sein bei <b>$this->ext_id</b> - $this->anmerkung";
			return false;
		}
		if(strlen($this->lehreverzeichnis)>16)
		{
			$this->errormsg = "Lehreverzeichnis darf nicht laenger als 16 Zeichen sein bei <b>$this->ext_id</b> - $this->lehreverzeichnis";
			return false;
		}
		if(!is_numeric($this->studiengang_kz))         
		{
			$this->errormsg = "Studiengang_kz ist ungueltig bei <b>$this->ext_id</b> - $this->studiengang_kz";
			return false;
		}
		if($this->semester!='' && !is_numeric($this->semester))
		{
			$this->errormsg = "Semester ist ungueltig bei <b>$this->ext_id</b> - $this->semester";
			return false;
		}
		if($this->planfaktor!='' && !is_numeric($this->planfaktor))
		{
			$this->errormsg = "Planfaktor ist ungueltig bei <b>$this->ext_id</b> - $this->planfaktor";
			return false;
		}
		if($this->semesterstunden!='' && !is_numeric($this->semesterstunden)) 
		{
			$this->errormsg = "Semesterstunden ist ungueltig bei <b>$this->ext_id</b> - $this->semesterstunden";
			return false;
		}
		if($this->planlektoren!='' && !is_numeric($this->planlektoren))
		{
			$this->errormsg = "Planlektoren ist ungueltig bei <b>$this->ext_id</b> - $this->planlektoren";
			return false;
		}
		if($this->ects!='' && !is_numeric($this->ects))
		{
			$this->errormsg = "ECTS sind ungueltig bei <b>$this->ext_id</b> - $this->ects";
			return false;
		}		
		if($this->ects>40)
		{
			$this->errormsg = "ECTS größer als 40 bei <b>$this->ext_id</b> - $this->ects";
			return false;
		}		
		$this->errormsg = '';
		return true;		
	}
	
	/**
	 * Speichert den aktuellen Datensatz
	 * @return true wenn ok, false im Fehlerfall
	 */
	function save()
	{
		//Gueltigkeit der Variablen pruefen
		if(!$this->checkvars())
			return false;
			
		if($this->new)
		{
			//Neuen Datensatz anlegen
			$qry = 'INSERT INTO lehre.tbl_lehrveranstaltung (studiengang_kz, bezeichnung, kurzbz, 
				semester, ects, semesterstunden,  anmerkung, lehre, lehreverzeichnis, aktiv, ext_id, insertamum, 
				insertvon, planfaktor, planlektoren, planpersonalkosten, updateamum, updatevon) VALUES ('.
				$this->addslashes($this->studiengang_kz).', '.
				$this->addslashes($this->bezeichnung).', '.
				$this->addslashes($this->kurzbz).', '. 
				$this->addslashes($this->semester).', '.
				$this->addslashes($this->ects).', '.
				$this->addslashes($this->semesterstunden).', '. 
				$this->addslashes($this->anmerkung).', '.
				($this->lehre?'true':'false').','.
				$this->addslashes($this->lehreverzeichnis).', '.
				($this->aktiv?'true':'false').', '.
				$this->addslashes($this->ext_id).', '.
				$this->addslashes($this->insertamum).', '.
				$this->addslashes($this->insertvon).', '.
				$this->addslashes($this->planfaktor).', '.
				$this->addslashes($this->planlektoren).', '.
				$this->addslashes($this->planpersonalkosten).', '.
				$this->addslashes($this->updateamum).', '.
				$this->addslashes($this->updatevon).');';
		}
		else 
		{
			//bestehenden Datensatz akualisieren
			
			//Pruefen ob lehrveranstaltung_id eine gueltige Zahl ist
			if(!is_numeric($this->lehrveranstaltung_id) || $this->lehrveranstaltung_id == '')
			{
				$this->errormsg = 'lehrveranstaltung_id muss eine gueltige Zahl sein';
				return false;
			}
			$qry = 'UPDATE lehre.tbl_lehrveranstaltung SET '. 
				//'lehrveranstaltung_id= '.$this->addslashes($this->lehrveranstaltung_id) .', '.
				'studiengang_kz='.$this->addslashes($this->studiengang_kz) .', '.
				'bezeichnung='.$this->addslashes($this->bezeichnung) .', '.
				'kurzbz='.$this->addslashes($this->kurzbz) .', '.
				'semester='.$this->addslashes($this->semester) .', '.
				'ects='.$this->addslashes($this->ects) .', '.
				'semesterstunden='.$this->addslashes($this->semesterstunden) .', '.
				'anmerkung='.$this->addslashes($this->anmerkung) .', '.
				'lehre='.$this->addslashes($this->lehre) .', '.
				'lehreverzeichnis='.$this->addslashes($this->lehreverzeichnis) .', '.
				'aktiv='.($this->aktiv?'true':'false') .', '.
				'ext_id='.$this->addslashes($this->ext_id) .', '.
				'insertamum='.$this->addslashes($this->insertamum) .', '.
				'insertvon='.$this->addslashes($this->insertvon) .', '.
				'planfaktor='.$this->addslashes($this->planfaktor) .', '.
				'planlektoren='.$this->addslashes($this->planlektoren) .', '.
				'planpersonalkosten='.$this->addslashes($this->planpersonalkosten) .', '.
				'updateamum='.$this->addslashes($this->updateamum) .','.
				'updatevon='.$this->addslashes($this->updatevon) .' '.
				'WHERE ext_id = '.$this->addslashes($this->lehrveranstaltung_id).';';
		}
		
		if(pg_query($this->conn, $qry))
		{
			//Log schreiben
			/*$sql = $qry;
			$qry = "SELECT nextval('log_seq') as id;";
			if(!$row = pg_fetch_object(pg_query($this->conn, $qry)))
			{
				$this->errormsg = 'Fehler beim Auslesen der Log-Sequence';
				return false;
			}
						
			$qry = "INSERT INTO log(log_pk, creationdate, creationuser, sql) VALUES('$row->id', now(), '$this->updatevon', '".$this->addslashes($sql)."')";
			if(pg_query($this->conn, $qry))
				return true;
			else 
			{
				$this->errormsg = 'Fehler beim Speichern des Log-Eintrages';
				return false;
			}*/
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}		
	}
	
	/**
	 * Loescht einen Datensatz
	 * @param $lehrveranstaltung_id ID des zu loeeschenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($lehrveranstaltung_id)
	{
		//lehrveranstaltung_id auf gueltigkeit pruefen
		if(!is_numeric($lehrveranstaltung_id) || $lehrveranstaltung_id == '')
		{
			$this->errormsg = 'lehrveranstaltung_id muss eine gueltige Zahl sein';
			return false;
		}
		
		//Loeschen des Datensatzes
		$qry = "DELETE FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_pk = '$lehrveranstaltung_id';";
		
		if(pg_query($this->conn, $qry))
		{
			//Log schreiben
			$sql = $qry;
			$qry = "SELECT nextval('log_seq') as id;";
			if(!$row = pg_fetch_object(pg_query($this->conn, $qry)))
			{
				$this->errormsg = 'Fehler beim Auslesen der Log-Sequence';
				return false;
			}
						
			$qry = "INSERT INTO log(log_pk, creationdate, creationuser, sql) VALUES('$row->id', now(), '$this->updatevon', '".$this->addslashes($sql)."')";
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
			$this->errormsg = 'Fehler beim loeschen des Datensatzes';
			return false;
		}
	}
}
?>