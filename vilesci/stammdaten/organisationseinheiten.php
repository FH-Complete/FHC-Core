<?php
/* Copyright (C) 2009 Technikum-Wien
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *			Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/benutzerfunktion.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/fachbereich.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/mitarbeiter.class.php');
require_once('../../include/organisationseinheit.class.php');
require_once('../../include/studiengang.class.php');

$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('basis/organisationseinheit'))
	die('Sie haben keine Berechtigung fuer diese Seite');

echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">';

		include('../../include/meta/jquery.php');
		include('../../include/meta/jquery-tablesorter.php');

echo '	<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
		<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
		<script type="text/javascript" src="../../vendor/jquery/sizzle/sizzle.js"></script>
		<script language="Javascript">
			$(function() {
				$("a.ui-widget-content").draggable({revert: true, helper: \'clone\'});
				$("a.ui-widget-content").droppable({
					over: function(event, ui) {
						$(this).css("color","red");
					},
					out: function(event, ui) {
						$(this).css("color","#0086cc");
					},
					drop: function(event, ui) {
						$(this).css("color","#0086cc");
						var kurzbz = encodeURIComponent(ui.helper.context.id);
						var parent_kurzbz = encodeURIComponent(this.id);
						window.location.href="'.$_SERVER['PHP_SELF'].'?action=updateparent&kurzbz="+kurzbz+"&parent_kurzbz="+parent_kurzbz;
					}
				});
			});
		</script>
		<style>
		td
		{
			font-size: small;
			margin: 0px;
			padding: 2px;
		}

		a.Item
		{
			background: none;
			border: none;
		}
		a.Item:hover
		{
			color:#0086cc;
			text-decoration:underline;
			cursor:pointer;
		}
		</style>
	</head>
<body>

<h2>Organisationseinheiten</h2>
<table width="100%">
	<tr>
		<td><span style="font-size: small">Zuordnung kann per Drag&amp;Drop geändert werden!</span><br>
		€...Freigabegrenze hinterlegt (siehe Tooltip)<br>
		<span style="color: green">L</span>...Leitung hinterlegt (siehe Tooltip)</td>
		<td align="right"><a href="'.$_SERVER['PHP_SELF'].'?action=neu">Neue Organisationseinheit anlegen</a></td>
	</tr>
</table>
';

//Parent durch Drag&Drop Updaten
if(isset($_GET['action']) && $_GET['action']=='updateparent')
{
	if(!$rechte->isBerechtigt('basis/organisationseinheit', null, 'suid'))
		die('Sie haben keine Berechtigung fuer diese Aktion');

	$oe = new organisationseinheit($_GET['kurzbz']);

	$oe->oe_parent_kurzbz = $_GET['parent_kurzbz'];
	if(!$oe->save())
	{
		echo 'Fehler:'.$oe->errormsg;
	}
}

//Speichern der Daten
if(isset($_POST['save']))
{
	if(!$rechte->isBerechtigt('basis/organisationseinheit', null, 'suid'))
		die('Sie haben keine Berechtigung fuer diese Aktion');

	if(!isset($_POST['oe_kurzbz'])
	|| !isset($_POST['oe_kurzbz_orig'])
	|| !isset($_POST['oe_parent_kurzbz'])
	|| !isset($_POST['bezeichnung'])
	|| !isset($_POST['organisationseinheittyp_kurzbz']))
	{
		die('Fehler bei der Parameteruebergabe');
	}
	else
	{
		if($_POST['oe_kurzbz_orig']=='')
		{
			$new = true;
			$oe = new organisationseinheit();
		}
		else
		{
			$new = false;
			$oe = new organisationseinheit($_POST['oe_kurzbz_orig']);
		}

		$oe->oe_kurzbz_orig = $_POST['oe_kurzbz_orig'];
		$oe->oe_kurzbz = $_POST['oe_kurzbz'];
		$oe->oe_parent_kurzbz = $_POST['oe_parent_kurzbz'];
		$oe->bezeichnung = $_POST['bezeichnung'];
		$oe->organisationseinheittyp_kurzbz = $_POST['organisationseinheittyp_kurzbz'];
		$oe->aktiv = isset($_POST['aktiv']);
		$oe->mailverteiler = isset($_POST['mailverteiler']);
		$oe->lehre = isset($_POST['lehre']);

		if($oe->save($new))
		{
			echo '<br><b>Daten erfolgreich gespeichert</b>';
		}
		else
		{
			echo '<br><span class="error">Fehler: '.$oe->errormsg.'</span>';
		}
	}
}

//Formular zum Editieren und NEU anlegen anzeigen
if(isset($_GET['action']) && ($_GET['action']=='edit' || $_GET['action']=='neu'))
{
	if(!$rechte->isBerechtigt('basis/organisationseinheit', null, 'suid'))
		die('Sie haben keine Berechtigung fuer diese Aktion');

	if($_GET['action']=='edit')
		$oe = new organisationseinheit($_GET['kurzbz']);
	else
		$oe = new organisationseinheit();

	echo '
	<form action="'.$_SERVER['PHP_SELF'].'" method="POST">
		<table>
			<tr>
				<td>Kurzbz</td><td>';
	echo '<input type="text" name="oe_kurzbz" value="'.$oe->oe_kurzbz.'" />';
	echo '<input type="hidden" name="oe_kurzbz_orig" value="'.$oe->oe_kurzbz.'" />';
	echo '</td>
			</tr>
			<tr>
				<td>Parent</td>
				<td>';

	//Parent DropDown
	echo '<SELECT name="oe_parent_kurzbz">
	<OPTION value="">-- keine Auswahl --</OPTION>';

	$hlp = new organisationseinheit();
	$hlp->getAll();
	foreach($hlp->result as $row)
	{
		if($row->oe_kurzbz==$oe->oe_parent_kurzbz)
			$selected='selected';
		else
			$selected='';

		echo '<OPTION value="'.$row->oe_kurzbz.'" '.$selected.'>'.$row->organisationseinheittyp_kurzbz.' '.$row->bezeichnung.'</OPTION>';
	}
	echo '</SELECT>';
	echo '
				</td>
			</tr>
			<tr>
				<td>Bezeichnung</td><td><input type="text" size="50" name="bezeichnung" value="'.$oe->bezeichnung.'"></td>
			</tr>
			<tr>
				<td>Typ</td><td>';

	//TYP DropDown
	echo '<SELECT name="organisationseinheittyp_kurzbz">';
	$hlp = new organisationseinheit();
	$hlp->getTypen();
	foreach($hlp->result as $row)
	{
		if($row->organisationseinheittyp_kurzbz==$oe->organisationseinheittyp_kurzbz)
			$selected='selected';
		else
			$selected='';

		echo '<OPTION value="'.$row->organisationseinheittyp_kurzbz.'" '.$selected.'>'.$row->organisationseinheittyp_kurzbz.'</OPTION>';
	}
	echo '</SELECT>';

	echo '</td>
			</tr>
			<tr>
				<td>Aktiv</td><td><input type="checkbox" name="aktiv" '.($oe->aktiv?'checked':'').'></td>
			</tr>
			<tr>
				<td>Mailverteiler</td><td><input type="checkbox" name="mailverteiler" '.($oe->mailverteiler?'checked':'').'></td>
			</tr>
			<tr>
				<td>Lehre</td><td><input type="checkbox" name="lehre" '.($oe->lehre?'checked':'').'></td>
			</tr>
			<tr>
				<td>&nbsp;</td><td><input type="submit" name="save" value="Speichern" /></td>
			</tr>
		</table>
	</form>
	';
}

//Benutzerdefiniert Sortierfunktion damit die Eintraege mit
//Kindelementen nach oben sortiert werden
function mysort($a, $b)
{
	if(is_array($a) && is_array($b))
	{
		if(count($a)==count($b))
			return 0;

		if(count($a)>count($b))
			return -1;
		else
			return 1;
	}
	else
	{
		if(is_array($a))
			return 1;
		if(is_array($b))
			return -1;
		else
			return 0;
	}
}

//Uebersicht anzeigen
//Alle obersten Organisationseinheiten holen
$oe = new organisationseinheit();
$oe->getHeads();

echo "\n";
echo '<table style="text-align: center; padding:5;" cellspacing=5 cellpadding=5><tr>';
foreach ($oe->result as $result)
{
	echo '<td valign="top" >';
	$arr = array();
	$arr1 = array();

	//Array mit den Kindelementen erzeugen
	$arr = getChilds($result->oe_kurzbz);
	//Sortieren damit die Eintraege mit Kindern weiter oben stehen
	uasort($arr,'mysort');

	//Parent hinzufuegen
	$arr1[$result->oe_kurzbz] = $arr;
	echo "\n";

	//Anzeigen
	display($arr1);
	echo '</td>';

}
echo "\n";
echo '</tr></table>';

//Liefert die Kindelemente einer Organisationseinheit in
//einem verschachteltem Array zurueck
function getChilds($foo)
{
	$obj = new organisationseinheit();
	$arr = array();
	$arr1 = $obj->getDirectChilds($foo);
	foreach ($arr1 as $value)
		$arr[$value]=array();

	foreach ($arr as $val=>$k)
	{
		$hlp = getChilds($val);
		$arr[$val] = $hlp;
	}

	return $arr;
}

//Zeigt das Array in einer Verschachtelten Tabelle an
function display($arr)
{
	//Wenn eines der Elemente noch Unterelemente hat, dann das Array sortieren, damit
	//die Eintraege mit den Untereintraegen zuerst kommen
	$sort = false;
	foreach ($arr as $row)
	{
		if(count($row)>0)
			$sort=true;
	}
	if($sort)
	{
		uasort($arr,'mysort');
		$style='background-color: #F5F5F5;';
	}
	else
	{
		$style='background-color: #b1b1b1;';
	}

	echo "\n   ";
	echo '<table style="'.$style.'" cellspacing=0 cellpadding=5><tr>';
	$td=false;
	foreach ($arr as $key=>$val)
	{
		$obj = new organisationseinheit();
		$obj->load($key);

		//inaktive OEs farblich markieren
		if($obj->aktiv)
			$aktivstyle='';
		else
			$aktivstyle='color:grey;';

		if(is_array($val) && count($val)>0)
			echo '<td valign="top"><div style="background-color: #b1b1b1; padding: 0px; margin:0px"><br><span style="font-weight: bold;">';
		else
		{
			if(!$td)
			{
				echo '<td nowrap valign="top">';
				$td=true;
			}
			else
				echo '<br>';
		}
		//echo '<span class="ui-widget-content" style=" padding: 0px; margin:0px;'.$aktivstyle.'" >';
		if($obj->organisationseinheittyp_kurzbz=='Institut')
		{
			$bezeichnung = substr($obj->organisationseinheittyp_kurzbz, 0, 1).substr($obj->organisationseinheittyp_kurzbz, -1).' - '.$obj->oe_kurzbz;
		}
		else
		{
			$bezeichnung = substr($obj->organisationseinheittyp_kurzbz, 0, 1).substr($obj->organisationseinheittyp_kurzbz, -1).' - '.$obj->bezeichnung;
		}
		$leitung = array();
		$bnfunktion = new benutzerfunktion();
		$bnfunktion->getBenutzerFunktionen('Leitung', $obj->oe_kurzbz);
		foreach ($bnfunktion->result AS $funktion)
		{
			$mitarbeiter = new mitarbeiter($funktion->uid);
			$leitung[] = $mitarbeiter->vorname.' '.$mitarbeiter->nachname;
		}
		$leitung = array_unique($leitung);
		$title = $obj->organisationseinheittyp_kurzbz.' - '.$obj->bezeichnung;
		if (count($leitung) > 0)
		{
			$bezeichnung .= ' <span style="color: green">L</span>';
			$title .= "\nLeitung(en): ".implode(',',$leitung); // Keep double-quotes to display linebreak in title-attribute
		}
		if ($obj->freigabegrenze != '')
		{
			$bezeichnung .= ' <span title="Freigabegrenze €'.$obj->freigabegrenze.'" style="color: black">€</span>';
			$title .= "\nFreigabegrenze: €".$obj->freigabegrenze; // Keep double-quotes to display linebreak in title-attribute
		}



		echo '<a    href="'.$_SERVER['PHP_SELF'].'?action=edit&kurzbz='.$obj->oe_kurzbz.'" 
					style="'.$aktivstyle.'" 
					class="Item ui-widget-content" 
					title="'.$title.'" 
					id="'.$obj->oe_kurzbz.'">';
		echo $bezeichnung;
		echo '</a>';
		//echo '</span>';
		if(is_array($val) && count($val)>0)
		{
			echo '</span><br><br>';

			display($val);
			echo '</div></td>';
		}
	}
	if($td)
		echo '</td>';
	echo "</tr>\n   </table>";
}

echo '
</body>
</html>';
?>
