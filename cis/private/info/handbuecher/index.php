<?php
require_once('../../../../config/cis.config.inc.php');
require_once('../../../../include/functions.inc.php');

$user=get_uid();

$lektor=check_lektor($user);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../../skin/style.css.php" rel="stylesheet" type="text/css"><title>Handbuch</title></head>
<body>
<table class="tabcontent" id="inhalt">
  <tbody><tr>
    <td class="tdwidth10">&nbsp;</td>
    <td>
    <table class="tabcontent">
      <tbody>
	  <tr>
        <td class="ContentHeader"><font class="ContentHeader">&nbsp;Handb&uuml;cher</font></td>
      </tr>
<?php
if($lektor)
{
	

?>
      <tr>
        <td>&nbsp;</td>
      </tr>
	  <tr>
        <td class="ContentHeader2"><font class="ContentHeader2">&nbsp;Abgabe OPUS</font></td>
      </tr>
<!--
	  <tr class="liste0">
	  	<td>
			<a target="_blank" href="Projektarbeitsabgabe_FHTW_Anleitung.pdf" class='Item'>Projektarbeitsabgabe Anleitung</a>
	  	</td>
	  </tr>
-->
	  <tr class="liste0">
	  	<td>
			<a target="_blank" href="Projektarbeitsabgabe_FHTW_Anleitung_A.pdf" class='Item'>Projektarbeitsabgabe Anleitung Assistenz</a>
	  	</td>
	  </tr>
	  <tr class="liste1">
	  	<td>
			<a target="_blank" href="Projektarbeitsabgabe_FHTW_Anleitung_L.pdf" class='Item'>Projektarbeitsabgabe Lektor(inn)en</a>
	  	</td>
	  </tr>

	  <tr class="liste0">
	  	<td>
			<a target="_blank" href="Publikationsdatenbank_FHTW_Handbuch.pdf" class='Item'>Publikationsdatenbank Handbuch</a>
	  	</td>
	  </tr>

      <tr>
        <td>&nbsp;</td>
      </tr>	  
	  <tr>
        <td class="ContentHeader2"><font class="ContentHeader2">&nbsp;Benotungstool</font></td>
      </tr>
	  <tr class="liste0">
	  	<td>
			<a target="_blank" href="../../../cisdocs/handbuch_benotungstool.pdf" class='Item'>Handbuch Benotungstool</a>
	  	</td>
	  </tr>
	  <tr>
        <td>&nbsp;</td>
      </tr>	  
	  <tr>
        <td class="ContentHeader2"><font class="ContentHeader2">&nbsp;Moodle</font></td>
      </tr>
	  <tr class="liste0">
	  	<td>
			<a target="_blank" href="../../../cisdocs/Moodle-Handbuch.pdf" class='Item'>Handbuch Moodle</a>
	  	</td>
	  </tr>
<?php
}
?>
    </tbody></table></td>
	<td class="tdwidth30">&nbsp;</td>
  </tr>
</tbody></table>
</body></html>