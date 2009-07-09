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
			

/**
 * Changes:	22.10.2004: Anpassung an neues DB-Schema (WM)
 */
	
	
	include('../../include/functions.inc.php');

	
	$sql_query="SELECT uid, titelpre, vorname, nachname, UPPER(typ::varchar(1) || kurzbz) as kurzbz, semester, verband, gruppe, matrikelnr FROM campus.vw_student JOIN public.tbl_studiengang USING(studiengang_kz) WHERE true ";
	if (isset($_GET['stg_kz']))
		$sql_query.="AND studiengang_kz='".addslashes($_GET['stg_kz'])."' ";
	if (isset($_GET['sem']) && is_numeric($_GET['sem']))
		$sql_query.="AND semester=".$_GET['sem']." ";
	if (isset($_GET['ver']))
		$sql_query.="AND verband='".addslashes($_GET['ver'])."' ";
	if (isset($_GET['grp']) && is_numeric($_GET['grp']))
		$sql_query.="AND gruppe=".$_GET['grp']." ";
	$sql_query.="ORDER BY nachname, kurzbz, semester, verband";
	if(!($erg=$db->db_query($sql_query)))
		die($db->db_last_error());
	$num_rows=$db->db_num_rows($erg);
?>

<html>
<head>
<title>Detail Studenten</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>

<body class="background_main">
<h4>Detailansicht</h4>
Results: <?php echo $num_rows; ?><br>
<br>
<table border="0">
<tr><th>Titel</th><th>Vornamen</th><th>Nachname</th><th>STG</th><th>Sem.</th><th>Verband</th><th>Gruppe</th><th>Matrikelnr.</th><th>eMail</th></tr>
<?php
	for ($i=0; $row=$db->db_fetch_object($erg); $i++)
	{
		$zeile=$i % 2;

		$vorname=$row->vorname;
		$nachname=$row->nachname;
		$stgkurzbz=$row->kurzbz;
		$titel=$row->titelpre;
		$matrikelnr=$row->matrikelnr;
		$sem=$row->semester;
		$ver=$row->verband;
		$grp=$row->gruppe;
		$id=$row->uid;
		$emailtw=$id.'@technikum-wien.at';
		?>
		<tr class="liste<?php echo $zeile; ?>">
		<td><?php echo $titel; ?></td>
		<td><?php echo $vorname; ?></td>
		<td><?php echo $nachname; ?></td>
		<td><?php echo $stgkurzbz; ?></td>
		<td><?php echo $sem; ?></td>
		<td><?php echo $ver; ?></td>
		<td><?php echo $grp; ?></td>
		<td><?php echo $matrikelnr; ?></td>
		<td><a href="mailto:<?php echo $emailtw; ?>"><?php echo $emailtw; ?></a></td>
		<td><a href="student_edit.php?id=<?php echo $id; ?>" class="linkblue">Edit</a></td>
		</tr>
		<?php
	}
?>
</table>
</body>
</html>
