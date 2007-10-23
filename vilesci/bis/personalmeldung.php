<?php
/* Copyright (C) 2007
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */


require('../config.inc.php');
require('../../include/studiensemester.class.php');
require('../../include/datum.class.php');

$conn=pg_connect(CONN_STRING) or die("Connection zur Portal Datenbank fehlgeschlagen");
$conn_fas=pg_connect(CONN_STRING_FAS) or die("Connection zur FAS Datenbank fehlgeschlagen");



$error_log='';
$error_log1='';
$error_log_all="";
$stgart='';
$fehler='';
$v='';


$qry="SELECT * FROM public.tbl_mitarbeiter JOIN public.tbl_benutzer ON(mitarbeiter_uid=uid) 
	JOIN public.tbl_person USING(person_id) 
	JOIN bis.tbl_bisverwendung USING (mitarbeiter_uid)  
	WHERE aktiv AND bismelden AND (ende>now() OR ende IS NULL)  
	";
/*
	bis.tbl_bisfunktion USING(bisverwendung_id) 
	bis.tbl_entwicklungsteam USING(mitarbeiter_uid) 
	public.tbl_benutzerfunktion
*/
?>