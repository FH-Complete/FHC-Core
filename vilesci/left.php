<?php
 require("config.inc.php");
 	if (!isset($REMOTE_USER))
		$REMOTE_USER='pam';
	$uid=$REMOTE_USER;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../skin/vilesci.css" rel="stylesheet" type="text/css">

<script language="JavaScript">
<!--
	__js_page_array = new Array();

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
            	document.getElementById(conid).style.display = block;
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
//-->
</script>
<title>VileSci Men&uuml;</title>
</head>

<body>
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
<tr>
	<td width="159" valign="top" nowrap>
		<table width="100%"  border="0" cellspacing="0" cellpadding="0" style="border-right-width:1px;border-right-color:#BCBCBC;">
		<tr>
			<td>&nbsp;</td>
		</tr>
		<!--Menu Eintrag Personen-->
		<tr>
          	<td nowrap><a href="?Lehre" class="MenuItem1" onClick="return(js_toggle_container('Personen'));"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Personen</a></td>
        </tr>
		<tr>
          	<td nowrap>
		  		<table width="100%"  border="0" cellspacing="0" cellpadding="0" id="Personen" style="display: visible;">
			  	<tr>
			  		<td width="10" nowrap>&nbsp;</td>
					<td nowrap><a class="MenuItem2" href="personen/index.html" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Suche</a></td>
			  	</tr>
			  	<tr>
			  		<td width="10" nowrap>&nbsp;</td>
					<td nowrap><a class="MenuItem2" href="personen/funktion.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Funtionen</a></td>
			  	</tr>
			  	<!--Menu Eintrag Personen->Studenten -->
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
				<!--Menu Eintrag Personen->Lektoren -->
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
					    	<td nowrap><a class="MenuItem2" href="stundenplan/einheit_menu.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;&Uuml;bersicht</a></td>
						</tr>						
						<tr>
							<td width="10" nowrap>&nbsp;</td>
					    	<td nowrap><a class="MenuItem2" href="stundenplan/einheit_menu.php?newFrm=true" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Neu</a></td>
						</tr>
						</table>
					</td>
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
		  		<table width="100%"  border="0" cellspacing="0" cellpadding="0" id="Kommunikation" style="display: visible;">
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
		  		<table width="100%"  border="0" cellspacing="0" cellpadding="0" id="Stundenplan" style="display: visible;">
			  	<tr>
			  		<td width="10" nowrap>&nbsp;</td>
					<td nowrap><a class="MenuItem2" href="http://cis.technikum-wien.at/stdplan/index.html" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Cis Stundenplan</a></td>
			  	</tr>
			  	<tr>
			  		<td width="10" nowrap>&nbsp;</td>
					<td nowrap><a class="MenuItem2" href="stundenplan/zeitwuensche.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Zeitw&uuml;nsche</a></td>
			  	</tr>
			  	<tr>
			  		<td width="10" nowrap>&nbsp;</td>
					<td nowrap><a class="MenuItem2" href="stundenplan/lv_verteilung/lv_verteilung.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Lehreinheiten</a></td>
			  	</tr>
			  	<tr>
			  		<td width="10" nowrap>&nbsp;</td>
					<td nowrap><a class="MenuItem2" href="stundenplan/stdplan_insert.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Stundenplan</a></td>
			  	</tr>
			  	<tr>
			  		<td width="10" nowrap>&nbsp;</td>
					<td nowrap><a class="MenuItem2" href="stundenplan/stdplan_delete.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Delete</a></td>
			  	</tr>
			  	<!--
			  	<td width="10" nowrap>&nbsp;</td>
					<td nowrap><a class="MenuItem2" href="stundenplan/einheit_menu.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Einheiten</a></td>
			  	</tr>-->
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
								<td nowrap><a class="MenuItem2" href="stundenplan/lehrfach.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Verwaltung</a></td>
						  	</tr>
						  	<tr>
						  		<td width="10" nowrap>&nbsp;</td>
								<td nowrap><a class="MenuItem2" href="stundenplan/lehrfach/wartung.php" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Wartung</a></td>
						  	</tr>
					  	</table>
					</td>
				</tr>
				<tr>
			  		<td width="10" nowrap>&nbsp;</td>
					<td nowrap><a class="MenuItem2" href="stundenplan/check/index.html" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Checken</a></td>
			  	</tr>
			  	<tr>
			  		<td width="10" nowrap>&nbsp;</td>
					<td nowrap><a class="MenuItem2" href="stundenplan/import/index.html" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Import</a></td>
			  	</tr>
			  	<tr>
			  		<td width="10" nowrap>&nbsp;</td>
					<td nowrap><a class="MenuItem2" href="stundenplan/export/index.html" target="main"><img src="../skin/images/menu_item.gif" width="7" height="9">&nbsp;Export</a></td>
			  	</tr>
			  	</table>
			</td>
		</tr>
		</table>

  	</td>
 </tr>
 </table>
 </body>
 </html>