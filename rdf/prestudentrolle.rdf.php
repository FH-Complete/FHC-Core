<?php

// header für no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
require_once('../vilesci/config.inc.php');
require_once('../include/person.class.php');
require_once('../include/prestudent.class.php');
require_once('../include/datum.class.php');

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$rdf_url='http://www.technikum-wien.at/prestudentrolle';
$datum = new datum();

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:ROLLE="'.$rdf_url.'/rdf#"
>


  <RDF:Seq about="'.$rdf_url.'/liste">
';

if(isset($_GET['prestudent_id']) && is_numeric($_GET['prestudent_id']))
	$prestudent_id = $_GET['prestudent_id'];
else 
	die('Prestudent_id muss angegeben werden');
	
if(isset($_GET['rolle_kurzbz']))
	$rolle_kurzbz = $_GET['rolle_kurzbz'];
else 
	$rolle_kurzbz=null;
	
if(isset($_GET['studiensemester_kurzbz']))
	$studiensemester_kurzbz = $_GET['studiensemester_kurzbz'];
else 
	$studiensemester_kurzbz=null;

if(isset($_GET['ausbildungssemester']))
	$ausbildungssemester=$_GET['ausbildungssemester'];
else 
	$ausbildungssemester=null;
	
$ps = new prestudent($conn);
$ps->getPrestudentRolle($prestudent_id, $rolle_kurzbz, $studiensemester_kurzbz, 'datum, insertamum', $ausbildungssemester);

foreach($ps->result as $row)
{
	echo '
	  <RDF:li>
      	<RDF:Description  id="'.$row->prestudent_id.'/'.$row->rolle_kurzbz.'/'.$row->studiensemester_kurzbz.'/'.$row->ausbildungssemester.'"  about="'.$rdf_url.'/'.$row->prestudent_id.'/'.$row->rolle_kurzbz.'/'.$row->studiensemester_kurzbz.'/'.$row->ausbildungssemester.'" >
        	<ROLLE:prestudent_id><![CDATA['.$row->prestudent_id.']]></ROLLE:prestudent_id>
        	<ROLLE:rolle_kurzbz><![CDATA['.$row->rolle_kurzbz.']]></ROLLE:rolle_kurzbz>
        	<ROLLE:studiensemester_kurzbz><![CDATA['.$row->studiensemester_kurzbz.']]></ROLLE:studiensemester_kurzbz>
        	<ROLLE:ausbildungssemester><![CDATA['.$row->ausbildungssemester.']]></ROLLE:ausbildungssemester>
        	<ROLLE:datum><![CDATA['.$datum->convertISODate($row->datum).']]></ROLLE:datum>
        	<ROLLE:datum_iso><![CDATA['.$row->datum.']]></ROLLE:datum_iso>
      	</RDF:Description>
      </RDF:li>
	';

}
?>
  </RDF:Seq>
</RDF:RDF>