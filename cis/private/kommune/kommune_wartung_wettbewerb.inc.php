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
*	Wettbewerb - Pflege
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
			// iconverarbeitung 
			if(isset($_FILES['uploadicon']['tmp_name']) && !empty($_FILES['uploadicon']['tmp_name']) )
			{
				$filename = $_FILES['uploadicon']['tmp_name'];
				//File oeffnen
				if ($fp = fopen($filename,'r'))
				{
					//auslesen
					$string = fread($fp, filesize($filename));
					fclose($fp);
					if (isset($fp)) unset($fp);
					//in HEX-Werte umrechnen
			    		$hex="";
					for ($i=0;$i<strlen($string);$i++)
					        $hex.=(strlen(dechex(ord($string[$i])))<2)? "0".dechex(ord($string[$i])): dechex(ord($string[$i]));
					if (!empty($hex)) 
						$_REQUEST["icon"]=$hex;
				}	
			}		
		if ($_REQUEST["wettbewerb_kurzbz"]==constEingabeFehlt)
		{
			$oWettbewerb->errormsg[]='Fehler Kurzbezeichnung ist leer! Type '.$_REQUEST["wbtyp_kurzbz"];
			$work='';
		}	
		$Wettbewerb= new komune_wettbewerb();
		if ($work=='save')
		{
			$Wettbewerb->new=false;
			if ( 
				(isset($_REQUEST["wbtyp_kurzbz_old"]) && $_REQUEST["wbtyp_kurzbz"] != $_REQUEST["wbtyp_kurzbz_old"]) 
			   	|| !isset($_REQUEST["wbtyp_kurzbz_old"]) || (isset($_REQUEST["wbtyp_kurzbz_old"]) && empty($_REQUEST["wbtyp_kurzbz_old"]))
				|| (isset($_REQUEST["wettbewerb_kurzbz_old"]) && $_REQUEST["wettbewerb_kurzbz"] != $_REQUEST["wettbewerb_kurzbz_old"])
				|| !isset($_REQUEST["wettbewerb_kurzbz_old"]) || (isset($_REQUEST["wettbewerb_kurzbz_old"]) && empty($_REQUEST["wettbewerb_kurzbz_old"]))  )
			{
				$Wettbewerb->new=true;
				if ( (isset($_REQUEST["wbtyp_kurzbz_old"]) && $_REQUEST["wbtyp_kurzbz"] != $_REQUEST["wbtyp_kurzbz_old"])
				||  (isset($_REQUEST["wettbewerb_kurzbz_old"]) && $_REQUEST["wettbewerb_kurzbz"] != $_REQUEST["wbtyp_kurzbz_old"]) )
					$Wettbewerb->deleteWettbewerb($_REQUEST["wbtyp_kurzbz_old"],$_REQUEST["wettbewerb_kurzbz_old"]);
			}	
		
			$Wettbewerb->wbtyp_kurzbz=$_REQUEST["wbtyp_kurzbz"];
			$Wettbewerb->wettbewerb_kurzbz=$_REQUEST["wettbewerb_kurzbz"];

			$Wettbewerb->regeln=$_REQUEST["regeln"];
			if (empty($Wettbewerb->regeln))
				$Wettbewerb->regeln=constEingabeFehlt;
				
			$Wettbewerb->icon=$_REQUEST["icon"];
			$Wettbewerb->forderungstage=$_REQUEST["forderungstage"];
			if (empty($Wettbewerb->forderungstage))
				$Wettbewerb->forderungstage=7;
			
			$Wettbewerb->teamgroesse=$_REQUEST["teamgroesse"];
			if (empty($Wettbewerb->teamgroesse))
				$Wettbewerb->teamgroesse=1;
			
			$Wettbewerb->uid=$_REQUEST["uid"];
			if (empty($Wettbewerb->uid))
				$Wettbewerb->uid=$oWettbewerb->user;

#			var_dump($Wettbewerb);
			
			if(!$Wettbewerb->saveWettbewerb())
			{	
				$oWettbewerb->errormsg[]='Fehler bei der '.($Wettbewerb->new?' Neuanlage ':' &Auml;nderung ').' '.$Wettbewerb->errormsg;
			}
			else
			{
				$oWettbewerb->errormsg[]=$_REQUEST["wbtyp_kurzbz"].'-'.$_REQUEST["wettbewerb_kurzbz"].' '.($Wettbewerb->new?' Neuanlage ':' &Auml;nderung ').' erfolgreich! '.$Wettbewerb->errormsg;
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
			$Wettbewerb->wbtyp_kurzbz=$_REQUEST["wbtyp_kurzbz"];
			$Wettbewerb->wettbewerb_kurzbz=$_REQUEST["wettbewerb_kurzbz"];

			if(!$Wettbewerb->deleteWettbewerb($Wettbewerb->wbtyp_kurzbz,$Wettbewerb->wettbewerb_kurzbz))
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
   	$oWettbewerb->wbtyp_kurzbz=trim((isset($_REQUEST['wbtyp_kurzbz_sel']) ? $_REQUEST['wbtyp_kurzbz_sel']:''));
	$oWettbewerb->wettbewerb_kurzbz='';
	kommune_funk_wettbewerb($oWettbewerb);	
	
#var_dump($oWettbewerb);	
	
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

	<form name="selWettbewerbtypenXX" target="_self" action="<?php echo $_SERVER['PHP_SELF'];?>"  method="post" enctype="multipart/form-data">
	<h1>&nbsp;Wettbewerb&nbsp;&nbsp;Typenauswahl&nbsp;
				<select class="pflichtfeld" name="wbtyp_kurzbz_sel" onchange="window.document.selWettbewerbtypenXX.submit();">
				 	<?php
					echo '<option  value="">alle</option>';
					reset($oWettbewerb->WettbewerbTyp);
					for ($iTmpZehler2=0;$iTmpZehler2<count($oWettbewerb->WettbewerbTyp);$iTmpZehler2++) 
					{ 
						$oWettbewerb->WettbewerbTyp[$iTmpZehler2]['wbtyp_kurzbz']=trim($oWettbewerb->WettbewerbTyp[$iTmpZehler2]['wbtyp_kurzbz']);
						echo '<option '.(!empty($oWettbewerb->WettbewerbTyp[$iTmpZehler2]['farbe'])?' style="background-color:#'.$oWettbewerb->WettbewerbTyp[$iTmpZehler2]['farbe'].'" ':'').'   '.(isset($_REQUEST['wbtyp_kurzbz_sel']) && !empty($_REQUEST['wbtyp_kurzbz_sel']) && $_REQUEST['wbtyp_kurzbz_sel']==$oWettbewerb->WettbewerbTyp[$iTmpZehler2]['wbtyp_kurzbz']?' selected="selected" ':'') .' value="'.$oWettbewerb->WettbewerbTyp[$iTmpZehler2]['wbtyp_kurzbz'].'">'.trim($oWettbewerb->WettbewerbTyp[$iTmpZehler2]['wbtyp_kurzbz']).'</option>';
					}	 
				 	?>
				</select>
				<input class="ausblenden"  name="<?php echo constKommuneParmSetWork;?>" value="<?php echo $oWettbewerb->workSITE; ?>" />
			&nbsp;
	</h1>
	</form>
	<table  class="tabcontent" cellpadding="1" cellspacing="4">
		<tr>
			<th>Type</th>
			<th>Wettbewerb</th>
			
			<th>Regeln</th>

			<th>Fordertage</th>
			<th>Teamgr&ouml;sse</th>
			<th colspan="2">Teamleiter(UID)</th>

			<th>iconladen</th>
			<th>icon</th>			
			<th colspan="3">Aktion</th>
		</tr>
			
		<?php 
		
		#var_dump($oWettbewerb->Wettbewerb);
		
			 // Zaehler = -1 fuer die Neuanlage  	
		$iTmpWettbewerb=0;
		 for ($iTmpZehler=-1;$iTmpZehler<count($oWettbewerb->Wettbewerb);$iTmpZehler++) 
		 { 
		 	if ($iTmpZehler!= -1 && empty($oWettbewerb->Wettbewerb[$iTmpZehler]['wettbewerb_kurzbz']))
				continue;
			
			$iTmpWettbewerb++;
		/*
 			 if (isset($oWettbewerb->Wettbewerb[$iTmpZehler]))
			 {
			 	$oWettbewerb->Wettbewerb[$iTmpZehler]->wbtyp_kurzbz=trim($oWettbewerb->Wettbewerb[$iTmpZehler]->wbtyp_kurzbz);
			 	$oWettbewerb->Wettbewerb[$iTmpZehler]->regeln=trim($oWettbewerb->Wettbewerb[$iTmpZehler]->regeln);
			 	$oWettbewerb->Wettbewerb[$iTmpZehler]->farbe=trim($oWettbewerb->Wettbewerb[$iTmpZehler]->farbe);
			}	
		*/	
		?>
			     
		<form name="selWettbewerbtypen<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>" target="_self" action="<?php echo $_SERVER['PHP_SELF'];?>"  method="post" enctype="multipart/form-data">
		<tr <?php echo ($iTmpZehler==-1? ' style="background-color:yellow;" ':($iTmpWettbewerb%2? ' class="liste0" ':' class="liste1" '));?> >
	
			<td>
				<select class="pflichtfeld" name="wbtyp_kurzbz">
				 	<?php
					reset($oWettbewerb->WettbewerbTyp);
					for ($iTmpZehler2=0;$iTmpZehler2<count($oWettbewerb->WettbewerbTyp);$iTmpZehler2++) 
					{ 
						$oWettbewerb->WettbewerbTyp[$iTmpZehler2]['wbtyp_kurzbz']=trim($oWettbewerb->WettbewerbTyp[$iTmpZehler2]['wbtyp_kurzbz']);
						echo '<option '.(!empty($oWettbewerb->WettbewerbTyp[$iTmpZehler2]['farbe'])?' style="background-color:#'.$oWettbewerb->WettbewerbTyp[$iTmpZehler2]['farbe'].'" ':'').'   '.(isset($oWettbewerb->Wettbewerb[$iTmpZehler]) && isset($oWettbewerb->Wettbewerb[$iTmpZehler]['wbtyp_kurzbz']) && $iTmpZehler != -1 && $oWettbewerb->Wettbewerb[$iTmpZehler]['wbtyp_kurzbz']==$oWettbewerb->WettbewerbTyp[$iTmpZehler2]['wbtyp_kurzbz']?' selected="selected" ':(!empty($oWettbewerb->wbtyp_kurzbz) && $oWettbewerb->wbtyp_kurzbz==$oWettbewerb->WettbewerbTyp[$iTmpZehler2]['wbtyp_kurzbz']?' selected="selected" ':( $iTmpZehler == -1 && $work=='save' && isset($_REQUEST['wbtyp_kurzbz']) && $_REQUEST['wbtyp_kurzbz']==$oWettbewerb->WettbewerbTyp[$iTmpZehler2]['wbtyp_kurzbz']?' selected="selected" ':''))) .' value="'.$oWettbewerb->WettbewerbTyp[$iTmpZehler2]['wbtyp_kurzbz'].'">'.trim($oWettbewerb->WettbewerbTyp[$iTmpZehler2]['wbtyp_kurzbz']).'</option>';
					}	 
				 	?>
				</select>
				
				
				<input class="ausblenden" name="wbtyp_kurzbz_old" value="<?php echo (isset($oWettbewerb->Wettbewerb[$iTmpZehler]) && isset($oWettbewerb->Wettbewerb[$iTmpZehler]['wbtyp_kurzbz'])?$oWettbewerb->Wettbewerb[$iTmpZehler]['wbtyp_kurzbz']:'');?>" />
			</td>
	
			<td>
				<input class="pflichtfeld" type="text" name="wettbewerb_kurzbz" value="<?php echo (isset($oWettbewerb->Wettbewerb[$iTmpZehler]) && isset($oWettbewerb->Wettbewerb[$iTmpZehler]['wettbewerb_kurzbz'])?$oWettbewerb->Wettbewerb[$iTmpZehler]['wettbewerb_kurzbz']:($work=='save' && isset($_REQUEST["wettbewerb_kurzbz"])?$_REQUEST["wettbewerb_kurzbz"]:constEingabeFehlt));?>" size="17" maxlength="16" onblur="if (this.value=='') {this.value=this.defaultValue;}" onfocus="if (this.value=='<?php echo constEingabeFehlt; ?>') { this.value='';}" />
				<input class="ausblenden" name="wettbewerb_kurzbz_old" value="<?php echo (isset($oWettbewerb->Wettbewerb[$iTmpZehler]) && isset($oWettbewerb->Wettbewerb[$iTmpZehler]['wettbewerb_kurzbz'])?$oWettbewerb->Wettbewerb[$iTmpZehler]['wettbewerb_kurzbz']:'');?>" />
			</td>


			<td><input name="regeln" value="<?php echo (isset($oWettbewerb->Wettbewerb[$iTmpZehler]) && isset($oWettbewerb->Wettbewerb[$iTmpZehler]['regeln'])?$oWettbewerb->Wettbewerb[$iTmpZehler]['regeln']:($work=='save' && isset($_REQUEST["regeln"])?$_REQUEST["regeln"]:''));?>" size="20" maxlength="240" /></td>
			<td><input name="forderungstage" value="<?php echo (isset($oWettbewerb->Wettbewerb[$iTmpZehler]) && isset($oWettbewerb->Wettbewerb[$iTmpZehler]['forderungstage'])?$oWettbewerb->Wettbewerb[$iTmpZehler]['forderungstage']:($work=='save' && isset($_REQUEST["forderungstage"])?$_REQUEST["forderungstage"]:7));?>" size="3" maxlength="5" /></td>
			<td><input name="teamgroesse" value="<?php echo (isset($oWettbewerb->Wettbewerb[$iTmpZehler]) && isset($oWettbewerb->Wettbewerb[$iTmpZehler]['teamgroesse'])?$oWettbewerb->Wettbewerb[$iTmpZehler]['teamgroesse']:($work=='save' && isset($_REQUEST["teamgroesse"])?$_REQUEST["teamgroesse"]:1));?>" size="3" maxlength="5" /></td>
			<td><input class="pflichtfeld" name="uid" value="<?php echo (isset($oWettbewerb->Wettbewerb[$iTmpZehler]) && isset($oWettbewerb->Wettbewerb[$iTmpZehler]['uid'])?$oWettbewerb->Wettbewerb[$iTmpZehler]['uid']:($work=='save' && isset($_REQUEST["uid"])?$_REQUEST["uid"]:$oWettbewerb->user));?>" size="10" maxlength="20" /></td>

			<td><?php echo (isset($oWettbewerb->Wettbewerb[$iTmpZehler]->foto_image)?$oWettbewerb->Wettbewerb[$iTmpZehler]->foto_image:''); ?></td>
			
			
			<td>
				 <input size="8" maxlength="140" type="file" id="uploadicon" name="uploadicon" alt="suche" title="suchen" style="font-size:xx-small;" />

				 <input class="ausblenden" name="icon" value="<?php echo (isset($oWettbewerb->Wettbewerb[$iTmpZehler]) && isset($oWettbewerb->Wettbewerb[$iTmpZehler]['icon'])?$oWettbewerb->Wettbewerb[$iTmpZehler]['icon']:($work=='save' && isset($_REQUEST["icon"])?$_REQUEST["icon"]:''));?>" />				
			</td>
			
			<td>
				<input class="ausblenden" name="wbtyp_kurzbz_sel" value="<?php echo (isset($_REQUEST['wbtyp_kurzbz_sel']) && !empty($_REQUEST['wbtyp_kurzbz_sel'])?$_REQUEST['wbtyp_kurzbz_sel']:''); ?>" />
				<input class="ausblenden" name="<?php echo constKommuneParmSetWork;?>" value="<?php echo $oWettbewerb->workSITE; ?>" />
				<input class="ausblenden" name="work" value="?" />
				<?php echo (isset($oWettbewerb->Wettbewerb[$iTmpZehler]) && isset($oWettbewerb->Wettbewerb[$iTmpZehler]['icon_image'])?$oWettbewerb->Wettbewerb[$iTmpZehler]['icon_image']:'');?>
			</td>
			
			<td class="cursor_hand" onclick="if (window.document.selWettbewerbtypen<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.wbtyp_kurzbz.value=='<?php echo constEingabeFehlt; ?>')  {window.document.selWettbewerbtypen<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.wbtyp_kurzbz.focus();return false;};if (window.document.selWettbewerbtypen<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.wettbewerb_kurzbz.value=='<?php echo constEingabeFehlt; ?>')  {window.document.selWettbewerbtypen<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.wettbewerb_kurzbz.focus();return false;};if (window.document.selWettbewerbtypen<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.regeln.value.length<1) {window.document.selWettbewerbtypen<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.regeln.focus();return false;}; window.document.selWettbewerbtypen<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.work.value='save';window.document.selWettbewerbtypen<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.submit();" >speichern <img height="14px" border="0" alt="sichern - save" src="../../../skin/images/date_edit.png" />&nbsp;</td>
			<td <?php echo ($iTmpZehler<0?' class="ausblenden" ':''); ?> class="cursor_hand" onclick="window.document.selWettbewerbtypen<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.work.value='del';window.document.selWettbewerbtypen<?php echo ($iTmpZehler<0?'':$iTmpZehler); ?>.submit();" >l&ouml;schen <img height="14px" border="0" alt="entfernen - delete" src="../../../skin/images/date_delete.png" />&nbsp;</td>
		
			<td valign="top"><?php echo ($iTmpZehler<0?'':'<a href="'.$_SERVER['PHP_SELF'].'?userSel=kommune_wartung_team&amp;wbtyp_kurzbz='.(isset($oWettbewerb->Wettbewerb[$iTmpZehler]['wbtyp_kurzbz'])?$oWettbewerb->Wettbewerb[$iTmpZehler]['wbtyp_kurzbz']:'').'&amp;wettbewerb_kurzbz='.(isset($oWettbewerb->Wettbewerb[$iTmpZehler]['wettbewerb_kurzbz'])?$oWettbewerb->Wettbewerb[$iTmpZehler]['wettbewerb_kurzbz']:'').'" target="_self"  title="zum Wettbewerb '.(isset($oWettbewerb->Wettbewerb[$iTmpZehler]['wettbewerb_kurzbz'])?$oWettbewerb->Wettbewerb[$iTmpZehler]['wettbewerb_kurzbz']:'').'"><img height="14px" border="0" alt="entfernen - delete" src="../../../skin/images/application_form_edit.png" /></a>'); ?>&nbsp;</td>
		
		
		</tr>
		</form>
		<?php
		if ($iTmpZehler==-1)
			echo '<tr><td colspan="10"><hr></td></tr>';
		 } ?>				
	</table>
	<div><table><tr><td style="color:black;background-color:#FFFFE0;border : 1px solid Black;">&nbsp;&nbsp;&nbsp;</td><td>Pflichtfeld</td>
	<td style="color:black;background-color:yellow;border : 1px solid Black;">&nbsp;&nbsp;&nbsp;</td><td>Neuanlage</td>
	</tr></table>
				
