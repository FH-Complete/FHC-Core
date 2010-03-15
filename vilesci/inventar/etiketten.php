<?php
	$path='../';
	if (!is_file($path.'config/vilesci.config.inc.php'))
			$path='../../';
	if (!is_file($path.'config/vilesci.config.inc.php'))
			$path='../../../';
			
//Zebra Etiketen 5cm*2.5cm
  	$nummer=trim((isset($_REQUEST['nummer']) ? str_replace(array('`','�','*','~'),'+',$_REQUEST['nummer']):''));
	
	// Formel: Gr��e in cm * 72 dpi / 2,54 = Ma� in Pixel; Ma� in Pixel * 2,54 / 72 dpi = Gr��e in cm
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
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/frameset.dtd">
<html>
<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta http-equiv="pragma" content="no-cache">
		<meta http-equiv="expires" content="0">
		<link rel="stylesheet" href="<?php echo $path;?>skin/vilesci.css" type="text/css">
		<link rel="stylesheet" href="<?php echo $path;?>include/js/jquery.css" rel="stylesheet" type="text/css">
	
		<script src="<?php echo $path;?>include/js/jquery.js" type="text/javascript"></script>
		<script src="<?php echo $path;?>include/js/jquery-ui.js" type="text/javascript"></script>
		<script src="<?php echo $path;?>include/js/jquery.autocomplete.js" type="text/javascript"></script>
		<script src="<?php echo $path;?>include/js/jquery.autocomplete.min.js" type="text/javascript"></script>
		<script src="<?php echo $path;?>include/js/jquery-barcode-1.3.3.js" type="text/javascript"></script>	
	
	<title><?php echo $nummer;?></title>
		<style type="text/css" >
			body {background: #ffffff;}
			div {text-align:left;background: #ffffff;}
			div.etikette_kpl {text-align: center;vertical-align: middle;height:<?php echo $etikette_druck_height;?>px;width:<?php echo $etikette_druck_width;?>px;display:inline;background: #FFFFFF;}
			table.etikette_kpl {height:<?php echo $etikette_druck_height;?>px;width:<?php echo $etikette_druck_width;?>px;display:inline;background:  #FFFFFF; border:1px;}
			table.etikette_kpl tr {text-align:left;}
			table.etikette_kpl td {text-align:left;white-space: nowrap;}
			img.etikette_logo {height:<?php echo $etikette_logo_height;?>px;width:<?php echo $etikette_logo_width;?>px;border:0px;}
			div.bcTarget {display:inline;height:<?php echo $etikette_logo_height;?>px;width:<?php echo $etikette_logo_width;?>px;border:0px;}
		</style>
</head>
<?php
	if (empty($nummer))
	{ 
	//onchange="if (this.value.length>0) {setTimeout('document.sendform.submit()',1500);}"
?>	
		<body  onload="window.print();">
			<form target="_blank" name="sendform" action="<?php echo $_SERVER["PHP_SELF"];?>" method="post" enctype="application/x-www-form-urlencoded">
				<label for="nummer">Inventarnummer :</label>&nbsp;
					<input id="nummer" name="nummer" type="text" size="10" maxlength="30" value="">&nbsp;
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
							  $('#nummer').autocomplete('inventar_autocomplete.php',{
								minChars:2,
								scroll: true, 
						        scrollHeight: 200, 
								width:350,
								onItemSelect:selectItem,
								formatItem:formatItem,
								onFindValue: findValue, 
								extraParams:{'work':'nummer'}
							  });
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
<body onclick="window.print();">
	<div>
		<table class="etikette_kpl">
			<tr> 
				<td>
					<img class="etikette_logo" src="../../skin/images/technikum_logo.gif">
				</td>
			</tr>
			<tr>
				<td>
					<div id="bcTarget"></div>
					<script type="text/javascript" language="JavaScript1.2">	
						if ('<?php echo $nummer;?>' != '') 	
						{		
							$("#bcTarget").barcode('<?php echo $nummer;?>', 'code128',{output: "<?php echo $output; ?>",barWidth:<?php echo $etikette_width;?>, barHeight:<?php echo $etikette_height;?>}); 
						}	
					</script>
				</td>
			</tr>
		</table>
	</div>
	<noscript>
		  Bitte Javascript ist nicht aktiv ! Bitte aktivieren.
	</noscript>
</body>
</html>
