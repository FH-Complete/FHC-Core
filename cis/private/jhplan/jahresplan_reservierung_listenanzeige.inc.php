<?php
#-------------------------------------------------------------------------------------------	
/* 
*
* @jahresplan_reservierung_listenanzeige Reservierungen im Veranstaltungszeitraum anzeigen
*
* @param $oJahresplan Objekt mit allen Daten zur Selektion wie Veranstaltungskategorie
* @param $veranstaltung_id Reservierung zu einer bestimmten Veranstaltung
*
* @return Erweiterte Veranstaltungs Array
*
*/
#-------------------------------------------------------------------------------------------	

function jahresplan_reservierung_listenanzeige($oJahresplan,$veranstaltung_id='')
{

	if (empty($veranstaltung_id) && isset($_REQUEST['veranstaltung_id']) )
		$veranstaltung_id=$_REQUEST['veranstaltung_id'];

	// Veranstaltungskategorie
	$oJahresplan->classJahresplan->InitReservierung();

	$oJahresplan->classJahresplan->setVeranstaltungskategorie_kurzbz($oJahresplan->veranstaltungskategorie_kurzbz);

#	$oJahresplan->classJahresplan->setVeranstaltung_id($oJahresplan->veranstaltung_id);
	$oJahresplan->classJahresplan->setVeranstaltung_id('');
	$oJahresplan->classJahresplan->setReservierung_id('');

	$oJahresplan->classJahresplan->setStart($oJahresplan->start);
	$oJahresplan->classJahresplan->setEnde($oJahresplan->ende);	
	
	$oJahresplan->reservierung=array();
	if ($oJahresplan->classJahresplan->loadReservierung())
		$oJahresplan->reservierung=$oJahresplan->classJahresplan->getReservierung();
	else
		$oJahresplan->reservierung=array();
	$oJahresplan->Error=$oJahresplan->classJahresplan->getError();


	$showHTML='';
#	$showHTML.=Test($_REQUEST);

	$showHTML.='<table style="font-size:small;width:100px;border: 2px outset #DDDDDD; padding: 1px 1px 1px 1px;background-color: #FFF5EC;">
			<tr>
				<td>Reservierung ID</td>
				<td>Titel</td>
				<td colspan="2">Datum</td>
				<td>Anlage</td>				
				<td>Veranstaltung</td>				
			</tr>';
		$reserv=$oJahresplan->reservierung;	
		for ($iTmpRes=0;$iTmpRes<count($reserv);$iTmpRes++)
		{			
			if ($reserv[$iTmpRes]['veranstaltung_id']==$veranstaltung_id )
				continue;

			if (isset($pers->langname)) unset($pers->langname);
			$pers=jahresplan_funk_benutzerperson($reserv[$iTmpRes]['uid'],$oJahresplan);

		$showHTML.='		
			<tr onclick="document.getElementById(\'reservierung_id\').value=\''.$reserv[$iTmpRes]['reservierung_id'].'\';">
				<td>'.$reserv[$iTmpRes]['reservierung_id'].'</td>
				<td>'.$reserv[$iTmpRes]['titel'].'</td>
				<td>'.$reserv[$iTmpRes]['datum_anzeige'].'</td>
				';	
				$showHTML.='<td>'.$reserv[$iTmpRes]['beginn_anzeige'].'-'.$reserv[$iTmpRes]['ende_anzeige'].'</td>';				
				$showHTML.='<td>'.(isset($pers->langname)?$pers->langname:$reserv[$iTmpRes]['uid']).'</td>';
				
				if ($reserv[$iTmpRes]['veranstaltung_id']==$veranstaltung_id )
					$showHTML.='<td style="color:green"><b>'.$reserv[$iTmpRes]['veranstaltung_id'].'</b></td>';
				else
					$showHTML.='<td style="color:red">'.$reserv[$iTmpRes]['veranstaltung_id'].'</td>';

			$cTmpResJavaUPD="'".$_SERVER["PHP_SELF"]."?".constJahresplanParmSetWork."=".constJahresplanAJAX."&amp;client_encode=UTF8&amp;".constJahresplanParmSetFunk."=".constJahresplanWartungRESERVIERUNG."&amp;tabindex=&amp;timecheck=".time()."&amp;reservierung_id=".$reserv[$iTmpRes]['reservierung_id']."&amp;veranstaltung_id='";      
			$cTmpResJavaNEW="'".$_SERVER["PHP_SELF"]."?".constJahresplanParmSetWork."=".constJahresplanAJAX."&amp;client_encode=UTF8&amp;".constJahresplanParmSetFunk."=".constJahresplanWartungRESERVIERUNG."&amp;tabindex=&amp;timecheck=".time()."&amp;reservierung_id=".$reserv[$iTmpRes]['reservierung_id']."&amp;veranstaltung_id=".$veranstaltung_id."'";      
			$cTmpResScript=" onclick=\"if ('".$veranstaltung_id."'=='') {alert('Veranstaltung noch nicht gespeichert. ID fehlt ');return false;} ;  if (this.checked==false) { callAjax(".$cTmpResJavaUPD.",'resNEW".$iTmpRes."'); } else { callAjax(".$cTmpResJavaNEW.",'resNEW".$iTmpRes."'); } ; \"" ;	
				
				if (empty($reserv[$iTmpRes]['veranstaltung_id'])  )
					$showHTML.='<td><input '.$cTmpResScript.' type="checkbox" value="'.$reserv[$iTmpRes]['reservierung_id'].'" name="reservierung_id_'.$reserv[$iTmpRes]['reservierung_id'].'" /></td>';
				else
					$showHTML.='<td>wechsel auf '.$veranstaltung_id.'<input '.$cTmpResScript.' type="checkbox" value="'.$reserv[$iTmpRes]['reservierung_id'].'" name="reservierung_id_'.$reserv[$iTmpRes]['reservierung_id'].'" /></td>';
				$showHTML.='<td id="resNEW'.$iTmpRes.'">&nbsp;</td>';
				$showHTML.='</tr>
				<tr><td colspan="10"><hr /></td></tr>';
		$showHTML.='</tr>';
		}
		$showHTML.='		
		</table>';
	return $showHTML;
}
?>
