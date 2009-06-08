<?php
/* Copyright (C) 2006 Technikum-Wien
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
require_once('../include/reihungstest.class.php');
require_once('../include/studiengang.class.php');

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$rdf_url='http://www.technikum-wien.at/reihungstest';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:RT="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq about="<?php echo $rdf_url ?>/alle">

<?php
if(isset($_GET['optional']) && $_GET['optional']=='true')
{
	echo '
	  <RDF:li>
      	<RDF:Description  id=""  about="'.$rdf_url.'/" >
			<RT:reihungstest_id></RT:reihungstest_id>
			<RT:studiengang_kz></RT:studiengang_kz>
			<RT:ort_kurzbz></RT:ort_kurzbz>
			<RT:anmerkung></RT:anmerkung>
			<RT:datum></RT:datum>
			<RT:uhrzeit></RT:uhrzeit>
			<RT:bezeichnung>-- keine Auswahl --</RT:bezeichnung>			
      	</RDF:Description>
      </RDF:li>
	
';
}

$stg = array();
$stg_obj = new studiengang($conn);
$stg_obj->getAll();
foreach ($stg_obj->result as $row) 
	$stg[$row->studiengang_kz]=$row->kuerzel;

$rt = new reihungstest($conn, null, true);
$rt->getAll();
foreach ($rt->result as $row)
{
?>
	  <RDF:li>
      	<RDF:Description  id="<?php echo $row->reihungstest_id; ?>"  about="<?php echo $rdf_url.'/'.$row->reihungstest_id; ?>" >
			<RT:reihungstest_id><![CDATA[<?php echo $row->reihungstest_id;  ?>]]></RT:reihungstest_id>
			<RT:studiengang_kz><![CDATA[<?php echo $row->studiengang_kz;  ?>]]></RT:studiengang_kz>
			<RT:ort_kurzbz><![CDATA[<?php echo $row->ort_kurzbz;  ?>]]></RT:ort_kurzbz>
			<RT:anmerkung><![CDATA[<?php echo $row->anmerkung;  ?>]]></RT:anmerkung>
			<RT:datum><![CDATA[<?php echo $row->datum;  ?>]]></RT:datum>
			<RT:uhrzeit><![CDATA[<?php echo $row->uhrzeit;  ?>]]></RT:uhrzeit>
			<RT:bezeichnung><![CDATA[<?php echo (array_key_exists($row->studiengang_kz, $stg)?$stg[$row->studiengang_kz].' ':'').$row->datum.' '.$row->uhrzeit.' '.$row->ort_kurzbz.' '.$row->anmerkung;  ?>]]></RT:bezeichnung>			
      	</RDF:Description>
      </RDF:li>
<?php
}
?>
  </RDF:Seq>
</RDF:RDF>