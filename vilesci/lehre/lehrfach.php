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
require_once('../../include/benutzerberechtigung.class.php');

if(!$conn=pg_pconnect(CONN_STRING))
   die("Konnte Verbindung zur Datenbank nicht herstellen");

$f=new fachbereich($conn);
$f->getAll();
$fachbereiche=$f->result;
$s=new studiengang($conn);
$s->getAll('typ, kurzbz', false);
$studiengang=$s->result;

$user = get_uid();

$rechte =  new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);

if (isset($_GET['stg_kz']) || isset($_POST['stg_kz']))
	$stg_kz=(isset($_GET['stg_kz'])?$_GET['stg_kz']:$_POST['stg_kz']);
else
	$stg_kz='';
if (isset($_GET['semester']) || isset($_POST['semester']))
	$semester=(isset($_GET['semester'])?$_GET['semester']:$_POST['semester']);
else
	$semester='';
	
$fachbereich_kurzbz = (isset($_GET['fachbereich_kurzbz'])?$_GET['fachbereich_kurzbz']:'');

/*
if(!is_numeric($stg_kz))
	$stg_kz=0;
if(!is_numeric($semester))
	$semester=0;
*/
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

$outp='<form method="GET" action="'.$_SERVER['PHP_SELF'].'">';

$s=array();
$outp.= " Studiengang: <SELECT name='stg_kz'>";

if(count($rechte->getFbKz())>0)
	$outp.= '<option value="" >-- Alle --</option>';
$s['']->max_sem=8;
$s['']->kurzbz='';

foreach ($studiengang as $stg)
{
	if($rechte->isBerechtigt('assistenz', $stg->studiengang_kz) || $rechte->isBerechtigt('admin', $stg->studiengang_kz) ||
		$rechte->isBerechtigt('assistenz', 0) || $rechte->isBerechtigt('admin', 0))
	{
		if(count($rechte->getFbKz())==0 && $stg_kz=='')
			$stg_kz=$stg->studiengang_kz;
		
		if($stg->studiengang_kz==$stg_kz)
			$selected='selected';
		else 	
			$selected='';
		//$outp.= '<A href="lehrfach.php?stg_kz='.$stg->studiengang_kz.'&semester='.$semester.'">'.$stg->kurzbzlang.'</A> - ';
		$outp.= '<option value="'.$stg->studiengang_kz.'" '.$selected.'>'.$stg->kuerzel.'</option>';
	}
	
	$s[$stg->studiengang_kz]->max_sem=$stg->max_semester;
	$s[$stg->studiengang_kz]->kurzbz=$stg->kuerzel;
		
}
$outp.="</SELECT>";
$outp.=" Semester: <SELECT name='semester'>";
$outp.= '<option value="" >-- Alle --</option>';
for ($i=0;$i<=$s[$stg_kz]->max_sem;$i++)
{
	if($semester!='' && $i==$semester)
		$selected='selected';
	else 
		$selected='';
	//$outp.= '<A href="lehrfach.php?stg_kz='.$stg_kz.'&semester='.$i.'">'.$i.'</A> -- ';
	$outp.= '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
}
$outp.="</SELECT>";

$outp.= " Institut: <SELECT name='fachbereich_kurzbz'>";

$outp.= '<option value="">-- Alle --</option>';

foreach ($fachbereiche as $fb)
{
	/*
	if($rechte->isBerechtigt('assistenz', null, null, $fb->fachbereich_kurzbz) || $rechte->isBerechtigt('admin', null, null, $fb->fachbereich_kurzbz) ||
		$rechte->isBerechtigt('assistenz', 0) || $rechte->isBerechtigt('admin', 0))
	{*/
		if($fb->fachbereich_kurzbz==$fachbereich_kurzbz)
			$selected='selected';
		else 
			$selected='';
		
		$outp.= '<option value="'.$fb->fachbereich_kurzbz.'" '.$selected.'>'.$fb->bezeichnung.'</option>';
	//}
	
}
$outp.="</SELECT>";

$outp.="
	<input type='submit' value='Anzeigen'>
	</form>";

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
<H2>Lehrfach Verwaltung ('.$s[$stg_kz]->kurzbz.' '.$semester.' '.$fachbereich_kurzbz.')</H2>
';

echo $outp;

if($rechte->isBerechtigt('admin',0))
{
	if (isset($_GET['type']) && $_GET['type']=='edit')
	{
		$lf=new lehrfach($conn);
		$lf->load($_GET['lehrfach_nr']);
		echo '<form name="lehrfach_edit" method="post" action="lehrfach.php">';
		echo '<p><b>Edit Lehrfach: '.$_GET['lehrfach_nr'].'</b>';
		echo '<table>';
		echo '<tr><td>';
		echo " Studiengang:</td><td> <SELECT name='stg_kz'>";
		
		foreach ($studiengang as $stg)
		{
				if($stg->studiengang_kz==$lf->studiengang_kz)
					$selected='selected';
				else 
					$selected='';

				echo '<option value="'.$stg->studiengang_kz.'" '.$selected.'>'.$stg->kuerzel.'</option>';
		}
		echo "</SELECT></td></tr>";
		
		echo "<tr><td>Semester:</td><td> <SELECT name='semester'>";
		for ($i=0;$i<=$s[$stg_kz]->max_sem;$i++)
		{
			if($semester!='' && $i==$lf->semester)
				$selected='selected';
			else 
				$selected='';
			
			echo '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
		}
		echo "</SELECT></td></tr>";
		echo '
		<tr><td><i>Institut</i></td><td><SELECT name="fachbereich_kurzbz" onchange="document.getElementById(\'farbe\').value=this.options[this.selectedIndex].getAttribute(\'farbe\')">
	      			<option value="-1">- ausw&auml;hlen -</option>';
	
		foreach($fachbereiche as $fb)
		{
			if($fb->fachbereich_kurzbz==$lf->fachbereich_kurzbz)
				$selected='selected';
			else 
				$selected='';
			
			echo "<option value=\"$fb->fachbereich_kurzbz\" farbe=\"$fb->farbe\" $selected";
			echo " >$fb->fachbereich_kurzbz</option>\n";
		}
	
		echo '</SELECT></td></tr>';
	
	    echo '<tr><td><i>Name</i></td><td><input type="text" name="bezeichnung" size="30" maxlength="250" value="'.$lf->bezeichnung.'"></td></tr>';
		echo '<tr><td><i>Kurzbezeichnung</i></td><td>';
		echo '<input type="text" name="kurzbz" size="30" maxlength="12" value="'.$lf->kurzbz.'"></td></tr>';
		echo '<tr><td><i>Farbe</i></td><td>';
	    echo '<input type="text" name="farbe" id="farbe" size="30" maxlength="7" value="'.$lf->farbe.'"></td></tr>';
	
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
		echo '<tr><td>';
		echo " Studiengang:</td><td> <SELECT name='stg_kz'>";
		
		foreach ($studiengang as $stg)
		{
				if($stg->studiengang_kz==$stg_kz)
					$selected='selected';
				else 
					$selected='';

				echo '<option value="'.$stg->studiengang_kz.'" '.$selected.'>'.$stg->kuerzel.'</option>';
		}
		echo "</SELECT></td></tr>";
		
		echo "<tr><td>Semester:</td><td> <SELECT name='semester'>";
		for ($i=0;$i<=$s[$stg_kz]->max_sem;$i++)
		{
			if($semester!='' && $i==$semester)
				$selected='selected';
			else 
				$selected='';
			
			echo '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
		}
		echo "</SELECT></td></tr>";
		echo '
		<tr><td><i>Institut</i></td><td><SELECT name="fachbereich_kurzbz" onchange="document.getElementById(\'farbe\').value=this.options[this.selectedIndex].getAttribute(\'farbe\')">
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
	}
}

if($rechte->isBerechtigt('admin'))
	$where = ' AND aktiv=true';
else 
	$where = '';

$sql_query="SELECT 
				tbl_lehrfach.lehrfach_id AS Nummer, tbl_lehrfach.kurzbz AS Fach, tbl_lehrfach.bezeichnung AS Bezeichnung,
				tbl_lehrfach.farbe AS Farbe, fachbereich_kurzbz as fachbereich,	tbl_lehrfach.aktiv, tbl_lehrfach.sprache AS Sprache,
				tbl_lehrfach.studiengang_kz, tbl_lehrfach.semester
			FROM 
				lehre.tbl_lehrfach
			WHERE true
			".($stg_kz!=''?"AND tbl_lehrfach.studiengang_kz='$stg_kz'":'')."
			".($semester!=''?"AND semester='$semester'":'')."
			".($fachbereich_kurzbz!=''?"AND fachbereich_kurzbz = '$fachbereich_kurzbz'":'')."
			$where
			ORDER BY tbl_lehrfach.kurzbz";

//echo $sql_query;
$result_lehrfach=pg_query($conn, $sql_query);
if(!$result_lehrfach) error("Lehrfach not found!");

if(!isset($_GET['type']))
{
	if ($result_lehrfach!=0)
	{
		echo '
		<h3>&Uuml;bersicht - '.pg_num_rows($result_lehrfach).' Einträge</h3>
		<table class="liste table-autosort:2 table-stripeclass:alternate table-autostripe">
		<thead>';
		
		echo "
			<tr class='liste'>
				<th class='table-sortable:default'>ID</th>
				<th class='table-sortable:default'>Stg</th>
				<th class='table-sortable:default'>Sem</th>
				<th class='table-sortable:default'>Kurzbz</th>
				<th class='table-sortable:default'>Bezeichnung</th>
				<th class='table-sortable:default'>Farbe</th>
				<th class='table-sortable:default'>Aktiv</th>
				<th class='table-sortable:default'>Institut</th>
				<th class='table-sortable:default'>Sprache</th>
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
				<td>".$s[$row->studiengang_kz]->kurzbz."</td>
				<td>$row->semester</td>
				<td>$row->fach</td>
				<td>$row->bezeichnung</td>
				<td>$row->farbe</td>
				<td>".($row->aktiv=='t'?'Ja':'Nein')."</td>
				<td>$row->fachbereich</td>
				<td>$row->sprache</td>
				<td>";
		   if($rechte->isBerechtigt('admin', 0))
				echo "<a href=\"lehrfach.php?lehrfach_nr=$row->nummer&type=edit&stg_kz=$stg_kz&semester=$semester\">Edit</a>";
			echo "</td></tr>";
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