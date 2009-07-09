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
 
/*
Entfernen (doppelter) Interessenten
*/


		require_once('../../config/vilesci.config.inc.php');
		require_once('../../include/basis_db.class.php');
		if (!$db = new basis_db())
				die('Es konnte keine Verbindung zum Server aufgebaut werden.');
			
		require_once('../../include/functions.inc.php');


function myaddslashes($var)
{
	return ($var!=''?"'".addslashes($var)."'":'null');
}

$msg='';


//prestudent_id
if (isset($_GET['prestudent']) || isset($_POST['prestudent']))
{
	$prestudent=(isset($_GET['prestudent'])?$_GET['prestudent']:$_POST['prestudent']);
}
else 
{
	$prestudent='';
}

//person_id
if (isset($_GET['person']) || isset($_POST['person']))
{
	$person=(isset($_GET['person'])?$_GET['person']:$_POST['person']);
}
else 
{
	$person='';
}
if($person!='' && $prestudent!='')
{
	$qry="SELECT * FROM public.tbl_prestudent WHERE person_id=".$person.";";
	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>1)
		{
			$q2="SELECT * FROM public.tbl_prestudent WHERE person_id=".$person." AND prestudent_id=".$prestudent.";";
			if($result2 = $db->db_query($q2))
			{
				if($db->db_num_rows($result2)<1)
				{
					//kein prestudent mit eingegebener person_id und prestudent_id gefunden
					$msg="Die Eingaben passen nicht zusammen!";
				}
				else 
				{
					$q3="SELECT * FROM public.tbl_prestudentstatus WHERE prestudent_id=".$prestudent." AND status_kurzbz='Interessent';";
					if($result3 = $db->db_query($q3))
					{
						if($db->db_num_rows($result3)==1)
						{
							//mehrere prestudenten an diesem studenten => nur prestudentrolle und prestudent werden gelöscht
							$del="DELETE FROM public.tbl_prestudentstatus WHERE prestudent_id=".$prestudent." AND status_kurzbz='Interessent';DELETE FROM public.tbl_prestudent WHERE prestudent_id=".$prestudent.";";
							if($db->db_query($del))
							{
								$msg="Prestudent mit ID ".$prestudent." und Prestudentrolle Interessent entfernt.<br>".mb_eregi_replace(";DELETE",";<br>DELETE",$del);
							}
							else 
							{
								$msg="Fehler bei: ".$del;
							}
							
						}
						else 
						{
							$msg="Eingabedaten zeigen nicht auf einen Interessenten!";
						}
			 		}
				}	
			}
		}
		elseif($db->db_num_rows($result)==1)
		{
			if($row = $db->db_fetch_object($result))
			{
				if($row->prestudent_id==$prestudent)
				{
					$q3="SELECT * FROM public.tbl_prestudentstatus WHERE prestudent_id=".$prestudent." AND status_kurzbz='Interessent';";
					if($result3 = $db->db_query($q3))
					{
						if($db->db_num_rows($result3)==1)
						{
							//löschen von prestudentrolle, prestudent, adresse, kontakt und person werden gelöscht
							$del="DELETE FROM public.tbl_prestudentstatus WHERE prestudent_id=".$prestudent." AND status_kurzbz='Interessent';DELETE FROM public.tbl_prestudent WHERE prestudent_id=".$prestudent.";DELETE FROM public.tbl_adresse WHERE person_id=".$person.";DELETE FROM public.tbl_kontakt WHERE person_id=".$person.";DELETE FROM public.tbl_person WHERE person_id=".$person.";";
							if($db->db_query($del))
							{
								$msg="Prestudent mit ID ".$prestudent." und Person mit ID ".$person." entfernt.<br>".mb_eregi_replace(";DELETE",";<br>DELETE",$del);	
							}
							else 
							{
								$msg="Fehler bei: ".$del;
							}
							
						}
						else 
						{
							$msg="Eingabedaten zeigen nicht auf einen Interessenten!";
						}
			 		}
				}
				else 
				{
					$msg="Eingaben passen nicht zusammen!";
				}
			}
				
		}
		else 
		{
			//kein prestudent gefunden
			$msg="Keinen Prestudent mit dieser person_id gefunden! Bitte Eingabe überprüfen!";
		}
	}
}
else 
{
	$msg="Bitte beide Parameter eingeben!";
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">

<title>Entfernen von (doppelten) Interessenten</title>
</head>
<body>

<H1>Entfernen von (doppelten) Interessenten</H1>

<?php
echo "<form name='suche' action='interessent_entfernen.php' method='POST'>";
echo "<table><tr>";
echo "<th>prestudent_id</th><th>person_id</th><th>&nbsp;</th>";
echo "<tr>";
echo "<td><input name=\"prestudent\" type=\"text\" value=\"$prestudent\" size=\"16\" maxlength=\"8\"></td>";
echo "<td><input name='person' type='text' value=\"$person\" size='16' maxlength='8'></td>";
echo "<td><input type='submit' value=' entfernen '></td></tr>";
echo "</table></form>";

?>
<br>
<center><h2><?php echo "<span style=\"font-size:0.7em\">".
#substr(CONN_STRING,strpos(CONN_STRING,'dbname=')+7,strpos(CONN_STRING,'user=')-strpos(CONN_STRING,'dbname=')-7).": ".
$msg."</span>"; ?></h2></center>
<br>

</tr>
</table>
</body>
</html>