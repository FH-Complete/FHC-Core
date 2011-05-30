<?php
/* Copyright (C) 2011 FH Technikum Wien
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
 *          Karl Burkhart <karl.burkhart@technikum-wien.at>.
 */
/**
 * Laedt den Content und das zugeordnete Template aus der Datenbank und 
 * zeigt diese an.
 */
require_once('../config/cis.config.inc.php');
require_once('../include/content.class.php');
require_once('../include/template.class.php');
require_once('../include/functions.inc.php');

$version = (isset($_GET['version'])?$_GET['version']:null);
$sprache = (isset($_GET['sprache'])?$_GET['sprache']:getSprache());
$sichtbar = !isset($_GET['sichtbar']);

//XML Content laden
$content = new content();
$db = new basis_db();


$qry = "SELECT content FROM campus.tbl_content JOIN campus.tbl_contentsprache USING(content_id) WHERE tbl_content.template_kurzbz='news'";
$content = '<?xml version="1.0" encoding="UTF-8"?><content>';
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$content .=$row->content;
	}
}
$content .= '</content>';
//echo $content;
$XML = new DOMDocument();
$XML->loadXML($content);

//XSLT Vorlage laden
$template = new template();
if(!$template->load('news'))
	die($template->errormsg);

$xsltemplate = new DOMDocument();
$xsltemplate->loadXML($template->xslt_xhtml);

//Transformation
$processor = new XSLTProcessor();
$processor->importStylesheet($xsltemplate);

echo $processor->transformToXML($XML);
?>