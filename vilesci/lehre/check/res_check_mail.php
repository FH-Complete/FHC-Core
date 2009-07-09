<?php
/* Copyright (C) 2006 Technikum-Wien
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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
		require_once('../../../config/vilesci.config.inc.php');
		require_once('../../../include/basis_db.class.php');
		if (!$db = new basis_db())
			die('Es konnte keine Verbindung zum Server aufgebaut werden.');


	$id=(isset($_REQUEST['id']) ? $_REQUEST['id'] :0 );
			
	
	$sql_query="SELECT tbl_reservierung.reservierung_id, tbl_reservierung.datum, tbl_reservierung.stunde, tbl_reservierung.ort_kurzbz AS ortkurzbz, tbl_mitarbeiter.kurzbz AS lektorkurzbz, tbl_reservierung.titel, tbl_reservierung.beschreibung, campus.tbl_reservierung.uid FROM campus.tbl_reservierung, public.tbl_ort, public.tbl_mitarbeiter WHERE campus.tbl_reservierung.reservierung_id=$id AND public.tbl_ort.ort_kurzbz=campus.tbl_reservierung.ort_kurzbz AND public.tbl_mitarbeiter.mitarbeiter_uid=campus.tbl_reservierung.uid";
	//echo $sql_query."<br>";
	$num_rows=0;
	if ($result=$db->db_query($sql_query))
			$num_rows=$db->db_num_rows($result);
	else
		die($db->db_last_error().' <a href="javascript:history.back()">Zur&uuml;ck</a>');
	if ($num_rows==1)
	{	
		$row=$db->db_fetch_object($result,0);
		$text="Dies ist eine automatische eMail!\r\rAufgrund einer Stundenplankollision wurde folgende Reservierung gelöscht:\r\r";
		$text.="Datum:\t$row->datum\rStunde:\t$row->stunde\rOrt:\t$row->ortkurzbz\rTitel:\t$row->titel\r\r";
		$text.="Wir bitten um Verständnis.";
 		$adress=$row->uid.'@technikum-wien.at';
	
		if (mail($adress,"Stundenplankollision",$text,"From: stpl@technikum-wien.at"))
				$sendmail=true;
		else
				$sendmail=false;
	}
	else
		$sendmail=false;
	//Stundenplandaten ermitteln welche mehrfach vorkommen
	$sql_query="DELETE FROM campus.tbl_reservierung WHERE id=$id";
	$num_rows=0;
	if ($result=$db->db_query($sql_query))
			$num_rows=$db->db_affected_rows($result);
	else
		die($db->db_last_error().' <a href="javascript:history.back()">Zur&uuml;ck</a>');			
			
?>

<html>
<head>
<title>Reservierung Check Delete</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<LINK rel="stylesheet" href="../../../skin/vilesci.css" type="text/css">
</head>
<body>
<H1>Mehrfachbelegungen in Reservierung l&ouml;schen</H1>
<?php 
if ($sendmail)
	echo 'Mail wurde verschickt an '.$adress.'!<br>';
else
	echo "Mail konnte nicht verschickt werden!<br>";

if ($num_rows)
		echo "Datensatz wurde erfolgreich gel&ouml;scht!";
else
		echo "Es ist ein Fehler aufgetreten! ".$db->db_last_error();;
?><br>
<a href="res_check.php"><br>
<br>
Zur&uuml;ck zu &Uuml;bersicht</a>
</body>
</html>