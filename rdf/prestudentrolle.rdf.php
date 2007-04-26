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

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$rdf_url='http://www.technikum-wien.at/prestudentrolle';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:ROLLE="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq about="<?php echo $rdf_url ?>/liste">

<?php
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

$ps = new prestudent($conn);
$ps->getPrestudentRolle($prestudent_id, $rolle_kurzbz, $studiensemester_kurzbz);

foreach($ps->result as $row)
{
?>
	  <RDF:li>
      	<RDF:Description  id="<?php echo $row->prestudent_id.'/'.$row->rolle_kurzbz.'/'.$row->studiensemester_kurzbz; ?>"  about="<?php echo $rdf_url.'/'.$row->prestudent_id.'/'.$row->rolle_kurzbz.'/'.$row->studiensemester_kurzbz; ?>" >
        	<ROLLE:prestudent_id><![CDATA[<?php echo $row->prestudent_id;  ?>]]></ROLLE:prestudent_id>
        	<ROLLE:rolle_kurzbz><![CDATA[<?php echo $row->rolle_kurzbz;  ?>]]></ROLLE:rolle_kurzbz>
        	<ROLLE:studiensemester_kurzbz><![CDATA[<?php echo $row->studiensemester_kurzbz;  ?>]]></ROLLE:studiensemester_kurzbz>
        	<ROLLE:ausbildungssemester><![CDATA[<?php echo $row->ausbildungssemester;  ?>]]></ROLLE:ausbildungssemester>
        	<ROLLE:datum><![CDATA[<?php echo $row->datum;  ?>]]></ROLLE:datum>
      	</RDF:Description>
      </RDF:li>
<?php

}
?>
  </RDF:Seq>
</RDF:RDF>