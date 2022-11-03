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

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
require_once('../config/vilesci.config.inc.php');
require_once('../include/basis_db.class.php');

$rdf_url='http://www.technikum-wien.at/zgvmaster';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:ZGVMASTER="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq about="<?php echo $rdf_url ?>/alle">

<?php
if(isset($_GET['optional']) && $_GET['optional']=='true')
{
	echo '
	  <RDF:li>
      	<RDF:Description  id=""  about="'.$rdf_url.'/" >
        	<ZGVMASTER:code></ZGVMASTER:code>
        	<ZGVMASTER:bezeichnung>-- keine Auswahl --</ZGVMASTER:bezeichnung>
        	<ZGVMASTER:kurzbz>-- keine Auswahl --</ZGVMASTER:kurzbz>
					<ZGVMASTER:aktiv></ZGVMASTER:aktiv>
      	</RDF:Description>
      </RDF:li>
';
}
$qry = 'SELECT * FROM bis.tbl_zgvmaster ORDER BY zgvmas_code';
$db = new basis_db();

if($db->db_query($qry))
{
	while($row = $db->db_fetch_object())
	{
?>
	  <RDF:li>
      	<RDF:Description  id="<?php echo $row->zgvmas_code; ?>"  about="<?php echo $rdf_url.'/'.$row->zgvmas_code; ?>" >
        	<ZGVMASTER:code><![CDATA[<?php echo $row->zgvmas_code;  ?>]]></ZGVMASTER:code>
        	<ZGVMASTER:bezeichnung><![CDATA[<?php echo $row->zgvmas_bez; ?>]]></ZGVMASTER:bezeichnung>
        	<ZGVMASTER:kurzbz><![CDATA[<?php echo $row->zgvmas_kurzbz; ?>]]></ZGVMASTER:kurzbz>
					<ZGVMASTER:aktiv><![CDATA[<?php echo $row->aktiv?>]]></ZGVMASTER:aktiv>
      	</RDF:Description>
      </RDF:li>
<?php
	}
}
?>
  </RDF:Seq>
</RDF:RDF>
