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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */

require_once('../../../../config/cis.config.inc.php');
require_once('../../../../include/functions.inc.php');
require_once('../../../../include/phrasen.class.php');

$sprache = getSprache(); 
$p= new phrasen($sprache);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../../skin/style.css.php" rel="stylesheet" type="text/css">
<title><?php echo $p->t('courseInformation/ectsLvInfo');?></title>

</head>
<body style="padding: 10px">
<h1><?php echo $p->t('courseInformation/lvInfoBeispiele');?></h1>
			      <table class="tabcontent">
			         <tr>
			               <td width="85%">
				              &nbsp;
						    </td>
							<td>
								<ul>
								<li>&nbsp;<a class="Item" href='index.php'><font size='3'><?php echo $p->t('global/bearbeiten');?></font></a></li>
								<li>&nbsp;<a class="Item" href='freigabe.php'><font size='3'><?php echo $p->t('courseInformation/freigabe')?></font></a></li>
								<li>&nbsp;<a class="Item" href='beispiele.php'><font size='3'><?php echo $p->t('global/beispiele');?></font></a></li>
								<li>&nbsp;<a class="Item" href='terminologie.php'><font size='3'><?php echo $p->t('courseInformation/terminologie')?></font></a></li>
				 				</ul>
							</td>
			          </tr>
			       </table>
			  

	   <table class="tabcontent">
		   <tr>
			   <td class="tdwidth10">
			   <ul>
			     <!--
				 <li><a href='../../../../documents/lva_info/Beispiel__IT_Projektarbeit_6.pdf' target="_blank">Beispiel IT Projektarbeit (kommentiert und ausgef&uuml;llt)</a></li>
			     <li><a href='../../../../documents/lva_info/Beispiel_Balog.pdf' target="_blank"">Beispiel Computerarchitektur (ausgef&uuml;llt)</a></li>
			     <li><a href='../../../../documents/lva_info/Beispiel_Woletz.pdf' target="_blank">Beispiel Projektmanagement (ausgef&uuml;llt)</a></li>
			     -->
			     <li><a href='Beispiel_Projektmarketing.pdf' target='_blank' class='Item'><?php echo $p->t('courseInformation/beispielProjektmarketing');?></a></li>
			     <li><a href='Beispiel_ITProjektarbeit_at.pdf' target='_blank' class='Item'><?php echo $p->t('courseInformation/beispielItProjektarbeitDeutsch');?></a></li>
			     <li><a href='Beispiel_ITProjektarbeit_en.pdf' target='_blank' class='Item'><?php echo $p->t('courseInformation/beispielItProjektarbeitEnglisch');?></a></li>
			     <li><a href='Beispiel_Computerarchitektur.pdf' target='_blank' class='Item'><?php echo $p->t('courseInformation/beispielComputerarchitektur');?></a></li>
			     <li><a href='Beispiel_Change_Management.pdf' target='_blank' class='Item'><?php echo $p->t('courseInformation/beispielMSEChangeManagement');?></a></li>
			   </ul>
			   </td>
		   </tr>
	   </table>
	
</body>
</html>