<?php
/* Copyright (C) 2004 Technikum-Wien
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
// header for no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/vnd.mozilla.xul+xml");
// xml
echo '<?xml version="1.0" encoding="ISO-8859-1" standalone="yes"?>';
// DAO
include('../vilesci/config.inc.php');
require_once('../include/person.class.php');
require_once('../include/benutzer.class.php');
require_once('../include/mitarbeiter.class.php');
require_once('../include/benutzerberechtigung.class.php');

if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';


if (isset($_GET['lektor']))
	$lektor=$_GET['lektor'];
else
	$lektor=true;

if (isset($_GET['fixangestellt']))
	$fixangestellt=$_GET['fixangestellt'];
else
	$fixangestellt=null;

if (isset($_GET['stg_kz']))
	$stg_kz=$_GET['stg_kz'];
else
	$stg_kz=null;

if (isset($_GET['fachbereich_id']))
	$fachbereich_id=$_GET['fachbereich_id'];
else
	$fachbereich_id=null;

if (isset($_GET['user']))
	$user=$_GET['user'];
else
	$user=false;

// Mitarbeiter holen
$mitarbeiter=new mitarbeiter($conn);
if ($user)
{
	$bb=new benutzerberechtigung($conn);
	if($bb->getBerechtigungen($REMOTE_USER))
	{
		$stge=$bb->getStgKz();
		$ma=$mitarbeiter->getMitarbeiterStg($lektor,$fixangestellt,$stge);
	}
}
else
	$ma=$mitarbeiter->getMitarbeiter($lektor,$fixangestellt,$stg_kz,$fachbereich_id);

$rdf_url='http://www.technikum-wien.at/mitarbeiter/';
?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:MITARBEITER="<?php echo $rdf_url; ?>rdf#"
>


  <RDF:Seq about="<?php echo $rdf_url ?>alle">

<?php
foreach ($ma as $mitarbeiter)
{
	?>
	  <RDF:li>
      	<RDF:Description about="<?php echo $rdf_url.$mitarbeiter->uid; ?>" >
        	<MITARBEITER:uid><?php echo $mitarbeiter->uid; ?></MITARBEITER:uid>
    		<MITARBEITER:titelpre><?php echo $mitarbeiter->titelpre; ?></MITARBEITER:titelpre>
    		<MITARBEITER:titelpost><?php echo $mitarbeiter->titelpost; ?></MITARBEITER:titelpost>
    		<MITARBEITER:vornamen><?php echo $mitarbeiter->vornamen; ?></MITARBEITER:vornamen>
    		<MITARBEITER:vorname><?php echo $mitarbeiter->vorname; ?></MITARBEITER:vorname>
    		<MITARBEITER:nachname><?php echo $mitarbeiter->nachname; ?></MITARBEITER:nachname>
    		<MITARBEITER:kurzbz><?php echo $mitarbeiter->kurzbz; ?></MITARBEITER:kurzbz>
      	</RDF:Description>
      </RDF:li>
<?php
}
?>
  </RDF:Seq>
</RDF:RDF>