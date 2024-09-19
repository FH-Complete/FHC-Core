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
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 *
 */
require_once('../config/cis.config.inc.php');
require_once('../config/global.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/sprache.class.php');
require_once('../include/phrasen.class.php');
require_once('../include/mail.class.php');
require_once('../include/student.class.php');

$redirectPasswordChange=false;
if(defined('CIS_CHECK_PASSWORD_CHANGE') && CIS_CHECK_PASSWORD_CHANGE==true)
{
	require_once('../addons/ldap/vilesci/ldap.class.php');
	$user = get_uid();
	$password = $_SERVER['PHP_AUTH_PW'];
	$ldap = new ldap();
	$ldap->connect();
	$userdn = $ldap->GetUserDN($user);
	$ldap = new ldap();
	if($ldap->connect(LDAP_SERVER, LDAP_PORT, $userdn, $password))
	{
		$lastchange = $ldap->getEntry($user,'shadowLastChange');
		if(isset($lastchange[0])
		&& isset($lastchange[0]['shadowlastchange'])
		&& isset($lastchange[0]['shadowlastchange'][0]))
		{
			$shadowlastchange = $lastchange[0]['shadowlastchange'][0];
		}
		else
			$shadowlastchange = 0;

		// get unix timestamp 1 year ago
		$dt = new DateTime();
		$dt1year = $dt->sub(new DateInterval('P12M'));
		$ux1year = $dt1year->format('U');

		if($shadowlastchange <= $ux1year)
			$redirectPasswordChange = true;
		else
			$redirectPasswordChange = false;
	}
	else
		die('Bind Failed'.$ldap->errormsg);
}
/**
 * Prueft die URL damit keine boesen URLS uebergeben werden koennen
 * @param $param
 */
function validURLCheck($param)
{
	if (strstr($param,':') || strstr($param,'//'))
	{
		// Der APP_ROOT muss in der URL vorkommen, sonfern es kein relativer Pfad ist
		// HTTPS und HTTP
		if(mb_strpos($param, APP_ROOT)!==0
			&& mb_strpos(mb_str_replace("http://","https://", $param), APP_ROOT)!==0
			&& mb_strpos(mb_str_replace("https://","http://", $param), APP_ROOT)!==0
			&& $param != 'about:blank')
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
$student = new student();
if($student->load($user))
{
	$studiengang_kz=$student->studiengang_kz;
	$semester=$student->semester;
	$verband=$student->verband;
}
else
{
	$studiengang_kz='';
	$semester='';
	$verband='';
}

if(isset($_GET['content']))
{
	$content = $_GET['content'];
	validURLCheck($content);
}
else
{
	if($studiengang_kz=='' && $semester=='' && $verband=='' )
		$content = '../cms/news.php';
	else
		if ($semester=='0' && $verband=='I')
			$content = '../cms/news.php?studiengang_kz=10006&semester=0';
		else
			$content = '../cms/news.php?studiengang_kz='.$studiengang_kz.'&semester='.$semester.'';
}

if($redirectPasswordChange)
	$content = '../cis/private/profile/change_password.php?requiredtochange=true';

$sprache = getSprache();
$p = new phrasen($sprache);
$db = new basis_db();
?><!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1" />
	<title>CIS - <?php echo CAMPUS_NAME; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" href="../skin/jquery.css" type="text/css">
	<link href="../skin/style.css.php" rel="stylesheet" type="text/css">
	<link rel="stylesheet" type="text/css" href="../skin/jquery-ui-1.9.2.custom.min.css">
	<script type="text/javascript" src="../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../include/js/jquery.ui.datepicker.translation.js"></script>
	<script type="text/javascript" src="../vendor/jquery/sizzle/sizzle.js"></script>
	<link rel="stylesheet" type="text/css" href="../vendor/twbs/bootstrap3/dist/css/bootstrap.min.css">
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
	<table cellspacing="0" cellpadding="0" class="header" style="position: relative">
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
				<div id="ampel_div"></div>
			</td>
		</tr>
	</table>
</td>
<td class="rand">
</td>
</tr>
</table>
</body>
</html>
<?php
ob_end_flush();
?>
