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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >,
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at > and
 *          Andreas Moik <moik@technikum-wien.at>.
 *
 */
require_once('../config/cis.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/sprache.class.php');
require_once('../include/phrasen.class.php');
require_once('../include/mail.class.php');
require_once('../include/prestudent.class.php');
require_once('../include/studiensemester.class.php');

/**
 * Prueft die URL damit keine boesen URLS uebergeben werden koennen
 * @param $param
 */
function validURLCheck($param)
{
	if(strstr($param,'://'))
	{
		// Der APP_ROOT muss in der URL vorkommen, sonfern es kein relativer Pfad ist
		// HTTPS und HTTP
		if(mb_strpos($param, APP_ROOT)!==0
			&& mb_strpos(mb_str_replace("http://","https://", $param), APP_ROOT)!==0
			&& mb_strpos(mb_str_replace("https://","http://", $param), APP_ROOT)!==0)
		{
			$text="Dies ist eine automatische Mail.\nEs wurde eine mögliche XSS Attacke durchgefuehrt:\n";
			$text.="\nFolgende URL wurde versucht aufzurufen: \n".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
			$text.="\n\nIP des Aufrufers: ".$_SERVER['REMOTE_ADDR'];
			$text.="\n\nUserAgent: ".$_SERVER['HTTP_USER_AGENT'];
			
			$text.="\n\nAuffälliger Value: $param";
			
			$mail = new mail(MAIL_ADMIN, 'no-reply@'.DOMAIN, 'Versuchte XSS Attacke', $text);
			$mail->send();
			die('Invalid URL detected');
		}
	}
}
ob_start();
if(isset($_GET['sprache']))
{
	$sprache = new sprache();
	if($sprache->load($_GET['sprache']))
	{
		setSprache($_GET['sprache']);
	}
	else
		setSprache(DEFAULT_LANGUAGE);
}
if(isset($_GET['content_id']))
{
	$id = $_GET['content_id'];
	if(!is_numeric($id))
		$id='';
}
else
	$id = '';
	
if(isset($_GET['menu']))
{
	$menu = $_GET['menu'];
	validURLCheck($menu);
}
else
	$menu = 'menu.php?content_id='.$id;
	
$user = get_uid();
$prestudent = new prestudent();
$prestudent->getPrestudentsFromUid($user);

$newsGetArr = array();

if(count($prestudent->result) > 0)
{
	foreach($prestudent->result as $ps)
	{
		$lvb = new prestudent();
		$studsem = new studiensemester();

		$lvb->load_studentlehrverband($ps->prestudent_id, $studsem->getaktorNext());
		$newsGetArr[] = array("studiengang_kz" => $ps->studiengang_kz,"semester" => $lvb->semester);

		if($lvb->verband == "I" && $lvb->semester == "0")
		{
			$newsGetArr[] = array("studiengang_kz" => "10006","semester" => 0);
		}
	}
}

if(isset($_GET['content']))
{
	$content = $_GET['content'];
	validURLCheck($content);
}
else
{
	$content = '../cms/news.php?newsReq='.json_encode($newsGetArr);
}

$sprache = getSprache();
$p = new phrasen($sprache);
$db = new basis_db();
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=9"/> 
	<title>CIS - <?php echo CAMPUS_NAME; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" href="../skin/jquery.css" type="text/css">
	<link href="../skin/style.css.php" rel="stylesheet" type="text/css">
	<link rel="shortcut icon" href="../favicon.ico" type="image/x-icon">
	<script src="../include/js/jquery.js" type="text/javascript"></script>
</head>
<script type="text/javascript">
function changeSprache(sprache)
{
	var menu = '';
	var content = '';
	menu = document.getElementById('menue').contentWindow.location.href;
	content = document.getElementById('content').contentWindow.location.href;
	menu = escape(menu);
	content = escape(content);
	
	window.location.href="index.php?sprache="+sprache+"&content_id=<?php echo $db->convert_html_chars($id);?>&menu="+menu+"&content="+content;
}
function gettimestamp()
{
	var now = new Date();
	var ret = now.getHours()*60*60*60;
	ret = ret + now.getMinutes()*60*60;
	ret = ret + now.getSeconds()*60;
	ret = ret + now.getMilliseconds();
	return ret;
}
function loadampel()
{
	$('#ampel').load('ampel.php?'+gettimestamp());
}
</script>
<body class="main" onload="loadampel()">
<table width="100%" height="100%" cellspacing="0" cellpadding="0" border="0">
<tr>
<td class="rand"></td>
<td class="boxshadow">
	<table cellspacing="0" cellpadding="0" class="header">
		<tr>
		<td valign="top" align="left" style="background-image: url(<?php echo APP_ROOT.'skin/styles/'.DEFAULT_STYLE.'/header.png'; ?>); background-position: top; background-repeat: repeat-x;">
		<a href="index.php"><img class="header_logo" src="<?php echo APP_ROOT.'skin/styles/'.DEFAULT_STYLE.'/logo_250x130.png'; ?>" alt="logo"></a>
		<!--<img class="header" src="<?php echo APP_ROOT.'skin/styles/'.DEFAULT_STYLE.'/header.png'; ?>" alt="header">-->  	 	
	   	 	<table class="header_content" cellpadding="0">
		   	  <tr>
		   	    <td width="20%" align="center">&nbsp;
		        </td>
		         <td valign="middle" align="center">
					<form name="searchform" action="private/tools/suche.php" method="GET" target="content" style="display:inline">				
		        	<input id="globalsearch" type="search" size="55" name="search" placeholder=" <?php echo $p->t('menu/suchePersonOrtDokumentInhalt');?> ..." title="<?php echo $p->t('menu/suchePersonOrtDokumentInhaltLang');?>"/>
		        	<img src="../skin/images/search.png" onclick="document.searchform.submit()" class="suchicon"/>
		        	</form>
		        </td>
		         <td align="right" valign="top" style="width: 20%; padding-right: 10px; padding-top: 10px;">
			          <nobr><span style="vertical-align:top;" id="ampel"></span><a href="private/lvplan/stpl_week.php?pers_uid=<?php echo $user; ?>" target="_blank"><?php echo $p->t('lvplan/lvPlan'); ?></a>&nbsp;&nbsp;<span style="color: #A5AFB6">|</span>
						<?php	  
							$sprache = new sprache();
							$sprache->getAll(true);
							foreach($sprache->result as $row)
							{
								echo ' &nbsp;&nbsp;<a href="#'.$row->sprache.'" title="'.$row->sprache.'" onclick="changeSprache(\''.$row->sprache.'\'); return false;">'.$row->sprache.'</a>';
						}?>
					</nobr>
		        </td>
		   	  </tr>
	   	    </table>
	   	    
	   	</td>
	   	</tr>
	   	<tr>
			<td valign="top" align="left">
			<iframe id="menue" src="<?php echo $db->convert_html_chars($menu); ?>" name="menu" frameborder="0">
				No iFrames
			</iframe>
			<iframe id="content" src="<?php echo $db->convert_html_chars($content); ?>" name="content" frameborder="0">
				No iFrames
			</iframe>
			</td>
		</tr>
	</table>
</td>
<td class="rand">
	<?php /*echo'
	<!--  Menubox-Effekt am Seitenrand. Nettes Feature aber dzt. nicht sinnvoll einsetzbar
	<div class="hoverbox">
		<div class="preview">
			<img src="../skin/images/aufklappen.png" />
			<div class="hoverbox_inhalt">
				<table class="hoverbox">
					<tr>
					<td><table style="text-align:right;" width="150px">
						<tr>
						<td><a href="http://www.hofer.at" target="blank">Impressum</a></td>
						</tr>
						<tr>
						<td><a href="mailto:kindlm@technikum-wien.at">Kontakt</a></td>
						</tr>
						<tr>
						<td>&nbsp;</td>
						</tr>
						<tr>
						<td>Powered by <a href="http://fhcomplete.technikum-wien.at" target="blank">FH Complete 2.0</i></a></td>
						</tr>
						</table>
					</td>
					<td><img src="../skin/images/aufklappen.png" /></td>
					</td>
					</tr>
				
				</table>
			</div>
		</div>
	</div>-->';*/
	?>
</td>
</tr>
</table>
</body>
</html>
<?php
ob_end_flush();
?>
