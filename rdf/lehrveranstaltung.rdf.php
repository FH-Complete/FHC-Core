<?php
/*
 * Created on 02.12.2004
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
// header fuer no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");
// xml
echo '<?xml version="1.0" encoding="ISO-8859-1" standalone="yes"?>';
// DAO
include_once('../vilesci/config.inc.php');
include_once('../include/functions.inc.php');
include_once('../include/lehrveranstaltung.class.php');

$uid=get_uid();

$error_msg='';

if (!$conn = @pg_pconnect(CONN_STRING))
   	die('Es konnte keine Verbindung zum Server aufgebaut werden!');

$error_msg.=loadVariables($conn,$uid);

if (isset($semester_aktuell))
	$studiensemester=$semester_aktuell;
else
	die('studiensemester is not set!');

if (isset($_GET['stg_kz']))
	$stg_kz=$_GET['stg_kz'];
else
	$stg_kz=null;
if (isset($_GET['sem']) && is_numeric($_GET['sem']))
	$sem=$_GET['sem'];
else
	$sem=null;
if(isset($_GET['uid']))
	$student_uid = $_GET['uid'];
else 
	$student_uid=null;

$lehrveranstaltung=new lehrveranstaltung($conn);

if($student_uid!='')
	$lehrveranstaltung->load_lva_student($student_uid);
else
	$lehrveranstaltung->load_lva($stg_kz,$sem);

$rdf_url='http://www.technikum-wien.at/lehrveranstaltung/';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:LVA="'.$rdf_url.'rdf#">

<RDF:Seq about="'.$rdf_url.'liste">
';

if(isset($_GET['optional']) && $_GET['optional']=='true')
{
	echo'<RDF:li>
      		<RDF:Description  id="" about="">
        		<LVA:lehrveranstaltung_id><![CDATA[]]></LVA:lehrveranstaltung_id>
        		<LVA:kurzbz><![CDATA[]]></LVA:kurzbz>
        		<LVA:bezeichnung><![CDATA[-- keine Auswahl --]]></LVA:bezeichnung>
        		<LVA:studiengang_kz><![CDATA[]]></LVA:studiengang_kz>
        		<LVA:semester><![CDATA[0]]></LVA:semester>
        		<LVA:sprache><![CDATA[]]></LVA:sprache>
        		<LVA:ects><![CDATA[]]></LVA:ects>
        		<LVA:semesterstunden><![CDATA[]]></LVA:semesterstunden>
        		<LVA:anmerkung><![CDATA[]]></LVA:anmerkung>
        		<LVA:lehre><![CDATA[]]></LVA:lehre>
        		<LVA:lehreverzeichnis><![CDATA[]]></LVA:lehreverzeichnis>
        		<LVA:aktiv><![CDATA[]]></LVA:aktiv>
        		<LVA:planfaktor><![CDATA[]]></LVA:planfaktor>
        		<LVA:planlektoren><![CDATA[]]></LVA:planlektoren>
        		<LVA:planpersonalkosten><![CDATA[]]></LVA:planpersonalkosten>
        		<LVA:plankostenprolektor><![CDATA[]]></LVA:plankostenprolektor>
        		<LVA:lehrform_kurzbz><![CDATA[]]></LVA:lehrform_kurzbz>
      		</RDF:Description>
		</RDF:li>';
}

foreach ($lehrveranstaltung->lehrveranstaltungen as $row)
{
	if(isset($_GET['projektarbeit']) && $row->projektarbeit==false)
	{
		if(isset($_GET['withlv']) && $_GET['withlv']==$row->lehrveranstaltung_id)
		{
			//Diese LV soll zusaetzlich in der liste aufscheinen unabhaengig ob
			//Projektarbeit gesetzt ist oder nicht
		}
		else
			continue;
	}
	
	echo'<RDF:li>
      		<RDF:Description  id="'.$row->lehrveranstaltung_id.'" about="'.$rdf_url.$row->lehrveranstaltung_id.'">
        		<LVA:lehrveranstaltung_id><![CDATA['.$row->lehrveranstaltung_id.']]></LVA:lehrveranstaltung_id>
        		<LVA:kurzbz><![CDATA['.$row->kurzbz.']]></LVA:kurzbz>
        		<LVA:bezeichnung><![CDATA['.$row->bezeichnung.']]></LVA:bezeichnung>
        		<LVA:studiengang_kz><![CDATA['.$row->studiengang_kz.']]></LVA:studiengang_kz>
        		<LVA:semester><![CDATA['.$row->semester.']]></LVA:semester>
        		<LVA:sprache><![CDATA['.$row->sprache.']]></LVA:sprache>
        		<LVA:ects><![CDATA['.$row->ects.']]></LVA:ects>
        		<LVA:semesterstunden><![CDATA['.$row->semesterstunden.']]></LVA:semesterstunden>
        		<LVA:anmerkung><![CDATA['.$row->anmerkung.']]></LVA:anmerkung>
        		<LVA:lehre><![CDATA['.($row->lehre?'Ja':'Nein').']]></LVA:lehre>
        		<LVA:lehreverzeichnis><![CDATA['.$row->lehreverzeichnis.']]></LVA:lehreverzeichnis>
        		<LVA:aktiv><![CDATA['.($row->aktiv?'Ja':'Nein').']]></LVA:aktiv>
        		<LVA:planfaktor><![CDATA['.$row->planfaktor.']]></LVA:planfaktor>
        		<LVA:planlektoren><![CDATA['.$row->planlektoren.']]></LVA:planlektoren>
        		<LVA:planpersonalkosten><![CDATA['.$row->planpersonalkosten.']]></LVA:planpersonalkosten>
        		<LVA:plankostenprolektor><![CDATA['.$row->plankostenprolektor.']]></LVA:plankostenprolektor>
        		<LVA:lehrform_kurzbz><![CDATA['.$row->lehrform_kurzbz.']]></LVA:lehrform_kurzbz>
      		</RDF:Description>
		</RDF:li>';
}
?>
</RDF:Seq>
</RDF:RDF>