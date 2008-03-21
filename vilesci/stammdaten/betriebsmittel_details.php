<?php
/* Copyright (C) 2007 Technikum-Wien
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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
	require_once('../config.inc.php');
	require_once('../../include/globals.inc.php');
	require_once('../../include/functions.inc.php');
	require_once('../../include/benutzerberechtigung.class.php');
	require_once('../../include/variable.class.php');
	require_once('../../include/person.class.php');
	require_once('../../include/benutzer.class.php');
	require_once('../../include/studiensemester.class.php');
	
	if (!$conn = @pg_pconnect(CONN_STRING))
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');

	$user = get_uid();
	$rechte = new benutzerberechtigung($conn);
	$rechte->getBerechtigungen($user);
	
	if(!$rechte->isBerechtigt('admin'))
		die('Sie haben keine Rechte für diese Seite');
	
	$reloadstr = "";  // neuladen der liste im oberen frame
	$htmlstr = "";
	$errorstr = ""; //fehler beim insert
		
	if (isset($_REQUEST['betriebsmittel_id']))
		$betriebsmittel_id =$_REQUEST['betriebsmittel_id'];
	if (isset($_REQUEST['person_id']))
		$person_id =$_REQUEST['person_id'];
	
	$uid = isset($_REQUEST['uid'])?$_REQUEST['uid']:'';
	$wert = isset($_REQUEST['wert'])?$_REQUEST['wert']:'';
	
	if(isset($_GET['standard']))
	{
		$stsem_obj = new studiensemester($conn);
		$stsem = $stsem_obj->getaktorNext();
		
		$qrys = array(
					"Insert into public.tbl_variable(name, uid, wert) values('semester_aktuell','$uid','$stsem');",
					"Insert into public.tbl_variable(name, uid, wert) values('db_stpl_table','$uid','stundenplandev');",
					"Insert into public.tbl_variable(name, uid, wert) values('ignore_kollision','$uid','false');",
					"Insert into public.tbl_variable(name, uid, wert) values('kontofilterstg','$uid','false');",
					"Insert into public.tbl_variable(name, uid, wert) values('ignore_zeitsperre','$uid','false');",
					"Insert into public.tbl_variable(name, uid, wert) values('ignore_reservierung','$uid','false');"
					);
					
		$error = false;
		foreach ($qrys as $qry)
		{
			if(!@pg_query($conn, $qry))
			{
				$error = true;
			}
		}
		
		if($error)
			$errorstr.="Es konnten nicht alle Werte angelegt werden";
		
		$reloadstr .= "<script type='text/javascript'>\n";
		$reloadstr .= "	parent.uebersicht.location.href='variablen_uebersicht.php';";
		$reloadstr .= "</script>\n";
	}
	if(isset($_POST["del"]))
	{
		if($name!='' && $uid!='')
		{
			$variable = new variable($conn);
			
			if(!$variable->delete($name, $uid))
				$errorstr .= "Datensatz konnte nicht gel&ouml;scht werden!";
			else 
			{
				$reloadstr .= "<script type='text/javascript'>\n";
				$reloadstr .= "	parent.uebersicht.location.href='variablen_uebersicht.php';";
				$reloadstr .= "</script>\n";
			}
		}
		else 
		{
			die('Falsche Parameteruebergabe');
		}
	}
	
	if(isset($_POST["schick"]))
	{
		$variable=new variable($conn);
		
		if($variable->load($uid, $name))
			$varialbe->new = false;
		else 
			$variable->new = true;
		
		$variable->name = $name;
		$variable->uid = $uid;
		$variable->wert = $wert;
		
		if ($variable->save())
		{
			$reloadstr .= "<script type='text/javascript'>\n";
			$reloadstr .= "	parent.uebersicht.location.href='variablen_uebersicht.php';";
			$reloadstr .= "</script>\n";
		}
	}
	
	
	if (isset($person_id) && isset($betriebsmittel_id))
	{
		$qry = "SELECT * FROM public.tbl_betriebsmittelperson 
				WHERE betriebsmittel_id=$betriebsmittel_id AND person_id=$person_id";
		if($result = pg_query($conn, $qry))
			$bmp = pg_fetch_object($result);
		$qry = "SELECT * FROM public.tbl_betriebsmittel 
				WHERE betriebsmittel_id=$betriebsmittel_id";
		if($result = pg_query($conn, $qry))
			$bm = pg_fetch_object($result);
	
		$htmlstr .= "<table style='padding-top:10px;'>\n";
		$htmlstr .= "<tr><th>BMid</th><th>betriebsmitteltyp</th><th>nummer</th><th>nummerintern</th>
						<th>beschreibung</th><th>ort_kurzbz</th><th>reservieren</th><th>insertvon</th><th>updateamum</th><th>updatevon</th><th>ext_id</th></tr>\n";
		$htmlstr .= "<form action='' method='POST'>\n";
		$htmlstr .= "	<tr>\n";
		$htmlstr .= "		<td>".$bm->betriebsmittel_id."</td>\n";
		$htmlstr .= "		<td><input type='text' name='wert' value='".$bm->betriebsmitteltyp."' size='15' maxlength='64'></td>\n";
		$htmlstr .= "		<td><input type='text' name='wert' value='".$bm->nummer."' size='15' maxlength='64'></td>\n";
		$htmlstr .= "		<td><input type='text' name='wert' value='".$bm->nummerintern."' size='15' maxlength='64'></td>\n";
		$htmlstr .= "		<td><input type='text' name='wert' value='".$bm->beschreibung."' size='15' maxlength='64'></td>\n";
		$htmlstr .= "		<td><input type='text' name='wert' value='".$bm->ort_kurzbz."' size='15' maxlength='64'></td>\n";
		$htmlstr .= "		<td><input type='text' name='wert' value='".$bm->reservieren."' size='15' maxlength='64'></td>\n";
		$htmlstr .= "		<td>".$bm->insertamum."</td>\n";
		$htmlstr .= "		<td>".$bm->insertvon."</td>\n";
		$htmlstr .= "		<td>".$bm->updateamum."</td>\n";
		$htmlstr .= "		<td>".$bm->updatevon."</td>\n";
		$htmlstr .= "		<td>".$bm->ext_id."</td>\n";
		$htmlstr .= "		<td><input type='submit' name='schick' value='speichern'></td>";
		$htmlstr .= "		<td><input type='submit' name='del' value='l&ouml;schen'></td>";
		$htmlstr .= "	</tr>\n";
		$htmlstr .= "</form>\n";
		$htmlstr .= "</table>\n";

		$htmlstr .= "<table style='padding-top:10px;'>\n";
		$htmlstr .= "<tr><th>BMid</th><th>Pid</th><th>kaution</th><th>ausgegebenam</th><th>retouram</th>
						<th>anmerkung</th><th>insertamum</th><th>insertvon</th><th>updateamum</th><th>updatevon</th><th>ext_id</th></tr>\n";
		$htmlstr .= "<form action='' method='POST'>\n";
		$htmlstr .= "	<tr>\n";
		$htmlstr .= "		<td>".$bmp->betriebsmittel_id."</td>\n";
		$htmlstr .= "		<td>".$bmp->person_id."</td>\n";
		$htmlstr .= "		<td><input type='text' name='wert' value='".$bmp->kaution."' size='15' maxlength='64'></td>\n";
		$htmlstr .= "		<td><input type='text' name='wert' value='".$bmp->ausgegebenam."' size='15' maxlength='64'></td>\n";
		$htmlstr .= "		<td><input type='text' name='wert' value='".$bmp->retouram."' size='15' maxlength='64'></td>\n";
		$htmlstr .= "		<td><input type='text' name='wert' value='".$bmp->anmerkung."' size='15' maxlength='64'></td>\n";
		$htmlstr .= "		<td>".$bmp->insertamum."</td>\n";
		$htmlstr .= "		<td>".$bmp->insertvon."</td>\n";
		$htmlstr .= "		<td>".$bmp->updateamum."</td>\n";
		$htmlstr .= "		<td>".$bmp->updatevon."</td>\n";
		$htmlstr .= "		<td>".$bmp->ext_id."</td>\n";
		$htmlstr .= "		<td><input type='submit' name='schick' value='speichern'></td>";
		$htmlstr .= "		<td><input type='submit' name='del' value='l&ouml;schen'></td>";
		$htmlstr .= "	</tr>\n";
		$htmlstr .= "</form>\n";
		$htmlstr .= "</table>\n";
			
 		/*
			$htmlstr .= "<form action='".$_SERVER['PHP_SELF']."' method='POST'>\n";
			$htmlstr .= "<input type='hidden' name='uid' value='".$uid."'>\n";
			$htmlstr .= "	<tr>\n";
			$htmlstr .= "		<td><select name='name'>\n";
			
			foreach($namen as $val)
			{
				$htmlstr .= "				<option value='".$val."'>".$val."</option>";
			}
			$htmlstr .= "		</select></td>\n";
			
			$htmlstr .= "		<td><input type='text' name='wert' value='' size='15' maxlength='64'></td>\n";
			
			$htmlstr .= "		<td><input type='submit' name='schick' value='neu'></td>";
			$htmlstr .= "	</tr>\n";
			$htmlstr .= "</form>\n";
			
			
			$htmlstr .="<br><br><a href='".$_SERVER['PHP_SELF']."?standard=true&uid=$uid'>Standardwerte anlegen</a>";
		*/
	}
	$htmlstr .= "<div class='inserterror'>".$errorstr."</div>\n";
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Betriebsmitel-Details</title>
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<script src="../../include/js/mailcheck.js"></script>
<script src="../../include/js/datecheck.js"></script>
<script type="text/javascript">

function confdel()
{
	if(confirm("Diesen Datensatz wirklick loeschen?"))
	  return true;
	return false;
}

</script>
</head>
<body style="background-color:#eeeeee;">
<h2>Betriebsmittel - Details</h2>
<?php
	echo $htmlstr;
	echo $reloadstr;
?>

</body>
</html>
