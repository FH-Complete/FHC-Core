<?php
/* Copyright (C) 2007 Technikum-Wien
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

//*
//* Synchronisiert Adressendatensaetze von FAS DB in PORTAL DB
//*
//*

include('../../../vilesci/config.inc.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");

$adress='ruhan@technikum-wien.at';
//$adress='fas_sync@technikum-wien.at';

$error_log='';
$text = '';
$anzahl_quelle=0;
$anzahl_eingefuegt=0;
$anzahl_fehler=0;
$anzahl_quelle2=0;
$anzahl_eingefuegt2=0;
$anzahl_fehler2=0;

function validate($row)
{
}

/*************************
 * FAS-PORTAL - Synchronisation
 */
?>

<html>
<head>
<title>Synchro - FAS -> Portal - Kleinvieh</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
$aufmerksam_kurzbz=array('k.A.', 'Internet', 'Zeitungen','Werbung','Mundpropaganda','FH-F�hrer','BEST Messe','Partnerfirma','Schule','Bildungstelefon',
			'TGM','Abgeworben','Technikum Wien','Aussendungen','offene T�r');
$beschreibung=array('keine Angabe',null,null,'Werbung, Veranstaltungen', null,null,null,null,null,'Bildungstelefon AK',null,'Abgeworben von Konkurrenz',null,'Aussendungen eines Studiengangs','Tag der offenen T�r');			

for ($i=0; $i<=14; $i++)
{
	$qry="INSERT INTO public.tbl_aufmerksamdurch (aufmerksamdurch_kurzbz, beschreibung, ext_id) VALUES('".
	     $aufmerksam_kurzbz[$i]."', '".
	     $beschreibung[$i]."', '".
	     ($i+1)."');";
	pg_query($conn,$qry);
}

$qry="INSERT INTO public.tbl_erhalter (erhalter_kz, kurzbz, bezeichnung, dvr, logo, zvr) VALUES('5', 'TW','Technikum Wien', '0928381',null,'074476426');";
$result=pg_query($conn,$qry);

$rolle_kurzbz=array('Interessent','Bewerber','Student','Ausserordentlicher','Abgewiesener','Aufgenommener','Wartender',
		'Abbrecher','Unterbrecher','Outgoing','Incoming','Praktikant','Diplomant','Absolvent');		
for ($i=0; $i<=13; $i++)
{
	$qry="INSERT INTO public.tbl_rolle(rolle_kurzbz, beschreibung, anmerkung, ext_id) VALUES('".
		$rolle_kurzbz[$i]."', null, null,'".($i+1)."');";
	pg_query($conn,$qry);
}

$raumtyp=array('SEM','GLAB','UEB','HSk','HSg','LM','CLAB','PLAB','RLAB','ESLab','TKLab','EDV6','Dummy','EXT','DIV','EDV2','BMLab','DASLab','SETLab','ITSLab','EDV5');
$rtbeschreibung=array('Seminiarraum','Laboratoium','�bungsraum','H�rsaal klein','H�rsaal gro�','Lehrmittel','Chemielabor','Projektlabor','Robotiklabor','Embedded Systems Labor','Telekom Labor','EDV-Saal 6.Stock','Dummy','Externe R�ume','Diverse','EDV_Saal 2.Stock','Biomechanisches Labor','Datensicherheitslabor','SET Labor','ITS Labor','EDV-Saal 5. Stock');
for($i=0;$i<20;$i++)
{
	$qry="INSERT INTO public.tbl_raumtyp(raumtyp_kurzbez.beschreibung)VALUES('$raumtyp[$i]','$rtbeschreibung[$i]');";
	pg_query($conn,$qry);
}

$sprache=array('German','English','Espanol');
for ($i=0; $i<=2; $i++)
{
	$qry="INSERT INTO public.tbl_sprache(sprache) VALUES('".
		$sprache[$i]."');";
	pg_query($conn,$qry);
}
?>
</body>
</html>