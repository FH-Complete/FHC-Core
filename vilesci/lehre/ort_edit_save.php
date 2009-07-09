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
	if ($type=='save')
	{
		$sql_query="\lo_import $file";
		//$sql_query="UPDATE ort SET picture=lo_import('$file') WHERE id=$id";
		if(!($erg=$db->db_query($sql_query)))
			die($db->db_last_error());
		echo $erg.'<BR>';
	//echo $sql_query;
	}
	
?>

<html>
<head>
<title>Ort &auml;ndern</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../include/styles.css" type="text/css">
</head>

<body class="background_main">
<h4>Ort &auml;ndern</h4>

<FORM name="form1" method="post" action="ort_edit_save.php">
  ID:
  <INPUT type="text" name="id" size="3">
  <INPUT type="file" name="file">
  <INPUT type="submit" name="Abschicken" value="Save">
  <INPUT type="hidden" name="type" value="save">
</FORM>
</body>
</html>
