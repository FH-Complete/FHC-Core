<?php
#-------------------------------------------------------------------------------------------	
/* 
*
* @jahresplan_veranstaltung_listenanzeige anzeigen Veranstaltungen in Listenform 
*
* @param $oJahresplan Objekt mit allen Daten zur Selektion wie Veranstaltungskategorie
*
* @return HTML Liste der Ergebnisse der Veranstaltungen
*
*/
function jahresplan_veranstaltung_listenanzeige($oJahresplan)
{
	$showHTML='';
	if (!$oJahresplan->classJahresplan)
		return $showHTML;

	if (!is_array($oJahresplan->veranstaltung) || count($oJahresplan->veranstaltung)<1 )
		jahresplan_funk_veranstaltung($oJahresplan,true);	
		
	if (!is_array($oJahresplan->veranstaltung) || count($oJahresplan->veranstaltung)<1 )
		return 'keine Veranstaltung ' .(isset($oJahresplan->veranstaltung[0]['bezeichnung'])?$oJahresplan->veranstaltung[0]['bezeichnung']:$oJahresplan->veranstaltungskategorie_kurzbz);

	if (!isset($oJahresplan->veranstaltung[0]['veranstaltung_id']) 
	|| empty($oJahresplan->veranstaltung[0]['veranstaltung_id']) )
	{
		$oJahresplan->Error[]='Keine Information gefunden'.(!empty($oJahresplan->start)?' ab Datum '.$oJahresplan->start:''); 
		return $showHTML;		
	}

	$showHTML.='<table  style="width:100%;border: 0px outset #000000; padding: 1px 1px 1px 1px;background-color: #EBEBEB; " summary="Veranstaltungenpflegen - Auswahl">
			<tr style="background-color: #F7F7F7;">	
				<td style="border: 1px inset #F7F7F7;text-align:center;">Titel</td>
				<td style="border: 1px inset #F7F7F7;text-align:center;">Veranstaltung</td>
				<td style="border: 1px inset #F7F7F7;text-align:center;">Beginn/Ende</td>
				<td style="border: 1px inset #F7F7F7;text-align:center;">Res.</td>
			';
	if ($oJahresplan->Wartungsberechtigt)					
		$showHTML.='
				<td colspan="2">Aktion</td>
				<td colspan="2" style="text-align:center;">Freigabe</td>
				';
				
	$showHTML.='</tr>';
	reset($oJahresplan->veranstaltung);
  	$cTmpLastKat="";
	$cTmpLastRow=0;
	$cTmpLastDat="";
	for ($iTmpZehler=0;$iTmpZehler<count($oJahresplan->veranstaltung);$iTmpZehler++)
	{

		if ($cTmpLastDat!=$oJahresplan->veranstaltung[$iTmpZehler]['start_jjjjmmtt'])
		{
#			if (!empty($cTmpLastDat))
#				$showHTML.='<tr><td colspan="10"><hr /></td></tr>';
			$showHTML.='<tr>';
					$showHTML.='<td colspan="15"><h1>&nbsp;'.strftime(constDatumLang,$oJahresplan->veranstaltung[$iTmpZehler]['start_timestamp']).'&nbsp;</h1></td>';
			$showHTML.='</tr>';
			$cTmpLastKat="";
		}
		$cTmpLastDat=$oJahresplan->veranstaltung[$iTmpZehler]['start_jjjjmmtt'];
		
		if ($cTmpLastKat!=$oJahresplan->veranstaltung[$iTmpZehler]['veranstaltungskategorie_kurzbz'])
		{
			$showHTML.='<tr>';
					$showHTML.='<td style="background:#'.$oJahresplan->veranstaltung[$iTmpZehler]['farbe'].';" colspan="15">&nbsp;'.$oJahresplan->veranstaltung[$iTmpZehler]['bezeichnung'].'&nbsp;'.(isset($oJahresplan->veranstaltung[$iTmpZehler]['bild_image'])?$oJahresplan->veranstaltung[$iTmpZehler]['bild_image']:'').'</td>';
			$showHTML.='</tr>';
			$cTmpLastRow=0;
		}
		$cTmpLastKat=$oJahresplan->veranstaltung[$iTmpZehler]['veranstaltungskategorie_kurzbz'];
		
		if ($cTmpLastRow%2)
			$showCSS=' style="border: 1px inset #F7F7F7; background:#FEFFEC" ';
		else
			$showCSS=' style="border: 1px inset #F7F7F7; background:#F5FEE9"  ';
	
		$cTmpLastRow++;
		$showHTML.='<tr id="jh_va_row'.$iTmpZehler.'" style="font-size:small;vertical-align: top;">';
		$showHTML.='
				<td '.$showCSS.' title="ID&nbsp;'.$oJahresplan->veranstaltung[$iTmpZehler]['veranstaltung_id'].'&nbsp;'.$oJahresplan->veranstaltung[$iTmpZehler]['titel'].'" onclick="show_layer(\'va_detail_kal'.$iTmpZehler.'\');" ondblclick="hide_layer(\'va_detail_kal'.$iTmpZehler.'\');" >
					<input onclick="this.checked=false;" onfocus="show_layer(\'va_detail_kal'.$iTmpZehler.'\');" onblur="this.checked=false;hide_layer(\'va_detail_kal'.$iTmpZehler.'\');" type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4px;border:0px solid transparent;text-decoration:none; background-color: transparent;" name="check_va_detail_kal'.$iTmpZehler.'" />
					'.$oJahresplan->veranstaltung[$iTmpZehler]['veranstaltung_id'] .' 
					<img title="Detailansicht ID&nbsp;'.$oJahresplan->veranstaltung[$iTmpZehler]['veranstaltung_id'].'&nbsp;'.$oJahresplan->veranstaltung[$iTmpZehler]['titel'].'" height="14px" src="../../../skin/images/icon_voransicht.gif" alt="Detailansicht der Veranstaltung" border="0" />
					';
					$showHTML.=($oJahresplan->Wartungsberechtigt && strlen($oJahresplan->veranstaltung[$iTmpZehler]['titel'])>13?substr($oJahresplan->veranstaltung[$iTmpZehler]['titel'],0,13).'...':$oJahresplan->veranstaltung[$iTmpZehler]['titel']);
				
				if ($oJahresplan->Wartungsberechtigt && stristr($oJahresplan->veranstaltung[$iTmpZehler]['veranstaltungskategorie_kurzbz'],'*'))	
					$showHTML.='&nbsp;<img title="Anzeige nur fuer Mitarbeiter" height="14px" src="../../../skin/images/personen_liste.gif" alt="Anzeige nur fuer Mitarbeiter" border="0" />';
					
			$showHTML.='</td>';
			if ($oJahresplan->Wartungsberechtigt)			
				$showHTML.='
				<td '.$showCSS.'>'.(strlen($oJahresplan->veranstaltung[$iTmpZehler]['beschreibung'])>30?substr($oJahresplan->veranstaltung[$iTmpZehler]['beschreibung'],0,30).'...':$oJahresplan->veranstaltung[$iTmpZehler]['beschreibung']) .'</td>';
			else
				$showHTML.='
				<td '.$showCSS.'>'.(strlen($oJahresplan->veranstaltung[$iTmpZehler]['beschreibung'])>70?substr($oJahresplan->veranstaltung[$iTmpZehler]['beschreibung'],0,70).'...':$oJahresplan->veranstaltung[$iTmpZehler]['beschreibung']) .'</td>';

				
			$showHTML.='
				<td '.$showCSS.' title="'.strftime(constZeitKalenderListe,$oJahresplan->veranstaltung[$iTmpZehler]["start_timestamp"]).' '.strftime(constZeitKalenderListe,$oJahresplan->veranstaltung[$iTmpZehler]["ende_timestamp"]).'">'.strftime('%R',$oJahresplan->veranstaltung[$iTmpZehler]["start_timestamp"]).' / '.strftime('%R',$oJahresplan->veranstaltung[$iTmpZehler]["ende_timestamp"]).'&nbsp;</td>
				';

		if (isset($oJahresplan->veranstaltung[$iTmpZehler]['reservierung_id']) && !empty($oJahresplan->veranstaltung[$iTmpZehler]['reservierung_titel']) )			
			$showHTML.='
				<td '.$showCSS.'><img onclick="show_layer(\'DetailReservierung'.$iTmpZehler.'\');" ondblclick="hide_layer(\'DetailReservierung'.$iTmpZehler.'\');" title="Reservierung"  height="14px"  src="../../../skin/images/image_legend0.gif" alt="Detailansicht der Reservierung" border="0" /></td>
				';
		else
			$showHTML.='
				<td '.$showCSS.'>-</td>
				';
				
		if ($oJahresplan->Wartungsberechtigt)			
		{
			$cTmpJavaWartung="'".$_SERVER["PHP_SELF"]."?".constJahresplanParmSetWork."=".constJahresplanAJAX."&amp;client_encode=UTF8&amp;".constJahresplanParmSetFunk."=".constJahresplanDetailVERANSTALTUNG."&amp;veranstaltung_id=".$oJahresplan->veranstaltung[$iTmpZehler]['veranstaltung_id']."'";
			$cTmpJavaWartung="show_layer('".constPopUpName."');callAjax(".$cTmpJavaWartung.",'".constPopUpName."');" ;	
			$cTmpScriptWartung=" onclick=\"".$cTmpJavaWartung."\"";
			$showHTML.='<td '.$cTmpScriptWartung.' style="cursor: pointer;text-align: center;width:75px;font-size:smaller;border: 2px outset  #F0F0F0;background-color: #FDF7EA; " >
				<img title="ID '.$oJahresplan->veranstaltung[$iTmpZehler]['veranstaltung_id'].' aendern '.$oJahresplan->veranstaltung[$iTmpZehler]['titel'].'" height="14px" src="../../../skin/images/edit.png" alt="aendern Veranstaltung" border="0" />
				&auml;ndern
				<input onclick="this.checked=false;'.$cTmpJavaWartung.'" onblur="this.checked=false;" type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4px;border:0px solid transparent;text-decoration:none; background-color: transparent;" name="check_va_detail_kal'.$iTmpZehler.'" />
				</td>';
				
			$cTmpJavaWartung="'".$_SERVER["PHP_SELF"]."?".constJahresplanParmSetWork."=".constJahresplanAJAX."&amp;client_encode=UTF8&amp;".constJahresplanParmSetFunk."=".constJahresplanDeleteVERANSTALTUNG."&amp;veranstaltung_id=".$oJahresplan->veranstaltung[$iTmpZehler]['veranstaltung_id']."'";
			$cTmpJavaWartung="callAjax(".$cTmpJavaWartung.",'jh_va_delrow".$iTmpZehler."');" ;	
			$cTmpScriptWartung=" onclick=\"".$cTmpJavaWartung."\"";
			$showHTML.='<td id="jh_va_delrow'.$iTmpZehler.'" '.$cTmpScriptWartung.' style="cursor: pointer;text-align: center;width:75px;font-size:smaller;border: 2px outset  #F0F0F0;background-color: #FDF7EA; " >
				<img title="ID '.$oJahresplan->veranstaltung[$iTmpZehler]['veranstaltung_id'].' entfernen '.$oJahresplan->veranstaltung[$iTmpZehler]['titel'].'" height="14px" src="../../../skin/images/edit_trash.png" alt="entfernen Veranstaltung" border="0" />
				entfernen
				<input onclick="this.checked=false;'.$cTmpJavaWartung.'" onblur="this.checked=false;" type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4px;border:0px solid transparent;text-decoration:none; background-color: transparent;" name="check_va_detail_kal'.$iTmpZehler.'" />
				</td>';
				
			unset($cTmpJavaWartung);		
			$showHTML.='<td style="font-size:10px;" '.$showCSS.' title="Freigabe von '.(!empty($oJahresplan->veranstaltung[$iTmpZehler]['freigabename'])?$oJahresplan->veranstaltung[$iTmpZehler]['freigabename']:$oJahresplan->veranstaltung[$iTmpZehler]['freigabevon']).'">'.(!empty($oJahresplan->veranstaltung[$iTmpZehler]['freigabeamum'])?$oJahresplan->veranstaltung[$iTmpZehler]['freigabeamum'].', '.$oJahresplan->veranstaltung[$iTmpZehler]['freigabevon']:'') ;
				if (empty($oJahresplan->veranstaltung[$iTmpZehler]['freigabeamum']))
					$showHTML.='<img title="keine Freigabe" height="14px" src="../../../skin/images/login.gif" alt="keine Freigabe" border="0" />';
				$showHTML.='</td>';
			
			if ($oJahresplan->veranstaltung[$iTmpZehler]["start_timestamp"]>$oJahresplan->veranstaltung[$iTmpZehler]["ende_timestamp"])
				$showHTML.='
					<td '.$showCSS.'><b>Fehler! Start kleiner Ende</b>&nbsp;</td>
				';	
		}
		
		$showHTML.='
			</tr>
		';
#	<div style="position: absolute;z-index:10; padding: 15px 15px 15px 15px;display:none;border:2px outset #CCCCCC;background-color:#F5F5F5;" id="va_detail_kal'.$iTmpZehler.'">
		$showHTML.='
			<tr><td colspan="7">
				<div  style="position: absolute;z-index:50; padding: 10px 10px 10px 10px;display:none;border:3px outset #CCCCCC;background-color:#F5F5F5;"  id="va_detail_kal'.$iTmpZehler.'">
				<div onclick="hide_layer(\'va_detail_kal'.$iTmpZehler.'\');" style="cursor: pointer;width:100%;text-align:right;border:1px solid #CCCCCC;background-color:#CCCCCC;">schliessen<input type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4px;border:0px solid transparent;text-decoration:none; background-color: transparent;" onclick="this.checked=false;" onblur="this.checked=false;" name="schliessen_oben_'.time().'" />[x]</div>
			';
			$showHTML.=jahresplan_funk_show_veranstaltung_detail($oJahresplan->veranstaltung[$iTmpZehler],$oJahresplan);
		
		$showHTML.='</div>					
			</td>
		</tr>
		';

		if (isset($oJahresplan->veranstaltung[$iTmpZehler]['reservierung_id']) && !empty($oJahresplan->veranstaltung[$iTmpZehler]['reservierung_titel']) )			
		{
			$showHTML.='<tr><td colspan="7">
				<div style="position: absolute;z-index:13; padding: 15px 15px 15px 15px;display:none;border:2px outset #CCCCCC;background-color:#F5F5F5;"  id="DetailReservierung'.$iTmpZehler.'">
						<div onclick="hide_layer(\'DetailReservierung'.$iTmpZehler.'\');" style="width:100%;text-align:right;border:1px solid #CCCCCC;background-color:#FEFFD5;"><a href="#">schliessen [x]</a></div>
								'.jahresplan_funk_show_reservierung_detail($oJahresplan->veranstaltung[$iTmpZehler]['reservierung'],$oJahresplan).'
				</div></td></tr>';
		}					
	}
	$showHTML.='<tr><td colspan="7"  style="border:1px inset silver;">Legende :
					 Detailanzeige <img title="Detailansicht" height="14px"  src="../../../skin/images/icon_voransicht.gif" alt="Detailansicht der Veranstaltung" border="0" />
					, Druckvorschau <img  title="Druckansicht"  height="14px" src="../../../skin/images/printbutton.gif" alt="Druckansicht der Veranstaltung" border="0" />
					, Reservierung <img  title="Reservierungsdetail"  height="14px" src="../../../skin/images/image_legend0.gif" alt="Druckansicht der Veranstaltung" border="0" />
					';
					if ($oJahresplan->Wartungsberechtigt)			
					{
						$showHTML.=', Datenwartung (Neuanlage, &Auml;nderung) <img title="Wartung" height="14px" src="../../../skin/images/edit.png" alt="Wartung Veranstaltung" border="0" />';
						$showHTML.=', Entfernen <img title="Enfernen" height="14px" src="../../../skin/images/edit_trash.png" alt="Entfernen Veranstaltung" border="0" />';

						$showHTML.=', keine Freigabe <img title="keine Freigabe" height="14px" src="../../../skin/images/login.gif" alt="Legende keine Freigabe" border="0" />';
						$showHTML.=', nur Mitarbeiter <img title="Anzeige nur fuer Mitarbeiter" height="14px" src="../../../skin/images/personen_liste.gif" alt="Legende Anzeige nur fuer Mitarbeiter" border="0" />';
					}
	$showHTML.='<br /><b>Bei Fragen geben Sie bitte immer die Veranstaltungs ID an.</b>';	
	$showHTML.='</td></tr>';
	$showHTML.='</table>';	
	return $showHTML.=jahresplan_funk_disp_error($oJahresplan);		
}
?>
