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
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
require_once('../config/vilesci.config.inc.php');
require_once('../include/basis_db.class.php');

$rdf_url='http://www.technikum-wien.at/berufstaetigkeit';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:BT="<?php echo $rdf_url; ?>/rdf#">

  <RDF:Seq about="<?php echo $rdf_url ?>/alle">

<?php
if(isset($_GET['optional']) && $_GET['optional']=='true')
{
echo '
	  <RDF:li>
      	<RDF:Description  id=""  about="'.$rdf_url.'/'.'" >
        	<BT:code></BT:code>
        	<BT:bezeichnung>-- keine Auswahl --</BT:bezeichnung>
        	<BT:kurzbz>-- keine Auswahl --</BT:kurzbz>
      	</RDF:Description>
      </RDF:li>
';
}
$qry = 'SELECT * FROM bis.tbl_berufstaetigkeit ORDER BY berufstaetigkeit_bez';
$db = new basis_db();
if($db->db_query($qry))
{
	while($row = $db->db_fetch_object())
	{
?>
	  <RDF:li>
      	<RDF:Description  id="<?php echo $row->berufstaetigkeit_code; ?>"  about="<?php echo $rdf_url.'/'.$row->berufstaetigkeit_code; ?>" >
        	<BT:code><![CDATA[<?php echo $row->berufstaetigkeit_code;  ?>]]></BT:code>
        	<BT:bezeichnung><![CDATA[<?php echo $row->berufstaetigkeit_bez; ?>]]></BT:bezeichnung>
        	<BT:kurzbz><![CDATA[<?php echo $row->berufstaetigkeit_kurzbz; ?>]]></BT:kurzbz>
      	</RDF:Description>
      </RDF:li>
<?php
	}
}
?>
  </RDF:Seq>
</RDF:RDF>