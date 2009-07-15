<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>TesttoolCleanEncoding</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
<?php
/**
 * Die Testtool Daten haben nach der Umstellung auf UTF8 ein Fehlerhaftes Encoding.
 * Um dies zu bereinigen, muss dieses Script gestartet werden.
 * 
 * ACHTUNG! Dieses Script darf nicht mehrmals auf die gleiche DB angewandt werden
 */
require_once('../config/system.config.inc.php');
require_once('../include/basis_db.class.php');

$db = new basis_db();

$db->db_query('BEGIN;');

$qry = "UPDATE testtool.tbl_frage_sprache SET text=convert_from(convert_to(text,'LATIN9'),'UTF8');
		UPDATE testtool.tbl_vorschlag_sprache SET text=convert_from(convert_to(text,'LATIN9'),'UTF8') WHERE text<>'' and text is not null AND strpos(text,'ß')=0;";
if($db->db_query($qry))
	echo "Aktualiserung erfolgreich";
else 
	echo "Fehler beim Aktualisieren:".$db->db_last_error();
?>
</body>
</html>