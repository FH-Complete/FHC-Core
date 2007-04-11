<?php
/**
 * Erstellt ein XML File fuer die BIS-Meldung
 * Personal
 */
 include('../vilesci/config.inc.php');
 header("content-type text/xml");
 if(!$conn=pg_connect(CONN_STRING_FAS))
 	die("Connection Problem");
 $error='<table><tr><th>Vorname</th><th>Nachname</th><th>PersNr</th><th>Fehlermeldung</th></tr>';
 $funktionen='';
 $stgleitung='';
 $myausmass='<table><tr><th>Vorname</th><th>Nachname</th><th>Ausmass</th></tr>';
 $stsem1=9;
 $stsem2=8;
 $jahr='2005-09-01';
 $stsemwhere = "(funktion.studiensemester_fk=$stsem1 OR funktion.studiensemester_fk=$stsem2)";
 if(isset($_GET['printerror']) && $_GET['printerror']=='false')
 	$printerror=false;
 else
 	$printerror=true;
 echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
 ?>
<Erhalter>
   <ErhKz>005</ErhKz>
   <MeldeDatum>1511<?php echo date('Y');?></MeldeDatum>
   <PersonalMeldung>
<?php
	//$qry = "Select mitarbeiter_pk, vorname, familienname, persnr, gebdat, geschlecht, ausbildung, habilitation,funktion.besonderequalifikation, funktion.beschart1, funktion.beschart2, funktion.ausmass, funktion.verwendung,(select kennzahl from studiengang where studiengang_pk=studiengang_fk) as kennzahl, funktion.sws, funktion.hauptberuflich, funktion.hauptberuf, funktion.entwicklungsteam, funktion.funktion, (Select kennzahl from studiengang where studiengang_pk=funktion.studiengang_fk) as stgkz from person, mitarbeiter, funktion where person_pk=person_fk and mitarbeiter_pk=mitarbeiter_fk AND (studiensemester_fk=9 OR studiensemester_fk=8) AND ausgeschieden='N' and familienname!='Dummy' order by persnr ASC, beschart1 DESC";
	//$qry = "Select * from vw_bis_personal";
	$qry = "SET CLIENT_ENCODING TO 'UNICODE';SELECT distinct person_pk, mitarbeiter_pk, persnr, vorname, familienname, gebdat, geschlecht, ausbildung, habilitation FROM person, funktion, mitarbeiter where bismelden='J' AND person.person_pk=mitarbeiter.person_fk AND mitarbeiter.mitarbeiter_pk=funktion.mitarbeiter_fk AND $stsemwhere AND familienname!='Dummy' AND (beendigungsdatum>'$jahr' OR beendigungsdatum is null) ORDER BY persnr ASC";
	if(!$result=pg_query($conn,$qry))
		die("Fehler beim auslesen der Datenbank".$qry);
	$aktpers=0;
	while($row=pg_fetch_object($result))
	{
		//Neue Person
		//Verwendungsblock hinausschreiben
		$aktpers=$row->persnr;
		$funktionen='';
		$stgleitung='';
		echo "   <Person>\n";
		echo "      <PersonalNummer>".sprintf("%015d",$row->persnr)."</PersonalNummer>\n";
		//Geburtsdatum
		list($y,$m,$d) = explode("-",$row->gebdat);
		if(date('Y')-$y<10)
			$error.= '<tr><td>'.$row->vorname.'</td><td>'.$row->familienname.'</td><td>'.$row->persnr.'</td><td>Person muss aelter als 10 Jahre sein</td></tr>';
		echo "      <GeburtsDatum>".$d.$m.$y."</GeburtsDatum>\n";
		//Geschlecht
		if(!in_array($row->geschlecht,array('M','m','W','w')))
			$error.= '<tr><td>'.$row->vorname.'</td><td>'.$row->familienname.'</td><td>'.$row->persnr.'</td><td>Geschlecht ist ungueltig</td></tr>';
		echo "      <Geschlecht>".$row->geschlecht."</Geschlecht>\n";
		//Hoechste abgeschlossene Ausbildung
		if(!in_array($row->ausbildung,array(1,2,3,4,5,6,7,8,9,10,11)))
			$error.= '<tr><td>'.$row->vorname.'</td><td>'.$row->familienname.'</td><td>'.$row->persnr.'</td><td>HoechsteAbgeschlosseneAusbildung ist ungueltig</td></tr>';
		echo "      <HoechsteAbgeschlosseneAusbildung>".$row->ausbildung."</HoechsteAbgeschlosseneAusbildung>\n";
		//Habilitation
		if(!in_array($row->habilitation,array('J','j','N','n')))
			$error.= '<tr><td>'.$row->vorname.'</td><td>'.$row->familienname.'</td><td>'.$row->persnr.'</td><td>Habilitation ist ungueltig</td></tr>';
		echo "      <Habilitation>".$row->habilitation."</Habilitation>\n";

		//VERWENDUNG
		echo "      <Verwendung>\n";

		//Beschaeftigungsart1
		$qry1 = "Select beschart1 from funktion where mitarbeiter_fk='$row->mitarbeiter_pk' and beschart1 in(1,2,3,4,5,6) AND $stsemwhere";
		if(!$result1 = pg_query($conn,$qry1))
			$error.= "<br>qry failed: $qry1";
		else
			if(!$row1 = pg_fetch_object($result1))
				$error.= '<tr><td>'.$row->vorname.'</td><td>'.$row->familienname.'</td><td>'.$row->persnr.'</td><td>BeschaeftigungsArt1 ist ungueltig</td></tr>';
			else
				$beschart1 = $row1->beschart1;

		echo "         <BeschaeftigungsArt1>".$beschart1."</BeschaeftigungsArt1>\n";

		//Beschaeftingungsart2
		$qry1 = "Select beschart2 from funktion where mitarbeiter_fk='$row->mitarbeiter_pk' and beschart2 in(1,2) AND $stsemwhere";
		if(!$result1 = pg_query($conn,$qry1))
			$error.= "<tr><td>qry failed: $qry1</td></tr>";
		else
			if(!$row1 = pg_fetch_object($result1))
				$error.= '<tr><td>'.$row->vorname.'</td><td>'.$row->familienname.'</td><td>'.$row->persnr.'</td><td>BeschaeftigungsArt2 ist ungueltig</td></tr>';
			else
				$beschart2 = $row1->beschart2;

		echo "         <BeschaeftigungsArt2>".$beschart2."</BeschaeftigungsArt2>\n";

		//Beschaeftigungsausmass
		$qry1 = "Select ausmass from funktion where mitarbeiter_fk='$row->mitarbeiter_pk' and ausmass in(1,2,3,4,5) AND $stsemwhere";
		if(!$result1 = pg_query($conn,$qry1))
			$error.= "<br>qry failed: $qry1";
		else
			if(!$row1 = pg_fetch_object($result1))
				$error.= '<tr><td>'.$row->vorname.'</td><td>'.$row->familienname.'</td><td>'.$row->persnr.'</td><td>Ausmass ist ungueltig</td></tr>';
			else
				$ausmass = $row1->ausmass;

		echo "         <BeschaeftigungsAusmass>".$ausmass."</BeschaeftigungsAusmass>\n";
		$myausmass .= "<tr><td>$row->vorname</td><td>$row->familienname</td><td>$ausmass</td></tr>";
		//Verwendung
		$qry1 = "Select verwendung from funktion where mitarbeiter_fk='$row->mitarbeiter_pk' and verwendung in(1,2,3,4,5,6,7,8,9) AND $stsemwhere";
		if(!$result1 = pg_query($conn,$qry1))
			$error.= "<tr><td>qry failed: $qry1</td></tr>";
		else
			if(!$row1 = pg_fetch_object($result1))
				$error.= '<tr><td>'.$row->vorname.'</td><td>'.$row->familienname.'</td><td>'.$row->persnr.'</td><td>Verwendung ist ungueltig</td></tr>';
			else
				$verwendung = $row1->verwendung;

		echo "         <VerwendungsCode>".$verwendung."</VerwendungsCode>\n";

		//Hauptberuflich / Hauptberuf
		$qry1 = "Select hauptberuflich, hauptberuf from funktion where mitarbeiter_fk = '$row->mitarbeiter_pk' and hauptberuflich!='' AND $stsemwhere";
		if(!$result1=pg_query($conn,$qry1))
			$error.= "<tr><td>qry failed: $qry1</td></tr>";
		else
			if(!$row1 = pg_fetch_object($result1))
				$error.= '<tr><td>'.$row->vorname.'</td><td>'.$row->familienname.'</td><td>'.$row->persnr.'</td><td>hauptberuflich/hauptberuf ist ungueltig</td></tr>';
			else
			{
				$hauptberuf = $row1->hauptberuf;
				$hauptberuflich = $row1->hauptberuflich;
			}

		if(($hauptberuflich=='N' || $hauptberuflich=='n') && !in_array($hauptberuf, array(0,1,2,3,4,5,6,7,8,9,10,11,12)))
		{
			$qry1 = "Select hauptberuf from funktion where mitarbeiter_fk = '$row->mitarbeiter_pk' and hauptberuf in (0,1,2,3,4,5,6,7,8,9,10,11,12) AND $stsemwhere AND hauptberuf is not null limit 1";
			if(!$result1 = pg_query($conn,$qry1))
				$error.="<tr><td>qry failed: $qry1</td></tr>";
			else
				if(!$row1=pg_fetch_object($result1))
					$error.='<tr><td>'.$row->vorname.'</td><td>'.$row->familienname.'</td><td>'.$row->persnr.'</td><td>hauptberuf ist ungueltig</td></tr>';
				else
					$hauptberuf = $row1->hauptberuf;

		}


		//Studiengangsleiter Funktionen zusammenbauen
		$qry1 = "Select distinct kennzahl FROM studiengang where studiengang_pk in(Select studiengang_fk from funktion where funktion.funktion=5 AND $stsemwhere AND mitarbeiter_fk='$row->mitarbeiter_pk')";
		if(!$result1 = pg_query($conn,$qry1))
			$error.= "<tr><td>qry failed: $qry1</td></tr>";
		while($row1 = pg_fetch_object($result1))
		{
			$x = sprintf("%04d",$row1->kennzahl);
			$stgleitung.="         <StgLeitung>\n";
			$stgleitung.="            <StgKz>".$x."</StgKz>\n";
			$stgleitung.="         </StgLeitung>\n";
		}


		//FUNKTIONEN
		$qry1 = "SELECT studiengang_pk, kennzahl, entwicklungsteam, besonderequalifikation FROM funktion, studiengang WHERE mitarbeiter_fk='$row->mitarbeiter_pk' AND $stsemwhere AND studiengang_fk=studiengang_pk";

		if($result1 = pg_query($conn,$qry1))
		{
			$stg = array();
			while($row1 = pg_fetch_object($result1))
			{
				//Wenn noch kein Funktionseintrag fuer diesen Studiengang vorhanden ist
				if(!in_array($row1->studiengang_pk,$stg))
				{
					$stg[] = $row1->studiengang_pk;
					$funktion='';
					$valid=true;
					$x = sprintf("%04d",$row1->kennzahl);
					$funktion.= "         <Funktion>\n";
					$funktion.= "            <StgKz>".$x."</StgKz>\n";

					//$qry2 = "Select sum(sws) as sws from lehreinheit, mitarbeiterlehreinheit where lehreinheit_pk = lehreinheit_fk and (lehreinheit.studiensemester_fk=$stsem1 or lehreinheit.studiensemester_fk=$stsem2) and mitarbeiter_fk='$row->mitarbeiter_pk'";
					$qry2 = "Select sum(semesterwochenstunden) as sws from lehreinheit, mitarbeiter_lehreinheit where studiengang_fk='$row1->studiengang_pk' AND lehreinheit_pk = mitarbeiter_lehreinheit.lehreinheit_fk and (lehreinheit.studiensemester_fk=$stsem1 or lehreinheit.studiensemester_fk=$stsem2) and mitarbeiter_fk='$row->mitarbeiter_pk'";
					if(!$row2 = pg_fetch_object(pg_query($conn,$qry2)))
						$error.="<br>qry failed: $qry2";

					//Semesterwochenstunden
					if($row2->sws > 80 || $row2->sws < 0)
						$error.= '<tr><td>'.$row->vorname.'</td><td>'.$row->familienname.'</td><td>'.$row->persnr.'</td><td>SWS ist ungueltig:'.$row2->sws.'</td></tr>';
					if($row2->sws==0)
						$valid=false;
					$funktion.= "            <SWS>".sprintf("%.2f",$row2->sws)."</SWS>\n";
					//Hauptberuflich
					if(!in_array($hauptberuflich,array('j','J','n','N')))
						$error.= '<tr><td>'.$row->vorname.'</td><td>'.$row->familienname.'</td><td>'.$row->persnr.'</td><td>Hauptberuflich ist ungueltig</td></tr>';
					$funktion.= "            <Hauptberuflich>".$hauptberuflich."</Hauptberuflich>\n";
					//Hauptberuf
					if($hauptberuflich=='n' || $hauptberuflich=='N')
					{
						if(!in_array($hauptberuf, array(0,1,2,3,4,5,6,7,8,9,10,11,12)))
							$error.= '<tr><td>'.$row->vorname.'</td><td>'.$row->familienname.'</td><td>'.$row->persnr.' HauptberufCode ist ungueltig</td></tr>';
						$funktion.= "            <HauptberufCode>".$hauptberuf."</HauptberufCode>\n";
					}
					//Mitglied im Entwicklungsteam
					if(!in_array($row1->entwicklungsteam, array('J','j','n','N')))
						$funktion.= "            <Entwicklungsteam>N</Entwicklungsteam>\n";
					else
						$funktion.= "            <Entwicklungsteam>".$row1->entwicklungsteam."</Entwicklungsteam>\n";

					//Besondere Qualifikation
					if($row1->entwicklungsteam=='J' || $row1->entwicklungsteam=='j')
					{
						if(!in_array($row1->besonderequalifikation,array(0,1,2,3)))
							$error.= '<tr><td>'.$row->vorname.'</td><td>'.$row->familienname.'</td><td>'.$row->persnr.'</td><td>BesondereQualifikationCode ist ungueltig</td></tr>';
						$funktion.= "            <BesondereQualifikationCode>".$row1->besonderequalifikation."</BesondereQualifikationCode>\n";
					}
					$funktion.= "         </Funktion>\n";
					if($valid)
						$funktionen .= $funktion;
				}
			}
		}
		else
			$error.="<tr><td>qry failed: $qry1</td></tr>";

		if($aktpers!=0) //Ende einer Person erreicht
			{
				echo $stgleitung;
				echo $funktionen;
				echo "      </Verwendung>\n";
				echo "   </Person>\n";
			}
	}

	if($printerror)
	{
		echo $error.'</table>';
		echo $myausmass.'</table>';
	}
?>
   </PersonalMeldung>
</Erhalter>