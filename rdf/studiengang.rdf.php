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
require_once('../include/studiengang.class.php');

// raumtypen holen
$studiengangDAO=new studiengang();

if(isset($_GET['studiengang_kz']))
{
	if($studiengangDAO->load($_GET['studiengang_kz']))
	{
		$studiengangDAO->result[] = $studiengangDAO;
	}
}
else
	$studiengangDAO->getAll('typ, kurzbz', false);

$rdf_url='http://www.technikum-wien.at/studiengang';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:STUDIENGANG="<?php echo $rdf_url; ?>/rdf#"
>

  <RDF:Seq about="<?php echo $rdf_url ?>/liste">

<?php
foreach ($studiengangDAO->result as $sg)
{
	?>
  <RDF:li>
      	<RDF:Description  id="<?php echo $sg->studiengang_kz; ?>"  about="<?php echo $rdf_url.'/'.$sg->studiengang_kz; ?>" >
        	<STUDIENGANG:studiengang_kz><![CDATA[<?php echo $sg->studiengang_kz  ?>]]></STUDIENGANG:studiengang_kz>
    		<STUDIENGANG:kurzbz><![CDATA[<?php echo $sg->kurzbz  ?>]]></STUDIENGANG:kurzbz>
    		<STUDIENGANG:kurzbzlang><![CDATA[<?php echo $sg->kurzbzlang  ?>]]></STUDIENGANG:kurzbzlang>
			<STUDIENGANG:bezeichnung><![CDATA[<?php echo $sg->bezeichnung  ?>]]></STUDIENGANG:bezeichnung>
			<STUDIENGANG:max_semester><![CDATA[<?php echo $sg->max_semester  ?>]]></STUDIENGANG:max_semester>
			<STUDIENGANG:typ><![CDATA[<?php echo $sg->typ  ?>]]></STUDIENGANG:typ>
			<STUDIENGANG:farbe><![CDATA[<?php echo $sg->farbe  ?>]]></STUDIENGANG:farbe>
			<STUDIENGANG:email><![CDATA[<?php echo $sg->email  ?>]]></STUDIENGANG:email>
			<STUDIENGANG:kuerzel><![CDATA[<?php echo $sg->kuerzel  ?>]]></STUDIENGANG:kuerzel>
      	</RDF:Description>
  </RDF:li>
	  <?php
}
?>


  </RDF:Seq>
</RDF:RDF>
