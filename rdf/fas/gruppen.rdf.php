<?php
/**
 * erstellt ein RDF File mit den Gruppen
 * Created on 23.3.2006
 * Aufruf: gruppen.rdf.php?stg=xx&ausbsem=xx
 */
// header fuer no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/vnd.mozilla.xul+xml");
// xml
error_reporting(E_ALL);
ini_set('display_errors','1');
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
require('../../vilesci/config.inc.php');
require('../../include/fas/gruppe.class.php');
require('../../include/fas/benutzer.class.php');
require('../../include/functions.inc.php');
require('../../include/fas/functions.inc.php');

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING_FAS))
   	die('Es konnte keine Verbindung zum Server aufgebaut werden!');

if(!$conn_vilesci = pg_pconnect(CONN_STRING))
	die('Es konnte keine Verbindung zur Datenbank hergestellt werden!');

$rdf_url='http://www.technikum-wien.at/gruppen';

$user = get_uid();
$benutzer = new benutzer($conn_vilesci);
if(!$benutzer->loadVariables($user))
	die($benutzer->errormsg);

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:GRUPPE="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq RDF:about="<?php echo $rdf_url ?>/liste">
<?php
$gruppen_obj = new gruppe($conn);

if(isset($_GET['stg']) && is_numeric($_GET['stg'])
&& isset($_GET['ausbsem']) && is_numeric($_GET['ausbsem']))
{
	$gruppen_obj->load_gruppen($_GET['stg'], getStudiensemesterIdFromName($conn, $benutzer->variable->semester_aktuell), $_GET['ausbsem']);
	$arr = array();
	foreach ($gruppen_obj->result as $grp)
	{
		$arr['id'][] = $grp->gruppe_id;
		$arr['fullname'][] = $grp->fullname;
	}
	if(isset($arr['id']))
	{
		array_multisort($arr['fullname'],$arr['id']);
		for($i=0;$i<count($arr['id']);$i++)
		{
			?>
			  <RDF:li>
		      	<RDF:Description RDF:about="<?php echo $rdf_url.'/'.$arr['id'][$i]; ?>" >
		        	<GRUPPE:gruppe_id><?php echo $arr['id'][$i];  ?></GRUPPE:gruppe_id>
		    		<GRUPPE:fullname><?php echo $arr['fullname'][$i];  ?></GRUPPE:fullname>
		      	</RDF:Description>
		      </RDF:li>
		<?php
		}
	}
}
?>

  </RDF:Seq>


</RDF:RDF>