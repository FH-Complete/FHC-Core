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
			
	
	$type=(isset($_GET['type'])?$_GET['type']:(isset($_POST['type'])?$_POST['type']:'')); 
	$stgid=(isset($_GET['stgid'])?$_GET['stgid']:(isset($_POST['stgid'])?$_POST['stgid']:'')); 
	$stgbz=''; 

	
	$sql_query="SELECT * FROM public.tbl_studiengang WHERE studienplaetze>0 ORDER BY kurzbz";
	$result_stg=$db->db_query($sql_query);
	if(!$result_stg) echo "studiengang not found! ".$db->db_last_error().' line '. __LINE__ .' ; file ' . __FILE__."<br>";

	$sql_query="SELECT * FROM lehre.tbl_lehrfach ORDER BY kurzbz";
	$result_lehrf=$db->db_query($sql_query);
	if(!$result_lehrf) echo "lehrfach not found! ".$db->db_last_error().' line '. __LINE__ .' ; file ' . __FILE__."<br>";

	$sql_query="SELECT * FROM public.tbl_ort ORDER BY ort_kurzbz";
	$result_ort=$db->db_query($sql_query);
	if(!$result_ort) echo "ort not found! ".$db->db_last_error().' line '. __LINE__ .' ; file ' . __FILE__."<br>";
	
		
	if ($result_stg && (!isset($stgid) || empty($stgid)) )
			$stgid=$stgbz=$db->db_result($result_stg,0,'studiengang_kz');;
		

	$sql_query="SELECT * FROM public.tbl_studiengang WHERE studiengang_kz=$stgid";
	$result_stgbz=$db->db_query($sql_query);
	if(!$result_stgbz) 
			echo "lehrfach not found! ".$db->db_last_error().' line '. __LINE__ .' ; file ' . __FILE__."<br>";
	else
			$stgbz=$db->db_result($result_stgbz,0,'kurzbz');
		
	if (!isset($semester))
		$semester=0;
	if (!isset($verband))
		$verband='0';
	if (!isset($gruppe))
		$gruppe=0;
	if (!isset($tag))
		$tag=1;
	if (!isset($monat))
		$monat=1;
	if (!isset($jahr))
		$jahr=2009;
	$datum=" AND datum<='2009-07-01' AND datum>='2009-02-05'";
?>

<html>
<head>
<title>Lehrfachverteilung</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<LINK rel="stylesheet" href="../../include/styles.css" type="text/css">
</head>
<body class="background_main">
<H1>Lehrfachverteilung</H1>
<hr>
<form name="stdplan" method="post" action="lehrfach_verteilung.php">
  <p>Studiengang 
    <select name="stgid">
      <?php 
		if ($result_stg)
			$num_rows=$db->db_num_rows($result_stg);
		else
			$num_rows=0;
		for ($i=0;$i<$num_rows;$i++) 
		{
			$row=$db->db_fetch_object ($result_stg, $i);
			echo "<option value=\"$row->studiengang_kz\" ".($stgid==$row->studiengang_kz?' selected="selected" ':'') .">$row->kurzbzlang, $row->bezeichnung </option>";
		}
		?>
    </select>
    Semester 
    <select name="semester">
      <?php
		for ($i=1;$i<9;$i++) 
		{
			if ($semester==$i)
				echo "<option value=\"$i\" selected>$i</option>";
			else
				echo "<option value=\"$i\">$i</option>";
		}
		?>
    </select>
    </p>
  <p>
    <input type="hidden" name="type" value="show">
    <input type="submit" name="Save" value="Ausf&uuml;hren">
  </p>
  <hr>
</form>
<?php

	$cfgBorder=1;
	$cfgBgcolorOne='#DAD8D8';
  $cfgBgcolorTwo='#ECECEC';


if ($type=="show")
{
	?>
	<table border="<?php echo $cfgBorder; ?>" name="Verteilung">
  	<tr>
		<th>Lehrfach</th>
		<th>Gesamt</th>
		<th><?php echo $stgbz."-".$semester; ?></th>
		<th><?php echo $stgbz."-".$semester."A"; ?></th>
		<th><?php echo $stgbz."-".$semester."A1"; ?></th>
		<th><?php echo $stgbz."-".$semester."A2"; ?></th>
		<th><?php echo $stgbz."-".$semester."B"; ?></th>
		<th><?php echo $stgbz."-".$semester."B1"; ?></th>
		<th><?php echo $stgbz."-".$semester."B2"; ?></th>
		<th><?php echo $stgbz."-".$semester."C"; ?></th>
		<th><?php echo $stgbz."-".$semester."C1"; ?></th>
		<th><?php echo $stgbz."-".$semester."C2"; ?></th>
	</tr>
	<?php

	// Selektieren der Lehrfaecher
	$sql_query="SELECT DISTINCT lehreinheit_id, lehreinheit_id as lehrfach_id,ort_kurzbz as kurzbz FROM lehre.tbl_stundenplan WHERE studiengang_kz='$stgid' AND semester='$semester'".$datum;
	$result_lehrfach=$db->db_query($sql_query);
	if(!$result_lehrfach) echo "stundenplan not found! ".$db->db_last_error().' line '. __LINE__ .' ; file ' . __FILE__."<br>";
	
	$foo = 0;


	while ($row=@$db->db_fetch_object($result_lehrfach, $foo))
	{
		$lehrfachid=$row->lehrfach_id;
		$bgcolor = $cfgBgcolorOne;
    $foo % 2  ? 0: $bgcolor = $cfgBgcolorTwo;
		echo "<tr bgcolor=".$bgcolor.">";
		
		$sql_query="SELECT * FROM lehre.tbl_lehrfach WHERE lehrfach_id='$lehrfachid'";

		if (!$result=$db->db_query($sql_query))
			 	die($sql_query.' '.$db->db_last_error().' line '. __LINE__ .' ; file ' . __FILE__."<br>");
		if ($db->db_num_rows($result)<1)
				die('keine Daten gefunden ');
				
		$row_lfbz=$db->db_fetch_object($result);

		echo "<td bgcolor=$bgcolor> $row_lfbz->kurzbz </td>";
		
		$sql_query="SELECT count(*) AS stunden FROM lehre.tbl_stundenplan WHERE studiengang_kz='$stgid' AND semester='$semester' AND lehreinheit_id='$lehrfachid'".$datum." GROUP BY lehreinheit_id";
		if (!$result=$db->db_query($sql_query))
			 	die($sql_query.' '.$db->db_last_error().' line '. __LINE__ .' ; file ' . __FILE__."<br>");


		if ($db->db_num_rows($result)>0)
			$row=$db->db_fetch_object ($result, 0);
		else
			$row->stunden=0;
		echo "<td bgcolor=$bgcolor>$row->stunden</td>";
		
		$sql_query="SELECT count(*) AS stunden FROM lehre.tbl_stundenplan WHERE studiengang_kz='$stgid' AND semester='$semester' AND verband='0' AND gruppe='0' AND lehreinheit_id='$lehrfachid'".$datum." GROUP BY lehreinheit_id";
		if (!$result=$db->db_query($sql_query))
			 	die($db->db_last_error().' line '. __LINE__ .' ; file ' . __FILE__."<br>");

		if ($db->db_num_rows($result)>0)
				$row=$db->db_fetch_object ($result, 0);
		else
				$row->stunden=0;
		echo "<td bgcolor=$bgcolor>$row->stunden</td>";
		
		$sql_query="SELECT count(*) AS stunden FROM lehre.tbl_stundenplan WHERE studiengang_kz='$stgid' AND semester='$semester' AND verband='A' AND gruppe='0' AND lehreinheit_id='$lehrfachid'".$datum." GROUP BY lehreinheit_id";
		if (!$result=$db->db_query($sql_query))
				 	die($db->db_last_error().' line '. __LINE__ .' ; file ' . __FILE__."<br>");
		if ($db->db_num_rows($result)>0)
				$row=$db->db_fetch_object ($result, 0);
		else
				$row->stunden=0;
		echo "<td bgcolor=$bgcolor>$row->stunden</td>";
		
		$sql_query="SELECT count(*) AS stunden FROM lehre.tbl_stundenplan WHERE studiengang_kz='$stgid' AND semester='$semester' AND verband='A' AND gruppe='1' AND lehreinheit_id='$lehrfachid'".$datum." GROUP BY lehreinheit_id";
		if (!$result=$db->db_query($sql_query))
			 	die($db->db_last_error().' line '. __LINE__ .' ; file ' . __FILE__."<br>");
		
		if ($db->db_num_rows($result)>0)
				$row=$db->db_fetch_object ($result, 0);
		else
				$row->stunden=0;
		echo "<td bgcolor=$bgcolor>$row->stunden</td>";
		
		$sql_query="SELECT count(*) AS stunden FROM lehre.tbl_stundenplan WHERE studiengang_kz='$stgid' AND semester='$semester' AND verband='A' AND gruppe='2' AND lehreinheit_id='$lehrfachid'".$datum." GROUP BY lehreinheit_id";
		if (!$result=$db->db_query($sql_query))
			 	die($db->db_last_error().' line '. __LINE__ .' ; file ' . __FILE__."<br>");

		
		if ($db->db_num_rows($result)>0)
			$row=$db->db_fetch_object ($result, 0);
		else
			$row->stunden=0;
		echo "<td bgcolor=$bgcolor>$row->stunden</td>";
		
		$sql_query="SELECT count(*) AS stunden FROM lehre.tbl_stundenplan WHERE studiengang_kz='$stgid' AND semester='$semester' AND verband='B' AND gruppe='0' AND lehreinheit_id='$lehrfachid'".$datum." GROUP BY lehreinheit_id";
		if (!$result=$db->db_query($sql_query))
			 	die($db->db_last_error().' line '. __LINE__ .' ; file ' . __FILE__."<br>");

		
		if ($db->db_num_rows($result)>0)
			$row=$db->db_fetch_object ($result, 0);
		else
			$row->stunden=0;
		echo "<td bgcolor=$bgcolor>$row->stunden</td>";
		
		$sql_query="SELECT count(*) AS stunden FROM lehre.tbl_stundenplan WHERE studiengang_kz='$stgid' AND semester='$semester' AND verband='B' AND gruppe='1' AND lehreinheit_id='$lehrfachid'".$datum." GROUP BY lehreinheit_id";
		if (!$result=$db->db_query($sql_query))
			 	die($db->db_last_error().' line '. __LINE__ .' ; file ' . __FILE__."<br>");

		
		if ($db->db_num_rows($result)>0)
			$row=$db->db_fetch_object ($result, 0);
		else
			$row->stunden=0;
		echo "<td bgcolor=$bgcolor>$row->stunden</td>";
		
		$sql_query="SELECT count(*) AS stunden FROM lehre.tbl_stundenplan WHERE studiengang_kz='$stgid' AND semester='$semester' AND verband='B' AND gruppe='2' AND lehreinheit_id='$lehrfachid'".$datum." GROUP BY lehreinheit_id";
		if (!$result=$db->db_query($sql_query))
			 	die($db->db_last_error().' line '. __LINE__ .' ; file ' . __FILE__."<br>");

		
		if ($db->db_num_rows($result)>0)
			$row=$db->db_fetch_object ($result, 0);
		else
			$row->stunden=0;
		echo "<td bgcolor=$bgcolor>$row->stunden</td>";
		
		$sql_query="SELECT count(*) AS stunden FROM lehre.tbl_stundenplan WHERE studiengang_kz='$stgid' AND semester='$semester' AND verband='C' AND gruppe='0' AND lehreinheit_id='$lehrfachid'".$datum." GROUP BY lehreinheit_id";
		if (!$result=$db->db_query($sql_query))
			 	die($db->db_last_error().' line '. __LINE__ .' ; file ' . __FILE__."<br>");

		
		if ($db->db_num_rows($result)>0)
			$row=$db->db_fetch_object ($result, 0);
		else
			$row->stunden=0;
		echo "<td bgcolor=$bgcolor>$row->stunden</td>";
		
		$sql_query="SELECT count(*) AS stunden FROM lehre.tbl_stundenplan WHERE studiengang_kz='$stgid' AND semester='$semester' AND verband='C' AND gruppe='1' AND lehreinheit_id='$lehrfachid'".$datum." GROUP BY lehreinheit_id";
		if (!$result=$db->db_query($sql_query))
			 	die($db->db_last_error().' line '. __LINE__ .' ; file ' . __FILE__."<br>");

		
		if ($db->db_num_rows($result)>0)
			$row=$db->db_fetch_object ($result, 0);
		else
			$row->stunden=0;
		echo "<td bgcolor=$bgcolor>$row->stunden</td>";
		
		$sql_query="SELECT count(*) AS stunden FROM lehre.tbl_stundenplan WHERE studiengang_kz='$stgid' AND semester='$semester' AND verband='C' AND gruppe='2' AND lehreinheit_id='$lehrfachid'".$datum." GROUP BY lehreinheit_id";
		if (!$result=$db->db_query($sql_query))
			 	die($db->db_last_error().' line '. __LINE__ .' ; file ' . __FILE__."<br>");

		
		if ($db->db_num_rows($result)>0)
			$row=$db->db_fetch_object ($result, 0);
		else
			$row->stunden=0;
		echo "<td bgcolor=$bgcolor>$row->stunden</td>";
		
		echo "</tr>\n";
		$foo++;
	}
	
}
?>
</table>
</body>
</html>