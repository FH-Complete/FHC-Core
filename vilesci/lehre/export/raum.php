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
		
		include('../../../include/functions.inc.php');


	header("Content-disposition: filename=raum.txt");
	header("Content-type: application/octetstream");
	header("Pragma: no-cache");
	header("Expires: 0");
	
	// doing some DOS-CRLF magic...
	$crlf="\n";
	$client=getenv("HTTP_USER_AGENT");
	if (ereg('[^(]*\((.*)\)[^)]*',$client,$regs)) 
	{
		$os = $regs[1];
		// this looks better under WinX
		if (eregi("Win",$os)) $crlf="\r\n";
	}


	$sql_query="SELECT * FROM public.tbl_ort ORDER BY ort_kurzbz";
	//echo $sql_query;
	if(!($result=$db->db_query($sql_query)))
		die($db->db_last_error());
	$anz=$db->db_num_rows($result);
	for  ($j=0; $j<$anz; $j++)
	{
		$row=$db->db_fetch_object($result, $j);
		echo '"'.$row->ort_kurzbz.'","'.$row->bezeichnung.'",,,,,,'.$row->max_person.',,,,,,,,,,'.$crlf;
	}
?>