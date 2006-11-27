<?php
/*
 * Created on 31-03-2006
 *
 * RDF fuer die Lehrfachverteilungen
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
include('../../vilesci/config.inc.php');
include('../../include/functions.inc.php');
include('../../include/fas/functions.inc.php');
include('../../include/fas/benutzer.class.php');
include('../../include/fas/lehreinheit.class.php');
include('../../include/fas/raumtyp.class.php');

error_reporting(E_ALL);
ini_set('display_errors','1');
// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

if (!$conn_fas = @pg_pconnect(CONN_STRING_FAS))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$studiengang_id = isset($_GET['studiengang_id'])?$_GET['studiengang_id']:'';
$lehreinheit_id = isset($_GET['lehreinheit_id'])?$_GET['lehreinheit_id']:'';
$gruppe_id = isset($_GET['gruppe_id'])?$_GET['gruppe_id']:'';

$user = get_uid();
$benutzer = new benutzer($conn);
if(!$benutzer->loadVariables($user))
	die("error:".$benutzer->errormsg);

// LVAs holen
$lvaDAO=new lehreinheit($conn_fas);
if($lehreinheit_id!='')
{
	if(!$lvaDAO->getLehreinheiten(null,null,null,$lehreinheit_id))
		die("error:".$lvaDAO->errormsg);
}
elseif($gruppe_id!='')
{
	if(!$lvaDAO->getLehreinheitenfromGruppe($gruppe_id, getStudiensemesterIdFromName($conn_fas, $benutzer->variable->semester_aktuell)))
		die("error:".$lvaDAO->errormsg);
}
else
{
	if(!$lvaDAO->getLehreinheiten($studiengang_id, null, getStudiensemesterIdFromName($conn_fas, $benutzer->variable->semester_aktuell),null,true))
		die("error:".$lvaDAO->errormsg);
}

$lehreinheiten = $lvaDAO->result;
$raumtyp_obj = new raumtyp($conn_fas);

$rdf_url='http://www.technikum-wien.at/lehreinheiten';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:LVA="<?php echo $rdf_url; ?>/rdf#"
>

<?php
if (is_array($lehreinheiten))
{
	$hier_arr = array();
	$currentLFK='';
	$currentLPK='';
	$descr = '';
	$hier = '';
	for ($i=0;$i<count($lehreinheiten);$i++)
	{
		$lva=$lehreinheiten[$i];
		$currentLFK=$lva->lehreinheit_fk;
		$currentLPK=$lva->lehreinheit_id;
		$descr.="
   <RDF:Description  id=\"".$lva->lehreinheit_id."\"  about=\"".$rdf_url.'/'.$lva->lehreinheit_id."\" >
      <LVA:lehreinheit_id>".$lva->lehreinheit_id."</LVA:lehreinheit_id>
      <LVA:studiengang_id>".$lva->studiengang_id."</LVA:studiengang_id>
      <LVA:studiengang_kurzbz><![CDATA[".$lva->studiengang_kurzbz."]]></LVA:studiengang_kurzbz>
      <LVA:studiensemester_id>".$lva->studiensemester_id."</LVA:studiensemester_id>
      <LVA:studiensemester_kurzbz>".$lva->studiensemester_kurzbz."</LVA:studiensemester_kurzbz>
      <LVA:lehrveranstaltung_id>".$lva->lehrveranstaltung_id."</LVA:lehrveranstaltung_id>
      <LVA:fachbereich_id>".$lva->fachbereich_id."</LVA:fachbereich_id>
      <LVA:fachbereich_bezeichnung><![CDATA[".$lva->fachbereich_bezeichnung."]]></LVA:fachbereich_bezeichnung>
      <LVA:ausbildungssemester_id>".$lva->ausbildungssemester_id."</LVA:ausbildungssemester_id>
      <LVA:ausbildungssemester_semester>".$lva->ausbildungssemester_semester."</LVA:ausbildungssemester_semester>
      <LVA:ausbildungssemester_kurzbz>".$lva->ausbildungssemester_kurzbz."</LVA:ausbildungssemester_kurzbz>
      <LVA:lehreinheit_fk>".$lva->lehreinheit_fk."</LVA:lehreinheit_fk>
      <LVA:lehrform_id>".$lva->lehrform_id."</LVA:lehrform_id>
      <LVA:lehrform_kurzbz>".$lva->lehrform_kurzbz."</LVA:lehrform_kurzbz>
      <LVA:gruppe_id>".$lva->gruppe_id."</LVA:gruppe_id>
      <LVA:gruppe_kurzbz>".$lva->gruppe_kurzbz."</LVA:gruppe_kurzbz>
      <LVA:nummer>".$lva->nummer."</LVA:nummer>
      <LVA:bezeichnung><![CDATA[".$lva->bezeichnung."]]></LVA:bezeichnung>
      <LVA:kurzbezeichnung><![CDATA[".$lva->kurzbezeichnung."]]></LVA:kurzbezeichnung>
      <LVA:semesterwochenstunden>".$lva->semesterwochenstunden."</LVA:semesterwochenstunden>
      <LVA:gesamtstunden>".$lva->gesamtstunden."</LVA:gesamtstunden>
      <LVA:plankostenprolektor>".$lva->plankostenprolektor."</LVA:plankostenprolektor>
      <LVA:planfaktor>".$lva->planfaktor."</LVA:planfaktor>
      <LVA:planlektoren>".$lva->planlektoren."</LVA:planlektoren>
      <LVA:raumtyp_id>".$lva->raumtyp_id."</LVA:raumtyp_id>";
		if($raumtyp_obj->load($lva->raumtyp_id))
			$bezeichnung = $raumtyp_obj->bezeichnung;
		else
			$bezeichnung = '';
		$descr.="
      <LVA:raumtyp_bezeichnung><![CDATA[".$bezeichnung."]]></LVA:raumtyp_bezeichnung>
      <LVA:raumtypalternativ_id>".$lva->raumtypalternativ_id."</LVA:raumtypalternativ_id>";
		if($raumtyp_obj->load($lva->raumtypalternativ_id))
			$bezeichnung = $raumtyp_obj->bezeichnung;
		else
			$bezeichnung = '';
		$descr.="
      <LVA:raumtypalternativ_bezeichnung><![CDATA[".$bezeichnung."]]></LVA:raumtypalternativ_bezeichnung>
      <LVA:bemerkungen><![CDATA[".$lva->bemerkungen."]]></LVA:bemerkungen>
      <LVA:wochenrythmus>".$lva->wochenrythmus."</LVA:wochenrythmus>
      <LVA:kalenderwoche>".$lva->start_kw."</LVA:kalenderwoche>
      <LVA:stundenblockung>".$lva->stundenblockung."</LVA:stundenblockung>
      <LVA:koordinator_id>".$lva->koordinator_id."</LVA:koordinator_id>
      <LVA:koordinator_nachname>".$lva->koordinator_nachname."</LVA:koordinator_nachname>
      <LVA:koordinator_vorname>".$lva->koordinator_vorname."</LVA:koordinator_vorname>
      <LVA:updateamum>".$lva->updateamum."</LVA:updateamum>
      <LVA:updatevon>".$lva->updatevon."</LVA:updatevon>
   </RDF:Description>
      		";

		if($currentLFK!=0 && $currentLFK!=-1)
		{
			$hier_arr[$currentLFK][] = $currentLPK;
		}
		else
		{
			if(!array_key_exists($currentLPK,$hier_arr))
				$hier_arr[$currentLPK]='';
		}
	}

	foreach ($hier_arr as $hkey=>$hval)
	{
		if(is_array($hier_arr[$hkey]))
		{
			$hier.="
      <RDF:li>
         <RDF:Seq about=\"".$rdf_url.'/'.$hkey."\" >";
			foreach ($hier_arr[$hkey] as $elem)
				$hier .= "
            <RDF:li resource=\"".$rdf_url.'/'.$elem."\" />";
			$hier.= "
         </RDF:Seq>
      </RDF:li>";
		}
		else
			$hier.="
      <RDF:li resource=\"".$rdf_url.'/'.$hkey."\" /> ";
	}

	$hier="
   <RDF:Seq about=\"".$rdf_url."/liste\">".$hier."
   </RDF:Seq>";
	echo $descr;
	echo $hier;
}
?>


</RDF:RDF>
