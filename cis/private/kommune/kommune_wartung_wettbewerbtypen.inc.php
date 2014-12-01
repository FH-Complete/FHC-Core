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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
 
#-------------------------------------------------------------------------------------------	
/* 
*	Wettbewerbstypen - Pflege
*
*		Aktionen: Anzeige, Anlage, Aenderung und Loeschen
*		Ansicht : Voll oder Popup (window.opener)	
*
*
*/
	if (!isset($oWettbewerb) || !isset($oWettbewerb->wartungsberechtigt) || !$oWettbewerb->wartungsberechtigt)
		die('Sie sind nicht berechtigt f&uuml;r diese Seite !  <a href="javascript:history.back()">Zur&uuml;ck</a>');
// ------------------------------------------------------------------------------------------
// Datenverarbeiten	
// ------------------------------------------------------------------------------------------
	$work=(isset($_REQUEST['work']) ? $_REQUEST['work'] :'');
	if (!empty($work) && isset($_REQUEST['wbtyp_kurzbz'])  && !empty($_REQUEST['wbtyp_kurzbz']))
	{
		$Wettbewerb= new komune_wettbewerb();
		
		$Wettbewerb->wbtyp_kurzbz=$_REQUEST["wbtyp_kurzbz"];
		$Wettbewerb->bezeichnung=$_REQUEST["bezeichnung"];
		if (empty($Wettbewerb->bezeichnung))
			$Wettbewerb->bezeichnung=constEingabeFehlt;
		$Wettbewerb->farbe=$_REQUEST["farbe"];
			
		if ($work=='save')
		{
			$Wettbewerb->new=false;
			if ( (isset($_REQUEST["wbtyp_kurzbz_old"]) && $_REQUEST["wbtyp_kurzbz"] != $_REQUEST["wbtyp_kurzbz_old"] )
			||  (!isset($_REQUEST["wbtyp_kurzbz_old"]) || empty($_REQUEST["wbtyp_kurzbz_old"])) )
			{
				$Wettbewerb->new=true;
				if (isset($_REQUEST["wbtyp_kurzbz_old"]) && $_REQUEST["wbtyp_kurzbz"] != $_REQUEST["wbtyp_kurzbz_old"])
					$Wettbewerb->deleteWettbewerbTyp($_REQUEST["wbtyp_kurzbz_old"]);
			}	
			if(!$resurce=$Wettbewerb->saveWettbewerbTyp())
			{	
				$oWettbewerb->errormsg[]='Fehler bei der '.($Wettbewerb->new?' Neuanlage ':' &Auml;nderung ').' '.$Wettbewerb->errormsg;
			}
			else
			{
				$oWettbewerb->errormsg[]=$_REQUEST["wbtyp_kurzbz"].' '.($Wettbewerb->new?' Neuanlage ':' &Auml;nderung ').' erfolgreich! '.$Wettbewerb->errormsg;
				echo ' <script language="JavaScript1.2" type="text/javascript">
						<!--
							if (window.opener && !window.opener.closed) {
								if (confirm("Soll die Hauptseite neu aufgebaut werden?")) {
									window.opener.location.reload();
								}	
							}
						-->
						</script>				
					';
			}
		}
		
		if ($work=='del')
		{
			if(!$Wettbewerb->deleteWettbewerbTyp($Wettbewerb->wbtyp_kurzbz))
			{	
				$oWettbewerb->errormsg[]=$Wettbewerb->errormsg;
			}
			else
			{
				$oWettbewerb->errormsg[]='Veranstaltungskategorie "'.$_REQUEST['wbtyp_kurzbz'].'" gel&ouml;scht.';
				echo '<script language="JavaScript1.2" type="text/javascript">
						<!--
							if (window.opener && !window.opener.closed) {
								if (confirm("Soll die Hauptseite neu aufgebaut werden?")) {
									window.opener.location.reload();
								}	
							}
						-->
						</script>				
					';
			}
		}
		
	} // Datenverarbeiten ende
// Lesen WettbewerbTypen und Wettbewerbe
	$oWettbewerb->wbtyp_kurzbz='';
	$oWettbewerb->wettbewerb_kurzbz='';
	kommune_funk_wettbewerb($oWettbewerb);	
// ------------------------------------------------------------------------------------------
// Aktuelle Datenlesen
// ------------------------------------------------------------------------------------------
?>
		<script language="JavaScript1.2" type="text/javascript">
		<!--
			if (!window.opener || window.opener.closed) {
				document.write('');
			} else {
				window.resizeTo(800,600);
			}

		-->
		</script>				


	<h1>&nbsp;Wettbewerbtype&nbsp;</h1>
	<table  class="tabcontent" cellpadding="1" cellspacing="4">
		<tr>
			<th>Kurzbezeichnung</th>
			<th>Bezeichnung</th>
			<th>Farbe</th>
			<th colspan="3">Aktion</th>
		</tr>
			
		<?php 
		
			 // Zaehler = -1 fuer die Neuanlage  	
			 for ($iTmpZehler=-1;$iTmpZehler<count($oWettbewerb->WettbewerbTyp);$iTmpZehler++) 
			 { 
			 
 			 	if (isset($oWettbewerb->WettbewerbTyp[$iTmpZehler]))
				{
					
					$oWettbewerb->WettbewerbTyp[$iTmpZehler]=(object)$oWettbewerb->WettbewerbTyp[$iTmpZehler];
					
				 	$oWettbewerb->WettbewerbTyp[$iTmpZehler]->wbtyp_kurzbz=trim($oWettbewerb->WettbewerbTyp[$iTmpZehler]->wbtyp_kurzbz);
				 	$oWettbewerb->WettbewerbTyp[$iTmpZehler]->bezeichnung=trim($oWettbewerb->WettbewerbTyp[$iTmpZehler]->bezeichnung);
				 	$oWettbewerb->WettbewerbTyp[$iTmpZehler]->farbe=trim($oWettbewerb->WettbewerbTyp[$iTmpZehler]->farbe);
				}	
		?>
			     
		<form name="selWettbewerbtypen<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>" target="_self" action="<?php echo $_SERVER['PHP_SELF'];?>"  method="post" enctype="multipart/form-data">
		<tr <?php echo ($iTmpZehler%2? ' class="liste0" ':' class="liste1" ');?> >
	
			<td>
				<input class="pflichtfeld" type="text" name="wbtyp_kurzbz" value="<?php echo (isset($oWettbewerb->WettbewerbTyp[$iTmpZehler]->wbtyp_kurzbz)?$oWettbewerb->WettbewerbTyp[$iTmpZehler]->wbtyp_kurzbz:constEingabeFehlt);?>" size="17" maxlength="16" onblur="if (this.value=='') {this.value=this.defaultValue;}" onfocus="if (this.value=='<?php echo constEingabeFehlt; ?>') { this.value='';}" />
				<input class="ausblenden" name="wbtyp_kurzbz_old" value="<?php echo (isset($oWettbewerb->WettbewerbTyp[$iTmpZehler]->wbtyp_kurzbz)?$oWettbewerb->WettbewerbTyp[$iTmpZehler]->wbtyp_kurzbz:'');?>" />
			</td>
	
			<td><input name="bezeichnung" value="<?php echo (isset($oWettbewerb->WettbewerbTyp[$iTmpZehler]->bezeichnung)?$oWettbewerb->WettbewerbTyp[$iTmpZehler]->bezeichnung:'');?>" size="80" maxlength="240" /></td>

			<td><input  <?php echo (isset($oWettbewerb->WettbewerbTyp[$iTmpZehler]->farbe)?' style="background-color:#'.$oWettbewerb->WettbewerbTyp[$iTmpZehler]->farbe.';"':'');?> name="farbe" onchange="if (this.value=='') {this.style.backgroundColor='transparent';} else {this.style.backgroundColor='#' + this.value;}" value="<?php echo (isset($oWettbewerb->WettbewerbTyp[$iTmpZehler]->farbe)?$oWettbewerb->WettbewerbTyp[$iTmpZehler]->farbe:'');?>" size="7" maxlength="6" /></td>

			<td>
				<input class="ausblenden" size="30" name="<?php echo constKommuneParmSetWork;?>" value="<?php echo $oWettbewerb->workSITE; ?>" />
				<input class="ausblenden" size="10" name="work" value="?" />
				<?php echo (isset($oWettbewerb->WettbewerbTyp[$iTmpZehler]->bild_image)?$oWettbewerb->WettbewerbTyp[$iTmpZehler]->bild_image:'');?>
			</td>
			
			<td class="cursor_hand" onclick="if (window.document.selWettbewerbtypen<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.wbtyp_kurzbz.value=='<?php echo constEingabeFehlt; ?>')  {window.document.selWettbewerbtypen<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.wbtyp_kurzbz.focus();return false;}; if (window.document.selWettbewerbtypen<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.bezeichnung.value.length<1) {window.document.selWettbewerbtypen<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.bezeichnung.focus();return false;}; window.document.selWettbewerbtypen<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.work.value='save';window.document.selWettbewerbtypen<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.submit();" >speichern <img height="14px" border="0" alt="sichern - save" src="../../../skin/images/date_edit.png" />&nbsp;</td>
			<td <?php echo ($iTmpZehler<0?' class="ausblenden" ':''); ?> class="cursor_hand" onclick="window.document.selWettbewerbtypen<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.work.value='del';window.document.selWettbewerbtypen<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.submit();" >l&ouml;schen <img height="14px" border="0" alt="entfernen - delete" src="../../../skin/images/date_delete.png" />&nbsp;</td>
			<td <?php echo ($iTmpZehler<0?' class="ausblenden" ':''); ?> ><?php echo ($iTmpZehler<0?'':'<a href="'.$_SERVER['PHP_SELF'].'?userSel=kommune_wartung_wettbewerb&amp;wbtyp_kurzbz='.(isset($oWettbewerb->WettbewerbTyp[$iTmpZehler]->wbtyp_kurzbz)?$oWettbewerb->WettbewerbTyp[$iTmpZehler]->wbtyp_kurzbz:'').'&amp;wbtyp_kurzbz_sel='.(isset($oWettbewerb->WettbewerbTyp[$iTmpZehler]->wbtyp_kurzbz)?$oWettbewerb->WettbewerbTyp[$iTmpZehler]->wbtyp_kurzbz:'').'" target="_self"  title="zum Wettbewerb '.(isset($oWettbewerb->WettbewerbTyp[$iTmpZehler]->wbtyp_kurzbz)?$oWettbewerb->WettbewerbTyp[$iTmpZehler]->wbtyp_kurzbz:'').'"><img height="14px" border="0" alt="entfernen - delete" src="../../../skin/images/application_form_edit.png" /></a>'); ?>&nbsp;</td>
		</tr>
		</form>
		<?php } ?>				
	</table>
	<div><table><tr><td style="color:black;background-color:#FFFFE0;border : 1px solid Black;">&nbsp;&nbsp;&nbsp;</td><td>Pflichtfeld</td></tr></table></div>
				
