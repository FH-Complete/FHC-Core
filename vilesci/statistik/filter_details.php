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
 * Authors: Christian Paminger < christian.paminger@technikum-wien.at > and
 *          Andreas Moik < moik@technikum-wien.at >.
 */
	require_once('../../config/vilesci.config.inc.php');
	require_once('../../include/globals.inc.php');
	require_once('../../include/functions.inc.php');
	require_once('../../include/benutzerberechtigung.class.php');
	require_once('../../include/filter.class.php');

	if (!$db = new basis_db())
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');

	$user = get_uid();
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);

	if(!$rechte->isBerechtigt('basis/statistik', null, 'suid'))
		die('Sie haben keine Berechtigung fuer diese Seite!');


	$reloadstr = '';  // neuladen der liste im oberen frame
	$htmlstr = '';
	$errorstr = ''; //fehler beim insert
	$sel = '';
	$chk = '';

	$filter = new filter();
	$filter->filter_id		= '';
	$filter->kurzbz			= 'NewFilter';
	$filter->bezeichnung	= 'New Filter';
	$filter->sql			= 'SELECT foo AS value, bar AS name FROM foobar WHERE ... ORDER BY name';
	$filter->valuename		= '';
	$filter->showvalue		= true;
	$filter->type			= 'select';
	$filter->htmlattr		= '';
	$filter->insertvon		= $user;
	$filter->updatevon		= $user;

	if(isset($_POST["save"]) && isset($_REQUEST["filter_id"]))
	{
		if(!$rechte->isBerechtigt('basis/statistik', null, 'suid'))
			die('Sie haben keine Berechtigung fuer diese Aktion');

		if ($_REQUEST["filter_id"]!='')
		{
			if($filter->load($_REQUEST["filter_id"]))
			{
				$filter->updatevon=$user;
			}
			else
				die('Fehlgeschlagen:'.$filter->errormsg);
		}

		$filter->kurzbz = $_POST["kurzbz"];
		$filter->bezeichnung = $_POST["bezeichnung"];
		$filter->valuename = $_POST["valuename"];
		$filter->sql = $_POST["sql"];
		$filter->showvalue = isset($_POST["showvalue"]);
		$filter->type = $_POST["type"];
		$filter->htmlattr = $_POST["htmlattr"];

		// Check if the SQL string contains functions to decrypt data and if there are
		// variables to replace the value of the password (no clear password wanted!)
		if (isSQLDecryptionValid($filter->sql))
		{
			if (!$filter->save())
			{
				$errorstr .= $filter->errormsg;
			}
		}
		else
		{
			$errorstr .= 'It is not possible to store a SQL that contains clear passwords to decrypt data from the DB';
		}

		$reloadstr .= "<script type='text/javascript'>\n";
		$reloadstr .= "	parent.frame_filter_overview.location.href='filter_overview.php';";
		$reloadstr .= "</script>\n";
	}

	if ((isset($_REQUEST['filter_id'])) && ((!isset($_REQUEST['neu'])) || ($_REQUEST['neu']!= "true")) && is_numeric($_REQUEST['filter_id']))
	{
		$filter->load($_REQUEST["filter_id"]);
		if ($filter->errormsg!='')
			die($filter->errormsg);
	}

	if($filter->filter_id > 0)
		$htmlstr .= "<br><div class='kopf'>Filter <b>".$filter->filter_id."</b></div>\n";
	else
		$htmlstr .="<br><div class='kopf'>Neuer Filter</div>\n";

	if($filter->showvalue)
		$chk = "checked";
	else
		$chk = '';
	$htmlstr .= "<form action='filter_details.php' method='POST' name='filterform'>\n";
	$htmlstr .= "	<table class='detail'>\n";
	$htmlstr .= "			<tr>\n";
	$htmlstr .= "				<td>KurzBz</td>\n";
	$htmlstr .= "				<td><input class='detail' type='text' name='kurzbz' size='12' maxlength='24' value='".$filter->kurzbz."' onchange='submitable()'></td>\n";
	$htmlstr .= "				<td>Bezeichnung</td>\n";
	$htmlstr .= "				<td><input class='detail' type='text' name='bezeichnung' size='12' maxlength='24' value='".$filter->bezeichnung."' onchange='submitable()'></td>\n";
	$htmlstr .= "				<td>ValueName</td>\n";
	$htmlstr .= "				<td><input class='detail' type='text' name='valuename' size='12' maxlength='24' value='".$filter->valuename."' onchange='submitable()'></td>\n";
	$htmlstr .= "				<td>Type</td>\n";
	$htmlstr .= "				<td><input class='detail' type='text' name='type' size='12' maxlength='24' value='".$filter->type."' onchange='submitable()'></td>\n";
	$htmlstr .= "				<td>ShowValue</td>\n";
	$htmlstr .= "				<td><input class='detail' type='checkbox' name='showvalue' $chk onchange='submitable()'></td>\n";
	$htmlstr .= "			</tr>\n";
	$htmlstr .= "			<tr>\n";
	$htmlstr .= "				<td valign='top'>SQL</td>\n";
	$htmlstr .= " 				<td colspan='7'><textarea name='sql' cols='95' rows='6' onchange='submitable()'>".$filter->sql."</textarea></td>\n";
	$htmlstr .= "				<td valign='top'>HTML-Attributes</td>\n";
	$htmlstr .= " 				<td colspan='3'><textarea name='htmlattr' cols='70' rows='6' onchange='submitable()'>".$filter->htmlattr."</textarea></td>\n";
	$htmlstr .= "			</tr>\n";
	$htmlstr .= "	</table>\n";
	$htmlstr .= "<br>\n";
	$htmlstr .= "<div align='right' id='sub'>\n";
	$htmlstr .= "	<span id='submsg' style='color:red; visibility:hidden;'>Datensatz ge&auml;ndert!&nbsp;&nbsp;</span>\n";
	$htmlstr .= "	<input type='hidden' name='filter_id' value='".$filter->filter_id."'>";
	$htmlstr .= "	<input type='submit' value='Speichern' name='save'>\n";
	$htmlstr .= "</div>";
	$htmlstr .= "</form>";
	$htmlstr .= "<div class='inserterror'>".$errorstr."</div>"
?>
<!DOCTYPE HTML>
<html>
	<head>
	<title>Filter - Details</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<script type="text/javascript">

	function checkrequired(feld)
	{
		if(feld.value == '')
		{
			feld.className = "input_error";
			return false;
		}
		else
		{
			feld.className = "input_ok";
			return true;
		}
	}

	function submitable()
	{
		required1 = checkrequired(document.filterform.filter_id);

		if(!required1)
		{
			document.filterform.schick.disabled = true;
			document.getElementById("submsg").style.visibility="hidden";
		}
		else
		{
			document.filterform.schick.disabled = false;
			document.getElementById("submsg").style.visibility="visible";
		}
	}
</script>
</head>
<body>

<?php
	echo $htmlstr;
	echo $reloadstr;
?>

</body>
</html>
