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

		require_once('../../config/vilesci.config.inc.php');
		require_once('../../include/basis_db.class.php');
		if (!$db = new basis_db())
				die('Es konnte keine Verbindung zum Server aufgebaut werden.');
			
			
	include('../../include/functions.inc.php');


	foreach ($_REQUEST as $key => $value) 
	{
			 $key=$value; 
	}
	
	$sql_query="INSERT INTO lektor (uid, titel, vornamen, nachname,gebdatum, gebort, gebzeit,emailtw,emailforw,emailalias,kurzbz,teltw,fixangestellt) ";
	$sql_query.="VALUES('$uid','$titel','$vornamen','$nachname','$gebdatum','$gebort','00:00','$emailtw','$emailforw','$emailalias','$kurzbz','$teltw','$fixangestellt')";
	//echo $sql_query;
	if(!($erg=$db->db_query($sql_query)))
		die($db->db_last_error());
?>

<html>
<head>
<title>Lektor Speichern</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../include/styles.css" type="text/css">
</head>
<body class="background_main">
<h4>Lektor Speichern</h4>
Speichern erfolgreich!
</body>
</html>
