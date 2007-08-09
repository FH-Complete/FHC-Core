<?php
/**
 * Ueberpruefung der Daten fuer Datenbankintegration FAS->VILESCI
 *
 * Prueft im FAS ob in der Tabelle lehreinheit die bezeichnung und die Kurzbezeichnung
 * innerhalb eines Studiensemesters, Studienganges und Semesters eindeutig ist.
 *
 * Prueft im FAS ob in der Tabelle lehrveranstaltung die bezeichnung und die Kurzbezeichnung
 * innerhalb eines Studiensemesters, Studienganges und Semesters eindeutig ist.
 *
 * Zusaetzlich wird in der Tabelle Lehrveranstaltung geprueft ob die ECTS Punkte in
 * zusammengehoerigen Lehrveranstaltungen gleich sind.
 *
 * Danach wird eine Mail an die zustaendige Assistentin geschickt.
 */

 include("../../config.inc.php");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head><title>check_fas_lvbz</title>
<style>
table
{
   border:1px solid black;

}
TR.liste
{
	background-color: #D3DCE3;
}
TR.liste0
{
	background-color: #FFFFFF;
}
TR.liste1
{
	background-color: #EEEEEE;
}
</style>
</head>
<body>
<?php

	if(!$conn=pg_pconnect(CONN_STRING_FAS))
		die("Fehler beim Connecten zur DB");
	if(!$conn_calva=pg_pconnect(CONN_STRING))
		die("Fehler beim Connecten zur DB");

	//Liste der Studiengaenge holen
	$qry="Select studiengang_kz, kurzbzlang from tbl_studiengang";
	if(!$result=pg_exec($conn_calva,$qry))
		die("Fehler beim Auslesen der Studiengaenge");

	$studiengaenge=array();
	while($row=pg_fetch_object($result))
		$studiengaenge[$row->studiengang_kz]=$row->kurzbzlang;

	//alle Kurzbezeichnungen trimmen
	//Select mit allen eintraegen wo die kurzbezeichnung bzw langbezeichnung nicht eindeutig ist
	$qry="UPDATE lehreinheit SET kurzbezeichnung=UPPER(trim(kurzbezeichnung)), bezeichnung=trim(bezeichnung)
			WHERE kurzbezeichnung<>UPPER(trim(kurzbezeichnung)) OR bezeichnung<>trim(bezeichnung);
		Select distinct on(studiengang.kennzahl,
	      studiensemester.jahr, studiensemester.art, ausbildungssemester.name,
	      a.bezeichnung, a.kurzbezeichnung, b.kurzbezeichnung)
	      a.lehreinheit_pk as pk1,b.lehreinheit_pk as pk2, studiengang.kennzahl as stg,
	      studiensemester.jahr as jahr, studiensemester.art as art, ausbildungssemester.name as sem,
	      a.bezeichnung as bez1, b.bezeichnung as bez2, a.kurzbezeichnung as kurzbz1, b.kurzbezeichnung as kurzbz2
	      from lehreinheit a, lehreinheit b , studiengang, studiensemester, ausbildungssemester
	      where a.lehreinheit_pk<>b.lehreinheit_pk AND a.studiengang_fk=b.studiengang_fk AND
	      a.studiensemester_fk=b.studiensemester_fk AND a.ausbildungssemester_fk=b.ausbildungssemester_fk
	      AND ((trim(a.bezeichnung)=trim(b.bezeichnung) AND a.kurzbezeichnung<>b.kurzbezeichnung) OR
	      (trim(a.bezeichnung)<>trim(b.bezeichnung) AND a.kurzbezeichnung=b.kurzbezeichnung)) AND
	      a.studiengang_fk=studiengang.studiengang_pk AND a.studiensemester_fk=studiensemester.studiensemester_pk
	      AND a.ausbildungssemester_fk=ausbildungssemester.ausbildungssemester_pk order by studiengang.kennzahl";

	$arr=array();
	if(!$result=pg_exec($conn,$qry))
		die("Fehler bei qry".pg_last_error($conn));

	while($row=pg_fetch_object($result))
	{
		if((!array_key_exists($row->pk1.$row->pk2,$arr) || $arr[$row->pk1.$row->pk2]['bez1']!=$row->bez1)
		&& (!array_key_exists($row->pk2.$row->pk1,$arr) || $arr[$row->pk2.$row->pk1]['bez1']!=$row->bez1))
		{
			$arr[$row->pk1.$row->pk2]['id']=$row->pk1." / ".$row->pk2;
			$arr[$row->pk1.$row->pk2]['stg']=$row->stg;
			$arr[$row->pk1.$row->pk2]['jahr']=$row->jahr;
			$arr[$row->pk1.$row->pk2]['art']=$row->art;
			$arr[$row->pk1.$row->pk2]['sem']=$row->sem;
			$arr[$row->pk1.$row->pk2]['bez1']=$row->bez1;
			$arr[$row->pk1.$row->pk2]['bez2']=$row->bez2;
			$arr[$row->pk1.$row->pk2]['kurzbz1']=$row->kurzbz1;
			$arr[$row->pk1.$row->pk2]['kurzbz2']=$row->kurzbz2;
		}
	}
	echo "anzahl:".count($arr);
	echo "<br>";

	$i=1;
	$laststg='0';
	foreach($arr as $elem)
	{
		if($laststg!=$elem['stg'])
		{
			if($i!=1)
				$mesg[$laststg].= "</table>";
			$i=1;
			$laststg=$elem['stg'];
			$mesg[$elem['stg']].="\n<html><head><style>
									table
									{
									   border:1px solid black;

									}
									TR.liste
									{
										background-color: #D3DCE3;
									}
									TR.liste0
									{
										background-color: #FFFFFF;
									}
									TR.liste1
									{
										background-color: #EEEEEE;
									}
									</style>
									</head><body>";
			$mesg[$elem['stg']].="Sehr geehrte Assistentin!<br><br>Aufgrund des Projekts Datenbankintegration ist es notwendig die Daten in einen konsitenten Zustand zu bringen.";
			$mesg[$elem['stg']].="Bitte beheben Sie die folgenden Probleme:<br><br>";
			$mesg[$elem['stg']].="Bei folgenden Eintr&auml;gen gibt es zu einer Lang-Bezeichnungen mehrere Kurzbezeichnungen bzw. umgekehrt!<br>(FAS->Semesterplanung->Lehreinheiten)<br>";
			$mesg[$elem['stg']].="<table class='liste'><tr><th>id</th><th>Studiengang</th><th>Studiensemester</th><th>Semester</th><th>Bezeichnung1</th><th>Bezeichnung2</th><th>Kuerzel1</th><th>Kuerzel2</th></tr>";
		}
		$mesg[$elem['stg']].= "\n";
		$mesg[$elem['stg']].= "<tr class='liste".($i%2)."'>";
		$mesg[$elem['stg']].= "<td>".$elem['id']."</td>";
		$mesg[$elem['stg']].= "<td>".$studiengaenge[$elem['stg']]."</td>";
		$mesg[$elem['stg']].= "<td>".($elem['art']==1?'WS':'SS').$elem['jahr']."</td>";
		$mesg[$elem['stg']].= "<td>".$elem['sem']."</td>";
		$mesg[$elem['stg']].= "<td>".$elem['bez1']."</td>";
		$mesg[$elem['stg']].= "<td>".$elem['bez2']."</td>";
		$mesg[$elem['stg']].= "<td>".$elem['kurzbz1']."</td>";
		$mesg[$elem['stg']].= "<td>".$elem['kurzbz2']."</td>";
		$mesg[$elem['stg']].= "</tr>";
		$i++;
	}
	$mesg[$laststg].= "</table>";


	// ***** Stammdaten

	//Alle bezeichnungen mit unterschiedlichen kurzbezeichnungen bzw umgekehrt aus Tab. lehrveranstaltung heraussuchen
	$qry="UPDATE lehrveranstaltung set kurzbezeichnung=UPPER(trim(kurzbezeichnung)), name=trim(name)
	         WHERE kurzbezeichnung<>UPPER(trim(kurzbezeichnung)) OR name<>trim(name);
	      SELECT distinct on(lva1.studiengang_fk, lva1.ausbildungssemester_fk, lva1.studiensemester_fk)
	         lva1.lehrveranstaltung_pk as id1, lva1.fachbereich_fk,  studiengang.kennzahl as stg,
	         ausbildungssemester.name as sem, lva1.name as name1, lva1.kurzbezeichnung as kuerzel1, lva1.art,
	         studiensemester.art as stsemart, studiensemester.jahr as stsemjahr,
	         lva2.lehrveranstaltung_pk as id2, lva2.fachbereich_fk, lva2.name as name2, lva2.kurzbezeichnung as kuerzel2, lva2.art
	      FROM lehrveranstaltung lva1, lehrveranstaltung lva2, studiengang, studiensemester, ausbildungssemester
	      WHERE lva1.studiensemester_fk=studiensemester.studiensemester_pk AND
	         lva1.ausbildungssemester_fk=ausbildungssemester_pk AND
	         studiengang.studiengang_pk=lva1.studiengang_fk AND
	         lva1.lehrveranstaltung_pk<>lva2.lehrveranstaltung_pk AND
	         ((lva1.name=lva2.name AND lva1.kurzbezeichnung<>lva2.kurzbezeichnung)
	           OR (lva1.name<>lva2.name AND lva1.kurzbezeichnung=lva2.kurzbezeichnung)) AND
	         lva1.studiengang_fk=lva2.studiengang_fk AND
	         lva1.studiensemester_fk=lva2.studiensemester_fk AND
	         lva1.ausbildungssemester_fk=lva2.ausbildungssemester_fk
	      ORDER BY lva1.studiengang_fk";

	if(!$result=pg_exec($conn,$qry))
		die("Fehler beim ueberpruefen der Stammdaten");

	$laststg='0';

	//Tabelle aufbauen
	while($row=pg_fetch_object($result))
	{
	   if($laststg!=$row->stg)
	   {
	   	    if($laststg!='0')
	   			$mesg[$laststg].="</table>";

	   	    $laststg=$row->stg;
			if(!array_key_exists($row->stg,$mesg))
			{
				//Header schreibgen falls noch keiner geschrieben wurde
				$mesg[$row->stg]="<html><head><style>
									table
									{
									   border:1px solid black;

									}
									TR.liste
									{
										background-color: #D3DCE3;
									}
									TR.liste0
									{
										background-color: #FFFFFF;
									}
									TR.liste1
									{
										background-color: #EEEEEE;
									}
									</style>
									</head><body>";
			}

			$mesg[$row->stg].="<br>Bei folgenden Eintr&auml;gen gibt es zu einer Lang-Bezeichnungen mehrere Kurzbezeichnungen bzw. umgekehrt!<br>(FAS->Stammdaten->Lehrveranstaltung)<br>";
			$mesg[$row->stg].="<table class='liste'><tr><th>id</th><th>Studiengang</th><th>Studiensemester</th><th>Semester</th><th>Bezeichnung1</th><th>Bezeichnung2</th><th>Kuerzel1</th><th>Kuerzel2</th></tr>";
		}

		$mesg[$row->stg].= "<tr class='liste".($i%2)."'>";
		$mesg[$row->stg].= "<td>".$row->id1.' / '.$row->id2."</td>";
		$mesg[$row->stg].= "<td>".$studiengaenge[$row->stg]."</td>";
		$mesg[$row->stg].= "<td>".($row->stsemart==1?'WS':'SS').$row->stsemjahr."</td>";
		$mesg[$row->stg].= "<td>".$row->sem."</td>";
		$mesg[$row->stg].= "<td>".$row->name1."</td>";
		$mesg[$row->stg].= "<td>".$row->name2."</td>";
		$mesg[$row->stg].= "<td>".$row->kuerzel1."</td>";
		$mesg[$row->stg].= "<td>".$row->kuerzel2."</td>";
		$mesg[$row->stg].= "</tr>";
		$i++;
	}



	// ***** ECTS Punkte pruefen

	$qry="Select a.lehrveranstaltung_pk as pk1, b.lehrveranstaltung_pk as pk2,
	      studiengang.kennzahl as stg, ausbildungssemester.name as sem,
	      a.name as bez, a.ectspunkte as ects1, b.ectspunkte as ects2
	      from lehrveranstaltung a, lehrveranstaltung b, studiengang, ausbildungssemester
	      where a.studiengang_fk=b.studiengang_fk and a.ausbildungssemester_fk=b.ausbildungssemester_fk
	      and a.studiensemester_fk=b.studiensemester_fk and a.ectspunkte<>b.ectspunkte
	      AND a.lehrveranstaltung_pk<>b.lehrveranstaltung_pk
	      AND lower(a.kurzbezeichnung)=lower(b.kurzbezeichnung)
	      AND studiengang.studiengang_pk=a.studiengang_fk
	      AND a.ausbildungssemester_fk=ausbildungssemester_pk order by studiengang.kennzahl";
	$arr=array();
	if(!$result=pg_exec($conn,$qry))
		die("Fehler bei qry".pg_last_error($conn));

	while($row=pg_fetch_object($result))
	{
		if(!array_key_exists($row->pk1.$row->pk2,$arr) && !array_key_exists($row->pk2.$row->pk1,$arr))
		{
			$arr[$row->pk1.$row->pk2]['id']=$row->pk1." / ".$row->pk2;
			$arr[$row->pk1.$row->pk2]['stg']=$row->stg;
			//$arr[$row->pk1.$row->pk2]['jahr']=$row->jahr;
			//$arr[$row->pk1.$row->pk2]['art']=$row->art;
			$arr[$row->pk1.$row->pk2]['sem']=$row->sem;
			$arr[$row->pk1.$row->pk2]['bez']=$row->bez;
			$arr[$row->pk1.$row->pk2]['ects1']=$row->ects1;
			$arr[$row->pk1.$row->pk2]['ects2']=$row->ects2;
		}
	}

	//echo "<table class='liste'><tr><th>id</th><th>Studiengang</th><th>Semester</th><th>Bezeichnung</th><th>ECTS1</th><th>ECTS2</th></tr>";
	$i=1;
	$laststg='0';

	foreach($arr as $elem)
	{
		if($laststg!=$elem['stg'])
		{
			$mesg[$laststg].="</table>";
			$i=1;
			$laststg=$elem['stg'];
			if(!array_key_exists($elem['stg'],$mesg))
			{
				$mesg[$elem['stg']]="<html><head><style>
									table
									{
									   border:1px solid black;

									}
									TR.liste
									{
										background-color: #D3DCE3;
									}
									TR.liste0
									{
										background-color: #FFFFFF;
									}
									TR.liste1
									{
										background-color: #EEEEEE;
									}
									</style>
									</head><body>";
			}

			$mesg[$elem['stg']].="<br>Es sind gleiche Eintr&auml;ge mit unterschiedlichen ECTS Punkten vorhanden<br><br>";
			$mesg[$elem['stg']].="<table class='liste'><tr><th>id</th><th>Studiengang</th><th>Semester</th><th>Bezeichnung</th><th>ECTS1</th><th>ECTS2</th></tr>";
		}
		$mesg[$elem['stg']].= "<tr class='liste".($i%2)."'>";
		$mesg[$elem['stg']].= "<td>".$elem['id']."</td>";
		$mesg[$elem['stg']].= "<td>".$studiengaenge[$elem['stg']]."</td>";
		//echo "<td>".($elem['art']==1?'WS':'SS').$elem['jahr']."</td>";
		$mesg[$elem['stg']].= "<td>".$elem['sem']."</td>";
		$mesg[$elem['stg']].= "<td>".$elem['bez']."</td>";
		$mesg[$elem['stg']].= "<td>".$elem['ects1']."</td>";
		$mesg[$elem['stg']].= "<td>".$elem['ects2']."</td>";
		$mesg[$elem['stg']].= "</tr>";
		$i++;
	}


	// ********** Mails verschicken
	$mesg[$laststg].= "</table>";
	echo $msg;
	foreach (array_keys($mesg) as $elem)
	{
		if($elem!='0')
		{
			$qry="Select email from tbl_studiengang where studiengang_kz='$elem'";
			$result=pg_exec($conn_calva,$qry);

			$row=pg_fetch_object($result);

			echo "<br>".$studiengaenge[$elem]." goes to $row->email<br><br>";
			echo $mesg[$elem];
			if($row->email!='')
			{
				/* To send HTML mail, you can set the Content-type header. */
				$headers  = "MIME-Version: 1.0\r\n";
				$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";

				/* additional headers */
				$headers .= "From: Systementwicklung - automatisch generiert <systementwicklung@technikum-wien.at>\r\n";
				//$headers .= "Bcc: oesi@technikum-wien.at\r\n";
				//mail('oesi@technikum-wien.at','Datenbereinigung ('.$studiengaenge[$elem].')',$mesg[$elem],$headers);
				mail($row->email,'Datenbereinigung ('.$studiengaenge[$elem].')',$mesg[$elem],$headers);

			}
		}
	}
?>