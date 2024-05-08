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
/*
 * Created on 02.12.2004
 *
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

require_once('../config/vilesci.config.inc.php');
require_once('../include/nation.class.php');

// studiensemester holen
$nation = new nation();
$nation->getAll();

$rdf_url='http://www.technikum-wien.at/nation';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:NATION="<?php echo $rdf_url; ?>/rdf#"
>
   <RDF:Seq about="<?php echo $rdf_url ?>/liste">
<?php
if(isset($_GET['optional']) && $_GET['optional']=='true')
{
	?>
      <RDF:li>
         <RDF:Description  id=""  about="<?php echo $rdf_url.'/'; ?>" >
            <NATION:nation_code><![CDATA[]]></NATION:nation_code>
            <NATION:entwicklungsstand><![CDATA[]]></NATION:entwicklungsstand>
            <NATION:eu><![CDATA[]]></NATION:eu>
            <NATION:ewr><![CDATA[]]></NATION:ewr>
            <NATION:kontinent><![CDATA[]]></NATION:kontinent>
            <NATION:kurztext><![CDATA[-- keine Auswahl --]]></NATION:kurztext>
            <NATION:langtext><![CDATA[-- keine Auswahl --]]></NATION:langtext>
            <NATION:engltext><![CDATA[-- keine Auswahl --]]></NATION:engltext>
            <NATION:sperre><![CDATA[false]]></NATION:sperre>
         </RDF:Description>
      </RDF:li>
<?php
}

foreach ($nation->nation as $row)
{
	?>
      <RDF:li>
         <RDF:Description  id="<?php echo $row->code; ?>"  about="<?php echo $rdf_url.'/'.$row->code; ?>" >
            <NATION:nation_code><![CDATA[<?php echo $row->code ?>]]></NATION:nation_code>
            <NATION:entwicklungsstand><![CDATA[<?php echo $row->entwicklungsstand ?>]]></NATION:entwicklungsstand>
            <NATION:eu><![CDATA[<?php echo ($row->eu?'true':'false') ?>]]></NATION:eu>
            <NATION:ewr><![CDATA[<?php echo ($row->ewr?'true':'false') ?>]]></NATION:ewr>
            <NATION:kontinent><![CDATA[<?php echo $row->kontinent ?>]]></NATION:kontinent>
            <NATION:kurztext><![CDATA[<?php echo $row->kurztext ?>]]></NATION:kurztext>
            <NATION:langtext><![CDATA[<?php echo $row->langtext ?>]]></NATION:langtext>
            <NATION:engltext><![CDATA[<?php echo $row->engltext ?>]]></NATION:engltext>
            <NATION:sperre><![CDATA[<?php echo ($row->sperre?'true':'false') ?>]]></NATION:sperre>
         </RDF:Description>
      </RDF:li>
<?php
}
?>
   </RDF:Seq>
</RDF:RDF>
