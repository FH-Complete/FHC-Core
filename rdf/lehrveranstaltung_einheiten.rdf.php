<?php
/*
 * Created on 02.12.2004
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
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
require_once('../include/lehrveranstaltung.class.php');
require_once('../include/lehreinheit.class.php');
require_once('../include/functions.inc.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

//pg_query($conn, "SET CLIENT_ENCODING to 'UNICODE'");
$user = get_uid();
/*
// test
$einheit_kurzbz='';
$grp='1';
$ver='A';
$sem=6;
$stg_kz=257;

*/
$hier='';
$einheit_kurzbz=(isset($_GET['einheit'])?$_GET['einheit']:'');
$grp=(isset($_GET['grp'])?$_GET['grp']:'');
$ver=(isset($_GET['ver'])?$_GET['ver']:'');
$sem=(isset($_GET['sem'])?$_GET['sem']:'');
$stg_kz=(isset($_GET['stg_kz'])?$_GET['stg_kz']:'');
$lektor=(isset($_GET['lektor'])?$_GET['lektor']:'');


loadVariables($conn, $user);
// LVAs holen
$lvaDAO=new lehrveranstaltung($conn, null, true);
$lvaDAO->load_lva($stg_kz, $sem);

$rdf_url='http://www.technikum-wien.at/lehrveranstaltung_einheiten';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:LVA="<?php echo $rdf_url; ?>/rdf#"
>

<?php

	foreach ($lvaDAO->lehrveranstaltungen as $row_lva)
	{		
		//Lehrveranstaltung
		echo "
      		<RDF:Description  id=\"".$row_lva->lehrveranstaltung_id."\"  about=\"".$rdf_url.'/'.$row_lva->lehrveranstaltung_id."\" >
				<LVA:lehrveranstaltung_id>".$row_lva->lehrveranstaltung_id."</LVA:lehrveranstaltung_id>
				<LVA:kurzbz><![CDATA[".$row_lva->kurzbz."]]></LVA:kurzbz>
				<LVA:bezeichnung><![CDATA[".$row_lva->bezeichnung."]]></LVA:bezeichnung>
				<LVA:studiengang_kz>".$row_lva->studiengang_kz."</LVA:studiengang_kz>
				<LVA:semester>".$row_lva->semester."</LVA:semester>
    			<LVA:sprache><![CDATA[".$row_lva->sprache."]]></LVA:sprache>
				<LVA:ects>".$row_lva->ects."</LVA:ects>
				<LVA:semesterstunden>".$row_lva->semesterstunden."</LVA:semesterstunden>
				<LVA:anmerkung><![CDATA[".$row_lva->anmerkung."]]></LVA:anmerkung>
				<LVA:lehre>".($row_lva->lehre?'Ja':'Nein')."</LVA:lehre>
				<LVA:lehreverzeichnis><![CDATA[".$row_lva->lehreverzeichnis."]]></LVA:lehreverzeichnis>
				<LVA:aktiv>".($row_lva->aktiv?'Ja':'Nein')."</LVA:aktiv>
				<LVA:planfaktor>".$row_lva->planfaktor."</LVA:planfaktor>
				<LVA:planlektoren>".$row_lva->planlektoren."</LVA:planlektoren>
				<LVA:planpersonalkosten>".$row_lva->planpersonalkosten."</LVA:planpersonalkosten>
				<LVA:plankostenprolektor>".$row_lva->plankostenprolektor."</LVA:plankostenprolektor>
				
				<LVA:lehreinheit_id></LVA:lehreinheit_id>
				<LVA:lehrform_kurzbz></LVA:lehrform_kurzbz>
				<LVA:stundenblockung></LVA:stundenblockung>
				<LVA:wochenrythmus></LVA:wochenrythmus>
				<LVA:startkw></LVA:startkw>
				<LVA:raumtyp></LVA:raumtyp>
				<LVA:raumtypalternativ></LVA:raumtypalternativ>
				<LVA:gruppen></LVA:gruppen>
				<LVA:lektoren></LVA:lektoren>				
      		</RDF:Description>";

		$hier.="      	
      	<RDF:li>
      		<RDF:Seq about=\"".$rdf_url.'/'.$row_lva->lehrveranstaltung_id."\" >";
		
		//zugehoerige LE holen
		$le = new lehreinheit($conn, null, true);
				
		if(!$le->load_lehreinheiten($row_lva->lehrveranstaltung_id, $semester_aktuell))
			echo "Fehler: $le->errormsg";
		
		foreach ($le->lehreinheiten as $row_le)
		{			
			//Lehrfach holen
			$qry = "SELECT kurzbz, bezeichnung FROM lehre.tbl_lehrfach WHERE lehrfach_id='$row_le->lehrfach_id'";
			$result_lf = pg_query($conn, $qry);
			$row_lf = pg_fetch_object($result_lf);
			
			//Gruppen holen
			$qry = "SELECT upper(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as kuerzel, * FROM lehre.tbl_lehreinheitgruppe LEFT JOIN public.tbl_studiengang USING(studiengang_kz) WHERE lehreinheit_id='$row_le->lehreinheit_id'";
			$result_grp = pg_query($conn, $qry);
			$grp='';
			while($row_grp=pg_fetch_object($result_grp))
			{
				if($row_grp->gruppe_kurzbz=='')
					$grp.=' '.$row_grp->kuerzel.trim($row_grp->semester).trim($row_grp->verband).trim($row_grp->gruppe);
				else 
					$grp.=' '.$row_grp->gruppe_kurzbz;					
			}
			//Lektoren holen
			$qry = "SELECT * FROM lehre.tbl_lehreinheitmitarbeiter JOIN public.tbl_mitarbeiter USING(mitarbeiter_uid) WHERE lehreinheit_id='$row_le->lehreinheit_id'";
			$result_lkt = pg_query($conn, $qry);
			$lkt='';
			while($row_lkt = pg_fetch_object($result_lkt))
				$lkt.=$row_lkt->kurzbz.' ';
			
			echo "
      		<RDF:Description  id=\"".$row_le->lehreinheit_id."\"  about=\"".$rdf_url.'/'.$row_lva->lehrveranstaltung_id."/$row_le->lehreinheit_id\" >
				<LVA:lehrveranstaltung_id>".$row_lva->lehrveranstaltung_id."</LVA:lehrveranstaltung_id>
				<LVA:kurzbz><![CDATA[".$row_lf->kurzbz."]]></LVA:kurzbz>
				<LVA:bezeichnung><![CDATA[".$row_lf->bezeichnung."]]></LVA:bezeichnung>
				<LVA:studiengang_kz>".$row_lva->studiengang_kz."</LVA:studiengang_kz>
				<LVA:semester>".$row_lva->semester."</LVA:semester>
    			<LVA:sprache><![CDATA[".$row_le->sprache."]]></LVA:sprache>
				<LVA:ects></LVA:ects>
				<LVA:semesterstunden></LVA:semesterstunden>
				<LVA:anmerkung><![CDATA[".$row_le->anmerkung."]]></LVA:anmerkung>
				<LVA:lehre>".($row_le->lehre?'Ja':'Nein')."</LVA:lehre>
				<LVA:lehreverzeichnis></LVA:lehreverzeichnis>
				<LVA:aktiv></LVA:aktiv>
				<LVA:planfaktor></LVA:planfaktor>
				<LVA:planlektoren></LVA:planlektoren>
				<LVA:planpersonalkosten></LVA:planpersonalkosten>
				<LVA:plankostenprolektor></LVA:plankostenprolektor>
				
				<LVA:lehreinheit_id>$row_le->lehreinheit_id</LVA:lehreinheit_id>
				<LVA:studiensemester_kurzbz>$row_le->studiensemester_kurzbz</LVA:studiensemester_kurzbz>
				<LVA:lehrfach_id>$row_le->lehrfach_id</LVA:lehrfach_id>
				<LVA:lehrform_kurzbz>$row_le->lehrform_kurzbz</LVA:lehrform_kurzbz>
				<LVA:stundenblockung>$row_le->stundenblockung</LVA:stundenblockung>
				<LVA:wochenrythmus>$row_le->wochenrythmus</LVA:wochenrythmus>
				<LVA:startkw>$row_le->start_kw</LVA:startkw>
				<LVA:raumtyp>$row_le->raumtyp</LVA:raumtyp>
				<LVA:raumtypalternativ>$row_le->raumtypalternativ</LVA:raumtypalternativ>
				<LVA:anmerkung><![CDATA[$row_le->anmerkung]]></LVA:anmerkung>
				<LVA:unr>$row_le->unr</LVA:unr>
				<LVA:lvnr>$row_le->lvnr</LVA:lvnr>				
				<LVA:gruppen><![CDATA[$grp]]></LVA:gruppen>
				<LVA:lektoren><![CDATA[".$lkt."]]></LVA:lektoren>
      		</RDF:Description>";
			
			$hier.="
			<RDF:li resource=\"".$rdf_url.'/'.$row_lva->lehrveranstaltung_id.'/'.$row_le->lehreinheit_id."\" />";
		}
		//<RDF:li resource=\"".$rdf_url.'/'.$row_lva->lehrveranstaltung_id."\" />
		$hier.="			
      		</RDF:Seq>
      	</RDF:li>";
		
	}

	$hier="
  	<RDF:Seq about=\"".$rdf_url."/liste\">".$hier."
  	</RDF:Seq>";

	echo $hier;
?>


</RDF:RDF>
