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
require_once('../include/konto.class.php');

// studiensemester holen
$typ = new konto();

$aktiv=null;
if(isset($_GET['aktiv']))
{
	if($_GET['aktiv']=='true')
		$aktiv=true;
	else
		$aktiv=false;
}

$typ->getBuchungstyp($aktiv);

$rdf_url='http://www.technikum-wien.at/buchungstyp';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:TYP="<?php echo $rdf_url; ?>/rdf#"
>
   <RDF:Seq about="<?php echo $rdf_url ?>/liste">
<?php
foreach ($typ->result as $row)
{
	?>
      <RDF:li>
         <RDF:Description  id="<?php echo $row->buchungstyp_kurzbz; ?>"  about="<?php echo $rdf_url.'/'.$row->buchungstyp_kurzbz; ?>" >
            <TYP:buchungstyp_kurzbz><![CDATA[<?php echo $row->buchungstyp_kurzbz ?>]]></TYP:buchungstyp_kurzbz>
            <TYP:beschreibung><![CDATA[<?php echo $row->beschreibung ?>]]></TYP:beschreibung>
            <TYP:standardbetrag><![CDATA[<?php echo ($row->standardbetrag!=''?$row->standardbetrag:'-0.00'); ?>]]></TYP:standardbetrag>
            <TYP:standardtext><![CDATA[<?php echo $row->standardtext; ?>]]></TYP:standardtext>
			<TYP:credit_points><![CDATA[<?php echo $row->credit_points; ?>]]></TYP:credit_points>
            <TYP:aktiv><![CDATA[<?php echo ($row->aktiv?'true':'false'); ?>]]></TYP:aktiv>
         </RDF:Description>
      </RDF:li>
<?php
}
?>
   </RDF:Seq>
</RDF:RDF>
