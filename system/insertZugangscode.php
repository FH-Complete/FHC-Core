<?php

/* Copyright (C) 2012 FH Technikum-Wien
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
 * Authors: Karl Burkhart 	<burkhart@technikum-wien.at>
 */

require_once('../config/vilesci.config.inc.php');
require_once('../include/basis_db.class.php');

?>
<html>
	<head>
	<title>Check Studenten</title>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	
	<link rel="stylesheet" href="../skin/vilesci.css" type="text/css">
	</head>

	<body class="background_main">
		<h2>Zuweisung von Zugangscodes zu Personen</h2>

<?php

$db = new basis_db();
$count = 0;
$countError=0;

$qry="SELECT person_id FROM public.tbl_person WHERE zugangscode is null";

if($result = $db->db_query($qry))
{
    while($row = $db->db_fetch_object($result))
    {
        $qry_zugangscode = "UPDATE public.tbl_person Set zugangscode ='".uniqid()."' where person_id = '".$row->person_id."'";
        if($db->db_query($qry_zugangscode))
            $count+=1; 
        else
            $countError+=1;
    }
}
else
{
    die('Es ist ein fehler bei der Abfrage aufgetreten');
}

echo $count." Datensätze wurden upgedatet dabei sind ".$countError." Fehler aufgetreten";
?>

    </body>
    </html>
