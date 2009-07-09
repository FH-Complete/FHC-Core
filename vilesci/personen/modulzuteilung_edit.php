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
	$type=(isset($_REQUEST['type'])?$_REQUEST['type']:''); 
			
	if ($type=='new')
	{
		$sql_query="INSERT INTO modulzuteilung (lektor_id, modul_id, semester) VALUES ($lektorid,$modulid, $semester)";
		//echo $sql_query;
		$result=$db->db_query($sql_query);
	}
	if ($type=='del')
	{
		$sql_query="DELETE FROM modulzuteilung WHERE id=$id";
		//echo $sql_query;
		$result=$db->db_query($sql_query);
	}

	// Daten für Lektorenauswahl
	$sql_query="SELECT id, nachname, vornamen, uid FROM lektor ORDER BY upper(nachname), vornamen, uid";
	$result_lektor=$db->db_query($sql_query);
	if(!$result_lektor)
		die ($db->db_last_error());
	// Daten für Modulauswahl
	$sql_query="SELECT id, kurzbz, bezeichnung FROM einheit ORDER BY kurzbz";
	$result_modul=$db->db_query($sql_query);
	if(!$result_modul)
		die ($db->db_last_error());

	// Daten für die Zuteilungen
	if (!isset($order))
		$order='upper(nachname), vornamen, uid';
	$sql_query="SELECT modulzuteilung.id, nachname, nachname, vornamen, uid, einheit.kurzbz AS mdkurzbz";
	$sql_query.=" FROM modulzuteilung, lektor, einheit WHERE modulzuteilung.lektor_id=lektor.id";
	$sql_query.=" AND modulzuteilung.modul_id=einheit.id ORDER BY $order";
	//echo $sql_query;
	if(!($erg=$db->db_query($sql_query)))
		die($db->db_last_error());
	$num_rows=$db->db_num_rows($erg);
	
	
$cfgBorder=1;	
$cfgThBgcolor='#CCCCCC';

$cfgBgcolorOne='#F4F4F4';
$cfgBgcolorTwo='#FEFFE6';	
?>

<html>
<head>
<title>Zuteilung der Lektoren</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<!--<link rel="stylesheet" href="../include/styles.css" type="text/css">-->
<LINK rel="stylesheet" href="../../include/styles.css" type="text/css">
</head>

<body class="background_main">
<h2>Lektoren - Modul Zuteilung</h2>
Anzahl:
<?php echo $num_rows; ?>
<br>
<br>
<table border="<?php echo $cfgBorder;?>">
<tr bgcolor="<?php echo $cfgThBgcolor; ?>">
	<th></th><th>Nachname</th><th>Vornamen</th>
	<th>uid</th>
	<th>Modul</th>
<?php
	for ($i=0; $i<$num_rows; $i++)
	{
		$bgcolor = $cfgBgcolorOne;
     	$i % 2  ? 0: $bgcolor = $cfgBgcolorTwo;

		$row=$db->db_fetch_object ($erg, $i);

		?>
		<tr bgcolor=<?php echo $bgcolor; ?>>
		<td><a href="modulzuteilung_edit.php?id=<?php echo $row->id.'&type=del'; ?>" class="linkblue">Delete</a></td>
		<td><?php echo $row->nachname; ?></td>
		<td><?php echo $row->vornamen; ?></td>
		<td><A href="mailto:<?php echo $row->uid; ?>@technikum-wien.at" class="linkgreen"><?php echo $row->uid; ?></A></td>
		<td><?php echo $row->mdkurzbz; ?></td>
		</tr>
		<?php
	}
?>
</table>
<FORM name="newpers" method="post" action="modulzuteilung_edit.php">
  <INPUT type="hidden" name="type" value="new">
  Lektor:
  <SELECT name="lektorid">
    <?php
		// Auswahl des Lektors
		$num_rows=$db->db_num_rows($result_lektor);
		for ($i=0;$i<$num_rows;$i++)
		{
			$row=$db->db_fetch_object ($result_lektor, $i);
			echo "<option value=\"$row->id\">$row->nachname $row->vornamen - $row->uid</option>";
		}
		?>
  </SELECT>
  <BR>
  Modul:
  <SELECT name="modulid">
    <?php
		// Auswahl des Moduls
		$num_rows=$db->db_num_rows($result_modul);
		for ($i=0;$i<$num_rows;$i++)
		{
			$row=$db->db_fetch_object ($result_modul, $i);
			echo "<option value=\"$row->id\">$row->kurzbz - $row->bezeichnung</option>";
		}
		?>
  </SELECT>
  &nbsp;
  Semester:&nbsp;<font face="Arial, Helvetica, sans-serif" size="2"><select name="semester">
	<OPTION value="1" <?php if ($semester==1) echo 'selected'; ?>>1</OPTION>
	<OPTION value="2" <?php if ($semester==2) echo 'selected'; ?>>2</OPTION>
	<OPTION value="3" <?php if ($semester==3) echo 'selected'; ?>>3</OPTION>
	<OPTION value="4" <?php if ($semester==4) echo 'selected'; ?>>4</OPTION>
	<OPTION value="5" <?php if ($semester==5) echo 'selected'; ?>>5</OPTION>
	<OPTION value="6" <?php if ($semester==6) echo 'selected'; ?>>6</OPTION>
	<OPTION value="7" <?php if ($semester==7) echo 'selected'; ?>>7</OPTION>
	<OPTION value="8" <?php if ($semester==8) echo 'selected'; ?>>8</OPTION>
  </select></font>
  <INPUT type="submit" name="Abschicken" value="Hinzuf&uuml;gen">
</FORM>
</body>
</html>