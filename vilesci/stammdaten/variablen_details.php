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
require_once('../../include/globals.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/variable.class.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/studiensemester.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');
	
if (!$user = get_uid())
	die('Keine UID gefunden !  <a href="javascript:history.back()">Zur&uuml;ck</a>');
		
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('basis/variable'))
	die('Sie haben keine Berechtigung fuer diese Seite. !  <a href="javascript:history.back()">Zur&uuml;ck</a>');

$reloadstr = "";  // neuladen der liste im oberen frame
$htmlstr = "";
$errorstr = ""; //fehler beim insert
	
$name = isset($_REQUEST['name'])?$_REQUEST['name']:'';
$uid = isset($_REQUEST['uid'])?$_REQUEST['uid']:'';
$wert = isset($_REQUEST['wert'])?$_REQUEST['wert']:'';

if(isset($_GET['standard']))
{
	if($rechte->isBerechtigt('basis/variable', null, 'suid'))
	{		
		$stsem_obj = new studiensemester();
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
			if(!@$db->db_query($qry))
			{
				$error = true;
			}
		}
		
		if($error)
			$errorstr.="Es konnten nicht alle Werte angelegt werden";
		
		$reloadstr .= "<script type='text/javascript' language='JavaScript'>\n";
		$reloadstr .= "	parent.uebersicht.location.href='variablen_uebersicht.php';";
		$reloadstr .= "</script>\n";
	}
	else 
	{
		$errorstr.='Sie haben keine Berechtigung fuer diesen Vorgang';
	}
}
if(isset($_POST["del"]))
{
	if($rechte->isBerechtigt('basis/variable', null, 'suid'))
	{
		if($name!='' && $uid!='')
		{
			$variable = new variable();
			if(!$variable->delete($name, $uid))
				$errorstr .= "Datensatz konnte nicht gel&ouml;scht werden!";
			else 
			{
				$reloadstr .= "<script type='text/javascript' language='JavaScript'>\n";
				$reloadstr .= "	parent.uebersicht.location.href='variablen_uebersicht.php';";
				$reloadstr .= "</script>\n";
			}
		}
		else 
		{
			die('Falsche Parameteruebergabe');
		}
	}
	else 
		$errorstr.='Sie haben keine Berechtigung fuer diesen Vorgang';
}

if(isset($_POST["schick"]))
{
	if($rechte->isBerechtigt('basis/variable', null, 'suid'))
	{
		$variable=new variable();
		
		if($variable->load($uid, $name))
			$variable->new = false;
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
	else 
		$errorstr.='Sie haben keine Berechtigung fuer diesen Vorgang';
}

$qry = "SELECT distinct name FROM public.tbl_variable ORDER BY name";
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$namen[] = $row->name;
	}
}

if ($uid!='')
{
			
	$ben = new benutzer();
	if (!$ben->load($uid))
		$htmlstr .= "<br><div class='kopf'>Benutzer <b>".$uid."</b> existiert nicht</div>";
	else
	{
		$var = new variable();
		$var->getVars($uid);

		$htmlstr .= "<br><div class='kopf'>Variablen für <b>".$uid."</b></div>\n";
		$htmlstr .= "<table style='padding-top:10px;'>\n";
		$htmlstr .= "<tr></tr>\n";
		$htmlstr .= "<tr><td>Name</td><td>Wert</td></tr>\n";
		foreach($var->variables as $v)
		{
			$htmlstr .= "<form action='".$_SERVER['PHP_SELF']."' method='POST'>\n";
			$htmlstr .= "<input type='hidden' name='uid' value='".$v->uid."'>\n";
			$htmlstr .= "	<tr>\n";
			$htmlstr .= "		<td><select name='name'>\n";
			
			foreach($namen as $val)
			{
				if ($val == $v->name)
					$sel = " selected";
				else
					$sel = "";
				$htmlstr .= "				<option value='".$val."' ".$sel.">".$val."</option>";
			}
			$htmlstr .= "		</select></td>\n";
			
			$htmlstr .= "		<td><input type='text' name='wert' value='".$v->wert."' size='15' maxlength='64'></td>\n";
			
			$htmlstr .= "		<td><input type='submit' name='schick' value='speichern'></td>";
			$htmlstr .= "		<td><input type='submit' name='del' value='l&ouml;schen'></td>";
			$htmlstr .= "	</tr>\n";
			$htmlstr .= "</form>\n";
	
		}
		
		
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
		
		$htmlstr .= "</table>\n";
		
		$htmlstr .="<br><br><a href='".$_SERVER['PHP_SELF']."?standard=true&uid=$uid'>Standardwerte anlegen</a>";
	}

}
$htmlstr .= "<div class='inserterror'>".$errorstr."</div>\n";
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Studiengang - Details</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<script src="../../include/js/mailcheck.js"></script>
<script src="../../include/js/datecheck.js"></script>
<script type="text/javascript">

function confdel()
{
	if(confirm("Diesen Datensatz wirklich loeschen?"))
	  return true;
	return false;
}

</script>
</head>
<body style="background-color:#eeeeee;">

<?php
	echo $htmlstr;
	echo $reloadstr;
?>

</body>
</html>