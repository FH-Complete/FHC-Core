<?php
/* Copyright (C) 2010 Technikum-Wien
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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 */
/**
 * Druckt die Inventarpickerl
 */

//Zebra Etiketen 5cm*2.5cm
$inventarnummer=trim((isset($_REQUEST['inventarnummer']) ? str_replace(array('`','�','*','~'),'+',$_REQUEST['inventarnummer']):''));

// Formel: Groesse in cm * 72 dpi / 2,54 = Masse in Pixel; Masse in Pixel * 2,54 / 72 dpi = Groesse in cm
$dpiDefault=96;

$dpi=trim((isset($_REQUEST['dpi']) ? $_REQUEST['dpi']:$dpiDefault));
if (!is_numeric($dpi))
	$dpi=$dpiDefault;
$dpi=(int)$dpi;

// GesamtEtikette
$etikette_druck_height=(int)(2.54 * ($dpi/2.54));		// 2.54 - '72'
$etikette_druck_width=(int)(5 * ($dpi/2.54));			// 5cm - '142'
// Logo 4cm * 1cm
$etikette_logo_height=(int)(1 * ($dpi/2.54)); 			// 1cm - '28'
$etikette_logo_width=(int)(4 * ($dpi/2.54));			// 4cm - '113'
// Barcode
$etikette_height=(int)(1 * ($dpi/2.54));				// 1cm - '28'
$etikette_width=(int)((int)(4 * ($dpi/2.54))/100);				// 4cm - '113'

$browser=strtolower($_SERVER['HTTP_USER_AGENT']);
$output='css';
if (!strstr($browser,'msie'))
	$output='svg';
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta http-equiv="pragma" content="no-cache">
	<meta http-equiv="expires" content="0">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<!--	<link rel="stylesheet" href="../../include/js/jquery.css" rel="stylesheet" type="text/css"> -->

	<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
	<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css"/>

	<title>Etiketten</title>

<?php
	if (empty($inventarnummer))
	{
	//onchange="if (this.value.length>0) {setTimeout('document.sendform.submit()',1500);}"
?>
	</head>
		<body>
			<h1>Etiketten</h1>
			<form target="_blank" name="sendform" action="<?php echo $_SERVER["PHP_SELF"];?>" method="post" enctype="application/x-www-form-urlencoded">
				<label for="inventarnummer">Inventarnummer :</label>&nbsp;
					<input id="inventarnummer" name="inventarnummer" type="text" size="10" maxlength="30" value="">&nbsp;
					<script type="text/javascript">
						function selectItem(li)
						{
							//	onItemSelect (default value: none)
							//	  A JavaScript function that will be called when an item is selected. The
							//	  autocompleter will specify a single argument, being the LI element selected.
							//	  This LI element will have an attribute "extra" that contains an array of all
							//	  cells that the backend specified. See the source code of
							// --------------------------------------------------------------------------------
							if ((li.extra != null) && (li.extra != ""))
							{
								if ((li.extra[0] != null) && (li.extra[0] != ""))
									alert(li.extra[0]);
								if ((li.extra[1] != null) && (li.extra[1] != ""))
									alert(li.extra[1]);
							}
						   return false;
						}
						function formatItem(row)
						{
							//	  formatItem (default value: none)
							//	    A JavaScript funcion that can provide advanced markup for an item. For each
							//	    row of results, this function will be called. The returned value will be
							//	    displayed inside an LI element in the results list. Autocompleter will
							//	    provide 3 parameters: the results row, the position of the row in the list
							//	    of results, and the number of items in the list of results. See the source
							//	    code of http://www.dyve.net/jquery?autocomplete for an example.
							// --------------------------------------------------------------------------------
							row[0] = row[0].replace('`', '+');
							row[0] = row[0].replace('`', '+');
							row[0] = row[0].replace('�', '+');
							row[0] = row[0].replace('�', '+');
							row[0] = row[0].replace('*', '+');
							row[0] = row[0].replace('*', '+');
							row[0] = row[0].replace('-', '+');
							row[0] = row[0].replace('-', '+');
						    return row[0] + " <i>" + row[1] + "</i> ";
						}

						function findValue(li)
						{
						     if( li == null ) return alert("No match!");
								 // if coming from an AJAX call, let's use the product id as the value
						     if( !!li.extra ) var sValue = li.extra[0];
							    // otherwise, let's just display the value in the text box
							 else var sValue = li.selectValue;
								 alert("The value you selected was: " + sValue);
						 }

						// http://www.pengoworks.com/workshop/jquery/autocomplete_docs.txt
						$(document).ready(function() {
							$('#inventarnummer').autocomplete({
								source: "inventar_autocomplete.php?work=inventarnummer",
								minLength:2,
								response: function(event, ui)
								{
									//Value und Label fuer die Anzeige setzen
									for(i in ui.content)
									{
										ui.content[i].value=ui.content[i].inventarnummer;
										ui.content[i].label=ui.content[i].inventarnummer+" "+ui.content[i].beschreibung;
									}
								},
								select: function(event, ui)
								{
									ui.item.value=ui.item.inventarnummer;
									setTimeout('document.sendform.submit()',1500);
								}
							});
							/*  $('#inventarnummer').autocomplete('inventar_autocomplete.php',{
								minChars:2,
								scroll: true,
						        scrollHeight: 200,
								width:350,
								onItemSelect:selectItem,
								formatItem:formatItem,
								onFindValue: findValue,
								extraParams:{'work':'inventarnummer'}
							  }); */
					  });
					</script>
					<input type="Submit">
			</form>
			<noscript>
				  Bitte Javascript ist nicht aktiv ! Bitte aktivieren.
			</noscript>
		</body>
</html>
<?php
exit;
}
/*
Parameter Type Default value Detail
barWidth int 1 width of a bar
barHeight int 50 container height
showHRI bool true display text (HRI : Human readable Interpretation)
bgColor text #FFFFFF background color
color text #000000 barcode color
fontSize text 10px font size of the HRI
output text css output type : css, svg or bmp
*/
?>
	<style type="text/css" >
		body {background-color: #ffffff;}
		div {text-align:left;background: #ffffff;}
		div.etikette_kpl {text-align: center;vertical-align: middle;height:<?php echo $etikette_druck_height;?>px;width:<?php echo $etikette_druck_width;?>px;display:inline;background: #FFFFFF;}
		table.etikette_kpl {height:<?php echo $etikette_druck_height;?>px;width:<?php echo $etikette_druck_width;?>px;display:inline;background:  #FFFFFF; border:1px;}
		table.etikette_kpl tr {text-align:left;}
		table.etikette_kpl td {text-align:left;white-space: nowrap;}
		img.etikette_logo {height:<?php echo $etikette_logo_height;?>px;width:<?php echo $etikette_logo_width;?>px;border:0px;}
		div.bcTarget {display:inline;height:<?php echo $etikette_logo_height;?>px;width:<?php echo $etikette_logo_width;?>px;border:0px;}
	</style>
</head>
<body onload="window.print();">
	<div>
		<table class="etikette_kpl">
			<tr>
				<td>
					<img class="etikette_logo" src="../../skin/images/TWLogo_klein.gif">
				</td>
			</tr>
			<tr>
				<td>
					<div id="bcTarget"></div>
					<script type="text/javascript" language="JavaScript1.2">
						if ('<?php echo $inventarnummer;?>' != '')
						{
							$("#bcTarget").barcode('<?php echo $inventarnummer;?>', 'code128',{output: "<?php echo $output; ?>",barWidth:<?php echo $etikette_width;?>, barHeight:<?php echo $etikette_height;?>});
						}
					</script>
				</td>
			</tr>
		</table>
	</div>
	<noscript>
		  Javascript ist nicht aktiv ! Bitte aktivieren.
	</noscript>
</body>
</html>
