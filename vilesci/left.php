<?php
 require("config.inc.php");
 	if (!isset($REMOTE_USER))
 		//die ('REMOTE_USER ist nicht gesetzt!');
		$REMOTE_USER='pam';
	$uid=$REMOTE_USER;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>VileSci Men&uuml;</title>
	<link href="../skin/vilesci.css" rel="stylesheet" type="text/css">

	<script language="JavaScript" type="text/javascript">
	<!--
		__js_page_array = new Array();
		__js_tab_array= new Array();
		__js_menu_array= new Array();
		__js_menu_array[0]='menueStammdaten';
		__js_tab_array[0]='tabStammdaten';
		__js_menu_array[1]='menuePersonen';
		__js_tab_array[1]='tabPersonen';
		__js_menu_array[2]='menueLehre';
		__js_tab_array[2]='tabLehre';

   	function js_toggle_container(conid)
   	{
			if (document.getElementById)
			{
      	  	var block = "table-row";
				if (navigator.appName.indexOf('Microsoft') > -1)
					block = 'block';
      	   var status = __js_page_array[conid];
         	if (status == null)
            	status = "none";
 				if (status == "none")
 			   {
         	  	document.getElementById(conid).style.display = 'block';
            	__js_page_array[conid] = "visible";
         	}
         	else
           	{
           		document.getElementById(conid).style.display = 'none';
            	__js_page_array[conid] = "none";
      		}
      	   return false;
     		}
     		else
     			return true;
  		}

	function js_show_tab(tabid)
   	{
		for(i=0;i<(__js_menu_array.length);i++)
			if (__js_menu_array[i]==tabid)
			{
				document.getElementById(__js_menu_array[i]).style.display = 'block';
				document.getElementById(__js_tab_array[i]).class = 'tab active';
			}
       		else
       		{
       			document.getElementById(__js_menu_array[i]).style.display = 'none';
       			document.getElementById(__js_tab_array[i]).class = 'tab';
			}
        return true;
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

<table class="tabs">
	<tr>
		<td class="tab" id="tabStammdaten" onClick="js_show_tab('menueStammdaten');">Stammdaten</td>
		<td class="tab" id="tabPersonen" onClick="js_show_tab('menuePersonen');">Personen</a></td>
		<td class="tab" id="tabLehre" onClick="js_show_tab('menueLehre');">Lehre</td>
	</tr>
</table>

<!--____________Menue Stammdaten______________________________________________-->
<div id="menueStammdaten" style="display:block;">
	<table class="menue" style="border-right-width:1px;border-right-color:#BCBCBC;">
		<tr>
			<td>&nbsp;</td>
		</tr>
		<!--______Menue Admin->Personen_______________________________________-->
		<tr>
        	<td nowrap>
				<a href="?Lehre" class="MenuItem1" onClick="return(js_toggle_container('Personen'));">
      			<img src="../skin/images/menu_item.gif" width="7" height="9" />
      			&nbsp;Personen
        		</a>
        	</td>
      </tr>
		<tr>
        	<td nowrap>
				<table width="100%"  border="0" cellspacing="0" cellpadding="0" id="Personen" style="display:block;">
				<tr>
					<td width="10" nowrap>&nbsp;</td>
					<td nowrap><a class="MenuItem2" href="personen/index.html" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Suche</a></td>
				</tr>
				<tr>
					<td width="10" nowrap>&nbsp;</td>
					<td nowrap><a class="MenuItem2" href="personen/funktion.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Funtionen</a></td>
				</tr>
			  	<!--__Menue Admin->Personen->Studenten___________________________-->
				<tr>
					<td width="10" nowrap>&nbsp;</td>
					<td nowrap>
					    <a href="?Lehre" class="MenuItem1" onClick="return(js_toggle_container('Studenten'));"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Studenten</a>
						<table width="100%"  border="0" cellspacing="0" cellpadding="0" id="Studenten" style="display: none">
						<tr>
							<td width="10" nowrap>&nbsp;</td>
					    	<td nowrap><a class="MenuItem2" href="personen/studenten_uebersicht.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;&Uuml;bersicht</a></td>
						</tr>
						<tr>
							<td width="10" nowrap>&nbsp;</td>
					    	<td nowrap><a class="MenuItem2" href="personen/student_edit.php?new=1" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Neu</a></td>
						</tr>
						</table>
					</td>
				</tr>
				<!--Menu Eintrag Personen - Lektoren -->
				<tr>
					<td width="10" nowrap>&nbsp;</td>
					<td nowrap>
						<a href="?Lehre" class="MenuItem1" onClick="return(js_toggle_container('Lektoren'));"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Lektoren</a>
						<table width="100%"  border="0" cellspacing="0" cellpadding="0" id="Lektoren" style="display: none">
						<tr>
							<td width="10" nowrap>&nbsp;</td>
					    	<td nowrap><a class="MenuItem2" href="personen/lektor_uebersicht.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;&Uuml;bersicht</a></td>
						</tr>
						<tr>
							<td width="10" nowrap>&nbsp;</td>
					    	<td nowrap><a class="MenuItem2" href="personen/lektor_edit.php?new=1" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Neu</a></td>
						</tr>
						</table>
					</td>
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
		<tr><td>&nbsp;</td></tr>
		<!--Menu Eintrag Kommunikation-->
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
					<td nowrap><a class="MenuItem2" href="kommunikation/mlists/index.html" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Mail-Verteiler</a></td>
			  	</tr>
			  	<tr><td width="10" nowrap>&nbsp;</td></tr>
			  	</table>
			</td>
		</tr>
		<!--Menu Eintrag Stundenplan-->
		<tr>
          	<td nowrap><a href="?Lehre" class="MenuItem1" onClick="return(js_toggle_container('Stundenplan'));"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Stundenplan</a></td>
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
					<td nowrap><a class="MenuItem2" href="lehre/zeitwuensche.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Zeitw&uuml;nsche</a></td>
			  	</tr>
			  	<tr>
			  		<td width="10" nowrap>&nbsp;</td>
					<td nowrap><a class="MenuItem2" href="lehre/lv_verteilung/lv_verteilung.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Lehreinheiten</a></td>
			  	</tr>
			  	<!--<tr>
			  		<td width="10" nowrap>&nbsp;</td>
					<td nowrap><a class="MenuItem2" href="lehre/lehrveranstaltung.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Lehrveranstaltungen</a></td>
			  	</tr>-->
			  	<tr>
			  		<td width="10" nowrap>&nbsp;</td>
					<td nowrap><a class="MenuItem2" href="lehre/stdplan_insert.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Stundenplan</a></td>
			  	</tr>
			  	<tr>
			  		<td width="10" nowrap>&nbsp;</td>
					<td nowrap><a class="MenuItem2" href="lehre/stdplan_delete.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Delete</a></td>
			  	</tr>
			  	<!--
			  	<td width="10" nowrap>&nbsp;</td>
					<td nowrap><a class="MenuItem2" href="lehre/einheit_menu.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Einheiten</a></td>
			  	</tr> -->
			  	<!--Menu Eintrag Lehrfach-->
			  	<tr>
				  	<td width="10" nowrap>&nbsp;</td>
				  	<td nowrap>
				  		<a href="?Lehre" class="MenuItem1" onClick="return(js_toggle_container('Lehrfach'));">
				  			<img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Lehrfach
				  		</a>
				  		<table width="100%"  border="0" cellspacing="0" cellpadding="0" id="Lehrfach" style="display: none;">
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
				<tr>
			  		<td width="10" nowrap>&nbsp;</td>
					<td nowrap><a class="MenuItem2" href="lehre/check/index.html" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Checken</a></td>
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
        <tr><td width="10" nowrap>&nbsp;</td></tr>
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

			  	<tr><td width="10" nowrap>&nbsp;</td></tr>
			  	</table>
			</td>
		</tr>
        <tr><td width="10" nowrap>&nbsp;</td></tr>
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

			  	<tr><td width="10" nowrap>&nbsp;</td></tr>
			  	</table>
			</td>
		</tr>
		<tr><td width="10" nowrap>&nbsp;</td></tr>
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

			  	<tr><td width="10" nowrap>&nbsp;</td></tr>
			  	</table>
			</td>
		</tr>
        <tr><td width="10" nowrap>&nbsp;</td></tr>
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
        <tr><td width="10" nowrap>&nbsp;</td></tr>
		</table>
</div>
<div id="menuePersonen" style="display: none;">
bal
 </div>
 <div id="menueLehre" style="display: none;">
 bla
  </div>

 </body>
 </html>
