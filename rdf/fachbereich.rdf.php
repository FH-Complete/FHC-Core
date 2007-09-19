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
// header fuer no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/vnd.mozilla.xul+xml");
// xml
echo '<?xml version="1.0" encoding="ISO-8859-1" standalone="yes"?>';
// DAO
require_once('../vilesci/config.inc.php');
require_once('../include/fachbereich.class.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');

// Datenbank Verbindung
if (!$conn = @pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';

$user = get_uid();

$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);
$fb = $rechte->getFbKz();

// fachbereiche holen
//$fachbereichDAO=new fachbereich($conn);
//$fb = $fachbereiche=$fachbereichDAO->getAll();

$qry = "SELECT * FROM public.tbl_fachbereich";

if(count($fb)>0 && !in_array('0',$fb))
{
	foreach($fb as $fbbz)
		$in = ", '".addslashes($fbbz)."'";
	$qry.=" WHERE fachbereich_kurzbz in (1$in)";
}

$qry.=" ORDER BY bezeichnung";
$result = pg_query($conn, $qry);

$rdf_url='http://www.technikum-wien.at/fachbereich';

?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:FACHBEREICH="<?php echo $rdf_url; ?>/rdf#"
>

  <RDF:Seq about="<?php echo $rdf_url ?>/liste">

<?php
if(isset($_GET['optional']) && $_GET['optional']=='true')
{
		?>
  <RDF:li>
      	<RDF:Description  id=""  about="<?php echo $rdf_url.'/'; ?>" >
    		<FACHBEREICH:kurzbz></FACHBEREICH:kurzbz>
    		<FACHBEREICH:bezeichnung>-- keine Auswahl --</FACHBEREICH:bezeichnung>
    		<FACHBEREICH:farbe></FACHBEREICH:farbe>
    		<FACHBEREICH:studiengang_kz></FACHBEREICH:studiengang_kz>
      	</RDF:Description>
  </RDF:li>
	  <?php
}

while ($row = pg_fetch_object($result))
{
	?>
  <RDF:li>
      	<RDF:Description  id="<?php echo $row->fachbereich_kurzbz; ?>"  about="<?php echo $rdf_url.'/'.$row->fachbereich_kurzbz; ?>" >
    		<FACHBEREICH:kurzbz><?php echo $row->fachbereich_kurzbz  ?></FACHBEREICH:kurzbz>
    		<FACHBEREICH:bezeichnung><?php echo $row->bezeichnung  ?></FACHBEREICH:bezeichnung>
    		<FACHBEREICH:farbe><?php echo $row->farbe  ?></FACHBEREICH:farbe>
    		<FACHBEREICH:studiengang_kz><?php echo $row->studiengang_kz  ?></FACHBEREICH:studiengang_kz>
      	</RDF:Description>
  </RDF:li>
	  <?php
}
?>
  </RDF:Seq>
</RDF:RDF>