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
 
// Liste der Archive ab 2006

  require_once('../../config/cis.config.inc.php');
  require_once('../../include/basis_db.class.php');
  
  $iStartJahr=0;	
  $iEndeJahr=0;	
  $cMonat='08';

// bsp. Verzeichnis der Archivierung fuer das Jahr 2006  http://cis.technikum-wien.at/cis200608/

  $iEnde=Date('Y')-1;
  if (Date('m') > 8)
	  $iEnde=Date('Y');
  
  for ($i=1999;$i<=Date('Y');$i++)
  {

  		$cSuchVerzeichnis='../../cis'.$i.$cMonat;
		//echo "<br>Check $cSuchVerzeichnis ::: ";
  		if (is_dir($cSuchVerzeichnis))
		{
			if (empty($iStartJahr))
				$iStartJahr=$i;
			$iEndeJahr=$i;  
			//echo "$iStartJahr $iEndeJahr <hr>";
		}
  }
  if ( empty($iStartJahr) || empty($iEndeJahr) )
		exit('Keine '.CAMPUS_NAME.' - Archive gefunden.');  
  
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
<title>Archiv-Links LV-Info</title>
</head>

<body>
<table id="inhalt" class="tabcontent">
  <tr>
    <td class="tdwidth10">&nbsp;</td>
    <td><table class="tabcontent">
      <tr>
        <td class="ContentHeader"><font class="ContentHeader">&nbsp;<?php echo CAMPUS_NAME; ?> Archiv-Links LV-Info <?php echo ($iStartJahr!=$iEndeJahr?'(e)':'') ; ?> von <?php echo $iStartJahr; ?> - <?php echo $iEndeJahr; ?></font></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
	  <tr>
	  	<td>Dieses Dokument soll dazu dienen Antworten auf h&auml;ufig gestellte Fragen &uuml;ber das Archiv-Links LV-Info zu beantworten. Sollten Sie hier keine Antworten finden, melden Sie sich bitte bei <a class="Item" href="mailto:support@technikum-wien.at">support@technikum-wien.at</a>.</td>
	  </tr>

	  <tr>
	 	<td>&nbsp;</td>
	  </tr>

<!--
	  <tr>
	  	<td>
				<a class="Item2" href= "#Liste_der_Archiv_Links_LV_Info"><img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Archiv-Links LV-Info</a><br>
		</td>
	  </tr>
	  <tr>
	 	<td>&nbsp;</td>
	  </tr>
	  <tr>
	 	<td>&nbsp;</td>
	  </tr>
	  <tr>
	 	<td>&nbsp;</td>
	  </tr>
-->
	  
	  <tr>
	 	<td class="ContentHeader2">&nbsp;Liste der Archiv-Links LV-Info</td>
	  </tr>
	  <tr>
	  	<td><a name="Liste_der_Archiv_Links_LV_Info">&nbsp;</a></td>
	  </tr>
<?php

		for ($i=$iStartJahr;$i<=$iEndeJahr;$i++)
		{
			$cSuchVerzeichnis='../../cis'.$i.$cMonat;
			echo '<tr><td><a href="'.$cSuchVerzeichnis.'" target="_parent">Archiv '.$i.'</a></td></tr>';
		}
?>	  
	  <tr>
	  	<td>&nbsp;</td>
	  </tr>
	  <tr>
	 	<td>&nbsp;</td>
	  </tr>

	  
	  
	  
	  <tr>
	 	<td>&nbsp;</td>
	  </tr>
	  <tr>
	 	<td>&nbsp;</td>
	  </tr>
	  <tr>
	 	<td>&nbsp;</td>
	  </tr>	  
	  <tr>
	  	<td>Sollten Sie hier keine Archivdaten finden, melden Sie sich bitte bei <a class="Item" href="mailto:support@technikum-wien.at">support@technikum-wien.at</a>.</td>
	  </tr>	  
	  
    </table></td>
	<td class="tdwidth30">&nbsp;</td>
  </tr>
</table>
</body>
</html>
 