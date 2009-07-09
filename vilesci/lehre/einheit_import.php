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
	
	$uid=(isset($_REQUEST['uid']) ? $_REQUEST['uid'] :get_uid() );			
	$einheit=(isset($_REQUEST['einheit']) ? $_REQUEST['einheit'] :'' );			
	$einheit_id=(isset($_REQUEST['einheit_id']) ? $_REQUEST['einheit_id'] :'' );			
	$student_id=(isset($_REQUEST['student_id']) ? $_REQUEST['student_id'] :'' );			
			
?>		
<HTML>
<BODY>
<?php
	$field=file($userfile);
	$anz=count($field);
	for ($i=0;$i<$anz;$i++)
	{
		//echo $field[$i].'<br>';
		$enduid=strpos($field[$i],'"',1);
		//echo $enduid.'<br>';
		$uid=substr($field[$i],1,$enduid-1);
		//echo $uid.'<br>';
		$begineinh=strpos($field[$i],'"',$enduid+2)+1;
		//echo $begineinh.'<br>';
		$endeinh=strpos($field[$i],'"',$begineinh);
		//echo $endeinh.'<br>';
		$einheit=substr($field[$i],$begineinh,$endeinh-$begineinh);
		//echo $einheit.'<br>';
		
		$sql_query="SELECT * FROM einheit WHERE kurzbz='$einheit'";
		$result=$db->db_query($sql_query);
		$rows=$db->db_num_rows($result);
		if ($rows==0)
		{
			$result_ins=$db->db_query("INSERT INTO einheit (kurzbz) VALUES ('$einheit')");
			if(!$result_ins) 
				error($db->db_last_error() );
			$sql_query="SELECT * FROM einheit WHERE kurzbz='$einheit'";
			$result=$db->db_query($sql_query);
		}
		$row=$db->_fetch_object($result,0);
		$einheit_id=$row->id;


		$sql_query="SELECT * FROM student WHERE uid='$uid'";
		//echo $sql_query.'<BR>';
		$result=$db->db_query($sql_query);
		if(!$result) 
				error($db->db_last_error());
	
		$rows=$db->db_num_rows($result);
		if ($rows==0)
			die("Student $uid not found!");
		$row=$db->db_fetch_object($result,0);
		
		
		$student_id=$row->id;
		
		$sql_query="SELECT * FROM einheitstudent WHERE einheit_id=$einheit_id AND student_id=$student_id";
	
		$rows=0;
		if ($result=$db->db_query($sql_query))
				$rows=$db->db_num_rows($result);
		if ($rows==0)
		{
			$result_ins=$db->db_query("INSERT INTO einheitstudent (einheit_id, student_id) VALUES ($einheit_id, $student_id)");
			if(!$result_ins) 
				error($db->db_last_error());
			$result=$db->db_query($sql_query);
		}		
	}
?>
Finished <BR>
<A href="einheit_menu.php">Zur&uuml;ck</A> 
</BODY>
</HTML>