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
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/basis_db.class.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/funktion.class.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/person.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/student.class.php');
require_once('../../../include/benutzerfunktion.class.php');
require_once('../../../include/globals.inc.php');
require_once('../../../include/phrasen.class.php');

$sprache = getSprache(); 
	$p=new phrasen($sprache); 

if (!$db = new basis_db())
    die($p->t("global/fehlerBeimOeffnenDerDatenbankverbindung"));
	
	
$uid=get_uid();
$cmbLektorMitarbeiter=(isset($_REQUEST['cmbLektorMitarbeiter'])?$_REQUEST['cmbLektorMitarbeiter']:'all');
$cmbChoice=(isset($_REQUEST['cmbChoice'])?$_REQUEST['cmbChoice']:null);
$txtSearchQuery=(isset($_REQUEST['txtSearchQuery'])?$_REQUEST['txtSearchQuery']:null);
$do_search=(isset($_REQUEST['do_search'])?$_REQUEST['do_search']:null);
$do_excel=(isset($_REQUEST['do_excel'])?$_REQUEST['do_excel']:(isset($_REQUEST['btnExcel_x'])?$_REQUEST['btnExcel_x']:false));
$do_pdf=(isset($_REQUEST['do_pdf'])?$_REQUEST['do_pdf']:(isset($_REQUEST['btnPdf_x'])?$_REQUEST['btnPdf_x']:false));

$num_rows=0;
$rows=array();	

$sql_query='';
if(isset($do_search) || $do_excel)
{
	$sql_extend_query= " and ( tbl_benutzerfunktion.datum_von is null OR tbl_benutzerfunktion.datum_von<=now()) AND
			(tbl_benutzerfunktion.datum_bis is null OR tbl_benutzerfunktion.datum_bis>=now()) ";

	if ($cmbLektorMitarbeiter=='all' || $cmbLektorMitarbeiter=='Mitarbeiter_Alle'  
	|| $cmbLektorMitarbeiter=='Mitarbeiter_Fix' || $cmbLektorMitarbeiter=='Mitarbeiter_Extern')			  
	{
		if($txtSearchQuery == "" || $txtSearchQuery == "*" || $txtSearchQuery == "*.*")
		{
			if($cmbChoice == "all")
				$sql_query.= "SELECT person_id, uid, titelpre, titelpost, nachname, vorname, vornamen, standort_id, telefonklappe as teltw,(uid || '@".DOMAIN."') AS emailtw, foto,-1 AS studiengang_kz, -1 AS semester, ort_kurzbz as ort, alias, CASE WHEN (fixangestellt) THEN 'Fix' ELSE 'Extern' END as personenart  FROM campus.vw_mitarbeiter WHERE 1=1 AND aktiv ";
			else
				$sql_query.= "SELECT DISTINCT person_id, uid, titelpre, titelpost, nachname, vorname, vornamen, standort_id, telefonklappe AS teltw, (uid || '@".DOMAIN."') AS emailtw, foto, -1 AS studiengang_kz, -1 AS semester, ort_kurzbz as ort, alias, CASE WHEN (fixangestellt) THEN 'Fix' ELSE 'Extern' END as personenart FROM campus.vw_mitarbeiter JOIN public.tbl_benutzerfunktion using(uid) WHERE funktion_kurzbz='$cmbChoice' AND aktiv ".$sql_extend_query;
		}
		else
		{
			$txtSearchQuery = addslashes($txtSearchQuery);
			if($cmbChoice == "all")
				$sql_query.= "SELECT DISTINCT person_id, uid, titelpre, titelpost, nachname, vorname, vornamen, standort_id, telefonklappe AS teltw, (uid || '@".DOMAIN."') AS emailtw, foto, -1 AS studiengang_kz, -1 AS semester, ort_kurzbz as ort, alias, CASE WHEN (fixangestellt) THEN 'Fix' ELSE 'Extern' END as personenart FROM campus.vw_mitarbeiter WHERE (LOWER(nachname) LIKE LOWER('%$txtSearchQuery%') OR uid LIKE LOWER('%$txtSearchQuery%') OR LOWER(vorname) LIKE LOWER('%$txtSearchQuery%') OR LOWER(nachname || ' ' || vorname) LIKE LOWER('%$txtSearchQuery%') OR LOWER(vorname || ' ' || nachname) LIKE LOWER('%$txtSearchQuery%')) AND aktiv=TRUE ";
			else
				$sql_query.= "SELECT DISTINCT person_id, uid, titelpre, titelpost, nachname, vorname, vornamen, standort_id, telefonklappe AS teltw, (uid || '@".DOMAIN."') AS emailtw, foto, -1 AS studiengang_kz, -1 AS semester, ort_kurzbz as ort, alias, CASE WHEN (fixangestellt) THEN 'Fix' ELSE 'Extern' END as personenart FROM campus.vw_mitarbeiter JOIN public.tbl_benutzerfunktion USING(uid) WHERE ((LOWER(nachname) LIKE LOWER('%$txtSearchQuery%') OR uid LIKE LOWER('%$txtSearchQuery%') OR LOWER(vorname) LIKE LOWER('%$txtSearchQuery%') OR LOWER(nachname || ' ' || vorname) LIKE LOWER('%$txtSearchQuery%') OR LOWER(vorname || ' ' || nachname) LIKE LOWER('%$txtSearchQuery%')) AND funktion_kurzbz='$cmbChoice') AND aktiv=TRUE ".$sql_extend_query;
		}
		
		if ($cmbLektorMitarbeiter=='Mitarbeiter_Fix')
			$sql_query.= ' AND fixangestellt ';
		if ($cmbLektorMitarbeiter=='Mitarbeiter_Extern')
			$sql_query.= ' AND not fixangestellt ';
	}
	
	if ($cmbLektorMitarbeiter=='all')			  
		$sql_query.= " UNION ";
	
	if ($cmbLektorMitarbeiter=='all' || $cmbLektorMitarbeiter=='Student')			  
	{
		if($txtSearchQuery == "" || $txtSearchQuery == "*" || $txtSearchQuery == "*.*")
		{
			if($cmbChoice == "all")
				$sql_query.= " SELECT DISTINCT person_id,uid, titelpre, titelpost, nachname, vorname, vornamen,null::integer AS standort_id, (''::varchar) AS teltw, (uid || '@".DOMAIN."') AS emailtw, foto, studiengang_kz, vw_student.semester, ''::varchar as ort, alias,CASE WHEN (TRUE) THEN 'StudentIn' ELSE 'StudentIn' END as personenart FROM campus.vw_student WHERE vw_student.semester<10 AND aktiv";
			else
				$sql_query.= " SELECT DISTINCT person_id,uid, titelpre,titelpost, nachname, vorname, vornamen,null::integer AS standort_id, (''::varchar) AS teltw, (uid || '@".DOMAIN."') AS emailtw, foto, vw_student.studiengang_kz, vw_student.semester, ''::varchar as ort, alias,CASE WHEN (TRUE) THEN 'StudentIn' ELSE 'StudentIn' END as personenart FROM campus.vw_student JOIN public.tbl_benutzerfunktion using(uid) WHERE vw_student.semester<10 AND funktion_kurzbz='$cmbChoice' AND aktiv ".$sql_extend_query;
		}
		else
		{
			$txtSearchQuery = addslashes($txtSearchQuery);
			if($cmbChoice == "all")
				$sql_query.= " SELECT DISTINCT person_id,uid, titelpre, titelpost, nachname, vorname, vornamen,null::integer AS standort_id, (''::varchar) AS teltw, (uid || '@".DOMAIN."') AS emailtw, foto, studiengang_kz, semester, ''::varchar as ort, alias,CASE WHEN (TRUE) THEN 'StudentIn' ELSE 'StudentIn' END as personenart FROM campus.vw_student WHERE semester<10 AND aktiv AND (LOWER(nachname) LIKE LOWER('%$txtSearchQuery%') OR uid LIKE LOWER('%$txtSearchQuery%') OR LOWER(vorname) LIKE LOWER('%$txtSearchQuery%') OR LOWER(nachname || ' ' || vorname) LIKE LOWER('%$txtSearchQuery%') OR LOWER(vorname || ' ' || nachname) LIKE LOWER('%$txtSearchQuery%')) ";
			else
				$sql_query.= " SELECT DISTINCT person_id,uid, titelpre, titelpost, nachname, vorname, vornamen,null::integer AS standort_id, (''::varchar) AS teltw, (uid || '@".DOMAIN."') AS emailtw, foto, vw_student.studiengang_kz, vw_student.semester, ''::varchar as ort, alias,CASE WHEN (TRUE) THEN 'StudentIn' ELSE 'StudentIn' END as personenart FROM campus.vw_student JOIN public.tbl_benutzerfunktion USING(uid) WHERE vw_student.semester <10 AND ((LOWER(nachname) LIKE LOWER('%$txtSearchQuery%') OR uid LIKE LOWER('%$txtSearchQuery%') OR LOWER(vorname) LIKE LOWER('%$txtSearchQuery%') OR LOWER(nachname || ' ' || vorname) LIKE LOWER('%$txtSearchQuery%') OR LOWER(vorname || ' ' || nachname) LIKE LOWER('%$txtSearchQuery%')) AND funktion_kurzbz='$cmbChoice') AND aktiv=TRUE ".$sql_extend_query;
		}
	}
	$sql_query.= " ORDER BY nachname, vorname";
	$num_rows=0;
	if ($sql_query && $result = $db->db_query($sql_query))
		$num_rows = $db->db_num_rows($result);

	if($num_rows > 0)
	{
		// **** EXCEL 
		if ($do_excel)
		{	
			require_once('../../../include/Excel/excel.php');
			// Creating a workbook
			$workbook = new Spreadsheet_Excel_Writer();
			$workbook->setVersion(8);
			// sending HTTP headers
			$workbook->send("Personensuche_FH_Technikum_Wien_" . date("d_m_Y").".xls");
			// Creating a worksheet
			$worksheet =& $workbook->addWorksheet("Personensuche FH Technikum Wien");
			$worksheet->setInputEncoding('utf-8');

			$format_bold =& $workbook->addFormat();
			$format_bold->setBold();
				
			$spalte=0;
			$zeile=0;
			
			$maxlength = array();

			//Ueberschrift
			$spalte=0;
			$zeile++;
			$worksheet->write($zeile,$spalte,"Titel", $format_bold);
			$maxlength[$spalte]=strlen('Titel');
			$worksheet->write($zeile,++$spalte,"Vorname", $format_bold);
			$maxlength[$spalte]=strlen('Vorname');
			$worksheet->write($zeile,++$spalte,"Nachname", $format_bold);
			$maxlength[$spalte]=strlen('Nachname');
			$worksheet->write($zeile,++$spalte,"Titel", $format_bold);
			$maxlength[$spalte]=strlen('Titel');
			
			
			$worksheet->write($zeile,++$spalte,"Telefonnummer", $format_bold);
			$maxlength[$spalte]=strlen('Telefonnummer');
			$worksheet->write($zeile,++$spalte,"E-Mail Adresse", $format_bold);
			$maxlength[$spalte]=strlen('E-Mail Adresse');
			$worksheet->write($zeile,++$spalte,"Raum", $format_bold);
			$maxlength[$spalte]=strlen('Raum');
			$worksheet->write($zeile,++$spalte,"Studiengang", $format_bold);
			$maxlength[$spalte]=strlen('Studiengang');
			$worksheet->write($zeile,++$spalte,"Semester", $format_bold);
			$maxlength[$spalte]=strlen('Semester');
			$worksheet->write($zeile,++$spalte,"Hauptverteiler", $format_bold);
			$maxlength[$spalte]=strlen('Hauptverteiler');
			$worksheet->write($zeile,++$spalte,"Kz", $format_bold);
			$maxlength[$spalte]=strlen('Kz');
			$worksheet->write($zeile,++$spalte,"Funktion", $format_bold);
			$maxlength[$spalte]=strlen('Funktion');
			$worksheet->write($zeile,++$spalte,"Handy", $format_bold);
			$maxlength[$spalte]=strlen('Handy');
			
			
			for($i = 0; $i < $num_rows; $i++)
			{
				$row = $db->db_fetch_object($result, $i);

				$spalte=0;
				$zeile++;
				
				$titel=(isset($row->titelpre) && $row->titelpre?$row->titelpre:'');
				$worksheet->write($zeile,$spalte,$titel);
				$maxlength[$spalte]=strlen($titel);

				$vorname='';	
				if(isset($row->nachname) && $row->vorname != "")
				{
					$vorname=$row->vorname;
					if($row->vornamen != "")
						$vorname.=' '.substr($row->vornamen,0,1).'.';
				}
				$worksheet->write($zeile,++$spalte,$vorname);
				$maxlength[$spalte]=strlen($vorname);

				$nachname=(isset($row->nachname) && $row->nachname?$row->nachname:'');
				$worksheet->write($zeile,++$spalte,$nachname);
				$maxlength[$spalte]=strlen($nachname);

				$titelpost=(isset($row->titelpost) && $row->titelpost?$row->titelpost:'');
				$worksheet->write($zeile,++$spalte,$titelpost);
				$maxlength[$spalte]=strlen($titelpost);
				
				if($row->teltw != "")
				{
					$vorwahl = '';
					if($row->standort_id!='')
					{
						$qry = "SELECT kontakt as telefon FROM public.tbl_kontakt WHERE standort_id='$row->standort_id' AND kontakttyp='telefon'";
						if($result_tel = $db->db_query($qry))
						{
							if($result_tel && $row_tel = $db->db_fetch_object($result_tel))
								$vorwahl = $row_tel->telefon;
						}	
					}	
				}
				$tel=(isset($row->teltw) && $row->teltw?$vorwahl.' - '.$row->teltw:' ');

				$worksheet->write($zeile,++$spalte,$tel);
				$maxlength[$spalte]=strlen($tel);
				
				if ($row->alias && !in_array($row->studiengang_kz,$noalias))						
					$mail=(isset($row->alias) && $row->alias?$row->alias.'@'.DOMAIN:'');
				else
					$mail=(isset($row->emailtw) && $row->emailtw?$row->emailtw:'');
		
				$worksheet->write($zeile,++$spalte,$mail);
				$maxlength[$spalte]=strlen($mail);
				
				$ort=(isset($row->ort) && $row->ort?$row->ort:'');
				$worksheet->write($zeile,++$spalte,$ort);
				$maxlength[$spalte]=strlen($ort);

				$kurzbz='';
				if(isset($row->studiengang_kz) && $row->studiengang_kz != -1)
				{
					if ($stg_obj = new studiengang($row->studiengang_kz))
						$kurzbz=$stg_obj->kuerzel;
				}
				$row->kurzbz=$kurzbz;
				$worksheet->write($zeile,++$spalte,$kurzbz);
				$maxlength[$spalte]=strlen($kurzbz);
				
				$sem=(isset($row->semester) && $row->semester && $row->semester!= -1?$row->semester:'&nbsp;');
				$worksheet->write($zeile,++$spalte,"Semester");
				$maxlength[$spalte]=strlen('Semester');
				
				$verband='';
				$gruppe='';
				$verteiler='';
				if(isset($row->studiengang_kz) && $row->studiengang_kz != -1)
				{
					if ($std_obj = new student($row->uid))
					{
						$verband=$std_obj->verband;
						$gruppe=$std_obj->gruppe;
					}
					$kurzbz=strtolower($kurzbz);
					$verband=strtolower($verband);

					$verteiler=trim($kurzbz.$row->semester.$verband.$gruppe);
					$verteiler.=($verteiler?'@'.DOMAIN:'');
				}
				$worksheet->write($zeile,++$spalte,$verteiler);
				$maxlength[$spalte]=strlen($verteiler);

				$kz=(isset($row->personenart) && $row->personenart?$row->personenart:'');
				$worksheet->write($zeile,++$spalte,$kz);
				$maxlength[$spalte]=strlen('Kz');
				
				$funktion='-';
				if(isset($row->personenart) && $row->personenart!='StudentIn')
				{
					$funktion='';
					//Funktionen
					$qry = "SELECT distinct 
								*, tbl_benutzerfunktion.oe_kurzbz as oe_kurzbz, tbl_organisationseinheit.bezeichnung as oe_bezeichnung,
								 tbl_benutzerfunktion.semester, tbl_benutzerfunktion.bezeichnung as bf_bezeichnung
							FROM 
								public.tbl_benutzerfunktion 
								JOIN public.tbl_funktion USING(funktion_kurzbz) 
								JOIN public.tbl_organisationseinheit USING(oe_kurzbz)
							WHERE 
								uid='".$row->uid."' AND 
								(tbl_benutzerfunktion.datum_von is null OR tbl_benutzerfunktion.datum_von<=now()) AND
								(tbl_benutzerfunktion.datum_bis is null OR tbl_benutzerfunktion.datum_bis>=now()) 
								order by tbl_benutzerfunktion.bezeichnung
								";
					
					if($result_funktion = $db->db_query($qry))
					{
						$anz_funktion=$db->db_num_rows($result_funktion);
						if($anz_funktion>0)
						{
							while($row_funktion = $db->db_fetch_object($result_funktion))
							{
								if ($funktion)
									$funktion.=', ';
								$funktion.=$row_funktion->bf_bezeichnung.' '.$row_funktion->organisationseinheittyp_kurzbz.' '.$row_funktion->oe_bezeichnung ." ".$row_funktion->semester." ".$row_funktion->fachbereich_kurzbz;
							}	
						}
					}
				}	
				$worksheet->write($zeile,++$spalte,$funktion);
				$maxlength[$spalte]=strlen($funktion);						
				
				$funktion='-';
				if(isset($row->person_id) && $row->personenart!='StudentIn')
				{
					//Funktionen
					$qry = "SELECT distinct tbl_kontakt.kontakttyp,tbl_kontakt.anmerkung,tbl_kontakt.kontakt,tbl_kontakttyp.beschreibung
							FROM 
								public.tbl_kontakt 
								JOIN public.tbl_kontakttyp USING(kontakttyp)
							WHERE 
								tbl_kontakt.kontakttyp ='mobil'
							and	tbl_kontakt.person_id='".$row->person_id."' ";
								
					if($result_kontakt = $db->db_query($qry))
					{
						$anz_kontakt=$db->db_num_rows($result_kontakt);
						if($anz_kontakt>0)
						{
							$funktion='';
							while($row_kontakt = $db->db_fetch_object($result_kontakt))
							{
								if (!$row_kontakt->kontakttyp || !stristr($row_kontakt->kontakttyp,'Firmenhandy') )
									continue;
								if ($funktion)
									$funktion.=', ';
								$funktion.=$row_kontakt->anmerkung." ".$row_kontakt->kontakt." ".$row_kontakt->beschreibung;
							}	
						}
					}
				}	
				$worksheet->write($zeile,++$spalte,$funktion);
				$maxlength[$spalte]=strlen($funktion);
				
			}	
			//Die Breite der Spalten setzen
			foreach($maxlength as $i=>$breite)
				$worksheet->setColumn($i, $i, $breite+2);
		    
			$workbook->close();
			exit;
		}		

		// **** PDF 
		if ($do_pdf)
		{	
			$rows=array();
			$rows_anz=array();
			for($i = 0; $i < $num_rows; $i++)
			{
				$row = $db->db_fetch_object($result, $i);
			
				$row->titelpre=trim(isset($row->titelpre) && $row->titelpre?$row->titelpre:'');
				$vorname=$row->vorname;
				if(isset($row->nachname) && $row->vorname != "")
				{
					$vorname=$row->vorname;
					if($row->vornamen != "")
						$vorname.=' '.substr($row->vornamen,0,1).'.';
				}
				$row->vorname=$vorname;
				if($row->teltw != "")
				{
					$vorwahl = '';
					if($row->standort_id!='')
					{
						$qry = "SELECT kontakt as telefon FROM public.tbl_kontakt WHERE standort_id='$row->standort_id' AND kontakttyp='telefon'";
						if($result_tel = $db->db_query($qry))
						{
							if($result_tel && $row_tel = $db->db_fetch_object($result_tel))
								$vorwahl = $row_tel->telefon;
						}	
					}	
				}
				$row->teltw=(isset($row->teltw) && $row->teltw?$vorwahl.' - '.$row->teltw:'');
				if ($row->alias && !in_array($row->studiengang_kz, $noalias))						
					$mail=(isset($row->alias) && $row->alias?$row->alias.'@'.DOMAIN:'');
				else
					$mail=(isset($row->emailtw) && $row->emailtw?$row->emailtw:'');
				$row->email=$mail;
				
				$row->ort=(isset($row->ort) && $row->ort?$row->ort:'');

				$verband='';
				$gruppe='';
				$verteiler='';
				$kurzbz='';
				if(isset($row->studiengang_kz) && $row->studiengang_kz != -1)
				{
					if ($stg_obj = new studiengang($row->studiengang_kz))
						$kurzbz=$stg_obj->kurzbzlang;

					$row->kurzbz=$kurzbz;
					if(isset($row->studiengang_kz) && $row->studiengang_kz != -1  && $row->studiengang_kz != ''  && $row->studiengang_kz != '-')
					{
						if ($std_obj = new student($row->uid))
						{
							$verband=$std_obj->verband;
							$gruppe=$std_obj->gruppe;
						}
						$verband=strtolower($verband);
						$verteiler=trim(strtolower($kurzbz).$row->semester.$verband.$gruppe);
						$verteiler.=($verteiler?'@'.DOMAIN:'');
					}
				}
				else
					$row->studiengang_kz='-';

				$row->kurzbz=$kurzbz;
				$row->verband=$verband;
				$row->gruppe=$gruppe;
				$row->verteiler=$verteiler;
				if ($row->verteiler)
					$row->email=$row->email."\n".$row->verteiler;
				
				$row->kz=(isset($row->personenart) && $row->personenart?$row->personenart:'');
				
				$funktion='-';
				if(isset($row->personenart) && $row->personenart!='StudentIn')
				{
					$funktion='';
					//Funktionen
					$qry = "SELECT distinct 
								*, tbl_benutzerfunktion.oe_kurzbz as oe_kurzbz, tbl_organisationseinheit.bezeichnung as oe_bezeichnung,
								 tbl_benutzerfunktion.semester, tbl_benutzerfunktion.bezeichnung as bf_bezeichnung
							FROM 
								public.tbl_benutzerfunktion 
								JOIN public.tbl_funktion USING(funktion_kurzbz) 
								JOIN public.tbl_organisationseinheit USING(oe_kurzbz)
							WHERE 
								uid='".$row->uid."' AND 
								(tbl_benutzerfunktion.datum_von is null OR tbl_benutzerfunktion.datum_von<=now()) AND
								(tbl_benutzerfunktion.datum_bis is null OR tbl_benutzerfunktion.datum_bis>=now()) 
								order by tbl_benutzerfunktion.bezeichnung
								";
					
					if($result_funktion = $db->db_query($qry))
					{
						$anz_funktion=$db->db_num_rows($result_funktion);
						if($anz_funktion>0)
						{
							while($row_funktion = $db->db_fetch_object($result_funktion))
							{
								if ($funktion)
									$funktion.=', ';
								$funktion.=$row_funktion->bf_bezeichnung.' '.$row_funktion->organisationseinheittyp_kurzbz.' '.$row_funktion->oe_bezeichnung ." ".$row_funktion->semester." ".$row_funktion->fachbereich_kurzbz;
							}	
						}
					}
				}	
				$row->funktion=$funktion;
				
				$funktion='-';
				if(isset($row->person_id) && $row->personenart!='StudentIn')
				{
					//Funktionen
					$qry = "SELECT distinct tbl_kontakt.kontakttyp,tbl_kontakt.anmerkung,tbl_kontakt.kontakt,tbl_kontakttyp.beschreibung
							FROM 
								public.tbl_kontakt 
								JOIN public.tbl_kontakttyp USING(kontakttyp)
							WHERE 
								tbl_kontakt.kontakttyp ='Firmenhandy'
							and	tbl_kontakt.person_id='".$row->person_id."' ";
								
					if($result_kontakt = $db->db_query($qry))
					{
						$anz_kontakt=$db->db_num_rows($result_kontakt);
						if($anz_kontakt>0)
						{
							$funktion='';
							while($row_kontakt = $db->db_fetch_object($result_kontakt))
							{
								if (!$row_kontakt->kontakttyp || !stristr($row_kontakt->kontakttyp,'Firmenhandy') )
									continue;
								if ($funktion)
									$funktion.=', ';
								$funktion.=$row_kontakt->anmerkung." ".$row_kontakt->kontakt." ".$row_kontakt->beschreibung;
							}	
						}
					}
				}	
				$row->firmenhandy=$funktion;
				
				foreach ($row as $key => $value) 
				{
					$row->$key=trim(iconv('UTF-8','ISO-8859-15',$row->$key));
					$anz=strlen($value);
					if (!isset($rows_anz[$key]) || $rows_anz[$key]<$anz)
						$rows_anz[$key]=$anz;
				}
				$rows[]=$row;
			}	
			

			require_once('../../../include/pdf/fpdf.php');
			class PDF extends FPDF
			{
				//Simple table
				function BasicTable($header,$data,$items,$rows_anz)
				{
				    //Header
				    foreach($header as $col)
				        $this->Cell(40,7,$col,1);
				    $this->Ln();
				    //Data
				    foreach($data as $row)
				    {
					    foreach($items as $col)
						{
							if (!isset($row->$col))
								die("Achtung ! Die Spalte $col wurde nicht in den Daten gefunden.");
					        $this->Cell(40,6,$row->$col,1);
						}	
				        $this->Ln();
				    }
				}
				
				//Better table
				function ImprovedTable($header,$data,$items,$rows_anz)
				{
				    //Header
				    for($i=0;$i<count($header);$i++)
				        $this->Cell($rows_anz[$items[$i]] *2.5 ,7,$header[$i],1,0,'C');
				    $this->Ln();
				    //Data
				    foreach($data as $row)
				    {
				     	$i=0;
					    foreach($items as $col)
						{
							if (!isset($row->$col))
								die("Achtung ! Die Spalte $col wurde nicht in den Daten gefunden.");
						    $this->Cell($rows_anz[$items[$i]] *2.5,6,$row->$col,'LR');
							$i++;
						}
				        $this->Ln();
				    }
				}

				var $widths;
				var $aligns;
				function SetWidths($w)
				{
				    //Set the array of column widths
					if (is_array($w) && isset($w[0]))
					    $this->widths=$w;
					else if (is_array($w) )	
					{
						$this->widths=array();
						foreach ($w as $key => $value) 
							$this->widths[]=$value;
					}
					if (!is_array($this->widths) || !isset($this->widths[0]))
						return;
					
				}
				
				function SetAligns($a)
				{
				    //Set the array of column alignments
				    $this->aligns=$a;
				}
				
				function Row($data,$fill=false)
				{
				
					if (is_array($data) && isset($data[0]))
					{
					    $row_data=$data;
					}	
					else if (is_array($data) || is_object($data))	
					{
						$row_data=array();
						foreach ($data as $key => $value) 
							$row_data[]=$value;
					}
					else
					{
						$this->Cell(40,10,'Keine Daten uebergeben ');
						return; 
					}
				    //Calculate the height of the row
				    $nb=0;
				    for($i=0;$i<count($row_data);$i++)
				        $nb=max($nb, $this->NbLines($this->widths[$i], $row_data[$i]));
				    $h=5*$nb;
				    //Issue a page break first if needed
				    $this->CheckPageBreak($h);
				    //Draw the cells of the row
				    for($i=0;$i<count($row_data);$i++)
				    {
				        $w=$this->widths[$i];
				        $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
				        //Save the current position
				        $x=$this->GetX();
				        $y=$this->GetY();
				        //Draw the border
				        $this->Rect($x, $y, $w, $h);
				        //Print the text
				        $this->MultiCell($w, 5, $row_data[$i], 0, $a,$fill);
				        //Put the position to the right of the cell
				        $this->SetXY($x+$w, $y);
				    }
				    //Go to the next line
				    $this->Ln($h);
				}
				
				function CheckPageBreak($h)
				{
				    //If the height h would cause an overflow, add a new page immediately
				    if($this->GetY()+$h>$this->PageBreakTrigger)
				        $this->AddPage($this->CurOrientation);
				}
				
				function NbLines($w, $txt)
				{
				    //Computes the number of lines a MultiCell of width w will take
				    $cw=&$this->CurrentFont['cw'];
				    if($w==0)
				        $w=$this->w-$this->rMargin-$this->x;
				    $wmax=((($w - 2) *$this->cMargin)*1000 ) / $this->FontSize;
					
				    $s=str_replace("\r", '', $txt);
				    $nb=strlen($s);
				    if($nb>0 and $s[$nb-1]=="\n")
				        $nb--;
				    $sep=-1;
				    $i=0;
				    $j=0;
				    $l=0;
				    $nl=1;
				    while($i<$nb)
				    {
				        $c=$s[$i];
				        if($c=="\n")
				        {
				            $i++;
				            $sep=-1;
				            $j=$i;
				            $l=0;
				            $nl++;
				            continue;
				        }
				        if($c==' ')
				            $sep=$i;
				        $l+=$cw[$c];
				        if($l>$wmax)
				        {
				            if($sep==-1)
				            {
				                if($i==$j)
				                    $i++;
				            }
				            else
				                $i=$sep+1;
				            $sep=-1;
				            $j=$i;
				            $l=0;
				            $nl++;
				        }
				        else
				            $i++;
				    }
				    return $nl;
				}
				//Colored table
				function FancyTable($header,$data,$items,$rows_anz)
				{
				    //Colors, line width and bold font
				    $this->SetFillColor(102,205,170);
				    $this->SetTextColor(255);
				    $this->SetDrawColor(0,0,0);
				    $this->SetLineWidth(.1);
				    $this->SetFont('','B');
		   			//Header
					$width=array();	
					reset($items);
				    foreach($items as $col)
					{
						if (!isset($rows_anz[$col]))
							die("Achtung ! Die Spalte $col wurde nicht in den Daten gefunden.");
						$width[]=$rows_anz[$col];
					}
					$this->SetWidths($width);
					$this->SetAligns('C');
					$row_data=array();
				    for($i=0;$i<count($header);$i++)
					{
						$row_data[]=$header[$i];
					}	
					$this->Row($row_data,true);
		    		//Data
				    //Color and font restoration 95,158,160
				    $this->SetFillColor(224,235,255);
				    $this->SetTextColor(0);
				    $this->SetFont('');
					$this->SetAligns('L');
				    $fill=false;
				    foreach($data as $row)
				    {
				     	$i=0;
						$row_data=array();
						reset($items);
					    foreach($items as $col)
						{
							if (!isset($row->$col))
								die("Achtung ! Die Spalte $col wurde nicht in den Daten gefunden.");
							$row_data[]=$row->$col;
							$i++;
						}
						$fill=false;
						$this->Row($row_data,$fill);
				        $fill=!$fill;
				    }
				}
			}	// Ende Extend FPDF Class				
				

			// Creating a workbook
			$orientation='l'; // 'p' 
			$pdf = new PDF($orientation);

			$pdf->SetTitle('Personensuche '.CAMPUS_NAME);
			$pdf->SetSubject(CAMPUS_NAME . date('Y-m-d'));					
			$pdf->SetAuthor(CAMPUS_NAME);						
			$pdf->SetCreator($uid) ;						
			

			//Column titles
			$header=array('Titel','Vorname','Nachname','Titel','Tel.nr','E-Mail Adresse / Hauptverteiler','Raum','Studieng','Sem','Personenart','Handy','Funktion' );
			//Data loading
			$item=array('titelpre','vorname','nachname','titelpost','teltw','email','ort','kurzbz','semester','kz','firmenhandy','funktion' );
			
			foreach ($item as $key => $value) 
			{
					$anz=strlen($header[$key]);
					if (!isset($rows_anz[$value]) || $rows_anz[$value]<$anz)
						$rows_anz[$value]=$anz;
			}

			$rows_anz['titelpre']=10;
			$rows_anz['nachname']=35;
			$rows_anz['vorname']=35;
			$rows_anz['titelpost']=10;
			$rows_anz['teltw']=25;
			$rows_anz['firmenhandy']=25;					
			$rows_anz['kurzbz']=12;
			$rows_anz['kz']=15;					
			$rows_anz['semester']=7;
			$rows_anz['email']=50;
			$rows_anz['funktion']=45;
			
			$pdf->SetFont('Arial','',6);
			$pdf->AddPage();
			if (count($rows)>0)
			{
				$pdf->FancyTable($header,$rows,$item,$rows_anz);
			}	
			else
				$pdf->Cell(40,10,'Keine Daten gefunden');
			$pdf->Output();
			exit;
		}
	}	
}


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
	<script language="JavaScript" type="text/javascript">
	<!--
		var __js_page_array = new Array();
	    function js_toggle_container(conid)
	    {
			if (document.getElementById)
			{
	        	var block = "table-row";
				if (navigator.appName.indexOf('Microsoft') > -1)
					block = 'block';
					
				// Aktueller Anzeigemode ermitteln	
	            var status = __js_page_array[conid];
	            if (status == null)
				{
			 		if (document.getElementById && document.getElementById(conid)) 
					{  
						status=document.getElementById(conid).style.display;
					} else if (document.all && document.all[conid]) {      
						status=document.all[conid].style.display;
			      	} else if (document.layers && document.layers[conid]) {                          
					 	status=document.layers[conid].style.display;
			        }							
				}	
				
				// Anzeigen oder Ausblenden
	            if (status == 'none')
	            {
			 		if (document.getElementById && document.getElementById(conid)) 
					{  
						document.getElementById(conid).style.display = 'block';
					} else if (document.all && document.all[conid]) {      
						document.all[conid].style.display='block';
			      	} else if (document.layers && document.layers[conid]) {                          
					 	document.layers[conid].style.display='block';
			        }				
	            	__js_page_array[conid] = 'block';
	            }
	            else
	            {
			 		if (document.getElementById && document.getElementById(conid)) 
					{  
						document.getElementById(conid).style.display = 'none';
					} else if (document.all && document.all[conid]) {      
						document.all[conid].style.display='none';
			      	} else if (document.layers && document.layers[conid]) {                          
					 	document.layers[conid].style.display='none';
			        }				
	            	__js_page_array[conid] = 'none';
	            }
	            return false;
	     	}
	     	else
	     		return true;
	  	}
	//-->
	</script>
</head>
<body onLoad="document.SearchFormular.txtSearchQuery.focus();">
<table class="tabcontent" id="inhalt">
  <tr>
    <td class="tdwidth10">&nbsp;</td>
    <td>
    <form target="_self" method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>" name="SearchFormular" id="SearchFormular" >
    <table class="tabcontent">
      <tr>
        <td class="ContentHeader"><font class="ContentHeader">&nbsp;<?php echo $p->t('personensuche/personensuche');?> <?php echo CAMPUS_NAME;?></font></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
	  <tr>
	  	<td nowrap>
	  	  <?php echo $p->t("personensuche/sucheNach");?>:
	  	  <input type="text" name="txtSearchQuery" size="45" value="<?php echo $txtSearchQuery; ?>">

	  	  <?php echo $p->t("personensuche/kennzeichen");?>
	  	  <select name="cmbLektorMitarbeiter">
			  <option <?php echo ($cmbLektorMitarbeiter=='all'?' selected="selected" ':'');  ;?> value="all"><?php echo $p->t("personensuche/allePersonen");?></option>
			  <option <?php echo ($cmbLektorMitarbeiter=='Mitarbeiter_Alle'?' selected="selected" ':'');?> value="Mitarbeiter_Alle"><?php echo $p->t("personensuche/mitarbeiterIn");?></option>
			  <option <?php echo ($cmbLektorMitarbeiter=='Mitarbeiter_Fix'?' selected="selected" ':'');?> value="Mitarbeiter_Fix"><?php echo $p->t("personensuche/mitarbeiterInFix");?></option>
			  <option <?php echo ($cmbLektorMitarbeiter=='Mitarbeiter_Extern'?' selected="selected" ':'');?> value="Mitarbeiter_Extern"><?php echo $p->t("personensuche/mitarbeiterInExtern");?></option>
			  <option <?php echo ($cmbLektorMitarbeiter=='Student'?' selected="selected" ':'');?> value="Student"><?php echo $p->t("personensuche/studentIn");?></option>
	  	  </select>

	  	  <?php echo $p->t("personensuche/inGruppe");?>
	  	  <select name="cmbChoice">
			  <option value="all"><?php echo $p->t("personensuche/alleKategorien");?></option>
			  <?php
				$fkt_obj = new funktion();
				$fkt_obj->getAll();
				foreach ($fkt_obj->result as $row)
				{
					if(isset($cmbChoice) && $cmbChoice == $row->funktion_kurzbz)
					{
						echo "<option value=\"$row->funktion_kurzbz\" selected>".$row->beschreibung."</option>";
					}
					else
					{
						echo "<option value=\"$row->funktion_kurzbz\">".$row->beschreibung."</option>";
					}
				}
			  ?>
	  	  </select>
	  	  <input onclick="document.SearchFormular.target = '_self';"  type="submit" name="btnSearch" value="<?php echo $p->t("global/suchen");?>">

	  	  </tr>

		  <tr>
		<td colspan="6">
			<table>
				<tr>
					<td align="center">
			  			<input type="hidden" name="do_search">
			  			<input onclick="document.SearchFormular.target = '_blank';" type="Image" src="../../../skin/images/excel.gif" type="submit" name="btnExcel" value="Excel"><br />
			  			<input style="border:0; background-color: transparent;" onclick="document.SearchFormular.target = '_blank';" type="submit" name="do_excel" value="Excel">
			  		</td>
			  		<td align="center">
			  			<input onclick="document.SearchFormular.target = '_blank';" type="Image" src="../../../skin/images/pdfs.jpg" height="32" type="submit" name="btnPdf" value="Pdf"><br />
			  			<input style="border:0; background-color: transparent;" onclick="document.SearchFormular.target = '_blank';" type="submit" name="do_pdf" value="Pdf">
			  		</td>
				</tr>
			</table>
		</td>
	  </tr>
		

	  <tr>
	  	<td nowrap>
		<?php
		if($num_rows > 0)
		{
			echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"1\" width=\"100%\">";
			echo '<tr>
					<td align="left" class="ContentHeader" class="tdwrap"><font class="ContentHeader">&nbsp;'.$p->t("global/titel").'</font></td>
					<td align="left" class="ContentHeader" class="tdwrap"><font class="ContentHeader">&nbsp;'.$p->t("global/vorname").'</font></td>
					<td align="left" class="ContentHeader" class="tdwrap"><font class="ContentHeader">&nbsp;'.$p->t("global/nachname").'</font></td>
					<td align="left" class="ContentHeader" class="tdwrap"><font class="ContentHeader">&nbsp;'.$p->t("global/titel").'</font></td>
					<td align="left" class="ContentHeader" class="tdwrap"><font class="ContentHeader">&nbsp;'.$p->t("global/telefonnummer").'</font></td>
					<td align="left" class="ContentHeader" class="tdwrap"><font class="ContentHeader">&nbsp;'.$p->t("global/emailAdresse").'</font></td>
					<td align="left" class="ContentHeader" class="tdwrap"><font class="ContentHeader">&nbsp;'.$p->t("lvplan/raum").'</font></td>
					<td align="left" class="ContentHeader" class="tdwrap"><font class="ContentHeader">&nbsp;'.$p->t("global/studiengang").'</font></td>
					<td align="left" class="ContentHeader" class="tdwrap"><font class="ContentHeader">&nbsp;'.$p->t("global/semester").'</font></td>
					<td align="left" class="ContentHeader" class="tdwrap"><font class="ContentHeader">&nbsp;'.$p->t("personensuche/hauptverteiler").'</font></td>
					<td style="display:none;" align="left" class="ContentHeader" class="tdwrap"><font class="ContentHeader">&nbsp;Alias</font></td>
					<td align="left" class="ContentHeader" class="tdwrap"><font class="ContentHeader">&nbsp;'.$p->t("personensuche/art").'</font></td>
					<td align="left" class="ContentHeader" class="tdwrap"><font class="ContentHeader">&nbsp;'.$p->t("global/funktion").'</font></td>
					<td align="left" class="ContentHeader" class="tdwrap"><font class="ContentHeader">&nbsp;'.$p->t("global/handy").'</font></td>								
			';
			echo '</tr>
				  <tr>
				  	<td class="tdwrap">&nbsp;</td>
				  </tr>';
			for($i = 0; $i < $num_rows; $i++)
			{
				$row = $db->db_fetch_object($result, $i);
				echo '<tr valign="top">';
				echo '	<td '.($i % 2==0?'':' class="MarkLine" ').' align="left" class="tdwrap">&nbsp;'.(isset($row->titelpre) && $row->titelpre?$row->titelpre:'&nbsp;').'</td>';
				$vorname='';	
				if(isset($row->nachname) && $row->vorname != "")
				{
					$vorname=$row->vorname;
					if($row->vornamen != "")
						$vorname.=' '.substr($row->vornamen,0,1).'.';
				}
				echo '	<td '.($i % 2==0?'':' class="MarkLine" ').' align="left" class="tdwrap">&nbsp;'.$vorname.'</td>';
				echo '	<td '.($i % 2==0?'':' class="MarkLine" ').' align="left" class="tdwrap">&nbsp;'.(isset($row->nachname) && $row->nachname?'<a href="../profile/index.php?uid='.urlencode($row->uid).'">'.$row->nachname.'</a>':'&nbsp;').'</td>';
				echo '	<td '.($i % 2==0?'':' class="MarkLine" ').' align="left" class="tdwrap">&nbsp;'.(isset($row->titelpost) && $row->titelpost?$row->titelpost:'&nbsp;').'</td>';
				if($row->teltw != "")
				{
					$vorwahl = '';
					if($row->standort_id!='')
					{
						$qry = "SELECT kontakt as telefon FROM public.tbl_kontakt WHERE standort_id='$row->standort_id' AND kontakttyp='telefon'";
						if($result_tel = $db->db_query($qry))
						{
							if($result_tel && $row_tel = $db->db_fetch_object($result_tel))
								$vorwahl = $row_tel->telefon;
						}	
					}	
				}
				echo '	<td '.($i % 2==0?'':' class="MarkLine" ').' align="left" class="tdwrap">&nbsp;'.(isset($row->teltw) && $row->teltw?$vorwahl.' - '.$row->teltw:'&nbsp;').'</td>';

				if ($row->alias && !in_array($row->studiengang_kz, $noalias))						
					echo '	<td '.($i % 2==0?'':' class="MarkLine" ').' align="left" class="tdwrap">&nbsp;'.(isset($row->alias) && $row->alias?'<a href="mailto:'.$row->alias.'@'.DOMAIN.'" class="Item">'.$row->alias.'@'.DOMAIN.'</a>':'&nbsp;').'</td>';
				else
					echo '	<td '.($i % 2==0?'':' class="MarkLine" ').' align="left" class="tdwrap">&nbsp;'.(isset($row->emailtw) && $row->emailtw?'<a href="mailto:'.$row->emailtw.'" class="Item">'.$row->emailtw.'</a>':'&nbsp;').'</td>';
				
				echo '	<td '.($i % 2==0?'':' class="MarkLine" ').' align="left" class="tdwrap">&nbsp;'.(isset($row->ort) && $row->ort?$row->ort:'&nbsp;').'</td>';

				$kurzbz='';
				if(isset($row->studiengang_kz) && $row->studiengang_kz != -1)
				{
					if ($stg_obj = new studiengang($row->studiengang_kz))
						$kurzbz=$stg_obj->kuerzel;
				}
				echo '	<td '.($i % 2==0?'':' class="MarkLine" ').' align="left" class="tdwrap">&nbsp;'.($kurzbz?$kurzbz:'&nbsp;').'</td>';
				echo '	<td '.($i % 2==0?'':' class="MarkLine" ').' align="left" class="tdwrap">&nbsp;'.(isset($row->semester) && $row->semester && $row->semester!= -1?$row->semester:'&nbsp;').'</td>';

				$verband='';
				$gruppe='';
				$verteiler='';
				if(isset($row->studiengang_kz) && $row->studiengang_kz != -1)
				{
					if ($std_obj = new student($row->uid))
					{
						$verband=$std_obj->verband;
						$gruppe=$std_obj->gruppe;
					}
					$kurzbz=strtolower($kurzbz);
					$verband=strtolower($verband);

					$verteiler='<a class="Item" href="mailto:'.trim($kurzbz.$row->semester.$verband.$gruppe).'@'.DOMAIN.'">'.trim($kurzbz.$row->semester.$verband.$gruppe).'@'.DOMAIN;
				}
				echo '	<td '.($i % 2==0?'':' class="MarkLine" ').' align="left" class="tdwrap">&nbsp;'.($verteiler?$verteiler:'&nbsp;').'</td>';
				echo '	<td style="display:none;" '.($i % 2==0?'':' class="MarkLine" ').' align="left" class="tdwrap">&nbsp;'.(isset($row->alias) && $row->alias?'<a href="mailto:'.$row->alias.'@'.DOMAIN.'" class="Item">'.$row->alias.'@'.DOMAIN.'</a>':'&nbsp;').'</td>';
				echo '	<td '.($i % 2==0?'':' class="MarkLine" ').' align="left" class="tdwrap">&nbsp;'.(isset($row->personenart) && $row->personenart?$row->personenart:'&nbsp;').'</td>';

				$funktion='-';
				if(isset($row->personenart) && $row->personenart!='StudentIn')
				{
					$funktion='';
					//Funktionen
					$qry = "SELECT distinct 
								*, tbl_benutzerfunktion.oe_kurzbz as oe_kurzbz, tbl_organisationseinheit.bezeichnung as oe_bezeichnung,
								 tbl_benutzerfunktion.semester, tbl_benutzerfunktion.bezeichnung as bf_bezeichnung
							FROM 
								public.tbl_benutzerfunktion 
								JOIN public.tbl_funktion USING(funktion_kurzbz) 
								JOIN public.tbl_organisationseinheit USING(oe_kurzbz)
							WHERE 
								uid='".$row->uid."' AND 
								(tbl_benutzerfunktion.datum_von is null OR tbl_benutzerfunktion.datum_von<=now()) AND
								(tbl_benutzerfunktion.datum_bis is null OR tbl_benutzerfunktion.datum_bis>=now()) 
								order by tbl_benutzerfunktion.bezeichnung
								";
					
					if($result_funktion = $db->db_query($qry))
					{
						$anz_funktion=$db->db_num_rows($result_funktion);
						if($anz_funktion>0)
						{
							$funktion_anf='<span onclick="js_toggle_container(\'funktion'.$i.'\');">';
							while($row_funktion = $db->db_fetch_object($result_funktion))
							{
								if (!$funktion)
									$funktion_anf.=($anz_funktion>1?'&raquo;':'&nbsp;').$row_funktion->bf_bezeichnung;
								$funktion.="<tr class='liste1'><td>".$row_funktion->bf_bezeichnung."</td><td nowrap>".$row_funktion->organisationseinheittyp_kurzbz.' '.$row_funktion->oe_bezeichnung."</td><td>".$row_funktion->semester."</td><td>".$row_funktion->fachbereich_kurzbz."</td></tr>";
							}	
							$funktion=$funktion_anf.'</span><div style="display:none;" id="funktion'.$i.'"><table><tr class="liste"><th>Bezeichnung</th><th>Organisationseinheit</th><th>Semester</th><th>Institut</th></tr>'.$funktion.'</table><br></div>';												
						}
					}
				}	
				echo '	<td '.($i % 2==0?'':' class="MarkLine" ').' align="left" class="tdwrap">&nbsp;'.$funktion.'</td>';
				
				$funktion='-';

				if(isset($row->person_id) && $row->personenart!='StudentIn')
				{
					$funktion='';
					//Funktionen
					$qry = "SELECT distinct tbl_kontakt.kontakttyp,tbl_kontakt.anmerkung,tbl_kontakt.kontakt,tbl_kontakttyp.beschreibung
							FROM 
								public.tbl_kontakt 
								JOIN public.tbl_kontakttyp USING(kontakttyp)
							WHERE 
								tbl_kontakt.kontakttyp ='mobil'
							and	tbl_kontakt.person_id='".$row->person_id."' ";
								
					if($result_kontakt = $db->db_query($qry))
					{
						$anz_kontakt=$db->db_num_rows($result_kontakt);
						if($anz_kontakt>0)
						{
							$funktion_anzahl=0;
							$funktion_anf='';
							if ($anz_kontakt>1)
								$funktion_anf='<span onclick="js_toggle_container(\'kontakt'.$i.'\');">';
							while($row_kontakt = $db->db_fetch_object($result_kontakt))
							{
								if (!$row_kontakt->kontakttyp || !stristr($row_kontakt->kontakttyp,'Firmenhandy') )
									continue;
									
								if (!$funktion)
									$funktion_anf.=($anz_kontakt>1?'&raquo;':'&nbsp;').$row_kontakt->kontakt;

								$funktion.="<tr class='liste1'><td>".$row_kontakt->anmerkung."</td><td nowrap>".$row_kontakt->kontakt."</td><td nowrap>".$row_kontakt->beschreibung."</td></tr>";
								$funktion_anzahl++;
							}	
							if ($funktion_anzahl>1)
								$funktion=$funktion_anf.'</span><div style="display:none;" id="kontakt'.$i.'"><table><tr class="liste"><th>Anmerkung</th><th>Kontakt</th><th>Beschreibung</th></tr>'.$funktion.'</table><br></div>';												
							else
								$funktion=str_replace('&raquo;','&nbsp;',$funktion_anf);
						}
					}
				}	
				echo '	<td '.($i % 2==0?'':' class="MarkLine" ').' align="left" class="tdwrap">&nbsp;'.$funktion.'</td>';
				echo "</tr>";
			}
			echo '<tr><td class="tdwrap">&nbsp;</td></tr>';
			echo '</table>';
		}
			
		if(!isset($do_search))
			echo '<br>'.$p->t("personensuche/bitteSuchbegriffEingeben").'.';
		else if($num_rows > 0)
			echo '<br>'.$p->t("personensuche/esWurden") .'&nbsp;' .$num_rows. '&nbsp;'.$p->t("personensuche/eintraegeGefunden");
		else if(isset($do_search))
			echo '<br>'.$p->t("personensuche/keineEintraegeGefunden");
?>
		</td>
	  </tr>
    </table>
    </form></td>
	<td class="tdwidth30">&nbsp;</td>
  </tr>
</table>
</body>
</html>