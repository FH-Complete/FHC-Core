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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
/**
 * Reihungstestverwaltung
 * 
 * - Anlegen und Bearbeiten von Reihungstestterminen
 * - Export von Anwesenheitslisten als Excel
 * - Uebertragung der Reihungstestpunkte ins FAS
 * 
 * Parameter:
 * excel ... wenn gesetzt, dann wird die Anwesenheitsliste als Excel exportiert
 */
	require_once('../../config/vilesci.config.inc.php');
	require_once('../../include/functions.inc.php');
	require_once('../../include/studiengang.class.php');
	require_once('../../include/reihungstest.class.php');
	require_once('../../include/ort.class.php');
	require_once('../../include/datum.class.php');
	require_once('../../include/benutzerberechtigung.class.php');
	require_once('../../include/pruefling.class.php');
	require_once('../../include/person.class.php');
	require_once('../../include/prestudent.class.php');
	require_once('../../include/Excel/excel.php');
	
	if (!$db = new basis_db())
			die('Es konnte keine Verbindung zum Server aufgebaut werden.');
	
	$user = get_uid();
	$datum_obj = new datum();
	$stg_kz = (isset($_GET['stg_kz'])?$_GET['stg_kz']:'-1');
	$reihungstest_id = (isset($_GET['reihungstest_id'])?$_GET['reihungstest_id']:'');
	$prestudent_id = (isset($_GET['prestudent_id'])?$_GET['prestudent_id']:'');
	$rtpunkte = (isset($_GET['rtpunkte'])?$_GET['rtpunkte']:'');
	$neu = (isset($_GET['neu'])?true:false);
	$stg_arr = array();
	$error = false;
	
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);
	
	if(!$rechte->isBerechtigt('lehre/reihungstest'))
		die('Sie haben keine Berechtigung fuer diese Seite');
	
	$studiengang = new studiengang();
	$studiengang->getAll('typ, kurzbz', false);
		
	if(isset($_GET['excel']))
	{	
		$reihungstest = new reihungstest();
		if($reihungstest->load($_GET['reihungstest_id']))
		{
			// Creating a workbook
			$workbook = new Spreadsheet_Excel_Writer();
			$workbook->setVersion(8);
			// sending HTTP headers
			$workbook->send("Anwesenheitsliste_Reihungstest_".$reihungstest->datum.".xls");
			
			// Creating a worksheet
			$worksheet =& $workbook->addWorksheet("Reihungstest");
			$worksheet->setInputEncoding('utf-8');
			//Formate Definieren
			$format_bold =& $workbook->addFormat();
			$format_bold->setBold();
					
			$worksheet->write(0,0,'Anwesenheitsliste Reihungstest '.$datum_obj->convertISODate($reihungstest->datum).' '.$reihungstest->uhrzeit.' Uhr '.$reihungstest->anmerkung.', erstellt am '.date('d.m.Y'), $format_bold);
			//Ueberschriften
			$i=0;
			$worksheet->write(2,$i,"Vorname", $format_bold);
			$maxlength[$i] = 7;
			$worksheet->write(2,++$i,"Nachname", $format_bold);
			$maxlength[$i] = 8;
			$worksheet->write(2,++$i,"Geburtsdatum", $format_bold);
			$maxlength[$i] = 12;
			$worksheet->write(2,++$i,"Studiengang", $format_bold);
			$maxlength[$i] = 11;
			$worksheet->write(2,++$i,"bereits absolvierte RTs", $format_bold);
			$maxlength[$i] = 18;
			$worksheet->write(2,++$i,"EMail", $format_bold);
			$maxlength[$i] = 5;
			$worksheet->write(2,++$i,"STRASSE", $format_bold);
			$maxlength[$i] = 6;
			$worksheet->write(2,++$i,"PLZ", $format_bold);
			$maxlength[$i] = 3;
			$worksheet->write(2,++$i,"ORT", $format_bold);
			$maxlength[$i] = 3;
			
			$qry = "SELECT *, (SELECT kontakt FROM tbl_kontakt WHERE kontakttyp='email' AND person_id=tbl_prestudent.person_id AND zustellung=true LIMIT 1) as email FROM public.tbl_prestudent JOIN public.tbl_person USING(person_id) WHERE reihungstest_id='$reihungstest->reihungstest_id' ORDER BY nachname, vorname";

			if($result = $db->db_query($qry))
			{
				$zeile=3;
				while($row = $db->db_fetch_object($result))
				{
					$i=0;
					$pruefling = new pruefling();
					
					$prestudent = new prestudent();
					$prestudent->getPrestudenten($row->person_id);
					$rt_in_anderen_stg='';
					foreach($prestudent->result as $item)
					{
						if($item->prestudent_id!=$row->prestudent_id)
						{
							$erg = $pruefling->getReihungstestErgebnis($item->prestudent_id);
							if($erg!=0)
							{
								$rt_in_anderen_stg.=number_format($erg,2).' Punkte im Studiengang '.$studiengang->kuerzel_arr[$item->studiengang_kz]."\n";
							}
							
						}
					}
					
					$worksheet->write($zeile,$i, $row->vorname);
					if(strlen($row->vorname)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($row->vorname);
					
					$worksheet->write($zeile,++$i,$row->nachname);
					if(strlen($row->nachname)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($row->nachname);
					
					$worksheet->write($zeile,++$i,$datum_obj->convertISODate($row->gebdatum));
					if(strlen($row->gebdatum)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($row->gebdatum);
					
					$worksheet->write($zeile,++$i,$studiengang->kuerzel_arr[$row->studiengang_kz]);
					if(strlen($studiengang->kuerzel_arr[$row->studiengang_kz])>$maxlength[$i])
						$maxlength[$i] = mb_strlen($studiengang->kuerzel_arr[$row->studiengang_kz]);
						
					$worksheet->write($zeile,++$i,$rt_in_anderen_stg);
					if(strlen($rt_in_anderen_stg)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($rt_in_anderen_stg);
					
					$worksheet->write($zeile,++$i,$row->email);
					if(strlen($row->email)>$maxlength[$i])
						$maxlength[$i] = mb_strlen($row->email);
					
					$qry = "SELECT * FROM public.tbl_adresse WHERE person_id='$row->person_id' AND zustelladresse=true LIMIT 1";
					if($result_adresse = $db->db_query($qry))
					{
						if($row_adresse = $db->db_fetch_object($result_adresse))
						{
							$worksheet->write($zeile,++$i,$row_adresse->strasse);
							if(strlen($row_adresse->strasse)>$maxlength[$i])
								$maxlength[$i] = mb_strlen($row_adresse->strasse);
								
							$worksheet->write($zeile,++$i,$row_adresse->plz);
							if(strlen($row_adresse->plz)>$maxlength[$i])
								$maxlength[$i] = mb_strlen($row_adresse->plz);
							
							$worksheet->write($zeile,++$i,$row_adresse->ort);
							if(strlen($row_adresse->ort)>$maxlength[$i])
								$maxlength[$i] = mb_strlen($row_adresse->ort);						
						}
					}
					$zeile++;					
				}
			}
			//Die Breite der Spalten setzen
			foreach($maxlength as $i=>$breite)
				$worksheet->setColumn($i, $i, $breite+2);
		    
			$workbook->close();
		}
		else 
		{
			echo 'Reihungstest wurde nicht gefunden!';
		}
	}
	else 
	{
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//DE" "http://www.w3.org/TR/html4/strict.dtd">
				<html>
				<head>
				<title>Reihungstest</title>
				<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
				<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
				<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
				<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
				</head>
				<body class="Background_main">
				<h2>Reihungstest - Verwaltung</h2>';
		
		// Speichern eines Reihungstesttermines
		if(isset($_POST['speichern']))
		{
			if(!$rechte->isBerechtigt('lehre/reihungstest', null, 'sui'))
				die('Sie haben keine Berechtigung fuer diese Aktion');
			$reihungstest = new reihungstest();
			
			if(isset($_POST['reihungstest_id']) && $_POST['reihungstest_id']!='')
			{
				//Reihungstest laden
				if(!$reihungstest->load($_POST['reihungstest_id']))
					die($reihungstest->errormsg);
				$reihungstest->new = false;
			}
			else 
			{
				//Neuen Reihungstest anlegen
				$reihungstest->new=true;
				$reihungstest->insertvon = $user;
				$reihungstest->insertamum = date('Y-m-d H:i:s');
			}
			
			//Datum und Uhrzeit pruefen
			if($_POST['datum']!='' && !$datum_obj->checkDatum($_POST['datum']))
			{
				echo '<span class="input_error">Datum ist ungueltig. Das Datum muss im Format DD.MM.JJJJ eingegeben werden<br></span>';
				$error = true;
			}
			if($_POST['uhrzeit']!='' && !$datum_obj->checkUhrzeit($_POST['uhrzeit']))
			{
				echo '<span class="input_error">Uhrzeit ist ungueltig:'.$_POST['uhrzeit'].'. Die Uhrzeit muss im Format HH:MM:SS angegeben werden!<br></span>';
				$error = true;
			}
			
			if(!$error)
			{
				$reihungstest->studiengang_kz = $_POST['studiengang_kz'];
				$reihungstest->ort_kurzbz = $_POST['ort_kurzbz'];
				$reihungstest->anmerkung = $_POST['anmerkung'];
				$reihungstest->datum = $datum_obj->formatDatum($_POST['datum']);
				$reihungstest->uhrzeit = $_POST['uhrzeit'];
				$reihungstest->updateamum = date('Y-m-d H:i:s');
				$reihungstest->updatevon = $user;
				
				if($reihungstest->save())
				{
					echo '<b>Daten wurden erfolgreich gespeichert</b> <script>window.opener.StudentReihungstestDropDownRefresh();</script>';
					$reihungstest_id = $reihungstest->reihungstest_id;
					$stg_kz = $reihungstest->studiengang_kz;
				}
				else
				{
					echo '<span class="input_error">Fehler beim Speichern der Daten: '.$reihungstest->errormsg.'</span>';
				}
			}
			$neu=false;
		}

		// Uebertraegt die Punkte eines Prestudenten ins FAS
		if(isset($_GET['type']) && $_GET['type']=='savertpunkte')
		{
			$prestudent = new prestudent();
			$prestudent->load($prestudent_id);
			
			if($rechte->isBerechtigt('admin') || $rechte->isBerechtigt('assistenz', $prestudent->studiengang_kz, 'suid'))
			{
				$prestudent->rt_punkte1 = str_replace(',','.',$rtpunkte);
				$prestudent->punkte = str_replace(',','.',$prestudent->rt_punkte1 + $prestudent->rt_punkte2);
				$prestudent->reihungstestangetreten=true;
				$prestudent->save(false);
			}
			else 
			{
				echo '<span class="input_error"><br>Sie haben keine Berechtigung zur Uebernahme der Punkte fuer '.$row->nachname.' '.$row->vorname.'</span>';
			}
		}
		
		// Uebertraegt alle Punkte eines Reihungstests ins FAS
		if(isset($_GET['type']) && $_GET['type']=='saveallrtpunkte')
		{
			$errormsg='';
			$qry = "SELECT prestudent_id, studiengang_kz, nachname, vorname 
					FROM public.tbl_prestudent JOIN public.tbl_person USING(person_id) 
					WHERE reihungstest_id='".addslashes($reihungstest_id)."'";
			// AND (rt_punkte1='' OR rt_punkte1 is null)";
			if($result = $db->db_query($qry))
			{
				while($row = $db->db_fetch_object($result))
				{
					if($rechte->isBerechtigt('admin') || $rechte->isBerechtigt('assistenz', $row->studiengang_kz, 'suid'))
					{
						$prestudent = new prestudent();
						$prestudent->load($row->prestudent_id);
						
						$pruefling = new pruefling();
						$rtpunkte = $pruefling->getReihungstestErgebnis($row->prestudent_id);
						
						$prestudent->rt_punkte1 = str_replace(',','.',$rtpunkte);
						$prestudent->punkte = str_replace(',','.',$prestudent->rt_punkte1 + $prestudent->rt_punkte2);
						$prestudent->reihungstestangetreten=true;
						
						$prestudent->save(false);
					}
					else 
					{
						$errormsg .= "<br>Sie haben keine Berechtigung zur Uebernahme der Punkte fuer $row->nachname $row->vorname";
					}
				}
				if($errormsg!='')
				{
					echo '<span class="input_error">'.$errormsg.'</span>';
				}
			}			
		}
		
		echo '<br><table width="100%"><tr><td>';
		
		//Studiengang DropDown
		//$studiengang = new studiengang();
		//$studiengang->getAll('typ, kurzbz', false);
			
		echo "<SELECT name='studiengang' onchange='window.location.href=this.value'>";
		if($stg_kz==-1)
			$selected='selected';
		else 
			$selected='';
		
		echo "<OPTION value='".$_SERVER['PHP_SELF']."?stg_kz=-1' $selected>Alle Studiengaenge</OPTION>";
		foreach ($studiengang->result as $row) 
		{
			$stg_arr[$row->studiengang_kz] = $row->kuerzel;
			if($stg_kz=='')
				$stg_kz=$row->studiengang_kz;
			if($row->studiengang_kz==$stg_kz)
				$selected='selected';
			else 
				$selected='';
				
			echo "<OPTION value='".$_SERVER['PHP_SELF']."?stg_kz=$row->studiengang_kz' $selected>$row->kuerzel</OPTION>";
		}
		echo "</SELECT>";
		
		//Reihungstest DropDown
		$reihungstest = new reihungstest();
		if($stg_kz==-1)
			$reihungstest->getAll(date('Y').'-01-01'); //Alle Reihungstests ab diesem Jahr laden
		else
			$reihungstest->getReihungstest($stg_kz);
		
		echo "<SELECT name='reihungstest' id='reihungstest' onchange='window.location.href=this.value'>";
		foreach ($reihungstest->result as $row) 
		{
			//if($reihungstest_id=='')
			//	$reihungstest_id=$row->reihungstest_id;
			if($row->reihungstest_id==$reihungstest_id)
				$selected='selected';
			else
				$selected='';
				
			echo "<OPTION value='".$_SERVER['PHP_SELF']."?stg_kz=$stg_kz&reihungstest_id=$row->reihungstest_id' $selected>$row->datum $row->uhrzeit $row->ort_kurzbz $row->anmerkung</OPTION>";
		}
		echo "</SELECT>";
		echo "<INPUT type='button' value='Anzeigen' onclick='window.location.href=document.getElementById(\"reihungstest\").value;'>";
		echo "</td>";
		echo "<td align='right'>";
		if($rechte->isBerechtigt('basis/testtool', null, 'suid'))
		{
			echo '<a href="reihungstest_administration.php">Administration</a>';
		}
		echo "</td></tr></table><br>";
		
		if($reihungstest_id=='')
			$neu=true;
		$reihungstest = new reihungstest();
		
		if(!$neu)
		{
			if(!$reihungstest->load($reihungstest_id))
				die('Reihungstest existiert nicht: '.$reihungstest_id);
		}
		else 
		{
			if($stg_kz!=-1 && $stg_kz!='')
				$reihungstest->studiengang_kz = $stg_kz;
			$reihungstest_id='';
			$reihungstest->datum = date('Y-m-d');
			$reihungstest->uhrzeit = date('H:i:s');
		}
	
		
		echo "<INPUT type='button' value='Neuen Reihungstesttermin anlegen' onclick='window.location.href=\"".$_SERVER['PHP_SELF']."?stg_kz=$stg_kz&neu=true\"' >";
		//Formular zum Bearbeiten des Reihungstests
		echo '<HR>';
		echo "<FORM method='POST' action='".$_SERVER['PHP_SELF']."'>";
		echo "<input type='hidden' value='$reihungstest->reihungstest_id' name='reihungstest_id' />";
		
		//Studiengang DropDown
		echo "<table><tr><td>Studiengang</td><td><SELECT name='studiengang_kz'>";
		if($reihungstest->studiengang_kz=='')
			$selected = 'selected';
		else 
			$selected = '';
			
		echo "<OPTION value='' $selected>-- keine Auswahl --</OPTION>";
		foreach ($studiengang->result as $row)
		{
			if($row->studiengang_kz==$reihungstest->studiengang_kz)
				$selected = 'selected';
			else 
				$selected = '';
			
			echo "<OPTION value='$row->studiengang_kz' $selected>$row->kuerzel</OPTION>";
		}
		echo "</SELECT></TD></TR>";
		
		//Ort DropDown
		echo "<tr><td>Ort</td><td><SELECT name='ort_kurzbz'>";
		
		if($reihungstest->ort_kurzbz=='')
			$selected = 'selected';
		else 
			$selected = '';
		echo "<OPTION value='' $selected>-- keine Auswahl --</OPTION>";	
		
		$ort = new ort();
		$ort->getAll();
		
		foreach ($ort->result as $row) 
		{
			if($row->ort_kurzbz==$reihungstest->ort_kurzbz)
				$selected='selected';
			else 
				$selected='';
			
			echo "<OPTION value='$row->ort_kurzbz' $selected>$row->ort_kurzbz</OPTION>";
		}
		echo '</SELECT></td></tr>';
		echo '<tr><td>Anmerkung</td><td><input type="text" name="anmerkung" value="'.$reihungstest->anmerkung.'"></td></tr>';
		echo '<tr><td>Datum</td><td><input type="text" name="datum" value="'.$datum_obj->convertISODate($reihungstest->datum).'"></td></tr>';
		echo '<tr><td>Uhrzeit</td><td><input type="text" name="uhrzeit" value="'.$reihungstest->uhrzeit.'"> (Format: HH:MM:SS)</td></tr>';
		if(!$neu)
			$val = 'Änderung Speichern';
		else 
			$val = 'Neu anlegen';
		
		echo '<tr><td></td><td><input type="submit" name="speichern" value="'.$val.'"></td></tr>';
		echo '</table>';
		echo '</FORM>';
		
		echo '<HR>';
		
		if($reihungstest_id!='')
		{
			echo '<table width="100%"><tr><td>';
			echo "<a href='".$_SERVER['PHP_SELF']."?reihungstest_id=$reihungstest_id&excel=true'>Excel Export</a>";
			echo '</td><td align="right">';
			echo "<a href='".$_SERVER['PHP_SELF']."?reihungstest_id=$reihungstest_id&type=saveallrtpunkte'>alle Punkte ins FAS &uuml;bertragen</a>";
			echo '</td></tr></table>';
			
			//Liste der Interessenten die zum Reihungstest angemeldet sind
			$qry = "SELECT *, (SELECT kontakt FROM tbl_kontakt WHERE kontakttyp='email' AND person_id=tbl_prestudent.person_id AND zustellung=true LIMIT 1) as email FROM public.tbl_prestudent JOIN public.tbl_person USING(person_id) WHERE reihungstest_id='$reihungstest_id' ORDER BY nachname, vorname";
			$mailto = '';
			if($result = $db->db_query($qry))
			{
				echo 'Anzahl: '.$db->db_num_rows($result);
				$pruefling = new pruefling();
				
				echo "<table class='liste table-autosort:2 table-stripeclass:alternate table-autostripe'>
						<thead>
						<tr class='liste'>
							<th class='table-sortable:default'>Vorname</th>
							<th class='table-sortable:default'>Nachname</th>
							<th class='table-sortable:default'>Studiengang</th>
							<th class='table-sortable:default'>Geburtsdatum</th>
							<th class='table-sortable:default'>EMail</th>
							<th class='table-sortable:default'>bereits absolvierte RTs</th>
							<th class='table-sortable:default'>Ergebnis</th>
							<th class='table-sortable:default'>FAS</th>
						</tr>
						</thead>
						<tbody>";
				while($row = $db->db_fetch_object($result))
				{
					$rtergebnis = $pruefling->getReihungstestErgebnis($row->prestudent_id);
					$prestudent = new prestudent();
					$prestudent->getPrestudenten($row->person_id);
					$rt_in_anderen_stg='';
					foreach($prestudent->result as $item)
					{
						if($item->prestudent_id!=$row->prestudent_id)
						{
							$erg = $pruefling->getReihungstestErgebnis($item->prestudent_id);
							if($erg!=0)
							{
								$rt_in_anderen_stg.=number_format($erg,2).' Punkte im Studiengang '.$studiengang->kuerzel_arr[$item->studiengang_kz].'<br>';
							}
							
						}
					}
					echo "
						<tr>
							<td>$row->vorname</td>
							<td>$row->nachname</td>
							<td>".$stg_arr[$row->studiengang_kz]."</td>
							<td>".$datum_obj->convertISODate($row->gebdatum)."</td>
							<td><a href='mailto:$row->email'>$row->email</a></td>
							<td>$rt_in_anderen_stg</td>
							<td align='right'>".($rtergebnis==0?'-':number_format($rtergebnis,2,'.',''))."</td>
							<td align='right'>".($rtergebnis!=0 && $row->rt_punkte1==''?'<a href="'.$_SERVER['PHP_SELF'].'?reihungstest_id='.$reihungstest_id.'&stg_kz='.$stg_kz.'&type=savertpunkte&prestudent_id='.$row->prestudent_id.'&rtpunkte='.$rtergebnis.'" >&uuml;bertragen</a>':$row->rt_punkte1)."</td>
						</tr>";
					
					$mailto.= ($mailto!=''?',':'').$row->email;
				}
				echo "</tbody></table>";
				echo "<br><a href='mailto:?bcc=$mailto'>Mail an alle senden</a>";
			}
		}
		echo '
				</body>
				</html>';
		
	}
?>