<?php
	require_once('../../config.inc.php');
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/globals.inc.php');
	require_once('../../../include/studiengang.class.php');

	//if (!isset($REMOTE_USER))
	//	$REMOTE_USER='pam';
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
		$stg_arr[$row->studiengang_kz]=$row->kuerzel;
	
	if(!($erg=pg_exec($conn, "SET search_path TO campus;SELECT * FROM vw_benutzer WHERE uid='$uid'")))
		die(pg_last_error($conn));
	$num_rows=pg_num_rows($erg);
	if ($num_rows==1)
	{
		$vorname=pg_result($erg,0,"vorname");
		$vornamen=pg_result($erg,0,"vornamen");
		$nachname=pg_result($erg,0,"nachname");
		$gebdatum=pg_result($erg,0,"gebdatum");
		$gebort=pg_result($erg,0,"gebort");
		$titelpre=pg_result($erg,0,"titelpre");
		$titelpost=pg_result($erg,0,"titelpost");
		$email=pg_result($erg,0,"uid").'@technikum-wien.at';
		$email_alias=pg_result($erg,0,"alias");
		$hp=pg_result($erg,0,"homepage");
	}
	if(!($erg_stud=pg_exec($conn, "SELECT studiengang_kz, semester, verband, gruppe, matrikelnr, typ::varchar(1) || kurzbz AS stgkz, tbl_studiengang.bezeichnung AS stgbz FROM public.tbl_student JOIN public.tbl_studiengang USING(studiengang_kz) WHERE student_uid='$uid'")))
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
	if(!($erg_lekt=pg_exec($conn, "SELECT * FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid='$uid'")))
		die(pg_last_error($conn));
	$lekt_num_rows=pg_num_rows($erg_lekt);
	if ($lekt_num_rows==1)
	{
		$row=pg_fetch_object($erg_lekt,0);
		$kurzbz=$row->kurzbz;
		$tel=$row->telefonklappe;
	}

	// Mail-Groups
	if(!($erg_mg=pg_exec($conn, "SELECT gruppe_kurzbz, beschreibung FROM vw_persongruppe WHERE mailgrp AND uid='$uid' ORDER BY gruppe_kurzbz")))
		die(pg_last_error($conn));
	$nr_mg=pg_numrows($erg_mg);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Profil</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
</head>

<body>
<table class="tabcontent" id="inhalt">
  <tr>
    <td class="tdwidth10">&nbsp;</td>
    <td>
    <table class="tabcontent">
      <tr>
		<td class='ContentHeader'><font class='ContentHeader'>&nbsp;Userprofil</font></td>
		<!--<td align="right"><A href="../lvplan/help/index.html" class="hilfe" target="_blank">HELP&nbsp;</A></td>-->
	  </tr>
	</table>

	Results: <?php echo $num_rows; ?><br>
	Username: <?php echo $uid; ?><br><br>
	<HR>
	<?php
	if ($num_rows==1)
	{
		?>
		<table class="tabcontent">
  		<tr>
    		<td colspan="2" class="MarkLine">
      		<P><br>
      			Titel: <?php echo $titelpre; ?> <br>
        		Vornamen: <?php echo $vorname.'  '.$vornamen; ?> <br>
        		Nachname: <?php echo $nachname; ?> <br>
        		Postnomen: <?php echo $titelpost; ?> <br>
        		<?php
        		if(!$ansicht)
        		{
        		?>
        			Geburtsdatum: <?php echo $gebdatum; ?> <br>
        			Geburtsort: <?php echo $gebort; ?> <br>
        		<?php
        		}
        		?>
        		<!--<b>Passwort</b><br>
        		<a href="password.php">Passwort &auml;ndern</a></FONT><br>-->
      		</P>
      		<P>
      			<b>eMail</b><br>
        		Technikum: <a class='Item' href='mailto:<?php echo $uid; ?>@technikum-wien.at'> <?php echo $uid; ?>@technikum-wien.at</a><br>

        		<?php
        		if($email_alias!='' && !in_array($stg,$noalias))
        		{
        		?>
        			Alias: <a class='Item' href='mailto:<?php echo $email_alias; ?>@technikum-wien.at'><?php echo $email_alias; ?>@technikum-wien.at</a>
        		<?php
				}
				?>
        		<!--<small>(fr&uuml;hestens ab Sommer in Verwendung)</small><BR>-->
        		<?php
        	    if($email!='')
        	       	echo "<br>Extern: $email";
        	    ?>

        	</P>
        	<?php
        	if($hp!='')
        		echo "<P><b>Homepage</b><br><a href='$hp' target='_blank'>$hp</a></p>";
        	?>

        	<br>
    		</td>
    		<td rowspan="2">
      			<?php
      			echo '<P>';
				if ($stud_num_rows==1)
				{
					?>
      				<b>Student</b><br><br>
        			Studiengang: <?php echo $stgbez; ?><br>
        			Semester: <?php echo $semester; ?><br>
        			Verband: <?php echo $verband; ?><br>
        			Gruppe: <?php echo $gruppe; ?><br>
	        		Matrikelnummer: <?php echo $matrikelnr; ?><br />
	        		<?php
	        		if(!$ansicht)
	        		{
	        			?>
	        		<br />
	        		<A class='Item' href='../lehre/notenliste.php'>Leistungsbeurteilung</a><br />

      				<?php
	        		}
				}
				if ($lekt_num_rows==1)
				{
					?>
      				<P>
      				<b>Lektor</b><br><br>
        			Kurzzeichen: <?php echo $kurzbz; ?><BR>
        			<?php
        			if($tel!='')
        				echo "Telefon TW: +43 1 333 40 77- $tel<BR><BR>";

        			if(!$ansicht)
        			{?>
        			<A class="Item" href="zeitwunsch.php?uid=<?php echo $uid; ?>">Zeitw&uuml;nsche</A><BR>
        			<A class="Item" href="lva_liste.php?uid=<?php echo $uid; ?>">Lehrveranstaltungen</A>
        			<?php
        			}
				}
				if(!$ansicht)
				{
					//Funktionen
					$qry = "SELECT * FROM public.tbl_benutzerfunktion JOIN public.tbl_funktion USING(funktion_kurzbz) WHERE uid='$uid'";
					if($result_funktion = pg_query($conn, $qry))
					{
						if(pg_num_rows($result_funktion)>0)
						{
							echo '<br><br><b>Funktionen</b><table><tr class="liste"><th>Funktion</th><th>Studiengang</th><th>Institut</th></tr>';

							while($row_funktion = pg_fetch_object($result_funktion))
							{
								echo "<tr class='liste1'><td>$row_funktion->beschreibung</td><td>".$stg_arr[$row_funktion->studiengang_kz]."</td><td>$row_funktion->fachbereich_kurzbz</td></tr>";
							}
							echo '</table>';
						}
					}
					
					//Betriebsmittel
					$qry = "SELECT tbl_betriebsmittel.betriebsmitteltyp as betriebsmitteltyp, tbl_betriebsmittel.beschreibung as beschreibung, tbl_betriebsmittel.nummer as nummer, tbl_betriebsmittelperson.ausgegebenam as ausgegebenam FROM public.tbl_betriebsmittelperson JOIN public.tbl_betriebsmittel USING(betriebsmittel_id) WHERE person_id=(SELECT person_id FROM public.tbl_benutzer WHERE uid='$uid' LIMIT 1)";
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
				?>
    		</td>
  		</tr>
  		<TR>
    		<TD colspan="2">
      		<P><B>Mail-Verteiler</B><BR><BR>
      		<?php
      		if(!$ansicht)
        		echo "<SMALL>Sie sind Mitglied in folgenden Verteilern:</SMALL>";
        	else
        		echo "<SMALL>Der User $uid ist Mitglied in folgenden Verteilern:</SMALL>";
        	?>
        	</P>
    		</TD>
    		<TD> </TD>
  		</TR>
  		<?php
  		for($i=0;$i<$nr_mg;$i++)
		{
			$row=pg_fetch_object($erg_mg,$i);
			echo '<TR><TD><A class="Item" href="mailto:'.strtolower($row->gruppe_kurzbz).'@technikum-wien.at">'.strtolower($row->gruppe_kurzbz).'&nbsp;</TD>';
    		echo "<TD>&nbsp;$row->beschreibung</TD><TD></TD></TR>";
		}
		if (isset($matrikelnr))
		{
			echo '<TR><TD><A class="Item" href="mailto:'.strtolower($stgkz).'_std@technikum-wien.at">'.strtolower($stgkz).'_std&nbsp;</TD>';
    		echo "<TD>&nbsp;Alle Studenten von $stgbez</TD><TD></TD></TR>";
			echo '<TR><TD><A class="Item" href="mailto:'.strtolower($stgkz).$semester.'@technikum-wien.at">'.strtolower($stgkz).$semester.'&nbsp;</TD>';
    		echo "<TD>&nbsp;Alle Studenten von $stgkz $semester</TD><TD></TD></TR>";
			echo '<TR><TD><A class="Item" href="mailto:'.strtolower($stgkz).$semester.strtolower($verband).'@technikum-wien.at">'.strtolower($stgkz).$semester.strtolower($verband).'&nbsp;</TD>';
    		echo "<TD>&nbsp;Alle Studenten von $stgkz $semester$verband</TD><TD></TD></TR>";
			echo '<TR><TD><A class="Item" href="mailto:'.strtolower($stgkz).$semester.strtolower($verband).$gruppe.'@technikum-wien.at">'.strtolower($stgkz).$semester.strtolower($verband).$gruppe.'&nbsp;</TD>';
    		echo "<TD>&nbsp;Alle Studenten von $stgkz $semester$verband$gruppe</TD><TD></TD></TR>";
		}


		$mail = 'vilesci@technikum-wien.at';
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
					$mail = 'vilesci@technikum-wien.at';
			}
			if($stg=='0')
				$mail = MAIL_GST;


			?>
			</table>
			<BR><HR>
			Sollten ihre Daten nicht stimmen, wenden sie sich bitte an die <a class='Item' href="mailto:<?php echo $mail ?>?subject=Datenkorrektur&body=Die%20Profildaten%20fuer%20User%20'<?php echo $uid; ?>'%20sind%20nicht%20korrekt.%0D
				Hier die richtigen Daten:%0DNachname:%20<?php echo $nachname;?>%0DVorname:%20<?php echo $vorname;?>%0DGeburtsdatum:%20<?php echo $gebdatum;?>
				%0DGeburtsort:%20<?php echo $gebort;?>%0DTitelPre:%20<?php echo $titelpre;?>%0DTitelPost:%20<?php echo $titelpost;?>
				%0D%0D***%0DPlatz fuer weitere (nicht angefuehrte Daten)%0D***">zuständige Assistentin</a>
			<?php
	}
	else
	{
		?>
		<br><br>
		Es wurden keine oder mehrere Profile f&uuml;r ihren Useraccount gefunden.
		<br>
		Bitte wenden sie sich an die <a class='Item' href="mailto:vilesci@technikum-wien.at?subject=Profilfehler&body=Es wurden zuviele oder zuwenige Profile fuer User <?php echo $uid; ?> gefunden. %0DBitte kontrollieren sie die Datenbank!%0D%0DMeine Daten sind:%0DNachname:%0DVornamen:%0D...">Administration</a>
		<?php
	}
	?>
</body>
</html>
