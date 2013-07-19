<?php
/**
 * Resettet den Usability Test auf der CIS-Redesign Seite
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/basis_db.class.php');

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>Reset Usability Test</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
<h2>Reset Usability Test</h2>
<p>
<ul>
	<li>Foto löschen</li>
	<li>Fotostati löschen</li>
	<li>Daten aus Urlaubstool löschen und Demodaten eintragen</li>
	<li>Newseinträge löschen</li>
</ul>
</p>
<form action="'.$_SERVER['PHP_SELF'].'" method="POST">
<input type="submit" name="reset" value="Reset starten">
</form>';

/**  echo '
<br />
<br />
<h2>Abgabetool</h2>
<form action="'.$_SERVER['PHP_SELF'].'" method="POST">
<input type="hidden" value="student1" name="uid">
<input type="submit" name="reset_abgabe" value="Projektabgaben von Student1 resetten">
</form>
<form action="'.$_SERVER['PHP_SELF'].'" method="POST">
<input type="hidden" value="student2" name="uid">
<input type="submit" name="reset_abgabe" value="Projektabgaben von Student2 resetten">
</form>
<form action="'.$_SERVER['PHP_SELF'].'" method="POST">
<input type="hidden" value="student3" name="uid">
<input type="submit" name="reset_abgabe" value="Projektabgaben von Student3 resetten">
</form>
';  */

if(isset($_POST['reset']))
{
	/*echo '	<font color="green"><ul>
			<li>Foto gelöscht</li>
			<li>Fotostati gelöscht</li>
			<li>Daten aus Urlaubstool gelöscht</li>
			</ul></font> ';*/
	$db = new basis_db();
	
	$qry = "
	delete from public.tbl_person_fotostatus where person_id in (30566,453);
	delete from public.tbl_ampel_benutzer_bestaetigt where uid in ('gl1','if10b066');
	update public.tbl_person set foto=NULL, foto_sperre=FALSE where person_id in (30566,453);
	
	
	delete from campus.tbl_zeitsperre where mitarbeiter_uid='gl1';
	INSERT INTO campus.tbl_zeitsperre (zeitsperretyp_kurzbz,mitarbeiter_uid,bezeichnung,vondatum,vonstunde,bisdatum,bisstunde,vertretung_uid,updateamum,updatevon,insertamum,insertvon,erreichbarkeit_kurzbz,freigabeamum,freigabevon) VALUES ('Urlaub', 'gl1', 'Urlaub', '2013-05-01', NULL, '2013-05-03', NULL, NULL,NULL,NULL, now(), 'gl1',NULL,now(), 'kindlm');
	INSERT INTO campus.tbl_zeitsperre (zeitsperretyp_kurzbz,mitarbeiter_uid,bezeichnung,vondatum,vonstunde,bisdatum,bisstunde,vertretung_uid,updateamum,updatevon,insertamum,insertvon,erreichbarkeit_kurzbz,freigabeamum,freigabevon) VALUES ('Urlaub', 'gl1', 'Urlaub', '2013-05-06', NULL, '2013-05-10', NULL, NULL,NULL,NULL, now(), 'gl1',NULL,now(), 'kindlm');
	INSERT INTO campus.tbl_zeitsperre (zeitsperretyp_kurzbz,mitarbeiter_uid,bezeichnung,vondatum,vonstunde,bisdatum,bisstunde,vertretung_uid,updateamum,updatevon,insertamum,insertvon,erreichbarkeit_kurzbz,freigabeamum,freigabevon) VALUES ('Urlaub', 'gl1', 'Urlaub', '2013-07-22', NULL, '2013-07-26', NULL, NULL,NULL,NULL, now(), 'gl1',NULL,now(), 'kindlm');
	INSERT INTO campus.tbl_zeitsperre (zeitsperretyp_kurzbz,mitarbeiter_uid,bezeichnung,vondatum,vonstunde,bisdatum,bisstunde,vertretung_uid,updateamum,updatevon,insertamum,insertvon,erreichbarkeit_kurzbz,freigabeamum,freigabevon) VALUES ('Urlaub', 'gl1', 'Urlaub', '2013-07-29', NULL, '2013-08-02', NULL, NULL,NULL,NULL, now(), 'gl1',NULL,now(), 'kindlm');
	
	delete from campus.tbl_news where insertvon='gl1';
	delete from campus.tbl_contentsprache where insertvon='gl1';
	delete from campus.tbl_content where insertvon='gl1';	
	";
		
	
	if($db->db_query($qry))
		echo '<font color="green">ERFOLGREICH BEENDET</font>';
	else
		echo '<font color="red">ERROR</font>'.$db->db_last_error();
}
/* if(isset($_POST['reset_abgabe']))
{
	$uid=$_POST['uid'];
	echo '<br />Resetting Abgabetool '.$uid.'...';
	$db = new basis_db();
	
	$qry = "
	DELETE FROM campus.tbl_paabgabe 
	WHERE paabgabe_id IN(SELECT paabgabe_id 
						FROM 
							campus.tbl_paabgabe 
							JOIN lehre.tbl_projektarbeit USING(projektarbeit_id)
						WHERE student_uid='".addslashes($uid)."');
	DELETE FROM lehre.tbl_projektbetreuer WHERE projektarbeit_id IN(SELECT projektarbeit_id FROM lehre.tbl_projektarbeit WHERE student_uid='".addslashes($uid)."');
	DELETE FROM lehre.tbl_projektarbeit WHERE student_uid='".addslashes($uid)."';
	";
	
	if($db->db_query($qry))
		echo '<font color="green">done</font>';
	else
		echo '<font color="red">error</font>'.$db->db_last_error();
}*/
?>