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
	//Stundenplandaten ermitteln welche mehrfach vorkommen
	$sql_query="DELETE FROM lehre.tbl_stundenplan WHERE id=$id";
#	echo $sql_query."<br>";
	$num_rows=0;
	if ($result=$db->db_query($sql_query))
			$num_rows=$db->db_affected_rows($result);
	else
		die($db->db_last_error().' <a href="javascript:history.back()">Zur&uuml;ck</a>');	
?>

<html>
<head>
<title>Stundenplan Check Details</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<LINK rel="stylesheet" href="../../../skin/vilesci.css" type="text/css">
</head>
<body>
<H1>Mehrfachbelegungen L&ouml;schen</H1>
<?php 
if ($num_rows)
	echo "Datensatz wurde erfolgreich gel&ouml;scht!";
else
	echo "Es ist ein Fehler aufgetreten! ".$db->db_last_error();
?><br>
<a href="stdplan_check.php"><br>
<br>
Zur&uuml;ck zu &Uuml;bersicht</a>
</body>
</html>