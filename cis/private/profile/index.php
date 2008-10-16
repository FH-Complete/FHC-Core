<?php
/* Copyright (C) 2008 Technikum-Wien
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
 *          Gerald Simane-Sequens <	gerald.simane-sequens@technikum-wien.at>.
 */
	require_once('../../config.inc.php');
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/globals.inc.php');
	require_once('../../../include/studiengang.class.php');
	require_once('../../../include/fckeditor/fckeditor.php');
	require_once('../../../include/person.class.php');
	require_once('../../../include/safehtml/safehtml.class.php');
		
	$uid=get_uid();
	$ansicht=false; //Wenn ein anderer User sich das Profil ansieht (Bei Personensuche)
	if(isset($_GET['uid']))
	{
		$uid=stripslashes($_GET['uid']);
		$ansicht=true;
	}
		
	$stg = '';
	if (!$conn = @pg_pconnect(CONN_STRING))
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");
	   	
	$stg_obj = new studiengang($conn);
	$stg_obj->getAll('typ, kurzbz', false);
	
	$stg_arr = array();
	foreach ($stg_obj->result as $row)
		$stg_arr[$row->studiengang_kz]=$row->kurzbzlang;
	
	if(!($erg=pg_query($conn, "SELECT * FROM campus.vw_benutzer WHERE uid='$uid'")))
		die(pg_last_error($conn));
	$num_rows=pg_num_rows($erg);
	if ($num_rows==1)
	{
		$person_id=pg_result($erg,0,"person_id");
		$vorname=pg_result($erg,0,"vorname");
		$vornamen=pg_result($erg,0,"vornamen");
		$nachname=pg_result($erg,0,"nachname");
		$gebdatum=pg_result($erg,0,"gebdatum");
		$gebort=pg_result($erg,0,"gebort");
		$titelpre=pg_result($erg,0,"titelpre");
		$titelpost=pg_result($erg,0,"titelpost");
		$email=pg_result($erg,0,"uid").'@'.DOMAIN;
		$email_alias=pg_result($erg,0,"alias");
		$hp=pg_result($erg,0,"homepage");
		$aktiv=pg_result($erg,0,"aktiv");
		$foto=pg_result($erg,0,"foto");
	}
	if(!($erg_stud=pg_query($conn, "SELECT studiengang_kz, semester, verband, gruppe, matrikelnr, typ::varchar(1) || kurzbz AS stgkz, tbl_studiengang.bezeichnung AS stgbz FROM public.tbl_student JOIN public.tbl_studiengang USING(studiengang_kz) WHERE student_uid='$uid'")))
		die(pg_last_error($conn));
	$stud_num_rows=pg_num_rows($erg_stud);

	if ($stud_num_rows==1)
	{
		$stg=pg_result($erg_stud,0,"studiengang_kz");
		$stgbez=pg_result($erg_stud,0,"stgbz");
		$stgkz=pg_result($erg_stud,0,"stgkz");
		$semester=pg_result($erg_stud,0,"semester");
		$verband=pg_result($erg_stud,0,"verband");
		$gruppe=pg_result($erg_stud,0,"gruppe");
		$matrikelnr=pg_result($erg_stud,0,"matrikelnr");
	}
	if(!($erg_lekt=pg_query($conn, "SELECT * FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid='$uid'")))
		die(pg_last_error($conn));
	$lekt_num_rows=pg_num_rows($erg_lekt);
	if ($lekt_num_rows==1)
	{
		$row=pg_fetch_object($erg_lekt,0);
		$kurzbz=$row->kurzbz;
		$tel=$row->telefonklappe;
	}

	// Mail-Groups
	if(!($erg_mg=pg_query($conn, "SELECT gruppe_kurzbz, beschreibung FROM campus.vw_persongruppe WHERE mailgrp AND uid='$uid' ORDER BY gruppe_kurzbz")))
		die(pg_last_error($conn));
	$nr_mg=pg_num_rows($erg_mg);
	
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Profil</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
<script language="Javascript">
<!--
function RefreshImage()
{
	window.location.reload();
}
-->
</script>
</head>

<body>
<table class="tabcontent" id="inhalt">
  <tr>
    <td class="tdwidth10">&nbsp;</td>
    <td>
    <table class="tabcontent">
      <tr>
		<td class='ContentHeader'><font class='ContentHeader'>&nbsp;Userprofil</font></td>
	  </tr>
	</table>
	<!--
	Results: <?php echo $num_rows; ?><br>
	Username: <?php echo $uid; ?><br><br>
	-->
<?php
	
	if(isset($_POST['savekurzbeschreibung']) && !$ansicht)
	{
		$person = new person($conn);
		$person->load($person_id);
		
		//Remove Script Tags and other stuff
		$parser = new SafeHTML();
		$result = $parser->parse($_POST['kurzbeschreibung']);
		
		$person->kurzbeschreibung = $result;
		$person->updateamum = date('Y-m-d H:i:s');
		$person->udpatevon = $uid;
		if($person->save())
			echo '<b>Kurzbeschreibung wurde erfolgreich gespeichert</b>';
		else 
			echo '<span class="error">Fehler beim Speichern der Kurzbeschreibung</span>';
	}
	
	if($aktiv=='f')
	{
		$message = "Ihr Benutzerdatensatz wurde von einem unserer Mitarbeiter deaktiviert. Was bedeutet das nun für Sie?<br><br>";
		$message .= "Vorerst werden Sie aus allen Mail-Verteilern gelöscht.<br>";
		$message .= "Wenn der Datensatz in den nächsten Tagen nicht mehr aktiviert wird, führt das System automatisch folgende Aktionen durch:<br>";
		$message .= "- Ihr Account wird gelöscht.<br>";
		$message .= "- Ihre Mailbox mit sämtlichen Mails wird gelöscht.<br>";
		$message .= "- Ihr Home-Verzeichnis mit allen enthaltenen Dateien wird gelöscht.<br><br>";
		$message .= "Sollte es sich hierbei um einen Irrtum handeln, wenden sie sich bitte an ihre Studiengangsassistenz.<br>";

		echo "<span style='color: red;'>Achtung!<br>$message</span>";
	}
	
	if ($num_rows==1)
	{
		echo '
		<table class="tabcontent">
  		<tr>
    		<td colspan="2" class="MarkLine" width="60%">
    		<table width="100%"><tr><td>
      		<P><br>
      			Username: '.$uid.'<br>
      			Titel: '.$titelpre.' <br>
        		Vornamen: '.$vorname.'  '.$vornamen.'<br>
        		Nachname:'.$nachname.'<br>
        		Postnomen: '.$titelpost.'<br>';
        		
		if(!$ansicht)
		{
        	echo "	Geburtsdatum: $gebdatum<br>
        			Geburtsort: $gebort<br>";
        		
        }
        
        echo '
      		</P>
      		</td>
      		<td align="right">';
        //Foto anzeigen oder Upload Button
        if($foto!='')
        	echo '<img id="personimage" src="../../public/bild.php?src=person&person_id='.$person_id.'" height="100px">';
        else
        {
        	if(!$ansicht)
        		echo "<a href='#BildUpload' onclick='window.open(\"../bildupload.php?person_id=$person_id\",\"BildUpload\", \"height=100,width=500,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes\"); return false;'>Bild hochladen</a>";
        }
      	echo '
      		</td></tr></table>
      		<P>
      			<b>eMail</b><br>
        		Intern: <a class="Item" href="mailto:'.$uid.'@'.DOMAIN.'">'.$uid.'@'.DOMAIN.'</a><br>';

		if($email_alias!='' && !in_array($stg,$noalias))
		{
			echo "Alias: <a class='Item' href='mailto:$email_alias@".DOMAIN."'>$email_alias@".DOMAIN."</a>";
		}
				
		if($email!='')
			echo "<br>Extern: $email";
        
        echo '</P>';

		if($hp!='')
			echo "<P><b>Homepage</b><br><a href='$hp' target='_blank'>$hp</a></p>";
		echo '<p>';
      		
  		/*
  		$qry = "SELECT kompetenzen FROM public.tbl_person WHERE person_id='$person_id'";
  		if($result = pg_query($conn, $qry))
  		{
  			if($row = pg_fetch_object($result))
  			{
  				if($row->kompetenzen!='')
  				{
  					echo "<b>Kompetenzen</b><br>".str_replace(';','<br>', $row->kompetenzen);
  				}
  			}
  		}
  		*/
		echo '
      		</p>
        	<br>
    		</td>
    		<td rowspan="2">';
      			
		echo '<P>';
		if ($stud_num_rows==1)
		{
			echo "
				<b>Student</b><br><br>
			Studiengang: $stgbez<br>
			Semester: $semester<br>
			Verband: $verband<br>
			Gruppe: $gruppe<br>
    		Matrikelnummer: $matrikelnr<br />";
    		
    		if(!$ansicht)
    		{
    			echo "
		    		<br />
		    		<A class='Item' href='../lehre/notenliste.php'>Leistungsbeurteilung</a><br />";
    		}
		}
		
		if ($lekt_num_rows==1)
		{
			echo "
				<P>
				<b>Lektor</b><br><br>
			Kurzzeichen: $kurzbz<BR>";
			
			if($tel!='')
				echo "Telefon TW: +43 1 333 40 77- $tel<BR><BR>";

			if(!$ansicht)
			{
				echo '
					<A class="Item" href="zeitwunsch.php?uid='.$uid.'">Zeitw&uuml;nsche</A><BR>
					<A class="Item" href="lva_liste.php?uid='.$uid.'">Lehrveranstaltungen</A>';
			}
		}
		
		if(!$ansicht)
		{
			//Funktionen
			$qry = "SELECT 
						*, tbl_benutzerfunktion.studiengang_kz as studiengang_kz, 
						tbl_fachbereich.bezeichnung as bezeichnung, tbl_benutzerfunktion.semester
					FROM 
						public.tbl_benutzerfunktion JOIN public.tbl_funktion USING(funktion_kurzbz) 
						LEFT JOIN public.tbl_fachbereich USING(fachbereich_kurzbz) 
						LEFT JOIN public.tbl_studiengang ON(tbl_benutzerfunktion.studiengang_kz=tbl_studiengang.studiengang_kz) 
					WHERE 
						uid='$uid' AND 
						(tbl_fachbereich.aktiv=true OR fachbereich_kurzbz is null) AND 
						(tbl_studiengang.aktiv=true OR tbl_benutzerfunktion.studiengang_kz is null)";
			if($result_funktion = pg_query($conn, $qry))
			{
				if(pg_num_rows($result_funktion)>0)
				{
					echo '<br><br><b>Funktionen</b><table><tr class="liste"><th>Funktion</th><th>Studiengang</th><th>Semester</th><th>Institut</th></tr>';

					while($row_funktion = pg_fetch_object($result_funktion))
					{
						echo "<tr class='liste1'><td>$row_funktion->beschreibung</td><td>".($row_funktion->studiengang_kz!=0?$stg_arr[$row_funktion->studiengang_kz]:'')."</td><td>$row_funktion->semester</td><td>$row_funktion->bezeichnung</td></tr>";
					}
					echo '</table>';
				}
			}
			
			//Betriebsmittel
			$qry = "SELECT 
						tbl_betriebsmittel.betriebsmitteltyp as betriebsmitteltyp, 
						tbl_betriebsmittel.beschreibung as beschreibung, tbl_betriebsmittel.nummer as nummer, 
						tbl_betriebsmittelperson.ausgegebenam as ausgegebenam
					FROM 
						public.tbl_betriebsmittelperson JOIN public.tbl_betriebsmittel USING(betriebsmittel_id) 
					WHERE 
						person_id=(SELECT person_id FROM public.tbl_benutzer WHERE uid='$uid' LIMIT 1) AND 
						retouram is null";
			if($result_betriebsmittel = pg_query($conn, $qry))
			{
				if(pg_num_rows($result_betriebsmittel)>0)
				{
					echo '<br><br><b>Entlehnte Betriebsmittel</b><table><tr class="liste"><th>Betriebsmittel</th><th>Nummer</th><th>Ausgegeben am</th></tr>';

					while($row_bm = pg_fetch_object($result_betriebsmittel))
					{
						echo "<tr class='liste1'><td>$row_bm->betriebsmitteltyp</td><td>$row_bm->nummer</td><td>$row_bm->ausgegebenam</td></tr>";
					}
					echo '</table>';
				}
			}
		}
		if(!$ansicht)
		{
			echo "";
			echo "";
		}
		echo "</P>";
		
		echo '	
    		</td>
  		</tr>
  		<TR>
    		<TD colspan="2">
      		<P><B>Mail-Verteiler</B><BR><BR>
      		';
		//Mailverteiler
		if(!$ansicht)
			echo "<SMALL>Sie sind Mitglied in folgenden Verteilern:</SMALL>";
		else
			echo "<SMALL>Der User $uid ist Mitglied in folgenden Verteilern:</SMALL>";
        
		echo '
        	</P>
    		</TD>
    		<TD> </TD>
  		</TR>';
  		
  		for($i=0;$i<$nr_mg;$i++)
		{
			$row=pg_fetch_object($erg_mg,$i);
			echo '<TR><TD><A class="Item" href="mailto:'.strtolower($row->gruppe_kurzbz).'@'.DOMAIN.'">'.strtolower($row->gruppe_kurzbz).'&nbsp;</TD>';
    		echo "<TD>&nbsp;$row->beschreibung</TD><TD></TD></TR>";
		}
		
		if (isset($matrikelnr))
		{
			echo '<TR><TD><A class="Item" href="mailto:'.strtolower($stgkz).'_std@'.DOMAIN.'">'.strtolower($stgkz).'_std&nbsp;</TD>';
    		echo "<TD>&nbsp;Alle Studenten von $stgbez</TD><TD></TD></TR>";
			echo '<TR><TD><A class="Item" href="mailto:'.strtolower($stgkz).$semester.'@'.DOMAIN.'">'.strtolower($stgkz).$semester.'&nbsp;</TD>';
    		echo "<TD>&nbsp;Alle Studenten von $stgkz $semester</TD><TD></TD></TR>";
			echo '<TR><TD><A class="Item" href="mailto:'.strtolower($stgkz).$semester.strtolower($verband).'@'.DOMAIN.'">'.strtolower($stgkz).$semester.strtolower($verband).'&nbsp;</TD>';
    		echo "<TD>&nbsp;Alle Studenten von $stgkz $semester$verband</TD><TD></TD></TR>";
			echo '<TR><TD><A class="Item" href="mailto:'.strtolower($stgkz).$semester.strtolower($verband).$gruppe.'@'.DOMAIN.'">'.strtolower($stgkz).$semester.strtolower($verband).$gruppe.'&nbsp;</TD>';
    		echo "<TD>&nbsp;Alle Studenten von $stgkz $semester$verband$gruppe</TD><TD></TD></TR>";
		}

		$mail = MAIL_ADMIN;
		if($stg=='')
		{
			$stg = 0;
		}
		
		//Wenn eine Assistentin fuer diesen Studiengang eingetragen ist,
		//dann werden die aenderungswuesche an diese Adresse gesendet
		$qry = "SELECT email FROM public.tbl_studiengang where studiengang_kz='$stg'";
		if($row=pg_fetch_object(pg_query($conn,$qry)))
		{
			if($row->email!='')
				$mail = $row->email;
			else
				$mail = MAIL_ADMIN;
		}
		if($stg=='0')
			$mail = MAIL_GST;

		echo '
			</table>
			<BR>';
			
		if(!$ansicht)
		{
			//Wenn eine OEH Kandidatur vorhanden ist, WYSIWYG Editor anzeigen
			$qry = "SELECT * FROM public.tbl_benutzerfunktion WHERE funktion_kurzbz='oeh-kandidatur' AND uid='$uid'";
			if($result = pg_query($conn, $qry))
			{
				if(pg_num_rows($result)>0)
				{
					$person = new person($conn);
					$person->load($person_id);
					echo '<hr>';
					echo '<b>Kurzbeschreibung für die &Ouml;H-Kandidatur:</b><br>';
					echo '<form action="'.$_SERVER['PHP_SELF'].'" method="post">';
					
					// Automatically calculates the editor base path based on the _samples directory.
					// This is usefull only for these samples. A real application should use something like this:
					// $oFCKeditor->BasePath = '/fckeditor/' ;	// '/fckeditor/' is the default value.
					$sBasePath = $_SERVER['PHP_SELF'] ;
					$sBasePath = '../../../include/fckeditor/';
					
					$oFCKeditor = new FCKeditor('kurzbeschreibung') ;
					
					$oFCKeditor->BasePath	= $sBasePath ;
					$oFCKeditor->Value		= $person->kurzbeschreibung;
					$oFCKeditor->Create() ;
					
					echo '
					    <br>
					    <input type="submit" value="Speichern" name="savekurzbeschreibung">
					  </form>';
				}
			}
			
			echo "
			<HR>
			Sollten ihre Daten nicht stimmen, wenden sie sich bitte an die <a class='Item' href=\"mailto:$mail?subject=Datenkorrektur&body=Die%20Profildaten%20fuer%20User%20'$uid'%20sind%20nicht%20korrekt.%0D
			Hier die richtigen Daten:%0DNachname:%20$nachname%0DVorname:%20$vorname%0DGeburtsdatum:%20$gebdatum
			%0DGeburtsort:%20$gebort%0DTitelPre:%20$titelpre%0DTitelPost:%20$titelpost
			%0D%0D***%0DPlatz fuer weitere (nicht angefuehrte Daten)%0D***\">zuständige Assistenz</a>";
		
		}
	}
	else
	{
		echo '		
		<br><br>
		Es wurden keine oder mehrere Profile f&uuml;r ihren Useraccount gefunden.
		<br>
		Bitte wenden sie sich an die <a class="Item" href="mailto:'.MAIL_ADMIN.'?subject=Profilfehler&body=Es wurden zuviele oder zuwenige Profile fuer User '.$uid.' gefunden. %0DBitte kontrollieren sie die Datenbank!%0D%0DMeine Daten sind:%0DNachname:%0DVornamen:%0D...">Administration</a>
		';
	}
	?>
</body>
</html>
