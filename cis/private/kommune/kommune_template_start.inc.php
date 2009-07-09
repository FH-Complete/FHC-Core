<?php
/* Copyright (C) 2008 Technikum-Wien
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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>,
 *          Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>.
 */
 
#-------------------------------------------------------------------------------------------	
/*
*
* @showStartseite HTML Ausgabe der Wettbewerbe, Eigene Wettbewerbe , Eingeladen zu einem Wettkamp
*
* @param $oWettbewerb 	Objekt zum Wettbewerb, Team, Personen, Match
*
* @return showHTML String mit HTML Ausgabe der Wettbewerbe, Eigene Wettbewerbe 
*
*/
	// Plausib
$last=null;
$oWettbewerb->wbtyp_kurzbz='';
$oWettbewerb->wettbewerb_kurzbz='';
kommune_funk_wettbewerb(&$oWettbewerb);	
kommune_funk_eigene_wettbewerb(&$oWettbewerb);
#var_dump($oWettbewerb);

$last=null;
$iTmpWettbewerb=0;
echo '<table  class="tabcontent">';
echo '<tr>';
for ($iTmpZehler=0;$iTmpZehler<count($oWettbewerb->Wettbewerb);$iTmpZehler++) 
{
	if ($iTmpWettbewerb!=0 && ($iTmpWettbewerb%constMaxWettbwerbeZeile==0 || $last!=$oWettbewerb->Wettbewerb[$iTmpZehler]->wbtyp_kurzbz) )
	{
		$iTmpWettbewerb=0;
		echo '</tr><tr>';
	}	
	if ($last!=$oWettbewerb->Wettbewerb[$iTmpZehler]->wbtyp_kurzbz)
	{
		$last=$oWettbewerb->Wettbewerb[$iTmpZehler]->wbtyp_kurzbz;
		echo '<tr>
			<td colspan="'.constMaxWettbwerbeZeile.'">
				<b class="rtop">
					  <b class="r1" style="background: #'.$oWettbewerb->Wettbewerb[$iTmpZehler]->farbe.';"></b> <b class="r2"  style="background: #'.$oWettbewerb->Wettbewerb[$iTmpZehler]->farbe.';"></b> <b class="r3" style="background: #'.$oWettbewerb->Wettbewerb[$iTmpZehler]->farbe.';"></b> <b class="r4" style="background: #'.$oWettbewerb->Wettbewerb[$iTmpZehler]->farbe.';"></b>
				</b>				
				<table class="tabcontent" cellpadding="0" cellspacing="0"  style="background: #'.$oWettbewerb->Wettbewerb[$iTmpZehler]->farbe.';">
					<tr><td>&nbsp;'.$last.'&nbsp;</td></tr>
				</table>
				<b class="rbottom">
					  <b class="r4"  style="background: #'.$oWettbewerb->Wettbewerb[$iTmpZehler]->farbe.';"></b> <b class="r3" style="background: #'.$oWettbewerb->Wettbewerb[$iTmpZehler]->farbe.';"></b> <b class="r2" style="background: #'.$oWettbewerb->Wettbewerb[$iTmpZehler]->farbe.';"></b> <b class="r1"  style="background: #'.$oWettbewerb->Wettbewerb[$iTmpZehler]->farbe.';"></b>
				</b>
							
			</td></tr>';				
	}
	
	if (!empty($oWettbewerb->Wettbewerb[$iTmpZehler]->wettbewerb_kurzbz))
	{
		$oWettbewerb->wbtyp_kurzbz=$oWettbewerb->Wettbewerb[$iTmpZehler]->wbtyp_kurzbz;
		$oWettbewerb->wettbewerb_kurzbz=$oWettbewerb->Wettbewerb[$iTmpZehler]->wettbewerb_kurzbz;
		if ($oWettbewerb->Wettbewerb[$iTmpZehler]->bereits_eingetragen)
			$oWettbewerb->team_kurzbz=$oWettbewerb->Wettbewerb[$iTmpZehler]->daten_eingetragen[0]->team_kurzbz;
		else
			$oWettbewerb->team_kurzbz='';
			
		$iTmpWettbewerb++;
		echo '<td valign="top">

			<b class="rtop">
			  <b class="r1" style="background: #'.$oWettbewerb->Wettbewerb[$iTmpZehler]->farbe.';"></b> <b class="r2"  style="background: #'.$oWettbewerb->Wettbewerb[$iTmpZehler]->farbe.';"></b> <b class="r3" style="background: #'.$oWettbewerb->Wettbewerb[$iTmpZehler]->farbe.';"></b> <b class="r4" style="background: #'.$oWettbewerb->Wettbewerb[$iTmpZehler]->farbe.';"></b>
			</b>

			<table width="100%" cellpadding="0" cellspacing="0" style="background: #'.$oWettbewerb->Wettbewerb[$iTmpZehler]->farbe.';">
				<tr>
					<td valign="top"><h2>&nbsp;'.$oWettbewerb->Wettbewerb[$iTmpZehler]->wettbewerb_kurzbz.'&nbsp;</h2></td>
					<td valign="top" rowspan="4" height="70">'.$oWettbewerb->Wettbewerb[$iTmpZehler]->icon_image.'&nbsp;</td>
				</tr>
				<tr><td>'.$oWettbewerb->Wettbewerb[$iTmpZehler]->wettbewerbart.'</td></tr>
				
				<tr><td>[&nbsp;<a href="'.kommune_funk_create_url('kommune_team_wartung',$oWettbewerb).'">'.($oWettbewerb->Wettbewerb[$iTmpZehler]->bereits_eingetragen?'meine Daten':'anmelden').'</a>&nbsp;]&nbsp;[&nbsp;<a href="'.kommune_funk_create_url('kommune_team_wartung.inc.php',$oWettbewerb).'">zum&nbsp;Spiel</a>&nbsp;]</td></tr>
			</table>

			<b class="rbottom">
				  <b class="r4"  style="background: #'.$oWettbewerb->Wettbewerb[$iTmpZehler]->farbe.';"></b> <b class="r3" style="background: #'.$oWettbewerb->Wettbewerb[$iTmpZehler]->farbe.';"></b> <b class="r2" style="background: #'.$oWettbewerb->Wettbewerb[$iTmpZehler]->farbe.';"></b> <b class="r1"  style="background: #'.$oWettbewerb->Wettbewerb[$iTmpZehler]->farbe.';"></b>
			</b>

		</td>';
	}
}
if ($iTmpWettbewerb==0) // Kein Tabellenelement angelegt (Nun ein Dummy td anlegen das die Tab.stimmt)
	echo '<td>&nbsp;</td>';
echo '<tr>';
echo '</table>';	

?>