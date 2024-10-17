<?php
/* echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
 
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

require_once('../../../config/cis.config.inc.php');
require_once('../../../include/phrasen.class.php');

	$sprache = getSprache(); 
	$p=new phrasen($sprache);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
		<meta http-equiv="Content-Type' content='text/xml;charset=UTF-8" />
		<meta name="mssmarttagspreventparsing" content="true" />
		<meta http-equiv="imagetoolbar" content="no" />
	
		<meta http-equiv="expires" content="<?php echo gmdate("D, d M Y H:i:s") . ' GMT';?> " />
		<meta http-equiv="Cache-Control" content="Private" />
				
		<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
		<style type="text/css" >
			
			.ausblenden {display:none;}
			.fehler {border: 1px  solid red;background: red;color:#FFFFFF; }

			.container {vertical-align:top;padding:1px 0px 1px 0px;margin:1px 0px 1px 0px;border: 1px  inset silver; }

			.container_zeile {vertical-align:top;padding:0px 0px 0px 0px;margin:0px 0px 0px 0px;border: 0px; }
			.container_schalter {vertical-align:top;padding:0px 0px 0px 0px;margin:0px 0px 0px 0px;border: 0px; }
			
			.row1 {vertical-align:top; background: #F7F7F7;border-bottom : 1px inset black;height: 1px;}
			.row2 {vertical-align:top; background: #F7F7F7;border-bottom : 1px inset black;height: 1px;}
		</style>
		
	<script type="text/javascript" language="JavaScript1.2">
	<!--
		function show_layer(x)
		{
	 		if (document.getElementById && document.getElementById(x)) 
			{  
				document.getElementById(x).style.visibility = 'visible';
				document.getElementById(x).style.display = 'inline';
			} else if (document.all && document.all[x]) {      
			   	document.all[x].visibility = 'visible';
				document.all[x].style.display='inline';
		      	} else if (document.layers && document.layers[x]) {                          
		           	 document.layers[x].visibility = 'show';
				 document.layers[x].style.display='inline';
		          }
		}
	
		function hide_layer(x)
		{
			if (document.getElementById && document.getElementById(x)) 
			{                       
			   	document.getElementById(x).style.visibility = 'hidden';
				document.getElementById(x).style.display = 'none';
	       	} else if (document.all && document.all[x]) {                                
				document.all[x].visibility = 'hidden';
				document.all[x].style.display='none';
	       	} else if (document.layers && document.layers[x]) {                          
		           	 document.layers[x].visibility = 'hide';
				 document.layers[x].style.display='none';
		          }
		}	
	-->
	</script>
			
</head>
<body>
	<h1><?php echo $p->t('tools/applikationsliste');?></h1>
	<table>
	<tr>
		<td><br/><?php echo $p->t('tools/naehereInformationenfindenSieUnter');?>.<br/><br/></td>
	</tr>
	</table>
<?php

	$cSoftGridApplicationsRoot=array('%CSIDL_PROGRAMS%\\SoftGrid Applications\\','%CSIDL_PROGRAMS%\\SoftGrid Applications');

	$debug=trim((isset($_REQUEST['debug']) ? $_REQUEST['debug']:false));
	
	$cXMLFile=trim((isset($_REQUEST['xml']) ? $_REQUEST['xml']:'../../../documents/infrastruktur/AppList.xml'));	
	if (!is_file($cXMLFile) && !stristr($cXMLFile,'http') )
	{
		if (is_file("AppList.xml") )
		{
			$cXMLFile="AppList.xml";
		}
	}	
									
	if (!is_file($cXMLFile) && !stristr($cXMLFile,'http') )
	{
		die($p->t('tools/datei') .$cXMLFile. $p->t('tools/wurdeNichtGefunden'));
	}		

    if (!$xml = (Array)simplexml_load_file($cXMLFile))
	{
		die($p->t('tools/fehlerBeimLesenDerDatei').$cXMLFile);
	}
	
    if (!isset($xml['APP']))
	{
		die($p->t('tools/keineDatenGefunden'));
	}

	$arrAPPMENUE=array();
	for ($i=0;$i<count($xml['APP']);$i++)
	{
		// Applikation und Link lesen					
		$cAPPName=trim((isset($xml['APP'][$i]['NAME'])?$xml['APP'][$i]['NAME']:''));
		$cAPPLink=trim((isset($xml['APP'][$i]['OSD'])?$xml['APP'][$i]['OSD']:'')); 
		//echo '<hr>'.$cAPPName.' - '.$cAPPLink.'<br>';
		
		if (empty($cAPPName) || empty($cAPPLink))
		{
			continue;
		}
		$cAPPHref='<a onclick="this.target=\'_blank\';" href="'.$cAPPLink.'">'.$cAPPName.'</a>';
		$cAPPIcon=trim((isset($xml['APP'][$i]['ICON'])?$xml['APP'][$i]['ICON']:'')); 

		// Applikation-Array Main
		$arrAPPRow=array('MainLevel'=>$cAPPName,'SecondLevel'=>'','ThirdLevel'=>'','FourthLevel'=>'','APPLink'=>$cAPPLink,'APPHref'=>$cAPPHref,'Icon'=>$cAPPIcon,'Error'=>1);
	
		// SHORTCUTLIST / SHORTCUT lesen
		$cAPPLocation='';
		$arrAPPShortcutlist=(array)$xml['APP'][$i];

		if (isset($arrAPPShortcutlist['SHORTCUTLIST']) && is_array($arrAPPShortcutlist) )
		{
			// SHORTCUT lesen
			$arrAPPShortcut=(array)$arrAPPShortcutlist['SHORTCUTLIST'];
			if(isset($arrAPPShortcut['SHORTCUT']) && !isset($arrAPPShortcut['SHORTCUT']['LOCATION']))
				$arrAPPShortcut['SHORTCUT']=$arrAPPShortcut['SHORTCUT'][0];
			//var_dump($arrAPPShortcut);
			
			if (isset($arrAPPShortcut['SHORTCUT']) && isset($arrAPPShortcut['SHORTCUT']['LOCATION']) )
			{
				// Location - Path zur Anwendung aufsplitten fuer Menue
				$cAPPLocation=(string)$arrAPPShortcut['SHORTCUT']['LOCATION'];
				// den APP-Path entfernen aus der Location - wird benoetigt fuer die Softwareunterteilung
				$cAPPLocation=trim(str_ireplace($cSoftGridApplicationsRoot,'',$cAPPLocation));
				
				$arrAPPRow['Error']=0; 

				// Softwarunterteilung
				if (!empty($cAPPLocation))
				{
					$cAPPDisplay=(string)$arrAPPShortcut['SHORTCUT']['DISPLAY'];
					if (!empty($cAPPDisplay))
					{
						$arrAPPRow['APPHref']='<a onclick="this.target=\'_blank\';" href="'.$cAPPLink.'">'.$cAPPDisplay.'</a>';
					}

					$arrLevel=explode('\\',$cAPPLocation);	
					if (is_array($arrLevel) && count($arrLevel)<1)
					{
						$arrLevel=explode('/',$cAPPLocation);	
					}
					$arrAPPRow['SecondLevel']=trim((isset($arrLevel[0])?$arrLevel[0]:'')); 
					$arrAPPRow['ThirdLevel']=trim((isset($arrLevel[1])?$arrLevel[1]:'')); 
					$arrAPPRow['FourthLevel']=trim((isset($arrLevel[2])?$arrLevel[2]:''));
					 
				}
			}
		}
		// Sortkey umwandeln auf Kleinbuchstaben
		$cSort=strtolower($arrAPPRow['SecondLevel'].$arrAPPRow['ThirdLevel'].$arrAPPRow['FourthLevel'].' '.$i);

		// Hinzufuegen der Applikation zur Softwareliste - Array
		if ($debug || empty($tmp_value['Error']) )
		{
			$arrAPPMENUE[$cSort]=$arrAPPRow;
		}
			
	} // Ende XML Verarbeiten
	
	// Plausib ob Daten ermittelt werden konnten fuer die HTML Liste
	if (!is_array($arrAPPMENUE) || count($arrAPPMENUE)<1)
	{
		die('Keine Daten gefunden.');
	}

	// Array Sort nach Key
	ksort($arrAPPMENUE);
	#krsort($arrAPPMENUE);
	
	// DebugMode
	if ($debug)
	{
		$cDebug='';
		$cDebug.="<h3>XML Datei - $cXMLFile - wird verarbeitet.</h3>";
		$cDebug.='<table class="container">';
				$cDebug.='<tr>';
					$cDebug.='<td>Main</td>';
					$cDebug.='<td>Second</td>';
					$cDebug.='<td>Third</td>';
					$cDebug.='<td>Fourth</td>';
					$cDebug.='<td>Href</td>';
					$cDebug.='<td>Icon</td>';
					$cDebug.='<td>Error</td>';
				$cDebug.='</tr>';
				
			reset($arrAPPMENUE);
			$cLastSecondLevel=null;
			while (list( $tmp_key, $tmp_value ) = each($arrAPPMENUE)) 
			{
				if (isset($tmp_value['Error']) && !empty($tmp_value['Error']) ) 
				{
					$cDebug.='<tr class="fehler">';
				}
				else
				{
					$cDebug.='<tr>';
				}
					$cDebug.='<td title="Main">'.$tmp_value['MainLevel'].'</td>';
					$cDebug.='<td title="Second">'.$tmp_value['SecondLevel'].'</td>';
					$cDebug.='<td title="Third">'.$tmp_value['ThirdLevel'].'</td>';
					$cDebug.='<td title="Fourth">'.$tmp_value['FourthLevel'].'</td>';
					$cDebug.='<td title="Href">'.$tmp_value['APPHref'].'</td>';
					$cDebug.='<td title="Icon">'.(!empty($tmp_value['Icon'])?'<img onError="this.onerror=null;this.src=\'../../../skin/images/blank.gif\';" height="16" src="'.$tmp_value['Icon'].'" alt="Icon"  />':'').'</td>';
					$cDebug.='<td title="Error">'.$tmp_value['Error'].'</td>';
				$cDebug.='</tr>';
			}
		$cDebug.='</table>';
		echo $cDebug;
	}	

	/* -----------------------------------------
	 Ausgabe der Softwareliste in HTML Form
	------------------------------------------- */
	$cLastSecondLevel=null;
	$cHTML=''; 

	$cHTML.='<table class="container">'; 

	$bNaechsteReihe=true;
	$cHTML.='<tr><th>'.$p->t('tools/einzelanwendung').'</th><th>'.$p->t('tools/softwarepaket').'</th></tr>'; 
	$cHTML.='<tr valign="top"><td><table class="container">'; 

	reset($arrAPPMENUE);
	while (list( $tmp_key, $tmp_value ) = each($arrAPPMENUE)) 
	{
		$cAPPMainLevel=(isset($tmp_value['MainLevel'])?$tmp_value['MainLevel']:'');
		$cAPPLink=(isset($tmp_value['APPLink'])?$tmp_value['APPLink']:'');
		if (empty($cAPPMainLevel) || empty($cAPPLink))
		{
			continue;
		}
		if (isset($tmp_value['Error']) && !empty($tmp_value['Error']) ) 
		{
			continue;
		}
				
		if (isset($cClass) && $cClass=='row2')
		{
			$cClass='row1';
		}
		else
		{
			$cClass='row2';
		}
		
		
		if ($bNaechsteReihe && !empty($tmp_value['SecondLevel']))
		{
			$bNaechsteReihe=false;
			$cHTML.='</table></td>';
			$cHTML.='<td><table class="container">';
		}
			
		// MainLevel - Es gibt keine Unterteilung
		if (empty($tmp_value['SecondLevel']))
		{ 
			$cHTML.='<tr class="'.$cClass.'">';
				$cHTML.='<td class="'.$cClass.'">'.(!empty($tmp_value['Icon'])?'<img onError="this.onerror=null;this.src=\'../../../skin/images/blank.gif\';" height="16" src="'.$tmp_value['Icon'].'" alt="Icon"  />':'').'&nbsp;'.$tmp_value['APPHref'].'&nbsp;</td>';
			$cHTML.='</tr>';
		}
		else if ($cLastSecondLevel!=strtolower($tmp_value['SecondLevel']))
		{
			$cLastSecondLevel=strtolower($tmp_value['SecondLevel']);	
			$cKeyMD5=md5($cLastSecondLevel.$cClass);
			$cHTML.='<tr class="'.$cClass.'">';
				$cHTML.='<td class="'.$cClass.'">
					<table class="container_zeile">
						<tr>
							<td nowrap onclick="hide_layer(\'on'.$cKeyMD5.'\');show_layer(\'off'.$cKeyMD5.'\');show_layer(\'dat'.$cKeyMD5.'\');">'.$tmp_value['SecondLevel'].'&nbsp;</td>
							<td width="100%" align="right">
								<table class="container_schalter">
								<tr>
									<td id="on'.$cKeyMD5.'" title="anzeigen" onclick="hide_layer(\'on'.$cKeyMD5.'\');show_layer(\'off'.$cKeyMD5.'\');show_layer(\'dat'.$cKeyMD5.'\');"><img alt="anzeigen" src="../../../skin/images/bullet_arrow_right.png" /></td>
									<td class="ausblenden" id="off'.$cKeyMD5.'" title="ausblenden" onclick="hide_layer(\'off'.$cKeyMD5.'\');hide_layer(\'dat'.$cKeyMD5.'\');show_layer(\'on'.$cKeyMD5.'\');"><img alt="ausblenden" src="../../../skin/images/bullet_arrow_down.png" /></td>
								</tr>
								</table>
							</td>	
						</tr>
					</table>';
				$cHTML.='<td class="ausblenden" id="dat'.$cKeyMD5.'">'.SecondLevel($arrAPPMENUE,$cLastSecondLevel).'</td>';
			$cHTML.='</tr>';
		}
		$cLastSecondLevel=strtolower($tmp_value['SecondLevel']);	
	}		
	$cHTML.='</table></td></tr>';
	$cHTML.='</table>';		
	echo $cHTML;

	function SecondLevel($arrAPPMENUE,$cSearchSecondLevel)
	{

		$cLastThirdLevel=null;
		$cHTML='<table class="container">'; 
	
		reset($arrAPPMENUE);
		while (list( $tmp_key, $tmp_value ) = each($arrAPPMENUE)) 
		{
			$cAPPMainLevel=(isset($tmp_value['MainLevel'])?$tmp_value['MainLevel']:'');
			$cAPPLink=(isset($tmp_value['APPLink'])?$tmp_value['APPLink']:'');
			if (empty($cAPPMainLevel) || empty($cAPPLink))
			{
				break;
			}
			$cLastSecondLevel=strtolower($tmp_value['SecondLevel']);
			if ($cSearchSecondLevel!=$cLastSecondLevel)
			{
				continue;
			}
			
			if (isset($cClass) && $cClass=='row2')
			{
				$cClass='row1';
			}
			else
			{
				$cClass='row2';
			}			
			
			// SecondLevel - Es gibt keine ThirdLeve Unterteilung
			if (empty($tmp_value['ThirdLevel']))
			{ 
				$cHTML.='<tr class="'.$cClass.'">';
					$cHTML.='<td class="'.$cClass.'">'.(!empty($tmp_value['Icon'])?'<img onError="this.onerror=null;this.src=\'../../../skin/images/blank.gif\';" height="16" src="'.$tmp_value['Icon'].'" alt="Icon"  />':'').'&nbsp;'.$tmp_value['APPHref'].'</td>';
				$cHTML.='</tr>';
			}
			else if ($cLastThirdLevel!=strtolower($tmp_value['ThirdLevel']))
			{
				$cLastThirdLevel=strtolower($tmp_value['ThirdLevel']);	
				$cKey=md5($cLastSecondLevel.$cLastThirdLevel.$cClass);			
				$cHTML.='<tr  class="'.$cClass.'">';
					$cHTML.='<td class="'.$cClass.'">
							<table class="container_zeile">
								<tr>
									<td nowrap onclick="hide_layer(\'onSecondLevel'.$cKey.'\');show_layer(\'offSecondLevel'.$cKey.'\');show_layer(\'datSecondLevel'.$cKey.'\');">'.$tmp_value['ThirdLevel'].'</td>
									<td width="100%" align="right">
										<table class="container_schalter">
										<tr>
											<td id="onSecondLevel'.$cKey.'" title="anzeigen" onclick="hide_layer(\'onSecondLevel'.$cKey.'\');show_layer(\'offSecondLevel'.$cKey.'\');show_layer(\'datSecondLevel'.$cKey.'\');"><img alt="anzeigen" src="../../../skin/images/bullet_arrow_right.png" /></td>
											<td id="offSecondLevel'.$cKey.'" title="ausblenden" onclick="hide_layer(\'offSecondLevel'.$cKey.'\');hide_layer(\'datSecondLevel'.$cKey.'\');show_layer(\'onSecondLevel'.$cKey.'\');" class="ausblenden"><img alt="ausblenden" src="../../../skin/images/bullet_arrow_down.png" /></td>
										</tr>
										</table>
									</td>									
								</tr>
							</table>';
					$cHTML.='<td class="ausblenden" id="datSecondLevel'.$cKey.'">'.ThirdLevel($arrAPPMENUE,$cSearchSecondLevel,$cLastThirdLevel).'</td>';
				$cHTML.='</tr>';
			}
			$cLastThirdLevel=strtolower($tmp_value['ThirdLevel']);				
		}		
		$cHTML.='</table>';		
		return $cHTML;
	}	

	function ThirdLevel($arrAPPMENUE,$cSearchSecondLevel,$cSearchThirdLevel)
	{

		$cLastFourthLevel=null;
		$cHTML='<table class="container">'; 

		reset($arrAPPMENUE);
		while (list( $tmp_key, $tmp_value ) = each($arrAPPMENUE)) 
		{
			$cAPPMainLevel=(isset($tmp_value['MainLevel'])?$tmp_value['MainLevel']:'');
			$cAPPLink=(isset($tmp_value['APPLink'])?$tmp_value['APPLink']:'');
			if (empty($cAPPMainLevel) || empty($cAPPLink))
			{
				break;
			}
			
			$cLastSecondLevel=strtolower($tmp_value['SecondLevel']);
			$cLastThirdLevel=strtolower($tmp_value['ThirdLevel']);

			if ($cSearchSecondLevel!=$cLastSecondLevel 
			|| $cSearchThirdLevel!=$cLastThirdLevel )
			{
				continue;
			}

			if (isset($cClass) && $cClass=='row2')
			{
				$cClass='row1';
			}
			else
			{
				$cClass='row2';
			}					
			
			// MainLevel - Es gibt keine Unterteilung
			if (empty($tmp_value['FourthLevel']))
			{ 
				$cHTML.='<tr  class="'.$cClass.'">';
					$cHTML.='<td  class="'.$cClass.'">'.(!empty($tmp_value['Icon'])?'<img onError="this.onerror=null;this.src=\'../../../skin/images/blank.gif\';" height="16" src="'.$tmp_value['Icon'].'" alt="Icon"  />':'').'&nbsp;'.$tmp_value['APPHref'].'</td>';
				$cHTML.='</tr>';
			}
			else if ($cLastFourthLevel!=strtolower($tmp_value['FourthLevel']))
			{

				$cLastFourthLevel=strtolower($tmp_value['FourthLevel']);	
				$cKey=md5($cSearchSecondLevel.$cSearchThirdLevel.$cLastFourthLevel.$cClass);	
				
				$cHTML.='<tr class="'.$cClass.'">';
					$cHTML.='<td class="'.$cClass.'">
							<table class="container_zeile">
							<tr>
								<td nowrap onclick="hide_layer(\'onFourthLevel'.$cKey.'\');show_layer(\'offFourthLevel'.$cKey.'\');show_layer(\'datFourthLevel'.$cKey.'\');">'.$tmp_value['FourthLevel'].'</td>
								<td width="100%" align="right">
									<table class="container_schalter">
										<tr>
											<td id="onFourthLevel'.$cKey.'" title="anzeigen" onclick="hide_layer(\'onFourthLevel'.$cKey.'\');show_layer(\'offFourthLevel'.$cKey.'\');show_layer(\'datFourthLevel'.$cKey.'\');"><img alt="anzeigen" src="../../../skin/images/bullet_arrow_right.png" /></td>
											<td id="offFourthLevel'.$cKey.'" title="ausblenden" onclick="hide_layer(\'offFourthLevel'.$cKey.'\');hide_layer(\'datFourthLevel'.$cKey.'\');show_layer(\'onFourthLevel'.$cKey.'\');" class="ausblenden"><img alt="ausblenden" src="../../../skin/images/bullet_arrow_down.png" /></td>
										</tr>
									</table>
								</td>													
							</tr>
							</table>';
					$cHTML.='<td class="ausblenden" id="datFourthLevel'.$cKey.'">'.FourthLevel($arrAPPMENUE,$cSearchSecondLevel,$cSearchThirdLevel,$cLastFourthLevel).'</td>';
				$cHTML.='</tr>';
			}
			$cLastFourthLevel=strtolower($tmp_value['FourthLevel']);				
		}		
		$cHTML.='</table>';		
		return $cHTML;
	}	

	function FourthLevel($arrAPPMENUE,$cSearchSecondLevel,$cSearchThirdLevel,$cSearchFourthLevel)
	{
		
		$cHTML='<table class="container">'; 

		reset($arrAPPMENUE);
		while (list( $tmp_key, $tmp_value ) = each($arrAPPMENUE)) 
		{
			$cAPPMainLevel=(isset($tmp_value['MainLevel'])?$tmp_value['MainLevel']:'');
			$cAPPLink=(isset($tmp_value['APPLink'])?$tmp_value['APPLink']:'');
			if (empty($cAPPMainLevel) || empty($cAPPLink))
			{
				break;
			}
			
			$cLastSecondLevel=strtolower($tmp_value['SecondLevel']);
			$cLastThirdLevel=strtolower($tmp_value['ThirdLevel']);
			$cLastFourthLevel=strtolower($tmp_value['FourthLevel']);

			if ($cSearchSecondLevel!=$cLastSecondLevel 
			|| $cSearchThirdLevel!=$cLastThirdLevel 
			|| $cSearchFourthLevel!=$cLastFourthLevel )			
			{
				continue;
			}
			if (isset($cClass) && $cClass=='row2')
			{
				$cClass='row1';
			}
			else
			{
				$cClass='row2';
			}	
			$cHTML.='<tr class="'.$cClass.'">';
				$cHTML.='<td class="'.$cClass.'">'.(!empty($tmp_value['Icon'])?'<img onError="this.onerror=null;this.src=\'../../../skin/images/blank.gif\';" height="16" src="'.$tmp_value['Icon'].'" alt="Icon"  />':'').'&nbsp;'.$tmp_value['APPHref'].'</td>';
			$cHTML.='</tr>';
		}		
		$cHTML.='</table>';		
		return $cHTML;		
	}	
?>		

	</body>
</html>

