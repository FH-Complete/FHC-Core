<?php
/* Copyright (C) 2009 Technikum-Wien
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>.
 */

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
require_once('../include/organisationseinheit.class.php');

// raumtypen holen
$org=new organisationseinheit();
if (isset($_GET['onlyRoots']) && $_GET['onlyRoots'] === 'true')
{
	$org->getRoots();
}
else
	$org->getAll(null, null, 'organisationseinheittyp_kurzbz, bezeichnung');

$rdf_url='http://www.technikum-wien.at/organisationseinheit';

echo '
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:ORGANISATIONSEINHEIT="'.$rdf_url.'/rdf#"
>

  <RDF:Seq about="'.$rdf_url.'/liste">
';

foreach ($org->result as $oe)
{
	echo '
  <RDF:li>
      	<RDF:Description  id="'.$oe->oe_kurzbz.'"  about="'.$rdf_url.'/'.$oe->oe_kurzbz.'" >
        	<ORGANISATIONSEINHEIT:oe_kurzbz><![CDATA['.$oe->oe_kurzbz.']]></ORGANISATIONSEINHEIT:oe_kurzbz>
    		<ORGANISATIONSEINHEIT:oe_parent_kurzbz><![CDATA['.$oe->oe_parent_kurzbz.']]></ORGANISATIONSEINHEIT:oe_parent_kurzbz>
    		<ORGANISATIONSEINHEIT:bezeichnung><![CDATA['.$oe->bezeichnung.']]></ORGANISATIONSEINHEIT:bezeichnung>
    		<ORGANISATIONSEINHEIT:organisationseinheittyp_kurzbz><![CDATA['.$oe->organisationseinheittyp_kurzbz.']]></ORGANISATIONSEINHEIT:organisationseinheittyp_kurzbz>
    		<ORGANISATIONSEINHEIT:aktiv><![CDATA['.($oe->aktiv?'true':'false').']]></ORGANISATIONSEINHEIT:aktiv>
      	</RDF:Description>
  </RDF:li>';
}
?>

  </RDF:Seq>
</RDF:RDF>
