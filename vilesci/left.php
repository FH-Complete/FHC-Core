<?php
	require('config.inc.php');
 	require('../include/functions.inc.php');
 	require('../include/benutzerberechtigung.class.php');
 	$uid=get_uid();
 	$conn=pg_connect(CONN_STRING) or die('Connection zur Portal Datenbank fehlgeschlagen');
	$berechtigung=new benutzerberechtigung($conn);
	$berechtigung->getBerechtigungen($uid);
	if (!($berechtigung->isBerechtigt('admin') || $berechtigung->isBerechtigt('support') || $berechtigung->isBerechtigt('lvplan') ))
		die ('Keine Berechtigung!');

	$menu=array
	(
		//'Admin'=> 		array('name'=>'Admin', 'link'=>'admin/menu.html', 'target'=>'main'),
		'Lehre'=> 		array
		(
			'name'=>'Lehre',
			'Lehrveranstaltung'=>array
			(
				'name'=>'Lehrveranstaltung',
				'Verwaltung'=>array('name'=>'Verwaltung', 'link'=>'lehre/lehrveranstaltung_frameset.html', 'target'=>'main'),
				'Wartung'=>array('name'=>'Wartung', 'link'=>'stammdaten/lv_wartung.php', 'target'=>'main')
			),
			'Lehrfach'=>array
			(
				'name'=>'Lehrfach',
				'Verwaltung'=>array('name'=>'Verwaltung', 'link'=>'lehre/lehrfach.php', 'target'=>'main'),
				'Wartung'=>array('name'=>'Wartung', 'link'=>'lehre/lehrfach/wartung.php', 'target'=>'main')
			),
			'Lehreinheit'=>array
			(
				'name'=>'Lehreinheit',
				'Verwaltung'=>array('name'=>'Verwaltung', 'link'=>'lehre/lv_verteilung/lv_verteilung.php', 'target'=>'main'),
				'Wartung'=>array('name'=>'Wartung', 'link'=>'stammdaten/le_wartung.php', 'target'=>'main'),
				'Vorrueckung'=>array('name'=>'Vorrueckung', 'link'=>'lehre/lehreinheiten_vorrueckung.php', 'target'=>'main')
			),
			'Freifach'=>array
			(
				'name'=>'Freifach',
				'Studenten'=>array('name'=>'Studenten', 'link'=>'lehre/freifach.php', 'target'=>'main'),
				'Lektoren'=>array('name'=>'Lektoren', 'link'=>'lehre/freifach_lektoren.php', 'target'=>'main')
			),
			'LV-Planung'=>array
			(
				'name'=>'LV-Planung',
				'Wartung'=>array('name'=>'Verwaltung', 'link'=>'lehre/lehrveranstaltung_frameset.html', 'target'=>'main'),
				'Check'=>array('name'=>'Studenten', 'link'=>'lehre/lehrveranstaltung_frameset.html', 'target'=>'main'),
				'Kollision'=>array('name'=>'Studenten', 'link'=>'lehre/lehrveranstaltung_frameset.html', 'target'=>'main'),
				'Stundenplan'=>array('name'=>'Studenten', 'link'=>'lehre/lehrveranstaltung_frameset.html', 'target'=>'main'),
				'Studenten'=>array('name'=>'Studenten', 'link'=>'lehre/lehrveranstaltung_frameset.html', 'target'=>'main'),
				'Studenten'=>array('name'=>'Studenten', 'link'=>'lehre/lehrveranstaltung_frameset.html', 'target'=>'main')
			)
		),
		'Personen'=> 	array
		(
			'name'=>'Personen', 'link'=>'admin/menu.html', 'target'=>'main', 'opener'=>'true', 'hide'=>'true',
			'Personen'=>array('name'=>'Personen', 'link'=>'lehre/lehrveranstaltung_frameset.html', 'target'=>'main'),
			'Benutzer'=>array('name'=>'Benutzer', 'link'=>'lehre/lehrveranstaltung_frameset.html', 'target'=>'main'),
			'Mitarbeiter'=>array('name'=>'Mitarbeiter', 'link'=>'lehre/lehrveranstaltung_frameset.html', 'target'=>'main'),
			'Studenten'=>array('name'=>'Studenten', 'link'=>'lehre/lehrveranstaltung_frameset.html', 'target'=>'main')
		),
		'Stammdaten'=>	array('name'=>'Stammdaten', 'link'=>'admin/menu.html', 'target'=>'main'),
		'Vorrueckung'=>	array('name'=>'Vorrueckung', 'link'=>'admin/menu.html', 'target'=>'main', 'opener'=>'true', 'hide'=>'true'),
		'Auswertung'=>	array('name'=>'Auswertung', 'link'=>'admin/menu.html', 'target'=>'main')
	);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>VileSci Men&uuml;</title>
	<link href="../skin/vilesci.css" rel="stylesheet" type="text/css">
	<style type="text/css">
	</style>

	<script language="JavaScript" type="text/javascript">
	<!--
		function js_toggle_container(conid)
   	{
   		//alert(conid);
   		//var display = document.getElementById(conid).style.display;
			if (document.getElementById(conid).style.display=='none')
			{
				document.getElementById(conid).style.display='block';
				document.getElementById(conid+'_dot').innerHTML='&#8211; ';
			}
        	else
        	{
				document.getElementById(conid).style.display='none';
				document.getElementById(conid+'_dot').innerHTML='+ ';
			}
  		}
	//-->
	</script>
</head>

<body style="background-color:#eeeeee;">
<div class="logo">
	<a href="intro.php" target="detail">
		<img src="../skin/images/logo.png" width="200" height="50" alt="VileSci (FASonline)" title="VileSci" />
	</a>
</div>

<?php
if ($berechtigung->isBerechtigt('admin'))
	echo '<div>
			<a href="admin/menu.html" target="main">Admin</a>
		</div><hr>';

/*foreach($menu AS $m)
{
	$opener=false;
	$hide=false;
	if (isset($m['opener']))
		if ($m['opener']=='true')
			$opener=true;
	if (isset($m['hide']))
		if ($m['hide']=='true')
			$hide=true;

	if ($opener)
	{
		echo '<SPAN id="'.$m['name'].'_dot" onclick="js_toggle_container('."'".$m['name']."'".')" style="font-weight:bold">';
		if ($hide)
			echo '+ ';
		else
			echo '&#8211; ';
		echo '</SPAN>';
	}
	else
		echo '&curren; ';

	if (isset($m['link']))
		echo '<a href="'.$m['link'].'" ';
	if (isset($m['target']))
		echo 'target="'.$m['target'].'" ';
	if (isset($m['link']))
		echo '>';
	if (isset($m['name']))
		echo '<u><strong>'.$m['name'].'</strong></u>';
	if (isset($m['link']))
		echo '</a>';
	if ($hide)
		$display='none';
	else
		$display='block';
	echo "\n<DIV>\n".'<SPAN id="'.$m['name'].'" style="display:'.$display.'">';
	foreach($m AS $m1)
		if (is_array($m1))
		{
			$opener=false;
			$hide=false;
			if (isset($m1['opener']))
				if ($m1['opener']=='true')
					$opener=true;
			if (isset($m1['hide']))
				if ($m1['hide']=='true')
					$hide=true;

			if ($opener)
			{
				echo "\n\t".'<SPAN onclick="js_toggle_container('."'".$m1['name']."'".')">';
				if ($hide)
					echo '+ ';
				else
					echo '&#8211; ';
				echo "\n\t\t</SPAN>";
			}
			else
				echo "\t &nbsp;&nbsp;&nbsp;&nbsp;&middot; ";

			if (isset($m1['link']))
				echo '<a href="'.$m1['link'].'" ';
			if (isset($m1['target']))
				echo 'target="'.$m1['target'].'" ';
			if (isset($m1['link']))
				echo '>';
			if (isset($m1['name']))
				echo '<strong>'.$m1['name'].'</strong>';
			if (isset($m1['link']))
				echo '</a>';
			if ($hide)
				$display='none';
			else
				$display='block';
			echo "\n\t<DIV>\n\t".'<SPAN id="'.$m1['name'].'" style="display:'.$display.'">';
			foreach($m1 AS $m2)
				if (is_array($m2))
				{
					if (isset($m2['link']))
						echo "\n\t\t".'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&middot; <a href="'.$m2['link'].'" ';
					if (isset($m2['target']))
						echo 'target="'.$m2['target'].'" ';
					if (isset($m2['link']))
						echo '>';
					if (isset($m2['name']))
						echo $m2['name'];
					if (isset($m2['link']))
						echo '</a><BR />';
				}
			echo "\n\t</SPAN>\n\t</DIV>\n";
		}
	echo "\n</SPAN>\n</DIV>\n";
}*/
?>


<!-- ******************* Haupt-Menue Lehre *******************************-->
<a href="?Lehre" class="MenuItem1" onClick="return(js_toggle_container('menueLehre'));">
	<img src="../skin/images/menu_item.gif" width="7" height="9" />&nbsp;Lehre
</a>
<div id="menueLehre" style="display: block;">
	<table class="menue">
	<!--Menu Eintrag LV-Planung -->
	<tr>
       	<td nowrap><a href="?Lehre" class="MenuItem1" onClick="return(js_toggle_container('Stundenplan'));"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;LV-Planung</a></td>
    </tr>
	<tr>
       	<td nowrap>
	 		<table width="100%"  border="0" cellspacing="0" cellpadding="0" id="Stundenplan" style="display:block;">
		  	<tr>
		  		<td width="10" nowrap>&nbsp;</td>
				<td nowrap><a class="MenuItem2" href="../cis/private/lvplan/index.html" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Stundenplan</a></td>
		  	</tr>
		  	<tr>
		  		<td width="10" nowrap>&nbsp;</td>
				<td nowrap><a class="MenuItem2" href="lehre/lvplanwartung.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Wartung</a></td>
		  	</tr>
		  	<tr>
		  		<td width="10" nowrap>&nbsp;</td>
				<td nowrap><a class="MenuItem2" href="lehre/stdplan_insert.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Insert</a></td>
		  	</tr>
		  	<tr>
		  		<td width="10" nowrap>&nbsp;</td>
				<td nowrap><a class="MenuItem2" href="lehre/stdplan_delete.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Delete</a></td>
		  	</tr>
			<tr>
		  		<td width="10" nowrap>&nbsp;</td>
				<td nowrap><a class="MenuItem2" href="lehre/check/index.html" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Checken</a></td>
		  	</tr>
		  	<tr>
		  		<td width="10" nowrap>&nbsp;</td>
				<td nowrap><a class="MenuItem2" href="lehre/stpl_benutzer_kollision_frameset.html" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Kollision Student</a></td>
		  	</tr>
		  	<tr>
		  		<td width="10" nowrap>&nbsp;</td>
				<td nowrap><a class="MenuItem2" href="lehre/import/index.html" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Import</a></td>
		  	</tr>
		  	<tr>
		  		<td width="10" nowrap>&nbsp;</td>
				<td nowrap><a class="MenuItem2" href="lehre/export/index.html" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Export</a></td>
		  	</tr>
			</table>
		</td>
	</tr>
	<!--Menu Eintrag Zeitwunsch-->
  	<tr>
		<td nowrap><a class="MenuItem1" href="lehre/zeitwuensche.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Zeitw&uuml;nsche</a></td>
  	</tr>
	<!--Menu Eintrag LV-Verteilung-->
	<tr>
       	<td nowrap><a href="?Lehre" class="MenuItem1" onClick="return(js_toggle_container('Lehreinheiten'));"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Lehreinheiten</a></td>
    </tr>
	<tr>
       	<td nowrap>
	  		<table width="100%"  border="0" cellspacing="0" cellpadding="0" id="Lehreinheiten" style="display:block;">
			  	<tr>
			  		<td width="10" nowrap>&nbsp;</td>
					<td nowrap><a class="MenuItem1" href="lehre/lv_verteilung/lv_verteilung.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Verwaltung</a></td>
				</tr>
				<tr>
			  		<td width="10" nowrap>&nbsp;</td>
					<td nowrap><a class="MenuItem1" href="lehre/lehreinheiten_vorrueckung.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Vorr&uuml;ckung</a></td>
				</tr>

			</table>
		</td>
	</tr>
    <!--Menu Eintrag Lehrveranstaltungen-->
	<tr>
       	<td nowrap><a href="?Lehre" class="MenuItem1" onClick="return(js_toggle_container('Lehrveranstaltungen'));"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Lehrveranstaltungen</a></td>
    </tr>
	<tr>
       	<td nowrap>
	  		<table width="100%"  border="0" cellspacing="0" cellpadding="0" id="Lehrveranstaltungen" style="display:block;">
		  	<tr>
		  		<td width="10" nowrap>&nbsp;</td>
				<td nowrap><a class="MenuItem2" href="lehre/lehrveranstaltung_frameset.html" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;&Uuml;bersicht</a></td>
		  	</tr>
		  	<tr>
		  		<td width="10" nowrap>&nbsp;</td>
				<td nowrap><a class="MenuItem2" href="stammdaten/lv_wartung.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;LV-Zusammenlegung</a></td>
		  	</tr>
		  	<tr>
		  		<td width="10" nowrap>&nbsp;</td>
				<td nowrap><a class="MenuItem2" href="stammdaten/le_wartung.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;LE-Zusammenlegung</a></td>
		  	</tr>
		  	</table>
		</td>
	</tr>
  	<!--Menu Eintrag Lehrfach-->
	<tr>
	  	<td nowrap><a href="?Lehre" class="MenuItem1" onClick="return(js_toggle_container('Lehrfach'));"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Lehrfach</a>
			<table width="100%"  border="0" cellspacing="0" cellpadding="0" id="Lehrfach" style="display: block;">
		  	<tr>
		  		<td width="10" nowrap>&nbsp;</td>
				<td nowrap><a class="MenuItem2" href="lehre/lehrfach.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Verwaltung</a></td>
		  	</tr>
		  	<tr>
		  		<td width="10" nowrap>&nbsp;</td>
				<td nowrap><a class="MenuItem2" href="lehre/lehrfach/wartung.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Wartung</a></td>
		  	</tr>
		 	</table>
		</td>
	</tr>
	<!--Menu Eintrag Freifaecher-->
	<tr>
	  	<td nowrap><a href="?Freifach" class="MenuItem1" onClick="return(js_toggle_container('Freifach'));"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Freifach</a>
			<table width="100%"  border="0" cellspacing="0" cellpadding="0" id="Freifach" style="display: block;">
		  	<tr>
		  		<td width="10" nowrap>&nbsp;</td>
				<td nowrap><a class="MenuItem2" href="lehre/freifach.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Studierende</a></td>
		  	</tr>
		  	<tr>
		  		<td width="10" nowrap>&nbsp;</td>
				<td nowrap><a class="MenuItem2" href="lehre/freifach_studentenvorrueckung.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Studierende vorr&uuml;cken</a></td>
		  	</tr>
		  	<tr>
		  		<td width="10" nowrap>&nbsp;</td>
				<td nowrap><a class="MenuItem2" href="lehre/freifach_lektoren.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;LektorInnen</a></td>
		  	</tr>
		 	</table>
		</td>
	</tr>

	</table>
</div>

<HR>
<!--****************** Haupt-Menue Personen *****************************-->
<a href="?Personen" class="MenuItem1" onClick="return(js_toggle_container('menuePersonen'));">
	<img src="../skin/images/menu_item.gif" width="7" height="9" />&nbsp;Personen
</a>
<div id="menuePersonen" style="display: none;">
	<table class="menue" style="border-right-width:1px;border-right-color:#BCBCBC;">
	<!--================ Menue Personen->Personen =====================-->
	<tr>
       	<td nowrap><a href="?Lehre" class="MenuItem1" onClick="return(js_toggle_container('Personen'));"><img src="../skin/images/menu_item.gif" width="7" height="9" />&nbsp;Personen</a>
       	</td>
   	</tr>
	<tr>
		<td nowrap>
			<table width="100%"  border="0" cellspacing="0" cellpadding="0" id="Personen" style="display:block;">
			<!-- Personen->Personen->Suche -->
			<tr>
				<td width="10" nowrap>&nbsp;</td>
				<td nowrap><a class="MenuItem2" href="personen/suche.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Suche</a></td>
			</tr>
			<!--Menu Eintrag Gruppen -->
			<tr>
				<td width="10" nowrap>&nbsp;</td>
				<td nowrap>
					<a href="?Lehre" class="MenuItem1" onClick="return(js_toggle_container('Gruppen'));"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Gruppen</a>
					<table width="100%"  border="0" cellspacing="0" cellpadding="0" id="Gruppen" style="display: none">
					<tr>
						<td width="10" nowrap>&nbsp;</td>
				    	<td nowrap><a class="MenuItem2" href="lehre/einheit_menu.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;&Uuml;bersicht</a></td>
					</tr>
					<tr>
						<td width="10" nowrap>&nbsp;</td>
				    	<td nowrap><a class="MenuItem2" href="lehre/einheit_menu.php?newFrm=true" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Neu</a></td>
					</tr>
					</table>
				</td>
			</tr>
			<!--Zusammenlegen von Personen-->
			<tr>
				<td width="10" nowrap>&nbsp;</td>
				<td nowrap><a class="MenuItem2" href="stammdaten/personen_wartung.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Personen zusammenlegen</a></td>
			</tr>
			</table>
		</td>
	</tr>
	<!--================ Menue Personen->Benutzer =====================-->
	<tr>
       	<td nowrap><a href="?Lehre" class="MenuItem1" onClick="return(js_toggle_container('Benutzer'));"><img src="../skin/images/menu_item.gif" width="7" height="9" />&nbsp;Benutzer</a></td>
   	</tr>
	<tr>
		<td nowrap>
			<table width="100%"  border="0" cellspacing="0" cellpadding="0" id="Benutzer" style="display:block;">
			<!-- Personen->Benutzer->LDAP-Check -->
			<tr>
				<td width="10" nowrap>&nbsp;</td>
				<td nowrap><a class="MenuItem2" href="personen/ldap_check.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;LDAP-Check</a></td>
			</tr>
			<tr>
				<td width="10" nowrap>&nbsp;</td>
				<td nowrap><a class="MenuItem2" href="personen/betriebsmittel_index.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Betriebsmittel</a></td>
			</tr>
			</table>
		</td>
	</tr>
	<!--================ Menue Personen->Studenten ===================== -->
	<tr>
		<td nowrap><a href="?Lehre" class="MenuItem1" onClick="return(js_toggle_container('Studenten'));"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Studenten</a></td>
	<tr>
	<tr>
		<td nowrap>
			<table width="100%"  border="0" cellspacing="0" cellpadding="0" id="Studenten" style="display: block">
			<tr>
				<td width="10" nowrap>&nbsp;</td>
			   	<td nowrap><a class="MenuItem2" href="personen/studenten_uebersicht.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;&Uuml;bersicht</a></td>
			</tr>
			<tr>
				<td width="10" nowrap>&nbsp;</td>
			   	<td nowrap><a class="MenuItem2" href="personen/student_edit.php?new=1" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Neu</a></td>
			</tr>
			<tr>
				<td width="10" nowrap>&nbsp;</td>
			   	<td nowrap>
			   		<a class="MenuItem2" href="personen/student_vorrueckung.php" target="main">
			   			<img src="../skin/images/menu_item.gif" width="7" height="9">
			   			&nbsp;Vorr&uuml;ckung
			   		</a>
			   	</td>
			</tr>
			</table>
		</td>
	</tr>
	<!--================ Menue Personen->Mitarbeiter =====================-->
	<tr>
		<td nowrap><a href="?Lehre" class="MenuItem1" onClick="return(js_toggle_container('Mitarbeiter'));"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Mitarbeiter</a></td>
	<tr>
	<tr>
		<td nowrap>
			<table width="100%"  border="0" cellspacing="0" cellpadding="0" id="Mitarbeiter" style="display: block">
			<tr>
				<td width="10" nowrap>&nbsp;</td>
		    	<td nowrap><a class="MenuItem2" href="personen/lektor_uebersicht.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;&Uuml;bersicht</a></td>
			</tr>
			<tr>
				<td width="10" nowrap>&nbsp;</td>
		    	<td nowrap><a class="MenuItem2" href="personen/lektor_edit.php?new=1" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Neu</a></td>
			</tr>
			<tr>
				<td width="10" nowrap>&nbsp;</td>
		    	<td nowrap><a class="MenuItem2" href="personen/institutsliste.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Institute</a></td>
			</tr>
			</table>
		</td>
	</tr>
	<!--=========== Menue Funktionen ==============-->
	<tr>
		<td nowrap>
			<a class="MenuItem2" href="personen/funktion.php" target="main">
			<img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Funktionen</a>
		</td>
	</tr>
	</table>
</div>

<HR>
<!-- ******************* Haupt-Menue Stammdaten *******************************-->
<a href="?Stammdaten" class="MenuItem1" onClick="return(js_toggle_container('menueStammdaten'));">
	<img src="../skin/images/menu_item.gif" width="7" height="9" />&nbsp;Stammdaten
</a>
<div id="menueStammdaten" style="display:block;">
	<table class="menue" style="border-right-width:1px;border-right-color:#BCBCBC;">
    <!--Menu Eintrag Berechtigungen-->
	<tr>
       	<td nowrap><a href="?Lehre" class="MenuItem1" onClick="return(js_toggle_container('Berechtigung'));"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Berechtigungen</a></td>
    </tr>
	<tr>
       	<td nowrap>
	  		<table width="100%"  border="0" cellspacing="0" cellpadding="0" id="Berechtigung" style="display:block;">
		  	<tr>
		  		<td width="10" nowrap>&nbsp;</td>
				<td nowrap><a class="MenuItem2" href="stammdaten/benutzerberechtigung_frameset.html" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;&Uuml;bersicht</a></td>
		  	</tr>
		  	<tr>
		  		<td width="10" nowrap>&nbsp;</td>
				<td nowrap><a class="MenuItem2" href="stammdaten/variablen_frameset.html" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Variablen</a></td>
		  	</tr>
		  	</table>
		</td>
	</tr>
    <!--Menu Eintrag Studiengang-->
	<tr>
       	<td nowrap><a href="?Lehre" class="MenuItem1" onClick="return(js_toggle_container('Studiengang'));"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Studiengang</a></td>
    </tr>
	<tr>
      	<td nowrap>
	  		<table width="100%"  border="0" cellspacing="0" cellpadding="0" id="Studiengang" style="display:block;">
		  	<tr>
		  		<td width="10" nowrap>&nbsp;</td>
				<td nowrap><a class="MenuItem2" href="stammdaten/studiengang_frameset.html" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;&Uuml;bersicht</a></td>
		  	</tr>
		  	<tr>
		  		<td width="10" nowrap>&nbsp;</td>
				<td nowrap><a class="MenuItem2" href="stammdaten/studiengang_details.php" target="detail"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Neu</a></td>
		  	</tr>
		  	</table>
		</td>
	</tr>
    <!--Menu Eintrag Lehrverbandsgruppen-->
	<tr>
       	<td nowrap><a href="?lehrverbandsgruppen" class="MenuItem1" onClick="return(js_toggle_container('Lehrverbandsgruppen'));"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Lehrverbandsgruppen</a></td>
    </tr>
	<tr>
       	<td nowrap>
	  		<table width="100%"  border="0" cellspacing="0" cellpadding="0" id="Lehrverbandsgruppen" style="display:block;">
		  	<tr>
		  		<td width="10" nowrap>&nbsp;</td>
				<td nowrap><a class="MenuItem2" href="stammdaten/lvbgruppenverwaltung.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Verwaltung</a></td>
		  	</tr>
		  	<tr><td width="10" nowrap>&nbsp;</td></tr>
		  	</table>
		</td>
	</tr>
	<!--=========== Menue Kommunikation ==============-->
		<tr>
          	<td nowrap><a href="?Lehre" class="MenuItem1" onClick="return(js_toggle_container('Kommunikation'));"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Kommunikation</a></td>
        </tr>
		<tr>
          	<td nowrap>
		  		<table width="100%"  border="0" cellspacing="0" cellpadding="0" id="Kommunikation" style="display:block;">
			  	<tr>
			  		<td width="10" nowrap>&nbsp;</td>
					<td nowrap><a class="MenuItem2" href="kommunikation/kontakt.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Kontakte</a></td>
			  	</tr>
			  	<tr>
			  		<td width="10" nowrap>&nbsp;</td>
					<td nowrap><a class="MenuItem2" href="kommunikation/index.html" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Mail-Verteiler</a></td>
			  	</tr>
			  	<tr><td width="10" nowrap>&nbsp;</td></tr>
			  	</table>
			</td>
		</tr>
        <tr><td width="10" nowrap>&nbsp;</td></tr>
          <!--Menu Eintrag Reihungstest-->
		<tr>
          	<td nowrap><a href="?reihungstest" class="MenuItem1" onClick="return(js_toggle_container('Reihungstest'));"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Reihungstest</a></td>
        </tr>
		<tr>
          	<td nowrap>
		  		<table width="100%"  border="0" cellspacing="0" cellpadding="0" id="Reihungstest" style="display:block;">
			  	<tr>
			  		<td width="10" nowrap>&nbsp;</td>
					<td nowrap><a class="MenuItem2" href="stammdaten/reihungstestverwaltung.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Verwaltung</a></td>
			  	</tr>
			  	<tr><td width="10" nowrap>&nbsp;</td></tr>
			  	</table>
			</td>
		</tr>
        <tr>
          	<td nowrap><a href="?firma" class="MenuItem1" onClick="return(js_toggle_container('Firma'));"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Firma</a></td>
        </tr>
		<tr>
          	<td nowrap>
		  		<table width="100%"  border="0" cellspacing="0" cellpadding="0" id="Firma" style="display:block;">
			  	<tr>
			  		<td width="10" nowrap>&nbsp;</td>
					<td nowrap><a class="MenuItem2" href="stammdaten/firma_frameset.html" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Verwaltung</a></td>
			  	</tr>
			  	<tr><td width="10" nowrap>&nbsp;</td></tr>
			  	</table>
			</td>
		</tr>
        <tr><td width="10" nowrap>&nbsp;</td></tr>
		</table>
</div>

</body>
</html>
