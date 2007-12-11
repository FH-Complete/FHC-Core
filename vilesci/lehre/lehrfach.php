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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *			Gerald Raab <gerald.raab@technikum-wien.at>.
 */
require_once('../config.inc.php');
require_once('../../include/fachbereich.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/lehrfach.class.php');
require_once('../../include/functions.inc.php');

if(!$conn=pg_pconnect(CONN_STRING))
   die("Konnte Verbindung zur Datenbank nicht herstellen");

$f=new fachbereich($conn);
$f->getAll();
$fachbereiche=$f->result;
$s=new studiengang($conn);
$s->getAll('typ, kurzbz');
$studiengang=$s->result;

$user = get_uid();

if (isset($_GET['stg_kz']) || isset($_POST['stg_kz']))
	$stg_kz=(isset($_GET['stg_kz'])?$_GET['stg_kz']:$_POST['stg_kz']);
else
	$stg_kz=0;
if (isset($_GET['semester']) || isset($_POST['semester']))
	$semester=(isset($_GET['semester'])?$_GET['semester']:$_POST['semester']);
else
	$semester=0;

if(!is_numeric($stg_kz))
	$stg_kz=0;
if(!is_numeric($semester))
	$semester=0;

if (isset($_POST['neu']))
{
	$lf = new lehrfach($conn);
	$lf->new=true;
	$lf->studiengang_kz=$stg_kz;
	$lf->fachbereich_kurzbz=$_POST['fachbereich_kurzbz'];
	$lf->kurzbz=$_POST['kurzbz'];
	$lf->bezeichnung = $_POST['bezeichnung'];
	$lf->farbe = $_POST['farbe'];
	$lf->aktiv = true;
	$lf->semester = $semester;
	$lf->sprache = $_POST['sprache'];
	$lf->updateamum = date('Y-m-d H:i:s');
	$lf->updatevon = $user;
	$lf->insertamum = date('Y-m-d H:i:s');
	$lf->insertvon = $user;

	if(!$lf->save())
	{
		echo "<br>$lf->errormsg<br>";
	}
}

if (isset($_POST['type']) && $_POST['type']=='editsave')
{
	$lf = new lehrfach($conn);
	$lf->new=false;
	$lf->lehrfach_id = $_POST['lehrfach_id'];
	$lf->studiengang_kz=$stg_kz;
	$lf->fachbereich_kurzbz=$_POST['fachbereich_kurzbz'];
	$lf->kurzbz=$_POST['kurzbz'];
	$lf->bezeichnung = $_POST['bezeichnung'];
	$lf->farbe = $_POST['farbe'];
	$lf->aktiv = isset($_POST['aktiv']);
	$lf->semester = $semester;
	$lf->sprache = $_POST['sprache'];
	$lf->updateamum = date('Y-m-d H:i:s');
	$lf->updatevon = $user;

	if(!$lf->save())
	{
		echo "<br>$lf->errormsg<br>";
	}
}

$sql_query="SELECT 
				tbl_lehrfach.lehrfach_id AS Nummer, tbl_lehrfach.kurzbz AS Fach, tbl_lehrfach.bezeichnung AS Bezeichnung,
				tbl_lehrfach.farbe AS Farbe, fachbereich_kurzbz as fachbereich,	tbl_lehrfach.aktiv, tbl_lehrfach.sprache AS Sprache
			FROM 
				lehre.tbl_lehrfach
			WHERE 
				tbl_lehrfach.studiengang_kz='$stg_kz' AND 
				semester='$semester' 
			ORDER BY tbl_lehrfach.kurzbz";

//echo $sql_query;
$result_lehrfach=pg_query($conn, $sql_query);
if(!$result_lehrfach) error("Lehrfach not found!");
$outp='';
$s=array();
$outp.= "Studiengang: <SELECT name='stg_kz' onchange='window.location.href=this.value'>";
foreach ($studiengang as $stg)
{
	if($stg->studiengang_kz==$stg_kz)
		$selected='selected';
	else 
		$selected='';
	//$outp.= '<A href="lehrfach.php?stg_kz='.$stg->studiengang_kz.'&semester='.$semester.'">'.$stg->kurzbzlang.'</A> - ';
	$outp.= '<option value="lehrfach.php?stg_kz='.$stg->studiengang_kz.'&semester='.$semester.'" '.$selected.'>'.$stg->kuerzel.'</option>';
	$s[$stg->studiengang_kz]->max_sem=$stg->max_semester;
	$s[$stg->studiengang_kz]->kurzbz=$stg->kurzbzlang;
}
$outp.="</SELECT>";

$outp.=" Semester: <SELECT name='semester' onchange='window.location.href=this.value'>";
for ($i=0;$i<=$s[$stg_kz]->max_sem;$i++)
{
	if($i==$semester)
		$selected='selected';
	else 
		$selected='';
	//$outp.= '<A href="lehrfach.php?stg_kz='.$stg_kz.'&semester='.$i.'">'.$i.'</A> -- ';
	$outp.= '<option value="lehrfach.php?stg_kz='.$stg_kz.'&semester='.$i.'" '.$selected.'>'.$i.'</option>';
}
$outp.="</SELECT>";

echo '
<html>
<head>
<title>Lehrfach Verwaltung</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
</head>
<body>
<H2>Lehrfach Verwaltung ('.$s[$stg_kz]->kurzbz.' - '.$semester.')</H2>
';

echo $outp;
if (isset($_GET['type']) && $_GET['type']=='edit')
{
	$lf=new lehrfach($conn);
	$lf->load($_GET['lehrfach_nr']);
	echo '<form name="lehrfach_edit" method="post" action="lehrfach.php">';
	echo '<p><b>Edit Lehrfach: '.$_GET['lehrfach_nr'].'</b>';
	echo '<table>';
	
	echo '
	<tr><td><i>Fachbereich</i></td><td><SELECT name="fachbereich_kurzbz">
      			<option value="-1">- ausw&auml;hlen -</option>';
	
	foreach($fachbereiche as $fb)
	{
		echo "<option value=\"$fb->fachbereich_kurzbz\" ";
		if ($lf->fachbereich_kurzbz==$fb->fachbereich_kurzbz)
			echo "selected";
		echo " >$fb->fachbereich_kurzbz</option>\n";
	}

	echo '</SELECT></td></tr>';

    echo '<tr><td><i>Name</i></td><td><input type="text" name="bezeichnung" size="30" maxlength="250" value="'.$lf->bezeichnung.'"></td></tr>';
	echo '<tr><td><i>Kurzbezeichnung</i></td><td>';
	echo '<input type="text" name="kurzbz" size="30" maxlength="12" value="'.$lf->kurzbz.'"></td></tr>';
	echo '<tr><td><i>Farbe</i></td><td>';
    echo '<input type="text" name="farbe" size="30" maxlength="7" value="'.$lf->farbe.'"></td></tr>';

	echo '<tr><td>Aktiv</td><td><input type="checkbox" name="aktiv" value="1" '.($lf->aktiv=='t'?'checked':'').' />';
    echo '<tr><td>Sprache</td><td><select name="sprache">';

	$qry1="SELECT * FROM public.tbl_sprache";
	if(!$result1=pg_query($conn,$qry1))
	{
		die( "Fehler bei der DB-Connection");
	}

	while($row1=pg_fetch_object($result1))
	{
	   if($row1->sprache==$lf->sprache)
	      echo "<option value='$row1->sprache' selected>$row1->sprache</option>";
	   else
	      echo "<option value='$row1->sprache'>$row1->sprache</option>";
	}

	echo '</select></td></tr>';
	echo '</table>';
	echo '<input type="hidden" name="type" value="editsave">';
	echo '<input type="hidden" name="lehrfach_id" value="'.$lf->lehrfach_id.'">';
	echo '<input type="hidden" name="stg_kz" value="'.$stg_kz.'">';
	echo '<input type="hidden" name="semester" value="'.$semester.'">';
	echo '<input type="submit" name="save" value="Speichern">';
	echo '</p><hr></form>';
}
else
{
	echo '
			<form action="lehrfach.php" method="post" name="lehrfach_neu" id="lehrfach_neu">
			  <p><b>Neues Lehrfach</b>: <br/>';
	echo '<table>';

	echo '
	<tr><td><i>Fachbereich</i></td><td><SELECT name="fachbereich_kurzbz" onchange="document.getElementById(\'farbe\').value=this.options[this.selectedIndex].getAttribute(\'farbe\')">
      			<option value="-1">- ausw&auml;hlen -</option>';

			foreach($fachbereiche as $fb)
			{
				echo "<option value=\"$fb->fachbereich_kurzbz\" farbe=\"$fb->farbe\" ";
				echo " >$fb->fachbereich_kurzbz</option>\n";
			}

	echo '</SELECT></td></tr>';

    echo '<tr><td><i>Name</i></td><td><input type="text" name="bezeichnung" size="30" maxlength="250" value=""></td></tr>';
	echo '<tr><td><i>Kurzbezeichnung</i></td><td>';
	echo '<input type="text" name="kurzbz" size="30" maxlength="12" value=""></td></tr>';
    echo '<tr><td><i>Farbe</i></td><td>';
    echo '<input type="text" name="farbe" id="farbe" size="30" maxlength="7" value=""></td></tr>';
    echo '<tr><td>Sprache</td><td><select name="sprache">';

	$qry1="SELECT * FROM public.tbl_sprache";
	if(!$result1=pg_query($conn,$qry1))
		die( 'Fehler bei der DB-Connection');

	while($row1=pg_fetch_object($result1))
	   echo "<option value='$row1->sprache'>$row1->sprache</option>";

	echo '</select></td></tr>	</table>';
		echo '<input type="hidden" name="stg_kz" value="'.$stg_kz.'">';
	echo '<input type="hidden" name="semester" value="'.$semester.'">';


	echo '
		    <input type="hidden" name="type" value="save">
		    <input type="submit" name="neu" value="Speichern">
		  </p>
		  </form>
		<hr>';

if ($result_lehrfach!=0)
{
	echo '
	<h3>&Uuml;bersicht - '.pg_num_rows($result_lehrfach).' Einträge</h3>
	<table class="liste table-autosort:2 table-stripeclass:alternate table-autostripe">
	<thead>';
	
	echo "
		<tr class='liste'>
			<th class='table-sortable:default'>id</th>
			<th class='table-sortable:default'>kurzbz</th>
			<th class='table-sortable:default'>bezeichnung</th>
			<th class='table-sortable:default'>farbe</th>
			<th class='table-sortable:default'>aktiv</th>
			<th class='table-sortable:default'>fachbereich</th>
			<th class='table-sortable:default'>sprache</th>
			<th class='table-sortable:default'>&nbsp;</th>
		</tr>
	</thead>
	<tbody>";
	
	$num_rows=pg_num_rows($result_lehrfach);
	for($i=0;$i<$num_rows;$i++)
	{
	   $row=pg_fetch_object($result_lehrfach);
	   echo "
		<tr>
			<td>$row->nummer</td>
			<td>$row->fach</td>
			<td>$row->bezeichnung</td>
			<td>$row->farbe</td>
			<td>".($row->aktiv=='t'?'Ja':'Nein')."</td>
			<td>$row->fachbereich</td>
			<td>$row->sprache</td>	   
			<td><a href=\"lehrfach.php?lehrfach_nr=$row->nummer&type=edit&stg_kz=$stg_kz&semester=$semester\">Edit</a></td>
		</tr>";
	}
	
	echo '</tbody></table>';
}
else
	echo "Kein Eintrag gefunden!";

}
?>
<br>
</body>
</html>