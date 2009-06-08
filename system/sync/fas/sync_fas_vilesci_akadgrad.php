<?php
/* Copyright (C) 2007 Technikum-Wien
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

//*
//* Synchronisiert Akadgrad - Datensaetze von FAS DB in PORTAL DB
//*
//*

include('../../../vilesci/config.inc.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");

$adress='ruhan@technikum-wien.at';
//$adress='fas_sync@technikum-wien.at';

$error_log = '';
$text = '';
$anzahl_quelle = 0;
$anzahl_eingefuegt = 0;
$anzahl_fehler = 0;
$anzahl_quelle2 = 0;
$anzahl_eingefuegt2 = 0;
$anzahl_fehler2 = 0;

function validate($row)
{
}

/*************************
 * FAS-PORTAL - Synchronisation
 */
?>

<html>
<head>
<title>Synchro - FAS -> Portal - Akademische Grade</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
<?php

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


?>
</body>
</html>