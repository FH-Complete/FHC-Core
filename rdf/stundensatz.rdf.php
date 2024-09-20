<?php
// header für no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
require_once('../config/vilesci.config.inc.php');
require_once('../include/stundensatz.class.php');


$rdf_url='http://www.technikum-wien.at/stundensatz';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:STUNDENSATZ="'.$rdf_url.'/rdf#"
>
	<RDF:Seq about="'.$rdf_url.'/liste">
';

$mitarbeiter_uid = isset($_GET['mitarbeiter_uid']) ? $_GET['mitarbeiter_uid'] : '';
$stundensatz_id = isset($_GET['stundensatz_id']) ? $_GET['stundensatz_id'] : '';

$stundensatz = new stundensatz();

if ($mitarbeiter_uid !== '')
{
	if (!$stundensatz->getAllStundensaetze($mitarbeiter_uid))
		die("Fehler beim Laden der Stundensaetze");
	
	foreach ($stundensatz->result as $row)
	{
		echo '
		<RDF:li>
			<RDF:Description  id="'.$row->stundensatz_id.'"  about="'.$rdf_url.'/'.$row->stundensatz_id.'" >
				<STUNDENSATZ:stundensatz_id><![CDATA['.$row->stundensatz_id.']]></STUNDENSATZ:stundensatz_id>
				<STUNDENSATZ:stundensatz><![CDATA['.$row->stundensatz.']]></STUNDENSATZ:stundensatz>
				<STUNDENSATZ:oe_kurzbz_bezeichnung><![CDATA['.($row->oe_bezeichnung).']]></STUNDENSATZ:oe_kurzbz_bezeichnung>
				<STUNDENSATZ:oe_kurzbz><![CDATA['. $row->oe_kurzbz .']]></STUNDENSATZ:oe_kurzbz>
				<STUNDENSATZ:stundensatztyp_bezeichnung><![CDATA['. $row->stundensatztyp_bezeichnung .']]></STUNDENSATZ:stundensatztyp_bezeichnung>
				<STUNDENSATZ:stundensatztyp><![CDATA['. $row->stundensatztyp .']]></STUNDENSATZ:stundensatztyp>
				<STUNDENSATZ:gueltig_von><![CDATA['.$row->gueltig_von.']]></STUNDENSATZ:gueltig_von>
				<STUNDENSATZ:gueltig_bis><![CDATA['.$row->gueltig_bis.']]></STUNDENSATZ:gueltig_bis>
			</RDF:Description>
		</RDF:li>
		';
	}
}
elseif ($stundensatz_id !== '')
{
	if (!$stundensatz->load($stundensatz_id))
		die("Fehler beim Laden des Stundensatzes");

	echo '
		<RDF:li>
			<RDF:Description  id="'.$stundensatz->stundensatz_id.'"  about="'.$rdf_url.'/'.$stundensatz->stundensatz_id.'" >
				<STUNDENSATZ:stundensatz_id><![CDATA['.$stundensatz->stundensatz_id.']]></STUNDENSATZ:stundensatz_id>
				<STUNDENSATZ:stundensatz><![CDATA['.$stundensatz->stundensatz.']]></STUNDENSATZ:stundensatz>
				<STUNDENSATZ:oe_kurzbz_bezeichnung><![CDATA['.($stundensatz->oe_bezeichnung).']]></STUNDENSATZ:oe_kurzbz_bezeichnung>
				<STUNDENSATZ:oe_kurzbz><![CDATA['. $stundensatz->oe_kurzbz .']]></STUNDENSATZ:oe_kurzbz>
				<STUNDENSATZ:stundensatztyp_bezeichnung><![CDATA['. $stundensatz->stundensatztyp_bezeichnung .']]></STUNDENSATZ:stundensatztyp_bezeichnung>
				<STUNDENSATZ:stundensatztyp><![CDATA['. $stundensatz->stundensatztyp .']]></STUNDENSATZ:stundensatztyp>
				<STUNDENSATZ:gueltig_von><![CDATA['.date_format(date_create($stundensatz->gueltig_von), 'd.m.Y').']]></STUNDENSATZ:gueltig_von>
				<STUNDENSATZ:gueltig_bis><![CDATA['. (is_null($stundensatz->gueltig_bis) ? '' : date_format(date_create($stundensatz->gueltig_bis), 'd.m.Y') ).']]></STUNDENSATZ:gueltig_bis>
			</RDF:Description>
		</RDF:li>
		';
}
?>
	</RDF:Seq>
</RDF:RDF>