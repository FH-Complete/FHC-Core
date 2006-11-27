<?php
/**
 * erstellt ein RDF File mit den Funktionen
 * Created on 23.3.2006
 * Aufruf: funktionen.rdf.php?mitarbeiter_id=xyz
 *         funktionen.rdf.php?funktion_id=xyz
 *		   funktionen.rdf.php?mitarbeiter_id=xyz&allesemester=true
 *
 * Parameter &leerzeichencodierung=true Sendet statt Leerfeldern &#xA0;
 * damit die leerfelder im XUL Tree richtig sortiert werden.
 */
// header fuer no cache
header("Cache-Control: no-cache");
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
include('../../include/fas/funktion.class.php');
include('../../include/fas/studiensemester.class.php');
include('../../include/fas/studiengang.class.php');
include('../../include/fas/fachbereich.class.php');
include('../../include/fas/benutzer.class.php');


// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING_FAS))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

if (!$conn_calva = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$user = get_uid();

$rdf_url='http://www.technikum-wien.at/funktionen';

function addCDATA($str)
{
	return ($str=='&#xA0;'?'&#xA0;':'<![CDATA['.$str.']]>');
}
?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:NC="http://home.netscape.com/NC-rdf#" 
	xmlns:FUNKTIONEN="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq RDF:about="<?php echo $rdf_url ?>/alle">
<?php
if(isset($_GET['leerzeichencodierung']))
	$leerzeichencodierung = true;
else 
	$leerzeichencodierung = false;
//Parameter holen
if(isset($_GET['mitarbeiter_id']))
{
	$pers_id = $_GET['mitarbeiter_id'];
	
	//Wenn der Parameter allesemester uebergeben wird, dann werden die
	//Funktionen aus allen Studiensemestern zurueckgeliefert.
	//Sonst nur die aus dem aktuell ausgewaehlten
	if(!isset($_GET['allesemester']))
	{
		$benutzer = new benutzer($conn_calva);
		if(!$benutzer->loadVariables($user))
			die("error:".$benutzer->errormsg);
			
		$stsem = $benutzer->variable->semester_aktuell;
			
		$qry = "SELECT studiensemester_pk from studiensemester where art=";
		if(substr($stsem,0,2)=='WS')
			$qry .="1";
		else 
			$qry .="2";
		$qry .= " AND jahr=";
		$qry .= substr($stsem,2,4);
		$stsem_id='';

		if($result=pg_query($conn,$qry))
			if($row=pg_fetch_object($result))
				$stsem_id=$row->studiensemester_pk;
	}
	else
		$stsem_id='';

	// Funktionen holen
	$funktionenDAO=new funktion($conn);
	$funktionenDAO->load_pers($mitarbeiter_id, $stsem_id);
	
	foreach ($funktionenDAO->result as $funktionen)
	{
		
		if($leerzeichencodierung)
		{
			if ($funktion->studiensemester_id=='') $funktion->studiensemester_id='&#xA0;';
			if ($funktion->studiengang_id=='') $funktion->studiengang_id='&#xA0;';
			if ($funktion->studiengang_id=='') $funktion->studiengang_id='&#xA0;';
			if ($funktion->fachbereich_id=='') $funktion->fachbereich_id='&#xA0;';
			if ($funktion->name=='') $funktion->name='&#xA0;';
			if ($funktion->funktion=='') $funktion->funktion='&#xA0;';
			if ($funktion->beschart1=='') $funktion->beschart1='&#xA0;';
			if ($funktion->beschart2='') $funktion->beschart2='&#xA0;';
			if ($funktion->verwendung='') $funktion->verwendung='&#xA0;';
			if ($funktion->hauptberuf='') $funktion->hauptberuf='&#xA0;';
			if ($funktion->hauptberuflich='') $funktion->hauptberuflich='&#xA0;';
			if ($funktion->entwicklungsteam='') $funktion->entwicklungsteam='&#xA0;';
			if ($funktion->besonderequalifikation='') $funktion->besonderequalifikation='&#xA0;';
			if ($funktion->ausmass='') $funktion->ausmass='&#xA0;';
			
		}
?>
	      <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/'.$funktionen->funktion_id; ?>" >
	        	<FUNKTIONEN:funktion_id><?php echo $funktionen->funktion_id;  ?></FUNKTIONEN:funktion_id>
	        	<FUNKTIONEN:mitarbeiter_id><?php echo $funktionen->mitarbeiter_id;  ?></FUNKTIONEN:mitarbeiter_id>
	        	<FUNKTIONEN:studiensemester_id NC:parseType="Integer"><?php echo $funktionen->studiensemester_id;  ?></FUNKTIONEN:studiensemester_id>
<?php
	        		$stsem_obj = new studiensemester($conn);
	        		if(!$stsem_obj->load($funktionen->studiensemester_id))
	        			echo $stsem_obj->errormsg;
	        		$bezeichnung = ($stsem_obj->art=='1'?'WS':'SS').$stsem_obj->jahr;
	        		if($leerzeichencodierung && $bezeichnung =='')
	        			$bezeichnung = '&#xA0;';
?>
	        	<FUNKTIONEN:studiensemester_bezeichnung><?php echo $bezeichnung;  ?></FUNKTIONEN:studiensemester_bezeichnung>
	        	<FUNKTIONEN:erhalter_id><?php echo ($funktionen->erhalter_id=='1'?'Technikum Wien':'unbekannt');  ?></FUNKTIONEN:erhalter_id>
	        	<FUNKTIONEN:studiengang_id><?php echo $funktionen->studiengang_id;  ?></FUNKTIONEN:studiengang_id>
<?php
	        		$stg_obj = new studiengang($conn);
	        		$stg_obj->load($funktionen->studiengang_id);
	        		$bezeichnung = $stg_obj->kuerzel;
	        		if($stg_obj->studiengangsart==1)
	        			$bezeichnung = '(B)'.$bezeichnung;
	        		elseif($stg_obj->studiengangsart==2)
	        			$bezeichnung = '(M)'.$bezeichnung;
	        		elseif($stg_obj->studiengangsart==3)
	        			$bezeichnung = '(D)'.$bezeichnung;
	        		if($leerzeichencodierung && $bezeichnung =='')
	        			$bezeichnung = '&#xA0;';
?>
	        	<FUNKTIONEN:studiengang_bezeichnung><?php echo $bezeichnung;  ?></FUNKTIONEN:studiengang_bezeichnung>
	        	<FUNKTIONEN:fachbereich_id><?php echo $funktionen->fachbereich_id;  ?></FUNKTIONEN:fachbereich_id>
<?php
	        		$fachb_obj = new fachbereich($conn);
	        		$fachb_obj->load($funktionen->fachbereich_id);
	        		$bezeichnung = $fachb_obj->name;
	        		if($leerzeichencodierung && $bezeichnung =='')
	        			$bezeichnung = '&#xA0;';
?>
	        	<FUNKTIONEN:fachbereich_bezeichnung><?php echo $bezeichnung;  ?></FUNKTIONEN:fachbereich_bezeichnung>
	        	<FUNKTIONEN:name><?php echo $funktionen->name;  ?></FUNKTIONEN:name>
	        	<FUNKTIONEN:funktion><?php echo $funktionen->funktion;  ?></FUNKTIONEN:funktion>
<?php
	        		$fkt_obj = new funktion($conn);
	        		$bezeichnung = $fkt_obj->getNameFunktion($funktionen->funktion);
	        		if($leerzeichencodierung && $bezeichnung =='')
	        			$bezeichnung = '&#xA0;';
?>
	        	<FUNKTIONEN:funktion_bezeichnung><?php echo $bezeichnung;  ?></FUNKTIONEN:funktion_bezeichnung>
	        	<FUNKTIONEN:beschart1><?php echo $funktionen->beschart1;  ?></FUNKTIONEN:beschart1>
<?php
	        		$fkt_obj = new funktion($conn);
	        		$bezeichnung = $fkt_obj->getNameBeschart1($funktionen->beschart1);
	        		if($leerzeichencodierung && $bezeichnung =='')
	        			$bezeichnung = '&#xA0;';
?>
	        	<FUNKTIONEN:beschart1_bezeichnung><?php echo $bezeichnung;  ?></FUNKTIONEN:beschart1_bezeichnung>
	        	<FUNKTIONEN:beschart2><?php echo $funktionen->beschart2;  ?></FUNKTIONEN:beschart2>
<?php
	        		$fkt_obj = new funktion($conn);
	        		$bezeichnung = $fkt_obj->getNameBeschart2($funktionen->beschart2);
	        		if($leerzeichencodierung && $bezeichnung =='')
	        			$bezeichnung = '&#xA0;';
?>
	        	<FUNKTIONEN:beschart2_bezeichnung><?php echo $bezeichnung;  ?></FUNKTIONEN:beschart2_bezeichnung>	        	
	        	<FUNKTIONEN:verwendung><?php echo $funktionen->verwendung;  ?></FUNKTIONEN:verwendung>
<?php
	        		$fkt_obj = new funktion($conn);
	        		$bezeichnung = $fkt_obj->getNameVerwendung($funktionen->verwendung);
	        		if($leerzeichencodierung && $bezeichnung =='')
	        			$bezeichnung = '&#xA0;';
?>
	        	<FUNKTIONEN:verwendung_bezeichnung><?php echo $bezeichnung;  ?></FUNKTIONEN:verwendung_bezeichnung>
	        	<FUNKTIONEN:hauptberuf><?php echo $funktionen->hauptberuf;  ?></FUNKTIONEN:hauptberuf>
<?php
	        		$fkt_obj = new funktion($conn);
	        		$bezeichnung = $fkt_obj->getNameHauptberuf($funktionen->hauptberuf);
	        		if($leerzeichencodierung && $bezeichnung =='')
	        			$bezeichnung = '&#xA0;';
?>
				<FUNKTIONEN:hauptberuf_bezeichnung><?php echo $bezeichnung;  ?></FUNKTIONEN:hauptberuf_bezeichnung>
	        	<FUNKTIONEN:hauptberuflich><?php echo ($funktionen->hauptberuflich?'Ja':'Nein');  ?></FUNKTIONEN:hauptberuflich>
	        	<FUNKTIONEN:entwicklungsteam><?php echo ($funktionen->entwicklungsteam?'Ja':'Nein');  ?></FUNKTIONEN:entwicklungsteam>
	        	<FUNKTIONEN:besonderequalifikation><?php echo $funktionen->besonderequalifikation;  ?></FUNKTIONEN:besonderequalifikation>
<?php
	        		$fkt_obj = new funktion($conn);
	        		$bezeichnung = $fkt_obj->getNameBesonderequalifikation($funktionen->besonderequalifikation);
	        		if($leerzeichencodierung && $bezeichnung =='')
	        			$bezeichnung = '&#xA0;';
?>
	        	<FUNKTIONEN:besonderequalifikation_bezeichnung><?php echo $bezeichnung;  ?></FUNKTIONEN:besonderequalifikation_bezeichnung>
	        	<FUNKTIONEN:ausmass><?php echo $funktionen->ausmass;  ?></FUNKTIONEN:ausmass>	    		
<?php
	        		$fkt_obj = new funktion($conn);
	        		$bezeichnung = $fkt_obj->getNameAusmass($funktionen->ausmass);
	        		if($leerzeichencodierung && $bezeichnung =='')
	        			$bezeichnung = '&#xA0;';
?>	        	
	        	<FUNKTIONEN:ausmass_bezeichnung><?php echo addCDATA($bezeichnung);  ?></FUNKTIONEN:ausmass_bezeichnung>
	      	</RDF:Description>
	      </RDF:li>
<?php
	
	}
}
elseif(isset($_GET['funktion_id']))
{
	$funktion_id = $_GET['funktion_id'];

	// Bankverbindungen holen
	$funktionenDAO=new funktion($conn);
	$funktionenDAO->load($funktion_id);
	?>
		  <RDF:li>
	      	<RDF:Description RDF:about="<?php echo $rdf_url.'/'.$funktionenDAO->funktion_id; ?>" >
	        	<FUNKTIONEN:funktion_id><?php echo $funktionenDAO->funktion_id;  ?></FUNKTIONEN:funktion_id>
	        	<FUNKTIONEN:mitarbeiter_id><?php echo $funktionenDAO->mitarbeiter_id;  ?></FUNKTIONEN:mitarbeiter_id>
	        	<FUNKTIONEN:studiensemester_id  NC:parseType="Integer"><?php echo $funktionenDAO->studiensemester_id;  ?></FUNKTIONEN:studiensemester_id>
	        	<FUNKTIONEN:erhalter_id><?php echo $funktionenDAO->erhalter_id;  ?></FUNKTIONEN:erhalter_id>
	        	<FUNKTIONEN:studiengang_id><?php echo $funktionenDAO->studiengang_id;  ?></FUNKTIONEN:studiengang_id>
	        	<FUNKTIONEN:fachbereich_id><?php echo $funktionenDAO->fachbereich_id;  ?></FUNKTIONEN:fachbereich_id>
	        	<FUNKTIONEN:name><?php echo $funktionenDAO->name;  ?></FUNKTIONEN:name>
	        	<FUNKTIONEN:funktion><?php echo $funktionenDAO->funktion;  ?></FUNKTIONEN:funktion>
	        	<FUNKTIONEN:beschart1><?php echo $funktionenDAO->beschart1;  ?></FUNKTIONEN:beschart1>
	        	<FUNKTIONEN:beschart2><?php echo $funktionenDAO->beschart2;  ?></FUNKTIONEN:beschart2>
	        	<FUNKTIONEN:verwendung><?php echo $funktionenDAO->verwendung;  ?></FUNKTIONEN:verwendung>
	        	<FUNKTIONEN:hauptberuf><?php echo $funktionenDAO->hauptberuf;  ?></FUNKTIONEN:hauptberuf>
	        	<FUNKTIONEN:hauptberuflich><?php echo ($funktionenDAO->hauptberuflich?'Ja':'Nein');  ?></FUNKTIONEN:hauptberuflich>
	        	<FUNKTIONEN:entwicklungsteam><?php echo ($funktionenDAO->entwicklungsteam?'Ja':'Nein');  ?></FUNKTIONEN:entwicklungsteam>
	        	<FUNKTIONEN:besonderequalifikation><?php echo $funktionenDAO->besonderequalifikation;  ?></FUNKTIONEN:besonderequalifikation>
	        	<FUNKTIONEN:ausmass><?php echo $funktionenDAO->ausmass;  ?></FUNKTIONEN:ausmass>	    		
	      	</RDF:Description>
	      </RDF:li>
	<?php
}
?>
  </RDF:Seq>
</RDF:RDF>