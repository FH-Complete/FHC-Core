<?php
/* Copyright (C) 2014 Technikum-Wien
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
 * Authors: Nikolaus Krondraf <nikolaus.krondraf@technikum-wien.at>
 */

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');

$studiengang = new studiengang;
$studiengang->getAll("bezeichnung");

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//DE" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title>Anwesenheitslisten mit Barcodes</title>
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<!--<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">-->
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<!--<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>-->
	<script type="text/javascript" src="../../include/js/jquery.js"></script>
	<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
	<link href="../../skin/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css">
	<script src="../../include/js/jquery1.9.min.js" type="text/javascript"></script> 
	<script type="text/javascript">
	$(document).ready(function() 
		{ 
		    $(".datepicker").datepicker($.datepicker.regional['de']).datepicker("setDate", new Date());
		});
	</script>
</head>
<body class="Background_main">
<h2>Anwesenheitslisten mit Barcodes</h2>
	<form method="get" action="../../content/pdfExport.php?xsl=AnwListBarcode&output=pdf">
		<table>
		<tbody>
			<tr>
				<td>von</td>
				<td><input type="text" name="von" class="datepicker" id="von" /></td>
			</tr>
			<tr>
				<td>bis</td>
				<td><input type="text" name="bis" class="datepicker" id="bis" /></td>
			</tr>
			<tr>
				<td>Studiengang</td>
				<td>
					<select name="stg_kz">
						<?php foreach($studiengang->result as $value) echo "<option value='$value->studiengang_kz'>$value->bezeichnung</option>\n"; ?>
					</select>
				</td>
			</tr>
			<tr>
				<td>Ausbildungssemster</td>
				<td>
					<select name="ss">
						<?php for($x = 1; $x <= 10; $x++) echo "<option value='$x'>$x</option>\n"; ?>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2"><input type="submit" value="Liste erstellen" /></td>
			</tr>
		</tbody>
		</table>
		
		<input type="hidden" name="xsl" value="AnwListBarcode" />
		<input type="hidden" name="output" value="pdf" />
		<input type="hidden" name="xml" value="anwesenheitsliste.xml.php" />
	</form>
</body>
</html>

