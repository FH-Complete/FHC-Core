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
		
		require_once('../../include/functions.inc.php');
		require_once('../../include/studiengang.class.php');
		require_once('../../include/gruppe.class.php');
		require_once('../../include/person.class.php');
		require_once('../../include/benutzer.class.php');
		require_once('../../include/student.class.php');

		if (!$db = new basis_db())
			die('Es konnte keine Verbindung zum Server aufgebaut werden.');

if (isset($_GET['studiengang_kz']))
	$studiengang_kz=$_GET['studiengang_kz'];
else if(isset($_POST['studiengang_kz']))
	$studiengang_kz = $_POST['studiengang_kz'];	
else
	$studiengang_kz=null;
	
if (isset($_GET['sem']))

	$sem=$_GET['sem'];
else
	$sem=null;

if (isset($_GET['ss']))

	$ss=$_GET['ss'];
else
	$ss=null;
?>
<html>
<head>
<title>Gruppe-Verwaltung</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<script language="JavaScript" type="text/javascript">
function conf_del()
{
	return confirm('Diese Gruppe wirklich löschen?');
}
</script>
</head>
<body>
<H1>Gruppen Verwaltung</H1>
<?php


// Studiengang AuswahlFilter
$stg=new studiengang();
if ($stg->getAll('kurzbzlang'))
{
	echo '- ';

		echo '<a href="?studiengang_kz=">Alle</a> - ';	
	
	foreach($stg->result AS $sg)
	{
		if (is_null($studiengang_kz))
			$studiengang_kz=$sg->studiengang_kz;
	
		echo '<a href="?studiengang_kz='.$sg->studiengang_kz.'">';
		if ($studiengang_kz==$sg->studiengang_kz)
			echo '<u>';
		echo $sg->kurzbzlang.' ('.$sg->typ.$sg->kurzbz.')';
		if ($studiengang_kz==$sg->studiengang_kz)
			echo '</u>';
		echo '</a> - ';
	}
	echo '<BR/>';
}

if (isset($_POST['newFrm']) || isset($_GET['newFrm']))
{
	doEdit(null,true);
}
else if (isset($_GET['edit']))
{
	doEdit(addslashes($_GET['kurzbz']),false);
}
else if (isset($_POST['type']) && $_POST['type']=='save')
{
	doSave();
	getUebersicht();
}
else if (isset($_GET['type']) && $_GET['type']=='delete')
{
	$e=new gruppe();
	if(!$e->delete($_GET['einheit_id']))
		echo $e->errormsg;
	getUebersicht();
}
else
{
	getUebersicht();
}


function doSave()
{

	$e=new gruppe();

	if ($_POST['new']=='true')
	{
		$e->new = true;
		$e->gruppe_kurzbz=$_POST['kurzbz'];
		$e->insertamum = date('Y-m-d H:i:s');
		$e->insertvon = get_uid();
	}
	else
	{
		$e->load($_POST['kurzbz']);
		$e->new=false;
	}

	$e->updateamum = date('Y-m-d H:i:s');
	$e->updatevon = get_uid();
	$e->bezeichnung=$_POST['bezeichnung'];
	$e->beschreibung=$_POST['beschreibung'];
	$e->studiengang_kz=$_POST['studiengang_kz'];
	$e->semester=$_POST['semester'];
	$e->mailgrp=isset($_POST['mailgrp']);
	$e->sichtbar=isset($_POST['sichtbar']);
	$e->generiert=isset($_POST['generiert']);
	$e->aktiv=isset($_POST['aktiv']);
	$e->sort=$_POST['sort'];
	if(!$e->save())
		echo $e->errormsg;
}



function doEdit($kurzbz,$new=false)
{
    if (!$new)
		$e=new gruppe($kurzbz);
	else
		$e = new gruppe();
	?>
	<form name="gruppe" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
  		<p><b>Gruppe <?php echo ($new?'hinzufügen':'bearbeiten'); ?></b>:
  			<table border="0">
  			<tr>
  				<td><i>Kurzbezeichnung</i></td>
      			<td>
      				<input type="text" name="kurzbz" size="10" maxlength="10" value="<?php echo $e->gruppe_kurzbz; ?>">
				</td>
			</tr>
  			<tr>
  				<td><i>Bezeichnung</i></td>
  				<td>
    				<input type="text" name="bezeichnung" size="20" maxlength="32" value="<?php echo $e->bezeichnung; ?>">
    			</td>
    		</tr>
    		<tr>
  				<td><i>Beschreibung</i></td>
  				<td>
    				<input type="text" name="beschreibung" size="20" maxlength="128" value="<?php echo $e->beschreibung; ?>">
    			</td>
    		</tr>
			<tr>
				<td><i>Studiengang</i><t/td>
				<td>
					<SELECT name="studiengang_kz">
      					<option value="-1">- auswählen -</option>
						<?php
							// Auswahl des Studiengangs
							$stg=new studiengang();
							$stg->getAll();
							foreach($stg->result as $studiengang)
							{
								echo "<option value=\"$studiengang->studiengang_kz\" ";
								if ($studiengang->studiengang_kz==$e->studiengang_kz)
								echo "selected";
								echo " >$studiengang->kuerzel ($studiengang->bezeichnung)</option>\n";
							}
						?>
		    		</SELECT>
				</td>
			</tr>
			<tr><td><i>Semester</i><t/td><td><input type="text" name="semester" size="2" maxlength="1" value="<?php echo $e->semester ?>"></td></tr>
			<tr><td><i>Mailgrp</i><t/td><td><input type='checkbox' name='mailgrp' <?php echo ($e->mailgrp?'checked':'');?>>
			<tr><td><i>Sichtbar</i><t/td><td><input type='checkbox' name='sichtbar' <?php echo ($e->sichtbar?'checked':'');?>>
			<tr><td><i>Generiert</i><t/td><td><input type='checkbox' name='generiert' <?php echo ($e->generiert?'checked':'');?>>
			<tr><td><i>Aktiv</i><t/td><td><input type='checkbox' name='aktiv' <?php echo ($e->aktiv?'checked':'');?>>
			<tr>
				<td><i>Sort</i><t/td><td><input type='text' name='sort' maxlength="4" value="<?php echo $e->sort;?>">
				</td>
			</tr>
		</table>
		<input type="hidden" name="pk" value="<?php echo $e->gruppe_kurzbz ?>" />
		<input type="hidden" name="new" value="<?php echo ($new?'true':'false') ?>" />
    	<input type="hidden" name="type" value="save">
    	<input type="submit" name="save" value="Speichern">
  	</p>
  	<hr>
</form>
<?php
}

function getUebersicht()
{
	global $studiengang_kz,$semester;
	if (!$db = new basis_db())
			die('Es konnte keine Verbindung zum Server aufgebaut werden.');
			
  $gruppe=new gruppe();
	// Array mit allen Einheiten holen
	$gruppeen=$gruppe->getgruppe($studiengang_kz,$semester);
	//print_r($gruppeen);
	?>
<h3>&Uuml;bersicht</h3>

<table class='liste'>

<?php

	$num_rows=count($gruppeen);
	$foo = 0;
	echo "<tr class='liste'><th>Kurzbz.</th><th>Bezeichnung</th><th>Stg.</th><th>Sem.</th><th>Mailgrp</th><th>Anzahl</th><th colspan=\"3\">Aktion</th></tr>";

	$i=0;
	$qry = "SELECT studiengang_kz, UPPER(typ::varchar(1) || kurzbz) as kuerzel FROM public.tbl_studiengang";
	$stg = array();
	if(!$result = $db->db_query($qry))
			die('Fehler beim Laden der Studiengaenge');
	while($row = $db->db_fetch_object($result))
			$stg[$row->studiengang_kz] = $row->kuerzel;

	foreach ($gruppe->result as $e)
	{
		$i++;
		$c=$i%2;

		echo '<tr class="liste'.$c.'">';

		echo "<td>$e->gruppe_kurzbz </td>";
		echo "<td>$e->bezeichnung </td>";
		echo "<td>".$stg[$e->studiengang_kz]."</td>";
		echo "<td>$e->semester </td>";
		echo "<td>".($e->mailgrp?'Ja':'Nein')."</td>";
		echo "<td>".$gruppe->countStudenten($e->gruppe_kurzbz)."</td>";
		echo "<td class='button'><a href='einheit_det.php?kurzbz=$e->gruppe_kurzbz'>Details</a></td>";
		echo "<td class='button'><a href=\"einheit_menu.php?edit=1&kurzbz=$e->gruppe_kurzbz\">Edit</a></td>";
	   	echo "<td class='button'><a href=\"einheit_menu.php?einheit_id=$e->gruppe_kurzbz&studiengang_kz=$e->studiengang_kz&type=delete\" onclick='return conf_del()'>Delete</a></td>";
	   	echo "</tr>\n";
	}
?>
</table>
<?php

}


?>

</body>
</html>
