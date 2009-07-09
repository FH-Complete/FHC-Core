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
			
	require_once('../../include/functions.inc.php');
	require_once('../../include/person.class.php');
	require_once('../../include/benutzer.class.php');
	require_once('../../include/mitarbeiter.class.php');
	require_once('../../include/studiengang.class.php');
	require_once('../../include/resturlaub.class.php');
	require_once('../../include/zeitsperre.class.php');

	$user = get_uid();
	$reloadstr = "";  // neuladen der liste im oberen frame
	$errorstr='';
	$htmlstr='';

	$zeitsperre_arr=array();
	$vertretung_arr=array();
	$erreichbarkeit_arr=array();
	$freigabe_arr=array();


	if(isset($_POST["schick"]) && $_POST['uid']!='')
	{
		$zs = new zeitsperre();
		
		if(isset($_POST['zeitsperre_id']) && $_POST['zeitsperre_id']!='')
		{
			if($zs->load($_POST['zeitsperre_id']))
			{
				$zs->new=false;
			}
		}
		else
		{
			$zs->new=true;
			$zs->insertamum=date('Y-m-d H:i:s');
			$zs->insertvon = $user;
		}
		$zs->zeitsperretyp_kurzbz = $_POST['zeitsperretyp_kurzbz'];
		$zs->bezeichnung = $_POST['bezeichnung'];
		$zs->mitarbeiter_uid = $_POST['uid'];
		$zs->vondatum = $_POST['vondatum'];
		$zs->vonstunde = $_POST['vonstunde'];
		$zs->bisdatum = $_POST['bisdatum'];
		$zs->bisstunde = $_POST['bisstunde'];
		$zs->vertretung_uid  = $_POST['vertretung_uid'];
		$zs->erreichbarkeit_kurzbz = $_POST['erreichbarkeit_kurzbz'];
		$zs->freigabeamum = $_POST['freigabeamum'];
		$zs->freigabevon = $_POST['freigabevon'];
		$zs->updateamum = date('Y-m-d H:i:s');
		$zs->updatevon = $user;
		if(!$zs->save())
		
			$errorstr = "Fehler beim Speichern der Daten: $zs->errormsg";
		else
		{
			$reloadstr .= "<script type='text/javascript'>\n";
			$reloadstr .= "parent.uebersicht.location.href='resturlaub.php?type=edit&uid=$zs->mitarbeiter_uid';";
			$reloadstr .= " window.location.href='".$_SERVER['PHP_SELF']."?zeitsperre_id=$zs->zeitsperre_id&neu=true';";
			$reloadstr .= "</script>\n";
		}
		
	}
		


	$qry = "SELECT * FROM campus.tbl_zeitsperretyp ORDER BY zeitsperretyp_kurzbz";
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			$zeitsperre_arr[] = $row->zeitsperretyp_kurzbz;
		}
	}

	$qry = "SELECT * FROM campus.vw_mitarbeiter WHERE uid not LIKE '\\\_%' ORDER BY nachname, vorname";
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			$vertretung_arr[] = $row->uid;
		}
	}
	
	$qry = "SELECT * FROM campus.tbl_erreichbarkeit ORDER BY erreichbarkeit_kurzbz";
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			$erreichbarkeit_arr[] = $row->erreichbarkeit_kurzbz;
		}
	}

	$qry = "SELECT * FROM campus.vw_mitarbeiter WHERE uid not LIKE '\\\_%' ORDER BY nachname, vorname";
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			$freigabe_arr[] = $row->uid;
		}
	}
	
	if (isset($_REQUEST['zeitsperre_id']) || isset($_REQUEST['neu']))
	{
		$zs = new zeitsperre();
		if (isset($_REQUEST['zeitsperre_id']))
		{
			$zsid = $_REQUEST['zeitsperre_id'];
			if (!$zs->load($zsid))
				$htmlstr .= "<br><div class='kopf'>Zeitsperre <b>".$zsid."</b> existiert nicht</div>";
		}
		else 
		{
			$zs->mitarbeiter_uid=$_REQUEST['uid'];
		}
		$htmlstr .= "<br><div class='kopf'>Zeitsperre ".(!isset($zs->zeitsperre_id)?'':$zs->zeitsperre_id)."</div>\n";
		$htmlstr .= "<form accept-charset='UTF-8' action='resturlaub_details.php' method='POST'>\n";
		$htmlstr .= "<input type='hidden' name='zeitsperre_id' value='".$zs->zeitsperre_id."'>\n";
		$htmlstr .= "<input type='hidden' name='uid' value='".$zs->mitarbeiter_uid."'>\n";
		$htmlstr .= "<table class='detail' style='padding-top:10px;'>\n";
		$htmlstr .= "<tr></tr>\n";

		$htmlstr .= "	<tr>\n";
		$htmlstr .= "		<td>Typ</td>";
		$htmlstr .= "		<td><select name='zeitsperretyp_kurzbz'>\n";
		$htmlstr .= "<option value=''>---ausw&auml;hlen---</option>";
		foreach ($zeitsperre_arr as $zeitsperre)
		{
			if ($zs->zeitsperretyp_kurzbz == $zeitsperre)
				$sel = " selected";
			else
				$sel = "";
			$htmlstr .= "	<option value='".$zeitsperre."' ".$sel.">".$zeitsperre."</option>";
		}
		$htmlstr .= "		</select></td>";
		$htmlstr .= "		<td>Bezeichnung</td>";
		$htmlstr .= "		<td colspan='3'><input type='text' name='bezeichnung' value='".$zs->bezeichnung."' size='32' maxlength='32'></td>\n";
		$htmlstr .= "	</select></td>";
		$htmlstr .= "</tr>";
		$htmlstr .= "<tr>";
		$htmlstr .= "		<td>Vertretung</td>";
		$htmlstr .= "		<td><select name='vertretung_uid'>\n";
		$htmlstr .= "<option value=''>---ausw&auml;hlen---</option>";
		foreach ($vertretung_arr as $vertretung)
		{
			if ($zs->vertretung_uid == $vertretung)
				$sel = " selected";
			else
				$sel = "";
			$htmlstr .= "	<option value='".$vertretung."' ".$sel.">".$vertretung."</option>";
		}
		$htmlstr .= "	</select></td>";
		$htmlstr .= "		<td>Erreichbarkeit</td>";
		$htmlstr .= "		<td><select name='erreichbarkeit_kurzbz'>\n";
		$htmlstr .= "<option value=''>---ausw&auml;hlen---</option>";
		foreach ($erreichbarkeit_arr as $erreichbarkeit)
		{
			if ($zs->erreichbarkeit_kurzbz == $erreichbarkeit)
				$sel = " selected";
			else
				$sel = "";
			$htmlstr .= "	<option value='".$erreichbarkeit."' ".$sel.">".$erreichbarkeit."</option>";
		}
		$htmlstr .= "	</select></td>";
		$htmlstr .= "</tr>";
		$htmlstr .= "<tr>";
		$htmlstr .= "	<td>Von-Datum</td>";
		$htmlstr .= "	<td><input type='text' name='vondatum' value='$zs->vondatum' maxlength='11'></td>";
		$htmlstr .= "	<td>Von-Stunde</td>";
		$htmlstr .= "	<td><input type='text' name='vonstunde' value='$zs->vonstunde' maxlength='5'></td>";
		$htmlstr .= "</tr><tr>";
		$htmlstr .= "	<td>Bis-Datum</td>";
		$htmlstr .= "	<td><input type='text' name='bisdatum' value='$zs->bisdatum' maxlength='11'></td>";
		$htmlstr .= "	<td>Bis-Stunde</td>";
		$htmlstr .= "	<td><input type='text' name='bisstunde' value='$zs->bisstunde' maxlength='5'></td>";
		$htmlstr .= "</tr>";
		$htmlstr .= "<tr><td>Freigabedatum</td>";
		$htmlstr .= "	<td><input type='text' name='freigabeamum' value='$zs->freigabeamum' maxlength='15'></td>";
		$htmlstr .= "		<td>Freigabe</td>";
		$htmlstr .= "		<td><select name='freigabevon'>\n";
		$htmlstr .= "<option value=''>---ausw&auml;hlen---</option>";
		foreach ($freigabe_arr as $freigabe)
		{
			if ($zs->freigabevon == $freigabe)
				$sel = " selected";
			else
				$sel = "";
			$htmlstr .= "	<option value='".$freigabe."' ".$sel.">".$freigabe."</option>";
		}
		$htmlstr .= "	</select></td>";
		$htmlstr .= "</tr><tr>";		
		$htmlstr .= "	<td></td>";
		$htmlstr .= "	<td><input type='submit' value='Speichern' name='schick'></td>";

		$htmlstr .= "	</tr>\n";
		$htmlstr .= "</table>\n";
		$htmlstr .= "<br>\n";
		$htmlstr .= "</form>\n";

	}
	$htmlstr .= "<div class='inserterror'>".$errorstr."</div>\n";
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Zeitsperren - Details</title>
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<script src="../../include/js/mailcheck.js"></script>
<script src="../../include/js/datecheck.js"></script>
<script type="text/javascript">

</script>
</head>
<body style="background-color:#eeeeee;">

<?php
	echo $htmlstr;
	echo $reloadstr;
?>

</body>
</html>