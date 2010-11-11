<?php
/**
 * Resettet den Usability Test auf der FHComplete Demoseite
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
<form action="'.$_SERVER['PHP_SELF'].'" method="POST">
<input type="submit" name="reset" value="Reset starten">
</form>
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
';

if(isset($_POST['reset']))
{
	echo '<br />Resetting Usability Test ... ';
	$db = new basis_db();
	
	$qry = "
	delete from public.tbl_konto where person_id in (6008,5821,22186,18441,17461,12749,21728,21297,17905,21768,1671,18572,16215,17469,1211,7938,16678,22731,15892,15732,15299,18396,752,5859,16370,18749,15812,23369);
	delete from public.tbl_konto where person_id=2656 and betrag='363.36';
	update lehre.tbl_lehrveranstaltung set sort=NULL where studiengang_kz=10002 and semester=2;
	delete from lehre.tbl_lehreinheitmitarbeiter where lehreinheit_id=26208;
	delete from lehre.tbl_lehreinheitgruppe where lehreinheit_id=26260;
	INSERT INTO lehre.tbl_lehreinheitgruppe (lehreinheitgruppe_id, lehreinheit_id, studiengang_kz, semester, verband, gruppe, gruppe_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id) VALUES ('23100', '26260', '10001', '1', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
	delete from public.tbl_prestudentstatus where prestudent_id=(select prestudent_id from public.tbl_prestudent where person_id=4095 and studiengang_kz=10001); delete from public.tbl_prestudent where person_id=4095 and studiengang_kz=10001;
	delete from public.tbl_prestudentstatus where prestudent_id=(select prestudent_id from public.tbl_prestudent where person_id=(select person_id from public.tbl_person where nachname='Midler'));
	delete from public.tbl_prestudent where person_id=(select person_id from public.tbl_person where nachname='Midler');
	delete from public.tbl_adresse where person_id=(select person_id from public.tbl_person where nachname='Midler');
	delete from public.tbl_person where nachname='Midler';
	";	
	
	if($db->db_query($qry))
		echo '<font color="green">done</font>';
	else
		echo '<font color="red">error</font>'.$db->db_last_error();
}
if(isset($_POST['reset_abgabe']))
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
}
?>