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
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */

require_once('../../../config/cis.config.inc.php');
require_once('../../../include/basis_db.class.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/phrasen.class.php'); 

if (!$db = new basis_db())
	die('Fehler beim Oeffnen der Datenbankverbindung');
	
$sprache = getSprache(); 
$p=new phrasen($sprache); 
	
if(!($erg=$db->db_query("SELECT * FROM tbl_studiengang WHERE studiengang_kz>0 ORDER BY kurzbz")))
	die($db->db_last_error());
$num_rows=$db->db_num_rows($erg);
?>
<html>
<head>
	<title><?php echo $p->t('lvplan/uebersichtDerLehrverbaende');?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
</head>

<body id="inhalt">
	<H2><table class="tabcontent" id="inhalt">
		<tr>
		<td>&nbsp;<a class="Item" href="index.php"><?php echo $p->t('lvplan/lehrveranstaltungsplan');?></a> &gt;&gt; <?php echo $p->t('lvplan/lehrverbaende');?></td>
		<td align="right"><A href="help/index.html" class="hilfe" target="_blank">HELP&nbsp;</A></td>
		</tr>
		</table>
	</H2>
<table border="1" cellpadding="10" rules="cols">
<tr class="liste">
<?php
	for ($i=0;$i<$num_rows;$i++)
	{
		$row=$db->db_fetch_object($erg, $i);
		echo "<th>$row->bezeichnung ($row->kurzbz)</th>";
	}
?>

</tr>
<tr>
<?php
	for ($i=0; $i<$num_rows; $i++)
	{
		echo '<td class="MarkLine" nowrap valign="top">';
		$row=$db->db_fetch_object($erg, $i);
     	$stg_kz=$row->studiengang_kz;
		$stg_kzbz=$row->typ.$row->kurzbz;
		$sql_query="SELECT DISTINCT semester FROM tbl_student WHERE studiengang_kz=$stg_kz AND semester<10 ORDER BY semester";
		//echo $sql_query;
		if(!($result_sem=$db->db_query($sql_query)))
			die($db->db_last_error());
		$nr_sem=$db->db_num_rows($result_sem);
		for  ($j=0; $j<$nr_sem; $j++)
		{
			$row_sem=$db->db_fetch_object($result_sem, $j);
			echo '<a class="Item" href="stpl_week.php?type=verband&stg_kz='.$stg_kz."&sem=$row_sem->semester\">$stg_kzbz-$row_sem->semester</a><br>";
			$sql_query="SELECT DISTINCT verband FROM tbl_student WHERE studiengang_kz=$stg_kz AND semester=$row_sem->semester ORDER BY verband";
			//echo $sql_query;
			if(!($result_ver=$db->db_query($sql_query)))
				die($db->db_last_error());
			$nr_ver=$db->db_num_rows($result_ver);
			for  ($k=0; $k<$nr_ver; $k++)
			{
				$row_ver=$db->db_fetch_object($result_ver, $k);
				echo "&nbsp;- <a class='Item' href=\"stpl_week.php?type=verband&stg_kz=$stg_kz&sem=$row_sem->semester&ver=$row_ver->verband\">$stg_kzbz-$row_sem->semester$row_ver->verband</a><br>";
				$sql_query="SELECT DISTINCT gruppe FROM tbl_student WHERE studiengang_kz=$stg_kz AND semester=$row_sem->semester AND verband='$row_ver->verband' ORDER BY gruppe";
				//echo $sql_query;
				if(!($result_grp=$db->db_query($sql_query)))
					die($db->db_last_error());
				$nr_grp=$db->db_num_rows($result_grp);
				for  ($l=0; $l<$nr_grp; $l++)
				{
					$row_grp=$db->db_fetch_object($result_grp, $l);
					echo "&nbsp;&nbsp;- <a class='Item' href=\"stpl_week.php?type=verband&stg_kz=$stg_kz&sem=$row_sem->semester&ver=$row_ver->verband&grp=$row_grp->gruppe\">$stg_kzbz-$row_sem->semester$row_ver->verband$row_grp->gruppe</a><br>";
				}
			}
		}
		echo"</td>";
	}
?>
</tr>
</table>
</body>
</html>