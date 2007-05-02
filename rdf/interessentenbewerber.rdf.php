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
//header("Content-type: application/vnd.mozilla.xul+xml");
header("Content-type: application/xhtml+xml");
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
require_once('../vilesci/config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/person.class.php');
require_once('../include/prestudent.class.php');

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$rdf_url='http://www.technikum-wien.at/interessent';
$user = get_uid();
loadVariables($conn, $user);
?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:PRESTD="<?php echo $rdf_url; ?>/rdf#"
>


  <RDF:Seq about="<?php echo $rdf_url ?>/alle">

<?php
if(isset($_GET['studiensemester_kurzbz']))
	$studiensemester_kurzbz = $_GET['studiensemester_kurzbz'];
else
	$studiensemester_kurzbz = null;
	
if($studiensemester_kurzbz==null)
	$studiensemester_kurzbz = $semester_aktuell;

if(isset($_GET['studiengang_kz']) && is_numeric($_GET['studiengang_kz']))
	$studiengang_kz = $_GET['studiengang_kz'];
else 
	$studiengang_kz = null;

if(isset($_GET['semester']) && is_numeric($_GET['semester']))
	$semester = $_GET['semester'];
else 
	$semester = null;
	
if(isset($_GET['prestudent_id']) && is_numeric($_GET['prestudent_id']))
	$prestudent_id=$_GET['prestudent_id'];
else 
	$prestudent_id=null;

$prestd = new prestudent($conn, null, true);

if($studiensemester_kurzbz!=null && $studiengang_kz!=null)
{
	if($prestd->loadIntessentenUndBewerber($studiensemester_kurzbz, $studiengang_kz, $semester))
	{
		foreach ($prestd->result as $row)
		{
			DrawInteressent($row);
		}
	}
}
elseif($prestudent_id!=null)
{
	if($prestd->load($prestudent_id))
		DrawInteressent($prestd);
	else 
		echo $prestd->errormsg;	
}
else 
{
	echo 'Falsche Parameteruebergabe';
}

function DrawInteressent($row)
{
		global $rdf_url;
?>
		  <RDF:li>
	      	<RDF:Description  id="<?php echo $row->prestudent_id; ?>"  about="<?php echo $rdf_url.'/'.$row->prestudent_id; ?>" >
	        	<PRESTD:person_id><![CDATA[<?php echo $row->person_id; ?>]]></PRESTD:person_id>
	        	<PRESTD:anrede><![CDATA[<?php echo $row->anrede; ?>]]></PRESTD:anrede>
	        	<PRESTD:sprache><![CDATA[<?php echo $row->sprache; ?>]]></PRESTD:sprache>
	        	<PRESTD:staatsbuergerschaft><![CDATA[<?php echo $row->staatsbuergerschaft; ?>]]></PRESTD:staatsbuergerschaft>
	        	<PRESTD:familienstand><![CDATA[<?php echo $row->familienstand; ?>]]></PRESTD:familienstand>
	    		<PRESTD:titelpre><![CDATA[<?php echo $row->titelpre; ?>]]></PRESTD:titelpre>
	    		<PRESTD:titelpost><![CDATA[<?php echo $row->titelpost; ?>]]></PRESTD:titelpost>
	    		<PRESTD:vornamen><![CDATA[<?php echo $row->vornamen  ?>]]></PRESTD:vornamen>
	    		<PRESTD:vorname><![CDATA[<?php echo $row->vorname  ?>]]></PRESTD:vorname>
	    		<PRESTD:nachname><![CDATA[<?php echo $row->nachname  ?>]]></PRESTD:nachname>
	    		<PRESTD:geburtsdatum><![CDATA[<?php echo $row->gebdatum  ?>]]></PRESTD:geburtsdatum>
	    		<PRESTD:geburtsdatum_iso><![CDATA[<?php echo $row->gebdatum;  ?>]]></PRESTD:geburtsdatum_iso>
	    		<PRESTD:geburtsnation><![CDATA[<?php echo $row->geburtsnation; ?>]]></PRESTD:geburtsnation>
	    		<PRESTD:homepage><![CDATA[<?php echo $row->homepage  ?>]]></PRESTD:homepage>
	    		<PRESTD:aktiv><![CDATA[<?php echo ($row->aktiv?'true':'false')  ?>]]></PRESTD:aktiv>
	    		<PRESTD:gebort><![CDATA[<?php echo $row->gebort;  ?>]]></PRESTD:gebort>
	    		<PRESTD:gebzeit><![CDATA[<?php echo $row->gebzeit;  ?>]]></PRESTD:gebzeit>
	    		<PRESTD:foto><![CDATA[<?php echo $row->foto;  ?>]]></PRESTD:foto>
	    		<PRESTD:anmerkungen><![CDATA[<?php echo $row->anmerkungen;  ?>]]></PRESTD:anmerkungen>
	    		<PRESTD:svnr><![CDATA[<?php echo $row->svnr; ?>]]></PRESTD:svnr>
	    		<PRESTD:ersatzkennzeichen><![CDATA[<?php echo $row->ersatzkennzeichen; ?>]]></PRESTD:ersatzkennzeichen>
	    		<PRESTD:geschlecht><![CDATA[<?php echo $row->geschlecht; ?>]]></PRESTD:geschlecht>
	    		<PRESTD:anzahlkinder><![CDATA[<?php echo $row->anzahlkinder; ?>]]></PRESTD:anzahlkinder>
	    		<PRESTD:updateamum><![CDATA[<?php echo $row->updateamum;  ?>]]></PRESTD:updateamum>
	    		<PRESTD:updatevon><![CDATA[<?php echo $row->updatevon;  ?>]]></PRESTD:updatevon>
	    		
				<PRESTD:prestudent_id><![CDATA[<?php echo $row->prestudent_id;  ?>]]></PRESTD:prestudent_id>
				<PRESTD:aufmerksamdurch_kurzbz><![CDATA[<?php echo $row->aufmerksamdurch_kurzbz;  ?>]]></PRESTD:aufmerksamdurch_kurzbz>
				<PRESTD:studiengang_kz><![CDATA[<?php echo $row->studiengang_kz;  ?>]]></PRESTD:studiengang_kz>
				<PRESTD:berufstaetigkeit_code><![CDATA[<?php echo $row->berufstaetigkeit_code;  ?>]]></PRESTD:berufstaetigkeit_code>
				<PRESTD:ausbildungcode><![CDATA[<?php echo $row->ausbildungcode;  ?>]]></PRESTD:ausbildungcode>
				<PRESTD:zgv_code><![CDATA[<?php echo $row->zgv_code;  ?>]]></PRESTD:zgv_code>
				<PRESTD:zgvort><![CDATA[<?php echo $row->zgvort;  ?>]]></PRESTD:zgvort>
				<PRESTD:zgvdatum><![CDATA[<?php echo $row->zgvdatum;  ?>]]></PRESTD:zgvdatum>
				<PRESTD:zgvmas_code><![CDATA[<?php echo $row->zgvmas_code;  ?>]]></PRESTD:zgvmas_code>
				<PRESTD:zgvmaort><![CDATA[<?php echo $row->zgvmaort;  ?>]]></PRESTD:zgvmaort>
				<PRESTD:zgvmadatum><![CDATA[<?php echo $row->zgvmadatum;  ?>]]></PRESTD:zgvmadatum>
				<PRESTD:aufnahmeschluessel><![CDATA[<?php echo $row->aufnahmeschluessel;  ?>]]></PRESTD:aufnahmeschluessel>
				<PRESTD:facheinschlberuf><![CDATA[<?php echo ($row->facheinschlberuf?'true':'false');  ?>]]></PRESTD:facheinschlberuf>
				<PRESTD:reihungstest_id><![CDATA[<?php echo $row->reihungstest_id;  ?>]]></PRESTD:reihungstest_id>
				<PRESTD:anmeldungreihungstest><![CDATA[<?php echo $row->anmeldungreihungstest;  ?>]]></PRESTD:anmeldungreihungstest>
				<PRESTD:reihungstestangetreten><![CDATA[<?php echo ($row->reihungstestangetreten?'true':'false');  ?>]]></PRESTD:reihungstestangetreten>
				<PRESTD:punkte><![CDATA[<?php echo $row->punkte;  ?>]]></PRESTD:punkte>
				<PRESTD:bismelden><![CDATA[<?php echo ($row->bismelden?'true':'false');  ?>]]></PRESTD:bismelden>
	      	</RDF:Description>
	      </RDF:li>
<?php
}
?>
  </RDF:Seq>
</RDF:RDF>