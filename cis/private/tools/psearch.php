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
  	if (!$db = new basis_db())
  	    die('Fehler beim Oeffnen der Datenbankverbindung');

    require_once('../../../include/functions.inc.php');
    require_once('../../../include/funktion.class.php');
    require_once('../../../include/studiengang.class.php');
    require_once('../../../include/person.class.php');
    require_once('../../../include/benutzer.class.php');
    require_once('../../../include/student.class.php');
	require_once('../../../include/benutzerfunktion.class.php');
	
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
						$sql_query.= "SELECT person_id, uid, titelpre, titelpost, nachname, vorname, vornamen, standort_kurzbz, telefonklappe as teltw,(uid || '@".DOMAIN."') AS emailtw, foto,-1 AS studiengang_kz, -1 AS semester, ort_kurzbz as ort, alias, CASE WHEN (fixangestellt) THEN 'Fix' ELSE 'Extern' END as personenart  FROM campus.vw_mitarbeiter ";
					else
						$sql_query.= "SELECT DISTINCT person_id, uid, titelpre, titelpost, nachname, vorname, vornamen, standort_kurzbz, telefonklappe AS teltw, (uid || '@".DOMAIN."') AS emailtw, foto, -1 AS studiengang_kz, -1 AS semester, ort_kurzbz as ort, alias, CASE WHEN (fixangestellt) THEN 'Fix' ELSE 'Extern' END as personenart FROM campus.vw_mitarbeiter JOIN public.tbl_benutzerfunktion using(uid) WHERE funktion_kurzbz='$cmbChoice' AND aktiv ".$sql_extend_query;
				}
				else
				{
					$txtSearchQuery = addslashes($txtSearchQuery);
					if($cmbChoice == "all")
						$sql_query.= "SELECT DISTINCT person_id, uid, titelpre, titelpost, nachname, vorname, vornamen, standort_kurzbz, telefonklappe AS teltw, (uid || '@".DOMAIN."') AS emailtw, foto, -1 AS studiengang_kz, -1 AS semester, ort_kurzbz as ort, alias, CASE WHEN (fixangestellt) THEN 'Fix' ELSE 'Extern' END as personenart FROM campus.vw_mitarbeiter WHERE (LOWER(nachname) LIKE LOWER('%$txtSearchQuery%') OR uid LIKE LOWER('%$txtSearchQuery%') OR LOWER(vorname) LIKE LOWER('%$txtSearchQuery%') OR LOWER(nachname || ' ' || vorname) LIKE LOWER('%$txtSearchQuery%') OR LOWER(vorname || ' ' || nachname) LIKE LOWER('%$txtSearchQuery%')) AND aktiv=TRUE ";
					else
						$sql_query.= "SELECT DISTINCT person_id, uid, titelpre, titelpost, nachname, vorname, vornamen, standort_kurzbz, telefonklappe AS teltw, (uid || '@".DOMAIN."') AS emailtw, foto, -1 AS studiengang_kz, -1 AS semester, ort_kurzbz as ort, alias, CASE WHEN (fixangestellt) THEN 'Fix' ELSE 'Extern' END as personenart FROM campus.vw_mitarbeiter JOIN public.tbl_benutzerfunktion USING(uid) WHERE ((LOWER(nachname) LIKE LOWER('%$txtSearchQuery%') OR uid LIKE LOWER('%$txtSearchQuery%') OR LOWER(vorname) LIKE LOWER('%$txtSearchQuery%') OR LOWER(nachname || ' ' || vorname) LIKE LOWER('%$txtSearchQuery%') OR LOWER(vorname || ' ' || nachname) LIKE LOWER('%$txtSearchQuery%')) AND funktion_kurzbz='$cmbChoice') AND aktiv=TRUE ".$sql_extend_query;
				}
				
				if ($cmbLektorMitarbeiter=='Mitarbeiter_Fix')
					$sql_query.= ($txtSearchQuery == "" || $txtSearchQuery == "*" || $txtSearchQuery == "*.*"?' where ':' and ').' fixangestellt ';
				if ($cmbLektorMitarbeiter=='Mitarbeiter_Extern')
					$sql_query.= ($txtSearchQuery == "" || $txtSearchQuery == "*" || $txtSearchQuery == "*.*"?' where ':' and ').' not fixangestellt ';
			}
			
			if ($cmbLektorMitarbeiter=='all')			  
				$sql_query.= " UNION ";
			
			if ($cmbLektorMitarbeiter=='all' || $cmbLektorMitarbeiter=='Student')			  
			{
				if($txtSearchQuery == "" || $txtSearchQuery == "*" || $txtSearchQuery == "*.*")
				{
					if($cmbChoice == "all")
						$sql_query.= " SELECT DISTINCT person_id,uid, titelpre, titelpost, nachname, vorname, vornamen,(''::varchar) AS standort_kurzbz, (''::varchar) AS teltw, (uid || '@".DOMAIN."') AS emailtw, foto, studiengang_kz, vw_student.semester, ''::varchar as ort, alias,CASE WHEN (TRUE) THEN 'StudentIn' ELSE 'StudentIn' END as personenart FROM campus.vw_student WHERE vw_student.semester<10 ";
					else
						$sql_query.= " SELECT DISTINCT person_id,uid, titelpre,titelpost, nachname, vorname, vornamen,(''::varchar) AS standort_kurzbz, (''::varchar) AS teltw, (uid || '@".DOMAIN."') AS emailtw, foto, vw_student.studiengang_kz, vw_student.semester, ''::varchar as ort, alias,CASE WHEN (TRUE) THEN 'StudentIn' ELSE 'StudentIn' END as personenart FROM campus.vw_student JOIN public.tbl_benutzerfunktion using(uid) WHERE vw_student.semester<10 AND funktion_kurzbz='$cmbChoice' AND aktiv ".$sql_extend_query;
				}
				else
				{
					$txtSearchQuery = addslashes($txtSearchQuery);
					if($cmbChoice == "all")
						$sql_query.= " SELECT DISTINCT person_id,uid, titelpre, titelpost, nachname, vorname, vornamen,(''::varchar) AS standort_kurzbz, (''::varchar) AS teltw, (uid || '@".DOMAIN."') AS emailtw, foto, studiengang_kz, semester, ''::varchar as ort, alias,CASE WHEN (TRUE) THEN 'StudentIn' ELSE 'StudentIn' END as personenart FROM campus.vw_student WHERE semester<10 AND (LOWER(nachname) LIKE LOWER('%$txtSearchQuery%') OR uid LIKE LOWER('%$txtSearchQuery%') OR LOWER(vorname) LIKE LOWER('%$txtSearchQuery%') OR LOWER(nachname || ' ' || vorname) LIKE LOWER('%$txtSearchQuery%') OR LOWER(vorname || ' ' || nachname) LIKE LOWER('%$txtSearchQuery%')) ";
					else
						$sql_query.= " SELECT DISTINCT person_id,uid, titelpre, titelpost, nachname, vorname, vornamen,(''::varchar) AS standort_kurzbz, (''::varchar) AS teltw, (uid || '@".DOMAIN."') AS emailtw, foto, vw_student.studiengang_kz, vw_student.semester, ''::varchar as ort, alias,CASE WHEN (TRUE) THEN 'StudentIn' ELSE 'StudentIn' END as personenart FROM campus.vw_student JOIN public.tbl_benutzerfunktion USING(uid) WHERE vw_student.semester <10 AND ((LOWER(nachname) LIKE LOWER('%$txtSearchQuery%') OR uid LIKE LOWER('%$txtSearchQuery%') OR LOWER(vorname) LIKE LOWER('%$txtSearchQuery%') OR LOWER(nachname || ' ' || vorname) LIKE LOWER('%$txtSearchQuery%') OR LOWER(vorname || ' ' || nachname) LIKE LOWER('%$txtSearchQuery%')) AND funktion_kurzbz='$cmbChoice') AND aktiv=TRUE ".$sql_extend_query;
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
##					$worksheet->write($zeile,$spalte,'Personensuche FH Technikum Wien erstellt am '.date("d.m.Y"), $format_bold);
					
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

						if($row->teltw != "")
						{
							$vorwahl = '';
							if($row->standort_kurzbz!='')
							{
								$qry = "SELECT telefon FROM public.tbl_standort, public.tbl_adresse, public.tbl_firma WHERE tbl_standort.standort_kurzbz='$row->standort_kurzbz' AND tbl_standort.adresse_id=tbl_adresse.adresse_id AND tbl_adresse.firma_id=tbl_firma.firma_id";
								if($result_tel = $db->db_query($qry))
								{
									if($result_tel && $row_tel = $db->db_fetch_object($result_tel))
										$vorwahl = $row_tel->telefon;
								}	
							}	
						}
						$tel=(isset($row->teltw) && $row->teltw?$vorwahl.' - '.$row->teltw:'&nbsp;');

						$worksheet->write($zeile,++$spalte,$tel);
						$maxlength[$spalte]=strlen($tel);
						
						if ($row->alias)						
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
							if($row->studiengang_kz != -1)
								$row->semester='';
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
						    //Column widths
						    $w=array(100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100);
							
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
						
						//Colored table
						function FancyTable($header,$data,$items,$rows_anz)
						{
						    //Colors, line width and bold font
##						    $this->SetFillColor(95,158,160);
							
						    $this->SetFillColor(102,205,170);
						    $this->SetTextColor(255);
						    $this->SetDrawColor(128,0,0);
						    $this->SetLineWidth(.3);
						    $this->SetFont('','B');
						    //Header
						    $w=array(100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100);
						    for($i=0;$i<count($header);$i++)
							{
##						        $this->Cell($w[$i],7,$header[$i],1,0,'C',true);
						        $this->Cell($rows_anz[$items[$i]] *1.4 ,7,$header[$i],1,0,'C',true);
							}	
						    $this->Ln();
						    //Color and font restoration 95,158,160
						    $this->SetFillColor(224,235,255);
							
						    $this->SetTextColor(0);
						    $this->SetFont('');
						    //Data
						    $fill=false;
						    foreach($data as $row)
						    {
						     	$i=0;
							    foreach($items as $col)
								{
									if (!isset($row->$col))
										die("Achtung ! Die Spalte $col wurde nicht in den Daten gefunden.");
##								    $this->Cell($w[$i],6,$row->$col,'LR',0,'L',$fill);
								    $this->Cell($rows_anz[$items[$i]] *1.4 ,6,$row->$col,'LR',0,'L',$fill);
									$i++;
								}
						        $this->Ln();
						        $fill=!$fill;
						    }
						}
					}					
					
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
							if($row->standort_kurzbz!='')
							{
								$qry = "SELECT telefon FROM public.tbl_standort, public.tbl_adresse, public.tbl_firma WHERE tbl_standort.standort_kurzbz='$row->standort_kurzbz' AND tbl_standort.adresse_id=tbl_adresse.adresse_id AND tbl_adresse.firma_id=tbl_firma.firma_id";
								if($result_tel = $db->db_query($qry))
								{
									if($result_tel && $row_tel = $db->db_fetch_object($result_tel))
										$vorwahl = $row_tel->telefon;
								}	
							}	
						}
						$row->teltw=(isset($row->teltw) && $row->teltw?$vorwahl.' - '.$row->teltw:'');
						if ($row->alias)						
							$mail=(isset($row->alias) && $row->alias?$row->alias.'@'.DOMAIN:'');
						else
							$mail=(isset($row->emailtw) && $row->emailtw?$row->emailtw:'');
						$row->email=$mail;
						$row->ort=(isset($row->ort) && $row->ort?$row->ort:'');

						$kurzbz='';
						if(isset($row->studiengang_kz) && $row->studiengang_kz != -1)
						{
							if ($stg_obj = new studiengang($row->studiengang_kz))
								$kurzbz=$stg_obj->kuerzel;
						}
						else
							$row->studiengang_kz='';
						$row->kurzbz=$kurzbz;
						$row->semester=(isset($row->semester) && $row->semester && $row->semester!= -1?$row->semester:'');
						
						$verband='';
						$gruppe='';
						$verteiler='';
						$kurzbz='';
						if(isset($row->studiengang_kz) && $row->studiengang_kz != -1  && $row->studiengang_kz != '' )
						{
							if ($std_obj = new student($row->uid))
							{
								$verband=$std_obj->verband;
								$gruppe=$std_obj->gruppe;
							}
							$kurzbz=strtolower($kurzbz);
							$verband=strtolower($verband);
							if($row->studiengang_kz != -1)
								$row->semester='';
							$verteiler=trim($kurzbz.$row->semester.$verband.$gruppe);
							$verteiler.=($verteiler?'@'.DOMAIN:'');
						}
						$row->kurzbz=$kurzbz;
						$row->verband=$verband;
						$row->gruppe=$gruppe;
						$row->verteiler=$verteiler;
						
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
##							echo "<br>$key  $value $anz ";
							if (!isset($rows_anz[$key]) || $rows_anz[$key]<$anz)
								$rows_anz[$key]=$anz;
						}
						$rows[]=$row;
					}	
					
					// Creating a workbook
					$orientation='l'; // 'p' 
					$pdf = new PDF($orientation);

					//Column titles
					$header=array('Titel','Vorname','Nachname','Tel.nr','E-Mail Adresse','Raum','Std-Kz','Sem','Hauptverteiler','Kz','Handy','Funktion' );
					//Data loading
					$item=array('titelpre','vorname','nachname','teltw','email','ort','studiengang_kz','semester','verteiler','kz','firmenhandy','funktion' );
					foreach ($item as $key => $value) 
					{
							$anz=strlen($header[$key]);
							if (!isset($rows_anz[$value]) || $rows_anz[$value]<$anz)
								$rows_anz[$value]=$anz;
					}

					$pdf->SetFont('Arial','',6);
					$pdf->AddPage();
					if (count($rows)>0)
					{
#						$pdf->BasicTable($header,$rows,$item,$rows_anz);
#						$pdf->AddPage();
#						$pdf->ImprovedTable($header,$rows,$item,$rows_anz);
#						$pdf->AddPage();
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
    <td><table class="tabcontent">
      <tr>
        <td class="ContentHeader"><font class="ContentHeader">&nbsp;Personensuche <?php echo CAMPUS_NAME;?></font></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
	  
	  <tr>
	  	<form target="_self" method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>" name="SearchFormular" id="SearchFormular" >
	  	<td nowrap>
	  	  Suche nach:
	  	  <input type="text" name="txtSearchQuery" size="45" value="<?php echo $txtSearchQuery; ?>">

	  	  Kennzeichen
	  	  <select name="cmbLektorMitarbeiter">
			  <option <?php echo ($cmbLektorMitarbeiter=='all'?' selected="selected" ':'');  ;?> value="all">Alle Personen</option>
			  <option <?php echo ($cmbLektorMitarbeiter=='Mitarbeiter_Alle'?' selected="selected" ':'');?> value="Mitarbeiter_Alle">MitarbeiterIn</option>
			  <option <?php echo ($cmbLektorMitarbeiter=='Mitarbeiter_Fix'?' selected="selected" ':'');?> value="Mitarbeiter_Fix">MitarbeiterIn Fix</option>
			  <option <?php echo ($cmbLektorMitarbeiter=='Mitarbeiter_Extern'?' selected="selected" ':'');?> value="Mitarbeiter_Extern">MitarbeiterIn Extern</option>
			  <option <?php echo ($cmbLektorMitarbeiter=='Student'?' selected="selected" ':'');?> value="Student">StudentIn</option>
	  	  </select>

	  	  in Gruppe
	  	  <select name="cmbChoice">
			  <option value="all">Alle Kategorien</option>
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
	  	  <input onclick="document.SearchFormular.target = '_self';"  type="submit" name="btnSearch" value="Suchen">
		  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		  <input type="hidden" name="do_search">
		  <input onclick="document.SearchFormular.target = '_blank';" type="Image" src="../../../skin/images/excel.gif" type="submit" name="btnExcel" value="Excel">
		  <input onclick="document.SearchFormular.target = '_blank';" type="submit" name="do_excel" value="Excel">
		  &nbsp;&nbsp;&nbsp;
		  <input onclick="document.SearchFormular.target = '_blank';" type="Image" src="../../../skin/images/pdfs.jpg" height="32" type="submit" name="btnPdf" value="Pdf">
		  <input onclick="document.SearchFormular.target = '_blank';" type="submit" name="do_pdf" value="Pdf">
		</td>
		</form>
		

	  </tr>
	  <tr>
	  	<td>&nbsp;</td>
	  </tr>
	  <tr>
	  	<td nowrap>
		<?php
		if($num_rows > 0)
		{
			
				echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"1\" width=\"100%\">";
				echo "<tr>
						<td align=\"left\" class=\"ContentHeader\" class='tdwrap'><font class=\"ContentHeader\">&nbsp;Titel</font></td>
						<td align=\"left\" class=\"ContentHeader\" class='tdwrap'><font class=\"ContentHeader\">&nbsp;Vorname</font></td>
						<td align=\"left\" class=\"ContentHeader\" class='tdwrap'><font class=\"ContentHeader\">&nbsp;Nachname</font></td>
						<td align=\"left\" class=\"ContentHeader\" class='tdwrap'><font class=\"ContentHeader\">&nbsp;Telefonnummer</font></td>
						<td align=\"left\" class=\"ContentHeader\" class='tdwrap'><font class=\"ContentHeader\">&nbsp;E-Mail Adresse</font></td>
						<td align=\"left\" class=\"ContentHeader\" class='tdwrap'><font class=\"ContentHeader\">&nbsp;Raum</font></td>
						<td align=\"left\" class=\"ContentHeader\" class='tdwrap'><font class=\"ContentHeader\">&nbsp;Studiengang</font></td>
						<td align=\"left\" class=\"ContentHeader\" class='tdwrap'><font class=\"ContentHeader\">&nbsp;Semester</font></td>
						<td align=\"left\" class=\"ContentHeader\" class='tdwrap'><font class=\"ContentHeader\">&nbsp;Hauptverteiler</font></td>
						<td style=\"display:none;\" align=\"left\" class=\"ContentHeader\" class='tdwrap'><font class=\"ContentHeader\">&nbsp;Alias</font></td>
						<td align=\"left\" class=\"ContentHeader\" class='tdwrap'><font class=\"ContentHeader\">&nbsp;Kz</font></td>
						<td align=\"left\" class=\"ContentHeader\" class='tdwrap'><font class=\"ContentHeader\">&nbsp;Funktion</font></td>
						<td align=\"left\" class=\"ContentHeader\" class='tdwrap'><font class=\"ContentHeader\">&nbsp;Handy</font></td>								
				";
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
						if($row->teltw != "")
						{
							$vorwahl = '';
							if($row->standort_kurzbz!='')
							{
								$qry = "SELECT telefon FROM public.tbl_standort, public.tbl_adresse, public.tbl_firma WHERE tbl_standort.standort_kurzbz='$row->standort_kurzbz' AND tbl_standort.adresse_id=tbl_adresse.adresse_id AND tbl_adresse.firma_id=tbl_firma.firma_id";
								if($result_tel = $db->db_query($qry))
								{
									if($result_tel && $row_tel = $db->db_fetch_object($result_tel))
										$vorwahl = $row_tel->telefon;
								}	
							}	
						}
						echo '	<td '.($i % 2==0?'':' class="MarkLine" ').' align="left" class="tdwrap">&nbsp;'.(isset($row->teltw) && $row->teltw?$vorwahl.' - '.$row->teltw:'&nbsp;').'</td>';

						if ($row->alias)						
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
							if($row->studiengang_kz != -1)
								$row->semester='';
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
##								if(isset($row->person_id) && $row->personenart!='' && $row->personenart!='StudentIn')
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
##													$funktion_anf.=($anz_kontakt>1?'&raquo;':'&nbsp;').($row_kontakt->anmerkung?$row_kontakt->anmerkung:$row_kontakt->beschreibung).' '.$row_kontakt->kontakt;
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
			echo '<br>Bitte geben Sie einen Suchbegriff ein, nach dem gesucht werden soll.';
		else if($num_rows > 0)
			echo 'Es wurden '.$num_rows.' Eintr&auml;ge gefunden.';
		else if(isset($do_search))
			echo 'Es wurden keine Eintr&auml;ge gefunden.';
	?>
		</td>
	  </tr>
    </table></td>
	<td class="tdwidth30">&nbsp;</td>
  </tr>
</table>
</body>
</html>