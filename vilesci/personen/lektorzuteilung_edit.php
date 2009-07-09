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
		$sql_query="INSERT INTO lektorzuteilung (lektor_id, lehrfach_id, stg_id, semester) VALUES ($lektorid,$lehrfachid,$stgid,$semester)";
		//echo $sql_query;
		if(!($result=$db->db_query($sql_query)))
				die($db->db_last_error());
	}
	
	if ($type=='del')
	{
		$sql_query="DELETE FROM lektorzuteilung WHERE id=$id";
		//echo $sql_query;
		if(!($result=$db->db_query($sql_query)))
				die($db->db_last_error());
	}

	// Daten für Lektorenauswahl
	$sql_query="SELECT id, nachname, vornamen, uid FROM lektor ORDER BY upper(nachname), vornamen, uid";
		if(!($result_lektor=$db->db_query($sql_query)))
				die($db->db_last_error());
		
		
	// Daten für Lehrfachauswahl
	$sql_query="SELECT id, kurzbz, bezeichnung FROM lehrfach ORDER BY kurzbz";
		if(!($result_lehrfach=$db->db_query($sql_query)))
				die($db->db_last_error());
		
		
	// Daten für Studiengang
	$sql_query="SELECT id, kurzbz, bezeichnung FROM studiengang ORDER BY kurzbz";
		if(!($result_stg=$db->db_query($sql_query)))
				die($db->db_last_error());

	// Daten für die Zuteilungen
	if (!isset($order))
		$order='upper(nachname), vornamen, uid';
	$sql_query="SELECT lektorzuteilung.id, nachname, nachname, vornamen, uid, lehrfach.kurzbz AS lfkurzbz, studiengang.kurzbz AS stgkurzbz, semester";
	$sql_query.=" FROM lektorzuteilung, lektor, lehrfach, studiengang WHERE lektorzuteilung.lektor_id=lektor.id";
	$sql_query.=" AND lektorzuteilung.lehrfach_id=lehrfach.id AND lektorzuteilung.stg_id=studiengang.id ORDER BY $order";
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
<!--<link rel="stylesheet" href="../../include/styles.css" type="text/css"> -->
<LINK rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>

<body class="background_main">
<h2>Lektoren - Lehrfach Zuteilung</h2>
Anzahl:
<?php echo $num_rows; ?>
<br>
<br>
<table border="<?php echo $cfgBorder;?>">
<tr bgcolor="<?php echo $cfgThBgcolor; ?>">
	<th></th><th>Nachname</th><th>Vornamen</th>
	<th>uid</th>
	<th>Lehrfach</th>
	<th>Studiengang</th>
	<th>Semester</th></tr>
<?php
	for ($i=0; $i<$num_rows; $i++)
	{
		$bgcolor = $cfgBgcolorOne;
     	$i % 2  ? 0: $bgcolor = $cfgBgcolorTwo;

		$row=pg_fetch_object ($erg, $i);

		?>
		<tr bgcolor=<?php echo $bgcolor; ?>>
		<td><a href="lektorzuteilung_edit.php?id=<?php echo $row->id.'&type=del'; ?>" class="linkblue">Delete</a></td>
		<td><?php echo $row->nachname; ?></td>
		<td><?php echo $row->vornamen; ?></td>
		<td><A href="mailto:<?php echo $row->uid; ?>@technikum-wien.at" class="linkgreen"><?php echo $row->uid; ?></A></td>
		<td><?php echo $row->lfkurzbz; ?></td>
		<td><?php echo $row->stgkurzbz; ?></td>
		<td><?php echo $row->semester; ?></td>
		</tr>
		<?php
	}
?>
</table>
<FORM name="newpers" method="post" action="lektorzuteilung_edit.php">
  <INPUT type="hidden" name="type" value="new">
  Lektor:
  <SELECT name="lektorid">
    <?php
		// Auswahl des Lektors
		$num_rows=pg_numrows($result_lektor);
		for ($i=0;$i<$num_rows;$i++)
		{
			$row=pg_fetch_object ($result_lektor, $i);
			echo "<option value=\"$row->id\">$row->nachname $row->vornamen - $row->uid</option>";
		}
		?>
  </SELECT>
  <BR>
  Lehrfach:
  <SELECT name="lehrfachid">
    <?php
		// Auswahl des Lehrfach
		$num_rows=pg_numrows($result_lehrfach);
		for ($i=0;$i<$num_rows;$i++)
		{
			$row=pg_fetch_object ($result_lehrfach, $i);
			echo "<option value=\"$row->id\">$row->kurzbz - $row->bezeichnung</option>";
		}
		?>
  </SELECT>
  <BR>
  Studiengang:
  <SELECT name="stgid">
    <?php
		// Auswahl des Lektors
		$num_rows=pg_numrows($result_stg);
		for ($i=0;$i<$num_rows;$i++)
		{
			$row=pg_fetch_object ($result_stg, $i);
			echo "<option value=\"$row->id\">$row->kurzbz - $row->bezeichnung</option>";
		}
		?>
  </SELECT>
  Semester:
  <SELECT name="semester">
    <option value="1">1</option>
	<option value="2">2</option>
	<option value="3">3</option>
	<option value="4">4</option>
	<option value="5">5</option>
	<option value="6">6</option>
	<option value="7">7</option>
	<option value="8">8</option>
  </SELECT>
  <INPUT type="submit" name="Abschicken" value="Hinzuf&uuml;gen">
</FORM>
</body>
</html>
