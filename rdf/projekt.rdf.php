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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>
 */
header("Content-type: application/xhtml+xml");
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/projekt.class.php');

$rdf_url='http://www.technikum-wien.at/projekt/';

if(isset($_GET['oe']))
	$oe=$_GET['oe'];
else 
	$oe=null;
$projekt_obj = new projekt();
$projekt_obj->getProjekte($oe);
//var_dump($projekt_obj);
?>
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:PROJEKT="<?php echo $rdf_url; ?>rdf#"
>

<?php
$descr='';
$sequenz='';
for ($i=0;$i<count($projekt_obj->result);$i++)
{
	$projekt=$projekt_obj->result[$i];
	$descr.='<RDF:Description RDF:about="'.$rdf_url.$projekt->projekt_kurzbz.'" >
		<PROJEKT:projekt_kurzbz>'.$projekt->projekt_kurzbz.'</PROJEKT:projekt_kurzbz>
		<PROJEKT:oe_kurzbz>'.$projekt->oe_kurzbz.'</PROJEKT:oe_kurzbz>
		<PROJEKT:nummer>'.$projekt->nummer.'</PROJEKT:nummer>
		<PROJEKT:titel>'.$projekt->titel.'</PROJEKT:titel>
		<PROJEKT:beschreibung>'.$projekt->beschreibung.'</PROJEKT:beschreibung>
		<PROJEKT:beginn>'.$projekt->beginn.'</PROJEKT:beginn>
		<PROJEKT:ende>'.$projekt->ende.'</PROJEKT:ende>
		<PROJEKT:budget></PROJEKT:budget>
	</RDF:Description>'."\n";

	$sequenz.='<RDF:li RDF:resource="'.$rdf_url.$projekt->projekt_kurzbz.'" />'."\n";
}
$sequenz='<RDF:Seq about="'.$rdf_url.'alle-projekte">'."\n\t".$sequenz.'
  	</RDF:Seq>'."\n";
echo $descr."\n";
echo $sequenz;
?>
</RDF:RDF>
