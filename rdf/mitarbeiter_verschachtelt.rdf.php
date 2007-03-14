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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
// header fuer no cache
header("Cache-Control: no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/vnd.mozilla.xul+xml");
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
require_once('../vilesci/config.inc.php');
require_once('../include/functions.inc.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$user = get_uid();

loadVariables($conn, $user);
// LVAs holen

$rdf_url='http://www.technikum-wien.at/ma';
$alle='';
$fix='';
$frei='';
$stgl='';
$fbl='';
pg_query($conn, "SET CLIENT_ENCODING to 'UNICODE'");
$qry = "SELECT * FROM campus.vw_mitarbeiter ORDER BY nachname, vorname";
$result = pg_query($conn, $qry);
?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:MA="<?php echo $rdf_url; ?>/rdf#"
>
<RDF:Description  id="_alle"  about="<?php echo $rdf_url.'/_alle'; ?>" >
	<MA:bezeichnung><![CDATA[Alle]]></MA:bezeichnung>
	<MA:uid></MA:uid>
	<MA:vorname></MA:vorname>
	<MA:nachname></MA:nachname>
</RDF:Description>
<RDF:Description  id="_fix"  about="<?php echo $rdf_url.'/_fix'; ?>" >
	<MA:bezeichnung><![CDATA[Fix Angestelte]]></MA:bezeichnung>
	<MA:uid></MA:uid>
	<MA:vorname></MA:vorname>
	<MA:nachname></MA:nachname>
</RDF:Description>
<RDF:Description  id="_frei"  about="<?php echo $rdf_url.'/_frei'; ?>" >
	<MA:bezeichnung><![CDATA[Frei Angestellte]]></MA:bezeichnung>
	<MA:uid></MA:uid>
	<MA:vorname></MA:vorname>
	<MA:nachname></MA:nachname>
</RDF:Description>
<RDF:Description  id="_stgl"  about="<?php echo $rdf_url.'/_stgl'; ?>" >
	<MA:bezeichnung><![CDATA[Studiengangsleiter]]></MA:bezeichnung>
	<MA:uid></MA:uid>
	<MA:vorname></MA:vorname>
	<MA:nachname></MA:nachname>
</RDF:Description>
<RDF:Description  id="_fbl"  about="<?php echo $rdf_url.'/_fbl'; ?>" >
	<MA:bezeichnung><![CDATA[Fachbereichsleiter]]></MA:bezeichnung>
	<MA:uid></MA:uid>
	<MA:vorname></MA:vorname>
	<MA:nachname></MA:nachname>
</RDF:Description>

<?php

	//Mitarbeiter
	while($row=pg_fetch_object($result))
	{		
		//Lehrveranstaltung
		echo "
	<RDF:Description  id=\"".$row->uid."\"  about=\"".$rdf_url.'/'.$row->uid."\" >
		<MA:bezeichnung><![CDATA[".$row->kurzbz."]]></MA:bezeichnung>
		<MA:uid><![CDATA[".$row->uid."]]></MA:uid>
		<MA:vorname><![CDATA[".$row->vorname."]]></MA:vorname>
		<MA:nachname><![CDATA[".$row->nachname."]]></MA:nachname>
	</RDF:Description>";

		//Alle
		$alle.="\n\t\t<RDF:li resource=\"".$rdf_url.'/'.$row->uid."\" />";
		
		if($row->fixangestellt=='t')
			$fix.="\n\t\t<RDF:li resource=\"".$rdf_url.'/'.$row->uid."\" />"; //Fixangestellte
		else 
			$frei.="\n\t\t<RDF:li resource=\"".$rdf_url.'/'.$row->uid."\" />"; //Freie
	}
	//Studiengangsleiter
	$qry = "SELECT * FROM public.tbl_benutzerfunktion WHERE funktion_kurzbz='stgl'";
	$result = pg_query($conn, $qry);
	while($row = pg_fetch_object($result))
		$stgl.= "\n\t\t<RDF:li resource=\"".$rdf_url.'/'.$row->uid."\" />";
		
	//Fachbereichsleiter
	$qry = "SELECT * FROM public.tbl_benutzerfunktion WHERE funktion_kurzbz='fbl'";
	$result = pg_query($conn, $qry);
	while($row = pg_fetch_object($result))
		$fbl.= "\n\t\t<RDF:li resource=\"".$rdf_url.'/'.$row->uid."\" />";
		
	echo "
<RDF:Seq about=\"".$rdf_url."/liste\" >
	<RDF:li>
		<RDF:Seq about=\"".$rdf_url."/_alle\" >$alle
		</RDF:Seq>
	</RDF:li>
	<RDF:li>
		<RDF:Seq about=\"".$rdf_url."/_fix\" >$fix
		</RDF:Seq>
	</RDF:li>
	<RDF:li>
		<RDF:Seq about=\"".$rdf_url."/_frei\" >$frei
		</RDF:Seq>
	</RDF:li>
	<RDF:li>
		<RDF:Seq about=\"".$rdf_url."/_stgl\" >$stgl
		</RDF:Seq>
	</RDF:li>
	<RDF:li>
		<RDF:Seq about=\"".$rdf_url."/_fbl\" >$fbl
		</RDF:Seq>
	</RDF:li>
</RDF:Seq>
";
?>


</RDF:RDF>
