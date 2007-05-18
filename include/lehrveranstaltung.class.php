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
class lehrveranstaltung
{
	var $conn;					// @var resource DB-Handle
	var $errormsg;				// @var string
	var $new;					// @var boolean
	var $lehrveranstaltungen = array();	// @var lehrveranstaltung Objekt	
	
	var $lehrveranstaltung_id;	// @var serial
	var $studiengang_kz;		//@var integer
	var $bezeichnung;   		//@var string
	var $kurzbz;   				//@var string
	var $semester;  		 	//@var smallint
	var $ects;   				//@var numeric(5,2)
	var $semesterstunden;   	//@var smallint

	var $anmerkung;				//@var string
	var $lehre;					//@var boolean
	var $lehreverzeichnis;		//@var string
	var $aktiv;					//@var boolean
	var $ext_id;				//@var bigint
	var $insertamum;			//@var timestamp
	var $insertvon;				//@var string
	var $planfaktor;			//@var numeric(3,2)
	var $planlektoren;			//@var integer
	var $planpersonalkosten;	//@var numeric(7,2)
	var $plankostenprolektor;	//@var numeric(6,2)
	var $updateamum;			//@var timestamp
	var $updatevon;				//@var string
	var $sprache;				//@var varchar(16)

	/**
	 * Konstruktor
	 * @param $conn Connection zur Datenbank
	 *        $lehrveranstaltung_id ID der zu ladenden Lehrveranstaltung
	 */
	function lehrveranstaltung($conn, $lehrveranstaltung_id=null, $unicode=false)
	{
		$this->conn = $conn;
		
		if($unicode!=null)
		{
			if($unicode)
				$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
			else
				$qry = "SET CLIENT_ENCODING TO 'LATIN9';";
		
			if(!pg_query($conn,$qry))
			{
				$this->errormsg	 = 'Encoding konnte nicht gesetzt werden';
				return false;
			}
		}
				
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
		if(!is_numeric($lehrveranstaltung_id))
		{
			$this->errormsg = 'Lehrveranstaltung_id muss eine gueltige Zahl sein';
			return false;
		}
		$qry = "SELECT * FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id='$lehrveranstaltung_id';";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}
		
		if($row = pg_fetch_object($res))
		{
			$this->lehrveranstaltung_id=$row->lehrveranstaltung_id;
			$this->studiengang_kz=$row->studiengang_kz;
			$this->bezeichnung=$row->bezeichnung;
			$this->kurzbz=$row->kurzbz;
			$this->semester=$row->semester;
			$this->ects=$row->ects;
			$this->semesterstunden=$row->semesterstunden;
			$this->anmerkung=$row->anmerkung;
			$this->lehre=($row->lehre=='t'?true:false);
			$this->lehreverzeichnis=$row->lehreverzeichnis;
			$this->aktiv=($row->aktiv=='t'?true:false);
			$this->ext_id=$row->ext_id;
			$this->insertamum=$row->insertamum;
			$this->insertvon=$row->insertvon;
			$this->planfaktor=$row->planfaktor;
			$this->planlektoren=$row->planlektoren;
			$this->planpersonalkosten=$row->planpersonalkosten;
			$this->plankostenprolektor=$row->plankostenprolektor;
			$this->updateamum=$row->updateamum;
			$this->updatevon=$row->updatevon;
			$this->sprache=$row->sprache;
		}
		
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
			$lv_obj = new lehrveranstaltung($this->conn, null, null);
			
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
			$lv_obj->plankostenprolektor=$row->plankostenprolektor;
			$lv_obj->updateamum=$row->updateamum;
			$lv_obj->updatevon=$row->updatevon;
			$lv_obj->sprache=$row->sprache;
			
			$this->lehrveranstaltungen[] = $lv_obj;
		}		
		
		return true;
	}
	
	/**
	 * Liefert alle Lehrveranstaltungen zu einem Studiengang/Semester
	 * @param $studiengang_kz
	 *        $semester
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load_lva($studiengang_kz, $semester=null, $lehreverzeichnis=null, $lehre=null)
	{						
		//Variablen pruefen
		
		if(!is_numeric($studiengang_kz) || $studiengang_kz=='')
		{
			$this->errormsg = 'studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}
		if($semester!=null && (!is_numeric($semester) || $semester==''))
		{
			$this->errormsg = 'Semester muss eine gueltige Zahl sein';
			return false;
		}
		if($lehre!=null && !is_bool($lehre))
		{
			$this->errormsg = 'Lehre muss ein boolscher Wert sein';
			return false;
		}
		
		//Select Befehl zusammenbauen
		$qry = "SELECT * FROM lehre.tbl_lehrveranstaltung WHERE studiengang_kz = '$studiengang_kz'";
		
		if($lehreverzeichnis!=null)
			$qry .= " AND lehreverzeichnis='$lehreverzeichnis'";
		if($semester != null)
			$qry .= " AND semester='$semester'";
		if($lehre!=null)
			$qry .= " AND lehre=".($lehre?'true':'false');
		
		$qry .= " AND semester is not null AND lehreverzeichnis<>'' ORDER BY bezeichnung";
		
		//Datensaetze laden
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$lv_obj = new lehrveranstaltung($this->conn, null, null);
			
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
			$lv_obj->plankostenprolektor=$row->plankostenprolektor;
			$lv_obj->updateamum=$row->updateamum;
			$lv_obj->updatevon=$row->updatevon;
			$lv_obj->sprache=$row->sprache;
			
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
	function save($new=null)
	{
		if($new==null)
			$new = $this->new;
		
		//Gueltigkeit der Variablen pruefen
		if(!$this->checkvars())
			return false;
			
		if($new)
		{
			//Neuen Datensatz anlegen
			$qry = 'BEGIN; INSERT INTO lehre.tbl_lehrveranstaltung (studiengang_kz, bezeichnung, kurzbz, 
				semester, ects, semesterstunden,  anmerkung, lehre, lehreverzeichnis, aktiv, ext_id, insertamum, 
				insertvon, planfaktor, planlektoren, planpersonalkosten, plankostenprolektor, updateamum, updatevon, sprache) VALUES ('.
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
				$this->addslashes($this->plankostenprolektor).', '.
				$this->addslashes($this->updateamum).', '.
				$this->addslashes($this->updatevon).','.
				$this->addslashes($this->sprache).');';
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
				'plankostenprolektor='.$this->addslashes($this->plankostenprolektor) .', '.
				'updateamum='.$this->addslashes($this->updateamum) .','.
				'updatevon='.$this->addslashes($this->updatevon) .','.
				'sprache='.$this->addslashes($this->sprache).' '.
				'WHERE lehrveranstaltung_id = '.$this->addslashes($this->lehrveranstaltung_id).';';
		}
		
		if(pg_query($this->conn, $qry))
		{
			if($new)
			{
				$qry = "SELECT currval('lehre.tbl_lehrveranstaltung_lehrveranstaltung_id_seq') as id";
				if($result = pg_query($this->conn, $qry))
				{
					if($row = pg_fetch_object($result))
					{
						$this->lehrveranstaltung_id = $row->id;
						pg_query($this->conn, 'COMMIT;');
						return true;
					}
					else 
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						pg_query($this->conn, 'ROLLBACK');
						return false;
					}
				}
				else 
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					pg_query($this->conn, 'ROLLBACK');
					return false;
				}
			}
			return true;
		}
		else
		{
			pg_query($this->conn, 'ROLLBACK');
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
		return false;
	}
	
	// ****************************************************
	// * Laedt die Lehrveranstaltung zu der ein Mitarbeiter 
	// * in einem Studiensemester zugeordnet ist
	// * @param studiengang_kz, uid, studiensemester_kurzbz
	// * @return true wenn ok, false wenn Fehler
	// ****************************************************
	function loadLVAfromMitarbeiter($studiengang_kz, $uid, $studiensemester_kurzbz)
	{
		if(!is_numeric($studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz ist ungueltig';
			return false;
		}
		
		$qry = "SELECT * FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter WHERE ";
		if($studiengang_kz!=0)
			$qry.="tbl_lehrveranstaltung.studiengang_kz='".addslashes($studiengang_kz)."' AND ";
					
		$qry.= "tbl_lehrveranstaltung.lehrveranstaltung_id = tbl_lehreinheit.lehrveranstaltung_id AND
				tbl_lehreinheitmitarbeiter.lehreinheit_id = tbl_lehreinheit.lehreinheit_id AND
				tbl_lehreinheit.studiensemester_kurzbz = '".addslashes($studiensemester_kurzbz)."' AND
				tbl_lehreinheitmitarbeiter.mitarbeiter_uid='".addslashes($uid)."';";
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$lv_obj = new lehrveranstaltung($this->conn, null, null);
			
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
				$lv_obj->plankostenprolektor=$row->plankostenprolektor;
				$lv_obj->updateamum=$row->updateamum;
				$lv_obj->updatevon=$row->updatevon;
				$lv_obj->sprache=$row->sprache;
				
				$this->lehrveranstaltungen[] = $lv_obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Lesen aus der Datenbank';
			return false;
		}
	}
	
	// *****************************************
	// * Erstellt das XML File fuers Zeugnis
	// * @param $uid
	// *****************************************
	function generateZeugnisXML($uid)
	{
		$qry = "SELECT * FROM lehre.tbl_zeugnisnote JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id) WHERE student_uid='".addslashes($uid)."' ORDER BY bezeichnung";
		$xml = '<?xml version="1.0" encoding="ISO-8859-15" ?><zeugnis>';
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$xml.='<lehreinheit typ="lehreinheit">
							<titel>'.$row->titel.'</titel>
						</lehreinheit>'; 
			}
		}
		$xml.='</zeugnis>';
		return $xml;
	}
}
?>