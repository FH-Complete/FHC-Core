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
	if(isset($_GET['uid']) && isset($_GET['del']))
	{
		//$sql_query = "Delete from tbl_person where uid='".$_GET["uid"]."';";
		//echo $sql_query;
		echo 'Loeschen noch nicht implementiert';
	}

	if(isset($_GET['fix']) && isset($_GET['uid']))
	{
		$sql_query = "UPDATE public.tbl_mitarbeiter SET fixangestellt=". ($_GET['fix']=='t'?'false':'true') ." WHERE mitarbeiter_uid='".addslashes($_GET['uid'])."'";
		//echo $sql_query;
		if(!($erg=$db->db_query($sql_query)))
			die($db->db_last_error());
		}

	if(isset($_GET['lek']) && isset($_GET['uid']))
	{
		$sql_query = "UPDATE public.tbl_mitarbeiter SET lektor=". ($_GET['lek']=='t'?'false':'true') ." WHERE mitarbeiter_uid='".addslashes($_GET['uid'])."'";
		//echo $sql_query;
		if(!($erg=$db->db_query($sql_query)))
				die($db->db_last_error());

	}
?>
<html>
<head>
<title>Mitarbeiter Uebersicht</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<script language="JavaScript" type="text/javascript">
function confdel()
{
	if(confirm("Diesen Datensatz wirklick loeschen?"))
	  return true;
	return false;
}
</script>
</head>

<body class="background_main">
<h1>Mitarbeiter &Uuml;bersicht</h1><br>


<?php
	$qry = "SELECT * FROM campus.vw_mitarbeiter";
	if(isset($order))
		$qry .= " ORDER BY $order";
	else
		$qry .= " ORDER BY nachname, vorname";

	if($result = $db->db_query($qry))
	{
		echo "<table class='liste'>";
		echo "<tr class='liste'><th><a href='lektor_uebersicht.php?order=uid'>UID</a></th><th>Titel</th><th>Vorname</th><th><a href='lektor_uebersicht.php?order=nachname'>Nachname</a></th><th><a href='lektor_uebersicht.php?order=fixangestellt DESC, nachname'>Fix</a></th><th>Lkt</th><th>Raum</th><th>Standort</th><th>Tel</th><th>eMail</th><th colspan='3'>Aktion</th></tr>";

		for ($i=0; $row=$db->db_fetch_object($result); $i++)
		{
			echo "<tr class='liste". ($i%2) ."'>";
			if((isset($fix) || isset($lek))&& isset($uid) && $uid==$row->uid) //Anker setzen
				echo "<td nowrap>".$row->uid."<a name='anker1'></a></td>";
			else
				echo "<td nowrap>".$row->uid."</td>";

			echo "<td nowrap>".$row->titelpre."</td>";
			echo "<td nowrap>".$row->vorname."</td>";
			echo "<td nowrap>".$row->nachname."</td>";
			echo "<td nowrap><a href='lektor_uebersicht.php?uid=".$row->uid."&fix=".$row->fixangestellt . (isset($order)?'&order='.$order:'') ."'><img src='../../skin/images/".($row->fixangestellt=='t'?'true':'false').".gif'></a></td>";
			echo "<td nowrap><a href='lektor_uebersicht.php?uid=".$row->uid."&lek=".$row->lektor . (isset($order)?'&order='.$order:'') ."'><img src='../../skin/images/".($row->lektor=='t'?'true':'false').".gif'></a></td>";

			echo "<td nowrap>".$row->ort_kurzbz."</td>";
			echo "<td nowrap>".$row->standort_kurzbz."</td>";
			echo "<td nowrap>".$row->telefonklappe."</td>";
			//echo "<td nowrap><a href='#' onClick='javascript:document.form1.uid=".$lektoren[$i]->uid.";document.form1.fix=".$lektoren[$i]->fixangestellt .";document.form1.order=". (isset($order)?$order:'') .";'><img src='../../skin/images/".$lektoren[$i]->fixangestellt.".gif'></a></td>";
			//echo "<td nowrap><a href='#' onClick='javascript:document.form1.uid=".$lektoren[$i]->uid.";document.form1.fix=".$lektoren[$i]->fixangestellt .";document.form1.order=". (isset($order)?$order:'') .";'lek=".$lektoren[$i]->lektor . (isset($order)?'&order='.$order:'') ."'><img src='../../skin/images/".$lektoren[$i]->lektor.".gif'></a></td>";

			$email=$row->uid.'@technikum-wien.at';
			echo "<td nowrap><a href='mailto:$email'>$email</a></td>";
			echo "<td nowrap class='button'><a href='lektor_edit.php?id=".$row->uid."'>Edit</a></td>";
			echo "<td nowrap class='button'>";
			if ($row->lektor)
			{
				echo "<a href='zeitwunsch.php?uid=".$row->uid."&vorname=".rawurlencode($row->vorname)."&nachname=".rawurlencode($row->nachname)."&titel=".rawurlencode($row->titelpre)." class='linkblue'>Zeitwunsch</a>";
			}
			echo "</td>";
			echo "<td nowrap class='button'><a href='lektor_uebersicht.php?del=1&uid=".$row->uid."' onClick='javascript: return confdel();'>Delete</a></td>";
			echo "</tr>";
		}
		echo "</table>";
	}
	else
		echo "Fehler beim laden der Mitarbeiter: ".$db->db_last_error();

	if(isset($_GET['fix']) || isset($_GET['lek'])) //Zum Anker hüpfen
	{
		echo "<script language='JavaScript'>this.location.hash='#anker1'</script>";
	}
?>

</body>
</html>
