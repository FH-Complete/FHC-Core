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

$rdf_url='http://www.technikum-wien.at/akadgrad';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:AKADGRAD="'.$rdf_url.'/rdf#"
>

   <RDF:Seq about="'.$rdf_url.'/liste">
';
if(isset($_GET['studiengang_kz']) && is_numeric($_GET['studiengang_kz']))
	$studiengang_kz = $_GET['studiengang_kz'];
else
	die('Studiengang_kz muss uebergeben werden');

if(!is_numeric($studiengang_kz))
	die('Studiengang_kz ist ungueltig');

$qry = "SELECT * FROM lehre.tbl_akadgrad WHERE studiengang_kz='$studiengang_kz' ORDER BY titel";

if(isset($_GET['optional']) && $_GET['optional']=='true')
{
	echo '
  <RDF:li>
     <RDF:Description  id=""  about="'.$rdf_url.'/" >
     	<AKADGRAD:akadgrad_id><![CDATA[]]></AKADGRAD:akadgrad_id>
        <AKADGRAD:titel><![CDATA[-- keine Auswahl --]]></AKADGRAD:titel>
        <AKADGRAD:akadgrad_kurzbz><![CDATA[]]></AKADGRAD:akadgrad_kurzbz>
        <AKADGRAD:studiengang_kz><![CDATA[]]></AKADGRAD:studiengang_kz>
        <AKADGRAD:geschlecht><![CDATA[]]></AKADGRAD:geschlecht>
     </RDF:Description>
  </RDF:li>';

}

$db = new basis_db();
if($db->db_query($qry))
{
	while($row = $db->db_fetch_object())
	{
		echo '
	      <RDF:li>
		     <RDF:Description  id="'.$row->akadgrad_id.'"  about="'.$rdf_url.'/'.$row->akadgrad_id.'" >
		     	<AKADGRAD:akadgrad_id><![CDATA['.$row->akadgrad_id.']]></AKADGRAD:akadgrad_id>
		        <AKADGRAD:titel><![CDATA['.$row->titel.']]></AKADGRAD:titel>
		        <AKADGRAD:akadgrad_kurzbz><![CDATA['.$row->akadgrad_kurzbz.']]></AKADGRAD:akadgrad_kurzbz>
        		<AKADGRAD:studiengang_kz><![CDATA['.$row->studiengang_kz.']]></AKADGRAD:studiengang_kz>
        		<AKADGRAD:geschlecht><![CDATA['.$row->geschlecht.']]></AKADGRAD:geschlecht>
		     </RDF:Description>
		  </RDF:li>';
	}
}
?>
   </RDF:Seq>
</RDF:RDF>