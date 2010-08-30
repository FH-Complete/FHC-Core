<?php
/* Copyright (C) 2010 Technikum-Wien
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
 * Authors: Christian Paminger 		< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 */
/**
 * Scannt das documents Verzeichnis nach LV-Ordnern die nicht mehr benoetigt werden
 * 
 * Parameter:
 * stg_von ... Kennzahl ab der gescannt wird (inklusive)
 * stg_bis ... Kennzahl bis zu der gescannt wird (inklusive)
 */
require_once('../config/cis.config.inc.php');
require_once('../include/basis_db.class.php');

$text='';
$getstr='';

$lv_arr = array();
$db = new basis_db();

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>Unused Dirs</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
Zu den folgenden Verzeichnissen sind keine aktiven Lehrveranstaltungen vorhanden:<br><br>
';

$qry = "SELECT * FROM lehre.tbl_lehrveranstaltung";
if (isset($_REQUEST["stg_von"]))		
		$getstr .= " studiengang_kz >= '".$_REQUEST["stg_von"]."'";
if (isset($_REQUEST["stg_bis"]))
{
	if ($getstr != "")
		$getstr .= " AND";		
	$getstr .= " studiengang_kz <= '".$_REQUEST["stg_bis"]."'";
}
if ($getstr != "")
	$getstr = " WHERE".$getstr;
$qry.= ($getstr!=''?$getstr:' WHERE ').' AND tbl_lehrveranstaltung.aktiv ';

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$lv_arr[$row->studiengang_kz][$row->semester][$row->lehreverzeichnis] = 1;
	}
}

$sqlstr = "SELECT studiengang_kz, lower(typ||kurzbz) as stg, max_semester FROM public.tbl_studiengang";		
$sqlstr = $sqlstr.$getstr." ORDER BY typ, kurzbz";
	
if($result = $db->db_query($sqlstr))
{
	while($row = $db->db_fetch_object($result))
	{			
		echo "<hr><b>".$row->stg."</b><br>";
		for ($i=1; $i <= $row->max_semester; $i++)
		{				
			$dir = "../documents/".strtolower($row->stg)."/".$i."/";
			echo "*** ".$i." ***<br>";
			if (is_dir($dir))
			{		
				$files = scandir($dir);
				foreach ($files as $f)
				{
					if (is_dir($dir.$f) && $f != "." && $f != "..")
					{
                        if (!key_exists($f, $lv_arr[$row->studiengang_kz][$i]))
                        {
                                echo $dir.$f.'<br>';
                        }
					}
				}
			}
		}
	}
}

?>
</body>
</html>