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
require_once('../config/global.config.inc.php');
require_once('../include/content.class.php');
require_once('../include/template.class.php');
require_once('../include/functions.inc.php');
require_once('../include/phrasen.class.php');
require_once('../include/webservicelog.class.php');

if(isset($_GET['content_id']))
	$content_id = $_GET['content_id'];
else
	die('ContentID muss uebergeben werden');

$version = (isset($_GET['version'])?$_GET['version']:null);
$sprache = (isset($_GET['sprache'])?$_GET['sprache']:getSprache());
$sichtbar = (isset($_GET['sichtbar'])?($_GET['sichtbar']=='true'?true:($_GET['sichtbar']=='false'?false:null)):true);

$p = new phrasen($sprache);
//XML Content laden
$content = new content();

if($content->islocked($content_id))
{
	$uid = get_uid();
	if(!$content->berechtigt($content_id, $uid))
	{
		echo '<html>
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
					<link href="../skin/style.css.php" rel="stylesheet" type="text/css">
				</head>
				<body>
					<h1>'.CAMPUS_NAME.'</h1>
					'.$p->t('global/keineBerechtigungFuerDieseSeite').'
				</body>
				</html>';
		exit;
	}		
}

if(!$content->getContent($content_id, $sprache, $version, $sichtbar, true))
	die($content->errormsg);

// Legt einen Logeintrag für die Klickstatistik an
if (defined('LOG_CONTENT') && LOG_CONTENT==true)
{
	// Nur eingeloggte User werden geloggt, das sonst auch alle Infoscreenaufrufe und dgl. mitgeloggt werden
	if (is_user_logged_in())
	{
		$uid = get_uid();

		$requestdata = $_SERVER['QUERY_STRING'].'&sprache='.$sprache;
		$log = new webservicelog();
		
		$log->webservicetyp_kurzbz = 'content';
		$log->request_id = $content_id;
		$log->beschreibung = 'content';
		$log->request_data = $requestdata;
		$log->execute_user = $uid;
		
		$log->save(true);
	}
}
	
$XML = new DOMDocument();
$XML->loadXML($content->content);

//XSLT Vorlage laden
$template = new template();
if(!$template->load($content->template_kurzbz))
	die($template->errormsg);

$xsltemplate = new DOMDocument();
$xsltemplate->loadXML($template->xslt_xhtml);

//Transformation
$processor = new XSLTProcessor();
$processor->importStylesheet($xsltemplate);

echo $processor->transformToXML($XML);
?>