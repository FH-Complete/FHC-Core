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
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
// header fuer no cache
header("Cache-Control: no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
require_once('../vilesci/config.inc.php');
require_once('../include/konto.class.php');
require_once('../include/functions.inc.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$user = get_uid();

$hier='';
if(isset($_GET['person_id']) && is_numeric($_GET['person_id']))
{
	$person_id=$_GET['person_id'];
}
else 
	$person_id='';

if(isset($_GET['filter']))
	$filter=$_GET['filter'];
else 
	$filter='alle';

if(isset($_GET['buchungsnr']) && is_numeric($_GET['buchungsnr']))
{
	$buchungsnr = $_GET['buchungsnr'];
}
else 
	$buchungsnr = '';

$konto = new konto($conn, null, true);

if($person_id!='')
{
	$konto->getBuchungen($person_id, $filter);
}
elseif($buchungsnr!='')
{
	if(!$konto->load($buchungsnr))
		die($konto->errormsg);
}
else 
	die('Falsche Parameteruebergabe');

$rdf_url='http://www.technikum-wien.at/konto';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:KONTO="<?php echo $rdf_url; ?>/rdf#"
>

<?php
function drawrow($row)
{
	global $rdf_url;
	echo "
  		<RDF:Description  id=\"".$row->buchungsnr."\"  about=\"".$rdf_url.'/'.$row->buchungsnr."\" >
			<KONTO:buchungsnr><![CDATA[".$row->buchungsnr."]]></KONTO:buchungsnr>
			<KONTO:person_id><![CDATA[".$row->person_id."]]></KONTO:person_id>
			<KONTO:studiengang_kz><![CDATA[".$row->studiengang_kz."]]></KONTO:studiengang_kz>
			<KONTO:studiensemester_kurzbz><![CDATA[".$row->studiensemester_kurzbz."]]></KONTO:studiensemester_kurzbz>
			<KONTO:buchungsnr_verweis><![CDATA[".$row->buchungsnr_verweis."]]></KONTO:buchungsnr_verweis>
			<KONTO:betrag><![CDATA[".$row->betrag."]]></KONTO:betrag>
			<KONTO:buchungsdatum><![CDATA[".$row->buchungsdatum."]]></KONTO:buchungsdatum>
			<KONTO:buchungstext><![CDATA[".$row->buchungstext."]]></KONTO:buchungstext>
			<KONTO:mahnspanne><![CDATA[".$row->mahnspanne."]]></KONTO:mahnspanne>
			<KONTO:buchungstyp_kurzbz><![CDATA[".$row->buchungstyp_kurzbz."]]></KONTO:buchungstyp_kurzbz>
			<KONTO:updateamum><![CDATA[".$row->updateamum."]]></KONTO:updateamum>
			<KONTO:updatevon><![CDATA[".$row->updatevon."]]></KONTO:updatevon>
			<KONTO:insertamum><![CDATA[".$row->insertamum."]]></KONTO:insertamum>
			<KONTO:insertvon><![CDATA[".$row->insertvon."]]></KONTO:insertvon>
		</RDF:Description>";
}

if($person_id!='')
{
	foreach ($konto->result as $buchung)
	{
		$buchung = $buchung['parent'];
		//1. Ebene
		drawrow($buchung);

		$hier.="      	
      	<RDF:li>
      		<RDF:Seq about=\"".$rdf_url.'/'.$buchung->buchungsnr."\" >";
		
		if(isset($konto->result[$buchung->buchungsnr]['childs']))
		{
			//2. Ebene
			foreach ($konto->result[$buchung->buchungsnr]['childs'] as $row)
			{	
				if(is_object($row))
				{
					drawrow($row);
					
					$hier.="
					<RDF:li resource=\"".$rdf_url.'/'.$row->buchungsnr.'" />';
				}
			}
		}

		$hier.="			
      		</RDF:Seq>
      	</RDF:li>";
		
	}
}
else 
{
	$hier.="<RDF:li resource=\"".$rdf_url.'/'.$konto->buchungsnr.'" />';
	drawrow($konto);
}
	$hier="
  	<RDF:Seq about=\"".$rdf_url."/liste\">".$hier."
  	</RDF:Seq>";

	echo $hier;
?>


</RDF:RDF>
