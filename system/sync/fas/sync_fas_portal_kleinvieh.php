<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert diverse Datensaetze von FAS DB in PORTAL DB
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

/*$raumtyp=array('SEM','GLAB','UEB','HSk','HSg','LM','CLAB','PLAB','RLAB','ESLab','TKLab','EDV6','Dummy','EXT','DIV','EDV2','BMLab','DASLab','SETLab','ITSLab','EDV5');
$rtbeschreibung=array('Seminiarraum','Laboratoium','Übungsraum','Hörsaal klein','Hörsaal groß','Lehrmittel','Chemielabor','Projektlabor','Robotiklabor','Embedded Systems Labor','Telekom Labor','EDV-Saal 6.Stock','Dummy','Externe Räume','Diverse','EDV_Saal 2.Stock','Biomechanisches Labor','Datensicherheitslabor','SET Labor','ITS Labor','EDV-Saal 5. Stock');
for($i=0;$i<20;$i++)
{
	$qry="INSERT INTO public.tbl_raumtyp(raumtyp_kurzbez, beschreibung)VALUES('$raumtyp[$i]','$rtbeschreibung[$i]');";
	pg_query($conn,$qry);
}
ECHO NL2BR ( "\nraumtyp synchronisiert");*/


$aufmerksam_kurzbz=array('k.A.', 'Internet', 'Zeitungen','Werbung','Mundpropaganda','FH-Führer','BEST Messe','Partnerfirma','Schule','Bildungstelefon',
			'TGM','Abgeworben','Technikum Wien','Aussendungen','offene Tür');
$beschreibung=array('keine Angabe',null,null,'Werbung, Veranstaltungen', null,null,null,null,null,'Bildungstelefon AK',null,'Abgeworben von Konkurrenz',null,'Aussendungen eines Studiengangs','Tag der offenen Tür');

for ($i=0; $i<=14; $i++)
{
	$qry="INSERT INTO public.tbl_aufmerksamdurch (aufmerksamdurch_kurzbz, beschreibung, ext_id) VALUES('".
	     $aufmerksam_kurzbz[$i]."', '".
	     $beschreibung[$i]."', '".
	     ($i+1)."');";
	pg_query($conn,$qry);
}
ECHO NL2BR ( "aufmerksamdurch synchronisiert");

//$qry="INSERT INTO public.tbl_erhalter (erhalter_kz, kurzbz, bezeichnung, dvr, logo, zvr) VALUES('5', 'TW','Technikum Wien', '0928381',null,'074476426');";
//$result=pg_query($conn,$qry);
//ECHO NL2BR ( "\nerhalter synchronisiert");

$rolle_kurzbz=array('Interessent','Bewerber','Student','Ausserordentlicher','Abgewiesener','Aufgenommener','Wartender',
		'Abbrecher','Unterbrecher','Outgoing','Incoming','Praktikant','Diplomant','Absolvent');
for ($i=0; $i<=13; $i++)
{
	$qry="INSERT INTO public.tbl_rolle(rolle_kurzbz, beschreibung, anmerkung, ext_id) VALUES('".
		$rolle_kurzbz[$i]."', null, null,'".($i+1)."');";
	pg_query($conn,$qry);
}
ECHO NL2BR ( "\nrolle synchronisiert");


$sprache=array('German','English','Espanol');
for ($i=0; $i<=2; $i++)
{
	$qry="INSERT INTO public.tbl_sprache(sprache) VALUES('".
		$sprache[$i]."');";
	pg_query($conn,$qry);
}
ECHO NL2BR ( "\nsprache synchronisiert");


pg_query($conn,"INSERT INTO bis.tbl_mobilitaetsprogramm(mobilitaetsprogramm_code, kurzbz, beschreibung) VALUES ('6','CEEPUS','CEEPUS');");
pg_query($conn,"INSERT INTO bis.tbl_mobilitaetsprogramm(mobilitaetsprogramm_code, kurzbz, beschreibung) VALUES ('7','ERASMUS','ERASMUS');");
pg_query($conn,"INSERT INTO bis.tbl_mobilitaetsprogramm(mobilitaetsprogramm_code, kurzbz, beschreibung) VALUES ('9','LEONARDO','LEONARDO da VINCI');");
pg_query($conn,"INSERT INTO bis.tbl_mobilitaetsprogramm(mobilitaetsprogramm_code, kurzbz, beschreibung) VALUES ('10','supranat','Praktikum bei einer internationalen oder supranationalen Organisation');");
pg_query($conn,"INSERT INTO bis.tbl_mobilitaetsprogramm(mobilitaetsprogramm_code, kurzbz, beschreibung) VALUES ('11','DAF','Deutsch als Fremdsprache - Praktikum (DAF)');");
pg_query($conn,"INSERT INTO bis.tbl_mobilitaetsprogramm(mobilitaetsprogramm_code, kurzbz, beschreibung) VALUES ('12','Postgrad','Postgraduate - Stipendium (Fulbright, Bundesministerium für Bildung, Wissenschaft und Kultur');");
pg_query($conn,"INSERT INTO bis.tbl_mobilitaetsprogramm(mobilitaetsprogramm_code, kurzbz, beschreibung) VALUES ('13','Austausch','Austauschstipendium (z.B. Kulturabkommen, Aktionen Österreich-...)');");
pg_query($conn,"INSERT INTO bis.tbl_mobilitaetsprogramm(mobilitaetsprogramm_code, kurzbz, beschreibung) VALUES ('14','Ausland','Auslandstipendium für Studierende von Universitäten der Künste');");
pg_query($conn,"INSERT INTO bis.tbl_mobilitaetsprogramm(mobilitaetsprogramm_code, kurzbz, beschreibung) VALUES ('20','Gödel','Kurt Gödl - Stipendium');");
pg_query($conn,"INSERT INTO bis.tbl_mobilitaetsprogramm(mobilitaetsprogramm_code, kurzbz, beschreibung) VALUES ('22','ALektorat','Auslandslektorat');");
pg_query($conn,"INSERT INTO bis.tbl_mobilitaetsprogramm(mobilitaetsprogramm_code, kurzbz, beschreibung) VALUES ('30','so.Stip.','sonstiges Stipendium');");
pg_query($conn,"INSERT INTO bis.tbl_mobilitaetsprogramm(mobilitaetsprogramm_code, kurzbz, beschreibung) VALUES ('31','EZusS','Stipendium der österreichischen Entwicklungszusammenarbeit');");
pg_query($conn,"INSERT INTO bis.tbl_mobilitaetsprogramm(mobilitaetsprogramm_code, kurzbz, beschreibung) VALUES ('32','ÖStip','Österreich - Stipendium');");
pg_query($conn,"INSERT INTO bis.tbl_mobilitaetsprogramm(mobilitaetsprogramm_code, kurzbz, beschreibung) VALUES ('33','Mach','Erst Mach - Stipendium');");
pg_query($conn,"INSERT INTO bis.tbl_mobilitaetsprogramm(mobilitaetsprogramm_code, kurzbz, beschreibung) VALUES ('34','Werfel','Franz Werfel - Stipendium');");
pg_query($conn,"INSERT INTO bis.tbl_mobilitaetsprogramm(mobilitaetsprogramm_code, kurzbz, beschreibung) VALUES ('35','Suttner','Bertha von Suttner - Stipendium');");
pg_query($conn,"INSERT INTO bis.tbl_mobilitaetsprogramm(mobilitaetsprogramm_code, kurzbz, beschreibung) VALUES ('37','APART','APART - Stipendium der Österreichischen Akademie der Wissenschaften');");
pg_query($conn,"INSERT INTO bis.tbl_mobilitaetsprogramm(mobilitaetsprogramm_code, kurzbz, beschreibung) VALUES ('41','EU3Prog.','EU-Drittstaatenprogramm (EU-China, EU-USA, EU-Kanada, usw.)');");
pg_query($conn,"INSERT INTO bis.tbl_mobilitaetsprogramm(mobilitaetsprogramm_code, kurzbz, beschreibung) VALUES ('42','EUPrak','EU-Praktikumstipendium (EU-Kommission, EU-Rat, EU_Parlament)');");
pg_query($conn,"INSERT INTO bis.tbl_mobilitaetsprogramm(mobilitaetsprogramm_code, kurzbz, beschreibung) VALUES ('201','FH-Mob','Von der FH organisierte/r Mobilitätsvereinbarung (Partnerschaftsabkommen, udgl.) bzw. Aufenthalt');");
pg_query($conn,"INSERT INTO bis.tbl_mobilitaetsprogramm(mobilitaetsprogramm_code, kurzbz, beschreibung) VALUES ('202','selbst','Vom Studierenden selbst organisierte/r Mobilitätsvereinbarung bzw. Aufenthalt');");
pg_query($conn,"INSERT INTO bis.tbl_mobilitaetsprogramm(mobilitaetsprogramm_code, kurzbz, beschreibung) VALUES ('203','FHspez.','FH-spezifisches Mobilitätsprogramm mit einem anderen österreichischen FH-Studiengang');");
ECHO NL2BR ( "\nmobilitaetsprogramm synchronisiert");

pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('Dipl.-Ing.(FH)','11','Diplomingenieur (FH) für technisch-wissenschaftliche Berufe','m');");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('Dipl.-Ing.(FH)','11','Diplomingenieurin (FH) für technisch-wissenschaftliche Berufe','w');");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('Dipl.-Ing.(FH)','91','Diplomingenieur (FH) für technisch-wissenschaftliche Berufe','m');");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('Dipl.-Ing.(FH)','91','Diplomingenieurin (FH) für technisch-wissenschaftliche Berufe','w');");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('Dipl.-Ing.(FH)','92','Diplomingenieur (FH) für technisch-wissenschaftliche Berufe','m');");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('Dipl.-Ing.(FH)','92','Diplomingenieurin (FH) für technisch-wissenschaftliche Berufe','w');");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('Dipl.-Ing.(FH)','94','Diplomingenieur (FH) für technisch-wissenschaftliche Berufe','m');");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('Dipl.-Ing.(FH)','94','Diplomingenieurin (FH) für technisch-wissenschaftliche Berufe','w');");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('Dipl.-Ing.(FH)','145','Diplomingenieur (FH) für technisch-wissenschaftliche Berufe','m');");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('Dipl.-Ing.(FH)','145','Diplomingenieurin (FH) für technisch-wissenschaftliche Berufe','w');");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('Dipl.-Ing.(FH)','182','Diplomingenieur (FH) für technisch-wissenschaftliche Berufe','m');");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('Dipl.-Ing.(FH)','182','Diplomingenieurin (FH) für technisch-wissenschaftliche Berufe','w');");
//pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('Dipl.-Ing.(FH)','203','Diplomingenieur (FH) für technisch-wissenschaftliche Berufe','m');");
//pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('Dipl.-Ing.(FH)','203','Diplomingenieurin (FH) für technisch-wissenschaftliche Berufe','w');");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('Dipl.-Ing.(FH)','204','Diplomingenieur (FH) für technisch-wissenschaftliche Berufe','m');");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('Dipl.-Ing.(FH)','204','Diplomingenieurin (FH) für technisch-wissenschaftliche Berufe','w');");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('Dipl.-Ing.(FH)','222','Diplomingenieur (FH) für technisch-wissenschaftliche Berufe','m');");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('Dipl.-Ing.(FH)','222','Diplomingenieurin (FH) für technisch-wissenschaftliche Berufe','w');");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('BSc','227','Bachelor of Science in Engineering',null);");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('MSc','228','Master of Science in Engineering',null);");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('BSc','254','Bachelor of Science in Engineering',null);");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('BSc','255','Bachelor of Science in Engineering',null);");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('BSc','256','Bachelor of Science in Engineering',null);");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('BSc','257','Bachelor of Science in Engineering',null);");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('BSc','258','Bachelor of Science in Engineering',null);");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('MSc','297','Master of Science in Engineering',null);");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('MSc','298','Master of Science in Engineering',null);");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('MSc','299','Master of Science in Engineering',null);");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('MSc','300','Master of Science in Engineering',null);");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('MSc','301','Master of Science in Engineering',null);");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('MSc','302','Master of Science in Engineering',null);");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('MSc','303','Master of Science in Engineering',null);");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('Dipl.-Ing.(FH)','308','Diplomingenieur (FH) für technisch-wissenschaftliche Berufe','m');");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('Dipl.-Ing.(FH)','308','Diplomingenieurin (FH) für technisch-wissenschaftliche Berufe','w');");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('BSc','327','Bachelor of Science in Engineering',null);");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('MSc','328','Master of Science in Engineering',null);");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('MSc','329','Master of Science in Engineering',null);");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('BSc','330','Bachelor of Science in Engineering',null);");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('MSc','331','Master of Science in Engineering',null);");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('MSc','332','Master of Science in Engineering',null);");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('BSc','333','Bachelor of Science in Engineering',null);");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('MSc','334','Master of Science in Engineering',null);");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('BSc','335','Bachelor of Science in Engineering',null);");
pg_query($conn,"INSERT INTO lehre.tbl_akadgrad (akadgrad_kurzbz, studiengang_kz, titel, geschlecht) VALUES ('MSc','336','Master of Science in Engineering',null);");
ECHO NL2BR ( "\nakadgrad synchronisiert");

pg_query($conn,"INSERT INTO bis.tbl_berufstaetigkeit (berufstaetigkeit_code, berufstaetigkeit_bez, berufstaetigkeit_kurzbz) VALUES ('0','nicht berufstätig','n.berufstätig')");
pg_query($conn,"INSERT INTO bis.tbl_berufstaetigkeit (berufstaetigkeit_code, berufstaetigkeit_bez, berufstaetigkeit_kurzbz) VALUES ('2','arbeitslos gemeldet mit facheinschlägiger Berufserfahrung','fach.arbeitslos')");
pg_query($conn,"INSERT INTO bis.tbl_berufstaetigkeit (berufstaetigkeit_code, berufstaetigkeit_bez, berufstaetigkeit_kurzbz) VALUES ('3','arbeitslos gemeldet sonstige','so.arbeitslos')");
pg_query($conn,"INSERT INTO bis.tbl_berufstaetigkeit (berufstaetigkeit_code, berufstaetigkeit_bez, berufstaetigkeit_kurzbz) VALUES ('6','Vollzeit facheinschlägig berufstätig','Vz fach')");
pg_query($conn,"INSERT INTO bis.tbl_berufstaetigkeit (berufstaetigkeit_code, berufstaetigkeit_bez, berufstaetigkeit_kurzbz) VALUES ('7','Teilzeit facheinschlägig berufstätig','Tz fach')");
pg_query($conn,"INSERT INTO bis.tbl_berufstaetigkeit (berufstaetigkeit_code, berufstaetigkeit_bez, berufstaetigkeit_kurzbz) VALUES ('9','Vollzeit nicht facheinschlägig berufstätig','Vz sonst')");
pg_query($conn,"INSERT INTO bis.tbl_berufstaetigkeit (berufstaetigkeit_code, berufstaetigkeit_bez, berufstaetigkeit_kurzbz) VALUES ('10','Teilzeit nicht facheinschlägig berufstätig','Tz sonst')");
ECHO NL2BR ( "\nberufstätigkeit synchronisiert");

pg_query($conn,"INSERT INTO bis.tbl_zgv (zgv_code, zgv_bez, zgv_kurzbz) VALUES ('4','Anerkannte Studienberechtigungsprüfung','Studberprüfung');");
pg_query($conn,"INSERT INTO bis.tbl_zgv (zgv_code, zgv_bez, zgv_kurzbz) VALUES ('5','Ausländische Universitätsreife','Ausl.Univreife');");
pg_query($conn,"INSERT INTO bis.tbl_zgv (zgv_code, zgv_bez, zgv_kurzbz) VALUES ('6','Abschlusszeugnis einer facheinschlägigen BMS','FachBMS');");
pg_query($conn,"INSERT INTO bis.tbl_zgv (zgv_code, zgv_bez, zgv_kurzbz) VALUES ('7','Lehrabschlussprüfung mit allfälligen Zusatzqualifikationen','Lehrabschluss');");
pg_query($conn,"INSERT INTO bis.tbl_zgv (zgv_code, zgv_bez, zgv_kurzbz) VALUES ('8','Werkmeister','Werkmeister');");
pg_query($conn,"INSERT INTO bis.tbl_zgv (zgv_code, zgv_bez, zgv_kurzbz) VALUES ('9','AHS (langform)','AHS-lang');");
pg_query($conn,"INSERT INTO bis.tbl_zgv (zgv_code, zgv_bez, zgv_kurzbz) VALUES ('10','Oberstufenrealgymnasium','OberstufenRG');");
pg_query($conn,"INSERT INTO bis.tbl_zgv (zgv_code, zgv_bez, zgv_kurzbz) VALUES ('11','AHS (Sonderformen)','AHS-sonder');");
pg_query($conn,"INSERT INTO bis.tbl_zgv (zgv_code, zgv_bez, zgv_kurzbz) VALUES ('12','Höhere technische und gewerbliche Lehranstalten','Htg.Lehranstalt');");
pg_query($conn,"INSERT INTO bis.tbl_zgv (zgv_code, zgv_bez, zgv_kurzbz) VALUES ('13','Handelsakademien','Handelsakademien');");
pg_query($conn,"INSERT INTO bis.tbl_zgv (zgv_code, zgv_bez, zgv_kurzbz) VALUES ('14','Höhere Lehranstalt für wirtschaftliche Berufe','Wirtschaftl.Ber.');");
pg_query($conn,"INSERT INTO bis.tbl_zgv (zgv_code, zgv_bez, zgv_kurzbz) VALUES ('15','Höhere land- und forstwirtschaftliche Lehranstalten','Land-u.Forst');");
pg_query($conn,"INSERT INTO bis.tbl_zgv (zgv_code, zgv_bez, zgv_kurzbz) VALUES ('16','Höhere Schulen der Lehrer- und Erzieherbildung','Lehrer');");
pg_query($conn,"INSERT INTO bis.tbl_zgv (zgv_code, zgv_bez, zgv_kurzbz) VALUES ('17','Externistenreifeprüfung','Externist');");
pg_query($conn,"INSERT INTO bis.tbl_zgv (zgv_code, zgv_bez, zgv_kurzbz) VALUES ('18','Berufreifeprüfung','Berufsreife');");
pg_query($conn,"INSERT INTO bis.tbl_zgv (zgv_code, zgv_bez, zgv_kurzbz) VALUES ('19','Inländische postsekundäre Bildungseinrichtung','Inl.postsekundär');");
pg_query($conn,"INSERT INTO bis.tbl_zgv (zgv_code, zgv_bez, zgv_kurzbz) VALUES ('99','Sonstige','Sonstige');");
ECHO NL2BR ( "\nzgv synchronisiert");

pg_query($conn,"INSERT INTO bis.tbl_zgvmaster (zgvmas_code, zgvmas_bez, zgvmas_kurzbz) VALUES ('1','FH-Abschluss Bachelor (Inland)','FH-Bachelor (I)');");
pg_query($conn,"INSERT INTO bis.tbl_zgvmaster (zgvmas_code, zgvmas_bez, zgvmas_kurzbz) VALUES ('2','FH-Abschluss Bachelor (Ausland)','FH-Bachelor (A)');");
pg_query($conn,"INSERT INTO bis.tbl_zgvmaster (zgvmas_code, zgvmas_bez, zgvmas_kurzbz) VALUES ('3','Abschluss postsekundäres Studium (Inland)','postsek.Inland');");
pg_query($conn,"INSERT INTO bis.tbl_zgvmaster (zgvmas_code, zgvmas_bez, zgvmas_kurzbz) VALUES ('4','Abschluss postsekundäres Studium (Ausland)','postsek.Ausland');");
pg_query($conn,"INSERT INTO bis.tbl_zgvmaster (zgvmas_code, zgvmas_bez, zgvmas_kurzbz) VALUES ('5','Univ.-Abschluss Bachelor (Inland)','Uni-Bachelor (I)');");
pg_query($conn,"INSERT INTO bis.tbl_zgvmaster (zgvmas_code, zgvmas_bez, zgvmas_kurzbz) VALUES ('6','Univ.-Abschluss Bachelor (Ausland)','Uni-Bachelor (A)');");
pg_query($conn,"INSERT INTO bis.tbl_zgvmaster (zgvmas_code, zgvmas_bez, zgvmas_kurzbz) VALUES ('7','FH-Abschluss Dipl.-Ing. / Mag. / Master / Dr. (Inland)','FH-Master (I)');");
pg_query($conn,"INSERT INTO bis.tbl_zgvmaster (zgvmas_code, zgvmas_bez, zgvmas_kurzbz) VALUES ('8','FH-Abschluss Dipl.-Ing. / Mag. / Master / Dr. / PhD (Ausland)','FH-Master (A)');");
pg_query($conn,"INSERT INTO bis.tbl_zgvmaster (zgvmas_code, zgvmas_bez, zgvmas_kurzbz) VALUES ('9','Univ. Abschluss Dipl.-Ing. / Mag. / Master / Dr. (Inland)','Uni-Master (I)');");
pg_query($conn,"INSERT INTO bis.tbl_zgvmaster (zgvmas_code, zgvmas_bez, zgvmas_kurzbz) VALUES ('10','Univ. Abschluss Dipl.-Ing. / Mag. / Master / Dr. / PhD (Ausland)','Uni-Master (A)');");
pg_query($conn,"INSERT INTO bis.tbl_zgvmaster (zgvmas_code, zgvmas_bez, zgvmas_kurzbz) VALUES ('11','Sonstige','Sonstige');");
ECHO NL2BR ( "\nzgvmaster synchronisiert");

pg_query($conn,"INSERT INTO bis.tbl_ausbildung (ausbildungcode, ausbildungbez, ausbildungbeschreibung) VALUES ('1','PhD','Universitätsabschluss mit Doktorat als Zweit- oder Drittabschluss oder PhD-Abschluss');");
pg_query($conn,"INSERT INTO bis.tbl_ausbildung (ausbildungcode, ausbildungbez, ausbildungbeschreibung) VALUES ('2','Univ.-Master','Universitäts- oder Hochschulabschluss auf Dipolom- oder Masterebene, Doktorat der Medizin bzw. der Human- oder Zahnmedizin oder Doktorat auf Grund von Studienvorschriften aus der Zeit vor dem Inkrafttretendes AHStG BGBl. Nr. 177/1966 oder Abschluss eines Universitätslehrganges oder Lehrganges universitären Charakters (§51 Abs. 2 Z 23 UG 2002 oder §§26 Abs.1 und 28 Abs.1 UniStG) oder eines Lehrganges zur Weiterbildung (§14a Abs.2 FHStG) mit Mastergrad');");
pg_query($conn,"INSERT INTO bis.tbl_ausbildung (ausbildungcode, ausbildungbez, ausbildungbeschreibung) VALUES ('3','FH-Master','Fachhochschulabschluss auf Diplom- oder Masterebene');");
pg_query($conn,"INSERT INTO bis.tbl_ausbildung (ausbildungcode, ausbildungbez, ausbildungbeschreibung) VALUES ('4','Univ.-Bachelor','Universitäts- oder Hochschulabschluss auf Bachelorebene (einschließlich Kurzstudien)');");
pg_query($conn,"INSERT INTO bis.tbl_ausbildung (ausbildungcode, ausbildungbez, ausbildungbeschreibung) VALUES ('5','FH-Bachelor','Fachhochschulabschluss auf Bachelorebene');");
pg_query($conn,"INSERT INTO bis.tbl_ausbildung (ausbildungcode, ausbildungbez, ausbildungbeschreibung) VALUES ('6','Akad-Diplom','Diplom einer Akademie für Lehrerbildung, Akademie für Sozialarbeit, Medizinisch-technische Akademie, Hebammenakademie, Militärakademie oder einer anderen anerkannten postsekundären Bildungseinrichtung');");
pg_query($conn,"INSERT INTO bis.tbl_ausbildung (ausbildungcode, ausbildungbez, ausbildungbeschreibung) VALUES ('7','tertiär','Anderer tertieärer Bildungsabschluss (Kolleg; Meisterprüfung; Universitätslehrgang oder Lehrgang gemäß §14a Abs.3 FHStG, mit dem kein akademischer Grad verbunden war)');");
pg_query($conn,"INSERT INTO bis.tbl_ausbildung (ausbildungcode, ausbildungbez, ausbildungbeschreibung) VALUES ('8','AHS','Reifeprüfung an einer allgemeinbildenden höheren Schule');");
pg_query($conn,"INSERT INTO bis.tbl_ausbildung (ausbildungcode, ausbildungbez, ausbildungbeschreibung) VALUES ('9','BHS','Reife- und Diplomprüfung einer berufsbildenden oder lehrer- und erzieherbildenden höheren Schule');");
pg_query($conn,"INSERT INTO bis.tbl_ausbildung (ausbildungcode, ausbildungbez, ausbildungbeschreibung) VALUES ('10','Lehrabschluss','Lehrabschlussprüfung, berufsbildende mittlere Schule oder vergleichbare Berufsausbildung');");
pg_query($conn,"INSERT INTO bis.tbl_ausbildung (ausbildungcode, ausbildungbez, ausbildungbeschreibung) VALUES ('11','Pflichtschule','Pflichtschule');");
ECHO NL2BR ( "\nausbildung synchronisiert");

pg_query($conn,"INSERT INTO bis.tbl_beschaeftigungsart1(ba1code, ba1bez) VALUES ('1','Dienstverhältnis zum Bund');");
pg_query($conn,"INSERT INTO bis.tbl_beschaeftigungsart1(ba1code, ba1bez) VALUES ('2','Dienstverhältnis zu einer anderen Gebietskörperschaft');");
pg_query($conn,"INSERT INTO bis.tbl_beschaeftigungsart1(ba1code, ba1bez) VALUES ('3','Dienstverhältnis zur Bildungseinrichtung oder deren Träger (Echter Dienstvertrag)');");
pg_query($conn,"INSERT INTO bis.tbl_beschaeftigungsart1(ba1code, ba1bez) VALUES ('4','Dienstverhältnis zur Bildungseinrichtung oder deren Träger (Freier Dienstvertrag)');");
pg_query($conn,"INSERT INTO bis.tbl_beschaeftigungsart1(ba1code, ba1bez) VALUES ('5','Lehr- oder Ausbildungsverhältnis');");
pg_query($conn,"INSERT INTO bis.tbl_beschaeftigungsart1(ba1code, ba1bez) VALUES ('6','Sonstiges Beschäftigungsverhältnis (inkludiert z.B. Werkverträge');");
ECHO NL2BR ( "\nbeschaeftigungsart1 synchronisiert");

pg_query($conn,"INSERT INTO bis.tbl_beschaeftigungsart2(ba2code, ba2bez) VALUES ('1','unbefristet');");
pg_query($conn,"INSERT INTO bis.tbl_beschaeftigungsart2(ba2code, ba2bez) VALUES ('2','befristet');");
ECHO NL2BR ( "\nbeschaeftigungsart2 synchronisiert");

pg_query($conn, "INSERT INTO bis.tbl_beschaeftigungsausmass(beschausmasscode, beschausmassbez, min, max) VALUES ('1','Vollzeit','36','168')");
pg_query($conn, "INSERT INTO bis.tbl_beschaeftigungsausmass(beschausmasscode, beschausmassbez, min, max) VALUES ('2','0-15','0','15')");
pg_query($conn, "INSERT INTO bis.tbl_beschaeftigungsausmass(beschausmasscode, beschausmassbez, min, max) VALUES ('3','16-25','16','25')");
pg_query($conn, "INSERT INTO bis.tbl_beschaeftigungsausmass(beschausmasscode, beschausmassbez, min, max) VALUES ('4','26-35','26','35')");
pg_query($conn, "INSERT INTO bis.tbl_beschaeftigungsausmass(beschausmasscode, beschausmassbez, min, max) VALUES ('5','Karenz','0','0')");
ECHO NL2BR ( "\nbeschaeftigungsausmass synchronisiert");

pg_query($conn,"INSERT INTO bis.tbl_verwendung(verwendung_code, verwendungbez) VALUES ('1','Lehr- und Forschungspersonal (Academic staff)');");
pg_query($conn,"INSERT INTO bis.tbl_verwendung(verwendung_code, verwendungbez) VALUES ('2','Lehr- und Forschungshilfspersonal (Teaching and Research assistants)');");
pg_query($conn,"INSERT INTO bis.tbl_verwendung(verwendung_code, verwendungbez) VALUES ('3','Akademische Dienste für Studierende(Academic Support');");
pg_query($conn,"INSERT INTO bis.tbl_verwendung(verwendung_code, verwendungbez) VALUES ('4','Soziale Dienste und Gesundheitsdienste (Health and Social Support');");
pg_query($conn,"INSERT INTO bis.tbl_verwendung(verwendung_code, verwendungbez) VALUES ('5','Studiengangsleiter/in');");
pg_query($conn,"INSERT INTO bis.tbl_verwendung(verwendung_code, verwendungbez) VALUES ('6','Leiter/in FH-Kollegium');");
pg_query($conn,"INSERT INTO bis.tbl_verwendung(verwendung_code, verwendungbez) VALUES ('7','Management (Scool Level Management');");
pg_query($conn,"INSERT INTO bis.tbl_verwendung(verwendung_code, verwendungbez) VALUES ('8','Verwaltung (Scool Level Administrative Personnel');");
pg_query($conn,"INSERT INTO bis.tbl_verwendung(verwendung_code, verwendungbez) VALUES ('9','Hauspersonal, Gebäude-/Hautechnik (Maintainance and Operations Personnel');");
ECHO NL2BR ( "\nverwendung synchronisiert");

pg_query($conn,"INSERT INTO bis.tbl_besqual(besqualcode, besqualbez) VALUES ('0','Keine');");
pg_query($conn,"INSERT INTO bis.tbl_besqual(besqualcode, besqualbez) VALUES ('1','Habilitation');");
pg_query($conn,"INSERT INTO bis.tbl_besqual(besqualcode, besqualbez) VALUES ('2','der Habilitation gleichwertige Qualifikation');");
pg_query($conn,"INSERT INTO bis.tbl_besqual(besqualcode, besqualbez) VALUES ('3','berufliche Tätigkeit');");
ECHO NL2BR ( "\nbesqual synchronisiert");

pg_query($conn,"INSERT INTO bis.tbl_hauptberuf(hauptberufcode, bezeichnung) VALUES ('0','Universität');");
pg_query($conn,"INSERT INTO bis.tbl_hauptberuf(hauptberufcode, bezeichnung) VALUES ('1','Fachhochschule');");
pg_query($conn,"INSERT INTO bis.tbl_hauptberuf(hauptberufcode, bezeichnung) VALUES ('2','Andere postsekundäre Bildungseinrichtung');");
pg_query($conn,"INSERT INTO bis.tbl_hauptberuf(hauptberufcode, bezeichnung) VALUES ('3','Allgemeinbildende höhere Schule');");
pg_query($conn,"INSERT INTO bis.tbl_hauptberuf(hauptberufcode, bezeichnung) VALUES ('4','Berufsbildende höhere Schule');");
pg_query($conn,"INSERT INTO bis.tbl_hauptberuf(hauptberufcode, bezeichnung) VALUES ('5','Andere Schule');");
pg_query($conn,"INSERT INTO bis.tbl_hauptberuf(hauptberufcode, bezeichnung) VALUES ('6','Öffentlicher Sektor');");
pg_query($conn,"INSERT INTO bis.tbl_hauptberuf(hauptberufcode, bezeichnung) VALUES ('7','Unternehmenssektor');");
pg_query($conn,"INSERT INTO bis.tbl_hauptberuf(hauptberufcode, bezeichnung) VALUES ('8','Freiberuflich tätig');");
pg_query($conn,"INSERT INTO bis.tbl_hauptberuf(hauptberufcode, bezeichnung) VALUES ('9','Privater gemeinnütziger Sektor');");
pg_query($conn,"INSERT INTO bis.tbl_hauptberuf(hauptberufcode, bezeichnung) VALUES ('10','Außerhochschulische Forschungseinrichtung');");
pg_query($conn,"INSERT INTO bis.tbl_hauptberuf(hauptberufcode, bezeichnung) VALUES ('11','Internationale Organisation');");
pg_query($conn,"INSERT INTO bis.tbl_hauptberuf(hauptberufcode, bezeichnung) VALUES ('12','Sonstiges');");
ECHO NL2BR ( "\nhauptberuf synchronisiert");

pg_query($conn,"INSERT INTO bis.tbl_zweck(zweck_code, kurzbz, bezeichnung) VALUES ('1','S','Studium');");
pg_query($conn,"INSERT INTO bis.tbl_zweck(zweck_code, kurzbz, bezeichnung) VALUES ('2','P','Praktikum');");
pg_query($conn,"INSERT INTO bis.tbl_zweck(zweck_code, kurzbz, bezeichnung) VALUES ('3','SP','Studium und Praktikum');");
ECHO NL2BR ( "\nzweck synchronisiert");

pg_query($conn,"INSERT INTO lehre.tbl_note(note, bezeichnung, anmerkung) VALUES ('1','Sehr Gut','1');");
pg_query($conn,"INSERT INTO lehre.tbl_note(note, bezeichnung, anmerkung) VALUES ('2','Gut','2');");
pg_query($conn,"INSERT INTO lehre.tbl_note(note, bezeichnung, anmerkung) VALUES ('3','Befriedigend','3');");
pg_query($conn,"INSERT INTO lehre.tbl_note(note, bezeichnung, anmerkung) VALUES ('4','Genügend','4');");
pg_query($conn,"INSERT INTO lehre.tbl_note(note, bezeichnung, anmerkung) VALUES ('5','Nicht Genügend','5');");
pg_query($conn,"INSERT INTO lehre.tbl_note(note, bezeichnung, anmerkung) VALUES ('6','angerechnet','ar');");
pg_query($conn,"INSERT INTO lehre.tbl_note(note, bezeichnung, anmerkung) VALUES ('7','Nicht beurteilt','nb');");
pg_query($conn,"INSERT INTO lehre.tbl_note(note, bezeichnung, anmerkung) VALUES ('8','Teilgenommen','tg');");
pg_query($conn,"INSERT INTO lehre.tbl_note(note, bezeichnung, anmerkung) VALUES ('9','Noch nicht eingetragen', null);");
pg_query($conn,"INSERT INTO lehre.tbl_note(note, bezeichnung, anmerkung) VALUES ('10','Bestanden','b');");
pg_query($conn,"INSERT INTO lehre.tbl_note(note, bezeichnung, anmerkung) VALUES ('11','Approbiert','ap');");
pg_query($conn,"INSERT INTO lehre.tbl_note(note, bezeichnung, anmerkung) VALUES ('12','Erfolgreich absolviert','ea');");
pg_query($conn,"INSERT INTO lehre.tbl_note(note, bezeichnung, anmerkung) VALUES ('13','Nicht erfolgreich absolviert','nea');");
ECHO NL2BR ( "\nnote synchronisiert");

pg_query($conn,"INSERT INTO public.tbl_dokument(dokument_kurzbz, bezeichnung, ext_id) VALUES ('Geburtsu','Geburtsurkunde','2');");
pg_query($conn,"INSERT INTO public.tbl_dokument(dokument_kurzbz, bezeichnung, ext_id) VALUES ('Staatsbn','Staatsbürgerschaftsnachweis','3');");
pg_query($conn,"INSERT INTO public.tbl_dokument(dokument_kurzbz, bezeichnung, ext_id) VALUES ('Meldezet','Meldezettel','4');");
pg_query($conn,"INSERT INTO public.tbl_dokument(dokument_kurzbz, bezeichnung, ext_id) VALUES ('Maturaze','Maturazeugnis','5');");
pg_query($conn,"INSERT INTO public.tbl_dokument(dokument_kurzbz, bezeichnung, ext_id) VALUES ('Führungs','Führungszeugnis','6');");
pg_query($conn,"INSERT INTO public.tbl_dokument(dokument_kurzbz, bezeichnung, ext_id) VALUES ('Lichtbil','2 Lichtbilder','7');");
pg_query($conn,"INSERT INTO public.tbl_dokument(dokument_kurzbz, bezeichnung, ext_id) VALUES ('SVNr','Sozialversicherungsnummer','8');");
pg_query($conn,"INSERT INTO public.tbl_dokument(dokument_kurzbz, bezeichnung, ext_id) VALUES ('Ausbvert','Ausbildungsvertrag 2-fach','9');");
pg_query($conn,"INSERT INTO public.tbl_dokument(dokument_kurzbz, bezeichnung, ext_id) VALUES ('Statisti','Statistikblatt','10');");
pg_query($conn,"INSERT INTO public.tbl_dokument(dokument_kurzbz, bezeichnung, ext_id) VALUES ('Studausw','Studentenausweis','11');");
pg_query($conn,"INSERT INTO public.tbl_dokument(dokument_kurzbz, bezeichnung, ext_id) VALUES ('Heiratsu','Heiratsurkunde','13');");
pg_query($conn,"INSERT INTO public.tbl_dokument(dokument_kurzbz, bezeichnung, ext_id) VALUES ('Ing.Nach','Ing.Nachweis','14');");
pg_query($conn,"INSERT INTO public.tbl_dokument(dokument_kurzbz, bezeichnung, ext_id) VALUES ('BD-urkun','Bakkalaureats- bzw. Diplomurkunde','15');");
ECHO NL2BR ( "\ndokument synchronisiert");

pg_query($conn,"INSERT INTO public.tbl_firmentyp(firmentyp_kurzbz, beschreibung) VALUES ('Partnerfirma','');");
pg_query($conn,"INSERT INTO public.tbl_firmentyp(firmentyp_kurzbz, beschreibung) VALUES ('Partneruniversität','');");
ECHO NL2BR ( "\nfirmentyp synchronisiert");

pg_query($conn, "INSERT INTO lehre.tbl_projekttyp(projekttyp_kurzbz, bezeichnung) VALUES ('Bachelor','Bachelorarbeit');");
pg_query($conn, "INSERT INTO lehre.tbl_projekttyp(projekttyp_kurzbz, bezeichnung) VALUES ('Diplom','Diplomarbeit');");
pg_query($conn, "INSERT INTO lehre.tbl_projekttyp(projekttyp_kurzbz, bezeichnung) VALUES ('Projekt','Projektarbeit');");
pg_query($conn, "INSERT INTO lehre.tbl_projekttyp(projekttyp_kurzbz, bezeichnung) VALUES ('Praktikum','Berufspraktikum');");
pg_query($conn, "INSERT INTO lehre.tbl_projekttyp(projekttyp_kurzbz, bezeichnung) VALUES ('Praxis','Praxissemester');");
ECHO NL2BR ( "\nprojekttyp synchronisiert");

pg_query($conn, "INSERT INTO lehre.tbl_pruefungstyp(pruefungstyp_kurzbz, beschreibung) VALUES ('Bachelor','Bachelorprüfung');");
pg_query($conn, "INSERT INTO lehre.tbl_pruefungstyp(pruefungstyp_kurzbz, beschreibung) VALUES ('Diplom','Diplomprüfung');");
ECHO NL2BR ( "\npruefungstyp synchronisiert");

pg_query($conn, "INSERT INTO public.tbl_kontakttyp(kontakttyp, beschreibung) VALUES ('email','E-Mail');");
pg_query($conn, "INSERT INTO public.tbl_kontakttyp(kontakttyp, beschreibung) VALUES ('telefon','Telefonnummer');");
pg_query($conn, "INSERT INTO public.tbl_kontakttyp(kontakttyp, beschreibung) VALUES ('mobil','Mobiltelefonnummer');");
pg_query($conn, "INSERT INTO public.tbl_kontakttyp(kontakttyp, beschreibung) VALUES ('fax','Faxnummer');");
pg_query($conn, "INSERT INTO public.tbl_kontakttyp(kontakttyp, beschreibung) VALUES ('so.tel','sonstige Telefonnummer');");
ECHO NL2BR ( "\nkontaktyp synchronisiert");

?>
</body>
</html>