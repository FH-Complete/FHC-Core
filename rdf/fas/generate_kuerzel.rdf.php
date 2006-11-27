<?php
/**
 * Generiert eindeutige kuerzel und UIDs
 *
 * Aufruf:
 * Fuer Kuerzel: generate_kuerzel.php?type=kurzbz&vorname=xyz&nachname=xyz
 * Fuer UID:     generate_kuerzel.php?type=uid&vorname=xyz&nachname=xyz
 */
 require("../../vilesci/config.inc.php");
 require("../../include/fas/person.class.php");
 require("../../include/fas/mitarbeiter.class.php");

 //Header Schicken
 header("Cache-Control: no-cache");
 header("Cache-Control: post-check=0, pre-check=0",false);
 header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
 header("Pragma: no-cache");
 // content type setzen
 header("Content-type: application/vnd.mozilla.xul+xml");
 // xml
 echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

 // Clean stuff from a string
 function clean_string($string)
 {
 	$trans = array("ä" => "ae",
 				   "Ä" => "Ae",
 				   "ö" => "oe",
 				   "Ö" => "Oe",
 				   "ü" => "ue",
 				   "Ü" => "Ue",
 				   "á" => "a",
 				   "à" => "a",
 				   "é" => "e",
 				   "è" => "e",
 				   "ó" => "o",
 				   "ò" => "o",
 				   "í" => "i",
 				   "ì" => "i",
 				   "ú" => "u",
 				   "ù" => "u",
 				   "ß" => "ss");
	$string = strtr($string, $trans);
    return ereg_replace("[^a-zA-Z0-9]", "", $string);
    //[:space:]
 }

 $return=false;
 $msg='unbekannter Fehler';

 //Connection zu FAS DB herstellen
 if(!$conn = pg_connect(CONN_STRING_FAS))
 {
 	$return = 'false';
 	$msg = 'Datenbankverbindung konnte nicht hergestellt werden';
 }

 if(isset($_GET['type']))
 	$type=$_GET['type'];
 else
 	$type=null;

 if(isset($_GET['vorname']))
  	$vorname = $_GET['vorname'];
 else
 	$vorname = null;

 if(isset($_GET['nachname']))
 	$nachname = $_GET['nachname'];
 else
 	$nachname = null;

 if($type!=null && $vorname!=null && $nachname!=null)
 {
 	if($type=='kurzbz')
 	{
 		$kurzbz='';
 		$mitarbeiter = new mitarbeiter($conn);
 		$nachname = clean_string($nachname);
 		$vorname = clean_string($vorname);
 		for($nn=6,$vn=2;$nn!=0;$nn--,$vn++)
 		{
 			$kurzbz = substr($nachname,0,$nn);
 			$kurzbz .= substr($vorname,0,$vn);

 			if(!$mitarbeiter->kurzbz_exists($kurzbz))
 				if($mitarbeiter->errormsg=='')
 					break;
 		}

 		if($mitarbeiter->kurzbz_exists($kurzbz))
 		{
 			$return = 'false';
 			$msg = 'Es konnte keine Kurzbezeichnung ermittelt werden';
 		}
 		else
 		{
 			$return = 'true';
 			$msg = $kurzbz;
 		}
 	}
 	if($type=='uid')
 	{
 		$return = 'true';
 		$nachname = strtolower(clean_string($nachname));
 		$vorname = strtolower(clean_string($vorname));
 		$uid='';
 		$mitarbeiter = new mitarbeiter($conn);

 		for($nn=8,$vn=0;$nn!=0;$nn--,$vn++)
 		{
 			$uid = substr($nachname,0,$nn);
 			$uid .= substr($vorname,0,$vn);

 			if(!$mitarbeiter->uid_exists($uid))
 				if($mitarbeiter->errormsg=='')
 					break;
 			//echo "<br>$uid";
 		}

 		if($mitarbeiter->uid_exists($uid))
 		{
 			$return = 'false';
 			$msg = 'Es konnte keine UID ermittelt werden';
 		}
 		else
 		{
 			$return = 'true';
 			$msg = $uid;
 		}
 	}
 }
 else
 {
 	$return = false;
 	$msg = 'Fehler bei der Parameteruebergabe';
 }

 $rdf_url='http://www.technikum-wien.at/generate_kurzbz';
?>
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:NC="http://home.netscape.com/NC-rdf#"
	xmlns:DATA="<?php echo $rdf_url; ?>/rdf#"
>

  <RDF:Seq RDF:about="<?php echo $rdf_url ?>/msg">
  <RDF:li>
    	<RDF:Description RDF:about="<?php echo $rdf_url.'/0' ?>" >
    		<DATA:return><?php echo $return;  ?></DATA:return>
        	<DATA:msg><?php echo $msg;  ?></DATA:msg>
        </RDF:Description>
	</RDF:li>
  </RDF:Seq>
</RDF:RDF>