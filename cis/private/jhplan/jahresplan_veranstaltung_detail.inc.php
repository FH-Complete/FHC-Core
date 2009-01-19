<?php
#-------------------------------------------------------------------------------------------	
/* 
*
* @jahresplan_veranstaltung_detail erweitert die anzeige Veranstaltung um PopUp Code
*												 jahresplan_funk_detail_veranstaltung
*
* @param $oJahresplan Objekt mit allen Daten zur Selektion wie Veranstaltungskategorie
*
* @return HTML Anzeige oder Wartungsanzeige der Ergebnisse einer Veranstaltung in PopUp form
*
*/
function jahresplan_veranstaltung_detail($oJahresplan)
{
	$showHTML='';
	if (!jahresplan_funk_veranstaltung($oJahresplan))
		return $showHTML='keine Veranstaltungen gefunden';
		
	$showHTML.='<div id="work_veranstaltung_popup" style="width:75%;border: 3px inset #F0F0F0 ; padding: 1px 5px 1px 5px;">';
		$showHTML.='<table style="width:100%;background-color: #F5F5F5;"><tr>';
			$showHTML.='<td style="width:70%">&nbsp;</td>
			<td  style="cursor: pointer;border: 2px outset #DDDDDD; padding: 1px 15px 1px 15px;  text-align: center;background-color: #F5F5F5;" onclick="hide_layer(\'work_veranstaltung_popup\');hide_layer(\''.constPopUpName.'\');">
				schliessen<input type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4px;border:0px solid transparent;text-decoration:none; background-color: transparent;" onclick="this.checked=false;" onblur="this.checked=false;" name="schliessen_oben_'.time().'" />[x]
				</td>';
		$showHTML.='</tr></table>';

		$showHTML.='<div id="show_veranstaltung_wartung">';
			$showHTML.=jahresplan_funk_veranstaltung_detail($oJahresplan);
		$showHTML.='<div>';			

		$showHTML.='<table style="width:100%;background-color: #F5F5F5;"><tr>';
			$showHTML.='<td style="width:70%;"></td>
			<td style="cursor: pointer;border: 2px outset #DDDDDD; padding: 1px 15px 1px 15px;  text-align: center;background-color: #F5F5F5;" onclick="hide_layer(\'work_veranstaltung_popup\');hide_layer(\''.constPopUpName.'\');">
				schliessen<input type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4px;border:0px solid transparent;text-decoration:none; background-color: transparent;" onclick="this.checked=false;" onblur="this.checked=false;" name="schliessen_unten_'.time().'" />[x]
			</td>';
		$showHTML.='</tr></table>';
					
	$showHTML.='</div>';

	return $showHTML;
}

#-------------------------------------------------------------------------------------------	
/* 
*
* @jahresplan_funk_veranstaltung_detail anzeige Veranstaltung im Detail oder Wartung (mit Neuanlage)
*
* @param $oJahresplan Objekt mit allen Daten zur Selektion wie Veranstaltungskategorie
*
* @return HTML Anzeige oder Wartungsanzeige der Ergebnisse einer Veranstaltung 
*
*/
function jahresplan_funk_veranstaltung_detail($oJahresplan)
{
	$showHTML='';

	$arrTmpTableStrucktur=$oJahresplan->classJahresplan->getStruckturVeranstaltung();
	$cTmpFormName='workva'.time().'form';
	$cTmpAjaxOutput=$cTmpFormName.'_saveout2';

	$showHTML.='<form name="'.$cTmpFormName.'"  target="_self" action="'.$_SERVER['PHP_SELF'].'"  method="post" enctype="multipart/form-data">';
	$showHTML.='<fieldset id="work_va_fieldset">';

		$showHTML.='<legend>'.(isset($oJahresplan->veranstaltung_id) && !empty($oJahresplan->veranstaltung_id)?'&Auml;nderung':'Neuanlage').'</legend>';

		// Werte ermitteln fuer Anzeige
		$param=(isset($oJahresplan->veranstaltung[0])?$oJahresplan->veranstaltung[0]:array());
		$cTmpJava="'".$_SERVER["PHP_SELF"]."?".constJahresplanParmSetWork."=".constJahresplanAJAX."&amp;client_encode=UTF8&amp;".constJahresplanParmSetFunk."=".constJahresplanWartungVERANSTALTUNG."&amp;tabindex=&amp;form=".$cTmpFormName."'";      

		for ($fildIND=0;$fildIND<count($arrTmpTableStrucktur);$fildIND++)
		{				
		
				$cTmpWert='';
				$cTmpName=$arrTmpTableStrucktur[$fildIND]['name'];
				if ($arrTmpTableStrucktur[$fildIND]['laenge']>5)
					$arrTmpTableStrucktur[$fildIND]['laenge']=$arrTmpTableStrucktur[$fildIND]['laenge']-4;
				$cTmpSize=$arrTmpTableStrucktur[$fildIND]['laenge'];
				
				$cTmpDispName=jahresplan_funk_chang_language($cTmpName);

				if (isset($param[$cTmpName]) 
				&& $param[$cTmpName]!='')
					$cTmpWert=$param[$cTmpName];
			
				if (stristr($arrTmpTableStrucktur[$fildIND]['type'],'timestamp') && !is_numeric($cTmpWert) )
				{
					$date=explode('.',$param[$cTmpName.'_datum']);
					$time=explode(':',$param[$cTmpName.'_zeit']);
					if (@checkdate($date[1], $date[0], $date[2]) )
					{			
						if (is_numeric($cTmpTimeStampWert=@mktime($time[0], $time[1], 0, $date[1],$date[0],$date[2] )))
						{
							$cTmpWert=$cTmpTimeStampWert;	
							$param[$cTmpName]=$cTmpTimeStampWert;	
						}
					}	
				}
			
				$showHTML.='<table style="width:100%;font-size:small;white-space : nowrap;background-color: #F0F0F0;"><tr>';

					$showHTML.='<td style="font-size:small;width:140px;text-align:right;vertical-align: top;background-color: #888888;color:#FFFFFF;white-space : nowrap;"><label title="'.$cTmpDispName.'" for="'.$cTmpName.'">'.$cTmpDispName.'&nbsp;</label></td>';
					$showHTML.='<td id="'.$cTmpName.'_empfang" text-align:left;vertical-align: top;>';

		// ID Key Feld
				if (stristr($cTmpName,'_id') || stristr($arrTmpTableStrucktur[$fildIND]['type'],'primary') )
				{
						$showHTML.='<input style="display:none;font-size:small;" name="'.$cTmpName.'" type="text" value="'.$cTmpWert.'" />'.$cTmpWert;
						$cTmpJava.="+'&amp;".$cTmpName."='+window.document.".$cTmpFormName.".".$cTmpName.".value";

						$showHTML.='<input style="display:none;font-size:small;" name="'.$cTmpName.'_old" type="text" value="'.$cTmpWert.'" />';
						$cTmpJava.="+'&amp;".$cTmpName."_old='+window.document.".$cTmpFormName.".".$cTmpName."_old.value";
				}
		// Kategorie		
				else if (stristr($cTmpName,'veranstaltungskategorie_kurzbz') || stristr($cTmpName,'kategorie_kurzbz'))
				{
#					function jahresplan_funk_veranstaltungskategorie_create_select($oJahresplan,$name,$script="",$leerselect=null,$select=null)
					$showHTML.=jahresplan_funk_veranstaltungskategorie_create_select($oJahresplan,$cTmpName,' style="font-size:small;" ','-',(isset($param['veranstaltungskategorie_kurzbz'])?$param['veranstaltungskategorie_kurzbz']:$oJahresplan->veranstaltungskategorie_kurzbz) );
					$cTmpJava.="+'&amp;".$cTmpName."='+window.document.".$cTmpFormName.".veranstaltungskategorie_kurzbz.options[window.document.".$cTmpFormName.".veranstaltungskategorie_kurzbz.selectedIndex].value";
				}
		// Start - Ende		
				else if (stristr($arrTmpTableStrucktur[$fildIND]['name'],'start') 
				|| stristr($arrTmpTableStrucktur[$fildIND]['name'],'ende'))
				{
					if (empty($param[$cTmpName]) && stristr($arrTmpTableStrucktur[$fildIND]['name'],'start'))
					{
						$param[$cTmpName]=mktime(12,0,0,date("m"),date("d"),date("y"));  
						$param[$cTmpName.'_datum']=date("d.m.Y",$param[$cTmpName]); 
						$param[$cTmpName.'_zeit']=date("H:i",$param[$cTmpName]); 
					}
					if (empty($param[$cTmpName]) && stristr($arrTmpTableStrucktur[$fildIND]['name'],'ende'))
					{
						$param[$cTmpName]=mktime(13,0,0,date("m"),date("d"),date("y")); 
						$param[$cTmpName.'_datum']=date("d.m.Y",$param[$cTmpName]); 
						$param[$cTmpName.'_zeit']=date("H:i",$param[$cTmpName]); 
					}
				
					if (isset($param[$cTmpName.'_datum']) 
					&& $param[$cTmpName.'_datum']!='')
						$cTmpWert=$param[$cTmpName.'_datum'];
					
					if (isset($param[$cTmpName.'_zeit']) 
					&& $param[$cTmpName.'_zeit']!='')
						$cTmpVeranstaltungszeit=$param[$cTmpName.'_zeit'];
					
					if (empty($cTmpVeranstaltungszeit) && stristr($arrTmpTableStrucktur[$fildIND]['name'],'start'))
						$cTmpVeranstaltungszeit=(isset($oWettbewerb->Einladung[0]['start'])?$oWettbewerb->Einladung[0]['start_zeit']:'12:00');
					if (empty($cTmpVeranstaltungszeit) && stristr($arrTmpTableStrucktur[$fildIND]['name'],'ende'))
						$cTmpVeranstaltungszeit=(isset($oWettbewerb->Einladung[0]['ende'])?$oWettbewerb->Einladung[0]['ende_zeit']:'13:00');
				
					$cTmpCheckHeute = date("d.m.Y", mktime(0,0,0,date("m"),date("d"),date("y")));
					$showHTML.='<input style="font-size:small;" id="'.$cTmpName.'1" onblur="var time_stamp=TimestampDatumZeit(window.document.'.$cTmpFormName.'.'.$cTmpName.'1.value,window.document.'.$cTmpFormName.'.'.$cTmpName.'2.value); if (!time_stamp) {this.focus();} else {window.document.'.$cTmpFormName.'.'.$cTmpName.'.value=time_stamp; };" name="'.$cTmpName.'1" type="text" size="11" maxlength="11"  title="eingabe '.$cTmpDispName.'" value="'.(empty($cTmpWert)?$cTmpCheckHeute:$cTmpWert).'"  />';
 
  				#    if (window.document.'.$cTmpFormName.'.start.value > window.document.'.$cTmpFormName.'.ende.value) {alert(\'Endedatum ist kleiner dem Beginndatum\');window.document.'.$cTmpFormName.'.start1.focus(); return false;};
					$showHTML.='<select  style="font-size:small;" onchange="var time_stamp=TimestampDatumZeit(window.document.'.$cTmpFormName.'.'.$cTmpName.'1.value,window.document.'.$cTmpFormName.'.'.$cTmpName.'2.value); if (!time_stamp) {this.focus();} else {window.document.'.$cTmpFormName.'.'.$cTmpName.'.value=time_stamp; }; "  id="'.$cTmpName.'2" name="'.$cTmpName.'2">';
						for ($timeIND=0;$timeIND<24;$timeIND++)
						{
							$cTmpTime=$timeIND.':00';
							$showHTML.='<option '. ($cTmpVeranstaltungszeit==$cTmpTime || $cTmpVeranstaltungszeit=='0'.$cTmpTime ?'selected="selected"':'') .' value="'.(strlen($cTmpTime)==4?'0'.$cTmpTime:$cTmpTime).'">'.$cTmpTime.'</option>';	
							$cTmpTime=$timeIND.':15';
							$showHTML.='<option '. ($cTmpVeranstaltungszeit==$cTmpTime || $cTmpVeranstaltungszeit=='0'.$cTmpTime ?'selected="selected"':'') .' value="'.(strlen($cTmpTime)==4?'0'.$cTmpTime:$cTmpTime).'">'.$cTmpTime.'</option>';	
							$cTmpTime=$timeIND.':30';
							$showHTML.='<option '. ($cTmpVeranstaltungszeit==$cTmpTime || $cTmpVeranstaltungszeit=='0'.$cTmpTime ?'selected="selected"':'') .' value="'.(strlen($cTmpTime)==4?'0'.$cTmpTime:$cTmpTime).'">'.$cTmpTime.'</option>';	
							$cTmpTime=$timeIND.':45';
							$showHTML.='<option '. ($cTmpVeranstaltungszeit==$cTmpTime || $cTmpVeranstaltungszeit=='0'.$cTmpTime ?'selected="selected"':'') .' value="'.(strlen($cTmpTime)==4?'0'.$cTmpTime:$cTmpTime).'">'.$cTmpTime.'</option>';	
						}	
					$showHTML.='</select>';	

					$showHTML.='<input style="display:none;font-size:small;" id="'.$cTmpName.'" name="'.$cTmpName.'" type="text" value="'.$param[$cTmpName].'"  />';
				
					$cTmpJava.="+'&amp;".$cTmpName."='+window.document.".$cTmpFormName.".".$cTmpName.".value";
					$cTmpJava.="+'&amp;".$cTmpName."_datum='+window.document.".$cTmpFormName.".".$cTmpName."1.value";
					$cTmpJava.="+'&amp;".$cTmpName."_zeit='+window.document.".$cTmpFormName.".".$cTmpName."2.value";
			
				}	
	// Insert
			else if (stristr($arrTmpTableStrucktur[$fildIND]['name'],'insert'))
			{
				$cTmpWert=$param[$cTmpName];
				if (isset($param[$cTmpName.'_datum']) 
				&& $param[$cTmpName.'_datum']!='')
				{
					$cTmpWert=$param[$cTmpName.'_datum'];
					$showHTML.= $cTmpWert; // Datum 
				}
				if (isset($param[$cTmpName.'_zeit']) 
				&& $param[$cTmpName.'_zeit']!='')
					$showHTML.=' '.$param[$cTmpName.'_zeit'];

				if (stristr($cTmpName,'von') )		
				{
					if (!empty($cTmpWert))
					{
						$pers=jahresplan_funk_benutzerperson($cTmpWert,$oJahresplan);
						if (isset($pers->langname)) 
							$showHTML.=' '.$pers->langname;
						else	
							$showHTML.=' '.$cTmpWert;
						if (isset($pers->foto_image)) 
							$showHTML.=' '.$pers->foto_image;
					}
					else	
						$cTmpWert=$oJahresplan->userUID;
				}	
				else if (stristr($cTmpName,'amum') && empty($cTmpWert))		
				{
					$cTmpWert=time();
				}	
				$showHTML.='<input style="display:none;font-size:small;" id="'.$cTmpName.'" name="'.$cTmpName.'" type="text" title="eingabe '.$cTmpDispName.' orig:'.$param[$cTmpName].'" value="'.$cTmpWert.'" />';
				if (empty($param['veranstaltung_id']))
					$cTmpJava.="+'&amp;".$cTmpName."='+encodeURIComponent(window.document.".$cTmpFormName.".".$cTmpName.'.value)';
			}	

		// Update	
			else if (stristr($arrTmpTableStrucktur[$fildIND]['name'],'update'))
			{
				$cTmpWert=$param[$cTmpName];
				
				if (isset($param[$cTmpName.'_datum']) 
				&& $param[$cTmpName.'_datum']!='')
				{
					$cTmpWert=$param[$cTmpName.'_datum'];
					$showHTML.= $cTmpWert;
				}
				if (isset($param[$cTmpName.'_zeit']) 
				&& $param[$cTmpName.'_zeit']!='')
					$showHTML.=' '.$param[$cTmpName.'_zeit'];


				if (stristr($cTmpName,'von') )		
				{
					if (!empty($cTmpWert))
					{
						$pers=jahresplan_funk_benutzerperson($cTmpWert,$oJahresplan);
						if (isset($pers->langname)) 
							$showHTML.=' '.$pers->langname;
						else	
							$showHTML.=' '.$cTmpWert;
						if (isset($pers->foto_image) ) 
							$showHTML.=' '.$pers->foto_image;
						$cTmpWert=$oJahresplan->userUID;
					}
					else
						$cTmpWert=$oJahresplan->userUID;	
				}	
				else if (stristr($cTmpName,'amum'))		
					$cTmpWert=time();		
							
				$showHTML.='<input style="display:none;font-size:small;" id="'.$cTmpName.'" name="'.$cTmpName.'" type="text" value="'.$cTmpWert.'" />';
				if (!empty($param['veranstaltung_id']))
					$cTmpJava.="+'&amp;".$cTmpName."='+encodeURIComponent(window.document.".$cTmpFormName.".".$cTmpName.'.value)';
			}	
	// Freigabe	
			else if (stristr($arrTmpTableStrucktur[$fildIND]['name'],'freigabe'))
			{
				$cTmpWert=$param[$cTmpName];
	
				if (isset($param[$cTmpName.'_datum']) 
				&& $param[$cTmpName.'_datum']!='')
				{
					$cTmpWert=$param[$cTmpName.'_datum'];
					$showHTML.= $cTmpWert;
				}
				if (isset($param[$cTmpName.'_zeit']) 
				&& $param[$cTmpName.'_zeit']!='')
					$showHTML.=' '.$param[$cTmpName.'_zeit'];

				if (stristr($cTmpName,'von'))		
				{
					if (!empty($cTmpWert))
					{
						$pers=jahresplan_funk_benutzerperson($cTmpWert,$oJahresplan);
						if (isset($pers->langname)) 
							$showHTML.=' '.$pers->langname;
						else	
							$showHTML.=' '.$cTmpWert;
						if (isset($pers->foto_image)) 
							$showHTML.=' '.$pers->foto_image;				
					}
					$showHTML.='<input style="display:none;" style="font-size:small;" id="'.$cTmpName.'" type="text" title="eingabe '.$cTmpDispName.'" value="'.$cTmpWert.'" />';
				}
				else if (stristr($cTmpName,'amum') )		
				{
					$showHTML.='<input onclick="if (this.checked==false) {window.document.'.$cTmpFormName.'.freigabeamum.value=\'\';window.document.'.$cTmpFormName.'.freigabevon.value=\'\';} else {window.document.'.$cTmpFormName.'.freigabeamum.value=\''.time().'\';window.document.'.$cTmpFormName.'.freigabevon.value=\''.$oJahresplan->userUID.'\';} ;" id="'.$cTmpName.'1" name="'.$cTmpName.'1" title="eingabe '.$cTmpDispName.'" type="checkbox" '.(!empty($cTmpWert)? ' checked="checked" ':'').' value="'.$cTmpWert.'" />';
					$showHTML.='<input style="display:none;" style="font-size:small;" id="'.$cTmpName.'" type="text" title="eingabe '.$cTmpDispName.'" value="'.$cTmpWert.'" />';
				}
				$cTmpJava.="+'&amp;".$cTmpName."='+encodeURIComponent(window.document.".$cTmpFormName.".".$cTmpName.".value)";
			}	
			else
			{			
				switch (trim($arrTmpTableStrucktur[$fildIND]['type']))
				{
			        case 'timestamp':

						$showHTML.='<input style="font-size:small;" id="'.$cTmpName.'" type="text" maxlength="'.$cTmpSize.'" size="'.($cTmpSize>30?30:$cTmpSize) .'"  title="eingabe '.$cTmpDispName.'" value="'.$cTmpWert.'" />';
						$cTmpJava.="+'&amp;".$cTmpName."='+window.document.".$cTmpFormName.".".$cTmpName.".value";

			            break;	
			        case 'text':
						$showHTML.='<textarea style="font-size:small;" id="'.$cTmpName.'"  name="'.$cTmpName.'" cols="40" rows="3">'.trim($cTmpWert).'</textarea>';						
						$cTmpJava.="+'&amp;".$cTmpName."='+encodeURIComponent(window.document.".$cTmpFormName.".".$cTmpName.".value)";

			            break;
      				 case 'character':
						$showHTML.='<input style="font-size:small;" id="'.$cTmpName.'"  name="'.$cTmpName.'" type="text" maxlength="32" size="'.($cTmpSize>30?30:$cTmpSize) .'"  title="eingabe '.$cTmpDispName.'" value="'.$cTmpWert.'"  />';
						$cTmpJava.="+'&amp;".$cTmpName."='+encodeURIComponent(window.document.".$cTmpFormName.".".$cTmpName.".value)";
			          	break;											
      				 default:
						$showHTML.='<input style="font-size:small;" id="'.$cTmpName.'"  name="'.$cTmpName.'" type="text" maxlength="'.$cTmpSize.'" size="'.($cTmpSize>30?30:$cTmpSize) .'"  title="eingabe '.$cTmpDispName.'" value="'.$cTmpWert.'"  />';
						$cTmpJava.="+'&amp;".$cTmpName."='+encodeURIComponent(window.document.".$cTmpFormName.".".$cTmpName.".value)";
			          	break;
				}	
			}
			$showHTML.='</td>';	
		$showHTML.='</tr></table>';	
		}
// Speicherknopf und Entfernen				
		$showHTML.='<table style="width:100%;text-align:right;">
				<tr><td style="white-space : nowrap;width:50%;"></td>';
				
				if (!empty($param['veranstaltung_id']))
				{
					$cTmpDeleteScript="if (window.document.".$cTmpFormName.".veranstaltungskategorie_kurzbz.options[window.document.".$cTmpFormName.".veranstaltungskategorie_kurzbz.selectedIndex].value=='') {alert('".constEingabeFehlt." ".jahresplan_funk_chang_language("veranstaltungskategorie_kurzbz")."');window.document.".$cTmpFormName.".veranstaltungskategorie_kurzbz.focus(); return false;} ; if (window.document.".$cTmpFormName.".titel.value=='') {alert('".constEingabeFehlt." ".jahresplan_funk_chang_language("titel")."');window.document.".$cTmpFormName.".titel.focus(); return false;} ; if (window.document.".$cTmpFormName.".beschreibung.value=='') {alert('".constEingabeFehlt." ".jahresplan_funk_chang_language("beschreibung")."');window.document.".$cTmpFormName.".beschreibung.focus(); return false;} ;  if (window.document.".$cTmpFormName.".start.value > window.document.".$cTmpFormName.".ende.value) {alert('Endedatum ist kleiner dem Beginndatum');window.document.".$cTmpFormName.".start1.focus(); return false;}; callAjax(".$cTmpJava.",'".$cTmpAjaxOutput."');";
					$cTmpDeleteScript=' onclick="' .str_replace(constJahresplanWartungVERANSTALTUNG,constJahresplanDeleteVERANSTALTUNG,$cTmpDeleteScript).'"';
					$showHTML.='<td '.$cTmpDeleteScript.' style="white-space : nowrap;cursor: pointer;text-align:center;border: 2px outset #DDDDDD; padding: 1px 15px 1px 15px;background-color: #F5F5F5;" >
						entfernen
						<input onclick="this.checked=false;" onblur="this.checked=false;" type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4px;border:0px solid transparent;text-decoration:none; background-color: transparent;" name="check_neuanlage_aendern" />
						<img title="entfernen" height="14px" src="../../../skin/images/edit_trash.png" alt="entfernen Veranstaltung'.time().'" border="0" />
						</td>';
				}
				
				$showHTML.='<td id="'.$cTmpName.'empfang" text-align:left;vertical-align: top;>';

				$cTmpSaveScript=" onclick=\"if (window.document.".$cTmpFormName.".veranstaltungskategorie_kurzbz.options[window.document.".$cTmpFormName.".veranstaltungskategorie_kurzbz.selectedIndex].value=='') {alert('".constEingabeFehlt." ".jahresplan_funk_chang_language("veranstaltungskategorie_kurzbz")."');window.document.".$cTmpFormName.".veranstaltungskategorie_kurzbz.focus(); return false;} ; if (window.document.".$cTmpFormName.".titel.value=='') {alert('".constEingabeFehlt." ".jahresplan_funk_chang_language("titel")."');window.document.".$cTmpFormName.".titel.focus(); return false;} ; if (window.document.".$cTmpFormName.".beschreibung.value=='') {alert('".constEingabeFehlt." ".jahresplan_funk_chang_language("beschreibung")."');window.document.".$cTmpFormName.".beschreibung.focus(); return false;} ;  if (window.document.".$cTmpFormName.".start.value > window.document.".$cTmpFormName.".ende.value) {alert('Endedatum ist kleiner dem Beginndatum');window.document.".$cTmpFormName.".start1.focus(); return false;}; set_layer('<b>speichern aktiv</b>','saveVa'); callAjax(".$cTmpJava.",'veranstaltung_id_empfang');\"" ;	
				$showHTML.='<td id="saveVa" '.$cTmpSaveScript.' style="white-space : nowrap;cursor: pointer;text-align:center;border: 2px outset #DDDDDD; padding: 1px 15px 1px 15px;background-color: #F5F5F5;">
						speicher
						<input onclick="this.checked=false;" onblur="this.checked=false;" type="checkbox" value="" style="'.(!stristr($_SERVER['HTTP_USER_AGENT'],'OPERA') && !stristr($_SERVER['HTTP_USER_AGENT'],'Safari')?'display:none;':'').'font-size: 4px;border:0px solid transparent;text-decoration:none; background-color: transparent;" name="check_neuanlage_aendern" />
						<img title="speichern oder aendern" height="14px" src="../../../skin/images/edit.png" alt="aendernVeranstaltung'.time().'" border="0" />
					</td>';

		$showHTML.='</tr></table>';

	$showHTML.='</fieldset>';

	$showHTML.='<fieldset id="work_re_fieldset">';
		$showHTML.='<legend>Reservierung</legend>';

		$showHTML.='<table summary="zugeteilte Reservierungen ">';
		if (isset($param['reservierung']) && is_array($param['reservierung']) )
		{	
			$showHTML.='<tr><td>ID</td><td>Titel</td><td>Beschreibung</td><td colspan="3">Anlage</td></tr>';
	
			$reserv=$param['reservierung'];
			
			for ($iTmpRes=0;$iTmpRes<count($reserv);$iTmpRes++)
			{

				if (isset($pers->langname)) unset($pers->langname);
				$pers=jahresplan_funk_benutzerperson($reserv[$iTmpRes]['uid'],$oJahresplan);
				$showHTML.='<tr >';
					$showHTML.='<td>'.$reserv[$iTmpRes]['reservierung_id'].'</td>';
					$showHTML.='<td>'.$reserv[$iTmpRes]['titel'].'</td>';
					$showHTML.='<td>'.$reserv[$iTmpRes]['beschreibung'].'</td>';
					$showHTML.='<td>'.(isset($pers->langname)?$pers->langname:$reserv[$iTmpRes]['uid']).'</td>';
					
					$showHTML.='<td>'.$reserv[$iTmpRes]['datum_anzeige'].'</td>';
					$showHTML.='<td>'.$reserv[$iTmpRes]['beginn_anzeige'].' - '.$reserv[$iTmpRes]['ende_anzeige'].'</td>';

					
		$cTmpResJavaUPD="'".$_SERVER["PHP_SELF"]."?".constJahresplanParmSetWork."=".constJahresplanAJAX."&amp;client_encode=UTF8&amp;".constJahresplanParmSetFunk."=".constJahresplanWartungRESERVIERUNG."&amp;tabindex=&amp;timecheck=".time()."&amp;reservierung_id=".$reserv[$iTmpRes]['reservierung_id']."&amp;veranstaltung_id='";      
		$cTmpResJavaNEW="'".$_SERVER["PHP_SELF"]."?".constJahresplanParmSetWork."=".constJahresplanAJAX."&amp;client_encode=UTF8&amp;".constJahresplanParmSetFunk."=".constJahresplanWartungRESERVIERUNG."&amp;tabindex=&amp;timecheck=".time()."&amp;reservierung_id=".$reserv[$iTmpRes]['reservierung_id']."&amp;veranstaltung_id='+window.document.".$cTmpFormName.".veranstaltung_id.value";      
		
		$cTmpResScript=" onchange=\"if (window.document.".$cTmpFormName.".veranstaltung_id.value=='') {alert('Veranstaltung noch nicht gespeichert. ID fehlt ');return false;} ;  if (this.checked==false) { callAjax(".$cTmpResJavaUPD.",'resUPD".$iTmpRes."'); } else { callAjax(".$cTmpResJavaNEW.",'resUPD".$iTmpRes."'); } ; \"" ;	
		$cTmpResScript=" onclick=\"if (window.document.".$cTmpFormName.".veranstaltung_id.value=='') {alert('Veranstaltung noch nicht gespeichert. ID fehlt ');return false;} ;  if (this.checked==false) { callAjax(".$cTmpResJavaUPD.",'resUPD".$iTmpRes."'); } else { callAjax(".$cTmpResJavaNEW.",'resUPD".$iTmpRes."'); } ; \"" ;	

				$showHTML.='<td ><input '.$cTmpResScript.' checked="checked" type="checkbox" value="'.$reserv[$iTmpRes]['reservierung_id'].'" name="reservierung_id_'.$reserv[$iTmpRes]['reservierung_id'].'" /></td>';
				$showHTML.='<td id="resUPD'.$iTmpRes.'">&nbsp;</td>';
				$showHTML.='</tr>';
			}
		}
		$showHTML.='</table>';

		$cTmpAjaxOutput=constPopUpReserv."a";
		$cTmpJava=str_replace(constJahresplanWartungVERANSTALTUNG,constJahresplanLesenRESERVIERUNG,$cTmpJava) ;      
		$cTmpResScript='if (window.document.'.$cTmpFormName.'.veranstaltung_id.value==\'\') {alert(\''.constEingabeFehlt.' Veranstaltung ID \');return false;} ;hide_layer(\''.$cTmpAjaxOutput.'on\');show_layer(\''.$cTmpAjaxOutput.'off\');show_layer(\''.$cTmpAjaxOutput.'\');  callAjax('.$cTmpJava.',\''.$cTmpAjaxOutput.'\'); ' ;	

	$showHTML.='</fieldset>';

	$showHTML.='<fieldset id="work_re_fieldset">
		<legend>weitere Reservierung 
					<span id="'.$cTmpAjaxOutput.'on"  onclick="'.$cTmpResScript.'" style="border: 2px outset #DDDDDD;cursor: pointer; padding: 1px 15px 1px 15px;  text-align: center;background-color: #F5F5F5;"  >
						suchen
					</span> 
					<span id="'.$cTmpAjaxOutput.'off" onclick="clear_layer(\''.$cTmpAjaxOutput.'\');hide_layer(\''.$cTmpAjaxOutput.'\');hide_layer(\''.$cTmpAjaxOutput.'off\');show_layer(\''.$cTmpAjaxOutput.'on\');" style="display:none;cursor: pointer;border: 2px outset #DDDDDD;cursor: pointer; padding: 1px 15px 1px 15px;  text-align: center;background-color: #F5F5F5;"  >
						schliessen
					</span> 		
		
		</legend>
		<table style="width:100%;text-align:right;">
			<tr>
				<td style="text-align:left;vertical-align: top;"><div id="'.$cTmpAjaxOutput.'">&nbsp;</div></td>
			</tr>
		</table>
		</fieldset>';

	$showHTML.='</form>';
	return $showHTML;
}
?>
