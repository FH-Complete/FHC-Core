<?php
/* Copyright (C) 2007 Technikum-Wien
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

// Orte holen
$sql_query="SELECT * FROM (public.tbl_ort JOIN public.tbl_ortraumtyp USING (ort_kurzbz)) JOIN public.tbl_raumtyp USING (raumtyp_kurzbz)
				WHERE tbl_ort.aktiv AND tbl_raumtyp.aktiv AND raumtyp_kurzbz!='LM' ORDER BY raumtyp_kurzbz, hierarchie,ort_kurzbz";
$db = new basis_db();
if(!$result = $db->db_query($sql_query))
	$error_msg.=$db->db_last_error();
else
	$num_rows=$db->db_num_rows($result);

$rdf_url='http://www.technikum-wien.at/ort/';
?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:ORT="<?php echo $rdf_url; ?>rdf#"
>

<?php
$descr='';
$sequenz='';

for ($i=0;$i<$num_rows;$i++)
{
    $ortLAST=($i>0?$db->db_fetch_object($result,$i-1):null);
	$ort=$db->db_fetch_object($result,$i);
	$ortNEXT=(($i<$num_rows-1)?$db->db_fetch_object($result,$i+1):null);
	$currentTYP=$ort->raumtyp_kurzbz;
	$lastTYP=($i>0?$ortLAST->raumtyp_kurzbz:null);
	$nextTYP=(($i<$num_rows-1)?$ortNEXT->raumtyp_kurzbz:null);
	//echo "current:$currentTYP last:$lastTYP next:$nextTYP";
	$raumtypen='';
	$qry = "SELECT tbl_ortraumtyp.raumtyp_kurzbz FROM public.tbl_ortraumtyp
			JOIN tbl_raumtyp USING(raumtyp_kurzbz)
			WHERE tbl_raumtyp.aktiv AND ort_kurzbz='$ort->ort_kurzbz'";
	if($result_rt = $db->db_query($qry))
	{
		while($row_rt = $db->db_fetch_object($result_rt))
		{
			if($raumtypen!='')
				$raumtypen.=', ';

			$raumtypen.=$row_rt->raumtyp_kurzbz;
		}
	}
	if ($lastTYP!=$currentTYP || $i==0)
		$descr.='<RDF:Description RDF:about="'.$rdf_url.$ort->raumtyp_kurzbz.'" >
        			<ORT:raumtyp>'.$ort->raumtyp_kurzbz.'</ORT:raumtyp>
    				<ORT:hierarchie></ORT:hierarchie>
    				<ORT:ort_kurzbz></ORT:ort_kurzbz>
    				<ORT:ort_bezeichnung></ORT:ort_bezeichnung>
    				<ORT:max_person></ORT:max_person>
    				<ORT:stockwerk></ORT:stockwerk>
    				<ORT:raumtypen>'.$ort->raumtyp_kurzbz.'</ORT:raumtypen>
    				<ORT:planbezeichnung>'.$ort->planbezeichnung.'</ORT:planbezeichnung>
    				<ORT:arbeitsplaetze></ORT:arbeitsplaetze>
      			</RDF:Description>';
	$descr.='<RDF:Description RDF:about="'.$rdf_url.$ort->raumtyp_kurzbz.'/'.$ort->ort_kurzbz.'" >
        			<ORT:raumtyp>'.$ort->ort_kurzbz."</ORT:raumtyp>
    				<ORT:hierarchie>".$ort->hierarchie."</ORT:hierarchie>
    				<ORT:ort_kurzbz>".$ort->ort_kurzbz."</ORT:ort_kurzbz>
    				<ORT:ort_bezeichnung>".$ort->bezeichnung."</ORT:ort_bezeichnung>
    				<ORT:max_person>".$ort->max_person."</ORT:max_person>
        			<ORT:stockwerk>".$ort->stockwerk."</ORT:stockwerk>
        			<ORT:raumtypen>".$raumtypen."</ORT:raumtypen>
        			<ORT:planbezeichnung>".$ort->planbezeichnung."</ORT:planbezeichnung>
        			<ORT:arbeitsplaetze>".$ort->arbeitsplaetze."</ORT:arbeitsplaetze>
      			</RDF:Description>\n";

	if ($lastTYP!=$currentTYP)
		$sequenz.='<RDF:li RDF:resource="'.$rdf_url.$ort->raumtyp_kurzbz.'" />
					<RDF:li>
      					<RDF:Seq RDF:about="'.$rdf_url.$ort->raumtyp_kurzbz.'" >'."\n";
	if ($nextTYP!=$currentTYP || $i==$num_rows-1)
		$sequenz.='<RDF:li RDF:resource="'.$rdf_url.$ort->raumtyp_kurzbz.'/'.$ort->ort_kurzbz.'" />
					</RDF:Seq>
      			</RDF:li>'."\n";
	elseif ($lastTYP==$currentTYP || $nextTYP==$currentTYP || $num_rows==1)
		$sequenz.='<RDF:li RDF:resource="'.$rdf_url.$ort->raumtyp_kurzbz.'/'.$ort->ort_kurzbz.'" />'."\n";
}
$sequenz='<RDF:Seq about="'.$rdf_url.'alle-orte">'."\n".$sequenz.'
  	</RDF:Seq>';
echo $descr;
echo $sequenz;
?>
</RDF:RDF>