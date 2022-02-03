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
require_once('../include/person.class.php');
require_once('../include/prestudent.class.php');
require_once('../include/datum.class.php');

$rdf_url='http://www.technikum-wien.at/prestudent';

$datum = new datum();

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:PRESTD="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq about="<?php echo $rdf_url ?>/alle">

<?php
if(isset($_GET['prestudent_id']) && is_numeric($_GET['prestudent_id']))
{
	$prestd = new prestudent();
	if($prestd->load($_GET['prestudent_id']))
	{
?>
		  <RDF:li>
	      	<RDF:Description  id="<?php echo $prestd->prestudent_id; ?>"  about="<?php echo $rdf_url.'/'.$prestd->prestudent_id; ?>" >
				<PRESTD:prestudent_id><![CDATA[<?php echo $prestd->prestudent_id;  ?>]]></PRESTD:prestudent_id>
				<PRESTD:aufmerksamdurch_kurzbz><![CDATA[<?php echo $prestd->aufmerksamdurch_kurzbz;  ?>]]></PRESTD:aufmerksamdurch_kurzbz>
				<PRESTD:person_id><![CDATA[<?php echo $prestd->person_id;  ?>]]></PRESTD:person_id>
				<PRESTD:studiengang_kz><![CDATA[<?php echo $prestd->studiengang_kz;  ?>]]></PRESTD:studiengang_kz>
				<PRESTD:berufstaetigkeit_code><![CDATA[<?php echo $prestd->berufstaetigkeit_code;  ?>]]></PRESTD:berufstaetigkeit_code>
				<PRESTD:ausbildungcode><![CDATA[<?php echo $prestd->ausbildungcode;  ?>]]></PRESTD:ausbildungcode>
				<PRESTD:zgv_code><![CDATA[<?php echo $prestd->zgv_code;  ?>]]></PRESTD:zgv_code>
				<PRESTD:zgvort><![CDATA[<?php echo $prestd->zgvort;  ?>]]></PRESTD:zgvort>
				<PRESTD:zgvdatum_iso><![CDATA[<?php echo $prestd->zgvdatum;  ?>]]></PRESTD:zgvdatum_iso>
                <PRESTD:zgvdatum><![CDATA[<?php echo $datum->convertISODate($prestd->zgvdatum);  ?>]]></PRESTD:zgvdatum>
                <PRESTD:zgvnation><![CDATA[<?php echo $prestd->zgvnation;  ?>]]></PRESTD:zgvnation>
				<PRESTD:zgv_erfuellt><![CDATA[<?php echo $prestd->zgv_erfuellt;  ?>]]></PRESTD:zgv_erfuellt>
                <PRESTD:zgvmas_code><![CDATA[<?php echo $prestd->zgvmas_code;  ?>]]></PRESTD:zgvmas_code>
				<PRESTD:zgvmaort><![CDATA[<?php echo $prestd->zgvmaort;  ?>]]></PRESTD:zgvmaort>
				<PRESTD:zgvmadatum_iso><![CDATA[<?php echo $prestd->zgvmadatum;  ?>]]></PRESTD:zgvmadatum_iso>
                <PRESTD:zgvmadatum><![CDATA[<?php echo $datum->convertISODate($prestd->zgvmadatum);  ?>]]></PRESTD:zgvmadatum>
                <PRESTD:zgvmanation><![CDATA[<?php echo $prestd->zgvmanation;  ?>]]></PRESTD:zgvmanation>
				<PRESTD:zgvmas_erfuellt><![CDATA[<?php echo $prestd->zgvmas_erfuellt;  ?>]]></PRESTD:zgvmas_erfuellt>
				<PRESTD:zgvdoktor_code><![CDATA[<?php echo $prestd->zgvdoktor_code;  ?>]]></PRESTD:zgvdoktor_code>
				<PRESTD:zgvdoktorort><![CDATA[<?php echo $prestd->zgvdoktorort;  ?>]]></PRESTD:zgvdoktorort>
				<PRESTD:zgvdoktordatum_iso><![CDATA[<?php echo $prestd->zgvdoktordatum;  ?>]]></PRESTD:zgvdoktordatum_iso>
                <PRESTD:zgvdoktordatum><![CDATA[<?php echo $datum->convertISODate($prestd->zgvdoktordatum);  ?>]]></PRESTD:zgvdoktordatum>
                <PRESTD:zgvdoktornation><![CDATA[<?php echo $prestd->zgvdoktornation;  ?>]]></PRESTD:zgvdoktornation>
				<PRESTD:zgvdoktor_erfuellt><![CDATA[<?php echo $prestd->zgvdoktor_erfuellt;  ?>]]></PRESTD:zgvdoktor_erfuellt>
                <PRESTD:aufnahmeschluessel><![CDATA[<?php echo $prestd->aufnahmeschluessel;  ?>]]></PRESTD:aufnahmeschluessel>
				<PRESTD:facheinschlberuf><![CDATA[<?php echo ($prestd->facheinschlberuf?'true':'false');  ?>]]></PRESTD:facheinschlberuf>
				<PRESTD:reihungstest_id><![CDATA[<?php echo $prestd->reihungstest_id;  ?>]]></PRESTD:reihungstest_id>
				<PRESTD:anmeldungreihungstest_iso><![CDATA[<?php echo $prestd->anmeldungreihungstest;  ?>]]></PRESTD:anmeldungreihungstest_iso>
				<PRESTD:anmeldungreihungstest><![CDATA[<?php echo $datum->convertISODate($prestd->anmeldungreihungstest);  ?>]]></PRESTD:anmeldungreihungstest>
				<PRESTD:reihungstestangetreten><![CDATA[<?php echo ($prestd->reihungstestangetreten?'true':'false');  ?>]]></PRESTD:reihungstestangetreten>
				<PRESTD:punkte><![CDATA[<?php echo $prestd->punkte;  ?>]]></PRESTD:punkte>
				<PRESTD:bismelden><![CDATA[<?php echo ($prestd->bismelden?'true':'false');  ?>]]></PRESTD:bismelden>
				<PRESTD:anmerkung><![CDATA[<?php echo $prestd->anmerkung;  ?>]]></PRESTD:anmerkung>
				<PRESTD:mentor><![CDATA[<?php echo $prestd->mentor;  ?>]]></PRESTD:mentor>
	      	</RDF:Description>
	      </RDF:li>
<?php
	}
}
?>
  </RDF:Seq>
</RDF:RDF>