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
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/betriebsmittel.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('basis/betriebsmittel'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$htmlstr = "";

if(isset($_GET['searchstr']))
	$searchstr = $_GET['searchstr'];
else
	$searchstr = '';
if(isset($_GET['typ']))
	$typ=$_GET['typ'];
else
	$typ='Zutrittskarte';

$htmlstr.='
<table width="100%">
<tr>
<td>
	<form accept-charset="UTF-8" name="search" method="GET">
		Bitte Suchbegriff eingeben:
		<input type="text" name="searchstr" size="30" value="'.$db->convert_html_chars($searchstr).'">
		<SELECT name="typ">
			<option value="Zutrittskarte" '.($typ=='Zutrittskarte'?'selected':'').'>Zutrittskarte</option>
			<option value="Schluessel" '.($typ=='Schluessel'?'selected':'').'>Schluessel</option>
		</SELECT>
		<input type="submit" value="Suchen">
	</form>
</td>
<td align="right">
	<form name="suche" method="POST" action="">
		Kartennummer (Lesegerät): <input type="text" value="" id="bmsuche" maxlength=12 size=12 name="bmsuche" tabindex="1"/>&nbsp;
		<input type="submit" name="submit" value="Suchen">
	</form>
</td>
</tr></table>';

if(isset($_GET['searchstr']) || isset($_POST['bmsuche']))
{
	$bm_obj = new betriebsmittel();
	$sql_query='';
	if (isset($_POST['bmsuche']))
	{
		$bmsuche=strtoupper($_POST['bmsuche']);
		$kartennummer = $bm_obj->transform_kartennummer($bmsuche);

		$sql_query="SELECT
						distinct on(tbl_betriebsmittelperson.betriebsmittelperson_id)
						tbl_betriebsmittel.*,
						tbl_betriebsmittelperson.*,
						tbl_person.vorname, tbl_person.nachname,
						tbl_benutzer.uid, tbl_betriebsmittelperson.uid as bmpuid
					FROM
						wawi.tbl_betriebsmittel
						JOIN wawi.tbl_betriebsmittelperson USING(betriebsmittel_id)
						JOIN public.tbl_person USING(person_id)
						LEFT JOIN public.tbl_benutzer USING(person_id)
					WHERE
						(
						upper(nummer) LIKE '%".$db->db_escape($kartennummer)."%'
						OR
						upper(nummer2) LIKE '%".$db->db_escape($kartennummer)."%'
						OR
						upper(nummer) LIKE '%".$db->db_escape($bmsuche)."%'
						OR
						upper(nummer2) LIKE '%".$db->db_escape($bmsuche)."%'
						)
						AND betriebsmitteltyp=".$db->db_add_param($typ)." LIMIT 30";
		//echo $sql_query;
	}
	elseif(!empty($searchstr))
	{

		$sql_query = '
			SELECT
				distinct on(tbl_betriebsmittelperson.betriebsmittelperson_id)
				tbl_betriebsmittel.*,
				tbl_betriebsmittelperson.*,
				tbl_person.vorname, tbl_person.nachname,
				tbl_benutzer.uid, tbl_betriebsmittelperson.uid as bmpuid
			FROM
				wawi.tbl_betriebsmittel
				JOIN wawi.tbl_betriebsmittelperson USING(betriebsmittel_id)
				JOIN public.tbl_person USING(person_id)
				LEFT JOIN public.tbl_benutzer USING(person_id)
			';

		//Wenn searchstring nur Hexwerte enthaelt, dann in kartennummer umwandeln
		if(preg_match("/^[A-F0-9]+$/", $searchstr))
			$kartennummer = $bm_obj->transform_kartennummer($searchstr);
		else
			$kartennummer='';
		$sql_query.=" WHERE
					(tbl_benutzer.uid  ~* ".$db->db_add_param($searchstr)."
					OR nummer  = ".$db->db_add_param($searchstr)."
					OR nummer  = ".$db->db_add_param($kartennummer)."
					OR nummer2  = ".$db->db_add_param($searchstr)."
					OR nummer2  = ".$db->db_add_param($kartennummer)."
					OR nachname  ~* ".$db->db_add_param($searchstr)."
					OR vorname  ~* ".$db->db_add_param($searchstr).") ";

		$sql_query.=" AND betriebsmitteltyp=".$db->db_add_param($typ);
	}
	//echo $sql_query;
	if($sql_query!='')
	{
	    if(!$erg=$db->db_query($sql_query))
		{
			$htmlstr='Fehler beim Laden der Daten';
		}
		else
		{
			$htmlstr .= "<table id='t1' class='tablesorter'><thead><tr>\n";
	    	$htmlstr .= "       <th>Typ</th>
	    						<th>Nummer</th>
	    						<th>Nummer2</th>
	    						<th>Person (UID)</th>
	    						<th>Ausgabe</th>
	    						<th>Retour</th>";
	    	$htmlstr .= "   </tr></thead><tbody>\n";
	    	$i = 0;

			while($row=$db->db_fetch_object($erg))
			{
				$htmlstr .= "   <tr>\n";
			    $htmlstr .= "       <td>".$row->betriebsmitteltyp."</td>\n";
				$htmlstr .= '       <td>
										<a href="betriebsmittel_details.php?betriebsmittel_id='.$db->convert_html_chars($row->betriebsmittel_id).'&betriebsmittelperson_id='.$db->convert_html_chars($row->betriebsmittelperson_id).'"
											target="betriebsmittel_details">'.$db->convert_html_chars($row->nummer)."</a></td>\n";
				$htmlstr .= '       <td>
										<a href="betriebsmittel_details.php?betriebsmittel_id='.$db->convert_html_chars($row->betriebsmittel_id).'&betriebsmittelperson_id='.$db->convert_html_chars($row->betriebsmittelperson_id).'"
											target="betriebsmittel_details">'.$db->convert_html_chars($row->nummer2)."</a></td>\n";
				$htmlstr .= "       <td>".$db->convert_html_chars($row->nachname.' '.$row->vorname)." &nbsp; ( ".$db->convert_html_chars($row->bmpuid)." )</td>\n";
		    	$htmlstr .= "       <td>".$db->convert_html_chars($row->ausgegebenam)."</td>\n";
				$htmlstr .= "       <td>".$db->convert_html_chars($row->retouram)."</td>\n";
		    	$htmlstr .= "   </tr>\n";
		    	$i++;
			}
		    $htmlstr .= "</tbody></table>\n";
		}
	}
}
?>
<html>
<head>
	<title>Betriebsmittel - Uebersicht</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link rel="stylesheet" href="../../skin/jquery.css" type="text/css"/>
	<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../vendor/jquery/sizzle/sizzle.js"></script>
	<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>

	<script type="text/javascript">
	<!--
		// $(document).ready(function()
		// 	{
		// 		$("#t1").tablesorter(
		// 		{
		// 			sortList: [[3,0]],
		// 			widgets: ["zebra"]
		// 		});
		// 	});
		//
		// document.getElementById('bmsuche').focus();
	//-->
	</script>
</head>

<body class="background_main">
<h2>Betriebsmittel &Uuml;bersicht</h2>

<?php
    echo $htmlstr;
?>

</body>
</html>
