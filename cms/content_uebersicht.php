<?php
/* Copyright (C) 2011 FH Technikum Wien
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
 *          Karl Burkhart <karl.burkhart@technikum-wien.at>.
 */
/**
 * Zeigt eine Übersichtsliste für alle Content-Einträge an
 */
require_once('../config/cis.config.inc.php');
require_once('../config/global.config.inc.php');
require_once('../include/content.class.php');
require_once('../include/template.class.php');
require_once('../include/functions.inc.php');
require_once('../include/phrasen.class.php');

$db = new basis_db();

echo '<html>
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
			<link href="../skin/style.css.php" rel="stylesheet" type="text/css">';

			include('../include/meta/jquery.php');
			include('../include/meta/jquery-tablesorter.php');

echo '	</head>
		<script type="text/javascript">
			// For correct sorting of Umlauts
			$.tablesorter.characterEquivalents = {
				"a" : "\u00e1\u00e0\u00e2\u00e3\u00e4\u0105\u00e5", // áàâãäąå
				"A" : "\u00c1\u00c0\u00c2\u00c3\u00c4\u0104\u00c5", // ÁÀÂÃÄĄÅ
				"c" : "\u00e7\u0107\u010d", // çćč
				"C" : "\u00c7\u0106\u010c", // ÇĆČ
				"e" : "\u00e9\u00e8\u00ea\u00eb\u011b\u0119", // éèêëěę
				"E" : "\u00c9\u00c8\u00ca\u00cb\u011a\u0118", // ÉÈÊËĚĘ
				"i" : "\u00ed\u00ec\u0130\u00ee\u00ef\u0131", // íìİîïı
				"I" : "\u00cd\u00cc\u0130\u00ce\u00cf", // ÍÌİÎÏ
				"o" : "\u00f3\u00f2\u00f4\u00f5\u00f6\u014d", // óòôõöō
				"O" : "\u00d3\u00d2\u00d4\u00d5\u00d6\u014c", // ÓÒÔÕÖŌ
				"ss": "\u00df", // ß (s sharp)
				"SS": "\u1e9e", // ẞ (Capital sharp s)
				"u" : "\u00fa\u00f9\u00fb\u00fc\u016f", // úùûüů
				"U" : "\u00da\u00d9\u00db\u00dc\u016e" // ÚÙÛÜŮ
			  };
			$.tablesorter.addParser({
				id: "customDate",
				is: function(s) {
					//return false;
					//use the above line if you don\'t want table sorter to auto detected this parser
					//else use the below line.
					//attention: doesn\'t check for invalid stuff
					//2009-77-77 77:77:77.0 would also be matched
					//if that doesn\'t suit you alter the regex to be more restrictive
					//return /\d{1,4}-\d{1,2}-\d{1,2} \d{1,2}:\d{1,2}:\d{1,2}\.\d+/.test(s);
					return /\d{1,4}-\d{1,2}-\d{1,2} \d{1,2}:\d{1,2}:\d{1,2} .*/.test(s);
				},
				format: function(s) {
					s = s.replace(/\-/g," ");
					s = s.replace(/:/g," ");
					s = s.replace(/\./g," ");
					s = s.split(" ");
					return $.tablesorter.formatFloat(new Date(s[0], s[1]-1, s[2], s[3], s[4], s[5]).getTime());
				},
				type: "numeric"
			});
			$(document).ready(function() 
			{ 
				$("#t1").tablesorter(
				{
					sortList: [[1,0],[0,1]],
					widgets: ["zebra", "filter", "stickyHeaders"],
					headers: { 5: { sorter: "customDate"}, 10: { sorter: "customDate"}},
					sortLocaleCompare : true
				}); 
			});
		</script>
		<style>
		table.tablesorter tbody td
		{
			padding: 5px;
		}
		</style>    
		<body>
		<div style="padding: 10px">
			<h1>Content-Übersicht</h1>';
		echo '<br><br><table class="tablesorter" id="t1"><thead>';
		echo '<tr>';
		echo '	<th>Content ID</th>
				<th>Titel</th>
				<th>Art</th>
				<th>OE</th>
				<th>Content-Aktiv</th>
				<th>Eingefügt</th>
				<th>Aktualisiert</th>
				<th>Verwendet in</th>
				<th>Sprache</th>
				<th>Version</th>
				<th>Eingefügt</th>
				<th>Aktualisiert</th>
				<th>Version sichtbar</th>';
		echo '</tr></thead><tbody>';

$qry = "
SELECT
	content.content_id,
	tbl_contentsprache.titel,
	content.template_kurzbz,
	content.oe_kurzbz,
	content.aktiv,
	content.insertamum||' von '||content.insertvon AS content_eingefuegt,
	content.updateamum||' von '||content.updatevon AS content_aktualisiert,
	(
		SELECT ARRAY_TO_STRING(array_agg(verwendung), '<br>') AS verwendung_in
			FROM (
				SELECT
					'Infoscreen ID'||infoscreen_id AS art
			    FROM campus.tbl_infoscreen_content
			    WHERE content_id=content.content_id
				
				UNION
				
				SELECT
					'Software ID'||software_id AS art
				FROM addon.tbl_software
				WHERE content_id=content.content_id
				
				UNION
				
				SELECT
						'Ort '||ort_kurzbz AS art
				FROM public.tbl_ort
				WHERE content_id=content.content_id
				
				UNION
				
				SELECT
						'Service ID'||service_id AS art
				FROM public.tbl_service
				WHERE content_id=content.content_id
				
				UNION
				
				SELECT
						'Statistik '||statistik_kurzbz AS art
				FROM public.tbl_statistik
				WHERE content_id=content.content_id
				
				UNION
				
				SELECT
						'Gebiet '||tbl_gebiet.bezeichnung||' in Studiengang '||tbl_ablauf.studiengang_kz AS art
				FROM testtool.tbl_ablauf_vorgaben
				JOIN testtool.tbl_ablauf USING (ablauf_vorgaben_id)
				JOIN testtool.tbl_gebiet USING (gebiet_id)
				WHERE content_id=content.content_id
				
				UNION
				
				SELECT
						'Verlinkung von '||content_id AS art
				FROM campus.tbl_contentsprache
				WHERE content::text LIKE '%content.php?content_id='||content.content_id
				/*WHERE content::text ~* '[\w\d\s]*content\.php\?content_id=1245[\w\d\s]*'*/
			) verwendung
	) AS verwendung_in,
	tbl_contentsprache.sprache,
	tbl_contentsprache.version,
	tbl_contentsprache.insertamum||' von '||tbl_contentsprache.insertvon AS contentsprache_eingefuegt,
	tbl_contentsprache.updateamum||' von '||tbl_contentsprache.updatevon AS contentsprache_aktualisiert,
	tbl_contentsprache.sichtbar
FROM campus.tbl_content content
	JOIN campus.tbl_contentsprache USING (content_id)
WHERE version=campus.get_highest_content_version (content.content_id)
AND template_kurzbz NOT IN ('news')
/*ORDER BY content_id ASC, sprache DESC, version DESC 
LIMIT 200*/";

if($db->db_query($qry))
{
	while ($row = $db->db_fetch_object())
	{
		echo '	<tr>
					<td>'.$row->content_id.'</td>
					<td>'.$row->titel.'</td>
					<td>'.$row->template_kurzbz.'</td>
					<td>'.$row->oe_kurzbz.'</td>
					<td>'.($row->aktiv == 't' ? 'Ja':'Nein').'</td>
					<td>'.$row->content_eingefuegt.'</td>
					<td>'.$row->content_aktualisiert.'</td>
					<td>'.$row->verwendung_in.'</td>
					<td>'.$row->sprache.'</td>
					<td>'.$row->version.'</td>
					<td>'.$row->contentsprache_eingefuegt.'</td>
					<td>'.$row->contentsprache_aktualisiert.'</td>
					<td>'.($row->sichtbar == 't' ? 'Ja':'Nein').'</td>
					
				</tr>';
	}
}
echo '</tbody></table>';
echo '	</div></body>
		</html>';


?>