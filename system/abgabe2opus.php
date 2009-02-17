<?php
/* Copyright (C) 2009 Technikum-Wien
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
 *
 *******************************************************************************************************
 *				abgabe2opus.php
 * 		abgabe2opus kopiert neue Abgaben ins opus
 *******************************************************************************************************/

require_once('../cis/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/studiengang.class.php');
require_once('../include/datum.class.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/datum.class.php');
require_once('../include/mail.class.php');

	//DB Verbindung herstellen
	if (!$conn = @pg_pconnect(CONN_STRING))
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');
		
	// zugriff auf mssql-datenbank
	if (!$conn_ext=mysql_connect (OPUS_SERVER, OPUS_USER, OPUS_PASSWD))
		die('Fehler beim Verbindungsaufbau!');
	mysql_select_db(OPUS_DB, $conn_ext);



$datum_obj = new datum();
$fehler='';
$error=false;
$begutachter1='';
$begutachter2='';
$verfasser='';
$abgabedatum='';
$datum='';
$institut='';
$typ='';
$bereich=2;
$stg='';
$row_opus=0;
$opus_url=OPUS_PATH_PAA;			//http://cis.technikum-wien.at/opus/htdocs/volltexte/2008/10/

	
echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Abgabe2OPUS</title>
<link rel="stylesheet" href="../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../include/js/tablesort/table.css" type="text/css">
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-9" />
<script src="../include/js/tablesort/table.js" type="text/javascript"></script>
<script language="Javascript">
</script>
</head>
<body class="Background_main"  style="background-color:#eeeeee">';

//****************************************************************************************************
//Einlesen Projektarbeiten
//****************************************************************************************************
$qry="SELECT tbl_fachbereich.bezeichnung as fb_bez, tbl_lehrveranstaltung.studiengang_kz as stg_kz, * FROM lehre.tbl_projektarbeit 
	JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) 
	JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id) 
	JOIN lehre.tbl_lehrfach USING(lehrfach_id) 
	JOIN public.tbl_fachbereich USING(fachbereich_kurzbz) 
	WHERE tbl_projektarbeit.note>0 AND tbl_projektarbeit.note<5 
	AND abgabedatum>".mktime(0, 0, 0, date('m')-3, date('d'), date('Y'));

if($erg=pg_query($conn, $qry))
{
	while($row=pg_fetch_object($erg))
	{
		//echo "--->".$row->projektarbeit_id.", ".$row->projekttyp_kurzbz.", ".$row->student_uid;
		//****************************************************************************************************
		//weitere benötigte Daten
		//****************************************************************************************************
		//verfasser
		$qry_std="SELECT * FROM public.tbl_benutzer 
			JOIN public.tbl_person on(tbl_person.person_id=tbl_benutzer.person_id) 
			WHERE uid='".$row->student_uid."';";
		if($result_std=pg_query($conn, $qry_std))
		{
			if(pg_num_rows($result_std)>0)
			{
				while($row_std=pg_fetch_object($result_std))
				{
					if(trim($verfasser)=='')
					{
						$verfasser=trim($row_std->titelpre." ".$row_std->vorname." ".$row_std->nachname." ".$row_std->titelpost);
					}
					else 
					{
						$verfasser.=" , ".trim($row_std->titelpre." ".$row_std->vorname." ".$row_std->nachname." ".$row_std->titelpost);
					}
				}
			}
			else 
			{
				$fehler.="\nKein Verfasser zugeordnet!";
				$error=true;
			}
		}
		else 
		{
			$mail = new mail('ruhan@technikum-wien.at', 'vilesci@technikum-wien.at', 'abgabe2opus', 'Quelldatenbanken konnten nicht geöffnet werden!');
			$mail->send();
		}
		//begutachter
		$qry_bet="SELECT * FROM lehre.tbl_projektbetreuer 
			JOIN public.tbl_person on(lehre.tbl_projektbetreuer.person_id=public.tbl_person.person_id) 
			WHERE projektarbeit_id='".$row->projektarbeit_id."'  
			AND (betreuerart_kurzbz='Betreuer' OR betreuerart_kurzbz='Begutachter' OR betreuerart_kurzbz='Erstbegutachter' OR betreuerart_kurzbz='Erstbegutachter');";
		if($result_bet=pg_query($conn, $qry_bet))
		{
			if(pg_num_rows($result_bet)>0)
			{
				while($row_bet=pg_fetch_object($result_bet))
				{
					if(trim($begutachter1)=='')
					{
						$begutachter1=trim($row_bet->titelpre." ".$row_bet->vorname." ".$row_bet->nachname." ".$row_bet->titelpost);
					}
					else 
					{
						$begutachter1.=" , ".trim($row_bet->titelpre." ".$row_bet->vorname." ".$row_bet->nachname." ".$row_bet->titelpost);
					}
				}
			}
			else 
			{
				$fehler.="\nKein Begutachter zugeordnet!";
				$error=true;
			}
		}
		else 
		{
			$mail = new mail('ruhan@technikum-wien.at', 'vilesci@technikum-wien.at', 'abgabe2opus', 'Quelldatenbanken konnten nicht geöffnet werden!');
			$mail->send();
		}
		if($row->projekttyp_kurzbz!='Bachelor')
		{
			$qry_bet="SELECT * FROM lehre.tbl_projektbetreuer 
				JOIN public.tbl_person on(lehre.tbl_projektbetreuer.person_id=public.tbl_person.person_id) 
				WHERE projektarbeit_id='".$row->projektarbeit_id."'  
				AND (betreuerart_kurzbz='Zweitbetreuer' OR betreuerart_kurzbz='Zweitbegutachter');";
			if($result_bet=pg_query($conn, $qry_bet))
			{
				if(pg_num_rows($result_bet)>0)
				{
					while($row_bet=pg_fetch_object($result_bet))
					{
						if(trim($begutachter2)=='')
						{
							$begutachter2=trim($row_bet->titelpre." ".$row_bet->vorname." ".$row_bet->nachname." ".$row_bet->titelpost);
						}
						else 
						{
							$begutachter2.=" , ".trim($row_bet->titelpre." ".$row_bet->vorname." ".$row_bet->nachname." ".$row_bet->titelpost);
						}
					}
				}
				else 
				{
					$fehler.="\nKein Zweitbegutachter zugeordnet!";
					$error=true;
				}
			}
			else 
			{
				$mail = new mail('ruhan@technikum-wien.at', 'vilesci@technikum-wien.at', 'abgabe2opus', 'Quelldatenbanken konnten nicht geöffnet werden!');
				$mail->send();
			}
		}
		//Institute
		if($row->fb_bez==NULL || trim($row->fb_bez)=='')
		{
			$fehler.="\nInstitut nicht gefunden!";
			$error=true;	
		}
		else 
		{
			$qry_inst="SELECT * FROM institute_de WHERE trim(name)='".trim($row->fb_bez)."';";
			if($result_inst = mysql_query($qry_inst,$conn_ext))
			{
				if(mysql_num_rows($result_inst)>0)
				{
					while($row_inst=mysql_fetch_object($result_inst))
					{
						$institut=$row_inst->nr;
					}
				}
				else 
				{
					$fehler.="\nInstitutsname nicht gefunden!";
					$error=true;	
				}
			}
		}
		if($row->kontrollschlagwoerter==NULL || $row->kontrollschlagwoerter=='' || $row->abstract==NULL || $row->abstract=='' || $row->abstract_en==NULL || $row->abstract_en=='' )
		{			
			$fehler=$row->student_uid.": Projektarbeit (".$row->projekttyp_kurzbz.") ".$row->projektarbeit_id.$fehler;
			if($row->kontrollschlagwoerter==NULL || $row->kontrollschlagwoerter=='')
			{
				$fehler.="\nKontrollierte Schlagwörter nicht eingegeben!";
				$error=true;
			}
			if($row->abstract==NULL || $row->abstract=='')
			{
				$fehler.="\nAbstract nicht eingegeben!";
				$error=true;
			}
			if($row->abstract_en==NULL || $row->abstract_en=='')
			{
				$fehler.="\nEnglischer Abstract nicht eingegeben!";
				$error=true;
			}
			if($row->seitenanzahl==NULL || $row->seitenanzahl=='')
			{
				$fehler.="\nSeitenanzahl nicht eingegeben!";
				$error=true;
			}
			if($row->stg_kz==NULL || $row->stg_kz=='' || $row->stg_kz==0)
			{
				$fehler.="\nStudiengang nicht gefunden!";
				$error=true;
			}
			if($row->studiensemester_kurzbz==NULL || $row->studiensemester_kurzbz=='')
			{
				$fehler.="\nStudiensemester nicht gefunden!";
				$error=true;
			}
		}
		
		if(!$error)
		{
			//*******************************************************************************************
			//Einfügen in OPUS
			//*******************************************************************************************
					
			//	Originaltitel der Arbeit				title
			//	Titel der Arbeit in Englisch			title_en
			//	1. Verfasser(innen)name 				(opus_autor) source_opus, creator_name, 1
			//	Universität								publisher_university = FHTW
			//	Typ der Arbeit							type (Nummer)								7=Diplomarbeit, 25=Bachelorarbeit
			//	Institut								(opus_inst) source_opus, inst_nr			
			//	Studiengang								stg_nr
			//	Datumsfeld								datum
			//	1. Gutachter							begutachter1
			//	2. Gutachter							begutachter2
			//	Kontrollierte Schlagwörter (Deutsch)	subject_swd
			//	Schlagwörter dt							subject_uncontrolled_german
			//	Schlagwörter en							subject_uncontrolled_english
			//	Abstract								description
			//	Abstract en								description2
			//	Abstract Sprache 1						description_lang = ger
			//	Abstract Sprache 2						description2_lang = eng
			//	Sachgrupppe								sachgruppe_ddc = 000						000=Allgemeines, Wissenschaft
			//	Jahr									date_year
			//	Seitenanzahl							seitenanzahl
			//	Studiensemester							studiensemester_kurzbz
			//	Projektabeit ID							projektarbeit_id
			//	Sprache									language = ger			
			//	Zugriffsbeschränkung					bereich_id									1=uneingeschränkt, 2=innerh. Campus
			
			if($row->projekttyp_kurzbz=='Diplom')
				$typ=7;
			if($row->projekttyp_kurzbz=='Bachelor')
				$typ=25;
			$stg=($row->stg_kz<1000?'0'.$row->stg_kz:$row->stg_kz);
			$qry_src="SELECT max(source_opus) as source FROM opus";
			if($result_src = mysql_query($qry_src,$conn_ext))
			{
				while($row_src=mysql_fetch_object($result_src))
				{
					$row_opus=$row_src->source+1;
				}
			}
			$qry_ins="INSERT INTO opus 
				(source_opus, title, title_en, publisher_university, type, stg_nr, datum, begutachter1, begutachter2, subject_swd, 
				subject_uncontrolled_german, subject_uncontrolled_english, description, description2, description_lang, description2_lang, 
				sachgruppe_ddc, date_year, seitenanzahl, studiensemester_kurzbz, projektarbeit_id, language, bereich_id, date_creation) values 
				('".$row_opus."', '".addslashes($row->titel)."', '".addslashes($row->titel_english)."', 'FHTW', '".$typ."', '".$stg."', '".$row->abgabedatum."', '".addslashes($begutachter1)."', '".addslashes($begutachter2)."', '".addslashes($row->kontrollschlagwoerter)."', 
				'".addslashes($row->schlagwoerter)."', '".addslashes($row->schlagwoerter_en)."', '".addslashes($row->abstract)."', '".addslashes($row->abstract_en)."', 'ger', 'eng', 
				'000', '".$datum_obj->formatDatum($row->abgabedatum,'Y')."', '".$row->seitenanzahl."', '".$row->studiensemester_kurzbz."', '".$row->projektarbeit_id."', 'ger', '".$bereich."', UNIX_TIMESTAMP())";
			$qry_cre="INSERT INTO opus_autor (source_opus, creator_name, reihenfolge) VALUES ('".$row_opus."', '".$verfasser."', '1')";
			$qry_inst="INSERT INTO opus_inst (source_opus, inst_nr) VALUES ('".$row_opus."', '".$institut."')";
			
			$opus_url.="/".$datum_obj->formatDatum($row->abgabedatum,'Y')."/".$row_opus."/pdf/";
			
			$qry="START TRANSACTION";

			//echo $qry.$qry_ins.$qry_cre.$qry_inst;
			if(!$result=mysql_query($qry))
			{
				echo nl2br("\n\nTransaktion nicht begonnen! \n".mysql_errno($conn_ext) . ": " . mysql_error($conn_ext));
			}
			else 
			{
				if(!$result=mysql_query($qry_ins))
				{
					echo nl2br("\n\nTransaktion abgebrochen! \n".mysql_errno($conn_ext) . ": " . mysql_error($conn_ext));
					mysql_query('ROLLBACK',$conn_ext);
				}
				else 
				{
					if(!$result=mysql_query($qry_cre))
					{
						echo nl2br("\n\nTransaktion abgebrochen!! \n".mysql_errno($conn_ext) . ": " . mysql_error($conn_ext));
						if(!$result=mysql_query('ROLLBACK',$conn_ext))
						{
							echo nl2br("\n\nRollback nicht durchgef&uuml;hrt. \n".mysql_errno($conn_ext) . ": " . mysql_error($conn_ext));
						}
					}
					else 
					{
						if(!$result=mysql_query($qry_inst))
						{
							echo nl2br("\n\nTransaktion abgebrochen!!! \n".mysql_errno($conn_ext) . ": " . mysql_error($conn_ext));
							mysql_query('ROLLBACK',$conn_ext);
						}
						else 
						{
							//Kopieren der Abgabedatei
							$qry_file="SELECT * FROM campus.tbl_paabgabe WHERE projektarbeit_id='".$row->projektabgabe_id."' and projektabgabetyp_kurzbz='end' ORDER BY abgabedatum desc LIMIT 1";
							if($result=mysql_query($qry))
							{
								if($row_inst=mysql_fetch_object($result_inst))
								{
									copy($_SERVER['DOCUMENT_ROOT'].PAABGABE_PATH.$row_file->paabgabe_id.'_'.$row->student_uid.'.pdf',$opus_url.$row_file->paabgabe_id.'_'.$row->student_uid.'.pdf');
									//Überprüfen, ob Datei wirklich kopiert wurde
									if(isfile($opus_url.$row_file->paabgabe_id.'_'.$row->student_uid.'.pdf'))
									{
										//COMMIT durchführen
										if(!$result=mysql_query('COMMIT',$conn_ext))
										{
											mysql_query('ROLLBACK',$conn_ext);
											echo "Commit nicht ausgef&um;hrt! \n".mysql_errno($conn_ext) . ": " . mysql_error($conn_ext);
										}
									}
									else 
									{
										mysql_query('ROLLBACK',$conn_ext);
										echo "Datei wurde nicht kopiert! \n".mysql_errno($conn_ext) . ": " . mysql_error($conn_ext);
									}
								}
								else 
								{
									mysql_query('ROLLBACK',$conn_ext);
									echo "Abgabe konnte nicht geladen werden! \n".mysql_errno($conn_ext) . ": " . mysql_error($conn_ext);
								}
							}
							else 
							{
								mysql_query('ROLLBACK',$conn_ext);
								echo "Eintragung der Abgabe nicht gefunden! \n".mysql_errno($conn_ext) . ": " . mysql_error($conn_ext);
							}	
							
						}
					}
				}
			}
			
		}
		else 
		{
			$fehler.="\n-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------";
			echo nl2br($fehler."\nBegutachter1: <b>".$begutachter1."</b>\nBegutachter2: <b>".$begutachter2."</b>\nTitel: <b>".$row->titel."</b>\nTitel en: <b>".$row->titel_english."</b>\n");
			echo nl2br("Verfasser: <b>".$verfasser."</b>\nInstitut: <b>".$institut."</b>\nStudiengang: <b>".($row->stg_kz<1000?'0'.$row->stg_kz:$row->stg_kz)."</b>\nDatum: <b>".$datum_obj->formatDatum($row->abgabedatum,'d.m.Y')."</b>\n");
			echo nl2br("Kontr. Schlagw&ouml;rter: <b>".$row->kontrollschlagwoerter."</b>\nSchlagw&ouml;rter dt: <b>".$row->schlagwoerter."</b>\nSchlagw&ouml;rter en: <b>".$row->schlagwoerter_en."</b>\n");
			echo nl2br("Abstract: <b>".$row->abstract."</b>\nAbstract_en: <b>".$row->abstract_en."</b>\nSeitenanzahl: <b>".$row->seitenanzahl."</b>\nStudiensemester: <b>".$row->studiensemester_kurzbz."</b>\n");
			echo nl2br("Projektarbeit ID: <b>".$row->projektarbeit_id."</b>\nTyp der Arbeit: <b>".$row->projekttyp_kurzbz."</b>\n");
			$fehler='';
			
			$mail = new mail('ruhan@technikum-wien.at', 'vilesci@technikum-wien.at', 'abgabe2opus', 'Aufgetretene Fehler: \n'.$fehler);
			$mail->send();
			
		}
		
		

	}
}
else 
{
	$mail = new mail('ruhan@technikum-wien.at', 'vilesci@technikum-wien.at', 'abgabe2opus', 'Quelldatenbank konnte nicht geöffnet werden!');
	$mail->send();
}


?>