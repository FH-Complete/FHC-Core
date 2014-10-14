<?php
/* Copyright (C) 2011 FH Technikum-Wien
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
 *          Karl Burkhart		< burkhart@technikum-wien.at >
 */
/**
 * Seite zur Wartung der Statistiken
 */
require_once('../../config/vilesci.config.inc.php');		
require_once('../../include/statistik.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/berechtigung.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');
		
$user = get_uid();
	
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
	
if(!$rechte->isBerechtigt('basis/statistik'))
	die('Sie haben keine Berechtigung fuer diese Seite');

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Statistik - Details</title>
	<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>
<body>

<?php
	$action = (isset($_GET['action'])?$_GET['action']:'new');
	$statistik_kurzbz = (isset($_REQUEST['statistik_kurzbz'])?$_REQUEST['statistik_kurzbz']:'');
	$statistik = new statistik();
	
	if($action=='save')
	{
		$statistik_kurzbz_orig = (isset($_POST['statistik_kurzbz_orig'])?$_POST['statistik_kurzbz_orig']:die('Statistik_kurzbz_orig fehlt'));
		$bezeichnung = (isset($_POST['bezeichnung'])?$_POST['bezeichnung']:die('Bezeichnung fehlt'));
		$url = (isset($_POST['url'])?$_POST['url']:die('URL fehlt'));
		$sql = (isset($_POST['sql'])?$_POST['sql']:die('SQL fehlt'));
		$gruppe = (isset($_POST['gruppe'])?$_POST['gruppe']:die('Gruppe fehlt'));
		$content_id = (isset($_POST['content_id'])?$_POST['content_id']:die('ContentID fehlt'));
		$php = (isset($_POST['php'])?$_POST['php']:die('PHP fehlt'));
		$r = (isset($_POST['r'])?$_POST['r']:die('R fehlt'));
		$publish = (isset($_POST['publish'])?$_POST['publish']:die('Publish fehlt'));
		$new = (isset($_POST['new'])?$_POST['new']:die('New fehlt'));
		$berechtigung_kurzbz = (isset($_POST['berechtigung_kurzbz'])?$_POST['berechtigung_kurzbz']:die('Berechtigungkurzbz fehlt'));
		
		if($new=='true')
		{
			$statistik->insertamum=date('Y-m-d H:i:s');
			$statistik->insertvon = $user;
			$statistik->new = true;
		}
		else
		{
			if(!$statistik->load($statistik_kurzbz_orig))
				die($statistik->errormsg);
				
			$statistik->new=false;
		}
		
		$statistik->statistik_kurzbz=$statistik_kurzbz;
		$statistik->statistik_kurzbz_orig = $statistik_kurzbz_orig;
		$statistik->bezeichnung = $bezeichnung;
		$statistik->url = $url;
		$statistik->sql = $sql;
		$statistik->gruppe = $gruppe;
		$statistik->content_id = $content_id;
		$statistik->php = $php;
		$statistik->r = $r;
		$statistik->publish = $publish;
		$statistik->updateamum = date('Y-m-d H:i:s');
		$statistik->updatevon = $user;
		$statistik->berechtigung_kurzbz = $berechtigung_kurzbz;
		
		if($statistik->save())
		{		
			echo '<span class="ok">Daten erfolgreich gespeichert</span>';
			echo "<script type='text/javascript'>\n";
			echo "	parent.uebersicht_statistik.location.href='statistik_uebersicht.php';";
			echo "</script>\n";
			$action='update';
		}
		else
		{
			$action='new';
			echo '<span class="error">'.$statistik->errormsg.'</span>';
		}
	}

	echo '<fieldset>';
	switch($action)
	{
		case 'new':
			echo '<legend>Neu</legend>';
			$new = 'true';
			break;
		case 'update':
			if(!$statistik->load($statistik_kurzbz))
				die($statistik->errormsg);
			echo "<legend>Bearbeiten - $statistik_kurzbz</legend>";
			$new = 'false';
			break;
		default:
			die('Invalid Action');
			break;
	}
	echo '<form action="'.$_SERVER['PHP_SELF'].'?action=save" method="POST">';
	echo '<input type="hidden" name="new" value="'.$new.'">';
	echo '<input type="hidden" name="statistik_kurzbz_orig" value="'.$statistik->statistik_kurzbz.'">';
	echo '<table>';
	echo '<tr>';
	echo '   <td>Kurzbz</td>';	
	echo '   <td><input type="text" name="statistik_kurzbz" size="50" maxlength="64" value="'.$statistik->statistik_kurzbz.'"></td>';
	echo '	 <td></td>';
	echo '   <td>Gruppe</td>';
	echo '   <td><input type="text" name="gruppe" value="'.$statistik->gruppe.'"></td>';
	echo '</tr>';
	echo '<tr>';
	echo '   <td>Bezeichnung</td>';
	echo '   <td><input type="text" name="bezeichnung" size="80" maxlength="256" value="'.$statistik->bezeichnung.'"></td>';
	echo '   <td></td>';
	echo '   <td>ContentID</td>';
	echo '   <td><input type="text" name="content_id" value="'.$statistik->content_id.'"></td>';
	echo '</tr>';
	echo '<tr>';
	echo '   <td>URL</td>';
	echo '   <td><input type="text" name="url" size="80" maxlength="512" value="'.$statistik->url.'"></td>';
	echo '   <td></td>';
	echo '   <td>Berechtigung</td>';
	echo '   <td>';
	$berechtigung = new berechtigung();
	$berechtigung->getBerechtigungen();
	echo '<select name="berechtigung_kurzbz">';
	echo '<option value="">-- keine Auswahl --</option>';
	foreach($berechtigung->result as $row)
	{
		if($row->berechtigung_kurzbz==$statistik->berechtigung_kurzbz)
			$selected='selected';
		else
			$selected='';
		echo '<option value="'.$row->berechtigung_kurzbz.'" '.$selected.'>'.$row->berechtigung_kurzbz.'</option>';
	}
	echo '</select>';
	//<input type="text" name="berechtigung_kurzbz" value="'.$statistik->berechtigung_kurzbz.'">
	echo '</td>';
	echo '</tr>';
	echo '<tr valign="top">';
	echo '   <td rowspan="3">SQL</td>';
	echo '   <td rowspan="3"><textarea name="sql" cols="60" rows="5">'.$statistik->sql.'</textarea></td>';
	echo '   <td></td>';
	echo '   <td>R</td>';
	echo '   <td><input type="text" name="r" value="'.$statistik->r.'"></td>';
	echo '</tr>';
	echo '<tr valign="top">';
	echo '   <td></td>';
	echo '   <td>PHP</td>';
	echo '   <td><input type="text" name="php" value="'.$statistik->php.'"></td>';
	echo '</tr>';
	echo '<tr valign="top">';
	echo '   <td></td>';
	echo '   <td>Publish</td>';
	echo '   <td><input type="text" name="publish" value="'.$statistik->publish.'"></td>';
	echo '</tr>';
	
	echo '<tr>';
	echo '   <td></td>';
	echo '   <td></td>';
	echo '   <td></td>';
	echo '   <td></td>';
	echo '   <td><input type="submit" value="Speichern" name="save"></td>';
	echo '</tr>';
	echo '</table>';
	echo '</form>';
	
	echo '</fieldset>';
?>
</body>
</html>
